<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop09ChainOfCustody extends Model
{
    use HasFactory;

    protected $table = 'sop09_bukti_custodies'; // Sesuaikan dengan nama tabel di migrasi
    protected $guarded = ['id'];
}