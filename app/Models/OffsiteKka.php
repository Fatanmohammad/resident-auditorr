<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffsiteKka extends Model
{
    protected $table = 'offsite_kka';

    protected $fillable = [
        'wp_id', 'staging_id', 'unit_id', 'area_review', 'dump_source',
        'risk_level', 'status_kka', 'initial_risk_level',
        'is_escalated', 'catatan', 'kka_sheet', 'kka_status',
    ];

    protected $casts = ['is_escalated' => 'boolean'];

    public function wp()      { return $this->belongsTo(WpOffsite::class, 'wp_id'); }
    public function staging() { return $this->belongsTo(OffsiteStaging::class, 'staging_id'); }
    public function unit()    { return $this->belongsTo(Unit::class); }

    // Rank numerik untuk deteksi eskalasi (semakin tinggi = semakin berisiko)
    public static function riskRank(string $level): int
    {
        return match($level) {
            'Low'              => 1,
            'Low to Moderate'  => 2,
            'Moderate'         => 3,
            'Moderate to High' => 4,
            'High'             => 5,
            default            => 0,
        };
    }
}
