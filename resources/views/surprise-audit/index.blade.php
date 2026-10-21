@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Modul SOP 05 — Surprise Audit</h1>
            <p class="text-muted mb-0">Register & Rekapitulasi Pelaksanaan Audit Mendadak</p>
        </div>
        <a href="{{ route('surprise_audit.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Buat Penugasan Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white fw-bold">
            <i class="fas fa-list me-1"></i> Daftar Penugasan Surprise Audit
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Visit ID</th>
                            <th>No. Assignment</th>
                            <th>Unit Kerja</th>
                            <th>Auditor (RA)</th>
                            <th>Reviewer</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $item)
                            <tr>
                                <td><code>{{ $item->visit_id }}</code></td>
                                <td>{{ $item->nomor_assignment }}</td>
                                <td><strong>{{ $item->kode_unit }}</strong> — {{ $item->nama_unit }}</td>
                                <td>{{ $item->resident_auditor }}</td>
                                <td>{{ $item->reviewer }}</td>
                                <td><span class="badge bg-warning text-dark">{{ $item->status }}</span></td>
                                <td>
                                    <a href="{{ route('surprise_audit.show', $item->id) }}" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-folder-open me-1"></i> Kelola KKA
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada penugasan Surprise Audit. Klik "Buat Penugasan Baru" di atas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection