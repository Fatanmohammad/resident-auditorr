<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParameterAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_parameter',
        'bobot',
        'deskripsi',
    ];
}