<?php

namespace App\Models\Offsite;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KkaFinding extends Model
{
    use HasFactory;

    // Mengizinkan semua kolom diisi secara massal, kecuali 'id'
    protected $guarded = ['id'];
}
