<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OffsiteUnitSummary extends Model
{
    protected $table = 'offsite_unit_summary';

    protected $fillable = [
        'unit_id',
        'ra_id',
        'tahun',
        'bulan',
        'periode_label',
        'status_review',
        'total_area_eligible',
        'total_area_risiko',
        'total_klarifikasi',
        'total_eskalasi',
        'risiko_tertinggi',
        'terakhir_upload',
        'catatan',
    ];

    protected $casts = [
        'terakhir_upload' => 'datetime',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function ra(): BelongsTo
    {
        return $this->belongsTo(Ra::class);
    }

    /**
     * Scope untuk filter tahun dan bulan
     */
    public function scopePeriod($query, $tahun, $bulan)
    {
        return $query->where('tahun', $tahun)->where('bulan', $bulan);
    }

    /**
     * Scope untuk filter cabang
     */
    public function scopeByCabang($query, $cabang_id)
    {
        return $query->whereHas('unit', fn($q) => $q->where('cabang_id', $cabang_id));
    }

    /**
     * Tentukan apakah unit perlu di-review
     */
    public function isNeedReview(): bool
    {
        return in_array($this->status_review, ['Perlu Review', 'Dalam Review']);
    }
}
