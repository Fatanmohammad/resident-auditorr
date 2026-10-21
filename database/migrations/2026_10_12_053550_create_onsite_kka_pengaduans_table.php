<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onsite_kka_pengaduans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onsite_visit_id')->constrained('onsite_visits')->onDelete('cascade');
            $table->foreignId('onsite_population_id')->nullable()->constrained('onsite_populations')->onDelete('cascade');
            
            // Identitas Sampel Pengaduan
            $table->string('sample_id')->unique();
            $table->date('tgl_pengaduan');
            $table->string('no_tiket');
            $table->string('nama_nasabah')->nullable();
            $table->string('kategori_pengaduan')->nullable();
            $table->decimal('nominal_sengketa', 18, 2)->default(0);
            $table->string('jenis_sampling'); // Mandatory, Certainty, Targeted, Initial
            $table->integer('prioritas')->default(5);
            $table->text('alasan_sampling')->nullable();

            // Atribut Pengujian 10 Pengaduan Nasabah (SOP 04.1)
            $table->string('attr_pencatatan_register')->default('Sesuai');
            $table->string('attr_pemenuhan_sla')->default('Sesuai');
            $table->string('attr_validitas_investigasi')->default('Sesuai');
            $table->string('attr_otorisasi_pemberian_ganti_rugi')->default('Sesuai');
            $table->string('attr_penyampaian_tanggapan_nasabah')->default('Sesuai');
            $table->string('attr_pelaporan_ojk_laps')->default('Sesuai');
            $table->string('attr_kesesuaian_posting_ganti_rugi')->default('Sesuai');
            $table->string('attr_kerahasiaan_data_nasabah')->default('Sesuai');
            $table->string('attr_tindak_lanjut_perbaikan')->default('Sesuai');
            $table->string('attr_kelengkapan_berkas')->default('Sesuai');

            // Exception & Risiko
            $table->string('status_uji')->default('Belum Diuji');
            $table->string('jenis_exception')->nullable();
            $table->text('kondisi')->nullable();
            $table->text('kriteria')->nullable();
            $table->text('sebab')->nullable();
            $table->text('dampak')->nullable();
            $table->integer('skor_dampak')->default(1);
            $table->integer('skor_kemungkinan')->default(1);
            $table->integer('skor_risiko')->default(1);
            $table->string('risk_level')->default('Low');
            $table->string('critical_trigger')->default('Tidak');

            // Klarifikasi & Review
            $table->string('perlu_klarifikasi')->default('Ya');
            $table->string('status_klarifikasi')->default('Draft');
            $table->text('simpulan_ra')->nullable();
            $table->text('catatan_reviewer')->nullable();
            $table->string('status_review')->default('Belum Direview');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onsite_kka_pengaduans');
    }
};