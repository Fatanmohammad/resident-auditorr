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
        Schema::table('daily_register_findings', function (Blueprint $table) {
            $table->string('status_klarifikasi', 100)->nullable()->change();
            $table->string('perluasan_sampel', 50)->nullable()->change();
            $table->string('keputusan_onsite', 100)->nullable()->change();
            $table->string('keputusan_eskalasi', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_register_findings', function (Blueprint $table) {
            //
        });
    }
};
