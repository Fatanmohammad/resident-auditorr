<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('physical_kkas', function (Blueprint $table) {
            if (!Schema::hasColumn('physical_kkas', 'no_arsip')) {
                $table->string('no_arsip')->nullable()->after('tanggal_pemeriksaan');
            }
            if (!Schema::hasColumn('physical_kkas', 'posisi_tanggal_rekon')) {
                $table->date('posisi_tanggal_rekon')->nullable()->after('no_arsip');
            }
            if (!Schema::hasColumn('physical_kkas', 'saldo_kas_olibs')) {
                $table->decimal('saldo_kas_olibs', 20, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('physical_kkas', 'saldo_fisik_khasanah')) {
                $table->decimal('saldo_fisik_khasanah', 20, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('physical_kkas', 'selisih_kas_khasanah')) {
                $table->decimal('selisih_kas_khasanah', 20, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('physical_kkas', 'penjelasan_selisih_kas')) {
                $table->text('penjelasan_selisih_kas')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'kas_pelaksana_nama')) {
                $table->string('kas_pelaksana_nama')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'kas_pelaksana_jabatan')) {
                $table->string('kas_pelaksana_jabatan')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'kas_saksi_nama')) {
                $table->string('kas_saksi_nama')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'kas_saksi_jabatan')) {
                $table->string('kas_saksi_jabatan')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'rekon_kartu_penyebab_selisih')) {
                $table->text('rekon_kartu_penyebab_selisih')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'rekon_kartu_status_selisih')) {
                $table->string('rekon_kartu_status_selisih')->nullable();
            }
        });

        Schema::table('physical_kka_cash_details', function (Blueprint $table) {
            if (!Schema::hasColumn('physical_kka_cash_details', 'kelompok')) {
                $table->string('kelompok')->nullable()->after('sub_area');
            }
            if (!Schema::hasColumn('physical_kka_cash_details', 'jumlah_lembar')) {
                $table->integer('jumlah_lembar')->nullable()->default(0)->after('pecahan');
            }
            if (!Schema::hasColumn('physical_kka_cash_details', 'nominal')) {
                $table->decimal('nominal', 20, 2)->nullable()->default(0)->after('jumlah_lembar');
            }
        });

        Schema::table('physical_kka_kartu_atm_details', function (Blueprint $table) {
            if (!Schema::hasColumn('physical_kka_kartu_atm_details', 'harga_modal')) {
                $table->decimal('harga_modal', 15, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('physical_kka_kartu_atm_details', 'qty_fisik_kosong')) {
                $table->integer('qty_fisik_kosong')->nullable()->default(0);
            }
            if (!Schema::hasColumn('physical_kka_kartu_atm_details', 'qty_fisik_rusak')) {
                $table->integer('qty_fisik_rusak')->nullable()->default(0);
            }
            if (!Schema::hasColumn('physical_kka_kartu_atm_details', 'nominal_fisik')) {
                $table->decimal('nominal_fisik', 20, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('physical_kka_kartu_atm_details', 'saldo_buku_qty')) {
                $table->integer('saldo_buku_qty')->nullable()->default(0);
            }
            if (!Schema::hasColumn('physical_kka_kartu_atm_details', 'saldo_buku_nominal')) {
                $table->decimal('saldo_buku_nominal', 20, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('physical_kka_kartu_atm_details', 'selisih_persediaan')) {
                $table->decimal('selisih_persediaan', 20, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('physical_kka_kartu_atm_details', 'pin_mailer_qty')) {
                $table->integer('pin_mailer_qty')->nullable()->default(0);
            }
            if (!Schema::hasColumn('physical_kka_kartu_atm_details', 'selisih_pin_mailer')) {
                $table->integer('selisih_pin_mailer')->nullable()->default(0);
            }
            if (!Schema::hasColumn('physical_kka_kartu_atm_details', 'jawaban_kontrol')) {
                $table->string('jawaban_kontrol')->nullable();
            }
            if (!Schema::hasColumn('physical_kka_kartu_atm_details', 'catatan_kontrol')) {
                $table->text('catatan_kontrol')->nullable();
            }
        });

        Schema::table('physical_kka_barang_cetakan_details', function (Blueprint $table) {
            if (!Schema::hasColumn('physical_kka_barang_cetakan_details', 'jawaban_kontrol')) {
                $table->string('jawaban_kontrol')->nullable();
            }
            if (!Schema::hasColumn('physical_kka_barang_cetakan_details', 'catatan_kontrol')) {
                $table->text('catatan_kontrol')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('physical_kkas', function (Blueprint $table) {
            $table->dropColumn([
                'no_arsip',
                'posisi_tanggal_rekon',
                'saldo_kas_olibs',
                'saldo_fisik_khasanah',
                'selisih_kas_khasanah',
                'penjelasan_selisih_kas',
                'kas_pelaksana_nama',
                'kas_pelaksana_jabatan',
                'kas_saksi_nama',
                'kas_saksi_jabatan',
                'rekon_kartu_penyebab_selisih',
                'rekon_kartu_status_selisih',
            ]);
        });

        Schema::table('physical_kka_cash_details', function (Blueprint $table) {
            $table->dropColumn(['kelompok', 'jumlah_lembar', 'nominal']);
        });

        Schema::table('physical_kka_kartu_atm_details', function (Blueprint $table) {
            $table->dropColumn([
                'harga_modal',
                'qty_fisik_kosong',
                'qty_fisik_rusak',
                'nominal_fisik',
                'saldo_buku_qty',
                'saldo_buku_nominal',
                'selisih_persediaan',
                'pin_mailer_qty',
                'selisih_pin_mailer',
                'jawaban_kontrol',
                'catatan_kontrol',
            ]);
        });

        Schema::table('physical_kka_barang_cetakan_details', function (Blueprint $table) {
            $table->dropColumn(['jawaban_kontrol', 'catatan_kontrol']);
        });
    }
};
