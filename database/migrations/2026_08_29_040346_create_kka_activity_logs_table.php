<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kka_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); 
            $table->string('user_name');
            $table->string('kode_unit'); 
            $table->string('case_id');
            $table->string('sheet_name'); 
            $table->string('action'); // 'UPLOAD', 'UPDATE', 'REVIEW'
            $table->text('deskripsi_perubahan'); 
            $table->enum('status_review', ['Belum', 'Selesai', 'Pending'])->default('Belum');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kka_activity_logs');
    }
};