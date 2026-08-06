<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPeriod;
use App\Models\PlaygroundSession;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlaygroundController extends Controller
{
    use ResolvesPeriod;

    public function index()
    {
        $today = Carbon::today();

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
        ]);

        $startedAt = now();

        PlaygroundSession::create([
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

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'child_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'clothing_color' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1|max:600',
        ]);

        $session = PlaygroundSession::findOrFail($id);

        $update = [
            'child_name' => $data['child_name'],
            'gender' => $data['gender'],
            'clothing_color' => $data['clothing_color'],
            'duration_minutes' => $data['duration_minutes'],
        ];

        // Recompute the countdown off the original start time — editing
        // duration corrects/extends the session, it doesn't restart the clock.
        if ($session->status !== 'picked_up') {
            $newEndAt = $session->started_at->copy()->addMinutes($data['duration_minutes']);
            $update['end_at'] = $newEndAt;
            if ($newEndAt->isFuture()) {
                $update['status'] = 'ongoing';
                $update['is_calling'] = false;
            }
        }

        $session->update($update);

        return response()->json(['status' => 'success']);
    }

    public function destroy($id)
    {
        PlaygroundSession::findOrFail($id)->delete();

        return response()->json(['status' => 'success']);
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

        $sessions = PlaygroundSession::with('createdBy')
            ->whereBetween('started_at', [$start, $end])
            ->latest('started_at')
            ->get();

        $data['data'] = $sessions;
        $data['label'] = $label;

        return view('playground.report', $data);
    }

    // Standalone daily report for playground-role users, opened in a new tab
    // from the Playground page itself (that page uses the sidebar-less kiosk
    // layout, so it needs its own direct link rather than the admin Report menu).
    public function todayReport()
    {
        $today = Carbon::today();

        $data['data'] = PlaygroundSession::with('createdBy')
            ->whereDate('started_at', $today)
            ->latest('started_at')
            ->get();
        $data['date'] = $today->format('d M Y');

        return view('playground.today-report', $data);
    }
}
