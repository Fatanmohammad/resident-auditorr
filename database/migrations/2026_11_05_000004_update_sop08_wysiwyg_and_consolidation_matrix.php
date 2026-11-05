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
        // 1. Tambahkan kolom WYSIWYG ke sop08_narasi_laporans
        if (Schema::hasTable('sop08_narasi_laporans')) {
            Schema::table('sop08_narasi_laporans', function (Blueprint $table) {
                if (!Schema::hasColumn('sop08_narasi_laporans', 'catatan_eksekutif')) {
                    $table->longText('catatan_eksekutif')->nullable()->after('report_id');
                }
                if (!Schema::hasColumn('sop08_narasi_laporans', 'rencana_bulan_depan')) {
                    $table->longText('rencana_bulan_depan')->nullable()->after('catatan_eksekutif');
                }
                if (!Schema::hasColumn('sop08_narasi_laporans', 'admin_editor_id')) {
                    $table->unsignedBigInteger('admin_editor_id')->nullable()->after('rencana_bulan_depan');
                }
                if (!Schema::hasColumn('sop08_narasi_laporans', 'is_consolidated')) {
                    $table->boolean('is_consolidated')->default(false)->after('admin_editor_id');
                }
            });
        }

        // 2. Buat tabel pencatatan kepatuhan KKA & konfirmasi nihil per cabang (sop08_kka_branch_statuses)
        if (!Schema::hasTable('sop08_kka_branch_statuses')) {
            Schema::create('sop08_kka_branch_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('kode_unit', 20)->index();
                $table->date('periode')->index();
                $table->string('area', 100)->default('ALL');
                $table->integer('populasi')->default(0);
                $table->integer('item_selesai')->default(0);
                $table->boolean('is_nihil')->default(false);
                $table->boolean('is_confirmed_nihil')->default(false);
                $table->enum('status_kepatuhan', ['HIJAU', 'MERAH'])->default('HIJAU');
                $table->text('catatan')->nullable();
                $table->unsignedBigInteger('confirmed_by')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamps();

                $table->unique(['kode_unit', 'periode', 'area']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sop08_narasi_laporans')) {
            Schema::table('sop08_narasi_laporans', function (Blueprint $table) {
                if (Schema::hasColumn('sop08_narasi_laporans', 'catatan_eksekutif')) {
                    $table->dropColumn(['catatan_eksekutif', 'rencana_bulan_depan', 'admin_editor_id', 'is_consolidated']);
                }
            });
        }

        Schema::dropIfExists('sop08_kka_branch_statuses');
    }
};
