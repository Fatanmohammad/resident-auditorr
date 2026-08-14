<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffsiteStaging extends Model
{
    protected $table = 'offsite_staging';

    protected $fillable = [
        'wp_id', 'unit_id', 'dump_source', 'area_review',
        'no_transaksi', 'tanggal_transaksi', 'nominal', 'keterangan', 'unit_asal',
        'is_normalized', 'is_eligible', 'is_salah_unit', 'is_luar_periode',
    ];

    protected $casts = [
        'is_normalized'   => 'boolean',
        'is_eligible'     => 'boolean',
        'is_salah_unit'   => 'boolean',
        'is_luar_periode' => 'boolean',
        'tanggal_transaksi' => 'date',
    ];

    public function wp()   { return $this->belongsTo(WpOffsite::class, 'wp_id'); }
    public function unit() { return $this->belongsTo(Unit::class); }
    public function kka()  { return $this->hasOne(OffsiteKka::class, 'staging_id'); }
}
