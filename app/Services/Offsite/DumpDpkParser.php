<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\DailyRegister;
use App\Models\DailyRegister\DailyRegisterFinding;

class DumpDpkParser
{
    public function parse($filePath, $kodeUnit, $isDailyRegister = false, $namaUnit = null): array
    {
        if (!file_exists($filePath)) throw new \Exception("File CSV DPK tidak ditemukan.");

        if (!$namaUnit) {
            $namaUnit = \App\Models\Unit::where('unit_code', $kodeUnit)->value('unit_name');
        }

        $file = fopen($filePath, 'r');
        
        // 1. BACA HEADER DAN BUAT MAPPING INDEX OTOMATIS
        $header = fgetcsv($file);
        $headerMap = [];
        if ($header) {
            foreach ($header as $index => $colName) {
                $headerMap[trim(strtoupper($colName))] = $index;
            }
        }

        // Tentukan letak index
        $idxDate = $headerMap['TGL_BUKA_REK'] ?? ($headerMap['TGL_TX_AKHIR'] ?? 0);
        $idxStatus = $headerMap['STSDESC'] ?? ($headerMap['KD_STATUS'] ?? 1);
        $idxNominal = $headerMap['SALDO_AKHIR'] ?? 2;
        $idxNoRek = $headerMap['NO_REK'] ?? 0;
        $idxNama = $headerMap['NAMA_SINGKAT'] ?? 2;

        $lowRiskData = [];
        $moderateHighRiskData = [];
        $dailyRegisterData = [];
        $countHigh = 0;
        $countModerate = 0;

        while (($row = fgetcsv($file)) !== false) {
            if (empty($row) || count($row) < 3) continue;

            $rawDate = trim($row[$idxDate] ?? '');
            if (empty($rawDate) || preg_match('/^[0-9]+$/', $rawDate)) {
                $tanggal = now()->toDateString();
            } else {
                $tanggal = date('Y-m-d', strtotime(str_replace('/', '-', $rawDate)));
            }

            $statusRekening = strtoupper(trim($row[$idxStatus] ?? '')); 
            $nominal = (float) ($row[$idxNominal] ?? 0); 
            $noRekening = $row[$idxNoRek] ?? '-';
            $namaNasabah = $row[$idxNama] ?? '-';

            // --- LOGIKA ALARM KKA ---
            $isDormantActive = ($statusRekening === 'DORMANT' && $nominal > 0);
            $isNewHighValue = ($statusRekening === 'BARU' && $nominal >= 100000000);

            $riskLevel = ($isDormantActive || $isNewHighValue) ? ($isDormantActive ? 'High' : 'Moderate') : 'Low';
            $jenisTemuan = $isDormantActive ? 'Aktivasi Dormant Ireguler' : 'Rekening Baru Nominal Besar';
            $deskripsiOtomatis = "Terdeteksi anomali pada rekening {$noRekening} atas nama {$namaNasabah} dengan status '{$statusRekening}' dan saldo/nominal Rp " . number_format($nominal, 0, ',', '.') . " di Unit {$kodeUnit}.";

            if ($riskLevel === 'High') {
                $countHigh++;
            } elseif ($riskLevel === 'Moderate') {
                $countModerate++;
            }

            if ($isDailyRegister) {
                if ($riskLevel === 'Low') {
                    $lowRiskData[] = [
                        'tanggal_data'    => $tanggal,
                        'kode_unit'       => $kodeUnit,
                        'nama_unit'       => $namaUnit,
                        'source_sheet'    => 'DUMP_02_DPK',
                        'kategori'        => 'Pembukaan/Mutasi Wajar',
                        'nominal_terkait' => $nominal,
                        'risk_awal'       => 'Low',
                        'rincian'         => 'Status: ' . $statusRekening,
                    ];
                } else {
                    $dailyRegisterData[] = [
                        'tanggal_data'         => $tanggal,
                        'periode'              => date('Y-m-01', strtotime($tanggal)),
                        'kode_unit'            => $kodeUnit,
                        'nama_unit'            => $namaUnit,
                        'source_sheet'         => 'KKA_DPK_Layanan',
                        'nominal_terkait'      => $nominal,
                        'risk_awal'            => $riskLevel,
                        'jenis_exception_awal' => $jenisTemuan,
                        'deskripsi'            => $deskripsiOtomatis,
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                }
            } else {
                if ($isDormantActive || $isNewHighValue) {
                    $moderateHighRiskData[] = [
                        'tanggal_data' => $tanggal,
                        'periode' => date('Y-m-01', strtotime($tanggal)), // awal bulan sebagai penanda periode
                        'kode_unit' => $kodeUnit,
                        'nama_unit' => $namaUnit,
                        'source_sheet' => 'KKA_DPK_Layanan', 
                        'nominal_terkait' => $nominal,
                        'risk_awal' => $riskLevel,
                        'jenis_exception_awal' => $jenisTemuan,
                        'deskripsi' => $deskripsiOtomatis,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } else {
                    $lowRiskData[] = [
                        'tanggal_data' => $tanggal,
                        'kode_unit' => $kodeUnit,
                        'nama_unit' => $namaUnit,
                        'source_sheet' => 'DUMP_02_DPK',
                        'kategori' => 'Pembukaan/Mutasi Wajar',
                        'nominal_terkait' => $nominal,
                        'risk_awal' => 'Low',
                        'rincian' => 'Status: ' . $statusRekening,
                        'created_at' => now(),
                        'updated_at' => now(),
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