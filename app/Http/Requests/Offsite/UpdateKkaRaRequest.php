<?php

namespace App\Http\Requests\Offsite;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKkaRaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya user dengan role RA yang boleh akses
        return auth()->check() && auth()->user()->role === 'ra';
    }

    public function rules(): array
    {
        return [
            'bukti_referensi'    => 'nullable|string',
            'hasil_uji'          => 'nullable|string',
            'jenis_exception_ra' => 'nullable|string',
            'skor_dampak'        => 'nullable|integer|between:1,5',
            'skor_kemungkinan'   => 'nullable|integer|between:1,5',
            'perlu_onsite'       => 'nullable|in:Ya,Tidak',
            'simpulan_ra'        => 'nullable|string',
            'tanggal_ditemukan'  => 'nullable|date',
        ];
    }
}