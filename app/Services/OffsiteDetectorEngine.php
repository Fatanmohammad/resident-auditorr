<?php

namespace App\Services;

use App\Models\StagingOffsite;
use App\Models\WpOffsite;
use App\Services\OffsiteDetectionService;
use App\Models\KkaTellerKas;
use App\Models\KkaKredit;
use App\Models\KkaBiayaBeban;
use App\Models\KkaBiayaInternal;
use App\Models\KkaPengaduan;
use App\Models\KkaTransaksiUmum;
use App\Models\KkaTransferKu;
use Illuminate\Support\Str;

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
        // Sesuaikan query dengan model StagingOffsite dan nama kolom tabel staging_offsite
        $stagings = StagingOffsite::where('wp_offsite_id', $wp->id)
            ->where('source_table', strtoupper($domainType))
            ->whereNull('processed_at')
            ->get();

        $flaggedCount = 0;

        foreach ($stagings as $staging) {
            // Ambil array JSON dari kolom deskripsi_narasi
            $data = $staging->deskripsi_narasi;

            if (is_string($data)) {
                $data = json_decode($data, true);
            }
            if (is_string($data)) {
                $data = json_decode($data, true);
            }

            $data = is_array($data) ? $data : [];

            $hasilDeteksi = $this->detectionService->detectBaris($data, strtoupper($domainType), $wp);

            // Generate Case ID unik jika dari detection service tidak tersedia
            $caseId = !empty($hasilDeteksi['case_id']) 
                ? $hasilDeteksi['case_id'] 
                : ('CS-' . strtoupper(substr($domainType, 0, 3)) . '-' . Str::random(6));

            $hasilDeteksi['case_id'] = $caseId;

            // Update status deteksi ke record StagingOffsite yang valid
            $staging->update([
                'flags'              => $hasilDeteksi['flags'],
                'jumlah_flag_risiko' => $hasilDeteksi['jumlah_flag_risiko'],
                'area_review'        => $hasilDeteksi['area_review'],
                'risk_level'         => $hasilDeteksi['risk_level'],
                'case_id'            => $caseId,
                'kka_sheet_tujuan'   => $hasilDeteksi['kka_sheet_tujuan'],
                'perlu_kka'          => $hasilDeteksi['perlu_kka'],
                'perlu_klarifikasi'  => $hasilDeteksi['perlu_klarifikasi'],
                'perlu_eskalasi'     => $hasilDeteksi['perlu_eskalasi'],
                'processed_at'       => now(),
            ]);

            // Jika berisiko, simpan ke tabel KKA yang tepat dengan ID staging yang pasti valid ($staging->id)
            if ($hasilDeteksi['perlu_kka'] && isset($this->kkaModelMap[$hasilDeteksi['kka_sheet_tujuan']])) {
                $this->buatBarisKka($staging, $hasilDeteksi, $wp, $data);
                $flaggedCount++;
            }
        }

        return $flaggedCount;
    }

    private function buatBarisKka(StagingOffsite $staging, array $hasil, WpOffsite $wp, array $data): void
    {
        $model = $this->kkaModelMap[$hasil['kka_sheet_tujuan']];

        $deskripsi = $staging->deskripsi_narasi_text 
            ?? $data['KET_TX'] 
            ?? $data['keterangan_transaksi'] 
            ?? $data['isi_pengaduan'] 
            ?? $data['URAIAN'] 
            ?? (is_array($data) ? implode(' ', $data) : $data);

        $nominal = $staging->nominal ?? $data['nominal'] ?? $data['JUMLAH_TX'] ?? 0;

        $model::create([
            'wp_offsite_id'        => $wp->id,
            'staging_id'           => $staging->id, // Menggunakan ID asli dari model StagingOffsite
            'area_review'          => $hasil['area_review'],
            'tanggal_data'         => $staging->tanggal_data,
            'kode_unit'            => $staging->kode_unit ?? $wp->kode_unit,
            'nama_unit'            => $staging->nama_unit ?? $wp->nama_unit,
            'ra_id'                => $staging->ra_id ?? $wp->ra_pelaksana_id,
            'object_id'            => $data['NO_ARSIP'] ?? $data['no_referensi'] ?? $data['NO_REK'] ?? $data['no_rekening'] ?? null,
            'case_id'              => $hasil['case_id'],
            'deskripsi_narasi'     => $deskripsi,
            'nominal'              => $nominal,
            'user_maker'           => $staging->user_maker ?? $data['user_maker'] ?? $data['USER_MAKER'] ?? null,
            'risk_awal'            => $hasil['risk_level'],
            'exception_awal'       => $hasil['jumlah_flag_risiko'] > 0,
            'jenis_exception_awal' => implode(', ', array_keys(array_filter($hasil['flags'] ?? []))),
            'status_review'        => 'Belum Review',
        ]);
    }
}