<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StrukturCabangUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Kantor Pusat & Cabang Utama sebagai induk dasar
        $pusat = Cabang::firstOrCreate(['kode_cabang' => 'BS-000'], [
            'nama_cabang' => 'KANTOR PUSAT',
            'tipe' => 'pusat',
        ]);

        $kcuPalu = Cabang::firstOrCreate(['kode_cabang' => 'BS-001'], [
            'nama_cabang' => 'CABANG UTAMA',
            'tipe' => 'kcu',
            'parent_id' => $pusat->id,
        ]);

        // Daftar cabang utama lainnya sesuai dengan base_branch di RaSeeder
        $daftarCabang = [
            ['kode' => 'BS-002', 'nama' => 'CABANG LUWUK', 'tipe' => 'cabang_a'],
            ['kode' => 'BS-003', 'nama' => 'CABANG POSO', 'tipe' => 'cabang_a'],
            ['kode' => 'BS-004', 'nama' => 'CABANG DONGGALA', 'tipe' => 'cabang_b'],
            ['kode' => 'BS-005', 'nama' => 'CABANG SIGI', 'tipe' => 'cabang_b'],
            ['kode' => 'BS-006', 'nama' => 'CABANG BUOL', 'tipe' => 'cabang_b'],
            ['kode' => 'BS-007', 'nama' => 'CABANG SALAKAN', 'tipe' => 'cabang_b'],
            ['kode' => 'BS-008', 'nama' => 'CABANG BANGGAI LAUT', 'tipe' => 'cabang_b'],
            ['kode' => 'BS-009', 'nama' => 'CABANG PARIGI', 'tipe' => 'cabang_a'],
            ['kode' => 'BS-010', 'nama' => 'CABANG PALU BARAT', 'tipe' => 'cabang_b'],
            ['kode' => 'BS-011', 'nama' => 'CABANG TOLITOLI', 'tipe' => 'cabang_a'],
            ['kode' => 'BS-012', 'nama' => 'CABANG BUNGKU', 'tipe' => 'cabang_a'],
            ['kode' => 'BS-013', 'nama' => 'CABANG AMPANA', 'tipe' => 'cabang_a'],
            ['kode' => 'BS-014', 'nama' => 'CABANG KOLONODALE', 'tipe' => 'cabang_b'],
            ['kode' => 'BS-015', 'nama' => 'CABANG JAKARTA', 'tipe' => 'cabang_b'],
            ['kode' => 'BS-101', 'nama' => 'CABANG PEMBANTU TAWELI', 'tipe' => 'cabang_pembantu'],
        ];

        foreach ($daftarCabang as $cab) {
            Cabang::firstOrCreate(['kode_cabang' => $cab['kode']], [
                'nama_cabang' => $cab['nama'],
                'tipe' => $cab['tipe'],
                'parent_id' => $pusat->id,
            ]);
        }

        // 2. Daftar Akun Utama / Petinggi
        $petinggi = [
            ['email' => 'kadiv@banksulteng.co.id', 'nip' => '1001', 'name' => 'Budi (Kadiv SKAI)', 'role' => 'kadiv_skai', 'cabang_id' => $pusat->id],
            ['email' => 'kabag@banksulteng.co.id', 'nip' => '1002', 'name' => 'Siti (Kabag RA Korwas)', 'role' => 'kabag_ra', 'cabang_id' => $kcuPalu->id],
            ['email' => 'pimsie@banksulteng.co.id', 'nip' => '1005', 'name' => 'PIMSIE Bank Sulteng', 'role' => 'pimsie', 'cabang_id' => $pusat->id],
            ['email' => 'admin@banksulteng.co.id', 'nip' => '1006', 'name' => 'Admin Sistem', 'role' => 'admin', 'cabang_id' => $pusat->id],
        ];

        foreach ($petinggi as $p) {
            User::firstOrCreate(['email' => $p['email']], [
                'nip' => $p['nip'],
                'name' => $p['name'],
                'password' => Hash::make('password123'),
                'role' => $p['role'],
                'cabang_id' => $p['cabang_id'],
            ]);
        }

        // 3. Akun RA Berdasarkan Data RaSeeder (19 Akun RA)
        $ras = [
            ['ra_id' => 'LWK-1', 'ra_name' => 'Jilly Keshia Lambeto', 'base_branch' => 'CABANG LUWUK'],
            ['ra_id' => 'LWK-2', 'ra_name' => 'Selvi R. Madina', 'base_branch' => 'CABANG LUWUK'],
            ['ra_id' => 'SGI-1', 'ra_name' => 'Yuyun', 'base_branch' => 'CABANG SIGI'],
            ['ra_id' => 'BUOL-1', 'ra_name' => 'Andika', 'base_branch' => 'CABANG BUOL'],
            ['ra_id' => 'SLKN-1', 'ra_name' => 'Lucky Haryanto L', 'base_branch' => 'CABANG SALAKAN'],
            ['ra_id' => 'BLT-1', 'ra_name' => 'Moh. Rizal Abbas', 'base_branch' => 'CABANG BANGGAI LAUT'],
            ['ra_id' => 'PRG-1', 'ra_name' => 'Nur Santi Armatia', 'base_branch' => 'CABANG PARIGI'],
            ['ra_id' => 'PLB-1', 'ra_name' => 'Mardudin', 'base_branch' => 'CABANG PALU BARAT'],
            ['ra_id' => 'KP-1', 'ra_name' => 'Evawani A. Thayeb', 'base_branch' => 'KANTOR PUSAT'],
            ['ra_id' => 'KP-2', 'ra_name' => 'Backup Kantor Pusat', 'base_branch' => 'KANTOR PUSAT'],
            ['ra_id' => 'PSO-1', 'ra_name' => 'Yan Hamsah', 'base_branch' => 'CABANG POSO'],
            ['ra_id' => 'TWL-1', 'ra_name' => 'Risnandar Thayeb', 'base_branch' => 'CABANG PEMBANTU TAWELI'],
            ['ra_id' => 'KCU-1', 'ra_name' => 'Januar', 'base_branch' => 'CABANG UTAMA'],
            ['ra_id' => 'KCU-2', 'ra_name' => 'Backup Cabang Utama', 'base_branch' => 'CABANG UTAMA'],
            ['ra_id' => 'TLS-1', 'ra_name' => 'Suparman', 'base_branch' => 'CABANG TOLITOLI'],
            ['ra_id' => 'JKT-1', 'ra_name' => 'Sri Fika Reski', 'base_branch' => 'CABANG JAKARTA'],
            ['ra_id' => 'KDL-1', 'ra_name' => 'Dedi Paris Djafar', 'base_branch' => 'CABANG KOLONODALE'],
            ['ra_id' => 'BGK-1', 'ra_name' => 'Mastini', 'base_branch' => 'CABANG BUNGKU'],
            ['ra_id' => 'AMP-1', 'ra_name' => 'Treesya', 'base_branch' => 'CABANG AMPANA'],
        ];

        foreach ($ras as $ra) {
            $cabangObj = Cabang::where('nama_cabang', $ra['base_branch'])->first();
            $cleanId = strtolower(str_replace('-', '', $ra['ra_id']));
            $email = "ra.{$cleanId}@banksulteng.co.id";

            User::firstOrCreate(['email' => $email], [
                'nip' => $ra['ra_id'],
                'name' => $ra['ra_name'] . ' (' . $ra['base_branch'] . ')',
                'password' => Hash::make('password123'),
                'role' => 'ra',
                'cabang_id' => $cabangObj ? $cabangObj->id : $pusat->id,
            ]);
        }
    }
}