@extends('layouts.app')

@section('content')
<div>
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('admin-offsite.index', ['tahun' => $tahun, 'bulan' => $bulan]) }}" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; background: #fff;">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Cabang
        </a>
    </div>

    <div class="page-header">
        <div class="page-header-title">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-gray" style="font-family: monospace;">{{ $cabang->cabang_code }}</span>
                <span class="badge badge-info">Periode: {{ \Carbon\Carbon::create($tahun, $bulan)->isoFormat('MMMM Y') }}</span>
            </div>
            <h1 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--bs-blue-dark);">{{ $cabang->cabang_name }}</h1>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">Daftar Unit Operasional dan Status Pelaksanaan Offsite Review</p>
        </div>
        <div class="page-header-actions">
            <form method="GET" style="display: flex; align-items: flex-end; gap: 0.5rem;">
                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <label style="font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Status Review</label>
                    <select name="status" class="form-select" style="padding: 0.4rem 2rem 0.4rem 0.75rem; font-size: 0.8rem; width: auto; height: 34px;">
                        <option value="">Semua Status Review</option>
                        <option value="perlu_review" @selected(request('status') === 'perlu_review')>Perlu Review</option>
                        <option value="tidak_perlu" @selected(request('status') === 'tidak_perlu')>Tidak Perlu Review</option>
                        <option value="selesai" @selected(request('status') === 'selesai')>Selesai Review</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-outline" style="height: 34px; padding: 0 1rem; font-size: 0.8rem; background: #fff;">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </form>
        </div>
    </div>

    @php
        $totalUnit = count($unitData);
        $perluReviewCount = collect($unitData)->filter(fn($i) => in_array($i['status_review'], ['Perlu Review', 'Dalam Review']))->count();
        $selesaiCount = collect($unitData)->filter(fn($i) => $i['status_review'] === 'Selesai Review')->count();
        $highRiskCount = collect($unitData)->filter(fn($i) => $i['risiko_tertinggi'] === 'High')->count();
    @endphp

    <div class="grid grid-cols-4" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="bi bi-diagram-3"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">TOTAL UNIT</div>
                <div class="stat-value">{{ $totalUnit }}</div>
                <div class="stat-sub">Terdaftar di Cabang</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow">
                <i class="bi bi-exclamation-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">PERLU REVIEW</div>
                <div class="stat-value">{{ $perluReviewCount }}</div>
                <div class="stat-sub">Menunggu Tindak Lanjut</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">SELESAI REVIEW</div>
                <div class="stat-value">{{ $selesaiCount }}</div>
                <div class="stat-sub">Review Telah Rampung</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="bi bi-shield-exclamation"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">RISIKO TINGGI</div>
                <div class="stat-value">{{ $highRiskCount }}</div>
                <div class="stat-sub">Area Risiko High</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Rincian Unit Operasional</div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">
                Menampilkan {{ $totalUnit }} unit
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">KODE UNIT</th>
                            <th style="width: 20%;">NAMA UNIT</th>
                            <th style="width: 10%;">JENIS UNIT</th>
                            <th style="width: 12%; text-align: center;">AREA RISIKO</th>
                            <th style="width: 13%; text-align: center;">RISIKO TERTINGGI</th>
                            <th style="width: 15%; text-align: center;">STATUS REVIEW</th>
                            <th style="width: 12%;">UPDATE TERAKHIR</th>
                            <th style="width: 8%; text-align: right;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($unitData as $item)
                            <tr>
                                <td>
                                    <span class="badge badge-gray" style="font-family: monospace;">{{ $item['unit']->unit_code }}</span>
                                </td>
                                <td style="font-weight: 600; color: var(--bs-blue-dark);">{{ $item['unit']->unit_name }}</td>
                                <td style="color: var(--text-muted);">{{ $item['unit']->unit_type }}</td>
                                <td style="text-align: center;">
                                    <span class="badge badge-gray">{{ $item['total_area_risiko'] }} Area</span>
                                </td>
                                <td style="text-align: center;">
                                    @if($item['risiko_tertinggi'] === 'High')
                                        <span class="badge badge-danger">High</span>
                                    @elseif(in_array($item['risiko_tertinggi'], ['Moderate', 'Moderate to High']))
                                        <span class="badge badge-warning">{{ $item['risiko_tertinggi'] }}</span>
                                    @elseif(in_array($item['risiko_tertinggi'], ['Low', 'Low to Moderate']))
                                        <span class="badge badge-success">{{ $item['risiko_tertinggi'] }}</span>
                                    @else
                                        <span class="badge badge-gray">{{ $item['risiko_tertinggi'] }}</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if(in_array($item['status_review'], ['Perlu Review', 'Dalam Review']))
                                        <span class="badge badge-warning">{{ $item['status_review'] }}</span>
                                    @elseif($item['status_review'] === 'Selesai Review')
                                        <span class="badge badge-success">{{ $item['status_review'] }}</span>
                                    @else
                                        <span class="badge badge-gray">{{ $item['status_review'] }}</span>
                                    @endif
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.75rem;">
                                    {{ $item['terakhir_update'] ? \Carbon\Carbon::parse($item['terakhir_update'])->isoFormat('D MMM Y, HH:mm') : '-' }}
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ route('admin-offsite.unit-detail', $item['unit']->id) }}?tahun={{ $tahun }}&bulan={{ $bulan }}" 
                                       class="btn btn-outline" style="padding: 0.35rem 0.75rem; font-size: 0.75rem;">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <p>Tidak ada unit dengan kriteria filter yang dipilih.</p>
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
