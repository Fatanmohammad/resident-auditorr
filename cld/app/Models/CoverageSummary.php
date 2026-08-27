<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoverageSummary extends Model
{
    protected $fillable = [
        'unit_id', 'period',
        'status_teller_kas', 'status_cs_dpk', 'status_kredit', 'status_atm',
        'status_biaya_jurnal', 'status_apu_fds', 'status_ti_event', 'status_pengaduan_aset',
        'active_area_count', 'coverage_score', 'coverage_status',
    ];

    public function unit() { return $this->belongsTo(Unit::class); }
}
