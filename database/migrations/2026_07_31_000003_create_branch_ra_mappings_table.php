<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_ra_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name')->unique();
            $table->foreignId('primary_ra_id')->constrained('ras')->onDelete('cascade');
            $table->foreignId('backup_ra_id')->nullable()->constrained('ras')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_ra_mappings');
    }
};
