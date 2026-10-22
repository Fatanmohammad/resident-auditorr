<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('physical_kkas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_unit');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal_pemeriksaan');
            $table->string('status_final')->default('Draft'); // Draft, Final
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('physical_kkas');
    }
};
