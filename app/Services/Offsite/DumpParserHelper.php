<?php

namespace App\Services\Offsite;

class DumpParserHelper
{
    /**
     * Cari index kolom dari baris header dengan mencocokkan beberapa variasi kandidat (case-insensitive & alphanumeric).
     */
    public static function findColumnIndex(array $headerRow, array $candidates): ?int
    {
        $cleanMap = [];
        foreach ($headerRow as $idx => $col) {
            $clean = strtoupper(trim(preg_replace('/[^A-Za-z0-9]/', '', (string)$col)));
            if ($clean !== '') {
                $cleanMap[$clean] = $idx;
            }
        }

        // 1. Exact match
        foreach ($candidates as $cand) {
            $candClean = strtoupper(trim(preg_replace('/[^A-Za-z0-9]/', '', $cand)));
            if (isset($cleanMap[$candClean])) {
                return $cleanMap[$candClean];
            }
        }

        // 2. Fuzzy / Prefix match (e.g. "NO_ARSIP (VOUCHER/REF)" -> "NOARSIPVOUCHERREF" starts with "NOARSIP")
        foreach ($candidates as $cand) {
            $candClean = strtoupper(trim(preg_replace('/[^A-Za-z0-9]/', '', $cand)));
            if (strlen($candClean) < 3) continue;
            foreach ($cleanMap as $colClean => $idx) {
                if (str_starts_with($colClean, $candClean) || str_starts_with($candClean, $colClean)) {
                    return $idx;
                }
            }
        }

        return null;
    }

    /**
     * Cari index No. Arsip dengan variasi: NO_ARSIP, NO ARSIP, ARSIP, dan fallback NO_VOUCHER, NO_REF, NO_BUKTI.
     */
    public static function findNoArsipIndex(array $headerRow): ?int
    {
        return self::findColumnIndex($headerRow, [
            'NO_ARSIP', 'NO ARSIP', 'NOARSIP', 'ARSIP',
            'NOMOR_ARSIP', 'NOMOR ARSIP', 'NOMORARSIP',
            'NO_VOUCHER', 'NO VOUCHER', 'NOVOUCHER', 'VOUCHER',
            'NOMOR_VOUCHER', 'NOMOR VOUCHER', 'NOMORVOUCHER',
            'NO_REF', 'NO REF', 'NOREF', 'REF', 'REFERENSI', 'NO_REFERENSI',
            'NOMOR_REF', 'NOMOR REF', 'NOMORREF',
            'NO_BUKTI', 'NO BUKTI', 'NOBUKTI', 'BUKTI',
            'NOMOR_BUKTI', 'NOMOR BUKTI', 'NOMORBUKTI'
        ]);
    }

    /**
     * Cari index KET_TX dengan variasi: KET_TX, ket_tx, KETERANGAN_TX, KET_TRANSAKSI, KETERANGAN, DESKRIPSI, RINCIAN, URAIAN.
     */
    public static function findKetTxIndex(array $headerRow): ?int
    {
        return self::findColumnIndex($headerRow, [
            'KET_TX', 'KET TX', 'KETTX',
            'KETERANGAN_TX', 'KETERANGAN TX', 'KETERANGANTX',
            'KET_TRANSAKSI', 'KET TRANSAKSI', 'KETTRANSAKSI',
            'KETERANGAN_TRANSAKSI', 'KETERANGAN TRANSAKSI', 'KETERANGANTRANSAKSI',
            'KETERANGAN', 'KET', 'DESKRIPSI', 'URAIAN', 'RINCIAN', 'TRANSAKSI',
            'NARASI', 'NARRATIVE', 'MEMO', 'NOTE', 'NOTES', 'CATATAN', 'PENJELASAN'
        ]);
    }

    /**
     * Bersihkan nilai no_arsip.
     */
    public static function cleanNoArsip(?string $val): ?string
    {
        if ($val === null) return null;
        $t = trim($val);
        if ($t === '' || in_array(strtoupper($t), ['-', 'NULL', 'NONE', 'N/A'])) {
            return null;
        }
        return $t;
    }

    /**
     * Bersihkan nilai keterangan / deskripsi.
     */
    public static function cleanKetTx(?string $val): ?string
    {
        if ($val === null) return null;
        $t = trim($val);
        if ($t === '' || in_array(strtoupper($t), ['-', 'NULL', 'NONE', 'N/A'])) {
            return null;
        }
        return $t;
    }
}
