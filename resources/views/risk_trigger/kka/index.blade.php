@extends('layouts.app')
@section('title', 'KKA Risk Trigger - KKA Induk (SOP 06)')

@section('content')
<style>
    :root {
        --primary-blue: #1e3a8a;
    }

    .kka-tabs { 
        display: flex; 
        align-items: center; 
        flex-wrap: wrap; 
        gap: 0.5rem; 
        padding: 0.5rem 0.75rem; 
        margin-bottom: 1.25rem; 
        background: #ffffff; 
        border: 1px solid var(--border-color); 
        border-radius: 12px; 
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .kka-tab-item {
        background: #f8fafc; 
        border: 1px solid #e2e8f0; 
        border-radius: 8px; 
        padding: 0.5rem 1rem; 
        font-size: 0.86rem; 
        font-weight: 600; 
        color: #475569; 
        display: inline-flex; 
        align-items: center; 
        gap: 0.5rem; 
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .kka-tab-item i { font-size: 0.95rem; color: #64748b; transition: color 0.2s; }
    .kka-tab-item:hover { 
        background: #eff6ff; 
        border-color: #bfdbfe; 
        color: var(--bs-blue); 
    }
    .kka-tab-item:hover i { color: var(--bs-blue); }
    .kka-tab-item.active { 
        background: var(--bs-blue); 
        border-color: var(--bs-blue); 
        color: #ffffff; 
        box-shadow: 0 2px 6px rgba(30, 75, 133, 0.25); 
    }
    .kka-tab-item.active i { color: #ffffff !important; }

    .filter-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.15rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .filter-card .form-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.35rem;
        display: block;
    }
    .filter-card .form-select, .filter-card .form-control {
        font-size: 0.86rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0.45rem 0.75rem;
        color: #1e293b;
    }

    .table-container {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        overflow: hidden;
    }
    .data-table th {
        font-size: 0.78rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        background: #f8fafc;
        padding: 0.85rem 0.95rem;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    .data-table td {
        font-size: 0.85rem;
        padding: 0.85rem 0.95rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
    }
    .data-table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Badges Harmonized */
    .badge-status {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }
    .badge-draft { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .badge-revisi { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
    .badge-final { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
</style>

<div class="container-fluid px-3 px-md-4 py-3">
    <!-- Page Header Harmonized (Selaras dengan Menu Lain) -->
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div class="page-header-title">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1" style="font-size: 0.78rem; font-weight: 700;">
                    SOP 06 — FASE 3
                </span>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1" style="font-size: 0.78rem; font-weight: 600;">
                    20 KOLOM EXCEL
                </span>
                @if($isRa)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1" style="font-size: 0.78rem; font-weight: 600;">
                        <i class="bi bi-geo-alt-fill me-1"></i> Cabang: {{ $userCabangName }}
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1" style="font-size: 0.78rem; font-weight: 600;">
                        <i class="bi bi-shield-check me-1"></i> Mode Reviewer / Admin (Global)
                    </span>
                @endif
            </div>
            <h4 class="fw-bold mb-1" style="color: var(--bs-blue-dark); font-size: 1.25rem;">
                <i class="bi bi-file-earmark-spreadsheet text-primary me-2"></i>Kertas Kerja Audit (KKA) Risk Trigger Visit
            </h4>
            <p class="text-muted mb-0" style="font-size: 0.86rem;">
                KKA Induk (20 Kolom Excel A s.d. T) terintegrasi dengan agregasi Exception real-time dari 8 KKA Spesifik Area pengujian lapangan.
            </p>
        </div>
    </div>

    {{-- Sub Menu Tabs Harmonized (6 Tab Utuh SOP 06) --}}
    <div class="kka-tabs">
        <a href="{{ route('risk-trigger.control') }}" class="kka-tab-item {{ request()->routeIs('risk-trigger.control') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Ringkasan Control
        </a>
        <a href="{{ route('risk-trigger.register') }}" class="kka-tab-item {{ request()->routeIs('risk-trigger.register') ? 'active' : '' }}">
            <i class="bi bi-table"></i> Register Trigger (37 Kolom)
        </a>
        <a href="{{ route('risk-trigger.assignment') }}" class="kka-tab-item {{ request()->routeIs('risk-trigger.assignment*') ? 'active' : '' }}">
            <i class="bi bi-clipboard-check"></i> Assignment & Scope (22 Kolom)
        </a>
        <a href="{{ route('risk-trigger.program-kerja') }}" class="kka-tab-item {{ request()->routeIs('risk-trigger.program-kerja*') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i> Program Kerja & Data (18 Kolom)
        </a>
        <a href="{{ route('risk-trigger.kka.index') }}" class="kka-tab-item {{ request()->routeIs('risk-trigger.kka*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-spreadsheet"></i> KKA Pengujian (Induk & 8 Area)
        </a>
        <a href="{{ route('risk-trigger.simpulan-tl.index') }}" class="kka-tab-item {{ request()->routeIs('risk-trigger.simpulan-tl*') ? 'active' : '' }}">
            <i class="bi bi-check2-circle"></i> Klarifikasi & Simpulan TL
        </a>
    </div>

    {{-- 5 Summary KPI Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #3b82f6 !important;">
                <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Total KKA Induk</div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span style="font-size: 1.65rem; font-weight: 800; color: #1e293b;">{{ number_format($stats['total'] ?? 0) }}</span>
                    <span class="text-muted" style="font-size: 0.78rem;">Visit ID</span>
                </div>
                <div class="text-muted mt-1" style="font-size: 0.74rem;">Otomatis dari Assignment</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #64748b !important;">
                <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Draft / Belum Direview</div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span style="font-size: 1.65rem; font-weight: 800; color: #475569;">{{ number_format($stats['draft'] ?? 0) }}</span>
                    <span class="text-muted" style="font-size: 0.78rem;">KKA</span>
                </div>
                <div class="text-secondary mt-1" style="font-size: 0.74rem;"><i class="bi bi-pencil me-1"></i>Dalam pengerjaan RA</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #f59e0b !important;">
                <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Revisi</div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span style="font-size: 1.65rem; font-weight: 800; color: #92400e;">{{ number_format($stats['revisi'] ?? 0) }}</span>
                    <span class="text-muted" style="font-size: 0.78rem;">KKA</span>
                </div>
                <div class="text-warning mt-1" style="font-size: 0.74rem;"><i class="bi bi-arrow-repeat me-1"></i>Perlu tindak lanjut RA</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #10b981 !important;">
                <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Final (Dikunci)</div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span style="font-size: 1.65rem; font-weight: 800; color: #065f46;">{{ number_format($stats['final'] ?? 0) }}</span>
                    <span class="text-muted" style="font-size: 0.78rem;">KKA</span>
                </div>
                <div class="text-success mt-1" style="font-size: 0.74rem;"><i class="bi bi-lock-fill me-1"></i>Disetujui Reviewer</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #ef4444 !important;">
                <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Total Exception Area</div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span style="font-size: 1.65rem; font-weight: 800; color: #991b1b;">{{ number_format($stats['total_exception'] ?? 0) }}</span>
                    <span class="text-muted" style="font-size: 0.78rem;">temuan</span>
                </div>
                <div class="text-danger mt-1" style="font-size: 0.74rem;"><i class="bi bi-exclamation-octagon me-1"></i>COUNTIFS 8 KKA Area</div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('risk-trigger.kka.index') }}" class="row g-2 align-items-end">
            @if(!$isRa)
            <div class="col-12 col-md-3">
                <label class="form-label"><i class="bi bi-building me-1"></i>Filter Unit / Cabang</label>
                <select name="kode_unit" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Cabang (Global) --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->unit_code }}" {{ $filterKodeUnit == $u->unit_code ? 'selected' : '' }}>
                            {{ $u->unit_code }} - {{ $u->unit_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="col-6 col-md-2">
                <label class="form-label"><i class="bi bi-tag me-1"></i>Area Risiko</label>
                <select name="area" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Area --</option>
                    @foreach($standarAreas as $sa)
                        <option value="{{ $sa }}" {{ request('area') == $sa ? 'selected' : '' }}>{{ $sa }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label"><i class="bi bi-check2-circle me-1"></i>Status Review</label>
                <select name="status_review" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Status --</option>
                    <option value="DRAFT / BELUM DIREVIEW" {{ request('status_review') == 'DRAFT / BELUM DIREVIEW' ? 'selected' : '' }}>DRAFT / BELUM DIREVIEW</option>
                    <option value="REVISI" {{ request('status_review') == 'REVISI' ? 'selected' : '' }}>REVISI</option>
                    <option value="FINAL" {{ request('status_review') == 'FINAL' ? 'selected' : '' }}>FINAL</option>
                </select>
            </div>

            <div class="col-12 col-md">
                <label class="form-label"><i class="bi bi-search me-1"></i>Cari KKA / Visit ID</label>
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Visit ID, Trigger ID, simpulan, dll..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    @if(request()->anyFilled(['kode_unit', 'area', 'status_review', 'search']))
                        <a href="{{ route('risk-trigger.kka.index') }}" class="btn btn-outline-danger" title="Reset Filter"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Main Table Container: 20 Kolom Excel A s.d. T --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2 border-bottom">
            <h6 class="mb-0 fw-bold" style="color: var(--primary-blue);">
                <i class="bi bi-file-earmark-spreadsheet-fill text-primary me-2"></i>
                Daftar KKA Induk (KKA_Risk_Trigger - 20 Kolom Excel: Sel A s.d. T)
            </h6>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border">Total: {{ $kkas->total() }} Visit</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 data-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">Aksi</th>
                        <th style="min-width: 170px;">Visit ID (A)</th>
                        <th style="min-width: 140px;">Trigger ID (B)</th>
                        <th style="min-width: 160px;">Unit/Kantor (C)</th>
                        <th style="min-width: 140px;">Area (D)</th>
                        <th style="min-width: 110px;">Risk Level (E)</th>
                        <th style="min-width: 250px;">Pertanyaan Audit (F)</th>
                        <th style="min-width: 220px;">Kondisi Awal (G)</th>
                        <th style="min-width: 220px;">Kriteria (H)</th>
                        <th style="min-width: 220px;">Prosedur Utama (I)</th>
                        <th style="min-width: 200px;">Ringkasan Bukti (J)</th>
                        <th style="min-width: 200px;">Hasil Analisis (K)</th>
                        <th style="min-width: 130px;">Trigger Terbukti? (L)</th>
                        <th style="min-width: 130px;">Perlu Perluasan? (M)</th>
                        <th style="min-width: 180px;">Alasan Perluasan (N)</th>
                        <th style="min-width: 170px;">KKA Area (O)</th>
                        <th class="text-center" style="min-width: 130px;">Exception Area (P)</th>
                        <th style="min-width: 220px;">Simpulan RA (Q)</th>
                        <th style="min-width: 140px;">RA Pelaksana (R)</th>
                        <th style="min-width: 140px;">Reviewer (S)</th>
                        <th style="min-width: 150px;">Status Review (T)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kkas as $k)
                    @php
                        $excCount = (int)($k->exception_area ?? 0);
                    @endphp
                    <tr>
                        {{-- Aksi --}}
                        <td class="text-center">
                            <a href="{{ route('risk-trigger.kka.edit', ['id' => $k->id, 'tab' => $k->kka_area]) }}" 
                               class="btn btn-sm btn-primary px-2.5 py-1 text-nowrap" 
                               title="Buka Lembar Kerja KKA Induk & 8 KKA Area">
                                <i class="bi bi-pencil-square me-1"></i> Buka KKA
                            </a>
                        </td>

                        {{-- Kolom A: Visit ID --}}
                        <td>
                            <span class="font-monospace fw-bold text-primary">{{ $k->visit_id }}</span>
                        </td>

                        {{-- Kolom B: Trigger ID --}}
                        <td>
                            <span class="font-monospace fw-semibold text-secondary">{{ $k->trigger_id }}</span>
                        </td>

                        {{-- Kolom C: Unit/Kantor --}}
                        <td>
                            <div class="fw-semibold text-dark">{{ $k->unit_kantor }}</div>
                            <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">{{ $k->kode_unit }}</span>
                        </td>

                        {{-- Kolom D: Area --}}
                        <td>
                            <span class="badge bg-light text-dark border">{{ $k->area }}</span>
                        </td>

                        {{-- Kolom E: Risk Level --}}
                        <td>
                            <span class="badge {{ $k->risk_level == 'TINGGI' || $k->risk_level == 'HIGH' ? 'bg-danger' : ($k->risk_level == 'SEDANG' || $k->risk_level == 'MEDIUM' ? 'bg-warning text-dark' : 'bg-info text-white') }}">
                                {{ $k->risk_level }}
                            </span>
                        </td>

                        {{-- Kolom F: Pertanyaan Audit --}}
                        <td>
                            <div class="text-wrap" style="max-width: 280px; font-size: 0.82rem; line-height: 1.4;">
                                {{ $k->pertanyaan_audit }}
                            </div>
                        </td>

                        {{-- Kolom G: Kondisi Awal --}}
                        <td>
                            <div class="text-wrap text-muted" style="max-width: 240px; font-size: 0.82rem;">
                                {{ $k->kondisi_awal ?: '-' }}
                            </div>
                        </td>

                        {{-- Kolom H: Kriteria --}}
                        <td>
                            <div class="text-wrap text-muted" style="max-width: 240px; font-size: 0.82rem;">
                                {{ $k->kriteria ?: '-' }}
                            </div>
                        </td>

                        {{-- Kolom I: Prosedur Utama --}}
                        <td>
                            <div class="text-wrap text-muted" style="max-width: 240px; font-size: 0.82rem;">
                                {{ $k->prosedur_utama ?: '-' }}
                            </div>
                        </td>

                        {{-- Kolom J: Ringkasan Bukti --}}
                        <td>
                            @if(!empty($k->ringkasan_bukti))
                                <div class="text-wrap" style="max-width: 220px; font-size: 0.82rem;">
                                    {{ Str::limit($k->ringkasan_bukti, 100) }}
                                </div>
                            @else
                                <span class="text-muted fst-italic" style="font-size: 0.78rem;">Belum diisi</span>
                            @endif
                        </td>

                        {{-- Kolom K: Hasil Analisis --}}
                        <td>
                            @if(!empty($k->hasil_analisis))
                                <div class="text-wrap" style="max-width: 220px; font-size: 0.82rem;">
                                    {{ Str::limit($k->hasil_analisis, 100) }}
                                </div>
                            @else
                                <span class="text-muted fst-italic" style="font-size: 0.78rem;">Belum diisi</span>
                            @endif
                        </td>

                        {{-- Kolom L: Trigger Terbukti? --}}
                        <td>
                            @if($k->trigger_terbukti == 'Ya')
                                <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Ya</span>
                            @elseif($k->trigger_terbukti == 'Sebagian')
                                <span class="badge bg-warning text-dark"><i class="bi bi-dash-circle-fill me-1"></i> Sebagian</span>
                            @elseif($k->trigger_terbukti == 'Tidak')
                                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Tidak</span>
                            @else
                                <span class="badge bg-light text-muted border">-</span>
                            @endif
                        </td>

                        {{-- Kolom M: Perlu Perluasan? --}}
                        <td>
                            @if($k->perlu_perluasan == 'Ya')
                                <span class="badge bg-warning text-dark"><i class="bi bi-arrows-fullscreen me-1"></i> Ya</span>
                            @else
                                <span class="badge bg-light text-secondary border">Tidak</span>
                            @endif
                        </td>

                        {{-- Kolom N: Alasan Perluasan --}}
                        <td>
                            <div class="text-wrap" style="max-width: 200px; font-size: 0.82rem;">
                                {{ $k->alasan_perluasan ?: '-' }}
                            </div>
                        </td>

                        {{-- Kolom O: KKA Area --}}
                        <td>
                            <span class="badge bg-light text-primary border" style="font-size: 0.78rem;">
                                <i class="bi bi-file-earmark-code me-1"></i> {{ $k->kka_area ?: '-' }}
                            </span>
                        </td>

                        {{-- Kolom P: Exception Area (COUNTIFS Lintas 8 KKA Area) --}}
                        <td class="text-center">
                            @if($excCount > 0)
                                <span class="badge bg-danger px-2.5 py-1.5" style="font-size: 0.82rem;" title="COUNTIFS 8 KKA Area = {{ $excCount }}">
                                    <i class="bi bi-exclamation-octagon-fill me-1"></i> {{ $excCount }}
                                </span>
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1" style="font-size: 0.8rem;">
                                    0 Sesuai
                                </span>
                            @endif
                        </td>

                        {{-- Kolom Q: Simpulan RA --}}
                        <td>
                            @if(!empty($k->simpulan_ra))
                                <div class="text-wrap" style="max-width: 240px; font-size: 0.82rem;">
                                    {{ Str::limit($k->simpulan_ra, 120) }}
                                </div>
                            @else
                                <span class="text-muted fst-italic" style="font-size: 0.78rem;">Belum diisi</span>
                            @endif
                        </td>

                        {{-- Kolom R: RA Pelaksana --}}
                        <td>
                            <span class="text-dark fw-semibold" style="font-size: 0.82rem;">{{ $k->ra_pelaksana ?: '-' }}</span>
                        </td>

                        {{-- Kolom S: Reviewer --}}
                        <td>
                            <span class="text-dark fw-semibold" style="font-size: 0.82rem;">{{ $k->reviewer ?: '-' }}</span>
                        </td>

                        {{-- Kolom T: Status Review --}}
                        <td>
                            @if($k->status_review == 'FINAL')
                                <span class="badge-status badge-final"><i class="bi bi-lock-fill"></i> FINAL</span>
                            @elseif($k->status_review == 'REVISI')
                                <span class="badge-status badge-revisi"><i class="bi bi-arrow-repeat"></i> REVISI</span>
                            @else
                                <span class="badge-status badge-draft"><i class="bi bi-pencil"></i> DRAFT</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="21" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <div class="fw-semibold">Belum Ada KKA Induk</div>
                            <small class="text-muted">KKA Induk otomatis terbuat setiap kali Assignment Visit diterbitkan.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kkas->hasPages())
        <div class="p-3 border-top bg-light">
            {{ $kkas->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
