<?php

namespace App\Services;

use App\Models\WpOffsite;
use App\Models\OffsiteStaging;
use App\Models\OffsiteKka;
use Illuminate\Support\Collection;

class OffsiteReviewService
{
    const AREAS = [
        'Teller/Kas', 'Biaya/Internal', 'Kredit',
        'Transaksi Umum', 'Transfer/KU', 'Pengaduan',
    ];

    const DUMPS = [
        'DUMP_01' => 'CBS',
        'DUMP_02' => 'DPK/APUPPT',
        'DUMP_03' => 'Kredit',
        'DUMP_04' => 'Biaya/Beban',
        'DUMP_05' => 'Pengaduan',
    ];

    /**
     * Blok 1 — 6 Kartu Stat Ringkasan
     * Populasi Eligible, KKA Final, Exception, Klarifikasi, Eskalasi, Progress Review
     */
    public function statCards(WpOffsite $wp): array
    {
        $eligible  = $wp->staging()->where('is_eligible', true)->count();
        $kkaFinal  = $wp->kka()->where('kka_status', 'Final')->count();
        $exception = $wp->kka()->where('status_kka', 'Exception')->count();
        $klarifikasi = $wp->kka()->where('status_kka', 'Klarifikasi')->count();
        $eskalasi  = $wp->kka()->where('is_escalated', true)->count();

        // Progress = KKA Final / Eligible × 100 (0 jika eligible = 0)
        $progress  = $eligible > 0 ? round($kkaFinal / $eligible * 100, 1) : 0;

        return compact('eligible', 'kkaFinal', 'exception', 'klarifikasi', 'eskalasi', 'progress');
    }

    /**
     * Blok 2 — Ringkasan Eligible Per Area Review
     * Kolom: Area, Eligible, High, Moderate, Low, Low to Moderate, Moderate to High,
     *        KKA Final, Exception, Klarifikasi
     *
     * "Low to Moderate" dan "Moderate to High" = item yang risk_level-nya
     * NAIK dari initial_risk_level (deteksi rule engine → is_escalated = true)
     * dan mendarat di level tersebut.
     */
    public function ringkasanPerArea(WpOffsite $wp): Collection
    {
        $kkaAll = $wp->kka()->get();

        return collect(self::AREAS)->map(function ($area) use ($wp, $kkaAll) {
            $eligible  = $wp->staging()->where('area_review', $area)->where('is_eligible', true)->count();
            $kkaArea   = $kkaAll->where('area_review', $area);
            $kkaFinal  = $kkaArea->where('kka_status', 'Final')->count();
            $exception = $kkaArea->where('status_kka', 'Exception')->count();
            $klarifikasi = $kkaArea->where('status_kka', 'Klarifikasi')->count();

            // Distribusi risk level dari KKA
            $high     = $kkaArea->where('risk_level', 'High')->count();
            $moderate = $kkaArea->where('risk_level', 'Moderate')->count();
            $low      = $kkaArea->where('risk_level', 'Low')->count();

            // Eskalasi: item yang is_escalated=true dan risk_level = level tersebut
            $lowToModerate  = $kkaArea->where('is_escalated', true)->where('risk_level', 'Low to Moderate')->count();
            $moderateToHigh = $kkaArea->where('is_escalated', true)->where('risk_level', 'Moderate to High')->count();

            return [
                'area'             => $area,
                'eligible'         => $eligible,
                'high'             => $high,
                'moderate'         => $moderate,
                'low'              => $low,
                'low_to_moderate'  => $lowToModerate,
                'moderate_to_high' => $moderateToHigh,
                'kka_final'        => $kkaFinal,
                'exception'        => $exception,
                'klarifikasi'      => $klarifikasi,
            ];
        });
    }

