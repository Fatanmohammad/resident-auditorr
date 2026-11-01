<?php

namespace App\Http\Requests\Offsite;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKkaRaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(strtolower(auth()->user()->role), ['ra', 'admin']);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('checklist_answers') && is_string($this->checklist_answers)) {
            $decoded = json_decode($this->checklist_answers, true);
            if (is_array($decoded)) {
                $this->merge(['checklist_answers' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        $hasTidak = false;
        $answers = $this->input('checklist_answers');
        if (is_array($answers)) {
            foreach ($answers as $ans) {
                if (($ans['jawaban'] ?? '') === 'Tidak') {
                    $hasTidak = true;
                    break;
                }
            }
        }

        $rules = [
            'bukti_referensi'    => 'nullable|string',
            'critical_trigger'   => 'nullable|in:Ya,Tidak',
            'perlu_onsite'       => 'nullable|string',
            'tanggal_ditemukan'  => 'nullable|date',
            'file_bukti'         => 'nullable|file|mimes:pdf|max:5120',
            'checklist_answers'  => 'nullable',
        ];

        // Validasi penguncian Tanggal Ditemukan / Diuji
        $findingId = $this->route('id');
        $finding = $findingId ? \App\Models\DailyRegister\DailyRegisterFinding::find($findingId) : null;
        if ($finding && !empty($finding->tanggal_ditemukan) && $this->filled('tanggal_ditemukan')) {
            $formattedInput = date('Y-m-d', strtotime((string)$this->input('tanggal_ditemukan')));
            $formattedFinding = date('Y-m-d', strtotime((string)$finding->tanggal_ditemukan));
            if ($formattedInput !== $formattedFinding) {
                $rules['tanggal_ditemukan'] = ['prohibited'];
            }
        }

        if ($hasTidak) {
            $rules['jenis_exception_ra'] = 'required|string';
            $rules['hasil_uji']          = 'required|string';
            $rules['klarifikasi_unit']   = 'required|string';
        } else {
            $rules['jenis_exception_ra'] = 'nullable|string';
            $rules['hasil_uji']          = 'nullable|string';
            $rules['klarifikasi_unit']   = 'nullable|string';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'jenis_exception_ra.required' => 'Jenis Exception (Versi RA) wajib diisi ketika ada item checklist yang bernilai Tidak.',
            'hasil_uji.required'          => 'Hasil Pengujian Lapangan wajib diisi ketika ada item checklist yang bernilai Tidak.',
            'klarifikasi_unit.required'   => 'Klarifikasi Unit Kerja / Cabang wajib diisi ketika ada item checklist yang bernilai Tidak.',
            'tanggal_ditemukan.prohibited'=> 'Tanggal Ditemukan / Diuji terkunci pada saat KKA pertama kali dibuka dan tidak dapat diubah manual oleh RA.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Validasi Gagal',
            'errors'  => $validator->errors()
        ], 422));
    }
}