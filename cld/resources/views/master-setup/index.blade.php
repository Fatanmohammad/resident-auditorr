@extends('layouts.app')
@section('title', 'Pengaturan Modul')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Pengaturan Modul</h1>
        <p>Kelola bobot skoring penilaian risiko (khusus Admin)</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

@if(session('success'))
<div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
@endif

{{-- ===================== BOBOT INDIKATOR ===================== --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div class="card-title"><i class="bi bi-list-nested" style="color:var(--bs-blue);"></i> Bobot Indikator dalam Bidang</div>
    </div>
    <div class="card-body" style="padding-top:0;">
        <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1rem;">
            Bobot ini mengalikan masing-masing angka mentah (raw metric) untuk menghitung skor tiap bidang. Isi nilai antara 0.00 dan 1.00.
        </p>
        <form action="{{ route('master-setup.field-weights') }}" method="POST">
            @csrf
            @foreach($bidangOrder as $key => $label)
            <div style="margin-bottom:1.25rem;">
                <div style="font-weight:600;font-size:0.85rem;color:var(--bs-blue-dark);margin-bottom:0.5rem;">{{ $label }}</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Indikator</th>
                            <th style="width:120px;">Bobot</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($fieldWeights[$key] ?? collect()) as $fw)
                        <tr>
                            <td style="font-size:0.85rem;">{{ $fw->label }}</td>
                            <td>
                                <input type="number" name="weights[{{ $fw->id }}]" class="form-input" value="{{ $fw->weight }}"
                                       min="0" max="1" step="0.01" style="text-align:center;padding:0.35rem 0.5rem;font-size:0.85rem;" required>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" style="font-size:0.8rem;color:var(--text-muted);">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endforeach
            <div class="form-group">
                <label class="form-label">Alasan Perubahan (wajib diisi)</label>
                <input type="text" name="reason" class="form-input" placeholder="Contoh: Penyesuaian bobot sesuai SOP" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Bobot Indikator</button>
        </form>
    </div>
</div>

{{-- ===================== BOBOT BIDANG ===================== --}}
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="bi bi-layers" style="color:var(--bs-blue);"></i> Bobot Bidang ke Skor Final (per Jenis Unit)</div>
    </div>
    <div class="card-body" style="padding-top:0;">
        <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1rem;">
            Bobot ini menggabungkan 6 skor bidang menjadi skor final. Setiap jenis unit punya bobot berbeda. Isi nilai antara 0.00 dan 1.00.
        </p>
        <form action="{{ route('master-setup.bidang-weights') }}" method="POST">
            @csrf
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Jenis Unit</th>
                        <th>Riwayat RA</th>
                        <th>Kas/Teller</th>
                        <th>CS/DPK</th>
                        <th>Kredit</th>
                        <th>TI/ATM</th>
                        <th>Monitoring TL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unitTypeOrder as $type)
                    @php
                        $rows = $bidangWeights[$type] ?? collect();
                        $bidangKeys = ['riwayat_ra','kas_teller','cs_dpk','kredit','ti_atm','monitoring_tl'];
                        $rowById = $rows->keyBy('bidang');
                    @endphp
                    <tr>
                        <td style="font-weight:600;">{{ $type }}</td>
                        @foreach($bidangKeys as $bk)
                        @php $bw = $rowById->get($bk); @endphp
                        <td>
                            @if($bw)
                            <input type="number" name="weights[{{ $bw->id }}]" class="form-input" value="{{ $bw->weight }}"
                                   min="0" max="1" step="0.01" style="text-align:center;padding:0.35rem 0.5rem;font-size:0.85rem;width:80px;" required>
                            @else
                            <span style="color:var(--text-muted);font-size:0.8rem;">-</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label">Alasan Perubahan (wajib diisi)</label>
                <input type="text" name="reason" class="form-input" placeholder="Contoh: Penyesuaian bobot sesuai SOP" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Bobot Bidang</button>
        </form>
    </div>
</div>
@endsection
