<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staging_offsite', function (Blueprint $table) {
            $table->json('flags')->nullable()->after('status_data_quality');
            $table->integer('jumlah_flag_risiko')->default(0)->after('flags');
            $table->boolean('perlu_kka')->default(false)->after('jumlah_flag_risiko');
            $table->boolean('perlu_klarifikasi')->default(false)->after('perlu_kka');
            $table->boolean('perlu_eskalasi')->default(false)->after('perlu_klarifikasi');
            $table->timestamp('processed_at')->nullable()->after('perlu_eskalasi');
        });
    }

    public function down(): void
    {
        Schema::table('staging_offsite', function (Blueprint $table) {
            $table->dropColumn([
                'flags',
                'jumlah_flag_risiko',
                'perlu_kka',
                'perlu_klarifikasi',
                'perlu_eskalasi',
                'processed_at',
            ]);
        });
    }
};