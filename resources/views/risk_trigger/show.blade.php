@extends('layouts.app')
@section('title', 'Detail Trigger: ' . $trigger->trigger_id . ' - SOP 06')

@section('content')
<style>
    .stat-card-summary {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .detail-section-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .detail-section-header {
        background: #f8fafc;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .detail-section-title {
        font-size: 0.98rem;
        font-weight: 700;
        color: var(--bs-blue-dark);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .detail-section-body {
        padding: 1.25rem;
    }
    .param-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.85rem 1rem;
        height: 100%;
        transition: all 0.2s ease;
    }
    .param-box:hover {
        background: #f1f5f9;
    }
    .param-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.35rem;
    }
    .param-value {
        font-size: 0.92rem;
        font-weight: 600;
        color: #1e293b;
    }
</style>

@php
    $riskCls = match($trigger->risk_level_reassessment) {
        'KRITIS' => 'badge-risk-kritis',
        'TINGGI' => 'badge-risk-tinggi',
        'SEDANG' => 'badge-risk-sedang',
        default => 'badge-risk-rendah',
    };

    $kepCls = match($trigger->keputusan_final) {
        'RISK TRIGGER VISIT' => 'badge-decision-visit',
        'KLARIFIKASI OFFSITE' => 'badge-decision-offsite',
        'MONITORING' => 'badge-decision-monitoring',
        'SOP 05 - SURPRISE AUDIT' => 'badge-decision-surprise',
        'SOP 07 - MONITORING TL' => 'badge-decision-tl',
        'SOP 09 - PEMERIKSAAN AWAL FRAUD' => 'badge-decision-fraud',
        default => 'badge-decision-monitoring',
    };

    $isOverdue = $trigger->sla_deadline && $trigger->sla_deadline->lt(now()->startOfDay()) && $trigger->status_workflow !== 'SELESAI/DALAM TL';
@endphp

<!-- Header Harmonized SOP 06 -->
<div class="rtv-header">
    <div>
        <div class="rtv-breadcrumb">
            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a> &rsaquo;
            <a href="{{ route('risk-trigger.register') }}">Register Trigger</a> &rsaquo;
            <span class="text-dark fw-semibold">Detail: {{ $trigger->trigger_id }}</span>
        </div>
        <h4 class="rtv-title">
            <i class="bi bi-file-earmark-text text-primary"></i> Detail Register Trigger
            <span class="badge-wp">{{ $trigger->trigger_id }}</span>
        </h4>
        <p class="rtv-subtitle">
            {{ $trigger->nama_unit }} ({{ $trigger->kode_unit }}) &bull; Area Risiko: <strong>{{ $trigger->area }}</strong> &bull; Sumber: <strong>{{ $trigger->sumber }}</strong>
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('risk-trigger.register') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Register
        </a>
        @if(!$isRa)
            @if($trigger->keputusan_final === 'RISK TRIGGER VISIT')
                @if($trigger->assignmentScope)
                    <a href="{{ route('risk-trigger.assignment', ['search' => $trigger->assignmentScope->visit_id]) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="bi bi-clipboard-check me-1"></i> Penugasan: {{ $trigger->assignmentScope->visit_id }}
                    </a>
                @else
                    <a href="{{ route('risk-trigger.assignment.create', ['trigger_id' => $trigger->id]) }}" class="btn btn-success btn-sm rounded-pill px-3">
                        <i class="bi bi-plus-circle me-1"></i> + Terbitkan Assignment Visit
                    </a>
                @endif
            @endif
            <a href="{{ route('risk-trigger.edit-override', $trigger->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-sliders me-1"></i> Override Keputusan
            </a>
            @if(str_contains($trigger->source_wp_id ?? '', 'MANUAL-'))
                <form action="{{ route('risk-trigger.destroy', $trigger->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus trigger manual ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </button>
                </form>
            @endif
        @endif
    </div>
</div>

