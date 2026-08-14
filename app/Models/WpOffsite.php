<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WpOffsite extends Model
{
    protected $table = 'wp_offsite';

    protected $fillable = [
        'kode_wp', 'unit_id', 'ra_id', 'periode_data',
        'tahun', 'bulan', 'status_wp', 'reviewer', 'validasi_unit',
    ];

    protected $casts = ['validasi_unit' => 'boolean'];

    public function unit()    { return $this->belongsTo(Unit::class); }
    public function ra()      { return $this->belongsTo(Ra::class); }
    public function staging() { return $this->hasMany(OffsiteStaging::class, 'wp_id'); }
    public function kka()     { return $this->hasMany(OffsiteKka::class, 'wp_id'); }
}
