<?php

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;
use Illuminate\Http\Request;

trait ResolvesPeriod
{
    /**
     * Resolve a [start, end, label, type] Carbon range from a request's
     * `type` (day|week|month|year|period) and, for `period`, a `period`
     * range string in the "start - end" format used by bs-rangepicker-basic.
     */
    protected function resolvePeriod(Request $request): array
    {
        $type = $request->get('type', 'day');
        $now = Carbon::now();

        switch ($type) {
            case 'week':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                $label = 'Mingguan (' . $start->format('d M') . ' - ' . $end->format('d M Y') . ')';
                break;

            case 'month':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $label = 'Bulanan (' . $start->format('F Y') . ')';
                break;

            case 'year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                $label = 'Tahunan (' . $start->format('Y') . ')';
                break;

            case 'period':
                if ($request->filled('period')) {
                    $dates = explode(' - ', $request->period);
                    $start = Carbon::parse(trim($dates[0]))->startOfDay();
                    $end = Carbon::parse(trim($dates[1] ?? $dates[0]))->endOfDay();
                } else {
                    $start = $now->copy()->startOfMonth();
                    $end = $now->copy()->endOfMonth();
                }
                $label = 'Periode (' . $start->format('d M Y') . ' - ' . $end->format('d M Y') . ')';
                break;

            case 'day':
            default:
                $type = 'day';
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                $label = 'Harian (' . $start->format('d M Y') . ')';
                break;
        }

        return [$start, $end, $label, $type];
    }
}