<!-- 4 Top KPI Stat Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card-summary" style="border-left: 4px solid var(--bs-blue) !important;">
            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.76rem; letter-spacing: 0.03em;">Reassessment Risiko</div>
            <div class="d-flex align-items-center gap-2 mt-1">
                <span class="badge {{ $riskCls }}" style="font-size: 0.88rem; font-weight: 700; padding: 0.35rem 0.75rem;">
                    {{ $trigger->risk_level_reassessment }} ({{ $trigger->risk_score_reassessment }})
                </span>
            </div>
            <div class="text-muted mt-2" style="font-size: 0.82rem;">
                Likelihood: <strong>{{ $trigger->likelihood }}</strong> &times; Impact: <strong>{{ $trigger->impact }}</strong>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card-summary" style="border-left: 4px solid #ef4444 !important;">
            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.76rem; letter-spacing: 0.03em;">Keputusan Final</div>
            <div class="mt-1">
                <span class="badge {{ $kepCls }}" style="font-size: 0.88rem; font-weight: 700; padding: 0.35rem 0.75rem;">
                    {{ $trigger->keputusan_final }}
                </span>
            </div>
            <div class="mt-2" style="font-size: 0.82rem;">
                @if($trigger->override_keputusan)
                    <span class="text-warning fw-bold"><i class="bi bi-pencil-fill me-1"></i>Override RA Aktif</span>
                @else
                    <span class="text-muted">Kalkulasi Otomatis Sistem</span>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card-summary" style="border-left: 4px solid {{ $isOverdue ? '#dc2626' : '#10b981' }} !important;">
            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.76rem; letter-spacing: 0.03em;">Target SLA &amp; Deadline</div>
            <div class="fw-bold mt-1 {{ $isOverdue ? 'text-danger' : 'text-dark' }}" style="font-size: 1.15rem;">
                {{ $trigger->sla_deadline ? $trigger->sla_deadline->format('d F Y') : '-' }}
            </div>
            <div class="mt-2" style="font-size: 0.82rem;">
                @if($isOverdue)
                    <span class="badge badge-danger">OVERDUE SLA</span>
                @else
                    <span class="text-success fw-bold"><i class="bi bi-clock-check me-1"></i>Dalam Batas Hari Kerja</span>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card-summary" style="border-left: 4px solid #8b5cf6 !important;">
            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.76rem; letter-spacing: 0.03em;">Status Workflow</div>
            <div class="mt-1">
                <span class="badge badge-gray" style="font-size: 0.88rem; font-weight: 700; padding: 0.35rem 0.75rem;">
                    {{ $trigger->status_workflow }}
                </span>
            </div>
            <div class="text-muted mt-2" style="font-size: 0.82rem;">
                Prioritas: <strong>{{ $trigger->prioritas_visit }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 1: Identitas & Sumber Data (Kolom 1 - 7, 20) -->
<div class="detail-section-card">
    <div class="detail-section-header">
        <h5 class="detail-section-title">
            <i class="bi bi-info-circle text-primary"></i> 1. Identitas &amp; Sumber Data
        </h5>
        <span class="badge badge-gray" style="font-size: 0.78rem;">Kolom 1 - 7, 20</span>
    </div>
    <div class="detail-section-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="param-box">
                    <div class="param-label">1. Trigger ID</div>
                    <div class="param-value text-primary fs-5">{{ $trigger->trigger_id }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="param-box">
                    <div class="param-label">2. Sumber Data</div>
                    <div class="param-value">{{ $trigger->sumber }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="param-box">
                    <div class="param-label">3. Source WP ID</div>
                    <div class="param-value" style="font-family: monospace;">{{ $trigger->source_wp_id ?: '-' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="param-box">
                    <div class="param-label">4. Tanggal Trigger</div>
                    <div class="param-value">{{ $trigger->tanggal_trigger ? $trigger->tanggal_trigger->format('d/m/Y') : '-' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="param-box">
                    <div class="param-label">5. Kode Unit</div>
                    <div class="param-value">{{ $trigger->kode_unit }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="param-box">
                    <div class="param-label">6. Unit / Kantor</div>
                    <div class="param-value">{{ $trigger->nama_unit }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="param-box">
                    <div class="param-label">7. Area Standar SOP 06</div>
                    <div class="param-value text-primary">{{ $trigger->area }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="param-box">
                    <div class="param-label">20. Kelengkapan Data</div>
                    <div class="param-value">
                        @if($trigger->kelengkapan_data === 'VALID')
                            <span class="badge badge-success">VALID (7 Parameter Lengkap)</span>
                        @else
                            <span class="badge badge-danger">LENGKAPI PARAMETER</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 2: Objek & Bukti Pengujian (Kolom 8 - 12, 15, 16, 21, 22) -->
<div class="detail-section-card">
    <div class="detail-section-header">
        <h5 class="detail-section-title">
            <i class="bi bi-box-seam text-primary"></i> 2. Objek &amp; Bukti Pengujian
        </h5>
        <span class="badge badge-gray" style="font-size: 0.78rem;">Kolom 8 - 12, 15, 16, 21, 22</span>
    </div>
    <div class="detail-section-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="param-box">
                    <div class="param-label">8. Objek / Key Pengujian</div>
                    <div class="param-value text-primary fs-5">{{ $trigger->objek_key }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="param-box">
                    <div class="param-label">9. Periode Mulai</div>
                    <div class="param-value">{{ $trigger->periode_mulai ? $trigger->periode_mulai->format('d/m/Y') : '-' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="param-box">
                    <div class="param-label">10. Periode Selesai</div>
                    <div class="param-value">{{ $trigger->periode_selesai ? $trigger->periode_selesai->format('d/m/Y') : '-' }}</div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="param-box">
                    <div class="param-label">11. Uraian Trigger / Temuan</div>
                    <div class="param-value fw-normal" style="white-space: pre-line; line-height: 1.6;">
                        {{ $trigger->uraian_trigger }}
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="param-box">
                    <div class="param-label">12. Bukti Awal Minimum</div>
                    <div class="param-value fw-normal" style="white-space: pre-line; line-height: 1.6;">
                        {{ $trigger->bukti_awal ?: '-' }}
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="param-box">
                    <div class="param-label">15. Jumlah Kejadian</div>
                    <div class="param-value fs-5">{{ $trigger->jumlah_kejadian }} Kali</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="param-box">
                    <div class="param-label">16. Eksposur Nominal (Rp)</div>
                    <div class="param-value text-danger fs-5">
                        Rp {{ number_format($trigger->eksposur_nominal, 0, ',', '.') }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="param-box">
                    <div class="param-label">21 &amp; 22. Status Duplikat</div>
                    <div class="param-value">
                        @if($trigger->status_duplikat === 'DUPLIKAT - REVIEW')
                            <span class="badge badge-warning">DUPLIKAT - REVIEW</span>
                        @else
                            <span class="badge badge-gray">UNIK</span>
                        @endif
                    </div>
                    <div class="text-muted mt-1" style="font-size: 0.78rem; font-family: monospace;">
                        Key: {{ $trigger->duplicate_key ?: '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 3: Reassessment Risiko (Kolom 13, 14, 23 - 26) -->
<div class="detail-section-card">
    <div class="detail-section-header">
        <h5 class="detail-section-title">
            <i class="bi bi-shield-check text-primary"></i> 3. Reassessment Risiko (Likelihood &amp; Impact)
        </h5>
        <span class="badge badge-gray" style="font-size: 0.78rem;">Kolom 13, 14, 23 - 26</span>
    </div>
    <div class="detail-section-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="param-box">
                    <div class="param-label">13 &amp; 14. Risk Sumber Asal</div>
                    <div class="param-value fs-5">
                        {{ $trigger->risk_score_sumber }} <span class="badge badge-gray ms-1">{{ $trigger->risk_level_sumber }}</span>
                    </div>
                    <div class="text-muted mt-1" style="font-size: 0.8rem;">Skor awal saat ditarik dari modul sumber</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="param-box">
                    <div class="param-label">23. Likelihood (1 - 5)</div>
                    <div class="param-value text-primary fs-4">{{ $trigger->likelihood }}</div>
                    <div class="text-muted mt-1" style="font-size: 0.8rem;">Probabilitas kemunculan berulang</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="param-box">
                    <div class="param-label">24. Impact (1 - 5)</div>
                    <div class="param-value text-danger fs-4">{{ $trigger->impact }}</div>
                    <div class="text-muted mt-1" style="font-size: 0.8rem;">Dampak finansial / operasional</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="param-box">
                    <div class="param-label">25. Risk Score Reassessment (MAX Formula)</div>
                    <div class="param-value fs-4 text-dark">{{ $trigger->risk_score_reassessment }}</div>
                    <div class="text-muted mt-1" style="font-size: 0.8rem;">Rumus: MAX(Risk Sumber, Likelihood &times; Impact)</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="param-box">
                    <div class="param-label">26. Tingkat Risiko Reassessment</div>
                    <div class="param-value mt-1">
                        <span class="badge {{ $riskCls }}" style="font-size: 0.95rem; font-weight: 700; padding: 0.4rem 0.85rem;">
                            {{ $trigger->risk_level_reassessment }}
                        </span>
                    </div>
                    <div class="text-muted mt-1" style="font-size: 0.8rem;">Kategori risiko terkini setelah penyesuaian</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 4: Routing & Keputusan Final (Kolom 17 - 19, 27 - 30) -->
<div class="detail-section-card">
    <div class="detail-section-header">
        <h5 class="detail-section-title">
            <i class="bi bi-signpost-split text-primary"></i> 4. Routing &amp; Keputusan Final
        </h5>
        <span class="badge badge-gray" style="font-size: 0.78rem;">Kolom 17 - 19, 27 - 30</span>
    </div>
    <div class="detail-section-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="param-box">
                    <div class="param-label">17. Perlu Verifikasi Lapangan</div>
                    <div class="param-value fs-5">{{ $trigger->perlu_verifikasi_lapangan }}</div>
                    <div class="text-muted mt-1" style="font-size: 0.8rem;">Uji keabsahan langsung di unit</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="param-box">
                    <div class="param-label">18. Indikasi Fraud</div>
                    <div class="param-value fs-5">
                        @if(strtoupper((string)$trigger->indikasi_fraud) === 'YA')
                            <span class="badge badge-danger">YA - RUJUKAN FRAUD</span>
                        @else
                            <span class="badge badge-gray">Tidak</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="param-box">
                    <div class="param-label">19. Objek Surprise / Fisik</div>
                    <div class="param-value fs-5">
                        @if(strtoupper((string)$trigger->objek_surprise_fisik) === 'YA')
                            <span class="badge badge-purple">YA - SURPRISE AUDIT</span>
                        @else
                            <span class="badge badge-gray">Tidak</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="param-box">
                    <div class="param-label">27. Keputusan Otomatis Sistem</div>
                    <div class="param-value text-primary fs-5">{{ $trigger->keputusan_otomatis }}</div>
                    <div class="text-muted mt-1" style="font-size: 0.8rem;">Dihitung dari matriks 8 formula keputusan SOP 06</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="param-box">
                    <div class="param-label">30. Keputusan Final</div>
                    <div class="param-value mt-1">
                        <span class="badge {{ $kepCls }}" style="font-size: 0.95rem; font-weight: 700; padding: 0.4rem 0.85rem;">
                            {{ $trigger->keputusan_final }}
                        </span>
                    </div>
                    <div class="text-muted mt-1" style="font-size: 0.8rem;">Keputusan mengikat yang menentukan alur tindak lanjut</div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="param-box">
                    <div class="param-label">28. Override Keputusan</div>
                    <div class="param-value">
                        @if($trigger->override_keputusan)
                            <span class="text-warning fw-bold fs-6">{{ $trigger->override_keputusan }}</span>
                        @else
                            <span class="text-muted">Tidak Ada Override (Gunakan Otomatis)</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="param-box">
                    <div class="param-label">29. Alasan Override</div>
                    <div class="param-value fw-normal" style="line-height: 1.5;">
                        {{ $trigger->alasan_override ?: 'Tidak ada alasan dicantumkan.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 5: Pelaksanaan, Tim & Review (Kolom 31 - 37) -->
<div class="detail-section-card mb-5">
    <div class="detail-section-header">
        <h5 class="detail-section-title">
            <i class="bi bi-person-check text-primary"></i> 5. Pelaksanaan, Tim &amp; Review
        </h5>
        <span class="badge badge-gray" style="font-size: 0.78rem;">Kolom 31 - 37</span>
    </div>
    <div class="detail-section-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="param-box">
                    <div class="param-label">31. SLA / Deadline</div>
                    <div class="param-value fs-5 {{ $isOverdue ? 'text-danger' : 'text-dark' }}">
                        {{ $trigger->sla_deadline ? $trigger->sla_deadline->format('d/m/Y') : '-' }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="param-box">
                    <div class="param-label">32. Status Workflow</div>
                    <div class="param-value fs-5">{{ $trigger->status_workflow }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="param-box">
                    <div class="param-label">33. KKA Tujuan</div>
                    <div class="param-value text-primary fs-5">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i>{{ $trigger->kka_tujuan }}
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="param-box">
                    <div class="param-label">34. Prioritas Visit</div>
                    <div class="param-value fs-5">{{ $trigger->prioritas_visit }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="param-box">
                    <div class="param-label">35. Assigned RA</div>
                    <div class="param-value fs-5">{{ $trigger->assigned_ra ?: 'Belum Ditetapkan' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="param-box">
                    <div class="param-label">36. Reviewer</div>
                    <div class="param-value fs-5">{{ $trigger->reviewer ?: 'Belum Direview' }}</div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="param-box">
                    <div class="param-label">37. Catatan Review</div>
                    <div class="param-value fw-normal" style="white-space: pre-line; line-height: 1.6;">
                        {{ $trigger->catatan_review ?: 'Belum ada catatan review supervisor/kabag.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
