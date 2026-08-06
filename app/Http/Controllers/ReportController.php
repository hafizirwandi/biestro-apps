<?php

namespace App\Http\Controllers;

use App\Models\IssuedTicket;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Wahana;
use App\Http\Controllers\Concerns\ResolvesPeriod;
use Illuminate\Http\Request;
use Intervention\Image\Colors\Rgb\Channels\Red;

class ReportController extends Controller
{
    use ResolvesPeriod;

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

    public function revenue(Request $request)
    {
        [$start, $end, $label, $type] = $this->resolvePeriod($request);

        $transactions = Transaction::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->get();

        $bucketFormat = match ($type) {
            'day' => 'H',
            'year' => 'Y-m',
            default => $start->diffInDays($end) > 62 ? 'Y-m' : 'Y-m-d',
        };

        $grouped = $transactions->groupBy(function ($item) use ($bucketFormat) {
            return Carbon::parse($item->paid_at)->format($bucketFormat);
        });

        $rows = [];
        if ($type === 'day') {
            foreach (range(0, 23) as $hour) {
                $key = str_pad($hour, 2, '0', STR_PAD_LEFT);
                $bucket = $grouped->get($key);
                $rows[] = [
                    'label' => $key . ':00',
                    'count' => $bucket ? $bucket->count() : 0,
                    'total' => $bucket ? $bucket->sum('total_amount') : 0,
                ];
            }
        } elseif ($bucketFormat === 'Y-m') {
            $period = CarbonPeriod::create($start->copy()->startOfMonth(), '1 month', $end);
            foreach ($period as $date) {
                $key = $date->format('Y-m');
                $bucket = $grouped->get($key);
                $rows[] = [
                    'label' => $date->format('F Y'),
                    'count' => $bucket ? $bucket->count() : 0,
                    'total' => $bucket ? $bucket->sum('total_amount') : 0,
                ];
            }
        } else {
            $period = CarbonPeriod::create($start, $end);
            foreach ($period as $date) {
                $key = $date->format('Y-m-d');
                $bucket = $grouped->get($key);
                $rows[] = [
                    'label' => $date->format('d M Y'),
                    'count' => $bucket ? $bucket->count() : 0,
                    'total' => $bucket ? $bucket->sum('total_amount') : 0,
                ];
            }
        }

        $data['rows'] = $rows;
        $data['label'] = $label;
        $data['grandCount'] = $transactions->count();
        $data['grandTotal'] = $transactions->sum('total_amount');

        return view('report.revenue', $data);
    }

    public function popularWahana(Request $request)
    {
        [$start, $end, $label, $type] = $this->resolvePeriod($request);

        $query = IssuedTicket::whereBetween('created_at', [$start, $end]);

        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status == 'sold') {
                $query->whereNull('free_gift_rule_id');
            }
            if ($request->status == 'free') {
                $query->whereNotNull('free_gift_rule_id');
            }
        }

        $ranking = $query->selectRaw('wahana_id, COUNT(*) as total')
            ->groupBy('wahana_id')
            ->with('wahana:id,name,key')
            ->orderByDesc('total')
            ->get();

        $data['data'] = $ranking;
        $data['label'] = $label;
        $data['grandTotal'] = $ranking->sum('total');

        return view('report.popular-wahana', $data);
    }

    public function ticketUsage(Request $request)
    {
        [$start, $end, $label, $type] = $this->resolvePeriod($request);

        // Tiket hanya bisa discan pada hari yang sama saat diterbitkan
        // (lihat ScanController::scan), jadi created_at = tanggal tiket
        // "seharusnya" dipakai — field yang tepat untuk difilter/dikelompokkan.
        $query = IssuedTicket::with('wahana:id,name')->whereBetween('created_at', [$start, $end]);

        if ($request->filled('wahana') && $request->wahana !== 'all') {
            $query->where('wahana_id', $request->wahana);
        }

        $tickets = $query->get();

        $bucketFormat = match ($type) {
            'day' => 'H',
            'year' => 'Y-m',
            default => $start->diffInDays($end) > 62 ? 'Y-m' : 'Y-m-d',
        };

        $grouped = $tickets->groupBy(function ($item) use ($bucketFormat) {
            return Carbon::parse($item->created_at)->format($bucketFormat);
        });

        $bucketRow = function ($label, $bucket) {
            $total = $bucket ? $bucket->count() : 0;
            $used = $bucket ? $bucket->where('is_used', true)->count() : 0;
            return [
                'label' => $label,
                'total' => $total,
                'used' => $used,
                'unused' => $total - $used,
                'pct' => $total > 0 ? round($used / $total * 100, 1) : 0,
            ];
        };

        $rows = [];
        if ($type === 'day') {
            foreach (range(0, 23) as $hour) {
                $key = str_pad($hour, 2, '0', STR_PAD_LEFT);
                $rows[] = $bucketRow($key . ':00', $grouped->get($key));
            }
        } elseif ($bucketFormat === 'Y-m') {
            $period = CarbonPeriod::create($start->copy()->startOfMonth(), '1 month', $end);
            foreach ($period as $date) {
                $rows[] = $bucketRow($date->format('F Y'), $grouped->get($date->format('Y-m')));
            }
        } else {
            $period = CarbonPeriod::create($start, $end);
            foreach ($period as $date) {
                $rows[] = $bucketRow($date->format('d M Y'), $grouped->get($date->format('Y-m-d')));
            }
        }

        $byWahana = $tickets
            ->groupBy(fn($t) => $t->wahana->name ?? '-')
            ->map(function ($group, $name) {
                $total = $group->count();
                $used = $group->where('is_used', true)->count();
                return [
                    'wahana' => $name,
                    'total' => $total,
                    'used' => $used,
                    'unused' => $total - $used,
                    'pct' => $total > 0 ? round($used / $total * 100, 1) : 0,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $data['rows'] = $rows;
        $data['byWahana'] = $byWahana;
        $data['label'] = $label;
        $data['grandTotal'] = $tickets->count();
        $data['grandUsed'] = $tickets->where('is_used', true)->count();
        $data['wahanaList'] = Wahana::orderBy('name')->get();

        return view('report.ticket-usage', $data);
    }
}
