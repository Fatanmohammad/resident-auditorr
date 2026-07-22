<?php

namespace App\Http\Controllers;

use App\Models\ScoringAudit;
use App\Models\AuditPlan;
use Illuminate\Http\Request;

class ScoringAuditController extends Controller
{
    public function index()
    {
        $scorings   = ScoringAudit::with('auditPlan.cabang')->latest()->get();
        $auditPlans = AuditPlan::where('status_approval', 'approved')
            ->whereDoesntHave('scoringAudit')
            ->with('cabang')->get();
        return view('scoring.index', compact('scorings', 'auditPlans'));
    }

    public function hitungSkor(Request $request)
    {
        $validated = $request->validate([
            'audit_plan_id'       => 'required|exists:audit_plans,id',
            'skor_parameter_kat'  => 'required|numeric|min:0|max:100',
            'skor_tindak_lanjut'  => 'required|numeric|min:0|max:100',
        ]);

        $skorAkhir = ($validated['skor_parameter_kat'] * 0.4) + ($validated['skor_tindak_lanjut'] * 0.6);

        if ($skorAkhir >= 85) {
            $peringkat = 'Sangat Baik (WTP)';
        } elseif ($skorAkhir >= 70) {
            $peringkat = 'Baik / Cukup';
        } else {
            $peringkat = 'Kurang / Perlu Perhatian Khusus';
        }

        ScoringAudit::updateOrCreate(
            ['audit_plan_id' => $validated['audit_plan_id']],
            [
                'skor_parameter_kat' => $validated['skor_parameter_kat'],
                'skor_tindak_lanjut' => $validated['skor_tindak_lanjut'],
                'skor_akhir'         => $skorAkhir,
                'peringkat_ra'       => $peringkat,
            ]
        );

        return back()->with('success', 'Scoring berhasil dikalkulasi. Peringkat: ' . $peringkat);
    }
}
