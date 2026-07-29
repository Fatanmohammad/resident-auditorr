@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

{{-- Greeting --}}
<div style="margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.3rem; font-weight: 700; color: var(--bs-blue-dark);">
        Selamat datang, {{ explode(' ', auth()->user()->name)[0] }}
    </h1>
    <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 0.2rem;">
        {{ now()->isoFormat('dddd, D MMMM Y') }} &mdash; Sistem Resident Auditor PT Bank Sulteng
    </p>
</div>

{{-- ============================================================ --}}
{{-- DASHBOARD: PIMSIE --}}
{{-- ============================================================ --}}
@if(auth()->user()->role === 'pimsie')

    {{-- Stat Cards --}}
    <div class="grid grid-cols-3" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-calendar3"></i></div>
            <div class="stat-info">
                <div class="stat-label">Total Audit Plan</div>
                <div class="stat-value">{{ $totalJadwal }}</div>
                <div class="stat-sub">{{ $jadwalSelesai }} telah disetujui</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Temuan Signifikan</div>
                <div class="stat-value">{{ $temuanSignifikan }}</div>
                <div class="stat-sub">{{ $temuanBerulang }} temuan berulang</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-people"></i></div>
            <div class="stat-info">
                <div class="stat-label">Total RA</div>
                <div class="stat-value">{{ $totalRa }}</div>
                <div class="stat-sub">{{ $totalCabang }} cabang</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2">
        {{-- Jadwal Audit --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">Jadwal Audit Terkini</div>
                <a href="{{ route('audit-plan.index') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>Cabang</th><th>RA</th><th>Periode</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($jadwalAktif as $plan)
                        <tr>
                            <td><strong>{{ $plan->cabang?->nama_cabang ?? '-' }}</strong></td>
                            <td style="color:var(--text-muted);">{{ $plan->raUser?->name ?? '-' }}</td>
                            <td>{{ $plan->tahun_periode }}</td>
                            <td>
                                @php $cls = match($plan->status_approval) { 'approved'=>'badge-success','rejected'=>'badge-danger','waiting_kabag_approval','waiting_kadiv_approval'=>'badge-warning',default=>'badge-gray' }; @endphp
                                <span class="badge {{ $cls }}">{{ $plan->status_approval === 'approved' ? 'Approved' : ($plan->status_approval === 'draft' ? 'Draft' : 'Proses') }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4"><div class="empty-state"><i class="bi bi-calendar-x"></i><p>Belum ada jadwal.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Temuan Signifikan & Berulang --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">Temuan Signifikan & Berulang</div>
                <a href="{{ route('temuan.index') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-2" style="gap: 0.75rem; margin-bottom: 1rem;">
                    <div style="text-align:center; padding:1rem; background:var(--bg-main); border-radius:var(--radius-md);">
                        <div style="font-size:1.75rem; font-weight:700; color:var(--bs-blue-dark);">{{ $temuanSignifikan }}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.2rem;">Signifikan</div>
                    </div>
                    <div style="text-align:center; padding:1rem; background:var(--bg-main); border-radius:var(--radius-md);">
                        <div style="font-size:1.75rem; font-weight:700; color:var(--bs-blue-dark);">{{ $temuanBerulang }}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.2rem;">Berulang</div>
                    </div>
                </div>
                <a href="{{ route('laporan.index') }}" class="btn btn-outline btn-sm btn-full">
                    <i class="bi bi-file-earmark-arrow-down"></i> Tarik Laporan Audit
                </a>
            </div>
        </div>
    </div>

{{-- ============================================================ --}}
{{-- DASHBOARD: RA --}}
{{-- ============================================================ --}}
@elseif(auth()->user()->role === 'ra')

    <div class="grid grid-cols-3" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-calendar3"></i></div>
            <div class="stat-info">
                <div class="stat-label">Audit Plan Saya</div>
                <div class="stat-value">{{ $jadwalAktif->count() }}</div>
                <div class="stat-sub">{{ $jadwalSelesai }} approved</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Temuan Signifikan</div>
                <div class="stat-value">{{ $temuanSignifikan }}</div>
                <div class="stat-sub">{{ $temuanBerulang }} berulang</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-clipboard2-check"></i></div>
            <div class="stat-info">
                <div class="stat-label">TL Selesai</div>
                <div class="stat-value">{{ $monitoringData->total_selesai ?? 0 }}</div>
                <div class="stat-sub">{{ $monitoringData->total_pending ?? 0 }} pending</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Jadwal Audit Saya</div>
                <a href="{{ route('audit-plan.index') }}" class="btn btn-outline btn-sm">Semua</a>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>Cabang</th><th>Periode</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($jadwalAktif as $plan)
                        <tr>
                            <td><strong>{{ $plan->cabang?->nama_cabang ?? '-' }}</strong></td>
                            <td>{{ $plan->tahun_periode }}</td>
                            <td>
                                @php $cls = match($plan->status_approval) { 'approved'=>'badge-success','rejected'=>'badge-danger',default=>'badge-warning' }; @endphp
                                <span class="badge {{ $cls }}">{{ $plan->status_approval === 'approved' ? 'Approved' : ($plan->status_approval === 'draft' ? 'Draft' : 'Proses') }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3"><div class="empty-state"><i class="bi bi-calendar-x"></i><p>Belum ada jadwal.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Progress Tindak Lanjut</div>
                <a href="{{ route('tindak-lanjut.index') }}" class="btn btn-outline btn-sm">Detail</a>
            </div>
            <div class="card-body">
                @php
                    $total   = $monitoringData->total_temuan ?? 0;
                    $selesai = $monitoringData->total_selesai ?? 0;
                    $pct     = $total > 0 ? round(($selesai / $total) * 100) : 0;
                @endphp
                <div style="margin-bottom:1rem;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:0.4rem;">
                        <span style="font-size:0.8rem; font-weight:600; color:var(--bs-blue-dark);">Progress TL</span>
                        <span style="font-size:0.8rem; font-weight:700; color:var(--bs-blue);">{{ $pct }}%</span>
                    </div>
                    <div style="height:8px; background:var(--border-color); border-radius:4px; overflow:hidden;">
                        <div style="width:{{ $pct }}%; height:100%; background:var(--bs-blue); border-radius:4px;"></div>
                    </div>
                </div>
                <div class="grid grid-cols-3" style="gap:0.75rem;">
                    <div style="text-align:center; padding:0.75rem; background:var(--bg-main); border-radius:var(--radius-md);">
                        <div style="font-size:1.25rem; font-weight:700; color:var(--bs-blue-dark);">{{ $total }}</div>
                        <div style="font-size:0.7rem; color:var(--text-muted);">Total</div>
                    </div>
                    <div style="text-align:center; padding:0.75rem; background:#f0fdf4; border-radius:var(--radius-md);">
                        <div style="font-size:1.25rem; font-weight:700; color:#065f46;">{{ $selesai }}</div>
                        <div style="font-size:0.7rem; color:#065f46;">Selesai</div>
                    </div>
                    <div style="text-align:center; padding:0.75rem; background:#fffbeb; border-radius:var(--radius-md);">
                        <div style="font-size:1.25rem; font-weight:700; color:#92400e;">{{ $monitoringData->total_pending ?? 0 }}</div>
                        <div style="font-size:0.7rem; color:#92400e;">Pending</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- ============================================================ --}}
{{-- DASHBOARD: KABAG RA & KADIV SKAI --}}
{{-- ============================================================ --}}
@else

    <div class="grid grid-cols-4" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-people"></i></div>
            <div class="stat-info">
                <div class="stat-label">Total RA Aktif</div>
                <div class="stat-value">{{ $totalRa }}</div>
                <div class="stat-sub">{{ $totalCabang }} cabang</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-calendar3"></i></div>
            <div class="stat-info">
                <div class="stat-label">Audit Plan</div>
                <div class="stat-value">{{ $totalJadwal }}</div>
                <div class="stat-sub">{{ $jadwalSelesai }} approved</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-file-earmark-text"></i></div>
            <div class="stat-info">
                <div class="stat-label">Temuan Signifikan</div>
                <div class="stat-value">{{ $temuanSignifikan }}</div>
                <div class="stat-sub">{{ $temuanBerulang }} berulang</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-clipboard2-check"></i></div>
            <div class="stat-info">
                <div class="stat-label">TL Selesai</div>
                <div class="stat-value">{{ $monitoringData->total_selesai ?? 0 }}</div>
                <div class="stat-sub">{{ $monitoringData->total_pending ?? 0 }} pending</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2" style="margin-bottom: 1.5rem;">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Jadwal Audit Terkini</div>
                <a href="{{ route('audit-plan.index') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>Cabang</th><th>RA</th><th>Periode</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($jadwalAktif as $plan)
                        <tr>
                            <td><strong>{{ $plan->cabang?->nama_cabang ?? '-' }}</strong></td>
                            <td style="color:var(--text-muted);">{{ $plan->raUser?->name ?? '-' }}</td>
                            <td>{{ $plan->tahun_periode }}</td>
                            <td>
                                @php
                                    $cls = match($plan->status_approval) { 'approved'=>'badge-success','rejected'=>'badge-danger','waiting_kabag_approval','waiting_kadiv_approval'=>'badge-warning',default=>'badge-gray' };
                                    $lbl = match($plan->status_approval) { 'approved'=>'Approved','rejected'=>'Ditolak','waiting_kabag_approval'=>'Menunggu Kabag','waiting_kadiv_approval'=>'Menunggu Kadiv',default=>'Draft' };
                                @endphp
                                <span class="badge {{ $cls }}">{{ $lbl }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4"><div class="empty-state"><i class="bi bi-calendar-x"></i><p>Belum ada jadwal.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Ringkasan Monitoring TL</div>
                <a href="{{ route('monitoring.index') }}" class="btn btn-outline btn-sm">Detail</a>
            </div>
            <div class="card-body">
                @php
                    $total   = $monitoringData->total_temuan ?? 0;
                    $selesai = $monitoringData->total_selesai ?? 0;
                    $pct     = $total > 0 ? round(($selesai / $total) * 100) : 0;
                @endphp
                <div style="margin-bottom:1.25rem;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:0.4rem;">
                        <span style="font-size:0.8rem; font-weight:600; color:var(--bs-blue-dark);">Progress Penyelesaian TL</span>
                        <span style="font-size:0.8rem; font-weight:700; color:var(--bs-blue);">{{ $pct }}%</span>
                    </div>
                    <div style="height:8px; background:var(--border-color); border-radius:4px; overflow:hidden;">
                        <div style="width:{{ $pct }}%; height:100%; background:var(--bs-blue); border-radius:4px;"></div>
                    </div>
                </div>
                <div class="grid grid-cols-3" style="gap:0.75rem; margin-bottom:1.25rem;">
                    <div style="text-align:center; padding:0.875rem; background:var(--bg-main); border-radius:var(--radius-md);">
                        <div style="font-size:1.5rem; font-weight:700; color:var(--bs-blue-dark);">{{ $total }}</div>
                        <div style="font-size:0.72rem; color:var(--text-muted);">Total Temuan</div>
                    </div>
                    <div style="text-align:center; padding:0.875rem; background:#f0fdf4; border-radius:var(--radius-md);">
                        <div style="font-size:1.5rem; font-weight:700; color:#065f46;">{{ $selesai }}</div>
                        <div style="font-size:0.72rem; color:#065f46;">TL Selesai</div>
                    </div>
                    <div style="text-align:center; padding:0.875rem; background:#fffbeb; border-radius:var(--radius-md);">
                        <div style="font-size:1.5rem; font-weight:700; color:#92400e;">{{ $monitoringData->total_pending ?? 0 }}</div>
                        <div style="font-size:0.72rem; color:#92400e;">TL Pending</div>
                    </div>
                </div>
                @if($temuanPerBidang->count())
                <div style="border-top:1px solid var(--border-color); padding-top:1rem;">
                    <div style="font-size:0.72rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.75rem;">Temuan per Kategori</div>
                    @foreach($temuanPerBidang as $kategori => $jumlah)
                    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.5rem;">
                        <span style="font-size:0.78rem; color:var(--text-muted); width:90px; flex-shrink:0;">{{ ucfirst($kategori) }}</span>
                        <div style="flex:1; height:6px; background:var(--border-color); border-radius:3px;">
                            <div style="width:{{ $total > 0 ? round(($jumlah/$total)*100) : 0 }}%; height:100%; background:var(--bs-blue); border-radius:3px;"></div>
                        </div>
                        <span style="font-size:0.78rem; font-weight:600; color:var(--bs-blue-dark); width:20px; text-align:right;">{{ $jumlah }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2">
        <div class="card">
            <div class="card-header"><div class="card-title">Alur Proses Audit</div></div>
            <div class="card-body">
                @php
                    $steps = [
                        ['num'=>1,'label'=>'Input Parameter','desc'=>'Parameter RKAT & scoring awal','route'=>'parameter.index'],
                        ['num'=>2,'label'=>'Penjadwalan Audit','desc'=>'Audit Plan & approval berjenjang','route'=>'audit-plan.index'],
                        ['num'=>3,'label'=>'Pelaksanaan Audit','desc'=>'KKA, KHA & pencatatan temuan','route'=>'kka.index'],
                        ['num'=>4,'label'=>'Monitoring','desc'=>'Monitoring temuan & tindak lanjut','route'=>'monitoring.index'],
                        ['num'=>5,'label'=>'Scoring & Laporan','desc'=>'Kalkulasi skor & generate laporan','route'=>'scoring.index'],
                    ];
                @endphp
                @foreach($steps as $i => $step)
                <a href="{{ route($step['route']) }}" style="display:flex; align-items:center; gap:1rem; padding:0.75rem 0; text-decoration:none; {{ $i < count($steps)-1 ? 'border-bottom:1px solid var(--border-color);' : '' }}">
                    <div style="width:32px; height:32px; border-radius:50%; background:var(--bs-blue); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.78rem; font-weight:700; flex-shrink:0;">{{ $step['num'] }}</div>
                    <div style="flex:1;">
                        <div style="font-size:0.85rem; font-weight:600; color:var(--bs-blue-dark);">{{ $step['label'] }}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">{{ $step['desc'] }}</div>
                    </div>
                    <i class="bi bi-chevron-right" style="color:var(--text-muted); font-size:0.75rem;"></i>
                </a>
                @endforeach
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:1.25rem;">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Scoring RA Terbaru</div>
                    <a href="{{ route('scoring.index') }}" class="btn btn-outline btn-sm">Semua</a>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead><tr><th>Cabang</th><th>Skor</th><th>Peringkat</th></tr></thead>
                        <tbody>
                            @forelse($scoringTerbaru as $s)
                            <tr>
                                <td><strong>{{ $s->auditPlan?->cabang?->nama_cabang ?? '-' }}</strong></td>
                                <td style="font-weight:700; color:var(--bs-blue);">{{ $s->skor_akhir }}</td>
                                <td><span class="badge {{ $s->skor_akhir >= 85 ? 'badge-success' : ($s->skor_akhir >= 70 ? 'badge-warning' : 'badge-danger') }}">{{ $s->peringkat_ra }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="3"><div class="empty-state" style="padding:1rem;"><i class="bi bi-bar-chart"></i><p>Belum ada data scoring.</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($raMalas > 0)
            <div style="padding:1rem 1.25rem; background:#fffbeb; border:1px solid #fde68a; border-radius:var(--radius-lg); display:flex; align-items:center; gap:0.875rem;">
                <i class="bi bi-exclamation-triangle" style="font-size:1.25rem; color:#92400e; flex-shrink:0;"></i>
                <div>
                    <div style="font-size:0.85rem; font-weight:600; color:#92400e;">Perhatian</div>
                    <div style="font-size:0.78rem; color:#92400e; margin-top:0.1rem;">{{ $raMalas }} RA tidak memiliki aktivitas dalam 30 hari terakhir.</div>
                </div>
            </div>
            @endif
        </div>
    </div>

@endif
@endsection
