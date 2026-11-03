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
            if (!Schema::hasColumn('daily_registers', 'risk_awal')) {
                $table->string('risk_awal', 20)->default('Low')->nullable()->after('kategori');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_registers', function (Blueprint $table) {
            if (Schema::hasColumn('daily_registers', 'risk_awal')) {
                $table->dropColumn('risk_awal');
            }
        });
    }
};

