<?php

namespace App\Models\Onsite;

use Illuminate\Database\Eloquent\Model;

class OnsitePermintaanData extends Model
{
    protected $table = 'onsite_permintaan_data';
    protected $guarded = ['id'];

    protected $casts = ['tanggal_surat' => 'date'];

    public function visit() { return $this->belongsTo(OnsiteVisit::class, 'onsite_visit_id'); }
    public function items() { return $this->hasMany(OnsitePermintaanItem::class, 'permintaan_id')->orderBy('urut'); }
}
