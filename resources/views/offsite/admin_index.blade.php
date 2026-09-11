@extends('layouts.app')

@section('content')
<style>
    .rekap-card { border-radius: 12px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .table-custom th { background-color: #f8fafc; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .table-custom td { vertical-align: middle; color: #334155; border-bottom: 1px solid #f1f5f9; padding: 1rem 0.5rem; }
    .table-custom tbody tr:hover { background-color: #f8fafc; transition: all 0.2s; }
    .risk-badge { padding: 0.4rem 0.8rem; border-radius: 8px; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; justify-content: center; min-width: 90px; }
    .risk-low { background-color: #f1f5f9; color: #475569; }
    .risk-mod { background-color: #fef3c7; color: #d97706; }
    .risk-high { background-color: #fee2e2; color: #ef4444; }
    .branch-icon { width: 35px; height: 35px; background: #eff6ff; color: #3b82f6; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
</style>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold" style="color: #0f172a; margin-bottom: 0.2rem;">Dashboard Rekapitulasi Offsite</h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Pantau tingkat risiko dan temuan KKA di seluruh cabang Bank Sulteng.</p>
        </div>
        <div class="badge bg-primary px-3 py-2" style="border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
            <i class="bi bi-shield-lock-fill me-1"></i> AKSES: {{ strtoupper(auth()->user()->role) }}
        </div>
    </div>

    <!-- Data Card -->
    <div class="card rekap-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 5%;">NO</th>
                            <th style="width: 25%;">KANTOR CABANG / WILAYAH</th>
                            <th class="text-center">REGISTER HARIAN (LOW)</th>
                            <th class="text-center">TEMUAN (MODERATE)</th>
                            <th class="text-center">TEMUAN (HIGH RISK)</th>
                            <th class="text-center" style="width: 15%;">TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapCabang as $index => $cabang)
                        <tr>
                            <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="branch-icon me-3 shadow-sm">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold" style="color: #1e293b;">{{ $cabang['nama_cabang'] }}</h6>
                                        <small class="text-muted" style="font-family: monospace;">Kode: {{ $cabang['kode_cabang'] }}</small>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- PERBAIKAN LOGIKA: Menggunakan total_low_all -->
                            <td class="text-center">
                                @if(($cabang['total_low_all'] ?? $cabang['total_low']) > 0)
                                    <span class="risk-badge risk-low shadow-sm">
                                        {{ $cabang['total_low_all'] ?? $cabang['total_low'] }} Data
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            
                            <td class="text-center">
                                @if(($cabang['total_moderate_all'] ?? $cabang['total_moderate']) > 0)
                                    <span class="risk-badge risk-mod shadow-sm">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $cabang['total_moderate_all'] ?? $cabang['total_moderate'] }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            
                            <td class="text-center">
                                @if(($cabang['total_high_all'] ?? $cabang['total_high']) > 0)
                                    <span class="risk-badge risk-high shadow-sm">
                                        <i class="bi bi-exclamation-octagon-fill me-1"></i> {{ $cabang['total_high_all'] ?? $cabang['total_high'] }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            
                            <td class="text-center">
                                <!-- Tombol disesuaikan dengan route lama Anda -->
                                <a href="{{ url('/offsite/kka?cabang=' . $cabang['kode_cabang']) }}" class="btn btn-primary btn-sm px-3 shadow-sm" style="border-radius: 8px; font-weight: 600;">
                                    Buka KKA <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
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
    </div>
</div>
@endsection