@extends('layouts.app')
@section('title', 'Input Raw Metrics')

@section('content')
<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;">
    <div class="page-header-title">
        <h1>Input & Sinkronisasi Raw Metrics</h1>
        <p>Kelola data mentah (raw metrics) untuk perhitungan skor risiko audit universe.</p>
    </div>
    <div style="display:flex;gap:0.75rem;align-items:center;">
        <form method="GET" action="{{ route('raw-metrics.index') }}" style="display:flex;gap:0.5rem;align-items:center;">
            <label style="font-size:0.85rem;font-weight:600;margin:0;">Periode:</label>
            <select name="period" class="form-select form-select-sm" onchange="this.form.submit()" style="padding:0.35rem 0.65rem;border-radius:6px;border:1px solid #cbd5e1;">
                @foreach([date('Y')-1, date('Y'), date('Y')+1] as $p)
                <option value="{{ $p }}" {{ $period == $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
        </form>

        <form action="{{ route('raw-metrics.sync-all') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin melakukan sinkronisasi otomatis untuk SELURUH unit aktif pada periode {{ $period }}? Skor risiko dan jadwal audit seluruh unit akan dihitung ulang secara otomatis.');">
            @csrf
            <input type="hidden" name="period" value="{{ $period }}">
            <button type="submit" class="btn btn-warning" style="font-weight:600;">
                <i class="bi bi-lightning-charge-fill"></i> Sinkronisasi Otomatis Seluruh Unit
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1.25rem;">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-error" style="margin-bottom:1.25rem;">
    <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
</div>
@endif

<div class="card" style="margin-bottom:1.25rem;border-left:4px solid #0284c7;background:linear-gradient(135deg, rgba(240,249,255,0.9), rgba(255,255,255,0.9));">
    <div class="card-body" style="padding:1rem 1.25rem;">
        <div style="display:flex;align-items:center;gap:0.85rem;">
            <div style="font-size:1.5rem;color:#0284c7;"><i class="bi bi-info-circle-fill"></i></div>
            <div style="font-size:0.85rem;color:#334155;line-height:1.45;">
                Tombol <strong>Sinkronisasi Otomatis</strong> akan menarik seluruh data operasional dari <strong>SOP 01–SOP 09, Daily Register, KKA Onsite, KKA Offsite, dan Register Tindak Lanjut</strong> secara langsung, lalu menghitung otomatis <strong>Skor Risiko</strong> dan <strong>Jadwal Audit Plan</strong> setiap unit.
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <div class="card-title">Daftar Unit Kerja — Periode <strong>{{ $period }}</strong></div>
        <span class="badge badge-info">{{ $units->count() }} Unit Kerja</span>
    </div>
    <div class="card-body" style="padding:0;">
        @if($units->isEmpty())
            <div style="padding:1.5rem;" class="alert alert-info"><i class="bi bi-info-circle"></i> Tidak ada unit yang dapat diinput pada wilayah Anda.</div>
        @else
        <div class="table-wrapper" style="margin:0;">
            <table class="table table-striped" style="margin-bottom:0;">
                <thead>
                    <tr>
                        <th style="width:100px;">Kode</th>
                        <th>Nama Unit</th>
                        <th>Jenis Unit</th>
                        <th>Status Data</th>
                        <th class="text-right" style="width:230px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($units as $unit)
                    @php
                        $hasData = $unit->raw_metrics_count > 0;
                    @endphp
                    <tr>
                        <td style="font-size:0.82rem;font-weight:600;color:var(--text-muted);">{{ $unit->unit_code }}</td>
                        <td><strong>{{ $unit->unit_name }}</strong></td>
                        <td><span class="badge badge-info">{{ $unit->unit_type }}</span></td>
                        <td>
                            @if($hasData)
                                <span class="badge badge-success"><i class="bi bi-check-circle"></i> Sudah Terisi</span>
                            @else
                                <span class="badge badge-secondary"><i class="bi bi-hourglass-split"></i> Belum Terisi</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div style="display:inline-flex;gap:0.4rem;align-items:center;">
                                <form action="{{ route('raw-metrics.sync-unit', $unit) }}" method="POST" style="margin:0;">
                                    @csrf
                                    <input type="hidden" name="period" value="{{ $period }}">
                                    <button type="submit" class="btn btn-sm btn-outline" title="Sinkronkan otomatis unit ini dari database">
                                        <i class="bi bi-lightning-charge"></i> Auto-Sync
                                    </button>
                                </form>
                                <a href="{{ route('raw-metrics.create', ['unit' => $unit, 'period' => $period]) }}" class="btn btn-sm btn-primary" title="Buka form untuk review & sesuaikan nilai">
                                    <i class="bi bi-pencil-square"></i> Input / Review
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
