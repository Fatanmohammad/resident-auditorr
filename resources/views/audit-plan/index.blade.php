@extends('layouts.app')
@section('title', 'Penjadwalan Audit RA')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Penjadwalan Audit RA</h1>
        <p>Daftar Audit Plan — approval berjenjang RA → Kabag RA → Kadiv SKAI</p>
    </div>
    @if(in_array(auth()->user()->role, ['kadiv_skai','kabag_ra']))
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
                                'draft'                  => 'badge-gray',
                                'waiting_kabag_approval' => 'badge-warning',
                                'waiting_kadiv_approval' => 'badge-purple',
                                default                  => 'badge-info',
                            };
                            $label = match($plan->status_approval) {
                                'approved'               => 'Approved',
                                'rejected'               => 'Ditolak',
                                'draft'                  => 'Draft',
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
                        {{-- Tombol approval sesuai role & status --}}
                        @if(auth()->user()->role === 'ra' && $plan->status_approval === 'draft')
                        <form action="{{ route('audit-plan.approve', $plan->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="action" value="submit_kabag">
                            <button type="submit" class="btn btn-yellow btn-sm" title="Kirim ke Kabag">
                                <i class="bi bi-send"></i>
                            </button>
                        </form>
                        @elseif(auth()->user()->role === 'kabag_ra' && $plan->status_approval === 'waiting_kabag_approval')
                        <form action="{{ route('audit-plan.approve', $plan->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="action" value="approve_kabag">
                            <button type="submit" class="btn btn-success btn-sm" title="Approve ke Kadiv">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </form>
                        @elseif(auth()->user()->role === 'kadiv_skai' && $plan->status_approval === 'waiting_kadiv_approval')
                        <form action="{{ route('audit-plan.approve', $plan->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="action" value="approve_kadiv">
                            <button type="submit" class="btn btn-success btn-sm" title="Final Approve">
                                <i class="bi bi-check2-all"></i>
                            </button>
                        </form>
                        @endif
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
