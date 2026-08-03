<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\RawMetric;
use App\Services\RiskScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RawMetricController extends Controller
{
    public function __construct(private RiskScoringService $scoringService) {}

    public function create(Unit $unit)
    {
        $period  = request('period', date('Y'));
        $existing = RawMetric::where('unit_id', $unit->id)->where('period', $period)->first();
        return view('raw-metrics.form', compact('unit', 'period', 'existing'));
    }

    public function store(Request $request, Unit $unit)
    {
        $period = $request->integer('period', date('Y'));

        $validated = $request->validate([
            'period'                      => 'required|integer|min:2020|max:2099',
            'prior_onsite_findings'       => 'required|integer|min:0',
            'significant_findings'        => 'required|integer|min:0',
            'repeat_findings'             => 'required|integer|min:0',
            'offsite_deviation'           => 'required|integer|min:0',
            'offsite_deviation_significant'=> 'required|integer|min:0',
            'offsite_deviation_repeat'    => 'required|integer|min:0',
            'months_since_last_onsite'    => 'required|integer|min:0',
            'reversal_correction_txn'     => 'required|integer|min:0',
            'cash_discrepancy'            => 'required|integer|min:0',
            'unusual_cost_journal'        => 'required|integer|min:0',
            'large_risky_cash_txn'        => 'required|integer|min:0',
            'dpk_anomaly'                 => 'required|integer|min:0',
            'overdue_complaints'          => 'required|integer|min:0',
            'incomplete_cdd_edd'          => 'required|integer|min:0',
            'debtors_col_3_5'             => 'required|integer|min:0',
            'npl_ratio'                   => 'required|numeric|min:0|max:1',
            'credit_deviation'            => 'required|integer|min:0',
            'atm_dispute'                 => 'required|integer|min:0',
            'atm_downtime_hours'          => 'required|numeric|min:0',
            'critical_ti_incident'        => 'required|integer|min:0',
            'unusual_user_reset'          => 'required|integer|min:0',
            'ra_onsite_tl_overdue'        => 'required|integer|min:0',
            'ra_offsite_tl_overdue'       => 'required|integer|min:0',
            'skai_tl_overdue'             => 'required|integer|min:0',
            'regulator_tl_overdue'        => 'required|integer|min:0',
            'kap_tl_overdue'              => 'required|integer|min:0',
            'avg_response_days'           => 'required|numeric|min:0',
            'tl_response_quality'         => 'required|integer|min:0|max:4',
        ]);

        $validated['unit_id']  = $unit->id;
        $validated['input_by'] = Auth::id();

        RawMetric::updateOrCreate(
            ['unit_id' => $unit->id, 'period' => $period],
            $validated
        );

        // Recompute otomatis seluruh chain scoring
        $this->scoringService->recompute($unit, $period);

        return redirect()->route('units.show', $unit)->with('success', 'Raw metrics disimpan dan scoring diperbarui.');
    }
}
