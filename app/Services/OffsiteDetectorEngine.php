<?php

namespace App\Services;

use App\Models\WpOffsiteStaging;
use App\Models\WpOffsite;
use App\Services\OffsiteDetectionService;
use App\Models\KkaTellerKas;
use App\Models\KkaKredit;
use App\Models\KkaBiayaBeban;
use App\Models\KkaBiayaInternal;
use App\Models\KkaPengaduan;
use App\Models\KkaTransaksiUmum;
use App\Models\KkaTransferKu;

class OffsiteDetectorEngine
{
    protected OffsiteDetectionService $detectionService;

    private array $kkaModelMap = [
        'kka_teller_kas'     => KkaTellerKas::class,
        'kka_kredit'         => KkaKredit::class,
        'kka_biaya_beban'    => KkaBiayaBeban::class,
        'kka_biaya_internal' => KkaBiayaInternal::class,
        'kka_pengaduan'      => KkaPengaduan::class,
        'kka_transaksi_umum' => KkaTransaksiUmum::class,
        'kka_transfer_ku'    => KkaTransferKu::class,
    ];

    public function __construct(OffsiteDetectionService $detectionService)
    {
        $this->detectionService = $detectionService;
    }

    public function scan(WpOffsite $wp, string $domainType): int
    {
        // FIX: cari kolom yang BENAR-BENAR ada
        $stagings = WpOffsiteStaging::where('wp_offsite_id', $wp->id)
            ->where('domain_type', $domainType)
            ->whereNull('processed_at')
            ->get();

        $flaggedCount = 0;

        foreach ($stagings as $staging) {
            $data = json_decode($staging->raw_data, true);

            $hasilDeteksi = $this->detectionService->detectBaris($data, strtoupper($domainType), $wp);

            $staging->update([
                'flags'              => $hasilDeteksi['flags'],
                'jumlah_flag_risiko' => $hasilDeteksi['jumlah_flag_risiko'],
                'area_review'        => $hasilDeteksi['area_review'],
                'risk_level'         => $hasilDeteksi['risk_level'],
                'case_id'            => $hasilDeteksi['case_id'],
                'kka_sheet_tujuan'   => $hasilDeteksi['kka_sheet_tujuan'],
                'perlu_kka'          => $hasilDeteksi['perlu_kka'],
                'perlu_klarifikasi'  => $hasilDeteksi['perlu_klarifikasi'],
                'perlu_eskalasi'     => $hasilDeteksi['perlu_eskalasi'],
                'processed_at'       => now(),
            ]);

            if ($hasilDeteksi['perlu_kka'] && isset($this->kkaModelMap[$hasilDeteksi['kka_sheet_tujuan']])) {
                $this->buatBarisKka($staging, $hasilDeteksi, $wp, $data);
                $flaggedCount++;
            }
        }

        return $flaggedCount;
    }

    private function buatBarisKka($staging, array $hasil, WpOffsite $wp, array $data): void
    {
        $model = $this->kkaModelMap[$hasil['kka_sheet_tujuan']];

        // Ambil deskripsi & nominal dari raw_data, fallback ke gabungan teks kalau field spesifik gak ada
        $deskripsi = $data['deskripsi_narasi'] ?? $data['keterangan_transaksi'] ?? $data['isi_pengaduan'] ?? implode(' ', $data);
        $nominal = $data['nominal'] ?? 0;

        $model::create([
            'wp_offsite_id'        => $wp->id,
            'staging_id'           => $staging->id,
            'area_review'          => $hasil['area_review'],
            'tanggal_data'         => $staging->tgl_transaksi,
            'kode_unit'            => $wp->kode_unit,
            'nama_unit'            => $wp->nama_unit,
            'ra_id'                => $wp->ra_pelaksana_id,
            'object_id'            => $data['no_referensi'] ?? $data['no_rekening'] ?? null,
            'case_id'              => $hasil['case_id'],
            'deskripsi_narasi'     => $deskripsi,
            'nominal'              => $nominal,
            'user_maker'           => $data['user_maker'] ?? null,
            'risk_awal'            => $hasil['risk_level'],
            'exception_awal'       => $hasil['jumlah_flag_risiko'] > 0,
            'jenis_exception_awal' => implode(', ', array_keys(array_filter($hasil['flags']))),
            'status_review'        => 'Belum Review',
        ]);
    }
}