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
        $stokItems = [
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

        return view('daily_register.physical_kka', compact(
            'cashItems', 'khasanahUleItems', 'khasanahLogamItems', 'khasanahUtleItems',
            'atmMachines', 'obsItems', 'stokItems', 'kartuAtmStockRows', 'kartuAtmControlRows', 'kartuAtmItems',
            'barangCetakanItems', 'barangCetakanControls', 'noArsip', 'posisiTanggalRekon',
            'saldoKasOlibs', 'saldoFisikKhasanah', 'selisihKasKhasanah', 'penjelasanSelisihKas', 'rekonKartuPenyebabSelisih',
            'isAdmin', 'id', 'kka', 'suratIzin', 'statusSurat', 'catatanSurat', 'kodeUnit', 'kontrolMingguan'
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

            $noArsip = $request->filled('no_arsip') ? $request->no_arsip : ('ARSIP-KKA-' . $kodeUnit . '-' . Carbon::now()->format('Ymd') . '-001');
            $posisiTanggalRekon = $request->filled('posisi_tanggal_rekon') ? $request->posisi_tanggal_rekon : Carbon::now()->toDateString();
            $saldoKasOlibs = $request->filled('saldo_kas_olibs') ? (float)$request->saldo_kas_olibs : 0;

            if ($physicalKka) {
                $updateData = [
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
                ];
                if ($suratIzinPath) {
                    $updateData['surat_izin_path'] = $suratIzinPath;
                    $updateData['status_surat'] = 'Pending';
                }
                $physicalKka->update($updateData);

                // Bersihkan RegisterKlarifikasi terkait detail lama untuk mencegah orphaned records dan duplikasi
                $cashIds = PhysicalKkaCashDetail::where('physical_kka_id', $physicalKka->id)->pluck('id');
                if ($cashIds->isNotEmpty()) {
                    RegisterKlarifikasi::where('source_type', PhysicalKkaCashDetail::class)->whereIn('source_id', $cashIds)->delete();
                }

                $atmIds = $physicalKka->atmMachines->pluck('id');
                if ($atmIds->isNotEmpty()) {
                    RegisterKlarifikasi::where('source_type', PhysicalKkaAtmMachine::class)->whereIn('source_id', $atmIds)->delete();
                }

                $obsIds = PhysicalKkaObsDetail::where('physical_kka_id', $physicalKka->id)->pluck('id');
                if ($obsIds->isNotEmpty()) {
                    RegisterKlarifikasi::where('source_type', PhysicalKkaObsDetail::class)->whereIn('source_id', $obsIds)->delete();
                }

                $stokIds = PhysicalKkaStokDetail::where('physical_kka_id', $physicalKka->id)->pluck('id');
                if ($stokIds->isNotEmpty()) {
                    RegisterKlarifikasi::where('source_type', PhysicalKkaStokDetail::class)->whereIn('source_id', $stokIds)->delete();
                }

                $kartuIds = PhysicalKkaKartuAtmDetail::where('physical_kka_id', $physicalKka->id)->pluck('id');
                if ($kartuIds->isNotEmpty()) {
                    RegisterKlarifikasi::where('source_type', PhysicalKkaKartuAtmDetail::class)->whereIn('source_id', $kartuIds)->delete();
                }

                $bctIds = PhysicalKkaBarangCetakanDetail::where('physical_kka_id', $physicalKka->id)->pluck('id');
                if ($bctIds->isNotEmpty()) {
                    RegisterKlarifikasi::where('source_type', PhysicalKkaBarangCetakanDetail::class)->whereIn('source_id', $bctIds)->delete();
                }

                // Hapus detail sebelumnya untuk diperbarui dengan data terkini tanpa duplikasi
                PhysicalKkaCashDetail::where('physical_kka_id', $physicalKka->id)->delete();
                PhysicalKkaObsDetail::where('physical_kka_id', $physicalKka->id)->delete();
                PhysicalKkaStokDetail::where('physical_kka_id', $physicalKka->id)->delete();
                PhysicalKkaKartuAtmDetail::where('physical_kka_id', $physicalKka->id)->delete();
                PhysicalKkaBarangCetakanDetail::where('physical_kka_id', $physicalKka->id)->delete();
                
                // ATM Machines & Cassettes
                foreach ($physicalKka->atmMachines as $oldMach) {
                    $oldMach->cassettes()->delete();
                    $oldMach->delete();
                }
            } else {
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

            // =========================================================================
            // 1. PROCESS CASH KHASANAH (3 KELOMPOK ULE, LOGAM, UTLE)
            // =========================================================================
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

            $selisihKasKhasanah = $totalFisikKasKhasanah - $saldoKasOlibs;
            $physicalKka->update([
                'saldo_fisik_khasanah' => $totalFisikKasKhasanah,
                'selisih_kas_khasanah' => $selisihKasKhasanah,
            ]);

            if (abs($selisihKasKhasanah) >= 0.01) {
                if (empty(trim($request->penjelasan_selisih_kas ?? ''))) {
                    throw ValidationException::withMessages([
                        'penjelasan_selisih_kas' => 'Uraian penjelasan selisih Kas Khasanah wajib diisi apabila terdapat selisih fisik vs saldo sistem Olibs.'
                    ]);
                }
                $hasException = true;
                // Buat finding/klarifikasi untuk kas khasanah
                $firstCash = PhysicalKkaCashDetail::where('physical_kka_id', $physicalKka->id)->first();
                if ($firstCash) {
                    $firstCash->update([
                        'is_exception' => true,
                        'status_uji' => 'Exception',
                        'selisih' => $selisihKasKhasanah,
                        'penyebab' => $request->penjelasan_selisih_kas,
                    ]);
                    $this->createKlarifikasi($firstCash, $kodeUnit);
                }
            }

            // =========================================================================
            // 2. PROCESS CASH OPNAME MESIN ATM (CARD & KASET)
            // =========================================================================
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
                        if (empty(trim($atmData['keterangan_selisih'] ?? ''))) {
                            throw ValidationException::withMessages([
                                "atm_machines.{$mIdx}.keterangan_selisih" => "Keterangan penyebab selisih kas ATM ({$atmData['terminal_id']}) wajib diisi apabila terdapat selisih fisik vs saldo CBS."
                            ]);
                        }
                        $hasException = true;
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

            // =========================================================================
            // 3. PROCESS OBSERVASI DETAILS
            // =========================================================================
            if ($request->has('obs_details') && is_array($request->obs_details)) {
                foreach ($request->obs_details as $item) {
                    if (empty($item['hasil_observasi'])) continue;

                    $isException = ($item['hasil_observasi'] === 'Tidak Sesuai');
                    $item['status_uji'] = $isException ? 'Exception' : 'Sesuai';

                    if ($isException) {
                        $hasException = true;
                        $severity = $this->calculateSeverity($item['impact'] ?? 1, $item['likelihood'] ?? 1, !empty($item['critical_trigger']) && $item['critical_trigger'] == 'Ya');
                        $item['risk_score'] = $severity['score'];
                        $item['severity_level'] = $severity['level'];
                    } else {
                        $item['risk_score'] = 0;
                        $item['severity_level'] = 'Rendah';
                    }
                    $item['is_exception'] = $isException;
                    $item['physical_kka_id'] = $physicalKka->id;

                    $detail = PhysicalKkaObsDetail::create($item);
                    if ($isException) $this->createKlarifikasi($detail, $kodeUnit);
                }
            }

            // =========================================================================
            // 4. PROCESS STOK OPNAME DETAILS
            // =========================================================================
            if ($request->has('stok_details') && is_array($request->stok_details)) {
                foreach ($request->stok_details as $item) {
                    if (empty($item['hasil_fisik'])) continue;

                    $selisih = (float)$item['saldo_sistem'] - (float)$item['hasil_fisik'];
                    $isException = ($selisih != 0);
                    
                    $item['selisih'] = $selisih;
                    $item['status_uji'] = $isException ? 'Exception' : 'Sesuai';
                    
                    if ($isException) {
                        $hasException = true;
                        $severity = $this->calculateSeverity($item['impact'] ?? 1, $item['likelihood'] ?? 1, !empty($item['critical_trigger']) && $item['critical_trigger'] == 'Ya');
                        $item['risk_score'] = $severity['score'];
                        $item['severity_level'] = $severity['level'];
                    } else {
                        $item['risk_score'] = 0;
                        $item['severity_level'] = 'Rendah';
                    }
                    $item['is_exception'] = $isException;
                    $item['physical_kka_id'] = $physicalKka->id;

                    $detail = PhysicalKkaStokDetail::create($item);
                    if ($isException) $this->createKlarifikasi($detail, $kodeUnit);
                }
            }

            // =========================================================================
            // 5. PROCESS REKONSILIASI KARTU ATM (SECTION A & SECTION B)
            // =========================================================================
            // Section A: Stock Matrix
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

            // =========================================================================
            // 6. PROCESS REKONSILIASI BARANG CETAKAN (MERGED KRITERIA & 4 KONTROL)
            // =========================================================================
            $noUrutBct = 1;
            if ($request->has('barang_cetakan_details') && is_array($request->barang_cetakan_details)) {
                foreach ($request->barang_cetakan_details as $bRow) {
                    if (empty($bRow['nama_barang']) && empty($bRow['kriteria'])) continue;

                    $stokSistem = isset($bRow['stok_sistem']) ? (float)$bRow['stok_sistem'] : 0;
                    $hasilFisik = isset($bRow['hasil_fisik']) && $bRow['hasil_fisik'] !== '' ? (float)$bRow['hasil_fisik'] : 0;
                    $selisih = $hasilFisik - $stokSistem;
                    $isException = (abs($selisih) >= 0.01);
                    if ($isException) $hasException = true;

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
                        'penyebab' => $bRow['penyebab'] ?? null,
                        'is_exception' => $isException,
                        'tanggal_uji' => Carbon::now()->toDateString(),
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
                    if ($isException) $hasException = true;

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
                        'is_exception' => $isException,
                        'tanggal_uji' => Carbon::now()->toDateString(),
                        'ra_pelaksana' => $user ? $user->name : 'Resident Auditor',
                    ]);

                    if ($isException) $this->createKlarifikasi($detail, $kodeUnit);
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
            
            $msg = $hasException 
                ? "KKA Fisik & Rekonsiliasi berhasil disimpan dan masuk ke arsip siap cetak (No. Arsip: {$noArsip}). Terdapat Exception yang telah dimasukkan ke Register Klarifikasi." 
                : "KKA Fisik & Rekonsiliasi berhasil disimpan dan masuk ke arsip siap cetak (No. Arsip: {$noArsip}) tanpa Exception.";
                
            return redirect()->route('daily-register.physical-kka.index', $physicalKka->id)->with('success', $msg);

        } catch (ValidationException $ve) {
            DB::rollBack();
            return redirect()->back()->withErrors($ve->validator)->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
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

        return view('daily_register.print.ba_cash_khasanah', compact('kka', 'namaCabang', 'uleDetails', 'logamDetails', 'utleDetails'));
    }

    public function printBaAtm($id)
    {
        $kka = PhysicalKka::with(['user', 'atmMachines.cassettes'])->findOrFail($id);
        $cabang = Cabang::where('kode_cabang', 'like', '%' . $kka->kode_unit . '%')->first();
        $namaCabang = $cabang ? $cabang->nama_cabang : ('Cabang ' . $kka->kode_unit);
        $atmMachines = $kka->atmMachines;

        return view('daily_register.print.ba_cash_atm', compact('kka', 'namaCabang', 'atmMachines'));
    }

    public function printKkaKartu($id)
    {
        $kka = PhysicalKka::with(['user', 'kartuAtmDetails'])->findOrFail($id);
        $stockRows = $kka->kartuAtmDetails->where('sub_area', 'Persediaan Kartu ATM');
        $controlRows = $kka->kartuAtmDetails->where('sub_area', 'Kontrol');

        return view('daily_register.print.kka_kartu_atm', compact('kka', 'stockRows', 'controlRows'));
    }

    public function printKkaCetakan($id)
    {
        $kka = PhysicalKka::with(['user', 'barangCetakanDetails'])->findOrFail($id);
        $bctItems = $kka->barangCetakanDetails->where('kategori', '!=', 'Kontrol');
        $bctControls = $kka->barangCetakanDetails->where('kategori', 'Kontrol')->map(function($c) {
            return [
                'butir' => $c->nama_barang,
                'jawaban' => $c->jawaban_kontrol ?: ($c->kondisi === 'Sesuai' ? 'Ya' : 'Tidak'),
                'catatan' => $c->catatan_kontrol ?: $c->simpulan_ra,
            ];
        })->toArray();

        return view('daily_register.print.kka_barang_cetakan', compact('kka', 'bctItems', 'bctControls'));
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

        return view('daily_register.print.kka_fisik_all', compact(
            'kka', 'namaCabang', 'uleDetails', 'logamDetails', 'utleDetails',
            'atmMachines', 'stockRows', 'controlRows', 'bctItems', 'bctControls', 'obsDetails'
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

        return view('daily_register.print.kka_observasi', compact('kka', 'namaCabang', 'obsItems'));
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
}
