<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Ra;
use App\Models\OnsiteFrequency;
use App\Models\ScheduledVisit;
use App\Models\RaCapacity;
use App\Services\SchedulingService;
use Illuminate\Http\Request;

class SchedulingController extends Controller
{
    public function __construct(private SchedulingService $schedulingService) {}

    // Halaman index: semua unit + frekuensi + jadwal
    public function index()
    {
        $period = request('period', date('Y'));
        $units  = Unit::with([
            'riskScorings'    => fn($q) => $q->where('period', $period),
            'raAssignment.primaryRa',
        ])->where('is_active', true)->orderBy('unit_type')->get();

        $frequencies = OnsiteFrequency::where('period', $period)
            ->pluck('final_frequency_label', 'unit_id');

        return view('scheduling.index', compact('units', 'frequencies', 'period'));
    }

    // Generate semua frekuensi + jadwal sekaligus
    public function generateAll(Request $request)
    {
        $period = $request->integer('period', date('Y'));
        $this->schedulingService->computeAllFrequencies($period);
        $this->schedulingService->generateAllSchedules($period);
        $this->schedulingService->computeAllCapacities($period);
        return back()->with('success', "Frekuensi, jadwal, dan kapasitas RA untuk periode {$period} berhasil digenerate.");
    }

    // Override frekuensi manual untuk satu unit
    public function overrideFrequency(Request $request, Unit $unit)
    {
        $request->validate([
            'period'                    => 'required|integer',
            'manual_override_frequency' => 'required|in:Bulanan,Triwulanan,Semesteran,Tahunan,Tidak Terjadwal',
        ]);

        $period = $request->integer('period');
        OnsiteFrequency::updateOrCreate(
            ['unit_id' => $unit->id, 'period' => $period],
            ['manual_override_frequency' => $request->manual_override_frequency]
        );

        // Recompute frequency + jadwal + kapasitas
        $this->schedulingService->computeFrequency($unit, $period);
        $this->schedulingService->generateSchedule($unit, $period);

        $assignment = $unit->raAssignment;
        if ($assignment?->primaryRa) {
            $this->schedulingService->computeRaCapacity($assignment->primaryRa, $period);
        }

        return back()->with('success', 'Override frekuensi disimpan dan jadwal diperbarui.');
    }

    // Override tanggal kunjungan manual
    public function overrideVisit(Request $request, ScheduledVisit $visit)
    {
        $request->validate([
            'manual_override_start' => 'required|date',
            'manual_override_end'   => 'required|date|after_or_equal:manual_override_start',
            'manual_notes'          => 'nullable|string',
        ]);

        $start    = $request->manual_override_start;
        $end      = $request->manual_override_end;
        $duration = \Carbon\Carbon::parse($start)->diffInDays(\Carbon\Carbon::parse($end)) + 1;

        $visit->update([
            'manual_override_start' => $start,
            'manual_override_end'   => $end,
            'final_start_date'      => $start,
            'final_end_date'        => $end,
            'final_duration_days'   => $duration,
            'manual_notes'          => $request->manual_notes,
        ]);

        // Recompute kapasitas RA
        $assignment = $visit->unit->raAssignment;
        if ($assignment?->primaryRa) {
            $this->schedulingService->computeRaCapacity($assignment->primaryRa, $visit->period);
        }

        return back()->with('success', 'Jadwal kunjungan diperbarui.');
    }

    // Update status kunjungan
    public function updateVisitStatus(Request $request, ScheduledVisit $visit)
    {
        $request->validate(['status' => 'required|in:Planned,In Progress,Completed,Postponed,Cancelled']);
        $visit->update(['status' => $request->status]);
        return back()->with('success', 'Status kunjungan diperbarui.');
    }

    // Halaman kapasitas RA
    public function capacity()
    {
        $period     = request('period', date('Y'));
        $ras        = Ra::where('status', 'Aktif')->with(['capacities' => fn($q) => $q->where('period', $period)->orderBy('month')])->get();
        return view('scheduling.capacity', compact('ras', 'period'));
    }

    // Detail jadwal per unit
    public function unitSchedule(Unit $unit)
    {
        $period  = request('period', date('Y'));
        $freq    = OnsiteFrequency::where('unit_id', $unit->id)->where('period', $period)->first();
        $visits  = ScheduledVisit::where('unit_id', $unit->id)->where('period', $period)->orderBy('visit_number')->get();
        $scoring = $unit->riskScorings()->where('period', $period)->first();
        return view('scheduling.unit', compact('unit', 'freq', 'visits', 'scoring', 'period'));
    }
}
