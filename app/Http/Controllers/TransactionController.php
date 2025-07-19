<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\IssuedTicket;
use Illuminate\Http\Request;
use App\Models\TicketPackage;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use App\Models\DailyTicketSequence;
use charlieuki\ReceiptPrinter\ReceiptPrinter;
use Exception;

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
            $this->generateIssuedTickets($transaction);
            DB::commit();
            return redirect()->route('transaction.view', $transaction->id)->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }
    public function view($id)
    {
        $data = Transaction::where('payment_status', 'paid')->where('id', $id)->first();
        $detailIds = $data->details->pluck('id'); // Ambil semua ID dari details
        $ticket = IssuedTicket::whereIn('transaction_detail_id', $detailIds)->get();
        // dd($ticket);
        return view('transaction.view', compact('data', 'ticket'));
    }
    public function printBill(Request $request)
    {
        try {
            $transaction = Transaction::with('details')->where('id', $request->transaction_id)->where('payment_status', 'paid')->firstOrFail();


            // Init printer
            $printer = new CustomReceiptPrinter;
            $printer->init(setting('connector_type'), setting('connector_descriptor'));
            $printer->printBill($transaction->details, $transaction->total_amount);


            return response()->json(['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error']);
        }
    }
    public function printTicket(Request $request)
    {
        try {


            $data = IssuedTicket::findOrFail($request->id);



            // Init printer
            $printer = new CustomReceiptPrinter;
            $printer->init(setting('connector_type'), setting('connector_descriptor'));
            $printer->printTicket($data);


            return response()->json(['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error']);
        }
    }
    public function printTicketAll(Request $request)
    {
        try {
            $data = Transaction::where('payment_status', 'paid')->where('id', $request->transaction_id)->first();
            $detailIds = $data->details->pluck('id'); // Ambil semua ID dari details
            $tickets = IssuedTicket::whereIn('transaction_detail_id', $detailIds)->get();
            // Init printer
            $printer = new CustomReceiptPrinter;
            $printer->init(setting('connector_type'), setting('connector_descriptor'));
            $printer->printTicketAll($tickets);

            return response()->json(['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error']);
        }
    }
    public function generateIssuedTickets($transaction)
    {
        foreach ($transaction->details as $detail) {
            if (isset($detail->ticket_id)) {
                for ($i = 0; $i < $detail->quantity; $i++) {

                    $ticketCode =  $this->generateUniqueTicketCode($detail->ticket->wahana_id);
                    IssuedTicket::create([
                        'transaction_detail_id' => $detail->id,
                        'ticket_id' => $detail->ticket_id,
                        'ticket_package_id' => null,
                        'wahana_id' => $detail->ticket->wahana_id,
                        'ticket_code' => $ticketCode,
                    ]);
                }
            }

            if (isset($detail->ticket_package_id)) {
                // Ambil paket dan relasi wahananya
                $package = TicketPackage::with('wahanas')->find($detail->ticket_package_id);
                if (!$package) continue;

                foreach ($package->wahanas as $wahana) {

                    // Setiap wahana, buat tiket sebanyak qty
                    for ($i = 0; $i < $detail->quantity; $i++) {
                        for ($j = 0; $j < $wahana->pivot->qty; $j++) {
                            $ticketCode =  $this->generateUniqueTicketCode($wahana->id);
                            IssuedTicket::create([
                                'transaction_detail_id' => $detail->id,
                                'ticket_id' => null,
                                'ticket_package_id' => $detail->ticket_package_id,
                                'wahana_id' => $wahana->id,
                                'ticket_code' => $ticketCode,
                            ]);
                        }
                    }
                }
            }
        }
    }

    private function generateUniqueTicketCode($wahanaId)
    {
        $today = Carbon::today()->toDateString(); // '2025-07-10'

        return DB::transaction(function () use ($wahanaId, $today) {
            // Lock baris kombinasi wahana_id + tanggal untuk hindari race condition
            $sequence = DailyTicketSequence::where('wahana_id', $wahanaId)
                ->where('date', $today)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                $sequence = DailyTicketSequence::create([
                    'wahana_id' => $wahanaId,
                    'date' => $today,
                    'last_number' => 1,
                ]);
            } else {
                $sequence->last_number += 1;
                $sequence->save();
            }

            $random = strtoupper(Str::random(10)); // 10 huruf random
            $number = str_pad($sequence->last_number, 4, '0', STR_PAD_LEFT);

            return "BIE-{$random}-{$number}";
        });
    }


    // public function printUsb(Request $request)
    // {
    //     $sale_id = $request->input('sale_id');
    //     $q = Sale::findOrFail($sale_id);


    //     $user = Auth::user();
    //     $store_id = $user->store_id;
    //     $s = Store::find($store_id);
    //     // Set params
    //     $params = array(
    //         'nama' => $s->nama,
    //         'alamat' => $s->alamat,
    //         'nota' => $q->nota,
    //         'subtotal' => $q->total,
    //         'diskon' => $q->diskon,
    //         'grandtotal' => $q->grandtotal,
    //         'footer' => "Thank you for shopping!\n",
    //     );

    //     // Init printer
    //     $printer = new ReceiptPrinter;
    //     $printer->init(
    //         config('receiptprinter.connector_type'),
    //         config('receiptprinter.connector_descriptor')
    //     );


    //     // Print receipt
    //     $printer->printReceipt($params, $q->detailItem);
    // }
    // public function printBt($id)
    // {
    //     $q = Sale::findOrFail($id);


    //     $user = Auth::user();
    //     $store_id = $user->store_id;
    //     $s = Store::find($store_id);
    //     // Set params
    //     $params = array(
    //         'nama' => $s->nama,
    //         'alamat' => $s->alamat,
    //         'nota' => $q->nota,
    //         'subtotal' => $q->total,
    //         'diskon' => $q->diskon,
    //         'grandtotal' => $q->grandtotal,
    //         'footer' => "Thank you for shopping!",
    //     );

    //     // Init printer
    //     $printer = new ReceiptPrinter;
    //     $printer->init(
    //         config('receiptprinter.connector_type'),
    //         config('receiptprinter.connector_descriptor')
    //     );

    //     $html = $printer->printReceiptHtml($params, $q->detailItem);
    //     $data['printContent'] = $html;
    //     return view('sale.print-bt', $data);
    // }
}
