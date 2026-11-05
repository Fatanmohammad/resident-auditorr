@extends('layouts.app')

@section('title', 'Modul SOP 05 — Surprise Audit')

@section('content')
<div class="container-fluid px-4 py-3" style="max-width: 1440px; margin: 0 auto;">
    <!-- Header Navigasi -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge px-3 py-2 fs-7 fw-bold" style="background-color: #e0e7ff; color: #3730a3; border-radius: 6px;">
                    <i class="bi bi-shield-lock-fill me-1"></i> STANDAR SOP 05
                </span>
                <h3 class="mb-0 fw-bold" style="color: #0f2e5a; letter-spacing: -0.3px;">
                    Daftar Penugasan Surprise Audit
                </h3>
            </div>
            <p class="text-secondary mb-0" style="font-size: 0.95rem;">
                Register, rekapitulasi, dan kontrol pelaksanaan audit fisik mendadak pada seluruh Kantor Cabang Bank Sulteng.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
            @if(auth()->check())
                @if(auth()->user()->role === 'admin')
                    <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: #fee2e2; color: #991b1b; border: 1px solid #fecdd3; font-size: 0.85rem;">
                        <i class="bi bi-shield-lock-fill me-1"></i> Mode: Admin SKAI
                    </span>
                @else
                    <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 0.85rem;">
                        <i class="bi bi-person-workspace me-1"></i> Mode: Resident Auditor
                    </span>
                @endif
            @endif
            <a href="{{ route('surprise_audit.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" style="background: #0f2e5a; border-color: #0f2e5a; font-size: 0.92rem;">
                <i class="bi bi-plus-circle me-1"></i> Buat Penugasan Baru
            </a>
        </div>
    </div>

    <!-- Alert Sukses / Error -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4 p-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
            <div>
                <h6 class="mb-0 fw-bold text-success">Berhasil</h6>
                <div style="font-size: 0.92rem;">{{ session('success') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4 p-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
            <div>
                <h6 class="mb-0 fw-bold text-danger">Perhatian</h6>
                <div style="font-size: 0.92rem;">{{ session('error') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Kartu KPI Ringkasan Metrik (Warna Tenang, Bersih & Ramah di Mata) -->
    <div class="row g-3 mb-4">
        <!-- Total Penugasan -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary fw-bold text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">TOTAL PENUGASAN</div>
                        <h3 class="fw-bold mb-0 mt-1" style="color: #0f2e5a;">{{ $totalCount ?? $assignments->count() }}</h3>
                        <div class="text-muted small mt-1">Seluruh jadwal sidak</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 48px; height: 48px; background: #eff6ff; color: #1d4ed8; font-size: 1.4rem;">
                        <i class="bi bi-folder2-open"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Siap Dilaksanakan -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary fw-bold text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">SIAP DILAKSANAKAN</div>
                        <h3 class="fw-bold mb-0 mt-1" style="color: #1d4ed8;">{{ $siapCount ?? 0 }}</h3>
                        <div class="text-muted small mt-1">Menunggu waktu sidak</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 48px; height: 48px; background: #eff6ff; color: #2563eb; font-size: 1.4rem;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dalam Proses -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary fw-bold text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">DALAM PROSES</div>
                        <h3 class="fw-bold mb-0 mt-1" style="color: #b45309;">{{ $prosesCount ?? 0 }}</h3>
                        <div class="text-muted small mt-1">Audit fisik sedang berjalan</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 48px; height: 48px; background: #fffbeb; color: #d97706; font-size: 1.4rem;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selesai -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary fw-bold text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">AUDIT SELESAI</div>
                        <h3 class="fw-bold mb-0 mt-1 text-success">{{ $selesaiCount ?? 0 }}</h3>
                        <div class="text-muted small mt-1">KKA lengkap & diverifikasi</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 48px; height: 48px; background: #f0fdf4; color: #15803d; font-size: 1.4rem;">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Register Penugasan Surprise Audit -->
    <div class="card shadow-sm border-0 rounded-4 bg-white" style="border: 1px solid #e2e8f0 !important; overflow: hidden;">
        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background: #0f2e5a; color: #ffffff; border-top-left-radius: 16px; border-top-right-radius: 16px;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-table text-warning fs-5"></i>
                <h5 class="mb-0 fw-bold text-white" style="font-size: 1.05rem;">Register Penugasan Surprise Audit (WP-RA-05-A)</h5>
            </div>
            <span class="badge bg-white text-dark fw-bold px-3 py-1 rounded-pill" style="font-size: 0.82rem;">
                Klasifikasi: Rahasia - Need to Know
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                    <thead style="background-color: #f8fafc; color: #334155;">
                        <tr>
                            <th class="ps-4 py-3" style="font-weight: 700; width: 160px;">Visit ID</th>
                            <th class="py-3" style="font-weight: 700; width: 170px;">No. Penugasan</th>
                            <th class="py-3" style="font-weight: 700;">Kantor Cabang / Unit</th>
                            <th class="py-3" style="font-weight: 700; width: 170px;">Tanggal Rencana</th>
                            <th class="py-3" style="font-weight: 700;">Auditor (RA) &amp; Reviewer</th>
                            <th class="py-3 text-center" style="font-weight: 700; width: 150px;">Status</th>
                            <th class="py-3 text-center pe-4" style="font-weight: 700; width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $item)
                            @php
                                $statusVal = strtoupper(trim($item->status_assignment ?? $item->status ?? 'SIAP DILAKSANAKAN'));
                                if (empty($statusVal)) {
                                    $statusVal = 'SIAP DILAKSANAKAN';
                                }
                                $statusStyle = match($statusVal) {
                                    'SELESAI', 'SELESAI LENGKAP' => 'background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;',
                                    'DALAM PROSES', 'SEDANG BERJALAN' => 'background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a;',
                                    'SIAP DILAKSANAKAN' => 'background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;',
                                    default => 'background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;',
                                };
                                $statusIcon = match($statusVal) {
                                    'SELESAI', 'SELESAI LENGKAP' => 'bi-check-circle-fill',
                                    'DALAM PROSES', 'SEDANG BERJALAN' => 'bi-hourglass-split',
                                    'SIAP DILAKSANAKAN' => 'bi-clock-history',
                                    default => 'bi-info-circle-fill',
                                };
                            @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td class="ps-4 py-3">
                                    <span class="badge px-3 py-2 fw-bold" style="background-color: #f1f5f9; color: #0f2e5a; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                                        {{ $item->visit_id }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span class="fw-semibold text-secondary" style="font-size: 0.88rem;">{{ $item->nomor_assignment }}</span>
                                </td>
                                <td class="py-3">
                                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">{{ $item->nama_unit }}</div>
                                    <div class="text-secondary small mt-1"><i class="bi bi-geo-alt-fill text-primary me-1"></i>Kode: {{ $item->kode_unit }}</div>
                                </td>
                                <td class="py-3">
                                    @if($item->tanggal_rencana)
                                        <div class="fw-bold text-dark" style="font-size: 0.92rem;">
                                            {{ \Carbon\Carbon::parse($item->tanggal_rencana)->translatedFormat('d M Y') }}
                                        </div>
                                        <div class="text-secondary small mt-1">
                                            <i class="bi bi-clock me-1 text-primary"></i>{{ \Carbon\Carbon::parse($item->tanggal_rencana)->format('H:i') }} WITA
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <div class="text-dark" style="font-size: 0.92rem;">
                                        <strong>RA:</strong> {{ $item->resident_auditor }}
                                        @if(auth()->check() && (auth()->id() == $item->ra_user_id || auth()->user()->name == $item->resident_auditor))
                                            <span class="badge px-2 py-0 ms-1" style="background-color: #dcfce7; color: #15803d; font-size: 0.72rem;">
                                                <i class="bi bi-person-check-fill"></i> Tugas Anda
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-secondary small mt-1"><strong>Reviewer:</strong> {{ $item->reviewer }}</div>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge px-3 py-2 rounded-pill fw-bold" style="{{ $statusStyle }}; font-size: 0.82rem;">
                                        <i class="bi {{ $statusIcon }} me-1"></i> {{ $statusVal }}
                                    </span>
                                </td>
                                <td class="py-3 text-center pe-4">
                                    <a href="{{ route('surprise_audit.show', $item->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-bold" 
                                       style="background: #0f2e5a; border-color: #0f2e5a; font-size: 0.85rem;">
                                        <i class="bi bi-folder-check me-1"></i> Kelola KKA
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <div class="py-4">
                                        <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        <h6 class="fw-bold text-secondary mb-1">Belum ada data penugasan Surprise Audit.</h6>
                                        <p class="small text-muted mb-3">Gunakan tombol di bawah untuk membuat penugasan sidak baru.</p>
                                        <a href="{{ route('surprise_audit.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background: #0f2e5a; border-color: #0f2e5a;">
                                            <i class="bi bi-plus-circle me-1"></i> Buat Penugasan Baru Sekarang
                                        </a>
                                    </div>
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
