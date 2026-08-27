@extends('layouts.app')
@section('title', 'Detail Audit Plan')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Detail Audit Plan</h1>
        <p>{{ $auditPlan->cabang?->nama_cabang }} — {{ $auditPlan->tahun_periode }}</p>
    </div>
    <a href="{{ route('audit-plan.index') }}" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

@php $status = $auditPlan->status_approval; @endphp

{{-- Info Card --}}
<div class="card" style="margin-bottom: 1.25rem; max-width: 640px;">
    <div class="card-header"><div class="card-title">Informasi Audit Plan</div></div>
    <div class="card-body">
        <table style="width:100%; font-size:0.875rem; border-collapse:collapse;">
            <tr><td style="padding:0.5rem 0; color:var(--text-muted); width:40%;">Cabang</td><td><strong>{{ $auditPlan->cabang?->nama_cabang ?? '-' }}</strong></td></tr>
            <tr><td style="padding:0.5rem 0; color:var(--text-muted);">Resident Auditor</td><td>{{ $auditPlan->raUser?->name ?? '-' }}</td></tr>
            <tr><td style="padding:0.5rem 0; color:var(--text-muted);">Tahun Periode</td><td>{{ $auditPlan->tahun_periode }}</td></tr>
            <tr><td style="padding:0.5rem 0; color:var(--text-muted);">Jadwal Mulai</td><td>{{ \Carbon\Carbon::parse($auditPlan->jadwal_mulai)->format('d M Y') }}</td></tr>
            <tr><td style="padding:0.5rem 0; color:var(--text-muted);">Jadwal Selesai</td><td>{{ \Carbon\Carbon::parse($auditPlan->jadwal_selesai)->format('d M Y') }}</td></tr>
            <tr>
                <td style="padding:0.5rem 0; color:var(--text-muted);">Status</td>
                <td>
                    @php
                        $cls = match($status) {
                            'approved'               => 'badge-success',
                            'rejected'               => 'badge-danger',
                            'draft'                  => 'badge-gray',
                            'waiting_kabag_approval' => 'badge-warning',
                            'waiting_kadiv_approval' => 'badge-purple',
                            default                  => 'badge-info',
                        };
                        $label = match($status) {
                            'approved'               => 'Approved',
                            'rejected'               => 'Ditolak',
                            'draft'                  => 'Draft',
                            'waiting_kabag_approval' => 'Menunggu Kabag RA',
                            'waiting_kadiv_approval' => 'Menunggu Kadiv SKAI',
                            default                  => $status,
                        };
                    @endphp
                    <span class="badge {{ $cls }}">{{ $label }}</span>
                </td>
            </tr>
            @if($auditPlan->catatan_revisi)
            <tr>
                <td style="padding:0.5rem 0; color:var(--text-muted);">Catatan Revisi</td>
                <td>
                    <div style="padding:0.5rem 0.75rem; background:#fff7ed; border-left:3px solid #f59e0b; border-radius:0 var(--radius-sm) var(--radius-sm) 0; font-size:0.82rem; color:#92400e;">
                        {{ $auditPlan->catatan_revisi }}
                    </div>
                </td>
            </tr>
            @endif
        </table>

{{-- Tombol Aksi Approval (admin = akses sama seperti kabag_ra) --}}
        @if(in_array(auth()->user()->role, ['kabag_ra','kadiv_skai','admin']))
        <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
            @if(in_array(auth()->user()->role, ['kabag_ra','admin']) && $status === 'waiting_kabag_approval')
            <div style="display: flex; gap: 0.75rem;">
                <form action="{{ route('audit-plan.approve', $auditPlan->id) }}" method="POST" style="flex:1;">
                    @csrf
                    <input type="hidden" name="action" value="approve_kabag">
                    <button type="submit" class="btn btn-primary" style="width:100%;"><i class="bi bi-check-lg"></i> Setujui</button>
                </form>
                <button type="button" class="btn btn-outline" style="flex:1; border-color:#f59e0b; color:#92400e;" onclick="document.getElementById('modalReject').classList.add('open')">
                    <i class="bi bi-x-lg"></i> Tolak
                </button>
            </div>

            @elseif(auth()->user()->role === 'kadiv_skai' && $status === 'waiting_kadiv_approval')
            <div style="display: flex; gap: 0.75rem;">
                <form action="{{ route('audit-plan.approve', $auditPlan->id) }}" method="POST" style="flex:1;">
                    @csrf
                    <input type="hidden" name="action" value="approve_kadiv">
                    <button type="submit" class="btn btn-primary" style="width:100%;"><i class="bi bi-check2-all"></i> Setujui</button>
                </form>
                <button type="button" class="btn btn-outline" style="flex:1; border-color:#f59e0b; color:#92400e;" onclick="document.getElementById('modalReject').classList.add('open')">
                    <i class="bi bi-x-lg"></i> Tolak
                </button>
            </div>

            @endif
        </div>
        @endif
    </div>
</div>

{{-- KKA Table --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">Kartu Kerja Audit (KKA)</div>
        @if(auth()->user()->role === 'ra' && $status === 'approved')
        <a href="{{ route('kka.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah KKA</a>
        @endif
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Bidang Audit</th><th>Sub Bidang</th><th>Tanggal Pemeriksaan</th><th>Temuan</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($auditPlan->kertasKerjaAudits as $kka)
                <tr>
                    <td><strong>{{ $kka->bidang_audit }}</strong></td>
                    <td>{{ $kka->sub_bidang ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($kka->tanggal_pemeriksaan)->format('d M Y') }}</td>
                    <td><span class="badge badge-warning">{{ $kka->temuanAudits->count() }} temuan</span></td>
                    <td>
                        @php $cls = match($kka->status_kka) { 'approved_kadiv'=>'badge-success','reviewed_kabag'=>'badge-info','revisi'=>'badge-danger',default=>'badge-gray' }; @endphp
                        <span class="badge {{ $cls }}">{{ strtoupper(str_replace('_',' ',$kka->status_kka)) }}</span>
                    </td>
                    <td><a href="{{ route('kka.show', $kka->id) }}" class="btn btn-outline btn-sm"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-journal-x"></i><p>Belum ada KKA untuk audit plan ini.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Reject (admin = akses sama seperti kabag_ra) --}}
@if(in_array(auth()->user()->role, ['kabag_ra','kadiv_skai','admin']))
<div class="modal-overlay" id="modalReject">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Tolak Audit Plan</div>
            <button class="modal-close" onclick="document.getElementById('modalReject').classList.remove('open')">&times;</button>
        </div>
        <form action="{{ route('audit-plan.approve', $auditPlan->id) }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="reject">
            <div class="modal-body">
                <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1rem;">Berikan catatan revisi agar PIMSIE dapat memperbaiki Audit Plan.</p>
                <div class="form-group">
                    <label class="form-label">Catatan Revisi <span style="color:#dc2626;">*</span></label>
                    <textarea name="catatan_revisi" class="form-textarea" required placeholder="Tuliskan alasan penolakan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalReject').classList.remove('open')">Batal</button>
                <button type="submit" class="btn btn-danger" style="background:#dc2626; color:#fff;">Tolak Audit Plan</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
