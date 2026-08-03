<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cabang;
use App\Models\User;
use App\Models\ParameterAudit;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data Struktur Percabangan PT Bank Sulteng
        $pusat = Cabang::create([
            'nama_cabang' => 'PT Bank Sulteng Kantor Pusat',
            'kode_cabang' => 'BS-000',
            'tipe' => 'pusat',
        ]);

        $kcuPalu = Cabang::create([
            'nama_cabang' => 'Cabang Utama Palu (KCU)',
            'kode_cabang' => 'BS-001',
            'tipe' => 'kcu',
            'parent_id' => $pusat->id,
        ]);

        $anakCabangSigi = Cabang::create([
            'nama_cabang' => 'Anak Cabang Sigi',
            'kode_cabang' => 'BS-001-A',
            'tipe' => 'anak_cabang',
            'parent_id' => $kcuPalu->id,
        ]);

        $cabangLuwuk = Cabang::create([
            'nama_cabang' => 'Cabang Luwuk',
            'kode_cabang' => 'BS-002',
            'tipe' => 'cabang_a',
            'parent_id' => $pusat->id,
        ]);

        // 2. Data Akun Penguji Sesuai Peran & Hak Akses
        User::create([
            'nip' => '1001',
            'name' => 'Budi (Kadiv SKAI)',
            'email' => 'kadiv@banksulteng.co.id',
            'password' => Hash::make('password123'),
            'role' => 'kadiv_skai',
            'cabang_id' => $pusat->id,
        ]);

        User::create([
            'nip' => '1002',
            'name' => 'Siti (Kabag RA Korwas)',
            'email' => 'kabag@banksulteng.co.id',
            'password' => Hash::make('password123'),
            'role' => 'kabag_ra',
            'cabang_id' => $kcuPalu->id,
        ]);

        User::create([
            'nip' => '1003',
            'name' => 'Andi (Resident Auditor Palu)',
            'email' => 'ra@banksulteng.co.id',
            'password' => Hash::make('password123'),
            'role' => 'ra',
            'cabang_id' => $kcuPalu->id,
        ]);

        User::create([
            'nip' => '1004',
            'name' => 'Pimpinan Cabang Palu (Auditee)',
            'email' => 'auditee@banksulteng.co.id',
            'password' => Hash::make('password123'),
            'role' => 'auditee',
            'cabang_id' => $kcuPalu->id,
        ]);

        User::create([
            'nip' => '1005',
            'name' => 'PIMSIE Bank Sulteng',
            'email' => 'pimsie@banksulteng.co.id',
            'password' => Hash::make('password123'),
            'role' => 'pimsie',
            'cabang_id' => $pusat->id,
        ]);

        // 3. Parameter Penilaian Audit KAT/RA Awal
        ParameterAudit::create([
            'nama_parameter' => 'Profil Risiko Kepatuhan & Operasional',
            'bobot' => 40.00,
            'deskripsi' => 'Penilaian parameter risiko KAT RA per bidang audit',
        ]);

        ParameterAudit::create([
            'nama_parameter' => 'Penyelesaian Tindak Lanjut Temuan Audit',
            'bobot' => 60.00,
            'deskripsi' => 'Persentase penyelesaian Tindak Lanjut oleh Auditee',
        ]);

        // 4. Master Setup Modul Audit Plan (SOP 01)
        $this->call([
            MasterSetupSeeder::class,
            RaSeeder::class,
        ]);
    }
}