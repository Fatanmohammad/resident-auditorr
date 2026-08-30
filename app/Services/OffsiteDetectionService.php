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
use Carbon\Carbon;
use DB;

class OffsiteDetectionService
{
    /**
     * Deteksi per baris - main detection logic
     * Implements §3 of spec: Langkah 1-8 detection flow
     */
    public function detectBaris(array $data, string $source, WpOffsite $wp): array
    {
        $sourceUpper = strtoupper($source);

        // Routing logika deteksi spesifik berdasarkan domain
        switch ($sourceUpper) {
            case 'DPK':
                return $this->detectDpk($data, $sourceUpper);
            case 'KREDIT':
                return $this->detectKredit($data, $sourceUpper, $wp);
            case 'BIAYA':
            case 'BIAYA_BEBAN':
                return $this->detectBiaya($data, $sourceUpper);
            case 'PENGADUAN':
                return $this->detectPengaduan($data, $sourceUpper);
            default: // CBS dan default lainnya
                return $this->detectCbs($data, $sourceUpper, $wp);
        }
    }

    /**
     * Logika Utama Deteksi CBS (Berbasis Rule Engine & Keyword)
     */
    private function detectCbs(array $data, string $source, WpOffsite $wp): array
    {
        // Langkah 1: Gabungkan jadi Teks Deteksi
        $teksDeteksi = $this->buildDetectionText($data, $source);

        // Langkah 2-3: Cek flag dan hitung jumlah
        $flags = $this->checkFlags($teksDeteksi, $source, $data);
        $jumlahFlagRisiko = $this->countRiskFlags($flags);

        // Langkah 4: Tentukan Area Review
        $areaReview = $this->determineArea($teksDeteksi, $flags, $source);

        // Langkah 5: Tentukan Risk Level
        $riskLevel = $this->determineRiskLevel($jumlahFlagRisiko, $flags);

        // Langkah 6: Case Pairing (CBS)
        $caseId = $this->generateCaseId($data);

        // Langkah 7: Tentukan tujuan KKA & apakah masuk review
        $kkaSheet = $this->determineKkaSheet($riskLevel, $areaReview);

        // Langkah 8: Flag untuk sampling / KKA
        $perluKka = $jumlahFlagRisiko > 0 || $riskLevel === 'High';
        $perluKlarifikasi = $flags['whitelist'] ?? false;
        $perluEskalasi = $riskLevel === 'High';

        return [
            'flags'              => $flags,
            'jumlah_flag_risiko' => $jumlahFlagRisiko,
            'area_review'        => $areaReview,
            'risk_level'         => $riskLevel,
            'case_id'            => $caseId,
            'kka_sheet_tujuan'   => $kkaSheet,
            'perlu_kka'          => $perluKka,
            'perlu_klarifikasi'  => $perluKlarifikasi,
            'perlu_eskalasi'     => $perluEskalasi,
        ];
    }

