@extends('layouts.app')
@section('title', 'Change Log')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Change Log</h1>
        <p>Audit trail semua perubahan manual pada sistem</p>
    </div>
    <a href="{{ route('final-audit-plan.index') }}" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

@if(session('success'))
<div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Tanggal</th><th>Modul</th><th>Unit</th><th>Perubahan</th><th>Alasan</th><th>Disetujui</th><th>Status</th><th>Oleh</th></tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                @php $stCls = match($log->status) { 'Approved'=>'badge-success','Rejected'=>'badge-danger','Implemented'=>'badge-info',default=>'badge-gray' }; @endphp
                <tr>
                    <td style="font-size:0.78rem;">{{ $log->date?->format('d M Y') }}</td>
                    <td><span class="badge badge-info">{{ $log->sheet_area }}</span></td>
                    <td style="font-size:0.8rem;">{{ $log->unit?->unit_name ?? '-' }}</td>
                    <td style="font-size:0.82rem;max-width:200px;">{{ $log->change_description }}</td>
                    <td style="font-size:0.8rem;color:var(--text-muted);">{{ $log->reason ?? '-' }}</td>
                    <td style="font-size:0.8rem;">{{ $log->approved_by ?? '-' }}</td>
                    <td><span class="badge {{ $stCls }}">{{ $log->status }}</span></td>
                    <td style="font-size:0.78rem;">{{ $log->createdBy?->name ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="8"><div class="empty-state"><i class="bi bi-clock-history"></i><p>Belum ada change log.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem 1.25rem;">{{ $logs->links() }}</div>
</div>
@endsection
