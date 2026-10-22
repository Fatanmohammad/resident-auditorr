@extends('layouts.app')
@section('title', 'Register Trigger - Risk Trigger Visit (SOP 06)')

@section('content')
<style>
    /* Styling Harmonized with Daily Register & Other Modules */
    :root {
        --primary-blue: #1e3a8a;
    }

    /* Tabs Sub Menu Harmonized with Daily Register */
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
        padding: 0.9rem 0.95rem;
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
    .badge-pill-sub {
        font-size: 0.74rem;
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        border-radius: 5px;
    }
</style>

<!-- Page Header Harmonized -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="page-header-title">
        <h4 class="fw-bold mb-1" style="color: var(--bs-blue-dark); font-size: 1.25rem;">
            <i class="bi bi-table text-primary me-2"></i>Register Trigger (SOP 06)
        </h4>
        <p class="text-muted mb-0" style="font-size: 0.86rem;">
            Daftar lengkap 37 kolom pemicu audit, hasil agregasi INBOX_TRIGGER, reassessment risiko otomatis, dan routing keputusan visit.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- 1-Click Sync Button -->
        <form action="{{ route('risk-trigger.sync') }}" method="POST" class="m-0" id="formSyncRegister">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm" id="btnSyncRegister" style="font-size: 0.85rem; font-weight: 600; padding: 0.45rem 0.9rem; border-radius: 8px;">
                <i class="bi bi-arrow-repeat me-1" id="iconSyncRegister"></i>
                <span>{{ $isRa ? 'Sinkronkan Data Cabang' : 'Tarik Sinkronisasi' }}</span>
            </button>
        </form>

        <!-- Input Manual Trigger Button (Halaman Khusus) - Khusus Role Admin -->
        @if(!$isRa)
        <a href="{{ route('risk-trigger.create') }}" class="btn btn-primary btn-sm" style="font-size: 0.85rem; font-weight: 600; padding: 0.45rem 0.9rem; border-radius: 8px;">
            <i class="bi bi-plus-lg me-1"></i>
            <span>Input Trigger Manual</span>
        </a>
        @endif
    </div>
</div>

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

<!-- NOTIFIKASI ALARM KEPALA BIDANG (> 5 Temuan per Area/Unit) -->
@if(!empty($alarms))
    <div class="card mb-3 shadow-sm border-0" style="border-left: 4px solid #dc2626 !important; background: #fff; border-radius: 12px; border: 1px solid var(--border-color);">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-danger" style="font-size: 0.78rem; padding: 0.35rem 0.7rem;">
                        <i class="bi bi-bell-fill me-1"></i> PERINGATAN ALARM KEPALA BIDANG
                    </span>
                    <span class="text-muted fw-semibold" style="font-size: 0.85rem;">
                        Terdeteksi akumulasi &gt; 5 temuan per Area Risiko pada Unit terkait:
                    </span>
                </div>
            </div>
            <div class="d-flex flex-column gap-2 mt-2">
                @foreach($alarms as $alarm)
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-2.5 rounded" style="background: #fef2f2; border: 1px solid #fee2e2;">
                        <div class="d-flex align-items-center gap-2.5" style="font-size: 0.86rem; color: #991b1b;">
                            <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                            <div>
                                <strong>{{ $alarm['nama_unit'] }} ({{ $alarm['kode_unit'] }})</strong> &mdash; Area Risiko: <strong class="text-dark">{{ $alarm['area'] }}</strong> ({{ $alarm['count'] }} temuan)
                                <div style="font-size: 0.82rem; color: #7f1d1d; margin-top: 0.15rem;">
                                    {{ $alarm['message'] }} &bull; Verifikasi Lapangan: <strong>Ya</strong>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('risk-trigger.register', ['kode_unit' => $alarm['kode_unit'], 'area' => $alarm['area']]) }}" class="btn btn-danger btn-sm" style="font-size: 0.82rem; font-weight: 600; padding: 0.35rem 0.85rem; border-radius: 6px;">
                            Fokus Area Ini <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<!-- FILTER CARD (HARMONIZED) -->
