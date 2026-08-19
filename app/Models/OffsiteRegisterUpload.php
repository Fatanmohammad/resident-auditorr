<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OffsiteRegisterUpload extends Model
{
    protected $table = 'offsite_register_uploads';

    protected $fillable = [
        'unit_id',
        'tahun',
        'bulan',
        'file_name',
        'file_path',
        'total_records',
        'total_areas',
        'status',
        'error_message',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
