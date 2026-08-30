<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StagingOffsite;
use App\Models\WpOffsite;
use App\Services\OffsiteDetectionService;

class RunOffsiteDetection extends Command
{
    protected $signature = 'offsite:detect {--wp_id= : ID WpOffsite spesifik jika ingin memproses 1 WP saja}';
    protected $description = 'Jalankan ulang deteksi risiko offsite pada tabel staging_offsites';

    public function handle(OffsiteDetectionService $detectionService)
    {
        $wpId = $this->option('wp_id');

        $query = StagingOffsite::query();
        if ($wpId) {
            $query->where('wp_offsite_id', $wpId);
        }

        $totalData = $query->count();
        if ($totalData === 0) {
            $this->warn('Tidak ada data di tabel staging_offsites yang perlu diproses.');
            return 0;
        }

        $this->info("Memulai proses deteksi untuk {$totalData} data...");
        $bar = $this->output->createProgressBar($totalData);
        $bar->start();

        $wpCache = [];

        $query->chunk(200, function ($stagings) use ($detectionService, &$wpCache, $bar) {
            foreach ($stagings as $staging) {
                if (!isset($wpCache[$staging->wp_offsite_id])) {
                    $wpCache[$staging->wp_offsite_id] = WpOffsite::find($staging->wp_offsite_id);
                }
                $wp = $wpCache[$staging->wp_offsite_id];

                if (!$wp) {
                    $bar->advance();
                    continue;
                }

                $dataArray = $staging->toArray();

                // 1. Jalankan Deteksi Risiko
                $result = $detectionService->detectBaris($dataArray, $staging->source_table ?? 'CBS', $wp);

                // 2. Jalankan Validasi Data Quality
                $dataQuality = $detectionService->validateDataQuality($dataArray, $wp);

                // 3. Simpan Hasil Deteksi ke Database
                $staging->update([
                    'area_review'         => $result['area_review'],
                    'risk_level'          => $result['risk_level'],
                    'case_id'             => $result['case_id'],
                    'kka_sheet_tujuan'    => $result['kka_sheet_tujuan'],
                    'exception_awal'      => $result['perlu_kka'] ? 1 : 0,
                    'masuk_kka_final'     => $result['perlu_kka'] ? 1 : 0,
                    'status_data_quality' => $dataQuality,
                ]);

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info('Proses deteksi selesai dan database berhasil diperbarui!');

        return 0;
    }
}