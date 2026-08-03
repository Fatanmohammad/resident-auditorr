<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('units')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $units = [
            // KC
            ['301',  'Kantor Cabang Ampana',                    'KC',           'KANTOR PUSAT',             'AMPANA',        true,  'CABANG AMPANA',      0,      'Sedang'],
            ['402',  'Kantor Cabang Banggai Laut',              'KC',           'KANTOR PUSAT',             'BANGKEP',       true,  'CABANG BANGGAI LAUT',0,      'Sedang'],
            ['005',  'Kantor Cabang Bungku',                    'KC',           'KANTOR PUSAT',             'BUNGKU',        true,  'CABANG BUNGKU',      0,      'Rendah'],
            ['401',  'Kantor Cabang Kolonodale',                'KC',           'KANTOR PUSAT',             'KOLONODALE',    true,  'CABANG KOLONODALE',  0,      'Rendah'],
            ['201',  'Kantor Cabang Buol',                      'KC',           'KANTOR PUSAT',             'BUOL',          true,  'CABANG BUOL',        0,      'Rendah'],
            ['009',  'Kantor Cabang Jakarta',                   'KC',           'KANTOR PUSAT',             'JAKARTA',       true,  'CABANG JAKARTA',     0,      'Rendah'],
            ['101',  'Kantor Cabang Donggala',                  'KC',           'KANTOR PUSAT',             'DONGGALA',      true,  'CABANG DONGGALA',    0,      'Sedang'],
            ['004',  'Kantor Cabang Luwuk',                     'KC',           'KANTOR PUSAT',             'LUWUK',         true,  'CABANG LUWUK',       0,      'Rendah'],
            ['008',  'Kantor Cabang Palu Barat',                'KC',           'KANTOR PUSAT',             'PALU',          true,  'CABANG PALU BARAT',  53,     'Rendah'],
            ['102',  'Kantor Cabang Parigi',                    'KC',           'KANTOR PUSAT',             'PARIGI',        true,  'CABANG PARIGI',      135,    'Rendah'],
            ['003',  'Kantor Cabang Poso',                      'KC',           'KANTOR PUSAT',             'POSO',          true,  'CABANG POSO',        130,    'Rendah'],
            ['006',  'Kantor Cabang Salakan',                   'KC',           'KANTOR PUSAT',             'BANGKEP',       true,  'CABANG SALAKAN',     48,     'Rendah'],
            ['007',  'Kantor Cabang Sigi',                      'KC',           'KANTOR PUSAT',             'SIGI',          true,  'CABANG SIGI',        90,     'Sedang'],
            ['002',  'Kantor Cabang Tolitoli',                  'KC',           'KANTOR PUSAT',             'TOLITOLI',      true,  'CABANG TOLITOLI',    26,     'Rendah'],
            // KCU
            ['001',  'Kantor Cabang Utama Palu',                'KCU',          'KANTOR PUSAT',             'PALU',          true,  'CABANG UTAMA',       0,      'Sedang'],
            // KP
            ['000',  'Kantor Pusat',                            'KP',           null,                       'PALU',          true,  'KANTOR PUSAT',       0,      'Rendah'],
            // KCP
            ['502',  'Kantor Cabang Pembantu Bohodopi',         'KCP',          'KANTOR CABANG BUNGKU',     'BAHODOPI',      true,  'CABANG BUNGKU',      30,     'Rendah'],
            ['211',  'Kantor Cabang Pembantu Paleleh',          'KCP',          'KANTOR CABANG BUOL',       'TAWAELI',       true,  'CABANG BUOL',        71,     'Sedang'],
            ['104',  'Kantor Cabang Pembantu Labean',           'KCP',          'KANTOR CABANG DONGGALA',   'SONI',          true,  'CABANG DONGGALA',    59,     'Rendah'],
            ['403',  'Kantor Cabang Pembantu Beteleme',         'KCP',          'KANTOR CABANG KOLONODALE', 'BETELEME',      true,  'CABANG KOLONODALE',  85,     'Rendah'],
            ['405',  'Kantor Cabang Pembantu Toili',            'KCP',          'KANTOR CABANG LUWUK',      'TOILI',         true,  'CABANG LUWUK',       0,      'Sedang'],
            ['801',  'Kantor Cabang Pembantu Tawaeli',          'KCP',          'KANTOR CABANG PALU BARAT', 'TAWAELI',       true,  'CABANG PALU BARAT',  78,     'Rendah'],
            ['105',  'Kantor Cabang Pembantu Tolai',            'KCP',          'KANTOR CABANG PARIGI',     'PALELEH',       true,  'CABANG PARIGI',      20,     'Rendah'],
            ['106',  'Kantor Cabang Pembantu Tinombo',          'KCP',          'KANTOR CABANG PARIGI',     'TOILI',         true,  'CABANG PARIGI',      148.16, 'Rendah'],
            ['303',  'Kantor Cabang Pembantu Tentena',          'KCP',          'KANTOR CABANG POSO',       'TENTENA',       true,  'CABANG POSO',        101,    'Rendah'],
            ['202',  'Kantor Cabang Pembantu Soni',             'KCP',          'KANTOR CABANG TOLIS',      'SONI',          true,  'CABANG TOLITOLI',    130,    'Sedang'],
            ['107',  'Kantor Cabang Pembantu Tinombala',        'KCP',          'KANTOR CABANG UTAMA',      'PALU',          true,  'CABANG UTAMA',       58,     'Rendah'],
            // KCPLK
            ['302',  'Kantor Cabang Pembantu Wakai',            'KCPLK',        'KANTOR CABANG AMPANA',     'WAKAI',         true,  'CABANG AMPANA',      52.5,   'Sedang'],
            ['501',  'Kantor Cabang Pembantu Bahomotefe',       'KCPLK',        'KANTOR CABANG AMPANA',     'BAHOMOTEFE',    true,  'CABANG BUNGKU',      142,    'Sedang'],
            ['411',  'Kantor Cabang Pembantu Mamosalato',       'KCPLK',        'KANTOR CABANG KOLONODALE', 'MAMOSALATO',    true,  'CABANG KOLONODALE',  260,    'Rendah'],
            ['412',  'Kantor Cabang Pembantu Tomata',           'KCPLK',        'KANTOR CABANG KOLONODALE', 'TOMATA',        true,  'CABANG KOLONODALE',  120,    'Rendah'],
            ['413',  'Kantor Cabang Pembantu Baturube',         'KCPLK',        'KANTOR CABANG LUWUK',      'BATURUBE',      true,  'CABANG KOLONODALE',  133,    'Sedang'],
            ['404',  'Kantor Cabang Pembantu Batui',            'KCPLK',        'KANTOR CABANG BUNGKU',     'BATUI',         true,  'CABANG LUWUK',       336,    'Rendah'],
            ['406',  'Kantor Cabang Pembantu Masama',           'KCPLK',        'KANTOR CABANG PARIGI',     'MASAMA',        true,  'CABANG LUWUK',       54,     'Rendah'],
            ['407',  'Kantor Cabang Pembantu Bunta',            'KCPLK',        'KANTOR CABANG LUWUK',      'BUNTA',         true,  'CABANG LUWUK',       180,    'Rendah'],
            ['103',  'Kantor Cabang Pembantu Lambunu',          'KCPLK',        'KANTOR CABANG PARIGI',     'PARIGI',        true,  'CABANG PARIGI',      0,      'Rendah'],
            ['108',  'Kantor Cabang Pembantu Kotaraya',         'KCPLK',        'KANTOR CABANG PARIGI',     'KOTARAYA',      true,  'CABANG PARIGI',      0,      'Rendah'],
            ['304',  'Kantor Cabang Pembantu Pendolo',          'KCPLK',        'KANTOR CABANG KOLONODALE', 'PENDOLO',       true,  'CABANG POSO',        0,      'Rendah'],
            ['305',  'Kantor Cabang Pembantu Napu',             'KCPLK',        'KANTOR CABANG POSO',       'NAPU',          true,  'CABANG POSO',        0,      'Rendah'],
            ['306',  'Kantor Cabang Pembantu Tambarana',        'KCPLK',        'KANTOR CABANG POSO',       'TAMBARANA',     true,  'CABANG POSO',        0,      'Rendah'],
            ['701',  'Kantor Cabang Pembantu Kulawi',           'KCPLK',        'KANTOR CABANG POSO',       'KULAWI',        true,  'CABANG POSO',        0,      'Sedang'],
            // Payment Point
            ['301-pp1', 'Payment Point Kantor Bupati Tojo Una-Una',         'Payment Point', 'CABANG AMPANA',          'TOJO UNAUNA',   true,  'CABANG AMPANA',   0, 'Rendah'],
            ['301-pp2', 'Payment Point Kantor Samsat Tojo Una-Una',         'Payment Point', 'CABANG AMPANA',          'TOJO UNAUNA',   true,  'CABANG AMPANA',   0, 'Rendah'],
            ['005-pp1', 'Payment Point BAPPENDA Kab. Morowali',             'Payment Point', 'CABANG BUNGKU',          'MOROWALI',      true,  'CABANG BUNGKU',   0, 'Rendah'],
            ['005-pp2', 'Payment Point Kantor BPKAD Kab. Morowali',        'Payment Point', 'CABANG BUNGKU',          'MOROWALI',      true,  'CABANG BUNGKU',   0, 'Rendah'],
            ['005-pp3', 'Payment Point Kantor SAMSAT Kab. Morowali',       'Payment Point', 'CABANG BUNGKU',          'MOROWALI',      true,  'CABANG BUNGKU',   0, 'Rendah'],
            ['005-pp4', 'Payment Point Mall Pelayanan Publik (MPP)',        'Payment Point', 'CABANG BUNGKU',          'MOROWALI',      true,  'CABANG BUNGKU',   0, 'Rendah'],
            ['201-mpp', 'Mall Pelayanan Publik (MPP)',                      'Payment Point', 'CABANG BUOL',            'BUOL',          true,  'CABANG BUOL',     0, 'Rendah'],
            ['201-pp1', 'Payment Point BPKAD Buol',                        'Payment Point', 'CABANG BUOL',            'BUOL',          true,  'CABANG BUOL',     0, 'Rendah'],
            ['201-pp2', 'Payment Point Kantor Samsat Buol',                'Payment Point', 'CABANG BUOL',            'BUOL',          true,  'CABANG BUOL',     0, 'Rendah'],
            ['201-pp3', 'Payment Point RSUD Mokoyurli Buol',               'Payment Point', 'CABANG BUOL',            'BUOL',          true,  'CABANG BUOL',     0, 'Rendah'],
            ['101-pp1', 'Payment Point Kantor BAPENDA Donggala',           'Payment Point', 'CABANG DONGGALA',        'DONGGALA',      true,  'CABANG DONGGALA', 0, 'Rendah'],
            ['101-pp2', 'Payment Point Kantor BPKAD',                      'Payment Point', 'CABANG DONGGALA',        'DONGGALA',      true,  'CABANG DONGGALA', 0, 'Rendah'],
            ['101-pp3', 'Payment Point Kantor Samsat Donggala',            'Payment Point', 'CABANG DONGGALA',        'DONGGALA',      true,  'CABANG DONGGALA', 0, 'Rendah'],
            ['401-pp1', 'Payment Point KKP BPKAD Morowali Utara',         'Payment Point', 'CABANG KOLONODALE',      'MOROWALI UTARA',true,  'CABANG KOLONODALE',0,'Rendah'],
            ['401-pp2', 'Payment Point SAMSAT Kolonodale',                 'Payment Point', 'CABANG KOLONODALE',      'MOROWALI UTARA',true,  'CABANG KOLONODALE',0,'Rendah'],
            ['004-pp1', 'Payment Point Kantor BPKAD-KKP Halimun',         'Payment Point', 'CABANG LUWUK',           'BANGGAI',       true,  'CABANG LUWUK',    0, 'Rendah'],
            ['004-pp2', 'Payment Point Kantor BAPENDA Kab. Banggai',       'Payment Point', 'CABANG LUWUK',           'BANGGAI',       true,  'CABANG LUWUK',    0, 'Rendah'],
            ['004-pp3', 'Payment Point SAMSAT Luwuk Banggai WIL V',        'Payment Point', 'CABANG LUWUK',           'BANGGAI',       true,  'CABANG LUWUK',    0, 'Rendah'],
            ['004-pp4', 'Payment Point PDAM Luwuk',                        'Payment Point', 'CABANG LUWUK',           'BANGGAI',       true,  'CABANG LUWUK',    0, 'Rendah'],
            ['008-pp1', 'Payment Point Kantor Walikota Loket Keuangan',    'Payment Point', 'CABANG PALU BARAT',      'PALU BARAT',    true,  'CABANG PALU BARAT',0,'Rendah'],
            ['008-pp2', 'Payment Point Kantor Walikota Loket Perizinan',   'Payment Point', 'CABANG PALU BARAT',      'PALU BARAT',    true,  'CABANG PALU BARAT',0,'Rendah'],
            ['102-pp1', 'Payment Point Kantor Bupati Parigi Moutong',      'Payment Point', 'CABANG PARIGI MOUTONG',  'PARIGI',        true,  'CABANG PARIGI',   0, 'Rendah'],
            ['102-pp2', 'Payment Point Kantor Samsat Parigi',              'Payment Point', 'CABANG PARIGI MOUTONG',  'PARIGI',        true,  'CABANG PARIGI',   0, 'Rendah'],
            ['003-mpp', 'Mall Pelayanan Publik (MPP)',                      'Payment Point', 'CABANG POSO',            'POSO',          true,  'CABANG POSO',     0, 'Rendah'],
            ['003-pp1', 'Payment Point Kantor Samsat Poso',                'Payment Point', 'CABANG POSO',            'POSO',          true,  'CABANG POSO',     0, 'Rendah'],
            ['003-pp2', 'Payment Point RSUD Poso',                         'Payment Point', 'CABANG POSO',            'POSO',          true,  'CABANG POSO',     0, 'Rendah'],
            ['006-pp1', 'Payment Poin Kantor Perizinan',                   'Payment Point', 'CABANG SALAKAN',         'SALAKAN',       true,  'CABANG SALAKAN',  0, 'Rendah'],
            ['006-pp2', 'Payment Point KKP BPKAD Kab. Banggai Kepulauan', 'Payment Point', 'CABANG SALAKAN',         'SALAKAN',       true,  'CABANG SALAKAN',  0, 'Rendah'],
            ['006-pp3', 'Payment Point SAMSAT',                            'Payment Point', 'CABANG SALAKAN',         'SALAKAN',       true,  'CABANG SALAKAN',  0, 'Rendah'],
            ['007-pp1', 'Payment Point Kantor Bapenda',                    'Payment Point', 'CABANG SIGI',            'SIGI',          true,  'CABANG SIGI',     0, 'Rendah'],
            ['007-pp2', 'Payment Point Kantor BPKAD Bora',                 'Payment Point', 'CABANG SIGI',            'SIGI',          true,  'CABANG SIGI',     0, 'Rendah'],
            ['007-pp3', 'Payment Point Kantor Samsat Sigi',                'Payment Point', 'CABANG SIGI',            'SIGI',          true,  'CABANG SIGI',     0, 'Rendah'],
            ['002-pp1', 'Payment Point Kantor Bersama Samsat Tolitoli',    'Payment Point', 'CABANG TOLITOLI',        'TOLI-TOLI',     true,  'CABANG TOLITOLI', 0, 'Rendah'],
            ['002-pp2', 'Payment Point PBB Canter',                        'Payment Point', 'CABANG TOLITOLI',        'TOLI-TOLI',     true,  'CABANG TOLITOLI', 0, 'Rendah'],
            ['001-pp1', 'Payment Point BPKAD Prov Sulawesi Tengah',        'Payment Point', 'CABANG UTAMA',           'PALU',          true,  'CABANG UTAMA',    0, 'Rendah'],
            ['001-pp2', 'Payment Point Dinas Perizinan',                   'Payment Point', 'CABANG UTAMA',           'PALU',          true,  'CABANG UTAMA',    0, 'Rendah'],
            ['001-pp3', 'Payment Point Kantor SAMSAT Palu',                'Payment Point', 'CABANG UTAMA',           'PALU',          true,  'CABANG UTAMA',    0, 'Rendah'],
            ['001-pp4', 'Payment Point Rumah Sakit Madani',                'Payment Point', 'CABANG UTAMA',           'PALU',          true,  'CABANG UTAMA',    0, 'Rendah'],
            ['001-pp5', 'Payment Point Rumah Sakit Undata',                'Payment Point', 'CABANG UTAMA',           'PALU',          true,  'CABANG UTAMA',    0, 'Rendah'],
            ['001-pp6', 'Payment Point SAMSAT Corner Palu',                'Payment Point', 'CABANG UTAMA',           'PALU',          true,  'CABANG UTAMA',    0, 'Rendah'],
        ];

        $now = now();
        $rows = [];
        foreach ($units as [$code, $name, $type, $parent, $region, $active, $base_ra, $distance, $vol]) {
            $isKcKcu = in_array($type, ['KC', 'KCU']);
            $rows[] = [
                'unit_code'                  => $code,
                'unit_name'                  => $name,
                'unit_type'                  => $type,
                'parent_office'              => $parent,
                'region'                     => $region,
                'is_active'                  => $active,
                'base_ra_unit'               => $base_ra,
                'distance_from_parent_km'    => $distance,
                'transaction_volume_category'=> $vol,
                'auto_description'           => $isKcKcu
                    ? 'KC induk tempat RA berkedudukan - Resident Daily Review + Daily Offsite H+1'
                    : 'Offsite harian H+1 - Frekuensi onsite: Tidak Terjadwal',
                'created_at'                 => $now,
                'updated_at'                 => $now,
            ];
        }

        DB::table('units')->insert($rows);
    }
}
