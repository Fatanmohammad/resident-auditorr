<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temuan_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kka_id')->constrained('kertas_kerja_audits')->onDelete('cascade');
            $table->string('judul_temuan');
            $table->enum('kategori', ['signifikan', 'berulang', 'operasional', 'kepatuhan', 'lainnya']);
            $table->text('kondisi');
            $table->text('kriteria');
            $table->text('sebab');
            $table->text('akibat');
            $table->text('rekomendasi_ra');
            $table->date('target_selesai_tl')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temuan_audits');
    }
};