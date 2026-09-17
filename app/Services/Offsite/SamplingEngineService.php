<?php

namespace App\Services\Offsite;

use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\SamplingAreaPolicy;
use App\Models\Offsite\SamplingSizePolicy;
use App\Models\Offsite\WorkQueue;
use Illuminate\Support\Facades\DB;

class SamplingEngineService
{
    /**
     * Jalankan mesin sampling untuk unit dan periode tertentu.
     */
    public function runSampling(string $kodeUnit, string $periode): array
    {
        return DB::transaction(function () use ($kodeUnit, $periode) {
            // Ambil semua temuan yang belum masuk work_queue
            $findings = KkaFinding::where('kode_unit', $kodeUnit)
                ->where('periode', $periode)
                ->whereNotIn('id', WorkQueue::pluck('kka_finding_id'))
                ->get();

            if ($findings->isEmpty()) {
                return ['status' => 'info', 'message' => 'Tidak ada temuan baru untuk disampling.'];
            }

            $selectedQueue = [];
            $groupedBySheet = $findings->groupBy('source_sheet');

            foreach ($groupedBySheet as $sourceSheet => $sheetFindings) {

                $totalPopulasi = $sheetFindings->count();
                
                // Ambil kebijakan metode sampling per area/sheet
                $policy = SamplingAreaPolicy::where('source_sheet', $sourceSheet)
                    ->where('aktif', true)
                    ->first();
                
                $baseMetode = $policy?->metode_sampling ?? 'Random';

                // ATURAN TRANSISI: Jika populasi > 1000, otomatis override ke Stratified
                $metodeSampling = ($totalPopulasi > 1000) ? 'Stratified' : $baseMetode;

                // -------------------------------------------------------------
                // 1. Mandatory Sampling (Risk High)
                // -------------------------------------------------------------
                $mandatory = $sheetFindings->filter(fn($f) => strtolower($f->risk_awal) === 'high');
                foreach ($mandatory as $item) {
                    $selectedQueue[] = $this->formatQueueItem($item, 'Mandatory', null, $totalPopulasi, $periode);
                }

                // Sisa kandidat setelah Mandatory
                $remaining = $sheetFindings->reject(fn($f) => strtolower($f->risk_awal) === 'high');

                if ($remaining->isNotEmpty()) {
                    // ---------------------------------------------------------
                    // 2. Certainty Sampling (Nominal > Rata-rata Populasi)
                    // ---------------------------------------------------------
                    $averageAmount = $remaining->avg('nominal_terkait') ?? 0;

                    $certainty = $remaining->filter(fn($f) => (float)$f->nominal_terkait > (float)$averageAmount);
                    foreach ($certainty as $item) {
                        $selectedQueue[] = $this->formatQueueItem($item, 'Certainty', $metodeSampling, $totalPopulasi, $periode);

                    }

                    // ---------------------------------------------------------
                    // 3. Initial / Random / Stratified Sampling
                    // ---------------------------------------------------------
                    $forRandom = $remaining->reject(fn($f) => (float)$f->nominal_terkait > (float)$averageAmount);

                    if ($forRandom->isNotEmpty()) {
                        $sampleSize = $this->calculateSampleSize($forRandom->count());
                        
                        // Jika metode Stratified (atau populasi > 1000), gunakan sampling berstrata
                        if ($metodeSampling === 'Stratified' && !empty($policy?->stratify_by)) {
                            $stratifyCol = $policy->stratify_by;
                            $groupedByStrata = $forRandom->groupBy($stratifyCol);
                            $randomSample = collect();

                            foreach ($groupedByStrata as $strataItems) {
                                $strataSampleSize = max(1, (int) round(($strataItems->count() / $totalPopulasi) * $sampleSize));
                                $selectedFromStrata = $strataItems->random(min($strataSampleSize, $strataItems->count()));
                                $randomSample = $randomSample->merge($selectedFromStrata);
                            }
                            
                            $randomSample = $randomSample->take($sampleSize);
                        } else {
                            // Random Murni (untuk populasi <= 1000)
                            $randomSample = $forRandom->random(min($sampleSize, $forRandom->count()));
                        }

                        foreach ($randomSample as $item) {
                            $selectedQueue[] = $this->formatQueueItem($item, 'Initial', $metodeSampling, $totalPopulasi, $periode);
                        }
                    }

                }
            }

            // Simpan seluruh sampel terpilih ke tabel work_queues
            if (!empty($selectedQueue)) {
                WorkQueue::insert($selectedQueue);
            }

            return [
                'status'       => 'success',
                'message'      => 'Sampling berhasil dijalankan.',
                'total_sampel' => count($selectedQueue),
            ];
        });
    }

    private function formatQueueItem($finding, string $alasan, ?string $metode, int $populasi, string $periode): array
    {
        return [
            'kka_finding_id'       => $finding->id,
            'source_sheet'         => $finding->source_sheet,
            'periode'              => $finding->periode ?? $periode,
            'kode_unit'            => $finding->kode_unit,
            'alasan_terpilih'      => $alasan,
            'metode_sampling'      => $metode,
            'total_populasi'       => $populasi,
            'total_sampel_diambil' => null,
            'status'               => 'Menunggu',
            'created_at'           => now(),
            'updated_at'           => now(),
        ];
    }


    private function calculateSampleSize(int $populasi): int
    {
        $rule = SamplingSizePolicy::where('min_populasi', '<=', $populasi)
            ->where(function($q) use ($populasi) {
                $q->where('max_populasi', '>=', $populasi)
                  ->orWhereNull('max_populasi');
            })
            ->first();

        if (!$rule) return min(5, $populasi);

        if ($rule->sample_all) {
            return $populasi;
        }

        return (int) ($rule->min_sampel ?? 5);
    }
}