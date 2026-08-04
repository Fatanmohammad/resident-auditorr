<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\CoverageSetup;
use App\Models\ChangeLog;
use App\Services\CoverageService;
use Illuminate\Http\Request;

class CoverageController extends Controller
{
public function __construct(private CoverageService $coverageService) {}

    /**
     * Halaman index Coverage Offsite — daftar semua unit + status coverage (§3B.4)
     */
    public function index()
    {
        $period = request('period', date('Y'));
        $units  = Unit::with([
            'coverageSummaries' => fn($q) => $q->where('period', $period),
        ])->where('is_active', true)
          ->orderByRaw("FIELD(unit_type,'KC','KCU','KCP','KCPLK','Payment Point')")
          ->orderBy('unit_name')
          ->get();

        return view('coverage.index', compact('units', 'period'));
    }

    public function show(Unit $unit)
    {
        $period  = request('period', date('Y'));
        $setup   = CoverageSetup::where('unit_id', $unit->id)->where('period', $period)->first();
        $summary = \App\Models\CoverageSummary::where('unit_id', $unit->id)->where('period', $period)->first();
        $details = \App\Models\CoverageDetail::with('dataCode')
            ->where('unit_id', $unit->id)->where('period', $period)->get();
        $defaults = CoverageSetup::defaultsForUnitType($unit->unit_type);

        return view('coverage.show', compact('unit', 'period', 'setup', 'summary', 'details', 'defaults'));
    }

public function store(Request $request, Unit $unit)
    {
        $period = $request->integer('period', date('Y'));

$validated = $request->validate([
            'period'         => 'required|integer|min:2020|max:2099',
            'teller_kas'     => 'required|in:Ya,Tidak,Event',
            'cs_dpk'         => 'required|in:Ya,Tidak,Event',
            'kredit'         => 'required|in:Ya,Tidak,Event',
            'atm'            => 'required|in:Ya,Tidak,Event',
            'biaya_jurnal'   => 'required|in:Ya,Tidak,Event',
            'apu_fds'        => 'required|in:Ya,Tidak,Event',
            'ti_event'       => 'required|in:Ya,Tidak,Event',
            'pengaduan_aset' => 'required|in:Ya,Tidak,Event',
            'reason'         => 'nullable|string',
        ]);

CoverageSetup::updateOrCreate(
            ['unit_id' => $unit->id, 'period' => $period],
            array_merge(
                \Illuminate\Support\Arr::except($validated, ['period', 'reason']),
                ['unit_id' => $unit->id]
            )
        );

        // Recompute summary + detail otomatis
        $this->coverageService->computeCoverageSummary($unit, $period);

        // Catat ke Change Log
        ChangeLog::record(
            sheetArea: 'Coverage Offsite',
            changeDescription: "Update setup coverage unit {$unit->unit_name} ({$unit->unit_code}) untuk periode {$period}",
            reason: $request->input('reason'),
            unitId: $unit->id,
        );

        return back()->with('success', 'Coverage setup disimpan dan summary diperbarui.');
    }

    /**
     * Generate coverage summary + detail untuk semua unit aktif sekaligus
     */
    public function generateAll(Request $request)
    {
        $period = $request->integer('period', date('Y'));
        $this->coverageService->generateAllCoverage($period);
        return back()->with('success', "Coverage summary & detail untuk semua unit periode {$period} berhasil diproses.");
    }

public function assignAll(Request $request)
    {
        $year = $request->integer('year', date('Y'));
        $this->coverageService->assignAllRa($year);

        // Catat ke Change Log
        ChangeLog::record(
            sheetArea: 'Assignment RA',
            changeDescription: "Generate assignment RA untuk semua unit aktif tahun {$year}",
            reason: $request->input('reason'),
        );

        return back()->with('success', "Assignment RA untuk tahun {$year} berhasil diproses.");
    }

    public function assignmentIndex()
    {
        $period      = request('period', date('Y'));
        $assignments = \App\Models\RaAssignment::with(['unit', 'primaryRa', 'backupRa'])
            ->where('valid_from', '<=', $period)
            ->where('valid_to', '>=', $period)
            ->get()
            ->sortBy('unit.unit_name');

        $riskScorings = \App\Models\RiskScoring::where('period', $period)
            ->pluck('final_category', 'unit_id');

        return view('assignment-ra.index', compact('assignments', 'riskScorings', 'period'));
    }
}
