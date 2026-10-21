<?php

namespace App\Models\Onsite;

use Illuminate\Database\Eloquent\Model;

class OnsiteRekonCekBg extends Model
{
    protected $guarded = ['id'];
    protected $table   = 'onsite_rekon_cek_bg';

    public function visit() { return $this->belongsTo(OnsiteVisit::class, 'onsite_visit_id'); }
    public function items() { return $this->hasMany(OnsiteRekonCekBgItem::class, 'rekon_id'); }

    public function getTotalAttribute()      { return $this->items()->count(); }
    public function getSesuaiAttribute()     { return $this->items()->where('status_review', 'Sesuai')->count(); }
    public function getTidakSesuaiAttribute(){ return $this->items()->where('status_review', 'Tidak Sesuai')->count(); }
}
