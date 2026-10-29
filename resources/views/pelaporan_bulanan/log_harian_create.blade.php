@extends('layouts.app')
@section('title', 'Input Log Aktivitas Harian RA')

@section('content')
<div class="container-fluid px-3 py-3">
    {{-- Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">Pelaporan Bulanan</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.log-harian') }}" class="text-decoration-none">Log Harian RA</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Input Log Baru</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-journal-plus text-success"></i> Input Log Aktivitas Harian Resident Auditor
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Catat pengujian harian, penyelesaian KKA, atau status kehadiran resmi (Cuti, Sakit, Dinas Luar, Pelatihan).
            </p>
        </div>
        <div>
            <a href="{{ route('pelaporan-bulanan.log-harian') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
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
                <div class="card-header bg-success text-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-pencil-square me-2"></i> Formulir Catatan Aktivitas Harian
                    </h5>
                    <span class="badge bg-white text-success font-monospace">{{ $autoLogId }}</span>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pelaporan-bulanan.log-harian.store') }}">
                        @csrf

                        {{-- Section 1: Tanggal & Resident Auditor --}}
                        <div class="bg-light p-3 rounded-3 mb-3 border">
                            <h6 class="fw-bold text-success mb-3">
                                <i class="bi bi-person-badge me-1"></i> 1. Tanggal & Resident Auditor Penugasan
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                                    @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold text-muted">Resident Auditor <span class="text-danger">*</span></label>
                                    @if($isRa && $myMasterRa)
                                        <input type="hidden" name="ra_id" value="{{ $myMasterRa->ra_id }}">
                                        <input type="text" class="form-control bg-white fw-bold text-primary" value="{{ $myMasterRa->ra_id }} - {{ $myMasterRa->nama_ra }} ({{ $myMasterRa->nama_kc }})" readonly>
                                    @else
                                        <select name="ra_id" class="form-select @error('ra_id') is-invalid @enderror" required>
                                            <option value="">-- Pilih Resident Auditor --</option>
                                            @foreach($masterRas as $mra)
                                                <option value="{{ $mra->ra_id }}" {{ old('ra_id') == $mra->ra_id ? 'selected' : '' }}>
                                                    {{ $mra->ra_id }} - {{ $mra->nama_ra }} ({{ $mra->kode_kc_penempatan }} - {{ $mra->nama_kc }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('ra_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Status Hari & Uraian Aktivitas --}}
                        <div class="bg-light p-3 rounded-3 mb-3 border">
                            <h6 class="fw-bold text-success mb-3">
                                <i class="bi bi-activity me-1"></i> 2. Status Kehadiran & Uraian Aktivitas
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Status Hari <span class="text-danger">*</span></label>
                                    <select name="status_hari" id="status_hari_select" class="form-select fw-bold @error('status_hari') is-invalid @enderror" required onchange="handleStatusHariChange()">
                                        @foreach($statusHariList as $sh)
                                            <option value="{{ $sh }}" {{ old('status_hari', 'KERJA') == $sh ? 'selected' : '' }}>{{ $sh }}</option>
                                        @endforeach
                                    </select>
                                    @error('status_hari') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 kerja-field">
                                    <label class="form-label small fw-bold text-muted">Sumber SOP</label>
                                    <select name="sumber_sop" class="form-select">
                                        <option value="">-- Pilih Sumber SOP --</option>
                                        @foreach($sumberSopList as $ss)
                                            <option value="{{ $ss }}" {{ old('sumber_sop') == $ss ? 'selected' : '' }}>{{ $ss }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 kerja-field">
                                    <label class="form-label small fw-bold text-muted">Area Risiko</label>
                                    <select name="area" class="form-select">
                                        @foreach($areaList as $ar)
                                            <option value="{{ $ar }}" {{ old('area', 'Teller Kas') == $ar ? 'selected' : '' }}>{{ $ar }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-muted">Jenis Aktivitas / Deskripsi <span class="text-danger">*</span></label>
                                    <textarea name="jenis_aktivitas" id="jenis_aktivitas_field" rows="3" class="form-control @error('jenis_aktivitas') is-invalid @enderror" placeholder="Contoh: Pengujian fisik kas khazanah, verifikasi register kartu ATM, tindak lanjut temuan..." required>{{ old('jenis_aktivitas') }}</textarea>
                                    @error('jenis_aktivitas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 kerja-field">
                                    <label class="form-label small fw-bold text-muted">Output / Referensi KKA</label>
                                    <input type="text" name="output_referensi_wp" class="form-control" value="{{ old('output_referensi_wp') }}" placeholder="Contoh: KKA-KAS-01, KKA-OFFSITE-03, SURAT-TUGAS-12...">
                                </div>
                                <div class="col-md-3 kerja-field">
                                    <label class="form-label small fw-bold text-muted">Status Penyelesaian</label>
                                    <select name="status_penyelesaian" class="form-select fw-bold">
                                        @foreach($statusPenyelesaianList as $sp)
                                            <option value="{{ $sp }}" {{ old('status_penyelesaian', 'COMPLETED') == $sp ? 'selected' : '' }}>{{ $sp }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 kerja-field">
                                    <label class="form-label small fw-bold text-muted">Jumlah Item / Sampel</label>
                                    <input type="number" name="jumlah_item" class="form-control" value="{{ old('jumlah_item', 1) }}" min="0">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-muted">Catatan Tambahan</label>
                                    <input type="text" name="catatan" class="form-control" value="{{ old('catatan') }}" placeholder="Catatan internal audit...">
                                </div>
                            </div>
                        </div>
                            </div>
                        </div>

                        <div class="alert alert-info py-2 px-3 small mb-4">
                            <i class="bi bi-info-circle me-1"></i> Log baru otomatis berstatus review <strong>BELUM DIREVIEW</strong>. Reviewer SKAI/Bagian RA akan melakukan penilaian mutu (Memadai / Perlu Perbaikan) yang memengaruhi skor kinerja bulanan.
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <a href="{{ route('pelaporan-bulanan.log-harian') }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success px-4 shadow-sm fw-bold">
                                <i class="bi bi-save me-1"></i> Simpan Log Harian
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function handleStatusHariChange() {
    const sh = document.getElementById('status_hari_select').value;
    const kerjaFields = document.querySelectorAll('.kerja-field');
    const actField = document.getElementById('jenis_aktivitas_field');

    if (sh !== 'KERJA') {
        kerjaFields.forEach(el => el.style.opacity = '0.5');
        if (!actField.value || actField.value.startsWith('Izin:') || actField.value === 'Aktivitas Harian Audit') {
            actField.value = 'Status: ' + sh;
        }
    } else {
        kerjaFields.forEach(el => el.style.opacity = '1');
        if (actField.value.startsWith('Status:')) {
            actField.value = '';
        }
    }
}
document.addEventListener('DOMContentLoaded', handleStatusHariChange);
</script>
@endsection
