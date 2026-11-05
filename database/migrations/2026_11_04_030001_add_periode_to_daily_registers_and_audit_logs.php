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
        Schema::table('daily_registers', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_registers', 'periode')) {
                $table->date('periode')->nullable()->after('tanggal_data');
                $table->index(['kode_unit', 'periode']);
            }
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'periode')) {
                $table->date('periode')->nullable()->after('kode_unit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_registers', function (Blueprint $table) {
            if (Schema::hasColumn('daily_registers', 'periode')) {
                $table->dropIndex(['kode_unit', 'periode']);
                $table->dropColumn('periode');
            }
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'periode')) {
                $table->dropColumn('periode');
            }
        });
    }
};

