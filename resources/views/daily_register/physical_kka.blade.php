@extends('layouts.app')

@section('content')
<style>
    :root {
        --bg-body: #f8fafc;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --primary-blue: #1e3a8a;
    }

    /* Page Header */
    .page-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 0.25rem;
    }
    .page-subtitle {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 0;
    }

    /* Modern Soft Badges */
    .badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.65rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 6px;
        line-height: 1.2;
        white-space: nowrap;
    }
    .badge-soft-primary   { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    .badge-soft-success   { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .badge-soft-danger    { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-soft-warning   { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .badge-soft-secondary { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }
    .badge-soft-info      { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }

    /* Summary Card & Modern Table */
    .summary-card {
        background: #ffffff;
        border: 1px solid var(--border-color) !important;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .summary-card-header {
        background: #ffffff;
        border-bottom: 1px solid var(--border-color);
        padding: 1rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .summary-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #eff6ff;
        color: var(--primary-blue);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .table-summary {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
        font-size: 0.84rem;
    }
    .table-summary th {
        background-color: #f8fafc;
        color: var(--text-muted);
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.75rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        border-top: none;
        vertical-align: middle;
        white-space: nowrap;
    }
    .table-summary td {
        padding: 0.75rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
        border-top: none;
        color: var(--text-dark);
        background: #ffffff;
        transition: background-color 0.15s ease;
    }
    .table-summary tbody tr:hover td {
        background-color: #f8fafc;
    }

    /* Monospace Code Pill */
    .sheet-code {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 0.74rem;
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        padding: 0.18rem 0.45rem;
        display: inline-block;
    }

    /* 5 Tabs Navigation - Harmonized with kka-tabs */
    .kka-nav-tabs {
        display: flex;
        gap: 0.25rem;
        overflow-x: auto;
        padding: 0.5rem 0.75rem 0 0.75rem;
        margin-bottom: 0;
        border-bottom: 1px solid var(--border-color);
        background: #ffffff;
        list-style: none;
        flex-wrap: nowrap;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
    .kka-nav-tabs::-webkit-scrollbar {
        height: 3px;
    }
    .kka-nav-tabs::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .kka-nav-tabs .nav-item {
        margin-bottom: -1px;
    }
    .kka-tab-btn {
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 0.65rem 0.85rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-muted);
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        border-radius: 6px 6px 0 0;
        outline: none !important;
        box-shadow: none !important;
    }
    .kka-tab-btn:hover {
        color: var(--primary-blue);
        background: #f8fafc;
    }
    .kka-tab-btn.active {
        color: var(--primary-blue);
        border-bottom: 3px solid var(--primary-blue);
        font-weight: 700;
        background: transparent;
    }

    /* Mini status badge on tab */
    .tab-status-pill {
        font-size: 0.68rem;
        padding: 0.15rem 0.5rem;
        border-radius: 20px;
        font-weight: 600;
        letter-spacing: 0.2px;
        line-height: 1.2;
        white-space: nowrap;
    }

    /* Excel Table Inner Details */
    .table-excel th { background-color: #f8fafc; font-size: 0.74rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.3px; text-align: center; vertical-align: middle; white-space: nowrap; border: 1px solid var(--border-color); padding: 0.6rem 0.5rem; }
    .table-excel td { font-size: 0.83rem; vertical-align: middle; padding: 0.4rem; border: 1px solid var(--border-color); }
    .table-excel input, .table-excel select, .table-excel textarea { font-size: 0.83rem; padding: 0.3rem 0.5rem; border-radius: 6px; }
    .col-disabled { background-color: #f8fafc; }
</style>

<div class="container-fluid py-4">

    <!-- Header & Target Info -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <a href="{{ url('/daily-register/kka') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" style="border-radius: 8px; font-weight: 500; font-size: 0.8rem; padding: 0.3rem 0.7rem; border-color: var(--border-color); background: #fff; color: var(--text-dark);">
                    <i class="bi bi-arrow-left"></i> Kembali ke Register Harian
                </a>
                <span class="badge-soft badge-soft-secondary" style="font-size: 0.72rem;">
                    <i class="bi bi-building me-1 text-primary"></i> Unit: {{ $kodeUnit ?? '000' }}
                </span>
            </div>
            <h4 class="page-title">KKA Fisik Harian</h4>
            <p class="page-subtitle">Pemeriksaan fisik mingguan 5 KKA lapangan sesuai SOP 04.2 &amp; Parameter SOP 02. Area abu-abu otomatis terkunci hingga terjadi Exception.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge-soft {{ $isAdmin ? 'badge-soft-warning' : 'badge-soft-primary' }} px-3 py-2" style="font-size: 0.8rem;">
                <i class="bi {{ $isAdmin ? 'bi-shield-check' : 'bi-person-badge' }} me-1"></i>
                {{ $isAdmin ? 'Mode: Reviewer / Admin (Read-Only RA + Editable Review)' : 'Mode: Input Lapangan (Resident Auditor)' }}
            </span>
        </div>
    </div>

    <!-- FORM UTAMA -->
    <form id="formKkaFisik" action="{{ $isAdmin ? route('daily-register.physical-kka.review') : route('daily-register.physical-kka.submit') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        @if(isset($id))
            <input type="hidden" name="physical_kka_id" value="{{ $id }}">
        @endif

@php
    $schedMap = [];
    if (isset($kontrolMingguan)) {
        foreach ($kontrolMingguan as $sItem) {
            $schedMap[$sItem->area_key] = $sItem;
        }
    }
    $cashSched = $schedMap['cash_opname'] ?? null;
    $obsSched  = $schedMap['observasi_lingkungan'] ?? null;
    $stokSched = $schedMap['rekon_stok_cek_bg'] ?? null;
    $atmSched  = $schedMap['rekon_kartu_atm'] ?? null;
    $bctSched  = $schedMap['rekon_barang_cetakan'] ?? null;

    $hariNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];

    $renderStatusBadge = function($status) {
        $st = strtoupper($status ?? 'TERJADWAL');
        if (str_contains($st, 'SELESAI')) {
            return '<span class="badge-soft badge-soft-success"><i class="bi bi-check-circle-fill"></i> Selesai Minggu Ini</span>';
        } elseif (str_contains($st, 'WAJIB') || str_contains($st, 'HARI INI')) {
            return '<span class="badge-soft badge-soft-warning"><i class="bi bi-exclamation-circle-fill"></i> Wajib Hari Ini</span>';
        } elseif (str_contains($st, 'TERLAMBAT')) {
            return '<span class="badge-soft badge-soft-danger"><i class="bi bi-x-circle-fill"></i> Terlambat</span>';
        } else {
            return '<span class="badge-soft badge-soft-info"><i class="bi bi-calendar3"></i> Terjadwal</span>';
        }
    };

    $renderTabPill = function($status) {
        $st = strtoupper($status ?? 'TERJADWAL');
        if (str_contains($st, 'SELESAI')) {
            return '<span class="tab-status-pill badge-soft-success">Selesai</span>';
        } elseif (str_contains($st, 'WAJIB') || str_contains($st, 'HARI INI')) {
            return '<span class="tab-status-pill badge-soft-warning">Wajib Hari Ini</span>';
        } elseif (str_contains($st, 'TERLAMBAT')) {
            return '<span class="tab-status-pill badge-soft-danger">Terlambat</span>';
        } else {
            return '<span class="tab-status-pill badge-soft-info">Terjadwal</span>';
        }
    };

    $reviewBadge = '<span class="badge-soft badge-soft-secondary"><i class="bi bi-clock me-1"></i>Siap Reviu</span>';
@endphp

    <!-- TABEL RINGKASAN KKA CONTROL & INTEGRASI -->
    <div class="summary-card">
        <div class="summary-card-header">
            <div class="d-flex align-items-center gap-3">
                <div class="summary-icon-box">
                    <i class="bi bi-calendar-week"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold" style="color: var(--text-dark); font-size: 0.95rem;">
                        Ringkasan Kontrol Mingguan &amp; KKA Fisik Lapangan (5 KKA Fisik)
                    </h6>
                    <small class="text-muted" style="font-size: 0.75rem;">
                        Penjadwalan 1x per minggu, beban waktu potong kuota harian, dan carry-forward otomatis dalam minggu berjalan.
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if(isset($kontrolMingguan) && $kontrolMingguan->isNotEmpty())
                    <span class="badge-soft badge-soft-secondary">
                        <i class="bi bi-calendar3 me-1 text-primary"></i> Minggu: {{ $kontrolMingguan->first()->start_date?->format('d M') }} s.d. {{ $kontrolMingguan->first()->end_date?->format('d M Y') }}
                    </span>
                @endif
                <span class="badge-soft badge-soft-success">
                    <i class="bi bi-check-circle-fill me-1"></i> 5 KKA Standar SOP
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table-summary text-center">
                    <thead>
                        <tr>
                            <th style="width: 45px;">No</th>
                            <th class="text-start" style="min-width: 190px;">Nama KKA Fisik</th>
                            <th style="min-width: 170px;">Kode Lembar Kerja</th>
                            <th style="min-width: 140px;">Jadwal Mingguan</th>
                            <th style="min-width: 110px;">Beban Waktu</th>
                            <th style="min-width: 145px;">Status Minggu Ini</th>
                            <th style="min-width: 90px;">Total Baris</th>
                            <th style="min-width: 110px;">Status Uji</th>
                            <th style="min-width: 135px;">Integrasi Klarifikasi</th>
                            <th style="min-width: 120px;">Status Review</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-muted fw-semibold">1</td>
                            <td class="text-start">
                                <i class="bi bi-cash-stack text-primary me-2"></i><strong>Cash Opname</strong>
                            </td>
                            <td><span class="sheet-code">KKA_Cash_Opname</span></td>
                            <td>
                                <span class="badge-soft badge-soft-secondary">
                                    Hari {{ $cashSched->hari_rencana ?? 2 }} ({{ $hariNames[$cashSched->hari_rencana ?? 2] ?? '-' }})
                                </span>
                                <div style="font-size:0.72rem; color:#64748b; margin-top: 0.2rem;">
                                    {{ $cashSched?->tanggal_rencana_efektif ? \Carbon\Carbon::parse($cashSched->tanggal_rencana_efektif)->format('d M Y') : '-' }}
                                    @if($cashSched?->override_tanggal)
                                        <span class="text-warning fw-bold">(Override)</span>
                                    @endif
                                </div>
                            </td>
                            <td><span class="badge-soft badge-soft-secondary"><i class="bi bi-stopwatch text-muted me-1"></i>{{ $cashSched->menit_rencana ?? 120 }}m</span></td>
                            <td>{!! $renderStatusBadge($cashSched->calculated_status ?? 'TERJADWAL') !!}</td>
                            <td><span class="fw-semibold text-dark">{{ count($cashItems) }}</span> <small class="text-muted">Baris</small></td>
                            <td><span class="badge-soft badge-soft-info"><i class="bi bi-play-circle me-1"></i>Aktif Diuji</span></td>
                            <td><span class="badge-soft badge-soft-success"><i class="bi bi-check2-circle me-1"></i>Tersinkron (05A)</span></td>
                            <td>{!! $reviewBadge !!}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">2</td>
                            <td class="text-start">
                                <i class="bi bi-eye text-primary me-2"></i><strong>Observasi Lingkungan</strong>
                            </td>
                            <td><span class="sheet-code">KKA_Observasi_Lingkungan</span></td>
                            <td>
                                <span class="badge-soft badge-soft-secondary">
                                    Hari {{ $obsSched->hari_rencana ?? 3 }} ({{ $hariNames[$obsSched->hari_rencana ?? 3] ?? '-' }})
                                </span>
                                <div style="font-size:0.72rem; color:#64748b; margin-top: 0.2rem;">
                                    {{ $obsSched?->tanggal_rencana_efektif ? \Carbon\Carbon::parse($obsSched->tanggal_rencana_efektif)->format('d M Y') : '-' }}
                                    @if($obsSched?->override_tanggal)
                                        <span class="text-warning fw-bold">(Override)</span>
                                    @endif
                                </div>
                            </td>
                            <td><span class="badge-soft badge-soft-secondary"><i class="bi bi-stopwatch text-muted me-1"></i>{{ $obsSched->menit_rencana ?? 90 }}m</span></td>
                            <td>{!! $renderStatusBadge($obsSched->calculated_status ?? 'TERJADWAL') !!}</td>
                            <td><span class="fw-semibold text-dark">{{ count($obsItems) }}</span> <small class="text-muted">Baris</small></td>
                            <td><span class="badge-soft badge-soft-info"><i class="bi bi-play-circle me-1"></i>Aktif Diuji</span></td>
                            <td><span class="badge-soft badge-soft-success"><i class="bi bi-check2-circle me-1"></i>Tersinkron (05A)</span></td>
                            <td>{!! $reviewBadge !!}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">3</td>
                            <td class="text-start">
                                <i class="bi bi-journal-check text-primary me-2"></i><strong>Rekonsiliasi Stok Cek/BG</strong>
                            </td>
                            <td><span class="sheet-code">KKA_Rekon_Stok_Cek_BG</span></td>
                            <td>
                                <span class="badge-soft badge-soft-secondary">
                                    Hari {{ $stokSched->hari_rencana ?? 4 }} ({{ $hariNames[$stokSched->hari_rencana ?? 4] ?? '-' }})
                                </span>
                                <div style="font-size:0.72rem; color:#64748b; margin-top: 0.2rem;">
                                    {{ $stokSched?->tanggal_rencana_efektif ? \Carbon\Carbon::parse($stokSched->tanggal_rencana_efektif)->format('d M Y') : '-' }}
                                    @if($stokSched?->override_tanggal)
                                        <span class="text-warning fw-bold">(Override)</span>
                                    @endif
                                </div>
                            </td>
                            <td><span class="badge-soft badge-soft-secondary"><i class="bi bi-stopwatch text-muted me-1"></i>{{ $stokSched->menit_rencana ?? 45 }}m</span></td>
                            <td>{!! $renderStatusBadge($stokSched->calculated_status ?? 'TERJADWAL') !!}</td>
                            <td><span class="fw-semibold text-dark">{{ count($stokItems) }}</span> <small class="text-muted">Baris</small></td>
                            <td><span class="badge-soft badge-soft-info"><i class="bi bi-play-circle me-1"></i>Aktif Diuji</span></td>
                            <td><span class="badge-soft badge-soft-success"><i class="bi bi-check2-circle me-1"></i>Tersinkron (05A)</span></td>
                            <td>{!! $reviewBadge !!}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">4</td>
                            <td class="text-start">
                                <i class="bi bi-credit-card-2-front text-primary me-2"></i><strong>Rekonsiliasi Kartu ATM</strong>
                            </td>
                            <td><span class="sheet-code">KKA_Rekon_Kartu_ATM</span></td>
                            <td>
                                <span class="badge-soft badge-soft-secondary">
                                    Hari {{ $atmSched->hari_rencana ?? 1 }} ({{ $hariNames[$atmSched->hari_rencana ?? 1] ?? '-' }})
                                </span>
                                <div style="font-size:0.72rem; color:#64748b; margin-top: 0.2rem;">
                                    {{ $atmSched?->tanggal_rencana_efektif ? \Carbon\Carbon::parse($atmSched->tanggal_rencana_efektif)->format('d M Y') : '-' }}
                                    @if($atmSched?->override_tanggal)
                                        <span class="text-warning fw-bold">(Override)</span>
                                    @endif
                                </div>
                            </td>
                            <td><span class="badge-soft badge-soft-secondary"><i class="bi bi-stopwatch text-muted me-1"></i>{{ $atmSched->menit_rencana ?? 60 }}m</span></td>
                            <td>{!! $renderStatusBadge($atmSched->calculated_status ?? 'TERJADWAL') !!}</td>
                            <td><span class="fw-semibold text-dark" id="summaryAtmTotal">{{ count($kartuAtmItems) }}</span> <small class="text-muted">Baris</small></td>
                            <td><span class="badge-soft badge-soft-info" id="summaryAtmStatus"><i class="bi bi-play-circle me-1"></i>Aktif Diuji</span></td>
                            <td><span class="badge-soft badge-soft-success"><i class="bi bi-check2-circle me-1"></i>Tersinkron (05A)</span></td>
                            <td>{!! $reviewBadge !!}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">5</td>
                            <td class="text-start">
                                <i class="bi bi-printer text-primary me-2"></i><strong>Rekonsiliasi Barang Cetakan</strong>
                            </td>
                            <td><span class="sheet-code">KKA_Rekon_Barang_Cetakan</span></td>
                            <td>
                                <span class="badge-soft badge-soft-secondary">
                                    Hari {{ $bctSched->hari_rencana ?? 5 }} ({{ $hariNames[$bctSched->hari_rencana ?? 5] ?? '-' }})
                                </span>
                                <div style="font-size:0.72rem; color:#64748b; margin-top: 0.2rem;">
                                    {{ $bctSched?->tanggal_rencana_efektif ? \Carbon\Carbon::parse($bctSched->tanggal_rencana_efektif)->format('d M Y') : '-' }}
                                    @if($bctSched?->override_tanggal)
                                        <span class="text-warning fw-bold">(Override)</span>
                                    @endif
                                </div>
                            </td>
                            <td><span class="badge-soft badge-soft-secondary"><i class="bi bi-stopwatch text-muted me-1"></i>{{ $bctSched->menit_rencana ?? 45 }}m</span></td>
                            <td>{!! $renderStatusBadge($bctSched->calculated_status ?? 'TERJADWAL') !!}</td>
                            <td><span class="fw-semibold text-dark" id="summaryBctTotal">{{ count($barangCetakanItems) }}</span> <small class="text-muted">Baris</small></td>
                            <td><span class="badge-soft badge-soft-info" id="summaryBctStatus"><i class="bi bi-play-circle me-1"></i>Aktif Diuji</span></td>
                            <td><span class="badge-soft badge-soft-success"><i class="bi bi-check2-circle me-1"></i>Tersinkron (05A)</span></td>
                            <td>{!! $reviewBadge !!}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 5 TAB KKA FISIK LENGKAP DENGAN INDIKATOR STATUS MINGGUAN -->
    <div class="summary-card">
        <div class="summary-card-header p-0" style="background: #ffffff; border-bottom: 1px solid var(--border-color);">
            <ul class="kka-nav-tabs" id="physicalKkaTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="kka-tab-btn active" id="cash-tab" data-bs-toggle="tab" data-bs-target="#cash" type="button" role="tab" aria-selected="true">
                        <i class="bi bi-cash-stack"></i>
                        <span>1. Cash Opname (Khasanah)</span>
                        {!! $renderTabPill($cashSched->calculated_status ?? null) !!}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="kka-tab-btn" id="cash-atm-tab" data-bs-toggle="tab" data-bs-target="#cash-atm" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-wallet2"></i>
                        <span>2. Cash Opname (Kas ATM)</span>
                        {!! $renderTabPill($cashSched->calculated_status ?? null) !!}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="kka-tab-btn" id="obs-tab" data-bs-toggle="tab" data-bs-target="#obs" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-eye"></i>
                        <span>3. Observasi Lingkungan</span>
                        {!! $renderTabPill($obsSched->calculated_status ?? null) !!}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="kka-tab-btn" id="stok-tab" data-bs-toggle="tab" data-bs-target="#stok" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-journal-check"></i>
                        <span>4. Rekon Stok Cek/BG</span>
                        {!! $renderTabPill($stokSched->calculated_status ?? null) !!}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="kka-tab-btn" id="atm-tab" data-bs-toggle="tab" data-bs-target="#atm" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-credit-card-2-front"></i>
                        <span>5. Rekon Kartu ATM</span>
                        {!! $renderTabPill($atmSched->calculated_status ?? null) !!}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="kka-tab-btn" id="cetakan-tab" data-bs-toggle="tab" data-bs-target="#cetakan" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-printer"></i>
                        <span>6. Rekon Barang Cetakan</span>
                        {!! $renderTabPill($bctSched->calculated_status ?? null) !!}
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            @if(session('success'))
                <div class="alert alert-success m-3">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger m-3">{{ session('error') }}</div>
            @endif
            
                <div class="tab-content" id="physicalKkaTabContent">
                    
                    <!-- TAB 1: CASH OPNAME KHASANAH -->
                    <div class="tab-pane fade show active p-3" id="cash" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-1"><i class="bi bi-cash-stack me-2 text-primary"></i>KKA_Cash_Opname_Khasanah (Pemeriksaan Kas &amp; Fisik Khasanah)</h6>
                                <small class="text-muted">Pemeriksaan fisik saldo uang tunai layak edar, uang logam, dan uang lusuh di khasanah.</small>
                            </div>
                            <span class="badge-soft badge-soft-primary">
                                <i class="bi bi-safe2 me-1"></i> Fisik Brankas Khasanah
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-excel">
                                <thead>
                                    <tr>
                                        <th>Objek Persediaan</th>
                                        <th>Pecahan</th>
                                        <th>Saldo Buku</th>
                                        <th style="min-width:120px;">Hasil Fisik</th>
                                        <th>Selisih</th>
                                        <th>Status Uji</th>
                                        <th style="min-width:150px;">Kondisi / Sebab</th>
                                        <th>Impact</th>
                                        <th>Likelihood</th>
                                        <th>Critical?</th>
                                        <th>Risk Score</th>
                                        <th>Risk Level</th>
                                        <th style="min-width:150px;">Simpulan RA</th>
                                        <th style="min-width:150px;">Bukti</th>
                                        <th style="min-width:130px;">Tgl Uji</th>
                                        @if($isAdmin)
                                        <th class="bg-warning text-dark">Reviewer ID</th>
                                        <th class="bg-warning text-dark" style="min-width:150px;">Status Review</th>
                                        <th class="bg-warning text-dark" style="min-width:200px;">Catatan Admin</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cashItems as $index => $item)
                                    @if(($item['sub_area'] ?? '') !== 'ATM')
                                    <tr class="data-row" data-type="cash">
                                        <td>
                                            {{ $item['objek'] }}
                                            <input type="hidden" name="cash_details[{{ $index }}][objek_persediaan]" value="{{ $item['objek'] }}">
                                            <input type="hidden" name="cash_details[{{ $index }}][sub_area]" value="{{ $item['sub_area'] ?? 'Khasanah' }}">
                                        </td>
                                        <td>
                                            {{ $item['pecahan'] }}
                                            <input type="hidden" name="cash_details[{{ $index }}][pecahan]" value="{{ $item['pecahan'] }}">
                                        </td>
                                        <td><input type="number" name="cash_details[{{ $index }}][saldo_sistem]" class="form-control saldo-sistem border-0 bg-transparent text-end" value="{{ $item['saldo_sistem'] ?? 50000000 }}" readonly></td>
                                        <td><input type="number" name="cash_details[{{ $index }}][hasil_fisik]" class="form-control hasil-fisik text-end" placeholder="0" value="{{ $item['hasil_fisik'] ?? '' }}" required></td>
                                        <td><input type="text" class="form-control selisih-display border-0 bg-transparent text-end fw-bold" readonly value="{{ isset($item['saldo_sistem'], $item['hasil_fisik']) && $item['hasil_fisik'] !== '' ? ((float)$item['saldo_sistem'] - (float)$item['hasil_fisik']) : '' }}"></td>
                                        <td><input type="text" class="form-control status-uji border-0 bg-transparent fw-bold text-center" value="{{ $item['status_uji'] ?? 'BELUM DIUJI' }}" readonly></td>
                                        
                                        <!-- Exception Fields (Disabled by default) -->
                                        <td><input type="text" name="cash_details[{{ $index }}][penyebab]" class="form-control exc-field" placeholder="Keterangan..." value="{{ $item['penyebab'] ?? '' }}" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}></td>
                                        <td>
                                            <select name="cash_details[{{ $index }}][impact]" class="form-select exc-field impact-sel" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}>
                                                @for($val=1; $val<=5; $val++)
                                                    <option value="{{ $val }}" {{ ($item['impact'] ?? 1) == $val ? 'selected' : '' }}>{{ $val }}</option>
                                                @endfor
                                            </select>
                                        </td>
                                        <td>
                                            <select name="cash_details[{{ $index }}][likelihood]" class="form-select exc-field likelihood-sel" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}>
                                                @for($val=1; $val<=5; $val++)
                                                    <option value="{{ $val }}" {{ ($item['likelihood'] ?? 1) == $val ? 'selected' : '' }}>{{ $val }}</option>
                                                @endfor
                                            </select>
                                        </td>
                                        <td>
                                            <select name="cash_details[{{ $index }}][critical_trigger]" class="form-select exc-field critical-sel" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}>
                                                <option value="Tidak" {{ ($item['critical_trigger'] ?? 'Tidak') === 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                                <option value="Ya" {{ ($item['critical_trigger'] ?? '') === 'Ya' ? 'selected' : '' }}>Ya</option>
                                            </select>
                                        </td>
                                        
                                        <!-- Auto calculated -->
                                        <td><input type="text" class="form-control risk-score border-0 bg-transparent text-center" readonly value="{{ $item['risk_score'] ?? '' }}"></td>
                                        <td><input type="text" class="form-control risk-level border-0 bg-transparent text-center" readonly value="{{ $item['severity_level'] ?? '' }}"></td>
                                        
                                        <td><input type="text" name="cash_details[{{ $index }}][simpulan_ra]" class="form-control exc-field" placeholder="Simpulan..." value="{{ $item['simpulan_ra'] ?? '' }}" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}></td>
                                        <td><input type="file" class="form-control exc-field" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}></td>
                                        <td><input type="date" name="cash_details[{{ $index }}][tanggal_uji]" class="form-control" value="{{ $item['tanggal_uji'] ?? date('Y-m-d') }}" {{ $isAdmin ? 'readonly' : 'required' }}></td>
                                        
                                        @if($isAdmin)
                                        @php $fakeId = $index + 1; @endphp
                                        <td><input type="text" class="form-control bg-light" value="{{ auth()->user()->name ?? 'Admin' }}" readonly></td>
                                        <td>
                                            <select name="cash_review[{{ $fakeId }}][status_review]" class="form-select border-warning">
                                                <option value="Pending" {{ ($item['status_review'] ?? '') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="Approved" {{ ($item['status_review'] ?? '') === 'Approved' ? 'selected' : '' }}>Approved</option>
                                                <option value="Revisi" {{ ($item['status_review'] ?? '') === 'Revisi' ? 'selected' : '' }}>Revisi</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="cash_review[{{ $fakeId }}][catatan_reviewer]" class="form-control border-warning" placeholder="Ketik catatan review..." value="{{ $item['catatan_reviewer'] ?? '' }}"></td>
                                        @endif
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 2: CASH OPNAME KAS ATM -->
                    <div class="tab-pane fade p-3" id="cash-atm" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-1"><i class="bi bi-wallet2 me-2 text-primary"></i>KKA_Cash_Opname_ATM (Pemeriksaan Kas Kaset Mesin ATM &amp; CRM)</h6>
                                <small class="text-muted">Pemeriksaan fisik saldo uang tunai pada kaset mesin ATM/CRM terhadap saldo buku/sistem (maksimal 4 kaset + reject/retain per mesin).</small>
                            </div>
                            <span class="badge-soft badge-soft-info">
                                <i class="bi bi-link-45deg me-1"></i> Terhubung dengan KKA Cash Opname
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-excel">
                                <thead>
                                    <tr>
                                        <th style="min-width: 220px;">ID / Lokasi Mesin ATM</th>
                                        <th style="min-width: 200px;">Rincian Kaset / Keterangan</th>
                                        <th style="min-width: 130px;">Saldo Buku (Rp)</th>
                                        <th style="min-width: 130px;">Hasil Fisik (Rp)</th>
                                        <th style="min-width: 110px;">Selisih</th>
                                        <th style="min-width: 120px;">Status Uji</th>
                                        <th style="min-width: 180px;">Kondisi / Sebab</th>
                                        <th>Impact</th>
                                        <th>Likelihood</th>
                                        <th>Critical?</th>
                                        <th>Risk Score</th>
                                        <th>Risk Level</th>
                                        <th style="min-width: 160px;">Simpulan RA</th>
                                        <th style="min-width: 150px;">Bukti</th>
                                        <th style="min-width: 130px;">Tgl Uji</th>
                                        @if($isAdmin)
                                        <th class="bg-warning text-dark">Reviewer ID</th>
                                        <th class="bg-warning text-dark" style="min-width: 150px;">Status Review</th>
                                        <th class="bg-warning text-dark" style="min-width: 200px;">Catatan Admin</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cashItems as $index => $item)
                                    @if(($item['sub_area'] ?? '') === 'ATM')
                                    <tr class="data-row" data-type="cash">
                                        <td>
                                            @if(empty($item['objek']))
                                                <input type="text" name="cash_details[{{ $index }}][objek_persediaan]" class="form-control" placeholder="Ketik ID/lokasi ATM..." value="{{ $item['objek'] ?? '' }}">
                                            @else
                                                <div class="fw-bold">{{ $item['objek'] }}</div>
                                                <input type="hidden" name="cash_details[{{ $index }}][objek_persediaan]" value="{{ $item['objek'] }}">
                                            @endif
                                            <input type="hidden" name="cash_details[{{ $index }}][sub_area]" value="ATM">
                                        </td>
                                        <td>
                                            <input type="text" name="cash_details[{{ $index }}][pecahan]" class="form-control" value="{{ $item['pecahan'] ?? 'Isi ID/lokasi ATM; maksimal 4 kaset + reject/retain' }}">
                                        </td>
                                        <td><input type="number" name="cash_details[{{ $index }}][saldo_sistem]" class="form-control saldo-sistem border-0 bg-transparent text-end" value="{{ $item['saldo_sistem'] ?? 50000000 }}" readonly></td>
                                        <td><input type="number" name="cash_details[{{ $index }}][hasil_fisik]" class="form-control hasil-fisik text-end" placeholder="0" value="{{ $item['hasil_fisik'] ?? '' }}"></td>
                                        <td><input type="text" class="form-control selisih-display border-0 bg-transparent text-end fw-bold" readonly value="{{ isset($item['saldo_sistem'], $item['hasil_fisik']) && $item['hasil_fisik'] !== '' ? ((float)$item['saldo_sistem'] - (float)$item['hasil_fisik']) : '' }}"></td>
                                        <td><input type="text" class="form-control status-uji border-0 bg-transparent fw-bold text-center" value="{{ $item['status_uji'] ?? 'BELUM DIUJI' }}" readonly></td>
                                        
                                        <!-- Exception Fields -->
                                        <td><input type="text" name="cash_details[{{ $index }}][penyebab]" class="form-control exc-field" placeholder="Keterangan..." value="{{ $item['penyebab'] ?? '' }}" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}></td>
                                        <td>
                                            <select name="cash_details[{{ $index }}][impact]" class="form-select exc-field impact-sel" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}>
                                                @for($val=1; $val<=5; $val++)
                                                    <option value="{{ $val }}" {{ ($item['impact'] ?? 1) == $val ? 'selected' : '' }}>{{ $val }}</option>
                                                @endfor
                                            </select>
                                        </td>
                                        <td>
                                            <select name="cash_details[{{ $index }}][likelihood]" class="form-select exc-field likelihood-sel" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}>
                                                @for($val=1; $val<=5; $val++)
                                                    <option value="{{ $val }}" {{ ($item['likelihood'] ?? 1) == $val ? 'selected' : '' }}>{{ $val }}</option>
                                                @endfor
                                            </select>
                                        </td>
                                        <td>
                                            <select name="cash_details[{{ $index }}][critical_trigger]" class="form-select exc-field critical-sel" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}>
                                                <option value="Tidak" {{ ($item['critical_trigger'] ?? 'Tidak') === 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                                <option value="Ya" {{ ($item['critical_trigger'] ?? '') === 'Ya' ? 'selected' : '' }}>Ya</option>
                                            </select>
                                        </td>
                                        
                                        <!-- Auto calculated -->
                                        <td><input type="text" class="form-control risk-score border-0 bg-transparent text-center" readonly value="{{ $item['risk_score'] ?? '' }}"></td>
                                        <td><input type="text" class="form-control risk-level border-0 bg-transparent text-center" readonly value="{{ $item['severity_level'] ?? '' }}"></td>
                                        
                                        <td><input type="text" name="cash_details[{{ $index }}][simpulan_ra]" class="form-control exc-field" placeholder="Simpulan..." value="{{ $item['simpulan_ra'] ?? '' }}" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}></td>
                                        <td><input type="file" class="form-control exc-field" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}></td>
                                        <td><input type="date" name="cash_details[{{ $index }}][tanggal_uji]" class="form-control" value="{{ $item['tanggal_uji'] ?? date('Y-m-d') }}" {{ $isAdmin ? 'readonly' : 'required' }}></td>
                                        
                                        @if($isAdmin)
                                        @php $fakeId = $index + 1; @endphp
                                        <td><input type="text" class="form-control bg-light" value="{{ auth()->user()->name ?? 'Admin' }}" readonly></td>
                                        <td>
                                            <select name="cash_review[{{ $fakeId }}][status_review]" class="form-select border-warning">
                                                <option value="Pending" {{ ($item['status_review'] ?? '') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="Approved" {{ ($item['status_review'] ?? '') === 'Approved' ? 'selected' : '' }}>Approved</option>
                                                <option value="Revisi" {{ ($item['status_review'] ?? '') === 'Revisi' ? 'selected' : '' }}>Revisi</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="cash_review[{{ $fakeId }}][catatan_reviewer]" class="form-control border-warning" placeholder="Ketik catatan review..." value="{{ $item['catatan_reviewer'] ?? '' }}"></td>
                                        @endif
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 2: OBSERVASI -->
                    <div class="tab-pane fade p-3" id="obs" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-1"><i class="bi bi-eye me-2 text-primary"></i>KKA_Observasi_Lingkungan (Observasi Fisik &amp; Lingkungan Kerja)</h6>
                                <small class="text-muted">Pengujian kondisi fisik keamanan, kebersihan, kepatuhan atribut, dan operasional kantor.</small>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-excel">
                                <thead>
                                    <tr>
                                        <th>Area Observasi</th>
                                        <th style="min-width:250px;">Kriteria</th>
                                        <th style="min-width:150px;">Kondisi Aktual</th>
                                        <th style="min-width:130px;">Hasil Observasi</th>
                                        <th>Status Uji</th>
                                        <th style="min-width:200px;">Penyebab</th>
                                        <th>Impact</th>
                                        <th>Likelihood</th>
                                        <th>Critical?</th>
                                        <th>Risk Score</th>
                                        <th>Risk Level</th>
                                        <th style="min-width:150px;">Bukti</th>
                                        <th style="min-width:130px;">Tgl Uji</th>
                                        @if($isAdmin)
                                        <th class="bg-warning text-dark">Reviewer ID</th>
                                        <th class="bg-warning text-dark" style="min-width:150px;">Status Review</th>
                                        <th class="bg-warning text-dark" style="min-width:200px;">Catatan Admin</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($obsItems as $index => $item)
                                    <tr class="data-row" data-type="obs">
                                        <td>
                                            {{ $item['area'] }}
                                            <input type="hidden" name="obs_details[{{ $index }}][area_observasi]" value="{{ $item['area'] }}">
                                        </td>
                                        <td class="text-wrap text-start">
                                            @if(empty($item['kriteria']))
                                                <input type="text" name="obs_details[{{ $index }}][kriteria_diharapkan]" class="form-control" placeholder="Ketik lokasi ATM..." required>
                                            @else
                                                {{ $item['kriteria'] }}
                                                <input type="hidden" name="obs_details[{{ $index }}][kriteria_diharapkan]" value="{{ $item['kriteria'] }}">
                                            @endif
                                        </td>
                                        <td><input type="text" name="obs_details[{{ $index }}][kondisi_aktual]" class="form-control" required></td>
                                        <td>
                                            <select name="obs_details[{{ $index }}][hasil_observasi]" class="form-select hasil-obs" required>
                                                <option value="">Pilih...</option>
                                                <option value="Sesuai">Sesuai</option>
                                                <option value="Tidak Sesuai">Tidak Sesuai</option>
                                                <option value="Tidak Berlaku">Tidak Berlaku</option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control status-uji border-0 bg-transparent fw-bold text-center" value="BELUM DIUJI" readonly></td>
                                        
                                        <!-- Exception Fields -->
                                        <td><input type="text" name="obs_details[{{ $index }}][penyebab]" class="form-control exc-field" disabled></td>
                                        <td><select name="obs_details[{{ $index }}][impact]" class="form-select exc-field impact-sel" disabled><option value="1">1</option><option value="5">5</option></select></td>
                                        <td><select name="obs_details[{{ $index }}][likelihood]" class="form-select exc-field likelihood-sel" disabled><option value="1">1</option><option value="5">5</option></select></td>
                                        <td><select name="obs_details[{{ $index }}][critical_trigger]" class="form-select exc-field critical-sel" disabled><option value="Tidak">Tidak</option><option value="Ya">Ya</option></select></td>
                                        
                                        <!-- Auto calculated -->
                                        <td><input type="text" class="form-control risk-score border-0 bg-transparent text-center" readonly></td>
                                        <td><input type="text" class="form-control risk-level border-0 bg-transparent text-center" readonly></td>
                                        
                                        <td><input type="file" class="form-control exc-field" disabled></td>
                                        <td><input type="date" name="obs_details[{{ $index }}][tanggal_uji]" class="form-control" value="{{ date('Y-m-d') }}" {{ $isAdmin ? 'readonly' : 'required' }}></td>
                                        
                                        @if($isAdmin)
                                        @php $fakeId = $index + 1; @endphp
                                        <td><input type="text" class="form-control bg-light" value="{{ auth()->user()->name ?? 'Admin' }}" readonly></td>
                                        <td>
                                            <select name="obs_review[{{ $fakeId }}][status_review]" class="form-select border-warning">
                                                <option value="Pending">Pending</option>
                                                <option value="Approved">Approved</option>
                                                <option value="Revisi">Revisi</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="obs_review[{{ $fakeId }}][catatan_reviewer]" class="form-control border-warning" placeholder="Ketik catatan review..."></td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 3: STOK OPNAME -->
                    <div class="tab-pane fade p-3" id="stok" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-1"><i class="bi bi-journal-check me-2 text-primary"></i>KKA_Rekon_Stok_Cek_BG (Rekonsiliasi Persediaan Cek &amp; Bilyet Giro)</h6>
                                <small class="text-muted">Pemeriksaan stok fisik warkat Cek dan Bilyet Giro terhadap saldo nominatif sistem.</small>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-excel">
                                <thead>
                                    <tr>
                                        <th>Sub Area</th>
                                        <th>Kondisi Objek</th>
                                        <th>Saldo Buku</th>
                                        <th style="min-width:120px;">Hasil Fisik</th>
                                        <th>Selisih</th>
                                        <th>Status Uji</th>
                                        <th style="min-width:150px;">Sebab</th>
                                        <th>Impact</th>
                                        <th>Likelihood</th>
                                        <th>Critical?</th>
                                        <th>Risk Score</th>
                                        <th>Risk Level</th>
                                        <th style="min-width:150px;">Simpulan RA</th>
                                        <th style="min-width:150px;">Bukti</th>
                                        <th style="min-width:130px;">Tgl Uji</th>
                                        @if($isAdmin)
                                        <th class="bg-warning text-dark">Reviewer ID</th>
                                        <th class="bg-warning text-dark" style="min-width:150px;">Status Review</th>
                                        <th class="bg-warning text-dark" style="min-width:200px;">Catatan Admin</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stokItems as $index => $item)
                                    <tr class="data-row" data-type="stok">
                                        <td style="min-width: 150px;">
                                            {{ $item['sub_area'] }}
                                            <input type="hidden" name="stok_details[{{ $index }}][sub_area]" value="{{ $item['sub_area'] }}">
                                        </td>
                                        <td class="text-wrap text-start" style="min-width: 300px;">
                                            <div class="fw-bold">{{ $item['objek'] }}</div>
                                            <div class="text-muted small">{{ $item['pecahan'] }}</div>
                                            <input type="hidden" name="stok_details[{{ $index }}][kondisi_objek]" value="{{ $item['objek'] . ' | ' . $item['pecahan'] }}">
                                        </td>
                                        <td><input type="number" name="stok_details[{{ $index }}][saldo_sistem]" class="form-control saldo-sistem border-0 bg-transparent text-end" value="150" readonly></td>
                                        <td><input type="number" name="stok_details[{{ $index }}][hasil_fisik]" class="form-control hasil-fisik text-end" placeholder="0" required></td>
                                        <td><input type="text" class="form-control selisih-display border-0 bg-transparent text-end fw-bold" readonly></td>
                                        <td><input type="text" class="form-control status-uji border-0 bg-transparent fw-bold text-center" value="BELUM DIUJI" readonly></td>
                                        
                                        <!-- Exception Fields -->
                                        <td><input type="text" name="stok_details[{{ $index }}][penyebab]" class="form-control exc-field" disabled></td>
                                        <td><select name="stok_details[{{ $index }}][impact]" class="form-select exc-field impact-sel" disabled><option value="1">1</option><option value="5">5</option></select></td>
                                        <td><select name="stok_details[{{ $index }}][likelihood]" class="form-select exc-field likelihood-sel" disabled><option value="1">1</option><option value="5">5</option></select></td>
                                        <td><select name="stok_details[{{ $index }}][critical_trigger]" class="form-select exc-field critical-sel" disabled><option value="Tidak">Tidak</option><option value="Ya">Ya</option></select></td>
                                        
                                        <!-- Auto calculated -->
                                        <td><input type="text" class="form-control risk-score border-0 bg-transparent text-center" readonly></td>
                                        <td><input type="text" class="form-control risk-level border-0 bg-transparent text-center" readonly></td>
                                        <td><input type="text" name="stok_details[{{ $index }}][simpulan_ra]" class="form-control exc-field" disabled></td>
                                        <td><input type="file" class="form-control exc-field" disabled></td>
                                        <td><input type="date" name="stok_details[{{ $index }}][tanggal_uji]" class="form-control" value="{{ date('Y-m-d') }}" {{ $isAdmin ? 'readonly' : 'required' }}></td>
                                        
                                        @if($isAdmin)
                                        @php $fakeId = $index + 1; @endphp
                                        <td><input type="text" class="form-control bg-light" value="{{ auth()->user()->name ?? 'Admin' }}" readonly></td>
                                        <td>
                                            <select name="stok_review[{{ $fakeId }}][status_review]" class="form-select border-warning">
                                                <option value="Pending">Pending</option>
                                                <option value="Approved">Approved</option>
                                                <option value="Revisi">Revisi</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="stok_review[{{ $fakeId }}][catatan_reviewer]" class="form-control border-warning" placeholder="Ketik catatan review..."></td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 4: REKONSILIASI KARTU ATM -->
                    <div class="tab-pane fade p-3" id="atm" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-1"><i class="bi bi-credit-card-2-front me-2 text-primary"></i>KKA_Rekon_Kartu_ATM (Rekonsiliasi Persediaan Kartu ATM)</h6>
                                <small class="text-muted">Standar 28 Kolom KKA Fisik. Kelompok 1: Stok Batch Kartu (Dinamis), Kelompok 2: Prosedur Kontrol Wajib (Statis).</small>
                            </div>
                            @if(!$isAdmin)
                            <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="btnAddAtmRow">
                                <i class="bi bi-plus-circle me-1"></i> + Tambah Baris Batch Kartu
                            </button>
                            @endif
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-excel" id="tableKartuAtm">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">No</th>
                                        <th style="min-width: 200px;">Procedure ID</th>
                                        <th style="min-width: 140px;">Sub Area</th>
                                        <th style="min-width: 180px;">Jenis Kartu / Batch / Seri</th>
                                        <th style="min-width: 180px;">Keterangan / Lokasi</th>
                                        <th style="min-width: 110px;">Saldo Nominatif</th>
                                        <th style="min-width: 130px;">Fisik / Kondisi Aktual</th>
                                        <th style="min-width: 100px;">Selisih</th>
                                        <th style="min-width: 120px;">Status Uji Sistem</th>
                                        <th style="min-width: 140px;">Hasil Kontrol / Kondisi</th>
                                        <th style="min-width: 180px;">Kriteria Tidak Dipenuhi</th>
                                        <th style="min-width: 180px;">Penyebab</th>
                                        <th style="min-width: 180px;">Dampak</th>
                                        <th style="min-width: 80px;">Impact (1-5)</th>
                                        <th style="min-width: 90px;">Likelihood (1-5)</th>
                                        <th style="min-width: 90px;">Critical Trigger</th>
                                        <th style="min-width: 90px;">Risk Score</th>
                                        <th style="min-width: 100px;">Risk Level</th>
                                        <th style="min-width: 110px;">Perlu Klarifikasi</th>
                                        <th style="min-width: 160px;">Referensi Bukti</th>
                                        <th style="min-width: 180px;">Simpulan Profesional RA</th>
                                        <th style="min-width: 130px;">Tanggal Uji</th>
                                        <th style="min-width: 140px;">RA Pelaksana</th>
                                        <th class="bg-warning text-dark" style="min-width: 140px;">Reviewer</th>
                                        <th class="bg-warning text-dark" style="min-width: 150px;">Status Review</th>
                                        <th class="bg-warning text-dark" style="min-width: 180px;">Validasi</th>
                                        <th style="min-width: 120px;">Siap Register Temuan</th>
                                        <th style="min-width: 110px;">Exception Aktif</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyKartuAtm">
                                    @php $noUrutAtm = 1; @endphp
                                    @foreach($kartuAtmItems as $index => $item)
                                        @php
                                            $isCtrl = !empty($item['is_control']);
                                            $detailId = $item['id'] ?? ($index + 1);
                                            $defaultProcId = $item['procedure_id'] ?? ('FLD-WK-' . $kodeUnit . '-' . date('Ymd') . '-KATREC-' . str_pad($noUrutAtm, 3, '0', STR_PAD_LEFT));
                                            $isExc = ($item['status_uji'] ?? '') === 'EXCEPTION';
                                            $statusClass = match($item['status_uji'] ?? 'BELUM DIUJI') {
                                                'SESUAI' => 'text-success',
                                                'EXCEPTION' => 'text-danger',
                                                'TIDAK BERLAKU' => 'text-secondary',
                                                default => 'text-muted'
                                            };
                                            $riskColor = match($item['severity_level'] ?? '') {
                                                'Kritis' => 'text-danger',
                                                'Tinggi' => 'text-warning',
                                                'Sedang' => 'text-primary',
                                                'Rendah' => 'text-success',
                                                default => 'text-muted'
                                            };
                                        @endphp
                                        <tr class="data-row atm-row {{ $isCtrl ? 'bg-light bg-opacity-50' : '' }}" data-type="atm" data-group="{{ $isCtrl ? 'control' : 'stock' }}" data-index="{{ $index }}">
                                            <!-- 1. No -->
                                            <td class="text-center fw-bold row-no">{{ $noUrutAtm++ }}</td>

                                            <!-- 2. Procedure ID -->
                                            <td>
                                                <input type="text" name="kartu_atm_details[{{ $index }}][procedure_id]" class="form-control form-control-sm bg-light border-0 fw-bold font-monospace" value="{{ $defaultProcId }}" readonly>
                                            </td>

                                            <!-- 3. Sub Area -->
                                            <td>
                                                <span class="badge {{ $isCtrl ? 'bg-secondary' : 'bg-primary' }}">{{ $item['sub_area'] }}</span>
                                                <input type="hidden" name="kartu_atm_details[{{ $index }}][sub_area]" value="{{ $item['sub_area'] }}">
                                            </td>

                                            <!-- 4. Jenis Kartu / Batch / Seri -->
                                            <td>
                                                @if($isCtrl)
                                                    <div class="fw-bold">{{ $item['jenis_kartu'] }}</div>
                                                    <input type="hidden" name="kartu_atm_details[{{ $index }}][jenis_kartu]" value="{{ $item['jenis_kartu'] }}">
                                                @else
                                                    <input type="text" name="kartu_atm_details[{{ $index }}][jenis_kartu]" class="form-control form-control-sm atm-jenis-kartu" placeholder="Contoh: Kartu ATM Silver/Gold/Batch X" value="{{ $item['jenis_kartu'] ?? '' }}">
                                                @endif
                                            </td>

                                            <!-- 5. Keterangan / Lokasi -->
                                            <td>
                                                @if($isCtrl)
                                                    <div class="small text-muted">{{ $item['keterangan_lokasi'] }}</div>
                                                    <input type="hidden" name="kartu_atm_details[{{ $index }}][keterangan_lokasi]" value="{{ $item['keterangan_lokasi'] }}">
                                                @else
                                                    <input type="text" name="kartu_atm_details[{{ $index }}][keterangan_lokasi]" class="form-control form-control-sm" placeholder="Contoh: Khasanah / Lemari Besi" value="{{ $item['keterangan_lokasi'] ?? '' }}">
                                                @endif
                                            </td>

                                            <!-- 6. Saldo Nominatif -->
                                            <td>
                                                @if($isCtrl)
                                                    <input type="text" class="form-control form-control-sm text-center bg-light border-0" value="N/A" readonly>
                                                    <input type="hidden" name="kartu_atm_details[{{ $index }}][saldo_nominatif]" value="N/A">
                                                @else
                                                    <input type="number" step="1" name="kartu_atm_details[{{ $index }}][saldo_nominatif]" class="form-control form-control-sm text-end atm-saldo" placeholder="0" value="{{ $item['saldo_nominatif'] ?? '' }}">
                                                @endif
                                            </td>

                                            <!-- 7. Fisik / Kondisi Aktual -->
                                            <td>
                                                @if($isCtrl)
                                                    <input type="text" name="kartu_atm_details[{{ $index }}][hasil_fisik]" class="form-control form-control-sm atm-ctrl-fisik" placeholder="Catat kondisi lapangan..." value="{{ $item['hasil_fisik'] ?? '' }}">
                                                @else
                                                    <input type="number" step="1" name="kartu_atm_details[{{ $index }}][hasil_fisik]" class="form-control form-control-sm text-end atm-fisik" placeholder="0" value="{{ $item['hasil_fisik'] ?? '' }}">
                                                @endif
                                            </td>

                                            <!-- 8. Selisih -->
                                            <td>
                                                @if($isCtrl)
                                                    <input type="text" class="form-control form-control-sm text-center bg-light border-0 fw-bold" value="N/A" readonly>
                                                    <input type="hidden" name="kartu_atm_details[{{ $index }}][selisih]" value="N/A">
                                                @else
                                                    <input type="text" name="kartu_atm_details[{{ $index }}][selisih]" class="form-control form-control-sm text-end atm-selisih border-0 bg-transparent fw-bold" readonly value="{{ $item['selisih'] ?? '' }}">
                                                @endif
                                            </td>

                                            <!-- 9. Status Uji Sistem -->
                                            <td>
                                                <input type="text" name="kartu_atm_details[{{ $index }}][status_uji]" class="form-control form-control-sm atm-status border-0 bg-transparent fw-bold text-center {{ $statusClass }}" readonly value="{{ $item['status_uji'] ?? 'BELUM DIUJI' }}">
                                            </td>

                                            <!-- 10. Hasil Kontrol / Kondisi -->
                                            <td>
                                                @if($isCtrl)
                                                    <select name="kartu_atm_details[{{ $index }}][kondisi]" class="form-select form-select-sm atm-ctrl-kondisi">
                                                        <option value="Sesuai" {{ ($item['kondisi'] ?? '') === 'Sesuai' ? 'selected' : '' }}>Sesuai</option>
                                                        <option value="Tidak Sesuai" {{ ($item['kondisi'] ?? '') === 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                                        <option value="Tidak Berlaku" {{ ($item['kondisi'] ?? '') === 'Tidak Berlaku' ? 'selected' : '' }}>Tidak Berlaku</option>
                                                    </select>
                                                @else
                                                    <select name="kartu_atm_details[{{ $index }}][kondisi]" class="form-select form-select-sm atm-kondisi">
                                                        <option value="Baik" {{ ($item['kondisi'] ?? '') === 'Baik' ? 'selected' : '' }}>Baik</option>
                                                        <option value="Rusak" {{ ($item['kondisi'] ?? '') === 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                                        <option value="Outdated" {{ ($item['kondisi'] ?? '') === 'Outdated' ? 'selected' : '' }}>Outdated</option>
                                                        <option value="Tidak Baik" {{ ($item['kondisi'] ?? '') === 'Tidak Baik' ? 'selected' : '' }}>Tidak Baik</option>
                                                        <option value="Tidak Berlaku" {{ ($item['kondisi'] ?? '') === 'Tidak Berlaku' ? 'selected' : '' }}>Tidak Berlaku</option>
                                                    </select>
                                                @endif
                                            </td>

                                            <!-- 11. Kriteria yang Tidak Dipenuhi -->
                                            <td><input type="text" name="kartu_atm_details[{{ $index }}][kriteria]" class="form-control form-control-sm exc-field" placeholder="Kriteria..." value="{{ $item['kriteria'] ?? '' }}" {{ $isExc ? '' : 'disabled' }}></td>

                                            <!-- 12. Penyebab -->
                                            <td><input type="text" name="kartu_atm_details[{{ $index }}][penyebab]" class="form-control form-control-sm exc-field" placeholder="Penyebab..." value="{{ $item['penyebab'] ?? '' }}" {{ $isExc ? '' : 'disabled' }}></td>

                                            <!-- 13. Dampak -->
                                            <td><input type="text" name="kartu_atm_details[{{ $index }}][dampak]" class="form-control form-control-sm exc-field" placeholder="Dampak..." value="{{ $item['dampak'] ?? '' }}" {{ $isExc ? '' : 'disabled' }}></td>

                                            <!-- 14. Impact (1-5) -->
                                            <td>
                                                <select name="kartu_atm_details[{{ $index }}][impact]" class="form-select form-select-sm exc-field impact-sel" {{ $isExc ? '' : 'disabled' }}>
                                                    @for($val=1; $val<=5; $val++)
                                                        <option value="{{ $val }}" {{ ($item['impact'] ?? 1) == $val ? 'selected' : '' }}>{{ $val }}</option>
                                                    @endfor
                                                </select>
                                            </td>

                                            <!-- 15. Likelihood (1-5) -->
                                            <td>
                                                <select name="kartu_atm_details[{{ $index }}][likelihood]" class="form-select form-select-sm exc-field likelihood-sel" {{ $isExc ? '' : 'disabled' }}>
                                                    @for($val=1; $val<=5; $val++)
                                                        <option value="{{ $val }}" {{ ($item['likelihood'] ?? 1) == $val ? 'selected' : '' }}>{{ $val }}</option>
                                                    @endfor
                                                </select>
                                            </td>

                                            <!-- 16. Critical Trigger -->
                                            <td>
                                                <select name="kartu_atm_details[{{ $index }}][critical_trigger]" class="form-select form-select-sm exc-field critical-sel" {{ $isExc ? '' : 'disabled' }}>
                                                    <option value="Tidak" {{ ($item['critical_trigger'] ?? 'Tidak') === 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                                    <option value="Ya" {{ ($item['critical_trigger'] ?? '') === 'Ya' ? 'selected' : '' }}>Ya</option>
                                                </select>
                                            </td>

                                            <!-- 17. Risk Score -->
                                            <td><input type="text" name="kartu_atm_details[{{ $index }}][risk_score]" class="form-control form-control-sm risk-score border-0 bg-transparent text-center fw-bold" readonly value="{{ $item['risk_score'] ?? '' }}"></td>

                                            <!-- 18. Risk Level -->
                                            <td><input type="text" name="kartu_atm_details[{{ $index }}][severity_level]" class="form-control form-control-sm risk-level border-0 bg-transparent text-center fw-bold {{ $riskColor }}" readonly value="{{ $item['severity_level'] ?? '' }}"></td>

                                            <!-- 19. Perlu Klarifikasi -->
                                            <td><input type="text" name="kartu_atm_details[{{ $index }}][perlu_klarifikasi]" class="form-control form-control-sm atm-perlu-klarifikasi border-0 bg-transparent text-center fw-bold" readonly value="{{ $item['perlu_klarifikasi'] ?? ($isExc ? 'Ya' : 'Tidak') }}"></td>

                                            <!-- 20. Referensi Bukti -->
                                            <td>
                                                <input type="file" name="kartu_atm_details[{{ $index }}][bukti]" class="form-control form-control-sm exc-field" {{ $isExc ? '' : 'disabled' }}>
                                                @if(!empty($item['bukti_path']))
                                                    <a href="{{ asset('storage/' . $item['bukti_path']) }}" target="_blank" class="badge bg-info text-decoration-none mt-1 d-inline-block"><i class="bi bi-file-earmark-image"></i> Lihat Bukti</a>
                                                @endif
                                            </td>

                                            <!-- 21. Simpulan Profesional RA -->
                                            <td><input type="text" name="kartu_atm_details[{{ $index }}][simpulan_ra]" class="form-control form-control-sm" placeholder="Simpulan..." value="{{ $item['simpulan_ra'] ?? '' }}"></td>

                                            <!-- 22. Tanggal Uji -->
                                            <td><input type="date" name="kartu_atm_details[{{ $index }}][tanggal_uji]" class="form-control form-control-sm" value="{{ $item['tanggal_uji'] ?? date('Y-m-d') }}"></td>

                                            <!-- 23. RA Pelaksana -->
                                            <td><input type="text" name="kartu_atm_details[{{ $index }}][ra_pelaksana]" class="form-control form-control-sm bg-light" value="{{ $item['ra_pelaksana'] ?? (auth()->user()->name ?? 'RA') }}" readonly></td>

                                            <!-- 24. Reviewer (Admin) -->
                                            <td class="bg-warning bg-opacity-10">
                                                @if($isAdmin)
                                                    <input type="text" class="form-control form-control-sm bg-light border-warning" value="{{ auth()->user()->name ?? 'Admin Reviewer' }}" readonly>
                                                @else
                                                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $item['reviewer_id'] ?? '-' }}" readonly>
                                                @endif
                                            </td>

                                            <!-- 25. Status Review (Admin) -->
                                            <td class="bg-warning bg-opacity-10">
                                                @if($isAdmin)
                                                    <select name="kartu_atm_review[{{ $detailId }}][status_review]" class="form-select form-select-sm border-warning admin-review-status">
                                                        <option value="Belum Direviu" {{ ($item['status_review'] ?? '') === 'Belum Direviu' ? 'selected' : '' }}>Belum Direviu</option>
                                                        <option value="Dikembalikan" {{ ($item['status_review'] ?? '') === 'Dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                                                        <option value="Disetujui" {{ ($item['status_review'] ?? '') === 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                                                    </select>
                                                @else
                                                    <span class="badge bg-secondary">{{ $item['status_review'] ?? 'Belum Direviu' }}</span>
                                                @endif
                                            </td>

                                            <!-- 26. Validasi (Admin) -->
                                            <td class="bg-warning bg-opacity-10">
                                                @if($isAdmin)
                                                    <select name="kartu_atm_review[{{ $detailId }}][validasi]" class="form-select form-select-sm border-warning admin-validasi-sel">
                                                        <option value="">Pilih Validasi...</option>
                                                        <option value="VALID" {{ strtoupper($item['validasi'] ?? '') === 'VALID' ? 'selected' : '' }}>VALID</option>
                                                        <option value="INVALID" {{ strtoupper($item['validasi'] ?? '') === 'INVALID' ? 'selected' : '' }}>INVALID</option>
                                                        <option value="CATATAN" {{ strtoupper($item['validasi'] ?? '') === 'CATATAN' ? 'selected' : '' }}>CATATAN</option>
                                                    </select>
                                                @else
                                                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $item['validasi'] ?? '-' }}" readonly>
                                                @endif
                                            </td>

                                            <!-- 27. Siap Register Temuan -->
                                            <td>
                                                <input type="text" class="form-control form-control-sm atm-siap-register border-0 bg-transparent text-center fw-bold" readonly value="{{ $item['siap_register'] ?? 'Tidak' }}">
                                            </td>

                                            <!-- 28. Exception Aktif -->
                                            <td>
                                                <input type="text" class="form-control form-control-sm atm-exc-aktif border-0 bg-transparent text-center fw-bold {{ $isExc ? 'text-danger' : 'text-muted' }}" readonly value="{{ $isExc ? 'Ya' : 'Tidak' }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 5: REKONSILIASI BARANG CETAKAN -->
                    <div class="tab-pane fade p-3" id="cetakan" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-1"><i class="bi bi-printer me-2 text-success"></i>KKA_Rekon_Barang_Cetakan (Rekonsiliasi Persediaan Barang Cetakan)</h6>
                                <small class="text-muted">Standar 28 Kolom KKA Fisik. Memuat 27 item barang cetakan standar perbankan sesuai SOP Excel.</small>
                            </div>
                            @if(!$isAdmin)
                            <button type="button" class="btn btn-sm btn-outline-success fw-bold" id="btnAddBctRow">
                                <i class="bi bi-plus-circle me-1"></i> + Tambah Item Barang Cetakan
                            </button>
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-excel" id="tableBarangCetakan">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">No</th>
                                        <th style="min-width: 200px;">Procedure ID</th>
                                        <th style="min-width: 140px;">Kategori</th>
                                        <th style="min-width: 200px;">Nama Barang Cetakan</th>
                                        <th style="min-width: 80px;">Satuan</th>
                                        <th style="min-width: 120px;">Stok Laporan / Listing</th>
                                        <th style="min-width: 120px;">Hasil Hitung Fisik</th>
                                        <th style="min-width: 100px;">Selisih</th>
                                        <th style="min-width: 120px;">Status Uji Sistem</th>
                                        <th style="min-width: 140px;">Kondisi / Keusangan</th>
                                        <th style="min-width: 180px;">Kriteria Tidak Dipenuhi</th>
                                        <th style="min-width: 180px;">Penyebab</th>
                                        <th style="min-width: 180px;">Dampak</th>
                                        <th style="min-width: 80px;">Impact (1-5)</th>
                                        <th style="min-width: 90px;">Likelihood (1-5)</th>
                                        <th style="min-width: 90px;">Critical Trigger</th>
                                        <th style="min-width: 90px;">Risk Score</th>
                                        <th style="min-width: 100px;">Risk Level</th>
                                        <th style="min-width: 110px;">Perlu Klarifikasi</th>
                                        <th style="min-width: 160px;">Referensi Bukti</th>
                                        <th style="min-width: 180px;">Simpulan Profesional RA</th>
                                        <th style="min-width: 130px;">Tanggal Uji</th>
                                        <th style="min-width: 140px;">RA Pelaksana</th>
                                        <th class="bg-warning text-dark" style="min-width: 140px;">Reviewer</th>
                                        <th class="bg-warning text-dark" style="min-width: 150px;">Status Review</th>
                                        <th class="bg-warning text-dark" style="min-width: 180px;">Validasi</th>
                                        <th style="min-width: 120px;">Siap Register Temuan</th>
                                        <th style="min-width: 110px;">Exception Aktif</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyBarangCetakan">
                                    @php $noUrutBct = 1; @endphp
                                    @foreach($barangCetakanItems as $index => $item)
                                        @php
                                            $detailId = $item['id'] ?? ($index + 1);
                                            $defaultProcId = $item['procedure_id'] ?? ('FLD-WK-' . $kodeUnit . '-' . date('Ymd') . '-BCT-' . str_pad($noUrutBct, 3, '0', STR_PAD_LEFT));
                                            $isExc = ($item['status_uji'] ?? '') === 'EXCEPTION';
                                            $statusClass = match($item['status_uji'] ?? 'BELUM DIUJI') {
                                                'SESUAI' => 'text-success',
                                                'EXCEPTION' => 'text-danger',
                                                'TIDAK BERLAKU' => 'text-secondary',
                                                default => 'text-muted'
                                            };
                                            $riskColor = match($item['severity_level'] ?? '') {
                                                'Kritis' => 'text-danger',
                                                'Tinggi' => 'text-warning',
                                                'Sedang' => 'text-primary',
                                                'Rendah' => 'text-success',
                                                default => 'text-muted'
                                            };
                                        @endphp
                                        <tr class="data-row bct-row" data-type="bct" data-index="{{ $index }}">
                                            <!-- 1. No -->
                                            <td class="text-center fw-bold row-no">{{ $noUrutBct++ }}</td>

                                            <!-- 2. Procedure ID -->
                                            <td>
                                                <input type="text" name="barang_cetakan_details[{{ $index }}][procedure_id]" class="form-control form-control-sm bg-light border-0 fw-bold font-monospace" value="{{ $defaultProcId }}" readonly>
                                            </td>

                                            <!-- 3. Kategori -->
                                            <td>
                                                <input type="text" name="barang_cetakan_details[{{ $index }}][kategori]" class="form-control form-control-sm bg-light border-0 fw-bold" value="{{ $item['kategori'] ?? 'Formulir' }}" readonly>
                                            </td>

                                            <!-- 4. Nama Barang Cetakan -->
                                            <td>
                                                <div class="fw-bold">{{ $item['nama_barang'] }}</div>
                                                <input type="hidden" name="barang_cetakan_details[{{ $index }}][nama_barang]" value="{{ $item['nama_barang'] }}">
                                            </td>

                                            <!-- 5. Satuan -->
                                            <td>
                                                <input type="text" name="barang_cetakan_details[{{ $index }}][satuan]" class="form-control form-control-sm text-center bg-light border-0" value="{{ $item['satuan'] ?? 'Bh' }}" readonly>
                                            </td>

                                            <!-- 6. Stok Laporan / Listing -->
                                            <td>
                                                <input type="number" step="1" name="barang_cetakan_details[{{ $index }}][stok_sistem]" class="form-control form-control-sm text-end bct-stok" placeholder="0" value="{{ $item['stok_sistem'] ?? 0 }}">
                                            </td>

                                            <!-- 7. Hasil Hitung Fisik -->
                                            <td>
                                                <input type="number" step="1" name="barang_cetakan_details[{{ $index }}][hasil_fisik]" class="form-control form-control-sm text-end bct-fisik" placeholder="0" value="{{ $item['hasil_fisik'] ?? '' }}">
                                            </td>

                                            <!-- 8. Selisih -->
                                            <td>
                                                <input type="text" name="barang_cetakan_details[{{ $index }}][selisih]" class="form-control form-control-sm text-end bct-selisih border-0 bg-transparent fw-bold" readonly value="{{ $item['selisih'] ?? '' }}">
                                            </td>

                                            <!-- 9. Status Uji Sistem -->
                                            <td>
                                                <input type="text" name="barang_cetakan_details[{{ $index }}][status_uji]" class="form-control form-control-sm bct-status border-0 bg-transparent fw-bold text-center {{ $statusClass }}" readonly value="{{ $item['status_uji'] ?? 'BELUM DIUJI' }}">
                                            </td>

                                            <!-- 10. Kondisi / Keusangan -->
                                            <td>
                                                <select name="barang_cetakan_details[{{ $index }}][kondisi]" class="form-select form-select-sm bct-kondisi">
                                                    <option value="Baik" {{ ($item['kondisi'] ?? '') === 'Baik' ? 'selected' : '' }}>Baik</option>
                                                    <option value="Outdated" {{ ($item['kondisi'] ?? '') === 'Outdated' ? 'selected' : '' }}>Outdated</option>
                                                    <option value="Rusak" {{ ($item['kondisi'] ?? '') === 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                                    <option value="Tidak Baik" {{ ($item['kondisi'] ?? '') === 'Tidak Baik' ? 'selected' : '' }}>Tidak Baik</option>
                                                    <option value="Tidak Berlaku" {{ ($item['kondisi'] ?? '') === 'Tidak Berlaku' ? 'selected' : '' }}>Tidak Berlaku</option>
                                                </select>
                                            </td>

                                            <!-- 11. Kriteria yang Tidak Dipenuhi -->
                                            <td><input type="text" name="barang_cetakan_details[{{ $index }}][kriteria]" class="form-control form-control-sm exc-field" placeholder="Kriteria..." value="{{ $item['kriteria'] ?? '' }}" {{ $isExc ? '' : 'disabled' }}></td>

                                            <!-- 12. Penyebab -->
                                            <td><input type="text" name="barang_cetakan_details[{{ $index }}][penyebab]" class="form-control form-control-sm exc-field" placeholder="Penyebab..." value="{{ $item['penyebab'] ?? '' }}" {{ $isExc ? '' : 'disabled' }}></td>

                                            <!-- 13. Dampak -->
                                            <td><input type="text" name="barang_cetakan_details[{{ $index }}][dampak]" class="form-control form-control-sm exc-field" placeholder="Dampak..." value="{{ $item['dampak'] ?? '' }}" {{ $isExc ? '' : 'disabled' }}></td>

                                            <!-- 14. Impact (1-5) -->
                                            <td>
                                                <select name="barang_cetakan_details[{{ $index }}][impact]" class="form-select form-select-sm exc-field impact-sel" {{ $isExc ? '' : 'disabled' }}>
                                                    @for($val=1; $val<=5; $val++)
                                                        <option value="{{ $val }}" {{ ($item['impact'] ?? 1) == $val ? 'selected' : '' }}>{{ $val }}</option>
                                                    @endfor
                                                </select>
                                            </td>

                                            <!-- 15. Likelihood (1-5) -->
                                            <td>
                                                <select name="barang_cetakan_details[{{ $index }}][likelihood]" class="form-select form-select-sm exc-field likelihood-sel" {{ $isExc ? '' : 'disabled' }}>
                                                    @for($val=1; $val<=5; $val++)
                                                        <option value="{{ $val }}" {{ ($item['likelihood'] ?? 1) == $val ? 'selected' : '' }}>{{ $val }}</option>
                                                    @endfor
                                                </select>
                                            </td>

                                            <!-- 16. Critical Trigger -->
                                            <td>
                                                <select name="barang_cetakan_details[{{ $index }}][critical_trigger]" class="form-select form-select-sm exc-field critical-sel" {{ $isExc ? '' : 'disabled' }}>
                                                    <option value="Tidak" {{ ($item['critical_trigger'] ?? 'Tidak') === 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                                    <option value="Ya" {{ ($item['critical_trigger'] ?? '') === 'Ya' ? 'selected' : '' }}>Ya</option>
                                                </select>
                                            </td>

                                            <!-- 17. Risk Score -->
                                            <td><input type="text" name="barang_cetakan_details[{{ $index }}][risk_score]" class="form-control form-control-sm risk-score border-0 bg-transparent text-center fw-bold" readonly value="{{ $item['risk_score'] ?? '' }}"></td>

                                            <!-- 18. Risk Level -->
                                            <td><input type="text" name="barang_cetakan_details[{{ $index }}][severity_level]" class="form-control form-control-sm risk-level border-0 bg-transparent text-center fw-bold {{ $riskColor }}" readonly value="{{ $item['severity_level'] ?? '' }}"></td>

                                            <!-- 19. Perlu Klarifikasi -->
                                            <td><input type="text" name="barang_cetakan_details[{{ $index }}][perlu_klarifikasi]" class="form-control form-control-sm bct-perlu-klarifikasi border-0 bg-transparent text-center fw-bold" readonly value="{{ $item['perlu_klarifikasi'] ?? ($isExc ? 'Ya' : 'Tidak') }}"></td>

                                            <!-- 20. Referensi Bukti -->
                                            <td>
                                                <input type="file" name="barang_cetakan_details[{{ $index }}][bukti]" class="form-control form-control-sm exc-field" {{ $isExc ? '' : 'disabled' }}>
                                                @if(!empty($item['bukti_path']))
                                                    <a href="{{ asset('storage/' . $item['bukti_path']) }}" target="_blank" class="badge bg-info text-decoration-none mt-1 d-inline-block"><i class="bi bi-file-earmark-image"></i> Lihat Bukti</a>
                                                @endif
                                            </td>

                                            <!-- 21. Simpulan Profesional RA -->
                                            <td><input type="text" name="barang_cetakan_details[{{ $index }}][simpulan_ra]" class="form-control form-control-sm" placeholder="Simpulan..." value="{{ $item['simpulan_ra'] ?? '' }}"></td>

                                            <!-- 22. Tanggal Uji -->
                                            <td><input type="date" name="barang_cetakan_details[{{ $index }}][tanggal_uji]" class="form-control form-control-sm" value="{{ $item['tanggal_uji'] ?? date('Y-m-d') }}"></td>

                                            <!-- 23. RA Pelaksana -->
                                            <td><input type="text" name="barang_cetakan_details[{{ $index }}][ra_pelaksana]" class="form-control form-control-sm bg-light" value="{{ $item['ra_pelaksana'] ?? (auth()->user()->name ?? 'RA') }}" readonly></td>

                                            <!-- 24. Reviewer (Admin) -->
                                            <td class="bg-warning bg-opacity-10">
                                                @if($isAdmin)
                                                    <input type="text" class="form-control form-control-sm bg-light border-warning" value="{{ auth()->user()->name ?? 'Admin Reviewer' }}" readonly>
                                                @else
                                                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $item['reviewer_id'] ?? '-' }}" readonly>
                                                @endif
                                            </td>

                                            <!-- 25. Status Review (Admin) -->
                                            <td class="bg-warning bg-opacity-10">
                                                @if($isAdmin)
                                                    <select name="barang_cetakan_review[{{ $detailId }}][status_review]" class="form-select form-select-sm border-warning admin-review-status">
                                                        <option value="Belum Direviu" {{ ($item['status_review'] ?? '') === 'Belum Direviu' ? 'selected' : '' }}>Belum Direviu</option>
                                                        <option value="Dikembalikan" {{ ($item['status_review'] ?? '') === 'Dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                                                        <option value="Disetujui" {{ ($item['status_review'] ?? '') === 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                                                    </select>
                                                @else
                                                    <span class="badge bg-secondary">{{ $item['status_review'] ?? 'Belum Direviu' }}</span>
                                                @endif
                                            </td>

                                            <!-- 26. Validasi (Admin) -->
                                            <td class="bg-warning bg-opacity-10">
                                                @if($isAdmin)
                                                    <select name="barang_cetakan_review[{{ $detailId }}][validasi]" class="form-select form-select-sm border-warning admin-validasi-sel">
                                                        <option value="">Pilih Validasi...</option>
                                                        <option value="VALID" {{ strtoupper($item['validasi'] ?? '') === 'VALID' ? 'selected' : '' }}>VALID</option>
                                                        <option value="INVALID" {{ strtoupper($item['validasi'] ?? '') === 'INVALID' ? 'selected' : '' }}>INVALID</option>
                                                        <option value="CATATAN" {{ strtoupper($item['validasi'] ?? '') === 'CATATAN' ? 'selected' : '' }}>CATATAN</option>
                                                    </select>
                                                @else
                                                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $item['validasi'] ?? '-' }}" readonly>
                                                @endif
                                            </td>

                                            <!-- 27. Siap Register Temuan -->
                                            <td>
                                                <input type="text" class="form-control form-control-sm bct-siap-register border-0 bg-transparent text-center fw-bold" readonly value="{{ $item['siap_register'] ?? 'Tidak' }}">
                                            </td>

                                            <!-- 28. Exception Aktif -->
                                            <td>
                                                <input type="text" class="form-control form-control-sm bct-exc-aktif border-0 bg-transparent text-center fw-bold {{ $isExc ? 'text-danger' : 'text-muted' }}" readonly value="{{ $isExc ? 'Ya' : 'Tidak' }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="d-flex justify-content-between align-items-center p-3 px-4 border-top bg-light bg-opacity-50">
                    <div class="text-muted small">
                        <i class="bi bi-shield-check text-primary me-1"></i> Data pemeriksaan fisik tersimpan dan terintegrasi otomatis ke register mingguan unit.
                    </div>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="btnSubmitFinal" style="background-color: var(--primary-blue); border-color: var(--primary-blue); border-radius: 8px; font-size: 0.88rem; padding: 0.55rem 1.4rem;">
                        <i class="bi bi-save me-1"></i> {{ $isAdmin ? 'Simpan Review Admin' : 'Submit KKA Fisik' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const kodeUnit = "{{ $kodeUnit ?? '000' }}";
    const dateCode = "{{ date('Ymd') }}";
    const isAdmin = {{ $isAdmin ? 'true' : 'false' }};

    // 1. RISK SCORE & RISK LEVEL CALCULATION
    function calculateRisk(row) {
        const impactSel = row.querySelector('.impact-sel');
        const likelihoodSel = row.querySelector('.likelihood-sel');
        const criticalSel = row.querySelector('.critical-sel');
        if (!impactSel || !likelihoodSel) return;

        const impact = parseInt(impactSel.value) || 1;
        const likelihood = parseInt(likelihoodSel.value) || 1;
        const isCritical = criticalSel ? criticalSel.value === 'Ya' : false;
        
        const score = impact * likelihood;
        const scoreInput = row.querySelector('.risk-score');
        if (scoreInput) scoreInput.value = score;
        
        let level = 'Rendah';
        let color = 'text-success';
        if (isCritical || score >= 15) {
            level = 'Kritis'; color = 'text-danger';
        } else if (score >= 10) {
            level = 'Tinggi'; color = 'text-warning';
        } else if (score >= 5) {
            level = 'Sedang'; color = 'text-primary';
        }
        
        const levelInput = row.querySelector('.risk-level');
        if (levelInput) {
            levelInput.value = level;
            levelInput.className = 'form-control form-control-sm risk-level border-0 bg-transparent text-center fw-bold ' + color;
        }
    }

    // 2. UNIVERSAL STATUS & EXCEPTION HANDLER
    function updateRowStatus(row, status) {
        const statusInput = row.querySelector('.status-uji, .atm-status, .bct-status');
        const excFields = row.querySelectorAll('.exc-field');
        const perluKlarifInput = row.querySelector('.atm-perlu-klarifikasi, .bct-perlu-klarifikasi');
        const excAktifInput = row.querySelector('.atm-exc-aktif, .bct-exc-aktif');
        const siapRegInput = row.querySelector('.atm-siap-register, .bct-siap-register');
        const validasiInput = row.querySelector('.admin-validasi-sel');

        if (!statusInput) return;

        statusInput.value = status;
        statusInput.classList.remove('text-success', 'text-danger', 'text-secondary', 'text-muted');

        if (status === 'EXCEPTION') {
            statusInput.classList.add('text-danger');
            
            excFields.forEach(f => {
                if (!isAdmin) f.disabled = false;
                f.closest('td')?.classList.remove('col-disabled');
            });

            if (perluKlarifInput) { perluKlarifInput.value = 'Ya'; perluKlarifInput.className = 'form-control form-control-sm border-0 bg-transparent text-center fw-bold text-danger'; }
            if (excAktifInput) { excAktifInput.value = 'Ya'; excAktifInput.className = 'form-control form-control-sm border-0 bg-transparent text-center fw-bold text-danger'; }

            calculateRisk(row);

        } else if (status === 'SESUAI') {
            statusInput.classList.add('text-success');

            excFields.forEach(f => {
                f.disabled = true;
                f.closest('td')?.classList.add('col-disabled');
            });

            if (perluKlarifInput) { perluKlarifInput.value = 'Tidak'; perluKlarifInput.className = 'form-control form-control-sm border-0 bg-transparent text-center fw-bold text-muted'; }
            if (excAktifInput) { excAktifInput.value = 'Tidak'; excAktifInput.className = 'form-control form-control-sm border-0 bg-transparent text-center fw-bold text-muted'; }

            const sc = row.querySelector('.risk-score');
            const lv = row.querySelector('.risk-level');
            if (sc) sc.value = '';
            if (lv) { lv.value = ''; lv.className = 'form-control form-control-sm risk-level border-0 bg-transparent text-center fw-bold text-muted'; }

        } else if (status === 'TIDAK BERLAKU') {
            statusInput.classList.add('text-secondary');

            excFields.forEach(f => {
                f.disabled = true;
                f.closest('td')?.classList.add('col-disabled');
            });

            if (perluKlarifInput) { perluKlarifInput.value = 'Tidak'; perluKlarifInput.className = 'form-control form-control-sm border-0 bg-transparent text-center fw-bold text-muted'; }
            if (excAktifInput) { excAktifInput.value = 'Tidak'; excAktifInput.className = 'form-control form-control-sm border-0 bg-transparent text-center fw-bold text-muted'; }

            const sc = row.querySelector('.risk-score');
            const lv = row.querySelector('.risk-level');
            if (sc) sc.value = '';
            if (lv) { lv.value = ''; lv.className = 'form-control form-control-sm risk-level border-0 bg-transparent text-center fw-bold text-muted'; }

        } else {
            // BELUM DIUJI
            statusInput.classList.add('text-muted');

            excFields.forEach(f => {
                f.disabled = true;
                f.closest('td')?.classList.add('col-disabled');
            });

            if (perluKlarifInput) perluKlarifInput.value = 'Tidak';
            if (excAktifInput) excAktifInput.value = 'Tidak';
            const sc = row.querySelector('.risk-score');
            const lv = row.querySelector('.risk-level');
            if (sc) sc.value = '';
            if (lv) lv.value = '';
        }

        // Siap Register Temuan (Otomatis: Ya jika Exception Aktif = Ya dan Validasi = VALID)
        if (siapRegInput) {
            const isExcNow = excAktifInput ? excAktifInput.value === 'Ya' : (status === 'EXCEPTION');
            const valVal = validasiInput ? validasiInput.value.toUpperCase() : '';
            const isValid = ['VALID', 'DISETUJUI'].includes(valVal);
            if (isExcNow && isValid) {
                siapRegInput.value = 'Ya';
                siapRegInput.className = 'form-control form-control-sm border-0 bg-transparent text-center fw-bold text-success';
            } else {
                siapRegInput.value = 'Tidak';
                siapRegInput.className = 'form-control form-control-sm border-0 bg-transparent text-center fw-bold text-muted';
            }
        }

        updateSummaryCounts();
    }

    // 3. TAB 1 (CASH) & TAB 3 (STOK CEK/BG) CALCULATION
    function initCashAndStokListeners(context = document) {
        context.querySelectorAll('.hasil-fisik').forEach(input => {
            input.addEventListener('input', function() {
                const row = this.closest('tr');
                const saldoSistem = parseFloat(row.querySelector('.saldo-sistem')?.value) || 0;
                const hasilFisik = parseFloat(this.value);
                const selisihInput = row.querySelector('.selisih-display');
                
                if (!isNaN(hasilFisik)) {
                    const selisih = saldoSistem - hasilFisik;
                    if (selisihInput) selisihInput.value = selisih;
                    updateRowStatus(row, selisih !== 0 ? 'EXCEPTION' : 'SESUAI');
                } else {
                    if (selisihInput) selisihInput.value = '';
                    updateRowStatus(row, 'BELUM DIUJI');
                }
            });
        });
    }

    // TAB 2 (OBSERVASI) DROPDOWN
    function initObsListeners(context = document) {
        context.querySelectorAll('.hasil-obs').forEach(select => {
            select.addEventListener('change', function() {
                const row = this.closest('tr');
                if (this.value === 'Tidak Sesuai') {
                    updateRowStatus(row, 'EXCEPTION');
                } else if (this.value === 'Sesuai') {
                    updateRowStatus(row, 'SESUAI');
                } else if (this.value === 'Tidak Berlaku') {
                    updateRowStatus(row, 'TIDAK BERLAKU');
                } else {
                    updateRowStatus(row, 'BELUM DIUJI');
                }
            });
        });
    }

    // 4. TAB 4: REKONSILIASI KARTU ATM CALCULATION
    function calculateAtmRow(row) {
        const group = row.dataset.group;
        
        if (group === 'stock') {
            const saldoInput = row.querySelector('.atm-saldo');
            const fisikInput = row.querySelector('.atm-fisik');
            const kondisiSel = row.querySelector('.atm-kondisi');
            const selisihInput = row.querySelector('.atm-selisih');

            const saldoVal = saldoInput ? saldoInput.value.trim() : '';
            const fisikVal = fisikInput ? fisikInput.value.trim() : '';
            const kondisi = kondisiSel ? kondisiSel.value : 'Baik';

            if (kondisi === 'Tidak Berlaku') {
                if (selisihInput) selisihInput.value = 'N/A';
                updateRowStatus(row, 'TIDAK BERLAKU');
                return;
            }

            if (saldoVal === '' || fisikVal === '') {
                if (selisihInput) selisihInput.value = '';
                updateRowStatus(row, 'BELUM DIUJI');
                return;
            }

            const saldoNum = parseFloat(saldoVal) || 0;
            const fisikNum = parseFloat(fisikVal) || 0;
            const selisih = fisikNum - saldoNum;
            if (selisihInput) selisihInput.value = selisih;

            const isSelisih = Math.abs(selisih) >= 0.01;
            const isKondisiBuruk = ['Outdated', 'Rusak', 'Tidak Baik'].includes(kondisi);

            if (isSelisih || isKondisiBuruk) {
                updateRowStatus(row, 'EXCEPTION');
            } else {
                updateRowStatus(row, 'SESUAI');
            }

        } else if (group === 'control') {
            const kondisiSel = row.querySelector('.atm-ctrl-kondisi');
            const kondisi = kondisiSel ? kondisiSel.value : 'Sesuai';

            if (kondisi === 'Sesuai') {
                updateRowStatus(row, 'SESUAI');
            } else if (kondisi === 'Tidak Sesuai') {
                updateRowStatus(row, 'EXCEPTION');
            } else if (kondisi === 'Tidak Berlaku') {
                updateRowStatus(row, 'TIDAK BERLAKU');
            } else {
                updateRowStatus(row, 'BELUM DIUJI');
            }
        }
    }

    function initAtmListeners(context = document) {
        context.querySelectorAll('.atm-saldo, .atm-fisik').forEach(el => {
            el.addEventListener('input', function() {
                calculateAtmRow(this.closest('tr'));
            });
        });

        context.querySelectorAll('.atm-kondisi, .atm-ctrl-kondisi').forEach(el => {
            el.addEventListener('change', function() {
                calculateAtmRow(this.closest('tr'));
            });
        });
    }

    // 5. TAB 5: REKONSILIASI BARANG CETAKAN CALCULATION
    function calculateBctRow(row) {
        const stokInput = row.querySelector('.bct-stok');
        const fisikInput = row.querySelector('.bct-fisik');
        const kondisiSel = row.querySelector('.bct-kondisi');
        const selisihInput = row.querySelector('.bct-selisih');

        const stokVal = stokInput ? stokInput.value.trim() : '0';
        const fisikVal = fisikInput ? fisikInput.value.trim() : '';
        const kondisi = kondisiSel ? kondisiSel.value : 'Baik';

        if (kondisi === 'Tidak Berlaku') {
            if (selisihInput) selisihInput.value = '0';
            updateRowStatus(row, 'TIDAK BERLAKU');
            return;
        }

        if (fisikVal === '') {
            if (selisihInput) selisihInput.value = '';
            updateRowStatus(row, 'BELUM DIUJI');
            return;
        }

        const stokNum = parseFloat(stokVal) || 0;
        const fisikNum = parseFloat(fisikVal) || 0;
        const selisih = fisikNum - stokNum;
        if (selisihInput) selisihInput.value = selisih;

        const isSelisih = Math.abs(selisih) >= 0.01;
        const isKondisiBuruk = ['Outdated', 'Rusak', 'Tidak Baik'].includes(kondisi);

        if (isSelisih || isKondisiBuruk) {
            updateRowStatus(row, 'EXCEPTION');
        } else if (kondisi === 'Baik' && !isSelisih) {
            updateRowStatus(row, 'SESUAI');
        } else {
            updateRowStatus(row, 'BELUM DIUJI');
        }
    }

    function initBctListeners(context = document) {
        context.querySelectorAll('.bct-stok, .bct-fisik').forEach(el => {
            el.addEventListener('input', function() {
                calculateBctRow(this.closest('tr'));
            });
        });

        context.querySelectorAll('.bct-kondisi').forEach(el => {
            el.addEventListener('change', function() {
                calculateBctRow(this.closest('tr'));
            });
        });
    }

    // 6. DYNAMIC ROW ADDITION
    // Tab 4: + Tambah Baris Batch Kartu (Maksimal 30 baris)
    const btnAddAtm = document.getElementById('btnAddAtmRow');
    if (btnAddAtm) {
        btnAddAtm.addEventListener('click', function() {
            const tbody = document.getElementById('tbodyKartuAtm');
            const currentRows = tbody.querySelectorAll('tr.atm-row').length;
            if (currentRows >= 30) {
                alert('Maksimal 30 baris kartu ATM sesuai kapasitas Excel (baris 15-44).');
                return;
            }

            const newIndex = currentRows + 100;
            const newNo = currentRows + 1;
            const procId = 'FLD-WK-' + kodeUnit + '-' + dateCode + '-KATREC-' + String(newNo).padStart(3, '0');

            const tr = document.createElement('tr');
            tr.className = 'data-row atm-row';
            tr.dataset.type = 'atm';
            tr.dataset.group = 'stock';
            tr.dataset.index = newIndex;

            tr.innerHTML = `
                <td class="text-center fw-bold row-no">${newNo}</td>
                <td><input type="text" name="kartu_atm_details[${newIndex}][procedure_id]" class="form-control form-control-sm bg-light border-0 fw-bold font-monospace" value="${procId}" readonly></td>
                <td><span class="badge bg-primary">Persediaan Kartu ATM</span><input type="hidden" name="kartu_atm_details[${newIndex}][sub_area]" value="Persediaan Kartu ATM"></td>
                <td><input type="text" name="kartu_atm_details[${newIndex}][jenis_kartu]" class="form-control form-control-sm atm-jenis-kartu" placeholder="Contoh: Kartu ATM GPN/Silver Batch ${newNo}"></td>
                <td><input type="text" name="kartu_atm_details[${newIndex}][keterangan_lokasi]" class="form-control form-control-sm" placeholder="Contoh: Khasanah / Lemari Besi"></td>
                <td><input type="number" step="1" name="kartu_atm_details[${newIndex}][saldo_nominatif]" class="form-control form-control-sm text-end atm-saldo" placeholder="0"></td>
                <td><input type="number" step="1" name="kartu_atm_details[${newIndex}][hasil_fisik]" class="form-control form-control-sm text-end atm-fisik" placeholder="0"></td>
                <td><input type="text" name="kartu_atm_details[${newIndex}][selisih]" class="form-control form-control-sm text-end atm-selisih border-0 bg-transparent fw-bold" readonly></td>
                <td><input type="text" name="kartu_atm_details[${newIndex}][status_uji]" class="form-control form-control-sm atm-status border-0 bg-transparent fw-bold text-center text-muted" readonly value="BELUM DIUJI"></td>
                <td>
                    <select name="kartu_atm_details[${newIndex}][kondisi]" class="form-select form-select-sm atm-kondisi">
                        <option value="Baik" selected>Baik</option>
                        <option value="Rusak">Rusak</option>
                        <option value="Outdated">Outdated</option>
                        <option value="Tidak Baik">Tidak Baik</option>
                        <option value="Tidak Berlaku">Tidak Berlaku</option>
                    </select>
                </td>
                <td><input type="text" name="kartu_atm_details[${newIndex}][kriteria]" class="form-control form-control-sm exc-field" placeholder="Kriteria..." disabled></td>
                <td><input type="text" name="kartu_atm_details[${newIndex}][penyebab]" class="form-control form-control-sm exc-field" placeholder="Penyebab..." disabled></td>
                <td><input type="text" name="kartu_atm_details[${newIndex}][dampak]" class="form-control form-control-sm exc-field" placeholder="Dampak..." disabled></td>
                <td><select name="kartu_atm_details[${newIndex}][impact]" class="form-select form-select-sm exc-field impact-sel" disabled><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option></select></td>
                <td><select name="kartu_atm_details[${newIndex}][likelihood]" class="form-select form-select-sm exc-field likelihood-sel" disabled><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option></select></td>
                <td><select name="kartu_atm_details[${newIndex}][critical_trigger]" class="form-select form-select-sm exc-field critical-sel" disabled><option value="Tidak" selected>Tidak</option><option value="Ya">Ya</option></select></td>
                <td><input type="text" name="kartu_atm_details[${newIndex}][risk_score]" class="form-control form-control-sm risk-score border-0 bg-transparent text-center fw-bold" readonly></td>
                <td><input type="text" name="kartu_atm_details[${newIndex}][severity_level]" class="form-control form-control-sm risk-level border-0 bg-transparent text-center fw-bold" readonly></td>
                <td><input type="text" name="kartu_atm_details[${newIndex}][perlu_klarifikasi]" class="form-control form-control-sm atm-perlu-klarifikasi border-0 bg-transparent text-center fw-bold" readonly value="Tidak"></td>
                <td><input type="file" name="kartu_atm_details[${newIndex}][bukti]" class="form-control form-control-sm exc-field" disabled></td>
                <td><input type="text" name="kartu_atm_details[${newIndex}][simpulan_ra]" class="form-control form-control-sm" placeholder="Simpulan..."></td>
                <td><input type="date" name="kartu_atm_details[${newIndex}][tanggal_uji]" class="form-control form-control-sm" value="{{ date('Y-m-d') }}"></td>
                <td><input type="text" name="kartu_atm_details[${newIndex}][ra_pelaksana]" class="form-control form-control-sm bg-light" value="{{ auth()->user()->name ?? 'RA' }}" readonly></td>
                <td class="bg-warning bg-opacity-10"><input type="text" class="form-control form-control-sm bg-light" value="-" readonly></td>
                <td class="bg-warning bg-opacity-10"><span class="badge bg-secondary">Belum Direviu</span></td>
                <td class="bg-warning bg-opacity-10"><input type="text" class="form-control form-control-sm bg-light" value="-" readonly></td>
                <td><input type="text" class="form-control form-control-sm atm-siap-register border-0 bg-transparent text-center fw-bold" readonly value="Tidak"></td>
                <td><input type="text" class="form-control form-control-sm atm-exc-aktif border-0 bg-transparent text-center fw-bold text-muted" readonly value="Tidak"></td>
            `;

            // Insert before control rows if any, or at the end of stock group
            const firstControlRow = tbody.querySelector('tr[data-group="control"]');
            if (firstControlRow) {
                tbody.insertBefore(tr, firstControlRow);
            } else {
                tbody.appendChild(tr);
            }

            // Re-index row numbers
            let rNo = 1;
            tbody.querySelectorAll('tr.atm-row').forEach(row => {
                row.querySelector('.row-no').innerText = rNo++;
            });

            initAtmListeners(tr);
            updateSummaryCounts();
        });
    }

    // Tab 5: + Tambah Item Barang Cetakan
    const btnAddBct = document.getElementById('btnAddBctRow');
    if (btnAddBct) {
        btnAddBct.addEventListener('click', function() {
            const tbody = document.getElementById('tbodyBarangCetakan');
            const currentRows = tbody.querySelectorAll('tr.bct-row').length;
            const newIndex = currentRows + 100;
            const newNo = currentRows + 1;
            const procId = 'FLD-WK-' + kodeUnit + '-' + dateCode + '-BCT-' + String(newNo).padStart(3, '0');

            const tr = document.createElement('tr');
            tr.className = 'data-row bct-row';
            tr.dataset.type = 'bct';
            tr.dataset.index = newIndex;

            tr.innerHTML = `
                <td class="text-center fw-bold row-no">${newNo}</td>
                <td><input type="text" name="barang_cetakan_details[${newIndex}][procedure_id]" class="form-control form-control-sm bg-light border-0 fw-bold font-monospace" value="${procId}" readonly></td>
                <td>
                    <select name="barang_cetakan_details[${newIndex}][kategori]" class="form-select form-select-sm">
                        <option value="Formulir">Formulir</option>
                        <option value="Buku Tabungan">Buku Tabungan</option>
                        <option value="Kertas">Kertas</option>
                        <option value="Amplop">Amplop</option>
                        <option value="Perlengkapan">Perlengkapan</option>
                        <option value="Prioritas">Prioritas</option>
                    </select>
                </td>
                <td><input type="text" name="barang_cetakan_details[${newIndex}][nama_barang]" class="form-control form-control-sm" placeholder="Nama Barang Cetakan Baru" required></td>
                <td>
                    <select name="barang_cetakan_details[${newIndex}][satuan]" class="form-select form-select-sm">
                        <option value="Bh">Bh</option>
                        <option value="Rim">Rim</option>
                        <option value="Pack">Pack</option>
                        <option value="Dos">Dos</option>
                    </select>
                </td>
                <td><input type="number" step="1" name="barang_cetakan_details[${newIndex}][stok_sistem]" class="form-control form-control-sm text-end bct-stok" placeholder="0" value="0"></td>
                <td><input type="number" step="1" name="barang_cetakan_details[${newIndex}][hasil_fisik]" class="form-control form-control-sm text-end bct-fisik" placeholder="0"></td>
                <td><input type="text" name="barang_cetakan_details[${newIndex}][selisih]" class="form-control form-control-sm text-end bct-selisih border-0 bg-transparent fw-bold" readonly></td>
                <td><input type="text" name="barang_cetakan_details[${newIndex}][status_uji]" class="form-control form-control-sm bct-status border-0 bg-transparent fw-bold text-center text-muted" readonly value="BELUM DIUJI"></td>
                <td>
                    <select name="barang_cetakan_details[${newIndex}][kondisi]" class="form-select form-select-sm bct-kondisi">
                        <option value="Baik" selected>Baik</option>
                        <option value="Outdated">Outdated</option>
                        <option value="Rusak">Rusak</option>
                        <option value="Tidak Baik">Tidak Baik</option>
                        <option value="Tidak Berlaku">Tidak Berlaku</option>
                    </select>
                </td>
                <td><input type="text" name="barang_cetakan_details[${newIndex}][kriteria]" class="form-control form-control-sm exc-field" placeholder="Kriteria..." disabled></td>
                <td><input type="text" name="barang_cetakan_details[${newIndex}][penyebab]" class="form-control form-control-sm exc-field" placeholder="Penyebab..." disabled></td>
                <td><input type="text" name="barang_cetakan_details[${newIndex}][dampak]" class="form-control form-control-sm exc-field" placeholder="Dampak..." disabled></td>
                <td><select name="barang_cetakan_details[${newIndex}][impact]" class="form-select form-select-sm exc-field impact-sel" disabled><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option></select></td>
                <td><select name="barang_cetakan_details[${newIndex}][likelihood]" class="form-select form-select-sm exc-field likelihood-sel" disabled><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option></select></td>
                <td><select name="barang_cetakan_details[${newIndex}][critical_trigger]" class="form-select form-select-sm exc-field critical-sel" disabled><option value="Tidak" selected>Tidak</option><option value="Ya">Ya</option></select></td>
                <td><input type="text" name="barang_cetakan_details[${newIndex}][risk_score]" class="form-control form-control-sm risk-score border-0 bg-transparent text-center fw-bold" readonly></td>
                <td><input type="text" name="barang_cetakan_details[${newIndex}][severity_level]" class="form-control form-control-sm risk-level border-0 bg-transparent text-center fw-bold" readonly></td>
                <td><input type="text" name="barang_cetakan_details[${newIndex}][perlu_klarifikasi]" class="form-control form-control-sm bct-perlu-klarifikasi border-0 bg-transparent text-center fw-bold" readonly value="Tidak"></td>
                <td><input type="file" name="barang_cetakan_details[${newIndex}][bukti]" class="form-control form-control-sm exc-field" disabled></td>
                <td><input type="text" name="barang_cetakan_details[${newIndex}][simpulan_ra]" class="form-control form-control-sm" placeholder="Simpulan..."></td>
                <td><input type="date" name="barang_cetakan_details[${newIndex}][tanggal_uji]" class="form-control form-control-sm" value="{{ date('Y-m-d') }}"></td>
                <td><input type="text" name="barang_cetakan_details[${newIndex}][ra_pelaksana]" class="form-control form-control-sm bg-light" value="{{ auth()->user()->name ?? 'RA' }}" readonly></td>
                <td class="bg-warning bg-opacity-10"><input type="text" class="form-control form-control-sm bg-light" value="-" readonly></td>
                <td class="bg-warning bg-opacity-10"><span class="badge bg-secondary">Belum Direviu</span></td>
                <td class="bg-warning bg-opacity-10"><input type="text" class="form-control form-control-sm bg-light" value="-" readonly></td>
                <td><input type="text" class="form-control form-control-sm bct-siap-register border-0 bg-transparent text-center fw-bold" readonly value="Tidak"></td>
                <td><input type="text" class="form-control form-control-sm bct-exc-aktif border-0 bg-transparent text-center fw-bold text-muted" readonly value="Tidak"></td>
            `;

            tbody.appendChild(tr);
            initBctListeners(tr);
            updateSummaryCounts();
        });
    }

    // 7. SUMMARY COUNTERS
    function updateSummaryCounts() {
        const atmRows = document.querySelectorAll('#tbodyKartuAtm tr.atm-row');
        const bctRows = document.querySelectorAll('#tbodyBarangCetakan tr.bct-row');

        const atmExc = Array.from(atmRows).filter(r => r.querySelector('.atm-status')?.value === 'EXCEPTION').length;
        const bctExc = Array.from(bctRows).filter(r => r.querySelector('.bct-status')?.value === 'EXCEPTION').length;

        const elAtmTotal = document.getElementById('summaryAtmTotal');
        const elAtmExc = document.getElementById('summaryAtmExc');
        const elBctTotal = document.getElementById('summaryBctTotal');
        const elBctExc = document.getElementById('summaryBctExc');

        if (elAtmTotal) elAtmTotal.innerText = atmRows.length;
        if (elAtmExc) elAtmExc.innerText = atmExc > 0 ? (atmExc + ' Exception') : '0 Exception';
        if (elBctTotal) elBctTotal.innerText = bctRows.length;
        if (elBctExc) elBctExc.innerText = bctExc > 0 ? (bctExc + ' Exception') : '0 Exception';
    }

    // Global listener for impact, likelihood, critical trigger
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('impact-sel') || 
            e.target.classList.contains('likelihood-sel') || 
            e.target.classList.contains('critical-sel')) {
            calculateRisk(e.target.closest('tr'));
        }

        // Admin Validasi listener for Siap Register Temuan
        if (e.target.classList.contains('admin-validasi-sel')) {
            const row = e.target.closest('tr');
            const siapInput = row.querySelector('.atm-siap-register, .bct-siap-register');
            const excAktif = row.querySelector('.atm-exc-aktif, .bct-exc-aktif')?.value === 'Ya';
            const isValid = ['VALID', 'DISETUJUI'].includes(e.target.value.toUpperCase());

            if (siapInput) {
                if (excAktif && isValid) {
                    siapInput.value = 'Ya';
                    siapInput.className = 'form-control form-control-sm border-0 bg-transparent text-center fw-bold text-success';
                } else {
                    siapInput.value = 'Tidak';
                    siapInput.className = 'form-control form-control-sm border-0 bg-transparent text-center fw-bold text-muted';
                }
            }
        }
    });

    // 8. INITIALIZE LISTENERS
    initCashAndStokListeners();
    initObsListeners();
    initAtmListeners();
    initBctListeners();

    // Set initial col-disabled styling
    document.querySelectorAll('.exc-field').forEach(f => {
        if (f.disabled) { f.closest('td')?.classList.add('col-disabled'); }
    });

    // 9. ADMIN ROLE RESTRICTIONS (Read-Only for RA Inputs, Enable Admin Review)
    if (isAdmin) {
        document.querySelectorAll('#formKkaFisik input, #formKkaFisik select, #formKkaFisik textarea').forEach(el => {
            const name = el.name || '';
            const isReviewField = name.includes('_review[') || name === 'status_surat' || name === 'catatan_surat';
            
            if (!isReviewField && el.type !== 'hidden') {
                el.readOnly = true;
                if (el.tagName === 'SELECT' || el.type === 'file' || el.type === 'checkbox') {
                    el.disabled = true;
                }
            }
        });
    }

    updateSummaryCounts();
});
</script>

@endsection
