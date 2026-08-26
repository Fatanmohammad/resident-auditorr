<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wp_offsite', function (Blueprint $table) {
            $table->unsignedBigInteger('ra_pelaksana_id')->nullable()->change();
            $table->unsignedBigInteger('reviewer_bagian_ra_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('wp_offsite', function (Blueprint $table) {
            $table->unsignedBigInteger('ra_pelaksana_id')->nullable(false)->change();
            $table->unsignedBigInteger('reviewer_bagian_ra_id')->nullable(false)->change();
        });
    }
};