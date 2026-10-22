<?php

namespace App\Http\Controllers\DailyRegister;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyRegister\DailyTarget;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class CarryOverController extends Controller
{
    /**
     * RA uploads sick letter to request an extension.
     */
    public function requestExtension(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:daily_targets,id',
            'surat_sakit' => 'required|file|mimes:pdf,jpg,png|max:5120',
        ]);

        $target = DailyTarget::findOrFail($request->target_id);

        if ($target->status === 'LOCKED_UNFINISHED') {
            return response()->json(['success' => false, 'message' => 'Data sudah ter-lock otomatis. Tidak bisa mengajukan penangguhan.'], 403);
        }

        if ($target->status === 'Final') {
            return response()->json(['success' => false, 'message' => 'KKA sudah berstatus final.'], 400);
        }

        $path = $request->file('surat_sakit')->store('surat_sakit', 'local');

        $target->update([
            'surat_sakit_path' => $path,
            'status' => 'PENDING_APPROVAL_PUSAT'
        ]);

        return response()->json(['success' => true, 'message' => 'Pengajuan penangguhan berhasil dikirim ke Pusat.']);
    }

    /**
     * Admin Pusat approves or rejects the extension request.
     */
    public function processExtension(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:daily_targets,id',
            'action' => 'required|in:approve,reject',
        ]);

        $target = DailyTarget::findOrFail($request->target_id);

        if ($target->status !== 'PENDING_APPROVAL_PUSAT') {
            return response()->json(['success' => false, 'message' => 'Target tidak dalam status menunggu approval.'], 400);
        }

        if ($request->action === 'approve') {
            // Give 30 days extension from original task date
            $extendedDate = $target->tanggal_tugas->copy()->addDays(30);

            $target->update([
                'status' => 'Pending', // Back to active
                'carry_over_count' => $target->carry_over_count + 1,
                'extended_due_date' => $extendedDate
            ]);

            return response()->json(['success' => true, 'message' => 'Penangguhan disetujui. Batas waktu diperpanjang hingga ' . $extendedDate->toDateString()]);

        } else {
            // Rejected
            // Check if it already passed H+2. If it did, it should immediately be locked.
            $gracePeriodEnd = $target->tanggal_tugas->copy()->addDays(2);
            $now = Carbon::now()->startOfDay();

            if ($now->gt($gracePeriodEnd)) {
                $target->update(['status' => 'LOCKED_UNFINISHED']);
                return response()->json(['success' => true, 'message' => 'Penangguhan ditolak. Karena sudah melewati H+2, target langsung dikunci.']);
            } else {
                $target->update(['status' => 'Pending']); // Let them finish it today or tomorrow
                return response()->json(['success' => true, 'message' => 'Penangguhan ditolak. RA masih bisa mengerjakannya sebelum H+2.']);
            }
        }
    }
}
