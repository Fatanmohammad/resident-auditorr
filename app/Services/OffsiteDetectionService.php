<?php

namespace App\Services;

use App\Models\{
    DumpTransaksiCbs,
    DumpDpkApuppt,
    DumpKredit,
    DumpBiayaBeban,
    DumpPengaduan,
    RuleEngine,
    RuleThreshold,
    StagingOffsite,
    WpOffsite
};
use DB;

class OffsiteDetectionService
{
    /**
     * Deteksi per baris - main detection logic
     * Implements §3 of spec: Langkah 1-8 detection flow
     */
    public function detectBaris(array $data, string $source, WpOffsite $wp): array
    {
        // Langkah 1: Gabungkan jadi Teks Deteksi
        $teksDeteksi = $this->buildDetectionText($data, $source);

        // Langkah 2-3: Cek flag dan hitung jumlah
        $flags = $this->checkFlags($teksDeteksi, $source);
        $jumlahFlagRisiko = $this->countRiskFlags($flags);

        // Langkah 4: Tentukan Area Review
        $areaReview = $this->determineArea($teksDeteksi, $flags, $source);

        // Langkah 5: Tentukan Risk Level
        $riskLevel = $this->determineRiskLevel($jumlahFlagRisiko, $flags);

        // Langkah 6: Case Pairing (jika CBS)
        $caseId = $source === 'CBS' ? $this->generateCaseId($data) : null;

        // Langkah 7: Tentukan tujuan KKA & apakah masuk review
        $kkaSheet = $this->determineKkaSheet($riskLevel, $areaReview);

        // Langkah 8: Flag untuk sampling (Low Risk)
        $perluKka = $jumlahFlagRisiko > 0 || $riskLevel === 'High';
        $perluKlarifikasi = $flags['whitelist'] ?? false;
        $perluEskalasi = $riskLevel === 'High';

        return [
            'flags' => $flags,
            'jumlah_flag_risiko' => $jumlahFlagRisiko,
            'area_review' => $areaReview,
            'risk_level' => $riskLevel,
            'case_id' => $caseId,
            'kka_sheet_tujuan' => $kkaSheet,
            'perlu_kka' => $perluKka,
            'perlu_klarifikasi' => $perluKlarifikasi,
            'perlu_eskalasi' => $perluEskalasi,
        ];
    }

    /**
     * Langkah 1: Build detection text from relevant columns
     */
    private function buildDetectionText(array $data, string $source): string
    {
        $parts = [];

        switch ($source) {
            case 'CBS':
                $parts = [
                    $data['kode_transaksi'] ?? '',
                    $data['nama_transaksi'] ?? '',
                    $data['no_referensi'] ?? '',
                    $data['user_maker'] ?? '',
                    (string)($data['nominal'] ?? ''),
                    $data['deskripsi_narasi'] ?? '',
                ];
                break;
            case 'DPK':
                $parts = [
                    $data['no_rekening'] ?? '',
                    $data['nama_nasabah'] ?? '',
                    $data['produk'] ?? '',
                    $data['status_rekening'] ?? '',
                ];
                break;
            case 'Kredit':
                $parts = [
                    $data['no_rekening_kredit'] ?? '',
                    $data['nama_debitur'] ?? '',
                    $data['produk_kredit'] ?? '',
                    $data['kolektibilitas'] ?? '',
                ];
                break;
            case 'Biaya':
                $parts = [
                    $data['kode_transaksi'] ?? '',
                    $data['keterangan_transaksi'] ?? '',
                    (string)($data['nominal'] ?? ''),
                ];
                break;
            case 'Pengaduan':
                $parts = [
                    $data['jenis_pengaduan'] ?? '',
                    $data['isi_pengaduan'] ?? '',
                    $data['status_pengaduan'] ?? '',
                ];
                break;
        }

        // Gabung, uppercase, trim
        return strtoupper(trim(implode(' ', $parts)));
    }

    /**
     * Langkah 2-3: Check flags against rules
     */
    private function checkFlags(string $teksDeteksi, string $source): array
    {
        $flags = [
            'reversal' => false,
            'koreksi_override' => false,
            'selisih_kas' => false,
            'tunai_besar' => false,
            'biaya_jurnal' => false,
            'internal_account' => false,
            'pencairan_kredit' => false,
            'whitelist' => false,
        ];

        $rules = RuleEngine::active()->get();

        foreach ($rules as $rule) {
            $keywords = explode(',', $rule->keyword_pattern);
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                if (!empty($keyword) && strpos($teksDeteksi, strtoupper($keyword)) !== false) {
                    // Map rule_id to flag
                    if (str_starts_with($rule->rule_id, 'RISK_REV')) {
                        $flags['reversal'] = true;
                    } elseif (str_starts_with($rule->rule_id, 'RISK_KOR')) {
                        $flags['koreksi_override'] = true;
                    } elseif (str_starts_with($rule->rule_id, 'RISK_SEL')) {
                        $flags['selisih_kas'] = true;
                    } elseif (str_starts_with($rule->rule_id, 'CLS_KRD')) {
                        $flags['pencairan_kredit'] = true;
                    } elseif (str_starts_with($rule->rule_id, 'WL_')) {
                        $flags['whitelist'] = true;
                    }
                }
            }
        }

