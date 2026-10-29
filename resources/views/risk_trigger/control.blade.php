@extends('layouts.app')
@section('title', 'Ringkasan Control (WP-RA-06-A) - Risk Trigger Visit (SOP 06)')

@section('content')
<!-- Header Harmonized SOP 06 -->
<div class="rtv-header">
    <div>
        <div class="rtv-breadcrumb">
            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a> &rsaquo;
            <span>Risk Trigger Visit (SOP 06)</span> &rsaquo;
            <span class="text-dark fw-semibold">Ringkasan Control</span>
        </div>
        <h4 class="rtv-title">
            <i class="bi bi-speedometer2 text-primary"></i> Ringkasan Control
            <span class="badge-wp">WP-RA-06-A</span>
            @if($isRa)
                <span class="badge-role-scope"><i class="bi bi-geo-alt-fill text-success me-1"></i> Cabang: {{ $userCabangName }}</span>
            @else
                <span class="badge-role-scope"><i class="bi bi-shield-check text-primary me-1"></i> Mode Reviewer / Admin (Global)</span>
            @endif
        </h4>
        <p class="rtv-subtitle">
            Monitoring pemicu risiko, agregasi temuan lintas modul, penilaian ulang (Likelihood &amp; Impact), dan pengendalian visit terarah.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- 1-Click Sync Button -->
        <form action="{{ route('risk-trigger.sync') }}" method="POST" class="m-0" id="formSyncTrigger">
            @csrf
            @if(!empty($filterKodeUnit))
                <input type="hidden" name="kode_unit" value="{{ $filterKodeUnit }}">
            @endif
            <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" id="btnSyncTrigger">
                <i class="bi bi-arrow-repeat me-1" id="iconSync"></i>
                <span>{{ $isRa ? 'Sinkronkan Data Cabang' : ($filterKodeUnit ? 'Sinkronkan Cabang Terpilih' : 'Tarik Sinkronisasi') }}</span>
            </button>
        </form>

        <!-- Input Manual Button - Khusus Role Admin -->
        @if(!$isRa)
        <a href="{{ route('risk-trigger.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs">
            <i class="bi bi-plus-lg me-1"></i>
            <span>Input Trigger Manual</span>
        </a>
        @endif
    </div>
</div>

