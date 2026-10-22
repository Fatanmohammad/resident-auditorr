<?php

namespace App\Models\DailyRegister;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterTemuan extends Model
{
    use HasFactory;
    
    protected $table = 'register_temuans';
    protected $guarded = [];

    public function klarifikasi()
    {
        return $this->belongsTo(RegisterKlarifikasi::class, 'register_klarifikasi_id');
    }
}
