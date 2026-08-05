<?php

namespace App\Http\Controllers;

use App\Models\IssuedTicket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data['wahanas'] = $user->wahanas()->get();
        $data['recentScans'] = IssuedTicket::where('used_by', $user->id)
            ->whereDate('used_at', Carbon::today())
            ->latest('used_at')
            ->limit(20)
            ->with('wahana:id,name')
            ->get();

        return view('scan.index', $data);
    }

    public function scan(Request $request)
    {
        $request->validate(['ticket_code' => 'required|string']);

        $user = auth()->user();
        $wahanaIds = $user->wahanas()->pluck('wahanas.id')->toArray();

        $ticket = IssuedTicket::with('wahana')->where('ticket_code', trim($request->ticket_code))->first();

        if (!$ticket) {
            return response()->json(['status' => 'error', 'message' => 'Kode tiket tidak ditemukan'], 404);
        }

        if (!in_array($ticket->wahana_id, $wahanaIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tiket ini bukan untuk wahana yang Anda tangani (' . ($ticket->wahana->name ?? '-') . ')',
            ], 422);
        }

        if (!$ticket->created_at->isToday()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tiket ini bukan untuk hari ini (diterbitkan ' . $ticket->created_at->format('d M Y') . ')',
            ], 422);
        }

        if ($ticket->is_used) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tiket sudah digunakan pada ' . optional($ticket->used_at)->format('d M Y H:i') . ' oleh ' . optional($ticket->usedBy)->name,
            ], 422);
        }

        $ticket->update([
            'is_used' => true,
            'used_at' => now(),
            'used_by' => $user->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tiket valid! Selamat menikmati ' . ($ticket->wahana->name ?? ''),
            'data' => [
                'ticket_code' => $ticket->ticket_code,
                'wahana' => $ticket->wahana->name ?? '-',
                'used_at' => $ticket->used_at->format('d M Y H:i'),
            ],
        ]);
    }

    public function unflag(Request $request)
    {
        $request->validate([
            'ticket_code' => 'required|string',
            'spv_code' => 'required|string',
            'reason' => 'required|string|max:255',
        ]);

        $spvUser = User::where('spv_pin', $request->spv_code)->first();

        if (!$spvUser || !$spvUser->can('scan-unflag')) {
            return response()->json(['status' => 'error', 'message' => 'PIN otorisasi tidak valid atau tidak memiliki hak unflag'], 403);
        }

        $ticket = IssuedTicket::where('ticket_code', trim($request->ticket_code))->first();

        if (!$ticket) {
            return response()->json(['status' => 'error', 'message' => 'Kode tiket tidak ditemukan'], 404);
        }

        if (!$ticket->is_used) {
            return response()->json(['status' => 'error', 'message' => 'Tiket ini belum berstatus digunakan'], 422);
        }

        $ticket->update([
            'is_used' => false,
            'used_at' => null,
            'unflagged_by' => $spvUser->id,
            'unflagged_at' => now(),
            'unflag_reason' => $request->reason,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Status tiket berhasil dibatalkan (unflag)']);
    }
}
