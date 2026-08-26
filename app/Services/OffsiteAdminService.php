<?php

namespace App\Services;

use App\Models\Cabang;
use App\Models\Unit;
use App\Models\WpOffsite;
use App\Models\RegisterHarian;
use App\Models\StagingOffsite;
use Illuminate\Support\Facades\DB;

class OffsiteAdminService
{
    public function getCabangStats(int $tahun, int $bulan)
    {
        return Cabang::query()
            ->where('cabangs.tipe', '!=', 'anak_cabang')
            ->leftJoin('cabangs as anak_cabang', 'anak_cabang.parent_id', '=', 'cabangs.id')
            ->leftJoin('units', function ($join) {
                $join->on('units.cabang_id', '=', 'cabangs.id')
                     ->orOn('units.cabang_id', '=', 'anak_cabang.id');
            })
            ->leftJoin('register_harian', function ($join) use ($tahun, $bulan) {
                $join->on('units.unit_code', '=', 'register_harian.kode_unit')
                     ->whereYear('register_harian.tanggal_data', $tahun)
                     ->whereMonth('register_harian.tanggal_data', $bulan);
            })
            ->selectRaw('
                cabangs.id, cabangs.kode_cabang, cabangs.nama_cabang,
                COUNT(DISTINCT units.id) as total_unit,
                COUNT(DISTINCT CASE WHEN register_harian.status_review IN ("Belum Review", "Dalam Review") 
                      THEN units.id END) as unit_perlu_review,
                COUNT(DISTINCT CASE WHEN register_harian.status_review = "Selesai" 
                      THEN units.id END) as unit_selesai_review
            ')
            ->where('units.is_active', 1)
            ->groupBy('cabangs.id', 'cabangs.kode_cabang', 'cabangs.nama_cabang')
            ->orderBy('cabangs.kode_cabang')
            ->get();
    }

    public function getUnitsByBranch(int $tahun, int $bulan, ?int $cabangId = null)
    {
        $query = Unit::where('is_active', true);

        if ($cabangId) {
            $cabangIds = Cabang::where('id', $cabangId)
                ->orWhere('parent_id', $cabangId)
                ->pluck('id');

            $query->whereIn('cabang_id', $cabangIds);
        }

        $units = $query->orderBy('unit_type')->orderBy('unit_name')->get();

        $units = $units->map(function ($unit) use ($tahun, $bulan) {
            // SINKRONISASI OTOMATIS: Ambil dari staging_offsite jika register_harian kosong
            $this->syncStagingToRegisterHarian($unit, $tahun, $bulan);

            $rows = RegisterHarian::where('kode_unit', $unit->unit_code)
                ->whereYear('tanggal_data', $tahun)
                ->whereMonth('tanggal_data', $bulan)
                ->get();

            $totalKlarifikasi = $rows->sum('perlu_klarifikasi');
            $totalEskalasi = $rows->sum('perlu_eskalasi');
            $adaBelumReview = $rows->whereIn('status_review', ['Belum Review', 'Dalam Review'])->count() > 0;

            $statusReview = $rows->isEmpty()
                ? 'Tidak Ada Data'
                : ($adaBelumReview ? 'Perlu Review' : 'Selesai Review');

            $risikoTertinggi = $this->hitungRisikoTertinggi($rows->pluck('risiko_tertinggi')->filter()->all());

            $totalAreaRisiko = $rows->whereIn('risiko_tertinggi', ['Moderate to High', 'High'])
                ->pluck('area_review')
                ->unique()
                ->count();

            return [
                'unit' => $unit,
                'status_review' => $statusReview,
                'total_klarifikasi' => $totalKlarifikasi,
                'total_eskalasi' => $totalEskalasi,
                'total_area_risiko' => $totalAreaRisiko,
                'risiko_tertinggi' => $risikoTertinggi,
                'terakhir_update' => $rows->max('updated_at'),
            ];
        });

        return $units;
    }

    /**
     * Otomatis membuat WpOffsite dan menyalin data StagingOffsite ke RegisterHarian jika ada upload baru
     */
    private function syncStagingToRegisterHarian(Unit $unit, int $tahun, int $bulan): void
    {
        // 1. Ambil atau Buat Header WP Offsite
        $kodeWp = 'SOP02-' . $unit->unit_code . '-' . $tahun . str_pad($bulan, 2, '0', STR_PAD_LEFT);
        
        $wp = WpOffsite::firstOrCreate(
            [
                'unit_id' => $unit->id,
                'periode_mulai' => "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-01"
            ],
            [
                'kode_wp'         => $kodeWp,
                'kode_unit'       => $unit->unit_code,
                'nama_unit'       => $unit->unit_name,
                'jenis_unit'      => $unit->unit_type ?? 'KC',
                'kantor_induk'    => $unit->parent_office ?? 'KANTOR PUSAT',
                'periode_selesai' => date('Y-m-t', strtotime("$tahun-$bulan-01")),
                'status_wp'       => 'Draft'
            ]
        );

        // 2. Hubungkan data staging_offsite yang belum punya wp_offsite_id
        StagingOffsite::where('kode_unit', $unit->unit_code)
            ->whereYear('tanggal_data', $tahun)
            ->whereMonth('tanggal_data', $bulan)
            ->whereNull('wp_offsite_id')
            ->update(['wp_offsite_id' => $wp->id]);

        // 3. Salin/Sync data dari StagingOffsite ke RegisterHarian
        $stagings = StagingOffsite::where('wp_offsite_id', $wp->id)->get();

        foreach ($stagings as $stg) {
            RegisterHarian::updateOrCreate(
                [
                    'wp_offsite_id' => $wp->id,
                    'kode_unit'     => $unit->unit_code,
                    'tanggal_data'  => $stg->tanggal_data,
                    'area_review'   => $stg->area_review,
                ],
                [
                    'nama_unit'         => $unit->unit_name,
                    'target_review_h1'  => $stg->target_review_h1 ?? $stg->tanggal_data,
                    'ra_id'             => $stg->ra_id ?? auth()->id() ?? 1,
                    'nama_ra'           => $stg->nama_ra ?? 'Resident Auditor',
                    'populasi_eligible' => $stg->populasi_eligible ?? 0,
                    'sampel_low'        => $stg->sampel_low ?? 0,
                    'kka_final'         => $stg->masuk_kka_final ?? 0,
                    'exception'         => $stg->exception_awal ?? 0,
                    'risiko_tertinggi'  => $stg->risk_level ?? 'Low',
                    'hasil_awal'        => $stg->catatan_ra ?? '-',
                    'status_review'     => $stg->status_review ?? 'Belum Review',
                    'updated_at'        => now(),
                ]
            );
        }
    }

    private function hitungRisikoTertinggi(array $risikoLevels): string
    {
        if (empty($risikoLevels)) {
            return 'Tidak Ada Data';
        }
        $urutan = [
            'Low' => 1,
            'Low to Moderate' => 2,
            'Moderate' => 3,
            'Moderate to High' => 4,
            'High' => 5,
        ];
        $tertinggi = 'Low';
        $nilaiTertinggi = 0;
        foreach ($risikoLevels as $level) {
            $nilai = $urutan[$level] ?? 0;
            if ($nilai > $nilaiTertinggi) {
                $nilaiTertinggi = $nilai;
                $tertinggi = $level;
            }
        }
        return $tertinggi;
    }
}