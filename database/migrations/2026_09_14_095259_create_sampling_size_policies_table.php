<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sampling_size_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('min_populasi');
            $table->unsignedInteger('max_populasi')->nullable(); // null = tidak terbatas (>250)
            $table->unsignedInteger('min_sampel')->nullable();   // null jika sample_all = true
            $table->boolean('sample_all')->default(false);       // true untuk band 1-10 (periksa semua)
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sampling_size_policies');
    }
};