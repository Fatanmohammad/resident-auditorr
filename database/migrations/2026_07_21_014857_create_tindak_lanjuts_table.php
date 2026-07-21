<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tindak_lanjuts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('temuan_id')->constrained('temuan_audits')->onDelete('cascade');
            $table->foreignId('auditee_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('respon_auditee')->nullable();
            $table->string('bukti_lampiran_path')->nullable(); // Upload berkas bukti TL[cite: 1]
            
            // Monitoring Status Tindak Lanjut[cite: 1]
            $table->enum('status_tl', ['belum_tl', 'proses_tl', 'selesai', 'terlambat'])->default('belum_tl');
            $table->text('catatan_verifikasi_ra')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjuts');
    }
};