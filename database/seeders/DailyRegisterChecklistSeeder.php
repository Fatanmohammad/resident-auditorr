<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\DailyRegister\DailyRegisterChecklist;

class DailyRegisterChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // ==========================================
            // TAB 1: KKA Teller & Kas (13 Item)
            // ==========================================
            // Sub-Area A1. Transaksi Teller (No. 1–6)
            ['KKA_Teller_Kas', 'A1', 'Transaksi Teller', 1, 'Voucher slip setoran / slip penarikan / pemindahbukuan (nota debet/kredit)'],
            ['KKA_Teller_Kas', 'A1', 'Transaksi Teller', 2, 'Bukti otorisasi transaksi (tanda tangan pejabat berwenang)'],
            ['KKA_Teller_Kas', 'A1', 'Transaksi Teller', 3, 'Kesesuaian nominal voucher dengan sistem/register'],
            ['KKA_Teller_Kas', 'A1', 'Transaksi Teller', 4, 'Validasi mesin teller pada voucher'],
            ['KKA_Teller_Kas', 'A1', 'Transaksi Teller', 5, 'Tanda tangan nasabah di voucher'],
            ['KKA_Teller_Kas', 'A1', 'Transaksi Teller', 6, 'Fotokopi identitas nasabah (transaksi ≥ Rp100 juta)'],

            // Sub-Area A2. Kas Kecil (No. 1–5)
            ['KKA_Teller_Kas', 'A2', 'Kas Kecil', 1, 'Buku kas kecil / register kas kecil'],
            ['KKA_Teller_Kas', 'A2', 'Kas Kecil', 2, 'Bukti pengeluaran kas kecil (kuitansi, nota)'],
            ['KKA_Teller_Kas', 'A2', 'Kas Kecil', 3, 'Otorisasi pengeluaran kas kecil'],
            ['KKA_Teller_Kas', 'A2', 'Kas Kecil', 4, 'Berita acara opname kas kecil'],
            ['KKA_Teller_Kas', 'A2', 'Kas Kecil', 5, 'Kesesuaian saldo fisik dengan catatan'],

            // Sub-Area A3. Kas / Khasanah (No. 1–2)
            ['KKA_Teller_Kas', 'A3', 'Kas / Khasanah', 1, 'Berita acara opname khasanah (BA harian)'],
            ['KKA_Teller_Kas', 'A3', 'Kas / Khasanah', 2, 'Akses kunci khasanah & Kombinasi brankas (dual control — 2 orang berbeda)'],

            // ==========================================
            // TAB 4: KKA DPK & Layanan (29 Item)
            // ==========================================
            // Sub-Area D1. Pembukaan Rekening & CIF (Perorangan) (No. 1–10)
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 1, 'Formulir pembukaan rekening (ditandatangani nasabah)'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 2, 'KTP nasabah (fotokopi, verifikasi keaslian)'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 3, 'NPWP nasabah (jika ada / surat pernyataan tidak memiliki NPWP)'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 4, 'Verifikasi data nasabah (face match / Dukcapil)'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 5, 'Spesimen tanda tangan nasabah'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 6, 'Formulir profil risiko nasabah (CDD/EDD)'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 7, 'Setoran awal sesuai ketentuan jenis tabungan'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 8, 'Buku tabungan / sertifikat deposito diserahkan ke nasabah'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 9, 'Tanda terima buku tabungan / kartu ATM'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 10, 'Otorisasi pembukaan rekening oleh pejabat berwenang'],

            // Sub-Area D2. Pembukaan Rekening & CIF (Badan Usaha / Non-Perorangan) (No. 11–18)
            ['KKA_DPK_Layanan', 'D2', 'Pembukaan Rekening & CIF (Badan Usaha)', 11, 'Akta pendirian perusahaan + perubahan terakhir'],
            ['KKA_DPK_Layanan', 'D2', 'Pembukaan Rekening & CIF (Badan Usaha)', 12, 'SK Kemenkumham (pengesahan badan hukum)'],
            ['KKA_DPK_Layanan', 'D2', 'Pembukaan Rekening & CIF (Badan Usaha)', 13, 'NIB / SIUP / TDP / Izin usaha yang masih berlaku'],
            ['KKA_DPK_Layanan', 'D2', 'Pembukaan Rekening & CIF (Badan Usaha)', 14, 'NPWP badan usaha'],
            ['KKA_DPK_Layanan', 'D2', 'Pembukaan Rekening & CIF (Badan Usaha)', 15, 'KTP pengurus / direksi yang berwenang'],
            ['KKA_DPK_Layanan', 'D2', 'Pembukaan Rekening & CIF (Badan Usaha)', 16, 'Surat kuasa penunjukan pengelola rekening (jika dikuasakan)'],
            ['KKA_DPK_Layanan', 'D2', 'Pembukaan Rekening & CIF (Badan Usaha)', 17, 'Formulir EDD (Enhanced Due Diligence) badan usaha'],
            ['KKA_DPK_Layanan', 'D2', 'Pembukaan Rekening & CIF (Badan Usaha)', 18, 'Otorisasi pembukaan rekening badan usaha'],

            // Sub-Area D3. Perubahan Data Nasabah & Rekening Dorman (No. 19–25)
            ['KKA_DPK_Layanan', 'D3', 'Perubahan Data & Rekening Dorman', 19, 'Formulir permohonan perubahan data (alamat, no telp, dll)'],
            ['KKA_DPK_Layanan', 'D3', 'Perubahan Data & Rekening Dorman', 20, 'Dokumen pendukung perubahan data'],
            ['KKA_DPK_Layanan', 'D3', 'Perubahan Data & Rekening Dorman', 21, 'Otorisasi perubahan data di sistem'],
            ['KKA_DPK_Layanan', 'D3', 'Perubahan Data & Rekening Dorman', 22, 'Permohonan pengaktifan rekening dorman (oleh nasabah sendiri)'],
            ['KKA_DPK_Layanan', 'D3', 'Perubahan Data & Rekening Dorman', 23, 'Verifikasi identitas nasabah saat reaktivasi dorman'],
            ['KKA_DPK_Layanan', 'D3', 'Perubahan Data & Rekening Dorman', 24, 'Transaksi pertama setelah reaktivasi dorman (setoran/penarikan)'],
            ['KKA_DPK_Layanan', 'D3', 'Perubahan Data & Rekening Dorman', 25, 'Otorisasi reaktivasi rekening dorman'],

            // Sub-Area D4. Penutupan Rekening (No. 26–29)
            ['KKA_DPK_Layanan', 'D4', 'Penutupan Rekening', 26, 'Formulir permohonan penutupan rekening'],
            ['KKA_DPK_Layanan', 'D4', 'Penutupan Rekening', 27, 'Buku tabungan asli diserahkan dan digunting'],
            ['KKA_DPK_Layanan', 'D4', 'Penutupan Rekening', 28, 'Kartu ATM dikembalikan dan dipotong/dimusnahkan'],
            ['KKA_DPK_Layanan', 'D4', 'Penutupan Rekening', 29, 'Saldo sisa ditarik/ditransfer & status rekening closed'],

            // ==========================================
            // TAB 5: KKA Kredit (Resmi Bank Sulteng: 3 Kategori) (83 Item)
            // ==========================================
            // Kategori 1: Kredit PNS / ASN (80% & 100%) (No. 1–20)
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 1, 'Surat Permohonan Kredit PNS / ASN'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 2, 'Fotokopi KTP Pemohon (Debitur) dan Pasangan'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 3, 'Fotokopi Kartu Keluarga (KK)'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 4, 'Fotokopi Surat Nikah / Akta Cerai'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 5, 'Fotokopi NPWP Pribadi'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 6, 'SK Pengangkatan CPNS (80%)'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 7, 'SK Pengangkatan PNS (100%)'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 8, 'SK Kenaikan Pangkat / Golongan Terakhir'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 9, 'Kartu Pegawai (Karpeg)'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 10, 'Kartu Peserta Taspen'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 11, 'Slip Gaji / Daftar Gaji Terakhir yang Dilegalisir Bendahara'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 12, 'Surat Pernyataan Kuasa Memotong Gaji (Debitur & Bendahara)'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 13, 'Surat Rekomendasi / Persetujuan Atasan Langsung & Bendahara Gaji'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 14, 'Hasil Penilaian BI-Checking / SLIK OJK'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 15, 'NPU (Nota Permohonan Usulan / Analisis Kredit)'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 16, 'SPPK (Surat Penawaran Persetujuan Kredit)'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 17, 'PK (Perjanjian Kredit) ditandatangani Debitur dan Pejabat Bank'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 18, 'BA Serah Terima Jaminan (Debitur -> Analis -> Admin)'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 19, 'Asuransi Jiwa Kredit & Bukti Pembayaran Premi'],
            ['KKA_Kredit', 'C1', 'Kredit PNS / ASN (80% & 100%)', 20, 'Pas Foto Pemohon dan Pasangan'],

            // Kategori 2: KPR / FLPP (37 syarat + NPU, SPPK, PK, BA Serah Terima Jaminan) (No. 1–41)
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 1, 'Formulir Aplikasi Permohonan KPR Sejahtera FLPP'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 2, 'Pas Foto Pemohon dan Pasangan'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 3, 'Fotokopi e-KTP Pemohon dan Pasangan'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 4, 'Fotokopi Kartu Keluarga (KK)'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 5, 'Fotokopi Buku Nikah / Akta Cerai / Surat Keterangan Belum Menikah'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 6, 'Fotokopi NPWP Pribadi'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 7, 'Fotokopi SPT Tahunan PPh Orang Pribadi'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 8, 'Surat Pernyataan Pemohon belum pernah memiliki rumah'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 9, 'Surat Pernyataan Pemohon belum pernah menerima subsidi perumahan pemerintah'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 10, 'Surat Keterangan Penghasilan / Slip Gaji Asli 3 bulan terakhir'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 11, 'Rekening Koran / Tabungan Bank Sulteng 3 bulan terakhir'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 12, 'Surat Keterangan Kerja / SK Pegawai Aktif'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 13, 'Surat Keterangan Domisili dari Kelurahan/Desa'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 14, 'Hasil Verifikasi Aplikasi SiKasep / BP Tapera'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 15, 'Formulir Konfirmasi Pemohon KPR Sejahtera FLPP'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 16, 'Salinan Sertifikat Pecahan / Induk (SHM / SHGB) atas nama Pengembang/Debitur'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 17, 'IMB (Izin Mendirikan Bangunan) / PBG (Persetujuan Bangunan Gedung)'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 18, 'Salinan PBB tahun berjalan beserta Bukti Lunas'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 19, 'Surat Penawaran Rumah dari Pengembang (Developer)'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 20, 'Site plan perumahan yang telah disahkan dinas berwenang'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 21, 'Sertifikat Laik Fungsi (SLF) Bangunan Rumah'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 22, 'Berita Acara Pemeriksaan Fisik Bangunan (Opname Fisik 100%)'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 23, 'Gambar denah dan spesifikasi teknis bangunan rumah'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 24, 'Dokumentasi foto fisik bangunan rumah (depan, ruang dalam, toilet, jalan lingkungan)'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 25, 'Data titik koordinat GPS lokasi rumah subsidi'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 26, 'Kuitansi Asli Pembayaran Uang Muka (Down Payment / DP)'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 27, 'Perjanjian Pengikatan Jual Beli (PPJB)'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 28, 'Akta Jual Beli (AJB) dari Notaris / PPAT'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 29, 'Surat Kuasa Membebankan Hak Tanggungan (SKMHT) / Akta Pemberian Hak Tanggungan (APHT)'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 30, 'Polis Asuransi Jiwa Kredit Debitur'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 31, 'Polis Asuransi Kebakaran atas Objek Agunan Rumah'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 32, 'Sertifikat Hak Tanggungan (SHT) dari Kantor Pertanahan (BPN)'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 33, 'Surat Tanda Terima Berkas Pendaftaran Hak Tanggungan ke BPN'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 34, 'Surat Pernyataan Debitur wajib menghuni rumah subsidi min. 5 tahun'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 35, 'Surat Pernyataan Kesanggupan Membayar Angsuran melalui Autodebet'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 36, 'Bukti Pembukaan Rekening Tabungan & Rekening Titipan Subsidi di Bank Sulteng'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 37, 'Surat Rekomendasi Asosiasi Pengembang Perumahan (REI/Apersi/Himperra)'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 38, 'NPU (Nota Permohonan Usulan / Analisis Kredit)'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 39, 'SPPK (Surat Penawaran Persetujuan Kredit)'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 40, 'PK (Perjanjian Kredit KPR FLPP)'],
            ['KKA_Kredit', 'C2', 'KPR / FLPP', 41, 'BA Serah Terima Jaminan (Debitur -> Analis -> Admin)'],

            // Kategori 3: Kredit Usaha / Konstruksi (22 syarat termasuk Cessie, Termyn, IUJKN, SBUJPK) (No. 1–22)
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 1, 'Formulir Permohonan Kredit Usaha / Jasa Konstruksi'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 2, 'Profil Perusahaan (Company Profile) dan Riwayat Pengalaman Kerja Proyek'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 3, 'Fotokopi e-KTP Pengurus, Direksi, Komisaris, dan Pemegang Saham'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 4, 'NPWP Perusahaan dan NPWP Pengurus'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 5, 'Akta Pendirian Perusahaan beserta Perubahan Terakhir dan Pengesahan Kemenkumham'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 6, 'Nomor Induk Berusaha (NIB) / SIUP / TDP yang masih berlaku'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 7, 'IUJKN (Izin Usaha Jasa Konstruksi Nasional) yang masih berlaku'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 8, 'SBUJPK (Sertifikat Badan Usaha Jasa Pelaksana Konstruksi) dari LPJK'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 9, 'Sertifikat Keahlian (SKA) / Sertifikat Keterampilan (SKT) Tenaga Ahli'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 10, 'Rekening Koran Perusahaan 6 Bulan Terakhir'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 11, 'Laporan Keuangan Perusahaan 2 Tahun Terakhir (Audited / In-house)'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 12, 'Kontrak Kerja / Surat Perjanjian Pemborongan / Surat Perintah Kerja (SPK)'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 13, 'Surat Pengumuman Pemenang Tender / Surat Penunjukan Penyedia Barang/Jasa (SPPBJ)'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 14, 'Jadwal Pelaksanaan Proyek (Time Schedule / Kurva S)'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 15, 'Rencana Anggaran Biaya (RAB) Proyek Konstruksi'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 16, 'Akta Notariil Cessie (Pengalihan Hak Tagih Pembayaran Proyek ke Bank Sulteng)'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 17, 'Surat Pemberitahuan dan Persetujuan Cessie dari Pemilik Proyek (Bouwheer / PPK)'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 18, 'Berita Acara Prestasi / Kemajuan Fisik Proyek dan Berita Acara Pembayaran Termyn'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 19, 'Mekanisme Pembayaran Proyek melalui Rekening Penampung (Escrow Account di Bank Sulteng)'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 20, 'Dokumen Agunan Tambahan (SHM/SHGB/BPKB/Deposito) & Laporan Penilaian Agunan (Appraisal)'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 21, 'Pengikatan Agunan secara Notariil (APHT / SKMHT / Fidusia)'],
            ['KKA_Kredit', 'C3', 'Kredit Usaha / Konstruksi', 22, 'Polis Asuransi Konstruksi (Contractor\'s All Risks / CAR) & Polis Asuransi Agunan'],

            // ==========================================
            // TAB 6: KKA Biaya & Beban (25 Item)
            // ==========================================
            // Sub-Area E1. Beban Operasional Non-Bunga (No. 1–10)
            ['KKA_Biaya_Beban', 'E1', 'Beban Operasional Non-Bunga', 1, 'Kuitansi / invoice / bukti tagihan pihak ketiga'],
            ['KKA_Biaya_Beban', 'E1', 'Beban Operasional Non-Bunga', 2, 'Kesesuaian otorisasi pengeluaran beban (limit wewenang)'],
            ['KKA_Biaya_Beban', 'E1', 'Beban Operasional Non-Bunga', 3, 'Kesesuaian akun COA / pos biaya yang didebet'],
            ['KKA_Biaya_Beban', 'E1', 'Beban Operasional Non-Bunga', 4, 'Kelengkapan dokumen pendukung (PO, SPK, BA serah terima)'],
            ['KKA_Biaya_Beban', 'E1', 'Beban Operasional Non-Bunga', 5, 'Kesesuaian nominal transaksi dengan anggaran (budget vs aktual)'],
            ['KKA_Biaya_Beban', 'E1', 'Beban Operasional Non-Bunga', 6, 'Perhitungan dan pemotongan pajak (PPh 21, PPh 23, PPN)'],
            ['KKA_Biaya_Beban', 'E1', 'Beban Operasional Non-Bunga', 7, 'Bukti potong pajak dan bukti setor pajak (NTPN)'],
            ['KKA_Biaya_Beban', 'E1', 'Beban Operasional Non-Bunga', 8, 'Kewajaran harga pengadaan barang/jasa (prinsip efisiensi)'],
            ['KKA_Biaya_Beban', 'E1', 'Beban Operasional Non-Bunga', 9, 'Realisasi biaya promosi / iklan / CSR sesuai ketentuan'],
            ['KKA_Biaya_Beban', 'E1', 'Beban Operasional Non-Bunga', 10, 'Bukti pembayaran (transfer / cek / bilyet giro)'],

            // Sub-Area E2. Biaya Perjalanan Dinas (No. 11–17)
            ['KKA_Biaya_Beban', 'E2', 'Biaya Perjalanan Dinas', 11, 'Surat Perintah Perjalanan Dinas (SPPD) ditandatangani pejabat'],
            ['KKA_Biaya_Beban', 'E2', 'Biaya Perjalanan Dinas', 12, 'Tiket transportasi (pesawat, kereta, travel) asli / e-ticket'],
            ['KKA_Biaya_Beban', 'E2', 'Biaya Perjalanan Dinas', 13, 'Boarding pass / bukti perjalanan asli'],
            ['KKA_Biaya_Beban', 'E2', 'Biaya Perjalanan Dinas', 14, 'Tagihan / invoice hotel/penginapan asli'],
            ['KKA_Biaya_Beban', 'E2', 'Biaya Perjalanan Dinas', 15, 'Kesesuaian uang harian / lumpsum dengan standar tarif'],
            ['KKA_Biaya_Beban', 'E2', 'Biaya Perjalanan Dinas', 16, 'Laporan hasil perjalanan dinas'],
            ['KKA_Biaya_Beban', 'E2', 'Biaya Perjalanan Dinas', 17, 'Formulir pertanggungjawaban panjar kerja dinas'],

            // Sub-Area E3. Biaya Pemeliharaan & Umum (No. 18–25)
            ['KKA_Biaya_Beban', 'E3', 'Biaya Pemeliharaan & Umum', 18, 'Tagihan listrik (PLN), air (PDAM), telepon/internet'],
            ['KKA_Biaya_Beban', 'E3', 'Biaya Pemeliharaan & Umum', 19, 'Kesesuaian bukti bayar utilitas dengan meteran/faktur'],
            ['KKA_Biaya_Beban', 'E3', 'Biaya Pemeliharaan & Umum', 20, 'Surat perjanjian sewa gedung / kantor (jika status sewa)'],
            ['KKA_Biaya_Beban', 'E3', 'Biaya Pemeliharaan & Umum', 21, 'Pembayaran sewa gedung dan pemotongan PPh 4 ayat (2)'],
            ['KKA_Biaya_Beban', 'E3', 'Biaya Pemeliharaan & Umum', 22, 'SPK pemeliharaan gedung / kendaraan / peralatan kantor'],
            ['KKA_Biaya_Beban', 'E3', 'Biaya Pemeliharaan & Umum', 23, 'Kuitansi servis kendaraan dinas + rincian spare part'],
            ['KKA_Biaya_Beban', 'E3', 'Biaya Pemeliharaan & Umum', 24, 'Log book penggunaan kendaraan dinas dan BBM'],
            ['KKA_Biaya_Beban', 'E3', 'Biaya Pemeliharaan & Umum', 25, 'Biaya premi asuransi aset (gedung, kendaraan, kas/CIS)'],

            // ==========================================
            // TAB 7: KKA Kartu ATM (30 Item)
            // ==========================================
            // Sub-Area B1. Pengelolaan Fisik ATM (Offsite & Onsite) (No. 1–8)
            ['KKA_Kartu_ATM', 'B1', 'Pengelolaan Fisik ATM', 1, 'Kondisi fisik mesin ATM (bersih, tidak rusak, keypad utuh)'],
            ['KKA_Kartu_ATM', 'B1', 'Pengelolaan Fisik ATM', 2, 'CCTV ATM berfungsi dan mengarah ke area aman (bukan keypad)'],
            ['KKA_Kartu_ATM', 'B1', 'Pengelolaan Fisik ATM', 3, 'Skimming detection / pin guard terpasang'],
            ['KKA_Kartu_ATM', 'B1', 'Pengelolaan Fisik ATM', 4, 'Stiker call center resmi terpasang (bukan nomor palsu)'],
            ['KKA_Kartu_ATM', 'B1', 'Pengelolaan Fisik ATM', 5, 'Kondisi ruang ATM (penerangan, AC, pintu kaca)'],
            ['KKA_Kartu_ATM', 'B1', 'Pengelolaan Fisik ATM', 6, 'APAR (alat pemadam api) tersedia dan belum expire'],
            ['KKA_Kartu_ATM', 'B1', 'Pengelolaan Fisik ATM', 7, 'Kunci fascia dan brankas ATM dipegang petugas berbeda (dual control)'],
            ['KKA_Kartu_ATM', 'B1', 'Pengelolaan Fisik ATM', 8, 'Kombinasi brankas ATM diubah secara berkala'],

            // Sub-Area B2. Kas Opname ATM & Replenishment (No. 9–16)
            ['KKA_Kartu_ATM', 'B2', 'Kas Opname ATM & Replenishment', 9, 'Fisik uang dalam cassette ATM'],
            ['KKA_Kartu_ATM', 'B2', 'Kas Opname ATM & Replenishment', 10, 'Saldo ATM di sistem vs fisik'],
            ['KKA_Kartu_ATM', 'B2', 'Kas Opname ATM & Replenishment', 11, 'Selisih kas ATM (jika ada)'],
            ['KKA_Kartu_ATM', 'B2', 'Kas Opname ATM & Replenishment', 12, 'Dokumen serah terima kas ATM (dual control)'],
            ['KKA_Kartu_ATM', 'B2', 'Kas Opname ATM & Replenishment', 13, 'Jadwal replenishment vs realisasi'],
            ['KKA_Kartu_ATM', 'B2', 'Kas Opname ATM & Replenishment', 14, 'Status gangguan/downtime ATM'],
            ['KKA_Kartu_ATM', 'B2', 'Kas Opname ATM & Replenishment', 15, 'Laporan monitoring ATM (uptime/downtime)'],
            ['KKA_Kartu_ATM', 'B2', 'Kas Opname ATM & Replenishment', 16, 'Tindak lanjut atas gangguan ATM'],

            // Sub-Area B3. Pengaktifan/Distribusi Kartu ATM (No. 17–24)
            ['KKA_Kartu_ATM', 'B3', 'Pengaktifan/Distribusi Kartu ATM', 17, 'Formulir permohonan kartu ATM (asli, tanda tangan nasabah)'],
            ['KKA_Kartu_ATM', 'B3', 'Pengaktifan/Distribusi Kartu ATM', 18, 'Register kartu ATM keluar/masuk (stock opname)'],
            ['KKA_Kartu_ATM', 'B3', 'Pengaktifan/Distribusi Kartu ATM', 19, 'Otorisasi pengaktifan kartu'],
            ['KKA_Kartu_ATM', 'B3', 'Pengaktifan/Distribusi Kartu ATM', 20, 'Tanda terima kartu oleh nasabah'],
            ['KKA_Kartu_ATM', 'B3', 'Pengaktifan/Distribusi Kartu ATM', 21, 'PIN mailer — penyerahan terpisah dari kartu'],
            ['KKA_Kartu_ATM', 'B3', 'Pengaktifan/Distribusi Kartu ATM', 22, 'Kartu ATM rusak/expire — berita acara pemusnahan'],
            ['KKA_Kartu_ATM', 'B3', 'Pengaktifan/Distribusi Kartu ATM', 23, 'Stok kartu ATM blank — penyimpanan aman'],
            ['KKA_Kartu_ATM', 'B3', 'Pengaktifan/Distribusi Kartu ATM', 24, 'Register kartu ATM yang diblokir/ditutup'],

            // Sub-Area B4. Kontrol TI Dasar (No. 25–30)
            ['KKA_Kartu_ATM', 'B4', 'Kontrol TI Dasar', 25, 'Hak akses user CBS (user ID, level otorisasi)'],
            ['KKA_Kartu_ATM', 'B4', 'Kontrol TI Dasar', 26, 'User ID yang sudah tidak aktif / pegawai mutasi-resign'],
            ['KKA_Kartu_ATM', 'B4', 'Kontrol TI Dasar', 27, 'Password policy (penggantian berkala)'],
            ['KKA_Kartu_ATM', 'B4', 'Kontrol TI Dasar', 28, 'Log audit trail transaksi'],
            ['KKA_Kartu_ATM', 'B4', 'Kontrol TI Dasar', 29, 'Backup data harian'],
            ['KKA_Kartu_ATM', 'B4', 'Kontrol TI Dasar', 30, 'Keamanan server room / perangkat TI kantor'],

            // ==========================================
            // TAB 8: KKA Pengaduan (E.4.5 KELUHAN NASABAH) (9 Item)
            // ==========================================
            ['KKA_Pengaduan', 'D5', 'Pengaduan / Keluhan Nasabah (E.4.5)', 1, 'Register Buku Keluhan Nasabah (No. Registrasi / No. Arsip)'],
            ['KKA_Pengaduan', 'D5', 'Pengaduan / Keluhan Nasabah (E.4.5)', 2, 'Formulir Pengaduan / Surat Pengaduan Tertulis dari Nasabah'],
            ['KKA_Pengaduan', 'D5', 'Pengaduan / Keluhan Nasabah (E.4.5)', 3, 'Identitas Pengadu (KTP, Buku Rekening / Bukti Kepemilikan Rekening)'],
            ['KKA_Pengaduan', 'D5', 'Pengaduan / Keluhan Nasabah (E.4.5)', 4, 'Tanggal Penerimaan Pengaduan (Tgl Keluhan)'],
            ['KKA_Pengaduan', 'D5', 'Pengaduan / Keluhan Nasabah (E.4.5)', 5, 'Bukti Investigasi Transaksi / Log Jurnal Core Banking System (CBS)'],
            ['KKA_Pengaduan', 'D5', 'Pengaduan / Keluhan Nasabah (E.4.5)', 6, 'Tanggal Pembukuan / Penyelesaian Pengaduan (Tgl Buku)'],
            ['KKA_Pengaduan', 'D5', 'Pengaduan / Keluhan Nasabah (E.4.5)', 7, 'Surat Jawaban / Tanggapan Resmi Penyelesaian kepada Nasabah'],
            ['KKA_Pengaduan', 'D5', 'Pengaduan / Keluhan Nasabah (E.4.5)', 8, 'Perhitungan Lama Hari Kerja Penyelesaian (NETWORKDAYS) ≤ SLA Ketentuan'],
            ['KKA_Pengaduan', 'D5', 'Pengaduan / Keluhan Nasabah (E.4.5)', 9, 'Status Pengaduan (Selesai / Reject / Ditolak) beserta Berita Acara Hasil Penyelesaian'],
        ];

        DB::beginTransaction();
        try {
            DailyRegisterChecklist::query()->delete();

            $sortOrder = 1;
            foreach ($items as $item) {
                DailyRegisterChecklist::create([
                    'source_sheet'   => $item[0],
                    'sub_area_code'  => $item[1],
                    'sub_area_name'  => $item[2],
                    'item_no'        => $item[3],
                    'item_checklist' => $item[4],
                    'sort_order'     => $sortOrder++,
                ]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
