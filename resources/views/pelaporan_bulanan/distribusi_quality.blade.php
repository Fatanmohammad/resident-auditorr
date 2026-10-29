@extends('layouts.app')
@section('title', 'Distribusi, Arsip & Quality Score')

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
    .badge-sangat-baik { background: #14532d; color: #ffffff; font-weight: 700; }
    .badge-baik { background: #22c55e; color: #ffffff; font-weight: 700; }
    .badge-perbaikan { background: #eab308; color: #000000; font-weight: 700; }
    .badge-tidak-memadai { background: #ef4444; color: #ffffff; font-weight: 700; }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Header Modul --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a8a;">
                <i class="bi bi-award me-2 text-primary"></i>Distribusi, Arsip &amp; Quality Score
            </h4>
            <p class="text-muted small mb-0">
                Kalkulasi 7 Komponen Skor Mutu Laporan (Skala 0–100), Target KPI Kualitas, Penyesuaian Reviewer, serta Pencatatan Arsip &amp; Distribusi
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
        $totalDist = $distribusiList->count();
        $avgScore = $totalDist > 0 ? round($distribusiList->avg('quality_score'), 1) : 0;
        $sangatBaikCount = $distribusiList->where('kategori', 'SANGAT BAIK')->count();
        $baikCount = $distribusiList->where('kategori', 'BAIK')->count();
        $arsipCount = $distribusiList->where('status_arsip', 'TERARSIP LENGKAP')->count();
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-primary">
                <div class="kpi-title">Rata-rata Quality Score</div>
                <div class="d-flex align-items-baseline gap-1">
                    <span class="kpi-val text-primary">{{ $avgScore }}</span>
                    <span class="text-muted small">/ 100</span>
                </div>
                <small class="text-muted" style="font-size: 0.72rem;">Target KPI: {{ $params->target_quality_score ?? 80 }}</small>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-success">
                <div class="kpi-title">Sangat Baik (&ge; 90)</div>
                <div class="kpi-val text-success">{{ $sangatBaikCount }}</div>
                <small class="text-muted" style="font-size: 0.72rem;">Laporan Mutu Tinggi</small>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-info">
                <div class="kpi-title">Baik (&ge; 80)</div>
                <div class="kpi-val text-info">{{ $baikCount }}</div>
                <small class="text-muted" style="font-size: 0.72rem;">Memenuhi Target KPI</small>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-warning">
                <div class="kpi-title">Di Bawah Target (&lt; 80)</div>
                <div class="kpi-val text-warning">{{ $distribusiList->whereIn('kategori', ['PERLU PERBAIKAN', 'TIDAK MEMADAI'])->count() }}</div>
                <small class="text-muted" style="font-size: 0.72rem;">Perlu Pembinaan Mutu</small>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="kpi-card border-start border-4 border-dark">
                <div class="kpi-title">Terarsip Lengkap</div>
                <div class="kpi-val text-dark">{{ $arsipCount }}</div>
                <small class="text-muted" style="font-size: 0.72rem;">{{ $totalDist > 0 ? round(($arsipCount / $totalDist) * 100) : 0 }}% Dari Total Laporan</small>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="filter-card">
        <form action="{{ route('pelaporan-bulanan.distribusi-quality') }}" method="GET" class="row g-2 align-items-end">
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
                <label for="kategori" class="form-label small fw-bold text-muted mb-1">Filter Kategori Mutu</label>
                <select name="kategori" id="kategori" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Kategori --</option>
                    <option value="SANGAT BAIK" {{ request('kategori') == 'SANGAT BAIK' ? 'selected' : '' }}>SANGAT BAIK (&ge;90)</option>
                    <option value="BAIK" {{ request('kategori') == 'BAIK' ? 'selected' : '' }}>BAIK (&ge;80)</option>
                    <option value="PERLU PERBAIKAN" {{ request('kategori') == 'PERLU PERBAIKAN' ? 'selected' : '' }}>PERLU PERBAIKAN (&ge;60)</option>
                    <option value="TIDAK MEMADAI" {{ request('kategori') == 'TIDAK MEMADAI' ? 'selected' : '' }}>TIDAK MEMADAI (&lt;60)</option>
                </select>
            </div>
            <div class="col-md-3 text-end">
                <button type="submit" class="btn btn-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('pelaporan-bulanan.distribusi-quality', ['periode' => $periode]) }}" class="btn btn-light btn-sm rounded-pill px-2 text-muted">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Main Table 18 Kolom (A s.d. R Sesuai Sheet Distribusi_Quality) --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-table me-2 text-warning"></i>Tabel Distribusi, Arsip & Quality Score (18 Kolom A s.d. R)
            </h6>
            <span class="badge bg-light text-secondary border">
                7 Bobot Skor: Tepat Waktu(20), Kelengkapan(20), Akurasi(15), Analisis(15), Bukti(10), TL(10), Eskalasi(10)
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 45px;">No</th>
                        <th>Report ID</th>
                        <th>Tgl Distribusi</th>
                        <th>Penerima</th>
                        <th>Media Arsip</th>
                        <th>Versi</th>
                        {{-- 7 Komponen Skor Dasar --}}
                        <th class="text-center bg-light">Tepat Waktu [20]</th>
                        <th class="text-center bg-light">Lengkap [20]</th>
                        <th class="text-center bg-light">Akurasi [15]</th>
                        <th class="text-center bg-light">Analisis [15]</th>
                        <th class="text-center bg-light">Bukti [10]</th>
                        <th class="text-center bg-light">TL [10]</th>
                        <th class="text-center bg-light">Eskalasi [10]</th>
                        {{-- Skor & Kategori --}}
                        <th class="text-center">Skor Dasar</th>
                        <th class="text-center">Adjustment</th>
                        <th class="text-center text-primary">Quality Score</th>
                        <th class="text-center">Kategori</th>
                        <th>Catatan KPI</th>
                        <th class="text-center">Status Arsip</th>
                        @if(!$isRa)
                            <th class="text-center" style="width: 70px;">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($distribusiList as $idx => $item)
                        @php
                            $kal = $item->kalender;
                            $kategoriBadge = match($item->kategori) {
                                'SANGAT BAIK' => 'badge-sangat-baik',
                                'BAIK' => 'badge-baik',
                                'PERLU PERBAIKAN' => 'badge-perbaikan',
                                default => 'badge-tidak-memadai',
                            };
                            $statusArsipBadge = match($item->status_arsip) {
                                'TERARSIP LENGKAP' => 'bg-success text-white',
                                'DALAM PROSES' => 'bg-info text-white',
                                default => 'bg-secondary text-white',
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
                            <td class="text-nowrap font-monospace small">
                                {{ $item->tanggal_distribusi ? \Carbon\Carbon::parse($item->tanggal_distribusi)->format('d/m/y') : '-' }}
                            </td>
                            <td>
                                <div class="text-truncate small" style="max-width: 110px;" title="{{ $item->penerima }}">
                                    {{ $item->penerima ?: '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="text-truncate text-secondary small" style="max-width: 100px;" title="{{ $item->media_arsip }}">
                                    {{ $item->media_arsip ?: '-' }}
                                </div>
                            </td>
                            <td class="font-monospace small">{{ $item->versi ?: '-' }}</td>

                            {{-- 7 Komponen Nilai --}}
                            <td class="text-center font-monospace {{ $item->skor_tepat_waktu == 20 ? 'text-success fw-bold' : 'text-danger' }}">
                                {{ (float)$item->skor_tepat_waktu }}
                            </td>
                            <td class="text-center font-monospace {{ $item->skor_kelengkapan == 20 ? 'text-success fw-bold' : 'text-warning' }}">
                                {{ (float)$item->skor_kelengkapan }}
                            </td>
                            <td class="text-center font-monospace">{{ (float)$item->skor_akurasi }}</td>
                            <td class="text-center font-monospace">{{ (float)$item->skor_analisis }}</td>
                            <td class="text-center font-monospace">{{ (float)$item->skor_bukti }}</td>
                            <td class="text-center font-monospace">{{ (float)$item->skor_tindak_lanjut }}</td>
                            <td class="text-center font-monospace">{{ (float)$item->skor_eskalasi }}</td>

                            {{-- Skor Dasar & Hasil --}}
                            <td class="text-center font-monospace fw-bold">{{ (float)$item->skor_dasar }}</td>
                            <td class="text-center font-monospace small">
                                @if((float)$item->penyesuaian_reviewer > 0)
                                    <span class="text-success">+{{ (float)$item->penyesuaian_reviewer }}</span>
                                @elseif((float)$item->penyesuaian_reviewer < 0)
                                    <span class="text-danger">{{ (float)$item->penyesuaian_reviewer }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center font-monospace fs-6 fw-bold text-primary">
                                {{ (float)$item->quality_score }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $kategoriBadge }} px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                                    {{ $item->kategori }}
                                </span>
                            </td>
                            <td>
                                <div class="text-truncate text-secondary small" style="max-width: 130px;" title="{{ $item->catatan_kpi }}">
                                    {{ $item->catatan_kpi ?: '-' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $statusArsipBadge }} px-2 py-1" style="font-size: 0.7rem;">
                                    {{ $item->status_arsip }}
                                </span>
                            </td>

                            @if(!$isRa)
                                <td class="text-center text-nowrap">
                                    <a href="{{ route('pelaporan-bulanan.distribusi-quality.edit', ['reportId' => $item->report_id]) }}"
                                       class="btn btn-sm btn-outline-warning rounded-pill px-2 py-1 text-dark" style="font-size: 0.75rem;" title="Edit Distribusi, Arsip & Penyesuaian KPI">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </a>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isRa ? 19 : 20 }}" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Tidak ada data distribusi & quality score untuk periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
