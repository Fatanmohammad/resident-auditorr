<?php

namespace App\Http\Controllers;

use App\Models\KertasKerjaAudit;
use Illuminate\Http\Request;

class KertasKerjaAuditController extends Controller
{
    public function index($auditPlanId)
    {
        $kka = KertasKerjaAudit::where('audit_plan_id', $auditPlanId)->with(['kertasHasilAudit', 'temuanAudits'])->get();
        return response()->json(['status' => 'success', 'data' => $kka]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'audit_plan_id' => 'required|exists:audit_plans,id',
            'bidang_audit' => 'required|string',
            'sub_bidang' => 'nullable|string',
            'tanggal_pemeriksaan' => 'required|date',
            'sample_pemeriksaan' => 'nullable|string',
        ]);

        $validated['status_kka'] = 'draft';
        $kka = KertasKerjaAudit::create($validated);

        return response()->json(['status' => 'success', 'message' => 'KKA berhasil dicatat.', 'data' => $kka], 201);
    }

    public function review(Request $request, $id)
    {
        $kka = KertasKerjaAudit::findOrFail($id);
        $request->validate([
            'status_kka' => 'required|in:reviewed_kabag,approved_kadiv,revisi',
            'catatan_kabag' => 'nullable|string'
        ]);

        $kka->update([
            'status_kka' => $request->status_kka,
            'catatan_kabag' => $request->catatan_kabag
        ]);

        return response()->json(['status' => 'success', 'message' => 'Review KKA berhasil diperbarui.', 'data' => $kka]);
    }
}