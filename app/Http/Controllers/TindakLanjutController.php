<?php

namespace App\Http\Controllers;

use App\Models\TindakLanjut;
use Illuminate\Http\Request;

class TindakLanjutController extends Controller
{
    // Response dari Auditee (Upload Bukti TL)
    public function storeRespon(Request $request)
    {
        $validated = $request->validate([
            'temuan_id' => 'required|exists:temuan_audits,id',
            'auditee_user_id' => 'required|exists:users,id',
            'respon_auditee' => 'required|string',
            'bukti_lampiran' => 'nullable|file|mimes:pdf,jpg,png,zip|max:5120',
        ]);

        if ($request->hasFile('bukti_lampiran')) {
            $path = $request->file('bukti_lampiran')->store('bukti_tindak_lanjut', 'public');
            $validated['bukti_lampiran_path'] = $path;
        }

        $validated['status_tl'] = 'proses_tl';
        $tl = TindakLanjut::create($validated);

        return response()->json(['status' => 'success', 'message' => 'Tindak Lanjut berhasil diunggah oleh Auditee.', 'data' => $tl], 201);
    }

    // Verifikasi oleh RA
    public function verifikasiRa(Request $request, $id)
    {
        $tl = TindakLanjut::findOrFail($id);
        $request->validate([
            'status_tl' => 'required|in:selesai,terlambat,proses_tl',
            'catatan_verifikasi_ra' => 'nullable|string'
        ]);

        $tl->update([
            'status_tl' => $request->status_tl,
            'catatan_verifikasi_ra' => $request->catatan_verifikasi_ra
        ]);

        return response()->json(['status' => 'success', 'message' => 'Hasil verifikasi Tindak Lanjut disimpan.', 'data' => $tl]);
    }
}