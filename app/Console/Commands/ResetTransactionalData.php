<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ResetTransactionalData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:reset-transactions 
                            {--module=all : Modul yang akan direset (all, daily-csv, daily, offsite, onsite, risk, pelaporan)} 
                            {--files : Hapus juga file fisik CSV dan upload bukti di storage staging} 
                            {--force : Jalankan langsung tanpa konfirmasi interaktif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengosongkan data transaksional & upload file CSV (Offsite, Onsite, Daily Register) tanpa menghapus Master Data (User, Cabang, Unit, Setup).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $module = strtolower($this->option('module') ?? 'all');
        $cleanFiles = $this->option('files') || $module === 'all';
        $force = $this->option('force');

        $this->warn('================================================================');
        $this->warn('         PEMBERSIHAN DATA TRANSAKSI & UPLOAD CSV (RESET)        ');
        $this->warn('================================================================');
        $this->info("Modul target : " . strtoupper($module));
        $this->info("Hapus file CSV di storage : " . ($cleanFiles ? 'YA' : 'TIDAK'));
        $this->info("Master Data (User, Cabang, Unit, Master Parameter): AMAN / DIPERTAHANKAN");
        $this->newLine();

        if (!$force && !$this->confirm('Apakah Anda yakin ingin mengosongkan data pada modul ini?', false)) {
            $this->info('Operasi dibatalkan.');
            return 0;
        }

        $tablesToWipe = $this->getTablesForModule($module);

        if (empty($tablesToWipe)) {
            $this->error("Modul '{$module}' tidak valid. Pilihan modul: all, daily-csv, daily, offsite, onsite, risk, pelaporan");
            return 1;
        }

        $this->info("Memulai proses pembersihan tabel...");

        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

        $summary = [];

        foreach ($tablesToWipe as $table) {
            if (Schema::hasTable($table)) {
                $countBefore = DB::table($table)->count();
                DB::table($table)->truncate();
                $summary[] = [
                    'table' => $table,
                    'status' => 'Dikosongkan',
                    'rows_deleted' => $countBefore
                ];
            } else {
                $summary[] = [
                    'table' => $table,
                    'status' => 'Tidak Ditemukan (Lewati)',
                    'rows_deleted' => 0
                ];
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        // Bersihkan riwayat sinkronisasi di register_triggers (SOP 06) sesuai modul
        if (Schema::hasTable('register_triggers')) {
            $deletedTriggers = 0;
            if (in_array($module, ['all', 'daily-csv', 'daily'])) {
                $deletedTriggers += DB::table('register_triggers')
                    ->where('sumber', 'Daily Review')
                    ->where('source_wp_id', 'LIKE', 'DRF-%')
                    ->delete();
            }
            if (in_array($module, ['all', 'daily'])) {
                $deletedTriggers += DB::table('register_triggers')
                    ->where('sumber', 'Daily Review')
                    ->where('source_wp_id', 'LIKE', 'PKKA-%')
                    ->delete();
            }
            if (in_array($module, ['all', 'offsite'])) {
                $deletedTriggers += DB::table('register_triggers')
                    ->where('sumber', 'Offsite')
                    ->delete();
            }
            if (in_array($module, ['all', 'onsite'])) {
                $deletedTriggers += DB::table('register_triggers')
                    ->where('sumber', 'Onsite')
                    ->delete();
            }
            if ($deletedTriggers > 0) {
                $summary[] = [
                    'table' => 'register_triggers (SOP 06 Sync)',
                    'status' => 'Dibersihkan',
                    'rows_deleted' => $deletedTriggers
                ];
            }
        }

        $this->table(['Nama Tabel', 'Status', 'Baris Dihapus'], $summary);

        if ($cleanFiles) {
            $this->cleanStorageFiles($module);
        }

        $this->newLine();
        $this->info("Pembersihan data selesai dengan sukses! Sistem kembali bersih seperti belum ada transaksi/upload.");
        return 0;
    }

    /**
     * Daftar tabel per modul.
     */
    protected function getTablesForModule(string $module): array
    {
        $modules = [
            'daily-csv' => [
                'daily_register_findings',
                'daily_registers',
                'register_harian',
                'register_klarifikasis',
            ],
            'offsite' => [
                'dump_transaksi_cbs',
                'dump_biaya_beban',
                'dump_dpk_apuppt',
                'dump_kredit',
                'dump_pengaduan',
                'cbs_transactions',
                'tmp_cbs_transactions',
                'staging_offsite',
                'wp_offsite',
                'wp_offsite_stagings',
                'work_queues',
                'kka_findings',
                'kka_activity_logs',
                'kka_daily_summaries',
                'kka_monthly_summaries',
                'kka_teller_kas',
                'kka_transaksi_umum',
                'kka_transfer_ku',
                'kka_biaya_beban',
                'kka_biaya_internal',
                'kertas_kerja_audits',
                'kertas_hasil_audits',
            ],
            'onsite' => [
                'onsite_visits',
                'onsite_cash_opnames',
                'onsite_cash_opname_items',
                'onsite_observasi',
                'onsite_observasi_items',
                'onsite_rekon_cek_bg',
                'onsite_rekon_cek_bg_items',
                'onsite_rekon_kartu_atm',
                'onsite_rekon_kartu_atm_items',
                'onsite_rekon_barang_cetakan',
                'onsite_rekon_barang_cetakan_items',
                'onsite_permintaan_data',
                'onsite_permintaan_items',
                'onsite_kka_teller_kas',
                'onsite_kka_dpks',
                'onsite_kka_kredits',
                'onsite_kka_biayas',
                'onsite_kka_atms',
                'onsite_kka_pengaduans',
                'onsite_kka_findings',
                'onsite_populations',
                'scheduled_visits',
                'temuan_audits',
                'tindak_lanjuts',
                'scoring_audits',
            ],
            'daily' => [
                'physical_kkas',
                'physical_kka_cash_details',
                'physical_kka_obs_details',
                'physical_kka_stok_details',
                'physical_kka_kartu_atm_details',
                'physical_kka_barang_cetakan_details',
                'daily_registers',
                'daily_register_findings',
                'register_harian',
                'register_klarifikasis',
                'kontrol_mingguans',
                'daily_targets',
            ],
            'risk' => [
                'register_triggers',
                'risk_trigger_kka_induks',
                'risk_trigger_kka_teller_kas',
                'risk_trigger_kka_dpk_layanans',
                'risk_trigger_kka_kredits',
                'risk_trigger_kka_biaya_bebans',
                'risk_trigger_kka_atms',
                'risk_trigger_kka_observasis',
                'risk_trigger_kka_pengaduans',
                'risk_trigger_kka_fisiks',
                'risk_trigger_klarifikasi_buktis',
                'risk_trigger_simpulan_tls',
            ],
            'pelaporan' => [
                'sop08_rekap_kegiatans',
                'sop08_rekap_isu_tls',
                'sop08_log_harian_ras',
                'sop08_isu_signifikans',
                'sop08_feedbacks',
            ]
        ];

        if ($module === 'all') {
            $all = [];
            foreach ($modules as $modTables) {
                $all = array_merge($all, $modTables);
            }
            return array_unique($all);
        }

        return $modules[$module] ?? [];
    }

    /**
     * Membersihkan file fisik di storage staging/bukti tanpa menghapus foldernya.
     */
    protected function cleanStorageFiles(string $module): void
    {
        $this->info("Membersihkan file fisik pada storage staging...");

        $directories = [];

        if (in_array($module, ['all', 'offsite'])) {
            $directories[] = storage_path('app/private/offsite_staging');
            $directories[] = storage_path('app/offsite-registers');
        }

        if (in_array($module, ['all', 'onsite'])) {
            $directories[] = storage_path('app/private/onsite_staging');
        }

        if (in_array($module, ['all', 'daily-csv', 'daily'])) {
            $directories[] = storage_path('app/private/daily_register_staging');
            $directories[] = storage_path('app/tmp/daily_register');
        }

        if (in_array($module, ['all', 'daily'])) {
            $directories[] = storage_path('app/public/bukti_kka/kartu_atm');
            $directories[] = storage_path('app/public/bukti_kka/barang_cetakan');
            $directories[] = storage_path('app/public/surat_izin');
            $directories[] = storage_path('app/surat_sakit');
        }

        $deletedCount = 0;
        foreach ($directories as $dir) {
            if (File::isDirectory($dir)) {
                $files = File::files($dir);
                foreach ($files as $file) {
                    if ($file->getFilename() !== '.gitignore') {
                        File::delete($file->getPathname());
                        $deletedCount++;
                    }
                }
            }
        }

        $this->info("Total {$deletedCount} file fisik upload berhasil dibersihkan dari storage.");
    }
}

