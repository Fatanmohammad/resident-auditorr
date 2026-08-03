<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\BranchRaMapping;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::with(['riskScorings' => fn($q) => $q->where('period', date('Y'))])
            ->orderBy('unit_type')->orderBy('unit_name')->get();
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
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        Unit::create($validated);
        return redirect()->route('units.index')->with('success', 'Unit berhasil ditambahkan.');
    }

    public function show(Unit $unit)
    {
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
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $unit->update($validated);
        return redirect()->route('units.index')->with('success', 'Unit berhasil diperbarui.');
    }
}
