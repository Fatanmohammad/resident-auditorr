<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ra extends Model
{
    protected $fillable = ['ra_id', 'ra_name', 'base_branch', 'status', 'monthly_capacity_days'];

    public function primaryMappings() { return $this->hasMany(BranchRaMapping::class, 'primary_ra_id'); }
    public function backupMappings()  { return $this->hasMany(BranchRaMapping::class, 'backup_ra_id'); }
    public function assignments()     { return $this->hasMany(RaAssignment::class, 'primary_ra_id'); }
    public function capacities()      { return $this->hasMany(RaCapacity::class); }
}
