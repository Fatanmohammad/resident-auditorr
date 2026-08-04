@extends('layouts.app')
@section('title', 'Coverage Offsite')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Coverage Offsite</h1>
        <p>Setup Fungsi Unit &amp; pemantauan coverage per unit — Periode {{ $period }}</p>
    </div>
<form method="GET" style="display:flex;gap:0.5rem;">
        <select name="period" class="form-select" style="width:auto;padding:0.35rem 0.75rem;font-size:0.82rem;" onchange="this.form.submit()">
            @for($y = date('Y')+1; $y >= 2025; $y--)
            <option value="{{ $y }}" {{ $y == $period ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </form>
    @if(in_array(auth()->user()->role, ['kabag_ra','kadiv_skai']))
    <form action="{{ route('coverage.generate-all') }}" method="POST" style="display:inline;">
        @csrf
        <input type="hidden" name="period" value="{{ $period }}">
        <button type="submit" class="btn btn-primary" onclick="return confirm('Generate coverage summary & detail untuk semua unit periode {{ $period }}?')">
            <i class="bi bi-lightning-charge"></i> Generate Semua
        </button>
    </form>
    @endif
</div>

{{-- Ringkasan status coverage --}}
@php
    $lengkap = $units->filter(fn($u) => optional($u->coverageSummaries->first())->coverage_status === 'Lengkap')->count();
    $cukup   = $units->filter(fn($u) => optional($u->coverageSummaries->first())->coverage_status === 'Cukup')->count();
    $perlu   = $units->filter(fn($u) => in_array(optional($u->coverageSummaries->first())->coverage_status, ['Perlu Lengkapi Setup', 'Nonaktif']))->count();
    $belum   = $units->filter(fn($u) => !$u->coverageSummaries->first())->count();
@endphp
<div class="grid grid-cols-4" style="margin-bottom:1.25rem;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-building"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Unit Aktif</div>
            <div class="stat-value">{{ $units->count() }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Coverage Lengkap</div>
            <div class="stat-value">{{ $lengkap }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="bi bi-exclamation-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Coverage Cukup</div>
            <div class="stat-value">{{ $cukup }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="bi bi-shield-exclamation"></i></div>
        <div class="stat-info">
            <div class="stat-label">Perlu Lengkapi</div>
            <div class="stat-value">{{ $perlu + $belum }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Daftar Coverage per Unit — Periode {{ $period }}</div>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode</th><th>Unit</th><th>Tipe</th>
                    <th>Area Aktif</th><th>Coverage Score</th><th>Status Coverage</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $unit)
                @php
                    $summary = $unit->coverageSummaries->first();
                    $stCls = match($summary?->coverage_status) {
                        'Lengkap' => 'badge-success',
                        'Cukup'   => 'badge-warning',
                        'Nonaktif' => 'badge-gray',
                        default   => 'badge-danger',
                    };
                @endphp
                <tr>
                    <td style="font-size:0.78rem;color:var(--text-muted);">{{ $unit->unit_code }}</td>
                    <td><strong>{{ $unit->unit_name }}</strong></td>
                    <td><span class="badge badge-info">{{ $unit->unit_type }}</span></td>
                    <td style="text-align:center;">
                        @if($summary)
                            {{ $summary->active_area_count }} / 8
                        @else
                            <span class="badge badge-gray">Belum setup</span>
                        @endif
                    </td>
                    <td>
                        @if($summary)
                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                <div style="flex:1;background:#e5e7eb;border-radius:9999px;height:6px;min-width:60px;">
                                    <div style="width:{{ round($summary->coverage_score * 100) }}%;background:var(--bs-blue);height:6px;border-radius:9999px;"></div>
                                </div>
                                <span style="font-size:0.8rem;font-weight:600;">{{ round($summary->coverage_score * 100) }}%</span>
                            </div>
                        @else
                            <span style="font-size:0.8rem;color:var(--text-muted);">-</span>
                        @endif
                    </td>
                    <td>
                        @if($summary)
                            <span class="badge {{ $stCls }}">{{ $summary->coverage_status }}</span>
                        @else
                            <span class="badge badge-gray">Belum dinilai</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('coverage.show', $unit) }}" class="btn btn-outline btn-sm" title="Setup Coverage">
                            <i class="bi bi-grid-3x3-gap"></i> Setup
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-grid-3x3-gap"></i><p>Belum ada data unit.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
