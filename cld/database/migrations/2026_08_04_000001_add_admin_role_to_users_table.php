<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah enum role agar mencakup 'admin'
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['ra', 'kabag_ra', 'kadiv_skai', 'auditee', 'pimsie', 'admin'])->default('ra')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['ra', 'kabag_ra', 'kadiv_skai', 'auditee', 'pimsie'])->default('ra')->change();
        });
    }
};
