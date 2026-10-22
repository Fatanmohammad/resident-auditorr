<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop05_kka_kartu_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surprise_audit_assignment_id')
                  ->constrained('surprise_audit_assignments')
                  ->onDelete('cascade');

            $table->string('lokasi_penyimpanan')->default('Brankas Khusus / Vault');
            $table->string('jenis_kartu'); // Platinum, Gold, Silver
            
            // Input
            $table->decimal('saldo_persediaan_buku', 15, 2)->default(0);
            $table->decimal('harga_per_kartu', 15, 2)->default(0);
            $table->integer('jumlah_fisik_kartu')->default(0);
            $table->integer('jumlah_pin_mailer')->default(0);

            // Output / Calculated
            $table->integer('qty_menurut_buku')->default(0);
            $table->decimal('nilai_fisik_kartu', 15, 2)->default(0);
            $table->integer('selisih_qty_buku_fisik')->default(0);
            $table->integer('selisih_pin_fisik')->default(0);
            $table->decimal('selisih_nilai', 15, 2)->default(0);
            $table->string('status_rekonsiliasi')->default('SESUAI'); // SESUAI / TIDAK SESUAI

            // Keterangan & Analisis
            $table->text('sebab')->nullable();
            $table->text('dampak')->nullable();
            $table->string('referensi_bukti')->nullable();
            $table->string('validasi')->default('LENGKAP');
            $table->string('exception_aktif')->default('Tidak'); // Ya / Tidak

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop05_kka_kartu_details');
    }
};