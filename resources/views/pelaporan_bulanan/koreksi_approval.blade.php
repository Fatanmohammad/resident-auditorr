@extends('layouts.app')
@section('title', 'Koreksi & Approval Laporan')

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
    .badge-approved { background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-weight: 700; }
    .badge-revision { background: #fef9c3; color: #a16207; border: 1px solid #fde047; font-weight: 700; }
    .badge-revised { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; font-weight: 700; }
    .badge-under-review { background: #ccfbf1; color: #0f766e; border: 1px solid #5eead4; font-weight: 700; }
    .badge-rejected { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; font-weight: 700; }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Header Modul --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a8a;">
                <i class="bi bi-patch-check me-2"></i>Koreksi &amp; Approval Laporan Bulanan
            </h4>
            <p class="text-muted small mb-0">
                Siklus Manajemen Koreksi, Resubmit Perbaikan RA, Perhitungan Batas Hari Kerja, dan Penetapan Approval Final Laporan
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

    {{-- Summary KPI Cards --}}
    @php
        $totalKoreksi = $koreksiList->count();
        $approvedCount = $koreksiList->where('status_final', 'APPROVED/FINAL')->count();
        $revisionReqCount = $koreksiList->where('status_final', 'REVISION REQUIRED')->count();
        $revisedCount = $koreksiList->where('status_final', 'REVISED')->count();
        $overdueCount = $koreksiList->where('hari_terlambat_koreksi', '>', 0)->count();
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-primary">
                <div class="kpi-title">Total Siklus Laporan</div>
                <div class="kpi-val text-primary">{{ $totalKoreksi }}</div>
                <small class="text-muted" style="font-size: 0.72rem;">Periode {{ \Carbon\Carbon::parse($periode.'-01')->translatedFormat('F Y') }}</small>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-success">
                <div class="kpi-title">Approved / Final</div>
                <div class="kpi-val text-success">{{ $approvedCount }}</div>
                <small class="text-muted" style="font-size: 0.72rem;">{{ $totalKoreksi > 0 ? round(($approvedCount / $totalKoreksi) * 100) : 0 }}% Dari Total Laporan</small>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-warning">
                <div class="kpi-title">Perlu Revisi RA</div>
                <div class="kpi-val text-warning">{{ $revisionReqCount }}</div>
                <small class="text-muted" style="font-size: 0.72rem;">Menunggu Perbaikan RA</small>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-info">
                <div class="kpi-title">Telah Resubmit</div>
                <div class="kpi-val text-info">{{ $revisedCount }}</div>
                <small class="text-muted" style="font-size: 0.72rem;">Menunggu Review Ulang SKAI</small>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-danger">
                <div class="kpi-title">Terlambat Resubmit</div>
                <div class="kpi-val text-danger">{{ $overdueCount }}</div>
                <small class="text-muted" style="font-size: 0.72rem;">Melebihi Batas Koreksi</small>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="filter-card">
        <form action="{{ route('pelaporan-bulanan.koreksi-approval') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="periode" class="form-label small fw-bold text-muted mb-1">Periode Laporan</label>
                <input type="month" name="periode" id="periode" class="form-control form-control-sm" value="{{ $periode }}" onchange="this.form.submit()">
            </div>
            @if(!$isRa)
            <div class="col-md-3">
                <label for="kode_unit" class="form-label small fw-bold text-muted mb-1">Filter Unit / Cabang</label>
                <select name="kode_unit" id="kode_unit" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Unit Cabang --</option>
                    @foreach($masterUnits as $mu)
                        <option value="{{ $mu->kode_unit }}" {{ request('kode_unit') == $mu->kode_unit ? 'selected' : '' }}>
                            [{{ $mu->kode_unit }}] {{ $mu->nama_unit }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-3">
                <label for="status_final" class="form-label small fw-bold text-muted mb-1">Filter Status Final</label>
                <select name="status_final" id="status_final" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Status Final --</option>
                    <option value="APPROVED/FINAL" {{ request('status_final') == 'APPROVED/FINAL' ? 'selected' : '' }}>APPROVED/FINAL</option>
                    <option value="REVISED" {{ request('status_final') == 'REVISED' ? 'selected' : '' }}>REVISED</option>
                    <option value="UNDER REVIEW" {{ request('status_final') == 'UNDER REVIEW' ? 'selected' : '' }}>UNDER REVIEW</option>
                    <option value="REVISION REQUIRED" {{ request('status_final') == 'REVISION REQUIRED' ? 'selected' : '' }}>REVISION REQUIRED</option>
                    <option value="REJECTED FOR RESUBMISSION" {{ request('status_final') == 'REJECTED FOR RESUBMISSION' ? 'selected' : '' }}>REJECTED FOR RESUBMISSION</option>
                </select>
            </div>
            <div class="col-md-3 text-end">
                <button type="submit" class="btn btn-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('pelaporan-bulanan.koreksi-approval', ['periode' => $periode]) }}" class="btn btn-light btn-sm rounded-pill px-2 text-muted">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Main Table 15 Kolom (A s.d. O Sesuai Sheet Koreksi_Approval) --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-table me-2 text-primary"></i>Tabel Register Koreksi & Approval (15 Kolom A s.d. O)
            </h6>
            <span class="badge bg-light text-secondary border">
                Batas WORKDAY: Minor = +1 Hari Kerja | Substansi/Eskalasi = +2 Hari Kerja
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 45px;">No</th>
                        <th>Report ID</th>
                        <th>Hasil Review</th>
                        <th>Jenis Koreksi</th>
                        <th>Catatan Reviewer</th>
                        <th>Tgl Permintaan</th>
                        <th>Batas Koreksi</th>
                        <th>Tgl Resubmit</th>
                        <th>Perbaikan RA</th>
                        <th>Review Ulang</th>
                        <th>Keputusan</th>
                        <th>Approved By</th>
                        <th>Tgl Approval</th>
                        <th>Versi</th>
                        <th class="text-center">Status Final</th>
                        <th class="text-center">Hari Terlambat</th>
                        <th class="text-center" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($koreksiList as $idx => $item)
                        @php
                            $kal = $item->kalender;
                            $statusFinalBadgeClass = match($item->status_final) {
                                'APPROVED/FINAL' => 'badge-approved',
                                'REVISED' => 'badge-revised',
                                'UNDER REVIEW' => 'badge-under-review',
                                'REJECTED FOR RESUBMISSION' => 'badge-rejected',
                                default => 'badge-revision',
                            };
                            $jenisKoreksiBadge = match($item->jenis_koreksi) {
                                'TIDAK ADA' => 'bg-success-subtle text-success',
                                'ADMINISTRATIF/MINOR' => 'bg-info-subtle text-info-emphasis',
                                'ESKALASI' => 'bg-danger-subtle text-danger',
                                default => 'bg-warning-subtle text-warning-emphasis',
                            };
                        @endphp
                        <tr>
                            <td class="text-center text-muted">{{ $idx + 1 }}</td>
                            <td>
                                <strong class="text-primary font-monospace">{{ $item->report_id }}</strong>
                                <div class="text-muted small" style="font-size: 0.72rem;">
                                    {{ $kal ? $kal->nama_unit : '-' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $item->hasil_review == 'MEMADAI' ? 'bg-success' : ($item->hasil_review == 'KOREKSI MINOR' ? 'bg-info' : 'bg-warning text-dark') }} py-1 px-2" style="font-size: 0.72rem;">
                                    {{ $item->hasil_review }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $jenisKoreksiBadge }} py-1 px-2" style="font-size: 0.72rem;">
                                    {{ $item->jenis_koreksi }}
                                </span>
                            </td>
                            <td>
                                <div class="text-truncate text-secondary" style="max-width: 140px;" title="{{ $item->catatan_reviewer }}">
                                    {{ $item->catatan_reviewer ?: '-' }}
                                </div>
                            </td>
                            <td class="text-nowrap font-monospace" style="font-size: 0.75rem;">
                                {{ $item->tanggal_permintaan ? \Carbon\Carbon::parse($item->tanggal_permintaan)->format('d/m/y') : '-' }}
                            </td>
                            <td class="text-nowrap font-monospace text-primary fw-bold" style="font-size: 0.75rem;">
                                {{ $item->batas_koreksi ? \Carbon\Carbon::parse($item->batas_koreksi)->format('d/m/y') : '-' }}
                            </td>
                            <td class="text-nowrap font-monospace text-success fw-bold" style="font-size: 0.75rem;">
                                {{ $item->tanggal_resubmit ? \Carbon\Carbon::parse($item->tanggal_resubmit)->format('d/m/y') : '-' }}
                            </td>
                            <td>
                                <div class="text-truncate text-secondary" style="max-width: 130px;" title="{{ $item->perbaikan_ra }}">
                                    {{ $item->perbaikan_ra ?: '-' }}
                                </div>
                            </td>
                            <td class="text-nowrap font-monospace" style="font-size: 0.75rem;">
                                {{ $item->tanggal_review_ulang ? \Carbon\Carbon::parse($item->tanggal_review_ulang)->format('d/m/y') : '-' }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border" style="font-size: 0.7rem;">
                                    {{ $item->keputusan_approval ?: '-' }}
                                </span>
                            </td>
                            <td class="small">{{ $item->approved_by ?: '-' }}</td>
                            <td class="text-nowrap font-monospace" style="font-size: 0.75rem;">
                                {{ $item->tanggal_approval ? \Carbon\Carbon::parse($item->tanggal_approval)->format('d/m/y') : '-' }}
                            </td>
                            <td class="font-monospace small">{{ $item->versi_final ?: '-' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $statusFinalBadgeClass }} px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                                    {{ $item->status_final }}
                                </span>
                            </td>
                            <td class="text-center font-monospace">
                                @if($item->hari_terlambat_koreksi > 0)
                                    <span class="badge bg-danger text-white rounded-pill px-2">
                                        +{{ $item->hari_terlambat_koreksi }} Hari
                                    </span>
                                @else
                                    <span class="text-success small fw-bold">0 Hari</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                @if(!$isRa)
                                    {{-- Reviewer / Approver Action --}}
                                    <a href="{{ route('pelaporan-bulanan.koreksi-approval.edit', ['reportId' => $item->report_id]) }}"
                                       class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" style="font-size: 0.75rem;" title="Tinjau, Minta Revisi & Approval">
                                        <i class="bi bi-pencil-square me-1"></i> Review/Approve
                                    </a>
                                @else
                                    {{-- RA Action --}}
                                    @if($item->status_final !== 'APPROVED/FINAL')
                                        <a href="{{ route('pelaporan-bulanan.koreksi-approval.resubmit', ['reportId' => $item->report_id]) }}"
                                           class="btn btn-sm btn-warning rounded-pill px-2 py-1 text-dark fw-bold" style="font-size: 0.75rem;" title="Kirimkan Revisi Perbaikan">
                                            <i class="bi bi-send-check me-1"></i> Resubmit
                                        </a>
                                    @else
                                        <span class="badge bg-light text-success border border-success-subtle py-1 px-2" style="font-size: 0.7rem;">
                                            <i class="bi bi-lock-fill me-1"></i> Final
                                        </span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="17" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Tidak ada data register koreksi & approval untuk periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
