<?php

namespace App\Models\PelaporanBulanan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Sop08KkaBranchStatus extends Model
{
    use HasFactory;

    protected $table = 'sop08_kka_branch_statuses';

    protected $fillable = [
        'kode_unit',
        'periode',
        'area',
        'populasi',
        'item_selesai',
        'is_nihil',
        'is_confirmed_nihil',
        'status_kepatuhan',
        'catatan',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'is_nihil' => 'boolean',
        'is_confirmed_nihil' => 'boolean',
        'confirmed_at' => 'datetime',
        'populasi' => 'integer',
        'item_selesai' => 'integer',
    ];

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
