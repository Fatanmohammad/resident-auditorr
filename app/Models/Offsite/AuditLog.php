<?php

namespace App\Models\Offsite;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'kode_unit', 
        'jenis_file', 
        'nama_file', 
        'status', 
        'total_low', 
        'total_moderate', 
        'total_high', 
        'pesan_error'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
