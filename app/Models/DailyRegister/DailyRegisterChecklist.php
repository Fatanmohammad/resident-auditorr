<?php

namespace App\Models\DailyRegister;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyRegisterChecklist extends Model
{
    protected $guarded = ['id'];

    public function answers(): HasMany
    {
        return $this->hasMany(DailyRegisterChecklistAnswer::class, 'checklist_id');
    }
}
