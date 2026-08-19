<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. WP Offsite - identitas WP aktif per unit per periode
        Schema::create('wp_offsite', function (Blueprint $table) {
            $table->id();
            $table->string('kode_wp')->unique(); // Format: SOP02-{kode_unit}-{YYYYMM}
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->string('kode_unit');
            $table->string('nama_unit');
            $table->string('jenis_unit')->nullable();
            $table->string('kantor_induk')->nullable();
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->foreignId('ra_pelaksana_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewer_bagian_ra_id')->constrained('users')->onDelete('cascade');
            $table->enum('status_wp', ['Draft', 'Aktif', 'Final'])->default('Draft');
            $table->string('scope_wp')->default('1 UNIT / 1 PERIODE');
            $table->string('validasi_unit')->default('VALID'); // VALID / INVALID
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Rule Engine - Rule deteksi yang bisa dikonfigurasi
        Schema::create('rule_engine', function (Blueprint $table) {
            $table->id();
            $table->string('rule_id')->unique(); // RISK_REV_01, CLS_TLR_01, WL_001, dst
            $table->enum('rule_type', ['Risk Trigger', 'Classification', 'Whitelist']);
            $table->text('keyword_pattern'); // Teks yang dicari (case-insensitive)
            $table->string('area_terkait')->nullable(); // Opsional: area review terkait
            $table->text('description')->nullable(); // Deskripsi rule
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // 3. Rule Threshold - Parameter numerik (nominal threshold, dll)
        Schema::create('rule_threshold', function (Blueprint $table) {
            $table->id();
            $table->string('parameter_name'); // Misal: tunai_besar_threshold, ambang_high_skor, dst
            $table->string('jenis_unit')->nullable(); // KC, KCP, KCPLK, dst — NULL = berlaku semua
            $table->decimal('numeric_value', 15, 2)->nullable();
            $table->string('string_value')->nullable();
            $table->text('description')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // 4. Sampling Strata - Parameter sampling untuk Low Risk
        Schema::create('sampling_strata', function (Blueprint $table) {
            $table->id();
            $table->string('domain'); // CBS, DPK, Kredit, Biaya, Pengaduan
            $table->string('strata_name'); // Misal: Rekening Baru, Perubahan Data
            $table->integer('target_case'); // Jumlah sampel yang harus diambil per strata per periode
            $table->text('description')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->unique(['domain', 'strata_name']);
        });

        // 5. DUMP_TRANSAKSI_CBS
        Schema::create('dump_transaksi_cbs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wp_offsite_id')->constrained('wp_offsite')->onDelete('cascade');
            $table->string('kode_unit');
            $table->date('tanggal_data');
            $table->string('no_referensi')->nullable();
            $table->string('kode_transaksi')->nullable();
            $table->string('nama_transaksi')->nullable();
            $table->string('user_maker')->nullable();
            $table->string('nama_user')->nullable();
            $table->decimal('nominal', 15, 2)->nullable();
            $table->char('d_k', 1)->nullable(); // D atau K
            $table->text('deskripsi_narasi')->nullable();
            $table->string('data_source')->default('CBS');
            
            // Flag deteksi otomatis
            $table->boolean('flag_reversal')->default(false);
            $table->boolean('flag_koreksi_override')->default(false);
            $table->boolean('flag_selisih_kas')->default(false);
            $table->boolean('flag_tunai_besar')->default(false);
            $table->boolean('flag_biaya_jurnal')->default(false);
            $table->boolean('flag_internal_account')->default(false);
            $table->boolean('flag_pencairan_kredit')->default(false);
            $table->boolean('flag_rutin_whitelist')->default(false);
            
            // Hasil deteksi
            $table->enum('risk_level', ['High', 'Moderate', 'Low'])->nullable();
            $table->string('area_review')->nullable(); // Teller/Kas, Kredit, Biaya, Transfer, Transaksi Umum
            $table->string('kka_sheet_tujuan')->nullable(); // KKA area mana atau "Register"
            $table->string('case_id')->nullable(); // Untuk pairing transaksi
            $table->integer('jumlah_flag_risiko')->default(0);
            $table->enum('status_data_quality', ['VALID', 'Salah Unit', 'Luar Periode'])->default('VALID');
            $table->text('catatan_rule')->nullable();
            
            $table->timestamps();
            $table->index(['wp_offsite_id', 'tanggal_data']);
            $table->index(['kode_unit', 'tanggal_data']);
        });

        // 6. DUMP_DPK_APUPPT
        Schema::create('dump_dpk_apuppt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wp_offsite_id')->constrained('wp_offsite')->onDelete('cascade');
            $table->string('kode_unit')->nullable();
            $table->date('tanggal_data')->nullable(); // Input manual untuk file Nominatif
            $table->string('produk')->nullable();
            $table->string('cif_nasabah')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('nama_nasabah')->nullable();
            $table->string('gol_pemilik')->nullable();
            $table->date('tanggal_buka')->nullable();
            $table->date('jatuh_tempo')->nullable();
            $table->decimal('saldo_akhir', 15, 2)->nullable();
            $table->string('status_rekening')->nullable();
            $table->string('data_source')->default('DPK');
            
            // Deteksi fields
            $table->enum('risk_level', ['High', 'Moderate', 'Low'])->nullable();
            $table->string('area_review')->nullable();
            $table->string('kka_sheet_tujuan')->nullable();
            $table->enum('status_data_quality', ['VALID', 'Salah Unit', 'Luar Periode'])->default('VALID');
            
            $table->timestamps();
            $table->index(['wp_offsite_id', 'tanggal_data']);
        });

        // 7. DUMP_KREDIT
        Schema::create('dump_kredit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wp_offsite_id')->constrained('wp_offsite')->onDelete('cascade');
            $table->string('kode_unit')->nullable();
            $table->date('tanggal_data')->nullable(); // Input manual untuk file Nominatif
            $table->string('cif_nasabah')->nullable();
            $table->string('no_rekening_kredit')->nullable();
            $table->string('no_nasabah')->nullable();
            $table->string('no_akad')->nullable();
            $table->string('nama_debitur')->nullable();
            $table->string('produk_kredit')->nullable();
            $table->string('jenis_kredit')->nullable();
            $table->date('tanggal_realisasi')->nullable();
            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->integer('jangka_waktu_bulan')->nullable();
            $table->decimal('plafon', 15, 2)->nullable();
            $table->decimal('baki_debet', 15, 2)->nullable();
            $table->string('kolektibilitas')->nullable();
            $table->decimal('tunggakan_pokok', 15, 2)->nullable();
            $table->decimal('tunggakan_bunga', 15, 2)->nullable();
            $table->string('ao_pengelola')->nullable();
            $table->decimal('total_agunan', 15, 2)->nullable();
            $table->string('data_source')->default('Kredit');
            
            // Deteksi fields
            $table->enum('risk_level', ['High', 'Moderate', 'Low'])->nullable();
            $table->string('area_review')->nullable();
            $table->string('kka_sheet_tujuan')->nullable();
            $table->enum('status_data_quality', ['VALID', 'Salah Unit', 'Luar Periode'])->default('VALID');
            
            $table->timestamps();
            $table->index(['wp_offsite_id', 'tanggal_data']);
        });

        // 8. DUMP_BIAYA_BEBAN
        Schema::create('dump_biaya_beban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wp_offsite_id')->constrained('wp_offsite')->onDelete('cascade');
            $table->string('kode_unit');
            $table->date('tanggal_data');
            $table->string('no_rekening')->nullable();
            $table->string('no_arsip')->nullable();
            $table->string('kode_transaksi')->nullable();
            $table->text('keterangan_transaksi')->nullable();
            $table->char('d_k', 1)->nullable(); // D atau K
            $table->decimal('nominal', 15, 2)->nullable();
            $table->string('user_input')->nullable();
            $table->datetime('time_stamp')->nullable();
            $table->boolean('auto_system_flag')->default(false);
            $table->string('data_source')->default('Biaya/Beban');
            
            // Deteksi fields
            $table->enum('risk_level', ['High', 'Moderate', 'Low'])->nullable();
            $table->string('area_review')->nullable();
            $table->string('kka_sheet_tujuan')->nullable();
            $table->enum('status_data_quality', ['VALID', 'Salah Unit', 'Luar Periode'])->default('VALID');
            
            $table->timestamps();
            $table->index(['wp_offsite_id', 'tanggal_data']);
        });

        // 9. DUMP_PENGADUAN
        Schema::create('dump_pengaduan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wp_offsite_id')->constrained('wp_offsite')->onDelete('cascade');
            $table->string('kode_unit')->nullable();
            $table->date('tanggal_data')->nullable();
            $table->string('no_tiket')->nullable();
            $table->date('tanggal_pengaduan')->nullable();
            $table->string('jenis_pengaduan')->nullable();
            $table->text('isi_pengaduan')->nullable();
            $table->string('status_pengaduan')->nullable();
            $table->string('data_source')->default('Pengaduan');
            
            // Deteksi fields
            $table->enum('risk_level', ['High', 'Moderate', 'Low'])->nullable();
            $table->string('area_review')->nullable();
            $table->string('kka_sheet_tujuan')->nullable();
            $table->enum('status_data_quality', ['VALID', 'Salah Unit', 'Luar Periode'])->default('VALID');
            
            $table->timestamps();
            $table->index(['wp_offsite_id', 'tanggal_data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dump_pengaduan');
        Schema::dropIfExists('dump_biaya_beban');
        Schema::dropIfExists('dump_kredit');
        Schema::dropIfExists('dump_dpk_apuppt');
        Schema::dropIfExists('dump_transaksi_cbs');
        Schema::dropIfExists('sampling_strata');
        Schema::dropIfExists('rule_threshold');
        Schema::dropIfExists('rule_engine');
        Schema::dropIfExists('wp_offsite');
    }
};
