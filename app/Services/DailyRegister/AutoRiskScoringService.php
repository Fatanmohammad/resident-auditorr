<?php

namespace App\Services\DailyRegister;

use App\Models\DailyRegister\DailyRegisterFinding;
use App\Models\DailyRegister\PhysicalKka;
use App\Models\DailyRegister\PhysicalKkaCashDetail;
use App\Models\DailyRegister\PhysicalKkaAtmMachine;
use App\Models\DailyRegister\PhysicalKkaKartuAtmDetail;
use App\Models\DailyRegister\PhysicalKkaStokDetail;
use App\Models\DailyRegister\PhysicalKkaBarangCetakanDetail;
use App\Models\DailyRegister\PhysicalKkaObsDetail;
use Illuminate\Support\Facades\DB;

class AutoRiskScoringService
{
    /**
     * Hitung Skor Dampak (Impact) Skala 1 - 5.
     *
     * Aturan:
     * - Jika tidak ada temuan (semua checklist Ya/Sesuai dan Selisih == 0) -> Impact = 1
     * - Jika terdapat temuan (Tidak / Kurang Baik / Selisih != 0):
     *   - Dampak administratif ringan / nominal kecil (< Rp 5.000.000 atau 1 item non-kritikal) -> Impact = 2
     *   - Dampak sedang (Rp 5.000.000 s.d. < Rp 25.000.000 atau 2–3 item tidak sesuai) -> Impact = 3
     *   - Dampak besar (Rp 25.000.000 s.d. < Rp 100.000.000 atau selisih warkat berharga/kartu ATM atau > 3 item gagal) -> Impact = 4
     *   - Dampak sangat material (>= Rp 100.000.000, selisih kas/ATM signifikan, atau pelanggaran keamanan kritikal seperti dual control khasanah/ATM, CCTV mati, alarm mati) -> Impact = 5
     */
    public function calculateImpact(
        float $nominal = 0.0,
        int $failedItemCount = 0,
        bool $hasCriticalSecurity = false,
        bool $hasWarkatCardDiff = false,
        bool $hasCashAtmDiff = false,
        bool $hasFinding = true
    ): int {
        if (!$hasFinding) {
            return 1;
        }

        $absNominal = abs($nominal);

        // Level 5: Sangat Material
        if ($hasCriticalSecurity || $absNominal >= 100000000 || ($hasCashAtmDiff && $absNominal >= 25000000)) {
            return 5;
        }

        // Level 4: Dampak Besar
        if ($absNominal >= 25000000 || $hasWarkatCardDiff || $failedItemCount > 3) {
            return 4;
        }

        // Level 3: Dampak Sedang
        if ($absNominal >= 5000000 || $failedItemCount >= 2) {
            return 3;
        }

        // Level 2: Dampak Administratif Ringan / Nominal Kecil (< 5jt atau 1 item non-kritikal)
        return 2;
    }

    /**
     * Cari unit target dengan normalisasi BS-
     */
    public function normalizeUnitCodes(string $unitCode): array
    {
        $clean = str_replace('BS-', '', trim($unitCode));
        return array_values(array_unique(array_filter([
            $unitCode,
            $clean,
            'BS-' . $clean,
        ])));
    }

