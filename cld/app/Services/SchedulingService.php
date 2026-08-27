<?php

namespace App\Services;

use App\Models\Unit;
use App\Models\Ra;
use App\Models\OnsiteFrequency;
use App\Models\ScheduledVisit;
use App\Models\RaCapacity;
use App\Models\RaAssignment;
use App\Models\CoverageDetail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SchedulingService
{
    /**
     * Compute onsite frequency untuk satu unit (§4.10 & §4.11)
     */
    public function computeFrequency(Unit $unit, int $period): OnsiteFrequency
    {
        $scoring = $unit->riskScorings()->where('period', $period)->first();
        $riskCategory = $scoring?->final_category ?? 'Low';

        // Lookup frequency matrix
        $matrix = DB::table('frequency_matrix')
            ->where('risk_category', $riskCategory)
            ->where('unit_type', $unit->unit_type)
            ->first();

        $autoLabel       = $matrix?->frequency_label ?? 'Tidak Terjadwal';
        $autoVisits      = (int)($matrix?->visits_per_year ?? 0);
        $isResident      = (bool)($matrix?->is_resident_daily_review ?? false);

        // Ambil override manual jika ada
        $existing = OnsiteFrequency::where('unit_id', $unit->id)->where('period', $period)->first();
        $override = $existing?->manual_override_frequency;

        $finalLabel  = $override ?? $autoLabel;
        $finalVisits = $override ? OnsiteFrequency::labelToVisitsPerYear($override) : $autoVisits;
        $basisNote   = $override ? 'Override manual' : 'Otomatis dari kategori risiko dan jenis unit';

        return OnsiteFrequency::updateOrCreate(
            ['unit_id' => $unit->id, 'period' => $period],
            [
                'auto_frequency_label'  => $autoLabel,
                'auto_visits_per_year'  => $autoVisits,
                'manual_override_frequency' => $override,
                'final_frequency_label' => $finalLabel,
                'final_visits_per_year' => $finalVisits,
                'is_resident_daily_review' => $isResident,
                'basis_note'            => $basisNote,
            ]
        );
    }

    /**
     * Compute frequency untuk semua unit aktif + update running total (§4.11)
     */
    public function computeAllFrequencies(int $period): void
    {
        $units = Unit::where('is_active', true)->orderBy('id')->get();
        $cumulative = 0;

        foreach ($units as $unit) {
            $freq = $this->computeFrequency($unit, $period);
            $seqStart = $cumulative + 1;
            $cumulative += $freq->final_visits_per_year;

            $freq->update([
                'visit_sequence_start'           => $freq->final_visits_per_year > 0 ? $seqStart : 0,
                'cumulative_visits_running_total' => $cumulative,
            ]);
        }
    }

    /**
     * Generate jadwal kunjungan untuk satu unit (§4.12)
     */
    public function generateSchedule(Unit $unit, int $period): void
    {
        $freq = OnsiteFrequency::where('unit_id', $unit->id)->where('period', $period)->first();
        if (!$freq || $freq->final_visits_per_year <= 0) return;

$year         = DB::table('calendar_params')->where('param_key', 'audit_plan_year')->value('param_value') ?? $period;
        $durationDays = OnsiteFrequency::labelToDurationDays($freq->final_frequency_label);
        // Pastikan index tidak negatif (unit yang sebelumnya visit_sequence_start = 0, mis. KC/Tidak Terjadwal)
        $globalIndex  = max(0, $freq->visit_sequence_start - 1); // 0-based

        for ($v = 1; $v <= $freq->final_visits_per_year; $v++) {
            $month = $this->recommendedMonth($freq->final_frequency_label, $v);

            // Sebar hari mulai: 3,7,11,15,19,23 (MOD 5 × 4 + 3, max 24)
            $startDay = min(24, 3 + ($globalIndex % 5) * 4);
            $globalIndex++;

            $autoStart = Carbon::create($year, $month, $startDay);
            $autoEnd   = $autoStart->copy()->addDays($durationDays - 1);

            $existing = ScheduledVisit::where('unit_id', $unit->id)
                ->where('period', $period)
                ->where('visit_number', $v)
                ->first();

            $overrideStart = $existing?->manual_override_start;
            $overrideEnd   = $existing?->manual_override_end;
            $finalStart    = $overrideStart ?? $autoStart->toDateString();
            $finalEnd      = $overrideEnd   ?? $autoEnd->toDateString();
            $finalDuration = Carbon::parse($finalStart)->diffInDays(Carbon::parse($finalEnd)) + 1;

            ScheduledVisit::updateOrCreate(
                ['unit_id' => $unit->id, 'period' => $period, 'visit_number' => $v],
                [
                    'recommended_month'    => $month,
                    'default_duration_days'=> $durationDays,
                    'auto_start_date'      => $autoStart->toDateString(),
                    'auto_end_date'        => $autoEnd->toDateString(),
                    'final_start_date'     => $finalStart,
                    'final_end_date'       => $finalEnd,
                    'final_duration_days'  => $finalDuration,
                    'status'               => $existing?->status ?? 'Planned',
                    'basis_note'           => $freq->basis_note,
                ]
            );
        }

        // Hapus kunjungan lama yang melebihi jumlah baru
        ScheduledVisit::where('unit_id', $unit->id)
            ->where('period', $period)
            ->where('visit_number', '>', $freq->final_visits_per_year)
            ->delete();
    }

    /**
     * Generate jadwal untuk semua unit aktif
     */
    public function generateAllSchedules(int $period): void
    {
        Unit::where('is_active', true)->each(fn($u) => $this->generateSchedule($u, $period));
    }

    /**
     * Hitung kapasitas RA per bulan (§4.13)
     */
    public function computeRaCapacity(Ra $ra, int $period): void
    {
        $effortPerUnit   = (float)(DB::table('calendar_params')->where('param_key', 'effort_offsite_per_unit')->value('param_value') ?? 1);
        $workingDays     = (int)(DB::table('calendar_params')->where('param_key', 'effective_working_days')->value('param_value') ?? 20);
        $warningThreshold= (float)(DB::table('calendar_params')->where('param_key', 'warning_utilization_threshold')->value('param_value') ?? 0.85);

        // Hitung unit offsite aktif milik RA ini
        $assignedUnitIds = RaAssignment::where('primary_ra_id', $ra->id)
            ->where('valid_from', '<=', $period)
            ->where('valid_to', '>=', $period)
            ->pluck('unit_id');

        $offsiteUnitCount = CoverageDetail::whereIn('unit_id', $assignedUnitIds)
            ->where('period', $period)
            ->whereIn('final_coverage_mode', ['H+1', 'Event-based'])
            ->distinct('unit_id')
            ->count('unit_id');

        for ($month = 1; $month <= 12; $month++) {
            $visitDays = ScheduledVisit::whereIn('unit_id', $assignedUnitIds)
                ->where('period', $period)
                ->where('status', '!=', 'Cancelled')
                ->whereMonth('final_start_date', $month)
                ->sum('final_duration_days');

            $visitCount = ScheduledVisit::whereIn('unit_id', $assignedUnitIds)
                ->where('period', $period)
                ->where('status', '!=', 'Cancelled')
                ->whereMonth('final_start_date', $month)
                ->count();

            $estimatedOffsite = $offsiteUnitCount * $effortPerUnit;
            $totalWorkload    = $estimatedOffsite + $visitDays;
            $utilization      = $workingDays > 0 ? round($totalWorkload / $workingDays, 4) : 0;

            $capacityStatus = match(true) {
                $utilization > 1.0             => 'Over Capacity',
                $utilization > $warningThreshold => 'Warning',
                default                        => 'OK',
            };

            $note = match($capacityStatus) {
                'Over Capacity' => 'Beban kerja melebihi kapasitas. Pertimbangkan redistribusi unit atau tambah RA.',
                'Warning'       => 'Beban kerja mendekati batas. Pantau jadwal kunjungan bulan ini.',
                default         => 'Kapasitas normal.',
            };

            RaCapacity::updateOrCreate(
                ['ra_id' => $ra->id, 'period' => $period, 'month' => $month],
                [
                    'effective_working_days'   => $workingDays,
                    'daily_offsite_unit_count' => $offsiteUnitCount,
                    'estimated_offsite_days'   => $estimatedOffsite,
                    'scheduled_visit_count'    => $visitCount,
                    'scheduled_visit_days'     => $visitDays,
                    'total_workload_days'       => $totalWorkload,
                    'utilization'              => $utilization,
                    'capacity_status'          => $capacityStatus,
                    'recommendation_note'      => $note,
                ]
            );
        }
    }

    /**
     * Hitung kapasitas semua RA aktif
     */
    public function computeAllCapacities(int $period): void
    {
        Ra::where('status', 'Aktif')->each(fn($ra) => $this->computeRaCapacity($ra, $period));
    }

    private function recommendedMonth(string $label, int $visitNumber): int
    {
        return match($label) {
            'Bulanan'    => $visitNumber,
            'Triwulanan' => $visitNumber * 3,
            'Semesteran' => $visitNumber * 6,
            default      => 6, // Tahunan → pertengahan tahun
        };
    }
}
