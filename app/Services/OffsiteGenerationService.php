<?php

namespace App\Services;

use App\Models\{
    DumpTransaksiCbs,
    DumpDpkApuppt,
    DumpKredit,
    DumpBiayaBeban,
    DumpPengaduan,
    StagingOffsite,
    RegisterHarian,
    KkaTellerKas,
    KkaKredit,
    KkaBiayaBeban,
    KkaBiayaInternal,
    KkaPengaduan,
    KkaTransaksiUmum,
    KkaTransferKu,
    WpOffsite
};
use Illuminate\Support\Facades\DB;

class OffsiteGenerationService
{
    public function __construct(private OffsiteDetectionService $detection) {}

    private array $dumpSources = [
        DumpTransaksiCbs::class => 'CBS',
        DumpDpkApuppt::class    => 'DPK',
        DumpKredit::class       => 'Kredit',
        DumpBiayaBeban::class   => 'Biaya',

        DumpPengaduan::class    => 'Pengaduan',
    ];

    private array $dumpTableNames = [
        DumpTransaksiCbs::class => 'dump_transaksi_cbs',
        DumpDpkApuppt::class    => 'dump_dpk_apuppt',
        DumpKredit::class       => 'dump_kredit',
        DumpBiayaBeban::class   => 'dump_biaya_beban',
        DumpPengaduan::class    => 'dump_pengaduan',
    ];

    private array $kkaModelBySlug = [
        'kka_teller_kas'     => KkaTellerKas::class,
        'kka_kredit'         => KkaKredit::class,
        'kka_biaya_beban'    => KkaBiayaBeban::class,
        'kka_biaya_internal' => KkaBiayaInternal::class,
        'kka_pengaduan'      => KkaPengaduan::class,
        'kka_transaksi_umum' => KkaTransaksiUmum::class,
        'kka_transfer_ku'    => KkaTransferKu::class,
    ];

    public function generate(WpOffsite $wp): array
    {
        return DB::transaction(function () use ($wp) {
            $this->resetPreviousResults($wp);

            $totalDiproses = 0;
            $totalMasukKka = 0;

            foreach ($this->dumpSources as $modelClass => $source) {
                $rows = $modelClass::where('wp_offsite_id', $wp->id)->get();

                foreach ($rows as $row) {
                    $totalDiproses++;
                    if ($this->processRow($row, $modelClass, $source, $wp)) {
                        $totalMasukKka++;
                    }
                }
            }

            // Pass kedua: deteksi lintas-baris untuk Pengaduan berulang
            $totalMasukKka += $this->tandaiPengaduanBerulang($wp);

            $this->rebuildRegisterHarian($wp);

            return [
                'total_diproses' => $totalDiproses,
                'total_masuk_kka' => $totalMasukKka,
            ];
        });
    }

    private function resetPreviousResults(WpOffsite $wp): void
    {
        foreach (array_keys($this->kkaModelBySlug) as $table) {
            DB::table($table)->where('wp_offsite_id', $wp->id)->delete();
        }
        StagingOffsite::where('wp_offsite_id', $wp->id)->delete();
        RegisterHarian::where('wp_offsite_id', $wp->id)->delete();
    }

    private function processRow($row, string $modelClass, string $source, WpOffsite $wp): bool
    {
        $data = $this->extractData($row, $source);

        $statusDq = $this->detection->validateDataQuality($data, $wp);
        $result   = $this->detection->detectBaris($data, $source, $wp);


        $masukKkaFinal = $result['risk_level'] !== 'Low';
        $alasan = $masukKkaFinal ? null : 'Low risk - kandidat sampling, belum otomatis masuk KKA';

        $staging = StagingOffsite::create([
            'wp_offsite_id'         => $wp->id,
            'tanggal_data'          => $data['tanggal_data'],
            'kode_unit'             => $data['kode_unit'],
            'nama_unit'             => $wp->nama_unit,
            'jenis_unit'            => $wp->jenis_unit,
            'ra_id'                 => $wp->ra_pelaksana_id,
            'nama_ra'               => optional($wp->raPelaksana)->name,
            'source_table'          => $this->dumpTableNames[$modelClass],
            'source_record_id'      => $row->getKey(),
            'object_id'             => $data['object_id'] ?? null,
            'case_id'               => $result['case_id'],
            'area_review'           => $result['area_review'],
            'deskripsi_narasi'      => $data['deskripsi_narasi'] ?? null,
            'nominal'               => $data['nominal'] ?? null,
            'user_maker'            => $data['user_maker'] ?? null,
            'risk_level'            => $result['risk_level'],
            'exception_awal'        => $result['jumlah_flag_risiko'] > 0,
            'kka_sheet_tujuan'      => $result['kka_sheet_tujuan'],
            'sampel_low'            => false,
            'masuk_kka_final'       => $masukKkaFinal,
            'alasan_tidak_masuk_kka'=> $alasan,
            'status_data_quality'   => $statusDq,
        ]);

        $row->forceFill([
            'risk_level'          => $result['risk_level'],
            'area_review'         => $result['area_review'],

            'kka_sheet_tujuan'    => $result['kka_sheet_tujuan'],
            'status_data_quality' => $statusDq,
        ])->save();

        $masukKka = $statusDq === 'VALID'
            && $masukKkaFinal
            && isset($this->kkaModelBySlug[$result['kka_sheet_tujuan']]);

        if ($masukKka) {
            $kkaModel = $this->kkaModelBySlug[$result['kka_sheet_tujuan']];
            $kkaModel::create([
                'wp_offsite_id'    => $wp->id,
                'staging_id'       => $staging->id,
                'area_review'      => $result['area_review'],
                'tanggal_data'     => $data['tanggal_data'],
                'kode_unit'        => $data['kode_unit'],
                'nama_unit'        => $wp->nama_unit,
                'ra_id'            => $wp->ra_pelaksana_id,
                'nama_ra'          => optional($wp->raPelaksana)->name,
                'source_sheet'     => $this->dumpTableNames[$modelClass],
                'object_id'        => $data['object_id'] ?? null,
                'case_id'          => $result['case_id'],
                'deskripsi_narasi' => $data['deskripsi_narasi'] ?? null,
                'nominal'          => $data['nominal'] ?? null,
                'user_maker'       => $data['user_maker'] ?? null,
                'risk_awal'        => $result['risk_level'],
                'status_review'    => 'Belum Review',
            ]);
        }

        return $masukKka;
    }


