<?php

namespace App\Models\Offsite;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DailyRegister\DailyRegisterChecklist;

class OffsiteKkaChecklistAnswer extends Model
{
    use HasFactory;

    protected $table = 'offsite_kka_checklist_answers';

    protected $fillable = [
        'kka_finding_id',
        'checklist_id',
        'jawaban',
        'keterangan',
    ];

    public function finding()
    {
        return $this->belongsTo(KkaFinding::class, 'kka_finding_id');
    }

    public function checklist()
    {
        return $this->belongsTo(DailyRegisterChecklist::class, 'checklist_id');
    }
}

