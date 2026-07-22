<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\TemuanAudit;
use App\Models\KertasKerjaAudit;
use App\Models\ScoringAudit;
use App\Models\MonitoringAudit;
use App\Models\User;
use App\Models\Cabang;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Widget 1: RA per Cabang
        $raPerCabang = User::where('role', 'ra')
            ->with('cabang')
            ->get()
            ->groupBy('cabang_id');

        $totalRa = User::where('role', 'ra')->count();
        $totalCabang = Cabang::whereNull('parent_id')->orWhere('tipe', '!=', 'anak_cabang')->count();

        // RA malas kerja: RA tanpa KKA dalam 30 hari terakhir
        $raMalas = User::where('role', 'ra')
            ->whereDoesntHave('auditPlansAsRa', fn($q) => $q->where('created_at', '>=', now()->subDays(30)))
            ->count();

        // Widget 2: Temuan Lemah
        $temuanSignifikan = TemuanAudit::where('kategori', 'signifikan')->count();
        $temuanBerulang   = TemuanAudit::where('kategori', 'berulang')->count();
        $temuanPerBidang  = TemuanAudit::selectRaw('kategori, count(*) as total')
            ->groupBy('kategori')->pluck('total', 'kategori');

        // Widget 3: Penjadwalan Audit
        $jadwalAktif = AuditPlan::with(['cabang', 'raUser'])
            ->whereIn('status_approval', ['approved', 'waiting_kabag_approval', 'waiting_kadiv_approval'])
            ->latest()->take(5)->get();

        $totalJadwal = AuditPlan::count();
        $jadwalSelesai = AuditPlan::where('status_approval', 'approved')->count();

        // Widget 4: Kinerja & Scoring
        $skorPerBidang = KertasKerjaAudit::selectRaw('bidang_audit, count(*) as total, status_kka')
            ->groupBy('bidang_audit', 'status_kka')->get();

        $scoringTerbaru = ScoringAudit::with('auditPlan.cabang')->latest()->take(5)->get();

        // Widget 5: Ringkasan Monitoring
        $monitoringData = MonitoringAudit::selectRaw('
            sum(total_temuan) as total_temuan,
            sum(total_tl_selesai) as total_selesai,
            sum(total_tl_pending) as total_pending
        ')->first();

        return view('dashboard', compact(
            'user', 'totalRa', 'totalCabang', 'raMalas', 'raPerCabang',
            'temuanSignifikan', 'temuanBerulang', 'temuanPerBidang',
            'jadwalAktif', 'totalJadwal', 'jadwalSelesai',
            'scoringTerbaru', 'monitoringData'
        ));
    }
}
