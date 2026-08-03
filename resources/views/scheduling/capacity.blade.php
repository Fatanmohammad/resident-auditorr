@extends('layouts.app')
@section('title', 'Kapasitas RA')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Kapasitas Resident Auditor</h1>
        <p>Beban kerja RA per bulan — Periode {{ $period }}</p>
    </div>
    <form method="GET">
        <select name="period" class="form-select" style="width:auto;" onchange="this.form.submit()">
            @for($y = date('Y')+1; $y >= 2025; $y--)
            <option value="{{ $y }}" {{ $y == $period ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </form>
</div>

@foreach($ras as $ra)
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header">
        <div class="card-title">{{ $ra->ra_name }} <span style="font-size:0.75rem;color:var(--text-muted);font-weight:400;">{{ $ra->ra_id }}</span></div>
        <span class="badge {{ $ra->status === 'Aktif' ? 'badge-success' : 'badge-gray' }}">{{ $ra->status }}</span>
    </div>
    @if($ra->capacities->count() > 0)
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bulan</th><th>Unit Offsite</th><th>Est. Offsite (hari)</th>
                    <th>Kunjungan</th><th>Visit Days</th><th>Total Beban</th>
                    <th>Utilisasi</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ra->capacities as $cap)
                @php
                    $stCls = match($cap->capacity_status) { 'Over Capacity'=>'badge-danger','Warning'=>'badge-warning',default=>'badge-success' };
                    $pct   = min(100, round($cap->utilization * 100));
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::create(null, $cap->month)->translatedFormat('F') }}</td>
                    <td style="text-align:center;">{{ $cap->daily_offsite_unit_count }}</td>
                    <td style="text-align:center;">{{ $cap->estimated_offsite_days }}</td>
                    <td style="text-align:center;">{{ $cap->scheduled_visit_count }}</td>
                    <td style="text-align:center;">{{ $cap->scheduled_visit_days }}</td>
                    <td style="text-align:center;font-weight:600;">{{ $cap->total_workload_days }} / {{ $cap->effective_working_days }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <div style="flex:1;background:#e5e7eb;border-radius:9999px;height:6px;">
                                <div style="width:{{ $pct }}%;background:{{ $cap->capacity_status==='Over Capacity'?'#dc2626':($cap->capacity_status==='Warning'?'#f59e0b':'var(--bs-blue)') }};height:6px;border-radius:9999px;"></div>
                            </div>
                            <span style="font-size:0.78rem;font-weight:600;width:38px;">{{ $pct }}%</span>
                        </div>
                    </td>
                    <td><span class="badge {{ $stCls }}">{{ $cap->capacity_status }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state" style="padding:1.5rem;"><i class="bi bi-calendar-x"></i><p>Belum ada data kapasitas untuk periode ini.</p></div>
    @endif
</div>
@endforeach
@endsection
