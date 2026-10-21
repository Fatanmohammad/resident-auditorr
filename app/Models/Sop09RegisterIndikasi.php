<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop09RegisterIndikasi extends Model
{
    use HasFactory;

    protected $table = 'sop09_register_indikasis';
    protected $guarded = ['id'];
}