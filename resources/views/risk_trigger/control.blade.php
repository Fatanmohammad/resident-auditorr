@extends('layouts.app')
@section('title', 'Ringkasan Control - Risk Trigger Visit (SOP 06)')

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

    /* Grid 4 columns for KPI Cards (harmonized with dashboard.blade.php) */
    .kpi-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 1200px) {
        .kpi-stat-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 768px) {
        .kpi-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 480px) {
        .kpi-stat-grid { grid-template-columns: repeat(1, minmax(0, 1fr)); }
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
        padding: 0.85rem 1rem;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    .data-table td {
        font-size: 0.86rem;
        padding: 0.9rem 1rem;
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
        <h4 class="fw-bold mb-1" style="color: var(--bs-blue-dark); font-size: 1.25rem;">
            <i class="bi bi-speedometer2 text-primary me-2"></i>Ringkasan Control - Risk Trigger Visit (SOP 06)
        </h4>
        <p class="text-muted mb-0" style="font-size: 0.86rem;">
            Monitoring pemicu risiko, agregasi temuan lintas modul, penilaian ulang (Likelihood &amp; Impact), dan pengendalian visit terarah.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- 1-Click Sync Button -->
        <form action="{{ route('risk-trigger.sync') }}" method="POST" class="m-0" id="formSyncTrigger">
            @csrf
            @if(!empty($filterKodeUnit))
                <input type="hidden" name="kode_unit" value="{{ $filterKodeUnit }}">
            @endif
            <button type="submit" class="btn btn-outline btn-sm" id="btnSyncTrigger" style="font-size: 0.85rem; font-weight: 600; padding: 0.45rem 0.9rem; border-radius: 8px;">
                <i class="bi bi-arrow-repeat me-1" id="iconSync"></i>
                <span>{{ $isRa ? 'Sinkronkan Data Cabang' : ($filterKodeUnit ? 'Sinkronkan Cabang Terpilih' : 'Sinkronisasi Seluruh Cabang') }}</span>
            </button>
        </form>

        <!-- Input Manual Button (Halaman Khusus) - Khusus Role Admin -->
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

<!-- Filter Bar Cabang / Isolasi Cabang -->
@if(!$isRa)
<div class="card mb-3 shadow-sm border-0" style="background: #ffffff; border-radius: 12px; border: 1px solid var(--border-color);">
    <div class="card-body p-3">
        <form action="{{ route('risk-trigger.control') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-auto d-flex align-items-center gap-2">
                <i class="bi bi-funnel-fill text-primary"></i>
                <span class="fw-bold" style="font-size: 0.88rem; color: #334155;">Filter Pemantauan Cabang:</span>
            </div>
            <div class="col-md-5">
                <select name="kode_unit" class="form-select form-select-sm" style="font-size: 0.86rem;" onchange="this.form.submit()">
                    <option value="">-- Semua Cabang (Agregasi Global) --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->unit_code }}" {{ ($filterKodeUnit == $u->unit_code) ? 'selected' : '' }}>
                            {{ $u->unit_code }} - {{ $u->unit_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-primary btn-sm" style="font-size: 0.82rem; padding: 0.35rem 0.8rem;">
                    <i class="bi bi-filter me-1"></i> Terapkan
                </button>
                @if(!empty($filterKodeUnit))
                    <a href="{{ route('risk-trigger.control') }}" class="btn btn-outline btn-sm" style="font-size: 0.82rem; padding: 0.35rem 0.8rem;">
                        <i class="bi bi-x-circle me-1"></i> Reset Global
                    </a>
                @endif
            </div>
            <div class="col-md-auto ms-auto text-end">
                <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.8rem; padding: 0.4rem 0.75rem;">
                    <i class="bi bi-buildings me-1 text-primary"></i> Cakupan: <strong class="text-dark">{{ $filterKodeUnit ? 'Cabang ' . $filterKodeUnit : 'Global (Seluruh Cabang)' }}</strong>
                </span>
            </div>
        </form>
    </div>
</div>
@else
<div class="card mb-3 shadow-sm border-0" style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
    <div class="card-body py-2.5 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge" style="background: #eff6ff; color: #1d4ed8; font-size: 0.8rem; padding: 0.35rem 0.7rem;">
                <i class="bi bi-shield-lock-fill me-1"></i> Akses RA Cabang
            </span>
            <span style="font-size: 0.86rem; color: #334155;">
                Data indikator, alarm akumulasi, dan matriks risiko di bawah ini terisolasi khusus untuk: <strong class="text-primary">{{ $userCabangName }}</strong>
            </span>
        </div>
        <span class="text-muted" style="font-size: 0.78rem;">
            <i class="bi bi-info-circle me-1"></i> Mode Isolasi Cabang Aktif
        </span>
    </div>
</div>
@endif

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
                            Lihat Daftar Trigger <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<!-- 12 STAT CARDS (HARMONIZED DENGAN STAT-CARD APPLICATION) -->
<div class="kpi-stat-grid">
    <!-- 1. Total Trigger -->
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-diagram-3"></i></div>
        <div class="stat-info">
            <div class="stat-label">1. Total Trigger</div>
            <div class="stat-value">{{ number_format($kpi['total_trigger']) }}</div>
            <div class="stat-sub">Seluruh temuan masuk</div>
        </div>
    </div>

    <!-- 2. Data Valid -->
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">2. Data Valid</div>
            <div class="stat-value" style="color: #065f46;">{{ number_format($kpi['data_valid']) }}</div>
            <div class="stat-sub">7 parameter lengkap</div>
        </div>
    </div>

    <!-- 3. Risk Kritis -->
    <div class="stat-card">
        <div class="stat-icon red"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-info">
            <div class="stat-label">3. Risk Kritis</div>
            <div class="stat-value" style="color: #dc2626;">{{ number_format($kpi['risk_kritis']) }}</div>
            <div class="stat-sub">Score Reassessment &ge; 16</div>
        </div>
    </div>

    <!-- 4. Risk Tinggi -->
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="bi bi-shield-exclamation"></i></div>
        <div class="stat-info">
            <div class="stat-label">4. Risk Tinggi</div>
            <div class="stat-value" style="color: #d97706;">{{ number_format($kpi['risk_tinggi']) }}</div>
            <div class="stat-sub">Score Reassessment 10-15</div>
        </div>
    </div>

    <!-- 5. Keputusan Visit -->
    <div class="stat-card">
        <div class="stat-icon purple"><i class="bi bi-lightning-charge"></i></div>
        <div class="stat-info">
            <div class="stat-label">5. Keputusan Visit</div>
            <div class="stat-value" style="color: #7c3aed;">{{ number_format($kpi['keputusan_visit']) }}</div>
            <div class="stat-sub">Perlu uji lapangan</div>
        </div>
    </div>

    <!-- 6. Menunggu Assignment -->
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="bi bi-hourglass-split"></i></div>
        <div class="stat-info">
            <div class="stat-label">6. Menunggu Assignment</div>
            <div class="stat-value" style="color: #b45309;">{{ number_format($kpi['menunggu_assignment']) }}</div>
            <div class="stat-sub">Antrean penetapan RA</div>
        </div>
    </div>

    <!-- 7. Assignment Siap -->
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-calendar-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">7. Assignment Siap</div>
            <div class="stat-value" style="color: var(--bs-blue);">{{ number_format($kpi['assignment_siap']) }}</div>
            <div class="stat-sub">Scope siap dilaksanakan</div>
        </div>
    </div>

    <!-- 8. Visit Dalam Proses -->
    <div class="stat-card">
        <div class="stat-icon purple"><i class="bi bi-arrow-repeat"></i></div>
        <div class="stat-info">
            <div class="stat-label">8. Visit Dalam Proses</div>
            <div class="stat-value" style="color: #6366f1;">{{ number_format($kpi['visit_dalam_proses']) }}</div>
            <div class="stat-sub">Pemeriksaan lapangan</div>
        </div>
    </div>

    <!-- 9. Simpulan Tersedia -->
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-clipboard2-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">9. Simpulan Tersedia</div>
            <div class="stat-value" style="color: #0d9488;">{{ number_format($kpi['simpulan_tersedia']) }}</div>
            <div class="stat-sub">KKA visit diselesaikan</div>
        </div>
    </div>

    <!-- 10. Masih Open -->
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-folder2-open"></i></div>
        <div class="stat-info">
            <div class="stat-label">10. Masih Open</div>
            <div class="stat-value" style="color: #475569;">{{ number_format($kpi['masih_open']) }}</div>
            <div class="stat-sub">Belum final closure</div>
        </div>
    </div>

    <!-- 11. Overdue SLA -->
    <div class="stat-card">
        <div class="stat-icon red"><i class="bi bi-clock-history"></i></div>
        <div class="stat-info">
            <div class="stat-label">11. Overdue SLA</div>
            <div class="stat-value" style="color: #b91c1c;">{{ number_format($kpi['overdue_sla']) }}</div>
            <div class="stat-sub">Melewati batas hari kerja</div>
        </div>
    </div>

    <!-- 12. Indikasi Fraud -->
    <div class="stat-card">
        <div class="stat-icon red"><i class="bi bi-shield-slash"></i></div>
        <div class="stat-info">
            <div class="stat-label">12. Indikasi Fraud</div>
            <div class="stat-value" style="color: #991b1b;">{{ number_format($kpi['indikasi_fraud']) }}</div>
            <div class="stat-sub">Rujukan SOP 09 Fraud</div>
        </div>
    </div>
</div>

<!-- 2 MAIN TABLES HARMONIZED -->
<div class="row g-3 mb-4">
    <!-- Card Kiri: Ringkasan 8 Keputusan Final -->
    <div class="col-lg-5">
        <div class="table-container h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-diagram-2 text-primary fs-5"></i>
                    <h6 class="mb-0 fw-bold" style="color: var(--bs-blue-dark); font-size: 0.95rem;">
                        Ringkasan Keputusan Final (SOP 06)
                    </h6>
                </div>
                <span class="badge badge-gray">8 Kategori</span>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Keputusan Final</th>
                            <th style="width: 25%; text-align: center;">Jumlah</th>
                            <th style="width: 20%; text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($keputusanSummary as $kep => $jml)
                            @php
                                $badgeCls = match($kep) {
                                    'RISK TRIGGER VISIT' => 'badge-danger',
                                    'KLARIFIKASI OFFSITE' => 'badge-warning',
                                    'MONITORING' => 'badge-info',
                                    'SOP 05 - SURPRISE AUDIT' => 'badge-purple',
                                    'SOP 07 - MONITORING TL' => 'badge-gray',
                                    'SOP 09 - PEMERIKSAAN AWAL FRAUD' => 'badge-danger',
                                    'ESKALASI LANGSUNG' => 'badge-danger',
                                    default => 'badge-gray',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge {{ $badgeCls }}" style="font-size: 0.8rem; padding: 0.32rem 0.65rem;">
                                        {{ $kep }}
                                    </span>
                                </td>
                                <td style="text-align: center; font-weight: 700; font-size: 0.95rem; color: #1e293b;">
                                    {{ number_format($jml) }}
                                </td>
                                <td style="text-align: right;">
                                    @if($jml > 0)
                                        <a href="{{ route('risk-trigger.register', ['keputusan_final' => $kep]) }}" class="btn btn-outline btn-sm" style="padding: 0.28rem 0.65rem; font-size: 0.8rem; border-radius: 6px;">
                                            Filter <i class="bi bi-chevron-right ms-1"></i>
                                        </a>
                                    @else
                                        <span class="text-muted" style="font-size: 0.82rem;">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Card Kanan: Matrix 13 Area Risiko Standar SOP 06 -->
    <div class="col-lg-7">
        <div class="table-container h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-grid-3x3-gap-fill text-primary fs-5"></i>
                    <h6 class="mb-0 fw-bold" style="color: var(--bs-blue-dark); font-size: 0.95rem;">
                        Matrix 13 Area Risiko (SOP 06 Master Parameter)
                    </h6>
                </div>
                <span class="badge badge-gray">13 Area Standar</span>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Area Risiko</th>
                            <th>KKA Tujuan</th>
                            <th style="width: 14%; text-align: center;">Trigger</th>
                            <th style="width: 14%; text-align: center;">Visit</th>
                            <th style="width: 20%; text-align: center;">Status Alarm</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($matrixArea as $ma)
                            <tr style="{{ $ma['is_alarm'] ? 'background-color: #fffdf5;' : '' }}">
                                <td>
                                    <div class="fw-bold" style="font-size: 0.88rem; color: #1e293b;">{{ $ma['area'] }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-info" style="font-size: 0.76rem;">
                                        <i class="bi bi-file-earmark-spreadsheet me-1"></i>{{ $ma['kka_tujuan'] }}
                                    </span>
                                </td>
                                <td style="text-align: center; font-weight: 700; font-size: 0.92rem; color: #1e293b;">
                                    {{ number_format($ma['total_trigger']) }}
                                </td>
                                <td style="text-align: center; font-weight: 700; font-size: 0.92rem; color: #dc2626;">
                                    {{ number_format($ma['total_visit']) }}
                                </td>
                                <td style="text-align: center;">
                                    @if($ma['is_alarm'])
                                        <span class="badge badge-danger" style="font-size: 0.76rem; padding: 0.28rem 0.65rem;">
                                            <i class="bi bi-bell-fill me-1"></i>ALARM &gt; 5
                                        </span>
                                    @else
                                        <span class="badge badge-gray" style="font-size: 0.76rem; padding: 0.28rem 0.65rem;">
                                            Normal (&le;5)
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



@push('scripts')
<script>
    // Form sync loading
    document.getElementById('formSyncTrigger')?.addEventListener('submit', function() {
        const btn = document.getElementById('btnSyncTrigger');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menarik Data...';
        }
    });
</script>
@endpush
@endsection
