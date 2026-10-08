<?php

namespace App\Models\Onsite;

use Illuminate\Database\Eloquent\Model;

class OnsiteKkaFinding extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'tgl_tx'            => 'date',
        'tanggal_ditemukan' => 'date',
    ];

    public function visit()      { return $this->belongsTo(OnsiteVisit::class, 'onsite_visit_id'); }
    public function population() { return $this->belongsTo(OnsitePopulation::class, 'onsite_population_id'); }
}
