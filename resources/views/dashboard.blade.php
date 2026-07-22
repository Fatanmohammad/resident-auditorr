@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
{{-- Row 1: 5 Stat Cards sesuai diagram --}}
<div class="grid grid-cols-5" style="margin-bottom: 1.25rem;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-people-fill"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total RA Aktif</div>
            <div class="stat-value">{{ $totalRa }}</div>
            <div class="stat-sub">{{ $totalCabang }} cabang</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div class="stat-info">
            <div class="stat-label">Temuan Signifikan</div>
            <div class="stat-value">{{ $temuanSignifikan }}</div>
            <div class="stat-sub">{{ $temuanBerulang }} berulang</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="bi bi-calendar3"></i></div>
        <div class="stat-info">
            <div class="stat-label">Audit Plan</div>
            <div class="stat-value">{{ $totalJadwal }}</div>
            <div class="stat-sub">{{ $jadwalSelesai }} approved</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="bi bi-bar-chart-line-fill"></i></div>
        <div class="stat-info">
            <div class="stat-label">Scoring Terbaru</div>
            <div class="stat-value">{{ $scoringTerbaru->count() }}</div>
            <div class="stat-sub">laporan scoring</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-graph-up-arrow"></i></div>
        <div class="stat-info">
            <div class="stat-label">TL Selesai</div>
            <div class="stat-value">{{ $monitoringData->total_selesai ?? 0 }}</div>
            <div class="stat-sub">{{ $monitoringData->total_pending ?? 0 }} pending</div>
        </div>
    </div>
</div>

{{-- Row 2: 5 Widget sesuai diagram --}}
<div class="grid grid-cols-5" style="margin-bottom: 1.25rem;">

    {{-- Widget 1: RA (Resident Auditor) --}}
    <div class="widget-card">
        <div class="widget-header">
            <div class="widget-title"><i class="bi bi-person-badge"></i> RA per Cabang</div>
        </div>
        <div class="widget-body">
            <ul class="widget-list">
                @forelse($raPerCabang as $cabangId => $ras)
                    <li>
                        <span class="label">{{ $ras->first()->cabang?->nama_cabang ?? 'Tanpa Cabang' }}</span>
                        <span class="value">{{ $ras->count() }} RA</span>
                    </li>
                @empty
                    <li><span class="label" style="width:100%; text-align:center;">Belum ada data</span></li>
                @endforelse
            </ul>
            @if($raMalas > 0)
            <div style="margin-top: 0.75rem; padding: 0.5rem 0.75rem; background: #fee2e2; border-radius: var(--radius-sm); font-size: 0.75rem; color: #dc2626;">
                <i class="bi bi-exclamation-circle"></i> {{ $raMalas }} RA tidak aktif 30 hari
            </div>
            @endif
        </div>
    </div>

    {{-- Widget 2: Temuan Lemah --}}
    <div class="widget-card">
        <div class="widget-header">
            <div class="widget-title"><i class="bi bi-bug"></i> Temuan Lemah</div>
        </div>
        <div class="widget-body">
            <ul class="widget-list">
                <li><span class="label">Signifikan</span><span class="badge badge-danger">{{ $temuanSignifikan }}</span></li>
                <li><span class="label">Berulang</span><span class="badge badge-warning">{{ $temuanBerulang }}</span></li>
                @foreach($temuanPerBidang as $kategori => $total)
                    @if(!in_array($kategori, ['signifikan','berulang']))
                    <li><span class="label">{{ ucfirst($kategori) }}</span><span class="value">{{ $total }}</span></li>
                    @endif
                @endforeach
            </ul>
            <a href="{{ route('temuan.index') }}" class="btn btn-outline btn-sm btn-full" style="margin-top: 0.75rem;">Lihat Semua</a>
        </div>
    </div>

    {{-- Widget 3: Penjadwalan Audit --}}
    <div class="widget-card">
        <div class="widget-header">
            <div class="widget-title"><i class="bi bi-calendar-check"></i> Penjadwalan Audit</div>
        </div>
        <div class="widget-body">
            <ul class="widget-list">
                @forelse($jadwalAktif as $plan)
                <li>
                    <div>
                        <div style="font-weight: 600; font-size: 0.78rem;">{{ $plan->cabang?->nama_cabang ?? '-' }}</div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $plan->raUser?->name ?? '-' }}</div>
                    </div>
                    <span class="badge {{ $plan->status_approval === 'approved' ? 'badge-success' : 'badge-warning' }}">
                        {{ $plan->status_approval === 'approved' ? 'OK' : 'Proses' }}
                    </span>
                </li>
                @empty
                <li><span class="label" style="width:100%; text-align:center;">Belum ada jadwal</span></li>
                @endforelse
            </ul>
            <a href="{{ route('audit-plan.index') }}" class="btn btn-outline btn-sm btn-full" style="margin-top: 0.75rem;">Lihat Semua</a>
        </div>
    </div>

    {{-- Widget 4: Kinerja & Scoring --}}
    <div class="widget-card">
        <div class="widget-header">
            <div class="widget-title"><i class="bi bi-trophy"></i> Kinerja & Scoring</div>
        </div>
        <div class="widget-body">
            <ul class="widget-list">
                @forelse($scoringTerbaru as $scoring)
                <li>
                    <div>
                        <div style="font-weight: 600; font-size: 0.78rem;">{{ $scoring->auditPlan?->cabang?->nama_cabang ?? '-' }}</div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $scoring->peringkat_ra }}</div>
                    </div>
                    <span style="font-weight: 700; color: var(--bs-blue); font-size: 0.85rem;">{{ $scoring->skor_akhir }}</span>
                </li>
                @empty
                <li><span class="label" style="width:100%; text-align:center;">Belum ada scoring</span></li>
                @endforelse
            </ul>
            <a href="{{ route('scoring.index') }}" class="btn btn-outline btn-sm btn-full" style="margin-top: 0.75rem;">Lihat Semua</a>
        </div>
    </div>

    {{-- Widget 5: Ringkasan Monitoring --}}
    <div class="widget-card">
        <div class="widget-header">
            <div class="widget-title"><i class="bi bi-clipboard-data"></i> Ringkasan Monitoring</div>
        </div>
        <div class="widget-body">
            <ul class="widget-list">
                <li><span class="label">Total Temuan</span><span class="value">{{ $monitoringData->total_temuan ?? 0 }}</span></li>
                <li><span class="label">TL Selesai</span><span class="badge badge-success">{{ $monitoringData->total_selesai ?? 0 }}</span></li>
                <li><span class="label">TL Pending</span><span class="badge badge-warning">{{ $monitoringData->total_pending ?? 0 }}</span></li>
                <li>
                    <span class="label">Progress</span>
                    @php
                        $total = ($monitoringData->total_temuan ?? 0);
                        $selesai = ($monitoringData->total_selesai ?? 0);
                        $pct = $total > 0 ? round(($selesai / $total) * 100) : 0;
                    @endphp
                    <span class="value">{{ $pct }}%</span>
                </li>
            </ul>
            <a href="{{ route('monitoring.index') }}" class="btn btn-outline btn-sm btn-full" style="margin-top: 0.75rem;">Lihat Detail</a>
        </div>
    </div>
