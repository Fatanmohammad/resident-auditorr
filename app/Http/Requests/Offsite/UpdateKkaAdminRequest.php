<?php

namespace App\Http\Requests\Offsite;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKkaAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Hanya Admin, Kabag RA, Korwas, atau Pimsie yang diizinkan
        return auth()->check() && in_array(strtolower(auth()->user()->role), ['admin', 'kabag_ra', 'korwas', 'pimsie']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status_klarifikasi' => 'nullable|string',
            'perluasan_sampel'   => 'nullable',
            'keputusan_onsite'   => 'nullable|string',
            'keputusan_eskalasi' => 'nullable|string',
            'status_review'      => 'required|string', // Wajib diisi oleh admin
            'catatan_reviewer'   => 'required|string', // Wajib diisi oleh admin
        ];
    }
}