<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('physical_kka_cash_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('physical_kka_id')->constrained('physical_kkas')->onDelete('cascade');
            $table->string('sub_area'); // Khasanah / ATM
            $table->string('objek_persediaan'); // Uang kertas layak edar, logam, dll.
            $table->string('pecahan');
            $table->decimal('saldo_sistem', 20, 2)->default(0);
            $table->decimal('hasil_fisik', 20, 2)->default(0);
            $table->decimal('selisih', 20, 2)->default(0);
            
            $table->boolean('is_exception')->default(false);
            $table->text('penyebab')->nullable();
            $table->string('bukti_path')->nullable();
            
            // Risk Matrix fields
            $table->integer('impact')->nullable();
            $table->integer('likelihood')->nullable();
            $table->boolean('critical_trigger')->default(false);
            $table->integer('risk_score')->nullable();
            $table->string('severity_level')->nullable(); // Rendah, Sedang, Tinggi, Kritis

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('physical_kka_cash_details');
    }
};
