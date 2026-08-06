import com.sun.net.httpserver.HttpExchange;
import com.sun.net.httpserver.HttpHandler;
import com.sun.net.httpserver.HttpServer;

import javax.print.Doc;
import javax.print.DocFlavor;
import javax.print.DocPrintJob;
import javax.print.PrintException;
import javax.print.PrintService;
import javax.print.PrintServiceLookup;
import javax.print.SimpleDoc;
import javax.print.attribute.HashPrintRequestAttributeSet;
import javax.print.attribute.PrintRequestAttributeSet;
import java.awt.AWTException;
import java.awt.Color;
import java.awt.Graphics2D;
import java.awt.GraphicsEnvironment;
import java.awt.Image;
import java.awt.MenuItem;
import java.awt.PopupMenu;
import java.awt.SystemTray;
import java.awt.TrayIcon;
import java.awt.image.BufferedImage;
import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.FileInputStream;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.io.PrintWriter;
import java.net.InetSocketAddress;
import java.net.URI;
import java.nio.charset.StandardCharsets;
import java.text.SimpleDateFormat;
import java.util.Arrays;
import java.util.Date;
import java.util.List;
import java.util.Properties;
import java.util.concurrent.Executors;

/**
 * Local print bridge for Biestro POS.
 * Receives raw ESC/POS bytes over loopback HTTP and forwards them to a
 * printer installed in Windows, via the standard javax.print API (same
 * technique escpos-coffee's PrinterOutputStream uses — a thin wrapper over
 * the OS print spooler, so it inherits real flow control instead of the
 * fixed-delay guessing that Web Bluetooth GATT requires).
 *
 * Ticket/receipt layout stays in JS (bluetooth-printer.js) — this bridge is
 * intentionally a dumb byte pipe, not a second copy of the layout logic.
 */
public class PrintBridge {

    private static int port = 9100;
    private static String configuredPrinterName = "";
    private static PrintWriter logWriter;

    public static void main(String[] args) throws Exception {
        loadConfig();
        openLog();

        HttpServer server = HttpServer.create(new InetSocketAddress("127.0.0.1", port), 0);
        server.setExecutor(Executors.newFixedThreadPool(4));
        server.createContext("/health", cors(PrintBridge::handleHealth));
        server.createContext("/print", cors(PrintBridge::handlePrint));
        server.start();

        log("Print Bridge started on http://127.0.0.1:" + port
                + " (printer=" + (configuredPrinterName.isEmpty() ? "<default>" : configuredPrinterName) + ")");

        setupTray();
    }

    // ── Config ──────────────────────────────────────────────────────────
    private static void loadConfig() {
        Properties props = new Properties();
        try (FileInputStream in = new FileInputStream("config.properties")) {
            props.load(in);
        } catch (IOException e) {
            // No config file yet — run with defaults (OS default printer, port 9100).
        }
        try {
            port = Integer.parseInt(props.getProperty("port", "9100").trim());
        } catch (NumberFormatException e) {
            port = 9100;
        }
        configuredPrinterName = props.getProperty("printer", "").trim();
    }

    private static void openLog() {
        try {
            logWriter = new PrintWriter(new FileWriter("printer-bridge.log", true), true);
        } catch (IOException e) {
            logWriter = null;
        }
    }

    private static void log(String msg) {
        String line = "[" + new SimpleDateFormat("yyyy-MM-dd HH:mm:ss").format(new Date()) + "] " + msg;
        System.out.println(line);
        if (logWriter != null) logWriter.println(line);
    }

    // ── Printer resolution (exact → case-insensitive → substring, same
    //    fallback order escpos-coffee's PrinterOutputStream.getPrintServiceByName uses) ──
    private static PrintService resolvePrintService(String requestedName) throws PrintException {
        PrintService[] all = PrintServiceLookup.lookupPrintServices(null, null);
        String name = (requestedName != null && !requestedName.trim().isEmpty())
                ? requestedName.trim()
                : configuredPrinterName;

        if (name.isEmpty()) {
            PrintService def = PrintServiceLookup.lookupDefaultPrintService();
            if (def == null) throw new PrintException("Tidak ada default printer di Windows.");
            return def;
        }

        for (PrintService ps : all) if (ps.getName().equals(name)) return ps;
        for (PrintService ps : all) if (ps.getName().equalsIgnoreCase(name)) return ps;
        for (PrintService ps : all) if (ps.getName().toLowerCase().contains(name.toLowerCase())) return ps;

        throw new PrintException("Printer '" + name + "' tidak ditemukan. Printer yang terdaftar: "
                + String.join(", ", printServiceNames(all)));
    }

    private static List<String> printServiceNames(PrintService[] services) {
        String[] names = new String[services.length];
        for (int i = 0; i < services.length; i++) names[i] = services[i].getName();
        return Arrays.asList(names);
    }

    // ── HTTP handlers ───────────────────────────────────────────────────
    private static void handleHealth(HttpExchange ex) throws IOException {
        if (!"GET".equals(ex.getRequestMethod())) {
            respond(ex, 405, "text/plain", "Method not allowed".getBytes(StandardCharsets.UTF_8));
            return;
        }
        PrintService[] all = PrintServiceLookup.lookupPrintServices(null, null);
        PrintService def = PrintServiceLookup.lookupDefaultPrintService();

        StringBuilder json = new StringBuilder();
        json.append("{\"status\":\"ok\"");
        json.append(",\"configuredPrinter\":").append(jsonString(configuredPrinterName));
        json.append(",\"defaultPrinter\":").append(jsonString(def == null ? "" : def.getName()));
        json.append(",\"printers\":[");
        for (int i = 0; i < all.length; i++) {
            if (i > 0) json.append(",");
            json.append(jsonString(all[i].getName()));
        }
        json.append("]}");
        respond(ex, 200, "application/json", json.toString().getBytes(StandardCharsets.UTF_8));
    }

