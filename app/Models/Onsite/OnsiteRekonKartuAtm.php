<?php

namespace App\Models\Onsite;

use Illuminate\Database\Eloquent\Model;

class OnsiteRekonKartuAtm extends Model
{
    protected $guarded = ['id'];
    protected $table   = 'onsite_rekon_kartu_atm';

    public function visit()       { return $this->belongsTo(OnsiteVisit::class, 'onsite_visit_id'); }
    public function items()       { return $this->hasMany(OnsiteRekonKartuAtmItem::class, 'rekon_id')->orderBy('urutan'); }
    public function persediaan()  { return $this->hasMany(OnsiteRekonKartuAtmItem::class, 'rekon_id')->where('tipe', 'persediaan')->orderBy('urutan'); }
    public function kontrol()     { return $this->hasMany(OnsiteRekonKartuAtmItem::class, 'rekon_id')->where('tipe', 'kontrol')->orderBy('urutan'); }
}
