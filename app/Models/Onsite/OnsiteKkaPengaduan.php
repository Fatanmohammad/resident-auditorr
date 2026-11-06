<?php

namespace App\Models\Onsite;

use Illuminate\Database\Eloquent\Model;

class OnsiteKkaPengaduan extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'checklist_answers'  => 'array',
        'selected_sub_areas' => 'array',
    ];

    public function visit()
    {
        return $this->belongsTo(OnsiteVisit::class, 'onsite_visit_id');
    }

    public function population()
    {
        return $this->belongsTo(OnsitePopulation::class, 'onsite_population_id');
    }
}
