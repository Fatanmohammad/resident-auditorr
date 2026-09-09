<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CabangSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Induk Cabang
        $indukData = [
            ['kode' => 'BS-000', 'nama' => 'Kantor Pusat', 'tipe' => 'pusat'],
            ['kode' => 'BS-001', 'nama' => 'Cabang Utama Palu', 'tipe' => 'kcu'],
            ['kode' => 'BS-002', 'nama' => 'Cabang Luwuk', 'tipe' => 'cabang_a'],
            ['kode' => 'BS-003', 'nama' => 'Cabang Poso', 'tipe' => 'cabang_a'],
            ['kode' => 'BS-004', 'nama' => 'Cabang Donggala', 'tipe' => 'cabang_b'],
            ['kode' => 'BS-005', 'nama' => 'Cabang Sigi', 'tipe' => 'cabang_b'],
            ['kode' => 'BS-006', 'nama' => 'Cabang Buol', 'tipe' => 'cabang_b'],
            ['kode' => 'BS-007', 'nama' => 'Cabang Salakan', 'tipe' => 'cabang_b'],
            ['kode' => 'BS-008', 'nama' => 'Cabang Banggai Laut', 'tipe' => 'cabang_b'],
            ['kode' => 'BS-009', 'nama' => 'Cabang Parigi', 'tipe' => 'cabang_a'],
            ['kode' => 'BS-010', 'nama' => 'Cabang Palu Barat', 'tipe' => 'cabang_b'],
            ['kode' => 'BS-011', 'nama' => 'Cabang Tolitoli', 'tipe' => 'cabang_a'],
            ['kode' => 'BS-012', 'nama' => 'Cabang Bungku', 'tipe' => 'cabang_a'],
            ['kode' => 'BS-013', 'nama' => 'Cabang Ampana', 'tipe' => 'cabang_a'],
            ['kode' => 'BS-014', 'nama' => 'Cabang Kolonodale', 'tipe' => 'cabang_b'],
            ['kode' => 'BS-015', 'nama' => 'Cabang Jakarta', 'tipe' => 'cabang_b'],
        ];

        foreach ($indukData as $d) {
            DB::table('cabangs')->updateOrInsert(
                ['kode_cabang' => $d['kode']],
                ['nama_cabang' => $d['nama'], 'tipe' => $d['tipe'], 'parent_id' => null, 'updated_at' => now()]
            );
        }

        // 2. Hubungkan Anak Cabang ke Induknya (Set parent_id)
        $luwukInduk = DB::table('cabangs')->where('kode_cabang', 'BS-002')->first();
        if ($luwukInduk) {
            DB::table('cabangs')->updateOrInsert(
                ['kode_cabang' => 'BS-002-1'],
                ['nama_cabang' => 'KCP Toili', 'tipe' => 'cabang_pembantu', 'parent_id' => $luwukInduk->id, 'updated_at' => now()]
            );
            DB::table('cabangs')->updateOrInsert(
                ['kode_cabang' => 'BS-002-2'],
                ['nama_cabang' => 'KCP Bunta', 'tipe' => 'cabang_pembantu', 'parent_id' => $luwukInduk->id, 'updated_at' => now()]
            );
        }

        $posoInduk = DB::table('cabangs')->where('kode_cabang', 'BS-003')->first();
        if ($posoInduk) {
            DB::table('cabangs')->updateOrInsert(
                ['kode_cabang' => 'BS-003-1'],
                ['nama_cabang' => 'KCP Tentena', 'tipe' => 'cabang_pembantu', 'parent_id' => $posoInduk->id, 'updated_at' => now()]
            );
            DB::table('cabangs')->updateOrInsert(
                ['kode_cabang' => 'BS-003-2'],
                ['nama_cabang' => 'KCP Pendolo', 'tipe' => 'cabang_pembantu', 'parent_id' => $posoInduk->id, 'updated_at' => now()]
            );
        }
    }
}