<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop09SerahTerimaBukti extends Model
{
    use HasFactory;

    protected $table = 'sop09_serah_terima_buktis';
    protected $guarded = ['id'];
}