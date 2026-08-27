<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaAssignment extends Model
{
    protected $fillable = [
        'unit_id', 'primary_ra_id', 'backup_ra_id',
        'resident_base', 'assignment_status', 'valid_from', 'valid_to', 'notes',
    ];

    public function unit()      { return $this->belongsTo(Unit::class); }
    public function primaryRa() { return $this->belongsTo(Ra::class, 'primary_ra_id'); }
    public function backupRa()  { return $this->belongsTo(Ra::class, 'backup_ra_id'); }
}
