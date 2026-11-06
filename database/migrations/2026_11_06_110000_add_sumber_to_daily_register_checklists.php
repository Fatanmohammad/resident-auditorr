<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('daily_register_checklists')) {
            Schema::table('daily_register_checklists', function (Blueprint $table) {
                if (!Schema::hasColumn('daily_register_checklists', 'sumber')) {
                    $table->string('sumber', 100)->default('BPP')->after('item_checklist');
                }
            });
        }

        if (Schema::hasTable('daily_register_checklist_answers')) {
            Schema::table('daily_register_checklist_answers', function (Blueprint $table) {
                // Ubah kolom jawaban agar bisa menyimpan '✓', '✗', 'N/A', 'Ya', 'Tidak'
                $table->string('jawaban', 20)->default('✓')->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('daily_register_checklists')) {
            Schema::table('daily_register_checklists', function (Blueprint $table) {
                if (Schema::hasColumn('daily_register_checklists', 'sumber')) {
                    $table->dropColumn('sumber');
                }
            });
        }
    }
};
