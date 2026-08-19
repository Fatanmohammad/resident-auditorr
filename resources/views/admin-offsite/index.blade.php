@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">Admin Offsite Review — Daftar Cabang</h2>
        </div>
        <div class="col-md-4">
            <form method="GET" class="d-flex gap-2">
                <input type="number" name="tahun" class="form-control form-control-sm" value="{{ $tahun }}" min="2020" max="2099">
                <select name="bulan" class="form-select form-select-sm">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" @selected($i == $bulan)>
                            {{ \Carbon\Carbon::create(2024, $i)->isoFormat('MMMM') }}
                        </option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            </form>
        </div>
    </div>

    <div class="row">
        @forelse($cabangs as $cabang)
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">{{ $cabang->kode_cabang }} — {{ $cabang->nama_cabang }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col">
                                <div class="text-muted small">Total Unit</div>
                                <div class="h5 mb-0">{{ $cabang->total_unit }}</div>
                            </div>
                            <div class="col">
                                <div class="text-muted small">Perlu Review</div>
                                <div class="h5 mb-0 text-warning">{{ $cabang->unit_perlu_review }}</div>
                            </div>
                            <div class="col">
                                <div class="text-muted small">Selesai Review</div>
                                <div class="h5 mb-0 text-success">{{ $cabang->unit_selesai_review }}</div>
                            </div>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            @php
                                $persen = $cabang->total_unit > 0 ? round($cabang->unit_selesai_review / $cabang->total_unit * 100) : 0;
                            @endphp
                            <div class="progress-bar bg-success" style="width: {{ $persen }}%"></div>
                        </div>
                        <a href="{{ route('admin-offsite.cabang-detail', $cabang->id) }}?tahun={{ $tahun }}&bulan={{ $bulan }}" 
                           class="btn btn-sm btn-primary">
                            Lihat Detail Unit →
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    Tidak ada cabang atau unit aktif untuk periode ini.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
