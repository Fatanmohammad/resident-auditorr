<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\User;
use App\Models\Unit;
use App\Models\FinalAuditPlan;
use App\Models\SurpriseAuditAssignment;
use App\Models\Sop07RegisterTl;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Sop09RegisterIndikasi;
use App\Models\PelaporanBulanan\Sop08KalenderLaporan;
use App\Http\Controllers\Monitoring\MonitoringController;
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

        // SOP 01 Agregat
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

        $riskTriggerAlarms = [];
        try {
            if (class_exists(\App\Services\RiskTrigger\RiskTriggerService::class)) {
                $riskTriggerAlarms = app(\App\Services\RiskTrigger\RiskTriggerService::class)->getActiveAlarms($user);
            }
        } catch (\Throwable $e) {}

        // =========================================================================
        // SOP 04: KUNJUNGAN ONSITE CABANG
        // =========================================================================
        $totalOnsiteVisits = OnsiteVisit::count();
        $onsiteBerlangsung = OnsiteVisit::whereIn('status', ['Berlangsung', 'Dijadwalkan', 'Draft'])->count();
        $pendingOnsiteVisits = OnsiteVisit::whereIn('status', ['Berlangsung', 'Dijadwalkan', 'Draft'])
            ->orderBy('tanggal_mulai', 'asc')
            ->take(5)
            ->get();

        // =========================================================================
        // SOP 05: SURPRISE AUDIT (SIDAK FISIK)
        // =========================================================================
        $totalSurpriseAudit = SurpriseAuditAssignment::count();
        $saDalamProses      = SurpriseAuditAssignment::whereIn('status_assignment', ['SIAP DILAKSANAKAN', 'DALAM PROSES'])->count();
        $saPerluPerbaikan   = SurpriseAuditAssignment::where('status_review_admin', 'PERLU PERBAIKAN')->count();
        $saSelesai          = SurpriseAuditAssignment::where('status_assignment', 'SELESAI')->count();

        $pendingSurpriseAudits = SurpriseAuditAssignment::where('status_assignment', '!=', 'SELESAI')
            ->orWhere('status_review_admin', 'PERLU PERBAIKAN')
            ->orderByRaw("CASE WHEN status_review_admin = 'PERLU PERBAIKAN' THEN 0 ELSE 1 END ASC")
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // =========================================================================
        // SOP 07: MONITORING TINDAK LANJUT TERPADU
        // =========================================================================
        try {
            MonitoringController::autoSyncSurpriseAuditFindings();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Dashboard autoSyncSurpriseAuditFindings warning: ' . $e->getMessage());
        }

        $queryInternal = Sop07RegisterTl::whereIn('sumber_tl', ['SKAI', 'Resident Auditor', 'Internal', 'Surprise Audit', 'Offsite', 'Onsite']);
        $totalTemuanInternal = (clone $queryInternal)->count();
        $temuanInternalBelumTl = (clone $queryInternal)->whereIn('status_final', ['OPEN', 'UNDER REVIEW', 'INADEQUATE', 'OVERDUE'])->count();
        $temuanInternalSudahTl = (clone $queryInternal)->whereIn('status_final', ['CLOSED', 'PROPOSED CLOSED'])->count();

        $queryRegulator = Sop07RegisterTl::whereIn('sumber_tl', ['Regulator', 'OJK', 'Bank Indonesia', 'BI', 'BPK', 'Auditor Eksternal']);
        $totalTemuanRegulator = (clone $queryRegulator)->count();
        $temuanRegulatorBelumTl = (clone $queryRegulator)->whereIn('status_final', ['OPEN', 'UNDER REVIEW', 'INADEQUATE', 'OVERDUE'])->count();
        $temuanRegulatorSudahTl = (clone $queryRegulator)->whereIn('status_final', ['CLOSED', 'PROPOSED CLOSED'])->count();

        $pendingFindings = Sop07RegisterTl::whereIn('status_final', ['OPEN', 'UNDER REVIEW', 'INADEQUATE', 'OVERDUE'])
            ->orderByRaw("CASE WHEN prioritas = 'HIGH' THEN 1 WHEN prioritas = 'MEDIUM' THEN 2 WHEN prioritas = 'LOW' THEN 3 ELSE 4 END ASC")
            ->orderBy('target_penyelesaian', 'asc')
            ->take(5)
            ->get();

        // =========================================================================
        // SOP 08 & SOP 09: PELAPORAN BULANAN & INDIKASI FRAUD
        // =========================================================================
        $totalLaporanBulanan = Sop08KalenderLaporan::count();
        $laporanBulanIni = Sop08KalenderLaporan::where('periode', 'LIKE', '%' . date('Y') . '%')
            ->orderBy('id', 'desc')
            ->first();
        $laporanBulanIniStatus = $laporanBulanIni ? ($laporanBulanIni->status_final ?: $laporanBulanIni->status_review) : 'Belum Diajukan';

        $activeFraudCases = Sop09RegisterIndikasi::whereIn('status_kasus', ['OPEN', 'INVESTIGASI', 'PROSES'])->count();
        $pendingFrauds = Sop09RegisterIndikasi::whereIn('status_kasus', ['OPEN', 'INVESTIGASI', 'PROSES'])->take(3)->get();

        return view('dashboard', compact(
            'user', 'totalRa', 'totalUnit',
            'jadwalAktif', 'totalJadwal', 'jadwalSelesai', 'totalFinalPlan',
            'riskDist', 'typeDist', 'freqDist', 'planStats', 'riskTriggerAlarms',
            // SOP 04 Onsite
            'totalOnsiteVisits', 'onsiteBerlangsung', 'pendingOnsiteVisits',
            // SOP 05 Surprise Audit
            'totalSurpriseAudit', 'saDalamProses', 'saPerluPerbaikan', 'saSelesai', 'pendingSurpriseAudits',
            // SOP 07 Monitoring
            'totalTemuanInternal', 'temuanInternalBelumTl', 'temuanInternalSudahTl',
            'totalTemuanRegulator', 'temuanRegulatorBelumTl', 'temuanRegulatorSudahTl',
            'pendingFindings',
            // SOP 08 & 09
            'totalLaporanBulanan', 'laporanBulanIniStatus', 'activeFraudCases', 'pendingFrauds'
        ));
    }
}
