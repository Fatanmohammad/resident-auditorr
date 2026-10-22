<?php

namespace App\Models\RiskTrigger;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterParameter extends Model
{
    use HasFactory;

    protected $table = 'risk_trigger_master_parameters';

    protected $guarded = ['id'];

    /**
     * Dapatkan master parameter untuk area tertentu
     */
    public static function getForArea(?string $area): ?self
    {
        if (empty($area)) {
            return null;
        }

        $param = static::where('area', $area)->first();
        if ($param) {
            return $param;
        }

        // Coba cari case-insensitive
        $param = static::whereRaw('LOWER(area) = ?', [strtolower(trim($area))])->first();
        if ($param) {
            return $param;
        }

        // Fallback default Umum/Lainnya
        return static::where('area', 'Umum/Lainnya')->first();
    }
}
