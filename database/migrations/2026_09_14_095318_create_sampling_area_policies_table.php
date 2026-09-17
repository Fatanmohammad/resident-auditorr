<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sampling_area_policies', function (Blueprint $table) {
            $table->id();
            $table->string('source_sheet')->unique(); // Misal: KKA_Teller_Kas, KKA_Kredit
            $table->enum('metode_sampling', ['Random', 'Stratified', 'MUS', 'Targeted'])->default('Random');
            $table->string('stratify_by')->nullable(); // Misal: kode_unit (dipakai jika metode = Stratified)
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sampling_area_policies');
    }
};