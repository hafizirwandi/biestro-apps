<?php

namespace App\Http\Controllers;

use App\Models\IssuedTicket;
use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Wahana;
use Illuminate\Http\Request;
use Intervention\Image\Colors\Rgb\Channels\Red;

class ReportController extends Controller
{
    public function transaction(Request $request)
    {
        $query = Transaction::query()->where('payment_status', 'paid');

        // Filter periode
        if ($request->filled('period')) {
            $dates = explode(' - ', $request->period);
            $startDate = Carbon::parse(trim($dates[0]))->startOfDay();
            $endDate = Carbon::parse(trim($dates[1]))->endOfDay();

            $query->whereBetween('paid_at', [$startDate, $endDate]);
        }

        // Filter metode pembayaran
        if ($request->filled('method') && $request->method !== 'all') {
            $query->where('payment_type', $request->method);
        }

        $data['data'] = $query->get();

        return view('report.transaction', $data);
    }
    public function ticket(Request $request)
    {
        $data['wahana'] = Wahana::all();
        $query = IssuedTicket::query();

        // Filter periode
        if ($request->filled('period')) {
            $dates = explode(' - ', $request->period);
            $startDate = Carbon::parse(trim($dates[0]))->startOfDay();
            $endDate = Carbon::parse(trim($dates[1]))->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->filled('wahana') && $request->wahana !== 'all') {
            $query->where('wahana_id', $request->wahana);
        }

        // Filter status tiket
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status == 'sold') {
                $query->whereNull('free_gift_rule_id');
            }
            if ($request->status == 'free') {
                $query->whereNotNull('free_gift_rule_id');
            }
        }

        $data['data'] = $query->get();

        // Debug dulu kalau masih salah
        // dd($query->toSql(), $query->getBindings());

        return view('report.ticket', $data);
    }
    public function detailTransactionModal($id)
    {
        $data = TransactionDetail::where('transaction_id', $id)->get();

        return view('report.detail-transaction-modal', compact('data'));
    }
    public function shift(Request $request)
    {
        $query = \App\Models\CashierShift::with('user');

        if ($request->filled('period')) {
            $dates = explode(' - ', $request->period);
            $startDate = Carbon::parse(trim($dates[0]))->startOfDay();
            $endDate = Carbon::parse(trim($dates[1]))->endOfDay();
            $query->whereBetween('opened_at', [$startDate, $endDate]);
        }

        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }

        $data['data'] = $query->latest()->get();
        $data['users'] = \App\Models\User::all();
        return view('report.shift', $data);
    }

    public function items(Request $request)
    {
        $query = TransactionDetail::with(['ticket', 'ticketPackage', 'transaction'])->whereHas('transaction', function ($q) use ($request) {
            $q->where('payment_status', 'paid');
            if ($request->filled('period')) {
                $dates = explode(' - ', $request->period);
                $startDate = Carbon::parse(trim($dates[0]))->startOfDay();
                $endDate = Carbon::parse(trim($dates[1]))->endOfDay();
                $q->whereBetween('paid_at', [$startDate, $endDate]);
            }
        });

        $details = $query->get();

        // Group by Item Name (Ticket or Package)
        $report = $details
            ->groupBy(function ($item) {
                return $item->ticket ? $item->ticket->name : ($item->ticketPackage ? $item->ticketPackage->name : 'Unknown');
            })
            ->map(function ($group) {
                return [
                    'name' => $group->first()->ticket ? $group->first()->ticket->name : ($group->first()->ticketPackage ? $group->first()->ticketPackage->name : 'Unknown'),
                    'type' => $group->first()->ticket ? 'Ticket' : 'Package',
                    'qty' => $group->sum('quantity'),
                    'total' => $group->sum('subtotal'),
                ];
            })
            ->values();

        $data['data'] = $report;
        return view('report.items', $data);
    }

    public function payment(Request $request)
    {
        $query = Transaction::where('payment_status', 'paid');

        if ($request->filled('period')) {
            $dates = explode(' - ', $request->period);
            $startDate = Carbon::parse(trim($dates[0]))->startOfDay();
            $endDate = Carbon::parse(trim($dates[1]))->endOfDay();
            $query->whereBetween('paid_at', [$startDate, $endDate]);
        }

        $transactions = $query->get();

        // Group by Payment Type & Channel
        $report = $transactions
            ->groupBy(function ($item) {
                if ($item->payment_type == 'cash') {
                    return 'Cash';
                }
                return 'Non-Cash (' . $item->noncash_method . ' - ' . $item->noncash_channel . ')';
            })
            ->map(function ($group, $key) {
                return [
                    'method' => $key,
                    'count' => $group->count(),
                    'total' => $group->sum('total_amount'),
                ];
            })
            ->values();

        $data['data'] = $report;
        return view('report.payment', $data);
    }
}
