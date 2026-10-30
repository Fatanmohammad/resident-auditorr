<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KkaIntegrationController extends Controller
{
    public function index(OnsiteVisit $visit)
    {
        // 11 Area KKA Sesuai Sheet KKA_Control & KKA_Integrasi Excel SOP 04.1
        $definitions = [
            ['no' => 1, 'area' => 'Teller / Kas', 'sheet' => 'KKA_Teller_Kas', 'table' => 'onsite_kka_teller_kas', 'route' => 'onsite.kka.teller_kas.index'],
            ['no' => 2, 'area' => 'DPK & Layanan Nasabah', 'sheet' => 'KKA_DPK_Layanan', 'table' => 'onsite_kka_dpks', 'route' => 'onsite.kka.dpk.index'],
            ['no' => 3, 'area' => 'Kredit', 'sheet' => 'KKA_Kredit', 'table' => 'onsite_kka_kredits', 'route' => 'onsite.kka.kredit.index'],
            ['no' => 4, 'area' => 'Biaya / Beban', 'sheet' => 'KKA_Biaya_Beban', 'table' => 'onsite_kka_biayas', 'route' => 'onsite.kka.biaya.index'],
            ['no' => 5, 'area' => 'Kartu ATM', 'sheet' => 'KKA_Kartu_ATM', 'table' => 'onsite_kka_atms', 'route' => 'onsite.kka.atm.index'],
            ['no' => 6, 'area' => 'Pengaduan Nasabah', 'sheet' => 'KKA_Pengaduan', 'table' => 'onsite_kka_pengaduans', 'route' => 'onsite.kka.pengaduan.index'],
            ['no' => 7, 'area' => 'Cash Opname Khasanah & ATM', 'sheet' => 'KKA_Cash_Opname', 'table' => 'onsite_cash_opnames', 'route' => 'onsite.cash_opname.create'],
            ['no' => 8, 'area' => 'Observasi Lingkungan & Gedung', 'sheet' => 'KKA_Observasi_Lingkungan', 'table' => 'onsite_observasis', 'route' => 'onsite.observasi.create'],
            ['no' => 9, 'area' => 'Rekonsiliasi Stok Cek & BG', 'sheet' => 'KKA_Rekon_Stok_Cek_BG', 'table' => 'onsite_rekon_cek_bgs', 'route' => 'onsite.rekon.cekbg.create'],
            ['no' => 10, 'area' => 'Rekonsiliasi Barang Cetakan', 'sheet' => 'KKA_Rekon_Barang_Cetakan', 'table' => 'onsite_rekon_barang_cetakans', 'route' => 'onsite.rekon.bct.create'],
            ['no' => 11, 'area' => 'Rekonsiliasi Kartu ATM', 'sheet' => 'KKA_Rekon_Kartu_ATM', 'table' => 'onsite_rekon_kartu_atms', 'route' => 'onsite.rekon.katm.create'],
        ];

        $summary = [];
        $totalEligibleAll = 0;
        $totalDiujiAll = 0;
        $totalExceptionAll = 0;
        $totalHighRiskAll = 0;

        foreach ($definitions as $def) {
            $eligible = 0;
            $diuji = 0;
            $exception = 0;
            $highRisk = 0;

            if (Schema::hasTable($def['table'])) {
                $query = DB::table($def['table'])->where('onsite_visit_id', $visit->id);
                $eligible = (clone $query)->count();

                if ($eligible > 0) {
                    if (Schema::hasColumn($def['table'], 'status_uji')) {
                        $diuji = (clone $query)->where('status_uji', '!=', 'Belum Diuji')->count();
                        $exception = (clone $query)->where('status_uji', 'Ada Exception')->count();
                    } else {
                        $diuji = $eligible;
                    }

                    if (Schema::hasColumn($def['table'], 'risk_level')) {
                        $highRisk = (clone $query)->whereIn('risk_level', ['High', 'Kritis'])->count();
                    }
                }
            }

            // Rumus Status KKA sesuai ketentuan Excel
            $statusKka = 'Belum Dibuat';
            if ($eligible > 0) {
                if ($diuji == 0) {
                    $statusKka = 'Belum Diuji';
                } elseif ($diuji < $eligible) {
                    $statusKka = 'In Progress';
                } else {
                    $statusKka = 'SELESAI';
                }
            }

            $totalEligibleAll += $eligible;
            $totalDiujiAll += $diuji;
            $totalExceptionAll += $exception;
            $totalHighRiskAll += $highRisk;

            $summary[] = [
                'no' => $def['no'],
                'area' => $def['area'],
                'sheet' => $def['sheet'],
                'route' => $def['route'],
                'status_kka' => $statusKka,
                'eligible' => $eligible,
                'diuji' => $diuji,
                'exception' => $exception,
                'high_risk' => $highRisk,
            ];
        }

        $statusIntegrasi = ($totalExceptionAll > 0) 
            ? 'Ada Exception (Lanjut Register Temuan)' 
            : (($totalDiujiAll > 0 && $totalDiujiAll == $totalEligibleAll) ? 'CLEAN (Tanpa Temuan)' : 'Dalam Proses Pengerjaan');

        return view('onsite.kka_integration.index', compact(
            'visit', 'summary', 'totalEligibleAll', 'totalDiujiAll', 
            'totalExceptionAll', 'totalHighRiskAll', 'statusIntegrasi'
        ));
    }
}