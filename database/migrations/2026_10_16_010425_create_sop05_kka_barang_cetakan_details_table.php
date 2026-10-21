<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop05_kka_barang_cetakan_details', function (Blueprint $table) {
            $table->id();

            // Foreign Key ke Assignment Penugasan dengan nama pendek agar aman dari error MySQL
            $table->unsignedBigInteger('surprise_audit_assignment_id');
            $table->foreign('surprise_audit_assignment_id', 'fk_bct_assignment')
                  ->references('id')
                  ->on('surprise_audit_assignments')
                  ->onDelete('cascade');

            $table->string('jenis_barang_cetakan'); // Kolom A: Jenis Barang Cetakan / Satuan
            $table->integer('catatan_petugas')->default(0); // Kolom B: Catatan Petugas
            $table->integer('hasil_fisik')->default(0); // Kolom C: Hasil Fisik
            $table->integer('selisih')->default(0); // Kolom D: Selisih (Fisik - Catatan)
            $table->string('kondisi_penyimpanan')->default('Aman/Terkunci'); // Kolom E: Aman/Terkunci atau Tidak Sesuai

            $table->string('status_uji')->default('BELUM LENGKAP'); // Kolom F: SESUAI / EXCEPTION / BELUM LENGKAP
            $table->text('sebab')->nullable(); // Kolom G
            $table->text('dampak')->nullable(); // Kolom H
            $table->string('referensi_bukti')->nullable(); // Kolom I
            $table->string('validasi')->default('BELUM LENGKAP'); // Kolom J
            $table->string('exception_aktif')->default('Tidak'); // Kolom K: Ya / Tidak

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop05_kka_barang_cetakan_details');
    }
};