@extends('layouts.app')
@section('title', 'Program Kerja Terarah & Daftar Data - Risk Trigger Visit (SOP 06)')

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
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1" style="font-size: 0.78rem; font-weight: 600;">
                18 KOLOM EXCEL
            </span>
            @if($isRa)
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1" style="font-size: 0.78rem; font-weight: 600;">
                    <i class="bi bi-geo-alt-fill me-1"></i> Cabang: {{ $userCabangName }}
                </span>
            @else
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1" style="font-size: 0.78rem; font-weight: 600;">
                    <i class="bi bi-shield-check me-1"></i> Mode Reviewer / Admin (Global)
                </span>
            @endif
        </div>
        <h4 class="fw-bold mb-1" style="color: var(--bs-blue-dark); font-size: 1.25rem;">
            <i class="bi bi-journal-text text-primary me-2"></i>Program Kerja Terarah &amp; Daftar Data (SOP 06)
        </h4>
        <p class="text-muted mb-0" style="font-size: 0.86rem;">
            Program kerja audit spesifik per area risiko (Master Parameter), formula ukuran sampel pemicu &amp; pembanding, serta pelacakan kesiapan dokumen pengujian mengacu pada sheet <em>Program_Kerja_Data</em>.
        </p>
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
            <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Total Program Kerja</div>
            <div class="d-flex align-items-baseline gap-2 mt-1">
                <span style="font-size: 1.65rem; font-weight: 800; color: #1e293b;">{{ number_format($stats['total'] ?? 0) }}</span>
                <span class="text-muted" style="font-size: 0.78rem;">PKA</span>
            </div>
            <div class="text-muted mt-1" style="font-size: 0.74rem;">Terhubung ke Visit ID aktif</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #10b981 !important;">
            <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Siap Uji</div>
            <div class="d-flex align-items-baseline gap-2 mt-1">
                <span style="font-size: 1.65rem; font-weight: 800; color: #065f46;">{{ number_format($stats['siap_uji'] ?? 0) }}</span>
                <span class="text-muted" style="font-size: 0.78rem;">PKA</span>
            </div>
            <div class="text-success mt-1" style="font-size: 0.74rem;"><i class="bi bi-check-circle me-1"></i>Dokumen lengkap / objek fisik</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #f97316 !important;">
            <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Menunggu Data</div>
            <div class="d-flex align-items-baseline gap-2 mt-1">
                <span style="font-size: 1.65rem; font-weight: 800; color: #c2410c;">{{ number_format($stats['menunggu_data'] ?? 0) }}</span>
                <span class="text-muted" style="font-size: 0.78rem;">PKA</span>
            </div>
            <div class="text-warning mt-1" style="font-size: 0.74rem;"><i class="bi bi-hourglass-split me-1"></i>Sudah diminta, menunggu serah terima</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #64748b !important;">
            <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;">Belum Diminta</div>
            <div class="d-flex align-items-baseline gap-2 mt-1">
                <span style="font-size: 1.65rem; font-weight: 800; color: #334155;">{{ number_format($stats['belum_diminta'] ?? 0) }}</span>
                <span class="text-muted" style="font-size: 0.78rem;">PKA</span>
            </div>
            <div class="text-muted mt-1" style="font-size: 0.74rem;"><i class="bi bi-clock me-1"></i>Belum mengajukan permintaan data</div>
        </div>
    </div>
</div>

