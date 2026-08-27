<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parameter_audits', function (Blueprint $table) {
            $table->id();
            $table->string('nama_parameter'); // Misal: Risk Profile, Kinerja, dll.
            $table->decimal('bobot', 5, 2); // Bobot persentase
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parameter_audits');
    }
};