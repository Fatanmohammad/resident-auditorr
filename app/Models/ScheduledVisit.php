<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledVisit extends Model
{
    protected $fillable = [
        'unit_id', 'period', 'visit_number', 'recommended_month',
        'default_duration_days', 'auto_start_date', 'auto_end_date',
        'manual_override_start', 'manual_override_end',
        'final_start_date', 'final_end_date', 'final_duration_days',
        'status', 'basis_note', 'manual_notes',
    ];

    protected $casts = [
        'auto_start_date'       => 'date',
        'auto_end_date'         => 'date',
        'manual_override_start' => 'date',
        'manual_override_end'   => 'date',
        'final_start_date'      => 'date',
        'final_end_date'        => 'date',
    ];

    public function unit() { return $this->belongsTo(Unit::class); }
}
