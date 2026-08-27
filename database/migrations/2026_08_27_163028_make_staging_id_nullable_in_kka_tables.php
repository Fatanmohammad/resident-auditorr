<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kka_transfer_ku', function (Blueprint $table) {
            $table->unsignedBigInteger('staging_id')->nullable()->change();
        });

        Schema::table('kka_transaksi_umum', function (Blueprint $table) {
            $table->unsignedBigInteger('staging_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('kka_transfer_ku', function (Blueprint $table) {
            $table->unsignedBigInteger('staging_id')->nullable(false)->change();
        });

        Schema::table('kka_transaksi_umum', function (Blueprint $table) {
            $table->unsignedBigInteger('staging_id')->nullable(false)->change();
        });
    }
};