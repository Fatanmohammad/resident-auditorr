<?php

namespace App\Services;

use App\Models\CbsTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class CbsFilterService
{
    public function processDumpData()
    {
        $unprocessed = CbsTransaction::where('is_processed', false)->get();

        if ($unprocessed->isEmpty()) {
            return;
        }

        $wpId = Schema::hasTable('wp_offsite') ? DB::table('wp_offsite')->value('id') : null;
        $stagingId = null;

        if (Schema::hasTable('staging_offsite')) {
            $stagingId = DB::table('staging_offsite')->value('id');

            if (!$stagingId && $wpId) {
                $firstTrx = $unprocessed->first();
                $currentUserId = Auth::id() ?? 29;

                $stagingData = [
                    'wp_offsite_id' => $wpId,
                    'kode_unit'     => $firstTrx->kode_unit ?? $firstTrx->data_unit ?? '001',
                    'tanggal_data'  => $firstTrx->tanggal_data ?? now()->toDateString(),
                    'ra_id'         => $currentUserId,
                    'source_table'  => 'cbs_transactions',
                    'status'        => 'draft',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];

                $existingCols = Schema::getColumnListing('staging_offsite');
                $filteredStagingData = array_intersect_key($stagingData, array_flip($existingCols));

                $stagingId = DB::table('staging_offsite')->insertGetId($filteredStagingData);
            }
        }

        $currentUserId = Auth::id() ?? 29;

        foreach ($unprocessed as $trx) {
            if (in_array($trx->kode_transaksi, ['TRF_INT', 'KU']) || $trx->nominal >= 50000000) {
                $rawPayload = [
                    'case_id'              => 'KU-' . $trx->no_referensi,
                    'no_referensi'         => $trx->no_referensi,
                    'tanggal_data'         => $trx->tanggal_data,
                    'kode_unit'            => $trx->kode_unit ?? $trx->data_unit ?? '001',
                    'user_maker'           => $trx->user_id ?? 'System',
                    'kode_transaksi'       => $trx->kode_transaksi,
                    'area_review'          => $trx->data_unit ?? 'Cabang Utama',
                    'deskripsi_narasi'     => $trx->deskripsi_narasi,
                    'nominal'              => $trx->nominal,
                    'data_source'          => 'cbs_transactions',
                    'risk_awal'            => $trx->nominal >= 100000000 ? 'High' : 'Moderate',
                    'jenis_exception_awal' => 'Indikasi Anomali Kas Utama',
                    'status_review'        => 'Belum',
                    'staging_id'           => $stagingId,
                    'wp_offsite_id'        => $wpId,
                    'ra_id'                => $currentUserId,
                    'user_id'              => $currentUserId,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];

                $filteredPayload = $this->filterPayloadForTable('kka_transfer_ku', $rawPayload);
                DB::table('kka_transfer_ku')->insert($filteredPayload);
            } 
            elseif (in_array($trx->kode_transaksi, ['TRF_UMUM', 'UM']) || $trx->nominal >= 100000000) {
                $rawPayload = [
                    'case_id'              => 'UM-' . $trx->no_referensi,
                    'no_referensi'         => $trx->no_referensi,
                    'tanggal_data'         => $trx->tanggal_data,
                    'kode_unit'            => $trx->kode_unit ?? $trx->data_unit ?? '001',
                    'user_maker'           => $trx->user_id ?? 'System',
                    'kode_transaksi'       => $trx->kode_transaksi,
                    'area_review'          => $trx->data_unit ?? 'Cabang Utama',
                    'deskripsi_narasi'     => $trx->deskripsi_narasi,
                    'nominal'              => $trx->nominal,
                    'data_source'          => 'cbs_transactions',
                    'risk_awal'            => 'Moderate',
                    'jenis_exception_awal' => 'Transfer Rekening Pihak Ketiga',
                    'status_review'        => 'Belum',
                    'staging_id'           => $stagingId,
                    'wp_offsite_id'        => $wpId,
                    'ra_id'                => $currentUserId,
                    'user_id'              => $currentUserId,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];

                $filteredPayload = $this->filterPayloadForTable('kka_transaksi_umum', $rawPayload);
                DB::table('kka_transaksi_umum')->insert($filteredPayload);
            }

            $trx->update(['is_processed' => true]);
        }
    }

    private function filterPayloadForTable(string $table, array $payload): array
    {
        $existingColumns = Schema::getColumnListing($table);
        return array_filter(
            $payload,
            fn($value, $key) => in_array($key, $existingColumns) && $value !== null,
            ARRAY_FILTER_USE_BOTH
        );
    }
}