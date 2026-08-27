@extends('layouts.app')
@section('title', 'Offsite Review — Daftar WP')

@section('content')

<div class="page-header">
    <div class="page-header-title">
        <h1><i class="bi bi-clipboard2-data" style="color:var(--bs-blue);"></i> Offsite Review</h1>
        <p>Daftar Kertas Kerja Pemantauan Harian (SOP 02)</p>
    </div>
    <a href="{{ route('offsite-review.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Buat WP Baru
    </a>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode WP</th>
                    <th>Unit</th>
                    <th>RA Pelaksana</th>
                    <th>Periode</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Validasi Unit</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($wps as $wp)
                <tr>
                    <td><span style="font-family:monospace;font-weight:600;color:var(--bs-blue);">{{ $wp->kode_wp }}</span></td>
                    <td>
                        <div style="font-weight:500;">{{ $wp->unit->unit_name ?? '-' }}</div>
                        <div style="font-size:0.72rem;color:var(--text-muted);">{{ $wp->unit->unit_type ?? '' }}</div>
                    </td>
                    <td>{{ $wp->ra->ra_name ?? '-' }}</td>
                    <td>{{ $wp->periode_data }}</td>
                    <td style="text-align:center;">
                        <span class="badge {{ match($wp->status_wp) {
                            'Approved'  => 'badge-success',
                            'Final'     => 'badge-info',
                            'In Review' => 'badge-warning',
                            default     => 'badge-gray'
                        } }}">{{ $wp->status_wp }}</span>
                    </td>
                    <td style="text-align:center;">
                        @if($wp->validasi_unit)
                            <span style="color:#16a34a;font-size:1rem;">✅</span>
                        @else
                            <span style="color:#d97706;font-size:1rem;">⚠️</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <a href="{{ route('offsite-review.dashboard', $wp) }}" class="btn btn-outline btn-sm">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="bi bi-clipboard2-x"></i>
                            <p>Belum ada WP Offsite. <a href="{{ route('offsite-review.create') }}">Buat yang pertama.</a></p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($wps->hasPages())
    <div style="padding:1rem 1.25rem;border-top:1px solid var(--border-color);">
        {{ $wps->links() }}
    </div>
    @endif
</div>

@endsection
