<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuleEngine extends Model
{
    protected $table = 'rule_engine';
    protected $fillable = ['rule_id', 'rule_type', 'keyword_pattern', 'area_terkait', 'description', 'aktif'];
    protected $casts = ['aktif' => 'boolean'];
}
