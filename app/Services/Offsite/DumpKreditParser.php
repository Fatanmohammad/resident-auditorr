<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\DailyRegister;

class DumpKreditParser
{
    public function parse($filePath, $kodeUnit)
    {
        if (!file_exists($filePath)) throw new \Exception("File CSV Kredit tidak ditemukan.");

        $file = fopen($filePath, 'r');
        fgetcsv($file); 

        $lowRiskData = [];
        $moderateHighRiskData = [];

        while (($row = fgetcsv($file)) !== false) {
            $tanggal = $row[0] ?? now()->toDateString();
            $kolektibilitas = (int) ($row[1] ?? 1); // 1 = Lancar, 2 = DPK, dst
            $plafon = (float) ($row[2] ?? 0);

            // LOGIKA KREDIT: Deteksi penurunan kolektibilitas atau pencairan plafon sangat besar
            $isKolTurun = $kolektibilitas >= 2;
            $isPlafonJumbo = $plafon >= 500000000;

            if ($isKolTurun || $isPlafonJumbo) {
                $moderateHighRiskData[] = [
                    'tanggal_data' => $tanggal,
                    'kode_unit' => $kodeUnit,
                    'source_sheet' => 'KKA_Kredit', // Langsung masuk ke KKA Kredit
                    'nominal_terkait' => $plafon,
                    'risk_awal' => 'High',
                    'jenis_exception_awal' => $isKolTurun ? 'Penurunan Kolektibilitas' : 'Pencairan Plafon Jumbo',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                $lowRiskData[] = [
                    'tanggal_data' => $tanggal,
                    'kode_unit' => $kodeUnit,
                    'source_sheet' => 'DUMP_03_KREDIT',
                    'kategori' => 'Pencairan / Angsuran Wajar',
                    'nominal_terkait' => $plafon,
                    'rincian' => 'Kolektibilitas: ' . $kolektibilitas,
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