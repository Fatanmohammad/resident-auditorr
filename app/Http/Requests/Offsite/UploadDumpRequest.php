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
            'jenis_file' => 'required|string|in:DUMP_01,DUMP_02,DUMP_03,DUMP_04,DUMP_05',
            'file_csv'   => 'required|file|mimes:csv,txt,xls,xlsx|max:20480', // Maksimal 20MB
        ];
    }
}