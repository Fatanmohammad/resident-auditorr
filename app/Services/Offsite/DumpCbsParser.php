<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\DailyRegister;

class DumpCbsParser
{
    /**
     * Memproses file DUMP_01 (Transaksi CBS)
     * Menggantikan logika rumus excel untuk mendeteksi Reversal & Anomali Nominal
     */
    public function parse($filePath, $kodeUnit)
    {
        // 1. Buka file CSV
        if (!file_exists($filePath)) {
            throw new \Exception("File CSV tidak ditemukan di folder staging.");
        }

        $file = fopen($filePath, 'r');
        fgetcsv($file); // Melewati baris pertama (Header CSV)

        $lowRiskData = [];
        $moderateHighRiskData = [];

        // 2. Baca baris per baris
        while (($row = fgetcsv($file)) !== false) {
            
            // --- MULAI PERBAIKAN: AUTO-DETECT DATE ---
            $rawDate = trim($row[0] ?? '');
            
            // Jika isinya murni angka (seperti '451', '296', dll) atau kosong, pakai tanggal hari ini
            if (empty($rawDate) || preg_match('/^[0-9]+$/', $rawDate)) {
                $tanggal = now()->toDateString();
            } else {
                // Jika formatnya teks tapi bukan tanggal valid, fallback ke hari ini
                $parsedDate = strtotime($rawDate);
                $tanggal = $parsedDate ? date('Y-m-d', $parsedDate) : now()->toDateString();
            }
            // --- SELESAI PERBAIKAN ---

            $keterangan = strtoupper($row[1] ?? '');
            $nominal = (float) ($row[2] ?? 0);

            // 3. TRANSLASI LOGIKA EXCEL KE PHP
            $isReversal = strpos($keterangan, 'REV-') !== false || strpos($keterangan, 'PEMBATALAN') !== false;
            
            // Deteksi limit batas
            $isHighNominal = $nominal >= 50000000; 

            // 4. PEMISAHAN JALUR RISIKO
            if ($isReversal || $isHighNominal) {
                // Masuk kategori Moderate/High -> Langsung tembak ke KKA Teller Kas
                $moderateHighRiskData[] = [
                    'tanggal_data'         => $tanggal,
                    'kode_unit'            => $kodeUnit,
                    'source_sheet'         => 'KKA_Teller_Kas',
                    'nominal_terkait'      => $nominal,
                    'risk_awal'            => $isHighNominal ? 'High' : 'Moderate',
                    'jenis_exception_awal' => $isReversal ? 'Indikasi Reversal' : 'Nominal Melewati Limit',
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];
            } else {
                // Masuk kategori Low -> Masuk Register Harian saja
                $lowRiskData[] = [
                    'tanggal_data'    => $tanggal,
                    'kode_unit'       => $kodeUnit,
                    'source_sheet'    => 'DUMP_01_CBS',
                    'kategori'        => 'Transaksi Wajar / Low Risk',
                    'nominal_terkait' => $nominal,
                    'rincian'         => $keterangan,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
        }

        fclose($file);

        // 5. INSERT KE DATABASE (Teknik Batch Insert via Chunk)
        if (!empty($lowRiskData)) {
            foreach (array_chunk($lowRiskData, 1000) as $chunk) {
                DailyRegister::insert($chunk);
            }
        }

        if (!empty($moderateHighRiskData)) {
            foreach (array_chunk($moderateHighRiskData, 1000) as $chunk) {
                KkaFinding::insert($chunk);
            }
        }

        return true;
    }
}