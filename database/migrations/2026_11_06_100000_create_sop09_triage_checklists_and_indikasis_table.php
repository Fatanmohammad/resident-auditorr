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
        // 1. Kertas Kerja Fraud Triage Checklist RA (POJK No. 12 Tahun 2024 & ACFE FEM)
        Schema::create('sop09_fraud_triage_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_triage')->unique();
            $table->string('kode_unit')->nullable();
            $table->string('nama_cabang');
            $table->date('tanggal_pemeriksaan');
            $table->string('nama_ra');
            $table->json('checklist_answers')->nullable(); // Array 28 item: [{no, jawaban, catatan, regulasi, critical}]
            $table->integer('total_item')->default(28);
            $table->integer('dijawab_ya')->default(0);
            $table->integer('dijawab_tidak')->default(0);
            $table->integer('belum_dijawab')->default(28);
            $table->boolean('ada_critical_trigger')->default(false); // Item 3, 15, 17 bernilai YA
            $table->string('keputusan_triage')->default('KLARIFIKASI TAMBAHAN'); 
            // 'ESKALASI SEGERA', 'LANJUT KE PENYELIDIKAN', 'KLARIFIKASI TAMBAHAN', 'TIDAK DILANJUTKAN'
            $table->text('catatan_kesimpulan')->nullable();
            $table->string('status')->default('Final');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 2. Rekap Matriks Temuan Indikasi Mencurigakan per Cabang (18 Indikator)
        Schema::create('sop09_temuan_indikasi_cabang', function (Blueprint $table) {
            $table->id();
            $table->string('kode_cabang')->nullable();
            $table->string('nama_cabang');
            $table->string('periode_bulan')->default(date('F Y'));

            // Grup 1: KAS & TELLER (6 Indikator)
            $table->integer('reversal_teller')->default(0);
            $table->integer('selisih_kas_teller')->default(0);
            $table->integer('selisih_kas_khasanah')->default(0);
            $table->integer('kas_kecil_melebihi_limit')->default(0);
            $table->integer('kas_kecil_belum_selesai')->default(0);
            $table->integer('dual_control_kunci_dilanggar')->default(0);

            // Grup 2: REKENING & SISTEM (6 Indikator)
            $table->integer('trx_rekening_pasif')->default(0);
            $table->integer('trx_rekening_titipan')->default(0);
            $table->integer('trx_tidak_wajar_pegawai')->default(0);
            $table->integer('trx_luar_jam_operasional')->default(0);
            $table->integer('setoran_non_tunai_pegawai')->default(0);
            $table->integer('sharing_user_id')->default(0);

            // Grup 3: ATM & APMK (2 Indikator)
            $table->integer('skimming_atm')->default(0);
            $table->integer('selisih_kas_crm_atm')->default(0);

            // Grup 4: KREDIT (2 Indikator)
            $table->integer('restrukturisasi_berulang')->default(0);
            $table->integer('pencairan_tanpa_dokumen')->default(0);

            // Grup 5: SDM (KONTROL PREVENTIF) (1 Indikator)
            $table->integer('cuti_wajib_belum_diambil')->default(0);

            // Grup 6: PENGADUAN NASABAH (1 Indikator)
            $table->integer('lonjakan_pengaduan_sla')->default(0);

            // Catatan Umum / Temuan Lain
            $table->text('catatan_umum')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sop09_temuan_indikasi_cabang');
        Schema::dropIfExists('sop09_fraud_triage_checklists');
    }
};
