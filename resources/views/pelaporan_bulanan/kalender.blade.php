@extends('layouts.app')
@section('title', 'Kalender Laporan Bulanan')

@section('content')
<style>
    .kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06);
    }
    .kpi-title {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 0.35rem;
    }
    .kpi-value {
        font-size: 1.6rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.15rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .badge-tepat { background-color: #10b981; color: #fff; }
    .badge-terlambat { background-color: #ef4444; color: #fff; }
    .badge-belum-masuk { background-color: #dc2626; color: #fff; }
    .badge-belum-tempo { background-color: #3b82f6; color: #fff; }
    .badge-tidak-wajib { background-color: #64748b; color: #fff; }
    .badge-konsolidasi-ya { background-color: #059669; color: #fff; font-weight: 700; }
    .badge-konsolidasi-belum { background-color: #94a3b8; color: #fff; }

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
                <i class="bi bi-calendar3-range me-2"></i>Kalender Laporan Bulanan
            </h4>
            <p class="text-muted small mb-0">
                Monitoring Kepatuhan, Jadwal Jatuh Tempo Hari Kerja ke-5, dan Rekapitulasi Pelaporan Bulanan Resident Auditor
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="{{ route('pelaporan-bulanan.kalender.create', ['periode' => $periode]) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Inisiasi Kalender Periode
            </a>
        </div>
    </div>

    {{-- Tabs Navigasi Modul --}}
    @include('pelaporan_bulanan.tabs')

    {{-- Alert Flash Message --}}
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

    {{-- KPI Summary Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Total Unit Lapor</div>
                <div class="kpi-value text-dark">{{ $totalLaporan }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center border-success">
                <div class="kpi-title text-success">Tepat Waktu</div>
                <div class="kpi-value text-success">{{ $tepatWaktu }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center border-danger">
                <div class="kpi-title text-danger">Terlambat</div>
                <div class="kpi-value text-danger">{{ $terlambat }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center border-warning">
                <div class="kpi-title text-danger">Belum Masuk</div>
                <div class="kpi-value text-danger">{{ $belumMasuk }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center border-primary">
                <div class="kpi-title text-primary">Siap Konsolidasi</div>
                <div class="kpi-value text-primary">{{ $siapKonsolidasi }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center bg-light">
                <div class="kpi-title">Cut-off Aktif</div>
                <div class="fw-bold text-secondary" style="font-size: 0.95rem; margin-top: 0.35rem;">
                    {{ \Carbon\Carbon::parse($params->tanggal_cutoff)->format('d M Y') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('pelaporan-bulanan.kalender') }}" class="row g-2 align-items-end">
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
                            [{{ $mu->kode_unit }}] {{ $mu->nama_unit }} ({{ $mu->jenis_unit }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Status Ketepatan</label>
                <select name="ketepatan" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Status --</option>
                    <option value="TEPAT WAKTU" {{ request('ketepatan') == 'TEPAT WAKTU' ? 'selected' : '' }}>TEPAT WAKTU</option>
                    <option value="TERLAMBAT" {{ request('ketepatan') == 'TERLAMBAT' ? 'selected' : '' }}>TERLAMBAT</option>
                    <option value="BELUM MASUK" {{ request('ketepatan') == 'BELUM MASUK' ? 'selected' : '' }}>BELUM MASUK</option>
                    <option value="BELUM JATUH TEMPO" {{ request('ketepatan') == 'BELUM JATUH TEMPO' ? 'selected' : '' }}>BELUM JATUH TEMPO</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('pelaporan-bulanan.kalender') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel Kalender Laporan --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 45px;">No</th>
                        <th>Report ID</th>
                        <th>Periode</th>
                        <th>Kode</th>
                        <th>Nama Unit</th>
                        <th>Resident Auditor</th>
                        <th>Wajib Lapor</th>
                        <th>Jatuh Tempo</th>
                        <th>Tgl Submit</th>
                        <th class="text-center">Hari Terlambat</th>
                        <th>Ketepatan</th>
                        <th class="text-center">Kelengkapan</th>
                        <th>Status Review</th>
                        <th>Status Final</th>
                        <th class="text-center">Quality Score</th>
                        <th class="text-center">Konsolidasi</th>
                        <th class="text-center" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
            <tbody>
                @forelse($kalenderList as $idx => $row)
                @php
                    $isSiapReview = $row->checklist && $row->checklist->status_kesiapan === 'SIAP REVIEW';
                    $canSubmit = empty($row->tanggal_submit) && $isSiapReview;
                    $bgRow = '';
                    if ($row->ketepatan === 'TERLAMBAT' || $row->ketepatan === 'BELUM MASUK') {
                        $bgRow = 'table-light-danger';
                    }
                @endphp
                <tr class="{{ $bgRow }}">
                    <td class="text-center text-muted fw-semibold">{{ $idx + 1 }}</td>
                    <td class="fw-bold text-primary font-monospace">{{ $row->report_id }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->periode)->format('m/Y') }}</td>
                    <td class="font-monospace fw-bold">{{ $row->kode_unit }}</td>
                    <td class="fw-semibold">{{ $row->nama_unit }}</td>
                    <td><small class="text-muted">{{ $row->resident_auditor }}</small></td>
                    <td>
                        <span class="badge {{ $row->wajib_lapor === 'YA' ? 'bg-success' : 'bg-secondary' }}">
                            {{ $row->wajib_lapor }}
                        </span>
                    </td>
                    <td class="font-monospace text-nowrap">
                        {{ \Carbon\Carbon::parse($row->jatuh_tempo)->format('d/m/Y') }}
                    </td>
                    <td class="font-monospace text-nowrap">
                        @if($row->tanggal_submit)
                            <span class="text-success fw-bold">{{ \Carbon\Carbon::parse($row->tanggal_submit)->format('d/m/Y') }}</span>
                        @else
                            <span class="text-muted fst-italic">-</span>
                        @endif
                    </td>
                    <td class="text-center font-monospace fw-bold {{ $row->hari_terlambat > 0 ? 'text-danger' : 'text-muted' }}">
                        {{ $row->hari_terlambat > 0 ? $row->hari_terlambat . ' hr' : '0' }}
                    </td>
                    <td>
                        @if($row->ketepatan === 'TEPAT WAKTU')
                            <span class="badge badge-tepat px-2 py-1"><i class="bi bi-check-circle me-1"></i>TEPAT WAKTU</span>
                        @elseif($row->ketepatan === 'TERLAMBAT')
                            <span class="badge badge-terlambat px-2 py-1"><i class="bi bi-clock-history me-1"></i>TERLAMBAT</span>
                        @elseif($row->ketepatan === 'BELUM MASUK')
                            <span class="badge badge-belum-masuk px-2 py-1"><i class="bi bi-exclamation-octagon me-1"></i>BELUM MASUK</span>
                        @elseif($row->ketepatan === 'BELUM JATUH TEMPO')
                            <span class="badge badge-belum-tempo px-2 py-1"><i class="bi bi-hourglass-split me-1"></i>BELUM TEMPO</span>
                        @else
                            <span class="badge badge-tidak-wajib px-2 py-1">{{ $row->ketepatan }}</span>
                        @endif
                    </td>
                    <td class="text-center font-monospace">
                        <a href="{{ route('pelaporan-bulanan.checklist', $row->report_id) }}" class="text-decoration-none fw-bold {{ $row->kelengkapan >= 100 ? 'text-success' : 'text-warning' }}" title="Lihat Checklist">
                            {{ number_format($row->kelengkapan, 0) }}%
                        </a>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">{{ $row->status_review }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $row->status_final === 'APPROVED/FINAL' ? 'bg-success' : ($row->status_final === 'SUBMITTED' ? 'bg-info text-dark' : 'bg-secondary') }}">
                            {{ $row->status_final }}
                        </span>
                    </td>
                    <td class="text-center font-monospace fw-bold">
                        {{ number_format($row->quality_score, 0) }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $row->masuk_konsolidasi === 'YA' ? 'badge-konsolidasi-ya' : 'badge-konsolidasi-belum' }} px-2 py-1">
                            {{ $row->masuk_konsolidasi }}
                        </span>
                    </td>
                    <td class="text-center text-nowrap">
                        <div class="btn-group btn-group-sm">
                            {{-- Tombol Submit RA --}}
                            @if(empty($row->tanggal_submit))
                                @if($isSiapReview)
                                    <form method="POST" action="{{ route('pelaporan-bulanan.kalender.submit', $row->id) }}" onsubmit="return confirm('Kirim Laporan Bulanan {{ $row->report_id }} sekarang?')" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" title="Submit Laporan Bulanan (Sudah 100% Lengkap)">
                                            <i class="bi bi-send-fill me-1"></i> Submit
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-outline-secondary btn-sm" disabled title="Checklist harus 100% SIAP REVIEW sebelum Submit">
                                        <i class="bi bi-lock-fill"></i> Submit
                                    </button>
                                @endif
                            @endif

                            {{-- Menu Cepat --}}
                            <a href="{{ route('pelaporan-bulanan.checklist', $row->report_id) }}" class="btn btn-outline-primary" title="Checklist Kelengkapan">
                                <i class="bi bi-ui-checks"></i>
                            </a>
                            <a href="{{ route('pelaporan-bulanan.rekap-kegiatan', $row->report_id) }}" class="btn btn-outline-info" title="Rekap Kegiatan">
                                <i class="bi bi-list-task"></i>
                            </a>
                            <a href="{{ route('pelaporan-bulanan.laporan-bulanan', $row->report_id) }}" class="btn btn-outline-success" title="Cetak Dokumen Resmi">
                                <i class="bi bi-printer"></i>
                            </a>
                            <a href="{{ route('pelaporan-bulanan.kalender.edit', $row->id) }}" class="btn btn-outline-dark" title="Edit Info / Catatan">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="17" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        Belum ada data kalender laporan pada periode <strong>{{ $periode }}</strong>.<br>
                        Klik tombol <strong>"Inisiasi Kalender Periode"</strong> di atas untuk men-generate otomatis seluruh unit kerja.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection
