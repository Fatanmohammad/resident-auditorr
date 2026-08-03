<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\CoverageSetup;
use App\Services\CoverageService;
use Illuminate\Http\Request;

class CoverageController extends Controller
{
    public function __construct(private CoverageService $coverageService) {}

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
        ]);

        CoverageSetup::updateOrCreate(
            ['unit_id' => $unit->id, 'period' => $period],
            array_merge($validated, ['unit_id' => $unit->id])
        );

        // Recompute summary + detail otomatis
        $this->coverageService->computeCoverageSummary($unit, $period);

        return back()->with('success', 'Coverage setup disimpan dan summary diperbarui.');
    }

    public function assignAll(Request $request)
    {
        $year = $request->integer('year', date('Y'));
        $this->coverageService->assignAllRa($year);
        return back()->with('success', "Assignment RA untuk tahun {$year} berhasil diproses.");
    }
}
