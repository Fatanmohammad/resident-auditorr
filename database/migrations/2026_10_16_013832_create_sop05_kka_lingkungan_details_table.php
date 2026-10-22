<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop05_kka_lingkungan_details', function (Blueprint $table) {
            $table->id();

            // Foreign Key pendek
            $table->unsignedBigInteger('surprise_audit_assignment_id');
            $table->foreign('surprise_audit_assignment_id', 'fk_ling_assignment')
                  ->references('id')
                  ->on('surprise_audit_assignments')
                  ->onDelete('cascade');

            // 11 Kolom Sesuai Header Excel Master KKA_Lingkungan
            $table->string('kategori'); // Kelayakan, Kebersihan, CCTV
            $table->string('area_perangkat');
            $table->text('kriteria_unik');
            $table->text('hasil_observasi_ra')->nullable();
            $table->string('hasil_uji_playback')->default('N.A.');
            $table->string('kesimpulan_uji')->default('Sesuai'); // Sesuai / Tidak Sesuai

            $table->text('sebab')->nullable();
            $table->text('dampak')->nullable();
            $table->string('referensi_bukti')->nullable();
            $table->string('validasi')->default('BELUM LENGKAP');
            $table->string('exception_aktif')->default('Tidak');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop05_kka_lingkungan_details');
    }
};