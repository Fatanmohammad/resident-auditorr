<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connections = array_unique(array_filter([
            $this->getConnection(),
            config('database.default'),
            'sqlite',
            'mysql',
        ]));

        foreach ($connections as $conn) {
            try {
                $schema = Schema::connection($conn);
                if (!$schema->hasTable('register_triggers')) {
                    continue;
                }

                if (!$schema->hasColumn('register_triggers', 'dokumen_override')) {
                    $schema->table('register_triggers', function (Blueprint $table) {
                        $table->string('dokumen_override', 255)->nullable()->after('alasan_override');
                    });
                }

                if (!$schema->hasColumn('register_triggers', 'status_tindak_lanjut_alarm')) {
                    $schema->table('register_triggers', function (Blueprint $table) {
                        $table->string('status_tindak_lanjut_alarm', 50)->nullable()->default('MENUNGGU TINDAK LANJUT');
                        $table->text('catatan_tindak_lanjut_alarm')->nullable();
                        $table->string('ditindaklanjuti_oleh', 150)->nullable();
                        $table->timestamp('waktu_tindak_lanjut_alarm')->nullable();
                    });
                }

                if ($schema->hasTable('risk_trigger_kka_induks')) {
                    $schema->table('risk_trigger_kka_induks', function (Blueprint $table) use ($schema) {
                        if (!$schema->hasColumn('risk_trigger_kka_induks', 'file_ringkasan_bukti')) {
                            $table->string('file_ringkasan_bukti', 255)->nullable()->after('ringkasan_bukti');
                        }
                        if (!$schema->hasColumn('risk_trigger_kka_induks', 'klausul_covenant')) {
                            $table->text('klausul_covenant')->nullable()->after('simpulan_ra');
                        }
                        if (!$schema->hasColumn('risk_trigger_kka_induks', 'status_covenant')) {
                            $table->string('status_covenant', 50)->nullable()->after('klausul_covenant');
                        }
                        if (!$schema->hasColumn('risk_trigger_kka_induks', 'catatan_covenant')) {
                            $table->text('catatan_covenant')->nullable()->after('status_covenant');
                        }
                        if (!$schema->hasColumn('risk_trigger_kka_induks', 'komparasi_sistem_data')) {
                            $table->text('komparasi_sistem_data')->nullable()->after('catatan_covenant');
                        }
                    });
                }

                if (!$schema->hasTable('risk_trigger_covenants')) {
                    $schema->create('risk_trigger_covenants', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('risk_trigger_kka_induk_id')->constrained('risk_trigger_kka_induks')->onDelete('cascade');
                        $table->string('visit_id', 100)->index();
                        $table->text('klausul_covenant');
                        $table->string('status_pemenuhan', 50)->default('Terpenuhi');
                        $table->text('catatan')->nullable();
                        $table->timestamps();
                    });
                }
            } catch (\Throwable $e) {
                // Ignore errors on unreachable connections (e.g. offline MySQL)
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connections = array_unique(array_filter([
            $this->getConnection(),
            config('database.default'),
            'sqlite',
            'mysql',
        ]));

        foreach ($connections as $conn) {
            try {
                $schema = Schema::connection($conn);

                if ($schema->hasTable('risk_trigger_covenants')) {
                    $schema->dropIfExists('risk_trigger_covenants');
                }

                if ($schema->hasTable('risk_trigger_kka_induks')) {
                    $schema->table('risk_trigger_kka_induks', function (Blueprint $table) use ($schema) {
                        $dropCols = [];
                        foreach (['file_ringkasan_bukti', 'klausul_covenant', 'status_covenant', 'catatan_covenant', 'komparasi_sistem_data'] as $col) {
                            if ($schema->hasColumn('risk_trigger_kka_induks', $col)) {
                                $dropCols[] = $col;
                            }
                        }
                        if (!empty($dropCols)) {
                            $table->dropColumn($dropCols);
                        }
                    });
                }

                if ($schema->hasTable('register_triggers')) {
                    $schema->table('register_triggers', function (Blueprint $table) use ($schema) {
                        $dropCols = [];
                        foreach (['dokumen_override', 'status_tindak_lanjut_alarm', 'catatan_tindak_lanjut_alarm', 'ditindaklanjuti_oleh', 'waktu_tindak_lanjut_alarm'] as $col) {
                            if ($schema->hasColumn('register_triggers', $col)) {
                                $dropCols[] = $col;
                            }
                        }
                        if (!empty($dropCols)) {
                            $table->dropColumn($dropCols);
                        }
                    });
                }
            } catch (\Throwable $e) {
                // Ignore unreachable connections
            }
        }
    }
};
