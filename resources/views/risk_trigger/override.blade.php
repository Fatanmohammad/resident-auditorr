@extends('layouts.app')
@section('title', 'Override Keputusan - ' . $trigger->trigger_id . ' - SOP 06')

@section('content')
<style>
    .override-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .override-header {
        background: #fefce8;
        border-bottom: 1px solid #fef08a;
        padding: 1.1rem 1.5rem;
    }
    .override-body {
        padding: 1.75rem;
    }
    .param-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.85rem 1rem;
        height: 100%;
    }
    .param-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.35rem;
    }
    .param-value {
        font-size: 0.92rem;
        font-weight: 600;
        color: #1e293b;
    }
    .form-label {
        font-size: 0.84rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 0.4rem;
        display: block;
    }
    .form-control, .form-select {
        font-size: 0.9rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0.6rem 0.9rem;
        color: #1e293b;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--bs-blue);
        box-shadow: 0 0 0 3px rgba(30,75,133,0.12);
    }
</style>

@php
    $isFraud = strtoupper((string)$trigger->indikasi_fraud) === 'YA';
@endphp

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div class="page-header-title">
        <h4 class="fw-bold mb-1" style="color: var(--bs-blue-dark); font-size: 1.3rem;">
            <i class="bi bi-sliders text-warning me-2"></i>Override Keputusan Profesional RA / Reviewer
        </h4>
        <p class="text-muted mb-0" style="font-size: 0.88rem;">
            Penetapan keputusan berdasarkan pertimbangan keahlian auditor terhadap trigger <strong>{{ $trigger->trigger_id }}</strong>
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('risk-trigger.show', $trigger->id) }}" class="btn btn-outline btn-sm" style="font-size: 0.85rem; font-weight: 600; padding: 0.5rem 1rem; border-radius: 8px;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail
        </a>
    </div>
</div>

