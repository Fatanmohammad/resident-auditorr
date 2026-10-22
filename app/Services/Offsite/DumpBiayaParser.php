<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\DailyRegister;
use App\Models\DailyRegister\DailyRegisterFinding;

class DumpBiayaParser
{
    public function parse($filePath, $kodeUnit, $isDailyRegister = false, $namaUnit = null)
    {
        if (!file_exists($filePath)) throw new \Exception("File CSV Biaya tidak ditemukan.");

        if (!$namaUnit) {
            $namaUnit = \App\Models\Unit::where('unit_code', $kodeUnit)->value('unit_name');
        }

        $file = fopen($filePath, 'r');
        fgetcsv($file); // Melewati header

        $lowRiskData = [];
        $moderateHighRiskData = [];
        $dailyRegisterData = [];

        while (($row = fgetcsv($file)) !== false) {
            
            // --- MULAI PERBAIKAN: AUTO-DETECT DATE ---
            $rawDate = trim($row[0] ?? '');
            
            // Jika isinya murni angka (seperti '199', '299', dll) atau kosong, pakai tanggal hari ini
            if (empty($rawDate) || preg_match('/^[0-9]+$/', $rawDate)) {
                $tanggal = now()->toDateString();
            } else {
                // Jika formatnya teks tapi bukan tanggal valid, fallback ke hari ini
                $parsedDate = strtotime($rawDate);
                $tanggal = $parsedDate ? date('Y-m-d', $parsedDate) : now()->toDateString();
            }
            // --- SELESAI PERBAIKAN ---

            $akunGl = strtoupper($row[1] ?? ''); // Misal: BIAYA PROMOSI, BIAYA ENTERTAIN
            $nominal = (float) ($row[2] ?? 0);

            // LOGIKA BIAYA: Deteksi pengeluaran biaya operasional yang tidak wajar / melebihi toleransi
            // Nanti batas toleransi ini bisa dihubungkan dengan tabel master_parameters
            $isBiayaJumbo = $nominal >= 25000000;
            $isAkunSensitif = strpos($akunGl, 'ENTERTAIN') !== false || strpos($akunGl, 'LAIN-LAIN') !== false;

            if ($isDailyRegister) {
                if ($isBiayaJumbo || $isAkunSensitif) {
                    $riskLevel = $isBiayaJumbo ? 'High' : 'Moderate';
                    $dailyRegisterData[] = [
                        'tanggal_data'         => $tanggal,
                        'periode'              => date('Y-m-01', strtotime($tanggal)),
                        'kode_unit'            => $kodeUnit,
                        'nama_unit'            => $namaUnit,
                        'source_sheet'         => 'KKA_Biaya_Beban',
                        'nominal_terkait'      => $nominal,
                        'risk_awal'            => $riskLevel,
                        'jenis_exception_awal' => $isAkunSensitif ? 'Akun GL Sensitif' : 'Nominal Biaya Melebihi Toleransi',
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                } else {
                    $lowRiskData[] = [
                        'tanggal_data'    => $tanggal,
                        'kode_unit'       => $kodeUnit,
                        'nama_unit'       => $namaUnit,
                        'source_sheet'    => 'DUMP_04_BIAYA',
                        'nominal_terkait' => $nominal,
                        'risk_awal'       => 'Low',
                        'kategori'        => 'Pengeluaran Wajar',
                        'rincian'         => 'Akun: ' . $akunGl,
                    ];
                }
            } else {
                if ($isBiayaJumbo || $isAkunSensitif) {
                    $moderateHighRiskData[] = [
                        'tanggal_data'         => $tanggal,
                        'periode'              => date('Y-m-01', strtotime($tanggal)),
                        'kode_unit'            => $kodeUnit,
                        'nama_unit'            => $namaUnit,
                        'source_sheet'         => 'KKA_Biaya_Beban', // Masuk ke KKA Biaya
                        'nominal_terkait'      => $nominal,
                        'risk_awal'            => $isBiayaJumbo ? 'High' : 'Moderate',
                        'jenis_exception_awal' => $isAkunSensitif ? 'Akun GL Sensitif' : 'Nominal Biaya Melebihi Toleransi',
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                } else {
                    $lowRiskData[] = [
                        'tanggal_data'    => $tanggal,
                        'kode_unit'       => $kodeUnit,
                        'nama_unit'       => $namaUnit,
                        'source_sheet'    => 'DUMP_04_BIAYA',
                        'kategori'        => 'Pengeluaran Wajar',
                        'nominal_terkait' => $nominal,
                        'rincian'         => 'Akun: ' . $akunGl,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }
            }
        }
        fclose($file);

        if ($isDailyRegister) {
            if (!empty($dailyRegisterData)) {
                foreach (array_chunk($dailyRegisterData, 1000) as $chunk) DailyRegisterFinding::insert($chunk);
            }
            if (!empty($lowRiskData)) {
                \App\Services\DailyRegister\DailyRegisterTmpService::storeLowRisk($kodeUnit, $lowRiskData);
            }
        } else {
            if (!empty($lowRiskData)) {
                foreach (array_chunk($lowRiskData, 1000) as $chunk) DailyRegister::insert($chunk);
            }
            if (!empty($moderateHighRiskData)) {
                foreach (array_chunk($moderateHighRiskData, 1000) as $chunk) KkaFinding::insert($chunk);
            }
        }

        return true;
    }
}