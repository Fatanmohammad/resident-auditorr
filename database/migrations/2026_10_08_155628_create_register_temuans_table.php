<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('register_temuans', function (Blueprint $table) {
            $table->id();
            // Relation to the klarifikasi that was escalated
            $table->foreignId('register_klarifikasi_id')->constrained('register_klarifikasis')->onDelete('cascade');
            $table->string('kode_unit');
            
            $table->string('status_review_pusat')->default('Pending'); // Pending, Approved, Rejected
            $table->text('catatan_pusat')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('register_temuans');
    }
};
