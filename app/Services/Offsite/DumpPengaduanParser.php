<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\DailyRegister;
use App\Models\DailyRegister\DailyRegisterFinding;

class DumpPengaduanParser
{
    public function parse($filePath, $kodeUnit, $isDailyRegister = false, $namaUnit = null)
    {
        if (!file_exists($filePath)) throw new \Exception("File CSV Pengaduan tidak ditemukan.");

        if (!$namaUnit) {
            $namaUnit = \App\Models\Unit::where('unit_code', $kodeUnit)->value('unit_name');
        }

        $file = fopen($filePath, 'r');
        fgetcsv($file); 

        $lowRiskData = [];
        $moderateHighRiskData = [];
        $dailyRegisterData = [];

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

            // LOGIKA PENGADUAN: Deteksi jika penyelesaian pengaduan melebihi SLA
            $isSangatTerlambat = $hariPenyelesaian > 30; // High Risk
            $isSlaTerlewat = $hariPenyelesaian > 14;     // Moderate Risk

            if ($isDailyRegister) {
                if ($isSangatTerlambat || $isSlaTerlewat) {
                    $riskLevel = $isSangatTerlambat ? 'High' : 'Moderate';
                    $dailyRegisterData[] = [
                        'tanggal_data'         => $tanggalMasuk,
                        'periode'              => date('Y-m-01', strtotime($tanggalMasuk)),
                        'kode_unit'            => $kodeUnit,
                        'nama_unit'            => $namaUnit,
                        'source_sheet'         => 'KKA_Pengaduan',
                        'nominal_terkait'      => 0,
                        'risk_awal'            => $riskLevel,
                        'jenis_exception_awal' => $isSangatTerlambat ? 'SLA Sangat Terlambat (>30 Hari)' : 'Penyelesaian Melewati SLA (>14 Hari)',
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                } else {
                    $lowRiskData[] = [
                        'tanggal_data'    => $tanggalMasuk,
                        'kode_unit'       => $kodeUnit,
                        'nama_unit'       => $namaUnit,
                        'source_sheet'    => 'DUMP_05_PENGADUAN',
                        'kategori'        => 'SLA Terpenuhi',
                        'nominal_terkait' => 0,
                        'risk_awal'       => 'Low',
                        'rincian'         => 'Jenis: ' . $jenisPengaduan . ' | Diselesaikan dalam ' . $hariPenyelesaian . ' hari',
                    ];
                }
            } else {
                if ($isSangatTerlambat || $isSlaTerlewat) {
                    $moderateHighRiskData[] = [
                        'tanggal_data'         => $tanggalMasuk,
                        'periode'              => date('Y-m-01', strtotime($tanggalMasuk)),
                        'kode_unit'            => $kodeUnit,
                        'nama_unit'            => $namaUnit,
                        'source_sheet'         => 'KKA_Pengaduan', // Masuk ke KKA Pengaduan
                        'nominal_terkait'      => 0, // Pengaduan biasanya tidak wajib ada nominal
                        'risk_awal'            => $isSangatTerlambat ? 'High' : 'Moderate',
                        'jenis_exception_awal' => $isSangatTerlambat ? 'SLA Sangat Terlambat (>30 Hari)' : 'Penyelesaian Melewati SLA (>14 Hari)',
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                } else {
                    $lowRiskData[] = [
                        'tanggal_data'    => $tanggalMasuk,
                        'kode_unit'       => $kodeUnit,
                        'nama_unit'       => $namaUnit,
                        'source_sheet'    => 'DUMP_05_PENGADUAN',
                        'kategori'        => 'SLA Terpenuhi',
                        'nominal_terkait' => 0,
                        'rincian'         => 'Jenis: ' . $jenisPengaduan . ' | Diselesaikan dalam ' . $hariPenyelesaian . ' hari',
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