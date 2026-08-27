@extends('layouts.app')

@section('content')
<div>
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('admin-offsite.unit-detail', $wp->unit_id) }}?tahun={{ $wp->periode_mulai->format('Y') }}&bulan={{ $wp->periode_mulai->format('n') }}" 
           class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; background: #fff;">
            <i class="bi bi-arrow-left"></i> Kembali ke Detail Unit
        </a>
    </div>

    <div class="page-header">
        <div class="page-header-title">
            <h1 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--bs-blue-dark);">KKA {{ $areaLabel }}</h1>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">{{ $wp->kode_wp }} — {{ $wp->nama_unit }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 5%; text-align: center;">NO</th>
                            <th style="width: 10%;">TANGGAL</th>
                            <th style="width: 18%;">DESKRIPSI / NARASI</th>
                            <th style="width: 10%; text-align: right;">NOMINAL</th>
                            <th style="width: 8%; text-align: center;">RISK AWAL</th>
                            <th style="width: 7%; text-align: center;">DAMPAK</th>
                            <th style="width: 9%; text-align: center;">KEMUNGKINAN</th>
                            <th style="width: 8%; text-align: center;">SKOR RISIKO</th>
                            <th style="width: 9%; text-align: center;">KATEGORI FINAL</th>
                            <th style="width: 10%; text-align: center;">STATUS REVIEW</th>
                            <th style="width: 6%; text-align: right;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $row)
                        <tr>
                            <td style="text-align: center;">{{ $i + 1 }}</td>
                            <td>{{ $row->tanggal_data->format('d/m/Y') }}</td>
                            <td style="color: var(--text-muted); font-size: 0.85rem;">{{ Str::limit($row->deskripsi_narasi, 50) }}</td>
                            <td style="text-align: right; font-family: monospace;">{{ number_format($row->nominal, 0, ',', '.') }}</td>
                            <td style="text-align: center;">
                                @php
                                    $riskClass = match($row->risk_awal) {
                                        'High' => 'badge-danger',
                                        'Moderate' => 'badge-warning',
                                        'Low' => 'badge-success',
                                        default => 'badge-gray'
                                    };
                                @endphp
                                <span class="badge {{ $riskClass }}">
                                    {{ $row->risk_awal ?? '-' }}
                                </span>
                            </td>
                            <td style="text-align: center;">{{ $row->dampak ?? '-' }}</td>
                            <td style="text-align: center;">{{ $row->kemungkinan ?? '-' }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ $row->skor_risiko ?? '-' }}</td>
                            <td style="text-align: center;">
                                @php
                                    $finalClass = match($row->kategori_risiko_final) {
                                        'High' => 'badge-danger',
                                        'Moderate' => 'badge-warning',
                                        'Low' => 'badge-success',
                                        default => 'badge-gray'
                                    };
                                @endphp
                                <span class="badge {{ $finalClass }}">
                                    {{ $row->kategori_risiko_final ?? '-' }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @php
                                    $statusClass = match($row->status_review) {
                                        'Selesai' => 'badge-success',
                                        'Perlu Perbaikan' => 'badge-danger',
                                        'Dalam Proses' => 'badge-warning',
                                        default => 'badge-gray'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $row->status_review ?? 'Belum Review' }}</span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('admin-offsite.kka-show', [$wp->id, $area, $row->kka_id ?? $row->id]) }}" class="btn btn-outline" style="padding: 0.35rem 0.75rem; font-size: 0.75rem;">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Belum ada data KKA untuk area ini.</p>
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