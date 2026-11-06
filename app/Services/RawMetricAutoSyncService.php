<?php

namespace App\Services;

use App\Models\Unit;
use App\Models\RawMetric;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteKkaFinding;
use App\Models\Onsite\OnsiteKkaTellerKas;
use App\Models\Onsite\OnsiteKkaKredit;
use App\Models\Onsite\OnsiteKkaDpk;
use App\Models\Onsite\OnsiteKkaBiaya;
use App\Models\Onsite\OnsiteKkaAtm;
use App\Models\Onsite\OnsiteKkaPengaduan;
use App\Models\DailyRegister\DailyRegisterFinding;
use App\Models\Offsite\KkaFinding;
use App\Models\Sop07RegisterTl;
use App\Models\Sop09TemuanIndikasiCabang;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RawMetricAutoSyncService
{
    public function __construct(
        private RiskScoringService $scoringService,
        private FinalAuditPlanService $planService
    ) {}

    /**
     * Menghitung nilai metrik mentah (27 indikator) secara otomatis dari seluruh tabel operasional
     */
    public function calculate(Unit $unit, int $period): array
    {
        // 1. Kumpulkan kode unit & nama unit untuk pencocokan relasional
        $unitCodes = array_filter([$unit->unit_code, ltrim($unit->unit_code, '0'), str_pad($unit->unit_code, 3, '0', STR_PAD_LEFT)]);
        $cabangCodes = $unit->cabang ? array_filter([
            $unit->cabang->kode_cabang,
            str_replace('BS-', '', $unit->cabang->kode_cabang),
            ltrim(str_replace('BS-', '', $unit->cabang->kode_cabang), '0'),
            str_pad(ltrim(str_replace('BS-', '', $unit->cabang->kode_cabang), '0'), 3, '0', STR_PAD_LEFT)
        ]) : [];
        $allCodes = array_values(array_unique(array_merge($unitCodes, $cabangCodes)));

        $cleanUnitName = trim(preg_replace('/^(kantor cabang pembantu|kantor cabang utama|kantor cabang|kcp|kc|kantor pusat)\s+/i', '', $unit->unit_name));
        $unitNames = array_unique(array_filter([$unit->unit_name, $cleanUnitName, $unit->cabang?->nama_cabang]));

        // Query referensi SOP 09 Indikasi Fraud
        $sop09 = Sop09TemuanIndikasiCabang::where(function($q) use ($allCodes, $unitNames) {
            $q->whereIn('kode_cabang', $allCodes);
            foreach ($unitNames as $nm) {
                $q->orWhere('nama_cabang', 'like', "%{$nm}%");
            }
        })->first();

        // ----------------------------------------------------
        // BIDANG A — RIWAYAT PEMERIKSAAN RA
        // ----------------------------------------------------
        $priorOnsiteVisits = OnsiteVisit::whereIn('kode_unit', $allCodes)
            ->where(function($q) use ($period) {
                $q->whereYear('tanggal_mulai', $period - 1)
                  ->orWhere('periode', 'like', ($period - 1) . '%');
            })->pluck('id');
        
        $priorOnsiteFindings = 0;
        if ($priorOnsiteVisits->isNotEmpty()) {
            $priorOnsiteFindings = OnsiteKkaFinding::whereIn('onsite_visit_id', $priorOnsiteVisits)
                ->where(function($q) {
                    $q->where('hasil_uji', 'Ada Exception')
                      ->orWhere('risk_level', '!=', 'Low');
                })->count();
        }
        if ($priorOnsiteFindings === 0) {
            $prevRaw = RawMetric::where('unit_id', $unit->id)->where('period', $period - 1)->first();
            $priorOnsiteFindings = $prevRaw ? (int)$prevRaw->significant_findings : 0;
        }

        $significantFindings = OnsiteKkaFinding::whereIn('kode_unit', $allCodes)
            ->where(function($q) {
                $q->whereIn('risk_level', ['High', 'Critical', 'Kritis'])
                  ->orWhereIn('risk_final', ['High', 'Critical', 'Kritis'])
                  ->orWhere('critical_trigger', 'Ya');
            })->count();

        $repeatFindings = OnsiteKkaFinding::whereIn('kode_unit', $allCodes)
            ->where(function($q) {
                $q->where('alasan_sampling', 'like', '%berulang%')
                  ->orWhere('jenis_sampling', 'Certainty');
            })->count();

        $offsiteDev = DailyRegisterFinding::whereIn('kode_unit', $allCodes)
            ->where(function($q) {
                $q->where('hasil_uji', 'Ada Exception')
                  ->orWhere(function($sq) {
                      $sq->whereNotNull('risk_final')->where('risk_final', '!=', 'Low');
                  });
            })->count()
            + KkaFinding::whereIn('kode_unit', $allCodes)
            ->where(function($q) {
                $q->where('hasil_uji', 'Ada Exception')
                  ->orWhere(function($sq) {
                      $sq->whereNotNull('risk_final')->where('risk_final', '!=', 'Low');
                  });
            })->count();

        $offsiteDevSig = DailyRegisterFinding::whereIn('kode_unit', $allCodes)
            ->where(function($q) {
                $q->whereIn('risk_final', ['High', 'Critical', 'Kritis'])
                  ->orWhere('critical_trigger', 'Ya');
            })->count()
            + KkaFinding::whereIn('kode_unit', $allCodes)
            ->where(function($q) {
                $q->whereIn('risk_final', ['High', 'Critical', 'Kritis'])
                  ->orWhere('critical_trigger', 'Ya');
            })->count();

        $offsiteDevRepeat = DailyRegisterFinding::whereIn('kode_unit', $allCodes)
            ->where(function($q) {
                $q->where('kategori', 'like', '%berulang%')
                  ->orWhere('rincian', 'like', '%berulang%')
                  ->orWhere('deskripsi', 'like', '%berulang%');
            })->count();

        $lastVisit = OnsiteVisit::whereIn('kode_unit', $allCodes)
            ->orderByDesc('tanggal_selesai')
            ->first();
        $monthsSinceLastOnsite = 12;
        if ($lastVisit && $lastVisit->tanggal_mulai) {
            $monthsSinceLastOnsite = max(1, Carbon::parse($lastVisit->tanggal_mulai)->diffInMonths(now()));
        }

        // ----------------------------------------------------
        // BIDANG B — KAS/TELLER & OPERASIONAL
        // ----------------------------------------------------
        $reversalTxn = (int)($sop09?->reversal_teller ?? 0)
            + DailyRegisterFinding::whereIn('kode_unit', $allCodes)
                ->whereIn('source_sheet', ['KKA_Teller_Kas', 'KKA_Teller'])
                ->where(function($q) {
                    $q->where('rincian', 'like', '%reversal%')
                      ->orWhere('deskripsi', 'like', '%reversal%');
                })->count()
            + OnsiteKkaTellerKas::whereHas('visit', fn($q) => $q->whereIn('kode_unit', $allCodes))
                ->where('alasan_sampling', 'like', '%reversal%')
                ->count();

        $cashDiscrepancy = (int)($sop09?->selisih_kas_teller ?? 0)
            + (int)($sop09?->selisih_kas_khasanah ?? 0)
            + OnsiteKkaTellerKas::whereHas('visit', fn($q) => $q->whereIn('kode_unit', $allCodes))
                ->where(function($q) {
                    $q->where('alasan_sampling', 'like', '%selisih%')
                      ->orWhere('ket_tx', 'like', '%selisih%');
                })->count();

        $unusualCostJournal = (int)($sop09?->kas_kecil_melebihi_limit ?? 0)
            + (int)($sop09?->kas_kecil_belum_selesai ?? 0)
            + DailyRegisterFinding::whereIn('kode_unit', $allCodes)
                ->whereIn('source_sheet', ['KKA_Biaya_Beban', 'KKA_Biaya'])
                ->where(function($q) {
                    $q->where('hasil_uji', 'Ada Exception')
                      ->orWhere('risk_final', '!=', 'Low');
                })->count()
            + OnsiteKkaBiaya::whereHas('visit', fn($q) => $q->whereIn('kode_unit', $allCodes))
                ->where(function($q) {
                    $q->where('hasil_uji', 'Ada Exception')
                      ->orWhere('status_uji', 'Ada Exception')
                      ->orWhere('risk_level', '!=', 'Low');
                })->count();

        $largeRiskyCashTxn = (int)($sop09?->trx_tidak_wajar_pegawai ?? 0)
            + (int)($sop09?->setoran_non_tunai_pegawai ?? 0)
            + DailyRegisterFinding::whereIn('kode_unit', $allCodes)
                ->whereIn('source_sheet', ['KKA_Teller_Kas', 'KKA_Teller'])
                ->where('nominal_terkait', '>=', 100000000)
                ->count()
            + OnsiteKkaTellerKas::whereHas('visit', fn($q) => $q->whereIn('kode_unit', $allCodes))
                ->where('jumlah_tx', '>=', 100000000)
                ->count();

        // ----------------------------------------------------
        // BIDANG C — CS/DPK/APU-PPT
        // ----------------------------------------------------
        $dpkAnomaly = (int)($sop09?->trx_rekening_pasif ?? 0)
            + (int)($sop09?->trx_rekening_titipan ?? 0)
            + DailyRegisterFinding::whereIn('kode_unit', $allCodes)
                ->whereIn('source_sheet', ['KKA_DPK_Layanan', 'KKA_DPK'])
                ->where(function($q) {
                    $q->where('hasil_uji', 'Ada Exception')
                      ->orWhere('risk_final', '!=', 'Low');
                })->count()
            + OnsiteKkaDpk::whereHas('visit', fn($q) => $q->whereIn('kode_unit', $allCodes))
                ->where(function($q) {
                    $q->where('hasil_uji', 'Ada Exception')
                      ->orWhere('status_uji', 'Ada Exception')
                      ->orWhere('risk_level', '!=', 'Low');
                })->count();

        $overdueComplaints = (int)($sop09?->lonjakan_pengaduan_sla ?? 0)
            + DailyRegisterFinding::whereIn('kode_unit', $allCodes)
                ->whereIn('source_sheet', ['KKA_Pengaduan', 'KKA_Pengaduan_Nasabah'])
                ->where(function($q) {
                    $q->where('lama_hari_kerja', '>', 20)
                      ->orWhere('rincian', 'like', '%lewat sla%')
                      ->orWhere('hasil_uji', 'Ada Exception')
                      ->orWhere('risk_final', '!=', 'Low');
                })->count()
            + OnsiteKkaPengaduan::whereHas('visit', fn($q) => $q->whereIn('kode_unit', $allCodes))
                ->where(function($q) {
                    $q->where('hasil_uji', 'Ada Exception')
                      ->orWhere('status_uji', 'Ada Exception')
                      ->orWhere('risk_level', '!=', 'Low');
                })->count();

        $incompleteCddEdd = DailyRegisterFinding::whereIn('kode_unit', $allCodes)
            ->whereIn('source_sheet', ['KKA_DPK_Layanan', 'KKA_DPK'])
            ->where(function($q) {
                $q->where('rincian', 'like', '%cdd%')
                  ->orWhere('rincian', 'like', '%edd%')
                  ->orWhere('rincian', 'like', '%cif%');
            })->count()
            + OnsiteKkaDpk::whereHas('visit', fn($q) => $q->whereIn('kode_unit', $allCodes))
                ->where('alasan_sampling', 'like', '%cdd%')
                ->count();

        // ----------------------------------------------------
        // BIDANG D — KREDIT
        // ----------------------------------------------------
        $debtorsCol35 = (int)($sop09?->restrukturisasi_berulang ?? 0)
            + OnsiteKkaKredit::whereHas('visit', fn($q) => $q->whereIn('kode_unit', $allCodes))
                ->where(function($q) {
                    $q->where('alasan_sampling', 'like', '%kol%')
                      ->orWhere('alasan_sampling', 'like', '%3%')
                      ->orWhere('alasan_sampling', 'like', '%4%')
                      ->orWhere('alasan_sampling', 'like', '%5%');
                })->count();

        $totalDebitur = OnsiteKkaKredit::whereHas('visit', fn($q) => $q->whereIn('kode_unit', $allCodes))->count();
        $nplRatio = 0.0;
        if ($debtorsCol35 > 0) {
            $nplRatio = min(1.0, round($debtorsCol35 / max(1, $totalDebitur) * 0.05, 4));
            if ($nplRatio === 0.0) $nplRatio = 0.025;
        }

        $creditDeviation = (int)($sop09?->pencairan_tanpa_dokumen ?? 0)
            + DailyRegisterFinding::whereIn('kode_unit', $allCodes)
                ->where('source_sheet', 'KKA_Kredit')
                ->where(function($q) {
                    $q->where('hasil_uji', 'Ada Exception')
                      ->orWhere('risk_final', '!=', 'Low');
                })->count()
            + OnsiteKkaKredit::whereHas('visit', fn($q) => $q->whereIn('kode_unit', $allCodes))
                ->where(function($q) {
                    $q->where('hasil_uji', 'Ada Exception')
                      ->orWhere('status_uji', 'Ada Exception')
                      ->orWhere('risk_level', '!=', 'Low');
                })->count();

        // ----------------------------------------------------
        // BIDANG E — TI/ATM
        // ----------------------------------------------------
        $atmDispute = (int)($sop09?->selisih_kas_crm_atm ?? 0)
            + DailyRegisterFinding::whereIn('kode_unit', $allCodes)
                ->whereIn('source_sheet', ['KKA_Kartu_ATM', 'KKA_ATM'])
                ->where(function($q) {
                    $q->where('hasil_uji', 'Ada Exception')
                      ->orWhere('risk_final', '!=', 'Low');
                })->count()
            + OnsiteKkaAtm::whereHas('visit', fn($q) => $q->whereIn('kode_unit', $allCodes))
                ->where(function($q) {
                    $q->where('hasil_uji', 'Ada Exception')
                      ->orWhere('status_uji', 'Ada Exception')
                      ->orWhere('risk_level', '!=', 'Low');
                })->count();

        $atmDowntimeHours = 0.0;

        $criticalTiIncident = (int)($sop09?->skimming_atm ?? 0)
            + (int)($sop09?->sharing_user_id ?? 0)
            + DailyRegisterFinding::whereIn('kode_unit', $allCodes)
                ->whereIn('source_sheet', ['KKA_Kartu_ATM', 'KKA_ATM'])
                ->where('risk_final', 'Critical')
                ->count();

        $unusualUserReset = (int)($sop09?->sharing_user_id ?? 0)
            + DailyRegisterFinding::whereIn('kode_unit', $allCodes)
                ->where(function($q) {
                    $q->where('rincian', 'like', '%reset%')
                      ->orWhere('deskripsi', 'like', '%password%');
                })->count();

        // ----------------------------------------------------
        // BIDANG F — MONITORING TINDAK LANJUT (SOP 07)
        // ----------------------------------------------------
        $tlQuery = Sop07RegisterTl::where(function($q) use ($allCodes, $unitNames) {
            $q->whereIn('kode_unit', $allCodes);
            foreach ($unitNames as $nm) {
                $q->orWhere('nama_unit', 'like', "%{$nm}%");
            }
        });

        $raOnsiteTlOverdue = (clone $tlQuery)->where(function($q) {
            $q->where('sumber_tl', 'like', '%Onsite%')
              ->orWhere('sumber_tl', 'like', '%Surprise%');
        })->where('status_final', '!=', 'CLOSED')->where(function($q) {
            $q->where('aging_bucket', 'OVERDUE')
              ->orWhere('target_penyelesaian', '<', now()->toDateString());
        })->count();

        $raOffsiteTlOverdue = (clone $tlQuery)->where(function($q) {
            $q->where('sumber_tl', 'like', '%Offsite%')
              ->orWhere('sumber_tl', 'like', '%Daily%');
        })->where('status_final', '!=', 'CLOSED')->where(function($q) {
            $q->where('aging_bucket', 'OVERDUE')
              ->orWhere('target_penyelesaian', '<', now()->toDateString());
        })->count();

        $skaiTlOverdue = (clone $tlQuery)->where('sumber_tl', 'like', '%SKAI%')
            ->where('status_final', '!=', 'CLOSED')->where(function($q) {
                $q->where('aging_bucket', 'OVERDUE')
                  ->orWhere('target_penyelesaian', '<', now()->toDateString());
            })->count();

        $regulatorTlOverdue = (clone $tlQuery)->where(function($q) {
            $q->where('sumber_tl', 'like', '%OJK%')
              ->orWhere('sumber_tl', 'like', '%Regulator%')
              ->orWhere('sumber_tl', 'like', '%Bank Indonesia%');
        })->where('status_final', '!=', 'CLOSED')->where(function($q) {
            $q->where('aging_bucket', 'OVERDUE')
              ->orWhere('target_penyelesaian', '<', now()->toDateString());
        })->count();

        $kapTlOverdue = (clone $tlQuery)->where('sumber_tl', 'like', '%KAP%')
            ->where('status_final', '!=', 'CLOSED')->where(function($q) {
                $q->where('aging_bucket', 'OVERDUE')
                  ->orWhere('target_penyelesaian', '<', now()->toDateString());
            })->count();

        $tlRecords = (clone $tlQuery)->get();
        $totalDays = 0;
        $countWithDays = 0;
        foreach ($tlRecords as $tl) {
            if ($tl->tanggal_laporan && $tl->updated_at) {
                $days = Carbon::parse($tl->tanggal_laporan)->diffInDays($tl->updated_at);
                $totalDays += $days;
                $countWithDays++;
            }
        }
        $avgResponseDays = $countWithDays > 0 ? round($totalDays / $countWithDays, 2) : 2.5;

        $totalTlCount = $tlRecords->count();
        $closedTlCount = $tlRecords->where('status_final', 'CLOSED')->count();
        $qualityScore = 3;
        if ($totalTlCount > 0) {
            $pct = ($closedTlCount / $totalTlCount) * 100;
            if ($pct >= 80) $qualityScore = 4;
            elseif ($pct >= 60) $qualityScore = 3;
            elseif ($pct >= 40) $qualityScore = 2;
            elseif ($pct >= 20) $qualityScore = 1;
            else $qualityScore = 0;
        }

        // Terapkan penyesuaian jenis unit kerja
        if (in_array($unit->unit_type, ['Payment Point'])) {
            $dpkAnomaly = 0;
            $overdueComplaints = 0;
            $incompleteCddEdd = 0;
            $debtorsCol35 = 0;
            $nplRatio = 0.0;
            $creditDeviation = 0;
            $atmDispute = 0;
            $atmDowntimeHours = 0.0;
            $criticalTiIncident = 0;
            $unusualUserReset = 0;
        } elseif (in_array($unit->unit_type, ['KCPLK'])) {
            $debtorsCol35 = 0;
            $nplRatio = 0.0;
            $creditDeviation = 0;
        }

        return [
            // Bidang A
            'prior_onsite_findings'        => (int)$priorOnsiteFindings,
            'significant_findings'         => (int)$significantFindings,
            'repeat_findings'              => (int)$repeatFindings,
            'offsite_deviation'            => (int)$offsiteDev,
            'offsite_deviation_significant'=> (int)$offsiteDevSig,
            'offsite_deviation_repeat'     => (int)$offsiteDevRepeat,
            'months_since_last_onsite'     => (int)$monthsSinceLastOnsite,

            // Bidang B
            'reversal_correction_txn'      => (int)$reversalTxn,
            'cash_discrepancy'             => (int)$cashDiscrepancy,
            'unusual_cost_journal'         => (int)$unusualCostJournal,
            'large_risky_cash_txn'         => (int)$largeRiskyCashTxn,

            // Bidang C
            'dpk_anomaly'                  => (int)$dpkAnomaly,
            'overdue_complaints'           => (int)$overdueComplaints,
            'incomplete_cdd_edd'           => (int)$incompleteCddEdd,

            // Bidang D
            'debtors_col_3_5'              => (int)$debtorsCol35,
            'npl_ratio'                    => (float)$nplRatio,
            'credit_deviation'             => (int)$creditDeviation,

            // Bidang E
            'atm_dispute'                  => (int)$atmDispute,
            'atm_downtime_hours'           => (float)$atmDowntimeHours,
            'critical_ti_incident'         => (int)$criticalTiIncident,
            'unusual_user_reset'           => (int)$unusualUserReset,

            // Bidang F
            'ra_onsite_tl_overdue'         => (int)$raOnsiteTlOverdue,
            'ra_offsite_tl_overdue'        => (int)$raOffsiteTlOverdue,
            'skai_tl_overdue'              => (int)$skaiTlOverdue,
            'regulator_tl_overdue'         => (int)$regulatorTlOverdue,
            'kap_tl_overdue'               => (int)$kapTlOverdue,
            'avg_response_days'            => (float)$avgResponseDays,
            'tl_response_quality'          => (int)$qualityScore,
        ];
    }

    /**
     * Menyediakan label sumber data untuk tiap indikator agar transparan bagi auditor
     */
    public function getMetricSourceLabels(): array
    {
        return [
            'prior_onsite_findings'        => 'Kunjungan Onsite Tahun Lalu (SOP 04)',
            'significant_findings'         => 'Temuan Risiko High / Kritis Onsite & Offsite',
            'repeat_findings'              => 'Sampling Temuan Berulang (Certainty)',
            'offsite_deviation'            => 'Register Temuan Harian & Offsite (SOP 02)',
            'offsite_deviation_significant'=> 'Temuan Offsite Risiko High / Critical',
            'offsite_deviation_repeat'     => 'Temuan Offsite Berulang',
            'months_since_last_onsite'     => 'Log Jadwal Terakhir Kunjungan Onsite',

            'reversal_correction_txn'      => 'SOP 09 Indikasi Fraud & KKA Teller',
            'cash_discrepancy'             => 'SOP 05 Fisik Kas & SOP 09 Indikasi Fraud',
            'unusual_cost_journal'         => 'KKA Biaya/Beban & Limit Kas Kecil',
            'large_risky_cash_txn'         => 'Transaksi Tunai >= 100 Juta & CTR',

            'dpk_anomaly'                  => 'KKA DPK & Rekening Pasif/Titipan',
            'overdue_complaints'           => 'Log Keluhan Nasabah Lewat SLA (SOP 03)',
            'incomplete_cdd_edd'           => 'Sampling Berkas CIF / CDD-EDD',

            'debtors_col_3_5'              => 'KKA Kredit Debitur Bermasalah Kol 3-5',
            'npl_ratio'                    => 'Rasio NPL Estimasi Populasi Kredit',
            'credit_deviation'             => 'Penyimpangan Berkas/Pencairan Kredit',

            'atm_dispute'                  => 'Selisih Kas Opname CRM/ATM (SOP 05)',
            'atm_downtime_hours'           => 'Log Gangguan Mesin ATM',
            'critical_ti_incident'         => 'Insiden Skimming & Sharing ID User',
            'unusual_user_reset'           => 'Log Reset Password & Buka Blokir',

            'ra_onsite_tl_overdue'         => 'SOP 07 Register TL (Sumber Onsite)',
            'ra_offsite_tl_overdue'        => 'SOP 07 Register TL (Sumber Offsite)',
            'skai_tl_overdue'              => 'SOP 07 Register TL (Sumber SKAI)',
            'regulator_tl_overdue'         => 'SOP 07 Register TL (OJK / BI Overdue)',
            'kap_tl_overdue'               => 'SOP 07 Register TL (KAP Eksternal)',
            'avg_response_days'            => 'Rata-rata Hari Pemenuhan Bukti TL',
            'tl_response_quality'          => 'Persentase Closed TL (Skala 0-4)',
        ];
    }

    /**
     * Sinkronisasi dan simpan otomatis satu unit, lalu jalankan recompute scoring
     */
    public function syncUnit(Unit $unit, int $period, ?int $userId = null): RawMetric
    {
        $calculated = $this->calculate($unit, $period);
        $calculated['unit_id']  = $unit->id;
        $calculated['period']   = $period;
        $calculated['input_by'] = $userId ?: auth()->id() ?: 1;

        $rawMetric = RawMetric::updateOrCreate(
            ['unit_id' => $unit->id, 'period' => $period],
            $calculated
        );

        try {
            $this->scoringService->recompute($unit, $period);
            $this->planService->generate($unit, $period);
        } catch (\Throwable $e) {
            Log::warning("Recompute scoring gagal saat auto-sync unit {$unit->unit_code}: " . $e->getMessage());
        }

        return $rawMetric;
    }

    /**
     * Sinkronisasi massal seluruh unit aktif untuk suatu periode
     */
    public function syncAllUnits(int $period, ?int $userId = null): array
    {
        $units = Unit::where('is_active', true)->get();
        $synced = 0;
        $failed = 0;

        foreach ($units as $unit) {
            try {
                $this->syncUnit($unit, $period, $userId);
                $synced++;
            } catch (\Throwable $e) {
                Log::error("Auto-sync error on unit {$unit->unit_code}: " . $e->getMessage());
                $failed++;
            }
        }

        return [
            'total'  => $units->count(),
            'synced' => $synced,
            'failed' => $failed,
            'period' => $period,
        ];
    }
}
