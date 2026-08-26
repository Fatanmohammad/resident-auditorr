<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasUnitScope
{
    /**
     * Scope query untuk membatasi data berdasarkan unit naungan RA
     */
    public function scopeForCurrentUser(Builder $query)
    {
        $user = auth()->user();

        if (!$user) {
            return $query;
        }

        // Ambil cabang yang dapat diakses oleh user ini
        $accessibleCabangIds = $user->cabangIdYangDapatDiakses();

        // Jika bernilai null (Role selain RA: admin, kabag_ra, dsb) -> Akses Semua
        if ($accessibleCabangIds === null) {
            return $query;
        }

        // Jika RA -> Filter query berdasarkan kolom kode_unit / cabang_id yang dimiliki model
        // Catatan: Jika di tabel WpOffsite / StagingOffsite memakai `kode_unit`, sesuaikan kolomnya.
        // Jika menyimpan ID cabang, gunakan 'cabang_id' atau 'kode_unit'.
        return $query->whereIn('kode_unit', $accessibleCabangIds);
    }
}