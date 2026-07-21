<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kertas_hasil_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kka_id')->constrained('kertas_kerja_audits')->onDelete('cascade');
            $table->text('ringkasan_hasil');
            $table->enum('status_kha', ['draft', 'approved_kabag', 'approved_kadiv'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kertas_hasil_audits');
    }
};