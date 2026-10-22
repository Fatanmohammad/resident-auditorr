<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('surprise_audit_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('surprise_audit_assignments', 'tujuan')) {
                $table->text('tujuan')->nullable();
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'klasifikasi_kerahasiaan')) {
                $table->string('klasifikasi_kerahasiaan')->default('Rahasia - Need to Know');
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'status_assignment')) {
                $table->string('status_assignment')->default('SIAP DILAKSANAKAN');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surprise_audit_assignments', function (Blueprint $table) {
            $table->dropColumn(['tujuan', 'klasifikasi_kerahasiaan', 'status_assignment']);
        });
    }
};