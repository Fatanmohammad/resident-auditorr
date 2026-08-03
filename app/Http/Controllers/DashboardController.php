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

        return view('dashboard', compact(
            'user', 'totalRa', 'totalUnit',
            'jadwalAktif', 'totalJadwal', 'jadwalSelesai', 'totalFinalPlan'
        ));
    }
}
