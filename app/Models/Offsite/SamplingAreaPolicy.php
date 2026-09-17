<?php

namespace App\Models\Offsite;

use Illuminate\Database\Eloquent\Model;

class SamplingAreaPolicy extends Model
{
    protected $fillable = [
        'source_sheet', 'metode_sampling', 'stratify_by', 'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public static function untukArea(string $sourceSheet): ?self
    {
        return self::where('source_sheet', $sourceSheet)->where('aktif', true)->first();
    }
}