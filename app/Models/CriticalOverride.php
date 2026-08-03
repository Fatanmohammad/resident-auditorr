<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CriticalOverride extends Model
{
    protected $fillable = [
        'unit_id', 'trigger_date', 'trigger_type',
        'trigger_description', 'status', 'approved_by', 'notes', 'created_by',
    ];

    protected $casts = ['trigger_date' => 'date'];

    public function unit()      { return $this->belongsTo(Unit::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
}
