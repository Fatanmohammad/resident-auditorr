<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Checklist untuk 6 Tab KKA Daily Register
        if (!Schema::hasTable('daily_register_checklists')) {
            Schema::create('daily_register_checklists', function (Blueprint $table) {
                $table->id();
                $table->string('source_sheet', 50)->index(); // KKA_Teller_Kas, KKA_DPK_Layanan, KKA_Kredit, KKA_Biaya_Beban, KKA_Kartu_ATM, KKA_Pengaduan
                $table->string('sub_area_code', 20); // A1, A2, A3, A4, D1, D2, C1, E1, B1, etc.
                $table->string('sub_area_name', 255);
                $table->integer('item_no');
                $table->text('item_checklist');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 2. Jawaban Checklist per KKA Daily Register Finding
        if (!Schema::hasTable('daily_register_checklist_answers')) {
            Schema::create('daily_register_checklist_answers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('daily_register_finding_id');
                $table->unsignedBigInteger('checklist_id');
                $table->enum('jawaban', ['Ya', 'Tidak'])->default('Ya');
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->foreign('daily_register_finding_id', 'fk_drca_finding')
                      ->references('id')->on('daily_register_findings')->onDelete('cascade');
                $table->foreign('checklist_id', 'fk_drca_chk')
                      ->references('id')->on('daily_register_checklists')->onDelete('cascade');

                $table->unique(['daily_register_finding_id', 'checklist_id'], 'dr_finding_chk_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_register_checklist_answers');
        Schema::dropIfExists('daily_register_checklists');
    }
};
