<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dump_pengaduan', function (Blueprint $table) {
            $table->string('no_nasabah')->nullable()->after('no_tiket');      // NO_NSB / NO_CIF / CIF
            $table->string('no_rekening_nasabah')->nullable()->after('no_nasabah'); // NO_REK / NO_REKENING
            $table->string('nama_nasabah')->nullable()->after('no_rekening_nasabah');
            $table->date('tanggal_selesai')->nullable()->after('status_pengaduan'); // TGL_SELESAI
            $table->decimal('nominal_kerugian', 15, 2)->nullable()->after('tanggal_selesai');
            $table->text('bukti_penyelesaian')->nullable()->after('nominal_kerugian');
            $table->text('catatan_tl_cabang')->nullable()->after('bukti_penyelesaian');
        });
    }

    public function down(): void
    {
        Schema::table('dump_pengaduan', function (Blueprint $table) {
            $table->dropColumn([
                'no_nasabah',
                'no_rekening_nasabah',
                'nama_nasabah',
                'tanggal_selesai',
                'nominal_kerugian',
                'bukti_penyelesaian',
                'catatan_tl_cabang',
            ]);
        });
    }
};
