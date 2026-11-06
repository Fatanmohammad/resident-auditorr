@extends('layouts.app')

@section('title', 'KKA Kartu ATM — ' . ($visit->nama_unit ?? 'Onsite Audit'))

@section('content')

@php
    $userRole = strtolower(auth()->user()->role ?? '');
    $isReviewer = in_array($userRole, ['admin', 'kabag_ra', 'kadiv_skai']);
@endphp

<style>
    :root {
        --primary: #1e4b85;
        --border: #e2e8f0;
        --muted: #64748b;
        --dark: #1e293b;
    }
    .kka-container { padding: 1.5rem; }
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
        color: #12335e;
        margin: 0 0 0.25rem 0;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .page-header-box p { font-size: 0.85rem; color: var(--muted); margin: 0; }
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
    .subnav-item:hover { background: #f1f5f9; color: var(--primary); }
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
    .subnav-item.active .nav-num { background: rgba(255,255,255,0.25); color: #fff; }
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
    .stat-card-custom:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
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
    .stat-card-value { font-size: 1.45rem; font-weight: 700; color: var(--dark); line-height: 1.1; }
    .stat-card-desc { font-size: 0.74rem; color: var(--muted); margin-top: 0.25rem; }
    .filter-card {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
    }
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
    .table-kka { width: 100%; border-collapse: collapse; margin: 0; }
    .table-kka th {
        background: #f8fafc;
        padding: 0.85rem 1rem;
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
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
    .table-kka tbody tr:hover { background-color: #f8fafc; }
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
    .empty-state-box { text-align: center; padding: 4rem 1.5rem; }
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
    .empty-state-title { font-size: 1.15rem; font-weight: 700; color: var(--dark); margin-bottom: 0.5rem; }
    .empty-state-desc { font-size: 0.88rem; color: var(--muted); max-width: 520px; margin: 0 auto 1.5rem auto; line-height: 1.5; }
    .modal-attr-card {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 0.75rem 0.9rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-attr-card:hover { background: #f8fafc; }
    .modal-attr-title { font-size: 0.8rem; font-weight: 600; color: var(--dark); }
</style>

<div class="kka-container">

    {{-- HEADER --}}
    <div class="page-header-box">
        <div>
            <h1>
                <i class="bi bi-credit-card text-primary"></i>
                KKA 5. Kartu ATM — Onsite Audit
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

    {{-- SUB-NAVIGASI 6 AREA CBS --}}
    <div class="subnav-cbs">
        <a href="{{ route('onsite.kka.teller_kas.index', $visit->id) }}" class="subnav-item">
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
        <a href="{{ route('onsite.kka.atm.index', $visit->id) }}" class="subnav-item active">
            <span class="nav-num">5</span>
            <i class="bi bi-credit-card"></i> Kartu ATM
        </a>
        <a href="{{ route('onsite.kka.pengaduan.index', $visit->id) }}" class="subnav-item">
            <span class="nav-num">6</span>
            <i class="bi bi-headset"></i> Pengaduan Nasabah
        </a>
    </div>

    {{-- STAT CARDS --}}
    <div class="stat-grid">
        <div class="stat-card-custom">
            <div class="stat-card-icon" style="background: rgba(30, 75, 133, 0.08); color: var(--primary);">
                <i class="bi bi-credit-card"></i>
            </div>
            <div>
                <div class="stat-card-title">Total Sampel</div>
                <div class="stat-card-value">{{ $summary['total'] ?? $findings->total() }}</div>
                <div class="stat-card-desc">Sampel Transaksi / Kartu</div>
            </div>
        </div>

        <div class="stat-card-custom">
            <div class="stat-card-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div>
                <div class="stat-card-title">Status Sesuai</div>
                <div class="stat-card-value text-success">{{ $summary['sesuai'] ?? 0 }}</div>
                <div class="stat-card-desc">10 Atribut Memenuhi</div>
            </div>
        </div>

        <div class="stat-card-custom">
            <div class="stat-card-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div>
                <div class="stat-card-title">Ada Exception</div>
                <div class="stat-card-value text-danger">{{ $summary['exception'] ?? 0 }}</div>
                <div class="stat-card-desc">Penyimpangan Fisik / PIN</div>
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

    {{-- FILTER & TOOLBAR --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('onsite.kka.atm.index', $visit->id) }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm border-start-0" placeholder="Cari Sample ID, Kartu, Rekening, Nasabah...">
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
                    <a href="{{ route('onsite.kka.atm.index', $visit->id) }}" class="btn btn-sm btn-outline-danger" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- MAIN TABLE --}}
    <div class="main-table-card">
        <div class="main-table-header">
            <div>
                <span class="fw-bold text-dark me-2">Daftar Sampel Uji Kartu ATM</span>
                <span class="text-muted small">(Menampilkan {{ $findings->count() }} dari {{ $findings->total() }} sampel)</span>
            </div>
            <span class="badge bg-light text-secondary border">SOP 04.1 — Standar 10 Atribut Kartu ATM</span>
        </div>

        <div class="table-responsive">
            <table class="table-kka">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 45px;">No</th>
                        <th>Sample ID &amp; Tanggal</th>
                        <th>No. Kartu &amp; Nasabah</th>
                        <th>No. Rekening</th>
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
                            @endphp
                            <tr>
                                <td class="text-center text-muted fw-semibold">
                                    {{ $findings->firstItem() + $index }}
                                </td>
                                <td>
                                    <div class="font-monospace fw-bold text-primary" style="font-size:0.8rem;">
                                        {{ $item->sample_id ?? '-' }}
                                    </div>
                                    <div class="text-muted small mt-0.5">
                                        <i class="bi bi-calendar3"></i> {{ $item->tgl_transaksi ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark font-monospace">
                                        <i class="bi bi-credit-card-2-front me-1 text-muted"></i>
                                        {{ $item->nomor_kartu ?? '-' }}
                                    </div>
                                    <div class="text-muted small mt-0.5">
                                        {{ $item->nama_nasabah ?? '-' }}
                                        @if($item->alasan_sampling)
                                            &bull; <span class="text-secondary">{{ $item->alasan_sampling }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <code class="text-dark">{{ $item->no_rekening ?? '-' }}</code>
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
                                        <span class="badge bg-light text-muted border px-2 py-1">Belum Diuji</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge-risk {{ $risk }}">
                                        <i class="bi bi-shield-fill"></i> {{ $item->risk_level ?? 'Low' }}
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
                                            onclick='openModalAtm(@json($item))'>
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
                                        <i class="bi bi-credit-card"></i>
                                    </div>
                                    <div class="empty-state-title">
                                        Belum Ada Sampel Kartu ATM
                                    </div>
                                    <div class="empty-state-desc">
                                        Kunjungan onsite untuk Unit <strong>{{ $visit->nama_unit ?? 'Terkait' }} ({{ $visit->kode_unit ?? '-' }})</strong> 
                                        belum memiliki data sampel permohonan atau penerbitan Kartu ATM.
                                    </div>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('onsite.upload', $visit->id) }}" class="btn btn-primary" style="background:var(--primary); border-color:var(--primary);">
                                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload File CBS Sekarang
                                        </a>
                                        <a href="{{ route('onsite.kka', $visit->id) }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Ringkasan KKA
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if(isset($findings) && method_exists($findings, 'links') && $findings->hasPages())
            <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center">
                <span class="text-muted small">Halaman {{ $findings->currentPage() }} dari {{ $findings->lastPage() }}</span>
                <div>{{ $findings->links() }}</div>
            </div>
        @endif
    </div>

</div>

{{-- MODAL PENGUJIAN CHECKLIST KKA STANDAR (SOP 04.2 & DAILY REGISTER SPEC) --}}
@include('onsite.partials.modal_checklist_uji', [
    'sheetTitle'      => 'Kartu ATM',
    'updateUrlPrefix' => '/onsite/kka/atm',
    'checklists'      => $checklists,
])

@endsection
