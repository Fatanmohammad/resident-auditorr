<?php

namespace App\Http\Controllers\DailyRegister;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyRegister\PhysicalKka;
use App\Models\DailyRegister\PhysicalKkaCashDetail;
use App\Models\DailyRegister\PhysicalKkaAtmMachine;
use App\Models\DailyRegister\PhysicalKkaAtmCassette;
use App\Models\DailyRegister\PhysicalKkaObsDetail;
use App\Models\DailyRegister\PhysicalKkaStokDetail;
use App\Models\DailyRegister\PhysicalKkaKartuAtmDetail;
use App\Models\DailyRegister\PhysicalKkaBarangCetakanDetail;
use App\Models\DailyRegister\RegisterKlarifikasi;
use App\Models\DailyRegister\KontrolMingguan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Cabang;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Services\DailyRegister\SimplePdfBuilder;

class PhysicalKkaController extends Controller
{
    public function index($id = null)
    {
        $user = Auth::user();
        $isAdmin = $user && in_array($user->role, ['admin', 'reviewer', 'kabag_ra']);

        // Jika Admin dan ID tidak ada, tampilkan halaman rekapitulasi cabang
        if ($isAdmin && !$id) {
            $indukCabangs = Cabang::whereNull('parent_id')
                ->where('kode_cabang', 'like', 'BS-%')
                ->orderBy('kode_cabang', 'asc')
                ->get();

            $kkas = $indukCabangs->map(function($induk) {
                $kcUnit = \App\Models\Unit::where('cabang_id', $induk->id)
                    ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                    ->first();
                
                $kodeKC = $kcUnit ? $kcUnit->unit_code : '-';
                $namaKC = $kcUnit ? $kcUnit->unit_name : $induk->nama_cabang;

                $latestKka = PhysicalKka::with('user')->where(function($q) use ($kodeKC, $induk) {
                    $clean = str_replace('BS-', '', $kodeKC);
                    $q->where('kode_unit', $kodeKC)
                      ->orWhere('kode_unit', $clean)
                      ->orWhere('kode_unit', 'BS-' . $clean)
                      ->orWhere('kode_unit', $induk->kode_cabang);
                })->orderBy('created_at', 'desc')->first();

                $raUser = $latestKka ? $latestKka->user : \App\Models\User::where('cabang_id', $induk->id)->where('role', 'ra')->first();

                return (object) [
                    'id' => $latestKka ? $latestKka->id : null,
                    'kode_unit' => $kodeKC,
                    'nama_cabang' => $namaKC,
                    'tanggal_pemeriksaan' => $latestKka ? $latestKka->tanggal_pemeriksaan : null,
                    'user' => $raUser,
                    'status_final' => $latestKka ? $latestKka->status_final : 'Belum Submit',
                    'no_arsip' => $latestKka ? $latestKka->no_arsip : null,
                ];
            });

            return view('daily_register.rekap_kka', compact('kkas', 'isAdmin'));
        }

        // =========================================================================
        // 1. DATA CASH OPNAME KHASANAH (3 KELOMPOK STANDAR BA PEMERIKSAAN KAS)
        // =========================================================================
        $ulePecahan = [
            ['pecahan' => 'Rp 100.000', 'nilai' => 100000],
            ['pecahan' => 'Rp 75.000',  'nilai' => 75000],
            ['pecahan' => 'Rp 50.000',  'nilai' => 50000],
            ['pecahan' => 'Rp 20.000',  'nilai' => 20000],
            ['pecahan' => 'Rp 10.000',  'nilai' => 10000],
            ['pecahan' => 'Rp 5.000',   'nilai' => 5000],
            ['pecahan' => 'Rp 2.000',   'nilai' => 2000],
            ['pecahan' => 'Rp 1.000',   'nilai' => 1000],
        ];

        $logamPecahan = [
            ['pecahan' => 'Rp 1.000', 'nilai' => 1000],
            ['pecahan' => 'Rp 500',   'nilai' => 500],
            ['pecahan' => 'Rp 200',   'nilai' => 200],
            ['pecahan' => 'Rp 100',   'nilai' => 100],
            ['pecahan' => 'Rp 50',    'nilai' => 50],
            ['pecahan' => 'Rp 25',    'nilai' => 25],
        ];

        $utlePecahan = [
            ['pecahan' => 'Rp 100.000', 'nilai' => 100000],
            ['pecahan' => 'Rp 75.000',  'nilai' => 75000],
            ['pecahan' => 'Rp 50.000',  'nilai' => 50000],
            ['pecahan' => 'Rp 20.000',  'nilai' => 20000],
            ['pecahan' => 'Rp 10.000',  'nilai' => 10000],
            ['pecahan' => 'Rp 5.000',   'nilai' => 5000],
            ['pecahan' => 'Rp 2.000',   'nilai' => 2000],
            ['pecahan' => 'Rp 1.000',   'nilai' => 1000],
        ];

        $khasanahUleItems = [];
        foreach ($ulePecahan as $p) {
            $khasanahUleItems[] = [
                'kelompok' => 'ULE',
                'objek' => 'Uang Kertas Layak Edar',
                'pecahan' => $p['pecahan'],
                'nilai' => $p['nilai'],
                'jumlah_lembar' => 0,
                'nominal' => 0,
            ];
        }

        $khasanahLogamItems = [];
        foreach ($logamPecahan as $p) {
            $khasanahLogamItems[] = [
                'kelompok' => 'Logam',
                'objek' => 'Uang Logam',
                'pecahan' => $p['pecahan'],
                'nilai' => $p['nilai'],
                'jumlah_lembar' => 0,
                'nominal' => 0,
            ];
        }

        $khasanahUtleItems = [];
        foreach ($utlePecahan as $p) {
            $khasanahUtleItems[] = [
                'kelompok' => 'UTLE',
                'objek' => 'Uang lusuh/tidak layak edar',
                'pecahan' => $p['pecahan'],
                'nilai' => $p['nilai'],
                'jumlah_lembar' => 0,
                'nominal' => 0,
            ];
        }

        // Backward-compatible cashItems
        $cashItems = array_merge($khasanahUleItems, $khasanahLogamItems, $khasanahUtleItems);

        // =========================================================================
        // 2. DATA OBSERVASI LINGKUNGAN (TERSTRUKTUR SPESIFIK STANDAR BANK SULTENG)
        // =========================================================================
        $obsItems = self::getDefaultObsItems();

        // =========================================================================
        // 3. DATA STOK OPNAME DOKUMEN BERHARGA
        // =========================================================================
        $stokItems = self::getDefaultStokItems();

        // =========================================================================
        // 4. DATA REKONSILIASI KARTU ATM (SECTION A & SECTION B — ACUAN E.4.3)
        // =========================================================================
        $defaultCardTypes = [
            ['jenis_kartu' => 'Kartu Silver',   'harga_modal' => 42180],
            ['jenis_kartu' => 'Kartu Gold',     'harga_modal' => 68820],
            ['jenis_kartu' => 'Kartu Platinum', 'harga_modal' => 68820],
        ];

        $kartuAtmStockRows = [];
        foreach ($defaultCardTypes as $ct) {
            $kartuAtmStockRows[] = [
                'id' => null,
                'sub_area' => 'Persediaan Kartu ATM',
                'jenis_kartu' => $ct['jenis_kartu'],
                'harga_modal' => $ct['harga_modal'],
                'qty_fisik_kosong' => 0,
                'qty_fisik_rusak' => 0,
                'nominal_fisik' => 0,
                'saldo_buku_qty' => 0,
                'saldo_buku_nominal' => 0,
                'selisih_persediaan' => 0,
                'pin_mailer_qty' => 0,
                'selisih_pin_mailer' => 0,
                'keterangan_lokasi' => 'Brankas Khasanah',
            ];
        }

        // 8 Item Kontrol Pengelolaan Kartu ATM (Section B Checklist Murni Ya/Tidak)
        $standardAtmControls = [
            'Formulir permohonan kartu ATM (asli, tanda tangan nasabah) lengkap.',
            'Register kartu ATM keluar/masuk (stock opname) tertib dan mutakhir.',
            'Otorisasi pengaktifan kartu ATM sesuai kewenangan.',
            'Tanda terima penyerahan kartu ATM oleh nasabah terdokumentasi.',
            'Penyimpanan & penyerahan PIN Mailer dilakukan terpisah dari fisik kartu ATM (dual control).',
            'Kartu ATM rusak / expired / tertelan dibuatkan Berita Acara Pemusnahan.',
            'Penyimpanan stok kartu ATM kosong (blank) berada di lemari terkunci / brankas aman (dual control).',
            'Register kartu ATM yang diblokir / ditutup tercatat lengkap.',
        ];

        $kartuAtmControlRows = [];
        foreach ($standardAtmControls as $cText) {
            $kartuAtmControlRows[] = [
                'id' => null,
                'sub_area' => 'Kontrol',
                'jenis_kartu' => $cText,
                'jawaban_kontrol' => 'Ya',
                'catatan_kontrol' => '',
            ];
        }

        $kartuAtmItems = array_merge($kartuAtmStockRows, $kartuAtmControlRows);

        // =========================================================================
        // 5. DATA REKONSILIASI BARANG CETAKAN (MERGED KRITERIA & 4 KONTROL YA/TIDAK)
        // =========================================================================
        $rawBarangCetakan = [
            ['kategori' => 'Formulir',       'nama_barang' => 'Formulir / Dokumen Pembukaan ATM', 'kriteria' => 'Formulir Pembukaan ATM — Kosong / Belum Digunakan', 'satuan' => 'Bh'],
            ['kategori' => 'Formulir',       'nama_barang' => 'Formulir / Dokumen Pembukaan ATM', 'kriteria' => 'Formulir Pembukaan ATM — Rusak / Batal', 'satuan' => 'Bh'],
            ['kategori' => 'Bilyet Deposito', 'nama_barang' => 'Bilyet Deposito', 'kriteria' => 'Bilyet Deposito — Kosong / Belum Digunakan', 'satuan' => 'Bh'],
            ['kategori' => 'Bilyet Deposito', 'nama_barang' => 'Bilyet Deposito', 'kriteria' => 'Bilyet Deposito — Rusak / Batal', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan',  'nama_barang' => 'Tabungan Simantap', 'kriteria' => 'Tabungan Simantap — Kosong / Belum Digunakan', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan',  'nama_barang' => 'Tabungan Simantap', 'kriteria' => 'Tabungan Simantap — Rusak / Batal', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan',  'nama_barang' => 'Tabungan Simpeda', 'kriteria' => 'Tabungan Simpeda — Kosong / Belum Digunakan', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan',  'nama_barang' => 'Tabungan Simpeda', 'kriteria' => 'Tabungan Simpeda — Rusak / Batal', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan',  'nama_barang' => 'Tabungan TabunganKU', 'kriteria' => 'Tabungan TabunganKU — Kosong / Belum Digunakan', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan',  'nama_barang' => 'Tabungan TabunganKU', 'kriteria' => 'Tabungan TabunganKU — Rusak / Batal', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan',  'nama_barang' => 'Tabungan PNS', 'kriteria' => 'Tabungan PNS — Kosong / Belum Digunakan', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan',  'nama_barang' => 'Tabungan PNS', 'kriteria' => 'Tabungan PNS — Rusak / Batal', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan',  'nama_barang' => 'Tabungan Tawa', 'kriteria' => 'Tabungan Tawa — Kosong / Belum Digunakan', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan',  'nama_barang' => 'Tabungan Pensiunku', 'kriteria' => 'Tabungan Pensiunku — Kosong / Belum Digunakan', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan',  'nama_barang' => 'Tabungan Simpel', 'kriteria' => 'Tabungan Simpel — Kosong / Belum Digunakan', 'satuan' => 'Bh'],
            ['kategori' => 'Cek',            'nama_barang' => 'Cek', 'kriteria' => 'Cek — Kosong / Belum Digunakan', 'satuan' => 'Bh'],
            ['kategori' => 'Cek',            'nama_barang' => 'Cek', 'kriteria' => 'Cek — Rusak / Batal', 'satuan' => 'Bh'],
            ['kategori' => 'Bilyet Giro',    'nama_barang' => 'Bilyet Giro', 'kriteria' => 'Bilyet Giro — Kosong / Belum Digunakan', 'satuan' => 'Bh'],
            ['kategori' => 'Bilyet Giro',    'nama_barang' => 'Bilyet Giro', 'kriteria' => 'Bilyet Giro — Rusak / Batal', 'satuan' => 'Bh'],
            ['kategori' => 'Formulir',       'nama_barang' => 'Kwitansi pencairan kredit', 'kriteria' => 'Kwitansi pencairan kredit — Kosong / Belum Digunakan', 'satuan' => 'Bh'],
            ['kategori' => 'Formulir',       'nama_barang' => 'Slip setoran', 'kriteria' => 'Slip setoran operasional', 'satuan' => 'Bh'],
            ['kategori' => 'Formulir',       'nama_barang' => 'Slip penarikan', 'kriteria' => 'Slip penarikan operasional', 'satuan' => 'Bh'],
            ['kategori' => 'Formulir',       'nama_barang' => 'Slip RTGS', 'kriteria' => 'Slip transfer RTGS / SKN', 'satuan' => 'Bh'],
            ['kategori' => 'Formulir',       'nama_barang' => 'Form keluhan nasabah', 'kriteria' => 'Formulir pengaduan nasabah', 'satuan' => 'Bh'],
            ['kategori' => 'Formulir',       'nama_barang' => 'Form KYC', 'kriteria' => 'Formulir Customer Due Diligence (KYC)', 'satuan' => 'Bh'],
            ['kategori' => 'Kertas',         'nama_barang' => 'Kertas kop bank', 'kriteria' => 'Kertas kop surat resmi berlogo', 'satuan' => 'Rim'],
            ['kategori' => 'Amplop',         'nama_barang' => 'Amplop kecil berlogo', 'kriteria' => 'Amplop surat dinas kecil', 'satuan' => 'Pack'],
            ['kategori' => 'Amplop',         'nama_barang' => 'Amplop panjang berlogo', 'kriteria' => 'Amplop surat dinas panjang', 'satuan' => 'Pack'],
            ['kategori' => 'Amplop',         'nama_barang' => 'Amplop sedang berlogo', 'kriteria' => 'Amplop surat dinas sedang', 'satuan' => 'Pack'],
            ['kategori' => 'Perlengkapan',   'nama_barang' => 'Signature Spectroline', 'kriteria' => 'Tinta pengaman spesimen tanda tangan', 'satuan' => 'Dos'],
            ['kategori' => 'Perlengkapan',   'nama_barang' => 'Invisible Signature Spectroline', 'kriteria' => 'Spectroline invisible verifikasi buku tabungan', 'satuan' => 'Dos'],
            ['kategori' => 'Prioritas',      'nama_barang' => 'Amplop nasabah prioritas', 'kriteria' => 'Amplop eksklusif layanan prioritas', 'satuan' => 'Bh'],
            ['kategori' => 'Prioritas',      'nama_barang' => 'Form pembukaan nasabah prioritas', 'kriteria' => 'Formulir pembukaan CIF prioritas', 'satuan' => 'Bh'],
            ['kategori' => 'Perlengkapan',   'nama_barang' => 'Pita printer tally', 'kriteria' => 'Ribbon cartridge printer passbook', 'satuan' => 'Bh'],
        ];

        $barangCetakanItems = [];
        foreach ($rawBarangCetakan as $bct) {
            $barangCetakanItems[] = [
                'id' => null,
                'kategori' => $bct['kategori'],
                'nama_barang' => $bct['nama_barang'],
                'kriteria' => $bct['kriteria'],
                'satuan' => $bct['satuan'],
                'stok_sistem' => 0,
                'hasil_fisik' => '',
                'selisih' => 0,
                'status_uji' => 'BELUM DIUJI',
                'penyebab' => '',
                'simpulan_ra' => '',
                'tanggal_uji' => date('Y-m-d'),
                'ra_pelaksana' => $user ? $user->name : '',
                'is_exception' => false,
            ];
        }

        // 4 Butir Kontrol Barang Cetakan (Checklist Ya / Tidak)
        $barangCetakanControls = [
            ['no' => 1, 'butir' => 'Pencatatan register penerimaan dan pengeluaran barang cetakan tertib dan mutakhir.', 'jawaban' => 'Ya', 'catatan' => ''],
            ['no' => 2, 'butir' => 'Penyimpanan stok barang cetakan berada di lemari terkunci (dual control).', 'jawaban' => 'Ya', 'catatan' => ''],
            ['no' => 3, 'butir' => 'Berita acara pemusnahan warkat/barang cetakan rusak atau dibatalkan terdokumentasi.', 'jawaban' => 'Ya', 'catatan' => ''],
            ['no' => 4, 'butir' => 'Kesesuaian nomor seri warkat berurutan (tidak ada nomor seri hilang/melompat).', 'jawaban' => 'Ya', 'catatan' => ''],
        ];

        $suratIzin = null;
        $statusSurat = 'Pending';
        $catatanSurat = '';
        
        $kodeUnit = '000';
        if ($user && $user->cabang_id) {
            $kcUnit = \App\Models\Unit::where('cabang_id', $user->cabang_id)
                ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                ->first();
            if ($kcUnit) {
                $kodeUnit = $kcUnit->unit_code;
            } elseif ($user->cabang && $user->cabang->kode_cabang) {
                $kodeUnit = str_replace('BS-', '', $user->cabang->kode_cabang);
            }
        } elseif ($user && !empty($user->unit_code)) {
            $kodeUnit = $user->unit_code;
        }

        $monday = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $friday = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays(4)->toDateString();

        if (!$id && !$isAdmin) {
            $existingWeeklyKka = PhysicalKka::where(function($q) use ($kodeUnit) {
                $clean = str_replace('BS-', '', $kodeUnit);
                $q->where('kode_unit', $kodeUnit)
                  ->orWhere('kode_unit', $clean)
                  ->orWhere('kode_unit', 'BS-' . $clean);
            })
            ->whereBetween('tanggal_pemeriksaan', [$monday, $friday])
            ->latest('id')
            ->first();
            if ($existingWeeklyKka) {
                $id = $existingWeeklyKka->id;
            }
        }

        $kontrolMingguan = KontrolMingguan::getOrCreateForUnit($kodeUnit);

        $atmMachines = collect();
        $kka = null;
        $noArsip = 'ARSIP-KKA-' . $kodeUnit . '-' . Carbon::now()->format('Ymd') . '-001';
        $posisiTanggalRekon = Carbon::now()->toDateString();
        $saldoKasOlibs = 0;
        $saldoFisikKhasanah = 0;
        $selisihKasKhasanah = 0;
        $penjelasanSelisihKas = '';
        $rekonKartuPenyebabSelisih = 'Tidak terdapat selisih antara fisik kartu ATM, saldo persediaan, dan PIN Mailer';

        if ($id) {
            $kka = PhysicalKka::with(['cashDetails', 'obsDetails', 'stokDetails', 'kartuAtmDetails', 'barangCetakanDetails', 'atmMachines.cassettes'])->findOrFail($id);
            $kodeUnit = $kka->kode_unit ?? $kodeUnit;
            $suratIzin = $kka->surat_izin_path;
            $statusSurat = $kka->status_surat;
            $catatanSurat = $kka->catatan_surat;
            $noArsip = $kka->no_arsip ?: $noArsip;
            $posisiTanggalRekon = $kka->posisi_tanggal_rekon ?: $posisiTanggalRekon;
            $saldoKasOlibs = (float)$kka->saldo_kas_olibs;
            $saldoFisikKhasanah = (float)$kka->saldo_fisik_khasanah;
            $selisihKasKhasanah = (float)$kka->selisih_kas_khasanah;
            $penjelasanSelisihKas = $kka->penjelasan_selisih_kas ?? '';
            $rekonKartuPenyebabSelisih = $kka->rekon_kartu_penyebab_selisih ?: $rekonKartuPenyebabSelisih;

            // Mapping Cash Khasanah details
            if ($kka->cashDetails->isNotEmpty()) {
                foreach ($khasanahUleItems as &$item) {
                    $match = $kka->cashDetails->first(fn($d) => ($d->kelompok === 'ULE' || str_contains((string)($d->objek_persediaan ?? ''), 'Layak Edar')) && $d->pecahan === $item['pecahan']);
                    if ($match) {
                        $item['id'] = $match->id;
                        $item['jumlah_lembar'] = $match->jumlah_lembar ?? 0;
                        $item['nominal'] = $match->nominal ?: $match->hasil_fisik;
                    }
                }
                unset($item);

                foreach ($khasanahLogamItems as &$item) {
                    $match = $kka->cashDetails->first(fn($d) => ($d->kelompok === 'Logam' || str_contains((string)($d->objek_persediaan ?? ''), 'Logam')) && $d->pecahan === $item['pecahan']);
                    if ($match) {
                        $item['id'] = $match->id;
                        $item['jumlah_lembar'] = $match->jumlah_lembar ?? 0;
                        $item['nominal'] = $match->nominal ?: $match->hasil_fisik;
                    }
                }
                unset($item);

                foreach ($khasanahUtleItems as &$item) {
                    $match = $kka->cashDetails->first(fn($d) => ($d->kelompok === 'UTLE' || str_contains((string)($d->objek_persediaan ?? ''), 'lusuh')) && $d->pecahan === $item['pecahan']);
                    if ($match) {
                        $item['id'] = $match->id;
                        $item['jumlah_lembar'] = $match->jumlah_lembar ?? 0;
                        $item['nominal'] = $match->nominal ?: $match->hasil_fisik;
                    }
                }
                unset($item);

                $cashItems = array_merge($khasanahUleItems, $khasanahLogamItems, $khasanahUtleItems);
            }

            // ATM Machines
            $atmMachines = $kka->atmMachines;

            // Mapping Obs: Gabungkan default structured items dengan data tersimpan agar 32 item audit selalu lengkap
            $defaultObs = self::getDefaultObsItems();
            if ($kka->obsDetails->isNotEmpty()) {
                $existingObsMap = $kka->obsDetails->keyBy('area_observasi');
                $mergedObs = [];
                foreach ($defaultObs as $def) {
                    $match = $existingObsMap->get($def['area']);
                    if ($match) {
                        $mergedObs[] = array_merge($def, [
                            'id' => $match->id,
                            'kondisi_aktual' => $match->kondisi_aktual,
                            'hasil_observasi' => $match->hasil_observasi,
                            'status_uji' => $match->status_uji,
                            'penyebab' => $match->penyebab,
                            'impact' => $match->impact,
                            'likelihood' => $match->likelihood,
                            'critical_trigger' => $match->critical_trigger,
                            'risk_score' => $match->risk_score,
                            'severity_level' => $match->severity_level,
                            'tanggal_uji' => $match->tanggal_uji,
                            'reviewer_id' => $match->reviewer_id,
                            'status_review' => $match->status_review,
                            'catatan_reviewer' => $match->catatan_reviewer,
                        ]);
                        $existingObsMap->forget($def['area']);
                    } else {
                        $mergedObs[] = $def;
                    }
                }
                foreach ($existingObsMap as $extra) {
                    $mergedObs[] = [
                        'id' => $extra->id,
                        'kategori' => 'Observasi Tambahan',
                        'area' => $extra->area_observasi,
                        'kriteria' => $extra->kriteria_diharapkan,
                        'backup_90_hari' => '—',
                        'kondisi_aktual' => $extra->kondisi_aktual,
                        'hasil_observasi' => $extra->hasil_observasi,
                        'status_uji' => $extra->status_uji,
                        'penyebab' => $extra->penyebab,
                        'impact' => $extra->impact,
                        'likelihood' => $extra->likelihood,
                        'critical_trigger' => $extra->critical_trigger,
                        'risk_score' => $extra->risk_score,
                        'severity_level' => $extra->severity_level,
                        'tanggal_uji' => $extra->tanggal_uji,
                        'reviewer_id' => $extra->reviewer_id,
                        'status_review' => $extra->status_review,
                        'catatan_reviewer' => $extra->catatan_reviewer,
                    ];
                }
                $obsItems = $mergedObs;
            }

            // Mapping Stok
            if ($kka->stokDetails->isNotEmpty()) {
                $stokItems = $kka->stokDetails->map(function($detail) {
                    $objParts = explode(' | ', $detail->kondisi_objek);
                    $objek = $objParts[0] ?? $detail->kondisi_objek;
                    $pecahan = $objParts[1] ?? '';
                    return [
                        'id' => $detail->id,
                        'sub_area' => $detail->sub_area,
                        'objek' => $objek,
                        'pecahan' => $pecahan,
                        'saldo_sistem' => $detail->saldo_sistem,
                        'hasil_fisik' => $detail->hasil_fisik,
                        'status_uji' => $detail->status_uji,
                        'penyebab' => $detail->penyebab,
                        'impact' => $detail->impact,
                        'likelihood' => $detail->likelihood,
                        'critical_trigger' => $detail->critical_trigger,
                        'risk_score' => $detail->risk_score,
                        'severity_level' => $detail->severity_level,
                        'simpulan_ra' => $detail->simpulan_ra,
                        'tanggal_uji' => $detail->tanggal_uji,
                        'reviewer_id' => $detail->reviewer_id,
                        'status_review' => $detail->status_review,
                        'catatan_reviewer' => $detail->catatan_reviewer,
                    ];
                })->toArray();
            }

            // Mapping Kartu ATM (Section A & Section B)
            $existingAtmStock = $kka->kartuAtmDetails->where('sub_area', 'Persediaan Kartu ATM');
            if ($existingAtmStock->isNotEmpty()) {
                $kartuAtmStockRows = $existingAtmStock->map(function($detail) {
                    return [
                        'id' => $detail->id,
                        'sub_area' => 'Persediaan Kartu ATM',
                        'jenis_kartu' => $detail->jenis_kartu,
                        'harga_modal' => (float)($detail->harga_modal ?: ($detail->jenis_kartu === 'Kartu Silver' ? 42180 : 68820)),
                        'qty_fisik_kosong' => (int)($detail->qty_fisik_kosong ?: $detail->hasil_fisik),
                        'qty_fisik_rusak' => (int)($detail->qty_fisik_rusak ?: 0),
                        'nominal_fisik' => (float)$detail->nominal_fisik,
                        'saldo_buku_qty' => (int)($detail->saldo_buku_qty ?: $detail->saldo_nominatif),
                        'saldo_buku_nominal' => (float)$detail->saldo_buku_nominal,
                        'selisih_persediaan' => (float)$detail->selisih_persediaan,
                        'pin_mailer_qty' => (int)$detail->pin_mailer_qty,
                        'selisih_pin_mailer' => (int)$detail->selisih_pin_mailer,
                        'keterangan_lokasi' => $detail->keterangan_lokasi ?? 'Brankas Khasanah',
                    ];
                })->toArray();
            }

            $existingAtmControls = $kka->kartuAtmDetails->where('sub_area', 'Kontrol');
            if ($existingAtmControls->isNotEmpty()) {
                foreach ($kartuAtmControlRows as &$cRow) {
                    $match = $existingAtmControls->firstWhere('jenis_kartu', $cRow['jenis_kartu']);
                    if ($match) {
                        $cRow['id'] = $match->id;
                        $cRow['jawaban_kontrol'] = $match->jawaban_kontrol ?: ($match->kondisi === 'Sesuai' ? 'Ya' : 'Tidak');
                        $cRow['catatan_kontrol'] = $match->catatan_kontrol ?? '';
                    }
                }
                unset($cRow);
            }

            $kartuAtmItems = array_merge($kartuAtmStockRows, $kartuAtmControlRows);

            // Mapping Barang Cetakan
            $existingBctDetails = $kka->barangCetakanDetails->where('kategori', '!=', 'Kontrol');
            if ($existingBctDetails->isNotEmpty()) {
                $barangCetakanItems = $existingBctDetails->map(function($detail) {
                    return [
                        'id' => $detail->id,
                        'kategori' => $detail->kategori,
                        'nama_barang' => $detail->nama_barang,
                        'kriteria' => $detail->kriteria ?: $detail->nama_barang,
                        'satuan' => $detail->satuan,
                        'stok_sistem' => (float)$detail->stok_sistem,
                        'hasil_fisik' => (float)$detail->hasil_fisik,
                        'selisih' => (float)$detail->selisih,
                        'status_uji' => $detail->status_uji,
                        'penyebab' => $detail->penyebab ?? '',
                        'simpulan_ra' => $detail->simpulan_ra ?? '',
                        'tanggal_uji' => $detail->tanggal_uji,
                        'ra_pelaksana' => $detail->ra_pelaksana,
                        'is_exception' => (bool)$detail->is_exception,
                    ];
                })->toArray();
            }

            $existingBctControls = $kka->barangCetakanDetails->where('kategori', 'Kontrol');
            if ($existingBctControls->isNotEmpty()) {
                foreach ($barangCetakanControls as &$bcRow) {
                    $match = $existingBctControls->firstWhere('nama_barang', $bcRow['butir']);
                    if ($match) {
                        $bcRow['jawaban'] = $match->jawaban_kontrol ?: ($match->kondisi === 'Sesuai' ? 'Ya' : 'Tidak');
                        $bcRow['catatan'] = $match->catatan_kontrol ?? '';
                    }
                }
                unset($bcRow);
            }
        }

        $evalKas = $kka ? $kka->getEvaluationSummary('kas') : (new PhysicalKka())->getEvaluationSummary('kas');
        $evalAtm = $kka ? $kka->getEvaluationSummary('atm') : (new PhysicalKka())->getEvaluationSummary('atm');
        $evalKartu = $kka ? $kka->getEvaluationSummary('kartu') : (new PhysicalKka())->getEvaluationSummary('kartu');
        $evalObs = $kka ? $kka->getEvaluationSummary('observasi') : (new PhysicalKka())->getEvaluationSummary('observasi');
        $evalStok = $kka ? $kka->getEvaluationSummary('stok') : (new PhysicalKka())->getEvaluationSummary('stok');
        $evalCetakan = $kka ? $kka->getEvaluationSummary('cetakan') : (new PhysicalKka())->getEvaluationSummary('cetakan');

        return view('daily_register.physical_kka', compact(
            'cashItems', 'khasanahUleItems', 'khasanahLogamItems', 'khasanahUtleItems',
            'atmMachines', 'obsItems', 'stokItems', 'kartuAtmStockRows', 'kartuAtmControlRows', 'kartuAtmItems',
            'barangCetakanItems', 'barangCetakanControls', 'noArsip', 'posisiTanggalRekon',
            'saldoKasOlibs', 'saldoFisikKhasanah', 'selisihKasKhasanah', 'penjelasanSelisihKas', 'rekonKartuPenyebabSelisih',
            'isAdmin', 'id', 'kka', 'suratIzin', 'statusSurat', 'catatanSurat', 'kodeUnit', 'kontrolMingguan',
            'evalKas', 'evalAtm', 'evalKartu', 'evalObs', 'evalStok', 'evalCetakan'
        ));
    }

    private function calculateSeverity($impact, $likelihood, $isCritical)
    {
        $impact = (int) $impact;
        $likelihood = (int) $likelihood;
        $score = $impact * $likelihood;
        
        $level = 'Rendah';
        if ($isCritical || $score >= 15) {
            $level = 'Kritis';
        } elseif ($score >= 10) {
            $level = 'Tinggi';
        } elseif ($score >= 5) {
            $level = 'Sedang';
        }

        return [
            'score' => $score,
            'level' => $level
        ];
    }

    public function submitAll(Request $request)
    {
        $request->validate([
            'no_arsip' => 'nullable|string',
            'posisi_tanggal_rekon' => 'nullable|date',
            'saldo_kas_olibs' => 'nullable|numeric',
            'cash_details' => 'nullable|array',
            'kas_details' => 'nullable|array',
            'atm_machines' => 'nullable|array',
            'obs_details' => 'nullable|array',
            'stok_details' => 'nullable|array',
            'kartu_atm_stock' => 'nullable|array',
            'kartu_atm_controls' => 'nullable|array',
            'barang_cetakan_details' => 'nullable|array',
            'barang_cetakan_controls' => 'nullable|array',
            'sub_tab' => 'nullable|string',

            // Unified evaluation block attributes
            'tgl_uji' => 'nullable|date',
            'penyebab_keterangan' => 'nullable|string',
            'impact' => 'nullable|integer',
            'likelihood' => 'nullable|integer',
            'is_critical' => 'nullable',
            'risk_score' => 'nullable|integer',
            'risk_level' => 'nullable|string',

            'kas_tgl_uji' => 'nullable|date',
            'kas_penyebab_keterangan' => 'nullable|string',
            'kas_impact' => 'nullable|integer',
            'kas_likelihood' => 'nullable|integer',
            'kas_is_critical' => 'nullable',
            'kas_risk_score' => 'nullable|integer',
            'kas_risk_level' => 'nullable|string',

            'atm_tgl_uji' => 'nullable|date',
            'atm_penyebab_keterangan' => 'nullable|string',
            'atm_impact' => 'nullable|integer',
            'atm_likelihood' => 'nullable|integer',
            'atm_is_critical' => 'nullable',
            'atm_risk_score' => 'nullable|integer',
            'atm_risk_level' => 'nullable|string',

            'kartu_tgl_uji' => 'nullable|date',
            'kartu_penyebab_keterangan' => 'nullable|string',
            'kartu_impact' => 'nullable|integer',
            'kartu_likelihood' => 'nullable|integer',
            'kartu_is_critical' => 'nullable',
            'kartu_risk_score' => 'nullable|integer',
            'kartu_risk_level' => 'nullable|string',

            'obs_tgl_uji' => 'nullable|date',
            'obs_penyebab_keterangan' => 'nullable|string',
            'obs_impact' => 'nullable|integer',
            'obs_likelihood' => 'nullable|integer',
            'obs_is_critical' => 'nullable',
            'obs_risk_score' => 'nullable|integer',
            'obs_risk_level' => 'nullable|string',

            'stok_tgl_uji' => 'nullable|date',
            'stok_penyebab_keterangan' => 'nullable|string',
            'stok_impact' => 'nullable|integer',
            'stok_likelihood' => 'nullable|integer',
            'stok_is_critical' => 'nullable',
            'stok_risk_score' => 'nullable|integer',
            'stok_risk_level' => 'nullable|string',

            'cetakan_tgl_uji' => 'nullable|date',
            'cetakan_penyebab_keterangan' => 'nullable|string',
            'cetakan_impact' => 'nullable|integer',
            'cetakan_likelihood' => 'nullable|integer',
            'cetakan_is_critical' => 'nullable',
            'cetakan_risk_score' => 'nullable|integer',
            'cetakan_risk_level' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $kodeUnit = '000';
            if ($user && $user->cabang_id) {
                $kcUnit = \App\Models\Unit::where('cabang_id', $user->cabang_id)
                    ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                    ->first();
                if ($kcUnit) {
                    $kodeUnit = $kcUnit->unit_code;
                } elseif ($user->cabang && $user->cabang->kode_cabang) {
                    $kodeUnit = str_replace('BS-', '', $user->cabang->kode_cabang);
                }
            } elseif ($user && !empty($user->unit_code)) {
                $kodeUnit = $user->unit_code;
            }
            
            $suratIzinPath = null;
            if ($request->hasFile('surat_izin')) {
                $file = $request->file('surat_izin');
                $filename = time() . '_' . $file->getClientOriginalName();
                $suratIzinPath = $file->storeAs('surat_izin', $filename, 'public');
            }

            $monday = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
            $friday = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays(4)->toDateString();

            $physicalKka = null;
            if ($request->filled('physical_kka_id')) {
                $physicalKka = PhysicalKka::find($request->physical_kka_id);
            }
            if (!$physicalKka) {
                $physicalKka = PhysicalKka::where(function($q) use ($kodeUnit) {
                    $clean = str_replace('BS-', '', $kodeUnit);
                    $q->where('kode_unit', $kodeUnit)
                      ->orWhere('kode_unit', $clean)
                      ->orWhere('kode_unit', 'BS-' . $clean);
                })
                ->whereBetween('tanggal_pemeriksaan', [$monday, $friday])
                ->latest('id')
                ->first();
            }

            // Determine active sub_tab
            $subTab = $request->input('sub_tab');
            if (!$subTab) {
                $presentTabs = [];
                if ($request->has('cash_details') || $request->has('kas_details') || $request->has('saldo_kas_olibs')) $presentTabs[] = 'kas';
                if ($request->has('atm_machines')) $presentTabs[] = 'atm';
                if ($request->has('obs_details')) $presentTabs[] = 'observasi';
                if ($request->has('stok_details')) $presentTabs[] = 'stok';
                if ($request->has('kartu_atm_stock') || $request->has('kartu_atm_controls')) $presentTabs[] = 'kartu';
                if ($request->has('barang_cetakan_details') || $request->has('barang_cetakan_controls')) $presentTabs[] = 'cetakan';

                if (count($presentTabs) === 1) {
                    $subTab = $presentTabs[0];
                } else {
                    $subTab = 'all';
                }
            }

            $noArsip = $request->filled('no_arsip') ? $request->no_arsip : ($physicalKka->no_arsip ?? ('ARSIP-KKA-' . $kodeUnit . '-' . Carbon::now()->format('Ymd') . '-001'));
            $posisiTanggalRekon = $request->filled('posisi_tanggal_rekon') ? $request->posisi_tanggal_rekon : ($physicalKka->posisi_tanggal_rekon ?? Carbon::now()->toDateString());

            if ($physicalKka) {
                $updateData = [
                    'status_final' => 'Final',
                    'no_arsip' => $noArsip,
                    'posisi_tanggal_rekon' => $posisiTanggalRekon,
                ];
                if ($subTab === 'kas' || $subTab === 'all' || $request->filled('saldo_kas_olibs')) {
                    $updateData['saldo_kas_olibs'] = (float)$request->input('saldo_kas_olibs', $physicalKka->saldo_kas_olibs);
                    if ($request->has('penjelasan_selisih_kas')) {
                        $updateData['penjelasan_selisih_kas'] = $request->penjelasan_selisih_kas;
                    }
                    if ($request->has('kas_pelaksana_nama')) {
                        $updateData['kas_pelaksana_nama'] = $request->kas_pelaksana_nama;
                    }
                    if ($request->has('kas_pelaksana_jabatan')) {
                        $updateData['kas_pelaksana_jabatan'] = $request->kas_pelaksana_jabatan;
                    }
                    if ($request->has('kas_saksi_nama')) {
                        $updateData['kas_saksi_nama'] = $request->kas_saksi_nama;
                    }
                    if ($request->has('kas_saksi_jabatan')) {
                        $updateData['kas_saksi_jabatan'] = $request->kas_saksi_jabatan;
                    }
                }
                if ($subTab === 'kartu' || $subTab === 'all' || $request->has('rekon_kartu_penyebab_selisih')) {
                    $updateData['rekon_kartu_penyebab_selisih'] = $request->input('rekon_kartu_penyebab_selisih', $physicalKka->rekon_kartu_penyebab_selisih);
                }
                if ($suratIzinPath) {
                    $updateData['surat_izin_path'] = $suratIzinPath;
                    $updateData['status_surat'] = 'Pending';
                }
                $physicalKka->update($updateData);

                // Only delete child records for the active tab to prevent wiping other tabs
                if ($subTab === 'kas' || $subTab === 'all') {
                    $cashIds = PhysicalKkaCashDetail::where('physical_kka_id', $physicalKka->id)->pluck('id');
                    if ($cashIds->isNotEmpty()) {
                        RegisterKlarifikasi::where('source_type', PhysicalKkaCashDetail::class)->whereIn('source_id', $cashIds)->delete();
                    }
                    PhysicalKkaCashDetail::where('physical_kka_id', $physicalKka->id)->delete();
                }

                if ($subTab === 'atm' || $subTab === 'all') {
                    $atmIds = $physicalKka->atmMachines->pluck('id');
                    if ($atmIds->isNotEmpty()) {
                        RegisterKlarifikasi::where('source_type', PhysicalKkaAtmMachine::class)->whereIn('source_id', $atmIds)->delete();
                    }
                    foreach ($physicalKka->atmMachines as $oldMach) {
                        $oldMach->cassettes()->delete();
                        $oldMach->delete();
                    }
                }

                if ($subTab === 'observasi' || $subTab === 'obs' || $subTab === 'all') {
                    $obsIds = PhysicalKkaObsDetail::where('physical_kka_id', $physicalKka->id)->pluck('id');
                    if ($obsIds->isNotEmpty()) {
                        RegisterKlarifikasi::where('source_type', PhysicalKkaObsDetail::class)->whereIn('source_id', $obsIds)->delete();
                    }
                    PhysicalKkaObsDetail::where('physical_kka_id', $physicalKka->id)->delete();
                }

                if ($subTab === 'stok' || $subTab === 'all') {
                    $stokIds = PhysicalKkaStokDetail::where('physical_kka_id', $physicalKka->id)->pluck('id');
                    if ($stokIds->isNotEmpty()) {
                        RegisterKlarifikasi::where('source_type', PhysicalKkaStokDetail::class)->whereIn('source_id', $stokIds)->delete();
                    }
                    PhysicalKkaStokDetail::where('physical_kka_id', $physicalKka->id)->delete();
                }

                if ($subTab === 'kartu' || $subTab === 'all') {
                    $kartuIds = PhysicalKkaKartuAtmDetail::where('physical_kka_id', $physicalKka->id)->pluck('id');
                    if ($kartuIds->isNotEmpty()) {
                        RegisterKlarifikasi::where('source_type', PhysicalKkaKartuAtmDetail::class)->whereIn('source_id', $kartuIds)->delete();
                    }
                    PhysicalKkaKartuAtmDetail::where('physical_kka_id', $physicalKka->id)->delete();
                }

                if ($subTab === 'cetakan' || $subTab === 'all') {
                    $bctIds = PhysicalKkaBarangCetakanDetail::where('physical_kka_id', $physicalKka->id)->pluck('id');
                    if ($bctIds->isNotEmpty()) {
                        RegisterKlarifikasi::where('source_type', PhysicalKkaBarangCetakanDetail::class)->whereIn('source_id', $bctIds)->delete();
                    }
                    PhysicalKkaBarangCetakanDetail::where('physical_kka_id', $physicalKka->id)->delete();
                }
            } else {
                $saldoKasOlibs = $request->filled('saldo_kas_olibs') ? (float)$request->saldo_kas_olibs : 0;
                $physicalKka = PhysicalKka::create([
                    'kode_unit' => $kodeUnit,
                    'user_id' => $user ? $user->id : 1, 
                    'tanggal_pemeriksaan' => Carbon::now()->toDateString(),
                    'status_final' => 'Final',
                    'no_arsip' => $noArsip,
                    'posisi_tanggal_rekon' => $posisiTanggalRekon,
                    'saldo_kas_olibs' => $saldoKasOlibs,
                    'penjelasan_selisih_kas' => $request->penjelasan_selisih_kas,
                    'kas_pelaksana_nama' => $request->kas_pelaksana_nama,
                    'kas_pelaksana_jabatan' => $request->kas_pelaksana_jabatan,
                    'kas_saksi_nama' => $request->kas_saksi_nama,
                    'kas_saksi_jabatan' => $request->kas_saksi_jabatan,
                    'rekon_kartu_penyebab_selisih' => $request->rekon_kartu_penyebab_selisih,
                    'surat_izin_path' => $suratIzinPath,
                    'status_surat' => 'Pending'
                ]);
            }

            $hasException = false;
            $dateCode = Carbon::now()->format('Ymd');

            // 1. PROCESS CASH KHASANAH
            if ($subTab === 'kas' || $subTab === 'all' || $request->has('cash_details') || $request->has('kas_details')) {
                $totalFisikKasKhasanah = 0;
                $cashDetailsInput = $request->input('cash_details') ?? $request->input('kas_details');
                if ($cashDetailsInput && is_array($cashDetailsInput)) {
                    foreach ($cashDetailsInput as $item) {
                        $lembar = isset($item['jumlah_lembar']) ? (int)$item['jumlah_lembar'] : 0;
                        $nominal = isset($item['nominal']) ? (float)$item['nominal'] : ((float)($item['hasil_fisik'] ?? 0));
                        $totalFisikKasKhasanah += $nominal;

                        $item['physical_kka_id'] = $physicalKka->id;
                        $item['sub_area'] = 'Khasanah';
                        $item['jumlah_lembar'] = $lembar;
                        $item['nominal'] = $nominal;
                        $item['hasil_fisik'] = $nominal;
                        $item['status_uji'] = 'Sesuai';
                        $item['is_exception'] = false;
                        $item['tanggal_uji'] = Carbon::now()->toDateString();

                        PhysicalKkaCashDetail::create($item);
                    }
                }

                $activeSaldoOlibs = (float)($request->input('saldo_kas_olibs', $physicalKka->saldo_kas_olibs ?? 0));
                $selisihKasKhasanah = $totalFisikKasKhasanah - $activeSaldoOlibs;
                $physicalKka->update([
                    'saldo_fisik_khasanah' => $totalFisikKasKhasanah,
                    'selisih_kas_khasanah' => $selisihKasKhasanah,
                ]);

                $kasTglUji = $request->input('kas_tgl_uji') ?? $request->input('tgl_uji') ?? Carbon::now()->toDateString();
                $kasPenyebab = trim($request->input('kas_penyebab_keterangan') ?? $request->input('penyebab_keterangan') ?? $request->input('penjelasan_selisih_kas') ?? '');

                if (abs($selisihKasKhasanah) >= 0.01) {
                    if (empty($kasPenyebab)) {
                        throw ValidationException::withMessages([
                            'penyebab_keterangan' => 'Penyebab & Keterangan wajib diisi apabila terdapat selisih fisik Kas Khasanah vs saldo sistem Olibs.',
                            'kas_penyebab_keterangan' => 'Penyebab & Keterangan wajib diisi apabila terdapat selisih fisik Kas Khasanah vs saldo sistem Olibs.',
                            'penjelasan_selisih_kas' => 'Uraian penjelasan selisih Kas Khasanah wajib diisi apabila terdapat selisih fisik vs saldo sistem Olibs.'
                        ]);
                    }
                    $hasException = true;
                    $firstCash = PhysicalKkaCashDetail::where('physical_kka_id', $physicalKka->id)->first();
                    if ($firstCash) {
                        $firstCash->update([
                            'is_exception' => true,
                            'status_uji' => 'Exception',
                            'selisih' => $selisihKasKhasanah,
                            'penyebab' => $kasPenyebab,
                        ]);
                        $this->createKlarifikasi($firstCash, $kodeUnit);
                    }
                }

                $evalKas = app(\App\Services\DailyRegister\AutoRiskScoringService::class)->evaluatePhysicalKka('kas', $kodeUnit, array_merge($request->all(), ['selisih_kas_khasanah' => $selisihKasKhasanah]), $physicalKka->id);
                $kasImpact = (int)$evalKas['impact'];
                $kasLikelihood = (int)$evalKas['likelihood'];
                $kasIsCritical = (bool)$evalKas['is_critical'];
                $kasRiskScore = (int)$evalKas['risk_score'];
                $kasRiskLevel = $evalKas['risk_level'];

                $physicalKka->update([
                    'penjelasan_selisih_kas' => $kasPenyebab,
                    'kas_tgl_uji' => $kasTglUji,
                    'kas_penyebab_keterangan' => $kasPenyebab,
                    'kas_impact' => $kasImpact,
                    'kas_likelihood' => $kasLikelihood,
                    'kas_is_critical' => $kasIsCritical,
                    'kas_risk_score' => $kasRiskScore,
                    'kas_risk_level' => $kasRiskLevel,
                    'tgl_uji' => $kasTglUji,
                    'penyebab_keterangan' => $kasPenyebab,
                    'impact' => $kasImpact,
                    'likelihood' => $kasLikelihood,
                    'is_critical' => $kasIsCritical,
                    'risk_score' => $kasRiskScore,
                    'risk_level' => $kasRiskLevel,
                ]);
            }

            // 2. PROCESS CASH OPNAME MESIN ATM
            if ($subTab === 'atm' || $subTab === 'all' || $request->has('atm_machines')) {
                $hasAtmAnyException = false;
                $atmPenyebab = trim($request->input('atm_penyebab_keterangan') ?? $request->input('penyebab_keterangan') ?? '');
                $atmTglUji = $request->input('atm_tgl_uji') ?? $request->input('tgl_uji') ?? Carbon::now()->toDateString();

                if ($request->has('atm_machines') && is_array($request->atm_machines)) {
                    foreach ($request->atm_machines as $mIdx => $atmData) {
                        if (empty($atmData['terminal_id']) && empty($atmData['lokasi'])) {
                            continue;
                        }

                        $saldoCbs = isset($atmData['saldo_cbs']) ? (float)$atmData['saldo_cbs'] : 0;
                        $totalFisikAtm = 0;

                        $cassettesData = $atmData['cassettes'] ?? [];
                        if (is_array($cassettesData)) {
                            foreach ($cassettesData as $c) {
                                $totalFisikAtm += (float)($c['total_nominal'] ?? 0);
                            }
                        }

                        $selisihAtm = $saldoCbs - $totalFisikAtm;
                        $isAtmException = (abs($selisihAtm) >= 0.01);
                        if ($isAtmException) {
                            $hasAtmAnyException = true;
                            $hasException = true;
                            if (empty($atmPenyebab) && !empty($atmData['keterangan_selisih'])) {
                                $atmPenyebab = trim($atmData['keterangan_selisih']);
                            }
                        }

                        $hasFilledCassette = collect($cassettesData)->some(fn($c) => (!empty($c['total_nominal']) && (float)$c['total_nominal'] > 0) || (!empty($c['jumlah_lembar']) && (int)$c['jumlah_lembar'] > 0));
                        $statusUji = $hasFilledCassette ? 'Selesai Diuji' : 'Belum Diuji';

                        $atmMachine = PhysicalKkaAtmMachine::create([
                            'physical_kka_id' => $physicalKka->id,
                            'terminal_id' => $atmData['terminal_id'] ?? 'ATM-' . rand(100, 999),
                            'lokasi' => $atmData['lokasi'] ?? null,
                            'merk' => $atmData['merk'] ?? null,
                            'denominasi' => $atmData['denominasi'] ?? 100000,
                            'tanggal_pengisian_terakhir' => !empty($atmData['tanggal_pengisian_terakhir']) ? $atmData['tanggal_pengisian_terakhir'] : null,
                            'saldo_cbs' => $saldoCbs,
                            'total_fisik' => $totalFisikAtm,
                            'selisih' => $selisihAtm,
                            'status_uji' => $statusUji,
                            'keterangan_selisih' => $atmData['keterangan_selisih'] ?? null,
                            'pelaksana_nama' => $atmData['pelaksana_nama'] ?? ($user ? $user->name : 'Resident Auditor'),
                            'pelaksana_jabatan' => $atmData['pelaksana_jabatan'] ?? 'Resident Auditor SKAI',
                            'saksi_nama' => $atmData['saksi_nama'] ?? 'Supervisor / Custody ATM',
                            'saksi_jabatan' => $atmData['saksi_jabatan'] ?? 'Pemegang Kunci ATM',
                            'is_exception' => $isAtmException,
                        ]);

                        if (is_array($cassettesData)) {
                            foreach ($cassettesData as $cas) {
                                if (empty($cas['nama_kaset']) && empty($cas['total_nominal'])) continue;
                                PhysicalKkaAtmCassette::create([
                                    'atm_machine_id' => $atmMachine->id,
                                    'nama_kaset' => $cas['nama_kaset'] ?? 'Cassette',
                                    'denominasi' => (float)($cas['denominasi'] ?? 100000),
                                    'jumlah_lembar' => (int)($cas['jumlah_lembar'] ?? 0),
                                    'total_nominal' => (float)($cas['total_nominal'] ?? 0),
                                ]);
                            }
                        }

                        if ($isAtmException) {
                            $this->createKlarifikasi($atmMachine, $kodeUnit);
                        }
                    }
                }

                if ($hasAtmAnyException && empty($atmPenyebab)) {
                    throw ValidationException::withMessages([
                        'penyebab_keterangan' => 'Penyebab & Keterangan wajib diisi apabila terdapat selisih fisik vs saldo CBS pada Cash Opname Mesin ATM.',
                        'atm_penyebab_keterangan' => 'Penyebab & Keterangan wajib diisi apabila terdapat selisih fisik vs saldo CBS pada Cash Opname Mesin ATM.',
                        'atm_machines.0.keterangan_selisih' => 'Uraian penyebab selisih kas ATM wajib diisi apabila terdapat selisih.',
                    ]);
                }

                $evalAtm = app(\App\Services\DailyRegister\AutoRiskScoringService::class)->evaluatePhysicalKka('atm', $kodeUnit, $request->all(), $physicalKka->id);
                $atmImpact = (int)$evalAtm['impact'];
                $atmLikelihood = (int)$evalAtm['likelihood'];
                $atmIsCritical = (bool)$evalAtm['is_critical'];
                $atmRiskScore = (int)$evalAtm['risk_score'];
                $atmRiskLevel = $evalAtm['risk_level'];

                $physicalKka->update([
                    'atm_tgl_uji' => $atmTglUji,
                    'atm_penyebab_keterangan' => $atmPenyebab,
                    'atm_impact' => $atmImpact,
                    'atm_likelihood' => $atmLikelihood,
                    'atm_is_critical' => $atmIsCritical,
                    'atm_risk_score' => $atmRiskScore,
                    'atm_risk_level' => $atmRiskLevel,
                ]);

                if ($subTab === 'atm') {
                    $physicalKka->update([
                        'tgl_uji' => $atmTglUji,
                        'penyebab_keterangan' => $atmPenyebab,
                        'impact' => $atmImpact,
                        'likelihood' => $atmLikelihood,
                        'is_critical' => $atmIsCritical,
                        'risk_score' => $atmRiskScore,
                        'risk_level' => $atmRiskLevel,
                    ]);
                }
            }

            // 3. PROCESS OBSERVASI DETAILS
            if ($subTab === 'observasi' || $subTab === 'obs' || $subTab === 'all' || $request->has('obs_details')) {
                $hasObsException = false;
                if ($request->has('obs_details') && is_array($request->obs_details)) {
                    foreach ($request->obs_details as $item) {
                        if (empty($item['hasil_observasi'])) continue;
                        if (in_array($item['hasil_observasi'], ['Tidak Sesuai', 'Tidak', 'Kurang Baik'], true)) {
                            $hasObsException = true;
                            $hasException = true;
                            break;
                        }
                    }
                }

                $obsTglUji = $request->input('obs_tgl_uji') ?? $request->input('tgl_uji') ?? Carbon::now()->toDateString();
                $obsPenyebab = trim($request->input('obs_penyebab_keterangan') ?? $request->input('penyebab_keterangan') ?? '');

                // Fallback: check if any item had per-row 'penyebab' in legacy payloads
                if (empty($obsPenyebab) && $request->has('obs_details') && is_array($request->obs_details)) {
                    foreach ($request->obs_details as $item) {
                        if (!empty($item['penyebab'])) {
                            $obsPenyebab = trim($item['penyebab']);
                            break;
                        }
                    }
                }

                if ($hasObsException && empty($obsPenyebab)) {
                    throw ValidationException::withMessages([
                        'penyebab_keterangan' => 'Penyebab & Keterangan wajib diisi apabila terdapat temuan atau item yang dijawab Tidak Sesuai pada Observasi Lingkungan.',
                        'obs_penyebab_keterangan' => 'Penyebab & Keterangan wajib diisi apabila terdapat temuan atau item yang dijawab Tidak Sesuai pada Observasi Lingkungan.',
                    ]);
                }

                $evalObs = app(\App\Services\DailyRegister\AutoRiskScoringService::class)->evaluatePhysicalKka('observasi', $kodeUnit, $request->all(), $physicalKka->id);
                $isExplicitObsRisk = !$request->boolean('use_auto_risk') && ($request->filled('obs_risk_score') || $request->filled('obs_risk_level'));
                $obsImpact = $isExplicitObsRisk && $request->filled('obs_impact') ? (int)$request->input('obs_impact') : (int)$evalObs['impact'];
                $obsLikelihood = $isExplicitObsRisk && $request->filled('obs_likelihood') ? (int)$request->input('obs_likelihood') : (int)$evalObs['likelihood'];
                $obsIsCritical = $isExplicitObsRisk && $request->has('obs_is_critical') ? in_array($request->input('obs_is_critical'), ['Ya', '1', 1, true, 'true'], true) : (bool)$evalObs['is_critical'];
                $obsRiskScore = $isExplicitObsRisk && $request->filled('obs_risk_score') ? (int)$request->input('obs_risk_score') : (int)$evalObs['risk_score'];
                $obsRiskLevel = $isExplicitObsRisk && $request->filled('obs_risk_level') ? $request->input('obs_risk_level') : $evalObs['risk_level'];

                $physicalKka->update([
                    'obs_tgl_uji' => $obsTglUji,
                    'obs_penyebab_keterangan' => $obsPenyebab,
                    'obs_impact' => $obsImpact,
                    'obs_likelihood' => $obsLikelihood,
                    'obs_is_critical' => $obsIsCritical,
                    'obs_risk_score' => $obsRiskScore,
                    'obs_risk_level' => $obsRiskLevel,
                ]);

                if ($subTab === 'observasi' || $subTab === 'obs') {
                    $physicalKka->update([
                        'tgl_uji' => $obsTglUji,
                        'penyebab_keterangan' => $obsPenyebab,
                        'impact' => $obsImpact,
                        'likelihood' => $obsLikelihood,
                        'is_critical' => $obsIsCritical,
                        'risk_score' => $obsRiskScore,
                        'risk_level' => $obsRiskLevel,
                    ]);
                }

                if ($request->has('obs_details') && is_array($request->obs_details)) {
                    foreach ($request->obs_details as $item) {
                        if (empty($item['hasil_observasi'])) continue;

                        $isException = in_array($item['hasil_observasi'], ['Tidak Sesuai', 'Tidak', 'Kurang Baik'], true);
                        $item['status_uji'] = $isException ? 'Exception' : 'Sesuai';
                        $item['is_exception'] = $isException;
                        $item['physical_kka_id'] = $physicalKka->id;

                        if ($isException) {
                            $itemImp = isset($item['impact']) ? (int)$item['impact'] : $obsImpact;
                            $itemLik = isset($item['likelihood']) ? (int)$item['likelihood'] : $obsLikelihood;
                            $itemCrit = isset($item['critical_trigger']) ? (in_array($item['critical_trigger'], ['Ya', '1', 1, true, 'true'], true)) : $obsIsCritical;
                            $itemSeverity = $this->calculateSeverity($itemImp, $itemLik, $itemCrit);
                            $item['risk_score'] = $item['risk_score'] ?? $itemSeverity['score'];
                            $item['severity_level'] = $item['severity_level'] ?? $itemSeverity['level'];
                            $item['penyebab'] = $item['penyebab'] ?? $obsPenyebab;
                        } else {
                            $item['risk_score'] = 0;
                            $item['severity_level'] = 'Rendah';
                        }
                        $item['tanggal_uji'] = $item['tanggal_uji'] ?? $obsTglUji;

                        $detail = PhysicalKkaObsDetail::create($item);
                        if ($isException) $this->createKlarifikasi($detail, $kodeUnit);
                    }
                }
            }

            // 4. PROCESS STOK OPNAME DETAILS
            if ($subTab === 'stok' || $subTab === 'all' || $request->has('stok_details')) {
                $hasStokException = false;
                if ($request->has('stok_details') && is_array($request->stok_details)) {
                    foreach ($request->stok_details as $item) {
                        $isCtrl = (($item['sub_area'] ?? '') === 'Kontrol');
                        if ($isCtrl) {
                            if (($item['status_uji'] ?? '') === 'Tidak') {
                                $hasStokException = true;
                                $hasException = true;
                                break;
                            }
                        } else {
                            if (!isset($item['hasil_fisik']) || $item['hasil_fisik'] === '') continue;
                            $saldoSistem = isset($item['saldo_sistem']) ? (float)$item['saldo_sistem'] : 0;
                            $hasilFisik = (float)$item['hasil_fisik'];
                            if (abs($saldoSistem - $hasilFisik) >= 0.01) {
                                $hasStokException = true;
                                $hasException = true;
                                break;
                            }
                        }
                    }
                }

                $stokTglUji = $request->input('stok_tgl_uji') ?? $request->input('tgl_uji') ?? Carbon::now()->toDateString();
                $stokPenyebab = trim($request->input('stok_penyebab_keterangan') ?? $request->input('penyebab_keterangan') ?? '');

                if (empty($stokPenyebab) && $request->has('stok_details') && is_array($request->stok_details)) {
                    foreach ($request->stok_details as $item) {
                        if (!empty($item['penyebab'])) {
                            $stokPenyebab = trim($item['penyebab']);
                            break;
                        }
                    }
                }

                if ($hasStokException && empty($stokPenyebab)) {
                    throw ValidationException::withMessages([
                        'penyebab_keterangan' => 'Penyebab & Keterangan wajib diisi apabila terdapat selisih fisik vs saldo sistem atau butir kontrol dijawab Tidak pada Rekonsiliasi Stok Cek / BG.',
                        'stok_penyebab_keterangan' => 'Penyebab & Keterangan wajib diisi apabila terdapat selisih fisik vs saldo sistem atau butir kontrol dijawab Tidak pada Rekonsiliasi Stok Cek / BG.',
                    ]);
                }

                $evalStok = app(\App\Services\DailyRegister\AutoRiskScoringService::class)->evaluatePhysicalKka('stok', $kodeUnit, $request->all(), $physicalKka->id);
                $isExplicitStokRisk = !$request->boolean('use_auto_risk') && ($request->filled('stok_risk_score') || $request->filled('stok_risk_level'));
                $stokImpact = $isExplicitStokRisk && $request->filled('stok_impact') ? (int)$request->input('stok_impact') : (int)$evalStok['impact'];
                $stokLikelihood = $isExplicitStokRisk && $request->filled('stok_likelihood') ? (int)$request->input('stok_likelihood') : (int)$evalStok['likelihood'];
                $stokIsCritical = $isExplicitStokRisk && $request->has('stok_is_critical') ? in_array($request->input('stok_is_critical'), ['Ya', '1', 1, true, 'true'], true) : (bool)$evalStok['is_critical'];
                $stokRiskScore = $isExplicitStokRisk && $request->filled('stok_risk_score') ? (int)$request->input('stok_risk_score') : (int)$evalStok['risk_score'];
                $stokRiskLevel = $isExplicitStokRisk && $request->filled('stok_risk_level') ? $request->input('stok_risk_level') : $evalStok['risk_level'];

                $physicalKka->update([
                    'stok_tgl_uji' => $stokTglUji,
                    'stok_penyebab_keterangan' => $stokPenyebab,
                    'stok_impact' => $stokImpact,
                    'stok_likelihood' => $stokLikelihood,
                    'stok_is_critical' => $stokIsCritical,
                    'stok_risk_score' => $stokRiskScore,
                    'stok_risk_level' => $stokRiskLevel,
                ]);

                if ($subTab === 'stok') {
                    $physicalKka->update([
                        'tgl_uji' => $stokTglUji,
                        'penyebab_keterangan' => $stokPenyebab,
                        'impact' => $stokImpact,
                        'likelihood' => $stokLikelihood,
                        'is_critical' => $stokIsCritical,
                        'risk_score' => $stokRiskScore,
                        'risk_level' => $stokRiskLevel,
                    ]);
                }

                if ($request->has('stok_details') && is_array($request->stok_details)) {
                    foreach ($request->stok_details as $item) {
                        $isCtrl = (($item['sub_area'] ?? '') === 'Kontrol');
                        if (!$isCtrl && (!isset($item['hasil_fisik']) || $item['hasil_fisik'] === '')) continue;

                        $saldoSistem = isset($item['saldo_sistem']) ? (float)$item['saldo_sistem'] : 0;
                        $hasilFisik = (float)($item['hasil_fisik'] ?? 0);
                        $selisih = $isCtrl ? 0 : ($saldoSistem - $hasilFisik);

                        if ($isCtrl) {
                            $isException = (($item['status_uji'] ?? '') === 'Tidak');
                            $item['status_uji'] = $isException ? 'Tidak' : 'Ya';
                        } else {
                            $isException = (abs($selisih) >= 0.01);
                            $item['status_uji'] = $isException ? 'Exception' : 'Sesuai';
                        }
                        
                        $item['saldo_sistem'] = $saldoSistem;
                        $item['hasil_fisik'] = $hasilFisik;
                        $item['selisih'] = $selisih;
                        $item['is_exception'] = $isException;
                        $item['physical_kka_id'] = $physicalKka->id;

                        if ($isException) {
                            $itemImp = isset($item['impact']) ? (int)$item['impact'] : $stokImpact;
                            $itemLik = isset($item['likelihood']) ? (int)$item['likelihood'] : $stokLikelihood;
                            $itemCrit = isset($item['critical_trigger']) ? (in_array($item['critical_trigger'], ['Ya', '1', 1, true, 'true'], true)) : $stokIsCritical;
                            $itemSeverity = $this->calculateSeverity($itemImp, $itemLik, $itemCrit);
                            $item['risk_score'] = $item['risk_score'] ?? $itemSeverity['score'];
                            $item['severity_level'] = $item['severity_level'] ?? $itemSeverity['level'];
                            $item['penyebab'] = $item['penyebab'] ?? $stokPenyebab;
                        } else {
                            $item['risk_score'] = 0;
                            $item['severity_level'] = 'Rendah';
                        }
                        $item['tanggal_uji'] = $item['tanggal_uji'] ?? $stokTglUji;

                        $detail = PhysicalKkaStokDetail::create($item);
                        if ($isException) $this->createKlarifikasi($detail, $kodeUnit);
                    }
                }
            }

            // 5. PROCESS REKONSILIASI KARTU ATM (SECTION A & SECTION B)
            if ($subTab === 'kartu' || $subTab === 'all' || $request->has('kartu_atm_stock') || $request->has('kartu_atm_controls')) {
                $noUrutAtm = 1;
                if ($request->has('kartu_atm_stock') && is_array($request->kartu_atm_stock)) {
                    foreach ($request->kartu_atm_stock as $sRow) {
                        if (empty($sRow['jenis_kartu'])) continue;

                        $hargaModal = (float)($sRow['harga_modal'] ?? 0);
                        $qtyKosong = (int)($sRow['qty_fisik_kosong'] ?? 0);
                        $qtyRusak = (int)($sRow['qty_fisik_rusak'] ?? 0);
                        $nomFisik = $hargaModal * $qtyKosong;
                        $saldoBukuQty = (int)($sRow['saldo_buku_qty'] ?? 0);
                        $saldoBukuNom = (float)($sRow['saldo_buku_nominal'] ?? ($saldoBukuQty * $hargaModal));
                        $selisihPersediaan = $nomFisik - $saldoBukuNom;
                        $pinQty = (int)($sRow['pin_mailer_qty'] ?? 0);
                        $selisihPin = $qtyKosong - $pinQty;

                        $isException = (abs($selisihPersediaan) >= 0.01 || $selisihPin !== 0);
                        if ($isException) {
                            $findings = trim($request->rekon_kartu_penyebab_selisih ?? '');
                            if (empty($findings) || $findings === 'Tidak terdapat selisih antara fisik kartu ATM, saldo persediaan, dan PIN Mailer') {
                                throw ValidationException::withMessages([
                                    'rekon_kartu_penyebab_selisih' => 'Penyebab selisih / findings pada Rekonsiliasi Kartu ATM wajib diisi apabila terdapat selisih fisik, saldo persediaan, atau PIN Mailer.'
                                ]);
                            }
                            $hasException = true;
                        }

                        $detail = PhysicalKkaKartuAtmDetail::create([
                            'physical_kka_id' => $physicalKka->id,
                            'procedure_id' => 'FLD-WK-' . $kodeUnit . '-' . $dateCode . '-KATREC-' . str_pad($noUrutAtm++, 3, '0', STR_PAD_LEFT),
                            'sub_area' => 'Persediaan Kartu ATM',
                            'jenis_kartu' => $sRow['jenis_kartu'],
                            'keterangan_lokasi' => $sRow['keterangan_lokasi'] ?? 'Brankas Khasanah',
                            'saldo_nominatif' => (string)$saldoBukuQty,
                            'hasil_fisik' => (string)$qtyKosong,
                            'selisih' => (string)$selisihPersediaan,
                            'status_uji' => $isException ? 'EXCEPTION' : 'SESUAI',
                            'kondisi' => 'Baik',
                            'harga_modal' => $hargaModal,
                            'qty_fisik_kosong' => $qtyKosong,
                            'qty_fisik_rusak' => $qtyRusak,
                            'nominal_fisik' => $nomFisik,
                            'saldo_buku_qty' => $saldoBukuQty,
                            'saldo_buku_nominal' => $saldoBukuNom,
                            'selisih_persediaan' => $selisihPersediaan,
                            'pin_mailer_qty' => $pinQty,
                            'selisih_pin_mailer' => $selisihPin,
                            'is_exception' => $isException,
                            'tanggal_uji' => Carbon::now()->toDateString(),
                            'ra_pelaksana' => $user ? $user->name : 'Resident Auditor',
                        ]);

                        if ($isException) $this->createKlarifikasi($detail, $kodeUnit);
                    }
                }

                // Section B: 8 Item Checklist Kontrol Pengelolaan
                if ($request->has('kartu_atm_controls') && is_array($request->kartu_atm_controls)) {
                    foreach ($request->kartu_atm_controls as $cRow) {
                        if (empty($cRow['jenis_kartu'])) continue;

                        $jawaban = $cRow['jawaban_kontrol'] ?? 'Ya';
                        $isException = ($jawaban === 'Tidak');
                        if ($isException) $hasException = true;

                        $detail = PhysicalKkaKartuAtmDetail::create([
                            'physical_kka_id' => $physicalKka->id,
                            'procedure_id' => 'FLD-WK-' . $kodeUnit . '-' . $dateCode . '-KATCTRL-' . str_pad($noUrutAtm++, 3, '0', STR_PAD_LEFT),
                            'sub_area' => 'Kontrol',
                            'jenis_kartu' => $cRow['jenis_kartu'],
                            'jawaban_kontrol' => $jawaban,
                            'catatan_kontrol' => $cRow['catatan_kontrol'] ?? null,
                            'status_uji' => $isException ? 'EXCEPTION' : 'SESUAI',
                            'kondisi' => $jawaban === 'Ya' ? 'Sesuai' : 'Tidak Sesuai',
                            'saldo_nominatif' => 'N/A',
                            'hasil_fisik' => $jawaban,
                            'selisih' => 'N/A',
                            'is_exception' => $isException,
                            'tanggal_uji' => Carbon::now()->toDateString(),
                            'ra_pelaksana' => $user ? $user->name : 'Resident Auditor',
                        ]);

                        if ($isException) $this->createKlarifikasi($detail, $kodeUnit);
                    }
                }

                $kartuTglUji = $request->input('kartu_tgl_uji') ?? $request->input('tgl_uji') ?? Carbon::now()->toDateString();
                $kartuPenyebab = trim($request->input('kartu_penyebab_keterangan') ?? $request->input('penyebab_keterangan') ?? $request->input('rekon_kartu_penyebab_selisih') ?? '');

                $hasKartuExc = false;
                if ($request->has('kartu_atm_stock') && is_array($request->kartu_atm_stock)) {
                    foreach ($request->kartu_atm_stock as $sRow) {
                        $hargaModal = (float)($sRow['harga_modal'] ?? 0);
                        $qtyKosong = (int)($sRow['qty_fisik_kosong'] ?? 0);
                        $nomFisik = $hargaModal * $qtyKosong;
                        $saldoBukuQty = (int)($sRow['saldo_buku_qty'] ?? 0);
                        $saldoBukuNom = (float)($sRow['saldo_buku_nominal'] ?? ($saldoBukuQty * $hargaModal));
                        $selPers = $nomFisik - $saldoBukuNom;
                        $pinQty = (int)($sRow['pin_mailer_qty'] ?? 0);
                        $selPin = $qtyKosong - $pinQty;
                        if (abs($selPers) >= 0.01 || $selPin !== 0) {
                            $hasKartuExc = true;
                            break;
                        }
                    }
                }
                if (!$hasKartuExc && $request->has('kartu_atm_controls') && is_array($request->kartu_atm_controls)) {
                    foreach ($request->kartu_atm_controls as $cRow) {
                        if (($cRow['jawaban_kontrol'] ?? 'Ya') === 'Tidak') {
                            $hasKartuExc = true;
                            break;
                        }
                    }
                }

                if ($hasKartuExc && (empty($kartuPenyebab) || $kartuPenyebab === 'Tidak terdapat selisih antara fisik kartu ATM, saldo persediaan, dan PIN Mailer')) {
                    throw ValidationException::withMessages([
                        'penyebab_keterangan' => 'Penyebab & Keterangan wajib diisi apabila terdapat selisih fisik kartu, saldo persediaan, PIN Mailer, atau butir kontrol dijawab Tidak.',
                        'kartu_penyebab_keterangan' => 'Penyebab & Keterangan wajib diisi apabila terdapat selisih fisik kartu, saldo persediaan, PIN Mailer, atau butir kontrol dijawab Tidak.',
                        'rekon_kartu_penyebab_selisih' => 'Penyebab selisih / findings pada Rekonsiliasi Kartu ATM wajib diisi apabila terdapat selisih fisik, saldo persediaan, atau PIN Mailer.'
                    ]);
                }

                $evalKartu = app(\App\Services\DailyRegister\AutoRiskScoringService::class)->evaluatePhysicalKka('kartu', $kodeUnit, $request->all(), $physicalKka->id);
                $kartuImpact = (int)$evalKartu['impact'];
                $kartuLikelihood = (int)$evalKartu['likelihood'];
                $kartuIsCritical = (bool)$evalKartu['is_critical'];
                $kartuRiskScore = (int)$evalKartu['risk_score'];
                $kartuRiskLevel = $evalKartu['risk_level'];

                $physicalKka->update([
                    'rekon_kartu_penyebab_selisih' => $kartuPenyebab ?: $physicalKka->rekon_kartu_penyebab_selisih,
                    'kartu_tgl_uji' => $kartuTglUji,
                    'kartu_penyebab_keterangan' => $kartuPenyebab,
                    'kartu_impact' => $kartuImpact,
                    'kartu_likelihood' => $kartuLikelihood,
                    'kartu_is_critical' => $kartuIsCritical,
                    'kartu_risk_score' => $kartuRiskScore,
                    'kartu_risk_level' => $kartuRiskLevel,
                ]);

                if ($subTab === 'kartu') {
                    $physicalKka->update([
                        'tgl_uji' => $kartuTglUji,
                        'penyebab_keterangan' => $kartuPenyebab,
                        'impact' => $kartuImpact,
                        'likelihood' => $kartuLikelihood,
                        'is_critical' => $kartuIsCritical,
                        'risk_score' => $kartuRiskScore,
                        'risk_level' => $kartuRiskLevel,
                    ]);
                }
            }

            // 6. PROCESS REKONSILIASI BARANG CETAKAN
            if ($subTab === 'cetakan' || $subTab === 'all' || $request->has('barang_cetakan_details') || $request->has('barang_cetakan_controls')) {
                $hasBctException = false;
                if ($request->has('barang_cetakan_details') && is_array($request->barang_cetakan_details)) {
                    foreach ($request->barang_cetakan_details as $bRow) {
                        if (empty($bRow['nama_barang']) && empty($bRow['kriteria'])) continue;
                        $stokSistem = isset($bRow['stok_sistem']) ? (float)$bRow['stok_sistem'] : 0;
                        $hasilFisik = isset($bRow['hasil_fisik']) && $bRow['hasil_fisik'] !== '' ? (float)$bRow['hasil_fisik'] : 0;
                        if (abs($hasilFisik - $stokSistem) >= 0.01) {
                            $hasBctException = true;
                            $hasException = true;
                            break;
                        }
                    }
                }
                if (!$hasBctException && $request->has('barang_cetakan_controls') && is_array($request->barang_cetakan_controls)) {
                    foreach ($request->barang_cetakan_controls as $bcRow) {
                        if (empty($bcRow['butir'])) continue;
                        if (($bcRow['jawaban'] ?? 'Ya') === 'Tidak') {
                            $hasBctException = true;
                            $hasException = true;
                            break;
                        }
                    }
                }

                $cetakanTglUji = $request->input('cetakan_tgl_uji') ?? $request->input('tgl_uji') ?? Carbon::now()->toDateString();
                $cetakanPenyebab = trim($request->input('cetakan_penyebab_keterangan') ?? $request->input('penyebab_keterangan') ?? '');

                // Fallback: check if any item had per-row 'penyebab' in legacy payloads
                if (empty($cetakanPenyebab) && $request->has('barang_cetakan_details') && is_array($request->barang_cetakan_details)) {
                    foreach ($request->barang_cetakan_details as $bRow) {
                        if (!empty($bRow['penyebab'])) {
                            $cetakanPenyebab = trim($bRow['penyebab']);
                            break;
                        }
                    }
                }

                if ($hasBctException && empty($cetakanPenyebab)) {
                    throw ValidationException::withMessages([
                        'penyebab_keterangan' => 'Penyebab & Keterangan wajib diisi apabila terdapat selisih fisik atau butir kontrol bernilai Tidak pada Rekonsiliasi Barang Cetakan.',
                        'cetakan_penyebab_keterangan' => 'Penyebab & Keterangan wajib diisi apabila terdapat selisih fisik atau butir kontrol bernilai Tidak pada Rekonsiliasi Barang Cetakan.',
                    ]);
                }

                $evalCetakan = app(\App\Services\DailyRegister\AutoRiskScoringService::class)->evaluatePhysicalKka('cetakan', $kodeUnit, $request->all(), $physicalKka->id);
                $isExplicitCetakanRisk = !$request->boolean('use_auto_risk') && ($request->filled('cetakan_risk_score') || $request->filled('cetakan_risk_level'));
                $cetakanImpact = $isExplicitCetakanRisk && $request->filled('cetakan_impact') ? (int)$request->input('cetakan_impact') : (int)$evalCetakan['impact'];
                $cetakanLikelihood = $isExplicitCetakanRisk && $request->filled('cetakan_likelihood') ? (int)$request->input('cetakan_likelihood') : (int)$evalCetakan['likelihood'];
                $cetakanIsCritical = $isExplicitCetakanRisk && $request->has('cetakan_is_critical') ? in_array($request->input('cetakan_is_critical'), ['Ya', '1', 1, true, 'true'], true) : (bool)$evalCetakan['is_critical'];
                $cetakanRiskScore = $isExplicitCetakanRisk && $request->filled('cetakan_risk_score') ? (int)$request->input('cetakan_risk_score') : (int)$evalCetakan['risk_score'];
                $cetakanRiskLevel = $isExplicitCetakanRisk && $request->filled('cetakan_risk_level') ? $request->input('cetakan_risk_level') : $evalCetakan['risk_level'];

                $physicalKka->update([
                    'cetakan_tgl_uji' => $cetakanTglUji,
                    'cetakan_penyebab_keterangan' => $cetakanPenyebab,
                    'cetakan_impact' => $cetakanImpact,
                    'cetakan_likelihood' => $cetakanLikelihood,
                    'cetakan_is_critical' => $cetakanIsCritical,
                    'cetakan_risk_score' => $cetakanRiskScore,
                    'cetakan_risk_level' => $cetakanRiskLevel,
                ]);

                if ($subTab === 'cetakan') {
                    $physicalKka->update([
                        'tgl_uji' => $cetakanTglUji,
                        'penyebab_keterangan' => $cetakanPenyebab,
                        'impact' => $cetakanImpact,
                        'likelihood' => $cetakanLikelihood,
                        'is_critical' => $cetakanIsCritical,
                        'risk_score' => $cetakanRiskScore,
                        'risk_level' => $cetakanRiskLevel,
                    ]);
                }

                $noUrutBct = 1;
                if ($request->has('barang_cetakan_details') && is_array($request->barang_cetakan_details)) {
                    foreach ($request->barang_cetakan_details as $bRow) {
                        if (empty($bRow['nama_barang']) && empty($bRow['kriteria'])) continue;

                        $stokSistem = isset($bRow['stok_sistem']) ? (float)$bRow['stok_sistem'] : 0;
                        $hasilFisik = isset($bRow['hasil_fisik']) && $bRow['hasil_fisik'] !== '' ? (float)$bRow['hasil_fisik'] : 0;
                        $selisih = $hasilFisik - $stokSistem;
                        $isException = (abs($selisih) >= 0.01);

                        $detail = PhysicalKkaBarangCetakanDetail::create([
                            'physical_kka_id' => $physicalKka->id,
                            'procedure_id' => 'FLD-WK-' . $kodeUnit . '-' . $dateCode . '-BCT-' . str_pad($noUrutBct++, 3, '0', STR_PAD_LEFT),
                            'kategori' => $bRow['kategori'] ?? 'Formulir',
                            'nama_barang' => $bRow['nama_barang'],
                            'kriteria' => $bRow['kriteria'] ?? $bRow['nama_barang'],
                            'satuan' => $bRow['satuan'] ?? 'Bh',
                            'stok_sistem' => $stokSistem,
                            'hasil_fisik' => $hasilFisik,
                            'selisih' => $selisih,
                            'status_uji' => $isException ? 'EXCEPTION' : 'SESUAI',
                            'kondisi' => 'Baik',
                            'penyebab' => $bRow['penyebab'] ?? ($isException ? $cetakanPenyebab : null),
                            'impact' => $bRow['impact'] ?? $cetakanImpact,
                            'likelihood' => $bRow['likelihood'] ?? $cetakanLikelihood,
                            'critical_trigger' => !empty($bRow['critical_trigger']) && $bRow['critical_trigger'] == 'Ya' ? 'Ya' : ($cetakanIsCritical ? 'Ya' : 'Tidak'),
                            'risk_score' => $isException ? ($bRow['risk_score'] ?? $cetakanRiskScore) : 0,
                            'severity_level' => $isException ? ($bRow['severity_level'] ?? $cetakanRiskLevel) : 'Rendah',
                            'is_exception' => $isException,
                            'tanggal_uji' => $bRow['tanggal_uji'] ?? $cetakanTglUji,
                            'ra_pelaksana' => $user ? $user->name : 'Resident Auditor',
                        ]);

                        if ($isException) $this->createKlarifikasi($detail, $kodeUnit);
                    }
                }

                // 4 Butir Kontrol Barang Cetakan (Checklist Ya / Tidak)
                if ($request->has('barang_cetakan_controls') && is_array($request->barang_cetakan_controls)) {
                    foreach ($request->barang_cetakan_controls as $bcRow) {
                        if (empty($bcRow['butir'])) continue;

                        $jawaban = $bcRow['jawaban'] ?? 'Ya';
                        $isException = ($jawaban === 'Tidak');

                        $detail = PhysicalKkaBarangCetakanDetail::create([
                            'physical_kka_id' => $physicalKka->id,
                            'procedure_id' => 'FLD-WK-' . $kodeUnit . '-' . $dateCode . '-BCTCTRL-' . str_pad($noUrutBct++, 3, '0', STR_PAD_LEFT),
                            'kategori' => 'Kontrol',
                            'nama_barang' => $bcRow['butir'],
                            'kriteria' => 'Pengujian Pengendalian Internal Barang Cetakan',
                            'satuan' => 'Uji',
                            'stok_sistem' => 0,
                            'hasil_fisik' => 0,
                            'selisih' => 0,
                            'status_uji' => $isException ? 'EXCEPTION' : 'SESUAI',
                            'kondisi' => $jawaban === 'Ya' ? 'Sesuai' : 'Tidak Sesuai',
                            'jawaban_kontrol' => $jawaban,
                            'catatan_kontrol' => $bcRow['catatan'] ?? null,
                            'penyebab' => $isException ? $cetakanPenyebab : null,
                            'impact' => $cetakanImpact,
                            'likelihood' => $cetakanLikelihood,
                            'critical_trigger' => $cetakanIsCritical ? 'Ya' : 'Tidak',
                            'risk_score' => $isException ? $cetakanRiskScore : 0,
                            'severity_level' => $isException ? $cetakanRiskLevel : 'Rendah',
                            'is_exception' => $isException,
                            'tanggal_uji' => $cetakanTglUji,
                            'ra_pelaksana' => $user ? $user->name : 'Resident Auditor',
                        ]);

                        if ($isException) $this->createKlarifikasi($detail, $kodeUnit);
                    }
                }
            }

            // Evaluasi ulang status Kontrol Mingguan minggu ini
            KontrolMingguan::evaluateWeeklyStatuses(
                KontrolMingguan::getOrCreateForUnit($kodeUnit),
                $kodeUnit,
                $monday,
                $friday
            );

            DB::commit();

            $targetTab = match($subTab) {
                'kas' => 'cash',
                'atm' => 'cash-atm',
                'observasi', 'obs' => 'obs',
                'stok' => 'stok',
                'kartu' => 'atm',
                'cetakan' => 'cetakan',
                default => 'cash',
            };
            
            $msg = $hasException 
                ? "KKA Fisik & Rekonsiliasi berhasil disimpan dan masuk ke arsip siap cetak (No. Arsip: {$noArsip}). Terdapat Exception yang telah dimasukkan ke Register Klarifikasi." 
                : "KKA Fisik & Rekonsiliasi berhasil disimpan dan masuk ke arsip siap cetak (No. Arsip: {$noArsip}) tanpa Exception.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => $msg,
                    'id' => $physicalKka->id,
                    'tab' => $targetTab,
                ]);
            }
                
            return redirect()->route('daily-register.physical-kka.index', ['id' => $physicalKka->id, 'tab' => $targetTab])->with('success', $msg);

        } catch (ValidationException $ve) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $ve->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($ve->validator)->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function submitReview(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $reviewerId = $user ? $user->name : 'Admin Reviewer';
            $isSuratRevisi = $request->status_surat === 'Tidak Valid/Revisi';

            if ($request->has('physical_kka_id')) {
                $kka = PhysicalKka::find($request->physical_kka_id);
                if ($kka) {
                    $kka->status_surat = $request->status_surat;
                    $kka->catatan_surat = $request->catatan_surat;
                    $kka->save();
                }
            }

            if ($request->has('cash_review')) {
                foreach ($request->cash_review as $id => $review) {
                    $detail = PhysicalKkaCashDetail::find($id);
                    if ($detail) {
                        $detail->reviewer_id = $reviewerId;
                        $detail->status_review = $isSuratRevisi ? 'Revisi' : ($review['status_review'] ?? 'Pending');
                        $detail->catatan_reviewer = $review['catatan_reviewer'] ?? null;
                        $detail->save();
                    }
                }
            }
            
            if ($request->has('obs_review')) {
                foreach ($request->obs_review as $id => $review) {
                    $detail = PhysicalKkaObsDetail::find($id);
                    if ($detail) {
                        $detail->reviewer_id = $reviewerId;
                        $detail->status_review = $isSuratRevisi ? 'Revisi' : ($review['status_review'] ?? 'Pending');
                        $detail->catatan_reviewer = $review['catatan_reviewer'] ?? null;
                        $detail->save();
                    }
                }
            }

            if ($request->has('stok_review')) {
                foreach ($request->stok_review as $id => $review) {
                    $detail = PhysicalKkaStokDetail::find($id);
                    if ($detail) {
                        $detail->reviewer_id = $reviewerId;
                        $detail->status_review = $isSuratRevisi ? 'Revisi' : ($review['status_review'] ?? 'Pending');
                        $detail->catatan_reviewer = $review['catatan_reviewer'] ?? null;
                        $detail->save();
                    }
                }
            }

            if ($request->has('kartu_atm_review')) {
                foreach ($request->kartu_atm_review as $id => $review) {
                    $detail = PhysicalKkaKartuAtmDetail::find($id);
                    if ($detail) {
                        $detail->reviewer_id = $reviewerId;
                        $detail->status_review = $isSuratRevisi ? 'Dikembalikan' : ($review['status_review'] ?? 'Belum Direviu');
                        $detail->validasi = $review['validasi'] ?? null;
                        $isValid = in_array(strtoupper(trim($detail->validasi ?? '')), ['VALID', 'DISETUJUI']);
                        $detail->siap_register = ($detail->is_exception && $isValid) ? 'Ya' : 'Tidak';
                        $detail->save();
                    }
                }
            }

            if ($request->has('barang_cetakan_review')) {
                foreach ($request->barang_cetakan_review as $id => $review) {
                    $detail = PhysicalKkaBarangCetakanDetail::find($id);
                    if ($detail) {
                        $detail->reviewer_id = $reviewerId;
                        $detail->status_review = $isSuratRevisi ? 'Dikembalikan' : ($review['status_review'] ?? 'Belum Direviu');
                        $detail->validasi = $review['validasi'] ?? null;
                        $isValid = in_array(strtoupper(trim($detail->validasi ?? '')), ['VALID', 'DISETUJUI']);
                        $detail->siap_register = ($detail->is_exception && $isValid) ? 'Ya' : 'Tidak';
                        $detail->save();
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Review KKA Fisik berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan review: ' . $e->getMessage());
        }
    }

    private function createKlarifikasi($detailRecord, $kodeUnit)
    {
        RegisterKlarifikasi::create([
            'source_id' => $detailRecord->id,
            'source_type' => get_class($detailRecord),
            'kode_unit' => $kodeUnit,
            'status' => 'Menunggu Klarifikasi',
        ]);
    }

    // =========================================================================
    // PRINT METHODS FOR TAHAP 2 (BERITA ACARA & KKA STANDAR RESMI BANK SULTENG)
    // =========================================================================
    public function printBaKas($id)
    {
        $kka = PhysicalKka::with(['user', 'cashDetails'])->findOrFail($id);
        $cabang = Cabang::where('kode_cabang', 'like', '%' . $kka->kode_unit . '%')->first();
        $namaCabang = $cabang ? $cabang->nama_cabang : ('Cabang ' . $kka->kode_unit);

        $ulePecahan = ['Rp 100.000', 'Rp 75.000', 'Rp 50.000', 'Rp 20.000', 'Rp 10.000', 'Rp 5.000', 'Rp 2.000', 'Rp 1.000'];
        $logamPecahan = ['Rp 1.000', 'Rp 500', 'Rp 200', 'Rp 100', 'Rp 50', 'Rp 25'];
        $utlePecahan = ['Rp 100.000', 'Rp 75.000', 'Rp 50.000', 'Rp 20.000', 'Rp 10.000', 'Rp 5.000', 'Rp 2.000', 'Rp 1.000'];

        $uleDetails = [];
        foreach ($ulePecahan as $pec) {
            $item = $kka->cashDetails->first(fn($d) => ($d->kelompok === 'ULE' || str_contains((string)($d->objek_persediaan ?? ''), 'Layak Edar')) && $d->pecahan === $pec);
            $uleDetails[] = [
                'pecahan' => $pec,
                'jumlah_lembar' => $item ? $item->jumlah_lembar : 0,
                'nominal' => $item ? ($item->nominal ?: $item->hasil_fisik) : 0,
            ];
        }

        $logamDetails = [];
        foreach ($logamPecahan as $pec) {
            $item = $kka->cashDetails->first(fn($d) => ($d->kelompok === 'Logam' || str_contains((string)($d->objek_persediaan ?? ''), 'Logam')) && $d->pecahan === $pec);
            $logamDetails[] = [
                'pecahan' => $pec,
                'jumlah_lembar' => $item ? $item->jumlah_lembar : 0,
                'nominal' => $item ? ($item->nominal ?: $item->hasil_fisik) : 0,
            ];
        }

        $utleDetails = [];
        foreach ($utlePecahan as $pec) {
            $item = $kka->cashDetails->first(fn($d) => ($d->kelompok === 'UTLE' || str_contains((string)($d->objek_persediaan ?? ''), 'lusuh')) && $d->pecahan === $pec);
            $utleDetails[] = [
                'pecahan' => $pec,
                'jumlah_lembar' => $item ? $item->jumlah_lembar : 0,
                'nominal' => $item ? ($item->nominal ?: $item->hasil_fisik) : 0,
            ];
        }

        $eval = $kka->getEvaluationSummary('kas');
        return view('daily_register.print.ba_cash_khasanah', compact('kka', 'namaCabang', 'uleDetails', 'logamDetails', 'utleDetails', 'eval'));
    }

    public function printBaAtm($id)
    {
        $kka = PhysicalKka::with(['user', 'atmMachines.cassettes'])->findOrFail($id);
        $cabang = Cabang::where('kode_cabang', 'like', '%' . $kka->kode_unit . '%')->first();
        $namaCabang = $cabang ? $cabang->nama_cabang : ('Cabang ' . $kka->kode_unit);
        $atmMachines = $kka->atmMachines;
        $eval = $kka->getEvaluationSummary('atm');

        return view('daily_register.print.ba_cash_atm', compact('kka', 'namaCabang', 'atmMachines', 'eval'));
    }

    public function printKkaKartu($id)
    {
        $kka = PhysicalKka::with(['user', 'kartuAtmDetails'])->findOrFail($id);
        $stockRows = $kka->kartuAtmDetails->where('sub_area', 'Persediaan Kartu ATM');
        $controlRows = $kka->kartuAtmDetails->where('sub_area', 'Kontrol');
        $eval = $kka->getEvaluationSummary('kartu');

        return view('daily_register.print.kka_kartu_atm', compact('kka', 'stockRows', 'controlRows', 'eval'));
    }

    public function printKkaCetakan($id)
    {
        $kka = PhysicalKka::with(['user', 'barangCetakanDetails'])->findOrFail($id);
        $cabang = Cabang::where('kode_cabang', 'like', '%' . $kka->kode_unit . '%')->first();
        $namaCabang = $cabang ? $cabang->nama_cabang : ('Cabang ' . $kka->kode_unit);

        $bctItems = $kka->barangCetakanDetails->where('kategori', '!=', 'Kontrol');
        $bctControls = $kka->barangCetakanDetails->where('kategori', 'Kontrol')->map(function($c) {
            return [
                'butir' => $c->nama_barang,
                'jawaban' => $c->jawaban_kontrol ?: ($c->kondisi === 'Sesuai' ? 'Ya' : 'Tidak'),
                'catatan' => $c->catatan_kontrol ?: $c->simpulan_ra,
            ];
        })->toArray();

        $eval = $kka->getEvaluationSummary('cetakan');

        return view('daily_register.print.kka_barang_cetakan', compact('kka', 'namaCabang', 'bctItems', 'bctControls', 'eval'));
    }

    public function printKkaStok($id)
    {
        $kka = PhysicalKka::with(['user', 'stokDetails'])->findOrFail($id);
        $cabang = Cabang::where('kode_cabang', 'like', '%' . $kka->kode_unit . '%')->first();
        $namaCabang = $cabang ? $cabang->nama_cabang : ('Cabang ' . $kka->kode_unit);

        $stokItems = $kka->stokDetails->isNotEmpty() ? $kka->stokDetails : collect(self::getDefaultStokItems())->map(function($s) {
            $isCtrl = ($s['sub_area'] === 'Kontrol');
            return (object)[
                'sub_area' => $s['sub_area'],
                'kondisi_objek' => $s['objek'] . ' | ' . $s['pecahan'],
                'saldo_sistem' => $isCtrl ? 0 : ($s['saldo_sistem'] ?? 100),
                'hasil_fisik' => $isCtrl ? 0 : ($s['saldo_sistem'] ?? 100),
                'selisih' => 0,
                'status_uji' => $isCtrl ? 'Ya' : 'Sesuai',
                'is_exception' => false,
            ];
        });

        $eval = $kka->getEvaluationSummary('stok');

        return view('daily_register.print.kka_stok_cek_bg', compact('kka', 'namaCabang', 'stokItems', 'eval'));
    }

    public function printAll($id)
    {
        $kka = PhysicalKka::with(['user', 'cashDetails', 'atmMachines.cassettes', 'obsDetails', 'stokDetails', 'kartuAtmDetails', 'barangCetakanDetails'])->findOrFail($id);
        $cabang = Cabang::where('kode_cabang', 'like', '%' . $kka->kode_unit . '%')->first();
        $namaCabang = $cabang ? $cabang->nama_cabang : ('Cabang ' . $kka->kode_unit);

        $ulePecahan = ['Rp 100.000', 'Rp 75.000', 'Rp 50.000', 'Rp 20.000', 'Rp 10.000', 'Rp 5.000', 'Rp 2.000', 'Rp 1.000'];
        $logamPecahan = ['Rp 1.000', 'Rp 500', 'Rp 200', 'Rp 100', 'Rp 50', 'Rp 25'];
        $utlePecahan = ['Rp 100.000', 'Rp 75.000', 'Rp 50.000', 'Rp 20.000', 'Rp 10.000', 'Rp 5.000', 'Rp 2.000', 'Rp 1.000'];

        $uleDetails = [];
        foreach ($ulePecahan as $pec) {
            $item = $kka->cashDetails->first(fn($d) => ($d->kelompok === 'ULE' || str_contains((string)($d->objek_persediaan ?? ''), 'Layak Edar')) && $d->pecahan === $pec);
            $uleDetails[] = ['pecahan' => $pec, 'jumlah_lembar' => $item ? $item->jumlah_lembar : 0, 'nominal' => $item ? ($item->nominal ?: $item->hasil_fisik) : 0];
        }

        $logamDetails = [];
        foreach ($logamPecahan as $pec) {
            $item = $kka->cashDetails->first(fn($d) => ($d->kelompok === 'Logam' || str_contains((string)($d->objek_persediaan ?? ''), 'Logam')) && $d->pecahan === $pec);
            $logamDetails[] = ['pecahan' => $pec, 'jumlah_lembar' => $item ? $item->jumlah_lembar : 0, 'nominal' => $item ? ($item->nominal ?: $item->hasil_fisik) : 0];
        }

        $utleDetails = [];
        foreach ($utlePecahan as $pec) {
            $item = $kka->cashDetails->first(fn($d) => ($d->kelompok === 'UTLE' || str_contains((string)($d->objek_persediaan ?? ''), 'lusuh')) && $d->pecahan === $pec);
            $utleDetails[] = ['pecahan' => $pec, 'jumlah_lembar' => $item ? $item->jumlah_lembar : 0, 'nominal' => $item ? ($item->nominal ?: $item->hasil_fisik) : 0];
        }

        $atmMachines = $kka->atmMachines;
        $stockRows = $kka->kartuAtmDetails->where('sub_area', 'Persediaan Kartu ATM');
        $controlRows = $kka->kartuAtmDetails->where('sub_area', 'Kontrol');
        $bctItems = $kka->barangCetakanDetails->where('kategori', '!=', 'Kontrol');
        $bctControls = $kka->barangCetakanDetails->where('kategori', 'Kontrol')->map(function($c) {
            return [
                'butir' => $c->nama_barang,
                'jawaban' => $c->jawaban_kontrol ?: ($c->kondisi === 'Sesuai' ? 'Ya' : 'Tidak'),
                'catatan' => $c->catatan_kontrol ?: $c->simpulan_ra,
            ];
        })->toArray();

        $defaultObs = self::getDefaultObsItems();
        if ($kka->obsDetails->isNotEmpty()) {
            $existingObsMap = $kka->obsDetails->keyBy('area_observasi');
            $mergedDetails = collect();
            foreach ($defaultObs as $def) {
                $match = $existingObsMap->get($def['area']);
                if ($match) {
                    $mergedDetails->push($match);
                    $existingObsMap->forget($def['area']);
                } else {
                    $mergedDetails->push((object)[
                        'area_observasi' => $def['area'],
                        'kriteria_diharapkan' => $def['kriteria'],
                        'hasil_observasi' => 'Sesuai',
                        'kondisi_aktual' => 'Sesuai standar operasional fisik',
                        'status_uji' => 'Sesuai',
                        'is_exception' => false,
                        'penyebab' => null,
                        'risk_score' => 0,
                        'severity_level' => 'Rendah',
                        'tanggal_uji' => $kka->tanggal_pemeriksaan,
                    ]);
                }
            }
            foreach ($existingObsMap as $extra) {
                $mergedDetails->push($extra);
            }
            $obsDetails = $mergedDetails;
        } else {
            $obsDetails = collect($defaultObs)->map(fn($i) => (object)[
                'area_observasi' => $i['area'],
                'kriteria_diharapkan' => $i['kriteria'],
                'hasil_observasi' => 'Sesuai',
                'kondisi_aktual' => 'Sesuai ketentuan standar fisik operasional',
                'status_uji' => 'Sesuai',
                'is_exception' => false,
                'penyebab' => null,
                'risk_score' => 0,
                'severity_level' => 'Rendah',
                'tanggal_uji' => $kka->tanggal_pemeriksaan,
            ]);
        }

        $stokItems = $kka->stokDetails->isNotEmpty() ? $kka->stokDetails : collect(self::getDefaultStokItems())->map(function($s) {
            $isCtrl = ($s['sub_area'] === 'Kontrol');
            return (object)[
                'sub_area' => $s['sub_area'],
                'kondisi_objek' => $s['objek'] . ' | ' . $s['pecahan'],
                'saldo_sistem' => $isCtrl ? 0 : ($s['saldo_sistem'] ?? 100),
                'hasil_fisik' => $isCtrl ? 0 : ($s['saldo_sistem'] ?? 100),
                'selisih' => 0,
                'status_uji' => $isCtrl ? 'Ya' : 'Sesuai',
                'is_exception' => false,
            ];
        });

        $evalKas = $kka->getEvaluationSummary('kas');
        $evalAtm = $kka->getEvaluationSummary('atm');
        $evalKartu = $kka->getEvaluationSummary('kartu');
        $evalObs = $kka->getEvaluationSummary('observasi');
        $evalStok = $kka->getEvaluationSummary('stok');
        $evalCetakan = $kka->getEvaluationSummary('cetakan');

        return view('daily_register.print.kka_fisik_all', compact(
            'kka', 'namaCabang', 'uleDetails', 'logamDetails', 'utleDetails',
            'atmMachines', 'stockRows', 'controlRows', 'bctItems', 'bctControls', 'obsDetails', 'stokItems',
            'evalKas', 'evalAtm', 'evalKartu', 'evalObs', 'evalStok', 'evalCetakan'
        ));
    }

    public function printKkaObservasi($id)
    {
        $kka = PhysicalKka::with(['user', 'obsDetails'])->findOrFail($id);
        $cabang = Cabang::where('kode_cabang', 'like', '%' . $kka->kode_unit . '%')->first();
        $namaCabang = $cabang ? $cabang->nama_cabang : ('Cabang ' . $kka->kode_unit);

        $defaultObs = self::getDefaultObsItems();
        if ($kka->obsDetails->isNotEmpty()) {
            $existingObsMap = $kka->obsDetails->keyBy('area_observasi');
            $obsItems = [];
            foreach ($defaultObs as $def) {
                $match = $existingObsMap->get($def['area']);
                if ($match) {
                    $obsItems[] = (object)[
                        'area_observasi' => $match->area_observasi,
                        'kriteria_diharapkan' => $match->kriteria_diharapkan,
                        'hasil_observasi' => $match->hasil_observasi,
                        'kondisi_aktual' => $match->kondisi_aktual,
                        'status_uji' => $match->status_uji,
                        'is_exception' => $match->is_exception,
                        'penyebab' => $match->penyebab,
                        'risk_score' => $match->risk_score,
                        'severity_level' => $match->severity_level,
                        'tanggal_uji' => $match->tanggal_uji,
                    ];
                    $existingObsMap->forget($def['area']);
                } else {
                    $obsItems[] = (object)[
                        'area_observasi' => $def['area'],
                        'kriteria_diharapkan' => $def['kriteria'],
                        'hasil_observasi' => 'Sesuai',
                        'kondisi_aktual' => 'Sesuai standar operasional fisik',
                        'status_uji' => 'Sesuai',
                        'is_exception' => false,
                        'penyebab' => null,
                        'risk_score' => 0,
                        'severity_level' => 'Rendah',
                        'tanggal_uji' => $kka->tanggal_pemeriksaan,
                    ];
                }
            }
            foreach ($existingObsMap as $extra) {
                $obsItems[] = $extra;
            }
        } else {
            $obsItems = collect($defaultObs)->map(fn($i) => (object)[
                'area_observasi' => $i['area'],
                'kriteria_diharapkan' => $i['kriteria'],
                'hasil_observasi' => 'Sesuai',
                'kondisi_aktual' => 'Sesuai ketentuan standar fisik operasional',
                'status_uji' => 'Sesuai',
                'is_exception' => false,
                'penyebab' => null,
                'risk_score' => 0,
                'severity_level' => 'Rendah',
                'tanggal_uji' => $kka->tanggal_pemeriksaan,
            ]);
        }

        $eval = $kka->getEvaluationSummary('observasi');

        return view('daily_register.print.kka_observasi', compact('kka', 'namaCabang', 'obsItems', 'eval'));
    }

    // =========================================================================
    // EXPORT EXCEL & DOWNLOAD PDF METHODS (TAHAP 2 KKA FISIK)
    // =========================================================================

    public function exportExcel(Request $request, $id, $type = null)
    {
        $type = strtolower($type ?: $request->query('type', 'all'));
        $kka = PhysicalKka::with(['user', 'cashDetails', 'atmMachines.cassettes', 'obsDetails', 'stokDetails', 'kartuAtmDetails', 'barangCetakanDetails'])->findOrFail($id);
        
        $cabang = Cabang::where('kode_cabang', 'like', '%' . $kka->kode_unit . '%')->first();
        $namaCabang = $cabang ? $cabang->nama_cabang : ('Cabang ' . $kka->kode_unit);

        $spreadsheet = new Spreadsheet();
        $typesToExport = ($type === 'all') ? ['kas', 'atm', 'stok', 'kartu', 'cetakan', 'observasi'] : [$type];
        $sheetIndex = 0;

        foreach ($typesToExport as $t) {
            $sheet = $sheetIndex === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $sheetIndex++;

            switch ($t) {
                case 'kas':
                    $sheet->setTitle('Cash Opname Kas');
                    $this->buildExcelKasSheet($sheet, $kka, $namaCabang);
                    break;
                case 'atm':
                    $sheet->setTitle('Cash Opname ATM');
                    $this->buildExcelAtmSheet($sheet, $kka, $namaCabang);
                    break;
                case 'stok':
                    $sheet->setTitle('Rekon Stok Cek BG');
                    $this->buildExcelStokSheet($sheet, $kka, $namaCabang);
                    break;
                case 'kartu':
                    $sheet->setTitle('Rekon Kartu ATM');
                    $this->buildExcelKartuSheet($sheet, $kka, $namaCabang);
                    break;
                case 'cetakan':
                    $sheet->setTitle('Barang Cetakan');
                    $this->buildExcelCetakanSheet($sheet, $kka, $namaCabang);
                    break;
                case 'observasi':
                    $sheet->setTitle('Observasi Lingkungan');
                    $this->buildExcelObservasiSheet($sheet, $kka, $namaCabang);
                    break;
            }
        }

        $filename = 'KKA_Fisik_' . strtoupper($type) . '_' . ($kka->kode_unit ?? '000') . '_' . date('Ymd_His') . '.xlsx';

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    public function downloadPdf(Request $request, $id, $type = null)
    {
        $type = strtolower($type ?: $request->query('type', 'all'));
        $kka = PhysicalKka::with(['user', 'cashDetails', 'atmMachines.cassettes', 'obsDetails', 'stokDetails', 'kartuAtmDetails', 'barangCetakanDetails'])->findOrFail($id);
        
        $cabang = Cabang::where('kode_cabang', 'like', '%' . $kka->kode_unit . '%')->first();
        $namaCabang = $cabang ? $cabang->nama_cabang : ('Cabang ' . $kka->kode_unit);

        $builder = SimplePdfBuilder::make('BERITA ACARA & KKA FISIK - ' . strtoupper($type))
            ->addMetadata('Entitas', 'PT Bank Sulteng')
            ->addMetadata('Kantor Cabang', $namaCabang . ' (' . ($kka->kode_unit ?? '000') . ')')
            ->addMetadata('No. Arsip', $kka->no_arsip ?? '-')
            ->addMetadata('Tanggal Rekon', $kka->posisi_tanggal_rekon ?? ($kka->tanggal_pemeriksaan ?? date('Y-m-d')))
            ->addMetadata('Pelaksana', $kka->user ? $kka->user->name : 'Resident Auditor');

        $typesToExport = ($type === 'all') ? ['kas', 'atm', 'stok', 'kartu', 'cetakan', 'observasi'] : [$type];

        foreach ($typesToExport as $t) {
            switch ($t) {
                case 'kas':
                    $this->buildPdfKasSection($builder, $kka);
                    break;
                case 'atm':
                    $this->buildPdfAtmSection($builder, $kka);
                    break;
                case 'stok':
                    $this->buildPdfStokSection($builder, $kka);
                    break;
                case 'kartu':
                    $this->buildPdfKartuSection($builder, $kka);
                    break;
                case 'cetakan':
                    $this->buildPdfCetakanSection($builder, $kka);
                    break;
                case 'observasi':
                    $this->buildPdfObservasiSection($builder, $kka);
                    break;
            }
        }

        $pdfContent = $builder->render();
        $filename = 'KKA_Fisik_' . strtoupper($type) . '_' . ($kka->kode_unit ?? '000') . '_' . date('Ymd_His') . '.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    public function exportExcelKas($id, Request $request) { return $this->exportExcel($request, $id, 'kas'); }
    public function exportExcelAtm($id, Request $request) { return $this->exportExcel($request, $id, 'atm'); }
    public function exportExcelKartu($id, Request $request) { return $this->exportExcel($request, $id, 'kartu'); }
    public function exportExcelStok($id, Request $request) { return $this->exportExcel($request, $id, 'stok'); }
    public function exportExcelCetakan($id, Request $request) { return $this->exportExcel($request, $id, 'cetakan'); }
    public function exportExcelObservasi($id, Request $request) { return $this->exportExcel($request, $id, 'observasi'); }
    public function exportExcelAll($id, Request $request) { return $this->exportExcel($request, $id, 'all'); }

    public function downloadPdfKas($id, Request $request) { return $this->downloadPdf($request, $id, 'kas'); }
    public function downloadPdfAtm($id, Request $request) { return $this->downloadPdf($request, $id, 'atm'); }
    public function downloadPdfKartu($id, Request $request) { return $this->downloadPdf($request, $id, 'kartu'); }
    public function downloadPdfStok($id, Request $request) { return $this->downloadPdf($request, $id, 'stok'); }
    public function downloadPdfCetakan($id, Request $request) { return $this->downloadPdf($request, $id, 'cetakan'); }
    public function downloadPdfObservasi($id, Request $request) { return $this->downloadPdf($request, $id, 'observasi'); }
    public function downloadPdfAll($id, Request $request) { return $this->downloadPdf($request, $id, 'all'); }

    private function buildExcelKasSheet($sheet, $kka, $namaCabang)
    {
        $sheet->setCellValue('A1', 'PT BANK SULTENG - BERITA ACARA CASH OPNAME KAS KHASANAH');
        $sheet->setCellValue('A2', 'Cabang: ' . $namaCabang . ' | Unit: ' . ($kka->kode_unit ?? '000') . ' | No. Arsip: ' . ($kka->no_arsip ?? '-') . ' | Tanggal: ' . ($kka->posisi_tanggal_rekon ?? date('Y-m-d')));
        
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Kelompok');
        $sheet->setCellValue('C4', 'Pecahan');
        $sheet->setCellValue('D4', 'Jumlah Lembar/Kpg');
        $sheet->setCellValue('E4', 'Total Nominal (Rp)');

        $row = 5;
        $no = 1;
        $details = $kka->cashDetails ?? collect();
        if ($details->isEmpty()) {
            $sheet->setCellValue('A' . $row, 1);
            $sheet->setCellValue('B' . $row, 'Kas Khasanah');
            $sheet->setCellValue('C' . $row, 'Total Kas Fisik');
            $sheet->setCellValue('D' . $row, 1);
            $sheet->setCellValue('E' . $row, (float)($kka->saldo_fisik_khasanah ?? 0));
            $row++;
        } else {
            foreach ($details as $d) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $d->kelompok ?? '-');
                $sheet->setCellValue('C' . $row, $d->pecahan ?? '-');
                $sheet->setCellValue('D' . $row, (int)($d->jumlah_lembar ?? 0));
                $sheet->setCellValue('E' . $row, (float)($d->nominal ?: ($d->hasil_fisik ?? 0)));
                $row++;
            }
        }

        $row++;
        $sheet->setCellValue('B' . $row, 'TOTAL FISIK KAS KHASANAH:');
        $sheet->setCellValue('E' . $row, (float)($kka->saldo_fisik_khasanah ?? 0));
        $row++;
        $sheet->setCellValue('B' . $row, 'SALDO KAS SISTEM OLIBS:');
        $sheet->setCellValue('E' . $row, (float)($kka->saldo_kas_olibs ?? 0));
        $row++;
        $sheet->setCellValue('B' . $row, 'SELISIH KAS:');
        $sheet->setCellValue('E' . $row, (float)($kka->selisih_kas_khasanah ?? 0));
        $row++;
        $sheet->setCellValue('B' . $row, 'PENJELASAN SELISIH:');
        $sheet->setCellValue('C' . $row, $kka->penjelasan_selisih_kas ?: 'Nihil / Sesuai');
        $row += 2;
        $sheet->setCellValue('B' . $row, 'RA Pelaksana: ' . ($kka->kas_pelaksana_nama ?? '-') . ' (' . ($kka->kas_pelaksana_jabatan ?? '-') . ')');
        $sheet->setCellValue('D' . $row, 'Saksi: ' . ($kka->kas_saksi_nama ?? '-') . ' (' . ($kka->kas_saksi_jabatan ?? '-') . ')');

        $row += 2;
        $eval = $kka->getEvaluationSummary('kas');
        $sheet->setCellValue('A' . $row, 'RINGKASAN EVALUASI & PENILAIAN RISIKO KAS KHASANAH');
        $row++;
        $sheet->setCellValue('A' . $row, 'Tanggal Uji:');
        $sheet->setCellValue('B' . $row, $eval['tgl_uji']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Penyebab & Keterangan:');
        $sheet->setCellValue('B' . $row, $eval['penyebab_keterangan'] ?: 'Nihil / Sesuai');
        $row++;
        $sheet->setCellValue('A' . $row, 'Impact:');
        $sheet->setCellValue('B' . $row, $eval['impact']);
        $sheet->setCellValue('C' . $row, 'Likelihood:');
        $sheet->setCellValue('D' . $row, $eval['likelihood']);
        $sheet->setCellValue('E' . $row, 'Critical:');
        $sheet->setCellValue('F' . $row, $eval['is_critical'] ? 'Ya' : 'Tidak');
        $sheet->setCellValue('G' . $row, 'Risk Score:');
        $sheet->setCellValue('H' . $row, $eval['risk_score']);
        $sheet->setCellValue('I' . $row, 'Risk Level:');
        $sheet->setCellValue('J' . $row, $eval['risk_level']);
    }

    private function buildExcelAtmSheet($sheet, $kka, $namaCabang)
    {
        $sheet->setCellValue('A1', 'PT BANK SULTENG - BERITA ACARA CASH OPNAME MESIN ATM');
        $sheet->setCellValue('A2', 'Cabang: ' . $namaCabang . ' | Unit: ' . ($kka->kode_unit ?? '000') . ' | No. Arsip: ' . ($kka->no_arsip ?? '-') . ' | Tanggal: ' . ($kka->posisi_tanggal_rekon ?? date('Y-m-d')));

        $row = 4;
        $atms = $kka->atmMachines ?? collect();
        if ($atms->isEmpty()) {
            $sheet->setCellValue('A' . $row, 'Tidak ada data mesin ATM tercatat.');
            return;
        }

        foreach ($atms as $atm) {
            $sheet->setCellValue('A' . $row, 'Terminal ID: ' . $atm->terminal_id . ' | Lokasi: ' . ($atm->lokasi ?? '-') . ' | Merk: ' . ($atm->merk ?? '-'));
            $row++;
            $sheet->setCellValue('A' . $row, 'Saldo CBS: Rp ' . number_format($atm->saldo_cbs ?? 0, 0, ',', '.') . ' | Total Fisik: Rp ' . number_format($atm->total_fisik ?? 0, 0, ',', '.') . ' | Selisih: Rp ' . number_format($atm->selisih ?? 0, 0, ',', '.'));
            $row++;
            
            $sheet->setCellValue('A' . $row, 'No');
            $sheet->setCellValue('B' . $row, 'Nama Kaset');
            $sheet->setCellValue('C' . $row, 'Denominasi');
            $sheet->setCellValue('D' . $row, 'Lembar');
            $sheet->setCellValue('E' . $row, 'Total Nominal (Rp)');
            $row++;

            $cNo = 1;
            foreach ($atm->cassettes ?? collect() as $cas) {
                $sheet->setCellValue('A' . $row, $cNo++);
                $sheet->setCellValue('B' . $row, $cas->nama_kaset ?? 'Kaset');
                $sheet->setCellValue('C' . $row, (float)($cas->denominasi ?? 100000));
                $sheet->setCellValue('D' . $row, (int)($cas->jumlah_lembar ?? 0));
                $sheet->setCellValue('E' . $row, (float)($cas->total_nominal ?? 0));
                $row++;
            }

            if (!empty($atm->keterangan_selisih)) {
                $sheet->setCellValue('B' . $row, 'Keterangan Selisih: ' . $atm->keterangan_selisih);
                $row++;
            }
            $row++;
        }

        $row += 2;
        $eval = $kka->getEvaluationSummary('atm');
        $sheet->setCellValue('A' . $row, 'RINGKASAN EVALUASI & PENILAIAN RISIKO MESIN ATM');
        $row++;
        $sheet->setCellValue('A' . $row, 'Tanggal Uji:');
        $sheet->setCellValue('B' . $row, $eval['tgl_uji']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Penyebab & Keterangan:');
        $sheet->setCellValue('B' . $row, $eval['penyebab_keterangan'] ?: 'Nihil / Sesuai');
        $row++;
        $sheet->setCellValue('A' . $row, 'Impact:');
        $sheet->setCellValue('B' . $row, $eval['impact']);
        $sheet->setCellValue('C' . $row, 'Likelihood:');
        $sheet->setCellValue('D' . $row, $eval['likelihood']);
        $sheet->setCellValue('E' . $row, 'Critical:');
        $sheet->setCellValue('F' . $row, $eval['is_critical'] ? 'Ya' : 'Tidak');
        $sheet->setCellValue('G' . $row, 'Risk Score:');
        $sheet->setCellValue('H' . $row, $eval['risk_score']);
        $sheet->setCellValue('I' . $row, 'Risk Level:');
        $sheet->setCellValue('J' . $row, $eval['risk_level']);
    }

    private function buildExcelKartuSheet($sheet, $kka, $namaCabang)
    {
        $sheet->setCellValue('A1', 'PT BANK SULTENG - REKONSILIASI KARTU ATM & PIN MAILER');
        $sheet->setCellValue('A2', 'Cabang: ' . $namaCabang . ' | Unit: ' . ($kka->kode_unit ?? '000') . ' | No. Arsip: ' . ($kka->no_arsip ?? '-') . ' | Tanggal: ' . ($kka->posisi_tanggal_rekon ?? date('Y-m-d')));

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Jenis Kartu');
        $sheet->setCellValue('C4', 'Harga Modal (Rp)');
        $sheet->setCellValue('D4', 'Fisik Kosong');
        $sheet->setCellValue('E4', 'Fisik Rusak');
        $sheet->setCellValue('F4', 'Total Nominal Fisik (Rp)');
        $sheet->setCellValue('G4', 'Saldo Buku Qty');
        $sheet->setCellValue('H4', 'Saldo Buku Nom (Rp)');
        $sheet->setCellValue('I4', 'Selisih Persediaan (Rp)');
        $sheet->setCellValue('J4', 'PIN Mailer Qty');
        $sheet->setCellValue('K4', 'Selisih PIN Mailer');

        $row = 5;
        $no = 1;
        $stockRows = ($kka->kartuAtmDetails ?? collect())->where('sub_area', 'Persediaan Kartu ATM');
        foreach ($stockRows as $stk) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $stk->jenis_kartu ?? '-');
            $sheet->setCellValue('C' . $row, (float)($stk->harga_modal ?? 0));
            $sheet->setCellValue('D' . $row, (int)($stk->qty_fisik_kosong ?? $stk->hasil_fisik ?? 0));
            $sheet->setCellValue('E' . $row, (int)($stk->qty_fisik_rusak ?? 0));
            $sheet->setCellValue('F' . $row, (float)($stk->nominal_fisik ?? 0));
            $sheet->setCellValue('G' . $row, (int)($stk->saldo_buku_qty ?? $stk->saldo_nominatif ?? 0));
            $sheet->setCellValue('H' . $row, (float)($stk->saldo_buku_nominal ?? 0));
            $sheet->setCellValue('I' . $row, (float)($stk->selisih_persediaan ?? $stk->selisih ?? 0));
            $sheet->setCellValue('J' . $row, (int)($stk->pin_mailer_qty ?? 0));
            $sheet->setCellValue('K' . $row, (int)($stk->selisih_pin_mailer ?? 0));
            $row++;
        }

        $row += 2;
        $sheet->setCellValue('A' . $row, 'CHECKLIST KONTROL PENGELOLAAN KARTU ATM');
        $row++;
        $sheet->setCellValue('A' . $row, 'No');
        $sheet->setCellValue('B' . $row, 'Butir Kontrol');
        $sheet->setCellValue('C' . $row, 'Jawaban');
        $sheet->setCellValue('D' . $row, 'Catatan');
        $row++;

        $cNo = 1;
        $ctrlRows = ($kka->kartuAtmDetails ?? collect())->where('sub_area', 'Kontrol');
        foreach ($ctrlRows as $ctrl) {
            $sheet->setCellValue('A' . $row, $cNo++);
            $sheet->setCellValue('B' . $row, $ctrl->jenis_kartu ?? '-');
            $sheet->setCellValue('C' . $row, $ctrl->jawaban_kontrol ?? ($ctrl->kondisi === 'Sesuai' ? 'Ya' : 'Tidak'));
            $sheet->setCellValue('D' . $row, $ctrl->catatan_kontrol ?? '-');
            $row++;
        }

        $row += 2;
        $eval = $kka->getEvaluationSummary('kartu');
        $sheet->setCellValue('A' . $row, 'RINGKASAN EVALUASI & PENILAIAN RISIKO KARTU ATM');
        $row++;
        $sheet->setCellValue('A' . $row, 'Tanggal Uji:');
        $sheet->setCellValue('B' . $row, $eval['tgl_uji']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Penyebab & Keterangan:');
        $sheet->setCellValue('B' . $row, $eval['penyebab_keterangan'] ?: 'Nihil / Sesuai');
        $row++;
        $sheet->setCellValue('A' . $row, 'Impact:');
        $sheet->setCellValue('B' . $row, $eval['impact']);
        $sheet->setCellValue('C' . $row, 'Likelihood:');
        $sheet->setCellValue('D' . $row, $eval['likelihood']);
        $sheet->setCellValue('E' . $row, 'Critical:');
        $sheet->setCellValue('F' . $row, $eval['is_critical'] ? 'Ya' : 'Tidak');
        $sheet->setCellValue('G' . $row, 'Risk Score:');
        $sheet->setCellValue('H' . $row, $eval['risk_score']);
        $sheet->setCellValue('I' . $row, 'Risk Level:');
        $sheet->setCellValue('J' . $row, $eval['risk_level']);
    }

    private function buildExcelCetakanSheet($sheet, $kka, $namaCabang)
    {
        $sheet->setCellValue('A1', 'PT BANK SULTENG - REKONSILIASI PERSEDIAAN BARANG CETAKAN BERHARGA');
        $sheet->setCellValue('A2', 'Cabang: ' . $namaCabang . ' | Unit: ' . ($kka->kode_unit ?? '000') . ' | No. Arsip: ' . ($kka->no_arsip ?? '-') . ' | Tanggal: ' . ($kka->posisi_tanggal_rekon ?? date('Y-m-d')));

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Kategori');
        $sheet->setCellValue('C4', 'Nama Barang Cetakan');
        $sheet->setCellValue('D4', 'Satuan');
        $sheet->setCellValue('E4', 'Saldo Buku (Sistem)');
        $sheet->setCellValue('F4', 'Jumlah Fisik');
        $sheet->setCellValue('G4', 'Selisih');
        $sheet->setCellValue('H4', 'Status Uji');

        $row = 5;
        $no = 1;
        $bctItems = ($kka->barangCetakanDetails ?? collect())->where('kategori', '!=', 'Kontrol');
        foreach ($bctItems as $bct) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $bct->kategori ?? 'Formulir');
            $sheet->setCellValue('C' . $row, $bct->nama_barang ?? ($bct->kriteria ?? '-'));
            $sheet->setCellValue('D' . $row, $bct->satuan ?? 'Bh');
            $sheet->setCellValue('E' . $row, (float)($bct->stok_sistem ?? 0));
            $sheet->setCellValue('F' . $row, (float)($bct->hasil_fisik ?? 0));
            $sheet->setCellValue('G' . $row, (float)($bct->selisih ?? 0));
            $sheet->setCellValue('H' . $row, $bct->status_uji ?? 'SESUAI');
            $row++;
        }

        $row += 2;
        $sheet->setCellValue('A' . $row, 'CHECKLIST KONTROL FISIK BARANG CETAKAN');
        $row++;
        $sheet->setCellValue('A' . $row, 'No');
        $sheet->setCellValue('B' . $row, 'Butir Pengujian');
        $sheet->setCellValue('C' . $row, 'Jawaban');
        $sheet->setCellValue('D' . $row, 'Catatan');
        $row++;

        $cNo = 1;
        $ctrlItems = ($kka->barangCetakanDetails ?? collect())->where('kategori', 'Kontrol');
        foreach ($ctrlItems as $ctrl) {
            $sheet->setCellValue('A' . $row, $cNo++);
            $sheet->setCellValue('B' . $row, $ctrl->nama_barang ?? '-');
            $sheet->setCellValue('C' . $row, $ctrl->jawaban_kontrol ?? ($ctrl->kondisi === 'Sesuai' ? 'Ya' : 'Tidak'));
            $sheet->setCellValue('D' . $row, $ctrl->catatan_kontrol ?? '-');
            $row++;
        }

        $row += 2;
        $eval = $kka->getEvaluationSummary('cetakan');
        $sheet->setCellValue('A' . $row, 'RINGKASAN EVALUASI & PENILAIAN RISIKO');
        $row++;
        $sheet->setCellValue('A' . $row, 'Tanggal Uji:');
        $sheet->setCellValue('B' . $row, $eval['tgl_uji']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Penyebab & Keterangan:');
        $sheet->setCellValue('B' . $row, $eval['penyebab_keterangan'] ?: 'Nihil / Sesuai');
        $row++;
        $sheet->setCellValue('A' . $row, 'Impact:');
        $sheet->setCellValue('B' . $row, $eval['impact']);
        $sheet->setCellValue('C' . $row, 'Likelihood:');
        $sheet->setCellValue('D' . $row, $eval['likelihood']);
        $sheet->setCellValue('E' . $row, 'Critical:');
        $sheet->setCellValue('F' . $row, $eval['is_critical'] ? 'Ya' : 'Tidak');
        $sheet->setCellValue('G' . $row, 'Risk Score:');
        $sheet->setCellValue('H' . $row, $eval['risk_score']);
        $sheet->setCellValue('I' . $row, 'Risk Level:');
        $sheet->setCellValue('J' . $row, $eval['risk_level']);
    }

    private function buildExcelStokSheet($sheet, $kka, $namaCabang)
    {
        $sheet->setCellValue('A1', 'PT BANK SULTENG - REKONSILIASI STOK CEK & BILYET GIRO');
        $sheet->setCellValue('A2', 'Cabang: ' . $namaCabang . ' | Unit: ' . ($kka->kode_unit ?? '000') . ' | No. Arsip: ' . ($kka->no_arsip ?? '-') . ' | Tanggal: ' . ($kka->posisi_tanggal_rekon ?? date('Y-m-d')));

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Kategori / Sub Area');
        $sheet->setCellValue('C4', 'Kriteria / Jenis Warkat');
        $sheet->setCellValue('D4', 'Saldo Buku');
        $sheet->setCellValue('E4', 'Jumlah Fisik');
        $sheet->setCellValue('F4', 'Selisih');
        $sheet->setCellValue('G4', 'Status Cek');

        $row = 5;
        $no = 1;
        $stokItems = $kka->stokDetails ?? collect();
        if ($stokItems->isEmpty()) {
            $defaultStok = self::getDefaultStokItems();
            foreach ($defaultStok as $def) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $def['sub_area']);
                $sheet->setCellValue('C' . $row, $def['objek'] . ' (' . $def['pecahan'] . ')');
                $isCtrl = ($def['sub_area'] === 'Kontrol');
                $sheet->setCellValue('D' . $row, $isCtrl ? '-' : 0);
                $sheet->setCellValue('E' . $row, $isCtrl ? 'Ya' : 0);
                $sheet->setCellValue('F' . $row, $isCtrl ? '-' : 0);
                $sheet->setCellValue('G' . $row, $isCtrl ? 'Ya' : 'Sesuai');
                $row++;
            }
        } else {
            foreach ($stokItems as $item) {
                $isCtrl = ($item->sub_area === 'Kontrol');
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $item->sub_area ?? '-');
                $sheet->setCellValue('C' . $row, $item->kondisi_objek ?? '-');
                $sheet->setCellValue('D' . $row, $isCtrl ? '-' : (float)($item->saldo_sistem ?? 0));
                $sheet->setCellValue('E' . $row, $isCtrl ? ($item->status_uji ?: 'Ya') : (float)($item->hasil_fisik ?? 0));
                $selisih = $isCtrl ? 0 : ((float)($item->saldo_sistem ?? 0) - (float)($item->hasil_fisik ?? 0));
                $sheet->setCellValue('F' . $row, $isCtrl ? '-' : $selisih);
                $sheet->setCellValue('G' . $row, $item->status_uji ?: ($isCtrl ? 'Ya' : 'Sesuai'));
                $row++;
            }
        }

