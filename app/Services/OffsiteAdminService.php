<?php

namespace App\Services;

use App\Models\Cabang;
use App\Models\Unit;
use App\Models\RegisterHarian;
use Illuminate\Support\Facades\DB;

class OffsiteAdminService
{
    public function getCabangStats(int $tahun, int $bulan)
    {
        // Kantor Pusat + cabang induk (parent_id=1), tanpa anak cabang
        return Cabang::where(function($q) {
                $q->where('cabangs.parent_id', 1)->orWhere('cabangs.id', 1);
            })
            ->leftJoin('cabangs as anak', 'anak.parent_id', '=', 'cabangs.id')
            ->leftJoin('units', function ($join) {
                $join->on('units.cabang_id', '=', 'cabangs.id')
                     ->orOn('units.cabang_id', '=', 'anak.id');
            })
            ->leftJoin('register_harian', function ($join) use ($tahun, $bulan) {
                $join->on('units.unit_code', '=', 'register_harian.kode_unit')
                     ->whereYear('register_harian.tanggal_data', $tahun)
                     ->whereMonth('register_harian.tanggal_data', $bulan);
            })
            ->where('units.is_active', 1)
            ->selectRaw('
                cabangs.id, cabangs.kode_cabang, cabangs.nama_cabang,
                COUNT(DISTINCT units.id) as total_unit,
                COUNT(DISTINCT CASE WHEN register_harian.status_review IN ("Belum Review", "Dalam Review")
                      THEN units.id END) as unit_perlu_review,
                COUNT(DISTINCT CASE WHEN register_harian.status_review = "Selesai"
                      THEN units.id END) as unit_selesai_review
            ')
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
                'id'                => $unit->id,
                'kode_unit'         => $unit->unit_code,
                'nama_unit'         => $unit->unit_name,
                'unit_type'         => $unit->unit_type,
                'unit'              => $unit,
                'status_review'     => $statusReview,
                'total_klarifikasi' => $totalKlarifikasi,
                'total_eskalasi'    => $totalEskalasi,
                'total_area_risiko' => $totalAreaRisiko,
                'risiko_tertinggi'  => $risikoTertinggi,
                'terakhir_update'   => $rows->max('updated_at'),
            ];
        });

        return $units;
    }

    private function syncStagingToRegisterHarian(Unit $unit, int $tahun, int $bulan): void
    {
        // Flow baru: data sudah masuk register_harian via OffsiteGenerationService::generate()
        // Tidak perlu sync otomatis di sini
    }

    private function hitungRisikoTertinggi(array $risikoLevels): string
    {
        if (empty($risikoLevels)) {
            return 'Tidak Ada Data';
        }
        $urutan = [
            'Low'      => 1,
            'Moderate' => 2,
            'High'     => 3,
        ];
        return collect($risikoLevels)
            ->sortByDesc(fn($r) => $urutan[$r] ?? 0)
            ->first() ?? 'Tidak Ada Data';
    }
}