    private static void handlePrint(HttpExchange ex) throws IOException {
        if (!"POST".equals(ex.getRequestMethod())) {
            respond(ex, 405, "text/plain", "Method not allowed".getBytes(StandardCharsets.UTF_8));
            return;
        }

        byte[] bytes = readAll(ex.getRequestBody());
        if (bytes.length == 0) {
            respond(ex, 400, "text/plain", "Body kosong.".getBytes(StandardCharsets.UTF_8));
            return;
        }

        String printerParam = queryParam(ex.getRequestURI(), "printer");
        try {
            PrintService service = resolvePrintService(printerParam);
            DocFlavor flavor = DocFlavor.INPUT_STREAM.AUTOSENSE;
            Doc doc = new SimpleDoc(new ByteArrayInputStream(bytes), flavor, null);
            DocPrintJob job = service.createPrintJob();
            PrintRequestAttributeSet attrs = new HashPrintRequestAttributeSet();
            job.print(doc, attrs);

            log("Print OK -> " + service.getName() + " (" + bytes.length + " bytes)");
            respond(ex, 200, "application/json", "{\"status\":\"ok\"}".getBytes(StandardCharsets.UTF_8));
        } catch (Exception e) {
            log("Print FAILED: " + e.getMessage());
            respond(ex, 500, "application/json",
                    ("{\"status\":\"error\",\"message\":" + jsonString(String.valueOf(e.getMessage())) + "}")
                            .getBytes(StandardCharsets.UTF_8));
        }
    }

    // ── CORS wrapper ────────────────────────────────────────────────────
    // The page is served from the Laravel server's LAN IP (different origin
    // than 127.0.0.1:port), so the browser preflights every POST here.
    private static HttpHandler cors(HttpHandler inner) {
        return ex -> {
            ex.getResponseHeaders().add("Access-Control-Allow-Origin", "*");
            ex.getResponseHeaders().add("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
            ex.getResponseHeaders().add("Access-Control-Allow-Headers", "Content-Type");
            ex.getResponseHeaders().add("Access-Control-Max-Age", "86400");
            if ("OPTIONS".equals(ex.getRequestMethod())) {
                ex.sendResponseHeaders(204, -1);
                return;
            }
            inner.handle(ex);
        };
    }

    private static void respond(HttpExchange ex, int status, String contentType, byte[] body) throws IOException {
        ex.getResponseHeaders().add("Content-Type", contentType + "; charset=utf-8");
        ex.sendResponseHeaders(status, body.length);
        try (OutputStream os = ex.getResponseBody()) {
            os.write(body);
        }
    }

    private static byte[] readAll(InputStream in) throws IOException {
        ByteArrayOutputStream out = new ByteArrayOutputStream();
        byte[] buf = new byte[4096];
        int n;
        while ((n = in.read(buf)) != -1) out.write(buf, 0, n);
        return out.toByteArray();
    }

    private static String queryParam(URI uri, String key) {
        String query = uri.getRawQuery();
        if (query == null) return null;
        for (String pair : query.split("&")) {
            int eq = pair.indexOf('=');
            if (eq < 0) continue;
            String k = pair.substring(0, eq);
            if (k.equals(key)) {
                try {
                    return java.net.URLDecoder.decode(pair.substring(eq + 1), "UTF-8");
                } catch (Exception e) {
                    return pair.substring(eq + 1);
                }
            }
        }
        return null;
    }

    private static String jsonString(String s) {
        StringBuilder sb = new StringBuilder("\"");
        for (char c : s.toCharArray()) {
            switch (c) {
                case '"': sb.append("\\\""); break;
                case '\\': sb.append("\\\\"); break;
                case '\n': sb.append("\\n"); break;
                case '\r': sb.append("\\r"); break;
                default: sb.append(c);
            }
        }
        return sb.append("\"").toString();
    }

    // ── System tray icon (best-effort — headless environments just skip it) ──
    private static void setupTray() {
        if (GraphicsEnvironment.isHeadless() || !SystemTray.isSupported()) {
            log("System tray tidak tersedia di environment ini, bridge tetap jalan tanpa icon.");
            return;
        }
        try {
            SystemTray tray = SystemTray.getSystemTray();
            Image icon = renderIcon();

            PopupMenu menu = new PopupMenu();
            MenuItem statusItem = new MenuItem("Print Bridge — printer: "
                    + (configuredPrinterName.isEmpty() ? "(default)" : configuredPrinterName));
            statusItem.setEnabled(false);
            MenuItem exitItem = new MenuItem("Keluar");
            exitItem.addActionListener(e -> System.exit(0));
            menu.add(statusItem);
            menu.addSeparator();
            menu.add(exitItem);

            TrayIcon trayIcon = new TrayIcon(icon, "Biestro Print Bridge (port " + port + ")", menu);
            trayIcon.setImageAutoSize(true);
            tray.add(trayIcon);
        } catch (AWTException e) {
            log("Gagal menampilkan system tray: " + e.getMessage());
        }
    }

    private static Image renderIcon() {
        BufferedImage img = new BufferedImage(16, 16, BufferedImage.TYPE_INT_ARGB);
        Graphics2D g = img.createGraphics();
        g.setColor(new Color(0x26, 0x49, 0x9a));
        g.fillOval(0, 0, 16, 16);
        g.setColor(Color.WHITE);
        g.fillOval(5, 5, 6, 6);
        g.dispose();
        return img;
    }
}
