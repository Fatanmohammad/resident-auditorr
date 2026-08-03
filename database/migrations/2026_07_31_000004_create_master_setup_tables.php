<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bobot 26 indikator dalam bidang
        Schema::create('field_weights', function (Blueprint $table) {
            $table->id();
            $table->string('metric_key')->unique();
            $table->enum('bidang', ['riwayat_ra', 'kas_teller', 'cs_dpk', 'kredit', 'ti_atm', 'monitoring_tl']);
            $table->decimal('weight', 5, 4); // 0.0000 - 1.0000
            $table->string('label');
            $table->timestamps();
        });

        // Bobot 6 bidang ke skor final per jenis unit
        Schema::create('bidang_weights', function (Blueprint $table) {
            $table->id();
            $table->enum('unit_type', ['KC', 'KCU', 'KCP', 'KCPLK', 'Payment Point']);
            $table->enum('bidang', ['riwayat_ra', 'kas_teller', 'cs_dpk', 'kredit', 'ti_atm', 'monitoring_tl']);
            $table->decimal('weight', 5, 4); // 0.00 - 1.00
            $table->timestamps();
        });

        // 19 data code untuk coverage
        Schema::create('data_codes', function (Blueprint $table) {
            $table->id();
            $table->string('data_code')->unique();
            $table->string('area');
            $table->enum('daily_offsite_capable', ['Ya', 'Tidak']);
            $table->enum('default_frequency', ['H+1', 'Event-based', 'Onsite']);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Matriks frekuensi onsite (5 kategori × 5 jenis unit)
        Schema::create('frequency_matrix', function (Blueprint $table) {
            $table->id();
            $table->enum('risk_category', ['High', 'Moderate to High', 'Moderate', 'Low to Moderate', 'Low']);
            $table->enum('unit_type', ['KC', 'KCU', 'KCP', 'KCPLK', 'Payment Point']);
            $table->string('frequency_label');
            $table->integer('visits_per_year')->default(0);
            $table->boolean('is_resident_daily_review')->default(false);
            $table->timestamps();
        });

        // Parameter kalender & kapasitas
        Schema::create('calendar_params', function (Blueprint $table) {
            $table->id();
            $table->string('param_key')->unique();
            $table->string('param_value');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_params');
        Schema::dropIfExists('frequency_matrix');
        Schema::dropIfExists('data_codes');
        Schema::dropIfExists('bidang_weights');
        Schema::dropIfExists('field_weights');
    }
};
