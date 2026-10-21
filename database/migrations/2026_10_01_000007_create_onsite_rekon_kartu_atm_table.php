<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onsite_rekon_kartu_atm', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('onsite_visit_id');
            $table->string('kode_unit');
            $table->string('ra_pelaksana')->nullable();
            $table->date('tanggal_opname')->nullable();
            $table->enum('status', ['Draft', 'Submitted', 'Approved'])->default('Draft');
            $table->timestamps();
            $table->foreign('onsite_visit_id')->references('id')->on('onsite_visits')->onDelete('cascade');
        });

        Schema::create('onsite_rekon_kartu_atm_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rekon_id');
            $table->string('procedure_id')->nullable();
            $table->enum('tipe', ['persediaan', 'kontrol'])->default('persediaan');
            $table->string('sub_area')->nullable();           // Persediaan Kartu ATM / Kontrol
            $table->string('jenis_kartu')->nullable();        // untuk baris persediaan
            $table->string('keterangan')->nullable();
            $table->string('saldo_nominatif')->nullable();    // N/A untuk kontrol
            $table->string('fisik_kondisi')->nullable();
            $table->string('selisih')->nullable();            // N/A untuk kontrol
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
            $table->integer('urutan')->default(0);
            $table->timestamps();
            $table->foreign('rekon_id')->references('id')->on('onsite_rekon_kartu_atm')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onsite_rekon_kartu_atm_items');
        Schema::dropIfExists('onsite_rekon_kartu_atm');
    }
};
