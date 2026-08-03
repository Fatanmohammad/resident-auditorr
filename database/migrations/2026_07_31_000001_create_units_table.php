<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_code')->unique();
            $table->string('unit_name');
            $table->enum('unit_type', ['KC', 'KCU', 'KCP', 'KCPLK', 'KP', 'Payment Point']);
            $table->string('parent_office')->nullable();
            $table->string('region')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('base_ra_unit')->nullable(); // nama cabang → lookup ke branch_ra_mapping
            $table->decimal('distance_from_parent_km', 8, 2)->nullable();
            // computed fields (disimpan agar tidak hitung ulang tiap query)
            $table->enum('transaction_volume_category', ['Tinggi', 'Sedang', 'Rendah'])->nullable();
            $table->text('auto_description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