    private function extractData($row, string $source): array
    {
        return match ($source) {
            'CBS' => [
                'kode_unit'        => $row->kode_unit,
                'tanggal_data'     => $row->tanggal_data,
                'object_id'        => $row->no_referensi,
                'no_referensi'     => $row->no_referensi,
                'kode_transaksi'   => $row->kode_transaksi,
                'nama_transaksi'   => $row->nama_transaksi,
                'user_maker'       => $row->user_maker,
                'nominal'          => $row->nominal,
                'deskripsi_narasi' => $row->deskripsi_narasi,
            ],
            'DPK' => [
                'kode_unit'        => $row->kode_unit,
                'tanggal_data'     => $row->tanggal_data,
                'object_id'        => $row->no_rekening,
                'no_rekening'      => $row->no_rekening,
                'nama_nasabah'     => $row->nama_nasabah,
                'produk'           => $row->produk,
                'status_rekening'  => $row->status_rekening,
                'deskripsi_narasi' => trim(($row->nama_nasabah ?? '') . ' - ' . ($row->produk ?? '')),
                'nominal'          => $row->saldo_akhir,
            ],
            'Kredit' => [
                'kode_unit'          => $row->kode_unit,
                'tanggal_data'       => $row->tanggal_data,
                'object_id'          => $row->no_rekening_kredit,
                'no_rekening_kredit' => $row->no_rekening_kredit,
                'nama_debitur'       => $row->nama_debitur,

                'produk_kredit'      => $row->produk_kredit,
                'kolektibilitas'     => $row->kolektibilitas,
                'deskripsi_narasi'   => trim(($row->nama_debitur ?? '') . ' - ' . ($row->produk_kredit ?? '')),
                'nominal'            => $row->baki_debet,
            ],
            'Biaya' => [
                'kode_unit'            => $row->kode_unit,
                'tanggal_data'         => $row->tanggal_data,
                'object_id'            => $row->no_arsip,
                'kode_transaksi'       => $row->kode_transaksi,
                'keterangan_transaksi' => $row->keterangan_transaksi,
                'nominal'              => $row->nominal,
                'user_maker'           => $row->user_input,
                'deskripsi_narasi'     => $row->keterangan_transaksi,
            ],
            'Pengaduan' => [
                'kode_unit'            => $row->kode_unit,
                'tanggal_data'         => $row->tanggal_data,
                'object_id'            => $row->no_tiket,
                'no_nasabah'           => $row->no_nasabah,
                'no_rekening_nasabah'  => $row->no_rekening_nasabah,
                'jenis_pengaduan'      => $row->jenis_pengaduan,
                'isi_pengaduan'        => $row->isi_pengaduan,
                'status_pengaduan'     => $row->status_pengaduan,
                'TGL_SELESAI'          => $row->tanggal_selesai,
                'NOMINAL_KERUGIAN'     => $row->nominal_kerugian,
                'BUKTI_PENYELESAIAN'   => $row->bukti_penyelesaian,
                'CATATAN_TL_CABANG'    => $row->catatan_tl_cabang,
                'TGL_TERIMA'           => $row->tanggal_pengaduan,
                'deskripsi_narasi'     => trim(($row->jenis_pengaduan ?? '') . ' - ' . ($row->isi_pengaduan ?? '')),
                'nominal'              => $row->nominal_kerugian,
            ],
            default => [],
        };
    }

