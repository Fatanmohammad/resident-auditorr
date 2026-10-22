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
        Schema::table('physical_kkas', function (Blueprint $table) {
            $table->string('surat_izin_path')->nullable();
            $table->string('status_surat')->default('Pending');
            $table->text('catatan_surat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('physical_kkas', function (Blueprint $table) {
            $table->dropColumn(['surat_izin_path', 'status_surat', 'catatan_surat']);
        });
    }
};
