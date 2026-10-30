@extends('layouts.app')

@section('title', 'KKA Teller & Kas — ' . ($visit->nama_unit ?? 'Onsite Audit'))

@section('content')

@php
    $userRole = strtolower(auth()->user()->role ?? '');
    $isReviewer = in_array($userRole, ['admin', 'kabag_ra', 'kadiv_skai']);
@endphp

<style>
    :root {
        --primary: #1e4b85;
        --primary-light: #2c63a6;
        --primary-dark: #12335e;
        --accent-yellow: #f2c12e;
        --border: #e2e8f0;
        --muted: #64748b;
        --dark: #1e293b;
        --bg-soft: #f8fafc;
    }

    .kka-container {
        padding: 1.5rem;
    }

    /* Page Header */
    .page-header-box {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-header-box h1 {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--primary-dark);
        margin: 0 0 0.25rem 0;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .page-header-box p {
        font-size: 0.85rem;
        color: var(--muted);
        margin: 0;
    }

    /* Sub-navigasi 6 Area CBS */
    .subnav-cbs {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .subnav-item {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 1.15rem;
        border-radius: 8px;
        font-size: 0.84rem;
        font-weight: 600;
        text-decoration: none;
        color: var(--muted);
        background: #ffffff;
        border: 1px solid var(--border);
        transition: all 0.2s ease;
    }

    .subnav-item:hover {
        background: #f1f5f9;
        color: var(--primary);
        border-color: #cbd5e1;
    }

    .subnav-item.active {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
        box-shadow: 0 2px 6px rgba(30, 75, 133, 0.25);
    }

    .subnav-item .nav-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: rgba(0,0,0,0.06);
        font-size: 0.72rem;
    }

    .subnav-item.active .nav-num {
        background: rgba(255,255,255,0.25);
        color: #ffffff;
    }

    /* Stat Cards */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card-custom {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.1rem 1.25rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .stat-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }

    .stat-card-title {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--muted);
        margin-bottom: 0.25rem;
    }

    .stat-card-value {
        font-size: 1.45rem;
        font-weight: 700;
        color: var(--dark);
        line-height: 1.1;
    }

    .stat-card-desc {
        font-size: 0.74rem;
        color: var(--muted);
        margin-top: 0.25rem;
    }

    /* Filter & Toolbar */
    .filter-card {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    /* Main Table Container */
    .main-table-card {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    }

    .main-table-header {
        padding: 1rem 1.25rem;
        background: #ffffff;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .table-kka {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .table-kka th {
        background: #f8fafc;
        padding: 0.85rem 1rem;
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--muted);
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
        white-space: nowrap;
    }

    .table-kka td {
        padding: 0.85rem 1rem;
        font-size: 0.84rem;
        color: var(--dark);
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }

    .table-kka tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Sampling Badges */
    .badge-sampling {
        display: inline-block;
        padding: 0.25rem 0.65rem;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .badge-sampling.mandatory { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    .badge-sampling.certainty { background: #ffedd5; color: #ea580c; border: 1px solid #fed7aa; }
    .badge-sampling.targeted  { background: #fef9c3; color: #ca8a04; border: 1px solid #fef08a; }
    .badge-sampling.initial   { background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }

    /* Risk Badges */
    .badge-risk {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .badge-risk.kritis   { background: #dc2626; color: #ffffff; }
    .badge-risk.high     { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
    .badge-risk.moderate { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .badge-risk.low      { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }

    /* Review Badges */
    .badge-review {
        display: inline-block;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 600;
    }
    .badge-review.approved { background: #dcfce7; color: #166534; }
    .badge-review.revisi   { background: #fee2e2; color: #991b1b; }
    .badge-review.belum    { background: #f1f5f9; color: #475569; }

    /* Empty State */
    .empty-state-box {
        text-align: center;
        padding: 4rem 1.5rem;
    }

    .empty-state-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #f1f5f9;
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin-bottom: 1.25rem;
    }

    .empty-state-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    .empty-state-desc {
        font-size: 0.88rem;
        color: var(--muted);
        max-width: 520px;
        margin: 0 auto 1.5rem auto;
        line-height: 1.5;
    }

    /* Modal Styling */
    .modal-attr-card {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 0.75rem 0.9rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.15s ease;
    }
    .modal-attr-card:hover {
        background: #f8fafc;
    }
    .modal-attr-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--dark);
    }
</style>

<div class="kka-container">

    {{-- 1. HEADER SECTION --}}
    <div class="page-header-box">
        <div>
            <h1>
                <i class="bi bi-wallet2 text-primary"></i>
                KKA 1. Teller &amp; Kas — Onsite Audit
            </h1>
            <p>
                <i class="bi bi-building me-1"></i> Unit: <strong>{{ $visit->nama_unit ?? '-' }}</strong> ({{ $visit->kode_unit ?? '-' }})
                &bull; <i class="bi bi-calendar-event me-1"></i> Periode: <strong>{{ $visit->periode ?? '-' }}</strong>
                @if(isset($visit->status))
                    &bull; <span class="badge bg-light text-dark border">{{ $visit->status }}</span>
                @endif
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('onsite.kka', $visit->id) }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-arrow-left me-1"></i> Ringkasan KKA
            </a>
            <a href="{{ route('onsite.upload', $visit->id) }}" class="btn btn-primary btn-sm px-3" style="background:var(--primary); border-color:var(--primary);">
                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload File CBS
            </a>
            <a href="{{ route('onsite.kka.integration.index', $visit->id) }}" class="btn btn-outline-primary btn-sm px-3">
                <i class="bi bi-shield-check me-1"></i> Kontrol &amp; Integrasi
            </a>
        </div>
    </div>

    {{-- 2. SUB-NAVIGASI 6 AREA CBS --}}
    <div class="subnav-cbs">
        <a href="{{ route('onsite.kka.teller_kas.index', $visit->id) }}" class="subnav-item active">
            <span class="nav-num">1</span>
            <i class="bi bi-wallet2"></i> Teller &amp; Kas
        </a>
        <a href="{{ route('onsite.kka.dpk.index', $visit->id) }}" class="subnav-item">
            <span class="nav-num">2</span>
            <i class="bi bi-piggy-bank"></i> DPK &amp; Layanan
        </a>
        <a href="{{ route('onsite.kka.kredit.index', $visit->id) }}" class="subnav-item">
            <span class="nav-num">3</span>
            <i class="bi bi-cash-coin"></i> Kredit
        </a>
        <a href="{{ route('onsite.kka.biaya.index', $visit->id) }}" class="subnav-item">
            <span class="nav-num">4</span>
            <i class="bi bi-receipt"></i> Biaya &amp; Beban
        </a>
        <a href="{{ route('onsite.kka.atm.index', $visit->id) }}" class="subnav-item">
            <span class="nav-num">5</span>
            <i class="bi bi-credit-card"></i> Kartu ATM
        </a>
        <a href="{{ route('onsite.kka.pengaduan.index', $visit->id) }}" class="subnav-item">
            <span class="nav-num">6</span>
            <i class="bi bi-headset"></i> Pengaduan Nasabah
        </a>
    </div>

    {{-- 3. SUMMARY METRIC CARDS --}}
    <div class="stat-grid">
        <div class="stat-card-custom">
            <div class="stat-card-icon" style="background: rgba(30, 75, 133, 0.08); color: var(--primary);">
                <i class="bi bi-file-earmark-ruled"></i>
            </div>
            <div>
                <div class="stat-card-title">Total Sampel</div>
                <div class="stat-card-value">{{ $summary['total'] ?? (isset($findings) ? $findings->total() : 0) }}</div>
                <div class="stat-card-desc">Sampel Transaksi Teller</div>
            </div>
        </div>

        <div class="stat-card-custom">
            <div class="stat-card-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div>
                <div class="stat-card-title">Status Sesuai</div>
                <div class="stat-card-value text-success">{{ $summary['sesuai'] ?? 0 }}</div>
                <div class="stat-card-desc">Memenuhi 10 Atribut</div>
            </div>
        </div>

        <div class="stat-card-custom">
            <div class="stat-card-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div>
                <div class="stat-card-title">Ada Exception</div>
                <div class="stat-card-value text-danger">{{ $summary['exception'] ?? 0 }}</div>
                <div class="stat-card-desc">Penyimpangan / Temuan</div>
            </div>
        </div>

        <div class="stat-card-custom">
            <div class="stat-card-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <i class="bi bi-shield-fill-exclamation"></i>
            </div>
            <div>
                <div class="stat-card-title">Risiko Tinggi / Kritis</div>
                <div class="stat-card-value text-warning">{{ $summary['high_risk'] ?? 0 }}</div>
                <div class="stat-card-desc">High / Kritis SOP</div>
            </div>
        </div>

        <div class="stat-card-custom">
            <div class="stat-card-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;">
                <i class="bi bi-patch-check-fill"></i>
            </div>
            <div>
                <div class="stat-card-title">Review Approved</div>
                <div class="stat-card-value text-primary">{{ $summary['approved'] ?? 0 }}</div>
                <div class="stat-card-desc">Disetujui Supervisor</div>
            </div>
        </div>
    </div>

    {{-- 4. FILTER & SEARCH TOOLBAR --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('onsite.kka.teller_kas.index', $visit->id) }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm border-start-0" placeholder="Cari ID, Uraian, User, Arsip...">
                </div>
            </div>
            <div class="col-md-2">
                <select name="jenis_sampling" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Sampling</option>
                    <option value="Mandatory" {{ request('jenis_sampling') == 'Mandatory' ? 'selected' : '' }}>Mandatory</option>
                    <option value="Certainty" {{ request('jenis_sampling') == 'Certainty' ? 'selected' : '' }}>Certainty</option>
                    <option value="Targeted" {{ request('jenis_sampling') == 'Targeted' ? 'selected' : '' }}>Targeted</option>
                    <option value="Initial" {{ request('jenis_sampling') == 'Initial' ? 'selected' : '' }}>Initial</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status_uji" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Status Uji</option>
                    <option value="Sesuai" {{ request('status_uji') == 'Sesuai' ? 'selected' : '' }}>Sesuai</option>
                    <option value="Ada Exception" {{ request('status_uji') == 'Ada Exception' ? 'selected' : '' }}>Ada Exception</option>
                    <option value="Belum Diuji" {{ request('status_uji') == 'Belum Diuji' ? 'selected' : '' }}>Belum Diuji</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="risk_level" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Risiko</option>
                    <option value="Low" {{ request('risk_level') == 'Low' ? 'selected' : '' }}>Low</option>
                    <option value="Moderate" {{ request('risk_level') == 'Moderate' ? 'selected' : '' }}>Moderate</option>
                    <option value="High" {{ request('risk_level') == 'High' ? 'selected' : '' }}>High</option>
                    <option value="Kritis" {{ request('risk_level') == 'Kritis' ? 'selected' : '' }}>Kritis</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status_review" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Review</option>
                    <option value="Belum Direview" {{ request('status_review') == 'Belum Direview' ? 'selected' : '' }}>Belum Direview</option>
                    <option value="Revisi" {{ request('status_review') == 'Revisi' ? 'selected' : '' }}>Revisi</option>
                    <option value="Approved" {{ request('status_review') == 'Approved' ? 'selected' : '' }}>Approved</option>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-dark w-100" title="Terapkan Filter">
                    <i class="bi bi-funnel"></i>
                </button>
                @if(request()->hasAny(['q', 'jenis_sampling', 'status_uji', 'risk_level', 'status_review']))
                    <a href="{{ route('onsite.kka.teller_kas.index', $visit->id) }}" class="btn btn-sm btn-outline-danger" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- 5. MAIN TABLE CARD --}}
    <div class="main-table-card">
        <div class="main-table-header">
            <div>
                <span class="fw-bold text-dark me-2">Daftar Sampel Uji Teller &amp; Kas</span>
                <span class="text-muted small">
                    (Menampilkan {{ $findings->count() }} dari {{ $findings->total() }} sampel)
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border">
                    SOP 04.1 — Standar 10 Atribut
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-kka">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 45px;">No</th>
                        <th>Sample ID &amp; Arsip</th>
                        <th>Uraian / Keterangan Transaksi</th>
                        <th class="text-end">Nominal (Rp)</th>
                        <th class="text-center">Jenis Sampling</th>
                        <th class="text-center">Status Uji</th>
                        <th class="text-center">Tingkat Risiko</th>
                        <th class="text-center">Review</th>
                        <th class="text-center" style="width: 130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($findings) && $findings->count() > 0)
                        @foreach($findings as $index => $item)
                            @php
                                $sampleType = strtolower($item->jenis_sampling ?? 'initial');
                                $risk = strtolower($item->risk_level ?? 'low');
                                $review = strtolower($item->status_review ?? 'belum');
                                $isException = ($item->status_uji ?? '') === 'Ada Exception';
                            @endphp
                            <tr>
                                <td class="text-center text-muted fw-semibold">
                                    {{ $findings->firstItem() + $index }}
                                </td>
                                <td>
                                    <div class="font-monospace fw-bold text-primary" style="font-size:0.8rem;">
                                        {{ $item->sample_id ?? '-' }}
                                    </div>
                                    <div class="text-muted small d-flex align-items-center gap-1 mt-0.5">
                                        <i class="bi bi-calendar3"></i> {{ $item->tgl_tx ?? '-' }}
                                        &bull; Arsip: <code>{{ $item->no_arsip ?? '-' }}</code>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->ket_tx ?? 'Tanpa Keterangan' }}</div>
                                    <div class="text-muted small mt-0.5">
                                        <span class="badge bg-light text-dark border me-1">
                                            <i class="bi bi-person me-0.5"></i> {{ $item->kd_user ?? '-' }}
                                        </span>
                                        Alasan: {{ $item->alasan_sampling ?? 'Kriteria Sampling SOP' }}
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-dark font-monospace">
                                    Rp {{ number_format($item->jumlah_tx ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge-sampling {{ $sampleType }}">
                                        {{ $item->jenis_sampling ?? 'Initial' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if(($item->status_uji ?? '') === 'Sesuai')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-check2"></i> Sesuai
                                        </span>
                                    @elseif(($item->status_uji ?? '') === 'Ada Exception')
                                        <span class="badge bg-danger text-white px-2 py-1">
                                            <i class="bi bi-exclamation-circle-fill"></i> Ada Exception
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1">
                                            Belum Diuji
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge-risk {{ $risk }}">
                                        @if($risk === 'kritis')
                                            <i class="bi bi-shield-fill-x"></i>
                                        @elseif($risk === 'high')
                                            <i class="bi bi-shield-fill-exclamation"></i>
                                        @else
                                            <i class="bi bi-shield-fill-check"></i>
                                        @endif
                                        {{ $item->risk_level ?? 'Low' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge-review {{ str_contains($review, 'app') ? 'approved' : (str_contains($review, 'rev') ? 'revisi' : 'belum') }}">
                                        {{ $item->status_review ?? 'Belum' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-sm btn-primary px-2.5 py-1"
                                            style="background:var(--primary); border-color:var(--primary); font-size:0.78rem;"
                                            onclick='openModalUji(@json($item))'>
                                        <i class="bi bi-pencil-square me-1"></i> Uji Atribut
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="9" class="p-0">
                                <div class="empty-state-box">
                                    <div class="empty-state-icon">
                                        <i class="bi bi-wallet2"></i>
                                    </div>
                                    <div class="empty-state-title">
                                        Belum Ada Sampel Transaksi Teller &amp; Kas
                                    </div>
                                    <div class="empty-state-desc">
                                        Kunjungan onsite untuk Unit <strong>{{ $visit->nama_unit ?? 'Terkait' }} ({{ $visit->kode_unit ?? '-' }})</strong> 
                                        belum memiliki data sampel transaksi Teller &amp; Kas. Silakan lakukan upload file CBS transaksi agar sistem dapat men-generate sampel audit secara otomatis.
                                    </div>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('onsite.upload', $visit->id) }}" class="btn btn-primary" style="background:var(--primary); border-color:var(--primary);">
                                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload File CBS Sekarang
                                        </a>
                                        <a href="{{ route('onsite.kka', $visit->id) }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Ringkasan KKA
                                        </a>
                                    </div>

                                    {{-- Info Panduan 10 Atribut --}}
                                    <div class="card mt-4 text-start mx-auto bg-light border-0" style="max-width: 680px; border-radius: 10px;">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center gap-2 mb-2 text-primary fw-bold" style="font-size:0.85rem;">
                                                <i class="bi bi-info-circle-fill"></i> Standar Pengujian 10 Atribut Teller &amp; Kas (SOP 04.1):
                                            </div>
                                            <div class="row g-2 text-muted" style="font-size: 0.78rem;">
                                                <div class="col-6">1. Bukti Asli &amp; Keabsahan Dokumen</div>
                                                <div class="col-6">6. Tanggal, Jam &amp; Batas Cut-Off</div>
                                                <div class="col-6">2. Kesesuaian Saldo CBS vs Dokumen</div>
                                                <div class="col-6">7. Override &amp; Reversal Wajar</div>
                                                <div class="col-6">3. Otorisasi Sesuai Limit Jabatan</div>
                                                <div class="col-6">8. Kepatuhan Transaksi Tunai Besar</div>
                                                <div class="col-6">4. Validitas Identitas &amp; Spesimen Tanda Tangan</div>
                                                <div class="col-6">9. Akurasi Jurnal Posting Buku Besar (GL)</div>
                                                <div class="col-6">5. Denominasi Slip Kas Fisik Sesuai</div>
                                                <div class="col-6">10. Kelengkapan Berkas Pendukung Fisik</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(isset($findings) && method_exists($findings, 'links') && $findings->hasPages())
            <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    Halaman {{ $findings->currentPage() }} dari {{ $findings->lastPage() }}
                </span>
                <div>
                    {{ $findings->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

{{-- 6. MODAL PENGUJIAN 10 ATRIBUT STANDAR SOP (BOOTSTRAP 5 MODAL) --}}
<div class="modal fade" id="modalUjiAtribut" tabindex="-1" aria-labelledby="modalUjiLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            
            {{-- Modal Header --}}
            <div class="modal-header text-white" style="background: var(--primary);">
                <div>
                    <h5 class="modal-title fw-bold" id="modalUjiLabel">
                        <i class="bi bi-clipboard2-check me-2"></i>Lembar Pengujian 10 Atribut — Teller &amp; Kas
                    </h5>
                    <div class="small opacity-75 mt-0.5" id="modalSampleSubtitle">
                        Sample ID: <span id="lblSampleId" class="font-monospace fw-bold"></span> &bull; 
                        User: <span id="lblUser"></span> &bull; 
                        Nominal: <span id="lblNominal" class="fw-bold"></span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-4 bg-light">
                <form id="formUjiAtribut" onsubmit="simpanPengujian(event)">
                    <input type="hidden" id="f_item_id" name="item_id">

                    {{-- Transaction Detail Card --}}
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                        <div class="card-body p-3">
                            <div class="row g-2 text-sm" style="font-size:0.83rem;">
                                <div class="col-md-3">
                                    <span class="text-muted d-block small">Tanggal Transaksi:</span>
                                    <strong id="detTglTx" class="text-dark">-</strong>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted d-block small">No. Arsip:</span>
                                    <code id="detNoArsip" class="text-primary">-</code>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted d-block small">Jenis Sampling:</span>
                                    <span id="detJenisSampling" class="badge-sampling initial">-</span>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted d-block small">Alasan Sampling:</span>
                                    <span id="detAlasan" class="text-dark fw-medium">-</span>
                                </div>
                                <div class="col-12 mt-2 pt-2 border-top">
                                    <span class="text-muted small">Keterangan Transaksi: </span>
                                    <span id="detKetTx" class="fw-bold text-dark">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bagian A: 10 Atribut Kepatuhan SOP --}}
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                        <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex justify-content-between align-items-center">
                            <div class="fw-bold text-primary" style="font-size:0.88rem;">
                                <i class="bi bi-list-check me-1"></i> A. Checklist 10 Atribut Kepatuhan Standar SOP 04.1
                            </div>
                            <span class="badge bg-light text-muted border small">Wajib Diisi</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                @php
                                    $attributes = [
                                        ['key' => 'attr_bukti_asli',        'title' => '1. Bukti Asli / Keabsahan Dokumen'],
                                        ['key' => 'attr_sesuai_cbs',        'title' => '2. Kesesuaian Saldo CBS vs Dokumen'],
                                        ['key' => 'attr_otorisasi',         'title' => '3. Otorisasi Sesuai Limit Jabatan'],
                                        ['key' => 'attr_identitas',         'title' => '4. Identitas & Spesimen Valid'],
                                        ['key' => 'attr_slip_denominasi',   'title' => '5. Slip & Denominasi Kas Fisik Sesuai'],
                                        ['key' => 'attr_cutoff',            'title' => '6. Tanggal / Jam & Cut-off Wajar'],
                                        ['key' => 'attr_override_reversal', 'title' => '7. Override / Reversal Wajar & Sah'],
                                        ['key' => 'attr_tunai_besar',       'title' => '8. Kepatuhan Transaksi Tunai Besar (APU-PPT)'],
                                        ['key' => 'attr_posting_gl',        'title' => '9. Posting Jurnal GL Debit / Kredit Sesuai'],
                                        ['key' => 'attr_dokumentasi',       'title' => '10. Kelengkapan Dokumentasi Pendukung'],
                                    ];
                                @endphp

                                @foreach($attributes as $attr)
                                    <div class="col-md-6">
                                        <div class="modal-attr-card">
                                            <span class="modal-attr-title">{{ $attr['title'] }}</span>
                                            <select class="form-select form-select-sm w-auto attr-selector" 
                                                    id="f_{{ $attr['key'] }}" 
                                                    name="{{ $attr['key'] }}"
                                                    onchange="evaluateAutoStatus()">
                                                <option value="Sesuai">Sesuai</option>
                                                <option value="Tidak Sesuai">Tidak Sesuai</option>
                                                <option value="Tidak Berlaku">Tidak Berlaku</option>
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Bagian B: Status Uji & Penilaian Matriks Risiko --}}
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                        <div class="card-header bg-white py-2.5 px-3 border-bottom">
                            <div class="fw-bold text-dark" style="font-size:0.88rem;">
                                <i class="bi bi-shield-shaded me-1 text-warning"></i> B. Penilaian Risiko &amp; Hasil Pengujian
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Status Uji <span class="text-danger">*</span></label>
                                    <select id="f_status_uji" name="status_uji" class="form-select form-select-sm">
                                        <option value="Sesuai">Sesuai</option>
                                        <option value="Ada Exception">Ada Exception</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Skor Dampak (1 - 5)</label>
                                    <select id="f_skor_dampak" name="skor_dampak" class="form-select form-select-sm" onchange="hitungRiskLevel()">
                                        <option value="1">1 — Sangat Rendah</option>
                                        <option value="2">2 — Rendah</option>
                                        <option value="3">3 — Sedang</option>
                                        <option value="4">4 — Tinggi</option>
                                        <option value="5">5 — Sangat Tinggi</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Skor Kemungkinan (1 - 5)</label>
                                    <select id="f_skor_kemungkinan" name="skor_kemungkinan" class="form-select form-select-sm" onchange="hitungRiskLevel()">
                                        <option value="1">1 — Sangat Jarang</option>
                                        <option value="2">2 — Jarang</option>
                                        <option value="3">3 — Kadang-kadang</option>
                                        <option value="4">4 — Sering</option>
                                        <option value="5">5 — Sangat Sering</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Critical Trigger</label>
                                    <select id="f_critical_trigger" name="critical_trigger" class="form-select form-select-sm" onchange="hitungRiskLevel()">
                                        <option value="Tidak">Tidak</option>
                                        <option value="Ya">Ya (Langsung Kritis)</option>
                                    </select>
                                </div>

                                {{-- Kalkulasi Risiko Dinamis --}}
                                <div class="col-12">
                                    <div class="p-2.5 rounded bg-light border d-flex justify-content-between align-items-center">
                                        <div class="small text-muted">
                                            Matriks Risiko: <strong id="lblRumusRisk" class="text-dark">1 x 1 = 1</strong>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="small fw-semibold text-muted">Tingkat Risiko:</span>
                                            <span id="lblRiskBadge" class="badge-risk low">Low</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bagian C: Rincian Temuan & Simpulan Auditor --}}
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                        <div class="card-header bg-white py-2.5 px-3 border-bottom">
                            <div class="fw-bold text-dark" style="font-size:0.88rem;">
                                <i class="bi bi-pencil-square me-1 text-primary"></i> C. Rincian Temuan &amp; Simpulan Auditor
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Jenis Exception</label>
                                    <input type="text" id="f_jenis_exception" name="jenis_exception" class="form-control form-control-sm" placeholder="Contoh: Selisih Kas / Override Tanpa Otorisasi">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Kriteria / Ketentuan SOP</label>
                                    <input type="text" id="f_kriteria" name="kriteria" class="form-control form-control-sm" placeholder="Contoh: SOP 04.1 Butir 3 Mengenai Limit Kas">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-dark">Kondisi / Fakta Temuan</label>
                                    <textarea id="f_kondisi" name="kondisi" rows="2" class="form-control form-control-sm" placeholder="Uraikan kondisi penyimpangan yang ditemukan saat audit fisik/dokumen..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Penyebab (Sebab)</label>
                                    <textarea id="f_sebab" name="sebab" rows="2" class="form-control form-control-sm" placeholder="Faktor penyebab penyimpangan..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Dampak / Risiko Finansial</label>
                                    <textarea id="f_dampak" name="dampak" rows="2" class="form-control form-control-sm" placeholder="Potensi kerugian finansial atau risiko reputasi..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-dark">Simpulan Resident Auditor (RA)</label>
                                    <textarea id="f_simpulan_ra" name="simpulan_ra" rows="2" class="form-control form-control-sm" placeholder="Kesimpulan akhir pengujian dan rekomendasi tindak lanjut..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bagian D: Review Atasan (Admin/Kabag/Kadiv) --}}
                    @if($isReviewer)
                        <div class="card border-0 shadow-sm" style="border-radius: 10px; border-left: 4px solid #10b981 !important;">
                            <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex justify-content-between align-items-center">
                                <div class="fw-bold text-success" style="font-size:0.88rem;">
                                    <i class="bi bi-shield-check me-1"></i> D. Review &amp; Keputusan Supervisor (Admin / Kabag / Kadiv)
                                </div>
                                <span class="badge bg-success-subtle text-success">Porsi Reviewer</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-dark">Status Review</label>
                                        <select id="f_status_review" name="status_review" class="form-select form-select-sm">
                                            <option value="Belum Direview">Belum Direview</option>
                                            <option value="Revisi">Revisi</option>
                                            <option value="Approved">Approved</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label small fw-bold text-dark">Catatan Reviewer</label>
                                        <input type="text" id="f_catatan_reviewer" name="catatan_reviewer" class="form-control form-control-sm" placeholder="Instruksi perbaikan atau catatan persetujuan...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="modal-footer px-0 pb-0 pt-3 border-top mt-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">
                            Tutup
                        </button>
                        <button type="submit" id="btnSimpan" class="btn btn-primary btn-sm px-4" style="background:var(--primary); border-color:var(--primary);">
                            <i class="bi bi-save me-1"></i> Simpan Hasil Pengujian
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentModal = null;

    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('modalUjiAtribut');
        if (modalEl) {
            currentModal = new bootstrap.Modal(modalEl);
        }
    });

    function openModalUji(item) {
        if (!item) return;

        // Set ID
        document.getElementById('f_item_id').value = item.id;

        // Set Header Labels
        document.getElementById('lblSampleId').innerText = item.sample_id || '-';
        document.getElementById('lblUser').innerText = item.kd_user || '-';
        document.getElementById('lblNominal').innerText = 'Rp ' + Number(item.jumlah_tx || 0).toLocaleString('id-ID');

        // Set Detail box
        document.getElementById('detTglTx').innerText = item.tgl_tx || '-';
        document.getElementById('detNoArsip').innerText = item.no_arsip || '-';
        document.getElementById('detKetTx').innerText = item.ket_tx || '-';
        document.getElementById('detAlasan').innerText = item.alasan_sampling || '-';

        const jenisBadge = document.getElementById('detJenisSampling');
        const jenis = (item.jenis_sampling || 'Initial').toLowerCase();
        jenisBadge.className = 'badge-sampling ' + jenis;
        jenisBadge.innerText = item.jenis_sampling || 'Initial';

        // Set 10 Atribut
        const attrKeys = [
            'attr_bukti_asli', 'attr_sesuai_cbs', 'attr_otorisasi',
            'attr_identitas', 'attr_slip_denominasi', 'attr_cutoff',
            'attr_override_reversal', 'attr_tunai_besar', 'attr_posting_gl',
            'attr_dokumentasi'
        ];

        attrKeys.forEach(key => {
            const el = document.getElementById('f_' + key);
            if (el) {
                el.value = item[key] || 'Sesuai';
            }
        });

        // Set Status & Nilai Risiko
        document.getElementById('f_status_uji').value = item.status_uji || 'Sesuai';
        document.getElementById('f_skor_dampak').value = item.skor_dampak || 1;
        document.getElementById('f_skor_kemungkinan').value = item.skor_kemungkinan || 1;
        document.getElementById('f_critical_trigger').value = item.critical_trigger || 'Tidak';

        // Set Rincian Temuan
        document.getElementById('f_jenis_exception').value = item.jenis_exception || '';
        document.getElementById('f_kriteria').value = item.kriteria || '';
        document.getElementById('f_kondisi').value = item.kondisi || '';
        document.getElementById('f_sebab').value = item.sebab || '';
        document.getElementById('f_dampak').value = item.dampak || '';
        document.getElementById('f_simpulan_ra').value = item.simpulan_ra || '';

        // Supervisor Review
        const revEl = document.getElementById('f_status_review');
        if (revEl) {
            revEl.value = item.status_review || 'Belum Direview';
            document.getElementById('f_catatan_reviewer').value = item.catatan_reviewer || '';
        }

        hitungRiskLevel();

        if (currentModal) {
            currentModal.show();
        }
    }

    function evaluateAutoStatus() {
        const attrKeys = [
            'attr_bukti_asli', 'attr_sesuai_cbs', 'attr_otorisasi',
            'attr_identitas', 'attr_slip_denominasi', 'attr_cutoff',
            'attr_override_reversal', 'attr_tunai_besar', 'attr_posting_gl',
            'attr_dokumentasi'
        ];

        let adaTidakSesuai = false;
        attrKeys.forEach(k => {
            const val = document.getElementById('f_' + k)?.value;
            if (val === 'Tidak Sesuai') {
                adaTidakSesuai = true;
            }
        });

        const statusUjiEl = document.getElementById('f_status_uji');
        if (adaTidakSesuai) {
            statusUjiEl.value = 'Ada Exception';
        } else {
            statusUjiEl.value = 'Sesuai';
        }
    }

    function hitungRiskLevel() {
        const dampak = parseInt(document.getElementById('f_skor_dampak').value) || 1;
        const kemungkinan = parseInt(document.getElementById('f_skor_kemungkinan').value) || 1;
        const critical = document.getElementById('f_critical_trigger').value === 'Ya';

        const skor = dampak * kemungkinan;
        document.getElementById('lblRumusRisk').innerText = `${dampak} x ${kemungkinan} = ${skor}`;

        let riskLevel = 'Low';
        let badgeClass = 'low';

        if (critical || skor >= 20) {
            riskLevel = 'Kritis';
            badgeClass = 'kritis';
        } else if (skor >= 12) {
            riskLevel = 'High';
            badgeClass = 'high';
        } else if (skor >= 6) {
            riskLevel = 'Moderate';
            badgeClass = 'moderate';
        }

        const badge = document.getElementById('lblRiskBadge');
        badge.className = 'badge-risk ' + badgeClass;
        badge.innerText = riskLevel;
    }

    function simpanPengujian(event) {
        event.preventDefault();

        const itemId = document.getElementById('f_item_id').value;
        if (!itemId) return;

        const btn = document.getElementById('btnSimpan');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

        const payload = {
            attr_bukti_asli:        document.getElementById('f_attr_bukti_asli').value,
            attr_sesuai_cbs:        document.getElementById('f_attr_sesuai_cbs').value,
            attr_otorisasi:         document.getElementById('f_attr_otorisasi').value,
            attr_identitas:         document.getElementById('f_attr_identitas').value,
            attr_slip_denominasi:   document.getElementById('f_attr_slip_denominasi').value,
            attr_cutoff:            document.getElementById('f_attr_cutoff').value,
            attr_override_reversal: document.getElementById('f_attr_override_reversal').value,
            attr_tunai_besar:       document.getElementById('f_attr_tunai_besar').value,
            attr_posting_gl:        document.getElementById('f_attr_posting_gl').value,
            attr_dokumentasi:       document.getElementById('f_attr_dokumentasi').value,
            status_uji:             document.getElementById('f_status_uji').value,
            jenis_exception:        document.getElementById('f_jenis_exception').value,
            kriteria:               document.getElementById('f_kriteria').value,
            kondisi:                document.getElementById('f_kondisi').value,
            sebab:                  document.getElementById('f_sebab').value,
            dampak:                 document.getElementById('f_dampak').value,
            skor_dampak:            document.getElementById('f_skor_dampak').value,
            skor_kemungkinan:       document.getElementById('f_skor_kemungkinan').value,
            critical_trigger:       document.getElementById('f_critical_trigger').value,
            simpulan_ra:            document.getElementById('f_simpulan_ra').value,
            _token:                 '{{ csrf_token() }}'
        };

        // Kirim update pengujian RA
        fetch(`/onsite/kka/teller-kas/${itemId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Jika ada porsi review atasan
                const revEl = document.getElementById('f_status_review');
                if (revEl) {
                    const reviewPayload = {
                        status_review: revEl.value,
                        catatan_reviewer: document.getElementById('f_catatan_reviewer').value,
                        _token: '{{ csrf_token() }}'
                    };
                    return fetch(`/onsite/kka/teller-kas/${itemId}/review`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(reviewPayload)
                    });
                }
            } else {
                throw new Error(data.message || 'Gagal menyimpan pengujian');
            }
        })
        .then(() => {
            if (currentModal) currentModal.hide();
            window.location.reload();
        })
        .catch(err => {
            alert('Terjadi kesalahan: ' + err.message);
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }
</script>
@endpush

@endsection
