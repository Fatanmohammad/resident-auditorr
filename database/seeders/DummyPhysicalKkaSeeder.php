<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailyRegister\PhysicalKka;
use App\Models\DailyRegister\PhysicalKkaCashDetail;
use App\Models\DailyRegister\PhysicalKkaAtmMachine;
use App\Models\DailyRegister\PhysicalKkaAtmCassette;
use App\Models\DailyRegister\PhysicalKkaObsDetail;
use App\Models\DailyRegister\PhysicalKkaStokDetail;
use App\Models\DailyRegister\PhysicalKkaKartuAtmDetail;
use App\Models\DailyRegister\PhysicalKkaBarangCetakanDetail;
use App\Models\DailyRegister\KontrolMingguan;
use App\Http\Controllers\DailyRegister\PhysicalKkaController;
use App\Models\User;
use Carbon\Carbon;

class DummyPhysicalKkaSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::now()->toDateString();
        $dateCode = Carbon::now()->format('Ymd');

        // =========================================================================
        // 1. DUMMY KKA FISIK UNTUK KANTOR CABANG POSO (Kode Unit: 003)
        // =========================================================================
        $userPoso = User::where('email', 'ra1.poso@banksulteng.co.id')->first() 
                 ?? User::where('role', 'ra')->where('cabang_id', 6)->first()
                 ?? User::find(8);

        $userIdPoso = $userPoso ? $userPoso->id : 1;
        $userNamePoso = $userPoso ? $userPoso->name : 'Yan Hamsah';

        // Bersihkan data lama jika ada
        PhysicalKka::where('kode_unit', '003')->orWhere('kode_unit', 'BS-003')->delete();

        $kkaPoso = PhysicalKka::create([
            'kode_unit' => '003',
            'user_id' => $userIdPoso,
            'tanggal_pemeriksaan' => $today,
            'status_final' => 'Final',
            'no_arsip' => 'ARSIP-KKA-003-' . $dateCode . '-001',
            'posisi_tanggal_rekon' => $today,
            'saldo_kas_olibs' => 404400000,
            'saldo_fisik_khasanah' => 404400000,
            'selisih_kas_khasanah' => 0,
            'penjelasan_selisih_kas' => 'Fisik kas khasanah klop 100% dengan saldo buku kas besar Olibs.',
            'kas_pelaksana_nama' => $userNamePoso,
            'kas_pelaksana_jabatan' => 'Resident Auditor SKAI Cabang Poso',
            'kas_saksi_nama' => 'Ahmad Rinaldi',
            'kas_saksi_jabatan' => 'Supervisor Operasional & Pemegang Khasanah',
            'rekon_kartu_penyebab_selisih' => 'Tidak terdapat selisih antara fisik kartu ATM, saldo persediaan, dan PIN Mailer',
            'surat_izin_path' => 'surat_izin/surat_tugas_fisik_poso_dummy.pdf',
            'status_surat' => 'Approved',
            'catatan_surat' => 'Surat tugas pemeriksaan fisik harian cabang Poso lengkap dan disetujui.',
        ]);

        // TAB 1: CASH OPNAME KHASANAH POSO (3 Kelompok)
        $cashDataPoso = [
            // ULE
            ['kelompok' => 'ULE', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 100.000', 'lembar' => 2500, 'nominal' => 250000000],
            ['kelompok' => 'ULE', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 75.000',  'lembar' => 100,  'nominal' => 7500000],
            ['kelompok' => 'ULE', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 50.000',  'lembar' => 2000, 'nominal' => 100000000],
            ['kelompok' => 'ULE', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 20.000',  'lembar' => 1000, 'nominal' => 20000000],
            ['kelompok' => 'ULE', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 10.000',  'lembar' => 1000, 'nominal' => 10000000],
            ['kelompok' => 'ULE', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 5.000',   'lembar' => 1000, 'nominal' => 5000000],
            ['kelompok' => 'ULE', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 2.000',   'lembar' => 1000, 'nominal' => 2000000],
            ['kelompok' => 'ULE', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 1.000',   'lembar' => 1000, 'nominal' => 1000000],
            // Logam
            ['kelompok' => 'Logam', 'objek' => 'Uang Logam', 'pecahan' => 'Rp 1.000', 'lembar' => 500, 'nominal' => 500000],
            ['kelompok' => 'Logam', 'objek' => 'Uang Logam', 'pecahan' => 'Rp 500',   'lembar' => 500, 'nominal' => 250000],
            ['kelompok' => 'Logam', 'objek' => 'Uang Logam', 'pecahan' => 'Rp 200',   'lembar' => 500, 'nominal' => 100000],
            ['kelompok' => 'Logam', 'objek' => 'Uang Logam', 'pecahan' => 'Rp 100',   'lembar' => 500, 'nominal' => 50000],
            // UTLE
            ['kelompok' => 'UTLE', 'objek' => 'Uang lusuh/tidak layak edar', 'pecahan' => 'Rp 100.000', 'lembar' => 50, 'nominal' => 5000000],
            ['kelompok' => 'UTLE', 'objek' => 'Uang lusuh/tidak layak edar', 'pecahan' => 'Rp 50.000',  'lembar' => 60, 'nominal' => 3000000],
        ];

        foreach ($cashDataPoso as $item) {
            PhysicalKkaCashDetail::create([
                'physical_kka_id' => $kkaPoso->id,
                'sub_area' => 'Khasanah',
                'kelompok' => $item['kelompok'],
                'objek_persediaan' => $item['objek'],
                'pecahan' => $item['pecahan'],
                'jumlah_lembar' => $item['lembar'],
                'nominal' => $item['nominal'],
                'saldo_sistem' => $item['nominal'],
                'hasil_fisik' => $item['nominal'],
                'selisih' => 0,
                'status_uji' => 'Sesuai',
                'is_exception' => false,
                'penyebab' => null,
                'tanggal_uji' => $today,
                'status_review' => 'Approved',
            ]);
        }

        // TAB 2: ATM MACHINES & CASSETTES POSO (Model Card)
        $atm1Poso = PhysicalKkaAtmMachine::create([
            'physical_kka_id' => $kkaPoso->id,
            'terminal_id' => '00128',
            'lokasi' => 'ATM KC Poso Galeri Utama',
            'merk' => 'DBL',
            'denominasi' => 100000,
            'tanggal_pengisian_terakhir' => $today,
            'saldo_cbs' => 200000000,
            'total_fisik' => 200000000,
            'selisih' => 0,
            'status_uji' => 'Selesai Diuji',
            'keterangan_selisih' => null,
            'pelaksana_nama' => $userNamePoso,
            'pelaksana_jabatan' => 'RA Pelaksana SKAI',
            'saksi_nama' => 'Budi Santoso',
            'saksi_jabatan' => 'Petugas Kas ATM',
            'is_exception' => false,
        ]);

        $cassettesPoso = [
            ['nama_kaset' => 'Cassette 1', 'denominasi' => 100000, 'jumlah_lembar' => 500, 'total_nominal' => 50000000],
            ['nama_kaset' => 'Cassette 2', 'denominasi' => 100000, 'jumlah_lembar' => 500, 'total_nominal' => 50000000],
            ['nama_kaset' => 'Cassette 3', 'denominasi' => 100000, 'jumlah_lembar' => 500, 'total_nominal' => 50000000],
            ['nama_kaset' => 'Cassette 4', 'denominasi' => 100000, 'jumlah_lembar' => 500, 'total_nominal' => 50000000],
            ['nama_kaset' => 'Kaset Reject', 'denominasi' => 100000, 'jumlah_lembar' => 0, 'total_nominal' => 0],
        ];

        foreach ($cassettesPoso as $c) {
            PhysicalKkaAtmCassette::create(array_merge($c, ['atm_machine_id' => $atm1Poso->id]));
        }

        // TAB 3: OBSERVASI LINGKUNGAN POSO (STANDAR STRUKTUR SPESIFIK BANK SULTENG)
        $defaultObsItems = PhysicalKkaController::getDefaultObsItems();
        foreach ($defaultObsItems as $item) {
            PhysicalKkaObsDetail::create([
                'physical_kka_id' => $kkaPoso->id,
                'area_observasi' => $item['area'],
                'kriteria_diharapkan' => $item['kriteria'],
                'hasil_observasi' => 'Sesuai',
                'kondisi_aktual' => 'Kondisi fisik operasional baik dan sesuai ketentuan',
                'status_uji' => 'Sesuai',
                'is_exception' => false,
                'tanggal_uji' => $today,
                'status_review' => 'Approved',
            ]);
        }

        // TAB 4: STOK DOKUMEN BERHARGA POSO
        PhysicalKkaStokDetail::create([
            'physical_kka_id' => $kkaPoso->id,
            'sub_area' => 'Cek',
            'kondisi_objek' => 'Buku cek kosong/belum digunakan | Seri C-001 s/d C-120',
            'saldo_sistem' => 120,
            'hasil_fisik' => 120,
            'selisih' => 0,
            'status_uji' => 'Sesuai',
            'is_exception' => false,
            'kondisi_aktual' => '120 buku cek utuh dan nomor seri berurutan',
            'tanggal_uji' => $today,
            'status_review' => 'Approved',
        ]);

        // TAB 5: REKONSILIASI KARTU ATM POSO (SECTION A & SECTION B)
        // Section A
        $atmTypesPoso = [
            ['jenis' => 'Kartu Silver',   'modal' => 42180, 'kosong' => 500, 'rusak' => 0, 'buku' => 500],
            ['jenis' => 'Kartu Gold',     'modal' => 68820, 'kosong' => 200, 'rusak' => 0, 'buku' => 200],
            ['jenis' => 'Kartu Platinum', 'modal' => 68820, 'kosong' => 100, 'rusak' => 0, 'buku' => 100],
        ];

        $procAtmNo = 1;
        foreach ($atmTypesPoso as $at) {
            $nomFisik = $at['modal'] * $at['kosong'];
            $bukuNom = $at['modal'] * $at['buku'];
            PhysicalKkaKartuAtmDetail::create([
                'physical_kka_id' => $kkaPoso->id,
                'procedure_id' => 'FLD-WK-003-' . $dateCode . '-KATREC-' . str_pad($procAtmNo++, 3, '0', STR_PAD_LEFT),
                'sub_area' => 'Persediaan Kartu ATM',
                'jenis_kartu' => $at['jenis'],
                'keterangan_lokasi' => 'Brankas Khasanah KC Poso',
                'saldo_nominatif' => (string)$at['buku'],
                'hasil_fisik' => (string)$at['kosong'],
                'selisih' => '0',
                'status_uji' => 'SESUAI',
                'kondisi' => 'Baik',
                'harga_modal' => $at['modal'],
                'qty_fisik_kosong' => $at['kosong'],
                'qty_fisik_rusak' => $at['rusak'],
                'nominal_fisik' => $nomFisik,
                'saldo_buku_qty' => $at['buku'],
                'saldo_buku_nominal' => $bukuNom,
                'selisih_persediaan' => 0,
                'pin_mailer_qty' => $at['kosong'],
                'selisih_pin_mailer' => 0,
                'is_exception' => false,
                'tanggal_uji' => $today,
                'ra_pelaksana' => $userNamePoso,
                'status_review' => 'Approved',
            ]);
        }

        // Section B: 8 Item Kontrol
        $controlsAtm = [
            'Formulir permohonan kartu ATM (asli, tanda tangan nasabah) lengkap.',
            'Register kartu ATM keluar/masuk (stock opname) tertib dan mutakhir.',
            'Otorisasi pengaktifan kartu ATM sesuai kewenangan.',
            'Tanda terima penyerahan kartu ATM oleh nasabah terdokumentasi.',
            'Penyimpanan & penyerahan PIN Mailer dilakukan terpisah dari fisik kartu ATM (dual control).',
            'Kartu ATM rusak / expired / tertelan dibuatkan Berita Acara Pemusnahan.',
            'Penyimpanan stok kartu ATM kosong (blank) berada di lemari terkunci / brankas aman (dual control).',
            'Register kartu ATM yang diblokir / ditutup tercatat lengkap.',
        ];

        foreach ($controlsAtm as $cText) {
            PhysicalKkaKartuAtmDetail::create([
                'physical_kka_id' => $kkaPoso->id,
                'procedure_id' => 'FLD-WK-003-' . $dateCode . '-KATCTRL-' . str_pad($procAtmNo++, 3, '0', STR_PAD_LEFT),
                'sub_area' => 'Kontrol',
                'jenis_kartu' => $cText,
                'jawaban_kontrol' => 'Ya',
                'catatan_kontrol' => 'Memenuhi prosedur operasional standar.',
                'status_uji' => 'SESUAI',
                'kondisi' => 'Sesuai',
                'saldo_nominatif' => 'N/A',
                'hasil_fisik' => 'Ya',
                'selisih' => 'N/A',
                'is_exception' => false,
                'tanggal_uji' => $today,
                'ra_pelaksana' => $userNamePoso,
                'status_review' => 'Approved',
            ]);
        }

        // TAB 6: REKONSILIASI BARANG CETAKAN POSO (MERGED KRITERIA)
        $bctItemsPoso = [
            ['kategori' => 'Formulir', 'nama' => 'Formulir / Dokumen Pembukaan ATM', 'kriteria' => 'Formulir Pembukaan ATM — Kosong / Belum Digunakan', 'satuan' => 'Bh', 'sistem' => 200, 'fisik' => 200],
            ['kategori' => 'Formulir', 'nama' => 'Formulir / Dokumen Pembukaan ATM', 'kriteria' => 'Formulir Pembukaan ATM — Rusak / Batal', 'satuan' => 'Bh', 'sistem' => 0, 'fisik' => 0],
            ['kategori' => 'Bilyet Deposito', 'nama' => 'Bilyet Deposito', 'kriteria' => 'Bilyet Deposito — Kosong / Belum Digunakan', 'satuan' => 'Bh', 'sistem' => 250, 'fisik' => 250],
            ['kategori' => 'Bilyet Deposito', 'nama' => 'Bilyet Deposito', 'kriteria' => 'Bilyet Deposito — Rusak / Batal', 'satuan' => 'Bh', 'sistem' => 0, 'fisik' => 0],
            ['kategori' => 'Buku Tabungan', 'nama' => 'Tabungan Simantap', 'kriteria' => 'Tabungan Simantap — Kosong / Belum Digunakan', 'satuan' => 'Bh', 'sistem' => 400, 'fisik' => 400],
            ['kategori' => 'Buku Tabungan', 'nama' => 'Tabungan Simantap', 'kriteria' => 'Tabungan Simantap — Rusak / Batal', 'satuan' => 'Bh', 'sistem' => 0, 'fisik' => 0],
            ['kategori' => 'Buku Tabungan', 'nama' => 'Tabungan Simpeda', 'kriteria' => 'Tabungan Simpeda — Kosong / Belum Digunakan', 'satuan' => 'Bh', 'sistem' => 500, 'fisik' => 500],
            ['kategori' => 'Buku Tabungan', 'nama' => 'Tabungan Simpeda', 'kriteria' => 'Tabungan Simpeda — Rusak / Batal', 'satuan' => 'Bh', 'sistem' => 0, 'fisik' => 0],
            ['kategori' => 'Formulir', 'nama' => 'Kwitansi pencairan kredit', 'kriteria' => 'Kwitansi pencairan kredit — Kosong / Belum Digunakan', 'satuan' => 'Bh', 'sistem' => 150, 'fisik' => 150],
            ['kategori' => 'Formulir', 'nama' => 'Slip setoran', 'kriteria' => 'Slip setoran operasional', 'satuan' => 'Bh', 'sistem' => 2000, 'fisik' => 2000],
        ];

        $procBctNo = 1;
        foreach ($bctItemsPoso as $b) {
            PhysicalKkaBarangCetakanDetail::create([
                'physical_kka_id' => $kkaPoso->id,
                'procedure_id' => 'FLD-WK-003-' . $dateCode . '-BCT-' . str_pad($procBctNo++, 3, '0', STR_PAD_LEFT),
                'kategori' => $b['kategori'],
                'nama_barang' => $b['nama'],
                'kriteria' => $b['kriteria'],
                'satuan' => $b['satuan'],
                'stok_sistem' => $b['sistem'],
                'hasil_fisik' => $b['fisik'],
                'selisih' => 0,
                'status_uji' => 'SESUAI',
                'kondisi' => 'Baik',
                'is_exception' => false,
                'tanggal_uji' => $today,
                'ra_pelaksana' => $userNamePoso,
                'status_review' => 'Approved',
            ]);
        }

        // 4 Kontrol Barang Cetakan
        $bctControls = [
            'Pencatatan register penerimaan dan pengeluaran barang cetakan tertib dan mutakhir.',
            'Penyimpanan stok barang cetakan berada di lemari terkunci (dual control).',
            'Berita acara pemusnahan warkat/barang cetakan rusak atau dibatalkan terdokumentasi.',
            'Kesesuaian nomor seri warkat berurutan (tidak ada nomor seri hilang/melompat).',
        ];

        foreach ($bctControls as $bText) {
            PhysicalKkaBarangCetakanDetail::create([
                'physical_kka_id' => $kkaPoso->id,
                'procedure_id' => 'FLD-WK-003-' . $dateCode . '-BCTCTRL-' . str_pad($procBctNo++, 3, '0', STR_PAD_LEFT),
                'kategori' => 'Kontrol',
                'nama_barang' => $bText,
                'kriteria' => 'Pengujian Pengendalian Internal Barang Cetakan',
                'satuan' => 'Uji',
                'stok_sistem' => 0,
                'hasil_fisik' => 0,
                'selisih' => 0,
                'status_uji' => 'SESUAI',
                'kondisi' => 'Sesuai',
                'jawaban_kontrol' => 'Ya',
                'catatan_kontrol' => 'Register tertib dan nomor seri berurutan.',
                'is_exception' => false,
                'tanggal_uji' => $today,
                'ra_pelaksana' => $userNamePoso,
                'status_review' => 'Approved',
            ]);
        }

        KontrolMingguan::getOrCreateForUnit('003');

        $this->command->info("Dummy data Tahap 2 KKA Fisik untuk KC Poso berhasil diisi lengkap.");
    }
}
