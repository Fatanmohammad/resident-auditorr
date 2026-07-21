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
        Schema::create('audit_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->onDelete('cascade');
            $table->foreignId('ra_user_id')->constrained('users')->onDelete('cascade');
            $table->integer('tahun_periode');
            $table->date('jadwal_mulai');
            $table->date('jadwal_selesai');
            
            // Approval Berjenjang: RA -> Kabag RA -> Kadiv SKAI
            $table->enum('status_approval', [
                'draft', 
                'waiting_kabag_approval', 
                'waiting_kadiv_approval', 
                'approved', 
                'rejected'
            ])->default('draft');
            
            $table->text('catatan_revisi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_plans');
    }
};