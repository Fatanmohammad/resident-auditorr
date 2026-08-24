@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <a href="{{ route('admin-offsite.unit-detail', $wp->unit_id) }}?tahun={{ $wp->periode_mulai->format('Y') }}&bulan={{ $wp->periode_mulai->format('n') }}" 
       class="btn btn-sm btn-secondary mb-3">← Kembali ke Detail Unit</a>

    <h2 class="mb-1">KKA {{ $areaLabel }}</h2>
    <p class="text-muted">{{ $wp->kode_wp }} — {{ $wp->nama_unit }}</p>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Deskripsi/Narasi</th>
                        <th>Nominal</th>
                        <th>Risk Awal</th>
                        <th>Dampak</th>
                        <th>Kemungkinan</th>
                        <th>Skor Risiko</th>
                        <th>Kategori Final</th>
                        <th>Status Review</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row->tanggal_data->format('d/m/Y') }}</td>
                        <td>{{ Str::limit($row->deskripsi_narasi, 50) }}</td>
                        <td>{{ number_format($row->nominal, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-{{ $row->risk_awal === 'High' ? 'danger' : ($row->risk_awal === 'Moderate' ? 'warning' : 'secondary') }}">
                                {{ $row->risk_awal ?? '-' }}
                            </span>
                        </td>
                        <td>{{ $row->dampak ?? '-' }}</td>
                        <td>{{ $row->kemungkinan ?? '-' }}</td>
                        <td>{{ $row->skor_risiko ?? '-' }}</td>
                        <td>{{ $row->kategori_risiko_final ?? '-' }}</td>
                        <td>{{ $row->status_review ?? 'Belum Review' }}</td>
                        <td>
                            <a href="{{ route('admin-offsite.kka-show', [$wp->id, $area, $row->id]) }}" 
                                class="btn btn-sm btn-primary">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center text-muted py-4">Belum ada data KKA untuk area ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection