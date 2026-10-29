<?php

namespace App\Models\PelaporanBulanan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Sop08MasterRa extends Model
{
    use HasFactory;

    protected $table = 'sop08_master_ras';

    protected $fillable = [
        'ra_id',
        'user_id',
        'nama_ra',
        'kode_kc_penempatan',
        'nama_kc',
        'unit_ra',
        'status',
        'tanggal_mulai',
        'tanggal_selesai',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function logs()
    {
        return $this->hasMany(Sop08LogHarianRa::class, 'ra_id', 'ra_id');
    }

    public function masterUnit()
    {
        return $this->belongsTo(Sop08MasterUnit::class, 'kode_kc_penempatan', 'kode_unit');
    }

    public static function statusList(): array
    {
        return ['Aktif', 'Non-Aktif'];
    }
}
