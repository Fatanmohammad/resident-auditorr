<?php

namespace App\Models\DailyRegister;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalKkaAtmCassette extends Model
{
    use HasFactory;

    protected $table = 'physical_kka_atm_cassettes';
    protected $guarded = ['id'];

    public function atmMachine()
    {
        return $this->belongsTo(PhysicalKkaAtmMachine::class, 'atm_machine_id');
    }
}
