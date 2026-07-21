<?php

namespace App\Http\Controllers;

use App\Models\LaporanAudit;
use Illuminate\Http\Request;

class LaporanAuditController extends Controller
{
    public function show($auditPlanId)
    {
        $laporan = LaporanAudit::where('audit_plan_id', $auditPlanId)->first();
        return response()->json(['status' => 'success', 'data' => $laporan]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'audit_plan_id' => 'required|exists:audit_plans,id',
            'nomor_laporan' => 'required|string|unique:laporan_audits,nomor_laporan',
        ]);

        $validated['status_approval_laporan'] = 'draft';
        $laporan = LaporanAudit::create($validated);

        return response()->json(['status' => 'success', 'message' => 'Laporan Audit berhasil dibuat.', 'data' => $laporan], 201);
    }

    public function approve(Request $request, $id)
    {
        $laporan = LaporanAudit::findOrFail($id);
        $request->validate([
            'status_approval_laporan' => 'required|in:approved_kabag,approved_kadiv',
        ]);

        $laporan->update(['status_approval_laporan' => $request->status_approval_laporan]);

        return response()->json(['status' => 'success', 'message' => 'Approval laporan berhasil diperbarui.', 'data' => $laporan]);
    }
}