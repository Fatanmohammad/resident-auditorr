<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tmp_cbs_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('no_referensi')->nullable();
            $table->date('tanggal_data');
            $table->string('kode_unit')->nullable();
            $table->string('user_id')->nullable();
            $table->string('kode_transaksi')->nullable();
            $table->string('deskripsi_narasi')->nullable();
            $table->decimal('nominal', 18, 2)->default(0);
            $table->string('data_unit')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tmp_cbs_transactions');
    }
};