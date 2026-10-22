<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiskTriggerMasterParameterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengacu penuh pada file Excel WP_SOP_06_RISK_TRIGGER_VISIT_v2.xlsx sheet Master_Parameter Sel E6:M18
     */
    public function run(): void
    {
        $parameters = [
            [
                'area' => 'Teller Kas',
                'kka_tujuan' => 'KKA_Teller_Kas',
                'tujuan_uji' => 'Membuktikan transaksi/selisih/reversal/otorisasi pemicu',
                'kriteria_utama' => 'BPP kas, limit, maker-checker, voucher dan bukti kas',
                'data_bukti_minimum' => 'Daftar transaksi pemicu, voucher, jurnal pasangan, log user, saldo kas',
                'metode_uji' => 'Tracing, vouching, rekonsiliasi dan klarifikasi',
                'metode_sampling' => 'Targeted + pembanding SOP 02',
                'perlu_fisik_default' => 'Ya',
                'keterangan_simpulan' => 'Status trigger, exception, sebab, dampak dan simpulan',
            ],
            [
                'area' => 'DPK Layanan',
                'kka_tujuan' => 'KKA_DPK_Layanan',
                'tujuan_uji' => 'Membuktikan kelengkapan KYC/CIF dan otorisasi layanan',
                'kriteria_utama' => 'Ketentuan pembukaan/perubahan rekening dan APU-PPT',
                'data_bukti_minimum' => 'CIF, formulir, identitas, specimen, otorisasi, profil risiko',
                'metode_uji' => 'Uji dokumen, rekonsiliasi sistem dan wawancara',
                'metode_sampling' => 'Targeted + pembanding SOP 02',
                'perlu_fisik_default' => 'Ya',
                'keterangan_simpulan' => 'Kelengkapan, exception dan simpulan layanan',
            ],
            [
                'area' => 'Kredit',
                'kka_tujuan' => 'KKA_Kredit',
                'tujuan_uji' => 'Membuktikan persetujuan, pencairan, agunan dan mitigasi kredit',
                'kriteria_utama' => 'Kebijakan kredit, limit, covenant, agunan dan asuransi',
                'data_bukti_minimum' => 'Nominatif, memo analisa, putusan, akad, pencairan, agunan, asuransi',
                'metode_uji' => 'Vouching berkas, perhitungan coverage dan klarifikasi',
                'metode_sampling' => 'Targeted + pembanding SOP 02',
                'perlu_fisik_default' => 'Ya',
                'keterangan_simpulan' => 'Coverage, exception, dampak dan simpulan',
            ],
            [
                'area' => 'Biaya Beban',
                'kka_tujuan' => 'KKA_Biaya_Beban',
                'tujuan_uji' => 'Membuktikan kewajaran, otorisasi dan dukungan transaksi biaya',
                'kriteria_utama' => 'Anggaran, kewenangan, invoice, pajak dan bukti penerimaan',
                'data_bukti_minimum' => 'Mutasi AD007, voucher, invoice, PO/SPK, pajak, persetujuan',
                'metode_uji' => 'Vouching, rekalkulasi, analisa pola dan klarifikasi',
                'metode_sampling' => 'Targeted + pembanding SOP 02',
                'perlu_fisik_default' => 'Ya',
                'keterangan_simpulan' => 'Selisih, pola, exception dan simpulan',
            ],
            [
                'area' => 'Kartu ATM',
                'kka_tujuan' => 'KKA_ATM',
                'tujuan_uji' => 'Membuktikan penggunaan, persediaan dan rekonsiliasi kartu/PIN',
                'kriteria_utama' => 'Nominatif persediaan, GL, register serah terima dan fisik',
                'data_bukti_minimum' => 'Nominatif, rekening koran persediaan, kartu fisik, PIN mailer, log custody',
                'metode_uji' => 'Rekonsiliasi kuantitas/nilai dan observasi custody',
                'metode_sampling' => 'Targeted atau 100% objek',
                'perlu_fisik_default' => 'Ya',
                'keterangan_simpulan' => 'Rekonsiliasi, exception dan simpulan',
            ],
            [
                'area' => 'Pengaduan',
                'kka_tujuan' => 'KKA_Pengaduan',
                'tujuan_uji' => 'Membuktikan penanganan, SLA, penyelesaian dan akar masalah',
                'kriteria_utama' => 'SLA layanan, register pengaduan dan bukti penyelesaian',
                'data_bukti_minimum' => 'Register, kronologi, komunikasi, bukti penyelesaian, kompensasi',
                'metode_uji' => 'Aging, walkthrough, uji bukti dan klarifikasi',
                'metode_sampling' => 'Targeted + pembanding SOP 02',
                'perlu_fisik_default' => 'Jika perlu',
                'keterangan_simpulan' => 'Aging, recurrence, exception dan simpulan',
            ],
            [
                'area' => 'Rekon Kartu ATM',
                'kka_tujuan' => 'KKA_ATM',
                'tujuan_uji' => 'Membuktikan kesesuaian buku, fisik kartu dan PIN mailer',
                'kriteria_utama' => 'Nominatif, GL persediaan dan standar custody',
                'data_bukti_minimum' => 'Nominatif, GL, kartu fisik, PIN mailer dan register',
                'metode_uji' => 'Rekonsiliasi kuantitas/nilai',
                'metode_sampling' => '100% jenis/objek relevan',
                'perlu_fisik_default' => 'Ya',
                'keterangan_simpulan' => 'Selisih dan simpulan rekonsiliasi',
            ],
            [
                'area' => 'Cash Opname',
                'kka_tujuan' => 'KKA_Fisik',
                'tujuan_uji' => 'Membuktikan kesesuaian saldo sistem/register dengan fisik',
                'kriteria_utama' => 'Saldo buku/GL dan ketentuan pengelolaan kas',
                'data_bukti_minimum' => 'Saldo sistem, register, hasil hitung fisik dan berita acara',
                'metode_uji' => 'Rekonsiliasi fisik dan observasi custody',
                'metode_sampling' => '100% objek terpilih',
                'perlu_fisik_default' => 'Ya',
                'keterangan_simpulan' => 'Selisih kuantitas/nilai dan simpulan',
            ],
            [
                'area' => 'Observasi Lingkungan',
                'kka_tujuan' => 'KKA_Observasi',
                'tujuan_uji' => 'Membuktikan kelayakan, keamanan, kebersihan dan fungsi CCTV',
                'kriteria_utama' => 'Standar keamanan, kelayakan area dan retensi CCTV',
                'data_bukti_minimum' => 'Foto, hasil observasi, uji playback, log akses dan wawancara',
                'metode_uji' => 'Observasi, walkthrough dan uji fungsi',
                'metode_sampling' => '100% titik kontrol scope',
                'perlu_fisik_default' => 'Ya',
                'keterangan_simpulan' => 'Kondisi, gap, dampak dan simpulan',
            ],
            [
                'area' => 'Rekon Cek BG',
                'kka_tujuan' => 'KKA_Fisik',
                'tujuan_uji' => 'Membuktikan persediaan cek/BG sistem, catatan dan fisik',
                'kriteria_utama' => 'Nominatif, GL, register nomor seri dan custody',
                'data_bukti_minimum' => 'Nominatif, GL, register petugas, fisik dan nomor seri',
                'metode_uji' => 'Rekonsiliasi kuantitas/nilai/seri',
                'metode_sampling' => '100% jenis/objek relevan',
                'perlu_fisik_default' => 'Ya',
                'keterangan_simpulan' => 'Selisih, gap seri dan simpulan',
            ],
            [
                'area' => 'Rekon Barang Cetakan',
                'kka_tujuan' => 'KKA_Fisik',
                'tujuan_uji' => 'Membuktikan catatan petugas dengan persediaan fisik',
                'kriteria_utama' => 'Register persediaan dan standar custody',
                'data_bukti_minimum' => 'Register petugas, bukti penerimaan/pengeluaran dan fisik',
                'metode_uji' => 'Rekonsiliasi kuantitas dan observasi',
                'metode_sampling' => '100% jenis/objek relevan',
                'perlu_fisik_default' => 'Ya',
                'keterangan_simpulan' => 'Selisih dan simpulan',
            ],
            [
                'area' => 'Aset',
                'kka_tujuan' => 'KKA_Fisik',
                'tujuan_uji' => 'Membuktikan keberadaan, lokasi, kondisi dan label aset',
                'kriteria_utama' => 'Register aset, BAST dan ketentuan inventarisasi',
                'data_bukti_minimum' => 'Register aset, label, BAST, foto dan fisik',
                'metode_uji' => 'Inventarisasi dan rekonsiliasi',
                'metode_sampling' => 'Targeted atau 100% objek',
                'perlu_fisik_default' => 'Ya',
                'keterangan_simpulan' => 'Selisih, kondisi dan simpulan',
            ],
            [
                'area' => 'Umum/Lainnya',
                'kka_tujuan' => 'KKA_Risk_Trigger',
                'tujuan_uji' => 'Menjawab pertanyaan audit spesifik sesuai trigger',
                'kriteria_utama' => 'Ketentuan relevan yang ditetapkan pada assignment',
                'data_bukti_minimum' => 'Bukti spesifik sesuai scope',
                'metode_uji' => 'Tracing, observasi, wawancara atau rekonsiliasi',
                'metode_sampling' => 'Sesuai SOP 02 dan sifat trigger',
                'perlu_fisik_default' => 'Jika perlu',
                'keterangan_simpulan' => 'Status trigger dan simpulan',
            ],
        ];

        foreach ($parameters as $param) {
            DB::table('risk_trigger_master_parameters')->updateOrInsert(
                ['area' => $param['area']],
                array_merge($param, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }
}
