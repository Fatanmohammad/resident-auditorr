<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\FinalAuditPlan;
use App\Models\ChangeLog;
use App\Services\FinalAuditPlanService;
use App\Services\RiskScoringService;
use App\Services\CoverageService;
use App\Services\SchedulingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinalAuditPlanController extends Controller
{
    public function __construct(
        private FinalAuditPlanService $planService,
        private RiskScoringService    $scoringService,
        private CoverageService       $coverageService,
        private SchedulingService     $schedulingService,
    ) {}

    public function index()
    {
        $period = request('period', date('Y'));
        $plans  = FinalAuditPlan::with(['unit', 'primaryRa', 'backupRa'])
            ->where('period', $period)
            ->orderByRaw("FIELD(risk_category,'High','Moderate to High','Moderate','Low to Moderate','Low')")
            ->get();

        // Agregat dashboard (§5)
        $stats = [
            'total'        => $plans->count(),
            'approved'     => $plans->where('plan_status', 'Approved')->count(),
            'draft'        => $plans->where('plan_status', 'Draft - Lengkapi Mapping RA')->count(),
            'by_risk'      => $plans->groupBy('risk_category')->map->count(),
            'by_frequency' => $plans->groupBy('onsite_frequency_label')->map->count(),
            'by_type'      => $plans->groupBy(fn($p) => $p->unit?->unit_type)->map->count(),
        ];

        return view('final-audit-plan.index', compact('plans', 'stats', 'period'));
    }

    public function show(FinalAuditPlan $finalAuditPlan)
    {
        $finalAuditPlan->load(['unit', 'primaryRa', 'backupRa']);
        $period  = $finalAuditPlan->period;
        $scoring = $finalAuditPlan->unit->riskScorings()->where('period', $period)->first();
        $cs      = $finalAuditPlan->unit->hasMany(\App\Models\RiskComponentScore::class)->where('period', $period)->first();
        $visits  = \App\Models\ScheduledVisit::where('unit_id', $finalAuditPlan->unit_id)
            ->where('period', $period)->orderBy('visit_number')->get();
        $coverage = \App\Models\CoverageSummary::where('unit_id', $finalAuditPlan->unit_id)
            ->where('period', $period)->first();

        return view('final-audit-plan.show', compact('finalAuditPlan', 'scoring', 'cs', 'visits', 'coverage'));
    }

    // Generate semua — jalankan seluruh pipeline SOP 01
    public function generateAll(Request $request)
    {
        $period = $request->integer('period', date('Y'));

        // Pipeline lengkap
        $this->coverageService->assignAllRa($period);
        $this->schedulingService->computeAllFrequencies($period);
        $this->schedulingService->generateAllSchedules($period);
        $this->schedulingService->computeAllCapacities($period);
        $this->planService->generateAll($period);

        ChangeLog::create([
            'date'               => now(),
            'sheet_area'         => 'Final Audit Plan',
            'change_description' => "Generate Final Audit Plan periode {$period}",
            'reason'             => 'Generate otomatis via sistem',
            'approved_by'        => Auth::user()->name,
            'status'             => 'Implemented',
            'created_by'         => Auth::id(),
        ]);

        return back()->with('success', "Final Audit Plan periode {$period} berhasil digenerate.");
    }

    // Change Log
    public function changeLog()
    {
        $logs = ChangeLog::with(['unit', 'createdBy'])->latest()->paginate(20);
        return view('final-audit-plan.change-log', compact('logs'));
    }

    public function storeChangeLog(Request $request)
    {
        $validated = $request->validate([
            'sheet_area'         => 'required|string',
            'unit_id'            => 'nullable|exists:units,id',
            'change_description' => 'required|string',
            'reason'             => 'nullable|string',
            'approved_by'        => 'nullable|string',
            'status'             => 'required|in:Draft,Approved,Rejected,Implemented',
        ]);

        $validated['date']       = now();
        $validated['created_by'] = Auth::id();
        ChangeLog::create($validated);

        return back()->with('success', 'Change log ditambahkan.');
    }
}
