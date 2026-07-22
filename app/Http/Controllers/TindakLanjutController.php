<?php

namespace App\Http\Controllers;

use App\Models\TindakLanjut;
use App\Models\TemuanAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TindakLanjutController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = TindakLanjut::with(['temuan.kka.auditPlan.cabang', 'auditeeUser']);

        if ($user->role === 'ra') {
            $query->whereHas('temuan.kka.auditPlan', fn($q) => $q->where('ra_user_id', $user->id));
        } elseif ($user->role === 'auditee') {
            $query->where('auditee_user_id', $user->id);
        }

        $tindakLanjuts = $query->latest()->get();
        $temuans = TemuanAudit::whereDoesntHave('tindakLanjuts', fn($q) => $q->where('status_tl', 'selesai'))->get();
        return view('tindak-lanjut.index', compact('tindakLanjuts', 'temuans'));
    }

    public function storeRespon(Request $request)
    {
        $validated = $request->validate([
            'temuan_id'       => 'required|exists:temuan_audits,id',
            'auditee_user_id' => 'required|exists:users,id',
            'respon_auditee'  => 'required|string',
            'bukti_lampiran'  => 'nullable|file|mimes:pdf,jpg,png,zip|max:5120',
        ]);

        if ($request->hasFile('bukti_lampiran')) {
            $validated['bukti_lampiran_path'] = $request->file('bukti_lampiran')->store('bukti_tindak_lanjut', 'public');
        }

        $validated['status_tl'] = 'proses_tl';
        TindakLanjut::create($validated);
        return back()->with('success', 'Tindak Lanjut berhasil diunggah.');
    }

    public function verifikasiRa(Request $request, $id)
    {
        $tl = TindakLanjut::findOrFail($id);
        $request->validate([
            'status_tl'              => 'required|in:selesai,terlambat,proses_tl',
            'catatan_verifikasi_ra'  => 'nullable|string',
        ]);
        $tl->update([
            'status_tl'             => $request->status_tl,
            'catatan_verifikasi_ra' => $request->catatan_verifikasi_ra,
        ]);
        return back()->with('success', 'Verifikasi Tindak Lanjut disimpan.');
    }
}
