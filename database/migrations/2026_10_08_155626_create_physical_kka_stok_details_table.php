<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('physical_kka_stok_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('physical_kka_id')->constrained('physical_kkas')->onDelete('cascade');
            $table->string('sub_area'); // Cek, BG, Buku Tabungan, Kartu ATM
            $table->string('kondisi_objek'); // Belum digunakan, Rusak, dll.
            $table->integer('saldo_sistem')->default(0);
            $table->integer('hasil_fisik')->default(0);
            $table->integer('selisih')->default(0);
            
            $table->boolean('is_exception')->default(false);
            $table->text('penyebab')->nullable();
            $table->string('bukti_path')->nullable();
            
            // Risk Matrix fields
            $table->integer('impact')->nullable();
            $table->integer('likelihood')->nullable();
            $table->boolean('critical_trigger')->default(false);
            $table->integer('risk_score')->nullable();
            $table->string('severity_level')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('physical_kka_stok_details');
    }
};
