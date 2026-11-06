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
            // TAB 5: KKA Kredit (134 Item — BPP & SK Direksi No. 78/SK-DIR/BPD-ST/2026)
            // ==========================================
            ['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 1, 'KTP pemohon (suami/istri)', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 2, 'Kartu Keluarga (KK)', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 3, 'Surat Nikah / Akta Cerai', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 4, 'NPWP', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 5, 'Slip gaji 3 bulan terakhir', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 6, 'SK PNS / SK pengangkatan terakhir', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 7, 'SK kenaikan pangkat terakhir', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 8, 'Surat keterangan masih aktif bekerja', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 9, 'Surat rekomendasi/persetujuan atasan/bendahara', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 10, 'Surat kuasa potong gaji', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 11, 'Rekening koran/tabungan 3 bulan terakhir', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 12, 'Pas foto pemohon', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 13, 'Formulir aplikasi kredit (lengkap, ditandatangani)', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 14, 'Hasil BI-Checking / SLIK OJK', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 15, 'Memorandum analisis kredit (MAK)', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 16, 'Nota persetujuan kredit (pejabat berwenang)', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 17, 'Perjanjian kredit (PK) ditandatangani kedua pihak', 'BPP'],
['KKA_Kredit', 'C1', 'Berkas Debitur Baru — Kredit Konsumtif PNS', 18, 'Jadwal angsuran', 'BPP'],
['KKA_Kredit', 'C2', 'Berkas Debitur Baru — Kredit Konsumtif Umum/Swasta', 19, 'KTP pemohon (suami/istri)', 'BPP'],
['KKA_Kredit', 'C2', 'Berkas Debitur Baru — Kredit Konsumtif Umum/Swasta', 20, 'Kartu Keluarga (KK)', 'BPP'],
['KKA_Kredit', 'C2', 'Berkas Debitur Baru — Kredit Konsumtif Umum/Swasta', 21, 'Surat Nikah / Akta Cerai', 'BPP'],
['KKA_Kredit', 'C2', 'Berkas Debitur Baru — Kredit Konsumtif Umum/Swasta', 22, 'NPWP', 'BPP'],
['KKA_Kredit', 'C2', 'Berkas Debitur Baru — Kredit Konsumtif Umum/Swasta', 23, 'Slip gaji 3 bulan terakhir', 'BPP'],
['KKA_Kredit', 'C2', 'Berkas Debitur Baru — Kredit Konsumtif Umum/Swasta', 24, 'Surat keterangan kerja dari perusahaan', 'BPP'],
['KKA_Kredit', 'C2', 'Berkas Debitur Baru — Kredit Konsumtif Umum/Swasta', 25, 'Rekening koran/tabungan 3-6 bulan terakhir', 'BPP'],
['KKA_Kredit', 'C2', 'Berkas Debitur Baru — Kredit Konsumtif Umum/Swasta', 26, 'Formulir aplikasi kredit', 'BPP'],
['KKA_Kredit', 'C2', 'Berkas Debitur Baru — Kredit Konsumtif Umum/Swasta', 27, 'Hasil SLIK OJK', 'BPP'],
['KKA_Kredit', 'C2', 'Berkas Debitur Baru — Kredit Konsumtif Umum/Swasta', 28, 'MAK (Memorandum Analisis Kredit)', 'BPP'],
['KKA_Kredit', 'C2', 'Berkas Debitur Baru — Kredit Konsumtif Umum/Swasta', 29, 'Nota persetujuan kredit', 'BPP'],
['KKA_Kredit', 'C2', 'Berkas Debitur Baru — Kredit Konsumtif Umum/Swasta', 30, 'Perjanjian kredit (PK)', 'BPP'],
['KKA_Kredit', 'C2', 'Berkas Debitur Baru — Kredit Konsumtif Umum/Swasta', 31, 'Dokumen agunan (lihat C7)', 'BPP'],
['KKA_Kredit', 'C2', 'Berkas Debitur Baru — Kredit Konsumtif Umum/Swasta', 32, 'Pengikatan agunan (SKMHT/APHT, Fidusia)', 'BPP'],
['KKA_Kredit', 'C2', 'Berkas Debitur Baru — Kredit Konsumtif Umum/Swasta', 33, 'Asuransi jiwa debitur', 'BPP'],
['KKA_Kredit', 'C2', 'Berkas Debitur Baru — Kredit Konsumtif Umum/Swasta', 34, 'Asuransi kebakaran (jika agunan bangunan)', 'BPP'],
['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 35, 'KTP pengurus / pemilik perusahaan', 'BPP'],
['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 36, 'NPWP perusahaan dan pribadi', 'BPP'],
['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 37, 'Akta pendirian perusahaan + perubahan terakhir', 'BPP'],
['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 38, 'SIUP / NIB (Nomor Induk Berusaha)', 'BPP'],
['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 39, 'TDP / SKU (Surat Keterangan Usaha)', 'BPP'],
['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 40, 'Laporan keuangan 2 tahun terakhir (audited jika ada)', 'BPP'],
['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 41, 'Rekening koran 6 bulan terakhir', 'BPP'],
['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 42, 'Rencana usaha / business plan', 'BPP'],
['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 43, 'Data agunan (SHM/SHGB, BPKB, deposito)', 'BPP'],
['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 44, 'Hasil SLIK OJK', 'BPP'],
['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 45, 'MAK / proposal kredit', 'BPP'],
['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 46, 'Nota persetujuan kredit', 'BPP'],
['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 47, 'Perjanjian kredit (PK)', 'BPP'],
['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 48, 'Pengikatan agunan', 'BPP'],
['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 49, 'Asuransi agunan', 'BPP'],
['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 50, 'Foto lokasi usaha dan agunan', 'BPP'],
['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 51, 'KTP pemohon (suami/istri)', 'BPP'],
['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 52, 'Kartu Keluarga (KK)', 'BPP'],
['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 53, 'Surat Nikah / Akta Cerai', 'BPP'],
['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 54, 'Surat Keterangan Usaha (SKU) dari kelurahan/desa', 'BPP'],
['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 55, 'NPWP (jika plafon > ketentuan)', 'BPP'],
['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 56, 'Formulir aplikasi KUR', 'BPP'],
['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 57, 'Hasil SLIK OJK', 'BPP'],
['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 58, 'Foto lokasi usaha', 'BPP'],
['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 59, 'MAK / catatan analisis', 'BPP'],
['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 60, 'Nota persetujuan kredit', 'BPP'],
['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 61, 'Perjanjian kredit (PK)', 'BPP'],
['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 62, 'Sertifikat penjaminan KUR (Jamkrindo/Askrindo)', 'BPP'],
['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 63, 'Jadwal angsuran', 'BPP'],
['KKA_Kredit', 'C5', 'Pencairan Kredit', 64, 'Nota pencairan / instruksi pencairan', 'BPP'],
['KKA_Kredit', 'C5', 'Pencairan Kredit', 65, 'Otorisasi pencairan (tanda tangan pejabat berwenang)', 'BPP'],
['KKA_Kredit', 'C5', 'Pencairan Kredit', 66, 'Kesesuaian nominal pencairan dengan PK', 'BPP'],
['KKA_Kredit', 'C5', 'Pencairan Kredit', 67, 'Biaya-biaya yang dibebankan (provisi, admin, notaris, asuransi)', 'BPP'],
['KKA_Kredit', 'C5', 'Pencairan Kredit', 68, 'Bukti transfer pencairan ke rekening debitur', 'BPP'],
['KKA_Kredit', 'C5', 'Pencairan Kredit', 69, 'Kesesuaian tanggal pencairan dengan jadwal', 'BPP'],
['KKA_Kredit', 'C5', 'Pencairan Kredit', 70, 'Checklist kelengkapan berkas sebelum pencairan (compliance)', 'BPP'],
['KKA_Kredit', 'C6', 'Administrasi Kredit & Penutupan', 71, 'Buku register kredit (pembukaan s.d. pelunasan)', 'BPP'],
['KKA_Kredit', 'C6', 'Administrasi Kredit & Penutupan', 72, 'Kartu angsuran / history pembayaran', 'BPP'],
['KKA_Kredit', 'C6', 'Administrasi Kredit & Penutupan', 73, 'Surat peringatan (SP1, SP2, SP3) jika NPL', 'BPP'],
['KKA_Kredit', 'C6', 'Administrasi Kredit & Penutupan', 74, 'Surat pelunasan kredit', 'BPP'],
['KKA_Kredit', 'C6', 'Administrasi Kredit & Penutupan', 75, 'Berita acara pengembalian agunan', 'BPP'],
['KKA_Kredit', 'C6', 'Administrasi Kredit & Penutupan', 76, 'Bukti penutupan rekening kredit di sistem', 'BPP'],
['KKA_Kredit', 'C6', 'Administrasi Kredit & Penutupan', 77, 'Surat kuasa jual agunan (jika ada)', 'BPP'],
['KKA_Kredit', 'C7', 'Dokumen Agunan', 78, 'SHM / SHGB / SHMRS (asli, cek keaslian)', 'BPP'],
['KKA_Kredit', 'C7', 'Dokumen Agunan', 79, 'IMB (Izin Mendirikan Bangunan)', 'BPP'],
['KKA_Kredit', 'C7', 'Dokumen Agunan', 80, 'PBB tahun terakhir (bukti bayar)', 'BPP'],
['KKA_Kredit', 'C7', 'Dokumen Agunan', 81, 'BPKB kendaraan (asli) + STNK', 'BPP'],
['KKA_Kredit', 'C7', 'Dokumen Agunan', 82, 'Faktur kendaraan', 'BPP'],
['KKA_Kredit', 'C7', 'Dokumen Agunan', 83, 'Bilyet deposito (asli, jika agunan deposito)', 'BPP'],
['KKA_Kredit', 'C7', 'Dokumen Agunan', 84, 'SKMHT / APHT (Surat Kuasa / Akta Pemberian Hak Tanggungan)', 'BPP'],
['KKA_Kredit', 'C7', 'Dokumen Agunan', 85, 'Sertifikat Hak Tanggungan (SHT) dari BPN', 'BPP'],
['KKA_Kredit', 'C7', 'Dokumen Agunan', 86, 'Akta Fidusia + Sertifikat Fidusia', 'BPP'],
['KKA_Kredit', 'C7', 'Dokumen Agunan', 87, 'Laporan penilaian agunan / taksasi', 'BPP'],
['KKA_Kredit', 'C7', 'Dokumen Agunan', 88, 'Foto agunan (eksterior dan interior)', 'BPP'],
['KKA_Kredit', 'C7', 'Dokumen Agunan', 89, 'Kesesuaian identitas di agunan dengan identitas debitur', 'BPP'],
['KKA_Kredit', 'C7', 'Dokumen Agunan', 90, 'Peta lokasi agunan', 'BPP'],
['KKA_Kredit', 'C7', 'Dokumen Agunan', 91, 'Register penyimpanan dokumen agunan', 'BPP'],
['KKA_Kredit', 'C7', 'Dokumen Agunan', 92, 'Berita acara serah terima dokumen agunan', 'BPP'],
['KKA_Kredit', 'C8', 'Klaim Asuransi Kredit', 93, 'Polis asuransi jiwa debitur', 'BPP'],
['KKA_Kredit', 'C8', 'Klaim Asuransi Kredit', 94, 'Polis asuransi kebakaran/kerugian', 'BPP'],
['KKA_Kredit', 'C8', 'Klaim Asuransi Kredit', 95, 'Surat klaim ke perusahaan asuransi', 'BPP'],
['KKA_Kredit', 'C8', 'Klaim Asuransi Kredit', 96, 'Bukti pengajuan klaim (tanggal, nomor surat)', 'BPP'],
['KKA_Kredit', 'C8', 'Klaim Asuransi Kredit', 97, 'Surat persetujuan klaim dari asuransi', 'BPP'],
['KKA_Kredit', 'C8', 'Klaim Asuransi Kredit', 98, 'Bukti pencairan klaim', 'BPP'],
['KKA_Kredit', 'C8', 'Klaim Asuransi Kredit', 99, 'Register klaim asuransi (tanggal ajuan, status, jumlah)', 'BPP'],
['KKA_Kredit', 'C9', 'FLAGGING KREDIT (Early Warning System) — Pelaksanaan Penandaan', 100, 'Ada ketentuan/kriteria flagging kredit yang berlaku di Bank (SK/SE/pedoman) dan dipahami unit', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C9', 'FLAGGING KREDIT (Early Warning System) — Pelaksanaan Penandaan', 101, 'Register/daftar kredit ter-flag tersedia dan diperbarui', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C9', 'FLAGGING KREDIT (Early Warning System) — Pelaksanaan Penandaan', 102, 'Flag di sistem/CBS sesuai dengan register kredit ter-flag', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C9', 'FLAGGING KREDIT (Early Warning System) — Pelaksanaan Penandaan', 103, 'Setiap flag mencatat alasan/kriteria pemicu', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C9', 'FLAGGING KREDIT (Early Warning System) — Pelaksanaan Penandaan', 104, 'Tanggal flag diberikan dan pejabat pemberi/otorisator tercatat', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C9', 'FLAGGING KREDIT (Early Warning System) — Pelaksanaan Penandaan', 105, 'Flag diberikan tepat waktu sejak kondisi pemicu terjadi', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C10', 'FLAGGING KREDIT — Uji Kelengkapan (kredit yang seharusnya ter-flag)', 106, 'Kredit dengan tunggakan angsuran / lewat jatuh tempo sudah ter-flag', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C10', 'FLAGGING KREDIT — Uji Kelengkapan (kredit yang seharusnya ter-flag)', 107, 'Kredit dengan kolektibilitas menurun sudah ter-flag', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C10', 'FLAGGING KREDIT — Uji Kelengkapan (kredit yang seharusnya ter-flag)', 108, 'Kredit restrukturisasi sudah ter-flag', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C10', 'FLAGGING KREDIT — Uji Kelengkapan (kredit yang seharusnya ter-flag)', 109, 'Kredit dengan dokumen/pencairan belum lengkap (pending dokumen) sudah ter-flag', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C10', 'FLAGGING KREDIT — Uji Kelengkapan (kredit yang seharusnya ter-flag)', 110, 'Kredit dengan asuransi, pengikatan agunan, atau taksasi kedaluwarsa sudah ter-flag', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C10', 'FLAGGING KREDIT — Uji Kelengkapan (kredit yang seharusnya ter-flag)', 111, 'Debitur meninggal, PHK/pensiun, atau usaha tutup: kreditnya sudah ter-flag', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C10', 'FLAGGING KREDIT — Uji Kelengkapan (kredit yang seharusnya ter-flag)', 112, 'Debitur dengan SLIK memburuk atau memiliki pengaduan nasabah sudah ter-flag', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C10', 'FLAGGING KREDIT — Uji Kelengkapan (kredit yang seharusnya ter-flag)', 113, 'Kredit mendekati jatuh tempo, plafon besar, atau debitur terkait (insider) sudah ter-flag', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C11', 'FLAGGING KREDIT — Tindak Lanjut dan Pencabutan Flag', 114, 'Tindak lanjut atas flag (kunjungan debitur, surat peringatan, penagihan) terdokumentasi', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C11', 'FLAGGING KREDIT — Tindak Lanjut dan Pencabutan Flag', 115, 'Pemantauan berkala atas kredit ter-flag dilakukan sesuai jadwal', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C11', 'FLAGGING KREDIT — Tindak Lanjut dan Pencabutan Flag', 116, 'Pencabutan flag disertai alasan, bukti penyelesaian, dan otorisasi pejabat berwenang', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C11', 'FLAGGING KREDIT — Tindak Lanjut dan Pencabutan Flag', 117, 'Tidak ada flag dicabut atau diubah tanpa otorisasi (cek audit trail sistem)', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C11', 'FLAGGING KREDIT — Tindak Lanjut dan Pencabutan Flag', 118, 'Flag yang lama tidak ditindaklanjuti dicatat sebagai exception dan dieskalasi', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C11A', 'FLAGGING KREDIT — Uji Ketepatan (kredit ter-flag yang seharusnya tidak ter-flag)', 119, 'Ambil sampel dari register kredit ter-flag, lalu cocokkan tiap flag dengan kriteria pemicunya', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C11A', 'FLAGGING KREDIT — Uji Ketepatan (kredit ter-flag yang seharusnya tidak ter-flag)', 120, 'Kondisi pemicu benar-benar ada pada kredit tersebut (cek history angsuran, kolektibilitas, dokumen)', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C11A', 'FLAGGING KREDIT — Uji Ketepatan (kredit ter-flag yang seharusnya tidak ter-flag)', 121, 'Debitur lancar tanpa tunggakan, kolektibilitas lancar, dan dokumen lengkap tetapi ter-flag: catat sebagai flag keliru', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C11A', 'FLAGGING KREDIT — Uji Ketepatan (kredit ter-flag yang seharusnya tidak ter-flag)', 122, 'Kondisi pemicu sudah selesai (tunggakan lunas, dokumen dilengkapi, asuransi diperpanjang) tetapi flag belum dicabut', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C11A', 'FLAGGING KREDIT — Uji Ketepatan (kredit ter-flag yang seharusnya tidak ter-flag)', 123, 'Flag terpasang pada rekening/debitur yang salah (nomor rekening atau CIF tertukar)', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C11A', 'FLAGGING KREDIT — Uji Ketepatan (kredit ter-flag yang seharusnya tidak ter-flag)', 124, 'Flag ganda atau tumpang tindih untuk kondisi yang sama pada satu fasilitas', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C11A', 'FLAGGING KREDIT — Uji Ketepatan (kredit ter-flag yang seharusnya tidak ter-flag)', 125, 'Pola flag tidak wajar: banyak flag pada satu pejabat/periode/segmen tanpa dasar yang jelas', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C11A', 'FLAGGING KREDIT — Uji Ketepatan (kredit ter-flag yang seharusnya tidak ter-flag)', 126, 'Dampak flag keliru dicek (debitur terhalang pencairan lanjutan, tambahan biaya, atau perlakuan kolektibilitas)', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C11A', 'FLAGGING KREDIT — Uji Ketepatan (kredit ter-flag yang seharusnya tidak ter-flag)', 127, 'Flag keliru dicatat di Register Exception, dikonfirmasi ke unit, dan dikoreksi dengan otorisasi', 'Saran RA (konfirmasi Bank)'],
['KKA_Kredit', 'C12', 'Trigger Insidentil Kredit (SOP-RA-10, kategori Kredit/DPK/Layanan)', 128, 'Berkas kritikal hilang', 'BPP'],
['KKA_Kredit', 'C12', 'Trigger Insidentil Kredit (SOP-RA-10, kategori Kredit/DPK/Layanan)', 129, 'Dokumen tidak sah (indikasi pemalsuan)', 'BPP'],
['KKA_Kredit', 'C12', 'Trigger Insidentil Kredit (SOP-RA-10, kategori Kredit/DPK/Layanan)', 130, 'Komplain nasabah signifikan terkait kredit', 'BPP'],
['KKA_Kredit', 'C12', 'Trigger Insidentil Kredit (SOP-RA-10, kategori Kredit/DPK/Layanan)', 131, 'CIF/rekening kredit bermasalah (CIF ganda, data tidak sinkron)', 'BPP'],
['KKA_Kredit', 'C12', 'Trigger Insidentil Kredit (SOP-RA-10, kategori Kredit/DPK/Layanan)', 132, 'Pencairan tidak sesuai kontrol (tanpa otorisasi/kelengkapan berkas)', 'BPP'],
['KKA_Kredit', 'C12', 'Trigger Insidentil Kredit (SOP-RA-10, kategori Kredit/DPK/Layanan)', 133, 'Bila berdampak material, nasabah, atau reputasi: dilaporkan ke Bagian RA', 'BPP'],
['KKA_Kredit', 'C12', 'Trigger Insidentil Kredit (SOP-RA-10, kategori Kredit/DPK/Layanan)', 134, 'Sampel diperluas sesuai level (L1: min 5 item/50%; L2: 2x sampel; L3: 100%)', 'BPP'],

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
                    'sumber'         => $item[5] ?? 'BPP',
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
