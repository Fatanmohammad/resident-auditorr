<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('surprise_audit_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('surprise_audit_assignments', 'ra_user_id')) {
                $table->foreignId('ra_user_id')->nullable()->after('reviewer')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('reviewer')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('surprise_audit_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('surprise_audit_assignments', 'ra_user_id')) {
                $table->dropForeign(['ra_user_id']);
                $table->dropColumn('ra_user_id');
            }
            if (Schema::hasColumn('surprise_audit_assignments', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};

