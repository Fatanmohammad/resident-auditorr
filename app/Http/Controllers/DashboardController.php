<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\User;
use App\Models\Unit;
use App\Models\FinalAuditPlan;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
public function index()
    {
        $user        = Auth::user();
        $totalRa     = User::where('role', 'ra')->count();
        $totalUnit   = Unit::where('is_active', true)->count();
        $jadwalAktif = AuditPlan::whereIn('status_approval', ['waiting_kabag_approval', 'waiting_kadiv_approval', 'approved'])
            ->latest()->take(5)->get();
        $totalJadwal   = AuditPlan::count();
        $jadwalSelesai = AuditPlan::where('status_approval', 'approved')->count();
        $totalFinalPlan = FinalAuditPlan::count();

        // §5 — Agregat dashboard
        $period    = date('Y');
        $units     = Unit::where('is_active', true)->get();
        $riskDist  = $units->groupBy(fn($u) => $u->latestRiskScoring($period)?->final_category ?? 'Belum Dinilai')
            ->map->count();
        $typeDist  = $units->groupBy('unit_type')->map->count();

        $freqDist = \App\Models\OnsiteFrequency::where('period', $period)
            ->get()
            ->groupBy(fn($f) => $f->is_resident_daily_review ? 'Resident Daily Review' : ($f->final_frequency_label ?? 'Tidak Terjadwal'))
            ->map->count();

        $planStats = [
            'total'    => $totalFinalPlan,
            'approved' => FinalAuditPlan::where('period', $period)->where('plan_status', 'Approved')->count(),
            'draft'    => FinalAuditPlan::where('period', $period)->where('plan_status', 'Draft - Lengkapi Mapping RA')->count(),
        ];

        return view('dashboard', compact(
            'user', 'totalRa', 'totalUnit',
            'jadwalAktif', 'totalJadwal', 'jadwalSelesai', 'totalFinalPlan',
            'riskDist', 'typeDist', 'freqDist', 'planStats'
        ));
    }
}
