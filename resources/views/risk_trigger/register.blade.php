@extends('layouts.app')
@section('title', 'Register Trigger (WP-RA-06-B) - Risk Trigger Visit (SOP 06)')

@section('content')
<!-- Header Harmonized SOP 06 -->
<div class="rtv-header">
    <div>
        <div class="rtv-breadcrumb">
            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a> &rsaquo;
            <span>Risk Trigger Visit (SOP 06)</span> &rsaquo;
            <span class="text-dark fw-semibold">Register Trigger</span>
        </div>
        <h4 class="rtv-title">
            <i class="bi bi-table text-primary"></i> Register Trigger
            <span class="badge-wp">WP-RA-06-B</span>
            @if($isRa)
                <span class="badge-role-scope"><i class="bi bi-geo-alt-fill text-success me-1"></i> Cabang: {{ $userCabangName }}</span>
            @else
                <span class="badge-role-scope"><i class="bi bi-shield-check text-primary me-1"></i> Mode Reviewer / Admin (Global)</span>
            @endif
        </h4>
        <p class="rtv-subtitle">
            Daftar lengkap 37 kolom pemicu audit, hasil agregasi INBOX_TRIGGER, reassessment risiko otomatis, dan routing keputusan visit.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- 1-Click Sync Button -->
        <form action="{{ route('risk-trigger.sync') }}" method="POST" class="m-0" id="formSyncRegister">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" id="btnSyncRegister">
                <i class="bi bi-arrow-repeat me-1" id="iconSyncRegister"></i>
                <span>{{ $isRa ? 'Sinkronkan Data Cabang' : 'Tarik Sinkronisasi' }}</span>
            </button>
        </form>

        <!-- Input Manual Trigger Button - Khusus Role Admin -->
        @if(!$isRa)
        <a href="{{ route('risk-trigger.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs">
            <i class="bi bi-plus-lg me-1"></i>
            <span>Input Trigger Manual</span>
        </a>
        @endif
    </div>
</div>

<!-- 4 TOP KPI SUMMARY CARDS (HARMONIZED DENGAN MENU LAIN) -->
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-primary">
            <div class="rtv-kpi-title">
                <span>Total Trigger Masuk</span>
                <i class="bi bi-diagram-3 text-primary fs-6"></i>
            </div>
            <div class="rtv-kpi-val">{{ number_format($stats['total'] ?? $triggers->total()) }}</div>
            <div class="rtv-kpi-sub">Agregasi lintas modul kerja</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-primary">
            <div class="rtv-kpi-title">
                <span>Keputusan Visit</span>
                <i class="bi bi-lightning-charge text-primary fs-6"></i>
            </div>
            <div class="rtv-kpi-val" style="color: #1d4ed8;">{{ number_format($stats['visit'] ?? 0) }}</div>
            <div class="rtv-kpi-sub">Pemicu perlu uji lapangan</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-danger">
            <div class="rtv-kpi-title">
                <span>Risk Kritis</span>
                <i class="bi bi-exclamation-triangle text-danger fs-6"></i>
            </div>
            <div class="rtv-kpi-val text-danger">{{ number_format($stats['kritis'] ?? 0) }}</div>
            <div class="rtv-kpi-sub">Reassessment skor &ge; 16</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-danger">
            <div class="rtv-kpi-title">
                <span>Overdue SLA</span>
                <i class="bi bi-clock-history text-danger fs-6"></i>
            </div>
            <div class="rtv-kpi-val text-danger">{{ number_format($stats['overdue'] ?? 0) }}</div>
            <div class="rtv-kpi-sub">Melewati batas hari kerja</div>
        </div>
    </div>
</div>

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
            @if(request('area'))
                <a href="{{ route('risk-trigger.register') }}" class="btn btn-outline-secondary btn-sm py-1 px-3 rounded-pill" style="font-size: 0.78rem;">
                    <i class="bi bi-x-circle me-1"></i> Reset Filter Area
                </a>
            @endif
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
                                <a href="{{ route('risk-trigger.register', ['kode_unit' => $alarm['kode_unit'], 'area' => $alarm['area']]) }}" class="btn btn-sm btn-outline-danger py-0.5 px-2.5 rounded-pill" style="font-size: 0.74rem;">
                                    Fokus Area Ini &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<!-- FILTER CARD (HARMONIZED) -->
