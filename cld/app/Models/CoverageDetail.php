<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoverageDetail extends Model
{
    protected $fillable = [
        'unit_id', 'data_code_id', 'period',
        'final_coverage_mode', 'enters_sop02', 'enters_sop04',
    ];

    protected $casts = ['enters_sop02' => 'boolean', 'enters_sop04' => 'boolean'];

    public function unit()     { return $this->belongsTo(Unit::class); }
    public function dataCode() { return $this->belongsTo(DataCode::class); }
}
