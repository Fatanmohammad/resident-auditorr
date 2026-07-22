@extends('layouts.app')

@section('title', 'Rencana Audit (Audit Plan)')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Rencana Audit</h2>
        <button class="btn btn-primary" style="width: auto;">
            <i class="bi bi-plus-lg"></i> Buat Audit Plan
        </button>
    </div>
    
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cabang</th>
                    <th>Tahun Periode</th>
                    <th>Jadwal Pelaksanaan</th>
                    <th>Auditor (RA)</th>
                    <th>Status Approval</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($auditPlans as $plan)
                <tr>
                    <td><strong>{{ $plan->cabang ? $plan->cabang->nama_cabang : '-' }}</strong></td>
                    <td>{{ $plan->tahun_periode }}</td>
                    <td>{{ \Carbon\Carbon::parse($plan->jadwal_mulai)->format('d M Y') }} - {{ \Carbon\Carbon::parse($plan->jadwal_selesai)->format('d M Y') }}</td>
                    <td>{{ $plan->raUser ? $plan->raUser->name : '-' }}</td>
                    <td>
                        @php
                            $badgeClass = 'badge-info';
                            if ($plan->status_approval == 'approved') $badgeClass = 'badge-success';
                            if ($plan->status_approval == 'rejected') $badgeClass = 'badge-warning'; // Usually danger/warning
                            if ($plan->status_approval == 'draft') $badgeClass = 'badge-info';
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ strtoupper(str_replace('_', ' ', $plan->status_approval)) }}
                        </span>
                    </td>
                    <td>
                        <a href="#" style="color: var(--bs-blue); margin-right: 10px;" title="Detail"><i class="bi bi-eye"></i></a>
                        <a href="#" style="color: var(--bs-yellow);" title="Approval"><i class="bi bi-check2-circle"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        Belum ada data Rencana Audit.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
