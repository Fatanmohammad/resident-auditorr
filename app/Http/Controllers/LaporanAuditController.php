<?php

namespace App\Http\Controllers;

use App\Models\LaporanAudit;
use App\Models\AuditPlan;
use Illuminate\Http\Request;

class LaporanAuditController extends Controller
{
    public function index()
    {
        $laporans   = LaporanAudit::with('auditPlan.cabang')->latest()->get();
        $auditPlans = AuditPlan::where('status_approval', 'approved')
            ->whereDoesntHave('laporanAudit')
            ->with('cabang')->get();
        return view('laporan.index', compact('laporans', 'auditPlans'));
    }

    public function show($auditPlanId)
    {
        $laporan = LaporanAudit::where('audit_plan_id', $auditPlanId)->first();
        return response()->json(['status' => 'success', 'data' => $laporan]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'audit_plan_id'  => 'required|exists:audit_plans,id',
            'nomor_laporan'  => 'required|string|unique:laporan_audits,nomor_laporan',
        ]);
        $validated['status_approval_laporan'] = 'draft';
        LaporanAudit::create($validated);
        return back()->with('success', 'Laporan Audit berhasil dibuat.');
    }

    public function approve(Request $request, $id)
    {
        $laporan = LaporanAudit::findOrFail($id);
        $request->validate([
            'status_approval_laporan' => 'required|in:approved_kabag,approved_kadiv',
        ]);
        $laporan->update(['status_approval_laporan' => $request->status_approval_laporan]);
        return back()->with('success', 'Approval laporan berhasil diperbarui.');
    }
}
