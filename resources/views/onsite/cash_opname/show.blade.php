@extends('layouts.app')
@section('content')
@php
    $userRole   = strtolower(auth()->user()->role);
    $isAdmin    = in_array($userRole, ['admin','kabag_ra','kadiv_skai']);
@endphp

<style>
    :root { --primary:#1e3a8a; --border:#e2e8f0; --muted:#64748b; --dark:#1e293b; }
    .stat-box { background:#fff; border:1px solid var(--border); border-radius:10px; padding:1rem 1.25rem; }
    .tbl { width:100%; border-collapse:collapse; font-size:0.82rem; }
    .tbl th { padding:0.65rem 0.75rem; background:#f8fafc; color:var(--muted); font-weight:600; font-size:0.7rem; text-transform:uppercase; border-bottom:1px solid var(--border); text-align:left; }
    .tbl td { padding:0.65rem 0.75rem; border-bottom:1px solid var(--border); vertical-align:middle; }
    .badge-status { padding:0.25rem 0.6rem; border-radius:4px; font-weight:600; font-size:0.75rem; display:inline-block; }
    .bg-sesuai { background:#dcfce7; color:#15803d; }
    .bg-selisih { background:#fee2e2; color:#b91c1c; }
</style>

<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.25rem; flex-wrap:wrap; gap:0.75rem;">
    <div>
        <a href="{{ route('onsite.kka', $visit) }}" style="font-size:0.85rem; color:var(--muted); text-decoration:none;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke KKA Onsite
        </a>
        <h4 style="font-weight:700; color:var(--dark); margin:0.4rem 0 0.2rem;">Berita Acara Cash Opname Khasanah & Rekonsiliasi ATM</h4>
        <p style="color:var(--muted); font-size:0.88rem; margin:0;">
            {{ $visit->kode_unit }} — {{ $visit->nama_unit }} &nbsp;|&nbsp;
            Teller/Pemegang Kas: <strong>{{ $opname->pemegang_kas }}</strong> &nbsp;|&nbsp;
            RA: <strong>{{ $opname->ra_pelaksana }}</strong>
        </p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="font-size:0.88rem;">{{ session('success') }}</div>
@endif

{{-- Stat Card Ringkasan Kas --}}
<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem;">
    <div class="stat-box">
        <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Total Physical Cash</div>
        <div style="font-size:1.4rem; font-weight:700; color:var(--primary);" id="totalFisikKas">Rp 0</div>
    </div>
    <div class="stat-box">
        <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Saldo GL System</div>
        <div style="font-size:1.4rem; font-weight:700; color:var(--dark);">Rp {{ number_format($opname->saldo_gl ?? 0, 0, ',', '.') }}</div>
    </div>
    <div class="stat-box">
        <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Selisih Total</div>
        <div style="font-size:1.4rem; font-weight:700;" id="totalSelisihKas">Rp 0</div>
    </div>
</div>

{{-- Tabel Item Cash Opname --}}
<div style="background:#fff; border:1px solid var(--border); border-radius:10px; overflow:hidden; margin-bottom:1.5rem;">
    <div style="padding:0.875rem 1.25rem; border-bottom:1px solid var(--border); font-weight:700; color:var(--primary); font-size:0.9rem;">
        Daftar Prosedur Khasanah & ATM (SOP 04.1)
    </div>
    <div style="overflow-x:auto;">
        <table class="tbl">
            <thead>
                <tr>
                    <th style="width:3%; text-align:center;">No</th>
                    <th>Sub Area</th>
                    <th>Objek / Jenis Persediaan</th>
                    <th>Pecahan / Standar</th>
                    <th style="text-align:right;">Saldo Buku (Rp)</th>
                    <th style="text-align:right;">Hasil Fisik (Rp)</th>
                    <th style="text-align:right;">Selisih (Rp)</th>
                    <th style="text-align:center;">Status Uji</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $item)
                @php
                    $selisih = ($item->hasil_fisik ?? 0) - ($item->saldo_buku ?? 0);
                @endphp
                <tr>
                    <td style="text-align:center; color:var(--muted);">{{ $i+1 }}</td>
                    <td><span class="badge" style="background:#f1f5f9; color:#475569; padding:0.2rem 0.5rem; border-radius:4px; font-size:0.75rem;">{{ $item->sub_area }}</span></td>
                    <td style="font-weight:600;">{{ $item->objek_lokasi }}</td>
                    <td>{{ $item->pecahan_seri }}</td>
                    <td style="text-align:right;">{{ number_format($item->saldo_buku ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align:right; font-weight:600; color:var(--primary);">
                        <span class="val-fisik" data-val="{{ $item->hasil_fisik ?? 0 }}">
                            {{ number_format($item->hasil_fisik ?? 0, 0, ',', '.') }}
                        </span>
                    </td>
                    <td style="text-align:right; font-weight:700; color: {{ $selisih != 0 ? '#b91c1c' : '#15803d' }};">
                        {{ number_format($selisih, 0, ',', '.') }}
                    </td>
                    <td style="text-align:center;">
                        <span class="badge-status {{ $selisih == 0 ? 'bg-sesuai' : 'bg-selisih' }}">
                            {{ $selisih == 0 ? 'Sesuai' : 'Selisih' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let saldoGL = {{ $opname->saldo_gl ?? 0 }};
        let totalFisik = 0;

        document.querySelectorAll('.val-fisik').forEach(el => {
            totalFisik += parseFloat(el.getAttribute('data-val') || 0);
        });

        let selisih = totalFisik - saldoGL;

        document.getElementById('totalFisikKas').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalFisik);
        document.getElementById('totalSelisihKas').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(selisih);
        document.getElementById('totalSelisihKas').style.color = selisih !== 0 ? '#b91c1c' : '#15803d';
    });
</script>
@endsection