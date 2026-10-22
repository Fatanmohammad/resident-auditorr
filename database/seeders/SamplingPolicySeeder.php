<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Offsite\SamplingSizePolicy;
use App\Models\Offsite\SamplingAreaPolicy;

class SamplingPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengacu pada SOP Excel: WP_SOP_04_2_DAILY_REVIEW_v1.xlsx
     * Sheet: Parameter_Sampling_SOP02 (Baris A5:C11 dan A14:I20)
     */
    public function run(): void
    {
        // 1. UKURAN SAMPEL BERTINGKAT SESUAI SOP 02 (Baris A5:C11)
        $sizePolicies = [
            [
                'min_populasi' => 1,
                'max_populasi' => 10,
                'min_sampel'   => null,
                'sample_all'   => true,
                'keterangan'   => 'Populasi 1–10 diperiksa 100%',
            ],
            [
                'min_populasi' => 11,
                'max_populasi' => 25,
                'min_sampel'   => 10,
                'sample_all'   => false,
                'keterangan'   => 'Populasi 11–25',
            ],
            [
                'min_populasi' => 26,
                'max_populasi' => 50,
                'min_sampel'   => 15,
                'sample_all'   => false,
                'keterangan'   => 'Populasi 26–50',
            ],
            [
                'min_populasi' => 51,
                'max_populasi' => 100,
                'min_sampel'   => 20,
                'sample_all'   => false,
                'keterangan'   => 'Populasi 51–100',
            ],
            [
                'min_populasi' => 101,
                'max_populasi' => 250,
                'min_sampel'   => 25,
                'sample_all'   => false,
                'keterangan'   => 'Populasi 101–250',
            ],
            [
                'min_populasi' => 251,
                'max_populasi' => null,
                'min_sampel'   => 30,
                'sample_all'   => false,
                'keterangan'   => 'Populasi di atas 250',
            ],
        ];

        foreach ($sizePolicies as $size) {
            SamplingSizePolicy::updateOrCreate(
                ['min_populasi' => $size['min_populasi']],
                $size
            );
        }

        // 2. KEBIJAKAN 6 AREA SAMPLING HARIAN KKA (Baris A14:I20)
        $areaPolicies = [
            [
                'source_sheet'    => 'KKA_Teller_Kas',
                'metode_sampling' => 'Random',
                'stratify_by'     => 'kategori',
                'aktif'           => true,
            ],
            [
                'source_sheet'    => 'KKA_DPK_Layanan',
                'metode_sampling' => 'Random',
                'stratify_by'     => 'kategori',
                'aktif'           => true,
            ],
            [
                'source_sheet'    => 'KKA_Kredit',
                'metode_sampling' => 'Random',
                'stratify_by'     => 'kategori',
                'aktif'           => true,
            ],
            [
                'source_sheet'    => 'KKA_Biaya_Beban',
                'metode_sampling' => 'Random',
                'stratify_by'     => 'kategori',
                'aktif'           => true,
            ],
            [
                'source_sheet'    => 'KKA_Kartu_ATM',
                'metode_sampling' => 'Random',
                'stratify_by'     => null,
                'aktif'           => true,
            ],
            [
                'source_sheet'    => 'KKA_Pengaduan',
                'metode_sampling' => 'Random',
                'stratify_by'     => null,
                'aktif'           => true,
            ],
        ];

        foreach ($areaPolicies as $area) {
            SamplingAreaPolicy::updateOrCreate(
                ['source_sheet' => $area['source_sheet']],
                $area
            );
        }
    }
}
