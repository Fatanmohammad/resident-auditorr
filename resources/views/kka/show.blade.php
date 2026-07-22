@extends('layouts.app')
@section('title', 'Detail KKA')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Detail KKA — {{ $kka->bidang_audit }}</h1>
        <p>{{ $kka->auditPlan?->cabang?->nama_cabang }} | {{ \Carbon\Carbon::parse($kka->tanggal_pemeriksaan)->format('d M Y') }}</p>
    </div>
    <a href="{{ route('kka.index') }}" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="grid grid-cols-2" style="margin-bottom: 1.25rem;">
    <div class="card">
        <div class="card-header"><div class="card-title">Info KKA</div></div>
        <div class="card-body">
            <table style="width: 100%; font-size: 0.875rem; border-collapse: collapse;">
                <tr><td style="padding: 0.5rem 0; color: var(--text-muted); width: 40%;">Cabang</td><td><strong>{{ $kka->auditPlan?->cabang?->nama_cabang ?? '-' }}</strong></td></tr>
                <tr><td style="padding: 0.5rem 0; color: var(--text-muted);">RA</td><td>{{ $kka->auditPlan?->raUser?->name ?? '-' }}</td></tr>
                <tr><td style="padding: 0.5rem 0; color: var(--text-muted);">Bidang</td><td>{{ $kka->bidang_audit }}</td></tr>
                <tr><td style="padding: 0.5rem 0; color: var(--text-muted);">Sub Bidang</td><td>{{ $kka->sub_bidang ?? '-' }}</td></tr>
                <tr><td style="padding: 0.5rem 0; color: var(--text-muted);">Tgl Pemeriksaan</td><td>{{ \Carbon\Carbon::parse($kka->tanggal_pemeriksaan)->format('d M Y') }}</td></tr>
                <tr><td style="padding: 0.5rem 0; color: var(--text-muted);">Sample</td><td>{{ $kka->sample_pemeriksaan ?? '-' }}</td></tr>
                <tr><td style="padding: 0.5rem 0; color: var(--text-muted);">Status</td>
                    <td>
                        @php $cls = match($kka->status_kka) { 'approved_kadiv'=>'badge-success','reviewed_kabag'=>'badge-info','revisi'=>'badge-danger',default=>'badge-gray' }; @endphp
                        <span class="badge {{ $cls }}">{{ strtoupper(str_replace('_',' ',$kka->status_kka)) }}</span>
                    </td>
                </tr>
                @if($kka->catatan_kabag)
                <tr><td style="padding: 0.5rem 0; color: var(--text-muted);">Catatan Kabag</td><td style="color: #92400e;">{{ $kka->catatan_kabag }}</td></tr>
                @endif
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Ringkasan Temuan</div>
            @if(auth()->user()->role === 'ra')
            <a href="{{ route('temuan.create') }}?kka_id={{ $kka->id }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah Temuan</a>
            @endif
        </div>
        <div class="card-body">
            @if($kka->temuanAudits->count() > 0)
            <div class="grid grid-cols-2" style="gap: 0.75rem;">
                @foreach($kka->temuanAudits->groupBy('kategori') as $kat => $items)
                <div style="padding: 0.75rem; background: var(--bg-main); border-radius: var(--radius-md); text-align: center;">
                    <div style="font-size: 1.25rem; font-weight: 700; color: var(--bs-blue-dark);">{{ $items->count() }}</div>
                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ ucfirst($kat) }}</div>
                </div>
                @endforeach
            </div>
            @else
            <div class="empty-state"><i class="bi bi-check-circle"></i><p>Tidak ada temuan pada KKA ini.</p></div>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><div class="card-title">Daftar Temuan</div></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Judul Temuan</th><th>Kategori</th><th>Target Selesai</th><th>Status TL</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($kka->temuanAudits as $temuan)
                <tr>
                    <td><strong>{{ $temuan->judul_temuan }}</strong></td>
                    <td>
                        @php $cls = match($temuan->kategori) { 'signifikan'=>'badge-danger','berulang'=>'badge-warning',default=>'badge-info' }; @endphp
                        <span class="badge {{ $cls }}">{{ ucfirst($temuan->kategori) }}</span>
                    </td>
                    <td>{{ $temuan->target_selesai_tl ? \Carbon\Carbon::parse($temuan->target_selesai_tl)->format('d M Y') : '-' }}</td>
                    <td>
                        @php $tl = $temuan->tindakLanjuts->last(); @endphp
                        @if($tl)
                            @php $cls = match($tl->status_tl) { 'selesai'=>'badge-success','terlambat'=>'badge-danger',default=>'badge-warning' }; @endphp
                            <span class="badge {{ $cls }}">{{ strtoupper(str_replace('_',' ',$tl->status_tl)) }}</span>
                        @else
                            <span class="badge badge-gray">Belum TL</span>
                        @endif
                    </td>
                    <td><a href="{{ route('temuan.show', $temuan->id) }}" class="btn btn-outline btn-sm"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="5"><div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada temuan.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
