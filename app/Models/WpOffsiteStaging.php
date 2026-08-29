<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class WpOffsiteStaging extends Model
{
    use HasFactory;

    protected $table = 'wp_offsite_stagings';

    protected $fillable = [
        'cabang_id',
        'domain_type',
        'tgl_transaksi',
        'raw_data',
        'uploaded_by',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'tgl_transaksi' => 'date',
    ];

    /**
     * Accessor Otomatis (Backend Only):
     * Menyaring array / string JSON raw_data secara otomatis menjadi 
     * teks ringkas "URAIAN (No. Rek: NO_REK)" tanpa mengubah View Blade.
     */
    protected function rawData(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Parse jika nilainya masih string JSON
                $data = is_string($value) ? json_decode($value, true) : $value;

                if (is_array($data)) {
                    // Ambil field URAIAN / DESKRIPSI dan NO_REK
                    $uraian = $data['URAIAN'] ?? $data['uraian'] ?? $data['DESKRIPSI'] ?? $data['deskripsi'] ?? '';
                    $noRek  = $data['NO_REK'] ?? $data['no_rek'] ?? $data['NO_REKENING'] ?? $data['no_rekening'] ?? '';

                    if ($uraian && $noRek) {
                        return "{$uraian} (No. Rek: {$noRek})";
                    } elseif ($uraian) {
                        return $uraian;
                    } elseif ($noRek) {
                        return "No. Rek: {$noRek}";
                    }
                }

                return $value ?? '-';
            }
        );
    }
}