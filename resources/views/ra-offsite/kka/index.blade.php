@extends('layouts.app')

@push('styles')
<style>
    .kka-container { display: flex; flex-direction: column; gap: 1.25rem; }
    .kka-nav-tabs { display: flex; gap: 0.5rem; border-bottom: 2px solid var(--border-color, #e2e8f0); overflow-x: auto; padding-bottom: 1px; }
    .kka-nav-tabs .nav-link { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.1rem; font-size: 0.85rem; font-weight: 600; color: var(--text-muted, #64748b); text-decoration: none; background: #fff; border: 1px solid var(--border-color, #e2e8f0); border-bottom: none; border-radius: 8px 8px 0 0; transition: all 0.2s ease; white-space: nowrap; }
    .kka-nav-tabs .nav-link:hover { color: var(--bs-blue-dark, #1e3a8a); background-color: #f8fafc; }
    .kka-nav-tabs .nav-link.active { color: #2563eb; background-color: #fff; border-color: var(--border-color, #e2e8f0); border-bottom: 3px solid #2563eb; font-weight: 700; box-shadow: 0 -2px 5px rgba(0,0,0,0.02); }
    .badge-risk-high { background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
    .badge-risk-mod { background-color: #fffbe3; color: #d97706; border: 1px solid #fef3c7; padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
    .badge-risk-low { background-color: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="kka-container">
        <!-- Header Halaman -->
        <div class="page-header">
            <div class="page-header-title">
                <h1 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--bs-blue-dark);">Kertas Kerja Audit (KKA) Offsite</h1>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">Review, verifikasi, dan tindak lanjuti indikasi temuan exception transaksi.</p>
            </div>
        </div>

        @php
            $totalNominal = $items->sum('nominal');
            $highRiskCount = $items->filter(fn($i) => strtolower($i->risk_awal ?? '') == 'high')->count();
            $doneCount = $items->filter(fn($i) => in_array(strtolower($i->status_review ?? ''), ['selesai', 'reviewed', 'closed']))->count();
        @endphp

        <!-- Stat Cards Ringkasan -->
        <div class="grid grid-cols-4">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-file-earmark-text"></i></div>
                <div class="stat-info">
                    <div class="stat-label">TOTAL EXCEPTION</div>
                    <div class="stat-value">{{ $items->total() }}</div>
                    <div class="stat-sub">Sampel Transaksi</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-info">
                    <div class="stat-label">TOTAL NOMINAL</div>
                    <div class="stat-value" style="font-size: 1.1rem;">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
                    <div class="stat-sub">Nilai Transaksi Ditemukan</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon yellow" style="background: #fef2f2; color: #dc2626;"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-info">
                    <div class="stat-label" style="color: #dc2626;">HIGH RISK</div>
                    <div class="stat-value" style="color: #dc2626;">{{ $highRiskCount }}</div>
                    <div class="stat-sub">Perlu Atensi Khusus</div>
                </div>
            </div>

            <div class="stat-card" style="position: relative; display: flex; justify-content: space-between; align-items: flex-start;">
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <div class="stat-icon green">
                        <i class="bi bi-check2-square"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">SELESAI REVIEW</div>
                        <div class="stat-value">{{ $doneCount }}</div>
                        <div class="stat-sub">Sudah Ditindaklanjuti</div>
                    </div>
                </div>
                <a href="{{ route('history.index') }}" class="btn btn-outline-primary" style="font-size: 0.75rem; padding: 0.35rem 0.65rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; font-weight: 600; white-space: nowrap;">
                    <i class="bi bi-clock-history"></i> Riwayat
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="kka-nav-tabs">
            @foreach($availableSheets as $key => $info)
                @php $routeParam = $info['route_param'] ?? str_replace('_', '-', $key); @endphp
                <a class="nav-link {{ $currentSheet == $key ? 'active' : '' }}" 
                   href="{{ route('ra-offsite.kka.index', ['sheet' => $routeParam]) }}">
                    <i class="bi bi-folder2-open"></i> {{ $info['title'] }}
                </a>
            @endforeach
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem;">
                <div>
                    <div class="card-title" style="margin: 0; font-size: 1rem;">{{ $sheetTitle }}</div>
                    <small style="color: var(--text-muted);">Daftar temuan hasil ekstraksi otomatis</small>
                </div>
                <span class="badge badge-gray" style="font-size: 0.8rem; font-weight: 600;">
                    <i class="bi bi-database me-1"></i> {{ $items->total() }} Data
                </span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 3%; text-align: center;">NO</th>
                                <th style="width: 10%;">TANGGAL DATA</th>
                                <th style="width: 9%;">KODE UNIT</th>
                                <th style="width: 11%;">NO REFERENSI</th>
                                <th style="width: 10%;">USER / MAKER</th>
                                <th style="width: 9%;">KODE TRX</th>
                                <th style="width: 12%;">NOMINAL</th>
                                <th>DESKRIPSI / NARASI</th>
                                <th style="width: 9%; text-align: center;">RISK LEVEL</th>
                                <th style="width: 7%; text-align: right;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $index => $item)
                                <tr>
                                    <td style="text-align: center; color: var(--text-muted); font-size: 0.8rem;">
                                        {{ $items->firstItem() + $index }}
                                    </td>
                                    <td style="white-space: nowrap; font-size: 0.82rem;">
                                        {{ isset($item->tanggal_data) ? \Carbon\Carbon::parse($item->tanggal_data)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td style="font-weight: 600; color: var(--bs-blue-dark); font-size: 0.83rem;">
                                        {{ $item->kode_unit ?? '-' }}
                                    </td>
                                    <td>
                                        <span class="badge badge-gray" style="font-family: monospace; font-size: 0.78rem;">
                                            {{ $item->no_referensi ?? $item->case_id ?? '-' }}
                                        </span>
                                    </td>
                                    <td style="font-size: 0.82rem;">
                                        {{ $item->user_maker ?? $item->user_id ?? '-' }}
                                    </td>
                                    <td style="font-size: 0.82rem;">
                                        {{ $item->kode_transaksi ?? $item->kode_trx ?? '-' }}
                                    </td>
                                    <td style="font-weight: 700; color: #0f172a; white-space: nowrap; font-size: 0.85rem;">
                                        Rp {{ number_format($item->nominal ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <span style="font-size: 0.83rem; color: #334155;" title="{{ $item->deskripsi_narasi ?? '' }}">
                                            {{ Str::limit($item->deskripsi_narasi ?? '-', 50) }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        @if(strtolower($item->risk_awal ?? '') == 'high')
                                            <span class="badge-risk-high"><i class="bi bi-exclamation-triangle-fill"></i> High</span>
                                        @elseif(strtolower($item->risk_awal ?? '') == 'moderate')
                                            <span class="badge-risk-mod"><i class="bi bi-exclamation-circle-fill"></i> Moderate</span>
                                        @else
                                            <span class="badge-risk-low"><i class="bi bi-check-circle-fill"></i> Low</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        <button class="btn btn-outline" style="padding: 0.28rem 0.65rem; font-size: 0.75rem; border-radius: 6px;">
                                            Review
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" style="padding: 3rem 1rem;">
                                        <div class="empty-state" style="text-align: center;">
                                            <i class="bi bi-folder-x" style="font-size: 2.5rem; color: var(--text-muted);"></i>
                                            <p style="margin-top: 0.5rem; color: var(--text-muted); font-size: 0.9rem;">Belum ada data exception pada sheet <strong>{{ $sheetTitle }}</strong> ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($items->hasPages())
                @php $activeRouteParam = $availableSheets[$currentSheet]['route_param'] ?? str_replace('_', '-', $currentSheet); @endphp
                <div style="padding: 0.8rem 1.25rem; display: flex; justify-content: flex-end; border-top: 1px solid var(--border-color, #e2e8f0); background: #f8fafc;">
                    {{ $items->appends(['sheet' => $activeRouteParam])->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection