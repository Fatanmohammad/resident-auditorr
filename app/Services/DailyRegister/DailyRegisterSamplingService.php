<?php

namespace App\Services\DailyRegister;

use App\Models\DailyRegister\DailyRegisterFinding;
use App\Models\Offsite\SamplingAreaPolicy;
use App\Models\Offsite\SamplingSizePolicy;
use Illuminate\Support\Facades\DB;

class DailyRegisterSamplingService
{
    /**
     * Kapasitas maksimum KKA per area review sesuai SOP 02 (Parameter_Sampling_SOP02 Baris A14:I20 & Baris 28).
     */
    public const KAPASITAS_KKA_PER_AREA = 500;

    /**
     * Jalankan mesin sampling khusus Daily Register untuk unit dan periode tertentu.
     * Mengamankan data yang telah diuji/direview, membatasi KKA maksimum 500 sampel per area,
     * serta menghapus data temuan (Moderate/High) non-sampel yang belum diuji.
     */
    public function runSampling(string $kodeUnit, ?string $periode = null): array
    {
        return DB::transaction(function () use ($kodeUnit, $periode) {
            $query = DailyRegisterFinding::where('kode_unit', $kodeUnit)
                ->whereIn('risk_awal', ['Moderate', 'High']);

            if (!empty($periode) && $periode !== 'all') {
                $query->where('periode', $periode);
            }

            $findings = $query->get();

            if ($findings->isEmpty()) {
                return [
                    'status'       => 'info',
                    'message'      => 'Tidak ada data temuan KKA aktif untuk disampling pada unit ' . $kodeUnit . '.',
                    'total_sampel' => 0,
                    'deleted'      => 0,
                ];
            }

            $selectedIds = [];
            $groupedBySheet = $findings->groupBy('source_sheet');
            $detailPerSheet = [];

            foreach ($groupedBySheet as $sourceSheet => $sheetFindings) {
                $areaSelected = [];
                $totalPopulasi = $sheetFindings->count();

                // 0. AMANKAN TEMUAN YANG SUDAH DIUJI / DIREVIEW AUDITOR (TIDAK BOLEH TERHAPUS)
                $alreadyTested = $sheetFindings->filter(function ($f) {
                    return !empty($f->hasil_uji) 
                        || !empty($f->simpulan_ra) 
                        || !empty($f->jenis_exception_ra)
                        || in_array(strtoupper($f->status_review ?? ''), ['APPROVED', 'REVISI']);
                });
                foreach ($alreadyTested as $tested) {
                    $areaSelected[] = $tested->id;
                }

                // Ambil kebijakan metode sampling per area/sheet
                $policy = SamplingAreaPolicy::where('source_sheet', $sourceSheet)
                    ->where('aktif', true)
                    ->first();

                $baseMetode = $policy?->metode_sampling ?? 'Random';
                $metodeSampling = ($totalPopulasi > 1000) ? 'Stratified' : $baseMetode;
                $stratifyCol = $policy?->stratify_by ?? 'kategori';

                // Hitung sisa slot kapasitas KKA untuk area ini
                $sisaKapasitasKka = max(0, self::KAPASITAS_KKA_PER_AREA - count($areaSelected));

                // 1. MANDATORY SAMPLING (Risk High yang belum diuji)
                $untestedHigh = $sheetFindings
                    ->where('risk_awal', 'High')
                    ->whereNotIn('id', $areaSelected);

                if ($untestedHigh->isNotEmpty() && $sisaKapasitasKka > 0) {
                    if ($untestedHigh->count() <= $sisaKapasitasKka) {
                        // Seluruh High tertampung dalam kapasitas KKA
                        foreach ($untestedHigh as $item) {
                            $areaSelected[] = $item->id;
                        }
                    } else {
                        // High melebihi kapasitas KKA (seperti kasus 3.000 Biaya):
                        // Prioritaskan yang bernominal terbesar (High Impact)
                        $sortedHigh = $untestedHigh->sortByDesc(fn($f) => (float)($f->nominal_terkait ?? 0));
                        $takenHigh = $sortedHigh->take($sisaKapasitasKka);
                        foreach ($takenHigh as $item) {
                            $areaSelected[] = $item->id;
                        }
                    }
                }

                // Update sisa kapasitas
                $sisaKapasitasKka = max(0, self::KAPASITAS_KKA_PER_AREA - count($areaSelected));

                // Sisa kandidat belum diuji (Hanya Moderate)
                $untestedModerate = $sheetFindings
                    ->where('risk_awal', 'Moderate')
                    ->whereNotIn('id', $areaSelected);

                if ($untestedModerate->isNotEmpty() && $sisaKapasitasKka > 0) {
                    // 2. CERTAINTY SAMPLING (Nominal > Rata-rata Populasi)
                    $averageAmount = $untestedModerate->avg('nominal_terkait') ?? 0;
                    $certainty = $untestedModerate->filter(fn($f) => (float)($f->nominal_terkait ?? 0) > (float)$averageAmount)
                        ->sortByDesc(fn($f) => (float)($f->nominal_terkait ?? 0));

                    $certaintyToTake = $certainty->take($sisaKapasitasKka);
                    foreach ($certaintyToTake as $item) {
                        $areaSelected[] = $item->id;
                    }

                    // Update sisa kapasitas
                    $sisaKapasitasKka = max(0, self::KAPASITAS_KKA_PER_AREA - count($areaSelected));

                    // 3. INITIAL / RANDOM / STRATIFIED SAMPLING (Sisa Moderate setelah Certainty)
                    $forRandom = $untestedModerate->whereNotIn('id', $areaSelected);

                    if ($forRandom->isNotEmpty() && $sisaKapasitasKka > 0) {
                        // Hitung kebutuhan sample size bertingkat sesuai SOP 02
                        $targetSampleSize = $this->calculateSampleSize($forRandom->count());
                        $sampleSize = min($targetSampleSize, $sisaKapasitasKka);

                        if ($sampleSize > 0) {
                            if ($metodeSampling === 'Stratified') {
                                // Cek kolom pengelompokan yang valid
                                $firstItem = $forRandom->first();
                                $useStratifyCol = ($firstItem && isset($firstItem->$stratifyCol)) ? $stratifyCol : null;

                                if ($useStratifyCol) {
                                    $groupedByStrata = $forRandom->groupBy($useStratifyCol);
                                    $stratifiedSample = collect();

                                    foreach ($groupedByStrata as $strataItems) {
                                        $strataSize = max(1, (int) round(($strataItems->count() / $forRandom->count()) * $sampleSize));
                                        $selectedFromStrata = $strataItems->random(min($strataSize, $strataItems->count()));
                                        $stratifiedSample = $stratifiedSample->merge($selectedFromStrata);
                                    }
                                    $finalRandom = $stratifiedSample->take($sampleSize);
                                } else {
                                    $finalRandom = $forRandom->random(min($sampleSize, $forRandom->count()));
                                }
                            } else {
                                // Random Murni
                                $finalRandom = $forRandom->random(min($sampleSize, $forRandom->count()));
                            }

                            foreach ($finalRandom as $item) {
                                $areaSelected[] = $item->id;
                            }
                        }
                    }
                }

                $selectedIds = array_merge($selectedIds, $areaSelected);
                $detailPerSheet[$sourceSheet] = [
                    'populasi_awal' => $totalPopulasi,
                    'sampel_masuk'  => count($areaSelected),
                    'kapasitas_kka' => self::KAPASITAS_KKA_PER_AREA,
                ];
            }

            // 4. BERSIHKAN FINDING MODERATE / HIGH YANG TIDAK TERPILIH DALAM SAMPEL
            //    (Hanya yang belum diuji/direview!)
            $deleteQuery = DailyRegisterFinding::where('kode_unit', $kodeUnit)
                ->whereIn('risk_awal', ['Moderate', 'High'])
                ->whereNotIn('id', $selectedIds)
                ->whereNull('hasil_uji')
                ->whereNull('simpulan_ra')
                ->whereNull('jenis_exception_ra')
                ->where(function ($q) {
                    $q->whereNull('status_review')
                      ->orWhere('status_review', 'Belum Direview');
                });

            if (!empty($periode) && $periode !== 'all') {
                $deleteQuery->where('periode', $periode);
            }

            $deletedCount = $deleteQuery->delete();

            return [
                'status'          => 'success',
                'message'         => 'Sampling SOP 02 berhasil dijalankan. Mengamankan ' . count($selectedIds) . ' sampel KKA (kapasitas max 500/area) dan membersihkan ' . $deletedCount . ' data non-sampel.',
                'total_sampel'    => count($selectedIds),
                'deleted'         => $deletedCount,
                'detail_per_area' => $detailPerSheet,
            ];
        });
    }

    /**
     * Hitung ukuran sampel awal (Initial Sample Size) bertingkat sesuai SOP 02
     * Mengacu pada sheet Parameter_Sampling_SOP02 Sel A5:C11:
     * - Populasi 1–10   : 100%
     * - Populasi 11–25  : 10
     * - Populasi 26–50  : 15
     * - Populasi 51–100 : 20
     * - Populasi 101–250: 25
     * - Populasi > 250  : 30
     */
    public function calculateSampleSize(int $populasi): int
    {
        if ($populasi <= 0) {
            return 0;
        }

        $rule = SamplingSizePolicy::where('min_populasi', '<=', $populasi)
            ->where(function ($q) use ($populasi) {
                $q->where('max_populasi', '>=', $populasi)
                  ->orWhereNull('max_populasi');
            })
            ->first();

        if ($rule) {
            if ($rule->sample_all) {
                return $populasi;
            }
            return (int) ($rule->min_sampel ?? 30);
        }

        // Fallback langsung sesuai tabel SOP 02
        if ($populasi <= 10) return $populasi;
        if ($populasi <= 25) return 10;
        if ($populasi <= 50) return 15;
        if ($populasi <= 100) return 20;
        if ($populasi <= 250) return 25;
        return 30;
    }
}
