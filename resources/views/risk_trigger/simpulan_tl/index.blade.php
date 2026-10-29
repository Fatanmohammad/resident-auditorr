@extends('layouts.app')
@section('title', 'Klarifikasi & Simpulan TL (WP-RA-06-E) - Risk Trigger Visit (SOP 06)')

@section('content')
<!-- Header Harmonized SOP 06 -->
<div class="rtv-header">
    <div>
        <div class="rtv-breadcrumb">
            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a> &rsaquo;
            <span>Risk Trigger Visit (SOP 06)</span> &rsaquo;
            <span class="text-dark fw-semibold">Klarifikasi &amp; Simpulan TL</span>
        </div>
        <h4 class="rtv-title">
            <i class="bi bi-check2-circle text-primary"></i> Klarifikasi, Simpulan &amp; Tindak Lanjut
            <span class="badge-wp">WP-RA-06-E</span>
            @if($isRa)
                <span class="badge-role-scope"><i class="bi bi-geo-alt-fill text-success me-1"></i> Cabang: {{ $userCabangName }}</span>
            @else
                <span class="badge-role-scope"><i class="bi bi-shield-check text-primary me-1"></i> Mode Reviewer / Admin (Global)</span>
            @endif
        </h4>
        <p class="rtv-subtitle">
            Register Klarifikasi &amp; Bukti, Rekapitulasi 23 Kolom Excel, Formula Otomatis Status Closure (Sel W6), dan Sinkronisasi Lintas Sheet SOP 06.
        </p>
    </div>
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

