<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;


class CustomReceiptPrinter
{
    private $printer;
    function __construct()
    {

        $this->printer = null;
    }


    public function init($connector_type, $connector_descriptor, $connector_port = 9100)
    {
        switch (strtolower($connector_type)) {
            case 'cups':
                $connector = new CupsPrintConnector($connector_descriptor);
                break;
            case 'windows':
                $connector = new WindowsPrintConnector($connector_descriptor);
                break;
            case 'network':
                $connector = new NetworkPrintConnector($connector_descriptor);
                break;
            default:
                $connector = new FilePrintConnector("php://stdout");
                break;
        }

        if ($connector) {
            // Load simple printer profile
            $profile = CapabilityProfile::load("default");
            // Connect to printer
            $this->printer = new Printer($connector, $profile);
        } else {
            throw new Exception('Invalid printer connector type. Accepted values are: cups');
        }
    }

    public function close()
    {
        if ($this->printer) {
            $this->printer->close();
        }
    }





    public function setTextSize($width = 1, $height = 1)
    {
        if ($this->printer) {
            $width = ($width >= 1 && $width <= 8) ? (int) $width : 1;
            $height = ($height >= 1 && $height <= 8) ? (int) $height : 1;
            $this->printer->setTextSize($width, $height);
        }
    }


    public function getPrintableHeader($left_text, $right_text, $is_double_width = false)
    {
        $cols_width = $is_double_width ? 8 : 16;

        return str_pad($left_text, $cols_width) . str_pad($right_text, $cols_width, ' ', STR_PAD_LEFT);
    }



    public function feed($feed = NULL)
    {
        $this->printer->feed($feed);
    }

    public function cut()
    {
        $this->printer->cut();
    }

    public function printDashedLine()
    {
        $line = '';

        for ($i = 0; $i < 32; $i++) {
            $line .= '-';
        }

        $this->printer->text($line);
    }
    public function printDashedLineDouble()
    {
        $line = '';

        for ($i = 0; $i < 32; $i++) {
            $line .= '=';
        }

        $this->printer->text($line);
    }

    public function printLogo($logo)
    {

        $image = EscposImage::load($logo, false);

        //$this->printer->feed();
        //$this->printer->bitImage($image);
        //$this->printer->feed();

    }


