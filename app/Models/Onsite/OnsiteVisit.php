<?php

namespace App\Models\Onsite;

use Illuminate\Database\Eloquent\Model;

class OnsiteVisit extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function populations() { return $this->hasMany(OnsitePopulation::class, 'onsite_visit_id'); }
    public function kkaFindings() { return $this->hasMany(OnsiteKkaFinding::class, 'onsite_visit_id'); }
}