</div>

{{-- Row 3: Alur Proses + Jadwal Terkini --}}
<div class="grid grid-cols-2">
    <div class="widget-card">
        <div class="widget-header">
            <div class="widget-title"><i class="bi bi-diagram-3"></i> Ringkasan Alur Proses</div>
        </div>
        <div class="widget-body">
            <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                @php
                    $steps = [
                        ['num'=>'1','label'=>'Input Parameter','route'=>'parameter.index','color'=>'#d1fae5','text'=>'#065f46'],
                        ['num'=>'2','label'=>'Penjadwalan Audit','route'=>'audit-plan.index','color'=>'#fef3c7','text'=>'#92400e'],
                        ['num'=>'3','label'=>'Pelaksanaan Audit','route'=>'kka.index','color'=>'#dbeafe','text'=>'#1e40af'],
                        ['num'=>'4','label'=>'Monitoring','route'=>'monitoring.index','color'=>'#ede9fe','text'=>'#7c3aed'],
                        ['num'=>'5','label'=>'Tindak Lanjut','route'=>'tindak-lanjut.index','color'=>'#fce7f3','text'=>'#9d174d'],
                        ['num'=>'6','label'=>'Scoring & Laporan','route'=>'scoring.index','color'=>'#fee2e2','text'=>'#dc2626'],
                    ];
                @endphp
                @foreach($steps as $step)
                <a href="{{ route($step['route']) }}" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0.75rem; border-radius: var(--radius-md); background: {{ $step['color'] }}; text-decoration: none; transition: var(--transition);">
                    <span style="width: 24px; height: 24px; border-radius: 50%; background: {{ $step['text'] }}; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; flex-shrink: 0;">{{ $step['num'] }}</span>
                    <span style="font-size: 0.82rem; font-weight: 600; color: {{ $step['text'] }};">{{ $step['label'] }}</span>
                    <i class="bi bi-arrow-right" style="margin-left: auto; color: {{ $step['text'] }}; font-size: 0.75rem;"></i>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="widget-card">
        <div class="widget-header">
            <div class="widget-title"><i class="bi bi-clock-history"></i> Jadwal Audit Terkini</div>
            <a href="{{ route('audit-plan.index') }}" class="btn btn-outline btn-sm">Semua</a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cabang</th>
                        <th>RA</th>
                        <th>Periode</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwalAktif as $plan)
                    <tr>
                        <td><strong>{{ $plan->cabang?->nama_cabang ?? '-' }}</strong></td>
                        <td>{{ $plan->raUser?->name ?? '-' }}</td>
                        <td>{{ $plan->tahun_periode }}</td>
                        <td>
                            @php
                                $cls = match($plan->status_approval) {
                                    'approved' => 'badge-success',
                                    'rejected' => 'badge-danger',
                                    'draft'    => 'badge-gray',
                                    default    => 'badge-warning',
                                };
                            @endphp
                            <span class="badge {{ $cls }}">{{ strtoupper(str_replace('_',' ',$plan->status_approval)) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada data</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