<div class="filter-card">
    <form method="GET" action="{{ route('risk-trigger.register') }}" class="row g-2 align-items-end">
        <!-- Filter Unit (Admin: Dropdown Seluruh Cabang, RA: Terisolasi ke Cabang Tugas) -->
        <div class="col-md-3">
            @if(!$isRa)
                <label class="form-label">
                    <i class="bi bi-buildings me-1 text-primary"></i> Kantor Cabang / Unit
                </label>
                <select name="kode_unit" class="form-select">
                    <option value="">-- Semua Unit Cabang --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->unit_code }}" {{ request('kode_unit') == $u->unit_code ? 'selected' : '' }}>
                            {{ $u->unit_code }} - {{ $u->unit_name }}
                        </option>
                    @endforeach
                </select>
            @else
                <label class="form-label">
                    <i class="bi bi-shield-lock-fill me-1 text-primary"></i> Cabang Tugas (Isolasi RA)
                </label>
                <input type="text" class="form-control bg-light" value="{{ $userCabangName }}" readonly title="Data otomatis dibatasi untuk cabang penugasan Anda">
            @endif
        </div>

        <!-- Filter Area -->
        <div class="col-md-2">
            <label class="form-label">
                <i class="bi bi-grid me-1 text-primary"></i> 13 Area Risiko
            </label>
            <select name="area" class="form-select">
                <option value="">-- Semua Area --</option>
                @foreach($standarAreas as $area)
                    <option value="{{ $area }}" {{ request('area') == $area ? 'selected' : '' }}>{{ $area }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filter Risk Level -->
        <div class="col-md-2">
            <label class="form-label">
                <i class="bi bi-shield me-1 text-primary"></i> Tingkat Risiko
            </label>
            <select name="risk_level" class="form-select">
                <option value="">-- Semua Level --</option>
                <option value="KRITIS" {{ request('risk_level') == 'KRITIS' ? 'selected' : '' }}>KRITIS (&ge;16)</option>
                <option value="TINGGI" {{ request('risk_level') == 'TINGGI' ? 'selected' : '' }}>TINGGI (10-15)</option>
                <option value="SEDANG" {{ request('risk_level') == 'SEDANG' ? 'selected' : '' }}>SEDANG (5-9)</option>
                <option value="RENDAH" {{ request('risk_level') == 'RENDAH' ? 'selected' : '' }}>RENDAH (&lt;5)</option>
            </select>
        </div>

        <!-- Filter Keputusan Final -->
        <div class="col-md-2">
            <label class="form-label">
                <i class="bi bi-signpost-split me-1 text-primary"></i> Keputusan Final
            </label>
            <select name="keputusan_final" class="form-select">
                <option value="">-- Semua Keputusan --</option>
                @foreach($standarKeputusan as $kep)
                    <option value="{{ $kep }}" {{ request('keputusan_final') == $kep ? 'selected' : '' }}>{{ $kep }}</option>
                @endforeach
            </select>
        </div>

        <!-- Search Keyword -->
        <div class="col-md-2">
            <label class="form-label">
                <i class="bi bi-search me-1 text-primary"></i> Cari Data
            </label>
            <input type="text" name="q" class="form-control" placeholder="Trigger ID / Objek / Uraian..." value="{{ request('q') }}">
        </div>

        <!-- Action Buttons -->
        <div class="col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm flex-fill" title="Terapkan Filter" style="padding: 0.45rem; font-size: 0.86rem; border-radius: 8px;">
                <i class="bi bi-funnel-fill"></i>
            </button>
            <a href="{{ route('risk-trigger.register') }}" class="btn btn-outline btn-sm flex-fill text-center" title="Reset Filter" style="padding: 0.45rem; font-size: 0.86rem; border-radius: 8px;">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- MAIN DATA TABLE CONTAINER -->
<div class="table-container mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-journal-check text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold" style="color: var(--bs-blue-dark); font-size: 0.98rem;">
                Data Register Trigger (37 Kolom SOP 06)
            </h6>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-3 py-1.5" style="font-size: 0.82rem; font-weight: 600;">
                Total: {{ number_format($triggers->total()) }} Data
            </span>
            <span class="text-muted" style="font-size: 0.84rem;">
                (Menampilkan {{ $triggers->firstItem() ?? 0 }} - {{ $triggers->lastItem() ?? 0 }})
            </span>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th>Trigger ID</th>
                    <th>Sumber &amp; Ref ID</th>
                    <th>Kantor Unit</th>
                    <th>Area Risiko</th>
                    <th style="min-width: 240px;">Objek &amp; Uraian Pemicu</th>
                    <th style="text-align: center;">Reassessment Risiko</th>
                    <th style="text-align: center;">Keputusan Final</th>
                    <th style="text-align: center;">SLA / Deadline</th>
                    <th style="text-align: center;">Status Workflow</th>
                    <th style="text-align: center; width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($triggers as $idx => $t)
                    @php
                        $riskCls = match($t->risk_level_reassessment) {
                            'KRITIS' => 'badge-danger',
                            'TINGGI' => 'badge-warning',
                            'SEDANG' => 'badge-info',
                            default => 'badge-gray',
                        };

                        $kepCls = match($t->keputusan_final) {
                            'RISK TRIGGER VISIT' => 'badge-danger',
                            'KLARIFIKASI OFFSITE' => 'badge-warning',
                            'MONITORING' => 'badge-info',
                            'SOP 05 - SURPRISE AUDIT' => 'badge-purple',
                            'SOP 07 - MONITORING TL' => 'badge-gray',
                            'SOP 09 - PEMERIKSAAN AWAL FRAUD' => 'badge-danger',
                            default => 'badge-gray',
                        };

                        $isOverdue = $t->sla_deadline && $t->sla_deadline->lt(now()->startOfDay()) && $t->status_workflow !== 'SELESAI/DALAM TL';
                        $isDuplikat = $t->status_duplikat === 'DUPLIKAT - REVIEW';
                    @endphp
                    <tr style="{{ $isDuplikat ? 'background-color: #fffdf5;' : '' }}">
                        <td style="text-align: center; font-weight: 600; color: #64748b; font-size: 0.85rem;">
                            {{ $triggers->firstItem() + $idx }}
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #1e3a8a; font-size: 0.9rem;">
                                {{ $t->trigger_id }}
                            </div>
                            <div class="d-flex gap-1 mt-1">
                                @if($isDuplikat)
                                    <span class="badge badge-warning badge-pill-sub">DUPLIKAT</span>
                                @else
                                    <span class="badge badge-gray badge-pill-sub">UNIK</span>
                                @endif
                                @if($t->kelengkapan_data === 'VALID')
                                    <span class="badge badge-success badge-pill-sub">VALID</span>
                                @else
                                    <span class="badge badge-danger badge-pill-sub">LENGKAPI</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $t->sumber }}</div>
                            <div class="text-muted" style="font-size: 0.82rem; font-family: monospace;">{{ $t->source_wp_id ?: '-' }}</div>
                            <div class="text-muted mt-0.5" style="font-size: 0.82rem;">
                                <i class="bi bi-calendar3 me-1"></i>{{ $t->tanggal_trigger ? $t->tanggal_trigger->format('d/m/Y') : '-' }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $t->kode_unit }}</div>
                            <div class="text-muted" style="font-size: 0.84rem; max-width: 150px;" class="text-truncate" title="{{ $t->nama_unit }}">
                                {{ $t->nama_unit }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $t->area }}</div>
                            <span class="badge badge-info mt-1" style="font-size: 0.76rem;">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i>{{ $t->kka_tujuan }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold text-primary mb-1" style="font-size: 0.88rem;">
                                {{ $t->objek_key }}
                            </div>
                            <div class="text-secondary" style="font-size: 0.84rem; line-height: 1.45; max-width: 280px;" class="text-truncate" title="{{ $t->uraian_trigger }}">
                                {{ Str::limit($t->uraian_trigger, 85) }}
                            </div>
                            <div class="d-flex flex-wrap gap-1 mt-1.5">
                                @if(strtoupper((string)$t->indikasi_fraud) === 'YA')
                                    <span class="badge badge-danger" style="font-size: 0.74rem;">
                                        <i class="bi bi-shield-slash me-1"></i>FRAUD (SOP 09)
                                    </span>
                                @endif
                                @if(strtoupper((string)$t->objek_surprise_fisik) === 'YA')
                                    <span class="badge badge-purple" style="font-size: 0.74rem;">
                                        <i class="bi bi-box-seam me-1"></i>FISIK/SURPRISE
                                    </span>
                                @endif
                                @if($t->eksposur_nominal > 0)
                                    <span class="badge badge-gray" style="font-size: 0.76rem;">
                                        Rp {{ number_format($t->eksposur_nominal, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge {{ $riskCls }}" style="font-size: 0.82rem; font-weight: 700; padding: 0.35rem 0.75rem;">
                                {{ $t->risk_level_reassessment }} ({{ $t->risk_score_reassessment }})
                            </span>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">
                                Likelihood: <strong>{{ $t->likelihood }}</strong> &times; Impact: <strong>{{ $t->impact }}</strong>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge {{ $kepCls }}" style="font-size: 0.82rem; font-weight: 700; padding: 0.35rem 0.75rem;">
                                {{ $t->keputusan_final }}
                            </span>
                            @if($t->override_keputusan)
                                <div class="mt-1" style="font-size: 0.78rem; color: #b45309; font-weight: 600;">
                                    <i class="bi bi-pencil-fill me-1"></i>Override RA
                                </div>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($t->sla_deadline)
                                <div class="fw-bold {{ $isOverdue ? 'text-danger' : 'text-dark' }}" style="font-size: 0.88rem;">
                                    {{ $t->sla_deadline->format('d/m/Y') }}
                                </div>
                                @if($isOverdue)
                                    <span class="badge badge-danger mt-1" style="font-size: 0.72rem; font-weight: 700; padding: 0.15rem 0.5rem;">OVERDUE</span>
                                @endif
                            @else
                                <span class="text-muted" style="font-size: 0.85rem;">-</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <span class="badge badge-gray" style="font-size: 0.8rem; font-weight: 600; padding: 0.3rem 0.65rem;">
                                {{ $t->status_workflow }}
                            </span>
                            <div class="text-muted mt-1" style="font-size: 0.78rem; font-weight: 600;">
                                {{ $t->prioritas_visit }}
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <div class="d-inline-flex gap-1">
                                <!-- Tombol Detail 37 Kolom (Halaman Khusus) - Tampil untuk Semua Role -->
                                <a href="{{ route('risk-trigger.show', $t->id) }}" class="btn btn-outline btn-sm" style="padding: 0.32rem 0.65rem; font-size: 0.82rem; font-weight: 600; border-radius: 6px;" title="Lihat Detail 37 Kolom">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>

                                <!-- Aksi Khusus Admin / Reviewer: Override Keputusan & Hapus -->
                                @if(!$isRa)
                                    @if($t->keputusan_final === 'RISK TRIGGER VISIT')
                                        <a href="{{ route('risk-trigger.assignment.create', ['trigger_id' => $t->id]) }}" class="btn btn-outline-success btn-sm" style="padding: 0.32rem 0.6rem; font-size: 0.82rem; border-radius: 6px;" title="Terbitkan Assignment Visit">
                                            <i class="bi bi-clipboard-plus"></i>
                                        </a>
                                    @endif

                                    <!-- Tombol Override Keputusan (Halaman Khusus) -->
                                    <a href="{{ route('risk-trigger.edit-override', $t->id) }}" class="btn btn-outline btn-sm" style="padding: 0.32rem 0.6rem; font-size: 0.82rem; border-radius: 6px;" title="Override Keputusan">
                                        <i class="bi bi-sliders"></i>
                                    </a>

                                    <!-- Tombol Hapus (khusus manual) -->
                                    @if(str_contains($t->source_wp_id ?? '', 'MANUAL-'))
                                        <form action="{{ route('risk-trigger.destroy', $t->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Hapus trigger manual ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus Trigger" style="padding: 0.32rem 0.6rem; font-size: 0.82rem; border-radius: 6px;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11">
                            <div class="empty-state py-5 text-center">
                                <i class="bi bi-inbox text-muted fs-1 mb-2 d-block"></i>
                                <h6 class="fw-bold mb-1" style="color: var(--bs-blue-dark);">Belum Ada Data Register Trigger</h6>
                                <p class="text-muted mb-3" style="font-size: 0.85rem;">
                                    Klik tombol "Tarik Sinkronisasi" untuk menarik temuan dari Daily Register &amp; Offsite atau gunakan tombol "+ Input Trigger Manual".
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($triggers->hasPages())
        <div class="card-footer bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2 border-top">
            <div class="text-muted" style="font-size: 0.84rem;">
                Halaman <strong>{{ $triggers->currentPage() }}</strong> dari <strong>{{ $triggers->lastPage() }}</strong>
            </div>
            <div>
                {{ $triggers->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Form sync loading
    document.getElementById('formSyncRegister')?.addEventListener('submit', function() {
        const btn = document.getElementById('btnSyncRegister');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menarik Data...';
        }
    });
</script>
@endpush
@endsection
