<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoverageSetup extends Model
{
    protected $fillable = [
        'unit_id', 'period',
        'teller_kas', 'cs_dpk', 'kredit', 'atm',
        'biaya_jurnal', 'apu_fds', 'ti_event', 'pengaduan_aset',
    ];

public function unit() { return $this->belongsTo(Unit::class); }

    // Default otomatis per unit_type (§4.7)
    public static function defaultsForUnitType(string $unitType): array
    {
        return [
            'kredit' => in_array($unitType, ['KC', 'KCU', 'KCP']) ? 'Ya' : 'Event',
            'atm'    => in_array($unitType, ['KC', 'KCU']) ? 'Ya' : 'Event',
        ];
    }

    /**
     * Daftar area fungsi yang RELEVAN untuk sebuah jenis unit.
     * Area yang tidak relevan tidak ikut dihitung ke coverage score.
     *
     * - Payment Point : hanya Teller/Kas
     * - KCPLK         : semua kecuali Kredit
     * - lainnya (KC, KCU, KP, KCP) : semua 8 area
     */
    public static function relevantAreas(string $unitType): array
    {
        $all = ['teller_kas', 'cs_dpk', 'kredit', 'atm', 'biaya_jurnal', 'apu_fds', 'ti_event', 'pengaduan_aset'];

        return match($unitType) {
            'Payment Point' => ['teller_kas'],
            'KCPLK'         => array_values(array_diff($all, ['kredit'])),
            default         => $all,
        };
    }
}