<!-- INFORMASI RINGKAS TEMUAN SAAT INI -->
<div class="card mb-4 shadow-sm border-0" style="border-radius: 12px; border: 1px solid var(--border-color); background: #ffffff;">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold" style="color: var(--bs-blue-dark); font-size: 0.95rem;">
            <i class="bi bi-info-circle text-primary me-2"></i>Informasi Temuan &amp; Kalkulasi Risiko Saat Ini
        </h6>
        <span class="badge bg-light text-dark border px-3 py-1.5" style="font-size: 0.8rem;">
            Trigger ID: <strong>{{ $trigger->trigger_id }}</strong>
        </span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="param-box">
                    <div class="param-label">Kantor Unit</div>
                    <div class="param-value">{{ $trigger->nama_unit }} ({{ $trigger->kode_unit }})</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="param-box">
                    <div class="param-label">Area Risiko</div>
                    <div class="param-value text-primary">{{ $trigger->area }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="param-box">
                    <div class="param-label">Reassessment Risiko</div>
                    <div class="param-value">
                        <span class="badge badge-info">{{ $trigger->risk_level_reassessment }} ({{ $trigger->risk_score_reassessment }})</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="param-box">
                    <div class="param-label">Keputusan Otomatis Sistem</div>
                    <div class="param-value text-primary fs-6">{{ $trigger->keputusan_otomatis }}</div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="param-box">
                    <div class="param-label">Objek / Key Pengujian</div>
                    <div class="param-value">{{ $trigger->objek_key }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="param-box">
                    <div class="param-label">Keputusan Final Saat Ini</div>
                    <div class="param-value">
                        <span class="badge badge-danger fs-6">{{ $trigger->keputusan_final }}</span>
                        @if($trigger->override_keputusan)
                            <span class="badge badge-warning ms-2">Override Aktif: {{ $trigger->override_keputusan }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="param-box">
                    <div class="param-label">Uraian Pemicu Risiko</div>
                    <div class="param-value fw-normal" style="line-height: 1.5;">
                        {{ $trigger->uraian_trigger }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FORM OVERRIDE KEPUTUSAN -->
<div class="override-card">
    <div class="override-header">
        <h5 class="fw-bold mb-1" style="color: #854d0e; font-size: 1.05rem;">
            <i class="bi bi-shield-lock-fill me-2"></i>Formulir Penetapan Keputusan Override
        </h5>
        <div class="text-muted" style="font-size: 0.84rem;">
            Setiap perubahan keputusan wajib didukung justifikasi profesional yang dapat dipertanggungjawabkan saat audit trail.
        </div>
    </div>
    <div class="override-body">
        @if($isFraud)
            <div class="alert alert-danger py-3 px-4 mb-4 shadow-sm" style="border-radius: 8px;">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-shield-slash-fill fs-3 text-danger"></i>
                    <div>
                        <div class="fw-bold fs-6">Proteksi Fraud Aktif (SOP 09 Master Rule)</div>
                        <div style="font-size: 0.86rem; margin-top: 0.2rem;">
                            Temuan ini memiliki flag <strong>Indikasi Fraud = Ya</strong>. Berdasarkan aturan kepatuhan SKAI dan SOP 06, keputusan <strong>TIDAK DAPAT DITURUNKAN</strong> ke keputusan lain selain <em>SOP 09 - PEMERIKSAAN AWAL FRAUD</em>.
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning py-3 px-4 mb-4 shadow-sm" style="border-radius: 8px; background: #fffbeb; border: 1px solid #fef3c7; color: #92400e;">
                <div class="d-flex align-items-center gap-2.5">
                    <i class="bi bi-exclamation-triangle-fill fs-4 text-warning"></i>
                    <div style="font-size: 0.88rem; line-height: 1.5;">
                        Override keputusan akan menggantikan routing otomatis sistem. Pastikan Anda telah meninjau bukti transaksi, dokumen klarifikasi, atau arahan tertulis sebelum menetapkan keputusan.
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('risk-trigger.override', $trigger->id) }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="form-label">
                    Pilih Keputusan Override <span class="text-danger">*</span>
                </label>
                <select name="override_keputusan" class="form-select" {{ $isFraud ? 'disabled' : '' }} required>
                    <option value="">-- Gunakan Keputusan Otomatis Sistem (Reset / Hapus Override) --</option>
                    @foreach($standarKeputusan as $kep)
                        <option value="{{ $kep }}" {{ (old('override_keputusan', $trigger->override_keputusan) == $kep) || ($isFraud && $kep == 'SOP 09 - PEMERIKSAAN AWAL FRAUD') ? 'selected' : '' }}>
                            {{ $kep }}
                        </option>
                    @endforeach
                </select>
                @if($isFraud)
                    <input type="hidden" name="override_keputusan" value="SOP 09 - PEMERIKSAAN AWAL FRAUD">
                @endif
                <div class="text-muted mt-2" style="font-size: 0.8rem;">
                    Pilih keputusan baru, atau pilih opsi reset untuk mengembalikan ke rekomendasi otomatis sistem ({{ $trigger->keputusan_otomatis }}).
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">
                    Alasan Profesional &amp; Dokumen Pendukung Override <span class="text-danger">*</span>
                </label>
                <textarea name="alasan_override" class="form-control" rows="3" placeholder="Tuliskan alasan mengapa keputusan ini di-override, pertimbangan profesional auditor, nomor memo/klarifikasi pendukung, atau disposisi manajemen..." required>{{ old('alasan_override', $trigger->alasan_override) }}</textarea>
                <div class="text-muted mt-2" style="font-size: 0.8rem;">
                    Alasan ini akan disimpan secara permanen pada log audit trail Kolom 29 (Kolom AC) Excel SOP 06.
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">
                    Catatan Review Supervisor / Reviewer (Kolom 37 / Kolom AK Excel)
                </label>
                <textarea name="catatan_review" class="form-control" rows="3" placeholder="Instruksi tambahan, arahan perluasan sampling, atau catatan supervisi bagi tim auditor lapangan...">{{ old('catatan_review', $trigger->catatan_review) }}</textarea>
                <div class="text-muted mt-2" style="font-size: 0.8rem;">
                    Catatan ini akan disimpan pada Kolom 37 (Kolom AK) Excel SOP 06 dan otomatis tercatat atas nama Reviewer: <strong>{{ auth()->user()->name }}</strong>.
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top">
                <a href="{{ route('risk-trigger.show', $trigger->id) }}" class="btn btn-outline" style="font-size: 0.88rem; padding: 0.6rem 1.25rem; border-radius: 8px;">
                    <i class="bi bi-x-circle me-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary" style="font-size: 0.92rem; font-weight: 700; padding: 0.65rem 1.75rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(30,75,133,0.25);">
                    <i class="bi bi-check2-circle me-1"></i> Simpan Keputusan Override
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
