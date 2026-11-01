<?php

namespace App\Models\DailyRegister;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyRegisterFinding extends Model
{
    protected $guarded = ['id'];

    public function checklistAnswers(): HasMany
    {
        return $this->hasMany(DailyRegisterChecklistAnswer::class, 'daily_register_finding_id');
    }
}
