@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="mb-3">
                <a href="{{ route('admin-offsite.index', ['tahun' => $tahun, 'bulan' => $bulan]) }}" class="btn btn-sm btn-secondary">← Kembali ke Daftar Cabang</a>
            </div>
            <h2 class="mb-2">{{ $cabang->cabang_code }} — {{ $cabang->cabang_name }}</h2>
            <p class="text-muted">Periode: {{ \Carbon\Carbon::create($tahun, $bulan)->isoFormat('MMMM Y') }}</p>
        </div>
        <div class="col-md-4">
            <form method="GET" class="d-flex gap-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="perlu_review" @selected(request('status') === 'perlu_review')>Perlu Review</option>
                    <option value="tidak_perlu" @selected(request('status') === 'tidak_perlu')>Tidak Perlu Review</option>
                    <option value="selesai" @selected(request('status') === 'selesai')>Selesai Review</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-sm">
            <thead class="table-light">
                <tr>
                    <th>Kode Unit</th>
                    <th>Nama Unit</th>
                    <th>Jenis Unit</th>
                    <th>Area Risiko</th>
                    <th>Risiko Tertinggi</th>
                    <th>Status Review</th>
                    <th>Upload Terakhir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($unitData as $item)
                    <tr>
                        <td><code>{{ $item['unit']->unit_code }}</code></td>
                        <td>{{ $item['unit']->unit_name }}</td>
                        <td>{{ $item['unit']->unit_type }}</td>
                        <td>
                            <span class="badge bg-info">{{ $item['total_area_risiko'] }}</span>
                        </td>
                        <td>
                            @if($item['risiko_tertinggi'] === 'High')
                                <span class="badge bg-danger">{{ $item['risiko_tertinggi'] }}</span>
                            @elseif(in_array($item['risiko_tertinggi'], ['Moderate', 'Moderate to High']))
                                <span class="badge bg-warning">{{ $item['risiko_tertinggi'] }}</span>
                            @elseif($item['risiko_tertinggi'] === 'Low')
                                <span class="badge bg-success">{{ $item['risiko_tertinggi'] }}</span>
                            @else
                                <span class="badge bg-secondary">{{ $item['risiko_tertinggi'] }}</span>
                            @endif
                        </td>
                        <td>
                            @if(in_array($item['status_review'], ['Perlu Review', 'Dalam Review']))
                                <span class="badge bg-warning text-dark">{{ $item['status_review'] }}</span>
                            @elseif($item['status_review'] === 'Selesai Review')
                                <span class="badge bg-success">{{ $item['status_review'] }}</span>
                            @else
                                <span class="badge bg-secondary">{{ $item['status_review'] }}</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $item['terakhir_upload'] ?? '-' }}</small>
                        </td>
                        <td>
                            <a href="{{ route('admin-offsite.unit-detail', $item['unit']->id) }}?tahun={{ $tahun }}&bulan={{ $bulan }}" 
                               class="btn btn-xs btn-primary">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            Tidak ada unit dengan status yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
