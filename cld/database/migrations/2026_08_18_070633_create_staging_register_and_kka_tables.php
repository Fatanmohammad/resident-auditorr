<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. STAGING_OFFSITE - Unified staging table
        Schema::create('staging_offsite', function (Blueprint $table) {
            $table->id(); // Use 'id' as primary key (default)
            $table->foreignId('wp_offsite_id')->constrained('wp_offsite')->onDelete('cascade');
            $table->date('tanggal_data');
            $table->string('kode_unit');
            $table->string('nama_unit')->nullable();
            $table->string('jenis_unit')->nullable();
            $table->foreignId('ra_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_ra')->nullable();
            $table->string('source_table'); // dump_transaksi_cbs, dump_dpk_apuppt, dst
            $table->unsignedBigInteger('source_record_id')->nullable(); // Reference ke DUMP asli
            $table->string('object_id')->nullable(); // No Rekening / No Referensi
            $table->string('case_id')->nullable(); // Untuk pairing transaksi
            $table->string('data_code')->nullable();
            $table->string('area_review')->nullable(); // Teller/Kas, Kredit, Biaya/Internal, Transaksi Umum, Transfer/KU, Pengaduan
            $table->text('deskripsi_narasi')->nullable();
            $table->decimal('nominal', 15, 2)->nullable();
            $table->string('user_maker')->nullable();
            
            // Deteksi results
            $table->enum('risk_level', ['High', 'Moderate', 'Low'])->nullable();
            $table->boolean('exception_awal')->default(false);
            $table->string('jenis_exception_awal')->nullable();
            $table->string('kka_sheet_tujuan')->nullable();
            
            // Sampling decision
            $table->boolean('sampel_low')->default(false); // Apakah dipilih sebagai sampel (Low risk only)
            $table->boolean('masuk_kka_final')->default(true); // Apakah final masuk KKA (High/Moderate=selalu Ya, Low=conditional)
            $table->string('alasan_tidak_masuk_kka')->nullable(); // Jika sampel_low=false, alasan
            
            // Quality status
            $table->enum('status_data_quality', ['VALID', 'Salah Unit', 'Luar Periode'])->default('VALID');
            $table->text('catatan_validasi')->nullable();
            
            $table->timestamps();
            $table->index(['wp_offsite_id', 'tanggal_data', 'area_review']);
            $table->index(['kode_unit', 'tanggal_data']);
            $table->index(['risk_level', 'sampel_low']);
        });

        // 2. REGISTER_HARIAN - Checklist harian per tanggal × area
        Schema::create('register_harian', function (Blueprint $table) {
            $table->id('offsite_id');
            $table->foreignId('wp_offsite_id')->constrained('wp_offsite')->onDelete('cascade');
            $table->date('tanggal_data');
            $table->date('target_review_h1'); // tanggal_data + 1
            $table->string('kode_unit');
            $table->string('nama_unit')->nullable();
            $table->foreignId('ra_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_ra')->nullable();
            $table->string('area_review'); // Teller/Kas, Kredit, Biaya/Beban, Biaya/Internal, Pengaduan, Transaksi Umum, Transfer/KU
            
            // Read-only aggregates dari staging_offsite
            $table->integer('populasi_eligible')->default(0);
            $table->integer('sampel_low')->default(0);
            $table->integer('kka_final')->default(0);
            $table->integer('exception')->default(0);
            $table->integer('perlu_klarifikasi')->default(0);
            $table->integer('perlu_eskalasi')->default(0);
            $table->string('risiko_tertinggi')->nullable(); // High/Moderate/Low
            $table->string('hasil_awal')->nullable(); // Belum Review, Dalam Review, Selesai, Ditunda
            $table->text('kka_sheet_tujuan')->nullable(); // JSON array reference ke KKA records
            
            // Editable by RA
            $table->date('tanggal_aktual_review')->nullable();
            $table->enum('status_review', ['Belum Review', 'Dalam Review', 'Selesai', 'Ditunda'])->default('Belum Review');
            $table->text('catatan_ra')->nullable();
            
            $table->timestamps();
            $table->unique(['wp_offsite_id', 'tanggal_data', 'kode_unit', 'area_review'], 'register_daily_unique');
            $table->index(['wp_offsite_id', 'tanggal_data']);
        });

        // 3-9. SEVEN KKA TABLES
        // Base KKA fields (shared across all 7 areas, dengan beberapa varian)
        $kkaAreas = [
            'kka_teller_kas' => 'Teller/Kas',
            'kka_kredit' => 'Kredit',
            'kka_biaya_beban' => 'Biaya/Beban',
            'kka_biaya_internal' => 'Biaya/Internal',
            'kka_pengaduan' => 'Pengaduan',
            'kka_transaksi_umum' => 'Transaksi Umum',
            'kka_transfer_ku' => 'Transfer/KU',
        ];

        foreach ($kkaAreas as $tableName => $areaName) {
            Schema::create($tableName, function (Blueprint $table) use ($areaName) {
                $table->id('kka_id');
                $table->foreignId('wp_offsite_id')->constrained('wp_offsite')->onDelete('cascade');
                $table->unsignedBigInteger('staging_id');
                $table->foreign('staging_id')->references('id')->on('staging_offsite')->onDelete('cascade');
                $table->string('kka_number')->nullable(); // No urut KKA
                $table->string('area_review')->default($areaName);
                
                // Read-only dari Staging (konteks transaksi)
                $table->date('tanggal_data');
                $table->string('kode_unit');
                $table->string('nama_unit')->nullable();
                $table->foreignId('ra_id')->constrained('users')->onDelete('cascade');
                $table->string('nama_ra')->nullable();
                $table->string('source_sheet')->nullable();
                $table->string('object_id')->nullable();
                $table->string('case_id')->nullable();
                $table->string('data_code')->nullable();
                $table->text('deskripsi_narasi')->nullable();
                $table->decimal('nominal', 15, 2)->nullable();
                $table->string('user_maker')->nullable();
                $table->string('risk_awal')->nullable();
                $table->string('exception_awal')->nullable();
                $table->string('jenis_exception_awal')->nullable();
                $table->boolean('sampel_low')->default(false);
                $table->text('catatan_rule')->nullable();
                
                // Tujuan Uji - Auto-generated dari template
                $table->text('tujuan_uji')->nullable();
                $table->text('kriteria')->nullable();
                $table->text('prosedur_uji')->nullable();
                
                // Editable by RA (kuning fields)
                $table->text('bukti_referensi')->nullable();
                $table->enum('hasil_uji', ['Belum Diuji', 'Sesuai', 'Tidak Sesuai', 'Tidak Dapat Disimpulkan'])->nullable();
                $table->string('jenis_exception_ra')->nullable();
                $table->integer('dampak')->nullable(); // 1-5
                $table->integer('kemungkinan')->nullable(); // 1-5
                $table->boolean('critical_trigger')->default(false);
                $table->text('klarifikasi_awal')->nullable();
                $table->text('klarifikasi_unit')->nullable();
                $table->enum('status_klarifikasi', ['Belum Diminta', 'Diminta', 'Diterima', 'Selesai', 'Eskalasi', 'Tidak Relevan'])->nullable();
                $table->boolean('perluasan_sampel')->default(false);
                $table->boolean('perlu_onsite')->default(false);
                $table->enum('keputusan_onsite', ['Tidak Perlu', 'Dijadwalkan', 'Dilaksanakan', 'Ditutup Offsite'])->nullable();
                $table->enum('keputusan_eskalasi', ['Belum Diputuskan', 'Tidak', 'Ya'])->nullable();
                $table->text('simpulan_ra')->nullable();
                $table->date('tanggal_ditemukan')->nullable();
                $table->enum('status_review', ['Belum Review', 'Dalam Proses', 'Selesai'])->nullable();
                
                // Computed fields
                $table->integer('skor_risiko')->nullable(); // dampak × kemungkinan
                $table->enum('kategori_risiko_final', ['High', 'Moderate', 'Low'])->nullable();
                $table->boolean('eskalasi_awal')->default(false);
                $table->boolean('rekomendasi_eskalasi')->default(false);
                
                // Reviewer field (Admin/Reviewer only)
                $table->text('catatan_reviewer')->nullable();
                $table->foreignId('reviewer_id')->nullable()->constrained('users')->onDelete('set null');
                
                $table->timestamps();
                $table->softDeletes();
                $table->index(['wp_offsite_id', 'tanggal_data']);
                $table->index(['status_review']);
            });
        }
    }

    public function down(): void
    {
        // Drop KKA tables
        Schema::dropIfExists('kka_transfer_ku');
        Schema::dropIfExists('kka_transaksi_umum');
        Schema::dropIfExists('kka_pengaduan');
        Schema::dropIfExists('kka_biaya_internal');
        Schema::dropIfExists('kka_biaya_beban');
        Schema::dropIfExists('kka_kredit');
        Schema::dropIfExists('kka_teller_kas');
        Schema::dropIfExists('register_harian');
        Schema::dropIfExists('staging_offsite');
    }
};
