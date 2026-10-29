@extends('layouts.app')
@section('title', 'Pelaksanaan Lapangan - ' . $assignment->visit_id . ' - SOP 06')

@section('content')
<style>
    .form-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .form-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 1.1rem 1.5rem;
    }
    .form-body {
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

<!-- Header Harmonized SOP 06 -->
<div class="rtv-header">
    <div>
        <div class="rtv-breadcrumb">
            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a> &rsaquo;
            <a href="{{ route('risk-trigger.assignment') }}">Assignment &amp; Scope</a> &rsaquo;
            <span class="text-dark fw-semibold">Pelaksanaan Lapangan ({{ $assignment->visit_id }})</span>
        </div>
        <h4 class="rtv-title">
            <i class="bi bi-calendar2-check-fill text-primary"></i> Pelaksanaan Lapangan Aktual
            <span class="badge-wp">{{ $assignment->visit_id }}</span>
            <span class="badge-status {{ $assignment->status_assignment === 'SIAP DILAKSANAKAN' ? 'badge-status-success' : 'badge-status-warning' }}">
                {{ $assignment->status_assignment }}
            </span>
        </h4>
        <p class="rtv-subtitle">
            Pembaruan tanggal pelaksanaan audit lapangan aktual oleh Resident Auditor pelaksana penugasan.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('risk-trigger.assignment') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Assignment
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius: 10px;" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius: 10px;" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('risk-trigger.assignment.actual-dates', $assignment->id) }}" method="POST">
    @csrf

    <div class="row g-4">
        <!-- Panel Ringkasan Assignment -->
        <div class="col-lg-5">
            <div class="form-card">
                <div class="form-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i>Informasi Penugasan Visit
                    </h6>
                    <span class="badge bg-light text-secondary border font-monospace">Kolom A - J</span>
                </div>
                <div class="form-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="param-box">
                                <div class="param-label">Visit ID & Trigger Asal</div>
                                <div class="param-value text-primary font-monospace">{{ $assignment->visit_id }}</div>
                                <div class="mt-1">
                                    <a href="{{ route('risk-trigger.show', $assignment->register_trigger_id) }}" class="fw-semibold text-decoration-none" style="font-size: 0.82rem;" target="_blank">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>{{ $assignment->trigger_id }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="param-box">
                                <div class="param-label">Kantor Cabang / Unit</div>
                                <div class="param-value">{{ $assignment->nama_unit ?: 'Unit '.$assignment->kode_unit }}</div>
                                <small class="text-muted font-monospace">Kode Unit: {{ $assignment->kode_unit }}</small>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="param-box">
                                <div class="param-label">Area Risiko</div>
                                <div class="param-value">{{ $assignment->area }}</div>
                                <span class="badge bg-light text-primary border mt-1 font-monospace" style="font-size: 0.72rem;">{{ $assignment->kka_tujuan }}</span>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="param-box">
                                <div class="param-label">Tingkat Risiko</div>
                                <div class="param-value">
                                    <span class="badge {{ $assignment->risk_level === 'KRITIS' ? 'bg-danger' : 'bg-warning text-dark' }} px-2 py-1">
                                        {{ $assignment->risk_level }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="param-box">
                                <div class="param-label">Pelaksana & Reviewer</div>
                                <div class="param-value" style="font-size: 0.86rem;">
                                    <div><i class="bi bi-person-badge text-primary me-1"></i> RA Pelaksana: <strong>{{ $assignment->ra_pelaksana }}</strong></div>
                                    <div class="mt-1"><i class="bi bi-shield-check text-secondary me-1"></i> Reviewer: <strong>{{ $assignment->reviewer }}</strong></div>
                                    <div class="mt-1"><i class="bi bi-file-earmark-check text-muted me-1"></i> Otorisasi: <span class="badge bg-light text-dark border">{{ $assignment->referensi_otorisasi }}</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="param-box">
                                <div class="param-label">Rencana Tanggal Visit (Kolom K)</div>
                                <div class="param-value text-primary font-monospace" style="font-size: 1.1rem;">
                                    <i class="bi bi-calendar-event me-1"></i>{{ $assignment->tanggal_visit ? $assignment->tanggal_visit->format('d/m/Y') : '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Form Input Tanggal Aktual -->
        <div class="col-lg-7">
            <div class="form-card">
                <div class="form-header" style="background: #eff6ff; border-bottom: 1px solid #bfdbfe;">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-pencil-square me-2"></i>Input Tanggal Pelaksanaan Lapangan Aktual
                    </h6>
                </div>
                <div class="form-body">
                    <div class="alert alert-info border-0 d-flex align-items-center gap-2 mb-4" style="border-radius: 8px; font-size: 0.85rem; background: #eff6ff; color: #1e3a8a;">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div>
                            Pengisian tanggal aktual lapangan merupakan bukti audit bahwa auditor telah memulai dan/atau menyelesaikan pemeriksaan visit di kantor cabang terkait.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-calendar-play me-1 text-primary"></i> Mulai Aktual Lapangan (Kolom L Excel)
                        </label>
                        <input type="date" name="mulai_aktual" class="form-control @error('mulai_aktual') is-invalid @enderror" 
                               value="{{ old('mulai_aktual', $assignment->mulai_aktual ? $assignment->mulai_aktual->format('Y-m-d') : '') }}">
                        @error('mulai_aktual')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted" style="font-size: 0.78rem;">
                            Tanggal saat auditor mulai terjun langsung melaksanakan audit investigasi / peninjauan fisik di unit kerja.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-calendar-check me-1 text-success"></i> Selesai Aktual Lapangan (Kolom M Excel)
                        </label>
                        <input type="date" name="selesai_aktual" class="form-control @error('selesai_aktual') is-invalid @enderror" 
                               value="{{ old('selesai_aktual', $assignment->selesai_aktual ? $assignment->selesai_aktual->format('Y-m-d') : '') }}">
                        @error('selesai_aktual')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted" style="font-size: 0.78rem;">
                            Tanggal saat seluruh prosedur audit lapangan selesai dilaksanakan dan klarifikasi auditee telah ditutup. Tanggal selesai harus sama atau setelah tanggal mulai.
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border mb-4" style="font-size: 0.82rem;">
                        <div class="fw-bold text-dark mb-1"><i class="bi bi-file-earmark-spreadsheet text-primary me-1"></i> Kertas Kerja Audit (KKA):</div>
                        <div class="text-muted">
                            Penugasan ini langsung terhubung ke KKA Pengujian Lapangan: 
                            <a href="{{ route('risk-trigger.kka.index', ['search' => $assignment->visit_id]) }}" class="fw-bold text-primary font-monospace ms-1" target="_blank">
                                Buka KKA {{ $assignment->visit_id }} <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="{{ route('risk-trigger.assignment') }}" class="btn btn-outline" style="font-size: 0.88rem; padding: 0.6rem 1.25rem; border-radius: 8px;">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary fw-bold" style="font-size: 0.88rem; padding: 0.6rem 1.5rem; border-radius: 8px;">
                            <i class="bi bi-save me-1"></i> Simpan Tanggal Aktual
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
