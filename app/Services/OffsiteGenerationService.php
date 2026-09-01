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

            // Pass kedua: deteksi lintas-baris Biaya (split & berulang) dan Pengaduan (berulang)
            $totalMasukKka += $this->tandaiBiayaLintas($wp);
            $totalMasukKka += $this->tandaiPengaduanBerulang($wp);

            // Pass ketiga: CBS case pairing net balance
            $totalMasukKka += $this->tandaiCbsCasePairing($wp);

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
        $data = $this->extractData($row, $source, $wp);

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


    private function extractData($row, string $source, WpOffsite $wp): array
    {
        return match ($source) {
            'CBS' => [
                'kode_unit'        => $row->kode_unit,
                'tanggal_data'     => $row->tanggal_data,
                'jenis_unit'       => $wp->jenis_unit,
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
                'tanggal_realisasi'  => $row->tanggal_realisasi,
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
     * Pass kedua Biaya: deteksi split_transaksi dan transaksi_berulang lintas-baris.
     *
     * split_transaksi  : kombinasi (kode_unit + tanggal + no_rekening + keterangan + nominal)
     *                    PERSIS SAMA muncul >1 kali — indikasi dipecah hindari threshold.
     * transaksi_berulang: kombinasi (kode_unit + no_rekening + keterangan + nominal)
     *                    sama tanpa syarat referensi sama — muncul >1 kali lintas tanggal.
     *
     * Keduanya men-upgrade Low → Moderate dan membuat baris KKA baru.
     * High tetap High (tidak diturunkan).
     */
    private function tandaiBiayaLintas(WpOffsite $wp): int
    {
        $tambahKka = 0;

        $stagings = StagingOffsite::where('wp_offsite_id', $wp->id)
            ->where('source_table', 'dump_biaya_beban')
            ->where('status_data_quality', 'VALID')
            ->get();

        if ($stagings->isEmpty()) {
            return 0;
        }

        // Bangun lookup dari dump_biaya_beban untuk data mentah (nominal, GL, keterangan)
        $dumpIds = $stagings->pluck('source_record_id')->filter();
        $dumpMap = DumpBiayaBeban::whereIn('id', $dumpIds)
            ->get()
            ->keyBy('id');

        // ── Hitung frekuensi untuk kedua flag ──────────────────────────────
        $countSplit    = []; // key: unit|tgl|gl|ket|nominal
        $countBerulang = []; // key: unit|gl|ket|nominal

        foreach ($stagings as $staging) {
            $dump = $dumpMap[$staging->source_record_id] ?? null;
            if (!$dump) continue;

            $gl  = $dump->no_rekening ?? '';
            $ket = strtoupper(trim($dump->keterangan_transaksi ?? ''));
            $nom = (string) ($dump->nominal ?? '0');
            $tgl = $dump->tanggal_data ? $dump->tanggal_data->format('Y-m-d') : '';

            $keySplit    = implode('|', [$staging->kode_unit, $tgl, $gl, $ket, $nom]);
            $keyBerulang = implode('|', [$staging->kode_unit, $gl, $ket, $nom]);

            $countSplit[$keySplit][]    = $staging->id;
            $countBerulang[$keyBerulang][] = $staging->id;
        }

        // Set staging_id yang kena flag
        $splitIds    = [];
        $berulangIds = [];

        foreach ($countSplit as $key => $ids) {
            if (count($ids) > 1) {
                $splitIds = array_merge($splitIds, $ids);
            }
        }
        foreach ($countBerulang as $key => $ids) {
            if (count($ids) > 1) {
                $berulangIds = array_merge($berulangIds, $ids);
            }
        }

        $flaggedIds = array_unique(array_merge($splitIds, $berulangIds));
        if (empty($flaggedIds)) {
            return 0;
        }

        // ── Update staging & buat KKA untuk yang belum punya ──────────────
        $splitSet    = array_flip($splitIds);
        $berulangSet = array_flip($berulangIds);

        foreach ($stagings->whereIn('id', $flaggedIds) as $staging) {
            $flags = $staging->flags ?? [];

            // Jangan proses ulang yang sudah ditandai
            if (($flags['split_transaksi'] ?? false) || ($flags['transaksi_berulang'] ?? false)) {
                continue;
            }

            $flags['split_transaksi']    = isset($splitSet[$staging->id]);
            $flags['transaksi_berulang'] = isset($berulangSet[$staging->id]);

            $riskLama = $staging->risk_level;
            // split_transaksi → High sesuai panduan §5.3; transaksi_berulang → Moderate
            if ($flags['split_transaksi']) {
                $riskBaru = 'High';
            } elseif ($riskLama === 'Low') {
                $riskBaru = 'Moderate';
            } else {
                $riskBaru = $riskLama;
            }

            $perluKkaBaru = !in_array($riskBaru, ['Low', 'Exclude']);

            $staging->update([
                'flags'                  => $flags,
                'risk_level'             => $riskBaru,
                'kka_sheet_tujuan'       => $perluKkaBaru ? 'kka_biaya_beban' : $staging->kka_sheet_tujuan,
                'masuk_kka_final'        => $perluKkaBaru,
                'alasan_tidak_masuk_kka' => $perluKkaBaru ? null : $staging->alasan_tidak_masuk_kka,
            ]);

            // Buat KKA hanya jika sebelumnya Low (belum ada baris KKA-nya)
            if ($riskLama === 'Low' && $perluKkaBaru) {
                $flagNames = implode(', ', array_keys(array_filter([
                    'split_transaksi'    => $flags['split_transaksi'],
                    'transaksi_berulang' => $flags['transaksi_berulang'],
                ])));

                KkaBiayaBeban::create([
                    'wp_offsite_id'        => $wp->id,
                    'staging_id'           => $staging->id,
                    'area_review'          => 'Biaya/Beban',
                    'tanggal_data'         => $staging->tanggal_data,
                    'kode_unit'            => $staging->kode_unit,
                    'nama_unit'            => $staging->nama_unit,
                    'ra_id'                => $wp->ra_pelaksana_id,
                    'nama_ra'              => optional($wp->raPelaksana)->name,
                    'source_sheet'         => 'dump_biaya_beban',
                    'object_id'            => $staging->object_id,
                    'deskripsi_narasi'     => $staging->deskripsi_narasi,
                    'nominal'              => $staging->nominal,
                    'risk_awal'            => $riskBaru,
                    'jenis_exception_awal' => $flagNames,
                    'status_review'        => 'Belum Review',
                ]);
                $tambahKka++;
            }
        }

        return $tambahKka;
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

        // Bangun lookup dump_pengaduan untuk identifier nasabah
        $dumpIds = $stagings->pluck('source_record_id')->filter();
        $dumpMap = DumpPengaduan::whereIn('id', $dumpIds)->get()->keyBy('id');

        // Bangun map: identifier_nasabah -> [stagings]
        $mapNasabah = [];
        foreach ($stagings as $staging) {
            $dump = $dumpMap[$staging->source_record_id] ?? null;
            $identifier = $dump
                ? ($dump->no_nasabah ?? $dump->no_rekening_nasabah ?? null)
                : null;

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

                // Cek nominal_material dari dump asli
                $dump = $dumpMap[$staging->source_record_id] ?? null;
                $nominalMaterial = $dump && ((float)$dump->nominal_kerugian >= 5000000);

                // berulang + nominal_material → High; berulang saja → Moderate
                if ($nominalMaterial) {
                    $riskBaru = 'High';
                } elseif ($riskLama === 'Low') {
                    $riskBaru = 'Moderate';
                } else {
                    $riskBaru = $riskLama;
                }
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

    private function areaToKkaSheet(string $area): string
    {
        return [
            'Teller/Kas'     => 'kka_teller_kas',
            'Kredit'         => 'kka_kredit',
            'Biaya/Beban'    => 'kka_biaya_beban',
            'Biaya/Internal' => 'kka_biaya_internal',
            'Pengaduan'      => 'kka_pengaduan',
            'Transaksi Umum' => 'kka_transaksi_umum',
            'Transfer/KU'    => 'kka_transfer_ku',
            'DPK/APU-PPT'    => 'kka_transaksi_umum',
        ][$area] ?? 'kka_teller_kas';
    }

    /**
     * Pass ketiga CBS: Case Pairing net balance (§2.6).
     * Key: tanggal(Ymd)|no_referensi — kumpulkan semua baris CBS dengan key sama,
     * hitung net nominal (Debit - Kredit). Jika |net| > 1 → case_tidak_balance → High.
     */
    private function tandaiCbsCasePairing(WpOffsite $wp): int
    {
        $tambahKka = 0;

        $stagings = StagingOffsite::where('wp_offsite_id', $wp->id)
            ->where('source_table', 'dump_transaksi_cbs')
            ->where('status_data_quality', 'VALID')
            ->whereNotNull('case_id')
            ->get();

        if ($stagings->isEmpty()) {
            return 0;
        }

        // Load dump CBS untuk ambil d_k (Debit/Kredit)
        $dumpIds = $stagings->pluck('source_record_id')->filter();
        $dumpMap = DumpTransaksiCbs::whereIn('id', $dumpIds)->get()->keyBy('id');

        // Kelompokkan per case_id, hitung net nominal
        $caseGroups = []; // case_id => ['net' => float, 'stagings' => []]
        foreach ($stagings as $staging) {
            $dump = $dumpMap[$staging->source_record_id] ?? null;
            if (!$dump) continue;

            $nominal = (float) ($dump->nominal ?? 0);
            $dk      = strtoupper(trim($dump->d_k ?? ''));
            // D = Debit (positif), K = Kredit (negatif)
            $signed  = in_array($dk, ['D', 'DB', 'DEBIT']) ? $nominal : -$nominal;

            $caseGroups[$staging->case_id]['net']       = ($caseGroups[$staging->case_id]['net'] ?? 0) + $signed;
            $caseGroups[$staging->case_id]['stagings'][] = $staging;
        }

        foreach ($caseGroups as $caseId => $group) {
            // Sesuai §2.6: |net| <= 1 = Balance (aman), > 1 = Tidak Balance (red flag)
            if (abs($group['net']) <= 1) {
                continue;
            }

            foreach ($group['stagings'] as $staging) {
                $flags = $staging->flags ?? [];
                if ($flags['case_tidak_balance'] ?? false) {
                    continue;
                }

                $flags['case_tidak_balance'] = true;
                $riskLama  = $staging->risk_level;
                $riskBaru  = 'High'; // case_tidak_balance selalu High per §2.6
                $perluKkaBaru = $riskLama === 'Low' || $riskLama === 'Moderate';

                $staging->update([
                    'flags'                  => $flags,
                    'risk_level'             => $riskBaru,
                    'kka_sheet_tujuan'       => $staging->kka_sheet_tujuan === 'Register'
                        ? $this->areaToKkaSheet($staging->area_review)
                        : $staging->kka_sheet_tujuan,
                    'masuk_kka_final'        => true,
                    'alasan_tidak_masuk_kka' => null,
                ]);

                // Buat KKA baru hanya jika sebelumnya belum masuk KKA
                if ($perluKkaBaru) {
                    $kkaSheet = $staging->kka_sheet_tujuan === 'Register'
                        ? $this->areaToKkaSheet($staging->area_review)
                        : $staging->kka_sheet_tujuan;
                    $kkaModel = $this->kkaModelBySlug[$kkaSheet] ?? KkaTellerKas::class;

                    $kkaModel::create([
                        'wp_offsite_id'        => $wp->id,
                        'staging_id'           => $staging->id,
                        'area_review'          => $staging->area_review,
                        'tanggal_data'         => $staging->tanggal_data,
                        'kode_unit'            => $staging->kode_unit,
                        'nama_unit'            => $staging->nama_unit,
                        'ra_id'                => $wp->ra_pelaksana_id,
                        'nama_ra'              => optional($wp->raPelaksana)->name,
                        'source_sheet'         => 'dump_transaksi_cbs',
                        'object_id'            => $staging->object_id,
                        'case_id'              => $staging->case_id,
                        'deskripsi_narasi'     => $staging->deskripsi_narasi,
                        'nominal'              => $staging->nominal,
                        'user_maker'           => $staging->user_maker,
                        'risk_awal'            => $riskBaru,
                        'jenis_exception_awal' => 'case_tidak_balance',
                        'status_review'        => 'Belum Review',
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