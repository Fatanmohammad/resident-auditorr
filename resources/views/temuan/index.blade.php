@extends('layouts.app')
@section('title', 'Temuan Audit')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Temuan Audit</h1>
        <p>Daftar temuan dari seluruh pelaksanaan audit</p>
    </div>
    @if(auth()->user()->role === 'ra')
    <a href="{{ route('temuan.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Catat Temuan</a>
    @endif
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cabang</th>
                    <th>Bidang Audit</th>
                    <th>Judul Temuan</th>
                    <th>Kategori</th>
                    <th>Target Selesai</th>
                    <th>Status TL</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($temuans as $temuan)
                <tr>
                    <td><strong>{{ $temuan->kka?->auditPlan?->cabang?->nama_cabang ?? '-' }}</strong></td>
                    <td>{{ $temuan->kka?->bidang_audit ?? '-' }}</td>
                    <td>{{ $temuan->judul_temuan }}</td>
                    <td>
                        @php $cls = match($temuan->kategori) { 'signifikan'=>'badge-danger','berulang'=>'badge-warning','operasional'=>'badge-info','kepatuhan'=>'badge-purple',default=>'badge-gray' }; @endphp
                        <span class="badge {{ $cls }}">{{ ucfirst($temuan->kategori) }}</span>
                    </td>
                    <td>{{ $temuan->target_selesai_tl ? \Carbon\Carbon::parse($temuan->target_selesai_tl)->format('d M Y') : '-' }}</td>
                    <td>
                        @php $tl = $temuan->tindakLanjuts->last(); @endphp
                        @if($tl)
                            @php $cls = match($tl->status_tl) { 'selesai'=>'badge-success','terlambat'=>'badge-danger','proses_tl'=>'badge-warning',default=>'badge-gray' }; @endphp
                            <span class="badge {{ $cls }}">{{ strtoupper(str_replace('_',' ',$tl->status_tl)) }}</span>
                        @else
                            <span class="badge badge-gray">Belum TL</span>
                        @endif
                    </td>
                    <td><a href="{{ route('temuan.show', $temuan->id) }}" class="btn btn-outline btn-sm"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-check-circle"></i><p>Belum ada temuan audit.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
