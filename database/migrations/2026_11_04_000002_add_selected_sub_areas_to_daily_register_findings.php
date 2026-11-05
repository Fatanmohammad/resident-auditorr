<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_register_findings', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_register_findings', 'selected_sub_areas')) {
                $table->json('selected_sub_areas')->nullable()->after('status_keluhan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_register_findings', function (Blueprint $table) {
            if (Schema::hasColumn('daily_register_findings', 'selected_sub_areas')) {
                $table->dropColumn('selected_sub_areas');
            }
        });
    }
};