        $row += 2;
        $eval = $kka->getEvaluationSummary('stok');
        $sheet->setCellValue('A' . $row, 'RINGKASAN EVALUASI & PENILAIAN RISIKO');
        $row++;
        $sheet->setCellValue('A' . $row, 'Tanggal Uji:');
        $sheet->setCellValue('B' . $row, $eval['tgl_uji']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Penyebab & Keterangan:');
        $sheet->setCellValue('B' . $row, $eval['penyebab_keterangan'] ?: 'Nihil / Sesuai');
        $row++;
        $sheet->setCellValue('A' . $row, 'Impact:');
        $sheet->setCellValue('B' . $row, $eval['impact']);
        $sheet->setCellValue('C' . $row, 'Likelihood:');
        $sheet->setCellValue('D' . $row, $eval['likelihood']);
        $sheet->setCellValue('E' . $row, 'Critical:');
        $sheet->setCellValue('F' . $row, $eval['is_critical'] ? 'Ya' : 'Tidak');
        $sheet->setCellValue('G' . $row, 'Risk Score:');
        $sheet->setCellValue('H' . $row, $eval['risk_score']);
        $sheet->setCellValue('I' . $row, 'Risk Level:');
        $sheet->setCellValue('J' . $row, $eval['risk_level']);
    }

