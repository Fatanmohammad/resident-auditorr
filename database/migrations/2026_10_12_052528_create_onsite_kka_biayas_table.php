<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onsite_kka_biayas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onsite_visit_id')->constrained('onsite_visits')->onDelete('cascade');
            $table->foreignId('onsite_population_id')->nullable()->constrained('onsite_populations')->onDelete('cascade');
            
            // Identitas Sampel Biaya
            $table->string('sample_id')->unique();
            $table->date('tgl_tx');
            $table->string('no_voucher');
            $table->string('keterangan_biaya')->nullable();
            $table->decimal('nominal', 18, 2)->default(0);
            $table->string('jenis_sampling'); // Mandatory, Certainty, Targeted, Initial
            $table->integer('prioritas')->default(5);
            $table->text('alasan_sampling')->nullable();

            // Atribut Pengujian 10 Biaya & Beban (SOP 04.1)
            $table->string('attr_ketersediaan_anggaran')->default('Sesuai');
            $table->string('attr_otorisasi_pengeluaran')->default('Sesuai');
            $table->string('attr_kelengkapan_dok_pendukung')->default('Sesuai');
            $table->string('attr_kepatuhan_pajak_pph')->default('Sesuai');
            $table->string('attr_validitas_perhitungan')->default('Sesuai');
            $table->string('attr_kewajaran_transaksi')->default('Sesuai');
            $table->string('attr_pemisahan_periode_akuntansi')->default('Sesuai');
            $table->string('attr_kesesuaian_cbs_gl')->default('Sesuai');
            $table->string('attr_pencatatan_akurat')->default('Sesuai');
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
        Schema::dropIfExists('onsite_kka_biayas');
    }
};
