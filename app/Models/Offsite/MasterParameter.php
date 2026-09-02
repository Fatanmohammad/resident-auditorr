<?php

namespace App\Models\Offsite;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'kategori',
        'nilai_batas',
        'deskripsi'
    ];
}
