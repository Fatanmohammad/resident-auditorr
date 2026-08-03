<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RiskScoring extends Model
{
    protected $fillable = [
        'unit_id', 'period', 'weighted_score',
        'initial_category', 'has_active_override',
        'final_category', 'override_reason', 'priority_rank',
    ];

    protected $casts = ['has_active_override' => 'boolean'];

    public function unit() { return $this->belongsTo(Unit::class); }

    public static function kategoridariSkor(float $skor): string
    {
        return match(true) {
            $skor <= 20  => 'Low',
            $skor <= 40  => 'Low to Moderate',
            $skor <= 60  => 'Moderate',
            $skor <= 80  => 'Moderate to High',
            default      => 'High',
        };
    }

    public static function priorityRank(string $kategori): int
    {
        return match($kategori) {
            'High'             => 1,
            'Moderate to High' => 2,
            'Moderate'         => 3,
            'Low to Moderate'  => 4,
            default            => 5,
        };
    }

    // Hitung weighted score dari component scores + bobot bidang per unit type
    public static function hitungWeightedScore(RiskComponentScore $cs, string $unitType): float
    {
        $bidangMap = [
            'riwayat_ra'   => $cs->skor_riwayat_ra,
            'kas_teller'   => $cs->skor_kas_teller,
            'cs_dpk'       => $cs->skor_cs_dpk,
            'kredit'       => $cs->skor_kredit,
            'ti_atm'       => $cs->skor_ti_atm,
            'monitoring_tl'=> $cs->skor_monitoring_tl,
        ];

        $weights = DB::table('bidang_weights')->where('unit_type', $unitType)->get()->keyBy('bidang');
        $total = 0;
        foreach ($bidangMap as $bidang => $skor) {
            $w = $weights->get($bidang);
            $total += (float)$skor * (float)($w?->weight ?? 0);
        }
        return round(min(100, $total), 2);
    }
}
