@extends('layouts.app')
@section('title', 'Assignment & Scope Visit (WP-RA-06-C) - Risk Trigger Visit (SOP 06)')

@section('content')
<!-- Header Harmonized SOP 06 -->
<div class="rtv-header">
    <div>
        <div class="rtv-breadcrumb">
            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a> &rsaquo;
            <span>Risk Trigger Visit (SOP 06)</span> &rsaquo;
            <span class="text-dark fw-semibold">Assignment & Scope</span>
        </div>
        <h4 class="rtv-title">
            <i class="bi bi-clipboard-check text-primary"></i> Assignment & Scope Visit
            <span class="badge-wp">WP-RA-06-C</span>
            @if($isRa)
                <span class="badge-role-scope"><i class="bi bi-geo-alt-fill text-success me-1"></i> Cabang: {{ $userCabangName }}</span>
            @else
                <span class="badge-role-scope"><i class="bi bi-shield-check text-primary me-1"></i> Mode Reviewer / Admin (Global)</span>
            @endif
        </h4>
        <p class="rtv-subtitle">
            Penerbitan surat tugas audit lapangan terarah, penetapan batas scope, otorisasi, dan pelaksana visit mengacu pada sheet <em>Assignment_Scope</em>.
        </p>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        @if(!$isRa)
        <a href="{{ route('risk-trigger.assignment.create', array_filter(['kode_unit' => request('kode_unit')])) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs">
            <i class="bi bi-plus-lg me-1"></i>
            <span>+ Buat Assignment Visit</span>
        </a>
        @endif
    </div>
</div>

