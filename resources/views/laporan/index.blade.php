@extends('layouts.app')
@section('title', 'Laporan Audit')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Laporan Audit</h1>
        <p>Laporan bulanan per cabang dan laporan evaluasi per unit RA</p>
    </div>
</div>

@if(in_array(auth()->user()->role, ['kadiv_skai','kabag_ra']))
<div class="card" style="margin-bottom: 1.25rem; max-width: 560px;">
    <div class="card-header"><div class="card-title">Generate Laporan Baru</div></div>
    <div class="card-body">
        <form action="{{ route('laporan.generate') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Audit Plan</label>
                <select name="audit_plan_id" class="form-select" required>
                    <option value="">-- Pilih Audit Plan --</option>
                    @foreach($auditPlans as $plan)
                    <option value="{{ $plan->id }}">{{ $plan->cabang?->nama_cabang }} — {{ $plan->tahun_periode }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nomor Laporan</label>
                <input type="text" name="nomor_laporan" class="form-input" required placeholder="cth: LAP/SKAI/2025/001">
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-file-earmark-plus"></i> Generate Laporan</button>
        </form>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header"><div class="card-title">Daftar Laporan Audit</div></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nomor Laporan</th>
                    <th>Cabang</th>
                    <th>Periode</th>
                    <th>Status Approval</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporans as $laporan)
                <tr>
                    <td><strong>{{ $laporan->nomor_laporan }}</strong></td>
                    <td>{{ $laporan->auditPlan?->cabang?->nama_cabang ?? '-' }}</td>
                    <td>{{ $laporan->auditPlan?->tahun_periode ?? '-' }}</td>
                    <td>
                        @php
                            $cls = match($laporan->status_approval_laporan) {
                                'approved_kadiv' => 'badge-success',
                                'approved_kabag' => 'badge-info',
                                default          => 'badge-gray',
                            };
                            $label = match($laporan->status_approval_laporan) {
                                'approved_kadiv' => 'Final Approved',
                                'approved_kabag' => 'Approved Kabag',
                                default          => 'Draft',
                            };
                        @endphp
                        <span class="badge {{ $cls }}">{{ $label }}</span>
                    </td>
                    <td>{{ $laporan->created_at->format('d M Y') }}</td>
                    <td style="display: flex; gap: 0.4rem;">
                        @if(auth()->user()->role === 'kabag_ra' && $laporan->status_approval_laporan === 'draft')
                        <form action="{{ route('laporan.approve', $laporan->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status_approval_laporan" value="approved_kabag">
                            <button type="submit" class="btn btn-yellow btn-sm"><i class="bi bi-check-lg"></i> Approve</button>
                        </form>
                        @elseif(auth()->user()->role === 'kadiv_skai' && $laporan->status_approval_laporan === 'approved_kabag')
                        <form action="{{ route('laporan.approve', $laporan->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status_approval_laporan" value="approved_kadiv">
                            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check2-all"></i> Final</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-file-earmark-x"></i><p>Belum ada laporan audit.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
