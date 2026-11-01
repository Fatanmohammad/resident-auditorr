<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\DailyRegister;
use App\Models\DailyRegister\DailyRegisterFinding;

class DumpKreditParser
{
    public function parse($filePath, $kodeUnit, $isDailyRegister = false, $namaUnit = null): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File CSV Kredit tidak ditemukan.");
        }

        if (!$namaUnit) {
            $namaUnit = \App\Models\Unit::where('unit_code', $kodeUnit)->value('unit_name');
        }

        $file = fopen($filePath, 'r');
        
        // 1. Baca header dan buat mapping index otomatis
        $header = fgetcsv($file);
        $headerMap = [];
        if ($header) {
            foreach ($header as $index => $colName) {
                $cleaned = strtoupper(trim(preg_replace('/[^A-Za-z0-9_]/', '', $colName)));
                $headerMap[$cleaned] = $index;
            }
        }

        // Tentukan letak index kolom secara dinamis
        $idxDate = $headerMap['TGLMULAI'] 
            ?? ($headerMap['TGL_JT'] 
            ?? ($headerMap['TANGGAL'] 
            ?? 0));

        $idxKol = $headerMap['KOLEKTIBILITY'] 
            ?? ($headerMap['KOL'] 
            ?? ($headerMap['KOLEKTIBILITAS'] 
            ?? 1));

        $idxPlafon = $headerMap['PLAFOND'] 
            ?? ($headerMap['PLAFON'] 
            ?? ($headerMap['SALDO_AKHIR'] 
            ?? ($headerMap['BAKI_DEBET'] 
            ?? ($headerMap['NOMINAL'] ?? 2))));

        $idxNama = $headerMap['NAMA_SINGKAT'] 
            ?? ($headerMap['NAMA_DEBITUR'] 
            ?? ($headerMap['NAMA'] ?? null));

        $idxNoRek = $headerMap['NO_REK'] 
            ?? ($headerMap['NOREK'] ?? null);

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
            if (empty($rawDate) || (preg_match('/^[0-9]+$/', $rawDate) && strlen($rawDate) > 8)) {
                $tanggal = now()->toDateString();
            } else {
                $parsedDate = strtotime(str_replace('/', '-', $rawDate));
                $tanggal = $parsedDate ? date('Y-m-d', $parsedDate) : now()->toDateString();
            }

            $kolektibilitas = (int) ($row[$idxKol] ?? 1);
            $rawPlafon = trim($row[$idxPlafon] ?? 0);
            $cleanPlafon = preg_replace('/[,.]00$/', '', $rawPlafon);
            $plafon = (float) str_replace(['Rp', ',', '.', ' '], '', $cleanPlafon);

            $namaDebitur = $idxNama !== null ? trim($row[$idxNama] ?? '') : '';
            $noRek = $idxNoRek !== null ? trim($row[$idxNoRek] ?? '') : '';

            // LOGIKA KREDIT (SOP 02):
            // 1. Plafon Jumbo (>= Rp 500.000.000) -> High Risk
            // 2. Kolektibilitas Turun (Kol >= 2) -> Moderate Risk
            // 3. Lainnya -> Low Risk (Wajar)
            $isKolTurun = $kolektibilitas >= 2;
            $isPlafonJumbo = $plafon >= 500000000;

            if ($isKolTurun || $isPlafonJumbo) {
                $riskLevel = $isPlafonJumbo ? 'High' : 'Moderate';
                if ($isPlafonJumbo) {
                    $countHigh++;
                } else {
                    $countModerate++;
                }

                $rincian = ($isKolTurun ? "Kolektibilitas {$kolektibilitas}" : "Plafon Jumbo")
                    . ($namaDebitur ? " ({$namaDebitur})" : '');

                $itemException = [
                    'tanggal_data'         => $tanggal,
                    'periode'              => date('Y-m-01', strtotime($tanggal)),
                    'kode_unit'            => $kodeUnit,
                    'nama_unit'            => $namaUnit,
                    'source_sheet'         => 'KKA_Kredit',
                    'nominal_terkait'      => $plafon,
                    'risk_awal'            => $riskLevel,
                    'jenis_exception_awal' => $isKolTurun ? 'Penurunan Kolektibilitas' : 'Pencairan Plafon Jumbo',
                    'rincian'              => $rincian,
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
                    'source_sheet'    => 'DUMP_03_KREDIT',
                    'kategori'        => 'Pencairan / Angsuran Wajar',
                    'nominal_terkait' => $plafon,
                    'risk_awal'       => 'Low',
                    'rincian'         => "Kol: {$kolektibilitas}" . ($namaDebitur ? " - {$namaDebitur}" : ''),
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
