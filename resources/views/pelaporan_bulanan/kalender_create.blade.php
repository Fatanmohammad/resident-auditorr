@extends('layouts.app')
@section('title', 'Inisiasi Kalender Laporan Bulanan')

@section('content')
<div class="container-fluid px-3 py-3">
    {{-- Header Modul --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">Kalender Laporan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Inisiasi Kalender Baru</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1" style="color: #1e3a8a;">
                <i class="bi bi-calendar-plus me-2"></i>Inisiasi Kalender Laporan Bulanan
            </h4>
            <p class="text-muted small mb-0">
                Generate kalender laporan, lembar checklist kelengkapan (12 komponen), dan struktur narasi laporan bulanan.
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="{{ route('pelaporan-bulanan.kalender', ['periode' => $periode]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kalender
            </a>
        </div>
    </div>

    {{-- Tabs Navigasi Modul --}}
    @include('pelaporan_bulanan.tabs')

    {{-- Alert Flash Message --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-3 px-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3 text-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-calendar-check fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">Form Inisiasi Kalender Periode</h5>
                            <small class="text-white-50">Mengacu pada Standar Pelaporan Bulanan Bank Sulteng</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pelaporan-bulanan.kalender.generate') }}">
                        @csrf

                        <div class="alert alert-info border-0 rounded-3 d-flex align-items-start mb-4">
                            <i class="bi bi-info-circle-fill fs-5 text-primary me-3 mt-1"></i>
                            <div class="small">
                                <strong>Ketentuan Pelaporan Bulanan:</strong>
                                <ul class="mb-0 ps-3 mt-1">
                                    <li>Jatuh tempo pelaporan otomatis dihitung pada <strong>Hari Kerja ke-5 (H+5)</strong> bulan berikutnya dengan memperhitungkan hari libur nasional & akhir pekan.</li>
                                    <li>Sistem secara otomatis membuat Checklist Laporan dengan 12 instrumen pengujian.</li>
                                    <li>Draf lembar Narasi Laporan akan siap diisi atau disinkronkan otomatis dari modul pengawasan.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">
                                Periode Pelaporan (Bulan & Tahun) <span class="text-danger">*</span>
                            </label>
                            <input type="month" name="periode" class="form-control form-control-lg fs-6" value="{{ $periode }}" required>
                            <div class="form-text small text-muted">
                                Pilih bulan yang akan dibuatkan lembar audit bulanan (misal: 2026-09 untuk periode September 2026).
                            </div>
                        </div>

                        @if(auth()->user()->role !== 'ra')
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">
                                Target Unit Kerja <span class="text-muted fw-normal">(Opsional)</span>
                            </label>
                            <select name="kode_unit" class="form-select form-select-lg fs-6">
                                <option value="">-- Seluruh 40 Unit Kerja Bank Sulteng (Konsolidasi Penuh) --</option>
                                @foreach($masterUnits as $mu)
                                    <option value="{{ $mu->kode_unit }}" {{ request('kode_unit') == $mu->kode_unit ? 'selected' : '' }}>
                                        [{{ $mu->kode_unit }}] {{ $mu->nama_unit }} ({{ $mu->jenis_unit }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small text-muted">
                                Pilih salah satu cabang tertentu jika hanya ingin menginisiasi draf satuan, atau kosongkan untuk men-generate 40 cabang sekaligus.
                            </div>
                        </div>
                        @else
                        <div class="alert alert-secondary border-0 rounded-3 mb-4 small">
                            <i class="bi bi-person-badge-fill me-2 text-primary"></i>
                            Sebagai Resident Auditor, sistem akan menginisiasi kalender untuk cabang penugasan resmi Anda.
                        </div>
                        @endif

                        <div class="p-3 bg-light rounded-3 mb-4">
                            <div class="row g-2 text-muted small">
                                <div class="col-sm-6">
                                    <i class="bi bi-clock-history me-1 text-primary"></i> <strong>Jatuh Tempo:</strong> Hari Kerja ke-5
                                </div>
                                <div class="col-sm-6">
                                    <i class="bi bi-calendar2-range me-1 text-primary"></i> <strong>Cut-off Berlaku:</strong> {{ \Carbon\Carbon::parse($params->tanggal_cutoff)->format('d M Y') }}
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <a href="{{ route('pelaporan-bulanan.kalender', ['periode' => $periode]) }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                                <i class="bi bi-lightning-charge me-1"></i> Mulai Generate Kalender
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
