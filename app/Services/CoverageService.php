<?php

namespace App\Services;

use App\Models\Unit;
use App\Models\BranchRaMapping;
use App\Models\RaAssignment;
use App\Models\CoverageSetup;
use App\Models\CoverageSummary;
use App\Models\CoverageDetail;
use App\Models\DataCode;

class CoverageService
{
    /**
     * Compute RA assignment untuk satu unit (§4.6)
     * Murni geografis: unit.base_ra_unit → branch_ra_mapping → primary/backup RA
     */
    public function assignRa(Unit $unit, int $year): RaAssignment
    {
        $mapping = BranchRaMapping::where('branch_name', $unit->base_ra_unit)->first();

        $notes = null;
        if (!$mapping) {
            $notes = 'Perlu Mapping RA — Lengkapi Master Setup';
        }

        return RaAssignment::updateOrCreate(
            ['unit_id' => $unit->id, 'valid_from' => $year],
            [
                'primary_ra_id'     => $mapping?->primary_ra_id,
                'backup_ra_id'      => $mapping?->backup_ra_id,
                'resident_base'     => $unit->base_ra_unit,
                'assignment_status' => 'Aktif',
                'valid_to'          => $year,
                'notes'             => $notes,
            ]
        );
    }

/**
     * Assign RA untuk semua unit aktif sekaligus
     */
    public function assignAllRa(int $year): void
    {
        Unit::where('is_active', true)->each(fn($unit) => $this->assignRa($unit, $year));
    }

    /**
     * Compute coverage summary + detail untuk semua unit aktif sekaligus (§4.8, §4.9)
     */
    public function generateAllCoverage(int $period): void
    {
        Unit::where('is_active', true)->each(function ($unit) use ($period) {
            $this->computeCoverageSummary($unit, $period);
        });
    }

    /**
     * Compute coverage summary dari coverage_setup (§4.8)
     */
    public function computeCoverageSummary(Unit $unit, int $period): CoverageSummary
    {
        $setup = CoverageSetup::where('unit_id', $unit->id)->where('period', $period)->first();

        if (!$setup) {
            // Buat default otomatis jika belum ada
            $defaults = CoverageSetup::defaultsForUnitType($unit->unit_type);
            $setup = CoverageSetup::create(array_merge([
                'unit_id' => $unit->id,
                'period'  => $period,
            ], $defaults));
        }

// Hanya area yang RELEVAN untuk jenis unit yang dihitung ke coverage score.
        // Misal: Payment Point hanya Teller/Kas; KCPLK semua kecuali Kredit.
        $relevantAreas = CoverageSetup::relevantAreas($unit->unit_type);
        $flagToStatus = ['Ya' => 'H+1', 'Event' => 'Event-based', 'Tidak' => 'Tidak'];

        $statuses = [];
        $activeCount = 0;
        foreach ($relevantAreas as $area) {
            $flag = $setup->$area ?? 'Tidak';
            $status = $flagToStatus[$flag] ?? 'Tidak';
            $statuses["status_{$area}"] = $status;
            if (in_array($status, ['H+1', 'Event-based'])) $activeCount++;
        }

        $relevantCount = count($relevantAreas);
        $score = $relevantCount > 0 ? $activeCount / $relevantCount : 0;
        $coverageStatus = match(true) {
            $score == 1.0  => 'Lengkap',
            $score >= 0.75 => 'Cukup',
            default        => 'Perlu Lengkapi Setup',
        };

        $summary = CoverageSummary::updateOrCreate(
            ['unit_id' => $unit->id, 'period' => $period],
            array_merge($statuses, [
                'active_area_count' => $activeCount,
                'coverage_score'    => $score,
                'coverage_status'   => $coverageStatus,
            ])
        );

        // Lanjut compute coverage detail
        $this->computeCoverageDetail($unit, $summary, $period);

        return $summary;
    }

    /**
     * Compute coverage detail per 19 data code (§4.9)
     */
    public function computeCoverageDetail(Unit $unit, CoverageSummary $summary, int $period): void
    {
        $areaSummaryMap = [
            'Teller/Kas'   => $summary->status_teller_kas,
            'Biaya/Jurnal' => $summary->status_biaya_jurnal,
            'Kredit'       => $summary->status_kredit,
            'CS/DPK'       => $summary->status_cs_dpk,
            'ATM'          => $summary->status_atm,
            'APU-PPT/FDS'  => $summary->status_apu_fds,
            'TI Event'     => $summary->status_ti_event,
            'Pengaduan'    => $summary->status_pengaduan_aset,
            'Aset'         => $summary->status_pengaduan_aset,
            'Dokumen/Agunan' => 'Tidak',
            'TI Fisik'     => 'Tidak',
        ];

        DataCode::all()->each(function ($dc) use ($unit, $period, $areaSummaryMap) {
            if ($dc->daily_offsite_capable === 'Tidak') {
                $mode = 'Onsite-Periodik';
            } else {
                $mode = $areaSummaryMap[$dc->area] ?? 'Tidak';
                if ($mode === 'Tidak') $mode = 'Tidak';
            }

            CoverageDetail::updateOrCreate(
                ['unit_id' => $unit->id, 'data_code_id' => $dc->id, 'period' => $period],
                [
                    'final_coverage_mode' => $mode,
                    'enters_sop02'        => in_array($mode, ['H+1', 'Event-based']),
                    'enters_sop04'        => $mode === 'Onsite-Periodik',
                ]
            );
        });
    }
}
