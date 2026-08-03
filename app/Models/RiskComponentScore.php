<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskComponentScore extends Model
{
    protected $fillable = [
        'unit_id', 'period',
        'skor_riwayat_ra', 'skor_kas_teller', 'skor_cs_dpk',
        'skor_kredit', 'skor_ti_atm', 'skor_monitoring_tl',
    ];

    public function unit() { return $this->belongsTo(Unit::class); }
}
