<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailyRegister\PhysicalKka;
use App\Models\DailyRegister\PhysicalKkaCashDetail;
use App\Models\DailyRegister\PhysicalKkaObsDetail;
use App\Models\DailyRegister\PhysicalKkaStokDetail;
use App\Models\DailyRegister\PhysicalKkaKartuAtmDetail;
use App\Models\DailyRegister\PhysicalKkaBarangCetakanDetail;
use App\Models\DailyRegister\KontrolMingguan;
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
            'surat_izin_path' => 'surat_izin/surat_tugas_fisik_poso_dummy.pdf',
            'status_surat' => 'Approved',
            'catatan_surat' => 'Surat tugas pemeriksaan fisik harian cabang Poso lengkap dan disetujui.',
        ]);

        // TAB 1: CASH OPNAME POSO
        $cashDataPoso = [
            // Uang Kertas Layak Edar
            ['sub_area' => 'Khasanah', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 100.000', 'sistem' => 250000000, 'fisik' => 250000000, 'simpulan' => 'Fisik uang kertas Rp 100.000 sesuai fisik khasanah'],
            ['sub_area' => 'Khasanah', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 75.000', 'sistem' => 7500000, 'fisik' => 7500000, 'simpulan' => 'Uang peringatan kemerdekaan lengkap'],
            ['sub_area' => 'Khasanah', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 50.000', 'sistem' => 100000000, 'fisik' => 100000000, 'simpulan' => 'Fisik uang kertas Rp 50.000 klop'],
            ['sub_area' => 'Khasanah', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 20.000', 'sistem' => 20000000, 'fisik' => 20000000, 'simpulan' => 'Fisik uang kertas Rp 20.000 klop'],
            ['sub_area' => 'Khasanah', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 10.000', 'sistem' => 10000000, 'fisik' => 10000000, 'simpulan' => 'Fisik uang kertas Rp 10.000 klop'],
            ['sub_area' => 'Khasanah', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 5.000', 'sistem' => 5000000, 'fisik' => 5000000, 'simpulan' => 'Fisik uang kertas Rp 5.000 klop'],
            ['sub_area' => 'Khasanah', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 2.000', 'sistem' => 2000000, 'fisik' => 2000000, 'simpulan' => 'Fisik uang kertas Rp 2.000 klop'],
            ['sub_area' => 'Khasanah', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 1.000', 'sistem' => 1000000, 'fisik' => 1000000, 'simpulan' => 'Fisik uang kertas Rp 1.000 klop'],
            // Uang Logam
            ['sub_area' => 'Khasanah', 'objek' => 'Uang Logam', 'pecahan' => 'Rp 1.000', 'sistem' => 500000, 'fisik' => 500000, 'simpulan' => 'Logam Rp 1.000 dalam rol utuh'],
            ['sub_area' => 'Khasanah', 'objek' => 'Uang Logam', 'pecahan' => 'Rp 500', 'sistem' => 250000, 'fisik' => 250000, 'simpulan' => 'Logam Rp 500 dalam kantong koin'],
            ['sub_area' => 'Khasanah', 'objek' => 'Uang Logam', 'pecahan' => 'Rp 200', 'sistem' => 100000, 'fisik' => 100000, 'simpulan' => 'Logam Rp 200 klop'],
            ['sub_area' => 'Khasanah', 'objek' => 'Uang Logam', 'pecahan' => 'Rp 100', 'sistem' => 50000, 'fisik' => 50000, 'simpulan' => 'Logam Rp 100 klop'],
            // Uang Lusuh
            ['sub_area' => 'Khasanah', 'objek' => 'Uang lusuh/tidak layak edar', 'pecahan' => 'Rp 100.000', 'sistem' => 5000000, 'fisik' => 5000000, 'simpulan' => 'Siap disetor ke BI Palu'],
            ['sub_area' => 'Khasanah', 'objek' => 'Uang lusuh/tidak layak edar', 'pecahan' => 'Rp 50.000', 'sistem' => 3000000, 'fisik' => 3000000, 'simpulan' => 'Uang lusuh terikat rapi'],
            // Rekonsiliasi Total
            ['sub_area' => 'Khasanah', 'objek' => 'Rekonsiliasi total khasanah', 'pecahan' => 'Total', 'sistem' => 404400000, 'fisik' => 404400000, 'simpulan' => 'Total fisik khasanah klop 100% dengan saldo Buku Besar'],
            // ATM
            ['sub_area' => 'ATM', 'objek' => 'ATM 003-01 Galeri KC Poso', 'pecahan' => '4 Kaset x Rp 100.000', 'sistem' => 100000000, 'fisik' => 100000000, 'simpulan' => 'Fisik kas di dalam mesin ATM sesuai sistem CRM'],
        ];

        foreach ($cashDataPoso as $item) {
            PhysicalKkaCashDetail::create([
                'physical_kka_id' => $kkaPoso->id,
                'sub_area' => $item['sub_area'],
                'objek_persediaan' => $item['objek'],
                'pecahan' => $item['pecahan'],
                'saldo_sistem' => $item['sistem'],
                'hasil_fisik' => $item['fisik'],
                'selisih' => $item['sistem'] - $item['fisik'],
                'status_uji' => 'Sesuai',
                'is_exception' => false,
                'penyebab' => null,
                'simpulan_ra' => $item['simpulan'],
                'tanggal_uji' => $today,
                'impact' => 1,
                'likelihood' => 1,
                'critical_trigger' => false,
                'risk_score' => 0,
                'severity_level' => 'Rendah',
                'reviewer_id' => 'REV-SKAI',
                'status_review' => 'Approved',
                'catatan_reviewer' => 'Hasil opname kas memadai dan tertib.',
            ]);
        }

        // TAB 2: OBSERVASI LINGKUNGAN POSO
        $obsDataPoso = [
            ['area' => 'Keamanan', 'kriteria' => 'Petugas keamanan dan buku tamu', 'hasil' => 'Sesuai', 'kondisi' => 'Satpam berjaga 2 orang, buku register tamu terisi tertib'],
            ['area' => 'Keamanan', 'kriteria' => 'Akses masuk gedung dan area terbatas', 'hasil' => 'Sesuai', 'kondisi' => 'Pintu khasanah terkunci dual control dan access card aktif'],
            ['area' => 'CCTV', 'kriteria' => 'Cakupan kamera pada area kritis', 'hasil' => 'Sesuai', 'kondisi' => 'Semua sudut teller, CS, khasanah, dan perimeter luar tercover'],
            ['area' => 'CCTV', 'kriteria' => 'Fungsi kamera, waktu, dan mutu rekaman', 'hasil' => 'Sesuai', 'kondisi' => 'Gambar jernih, timestamp CCTV sinkron dengan jam server'],
            ['area' => 'CCTV', 'kriteria' => 'Backup dan masa simpan rekaman', 'hasil' => 'Sesuai', 'kondisi' => 'Kapasitas HDD DVR mencukupi 60 hari retensi'],
            ['area' => 'Layanan Nasabah', 'kriteria' => 'Mesin/sistem antrean', 'hasil' => 'Sesuai', 'kondisi' => 'Mesin antrean touchscreen berfungsi normal'],
            ['area' => 'Gedung', 'kriteria' => 'Kondisi bangunan dan kebersihan', 'hasil' => 'Sesuai', 'kondisi' => 'Banking hall bersih, pendingin ruangan bekerja optimal'],
            ['area' => 'Gedung', 'kriteria' => 'Suhu ruangan, pencahayaan, dan ventilasi', 'hasil' => 'Sesuai', 'kondisi' => 'Suhu ruang server 19°C dan pencahayaan memadai'],
            ['area' => 'Kelistrikan', 'kriteria' => 'Panel, stopkontak, kabel, stabilizer, dan UPS', 'hasil' => 'Sesuai', 'kondisi' => 'UPS 10 KVA dan stabilizer berfungsi tanpa kendala'],
            ['area' => 'Keselamatan', 'kriteria' => 'APAR', 'hasil' => 'Sesuai', 'kondisi' => '3 tabung APAR masa berlaku aktif s/d Des 2026 dan jarum di zona hijau'],
            ['area' => 'Keselamatan', 'kriteria' => 'Jalur evakuasi, pintu darurat, dan titik kumpul', 'hasil' => 'Sesuai', 'kondisi' => 'Rambu evakuasi jelas, titik kumpul di halaman depan'],
            ['area' => 'Keselamatan', 'kriteria' => 'Kotak P3K dan komunikasi darurat', 'hasil' => 'Sesuai', 'kondisi' => 'Kotak P3K lengkap dengan obat standar, nomor darurat terpasang'],
            ['area' => 'Layanan Nasabah', 'kriteria' => 'Privasi nasabah pada area layanan', 'hasil' => 'Sesuai', 'kondisi' => 'Jarak antrean ke meja teller/CS menjaga kerahasiaan nasabah'],
            ['area' => 'Fasilitas', 'kriteria' => 'Genset dan fasilitas listrik cadangan', 'hasil' => 'Sesuai', 'kondisi' => 'Genset Cummins 45 KVA siap jalan otomatis saat PLN padam'],
            ['area' => 'ATM 01 | Bangunan/Ruang ATM', 'kriteria' => 'Kondisi ruang ATM', 'hasil' => 'Sesuai', 'kondisi' => 'Galeri ATM bersih, lampu terang, pintu kaca tidak rusak'],
            ['area' => 'ATM 01 | Mesin dan Kaset ATM', 'kriteria' => 'Integritas mesin ATM', 'hasil' => 'Sesuai', 'kondisi' => 'Slot card reader bersih dari indikasi skimming'],
        ];

        foreach ($obsDataPoso as $item) {
            PhysicalKkaObsDetail::create([
                'physical_kka_id' => $kkaPoso->id,
                'area_observasi' => $item['area'],
                'kriteria_diharapkan' => $item['kriteria'],
                'hasil_observasi' => $item['hasil'],
                'kondisi_aktual' => $item['kondisi'],
                'status_uji' => 'Sesuai',
                'is_exception' => false,
                'penyebab' => null,
                'simpulan_ra' => 'Kondisi fisik dan keamanan area mematuhi standar operasional cabang.',
                'tanggal_uji' => $today,
                'impact' => 1,
                'likelihood' => 1,
                'critical_trigger' => false,
                'risk_score' => 0,
                'severity_level' => 'Rendah',
                'reviewer_id' => 'REV-SKAI',
                'status_review' => 'Approved',
                'catatan_reviewer' => 'Observasi lingkungan memadai.',
            ]);
        }

        // TAB 3: STOK OPNAME DOKUMEN BERHARGA POSO
        $stokDataPoso = [
            ['sub_area' => 'Cek', 'objek' => 'Buku cek kosong/belum digunakan | Seri C-001 s/d C-120', 'sistem' => 120, 'fisik' => 120, 'simpulan' => '120 buku cek utuh dan nomor seri berurutan'],
            ['sub_area' => 'Cek', 'objek' => 'Cek rusak atau dibatalkan | Seri C-099', 'sistem' => 2, 'fisik' => 2, 'simpulan' => '2 lembar cek rusak telah dilubangi dan tercatat dalam register'],
            ['sub_area' => 'Cek', 'objek' => 'Cek tercetak/diserahkan tetapi belum diambil | Custody', 'sistem' => 0, 'fisik' => 0, 'simpulan' => 'Nihil, semua pesanan cek telah diambil nasabah'],
            ['sub_area' => 'Bilyet Giro', 'objek' => 'Bilyet giro kosong/belum digunakan | Seri BG-001 s/d BG-100', 'sistem' => 100, 'fisik' => 100, 'simpulan' => '100 buku BG lengkap dalam brankas'],
            ['sub_area' => 'Bilyet Giro', 'objek' => 'Bilyet giro rusak atau dibatalkan | Register', 'sistem' => 0, 'fisik' => 0, 'simpulan' => 'Nihil'],
            ['sub_area' => 'Bilyet Giro', 'objek' => 'Bilyet giro tercetak/diserahkan tetapi belum diambil | Custody', 'sistem' => 0, 'fisik' => 0, 'simpulan' => 'Nihil'],
            ['sub_area' => 'Deposito', 'objek' => 'Bilyet deposito kosong | Seri DEP-1001 s/d DEP-1250', 'sistem' => 250, 'fisik' => 250, 'simpulan' => '250 lembar bilyet deposito utuh berurutan'],
            ['sub_area' => 'Kontrol', 'objek' => 'Kartu kontrol harian | Register Harian', 'sistem' => 1, 'fisik' => 1, 'simpulan' => 'Kartu kontrol terisi dan ditandatangani supervisor harian'],
            ['sub_area' => 'Kontrol', 'objek' => 'Register penerimaan dan pengeluaran | Buku Register', 'sistem' => 1, 'fisik' => 1, 'simpulan' => 'Register mutasi dokumen berharga mutakhir'],
            ['sub_area' => 'Kontrol', 'objek' => 'Penyimpanan dokumen berharga | Lemari Khusus Dual Control', 'sistem' => 1, 'fisik' => 1, 'simpulan' => 'Tersimpan aman di lemari besi kunci ganda'],
        ];

        foreach ($stokDataPoso as $item) {
            PhysicalKkaStokDetail::create([
                'physical_kka_id' => $kkaPoso->id,
                'sub_area' => $item['sub_area'],
                'kondisi_objek' => $item['objek'],
                'saldo_sistem' => $item['sistem'],
                'hasil_fisik' => $item['fisik'],
                'selisih' => $item['sistem'] - $item['fisik'],
                'status_uji' => 'Sesuai',
                'is_exception' => false,
                'penyebab' => null,
                'kondisi_aktual' => 'Fisik dan register berkesesuaian 100%',
                'simpulan_ra' => $item['simpulan'],
                'tanggal_uji' => $today,
                'impact' => 1,
                'likelihood' => 1,
                'critical_trigger' => false,
                'risk_score' => 0,
                'severity_level' => 'Rendah',
                'reviewer_id' => 'REV-SKAI',
                'status_review' => 'Approved',
                'catatan_reviewer' => 'Dokumen berharga tersimpan aman.',
            ]);
        }

        // TAB 4: REKONSILIASI KARTU ATM POSO
        $atmItemsPoso = [
            // Persediaan
            [
                'sub_area' => 'Persediaan Kartu ATM',
                'jenis_kartu' => 'Kartu ATM Debit Chip GPN',
                'keterangan_lokasi' => 'Brankas Khasanah KC Poso - Batch 2026/GPN-01',
                'saldo_nominatif' => '500',
                'hasil_fisik' => '500',
                'selisih' => '0',
                'kondisi' => 'Baik',
                'status_uji' => 'SESUAI',
                'simpulan_ra' => 'Fisik kartu tersegel dalam kardus original pabrikan.',
            ],
            [
                'sub_area' => 'Persediaan Kartu ATM',
                'jenis_kartu' => 'Kartu ATM Debit Prioritas',
                'keterangan_lokasi' => 'Brankas Khasanah KC Poso - Batch 2026/PRIO-01',
                'saldo_nominatif' => '100',
                'hasil_fisik' => '100',
                'selisih' => '0',
                'kondisi' => 'Baik',
                'status_uji' => 'SESUAI',
                'simpulan_ra' => 'Fisik kartu prioritas tersimpan rapi.',
            ],
            [
                'sub_area' => 'Persediaan Kartu ATM',
                'jenis_kartu' => 'Kartu ATM Instant CS Teller',
                'keterangan_lokasi' => 'Laci CS 1 (Dual Control Supervisor)',
                'saldo_nominatif' => '50',
                'hasil_fisik' => '50',
                'selisih' => '0',
                'kondisi' => 'Baik',
                'status_uji' => 'SESUAI',
                'simpulan_ra' => 'Stok harian CS sesuai limit kerja harian.',
            ],
            // 6 Prosedur Kontrol Wajib
            [
                'sub_area' => 'Kontrol',
                'jenis_kartu' => 'Jumlah kartu dan PIN mailer',
                'keterangan_lokasi' => 'Jumlah kartu ATM sepadan dengan PIN mailer/komponen terkait atau perbedaannya dapat dijelaskan.',
                'saldo_nominatif' => 'N/A',
                'hasil_fisik' => 'Sesuai dengan sistem PIN Mailer',
                'selisih' => 'N/A',
                'kondisi' => 'Sesuai',
                'status_uji' => 'SESUAI',
                'simpulan_ra' => 'Seluruh kartu disertai amplop PIN mailer tersegel.',
            ],
            [
                'sub_area' => 'Kontrol',
                'jenis_kartu' => 'Register penerimaan dan pengeluaran',
                'keterangan_lokasi' => 'Seluruh penerimaan, penyerahan, retur, dan pemusnahan tercatat lengkap dan mutakhir.',
                'saldo_nominatif' => 'N/A',
                'hasil_fisik' => 'Tercatat mutakhir',
                'selisih' => 'N/A',
                'kondisi' => 'Sesuai',
                'status_uji' => 'SESUAI',
                'simpulan_ra' => 'Buku register kartu ATM terisi lengkap per transaksi penyerahan.',
            ],
            [
                'sub_area' => 'Kontrol',
                'jenis_kartu' => 'Kontrol nomor seri/batch',
                'keterangan_lokasi' => 'Nomor seri atau batch dapat ditelusuri dari penerimaan sampai penyerahan kepada nasabah.',
                'saldo_nominatif' => 'N/A',
                'hasil_fisik' => 'Nomor seri runtut',
                'selisih' => 'N/A',
                'kondisi' => 'Sesuai',
                'status_uji' => 'SESUAI',
                'simpulan_ra' => 'Nomor seri kartu dapat ditelusuri ke formulir pembukaan rekening.',
            ],
            [
                'sub_area' => 'Kontrol',
                'jenis_kartu' => 'Kartu rusak/retur',
                'keterangan_lokasi' => 'Kartu rusak, gagal personalisasi, retur, dan kartu yang akan dimusnahkan dipisahkan serta dikendalikan.',
                'saldo_nominatif' => 'N/A',
                'hasil_fisik' => 'Nihil kartu rusak',
                'selisih' => 'N/A',
                'kondisi' => 'Sesuai',
                'status_uji' => 'SESUAI',
                'simpulan_ra' => 'Tidak terdapat kartu rusak atau kartu retur tertahan.',
            ],
            [
                'sub_area' => 'Kontrol',
                'jenis_kartu' => 'Penyimpanan dan akses',
                'keterangan_lokasi' => 'Persediaan disimpan aman; akses dibatasi dan menerapkan dual control sesuai ketentuan.',
                'saldo_nominatif' => 'N/A',
                'hasil_fisik' => 'Brankas dual control',
                'selisih' => 'N/A',
                'kondisi' => 'Sesuai',
                'status_uji' => 'SESUAI',
                'simpulan_ra' => 'Akses brankas kartu ATM dipegang Supervisor dan Teller Kas.',
            ],
            [
                'sub_area' => 'Kontrol',
                'jenis_kartu' => 'Bukti serah terima',
                'keterangan_lokasi' => 'Penyerahan kartu kepada nasabah atau unit lain didukung bukti yang sah dan sesuai tanggal nominatif.',
                'saldo_nominatif' => 'N/A',
                'hasil_fisik' => 'Tanda terima lengkap',
                'selisih' => 'N/A',
                'kondisi' => 'Sesuai',
                'status_uji' => 'SESUAI',
                'simpulan_ra' => 'Tanda terima nasabah tersimpan di berkas arsip harian.',
            ],
        ];

        $urutAtm = 1;
        foreach ($atmItemsPoso as $item) {
            PhysicalKkaKartuAtmDetail::create([
                'physical_kka_id' => $kkaPoso->id,
                'procedure_id' => 'FLD-WK-003-' . $dateCode . '-KATREC-' . str_pad($urutAtm++, 3, '0', STR_PAD_LEFT),
                'sub_area' => $item['sub_area'],
                'jenis_kartu' => $item['jenis_kartu'],
                'keterangan_lokasi' => $item['keterangan_lokasi'],
                'saldo_nominatif' => $item['saldo_nominatif'],
                'hasil_fisik' => $item['hasil_fisik'],
                'selisih' => $item['selisih'],
                'status_uji' => $item['status_uji'],
                'kondisi' => $item['kondisi'],
                'kriteria' => null,
                'penyebab' => null,
                'dampak' => null,
                'impact' => 1,
                'likelihood' => 1,
                'critical_trigger' => 'Tidak',
                'risk_score' => null,
                'severity_level' => null,
                'perlu_klarifikasi' => 'Tidak',
                'bukti_path' => null,
                'simpulan_ra' => $item['simpulan_ra'],
                'tanggal_uji' => $today,
                'ra_pelaksana' => $userNamePoso,
                'reviewer_id' => 'REV-SKAI',
                'status_review' => 'Approved',
                'validasi' => 'Hasil opname kartu ATM memadai.',
                'siap_register' => 'Tidak',
                'is_exception' => false,
            ]);
        }

        // TAB 5: REKONSILIASI BARANG CETAKAN POSO
        $bctItemsPoso = [
            ['kategori' => 'Formulir', 'nama_barang' => 'Kwitansi pencairan kredit', 'satuan' => 'Bh', 'sistem' => 150, 'fisik' => 150],
            ['kategori' => 'Formulir', 'nama_barang' => 'Slip setoran', 'satuan' => 'Bh', 'sistem' => 2000, 'fisik' => 2000],
            ['kategori' => 'Formulir', 'nama_barang' => 'Slip penarikan', 'satuan' => 'Bh', 'sistem' => 1500, 'fisik' => 1500],
            ['kategori' => 'Formulir', 'nama_barang' => 'Slip RTGS', 'satuan' => 'Bh', 'sistem' => 300, 'fisik' => 300],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan Simantap', 'satuan' => 'Bh', 'sistem' => 400, 'fisik' => 400],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan Simpeda', 'satuan' => 'Bh', 'sistem' => 500, 'fisik' => 500],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan TabunganKU', 'satuan' => 'Bh', 'sistem' => 250, 'fisik' => 250],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan PNS', 'satuan' => 'Bh', 'sistem' => 200, 'fisik' => 200],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan Tawa', 'satuan' => 'Bh', 'sistem' => 150, 'fisik' => 150],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan Pensiunku', 'satuan' => 'Bh', 'sistem' => 100, 'fisik' => 100],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan Simpel', 'satuan' => 'Bh', 'sistem' => 300, 'fisik' => 300],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tawa Plus', 'satuan' => 'Bh', 'sistem' => 80, 'fisik' => 80],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'TabunganKU Plus', 'satuan' => 'Bh', 'sistem' => 120, 'fisik' => 120],
            ['kategori' => 'Formulir', 'nama_barang' => 'Pembukaan rekening perorangan', 'satuan' => 'Bh', 'sistem' => 350, 'fisik' => 350],
            ['kategori' => 'Formulir', 'nama_barang' => 'Pembukaan rekening nonperorangan', 'satuan' => 'Bh', 'sistem' => 100, 'fisik' => 100],
            ['kategori' => 'Formulir', 'nama_barang' => 'Pembukaan rekening Tawa', 'satuan' => 'Bh', 'sistem' => 150, 'fisik' => 150],
            ['kategori' => 'Formulir', 'nama_barang' => 'Form keluhan nasabah', 'satuan' => 'Bh', 'sistem' => 100, 'fisik' => 100],
            ['kategori' => 'Formulir', 'nama_barang' => 'Form KYC', 'satuan' => 'Bh', 'sistem' => 300, 'fisik' => 300],
            ['kategori' => 'Kertas', 'nama_barang' => 'Kertas kop bank', 'satuan' => 'Rim', 'sistem' => 20, 'fisik' => 20],
            ['kategori' => 'Amplop', 'nama_barang' => 'Amplop kecil berlogo', 'satuan' => 'Pack', 'sistem' => 15, 'fisik' => 15],
            ['kategori' => 'Amplop', 'nama_barang' => 'Amplop panjang berlogo', 'satuan' => 'Pack', 'sistem' => 12, 'fisik' => 12],
            ['kategori' => 'Amplop', 'nama_barang' => 'Amplop sedang berlogo', 'satuan' => 'Pack', 'sistem' => 10, 'fisik' => 10],
            ['kategori' => 'Perlengkapan', 'nama_barang' => 'Signature Spectroline', 'satuan' => 'Dos', 'sistem' => 5, 'fisik' => 5],
            ['kategori' => 'Perlengkapan', 'nama_barang' => 'Invisible Signature Spectroline', 'satuan' => 'Dos', 'sistem' => 3, 'fisik' => 3],
            ['kategori' => 'Prioritas', 'nama_barang' => 'Amplop nasabah prioritas', 'satuan' => 'Bh', 'sistem' => 50, 'fisik' => 50],
            ['kategori' => 'Prioritas', 'nama_barang' => 'Form pembukaan nasabah prioritas', 'satuan' => 'Bh', 'sistem' => 50, 'fisik' => 50],
            ['kategori' => 'Perlengkapan', 'nama_barang' => 'Pita printer tally', 'satuan' => 'Bh', 'sistem' => 10, 'fisik' => 10],
        ];

        $urutBct = 1;
        foreach ($bctItemsPoso as $item) {
            PhysicalKkaBarangCetakanDetail::create([
                'physical_kka_id' => $kkaPoso->id,
                'procedure_id' => 'FLD-WK-003-' . $dateCode . '-BCTREC-' . str_pad($urutBct++, 3, '0', STR_PAD_LEFT),
                'kategori' => $item['kategori'],
                'nama_barang' => $item['nama_barang'],
                'satuan' => $item['satuan'],
                'stok_sistem' => $item['sistem'],
                'hasil_fisik' => $item['fisik'],
                'selisih' => $item['sistem'] - $item['fisik'],
                'status_uji' => 'SESUAI',
                'kondisi' => 'Baik',
                'kriteria' => null,
                'penyebab' => null,
                'dampak' => null,
                'impact' => 1,
                'likelihood' => 1,
                'critical_trigger' => 'Tidak',
                'risk_score' => null,
                'severity_level' => null,
                'perlu_klarifikasi' => 'Tidak',
                'bukti_path' => null,
                'simpulan_ra' => 'Stok fisik BCT klop dengan kartu persediaan.',
                'tanggal_uji' => $today,
                'ra_pelaksana' => $userNamePoso,
                'reviewer_id' => 'REV-SKAI',
                'status_review' => 'Approved',
                'validasi' => 'Stok barang cetakan akurat.',
                'siap_register' => 'Tidak',
                'is_exception' => false,
            ]);
        }

        // Pastikan Kontrol Mingguan juga tersedia
        KontrolMingguan::getOrCreateForUnit('003');

        // =========================================================================
        // 2. DUMMY KKA FISIK UNTUK KANTOR CABANG UTAMA PALU (Kode Unit: 001)
        // =========================================================================
        $userPalu = User::where('email', 'ra1.palu@banksulteng.co.id')->first()
                 ?? User::where('role', 'ra')->where('cabang_id', 2)->first()
                 ?? User::find(2);

        $userIdPalu = $userPalu ? $userPalu->id : 2;
        $userNamePalu = $userPalu ? $userPalu->name : 'Januar';

        PhysicalKka::where('kode_unit', '001')->orWhere('kode_unit', 'BS-001')->delete();

        $kkaPalu = PhysicalKka::create([
            'kode_unit' => '001',
            'user_id' => $userIdPalu,
            'tanggal_pemeriksaan' => $today,
            'status_final' => 'Approved',
            'surat_izin_path' => 'surat_izin/surat_tugas_fisik_kcu_palu.pdf',
            'status_surat' => 'Approved',
            'catatan_surat' => 'Surat tugas fisik harian KCU Palu lengkap dan telah disetujui Kadiv SKAI.',
        ]);

        // Cash Opname KCU Palu
        $cashDataPalu = [
            ['sub_area' => 'Khasanah', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 100.000', 'sistem' => 750000000, 'fisik' => 750000000, 'simpulan' => 'Kas khasanah KCU Palu klop'],
            ['sub_area' => 'Khasanah', 'objek' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 50.000', 'sistem' => 300000000, 'fisik' => 300000000, 'simpulan' => 'Kas fisik Rp 50.000 klop'],
            ['sub_area' => 'Khasanah', 'objek' => 'Rekonsiliasi total khasanah', 'pecahan' => 'Total', 'sistem' => 1050000000, 'fisik' => 1050000000, 'simpulan' => 'Total khasanah klop 100% dengan CBS'],
            ['sub_area' => 'ATM', 'objek' => 'ATM 001-CRM Galeri KCU', 'pecahan' => '4 Kaset x Rp 100.000', 'sistem' => 200000000, 'fisik' => 200000000, 'simpulan' => 'Kas kaset ATM CRM sesuai sistem'],
        ];

        foreach ($cashDataPalu as $item) {
            PhysicalKkaCashDetail::create([
                'physical_kka_id' => $kkaPalu->id,
                'sub_area' => $item['sub_area'],
                'objek_persediaan' => $item['objek'],
                'pecahan' => $item['pecahan'],
                'saldo_sistem' => $item['sistem'],
                'hasil_fisik' => $item['fisik'],
                'selisih' => 0,
                'status_uji' => 'Sesuai',
                'is_exception' => false,
                'penyebab' => null,
                'simpulan_ra' => $item['simpulan'],
                'tanggal_uji' => $today,
                'impact' => 1,
                'likelihood' => 1,
                'critical_trigger' => false,
                'risk_score' => 0,
                'severity_level' => 'Rendah',
                'reviewer_id' => 'REV-SKAI',
                'status_review' => 'Approved',
                'catatan_reviewer' => 'Pemeriksaan fisik KCU Palu terverifikasi memadai.',
            ]);
        }

        // Obs KCU Palu
        PhysicalKkaObsDetail::create([
            'physical_kka_id' => $kkaPalu->id,
            'area_observasi' => 'Keamanan',
            'kriteria_diharapkan' => 'Petugas keamanan dan buku tamu',
            'hasil_observasi' => 'Sesuai',
            'kondisi_aktual' => 'Pengamanan 24 jam shift bergilir, register buku tamu lengkap',
            'status_uji' => 'Sesuai',
            'is_exception' => false,
            'penyebab' => null,
            'simpulan_ra' => 'Keamanan KCU terpantau sangat baik.',
            'tanggal_uji' => $today,
            'impact' => 1,
            'likelihood' => 1,
            'critical_trigger' => false,
            'risk_score' => 0,
            'severity_level' => 'Rendah',
            'reviewer_id' => 'REV-SKAI',
            'status_review' => 'Approved',
            'catatan_reviewer' => 'Memadai.',
        ]);

        // Stok KCU Palu
        PhysicalKkaStokDetail::create([
            'physical_kka_id' => $kkaPalu->id,
            'sub_area' => 'Cek',
            'kondisi_objek' => 'Buku cek kosong/belum digunakan | Seri KCU-001 s/d KCU-200',
            'saldo_sistem' => 200,
            'hasil_fisik' => 200,
            'selisih' => 0,
            'status_uji' => 'Sesuai',
            'is_exception' => false,
            'penyebab' => null,
            'kondisi_aktual' => 'Fisik buku cek tersimpan di khasanah KCU',
            'simpulan_ra' => '200 buku cek lengkap.',
            'tanggal_uji' => $today,
            'impact' => 1,
            'likelihood' => 1,
            'critical_trigger' => false,
            'risk_score' => 0,
            'severity_level' => 'Rendah',
            'reviewer_id' => 'REV-SKAI',
            'status_review' => 'Approved',
            'catatan_reviewer' => 'Memadai.',
        ]);

        // Kartu ATM KCU Palu
        PhysicalKkaKartuAtmDetail::create([
            'physical_kka_id' => $kkaPalu->id,
            'procedure_id' => 'FLD-WK-001-' . $dateCode . '-KATREC-001',
            'sub_area' => 'Persediaan Kartu ATM',
            'jenis_kartu' => 'Kartu ATM Chip GPN KCU Palu',
            'keterangan_lokasi' => 'Khasanah KCU Palu',
            'saldo_nominatif' => '1500',
            'hasil_fisik' => '1500',
            'selisih' => '0',
            'status_uji' => 'SESUAI',
            'kondisi' => 'Baik',
            'simpulan_ra' => 'Stok kartu ATM KCU Palu lengkap tersegel.',
            'tanggal_uji' => $today,
            'ra_pelaksana' => $userNamePalu,
            'reviewer_id' => 'REV-SKAI',
            'status_review' => 'Approved',
            'validasi' => 'Memadai.',
            'siap_register' => 'Tidak',
            'is_exception' => false,
        ]);

        // BCT KCU Palu
        PhysicalKkaBarangCetakanDetail::create([
            'physical_kka_id' => $kkaPalu->id,
            'procedure_id' => 'FLD-WK-001-' . $dateCode . '-BCTREC-001',
            'kategori' => 'Buku Tabungan',
            'nama_barang' => 'Tabungan Simantap KCU',
            'satuan' => 'Bh',
            'stok_sistem' => 1000,
            'hasil_fisik' => 1000,
            'selisih' => 0,
            'status_uji' => 'SESUAI',
            'kondisi' => 'Baik',
            'simpulan_ra' => 'Stok buku tabungan KCU sesuai kartu persediaan.',
            'tanggal_uji' => $today,
            'ra_pelaksana' => $userNamePalu,
            'reviewer_id' => 'REV-SKAI',
            'status_review' => 'Approved',
            'validasi' => 'Memadai.',
            'siap_register' => 'Tidak',
            'is_exception' => false,
        ]);

        KontrolMingguan::getOrCreateForUnit('001');

        $this->command->info("Dummy data KKA Fisik untuk KC Poso & KCU Palu berhasil diisi lengkap.");
    }
}
