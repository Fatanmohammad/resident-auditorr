@extends('layouts.app')
@section('title', 'Dashboard Pelaporan & Konsolidasi RA (WP-RA-08-L) - SOP 08')

@section('content')
<style>
    .kpi-card-dashboard {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.15rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .kpi-card-dashboard:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06);
    }
    .kpi-cell-code {
        font-size: 0.68rem;
        font-weight: 700;
        color: #94a3b8;
        font-family: monospace;
    }
    .kpi-card-title {
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 0.2rem;
    }
    .kpi-card-val {
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 1.1;
    }
    .filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.15rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .section-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        height: 100%;
    }
    .section-card-header {
        padding: 0.85rem 1.15rem;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
    .badge-perlu-perhatian { background-color: #ef4444; color: #fff; font-weight: 700; }
    .badge-normal-unit { background-color: #10b981; color: #fff; font-weight: 600; }
    .score-bar-mini {
        height: 6px;
        background-color: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
    }
    .score-bar-mini-fill {
        height: 100%;
        border-radius: 4px;
    }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Breadcrumb & Title --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">SOP 08</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard SOP 08 (WP-RA-08-L)</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-speedometer2 text-info"></i> Dashboard Pelaporan & Konsolidasi RA
                <span class="badge bg-info-subtle text-info border border-info-subtle fs-6 fw-semibold">WP-RA-08-L</span>
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Visualisasi terpusat 12 Indikator Utama, Ringkasan Status, Skor Perhatian 13 Area, dan Monitoring Perhatian Unit Kerja.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-printer me-1"></i> Cetak Dashboard
            </button>
            <a href="{{ route('pelaporan-bulanan.konsolidasi', ['periode' => $periode]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-diagram-3 me-1"></i> Buka Konsolidasi (WP-RA-08-J)
            </a>
        </div>
    </div>

    {{-- Sub-Menu Navigation Tabs --}}
    @include('pelaporan_bulanan.tabs')

    {{-- Filter Card --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('pelaporan-bulanan.dashboard') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Periode Dashboard (Bulan)</label>
                <input type="month" name="periode" class="form-control form-control-sm" value="{{ $periode }}">
            </div>
            @if(!$isRa)
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Lingkup Unit / Cabang (Global by Default)</label>
                <select name="kode_unit" class="form-select form-select-sm">
                    <option value="">-- Seluruh Cabang (Konsolidasi Seluruh Bank Sulteng) --</option>
                    @foreach($masterUnits as $u)
                        <option value="{{ $u->kode_unit }}" {{ request('kode_unit') == $u->kode_unit ? 'selected' : '' }}>
                            {{ $u->kode_unit }} - {{ $u->nama_unit }} ({{ $u->resident_auditor }})
                        </option>
                    @endforeach
                </select>
            </div>
            @else
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Unit Kerja Anda (Terisolasi)</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ auth()->user()->unit_kerja ?? 'Cabang Anda' }}" readonly>
            </div>
            @endif
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm">
                    <i class="bi bi-funnel me-1"></i> Terapkan
                </button>
                <a href="{{ route('pelaporan-bulanan.dashboard') }}" class="btn btn-outline-secondary btn-sm px-3" title="Reset Filter">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>

    @php
        $kpi = $dashboardData['kpi'];
        $statusSummary = $dashboardData['status_summary'];
        $trenIsuRows = $dashboardData['tren_isu'];
        $unitMonitoring = $dashboardData['unit_monitoring'];
    @endphp

    {{-- ========================================================================= --}}
    {{-- A. 12 KARTU INDIKATOR UTAMA (KPI CARDS - Baris 6 s.d. 15 Sheet Dashboard) --}}
    {{-- ========================================================================= --}}
    <h6 class="fw-bold text-uppercase text-muted mb-2 small d-flex align-items-center gap-2">
        <i class="bi bi-grid-fill text-primary"></i> 12 Indikator Kinerja Utama (Baris 6 s.d. 15 Sheet Dashboard)
    </h6>

    {{-- Baris 1 KPI: Baris 6-7 Excel (Laporan Wajib, Sudah Submit, Tepat Waktu, Terlambat/Belum) --}}
    <div class="row g-2 mb-2">
        <div class="col-6 col-md-3">
            <div class="kpi-card-dashboard">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-card-title">Laporan Wajib</div>
                    <span class="kpi-cell-code">Sel A7</span>
                </div>
                <div class="kpi-card-val text-primary">{{ $kpi['laporan_wajib'] }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Unit wajib lapor periode aktif</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card-dashboard">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-card-title">Sudah Submit</div>
                    <span class="kpi-cell-code">Sel D7</span>
                </div>
                <div class="kpi-card-val text-info">{{ $kpi['sudah_submit'] }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Laporan telah dikirimkan RA</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card-dashboard">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-card-title">Tepat Waktu</div>
                    <span class="kpi-cell-code">Sel G7</span>
                </div>
                <div class="kpi-card-val text-success">{{ $kpi['tepat_waktu'] }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Submit sebelum/pada batas waktu</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card-dashboard">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-card-title">Terlambat / Belum</div>
                    <span class="kpi-cell-code">Sel J7</span>
                </div>
                <div class="kpi-card-val {{ $kpi['terlambat_belum'] > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $kpi['terlambat_belum'] }}
                </div>
                <div class="text-muted small" style="font-size: 0.72rem;">Terlambat + Belum masuk</div>
            </div>
        </div>
    </div>

    {{-- Baris 2 KPI: Baris 10-11 Excel (Approved/Final, Rata-rata Quality, Exception, Finding) --}}
    <div class="row g-2 mb-2">
        <div class="col-6 col-md-3">
            <div class="kpi-card-dashboard">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-card-title">Approved / Final</div>
                    <span class="kpi-cell-code">Sel A11</span>
                </div>
                <div class="kpi-card-val text-success">{{ $kpi['approved_final'] }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Laporan telah disetujui penuh</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card-dashboard">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-card-title">Rata-rata Quality</div>
                    <span class="kpi-cell-code">Sel D11</span>
                </div>
                <div class="kpi-card-val {{ $kpi['rata_rata_quality'] >= ($params->target_quality_score ?? 80) ? 'text-success' : 'text-warning' }}">
                    {{ number_format($kpi['rata_rata_quality'], 1) }}
                </div>
                <div class="text-muted small" style="font-size: 0.72rem;">Target: &ge; {{ $params->target_quality_score ?? 80 }} (WP-RA-08-I)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card-dashboard">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-card-title">Exception</div>
                    <span class="kpi-cell-code">Sel G11</span>
                </div>
                <div class="kpi-card-val text-secondary">{{ $kpi['exception'] }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Penyimpangan Rekap Isu & TL</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card-dashboard">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-card-title">Finding</div>
                    <span class="kpi-cell-code">Sel J11</span>
                </div>
                <div class="kpi-card-val text-primary">{{ $kpi['finding'] }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Temuan formal Rekap Isu & TL</div>
            </div>
        </div>
    </div>

    {{-- Baris 3 KPI: Baris 14-15 Excel (High Risk, TL Overdue, Isu Signifikan, Unit High Attention) --}}
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card-dashboard">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-card-title">High Risk</div>
                    <span class="kpi-cell-code">Sel A15</span>
                </div>
                <div class="kpi-card-val text-danger">{{ $kpi['high_risk'] }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Isu berlevel risiko TINGGI</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card-dashboard">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-card-title">TL Overdue</div>
                    <span class="kpi-cell-code">Sel D15</span>
                </div>
                <div class="kpi-card-val text-danger">{{ $kpi['tl_overdue'] }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Tindak lanjut melewati target</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card-dashboard">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-card-title">Isu Signifikan</div>
                    <span class="kpi-cell-code">Sel G15</span>
                </div>
                <div class="kpi-card-val text-warning">{{ $kpi['isu_signifikan'] }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Flag Ya (WP-RA-08-H)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card-dashboard">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-card-title">Unit / Area Perhatian</div>
                    <span class="kpi-cell-code">Sel J15</span>
                </div>
                <div class="kpi-card-val text-danger">
                    {{ $kpi['unit_perlu_perhatian_count'] }} <span class="fs-6 text-muted">unit</span>
                    <span class="fs-6 text-secondary">({{ $kpi['unit_high_attention'] }} area)</span>
                </div>
                <div class="text-muted small" style="font-size: 0.72rem;">Perlu Perhatian Khusus</div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- B & C. RINGKASAN STATUS LAPORAN (A19:B24) & SKOR PERHATIAN 13 AREA (J19:K32) --}}
    {{-- ========================================================================= --}}
    <div class="row g-3 mb-4">
        {{-- B. Tabel Ringkasan Status Laporan (Sel A19:B24) --}}
        <div class="col-md-5">
            <div class="section-card">
                <div class="section-card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark small text-uppercase">
                        <i class="bi bi-pie-chart-fill text-primary me-1"></i> Ringkasan Status Laporan (A19:B24)
                    </span>
                    <span class="kpi-cell-code">Kalender_Laporan</span>
                </div>
                <div class="p-3">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small fw-bold">Status Laporan</th>
                                <th class="small fw-bold text-center" style="width: 100px;">Jumlah</th>
                                <th class="small fw-bold text-center" style="width: 120px;">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalReports = max(1, $kpi['laporan_wajib']);
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge bg-success me-1">&bull;</span>
                                    <strong class="text-success">TEPAT WAKTU</strong>
                                </td>
                                <td class="text-center fw-bold">{{ $statusSummary['TEPAT WAKTU'] }}</td>
                                <td class="text-center text-muted small">
                                    {{ number_format(($statusSummary['TEPAT WAKTU'] / $totalReports) * 100, 0) }}%
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="badge bg-danger me-1">&bull;</span>
                                    <strong class="text-danger">TERLAMBAT</strong>
                                </td>
                                <td class="text-center fw-bold text-danger">{{ $statusSummary['TERLAMBAT'] }}</td>
                                <td class="text-center text-muted small">
                                    {{ number_format(($statusSummary['TERLAMBAT'] / $totalReports) * 100, 0) }}%
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary me-1">&bull;</span>
                                    <strong class="text-secondary">BELUM MASUK</strong>
                                </td>
                                <td class="text-center fw-bold text-muted">{{ $statusSummary['BELUM MASUK'] }}</td>
                                <td class="text-center text-muted small">
                                    {{ number_format(($statusSummary['BELUM MASUK'] / $totalReports) * 100, 0) }}%
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="badge bg-primary me-1">&bull;</span>
                                    <strong class="text-primary">APPROVED/FINAL</strong>
                                </td>
                                <td class="text-center fw-bold text-primary">{{ $statusSummary['APPROVED/FINAL'] }}</td>
                                <td class="text-center text-muted small">
                                    {{ number_format(($statusSummary['APPROVED/FINAL'] / $totalReports) * 100, 0) }}%
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="badge bg-warning text-dark me-1">&bull;</span>
                                    <strong class="text-warning">REVISION REQUIRED</strong>
                                </td>
                                <td class="text-center fw-bold text-warning">{{ $statusSummary['REVISION REQUIRED'] }}</td>
                                <td class="text-center text-muted small">
                                    {{ number_format(($statusSummary['REVISION REQUIRED'] / $totalReports) * 100, 0) }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-3 text-end">
                        <a href="{{ route('pelaporan-bulanan.kalender', ['periode' => $periode]) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Kalender Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- C. Panel Skor Perhatian 13 Area (Sel J19:K32) --}}
        <div class="col-md-7">
            <div class="section-card">
                <div class="section-card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark small text-uppercase">
                        <i class="bi bi-bar-chart-line-fill text-danger me-1"></i> Skor Perhatian 13 Area Risiko (J19:K32)
                    </span>
                    <a href="{{ route('pelaporan-bulanan.tren-isu', ['periode' => $periode]) }}" class="small text-decoration-none text-primary">
                        Detail Tren Isu <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="p-3">
                    <div class="row g-2">
                        @foreach($trenIsuRows as $areaRow)
                        @php
                            $isHigh = $areaRow['status'] === 'HIGH ATTENTION';
                            $isPantau = $areaRow['status'] === 'PANTAU';
                            $color = $isHigh ? '#ef4444' : ($isPantau ? '#f59e0b' : '#10b981');
                        @endphp
                        <div class="col-md-6">
                            <div class="border rounded p-2 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-semibold text-dark">{{ $areaRow['area'] }}</span>
                                    <span class="badge {{ $isHigh ? 'bg-danger' : ($isPantau ? 'bg-warning text-dark' : 'bg-success') }}" style="font-size: 0.68rem;">
                                        {{ $areaRow['skor_perhatian'] }} pt
                                    </span>
                                </div>
                                <div class="score-bar-mini">
                                    <div class="score-bar-mini-fill" style="width: {{ $areaRow['skor_perhatian'] }}%; background-color: {{ $color }};"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1 text-muted" style="font-size: 0.68rem;">
                                    <span>Isu: {{ $areaRow['isu_bulan_ini'] }} (Δ {{ $areaRow['perubahan'] > 0 ? '+'.$areaRow['perubahan'] : $areaRow['perubahan'] }})</span>
                                    <span class="fw-bold" style="color: {{ $color }};">{{ $areaRow['status'] }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- D. TABEL MONITORING PERHATIAN PER UNIT / CABANG (Sel A27:H51 Sheet Dashboard) --}}
    {{-- ========================================================================= --}}
    <div class="section-card mb-4">
        <div class="section-card-header d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark small text-uppercase">
                <i class="bi bi-building-exclamation text-warning me-1"></i> Monitoring Perhatian Unit Kerja / Cabang (A27:H51)
            </span>
            <span class="text-muted small">
                Target Quality Score: <strong>&ge; {{ $params->target_quality_score ?? 80 }}</strong> | Rumus: High Risk &gt; 0 &or; Overdue &gt; 0 &or; Signifikan &gt; 0 &or; Quality &lt; Target
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Unit (A)</th>
                        <th>RA (B)</th>
                        <th class="text-center">Status Final (C)</th>
                        <th class="text-center">Quality (D)</th>
                        <th class="text-center">High Risk (E)</th>
                        <th class="text-center">Overdue (F)</th>
                        <th class="text-center">Signifikan (G)</th>
                        <th class="text-center">Perhatian (H)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($unitMonitoring as $idx => $um)
                    <tr class="{{ $um['perhatian'] === 'PERLU PERHATIAN' ? 'table-warning-subtle' : '' }}">
                        <td class="text-center text-muted small">{{ $idx + 1 }}</td>
                        <td class="fw-bold">
                            <span class="badge bg-light text-dark border me-1">{{ $um['kode_unit'] }}</span>
                            {{ $um['unit'] }}
                        </td>
                        <td>{{ $um['ra'] }}</td>
                        <td class="text-center">
                            @if($um['status_final'] === 'APPROVED/FINAL')
                                <span class="badge bg-success">APPROVED/FINAL</span>
                            @else
                                <span class="badge bg-secondary">{{ $um['status_final'] }}</span>
                            @endif
                        </td>
                        <td class="text-center fw-bold {{ $um['quality'] >= ($params->target_quality_score ?? 80) ? 'text-success' : 'text-danger' }}">
                            {{ number_format($um['quality'], 1) }}
                        </td>
                        <td class="text-center">
                            @if($um['high_risk'] > 0)
                                <span class="badge bg-danger rounded-pill">{{ $um['high_risk'] }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($um['overdue'] > 0)
                                <span class="badge bg-danger rounded-pill">{{ $um['overdue'] }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($um['isu_signifikan'] > 0)
                                <span class="badge bg-warning text-dark rounded-pill fw-bold">{{ $um['isu_signifikan'] }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($um['perhatian'] === 'PERLU PERHATIAN')
                                <span class="badge badge-perlu-perhatian px-2 py-1 shadow-sm">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> PERLU PERHATIAN
                                </span>
                            @else
                                <span class="badge badge-normal-unit px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i> NORMAL
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            Tidak ada data laporan untuk periode <strong>{{ $periode }}</strong>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
