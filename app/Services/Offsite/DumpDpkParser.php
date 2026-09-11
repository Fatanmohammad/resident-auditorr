<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\DailyRegister;

class DumpDpkParser
{
    public function parse($filePath, $kodeUnit)
    {
        if (!file_exists($filePath)) throw new \Exception("File CSV DPK tidak ditemukan.");

        $file = fopen($filePath, 'r');
        
        // 1. BACA HEADER DAN BUAT MAPPING INDEX OTOMATIS
        $header = fgetcsv($file);
        $headerMap = [];
        if ($header) {
            foreach ($header as $index => $colName) {
                $headerMap[trim(strtoupper($colName))] = $index;
            }
        }

        // Tentukan letak index
        $idxDate = $headerMap['TGL_BUKA_REK'] ?? ($headerMap['TGL_TX_AKHIR'] ?? 0);
        $idxStatus = $headerMap['STSDESC'] ?? ($headerMap['KD_STATUS'] ?? 1);
        $idxNominal = $headerMap['SALDO_AKHIR'] ?? 2;
        $idxNoRek = $headerMap['NO_REK'] ?? 0;
        $idxNama = $headerMap['NAMA_SINGKAT'] ?? 2;

        $lowRiskData = [];
        $moderateHighRiskData = [];

        while (($row = fgetcsv($file)) !== false) {
            if (empty($row) || count($row) < 3) continue;

            $rawDate = trim($row[$idxDate] ?? '');
            if (empty($rawDate) || preg_match('/^[0-9]+$/', $rawDate)) {
                $tanggal = now()->toDateString();
            } else {
                $tanggal = date('Y-m-d', strtotime(str_replace('/', '-', $rawDate)));
            }

            $statusRekening = strtoupper(trim($row[$idxStatus] ?? '')); 
            $nominal = (float) ($row[$idxNominal] ?? 0); 
            $noRekening = $row[$idxNoRek] ?? '-';
            $namaNasabah = $row[$idxNama] ?? '-';

            // --- LOGIKA ALARM KKA ---
            $isDormantActive = ($statusRekening === 'DORMANT' && $nominal > 0);
            $isNewHighValue = ($statusRekening === 'BARU' && $nominal >= 100000000);

            if ($isDormantActive || $isNewHighValue) {
                $jenisTemuan = $isDormantActive ? 'Aktivasi Dormant Ireguler' : 'Rekening Baru Nominal Besar';
                $deskripsiOtomatis = "Terdeteksi anomali pada rekening {$noRekening} atas nama {$namaNasabah} dengan status '{$statusRekening}' dan saldo/nominal Rp " . number_format($nominal, 0, ',', '.') . " di Unit {$kodeUnit}.";

                $moderateHighRiskData[] = [
                    'tanggal_data' => $tanggal,
                    'kode_unit' => $kodeUnit,
                    'source_sheet' => 'KKA Transaksi Umum', 
                    'nominal_terkait' => $nominal,
                    'risk_awal' => $isDormantActive ? 'High' : 'Moderate',
                    'jenis_exception_awal' => $jenisTemuan,
                    'deskripsi' => $deskripsiOtomatis, // Kolom deskripsi sekarang terisi dengan benar
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                $lowRiskData[] = [
                    'tanggal_data' => $tanggal,
                    'kode_unit' => $kodeUnit,
                    'source_sheet' => 'DUMP_02_DPK',
                    'kategori' => 'Pembukaan/Mutasi Wajar',
                    'nominal_terkait' => $nominal,
                    'rincian' => 'Status: ' . $statusRekening,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        fclose($file);

        if (!empty($lowRiskData)) {
            foreach (array_chunk($lowRiskData, 1000) as $chunk) DailyRegister::insert($chunk);
        }
        if (!empty($moderateHighRiskData)) {
            foreach (array_chunk($moderateHighRiskData, 1000) as $chunk) KkaFinding::insert($chunk);
        }

        return true;
    }
}