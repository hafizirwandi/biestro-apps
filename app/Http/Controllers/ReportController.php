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
        $query = Transaction::query()
            ->where('payment_status', 'paid');

        // Filter periode
        if ($request->filled('period')) {
            $dates = explode(' - ', $request->period);
            $startDate = Carbon::parse(trim($dates[0]))->startOfDay();
            $endDate   = Carbon::parse(trim($dates[1]))->endOfDay();

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
            $endDate   = Carbon::parse(trim($dates[1]))->endOfDay();

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
        $data  = TransactionDetail::where('transaction_id', $id)->get();

        return view('report.detail-transaction-modal', compact('data'));
    }
}
