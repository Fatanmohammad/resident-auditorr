<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamplingStrata extends Model
{
    protected $table = 'sampling_strata';
    protected $fillable = ['domain', 'strata_name', 'target_case', 'description', 'aktif'];
    protected $casts = ['target_case' => 'integer', 'aktif' => 'boolean'];
}
