<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\LogSystem;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditTraceController extends Controller
{



    public function index(Request $request)
    {
        $month = Carbon::now()->format('Y-m');
        $period = $request->query('period') ?? $month;
        $data['period'] =  $period;
        $name = $request->query('user') ?? '';
        $data['user'] = $name;

        // Definisikan $startTime dan $endTime sesuai awal dan akhir bulan
        $startTime = Carbon::parse($period)->startOfMonth();
        $endTime = Carbon::parse($period)->endOfMonth();

        // Ambil data LogSystem dengan whereBetween menggunakan rentang waktu
        $data['data'] = Activity::where('causer_type', 'App\Models\User')->whereHas('causer', function ($query) use ($name) {
            $query->whereRaw('LOWER(name) LIKE  ?', ['%' . strtolower($name) . '%']);
        })->whereBetween('created_at', [$startTime, $endTime])->get();

        return view('audit-trace.index', $data);
    }


    public function detail($id)
    {
        $r = Activity::findOrFail($id);

        return view('audit-trace.detail', compact('r'));
    }
}
