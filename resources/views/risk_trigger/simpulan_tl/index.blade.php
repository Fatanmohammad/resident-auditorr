@extends('layouts.app')
@section('title', 'Simpulan, Tindak Lanjut & Closure - SOP 06')

@section('content')
<style>
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

    .data-table th {
        font-size: 0.76rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        background: #f8fafc;
        padding: 0.8rem 0.9rem;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    .data-table td {
        font-size: 0.83rem;
        padding: 0.75rem 0.9rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
    }
    .data-table tbody tr:hover {
        background-color: #f8fafc;
    }

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
    .badge-closed { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-open { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-tl { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
    .badge-klar { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .badge-draft { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .badge-revisi { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
    .badge-final { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .badge-gray { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
</style>

<div class="container-fluid px-3 px-md-4 py-3">
    <!-- Page Header Harmonized (Selaras dengan Menu Lain) -->
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div class="page-header-title">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1" style="font-size: 0.78rem; font-weight: 700;">
                    SOP 06 — FASE 4 (FINAL)
                </span>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1" style="font-size: 0.78rem; font-weight: 600;">
                    23 KOLOM EXCEL
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
                <i class="bi bi-check2-circle text-primary me-2"></i>Simpulan, Tindak Lanjut & Closure (Simpulan_TL)
            </h4>
            <p class="text-muted mb-0" style="font-size: 0.86rem;">
                Register Klarifikasi & Bukti, Rekapitulasi 23 Kolom Excel, Formula Otomatis Status Closure (Sel W6), dan Sinkronisasi Lintas Sheet SOP 06.
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

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" style="border-radius: 10px;" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" style="border-radius: 10px;" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- 6 Summary KPI Cards (Harmonized dengan Menu Lain) --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #3b82f6 !important;">
                <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Total Visit</div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span style="font-size: 1.65rem; font-weight: 800; color: #1e293b;">{{ number_format($stats['total']) }}</span>
                    <span class="text-muted" style="font-size: 0.78rem;">Visit ID</span>
                </div>
                <div class="text-muted mt-1" style="font-size: 0.74rem;">Penugasan RTV Aktif</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #10b981 !important;">
                <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Closed / Diteruskan</div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span style="font-size: 1.65rem; font-weight: 800; color: #065f46;">{{ number_format($stats['closed']) }}</span>
                    <span class="text-muted" style="font-size: 0.78rem;">Visit</span>
                </div>
                <div class="text-success mt-1" style="font-size: 0.74rem;"><i class="bi bi-check-circle me-1"></i>Selesai & FINAL</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #ef4444 !important;">
                <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Belum Ditutup</div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span style="font-size: 1.65rem; font-weight: 800; color: #991b1b;">{{ number_format($stats['belum_ditutup']) }}</span>
                    <span class="text-muted" style="font-size: 0.78rem;">Visit</span>
                </div>
                <div class="text-danger mt-1" style="font-size: 0.74rem;"><i class="bi bi-clock me-1"></i>Belum Review FINAL</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #f97316 !important;">
                <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">TL Belum Lengkap</div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span style="font-size: 1.65rem; font-weight: 800; color: #c2410c;">{{ number_format($stats['tl_belum_lengkap']) }}</span>
                    <span class="text-muted" style="font-size: 0.78rem;">Visit</span>
                </div>
                <div class="text-warning mt-1" style="font-size: 0.74rem;"><i class="bi bi-exclamation-triangle me-1"></i>TL/PIC/Due Date kosong</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #f59e0b !important;">
                <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Klarifikasi Open</div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span style="font-size: 1.65rem; font-weight: 800; color: #92400e;">{{ number_format($stats['klarifikasi_open']) }}</span>
                    <span class="text-muted" style="font-size: 0.78rem;">Visit</span>
                </div>
                <div class="text-warning mt-1" style="font-size: 0.74rem;"><i class="bi bi-hourglass-split me-1"></i>Klarifikasi belum Closed</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #6366f1 !important;">
                <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Review FINAL</div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span style="font-size: 1.65rem; font-weight: 800; color: #4338ca;">{{ number_format($stats['final_review']) }}</span>
                    <span class="text-muted" style="font-size: 0.78rem;">Visit</span>
                </div>
                <div class="text-primary mt-1" style="font-size: 0.74rem;"><i class="bi bi-shield-check me-1"></i>Divalidasi Admin</div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="filter-card">
        <form action="{{ route('risk-trigger.simpulan-tl.index') }}" method="GET" class="row g-2 align-items-end">
            @if(!$isRa)
            <div class="col-md-3">
                <label class="form-label">Kantor Cabang / Unit:</label>
                <select name="kode_unit" class="form-select">
                    <option value="">-- Semua Cabang (Global) --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->unit_code }}" {{ $filterKodeUnit == $u->unit_code ? 'selected' : '' }}>
                            {{ $u->unit_code }} - {{ $u->unit_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="col-md-2">
                <label class="form-label">Status Closure (Kolom W):</label>
                <select name="status_closure" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="CLOSED/DITERUSKAN" {{ request('status_closure') == 'CLOSED/DITERUSKAN' ? 'selected' : '' }}>CLOSED / DITERUSKAN</option>
                    <option value="BELUM DITUTUP" {{ request('status_closure') == 'BELUM DITUTUP' ? 'selected' : '' }}>BELUM DITUTUP</option>
                    <option value="TL BELUM LENGKAP" {{ request('status_closure') == 'TL BELUM LENGKAP' ? 'selected' : '' }}>TL BELUM LENGKAP</option>
                    <option value="KLARIFIKASI BELUM SELESAI" {{ request('status_closure') == 'KLARIFIKASI BELUM SELESAI' ? 'selected' : '' }}>KLARIFIKASI BELUM SELESAI</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Status Review (Kolom V):</label>
                <select name="status_review" class="form-select">
                    <option value="">-- Semua Review --</option>
                    <option value="DRAFT" {{ request('status_review') == 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
                    <option value="REVISI" {{ request('status_review') == 'REVISI' ? 'selected' : '' }}>REVISI</option>
                    <option value="FINAL" {{ request('status_review') == 'FINAL' ? 'selected' : '' }}>FINAL</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Status Klarifikasi (Kolom H):</label>
                <select name="status_klarifikasi" class="form-select">
                    <option value="">-- Semua --</option>
                    <option value="SELESAI" {{ request('status_klarifikasi') == 'SELESAI' ? 'selected' : '' }}>SELESAI</option>
                    <option value="MASIH OPEN" {{ request('status_klarifikasi') == 'MASIH OPEN' ? 'selected' : '' }}>MASIH OPEN</option>
                    <option value="TIDAK ADA" {{ request('status_klarifikasi') == 'TIDAK ADA' ? 'selected' : '' }}>TIDAK ADA</option>
                </select>
            </div>

            <div class="col-md-{{ $isRa ? '3' : '3' }}">
                <label class="form-label">Cari Kata Kunci:</label>
                <input type="text" name="q" class="form-control" placeholder="Visit ID, Trigger, PIC, Simpulan..." value="{{ request('q') }}">
            </div>

            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 8px; padding: 0.5rem 1rem;">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('risk-trigger.simpulan-tl.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px; padding: 0.5rem 0.85rem;">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Master Table Simpulan_TL (23 Kolom Excel: A s.d. W) --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2 border-bottom">
            <h6 class="mb-0 fw-bold" style="color: var(--primary-blue);">
                <i class="bi bi-table text-primary me-2"></i>Rekapitulasi Simpulan & Tindak Lanjut (23 Kolom Excel: A s.d. W)
            </h6>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border">Total: {{ $simpulanList->total() }} Visit</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover data-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 60px;">Aksi</th>
                        <th style="min-width: 170px;">Visit ID (A)</th>
                        <th style="min-width: 140px;">Trigger ID (B)</th>
                        <th style="min-width: 160px;">Unit / Kantor (C)</th>
                        <th style="min-width: 140px;">Area Risiko (D)</th>
                        <th style="min-width: 110px;">Risk Level (E)</th>
                        <th style="min-width: 130px;">Trigger Terbukti? (F)</th>
                        <th class="text-center" style="min-width: 130px;">Jml Exception (G)</th>
                        <th style="min-width: 150px;">Status Klarifikasi (H)</th>
                        <th style="min-width: 220px;">Simpulan Akhir (I)</th>
                        <th style="min-width: 160px;">Klasifikasi Hasil (J)</th>
                        <th style="min-width: 130px;">Perlu Perluasan? (K)</th>
                        <th style="min-width: 180px;">Scope Perluasan (L)</th>
                        <th style="min-width: 200px;">Tindak Lanjut Awal (M)</th>
                        <th style="min-width: 150px;">PIC Unit (N)</th>
                        <th style="min-width: 120px;">Due Date (O)</th>
                        <th style="min-width: 130px;">Level Eskalasi (P)</th>
                        <th style="min-width: 150px;">Tujuan Eskalasi (Q)</th>
                        <th style="min-width: 140px;">No Laporan (R)</th>
                        <th style="min-width: 160px;">Referensi Bukti (S)</th>
                        <th style="min-width: 140px;">RA Pelaksana (T)</th>
                        <th style="min-width: 140px;">Reviewer (U)</th>
                        <th style="min-width: 150px;">Status Review (V)</th>
                        <th style="min-width: 180px;">Status Closure (W)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($simpulanList as $item)
                    <tr>
                        {{-- Aksi --}}
                        <td class="text-center">
                            <a href="{{ route('risk-trigger.simpulan-tl.edit', $item->id) }}" class="btn btn-sm btn-primary px-2.5 py-1 text-nowrap" title="Buka Lembar Kerja & Register Klarifikasi">
                                <i class="bi bi-pencil-square me-1"></i> Buka Simpulan
                            </a>
                        </td>

                        {{-- Kolom A: Visit ID --}}
                        <td class="fw-bold text-primary font-monospace">{{ $item->visit_id }}</td>

                        {{-- Kolom B: Trigger ID --}}
                        <td class="font-monospace small text-muted">{{ $item->trigger_id ?: '-' }}</td>

                        {{-- Kolom C: Unit / Kantor --}}
                        <td>
                            <div class="fw-semibold text-dark">{{ $item->unit_kantor }}</div>
                            <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">{{ $item->kode_unit }}</span>
                        </td>

                        {{-- Kolom D: Area Risiko --}}
                        <td><span class="badge bg-light text-dark border">{{ $item->area }}</span></td>

                        {{-- Kolom E: Risk Level Awal --}}
                        <td>
                            @if(strtoupper($item->risk_level_awal) === 'KRITIS')
                                <span class="badge bg-danger text-white">KRITIS</span>
                            @elseif(strtoupper($item->risk_level_awal) === 'TINGGI' || strtoupper($item->risk_level_awal) === 'HIGH')
                                <span class="badge bg-danger text-white">TINGGI</span>
                            @elseif(strtoupper($item->risk_level_awal) === 'SEDANG')
                                <span class="badge bg-warning text-dark">SEDANG</span>
                            @else
                                <span class="badge bg-secondary text-white">{{ $item->risk_level_awal ?: 'RENDAH' }}</span>
                            @endif
                        </td>

                        {{-- Kolom F: Trigger Terbukti? --}}
                        <td>
                            @if($item->trigger_terbukti === 'Ya')
                                <span class="badge bg-danger text-white">Ya</span>
                            @elseif($item->trigger_terbukti === 'Tidak')
                                <span class="badge bg-success text-white">Tidak</span>
                            @else
                                <span class="badge bg-light text-muted border">-</span>
                            @endif
                        </td>

                        {{-- Kolom G: Jumlah Exception --}}
                        <td class="text-center fw-bold">
                            @if($item->jumlah_exception > 0)
                                <span class="badge bg-danger text-white px-2.5 py-1">
                                    <i class="bi bi-exclamation-octagon-fill me-1"></i> {{ $item->jumlah_exception }} Temuan
                                </span>
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i> 0 Temuan
                                </span>
                            @endif
                        </td>

                        {{-- Kolom H: Status Klarifikasi --}}
                        <td>
                            @if($item->status_klarifikasi === 'SELESAI')
                                <span class="badge-status badge-closed"><i class="bi bi-check-circle-fill"></i> SELESAI</span>
                            @elseif($item->status_klarifikasi === 'MASIH OPEN')
                                <span class="badge-status badge-klar"><i class="bi bi-exclamation-circle-fill"></i> MASIH OPEN</span>
                            @else
                                <span class="badge-status badge-gray"><i class="bi bi-dash-circle"></i> TIDAK ADA</span>
                            @endif
                        </td>

                        {{-- Kolom I: Simpulan Akhir --}}
                        <td style="max-width: 220px;">
                            <div class="text-truncate" title="{{ $item->simpulan_akhir }}">
                                {{ $item->simpulan_akhir ?: '-' }}
                            </div>
                        </td>

                        {{-- Kolom J: Klasifikasi Hasil --}}
                        <td>
                            @if($item->klasifikasi_hasil === 'Temuan')
                                <span class="badge bg-danger text-white">Temuan</span>
                            @elseif($item->klasifikasi_hasil === 'Perlu Perluasan')
                                <span class="badge bg-warning text-dark">Perlu Perluasan</span>
                            @elseif($item->klasifikasi_hasil === 'Eskalasi')
                                <span class="badge bg-primary text-white">Eskalasi</span>
                            @elseif($item->klasifikasi_hasil === 'Tidak Terbukti / Selesai')
                                <span class="badge bg-success text-white">Tidak Terbukti / Selesai</span>
                            @else
                                <span class="text-muted small">- Belum Ditentukan -</span>
                            @endif
                        </td>

                        {{-- Kolom K: Perlu Perluasan? --}}
                        <td>
                            @if($item->perlu_perluasan === 'Ya')
                                <span class="badge bg-warning text-dark">Ya</span>
                            @else
                                <span class="badge bg-light text-muted border">Tidak</span>
                            @endif
                        </td>

                        {{-- Kolom L: Scope Perluasan --}}
                        <td style="max-width: 180px;">
                            <div class="text-truncate" title="{{ $item->scope_perluasan }}">
                                {{ $item->scope_perluasan ?: '-' }}
                            </div>
                        </td>

                        {{-- Kolom M: Tindak Lanjut Awal --}}
                        <td style="max-width: 200px;">
                            <div class="text-truncate" title="{{ $item->tindak_lanjut_awal }}">
                                {{ $item->tindak_lanjut_awal ?: '-' }}
                            </div>
                        </td>

                        {{-- Kolom N: PIC Unit --}}
                        <td class="small">{{ $item->pic_unit ?: '-' }}</td>

                        {{-- Kolom O: Due Date --}}
                        <td class="small font-monospace">
                            {{ $item->due_date ? $item->due_date->format('d/m/Y') : '-' }}
                        </td>

                        {{-- Kolom P: Level Eskalasi --}}
                        <td>
                            @if($item->level_eskalasi && $item->level_eskalasi !== 'Tidak Perlu')
                                <span class="badge bg-info text-dark">{{ $item->level_eskalasi }}</span>
                            @else
                                <span class="badge bg-light text-muted border">Tidak Perlu</span>
                            @endif
                        </td>

                        {{-- Kolom Q: Tujuan Eskalasi --}}
                        <td class="small">{{ $item->tujuan_eskalasi ?: '-' }}</td>

                        {{-- Kolom R: Laporan --}}
                        <td class="small font-monospace">{{ $item->laporan ?: '-' }}</td>

                        {{-- Kolom S: Referensi KKA/Bukti --}}
                        <td class="small text-muted" style="font-size: 0.72rem;">{{ $item->referensi_kka_bukti }}</td>

                        {{-- Kolom T: RA Pelaksana --}}
                        <td class="small">{{ $item->ra_pelaksana ?: '-' }}</td>

                        {{-- Kolom U: Reviewer --}}
                        <td class="small">{{ $item->reviewer ?: '-' }}</td>

                        {{-- Kolom V: Status Review (Reviewer Only) --}}
                        <td>
                            @if($item->status_review === 'FINAL')
                                <span class="badge-status badge-final"><i class="bi bi-lock-fill"></i> FINAL</span>
                            @elseif($item->status_review === 'REVISI')
                                <span class="badge-status badge-revisi"><i class="bi bi-arrow-repeat"></i> REVISI</span>
                            @else
                                <span class="badge-status badge-draft"><i class="bi bi-pencil"></i> DRAFT</span>
                            @endif
                        </td>

                        {{-- Kolom W: Status Closure (Rumus Excel Sel W6) --}}
                        <td>
                            @if($item->status_closure === 'CLOSED/DITERUSKAN')
                                <span class="badge-status badge-closed"><i class="bi bi-check-circle-fill"></i> CLOSED / DITERUSKAN</span>
                            @elseif($item->status_closure === 'TL BELUM LENGKAP')
                                <span class="badge-status badge-tl"><i class="bi bi-exclamation-triangle-fill"></i> TL BELUM LENGKAP</span>
                            @elseif($item->status_closure === 'KLARIFIKASI BELUM SELESAI')
                                <span class="badge-status badge-klar"><i class="bi bi-hourglass-split"></i> KLARIFIKASI BELUM SELESAI</span>
                            @else
                                <span class="badge-status badge-open"><i class="bi bi-x-circle-fill"></i> BELUM DITUTUP</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="24" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            Belum ada rekaman penugasan audit pada Simpulan & Tindak Lanjut.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($simpulanList->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center">
            <div class="small text-muted">
                Halaman {{ $simpulanList->currentPage() }} dari {{ $simpulanList->lastPage() }}
            </div>
            <div>
                {{ $simpulanList->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
