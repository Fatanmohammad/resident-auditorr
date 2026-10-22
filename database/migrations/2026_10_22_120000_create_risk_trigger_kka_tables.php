<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. KKA INDUK: KKA_Risk_Trigger (20 Kolom Excel A s.d. T)
        Schema::create('risk_trigger_kka_induks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_scope_id')->constrained('assignment_scopes')->onDelete('cascade');
            $table->foreignId('register_trigger_id')->nullable()->constrained('register_triggers')->onDelete('set null');
            $table->string('kode_unit', 50)->index();
            $table->string('visit_id', 100)->index(); // Kolom A
            $table->string('trigger_id', 100)->index(); // Kolom B
            $table->string('unit_kantor', 200); // Kolom C
            $table->string('area', 100); // Kolom D
            $table->string('risk_level', 50); // Kolom E
            $table->text('pertanyaan_audit')->nullable(); // Kolom F (Rumus Otomatis)
            $table->text('kondisi_awal')->nullable(); // Kolom G (dari RegisterTrigger)
            $table->text('kriteria')->nullable(); // Kolom H (dari ProgramKerjaData)
            $table->text('prosedur_utama')->nullable(); // Kolom I (dari ProgramKerjaData)

            // Kolom Input Pengujian Profesional RA (J s.d. N, Q)
            $table->text('ringkasan_bukti')->nullable(); // Kolom J
            $table->text('hasil_analisis')->nullable(); // Kolom K
            $table->string('trigger_terbukti', 30)->nullable(); // Kolom L (Ya / Tidak / Sebagian)
            $table->string('perlu_perluasan', 20)->nullable(); // Kolom M (Ya / Tidak)
            $table->text('alasan_perluasan')->nullable(); // Kolom N (Wajib jika Ya)
            $table->string('kka_area', 100)->nullable(); // Kolom O (KKA Tujuan)
            $table->integer('exception_area')->default(0); // Kolom P (COUNTIFS Lintas 8 KKA Area)
            $table->text('simpulan_ra')->nullable(); // Kolom Q (Simpulan Profesional RA)

            // Kolom Pelaksana & Status Review (R, S, T)
            $table->string('ra_pelaksana', 150)->nullable(); // Kolom R
            $table->string('reviewer', 150)->nullable(); // Kolom S
            $table->string('status_review', 50)->default('DRAFT / BELUM DIREVIEW'); // Kolom T (DRAFT / BELUM DIREVIEW, REVISI, FINAL)

            $table->timestamps();
        });

        // 2. KKA_Teller_Kas (24 Kolom A s.d. X)
        Schema::create('risk_trigger_kka_teller_kas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_scope_id')->constrained('assignment_scopes')->onDelete('cascade');
            $table->string('kode_unit', 50)->index();
            $table->string('visit_id', 100)->index(); // Kolom A
            $table->string('trigger_id', 100)->index(); // Kolom B
            $table->string('unit_kantor', 200); // Kolom C
            $table->string('area', 100)->default('Teller Kas'); // Kolom D

            // Kolom Input (E s.d. N, P s.d. V)
            $table->string('no_referensi', 100)->nullable(); // Kolom E
            $table->date('tanggal_transaksi')->nullable(); // Kolom F
            $table->text('redaksi')->nullable(); // Kolom G
            $table->string('d_k', 10)->nullable(); // Kolom H (D / K)
            $table->decimal('nominal', 18, 2)->default(0); // Kolom I
            $table->string('user_id_transaksi', 100)->nullable(); // Kolom J
            $table->string('jenis_transaksi', 100)->nullable(); // Kolom K
            $table->string('nama_transaksi', 150)->nullable(); // Kolom L
            $table->decimal('nominal_pasangan', 18, 2)->nullable(); // Kolom M
            $table->decimal('bukti_kas_voucher', 18, 2)->nullable(); // Kolom N
            $table->decimal('selisih', 18, 2)->nullable(); // Kolom O (Rumus Otomatis N - M)
            $table->string('reversal_match', 20)->default('Ya'); // Kolom P (Ya / Tidak)
            $table->string('otorisasi_sesuai', 20)->default('Ya'); // Kolom Q (Ya / Tidak)
            $table->text('analisis_pola')->nullable(); // Kolom R
            $table->text('sebab')->nullable(); // Kolom S
            $table->text('dampak')->nullable(); // Kolom T
            $table->text('referensi_bukti')->nullable(); // Kolom U
            $table->text('simpulan')->nullable(); // Kolom V

            // Output Rumus (W & X)
            $table->string('status_uji', 50)->default('BELUM LENGKAP'); // Kolom W
            $table->string('exception', 20)->default('Tidak'); // Kolom X (Ya / Tidak)

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 3. KKA_DPK_Layanan (22 Kolom A s.d. V)
        Schema::create('risk_trigger_kka_dpk_layanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_scope_id')->constrained('assignment_scopes')->onDelete('cascade');
            $table->string('kode_unit', 50)->index();
            $table->string('visit_id', 100)->index(); // Kolom A
            $table->string('trigger_id', 100)->index(); // Kolom B
            $table->string('unit_kantor', 200); // Kolom C
            $table->string('area', 100)->default('DPK Layanan'); // Kolom D

            // Kolom Input (E s.d. K, M s.d. T)
            $table->string('no_cif_rekening', 100)->nullable(); // Kolom E
            $table->string('produk_layanan', 150)->nullable(); // Kolom F
            $table->string('nama_nasabah', 150)->nullable(); // Kolom G
            $table->string('risk_nasabah', 50)->nullable(); // Kolom H
            $table->string('pep_high_risk', 20)->default('Tidak'); // Kolom I (Ya / Tidak)
            $table->integer('dokumen_wajib')->default(0); // Kolom J
            $table->integer('dokumen_tersedia')->default(0); // Kolom K
            $table->decimal('kelengkapan_persen', 8, 2)->default(0); // Kolom L (Rumus Otomatis K / J * 100)
            $table->string('kyc_terakhir', 100)->nullable(); // Kolom M
            $table->string('otorisasi_sesuai', 20)->default('Ya'); // Kolom N (Ya / Tidak)
            $table->string('profil_transaksi_sesuai', 20)->default('Ya'); // Kolom O (Ya / Tidak)
            $table->text('analisis_kyc_risiko')->nullable(); // Kolom P
            $table->text('sebab')->nullable(); // Kolom Q
            $table->text('dampak')->nullable(); // Kolom R
            $table->text('referensi_bukti')->nullable(); // Kolom S
            $table->text('simpulan')->nullable(); // Kolom T

            // Output Rumus (U & V)
            $table->string('status_uji', 50)->default('BELUM LENGKAP'); // Kolom U
            $table->string('exception', 20)->default('Tidak'); // Kolom V (Ya / Tidak)

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 4. KKA_Kredit (24 Kolom A s.d. X)
        Schema::create('risk_trigger_kka_kredits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_scope_id')->constrained('assignment_scopes')->onDelete('cascade');
            $table->string('kode_unit', 50)->index();
            $table->string('visit_id', 100)->index(); // Kolom A
            $table->string('trigger_id', 100)->index(); // Kolom B
            $table->string('unit_kantor', 200); // Kolom C
            $table->string('area', 100)->default('Kredit'); // Kolom D

            // Kolom Input (E s.d. J, L, N s.d. V)
            $table->string('no_kredit_debitur', 100)->nullable(); // Kolom E
            $table->string('nama_debitur', 150)->nullable(); // Kolom F
            $table->string('produk', 150)->nullable(); // Kolom G
            $table->decimal('plafond', 18, 2)->default(0); // Kolom H
            $table->decimal('outstanding', 18, 2)->default(0); // Kolom I
            $table->decimal('nilai_agunan', 18, 2)->default(0); // Kolom J
            $table->decimal('coverage_agunan', 8, 4)->nullable(); // Kolom K (J / I)
            $table->decimal('nilai_asuransi', 18, 2)->default(0); // Kolom L
            $table->decimal('coverage_asuransi', 8, 4)->nullable(); // Kolom M (L / I)
            $table->string('persetujuan_sesuai', 20)->default('Ya'); // Kolom N (Ya / Tidak)
            $table->string('pencairan_sesuai', 20)->default('Ya'); // Kolom O (Ya / Tidak)
            $table->string('dokumen_lengkap', 20)->default('Ya'); // Kolom P (Ya / Tidak)
            $table->text('covenant_deviasi')->nullable(); // Kolom Q
            $table->text('analisis_kredit')->nullable(); // Kolom R
            $table->text('sebab')->nullable(); // Kolom S
            $table->text('dampak')->nullable(); // Kolom T
            $table->text('referensi_bukti')->nullable(); // Kolom U
            $table->text('simpulan')->nullable(); // Kolom V

            // Output Rumus (W & X)
            $table->string('status_uji', 50)->default('BELUM LENGKAP'); // Kolom W
            $table->string('exception', 20)->default('Tidak'); // Kolom X (Ya / Tidak)

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 5. KKA_Biaya_Beban (24 Kolom A s.d. X)
        Schema::create('risk_trigger_kka_biaya_bebans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_scope_id')->constrained('assignment_scopes')->onDelete('cascade');
            $table->string('kode_unit', 50)->index();
            $table->string('visit_id', 100)->index(); // Kolom A
            $table->string('trigger_id', 100)->index(); // Kolom B
            $table->string('unit_kantor', 200); // Kolom C
            $table->string('area', 100)->default('Biaya Beban'); // Kolom D

            // Kolom Input (E s.d. K, M s.d. V)
            $table->string('gl_rekening', 100)->nullable(); // Kolom E
            $table->string('no_voucher', 100)->nullable(); // Kolom F
            $table->date('tanggal')->nullable(); // Kolom G
            $table->text('redaksi')->nullable(); // Kolom H
            $table->decimal('nominal_buku', 18, 2)->default(0); // Kolom I
            $table->decimal('nominal_invoice', 18, 2)->default(0); // Kolom J
            $table->decimal('nominal_disetujui', 18, 2)->default(0); // Kolom K
            $table->decimal('selisih', 18, 2)->nullable(); // Kolom L (I - K)
            $table->string('anggaran_tersedia', 20)->default('Ya'); // Kolom M (Ya / Tidak)
            $table->string('otorisasi_sesuai', 20)->default('Ya'); // Kolom N (Ya / Tidak)
            $table->string('pajak_sesuai', 20)->default('Ya'); // Kolom O (Ya / Tidak)
            $table->string('barang_jasa_diterima', 20)->default('Ya'); // Kolom P (Ya / Tidak)
            $table->string('transaksi_berulang', 20)->default('Tidak'); // Kolom Q (Ya / Tidak)
            $table->text('analisis_kewajaran')->nullable(); // Kolom R
            $table->text('sebab')->nullable(); // Kolom S
            $table->text('dampak')->nullable(); // Kolom T
            $table->text('referensi_bukti')->nullable(); // Kolom U
            $table->text('simpulan')->nullable(); // Kolom V

            // Output Rumus (W & X)
            $table->string('status_uji', 50)->default('BELUM LENGKAP'); // Kolom W
            $table->string('exception', 20)->default('Tidak'); // Kolom X (Ya / Tidak)

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 6. KKA_ATM (Kartu ATM & Rekon Kartu ATM - 23 Kolom A s.d. W)
        Schema::create('risk_trigger_kka_atms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_scope_id')->constrained('assignment_scopes')->onDelete('cascade');
            $table->string('kode_unit', 50)->index();
            $table->string('visit_id', 100)->index(); // Kolom A
            $table->string('trigger_id', 100)->index(); // Kolom B
            $table->string('unit_kantor', 200); // Kolom C
            $table->string('area', 100)->default('Kartu ATM'); // Kolom D

            // Kolom Input (E s.d. J, O s.d. U)
            $table->string('objek_jenis_kartu_atm', 150)->nullable(); // Kolom E
            $table->string('no_referensi', 100)->nullable(); // Kolom F
            $table->decimal('qty_buku', 12, 2)->default(0); // Kolom G
            $table->decimal('qty_fisik', 12, 2)->default(0); // Kolom H
            $table->decimal('qty_pin_mailer', 12, 2)->default(0); // Kolom I
            $table->decimal('harga_satuan', 18, 2)->default(0); // Kolom J
            $table->decimal('nilai_buku', 18, 2)->default(0); // Kolom K (G * J)
            $table->decimal('nilai_fisik', 18, 2)->default(0); // Kolom L (H * J)
            $table->decimal('selisih_qty', 12, 2)->default(0); // Kolom M (H - G)
            $table->decimal('selisih_nilai', 18, 2)->default(0); // Kolom N (L - K)
            $table->string('custody_sesuai', 20)->default('Ya'); // Kolom O (Ya / Tidak)
            $table->string('akses_log_sesuai', 20)->default('Ya'); // Kolom P (Ya / Tidak)
            $table->text('analisis_rekonsiliasi')->nullable(); // Kolom Q
            $table->text('sebab')->nullable(); // Kolom R
            $table->text('dampak')->nullable(); // Kolom S
            $table->text('referensi_bukti')->nullable(); // Kolom T
            $table->text('simpulan')->nullable(); // Kolom U

            // Output Rumus (V & W)
            $table->string('status_uji', 50)->default('BELUM LENGKAP'); // Kolom V
            $table->string('exception', 20)->default('Tidak'); // Kolom W (Ya / Tidak)

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 7. KKA_Pengaduan (21 Kolom A s.d. U)
        Schema::create('risk_trigger_kka_pengaduans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_scope_id')->constrained('assignment_scopes')->onDelete('cascade');
            $table->string('kode_unit', 50)->index();
            $table->string('visit_id', 100)->index(); // Kolom A
            $table->string('trigger_id', 100)->index(); // Kolom B
            $table->string('unit_kantor', 200); // Kolom C
            $table->string('area', 100)->default('Pengaduan'); // Kolom D

            // Kolom Input (E s.d. J, L s.d. S)
            $table->string('complaint_id', 100)->nullable(); // Kolom E
            $table->date('tanggal_terima')->nullable(); // Kolom F
            $table->string('kategori', 150)->nullable(); // Kolom G
            $table->text('uraian_pengaduan')->nullable(); // Kolom H
            $table->integer('sla_hari')->default(0); // Kolom I
            $table->date('tanggal_selesai')->nullable(); // Kolom J
            $table->integer('aging_hari')->default(0); // Kolom K (Rumus Otomatis)
            $table->string('status_penyelesaian', 50)->default('Dalam Proses'); // Kolom L (Selesai / Dalam Proses / Belum Ditangani)
            $table->string('bukti_penyelesaian', 20)->default('Tidak'); // Kolom M (Ya / Tidak)
            $table->string('berulang', 20)->default('Tidak'); // Kolom N (Ya / Tidak)
            $table->text('akar_masalah')->nullable(); // Kolom O
            $table->text('dampak_nasabah')->nullable(); // Kolom P
            $table->text('analisis_sla')->nullable(); // Kolom Q
            $table->text('referensi_bukti')->nullable(); // Kolom R
            $table->text('simpulan')->nullable(); // Kolom S

            // Output Rumus (T & U)
            $table->string('status_uji', 50)->default('BELUM LENGKAP'); // Kolom T
            $table->string('exception', 20)->default('Tidak'); // Kolom U (Ya / Tidak)

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 8. KKA_Fisik (Cash Opname, Rekon Cek BG, Rekon Barang Cetakan, & Aset - 22 Kolom A s.d. V)
        Schema::create('risk_trigger_kka_fisiks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_scope_id')->constrained('assignment_scopes')->onDelete('cascade');
            $table->string('kode_unit', 50)->index();
            $table->string('visit_id', 100)->index(); // Kolom A
            $table->string('trigger_id', 100)->index(); // Kolom B
            $table->string('unit_kantor', 200); // Kolom C
            $table->string('area', 100)->default('Cash Opname'); // Kolom D

            // Kolom Input (E s.d. J, O s.d. T)
            $table->string('jenis_objek', 150)->nullable(); // Kolom E
            $table->string('identitas_lokasi_seri', 150)->nullable(); // Kolom F
            $table->decimal('qty_sistem', 15, 2)->default(0); // Kolom G
            $table->decimal('qty_catatan', 15, 2)->default(0); // Kolom H
            $table->decimal('qty_fisik', 15, 2)->default(0); // Kolom I
            $table->decimal('harga_satuan', 18, 2)->default(0); // Kolom J
            $table->decimal('nilai_sistem', 18, 2)->default(0); // Kolom K (G * J)
            $table->decimal('nilai_fisik', 18, 2)->default(0); // Kolom L (I * J)
            $table->decimal('selisih_qty', 15, 2)->default(0); // Kolom M (I - G)
            $table->decimal('selisih_nilai', 18, 2)->default(0); // Kolom N (L - K)
            $table->string('kondisi_custody', 50)->default('Sesuai'); // Kolom O (Sesuai / Tidak Sesuai)
            $table->text('analisis_rekonsiliasi')->nullable(); // Kolom P
            $table->text('sebab')->nullable(); // Kolom Q
            $table->text('dampak')->nullable(); // Kolom R
            $table->text('referensi_bukti')->nullable(); // Kolom S
            $table->text('simpulan')->nullable(); // Kolom T

            // Output Rumus (U & V)
            $table->string('status_uji', 50)->default('BELUM LENGKAP'); // Kolom U
            $table->string('exception', 20)->default('Tidak'); // Kolom V (Ya / Tidak)

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 9. KKA_Observasi (18 Kolom A s.d. R)
        Schema::create('risk_trigger_kka_observasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_scope_id')->constrained('assignment_scopes')->onDelete('cascade');
            $table->string('kode_unit', 50)->index();
            $table->string('visit_id', 100)->index(); // Kolom A
            $table->string('trigger_id', 100)->index(); // Kolom B
            $table->string('unit_kantor', 200); // Kolom C
            $table->string('area', 100)->default('Observasi Lingkungan'); // Kolom D

            // Kolom Input (E s.d. P)
            $table->string('kategori', 150)->nullable(); // Kolom E
            $table->string('titik_kontrol_lokasi', 150)->nullable(); // Kolom F
            $table->text('kriteria_standar')->nullable(); // Kolom G
            $table->text('kondisi_hasil_observasi')->nullable(); // Kolom H
            $table->string('uji_fungsi_playback', 50)->default('Berfungsi'); // Kolom I (Berfungsi / Tidak Berfungsi / N/A)
            $table->string('akses_kebersihan_aman', 50)->default('Sesuai'); // Kolom J (Sesuai / Tidak Sesuai)
            $table->integer('rating_1_5')->default(3); // Kolom K (1 s.d. 5)
            $table->text('analisis_gap')->nullable(); // Kolom L
            $table->text('sebab')->nullable(); // Kolom M
            $table->text('dampak')->nullable(); // Kolom N
            $table->text('referensi_bukti_foto')->nullable(); // Kolom O
            $table->text('simpulan')->nullable(); // Kolom P

            // Output Rumus (Q & R)
            $table->string('status_uji', 50)->default('BELUM LENGKAP'); // Kolom Q
            $table->string('exception', 20)->default('Tidak'); // Kolom R (Ya / Tidak)

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_trigger_kka_observasis');
        Schema::dropIfExists('risk_trigger_kka_fisiks');
        Schema::dropIfExists('risk_trigger_kka_pengaduans');
        Schema::dropIfExists('risk_trigger_kka_atms');
        Schema::dropIfExists('risk_trigger_kka_biaya_bebans');
        Schema::dropIfExists('risk_trigger_kka_kredits');
        Schema::dropIfExists('risk_trigger_kka_dpk_layanans');
        Schema::dropIfExists('risk_trigger_kka_teller_kas');
        Schema::dropIfExists('risk_trigger_kka_induks');
    }
};
