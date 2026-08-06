# Biestro Print Bridge

Service kecil yang jalan di komputer kasir (Windows), menerima data tiket/struk
mentah (ESC/POS bytes, yang sudah disusun oleh halaman web di
`public/js/bluetooth-printer.js`) lewat HTTP lokal, lalu meneruskannya ke
printer USB yang terpasang — lewat print spooler Windows (`javax.print`),
bukan lewat Web Bluetooth.

Kenapa ini ada: cetak lewat Web Bluetooth (BLE) tidak reliable untuk banyak
tiket sekaligus (paket data bisa didrop diam-diam oleh printer tanpa
pemberitahuan), dan browser juga mensyaratkan HTTPS untuk Web Bluetooth,
padahal aplikasi ini diakses lewat IP LAN biasa. Bridge ini menghilangkan
kedua masalah itu sekaligus: `http://127.0.0.1` (loopback ke komputer
sendiri) tidak butuh HTTPS, dan print spooler Windows punya flow control
asli sehingga tidak ada lagi tiket yang "hilang" di tengah jalan.

Bridge ini **tidak** menyusun tampilan tiket — itu tetap tugas JS di
aplikasi web (satu sumber format, tidak dobel). Bridge cuma pipa byte mentah
ke printer.

## Kebutuhan

- Java Runtime (JRE) 8 atau lebih baru sudah terpasang di komputer kasir.
- Printer USB sudah terpasang & terlihat di **Windows > Devices and Printers**.

## Setup printer (WAJIB, sekali per printer)

Printer thermal biasanya harus di-set supaya menerima data **RAW** (byte
ESC/POS dikirim apa adanya), bukan diinterpretasikan sebagai dokumen teks:

1. Buka **Devices and Printers** → klik kanan printer → **Printer properties**.
2. Tab **Advanced** → pastikan driver yang dipakai adalah driver bawaan
   printer atau **"Generic / Text Only"**.
3. Tab **Ports** (atau lewat "Print Processor..." di Advanced) → pastikan
   opsi semacam **"Print directly to the printer"** aktif, dan datatype yang
   dipakai adalah **RAW**, bukan EMF/text-formatted.

Kalau langkah ini terlewat, tiket bisa tercetak sebagai karakter aneh/acak
karena Windows mencoba "merapikan" data yang sebenarnya sudah berupa command
ESC/POS mentah.

## Konfigurasi

1. Salin `config.properties.example` menjadi `config.properties` (folder yang
   sama dengan `printer-bridge.jar`).
2. Isi `printer=` dengan nama printer **persis** seperti di Devices and
   Printers — **tanpa tanda kutip** (`printer=POS58`, bukan `printer="POS58"`,
   ini format Java properties biasa).  Kosongkan untuk pakai printer default
   Windows.
3. `port=9100` — ubah kalau bentrok dengan aplikasi lain, dan samakan dengan
   **Pengaturan > Print Bridge URL** di aplikasi Biestro (defaultnya
   `http://127.0.0.1:9100`).

## Menjalankan (Windows — kasir)

- `run.bat` — start di background tanpa jendela konsol (`javaw`), PID
  disimpan ke `printer-bridge.pid`. Aman diklik berkali-kali (deteksi kalau
  sudah jalan, tidak dobel-start).
- `stop.bat` — hentikan proses yang disimpan di `printer-bridge.pid`.

Untuk auto-jalan tiap komputer nyala/login:

1. Tekan `Win+R`, ketik `shell:startup`, Enter.
2. Buat shortcut ke `run.bat` di folder itu.

(Skrip `.bat` ini ditulis & ditinjau logikanya dengan teliti, tapi dites
end-to-end di macOS/Linux lewat `run.sh`/`stop.sh` — silakan pastikan
`run.bat`/`stop.bat` jalan benar sekali di PC kasir Windows yang sesungguhnya
sebelum dipasang ke semua kasir.)

Untuk lihat status/keluar, cari icon biru kecil di system tray (pojok kanan
bawah, dekat jam) — klik kanan untuk menu "Keluar".

## Menjalankan (macOS/Linux — untuk development/testing)

```
./run.sh    # start di background, simpan PID ke printer-bridge.pid
./stop.sh   # hentikan proses yang dijalankan run.sh
```

`run.sh` aman dipanggil berkali-kali (deteksi kalau sudah jalan, tidak
dobel-start). Log proses tersimpan di `printer-bridge.out.log`.

## Test manual

Dengan bridge sedang jalan, dari komputer yang sama buka:

```
http://127.0.0.1:9100/health
```

(Catatan: `http://127.0.0.1:9100` tanpa `/health` di belakang tidak akan
menampilkan apa-apa — cuma `/health` dan `/print` yang terdaftar.)

Harus muncul JSON berisi daftar printer yang terdaftar di Windows. Kalau
sudah tampil dan nama printer yang dikonfigurasi ada di situ, aplikasi web
siap dipakai dengan mode cetak "bridge" (atur di Pengaturan aplikasi).

Log aktivitas cetak (sukses/gagal) tersimpan di `printer-bridge.log` di
folder yang sama, kalau perlu ditelusuri.

## Build ulang dari source (kalau perlu ubah kode)

Tidak butuh Maven — cukup JDK biasa:

```
cd printer-bridge
javac -d out src/PrintBridge.java
cd out
jar cfe ../printer-bridge.jar PrintBridge *.class
cd ..
```

Hasilnya: `printer-bridge.jar` siap dipakai ulang dengan `run.bat`.
