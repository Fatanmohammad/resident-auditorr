<?php

namespace App\Models\DailyRegister;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalKkaCashDetail extends Model
{
    use HasFactory;
    
    protected $table = 'physical_kka_cash_details';
    protected $guarded = [];

    public function physicalKka()
    {
        return $this->belongsTo(PhysicalKka::class, 'physical_kka_id');
    }
    
    public function klarifikasi()
    {
        return $this->morphOne(RegisterKlarifikasi::class, 'source');
    }
}
