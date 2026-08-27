<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ras', function (Blueprint $table) {
            $table->id();
            $table->string('ra_id')->unique(); // contoh: AMP-1, LWK-1
            $table->string('ra_name');
            $table->string('base_branch')->nullable(); // kantor cabang tempat RA menetap
            $table->enum('status', ['Aktif', 'Non-aktif'])->default('Aktif');
            $table->integer('monthly_capacity_days')->default(20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ras');
    }
};
