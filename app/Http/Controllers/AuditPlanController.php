<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use Illuminate\Http\Request;

class AuditPlanController extends Controller
{
    // Tampilkan semua jadwal audit plan
    public function index()
    {
        $auditPlans = AuditPlan::with(['cabang', 'raUser'])->get();
        return view('audit-plan.index', compact('auditPlans'));
    }

    // Buat Audit Plan baru oleh RA
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cabang_id' => 'required|exists:cabangs,id',
            'ra_user_id' => 'required|exists:users,id',
            'tahun_periode' => 'required|integer',
            'jadwal_mulai' => 'required|date',
            'jadwal_selesai' => 'required|date',
        ]);

        $validated['status_approval'] = 'draft';
        $auditPlan = AuditPlan::create($validated);

        return response()->json(['status' => 'success', 'message' => 'Audit Plan berhasil dibuat sebagai Draft.', 'data' => $auditPlan], 201);
    }

    // Approval Berjenjang: RA -> Kabag RA -> Kadiv SKAI
    public function approve(Request $request, $id)
    {
        $auditPlan = AuditPlan::findOrFail($id);
        $request->validate([
            'action' => 'required|in:submit_kabag,approve_kabag,approve_kadiv,reject',
            'catatan_revisi' => 'nullable|string'
        ]);

        switch ($request->action) {
            case 'submit_kabag':
                $auditPlan->status_approval = 'waiting_kabag_approval';
                break;
            case 'approve_kabag':
                $auditPlan->status_approval = 'waiting_kadiv_approval';
                break;
            case 'approve_kadiv':
                $auditPlan->status_approval = 'approved';
                break;
            case 'reject':
                $auditPlan->status_approval = 'rejected';
                $auditPlan->catatan_revisi = $request->catatan_revisi;
                break;
        }

        $auditPlan->save();

        return response()->json(['status' => 'success', 'message' => 'Status Audit Plan berhasil diperbarui.', 'data' => $auditPlan]);
    }
}