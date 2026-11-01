<?php

namespace App\Services\DailyRegister;

use App\Models\DailyRegister\DailyRegisterFinding;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class DailyRegisterTmpService
{
    /**
     * Dapatkan path folder penyimpanan file sementara (tmp)
     */
    public static function getDirectoryPath(): string
    {
        $dir = storage_path('app/tmp/daily_register');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Simpan data low-risk ke file JSON sementara untuk kode unit dan tanggal tertentu
     */
    public static function storeLowRisk(string $kodeUnit, array $records, ?string $date = null): void
    {
        if (empty($records)) {
            return;
        }

        $date = $date ?: Carbon::today()->format('Y-m-d');
        $dir = self::getDirectoryPath();
        $filePath = $dir . DIRECTORY_SEPARATOR . "low_risk_{$kodeUnit}_{$date}.json";

        $existing = [];
        if (File::exists($filePath)) {
            $content = File::get($filePath);
            $existing = json_decode($content, true) ?: [];
        }

        $merged = array_merge($existing, $records);
        File::put($filePath, json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Pembersihan berkala:
     * 1. Hapus file tmp berumur sebelum hari ini (< today)
     * 2. Bersihkan sisa data low risk di database utama
     */
    public static function cleanupExpired(): void
    {
        $today = Carbon::today()->format('Y-m-d');
        $dir = self::getDirectoryPath();

        // 1. Hapus file tmp sebelum tanggal hari ini
        if (File::isDirectory($dir)) {
            $files = File::files($dir);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                // Format: low_risk_{kode_unit}_{YYYY-MM-DD}.json
                if (preg_match('/low_risk_([A-Za-z0-9_-]+)_(\d{4}-\d{2}-\d{2})\.json$/', $filename, $matches)) {
                    $fileDate = $matches[2];
                    if ($fileDate < $today) {
                        File::delete($file->getPathname());
                    }
                }
            }
        }

        // 2. Bersihkan sisa data unreviewed low-risk lama di tabel daily_register_findings (hanya data mentah dump yang belum pernah di-review)
        DailyRegisterFinding::where('risk_awal', 'Low')
            ->whereDoesntHave('checklistAnswers')
            ->whereNull('status_review')
            ->whereNull('tanggal_ditemukan')
            ->whereNull('risk_final')
            ->delete();
    }

    /**
     * Ambil data low-risk aktif hari ini dari file tmp
     */
    public static function getTodayData(array $allowedUnitCodes = [], ?string $selectedUnit = null, ?string $selectedDate = null): Collection
    {
        self::cleanupExpired();

        $targetDate = $selectedDate ?: Carbon::today()->format('Y-m-d');
        $dir = self::getDirectoryPath();
        $allData = collect();

        if (!File::isDirectory($dir)) {
            return $allData;
        }

        $files = File::files($dir);
        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (preg_match('/low_risk_([A-Za-z0-9_-]+)_(\d{4}-\d{2}-\d{2})\.json$/', $filename, $matches)) {
                $unitCode = $matches[1];
                $fileDate = $matches[2];

                // Hanya ambil file sesuai target tanggal (default hari ini)
                if ($fileDate !== $targetDate) {
                    continue;
                }

                // Filter wewenang unit code jika dibatasi
                if (!empty($allowedUnitCodes) && !in_array($unitCode, $allowedUnitCodes)) {
                    continue;
                }

                // Filter jika ada request unit spesifik
                if (!empty($selectedUnit) && $unitCode !== $selectedUnit) {
                    continue;
                }

                $content = File::get($file->getPathname());
                $items = json_decode($content, true);
                if (is_array($items)) {
                    $allData = $allData->merge($items);
                }
            }
        }

        return $allData;
    }
}