    /**
     * Logika Deteksi DPK (Mendukung Depo, Tabungan, & Giro CS SPU)
     */
    private function detectDpk(array $data, string $source): array
    {
        $tglBuka     = $data['TGL_BUKA_REK'] ?? $data['TGL BUKA REKENING'] ?? $data['tgl_buka_rek'] ?? null;
        $tglJt       = $data['TGL_JT'] ?? $data['tgl_jt'] ?? null;
        $saldo       = (float) ($data['SALDO_AKHIR'] ?? $data['SALDO AKHIR'] ?? $data['saldo_akhir'] ?? 0);
        $statusRek   = strtoupper($data['KD_STATUS'] ?? $data['ACCSTS'] ?? $data['STSDESC'] ?? $data['STATUS'] ?? $data['status_rekening'] ?? '');
        $noBilyet    = $data['NO_BILYET'] ?? $data['no_bilyet'] ?? null;
        $produk      = strtoupper($data['PRODNM'] ?? $data['KD_PRODUK'] ?? $data['PRD_NAME'] ?? $data['produk'] ?? '');
        $cifRek      = $data['NO_NSB'] ?? $data['NO NASABAH'] ?? $data['NO_NASABAH'] ?? $data['cif'] ?? null;
        $cifBunga    = $data['NO_REK_BUNGA'] ?? $data['CIF_PENERIMA_BUNGA'] ?? $data['cif_penerima_bunga'] ?? null;
        $persenBunga = (float) ($data['PRS_BUNGA'] ?? $data['prs_bunga'] ?? 0);

        $flags = [
            'rekening_baru'              => $tglBuka && Carbon::parse($tglBuka)->isToday(),
            'saldo_besar'                => $saldo >= 500000000,
            'dormant_pasif'              => in_array($statusRek, ['DORMANT', 'PASIF', 'TIDAK AKTIF']),
            'blokir_tutup'               => in_array($statusRek, ['BLOKIR', 'TUTUP', 'CLOSED', 'CLOSE']),
            'deposito_tanpa_bilyet'      => str_contains($produk, 'DEPO') && empty($noBilyet),
            'cif_penerima_bunga_berbeda' => $cifBunga && $cifRek && $cifBunga !== $cifRek,
            'special_rate'               => $persenBunga > 8.0,
            'deposito_jatuh_tempo'       => $tglJt && Carbon::parse($tglJt)->diffInDays(now()) <= 7 && str_contains($produk, 'DEPO'),
        ];

        $jumlahFlag = count(array_filter($flags));

        if ($flags['deposito_tanpa_bilyet'] || $flags['cif_penerima_bunga_berbeda'] || $flags['special_rate']
            || ($flags['saldo_besar'] && ($flags['rekening_baru'] || $flags['dormant_pasif'] || $flags['blokir_tutup']))
            || $jumlahFlag >= 2) {
            $riskLevel = 'High';
        } elseif ($jumlahFlag >= 1) {
            $riskLevel = 'Moderate';
        } else {
            $riskLevel = 'Low';
        }

        return [
            'flags'              => $flags,
            'jumlah_flag_risiko' => $jumlahFlag,
            'area_review'        => 'DPK/APU-PPT',
            'risk_level'         => $riskLevel,
            'case_id'            => null,
            'kka_sheet_tujuan'   => $riskLevel === 'Low' ? 'Register' : 'kka_transaksi_umum',
            'perlu_kka'          => $riskLevel !== 'Low',
            'perlu_klarifikasi'  => false,
            'perlu_eskalasi'     => $riskLevel === 'High',
        ];
    }

    /**
     * Logika Deteksi KREDIT (Nomi KDT / Kredit)
     */
    private function detectKredit(array $data, string $source, WpOffsite $wp): array
    {
        $noAkad      = $data['NO_AKD'] ?? $data['no_akad'] ?? null;
        $plafon      = (float) ($data['PLAFOND'] ?? $data['plafon'] ?? 0);
        $bakiDebet   = (float) ($data['SALDO_AKHIR'] ?? $data['baki_debet'] ?? 0);
        $kolek       = (int) ($data['KOLEKTIBILITY'] ?? $data['kolektibilitas'] ?? 1);
        $tunggPokok  = (float) ($data['TUNGG_POKOK'] ?? $data['tunggakan_pokok'] ?? 0);
        $tunggBunga  = (float) ($data['TUNGG_BUNGA'] ?? $data['tunggakan_bunga'] ?? 0);
        $totalAgunan = (float) ($data['TOTAGUNAN'] ?? $data['total_agunan'] ?? 0);
        $jenisKredit = strtoupper($data['JENIS_KREDIT'] ?? $data['PRD_NAME'] ?? $data['GL_PRD_NAME'] ?? $data['produk_kredit'] ?? '');

        $wajibAgunanFisik = !str_contains($jenisKredit, 'TANPA AGUNAN') && !str_contains($jenisKredit, 'KUR MIKRO');

        $flags = [
            'akad_tidak_ada'              => empty($noAkad),
            'produk_belum_terklasifikasi' => empty($jenisKredit),
            'agunan_fisik_wajib_tidak_ada'=> $wajibAgunanFisik && $totalAgunan <= 0,
            'plafon_baki_debet_besar'     => $plafon >= 500000000 || $bakiDebet >= 500000000,
            'kolek_tunggakan_tidak_normal'=> $kolek >= 2 || $tunggPokok > 0 || $tunggBunga > 0,
        ];

        $jumlahFlag = count(array_filter($flags));

        if ($flags['akad_tidak_ada'] || $flags['agunan_fisik_wajib_tidak_ada'] || $flags['kolek_tunggakan_tidak_normal']) {
            $riskLevel = 'High';
        } elseif ($flags['produk_belum_terklasifikasi'] || $flags['plafon_baki_debet_besar']) {
            $riskLevel = 'Moderate';
        } else {
            $riskLevel = 'Low';
        }

        return [
            'flags'              => $flags,
            'jumlah_flag_risiko' => $jumlahFlag,
            'area_review'        => 'Kredit',
            'risk_level'         => $riskLevel,
            'case_id'            => null,
            'kka_sheet_tujuan'   => $riskLevel === 'Low' ? 'Register' : 'kka_kredit',
            'perlu_kka'          => $riskLevel !== 'Low',
            'perlu_klarifikasi'  => false,
            'perlu_eskalasi'     => $riskLevel === 'High',
        ];
    }

