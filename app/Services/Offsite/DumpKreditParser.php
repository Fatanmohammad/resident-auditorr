<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\DailyRegister;
use Illuminate\Support\Carbon;

class DumpKreditParser
{
    public function parse($filePath, $kodeUnit)
    {
        if (!file_exists($filePath)) throw new \Exception("File CSV Kredit tidak ditemukan.");

        $file = fopen($filePath, 'r');
        fgetcsv($file); // Skip header baris pertama

        $lowRiskData = [];
        $moderateHighRiskData = [];

        while (($row = fgetcsv($file)) !== false) {
            
            // 1. Amankan Kolom Tanggal (Cegah Nomor Rekening / CIF masuk ke Tanggal)
            $rawDate = $row[0] ?? '';
            
            // Jika isinya murni angka & lebih dari 8 digit (ciri-ciri no rekening), pakai tanggal hari ini
            if (preg_match('/^[0-9]+$/', $rawDate) && strlen($rawDate) > 8) {
                $tanggal = now()->toDateString();
            } else {
                // Cek apakah benar format tanggal, kalau salah fallback ke hari ini
                $tanggal = strtotime($rawDate) ? date('Y-m-d', strtotime($rawDate)) : now()->toDateString();
            }

            // 2. Ambil Kolektibilitas dan Plafon (Pastikan tidak error kalau datanya kosong)
            $kolektibilitas = (int) ($row[1] ?? 1); // 1 = Lancar, 2 = DPK, dst
            $plafon = (float) ($row[2] ?? 0);

            // 3. LOGIKA KREDIT: Deteksi penurunan kolektibilitas atau pencairan plafon sangat besar
            $isKolTurun = $kolektibilitas >= 2;
            $isPlafonJumbo = $plafon >= 500000000;

            if ($isKolTurun || $isPlafonJumbo) {
                $moderateHighRiskData[] = [
                    'tanggal_data'         => $tanggal,
                    'kode_unit'            => $kodeUnit,
                    'source_sheet'         => 'KKA_Kredit',
                    'nominal_terkait'      => $plafon,
                    'risk_awal'            => 'High',
                    'jenis_exception_awal' => $isKolTurun ? 'Penurunan Kolektibilitas' : 'Pencairan Plafon Jumbo',
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];
            } else {
                $lowRiskData[] = [
                    'tanggal_data'    => $tanggal,
                    'kode_unit'       => $kodeUnit,
                    'source_sheet'    => 'DUMP_03_KREDIT',
                    'kategori'        => 'Pencairan / Angsuran Wajar',
                    'nominal_terkait' => $plafon,
                    'rincian'         => 'Kolektibilitas: ' . $kolektibilitas,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
        }
        fclose($file);

        // 4. Masukkan ke Database dengan metode Chunking (Cicil per 1000 baris) agar enteng
        if (!empty($lowRiskData)) {
            foreach (array_chunk($lowRiskData, 1000) as $chunk) DailyRegister::insert($chunk);
        }
        if (!empty($moderateHighRiskData)) {
            foreach (array_chunk($moderateHighRiskData, 1000) as $chunk) KkaFinding::insert($chunk);
        }

        return true;
    }
}