{{-- 4 Summary KPI Cards (Harmonized) --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-primary">
            <div class="rtv-kpi-title">
                <span>Total Assignment</span>
                <i class="bi bi-clipboard-data text-primary fs-6"></i>
            </div>
            <div class="rtv-kpi-val">{{ number_format($stats['total'] ?? 0) }}</div>
            <div class="rtv-kpi-sub">Diterbitkan dari Risk Trigger</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-success">
            <div class="rtv-kpi-title">
                <span>Siap Dilaksanakan</span>
                <i class="bi bi-check-circle text-success fs-6"></i>
            </div>
            <div class="rtv-kpi-val text-success">{{ number_format($stats['siap'] ?? 0) }}</div>
            <div class="rtv-kpi-sub">Otorisasi & scope lengkap</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-warning">
            <div class="rtv-kpi-title">
                <span>Belum Lengkap</span>
                <i class="bi bi-exclamation-triangle text-warning fs-6"></i>
            </div>
            <div class="rtv-kpi-val" style="color: #c2410c;">{{ number_format($stats['belum_lengkap'] ?? 0) }}</div>
            <div class="rtv-kpi-sub">Perlu melengkapi parameter</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-danger">
            <div class="rtv-kpi-title">
                <span>Bukan Keputusan Visit</span>
                <i class="bi bi-x-circle text-danger fs-6"></i>
            </div>
            <div class="rtv-kpi-val text-danger">{{ number_format($stats['bukan_visit'] ?? 0) }}</div>
            <div class="rtv-kpi-sub">Keputusan != RISK TRIGGER VISIT</div>
        </div>
    </div>
</div>

{{-- Filter Card Harmonized --}}
<div class="rtv-filter-card">
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
            <button type="submit" class="btn btn-primary btn-sm rounded-pill flex-fill" title="Terapkan Filter">
                <i class="bi bi-funnel-fill me-1"></i> Filter
            </button>
            <a href="{{ route('risk-trigger.assignment') }}" class="btn btn-outline-secondary btn-sm rounded-pill flex-fill text-center" title="Reset Filter">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
            </a>
        </div>
    </form>
</div>

{{-- Tabel 22 Kolom Excel Assignment_Scope --}}
<div class="rtv-table-container">
    <div class="rtv-table-header">
        <h6 class="rtv-table-title">
            <i class="bi bi-clipboard-data text-primary"></i> Daftar Penugasan Lapangan (Assignment &amp; Scope — 22 Kolom)
        </h6>
        <div class="d-flex align-items-center gap-2">
            <span class="badge-role-scope">
                Total: <strong>{{ number_format($assignments->total()) }}</strong> Data
            </span>
            <span class="text-muted small">
                (Menampilkan {{ $assignments->firstItem() ?? 0 }} - {{ $assignments->lastItem() ?? 0 }})
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table rtv-table mb-0" style="min-width: 2400px;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="min-width: 170px;">Visit ID (A)</th>
                    <th style="min-width: 160px;">Trigger ID (B)</th>
                    <th style="min-width: 170px;">Unit / Kantor (C &amp; D)</th>
                    <th style="min-width: 130px;">Area (E)</th>
                    <th style="min-width: 120px; text-align: center;">Risk Level (F)</th>
                    <th style="min-width: 160px; text-align: center;">Keputusan Final (G)</th>
                    <th style="min-width: 130px;">KKA Tujuan (H)</th>
                    <th style="min-width: 260px;">Scope / Tujuan Awal (I)</th>
                    <th style="min-width: 240px;">Periode / Populasi (J)</th>
                    <th style="min-width: 120px;">Tgl Visit (K)</th>
                    <th style="min-width: 120px;">Mulai Aktual (L)</th>
                    <th style="min-width: 120px;">Selesai Aktual (M)</th>
                    <th style="min-width: 150px;">RA Sumber (N)</th>
                    <th style="min-width: 160px;">RA Pelaksana (O)</th>
                    <th style="min-width: 150px;">Reviewer (P)</th>
                    <th style="min-width: 160px;">Otorisasi (Q)</th>
                    <th style="min-width: 140px;">Klasifikasi (R)</th>
                    <th class="text-center" style="min-width: 170px;">Status Assignment (S)</th>
                    <th style="min-width: 280px;">Batas Scope (T)</th>
                    <th style="min-width: 280px;">Kriteria Selesai (U)</th>
                    <th style="min-width: 200px;">Perlu Data Unit (V)</th>
                    <th class="text-center" style="min-width: 180px; position: sticky; right: 0; background: #f8fafc; z-index: 2; box-shadow: -4px 0 6px -2px rgba(0,0,0,0.05);">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $idx => $as)
                @php
                    $riskCls = match($as->risk_level) {
                        'KRITIS' => 'badge-risk-kritis',
                        'TINGGI' => 'badge-risk-tinggi',
                        'SEDANG' => 'badge-risk-sedang',
                        default => 'badge-risk-rendah'
                    };

                    $statusCls = match($as->status_assignment) {
                        'SIAP DILAKSANAKAN' => 'badge-status-success',
                        'BELUM LENGKAP' => 'badge-status-warning',
                        default => 'badge-status-danger'
                    };
                @endphp
                <tr>
                    <td class="text-center text-muted fw-bold">{{ $assignments->firstItem() + $idx }}</td>
                    
                    {{-- Kolom A: Visit ID --}}
                    <td>
                        <span class="badge-code font-monospace fw-bold text-primary">
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
                        <span class="badge-code">{{ $as->kode_unit }}</span>
                    </td>

                    {{-- Kolom E: Area --}}
                    <td>
                        <span class="fw-bold text-dark">{{ $as->area }}</span>
                    </td>

                    {{-- Kolom F: Risk Level (Harmonized) --}}
                    <td style="text-align: center;">
                        <span class="badge-risk {{ $riskCls }}">
                            {{ $as->risk_level }}
                        </span>
                    </td>

                    {{-- Kolom G: Keputusan Final (Royal Navy) --}}
                    <td style="text-align: center;">
                        <span class="badge-decision badge-decision-visit">
                            {{ $as->keputusan_final }}
                        </span>
                    </td>

                    {{-- Kolom H: KKA Tujuan --}}
                    <td>
                        <span class="badge-code">{{ $as->kka_tujuan }}</span>
                    </td>

                    {{-- Kolom I: Scope / Tujuan Awal --}}
                    <td>
                        <div style="max-width: 250px; white-space: normal; line-height: 1.35; color: #334155;" class="small">
                            {{ Str::limit($as->scope_tujuan_awal, 95) }}
                        </div>
                    </td>

                    {{-- Kolom J: Periode / Populasi --}}
                    <td>
                        <div style="max-width: 230px; white-space: normal; font-size: 0.8rem; color: #475569;">
                            {{ $as->periode_populasi }}
                        </div>
                    </td>

                    {{-- Kolom K: Tanggal Visit --}}
                    <td>
                        <div class="fw-semibold text-dark small">
                            {{ $as->tanggal_visit ? $as->tanggal_visit->format('d/m/Y') : '-' }}
                        </div>
                    </td>

                    {{-- Kolom L & M: Mulai & Selesai Aktual --}}
                    <td>
                        <span class="{{ $as->mulai_aktual ? 'text-dark fw-semibold small' : 'text-muted small' }}">
                            {{ $as->mulai_aktual ? $as->mulai_aktual->format('d/m/Y') : 'Belum visit' }}
                        </span>
                    </td>
                    <td>
                        <span class="{{ $as->selesai_aktual ? 'text-dark fw-semibold small' : 'text-muted small' }}">
                            {{ $as->selesai_aktual ? $as->selesai_aktual->format('d/m/Y') : 'Belum selesai' }}
                        </span>
                    </td>

                    {{-- Kolom N: Assigned RA Sumber --}}
                    <td>
                        <span class="small text-muted">{{ $as->assigned_ra_sumber ?: '-' }}</span>
                    </td>

                    {{-- Kolom O: RA Pelaksana --}}
                    <td>
                        <span class="fw-semibold text-dark small"><i class="bi bi-person-fill text-primary me-1"></i>{{ $as->ra_pelaksana ?: '-' }}</span>
                    </td>

                    {{-- Kolom P: Reviewer --}}
                    <td>
                        <span class="text-dark small">{{ $as->reviewer ?: '-' }}</span>
                    </td>

                    {{-- Kolom Q: Referensi Otorisasi --}}
                    <td>
                        <span class="badge-code font-monospace">
                            {{ $as->referensi_otorisasi ?: '-' }}
                        </span>
                    </td>

                    {{-- Kolom R: Klasifikasi --}}
                    <td>
                        <span class="small text-muted">{{ $as->klasifikasi }}</span>
                    </td>

                    {{-- Kolom S: Status Assignment --}}
                    <td class="text-center">
                        <span class="badge-status {{ $statusCls }}">
                            <i class="bi bi-check-circle-fill me-1"></i>{{ $as->status_assignment }}
                        </span>
                    </td>

                    {{-- Kolom T: Batas Scope --}}
                    <td>
                        <div style="max-width: 280px; white-space: normal; line-height: 1.35; font-size: 0.8rem; color: #334155;">
                            {{ $as->batas_scope }}
                        </div>
                    </td>

                    {{-- Kolom U: Kriteria Selesai --}}
                    <td>
                        <div style="max-width: 280px; white-space: normal; line-height: 1.35; font-size: 0.8rem; color: #475569;">
                            {{ $as->kriteria_selesai }}
                        </div>
                    </td>

                    {{-- Kolom V: Perlu Data Unit --}}
                    <td>
                        @if(str_contains($as->perlu_data_unit, 'Tidak'))
                            <span class="badge-status badge-status-info">
                                <i class="bi bi-cpu me-1"></i>{{ $as->perlu_data_unit }}
                            </span>
                        @else
                            <span class="badge-status badge-status-warning">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>{{ $as->perlu_data_unit }}
                            </span>
                        @endif
                    </td>

                    {{-- Kolom Aksi Sticky --}}
                    <td class="text-center px-2 py-2" style="position: sticky; right: 0; background: #ffffff; z-index: 2; box-shadow: -4px 0 6px -2px rgba(0,0,0,0.05);">
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <a href="{{ route('risk-trigger.assignment.edit-dates', $as->id) }}" 
                               class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 py-1" 
                               style="font-size: 0.76rem; font-weight: 600; white-space: nowrap;" 
                               title="Input / Update Tanggal Lapangan (Mulai & Selesai Aktual)">
                                <i class="bi bi-calendar-event me-1"></i> Tgl Aktual
                            </a>

                            <a href="{{ route('risk-trigger.kka.index', ['search' => $as->visit_id]) }}" 
                               class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-1" 
                               style="font-size: 0.76rem; font-weight: 600; white-space: nowrap;" 
                               title="Buka Kertas Kerja Audit (KKA) Pengujian Lapangan">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Buka KKA
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="23" class="text-center py-5 text-muted">
                        <div class="py-4">
                            <i class="bi bi-inbox text-muted fs-1 mb-2 d-block"></i>
                            <h6 class="fw-bold mb-1 text-dark">Belum Ada Data Penugasan Visit</h6>
                            <p class="text-muted mb-3 small" style="max-width: 500px; margin: 0 auto;">
                                @if($isRa)
                                    Belum ada penugasan visit yang diterbitkan untuk cabang Anda ({{ $userCabangName }}).
                                @else
                                    Belum ada penugasan visit yang diterbitkan. Anda dapat menerbitkan penugasan baru dari trigger berstatus RISK TRIGGER VISIT.
                                @endif
                            </p>
                            @if(!$isRa)
                            <a href="{{ route('risk-trigger.assignment.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold">
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
        <div class="text-muted small">
            Menampilkan {{ $assignments->firstItem() }} s.d. {{ $assignments->lastItem() }} dari total {{ $assignments->total() }} penugasan
        </div>
        <div>
            {{ $assignments->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>
@endsection
