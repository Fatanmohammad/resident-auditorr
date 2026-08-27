<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wp_offsite_stagings', function (Blueprint $table) {
            $table->foreignId('wp_offsite_id')->nullable()->after('cabang_id')->constrained('wp_offsite')->onDelete('cascade');
            $table->json('flags')->nullable();
            $table->unsignedInteger('jumlah_flag_risiko')->nullable();
            $table->string('area_review')->nullable();
            $table->string('risk_level')->nullable();
            $table->string('case_id')->nullable();
            $table->string('kka_sheet_tujuan')->nullable();
            $table->boolean('perlu_kka')->default(false);
            $table->boolean('perlu_klarifikasi')->default(false);
            $table->boolean('perlu_eskalasi')->default(false);
            $table->string('status_data_quality')->nullable();
            $table->timestamp('processed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('wp_offsite_stagings', function (Blueprint $table) {
            $table->dropForeign(['wp_offsite_id']);
            $table->dropColumn(['wp_offsite_id','flags','jumlah_flag_risiko','area_review','risk_level','case_id','kka_sheet_tujuan','perlu_kka','perlu_klarifikasi','perlu_eskalasi','status_data_quality','processed_at']);
        });
    }
};