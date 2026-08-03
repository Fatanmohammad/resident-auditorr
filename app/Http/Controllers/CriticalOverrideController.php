<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\CriticalOverride;
use App\Services\RiskScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CriticalOverrideController extends Controller
{
    public function __construct(private RiskScoringService $scoringService) {}

    public function store(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'trigger_date'        => 'required|date',
            'trigger_type'        => 'required|in:Fraud Indicator,Selisih Kas Material,Dokumen/Agunan Hilang,User Sistem Tidak Sah,Transaksi Tanpa Otorisasi,TL High/Critical Overdue,Penolakan Data RA,Repeat Finding Critical',
            'trigger_description' => 'nullable|string',
            'approved_by'         => 'nullable|string',
            'notes'               => 'nullable|string',
        ]);

        $validated['unit_id']    = $unit->id;
        $validated['status']     = 'Aktif';
        $validated['created_by'] = Auth::id();

        CriticalOverride::create($validated);

        $period = date('Y');
        $this->scoringService->recomputeOverride($unit, $period);

        return back()->with('success', 'Critical override ditambahkan. Kategori risiko unit diperbarui ke High.');
    }

    public function updateStatus(Request $request, CriticalOverride $override)
    {
        $request->validate(['status' => 'required|in:Aktif,Tidak Aktif,Selesai']);
        $override->update(['status' => $request->status]);

        $period = date('Y');
        $this->scoringService->recomputeOverride($override->unit, $period);

        return back()->with('success', 'Status override diperbarui.');
    }
}
