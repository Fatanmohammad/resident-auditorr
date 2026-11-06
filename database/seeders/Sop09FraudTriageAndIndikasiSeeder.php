<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sop09TemuanIndikasiCabang;
use App\Models\Sop09FraudTriageChecklist;

class Sop09FraudTriageAndIndikasiSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Starter Data untuk 18 Indikator Temuan Mencurigakan Cabang
        if (Sop09TemuanIndikasiCabang::count() === 0) {
            $cabangs = [
                [
                    'kode_cabang' => '001',
                    'nama_cabang' => 'Kantor Cabang Utama Palu (contoh)',
                    'periode_bulan' => 'November 2026',
                    'reversal_teller' => 3,
                    'selisih_kas_teller' => 0,
                    'selisih_kas_khasanah' => 0,
                    'kas_kecil_melebihi_limit' => 0,
                    'kas_kecil_belum_selesai' => 0,
                    'dual_control_kunci_dilanggar' => 0,
                    'trx_rekening_pasif' => 1,
                    'trx_rekening_titipan' => 0,
                    'trx_tidak_wajar_pegawai' => 0,
                    'trx_luar_jam_operasional' => 2,
                    'setoran_non_tunai_pegawai' => 0,
                    'sharing_user_id' => 0,
                    'skimming_atm' => 0,
                    'selisih_kas_crm_atm' => 1,
                    'restrukturisasi_berulang' => 0,
                    'pencairan_tanpa_dokumen' => 0,
                    'cuti_wajib_belum_diambil' => 1,
                    'lonjakan_pengaduan_sla' => 0,
                    'catatan_umum' => 'Reversal teller terkonsentrasi pada User TL01 karena salah input denominasi, sudah dibina.',
                ],
                [
                    'kode_cabang' => '002',
                    'nama_cabang' => 'Kantor Cabang Parigi',
                    'periode_bulan' => 'November 2026',
                    'reversal_teller' => 1,
                    'selisih_kas_teller' => 1,
                    'selisih_kas_khasanah' => 0,
                    'kas_kecil_melebihi_limit' => 0,
                    'kas_kecil_belum_selesai' => 0,
                    'dual_control_kunci_dilanggar' => 0,
                    'trx_rekening_pasif' => 0,
                    'trx_rekening_titipan' => 0,
                    'trx_tidak_wajar_pegawai' => 0,
                    'trx_luar_jam_operasional' => 0,
                    'setoran_non_tunai_pegawai' => 0,
                    'sharing_user_id' => 0,
                    'skimming_atm' => 0,
                    'selisih_kas_crm_atm' => 0,
                    'restrukturisasi_berulang' => 0,
                    'pencairan_tanpa_dokumen' => 0,
                    'cuti_wajib_belum_diambil' => 0,
                    'lonjakan_pengaduan_sla' => 0,
                    'catatan_umum' => 'Selisih kas teller Rp 50.000 sudah disetor ke rekening penampung.',
                ],
                [
                    'kode_cabang' => '003',
                    'nama_cabang' => 'Kantor Cabang Tolitoli',
                    'periode_bulan' => 'November 2026',
                    'reversal_teller' => 0,
                    'selisih_kas_teller' => 0,
                    'selisih_kas_khasanah' => 0,
                    'kas_kecil_melebihi_limit' => 1,
                    'kas_kecil_belum_selesai' => 1,
                    'dual_control_kunci_dilanggar' => 0,
                    'trx_rekening_pasif' => 0,
                    'trx_rekening_titipan' => 0,
                    'trx_tidak_wajar_pegawai' => 0,
                    'trx_luar_jam_operasional' => 0,
                    'setoran_non_tunai_pegawai' => 0,
                    'sharing_user_id' => 0,
                    'skimming_atm' => 0,
                    'selisih_kas_crm_atm' => 0,
                    'restrukturisasi_berulang' => 1,
                    'pencairan_tanpa_dokumen' => 0,
                    'cuti_wajib_belum_diambil' => 0,
                    'lonjakan_pengaduan_sla' => 0,
                    'catatan_umum' => 'Kas kecil melampaui limit Rp 5jt karena biaya renovasi mendesak, SPMK sedang diproses.',
                ],
                [
                    'kode_cabang' => '004',
                    'nama_cabang' => 'Kantor Cabang Luwuk',
                    'periode_bulan' => 'November 2026',
                    'reversal_teller' => 0,
                    'selisih_kas_teller' => 0,
                    'selisih_kas_khasanah' => 0,
                    'kas_kecil_melebihi_limit' => 0,
                    'kas_kecil_belum_selesai' => 0,
                    'dual_control_kunci_dilanggar' => 0,
                    'trx_rekening_pasif' => 0,
                    'trx_rekening_titipan' => 0,
                    'trx_tidak_wajar_pegawai' => 0,
                    'trx_luar_jam_operasional' => 0,
                    'setoran_non_tunai_pegawai' => 0,
                    'sharing_user_id' => 0,
                    'skimming_atm' => 0,
                    'selisih_kas_crm_atm' => 0,
                    'restrukturisasi_berulang' => 0,
                    'pencairan_tanpa_dokumen' => 0,
                    'cuti_wajib_belum_diambil' => 0,
                    'lonjakan_pengaduan_sla' => 0,
                    'catatan_umum' => 'Operasional tertib sesuai SOP.',
                ],
                [
                    'kode_cabang' => '005',
                    'nama_cabang' => 'Kantor Cabang Poso',
                    'periode_bulan' => 'November 2026',
                    'reversal_teller' => 2,
                    'selisih_kas_teller' => 0,
                    'selisih_kas_khasanah' => 0,
                    'kas_kecil_melebihi_limit' => 0,
                    'kas_kecil_belum_selesai' => 0,
                    'dual_control_kunci_dilanggar' => 0,
                    'trx_rekening_pasif' => 0,
                    'trx_rekening_titipan' => 1,
                    'trx_tidak_wajar_pegawai' => 0,
                    'trx_luar_jam_operasional' => 1,
                    'setoran_non_tunai_pegawai' => 0,
                    'sharing_user_id' => 0,
                    'skimming_atm' => 0,
                    'selisih_kas_crm_atm' => 0,
                    'restrukturisasi_berulang' => 0,
                    'pencairan_tanpa_dokumen' => 0,
                    'cuti_wajib_belum_diambil' => 0,
                    'lonjakan_pengaduan_sla' => 0,
                    'catatan_umum' => 'Rekening titipan klaim asuransi mengendap 45 hari menunggu kelengkapan ahli waris.',
                ],
            ];

            foreach ($cabangs as $c) {
                Sop09TemuanIndikasiCabang::create($c);
            }
        }

        // 2. Starter Kertas Kerja Fraud Triage Checklist RA (Contoh Resmi KCU Palu)
        if (Sop09FraudTriageChecklist::count() === 0) {
            $questions = self::getMasterChecklistQuestions();
            $answers = [];
            $yaCount = 0;
            $tidakCount = 0;
            $hasCritical = false;

            foreach ($questions as $q) {
                // Beri contoh jawaban realistis
                $jawaban = 'Tidak';
                $catatan = 'Pemeriksaan sampel dokumen dan log sistem tidak menemukan anomali.';

                if ($q['no'] === 2) {
                    $jawaban = 'Ya';
                    $catatan = 'Ditemukan 2 transaksi batch posting pada pukul 21:15 WIB, telah diverifikasi ada memo persetujuan akhir bulan.';
                    $yaCount++;
                } elseif ($q['no'] === 22) {
                    $jawaban = 'Ya';
                    $catatan = '1 orang Kasir belum mengajukan cuti wajib dalam 6 bulan terakhir karena beban kerja akhir tahun.';
                    $yaCount++;
                } else {
                    $tidakCount++;
                }

                $answers[] = [
                    'no' => $q['no'],
                    'section' => $q['section'],
                    'question' => $q['question'],
                    'is_critical' => $q['is_critical'],
                    'jawaban' => $jawaban,
                    'catatan' => $catatan,
                    'regulasi' => $q['regulasi'],
                ];
            }

            Sop09FraudTriageChecklist::create([
                'nomor_triage' => 'TRG-202611-0001',
                'kode_unit' => '001',
                'nama_cabang' => 'Kantor Cabang Utama Palu (contoh)',
                'tanggal_pemeriksaan' => '2026-11-05',
                'nama_ra' => 'Ahmad Fathoni, S.E., CFrA',
                'checklist_answers' => $answers,
                'total_item' => 28,
                'dijawab_ya' => $yaCount,
                'dijawab_tidak' => $tidakCount,
                'belum_dijawab' => 0,
                'ada_critical_trigger' => $hasCritical,
                'keputusan_triage' => 'KLARIFIKASI TAMBAHAN',
                'catatan_kesimpulan' => 'Berdasarkan hasil triage awal instrumen POJK 12/2024, tidak ditemukan indikasi kritis (Item 3, 15, 17 bernilai Tidak). Terdapat 2 catatan administratif (transaksi jam operasional dan cuti wajib) yang memerlukan klarifikasi tambahan dan tindak lanjut monitoring unit kerja.',
                'status' => 'Final',
            ]);
        }
    }

    public static function getMasterChecklistQuestions(): array
    {
        return [
            // A. TRANSAKSI KEUANGAN YANG TIDAK WAJAR
            [
                'no' => 1,
                'section' => 'A. TRANSAKSI KEUANGAN YANG TIDAK WAJAR',
                'question' => 'Apakah terdapat transaksi tunai atau pemindahbukuan dalam jumlah yang tidak sesuai dengan profil atau aktivitas normal nasabah berdasarkan riwayat transaksi?',
                'is_critical' => false,
                'regulasi' => 'POJK 12/2024 (Pilar Deteksi – indikator transaksi tidak wajar); Panduan SAF BPR OJK 2022'
            ],
            [
                'no' => 2,
                'section' => 'A. TRANSAKSI KEUANGAN YANG TIDAK WAJAR',
                'question' => 'Apakah terdapat transaksi yang dilakukan di luar jam operasional normal cabang atau pada hari libur tanpa otorisasi khusus yang terdokumentasi?',
                'is_critical' => false,
                'regulasi' => 'POJK 12/2024 (Pilar Deteksi); Panduan SAF BPR OJK 2022 (red flag transaksi di luar kewajaran operasional)'
            ],
            [
                'no' => 3,
                'section' => 'A. TRANSAKSI KEUANGAN YANG TIDAK WAJAR',
                'question' => '⚡ Apakah terdapat transaksi pembatalan, koreksi, atau reversal yang dilakukan berulang kali oleh pegawai yang sama dalam periode singkat tanpa penjelasan bisnis yang memadai?',
                'is_critical' => true,
                'regulasi' => 'POJK 12/2024 (Pilar Deteksi); Panduan SAF BPR OJK 2022; ACFE FEM – Initial Assessment'
            ],
            [
                'no' => 4,
                'section' => 'A. TRANSAKSI KEUANGAN YANG TIDAK WAJAR',
                'question' => 'Apakah terdapat transaksi pada rekening dormant (tidak aktif) yang tiba-tiba aktif kembali tanpa instruksi resmi yang dapat diverifikasi dari nasabah?',
                'is_critical' => false,
                'regulasi' => 'Panduan SAF BPR OJK 2022 (red flag rekening tidak aktif tiba-tiba aktif); ACFE FEM – Initial Assessment'
            ],
            [
                'no' => 5,
                'section' => 'A. TRANSAKSI KEUANGAN YANG TIDAK WAJAR',
                'question' => 'Apakah terdapat transaksi yang tidak memiliki otorisasi dari pejabat yang berwenang sesuai limit transaksi yang berlaku di cabang?',
                'is_critical' => false,
                'regulasi' => 'POJK 12/2024 (Pilar Pencegahan – kewenangan transaksi); Kebijakan limit transaksi internal bank'
            ],

            // B. KELENGKAPAN DAN KEASLIAN DOKUMEN
            [
                'no' => 6,
                'section' => 'B. KELENGKAPAN DAN KEASLIAN DOKUMEN',
                'question' => 'Apakah terdapat dokumen transaksi atau kredit yang tidak lengkap, rusak, atau menunjukkan tanda-tanda perubahan yang tidak resmi (misalnya: coretan tidak berparaf, data yang ditimpa, tanda tangan yang meragukan)?',
                'is_critical' => false,
                'regulasi' => 'POJK 12/2024 (Pilar Deteksi – indikator pemalsuan dokumen); Panduan SAF BPR OJK 2022'
            ],
            [
                'no' => 7,
                'section' => 'B. KELENGKAPAN DAN KEASLIAN DOKUMEN',
                'question' => 'Apakah terdapat ketidaksesuaian antara data nasabah atau nilai transaksi yang tercatat di sistem core banking dengan dokumen fisik yang tersedia?',
                'is_critical' => false,
                'regulasi' => 'Panduan SAF BPR OJK 2022 (red flag selisih data sistem vs. dokumen fisik); ACFE FEM – Initial Assessment'
            ],
            [
                'no' => 8,
                'section' => 'B. KELENGKAPAN DAN KEASLIAN DOKUMEN',
                'question' => 'Apakah terdapat dokumen yang tidak melalui prosedur persetujuan atau penandatanganan yang dipersyaratkan (misalnya: tidak ada tanda tangan kepala cabang, tidak ada paraf checker)?',
                'is_critical' => false,
                'regulasi' => 'POJK 12/2024 (Pilar Pencegahan – kewenangan persetujuan); Kebijakan prosedur internal bank'
            ],
            [
                'no' => 9,
                'section' => 'B. KELENGKAPAN DAN KEASLIAN DOKUMEN',
                'question' => 'Apakah terdapat perubahan data nasabah (nama, alamat, nomor rekening tujuan, limit transaksi) yang dilakukan oleh pegawai tanpa adanya permohonan tertulis dari nasabah yang dapat diverifikasi?',
                'is_critical' => false,
                'regulasi' => 'Panduan SAF BPR OJK 2022 (red flag manipulasi data nasabah); ACFE FEM – Initial Assessment'
            ],

            // C. KREDIT DAN PEMBIAYAAN
            [
                'no' => 10,
                'section' => 'C. KREDIT DAN PEMBIAYAAN',
                'question' => 'Apakah terdapat pencairan kredit yang dilakukan sebelum seluruh dokumen persyaratan pencairan terpenuhi sesuai ketentuan internal bank?',
                'is_critical' => false,
                'regulasi' => 'POJK 12/2024 (Pilar Pencegahan); Prinsip kehati-hatian perbankan; Kebijakan perkreditan internal bank'
            ],
            [
                'no' => 11,
                'section' => 'C. KREDIT DAN PEMBIAYAAN',
                'question' => 'Apakah terdapat indikasi kredit yang diberikan kepada pihak yang memiliki hubungan dengan pegawai cabang (keluarga, rekanan, pihak afiliasi) tanpa pengungkapan kepada pejabat pemutus yang berwenang?',
                'is_critical' => false,
                'regulasi' => 'POJK 12/2024 (Pilar Pencegahan – benturan kepentingan); Referensi prinsip BMPK (ketentuan pihak terkait)'
            ],
            [
                'no' => 12,
                'section' => 'C. KREDIT DAN PEMBIAYAAN',
                'question' => 'Apakah terdapat perubahan nilai agunan, rating internal, atau data debitur yang dilakukan setelah persetujuan kredit tanpa dasar penilaian ulang yang dapat dipertanggungjawabkan?',
                'is_critical' => false,
                'regulasi' => 'Panduan SAF BPR OJK 2022 (red flag manipulasi data kredit pasca persetujuan); ACFE FEM – Initial Assessment'
            ],
            [
                'no' => 13,
                'section' => 'C. KREDIT DAN PEMBIAYAAN',
                'question' => 'Apakah terdapat kredit bermasalah yang dilunasi secara tiba-tiba tanpa sumber dana yang dapat diidentifikasi secara wajar, atau tanpa didahului proses restrukturisasi yang tercatat?',
                'is_critical' => false,
                'regulasi' => 'Panduan SAF BPR OJK 2022 (red flag pelunasan kredit bermasalah yang tidak wajar); ACFE FEM – Initial Assessment'
            ],
            [
                'no' => 14,
                'section' => 'C. KREDIT DAN PEMBIAYAAN',
                'question' => 'Apakah terdapat agunan kredit yang tidak dapat diverifikasi keberadaan fisiknya, atau nilainya jauh berbeda dari yang tercatat dalam berkas kredit saat pemeriksaan?',
                'is_critical' => false,
                'regulasi' => 'Panduan SAF BPR OJK 2022 (red flag agunan fiktif atau tidak wajar); Prinsip kehati-hatian perbankan'
            ],

            // D. KAS DAN REKENING NASABAH
            [
                'no' => 15,
                'section' => 'D. KAS DAN REKENING NASABAH',
                'question' => '⚡ Apakah terdapat selisih kas yang tidak dapat dijelaskan berdasarkan hasil penghitungan fisik atau rekonsiliasi kas pada saat pemeriksaan?',
                'is_critical' => true,
                'regulasi' => 'POJK 12/2024 (Pilar Deteksi – selisih kas tidak wajar); Panduan SAF BPR OJK 2022'
            ],
            [
                'no' => 16,
                'section' => 'D. KAS DAN REKENING NASABAH',
                'question' => 'Apakah terdapat bukti akses atau perubahan data pada rekening nasabah yang dilakukan oleh pegawai tanpa instruksi tertulis yang dapat diverifikasi dari nasabah yang bersangkutan?',
                'is_critical' => false,
                'regulasi' => 'Panduan SAF BPR OJK 2022 (red flag akses rekening tanpa otorisasi); ACFE FEM – Initial Assessment'
            ],
            [
                'no' => 17,
                'section' => 'D. KAS DAN REKENING NASABAH',
                'question' => '⚡ Apakah terdapat keluhan dari nasabah mengenai transaksi yang tidak pernah diperintahkan atau tidak sesuai dengan instruksi yang diberikan?',
                'is_critical' => true,
                'regulasi' => 'POJK 12/2024 (Pilar Deteksi – saluran pelaporan keluhan); Panduan SAF BPR OJK 2022'
            ],

            // E. PENYALAHGUNAAN KEWENANGAN DAN AKSES SISTEM
            [
                'no' => 18,
                'section' => 'E. PENYALAHGUNAAN KEWENANGAN DAN AKSES SISTEM',
                'question' => 'Apakah terdapat pegawai yang secara bersamaan menjalankan fungsi yang seharusnya dipisahkan, misalnya satu orang sekaligus menginput dan mengotorisasi transaksi yang sama?',
                'is_critical' => false,
                'regulasi' => 'POJK 12/2024 (Pilar Pencegahan – segregation of duties); Prinsip pengendalian internal (four-eyes principle)'
            ],
            [
                'no' => 19,
                'section' => 'E. PENYALAHGUNAAN KEWENANGAN DAN AKSES SISTEM',
                'question' => 'Apakah terdapat bukti akses sistem oleh pegawai yang tidak sesuai dengan jabatan atau level kewenangan (user access level) yang ditetapkan untuknya?',
                'is_critical' => false,
                'regulasi' => 'POJK 12/2024 (Pilar Pencegahan – hak akses sistem); Panduan SAF BPR OJK 2022; Kebijakan keamanan informasi bank'
            ],
            [
                'no' => 20,
                'section' => 'E. PENYALAHGUNAAN KEWENANGAN DAN AKSES SISTEM',
                'question' => 'Apakah terdapat pengesampingan (override) kontrol sistem yang tidak disertai dokumentasi alasan yang memadai atau tidak melalui prosedur persetujuan yang berlaku?',
                'is_critical' => false,
                'regulasi' => 'POJK 12/2024 (Pilar Pencegahan); ACFE FEM – red flag management override of controls'
            ],
            [
                'no' => 21,
                'section' => 'E. PENYALAHGUNAAN KEWENANGAN DAN AKSES SISTEM',
                'question' => 'Apakah terdapat indikasi penggunaan user ID atau akses sistem milik pegawai lain untuk melakukan transaksi atau perubahan data?',
                'is_critical' => false,
                'regulasi' => 'POJK 12/2024 (Pilar Pencegahan); Kebijakan keamanan sistem informasi dan autentikasi pengguna'
            ],

            // F. PERILAKU PEGAWAI
            [
                'no' => 22,
                'section' => 'F. PERILAKU PEGAWAI',
                'question' => 'Apakah terdapat pegawai yang secara konsisten menolak mengambil hak cuti tahunannya atau menolak dirotasi dari posisinya tanpa alasan yang dapat dipertanggungjawabkan?',
                'is_critical' => false,
                'regulasi' => 'Panduan SAF BPR OJK 2022 (red flag pegawai yang tidak mau cuti atau rotasi); ACFE FEM – Behavioral Red Flags'
            ],
            [
                'no' => 23,
                'section' => 'F. PERILAKU PEGAWAI',
                'question' => 'Apakah terdapat pegawai yang menunjukkan gaya hidup yang secara mencolok tidak sebanding dengan penghasilan yang diketahui (misalnya: kepemilikan kendaraan mewah, aset bernilai tinggi yang tidak wajar)?',
                'is_critical' => false,
                'regulasi' => 'Panduan SAF BPR OJK 2022; ACFE FEM – Behavioral Red Flags (unexplained wealth)'
            ],
            [
                'no' => 24,
                'section' => 'F. PERILAKU PEGAWAI',
                'question' => 'Apakah terdapat pegawai yang bersikap tidak kooperatif, defensif secara berlebihan, atau berupaya mempersulit akses terhadap dokumen, sistem, atau informasi yang diperlukan dalam pemeriksaan ini?',
                'is_critical' => false,
                'regulasi' => 'ACFE FEM – Initial Response to Suspicions of Fraud (red flag penolakan kooperasi)'
            ],
            [
                'no' => 25,
                'section' => 'F. PERILAKU PEGAWAI',
                'question' => 'Apakah terdapat informasi yang dapat dipercaya bahwa seorang pegawai sedang atau pernah mengalami tekanan keuangan yang signifikan (hutang besar, masalah perjudian, atau kebutuhan mendesak di luar kemampuan penghasilan normal)?',
                'is_critical' => false,
                'regulasi' => 'ACFE FEM – Fraud Triangle (Pressure/Incentive); Panduan SAF BPR OJK 2022 (referensi metodologi – faktor tekanan)'
            ],

            // G. PENGENDALIAN INTERNAL CABANG
            [
                'no' => 26,
                'section' => 'G. PENGENDALIAN INTERNAL CABANG',
                'question' => 'Apakah terdapat laporan harian, mingguan, atau berkala cabang yang tidak diserahkan tepat waktu atau tidak lengkap secara berulang dalam periode pemeriksaan?',
                'is_critical' => false,
                'regulasi' => 'POJK 12/2024 (Pilar Monitoring & Evaluasi); Kebijakan pelaporan internal bank'
            ],
            [
                'no' => 27,
                'section' => 'G. PENGENDALIAN INTERNAL CABANG',
                'question' => 'Apakah terdapat temuan pemeriksaan sebelumnya (audit internal, audit eksternal, atau pemeriksaan regulator) yang belum ditindaklanjuti secara memadai hingga saat ini?',
                'is_critical' => false,
                'regulasi' => 'POJK 12/2024 (Pilar Monitoring, Evaluasi & Tindak Lanjut); Prinsip audit berkelanjutan'
            ],
            [
                'no' => 28,
                'section' => 'G. PENGENDALIAN INTERNAL CABANG',
                'question' => 'Apakah terdapat fasilitas pengamanan fisik (CCTV, brankas, kunci ruangan kas, sistem alarm) yang tidak berfungsi, rusak, dinonaktifkan, atau menunjukkan tanda-tanda manipulasi?',
                'is_critical' => false,
                'regulasi' => 'Panduan SAF BPR OJK 2022; Referensi praktik – kontrol fisik sebagai komponen pencegahan fraud'
            ],
        ];
    }
}

