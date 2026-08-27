<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kka_transfer_ku', function (Blueprint $table) {
            $table->string('status_review', 50)->default('Belum')->change();
        });

        Schema::table('kka_transaksi_umum', function (Blueprint $table) {
            $table->string('status_review', 50)->default('Belum')->change();
        });
    }

    public function down(): void
    {
        //
    }
};