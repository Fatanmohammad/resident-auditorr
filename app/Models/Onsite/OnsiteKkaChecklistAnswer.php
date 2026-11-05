<?php

namespace App\Models\Onsite;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DailyRegister\DailyRegisterChecklist;

class OnsiteKkaChecklistAnswer extends Model
{
    use HasFactory;

    protected $table = 'onsite_kka_checklist_answers';

    protected $fillable = [
        'onsite_kka_finding_id',
        'checklist_id',
        'jawaban',
        'keterangan',
    ];

    public function finding()
    {
        return $this->belongsTo(OnsiteKkaFinding::class, 'onsite_kka_finding_id');
    }

    public function checklist()
    {
        return $this->belongsTo(DailyRegisterChecklist::class, 'checklist_id');
    }
}

