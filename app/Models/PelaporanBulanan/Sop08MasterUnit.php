<?php

namespace App\Models\PelaporanBulanan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cabang;
use App\Models\Unit;
use App\Models\User;

class Sop08MasterUnit extends Model
{
    use HasFactory;

    protected $table = 'sop08_master_units';

    protected $fillable = [
        'kode_unit',
        'nama_unit',
        'jenis_unit',
        'kantor_induk',
        'kode_kc_induk',
        'assigned_ra',
        'unit_ra',
        'cabang_id',
        'unit_id',
        'user_ra_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function userRa()
    {
        return $this->belongsTo(User::class, 'user_ra_id');
    }
}
