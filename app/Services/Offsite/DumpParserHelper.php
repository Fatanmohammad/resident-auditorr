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
            $colWithoutBom = preg_replace('/^\xEF\xBB\xBF/', '', (string)$col);
            $clean = strtoupper(trim(preg_replace('/[^A-Za-z0-9]/', '', $colWithoutBom)));
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
     * Cari index Kolom Tanggal / Timestamp dengan memprioritaskan TIME_STAMP sesuai standar Corebanking.
     */
    public static function findDateColumnIndex(array $headerRow): ?int
    {
        return self::findColumnIndex($headerRow, [
            'TIME_STAMP', 'TIME STAMP', 'TIMESTAMP', 'TIMESTAMPS',
            'TGL_TX', 'TGL TX', 'TGLTX',
            'TGL_TRANSAKSI', 'TGL TRANSAKSI', 'TGLTRANSAKSI',
            'TANGGAL_TX', 'TANGGAL TX', 'TANGGALTX',
            'TANGGAL', 'DATE', 'DATETIME', 'WAKTU', 'JAM'
        ]);
    }

    /**
     * Parsing format Timestamp / Tanggal Corebanking ke format standar YYYY-MM-DD.
     * Mampu menangani format unik DD/MM/YYYY HH:MM:SS:UUUUUU (titik dua sebelum mikrodetik bervariasi).
     */
    public static function parseTimestampToDate(?string $raw): string
    {
        if ($raw === null) {
            return now()->toDateString();
        }
        $raw = trim(preg_replace('/^\xEF\xBB\xBF/', '', (string)$raw));
        if ($raw === '') {
            return now()->toDateString();
        }

        // Format 8 digit YYYYMMDD
        if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $raw, $m)) {
            $year = (int)$m[1];
            $month = (int)$m[2];
            $day = (int)$m[3];
            if ($year >= 1970 && $year <= 2100 && $month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                return "{$m[1]}-{$m[2]}-{$m[3]}";
            }
        }

        if (preg_match('/^[0-9]+$/', $raw)) {
            return now()->toDateString();
        }

        // 1. Format: DD/MM/YYYY or DD-MM-YYYY (e.g. 13/08/2026 08:19:50:307034)
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $raw, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $year = $m[3];
            return "{$year}-{$month}-{$day}";
        }

        // 2. Format: YYYY-MM-DD or YYYY/MM/DD
        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})/', $raw, $m)) {
            $year = $m[1];
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $day = str_pad($m[3], 2, '0', STR_PAD_LEFT);
            return "{$year}-{$month}-{$day}";
        }

        // 3. Fallback: buang mikrodetik setelah titik dua ketiga jika ada, lalu strtotime
        $clean = preg_replace('/(\d{2}:\d{2}:\d{2}):\d+/', '$1', $raw);
        $parsed = strtotime(str_replace('/', '-', $clean));
        return $parsed ? date('Y-m-d', $parsed) : now()->toDateString();
    }

    /**
     * Cek apakah tanggal transaksi ($rowDate dalam format YYYY-MM-DD) berada dalam rentang filter periode.
     */
    public static function isWithinDateRange(string $rowDate, ?string $startDate, ?string $endDate): bool
    {
        if ($startDate !== null && $startDate !== '') {
            if ($rowDate < $startDate) {
                return false;
            }
        }
        if ($endDate !== null && $endDate !== '') {
            if ($rowDate > $endDate) {
                return false;
            }
        }
        return true;
    }

    /**
     * Bersihkan nilai no_arsip.
     */
    public static function cleanNoArsip(?string $val): ?string
    {
        if ($val === null) return null;
        $t = trim(preg_replace('/^\xEF\xBB\xBF/', '', (string)$val));
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
        $t = trim(preg_replace('/^\xEF\xBB\xBF/', '', (string)$val));
        if ($t === '' || in_array(strtoupper($t), ['-', 'NULL', 'NONE', 'N/A'])) {
            return null;
        }
        return $t;
    }
}
