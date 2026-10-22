<?php

namespace App\Models\DailyRegister;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DailyTarget extends Model
{
    use HasFactory;
    
    protected $table = 'daily_targets';
    protected $guarded = [];
    
    protected $casts = [
        'tanggal_tugas' => 'date',
        'extended_due_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    
    public function unit()
    {
        return $this->belongsTo(\App\Models\Unit::class, 'kode_unit', 'unit_code');
    }
    
    // Auto-Lock Helper
    public function isExpired(): bool
    {
        $now = Carbon::now();
        
        // If there's an extended due date, use it
        if ($this->extended_due_date) {
            return $now->startOfDay()->gt($this->extended_due_date);
        }
        
        // Otherwise use H+2 logic
        $gracePeriodEnd = $this->tanggal_tugas->copy()->addDays(2);
        return $now->startOfDay()->gt($gracePeriodEnd);
    }
}
