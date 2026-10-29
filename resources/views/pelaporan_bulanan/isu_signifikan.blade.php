@extends('layouts.app')
@section('title', 'Matriks Isu Signifikan & Rekomendasi Eskalasi')

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
</style>

<div class="container-fluid px-3 py-3">
    {{-- Header Modul --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a8a;">
                <i class="bi bi-shield-fill-exclamation me-2 text-danger"></i>Matriks Isu Signifikan &amp; Rekomendasi Eskalasi
            </h4>
            <p class="text-muted small mb-0">
                Inventarisasi Isu Bertingkat Signifikansi Tinggi, Rekomendasi Jalur Sistem, dan Penetapan Tujuan Eskalasi SKAI
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

    {{-- KPI Cards Summary --}}
    @php
        $totalIsu = $isuList->count();
        $fraudCount = $isuList->filter(fn($i) => str_contains($i->jalur_sistem, 'SOP-RA-09') || str_contains($i->keputusan_reviewer, '09'))->count();
        $eskalasiSop10Count = $isuList->filter(fn($i) => str_contains($i->jalur_sistem, 'SOP-RA-10') || str_contains($i->keputusan_reviewer, '10'))->count();
        $pendingDecision = $isuList->whereNull('keputusan_reviewer')->count();
        $highRiskCount = $isuList->where('risk_level', 'HIGH')->count();
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-danger">
                <div class="kpi-title">Total Isu Signifikan</div>
                <div class="kpi-val text-danger">{{ $totalIsu }}</div>
                <small class="text-muted" style="font-size: 0.72rem;">Flag Signifikan = YA</small>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-dark">
                <div class="kpi-title">Indikasi Fraud</div>
                <div class="kpi-val text-dark">{{ $fraudCount }}</div>
                <small class="text-muted" style="font-size: 0.72rem;">Triase Investigasi</small>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-warning">
                <div class="kpi-title">Pemeriksaan Khusus</div>
                <div class="kpi-val text-warning">{{ $eskalasiSop10Count }}</div>
                <small class="text-muted" style="font-size: 0.72rem;">Eskalasi Kadiv / Direksi</small>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-primary">
                <div class="kpi-title">High Risk Level</div>
                <div class="kpi-val text-primary">{{ $highRiskCount }}</div>
                <small class="text-muted" style="font-size: 0.72rem;">Tingkat Risiko Tinggi</small>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-secondary">
                <div class="kpi-title">Belum Diputuskan</div>
                <div class="kpi-val text-secondary">{{ $pendingDecision }}</div>
                <small class="text-muted" style="font-size: 0.72rem;">Menunggu Keputusan Reviewer</small>
            </div>
        </div>
    </div>

    {{-- Filter Bar & Tombol Tarik Isu --}}
    <div class="filter-card">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
            <form action="{{ route('pelaporan-bulanan.isu-signifikan') }}" method="GET" class="d-flex flex-wrap gap-2 align-items-end flex-grow-1">
                <div style="min-width: 150px;">
                    <label for="periode" class="form-label small fw-bold text-muted mb-1">Periode</label>
                    <input type="month" name="periode" id="periode" class="form-control form-control-sm" value="{{ $periode }}" onchange="this.form.submit()">
                </div>
                <div style="min-width: 260px;">
                    <label for="reportId" class="form-label small fw-bold text-muted mb-1">Pilih Report ID / Unit</label>
                    <select name="reportId" id="reportId" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($availableReports as $rep)
                            <option value="{{ $rep->report_id }}" {{ $selectedReportId == $rep->report_id ? 'selected' : '' }}>
                                {{ $rep->report_id }} - [{{ $rep->kode_unit }}] {{ $rep->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-funnel me-1"></i> Tampilkan
                    </button>
                </div>
            </form>

            @if($selectedReportId)
                <div>
                    <form action="{{ route('pelaporan-bulanan.isu-signifikan.pull', ['reportId' => $selectedReportId]) }}" method="POST" onsubmit="return confirm('Tarik semua isu dari Rekap Isu & TL yang memiliki Flag Signifikan = YA ke tabel ini?')">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm fw-bold">
                            <i class="bi bi-cloud-arrow-down-fill me-1"></i> Tarik Otomatis Isu Berstatus Flag Signifikan = YA
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- Main Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-table me-2 text-danger"></i>Matriks Isu Signifikan
            </h6>
            <span class="badge bg-light text-secondary border">
                Terhubung dengan Rekap Isu & TL
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 45px;">No</th>
                        <th>Issue ID</th>
                        <th>Report ID</th>
                        <th>Periode</th>
                        <th>Unit</th>
                        <th>Area</th>
                        <th>Jenis Isu</th>
                        <th>Risk Level</th>
                        <th>Uraian Isu</th>
                        <th>Status TL</th>
                        <th>Aging</th>
                        <th>Berulang</th>
                        <th>Jalur Sistem</th>
                        <th>Keputusan Reviewer</th>
                        <th>Tujuan Eskalasi</th>
                        <th>Tgl Eskalasi</th>
                        <th>Ref Bukti</th>
                        <th>Arahan TL</th>
                        <th class="text-center" style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($isuList as $idx => $item)
                        @php
                            $riskBadge = match($item->risk_level) {
                                'HIGH' => 'bg-danger text-white',
                                'MEDIUM' => 'bg-warning text-dark',
                                default => 'bg-success text-white',
                            };
                            $jalurBadge = match($item->jalur_sistem) {
                                'SOP-RA-09/10' => 'bg-dark text-white',
                                'SOP-RA-10' => 'bg-danger-subtle text-danger border border-danger',
                                'SOP-RA-07' => 'bg-warning-subtle text-warning-emphasis',
                                default => 'bg-info-subtle text-info-emphasis',
                            };
                        @endphp
                        <tr>
                            <td class="text-center text-muted">{{ $idx + 1 }}</td>
                            <td>
                                <strong class="text-danger font-monospace">{{ $item->issue_id }}</strong>
                            </td>
                            <td class="font-monospace small text-primary">{{ $item->report_id }}</td>
                            <td class="text-nowrap small">{{ \Carbon\Carbon::parse($item->periode)->format('m/Y') }}</td>
                            <td class="small">{{ $item->nama_unit }}</td>
                            <td class="small">{{ $item->area }}</td>
                            <td>
                                <span class="badge bg-light text-dark border" style="font-size: 0.72rem;">
                                    {{ $item->jenis_isu }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $riskBadge }} px-2 py-1" style="font-size: 0.72rem;">
                                    {{ $item->risk_level }}
                                </span>
                            </td>
                            <td>
                                <div class="text-truncate text-secondary" style="max-width: 180px;" title="{{ $item->uraian_isu }}">
                                    {{ $item->uraian_isu }}
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $item->status_tl == 'OVERDUE' ? 'bg-danger' : ($item->status_tl == 'CLOSED' ? 'bg-success' : 'bg-secondary') }} px-2 py-1" style="font-size: 0.7rem;">
                                    {{ $item->status_tl }}
                                </span>
                            </td>
                            <td class="font-monospace small">{{ $item->aging ? $item->aging . ' Hari' : '-' }}</td>
                            <td class="small text-center">
                                @if($item->berulang == 'YA')
                                    <span class="badge bg-danger-subtle text-danger fw-bold">YA</span>
                                @else
                                    <span class="text-muted">TIDAK</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $jalurBadge }} px-2 py-1" style="font-size: 0.7rem;">
                                    {{ $item->jalur_sistem }}
                                </span>
                            </td>
                            <td>
                                <strong class="text-dark small">{{ $item->keputusan_reviewer ?: '-' }}</strong>
                            </td>
                            <td class="small">{{ $item->tujuan_eskalasi ?: '-' }}</td>
                            <td class="text-nowrap font-monospace small">
                                {{ $item->tanggal_eskalasi ? \Carbon\Carbon::parse($item->tanggal_eskalasi)->format('d/m/y') : '-' }}
                            </td>
                            <td>
                                <div class="text-truncate text-secondary small" style="max-width: 120px;" title="{{ $item->referensi_bukti }}">
                                    {{ $item->referensi_bukti ?: '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="text-truncate text-secondary small" style="max-width: 140px;" title="{{ $item->arahan_tindak_lanjut }}">
                                    {{ $item->arahan_tindak_lanjut ?: '-' }}
                                </div>
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('pelaporan-bulanan.isu-signifikan.edit', ['id' => $item->id]) }}"
                                   class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" style="font-size: 0.75rem;" title="Edit Keputusan Eskalasi / Referensi Bukti">
                                    <i class="bi bi-pencil-square me-1"></i> {{ $isRa ? 'Input Bukti' : 'Putuskan' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="19" class="text-center py-4 text-muted">
                                <i class="bi bi-shield-check fs-3 d-block mb-2 text-success"></i>
                                Tidak ada isu signifikan yang terdaftar untuk laporan ini.
                                @if($selectedReportId)
                                    <div class="mt-2">
                                        <form action="{{ route('pelaporan-bulanan.isu-signifikan.pull', ['reportId' => $selectedReportId]) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill">
                                                <i class="bi bi-cloud-arrow-down me-1"></i> Tarik Otomatis dari Rekap Isu & TL
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
