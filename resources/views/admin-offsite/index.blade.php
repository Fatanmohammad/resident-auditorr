@extends('layouts.app')

@push('styles')
<style>
    .admin-container { display: flex; flex-direction: column; gap: 1.25rem; }
    .badge-status-warning { background-color: #fffbe3; color: #d97706; border: 1px solid #fef3c7; padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
    .badge-status-success { background-color: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
    .badge-status-gray { background-color: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="admin-container">
        <!-- Header Halaman -->
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
            <div class="page-header-title">
                <h1 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--bs-blue-dark);">Admin Offsite Review</h1>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">Pemantauan dan Pengawasan Status Review Offsite Seluruh Kantor Cabang.</p>
            </div>
            
            <div class="page-header-actions">
                <form method="GET" style="display: flex; align-items: flex-end; gap: 0.5rem;">
                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                        <label style="font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Bulan</label>
                        <select name="bulan" class="form-select" style="padding: 0.4rem 2rem 0.4rem 0.75rem; font-size: 0.8rem; width: auto; height: 34px;">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" @selected($i == $bulan)>
                                    {{ \Carbon\Carbon::create(2024, $i)->isoFormat('MMMM') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                        <label style="font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Tahun</label>
                        <input type="number" name="tahun" class="form-input" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; width: 80px; height: 34px;" value="{{ $tahun }}" min="2020" max="2099">
                    </div>
                    <button type="submit" class="btn btn-outline" style="height: 34px; padding: 0 1rem; font-size: 0.8rem; background: #fff; border-radius: 6px; font-weight: 600;">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                </form>
            </div>
        </div>

        @php
            $totalCabang = $cabangs->count();
            $totalUnit = $cabangs->sum('total_unit');
            $totalPerlu = $cabangs->sum('unit_perlu_review');
            $totalSelesai = $cabangs->sum('unit_selesai_review');
            $persenTotal = $totalUnit > 0 ? round(($totalSelesai / $totalUnit) * 100) : 0;
        @endphp

        <!-- Stat Cards Ringkasan -->
        <div class="grid grid-cols-4">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-bank"></i></div>
                <div class="stat-info">
                    <div class="stat-label">TOTAL CABANG</div>
                    <div class="stat-value">{{ $totalCabang }}</div>
                    <div class="stat-sub">Kantor Cabang Induk</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-diagram-3"></i></div>
                <div class="stat-info">
                    <div class="stat-label">TOTAL UNIT</div>
                    <div class="stat-value">{{ $totalUnit }}</div>
                    <div class="stat-sub">Unit Operasional</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon yellow" style="background: #fffbe3; color: #d97706;"><i class="bi bi-exclamation-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-label" style="color: #d97706;">PERLU REVIEW</div>
                    <div class="stat-value" style="color: #d97706;">{{ $totalPerlu }}</div>
                    <div class="stat-sub">Menunggu Tindak Lanjut</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-label">SELESAI REVIEW</div>
                    <div class="stat-value">{{ $totalSelesai }}</div>
                    <div class="stat-sub">Penyelesaian: {{ $persenTotal }}%</div>
                </div>
            </div>
        </div>

        <!-- Data Table Card -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem;">
                <div>
                    <div class="card-title" style="margin: 0; font-size: 1rem;">Daftar Cabang</div>
                    <small style="color: var(--text-muted);">Periode Pemantauan: {{ \Carbon\Carbon::create($tahun, $bulan)->isoFormat('MMMM Y') }}</small>
                </div>
                <span class="badge badge-gray" style="font-size: 0.8rem; font-weight: 600;">
                    <i class="bi bi-bank me-1"></i> {{ $totalCabang }} Cabang
                </span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 12%;">KODE</th>
                                <th>NAMA CABANG</th>
                                <th style="width: 15%; text-align: center;">TOTAL UNIT</th>
                                <th style="width: 18%; text-align: center;">PERLU REVIEW</th>
                                <th style="width: 18%; text-align: center;">SELESAI REVIEW</th>
                                <th style="width: 12%; text-align: right;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cabangs as $cabang)
                                <tr>
                                    <td>
                                        <span class="badge badge-gray" style="font-family: monospace; font-size: 0.8rem;">
                                            {{ $cabang->kode_cabang }}
                                        </span>
                                    </td>
                                    <td style="font-weight: 600; color: var(--bs-blue-dark); font-size: 0.85rem;">
                                        {{ $cabang->nama_cabang }}
                                    </td>
                                    <td style="text-align: center; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                                        {{ $cabang->total_unit }}
                                    </td>
                                    <td style="text-align: center;">
                                        @if($cabang->unit_perlu_review > 0)
                                            <span class="badge-status-warning">
                                                <i class="bi bi-exclamation-triangle-fill"></i> {{ $cabang->unit_perlu_review }} Unit
                                            </span>
                                        @else
                                            <span class="badge-status-gray">0</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        @if($cabang->unit_selesai_review > 0)
                                            <span class="badge-status-success">
                                                <i class="bi bi-check-circle-fill"></i> {{ $cabang->unit_selesai_review }} Unit
                                            </span>
                                        @else
                                            <span class="badge-status-gray">0</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="{{ route('admin-offsite.cabang-detail', $cabang->id) }}?tahun={{ $tahun }}&bulan={{ $bulan }}" class="btn btn-outline" style="padding: 0.28rem 0.65rem; font-size: 0.75rem; border-radius: 6px; font-weight: 600;">
                                            Detail Cabang
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding: 3rem 1rem;">
                                        <div class="empty-state" style="text-align: center;">
                                            <i class="bi bi-inbox" style="font-size: 2.5rem; color: var(--text-muted);"></i>
                                            <p style="margin-top: 0.5rem; color: var(--text-muted); font-size: 0.9rem;">Tidak ada data cabang untuk periode yang dipilih.</p>
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
</div>
@endsection