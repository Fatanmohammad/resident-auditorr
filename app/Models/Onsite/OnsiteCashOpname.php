<?php

namespace App\Models\Onsite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnsiteCashOpname extends Model
{
    protected $table = 'onsite_cash_opnames';
    protected $guarded = ['id'];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(OnsiteVisit::class, 'onsite_visit_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OnsiteCashOpnameItem::class, 'onsite_cash_opname_id');
    }
}