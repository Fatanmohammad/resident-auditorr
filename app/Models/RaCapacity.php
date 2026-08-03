<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaCapacity extends Model
{
    protected $fillable = [
        'ra_id', 'period', 'month', 'effective_working_days',
        'daily_offsite_unit_count', 'estimated_offsite_days',
        'scheduled_visit_count', 'scheduled_visit_days',
        'total_workload_days', 'utilization', 'capacity_status', 'recommendation_note',
    ];

    public function ra() { return $this->belongsTo(Ra::class); }
}
