@extends('layouts.app')
@section('title', 'Rekapitulasi Cabang - Offsite')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Dashboard Rekapitulasi Offsite</h1>
        <p>Pantau agregasi tingkat risiko dan temuan KKA di tingkat Kantor Cabang (KC/KCU/KP).</p>
    </div>
    <div class="badge badge-purple px-3 py-2" style="font-size: 0.85rem;">
        <i class="bi bi-shield-lock-fill me-1"></i> AKSES: {{ strtoupper(auth()->user()->role) }}
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%;">Kode</th>
                    <th style="width: 25%;">Kantor Cabang / Wilayah</th>
                    <th class="text-center">Register Harian (Low)</th>
                    <th class="text-center">Temuan (Moderate)</th>
                    <th class="text-center">Temuan (High Risk)</th>
                    <th class="text-center" style="width: 15%;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekapCabang as $cabang)
                <tr>
                    <td style="font-size:0.78rem;color:var(--text-muted);font-weight:600;">
                        {{ $cabang['kode_unit'] }}
                    </td>
                    <td>
                        <strong>{{ $cabang['nama_cabang'] }}</strong>
                    </td>
                    
                    <td class="text-center">
                        @if($cabang['total_low_all'] > 0)
                            <span class="badge badge-info shadow-sm">
                                {{ $cabang['total_low_all'] }} Data
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    
                    <td class="text-center">
                        @if($cabang['total_moderate_all'] > 0)
                            <span class="badge badge-warning shadow-sm">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $cabang['total_moderate_all'] }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    
                    <td class="text-center">
                        @if($cabang['total_high_all'] > 0)
                            <span class="badge badge-danger shadow-sm">
                                <i class="bi bi-exclamation-octagon-fill me-1"></i> {{ $cabang['total_high_all'] }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    
                    <td class="text-center">
                        <a href="{{ route('offsite.detail', $cabang['id']) }}" class="btn btn-outline btn-sm shadow-sm">
                            <i class="bi bi-eye me-1"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            <h6 class="fw-bold">Belum Ada Data Wilayah</h6>
                            <p class="mb-0 fs-7">Sistem belum merekam data cabang untuk ditampilkan.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection