<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPeriod;
use App\Models\PlaygroundSession;
use App\Models\Wahana;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlaygroundController extends Controller
{
    use ResolvesPeriod;

    public function index()
    {
        $today = Carbon::today();

        $data['wahanas'] = Wahana::all();
        $data['stat'] = [
            'total' => PlaygroundSession::whereDate('started_at', $today)->count(),
            'ongoing' => PlaygroundSession::whereDate('started_at', $today)->where('status', 'ongoing')->count(),
            'time_up' => PlaygroundSession::whereDate('started_at', $today)->where('status', 'time_up')->count(),
            'picked_up' => PlaygroundSession::whereDate('started_at', $today)->where('status', 'picked_up')->count(),
        ];

        return view('playground.index', $data);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'child_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'clothing_color' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1|max:600',
            'wahana_id' => 'nullable|exists:wahanas,id',
        ]);

        $startedAt = now();

        PlaygroundSession::create([
            'wahana_id' => $data['wahana_id'] ?? null,
            'child_name' => $data['child_name'],
            'gender' => $data['gender'],
            'clothing_color' => $data['clothing_color'],
            'duration_minutes' => $data['duration_minutes'],
            'started_at' => $startedAt,
            'end_at' => $startedAt->copy()->addMinutes($data['duration_minutes']),
            'status' => 'ongoing',
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Data anak berhasil ditambahkan');
    }

    public function activeSessions()
    {
        $sessions = PlaygroundSession::whereDate('started_at', Carbon::today())
            ->where('status', '!=', 'picked_up')
            ->orderBy('end_at')
            ->get();

        $sessions->each(function ($s) {
            if ($s->status === 'ongoing' && $s->remainingSeconds() <= 0) {
                $s->status = 'time_up';
                $s->is_calling = true;
                $s->save();
            }
        });

        return response()->json($sessions->map(function ($s) {
            return [
                'id' => $s->id,
                'child_name' => $s->child_name,
                'gender' => $s->gender,
                'clothing_color' => $s->clothing_color,
                'duration_minutes' => $s->duration_minutes,
                'status' => $s->status,
                'is_calling' => $s->is_calling,
                'remaining_seconds' => $s->remainingSeconds(),
                'call_count' => $s->call_count,
            ];
        }));
    }

    public function callManual($id)
    {
        $session = PlaygroundSession::findOrFail($id);
        $session->update([
            'is_calling' => true,
            'call_count' => $session->call_count + 1,
            'last_called_at' => now(),
            'status' => $session->status === 'ongoing' ? 'time_up' : $session->status,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function stopCalling($id)
    {
        $session = PlaygroundSession::findOrFail($id);
        $session->update(['is_calling' => false]);

        return response()->json(['status' => 'success']);
    }

    public function finish($id)
    {
        $session = PlaygroundSession::findOrFail($id);
        $session->update([
            'status' => 'picked_up',
            'is_calling' => false,
            'picked_up_at' => now(),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function report(Request $request)
    {
        [$start, $end, $label, $type] = $this->resolvePeriod($request);

        $sessions = PlaygroundSession::with(['wahana', 'createdBy'])
            ->whereBetween('started_at', [$start, $end])
            ->latest('started_at')
            ->get();

        $data['data'] = $sessions;
        $data['label'] = $label;

        return view('playground.report', $data);
    }
}