    /**
     * Logika Deteksi BIAYA / BEBAN (Jurnal Beban Biaya)
     */
    private function detectBiaya(array $data, string $source): array
    {
        $nominal   = (float) ($data['JUMLAH_TX'] ?? $data['nominal'] ?? 0);
        $deskripsi = strtoupper($data['KET_TX'] ?? $data['keterangan_transaksi'] ?? $data['URAIAN'] ?? $data['deskripsi'] ?? '');
        $isAuto    = ($data['ISAUTOTX'] ?? '0') == '1';

        $flags = [
            'nominal_besar'       => $nominal >= 20000000,
            'jurnal_manual'       => !$isAuto,
            'uraian_tidak_jelas'  => empty($deskripsi) || strlen($deskripsi) < 12 || (bool) preg_match('/LAIN-LAIN|PEMBAYARAN|BIAYA UMUM|KOREKSI|OB BY/i', $deskripsi),
            'salah_pos_indikatif' => (bool) preg_match('/PENCAIRAN KREDIT/i', $deskripsi),
        ];

        $jumlahFlag = count(array_filter($flags));

        if ($flags['salah_pos_indikatif']) {
            $riskLevel = 'High';
        } elseif ($flags['nominal_besar'] || $flags['uraian_tidak_jelas'] || $flags['jurnal_manual']) {
            $riskLevel = 'Moderate';
        } else {
            $riskLevel = 'Low';
        }

        return [
            'flags'              => $flags,
            'jumlah_flag_risiko' => $jumlahFlag,
            'area_review'        => 'Biaya/Internal',
            'risk_level'         => $riskLevel,
            'case_id'            => null,
            'kka_sheet_tujuan'   => $riskLevel === 'Low' ? 'Register' : 'kka_biaya_beban',
            'perlu_kka'          => $riskLevel !== 'Low',
            'perlu_klarifikasi'  => false,
            'perlu_eskalasi'     => $riskLevel === 'High',
        ];
    }

