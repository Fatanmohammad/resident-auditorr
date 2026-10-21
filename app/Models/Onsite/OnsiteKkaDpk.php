<?php

namespace App\Models\Onsite;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnsiteKkaDpk extends Model
{
    use HasFactory;

    protected $table = 'onsite_kka_dpks';

    protected $guarded = ['id'];
}