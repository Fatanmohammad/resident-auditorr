<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeLog extends Model
{
    protected $fillable = [
        'date', 'sheet_area', 'unit_id',
        'change_description', 'reason', 'approved_by', 'status', 'created_by',
    ];

    protected $casts = ['date' => 'date'];

    public function unit()      { return $this->belongsTo(Unit::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
}
