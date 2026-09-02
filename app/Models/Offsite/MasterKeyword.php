<?php

namespace App\Models\Offsite;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKeyword extends Model
{
    use HasFactory;

    protected $fillable = [
        'kategori_risiko',
        'keyword'
    ];
}
