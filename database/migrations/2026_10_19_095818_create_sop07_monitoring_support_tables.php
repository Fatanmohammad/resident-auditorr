<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Parameter SOP 07
        Schema::create('sop07_master_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('parameter')->unique();
            $table->text('nilai')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 2. Log Reminder & Bukti (WP-RA-07-D)
        Schema::create('sop07_log_reminder_buktis', function (Blueprint $table) {
            $table->id();
            $table->string('log_id')->unique();
            $table->string('tl_id');
            $table->string('unit_kantor')->nullable();
            $table->string('pic_unit')->nullable();
            $table->date('target_berlaku')->nullable();
            $table->date('tanggal_komunikasi')->nullable();
            $table->string('jenis_komunikasi')->nullable();
            $table->string('saluran')->nullable(); // Ditambahkan untuk menyamakan dengan controller
            $table->string('timing')->nullable();
            $table->string('media')->nullable();
            $table->string('penerima')->nullable();
            $table->text('permintaan_klarifikasi')->nullable();
            $table->text('respons_auditee')->nullable();
            $table->string('status_auditee')->nullable();
            $table->string('bukti_komunikasi')->nullable();
            $table->string('bukti_diterima')->default('TIDAK')->nullable(); // Ditambahkan untuk controller storeLogBukti
            $table->string('referensi_bukti')->nullable(); // Ditambahkan untuk controller storeLogBukti
            $table->text('ringkasan_komunikasi')->nullable(); // Ditambahkan untuk controller storeLogBukti
            $table->string('status_log')->default('SUBMITTED')->nullable(); // Ditambahkan untuk controller storeLogBukti
            $table->string('follow_up_berikut')->nullable();
            $table->string('ketepatan')->nullable();
            $table->string('ra_pemantau')->nullable();
            $table->string('reviewer')->nullable();
            $table->timestamps();
        });

        // 3. KKA Verifikasi Bukti (WP-RA-07-E)
        Schema::create('sop07_kka_verifikasis', function (Blueprint $table) {
            $table->id();
            $table->string('kka_id')->unique()->nullable();
            $table->string('no_kka')->nullable(); // Ditambahkan untuk controller storeKkaVerifikasi
            $table->string('tl_id');
            $table->date('tanggal_bukti_diterima')->nullable();
            $table->date('tanggal_verifikasi')->nullable();
            $table->string('referensi_bukti')->nullable();
            $table->text('ringkasan_bukti')->nullable();
            $table->string('unit_kantor')->nullable();
            $table->text('rekomendasi_komitmen')->nullable();
            $table->text('bukti_diharapkan')->nullable();
            $table->string('closure_authority')->nullable();
            $table->string('relevan')->nullable();
            $table->string('lengkap')->nullable();
            $table->string('bertanggal_bersumber')->nullable();
            $table->string('korektif_realisasi')->nullable();
            $table->string('dapat_diuji')->nullable();
            $table->integer('populasi_terdampak')->nullable();
            $table->integer('objek_diuji')->nullable();
            $table->string('coverage')->nullable(); // Diubah jadi string untuk menampung '100.0%' dari controller
            $table->string('efektif_setelah_koreksi')->nullable();
            $table->string('efektif')->nullable(); // Ditambahkan untuk controller storeKkaVerifikasi
            $table->text('exception')->nullable();
            $table->text('analisis_ra')->nullable();
            $table->string('hasil_verifikasi')->nullable();
            $table->string('bukti_tambahan_diperlukan')->nullable();
            $table->string('bukti_tambahan')->nullable(); // Ditambahkan untuk controller storeKkaVerifikasi
            $table->string('perlu_verifikasi_lapangan')->nullable();
            $table->string('perlu_lapangan')->nullable(); // Ditambahkan untuk controller storeKkaVerifikasi
            $table->string('usulan_status')->nullable();
            $table->string('ra_pemantau')->nullable();
            $table->string('reviewer')->nullable();
            $table->string('status_review')->nullable();
            $table->timestamps();
        });

        // 4. Closure Review (WP-RA-07-F)
        Schema::create('sop07_closure_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('closure_review_id')->unique();
            $table->string('tl_id');
            $table->string('unit_kantor')->nullable();
            $table->string('sumber_tl')->nullable();
            $table->string('closure_authority')->nullable();
            $table->string('hasil_verifikasi_terakhir')->nullable();
            $table->string('usulan_status')->nullable();
            $table->date('tanggal_pengajuan')->nullable();
            $table->string('referensi_paket_bukti')->nullable();
            $table->string('keputusan_authority')->nullable();
            $table->date('tanggal_keputusan')->nullable();
            $table->string('pejabat_authority')->nullable();
            $table->string('referensi_keputusan')->nullable();
            $table->text('alasan_catatan')->nullable();
            $table->string('status_efektif')->nullable();
            $table->timestamps();
        });

        // 5. Perpanjangan, Eskalasi & Reopening (WP-RA-07-G)
        Schema::create('sop07_perpanjangan_eskalasis', function (Blueprint $table) {
            $table->id();
            $table->string('action_id')->unique();
            $table->string('tl_id');
            $table->string('unit_kantor')->nullable();
            $table->string('risk_level')->nullable();
            $table->date('target_saat_aksi')->nullable();
            $table->string('jenis_aksi')->nullable();
            $table->date('tanggal_aksi_permintaan')->nullable();
            $table->text('alasan')->nullable();
            $table->text('progres')->nullable();
            $table->text('interim_control')->nullable();
            $table->date('target_baru_diminta')->nullable();
            $table->string('level_persetujuan')->nullable();
            $table->string('keputusan')->nullable();
            $table->date('tanggal_keputusan')->nullable();
            $table->date('target_baru_disetujui')->nullable();
            $table->string('level_eskalasi')->nullable();
            $table->string('tujuan_eskalasi')->nullable();
            $table->text('arahan_respons')->nullable();
            $table->string('referensi_bukti')->nullable();
            $table->string('ra_pemantau')->nullable();
            $table->string('reviewer')->nullable();
            $table->string('status_aksi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop07_perpanjangan_eskalasis');
        Schema::dropIfExists('sop07_closure_reviews');
        Schema::dropIfExists('sop07_kka_verifikasis');
        Schema::dropIfExists('sop07_log_reminder_buktis');
        Schema::dropIfExists('sop07_master_parameters');
    }
};