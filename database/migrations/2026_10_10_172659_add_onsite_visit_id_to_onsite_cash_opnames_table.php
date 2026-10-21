<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onsite_cash_opnames', function (Blueprint $table) {
            if (!Schema::hasColumn('onsite_cash_opnames', 'onsite_visit_id')) {
                $table->foreignId('onsite_visit_id')
                      ->after('id')
                      ->constrained('onsite_visits')
                      ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('onsite_cash_opnames', function (Blueprint $table) {
            if (Schema::hasColumn('onsite_cash_opnames', 'onsite_visit_id')) {
                $table->dropForeign(['onsite_visit_id']);
                $table->dropColumn('onsite_visit_id');
            }
        });
    }
};