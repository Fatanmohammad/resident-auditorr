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
        // 0. Struktur cabang lengkap + seluruh akun (19 RA + user pendukung)
        //    Dipanggil pertama agar cabang & user tersedia sebelum seeder lain.
        $this->call(StrukturCabangUserSeeder::class);

        // 1. Data Struktur Percabangan PT Bank Sulteng
        $pusat = Cabang::firstOrCreate(['kode_cabang' => 'BS-000'], [
            'nama_cabang' => 'PT Bank Sulteng Kantor Pusat',
            'tipe' => 'pusat',
        ]);

        $kcuPalu = Cabang::firstOrCreate(['kode_cabang' => 'BS-001'], [
            'nama_cabang' => 'Cabang Utama Palu (KCU)',
            'tipe' => 'kcu',
            'parent_id' => $pusat->id,
        ]);

        // 2. Data Akun Penguji Sesuai Peran & Hak Akses
        User::firstOrCreate(['email' => 'kadiv@banksulteng.co.id'], [
            'nip' => '1001',
            'name' => 'Budi (Kadiv SKAI)',
            'password' => Hash::make('password123'),
            'role' => 'kadiv_skai',
            'cabang_id' => $pusat->id,
        ]);

        User::firstOrCreate(['email' => 'kabag@banksulteng.co.id'], [
            'nip' => '1002',
            'name' => 'Siti (Kabag RA Korwas)',
            'password' => Hash::make('password123'),
            'role' => 'kabag_ra',
            'cabang_id' => $kcuPalu->id,
        ]);

        User::firstOrCreate(['email' => 'ra@banksulteng.co.id'], [
            'nip' => '1003',
            'name' => 'Andi (Resident Auditor Palu)',
            'password' => Hash::make('password123'),
            'role' => 'ra',
            'cabang_id' => $kcuPalu->id,
        ]);

        User::firstOrCreate(['email' => 'auditee@banksulteng.co.id'], [
            'nip' => '1004',
            'name' => 'Pimpinan Cabang Palu (Auditee)',
            'password' => Hash::make('password123'),
            'role' => 'auditee',
            'cabang_id' => $kcuPalu->id,
        ]);

        User::firstOrCreate(['email' => 'pimsie@banksulteng.co.id'], [
            'nip' => '1005',
            'name' => 'PIMSIE Bank Sulteng',
            'password' => Hash::make('password123'),
            'role' => 'pimsie',
            'cabang_id' => $pusat->id,
        ]);

        User::firstOrCreate(['email' => 'admin@banksulteng.co.id'], [
            'nip' => '1006',
            'name' => 'Admin Sistem',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'cabang_id' => $pusat->id,
        ]);

        // 3. Parameter Penilaian Audit KAT/RA Awal
        ParameterAudit::firstOrCreate(
            ['nama_parameter' => 'Profil Risiko Kepatuhan & Operasional'],
            ['bobot' => 40.00, 'deskripsi' => 'Penilaian parameter risiko KAT RA per bidang audit']
        );

        ParameterAudit::firstOrCreate(
            ['nama_parameter' => 'Penyelesaian Tindak Lanjut Temuan Audit'],
            ['bobot' => 60.00, 'deskripsi' => 'Persentase penyelesaian Tindak Lanjut oleh Auditee']
        );

        // 4. Master Setup Modul Audit Plan (SOP 01)
        $this->call([
            MasterSetupSeeder::class,
            RaSeeder::class,
            UnitSeeder::class,
            CoverageSeeder::class,
            // Hubungkan unit ke cabang (cabang_id) agar akses RA per cabang berfungsi
            CabangUnitSeeder::class,
        ]);
    }
}
