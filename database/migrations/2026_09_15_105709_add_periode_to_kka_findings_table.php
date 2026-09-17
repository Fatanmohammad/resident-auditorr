<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kka_findings', function (Blueprint $table) {
            $table->date('periode')->nullable()->after('tanggal_data');
            $table->index(['kode_unit', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::table('kka_findings', function (Blueprint $table) {
            $table->dropIndex(['kode_unit', 'periode']);
            $table->dropColumn('periode');
        });
    }
};