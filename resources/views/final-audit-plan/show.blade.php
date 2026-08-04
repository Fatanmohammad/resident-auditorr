@extends('layouts.app')
@section('title', 'Detail Final Audit Plan')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>{{ $finalAuditPlan->unit?->unit_name }}</h1>
        <p>{{ $finalAuditPlan->unit?->unit_code }} — Periode {{ $finalAuditPlan->period }}</p>
    </div>
    <a href="{{ route('final-audit-plan.index') }}" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="grid grid-cols-2" style="margin-bottom:1.25rem;">
    {{-- Info Unit & Plan --}}
    <div class="card">
        <div class="card-header"><div class="card-title">Informasi Unit & Plan</div></div>
        <div class="card-body">
            <table style="width:100%;font-size:0.875rem;border-collapse:collapse;">
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);width:45%;">Kode Unit</td><td>{{ $finalAuditPlan->unit?->unit_code }}</td></tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Nama Unit</td><td><strong>{{ $finalAuditPlan->unit?->unit_name }}</strong></td></tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Tipe Unit</td><td><span class="badge badge-info">{{ $finalAuditPlan->unit?->unit_type }}</span></td></tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Kantor Induk</td><td>{{ $finalAuditPlan->unit?->parent_office ?? '-' }}</td></tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Wilayah</td><td>{{ $finalAuditPlan->unit?->region ?? '-' }}</td></tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Kategori Risiko</td>
                    <td>
                        @php $cls = match($finalAuditPlan->risk_category) { 'High'=>'badge-danger','Moderate to High'=>'badge-warning','Moderate'=>'badge-info','Low to Moderate'=>'badge-purple',default=>'badge-gray' }; @endphp
                        <span class="badge {{ $cls }}">{{ $finalAuditPlan->risk_category ?? '-' }}</span>
                    </td>
                </tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Status Plan</td>
                    <td><span class="badge {{ $finalAuditPlan->plan_status === 'Approved' ? 'badge-success' : 'badge-warning' }}">{{ $finalAuditPlan->plan_status }}</span></td>
                </tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Catatan</td><td style="font-size:0.8rem;">{{ $finalAuditPlan->notes ?? '-' }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Info RA & Monitoring --}}
    <div class="card">
        <div class="card-header"><div class="card-title">Resident Auditor & Monitoring</div></div>
        <div class="card-body">
            <table style="width:100%;font-size:0.875rem;border-collapse:collapse;">
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);width:45%;">Primary RA</td>
                    <td>
                        @if($finalAuditPlan->primaryRa)
                            {{ $finalAuditPlan->primaryRa->ra_name }}
                            <div style="font-size:0.72rem;color:var(--text-muted);">{{ $finalAuditPlan->primaryRa->ra_id }}</div>
                        @else
                            <span style="color:#dc2626;font-size:0.82rem;">⚠ Belum ada mapping RA</span>
                        @endif
                    </td>
                </tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Backup RA</td>
                    <td>{{ $finalAuditPlan->backupRa?->ra_name ?? '<span style="color:var(--text-muted);font-size:0.8rem;">Tidak ada</span>' }}</td>
                </tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Daily Offsite H+1</td>
                    <td><span class="badge {{ $finalAuditPlan->daily_offsite_active ? 'badge-success' : 'badge-gray' }}">{{ $finalAuditPlan->daily_offsite_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                </tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Frekuensi Onsite</td>
                    <td>
                        {{ $finalAuditPlan->onsite_frequency_label ?? '-' }}
                        @if($finalAuditPlan->is_resident_daily_review)
                            <span class="badge badge-info" style="font-size:0.65rem;margin-left:4px;">Resident Daily</span>
                        @endif
                    </td>
                </tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Kunjungan/Tahun</td>
                    <td>{{ $finalAuditPlan->is_resident_daily_review ? 'Harian (Resident)' : $finalAuditPlan->visits_per_year.'x' }}</td>
                </tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Risk Trigger Visit</td>
                    <td><span class="badge {{ $finalAuditPlan->risk_trigger_visit_required ? 'badge-danger' : 'badge-gray' }}">{{ $finalAuditPlan->risk_trigger_visit_required ? 'Wajib' : 'Jika Trigger' }}</span></td>
                </tr>
            </table>
        </div>
    </div>
</div>

{{-- Skor Risiko --}}
@if($scoring)
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header"><div class="card-title">Detail Skor Risiko — Periode {{ $finalAuditPlan->period }}</div></div>
    <div class="card-body">
        <div class="grid grid-cols-3" style="gap:0.75rem;margin-bottom:1rem;">
            @php
                $bidangs = [
                    'Riwayat RA'    => $cs?->skor_riwayat_ra ?? 0,
                    'Kas/Teller'    => $cs?->skor_kas_teller ?? 0,
                    'CS/DPK'        => $cs?->skor_cs_dpk ?? 0,
                    'Kredit'        => $cs?->skor_kredit ?? 0,
                    'TI/ATM'        => $cs?->skor_ti_atm ?? 0,
                    'Monitoring TL' => $cs?->skor_monitoring_tl ?? 0,
                ];
            @endphp
            @foreach($bidangs as $label => $skor)
            <div style="background:#f8fafc;border-radius:var(--radius-md);padding:0.75rem;border:1px solid var(--border-color);">
                <div style="font-size:0.72rem;color:var(--text-muted);margin-bottom:0.25rem;">{{ $label }}</div>
                <div style="font-size:1.4rem;font-weight:700;color:var(--bs-blue-dark);">{{ number_format($skor, 1) }}</div>
                <div style="background:#e5e7eb;border-radius:9999px;height:4px;margin-top:0.4rem;">
                    <div style="width:{{ min(100,$skor) }}%;background:var(--bs-blue);height:4px;border-radius:9999px;"></div>
                </div>
            </div>
            @endforeach
        </div>
        <div style="display:flex;align-items:center;gap:1rem;padding:0.75rem;background:#f0f4f8;border-radius:var(--radius-md);">
            <div style="font-size:0.82rem;color:var(--text-muted);">Skor Weighted Final:</div>
            <div style="font-size:1.5rem;font-weight:700;color:var(--bs-blue-dark);">{{ number_format($scoring->weighted_score, 2) }}</div>
            <div style="font-size:0.82rem;color:var(--text-muted);">Kategori Awal:</div>
            <span class="badge badge-info">{{ $scoring->initial_category }}</span>
            @if($scoring->has_active_override)
            <span class="badge badge-danger">Override Aktif → High</span>
            @endif
        </div>
    </div>
</div>
@endif

{{-- Jadwal Kunjungan --}}
@if($visits->count() > 0)
<div class="card">
    <div class="card-header"><div class="card-title">Jadwal Kunjungan Onsite</div></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Kunjungan ke-</th><th>Bulan</th><th>Tanggal Mulai</th><th>Tanggal Selesai</th><th>Durasi</th><th>Status</th><th>Catatan</th></tr>
            </thead>
            <tbody>
                @foreach($visits as $v)
                @php
                    $stCls = match($v->status) {
                        'Completed'=>'badge-success','In Progress'=>'badge-info',
                        'Postponed'=>'badge-warning','Cancelled'=>'badge-danger',default=>'badge-gray'
                    };
                @endphp
                <tr>
                    <td style="text-align:center;font-weight:600;">{{ $v->visit_number }}</td>
                    <td>{{ \Carbon\Carbon::create(null, $v->recommended_month)->translatedFormat('F') }}</td>
                    <td>{{ $v->final_start_date?->format('d M Y') ?? '-' }}</td>
                    <td>{{ $v->final_end_date?->format('d M Y') ?? '-' }}</td>
                    <td>{{ $v->final_duration_days }} hari</td>
                    <td><span class="badge {{ $stCls }}">{{ $v->status }}</span></td>
                    <td style="font-size:0.78rem;color:var(--text-muted);">
                        {{ $v->manual_override_start ? '⚙ Override manual' : 'Auto' }}
                        {{ $v->manual_notes ? ' — '.$v->manual_notes : '' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
