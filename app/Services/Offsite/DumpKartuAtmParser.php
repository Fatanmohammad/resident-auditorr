<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\DailyRegister;
use App\Models\DailyRegister\DailyRegisterFinding;

class DumpKartuAtmParser
{
    /**
     * Memproses file CSV DUMP 06 (Mutasi Persediaan & Biaya Kartu ATM)
     * Mengikuti parameter dan aturan bisnis SOP 04.2 (DUMP_Kartu_ATM)
     */
    public function parse($filePath, $kodeUnit, $isDailyRegister = false, $namaUnit = null): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File CSV Kartu ATM tidak ditemukan.");
        }

        if (!$namaUnit) {
            $namaUnit = \App\Models\Unit::where('unit_code', $kodeUnit)->value('unit_name');
        }

        $file = fopen($filePath, 'r');
        
        // 1. BACA HEADER DAN MAPPING KOLOM
        $header = fgetcsv($file);
        $headerMap = [];
        if ($header) {
            foreach ($header as $index => $colName) {
                $cleanedName = strtoupper(trim(preg_replace('/[^A-Za-z0-9_]/', '', $colName)));
                $headerMap[$cleanedName] = $index;
            }
        }

        // Cari index kolom dinamis berdasarkan nama kolom
        $idxDate = $headerMap['TANGGAL'] ?? ($headerMap['TGL'] ?? ($headerMap['TGL_TX'] ?? 0));
        $idxArsip = $headerMap['NO_ARSIP'] ?? ($headerMap['NOARSIP'] ?? ($headerMap['NO_SURAT'] ?? ($headerMap['NO_BUKTI'] ?? 1)));
        $idxKet = $headerMap['KETERANGAN'] ?? ($headerMap['KET'] ?? ($headerMap['URAIAN'] ?? 2));
        $idxNominal = $headerMap['NOMINAL'] ?? ($headerMap['BIAYA'] ?? ($headerMap['JUMLAH'] ?? ($headerMap['NILAI'] ?? 3)));
        $idxUser = $headerMap['USER_ID'] ?? ($headerMap['USERID'] ?? ($headerMap['USER'] ?? ($headerMap['PETUGAS'] ?? 4)));

        $lowRiskData = [];
        $moderateHighRiskData = [];
        $dailyRegisterData = [];
        
        $countHigh = 0;
        $countModerate = 0;
        $countLow = 0;

        // Track No Arsip untuk mendeteksi duplikasi di dalam batch/file (No Arsip Ganda)
        $seenArsip = [];

        while (($row = fgetcsv($file)) !== false) {
            if (empty($row) || count($row) < 2) continue;

            // --- 1. NORMALISASI TANGGAL ---
            $rawDate = trim($row[$idxDate] ?? '');
            if (empty($rawDate) || preg_match('/^[0-9]+$/', $rawDate)) {
                $tanggal = now()->toDateString();
            } else {
                $parsedDate = strtotime(str_replace('/', '-', $rawDate));
                $tanggal = $parsedDate ? date('Y-m-d', $parsedDate) : now()->toDateString();
            }

            $noArsip = trim($row[$idxArsip] ?? '');
            $keterangan = trim($row[$idxKet] ?? '-');
            $nominal = (float) str_replace(['Rp', ',', '.', ' '], '', $row[$idxNominal] ?? 0);
            $userId = trim($row[$idxUser] ?? '');

            // Track duplikasi no arsip
            $isDuplicateArsip = false;
            if (!empty($noArsip)) {
                if (isset($seenArsip[$noArsip])) {
                    $isDuplicateArsip = true;
                    $seenArsip[$noArsip]++;
                } else {
                    $seenArsip[$noArsip] = 1;
                }
            }

            // --- 2. EVALUASI PARAMETER ANOMALI SESUAI SOP 04.2 EXCEL ---
            $isUserEmpty = empty($userId) || in_array(strtoupper($userId), ['-', 'NULL', 'NONE']);
            $isArsipEmpty = empty($noArsip) || in_array(strtoupper($noArsip), ['-', 'NULL', 'NONE']);
            $isMaterial = $nominal >= 10000000; // Pengeluaran/biaya kartu >= 10 Juta (Certainty)
            
            // Keyword Triggers di Keterangan
            $hasSelisihHilang = (bool) preg_match('/(SELISIH|HILANG|TERTELAN|VOID|ANOMALI)/i', $keterangan);
            $hasRusakKoreksi  = (bool) preg_match('/(RUSAK|CACAT|KOREKSI|PEMBATALAN|MANUAL|RETUR)/i', $keterangan);

            // Tentukan Level Risiko & Jenis Exception
            $riskLevel = 'Low';
            $jenisException = null;
            $deskripsiException = null;

            if ($isArsipEmpty) {
                $riskLevel = 'High';
                $jenisException = 'No Arsip Mutasi Kosong (Untraceable)';
                $deskripsiException = "Mutasi kartu ATM pada tanggal {$tanggal} tidak memiliki nomor arsip/bukti yang dapat ditelusuri.";
            } elseif ($isDuplicateArsip) {
                $riskLevel = 'High';
                $jenisException = 'Indikasi Duplikasi No Arsip Mutasi';
                $deskripsiException = "Nomor arsip {$noArsip} tercatat lebih dari satu kali pada mutasi kartu ATM.";
            } elseif ($isUserEmpty) {
                $riskLevel = 'High';
                $jenisException = 'User ID Petugas Tidak Tercatat';
                $deskripsiException = "Transaksi mutasi kartu ATM no arsip {$noArsip} tidak memuat User ID petugas yang bertanggung jawab.";
            } elseif ($hasSelisihHilang) {
                $riskLevel = 'High';
                $jenisException = 'Indikasi Selisih / Kehilangan Fisik Kartu';
                $deskripsiException = "Terdeteksi transaksi kartu berisiko: {$keterangan} pada no arsip {$noArsip}.";
            } elseif ($hasRusakKoreksi) {
                $riskLevel = 'Moderate';
                $jenisException = 'Mutasi Kartu Rusak / Koreksi Manual';
                $deskripsiException = "Tercatat mutasi koreksi atau kartu rusak: {$keterangan} pada no arsip {$noArsip}.";
            } elseif ($isMaterial) {
                $riskLevel = 'Moderate';
                $jenisException = 'Nominal Biaya Kartu Material (> Rp 10 Juta)';
                $deskripsiException = "Transaksi kartu memiliki nilai nominal material sebesar Rp " . number_format($nominal, 0, ',', '.') . " pada no arsip {$noArsip}.";
            }

            // Hitung statistik
            if ($riskLevel === 'High') {
                $countHigh++;
            } elseif ($riskLevel === 'Moderate') {
                $countModerate++;
            } else {
                $countLow++;
            }

            // --- 3. PEMBENTUKAN RECORD DATABASE ---
            if ($isDailyRegister) {
                if ($riskLevel !== 'Low') {
                    $dailyRegisterData[] = [
                        'tanggal_data'         => $tanggal,
                        'periode'              => date('Y-m-01', strtotime($tanggal)),
                        'kode_unit'            => $kodeUnit,
                        'nama_unit'            => $namaUnit,
                        'source_sheet'         => 'KKA_Kartu_ATM',
                        'nominal_terkait'      => $nominal,
                        'risk_awal'            => $riskLevel,
                        'jenis_exception_awal' => $jenisException,
                        'deskripsi'            => $deskripsiException,
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                } else {
                    $lowRiskData[] = [
                        'tanggal_data'    => $tanggal,
                        'kode_unit'       => $kodeUnit,
                        'nama_unit'       => $namaUnit,
                        'source_sheet'    => 'DUMP_06_KARTU_ATM',
                        'kategori'        => 'Mutasi Kartu Wajar',
                        'nominal_terkait' => $nominal,
                        'risk_awal'       => 'Low',
                        'rincian'         => "No Arsip: {$noArsip} | User: {$userId} | {$keterangan}",
                    ];
                }
            } else {
                if ($riskLevel !== 'Low') {
                    $moderateHighRiskData[] = [
                        'tanggal_data'         => $tanggal,
                        'periode'              => date('Y-m-01', strtotime($tanggal)),
                        'kode_unit'            => $kodeUnit,
                        'nama_unit'            => $namaUnit,
                        'source_sheet'         => 'KKA_Kartu_ATM',
                        'nominal_terkait'      => $nominal,
                        'risk_awal'            => $riskLevel,
                        'jenis_exception_awal' => $jenisException,
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                } else {
                    $lowRiskData[] = [
                        'tanggal_data'    => $tanggal,
                        'kode_unit'       => $kodeUnit,
                        'nama_unit'       => $namaUnit,
                        'source_sheet'    => 'DUMP_06_KARTU_ATM',
                        'kategori'        => 'Mutasi Kartu Wajar',
                        'nominal_terkait' => $nominal,
                        'rincian'         => "No Arsip: {$noArsip} | User: {$userId} | {$keterangan}",
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }
            }
        }
        fclose($file);

        // 4. INSERT KE DATABASE SECARA BATCH
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
