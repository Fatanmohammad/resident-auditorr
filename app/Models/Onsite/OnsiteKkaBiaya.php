<?php

namespace App\Models\Onsite;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnsiteKkaBiaya extends Model
{
    use HasFactory;

    protected $table = 'onsite_kka_biayas';

    protected $guarded = ['id'];
}