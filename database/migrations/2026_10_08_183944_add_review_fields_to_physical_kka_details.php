<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('physical_kka_cash_details', function (Blueprint $table) {
            $table->string('reviewer_id')->nullable();
            $table->string('status_review')->default('Pending');
            $table->text('catatan_reviewer')->nullable();
        });

        Schema::table('physical_kka_obs_details', function (Blueprint $table) {
            $table->string('reviewer_id')->nullable();
            $table->string('status_review')->default('Pending');
            $table->text('catatan_reviewer')->nullable();
        });

        Schema::table('physical_kka_stok_details', function (Blueprint $table) {
            $table->string('reviewer_id')->nullable();
            $table->string('status_review')->default('Pending');
            $table->text('catatan_reviewer')->nullable();
        });
    }

    public function down()
    {
        Schema::table('physical_kka_cash_details', function (Blueprint $table) {
            $table->dropColumn(['reviewer_id', 'status_review', 'catatan_reviewer']);
        });

        Schema::table('physical_kka_obs_details', function (Blueprint $table) {
            $table->dropColumn(['reviewer_id', 'status_review', 'catatan_reviewer']);
        });

        Schema::table('physical_kka_stok_details', function (Blueprint $table) {
            $table->dropColumn(['reviewer_id', 'status_review', 'catatan_reviewer']);
        });
    }
};