{{-- Filter Card Harmonized --}}
<div class="filter-card">
    <form method="GET" action="{{ route('risk-trigger.program-kerja') }}" class="row g-2 align-items-end">
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

        <div class="col-md-2">
            <label class="form-label">
                <i class="bi bi-grid me-1 text-primary"></i> Area Risiko
            </label>
            <select name="area" class="form-select">
                <option value="">-- Semua Area --</option>
                @foreach($standarAreas as $ar)
                    <option value="{{ $ar }}" {{ request('area') == $ar ? 'selected' : '' }}>{{ $ar }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">
                <i class="bi bi-check2-circle me-1 text-primary"></i> Status Kesiapan
            </label>
            <select name="status_kesiapan" class="form-select">
                <option value="">-- Semua Status --</option>
                <option value="SIAP UJI" {{ request('status_kesiapan') == 'SIAP UJI' ? 'selected' : '' }}>SIAP UJI</option>
                <option value="MENUNGGU DATA" {{ request('status_kesiapan') == 'MENUNGGU DATA' ? 'selected' : '' }}>MENUNGGU DATA</option>
                <option value="BELUM DIMINTA" {{ request('status_kesiapan') == 'BELUM DIMINTA' ? 'selected' : '' }}>BELUM DIMINTA</option>
                <option value="BELUM SIAP" {{ request('status_kesiapan') == 'BELUM SIAP' ? 'selected' : '' }}>BELUM SIAP</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">
                <i class="bi bi-search me-1 text-primary"></i> Cari Data
            </label>
            <input type="text" name="search" class="form-control" placeholder="Visit ID / Trigger / Area / Tujuan..." value="{{ request('search') }}">
        </div>

        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm flex-fill" title="Terapkan Filter" style="padding: 0.45rem; font-size: 0.86rem; border-radius: 8px;">
                <i class="bi bi-funnel-fill me-1"></i> Filter
            </button>
            <a href="{{ route('risk-trigger.program-kerja') }}" class="btn btn-outline btn-sm flex-fill text-center" title="Reset Filter" style="padding: 0.45rem; font-size: 0.86rem; border-radius: 8px;">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
            </a>
        </div>
    </form>
</div>

{{-- Tabel 18 Kolom Excel Program_Kerja_Data --}}
<div class="table-container mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-journal-bookmark-fill text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold" style="color: var(--bs-blue-dark); font-size: 0.98rem;">
                Daftar Program Kerja Terarah &amp; Status Kesiapan Data (18 Kolom SOP 06)
            </h6>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-3 py-1.5" style="font-size: 0.82rem; font-weight: 600;">
                Total: {{ number_format($programKerja->total()) }} Data
            </span>
            <span class="text-muted" style="font-size: 0.84rem;">
                (Menampilkan {{ $programKerja->firstItem() ?? 0 }} - {{ $programKerja->lastItem() ?? 0 }})
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table mb-0" style="min-width: 2500px;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="min-width: 190px;">Visit ID (A)</th>
                    <th style="min-width: 170px;">Trigger ID (B)</th>
                    <th style="min-width: 150px;">Area (C)</th>
                    <th style="min-width: 140px;">KKA Tujuan (D)</th>
                    <th style="min-width: 280px;">Tujuan Uji (E)</th>
                    <th style="min-width: 280px;">Kriteria Utama (F)</th>
                    <th style="min-width: 300px;">Data / Bukti Minimum (G)</th>
                    <th style="min-width: 250px;">Metode Uji (H)</th>
                    <th style="min-width: 190px;">Metode Sampling (I)</th>
                    <th class="text-center" style="min-width: 130px;">Sampel Pemicu (J)</th>
                    <th class="text-center" style="min-width: 140px;">Sampel Pembanding (K)</th>
                    <th style="min-width: 240px;">Langkah Tambahan RA (L)</th>
                    <th style="min-width: 130px;">Cutoff Data (M)</th>
                    <th style="min-width: 130px;">Tgl Minta (N)</th>
                    <th style="min-width: 130px;">Tgl Terima (O)</th>
                    <th style="min-width: 250px;">Bukti Awal (P)</th>
                    <th class="text-center" style="min-width: 170px;">Status Kesiapan (Q)</th>
                    <th style="min-width: 240px;">Catatan Akses/Data (R)</th>
                    <th class="text-center" style="min-width: 150px; position: sticky; right: 0; background: #f8fafc; z-index: 2; box-shadow: -4px 0 6px -2px rgba(0,0,0,0.05);">{{ $isRa ? 'Aksi RA' : 'Aksi' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programKerja as $idx => $pk)
                <tr>
                    <td class="text-center text-muted fw-bold">{{ $programKerja->firstItem() + $idx }}</td>

                    {{-- Kolom A: Visit ID --}}
                    <td>
                        <span class="badge bg-primary text-white font-monospace px-2.5 py-1" style="font-size: 0.82rem;">
                            <i class="bi bi-tag-fill me-1"></i>{{ $pk->visit_id }}
                        </span>
                    </td>

                    {{-- Kolom B: Trigger ID --}}
                    <td>
                        <a href="{{ route('risk-trigger.show', $pk->register_trigger_id) }}" class="fw-bold text-decoration-none text-primary" title="Lihat Master Trigger">
                            {{ $pk->trigger_id }}
                        </a>
                    </td>

                    {{-- Kolom C: Area --}}
                    <td>
                        <span class="badge bg-light text-dark border px-2 py-1">{{ $pk->area }}</span>
                    </td>

                    {{-- Kolom D: KKA Tujuan --}}
                    <td>
                        <span class="badge bg-light text-primary border font-monospace">{{ $pk->kka_tujuan }}</span>
                    </td>

                    {{-- Kolom E: Tujuan Uji --}}
                    <td>
                        <div style="max-width: 270px; white-space: normal; line-height: 1.35; color: #1e293b;">
                            {{ $pk->tujuan_uji }}
                        </div>
                    </td>

                    {{-- Kolom F: Kriteria Utama --}}
                    <td>
                        <div style="max-width: 270px; white-space: normal; line-height: 1.35; color: #475569;">
                            {{ $pk->kriteria_utama }}
                        </div>
                    </td>

                    {{-- Kolom G: Data / Bukti Minimum --}}
                    <td>
                        <div style="max-width: 290px; white-space: normal; line-height: 1.35; color: #334155;">
                            {{ $pk->data_bukti_minimum }}
                        </div>
                    </td>

                    {{-- Kolom H: Metode Uji --}}
                    <td>
                        <div style="max-width: 240px; white-space: normal; color: #475569;">
                            {{ $pk->metode_uji }}
                        </div>
                    </td>

                    {{-- Kolom I: Metode Sampling --}}
                    <td>
                        <span class="badge bg-light text-secondary border">{{ $pk->metode_sampling }}</span>
                    </td>

                    {{-- Kolom J: Sampel Pemicu --}}
                    <td class="text-center">
                        <span class="badge bg-secondary-subtle text-secondary border font-monospace px-2.5 py-1" style="font-size: 0.85rem; font-weight: 700;">
                            {{ $pk->sampel_pemicu }}
                        </span>
                    </td>

                    {{-- Kolom K: Sampel Pembanding --}}
                    <td class="text-center">
                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle font-monospace px-2.5 py-1" style="font-size: 0.85rem; font-weight: 700;">
                            {{ $pk->sampel_pembanding }}
                        </span>
                        <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px;">CEIL(SQRT)</div>
                    </td>

                    {{-- Kolom L: Langkah Tambahan RA --}}
                    <td>
                        <div style="max-width: 230px; white-space: normal; line-height: 1.35; font-size: 0.82rem;" class="{{ $pk->langkah_tambahan_ra ? 'text-dark' : 'text-muted fst-italic' }}">
                            {{ $pk->langkah_tambahan_ra ?: 'Tidak ada langkah tambahan' }}
                        </div>
                    </td>

                    {{-- Kolom M: Cutoff Data --}}
                    <td>
                        <span class="font-monospace text-dark">
                            {{ $pk->cutoff_data ? $pk->cutoff_data->format('d/m/Y') : '-' }}
                        </span>
                    </td>

                    {{-- Kolom N: Tanggal Permintaan --}}
                    <td>
                        <span class="{{ $pk->tanggal_permintaan ? 'text-dark fw-semibold' : 'text-muted fst-italic' }}">
                            {{ $pk->tanggal_permintaan ? $pk->tanggal_permintaan->format('d/m/Y') : 'Belum diminta' }}
                        </span>
                    </td>

                    {{-- Kolom O: Tanggal Diterima --}}
                    <td>
                        <span class="{{ $pk->tanggal_diterima ? 'text-dark fw-semibold' : 'text-muted fst-italic' }}">
                            {{ $pk->tanggal_diterima ? $pk->tanggal_diterima->format('d/m/Y') : 'Belum diterima' }}
                        </span>
                    </td>

                    {{-- Kolom P: Bukti Awal --}}
                    <td>
                        <div style="max-width: 240px; white-space: normal; line-height: 1.35; font-size: 0.82rem; color: #475569;">
                            {{ Str::limit($pk->referensi_bukti_awal, 100) }}
                        </div>
                    </td>

                    {{-- Kolom Q: Status Kesiapan --}}
                    <td class="text-center">
                        @if($pk->status_kesiapan === 'SIAP UJI')
                            <span class="badge bg-success text-white px-2.5 py-1.5" style="font-weight: 700; letter-spacing: 0.5px;">
                                <i class="bi bi-check-circle-fill me-1"></i>SIAP UJI
                            </span>
                        @elseif($pk->status_kesiapan === 'MENUNGGU DATA')
                            <span class="badge bg-warning text-dark px-2.5 py-1.5" style="font-weight: 700; letter-spacing: 0.5px;">
                                <i class="bi bi-hourglass-split me-1"></i>MENUNGGU DATA
                            </span>
                        @elseif($pk->status_kesiapan === 'BELUM DIMINTA')
                            <span class="badge bg-secondary text-white px-2.5 py-1.5" style="font-weight: 700; letter-spacing: 0.5px;">
                                <i class="bi bi-clock-history me-1"></i>BELUM DIMINTA
                            </span>
                        @else
                            <span class="badge bg-danger text-white px-2.5 py-1.5" style="font-weight: 700;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $pk->status_kesiapan }}
                            </span>
                        @endif
                    </td>

                    {{-- Kolom R: Catatan Akses/Data --}}
                    <td>
                        <div style="max-width: 230px; white-space: normal; line-height: 1.35; font-size: 0.82rem;" class="{{ $pk->catatan_akses_data ? 'text-danger' : 'text-muted fst-italic' }}">
                            {{ $pk->catatan_akses_data ?: 'Tidak ada kendala' }}
                        </div>
                    </td>

                    {{-- Kolom Aksi Sticky --}}
                    <td class="text-center px-2 py-2" style="position: sticky; right: 0; background: #ffffff; z-index: 2; box-shadow: -4px 0 6px -2px rgba(0,0,0,0.05);">
                        <a href="{{ route('risk-trigger.program-kerja.edit', $pk->id) }}" 
                           class="btn {{ $isRa ? 'btn-outline-primary' : 'btn-outline-secondary' }} btn-sm fw-semibold" 
                           style="padding: 0.35rem 0.75rem; font-size: 0.8rem; border-radius: 6px; white-space: nowrap;" 
                           title="{{ $isRa ? 'Buka Halaman Input Pelaksanaan Kerja & Kesiapan Data RA' : 'Tinjau Pelaksanaan Kerja & Kesiapan Data (Read-Only)' }}">
                            @if($isRa)
                                <i class="bi bi-pencil-square me-1"></i> Input Data RA
                            @else
                                <i class="bi bi-eye me-1"></i> Tinjau PKA
                            @endif
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="20" class="text-center py-5 text-muted">
                        <div class="py-4">
                            <i class="bi bi-journal-x text-muted" style="font-size: 2.8rem;"></i>
                            <h6 class="mt-3 fw-bold text-dark">Belum Ada Program Kerja Terarah</h6>
                            <p class="text-muted mb-3" style="font-size: 0.88rem; max-width: 500px; margin: 0 auto;">
                                Program Kerja Terarah akan terbit secara otomatis setiap kali Reviewer menerbitkan <strong>Assignment &amp; Scope</strong> untuk trigger pemicu visit.
                            </p>
                            @if(!$isRa)
                            <a href="{{ route('risk-trigger.assignment.create') }}" class="btn btn-primary btn-sm px-3 py-2 fw-semibold">
                                <i class="bi bi-plus-circle me-1"></i> Buat Penugasan Visit Sekarang
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($programKerja->hasPages())
    <div class="card-footer bg-white py-3 px-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted" style="font-size: 0.82rem;">
            Menampilkan {{ $programKerja->firstItem() }} s.d. {{ $programKerja->lastItem() }} dari total {{ $programKerja->total() }} program kerja
        </div>
        <div>
            {{ $programKerja->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
