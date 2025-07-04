<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\IssuedTicket;
use Illuminate\Http\Request;
use App\Models\TicketPackage;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {



        $ticket = Ticket::where('is_active', '1')
            ->get()
            ->map(function ($item) {
                $array = [
                    'type' => 'ticket',
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                ];
                return $array;
            });

        $ticketPackage = TicketPackage::where('is_active', '1')
            ->get()
            ->map(function ($item) {
                $array = [
                    'type' => 'package',
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'detail' => $item->wahanas->map(function ($wahana) {
                        return [
                            'name' => $wahana->name,
                            'qty' => $wahana->pivot->qty,
                        ];
                    })->toArray(),
                ];
                return $array;
            });

        $combined = $ticket->concat($ticketPackage);



        return view('transaction.index', compact('combined'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'payment_type' => 'required|in:cash,noncash',
            'total' => 'required|numeric',
            'order_list' => 'required|string',
            'amount_given' => 'nullable|numeric',
            'noncash_method' => 'nullable|string',
            'bank' => 'nullable|string',
        ]);

        $orderList = json_decode($request->order_list, true);

        if (!is_array($orderList) || count($orderList) === 0) {
            return back()->with('error', 'Daftar order kosong.');
        }

        DB::beginTransaction();
        try {
            // Simpan transaksi utama
            $transaction = Transaction::create([
                'transaction_code' => uniqid(),
                'payment_status' => 'paid',
                'payment_type' => $request->payment_type,
                'amount_given' => $request->amount_given ?? 0,
                'noncash_method' => $request->noncash_method,
                'bank' => $request->bank,
                'total_amount' => $request->total,
                'paid_at' => now()
            ]);

            // Simpan detail order
            foreach ($orderList as $item) {
                $isPackage = $item['type'] === 'package';

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'ticket_id' => $isPackage ? null : $item['id'],
                    'ticket_package_id' => $isPackage ? $item['id'] : null,
                    'quantity' => $item['qty'],
                    'price_each' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);
            }

            DB::commit();

            $this->generateIssuedTickets($transaction);
            return back()->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }
    public function generateIssuedTickets($transaction)
    {
        foreach ($transaction->details as $detail) {
            if ($detail->type === 'ticket') {
                // Langsung buat tiket sebanyak qty
                for ($i = 0; $i < $detail->quantity; $i++) {
                    IssuedTicket::create([
                        'transaction_detail_id' => $detail->id,
                        'ticket_id' => $detail->ticket_id,
                        'ticket_package_id' => null,
                        'wahana_id' => null,
                        'ticket_code' => $this->generateUniqueTicketCode(),
                    ]);
                }
            }

            if ($detail->type === 'package') {
                // Ambil paket dan relasi wahananya
                $package = TicketPackage::with('wahanas')->find($detail->ticket_package_id);
                if (!$package) continue;

                foreach ($package->wahanas as $wahana) {
                    // Setiap wahana, buat tiket sebanyak qty
                    for ($i = 0; $i < $detail->quantity; $i++) {
                        IssuedTicket::create([
                            'transaction_detail_id' => $detail->id,
                            'ticket_id' => null,
                            'ticket_package_id' => $detail->ticket_package_id,
                            'wahana_id' => $wahana->pivot->qty ?? 1, // sesuaikan jumlah wahana
                            'ticket_code' => $this->generateUniqueTicketCode(),
                        ]);
                    }
                }
            }
        }
    }

    private function generateUniqueTicketCode()
    {
        do {
            $code = strtoupper('TIX-' . Str::random(10));
        } while (IssuedTicket::where('ticket_code', $code)->exists());

        return $code;
    }
}
