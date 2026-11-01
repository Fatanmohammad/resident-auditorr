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
            // TAB 1: KKA Teller & Kas (30 Item)
            // ==========================================
            // Sub-Area A1. Transaksi Teller (Offsite & Onsite) (No. 1–10)
            ['KKA_Teller_Kas', 'A1', 'Transaksi Teller (Offsite & Onsite)', 1, 'Voucher/slip setoran tunai'],
            ['KKA_Teller_Kas', 'A1', 'Transaksi Teller (Offsite & Onsite)', 2, 'Voucher/slip penarikan tunai'],
            ['KKA_Teller_Kas', 'A1', 'Transaksi Teller (Offsite & Onsite)', 3, 'Slip pemindahbukuan'],
            ['KKA_Teller_Kas', 'A1', 'Transaksi Teller (Offsite & Onsite)', 4, 'Nota debet/kredit'],
            ['KKA_Teller_Kas', 'A1', 'Transaksi Teller (Offsite & Onsite)', 5, 'Bukti otorisasi transaksi (tanda tangan pejabat berwenang)'],
            ['KKA_Teller_Kas', 'A1', 'Transaksi Teller (Offsite & Onsite)', 6, 'Kesesuaian nominal voucher dengan sistem/register'],
            ['KKA_Teller_Kas', 'A1', 'Transaksi Teller (Offsite & Onsite)', 7, 'Validasi mesin teller pada voucher'],
            ['KKA_Teller_Kas', 'A1', 'Transaksi Teller (Offsite & Onsite)', 8, 'Urutan nomor voucher (sequence control)'],
            ['KKA_Teller_Kas', 'A1', 'Transaksi Teller (Offsite & Onsite)', 9, 'Tanda tangan nasabah di voucher'],
            ['KKA_Teller_Kas', 'A1', 'Transaksi Teller (Offsite & Onsite)', 10, 'Fotokopi identitas nasabah (transaksi ≥ Rp100 juta)'],

            // Sub-Area A2. Kas Kecil (No. 11–15)
            ['KKA_Teller_Kas', 'A2', 'Kas Kecil', 11, 'Buku kas kecil / register kas kecil'],
            ['KKA_Teller_Kas', 'A2', 'Kas Kecil', 12, 'Bukti pengeluaran kas kecil (kuitansi, nota)'],
            ['KKA_Teller_Kas', 'A2', 'Kas Kecil', 13, 'Otorisasi pengeluaran kas kecil'],
            ['KKA_Teller_Kas', 'A2', 'Kas Kecil', 14, 'Berita acara opname kas kecil'],
            ['KKA_Teller_Kas', 'A2', 'Kas Kecil', 15, 'Kesesuaian saldo fisik dengan catatan'],

            // Sub-Area A3. Kas Opname Khasanah (Onsite & Surprise) (No. 16–25)
            ['KKA_Teller_Kas', 'A3', 'Kas Opname Khasanah (Onsite & Surprise)', 16, 'Fisik uang tunai dalam khasanah (hitung fisik)'],
            ['KKA_Teller_Kas', 'A3', 'Kas Opname Khasanah (Onsite & Surprise)', 17, 'Register kas / buku khasanah'],
            ['KKA_Teller_Kas', 'A3', 'Kas Opname Khasanah (Onsite & Surprise)', 18, 'Saldo sistem CBS vs saldo fisik'],
            ['KKA_Teller_Kas', 'A3', 'Kas Opname Khasanah (Onsite & Surprise)', 19, 'Berita acara opname khasanah (BA harian)'],
            ['KKA_Teller_Kas', 'A3', 'Kas Opname Khasanah (Onsite & Surprise)', 20, 'Akses kunci khasanah (siapa pemegang kunci)'],
            ['KKA_Teller_Kas', 'A3', 'Kas Opname Khasanah (Onsite & Surprise)', 21, 'Kombinasi brankas (dual control — 2 orang berbeda)'],
            ['KKA_Teller_Kas', 'A3', 'Kas Opname Khasanah (Onsite & Surprise)', 22, 'Batas limit kas teller sesuai ketentuan'],
            ['KKA_Teller_Kas', 'A3', 'Kas Opname Khasanah (Onsite & Surprise)', 23, 'Batas limit kas khasanah sesuai ketentuan'],
            ['KKA_Teller_Kas', 'A3', 'Kas Opname Khasanah (Onsite & Surprise)', 24, 'Kesesuaian pengamanan khasanah (CCTV, alarm, gembok)'],
            ['KKA_Teller_Kas', 'A3', 'Kas Opname Khasanah (Onsite & Surprise)', 25, 'Selisih kas (jika ada → catat nominal, penyebab, tindak lanjut)'],

            // Sub-Area A4. Observasi Khasanah (No. 26–30)
            ['KKA_Teller_Kas', 'A4', 'Observasi Khasanah', 26, 'Kondisi fisik ruang khasanah (pintu, dinding, ventilasi)'],
            ['KKA_Teller_Kas', 'A4', 'Observasi Khasanah', 27, 'CCTV berfungsi dan merekam'],
            ['KKA_Teller_Kas', 'A4', 'Observasi Khasanah', 28, 'Brankas terkunci saat tidak digunakan'],
            ['KKA_Teller_Kas', 'A4', 'Observasi Khasanah', 29, 'Log akses khasanah (buku tamu/catatan akses)'],
            ['KKA_Teller_Kas', 'A4', 'Observasi Khasanah', 30, 'Prosedur buka-tutup khasanah (jam buka, jam tutup, saksi)'],

            // ==========================================
            // TAB 2: KKA DPK & Layanan (29 Item)
            // ==========================================
            // Sub-Area D1. Pembukaan Rekening & CIF (Perorangan) (No. 1–10)
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 1, 'Formulir pembukaan rekening (ditandatangani nasabah)'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 2, 'KTP nasabah (fotokopi, verifikasi keaslian)'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 3, 'NPWP nasabah (jika ada)'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 4, 'Kartu contoh tanda tangan (specimen)'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 5, 'Setoran awal minimum sesuai ketentuan'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 6, 'CIF (Customer Information File) terisi lengkap di sistem'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 7, 'Tidak ada CIF ganda (1 nasabah = 1 CIF)'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 8, 'Kategori risiko nasabah (APU-PPT: low/medium/high)'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 9, 'Bukti verifikasi data nasabah'],
            ['KKA_DPK_Layanan', 'D1', 'Pembukaan Rekening & CIF (Perorangan)', 10, 'Surat pernyataan beneficial owner (jika bukan milik sendiri)'],

            // Sub-Area D2. Pembukaan Rekening Badan Usaha (No. 11–17)
            ['KKA_DPK_Layanan', 'D2', 'Pembukaan Rekening Badan Usaha', 11, 'Akta pendirian + perubahan terakhir'],
            ['KKA_DPK_Layanan', 'D2', 'Pembukaan Rekening Badan Usaha', 12, 'SIUP / NIB'],
            ['KKA_DPK_Layanan', 'D2', 'Pembukaan Rekening Badan Usaha', 13, 'NPWP badan usaha'],
            ['KKA_DPK_Layanan', 'D2', 'Pembukaan Rekening Badan Usaha', 14, 'KTP pengurus yang tercantum di akta'],
            ['KKA_DPK_Layanan', 'D2', 'Pembukaan Rekening Badan Usaha', 15, 'Surat kuasa (jika yang membuka bukan pengurus)'],
            ['KKA_DPK_Layanan', 'D2', 'Pembukaan Rekening Badan Usaha', 16, 'Formulir pembukaan rekening badan'],
            ['KKA_DPK_Layanan', 'D2', 'Pembukaan Rekening Badan Usaha', 17, 'Specimen tanda tangan pengurus'],

            // Sub-Area D3. Perubahan Data Nasabah (No. 18–22)
            ['KKA_DPK_Layanan', 'D3', 'Perubahan Data Nasabah', 18, 'Formulir perubahan data'],
            ['KKA_DPK_Layanan', 'D3', 'Perubahan Data Nasabah', 19, 'Bukti identitas baru (KTP baru jika ganti alamat/nama)'],
            ['KKA_DPK_Layanan', 'D3', 'Perubahan Data Nasabah', 20, 'Otorisasi perubahan oleh pejabat berwenang'],
            ['KKA_DPK_Layanan', 'D3', 'Perubahan Data Nasabah', 21, 'Log perubahan di sistem (audit trail)'],
            ['KKA_DPK_Layanan', 'D3', 'Perubahan Data Nasabah', 22, 'Alasan perubahan tercatat'],

            // Sub-Area D4. Penutupan Rekening (No. 23–29)
            ['KKA_DPK_Layanan', 'D4', 'Penutupan Rekening', 23, 'Formulir penutupan rekening (tanda tangan nasabah)'],
            ['KKA_DPK_Layanan', 'D4', 'Penutupan Rekening', 24, 'KTP nasabah'],
            ['KKA_DPK_Layanan', 'D4', 'Penutupan Rekening', 25, 'Buku tabungan dikembalikan'],
            ['KKA_DPK_Layanan', 'D4', 'Penutupan Rekening', 26, 'Kartu ATM dikembalikan dan dipotong/dimusnahkan'],
            ['KKA_DPK_Layanan', 'D4', 'Penutupan Rekening', 27, 'Saldo terakhir ditarik/ditransfer'],
            ['KKA_DPK_Layanan', 'D4', 'Penutupan Rekening', 28, 'Biaya penutupan dipotong sesuai ketentuan'],
            ['KKA_DPK_Layanan', 'D4', 'Penutupan Rekening', 29, "Status rekening berubah 'Closed' di sistem"],

            // ==========================================
            // TAB 3: KKA Kredit (99 Item)
            // ==========================================
            // Sub-Area C1. Berkas Debitur — Kredit Konsumtif PNS (No. 1–18)
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 1, 'KTP pemohon (suami/istri)'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 2, 'Kartu Keluarga (KK)'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 3, 'Surat Nikah / Akta Cerai'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 4, 'NPWP'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 5, 'Slip gaji 3 bulan terakhir'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 6, 'SK PNS / SK pengangkatan terakhir'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 7, 'SK kenaikan pangkat terakhir'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 8, 'Surat keterangan masih aktif bekerja'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 9, 'Surat rekomendasi/persetujuan atasan/bendahara'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 10, 'Surat kuasa potong gaji'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 11, 'Rekening koran/tabungan 3 bulan terakhir'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 12, 'Pas foto pemohon'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 13, 'Formulir aplikasi kredit (lengkap, ditandatangani)'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 14, 'Hasil BI-Checking / SLIK OJK'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 15, 'Memorandum analisis kredit (MAK)'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 16, 'Nota persetujuan kredit (pejabat berwenang)'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 17, 'Perjanjian kredit (PK) ditandatangani kedua pihak'],
            ['KKA_Kredit', 'C1', 'Berkas Debitur — Kredit Konsumtif PNS', 18, 'Jadwal angsuran'],

            // Sub-Area C2. Berkas Debitur — Kredit Konsumtif Umum/Swasta (No. 19–34)
            ['KKA_Kredit', 'C2', 'Berkas Debitur — Kredit Konsumtif Umum/Swasta', 19, 'KTP pemohon (suami/istri)'],
            ['KKA_Kredit', 'C2', 'Berkas Debitur — Kredit Konsumtif Umum/Swasta', 20, 'Kartu Keluarga (KK)'],
            ['KKA_Kredit', 'C2', 'Berkas Debitur — Kredit Konsumtif Umum/Swasta', 21, 'Surat Nikah / Akta Cerai'],
            ['KKA_Kredit', 'C2', 'Berkas Debitur — Kredit Konsumtif Umum/Swasta', 22, 'NPWP'],
            ['KKA_Kredit', 'C2', 'Berkas Debitur — Kredit Konsumtif Umum/Swasta', 23, 'Slip gaji 3 bulan terakhir'],
            ['KKA_Kredit', 'C2', 'Berkas Debitur — Kredit Konsumtif Umum/Swasta', 24, 'Surat keterangan kerja dari perusahaan'],
            ['KKA_Kredit', 'C2', 'Berkas Debitur — Kredit Konsumtif Umum/Swasta', 25, 'Rekening koran/tabungan 3-6 bulan terakhir'],
            ['KKA_Kredit', 'C2', 'Berkas Debitur — Kredit Konsumtif Umum/Swasta', 26, 'Formulir aplikasi kredit'],
            ['KKA_Kredit', 'C2', 'Berkas Debitur — Kredit Konsumtif Umum/Swasta', 27, 'Hasil SLIK OJK'],
            ['KKA_Kredit', 'C2', 'Berkas Debitur — Kredit Konsumtif Umum/Swasta', 28, 'MAK (Memorandum Analisis Kredit)'],
            ['KKA_Kredit', 'C2', 'Berkas Debitur — Kredit Konsumtif Umum/Swasta', 29, 'Nota persetujuan kredit'],
            ['KKA_Kredit', 'C2', 'Berkas Debitur — Kredit Konsumtif Umum/Swasta', 30, 'Perjanjian kredit (PK)'],
            ['KKA_Kredit', 'C2', 'Berkas Debitur — Kredit Konsumtif Umum/Swasta', 31, 'Dokumen agunan (lihat sub-area C7)'],
            ['KKA_Kredit', 'C2', 'Berkas Debitur — Kredit Konsumtif Umum/Swasta', 32, 'Pengikatan agunan (SKMHT/APHT, Fidusia)'],
            ['KKA_Kredit', 'C2', 'Berkas Debitur — Kredit Konsumtif Umum/Swasta', 33, 'Asuransi jiwa debitur'],
            ['KKA_Kredit', 'C2', 'Berkas Debitur — Kredit Konsumtif Umum/Swasta', 34, 'Asuransi kebakaran (jika agunan bangunan)'],

            // Sub-Area C3. Berkas Debitur — Kredit Modal Kerja (KMK) (No. 35–50)
            ['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 35, 'KTP pengurus / pemilik perusahaan'],
            ['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 36, 'NPWP perusahaan dan pribadi'],
            ['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 37, 'Akta pendirian perusahaan + perubahan terakhir'],
            ['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 38, 'SIUP / NIB (Nomor Induk Berusaha)'],
            ['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 39, 'TDP / SKU (Surat Keterangan Usaha)'],
            ['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 40, 'Laporan keuangan 2 tahun terakhir (audited jika ada)'],
            ['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 41, 'Rekening koran 6 bulan terakhir'],
            ['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 42, 'Rencana usaha / business plan'],
            ['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 43, 'Data agunan (SHM/SHGB, BPKB, deposito)'],
            ['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 44, 'Hasil SLIK OJK'],
            ['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 45, 'MAK / proposal kredit'],
            ['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 46, 'Nota persetujuan kredit'],
            ['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 47, 'Perjanjian kredit (PK)'],
            ['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 48, 'Pengikatan agunan'],
            ['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 49, 'Asuransi agunan'],
            ['KKA_Kredit', 'C3', 'Berkas Debitur — Kredit Modal Kerja (KMK)', 50, 'Foto lokasi usaha dan agunan'],

            // Sub-Area C4. Berkas Debitur — KUR (Kredit Usaha Rakyat) (No. 51–63)
            ['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 51, 'KTP pemohon (suami/istri)'],
            ['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 52, 'Kartu Keluarga (KK)'],
            ['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 53, 'Surat Nikah / Akta Cerai'],
            ['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 54, 'Surat Keterangan Usaha (SKU) dari kelurahan/desa'],
            ['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 55, 'NPWP (jika plafon > ketentuan)'],
            ['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 56, 'Formulir aplikasi KUR'],
            ['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 57, 'Hasil SLIK OJK'],
            ['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 58, 'Foto lokasi usaha'],
            ['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 59, 'MAK / catatan analisis'],
            ['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 60, 'Nota persetujuan kredit'],
            ['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 61, 'Perjanjian kredit (PK)'],
            ['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 62, 'Sertifikat penjaminan KUR (Jamkrindo/Askrindo)'],
            ['KKA_Kredit', 'C4', 'Berkas Debitur — KUR (Kredit Usaha Rakyat)', 63, 'Jadwal angsuran'],

            // Sub-Area C5. Pencairan Kredit (No. 64–70)
            ['KKA_Kredit', 'C5', 'Pencairan Kredit', 64, 'Nota pencairan / instruksi pencairan'],
            ['KKA_Kredit', 'C5', 'Pencairan Kredit', 65, 'Otorisasi pencairan (tanda tangan pejabat berwenang)'],
            ['KKA_Kredit', 'C5', 'Pencairan Kredit', 66, 'Kesesuaian nominal pencairan dengan PK'],
            ['KKA_Kredit', 'C5', 'Pencairan Kredit', 67, 'Biaya-biaya yang dibebankan (provisi, admin, notaris, asuransi)'],
            ['KKA_Kredit', 'C5', 'Pencairan Kredit', 68, 'Bukti transfer pencairan ke rekening debitur'],
            ['KKA_Kredit', 'C5', 'Pencairan Kredit', 69, 'Kesesuaian tanggal pencairan dengan jadwal'],
            ['KKA_Kredit', 'C5', 'Pencairan Kredit', 70, 'Checklist kelengkapan berkas sebelum pencairan (compliance)'],

            // Sub-Area C6. Administrasi Kredit & Penutupan (No. 71–77)
            ['KKA_Kredit', 'C6', 'Administrasi Kredit & Penutupan', 71, 'Buku register kredit (pembukaan s.d. pelunasan)'],
            ['KKA_Kredit', 'C6', 'Administrasi Kredit & Penutupan', 72, 'Kartu angsuran / history pembayaran'],
            ['KKA_Kredit', 'C6', 'Administrasi Kredit & Penutupan', 73, 'Surat peringatan (SP1, SP2, SP3) jika NPL'],
            ['KKA_Kredit', 'C6', 'Administrasi Kredit & Penutupan', 74, 'Surat pelunasan kredit'],
            ['KKA_Kredit', 'C6', 'Administrasi Kredit & Penutupan', 75, 'Berita acara pengembalian agunan'],
            ['KKA_Kredit', 'C6', 'Administrasi Kredit & Penutupan', 76, 'Bukti penutupan rekening kredit di sistem'],
            ['KKA_Kredit', 'C6', 'Administrasi Kredit & Penutupan', 77, 'Surat kuasa jual agunan (jika ada)'],

            // Sub-Area C7. Dokumen Agunan (No. 78–92)
            ['KKA_Kredit', 'C7', 'Dokumen Agunan', 78, 'SHM / SHGB / SHMRS (asli, cek keaslian)'],
            ['KKA_Kredit', 'C7', 'Dokumen Agunan', 79, 'IMB (Izin Mendirikan Bangunan)'],
            ['KKA_Kredit', 'C7', 'Dokumen Agunan', 80, 'PBB tahun terakhir (bukti bayar)'],
            ['KKA_Kredit', 'C7', 'Dokumen Agunan', 81, 'BPKB kendaraan (asli) + STNK'],
            ['KKA_Kredit', 'C7', 'Dokumen Agunan', 82, 'Faktur kendaraan'],
            ['KKA_Kredit', 'C7', 'Dokumen Agunan', 83, 'Bilyet deposito (asli, jika agunan deposito)'],
            ['KKA_Kredit', 'C7', 'Dokumen Agunan', 84, 'SKMHT / APHT (Surat Kuasa / Akta Pemberian Hak Tanggungan)'],
            ['KKA_Kredit', 'C7', 'Dokumen Agunan', 85, 'Sertifikat Hak Tanggungan (SHT) dari BPN'],
            ['KKA_Kredit', 'C7', 'Dokumen Agunan', 86, 'Akta Fidusia + Sertifikat Fidusia'],
            ['KKA_Kredit', 'C7', 'Dokumen Agunan', 87, 'Laporan penilaian agunan / taksasi'],
            ['KKA_Kredit', 'C7', 'Dokumen Agunan', 88, 'Foto agunan (eksterior dan interior)'],
            ['KKA_Kredit', 'C7', 'Dokumen Agunan', 89, 'Kesesuaian identitas di agunan dengan identitas debitur'],
            ['KKA_Kredit', 'C7', 'Dokumen Agunan', 90, 'Peta lokasi agunan'],
            ['KKA_Kredit', 'C7', 'Dokumen Agunan', 91, 'Register penyimpanan dokumen agunan'],
            ['KKA_Kredit', 'C7', 'Dokumen Agunan', 92, 'Berita acara serah terima dokumen agunan'],

            // Sub-Area C8. Klaim Asuransi Kredit (No. 93–99)
            ['KKA_Kredit', 'C8', 'Klaim Asuransi Kredit', 93, 'Polis asuransi jiwa debitur'],
            ['KKA_Kredit', 'C8', 'Klaim Asuransi Kredit', 94, 'Polis asuransi kebakaran/kerugian'],
            ['KKA_Kredit', 'C8', 'Klaim Asuransi Kredit', 95, 'Surat klaim ke perusahaan asuransi'],
            ['KKA_Kredit', 'C8', 'Klaim Asuransi Kredit', 96, 'Bukti pengajuan klaim (tanggal, nomor surat)'],
            ['KKA_Kredit', 'C8', 'Klaim Asuransi Kredit', 97, 'Surat persetujuan klaim dari asuransi'],
            ['KKA_Kredit', 'C8', 'Klaim Asuransi Kredit', 98, 'Bukti pencairan klaim'],
            ['KKA_Kredit', 'C8', 'Klaim Asuransi Kredit', 99, 'Register klaim asuransi (tanggal ajuan, status, jumlah)'],

            // ==========================================
            // TAB 4: KKA Biaya & Beban (22 Item)
            // ==========================================
            // Sub-Area E1. Biaya Operasional (No. 1–7)
            ['KKA_Biaya_Beban', 'E1', 'Biaya Operasional', 1, 'Kuitansi/nota/faktur asli'],
            ['KKA_Biaya_Beban', 'E1', 'Biaya Operasional', 2, 'Otorisasi pengeluaran (tanda tangan pejabat berwenang)'],
            ['KKA_Biaya_Beban', 'E1', 'Biaya Operasional', 3, 'Kesesuaian pos pembukuan (kode akun GL)'],
            ['KKA_Biaya_Beban', 'E1', 'Biaya Operasional', 4, 'Kewajaran nominal biaya'],
            ['KKA_Biaya_Beban', 'E1', 'Biaya Operasional', 5, 'Bukti pendukung tambahan (SPK, kontrak, BA serah terima)'],
            ['KKA_Biaya_Beban', 'E1', 'Biaya Operasional', 6, 'Pajak yang dipungut/dipotong (PPh, PPN — bukti potong)'],
            ['KKA_Biaya_Beban', 'E1', 'Biaya Operasional', 7, 'Kesesuaian dengan anggaran/budget'],

            // Sub-Area E2. Biaya Pengadaan Kecil (No. 8–12)
            ['KKA_Biaya_Beban', 'E2', 'Biaya Pengadaan Kecil', 8, 'Nota pembelian / faktur'],
            ['KKA_Biaya_Beban', 'E2', 'Biaya Pengadaan Kecil', 9, 'Surat permintaan pembelian'],
            ['KKA_Biaya_Beban', 'E2', 'Biaya Pengadaan Kecil', 10, 'Perbandingan harga (minimal 2-3 penyedia untuk jumlah tertentu)'],
            ['KKA_Biaya_Beban', 'E2', 'Biaya Pengadaan Kecil', 11, 'Berita acara serah terima barang'],
            ['KKA_Biaya_Beban', 'E2', 'Biaya Pengadaan Kecil', 12, 'Otorisasi pembelian'],

            // Sub-Area E3. Biaya Perjalanan Dinas (No. 13–18)
            ['KKA_Biaya_Beban', 'E3', 'Biaya Perjalanan Dinas', 13, 'Surat tugas / surat perintah perjalanan dinas (SPPD)'],
            ['KKA_Biaya_Beban', 'E3', 'Biaya Perjalanan Dinas', 14, 'Tiket transportasi (pesawat, kereta, dll)'],
            ['KKA_Biaya_Beban', 'E3', 'Biaya Perjalanan Dinas', 15, 'Boarding pass'],
            ['KKA_Biaya_Beban', 'E3', 'Biaya Perjalanan Dinas', 16, 'Bukti penginapan (kuitansi hotel)'],
            ['KKA_Biaya_Beban', 'E3', 'Biaya Perjalanan Dinas', 17, 'Laporan hasil perjalanan dinas'],
            ['KKA_Biaya_Beban', 'E3', 'Biaya Perjalanan Dinas', 18, 'Kesesuaian biaya dengan ketentuan uang harian'],

            // Sub-Area E4. Pos Pembukuan & Transaksi Tidak Biasa (No. 19–22)
            ['KKA_Biaya_Beban', 'E4', 'Pos Pembukuan & Transaksi Tidak Biasa', 19, 'Jurnal koreksi / reversal — alasan dan otorisasi'],
            ['KKA_Biaya_Beban', 'E4', 'Pos Pembukuan & Transaksi Tidak Biasa', 20, 'Transaksi antar kantor (RAK) — rekonsiliasi'],
            ['KKA_Biaya_Beban', 'E4', 'Pos Pembukuan & Transaksi Tidak Biasa', 21, 'Biaya yang tidak lazim / nominal besar tanpa kontrak'],
            ['KKA_Biaya_Beban', 'E4', 'Pos Pembukuan & Transaksi Tidak Biasa', 22, 'Pola transaksi biaya yang berulang ke pihak yang sama'],

            // ==========================================
            // TAB 5: KKA Kartu ATM (30 Item)
            // ==========================================
            // Sub-Area B1. Kondisi Gardu ATM (No. 1–7)
            ['KKA_Kartu_ATM', 'B1', 'Kondisi Gardu ATM', 1, 'Foto kondisi fisik gardu ATM (eksterior dan interior)'],
            ['KKA_Kartu_ATM', 'B1', 'Kondisi Gardu ATM', 2, 'Mesin ATM berfungsi normal (layar, keypad, printer)'],
            ['KKA_Kartu_ATM', 'B1', 'Kondisi Gardu ATM', 3, 'CCTV di gardu ATM berfungsi'],
            ['KKA_Kartu_ATM', 'B1', 'Kondisi Gardu ATM', 4, 'Penerangan gardu ATM memadai'],
            ['KKA_Kartu_ATM', 'B1', 'Kondisi Gardu ATM', 5, 'Kebersihan dan keamanan lingkungan gardu'],
            ['KKA_Kartu_ATM', 'B1', 'Kondisi Gardu ATM', 6, 'Stiker informasi call center / pengaduan terpasang'],
            ['KKA_Kartu_ATM', 'B1', 'Kondisi Gardu ATM', 7, 'Anti-skimming device terpasang (jika ada)'],

            // Sub-Area B2. Kas Opname ATM & Replenishment (No. 8–16)
            ['KKA_Kartu_ATM', 'B2', 'Kas Opname ATM & Replenishment', 8, 'Berita acara replenishment ATM'],
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
            // TAB 6: KKA Pengaduan (8 Item)
            // ==========================================
            // Sub-Area D5. Pengaduan/Keluhan Nasabah (No. 1–8 / Item 30–37 Excel)
            ['KKA_Pengaduan', 'D5', 'Pengaduan/Keluhan Nasabah', 1, 'Register pengaduan nasabah'],
            ['KKA_Pengaduan', 'D5', 'Pengaduan/Keluhan Nasabah', 2, 'Formulir pengaduan (tertulis)'],
            ['KKA_Pengaduan', 'D5', 'Pengaduan/Keluhan Nasabah', 3, 'Bukti identitas pengadu'],
            ['KKA_Pengaduan', 'D5', 'Pengaduan/Keluhan Nasabah', 4, 'Tanggal terima pengaduan'],
            ['KKA_Pengaduan', 'D5', 'Pengaduan/Keluhan Nasabah', 5, 'Tindak lanjut penyelesaian pengaduan'],
            ['KKA_Pengaduan', 'D5', 'Pengaduan/Keluhan Nasabah', 6, 'Surat jawaban/tanggapan ke nasabah'],
            ['KKA_Pengaduan', 'D5', 'Pengaduan/Keluhan Nasabah', 7, 'Jangka waktu penyelesaian (maks 20 hari kerja, perpanjangan maks 20 hari)'],
            ['KKA_Pengaduan', 'D5', 'Pengaduan/Keluhan Nasabah', 8, 'Bukti penyelesaian (pengembalian dana, koreksi, dll)'],
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
