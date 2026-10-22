<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('register_klarifikasis', function (Blueprint $table) {
            $table->id();
            // Polymorphic relation to handle Cash, Obs, Stok, and potentially regular KKA
            $table->morphs('source'); 
            $table->string('kode_unit');
            
            $table->text('tanggapan_cabang')->nullable();
            $table->text('simpulan_ra')->nullable();
            
            $table->string('status')->default('Menunggu Klarifikasi'); // Menunggu Klarifikasi, Selesai (Clear), Escalated (Temuan Resmi)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('register_klarifikasis');
    }
};
