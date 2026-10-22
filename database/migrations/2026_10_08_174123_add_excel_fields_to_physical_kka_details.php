<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Cash Details
        Schema::table('physical_kka_cash_details', function (Blueprint $table) {
            $table->string('status_uji')->default('BELUM DIUJI');
            $table->text('simpulan_ra')->nullable();
            $table->date('tanggal_uji')->nullable();
        });

        // 2. Obs Details
        Schema::table('physical_kka_obs_details', function (Blueprint $table) {
            $table->string('status_uji')->default('BELUM DIUJI');
            $table->text('simpulan_ra')->nullable();
            $table->date('tanggal_uji')->nullable();
            $table->text('kondisi_aktual')->nullable();
        });

        // 3. Stok Details
        Schema::table('physical_kka_stok_details', function (Blueprint $table) {
            $table->string('status_uji')->default('BELUM DIUJI');
            $table->text('simpulan_ra')->nullable();
            $table->date('tanggal_uji')->nullable();
            $table->text('kondisi_aktual')->nullable(); // Sesuai permintaan form input teks
        });
    }

    public function down()
    {
        Schema::table('physical_kka_cash_details', function (Blueprint $table) {
            $table->dropColumn(['status_uji', 'simpulan_ra', 'tanggal_uji']);
        });

        Schema::table('physical_kka_obs_details', function (Blueprint $table) {
            $table->dropColumn(['status_uji', 'simpulan_ra', 'tanggal_uji', 'kondisi_aktual']);
        });

        Schema::table('physical_kka_stok_details', function (Blueprint $table) {
            $table->dropColumn(['status_uji', 'simpulan_ra', 'tanggal_uji', 'kondisi_aktual']);
        });
    }
};