<div class="rtv-filter-card">
    <form method="GET" action="{{ route('risk-trigger.register') }}" class="row g-2 align-items-end">
        <!-- Filter Unit -->
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

        <!-- Filter Sumber -->
        <div class="col-md-2">
            <label class="form-label">
                <i class="bi bi-diagram-2 me-1 text-primary"></i> Sumber Pemicu
            </label>
            <select name="sumber" class="form-select">
                <option value="">-- Semua Sumber --</option>
                @foreach($standarSumber as $src)
                    <option value="{{ $src }}" {{ request('sumber') == $src ? 'selected' : '' }}>{{ $src }}</option>
                @endforeach
            </select>
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
        <div class="col-md-3">
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
        <div class="col-md-10">
            <label class="form-label">
                <i class="bi bi-search me-1 text-primary"></i> Cari Data
            </label>
            <input type="text" name="q" class="form-control" placeholder="Trigger ID / Objek Key / Uraian Temuan / Bukti Awal..." value="{{ request('q') }}">
        </div>

        <!-- Action Buttons -->
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill flex-fill" title="Terapkan Filter">
                <i class="bi bi-funnel-fill me-1"></i> Filter
            </button>
            <a href="{{ route('risk-trigger.register') }}" class="btn btn-outline-secondary btn-sm rounded-pill flex-fill text-center" title="Reset Filter">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
            </a>
        </div>
    </form>
</div>

