<?php

namespace App\Models\Onsite;

use Illuminate\Database\Eloquent\Model;

class OnsiteObservasi extends Model
{
    protected $table = 'onsite_observasi';

    protected $fillable = [
        'onsite_visit_id', 'jumlah_atm', 'ra_pelaksana',
        'tanggal_observasi', 'status',
    ];

    public function visit()
    {
        return $this->belongsTo(OnsiteVisit::class, 'onsite_visit_id');
    }

    public function items()
    {
        return $this->hasMany(OnsiteObservasiItem::class)->orderBy('urut');
    }

    public function itemsGedung()
    {
        return $this->hasMany(OnsiteObservasiItem::class)->where('tipe', 'gedung')->orderBy('urut');
    }

    public function itemsAtm()
    {
        return $this->hasMany(OnsiteObservasiItem::class)->where('tipe', 'atm')->orderBy('urut');
    }

    // Statistik ringkasan
    public function getTotalAttribute()      { return $this->items()->count(); }
    public function getSesuaiAttribute()     { return $this->items()->where('hasil_observasi', 'Sesuai')->count(); }
    public function getTidakSesuaiAttribute(){ return $this->items()->where('hasil_observasi', 'Tidak Sesuai')->count(); }
    public function getExceptionAttribute()  { return $this->items()->where('hasil_observasi', 'Tidak Sesuai')->whereNotNull('risk_level')->count(); }
}