    /**
     * Logika Deteksi PENGADUAN
     */
    private function detectPengaduan(array $data, string $source): array
    {
        $tglTerima    = $data['TGL_TERIMA'] ?? $data['tgl_terima'] ?? now()->format('Y-m-d');
        $status       = strtoupper($data['status_pengaduan'] ?? $data['STATUS'] ?? '');
        $kategori     = strtoupper($data['jenis_pengaduan'] ?? '');
        $isiPengaduan = $data['isi_pengaduan'] ?? '';
        $nominalRugi  = (float) ($data['NOMINAL_KERUGIAN'] ?? $data['nominal_kerugian'] ?? 0);

        $isSelesai = in_array($status, ['SELESAI', 'CLOSED']);
        $overdue   = $isSelesai ? false : now()->greaterThan(Carbon::parse($tglTerima)->addDays(14));

        $flags = [
            'overdue_sla'          => $overdue,
            'dana_saldo_berkurang' => (bool) preg_match('/DANA BERKURANG|SALDO BERKURANG|UANG HILANG/i', $isiPengaduan),
            'atm_digital_banking'  => (bool) preg_match('/ATM|KARTU|MOBILE|MBANK|DIGITAL|EDC/i', $isiPengaduan . ' ' . $kategori),
            'nominal_material'     => $nominalRugi >= 5000000,
            'status_open'          => !$isSelesai,
        ];

        $jumlahFlag = count(array_filter($flags));

        if ($flags['overdue_sla'] || $flags['dana_saldo_berkurang']) {
            $riskLevel = 'High';
        } elseif ($jumlahFlag >= 1) {
            $riskLevel = 'Moderate';
        } else {
            $riskLevel = 'Low';
        }

        return [
            'flags'              => $flags,
            'jumlah_flag_risiko' => $jumlahFlag,
            'area_review'        => 'Pengaduan',
            'risk_level'         => $riskLevel,
            'case_id'            => null,
            'kka_sheet_tujuan'   => $riskLevel === 'Low' ? 'Register' : 'kka_pengaduan',
            'perlu_kka'          => $riskLevel !== 'Low',
            'perlu_klarifikasi'  => false,
            'perlu_eskalasi'     => $riskLevel === 'High',
        ];
    }

    /**
     * Langkah 1: Build detection text from relevant columns
     */
    private function buildDetectionText(array $data, string $source): string
    {
        $parts = [];

        switch (strtoupper($source)) {
            case 'CBS':
                $parts = [
                    $data['KD_TX'] ?? $data['kode_transaksi'] ?? '',
                    $data['KET_TX'] ?? $data['nama_transaksi'] ?? $data['deskripsi_narasi'] ?? '',
                    $data['NO_ARSIP'] ?? $data['no_referensi'] ?? '',
                    $data['KD_USER'] ?? $data['USER_MAKER'] ?? $data['user_maker'] ?? '',
                    $data['NAMA_USER'] ?? '',
                    (string)($data['JUMLAH_TX'] ?? $data['nominal'] ?? ''),
                    $data['NO_REK'] ?? '',
                ];
                break;
            case 'DPK':
                $parts = [
                    $data['NO_REK'] ?? $data['NO REKENING'] ?? $data['no_rekening'] ?? '',
                    $data['NAMA_SINGKAT'] ?? $data['NAMA SINGKAT'] ?? $data['nama_nasabah'] ?? '',
                    $data['PRODNM'] ?? $data['KD_PRODUK'] ?? $data['PRD_NAME'] ?? $data['produk'] ?? '',
                    $data['KD_STATUS'] ?? $data['ACCSTS'] ?? $data['STSDESC'] ?? $data['STATUS'] ?? '',
                ];
                break;
            case 'KREDIT':
                $parts = [
                    $data['NO_REK'] ?? $data['no_rekening_kredit'] ?? '',
                    $data['NAMA_SINGKAT'] ?? $data['nama_debitur'] ?? '',
                    $data['JENIS_KREDIT'] ?? $data['PRD_NAME'] ?? $data['GL_PRD_NAME'] ?? '',
                    $data['KOLEKTIBILITY'] ?? $data['kolektibilitas'] ?? '',
                ];
                break;
            case 'BIAYA':
            case 'BIAYA_BEBAN':
                $parts = [
                    $data['KD_TX'] ?? $data['kode_transaksi'] ?? '',
                    $data['KET_TX'] ?? $data['keterangan_transaksi'] ?? $data['URAIAN'] ?? '',
                    (string)($data['JUMLAH_TX'] ?? $data['nominal'] ?? ''),
                ];
                break;
            case 'PENGADUAN':
                $parts = [
                    $data['jenis_pengaduan'] ?? '',
                    $data['isi_pengaduan'] ?? '',
                    $data['status_pengaduan'] ?? $data['STATUS'] ?? '',
                ];
                break;
            default:
                $parts = array_values($data);
                break;
        }

        return strtoupper(trim(implode(' ', array_map('strval', $parts))));
    }