<!-- Filter Bar Cabang / Isolasi Cabang -->
@if(!$isRa)
<div class="rtv-filter-card">
    <form action="{{ route('risk-trigger.control') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-auto d-flex align-items-center gap-2">
            <i class="bi bi-funnel-fill text-primary"></i>
            <span class="fw-bold" style="font-size: 0.85rem; color: #334155;">Filter Pemantauan Cabang:</span>
        </div>
        <div class="col-md-5">
            <select name="kode_unit" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Semua Cabang (Agregasi Global Seluruh Bank Sulteng) --</option>
                @foreach($units as $u)
                    <option value="{{ $u->unit_code }}" {{ ($filterKodeUnit == $u->unit_code) ? 'selected' : '' }}>
                        {{ $u->unit_code }} - {{ $u->unit_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-auto d-flex align-items-center gap-2">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-filter me-1"></i> Terapkan
            </button>
            @if(!empty($filterKodeUnit))
                <a href="{{ route('risk-trigger.control') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-x-circle me-1"></i> Reset Global
                </a>
            @endif
        </div>
        <div class="col-md-auto ms-auto text-end">
            <span class="badge-role-scope">
                <i class="bi bi-buildings me-1 text-primary"></i> Cakupan: <strong class="text-dark">{{ $filterKodeUnit ? 'Cabang ' . $filterKodeUnit : 'Global (Seluruh Cabang)' }}</strong>
            </span>
        </div>
    </form>
</div>
@else
<div class="rtv-filter-card py-2.5 px-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #f8fafc;">
    <div class="d-flex align-items-center gap-2">
        <span class="badge-wp">
            <i class="bi bi-shield-lock-fill me-1"></i> Akses RA Cabang
        </span>
        <span style="font-size: 0.86rem; color: #334155;">
            Data indikator, alarm akumulasi, dan matriks risiko di bawah ini terisolasi khusus untuk: <strong class="text-primary">{{ $userCabangName }}</strong>
        </span>
    </div>
    <span class="text-muted small">
        <i class="bi bi-info-circle me-1"></i> Mode Isolasi Cabang Aktif
    </span>
</div>
@endif

<!-- NOTIFIKASI ALARM RINGKAS & TERINTEGRASI NAVBAR -->
@if(!empty($alarms))
    <div class="alert alert-danger d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3 mb-3 border-danger-subtle rounded-3 shadow-xs" style="background: #fff5f5; border: 1px solid #fecaca;">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-danger text-white px-2 py-1" style="font-size: 0.72rem; font-weight: 700;">
                <i class="bi bi-bell-fill me-1"></i> {{ count($alarms) }} ALARM AKTIF
            </span>
            <span style="font-size: 0.84rem; color: #991b1b;">
                Terdeteksi akumulasi <strong>&gt; 5 temuan</strong> pada {{ count($alarms) }} Area Risiko. Peringatan lengkap telah disematkan pada <strong>Lonceng Notifikasi Navbar</strong>.
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('risk-trigger.register', ['kode_unit' => $alarms[0]['kode_unit'] ?? '', 'area' => $alarms[0]['area'] ?? '']) }}" class="btn btn-danger btn-sm py-1 px-3 rounded-pill" style="font-size: 0.78rem; font-weight: 600;">
                <i class="bi bi-funnel me-1"></i> Filter Area di Register
            </a>
            <button type="button" class="btn btn-outline-danger btn-sm py-1 px-3 rounded-pill" onclick="toggleAlarmCollapse()" id="btnToggleAlarm" style="font-size: 0.78rem;">
                <i class="bi bi-chevron-down" id="iconToggleAlarm"></i> Rincian
            </button>
        </div>
    </div>
    <div id="collapseAlarmsArea" style="display: none;" class="mb-3">
        <div class="card border-danger-subtle shadow-sm p-3 rounded-3" style="background: #fffafa; border: 1px solid #fee2e2;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Rincian Area Risiko Terpicu (&gt; 5 Temuan Akumulasi):</span>
                <span class="text-muted small" style="font-size: 0.75rem;">(Bisa ditutup kapan saja agar layar tetap lega)</span>
            </div>
            <div class="row g-2">
                @foreach($alarms as $alarm)
                    <div class="col-md-4">
                        <div class="p-2.5 rounded border border-danger-subtle bg-white h-100 d-flex flex-column justify-content-between shadow-2xs">
                            <div>
                                <div class="fw-bold text-danger" style="font-size: 0.85rem;">{{ $alarm['nama_unit'] }} ({{ $alarm['kode_unit'] }})</div>
                                <div class="text-dark fw-bold mt-1" style="font-size: 0.82rem;">Area: {{ $alarm['area'] }} <span class="badge bg-danger text-white ms-1" style="font-size: 0.68rem;">{{ $alarm['count'] }} Temuan</span></div>
                                <div class="text-muted mt-1" style="font-size: 0.74rem;">Daily: {{ $alarm['dr_count'] }} &bull; Offsite: {{ $alarm['off_count'] }} &bull; Onsite: {{ $alarm['on_count'] }}</div>
                            </div>
                            <div class="mt-2 pt-2 border-top text-end">
                                <a href="{{ route('risk-trigger.register', ['kode_unit' => $alarm['kode_unit'], 'area' => $alarm['area']]) }}" class="btn btn-sm btn-outline-danger py-0.5 px-2 rounded-pill" style="font-size: 0.74rem;">
                                    Buka Register &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<!-- 12 KPI STAT CARDS (UNIFIED DESIGN) -->
