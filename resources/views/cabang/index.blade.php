@extends('layouts.app')

@section('title', 'Master Data Cabang')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Cabang Bank Sulteng</h2>
        <button class="btn btn-primary" style="width: auto;">
            <i class="bi bi-plus-lg"></i> Tambah Cabang
        </button>
    </div>
    
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Cabang</th>
                    <th>Tipe</th>
                    <th>Induk Cabang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cabangs as $cabang)
                <tr>
                    <td><strong>{{ $cabang->kode_cabang }}</strong></td>
                    <td>{{ $cabang->nama_cabang }}</td>
                    <td>
                        <span class="badge {{ $cabang->tipe == 'kcu' || $cabang->tipe == 'pusat' ? 'badge-info' : 'badge-warning' }}">
                            {{ strtoupper(str_replace('_', ' ', $cabang->tipe)) }}
                        </span>
                    </td>
                    <td>{{ $cabang->parentCabang ? $cabang->parentCabang->nama_cabang : '-' }}</td>
                    <td>
                        <a href="#" style="color: var(--bs-blue); margin-right: 10px;" title="Detail"><i class="bi bi-eye"></i></a>
                        <a href="#" style="color: var(--bs-yellow);" title="Edit"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        Belum ada data cabang.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
