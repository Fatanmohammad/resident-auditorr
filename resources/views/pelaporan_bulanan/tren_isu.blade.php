@extends('layouts.app')
@section('title', 'Matriks Tren Temuan & Isu (WP-RA-08-K) - SOP 08')

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
        font-size: 0.73rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 0.35rem;
    }
    .kpi-value {
        font-size: 1.55rem;
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
    .badge-high-att { background-color: #ef4444; color: #fff; font-weight: 700; }
    .badge-pantau { background-color: #f59e0b; color: #fff; font-weight: 600; }
    .badge-normal { background-color: #10b981; color: #fff; font-weight: 600; }
    .table-responsive {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
    }
    .table thead th {
        background: #f1f5f9;
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #334155;
        border-bottom: 2px solid #cbd5e1;
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
    }
    .table tbody td {
        font-size: 0.84rem;
        vertical-align: middle;
        white-space: nowrap;
    }
    .table tfoot th {
        background: #e2e8f0;
        font-size: 0.8rem;
        font-weight: 800;
        color: #1e293b;
        vertical-align: middle;
        white-space: nowrap;
    }
    .score-bar-bg {
        width: 100%;
        background-color: #e2e8f0;
        border-radius: 6px;
        height: 8px;
        overflow: hidden;
    }
    .score-bar-fill {
        height: 100%;
        border-radius: 6px;
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
                    <li class="breadcrumb-item active" aria-current="page">Tren Isu (WP-RA-08-K)</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-graph-up-arrow text-danger"></i> Matriks Tren Temuan, TL & Isu Berulang
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-6 fw-semibold">WP-RA-08-K</span>
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Analisis perbandingan tren isu 13 Area Standar antara Bulan Ini vs Bulan Lalu, skor perhatian risiko, dan status perhatian audit.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-printer me-1"></i> Cetak / Export
            </button>
            <a href="{{ route('pelaporan-bulanan.dashboard', ['periode' => $periode]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard Eksekutif
            </a>
        </div>
    </div>

    {{-- Sub-Menu Navigation Tabs --}}
    @include('pelaporan_bulanan.tabs')

    @php
        $carbonBulanIni = \Carbon\Carbon::parse($periode . '-01');
        $namaBulanIni = $carbonBulanIni->translatedFormat('F Y');
        $namaBulanLalu = $carbonBulanIni->copy()->subMonth()->translatedFormat('F Y');

        $totalIsuIni = collect($trenRows)->sum('isu_bulan_ini');
        $totalIsuLalu = collect($trenRows)->sum('isu_bulan_lalu');
        $netPerubahan = $totalIsuIni - $totalIsuLalu;
        $totalHighRisk = collect($trenRows)->sum('high_risk');
        $totalOverdue = collect($trenRows)->sum('tl_overdue');
        $totalBerulang = collect($trenRows)->sum('berulang');
        $totalSignifikan = collect($trenRows)->sum('isu_signifikan');
        $countHighAtt = collect($trenRows)->where('status', 'HIGH ATTENTION')->count();
        $countPantau = collect($trenRows)->where('status', 'PANTAU')->count();
        $countNormal = collect($trenRows)->where('status', 'NORMAL')->count();
    @endphp

    {{-- Filter Card --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('pelaporan-bulanan.tren-isu') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Periode Analisis (Bulan Ini)</label>
                <input type="month" name="periode" class="form-control form-control-sm" value="{{ $periode }}">
            </div>
            @if(!$isRa)
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Filter Unit Kerja / Cabang</label>
                <select name="kode_unit" class="form-select form-select-sm">
                    <option value="">-- Seluruh Cabang (Konsolidasi Global) --</option>
                    @foreach($masterUnits as $u)
                        <option value="{{ $u->kode_unit }}" {{ request('kode_unit') == $u->kode_unit ? 'selected' : '' }}>
                            {{ $u->kode_unit }} - {{ $u->nama_unit }} ({{ $u->resident_auditor }})
                        </option>
                    @endforeach
                </select>
            </div>
            @else
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Unit Kerja Anda</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ auth()->user()->unit_kerja ?? 'Cabang Terisolasi' }}" readonly>
            </div>
            @endif
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm">
                    <i class="bi bi-funnel me-1"></i> Terapkan
                </button>
                <a href="{{ route('pelaporan-bulanan.tren-isu') }}" class="btn btn-outline-secondary btn-sm px-3" title="Reset Filter">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- KPI Cards Summary --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Isu Bulan Ini</div>
                <div class="kpi-value text-primary">{{ $totalIsuIni }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">{{ $namaBulanIni }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Isu Bulan Lalu</div>
                <div class="kpi-value text-secondary">{{ $totalIsuLalu }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">{{ $namaBulanLalu }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Perubahan Bersih</div>
                <div class="kpi-value {{ $netPerubahan > 0 ? 'text-danger' : ($netPerubahan < 0 ? 'text-success' : 'text-muted') }}">
                    {{ $netPerubahan > 0 ? '+' : '' }}{{ $netPerubahan }}
                </div>
                <div class="text-muted small" style="font-size: 0.72rem;">{{ $netPerubahan > 0 ? 'Kenaikan Isu' : ($netPerubahan < 0 ? 'Penurunan Isu' : 'Stabil') }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">High Attention Area</div>
                <div class="kpi-value text-danger">{{ $countHighAtt }} <span class="fs-6 text-muted">/ 13</span></div>
                <div class="text-muted small" style="font-size: 0.72rem;">Skor &ge; {{ $params->ambang_high_attention ?? 60 }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Area Pantau</div>
                <div class="kpi-value text-warning">{{ $countPantau }} <span class="fs-6 text-muted">/ 13</span></div>
                <div class="text-muted small" style="font-size: 0.72rem;">Skor 30 &ndash; 59</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Area Normal</div>
                <div class="kpi-value text-success">{{ $countNormal }} <span class="fs-6 text-muted">/ 13</span></div>
                <div class="text-muted small" style="font-size: 0.72rem;">Skor &lt; 30</div>
            </div>
        </div>
    </div>

    {{-- Main 10-Column Table --}}
    <div class="table-responsive shadow-sm">
        <table class="table table-hover table-bordered mb-0">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 200px;">Area Risiko (A)</th>
                    <th colspan="3" class="bg-primary-subtle text-primary border-bottom-0">Tren Jumlah Isu (B s.d. D)</th>
                    <th colspan="4" class="bg-danger-subtle text-danger border-bottom-0">Komponen Risiko Bulan Ini (E s.d. H)</th>
                    <th colspan="2" class="bg-warning-subtle text-dark border-bottom-0">Evaluasi Perhatian (I s.d. J)</th>
                </tr>
                <tr>
                    <th title="Kolom B - Isu Bulan Ini">Bulan Ini (B)<br><small class="text-muted fw-normal">({{ $namaBulanIni }})</small></th>
                    <th title="Kolom C - Isu Bulan Lalu">Bulan Lalu (C)<br><small class="text-muted fw-normal">({{ $namaBulanLalu }})</small></th>
                    <th title="Kolom D - B - C">Perubahan (D)</th>
                    <th title="Kolom E - High Risk">High Risk (E)</th>
                    <th title="Kolom F - TL Overdue">TL Overdue (F)</th>
                    <th title="Kolom G - Berulang">Berulang (G)</th>
                    <th title="Kolom H - Isu Signifikan">Signifikan (H)</th>
                    <th title="Kolom I - Skor Perhatian (0-100)" style="width: 170px;">Skor Perhatian (I)</th>
                    <th title="Kolom J - Status Perhatian">Status (J)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trenRows as $idx => $row)
                @php
                    $isHigh = $row['status'] === 'HIGH ATTENTION';
                    $isPantau = $row['status'] === 'PANTAU';
                    $barColor = $isHigh ? '#ef4444' : ($isPantau ? '#f59e0b' : '#10b981');
                @endphp
                <tr class="{{ $isHigh ? 'table-danger' : ($isPantau ? 'table-warning' : '') }}">
                    {{-- Kolom A: Area --}}
                    <td class="fw-bold">
                        <span class="text-muted me-1 small">{{ $idx + 1 }}.</span>
                        {{ $row['area'] }}
                    </td>

                    {{-- Kolom B: Isu Bulan Ini --}}
                    <td class="text-center fw-semibold fs-6 {{ $row['isu_bulan_ini'] > 0 ? 'text-dark' : 'text-muted' }}">
                        {{ $row['isu_bulan_ini'] }}
                    </td>

                    {{-- Kolom C: Isu Bulan Lalu --}}
                    <td class="text-center text-muted">
                        {{ $row['isu_bulan_lalu'] }}
                    </td>

                    {{-- Kolom D: Perubahan --}}
                    <td class="text-center fw-bold">
                        @if($row['perubahan'] > 0)
                            <span class="text-danger"><i class="bi bi-arrow-up-right me-1"></i>+{{ $row['perubahan'] }}</span>
                        @elseif($row['perubahan'] < 0)
                            <span class="text-success"><i class="bi bi-arrow-down-right me-1"></i>{{ $row['perubahan'] }}</span>
                        @else
                            <span class="text-muted"><i class="bi bi-dash me-1"></i>0</span>
                        @endif
                    </td>

                    {{-- Kolom E: High Risk --}}
                    <td class="text-center">
                        @if($row['high_risk'] > 0)
                            <span class="badge bg-danger rounded-pill px-2">{{ $row['high_risk'] }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>

                    {{-- Kolom F: TL Overdue --}}
                    <td class="text-center">
                        @if($row['tl_overdue'] > 0)
                            <span class="badge bg-danger rounded-pill px-2">{{ $row['tl_overdue'] }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>

                    {{-- Kolom G: Berulang --}}
                    <td class="text-center">
                        @if($row['berulang'] > 0)
                            <span class="badge bg-warning text-dark rounded-pill px-2">{{ $row['berulang'] }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>

                    {{-- Kolom H: Signifikan --}}
                    <td class="text-center">
                        @if($row['isu_signifikan'] > 0)
                            <span class="badge bg-danger rounded-pill px-2">{{ $row['isu_signifikan'] }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>

                    {{-- Kolom I: Skor Perhatian (0-100) --}}
                    <td>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="fw-bold fs-6" style="color: {{ $barColor }};">{{ $row['skor_perhatian'] }}</span>
                            <span class="text-muted small" style="font-size: 0.7rem;">/ 100</span>
                        </div>
                        <div class="score-bar-bg">
                            <div class="score-bar-fill" style="width: {{ $row['skor_perhatian'] }}%; background-color: {{ $barColor }};"></div>
                        </div>
                    </td>

                    {{-- Kolom J: Status --}}
                    <td class="text-center">
                        @if($row['status'] === 'HIGH ATTENTION')
                            <span class="badge badge-high-att px-2 py-1 shadow-sm">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> HIGH ATTENTION
                            </span>
                        @elseif($row['status'] === 'PANTAU')
                            <span class="badge badge-pantau px-2 py-1">
                                <i class="bi bi-eye-fill me-1"></i> PANTAU
                            </span>
                        @else
                            <span class="badge badge-normal px-2 py-1">
                                <i class="bi bi-check-circle me-1"></i> NORMAL
                            </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>TOTAL LINTAS AREA:</th>
                    <th class="text-center fs-6 text-primary">{{ $totalIsuIni }}</th>
                    <th class="text-center text-secondary">{{ $totalIsuLalu }}</th>
                    <th class="text-center {{ $netPerubahan > 0 ? 'text-danger' : ($netPerubahan < 0 ? 'text-success' : '') }}">
                        {{ $netPerubahan > 0 ? '+' : '' }}{{ $netPerubahan }}
                    </th>
                    <th class="text-center text-danger">{{ $totalHighRisk }}</th>
                    <th class="text-center text-danger">{{ $totalOverdue }}</th>
                    <th class="text-center text-warning">{{ $totalBerulang }}</th>
                    <th class="text-center text-danger">{{ $totalSignifikan }}</th>
                    <th class="text-center">Rata-rata: {{ number_format(collect($trenRows)->avg('skor_perhatian'), 1) }}</th>
                    <th class="text-center text-danger fw-bold">{{ $countHighAtt }} HIGH | {{ $countPantau }} PANTAU</th>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Formula Explanation Card --}}
    <div class="card mt-3 bg-light border-0 shadow-sm">
        <div class="card-body py-2 px-3">
            <div class="row align-items-center">
                <div class="col-md-8 small text-muted">
                    <i class="bi bi-calculator text-primary me-1"></i>
                    <strong>Formula Skor Perhatian (Kolom I):</strong>
                    <code>=MIN(100, (HighRisk &times; 25) + (Overdue &times; 15) + (Berulang &times; 10) + (MAX(Perubahan, 0) &times; 5))</code>
                </div>
                <div class="col-md-4 text-md-end small text-muted">
                    <strong>Ambang Batas Status (Kolom J):</strong>
                    <span class="badge badge-high-att">&ge; {{ $params->ambang_high_attention ?? 60 }}</span>
                    <span class="badge badge-pantau">&ge; 30</span>
                    <span class="badge badge-normal">&lt; 30</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
