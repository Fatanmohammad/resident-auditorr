<?php

namespace App\Http\Requests\Offsite;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKkaRaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Menggunakan strtolower agar aman dari perbedaan kapitalisasi role (RA / ra)
        return auth()->check() && strtolower(auth()->user()->role) === 'ra';
    }

    public function rules(): array
    {
        return [
            'bukti_referensi'    => 'nullable|string',
            'hasil_uji'          => 'nullable|string',
            'klarifikasi_unit'   => 'nullable|string', // Ditambahkan karena ada di input form Blade
            'jenis_exception_ra' => 'nullable|string',
            'skor_dampak'        => 'nullable|integer|between:1,5',
            'skor_kemungkinan'   => 'nullable|integer|between:1,5',
            'critical_trigger'   => 'nullable|in:Ya,Tidak', // Ditambahkan karena ada di input form Blade
            'perlu_onsite'       => 'nullable|in:0,1,Ya,Tidak', // Diubah agar bisa menerima nilai 0 atau 1 dari select frontend
            'simpulan_ra'        => 'nullable|string',
            'tanggal_ditemukan'  => 'nullable|date',
            'file_bukti'         => 'nullable|file|mimes:pdf|max:5120', // Validasi file PDF maks 5MB
        ];
    }
}