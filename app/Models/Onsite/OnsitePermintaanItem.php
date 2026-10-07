<?php

namespace App\Models\Onsite;

use Illuminate\Database\Eloquent\Model;

class OnsitePermintaanItem extends Model
{
    protected $table = 'onsite_permintaan_items';
    protected $guarded = ['id'];

    public function permintaan() { return $this->belongsTo(OnsitePermintaanData::class, 'permintaan_id'); }
}
