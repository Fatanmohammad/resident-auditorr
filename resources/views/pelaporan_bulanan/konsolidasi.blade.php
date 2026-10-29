@extends('layouts.app')
@section('title', 'Register Konsolidasi Laporan (WP-RA-08-J) - SOP 08')

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
    .badge-siap { background-color: #10b981; color: #fff; font-weight: 700; }
    .badge-belum-siap { background-color: #f59e0b; color: #fff; font-weight: 600; }
    .table-responsive {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
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
        text-align: center;
        vertical-align: middle;
    }
    .table-section-identity { background: #f8fafc !important; }
    .table-section-kegiatan { background: #f0fdf4 !important; }
    .table-section-isu { background: #fef2f2 !important; }
    .table-section-status { background: #eff6ff !important; }
    .table tbody td {
        font-size: 0.82rem;
        vertical-align: middle;
        white-space: nowrap;
    }
    .table tfoot th {
        background: #e2e8f0;
        font-size: 0.78rem;
        font-weight: 800;
        color: #1e293b;
        vertical-align: middle;
        white-space: nowrap;
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
                    <li class="breadcrumb-item active" aria-current="page">Konsolidasi (WP-RA-08-J)</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-diagram-3 text-primary"></i> Register Konsolidasi Laporan Bagian RA
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 fw-semibold">WP-RA-08-J</span>
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Agregasi real-time 21 kolom (A s.d. U) dari Kalender Laporan, Rekap Kegiatan, dan Rekap Isu & TL sesuai rumus baris 6 sheet Konsolidasi.
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

    {{-- Filter Card --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('pelaporan-bulanan.konsolidasi') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Periode Konsolidasi (Bulan)</label>
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
                <a href="{{ route('pelaporan-bulanan.konsolidasi') }}" class="btn btn-outline-secondary btn-sm px-3" title="Reset Filter">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- KPI Cards Summary --}}
    @php
        $totalLaporan = count($konsolidasiRows);
        $totalSiap = collect($konsolidasiRows)->where('status_konsolidasi', 'SIAP KONSOLIDASI')->count();
        $totalBelumSiap = $totalLaporan - $totalSiap;
        $totalKegiatan = collect($konsolidasiRows)->sum('kegiatan');
        $totalSampel = collect($konsolidasiRows)->sum('sampel');
        $totalDiuji = collect($konsolidasiRows)->sum('diuji');
        $totalHighRisk = collect($konsolidasiRows)->sum('high_risk');
        $totalOverdue = collect($konsolidasiRows)->sum('tl_overdue');
        $totalSignifikan = collect($konsolidasiRows)->sum('isu_signifikan');
    @endphp

    <div class="row g-2 mb-3">
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Total Unit Lapor</div>
                <div class="kpi-value text-primary">{{ $totalLaporan }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Periode {{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Siap Konsolidasi</div>
                <div class="kpi-value text-success">{{ $totalSiap }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Final & Lengkap 100%</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Belum Siap</div>
                <div class="kpi-value text-warning">{{ $totalBelumSiap }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Review / <100%</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Kegiatan Diuji</div>
                <div class="kpi-value text-info">{{ $totalDiuji }} <span class="fs-6 text-muted">/ {{ $totalSampel }}</span></div>
                <div class="text-muted small" style="font-size: 0.72rem;">Realisasi Uji Sampel</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">High Risk & Overdue</div>
                <div class="kpi-value text-danger">{{ $totalHighRisk }} <span class="fs-6 text-muted">| {{ $totalOverdue }}</span></div>
                <div class="text-muted small" style="font-size: 0.72rem;">High Risk / TL Lewat</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Isu Signifikan</div>
                <div class="kpi-value text-danger">{{ $totalSignifikan }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Eskalasi Manajemen</div>
            </div>
        </div>
    </div>

    {{-- Main 21-Column Table --}}
    <div class="table-responsive shadow-sm">
        <table class="table table-hover table-bordered mb-0">
            <thead>
                {{-- Super Headers --}}
                <tr>
                    <th colspan="11" class="table-section-identity text-primary">
                        <i class="bi bi-file-earmark-text me-1"></i> IDENTITAS & STATUS LAPORAN (Kalender_Laporan Kolom A s.d. K)
                    </th>
                    <th colspan="4" class="table-section-kegiatan text-success">
                        <i class="bi bi-activity me-1"></i> REKAP KEGIATAN (Kolom L s.d. O)
                    </th>
                    <th colspan="5" class="table-section-isu text-danger">
                        <i class="bi bi-shield-slash me-1"></i> REKAP ISU & TL (Kolom P s.d. T)
                    </th>
                    <th class="table-section-status text-primary">
                        <i class="bi bi-check-all me-1"></i> KONSOLIDASI (U)
                    </th>
                </tr>
                {{-- Column Headers A s.d. U --}}
                <tr>
                    <th title="Kolom A - Report ID">Report ID (A)</th>
                    <th title="Kolom B - Periode">Periode (B)</th>
                    <th title="Kolom C - Kode Unit">Kode (C)</th>
                    <th title="Kolom D - Nama Unit">Nama Unit (D)</th>
                    <th title="Kolom E - Resident Auditor">Resident Auditor (E)</th>
                    <th title="Kolom F - Tanggal Submit">Tgl Submit (F)</th>
                    <th title="Kolom G - Ketepatan">Ketepatan (G)</th>
                    <th title="Kolom H - Kelengkapan">Kelengkapan (H)</th>
                    <th title="Kolom I - Status Review">Review (I)</th>
                    <th title="Kolom J - Status Final">Status Final (J)</th>
                    <th title="Kolom K - Quality Score">Quality (K)</th>

                    {{-- 4 Kolom Kegiatan --}}
                    <th title="Kolom L - COUNTIF Rekap_Kegiatan Report ID">Kegiatan (L)</th>
                    <th title="Kolom M - SUMIF Populasi">Populasi (M)</th>
                    <th title="Kolom N - SUMIF Sampel Terpilih">Sampel (N)</th>
                    <th title="Kolom O - SUMIF Sudah Diuji">Diuji (O)</th>

                    {{-- 5 Kolom Isu & TL --}}
                    <th title="Kolom P - COUNTIFS Exception">Exception (P)</th>
                    <th title="Kolom Q - COUNTIFS Finding">Finding (Q)</th>
                    <th title="Kolom R - COUNTIFS High Risk">High Risk (R)</th>
                    <th title="Kolom S - COUNTIFS TL Overdue">TL Overdue (S)</th>
                    <th title="Kolom T - COUNTIFS Isu Signifikan">Isu Signifikan (T)</th>

                    {{-- Kolom U Status Konsolidasi --}}
                    <th title="Kolom U - Status Konsolidasi">Status Konsolidasi (U)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($konsolidasiRows as $row)
                <tr>
                    {{-- Identitas & Status Laporan --}}
                    <td class="fw-bold">
                        <a href="{{ route('pelaporan-bulanan.laporan-bulanan', $row['report_id']) }}" class="text-decoration-none text-primary" title="Lihat Laporan Bulanan">
                            {{ $row['report_id'] }}
                        </a>
                    </td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row['periode'])->format('Y-m') }}</td>
                    <td class="text-center"><span class="badge bg-light text-dark border">{{ $row['kode_unit'] }}</span></td>
                    <td class="fw-semibold">{{ $row['nama_unit'] }}</td>
                    <td>{{ $row['resident_auditor'] }}</td>
                    <td class="text-center">
                        @if($row['tanggal_submit'])
                            <span class="text-success"><i class="bi bi-check-circle me-1"></i>{{ \Carbon\Carbon::parse($row['tanggal_submit'])->format('d/m/Y') }}</span>
                        @else
                            <span class="text-muted fst-italic">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($row['ketepatan'] === 'TEPAT WAKTU')
                            <span class="badge bg-success-subtle text-success border border-success-subtle">TEPAT WAKTU</span>
                        @elseif($row['ketepatan'] === 'TERLAMBAT')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">TERLAMBAT</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ $row['ketepatan'] }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <span class="fw-bold {{ $row['kelengkapan'] >= 100 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($row['kelengkapan'], 0) }}%
                            </span>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-secondary border">{{ $row['status_review'] }}</span>
                    </td>
                    <td class="text-center">
                        @if($row['status_final'] === 'APPROVED/FINAL')
                            <span class="badge bg-success"><i class="bi bi-check2-all me-1"></i>APPROVED/FINAL</span>
                        @elseif($row['status_final'] === 'REVISION REQUIRED')
                            <span class="badge bg-danger">REVISION REQUIRED</span>
                        @else
                            <span class="badge bg-warning text-dark">{{ $row['status_final'] }}</span>
                        @endif
                    </td>
                    <td class="text-center fw-bold text-primary">
                        {{ number_format($row['quality_score'], 1) }}
                    </td>

                    {{-- 4 Kolom Rekap Kegiatan --}}
                    <td class="text-center fw-semibold">{{ $row['kegiatan'] }}</td>
                    <td class="text-center text-muted">{{ number_format($row['populasi']) }}</td>
                    <td class="text-center text-muted">{{ number_format($row['sampel']) }}</td>
                    <td class="text-center fw-semibold text-success">{{ number_format($row['diuji']) }}</td>

                    {{-- 5 Kolom Rekap Isu & TL --}}
                    <td class="text-center text-muted">{{ $row['exception'] }}</td>
                    <td class="text-center text-muted">{{ $row['finding'] }}</td>
                    <td class="text-center">
                        @if($row['high_risk'] > 0)
                            <span class="badge bg-danger rounded-pill">{{ $row['high_risk'] }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($row['tl_overdue'] > 0)
                            <span class="badge bg-danger rounded-pill">{{ $row['tl_overdue'] }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($row['isu_signifikan'] > 0)
                            <span class="badge bg-warning text-dark fw-bold rounded-pill">{{ $row['isu_signifikan'] }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>

                    {{-- Kolom U Status Konsolidasi --}}
                    <td class="text-center">
                        @if($row['status_konsolidasi'] === 'SIAP KONSOLIDASI')
                            <span class="badge badge-siap px-2 py-1 shadow-sm">
                                <i class="bi bi-check-circle-fill me-1"></i> SIAP KONSOLIDASI
                            </span>
                        @else
                            <span class="badge badge-belum-siap px-2 py-1">
                                <i class="bi bi-clock-history me-1"></i> BELUM SIAP
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="21" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                        Tidak ada data konsolidasi laporan untuk periode <strong>{{ $periode }}</strong>.
                        @if($isAdmin)
                            <div class="mt-2">
                                <a href="{{ route('pelaporan-bulanan.kalender.create', ['periode' => $periode]) }}" class="btn btn-outline-primary btn-sm rounded-pill">
                                    <i class="bi bi-magic me-1"></i> Inisiasi Kalender Laporan Sekarang
                                </a>
                            </div>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(count($konsolidasiRows) > 0)
            <tfoot>
                <tr>
                    <th colspan="7" class="text-end">TOTAL / RATA-RATA:</th>
                    <th class="text-center">{{ number_format(collect($konsolidasiRows)->avg('kelengkapan'), 1) }}%</th>
                    <th colspan="2"></th>
                    <th class="text-center">{{ number_format(collect($konsolidasiRows)->where('quality_score', '>', 0)->avg('quality_score') ?: 0, 1) }}</th>
                    <th class="text-center">{{ $totalKegiatan }}</th>
                    <th class="text-center">{{ number_format(collect($konsolidasiRows)->sum('populasi')) }}</th>
                    <th class="text-center">{{ number_format($totalSampel) }}</th>
                    <th class="text-center text-success">{{ number_format($totalDiuji) }}</th>
                    <th class="text-center">{{ collect($konsolidasiRows)->sum('exception') }}</th>
                    <th class="text-center">{{ collect($konsolidasiRows)->sum('finding') }}</th>
                    <th class="text-center text-danger">{{ $totalHighRisk }}</th>
                    <th class="text-center text-danger">{{ $totalOverdue }}</th>
                    <th class="text-center text-warning">{{ $totalSignifikan }}</th>
                    <th class="text-center text-success fw-bold">{{ $totalSiap }} / {{ $totalLaporan }} SIAP</th>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- Footnote Formula Notice --}}
    <div class="card mt-3 bg-light border-0 shadow-sm">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center gap-2 small text-muted">
                <i class="bi bi-info-circle-fill text-primary fs-6"></i>
                <div>
                    <strong>Petunjuk Formula Excel (WP-RA-08-J):</strong>
                    Status Konsolidasi (Kolom U) otomatis <code>SIAP KONSOLIDASI</code> jika Status Final (J) == <code>"APPROVED/FINAL"</code> DAN Kelengkapan (H) == <code>100%</code>.
                    Kolom L s.d. O diagregasikan otomatis dari <code>Rekap_Kegiatan</code>, dan Kolom P s.d. T diagregasikan otomatis dari <code>Rekap_Isu_TL</code>.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
