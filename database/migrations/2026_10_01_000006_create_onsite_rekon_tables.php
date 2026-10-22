<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rekon Barang Cetakan — header
        Schema::create('onsite_rekon_barang_cetakan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('onsite_visit_id');
            $table->string('kode_unit');
            $table->string('ra_pelaksana')->nullable();
            $table->date('tanggal_opname')->nullable();
            $table->enum('status', ['Draft', 'Submitted', 'Approved'])->default('Draft');
            $table->timestamps();
            $table->foreign('onsite_visit_id')->references('id')->on('onsite_visits')->onDelete('cascade');
        });

        // Rekon Barang Cetakan — item
        Schema::create('onsite_rekon_barang_cetakan_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rekon_id');
            $table->string('procedure_id')->nullable();
            $table->string('kategori');
            $table->string('nama_barang');
            $table->string('satuan');
            $table->integer('stok_laporan')->nullable();
            $table->integer('hasil_fisik')->nullable();
            $table->integer('selisih')->virtualAs('COALESCE(hasil_fisik,0) - COALESCE(stok_laporan,0)')->nullable();
            $table->enum('kondisi', ['Baik', 'Rusak', 'Outdated', 'N/A'])->nullable();
            $table->text('kriteria_tidak_dipenuhi')->nullable();
            $table->text('penyebab')->nullable();
            $table->text('dampak')->nullable();
            $table->tinyInteger('impact')->nullable();
            $table->tinyInteger('likelihood')->nullable();
            $table->string('risk_level')->nullable();
            $table->text('referensi_bukti')->nullable();
            $table->text('simpulan_ra')->nullable();
            $table->date('tanggal_uji')->nullable();
            $table->enum('status_review', ['Belum Diuji', 'Sesuai', 'Tidak Sesuai', 'Tidak Berlaku'])->default('Belum Diuji');
            $table->text('catatan_reviewer')->nullable();
            $table->string('reviewer')->nullable();
            $table->timestamps();
            $table->foreign('rekon_id')->references('id')->on('onsite_rekon_barang_cetakan')->onDelete('cascade');
        });

        // Rekon Cek & BG — header
        Schema::create('onsite_rekon_cek_bg', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('onsite_visit_id');
            $table->string('kode_unit');
            $table->string('ra_pelaksana')->nullable();
            $table->date('tanggal_opname')->nullable();
            $table->enum('status', ['Draft', 'Submitted', 'Approved'])->default('Draft');
            $table->timestamps();
            $table->foreign('onsite_visit_id')->references('id')->on('onsite_visits')->onDelete('cascade');
        });

        // Rekon Cek & BG — item
        Schema::create('onsite_rekon_cek_bg_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rekon_id');
            $table->string('procedure_id')->nullable();
            $table->string('sub_area');
            $table->string('objek');
            $table->string('pecahan_seri')->nullable();
            $table->string('saldo_nominatif')->nullable();
            $table->string('hasil_fisik')->nullable();
            $table->string('selisih')->nullable();
            $table->text('hasil_kontrol')->nullable();
            $table->text('kriteria')->nullable();
            $table->text('sebab')->nullable();
            $table->text('dampak')->nullable();
            $table->tinyInteger('impact')->nullable();
            $table->tinyInteger('likelihood')->nullable();
            $table->string('risk_level')->nullable();
            $table->text('referensi_bukti')->nullable();
            $table->text('simpulan_ra')->nullable();
            $table->date('tanggal_uji')->nullable();
            $table->enum('status_review', ['Belum Diuji', 'Sesuai', 'Tidak Sesuai', 'Tidak Berlaku'])->default('Belum Diuji');
            $table->text('catatan_reviewer')->nullable();
            $table->string('reviewer')->nullable();
            $table->timestamps();
            $table->foreign('rekon_id')->references('id')->on('onsite_rekon_cek_bg')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onsite_rekon_cek_bg_items');
        Schema::dropIfExists('onsite_rekon_cek_bg');
        Schema::dropIfExists('onsite_rekon_barang_cetakan_items');
        Schema::dropIfExists('onsite_rekon_barang_cetakan');
    }
};
