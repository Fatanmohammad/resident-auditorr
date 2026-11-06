<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('offsite_kka_checklist_answers')) {
            DB::statement("ALTER TABLE `offsite_kka_checklist_answers` MODIFY `jawaban` VARCHAR(20) DEFAULT '✓'");
        }

        if (Schema::hasTable('onsite_kka_checklist_answers')) {
            DB::statement("ALTER TABLE `onsite_kka_checklist_answers` MODIFY `jawaban` VARCHAR(20) DEFAULT '✓'");
        }
    }

    public function down(): void
    {
        // No-op or revert to enum
    }
};

