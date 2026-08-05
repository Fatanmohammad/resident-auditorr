@extends('layouts.app')
@section('title', 'Assignment RA')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Assignment RA</h1>
        <p>Penugasan Resident Auditor per unit — murni berbasis lokasi (geografis)</p>
    </div>
    <div style="display:flex;gap:0.5rem;align-items:center;">
        <form method="GET" style="display:flex;gap:0.5rem;">
            <select name="period" class="form-select" style="width:auto;padding:0.35rem 0.75rem;font-size:0.82rem;" onchange="this.form.submit()">
                @for($y = date('Y')+1; $y >= 2025; $y--)
                <option value="{{ $y }}" {{ $y == $period ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
@if(in_array(auth()->user()->role, ['kabag_ra','kadiv_skai','admin']))
        <form action="{{ route('coverage.assign-all') }}" method="POST" style="display:inline;">
            @csrf
            <input type="hidden" name="year" value="{{ $period }}">
            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Proses assignment RA untuk semua unit?')">
                <i class="bi bi-arrow-repeat"></i> Assign Semua
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Ringkasan status mapping --}}
@php
    $needsMapping = $assignments->filter(fn($a) => str_contains($a->notes ?? '', 'Perlu Mapping'));
    $noBackup     = $assignments->filter(fn($a) => !$a->backup_ra_id);
@endphp
<div class="grid grid-cols-3" style="margin-bottom:1.25rem;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-people"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Unit Terproses</div>
            <div class="stat-value">{{ $assignments->count() }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Perlu Mapping RA</div>
            <div class="stat-value">{{ $needsMapping->count() }}</div>
            <div class="stat-sub">base_ra_unit belum terdaftar</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="bi bi-person-slash"></i></div>
        <div class="stat-info">
            <div class="stat-label">Tanpa Backup RA</div>
            <div class="stat-value">{{ $noBackup->count() }}</div>
            <div class="stat-sub">perlu perhatian</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Daftar Assignment — Periode {{ $period }}</div>
        <form method="GET" style="display:flex;gap:0.5rem;">
            <input type="hidden" name="period" value="{{ $period }}">
            <select name="ra" class="form-select" style="width:auto;padding:0.35rem 0.75rem;font-size:0.82rem;" onchange="this.form.submit()">
                <option value="">Semua RA</option>
                @foreach(\App\Models\Ra::where('status','Aktif')->orderBy('ra_name')->get() as $ra)
                <option value="{{ $ra->id }}" {{ request('ra') == $ra->id ? 'selected' : '' }}>{{ $ra->ra_name }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Unit</th><th>Kategori Risiko</th><th>Resident Base</th>
                    <th>Primary RA</th><th>Backup RA</th><th>Status</th><th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $filterRa = request('ra');
                    $rows = $assignments->when($filterRa, fn($c) => $c->filter(fn($a) => $a->primary_ra_id == $filterRa || $a->backup_ra_id == $filterRa));
                @endphp
                @forelse($rows as $assign)
                @php
                    $risk     = $riskScorings[$assign->unit_id] ?? null;
                    $riskCls  = match($risk) {
                        'High'=>'badge-danger','Moderate to High'=>'badge-warning',
                        'Moderate'=>'badge-info','Low to Moderate'=>'badge-purple',default=>'badge-gray'
                    };
                    $needsM = str_contains($assign->notes ?? '', 'Perlu Mapping');
                @endphp
                <tr>
                    <td>
                        <strong>{{ $assign->unit?->unit_name }}</strong>
                        <div style="font-size:0.72rem;color:var(--text-muted);">{{ $assign->unit?->unit_code }}</div>
                    </td>
                    <td>
                        @if($risk)
                            <span class="badge {{ $riskCls }}">{{ $risk }}</span>
                        @else
                            <span class="badge badge-gray">Belum dinilai</span>
                        @endif
                    </td>
                    <td style="font-size:0.82rem;">{{ $assign->resident_base ?? '-' }}</td>
                    <td style="font-size:0.82rem;">
                        @if($assign->primaryRa)
                            <strong>{{ $assign->primaryRa->ra_name }}</strong>
                            <div style="font-size:0.7rem;color:var(--text-muted);">{{ $assign->primaryRa->ra_id }}</div>
                        @else
                            <span style="color:#dc2626;font-size:0.8rem;">⚠ Belum Mapped</span>
                        @endif
                    </td>
                    <td>
                        @if($assign->backupRa)
                            <span style="font-size:0.82rem;">{{ $assign->backupRa->ra_name }}</span>
                        @else
                            <span class="badge badge-warning" style="font-size:0.7rem;">Tidak ada backup</span>
                        @endif
                    </td>
                    <td><span class="badge badge-success">{{ $assign->assignment_status }}</span></td>
                    <td>
                        @if($needsM)
                            <span class="badge badge-danger" style="font-size:0.7rem;">Perlu Mapping RA</span>
                        @else
                            <span style="font-size:0.78rem;color:var(--text-muted);">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-people"></i><p>Belum ada data assignment. Klik "Assign Semua" untuk memproses.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
