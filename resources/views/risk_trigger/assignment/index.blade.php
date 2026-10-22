@extends('layouts.app')
@section('title', 'Assignment & Scope Visit - Risk Trigger Visit (SOP 06)')

@section('content')
<style>
    /* Styling Harmonized with Register Trigger & Daily Register */
    :root {
        --primary-blue: #1e3a8a;
    }

    /* Tabs Sub Menu Harmonized */
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

    /* Filter Card */
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
    .filter-card .form-select:focus, .filter-card .form-control:focus {
        border-color: var(--bs-blue);
        box-shadow: 0 0 0 3px rgba(30,75,133,0.12);
    }

    /* Table Typography & Layout Harmonized */
    .table-container {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        overflow: hidden;
    }
    .table-container .card-header {
        background: #ffffff;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
    }
    .data-table th {
        font-size: 0.8rem;
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
        font-size: 0.86rem;
        padding: 0.85rem 0.95rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
    }
    .data-table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Badges Harmonized */
    .badge {
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.3rem 0.65rem;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
</style>

<!-- Page Header Harmonized -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="page-header-title">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1" style="font-size: 0.78rem; font-weight: 700;">
                SOP 06 — FASE 2
            </span>
            <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1" style="font-size: 0.78rem; font-weight: 600;">
                22 KOLOM EXCEL
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
            <i class="bi bi-clipboard-check text-primary me-2"></i>Assignment & Scope Visit (SOP 06)
        </h4>
        <p class="text-muted mb-0" style="font-size: 0.86rem;">
            Penerbitan surat tugas audit lapangan terarah, penetapan batas scope, otorisasi, dan pelaksana visit mengacu pada sheet <em>Assignment_Scope</em>.
        </p>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        @if(!$isRa)
        <a href="{{ route('risk-trigger.assignment.create', array_filter(['kode_unit' => request('kode_unit')])) }}" class="btn btn-primary btn-sm" style="font-size: 0.85rem; font-weight: 600; padding: 0.45rem 0.9rem; border-radius: 8px;">
            <i class="bi bi-plus-lg me-1"></i>
            <span>+ Buat Assignment Visit</span>
        </a>
        @endif
    </div>
</div>

{{-- Sub Menu Tabs Harmonized --}}
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

{{-- 4 Summary KPI Cards --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #3b82f6 !important;">
            <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Total Assignment</div>
            <div class="d-flex align-items-baseline gap-2 mt-1">
                <span style="font-size: 1.65rem; font-weight: 800; color: #1e293b;">{{ number_format($stats['total'] ?? 0) }}</span>
                <span class="text-muted" style="font-size: 0.78rem;">penugasan</span>
            </div>
            <div class="text-muted mt-1" style="font-size: 0.74rem;">Diterbitkan dari Risk Trigger Visit</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #10b981 !important;">
            <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Siap Dilaksanakan</div>
            <div class="d-flex align-items-baseline gap-2 mt-1">
                <span style="font-size: 1.65rem; font-weight: 800; color: #065f46;">{{ number_format($stats['siap'] ?? 0) }}</span>
                <span class="text-muted" style="font-size: 0.78rem;">assignment</span>
            </div>
            <div class="text-success mt-1" style="font-size: 0.74rem;"><i class="bi bi-check-circle me-1"></i>Otorisasi & scope lengkap</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #f59e0b !important;">
            <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Belum Lengkap</div>
            <div class="d-flex align-items-baseline gap-2 mt-1">
                <span style="font-size: 1.65rem; font-weight: 800; color: #92400e;">{{ number_format($stats['belum_lengkap'] ?? 0) }}</span>
                <span class="text-muted" style="font-size: 0.78rem;">assignment</span>
            </div>
            <div class="text-warning mt-1" style="font-size: 0.74rem;"><i class="bi bi-exclamation-triangle me-1"></i>Perlu melengkapi parameter</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #ef4444 !important;">
            <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Bukan Keputusan Visit</div>
            <div class="d-flex align-items-baseline gap-2 mt-1">
                <span style="font-size: 1.65rem; font-weight: 800; color: #991b1b;">{{ number_format($stats['bukan_visit'] ?? 0) }}</span>
                <span class="text-muted" style="font-size: 0.78rem;">record</span>
            </div>
            <div class="text-danger mt-1" style="font-size: 0.74rem;"><i class="bi bi-x-circle me-1"></i>Keputusan != RISK TRIGGER VISIT</div>
        </div>
    </div>
</div>

{{-- Filter Card Harmonized --}}
<div class="filter-card">
    <form method="GET" action="{{ route('risk-trigger.assignment') }}" class="row g-2 align-items-end">
        @if(!$isRa)
        <div class="col-md-3">
            <label class="form-label">
                <i class="bi bi-buildings me-1 text-primary"></i> Kantor Cabang / Unit
            </label>
            <select name="kode_unit" class="form-select">
                <option value="">-- Semua Unit Cabang --</option>
                @foreach($units as $u)
                    <option value="{{ $u->unit_code }}" {{ $filterKodeUnit == $u->unit_code ? 'selected' : '' }}>
                        {{ $u->unit_code }} - {{ $u->unit_name }}
                    </option>
                @endforeach
            </select>
        </div>
        @else
        <div class="col-md-3">
            <label class="form-label">
                <i class="bi bi-shield-lock-fill me-1 text-primary"></i> Cabang Tugas (Isolasi RA)
            </label>
            <input type="text" class="form-control bg-light" value="{{ $userCabangName }}" readonly title="Data otomatis dibatasi untuk cabang penugasan Anda">
        </div>
        @endif

        <div class="col-md-3">
            <label class="form-label">
                <i class="bi bi-check2-circle me-1 text-primary"></i> Status Assignment
            </label>
            <select name="status_assignment" class="form-select">
                <option value="">-- Semua Status --</option>
                <option value="SIAP DILAKSANAKAN" {{ request('status_assignment') == 'SIAP DILAKSANAKAN' ? 'selected' : '' }}>SIAP DILAKSANAKAN</option>
                <option value="BELUM LENGKAP" {{ request('status_assignment') == 'BELUM LENGKAP' ? 'selected' : '' }}>BELUM LENGKAP</option>
                <option value="BUKAN KEPUTUSAN VISIT" {{ request('status_assignment') == 'BUKAN KEPUTUSAN VISIT' ? 'selected' : '' }}>BUKAN KEPUTUSAN VISIT</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">
                <i class="bi bi-search me-1 text-primary"></i> Cari Data
            </label>
            <input type="text" name="search" class="form-control" placeholder="Visit ID / Trigger / Pelaksana / Memo..." value="{{ request('search') }}">
        </div>

        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm flex-fill" title="Terapkan Filter" style="padding: 0.45rem; font-size: 0.86rem; border-radius: 8px;">
                <i class="bi bi-funnel-fill me-1"></i> Filter
            </button>
            <a href="{{ route('risk-trigger.assignment') }}" class="btn btn-outline btn-sm flex-fill text-center" title="Reset Filter" style="padding: 0.45rem; font-size: 0.86rem; border-radius: 8px;">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
            </a>
        </div>
    </form>
</div>

{{-- Tabel 22 Kolom Excel Assignment_Scope --}}
<div class="table-container mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clipboard-data text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold" style="color: var(--bs-blue-dark); font-size: 0.98rem;">
                Daftar Penugasan Lapangan (Assignment &amp; Scope — 22 Kolom)
            </h6>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-3 py-1.5" style="font-size: 0.82rem; font-weight: 600;">
                Total: {{ number_format($assignments->total()) }} Data
            </span>
            <span class="text-muted" style="font-size: 0.84rem;">
                (Menampilkan {{ $assignments->firstItem() ?? 0 }} - {{ $assignments->lastItem() ?? 0 }})
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table mb-0" style="min-width: 2600px;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="min-width: 190px;">Visit ID (A)</th>
                    <th style="min-width: 170px;">Trigger ID (B)</th>
                    <th style="min-width: 180px;">Unit / Kantor (C &amp; D)</th>
                    <th style="min-width: 140px;">Area (E)</th>
                    <th style="min-width: 110px;">Risk Level (F)</th>
                    <th style="min-width: 170px;">Keputusan Final (G)</th>
                    <th style="min-width: 140px;">KKA Tujuan (H)</th>
                    <th style="min-width: 280px;">Scope / Tujuan Awal (I)</th>
                    <th style="min-width: 260px;">Periode / Populasi (J)</th>
                    <th style="min-width: 130px;">Tgl Visit (K)</th>
                    <th style="min-width: 130px;">Mulai Aktual (L)</th>
                    <th style="min-width: 130px;">Selesai Aktual (M)</th>
                    <th style="min-width: 160px;">RA Sumber (N)</th>
                    <th style="min-width: 170px;">RA Pelaksana (O)</th>
                    <th style="min-width: 160px;">Reviewer (P)</th>
                    <th style="min-width: 170px;">Otorisasi (Q)</th>
                    <th style="min-width: 160px;">Klasifikasi (R)</th>
                    <th class="text-center" style="min-width: 180px;">Status Assignment (S)</th>
                    <th style="min-width: 320px;">Batas Scope (T)</th>
                    <th style="min-width: 320px;">Kriteria Selesai (U)</th>
                    <th style="min-width: 220px;">Perlu Data Unit (V)</th>
                    <th class="text-center" style="min-width: 180px; position: sticky; right: 0; background: #f8fafc; z-index: 2; box-shadow: -4px 0 6px -2px rgba(0,0,0,0.05);">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $idx => $as)
                <tr>
                    <td class="text-center text-muted fw-bold">{{ $assignments->firstItem() + $idx }}</td>
                    
                    {{-- Kolom A: Visit ID --}}
                    <td>
                        <span class="badge bg-primary text-white font-monospace px-2.5 py-1" style="font-size: 0.82rem; letter-spacing: 0.5px;">
                            <i class="bi bi-tag-fill me-1"></i>{{ $as->visit_id }}
                        </span>
                    </td>

                    {{-- Kolom B: Trigger ID --}}
                    <td>
                        <a href="{{ route('risk-trigger.show', $as->register_trigger_id) }}" class="fw-bold text-decoration-none text-primary" title="Lihat Master Trigger">
                            {{ $as->trigger_id }}
                        </a>
                    </td>

                    {{-- Kolom C & D: Unit / Kantor --}}
                    <td>
                        <div class="fw-semibold text-dark">{{ $as->nama_unit ?: 'Unit '.$as->kode_unit }}</div>
                        <small class="text-muted">Kode: <span class="badge bg-light text-secondary border font-monospace">{{ $as->kode_unit }}</span></small>
                    </td>

                    {{-- Kolom E: Area --}}
                    <td>
                        <span class="badge bg-light text-dark border px-2 py-1">{{ $as->area }}</span>
                    </td>

                    {{-- Kolom F: Risk Level --}}
                    <td>
                        @php
                            $badgeClass = match($as->risk_level) {
                                'KRITIS' => 'bg-danger text-white',
                                'TINGGI' => 'bg-warning text-dark',
                                'SEDANG' => 'bg-info text-dark',
                                default => 'bg-secondary text-white'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }} px-2 py-1" style="font-weight: 700;">{{ $as->risk_level }}</span>
                    </td>

                    {{-- Kolom G: Keputusan Final --}}
                    <td>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 0.8rem; font-weight: 700;">
                            {{ $as->keputusan_final }}
                        </span>
                    </td>

                    {{-- Kolom H: KKA Tujuan --}}
                    <td>
                        <span class="badge bg-light text-primary border font-monospace">{{ $as->kka_tujuan }}</span>
                    </td>

                    {{-- Kolom I: Scope / Tujuan Awal --}}
                    <td>
                        <div style="max-width: 270px; white-space: normal; line-height: 1.35; color: #334155;">
                            {{ Str::limit($as->scope_tujuan_awal, 100) }}
                        </div>
                    </td>

                    {{-- Kolom J: Periode / Populasi --}}
                    <td>
                        <div style="max-width: 250px; white-space: normal; font-size: 0.82rem; color: #475569;">
                            {{ $as->periode_populasi }}
                        </div>
                    </td>

                    {{-- Kolom K: Tanggal Visit --}}
                    <td>
                        <div class="fw-semibold text-dark">
                            {{ $as->tanggal_visit ? $as->tanggal_visit->format('d/m/Y') : '-' }}
                        </div>
                    </td>

                    {{-- Kolom L & M: Mulai & Selesai Aktual --}}
                    <td>
                        <span class="{{ $as->mulai_aktual ? 'text-dark fw-semibold' : 'text-muted fst-italic' }}">
                            {{ $as->mulai_aktual ? $as->mulai_aktual->format('d/m/Y') : 'Belum visit' }}
                        </span>
                    </td>
                    <td>
                        <span class="{{ $as->selesai_aktual ? 'text-dark fw-semibold' : 'text-muted fst-italic' }}">
                            {{ $as->selesai_aktual ? $as->selesai_aktual->format('d/m/Y') : 'Belum selesai' }}
                        </span>
                    </td>

                    {{-- Kolom N: Assigned RA Sumber --}}
                    <td>
                        <small class="text-muted">{{ $as->assigned_ra_sumber ?: '-' }}</small>
                    </td>

                    {{-- Kolom O: RA Pelaksana --}}
                    <td>
                        <span class="fw-semibold text-dark"><i class="bi bi-person-fill text-primary me-1"></i>{{ $as->ra_pelaksana ?: '-' }}</span>
                    </td>

                    {{-- Kolom P: Reviewer --}}
                    <td>
                        <span class="text-dark">{{ $as->reviewer ?: '-' }}</span>
                    </td>

                    {{-- Kolom Q: Referensi Otorisasi --}}
                    <td>
                        <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                            {{ $as->referensi_otorisasi ?: '-' }}
                        </span>
                    </td>

                    {{-- Kolom R: Klasifikasi --}}
                    <td>
                        <small class="text-muted">{{ $as->klasifikasi }}</small>
                    </td>

                    {{-- Kolom S: Status Assignment --}}
                    <td class="text-center">
                        @if($as->status_assignment === 'SIAP DILAKSANAKAN')
                            <span class="badge bg-success text-white px-2.5 py-1.5" style="font-weight: 700; letter-spacing: 0.5px;">
                                <i class="bi bi-check-circle-fill me-1"></i>SIAP DILAKSANAKAN
                            </span>
                        @elseif($as->status_assignment === 'BELUM LENGKAP')
                            <span class="badge bg-warning text-dark px-2.5 py-1.5" style="font-weight: 700; letter-spacing: 0.5px;">
                                <i class="bi bi-exclamation-circle-fill me-1"></i>BELUM LENGKAP
                            </span>
                        @else
                            <span class="badge bg-danger text-white px-2.5 py-1.5" style="font-weight: 700;">
                                <i class="bi bi-x-circle-fill me-1"></i>{{ $as->status_assignment }}
                            </span>
                        @endif
                    </td>

                    {{-- Kolom T: Batas Scope --}}
                    <td>
                        <div style="max-width: 300px; white-space: normal; line-height: 1.35; font-size: 0.82rem; color: #334155;">
                            {{ $as->batas_scope }}
                        </div>
                    </td>

                    {{-- Kolom U: Kriteria Selesai --}}
                    <td>
                        <div style="max-width: 300px; white-space: normal; line-height: 1.35; font-size: 0.82rem; color: #475569;">
                            {{ $as->kriteria_selesai }}
                        </div>
                    </td>

                    {{-- Kolom V: Perlu Data Unit --}}
                    <td>
                        @if(str_contains($as->perlu_data_unit, 'Tidak'))
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                <i class="bi bi-cpu-fill me-1"></i>{{ $as->perlu_data_unit }}
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                <i class="bi bi-file-earmark-arrow-up-fill me-1"></i>{{ $as->perlu_data_unit }}
                            </span>
                        @endif
                    </td>

                    {{-- Kolom Aksi Sticky (Menggunakan Halaman Sendiri Untuk Mencegah Modal Terpotong/Tertutup Sidebar) --}}
                    <td class="text-center px-2 py-2" style="position: sticky; right: 0; background: #ffffff; z-index: 2; box-shadow: -4px 0 6px -2px rgba(0,0,0,0.05);">
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <a href="{{ route('risk-trigger.assignment.edit-dates', $as->id) }}" 
                               class="btn btn-outline-primary btn-sm fw-semibold" 
                               style="padding: 0.35rem 0.65rem; font-size: 0.8rem; border-radius: 6px; white-space: nowrap;" 
                               title="Input / Update Tanggal Lapangan (Mulai & Selesai Aktual)">
                                <i class="bi bi-calendar-event me-1"></i> Tgl Aktual
                            </a>

                            <a href="{{ route('risk-trigger.program-kerja', ['search' => $as->visit_id]) }}" 
                               class="btn btn-primary btn-sm fw-semibold" 
                               style="padding: 0.35rem 0.65rem; font-size: 0.8rem; border-radius: 6px; white-space: nowrap;" 
                               title="Buka Program Kerja Terarah">
                                <i class="bi bi-journal-check me-1"></i> PKA
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="23" class="text-center py-5 text-muted">
                        <div class="py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 2.8rem;"></i>
                            <h6 class="mt-3 fw-bold text-dark">Belum Ada Data Penugasan Visit</h6>
                            <p class="text-muted mb-3" style="font-size: 0.88rem; max-width: 500px; margin: 0 auto;">
                                @if($isRa)
                                    Belum ada penugasan visit yang diterbitkan untuk cabang Anda ({{ $userCabangName }}).
                                @else
                                    Belum ada penugasan visit yang diterbitkan. Anda dapat menerbitkan penugasan baru dari trigger berstatus RISK TRIGGER VISIT.
                                @endif
                            </p>
                            @if(!$isRa)
                            <a href="{{ route('risk-trigger.assignment.create') }}" class="btn btn-primary btn-sm px-3 py-2 fw-semibold">
                                <i class="bi bi-plus-circle me-1"></i> Terbitkan Penugasan Visit Pertama
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($assignments->hasPages())
    <div class="card-footer bg-white py-3 px-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted" style="font-size: 0.82rem;">
            Menampilkan {{ $assignments->firstItem() }} s.d. {{ $assignments->lastItem() }} dari total {{ $assignments->total() }} penugasan
        </div>
        <div>
            {{ $assignments->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
