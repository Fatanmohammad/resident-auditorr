<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onsite_populations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('onsite_visit_id');
            $table->string('kode_unit');

            // Kolom dari CBS (DUMP_01 format)
            $table->string('kd_tx')->nullable();
            $table->string('kd_cab')->nullable();
            $table->string('no_rek')->nullable();
            $table->string('no_arsip')->nullable();
            $table->text('ket_tx')->nullable();
            $table->string('db_kr', 1)->nullable(); // D atau K
            $table->string('txtype')->nullable();
            $table->string('kd_user')->nullable();
            $table->date('tgl_tx')->nullable();
            $table->string('time_stamp')->nullable();
            $table->decimal('jumlah_tx', 20, 2)->default(0);

            // Kunci populasi: TGL_TX + NO_ARSIP
            $table->string('populasi_key')->nullable(); // "{tgl_tx}|{no_arsip}"

            // Stable Score per user per hari (dihitung saat parsing)
            $table->integer('stable_score')->default(0);

            $table->timestamps();

            $table->foreign('onsite_visit_id')->references('id')->on('onsite_visits')->onDelete('cascade');
            $table->index(['onsite_visit_id', 'populasi_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onsite_populations');
    }
};
