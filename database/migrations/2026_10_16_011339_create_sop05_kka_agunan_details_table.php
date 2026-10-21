<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop05_kka_agunan_details', function (Blueprint $table) {
            $table->id();

            // Foreign key pendek agar aman dari batasan panjang nama di MySQL
            $table->unsignedBigInteger('surprise_audit_assignment_id');
            $table->foreign('surprise_audit_assignment_id', 'fk_agunan_assignment')
                  ->references('id')
                  ->on('surprise_audit_assignments')
                  ->onDelete('cascade');

            // 19 Kolom Sesuai Header Excel Master KKA_Agunan
            $table->string('cif_debitur')->nullable();
            $table->string('no_kredit')->nullable();
            $table->string('jenis_agunan')->nullable();
            $table->string('dokumen_menurut_register')->nullable();
            $table->string('bentuk_disyaratkan')->default('Asli');
            $table->string('dokumen_ditemukan')->default('Ya');
            $table->string('bentuk_ditemukan')->default('Asli');
            $table->string('identitas_sesuai')->default('Ya');
            $table->string('nomor_dokumen')->nullable();
            $table->date('tanggal_kedaluwarsa')->nullable();
            $table->integer('hari_ke_kedaluwarsa')->nullable();
            $table->string('akses_custodian')->default('Sesuai');
            $table->string('register_mutakhir')->default('Ya');
            $table->string('metode_pemilihan')->default('SOP 02 - System Driven');

            $table->text('sebab')->nullable();
            $table->text('dampak')->nullable();
            $table->string('referensi_bukti')->nullable();
            $table->string('status_uji')->default('BELUM LENGKAP');
            $table->string('exception_aktif')->default('Tidak');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop05_kka_agunan_details');
    }
};