<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('risk_trigger_master_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('area', 100)->unique()->index();
            $table->string('kka_tujuan', 100);
            $table->text('tujuan_uji');
            $table->text('kriteria_utama');
            $table->text('data_bukti_minimum');
            $table->text('metode_uji');
            $table->text('metode_sampling');
            $table->string('perlu_fisik_default', 20)->default('Ya');
            $table->text('keterangan_simpulan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_trigger_master_parameters');
    }
};