    /**
     * Hitung riwayat temuan berulang di database cabang tersebut.
     */
    public function countHistory(string $unitCode, string $type, ?int $excludeId = null, string $context = 'physical'): int
    {
        $targetUnits = $this->normalizeUnitCodes($unitCode);

        if ($context === 'daily_register') {
            $sheetGroup = match ($type) {
                'KKA_Teller', 'KKA_Teller_Kas', 'KKA_Kas_Kecil', 'KKA_Kas_Khasanah' => ['KKA_Teller_Kas', 'KKA_Teller', 'KKA_Kas_Kecil', 'KKA_Kas_Khasanah'],
                default => [$type],
            };

            $query = DailyRegisterFinding::whereIn('kode_unit', $targetUnits)
                ->whereIn('source_sheet', $sheetGroup);

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            return $query->where(function ($q) {
                $q->whereHas('checklistAnswers', function ($qa) {
                    $qa->where('jawaban', 'Tidak');
                })
                ->orWhereIn('risk_final', ['Low to Moderate', 'Moderate', 'Moderate to High', 'High', 'Critical'])
                ->orWhere(function ($sub) {
                    $sub->whereNull('risk_final')
                        ->where(function ($s2) {
                            $s2->whereNotNull('jenis_exception_ra')
                               ->orWhereNotNull('hasil_uji');
                        });
                });
            })->count();
        }

        // Context: physical KKA
        $query = PhysicalKka::where(function ($q) use ($targetUnits) {
            $q->whereIn('kode_unit', $targetUnits);
        });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $typeKey = strtolower($type);

        return match ($typeKey) {
            'kas', 'khasanah', 'cash' => (clone $query)->where(function ($q) {
                $q->where('kas_is_critical', true)
                  ->orWhereIn('kas_risk_level', ['Low to Moderate', 'Moderate', 'Moderate to High', 'High'])
                  ->orWhereRaw('ABS(COALESCE(selisih_kas_khasanah, 0)) >= 0.01')
                  ->orWhereHas('cashDetails', fn($cd) => $cd->where('is_exception', true));
            })->count(),

            'atm', 'cash_atm', 'mesin_atm' => (clone $query)->where(function ($q) {
                $q->where('atm_is_critical', true)
                  ->orWhereIn('atm_risk_level', ['Low to Moderate', 'Moderate', 'Moderate to High', 'High'])
                  ->orWhereHas('atmMachines', fn($am) => $am->where('is_exception', true)->orWhereRaw('ABS(COALESCE(selisih, 0)) >= 0.01'));
            })->count(),

            'kartu', 'kartu_atm' => (clone $query)->where(function ($q) {
                $q->where('kartu_is_critical', true)
                  ->orWhereIn('kartu_risk_level', ['Low to Moderate', 'Moderate', 'Moderate to High', 'High'])
                  ->orWhereHas('kartuAtmDetails', fn($kd) => $kd->where('is_exception', true)->orWhere('jawaban_kontrol', 'Tidak'));
            })->count(),

            'stok', 'cekbg', 'stok_cek_bg' => (clone $query)->where(function ($q) {
                $q->where('stok_is_critical', true)
                  ->orWhereIn('stok_risk_level', ['Low to Moderate', 'Moderate', 'Moderate to High', 'High'])
                  ->orWhereHas('stokDetails', fn($sd) => $sd->where('is_exception', true)->orWhere('status_uji', 'Tidak'));
            })->count(),

            'cetakan', 'bct', 'barang_cetakan' => (clone $query)->where(function ($q) {
                $q->where('cetakan_is_critical', true)
                  ->orWhereIn('cetakan_risk_level', ['Low to Moderate', 'Moderate', 'Moderate to High', 'High'])
                  ->orWhereHas('barangCetakanDetails', fn($bd) => $bd->where('is_exception', true)->orWhere('jawaban_kontrol', 'Tidak'));
            })->count(),

            'observasi', 'obs' => (clone $query)->where(function ($q) {
                $q->where('obs_is_critical', true)
                  ->orWhereIn('obs_risk_level', ['Low to Moderate', 'Moderate', 'Moderate to High', 'High'])
                  ->orWhereHas('obsDetails', fn($od) => $od->where('is_exception', true)->orWhereIn('hasil_observasi', ['Tidak Sesuai', 'Tidak', 'Kurang Baik']));
            })->count(),

            default => (clone $query)->count(),
        };
    }

    /**
     * Hitung Skor Kemungkinan (Likelihood) Skala 1 - 5 Berdasarkan Riwayat Temuan.
     */
    public function calculateLikelihoodFromHistoryCount(int $historyCount, bool $hasFinding = true): array
    {
        if (!$hasFinding) {
            return [
                'likelihood' => 1,
                'history_count' => 0,
                'label' => '1 — Temuan Baru',
                'badge' => 'Temuan Baru',
                'note' => 'Nihil Temuan',
            ];
        }

        if ($historyCount <= 0) {
            $likelihood = 1;
            $label = '1 — Temuan Baru';
            $badge = 'Temuan Baru';
            $note = 'Temuan pertama kali';
        } elseif ($historyCount === 1) {
            $likelihood = 2;
            $label = '2 — Berulang 1x';
            $badge = 'Berulang 1x';
            $note = 'Terdeteksi 1 temuan serupa sebelumnya di cabang ini — Berulang 1x';
        } elseif ($historyCount <= 3) {
            $likelihood = 3;
            $label = '3 — Temuan Berulang';
            $badge = 'Temuan Berulang';
            $note = "Terdeteksi {$historyCount} temuan serupa sebelumnya di cabang ini — Temuan Berulang";
        } elseif ($historyCount <= 5) {
            $likelihood = 4;
            $label = '4 — Sering Berulang';
            $badge = 'Sering Berulang';
            $note = "Terdeteksi {$historyCount} temuan serupa sebelumnya di cabang ini — Sering Berulang";
        } else {
            $likelihood = 5;
            $label = '5 — Sangat Sering Berulang';
            $badge = 'Sangat Sering Berulang';
            $note = "Terdeteksi {$historyCount} temuan serupa sebelumnya di cabang ini — Sangat Sering Berulang";
        }

        return [
            'likelihood' => $likelihood,
            'history_count' => $historyCount,
            'label' => $label,
            'badge' => $badge,
            'note' => $note,
        ];
    }

