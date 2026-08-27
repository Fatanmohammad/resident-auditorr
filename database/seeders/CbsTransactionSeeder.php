<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CbsTransactionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cbs_transactions')->insert([
            [
                'no_referensi' => 'REF-' . rand(100, 999),
                'tanggal_data' => now()->toDateString(),
                'kode_unit' => '001',
                'data_unit' => 'Cabang Utama',
                'kode_transaksi' => 'TRF_INT',
                'deskripsi_narasi' => 'Indikasi Anomali Kas Utama',
                'nominal' => 85000000,
                'is_processed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no_referensi' => 'REF-' . rand(100, 999),
                'tanggal_data' => now()->toDateString(),
                'kode_unit' => '001',
                'data_unit' => 'Cabang Utama',
                'kode_transaksi' => 'TRF_UMUM',
                'deskripsi_narasi' => 'Transfer Rekening Pihak Ketiga Besar',
                'nominal' => 120000000,
                'is_processed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}