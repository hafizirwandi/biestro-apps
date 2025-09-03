<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Answer;
use App\Models\Survey;
use App\Models\Wahana;
use App\Models\Response;
use Carbon\CarbonPeriod;
use App\Models\Transaction;
use App\Models\IssuedTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{

    public function index()
    {
        return view('home.index');
    }
    public function stat(Request $request)
    {
        $filter = $request->get('filter', 'day'); // default day
        $start = $request->get('start'); // custom range start
        $end   = $request->get('end');   // custom range end

        $result = [
            'totalTransaction' => 0,
            'totalOmset' => 0,
            'totalTicketSold' => 0,
            'totalTicketFree' => 0,
            'penjualan' => [
                'categories' => [],
                'data' => [],
            ],
            'transaction' => [
                'categories' => [],
                'data' => [],
            ],
            'ticket' => [
                'categories' => [],
                'data' => [],
            ],
            'ticket_free' => [
                'categories' => [],
                'data' => [],
            ]
        ];
        // dd($request->input());
        $now = Carbon::now();
        $transaction = Transaction::where('payment_status', 'paid');
        $ticket = IssuedTicket::query();

        switch ($filter) {
            case 'day': // Hari ini
                $today = $now->toDateString();
                $transaction->whereDate('updated_at', $today);
                $ticket->whereDate('updated_at', $today);


                $grouped = (clone $transaction)->get()
                    ->groupBy(function ($item) {
                        return Carbon::parse($item->updated_at)->format('H'); // per jam
                    });

                $result['penjualan']['categories'] = range(0, 23); // jam 0–23
                $result['penjualan']['data'] = [];


                $result['transaction']['categories'] = range(0, 23); // jam 0–23
                $result['transaction']['data'] = [];


                foreach ($result['penjualan']['categories'] as $hour) {

                    $h = str_pad($hour, 2, '0', STR_PAD_LEFT);
                    $label = str_pad($hour, 2, '0', STR_PAD_LEFT) . ":00";

                    $result['penjualan']['data'][] = isset($grouped[$h]) ? $grouped[$h]->sum('total_amount') : 0;
                    $result['penjualan']['categories'][$hour] = $label;

                    $result['transaction']['data'][] = isset($grouped[$h]) ? $grouped[$h]->count() : 0;
                    $result['transaction']['categories'][$hour] = $label;
                }





                break;

            case 'week': // Minggu ini
                $start = $now->copy()->startOfWeek();
                $end   = $now->copy()->endOfWeek();
                $transaction->whereBetween('updated_at', [$start, $end]);
                $ticket->whereBetween('updated_at', [$start, $end]);


                $grouped = (clone $transaction)->get()
                    ->groupBy(function ($item) {
                        return Carbon::parse($item->updated_at)->toDateString(); // per tanggal
                    });

                $period = CarbonPeriod::create($now->copy()->startOfWeek(), $now->copy()->endOfWeek());
                $result['penjualan']['categories'] = [];
                $result['penjualan']['data'] = [];

                $result['transaction']['categories'] = [];
                $result['transaction']['data'] = [];

                foreach ($period as $date) {
                    $label = $date->toDateString();
                    $result['penjualan']['categories'][] = $label;
                    $result['penjualan']['data'][] = isset($grouped[$label]) ? $grouped[$label]->sum('total_amount') : 0;

                    $result['transaction']['categories'][] = $label;
                    $result['transaction']['data'][] = isset($grouped[$label]) ? $grouped[$label]->count() : 0;
                }


                break;

            case 'lastweek': // Minggu lalu
                $start = $now->copy()->subWeek()->startOfWeek();
                $end   = $now->copy()->subWeek()->endOfWeek();
                $transaction->whereBetween('updated_at', [$start, $end]);
                $ticket->whereBetween('updated_at', [$start, $end]);

                $grouped = (clone $transaction)->get()
                    ->groupBy(function ($item) {
                        return Carbon::parse($item->updated_at)->toDateString(); // per tanggal
                    });

                $period = CarbonPeriod::create($now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek());
                $result['penjualan']['categories'] = [];
                $result['penjualan']['data'] = [];

                $result['transaction']['categories'] = [];
                $result['transaction']['data'] = [];

                foreach ($period as $date) {
                    $label = $date->toDateString();
                    $result['penjualan']['categories'][] = $label;
                    $result['penjualan']['data'][] = isset($grouped[$label]) ? $grouped[$label]->sum('total_amount') : 0;

                    $result['transaction']['categories'][] = $label;
                    $result['transaction']['data'][] = isset($grouped[$label]) ? $grouped[$label]->count() : 0;
                }
                break;

            case 'month': // Bulan ini
                $transaction->whereYear('updated_at', $now->year)
                    ->whereMonth('updated_at', $now->month);
                $ticket->whereYear('updated_at', $now->year)
                    ->whereMonth('updated_at', $now->month);

                $grouped = (clone $transaction)->get()
                    ->groupBy(function ($item) {
                        return Carbon::parse($item->updated_at)->toDateString(); // per tanggal
                    });

                $period = CarbonPeriod::create($now->copy()->startOfMonth(), $now->copy()->endOfMonth());
                $result['penjualan']['categories'] = [];
                $result['penjualan']['data'] = [];


                $result['transaction']['categories'] = [];
                $result['transaction']['data'] = [];

                foreach ($period as $date) {
                    $label = $date->toDateString();
                    $result['penjualan']['categories'][] = $label;
                    $result['penjualan']['data'][] = isset($grouped[$label]) ? $grouped[$label]->sum('total_amount') : 0;

                    $result['transaction']['categories'][] = $label;
                    $result['transaction']['data'][] = isset($grouped[$label]) ? $grouped[$label]->count() : 0;
                }
                break;

            case 'lastmonth': // Bulan lalu
                $lastMonth = $now->copy()->subMonth();
                $transaction->whereYear('updated_at', $lastMonth->year)
                    ->whereMonth('updated_at', $lastMonth->month);
                $ticket->whereYear('updated_at', $lastMonth->year)
                    ->whereMonth('updated_at', $lastMonth->month);

                $grouped = (clone $transaction)->get()
                    ->groupBy(function ($item) {
                        return Carbon::parse($item->updated_at)->toDateString(); // per tanggal
                    });


                // ambil full periode bulan lalu
                $period = CarbonPeriod::create($lastMonth->copy()->startOfMonth(), $lastMonth->copy()->endOfMonth());



                $result['penjualan']['categories'] = [];
                $result['penjualan']['data'] = [];

                $result['transaction']['categories'] = [];
                $result['transaction']['data'] = [];

                foreach ($period as $date) {
                    $label = $date->toDateString();
                    $result['penjualan']['categories'][] = $label;
                    $result['penjualan']['data'][] = isset($grouped[$label]) ? $grouped[$label]->sum('total_amount') : 0;

                    $result['transaction']['categories'][] = $label;
                    $result['transaction']['data'][] = isset($grouped[$label]) ? $grouped[$label]->count() : 0;
                }
                break;

            case 'year': // Tahun ini
                $transaction->whereYear('updated_at', $now->year);
                $ticket->whereYear('updated_at', $now->year);


                $grouped = (clone $transaction)->get()
                    ->groupBy(function ($item) {
                        return Carbon::parse($item->updated_at)->format('Y-m'); // group per bulan
                    });

                // periode 12 bulan penuh
                $result['penjualan']['categories'] = [];
                $result['penjualan']['data'] = [];


                // periode 12 bulan penuh
                $result['transaction']['categories'] = [];
                $result['transaction']['data'] = [];

                for ($m = 1; $m <= 12; $m++) {
                    $label = Carbon::createFromDate($now->year, $m, 1)->format('Y-m');
                    $result['penjualan']['categories'][] = $label;
                    $result['penjualan']['data'][] = isset($grouped[$label]) ? $grouped[$label]->sum('total_amount') : 0;

                    $result['transaction']['categories'][] = $label;
                    $result['transaction']['data'][] = isset($grouped[$label]) ? $grouped[$label]->count() : 0;
                }

                break;

            case 'custom': // Range custom
                if (!empty($start) && !empty($end)) {
                    $start = Carbon::parse($start)->startOfDay();
                    $end   = Carbon::parse($end)->endOfDay();
                    $transaction->whereBetween('updated_at', [$start, $end]);
                    $ticket->whereBetween('updated_at', [$start, $end]);

                    $period = CarbonPeriod::create($start, $end);


                    $grouped = (clone $transaction)->get()
                        ->groupBy(function ($item) {
                            return Carbon::parse($item->updated_at)->format('Y-m-d'); // group per bulan
                        });




                    $result['penjualan']['categories'] = [];
                    $result['penjualan']['data'] = [];

                    $result['transaction']['categories'] = [];
                    $result['transaction']['data'] = [];

                    foreach ($period as $date) {


                        $label = $date->toDateString();
                        $result['penjualan']['categories'][] = $label;
                        $result['penjualan']['data'][] = isset($grouped[$label]) ? $grouped[$label]->sum('total_amount') : 0;

                        $result['transaction']['categories'][] = $label;
                        $result['transaction']['data'][] = isset($grouped[$label]) ? $grouped[$label]->count() : 0;
                    }
                }
                break;
        }

        $ticketCount = (clone $ticket)
            ->whereNull('free_gift_rule_id')
            ->selectRaw('wahana_id, COUNT(*) as total')
            ->groupBy('wahana_id')
            ->with('wahana:id,name') // pastikan relasi ada: IssuedTicket belongsTo Wahana
            ->get();
        $result['ticket']['categories'] =  $ticketCount->pluck('wahana.name');
        $result['ticket']['data'] = $ticketCount->pluck('total');


        $ticketCount2 = (clone $ticket)
            ->whereNotNull('free_gift_rule_id')
            ->selectRaw('wahana_id, COUNT(*) as total')
            ->groupBy('wahana_id')
            ->with('wahana:id,name') // pastikan relasi ada: IssuedTicket belongsTo Wahana
            ->get();
        $result['ticket_free']['categories'] =  $ticketCount2->pluck('wahana.name');
        $result['ticket_free']['data'] = $ticketCount2->pluck('total');



        $result['totalTransaction'] = (clone $transaction)->count();
        $result['totalOmset'] = format_rupiah((clone $transaction)->sum('total_amount'));
        $result['totalTicketSold'] = (clone $ticket)->whereNull('free_gift_rule_id')->count();
        $result['totalTicketFree'] = (clone $ticket)->whereNotNull('free_gift_rule_id')->count();



        return response()->json($result);

        // // return JSON
    }
}
