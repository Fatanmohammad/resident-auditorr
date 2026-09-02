<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\DailyRegister;

class DumpBiayaParser
{
    public function parse($filePath, $kodeUnit)
    {
        if (!file_exists($filePath)) throw new \Exception("File CSV Biaya tidak ditemukan.");

        $file = fopen($filePath, 'r');
        fgetcsv($file); 

        $lowRiskData = [];
        $moderateHighRiskData = [];

        while (($row = fgetcsv($file)) !== false) {
            $tanggal = $row[0] ?? now()->toDateString();
            $akunGl = strtoupper($row[1] ?? ''); // Misal: BIAYA PROMOSI, BIAYA ENTERTAIN
            $nominal = (float) ($row[2] ?? 0);

            // LOGIKA BIAYA: Deteksi pengeluaran biaya operasional yang tidak wajar / melebihi toleransi
            // Nanti batas toleransi ini bisa dihubungkan dengan tabel master_parameters
            $isBiayaJumbo = $nominal >= 25000000;
            $isAkunSensitif = strpos($akunGl, 'ENTERTAIN') !== false || strpos($akunGl, 'LAIN-LAIN') !== false;

            if ($isBiayaJumbo || $isAkunSensitif) {
                $moderateHighRiskData[] = [
                    'tanggal_data' => $tanggal,
                    'kode_unit' => $kodeUnit,
                    'source_sheet' => 'KKA_Biaya_Beban', // Masuk ke KKA Biaya
                    'nominal_terkait' => $nominal,
                    'risk_awal' => $isBiayaJumbo ? 'High' : 'Moderate',
                    'jenis_exception_awal' => $isAkunSensitif ? 'Akun GL Sensitif' : 'Nominal Biaya Melebihi Toleransi',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                $lowRiskData[] = [
                    'tanggal_data' => $tanggal,
                    'kode_unit' => $kodeUnit,
                    'source_sheet' => 'DUMP_04_BIAYA',
                    'kategori' => 'Pengeluaran Wajar',
                    'nominal_terkait' => $nominal,
                    'rincian' => 'Akun: ' . $akunGl,
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