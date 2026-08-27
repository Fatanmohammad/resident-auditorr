<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbs_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_data');
            $table->string('data_unit');
            $table->string('no_referensi');
            $table->string('user_maker');
            $table->string('kode_transaksi');
            $table->decimal('nominal', 15, 2);
            $table->text('deskripsi_narasi');
            $table->boolean('is_processed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbs_transactions');
    }
};