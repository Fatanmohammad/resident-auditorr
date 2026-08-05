<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CabangUnitSeeder extends Seeder
{
    /**
     * Hubungkan unit ke cabang (cabang_id) berdasarkan base_ra_unit.
     *
     * SEMUA unit dipetakan ke cabang utama (KCU/KC) yang sesuai dalam
     * struktur hirarki cabangs. Dengan begitu, RA di setiap cabang utama
     * beserta seluruh anak cabangnya dapat menginput unit di wilayahnya.
     */
    public function run(): void
    {
        $cabangKode = DB::table('cabangs')->get()->keyBy('kode_cabang');

        $now = now();

        // Pemetaan base_ra_unit -> kode_cabang (cabang utama)
        $mapBaseRaToKode = [
            'KANTOR PUSAT'       => 'BS-000',   // Kantor Pusat
            'CABANG UTAMA'       => 'BS-001',   // KCU Palu
            'CABANG LUWUK'       => 'BS-002',   // KCU Luwuk
            'CABANG POSO'        => 'BS-003',   // Cabang Poso
            'CABANG DONGGALA'    => 'BS-004',   // Cabang Donggala
            'CABANG SIGI'        => 'BS-005',   // Cabang Sigi
            'CABANG BUOL'        => 'BS-006',   // Cabang Buol
            'CABANG SALAKAN'     => 'BS-007',   // Cabang Salakan
            'CABANG BANGGAI LAUT'=> 'BS-008',   // Cabang Banggai Laut
            'CABANG PARIGI'      => 'BS-009',   // Cabang Parigi
            'CABANG PALU BARAT'  => 'BS-010',   // Cabang Palu Barat
            'CABANG TOLITOLI'    => 'BS-011',   // Cabang Tolitoli
            'CABANG BUNGKU'      => 'BS-012',   // Cabang Bungku
            'CABANG AMPANA'      => 'BS-013',   // Cabang Ampana
            'CABANG KOLONODALE'  => 'BS-014',   // Cabang Kolonodale
            'CABANG JAKARTA'     => 'BS-015',   // Cabang Jakarta
        ];

        // 1) Petakan semua unit berdasarkan base_ra_unit ke cabang utama
        foreach ($mapBaseRaToKode as $baseRa => $kode) {
            $cabang = $cabangKode->get($kode);
            if (!$cabang) continue;

            DB::table('units')
                ->where('base_ra_unit', $baseRa)
                ->update(['cabang_id' => $cabang->id, 'updated_at' => $now]);
        }

        // 2) Anak cabang (lewat hirarki cabang) — petakan unit yang region-nya
        //    sesuai dengan nama anak cabang, dengan parent menginduk ke cabang utama.
$anakMap = [
            // Cabang utama kode => [base_ra anak, region anak]
            'BS-002' => [ // KCU Luwuk
                ['CABANG LUWUK', 'TOILI'],
                ['CABANG LUWUK', 'BUNTA'],
            ],
            'BS-003' => [ // Cabang Poso
                ['CABANG POSO', 'TENTENA'],
                ['CABANG POSO', 'PENDOLO'],
            ],
            'BS-004' => [ // Cabang Donggala
                ['CABANG DONGGALA', 'DONGGALA'],
                ['CABANG DONGGALA', 'BANAWA'],
                ['CABANG DONGGALA', 'SIRENJA'],
            ],
        ];

        foreach ($anakMap as $indukKode => $anakList) {
            $induk = $cabangKode->get($indukKode);
            if (!$induk) continue;

            $anakCabangs = DB::table('cabangs')->where('parent_id', $induk->id)->get();

            foreach ($anakList as [$baseRa, $region]) {
                $matched = DB::table('units')
                    ->where('base_ra_unit', $baseRa)
                    ->where('region', $region)
                    ->get();

                foreach ($matched as $unit) {
                    $target = $anakCabangs->first(fn($c) => stripos($c->nama_cabang, $region) !== false);
                    if ($target) {
                        DB::table('units')
                            ->where('id', $unit->id)
                            ->update(['cabang_id' => $target->id, 'updated_at' => $now]);
                    }
                }
            }
        }
    }
}
