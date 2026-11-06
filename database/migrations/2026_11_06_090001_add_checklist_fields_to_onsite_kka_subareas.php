<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'onsite_kka_teller_kas',
        'onsite_kka_dpks',
        'onsite_kka_kredits',
        'onsite_kka_biayas',
        'onsite_kka_atms',
        'onsite_kka_pengaduans',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName)) continue;

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'checklist_answers')) {
                    $table->longText('checklist_answers')->nullable()->after('status_uji');
                }
                if (!Schema::hasColumn($tableName, 'selected_sub_areas')) {
                    $table->text('selected_sub_areas')->nullable()->after('checklist_answers');
                }
                if (!Schema::hasColumn($tableName, 'hasil_uji')) {
                    $table->text('hasil_uji')->nullable()->after('selected_sub_areas');
                }
                if (!Schema::hasColumn($tableName, 'klarifikasi_unit')) {
                    $table->text('klarifikasi_unit')->nullable()->after('hasil_uji');
                }
                if (!Schema::hasColumn($tableName, 'file_bukti')) {
                    $table->string('file_bukti')->nullable()->after('klarifikasi_unit');
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName)) continue;

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $cols = ['checklist_answers', 'selected_sub_areas', 'hasil_uji', 'klarifikasi_unit', 'file_bukti'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn($tableName, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
