<?php

namespace App\Models\DailyRegister;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalKkaObsDetail extends Model
{
    use HasFactory;
    
    protected $table = 'physical_kka_obs_details';
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