<div class="row g-3 mb-4">
    <!-- 1. Total Trigger -->
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-primary">
            <div class="rtv-kpi-title">
                <span>1. Total Trigger</span>
                <i class="bi bi-diagram-3 text-primary fs-6"></i>
            </div>
            <div class="rtv-kpi-val">{{ number_format($kpi['total_trigger']) }}</div>
            <div class="rtv-kpi-sub">Seluruh temuan masuk</div>
        </div>
    </div>

    <!-- 2. Data Valid -->
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-success">
            <div class="rtv-kpi-title">
                <span>2. Data Valid</span>
                <i class="bi bi-check-circle text-success fs-6"></i>
            </div>
            <div class="rtv-kpi-val text-success">{{ number_format($kpi['data_valid']) }}</div>
            <div class="rtv-kpi-sub">7 parameter lengkap</div>
        </div>
    </div>

    <!-- 3. Risk Kritis -->
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-danger">
            <div class="rtv-kpi-title">
                <span>3. Risk Kritis</span>
                <i class="bi bi-exclamation-triangle text-danger fs-6"></i>
            </div>
            <div class="rtv-kpi-val text-danger">{{ number_format($kpi['risk_kritis']) }}</div>
            <div class="rtv-kpi-sub">Score Reassessment &ge; 16</div>
        </div>
    </div>

    <!-- 4. Risk Tinggi -->
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-warning">
            <div class="rtv-kpi-title">
                <span>4. Risk Tinggi</span>
                <i class="bi bi-shield-exclamation text-warning fs-6"></i>
            </div>
            <div class="rtv-kpi-val" style="color: #c2410c;">{{ number_format($kpi['risk_tinggi']) }}</div>
            <div class="rtv-kpi-sub">Score Reassessment 10-15</div>
        </div>
    </div>

    <!-- 5. Keputusan Visit -->
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-primary">
            <div class="rtv-kpi-title">
                <span>5. Keputusan Visit</span>
                <i class="bi bi-lightning-charge text-primary fs-6"></i>
            </div>
            <div class="rtv-kpi-val" style="color: #1d4ed8;">{{ number_format($kpi['keputusan_visit']) }}</div>
            <div class="rtv-kpi-sub">Perlu uji lapangan</div>
        </div>
    </div>

    <!-- 6. Menunggu Assignment -->
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-slate">
            <div class="rtv-kpi-title">
                <span>6. Menunggu Assignment</span>
                <i class="bi bi-hourglass-split text-secondary fs-6"></i>
            </div>
            <div class="rtv-kpi-val">{{ number_format($kpi['menunggu_assignment']) }}</div>
            <div class="rtv-kpi-sub">Antrean penetapan RA</div>
        </div>
    </div>

    <!-- 7. Assignment Siap -->
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-success">
            <div class="rtv-kpi-title">
                <span>7. Assignment Siap</span>
                <i class="bi bi-calendar-check text-success fs-6"></i>
            </div>
            <div class="rtv-kpi-val text-success">{{ number_format($kpi['assignment_siap']) }}</div>
            <div class="rtv-kpi-sub">Scope siap dilaksanakan</div>
        </div>
    </div>

    <!-- 8. Visit Dalam Proses -->
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-primary">
            <div class="rtv-kpi-title">
                <span>8. Visit Dalam Proses</span>
                <i class="bi bi-arrow-repeat text-primary fs-6"></i>
            </div>
            <div class="rtv-kpi-val">{{ number_format($kpi['visit_dalam_proses']) }}</div>
            <div class="rtv-kpi-sub">Pemeriksaan lapangan</div>
        </div>
    </div>

    <!-- 9. Simpulan Tersedia -->
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-success">
            <div class="rtv-kpi-title">
                <span>9. Simpulan Tersedia</span>
                <i class="bi bi-file-earmark-check text-success fs-6"></i>
            </div>
            <div class="rtv-kpi-val text-success">{{ number_format($kpi['simpulan_tersedia']) }}</div>
            <div class="rtv-kpi-sub">KKA visit diselesaikan</div>
        </div>
    </div>

    <!-- 10. Masih Open -->
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-slate">
            <div class="rtv-kpi-title">
                <span>10. Masih Open</span>
                <i class="bi bi-folder2-open text-secondary fs-6"></i>
            </div>
            <div class="rtv-kpi-val" style="color: #475569;">{{ number_format($kpi['masih_open']) }}</div>
            <div class="rtv-kpi-sub">Belum final closure</div>
        </div>
    </div>

    <!-- 11. Overdue SLA -->
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-danger">
            <div class="rtv-kpi-title">
                <span>11. Overdue SLA</span>
                <i class="bi bi-clock-history text-danger fs-6"></i>
            </div>
            <div class="rtv-kpi-val text-danger">{{ number_format($kpi['overdue_sla']) }}</div>
            <div class="rtv-kpi-sub">Melewati batas hari kerja</div>
        </div>
    </div>

    <!-- 12. Indikasi Fraud -->
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-danger">
            <div class="rtv-kpi-title">
                <span>12. Indikasi Fraud</span>
                <i class="bi bi-shield-slash text-danger fs-6"></i>
            </div>
            <div class="rtv-kpi-val text-danger">{{ number_format($kpi['indikasi_fraud']) }}</div>
            <div class="rtv-kpi-sub">Rujukan Pemeriksaan Fraud</div>
        </div>
    </div>
</div>

