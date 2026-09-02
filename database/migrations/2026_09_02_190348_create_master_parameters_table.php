<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('kategori'); // Misal: LIMIT_TARIK_TUNAI, SLA_PENGADUAN
            $table->decimal('nilai_batas', 20, 2); 
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_parameters');
    }
};
