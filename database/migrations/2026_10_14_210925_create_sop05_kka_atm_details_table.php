<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop05_kka_atm_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surprise_audit_assignment_id')
                  ->constrained('surprise_audit_assignments')
                  ->onDelete('cascade');

            $table->string('area_kka')->default('atm');
            $table->string('objek_lokasi_atm'); // Objek/Lokasi ATM (misal: DBL LWK)
            $table->decimal('saldo_buku', 15, 2)->default(0); // Saldo Buku/Expected

            // Kaset 1
            $table->decimal('kaset1_pecahan', 15, 2)->default(100000);
            $table->integer('kaset1_lembar')->default(0);
            $table->decimal('kaset1_nilai', 15, 2)->default(0); // Pecahan * Lembar

            // Kaset 2
            $table->decimal('kaset2_pecahan', 15, 2)->default(50000);
            $table->integer('kaset2_lembar')->default(0);
            $table->decimal('kaset2_nilai', 15, 2)->default(0);

            // Kaset 3
            $table->decimal('kaset3_pecahan', 15, 2)->default(100000);
            $table->integer('kaset3_lembar')->default(0);
            $table->decimal('kaset3_nilai', 15, 2)->default(0);

            // Kaset 4
            $table->decimal('kaset4_pecahan', 15, 2)->default(50000);
            $table->integer('kaset4_lembar')->default(0);
            $table->decimal('kaset4_nilai', 15, 2)->default(0);

            // Total & Analysis Fields
            $table->decimal('hasil_fisik_total', 15, 2)->default(0); // Kaset 1+2+3+4
            $table->decimal('selisih', 15, 2)->default(0); // Hasil Fisik - Saldo Buku
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
        Schema::dropIfExists('sop05_kka_atm_details');
    }
};