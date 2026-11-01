<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\DailyRegister;
use App\Models\DailyRegister\DailyRegisterFinding;

class DumpPengaduanParser
{
    public function parse($filePath, $kodeUnit, $isDailyRegister = false, $namaUnit = null): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File CSV Pengaduan tidak ditemukan.");
        }

        if (!$namaUnit) {
            $namaUnit = \App\Models\Unit::where('unit_code', $kodeUnit)->value('unit_name');
        }

        $file = fopen($filePath, 'r');
        
        // 1. Baca header dan mapping index otomatis
        $header = fgetcsv($file);
        $headerMap = [];
        if ($header) {
            foreach ($header as $index => $colName) {
                $cleaned = strtoupper(trim(preg_replace('/[^A-Za-z0-9_]/', '', $colName)));
                $headerMap[$cleaned] = $index;
            }
        }

        $idxDate = $headerMap['TGL_PENGADUAN'] 
            ?? ($headerMap['TANGGAL'] 
            ?? ($headerMap['TGL'] 
            ?? 0));

        $idxJenis = $headerMap['JENIS_PENGADUAN'] 
            ?? ($headerMap['KATEGORI'] 
            ?? ($headerMap['PERIHAL'] 
            ?? ($headerMap['KETERANGAN'] ?? 1)));

        $idxSla = $headerMap['HARI_PENYELESAIAN'] 
            ?? ($headerMap['SLA'] 
            ?? ($headerMap['HARI'] 
            ?? ($headerMap['DURASI'] ?? 2)));

        $lowRiskData = [];
        $moderateHighRiskData = [];
        $dailyRegisterData = [];
        $countHigh = 0;
        $countModerate = 0;
        $countLow = 0;

        while (($row = fgetcsv($file)) !== false) {
            if (empty($row) || count($row) < 2) {
                continue;
            }

            // Auto-detect Date
            $rawDate = trim($row[$idxDate] ?? '');
            if (empty($rawDate) || preg_match('/^[0-9]+$/', $rawDate)) {
                $tanggalMasuk = now()->toDateString();
            } else {
                $parsedDate = strtotime(str_replace('/', '-', $rawDate));
                $tanggalMasuk = $parsedDate ? date('Y-m-d', $parsedDate) : now()->toDateString();
            }

            $jenisPengaduan = strtoupper(trim($row[$idxJenis] ?? ''));
            $hariPenyelesaian = (int) ($row[$idxSla] ?? 0);

            // LOGIKA PENGADUAN: Deteksi jika penyelesaian pengaduan melebihi SLA
            $isSangatTerlambat = $hariPenyelesaian > 30; // High Risk
            $isSlaTerlewat = $hariPenyelesaian > 14;     // Moderate Risk

            if ($isSangatTerlambat || $isSlaTerlewat) {
                $riskLevel = $isSangatTerlambat ? 'High' : 'Moderate';
                if ($isSangatTerlambat) {
                    $countHigh++;
                } else {
                    $countModerate++;
                }

                $itemException = [
                    'tanggal_data'         => $tanggalMasuk,
                    'periode'              => date('Y-m-01', strtotime($tanggalMasuk)),
                    'kode_unit'            => $kodeUnit,
                    'nama_unit'            => $namaUnit,
                    'source_sheet'         => 'KKA_Pengaduan',
                    'nominal_terkait'      => 0,
                    'risk_awal'            => $riskLevel,
                    'jenis_exception_awal' => $isSangatTerlambat ? 'Penyelesaian SLA > 30 Hari' : 'Penyelesaian SLA Terlambat (> 14 Hari)',
                    'rincian'              => "Pengaduan: {$jenisPengaduan} ({$hariPenyelesaian} hari)",
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];

                if ($isDailyRegister) {
                    $dailyRegisterData[] = $itemException;
                } else {
                    $moderateHighRiskData[] = $itemException;
                }
            } else {
                $countLow++;
                $itemLow = [
                    'tanggal_data'    => $tanggalMasuk,
                    'kode_unit'       => $kodeUnit,
                    'nama_unit'       => $namaUnit,
                    'source_sheet'    => 'DUMP_05_PENGADUAN',
                    'kategori'        => 'Penyelesaian Tepat Waktu (SLA Terpenuhi)',
                    'nominal_terkait' => 0,
                    'risk_awal'       => 'Low',
                    'rincian'         => "Pengaduan: {$jenisPengaduan} ({$hariPenyelesaian} hari)",
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];

                $lowRiskData[] = $itemLow;
            }
        }
        fclose($file);

        if ($isDailyRegister) {
            if (!empty($dailyRegisterData)) {
                foreach (array_chunk($dailyRegisterData, 1000) as $chunk) {
                    DailyRegisterFinding::insert($chunk);
                }
            }
            if (!empty($lowRiskData)) {
                \App\Services\DailyRegister\DailyRegisterTmpService::storeLowRisk($kodeUnit, $lowRiskData);
            }
        } else {
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
        }

        return [
            'total_low'      => $countLow,
            'total_moderate' => $countModerate,
            'total_high'     => $countHigh,
        ];
    }
}
