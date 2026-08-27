@extends('layouts.app') {{-- sesuaikan dengan layout utama Anda --}}

@section('content')
<div class="container-fluid py-4">
    <h3 class="mb-4">Kertas Kerja Audit (KKA) Offsite</h3>

    {{-- Navigation Tabs --}}
    <ul class="nav nav-tabs mb-4">
        @foreach($availableSheets as $key => $info)
            <li class="nav-item">
                <a class="nav-link {{ $currentSheet == $key ? 'active fw-bold' : '' }}" 
                   href="{{ route('ra-offsite.kka.index', ['sheet' => $key]) }}">
                    {{ $info['title'] }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $sheetTitle }}</h5>
            <span class="badge bg-primary">Total Exception: {{ $items->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Case ID</th>
                            <th>Tanggal</th>
                            <th>Area Review</th>
                            <th>Deskripsi / Narasi</th>
                            <th>Nominal</th>
                            <th>Risk Level</th>
                            <th>Jenis Exception</th>
                            <th>Status Review</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            <tr>
                                <td>{{ $items->firstItem() + $index }}</td>
                                <td><code>{{ $item->case_id }}</code></td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal_data)->format('d/m/Y') }}</td>
                                <td>{{ $item->area_review }}</td>
                                <td>{{ Str::limit($item->deskripsi_narasi, 50) }}</td>
                                <td>Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                <td>
                                    @if($item->risk_awal == 'High')
                                        <span class="badge bg-danger">High Risk</span>
                                    @elseif($item->risk_awal == 'Moderate')
                                        <span class="badge bg-warning text-dark">Moderate</span>
                                    @else
                                        <span class="badge bg-secondary">Low</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $item->jenis_exception_awal }}</small></td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ $item->status_review }}</span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" title="Review Detail">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    Belum ada data exception/risiko pada sheet ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection