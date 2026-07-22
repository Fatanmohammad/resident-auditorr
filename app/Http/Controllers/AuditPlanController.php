<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditPlanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = AuditPlan::with(['cabang', 'raUser']);

        // RA hanya lihat audit plan miliknya
        if ($user->role === 'ra') {
            $query->where('ra_user_id', $user->id);
        }

        $auditPlans = $query->latest()->get();
        return view('audit-plan.index', compact('auditPlans'));
    }

    public function create()
    {
        $cabangs = Cabang::where('tipe', '!=', 'pusat')->get();
        $raUsers = User::where('role', 'ra')->with('cabang')->get();
        return view('audit-plan.create', compact('cabangs', 'raUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cabang_id'      => 'required|exists:cabangs,id',
            'ra_user_id'     => 'required|exists:users,id',
            'tahun_periode'  => 'required|integer|min:2020|max:2099',
            'jadwal_mulai'   => 'required|date',
            'jadwal_selesai' => 'required|date|after:jadwal_mulai',
        ]);
        $validated['status_approval'] = 'draft';
        AuditPlan::create($validated);
        return redirect()->route('audit-plan.index')->with('success', 'Audit Plan berhasil dibuat.');
    }

    public function show($id)
    {
        $auditPlan = AuditPlan::with(['cabang', 'raUser', 'kertasKerjaAudits.temuanAudits', 'scoringAudit', 'laporanAudit'])->findOrFail($id);
        return view('audit-plan.show', compact('auditPlan'));
    }

    public function approve(Request $request, $id)
    {
        $auditPlan = AuditPlan::findOrFail($id);
        $request->validate([
            'action'         => 'required|in:submit_kabag,approve_kabag,approve_kadiv,reject',
            'catatan_revisi' => 'nullable|string',
        ]);

        $map = [
            'submit_kabag'  => 'waiting_kabag_approval',
            'approve_kabag' => 'waiting_kadiv_approval',
            'approve_kadiv' => 'approved',
            'reject'        => 'rejected',
        ];

        $auditPlan->status_approval = $map[$request->action];
        if ($request->action === 'reject') {
            $auditPlan->catatan_revisi = $request->catatan_revisi;
        }
        $auditPlan->save();

        return back()->with('success', 'Status Audit Plan berhasil diperbarui.');
    }
}