<!-- MAIN DATA TABLE CONTAINER -->
<div class="rtv-table-container">
    <div class="rtv-table-header">
        <h6 class="rtv-table-title">
            <i class="bi bi-journal-check text-primary"></i> Data Register Trigger (37 Kolom SOP 06)
        </h6>
        <div class="d-flex align-items-center gap-2">
            <span class="badge-role-scope">
                Total: <strong>{{ number_format($triggers->total()) }}</strong> Data
            </span>
            <span class="text-muted small">
                (Menampilkan {{ $triggers->firstItem() ?? 0 }} - {{ $triggers->lastItem() ?? 0 }})
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table rtv-table mb-0">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
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
                        // Harmonized Banking Risk Badges (Sedang = Amber, Kritis = Crimson)
                        $riskCls = match($t->risk_level_reassessment) {
                            'KRITIS' => 'badge-risk-kritis',
                            'TINGGI' => 'badge-risk-tinggi',
                            'SEDANG' => 'badge-risk-sedang',
                            default => 'badge-risk-rendah',
                        };

                        // Harmonized Decision Badges (Visit = Royal Navy, NOT Danger Red!)
                        $kepCls = match($t->keputusan_final) {
                            'RISK TRIGGER VISIT' => 'badge-decision-visit',
                            'KLARIFIKASI OFFSITE' => 'badge-decision-offsite',
                            'MONITORING' => 'badge-decision-monitoring',
                            'SOP 05 - SURPRISE AUDIT' => 'badge-decision-surprise',
                            'SOP 07 - MONITORING TL' => 'badge-decision-tl',
                            'SOP 09 - PEMERIKSAAN AWAL FRAUD' => 'badge-decision-fraud',
                            default => 'badge-decision-monitoring',
                        };

                        $isOverdue = $t->sla_deadline && $t->sla_deadline->lt(now()->startOfDay()) && $t->status_workflow !== 'SELESAI/DALAM TL';
                        $isDuplikat = $t->status_duplikat === 'DUPLIKAT - REVIEW';
                    @endphp
                    <tr>
                        <td style="text-align: center; font-weight: 600; color: #64748b; font-size: 0.84rem;">
                            {{ $triggers->firstItem() + $idx }}
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #1e3a8a; font-size: 0.88rem; font-family: monospace;">
                                {{ $t->trigger_id }}
                            </div>
                            <div class="d-flex gap-1 mt-1">
                                @if($isDuplikat)
                                    <span class="badge-status badge-status-warning" style="font-size: 0.68rem;">DUPLIKAT</span>
                                @else
                                    <span class="badge-status badge-status-neutral" style="font-size: 0.68rem;">UNIK</span>
                                @endif
                                @if($t->kelengkapan_data === 'VALID')
                                    <span class="badge-status badge-status-success" style="font-size: 0.68rem;">VALID</span>
                                @else
                                    <span class="badge-status badge-status-danger" style="font-size: 0.68rem;">LENGKAPI</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $t->sumber }}</div>
                            <div class="text-muted" style="font-size: 0.78rem; font-family: monospace;">{{ $t->source_wp_id ?: '-' }}</div>
                            <div class="text-muted mt-0.5" style="font-size: 0.78rem;">
                                <i class="bi bi-calendar3 me-1"></i>{{ $t->tanggal_trigger ? $t->tanggal_trigger->format('d/m/Y') : '-' }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $t->kode_unit }}</div>
                            <div class="text-muted small text-truncate" style="max-width: 140px;" title="{{ $t->nama_unit }}">
                                {{ $t->nama_unit }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 0.86rem;">{{ $t->area }}</div>
                            <span class="badge-code mt-1 d-inline-block">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i>{{ $t->kka_tujuan }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold text-primary mb-1" style="font-size: 0.86rem;">
                                {{ $t->objek_key }}
                            </div>
                            <div class="text-secondary small" style="line-height: 1.45; max-width: 280px;" title="{{ $t->uraian_trigger }}">
                                {{ Str::limit($t->uraian_trigger, 85) }}
                            </div>
                            <div class="d-flex flex-wrap gap-1 mt-1.5">
                                @if(strtoupper((string)$t->indikasi_fraud) === 'YA')
                                    <span class="badge-status badge-status-danger" style="font-size: 0.7rem;">
                                        <i class="bi bi-shield-slash me-1"></i>FRAUD
                                    </span>
                                @endif
                                @if(strtoupper((string)$t->objek_surprise_fisik) === 'YA')
                                    <span class="badge-status badge-status-purple" style="font-size: 0.7rem;">
                                        <i class="bi bi-box-seam me-1"></i>FISIK
                                    </span>
                                @endif
                                @if($t->eksposur_nominal > 0)
                                    <span class="badge-status badge-status-neutral" style="font-size: 0.7rem;">
                                        Rp {{ number_format($t->eksposur_nominal, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge-risk {{ $riskCls }}">
                                {{ $t->risk_level_reassessment }} ({{ $t->risk_score_reassessment }})
                            </span>
                            <div class="text-muted mt-1 small">
                                Likelihood: <strong>{{ $t->likelihood }}</strong> &times; Impact: <strong>{{ $t->impact }}</strong>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge-decision {{ $kepCls }}">
                                {{ $t->keputusan_final }}
                            </span>
                            @if($t->override_keputusan)
                                <div class="mt-1 small" style="color: #b45309; font-weight: 600;">
                                    <i class="bi bi-pencil-fill me-1"></i>Override RA
                                </div>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($t->sla_deadline)
                                <div class="fw-semibold {{ $isOverdue ? 'text-danger' : 'text-dark' }}" style="font-size: 0.84rem;">
                                    {{ $t->sla_deadline->format('d/m/Y') }}
                                </div>
                                @if($isOverdue)
                                    <span class="badge-status badge-status-danger mt-1" style="font-size: 0.68rem; font-weight: 700;">OVERDUE</span>
                                @endif
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <span class="badge-status badge-status-neutral">
                                {{ $t->status_workflow }}
                            </span>
                            <div class="text-muted mt-1 small fw-semibold">
                                {{ $t->prioritas_visit }}
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('risk-trigger.show', $t->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 py-1" style="font-size: 0.78rem; font-weight: 600;" title="Lihat Detail 37 Kolom">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>

                                @if(!$isRa)
                                    @if($t->keputusan_final === 'RISK TRIGGER VISIT')
                                        <a href="{{ route('risk-trigger.assignment.create', ['trigger_id' => $t->id]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-1" style="font-size: 0.78rem;" title="Terbitkan Assignment Visit">
                                            <i class="bi bi-clipboard-plus"></i>
                                        </a>
                                    @endif

                                    <a href="{{ route('risk-trigger.edit-override', $t->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2 py-1" style="font-size: 0.78rem;" title="Override Keputusan">
                                        <i class="bi bi-sliders"></i>
                                    </a>

                                    @if(str_contains($t->source_wp_id ?? '', 'MANUAL-'))
                                        <form action="{{ route('risk-trigger.destroy', $t->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Hapus trigger manual ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2 py-1" title="Hapus Trigger" style="font-size: 0.78rem;">
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
                            <div class="py-5 text-center">
                                <i class="bi bi-inbox text-muted fs-1 mb-2 d-block"></i>
                                <h6 class="fw-bold mb-1" style="color: #1e293b;">Belum Ada Data Register Trigger</h6>
                                <p class="text-muted mb-3 small">
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
            <div class="text-muted small">
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
