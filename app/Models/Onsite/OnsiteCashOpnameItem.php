<?php

namespace App\Models\Onsite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnsiteCashOpnameItem extends Model
{
    protected $table = 'onsite_cash_opname_items';
    protected $guarded = ['id'];

    public function cashOpname(): BelongsTo
    {
        return $this->belongsTo(OnsiteCashOpname::class, 'onsite_cash_opname_id');
    }
}