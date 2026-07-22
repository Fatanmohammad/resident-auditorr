<?php

namespace App\Http\Controllers;

use App\Models\MonitoringAudit;
use App\Models\AuditPlan;
use Illuminate\Http\Request;

class MonitoringAuditController extends Controller
{
    public function index()
    {
        $monitorings = MonitoringAudit::with('auditPlan.cabang')->latest()->get();
        $auditPlans  = AuditPlan::where('status_approval', 'approved')->with('cabang')->get();
        return view('monitoring.index', compact('monitorings', 'auditPlans'));
    }

    public function show($auditPlanId)
    {
        $monitoring = MonitoringAudit::where('audit_plan_id', $auditPlanId)->get();
        return response()->json(['status' => 'success', 'data' => $monitoring]);
    }

    public function syncMonitoring(Request $request, $auditPlanId)
    {
        $auditPlan = AuditPlan::with('kertasKerjaAudits.temuanAudits.tindakLanjuts')->findOrFail($auditPlanId);

        $totalTemuan = 0;
        $totalSelesai = 0;
        $totalPending = 0;

        foreach ($auditPlan->kertasKerjaAudits as $kka) {
            foreach ($kka->temuanAudits as $temuan) {
                $totalTemuan++;
                $tlTerakhir = $temuan->tindakLanjuts->last();
                if ($tlTerakhir && $tlTerakhir->status_tl === 'selesai') {
                    $totalSelesai++;
                } else {
                    $totalPending++;
                }
            }
        }

        $monitoring = MonitoringAudit::updateOrCreate(
            ['audit_plan_id' => $auditPlanId, 'jenis_monitoring' => 'terstruktur'],
            [
                'total_temuan'      => $totalTemuan,
                'total_tl_selesai'  => $totalSelesai,
                'total_tl_pending'  => $totalPending,
                'catatan_monitoring' => $request->catatan_monitoring ?? 'Rekapitulasi otomatis sistem',
            ]
        );

        return back()->with('success', 'Data monitoring berhasil di-sync.');
    }
}
