<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sop09_register_indikasis', function (Blueprint $table) {
            if (!Schema::hasColumn('sop09_register_indikasis', 'tipe_kasus')) {
                $table->string('tipe_kasus')->default('INSIDENTIL')->after('sensitivitas_reputasi');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'pihak_terlibat')) {
                $table->string('pihak_terlibat')->default('INTERNAL')->after('tipe_kasus');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'dugaan_awal')) {
                $table->text('dugaan_awal')->nullable()->after('pihak_terlibat');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'fakta_temuan')) {
                $table->text('fakta_temuan')->nullable()->after('dugaan_awal');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'kronologi_awal')) {
                $table->text('kronologi_awal')->nullable()->after('fakta_temuan');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'area_kejadian')) {
                $table->string('area_kejadian')->nullable()->after('kronologi_awal');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'catatan_awal_ra')) {
                $table->text('catatan_awal_ra')->nullable()->after('area_kejadian');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'checklist_kriteria')) {
                $table->text('checklist_kriteria')->nullable()->after('catatan_awal_ra');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'skor_checklist')) {
                $table->integer('skor_checklist')->default(0)->after('checklist_kriteria');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'saran_keputusan_triase')) {
                $table->string('saran_keputusan_triase')->nullable()->after('skor_checklist');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'file_bukti')) {
                $table->string('file_bukti')->nullable()->after('saran_keputusan_triase');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'nama_bukti')) {
                $table->string('nama_bukti')->nullable()->after('file_bukti');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'tipe_bukti')) {
                $table->string('tipe_bukti')->default('DOKUMEN')->after('nama_bukti');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'nomor_memo_eskalasi')) {
                $table->string('nomor_memo_eskalasi')->nullable()->after('tipe_bukti');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'tanggal_memo')) {
                $table->date('tanggal_memo')->nullable()->after('nomor_memo_eskalasi');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'perihal_memo')) {
                $table->string('perihal_memo')->nullable()->after('tanggal_memo');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'isi_memo')) {
                $table->text('isi_memo')->nullable()->after('perihal_memo');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'arahan_kadiv')) {
                $table->text('arahan_kadiv')->nullable()->after('isi_memo');
            }
            if (!Schema::hasColumn('sop09_register_indikasis', 'flagged_from')) {
                $table->string('flagged_from')->nullable()->after('arahan_kadiv');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sop09_register_indikasis', function (Blueprint $table) {
            $table->dropColumn([
                'tipe_kasus',
                'pihak_terlibat',
                'dugaan_awal',
                'fakta_temuan',
                'kronologi_awal',
                'area_kejadian',
                'catatan_awal_ra',
                'checklist_kriteria',
                'skor_checklist',
                'saran_keputusan_triase',
                'file_bukti',
                'nama_bukti',
                'tipe_bukti',
                'nomor_memo_eskalasi',
                'tanggal_memo',
                'perihal_memo',
                'isi_memo',
                'arahan_kadiv',
                'flagged_from',
            ]);
        });
    }
};

