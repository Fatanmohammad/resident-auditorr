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
        Schema::table('kka_findings', function (Blueprint $table) {
            $table->text('deskripsi')->nullable()->after('jenis_exception_awal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kka_findings', function (Blueprint $table) {
            $table->dropColumn('deskripsi');
        });
    }
};