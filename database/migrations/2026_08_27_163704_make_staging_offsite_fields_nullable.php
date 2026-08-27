<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staging_offsite', function (Blueprint $table) {
            // Cek apakah kolom status_review ada sebelum diubah agar tidak throw Error 1054
            if (Schema::hasColumn('staging_offsite', 'status_review')) {
                $table->string('status_review')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('staging_offsite', function (Blueprint $table) {
            if (Schema::hasColumn('staging_offsite', 'status_review')) {
                $table->string('status_review')->nullable(false)->change();
            }
        });
    }
};