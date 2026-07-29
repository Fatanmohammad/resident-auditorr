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

        // Data umum
        $totalRa     = User::where('role', 'ra')->count();
        $totalCabang = Cabang::whereNull('parent_id')->orWhere('tipe', '!=', 'anak_cabang')->count();
        $raMalas     = User::where('role', 'ra')
            ->whereDoesntHave('auditPlansAsRa', fn($q) => $q->where('created_at', '>=', now()->subDays(30)))
            ->count();
        $raPerCabang = User::where('role', 'ra')->with('cabang')->get()->groupBy('cabang_id');

        $temuanSignifikan = TemuanAudit::where('kategori', 'signifikan')->count();
        $temuanBerulang   = TemuanAudit::where('kategori', 'berulang')->count();
        $temuanPerBidang  = TemuanAudit::selectRaw('kategori, count(*) as total')
            ->groupBy('kategori')->pluck('total', 'kategori');

        $jadwalAktif   = AuditPlan::with(['cabang', 'raUser'])
            ->whereIn('status_approval', ['approved', 'waiting_kabag_approval', 'waiting_kadiv_approval'])
            ->latest()->take(5)->get();
        $totalJadwal   = AuditPlan::count();
        $jadwalSelesai = AuditPlan::where('status_approval', 'approved')->count();

        $scoringTerbaru = ScoringAudit::with('auditPlan.cabang')->latest()->take(5)->get();

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
