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
        fgetcsv($file); // Lewati header

        $lowRiskData = [];
        $moderateHighRiskData = [];

        while (($row = fgetcsv($file)) !== false) {
            $tanggal = $row[0] ?? now()->toDateString();
            $statusRekening = strtoupper($row[1] ?? ''); // Misal: BARU, DORMANT, AKTIF
            $nominal = (float) ($row[2] ?? 0);

            // LOGIKA DPK: Deteksi rekening baru dengan nominal besar atau rekening dormant yang tiba-tiba aktif
            $isDormantActive = ($statusRekening === 'DORMANT' && $nominal > 0);
            $isNewHighValue = ($statusRekening === 'BARU' && $nominal >= 100000000);

            if ($isDormantActive || $isNewHighValue) {
                $moderateHighRiskData[] = [
                    'tanggal_data' => $tanggal,
                    'kode_unit' => $kodeUnit,
                    'source_sheet' => 'KKA_Transaksi_Umum', // Diarahkan ke KKA Transaksi Umum
                    'nominal_terkait' => $nominal,
                    'risk_awal' => $isDormantActive ? 'High' : 'Moderate',
                    'jenis_exception_awal' => $isDormantActive ? 'Aktivasi Dormant Ireguler' : 'Rekening Baru Nominal Besar',
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