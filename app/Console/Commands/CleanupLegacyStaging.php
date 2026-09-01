<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupLegacyStaging extends Command
{
    protected $signature = 'offsite:cleanup-legacy
                            {--force : Skip konfirmasi interaktif}
                            {--wp= : Hanya bersihkan WP ID tertentu (opsional)}';

    protected $description = 'Bersihkan data staging & KKA lama yang berasal dari flow upload langsung (bukan via dump_*).';

    private array $kkaTables = [
        'kka_teller_kas',
        'kka_kredit',
        'kka_biaya_beban',
        'kka_biaya_internal',
        'kka_pengaduan',
        'kka_transaksi_umum',
        'kka_transfer_ku',
    ];

    public function handle(): int
    {
        $wpId = $this->option('wp');

        // Hitung scope yang akan dihapus
        $stagingQuery = DB::table('staging_offsite')->whereNull('source_record_id');
        if ($wpId) {
            $stagingQuery->where('wp_offsite_id', $wpId);
        }

        $jumlahStaging = $stagingQuery->count();

        if ($jumlahStaging === 0) {
            $this->info('Tidak ada data legacy yang perlu dibersihkan.');
            return self::SUCCESS;
        }

        // Tampilkan ringkasan sebelum hapus
        $this->table(['Tabel', 'Jumlah baris'], $this->hitungRingkasan($wpId));

        if (!$this->option('force')) {
            if (!$this->confirm("Lanjutkan penghapusan {$jumlahStaging} baris staging legacy beserta KKA terkait?")) {
                $this->warn('Dibatalkan.');
                return self::FAILURE;
            }
        }

        DB::transaction(function () use ($wpId, $jumlahStaging) {
            // Ambil staging_ids yang akan dihapus
            $stagingIds = DB::table('staging_offsite')
                ->whereNull('source_record_id')
                ->when($wpId, fn($q) => $q->where('wp_offsite_id', $wpId))
                ->pluck('id');

            // Hapus KKA yang staging_id-nya termasuk dalam list
            foreach ($this->kkaTables as $table) {
                $deleted = DB::table($table)->whereIn('staging_id', $stagingIds)->delete();
                if ($deleted > 0) {
                    $this->line("  <fg=yellow>Hapus</> {$deleted} baris dari {$table}");
                }
            }

            // Hapus register_harian yang tidak punya staging valid lagi
            if ($wpId) {
                DB::table('register_harian')->where('wp_offsite_id', $wpId)->delete();
            } else {
                // Hapus register_harian yang wp_offsite_id-nya tidak punya staging valid
                $wpIdsLegacy = DB::table('staging_offsite')
                    ->whereNull('source_record_id')
                    ->pluck('wp_offsite_id')
                    ->unique();
                DB::table('register_harian')->whereIn('wp_offsite_id', $wpIdsLegacy)->delete();
            }

            // Hapus staging legacy
            DB::table('staging_offsite')
                ->whereNull('source_record_id')
                ->when($wpId, fn($q) => $q->where('wp_offsite_id', $wpId))
                ->delete();

            $this->info("✓ {$jumlahStaging} baris staging legacy berhasil dihapus.");
        });

        $this->info('Cleanup selesai. Silakan upload ulang CSV via /ra-offsite/upload untuk mengisi data baru.');

        return self::SUCCESS;
    }

    private function hitungRingkasan(?string $wpId): array
    {
        $stagingIds = DB::table('staging_offsite')
            ->whereNull('source_record_id')
            ->when($wpId, fn($q) => $q->where('wp_offsite_id', $wpId))
            ->pluck('id');

        $rows = [
            ['staging_offsite (legacy)', $stagingIds->count()],
        ];

        foreach ($this->kkaTables as $table) {
            $count = DB::table($table)->whereIn('staging_id', $stagingIds)->count();
            if ($count > 0) {
                $rows[] = [$table, $count];
            }
        }

        return $rows;
    }
}
