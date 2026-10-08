@extends('layouts.app')
@section('title', 'Detail Cabang - Offsite')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Detail: {{ $namaKC }}</h1>
        <p>Data tingkat risiko dan temuan untuk Kantor Cabang (KC) dan unit di bawahnya.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('offsite.index') }}" class="btn btn-outline btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <div class="badge badge-purple px-3 py-2" style="font-size: 0.85rem;">
            <i class="bi bi-shield-lock-fill me-1"></i> AKSES: {{ strtoupper(auth()->user()->role) }}
        </div>
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
                @forelse($units as $unit)
                <tr>
                    <td style="font-size:0.78rem;color:var(--text-muted);font-weight:600;">
                        {{ $unit['kode_unit'] }}
                    </td>
                    <td>
                        <strong>{{ $unit['nama_unit'] }}</strong>
                        @if(in_array($unit['tipe_unit'], ['KC', 'KCU', 'KP','KCP']))
                            <span class="badge badge-purple ms-2" style="font-size:0.65rem;">Induk</span>
                        @endif
                    </td>
                    
                    <td class="text-center">
                        @if($unit['total_low'] > 0)
                            <span class="badge badge-info shadow-sm">
                                {{ $unit['total_low'] }} Data
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    
                    <td class="text-center">
                        @if($unit['total_moderate'] > 0)
                            <span class="badge badge-warning shadow-sm">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $unit['total_moderate'] }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    
                    <td class="text-center">
                        @if($unit['total_high'] > 0)
                            <span class="badge badge-danger shadow-sm">
                                <i class="bi bi-exclamation-octagon-fill me-1"></i> {{ $unit['total_high'] }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    
                    <td class="text-center">
                        <a href="{{ route('offsite.kka.index', ['kode_unit' => $unit['kode_unit']]) }}" class="btn btn-outline btn-sm shadow-sm">
                            <i class="bi bi-folder2-open me-1"></i> KKA
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            <h6 class="fw-bold">Belum Ada Data Unit</h6>
                            <p class="mb-0 fs-7">Sistem belum merekam data unit untuk ditampilkan.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
