<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoverageSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('coverage_details')->truncate();
        DB::table('coverage_setups')->truncate();
        DB::table('ra_assignments')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now  = now();
        $year = 2026;

        // Helper
        $unitId  = fn($code) => DB::table('units')->where('unit_code', $code)->value('id');
        $raId    = fn($raCode) => DB::table('ras')->where('ra_id', $raCode)->value('id');

        // =====================================================================
        // WP06A — Coverage Setup per unit (flag fungsi)
        // Format: [unit_code, kas, cs_dpk, kredit, atm, biaya, apu, ti_event, pengaduan, aset]
        // =====================================================================
        $setups = [
            // KC — semua aktif
            ['301','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['402','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['005','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['401','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['201','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['009','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['101','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['004','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['008','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['102','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['003','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['006','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['007','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['002','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            // KCU
            ['001','Ya','Ya','Tidak','Ya','Ya','Ya','Event','Ya','Ya'],
            // KP
            ['000','Tidak','Tidak','Tidak','Tidak','Ya','Tidak','Event','Tidak','Ya'],
            // KCP — semua aktif
            ['502','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['211','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['104','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['403','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['405','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['801','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['105','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['106','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['303','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['202','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            ['107','Ya','Ya','Ya','Ya','Ya','Ya','Event','Ya','Ya'],
            // KCPLK — kredit = Tidak
            ['302','Ya','Ya','Tidak','Ya','Ya','Ya','Event','Ya','Ya'],
            ['501','Ya','Ya','Tidak','Ya','Ya','Ya','Event','Ya','Ya'],
            ['411','Ya','Ya','Tidak','Ya','Ya','Ya','Event','Ya','Ya'],
            ['412','Ya','Ya','Tidak','Ya','Ya','Ya','Event','Ya','Ya'],
            ['413','Ya','Ya','Tidak','Ya','Ya','Ya','Event','Ya','Ya'],
            ['404','Ya','Ya','Tidak','Ya','Ya','Ya','Event','Ya','Ya'],
            ['406','Ya','Ya','Tidak','Ya','Ya','Ya','Event','Ya','Ya'],
            ['407','Ya','Ya','Tidak','Ya','Ya','Ya','Event','Ya','Ya'],
            ['103','Ya','Ya','Tidak','Ya','Ya','Ya','Event','Ya','Ya'],
            ['108','Ya','Ya','Tidak','Ya','Ya','Ya','Event','Ya','Ya'],
            ['304','Ya','Ya','Tidak','Ya','Ya','Ya','Event','Ya','Ya'],
            ['305','Ya','Ya','Tidak','Ya','Ya','Ya','Event','Ya','Ya'],
            ['306','Ya','Ya','Tidak','Ya','Ya','Ya','Event','Ya','Ya'],
            ['701','Ya','Ya','Tidak','Ya','Ya','Ya','Event','Ya','Ya'],
            // Payment Point — hanya kas aktif
            ['301-pp1','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['301-pp2','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['005-pp1','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['005-pp2','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['005-pp3','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['005-pp4','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['201-mpp','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['201-pp1','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['201-pp2','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['201-pp3','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['101-pp1','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['101-pp2','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['101-pp3','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['401-pp1','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['401-pp2','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['004-pp1','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['004-pp2','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['004-pp3','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['004-pp4','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['008-pp1','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['008-pp2','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['102-pp1','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['102-pp2','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['003-mpp','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['003-pp1','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['003-pp2','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['006-pp1','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['006-pp2','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['006-pp3','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['007-pp1','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['007-pp2','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['007-pp3','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['002-pp1','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['002-pp2','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['001-pp1','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['001-pp2','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['001-pp3','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['001-pp4','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['001-pp5','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
            ['001-pp6','Ya','Tidak','Tidak','Tidak','Tidak','Tidak','Event','Tidak','Ya'],
        ];

        foreach ($setups as [$code, $kas, $cs, $kredit, $atm, $biaya, $apu, $ti, $pengaduan, $aset]) {
            $uid = $unitId($code);
            if (!$uid) continue;
            DB::table('coverage_setups')->insert([
                'unit_id'       => $uid,
                'period'        => $year,
                'teller_kas'    => $kas,
                'cs_dpk'        => $cs,
                'kredit'        => $kredit,
                'atm'           => $atm,
                'biaya_jurnal'  => $biaya,
                'apu_fds'       => $apu,
                'ti_event'      => $ti,
                'pengaduan_aset'=> $pengaduan,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        // =====================================================================
        // WP05 — RA Assignment per unit
        // Format: [unit_code, primary_ra_id, backup_ra_id]
        // null = belum ada mapping (Donggala)
        // =====================================================================
        $assignments = [
            ['301',  'AMP-1',  null],
            ['402',  'BLT-1',  null],
            ['005',  'BGK-1',  null],
            ['401',  'KDL-1',  null],
            ['201',  'BUOL-1', null],
            ['009',  'JKT-1',  null],
            ['101',  null,     null], // Donggala — belum ada RA
            ['004',  'LWK-1',  'LWK-2'],
            ['008',  'PLB-1',  null],
            ['102',  'PRG-1',  null],
            ['003',  'PSO-1',  null],
            ['006',  'SLKN-1', null],
            ['007',  'SGI-1',  null],
            ['002',  'TLS-1',  null],
            ['001',  'KCU-1',  'KCU-2'],
            ['000',  'KP-1',   'KP-2'],
            ['502',  'BGK-1',  null],
            ['211',  'BUOL-1', null],
            ['104',  null,     null], // Labean — belum ada RA
            ['403',  'KDL-1',  null],
            ['405',  'LWK-1',  'LWK-2'],
            ['801',  'PLB-1',  null],
            ['105',  'PRG-1',  null],
            ['106',  'PRG-1',  null],
            ['303',  'PSO-1',  null],
            ['202',  'TLS-1',  null],
            ['107',  'KCU-1',  'KCU-2'],
            ['302',  'AMP-1',  null],
            ['501',  'BGK-1',  null],
            ['411',  'KDL-1',  null],
            ['412',  'KDL-1',  null],
            ['413',  'KDL-1',  null],
            ['404',  'LWK-1',  'LWK-2'],
            ['406',  'LWK-1',  'LWK-2'],
            ['407',  'LWK-1',  'LWK-2'],
            ['103',  'PRG-1',  null],
            ['108',  'PRG-1',  null],
            ['304',  'PSO-1',  null],
            ['305',  'PSO-1',  null],
            ['306',  'PSO-1',  null],
            ['701',  'PSO-1',  null],
            ['301-pp1','AMP-1',null],
            ['301-pp2','AMP-1',null],
            ['005-pp1','BGK-1',null],
            ['005-pp2','BGK-1',null],
            ['005-pp3','BGK-1',null],
            ['005-pp4','BGK-1',null],
            ['201-mpp','BUOL-1',null],
            ['201-pp1','BUOL-1',null],
            ['201-pp2','BUOL-1',null],
            ['201-pp3','BUOL-1',null],
            ['101-pp1',null,   null], // Donggala PP
            ['101-pp2',null,   null],
            ['101-pp3',null,   null],
            ['401-pp1','KDL-1',null],
            ['401-pp2','KDL-1',null],
            ['004-pp1','LWK-1','LWK-2'],
            ['004-pp2','LWK-1','LWK-2'],
            ['004-pp3','LWK-1','LWK-2'],
            ['004-pp4','LWK-1','LWK-2'],
            ['008-pp1','PLB-1',null],
            ['008-pp2','PLB-1',null],
            ['102-pp1','PRG-1',null],
            ['102-pp2','PRG-1',null],
            ['003-mpp','PSO-1',null],
            ['003-pp1','PSO-1',null],
            ['003-pp2','PSO-1',null],
            ['006-pp1','SLKN-1',null],
            ['006-pp2','SLKN-1',null],
            ['006-pp3','SLKN-1',null],
            ['007-pp1','SGI-1',null],
            ['007-pp2','SGI-1',null],
            ['007-pp3','SGI-1',null],
            ['002-pp1','TLS-1',null],
            ['002-pp2','TLS-1',null],
            ['001-pp1','KCU-1','KCU-2'],
            ['001-pp2','KCU-1','KCU-2'],
            ['001-pp3','KCU-1','KCU-2'],
            ['001-pp4','KCU-1','KCU-2'],
            ['001-pp5','KCU-1','KCU-2'],
            ['001-pp6','KCU-1','KCU-2'],
        ];

        foreach ($assignments as [$code, $primary, $backup]) {
            $uid = $unitId($code);
            if (!$uid) continue;
            DB::table('ra_assignments')->insert([
                'unit_id'          => $uid,
                'primary_ra_id'    => $primary ? $raId($primary) : null,
                'backup_ra_id'     => $backup  ? $raId($backup)  : null,
                'resident_base'    => DB::table('units')->where('id', $uid)->value('base_ra_unit'),
                'assignment_status'=> 'Aktif',
                'valid_from'       => 2026,
                'valid_to'         => 2026,
                'notes'            => $primary ? 'Assignment otomatis dari Base RA Unit' : 'Perlu Mapping RA',
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }
    }
}
