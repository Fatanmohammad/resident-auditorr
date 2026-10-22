<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onsite_cash_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onsite_cash_opname_id')->constrained('onsite_cash_opnames')->cascadeOnDelete();
            $table->string('procedure_id')->nullable();
            $table->string('sub_area')->nullable();
            $table->string('objek_lokasi')->nullable();
            $table->string('pecahan_seri')->nullable();
            
            // Kolom input hasil fisik / kaset ATM
            $table->decimal('hasil_fisik', 15, 2)->nullable();
            $table->string('kaset_1_pecahan')->nullable();
            $table->integer('kaset_1_lembar')->nullable();
            $table->string('kaset_2_pecahan')->nullable();
            $table->integer('kaset_2_lembar')->nullable();
            $table->string('kaset_3_pecahan')->nullable();
            $table->integer('kaset_3_lembar')->nullable();
            $table->string('kaset_4_pecahan')->nullable();
            $table->integer('kaset_4_lembar')->nullable();
            
            $table->string('status_uji')->default('Sesuai'); // Sesuai / Selisih
            $table->text('kondisi')->nullable();
            $table->text('simpulan_ra')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onsite_cash_opname_items');
    }
};