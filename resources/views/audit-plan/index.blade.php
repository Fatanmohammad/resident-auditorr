@extends('layouts.app')
@section('title', 'Penjadwalan Audit RA')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Penjadwalan Audit RA</h1>
        <p>Daftar Audit Plan — approval berjenjang Kabag RA → Kadiv SKAI</p>
    </div>
    @if(auth()->user()->role === 'pimsie')
    <a href="{{ route('audit-plan.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Buat Audit Plan
    </a>
    @endif
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cabang</th>
                    <th>RA</th>
                    <th>Periode</th>
                    <th>Jadwal</th>
                    <th>Status Approval</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditPlans as $plan)
                <tr>
                    <td><strong>{{ $plan->cabang?->nama_cabang ?? '-' }}</strong></td>
                    <td>{{ $plan->raUser?->name ?? '-' }}</td>
                    <td>{{ $plan->tahun_periode }}</td>
                    <td style="font-size: 0.78rem;">
                        {{ \Carbon\Carbon::parse($plan->jadwal_mulai)->format('d M Y') }}<br>
                        <span style="color: var(--text-muted);">s/d {{ \Carbon\Carbon::parse($plan->jadwal_selesai)->format('d M Y') }}</span>
                    </td>
                    <td>
                        @php
                            $cls = match($plan->status_approval) {
                                'approved'               => 'badge-success',
                                'rejected'               => 'badge-danger',
                                'waiting_kabag_approval' => 'badge-warning',
                                'waiting_kadiv_approval' => 'badge-purple',
                                default                  => 'badge-info',
                            };
                            $label = match($plan->status_approval) {
                                'approved'               => 'Approved',
                                'rejected'               => 'Ditolak',
                                'waiting_kabag_approval' => 'Menunggu Kabag',
                                'waiting_kadiv_approval' => 'Menunggu Kadiv',
                                default                  => $plan->status_approval,
                            };
                        @endphp
                        <span class="badge {{ $cls }}">{{ $label }}</span>
                    </td>
                    <td>
                        <a href="{{ route('audit-plan.show', $plan->id) }}" class="btn btn-outline btn-sm" title="Detail">
                            <i class="bi bi-eye"></i>
                        </a>

                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-calendar-x"></i><p>Belum ada Audit Plan.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
