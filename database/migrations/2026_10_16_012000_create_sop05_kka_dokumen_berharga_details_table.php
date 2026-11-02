<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sop05_kka_dokumen_berharga_details')) {
            Schema::create('sop05_kka_dokumen_berharga_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('surprise_audit_assignment_id');
                $table->foreign('surprise_audit_assignment_id', 'fk_sop05_dok_berharga_sa_id')
                      ->references('id')
                      ->on('surprise_audit_assignments')
                      ->onDelete('cascade');
                $table->string('jenis_dokumen')->nullable();
                $table->integer('persediaan_sistem_qty')->default(0);
                $table->integer('catatan_petugas_qty')->default(0);
                $table->integer('hasil_fisik_qty')->default(0);
                $table->decimal('harga_per_lembar', 15, 2)->default(0);
                $table->decimal('nilai_sistem', 15, 2)->default(0);
                $table->decimal('nilai_fisik', 15, 2)->default(0);
                $table->integer('selisih_sistem_catatan')->default(0);
                $table->integer('selisih_sistem_fisik')->default(0);
                $table->integer('selisih_catatan_fisik')->default(0);
                $table->decimal('selisih_nilai', 15, 2)->default(0);
                $table->string('range_nomor_seri')->nullable();
                $table->string('nomor_hilang_lompat')->nullable();
                $table->string('status_uji')->default('SESUAI');
                $table->text('sebab')->nullable();
                $table->text('dampak')->nullable();
                $table->string('referensi_bukti')->nullable();
                $table->string('validasi')->default('VALID');
                $table->string('exception_aktif')->default('Tidak');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sop05_kka_dokumen_berharga_details');
    }
};
