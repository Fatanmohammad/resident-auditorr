<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpOffsiteStaging extends Model
{
    use HasFactory;

    protected $table = 'wp_offsite_stagings';

    protected $fillable = [
        'cabang_id',
        'domain_type',
        'tgl_transaksi',
        'raw_data',
        'uploaded_by',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'tgl_transaksi' => 'date',
    ];
}