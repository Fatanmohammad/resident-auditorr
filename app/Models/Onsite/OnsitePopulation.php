<?php

namespace App\Models\Onsite;

use Illuminate\Database\Eloquent\Model;

class OnsitePopulation extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['tgl_tx' => 'date'];

    public function visit() { return $this->belongsTo(OnsiteVisit::class, 'onsite_visit_id'); }
}
