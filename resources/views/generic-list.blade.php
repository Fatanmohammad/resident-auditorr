@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">{{ $title }}</h2>
        <button class="btn btn-primary" style="width: auto;">
            <i class="bi bi-plus-lg"></i> Tambah Data
        </button>
    </div>
    
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    @foreach($columns as $col)
                    <th>{{ $col }}</th>
                    @endforeach
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    @foreach($fields as $field)
                    <td>
                        @php
                            $value = data_get($row, $field);
                        @endphp
                        @if(is_string($value) && in_array(strtolower($value), ['draft', 'approved', 'rejected', 'selesai', 'revisi']))
                            <span class="badge {{ $value == 'approved' || $value == 'selesai' ? 'badge-success' : 'badge-warning' }}">{{ strtoupper($value) }}</span>
                        @else
                            {{ $value ?? '-' }}
                        @endif
                    </td>
                    @endforeach
                    <td>
                        <a href="#" style="color: var(--bs-blue); margin-right: 10px;" title="Detail"><i class="bi bi-eye"></i></a>
                        <a href="#" style="color: var(--bs-yellow);" title="Edit"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($columns) + 1 }}" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        Belum ada data untuk ditampilkan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