    /**
     * Langkah 2-3: Check flags against rules
     */
    private function checkFlags(string $teksDeteksi, string $source, array $data = []): array
    {
        $flags = [
            'reversal'         => false,
            'koreksi_override' => false,
            'selisih_kas'      => false,
            'tunai_besar'      => false,
            'biaya_jurnal'     => false,
            'internal_account' => false,
            'pencairan_kredit' => false,
            'whitelist'        => false,
        ];

        try {
            // Ambil rule aktif dari database
            $rules = RuleEngine::where('aktif', true)->get();

            foreach ($rules as $rule) {
                $keywords = explode(',', $rule->keyword_pattern);
                foreach ($keywords as $keyword) {
                    $keyword = trim($keyword);
                    if (!empty($keyword) && strpos($teksDeteksi, strtoupper($keyword)) !== false) {
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
        } catch (\Throwable $e) {
            // Fallback jika tabel rule_engine belum siap
        }

        // Logic tambahan untuk tunai_besar, biaya_jurnal, internal_account
        $nominal = floatval($data['JUMLAH_TX'] ?? $data['nominal'] ?? 0);
        $thresholdTunai = 50000000;

        if ((strpos($teksDeteksi, 'PENARIKAN TUNAI') !== false ||
            strpos($teksDeteksi, 'SETORAN TUNAI') !== false) && $nominal >= $thresholdTunai) {
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

        if (strtoupper($source) === 'PENGADUAN') {
            return 'Pengaduan';
        }

        return 'Transaksi Umum';
    }

    /**
     * Langkah 5: Determine risk level
     */
    private function determineRiskLevel(int $jumlahFlag, array $flags): string
    {
        if (($flags['selisih_kas'] ?? false) ||
            (($flags['tunai_besar'] ?? false) && (($flags['reversal'] ?? false) || ($flags['koreksi_override'] ?? false))) ||
            $jumlahFlag >= 3) {
            return 'High';
        }

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
        $tgl = $data['TGL_TX'] ?? $data['tanggal_data'] ?? null;
        $ref = $data['NO_ARSIP'] ?? $data['no_referensi'] ?? null;

        if (empty($tgl) || empty($ref)) {
            return null;
        }

        return date('Ymd', strtotime($tgl)) . '|' . $ref;
    }

    /**
     * Langkah 7: Determine KKA sheet tujuan
     */
    private function determineKkaSheet(string $riskLevel, string $areaReview): string
    {
        if ($riskLevel === 'Low') {
            return 'Register';
        }

        $mapping = [
            'Teller/Kas'     => 'kka_teller_kas',
            'Kredit'         => 'kka_kredit',
            'Biaya/Beban'    => 'kka_biaya_beban',
            'Biaya/Internal' => 'kka_biaya_internal',
            'Pengaduan'      => 'kka_pengaduan',
            'Transaksi Umum' => 'kka_transaksi_umum',
            'Transfer/KU'    => 'kka_transfer_ku',
        ];

        return $mapping[$areaReview] ?? 'kka_transaksi_umum';
    }

    /**
     * Validate data quality per WP period & unit
     */
    public function validateDataQuality(array $data, WpOffsite $wp): string
    {
        $kodeUnit = $data['KD_CAB'] ?? $data['kode_unit'] ?? null;
        if ($kodeUnit && (string)$kodeUnit !== (string)$wp->kode_unit) {
            return 'Salah Unit';
        }

        $tglData = $data['TGL_TX'] ?? $data['TGL_BUKA_REK'] ?? $data['tanggal_data'] ?? null;
        if ($tglData) {
            $tanggal = new \DateTime($tglData);
            if ($tanggal < $wp->periode_mulai || $tanggal > $wp->periode_selesai) {
                return 'Luar Periode';
            }
        }

        return 'VALID';
    }
}