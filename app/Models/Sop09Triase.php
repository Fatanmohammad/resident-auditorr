<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop09Triase extends Model
{
    use HasFactory;

    protected $table = 'sop09_triases';
    protected $guarded = ['id'];
}