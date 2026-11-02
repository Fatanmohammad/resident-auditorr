@extends('layouts.app')

@section('title', 'Modul SOP 05 — Surprise Audit')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-shield-lock-fill text-primary me-2"></i> Modul SOP 05 — Surprise Audit
            </h2>
            <p class="text-muted mb-0">Register, Rekapitulasi & Kontrol Pelaksanaan Audit Fisik Mendadak Bank Sulteng</p>
        </div>
        <a href="{{ route('surprise_audit.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
            <i class="bi bi-plus-circle me-1"></i> Buat Penugasan Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Kartu KPI Rekapitulasi -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 border-start border-primary border-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold text-uppercase d-block mb-1">Total Penugasan</small>
                        <h3 class="fw-bold mb-0 text-dark">{{ $totalCount ?? $assignments->count() }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="bi bi-folder2-open fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 border-start border-info border-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold text-uppercase d-block mb-1">Siap Dilaksanakan</small>
                        <h3 class="fw-bold mb-0 text-info">{{ $siapCount ?? 0 }}</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 border-start border-warning border-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold text-uppercase d-block mb-1">Dalam Proses</small>
                        <h3 class="fw-bold mb-0 text-warning">{{ $prosesCount ?? 0 }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 border-start border-success border-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold text-uppercase d-block mb-1">Selesai</small>
                        <h3 class="fw-bold mb-0 text-success">{{ $selesaiCount ?? 0 }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                        <i class="bi bi-check2-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Penugasan -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-table me-2 text-warning"></i> Register Penugasan Surprise Audit (WP-RA-05-A)</span>
            <span class="badge bg-secondary text-white fw-normal">Klasifikasi: Rahasia - Need to Know</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase small text-secondary">
                        <tr>
                            <th class="ps-3 py-3">Visit ID</th>
                            <th class="py-3">No. Surat Tugas</th>
                            <th class="py-3">Unit Kerja</th>
                            <th class="py-3">Tanggal Rencana</th>
                            <th class="py-3">Auditor (RA) & Reviewer</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-center pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $item)
                            @php
                                $statusVal = strtoupper(trim($item->status_assignment ?? $item->status ?? 'SIAP DILAKSANAKAN'));
                                if (empty($statusVal)) {
                                    $statusVal = 'SIAP DILAKSANAKAN';
                                }
                                $badgeClass = match($statusVal) {
                                    'SELESAI', 'SELESAI LENGKAP' => 'bg-success text-white',
                                    'DALAM PROSES', 'SEDANG BERJALAN' => 'bg-warning text-dark',
                                    'SIAP DILAKSANAKAN' => 'bg-primary text-white',
                                    default => 'bg-info text-dark',
                                };
                                $statusIcon = match($statusVal) {
                                    'SELESAI', 'SELESAI LENGKAP' => 'bi-check-circle-fill',
                                    'DALAM PROSES', 'SEDANG BERJALAN' => 'bi-hourglass-split',
                                    'SIAP DILAKSANAKAN' => 'bi-clock-history',
                                    default => 'bi-info-circle-fill',
                                };
                            @endphp
                            <tr>
                                <td class="ps-3 fw-bold">
                                    <span class="badge bg-dark font-monospace px-2 py-1">{{ $item->visit_id }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-secondary small">{{ $item->nomor_assignment }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->nama_unit }}</div>
                                    <small class="text-muted"><i class="bi bi-geo-alt me-1"></i>Kode: {{ $item->kode_unit }}</small>
                                </td>
                                <td>
                                    @if($item->tanggal_rencana)
                                        <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($item->tanggal_rencana)->translatedFormat('d M Y') }}</div>
                                        <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($item->tanggal_rencana)->format('H:i') }} WITA</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-dark small"><strong>RA:</strong> {{ $item->resident_auditor }}</div>
                                    <div class="text-muted small"><strong>Rev:</strong> {{ $item->reviewer }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $badgeClass }} px-3 py-2 rounded-pill shadow-sm">
                                        <i class="bi {{ $statusIcon }} me-1"></i> {{ $statusVal }}
                                    </span>
                                </td>
                                <td class="text-center pe-3">
                                    <a href="{{ route('surprise_audit.show', $item->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                        <i class="bi bi-folder-check me-1"></i> Kelola KKA
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                                    <p class="mb-2 fw-semibold">Belum ada data penugasan Surprise Audit.</p>
                                    <a href="{{ route('surprise_audit.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-plus-circle me-1"></i> Buat Penugasan Baru Sekarang
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
