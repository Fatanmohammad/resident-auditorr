@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

<div style="margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.3rem; font-weight: 700; color: var(--bs-blue-dark);">
        Selamat datang, {{ explode(' ', auth()->user()->name)[0] }}
    </h1>
    <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 0.2rem;">
        {{ now()->isoFormat('dddd, D MMMM Y') }} &mdash; Sistem Resident Auditor PT Bank Sulteng
    </p>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-4" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-diagram-3"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Unit Aktif</div>
            <div class="stat-value">{{ $totalUnit }}</div>
            <div class="stat-sub">unit pengawasan</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-people"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total RA</div>
            <div class="stat-value">{{ $totalRa }}</div>
            <div class="stat-sub">resident auditor</div>
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
        <div class="stat-icon blue"><i class="bi bi-clipboard2-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">Final Audit Plan</div>
            <div class="stat-value">{{ $totalFinalPlan }}</div>
            <div class="stat-sub">SOP 01 output</div>
        </div>
    </div>
</div>

{{-- Distrribusi Risiko & Frekuensi (§5) --}}
<div class="grid grid-cols-2" style="margin-bottom:1.5rem;">
    <div class="card">
        <div class="card-header"><div class="card-title">Distribusi Kategori Risiko</div></div>
        <div class="card-body">
            @foreach(['High','Moderate to High','Moderate','Low to Moderate','Low','Belum Dinilai'] as $cat)
            @php
                $count = $riskDist[$cat] ?? 0;
                $pct   = $totalUnit > 0 ? round($count / $totalUnit * 100) : 0;
                $cls   = match($cat) { 'High'=>'badge-danger','Moderate to High'=>'badge-warning','Moderate'=>'badge-info','Low to Moderate'=>'badge-purple','Belum Dinilai'=>'badge-gray',default=>'badge-gray' };
            @endphp
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.6rem;">
                <span class="badge {{ $cls }}" style="width:130px;justify-content:center;">{{ $cat }}</span>
                <div style="flex:1;background:#f3f4f6;border-radius:9999px;height:8px;">
                    <div style="width:{{ $pct }}%;background:var(--bs-blue);height:8px;border-radius:9999px;"></div>
                </div>
                <span style="font-size:0.8rem;font-weight:600;color:var(--text-main);width:30px;text-align:right;">{{ $count }}</span>
            </div>
            @endforeach
        </div>
    </div>
    <div>
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header"><div class="card-title">Distribusi Jenis Unit</div></div>
            <div class="card-body">
                @foreach($typeDist as $type => $count)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.4rem 0;border-bottom:1px solid var(--border-color);font-size:0.82rem;">
                    <span>{{ $type }}</span>
                    <span class="badge badge-info">{{ $count }} unit</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="card">
            <div class="card-header"><div class="card-title">Distribusi Frekuensi Onsite</div></div>
            <div class="card-body">
                @foreach($freqDist as $freq => $count)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.4rem 0;border-bottom:1px solid var(--border-color);font-size:0.82rem;">
                    <span>{{ $freq }}</span>
                    <span class="badge badge-info">{{ $count }} unit</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Audit Plan terkini + shortcut --}}
<div class="grid grid-cols-2">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Audit Plan Terkini</div>
            <a href="{{ route('audit-plan.index') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>RA</th><th>Periode</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($jadwalAktif as $plan)
                    <tr>
                        <td><strong>{{ $plan->raUser?->name ?? '-' }}</strong></td>
                        <td>{{ $plan->tahun_periode }}</td>
                        <td>
                            @php
                                $cls = match($plan->status_approval) {
                                    'approved'               => 'badge-success',
                                    'rejected'               => 'badge-danger',
                                    'waiting_kabag_approval',
                                    'waiting_kadiv_approval' => 'badge-warning',
                                    default                  => 'badge-gray',
                                };
                                $lbl = match($plan->status_approval) {
                                    'approved'               => 'Approved',
                                    'rejected'               => 'Ditolak',
                                    'waiting_kabag_approval' => 'Menunggu Kabag',
                                    'waiting_kadiv_approval' => 'Menunggu Kadiv',
                                    default                  => $plan->status_approval,
                                };
                            @endphp
                            <span class="badge {{ $cls }}">{{ $lbl }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3"><div class="empty-state"><i class="bi bi-calendar-x"></i><p>Belum ada data.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">Alur SOP 01</div></div>
        <div class="card-body">
            @php
                $steps = [
                    ['num'=>1,'label'=>'Master Unit','desc'=>'Data universe unit pengawasan','route'=>'units.index'],
                    ['num'=>2,'label'=>'Input Risiko','desc'=>'Raw metrics & risk scoring','route'=>'units.index'],
                    ['num'=>3,'label'=>'Penugasan RA','desc'=>'Coverage & assignment RA','route'=>'units.index'],
                    ['num'=>4,'label'=>'Jadwal Kunjungan','desc'=>'Frekuensi onsite & kapasitas','route'=>'scheduling.index'],
                    ['num'=>5,'label'=>'Final Audit Plan','desc'=>'Output akhir SOP 01','route'=>'final-audit-plan.index'],
                ];
            @endphp
            @foreach($steps as $i => $step)
            <a href="{{ route($step['route']) }}" style="display:flex; align-items:center; gap:1rem; padding:0.65rem 0; text-decoration:none; {{ $i < count($steps)-1 ? 'border-bottom:1px solid var(--border-color);' : '' }}">
                <div style="width:28px; height:28px; border-radius:50%; background:var(--bs-blue); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; flex-shrink:0;">{{ $step['num'] }}</div>
                <div style="flex:1;">
                    <div style="font-size:0.83rem; font-weight:600; color:var(--bs-blue-dark);">{{ $step['label'] }}</div>
                    <div style="font-size:0.72rem; color:var(--text-muted);">{{ $step['desc'] }}</div>
                </div>
                <i class="bi bi-chevron-right" style="color:var(--text-muted); font-size:0.72rem;"></i>
            </a>
            @endforeach
        </div>
    </div>
</div>

@endsection