    /**
     * Hitung Status Critical (Ya / Tidak).
     */
    public function calculateCritical(
        int $impact,
        bool $hasCashDiff = false,
        bool $hasDualControlViolation = false,
        bool $hasWarkatDiff = false,
        bool $explicitCritical = false
    ): bool {
        if ($explicitCritical) {
            return true;
        }

        if ($hasCashDiff || $hasDualControlViolation || $hasWarkatDiff || $impact === 5) {
            return true;
        }

        return false;
    }

    /**
     * Hitung Risk Score dan Risk Level dari Matriks Risiko Audit.
     */
    public function determineRisk(int $impact, int $likelihood, bool $isCritical, bool $hasFinding = true): array
    {
        if (!$hasFinding) {
            return [
                'score' => 1,
                'level' => 'Low',
                'perlu_onsite' => 'Tidak Perlu',
            ];
        }

        $impact = max(1, min(5, $impact));
        $likelihood = max(1, min(5, $likelihood));
        $score = $impact * $likelihood;

        if ($isCritical) {
            // Jika Critical == Ya dan skor masih rendah, pastikan minimal naik ke kategori Moderate to High / High
            if ($score < 16) {
                $score = max($score, 16);
            }
            return [
                'score' => $score,
                'level' => 'High',
                'perlu_onsite' => 'Perlu',
            ];
        }

        $level = match (true) {
            $score >= 16 => 'High',
            $score >= 12 => 'Moderate to High',
            $score >= 8  => 'Moderate',
            $score >= 4  => 'Low to Moderate',
            default      => 'Low',
        };

        $perluOnsite = ($score >= 8 || in_array($level, ['Moderate to High', 'High'])) ? 'Perlu' : 'Tidak Perlu';

        return [
            'score' => $score,
            'level' => $level,
            'perlu_onsite' => $perluOnsite,
        ];
    }

    /**
     * Evaluasi otomatis risiko untuk Tab KKA Daily Register.
     */
    public function evaluateDailyRegister(
        string $unitCode,
        string $sourceSheet,
        float $nominal = 0.0,
        int $failedCount = 0,
        bool $explicitCritical = false,
        ?int $findingId = null,
        array $extraContext = []
    ): array {
        $hasFinding = ($failedCount > 0 || $explicitCritical);
        $hasCriticalSecurity = !empty($extraContext['has_critical_security']);
        $hasWarkatCardDiff = !empty($extraContext['has_warkat_diff']) || in_array($sourceSheet, ['KKA_Kartu_ATM']);
        $hasCashAtmDiff = in_array($sourceSheet, ['KKA_Teller_Kas', 'KKA_Teller', 'KKA_Kas_Kecil', 'KKA_Kas_Khasanah']);

        // 1. Impact
        $impact = $this->calculateImpact(
            $nominal,
            $failedCount,
            $hasCriticalSecurity,
            $hasWarkatCardDiff,
            $hasCashAtmDiff,
            $hasFinding
        );

        // 2. Likelihood from branch history
        $historyCount = $this->countHistory($unitCode, $sourceSheet, $findingId, 'daily_register');
        $likInfo = $this->calculateLikelihoodFromHistoryCount($historyCount, $hasFinding);
        $likelihood = $likInfo['likelihood'];

        // 3. Critical
        $isCritical = $this->calculateCritical(
            $impact,
            !empty($extraContext['has_cash_diff']) || ($hasCashAtmDiff && abs($nominal) >= 25000000),
            $hasCriticalSecurity,
            $hasWarkatCardDiff,
            $explicitCritical
        );

        // 4. Risk Score & Level
        $riskInfo = $this->determineRisk($impact, $likelihood, $isCritical, $hasFinding);

        return [
            'has_finding'          => $hasFinding,
            'impact'               => $impact,
            'impact_label'         => $this->getImpactLabel($impact, $hasFinding),
            'likelihood'           => $likelihood,
            'likelihood_label'     => $likInfo['label'],
            'likelihood_badge'     => $likInfo['badge'],
            'likelihood_note'      => $likInfo['note'],
            'history_count'        => $historyCount,
            'is_critical'          => $isCritical,
            'critical_label'       => $isCritical ? 'Ya' : 'Tidak',
            'risk_score'           => $riskInfo['score'],
            'risk_level'           => $riskInfo['level'],
            'perlu_onsite'         => $riskInfo['perlu_onsite'],
            'failed_item_count'    => $failedCount,
        ];
    }

