@extends('layouts.app')
@section('title', 'Tambah Resident Auditor')

@section('content')
<div class="container-fluid px-3 py-3">
    {{-- Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">Pelaporan Bulanan</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.master-ra') }}" class="text-decoration-none">Roster RA</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah RA</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-person-plus-fill text-primary"></i> Pendaftaran Resident Auditor Baru
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Tambahkan profil penugasan Resident Auditor ke dalam Roster RA terpusat.
            </p>
        </div>
        <div>
            <a href="{{ route('pelaporan-bulanan.master-ra') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Roster RA
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
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-primary text-white py-3 px-4">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-card-checklist me-2"></i> Formulir Penempatan Resident Auditor
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pelaporan-bulanan.master-ra.store') }}">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">RA ID <span class="text-danger">*</span></label>
                                <input type="text" name="ra_id" class="form-control font-monospace fw-bold @error('ra_id') is-invalid @enderror" value="{{ old('ra_id', $autoRaId) }}" required>
                                @error('ra_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-muted">Nama Lengkap Resident Auditor <span class="text-danger">*</span></label>
                                <input type="text" name="nama_ra" class="form-control @error('nama_ra') is-invalid @enderror" value="{{ old('nama_ra') }}" placeholder="Contoh: Jilly Keshia Lambeto" required>
                                @error('nama_ra') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Tautkan Akun User Sistem (Opsional)</label>
                                <select name="user_id" class="form-select">
                                    <option value="">-- Pilih Akun User (Role RA) --</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }} ({{ $u->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Kantor Cabang Penempatan <span class="text-danger">*</span></label>
                                <select name="kode_kc_penempatan" id="kc_select" class="form-select @error('kode_kc_penempatan') is-invalid @enderror" required onchange="handleKcChange()">
                                    <option value="">-- Pilih Kantor Cabang Penempatan --</option>
                                    @foreach($masterUnits as $mu)
                                        <option value="{{ $mu->kode_unit }}" data-nama="{{ $mu->nama_unit }}" data-unitra="{{ $mu->unit_ra }}" {{ old('kode_kc_penempatan') == $mu->kode_unit ? 'selected' : '' }}>
                                            {{ $mu->kode_unit }} - {{ $mu->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kode_kc_penempatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Nama Kantor Cabang (Otomatis)</label>
                                <input type="text" name="nama_kc" id="nama_kc_display" class="form-control bg-light" readonly placeholder="Otomatis dari Master Unit">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Unit RA</label>
                                <input type="text" name="unit_ra" id="unit_ra_display" class="form-control" value="{{ old('unit_ra', 'Bagian Resident Auditor') }}">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select">
                                    @foreach($statusList as $st)
                                        <option value="{{ $st }}" {{ old('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Tanggal Mulai Penugasan</label>
                                <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', now()->format('Y-01-01')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai') }}">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Catatan</label>
                            <textarea name="catatan" rows="3" class="form-control" placeholder="Catatan SK penugasan, riwayat mutasi cabang, dll...">{{ old('catatan') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <a href="{{ route('pelaporan-bulanan.master-ra') }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                                <i class="bi bi-save me-1"></i> Simpan Resident Auditor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function handleKcChange() {
    const sel = document.getElementById('kc_select');
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
        document.getElementById('nama_kc_display').value = opt.getAttribute('data-nama') || '';
        const ura = opt.getAttribute('data-unitra');
        if (ura) {
            document.getElementById('unit_ra_display').value = ura;
        }
    } else {
        document.getElementById('nama_kc_display').value = '';
    }
}
document.addEventListener('DOMContentLoaded', handleKcChange);
</script>
@endsection
