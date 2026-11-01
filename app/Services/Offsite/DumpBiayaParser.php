<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\DailyRegister;
use App\Models\DailyRegister\DailyRegisterFinding;

class DumpBiayaParser
{
    public function parse($filePath, $kodeUnit, $isDailyRegister = false, $namaUnit = null): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File CSV Biaya tidak ditemukan.");
        }

        if (!$namaUnit) {
            $namaUnit = \App\Models\Unit::where('unit_code', $kodeUnit)->value('unit_name');
        }

        $file = fopen($filePath, 'r');
        
        // 1. Deteksi header dan mapping index kolom secara dinamis
        $header = fgetcsv($file);
        $headerMap = [];
        if ($header) {
            foreach ($header as $index => $colName) {
                $cleaned = strtoupper(trim(preg_replace('/[^A-Za-z0-9_]/', '', $colName)));
                $headerMap[$cleaned] = $index;
            }
        }

        // Cari index kolom dinamis berdasarkan nama kolom standar Core Banking
        $idxDate = $headerMap['TGL_TX'] 
            ?? ($headerMap['TANGGAL'] 
            ?? ($headerMap['TGL'] 
            ?? 0));

        $idxKet = $headerMap['KET_TX'] 
            ?? ($headerMap['KETERANGAN'] 
            ?? ($headerMap['KET'] 
            ?? ($headerMap['DESKRIPSI'] 
            ?? ($headerMap['URAIAN'] 
            ?? ($headerMap['NAMA_AKUN'] 
            ?? ($headerMap['AKUN'] 
            ?? ($headerMap['AKUN_GL'] ?? 1)))))));

        $idxNominal = $headerMap['JUMLAH_TX'] 
            ?? ($headerMap['NOMINAL'] 
            ?? ($headerMap['JUMLAH'] 
            ?? ($headerMap['NILAI'] 
            ?? ($headerMap['DEBET'] 
            ?? ($headerMap['BIAYA'] ?? null)))));

        // Fallback jika nama kolom nominal tidak standar (hindari kolom NO_REK / NO_ARSIP)
        if ($idxNominal === null) {
            foreach ($headerMap as $col => $pos) {
                if (str_contains($col, 'JUMLAH') || str_contains($col, 'NOMINAL') || str_contains($col, 'BIAYA') || str_contains($col, 'NILAI')) {
                    $idxNominal = $pos;
                    break;
                }
            }
            if ($idxNominal === null) {
                $idxNoRek = $headerMap['NO_REK'] ?? ($headerMap['NOREK'] ?? null);
                $idxArsip = $headerMap['NO_ARSIP'] ?? ($headerMap['NOARSIP'] ?? null);
                for ($i = 2; $i < count($headerMap); $i++) {
                    if ($i !== $idxNoRek && $i !== $idxArsip && $i !== $idxKet) {
                        $idxNominal = $i;
                        break;
                    }
                }
                if ($idxNominal === null) {
                    $idxNominal = 2;
                }
            }
        }

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

            // Normalisasi Tanggal
            $rawDate = trim($row[$idxDate] ?? '');
            if (empty($rawDate) || preg_match('/^[0-9]+$/', $rawDate)) {
                $tanggal = now()->toDateString();
            } else {
                $parsedDate = strtotime(str_replace('/', '-', $rawDate));
                $tanggal = $parsedDate ? date('Y-m-d', $parsedDate) : now()->toDateString();
            }

            $akunGl = strtoupper(trim($row[$idxKet] ?? ''));
            $rawNominal = trim($row[$idxNominal] ?? 0);
            $cleanNominal = preg_replace('/[,.]00$/', '', $rawNominal);
            $nominal = (float) str_replace(['Rp', ',', '.', ' '], '', $cleanNominal);

            // LOGIKA BIAYA (SOP 02):
            // 1. Biaya Jumbo (>= Rp 25.000.000) -> High Risk
            // 2. Akun Sensitif (Entertain, Lain-lain, Konsultan, Promosi) -> Moderate Risk
            // 3. Lainnya -> Low Risk (Wajar)
            $isBiayaJumbo = $nominal >= 25000000;
            $isAkunSensitif = str_contains($akunGl, 'ENTERTAIN') 
                || str_contains($akunGl, 'LAIN-LAIN')
                || str_contains($akunGl, 'KONSULTAN')
                || str_contains($akunGl, 'PROMOSI');

            if ($isBiayaJumbo || $isAkunSensitif) {
                $riskLevel = $isBiayaJumbo ? 'High' : 'Moderate';
                if ($isBiayaJumbo) {
                    $countHigh++;
                } else {
                    $countModerate++;
                }

                $itemException = [
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

                if ($isDailyRegister) {
                    $dailyRegisterData[] = $itemException;
                } else {
                    $moderateHighRiskData[] = $itemException;
                }
            } else {
                $countLow++;
                $itemLow = [
                    'tanggal_data'    => $tanggal,
                    'kode_unit'       => $kodeUnit,
                    'nama_unit'       => $namaUnit,
                    'source_sheet'    => 'DUMP_04_BIAYA',
                    'kategori'        => 'Pengeluaran Wajar',
                    'nominal_terkait' => $nominal,
                    'risk_awal'       => 'Low',
                    'rincian'         => 'Akun: ' . ($akunGl ?: 'Beban Operasional'),
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
