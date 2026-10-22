<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop05_kka_aset_details', function (Blueprint $table) {
            $table->id();

            // Foreign Key pendek
            $table->unsignedBigInteger('surprise_audit_assignment_id');
            $table->foreign('surprise_audit_assignment_id', 'fk_ast_assignment')
                  ->references('id')
                  ->on('surprise_audit_assignments')
                  ->onDelete('cascade');

            // 15 Kolom Sesuai Header Excel Master KKA_Aset
            $table->string('id_tag_aset')->nullable();
            $table->string('jenis_nama_aset')->nullable();
            $table->string('lokasi_register')->nullable();
            $table->string('lokasi_fisik')->nullable();
            $table->integer('qty_register')->default(1);
            $table->integer('qty_fisik')->default(1);
            $table->integer('selisih_qty')->default(0);
            $table->string('kondisi_fisik')->default('Baik'); // Baik, Rusak Ringan, Rusak Berat
            $table->string('label_inventaris')->default('Ya'); // Ya / Tidak

            $table->string('status_uji')->default('BELUM LENGKAP');
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
        Schema::dropIfExists('sop05_kka_aset_details');
    }
};