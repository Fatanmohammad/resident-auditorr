@extends('layouts.app')

@section('content')
<div>
    <div class="page-header">
        <div class="page-header-title">
            <h1 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--bs-blue-dark);">Admin Offsite Review</h1>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">Pemantauan dan Pengawasan Status Review Offsite Seluruh Kantor Cabang</p>
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
                <button type="submit" class="btn btn-outline" style="height: 34px; padding: 0 1rem; font-size: 0.8rem; background: #fff;">
                    <i class="bi bi-funnel"></i> Filter
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

    <div class="grid grid-cols-4" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="bi bi-bank"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">TOTAL CABANG</div>
                <div class="stat-value">{{ $totalCabang }}</div>
                <div class="stat-sub">Kantor Cabang Induk</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="bi bi-diagram-3"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">TOTAL UNIT</div>
                <div class="stat-value">{{ $totalUnit }}</div>
                <div class="stat-sub">Unit Operasional</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow">
                <i class="bi bi-exclamation-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">PERLU REVIEW</div>
                <div class="stat-value">{{ $totalPerlu }}</div>
                <div class="stat-sub">Menunggu Tindak Lanjut</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">SELESAI REVIEW</div>
                <div class="stat-value">{{ $totalSelesai }}</div>
                <div class="stat-sub">Penyelesaian: {{ $persenTotal }}%</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Daftar Cabang</div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">
                Periode: {{ \Carbon\Carbon::create($tahun, $bulan)->isoFormat('MMMM Y') }}
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">KODE</th>
                            <th style="width: 25%;">NAMA CABANG</th>
                            <th style="width: 15%; text-align: center;">TOTAL UNIT</th>
                            <th style="width: 15%; text-align: center;">PERLU REVIEW</th>
                            <th style="width: 15%; text-align: center;">SELESAI REVIEW</th>
                            <th style="width: 20%; text-align: right;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cabangs as $cabang)
                            <tr>
                                <td><span class="badge badge-gray" style="font-family: monospace;">{{ $cabang->kode_cabang }}</span></td>
                                <td style="font-weight: 600; color: var(--bs-blue-dark);">{{ $cabang->nama_cabang }}</td>
                                <td style="text-align: center; font-weight: 600;">{{ $cabang->total_unit }}</td>
                                <td style="text-align: center;">
                                    @if($cabang->unit_perlu_review > 0)
                                        <span class="badge badge-warning">{{ $cabang->unit_perlu_review }}</span>
                                    @else
                                        <span class="badge badge-gray">0</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($cabang->unit_selesai_review > 0)
                                        <span class="badge badge-success">{{ $cabang->unit_selesai_review }}</span>
                                    @else
                                        <span class="badge badge-gray">0</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ route('admin-offsite.cabang-detail', $cabang->id) }}?tahun={{ $tahun }}&bulan={{ $bulan }}" class="btn btn-outline" style="padding: 0.35rem 0.75rem; font-size: 0.75rem;">
                                        Detail Cabang
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <p>Tidak ada data cabang untuk periode yang dipilih.</p>
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
