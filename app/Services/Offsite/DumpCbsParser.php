<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\DailyRegister;
use App\Models\DailyRegister\DailyRegisterFinding;

class DumpCbsParser
{
    public function parse($filePath, $kodeUnit, $isDailyRegister = false, $namaUnit = null): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File CSV tidak ditemukan di folder staging.");
        }

        if (!$namaUnit) {
            $namaUnit = \App\Models\Unit::where('unit_code', $kodeUnit)->value('unit_name');
        }

        $file = fopen($filePath, 'r');
        
        $header = fgetcsv($file);
        $headerMap = [];
        if ($header) {
            foreach ($header as $index => $colName) {
                $headerMap[trim(strtoupper($colName))] = $index;
            }
        }

        $idxDate = $headerMap['TGL_TX'] ?? ($headerMap['TANGGAL'] ?? 0);
        $idxKet = $headerMap['KET_TX'] ?? ($headerMap['KETERANGAN'] ?? 1);
        $idxNominal = $headerMap['JUMLAH_TX'] ?? ($headerMap['NOMINAL'] ?? 2);

        $lowRiskData = [];
        $moderateHighRiskData = [];
        $dailyRegisterData = [];
        $countHigh = 0;
        $countModerate = 0;

        while (($row = fgetcsv($file)) !== false) {
            
            // Auto-detect Date
            $rawDate = trim($row[$idxDate] ?? '');
            if (empty($rawDate) || preg_match('/^[0-9]+$/', $rawDate)) {
                $tanggal = now()->toDateString();
            } else {
                $parsedDate = strtotime(str_replace('/', '-', $rawDate));
                $tanggal = $parsedDate ? date('Y-m-d', $parsedDate) : now()->toDateString();
            }

            $keterangan = strtoupper(trim($row[$idxKet] ?? ''));
            $nominal = (float) str_replace(['Rp', ',', '.'], '', $row[$idxNominal] ?? 0);

            // DETEKSI LOGIKA EXCEL SOP 02
            $isReversal = strpos($keterangan, 'REV-') !== false || strpos($keterangan, 'PEMBATALAN') !== false;
            $isHighNominal = $nominal >= 50000000;

            $riskLevel = ($isReversal || $isHighNominal) ? ($isHighNominal ? 'High' : 'Moderate') : 'Low';
            
            if ($riskLevel === 'High') {
                $countHigh++;
            } elseif ($riskLevel === 'Moderate') {
                $countModerate++;
            }

            if ($isReversal || $isHighNominal) {
                // =========================================================
                // PEMETAAN KE SHEET KKA SESUAI STANDAR SOP 04.2
                // =========================================================
                $sourceSheet = 'KKA_Teller_Kas'; // Default Fallback Teller & Kas

                if (preg_match('/(TRANSFER|KLIRING|SKN|RTGS|KIRIMAN UANG|INWARD|OUTWARD|TELLER|KAS|SETOR|TARIK|OTORISASI|SELISIH KAS)/i', $keterangan)) {
                    $sourceSheet = 'KKA_Teller_Kas';
                } elseif (preg_match('/(BIAYA|INTERN|JURNAL INTERN|PENAMPUNG|OVERBOOKING AKUN)/i', $keterangan)) {
                    $sourceSheet = 'KKA_Biaya_Beban';
                } elseif (preg_match('/(KREDIT|PENCAIRAN|ANGSURAN|PELUNASAN)/i', $keterangan)) {
                    $sourceSheet = 'KKA_Kredit';
                }
            } else {
                $sourceSheet = 'DUMP_01_CBS';
            }

            if ($isDailyRegister) {
                if ($riskLevel === 'Low') {
                    $lowRiskData[] = [
                        'tanggal_data'    => $tanggal,
                        'kode_unit'       => $kodeUnit,
                        'nama_unit'       => $namaUnit,
                        'source_sheet'    => 'DUMP_01_CBS',
                        'kategori'        => 'Transaksi Wajar / Low Risk',
                        'nominal_terkait' => $nominal,
                        'risk_awal'       => 'Low',
                        'rincian'         => $keterangan,
                    ];
                } else {
                    $dailyRegisterData[] = [
                        'tanggal_data'         => $tanggal,
                        'periode'              => date('Y-m-01', strtotime($tanggal)),
                        'kode_unit'            => $kodeUnit,
                        'nama_unit'            => $namaUnit,
                        'source_sheet'         => $sourceSheet,
                        'nominal_terkait'      => $nominal,
                        'risk_awal'            => $riskLevel,
                        'jenis_exception_awal' => $isReversal ? 'Indikasi Reversal' : 'Nominal Melewati Limit',
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                }
            } else {
                if ($riskLevel === 'High' || $riskLevel === 'Moderate') {
                    $moderateHighRiskData[] = [
                        'tanggal_data'         => $tanggal,
                        'periode'              => date('Y-m-01', strtotime($tanggal)),
                        'kode_unit'            => $kodeUnit,
                        'nama_unit'            => $namaUnit,
                        'source_sheet'         => $sourceSheet,
                        'nominal_terkait'      => $nominal,
                        'risk_awal'            => $riskLevel,
                        'jenis_exception_awal' => $isReversal ? 'Indikasi Reversal' : 'Nominal Melewati Limit',
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                } else {
                    $lowRiskData[] = [
                        'tanggal_data'    => $tanggal,
                        'kode_unit'       => $kodeUnit,
                        'nama_unit'       => $namaUnit,
                        'source_sheet'    => $sourceSheet,
                        'kategori'        => 'Transaksi Wajar / Low Risk',
                        'nominal_terkait' => $nominal,
                        'risk_awal'       => 'Low',
                        'rincian'         => $keterangan,
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

        return [
            'total_low'      => count($lowRiskData),
            'total_moderate' => $countModerate,
            'total_high'     => $countHigh,
        ];
    }
}