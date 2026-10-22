<?php

namespace App\Models\DailyRegister;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalKkaBarangCetakanDetail extends Model
{
    use HasFactory;

    protected $table = 'physical_kka_barang_cetakan_details';
    protected $guarded = [];

    public function physicalKka()
    {
        return $this->belongsTo(PhysicalKka::class, 'physical_kka_id');
    }
}