    /**
     * Blok 3 — Kualitas & Rekonsiliasi Sumber Data (per DUMP)
     *
     * Rumus per DUMP:
     *   Normalized  = COUNT staging WHERE dump_source = X AND is_normalized = true
     *   Eligible    = COUNT staging WHERE dump_source = X AND is_eligible = true
     *   Salah Unit  = COUNT staging WHERE dump_source = X AND is_salah_unit = true
     *   Luar Periode= COUNT staging WHERE dump_source = X AND is_luar_periode = true
     *   KKA Final   = COUNT kka WHERE dump_source = X AND kka_status = 'Final'
     *   Exception   = COUNT kka WHERE dump_source = X AND status_kka = 'Exception'
     */
    public function rekonsiliasi(WpOffsite $wp): Collection
    {
        $stagingAll = $wp->staging()->get();
        $kkaAll     = $wp->kka()->get();

        return collect(self::DUMPS)->map(function ($label, $dumpKey) use ($stagingAll, $kkaAll) {
            $staging     = $stagingAll->where('dump_source', $dumpKey);
            $kka         = $kkaAll->where('dump_source', $dumpKey);
            $total       = $staging->count();
            $normalized  = $staging->where('is_normalized', true)->count();
            $eligible    = $staging->where('is_eligible', true)->count();
            $salahUnit   = $staging->where('is_salah_unit', true)->count();
            $luarPeriode = $staging->where('is_luar_periode', true)->count();
            $kkaFinal    = $kka->where('kka_status', 'Final')->count();
            $exception   = $kka->where('status_kka', 'Exception')->count();

            return [
                'dump'         => $dumpKey,
                'label'        => $label,
                'total'        => $total,
                'normalized'   => $normalized,
                'eligible'     => $eligible,
                'salah_unit'   => $salahUnit,
                'luar_periode' => $luarPeriode,
                'kka_final'    => $kkaFinal,
                'exception'    => $exception,
            ];
        })->values();
    }

    /**
     * Blok 4a — Distribusi KKA Final per Sheet
     */
    public function distribusiKka(WpOffsite $wp): Collection
    {
        return $wp->kka()
            ->selectRaw('kka_sheet, kka_status, COUNT(*) as jumlah')
            ->groupBy('kka_sheet', 'kka_status')
            ->get()
            ->groupBy('kka_sheet')
            ->map(function ($rows, $sheet) {
                $total    = $rows->sum('jumlah');
                $draft    = $rows->where('kka_status', 'Draft')->sum('jumlah');
                $final    = $rows->where('kka_status', 'Final')->sum('jumlah');
                $approved = $rows->where('kka_status', 'Approved')->sum('jumlah');
                return compact('sheet', 'total', 'draft', 'final', 'approved');
            })->values();
    }

    /**
     * Blok 4b — Kontrol Kesiapan Dashboard (6 indikator otomatis)
     *
     * 1. Validasi Unit        : wp.validasi_unit = true
     * 2. Rekonsiliasi Staging : semua staging is_normalized = true (tidak ada record kotor)
     * 3. Populasi vs Register : eligible staging > 0
     * 4. KKA Final vs Register: COUNT kka_final >= COUNT eligible staging
     * 5. External Link        : wp.status_wp IN ['Final','Approved']
     * 6. Referensi Formula    : semua KKA punya risk_level terisi (tidak ada NULL)
     */
    public function kontrolKesiapan(WpOffsite $wp): array
    {
        $stagingAll  = $wp->staging()->get();
        $kkaAll      = $wp->kka()->get();
        $eligible    = $stagingAll->where('is_eligible', true)->count();
        $kkaFinal    = $kkaAll->where('kka_status', 'Final')->count();
        $kotor       = $stagingAll->where('is_normalized', false)->count();
        $kkaNoRisk   = $kkaAll->whereNull('risk_level')->count();

        return [
            [
                'label'  => 'Validasi Unit',
                'ok'     => $wp->validasi_unit,
                'detail' => $wp->validasi_unit ? 'Unit tervalidasi' : 'Unit belum divalidasi',
            ],
            [
                'label'  => 'Rekonsiliasi Staging',
                'ok'     => $kotor === 0 && $stagingAll->count() > 0,
                'detail' => $kotor === 0
                    ? 'Semua record staging bersih'
                    : "{$kotor} record belum dinormalisasi",
            ],
            [
                'label'  => 'Populasi vs Register',
                'ok'     => $eligible > 0,
                'detail' => $eligible > 0
                    ? "{$eligible} record eligible"
                    : 'Belum ada populasi eligible',
            ],
            [
                'label'  => 'KKA Final vs Register',
                'ok'     => $eligible > 0 && $kkaFinal >= $eligible,
                'detail' => $eligible > 0
                    ? "{$kkaFinal} / {$eligible} KKA final"
                    : 'Populasi eligible kosong',
            ],
            [
                'label'  => 'External Link',
                'ok'     => in_array($wp->status_wp, ['Final', 'Approved']),
                'detail' => "Status WP: {$wp->status_wp}",
            ],
            [
                'label'  => 'Referensi Formula',
                'ok'     => $kkaNoRisk === 0 && $kkaAll->count() > 0,
                'detail' => $kkaNoRisk === 0
                    ? 'Semua KKA memiliki risk level'
                    : "{$kkaNoRisk} KKA belum dinilai",
            ],
        ];
    }
}
