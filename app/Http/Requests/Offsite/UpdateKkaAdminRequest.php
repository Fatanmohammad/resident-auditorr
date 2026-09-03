<?php

namespace App\Http\Requests\Offsite;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKkaAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya Korwas / Kabag / Admin
        return auth()->check() && in_array(auth()->user()->role, ['kabag_ra', 'admin']);
    }

    public function rules(): array
    {
        return [
            'status_klarifikasi' => 'required|in:Belum Selesai,Selesai',
            'perluasan_sampel'   => 'required|in:Ya,Tidak',
            'keputusan_onsite'   => 'nullable|in:Ya,Tidak',
            'keputusan_eskalasi' => 'nullable|in:Ya,Tidak',
            'status_review'      => 'required|in:Belum Direview,Revisi,Approved',
            'catatan_reviewer'   => 'nullable|string',
        ];
    }
}