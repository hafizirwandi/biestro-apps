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

use Illuminate\Support\Facades\Cache;

class TransactionController extends Controller
{
    public function index()
    {
        $id = auth()->user()->id;
        $shift = CashierShift::where('user_id', $id)->whereDate('opened_at', Carbon::today())->latest()->first();

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
                $arr['wahanas'] = $item->wahanas
                    ->map(function ($w) {
                        return [
                            'id' => $w->id,
                            'name' => $w->name,
                            'qty' => $w->pivot->qty, // kalau ada
                        ];
                    })
                    ->toArray();

                return $arr;
            })
            ->toArray();

        $draft = Transaction::where('payment_status', 'pending')
            ->where('user_id', auth()->user()->id)
            ->get()
            ->map(function ($item) {
                $arr = $item->toArray();
                $arr['total_item'] = count($item->details);
                $arr['created_at'] = $item->created_at->format('d-M-Y H:i');
                return $arr;
            })
            ->toArray();

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
                    'detail' => $item->wahanas
                        ->map(function ($wahana) {
                            return [
                                'name' => $wahana->name,
                                'qty' => $wahana->pivot->qty,
                            ];
                        })
                        ->toArray(),
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
                $multiplier = $rule->is_multiple ? floor($totalHarga / $rule->min_purchase) : 1;
                $gifts = [];

                foreach ($rule->wahanas as $wahana) {
                    $wahanaId = $wahana->id;
                    $gifts[] = [
                        'wahana_id' => $wahana->id,
                        'wahana' => $wahana->name,
                        'qty' => $wahana->pivot->qty,
                    ];

                    if (isset($wahanaFree[$wahanaId])) {
                        // kalau sudah ada, tambahkan qty
                        $wahanaFree[$wahanaId]['qty'] += intval($wahana->pivot->qty * $multiplier);
                    } else {
                        $wahanaFree[$wahanaId] = [
                            'wahana_id' => $wahanaId,
                            'wahana' => $wahana->name,
                            'qty' => intval($wahana->pivot->qty * $multiplier),
                        ];
                    }
                }

                $result[] = [
                    'rule_id' => $rule->id,
                    'rule_name' => $rule->name,
                    'multiple' => $multiplier,
                    'gifts' => $gifts,
                ];
            }
        }

        return response()->json([
            'total_harga' => $totalHarga,
            'free_gifts' => $result,
            'wahana_free' => array_values($wahanaFree),
        ]);
    }
    public function getDetail($id)
    {
        $data = Transaction::findOrFail($id)
            ->details()
            ->get()
            ->map(function ($item) {
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
                    'id' => (string) $id,
                    'name' => $name,
                    'type' => $type,
                    'price' => (int) $item->price_each,
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
                'transaction_id' => 'nullable',
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
                'noncash_channel' => 'nullable|string',
            ];
            $payment_status = 'paid';
        }

        // Jalankan validasi manual
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator) // kirim error ke session
                ->withInput() // biar value lama tetap terisi
                ->with('error', 'Data tidak valid, periksa kembali input Anda.');
        }

        $orderList = json_decode($request->order_list, true);
        $freeRule = json_decode($request->free_rule, true);

        if (!is_array($orderList) || count($orderList) === 0) {
            return back()->with('error', 'Daftar order kosong.');
        }

        // --- LAPIS 2: Backend Atomic Lock ---
        // Kunci berdasarkan User ID (atau kombinasi shift ID)
        // Format key: 'transaction_lock_USERID'
        $lockKey = 'transaction_lock_' . auth()->user()->id;

        // Coba dapatkan lock selama 5 detik
        $lock = Cache::lock($lockKey, 5);

        if (!$lock->get()) {
            // Jika lock sedang aktif (user sudah submit < 5 detik lalu)
            // Langsung tolak request
            return back()->with('error', 'Transaksi sedang diproses. Mohon tunggu sebentar.');
        }

        DB::beginTransaction();
        try {
            $dataTransaction = [
                'transaction_code' => uniqid(),
                'payment_status' => $payment_status,
                'payment_type' => $request->payment_type ?? null,
                'amount_given' => $request->amount_given ?? null,
                'noncash_method' => $request->noncash_method ?? null,
                'noncash_channel' => $request->noncash_channel ?? null,
                'total_amount' => $request->total,
                'user_id' => auth()->user()->id,
                'cashier_shift_id' => session('cashier_shift_id'),
                'paid_at' => now(),
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
                    'claimed_at' => now(),
                ]);
            }
            DB::commit();
            $lock->release(); // Lepaskan lock jika berhasil

            if ($request->input('draft') == 'true') {
                // Draft: always redirect back (form submit)
                return back()->with('success', 'Transaksi berhasil disimpan');
            } else {
                $this->generateIssuedTickets($transaction);
                DB::commit();

                // AJAX request from POS index page → return JSON so page doesn't reload
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['status' => 'ok', 'id' => $transaction->id]);
                }

                // Normal (direct) form submit → redirect as before
                return redirect()->route('transaction.view', $transaction->id)->with('success', 'Transaksi berhasil disimpan.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $lock->release(); // Lepaskan lock jika gagal

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    // Render the view partial for AJAX modal injection (no layout)
    public function viewPartial($id)
    {
        $data = Transaction::with(['details', 'freeGifts'])
            ->where('payment_status', 'paid')
            ->where('id', $id)
            ->firstOrFail();

        $ticket = IssuedTicket::where('transaction_id', $id)->get();

        return view('transaction.view-partial', compact('data', 'ticket'));
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
        $ticket = IssuedTicket::where('transaction_id', $id)->get();

        return view('transaction.view', compact('data', 'ticket'));
    }
    public function printBill(Request $request)
    {
        try {
            $transaction = Transaction::with('details')->where('id', $request->transaction_id)->where('payment_status', 'paid')->firstOrFail();

            // Skip server-side print when called from Web Bluetooth (browser already printed)
            if (!$request->input('_bt')) {
                $printer = new CustomReceiptPrinter();
                $printer->init(setting('connector_type'), setting('connector_descriptor'));
                $printer->printBill($transaction->details, $transaction->total_amount);
            }

            $transaction->bill_count_print += 1;
            $transaction->save();

            return response()->json(['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }
    public function printTicket(Request $request)
    {
        try {
            $data = IssuedTicket::findOrFail($request->id);

            // Skip server-side print when called from Web Bluetooth
            if (!$request->input('_bt')) {
                $printer = new CustomReceiptPrinter();
                $printer->init(setting('connector_type'), setting('connector_descriptor'));
                $printer->printTicket($data);
            }

            $data->count_print += 1;
            $data->last_printed_at = now();
            $data->save();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }
    public function printTicketAll(Request $request)
    {
        try {
            $tickets = IssuedTicket::where('transaction_id', $request->transaction_id)->get();

            // Skip server-side print when called from Web Bluetooth
            if (!$request->input('_bt')) {
                $printer = new CustomReceiptPrinter();
                $printer->init(setting('connector_type'), setting('connector_descriptor'));
                $printer->printTicketAll($tickets);
            } elseif ($request->filled('ticket_ids')) {
                // Web Bluetooth path reports exactly which tickets actually made it
                // out of the printer — only flag those, not the whole transaction,
                // so tickets that failed to print stay reprintable.
                $printedIds = collect($request->input('ticket_ids'))->map(fn($id) => (int) $id);
                $tickets = $tickets->whereIn('id', $printedIds);
            }

            foreach ($tickets as $r) {
                $r->count_print += 1;
                $r->last_printed_at = now();
                $r->save();
            }

            return response()->json(['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ],
                500,
            );
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
                        'transaction_id' => $transaction->id,
                        'free_gift_rule_id' => $fg->free_gift_rule_id,
                        'wahana_id' => $r->id,
                        'ticket_code' => $ticketCode,
                    ]);
                }
            }
        }

        foreach ($transaction->details as $detail) {
            if (isset($detail->ticket_id)) {
                for ($i = 0; $i < $detail->quantity; $i++) {
                    $ticketCode = $this->generateUniqueTicketCode($detail->ticket->wahana_id);
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
                if (!$package) {
                    continue;
                }

                foreach ($package->wahanas as $wahana) {
                    // Setiap wahana, buat tiket sebanyak qty
                    for ($i = 0; $i < $detail->quantity; $i++) {
                        for ($j = 0; $j < $wahana->pivot->qty; $j++) {
                            $ticketCode = $this->generateUniqueTicketCode($wahana->id);
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
            $sequence = DailyTicketSequence::where('wahana_id', $wahanaId)->where('date', $today)->lockForUpdate()->first();

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

        if (!$shiftId) {
            return redirect()->route('transaction.open-shift')->with('error', 'Shift anda belum terbuat, silahkan open shift untuk memulai transaksi');
        }

        $shift = CashierShift::find($shiftId);

        if (!$shift) {
            return redirect()->route('transaction.open-shift')->with('error', 'Shift tidak ditemukan.');
        }

        $transaction = Transaction::where('cashier_shift_id', $shiftId)->where('payment_status', 'paid')->get();
        $totalCash = $transaction->where('payment_type', 'cash')->sum('total_amount');
        $totalNonCash = $transaction->where('payment_type', 'noncash')->sum('total_amount');
        $grandTotal = $transaction->sum('total_amount');

        $system_balance = $shift->opening_balance + $totalCash;

        $byMethod = $transaction->where('payment_type', 'noncash')->groupBy('noncash_method')->map(fn($rows) => $rows->sum('total_amount'))->toArray();
        $byChannel = $transaction->where('payment_type', 'noncash')->groupBy('noncash_channel')->map(fn($rows) => $rows->sum('total_amount'))->toArray();

        $counter = Counter::where('is_active', 1)->get();

        return view('transaction.close-shift', compact('counter', 'shift', 'system_balance', 'totalCash', 'totalNonCash', 'grandTotal', 'byMethod', 'byChannel'));
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
        $isAjax = $request->ajax() || $request->wantsJson();

        try {
            $rules = [
                'cashier_shift_id' => 'required|exists:cashier_shifts,id',
                'closing_balance' => 'required',
                'system_balance' => 'nullable',
                'difference' => 'nullable',
                'notes' => 'nullable',
            ];

            $data = $request->validate($rules);

            $shift = CashierShift::findOrFail($data['cashier_shift_id']);

            if ($shift->closed_at) {
                $msg = 'Shift ini sudah ditutup sebelumnya.';
                return $isAjax ? response()->json(['status' => 'error', 'message' => $msg], 422) : back()->with('error', $msg);
            }

            $closing_balance = (int) preg_replace('/[^\d]/', '', $request->closing_balance);
            $system_balance = (int) preg_replace('/[^\d]/', '', $request->system_balance);
            $difference = (int) preg_replace('/[^\d\-]/', '', $request->difference);

            $shift->closing_balance = $closing_balance;
            $shift->system_balance = $system_balance;
            $shift->difference = $difference;
            $shift->status_balance = $difference > 0 ? 'surplus' : ($difference < 0 ? 'deficit' : 'balanced');
            $shift->notes = $request->notes;
            $shift->closed_at = now();
            $shift->status = 'closed';
            $shift->save();

            // Clear cashier session so next page load knows shift is done
            session()->forget('cashier_shift_id');

            if ($isAjax) {
                return response()->json([
                    'status' => 'ok',
                    'redirect_url' => route('transaction.close'),
                ]);
            }

            return redirect()->route('transaction.close')->with('success', 'Cashier Shift closed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $isAjax ? response()->json(['status' => 'error', 'message' => implode(' ', collect($e->errors())->flatten()->toArray())], 422) : back()->with('error', 'Validasi gagal.');
        } catch (\Exception $e) {
            return $isAjax ? response()->json(['status' => 'error', 'message' => $e->getMessage()], 500) : back()->with('error', $e->getMessage());
        }
    }

    public function wahanaSold()
    {
        $shiftId = session('cashier_shift_id');

        $transactions = Transaction::with(['details.ticket.wahana', 'details.ticketPackage.wahanas'])
            ->where('cashier_shift_id', $shiftId)
            ->where('payment_status', 'paid')
            ->get();

        $wahanaCounts = [];

        foreach ($transactions as $t) {
            foreach ($t->details as $d) {
                if ($d->ticket_id && $d->ticket && $d->ticket->wahana) {
                    $name = $d->ticket->wahana->name;
                    $qty = $d->quantity;
                    if (!isset($wahanaCounts[$name])) {
                        $wahanaCounts[$name] = ['name' => $name, 'qty' => 0];
                    }
                    $wahanaCounts[$name]['qty'] += $qty;
                } elseif ($d->ticket_package_id && $d->ticketPackage) {
                    foreach ($d->ticketPackage->wahanas as $w) {
                        $name = $w->name;
                        // use pivot qty if exists to multiply package qty by contents
                        $wQty = isset($w->pivot->qty) ? $w->pivot->qty : 1;
                        $qty = $d->quantity * $wQty;

                        if (!isset($wahanaCounts[$name])) {
                            $wahanaCounts[$name] = ['name' => $name, 'qty' => 0];
                        }
                        $wahanaCounts[$name]['qty'] += $qty;
                    }
                }
            }
        }

        $wahanaCounts = array_values($wahanaCounts);
        usort($wahanaCounts, function ($a, $b) {
            return $b['qty'] <=> $a['qty'];
        });

        $top3 = array_slice($wahanaCounts, 0, 3);
        $all = $wahanaCounts;

        return response()->json([
            'status' => 'success',
            'top3' => $top3,
            'all' => $all,
        ]);
    }

    public function ticketSold()
    {
        $shiftId = session('cashier_shift_id');

        // we need all details logic
        // but simple fetch group by
        $transactions = Transaction::with(['details.ticket', 'details.ticketPackage'])
            ->where('cashier_shift_id', $shiftId)
            ->where('payment_status', 'paid')
            ->get();

        $ticketCounts = [];

        foreach ($transactions as $t) {
            foreach ($t->details as $d) {
                $name = 'Ticket';
                if ($d->ticket_id && $d->ticket) {
                    $name = $d->ticket->name;
                } elseif ($d->ticket_package_id && $d->ticketPackage) {
                    $name = $d->ticketPackage->name;
                } else {
                    continue; // skip if invalid
                }

                if (!isset($ticketCounts[$name])) {
                    $ticketCounts[$name] = [
                        'name' => $name,
                        'qty' => 0,
                    ];
                }
                $ticketCounts[$name]['qty'] += $d->quantity;
            }
        }

        $ticketCounts = array_values($ticketCounts);
        usort($ticketCounts, function ($a, $b) {
            return $b['qty'] <=> $a['qty'];
        });

        $top3 = array_slice($ticketCounts, 0, 3);
        $all = $ticketCounts;

        return response()->json([
            'status' => 'success',
            'top3' => $top3,
            'all' => $all,
        ]);
    }

    public function salesRevenue(Request $request)
    {
        $shiftId = session('cashier_shift_id');
        $shift = CashierShift::find($shiftId);

        // Tampilkan semua (paid + voided) lalu filter
        $query = Transaction::where('cashier_shift_id', $shiftId)->whereIn('payment_status', ['paid', 'voided']);

        // Filtering
        if ($request->has('filter') && $request->filter != '') {
            $filter = $request->filter;
            if ($filter == 'cash') {
                $query->where('payment_type', 'cash');
            } elseif ($filter == 'noncash') {
                $query->where('payment_type', 'noncash');
            } elseif ($filter == 'voided') {
                $query->where('payment_status', 'voided');
            } elseif (in_array($filter, ['qris', 'transfer', 'edc', 'lainnya'])) {
                $query->where('noncash_method', $filter);
            }
        }

        $transaction = $query->get();

        // Summary: hanya transaksi yang berhasil (bukan voided)
        $allTransactions = Transaction::where('cashier_shift_id', $shiftId)->where('payment_status', 'paid')->get();

        $totalCash = $allTransactions->where('payment_type', 'cash')->sum('total_amount');
        $totalNonCash = $allTransactions->where('payment_type', 'noncash')->sum('total_amount');
        $grandTotal = $allTransactions->sum('total_amount');
        $totalVoided = Transaction::where('cashier_shift_id', $shiftId)->where('payment_status', 'voided')->count();

        // Breakdown Non-Cash by Channel
        $nonCashBreakdown = $allTransactions->where('payment_type', 'noncash')->groupBy('noncash_channel')->map(fn($row) => $row->sum('total_amount'));

        return view('transaction.sales-revenue', compact('transaction', 'shift', 'totalCash', 'totalNonCash', 'grandTotal', 'nonCashBreakdown', 'totalVoided'));
    }
    public function getShiftData()
    {
        $shiftId = session('cashier_shift_id');
        $shift = CashierShift::find($shiftId);

        if (!$shift) {
            return response()->json(['status' => 'error', 'message' => 'Shift not found.'], 404);
        }

        $allTx = Transaction::where('cashier_shift_id', $shiftId)->where('payment_status', 'paid')->get();

        $totalCash = $allTx->where('payment_type', 'cash')->sum('total_amount');
        $totalNonCash = $allTx->where('payment_type', 'noncash')->sum('total_amount');
        $grandTotal = $allTx->sum('total_amount');

        $systemBalance = $shift->opening_balance + $totalCash;

        // Non-cash breakdown by method
        $byMethod = $allTx->where('payment_type', 'noncash')->groupBy('noncash_method')->map(fn($rows) => $rows->sum('total_amount'))->toArray();

        // Non-cash breakdown by channel
        $byChannel = $allTx->where('payment_type', 'noncash')->groupBy('noncash_channel')->map(fn($rows) => $rows->sum('total_amount'))->toArray();

        return response()->json([
            'shift' => [
                'id' => $shift->id,
                'opened_at' => $shift->opened_at ? \Carbon\Carbon::parse($shift->opened_at)->format('d M Y H:i') : '-',
                'opening_balance' => $shift->opening_balance,
                'status' => $shift->status,
                'cashier' => $shift->user->name ?? '-',
                'counter' => $shift->counter->name ?? '-',
            ],
            'system_balance' => $systemBalance,
            'total_cash' => $totalCash,
            'total_noncash' => $totalNonCash,
            'grand_total' => $grandTotal,
            'by_method' => $byMethod,
            'by_channel' => $byChannel,
        ]);
    }

    public function close()
    {
        $shiftId = session('cashier_shift_id');
        if (!$shiftId) {
            $shift = \App\Models\CashierShift::where('user_id', auth()->id())
                ->orderBy('id', 'desc')
                ->first();
            if ($shift) {
                $shiftId = $shift->id;
            }
        } else {
            $shift = \App\Models\CashierShift::find($shiftId);
        }

        $allTx = $shift ? \App\Models\Transaction::where('cashier_shift_id', $shiftId)->where('payment_status', 'paid')->get() : collect();

        $totalCash = $allTx->where('payment_type', 'cash')->sum('total_amount');
        $totalNonCash = $allTx->where('payment_type', 'noncash')->sum('total_amount');
        $grandTotal = $allTx->sum('total_amount');

        $byMethod = $allTx->where('payment_type', 'noncash')->groupBy('noncash_method')->map(fn($r) => $r->sum('total_amount'))->toArray();
        $byChannel = $allTx->where('payment_type', 'noncash')->groupBy('noncash_channel')->map(fn($r) => $r->sum('total_amount'))->toArray();

        return view('transaction.close', compact('shift', 'totalCash', 'totalNonCash', 'grandTotal', 'byMethod', 'byChannel'));
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

            $data = $request->validate($rules);

            $spvUser = \App\Models\User::where('spv_pin', $data['spv_code'])->first();

            if (!$spvUser) {
                if ($request->wantsJson()) {
                    return response()->json(['status' => 'error', 'message' => 'PIN SPV salah!'], 422);
                }
                throw new \Exception('PIN SPV salah!');
            }

            // reset count_print semua tiket dari transaksi terkait
            IssuedTicket::where('transaction_id', $data['id'])->update(['count_print' => 0]);

            // reset bill count
            Transaction::where('id', $data['id'])->update(['bill_count_print' => 0]);

            if ($request->wantsJson()) {
                return response()->json(['status' => 'success', 'message' => 'Tiket dan struk berhasil di-reset.']);
            }
            return back()->with('success', 'Tickets berhasil di-reset dan siap di reprint');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }
    public function voidTransaction(Request $request)
    {
        try {
            $data = $request->validate([
                'id' => 'required|exists:transactions,id',
                'spv_code' => 'required',
                'void_reason' => 'required|string|max:500',
            ]);

            $spvUser = \App\Models\User::where('spv_pin', $data['spv_code'])->first();
            if (!$spvUser) {
                return response()->json(['status' => 'error', 'message' => 'PIN SPV salah!'], 422);
            }

            $transaction = Transaction::where('id', $data['id'])->where('payment_status', 'paid')->firstOrFail();
            $transaction->payment_status = 'voided';
            $transaction->voided_at = now();
            $transaction->voided_by = $spvUser->id;
            $transaction->void_reason = $data['void_reason'];
            $transaction->save();

            return response()->json(['status' => 'ok', 'message' => 'Transaksi berhasil dibatalkan.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteDraftTransaction($id)
    {
        try {
            $data = Transaction::where('id', $id)->where('payment_status', 'pending')->first();

            if (!$data) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data not found!',
                    ],
                    404,
                ); // pakai 404
            }

            $data->delete();

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'Data deleted successfully',
                ],
                200,
            ); // pakai 200
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ],
                500,
            ); // ini oke untuk error tak terduga
        }
    }

    /**
     * AJAX: Save current cart as a pending draft transaction.
     */
    public function saveDraft(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total' => 'required|numeric',
            'order_list' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $orderList = json_decode($request->order_list, true);
        $freeRule = json_decode($request->free_rule ?? '[]', true) ?? [];

        if (!is_array($orderList) || count($orderList) === 0) {
            return response()->json(['status' => 'error', 'message' => 'Order list kosong.'], 422);
        }

        DB::beginTransaction();
        try {
            $dataTransaction = [
                'transaction_code' => uniqid(),
                'payment_status' => 'pending',
                'payment_type' => null,
                'total_amount' => $request->total,
                'user_id' => auth()->user()->id,
                'cashier_shift_id' => session('cashier_shift_id'),
                'paid_at' => null,
            ];

            // Update existing draft if transaction_id supplied
            if ($request->filled('transaction_id')) {
                $transaction = Transaction::where('id', $request->transaction_id)->where('payment_status', 'pending')->firstOrFail();
                $transaction->update($dataTransaction);
                $transaction->details()->delete();
                $transaction->freeGifts()->delete();
            } else {
                $transaction = Transaction::create($dataTransaction);
            }

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
                    'claimed_at' => now(),
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'ok', 'id' => $transaction->id, 'message' => 'Draft tersimpan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Return list of pending transactions for the current shift/user.
     */
    public function pendingList()
    {
        $shiftId = session('cashier_shift_id');
        $list = Transaction::with('details')
            ->where('payment_status', 'pending')
            ->where('user_id', auth()->id())
            ->when($shiftId, fn($q) => $q->where('cashier_shift_id', $shiftId))
            ->latest()
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'transaction_code' => $t->transaction_code,
                    'total_amount' => $t->total_amount,
                    'total_item' => $t->details->count(),
                    'created_at' => $t->created_at->format('d M Y H:i'),
                ];
            });

        return response()->json(['status' => 'ok', 'data' => $list]);
    }

    /**
     * AJAX: Return detail items of a pending transaction ready for localStorage.
     */
    public function pendingDetail($id)
    {
        $t = Transaction::with(['details.ticket', 'details.ticketPackage'])
            ->where('id', $id)
            ->where('payment_status', 'pending')
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $items = $t->details->map(function ($d) {
            $isPackage = $d->ticket_package_id !== null;
            $item = $isPackage ? $d->ticketPackage : $d->ticket;
            return [
                'id' => (string) ($item ? $item->id : $d->ticket_id ?? $d->ticket_package_id),
                'name' => $item ? $item->name : 'Unknown',
                'price' => (float) $d->price_each,
                'qty' => (int) $d->quantity,
                'type' => $isPackage ? 'package' : 'ticket',
            ];
        });

        return response()->json([
            'status' => 'ok',
            'transaction_id' => $t->id,
            'total' => $t->total_amount,
            'items' => $items,
        ]);
    }

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
