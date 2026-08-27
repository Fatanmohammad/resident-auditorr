<?php

namespace App\Services;

use App\Models\WpOffsite;
use Illuminate\Support\Collection;

class OffsiteReviewService
{
    const AREAS = [
        'Teller/Kas', 'Biaya/Internal', 'Kredit',
        'Transaksi Umum', 'Transfer/KU', 'Pengaduan',
    ];

    const DUMPS = [
        'dump_transaksi_cbs' => 'CBS',
        'dump_dpk_apuppt' => 'DPK/APUPPT',
        'dump_kredit' => 'Kredit',
        'dump_biaya_beban' => 'Biaya/Beban',
        'dump_pengaduan' => 'Pengaduan',
    ];

    /**
     * Blok 1 — 6 Kartu Stat Ringkasan
     */
    public function statCards(WpOffsite $wp): array
    {
        $stagingAll = $wp->staging()->get();
        $kkaAll     = $this->getAllKka($wp);

        $eligible   = $stagingAll->where('masuk_kka_final', true)->count();
        $kkaFinal   = $kkaAll->where('kka_status', 'Final')->count();
        $exception  = $kkaAll->where('status_kka', 'Exception')->count();
        $klarifikasi = $kkaAll->where('status_kka', 'Klarifikasi')->count();
        $eskalasi   = $kkaAll->where('is_escalated', true)->count();

        $progress = $eligible > 0 ? round($kkaFinal / $eligible * 100, 1) : 0;

        return compact('eligible', 'kkaFinal', 'exception', 'klarifikasi', 'eskalasi', 'progress');
    }

    /**
     * Blok 2 — Ringkasan Eligible Per Area Review
     */
    public function ringkasanPerArea(WpOffsite $wp): Collection
    {
        $stagingAll = $wp->staging()->get();
        $kkaAll     = $this->getAllKka($wp);

        return collect(self::AREAS)->map(function ($area) use ($stagingAll, $kkaAll) {
            $eligible    = $stagingAll->where('area_review', $area)->where('masuk_kka_final', true)->count();
            $kkaArea     = $kkaAll->where('area_review', $area);
            $kkaFinal    = $kkaArea->where('kka_status', 'Final')->count();
            $exception   = $kkaArea->where('status_kka', 'Exception')->count();
            $klarifikasi = $kkaArea->where('status_kka', 'Klarifikasi')->count();

            $high     = $kkaArea->where('risk_level', 'High')->count();
            $moderate = $kkaArea->where('risk_level', 'Moderate')->count();
            $low      = $kkaArea->where('risk_level', 'Low')->count();

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
     */
    public function rekonsiliasi(WpOffsite $wp): Collection
    {
        $stagingAll = $wp->staging()->get();
        $kkaAll     = $this->getAllKka($wp);

        return collect(self::DUMPS)->map(function ($label, $dumpTable) use ($stagingAll, $kkaAll) {
            $staging     = $stagingAll->where('source_table', $dumpTable);
            $kka         = $kkaAll->where('source_sheet', $dumpTable);
            $total       = $staging->count();
            $eligible    = $staging->where('status_data_quality', 'VALID')->count();
            $salahUnit   = $staging->where('status_data_quality', 'Salah Unit')->count();
            $luarPeriode = $staging->where('status_data_quality', 'Luar Periode')->count();
            $kkaFinal    = $kka->where('kka_status', 'Final')->count();
            $exception   = $kka->where('status_kka', 'Exception')->count();

              return [
                'dump'         => $dumpTable,
                'label'        => $label,
                'total'        => $total,
                'normalized'   => $total,
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
        $kkaAll = $this->getAllKka($wp);

        return $kkaAll->groupBy('area_review')->map(function ($rows, $area) {
            $total    = $rows->count();
            $draft    = $rows->where('kka_status', 'Draft')->count();
            $final    = $rows->where('kka_status', 'Final')->count();
            $approved = $rows->where('kka_status', 'Approved')->count();
            return compact('area', 'total', 'draft', 'final', 'approved');
        })->values();
    }

    /**
     * Blok 4b — Kontrol Kesiapan Dashboard
     */
    public function kontrolKesiapan(WpOffsite $wp): array
    {
        $stagingAll = $wp->staging()->get();
        $kkaAll     = $this->getAllKka($wp);
        $eligible   = $stagingAll->where('masuk_kka_final', true)->count();
        $kkaFinal   = $kkaAll->where('kka_status', 'Final')->count();
        $kotor      = $stagingAll->where('status_data_quality', '!=', 'VALID')->count();
        $kkaNoRisk  = $kkaAll->whereNull('risk_level')->count();

        return [
            [
                'label'  => 'Validasi Unit',
                'ok'     => $wp->validasi_unit === 'VALID',
                'detail' => $wp->validasi_unit === 'VALID' ? 'Unit tervalidasi' : 'Unit belum divalidasi',
            ],
            [
                'label'  => 'Rekonsiliasi Staging',
                'ok'     => $kotor === 0 && $stagingAll->count() > 0,
                'detail' => $kotor === 0
                    ? 'Semua record staging bersih'
                    : "{$kotor} record bermasalah (Salah Unit/Luar Periode)",
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
                'ok'     => in_array($wp->status_wp, ['Final']),
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

    /**
     * Helper: gabungkan semua KKA dari 7 tabel jadi 1 Collection.
     * Tambah field area_review berdasarkan tabel asal jika belum ada.
     */
    private function getAllKka(WpOffsite $wp): Collection
    {
        $all = collect();

        $mapping = [
            'kka_teller_kas'     => $wp->kkaTellerKas,
            'kka_kredit'         => $wp->kkaKredit,
            'kka_biaya_beban'    => $wp->kkaBiayaBeban,
            'kka_biaya_internal' => $wp->kkaBiayaInternal,
            'kka_pengaduan'      => $wp->kkaPengaduan,
            'kka_transaksi_umum' => $wp->kkaTransaksiUmum,
            'kka_transfer_ku'    => $wp->kkaTransferKu,
        ];

        foreach ($mapping as $sheet => $relation) {
            foreach ($relation as $record) {
                $all->push($record);
            }
        }

        return $all;
    }
}
