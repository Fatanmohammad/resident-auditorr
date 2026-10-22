<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Table Rekon Kartu ATM
        Schema::create('physical_kka_kartu_atm_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('physical_kka_id')->constrained('physical_kkas')->onDelete('cascade');
            $table->string('procedure_id')->nullable();
            $table->string('sub_area')->nullable(); // Persediaan Kartu ATM / Kontrol
            $table->string('jenis_kartu')->nullable(); // Jenis Kartu / Batch / Seri
            $table->text('keterangan_lokasi')->nullable();
            $table->string('saldo_nominatif')->nullable(); // String to allow 'N/A' or numeric
            $table->text('hasil_fisik')->nullable(); // String/text to allow number or control notes
            $table->string('selisih')->nullable(); // String to allow 'N/A' or numeric
            $table->string('status_uji')->default('BELUM DIUJI'); // SESUAI, EXCEPTION, TIDAK BERLAKU
            $table->string('kondisi')->nullable(); // Baik, Rusak, Outdated, Tidak Baik, Tidak Berlaku, Sesuai, Tidak Sesuai
            
            // Risk & Findings
            $table->text('kriteria')->nullable();
            $table->text('penyebab')->nullable();
            $table->text('dampak')->nullable();
            $table->integer('impact')->nullable();
            $table->integer('likelihood')->nullable();
            $table->string('critical_trigger')->default('Tidak');
            $table->integer('risk_score')->nullable();
            $table->string('severity_level')->nullable();
            $table->string('perlu_klarifikasi')->default('Tidak');
            $table->string('bukti_path')->nullable();
            $table->text('simpulan_ra')->nullable();
            $table->date('tanggal_uji')->nullable();
            $table->string('ra_pelaksana')->nullable();

            // Reviewer Admin
            $table->string('reviewer_id')->nullable();
            $table->string('status_review')->default('Belum Direviu');
            $table->text('validasi')->nullable();
            $table->string('siap_register')->default('Tidak');
            $table->boolean('is_exception')->default(false);

            $table->timestamps();
        });

        // 2. Table Rekon Barang Cetakan
        Schema::create('physical_kka_barang_cetakan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('physical_kka_id')->constrained('physical_kkas')->onDelete('cascade');
            $table->string('procedure_id')->nullable();
            $table->string('kategori')->nullable(); // Formulir, Buku Tabungan, Kertas, Amplop, Perlengkapan, Prioritas
            $table->string('nama_barang')->nullable();
            $table->string('satuan')->nullable(); // Bh, Rim, Pack, Dos
            $table->decimal('stok_sistem', 15, 2)->default(0);
            $table->decimal('hasil_fisik', 15, 2)->default(0);
            $table->decimal('selisih', 15, 2)->default(0);
            $table->string('status_uji')->default('BELUM DIUJI'); // SESUAI, EXCEPTION, TIDAK BERLAKU
            $table->string('kondisi')->nullable(); // Baik, Outdated, Rusak, Tidak Baik, Tidak Berlaku

            // Risk & Findings
            $table->text('kriteria')->nullable();
            $table->text('penyebab')->nullable();
            $table->text('dampak')->nullable();
            $table->integer('impact')->nullable();
            $table->integer('likelihood')->nullable();
            $table->string('critical_trigger')->default('Tidak');
            $table->integer('risk_score')->nullable();
            $table->string('severity_level')->nullable();
            $table->string('perlu_klarifikasi')->default('Tidak');
            $table->string('bukti_path')->nullable();
            $table->text('simpulan_ra')->nullable();
            $table->date('tanggal_uji')->nullable();
            $table->string('ra_pelaksana')->nullable();

            // Reviewer Admin
            $table->string('reviewer_id')->nullable();
            $table->string('status_review')->default('Belum Direviu');
            $table->text('validasi')->nullable();
            $table->string('siap_register')->default('Tidak');
            $table->boolean('is_exception')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('physical_kka_kartu_atm_details');
        Schema::dropIfExists('physical_kka_barang_cetakan_details');
    }
};
