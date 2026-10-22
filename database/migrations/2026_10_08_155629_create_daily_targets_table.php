<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('daily_targets', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_tugas');
            $table->string('kode_unit');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Carry Over tracking
            $table->integer('carry_over_count')->default(0);
            $table->date('extended_due_date')->nullable();
            $table->string('surat_sakit_path')->nullable();
            
            // Pending, PENDING_APPROVAL_PUSAT, In Progress, Final, LOCKED_UNFINISHED
            $table->string('status')->default('Pending'); 
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_targets');
    }
};
