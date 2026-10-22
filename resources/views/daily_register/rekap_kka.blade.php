@extends('layouts.app')
@section('title', 'Rekap KKA Fisik - Daily Register')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Dashboard Rekapitulasi KKA Fisik Cabang</h1>
        <p>Daftar 5 KKA Fisik Harian (Cash Opname, Observasi Lingkungan, Stok Opname, Rekon Kartu ATM, Rekon Barang Cetakan) yang telah disubmit oleh Resident Auditor (RA).</p>
    </div>
    <div class="badge badge-purple px-3 py-2" style="font-size: 0.85rem;">
        <i class="bi bi-shield-lock-fill me-1"></i> AKSES: {{ strtoupper(auth()->user()->role) }}
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Tanggal Pemeriksaan</th>
                    <th style="width: 30%;">Kode & Nama Cabang (KC)</th>
                    <th>Nama RA</th>
                    <th class="text-center">Status KKA</th>
                    <th class="text-center" style="width: 15%;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kkas as $kka)
                <tr>
                    <td style="font-size:0.85rem; color:var(--text-muted); font-weight:500;">
                        {{ $kka->tanggal_pemeriksaan ? \Carbon\Carbon::parse($kka->tanggal_pemeriksaan)->translatedFormat('d F Y') : '-' }}
                    </td>
                    <td>
                        <strong style="color:var(--primary-color);">{{ $kka->kode_unit }}</strong> - {{ $kka->nama_cabang }}
                    </td>
                    <td>
                        <strong>{{ $kka->user ? $kka->user->name : 'N/A' }}</strong>
                    </td>
                    <td class="text-center">
                        @php
                            $badgeClass = 'background-color: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;'; // abu-abu untuk Belum Submit
                            if(str_contains(strtolower($kka->status_final), 'final') || str_contains(strtolower($kka->status_final), 'pending')) {
                                $badgeClass = 'background-color: #fffbeb; color: #d97706; border: 1px solid #fde68a;'; // kuning/orange
                            }
                            if(str_contains(strtolower($kka->status_final), 'approved')) {
                                $badgeClass = 'background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;'; // hijau
                            }
                            if(str_contains(strtolower($kka->status_final), 'revisi')) {
                                $badgeClass = 'background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;'; // merah
                            }
                        @endphp
                        <span class="badge shadow-sm px-3 py-2" style="{{ $badgeClass }} font-weight: 600;">
                            {{ strtoupper($kka->status_final) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($kka->id)
                            <a href="{{ route('daily-register.physical-kka.index', $kka->id) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-search"></i> Review
                            </a>
                        @else
                            <button class="btn btn-sm btn-secondary" disabled>
                                <i class="bi bi-x-circle"></i> Kosong
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted" style="background-color: var(--bg-color);">
                        <i class="bi bi-inbox" style="font-size: 2.5rem; display: block; margin-bottom: 0.5rem; color: var(--text-muted);"></i>
                        Belum ada pengajuan KKA Fisik Harian dari Cabang.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
