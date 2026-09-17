<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_queues', function (Blueprint $table) {
            $table->id();

            // Relasi ke temuan asal (kka_findings) yang jadi kandidat sampel
            $table->foreignId('kka_finding_id')->constrained('kka_findings')->cascadeOnDelete();

            $table->string('source_sheet');   // area KKA, misal KKA_Teller_Kas
            $table->date('periode');          // periode/batch pengelompokan sampling (misal awal bulan data)
            $table->string('kode_unit');

            // Kenapa item ini terpilih jadi sampel
            $table->enum('alasan_terpilih', ['Mandatory', 'Certainty', 'Targeted', 'Initial', 'Expansion']);
            $table->string('metode_sampling')->nullable(); // Random/Stratified/MUS/Targeted (null jika Mandatory)

            // Info ukuran populasi & sampel saat batch ini dibuat (untuk audit trail)
            $table->unsignedInteger('total_populasi')->nullable();
            $table->unsignedInteger('total_sampel_diambil')->nullable();

            $table->enum('status', ['Menunggu', 'Sedang Diuji', 'Selesai'])->default('Menunggu');

            $table->timestamps();


            $table->unique(['kka_finding_id'], 'work_queues_finding_unique'); // 1 temuan cuma boleh 1x masuk antrean
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_queues');
    }
};