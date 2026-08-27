<?php

namespace App\Services;

use App\Models\Cabang;
use App\Models\Unit;
use App\Models\RegisterHarian;
use Illuminate\Support\Facades\DB;

class OffsiteAdminService
{
    /**
     * Ambil daftar semua cabang (induk saja) dengan statistik gabungan
     * (termasuk unit dari anak cabang di bawahnya), langsung dari register_harian, real-time
     */
    public function getCabangStats(int $tahun, int $bulan)
{
    return Cabang::query()
        ->where('cabangs.tipe', '!=', 'anak_cabang') // sembunyikan anak cabang, tampilkan pusat/kcu/cabang_a/cabang_b
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

    /**
     * Ambil daftar unit per cabang untuk admin view (langsung dari register_harian)
     * Termasuk unit dari anak cabang jika $cabangId adalah cabang induk.
     */
    public function getUnitsByBranch(int $tahun, int $bulan, ?int $cabangId = null)
    {
        $query = Unit::where('is_active', true);

        if ($cabangId) {
            // Ambil ID cabang ini + semua anak cabangnya (parent_id = cabang ini)
            $cabangIds = Cabang::where('id', $cabangId)
                ->orWhere('parent_id', $cabangId)
                ->pluck('id');

            $query->whereIn('cabang_id', $cabangIds);
        }

        $units = $query->orderBy('unit_type')->orderBy('unit_name')->get();

        $units = $units->map(function ($unit) use ($tahun, $bulan) {
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

            // Jumlah area review unik dengan risiko Moderate to High atau lebih tinggi
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