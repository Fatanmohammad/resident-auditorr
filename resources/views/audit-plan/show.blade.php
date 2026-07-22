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

<div class="grid grid-cols-2" style="margin-bottom: 1.25rem;">
    <div class="card">
        <div class="card-header"><div class="card-title">Informasi Audit Plan</div></div>
        <div class="card-body">
            <table style="width: 100%; font-size: 0.875rem; border-collapse: collapse;">
                <tr><td style="padding: 0.5rem 0; color: var(--text-muted); width: 40%;">Cabang</td><td><strong>{{ $auditPlan->cabang?->nama_cabang ?? '-' }}</strong></td></tr>
                <tr><td style="padding: 0.5rem 0; color: var(--text-muted);">Resident Auditor</td><td>{{ $auditPlan->raUser?->name ?? '-' }}</td></tr>
                <tr><td style="padding: 0.5rem 0; color: var(--text-muted);">Tahun Periode</td><td>{{ $auditPlan->tahun_periode }}</td></tr>
                <tr><td style="padding: 0.5rem 0; color: var(--text-muted);">Jadwal Mulai</td><td>{{ \Carbon\Carbon::parse($auditPlan->jadwal_mulai)->format('d M Y') }}</td></tr>
                <tr><td style="padding: 0.5rem 0; color: var(--text-muted);">Jadwal Selesai</td><td>{{ \Carbon\Carbon::parse($auditPlan->jadwal_selesai)->format('d M Y') }}</td></tr>
                <tr><td style="padding: 0.5rem 0; color: var(--text-muted);">Status</td>
                    <td>
                        @php $cls = match($auditPlan->status_approval) { 'approved'=>'badge-success','rejected'=>'badge-danger','draft'=>'badge-gray',default=>'badge-warning' }; @endphp
                        <span class="badge {{ $cls }}">{{ strtoupper(str_replace('_',' ',$auditPlan->status_approval)) }}</span>
                    </td>
                </tr>
                @if($auditPlan->catatan_revisi)
                <tr><td style="padding: 0.5rem 0; color: var(--text-muted);">Catatan Revisi</td><td style="color: #dc2626;">{{ $auditPlan->catatan_revisi }}</td></tr>
                @endif
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">Alur Approval</div></div>
        <div class="card-body">
            <div class="approval-flow">
                <div class="approval-step {{ in_array($auditPlan->status_approval, ['waiting_kabag_approval','waiting_kadiv_approval','approved']) ? 'done' : ($auditPlan->status_approval === 'draft' ? 'active' : '') }}">
                    <i class="bi bi-person"></i> RA
                </div>
                <span class="approval-arrow"><i class="bi bi-arrow-right"></i></span>
                <div class="approval-step {{ in_array($auditPlan->status_approval, ['waiting_kadiv_approval','approved']) ? 'done' : ($auditPlan->status_approval === 'waiting_kabag_approval' ? 'active' : '') }}">
                    <i class="bi bi-person-check"></i> Kabag RA
                </div>
                <span class="approval-arrow"><i class="bi bi-arrow-right"></i></span>
                <div class="approval-step {{ $auditPlan->status_approval === 'approved' ? 'done' : ($auditPlan->status_approval === 'waiting_kadiv_approval' ? 'active' : '') }}">
                    <i class="bi bi-shield-check"></i> Kadiv SKAI
                </div>
            </div>

            @if($auditPlan->scoringAudit)
            <div style="margin-top: 1rem; padding: 0.75rem; background: #f0fdf4; border-radius: var(--radius-md); border: 1px solid #bbf7d0;">
                <div style="font-size: 0.75rem; color: var(--text-muted);">Skor Akhir</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--bs-blue-dark);">{{ $auditPlan->scoringAudit->skor_akhir }}</div>
                <div style="font-size: 0.8rem; color: #065f46;">{{ $auditPlan->scoringAudit->peringkat_ra }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Kartu Kerja Audit (KKA)</div>
        @if(auth()->user()->role === 'ra')
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
@endsection
