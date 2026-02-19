<?php

namespace App\Http\Controllers;

use App\Models\IssuedTicket;
use App\Models\Transaction;
use Illuminate\Http\Request;

class PrinterTestController extends Controller
{
    /**
     * Show the printer test page.
     */
    public function index()
    {
        return view('printer.test');
    }

    /**
     * Return dummy receipt data as JSON for browser-side printing test.
     */
    public function getReceiptData()
    {
        return response()->json([
            'header' => setting('header_receipt') ?? 'BIESTRO APPS',
            'subheader' => setting('subheader_receipt') ?? 'Jl. Contoh No. 1',
            'footer' => setting('footer_receipt') ?? 'Terima Kasih!',
            'type' => 'receipt',
            'items' => [['name' => 'Ticket Wahana A', 'qty' => 2, 'price' => 25000, 'subtotal' => 50000], ['name' => 'Paket Keluarga', 'qty' => 1, 'price' => 80000, 'subtotal' => 80000]],
            'total' => 130000,
            'payment_type' => 'cash',
        ]);
    }

    /**
     * Return real transaction bill data as JSON (for browser-side printing).
     */
    public function getTransactionData($id)
    {
        $transaction = Transaction::with(['details.ticket', 'details.ticketPackage'])->findOrFail($id);

        $items = $transaction->details->map(
            fn($d) => [
                'name' => $d->ticket_id ? $d->ticket->name : $d->ticketPackage?->name ?? '-',
                'qty' => $d->quantity,
                'price' => $d->price_each,
                'subtotal' => $d->subtotal,
            ],
        );

        return response()->json([
            'header' => setting('header_receipt') ?? 'BIESTRO APPS',
            'subheader' => setting('subheader_receipt') ?? '',
            'footer' => setting('footer_receipt') ?? 'Terima Kasih!',
            'type' => 'receipt',
            'code' => $transaction->transaction_code,
            'paid_at' => $transaction->paid_at,
            'payment_type' => $transaction->payment_type,
            'items' => $items,
            'total' => $transaction->total_amount,
        ]);
    }

    /**
     * Return a single issued ticket data as JSON (for browser-side printing).
     */
    public function getTicketData($id)
    {
        $ticket = IssuedTicket::with('wahana')->findOrFail($id);

        return response()->json([
            'header' => setting('header_ticket') ?? 'BIESTRO APPS',
            'subheader' => setting('subheader_ticket') ?? '',
            'footer' => setting('footer_ticket') ?? 'Terima Kasih!',
            'wahana_name' => $ticket->wahana?->name ?? '',
            'wahana_key' => $ticket->wahana?->key ?? '',
            'ticket_code' => $ticket->ticket_code,
            'created_at' => $ticket->created_at?->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Return all issued tickets for a transaction as JSON (for browser-side printing).
     */
    public function getTicketsData($transactionId)
    {
        $tickets = IssuedTicket::with('wahana')->where('transaction_id', $transactionId)->get();

        $header = setting('header_ticket') ?? 'BIESTRO APPS';
        $subheader = setting('subheader_ticket') ?? '';
        $footer = setting('footer_ticket') ?? 'Terima Kasih!';

        $data = $tickets->map(
            fn($t) => [
                'id' => $t->id,
                'header' => $header,
                'subheader' => $subheader,
                'footer' => $footer,
                'wahana_name' => $t->wahana?->name ?? '',
                'wahana_key' => $t->wahana?->key ?? '',
                'ticket_code' => $t->ticket_code,
                'created_at' => $t->created_at?->format('d/m/Y H:i'),
                'count_print' => $t->count_print,
            ],
        );

        return response()->json(['tickets' => $data]);
    }
}
