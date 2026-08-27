<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\BranchRaMapping;
use App\Models\ChangeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    /**
     * Terapkan filter cabang pada query unit.
     * RA hanya melihat unit milik cabangnya + seluruh anak cabangnya.
     * Operator (kabag_ra/kadiv_skai/admin) melihat semua unit.
     */
    private function scopeByBranch($query)
    {
        $allowedCabangIds = Auth::user()->cabangIdYangDapatDiakses();

        if ($allowedCabangIds !== null) {
            $query->whereIn('units.cabang_id', $allowedCabangIds);
        }

        return $query;
    }

    public function index()
    {
        $units = $this->scopeByBranch(
            Unit::with(['riskScorings' => fn($q) => $q->where('period', date('Y'))])
        )->orderBy('unit_type')->orderBy('unit_name')->get();
        return view('units.index', compact('units'));
    }

    public function create()
    {
        $branches = BranchRaMapping::orderBy('branch_name')->pluck('branch_name');
        return view('units.create', compact('branches'));
    }

    public function store(Request $request)
    {
$validated = $request->validate([
            'unit_code'               => 'required|string|unique:units,unit_code',
            'unit_name'               => 'required|string',
            'unit_type'               => 'required|in:KC,KCU,KCP,KCPLK,Payment Point',
            'parent_office'           => 'nullable|string',
            'region'                  => 'nullable|string',
            'base_ra_unit'            => 'nullable|string',
            'distance_from_parent_km' => 'nullable|numeric|min:0',
            'is_active'               => 'boolean',
            'reason'                  => 'nullable|string',
        ]);
$validated['is_active'] = $request->boolean('is_active', true);
        $unit = Unit::create($validated);

        // Catat ke Change Log
        ChangeLog::record(
            sheetArea: 'Master Unit',
            changeDescription: "Tambah unit baru {$unit->unit_name} ({$unit->unit_code}) — tipe {$unit->unit_type}",
            reason: $request->input('reason'),
            unitId: $unit->id,
        );

        return redirect()->route('units.index')->with('success', 'Unit berhasil ditambahkan.');
    }

public function show(Unit $unit)
    {
        $allowedCabangIds = Auth::user()->cabangIdYangDapatDiakses();
        if ($allowedCabangIds !== null && (!$unit->cabang_id || !in_array($unit->cabang_id, $allowedCabangIds))) {
            abort(403, 'Anda hanya dapat mengakses unit di cabang Anda sendiri.');
        }

        $period  = request('period', date('Y'));
        $scoring = $unit->riskScorings()->where('period', $period)->first();
        $cs      = $unit->hasMany(\App\Models\RiskComponentScore::class)->where('period', $period)->first();
        $overrides = $unit->criticalOverrides()->latest()->get();
        return view('units.show', compact('unit', 'scoring', 'cs', 'overrides', 'period'));
    }

    public function edit(Unit $unit)
    {
        $branches = BranchRaMapping::orderBy('branch_name')->pluck('branch_name');
        return view('units.edit', compact('unit', 'branches'));
    }

    public function update(Request $request, Unit $unit)
    {
$validated = $request->validate([
            'unit_name'               => 'required|string',
            'unit_type'               => 'required|in:KC,KCU,KCP,KCPLK,Payment Point',
            'parent_office'           => 'nullable|string',
            'region'                  => 'nullable|string',
            'base_ra_unit'            => 'nullable|string',
            'distance_from_parent_km' => 'nullable|numeric|min:0',
            'is_active'               => 'boolean',
            'reason'                  => 'nullable|string',
        ]);
$validated['is_active'] = $request->boolean('is_active', true);
        $unit->update($validated);

        // Catat ke Change Log
        ChangeLog::record(
            sheetArea: 'Master Unit',
            changeDescription: "Update unit {$unit->unit_name} ({$unit->unit_code}) — tipe {$unit->unit_type}, base RA unit: {$unit->base_ra_unit}",
            reason: $request->input('reason'),
            unitId: $unit->id,
        );

        return redirect()->route('units.index')->with('success', 'Unit berhasil diperbarui.');
    }

public function riskScoringIndex()
    {
        $period = request('period', date('Y'));
        $query  = Unit::with([
            'riskScorings'      => fn($q) => $q->where('period', $period),
            'criticalOverrides' => fn($q) => $q->where('status', 'Aktif'),
        ])->where('is_active', true);

        $query = $this->scopeByBranch($query);

        $units = $query->orderByRaw("FIELD(unit_type,'KC','KCU','KCP','KCPLK','Payment Point')")
          ->get();

        return view('risk-scoring.index', compact('units', 'period'));
    }
}
