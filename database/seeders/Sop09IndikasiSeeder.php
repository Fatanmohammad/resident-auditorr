<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sop09RegisterIndikasi;
use App\Models\Sop09ChainOfCustody;
use App\Models\Sop09Triase;
use App\Models\Sop09TriaseAwal;

class Sop09IndikasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = [
            [
                'indikasi_id' => 'IND-202610-0001',
                'tanggal_diterima' => '2026-10-15',
                'jam' => '09:30',
                'kode_unit' => '501',
                'nama_unit' => 'Kantor Cabang Pembantu Bahomotefe',
                'ra_id' => 'RA-501',
                'nama_ra' => 'Andi (Resident Auditor)',
                'sumber_indikasi' => 'AUDIT_ONSITE',
                'referensi_sop_sumber' => 'SOP-04 KKA Teller & Kas',
                'area' => 'Operasional Kas & Teller',
                'media_sarana' => 'Pemeriksaan Fisik Kas Mendadak (Cash Opname)',
                'pemberi_informasi_role' => 'Resident Auditor Onsite',
                'fakta_awal_terverifikasi' => 'Ditemukan selisih fisik kas sebesar Rp 25.000.000 pada saat cash opname mendadak kas teller tanpa Berita Acara Selisih Kas.',
                'dugaan_belum_terverifikasi' => 'Diduga adanya penggunaan kas bank untuk kepentingan pribadi sementara (teeming & lading) sebelum jam tutup kas harian.',
                'red_flag_utama' => 'Selisih fisik kas teller harian tidak wajar dan keterlambatan pembukuan transaksi setoran nasabah',
                'nominal_awal' => 25000000,
                'nasabah_terdampak' => 'TIDAK',
                'pihak_kunci_pimpinan' => 'TIDAK',
                'risiko_bukti_hilang' => 'TINGGI',
                'sensitivitas_reputasi' => 'SEDANG',
                'konflik_kepentingan_ra' => 'TIDAK',
                'klasifikasi_kerahasiaan' => 'RAHASIA',
                'status_triase' => 'SELESAI',
                'keputusan_final' => 'INVESTIGASI_LANJUT',
                'status_kasus' => 'OPEN',
                'catatan' => 'Prioritas penanganan bukti fisik kas dan rekaman CCTV ruangan teller.',
            ],
            [
                'indikasi_id' => 'IND-202610-0002',
                'tanggal_diterima' => '2026-10-18',
                'jam' => '14:15',
                'kode_unit' => '001',
                'nama_unit' => 'Kantor Cabang Utama Palu',
                'ra_id' => 'RA-001',
                'nama_ra' => 'Andi (Resident Auditor)',
                'sumber_indikasi' => 'TEMUAN_OFFSITE',
                'referensi_sop_sumber' => 'SOP-02 EWS Transaksi Harian',
                'area' => 'DPK & Layanan',
                'media_sarana' => 'Alert EWS Rekening Dormant',
                'pemberi_informasi_role' => 'Analisis Data Offsite SKAI',
                'fakta_awal_terverifikasi' => 'Penarikan dana tunai dari rekening tabungan pasif (dormant) senilai Rp 75.000.000 dengan spesimen tanda tangan meragukan.',
                'dugaan_belum_terverifikasi' => 'Dugaan pemalsuan slip penarikan dan identitas nasabah oleh pihak luar bekerjasama dengan oknum internal.',
                'red_flag_utama' => 'Aktivasi rekening dormant diikuti penarikan tunai signifikan dalam hitungan menit',
                'nominal_awal' => 75000000,
                'nasabah_terdampak' => 'YA',
                'pihak_kunci_pimpinan' => 'TIDAK',
                'risiko_bukti_hilang' => 'TINGGI',
                'sensitivitas_reputasi' => 'TINGGI',
                'konflik_kepentingan_ra' => 'TIDAK',
                'klasifikasi_kerahasiaan' => 'SANGAT_RAHASIA',
                'status_triase' => 'SELESAI',
                'keputusan_final' => 'INVESTIGASI_LANJUT',
                'status_kasus' => 'OPEN',
                'catatan' => 'Segera amankan slip penarikan asli dan log akses mutasi sistem CBS.',
            ],
            [
                'indikasi_id' => 'IND-202610-0003',
                'tanggal_diterima' => '2026-10-22',
                'jam' => '11:00',
                'kode_unit' => '102',
                'nama_unit' => 'Kantor Cabang Parigi',
                'ra_id' => 'RA-102',
                'nama_ra' => 'Andi (Resident Auditor)',
                'sumber_indikasi' => 'LAPORAN_CABANG',
                'referensi_sop_sumber' => 'Laporan Pengaduan Audit',
                'area' => 'Kredit & Pembiayaan',
                'media_sarana' => 'Surat Pengaduan Debitur / Nasabah',
                'pemberi_informasi_role' => 'Bagian Kepatuhan Cabang',
                'fakta_awal_terverifikasi' => 'Sertifikat hak milik agunan kredit tidak terdaftar di BPN setempat saat dilakukan pengecekan legalitas berkas kredit macet.',
                'dugaan_belum_terverifikasi' => 'Dugaan rekayasa berkas kredit dan agunan fiktif oleh oknum Account Officer.',
                'red_flag_utama' => 'Kredit macet menunggak sejak pencairan pertama dengan agunan tidak terverifikasi keabsahannya',
                'nominal_awal' => 150000000,
                'nasabah_terdampak' => 'YA',
                'pihak_kunci_pimpinan' => 'TIDAK',
                'risiko_bukti_hilang' => 'SEDANG',
                'sensitivitas_reputasi' => 'TINGGI',
                'konflik_kepentingan_ra' => 'TIDAK',
                'klasifikasi_kerahasiaan' => 'RAHASIA',
                'status_triase' => 'SELESAI',
                'keputusan_final' => 'INVESTIGASI_LANJUT',
                'status_kasus' => 'OPEN',
                'catatan' => 'Amankan berkas permohonan kredit asli dan tanda terima dokumen agunan.',
            ],
            [
                'indikasi_id' => 'IND-202610-0004',
                'tanggal_diterima' => '2026-10-25',
                'jam' => '16:00',
                'kode_unit' => '105',
                'nama_unit' => 'Kantor Cabang Tolitoli',
                'ra_id' => 'RA-105',
                'nama_ra' => 'Andi (Resident Auditor)',
                'sumber_indikasi' => 'WHISTLEBLOWING',
                'referensi_sop_sumber' => 'Portal WBS Bank Sulteng',
                'area' => 'Operasional ATM & Kartu',
                'media_sarana' => 'Pengaduan Anonim WBS',
                'pemberi_informasi_role' => 'Karyawan Internal Cabang',
                'fakta_awal_terverifikasi' => 'Terdapat 14 kartu ATM tertelan yang tidak dicatat pada register kartu tertelan dan tidak dimusnahkan sesuai batas waktu SOP.',
                'dugaan_belum_terverifikasi' => 'Dugaan penyalahgunaan kartu ATM tertelan untuk transaksi ilegal sebelum kartu diblokir.',
                'red_flag_utama' => 'Fisik kartu ATM tertelan hilang dari tempat penyimpanan tanpa berita acara serah terima',
                'nominal_awal' => 12500000,
                'nasabah_terdampak' => 'YA',
                'pihak_kunci_pimpinan' => 'TIDAK',
                'risiko_bukti_hilang' => 'TINGGI',
                'sensitivitas_reputasi' => 'SEDANG',
                'konflik_kepentingan_ra' => 'TIDAK',
                'klasifikasi_kerahasiaan' => 'RAHASIA',
                'status_triase' => 'SELESAI',
                'keputusan_final' => 'INVESTIGASI_LANJUT',
                'status_kasus' => 'OPEN',
                'catatan' => 'Lakukan audit trail mesin ATM dan log CCTV ruangan mesin ATM.',
            ],
        ];

        foreach ($cases as $c) {
            Sop09RegisterIndikasi::updateOrCreate(
                ['indikasi_id' => $c['indikasi_id']],
                $c
            );
        }

        // Starter Chain of Custody
        $custodies = [
            [
                'kode_bukti' => 'COC-20261028-101',
                'indikasi_id' => 'IND-202610-0001',
                'unit' => 'Kantor Cabang Pembantu Bahomotefe',
                'jenis_bukti' => 'DOKUMEN_FISIK',
                'deskripsi_relevansi' => 'Slip setoran dan penarikan kas teller asli tanggal 15 Oktober 2026 berseri TR-501-8841',
                'sumber_perolehan' => 'Pimpinan Cabang Pembantu Bahomotefe',
                'tanggal_diperoleh' => '2026-10-16',
                'asli_salinan' => 'ASLI',
                'lokasi_penyimpanan' => 'Brankas SKAI Kantor Pusat Lt. 3 (Safe Box A-12)',
                'klasifikasi_akses' => 'TERBATAS',
                'diperoleh_oleh' => 'Andi (Resident Auditor)',
                'status_integritas' => 'UTUH',
                'pemegang_saat_ini' => 'BRANKAS_SKAI',
                'catatan' => 'Dokumen asli dimasukkan ke kantong bukti bersegel anti-tamper No. Seal: SKAI-0941',
            ],
            [
                'kode_bukti' => 'COC-20261028-102',
                'indikasi_id' => 'IND-202610-0001',
                'unit' => 'Kantor Cabang Pembantu Bahomotefe',
                'jenis_bukti' => 'FILE_DIGITAL',
                'deskripsi_relevansi' => 'Ekspor rekaman CCTV kamera channel 02 & 04 area kasir/teller tanggal 15 Oktober 2026 pukul 08:00 - 10:30 WITA',
                'sumber_perolehan' => 'DVR CCTV Cabang Bahomotefe via Petugas IT/Security',
                'tanggal_diperoleh' => '2026-10-16',
                'asli_salinan' => 'SALINAN_DIGITAL',
                'lokasi_penyimpanan' => 'Storage Enkripsi Server SKAI / Safe USB-08',
                'klasifikasi_akses' => 'RAHASIA',
                'diperoleh_oleh' => 'Andi (Resident Auditor)',
                'status_integritas' => 'UTUH',
                'pemegang_saat_ini' => 'BRANKAS_SKAI',
                'catatan' => 'File video format .mp4 dengan nilai SHA-256 terverifikasi dan tersimpan dalam drive terenkripsi',
            ],
        ];

        foreach ($custodies as $custody) {
            Sop09ChainOfCustody::updateOrCreate(
                ['kode_bukti' => $custody['kode_bukti']],
                $custody
            );
        }

        // Starter Triase Awal
        $triaseData = [
            [
                'indikasi_id' => 'IND-202610-0001',
                'tanggal' => '2026-10-16',
                'unit' => 'Kantor Cabang Pembantu Bahomotefe',
                'sumber' => 'AUDIT_ONSITE',
                'risiko_bukti_hilang' => 'TINGGI',
                'pola_berulang' => 'YA',
                'nasabah' => 'TIDAK',
                'pihak_kunci_pimpinan' => 'TIDAK',
                'skor_prioritas' => 5,
                'urgent' => 'YA',
                'keputusan_triase' => 'INVESTIGASI_LANJUT',
            ],
            [
                'indikasi_id' => 'IND-202610-0002',
                'tanggal' => '2026-10-19',
                'unit' => 'Kantor Cabang Utama Palu',
                'sumber' => 'TEMUAN_OFFSITE',
                'risiko_bukti_hilang' => 'TINGGI',
                'pola_berulang' => 'TIDAK',
                'nasabah' => 'YA',
                'pihak_kunci_pimpinan' => 'TIDAK',
                'skor_prioritas' => 5,
                'urgent' => 'YA',
                'keputusan_triase' => 'INVESTIGASI_LANJUT',
            ]
        ];

        foreach ($triaseData as $tr) {
            // Isi ke tabel sop09_triases jika ada atau model Sop09Triase
            try {
                Sop09Triase::updateOrCreate(['indikasi_id' => $tr['indikasi_id']], $tr);
            } catch (\Exception $e) {
                // Ignore jika kolom skema berbeda
            }
        }
    }
}
