<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'unit_code', 'unit_name', 'unit_type', 'parent_office', 'region',
        'is_active', 'base_ra_unit', 'distance_from_parent_km',
        'transaction_volume_category', 'auto_description',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function rawMetrics() { return $this->hasMany(RawMetric::class); }
    public function riskScorings() { return $this->hasMany(RiskScoring::class); }
    public function criticalOverrides() { return $this->hasMany(CriticalOverride::class); }
    public function raAssignment() { return $this->hasOne(RaAssignment::class); }
    public function coverageSetup() { return $this->hasOne(CoverageSetup::class); }
    public function onsiteFrequency() { return $this->hasOne(OnsiteFrequency::class); }

    public function latestRiskScoring(?int $period = null)
    {
        $period = $period ?? date('Y');
        return $this->riskScorings()->where('period', $period)->first();
    }

    public function hasActiveOverride(): bool
    {
        return $this->criticalOverrides()->where('status', 'Aktif')->exists();
    }
}
