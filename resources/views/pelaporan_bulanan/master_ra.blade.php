@extends('layouts.app')
@section('title', 'Roster Resident Auditor')

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
    .table thead th {
        background: #f8fafc;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        color: #475569;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
        padding: 0.75rem 0.85rem;
    }
    .table tbody td {
        font-size: 0.875rem;
        vertical-align: middle;
        white-space: nowrap;
        padding: 0.75rem 0.85rem;
        color: #334155;
    }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Breadcrumb & Title --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">Pelaporan Bulanan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Roster RA</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-people text-primary"></i> Roster Resident Auditor
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Data induk stabil Resident Auditor (RA ID), riwayat penempatan kantor cabang, dan masa tugas pengawasan.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(!$isRa)
            <a href="{{ route('pelaporan-bulanan.master-ra.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-person-plus me-1"></i> + Tambah Resident Auditor
            </a>
            @endif
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-printer me-1"></i> Cetak / Export
            </button>
        </div>
    </div>

    {{-- Sub-Menu Navigation Tabs --}}
    @include('pelaporan_bulanan.tabs')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Card --}}
    @if(!$isRa)
    <div class="filter-card">
        <form method="GET" action="{{ route('pelaporan-bulanan.master-ra') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Filter Kantor Cabang Penempatan</label>
                <select name="kode_kc" class="form-select form-select-sm">
                    <option value="">-- Seluruh Kantor Cabang --</option>
                    @foreach($masterUnits as $u)
                        <option value="{{ $u->kode_unit }}" {{ request('kode_kc') == $u->kode_unit ? 'selected' : '' }}>
                            {{ $u->kode_unit }} - {{ $u->nama_unit }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Status Penugasan</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="Aktif" {{ request('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Non-Aktif" {{ request('status') == 'Non-Aktif' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm">
                    <i class="bi bi-funnel me-1"></i> Terapkan
                </button>
                <a href="{{ route('pelaporan-bulanan.master-ra') }}" class="btn btn-outline-secondary btn-sm px-3" title="Reset Filter">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>
    @endif

    {{-- KPI Cards Summary --}}
    @php
        $totalRa = $masterRaList->count();
        $aktifRa = $masterRaList->where('status', 'Aktif')->count();
        $nonAktifRa = $totalRa - $aktifRa;
        $totalKcCovered = $masterRaList->pluck('kode_kc_penempatan')->unique()->count();
    @endphp

    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="kpi-card text-center">
                <div class="kpi-title">Total Roster RA</div>
                <div class="kpi-value text-primary">{{ $totalRa }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Profil Resident Auditor</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card text-center">
                <div class="kpi-title">RA Status Aktif</div>
                <div class="kpi-value text-success">{{ $aktifRa }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Sedang Bertugas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card text-center">
                <div class="kpi-title">RA Non-Aktif</div>
                <div class="kpi-value text-secondary">{{ $nonAktifRa }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Mutasi / Selesai Tugas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card text-center">
                <div class="kpi-title">Cabang Ter-Cover</div>
                <div class="kpi-value text-info">{{ $totalKcCovered }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Unit Kantor Cabang</div>
            </div>
        </div>
    </div>

    {{-- Main 9-Column Table --}}
    <div class="table-responsive shadow-sm">
        <table class="table table-hover table-bordered mb-0">
            <thead>
                <tr>
                    <th>RA ID</th>
                    <th>Nama Resident Auditor</th>
                    <th>Kode KC</th>
                    <th>Nama KC</th>
                    <th>Unit RA</th>
                    <th>Status</th>
                    <th>Tgl Mulai</th>
                    <th>Tgl Selesai</th>
                    <th>Catatan</th>
                    @if(!$isRa)
                    <th style="width: 80px;">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($masterRaList as $ra)
                <tr>
                    {{-- Kolom A: RA ID --}}
                    <td class="text-center fw-bold text-primary font-monospace">{{ $ra->ra_id }}</td>

                    {{-- Kolom B: Nama Resident Auditor --}}
                    <td class="fw-semibold">
                        <i class="bi bi-person-badge text-secondary me-1"></i>
                        {{ $ra->nama_ra }}
                    </td>

                    {{-- Kolom C: Kode KC Penempatan --}}
                    <td class="text-center font-monospace">{{ $ra->kode_kc_penempatan }}</td>

                    {{-- Kolom D: Nama KC --}}
                    <td>{{ $ra->nama_kc }}</td>

                    {{-- Kolom E: Unit RA --}}
                    <td class="small text-muted">{{ $ra->unit_ra }}</td>

                    {{-- Kolom F: Status --}}
                    <td class="text-center">
                        @if($ra->status === 'Aktif')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                <i class="bi bi-check-circle-fill me-1"></i> Aktif
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                Non-Aktif
                            </span>
                        @endif
                    </td>

                    {{-- Kolom G: Tanggal Mulai --}}
                    <td class="text-center small">
                        {{ $ra->tanggal_mulai ? \Carbon\Carbon::parse($ra->tanggal_mulai)->format('d/m/Y') : '-' }}
                    </td>

                    {{-- Kolom H: Tanggal Selesai --}}
                    <td class="text-center small">
                        {{ $ra->tanggal_selesai ? \Carbon\Carbon::parse($ra->tanggal_selesai)->format('d/m/Y') : '-' }}
                    </td>

                    {{-- Kolom I: Catatan --}}
                    <td style="max-width: 250px; white-space: normal;" class="small text-muted">
                        {{ $ra->catatan ?: '-' }}
                    </td>

                    {{-- Aksi (Admin Only) --}}
                    @if(!$isRa)
                    <td class="text-center">
                        <a href="{{ route('pelaporan-bulanan.master-ra.edit', $ra->id) }}" class="btn btn-outline-dark btn-sm px-2 py-1" title="Edit Profil RA">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $isRa ? 9 : 10 }}" class="text-center py-4 text-muted">
                        <i class="bi bi-people fs-2 d-block mb-2 text-secondary"></i>
                        Tidak ada data Resident Auditor yang terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footnote RBAC Notice --}}
    <div class="card mt-3 bg-light border-0 shadow-sm">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center gap-2 small text-muted">
                <i class="bi bi-shield-check text-primary fs-6"></i>
                <div>
                    <strong>Aturan Master Roster RA:</strong>
                    Master RA digunakan untuk mempertahankan histori kinerja bulanan meskipun ada mutasi cabang.
                    Nama KC dan Unit RA terhubung otomatis dengan referensi <code>Master_Unit</code>.
                    <strong>Resident Auditor (RA)</strong> berstatus <em>Read-Only</em> dan hanya dapat melihat profil penempatan dirinya sendiri.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
