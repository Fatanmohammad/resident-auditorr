<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuleThreshold extends Model
{
    protected $table = 'rule_threshold';
    protected $fillable = ['parameter_name', 'jenis_unit', 'numeric_value', 'string_value', 'description', 'aktif'];
    protected $casts = ['numeric_value' => 'decimal:2', 'aktif' => 'boolean'];
}
