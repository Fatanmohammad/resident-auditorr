<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchRaMapping extends Model
{
    protected $table = 'branch_ra_mappings';
    protected $fillable = ['branch_name', 'primary_ra_id', 'backup_ra_id'];

    public function primaryRa() { return $this->belongsTo(Ra::class, 'primary_ra_id'); }
    public function backupRa()  { return $this->belongsTo(Ra::class, 'backup_ra_id'); }
}
