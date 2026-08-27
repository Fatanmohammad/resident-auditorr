<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CbsTransaction extends Model
{
    use HasFactory;

    protected $table = 'cbs_transactions';

    protected $fillable = [
        'tanggal_data',
        'data_unit',
        'no_referensi',
        'user_maker',
        'kode_transaksi',
        'nominal',
        'deskripsi_narasi',
        'is_processed',
    ];

    protected $casts = [
        'tanggal_data' => 'date',
        'nominal' => 'decimal:2',
        'is_processed' => 'boolean',
    ];
}