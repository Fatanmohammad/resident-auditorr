<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('surprise_audit_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('surprise_audit_assignments', 'ruang_lingkup_area')) {
                $table->json('ruang_lingkup_area')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('surprise_audit_assignments', function (Blueprint $table) {
            $table->dropColumn('ruang_lingkup_area');
        });
    }
};