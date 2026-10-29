@extends('layouts.app')
@section('title', 'Review Laporan Bulanan')

@section('content')
<style>
    .filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.15rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        height: 100%;
    }
    .kpi-title {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
    }
    .kpi-val {
        font-size: 1.6rem;
        font-weight: 800;
        line-height: 1.2;
        margin-top: 0.35rem;
    }
    .table thead th {
        background: #f8fafc;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        color: #475569;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
        vertical-align: middle;
        padding: 0.75rem 0.85rem;
    }
    .table tbody td {
        font-size: 0.875rem;
        vertical-align: middle;
        padding: 0.75rem 0.85rem;
        border-color: #f1f5f9;
        color: #334155;
    }
    .badge-memadai { background: #dcfce7; color: #166534; font-weight: 600; }
    .badge-perbaikan { background: #fef9c3; color: #854d0e; font-weight: 600; }
    .badge-tidak { background: #fee2e2; color: #991b1b; font-weight: 600; }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Header Modul --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a8a;">
                <i class="bi bi-clipboard-check me-2"></i>Review Laporan Bulanan
            </h4>
            <p class="text-muted small mb-0">
                Evaluasi 6 Aspek Substansi, Perhitungan Skor Review Berbobot (40% Lengkap + 60% Substansi), dan Penetapan Status Review Final
            </p>
        </div>
    </div>

    {{-- Tabs Navigasi Modul --}}
    @include('pelaporan_bulanan.tabs')

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- KPI Cards --}}
    @php
        $totalReview = $reviewList->count();
        $memadaiCount = $reviewList->where('status_review_final', 'MEMADAI')->count();
        $minorCount = $reviewList->where('status_review_final', 'KOREKSI MINOR')->count();
        $substansiCount = $reviewList->where('status_review_final', 'KOREKSI SUBSTANSI')->count();
        $avgSkor = $totalReview > 0 ? $reviewList->avg('skor_review') : 0;
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-primary">
                <div class="kpi-title">Total Review</div>
                <div class="kpi-val text-primary">{{ $totalReview }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-success">
                <div class="kpi-title">Memadai</div>
                <div class="kpi-val text-success">{{ $memadaiCount }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-warning">
                <div class="kpi-title">Koreksi Minor</div>
                <div class="kpi-val text-warning">{{ $minorCount }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-danger">
                <div class="kpi-title">Koreksi Substansi</div>
                <div class="kpi-val text-danger">{{ $substansiCount }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-info">
                <div class="kpi-title">Rata-Rata Skor</div>
                <div class="kpi-val text-info">{{ number_format($avgSkor, 1) }}<small class="fs-6 fw-normal text-muted">/100</small></div>
            </div>
        </div>
    </div>

    {{-- Filter Periode & Search --}}
    <div class="filter-card">
        <form action="{{ route('pelaporan-bulanan.review-laporan') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Periode Laporan</label>
                <input type="month" name="periode" class="form-control form-control-sm" value="{{ $periode }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Status Review</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="MEMADAI" {{ request('status') === 'MEMADAI' ? 'selected' : '' }}>MEMADAI (>=85)</option>
                    <option value="KOREKSI MINOR" {{ request('status') === 'KOREKSI MINOR' ? 'selected' : '' }}>KOREKSI MINOR (70-84)</option>
                    <option value="KOREKSI SUBSTANSI" {{ request('status') === 'KOREKSI SUBSTANSI' ? 'selected' : '' }}>KOREKSI SUBSTANSI (&lt;70)</option>
                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>DRAFT / BELUM REVIEW</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Cari Unit / Auditor</label>
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Nama unit / RA / Report ID..." value="{{ request('q') }}">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('pelaporan-bulanan.review-laporan') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel Review Laporan --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 45px;">No</th>
                        <th>Report ID</th>
                        <th>Nama Unit</th>
                        <th>Reviewer</th>
                        <th>Tgl Review</th>
                        <th class="text-center">Kegiatan</th>
                        <th class="text-center">Isu</th>
                        <th class="text-center text-danger">High Risk</th>
                        <th class="text-center text-warning">Overdue</th>
                        <th class="text-center">% Lengkap</th>
                        <th>Akurasi</th>
                        <th>Konsistensi</th>
                        <th>Analisis</th>
                        <th>Bukti</th>
                        <th>Tindak Lanjut</th>
                        <th>Eskalasi</th>
                        <th class="text-center bg-light">Skor Akhir</th>
                        <th class="text-center">Status Final</th>
                        <th class="text-center" style="width: 90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviewList as $idx => $rev)
                    @php
                        $kal = $rev->kalender;
                        $badgeStatus = 'bg-secondary';
                        if ($rev->status_review_final === 'MEMADAI') $badgeStatus = 'bg-success';
                        elseif ($rev->status_review_final === 'KOREKSI MINOR') $badgeStatus = 'bg-warning text-dark';
                        elseif ($rev->status_review_final === 'KOREKSI SUBSTANSI') $badgeStatus = 'bg-danger';
                    @endphp
                    <tr>
                        <td class="text-center text-muted fw-semibold">{{ $idx + 1 }}</td>
                        <td class="fw-bold font-monospace text-primary">{{ $rev->report_id }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $kal->nama_unit ?? '-' }}</div>
                            <small class="text-muted">{{ $kal->kode_unit ?? '' }} - RA: {{ $kal->resident_auditor ?? '-' }}</small>
                        </td>
                        <td><small class="text-muted">{{ $rev->reviewer ?: '-' }}</small></td>
                        <td class="font-monospace text-nowrap">
                            {{ $rev->tanggal_review ? \Carbon\Carbon::parse($rev->tanggal_review)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="text-center font-monospace">{{ $rev->jumlah_kegiatan }}</td>
                        <td class="text-center font-monospace">{{ $rev->jumlah_isu }}</td>
                        <td class="text-center font-monospace fw-bold {{ $rev->high_risk > 0 ? 'text-danger' : 'text-muted' }}">
                            {{ $rev->high_risk }}
                        </td>
                        <td class="text-center font-monospace fw-bold {{ $rev->tl_overdue > 0 ? 'text-warning' : 'text-muted' }}">
                            {{ $rev->tl_overdue }}
                        </td>
                        <td class="text-center font-monospace">
                            {{ number_format($rev->persen_kelengkapan ?? 0, 0) }}%
                        </td>
                        <td><span class="badge {{ $rev->akurasi_data === 'MEMADAI' ? 'badge-memadai' : ($rev->akurasi_data === 'PERLU PERBAIKAN' ? 'badge-perbaikan' : 'badge-tidak') }}">{{ $rev->akurasi_data ?: '-' }}</span></td>
                        <td><span class="badge {{ $rev->konsistensi === 'MEMADAI' ? 'badge-memadai' : ($rev->konsistensi === 'PERLU PERBAIKAN' ? 'badge-perbaikan' : 'badge-tidak') }}">{{ $rev->konsistensi ?: '-' }}</span></td>
                        <td><span class="badge {{ $rev->kualitas_analisis === 'MEMADAI' ? 'badge-memadai' : ($rev->kualitas_analisis === 'PERLU PERBAIKAN' ? 'badge-perbaikan' : 'badge-tidak') }}">{{ $rev->kualitas_analisis ?: '-' }}</span></td>
                        <td><span class="badge {{ $rev->bukti_pendukung === 'MEMADAI' ? 'badge-memadai' : ($rev->bukti_pendukung === 'PERLU PERBAIKAN' ? 'badge-perbaikan' : 'badge-tidak') }}">{{ $rev->bukti_pendukung ?: '-' }}</span></td>
                        <td><span class="badge {{ $rev->tindak_lanjut === 'MEMADAI' ? 'badge-memadai' : ($rev->tindak_lanjut === 'PERLU PERBAIKAN' ? 'badge-perbaikan' : 'badge-tidak') }}">{{ $rev->tindak_lanjut ?: '-' }}</span></td>
                        <td><span class="badge {{ $rev->eskalasi === 'MEMADAI' ? 'badge-memadai' : ($rev->eskalasi === 'PERLU PERBAIKAN' ? 'badge-perbaikan' : 'badge-tidak') }}">{{ $rev->eskalasi ?: '-' }}</span></td>
                        <td class="text-center font-monospace fw-bold fs-6 bg-light text-primary">
                            {{ $rev->skor_review !== null ? number_format($rev->skor_review, 0) : '-' }}
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $badgeStatus }} px-2 py-1">
                                {{ $rev->status_review_final ?: 'DRAFT' }}
                            </span>
                        </td>
                        <td class="text-center text-nowrap">
                            <a href="{{ route('pelaporan-bulanan.review-laporan.edit', $rev->report_id) }}" class="btn btn-outline-primary btn-sm px-2 py-1" title="Buka Form Review">
                                <i class="bi bi-pencil-square me-1"></i> Review
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="19" class="text-center py-4 text-muted">
                            <i class="bi bi-clipboard-x fs-2 d-block mb-2"></i>
                            Belum ada lembar review untuk periode <strong>{{ $periode }}</strong>.<br>
                            Review otomatis diinisiasi ketika kalender laporan dibuat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
