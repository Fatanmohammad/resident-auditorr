@extends('layouts.app')
@section('title', 'Change Log')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Change Log</h1>
        <p>SOP01-WP11 — Audit trail semua perubahan parameter, mapping, override, dan alasan persetujuan</p>
    </div>
    <a href="{{ route('final-audit-plan.index') }}" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-body">
        <form method="GET" style="display:flex;gap:0.75rem;align-items:flex-end;flex-wrap:wrap;">
            <div class="form-group" style="margin:0;">
                <label class="form-label">Sheet/Area</label>
                <select name="area" class="form-select" style="min-width:180px;">
                    <option value="">Semua Area</option>
                    @foreach($areas as $a)
                    <option value="{{ $a }}" {{ $area == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" style="min-width:140px;">
                    <option value="">Semua Status</option>
                    @foreach(['Draft','Approved','Rejected','Implemented'] as $s)
                    <option value="{{ $s }}" {{ $status == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
            @if($area || $status)
            <a href="{{ route('final-audit-plan.change-log') }}" class="btn btn-outline">Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:45px;">No</th>
                    <th>Tanggal</th>
                    <th>Sheet/Area</th>
                    <th>Unit</th>
                    <th>Perubahan</th>
                    <th>Alasan</th>
                    <th>Disetujui Oleh</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $i => $log)
                @php
                    $stCls = match($log->status) {
                        'Approved'=>'badge-success',
                        'Rejected'=>'badge-danger',
                        'Implemented'=>'badge-info',
                        default=>'badge-gray'
                    };
                @endphp
                <tr>
                    <td style="text-align:center;color:var(--text-muted);">{{ $logs->firstItem() + $i }}</td>
                    <td style="font-size:0.78rem;">{{ $log->date?->format('d M Y') }}</td>
                    <td><span class="badge badge-info">{{ $log->sheet_area }}</span></td>
                    <td style="font-size:0.8rem;">{{ $log->unit?->unit_name ?? '-' }}</td>
                    <td style="font-size:0.82rem;max-width:220px;">{{ $log->change_description }}</td>
                    <td style="font-size:0.8rem;color:var(--text-muted);max-width:180px;">{{ $log->reason ?? '-' }}</td>
                    <td style="font-size:0.8rem;">{{ $log->approved_by ?? '-' }}</td>
                    <td><span class="badge {{ $stCls }}">{{ $log->status }}</span></td>
                </tr>
                @empty
                <tr><td colspan="8"><div class="empty-state"><i class="bi bi-clock-history"></i><p>Belum ada change log.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem 1.25rem;">{{ $logs->withQueryString()->links() }}</div>
</div>
@endsection
