<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Cabang;
use App\Models\User;

/**
 * Seeder pelengkap agar struktur cabang lengkap (BS-000 s.d. BS-015)
 * dan seluruh akun (19 RA + user pendukung) tersedia di setiap environment.
 *
 * Sebelumnya data ini hanya ada di database lokal (diisi manual), sehingga
 * tidak ikut tersebar ke teman/pengembang lain saat git pull. Dengan seeder
 * ini, siapa pun yang menjalankan `php artisan db:seed` akan mendapat
 * struktur cabang + akun yang sama.
 */
class StrukturCabangUserSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================================
        // 1. Struktur Cabang lengkap (BS-000 s.d. BS-015 + anak cabang)
        // =====================================================================
        $cabangs = [
            // Pusat
            ['BS-000',  'PT Bank Sulteng Kantor Pusat', 'pusat',       null],
            // KCU
            ['BS-001',  'KCU Palu',                     'kcu',         'BS-000'],
            ['BS-002',  'KCU Luwuk',                    'kcu',         'BS-000'],
            // Anak cabang KCU Luwuk
            ['BS-002-A','Anak Cabang Toili',            'anak_cabang', 'BS-002'],
            ['BS-002-B','Anak Cabang Bunta',            'anak_cabang', 'BS-002'],
            // Cabang operasional
            ['BS-003',  'Cabang Poso',                  'cabang_a',    'BS-000'],
            ['BS-003-A','Anak Cabang Tentena',          'anak_cabang', 'BS-003'],
            ['BS-003-B','Anak Cabang Pendolo',          'anak_cabang', 'BS-003'],
            ['BS-004',  'Cabang Donggala',              'cabang_b',    'BS-000'],
            ['BS-004-A','Anak Cabang Banawa',           'anak_cabang', 'BS-004'],
            ['BS-004-B','Anak Cabang Sirenja',          'anak_cabang', 'BS-004'],
            ['BS-005',  'Cabang Sigi',                  'cabang_b',    'BS-001'],
            ['BS-006',  'Cabang Buol',                  'cabang_b',    'BS-000'],
            ['BS-007',  'Cabang Salakan',               'cabang_b',    'BS-000'],
            ['BS-008',  'Cabang Banggai Laut',          'cabang_b',    'BS-000'],
            ['BS-009',  'Cabang Parigi',                'cabang_b',    'BS-000'],
            ['BS-010',  'Cabang Palu Barat',            'cabang_b',    'BS-001'],
            ['BS-011',  'Cabang Tolitoli',              'cabang_b',    'BS-000'],
            ['BS-012',  'Cabang Bungku',                'cabang_b',    'BS-002'],
            ['BS-013',  'Cabang Ampana',                'cabang_b',    'BS-000'],
            ['BS-014',  'Cabang Kolonodale',            'cabang_b',    'BS-002'],
            ['BS-015',  'Cabang Jakarta',               'cabang_b',    'BS-000'],
        ];

        $cabangIdByKode = [];
        foreach ($cabangs as [$kode, $nama, $tipe, $parentKode]) {
            $parentId = $parentKode ? ($cabangIdByKode[$parentKode] ?? null) : null;
            $cabang  = Cabang::firstOrCreate(
                ['kode_cabang' => $kode],
                [
                    'nama_cabang' => $nama,
                    'tipe'        => $tipe,
                    'parent_id'   => $parentId,
                ]
            );
            $cabangIdByKode[$kode] = $cabang->id;
        }

        // =====================================================================
        // 2. Seluruh akun pengguna (19 RA + user pendukung)
        // =====================================================================
        // Format: [nip, name, email, role, kode_cabang]
        $users = [
            // === 19 RA ===
            ['2001', 'Siti Korwas',             'korwas@banksulteng.co.id',         'kabag_ra',  'BS-000'],
            ['2002', 'Januar',                  'ra1.palu@banksulteng.co.id',       'ra',        'BS-001'],
            ['2003', 'Backup Cabang Utama',     'ra2.palu@banksulteng.co.id',       'ra',        'BS-001'],
            ['2004', 'Pimcab Palu',             'pimcab.palu@banksulteng.co.id',    'auditee',   'BS-001'],
            ['2005', 'Jilly Keshia Lambeto',    'ra1.luwuk@banksulteng.co.id',      'ra',        'BS-002'],
            ['2006', 'Selvi R. Madina',         'ra2.luwuk@banksulteng.co.id',      'ra',        'BS-002'],
            ['2007', 'Pimcab Luwuk',            'pimcab.luwuk@banksulteng.co.id',   'auditee',   'BS-002'],
            ['2008', 'Yan Hamsah',              'ra1.poso@banksulteng.co.id',       'ra',        'BS-003'],
            ['2009', 'Pimcab Poso',             'pimcab.poso@banksulteng.co.id',    'auditee',   'BS-003'],
            ['2011', 'Pimcab Donggala',         'pimcab.donggala@banksulteng.co.id','auditee',   'BS-004'],
            ['2012', 'Yuyun',                   'ra1.sigi@banksulteng.co.id',       'ra',        'BS-005'],
            ['2013', 'Andika',                  'ra1.buol@banksulteng.co.id',       'ra',        'BS-006'],
            ['2014', 'Lucky Haryanto L',        'ra1.salakan@banksulteng.co.id',    'ra',        'BS-007'],
            ['2015', 'Moh. Rizal Abbas',        'ra1.banggailaut@banksulteng.co.id','ra',        'BS-008'],
            ['2016', 'Nur Santi Armatia',       'ra1.parigi@banksulteng.co.id',     'ra',        'BS-009'],
            ['2017', 'Mardudin',                'ra1.palubarat@banksulteng.co.id',  'ra',        'BS-010'],
            ['2018', 'Suparman',                'ra1.tolitoli@banksulteng.co.id',   'ra',        'BS-011'],
            ['2019', 'Mastini',                 'ra1.bungku@banksulteng.co.id',     'ra',        'BS-012'],
            ['2020', 'Treesya',                 'ra1.ampana@banksulteng.co.id',     'ra',        'BS-013'],
            ['2021', 'Dedi Paris Djafar',       'ra1.kolonodale@banksulteng.co.id', 'ra',        'BS-014'],
            ['2022', 'Sri Fika Reski',          'ra1.jakarta@banksulteng.co.id',    'ra',        'BS-015'],
            ['2023', 'Evawani A. Thayeb',       'ra1.pusat@banksulteng.co.id',      'ra',        'BS-000'],
            ['2024', 'Backup Kantor Pusat',     'ra2.pusat@banksulteng.co.id',      'ra',        'BS-000'],
            ['2026', 'Risnandar Thayeb',        'ra1.taweli@banksulteng.co.id',     'ra',        'BS-010'],
        ];

        foreach ($users as [$nip, $name, $email, $role, $kodeCabang]) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'nip'       => $nip,
                    'name'      => $name,
                    'password'  => Hash::make('password123'),
                    'role'      => $role,
                    'cabang_id' => $cabangIdByKode[$kodeCabang] ?? null,
                ]
            );
        }
    }
}
