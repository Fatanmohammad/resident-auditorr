<?php

namespace App\Http\Controllers;

use App\Models\KertasKerjaAudit;
use App\Models\AuditPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KertasKerjaAuditController extends Controller
{
    public function indexAll()
    {
        $user = Auth::user();
        $query = KertasKerjaAudit::with(['auditPlan.cabang', 'auditPlan.raUser', 'temuanAudits']);

        if ($user->role === 'ra') {
            $query->whereHas('auditPlan', fn($q) => $q->where('ra_user_id', $user->id));
        }

        $kkas = $query->latest()->get();
        return view('kka.index', compact('kkas'));
    }

    public function index($auditPlanId)
    {
        $kka = KertasKerjaAudit::where('audit_plan_id', $auditPlanId)
            ->with(['temuanAudits', 'kertasHasilAudit'])->get();
        return response()->json(['status' => 'success', 'data' => $kka]);
    }

    public function create()
    {
        $user = Auth::user();
        $auditPlans = AuditPlan::where('ra_user_id', $user->id)
            ->where('status_approval', 'approved')
            ->with('cabang')->get();
        return view('kka.create', compact('auditPlans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'audit_plan_id'       => 'required|exists:audit_plans,id',
            'bidang_audit'        => 'required|string',
            'sub_bidang'          => 'nullable|string',
            'tanggal_pemeriksaan' => 'required|date',
            'sample_pemeriksaan'  => 'nullable|string',
        ]);
        $validated['status_kka'] = 'draft';
        KertasKerjaAudit::create($validated);
        return redirect()->route('kka.index')->with('success', 'KKA berhasil dicatat.');
    }

    public function show($id)
    {
        $kka = KertasKerjaAudit::with(['auditPlan.cabang', 'temuanAudits.tindakLanjuts', 'kertasHasilAudit'])->findOrFail($id);
        return view('kka.show', compact('kka'));
    }

    public function review(Request $request, $id)
    {
        $kka = KertasKerjaAudit::findOrFail($id);
        $request->validate([
            'status_kka'    => 'required|in:reviewed_kabag,approved_kadiv,revisi',
            'catatan_kabag' => 'nullable|string',
        ]);
        $kka->update([
            'status_kka'    => $request->status_kka,
            'catatan_kabag' => $request->catatan_kabag,
        ]);
        return back()->with('success', 'Review KKA berhasil diperbarui.');
    }
}
