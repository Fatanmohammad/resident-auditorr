@extends('layouts.app')
@section('title', 'Riwayat Upload Offsite')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Riwayat Upload Data Offsite</h1>
        <p>Jejak upload CSV harian — tetap tersimpan walau data Register sudah tertimpa upload berikutnya.</p>
    </div>
    <a href="{{ route('offsite.register.index') }}" class="btn btn-outline btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali ke Register
    </a>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-body" style="padding:1rem 1.25rem;">
        <form method="GET" action="{{ route('offsite.history.index') }}" style="display:flex; gap:0.75rem; align-items:end; flex-wrap:wrap;">
            <div>
                <label class="form-label" style="font-size:0.75rem;">Tanggal (harian)</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-input">
            </div>
            <div>
                <label class="form-label" style="font-size:0.75rem;">Bulan</label>
                <select name="bulan" class="form-select">
                    <option value="">-- Semua Bulan --</option>
                    @foreach(range(1,12) as $b)
                    <option value="{{ $b }}" {{ request('bulan') == $b ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $b)->isoFormat('MMMM') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" style="font-size:0.75rem;">Tahun</label>
                <input type="number" name="tahun" value="{{ request('tahun', date('Y')) }}" class="form-input" style="width:100px;">
            </div>
            <div>
                <label class="form-label" style="font-size:0.75rem;">Status</label>
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="Berhasil" {{ request('status') == 'Berhasil' ? 'selected' : '' }}>Berhasil</option>
                    <option value="Gagal" {{ request('status') == 'Gagal' ? 'selected' : '' }}>Gagal</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-filter"></i> Filter</button>
            <a href="{{ route('offsite.history.index') }}" class="btn btn-outline btn-sm">Reset</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="bi bi-clock-history me-2 text-muted"></i>Daftar Riwayat Upload</div>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>WAKTU UPLOAD</th>
                    <th>DIUPLOAD OLEH</th>
                    <th>KODE UNIT</th>
                    <th>JENIS FILE</th>
                    <th>NAMA FILE</th>
                    <th style="text-align:center;">STATUS</th>
                    <th style="text-align:right;">LOW</th>
                    <th style="text-align:right;">MODERATE</th>
                    <th style="text-align:right;">HIGH</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="white-space:nowrap; font-size:0.82rem;">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $log->user->name ?? '-' }}</td>
                    <td><span class="badge badge-gray" style="font-family:monospace;">{{ $log->kode_unit }}</span></td>
                    <td><span class="badge badge-info">{{ $log->jenis_file }}</span></td>
                    <td style="font-size:0.82rem; color:var(--text-muted);">{{ $log->nama_file }}</td>
                    <td style="text-align:center;">
                        @if($log->status === 'Berhasil')
                            <span class="badge badge-success">Berhasil</span>
                        @elseif($log->status === 'Gagal')
                            <span class="badge badge-danger">Gagal</span>
                        @else
                            <span class="badge badge-gray">{{ $log->status }}</span>
                        @endif
                    </td>
                    <td style="text-align:right; font-family:monospace;">{{ $log->total_low }}</td>
                    <td style="text-align:right; font-family:monospace;">{{ $log->total_moderate }}</td>
                    <td style="text-align:right; font-family:monospace;">{{ $log->total_high }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>Belum ada riwayat upload untuk filter ini.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div style="padding:0.8rem 1.25rem; display:flex; justify-content:flex-end; border-top:1px solid var(--border-color); background:#f8fafc;">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection