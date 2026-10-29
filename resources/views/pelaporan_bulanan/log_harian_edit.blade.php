@extends('layouts.app')
@section('title', 'Edit & Review Log Harian RA - SOP 08')

@section('content')
<div class="container-fluid px-3 py-3">
    {{-- Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">SOP 08</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.log-harian') }}" class="text-decoration-none">Log Harian RA</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $log->log_id }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-pencil-square text-success"></i> 
                {{ $isRa ? 'Perbarui Log Aktivitas Harian' : 'Evaluasi & Review Mutu Log Harian' }}
                <span class="badge bg-success-subtle text-success border border-success-subtle fs-6 fw-semibold">{{ $log->log_id }}</span>
            </h4>
            <p class="text-muted small mb-0 mt-1">
                {{ $isRa ? 'Perbarui detail pelaksanaan tugas atau status kehadiran.' : 'Tinjau kualitas pelaksanaan tugas harian RA dan berikan penilaian mutu kerja.' }}
            </p>
        </div>
        <div>
            <a href="{{ route('pelaporan-bulanan.log-harian', ['periode' => \Carbon\Carbon::parse($log->tanggal)->format('Y-m')]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Log Harian
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger shadow-sm">
            <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terjadi Kesalahan Input:</h6>
            <ul class="mb-0 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-dark text-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-journal-text me-2"></i> Log ID: {{ $log->log_id }}
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark font-monospace">{{ $log->nama_unit }}</span>
                        @if($log->status_hari === 'KERJA')
                            <span class="badge bg-success">KERJA</span>
                        @else
                            <span class="badge bg-warning text-dark">{{ $log->status_hari }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pelaporan-bulanan.log-harian.update', $log->id) }}">
                        @csrf
                        @method('PUT')

                        {{-- Section 1: Data Identitas Log --}}
                        <div class="bg-light p-3 rounded-3 mb-3 border">
                            <h6 class="fw-bold text-success mb-3">
                                <i class="bi bi-person-badge me-1"></i> 1. Tanggal & Resident Auditor
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Tanggal Pelaksanaan (Kolom B) <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', $log->tanggal->format('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Resident Auditor (Kolom C & D)</label>
                                    <input type="text" class="form-control bg-light" value="{{ $log->ra_id }} - {{ $log->nama_ra }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Kantor Cabang (Kolom E & F)</label>
                                    <input type="text" class="form-control bg-light" value="{{ $log->kode_unit }} - {{ $log->nama_unit }}" readonly>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Substansi Aktivitas --}}
                        <div class="bg-light p-3 rounded-3 mb-3 border">
                            <h6 class="fw-bold text-success mb-3">
                                <i class="bi bi-activity me-1"></i> 2. Aktivitas & Status Kehadiran
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Status Hari (Kolom G) <span class="text-danger">*</span></label>
                                    <select name="status_hari" class="form-select fw-bold" required>
                                        @foreach($statusHariList as $sh)
                                            <option value="{{ $sh }}" {{ old('status_hari', $log->status_hari) == $sh ? 'selected' : '' }}>{{ $sh }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Sumber SOP (Kolom H)</label>
                                    <select name="sumber_sop" class="form-select">
                                        <option value="">-- Tanpa SOP --</option>
                                        @foreach($sumberSopList as $ss)
                                            <option value="{{ $ss }}" {{ old('sumber_sop', $log->sumber_sop) == $ss ? 'selected' : '' }}>{{ $ss }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Area Risiko (Kolom J)</label>
                                    <select name="area" class="form-select">
                                        @foreach($areaList as $ar)
                                            <option value="{{ $ar }}" {{ old('area', $log->area) == $ar ? 'selected' : '' }}>{{ $ar }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-muted">Jenis Aktivitas (Kolom I) <span class="text-danger">*</span></label>
                                    <textarea name="jenis_aktivitas" rows="3" class="form-control" required>{{ old('jenis_aktivitas', $log->jenis_aktivitas) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Output / Referensi WP (Kolom K)</label>
                                    <input type="text" name="output_referensi_wp" class="form-control" value="{{ old('output_referensi_wp', $log->output_referensi_wp) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted">Status Penyelesaian (Kolom L)</label>
                                    <select name="status_penyelesaian" class="form-select fw-bold">
                                        <option value="">-</option>
                                        @foreach($statusPenyelesaianList as $sp)
                                            <option value="{{ $sp }}" {{ old('status_penyelesaian', $log->status_penyelesaian) == $sp ? 'selected' : '' }}>{{ $sp }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted">Jumlah Item / Sampel (Kolom P)</label>
                                    <input type="number" name="jumlah_item" class="form-control" value="{{ old('jumlah_item', $log->jumlah_item) }}" min="0">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-muted">Catatan Tambahan (Kolom Q)</label>
                                    <input type="text" name="catatan" class="form-control" value="{{ old('catatan', $log->catatan) }}">
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Review Mutu oleh Reviewer (Kolom M s.d. O) --}}
                        <div class="bg-light p-3 rounded-3 mb-4 border">
                            <h6 class="fw-bold text-primary mb-3 d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-shield-check me-1"></i> 3. Penilaian Mutu oleh Reviewer (Kolom M s.d. O)</span>
                                @if(!$isRa)
                                    <span class="badge bg-primary">Khusus Reviewer</span>
                                @endif
                            </h6>

                            @if($isRa)
                            {{-- Tampilan Read-Only untuk RA --}}
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="small text-muted fw-bold d-block">Hasil Review (Kolom M)</label>
                                    @if($log->hasil_review === 'MEMADAI')
                                        <span class="badge bg-success fs-6"><i class="bi bi-check-circle-fill me-1"></i>MEMADAI</span>
                                    @elseif($log->hasil_review === 'PERLU PERBAIKAN')
                                        <span class="badge bg-warning text-dark fs-6"><i class="bi bi-exclamation-triangle-fill me-1"></i>PERLU PERBAIKAN</span>
                                    @elseif($log->hasil_review === 'TIDAK MEMADAI')
                                        <span class="badge bg-danger fs-6"><i class="bi bi-x-circle-fill me-1"></i>TIDAK MEMADAI</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border fs-6">BELUM DIREVIEW</span>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <label class="small text-muted fw-bold d-block">Reviewer (Kolom N)</label>
                                    <span class="text-dark">{{ $log->reviewer ?: 'Belum dinilai' }}</span>
                                </div>
                                <div class="col-md-4">
                                    <label class="small text-muted fw-bold d-block">Tanggal Review (Kolom O)</label>
                                    <span class="text-dark">{{ $log->tanggal_review ? $log->tanggal_review->format('d/m/Y') : '-' }}</span>
                                </div>
                            </div>
                            @else
                            {{-- Form Interaktif untuk Reviewer / Admin --}}
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-dark">Hasil Review (Kolom M) <span class="text-danger">*</span></label>
                                    <select name="hasil_review" class="form-select fw-bold">
                                        @foreach($hasilReviewList as $hr)
                                            <option value="{{ $hr }}" {{ old('hasil_review', $log->hasil_review) == $hr ? 'selected' : '' }}>{{ $hr }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-dark">Nama Reviewer (Kolom N)</label>
                                    <input type="text" name="reviewer" class="form-control" value="{{ old('reviewer', $log->reviewer ?: (auth()->user()->name ?? 'Reviewer SKAI')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-dark">Tanggal Review (Kolom O)</label>
                                    <input type="date" name="tanggal_review" class="form-control" value="{{ old('tanggal_review', $log->tanggal_review?->format('Y-m-d') ?: now()->format('Y-m-d')) }}">
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <a href="{{ route('pelaporan-bulanan.log-harian', ['periode' => \Carbon\Carbon::parse($log->tanggal)->format('Y-m')]) }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