<!-- 2 MAIN TABLES HARMONIZED -->
<div class="row g-3 mb-4">
    <!-- Card Kiri: Ringkasan Keputusan Final -->
    <div class="col-lg-5">
        <div class="rtv-table-container h-100">
            <div class="rtv-table-header">
                <h6 class="rtv-table-title">
                    <i class="bi bi-diagram-2 text-primary"></i> Ringkasan Keputusan Final
                </h6>
                <span class="badge-role-scope">8 Kategori</span>
            </div>
            <div class="table-responsive">
                <table class="table rtv-table mb-0">
                    <thead>
                        <tr>
                            <th>Keputusan Final</th>
                            <th style="width: 25%; text-align: center;">Jumlah</th>
                            <th style="width: 25%; text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($keputusanSummary as $kep => $jml)
                            @php
                                $badgeCls = match($kep) {
                                    'RISK TRIGGER VISIT' => 'badge-decision-visit',
                                    'KLARIFIKASI OFFSITE' => 'badge-decision-offsite',
                                    'MONITORING' => 'badge-decision-monitoring',
                                    'SOP 05 - SURPRISE AUDIT' => 'badge-decision-surprise',
                                    'SOP 07 - MONITORING TL' => 'badge-decision-tl',
                                    'SOP 09 - PEMERIKSAAN AWAL FRAUD' => 'badge-decision-fraud',
                                    'ESKALASI LANGSUNG' => 'badge-decision-fraud',
                                    default => 'badge-decision-monitoring',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge-decision {{ $badgeCls }}">
                                        {{ $kep }}
                                    </span>
                                </td>
                                <td style="text-align: center; font-weight: 700; font-size: 0.95rem; color: #1e293b;">
                                    {{ number_format($jml) }}
                                </td>
                                <td style="text-align: right;">
                                    @if($jml > 0)
                                        <a href="{{ route('risk-trigger.register', ['keputusan_final' => $kep]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 py-0.5" style="font-size: 0.76rem;">
                                            Filter <i class="bi bi-chevron-right ms-0.5"></i>
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
        <div class="rtv-table-container h-100">
            <div class="rtv-table-header">
                <h6 class="rtv-table-title">
                    <i class="bi bi-grid-3x3-gap-fill text-primary"></i> Matrix 13 Area Risiko (Master Parameter)
                </h6>
                <span class="badge-role-scope">13 Area Standar</span>
            </div>
            <div class="table-responsive">
                <table class="table rtv-table mb-0">
                    <thead>
                        <tr>
                            <th>Area Risiko</th>
                            <th>KKA Tujuan</th>
                            <th style="width: 14%; text-align: center;">Trigger</th>
                            <th style="width: 14%; text-align: center;">Visit</th>
                            <th style="width: 22%; text-align: center;">Status Alarm</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($matrixArea as $ma)
                            <tr>
                                <td>
                                    <div class="fw-bold" style="font-size: 0.86rem; color: #1e293b;">{{ $ma['area'] }}</div>
                                </td>
                                <td>
                                    <span class="badge-code">
                                        <i class="bi bi-file-earmark-spreadsheet me-1"></i>{{ $ma['kka_tujuan'] }}
                                    </span>
                                </td>
                                <td style="text-align: center; font-weight: 700; font-size: 0.9rem; color: #1e293b;">
                                    {{ number_format($ma['total_trigger']) }}
                                </td>
                                <td style="text-align: center; font-weight: 700; font-size: 0.9rem; color: #1d4ed8;">
                                    {{ number_format($ma['total_visit']) }}
                                </td>
                                <td style="text-align: center;">
                                    @if($ma['is_alarm'])
                                        <span class="badge-status badge-status-danger">
                                            <i class="bi bi-bell-fill me-1"></i>ALARM &gt; 5
                                        </span>
                                    @else
                                        <span class="badge-status badge-status-neutral">
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
    function toggleAlarmCollapse() {
        const area = document.getElementById('collapseAlarmsArea');
        const icon = document.getElementById('iconToggleAlarm');
        const btn = document.getElementById('btnToggleAlarm');
        if (area.style.display === 'none' || area.style.display === '') {
            area.style.display = 'block';
            icon.className = 'bi bi-chevron-up';
            btn.innerHTML = '<i class="bi bi-chevron-up" id="iconToggleAlarm"></i> Tutup';
        } else {
            area.style.display = 'none';
            icon.className = 'bi bi-chevron-down';
            btn.innerHTML = '<i class="bi bi-chevron-down" id="iconToggleAlarm"></i> Rincian';
        }
    }

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
