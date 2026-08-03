@extends('layouts.app')
@section('title', 'Final Audit Plan')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Final Audit Plan</h1>
        <p>Output SOP 01 — Rencana Audit Tahunan Seluruh Unit Kerja</p>
    </div>
    @if(in_array(auth()->user()->role, ['kabag_ra','kadiv_skai']))
    <form action="{{ route('final-audit-plan.generate-all') }}" method="POST" style="display:inline;">
        @csrf
        <input type="hidden" name="period" value="{{ $period }}">
        <button type="submit" class="btn btn-primary" onclick="return confirm('Generate Final Audit Plan untuk periode {{ $period }}?')">
            <i class="bi bi-lightning-charge"></i> Generate Semua
        </button>
    </form>
    @endif
</div>

@if(session('success'))
<div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif

{{-- Dashboard Agregat --}}
<div class="grid grid-cols-4" style="margin-bottom:1.25rem;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-building"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Unit</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Plan Approved</div>
            <div class="stat-value">{{ $stats['approved'] }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="bi bi-exclamation-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Perlu Mapping RA</div>
            <div class="stat-value">{{ $stats['draft'] }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="bi bi-shield-exclamation"></i></div>
        <div class="stat-info">
            <div class="stat-label">Risiko High</div>
            <div class="stat-value">{{ $stats['by_risk']['High'] ?? 0 }}</div>
        </div>
    </div>
</div>

{{-- Distribusi Risiko & Frekuensi --}}
<div class="grid grid-cols-2" style="margin-bottom:1.25rem;">
    <div class="card">
        <div class="card-header"><div class="card-title">Distribusi Kategori Risiko</div></div>
        <div class="card-body">
            @foreach(['High','Moderate to High','Moderate','Low to Moderate','Low'] as $cat)
            @php
                $count = $stats['by_risk'][$cat] ?? 0;
                $pct   = $stats['total'] > 0 ? round($count / $stats['total'] * 100) : 0;
                $cls   = match($cat) { 'High'=>'badge-danger','Moderate to High'=>'badge-warning','Moderate'=>'badge-info','Low to Moderate'=>'badge-purple',default=>'badge-gray' };
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
    <div class="card">
        <div class="card-header"><div class="card-title">Distribusi Frekuensi Onsite</div></div>
        <div class="card-body">
            @foreach(['Bulanan','Triwulanan','Semesteran','Tahunan','Tidak Terjadwal','Resident Daily Review'] as $freq)
            @php $count = $stats['by_frequency'][$freq] ?? 0; @endphp
            @if($count > 0)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.4rem 0;border-bottom:1px solid var(--border-color);font-size:0.82rem;">
                <span>{{ $freq }}</span>
                <span class="badge badge-info">{{ $count }} unit</span>
            </div>
            @endif
            @endforeach
        </div>
    </div>
</div>

{{-- Tabel Final Audit Plan --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">Daftar Final Audit Plan — Periode {{ $period }}</div>
        <div style="display:flex;gap:0.5rem;align-items:center;">
            <form method="GET" style="display:flex;gap:0.5rem;">
                <select name="period" class="form-select" style="width:auto;padding:0.35rem 0.75rem;font-size:0.82rem;" onchange="this.form.submit()">
                    @for($y = date('Y')+1; $y >= 2025; $y--)
                    <option value="{{ $y }}" {{ $y == $period ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
            <a href="{{ route('final-audit-plan.change-log') }}" class="btn btn-outline btn-sm"><i class="bi bi-clock-history"></i> Change Log</a>
        </div>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode</th><th>Unit</th><th>Tipe</th><th>Risiko</th>
                    <th>Primary RA</th><th>Offsite H+1</th><th>Frekuensi Onsite</th>
                    <th>Kunjungan/Tahun</th><th>Status Plan</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                @php
                    $riskCls = match($plan->risk_category) {
                        'High'=>'badge-danger','Moderate to High'=>'badge-warning',
                        'Moderate'=>'badge-info','Low to Moderate'=>'badge-purple',default=>'badge-gray'
                    };
                    $planCls = $plan->plan_status === 'Approved' ? 'badge-success' : 'badge-warning';
                @endphp
                <tr>
                    <td style="font-size:0.78rem;color:var(--text-muted);">{{ $plan->unit?->unit_code }}</td>
                    <td><strong>{{ $plan->unit?->unit_name }}</strong></td>
                    <td><span class="badge badge-info">{{ $plan->unit?->unit_type }}</span></td>
                    <td><span class="badge {{ $riskCls }}">{{ $plan->risk_category ?? '-' }}</span></td>
                    <td style="font-size:0.82rem;">{{ $plan->primaryRa?->ra_name ?? '<span style="color:#dc2626;">Belum Mapped</span>' }}</td>
                    <td>
                        @if($plan->daily_offsite_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-gray">Tidak</span>
                        @endif
                    </td>
                    <td style="font-size:0.82rem;">
                        {{ $plan->onsite_frequency_label ?? '-' }}
                        @if($plan->is_resident_daily_review)
                            <span class="badge badge-info" style="font-size:0.65rem;">Resident</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @if($plan->is_resident_daily_review)
                            <span style="color:var(--text-muted);font-size:0.8rem;">Harian</span>
                        @else
                            {{ $plan->visits_per_year }}x
                        @endif
                    </td>
                    <td><span class="badge {{ $planCls }}">{{ $plan->plan_status }}</span></td>
                    <td><a href="{{ route('final-audit-plan.show', $plan->id) }}" class="btn btn-outline btn-sm"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="10"><div class="empty-state"><i class="bi bi-clipboard-x"></i><p>Belum ada Final Audit Plan. Klik "Generate Semua" untuk memproses.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
