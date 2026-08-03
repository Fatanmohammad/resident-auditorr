<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\User;
use App\Models\Unit;
use App\Models\RawMetric;
use App\Models\CoverageSummary;
use App\Models\ScheduledVisit;
use App\Models\FinalAuditPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditPlanController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $query = AuditPlan::with(['raUser']);
        if ($user->role === 'ra') {
            $query->where('ra_user_id', $user->id);
        }
        $auditPlans     = $query->latest()->get();
        $unitCount      = Unit::where('is_active', true)->count();
        $rawMetricCount = RawMetric::count();
        $coverageCount  = CoverageSummary::count();
        $scheduleCount  = ScheduledVisit::count();
        $finalPlanCount = FinalAuditPlan::count();

        return view('audit-plan.index', compact(
            'auditPlans', 'unitCount', 'rawMetricCount',
            'coverageCount', 'scheduleCount', 'finalPlanCount'
        ));
    }

    public function create()
    {
        $raUsers = User::where('role', 'ra')->get();
        return view('audit-plan.create', compact('raUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ra_user_id'     => 'required|exists:users,id',
            'tahun_periode'  => 'required|integer|min:2020|max:2099',
            'jadwal_mulai'   => 'required|date',
            'jadwal_selesai' => 'required|date|after:jadwal_mulai',
        ]);
        $validated['status_approval'] = 'waiting_kabag_approval';
        AuditPlan::create($validated);
        return redirect()->route('audit-plan.index')->with('success', 'Audit Plan berhasil dibuat.');
    }

    public function show($id)
    {
        $auditPlan = AuditPlan::with(['raUser'])->findOrFail($id);
        return view('audit-plan.show', compact('auditPlan'));
    }

    public function approve(Request $request, $id)
    {
        $auditPlan = AuditPlan::findOrFail($id);
        $request->validate([
            'action' => 'required|in:approve_kabag,approve_kadiv,reject',
            'catatan_revisi' => 'nullable|string',
        ]);

        $map = [
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
