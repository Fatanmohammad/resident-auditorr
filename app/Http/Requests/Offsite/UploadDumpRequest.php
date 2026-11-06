<?php

namespace App\Http\Requests\Offsite;

use Illuminate\Foundation\Http\FormRequest;

class UploadDumpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'kode_unit'       => 'required|string',
            'periode'         => 'nullable|string',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jenis_file'      => 'required|string|in:DUMP_01,DUMP_02,DUMP_03,DUMP_04,DUMP_05,DUMP_06',
            'file_csv'        => 'required|file|mimes:csv,txt,xls,xlsx|max:51200', // Maksimal 50MB
        ];
    }

    public function messages(): array
    {
        return [
            'kode_unit.required'  => 'Unit / Cabang pemilik data wajib dipilih.',
            'periode.required'    => 'Periode data transaksi (Bulan & Tahun) wajib diisi.',
            'jenis_file.required' => 'Jenis file DUMP wajib dipilih.',
            'file_csv.required'   => 'File CSV DUMP wajib diunggah.',
            'file_csv.max'        => 'Ukuran file CSV maksimal 50MB.',
        ];
    }
}
