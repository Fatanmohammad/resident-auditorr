<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataCode extends Model
{
    protected $fillable = ['data_code', 'area', 'daily_offsite_capable', 'default_frequency', 'description'];

    public function coverageDetails() { return $this->hasMany(CoverageDetail::class); }
}
