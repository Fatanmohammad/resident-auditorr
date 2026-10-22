@extends('layouts.app')
@section('title', ($isRa ? 'Input Pelaksanaan Kerja' : 'Tinjauan Pelaksanaan Kerja') . ' - ' . $pk->visit_id . ' - SOP 06')

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

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div class="page-header-title">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1" style="font-size: 0.78rem; font-weight: 700;">
                SOP 06 — FASE 2
            </span>
            <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 font-monospace" style="font-size: 0.78rem; font-weight: 600;">
                {{ $pk->visit_id }}
            </span>
            @php
                $statusBadge = match($pk->status_kesiapan) {
                    'SIAP UJI' => 'bg-success text-white',
                    'MENUNGGU DATA' => 'bg-warning text-dark',
                    'BELUM DIMINTA' => 'bg-secondary text-white',
                    default => 'bg-danger text-white'
                };
            @endphp
            <span class="badge {{ $statusBadge }} px-2 py-1">
                <i class="bi bi-circle-fill me-1" style="font-size: 0.65rem;"></i>{{ $pk->status_kesiapan }}
            </span>
            @if(!$isRa)
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 font-monospace" style="font-size: 0.76rem;">
                    <i class="bi bi-eye-fill me-1"></i> Mode Read-Only (Admin / Reviewer)
                </span>
            @else
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 font-monospace" style="font-size: 0.76rem;">
                    <i class="bi bi-pencil-fill me-1"></i> Mode Pengisian (Resident Auditor)
                </span>
            @endif
        </div>
        <h4 class="fw-bold mb-1" style="color: var(--bs-blue-dark); font-size: 1.35rem;">
            @if(!$isRa)
                <i class="bi bi-eye-fill text-primary me-2"></i>Tinjauan Pelaksanaan Kerja &amp; Kesiapan Data (PKA)
            @else
                <i class="bi bi-clipboard2-check-fill text-primary me-2"></i>Input Pelaksanaan Kerja &amp; Kesiapan Data (PKA)
            @endif
        </h4>
        <p class="text-muted mb-0" style="font-size: 0.88rem;">
            @if(!$isRa)
                Memantau tanggal permintaan &amp; penerimaan data, langkah pengujian, dan catatan kendala akses yang diisi oleh Resident Auditor (RA).
            @else
                Input tanggal permintaan &amp; penerimaan data serta pelaporan kendala akses sesuai sheet <em>Program_Kerja_Data</em>.
            @endif
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('risk-trigger.program-kerja') }}" class="btn btn-outline btn-sm" style="font-size: 0.85rem; font-weight: 600; padding: 0.5rem 1rem; border-radius: 8px;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Program Kerja
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

@if($isRa)
<form action="{{ route('risk-trigger.program-kerja.update', $pk->id) }}" method="POST">
    @csrf
@else
<div>
@endif

    <div class="row g-4">
        <!-- Panel Kiri: Ringkasan Standar Audit & Parameter -->
        <div class="col-lg-5">
            <div class="form-card">
                <div class="form-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Standar Uji &amp; Sampling (Master Parameter)
                    </h6>
                    <span class="badge bg-light text-secondary border font-monospace">Kolom A - K</span>
                </div>
                <div class="form-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="param-box">
                                <div class="param-label">Identitas Penugasan &amp; Trigger</div>
                                <div class="param-value text-primary font-monospace">{{ $pk->visit_id }}</div>
                                <div class="mt-1 d-flex gap-2">
                                    <span class="badge bg-light text-dark border">{{ $pk->trigger_id }}</span>
                                    <span class="badge bg-light text-secondary border">Unit: {{ $pk->kode_unit }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="param-box">
                                <div class="param-label">Area Risiko</div>
                                <div class="param-value text-dark">{{ $pk->area }}</div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="param-box">
                                <div class="param-label">KKA Tujuan</div>
                                <div class="param-value font-monospace text-primary" style="font-size: 0.85rem;">{{ $pk->kka_tujuan }}</div>
                            </div>
                        </div>

                        <!-- Ukuran Sampel -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="param-label mb-2"><i class="bi bi-calculator me-1"></i> Formula Ukuran Sampel Terarah SOP 06</div>
                                <div class="row text-center g-2">
                                    <div class="col-6">
                                        <div class="p-2 bg-white rounded border">
                                            <div class="text-muted" style="font-size: 0.72rem; font-weight: 700;">SAMPEL PEMICU (J)</div>
                                            <div class="fw-bold font-monospace text-dark" style="font-size: 1.3rem;">{{ $pk->sampel_pemicu }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">MAX(1, Kejadian)</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 bg-white rounded border">
                                            <div class="text-muted" style="font-size: 0.72rem; font-weight: 700;">SAMPEL PEMBANDING (K)</div>
                                            <div class="fw-bold font-monospace text-primary" style="font-size: 1.3rem;">{{ $pk->sampel_pembanding }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">CEIL(SQRT(J))</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="param-box">
                                <div class="param-label">Tujuan Pengujian (Kolom E)</div>
                                <div class="param-value" style="font-size: 0.85rem; line-height: 1.4; color: #334155;">{{ $pk->tujuan_uji }}</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="param-box">
                                <div class="param-label">Kriteria Utama &amp; Dasar Kebijakan (Kolom F)</div>
                                <div class="param-value" style="font-size: 0.84rem; line-height: 1.4; color: #475569;">{{ $pk->kriteria_utama }}</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="param-box">
                                <div class="param-label">Data / Bukti Minimum (Kolom G)</div>
                                <div class="param-value" style="font-size: 0.84rem; line-height: 1.4; color: #475569;">{{ $pk->data_bukti_minimum }}</div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="param-box">
                                <div class="param-label">Cutoff Data (Kolom M)</div>
                                <div class="param-value font-monospace">{{ $pk->cutoff_data ? $pk->cutoff_data->format('d/m/Y') : '-' }}</div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="param-box">
                                <div class="param-label">Metode Sampling (Kolom I)</div>
                                <div class="param-value" style="font-size: 0.84rem;">{{ $pk->metode_sampling }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Kanan: Form Pelaksanaan Kerja & Kesiapan Pengujian -->
        <div class="col-lg-7">
            <div class="form-card">
                <div class="form-header d-flex justify-content-between align-items-center" style="background: {{ $isRa ? '#eff6ff' : '#f8fafc' }}; border-bottom: 1px solid {{ $isRa ? '#bfdbfe' : '#e2e8f0' }};">
                    <h6 class="fw-bold mb-0 {{ $isRa ? 'text-primary' : 'text-dark' }}">
                        @if($isRa)
                            <i class="bi bi-pencil-square me-2 text-primary"></i>Input Pelaksanaan Kerja &amp; Kesiapan Pengujian
                        @else
                            <i class="bi bi-eye-fill me-2 text-secondary"></i>Tinjauan Data Pelaksanaan Lapangan (Read-Only)
                        @endif
                    </h6>
                    @if(!$isRa)
                        <span class="badge bg-secondary text-white font-monospace" style="font-size: 0.72rem;">
                            <i class="bi bi-lock-fill me-1"></i>Hanya Baca (Admin)
                        </span>
                    @endif
                </div>
                <div class="form-body">
                    @if(!$isRa)
                        {{-- Banner Info Khusus Role Admin (Read-Only) --}}
                        <div class="alert alert-secondary border-0 d-flex align-items-center gap-2 mb-4" style="border-radius: 8px; font-size: 0.85rem; background: #f1f5f9; color: #334155; border-left: 4px solid #64748b !important;">
                            <i class="bi bi-info-circle-fill fs-5 text-secondary flex-shrink-0"></i>
                            <div>
                                <strong>Mode Tinjauan (Read-Only):</strong> Halaman pelaksanaan kerja lapangan ini diisi secara mandiri oleh Resident Auditor (RA) pelaksana di unit kerja. Akun Admin / Reviewer memiliki hak baca untuk memantau progres tanpa mengubah data pengujian.
                            </div>
                        </div>
                    @else
                        {{-- Banner Info Khusus Role RA (Editable) --}}
                        <div class="alert alert-info border-0 d-flex align-items-center gap-2 mb-4" style="border-radius: 8px; font-size: 0.85rem; background: #eff6ff; color: #1e3a8a;">
                            <i class="bi bi-info-circle-fill fs-5 flex-shrink-0"></i>
                            <div>
                                <strong>Alur Kesiapan Data:</strong> Status kesiapan pengujian otomatis berstatus <strong>SIAP UJI</strong> setelah Tanggal Diterima terisi lengkap (atau untuk pemeriksaan objek fisik langsung).
                            </div>
                        </div>
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-envelope-arrow-up text-primary me-1"></i> Tanggal Permintaan Data (Kolom N)
                            </label>
                            @if($isRa)
                                <input type="date" name="tanggal_permintaan" class="form-control @error('tanggal_permintaan') is-invalid @enderror" 
                                       value="{{ old('tanggal_permintaan', $pk->tanggal_permintaan ? $pk->tanggal_permintaan->format('Y-m-d') : '') }}">
                                @error('tanggal_permintaan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @else
                                <input type="date" class="form-control bg-light" 
                                       value="{{ $pk->tanggal_permintaan ? $pk->tanggal_permintaan->format('Y-m-d') : '' }}" readonly disabled>
                            @endif
                            <div class="form-text text-muted" style="font-size: 0.78rem;">
                                Tanggal saat auditor resmi mengajukan daftar permintaan data/voucher ke unit kerja.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-box2-check text-success me-1"></i> Tanggal Diterima Data (Kolom O)
                            </label>
                            @if($isRa)
                                <input type="date" name="tanggal_diterima" class="form-control @error('tanggal_diterima') is-invalid @enderror" 
                                       value="{{ old('tanggal_diterima', $pk->tanggal_diterima ? $pk->tanggal_diterima->format('Y-m-d') : '') }}">
                                @error('tanggal_diterima')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @else
                                <input type="date" class="form-control bg-light" 
                                       value="{{ $pk->tanggal_diterima ? $pk->tanggal_diterima->format('Y-m-d') : '' }}" readonly disabled>
                            @endif
                            <div class="form-text text-muted" style="font-size: 0.78rem;">
                                Tanggal saat seluruh dokumen/data pemicu telah diserahterimakan lengkap ke auditor.
                            </div>
                        </div>
                    </div>

                    {{-- LANGKAH TAMBAHAN RA: READ-ONLY UNTUK RA KARENA DITETAPKAN OLEH ADMIN SAAT ASSIGNMENT --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0">
                                <i class="bi bi-list-check text-primary me-1"></i> Langkah Tambahan RA (Kolom L Excel)
                            </label>
                            <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">
                                <i class="bi bi-lock-fill me-1"></i> Ditetapkan Admin saat Assignment
                            </span>
                        </div>
                        <textarea rows="4" class="form-control bg-light" readonly disabled style="color: #334155; font-size: 0.86rem;" placeholder="Tidak ada instruksi langkah tambahan dari Reviewer/Admin">{{ $pk->langkah_tambahan_ra ?: 'Tidak ada instruksi langkah tambahan khusus.' }}</textarea>
                        <div class="form-text text-muted" style="font-size: 0.78rem;">
                            <i class="bi bi-shield-check me-1 text-primary"></i> Instruksi prosedur tambahan ini dibuat oleh Reviewer / Admin saat menerbitkan Assignment Visit dan bersifat panduan kerja baku bagi RA di lapangan.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-exclamation-octagon text-danger me-1"></i> Catatan Akses / Kendala Data (Kolom R Excel)
                        </label>
                        @if($isRa)
                            <textarea name="catatan_akses_data" rows="3" class="form-control @error('catatan_akses_data') is-invalid @enderror" 
                                      placeholder="Catatan jika ada data yang terlambat diserahkan, arsip tidak ditemukan, kendala otorisasi sistem, atau auditee menolak memberikan data...">{{ old('catatan_akses_data', $pk->catatan_akses_data) }}</textarea>
                            @error('catatan_akses_data')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @else
                            <textarea rows="3" class="form-control bg-light" readonly disabled 
                                      placeholder="Tidak ada kendala akses data yang dilaporkan.">{{ $pk->catatan_akses_data ?: 'Tidak ada kendala akses data yang dilaporkan.' }}</textarea>
                        @endif
                        <div class="form-text text-muted" style="font-size: 0.78rem;">
                            Catatan hambatan ini akan menjadi dasar dokumentasi perluasan scope atau eskalasi ke Reviewer / Kadiv SKAI.
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        @if($isRa)
                            <a href="{{ route('risk-trigger.program-kerja') }}" class="btn btn-outline" style="font-size: 0.88rem; padding: 0.6rem 1.25rem; border-radius: 8px;">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary fw-bold" style="font-size: 0.88rem; padding: 0.6rem 1.5rem; border-radius: 8px;">
                                <i class="bi bi-save me-1"></i> Simpan Pelaksanaan PKA
                            </button>
                        @else
                            <a href="{{ route('risk-trigger.program-kerja') }}" class="btn btn-outline-primary fw-semibold" style="font-size: 0.88rem; padding: 0.6rem 1.5rem; border-radius: 8px;">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Program Kerja
                            </a>
                            <span class="text-muted" style="font-size: 0.82rem;">
                                <i class="bi bi-eye me-1"></i> Mode Tinjauan Pengujian Aktif
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@if($isRa)
</form>
@else
</div>
@endif
@endsection