    /**
     * Pass kedua: tandai staging Pengaduan yang nasabahnya mengadu >1 kali dalam WP ini.
     * Butuh query lintas-baris — tidak bisa dihitung per-baris di detectBaris().
     * Identifier prioritas: no_nasabah, fallback ke no_rekening_nasabah.
     * Baris Low yang berulang di-upgrade ke Moderate dan dibuatkan baris KKA.
     */
    private function tandaiPengaduanBerulang(WpOffsite $wp): int
    {
        $tambahKka = 0;

        // Ambil semua staging Pengaduan WP ini yang punya identifier nasabah
        $stagings = StagingOffsite::where('wp_offsite_id', $wp->id)
            ->where('source_table', 'dump_pengaduan')
            ->where('status_data_quality', 'VALID')
            ->get();

        if ($stagings->isEmpty()) {
            return 0;
        }

        // Bangun map: identifier_nasabah -> [staging_ids]
        $mapNasabah = [];
        foreach ($stagings as $staging) {
            $raw = $staging->deskripsi_narasi;
            $data = is_array($raw) ? $raw : (json_decode($raw, true) ?? []);

            // Cek berbagai kemungkinan nama kolom CSV
            $identifier = $data['no_nasabah'] ?? $data['NO_NSB'] ?? $data['NO_CIF'] ?? $data['CIF']
                ?? $data['no_rekening_nasabah'] ?? $data['NO_REK'] ?? $data['NO_REKENING']
                ?? null;

            if (!empty($identifier)) {
                $mapNasabah[$identifier][] = $staging;
            }
        }

        // Proses identifier yang muncul >1 kali
        foreach ($mapNasabah as $identifier => $group) {
            if (count($group) <= 1) {
                continue;
            }

            foreach ($group as $staging) {
                $flags = $staging->flags ?? [];
                if ($flags['berulang'] ?? false) {
                    continue; // sudah ditandai sebelumnya
                }

                $flags['berulang'] = true;
                $riskLama = $staging->risk_level;

                // Low berulang → upgrade ke Moderate
                $riskBaru = ($riskLama === 'Low') ? 'Moderate' : $riskLama;
                $perluKkaBaru = !in_array($riskBaru, ['Low', 'Exclude']);

                $staging->update([
                    'flags'              => $flags,
                    'risk_level'         => $riskBaru,
                    'kka_sheet_tujuan'   => $perluKkaBaru ? 'kka_pengaduan' : $staging->kka_sheet_tujuan,
                    'masuk_kka_final'    => $perluKkaBaru,
                    'alasan_tidak_masuk_kka' => $perluKkaBaru ? null : $staging->alasan_tidak_masuk_kka,
                ]);

                // Buat baris KKA baru hanya jika sebelumnya Low (belum ada KKA-nya)
                if ($riskLama === 'Low' && $perluKkaBaru) {
                    $raw = $staging->deskripsi_narasi;
                    $data = is_array($raw) ? $raw : (json_decode($raw, true) ?? []);

                    KkaPengaduan::create([
                        'wp_offsite_id'    => $wp->id,
                        'staging_id'       => $staging->id,
                        'area_review'      => 'Pengaduan',
                        'tanggal_data'     => $staging->tanggal_data,
                        'kode_unit'        => $staging->kode_unit,
                        'nama_unit'        => $staging->nama_unit,
                        'ra_id'            => $wp->ra_pelaksana_id,
                        'nama_ra'          => optional($wp->raPelaksana)->name,
                        'source_sheet'     => 'dump_pengaduan',
                        'object_id'        => $staging->object_id,
                        'deskripsi_narasi' => $staging->deskripsi_narasi,
                        'nominal'          => $staging->nominal,
                        'risk_awal'        => $riskBaru,
                        'jenis_exception_awal' => 'berulang',
                        'status_review'    => 'Belum Review',
                    ]);
                    $tambahKka++;
                }
            }
        }

        return $tambahKka;
    }

    private function rebuildRegisterHarian(WpOffsite $wp): void
    {
        $rows = StagingOffsite::where('wp_offsite_id', $wp->id)

            ->where('status_data_quality', 'VALID')
            ->get()
            ->groupBy(fn ($r) => $r->tanggal_data->format('Y-m-d') . '|' . $r->area_review);

        foreach ($rows as $key => $group) {
            [$tanggal, $area] = explode('|', $key);
            $first = $group->first();

            $riskOrder = ['High' => 3, 'Moderate' => 2, 'Low' => 1];
            $risikoTertinggi = $group->pluck('risk_level')
                ->filter()
                ->sortByDesc(fn ($r) => $riskOrder[$r] ?? 0)
                ->first();

            RegisterHarian::create([
                'wp_offsite_id'      => $wp->id,
                'tanggal_data'       => $tanggal,
                'target_review_h1'   => \Carbon\Carbon::parse($tanggal)->addDay(),
                'kode_unit'          => $first->kode_unit,
                'nama_unit'          => $first->nama_unit,
                'ra_id'              => $wp->ra_pelaksana_id,
                'nama_ra'            => optional($wp->raPelaksana)->name,
                'area_review'        => $area,
                'populasi_eligible'  => $group->where('masuk_kka_final', true)->count(),
                'sampel_low'         => $group->where('sampel_low', true)->count(),
                'kka_final'          => $group->where('masuk_kka_final', true)->count(),
                'exception'          => $group->where('exception_awal', true)->count(),
                'perlu_klarifikasi'  => 0,
                'perlu_eskalasi'     => $group->where('risk_level', 'High')->count(),
                'risiko_tertinggi'   => $risikoTertinggi,
                'hasil_awal'         => 'Belum Review',
                'status_review'      => 'Belum Review',

            ]);
        }
    }
}