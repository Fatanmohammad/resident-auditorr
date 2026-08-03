<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnsiteFrequency extends Model
{
    protected $fillable = [
        'unit_id', 'period',
        'auto_frequency_label', 'auto_visits_per_year',
        'manual_override_frequency', 'final_frequency_label',
        'final_visits_per_year', 'is_resident_daily_review',
        'basis_note', 'cumulative_visits_running_total', 'visit_sequence_start',
    ];

    protected $casts = ['is_resident_daily_review' => 'boolean'];

    public function unit()           { return $this->belongsTo(Unit::class); }
    public function scheduledVisits(){ return $this->hasMany(ScheduledVisit::class, 'unit_id', 'unit_id')
        ->where('period', $this->period); }

    public static function labelToVisitsPerYear(string $label): int
    {
        return match($label) {
            'Bulanan'         => 12,
            'Triwulanan'      => 4,
            'Semesteran'      => 2,
            'Tahunan'         => 1,
            default           => 0,
        };
    }

    public static function labelToDurationDays(string $label): int
    {
        return match($label) {
            'Bulanan'    => 2,
            'Triwulanan' => 5,
            'Semesteran' => 7,
            'Tahunan'    => 12,
            default      => 0,
        };
    }
}
