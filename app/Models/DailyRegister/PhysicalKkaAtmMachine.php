<?php

namespace App\Models\DailyRegister;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalKkaAtmMachine extends Model
{
    use HasFactory;

    protected $table = 'physical_kka_atm_machines';
    protected $guarded = ['id'];

    public function kka()
    {
        return $this->belongsTo(PhysicalKka::class, 'physical_kka_id');
    }

    public function cassettes()
    {
        return $this->hasMany(PhysicalKkaAtmCassette::class, 'atm_machine_id');
    }

    public function klarifikasi()
    {
        return $this->morphOne(RegisterKlarifikasi::class, 'source');
    }
}
