<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterSetupSeeder extends Seeder
{
    public function run(): void
    {
        // ===================== FIELD WEIGHTS (A.1) =====================
        $fieldWeights = [
            // Bidang A - Riwayat RA
            ['metric_key' => 'prior_onsite_findings',       'bidang' => 'riwayat_ra',   'weight' => 0.15, 'label' => 'Temuan Onsite Tahun Lalu'],
            ['metric_key' => 'significant_findings',        'bidang' => 'riwayat_ra',   'weight' => 0.20, 'label' => 'Temuan Signifikan'],
            ['metric_key' => 'repeat_findings',             'bidang' => 'riwayat_ra',   'weight' => 0.15, 'label' => 'Temuan Berulang'],
            ['metric_key' => 'offsite_deviation',           'bidang' => 'riwayat_ra',   'weight' => 0.10, 'label' => 'Penyimpangan Pada Offsite'],
            ['metric_key' => 'offsite_deviation_significant','bidang' => 'riwayat_ra',  'weight' => 0.10, 'label' => 'Penyimpangan Offsite Signifikan'],
            ['metric_key' => 'offsite_deviation_repeat',    'bidang' => 'riwayat_ra',   'weight' => 0.05, 'label' => 'Penyimpangan Offsite Berulang'],
            ['metric_key' => 'months_since_last_onsite',    'bidang' => 'riwayat_ra',   'weight' => 0.25, 'label' => 'Lama Sejak Onsite (Bulan)'],
            // Bidang B - Kas/Teller
            ['metric_key' => 'reversal_correction_txn',     'bidang' => 'kas_teller',   'weight' => 0.25, 'label' => 'Transaksi Reversal/Koreksi'],
            ['metric_key' => 'cash_discrepancy',            'bidang' => 'kas_teller',   'weight' => 0.30, 'label' => 'Selisih Kas'],
            ['metric_key' => 'unusual_cost_journal',        'bidang' => 'kas_teller',   'weight' => 0.25, 'label' => 'Biaya/Jurnal Tidak Lazim'],
            ['metric_key' => 'large_risky_cash_txn',        'bidang' => 'kas_teller',   'weight' => 0.20, 'label' => 'Transaksi Tunai Besar Berisiko'],
            // Bidang C - CS/DPK
            ['metric_key' => 'dpk_anomaly',                 'bidang' => 'cs_dpk',       'weight' => 0.35, 'label' => 'Anomali Pengelolaan DPK'],
            ['metric_key' => 'overdue_complaints',          'bidang' => 'cs_dpk',       'weight' => 0.25, 'label' => 'Pengaduan Nasabah Overdue'],
            ['metric_key' => 'incomplete_cdd_edd',          'bidang' => 'cs_dpk',       'weight' => 0.40, 'label' => 'Pengkinian Data/CDD-EDD Belum Selesai'],
            // Bidang D - Kredit
            ['metric_key' => 'debtors_col_3_5',             'bidang' => 'kredit',       'weight' => 0.35, 'label' => 'Jumlah Debitur Kol 3-5'],
            ['metric_key' => 'npl_ratio',                   'bidang' => 'kredit',       'weight' => 0.40, 'label' => 'Rasio NPL'],
            ['metric_key' => 'credit_deviation',            'bidang' => 'kredit',       'weight' => 0.25, 'label' => 'Penyimpangan/Deviasi Kredit'],
            // Bidang E - TI/ATM
['metric_key' => 'atm_dispute',                 'bidang' => 'ti_atm',       'weight' => 0.30, 'label' => 'Selisih/Dispute ATM'],
            ['metric_key' => 'atm_downtime_hours',          'bidang' => 'ti_atm',       'weight' => 0.20, 'label' => 'Downtime ATM (Jam)'],
            ['metric_key' => 'critical_ti_incident',        'bidang' => 'ti_atm',       'weight' => 0.30, 'label' => 'Insiden TI Kritikal'],
            ['metric_key' => 'unusual_user_reset',          'bidang' => 'ti_atm',       'weight' => 0.20, 'label' => 'Reset/Buka Blokir User Tidak Lazim'],
            // Bidang F - Monitoring TL
            ['metric_key' => 'ra_onsite_tl_overdue',        'bidang' => 'monitoring_tl','weight' => 0.15, 'label' => 'Temuan RA Onsite Overdue'],
            ['metric_key' => 'ra_offsite_tl_overdue',       'bidang' => 'monitoring_tl','weight' => 0.10, 'label' => 'Temuan RA Offsite Overdue'],
            ['metric_key' => 'skai_tl_overdue',             'bidang' => 'monitoring_tl','weight' => 0.20, 'label' => 'Temuan SKAI Overdue'],
            ['metric_key' => 'regulator_tl_overdue',        'bidang' => 'monitoring_tl','weight' => 0.25, 'label' => 'Temuan Regulator Overdue'],
            ['metric_key' => 'kap_tl_overdue',              'bidang' => 'monitoring_tl','weight' => 0.10, 'label' => 'Temuan KAP Overdue'],
            ['metric_key' => 'avg_response_days',           'bidang' => 'monitoring_tl','weight' => 0.10, 'label' => 'Rata-Rata Hari Respons TL'],
        ];
foreach ($fieldWeights as $fw) {
            DB::table('field_weights')->updateOrInsert(['metric_key' => $fw['metric_key']], array_merge($fw, ['created_at' => now(), 'updated_at' => now()]));
        }

        // Hapus bobot yang TIDAK ada di SPEC A.1 (tl_response_quality = checklist kualitatif, tanpa bobot numerik)
        DB::table('field_weights')->where('metric_key', 'tl_response_quality')->delete();

        // ===================== BIDANG WEIGHTS (A.4) =====================
        $bidangWeights = [
            // KC
            ['unit_type' => 'KC',            'bidang' => 'riwayat_ra',   'weight' => 0.20],
            ['unit_type' => 'KC',            'bidang' => 'kas_teller',   'weight' => 0.15],
            ['unit_type' => 'KC',            'bidang' => 'cs_dpk',       'weight' => 0.15],
            ['unit_type' => 'KC',            'bidang' => 'kredit',       'weight' => 0.25],
            ['unit_type' => 'KC',            'bidang' => 'ti_atm',       'weight' => 0.10],
            ['unit_type' => 'KC',            'bidang' => 'monitoring_tl','weight' => 0.15],
            // KCU (alias KC)
            ['unit_type' => 'KCU',           'bidang' => 'riwayat_ra',   'weight' => 0.20],
            ['unit_type' => 'KCU',           'bidang' => 'kas_teller',   'weight' => 0.15],
            ['unit_type' => 'KCU',           'bidang' => 'cs_dpk',       'weight' => 0.15],
            ['unit_type' => 'KCU',           'bidang' => 'kredit',       'weight' => 0.25],
            ['unit_type' => 'KCU',           'bidang' => 'ti_atm',       'weight' => 0.10],
            ['unit_type' => 'KCU',           'bidang' => 'monitoring_tl','weight' => 0.15],
            // KCP
            ['unit_type' => 'KCP',           'bidang' => 'riwayat_ra',   'weight' => 0.20],
            ['unit_type' => 'KCP',           'bidang' => 'kas_teller',   'weight' => 0.20],
            ['unit_type' => 'KCP',           'bidang' => 'cs_dpk',       'weight' => 0.15],
            ['unit_type' => 'KCP',           'bidang' => 'kredit',       'weight' => 0.25],
            ['unit_type' => 'KCP',           'bidang' => 'ti_atm',       'weight' => 0.05],
            ['unit_type' => 'KCP',           'bidang' => 'monitoring_tl','weight' => 0.15],
            // KCPLK
            ['unit_type' => 'KCPLK',         'bidang' => 'riwayat_ra',   'weight' => 0.25],
            ['unit_type' => 'KCPLK',         'bidang' => 'kas_teller',   'weight' => 0.30],
            ['unit_type' => 'KCPLK',         'bidang' => 'cs_dpk',       'weight' => 0.20],
            ['unit_type' => 'KCPLK',         'bidang' => 'kredit',       'weight' => 0.00],
            ['unit_type' => 'KCPLK',         'bidang' => 'ti_atm',       'weight' => 0.05],
            ['unit_type' => 'KCPLK',         'bidang' => 'monitoring_tl','weight' => 0.20],
// Payment Point — hanya 3 bidang relevan (Riwayat RA, Teller, Monitoring TL)
            // Dinormalkan agar total bobot = 100% (tanpa cs_dpk, kredit, ti_atm)
            ['unit_type' => 'Payment Point', 'bidang' => 'riwayat_ra',   'weight' => 0.2105],
            ['unit_type' => 'Payment Point', 'bidang' => 'kas_teller',   'weight' => 0.5263],
            ['unit_type' => 'Payment Point', 'bidang' => 'cs_dpk',       'weight' => 0.0000],
            ['unit_type' => 'Payment Point', 'bidang' => 'kredit',       'weight' => 0.0000],
            ['unit_type' => 'Payment Point', 'bidang' => 'ti_atm',       'weight' => 0.0000],
            ['unit_type' => 'Payment Point', 'bidang' => 'monitoring_tl','weight' => 0.2632],
        ];
        foreach ($bidangWeights as $bw) {
            DB::table('bidang_weights')->updateOrInsert(
                ['unit_type' => $bw['unit_type'], 'bidang' => $bw['bidang']],
                array_merge($bw, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // ===================== DATA CODES (A.2) =====================
        $dataCodes = [
            ['data_code' => 'KAS_POSISI',          'area' => 'Teller/Kas',      'daily_offsite_capable' => 'Ya',   'default_frequency' => 'H+1',         'description' => 'Posisi Kas'],
            ['data_code' => 'REVERSAL',             'area' => 'Teller/Kas',      'daily_offsite_capable' => 'Ya',   'default_frequency' => 'H+1',         'description' => 'Transaksi Reversal'],
            ['data_code' => 'OVERRIDE',             'area' => 'Teller/Kas',      'daily_offsite_capable' => 'Ya',   'default_frequency' => 'H+1',         'description' => 'Override Transaksi'],
            ['data_code' => 'SELISIH_KAS',          'area' => 'Teller/Kas',      'daily_offsite_capable' => 'Ya',   'default_frequency' => 'H+1',         'description' => 'Selisih Kas'],
            ['data_code' => 'BIAYA_HARIAN',         'area' => 'Biaya/Jurnal',    'daily_offsite_capable' => 'Ya',   'default_frequency' => 'H+1',         'description' => 'Biaya Harian'],
            ['data_code' => 'JURNAL_MANUAL',        'area' => 'Biaya/Jurnal',    'daily_offsite_capable' => 'Ya',   'default_frequency' => 'H+1',         'description' => 'Jurnal Manual'],
            ['data_code' => 'PENCAIRAN_KREDIT',     'area' => 'Kredit',          'daily_offsite_capable' => 'Ya',   'default_frequency' => 'H+1',         'description' => 'Pencairan Kredit'],
            ['data_code' => 'EXCEPTION_KREDIT',     'area' => 'Kredit',          'daily_offsite_capable' => 'Ya',   'default_frequency' => 'H+1',         'description' => 'Exception Kredit'],
            ['data_code' => 'PERUBAHAN_DATA',       'area' => 'CS/DPK',          'daily_offsite_capable' => 'Ya',   'default_frequency' => 'H+1',         'description' => 'Perubahan Data Nasabah'],
            ['data_code' => 'DORMANT_AKTIF',        'area' => 'CS/DPK',          'daily_offsite_capable' => 'Ya',   'default_frequency' => 'H+1',         'description' => 'Rekening Dormant Diaktifkan'],
            ['data_code' => 'KARTU_ATM_GANTI',      'area' => 'CS/DPK',          'daily_offsite_capable' => 'Ya',   'default_frequency' => 'H+1',         'description' => 'Penggantian Kartu ATM'],
            ['data_code' => 'ATM_SELISIH_DISPUTE',  'area' => 'ATM',             'daily_offsite_capable' => 'Ya',   'default_frequency' => 'H+1',         'description' => 'Selisih/Dispute ATM'],
            ['data_code' => 'APU_FDS_ALERT',        'area' => 'APU-PPT/FDS',     'daily_offsite_capable' => 'Ya',   'default_frequency' => 'H+1',         'description' => 'Alert APU/FDS'],
            ['data_code' => 'TUNAI_BESAR_BERISIKO', 'area' => 'APU-PPT/FDS',     'daily_offsite_capable' => 'Ya',   'default_frequency' => 'H+1',         'description' => 'Transaksi Tunai Besar Berisiko'],
            ['data_code' => 'TI_EVENT',             'area' => 'TI Event',        'daily_offsite_capable' => 'Ya',   'default_frequency' => 'Event-based',  'description' => 'Event TI'],
            ['data_code' => 'PENGADUAN_BARU',       'area' => 'Pengaduan',       'daily_offsite_capable' => 'Ya',   'default_frequency' => 'H+1',         'description' => 'Pengaduan Nasabah Baru'],
            ['data_code' => 'ASET_FISIK',           'area' => 'Aset',            'daily_offsite_capable' => 'Tidak','default_frequency' => 'Onsite',      'description' => 'Pemeriksaan Aset Fisik'],
            ['data_code' => 'DOKUMEN_AGUNAN',       'area' => 'Dokumen/Agunan',  'daily_offsite_capable' => 'Tidak','default_frequency' => 'Onsite',      'description' => 'Dokumen Agunan Kredit'],
            ['data_code' => 'CCTV_ALARM',           'area' => 'TI Fisik',        'daily_offsite_capable' => 'Tidak','default_frequency' => 'Onsite',      'description' => 'CCTV dan Alarm Fisik'],
        ];
        foreach ($dataCodes as $dc) {
            DB::table('data_codes')->updateOrInsert(['data_code' => $dc['data_code']], array_merge($dc, ['created_at' => now(), 'updated_at' => now()]));
        }

        // ===================== FREQUENCY MATRIX (A.3) =====================
        $freqMatrix = [
            ['risk_category' => 'High',             'unit_type' => 'KC',            'frequency_label' => 'Resident Daily Review + Trigger', 'visits_per_year' => 0,  'is_resident_daily_review' => true],
            ['risk_category' => 'High',             'unit_type' => 'KCU',           'frequency_label' => 'Resident Daily Review + Trigger', 'visits_per_year' => 0,  'is_resident_daily_review' => true],
            ['risk_category' => 'High',             'unit_type' => 'KCP',           'frequency_label' => 'Bulanan',                         'visits_per_year' => 12, 'is_resident_daily_review' => false],
            ['risk_category' => 'High',             'unit_type' => 'KCPLK',         'frequency_label' => 'Bulanan',                         'visits_per_year' => 12, 'is_resident_daily_review' => false],
            ['risk_category' => 'High',             'unit_type' => 'Payment Point', 'frequency_label' => 'Bulanan',                         'visits_per_year' => 12, 'is_resident_daily_review' => false],
            ['risk_category' => 'Moderate to High', 'unit_type' => 'KC',            'frequency_label' => 'Resident Daily Review',           'visits_per_year' => 0,  'is_resident_daily_review' => true],
            ['risk_category' => 'Moderate to High', 'unit_type' => 'KCU',           'frequency_label' => 'Resident Daily Review',           'visits_per_year' => 0,  'is_resident_daily_review' => true],
            ['risk_category' => 'Moderate to High', 'unit_type' => 'KCP',           'frequency_label' => 'Triwulanan',                      'visits_per_year' => 4,  'is_resident_daily_review' => false],
            ['risk_category' => 'Moderate to High', 'unit_type' => 'KCPLK',         'frequency_label' => 'Triwulanan',                      'visits_per_year' => 4,  'is_resident_daily_review' => false],
            ['risk_category' => 'Moderate to High', 'unit_type' => 'Payment Point', 'frequency_label' => 'Triwulanan',                      'visits_per_year' => 4,  'is_resident_daily_review' => false],
            ['risk_category' => 'Moderate',         'unit_type' => 'KC',            'frequency_label' => 'Resident Daily Review',           'visits_per_year' => 0,  'is_resident_daily_review' => true],
            ['risk_category' => 'Moderate',         'unit_type' => 'KCU',           'frequency_label' => 'Resident Daily Review',           'visits_per_year' => 0,  'is_resident_daily_review' => true],
            ['risk_category' => 'Moderate',         'unit_type' => 'KCP',           'frequency_label' => 'Semesteran',                      'visits_per_year' => 2,  'is_resident_daily_review' => false],
            ['risk_category' => 'Moderate',         'unit_type' => 'KCPLK',         'frequency_label' => 'Semesteran',                      'visits_per_year' => 2,  'is_resident_daily_review' => false],
            ['risk_category' => 'Moderate',         'unit_type' => 'Payment Point', 'frequency_label' => 'Semesteran',                      'visits_per_year' => 2,  'is_resident_daily_review' => false],
            ['risk_category' => 'Low to Moderate',  'unit_type' => 'KC',            'frequency_label' => 'Resident Daily Review',           'visits_per_year' => 0,  'is_resident_daily_review' => true],
            ['risk_category' => 'Low to Moderate',  'unit_type' => 'KCU',           'frequency_label' => 'Resident Daily Review',           'visits_per_year' => 0,  'is_resident_daily_review' => true],
            ['risk_category' => 'Low to Moderate',  'unit_type' => 'KCP',           'frequency_label' => 'Tahunan',                         'visits_per_year' => 1,  'is_resident_daily_review' => false],
            ['risk_category' => 'Low to Moderate',  'unit_type' => 'KCPLK',         'frequency_label' => 'Tidak Terjadwal',                 'visits_per_year' => 0,  'is_resident_daily_review' => false],
            ['risk_category' => 'Low to Moderate',  'unit_type' => 'Payment Point', 'frequency_label' => 'Tidak Terjadwal',                 'visits_per_year' => 0,  'is_resident_daily_review' => false],
            ['risk_category' => 'Low',              'unit_type' => 'KC',            'frequency_label' => 'Resident Daily Review',           'visits_per_year' => 0,  'is_resident_daily_review' => true],
            ['risk_category' => 'Low',              'unit_type' => 'KCU',           'frequency_label' => 'Resident Daily Review',           'visits_per_year' => 0,  'is_resident_daily_review' => true],
            ['risk_category' => 'Low',              'unit_type' => 'KCP',           'frequency_label' => 'Tidak Terjadwal',                 'visits_per_year' => 0,  'is_resident_daily_review' => false],
            ['risk_category' => 'Low',              'unit_type' => 'KCPLK',         'frequency_label' => 'Tidak Terjadwal',                 'visits_per_year' => 0,  'is_resident_daily_review' => false],
            ['risk_category' => 'Low',              'unit_type' => 'Payment Point', 'frequency_label' => 'Tidak Terjadwal',                 'visits_per_year' => 0,  'is_resident_daily_review' => false],
        ];
        foreach ($freqMatrix as $fm) {
            DB::table('frequency_matrix')->updateOrInsert(
                ['risk_category' => $fm['risk_category'], 'unit_type' => $fm['unit_type']],
                array_merge($fm, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // ===================== CALENDAR PARAMS (A.6) =====================
        $calendarParams = [
            ['param_key' => 'audit_plan_year',              'param_value' => '2027', 'description' => 'Tahun Audit Plan aktif'],
            ['param_key' => 'duration_bulanan',             'param_value' => '2',    'description' => 'Durasi kunjungan Bulanan (hari)'],
            ['param_key' => 'duration_triwulanan',          'param_value' => '5',    'description' => 'Durasi kunjungan Triwulanan (hari)'],
            ['param_key' => 'duration_semesteran',          'param_value' => '7',    'description' => 'Durasi kunjungan Semesteran (hari)'],
            ['param_key' => 'duration_tahunan',             'param_value' => '12',   'description' => 'Durasi kunjungan Tahunan (hari)'],
            ['param_key' => 'effort_offsite_per_unit',      'param_value' => '1',    'description' => 'Effort daily offsite per unit per bulan (hari)'],
            ['param_key' => 'warning_utilization_threshold','param_value' => '0.85', 'description' => 'Ambang warning utilisasi RA'],
            ['param_key' => 'effective_working_days',       'param_value' => '20',   'description' => 'Hari kerja efektif default per bulan'],
        ];
        foreach ($calendarParams as $cp) {
            DB::table('calendar_params')->updateOrInsert(['param_key' => $cp['param_key']], array_merge($cp, ['created_at' => now(), 'updated_at' => now()]));
        }
    }
}
