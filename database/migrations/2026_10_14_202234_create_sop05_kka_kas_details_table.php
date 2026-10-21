<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop05_kka_kas_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surprise_audit_assignment_id')
                  ->constrained('surprise_audit_assignments')
                  ->onDelete('cascade');

            $table->string('objek_pemeriksaan'); // Kas Khasanah, Kas Teller 01, dll.
            $table->decimal('nilai_sistem', 15, 2)->default(0);

            // Uang Layak Edar (ULE) - Kertas (Jumlah Lembar)
            $table->integer('ule_100k')->default(0);
            $table->integer('ule_50k')->default(0);
            $table->integer('ule_20k')->default(0);
            $table->integer('ule_10k')->default(0);
            $table->integer('ule_5k')->default(0);
            $table->integer('ule_2k')->default(0);
            $table->integer('ule_1k')->default(0);

            // Uang Tidak Layak Edar (UTLE) - Kertas (Jumlah Lembar)
            $table->integer('utle_100k')->default(0);
            $table->integer('utle_50k')->default(0);
            $table->integer('utle_20k')->default(0);
            $table->integer('utle_10k')->default(0);
            $table->integer('utle_5k')->default(0);
            $table->integer('utle_2k')->default(0);
            $table->integer('utle_1k')->default(0);

            // Logam (Jumlah Keping)
            $table->integer('logam_1000')->default(0);
            $table->integer('logam_500')->default(0);
            $table->integer('logam_200')->default(0);
            $table->integer('logam_100')->default(0);

            // Total Nominal Hasil Kalkulasi Subtotal
            $table->decimal('total_ule', 15, 2)->default(0);
            $table->decimal('total_utle', 15, 2)->default(0);
            $table->decimal('total_logam', 15, 2)->default(0);

            // Total Hasil Fisik & Selisih
            $table->decimal('nilai_fisik', 15, 2)->default(0); // total_ule + total_utle + total_logam
            $table->decimal('selisih', 15, 2)->default(0); // nilai_fisik - nilai_sistem

            $table->string('status_uji')->default('SIAP DIREVIU');
            $table->text('catatan_pemeriksa')->nullable();
            $table->string('exception_aktif')->default('Tidak');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop05_kka_kas_details');
    }
};