{{-- 6 Summary KPI Cards (Harmonized) --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-2">
        <div class="rtv-kpi-card accent-primary">
            <div class="rtv-kpi-title">
                <span>Total Visit</span>
                <i class="bi bi-diagram-3 text-primary fs-6"></i>
            </div>
            <div class="rtv-kpi-val">{{ number_format($stats['total']) }}</div>
            <div class="rtv-kpi-sub">Penugasan RTV Aktif</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="rtv-kpi-card accent-success">
            <div class="rtv-kpi-title">
                <span>Closed / Tuntas</span>
                <i class="bi bi-check-circle text-success fs-6"></i>
            </div>
            <div class="rtv-kpi-val text-success">{{ number_format($stats['closed']) }}</div>
            <div class="rtv-kpi-sub">Selesai &amp; FINAL</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="rtv-kpi-card accent-danger">
            <div class="rtv-kpi-title">
                <span>Belum Ditutup</span>
                <i class="bi bi-clock text-danger fs-6"></i>
            </div>
            <div class="rtv-kpi-val text-danger">{{ number_format($stats['belum_ditutup']) }}</div>
            <div class="rtv-kpi-sub">Belum Review FINAL</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="rtv-kpi-card accent-warning">
            <div class="rtv-kpi-title">
                <span>TL Belum Lengkap</span>
                <i class="bi bi-exclamation-triangle text-warning fs-6"></i>
            </div>
            <div class="rtv-kpi-val" style="color: #c2410c;">{{ number_format($stats['tl_belum_lengkap']) }}</div>
            <div class="rtv-kpi-sub">TL/PIC/Due Date kosong</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="rtv-kpi-card accent-warning">
            <div class="rtv-kpi-title">
                <span>Klarifikasi Open</span>
                <i class="bi bi-hourglass-split text-warning fs-6"></i>
            </div>
            <div class="rtv-kpi-val" style="color: #92400e;">{{ number_format($stats['klarifikasi_open']) }}</div>
            <div class="rtv-kpi-sub">Belum Closed</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="rtv-kpi-card accent-primary">
            <div class="rtv-kpi-title">
                <span>Review FINAL</span>
                <i class="bi bi-shield-check text-primary fs-6"></i>
            </div>
            <div class="rtv-kpi-val text-primary">{{ number_format($stats['final_review']) }}</div>
            <div class="rtv-kpi-sub">Divalidasi Admin</div>
        </div>
    </div>
</div>

{{-- Filter Card Harmonized --}}
<div class="rtv-filter-card">
    <form action="{{ route('risk-trigger.simpulan-tl.index') }}" method="GET" class="row g-2 align-items-end">
        @if(!$isRa)
        <div class="col-md-3">
            <label class="form-label"><i class="bi bi-buildings me-1 text-primary"></i>Kantor Cabang / Unit:</label>
            <select name="kode_unit" class="form-select">
                <option value="">-- Semua Cabang (Global) --</option>
                @foreach($units as $u)
                    <option value="{{ $u->unit_code }}" {{ $filterKodeUnit == $u->unit_code ? 'selected' : '' }}>
                        {{ $u->unit_code }} - {{ $u->unit_name }}
                    </option>
                @endforeach
            </select>
        </div>
        @else
        <div class="col-md-3">
            <label class="form-label"><i class="bi bi-shield-lock-fill me-1 text-primary"></i>Cabang Tugas (Isolasi RA):</label>
            <input type="text" class="form-control bg-light" value="{{ $userCabangName }}" readonly title="Data otomatis dibatasi untuk cabang penugasan Anda">
        </div>
        @endif

        <div class="col-md-2">
            <label class="form-label"><i class="bi bi-check2-circle me-1 text-primary"></i>Status Closure (Kolom W):</label>
            <select name="status_closure" class="form-select">
                <option value="">-- Semua Status --</option>
                <option value="CLOSED/DITERUSKAN" {{ request('status_closure') == 'CLOSED/DITERUSKAN' ? 'selected' : '' }}>CLOSED / DITERUSKAN</option>
                <option value="BELUM DITUTUP" {{ request('status_closure') == 'BELUM DITUTUP' ? 'selected' : '' }}>BELUM DITUTUP</option>
                <option value="TL BELUM LENGKAP" {{ request('status_closure') == 'TL BELUM LENGKAP' ? 'selected' : '' }}>TL BELUM LENGKAP</option>
                <option value="KLARIFIKASI BELUM SELESAI" {{ request('status_closure') == 'KLARIFIKASI BELUM SELESAI' ? 'selected' : '' }}>KLARIFIKASI BELUM SELESAI</option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label"><i class="bi bi-shield-check me-1 text-primary"></i>Status Review (Kolom V):</label>
            <select name="status_review" class="form-select">
                <option value="">-- Semua Review --</option>
                <option value="DRAFT" {{ request('status_review') == 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
                <option value="REVISI" {{ request('status_review') == 'REVISI' ? 'selected' : '' }}>REVISI</option>
                <option value="FINAL" {{ request('status_review') == 'FINAL' ? 'selected' : '' }}>FINAL</option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label"><i class="bi bi-chat-left-text me-1 text-primary"></i>Status Klarifikasi (H):</label>
            <select name="status_klarifikasi" class="form-select">
                <option value="">-- Semua --</option>
                <option value="SELESAI" {{ request('status_klarifikasi') == 'SELESAI' ? 'selected' : '' }}>SELESAI</option>
                <option value="MASIH OPEN" {{ request('status_klarifikasi') == 'MASIH OPEN' ? 'selected' : '' }}>MASIH OPEN</option>
                <option value="TIDAK ADA" {{ request('status_klarifikasi') == 'TIDAK ADA' ? 'selected' : '' }}>TIDAK ADA</option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label"><i class="bi bi-search me-1 text-primary"></i>Cari Kata Kunci:</label>
            <input type="text" name="q" class="form-control" placeholder="Visit ID, PIC, Simpulan..." value="{{ request('q') }}">
        </div>

        <div class="col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill flex-fill" title="Terapkan Filter">
                <i class="bi bi-funnel-fill"></i>
            </button>
            <a href="{{ route('risk-trigger.simpulan-tl.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill flex-fill text-center" title="Reset Filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

{{-- Master Table Simpulan_TL (23 Kolom Excel: A s.d. W) --}}
<div class="rtv-table-container">
    <div class="rtv-table-header">
        <h6 class="rtv-table-title">
            <i class="bi bi-table text-primary"></i> Rekapitulasi Simpulan &amp; Tindak Lanjut (23 Kolom Excel: A s.d. W)
        </h6>
        <div class="d-flex align-items-center gap-2">
            <span class="badge-role-scope">Total: <strong>{{ number_format($simpulanList->total()) }}</strong> Visit</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table rtv-table mb-0" style="min-width: 2500px;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 100px;">Aksi</th>
                    <th style="min-width: 160px;">Visit ID (A)</th>
                    <th style="min-width: 150px;">Trigger ID (B)</th>
                    <th style="min-width: 160px;">Unit / Kantor (C)</th>
                    <th style="min-width: 130px;">Area Risiko (D)</th>
                    <th style="min-width: 120px; text-align: center;">Risk Level (E)</th>
                    <th style="min-width: 120px; text-align: center;">Trigger Terbukti? (F)</th>
                    <th class="text-center" style="min-width: 130px;">Jml Exception (G)</th>
                    <th style="min-width: 140px; text-align: center;">Status Klarifikasi (H)</th>
                    <th style="min-width: 240px;">Simpulan Akhir (I)</th>
                    <th style="min-width: 150px;">Klasifikasi Hasil (J)</th>
                    <th style="min-width: 120px; text-align: center;">Perlu Perluasan? (K)</th>
                    <th style="min-width: 180px;">Scope Perluasan (L)</th>
                    <th style="min-width: 200px;">Tindak Lanjut Awal (M)</th>
                    <th style="min-width: 140px;">PIC Unit (N)</th>
                    <th style="min-width: 110px;">Due Date (O)</th>
                    <th style="min-width: 130px;">Level Eskalasi (P)</th>
                    <th style="min-width: 150px;">Tujuan Eskalasi (Q)</th>
                    <th style="min-width: 130px;">No Laporan (R)</th>
                    <th style="min-width: 150px;">Referensi Bukti (S)</th>
                    <th style="min-width: 140px;">RA Pelaksana (T)</th>
                    <th style="min-width: 140px;">Reviewer (U)</th>
                    <th style="min-width: 140px; text-align: center;">Status Review (V)</th>
                    <th style="min-width: 170px; text-align: center;">Status Closure (W)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($simpulanList as $item)
                @php
                    $riskCls = match(strtoupper($item->risk_level_awal)) {
                        'KRITIS' => 'badge-risk-kritis',
                        'TINGGI', 'HIGH' => 'badge-risk-tinggi',
                        'SEDANG' => 'badge-risk-sedang',
                        default => 'badge-risk-rendah'
                    };

                    $closureCls = match($item->status_closure) {
                        'CLOSED/DITERUSKAN' => 'badge-status-success',
                        'TL BELUM LENGKAP', 'KLARIFIKASI BELUM SELESAI' => 'badge-status-warning',
                        default => 'badge-status-danger'
                    };

                    $reviewCls = match($item->status_review) {
                        'FINAL' => 'badge-status-success',
                        'REVISI' => 'badge-status-warning',
                        default => 'badge-status-neutral'
                    };
                @endphp
                <tr>
                    {{-- Aksi --}}
                    <td class="text-center">
                        <a href="{{ route('risk-trigger.simpulan-tl.edit', $item->id) }}" class="btn btn-sm btn-primary rounded-pill px-2.5 py-1 text-nowrap fw-semibold" title="Buka Lembar Kerja & Register Klarifikasi">
                            <i class="bi bi-pencil-square me-1"></i> Buka Simpulan
                        </a>
                    </td>

                    {{-- Kolom A: Visit ID --}}
                    <td><span class="badge-code font-monospace fw-bold text-primary">{{ $item->visit_id }}</span></td>

                    {{-- Kolom B: Trigger ID --}}
                    <td><span class="font-monospace small text-muted">{{ $item->trigger_id ?: '-' }}</span></td>

                    {{-- Kolom C: Unit / Kantor --}}
                    <td>
                        <div class="fw-semibold text-dark">{{ $item->unit_kantor }}</div>
                        <span class="badge-code">{{ $item->kode_unit }}</span>
                    </td>

                    {{-- Kolom D: Area Risiko --}}
                    <td><span class="fw-bold text-dark">{{ $item->area }}</span></td>

                    {{-- Kolom E: Risk Level Awal (Harmonized) --}}
                    <td style="text-align: center;">
                        <span class="badge-risk {{ $riskCls }}">
                            {{ $item->risk_level_awal ?: 'RENDAH' }}
                        </span>
                    </td>

                    {{-- Kolom F: Trigger Terbukti? --}}
                    <td style="text-align: center;">
                        @if($item->trigger_terbukti === 'Ya')
                            <span class="badge-status badge-status-danger">Ya</span>
                        @elseif($item->trigger_terbukti === 'Tidak')
                            <span class="badge-status badge-status-success">Tidak</span>
                        @else
                            <span class="badge-status badge-status-neutral">-</span>
                        @endif
                    </td>

                    {{-- Kolom G: Jumlah Exception --}}
                    <td class="text-center fw-bold">
                        @if($item->jumlah_exception > 0)
                            <span class="badge-status badge-status-danger">
                                <i class="bi bi-exclamation-octagon-fill me-1"></i> {{ $item->jumlah_exception }} Temuan
                            </span>
                        @else
                            <span class="badge-status badge-status-success">
                                <i class="bi bi-check-circle me-1"></i> 0 Temuan
                            </span>
                        @endif
                    </td>

                    {{-- Kolom H: Status Klarifikasi --}}
                    <td style="text-align: center;">
                        @if($item->status_klarifikasi === 'SELESAI')
                            <span class="badge-status badge-status-success"><i class="bi bi-check-circle-fill me-1"></i> SELESAI</span>
                        @elseif($item->status_klarifikasi === 'MASIH OPEN')
                            <span class="badge-status badge-status-warning"><i class="bi bi-exclamation-circle-fill me-1"></i> MASIH OPEN</span>
                        @else
                            <span class="badge-status badge-status-neutral">TIDAK ADA</span>
                        @endif
                    </td>

                    {{-- Kolom I: Simpulan Akhir --}}
                    <td style="max-width: 240px;">
                        <div class="text-truncate small" title="{{ $item->simpulan_akhir }}">
                            {{ $item->simpulan_akhir ?: '-' }}
                        </div>
                    </td>

                    {{-- Kolom J: Klasifikasi Hasil --}}
                    <td>
                        <span class="badge-status badge-status-info">{{ $item->klasifikasi_hasil ?: 'Normal' }}</span>
                    </td>

                    {{-- Kolom K: Perlu Perluasan? --}}
                    <td style="text-align: center;">
                        @if($item->perlu_perluasan === 'Ya')
                            <span class="badge-status badge-status-warning">Ya</span>
                        @else
                            <span class="badge-status badge-status-neutral">Tidak</span>
                        @endif
                    </td>

                    {{-- Kolom L: Scope Perluasan --}}
                    <td style="max-width: 180px;">
                        <div class="text-truncate small text-muted" title="{{ $item->scope_perluasan }}">
                            {{ $item->scope_perluasan ?: '-' }}
                        </div>
                    </td>

                    {{-- Kolom M: Tindak Lanjut Awal --}}
                    <td style="max-width: 200px;">
                        <div class="text-truncate small text-muted" title="{{ $item->tindak_lanjut_awal }}">
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
                            <span class="badge-status badge-status-warning">{{ $item->level_eskalasi }}</span>
                        @else
                            <span class="badge-status badge-status-neutral">Tidak Perlu</span>
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
                    <td style="text-align: center;">
                        <span class="badge-status {{ $reviewCls }}">
                            <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i> {{ $item->status_review }}
                        </span>
                    </td>

                    {{-- Kolom W: Status Closure (Rumus Excel Sel W6) --}}
                    <td style="text-align: center;">
                        <span class="badge-status {{ $closureCls }}">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ $item->status_closure }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="24" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                        <div class="fw-semibold text-dark">Belum Ada Rekaman Simpulan &amp; Tindak Lanjut</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($simpulanList->hasPages())
    <div class="p-3 border-top bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="small text-muted">
            Halaman <strong>{{ $simpulanList->currentPage() }}</strong> dari <strong>{{ $simpulanList->lastPage() }}</strong>
        </div>
        <div>
            {{ $simpulanList->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>
@endsection
