<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onsite_cash_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onsite_visit_id')->constrained('onsite_visits')->cascadeOnDelete();
            $table->string('ra_pelaksana')->nullable();
            $table->string('pemegang_kas')->nullable();
            $table->date('tanggal_opname')->nullable();
            $table->decimal('saldo_gl', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onsite_cash_opnames');
    }
};