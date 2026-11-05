<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\DailyRegister;
use App\Models\DailyRegister\DailyRegisterFinding;

class DumpCbsParser
{
    public function parse($filePath, $kodeUnit, $isDailyRegister = false, $namaUnit = null, $periode = null): array
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
                $cleanCol = preg_replace('/^\xEF\xBB\xBF/', '', trim((string)$colName));
                $headerMap[strtoupper($cleanCol)] = $index;
            }
        }

        $idxDate = $headerMap['TGL_TX'] ?? ($headerMap['TANGGAL'] ?? 0);
        $idxArsip = DumpParserHelper::findNoArsipIndex($header ?? []);
        $idxKetTx = DumpParserHelper::findKetTxIndex($header ?? []);
        $idxKeterangan = $headerMap['KETERANGAN'] ?? ($headerMap['KET'] ?? null);
        $idxKet = $idxKetTx ?? ($idxKeterangan ?? ($headerMap['RINCIAN'] ?? ($headerMap['DESKRIPSI'] ?? ($headerMap['URAIAN'] ?? ($headerMap['TRANSAKSI'] ?? 1)))));
        $idxNominal = $headerMap['JUMLAH_TX'] ?? ($headerMap['NOMINAL'] ?? ($headerMap['JUMLAH'] ?? ($headerMap['NILAI'] ?? ($headerMap['DEBET'] ?? ($headerMap['SALDO'] ?? 2)))));

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

            $rawKetTx = $idxKetTx !== null ? DumpParserHelper::cleanKetTx($row[$idxKetTx] ?? null) : null;
            $rawKet = $idxKeterangan !== null ? trim($row[$idxKeterangan] ?? '') : '';
            $keterangan = strtoupper(trim($row[$idxKet] ?? ''));
            $evalText = strtoupper(trim(($rawKet ? $rawKet . ' ' : '') . ($rawKetTx ?: $keterangan)));

            $nominal = (float) str_replace(['Rp', ',', '.', ' '], '', $row[$idxNominal] ?? 0);

            // DETEKSI LOGIKA EXCEL SOP 02 & KEYWORD HARDCODE (REVERSAL & TPP)
            $isReversal = strpos($evalText, 'REV-') !== false || strpos($evalText, 'PEMBATALAN') !== false;
            $isHighNominal = $nominal >= 50000000;
            $isTpp = (stripos($evalText, 'TPP') !== false || stripos($evalText, 'TUNJANGAN PERBAIKAN PENGHASILAN') !== false);

            $riskLevel = ($isReversal || $isHighNominal || $isTpp) ? ($isHighNominal ? 'High' : 'Moderate') : 'Low';
            
            if ($riskLevel === 'High') {
                $countHigh++;
            } elseif ($riskLevel === 'Moderate') {
                $countModerate++;
            }

            if ($isReversal || $isHighNominal || $isTpp) {
                // =========================================================
                // PEMETAAN KE SHEET KKA SESUAI STANDAR 6 TAB KKA HARIAN
                // =========================================================
                $sourceSheet = 'KKA_Teller_Kas'; // Default Fallback Teller & Kas

                if (preg_match('/(BIAYA|INTERN|JURNAL INTERN|PENAMPUNG|OVERBOOKING AKUN)/i', $evalText)) {
                    $sourceSheet = 'KKA_Biaya_Beban';
                } elseif (preg_match('/(KREDIT|PENCAIRAN|ANGSURAN|PELUNASAN)/i', $evalText)) {
                    $sourceSheet = 'KKA_Kredit';
                } elseif (preg_match('/(TRANSFER|KLIRING|SKN|RTGS|CEK|BG|TPP|TUNJANGAN|KIRIMAN UANG|INWARD|OUTWARD|TELLER|KAS|SETOR|TARIK|OTORISASI|SELISIH KAS|KAS\s*KECIL|KHASANAH|BRANKAS|KAS\s*BESAR)/i', $evalText)) {
                    $sourceSheet = 'KKA_Teller_Kas';
                }
            } else {
                $sourceSheet = 'DUMP_01_CBS';
            }

            $jenisException = $isReversal ? 'Indikasi Reversal' : ($isTpp ? 'Transaksi TPP' : 'Nominal Melewati Limit');

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
                        'rincian'         => $rawKetTx ?: $keterangan,
                    ];
                } else {
                    $subArea = null;
                    if ($sourceSheet === 'KKA_Teller_Kas') {
                        if (preg_match('/KAS\s*KECIL/i', $evalText)) {
                            $subArea = ['A2'];
                        } elseif (preg_match('/(KHASANAH|BRANKAS|KAS\s*BESAR)/i', $evalText)) {
                            $subArea = ['A3'];
                        } else {
                            $subArea = ['A1'];
                        }
                    }

                    $noArsip = $idxArsip !== null ? DumpParserHelper::cleanNoArsip($row[$idxArsip] ?? null) : null;
                    $deskripsiUtama = $rawKetTx ?: ($keterangan ?: $jenisException);

                    $dailyRegisterData[] = [
                        'no_arsip'             => $noArsip,
                        'tanggal_data'         => $tanggal,
                        'periode'              => $periode ?: date('Y-m-01', strtotime($tanggal)),
                        'kode_unit'            => $kodeUnit,
                        'nama_unit'            => $namaUnit,
                        'source_sheet'         => $sourceSheet,
                        'nominal_terkait'      => $nominal,
                        'risk_awal'            => $riskLevel,
                        'jenis_exception_awal' => $jenisException,
                        'deskripsi'            => $deskripsiUtama,
                        'rincian'              => $deskripsiUtama,
                        'selected_sub_areas'   => $subArea ? json_encode($subArea) : null,
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                }
            } else {
                $jenisException = $isReversal ? 'Indikasi Reversal' : ($isTpp ? 'Transaksi TPP' : 'Nominal Melewati Limit');

                if ($riskLevel === 'High' || $riskLevel === 'Moderate') {
                    $moderateHighRiskData[] = [
                        'tanggal_data'         => $tanggal,
                        'periode'              => $periode ?: date('Y-m-01', strtotime($tanggal)),
                        'kode_unit'            => $kodeUnit,
                        'nama_unit'            => $namaUnit,
                        'source_sheet'         => $sourceSheet,
                        'nominal_terkait'      => $nominal,
                        'risk_awal'            => $riskLevel,
                        'jenis_exception_awal' => $jenisException,
                        'deskripsi'            => $rawKetTx ?: $keterangan,
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                } else {
                    $lowRiskData[] = [
                        'tanggal_data'    => $tanggal,
                        'periode'         => $periode ?: date('Y-m-01', strtotime($tanggal)),
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