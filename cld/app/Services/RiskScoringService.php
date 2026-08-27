<?php

namespace App\Services;

use App\Models\RawMetric;
use App\Models\RiskComponentScore;
use App\Models\RiskScoring;
use App\Models\Unit;

class RiskScoringService
{
    /**
     * Recompute full chain: RawMetric → ComponentScore → RiskScoring
     * Dipanggil setiap kali raw_metrics disimpan/diubah.
     */
    public function recompute(Unit $unit, int $period): RiskScoring
    {
        $raw = RawMetric::where('unit_id', $unit->id)->where('period', $period)->firstOrFail();

        // Step 1: Hitung 6 skor bidang
        $scores = $raw->hitungSemuaSkor();

        $cs = RiskComponentScore::updateOrCreate(
            ['unit_id' => $unit->id, 'period' => $period],
            [
                'skor_riwayat_ra'    => $scores['riwayat_ra'],
                'skor_kas_teller'    => $scores['kas_teller'],
                'skor_cs_dpk'        => $scores['cs_dpk'],
                'skor_kredit'        => $scores['kredit'],
                'skor_ti_atm'        => $scores['ti_atm'],
                'skor_monitoring_tl' => $scores['monitoring_tl'],
            ]
        );

        // Step 2: Hitung weighted score final
        $weightedScore   = RiskScoring::hitungWeightedScore($cs, $unit->unit_type);
        $initialCategory = RiskScoring::kategoridariSkor($weightedScore);

        // Step 3: Cek critical override
        $activeOverride  = $unit->criticalOverrides()->where('status', 'Aktif')->latest()->first();
        $hasOverride     = (bool) $activeOverride;
        $finalCategory   = $hasOverride ? 'High' : $initialCategory;
        $overrideReason  = $activeOverride?->trigger_description;

        // Step 4: Update transaction_volume_category di unit
        $volCategory = match(true) {
            $weightedScore >= 70 => 'Tinggi',
            $weightedScore >= 40 => 'Sedang',
            default              => 'Rendah',
        };
        $unit->update(['transaction_volume_category' => $volCategory]);

        // Step 5: Simpan risk scoring
        return RiskScoring::updateOrCreate(
            ['unit_id' => $unit->id, 'period' => $period],
            [
                'weighted_score'      => $weightedScore,
                'initial_category'    => $initialCategory,
                'has_active_override' => $hasOverride,
                'final_category'      => $finalCategory,
                'override_reason'     => $overrideReason,
                'priority_rank'       => RiskScoring::priorityRank($finalCategory),
            ]
        );
    }

    /**
     * Recompute hanya bagian override (dipanggil saat critical_override berubah status).
     */
    public function recomputeOverride(Unit $unit, int $period): void
    {
        $scoring = RiskScoring::where('unit_id', $unit->id)->where('period', $period)->first();
        if (!$scoring) return;

        $activeOverride = $unit->criticalOverrides()->where('status', 'Aktif')->latest()->first();
        $hasOverride    = (bool) $activeOverride;
        $finalCategory  = $hasOverride ? 'High' : $scoring->initial_category;

        $scoring->update([
            'has_active_override' => $hasOverride,
            'final_category'      => $finalCategory,
            'override_reason'     => $activeOverride?->trigger_description,
            'priority_rank'       => RiskScoring::priorityRank($finalCategory),
        ]);
    }
}
