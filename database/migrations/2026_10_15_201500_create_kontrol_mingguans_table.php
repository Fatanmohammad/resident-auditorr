<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kontrol_mingguans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_unit');
            $table->integer('tahun');
            $table->integer('minggu_ke');
            $table->date('start_date'); // Tanggal Senin
            $table->date('end_date');   // Tanggal Jumat
            $table->string('area_key'); // rekon_kartu_atm, cash_opname, observasi_lingkungan, rekon_stok_cek_bg, rekon_barang_cetakan
            $table->string('sheet_kka');
            $table->string('nama_area');
            $table->integer('hari_rencana')->default(1); // 1 = Senin, 5 = Jumat
            $table->integer('menit_rencana')->default(60);
            $table->date('override_tanggal')->nullable();
            $table->date('tanggal_rencana_efektif');
            $table->integer('jumlah_auditor')->default(1);
            $table->string('status_minggu_ini')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['kode_unit', 'start_date', 'area_key'], 'uniq_unit_week_area');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrol_mingguans');
    }
};
