<?php

namespace App\Models\DailyRegister;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterKlarifikasi extends Model
{
    use HasFactory;
    
    protected $table = 'register_klarifikasis';
    protected $guarded = [];

    // Polymorphic relation to any exception source
    public function source()
    {
        return $this->morphTo();
    }
    
    public function temuanResmi()
    {
        return $this->hasOne(RegisterTemuan::class, 'register_klarifikasi_id');
    }
}
