<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom cabang_id pada tabel units untuk menghubungkan
     * setiap unit ke cabang/hirarki cabang (mendukung pembatasan akses RA).
     */
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->foreignId('cabang_id')
                ->nullable()
                ->after('base_ra_unit')
                ->constrained('cabangs')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['cabang_id']);
            $table->dropColumn('cabang_id');
        });
    }
};