    private function buildExcelObservasiSheet($sheet, $kka, $namaCabang)
    {
        $sheet->setCellValue('A1', 'PT BANK SULTENG - OBSERVASI LINGKUNGAN & FISIK');
        $sheet->setCellValue('A2', 'Cabang: ' . $namaCabang . ' | Unit: ' . ($kka->kode_unit ?? '000') . ' | No. Arsip: ' . ($kka->no_arsip ?? '-') . ' | Tanggal: ' . ($kka->posisi_tanggal_rekon ?? date('Y-m-d')));

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Area Observasi');
        $sheet->setCellValue('C4', 'Kriteria Diharapkan');
        $sheet->setCellValue('D4', 'Kondisi Aktual');
        $sheet->setCellValue('E4', 'Hasil Observasi');

        $row = 5;
        $no = 1;
        $obsItems = $kka->obsDetails ?? collect();
        if ($obsItems->isEmpty()) {
            $defaultObs = self::getDefaultObsItems();
            foreach ($defaultObs as $def) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $def['area']);
                $sheet->setCellValue('C' . $row, $def['kriteria']);
                $sheet->setCellValue('D' . $row, 'Sesuai ketentuan');
                $sheet->setCellValue('E' . $row, 'Sesuai');
                $row++;
            }
        } else {
            foreach ($obsItems as $obs) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $obs->area_observasi ?? '-');
                $sheet->setCellValue('C' . $row, $obs->kriteria_diharapkan ?? '-');
                $sheet->setCellValue('D' . $row, $obs->kondisi_aktual ?? 'Sesuai');
                $sheet->setCellValue('E' . $row, $obs->hasil_observasi ?? 'Sesuai');
                $row++;
            }
        }

        $row += 2;
        $eval = $kka->getEvaluationSummary('observasi');
        $sheet->setCellValue('A' . $row, 'RINGKASAN EVALUASI & PENILAIAN RISIKO');
        $row++;
        $sheet->setCellValue('A' . $row, 'Tanggal Uji:');
        $sheet->setCellValue('B' . $row, $eval['tgl_uji']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Penyebab & Keterangan:');
        $sheet->setCellValue('B' . $row, $eval['penyebab_keterangan'] ?: 'Nihil / Sesuai');
        $row++;
        $sheet->setCellValue('A' . $row, 'Impact:');
        $sheet->setCellValue('B' . $row, $eval['impact']);
        $sheet->setCellValue('C' . $row, 'Likelihood:');
        $sheet->setCellValue('D' . $row, $eval['likelihood']);
        $sheet->setCellValue('E' . $row, 'Critical:');
        $sheet->setCellValue('F' . $row, $eval['is_critical'] ? 'Ya' : 'Tidak');
        $sheet->setCellValue('G' . $row, 'Risk Score:');
        $sheet->setCellValue('H' . $row, $eval['risk_score']);
        $sheet->setCellValue('I' . $row, 'Risk Level:');
        $sheet->setCellValue('J' . $row, $eval['risk_level']);
    }

    private function buildPdfKasSection($builder, $kka)
    {
        $rows = [];
        $details = $kka->cashDetails ?? collect();
        if ($details->isEmpty()) {
            $rows[] = ['Kas Khasanah', 'Total Kas Fisik', '1', 'Rp ' . number_format($kka->saldo_fisik_khasanah ?? 0, 0, ',', '.')];
        } else {
            foreach ($details as $d) {
                $rows[] = [
                    $d->kelompok ?? '-',
                    $d->pecahan ?? '-',
                    (string)($d->jumlah_lembar ?? 0),
                    'Rp ' . number_format($d->nominal ?: ($d->hasil_fisik ?? 0), 0, ',', '.'),
                ];
            }
        }

        $builder->addSection('CASH OPNAME KAS KHASANAH (OLIBS)', ['Kelompok', 'Pecahan', 'Lembar/Kpg', 'Total Nominal'], $rows);

        $summaryRows = [
            ['Total Fisik Kas Khasanah', 'Rp ' . number_format($kka->saldo_fisik_khasanah ?? 0, 0, ',', '.')],
            ['Saldo Kas Sistem Olibs', 'Rp ' . number_format($kka->saldo_kas_olibs ?? 0, 0, ',', '.')],
            ['Selisih Kas', 'Rp ' . number_format($kka->selisih_kas_khasanah ?? 0, 0, ',', '.')],
            ['Penjelasan Selisih', $kka->penjelasan_selisih_kas ?: 'Nihil / Sesuai'],
            ['RA Pelaksana', ($kka->kas_pelaksana_nama ?: '-') . ' (' . ($kka->kas_pelaksana_jabatan ?: '-') . ')'],
            ['Saksi Khasanah', ($kka->kas_saksi_nama ?: '-') . ' (' . ($kka->kas_saksi_jabatan ?: '-') . ')'],
        ];
        $builder->addSection('REKONSILIASI KAS KHASANAH', ['Parameter', 'Keterangan / Nilai'], $summaryRows);

        $eval = $kka->getEvaluationSummary('kas');
        $evalRows = [
            ['Tanggal Uji', (string)$eval['tgl_uji']],
            ['Penyebab & Keterangan', (string)($eval['penyebab_keterangan'] ?: 'Nihil / Sesuai')],
            ['Impact', (string)$eval['impact']],
            ['Likelihood', (string)$eval['likelihood']],
            ['Critical', $eval['is_critical'] ? 'Ya' : 'Tidak'],
            ['Risk Score', (string)$eval['risk_score']],
            ['Risk Level', (string)$eval['risk_level']],
        ];
        $builder->addSection('RINGKASAN EVALUASI & PENILAIAN RISIKO KAS KHASANAH', ['Parameter', 'Keterangan / Nilai'], $evalRows);
    }

    private function buildPdfAtmSection($builder, $kka)
    {
        $atms = $kka->atmMachines ?? collect();
        if ($atms->isEmpty()) {
            $builder->addSection('CASH OPNAME MESIN ATM', ['Status'], [['Tidak ada mesin ATM tercatat']]);
            return;
        }

        foreach ($atms as $m) {
            $rows = [];
            foreach ($m->cassettes ?? collect() as $c) {
                $rows[] = [
                    $c->nama_kaset ?? 'Kaset',
                    'Rp ' . number_format($c->denominasi ?? 100000, 0, ',', '.'),
                    (string)($c->jumlah_lembar ?? 0),
                    'Rp ' . number_format($c->total_nominal ?? 0, 0, ',', '.'),
                ];
            }
            $rows[] = ['---', '---', '---', '---'];
            $rows[] = ['Saldo CBS', 'Rp ' . number_format($m->saldo_cbs ?? 0, 0, ',', '.'), 'Total Fisik', 'Rp ' . number_format($m->total_fisik ?? 0, 0, ',', '.')];
            $rows[] = ['Selisih', 'Rp ' . number_format($m->selisih ?? 0, 0, ',', '.'), 'Status Uji', $m->status_uji ?? 'Selesai'];

            $builder->addSection('ATM: ' . $m->terminal_id . ' (' . ($m->lokasi ?? 'Galeri') . ')', ['Nama Kaset', 'Denominasi', 'Lembar', 'Total'], $rows);
        }

        $eval = $kka->getEvaluationSummary('atm');
        $evalRows = [
            ['Tanggal Uji', (string)$eval['tgl_uji']],
            ['Penyebab & Keterangan', (string)($eval['penyebab_keterangan'] ?: 'Nihil / Sesuai')],
            ['Impact', (string)$eval['impact']],
            ['Likelihood', (string)$eval['likelihood']],
            ['Critical', $eval['is_critical'] ? 'Ya' : 'Tidak'],
            ['Risk Score', (string)$eval['risk_score']],
            ['Risk Level', (string)$eval['risk_level']],
        ];
        $builder->addSection('RINGKASAN EVALUASI & PENILAIAN RISIKO MESIN ATM', ['Parameter', 'Keterangan / Nilai'], $evalRows);
    }

    private function buildPdfKartuSection($builder, $kka)
    {
        $stockRows = ($kka->kartuAtmDetails ?? collect())->where('sub_area', 'Persediaan Kartu ATM');
        $rows = [];
        foreach ($stockRows as $s) {
            $rows[] = [
                $s->jenis_kartu ?? '-',
                'Fisik:' . ($s->qty_fisik_kosong ?? $s->hasil_fisik ?? 0),
                'Nom:Rp ' . number_format($s->nominal_fisik ?? 0, 0, ',', '.'),
                'Buku:' . ($s->saldo_buku_qty ?? $s->saldo_nominatif ?? 0),
                'Selisih:' . ($s->selisih_persediaan ?? $s->selisih ?? 0),
            ];
        }
        $builder->addSection('REKONSILIASI KARTU ATM (SECTION A)', ['Jenis Kartu', 'Fisik Kosong', 'Nominal Fisik', 'Saldo Buku', 'Selisih'], $rows);

        $ctrlRows = ($kka->kartuAtmDetails ?? collect())->where('sub_area', 'Kontrol');
        $cRows = [];
        $no = 1;
        foreach ($ctrlRows as $c) {
            $cRows[] = [
                (string)$no++,
                substr($c->jenis_kartu ?? '-', 0, 40),
                $c->jawaban_kontrol ?? ($c->kondisi === 'Sesuai' ? 'Ya' : 'Tidak'),
            ];
        }
        $builder->addSection('KONTROL KARTU ATM (SECTION B)', ['No', 'Butir Kontrol', 'Jawaban'], $cRows);

        $eval = $kka->getEvaluationSummary('kartu');
        $evalRows = [
            ['Tanggal Uji', (string)$eval['tgl_uji']],
            ['Penyebab & Keterangan', (string)($eval['penyebab_keterangan'] ?: 'Nihil / Sesuai')],
            ['Impact', (string)$eval['impact']],
            ['Likelihood', (string)$eval['likelihood']],
            ['Critical', $eval['is_critical'] ? 'Ya' : 'Tidak'],
            ['Risk Score', (string)$eval['risk_score']],
            ['Risk Level', (string)$eval['risk_level']],
        ];
        $builder->addSection('RINGKASAN EVALUASI & PENILAIAN RISIKO KARTU ATM', ['Parameter', 'Keterangan / Nilai'], $evalRows);
    }

    private function buildPdfCetakanSection($builder, $kka)
    {
        $bctItems = ($kka->barangCetakanDetails ?? collect())->where('kategori', '!=', 'Kontrol');
        $rows = [];
        $no = 1;
        foreach ($bctItems as $b) {
            $rows[] = [
                (string)$no++,
                substr($b->nama_barang ?? ($b->kriteria ?? '-'), 0, 30),
                (string)($b->satuan ?? 'Bh'),
                (string)($b->stok_sistem ?? 0),
                (string)($b->hasil_fisik ?? 0),
                (string)($b->selisih ?? 0),
                $b->status_uji ?? 'SESUAI',
            ];
        }
        $builder->addSection('REKONSILIASI PERSEDIAAN BARANG CETAKAN BERHARGA', ['No', 'Nama Barang', 'Satuan', 'Sistem', 'Fisik', 'Selisih', 'Status'], $rows);

        $ctrlItems = ($kka->barangCetakanDetails ?? collect())->where('kategori', 'Kontrol');
        $cRows = [];
        $cNo = 1;
        foreach ($ctrlItems as $ctrl) {
            $cRows[] = [
                (string)$cNo++,
                substr($ctrl->nama_barang ?? '-', 0, 45),
                $ctrl->jawaban_kontrol ?? ($ctrl->kondisi === 'Sesuai' ? 'Ya' : 'Tidak'),
                $ctrl->catatan_kontrol ?? '-',
            ];
        }
        if (!empty($cRows)) {
            $builder->addSection('CHECKLIST KONTROL FISIK BARANG CETAKAN', ['No', 'Butir Pengujian', 'Jawaban', 'Catatan'], $cRows);
        }

        $eval = $kka->getEvaluationSummary('cetakan');
        $evalRows = [
            ['Tanggal Uji', (string)$eval['tgl_uji']],
            ['Penyebab & Keterangan', (string)($eval['penyebab_keterangan'] ?: 'Nihil / Sesuai')],
            ['Impact', (string)$eval['impact']],
            ['Likelihood', (string)$eval['likelihood']],
            ['Critical', $eval['is_critical'] ? 'Ya' : 'Tidak'],
            ['Risk Score', (string)$eval['risk_score']],
            ['Risk Level', (string)$eval['risk_level']],
        ];
        $builder->addSection('RINGKASAN EVALUASI & PENILAIAN RISIKO BARANG CETAKAN', ['Parameter', 'Keterangan / Nilai'], $evalRows);
    }

    private function buildPdfStokSection($builder, $kka)
    {
        $stokItems = $kka->stokDetails ?? collect();
        $rows = [];
        $no = 1;
        if ($stokItems->isEmpty()) {
            $defaultStok = self::getDefaultStokItems();
            foreach ($defaultStok as $def) {
                $isCtrl = ($def['sub_area'] === 'Kontrol');
                $rows[] = [
                    (string)$no++,
                    $def['sub_area'],
                    substr($def['objek'], 0, 30),
                    $isCtrl ? '-' : '0',
                    $isCtrl ? '-' : '0',
                    $isCtrl ? '-' : '0',
                    $isCtrl ? 'Ya' : 'Sesuai',
                ];
            }
        } else {
            foreach ($stokItems as $s) {
                $isCtrl = ($s->sub_area === 'Kontrol');
                $selisih = $isCtrl ? '-' : (string)((float)($s->saldo_sistem ?? 0) - (float)($s->hasil_fisik ?? 0));
                $rows[] = [
                    (string)$no++,
                    $s->sub_area ?? '-',
                    substr($s->kondisi_objek ?? '-', 0, 30),
                    $isCtrl ? '-' : (string)($s->saldo_sistem ?? 0),
                    $isCtrl ? '-' : (string)($s->hasil_fisik ?? 0),
                    $selisih,
                    $s->status_uji ?: ($isCtrl ? 'Ya' : 'Sesuai'),
                ];
            }
        }
        $builder->addSection('REKONSILIASI STOK CEK & BILYET GIRO', ['No', 'Sub Area', 'Kriteria / Warkat', 'Saldo Buku', 'Fisik', 'Selisih', 'Status'], $rows);

        $eval = $kka->getEvaluationSummary('stok');
        $evalRows = [
            ['Tanggal Uji', (string)$eval['tgl_uji']],
            ['Penyebab & Keterangan', (string)($eval['penyebab_keterangan'] ?: 'Nihil / Sesuai')],
            ['Impact', (string)$eval['impact']],
            ['Likelihood', (string)$eval['likelihood']],
            ['Critical', $eval['is_critical'] ? 'Ya' : 'Tidak'],
            ['Risk Score', (string)$eval['risk_score']],
            ['Risk Level', (string)$eval['risk_level']],
        ];
        $builder->addSection('RINGKASAN EVALUASI & PENILAIAN RISIKO STOK CEK / BG', ['Parameter', 'Keterangan / Nilai'], $evalRows);
    }

    private function buildPdfObservasiSection($builder, $kka)
    {
        $obsItems = $kka->obsDetails ?? collect();
        $rows = [];
        $no = 1;
        if ($obsItems->isEmpty()) {
            $defaultObs = self::getDefaultObsItems();
            foreach ($defaultObs as $def) {
                $rows[] = [
                    (string)$no++,
                    substr($def['area'], 0, 45),
                    'Sesuai',
                ];
            }
        } else {
            foreach ($obsItems as $o) {
                $rows[] = [
                    (string)$no++,
                    substr($o->area_observasi ?? '-', 0, 45),
                    $o->hasil_observasi ?? 'Sesuai',
                ];
            }
        }
        $builder->addSection('OBSERVASI LINGKUNGAN & FISIK', ['No', 'Area Observasi', 'Hasil Observasi'], $rows);

        $eval = $kka->getEvaluationSummary('observasi');
        $evalRows = [
            ['Tanggal Uji', (string)$eval['tgl_uji']],
            ['Penyebab & Keterangan', (string)($eval['penyebab_keterangan'] ?: 'Nihil / Sesuai')],
            ['Impact', (string)$eval['impact']],
            ['Likelihood', (string)$eval['likelihood']],
            ['Critical', $eval['is_critical'] ? 'Ya' : 'Tidak'],
            ['Risk Score', (string)$eval['risk_score']],
            ['Risk Level', (string)$eval['risk_level']],
        ];
        $builder->addSection('RINGKASAN EVALUASI & PENILAIAN RISIKO OBSERVASI', ['Parameter', 'Keterangan / Nilai'], $evalRows);
    }

    /**
     * Master Checklist Observasi Lingkungan Terstruktur Standar Bank Sulteng
     */
    public static function getDefaultObsItems(): array
    {
        return [
            // =========================================================================
            // 1. SPESIFIK 4 TITIK ALARM (PANIC BUTTON)
            // =========================================================================
            [
                'kategori' => 'Spesifik 4 Titik Alarm',
                'area' => 'Titik Alarm | Ruang Pimpinan Cabang',
                'kriteria' => 'Tombol panic button / alarm berfungsi normal, tersambung ke pos security / pihak keamanan, dan diuji berkala',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Spesifik 4 Titik Alarm',
                'area' => 'Titik Alarm | Ruang Wakil Pimpinan Cabang',
                'kriteria' => 'Tombol panic button / alarm berfungsi normal, tersambung ke pos security / pihak keamanan, dan diuji berkala',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Spesifik 4 Titik Alarm',
                'area' => 'Titik Alarm | Counter Teller',
                'kriteria' => 'Tombol alarm / kick-bar teller berfungsi normal, mudah dijangkau tersembunyi, dan dilakukan pengujian berkala',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Spesifik 4 Titik Alarm',
                'area' => 'Titik Alarm | Meja Customer Service (CS)',
                'kriteria' => 'Tombol alarm / panic button CS berfungsi responsif ke pos jaga security, dan dilakukan pengujian berkala',
                'backup_90_hari' => '—',
            ],

            // =========================================================================
            // 2. SPESIFIK 5 AREA TERBATAS
            // =========================================================================
            [
                'kategori' => 'Spesifik 5 Area Terbatas',
                'area' => 'Area Terbatas | Counter Teller',
                'kriteria' => 'Akses pintu masuk counter teller terkunci otomatis / access control, hanya teller dan otorisator berwenang yang dapat masuk',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Spesifik 5 Area Terbatas',
                'area' => 'Area Terbatas | Customer Service (CS)',
                'kriteria' => 'Area kerja berkas nasabah terlindungi, pembatasan akses non-petugas, dan penanda area terbatas terpasang',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Spesifik 5 Area Terbatas',
                'area' => 'Area Terbatas | Ruang Arsip',
                'kriteria' => 'Pintu ruang arsip selalu terkunci, akses khusus pengelola arsip, dan logbook peminjaman arsip tercatat tertib',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Spesifik 5 Area Terbatas',
                'area' => 'Area Terbatas | Ruang Server',
                'kriteria' => 'Akses pintu terkunci menggunakan kunci fisik/biometric/PIN, suhu ruang 24 jam terkontrol, hanya petugas IT yang berwenang',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Spesifik 5 Area Terbatas',
                'area' => 'Area Terbatas | Khasanah',
                'kriteria' => 'Ruang khasanah terkunci dual control, teralis pengaman terpasang, dan larangan masuk bagi pihak tanpa pendampingan pejabat',
                'backup_90_hari' => '—',
            ],

            // =========================================================================
            // 3. MATRIKS 9 TITIK CCTV (ACUAN STANDAR AUDIT E.1.5)
            // =========================================================================
            [
                'kategori' => 'Matriks 9 Titik CCTV (Acuan E.1.5)',
                'area' => 'Matriks CCTV (E.1.5) | Titik 1: Depan Banking Hall',
                'kriteria' => 'Kamera aktif 24 jam memantau akses pintu masuk utama; Penegasan Backup Rekaman Minimal 90 Hari (E.1.5)',
                'backup_90_hari' => 'Wajib Min. 90 Hari',
            ],
            [
                'kategori' => 'Matriks 9 Titik CCTV (Acuan E.1.5)',
                'area' => 'Matriks CCTV (E.1.5) | Titik 2: Banking Hall',
                'kriteria' => 'Kamera memantau ruang tunggu, antrean, dan aktivitas banking hall; Penegasan Backup Rekaman Minimal 90 Hari (E.1.5)',
                'backup_90_hari' => 'Wajib Min. 90 Hari',
            ],
            [
                'kategori' => 'Matriks 9 Titik CCTV (Acuan E.1.5)',
                'area' => 'Matriks CCTV (E.1.5) | Titik 3: Gardu ATM',
                'kriteria' => 'Kamera memantau bilik galeri ATM tanpa menyorot PIN nasabah; Penegasan Backup Rekaman Minimal 90 Hari (E.1.5)',
                'backup_90_hari' => 'Wajib Min. 90 Hari',
            ],
            [
                'kategori' => 'Matriks 9 Titik CCTV (Acuan E.1.5)',
                'area' => 'Matriks CCTV (E.1.5) | Titik 4: Gudang Arsip',
                'kriteria' => 'Kamera memantau pintu akses dan lorong rak dokumen arsip; Penegasan Backup Rekaman Minimal 90 Hari (E.1.5)',
                'backup_90_hari' => 'Wajib Min. 90 Hari',
            ],
            [
                'kategori' => 'Matriks 9 Titik CCTV (Acuan E.1.5)',
                'area' => 'Matriks CCTV (E.1.5) | Titik 5: Khasanah',
                'kriteria' => 'Kamera menyorot pintu khasanah, aktivitas buka-tutup brankas kas besar; Penegasan Backup Rekaman Minimal 90 Hari (E.1.5)',
                'backup_90_hari' => 'Wajib Min. 90 Hari',
            ],
            [
                'kategori' => 'Matriks 9 Titik CCTV (Acuan E.1.5)',
                'area' => 'Matriks CCTV (E.1.5) | Titik 6: Teller',
                'kriteria' => 'Kamera menyorot meja counter teller, laci kas fisik, dan penyerahan uang; Penegasan Backup Rekaman Minimal 90 Hari (E.1.5)',
                'backup_90_hari' => 'Wajib Min. 90 Hari',
            ],
            [
                'kategori' => 'Matriks 9 Titik CCTV (Acuan E.1.5)',
                'area' => 'Matriks CCTV (E.1.5) | Titik 7: CS',
                'kriteria' => 'Kamera menyorot area meja customer service dan verifikasi nasabah; Penegasan Backup Rekaman Minimal 90 Hari (E.1.5)',
                'backup_90_hari' => 'Wajib Min. 90 Hari',
            ],
            [
                'kategori' => 'Matriks 9 Titik CCTV (Acuan E.1.5)',
                'area' => 'Matriks CCTV (E.1.5) | Titik 8: Ruang Server',
                'kriteria' => 'Kamera menyorot rak rack server, perangkat jaringan, dan panel kelistrikan; Penegasan Backup Rekaman Minimal 90 Hari (E.1.5)',
                'backup_90_hari' => 'Wajib Min. 90 Hari',
            ],
            [
                'kategori' => 'Matriks 9 Titik CCTV (Acuan E.1.5)',
                'area' => 'Matriks CCTV (E.1.5) | Titik 9: Kredit',
                'kriteria' => 'Kamera menyorot area kerja administrasi kredit dan penyimpanan berkas agunan; Penegasan Backup Rekaman Minimal 90 Hari (E.1.5)',
                'backup_90_hari' => 'Wajib Min. 90 Hari',
            ],

            // =========================================================================
            // 4. MUTASI & BUKU MUTASI SECURITY SERTA ABSENSI KARYAWAN
            // =========================================================================
            [
                'kategori' => 'Mutasi Security & Absensi Karyawan',
                'area' => 'Security | Mutasi & Buku Mutasi Security',
                'kriteria' => 'Buku mutasi serah terima tugas jaga security terisi rutin setiap pergantian shift, pencatatan logbook tamu dan patroli malam',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Mutasi Security & Absensi Karyawan',
                'area' => 'Security | Kesiapsiagaan Pos & Personil Jaga',
                'kriteria' => 'Personil security hadir sesuai jadwal shift, mengenakan seragam atribut lengkap, dan siaga di pos penjagaan',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Mutasi Security & Absensi Karyawan',
                'area' => 'Kepegawaian | Absensi Karyawan',
                'kriteria' => 'Pencatatan absensi kehadiran karyawan (fingerprint/aplikasi) tertib, kesesuaian jam kerja, dan rekonsiliasi berkas izin/cuti',
                'backup_90_hari' => '—',
            ],

            // =========================================================================
            // 5. SECTION KHUSUS ATM (E.4.4): BRANDING INTERIOR KANTOR VS FASILITAS ATM
            // =========================================================================
            // A. Branding Interior Kantor
            [
                'kategori' => 'Branding Interior Kantor',
                'area' => 'Branding Interior Kantor | Lisplank & Signage Kantor',
                'kriteria' => 'Lisplank fasad gedung kantor, huruf timbul, logo Bank Sulteng, dan signage informasi bersih, terawat, dan menyala normal',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Branding Interior Kantor',
                'area' => 'Branding Interior Kantor | Sandblast Kaca Kantor',
                'kriteria' => 'Stiker motif sandblast logo Bank Sulteng pada partisi kaca/pintu banking hall, teller, CS terpasang rapi, simetris, dan bersih',
                'backup_90_hari' => '—',
            ],
            // B. Fasilitas ATM (E.4.4)
            [
                'kategori' => 'Section Khusus ATM (E.4.4)',
                'area' => 'Fasilitas ATM (E.4.4) | Fasilitas UPS ATM',
                'kriteria' => 'Perangkat UPS ATM berfungsi baik, baterai prima untuk membackup daya operasional mesin ATM saat listrik PLN padam',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Section Khusus ATM (E.4.4)',
                'area' => 'Fasilitas ATM (E.4.4) | Fasilitas Stavol / Stabilizer',
                'kriteria' => 'Stavol / Automatic Voltage Regulator (AVR) beroperasi normal dan tegangan listrik keluaran stabil 220V',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Section Khusus ATM (E.4.4)',
                'area' => 'Fasilitas ATM (E.4.4) | Kunci Kombinasi Brankas ATM (Dual Control > 1 Kunci)',
                'kriteria' => 'Brankas mesin ATM wajib Dual Control: kunci fisik brankas dan nomor kombinasi brankas wajib dipegang oleh > 1 orang petugas berbeda',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Section Khusus ATM (E.4.4)',
                'area' => 'Fasilitas ATM (E.4.4) | Lolipop (Neon Box) ATM',
                'kriteria' => 'Neon box (Lolipop) ATM di depan bilik menyala terang di malam hari, bersih, dan visibilitas terbaca jelas oleh nasabah',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Section Khusus ATM (E.4.4)',
                'area' => 'Fasilitas ATM (E.4.4) | Lisplank Fasilitas ATM',
                'kriteria' => 'Lisplank kanopi/bilik ATM kokoh, warna corak corporate branding Bank Sulteng rapi, dan lampu kanopi berfungsi',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Section Khusus ATM (E.4.4)',
                'area' => 'Fasilitas ATM (E.4.4) | Sandblast & Kaca Bilik ATM',
                'kriteria' => 'Kaca bilik ATM bersih, stiker motif sandblast / safety glass terpasang rapi dengan batas privasi optimal saat bertransaksi',
                'backup_90_hari' => '—',
            ],

            // =========================================================================
            // 6. KESELAMATAN & FASILITAS LISTRIK CADANGAN GEDUNG
            // =========================================================================
            [
                'kategori' => 'Keselamatan & Fasilitas Gedung',
                'area' => 'Keselamatan | Alat Pemadam Api Ringan (APAR)',
                'kriteria' => 'APAR dalam masa berlaku aktif, segel utuh, jarum manometer pada posisi hijau, dan ditempatkan pada posisi mudah dijangkau',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Keselamatan & Fasilitas Gedung',
                'area' => 'Keselamatan | Jalur Evakuasi & Pintu Darurat',
                'kriteria' => 'Jalur evakuasi darurat bebas dari halangan barang, pintu darurat berfungsi, dan denah petunjuk titik kumpul terpasang jelas',
                'backup_90_hari' => '—',
            ],
            [
                'kategori' => 'Keselamatan & Fasilitas Gedung',
                'area' => 'Kelistrikan | Fasilitas Listrik Cadangan (Genset)',
                'kriteria' => 'Genset cadangan beroperasi normal, bahan bakar solar terisi mencukupi, dan dilakukan pemanasan berkala (warm-up)',
                'backup_90_hari' => '—',
            ],
        ];
    }

    /**
     * Master Items Rekonsiliasi Stok Cek & Bilyet Giro Standar Bank Sulteng
     */
    public static function getDefaultStokItems(): array
    {
        return [
            ['sub_area' => 'Cek', 'objek' => 'Buku cek kosong/belum digunakan', 'pecahan' => 'Seluruh buku dan nomor seri dalam listing'],
            ['sub_area' => 'Cek', 'objek' => 'Cek rusak atau dibatalkan', 'pecahan' => 'Seluruh fisik dan nomor seri dalam register pembatalan'],
            ['sub_area' => 'Cek', 'objek' => 'Cek tercetak/diserahkan tetapi belum diambil', 'pecahan' => 'Seluruh fisik berada dalam custody unit'],
            ['sub_area' => 'Bilyet Giro', 'objek' => 'Bilyet giro kosong/belum digunakan', 'pecahan' => 'Seluruh buku dan nomor seri dalam listing'],
            ['sub_area' => 'Bilyet Giro', 'objek' => 'Bilyet giro rusak atau dibatalkan', 'pecahan' => 'Seluruh fisik dan nomor seri dalam register pembatalan'],
            ['sub_area' => 'Bilyet Giro', 'objek' => 'Bilyet giro tercetak/diserahkan tetapi belum diambil', 'pecahan' => 'Seluruh fisik berada dalam custody unit'],
            ['sub_area' => 'Deposito', 'objek' => 'Bilyet deposito kosong', 'pecahan' => 'Seluruh fisik dan nomor seri dalam listing, bila berada dalam ruang lingkup'],
            ['sub_area' => 'Kontrol', 'objek' => 'Kartu kontrol harian', 'pecahan' => 'Dibuat, diperbarui, dan direkonsiliasi setiap hari'],
            ['sub_area' => 'Kontrol', 'objek' => 'Register penerimaan dan pengeluaran', 'pecahan' => 'Urutan seri, penerimaan, pengeluaran, dan serah terima lengkap'],
            ['sub_area' => 'Kontrol', 'objek' => 'Penyimpanan dokumen berharga', 'pecahan' => 'Terkunci, dual control, akses terbatas, dan penanggung jawab jelas'],
        ];
    }
}
