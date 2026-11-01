<?php

namespace App\Http\Controllers\DailyRegister;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyRegister\PhysicalKka;
use App\Models\DailyRegister\PhysicalKkaCashDetail;
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
                // Ambil KC Unit
                $kcUnit = \App\Models\Unit::where('cabang_id', $induk->id)
                    ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                    ->first();
                
                $kodeKC = $kcUnit ? $kcUnit->unit_code : '-';
                $namaKC = $kcUnit ? $kcUnit->unit_name : $induk->nama_cabang;

                // Ambil pengajuan KKA Fisik terbaru untuk kode unit ini
                $latestKka = PhysicalKka::with('user')->where(function($q) use ($kodeKC, $induk) {
                    $clean = str_replace('BS-', '', $kodeKC);
                    $q->where('kode_unit', $kodeKC)
                      ->orWhere('kode_unit', $clean)
                      ->orWhere('kode_unit', 'BS-' . $clean)
                      ->orWhere('kode_unit', $induk->kode_cabang);
                })->orderBy('created_at', 'desc')->first();

                // Ambil nama RA yang ditugaskan ke unit ini
                $raUser = $latestKka ? $latestKka->user : \App\Models\User::where('cabang_id', $induk->id)->where('role', 'ra')->first();

                return (object) [
                    'id' => $latestKka ? $latestKka->id : null,
                    'kode_unit' => $kodeKC,
                    'nama_cabang' => $namaKC,
                    'tanggal_pemeriksaan' => $latestKka ? $latestKka->tanggal_pemeriksaan : null,
                    'user' => $raUser,
                    'status_final' => $latestKka ? $latestKka->status_final : 'Belum Submit'
                ];
            });

            return view('daily_register.rekap_kka', compact('kkas', 'isAdmin'));
        }
        // 1. Data Cash Opname (Sesuai Excel)
        $cashItems = [
            // Uang Kertas
            ['objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 100.000', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 75.000', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 50.000', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 20.000', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 10.000', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 5.000', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 2.000', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 1.000', 'sub_area' => 'Khasanah'],
            // Uang Logam
            ['objek' => 'Uang Logam', 'pecahan' => 'Rp 1.000', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang Logam', 'pecahan' => 'Rp 500', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang Logam', 'pecahan' => 'Rp 200', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang Logam', 'pecahan' => 'Rp 100', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang Logam', 'pecahan' => 'Rp 50', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang Logam', 'pecahan' => 'Rp 25', 'sub_area' => 'Khasanah'],
            // Uang Lusuh / Tidak Layak Edar
            ['objek' => 'Uang lusuh/tidak layak edar', 'pecahan' => 'Rp 100.000', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang lusuh/tidak layak edar', 'pecahan' => 'Rp 75.000', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang lusuh/tidak layak edar', 'pecahan' => 'Rp 50.000', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang lusuh/tidak layak edar', 'pecahan' => 'Rp 20.000', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang lusuh/tidak layak edar', 'pecahan' => 'Rp 10.000', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang lusuh/tidak layak edar', 'pecahan' => 'Rp 5.000', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang lusuh/tidak layak edar', 'pecahan' => 'Rp 2.000', 'sub_area' => 'Khasanah'],
            ['objek' => 'Uang lusuh/tidak layak edar', 'pecahan' => 'Rp 1.000', 'sub_area' => 'Khasanah'],
            // Rekonsiliasi Total
            ['objek' => 'Rekonsiliasi total khasanah', 'pecahan' => 'Total', 'sub_area' => 'Khasanah'],
        ];

        // 20 Baris ATM Dinamis
        for ($i = 1; $i <= 20; $i++) {
            $cashItems[] = [
                'objek' => '', 
                'pecahan' => 'Isi ID/lokasi ATM; maksimal 4 kaset + reject/retain', 
                'sub_area' => 'ATM'
            ];
        }

        // 2. Data Observasi Lingkungan (Sesuai Excel)
        $obsItems = [
            // Area Umum
            ['area' => 'Keamanan', 'kriteria' => 'Petugas keamanan dan buku tamu'],
            ['area' => 'Keamanan', 'kriteria' => 'Akses masuk gedung dan area terbatas'],
            ['area' => 'CCTV', 'kriteria' => 'Cakupan kamera pada area kritis'],
            ['area' => 'CCTV', 'kriteria' => 'Fungsi kamera, waktu, dan mutu rekaman'],
            ['area' => 'CCTV', 'kriteria' => 'Backup dan masa simpan rekaman'],
            ['area' => 'Layanan Nasabah', 'kriteria' => 'Mesin/sistem antrean'],
            ['area' => 'Gedung', 'kriteria' => 'Kondisi bangunan dan kebersihan'],
            ['area' => 'Gedung', 'kriteria' => 'Suhu ruangan, pencahayaan, dan ventilasi'],
            ['area' => 'Kelistrikan', 'kriteria' => 'Panel, stopkontak, kabel, stabilizer, dan UPS'],
            ['area' => 'Keselamatan', 'kriteria' => 'APAR'],
            ['area' => 'Keselamatan', 'kriteria' => 'Jalur evakuasi, pintu darurat, dan titik kumpul'],
            ['area' => 'Keselamatan', 'kriteria' => 'Kotak P3K dan komunikasi darurat'],
            ['area' => 'Layanan Nasabah', 'kriteria' => 'Privasi nasabah pada area layanan'],
            ['area' => 'Layanan Nasabah', 'kriteria' => 'Area teller dan customer service'],
            ['area' => 'Lingkungan Kerja', 'kriteria' => 'Kerapian meja, lemari, dokumen, dan jalur berjalan'],
            ['area' => 'Arsip', 'kriteria' => 'Ruang/lemari penyimpanan arsip'],
            ['area' => 'Fasilitas', 'kriteria' => 'Toilet, pantry, air bersih, dan sanitasi'],
            ['area' => 'Fasilitas', 'kriteria' => 'Genset dan fasilitas listrik cadangan'],
            ['area' => 'Identitas Gedung', 'kriteria' => 'Papan nama, informasi layanan, dan branding'],
            ['area' => 'Lingkungan Sekitar', 'kriteria' => 'Risiko banjir, kebakaran, longsor, dan gangguan sekitar'],
        ];

        // Area ATM 01 sampai ATM 10
        for ($i = 1; $i <= 10; $i++) {
            $atmNumber = str_pad($i, 2, '0', STR_PAD_LEFT);
            $obsItems[] = ['area' => "ATM $atmNumber | Bangunan/Ruang ATM", 'kriteria' => ''];
            $obsItems[] = ['area' => "ATM $atmNumber | Mesin dan Kaset ATM", 'kriteria' => ''];
            $obsItems[] = ['area' => "ATM $atmNumber | Infrastruktur Pendukung ATM", 'kriteria' => ''];
        }

        // 3. Data Stok Opname (Sesuai Excel)
        $stokItems = [
            // Grup Cek
            ['sub_area' => 'Cek', 'objek' => 'Buku cek kosong/belum digunakan', 'pecahan' => 'Seluruh buku dan nomor seri dalam listing'],
            ['sub_area' => 'Cek', 'objek' => 'Cek rusak atau dibatalkan', 'pecahan' => 'Seluruh fisik dan nomor seri dalam register pembatalan'],
            ['sub_area' => 'Cek', 'objek' => 'Cek tercetak/diserahkan tetapi belum diambil', 'pecahan' => 'Seluruh fisik berada dalam custody unit'],
            
            // Grup Bilyet Giro
            ['sub_area' => 'Bilyet Giro', 'objek' => 'Bilyet giro kosong/belum digunakan', 'pecahan' => 'Seluruh buku dan nomor seri dalam listing'],
            ['sub_area' => 'Bilyet Giro', 'objek' => 'Bilyet giro rusak atau dibatalkan', 'pecahan' => 'Seluruh fisik dan nomor seri dalam register pembatalan'],
            ['sub_area' => 'Bilyet Giro', 'objek' => 'Bilyet giro tercetak/diserahkan tetapi belum diambil', 'pecahan' => 'Seluruh fisik berada dalam custody unit'],
            
            // Grup Deposito & Kontrol
            ['sub_area' => 'Deposito', 'objek' => 'Bilyet deposito kosong', 'pecahan' => 'Seluruh fisik dan nomor seri dalam listing, bila berada dalam ruang lingkup'],
            ['sub_area' => 'Kontrol', 'objek' => 'Kartu kontrol harian', 'pecahan' => 'Dibuat, diperbarui, dan direkonsiliasi setiap hari'],
            ['sub_area' => 'Kontrol', 'objek' => 'Register penerimaan dan pengeluaran', 'pecahan' => 'Urutan seri, penerimaan, pengeluaran, dan serah terima lengkap'],
            ['sub_area' => 'Kontrol', 'objek' => 'Penyimpanan dokumen berharga', 'pecahan' => 'Terkunci, dual control, akses terbatas, dan penanggung jawab jelas'],
        ];
        
        // 4. Data Rekonsiliasi Kartu ATM (Kelompok 1 & 2)
        $kartuAtmItems = [];
        // Kelompok 1: 10 Baris Dinamis Stok Kartu
        for ($i = 1; $i <= 10; $i++) {
            $kartuAtmItems[] = [
                'id' => null,
                'procedure_id' => null,
                'sub_area' => 'Persediaan Kartu ATM',
                'jenis_kartu' => '',
                'keterangan_lokasi' => '',
                'saldo_nominatif' => '',
                'hasil_fisik' => '',
                'selisih' => '',
                'status_uji' => 'BELUM DIUJI',
                'kondisi' => 'Baik',
                'kriteria' => '',
                'penyebab' => '',
                'dampak' => '',
                'impact' => 1,
                'likelihood' => 1,
                'critical_trigger' => 'Tidak',
                'risk_score' => null,
                'severity_level' => null,
                'perlu_klarifikasi' => 'Tidak',
                'bukti_path' => null,
                'simpulan_ra' => '',
                'tanggal_uji' => date('Y-m-d'),
                'ra_pelaksana' => $user ? $user->name : '',
                'reviewer_id' => null,
                'status_review' => 'Belum Direviu',
                'validasi' => null,
                'siap_register' => 'Tidak',
                'is_exception' => false,
                'is_control' => false,
            ];
        }

        // Kelompok 2: 6 Baris Prosedur Kontrol Wajib (Excel baris 45–50)
        $staticAtmControls = [
            [
                'jenis_kartu' => 'Jumlah kartu dan PIN mailer',
                'keterangan_lokasi' => 'Jumlah kartu ATM sepadan dengan PIN mailer/komponen terkait atau perbedaannya dapat dijelaskan.'
            ],
            [
                'jenis_kartu' => 'Register penerimaan dan pengeluaran',
                'keterangan_lokasi' => 'Seluruh penerimaan, penyerahan, retur, dan pemusnahan tercatat lengkap dan mutakhir.'
            ],
            [
                'jenis_kartu' => 'Kontrol nomor seri/batch',
                'keterangan_lokasi' => 'Nomor seri atau batch dapat ditelusuri dari penerimaan sampai penyerahan kepada nasabah.'
            ],
            [
                'jenis_kartu' => 'Kartu rusak/retur',
                'keterangan_lokasi' => 'Kartu rusak, gagal personalisasi, retur, dan kartu yang akan dimusnahkan dipisahkan serta dikendalikan.'
            ],
            [
                'jenis_kartu' => 'Penyimpanan dan akses',
                'keterangan_lokasi' => 'Persediaan disimpan aman; akses dibatasi dan menerapkan dual control sesuai ketentuan.'
            ],
            [
                'jenis_kartu' => 'Bukti serah terima',
                'keterangan_lokasi' => 'Penyerahan kartu kepada nasabah atau unit lain didukung bukti yang sah dan sesuai tanggal nominatif.'
            ]
        ];

        foreach ($staticAtmControls as $ctrl) {
            $kartuAtmItems[] = [
                'id' => null,
                'procedure_id' => null,
                'sub_area' => 'Kontrol',
                'jenis_kartu' => $ctrl['jenis_kartu'],
                'keterangan_lokasi' => $ctrl['keterangan_lokasi'],
                'saldo_nominatif' => 'N/A',
                'hasil_fisik' => '',
                'selisih' => 'N/A',
                'status_uji' => 'BELUM DIUJI',
                'kondisi' => 'Sesuai',
                'kriteria' => '',
                'penyebab' => '',
                'dampak' => '',
                'impact' => 1,
                'likelihood' => 1,
                'critical_trigger' => 'Tidak',
                'risk_score' => null,
                'severity_level' => null,
                'perlu_klarifikasi' => 'Tidak',
                'bukti_path' => null,
                'simpulan_ra' => '',
                'tanggal_uji' => date('Y-m-d'),
                'ra_pelaksana' => $user ? $user->name : '',
                'reviewer_id' => null,
                'status_review' => 'Belum Direviu',
                'validasi' => null,
                'siap_register' => 'Tidak',
                'is_exception' => false,
                'is_control' => true,
            ];
        }

        // 5. Data Rekonsiliasi Barang Cetakan (27 Item Default Excel baris 15–41)
        $rawBarangCetakan = [
            ['kategori' => 'Formulir', 'nama_barang' => 'Kwitansi pencairan kredit', 'satuan' => 'Bh'],
            ['kategori' => 'Formulir', 'nama_barang' => 'Slip setoran', 'satuan' => 'Bh'],
            ['kategori' => 'Formulir', 'nama_barang' => 'Slip penarikan', 'satuan' => 'Bh'],
            ['kategori' => 'Formulir', 'nama_barang' => 'Slip RTGS', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan Simantap', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan Simpeda', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan TabunganKU', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan PNS', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan Tawa', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan Pensiunku', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan Simpel', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tawa Plus', 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'TabunganKU Plus', 'satuan' => 'Bh'],
            ['kategori' => 'Formulir', 'nama_barang' => 'Pembukaan rekening perorangan', 'satuan' => 'Bh'],
            ['kategori' => 'Formulir', 'nama_barang' => 'Pembukaan rekening nonperorangan', 'satuan' => 'Bh'],
            ['kategori' => 'Formulir', 'nama_barang' => 'Pembukaan rekening Tawa', 'satuan' => 'Bh'],
            ['kategori' => 'Formulir', 'nama_barang' => 'Form keluhan nasabah', 'satuan' => 'Bh'],
            ['kategori' => 'Formulir', 'nama_barang' => 'Form KYC', 'satuan' => 'Bh'],
            ['kategori' => 'Kertas', 'nama_barang' => 'Kertas kop bank', 'satuan' => 'Rim'],
            ['kategori' => 'Amplop', 'nama_barang' => 'Amplop kecil berlogo', 'satuan' => 'Pack'],
            ['kategori' => 'Amplop', 'nama_barang' => 'Amplop panjang berlogo', 'satuan' => 'Pack'],
            ['kategori' => 'Amplop', 'nama_barang' => 'Amplop sedang berlogo', 'satuan' => 'Pack'],
            ['kategori' => 'Perlengkapan', 'nama_barang' => 'Signature Spectroline', 'satuan' => 'Dos'],
            ['kategori' => 'Perlengkapan', 'nama_barang' => 'Invisible Signature Spectroline', 'satuan' => 'Dos'],
            ['kategori' => 'Prioritas', 'nama_barang' => 'Amplop nasabah prioritas', 'satuan' => 'Bh'],
            ['kategori' => 'Prioritas', 'nama_barang' => 'Form pembukaan nasabah prioritas', 'satuan' => 'Bh'],
            ['kategori' => 'Perlengkapan', 'nama_barang' => 'Pita printer tally', 'satuan' => 'Bh'],
        ];

        $barangCetakanItems = [];
        foreach ($rawBarangCetakan as $bct) {
            $barangCetakanItems[] = [
                'id' => null,
                'procedure_id' => null,
                'kategori' => $bct['kategori'],
                'nama_barang' => $bct['nama_barang'],
                'satuan' => $bct['satuan'],
                'stok_sistem' => 0,
                'hasil_fisik' => '',
                'selisih' => '',
                'status_uji' => 'BELUM DIUJI',
                'kondisi' => 'Baik',
                'kriteria' => '',
                'penyebab' => '',
                'dampak' => '',
                'impact' => 1,
                'likelihood' => 1,
                'critical_trigger' => 'Tidak',
                'risk_score' => null,
                'severity_level' => null,
                'perlu_klarifikasi' => 'Tidak',
                'bukti_path' => null,
                'simpulan_ra' => '',
                'tanggal_uji' => date('Y-m-d'),
                'ra_pelaksana' => $user ? $user->name : '',
                'reviewer_id' => null,
                'status_review' => 'Belum Direviu',
                'validasi' => null,
                'siap_register' => 'Tidak',
                'is_exception' => false,
            ];
        }

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

        // Carry-Forward Mingguan:
        // Untuk minggu kalender yang sama (Senin–Jumat yang sama), data 5 KKA Fisik yang sudah diisi
        // pada hari sebelumnya tetap bertahan/terbawa (carry-forward) sehingga RA tidak perlu menginput ulang.
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

        if ($id) {
            $kka = PhysicalKka::with(['cashDetails', 'obsDetails', 'stokDetails', 'kartuAtmDetails', 'barangCetakanDetails'])->findOrFail($id);
            $kodeUnit = $kka->kode_unit ?? $kodeUnit;
            $suratIzin = $kka->surat_izin_path;
            $statusSurat = $kka->status_surat;
            $catatanSurat = $kka->catatan_surat;

            // Mapping Cash
            if ($kka->cashDetails->isNotEmpty()) {
                $cashItems = $kka->cashDetails->map(function($detail) {
                    return [
                        'id' => $detail->id,
                        'sub_area' => $detail->sub_area,
                        'objek' => $detail->objek_persediaan,
                        'pecahan' => $detail->pecahan,
                        'saldo_sistem' => 50000000,
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

                // Pastikan selalu ada slot ATM kosong di tab Cash Opname ATM
                $atmCount = collect($cashItems)->where('sub_area', 'ATM')->count();
                if ($atmCount < 10) {
                    for ($i = $atmCount + 1; $i <= 10; $i++) {
                        $cashItems[] = [
                            'objek' => '', 
                            'pecahan' => 'Isi ID/lokasi ATM; maksimal 4 kaset + reject/retain', 
                            'sub_area' => 'ATM',
                            'saldo_sistem' => 50000000,
                            'hasil_fisik' => '',
                            'status_uji' => 'BELUM DIUJI',
                        ];
                    }
                }
            }

            // Mapping Obs
            if ($kka->obsDetails->isNotEmpty()) {
                $obsItems = $kka->obsDetails->map(function($detail) {
                    return [
                        'id' => $detail->id,
                        'area' => $detail->area_observasi,
                        'kriteria' => $detail->kriteria_diharapkan,
                        'kondisi_aktual' => $detail->kondisi_aktual,
                        'hasil_observasi' => $detail->hasil_observasi,
                        'status_uji' => $detail->status_uji,
                        'penyebab' => $detail->penyebab,
                        'impact' => $detail->impact,
                        'likelihood' => $detail->likelihood,
                        'critical_trigger' => $detail->critical_trigger,
                        'risk_score' => $detail->risk_score,
                        'severity_level' => $detail->severity_level,
                        'tanggal_uji' => $detail->tanggal_uji,
                        'reviewer_id' => $detail->reviewer_id,
                        'status_review' => $detail->status_review,
                        'catatan_reviewer' => $detail->catatan_reviewer,
                    ];
                })->toArray();
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
                        'saldo_sistem' => 150,
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

            // Mapping Kartu ATM
            if ($kka->kartuAtmDetails->isNotEmpty()) {
                $kartuAtmItems = $kka->kartuAtmDetails->map(function($detail) {
                    return [
                        'id' => $detail->id,
                        'procedure_id' => $detail->procedure_id,
                        'sub_area' => $detail->sub_area,
                        'jenis_kartu' => $detail->jenis_kartu,
                        'keterangan_lokasi' => $detail->keterangan_lokasi,
                        'saldo_nominatif' => $detail->saldo_nominatif,
                        'hasil_fisik' => $detail->hasil_fisik,
                        'selisih' => $detail->selisih,
                        'status_uji' => $detail->status_uji,
                        'kondisi' => $detail->kondisi,
                        'kriteria' => $detail->kriteria,
                        'penyebab' => $detail->penyebab,
                        'dampak' => $detail->dampak,
                        'impact' => $detail->impact,
                        'likelihood' => $detail->likelihood,
                        'critical_trigger' => $detail->critical_trigger,
                        'risk_score' => $detail->risk_score,
                        'severity_level' => $detail->severity_level,
                        'perlu_klarifikasi' => $detail->perlu_klarifikasi,
                        'bukti_path' => $detail->bukti_path,
                        'simpulan_ra' => $detail->simpulan_ra,
                        'tanggal_uji' => $detail->tanggal_uji,
                        'ra_pelaksana' => $detail->ra_pelaksana,
                        'reviewer_id' => $detail->reviewer_id,
                        'status_review' => $detail->status_review,
                        'validasi' => $detail->validasi,
                        'siap_register' => $detail->siap_register,
                        'is_exception' => (bool)$detail->is_exception,
                        'is_control' => ($detail->sub_area === 'Kontrol'),
                    ];
                })->toArray();
            }

            // Mapping Barang Cetakan
            if ($kka->barangCetakanDetails->isNotEmpty()) {
                $barangCetakanItems = $kka->barangCetakanDetails->map(function($detail) {
                    return [
                        'id' => $detail->id,
                        'procedure_id' => $detail->procedure_id,
                        'kategori' => $detail->kategori,
                        'nama_barang' => $detail->nama_barang,
                        'satuan' => $detail->satuan,
                        'stok_sistem' => $detail->stok_sistem,
                        'hasil_fisik' => $detail->hasil_fisik,
                        'selisih' => $detail->selisih,
                        'status_uji' => $detail->status_uji,
                        'kondisi' => $detail->kondisi,
                        'kriteria' => $detail->kriteria,
                        'penyebab' => $detail->penyebab,
                        'dampak' => $detail->dampak,
                        'impact' => $detail->impact,
                        'likelihood' => $detail->likelihood,
                        'critical_trigger' => $detail->critical_trigger,
                        'risk_score' => $detail->risk_score,
                        'severity_level' => $detail->severity_level,
                        'perlu_klarifikasi' => $detail->perlu_klarifikasi,
                        'bukti_path' => $detail->bukti_path,
                        'simpulan_ra' => $detail->simpulan_ra,
                        'tanggal_uji' => $detail->tanggal_uji,
                        'ra_pelaksana' => $detail->ra_pelaksana,
                        'reviewer_id' => $detail->reviewer_id,
                        'status_review' => $detail->status_review,
                        'validasi' => $detail->validasi,
                        'siap_register' => $detail->siap_register,
                        'is_exception' => (bool)$detail->is_exception,
                    ];
                })->toArray();
            }
        }

        return view('daily_register.physical_kka', compact(
            'cashItems', 'obsItems', 'stokItems', 'kartuAtmItems', 'barangCetakanItems',
            'isAdmin', 'id', 'suratIzin', 'statusSurat', 'catatanSurat', 'kodeUnit', 'kontrolMingguan'
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
            'cash_details' => 'nullable|array',
            'obs_details' => 'nullable|array',
            'stok_details' => 'nullable|array',
            'kartu_atm_details' => 'nullable|array',
            'barang_cetakan_details' => 'nullable|array',
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

            if ($physicalKka) {
                $updateData = ['status_final' => 'Final'];
                if ($suratIzinPath) {
                    $updateData['surat_izin_path'] = $suratIzinPath;
                    $updateData['status_surat'] = 'Pending';
                }
                $physicalKka->update($updateData);

                // Hapus detail sebelumnya untuk diperbarui dengan data terkini tanpa duplikasi
                PhysicalKkaCashDetail::where('physical_kka_id', $physicalKka->id)->delete();
                PhysicalKkaObsDetail::where('physical_kka_id', $physicalKka->id)->delete();
                PhysicalKkaStokDetail::where('physical_kka_id', $physicalKka->id)->delete();
                PhysicalKkaKartuAtmDetail::where('physical_kka_id', $physicalKka->id)->delete();
                PhysicalKkaBarangCetakanDetail::where('physical_kka_id', $physicalKka->id)->delete();
            } else {
                $physicalKka = PhysicalKka::create([
                    'kode_unit' => $kodeUnit,
                    'user_id' => $user ? $user->id : 1, 
                    'tanggal_pemeriksaan' => Carbon::now()->toDateString(),
                    'status_final' => 'Final',
                    'surat_izin_path' => $suratIzinPath,
                    'status_surat' => 'Pending'
                ]);
            }

            $hasException = false;
            $dateCode = Carbon::now()->format('Ymd');

            // 1. Process Cash Details
            if ($request->has('cash_details') && is_array($request->cash_details)) {
                foreach ($request->cash_details as $item) {
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

                    $detail = PhysicalKkaCashDetail::create($item);
                    if ($isException) $this->createKlarifikasi($detail, $kodeUnit);
                }
            }

            // 2. Process Observasi Details
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

            // 3. Process Stok Details
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

            // 4. Process Tab 4: Rekonsiliasi Kartu ATM
            if ($request->has('kartu_atm_details') && is_array($request->kartu_atm_details)) {
                $noUrutAtm = 1;
                foreach ($request->kartu_atm_details as $index => $item) {
                    $subArea = $item['sub_area'] ?? 'Persediaan Kartu ATM';
                    $isControl = ($subArea === 'Kontrol');

                    // Lewati baris dinamis kosong jika RA belum mengisinya
                    if (!$isControl && empty($item['jenis_kartu']) && (!isset($item['hasil_fisik']) || $item['hasil_fisik'] === '') && (!isset($item['saldo_nominatif']) || $item['saldo_nominatif'] === '')) {
                        continue;
                    }

                    // Format Procedure ID: FLD-WK-{Kode_Unit}-{YYYYMMDD}-KATREC-{No_Urut_3_Digit}
                    $item['procedure_id'] = $item['procedure_id'] ?? ('FLD-WK-' . $kodeUnit . '-' . $dateCode . '-KATREC-' . str_pad($noUrutAtm++, 3, '0', STR_PAD_LEFT));

                    $statusUji = 'BELUM DIUJI';
                    if ($isControl) {
                        $kondisi = $item['kondisi'] ?? 'Sesuai';
                        if ($kondisi === 'Sesuai') {
                            $statusUji = 'SESUAI';
                        } elseif ($kondisi === 'Tidak Sesuai') {
                            $statusUji = 'EXCEPTION';
                        } elseif ($kondisi === 'Tidak Berlaku') {
                            $statusUji = 'TIDAK BERLAKU';
                        }
                        $item['saldo_nominatif'] = 'N/A';
                        $item['selisih'] = 'N/A';
                    } else {
                        $saldoNom = isset($item['saldo_nominatif']) && is_numeric($item['saldo_nominatif']) ? (float)$item['saldo_nominatif'] : 0;
                        $fisik = isset($item['hasil_fisik']) && is_numeric($item['hasil_fisik']) ? (float)$item['hasil_fisik'] : 0;
                        $selisih = $fisik - $saldoNom;
                        $kondisi = $item['kondisi'] ?? 'Baik';

                        if ($kondisi === 'Tidak Berlaku') {
                            $statusUji = 'TIDAK BERLAKU';
                        } elseif (abs($selisih) >= 0.01 || in_array($kondisi, ['Outdated', 'Rusak', 'Tidak Baik'])) {
                            $statusUji = 'EXCEPTION';
                        } elseif ($item['saldo_nominatif'] !== '' && $item['hasil_fisik'] !== '') {
                            $statusUji = 'SESUAI';
                        }
                        $item['selisih'] = (string)$selisih;
                    }

                    $item['status_uji'] = $statusUji;
                    $isException = ($statusUji === 'EXCEPTION');
                    $item['is_exception'] = $isException;

                    if ($isException) {
                        $hasException = true;
                        $severity = $this->calculateSeverity(
                            $item['impact'] ?? 1,
                            $item['likelihood'] ?? 1,
                            !empty($item['critical_trigger']) && $item['critical_trigger'] === 'Ya'
                        );
                        $item['risk_score'] = $severity['score'];
                        $item['severity_level'] = $severity['level'];
                        $item['perlu_klarifikasi'] = 'Ya';
                    } else {
                        $item['risk_score'] = null;
                        $item['severity_level'] = null;
                        $item['perlu_klarifikasi'] = 'Tidak';
                    }

                    $item['ra_pelaksana'] = $user ? $user->name : ($item['ra_pelaksana'] ?? 'RA Pelaksana');
                    $item['tanggal_uji'] = !empty($item['tanggal_uji']) ? $item['tanggal_uji'] : Carbon::now()->toDateString();
                    $item['status_review'] = 'Belum Direviu';
                    $item['siap_register'] = 'Tidak';
                    $item['physical_kka_id'] = $physicalKka->id;

                    // Upload Bukti jika ada
                    if ($request->hasFile("kartu_atm_details.{$index}.bukti")) {
                        $file = $request->file("kartu_atm_details.{$index}.bukti");
                        $item['bukti_path'] = $file->storeAs('bukti_kka/kartu_atm', time() . '_' . $index . '_' . $file->getClientOriginalName(), 'public');
                    }
                    unset($item['bukti']);

                    $detail = PhysicalKkaKartuAtmDetail::create($item);
                    if ($isException) {
                        $this->createKlarifikasi($detail, $kodeUnit);
                    }
                }
            }

            // 5. Process Tab 5: Rekonsiliasi Barang Cetakan
            if ($request->has('barang_cetakan_details') && is_array($request->barang_cetakan_details)) {
                $noUrutBct = 1;
                foreach ($request->barang_cetakan_details as $index => $item) {
                    if (empty($item['nama_barang']) && (!isset($item['hasil_fisik']) || $item['hasil_fisik'] === '') && (!isset($item['stok_sistem']) || $item['stok_sistem'] === '')) {
                        continue;
                    }

                    // Format Procedure ID: FLD-WK-{Kode_Unit}-{YYYYMMDD}-BCT-{No_Urut_3_Digit}
                    $item['procedure_id'] = $item['procedure_id'] ?? ('FLD-WK-' . $kodeUnit . '-' . $dateCode . '-BCT-' . str_pad($noUrutBct++, 3, '0', STR_PAD_LEFT));

                    $stokSistem = isset($item['stok_sistem']) && is_numeric($item['stok_sistem']) ? (float)$item['stok_sistem'] : 0;
                    $hasilFisik = isset($item['hasil_fisik']) && is_numeric($item['hasil_fisik']) ? (float)$item['hasil_fisik'] : 0;
                    $selisih = $hasilFisik - $stokSistem;
                    $kondisi = $item['kondisi'] ?? 'Baik';

                    $statusUji = 'BELUM DIUJI';
                    if ($kondisi === 'Tidak Berlaku') {
                        $statusUji = 'TIDAK BERLAKU';
                    } elseif (abs($selisih) >= 0.01 || in_array($kondisi, ['Outdated', 'Rusak', 'Tidak Baik'])) {
                        $statusUji = 'EXCEPTION';
                    } elseif ($item['hasil_fisik'] !== '' && $kondisi === 'Baik' && abs($selisih) < 0.01) {
                        $statusUji = 'SESUAI';
                    }

                    $item['selisih'] = $selisih;
                    $item['status_uji'] = $statusUji;
                    $isException = ($statusUji === 'EXCEPTION');
                    $item['is_exception'] = $isException;

                    if ($isException) {
                        $hasException = true;
                        $severity = $this->calculateSeverity(
                            $item['impact'] ?? 1,
                            $item['likelihood'] ?? 1,
                            !empty($item['critical_trigger']) && $item['critical_trigger'] === 'Ya'
                        );
                        $item['risk_score'] = $severity['score'];
                        $item['severity_level'] = $severity['level'];
                        $item['perlu_klarifikasi'] = 'Ya';
                    } else {
                        $item['risk_score'] = null;
                        $item['severity_level'] = null;
                        $item['perlu_klarifikasi'] = 'Tidak';
                    }

                    $item['ra_pelaksana'] = $user ? $user->name : ($item['ra_pelaksana'] ?? 'RA Pelaksana');
                    $item['tanggal_uji'] = !empty($item['tanggal_uji']) ? $item['tanggal_uji'] : Carbon::now()->toDateString();
                    $item['status_review'] = 'Belum Direviu';
                    $item['siap_register'] = 'Tidak';
                    $item['physical_kka_id'] = $physicalKka->id;

                    // Upload Bukti jika ada
                    if ($request->hasFile("barang_cetakan_details.{$index}.bukti")) {
                        $file = $request->file("barang_cetakan_details.{$index}.bukti");
                        $item['bukti_path'] = $file->storeAs('bukti_kka/barang_cetakan', time() . '_' . $index . '_' . $file->getClientOriginalName(), 'public');
                    }
                    unset($item['bukti']);

                    $detail = PhysicalKkaBarangCetakanDetail::create($item);
                    if ($isException) {
                        $this->createKlarifikasi($detail, $kodeUnit);
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
            
            $msg = $hasException 
                ? "KKA Fisik (5 Tab Pemeriksaan) berhasil di-submit. Terdapat Exception yang telah dimasukkan ke Register Klarifikasi." 
                : "KKA Fisik (5 Tab Pemeriksaan) berhasil di-submit tanpa Exception.";
                
            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function submitReview(Request $request)
    {
        // For admin/reviewer to submit their review
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

            // Cash
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
            
            // Obs
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

            // Stok
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

            // Tab 4: Kartu ATM Review
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

            // Tab 5: Barang Cetakan Review
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
}
