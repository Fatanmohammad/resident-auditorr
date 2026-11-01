<?php

namespace App\Models\DailyRegister;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyRegisterChecklistAnswer extends Model
{
    protected $guarded = ['id'];

    public function finding(): BelongsTo
    {
        return $this->belongsTo(DailyRegisterFinding::class, 'daily_register_finding_id');
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(DailyRegisterChecklist::class, 'checklist_id');
    }
}
