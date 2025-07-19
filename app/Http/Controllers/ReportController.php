<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Intervention\Image\Colors\Rgb\Channels\Red;

class ReportController extends Controller
{
    public function transaction(Request $request)
    {
        $data['data'] = collect();
        $query = Transaction::query()->where('payment_status', 'paid');

        if ($request->filled('period')) {
            if ($request->filled('period')) {
                $dates = explode(' - ', $request->period);
                $startDate = Carbon::parse(trim($dates[0]))->startOfDay();
                $endDate = Carbon::parse(trim($dates[1]))->endOfDay();

                $query->whereBetween('paid_at', [$startDate, $endDate]);
            }
            $data['data'] = $query->get();
        }

        return view('report.transaction', $data);
    }
    public function detailTransactionModal($id)
    {
        $data  = TransactionDetail::where('transaction_id', $id)->get();

        return view('report.detail-transaction-modal', compact('data'));
    }
}
