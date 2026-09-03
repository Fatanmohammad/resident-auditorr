<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\DailyRegister;

class DumpPengaduanParser
{
    public function parse($filePath, $kodeUnit)
    {
        if (!file_exists($filePath)) throw new \Exception("File CSV Pengaduan tidak ditemukan.");

        $file = fopen($filePath, 'r');
        fgetcsv($file); 

        $lowRiskData = [];
        $moderateHighRiskData = [];

        while (($row = fgetcsv($file)) !== false) {
            
            // --- AUTO-DETECT DATE ---
            $rawDate = trim($row[0] ?? '');
            if (empty($rawDate) || preg_match('/^[0-9]+$/', $rawDate)) {
                $tanggalMasuk = now()->toDateString();
            } else {
                $parsedDate = strtotime($rawDate);
                $tanggalMasuk = $parsedDate ? date('Y-m-d', $parsedDate) : now()->toDateString();
            }
            // ------------------------

            $jenisPengaduan = strtoupper($row[1] ?? ''); 
            $hariPenyelesaian = (int) ($row[2] ?? 0); // Jumlah hari (SLA)

            // LOGIKA PENGADUAN: Deteksi jika penyelesaian pengaduan melebihi SLA (misal: > 14 hari)
            $isSlaTerlewat = $hariPenyelesaian > 14;

            if ($isSlaTerlewat) {
                $moderateHighRiskData[] = [
                    'tanggal_data' => $tanggalMasuk,
                    'kode_unit' => $kodeUnit,
                    'source_sheet' => 'KKA_Pengaduan', // Masuk ke KKA Pengaduan
                    'nominal_terkait' => 0, // Pengaduan biasanya tidak wajib ada nominal
                    'risk_awal' => 'Moderate',
                    'jenis_exception_awal' => 'Penyelesaian Melewati SLA (>14 Hari)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                $lowRiskData[] = [
                    'tanggal_data' => $tanggalMasuk,
                    'kode_unit' => $kodeUnit,
                    'source_sheet' => 'DUMP_05_PENGADUAN',
                    'kategori' => 'SLA Terpenuhi',
                    'nominal_terkait' => 0,
                    'rincian' => 'Jenis: ' . $jenisPengaduan . ' | Diselesaikan dalam ' . $hariPenyelesaian . ' hari',
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