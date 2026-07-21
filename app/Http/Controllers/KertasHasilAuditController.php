<?php

namespace App\Http\Controllers;

use App\Models\KertasHasilAudit;
use Illuminate\Http\Request;

class KertasHasilAuditController extends Controller
{
    public function index($kkaId)
    {
        $kha = KertasHasilAudit::where('kka_id', $kkaId)->with('kertasKerjaAudit')->first();
        return response()->json(['status' => 'success', 'data' => $kha]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kka_id' => 'required|exists:kertas_kerja_audits,id',
            'ringkasan_hasil' => 'required|string',
        ]);

        $validated['status_kha'] = 'draft';
        $kha = KertasHasilAudit::updateOrCreate(
            ['kka_id' => $validated['kka_id']],
            $validated
        );

        return response()->json(['status' => 'success', 'message' => 'KHA berhasil disimpan.', 'data' => $kha], 201);
    }

    public function approve(Request $request, $id)
    {
        $kha = KertasHasilAudit::findOrFail($id);
        $request->validate([
            'status_kha' => 'required|in:approved_kabag,approved_kadiv',
        ]);

        $kha->update(['status_kha' => $request->status_kha]);

        return response()->json(['status' => 'success', 'message' => 'Status KHA berhasil diperbarui.', 'data' => $kha]);
    }
}