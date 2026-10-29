@extends('layouts.app')
@section('title', 'KKA Review Laporan Bulanan (WP-RA-08-F) - SOP 08')

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
        background: #f1f5f9;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #334155;
        border-bottom: 2px solid #cbd5e1;
        white-space: nowrap;
        vertical-align: middle;
        padding: 0.6rem 0.4rem;
    }
    .table tbody td {
        font-size: 0.8rem;
        vertical-align: middle;
        padding: 0.5rem 0.4rem;
        border-color: #f1f5f9;
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
                <i class="bi bi-clipboard-check me-2"></i>KKA Review Laporan Bulanan (WP-RA-08-F)
            </h4>
            <p class="text-muted small mb-0">
                Evaluasi 6 Aspek Substansi, Perhitungan Skor Review Berbobot (40% Lengkap + 60% Substansi), dan Penetapan Status Review Final
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="{{ route('pelaporan-bulanan.kalender', ['periode' => $periode]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kalender Laporan
            </a>
            <a href="{{ route('pelaporan-bulanan.koreksi-approval', ['periode' => $periode]) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                Lanjut ke Koreksi & Approval <i class="bi bi-arrow-right ms-1"></i>
            </a>
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

    {{-- Summary KPI Cards --}}
    @php
        $totalReview = $reviewList->count();
        $memadaiCount = $reviewList->where('status_review_final', 'MEMADAI')->count();
        $minorCount = $reviewList->where('status_review_final', 'KOREKSI MINOR')->count();
        $substansiCount = $reviewList->where('status_review_final', 'KOREKSI SUBSTANSI')->count();
        $eskalasiCount = $reviewList->where('status_review_final', 'PERLU ESKALASI')->count();
    @endphp
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Total Laporan</div>
                <div class="kpi-val text-primary">{{ $totalReview }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Memadai</div>
                <div class="kpi-val text-success">{{ $memadaiCount }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Koreksi Minor</div>
                <div class="kpi-val text-warning">{{ $minorCount }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card text-center">
                <div class="kpi-title">Koreksi Substansi</div>
                <div class="kpi-val text-danger">{{ $substansiCount }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card text-center">
                <div class="kpi-title">Perlu Eskalasi</div>
                <div class="kpi-val text-danger">{{ $eskalasiCount }}</div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('pelaporan-bulanan.review-laporan') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Periode Bulan</label>
                <input type="month" name="periode" class="form-control form-control-sm" value="{{ $periode }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Filter Unit Kerja</label>
                <select name="kode_unit" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Seluruh Unit Kerja ({{ count($masterUnits) }}) --</option>
                    @foreach($masterUnits as $mu)
                        <option value="{{ $mu->kode_unit }}" {{ request('kode_unit') == $mu->kode_unit ? 'selected' : '' }}>
                            [{{ $mu->kode_unit }}] {{ $mu->nama_unit }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Status Review Final</label>
                <select name="status_review_final" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Status --</option>
                    <option value="MEMADAI" {{ request('status_review_final') == 'MEMADAI' ? 'selected' : '' }}>MEMADAI</option>
                    <option value="KOREKSI MINOR" {{ request('status_review_final') == 'KOREKSI MINOR' ? 'selected' : '' }}>KOREKSI MINOR</option>
                    <option value="KOREKSI SUBSTANSI" {{ request('status_review_final') == 'KOREKSI SUBSTANSI' ? 'selected' : '' }}>KOREKSI SUBSTANSI</option>
                    <option value="PERLU ESKALASI" {{ request('status_review_final') == 'PERLU ESKALASI' ? 'selected' : '' }}>PERLU ESKALASI</option>
                    <option value="REJECTED FOR RESUBMISSION" {{ request('status_review_final') == 'REJECTED FOR RESUBMISSION' ? 'selected' : '' }}>REJECTED FOR RESUBMISSION</option>
                </select>
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

    {{-- Tabel Review Laporan (19 Kolom WP-RA-08-F) --}}
    <div class="table-responsive shadow-sm bg-white rounded-3">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 35px;">No</th>
                    <th>Report ID (A)</th>
                    <th>Nama Unit</th>
                    <th>Reviewer (B)</th>
                    <th>Tgl Review (C)</th>
                    <th class="text-center">Keg (D)</th>
                    <th class="text-center">Isu (E)</th>
                    <th class="text-center text-danger">HR (F)</th>
                    <th class="text-center text-warning">Overdue (G)</th>
                    <th class="text-center">% Lengkap (H)</th>
                    <th>Akurasi (I)</th>
                    <th>Konsistensi (J)</th>
                    <th>Analisis (K)</th>
                    <th>Bukti (L)</th>
                    <th>TL (M)</th>
                    <th>Eskalasi (N)</th>
                    <th class="text-center bg-light">Skor (O)</th>
                    <th class="text-center">Status Final (S)</th>
                    <th class="text-center" style="width: 80px;">Aksi</th>
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
                    elseif ($rev->status_review_final === 'PERLU ESKALASI') $badgeStatus = 'bg-danger text-white fw-bold';
                    elseif ($rev->status_review_final === 'REJECTED FOR RESUBMISSION') $badgeStatus = 'bg-dark text-white';
                @endphp
                <tr>
                    <td class="text-center text-muted fw-semibold">{{ $idx + 1 }}</td>
                    <td class="font-monospace fw-bold text-primary">{{ $rev->report_id }}</td>
                    <td>
                        <span class="fw-semibold text-dark">{{ $kal ? $kal->nama_unit : '-' }}</span>
                        <small class="d-block text-muted">[{{ $kal ? $kal->kode_unit : '' }}]</small>
                    </td>
                    <td><small class="text-muted">{{ $rev->reviewer ?: 'Belum Review' }}</small></td>
                    <td class="font-monospace small text-nowrap">
                        {{ $rev->tanggal_review ? \Carbon\Carbon::parse($rev->tanggal_review)->format('d/m/Y') : '-' }}
                    </td>
                    <td class="text-center font-monospace">{{ $rev->jumlah_kegiatan }}</td>
                    <td class="text-center font-monospace">{{ $rev->jumlah_isu }}</td>
                    <td class="text-center font-monospace fw-bold {{ $rev->high_risk > 0 ? 'text-danger' : 'text-muted' }}">
                        {{ $rev->high_risk }}
                    </td>
                    <td class="text-center font-monospace fw-bold {{ $rev->tl_overdue > 0 ? 'text-danger' : 'text-muted' }}">
                        {{ $rev->tl_overdue }}
                    </td>
                    <td class="text-center font-monospace fw-bold text-primary">
                        {{ number_format($rev->persen_kelengkapan, 0) }}%
                    </td>
                    <td><span class="badge {{ $rev->akurasi_data === 'MEMADAI' ? 'badge-memadai' : ($rev->akurasi_data === 'PERLU PERBAIKAN' ? 'badge-perbaikan' : 'badge-tidak') }}">{{ $rev->akurasi_data }}</span></td>
                    <td><span class="badge {{ $rev->konsistensi === 'MEMADAI' ? 'badge-memadai' : ($rev->konsistensi === 'PERLU PERBAIKAN' ? 'badge-perbaikan' : 'badge-tidak') }}">{{ $rev->konsistensi }}</span></td>
                    <td><span class="badge {{ $rev->kualitas_analisis === 'MEMADAI' ? 'badge-memadai' : ($rev->kualitas_analisis === 'PERLU PERBAIKAN' ? 'badge-perbaikan' : 'badge-tidak') }}">{{ $rev->kualitas_analisis }}</span></td>
                    <td><span class="badge {{ $rev->bukti_pendukung === 'MEMADAI' ? 'badge-memadai' : ($rev->bukti_pendukung === 'PERLU PERBAIKAN' ? 'badge-perbaikan' : 'badge-tidak') }}">{{ $rev->bukti_pendukung }}</span></td>
                    <td><span class="badge {{ $rev->tindak_lanjut === 'MEMADAI' ? 'badge-memadai' : ($rev->tindak_lanjut === 'PERLU PERBAIKAN' ? 'badge-perbaikan' : 'badge-tidak') }}">{{ $rev->tindak_lanjut }}</span></td>
                    <td><span class="badge {{ $rev->eskalasi === 'MEMADAI' ? 'badge-memadai' : ($rev->eskalasi === 'PERLU PERBAIKAN' ? 'badge-perbaikan' : 'badge-tidak') }}">{{ $rev->eskalasi }}</span></td>
                    <td class="text-center font-monospace fw-bold bg-light fs-6 text-dark">
                        {{ $rev->skor_review }}
                    </td>
                    <td class="text-center text-nowrap">
                        <span class="badge {{ $badgeStatus }} px-2 py-1">
                            {{ $rev->status_review_final }}
                        </span>
                        @if($rev->override)
                            <small class="d-block text-muted fst-italic" title="{{ $rev->alasan_override }}">*Override</small>
                        @endif
                    </td>
                    <td class="text-center text-nowrap">
                        @if(!$isRa)
                            <a href="{{ route('pelaporan-bulanan.review-laporan.edit', $rev->report_id) }}" class="btn btn-outline-primary btn-sm px-2" title="Evaluasi & Beri Penilaian">
                                <i class="bi bi-pencil-square me-1"></i> Review
                            </a>
                        @else
                            <span class="badge bg-light text-muted border" title="Hanya Reviewer/Admin yang dapat menilai">Read-Only</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="19" class="text-center py-4 text-muted">
                        <i class="bi bi-clipboard-x fs-2 d-block mb-2"></i>
                        Belum ada data Review Laporan pada periode <strong>{{ $periode }}</strong>.<br>
                        Silakan inisiasi kalender terlebih dahulu.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
