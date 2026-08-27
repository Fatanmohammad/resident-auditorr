<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // RA Assignments — computed dari lookup berantai
        Schema::create('ra_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('primary_ra_id')->nullable()->constrained('ras')->onDelete('set null');
            $table->foreignId('backup_ra_id')->nullable()->constrained('ras')->onDelete('set null');
            $table->string('resident_base')->nullable();
            $table->enum('assignment_status', ['Aktif', 'Nonaktif'])->default('Aktif');
            $table->integer('valid_from'); // tahun
            $table->integer('valid_to');   // tahun
            $table->text('notes')->nullable(); // "Perlu Mapping RA" jika tidak ketemu
            $table->timestamps();
            $table->unique(['unit_id', 'valid_from']);
        });

        // Coverage Setup — input manual per 8 area fungsi per unit
        Schema::create('coverage_setups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->integer('period');
            // 8 area: Ya / Tidak / Event
            $table->enum('teller_kas',      ['Ya', 'Tidak', 'Event'])->default('Tidak');
            $table->enum('cs_dpk',          ['Ya', 'Tidak', 'Event'])->default('Tidak');
            $table->enum('kredit',          ['Ya', 'Tidak', 'Event'])->default('Event');
            $table->enum('atm',             ['Ya', 'Tidak', 'Event'])->default('Event');
            $table->enum('biaya_jurnal',    ['Ya', 'Tidak', 'Event'])->default('Tidak');
            $table->enum('apu_fds',         ['Ya', 'Tidak', 'Event'])->default('Tidak');
            $table->enum('ti_event',        ['Ya', 'Tidak', 'Event'])->default('Tidak');
            $table->enum('pengaduan_aset',  ['Ya', 'Tidak', 'Event'])->default('Tidak');
            $table->timestamps();
            $table->unique(['unit_id', 'period']);
        });

        // Coverage Summary — computed dari coverage_setup
        Schema::create('coverage_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->integer('period');
            // status per area (H+1 / Event-based / Tidak)
            $table->string('status_teller_kas')->nullable();
            $table->string('status_cs_dpk')->nullable();
            $table->string('status_kredit')->nullable();
            $table->string('status_atm')->nullable();
            $table->string('status_biaya_jurnal')->nullable();
            $table->string('status_apu_fds')->nullable();
            $table->string('status_ti_event')->nullable();
            $table->string('status_pengaduan_aset')->nullable();
            $table->integer('active_area_count')->default(0);
            $table->decimal('coverage_score', 4, 3)->default(0); // 0.000 - 1.000
            $table->enum('coverage_status', ['Lengkap', 'Cukup', 'Perlu Lengkapi Setup', 'Nonaktif'])->default('Perlu Lengkapi Setup');
            $table->timestamps();
            $table->unique(['unit_id', 'period']);
        });

        // Coverage Detail — computed, 1 baris per unit × per data_code
        Schema::create('coverage_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('data_code_id')->constrained('data_codes')->onDelete('cascade');
            $table->integer('period');
            $table->enum('final_coverage_mode', ['H+1', 'Event-based', 'Onsite-Periodik', 'Tidak'])->default('Tidak');
            $table->boolean('enters_sop02')->default(false); // masuk Daily Offsite
            $table->boolean('enters_sop04')->default(false); // masuk Onsite Terjadwal
            $table->timestamps();
            $table->unique(['unit_id', 'data_code_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coverage_details');
        Schema::dropIfExists('coverage_summaries');
        Schema::dropIfExists('coverage_setups');
        Schema::dropIfExists('ra_assignments');
    }
};
