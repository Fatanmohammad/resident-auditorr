@extends('layouts.app')
@section('title', 'Edit Kalender Laporan ' . $kalender->report_id)

@section('content')
<div class="container-fluid px-3 py-3">
    {{-- Header Modul --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">Kalender Laporan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Laporan {{ $kalender->report_id }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1" style="color: #1e3a8a;">
                <i class="bi bi-pencil-square me-2"></i>Edit Kalender Laporan: {{ $kalender->report_id }}
            </h4>
            <p class="text-muted small mb-0">
                Pengaturan parameter wajib lapor, status approval, dan catatan evaluasi berkas.
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="{{ route('pelaporan-bulanan.kalender', ['periode' => \Carbon\Carbon::parse($kalender->periode)->format('Y-m')]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kalender
            </a>
        </div>
    </div>

    {{-- Tabs Navigasi Modul --}}
    @include('pelaporan_bulanan.tabs')

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            {{-- Summary Laporan Card --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <span class="badge bg-primary font-monospace fs-6 px-3 py-1">{{ $kalender->report_id }}</span>
                            <span class="ms-2 fw-bold text-dark fs-6">{{ $kalender->nama_unit }} ({{ $kalender->kode_unit }})</span>
                        </div>
                        <span class="badge {{ $kalender->ketepatan === 'TEPAT WAKTU' ? 'bg-success' : ($kalender->ketepatan === 'TERLAMBAT' ? 'bg-danger' : 'bg-secondary') }} px-3 py-1">
                            {{ $kalender->ketepatan }}
                        </span>
                    </div>
                    <div class="row g-3 small text-muted">
                        <div class="col-sm-4">
                            <strong>Periode:</strong> {{ \Carbon\Carbon::parse($kalender->periode)->translatedFormat('F Y') }}
                        </div>
                        <div class="col-sm-4">
                            <strong>Resident Auditor:</strong> {{ $kalender->resident_auditor }}
                        </div>
                        <div class="col-sm-4">
                            <strong>Jatuh Tempo:</strong> {{ \Carbon\Carbon::parse($kalender->jatuh_tempo)->format('d/m/Y') }}
                        </div>
                        <div class="col-sm-4">
                            <strong>Tanggal Submit:</strong> 
                            <span class="{{ $kalender->tanggal_submit ? 'text-success fw-bold' : 'text-muted' }}">
                                {{ $kalender->tanggal_submit ? \Carbon\Carbon::parse($kalender->tanggal_submit)->format('d/m/Y') : 'Belum Submit' }}
                            </span>
                        </div>
                        <div class="col-sm-4">
                            <strong>Keterlambatan:</strong> {{ $kalender->hari_terlambat }} Hari
                        </div>
                        <div class="col-sm-4">
                            <strong>Status Final:</strong> <span class="badge bg-dark">{{ $kalender->status_final }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Edit --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-sliders me-2 text-primary"></i>Pengaturan Data Laporan
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pelaporan-bulanan.kalender.update', $kalender->id) }}">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Wajib Lapor</label>
                            <select name="wajib_lapor" class="form-select form-select-lg fs-6">
                                <option value="YA" {{ $kalender->wajib_lapor === 'YA' ? 'selected' : '' }}>YA (Unit Aktif Beroperasi)</option>
                                <option value="TIDAK" {{ $kalender->wajib_lapor === 'TIDAK' ? 'selected' : '' }}>TIDAK (Unit Tutup Sementara / Dikecualikan)</option>
                            </select>
                            <div class="form-text small text-muted">
                                Unit yang ditandai TIDAK wajib lapor tidak akan dihitung dalam rekapitulasi keterlambatan konsolidasi.
                            </div>
                        </div>

                        @if(auth()->user()->role === 'admin')
                        <div class="p-3 bg-light rounded-3 mb-4 border">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-shield-lock-fill me-1"></i> Pengaturan Khusus Admin / Supervisor
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Tanggal Submit (Admin Override)</label>
                                    <input type="date" name="tanggal_submit" class="form-control" value="{{ $kalender->tanggal_submit ? \Carbon\Carbon::parse($kalender->tanggal_submit)->format('Y-m-d') : '' }}">
                                    <div class="form-text small">Override manual tanggal berkas diterima jika diperlukan.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Status Final Laporan</label>
                                    <select name="status_final" class="form-select">
                                        <option value="DRAFT" {{ $kalender->status_final === 'DRAFT' ? 'selected' : '' }}>DRAFT (Penyusunan)</option>
                                        <option value="SUBMITTED" {{ $kalender->status_final === 'SUBMITTED' ? 'selected' : '' }}>SUBMITTED (Diajukan RA)</option>
                                        <option value="APPROVED/FINAL" {{ $kalender->status_final === 'APPROVED/FINAL' ? 'selected' : '' }}>APPROVED/FINAL (Disetujui SKAI)</option>
                                    </select>
                                    <div class="form-text small">Status APPROVED mengunci draf agar tidak dapat diubah RA.</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Catatan / Evaluasi Laporan</label>
                            <textarea name="catatan" rows="4" class="form-control" placeholder="Tuliskan catatan tindak lanjut, verifikasi berkas, atau rekomendasi...">{{ $kalender->catatan }}</textarea>
                            <div class="form-text small text-muted">
                                Catatan ini akan ditampilkan pada lembar rincian kalender dan lampiran konsolidasi.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('pelaporan-bulanan.kalender', ['periode' => \Carbon\Carbon::parse($kalender->periode)->format('Y-m')]) }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
