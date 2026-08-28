<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AggregateKkaHistoryCommand extends Command
{
    protected $signature = 'kka:aggregate-history {date?}';
    protected $description = 'Melakukan rekapitulasi otomatis data KKA harian dan bulanan dari Engine Utama';

    public function handle()
    {
        $targetDate = $this->argument('date') 
            ? Carbon::parse($this->argument('date')) 
            : Carbon::yesterday();
            
        $dateString = $targetDate->toDateString();
        $year = $targetDate->year;
        $month = $targetDate->month;

        // Daftar seluruh tabel KKA dari Engine Utama
        $sheets = [
            'kka_teller_kas',
            'kka_kredit',
            'kka_biaya_beban',
            'kka_biaya_internal',
            'kka_pengaduan',
            'kka_transaksi_umum',
            'kka_transfer_ku',
        ];

        foreach ($sheets as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            // 1. Hitung Agregat Harian dari hasil pemrosesan Engine Utama
            $dailyStat = DB::table($tableName)
                ->whereDate('tanggal_data', $dateString)
                ->selectRaw('
                    COUNT(*) as total_exception,
                    COALESCE(SUM(nominal), 0) as total_nominal,
                    SUM(CASE WHEN LOWER(risk_awal) = "high" THEN 1 ELSE 0 END) as high_risk,
                    SUM(CASE WHEN LOWER(risk_awal) = "moderate" THEN 1 ELSE 0 END) as mod_risk,
                    SUM(CASE WHEN LOWER(risk_awal) = "low" THEN 1 ELSE 0 END) as low_risk
                ')
                ->first();

            if ($dailyStat && $dailyStat->total_exception > 0) {
                DB::table('kka_daily_summaries')->updateOrInsert(
                    ['tanggal' => $dateString, 'sheet_name' => $tableName],
                    [
                        'total_exception'     => $dailyStat->total_exception,
                        'total_nominal'       => $dailyStat->total_nominal,
                        'high_risk_count'     => $dailyStat->high_risk,
                        'moderate_risk_count' => $dailyStat->mod_risk,
                        'low_risk_count'      => $dailyStat->low_risk,
                        'updated_at'          => now(),
                    ]
                );
            }

            // 2. Hitung Agregat Bulanan dari Summary Harian
            $monthlyStat = DB::table('kka_daily_summaries')
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->where('sheet_name', $tableName)
                ->selectRaw('
                    SUM(total_exception) as total_exception,
                    SUM(total_nominal) as total_nominal,
                    SUM(high_risk_count) as high_risk
                ')
                ->first();

            if ($monthlyStat && $monthlyStat->total_exception > 0) {
                DB::table('kka_monthly_summaries')->updateOrInsert(
                    ['tahun' => $year, 'bulan' => $month, 'sheet_name' => $tableName],
                    [
                        'total_exception' => $monthlyStat->total_exception,
                        'total_nominal'   => $monthlyStat->total_nominal,
                        'high_risk_count' => $monthlyStat->high_risk,
                        'updated_at'      => now(),
                    ]
                );
            }
        }

        $this->info("Rekapitulasi KKA Engine Utama tanggal {$dateString} berhasil diproses.");
        return Command::SUCCESS;
    }
}