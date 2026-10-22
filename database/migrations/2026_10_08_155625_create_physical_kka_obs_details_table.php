<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('physical_kka_obs_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('physical_kka_id')->constrained('physical_kkas')->onDelete('cascade');
            $table->string('area_observasi'); 
            $table->text('kriteria_diharapkan');
            $table->string('hasil_observasi'); // Sesuai, Tidak Sesuai, Tidak Berlaku
            
            $table->boolean('is_exception')->default(false);
            $table->text('penyebab')->nullable(); // Uraian Ketidaksesuaian
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
        Schema::dropIfExists('physical_kka_obs_details');
    }
};
