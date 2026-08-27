<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wp_offsite_stagings', function (Blueprint $table) {
            $table->string('status_review')->default('Pending')->after('raw_data');
            $table->text('catatan_ra')->nullable()->after('status_review');
        });
    }

    public function down(): void
    {
        Schema::table('wp_offsite_stagings', function (Blueprint $table) {
            $table->dropColumn(['status_review', 'catatan_ra']);
        });
    }
};