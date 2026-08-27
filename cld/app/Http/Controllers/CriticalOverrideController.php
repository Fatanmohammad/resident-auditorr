<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\CriticalOverride;
use App\Models\ChangeLog;
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
            'reason'              => 'nullable|string',
        ]);

$validated['unit_id']    = $unit->id;
        $validated['status']     = 'Aktif';
        $validated['created_by'] = Auth::id();

        $override = CriticalOverride::create($validated);

        $period = date('Y');
        $this->scoringService->recomputeOverride($unit, $period);

        // Catat ke Change Log
        ChangeLog::record(
            sheetArea: 'Trigger Darurat',
            changeDescription: "Tambah Critical Override unit {$unit->unit_name} ({$unit->unit_code}) — tipe '{$override->trigger_type}'",
            reason: $request->input('reason', $override->trigger_description),
            unitId: $unit->id,
        );

        return back()->with('success', 'Critical override ditambahkan. Kategori risiko unit diperbarui ke High.');
    }

    public function updateStatus(Request $request, CriticalOverride $override)
    {
        $request->validate(['status' => 'required|in:Aktif,Tidak Aktif,Selesai']);
        $oldStatus = $override->status;
        $override->update(['status' => $request->status]);

        $period = date('Y');
        $this->scoringService->recomputeOverride($override->unit, $period);

        // Catat ke Change Log
        ChangeLog::record(
            sheetArea: 'Trigger Darurat',
            changeDescription: "Ubah status Critical Override tipe '{$override->trigger_type}' unit {$override->unit->unit_name} dari '{$oldStatus}' menjadi '{$request->status}'",
            reason: $request->input('reason'),
            unitId: $override->unit_id,
        );

        return back()->with('success', 'Status override diperbarui.');
    }
}
