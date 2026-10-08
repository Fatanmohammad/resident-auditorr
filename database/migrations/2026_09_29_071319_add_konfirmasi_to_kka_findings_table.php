<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kka_findings', function (Blueprint $table) {
            $table->enum('status_konfirmasi', ['Menunggu', 'Sesuai', 'Tidak Sesuai'])->default('Menunggu')->after('status');
            $table->text('komitmen_penyelesaian')->nullable()->after('status_konfirmasi');
            $table->date('target_penyelesaian')->nullable()->after('komitmen_penyelesaian');
            $table->timestamp('tanggal_konfirmasi')->nullable()->after('target_penyelesaian');
        });
    }

    public function down(): void
    {
        Schema::table('kka_findings', function (Blueprint $table) {
            $table->dropColumn([
                'status_konfirmasi',
                'komitmen_penyelesaian',
                'target_penyelesaian',
                'tanggal_konfirmasi',
            ]);
        });
    }
};