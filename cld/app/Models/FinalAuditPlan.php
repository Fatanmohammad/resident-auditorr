<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinalAuditPlan extends Model
{
    protected $fillable = [
        'unit_id', 'period', 'risk_category',
        'primary_ra_id', 'backup_ra_id',
        'daily_offsite_active', 'onsite_frequency_label', 'visits_per_year',
        'is_resident_daily_review', 'risk_trigger_visit_required',
        'plan_status', 'notes',
    ];

    protected $casts = [
        'daily_offsite_active'       => 'boolean',
        'is_resident_daily_review'   => 'boolean',
        'risk_trigger_visit_required'=> 'boolean',
    ];

    public function unit()      { return $this->belongsTo(Unit::class); }
    public function primaryRa() { return $this->belongsTo(Ra::class, 'primary_ra_id'); }
    public function backupRa()  { return $this->belongsTo(Ra::class, 'backup_ra_id'); }
}