        // Special logic untuk tunai_besar, biaya_jurnal, internal_account
        if (strpos($teksDeteksi, 'PENARIKAN TUNAI') !== false ||
            strpos($teksDeteksi, 'SETORAN TUNAI') !== false) {
            // Threshold check would go here
            $flags['tunai_besar'] = true;
        }

        if (strpos($teksDeteksi, 'BIAYA') !== false ||
            strpos($teksDeteksi, 'JURNAL') !== false ||
            strpos($teksDeteksi, 'NOTA DB') !== false ||
            strpos($teksDeteksi, 'NOTA KR') !== false) {
            $flags['biaya_jurnal'] = true;
        }

        if (strpos($teksDeteksi, 'SUSPENSE') !== false ||
            strpos($teksDeteksi, 'TITIPAN') !== false) {
            $flags['internal_account'] = true;
        }

        return $flags;
    }

    /**
     * Hitung jumlah risk flags (Langkah 3)
     */
    private function countRiskFlags(array $flags): int
    {
        $count = 0;
        $riskFlags = ['reversal', 'koreksi_override', 'selisih_kas', 'tunai_besar'];

        foreach ($riskFlags as $flag) {
            if ($flags[$flag] ?? false) {
                $count++;
            }
        }

        if (($flags['biaya_jurnal'] ?? false) && !($flags['whitelist'] ?? false)) {
            $count++;
        }

        if ($flags['internal_account'] ?? false) {
            $count++;
        }

        return $count;
    }

    /**
     * Langkah 4: Determine area review (routing)
     */
    private function determineArea(string $teksDeteksi, array $flags, string $source): string
    {
        // Priority order dari spec §3.4
        if ($flags['pencairan_kredit'] ?? false) {
            return 'Kredit';
        }

        if (($flags['reversal'] ?? false) ||
            ($flags['koreksi_override'] ?? false) ||
            ($flags['tunai_besar'] ?? false) ||
            ($flags['selisih_kas'] ?? false) ||
            strpos($teksDeteksi, 'PENARIKAN TUNAI') !== false ||
            strpos($teksDeteksi, 'SETORAN TUNAI') !== false ||
            strpos($teksDeteksi, 'KAS DARI') !== false ||
            strpos($teksDeteksi, 'KAS KPD') !== false) {
            return 'Teller/Kas';
        }

        if (strpos($teksDeteksi, 'KU-') !== false ||
            strpos($teksDeteksi, ' KLR ') !== false ||
            strpos($teksDeteksi, 'KELUAR_') !== false ||
            strpos($teksDeteksi, 'SETOR TRF') !== false) {
            return 'Transfer/KU';
        }

        if (($flags['biaya_jurnal'] ?? false) ||
            ($flags['internal_account'] ?? false) ||
            strpos($teksDeteksi, 'NOTA DB') !== false ||
            strpos($teksDeteksi, 'NOTA KR') !== false) {
            return 'Biaya/Internal';
        }

        if ($source === 'Pengaduan') {
            return 'Pengaduan';
        }

        return 'Transaksi Umum'; // Default
    }

    /**
     * Langkah 5: Determine risk level
     */
    private function determineRiskLevel(int $jumlahFlag, array $flags): string
    {
        // Kondisi HIGH
        if (($flags['selisih_kas'] ?? false) ||
            (($flags['tunai_besar'] ?? false) && (($flags['reversal'] ?? false) || ($flags['koreksi_override'] ?? false))) ||
            $jumlahFlag >= 3) {
            return 'High';
        }

        // Kondisi MODERATE
        if ($jumlahFlag > 0) {
            return 'Moderate';
        }

        return 'Low';
    }

    /**
     * Langkah 6: Generate case ID untuk pairing (CBS only)
     */
    private function generateCaseId(array $data): ?string
    {
        if (empty($data['tanggal_data']) || empty($data['no_referensi'])) {
            return null;
        }

        return date('Ymd', strtotime($data['tanggal_data'])) . '|' . $data['no_referensi'];
    }

    /**
     * Langkah 7: Determine KKA sheet tujuan
     */
    private function determineKkaSheet(string $riskLevel, string $areaReview): string
    {
        if ($riskLevel === 'Low') {
            return 'Register'; // Candidate for sampling, not auto KKA
        }

        // Map area to KKA table
        $mapping = [
            'Teller/Kas' => 'kka_teller_kas',
            'Kredit' => 'kka_kredit',
            'Biaya/Beban' => 'kka_biaya_beban',
            'Biaya/Internal' => 'kka_biaya_internal',
            'Pengaduan' => 'kka_pengaduan',
            'Transaksi Umum' => 'kka_transaksi_umum',
            'Transfer/KU' => 'kka_transfer_ku',
        ];

        return $mapping[$areaReview] ?? 'kka_transaksi_umum';
    }

    /**
     * Validate data quality per WP period & unit
     */
    public function validateDataQuality(array $data, WpOffsite $wp): string
    {
        // Cek unit
        if ($data['kode_unit'] !== $wp->kode_unit) {
            return 'Salah Unit';
        }

        // Cek periode
        $tanggal = new \DateTime($data['tanggal_data'] ?? now());
        if ($tanggal < $wp->periode_mulai || $tanggal > $wp->periode_selesai) {
            return 'Luar Periode';
        }

        return 'VALID';
    }
}
