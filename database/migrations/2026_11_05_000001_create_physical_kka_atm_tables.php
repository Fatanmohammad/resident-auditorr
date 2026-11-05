<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('physical_kka_atm_machines')) {
            Schema::create('physical_kka_atm_machines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('physical_kka_id')->constrained('physical_kkas')->onDelete('cascade');
                $table->string('terminal_id');
                $table->string('lokasi')->nullable();
                $table->string('merk')->nullable();
                $table->decimal('denominasi', 15, 2)->nullable()->default(100000);
                $table->date('tanggal_pengisian_terakhir')->nullable();
                $table->decimal('saldo_cbs', 20, 2)->default(0);
                $table->decimal('total_fisik', 20, 2)->default(0);
                $table->decimal('selisih', 20, 2)->default(0);
                $table->string('status_uji')->default('Belum Diuji');
                $table->text('keterangan_selisih')->nullable();
                $table->string('pelaksana_nama')->nullable();
                $table->string('pelaksana_jabatan')->nullable();
                $table->string('saksi_nama')->nullable();
                $table->string('saksi_jabatan')->nullable();
                $table->boolean('is_exception')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('physical_kka_atm_cassettes')) {
            Schema::create('physical_kka_atm_cassettes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('atm_machine_id')->constrained('physical_kka_atm_machines')->onDelete('cascade');
                $table->string('nama_kaset');
                $table->decimal('denominasi', 15, 2)->nullable()->default(0);
                $table->integer('jumlah_lembar')->nullable()->default(0);
                $table->decimal('total_nominal', 20, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_kka_atm_cassettes');
        Schema::dropIfExists('physical_kka_atm_machines');
    }
};
