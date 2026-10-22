<?php

namespace App\Models\DailyRegister;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalKka extends Model
{
    use HasFactory;
    
    protected $table = 'physical_kkas';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    
    public function cashDetails()
    {
        return $this->hasMany(PhysicalKkaCashDetail::class, 'physical_kka_id');
    }
    
    public function obsDetails()
    {
        return $this->hasMany(PhysicalKkaObsDetail::class, 'physical_kka_id');
    }
    
    public function stokDetails()
    {
        return $this->hasMany(PhysicalKkaStokDetail::class, 'physical_kka_id');
    }

    public function kartuAtmDetails()
    {
        return $this->hasMany(PhysicalKkaKartuAtmDetail::class, 'physical_kka_id');
    }

    public function barangCetakanDetails()
    {
        return $this->hasMany(PhysicalKkaBarangCetakanDetail::class, 'physical_kka_id');
    }
}