    /**
     * Evaluasi otomatis risiko untuk Tab KKA Fisik.
     */
    public function evaluatePhysicalKka(string $tab, string $unitCode, array $payload, ?int $currentKkaId = null): array
    {
        $tab = strtolower($tab);
        $hasFinding = false;
        $failedCount = 0;
        $hasCriticalSecurity = false;
        $hasWarkatCardDiff = false;
        $hasCashAtmDiff = false;
        $nominal = 0.0;
        $explicitCritical = !empty($payload['is_critical']) && in_array($payload['is_critical'], ['Ya', '1', 1, true, 'true'], true);

        switch ($tab) {
            case 'kas':
            case 'khasanah':
                $selisih = (float)($payload['selisih_kas_khasanah'] ?? 0);
                if (abs($selisih) >= 0.01) {
                    $hasFinding = true;
                    $hasCashAtmDiff = true;
                    $nominal = abs($selisih);
                    $failedCount = 1;
                }
                break;

            case 'atm':
            case 'cash_atm':
                $machines = $payload['atm_machines'] ?? [];
                if (is_array($machines)) {
                    foreach ($machines as $mach) {
                        $selisihAtm = (float)($mach['selisih'] ?? 0);
                        if (abs($selisihAtm) >= 0.01) {
                            $hasFinding = true;
                            $hasCashAtmDiff = true;
                            $failedCount++;
                            $nominal += abs($selisihAtm);
                        }
                    }
                }
                break;

            case 'kartu':
            case 'kartu_atm':
                $stockRows = $payload['kartu_atm_stock'] ?? [];
                if (is_array($stockRows)) {
                    foreach ($stockRows as $row) {
                        $selPers = (float)($row['selisih_persediaan'] ?? 0);
                        $selPin = (int)($row['selisih_pin_mailer'] ?? 0);
                        if (abs($selPers) >= 0.01 || $selPin !== 0) {
                            $hasFinding = true;
                            $hasWarkatCardDiff = true;
                            $failedCount++;
                            $nominal += abs($selPers);
                        }
                    }
                }
                $ctrlRows = $payload['kartu_atm_controls'] ?? [];
                if (is_array($ctrlRows)) {
                    foreach ($ctrlRows as $cRow) {
                        if (($cRow['jawaban_kontrol'] ?? 'Ya') === 'Tidak') {
                            $hasFinding = true;
                            $failedCount++;
                            // Cek indikasi dual control kunci lemari
                            if (stripos($cRow['jenis_kartu'] ?? '', 'dual control') !== false || stripos($cRow['jenis_kartu'] ?? '', 'kunci') !== false) {
                                $hasCriticalSecurity = true;
                            }
                        }
                    }
                }
                break;

            case 'stok':
            case 'cekbg':
                $stokDetails = $payload['stok_details'] ?? [];
                if (is_array($stokDetails)) {
                    foreach ($stokDetails as $sRow) {
                        $isCtrl = (($sRow['sub_area'] ?? '') === 'Kontrol');
                        if ($isCtrl) {
                            if (($sRow['status_uji'] ?? '') === 'Tidak') {
                                $hasFinding = true;
                                $failedCount++;
                            }
                        } else {
                            $selisih = isset($sRow['selisih']) ? (float)$sRow['selisih'] : ((float)($sRow['saldo_sistem'] ?? 0) - (float)($sRow['hasil_fisik'] ?? 0));
                            if (abs($selisih) >= 0.01 || ($sRow['status_uji'] ?? '') === 'Exception') {
                                $hasFinding = true;
                                $hasWarkatCardDiff = true;
                                $failedCount++;
                            }
                        }
                    }
                }
                break;

            case 'cetakan':
            case 'bct':
                $bctDetails = $payload['barang_cetakan_details'] ?? [];
                if (is_array($bctDetails)) {
                    foreach ($bctDetails as $bRow) {
                        $selisih = isset($bRow['selisih']) ? (float)$bRow['selisih'] : ((float)($bRow['stok_sistem'] ?? 0) - (float)($bRow['hasil_fisik'] ?? 0));
                        if (abs($selisih) >= 0.01 || ($bRow['status_uji'] ?? '') === 'Exception') {
                            $hasFinding = true;
                            $failedCount++;
                            if (preg_match('/(CEK|BILYET|GIRO|WARKAT)/i', $bRow['nama_barang'] ?? '')) {
                                $hasWarkatCardDiff = true;
                            }
                        }
                    }
                }
                $bctControls = $payload['barang_cetakan_controls'] ?? [];
                if (is_array($bctControls)) {
                    foreach ($bctControls as $bcRow) {
                        if (($bcRow['jawaban'] ?? 'Ya') === 'Tidak') {
                            $hasFinding = true;
                            $failedCount++;
                        }
                    }
                }
                break;

            case 'observasi':
            case 'obs':
                $obsDetails = $payload['obs_details'] ?? [];
                if (is_array($obsDetails)) {
                    foreach ($obsDetails as $item) {
                        $res = $item['hasil_observasi'] ?? '';
                        if (in_array($res, ['Tidak Sesuai', 'Tidak', 'Kurang Baik'], true)) {
                            $hasFinding = true;
                            $failedCount++;
                            $area = ($item['area_observasi'] ?? '') . ' ' . ($item['kriteria_diharapkan'] ?? '');
                            if (preg_match('/(DUAL\s*CONTROL|KHASANAH|BRANKAS|KUNCI|CCTV|ALARM)/i', $area)) {
                                $hasCriticalSecurity = true;
                            }
                        }
                    }
                }
                break;
        }

        // 1. Impact
        $impact = $this->calculateImpact(
            $nominal,
            $failedCount,
            $hasCriticalSecurity,
            $hasWarkatCardDiff,
            $hasCashAtmDiff,
            $hasFinding
        );

        // 2. Likelihood
        $historyCount = $this->countHistory($unitCode, $tab, $currentKkaId, 'physical');
        $likInfo = $this->calculateLikelihoodFromHistoryCount($historyCount, $hasFinding);
        $likelihood = $likInfo['likelihood'];

        // 3. Critical
        $isCritical = $this->calculateCritical(
            $impact,
            $hasCashAtmDiff,
            $hasCriticalSecurity,
            $hasWarkatCardDiff,
            $explicitCritical
        );

        // 4. Risk Score & Level
        $riskInfo = $this->determineRisk($impact, $likelihood, $isCritical, $hasFinding);

        return [
            'has_finding'          => $hasFinding,
            'impact'               => $impact,
            'impact_label'         => $this->getImpactLabel($impact, $hasFinding),
            'likelihood'           => $likelihood,
            'likelihood_label'     => $likInfo['label'],
            'likelihood_badge'     => $likInfo['badge'],
            'likelihood_note'      => $likInfo['note'],
            'history_count'        => $historyCount,
            'is_critical'          => $isCritical,
            'critical_label'       => $isCritical ? 'Ya' : 'Tidak',
            'risk_score'           => $riskInfo['score'],
            'risk_level'           => $riskInfo['level'],
            'perlu_onsite'         => $riskInfo['perlu_onsite'],
            'failed_item_count'    => $failedCount,
        ];
    }

    /**
     * Label representatif untuk Impact
     */
    public function getImpactLabel(int $impact, bool $hasFinding = true): string
    {
        if (!$hasFinding) {
            return '1 — Sangat Rendah (Nihil Temuan)';
        }

        return match ($impact) {
            5 => '5 — Sangat Material / Pelanggaran Keamanan Kritikal',
            4 => '4 — Dampak Besar / Selisih Warkat & Kartu',
            3 => '3 — Dampak Sedang',
            2 => '2 — Dampak Administratif Ringan',
            default => '1 — Sangat Rendah',
        };
    }
}
