<?php

namespace App\Services;

use App\Models\Unit;
use App\Models\FinalAuditPlan;
use App\Models\RaAssignment;
use App\Models\CoverageSummary;
use App\Models\OnsiteFrequency;

class FinalAuditPlanService
{
    /**
     * Generate Final Audit Plan untuk satu unit (§4.14)
     */
    public function generate(Unit $unit, int $period): FinalAuditPlan
    {
        $scoring    = $unit->riskScorings()->where('period', $period)->first();
        $assignment = RaAssignment::where('unit_id', $unit->id)
            ->where('valid_from', '<=', $period)->where('valid_to', '>=', $period)->first();
        $coverage   = CoverageSummary::where('unit_id', $unit->id)->where('period', $period)->first();
        $freq       = OnsiteFrequency::where('unit_id', $unit->id)->where('period', $period)->first();

        $riskCategory    = $scoring?->final_category ?? 'Low';
        $primaryRaId     = $assignment?->primary_ra_id;
        $backupRaId      = $assignment?->backup_ra_id;
        $needsMapping    = !$primaryRaId || str_contains($assignment?->notes ?? '', 'Perlu Mapping RA');

        $dailyOffsite    = $coverage && in_array($coverage->coverage_status, ['Lengkap', 'Cukup']);
        $freqLabel       = $freq?->final_frequency_label ?? 'Tidak Terjadwal';
        $visitsPerYear   = $freq?->final_visits_per_year ?? 0;
        $isResident      = $freq?->is_resident_daily_review ?? false;
        $triggerRequired = $riskCategory === 'High';

        $planStatus = $needsMapping ? 'Draft - Lengkapi Mapping RA' : 'Approved';

        $notes = $freqLabel === 'Tidak Terjadwal'
            ? 'Offsite H+1 tetap wajib; onsite hanya trigger'
            : 'Plan otomatis dari SOP 01';

        return FinalAuditPlan::updateOrCreate(
            ['unit_id' => $unit->id, 'period' => $period],
            [
                'risk_category'              => $riskCategory,
                'primary_ra_id'              => $primaryRaId,
                'backup_ra_id'               => $backupRaId,
                'daily_offsite_active'       => $dailyOffsite,
                'onsite_frequency_label'     => $freqLabel,
                'visits_per_year'            => $visitsPerYear,
                'is_resident_daily_review'   => $isResident,
                'risk_trigger_visit_required'=> $triggerRequired,
                'plan_status'                => $planStatus,
                'notes'                      => $notes,
            ]
        );
    }

    /**
     * Generate untuk semua unit aktif
     */
    public function generateAll(int $period): void
    {
        Unit::where('is_active', true)->each(fn($u) => $this->generate($u, $period));
    }
}
