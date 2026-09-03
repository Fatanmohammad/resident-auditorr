<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OffsiteUserTestingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pastikan Data Cabang Percobaan Ada di Tabel cabangs
        DB::table('cabangs')->updateOrInsert(
            ['id' => 1],
            ['kode_cabang' => '001', 'nama_cabang' => 'Kantor Pusat', 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('cabangs')->updateOrInsert(
            ['id' => 105],
            ['kode_cabang' => '105', 'nama_cabang' => 'Cabang Luwuk', 'created_at' => now(), 'updated_at' => now()]
        );

        // 2. Buat User Admin / Korwas
        User::updateOrCreate(
            ['email' => 'admin.audit@banksulteng.co.id'],
            [
                'nip'       => '999999',
                'name'      => 'Admin Audit Offsite',
                'password'  => Hash::make('password123'),
                'role'      => 'admin',
                'cabang_id' => 1,
            ]
        );

        // 3. Buat User RA (Resident Auditor)
        User::updateOrCreate(
            ['email' => 'ra.luwuk@banksulteng.co.id'],
            [
                'nip'       => '105001',
                'name'      => 'RA Cabang Luwuk',
                'password'  => Hash::make('password123'),
                'role'      => 'ra',
                'cabang_id' => 105,
            ]
        );
    }
}