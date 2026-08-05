@extends('layouts.app')
@section('title', 'Input Raw Metrics')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Input Raw Metrics</h1>
        <p>Pilih unit pada wilayah Anda untuk menginput data mentah (raw metrics).</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Daftar Unit — <strong>{{ $period }}</strong></div>
    </div>
    <div class="card-body">
        @if($units->isEmpty())
            <div class="alert alert-info"><i class="bi bi-info-circle"></i> Tidak ada unit yang dapat diinput pada wilayah Anda.</div>
        @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Unit</th>
                    <th>Jenis</th>
                    <th>Status Input</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($units as $unit)
                <tr>
                    <td>{{ $unit->unit_code }}</td>
                    <td>{{ $unit->unit_name }}</td>
                    <td>{{ $unit->unit_type }}</td>
                    <td>
                        @if($unit->raw_metrics_count > 0)
                            <span class="badge badge-success">Sudah diinput</span>
                        @else
                            <span class="badge badge-secondary">Belum diinput</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <a href="{{ route('raw-metrics.create', $unit) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil-square"></i> Input
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
