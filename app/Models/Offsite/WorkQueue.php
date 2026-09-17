<?php

namespace App\Models\Offsite;

use Illuminate\Database\Eloquent\Model;

class WorkQueue extends Model
{
    protected $fillable = [
        'kka_finding_id', 'source_sheet', 'periode', 'kode_unit',
        'alasan_terpilih', 'metode_sampling',
        'total_populasi', 'total_sampel_diambil', 'status',
    ];

    protected $casts = [
        'periode' => 'date',
    ];

    public function kkaFinding()
    {
        return $this->belongsTo(KkaFinding::class);
    }
}