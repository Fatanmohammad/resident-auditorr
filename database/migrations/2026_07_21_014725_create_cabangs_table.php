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
        Schema::create('cabangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_cabang');
            $table->string('kode_cabang')->unique();
            $table->enum('tipe', ['pusat', 'kcu', 'cabang_pembantu', 'siting', 'cabang_a', 'cabang_b', 'anak_cabang']);
            // Parent ID untuk struktur hirarki cabang & anak cabang
            $table->foreignId('parent_id')->nullable()->constrained('cabangs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabangs');
    }
};