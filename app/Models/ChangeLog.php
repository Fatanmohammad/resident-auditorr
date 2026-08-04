<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeLog extends Model
{
    protected $fillable = [
        'date', 'sheet_area', 'unit_id',
        'change_description', 'reason', 'approved_by', 'status', 'created_by',
    ];

    protected $casts = ['date' => 'date'];

public function unit()      { return $this->belongsTo(Unit::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    /**
     * Helper untuk mencatat perubahan manual ke Change Log (audit trail).
     * Dipanggil dari semua controller yang melakukan perubahan/override.
     */
    public static function record(
        string $sheetArea,
        string $changeDescription,
        ?string $reason = null,
        ?int $unitId = null,
        ?string $approvedBy = null,
        string $status = 'Implemented'
    ): self {
        return self::create([
            'date'               => now(),
            'sheet_area'         => $sheetArea,
            'change_description' => $changeDescription,
            'reason'             => $reason,
            'approved_by'        => $approvedBy ?? (auth()->user()->name ?? '-'),
            'status'             => $status,
            'unit_id'            => $unitId,
            'created_by'         => auth()->id(),
        ]);
    }
}
