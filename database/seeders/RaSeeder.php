<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RaSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('branch_ra_mappings')->truncate();
        DB::table('ras')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $ras = [
            ['ra_id' => 'LWK-1',  'ra_name' => 'Jilly Keshia Lambeto', 'base_branch' => 'CABANG LUWUK',          'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'LWK-2',  'ra_name' => 'Selvi R. Madina',      'base_branch' => 'CABANG LUWUK',          'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'SGI-1',  'ra_name' => 'Yuyun',                'base_branch' => 'CABANG SIGI',           'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'BUOL-1', 'ra_name' => 'Andika',               'base_branch' => 'CABANG BUOL',           'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'SLKN-1', 'ra_name' => 'Lucky Haryanto L',     'base_branch' => 'CABANG SALAKAN',        'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'BLT-1',  'ra_name' => 'Moh. Rizal Abbas',     'base_branch' => 'CABANG BANGGAI LAUT',   'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'PRG-1',  'ra_name' => 'Nur Santi Armatia',    'base_branch' => 'CABANG PARIGI',         'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'PLB-1',  'ra_name' => 'Mardudin',             'base_branch' => 'CABANG PALU BARAT',     'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'KP-1',   'ra_name' => 'Evawani A. Thayeb',    'base_branch' => 'KANTOR PUSAT',          'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'KP-2',   'ra_name' => 'Backup Kantor Pusat',  'base_branch' => 'KANTOR PUSAT',          'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'PSO-1',  'ra_name' => 'Yan Hamsah',           'base_branch' => 'CABANG POSO',           'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'TWL-1',  'ra_name' => 'Risnandar Thayeb',     'base_branch' => 'CABANG PEMBANTU TAWELI','status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'KCU-1',  'ra_name' => 'Januar',               'base_branch' => 'CABANG UTAMA',          'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'KCU-2',  'ra_name' => 'Backup Cabang Utama',  'base_branch' => 'CABANG UTAMA',          'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'TLS-1',  'ra_name' => 'Suparman',             'base_branch' => 'CABANG TOLITOLI',       'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'JKT-1',  'ra_name' => 'Sri Fika Reski',       'base_branch' => 'CABANG JAKARTA',        'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'KDL-1',  'ra_name' => 'Dedi Paris Djafar',    'base_branch' => 'CABANG KOLONODALE',     'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'BGK-1',  'ra_name' => 'Mastini',              'base_branch' => 'CABANG BUNGKU',         'status' => 'Aktif', 'monthly_capacity_days' => 20],
            ['ra_id' => 'AMP-1',  'ra_name' => 'Treesya',              'base_branch' => 'CABANG AMPANA',         'status' => 'Aktif', 'monthly_capacity_days' => 20],
        ];

        $now = now();
        foreach ($ras as $ra) {
            DB::table('ras')->insert(array_merge($ra, ['created_at' => $now, 'updated_at' => $now]));
        }

        $getRA = fn($raId) => DB::table('ras')->where('ra_id', $raId)->value('id');

        // branch_name harus cocok dengan base_ra_unit di tabel units (case-sensitive)
        $mappings = [
            ['branch_name' => 'CABANG LUWUK',          'primary' => 'LWK-1',  'backup' => 'LWK-2'],
            ['branch_name' => 'CABANG SIGI',           'primary' => 'SGI-1',  'backup' => null],
            ['branch_name' => 'CABANG BUOL',           'primary' => 'BUOL-1', 'backup' => null],
            ['branch_name' => 'CABANG SALAKAN',        'primary' => 'SLKN-1', 'backup' => null],
            ['branch_name' => 'CABANG BANGGAI LAUT',   'primary' => 'BLT-1',  'backup' => null],
            ['branch_name' => 'CABANG PARIGI',         'primary' => 'PRG-1',  'backup' => null],
            ['branch_name' => 'CABANG PALU BARAT',     'primary' => 'PLB-1',  'backup' => null],
            ['branch_name' => 'KANTOR PUSAT',          'primary' => 'KP-1',   'backup' => 'KP-2'],
            ['branch_name' => 'CABANG POSO',           'primary' => 'PSO-1',  'backup' => null],
            ['branch_name' => 'CABANG PEMBANTU TAWELI','primary' => 'TWL-1',  'backup' => null],
            ['branch_name' => 'CABANG UTAMA',          'primary' => 'KCU-1',  'backup' => 'KCU-2'],
            ['branch_name' => 'CABANG TOLITOLI',       'primary' => 'TLS-1',  'backup' => null],
            ['branch_name' => 'CABANG JAKARTA',        'primary' => 'JKT-1',  'backup' => null],
            ['branch_name' => 'CABANG KOLONODALE',     'primary' => 'KDL-1',  'backup' => null],
            ['branch_name' => 'CABANG BUNGKU',         'primary' => 'BGK-1',  'backup' => null],
            ['branch_name' => 'CABANG AMPANA',         'primary' => 'AMP-1',  'backup' => null],
        ];

        foreach ($mappings as $m) {
            DB::table('branch_ra_mappings')->insert([
                'branch_name'    => $m['branch_name'],
                'primary_ra_id'  => $getRA($m['primary']),
                'backup_ra_id'   => $m['backup'] ? $getRA($m['backup']) : null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }
}
