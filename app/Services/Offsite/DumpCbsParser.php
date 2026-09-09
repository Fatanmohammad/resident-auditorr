<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\DailyRegister;

class DumpCbsParser
{
    public function parse($filePath, $kodeUnit)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File CSV tidak ditemukan di folder staging.");
        }

        $file = fopen($filePath, 'r');
        fgetcsv($file); // Skip Header

        $lowRiskData = [];
        $moderateHighRiskData = [];

        while (($row = fgetcsv($file)) !== false) {
            
            // Auto-detect Date
            $rawDate = trim($row[0] ?? '');
            if (empty($rawDate) || preg_match('/^[0-9]+$/', $rawDate)) {
                $tanggal = now()->toDateString();
            } else {
                $parsedDate = strtotime($rawDate);
                $tanggal = $parsedDate ? date('Y-m-d', $parsedDate) : now()->toDateString();
            }

            $keterangan = strtoupper(trim($row[1] ?? ''));
            $nominal = (float) ($row[2] ?? 0);

            // DETEKSI LOGIKA EXCEL SOP 02
            $isReversal = strpos($keterangan, 'REV-') !== false || strpos($keterangan, 'PEMBATALAN') !== false;
            $isHighNominal = $nominal >= 50000000;

            if ($isReversal || $isHighNominal) {
                // =========================================================
                // PEMETAAAN OTOMATIS KE 5 SHEET KKA TUJUAN DARI DUMP_01
                // =========================================================
                $sourceSheet = 'KKA_Transaksi_Umum'; // Default Fallback

                if (preg_match('/(TRANSFER|KLIRING|SKN|RTGS|KIRIMAN UANG|INWARD|OUTWARD)/i', $keterangan)) {
                    $sourceSheet = 'KKA_Transfer_KU';
                } elseif (preg_match('/(TELLER|KAS|SETOR|TARIK|OTORISASI|SELISIH KAS)/i', $keterangan)) {
                    $sourceSheet = 'KKA_Teller_Kas';
                } elseif (preg_match('/(BIAYA|INTERN|JURNAL INTERN|PENAMPUNG|OVERBOOKING AKUN)/i', $keterangan)) {
                    $sourceSheet = 'KKA_Biaya_Internal';
                } elseif (preg_match('/(KREDIT|PENCAIRAN|ANGSURAN|PELUNASAN)/i', $keterangan)) {
                    $sourceSheet = 'KKA_Kredit';
                }

                $moderateHighRiskData[] = [
                    'tanggal_data'         => $tanggal,
                    'kode_unit'            => $kodeUnit,
                    'source_sheet'         => $sourceSheet, // Mengikuti aturan Excel
                    'nominal_terkait'      => $nominal,
                    'risk_awal'            => $isHighNominal ? 'High' : 'Moderate',
                    'jenis_exception_awal' => $isReversal ? 'Indikasi Reversal' : 'Nominal Melewati Limit',
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];
            } else {
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

        if (!empty($lowRiskData)) {
            foreach (array_chunk($lowRiskData, 1000) as $chunk) DailyRegister::insert($chunk);
        }
        if (!empty($moderateHighRiskData)) {
            foreach (array_chunk($moderateHighRiskData, 1000) as $chunk) KkaFinding::insert($chunk);
        }

        return true;
    }
}