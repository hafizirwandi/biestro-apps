<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\Counter;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\CashierShift;
use App\Models\FreeGiftRule;
use App\Models\IssuedTicket;
use Illuminate\Http\Request;
use App\Models\TicketPackage;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use App\Models\DailyTicketSequence;
use App\Models\TransactionFreeGift;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use charlieuki\ReceiptPrinter\ReceiptPrinter;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index()
    {
        $id = auth()->user()->id;
        $shift = CashierShift::where('user_id', $id)
            ->whereDate('opened_at', Carbon::today())
            ->latest()
            ->first();

        if ($shift) {
            if ($shift->status !== 'open') {
                return redirect()->route('transaction.close');
            }
            Session::put('cashier_shift_id', $shift->id);
        } else {
            // belum ada shift hari ini → redirect untuk buka shift baru
            return redirect()->route('transaction.open-shift');
        }
        // dd(session()->all());
        $fg = FreeGiftRule::with('wahanas')
            ->where('is_active', 1)
            ->get()
            ->map(function ($item) {
                $arr = $item->toArray();
                $arr['min_purchase'] = (int) $item->min_purchase;

                // Ambil list wahana (misalnya hanya nama & id)
                $arr['wahanas'] = $item->wahanas->map(function ($w) {
                    return [
                        'id'   => $w->id,
                        'name' => $w->name,
                        'qty' => $w->pivot->qty // kalau ada
                    ];
                })->toArray();

                return $arr;
            })
            ->toArray();

        $draft = Transaction::where('payment_status', 'pending')->where('user_id', auth()->user()->id)->get()
            ->map(function ($item) {
                $arr = $item->toArray();
                $arr['total_item'] = count($item->details);
                $arr['created_at'] = $item->created_at->format('d-M-Y H:i');
                return $arr;
            })->toArray();


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



        return view('transaction.index', compact('combined', 'draft', 'fg', 'shift'));
    }
    public function checkFree($totalHarga)
    {
        $rules = FreeGiftRule::with('wahanas') // relasi ke wahanas
            ->where('is_active', 1)
            ->get();


        $result = [];
        $wahanaFree = [];
        foreach ($rules as $rule) {
            if ($totalHarga >= $rule->min_purchase) {
                // hitung multiple
                $multiplier = $rule->is_multiple
                    ? floor($totalHarga / $rule->min_purchase)
                    : 1;
                $gifts = [];

                foreach ($rule->wahanas as $wahana) {
                    $wahanaId = $wahana->id;
                    $gifts[] = [
                        'wahana_id' => $wahana->id,
                        'wahana'  => $wahana->name,
                        'qty' => $wahana->pivot->qty,

                    ];

                    if (isset($wahanaFree[$wahanaId])) {
                        // kalau sudah ada, tambahkan qty
                        $wahanaFree[$wahanaId]['qty'] += intval($wahana->pivot->qty * $multiplier);
                    } else {
                        $wahanaFree[$wahanaId] = [
                            'wahana_id' => $wahanaId,
                            'wahana'    => $wahana->name,
                            'qty'       => intval($wahana->pivot->qty * $multiplier),
                        ];
                    }
                }

                $result[] = [
                    'rule_id'   => $rule->id,
                    'rule_name' => $rule->name,
                    'multiple'  => $multiplier,
                    'gifts'     => $gifts,
                ];
            }
        }


        return response()->json([
            'total_harga' => $totalHarga,
            'free_gifts'  => $result,
            'wahana_free' => array_values($wahanaFree),
        ]);
    }
    public function getDetail($id)
    {
        $data = Transaction::findOrFail($id)->details()->get()->map(function ($item) {

            if (isset($item->ticket_id)) {
                $id = $item->ticket_id;
                $type = 'ticket';
                $name = $item->ticket->name;
            }
            if (isset($item->ticket_package_id)) {
                $id = $item->ticket_package_id;
                $type = 'package';
                $name = $item->ticketPackage->name;
            }
            $array = [
                'id' => (string)$id,
                'name' => $name,
                'type' => $type,
                'price' => (int)$item->price_each,
                'qty' => $item->quantity,
            ];
            return $array;
        });



        return response()->json($data);
    }
    public function store(Request $request)
    {


        // dd($request->input());

        if ($request->input('draft') == 'true') {
            $rules = [
                'total' => 'required|numeric',
                'order_list' => 'required|string',
                'free_rule' => 'nullable|string',
                'draft' => 'required',
                'transaction_id' => 'nullable'
            ];
            $payment_status = 'pending';
        } else {
            $rules = [
                'payment_type' => 'required|in:cash,noncash',
                'total' => 'required|numeric',
                'order_list' => 'required|string',
                'free_rule' => 'nullable|string',
                'amount_given' => 'nullable|numeric',
                'noncash_method' => 'nullable|string',
                'bank' => 'nullable|string',
            ];
            $payment_status = 'paid';
        }

        // Jalankan validasi manual
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator) // kirim error ke session
                ->withInput()            // biar value lama tetap terisi
                ->with('error', 'Data tidak valid, periksa kembali input Anda.');
        }



        $orderList = json_decode($request->order_list, true);
        $freeRule = json_decode($request->free_rule, true);

        if (!is_array($orderList) || count($orderList) === 0) {
            return back()->with('error', 'Daftar order kosong.');
        }

        DB::beginTransaction();
        try {

            $dataTransaction = [
                'transaction_code' => uniqid(),
                'payment_status' => $payment_status,
                'payment_type' => $request->payment_type ?? null,
                'amount_given' => $request->amount_given ?? null,
                'noncash_method' => $request->noncash_method ?? null,
                'bank' => $request->bank ?? null,
                'total_amount' => $request->total,
                'user_id' => auth()->user()->id,
                'cashier_shift_id' => session('cashier_shift_id'),
                'paid_at' => now()
            ];
            if ($request->input('transaction_id')) {
                $transaction = Transaction::findOrFail($request->input('transaction_id'));
                $transaction->update($dataTransaction);

                // hapus detail lama
                $transaction->details()->delete();

                $transaction->freeGifts()->delete();
            } else {
                $transaction = Transaction::create($dataTransaction);
            }

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

            foreach ($freeRule as $item) {
                TransactionFreeGift::create([
                    'transaction_id' => $transaction->id,
                    'free_gift_rule_id' => $item['rule_id'],
                    'quantity' => $item['multiple'],
                    'is_claim' => true,
                    'claimed_at' => now()
                ]);
            }


            DB::commit();
            if ($request->input('draft') == 'true') {
                return back()->with('success', 'Transaksi berhasil disimpan');
            } else {
                $this->generateIssuedTickets($transaction);
                DB::commit();
                return redirect()->route('transaction.view', $transaction->id)->with('success', 'Transaksi berhasil disimpan.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }
    public function view($id)
    {
        $data = Transaction::with(['details', 'freeGifts'])
            ->where('payment_status', 'paid')
            ->where('id', $id)
            ->firstOrFail();

        // Ambil semua ID dari transaction_details
        $detailIds = $data->details->pluck('id');

        // Ambil semua rule_id dari free_gifts (bukan id row tabel relasi)
        $ruleIds = $data->freeGifts->pluck('free_gift_rule_id');

        // Cari tiket berdasarkan detail_id ATAU free_gift_rule_id
        $ticket = IssuedTicket::where('transaction_id', $id)
            ->get();

        return view('transaction.view', compact('data', 'ticket'));
    }
    public function printBill(Request $request)
    {
        try {
            $transaction = Transaction::with('details')->where('id', $request->transaction_id)->where('payment_status', 'paid')->firstOrFail();


            // Init printer
            $printer = new CustomReceiptPrinter;
            // dd(setting());

            $connected = $printer->init(setting('connector_type'), setting('connector_descriptor'));

            // ✅ cek apakah berhasil konek
            // if ($connected === false || $connected === null) {
            //     throw new \Exception("Printer tidak tersambung atau mati.");
            // }

            $printer->printBill($transaction->details, $transaction->total_amount);


            return response()->json(['status' => 'success']);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function printTicket(Request $request)
    {
        try {
            $data = IssuedTicket::findOrFail($request->id);

            $printer = new CustomReceiptPrinter;
            $connected = $printer->init(setting('connector_type'), setting('connector_descriptor'));

            // ✅ cek apakah berhasil konek
            if ($connected === false || $connected === null) {
                // throw new \Exception("Printer tidak tersambung atau mati.");
            }

            $printer->printTicket($data);


            $data->count_print += 1;
            $data->last_printed_at = now();
            $data->save();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function printTicketAll(Request $request)
    {
        try {
            // $data = Transaction::where('payment_status', 'paid')->where('id', $request->transaction_id)->first();
            // $detailIds = $data->details->pluck('id'); // Ambil semua ID dari details
            // $tickets = IssuedTicket::whereIn('transaction_detail_id', $detailIds)->get();
            $tickets = IssuedTicket::where('transaction_id', $request->transaction_id)->get();
            // Init printer
            $printer = new CustomReceiptPrinter;
            $connected = $printer->init(setting('connector_type'), setting('connector_descriptor'));

            // if ($connected === false || $connected === null) {
            //     throw new \Exception("Printer tidak tersambung atau mati.");
            // }

            $printer->printTicketAll($tickets);

            foreach ($tickets as $r) {
                $r->count_print += 1;
                $r->last_printed_at = now();
                $r->save();
            }

            return response()->json(['status' => 'success']);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function generateIssuedTickets($transaction)
    {
        // dd($transaction->freeGifts);

        foreach ($transaction->freeGifts as $fg) {
            $wahanas = $fg->freeGiftRule->wahanas;
            foreach ($wahanas as $r) {
                $totalTickets = $fg->quantity * $r->pivot->qty;

                for ($k = 0; $k < $totalTickets; $k++) {
                    $ticketCode = $this->generateUniqueTicketCode($r->id);
                    IssuedTicket::create([
                        'transaction_id'     => $transaction->id,
                        'free_gift_rule_id'  => $fg->free_gift_rule_id,
                        'wahana_id'          => $r->id,
                        'ticket_code'        => $ticketCode,
                    ]);
                }
            }
        }

        foreach ($transaction->details as $detail) {
            if (isset($detail->ticket_id)) {
                for ($i = 0; $i < $detail->quantity; $i++) {

                    $ticketCode =  $this->generateUniqueTicketCode($detail->ticket->wahana_id);
                    IssuedTicket::create([
                        'transaction_id' => $transaction->id,
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
                                'transaction_id' => $transaction->id,
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

            $random1 = strtoupper(Str::random(4));
            $random2 = strtoupper(Str::random(4));
            $number = str_pad($sequence->last_number, 4, '0', STR_PAD_LEFT);

            return "BIE-{$random1}{$number}{$random2}";
        });
    }


    public function openShift()
    {
        $id = auth()->user()->id;
        $shift = CashierShift::where('user_id', $id)
            ->whereDate('opened_at', now()->toDateString())
            ->latest()
            ->first();

        if ($shift) {
            if ($shift->status === 'open') {
                // simpan id shift ke session (biar bisa dipakai di transaksi/close shift)
                session(['cashier_shift_id' => $shift->id]);

                return redirect()->route('transaction')->with('error', 'Shift anda sudah terbuat');
            }

            // Kalau sudah ada shift tapi status closed → larang
            abort(403, 'Shift hari ini sudah ditutup.');
        }

        $counter = Counter::where('is_active', 1)->get();
        return view('transaction.open-shift', compact('counter'));
    }

    public function closeShift()
    {
        $shiftId = session('cashier_shift_id');

        $transaction = Transaction::where('cashier_shift_id', $shiftId)->where('payment_status', 'paid')->get();
        $totalCash = $transaction->where('payment_type', 'cash')->sum('total_amount');


        if (!$shiftId) {
            return redirect()->route('transaction.open-shift')
                ->with('error', 'Shift anda belum terbuat, silahkan open shift untuk memulai transaksi');
        }

        $shift = CashierShift::find($shiftId);

        if (!$shift) {
            return redirect()->route('transaction.open-shift')
                ->with('error', 'Shift tidak ditemukan.');
        }

        $system_balance = $shift->opening_balance + $totalCash;

        $counter = Counter::where('is_active', 1)->get();

        return view('transaction.close-shift', compact('counter', 'shift', 'system_balance'));
    }

    public function setOpenShift(Request $request)
    {
        try {
            $rules = [
                'counter_id' => 'required|exists:counters,id',
                'opening_balance' => 'required|numeric|min:0',
            ];


            $data = $request->validate($rules);

            $data['user_id'] = auth()->user()->id;
            $data['opened_at'] = now();
            CashierShift::create($data);
            $msg = 'Cashier Shift saved successfully';

            return redirect()->route('transaction')->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    public function setCloseShift(Request $request)
    {


        try {
            $rules = [
                'cashier_shift_id' => 'required|exists:cashier_shifts,id',
                'closing_balance' => 'required',
                'system_balance' => 'required',
                'difference' => 'required',
                'notes' => 'nullable',
            ];

            $data = $request->validate($rules);



            $shift = CashierShift::findOrFail($data['cashier_shift_id']);


            // Pastikan shift masih open
            if ($shift->closed_at) {
                return back()->with('error', 'Shift ini sudah ditutup sebelumnya.');
            }


            $closing_balance = preg_replace('/[^\d]/', '', $request->closing_balance);
            $system_balance  = preg_replace('/[^\d]/', '', $request->system_balance);
            $difference      = preg_replace('/[^\d\-]/', '', $request->difference);

            $shift->closing_balance = (int) $closing_balance;
            $shift->system_balance  = (int) $system_balance;
            $shift->difference      = (int) $difference;
            $shift->notes            = $request->notes;
            $shift->closed_at = now();
            $shift->status = 'closed'; // kalau ada field status
            $shift->save();


            $msg = 'Cashier Shift closed successfully';
            return redirect()->route('transaction')->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    public function salesRevenue()
    {
        $shiftId = session('cashier_shift_id');
        $shift = CashierShift::find($shiftId);
        $transaction = Transaction::where('cashier_shift_id', $shiftId)->where('payment_status', 'paid')->get();
        return view('transaction.sales-revenue', compact('transaction', 'shift'));
    }
    public function close()
    {

        return view('transaction.close');
    }

    public function reprintReceipt()
    {
        $shiftId = session('cashier_shift_id');
        $shift = CashierShift::find($shiftId);
        $transaction = Transaction::where('cashier_shift_id', $shiftId)->where('payment_status', 'paid')->get();
        return view('transaction.reprint-receipt', compact('transaction', 'shift'));
    }
    public function reprint(Request $request)
    {
        try {
            $rules = [
                'id' => 'required|exists:transactions,id',
                'spv_code' => 'required',
            ];

            $spv_code = setting('spv_approve');
            $data = $request->validate($rules);

            if ($spv_code !== $data['spv_code']) {
                throw new \Exception('Password SPV wrong!');
            }

            // reset count_print semua tiket dari transaksi terkait
            IssuedTicket::where('transaction_id', $data['id'])
                ->update(['count_print' => 0]);

            return back()->with('success', 'Tickets berhasil di-reset dan siap di reprint');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    public function deleteDraftTransaction($id)
    {
        try {
            $data = Transaction::where('id', $id)
                ->where('payment_status', 'pending')
                ->first();

            if (!$data) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Data not found!',
                ], 404); // pakai 404
            }

            $data->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data deleted successfully',
            ], 200); // pakai 200
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500); // ini oke untuk error tak terduga
        }
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