    public function printBill($data, $total)
    {
        if ($this->printer) {

            $this->printer->initialize();
            $this->printer->selectPrintMode();
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);


            // $this->printLogo();
            $this->printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $this->printer->feed(2);
            $this->printer->text(str_replace('\n', "\n", setting('header_receipt')));
            $this->printer->text("\n");
            $this->printer->selectPrintMode();
            $this->printer->selectPrintMode(Printer::MODE_FONT_A);
            $this->printer->text(str_replace('\n', "\n", setting('subheader_receipt')));
            $this->printer->feed(1);
            $this->printer->text("\nRECEIPT\n");
            $this->printer->feed(1);
            if ($data) {
                $this->printer->setJustification(Printer::JUSTIFY_LEFT);
                foreach ($data as $r) {
                    $item = $r->ticket_id ? $r->ticket->name : $r->ticketPackage->name;
                    $this->printer->text($this->addItem($item, $r->quantity, $r->price_each, $r->subtotal));
                }
                $this->printer->feed(1);
            }

            $this->printer->text($this->getPrintableSummary('Total', $total));
            $this->printer->feed(2);

            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text(str_replace('\n', "\n", setting('footer_receipt')));
            $this->printer->selectPrintMode();
            $this->printer->feed(4);
            $this->printer->cut();
            $this->printer->close();
        } else {
            throw new Exception('Printer has not been initialized.');
        }
    }
    public function printTicket($data)
    {
        // dd(setting());
        if ($this->printer) {
            $this->printer->initialize();
            $this->printer->feed(2);
            $this->printer->selectPrintMode();
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $this->printer->text(str_replace('\n', "\n", setting('header_ticket')));
            $this->printer->text("\n");
            $this->printer->selectPrintMode();
            $this->printer->selectPrintMode(Printer::MODE_FONT_A);
            $this->printer->text(str_replace('\n', "\n", setting('subheader_ticket')));
            $this->printer->feed(1);
            $this->printer->text($this->printDashedLine());

            $this->printer->selectPrintMode();
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH + Printer::MODE_DOUBLE_HEIGHT + Printer::MODE_EMPHASIZED);
            $this->printer->text("\n" . $data->wahana->name . "\n");
            $this->printer->selectPrintMode();
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH + Printer::MODE_DOUBLE_HEIGHT + Printer::MODE_EMPHASIZED);
            $this->printer->text("───────\n");
            $this->printer->text($data->wahana->key . "\n");
            $this->printer->text("───────\n");
            $this->printer->feed(1); // Beri jarak bawah
            $this->printer->selectPrintMode();
            $this->printer->text($data->ticket_code . "\n");
            $this->printer->selectPrintMode(Printer::MODE_FONT_B);
            $this->printer->text($data->created_at);
            $this->printer->feed(1);
            $this->printer->selectPrintMode();
            $this->printer->text($this->printDashedLine());
            $this->printer->text("\n");
            $this->printer->selectPrintMode();
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text(str_replace('\n', "\n", setting('footer_ticket')));

            $this->printer->feed(2);
            $this->printer->cut();
            $this->printer->close();
        } else {
            throw new Exception('Printer has not been initialized.');
        }
    }

    public function printTicketAll($tickets)
    {
        if ($this->printer) {

            $this->printer->initialize();

            foreach ($tickets as $data) {
                $this->printer->feed(2);
                $this->printer->selectPrintMode();
                $this->printer->setJustification(Printer::JUSTIFY_CENTER);
                $this->printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
                $this->printer->text(str_replace('\n', "\n", setting('header_ticket')));
                $this->printer->text("\n");
                $this->printer->selectPrintMode();
                $this->printer->selectPrintMode(Printer::MODE_FONT_A);
                $this->printer->text(str_replace('\n', "\n", setting('subheader_ticket')));
                $this->printer->feed(1);
                $this->printer->text($this->printDashedLine());
                $this->printer->feed(1);
                $this->printer->setJustification(Printer::JUSTIFY_CENTER);
                $this->printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH + Printer::MODE_DOUBLE_HEIGHT + Printer::MODE_EMPHASIZED);
                $this->printer->text("\n" . $data->wahana->name . "\n");
                $this->printer->selectPrintMode();

                $this->printer->setJustification(Printer::JUSTIFY_CENTER);
                $this->printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH + Printer::MODE_DOUBLE_HEIGHT + Printer::MODE_EMPHASIZED);
                $this->printer->text("───────\n");
                $this->printer->text($data->wahana->key . "\n");
                $this->printer->text("───────\n");
                $this->printer->feed(1); // Beri jarak bawah
                $this->printer->selectPrintMode();
                $this->printer->text($data->ticket_code . "\n");
                $this->printer->selectPrintMode(Printer::MODE_FONT_B);
                $this->printer->text($data->created_at);
                $this->printer->feed(1);
                $this->printer->selectPrintMode();
                $this->printer->text($this->printDashedLine());
                $this->printer->text("\n");
                $this->printer->selectPrintMode();
                $this->printer->setJustification(Printer::JUSTIFY_CENTER);
                $this->printer->text(str_replace('\n', "\n", setting('footer_ticket')));
                $this->printer->selectPrintMode();
                $this->printer->feed(2);
                $this->printer->cut();
            }

            $this->printer->close();
        } else {
            throw new Exception('Printer has not been initialized.');
        }
    }

    public function addItem($item, $qty, $price, $subtotal)
    {
        $right_cols = 16;
        $left_cols = 16;

        $item_price = 'Rp.' . number_format($price, 0, ',', '.');
        $item_subtotal = 'Rp.' . number_format($subtotal, 0, ',', '.');

        $print_name = str_pad($item, 20);
        $print_priceqty = str_pad($item_price . ' x ' . $qty, $left_cols);
        $print_subtotal = str_pad($item_subtotal, $right_cols, ' ', STR_PAD_LEFT);

        return "$print_name\n$print_priceqty$print_subtotal\n";
    }
    public function getPrintableSummary($label, $value, $is_double_width = false)
    {
        $left_cols = $is_double_width ? 6 : 12;
        $right_cols = $is_double_width ? 10 : 20;

        $formatted_value = 'Rp.' . number_format($value, 0, ',', '.');

        return str_pad($label, $left_cols) . str_pad($formatted_value, $right_cols, ' ', STR_PAD_LEFT);
    }
}
