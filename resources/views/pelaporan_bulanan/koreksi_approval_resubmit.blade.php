@extends('layouts.app')
@section('title', 'Resubmit Perbaikan Laporan (WP-RA-08-G) - SOP 08')

@section('content')
<style>
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        margin-bottom: 1.5rem;
    }
    .form-label {
        font-weight: 600;
        color: #334155;
        font-size: 0.85rem;
    }
    .instruction-card {
        background: #fffbeb;
        border: 1px solid #fef3c7;
        border-left: 4px solid #f59e0b;
        border-radius: 10px;
        padding: 1.25rem;
    }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">SOP 08</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.koreksi-approval') }}" class="text-decoration-none">Koreksi & Approval</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Resubmit Perbaikan</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0" style="color: #1e3a8a;">
                <i class="bi bi-send-check me-2"></i>Resubmit Perbaikan Laporan Bulanan (WP-RA-08-G)
            </h4>
            <p class="text-muted small mb-0">
                Kirimkan kembali laporan yang telah diperbaiki beserta uraian tindak lanjut atas catatan reviewer SKAI.
            </p>
        </div>
        <div>
            <a href="{{ route('pelaporan-bulanan.koreksi-approval') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Alert Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
            <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Terdapat Data Belum Lengkap:</h6>
            <ul class="mb-0 small ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3">
        {{-- Kolom Kiri: Instruksi & Form Resubmit --}}
        <div class="col-lg-8">
            {{-- Instruksi Catatan Reviewer --}}
            <div class="instruction-card mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-exclamation-circle-fill me-2 text-warning"></i>Catatan & Permintaan Koreksi dari Reviewer
                    </h6>
                    <span class="badge bg-warning text-dark px-2 py-1">
                        Jenis: {{ $koreksi->jenis_koreksi }}
                    </span>
                </div>
                <div class="bg-white p-3 rounded border text-dark mb-3" style="min-height: 60px;">
                    {{ $koreksi->catatan_reviewer ?: 'Tidak ada catatan khusus. Silakan periksa kembali kelengkapan substansi laporan.' }}
                </div>
                <div class="row g-2 small text-muted">
                    <div class="col-md-6">
                        Tanggal Permintaan: <strong>{{ $koreksi->tanggal_permintaan ? \Carbon\Carbon::parse($koreksi->tanggal_permintaan)->format('d F Y') : '-' }}</strong>
                    </div>
                    <div class="col-md-6 text-md-end">
                        Batas Resubmit WORKDAY:
                        <strong class="text-danger">
                            {{ $koreksi->batas_koreksi ? \Carbon\Carbon::parse($koreksi->batas_koreksi)->format('d F Y') : '-' }}
                        </strong>
                    </div>
                </div>
            </div>

            {{-- Form Input Resubmit RA --}}
            <div class="glass-card">
                <form action="{{ route('pelaporan-bulanan.koreksi-approval.post-resubmit', ['reportId' => $kalender->report_id]) }}" method="POST">
                    @csrf

                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-input-cursor me-2 text-primary"></i>Pengisian Data Resubmit (Kolom G & H)
                    </h6>

                    <div class="mb-3">
                        <label for="tanggal_resubmit" class="form-label">
                            Tanggal Resubmit Laporan (Kolom G) <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="tanggal_resubmit" id="tanggal_resubmit" class="form-control"
                               value="{{ old('tanggal_resubmit', $koreksi->tanggal_resubmit ? \Carbon\Carbon::parse($koreksi->tanggal_resubmit)->format('Y-m-d') : date('Y-m-d')) }}" required>
                        <small class="text-muted" style="font-size: 0.75rem;">
                            Tanggal aktual laporan dikirimkan kembali ke Reviewer SKAI.
                        </small>
                    </div>

                    <div class="mb-4">
                        <label for="perbaikan_ra" class="form-label">
                            Uraian Perbaikan yang Dilakukan RA (Kolom H) <span class="text-danger">*</span>
                        </label>
                        <textarea name="perbaikan_ra" id="perbaikan_ra" rows="5" class="form-control"
                                  placeholder="Jelaskan secara detail bagian laporan, lampiran, KKA, atau isu yang telah disesuaikan berdasarkan catatan reviewer..." required>{{ old('perbaikan_ra', $koreksi->perbaikan_ra) }}</textarea>
                        <small class="text-muted" style="font-size: 0.75rem;">
                            Uraian ini akan diverifikasi oleh Reviewer saat pelaksanaan review ulang.
                        </small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning px-4 py-2 fw-bold text-dark shadow-sm">
                            <i class="bi bi-send-fill me-1"></i> Kirimkan Resubmit Laporan
                        </button>
                        <a href="{{ route('pelaporan-bulanan.koreksi-approval') }}" class="btn btn-outline-secondary px-3 py-2">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Kolom Kanan: Info Metadata Laporan --}}
        <div class="col-lg-4">
            <div class="glass-card">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="bi bi-info-circle me-2 text-primary"></i>Informasi Laporan
                </h6>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Report ID</span>
                        <strong class="font-monospace text-primary">{{ $kalender->report_id }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Periode</span>
                        <strong>{{ \Carbon\Carbon::parse($kalender->periode)->translatedFormat('F Y') }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Unit Kerja</span>
                        <strong>[{{ $kalender->kode_unit }}] {{ $kalender->nama_unit }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Resident Auditor</span>
                        <strong>{{ $kalender->resident_auditor }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Status Final Saat Ini</span>
                        <span class="badge bg-warning text-dark">{{ $koreksi->status_final }}</span>
                    </li>
                </ul>

                <hr>

                <div class="alert alert-light border small text-muted mb-0">
                    <i class="bi bi-lightbulb me-1 text-warning"></i>
                    Pastikan Anda juga telah memperbarui data pada tab <strong>Rekap Kegiatan</strong>, <strong>Rekap Isu & TL</strong>, dan <strong>Narasi Laporan</strong> sesuai catatan sebelum menekan tombol kirim.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
