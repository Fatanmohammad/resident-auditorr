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
    .kka-nav-tabs::-webkit-scrollbar { height: 3px; }
    .kka-nav-tabs::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .kka-nav-tabs .nav-item { margin-bottom: -1px; }
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
    .kka-tab-btn:hover { color: var(--primary-blue); background: #f8fafc; }
    .kka-tab-btn.active {
        color: var(--primary-blue);
        border-bottom: 3px solid var(--primary-blue);
        font-weight: 700;
        background: transparent;
    }

    .tab-status-pill {
        font-size: 0.68rem;
        padding: 0.15rem 0.5rem;
        border-radius: 20px;
        font-weight: 600;
        letter-spacing: 0.2px;
        line-height: 1.2;
        white-space: nowrap;
    }

    .table-excel th { background-color: #f8fafc; font-size: 0.74rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.3px; text-align: center; vertical-align: middle; white-space: nowrap; border: 1px solid var(--border-color); padding: 0.6rem 0.5rem; }
    .table-excel td { font-size: 0.83rem; vertical-align: middle; padding: 0.4rem; border: 1px solid var(--border-color); }
    .table-excel input, .table-excel select, .table-excel textarea { font-size: 0.83rem; padding: 0.3rem 0.5rem; border-radius: 6px; }
    .col-disabled { background-color: #f8fafc; }

    /* ATM Card Modern Design */
    .atm-card-item {
        background: #ffffff;
        border: 1.5px solid var(--border-color);
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        margin-bottom: 1.5rem;
        overflow: hidden;
        transition: border-color 0.2s;
    }
    .atm-card-item:hover { border-color: #93c5fd; }
    .atm-card-topbar {
        background: #f8fafc;
        border-bottom: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .atm-ident-body {
        padding: 1rem;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
    }
    .atm-cassette-box {
        padding: 1rem;
        background: #fcfdfe;
    }
    .atm-calc-footer {
        padding: 0.85rem 1rem;
        background: #f8fafc;
        border-top: 1px solid var(--border-color);
    }

    /* Section A and B Containers */
    .section-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .section-card-header {
        background: #f1f5f9;
        border-bottom: 1px solid #cbd5e1;
        padding: 0.75rem 1rem;
        font-weight: 700;
        font-size: 0.88rem;
        color: var(--primary-blue);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>

<div class="container-fluid py-4">

    <!-- Header & Target Info -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <a href="{{ url('/daily-register/kka') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" style="border-radius: 8px; font-weight: 500; font-size: 0.8rem; padding: 0.3rem 0.7rem; border-color: var(--border-color); background: #fff; color: var(--text-dark);">
                    <i class="bi bi-arrow-left"></i> Kembali ke Register Harian
                </a>
                <span class="badge-soft badge-soft-secondary" style="font-size: 0.72rem;">
                    <i class="bi bi-building me-1 text-primary"></i> Unit: {{ $kodeUnit ?? '000' }}
                </span>
                <span class="badge-soft badge-soft-info" style="font-size: 0.72rem;">
                    <i class="bi bi-file-earmark-check me-1"></i> No. Arsip: <span class="fw-bold">{{ $noArsip }}</span>
                </span>
            </div>
            <h4 class="page-title">Tahap 2: KKA Fisik &amp; Rekonsiliasi (Bank Sulteng)</h4>
            <p class="page-subtitle">Sesuai Dokumen Berita Acara &amp; KKA Resmi Bank Sulteng (Model Card ATM, BA Kas Khasanah Olibs, Rekon Kartu Section A/B, Rekon Barang Cetakan).</p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if(isset($id))
            <!-- Dropdown Cetak Arsip PDF Resmi -->
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle fw-bold shadow-sm d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 8px; font-size: 0.82rem; padding: 0.45rem 0.85rem;">
                    <i class="bi bi-printer"></i> Cetak Berita Acara / Arsip PDF
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.83rem;">
                    <li><h6 class="dropdown-header text-uppercase" style="font-size: 0.7rem;">Cetak Per Dokumen</h6></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.print-ba-kas', $id) }}" target="_blank"><i class="bi bi-cash-stack me-2 text-primary"></i>BA Kas Khasanah (Olibs)</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.print-ba-atm', $id) }}" target="_blank"><i class="bi bi-wallet2 me-2 text-info"></i>BA Cash Opname ATM</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.print-kka-kartu', $id) }}" target="_blank"><i class="bi bi-credit-card-2-front me-2 text-warning"></i>KKA Kartu ATM (E.4.3)</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.print-kka-cetakan', $id) }}" target="_blank"><i class="bi bi-printer me-2 text-success"></i>KKA Barang Cetakan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item fw-bold text-primary" href="{{ route('daily-register.physical-kka.print-all', $id) }}" target="_blank"><i class="bi bi-collection me-2"></i>Cetak Berkas Lengkap (Dossier Tahap 2)</a></li>
                </ul>
            </div>
            @endif

            <span class="badge-soft {{ $isAdmin ? 'badge-soft-warning' : 'badge-soft-primary' }} px-3 py-2" style="font-size: 0.8rem;">
                <i class="bi {{ $isAdmin ? 'bi-shield-check' : 'bi-person-badge' }} me-1"></i>
                {{ $isAdmin ? 'Mode: Reviewer / Admin' : 'Mode: Input Lapangan (Resident Auditor)' }}
            </span>
        </div>
    </div>

    <!-- FORM UTAMA -->
    <form id="formKkaFisik" action="{{ $isAdmin ? route('daily-register.physical-kka.review') : route('daily-register.physical-kka.submit') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        @if(isset($id))
            <input type="hidden" name="physical_kka_id" value="{{ $id }}">
        @endif

        <!-- Global Header Inputs: No. Arsip & Posisi Tanggal Rekonsiliasi -->
        <div class="card mb-3 border-0 shadow-sm" style="border-radius: 10px; background: #ffffff;">
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1 text-muted"><i class="bi bi-bookmark-check text-primary me-1"></i>Nomor Arsip Dokumen KKA Fisik</label>
                        <input type="text" name="no_arsip" class="form-control form-control-sm font-monospace fw-bold" value="{{ $noArsip }}" placeholder="ARSIP-KKA-..." {{ $isAdmin ? 'readonly' : 'required' }}>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1 text-muted"><i class="bi bi-calendar-event text-primary me-1"></i>Posisi Tanggal Rekonsiliasi Fisik</label>
                        <input type="date" name="posisi_tanggal_rekon" class="form-control form-control-sm" value="{{ $posisiTanggalRekon }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1 text-muted"><i class="bi bi-building text-primary me-1"></i>Status Arsip Lapangan</label>
                        <div>
                            <span class="badge-soft badge-soft-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Siap Cetak Otomatis Tanpa Tertahan Review</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                    <i class="bi bi-check-circle-fill me-1"></i> 5 KKA Standar Bank Sulteng
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
                            <th style="min-width: 90px;">Total Objek</th>
                            <th style="min-width: 110px;">Status Uji</th>
                            <th style="min-width: 135px;">Integrasi Klarifikasi</th>
                            <th style="min-width: 120px;">Status Review</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-muted fw-semibold">1</td>
                            <td class="text-start">
                                <i class="bi bi-cash-stack text-primary me-2"></i><strong>Cash Opname (Khasanah &amp; ATM)</strong>
                            </td>
                            <td><span class="sheet-code">KKA_Cash_Opname</span></td>
                            <td>
                                <span class="badge-soft badge-soft-secondary">
                                    Hari {{ $cashSched->hari_rencana ?? 2 }} ({{ $hariNames[$cashSched->hari_rencana ?? 2] ?? '-' }})
                                </span>
                            </td>
                            <td><span class="badge-soft badge-soft-secondary"><i class="bi bi-stopwatch text-muted me-1"></i>{{ $cashSched->menit_rencana ?? 120 }}m</span></td>
                            <td>{!! $renderStatusBadge($cashSched->calculated_status ?? 'TERJADWAL') !!}</td>
                            <td><span class="fw-semibold text-dark">Khasanah + ATM</span></td>
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
                            </td>
                            <td><span class="badge-soft badge-soft-secondary"><i class="bi bi-stopwatch text-muted me-1"></i>{{ $obsSched->menit_rencana ?? 90 }}m</span></td>
                            <td>{!! $renderStatusBadge($obsSched->calculated_status ?? 'TERJADWAL') !!}</td>
                            <td><span class="fw-semibold text-dark">{{ count($obsItems) }}</span> <small class="text-muted">Item</small></td>
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
                            </td>
                            <td><span class="badge-soft badge-soft-secondary"><i class="bi bi-stopwatch text-muted me-1"></i>{{ $stokSched->menit_rencana ?? 45 }}m</span></td>
                            <td>{!! $renderStatusBadge($stokSched->calculated_status ?? 'TERJADWAL') !!}</td>
                            <td><span class="fw-semibold text-dark">{{ count($stokItems) }}</span> <small class="text-muted">Item</small></td>
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
                            </td>
                            <td><span class="badge-soft badge-soft-secondary"><i class="bi bi-stopwatch text-muted me-1"></i>{{ $atmSched->menit_rencana ?? 60 }}m</span></td>
                            <td>{!! $renderStatusBadge($atmSched->calculated_status ?? 'TERJADWAL') !!}</td>
                            <td><span class="fw-semibold text-dark">Section A &amp; B</span></td>
                            <td><span class="badge-soft badge-soft-info"><i class="bi bi-play-circle me-1"></i>Aktif Diuji</span></td>
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
                            </td>
                            <td><span class="badge-soft badge-soft-secondary"><i class="bi bi-stopwatch text-muted me-1"></i>{{ $bctSched->menit_rencana ?? 45 }}m</span></td>
                            <td>{!! $renderStatusBadge($bctSched->calculated_status ?? 'TERJADWAL') !!}</td>
                            <td><span class="fw-semibold text-dark">{{ count($barangCetakanItems) }} Item + 4 Kontrol</span></td>
                            <td><span class="badge-soft badge-soft-info"><i class="bi bi-play-circle me-1"></i>Aktif Diuji</span></td>
                            <td><span class="badge-soft badge-soft-success"><i class="bi bi-check2-circle me-1"></i>Tersinkron (05A)</span></td>
                            <td>{!! $reviewBadge !!}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 5 TAB KKA FISIK -->
    <div class="summary-card">
        <div class="summary-card-header p-0" style="background: #ffffff; border-bottom: 1px solid var(--border-color);">
            <ul class="kka-nav-tabs" id="physicalKkaTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="kka-tab-btn active" id="cash-tab" data-bs-toggle="tab" data-bs-target="#cash" type="button" role="tab" aria-selected="true">
                        <i class="bi bi-cash-stack"></i>
                        <span>1. Cash Opname (Kas Khasanah)</span>
                        {!! $renderTabPill($cashSched->calculated_status ?? null) !!}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="kka-tab-btn" id="cash-atm-tab" data-bs-toggle="tab" data-bs-target="#cash-atm" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-wallet2"></i>
                        <span>2. Cash Opname (Mesin ATM)</span>
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
                
                <!-- ========================================================================= -->
                <!-- TAB 1: CASH OPNAME KHASANAH (STANDAR BA PEMERIKSAAN KAS BANK SULTENG)    -->
                <!-- ========================================================================= -->
                <div class="tab-pane fade show active p-3" id="cash" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold mb-1"><i class="bi bi-safe2 text-primary me-2"></i>Pemeriksaan Fisik Kas Khasanah (Berita Acara Kas Besar Olibs)</h6>
                            <small class="text-muted">Standar resmi BERITA ACARA PEMERIKSAAN KAS Bank Sulteng: 3 Kelompok Uang (ULE, Logam, UTLE) vs Kas Besar Sistem Olibs.</small>
                        </div>
                        <div>
                            <span class="badge-soft badge-soft-primary">
                                <i class="bi bi-shield-lock me-1"></i> Dual Control Khasanah
                            </span>
                        </div>
                    </div>

                    <!-- KELOMPOK 1: ULE -->
                    <div class="card mb-3 border-0 shadow-sm" style="background:#f8fafc; border-radius:8px;">
                        <div class="card-header bg-transparent border-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-primary small"><i class="bi bi-cash me-1"></i>KELOMPOK 1 ΓÇö UANG KERTAS (ULE / LAYAK EDAR)</span>
                            <span class="small text-muted">Subtotal ULE: <strong id="subtotalUleDisplay" class="text-dark">Rp 0</strong></span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-excel mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:5%;">No</th>
                                            <th style="width:30%;">Pecahan Uang</th>
                                            <th style="width:30%;">Jumlah Lembar (Input RA)</th>
                                            <th style="width:35%;">Total Nominal Fisik (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($khasanahUleItems as $idx => $item)
                                        <tr class="cash-calc-row" data-kelompok="ULE" data-nilai="{{ $item['nilai'] }}">
                                            <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                                            <td>
                                                <strong>{{ $item['pecahan'] }}</strong>
                                                <input type="hidden" name="cash_details[{{ $idx }}][kelompok]" value="ULE">
                                                <input type="hidden" name="cash_details[{{ $idx }}][objek_persediaan]" value="Uang Kertas Layak Edar">
                                                <input type="hidden" name="cash_details[{{ $idx }}][pecahan]" value="{{ $item['pecahan'] }}">
                                            </td>
                                            <td>
                                                <input type="number" min="0" step="1" name="cash_details[{{ $idx }}][jumlah_lembar]" class="form-control text-end cash-lembar" placeholder="0" value="{{ $item['jumlah_lembar'] ?? 0 }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control text-end fw-bold cash-nominal-display border-0 bg-transparent text-primary" readonly value="Rp {{ number_format($item['nominal'] ?? 0, 0, ',', '.') }}">
                                                <input type="hidden" name="cash_details[{{ $idx }}][nominal]" class="cash-nominal-raw" value="{{ $item['nominal'] ?? 0 }}">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- KELOMPOK 2: UANG LOGAM -->
                    @php $offsetLogam = count($khasanahUleItems); @endphp
                    <div class="card mb-3 border-0 shadow-sm" style="background:#f8fafc; border-radius:8px;">
                        <div class="card-header bg-transparent border-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-success small"><i class="bi bi-coin me-1"></i>KELOMPOK 2 ΓÇö UANG LOGAM</span>
                            <span class="small text-muted">Subtotal Logam: <strong id="subtotalLogamDisplay" class="text-dark">Rp 0</strong></span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-excel mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:5%;">No</th>
                                            <th style="width:30%;">Pecahan Uang Logam</th>
                                            <th style="width:30%;">Jumlah Keping (Kpg)</th>
                                            <th style="width:35%;">Total Nominal Fisik (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($khasanahLogamItems as $idx => $item)
                                        @php $rowIdx = $offsetLogam + $idx; @endphp
                                        <tr class="cash-calc-row" data-kelompok="Logam" data-nilai="{{ $item['nilai'] }}">
                                            <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                                            <td>
                                                <strong>{{ $item['pecahan'] }}</strong>
                                                <input type="hidden" name="cash_details[{{ $rowIdx }}][kelompok]" value="Logam">
                                                <input type="hidden" name="cash_details[{{ $rowIdx }}][objek_persediaan]" value="Uang Logam">
                                                <input type="hidden" name="cash_details[{{ $rowIdx }}][pecahan]" value="{{ $item['pecahan'] }}">
                                            </td>
                                            <td>
                                                <input type="number" min="0" step="1" name="cash_details[{{ $rowIdx }}][jumlah_lembar]" class="form-control text-end cash-lembar" placeholder="0" value="{{ $item['jumlah_lembar'] ?? 0 }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control text-end fw-bold cash-nominal-display border-0 bg-transparent text-success" readonly value="Rp {{ number_format($item['nominal'] ?? 0, 0, ',', '.') }}">
                                                <input type="hidden" name="cash_details[{{ $rowIdx }}][nominal]" class="cash-nominal-raw" value="{{ $item['nominal'] ?? 0 }}">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- KELOMPOK 3: UTLE -->
                    @php $offsetUtle = $offsetLogam + count($khasanahLogamItems); @endphp
                    <div class="card mb-3 border-0 shadow-sm" style="background:#f8fafc; border-radius:8px;">
                        <div class="card-header bg-transparent border-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>KELOMPOK 3 ΓÇö UANG LUSUH (UTLE / TIDAK LAYAK EDAR)</span>
                            <span class="small text-muted">Subtotal UTLE: <strong id="subtotalUtleDisplay" class="text-dark">Rp 0</strong></span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-excel mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:5%;">No</th>
                                            <th style="width:30%;">Pecahan Uang Lusuh</th>
                                            <th style="width:30%;">Jumlah Lembar (Input RA)</th>
                                            <th style="width:35%;">Total Nominal Fisik (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($khasanahUtleItems as $idx => $item)
                                        @php $rowIdx = $offsetUtle + $idx; @endphp
                                        <tr class="cash-calc-row" data-kelompok="UTLE" data-nilai="{{ $item['nilai'] }}">
                                            <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                                            <td>
                                                <strong>{{ $item['pecahan'] }}</strong>
                                                <input type="hidden" name="cash_details[{{ $rowIdx }}][kelompok]" value="UTLE">
                                                <input type="hidden" name="cash_details[{{ $rowIdx }}][objek_persediaan]" value="Uang lusuh/tidak layak edar">
                                                <input type="hidden" name="cash_details[{{ $rowIdx }}][pecahan]" value="{{ $item['pecahan'] }}">
                                            </td>
                                            <td>
                                                <input type="number" min="0" step="1" name="cash_details[{{ $rowIdx }}][jumlah_lembar]" class="form-control text-end cash-lembar" placeholder="0" value="{{ $item['jumlah_lembar'] ?? 0 }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control text-end fw-bold cash-nominal-display border-0 bg-transparent text-danger" readonly value="Rp {{ number_format($item['nominal'] ?? 0, 0, ',', '.') }}">
                                                <input type="hidden" name="cash_details[{{ $rowIdx }}][nominal]" class="cash-nominal-raw" value="{{ $item['nominal'] ?? 0 }}">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- SUMMARY & REKONSILIASI KAS BESAR OLIBS -->
                    <div class="card border border-primary border-2 shadow-sm mb-3" style="border-radius:10px; background:#ffffff;">
                        <div class="card-header bg-primary text-white py-2 px-3 fw-bold small">
                            <i class="bi bi-calculator me-1"></i>REKONSILIASI TOTAL FISIK KHASANAH VS SALDO KAS BESAR SISTEM OLIBS
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">A. Total Saldo Fisik Kas Khasanah (1 + 2 + 3)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">Rp</span>
                                        <input type="text" id="totalFisikKasDisplay" class="form-control fw-bold font-monospace text-primary fs-6" readonly value="Rp 0">
                                    </div>
                                    <small class="text-muted" style="font-size:0.7rem;">Akumulasi otomatis seluruh fisik uang kertas ULE, logam, &amp; uang lusuh UTLE.</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">B. Saldo Menurut Kas Besar Sistem Olibs (Rp)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">Rp</span>
                                        <input type="number" step="any" name="saldo_kas_olibs" id="inputSaldoOlibs" class="form-control fw-bold text-end" value="{{ $saldoKasOlibs ?? 0 }}" placeholder="0" {{ $isAdmin ? 'readonly' : 'required' }}>
                                    </div>
                                    <small class="text-muted" style="font-size:0.7rem;">Saldo buku kas khasanah pada laporan akhir hari sistem Corebanking Olibs.</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">C. Selisih Kas (Fisik - Olibs)</label>
                                    <div>
                                        <span id="badgeSelisihKas" class="badge-soft badge-soft-success px-3 py-2 w-100 text-center fs-6">
                                            Nihil / Sesuai (Rp 0)
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label small fw-bold text-muted">
                                    Uraian Penjelasan Penyebab Selisih Kas:
                                    <span class="text-danger small fw-normal" id="labelPenyebabWajib" style="display:none;">*(Wajib diisi apabila terdapat selisih Kas!)</span>
                                </label>
                                <textarea name="penjelasan_selisih_kas" id="penjelasanSelisihKas" rows="2" class="form-control form-control-sm" placeholder="Jelaskan rincian selisih jika ada (misal selisih kurang pada ULE/UTLE beserta nomor register/penyebabnya)..." {{ $isAdmin ? 'readonly' : '' }}>{{ $penjelasanSelisihKas }}</textarea>
                            </div>

                            <!-- Pihak Pelaksana & Saksi BA Kas Khasanah -->
                            <div class="row g-2 mt-2 pt-2 border-top">
                                <div class="col-md-3">
                                    <label class="form-label small text-muted mb-0">Nama RA Pelaksana:</label>
                                    <input type="text" name="kas_pelaksana_nama" class="form-control form-control-sm bg-light" value="{{ $kka->kas_pelaksana_nama ?? (auth()->user()->name ?? 'Resident Auditor') }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted mb-0">Jabatan Pelaksana:</label>
                                    <input type="text" name="kas_pelaksana_jabatan" class="form-control form-control-sm" value="{{ $kka->kas_pelaksana_jabatan ?? 'Resident Auditor SKAI' }}" {{ $isAdmin ? 'readonly' : '' }}>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted mb-0">Nama Saksi (Khasanah):</label>
                                    <input type="text" name="kas_saksi_nama" class="form-control form-control-sm" value="{{ $kka->kas_saksi_nama ?? 'Supervisor Operasional / Kasir' }}" {{ $isAdmin ? 'readonly' : '' }}>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted mb-0">Jabatan Saksi:</label>
                                    <input type="text" name="kas_saksi_jabatan" class="form-control form-control-sm" value="{{ $kka->kas_saksi_jabatan ?? 'Pemegang Kunci Khasanah' }}" {{ $isAdmin ? 'readonly' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- TAB 2: CASH OPNAME MESIN ATM & KASET (MODEL CARD PER MESIN ATM)          -->
                <!-- ========================================================================= -->
                <div class="tab-pane fade p-3" id="cash-atm" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold mb-1"><i class="bi bi-wallet2 text-primary me-2"></i>Cash Opname Mesin ATM &amp; Kaset (Model Card per Mesin)</h6>
                            <small class="text-muted">Mengacu pada BERITA ACARA CASH OPNAME ATM. Setiap mesin memiliki Card tersendiri dengan tombol + Tambah Kaset dinamis.</small>
                        </div>
                        @if(!$isAdmin)
                        <div>
                            <button type="button" class="btn btn-sm btn-primary fw-bold shadow-sm" id="btnTambahAtmCard">
                                <i class="bi bi-plus-circle me-1"></i> + Tambah Mesin ATM
                            </button>
                        </div>
                        @endif
                    </div>

                    <!-- Container Card Mesin ATM -->
                    <div id="atmCardsContainer">
                        @forelse($atmMachines as $mIdx => $mach)
                            <div class="atm-card-item" data-atm-index="{{ $mIdx }}">
                                <div class="atm-card-topbar">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fs-5 text-primary">≡ƒÅº</span>
                                        <div class="input-group input-group-sm" style="width: 250px;">
                                            <span class="input-group-text fw-bold bg-white text-muted">ID ATM:</span>
                                            <input type="text" name="atm_machines[{{ $mIdx }}][terminal_id]" class="form-control font-monospace fw-bold fs-6 atm-terminal-id text-primary" placeholder="00128" value="{{ $mach->terminal_id }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge-soft atm-status-badge {{ $mach->status_uji === 'Selesai Diuji' ? 'badge-soft-success' : 'badge-soft-warning' }}">
                                            <i class="bi bi-check2-circle"></i> {{ $mach->status_uji ?? 'Belum Diuji' }}
                                        </span>
                                        @if(!$isAdmin)
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-atm" title="Hapus Mesin ATM">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </div>

                                <div class="atm-ident-body">
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold text-muted mb-0">Lokasi ATM:</label>
                                            <input type="text" name="atm_machines[{{ $mIdx }}][lokasi]" class="form-control form-control-sm" placeholder="Contoh: ATM Museum / Galeri KC" value="{{ $mach->lokasi }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold text-muted mb-0">Merk Mesin ATM:</label>
                                            <input type="text" name="atm_machines[{{ $mIdx }}][merk]" class="form-control form-control-sm" placeholder="DBL / WCR / CRM" value="{{ $mach->merk }}" {{ $isAdmin ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold text-muted mb-0">Denominasi Mesin:</label>
                                            <select name="atm_machines[{{ $mIdx }}][denominasi]" class="form-select form-select-sm atm-denom-select" {{ $isAdmin ? 'disabled' : '' }}>
                                                <option value="100000" {{ ($mach->denominasi ?? 100000) == 100000 ? 'selected' : '' }}>Rp 100.000</option>
                                                <option value="50000" {{ ($mach->denominasi ?? 100000) == 50000 ? 'selected' : '' }}>Rp 50.000</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold text-muted mb-0">Tgl Pengisian Terakhir:</label>
                                            <input type="date" name="atm_machines[{{ $mIdx }}][tanggal_pengisian_terakhir]" class="form-control form-control-sm" value="{{ $mach->tanggal_pengisian_terakhir ? \Carbon\Carbon::parse($mach->tanggal_pengisian_terakhir)->format('Y-m-d') : '' }}" {{ $isAdmin ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold text-primary mb-0">Total Saldo Sistem (CBS):</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                                <input type="number" step="any" name="atm_machines[{{ $mIdx }}][saldo_cbs]" class="form-control form-control-sm text-end fw-bold atm-saldo-cbs" placeholder="0" value="{{ $mach->saldo_cbs ?? 0 }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="atm-cassette-box">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small fw-bold text-dark"><i class="bi bi-inbox me-1 text-primary"></i>Rincian Fisik Uang Per Kaset ATM</span>
                                        @if(!$isAdmin)
                                        <button type="button" class="btn btn-xs btn-outline-primary btn-add-kaset fw-bold" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                                            <i class="bi bi-plus-lg"></i> + Tambah Kaset
                                        </button>
                                        @endif
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-excel mb-0 table-kaset">
                                            <thead>
                                                <tr>
                                                    <th style="width: 35%;">Nama / Jenis Kaset</th>
                                                    <th style="width: 20%;">Denominasi (Rp)</th>
                                                    <th style="width: 15%;">Jumlah Lembar</th>
                                                    <th style="width: 25%;">Total Nominal Fisik Kaset (Rp)</th>
                                                    <th style="width: 5%;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="tbody-kaset">
                                                @forelse($mach->cassettes as $cIdx => $cas)
                                                <tr class="kaset-row">
                                                    <td>
                                                        <select name="atm_machines[{{ $mIdx }}][cassettes][{{ $cIdx }}][nama_kaset]" class="form-select form-select-sm" {{ $isAdmin ? 'disabled' : '' }}>
                                                            <option value="Cassette 1" {{ $cas->nama_kaset === 'Cassette 1' ? 'selected' : '' }}>Cassette 1</option>
                                                            <option value="Cassette 2" {{ $cas->nama_kaset === 'Cassette 2' ? 'selected' : '' }}>Cassette 2</option>
                                                            <option value="Cassette 3" {{ $cas->nama_kaset === 'Cassette 3' ? 'selected' : '' }}>Cassette 3</option>
                                                            <option value="Cassette 4" {{ $cas->nama_kaset === 'Cassette 4' ? 'selected' : '' }}>Cassette 4</option>
                                                            <option value="Kaset Cadangan" {{ $cas->nama_kaset === 'Kaset Cadangan' ? 'selected' : '' }}>Kaset Cadangan</option>
                                                            <option value="Kaset Reject" {{ $cas->nama_kaset === 'Kaset Reject' ? 'selected' : '' }}>Kaset Reject (Cassette Reject Bin)</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="atm_machines[{{ $mIdx }}][cassettes][{{ $cIdx }}][denominasi]" class="form-control form-control-sm text-end kaset-denom" value="{{ $cas->denominasi ?? 100000 }}" {{ $isAdmin ? 'readonly' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="number" min="0" step="1" name="atm_machines[{{ $mIdx }}][cassettes][{{ $cIdx }}][jumlah_lembar]" class="form-control form-control-sm text-end kaset-lembar" placeholder="0" value="{{ $cas->jumlah_lembar ?? 0 }}" {{ $isAdmin ? 'readonly' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="any" name="atm_machines[{{ $mIdx }}][cassettes][{{ $cIdx }}][total_nominal]" class="form-control form-control-sm text-end fw-bold kaset-total-nominal text-primary" placeholder="0" value="{{ $cas->total_nominal ?? 0 }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                                                    </td>
                                                    <td class="text-center">
                                                        @if(!$isAdmin)
                                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-kaset" title="Hapus Kaset"><i class="bi bi-x-circle-fill"></i></button>
                                                        @else
                                                        -
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <!-- Default 4 Kaset jika kosong -->
                                                @foreach(['Cassette 1', 'Cassette 2', 'Cassette 3', 'Cassette 4'] as $dIdx => $cName)
                                                <tr class="kaset-row">
                                                    <td>
                                                        <select name="atm_machines[{{ $mIdx }}][cassettes][{{ $dIdx }}][nama_kaset]" class="form-select form-select-sm" {{ $isAdmin ? 'disabled' : '' }}>
                                                            <option value="{{ $cName }}" selected>{{ $cName }}</option>
                                                            <option value="Kaset Cadangan">Kaset Cadangan</option>
                                                            <option value="Kaset Reject">Kaset Reject (Cassette Reject Bin)</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="atm_machines[{{ $mIdx }}][cassettes][{{ $dIdx }}][denominasi]" class="form-control form-control-sm text-end kaset-denom" value="100000" {{ $isAdmin ? 'readonly' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="number" min="0" step="1" name="atm_machines[{{ $mIdx }}][cassettes][{{ $dIdx }}][jumlah_lembar]" class="form-control form-control-sm text-end kaset-lembar" placeholder="0" value="0" {{ $isAdmin ? 'readonly' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="any" name="atm_machines[{{ $mIdx }}][cassettes][{{ $dIdx }}][total_nominal]" class="form-control form-control-sm text-end fw-bold kaset-total-nominal text-primary" placeholder="0" value="0" {{ $isAdmin ? 'readonly' : 'required' }}>
                                                    </td>
                                                    <td class="text-center">
                                                        @if(!$isAdmin)
                                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-kaset"><i class="bi bi-x-circle-fill"></i></button>
                                                        @else
                                                        -
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                @php
                                    $mTotalFisik = (float)($mach->total_fisik ?? 0);
                                    $mSaldoCbs = (float)($mach->saldo_cbs ?? 0);
                                    $mSelisih = (float)($mach->selisih ?? ($mSaldoCbs - $mTotalFisik));
                                    $mStatusUji = $mach->status_uji ?: 'Belum Diuji';
                                @endphp
                                <div class="atm-calc-footer">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-4">
                                            <span class="small fw-bold text-muted">Total Fisik Seluruh Kaset:</span>
                                            <div class="fs-6 fw-bold text-primary atm-total-fisik-display font-monospace">Rp {{ number_format($mTotalFisik, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="col-md-5">
                                            <span class="small fw-bold text-muted">Status &amp; Selisih Kas ATM:</span>
                                            <div>
                                                @if(abs($mSelisih) < 0.01)
                                                    <span class="badge-soft badge-soft-success atm-selisih-badge py-2 px-3 fs-6 w-100 text-center">
                                                        Nihil / Sesuai (Rp 0)
                                                    </span>
                                                @elseif($mTotalFisik > $mSaldoCbs)
                                                    <span class="badge-soft badge-soft-warning atm-selisih-badge py-2 px-3 fs-6 w-100 text-center">
                                                        Ada Selisih: Lebih (+ Rp {{ number_format(abs($mTotalFisik - $mSaldoCbs), 0, ',', '.') }})
                                                    </span>
                                                @else
                                                    <span class="badge-soft badge-soft-danger atm-selisih-badge py-2 px-3 fs-6 w-100 text-center">
                                                        Ada Selisih: Kurang (- Rp {{ number_format(abs($mSaldoCbs - $mTotalFisik), 0, ',', '.') }})
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="{{ isset($id) ? route('daily-register.physical-kka.print-ba-atm', $id) : '#' }}" target="_blank" class="btn btn-sm btn-outline-secondary w-100 fw-bold">
                                                <i class="bi bi-printer me-1"></i> Cetak BA ATM
                                            </a>
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <label class="form-label small fw-bold text-muted mb-0">
                                            Keterangan Penyebab Selisih Kas ATM:
                                            <span class="text-danger small fw-normal atm-ket-wajib" style="{{ abs($mSelisih) >= 0.01 ? '' : 'display:none;' }}">*(Wajib diisi jika terdapat selisih!)</span>
                                        </label>
                                        <input type="text" name="atm_machines[{{ $mIdx }}][keterangan_selisih]" class="form-control form-control-sm atm-keterangan-selisih" placeholder="Ketik penyebab selisih kas mesin ATM..." value="{{ $mach->keterangan_selisih }}" {{ $isAdmin ? 'readonly' : '' }}>
                                    </div>

                                    <div class="row g-2 mt-2 pt-2 border-top">
                                        <div class="col-md-3">
                                            <input type="text" name="atm_machines[{{ $mIdx }}][pelaksana_nama]" class="form-control form-control-sm bg-light" value="{{ $mach->pelaksana_nama ?? (auth()->user()->name ?? 'Resident Auditor') }}" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="atm_machines[{{ $mIdx }}][pelaksana_jabatan]" class="form-control form-control-sm" value="{{ $mach->pelaksana_jabatan ?? 'RA Pelaksana SKAI' }}" {{ $isAdmin ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="atm_machines[{{ $mIdx }}][saksi_nama]" class="form-control form-control-sm" value="{{ $mach->saksi_nama ?? 'Supervisor ATM' }}" placeholder="Nama Saksi ATM" {{ $isAdmin ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="atm_machines[{{ $mIdx }}][saksi_jabatan]" class="form-control form-control-sm" value="{{ $mach->saksi_jabatan ?? 'Pemegang Kunci ATM Dual Control' }}" {{ $isAdmin ? 'readonly' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <!-- Card ATM default jika belum ada -->
                            <div class="atm-card-item" data-atm-index="0">
                                <div class="atm-card-topbar">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fs-5 text-primary">≡ƒÅº</span>
                                        <div class="input-group input-group-sm" style="width: 250px;">
                                            <span class="input-group-text fw-bold bg-white text-muted">ID ATM:</span>
                                            <input type="text" name="atm_machines[0][terminal_id]" class="form-control font-monospace fw-bold fs-6 atm-terminal-id text-primary" placeholder="00128" value="00128" {{ $isAdmin ? 'readonly' : 'required' }}>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge-soft badge-soft-warning atm-status-badge">
                                            <i class="bi bi-clock"></i> Belum Diuji
                                        </span>
                                        @if(!$isAdmin)
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-atm" title="Hapus Mesin ATM">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </div>

                                <div class="atm-ident-body">
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold text-muted mb-0">Lokasi ATM:</label>
                                            <input type="text" name="atm_machines[0][lokasi]" class="form-control form-control-sm" placeholder="Contoh: ATM Museum / Galeri KC" value="Galeri ATM KC" {{ $isAdmin ? 'readonly' : 'required' }}>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold text-muted mb-0">Merk Mesin ATM:</label>
                                            <input type="text" name="atm_machines[0][merk]" class="form-control form-control-sm" placeholder="DBL / WCR / CRM" value="DBL" {{ $isAdmin ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold text-muted mb-0">Denominasi Mesin:</label>
                                            <select name="atm_machines[0][denominasi]" class="form-select form-select-sm atm-denom-select" {{ $isAdmin ? 'disabled' : '' }}>
                                                <option value="100000" selected>Rp 100.000</option>
                                                <option value="50000">Rp 50.000</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold text-muted mb-0">Tgl Pengisian Terakhir:</label>
                                            <input type="date" name="atm_machines[0][tanggal_pengisian_terakhir]" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" {{ $isAdmin ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold text-primary mb-0">Total Saldo Sistem (CBS):</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                                <input type="number" step="any" name="atm_machines[0][saldo_cbs]" class="form-control form-control-sm text-end fw-bold atm-saldo-cbs" placeholder="0" value="0" {{ $isAdmin ? 'readonly' : 'required' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="atm-cassette-box">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small fw-bold text-dark"><i class="bi bi-inbox me-1 text-primary"></i>Rincian Fisik Uang Per Kaset ATM</span>
                                        @if(!$isAdmin)
                                        <button type="button" class="btn btn-xs btn-outline-primary btn-add-kaset fw-bold" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                                            <i class="bi bi-plus-lg"></i> + Tambah Kaset
                                        </button>
                                        @endif
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-excel mb-0 table-kaset">
                                            <thead>
                                                <tr>
                                                    <th style="width: 35%;">Nama / Jenis Kaset</th>
                                                    <th style="width: 20%;">Denominasi (Rp)</th>
                                                    <th style="width: 15%;">Jumlah Lembar</th>
                                                    <th style="width: 25%;">Total Nominal Fisik Kaset (Rp)</th>
                                                    <th style="width: 5%;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="tbody-kaset">
                                                @foreach(['Cassette 1', 'Cassette 2', 'Cassette 3', 'Cassette 4'] as $dIdx => $cName)
                                                <tr class="kaset-row">
                                                    <td>
                                                        <select name="atm_machines[0][cassettes][{{ $dIdx }}][nama_kaset]" class="form-select form-select-sm" {{ $isAdmin ? 'disabled' : '' }}>
                                                            <option value="{{ $cName }}" selected>{{ $cName }}</option>
                                                            <option value="Kaset Cadangan">Kaset Cadangan</option>
                                                            <option value="Kaset Reject">Kaset Reject (Cassette Reject Bin)</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="atm_machines[0][cassettes][{{ $dIdx }}][denominasi]" class="form-control form-control-sm text-end kaset-denom" value="100000" {{ $isAdmin ? 'readonly' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="number" min="0" step="1" name="atm_machines[0][cassettes][{{ $dIdx }}][jumlah_lembar]" class="form-control form-control-sm text-end kaset-lembar" placeholder="0" value="0" {{ $isAdmin ? 'readonly' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="any" name="atm_machines[0][cassettes][{{ $dIdx }}][total_nominal]" class="form-control form-control-sm text-end fw-bold kaset-total-nominal text-primary" placeholder="0" value="0" {{ $isAdmin ? 'readonly' : 'required' }}>
                                                    </td>
                                                    <td class="text-center">
                                                        @if(!$isAdmin)
                                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-kaset"><i class="bi bi-x-circle-fill"></i></button>
                                                        @else
                                                        -
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="atm-calc-footer">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-4">
                                            <span class="small fw-bold text-muted">Total Fisik Seluruh Kaset:</span>
                                            <div class="fs-6 fw-bold text-primary atm-total-fisik-display font-monospace">Rp 0</div>
                                        </div>
                                        <div class="col-md-5">
                                            <span class="small fw-bold text-muted">Status &amp; Selisih Kas ATM:</span>
                                            <div>
                                                <span class="badge-soft badge-soft-success atm-selisih-badge py-2 px-3 fs-6 w-100 text-center">
                                                    Nihil / Sesuai (Rp 0)
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" class="btn btn-sm btn-outline-secondary w-100 fw-bold" disabled>
                                                <i class="bi bi-printer me-1"></i> Cetak BA ATM
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <label class="form-label small fw-bold text-muted mb-0">
                                            Keterangan Penyebab Selisih Kas ATM:
                                            <span class="text-danger small fw-normal atm-ket-wajib" style="display:none;">*(Wajib diisi jika terdapat selisih!)</span>
                                        </label>
                                        <input type="text" name="atm_machines[0][keterangan_selisih]" class="form-control form-control-sm atm-keterangan-selisih" placeholder="Ketik penyebab selisih kas mesin ATM..." {{ $isAdmin ? 'readonly' : '' }}>
                                    </div>

                                    <div class="row g-2 mt-2 pt-2 border-top">
                                        <div class="col-md-3">
                                            <input type="text" name="atm_machines[0][pelaksana_nama]" class="form-control form-control-sm bg-light" value="{{ auth()->user()->name ?? 'Resident Auditor' }}" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="atm_machines[0][pelaksana_jabatan]" class="form-control form-control-sm" value="RA Pelaksana SKAI" {{ $isAdmin ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="atm_machines[0][saksi_nama]" class="form-control form-control-sm" value="Supervisor ATM" placeholder="Nama Saksi ATM" {{ $isAdmin ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="atm_machines[0][saksi_jabatan]" class="form-control form-control-sm" value="Pemegang Kunci ATM Dual Control" {{ $isAdmin ? 'readonly' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- TAB 3: OBSERVASI LINGKUNGAN                                              -->
                <!-- ========================================================================= -->
                <div class="tab-pane fade p-3" id="obs" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold mb-1"><i class="bi bi-eye me-2 text-primary"></i>KKA_Observasi_Lingkungan (Fasilitas &amp; Keamanan Fisik)</h6>
                            <small class="text-muted">Pemeriksaan fisik sarana prasarana, 4 titik alarm, 5 area terbatas, matriks 9 titik CCTV (backup min. 90 hari), mutasi security &amp; absensi, dan fasilitas ATM (E.4.4).</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if(!empty($kka->id))
                            <a href="{{ route('daily-register.physical-kka.print-kka-observasi', $kka->id) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-semibold shadow-sm">
                                <i class="bi bi-printer me-1"></i> Cetak Observasi Lingkungan
                            </a>
                            @endif
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-excel align-middle">
                            <thead>
                                <tr>
                                    <th style="min-width:180px;">Area Observasi</th>
                                    <th style="min-width:220px;">Kriteria yang Diharapkan</th>
                                    <th style="min-width:140px;" class="text-center">Backup Rekaman (E.1.5)</th>
                                    <th style="min-width:140px;">Hasil Uji</th>
                                    <th style="min-width:180px;">Kondisi Aktual</th>
                                    <th style="min-width:180px;">Penyebab &amp; Keterangan</th>
                                    <th>Impact</th>
                                    <th>Likelihood</th>
                                    <th>Critical?</th>
                                    <th>Risk Score</th>
                                    <th>Risk Level</th>
                                    <th style="min-width:130px;">Tgl Uji</th>
                                    @if($isAdmin)
                                    <th class="bg-warning text-dark">Reviewer</th>
                                    <th class="bg-warning text-dark" style="min-width:150px;">Status Review</th>
                                    <th class="bg-warning text-dark" style="min-width:200px;">Catatan Admin</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @php $lastGroup = null; @endphp
                                @foreach($obsItems as $index => $item)
                                @php
                                    $area = $item['area'] ?? '';
                                    $isCctv = str_contains($area, 'CCTV') || str_contains($area, 'E.1.5');
                                    
                                    $groupName = 'Observasi Lingkungan';
                                    if (str_contains($area, 'Titik Alarm')) {
                                        $groupName = '≡ƒÜ¿ 1. SPESIFIK 4 TITIK ALARM (PANIC BUTTON)';
                                    } elseif (str_contains($area, 'Area Terbatas')) {
                                        $groupName = '≡ƒöÆ 2. SPESIFIK 5 AREA TERBATAS (RESTRICTED ACCESS)';
                                    } elseif ($isCctv) {
                                        $groupName = '≡ƒô╣ 3. MATRIKS 9 TITIK CCTV (ACUAN E.1.5 ΓÇö WAJIB BACKUP MINIMAL 90 HARI)';
                                    } elseif (str_contains($area, 'Security') || str_contains($area, 'Kepegawaian')) {
                                        $groupName = '≡ƒæ« 4. MUTASI & BUKU MUTASI SECURITY SERTA ABSENSI KARYAWAN';
                                    } elseif (str_contains($area, 'Branding Interior')) {
                                        $groupName = '≡ƒÅó 5. BRANDING INTERIOR KANTOR (STANDAR CORPORATE BANK SULTENG)';
                                    } elseif (str_contains($area, 'Fasilitas ATM')) {
                                        $groupName = '≡ƒÅº 6. SECTION KHUSUS ATM (ACUAN STANDAR AUDIT E.4.4)';
                                    } elseif (str_contains($area, 'Keselamatan') || str_contains($area, 'Kelistrikan')) {
                                        $groupName = '≡ƒº» 7. KESELAMATAN KERJA & FASILITAS LISTRIK CADANGAN';
                                    }
                                @endphp

                                @if($groupName !== $lastGroup)
                                <tr class="table-light">
                                    <td colspan="{{ $isAdmin ? 15 : 12 }}" class="fw-bold text-primary py-2 px-3" style="background:#f1f5f9; border-top: 2px solid #cbd5e1; font-size: 0.82rem;">
                                        {{ $groupName }}
                                    </td>
                                </tr>
                                @php $lastGroup = $groupName; @endphp
                                @endif

                                <tr class="data-row" data-type="obs">
                                    <td>
                                        <strong>{{ $item['area'] }}</strong>
                                        <input type="hidden" name="obs_details[{{ $index }}][area_observasi]" value="{{ $item['area'] }}">
                                    </td>
                                    <td>
                                        {{ $item['kriteria'] }}
                                        <input type="hidden" name="obs_details[{{ $index }}][kriteria_diharapkan]" value="{{ $item['kriteria'] }}">
                                    </td>
                                    <td class="text-center">
                                        @if($isCctv)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fw-bold" style="font-size:0.72rem;">
                                                <i class="bi bi-shield-check me-1"></i> Wajib Min. 90 Hari
                                            </span>
                                        @else
                                            <span class="text-muted small">ΓÇö</span>
                                        @endif
                                    </td>
                                    <td>
                                        <select name="obs_details[{{ $index }}][hasil_observasi]" class="form-select hasil-obs" {{ $isAdmin ? 'disabled' : 'required' }}>
                                            <option value="">Pilih Hasil</option>
                                            <option value="Sesuai" {{ ($item['hasil_observasi'] ?? '') === 'Sesuai' ? 'selected' : '' }}>Sesuai</option>
                                            <option value="Tidak Sesuai" {{ ($item['hasil_observasi'] ?? '') === 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                            <option value="Tidak Berlaku" {{ ($item['hasil_observasi'] ?? '') === 'Tidak Berlaku' ? 'selected' : '' }}>Tidak Berlaku</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="obs_details[{{ $index }}][kondisi_aktual]" class="form-control" placeholder="Observasi lapangan..." value="{{ $item['kondisi_aktual'] ?? '' }}" {{ $isAdmin ? 'readonly' : '' }}></td>
                                    <td><input type="text" name="obs_details[{{ $index }}][penyebab]" class="form-control exc-field" placeholder="Keterangan..." value="{{ $item['penyebab'] ?? '' }}" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}></td>
                                    <td>
                                        <select name="obs_details[{{ $index }}][impact]" class="form-select exc-field impact-sel" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}>
                                            @for($val=1; $val<=5; $val++)
                                                <option value="{{ $val }}" {{ ($item['impact'] ?? 1) == $val ? 'selected' : '' }}>{{ $val }}</option>
                                            @endfor
                                        </select>
                                    </td>
                                    <td>
                                        <select name="obs_details[{{ $index }}][likelihood]" class="form-select exc-field likelihood-sel" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}>
                                            @for($val=1; $val<=5; $val++)
                                                <option value="{{ $val }}" {{ ($item['likelihood'] ?? 1) == $val ? 'selected' : '' }}>{{ $val }}</option>
                                            @endfor
                                        </select>
                                    </td>
                                    <td>
                                        <select name="obs_details[{{ $index }}][critical_trigger]" class="form-select exc-field critical-sel" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}>
                                            <option value="Tidak" {{ ($item['critical_trigger'] ?? 'Tidak') === 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                            <option value="Ya" {{ ($item['critical_trigger'] ?? '') === 'Ya' ? 'selected' : '' }}>Ya</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control risk-score border-0 bg-transparent text-center" readonly value="{{ $item['risk_score'] ?? '' }}"></td>
                                    <td><input type="text" class="form-control risk-level border-0 bg-transparent text-center" readonly value="{{ $item['severity_level'] ?? '' }}"></td>
                                    <td><input type="date" name="obs_details[{{ $index }}][tanggal_uji]" class="form-control" value="{{ $item['tanggal_uji'] ?? date('Y-m-d') }}" {{ $isAdmin ? 'readonly' : 'required' }}></td>
                                    
                                    @if($isAdmin)
                                    @php $fakeId = $index + 1; @endphp
                                    <td><input type="text" class="form-control bg-light" value="{{ auth()->user()->name ?? 'Admin' }}" readonly></td>
                                    <td>
                                        <select name="obs_review[{{ $fakeId }}][status_review]" class="form-select border-warning">
                                            <option value="Pending" {{ ($item['status_review'] ?? '') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="Approved" {{ ($item['status_review'] ?? '') === 'Approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="Approved - Fraud" {{ ($item['status_review'] ?? '') === 'Approved - Fraud' ? 'selected' : '' }}>Approved - Terindikasi Fraud (SOP 09)</option>
                                            <option value="Revisi" {{ ($item['status_review'] ?? '') === 'Revisi' ? 'selected' : '' }}>Revisi</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="obs_review[{{ $fakeId }}][catatan_reviewer]" class="form-control border-warning" placeholder="Ketik catatan review..." value="{{ $item['catatan_reviewer'] ?? '' }}"></td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- TAB 4: REKON STOK CEK / BILYET GIRO                                      -->
                <!-- ========================================================================= -->
                <div class="tab-pane fade p-3" id="stok" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold mb-1"><i class="bi bi-journal-check me-2 text-primary"></i>KKA_Rekon_Stok_Cek_BG (Pemeriksaan Warkat &amp; Dokumen Berharga)</h6>
                            <small class="text-muted">Pemeriksaan fisik stok buku cek, bilyet giro, dan sertifikat bilyet deposito dalam brankas.</small>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-excel">
                            <thead>
                                <tr>
                                    <th>Sub Area</th>
                                    <th>Objek / Seri Dokumen</th>
                                    <th>Keterangan / Kondisi</th>
                                    <th>Saldo Sistem</th>
                                    <th style="min-width:120px;">Hasil Fisik</th>
                                    <th>Selisih</th>
                                    <th>Status Uji</th>
                                    <th style="min-width:180px;">Kondisi / Penyebab</th>
                                    <th>Impact</th>
                                    <th>Likelihood</th>
                                    <th>Critical?</th>
                                    <th>Risk Score</th>
                                    <th>Risk Level</th>
                                    <th style="min-width:130px;">Tgl Uji</th>
                                    @if($isAdmin)
                                    <th class="bg-warning text-dark">Reviewer</th>
                                    <th class="bg-warning text-dark" style="min-width:150px;">Status Review</th>
                                    <th class="bg-warning text-dark" style="min-width:200px;">Catatan Admin</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stokItems as $index => $item)
                                <tr class="data-row" data-type="stok">
                                    <td>
                                        <span class="badge bg-secondary">{{ $item['sub_area'] }}</span>
                                        <input type="hidden" name="stok_details[{{ $index }}][sub_area]" value="{{ $item['sub_area'] }}">
                                    </td>
                                    <td>
                                        <strong>{{ $item['objek'] }}</strong>
                                        <input type="hidden" name="stok_details[{{ $index }}][kondisi_objek]" value="{{ $item['objek'] }} | {{ $item['pecahan'] }}">
                                    </td>
                                    <td><input type="text" name="stok_details[{{ $index }}][kondisi_aktual]" class="form-control" value="{{ $item['pecahan'] ?? '' }}" {{ $isAdmin ? 'readonly' : '' }}></td>
                                    <td><input type="number" name="stok_details[{{ $index }}][saldo_sistem]" class="form-control saldo-sistem border-0 bg-transparent text-end" value="{{ $item['saldo_sistem'] ?? 100 }}" readonly></td>
                                    <td><input type="number" name="stok_details[{{ $index }}][hasil_fisik]" class="form-control hasil-fisik text-end" placeholder="0" value="{{ $item['hasil_fisik'] ?? '' }}" {{ $isAdmin ? 'readonly' : 'required' }}></td>
                                    <td><input type="text" class="form-control selisih-display border-0 bg-transparent text-end fw-bold" readonly value="{{ isset($item['saldo_sistem'], $item['hasil_fisik']) && $item['hasil_fisik'] !== '' ? ((float)$item['saldo_sistem'] - (float)$item['hasil_fisik']) : '' }}"></td>
                                    <td><input type="text" class="form-control status-uji border-0 bg-transparent fw-bold text-center" value="{{ $item['status_uji'] ?? 'BELUM DIUJI' }}" readonly></td>
                                    <td><input type="text" name="stok_details[{{ $index }}][penyebab]" class="form-control exc-field" placeholder="Keterangan..." value="{{ $item['penyebab'] ?? '' }}" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}></td>
                                    <td>
                                        <select name="stok_details[{{ $index }}][impact]" class="form-select exc-field impact-sel" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}>
                                            @for($val=1; $val<=5; $val++)
                                                <option value="{{ $val }}" {{ ($item['impact'] ?? 1) == $val ? 'selected' : '' }}>{{ $val }}</option>
                                            @endfor
                                        </select>
                                    </td>
                                    <td>
                                        <select name="stok_details[{{ $index }}][likelihood]" class="form-select exc-field likelihood-sel" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}>
                                            @for($val=1; $val<=5; $val++)
                                                <option value="{{ $val }}" {{ ($item['likelihood'] ?? 1) == $val ? 'selected' : '' }}>{{ $val }}</option>
                                            @endfor
                                        </select>
                                    </td>
                                    <td>
                                        <select name="stok_details[{{ $index }}][critical_trigger]" class="form-select exc-field critical-sel" {{ ($item['status_uji'] ?? '') === 'Exception' ? '' : 'disabled' }}>
                                            <option value="Tidak" {{ ($item['critical_trigger'] ?? 'Tidak') === 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                            <option value="Ya" {{ ($item['critical_trigger'] ?? '') === 'Ya' ? 'selected' : '' }}>Ya</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control risk-score border-0 bg-transparent text-center" readonly value="{{ $item['risk_score'] ?? '' }}"></td>
                                    <td><input type="text" class="form-control risk-level border-0 bg-transparent text-center" readonly value="{{ $item['severity_level'] ?? '' }}"></td>
                                    <td><input type="date" name="stok_details[{{ $index }}][tanggal_uji]" class="form-control" value="{{ $item['tanggal_uji'] ?? date('Y-m-d') }}" {{ $isAdmin ? 'readonly' : 'required' }}></td>
                                    
                                    @if($isAdmin)
                                    @php $fakeId = $index + 1; @endphp
                                    <td><input type="text" class="form-control bg-light" value="{{ auth()->user()->name ?? 'Admin' }}" readonly></td>
                                    <td>
                                        <select name="stok_review[{{ $fakeId }}][status_review]" class="form-select border-warning">
                                            <option value="Pending" {{ ($item['status_review'] ?? '') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="Approved" {{ ($item['status_review'] ?? '') === 'Approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="Approved - Fraud" {{ ($item['status_review'] ?? '') === 'Approved - Fraud' ? 'selected' : '' }}>Approved - Terindikasi Fraud (SOP 09)</option>
                                            <option value="Revisi" {{ ($item['status_review'] ?? '') === 'Revisi' ? 'selected' : '' }}>Revisi</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="stok_review[{{ $fakeId }}][catatan_reviewer]" class="form-control border-warning" placeholder="Ketik catatan review..." value="{{ $item['catatan_reviewer'] ?? '' }}"></td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- TAB 5: REKONSILIASI KARTU ATM (FORMAT TEGAS SECTION A & SECTION B ΓÇö E.4.3)-->
                <!-- ========================================================================= -->
                <div class="tab-pane fade p-3" id="atm" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold mb-1"><i class="bi bi-credit-card-2-front text-primary me-2"></i>KKA E.4.3 Rekonsiliasi Persediaan Kartu ATM &amp; PIN Mailer</h6>
                            <small class="text-muted">Terbagi tegas atas Section A (Rekonsiliasi Stok, Saldo Buku &amp; PIN Mailer) dan Section B (Kontrol Pengelolaan Ya/Tidak).</small>
                        </div>
                        @if(isset($id))
                        <div>
                            <a href="{{ route('daily-register.physical-kka.print-kka-kartu', $id) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold">
                                <i class="bi bi-printer me-1"></i> Cetak KKA Kartu ATM (E.4.3)
                            </a>
                        </div>
                        @endif
                    </div>

                    <!-- SECTION A: REKONSILIASI STOK, SALDO BUKU (2 PEMBANDING) -->
                    <div class="section-card">
                        <div class="section-card-header">
                            <div>
                                <i class="bi bi-table me-1"></i>SECTION A ΓÇö REKONSILIASI STOK, SALDO BUKU (2 PEMBANDING), &amp; RINCIAN JENIS KARTU
                            </div>
                            @if(!$isAdmin)
                            <button type="button" class="btn btn-xs btn-outline-primary fw-bold" id="btnAddAtmStockRow" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                                <i class="bi bi-plus-lg"></i> + Tambah Jenis Kartu
                            </button>
                            @endif
                        </div>
                        <div class="p-3">
                            <div class="table-responsive">
                                <table class="table table-bordered table-excel" id="tableAtmSectionA">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" style="width: 3%;">No</th>
                                            <th rowspan="2" style="min-width: 150px;">Jenis Kartu ATM</th>
                                            <th colspan="3" class="bg-light">Rincian Fisik Aktual</th>
                                            <th colspan="5" style="background:#eff6ff; color:#1e40af;">Pembanding 1: Saldo Buku Persediaan (Nilai Modal &amp; Qty)</th>
                                            <th colspan="3" style="background:#ecfdf5; color:#047857;">Pembanding 2: Fisik Kartu vs PIN Mailer</th>
                                            <th rowspan="2" style="width: 5%;">Aksi</th>
                                        </tr>
                                        <tr>
                                            <!-- Rincian Fisik -->
                                            <th style="min-width: 80px;">Qty Kosong</th>
                                            <th style="min-width: 80px;">Qty Rusak</th>
                                            <th style="min-width: 85px;">Total Fisik</th>

                                            <!-- Pembanding 1 -->
                                            <th style="min-width: 95px;">Harga Modal (Rp)</th>
                                            <th style="min-width: 120px;">Nominal Fisik (Rp)</th>
                                            <th style="min-width: 90px;">Saldo Buku (Qty)</th>
                                            <th style="min-width: 120px;">Saldo Buku (Rp)</th>
                                            <th style="min-width: 110px;">Selisih Stok (Rp)</th>

                                            <!-- Pembanding 2 -->
                                            <th style="min-width: 85px;">Fisik Kartu</th>
                                            <th style="min-width: 85px;">PIN Mailer</th>
                                            <th style="min-width: 85px;">Selisih PIN</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyAtmSectionA">
                                        @foreach($kartuAtmStockRows as $sIdx => $row)
                                        <tr class="atm-stock-calc-row">
                                            <td class="text-center fw-bold">{{ $sIdx + 1 }}</td>
                                            <td>
                                                <input type="text" name="kartu_atm_stock[{{ $sIdx }}][jenis_kartu]" class="form-control form-control-sm fw-bold atm-jenis-name" value="{{ $row['jenis_kartu'] }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                                                <input type="hidden" name="kartu_atm_stock[{{ $sIdx }}][keterangan_lokasi]" value="{{ $row['keterangan_lokasi'] ?? 'Brankas Khasanah' }}">
                                            </td>
                                            <td>
                                                <input type="number" min="0" step="1" name="kartu_atm_stock[{{ $sIdx }}][qty_fisik_kosong]" class="form-control form-control-sm text-end atm-qty-kosong" placeholder="0" value="{{ $row['qty_fisik_kosong'] ?? 0 }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                                            </td>
                                            <td>
                                                <input type="number" min="0" step="1" name="kartu_atm_stock[{{ $sIdx }}][qty_fisik_rusak]" class="form-control form-control-sm text-end atm-qty-rusak" placeholder="0" value="{{ $row['qty_fisik_rusak'] ?? 0 }}" {{ $isAdmin ? 'readonly' : '' }}>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-end fw-bold atm-total-fisik-display border-0 bg-transparent" readonly value="{{ (int)($row['qty_fisik_kosong'] ?? 0) + (int)($row['qty_fisik_rusak'] ?? 0) }}">
                                            </td>
                                            <td>
                                                <input type="number" step="any" name="kartu_atm_stock[{{ $sIdx }}][harga_modal]" class="form-control form-control-sm text-end atm-harga-modal" value="{{ $row['harga_modal'] ?? ($row['jenis_kartu'] === 'Kartu Silver' ? 42180 : 68820) }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-end fw-bold atm-nominal-fisik-display border-0 bg-transparent text-primary" readonly value="Rp {{ number_format($row['nominal_fisik'] ?? 0, 0, ',', '.') }}">
                                            </td>
                                            <td>
                                                <input type="number" min="0" step="1" name="kartu_atm_stock[{{ $sIdx }}][saldo_buku_qty]" class="form-control form-control-sm text-end atm-buku-qty" placeholder="0" value="{{ $row['saldo_buku_qty'] ?? 0 }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                                            </td>
                                            <td>
                                                <input type="number" step="any" name="kartu_atm_stock[{{ $sIdx }}][saldo_buku_nominal]" class="form-control form-control-sm text-end atm-buku-nom" placeholder="0" value="{{ $row['saldo_buku_nominal'] ?? 0 }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-end fw-bold atm-selisih-persediaan-display border-0 bg-transparent" readonly value="0">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-end atm-kartu-count-display border-0 bg-transparent" readonly value="{{ $row['qty_fisik_kosong'] ?? 0 }}">
                                            </td>
                                            <td>
                                                <input type="number" min="0" step="1" name="kartu_atm_stock[{{ $sIdx }}][pin_mailer_qty]" class="form-control form-control-sm text-end atm-pin-qty" placeholder="0" value="{{ $row['pin_mailer_qty'] ?? ($row['qty_fisik_kosong'] ?? 0) }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-end fw-bold atm-selisih-pin-display border-0 bg-transparent" readonly value="0">
                                            </td>
                                            <td class="text-center">
                                                @if(!$isAdmin && $sIdx >= 3)
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-atm-stock"><i class="bi bi-x-circle-fill"></i></button>
                                                @else
                                                -
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="background:#f1f5f9; font-weight:bold;">
                                            <td colspan="2" class="text-center">TOTAL AKUMULASI:</td>
                                            <td class="text-end" id="totAtmKosong">0</td>
                                            <td class="text-end" id="totAtmRusak">0</td>
                                            <td class="text-end" id="totAtmFisik">0</td>
                                            <td class="text-center">-</td>
                                            <td class="text-end text-primary" id="totAtmNominalFisik">Rp 0</td>
                                            <td class="text-end" id="totAtmBukuQty">0</td>
                                            <td class="text-end" id="totAtmBukuNom">Rp 0</td>
                                            <td class="text-end" id="totAtmSelisihStok">Rp 0</td>
                                            <td class="text-end" id="totAtmFisikKartu">0</td>
                                            <td class="text-end" id="totAtmPinMailer">0</td>
                                            <td class="text-end" id="totAtmSelisihPin">0</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Indikator Otomatis Selisih & Keterangan Findings -->
                            <div class="row g-3 mt-2 align-items-center">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted mb-1">Status Indikator Rekonsiliasi Section A:</label>
                                    <div>
                                        <span id="badgeAtmStatusSelisih" class="badge-soft badge-soft-success px-3 py-2 w-100 text-center fs-6">
                                            <i class="bi bi-check-circle-fill me-1"></i> Tidak Terdapat Selisih
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold text-muted mb-1">
                                        Penyebab Selisih / Findings:
                                        <span class="text-danger small fw-normal" id="labelAtmFindingsWajib" style="display:none;">*(Wajib diisi jika terdapat selisih fisik/saldo/PIN!)*</span>
                                    </label>
                                    <input type="text" name="rekon_kartu_penyebab_selisih" id="inputAtmFindings" class="form-control form-control-sm" value="{{ $rekonKartuPenyebabSelisih }}" placeholder="Tidak terdapat selisih..." {{ $isAdmin ? 'readonly' : 'required' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION B: KONTROL PENGELOLAAN KARTU ATM (CHECKLIST YA / TIDAK) -->
                    <div class="section-card">
                        <div class="section-card-header">
                            <div>
                                <i class="bi bi-shield-check me-1"></i>SECTION B ΓÇö KONTROL PENGELOLAAN KARTU ATM (CHECKLIST MURNI YA / TIDAK)
                            </div>
                            <span class="small text-muted fw-normal">Format checklist murni satu per satu (tanpa tombol otomatis).</span>
                        </div>
                        <div class="p-3">
                            <div class="table-responsive">
                                <table class="table table-bordered table-excel mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 4%; text-align: center;">No</th>
                                            <th style="width: 58%;">Butir Pengujian Pengendalian Internal (Kontrol)</th>
                                            <th style="width: 16%; text-align: center;">Status (Ya / Tidak)</th>
                                            <th style="width: 22%;">Catatan / Keterangan Observasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($kartuAtmControlRows as $cIdx => $cItem)
                                        <tr>
                                            <td class="text-center fw-bold">{{ $cIdx + 1 }}</td>
                                            <td>
                                                <strong>{{ $cItem['jenis_kartu'] }}</strong>
                                                <input type="hidden" name="kartu_atm_controls[{{ $cIdx }}][jenis_kartu]" value="{{ $cItem['jenis_kartu'] }}">
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-3">
                                                    <div class="form-check form-check-inline m-0">
                                                        <input class="form-check-input atm-ctrl-radio" type="radio" name="kartu_atm_controls[{{ $cIdx }}][jawaban_kontrol]" id="atmCtrlYa_{{ $cIdx }}" value="Ya" {{ ($cItem['jawaban_kontrol'] ?? 'Ya') === 'Ya' ? 'checked' : '' }} {{ $isAdmin ? 'disabled' : '' }}>
                                                        <label class="form-check-label small fw-bold text-success" for="atmCtrlYa_{{ $cIdx }}">Ya</label>
                                                    </div>
                                                    <div class="form-check form-check-inline m-0">
                                                        <input class="form-check-input atm-ctrl-radio" type="radio" name="kartu_atm_controls[{{ $cIdx }}][jawaban_kontrol]" id="atmCtrlTidak_{{ $cIdx }}" value="Tidak" {{ ($cItem['jawaban_kontrol'] ?? '') === 'Tidak' ? 'checked' : '' }} {{ $isAdmin ? 'disabled' : '' }}>
                                                        <label class="form-check-label small fw-bold text-danger" for="atmCtrlTidak_{{ $cIdx }}">Tidak</label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="kartu_atm_controls[{{ $cIdx }}][catatan_kontrol]" class="form-control form-control-sm" placeholder="Catatan opsional..." value="{{ $cItem['catatan_kontrol'] ?? '' }}" {{ $isAdmin ? 'readonly' : '' }}>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- TAB 6: REKONSILIASI BARANG CETAKAN (MERGED KRITERIA & CHECKLIST KONTROL) -->
                <!-- ========================================================================= -->
                <div class="tab-pane fade p-3" id="cetakan" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold mb-1"><i class="bi bi-printer text-success me-2"></i>KKA Rekonsiliasi Persediaan Barang Cetakan Berharga</h6>
                            <small class="text-muted">Memuat Formulir Pembukaan ATM. Kolom Kondisi telah dilebur langsung ke Kriteria (Kosong/Rusak terpisah). Dilengkapi Checklist Kontrol Ya/Tidak.</small>
                        </div>
                        <div class="d-flex gap-2">
                            @if(isset($id))
                            <a href="{{ route('daily-register.physical-kka.print-kka-cetakan', $id) }}" target="_blank" class="btn btn-sm btn-outline-success fw-bold">
                                <i class="bi bi-printer me-1"></i> Cetak KKA Cetakan
                            </a>
                            @endif
                            @if(!$isAdmin)
                            <button type="button" class="btn btn-sm btn-success fw-bold shadow-sm" id="btnAddBctItem">
                                <i class="bi bi-plus-circle me-1"></i> + Tambah Item Barang Cetakan
                            </button>
                            @endif
                        </div>
                    </div>

                    <!-- TABEL UTAMA BARANG CETAKAN (TANPA KOLOM KONDISI) -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-excel" id="tableBarangCetakan">
                            <thead>
                                <tr>
                                    <th style="width: 3%;">No</th>
                                    <th style="width: 12%;">Kategori</th>
                                    <th style="width: 20%;">Objek / Nama Barang</th>
                                    <th style="width: 23%;">Kriteria Pengujian (Objek &amp; Kondisi)</th>
                                    <th style="width: 5%;">Satuan</th>
                                    <th style="width: 9%;">Saldo Buku</th>
                                    <th style="width: 9%;">Hasil Fisik</th>
                                    <th style="width: 7%;">Selisih</th>
                                    <th style="width: 5%;">Status</th>
                                    <th style="min-width: 130px;">Keterangan / Sebab</th>
                                    <th style="width: 4%; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyBarangCetakan">
                                @foreach($barangCetakanItems as $bIdx => $bItem)
                                <tr class="bct-calc-row">
                                    <td class="text-center fw-bold">{{ $bIdx + 1 }}</td>
                                    <td>
                                        <input type="text" name="barang_cetakan_details[{{ $bIdx }}][kategori]" class="form-control form-control-sm bg-light" value="{{ $bItem['kategori'] }}" readonly>
                                    </td>
                                    <td>
                                        <strong>{{ $bItem['nama_barang'] }}</strong>
                                        <input type="hidden" name="barang_cetakan_details[{{ $bIdx }}][nama_barang]" value="{{ $bItem['nama_barang'] }}">
                                    </td>
                                    <td>
                                        <input type="text" name="barang_cetakan_details[{{ $bIdx }}][kriteria]" class="form-control form-control-sm bct-kriteria-input" value="{{ $bItem['kriteria'] }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                                    </td>
                                    <td>
                                        <input type="text" name="barang_cetakan_details[{{ $bIdx }}][satuan]" class="form-control form-control-sm text-center bg-light" value="{{ $bItem['satuan'] }}" readonly>
                                    </td>
                                    <td>
                                        <input type="number" step="any" name="barang_cetakan_details[{{ $bIdx }}][stok_sistem]" class="form-control form-control-sm text-end bct-stok-buku" placeholder="0" value="{{ $bItem['stok_sistem'] ?? 0 }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                                    </td>
                                    <td>
                                        <input type="number" step="any" name="barang_cetakan_details[{{ $bIdx }}][hasil_fisik]" class="form-control form-control-sm text-end bct-hasil-fisik" placeholder="0" value="{{ $bItem['hasil_fisik'] ?? '' }}" {{ $isAdmin ? 'readonly' : 'required' }}>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm text-end fw-bold bct-selisih-display border-0 bg-transparent" readonly value="{{ $bItem['selisih'] ?? 0 }}">
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-soft bct-status-badge {{ ($bItem['status_uji'] ?? '') === 'EXCEPTION' ? 'badge-soft-danger' : 'badge-soft-success' }}">
                                            {{ $bItem['status_uji'] ?? 'Sesuai' }}
                                        </span>
                                    </td>
                                    <td>
                                        <input type="text" name="barang_cetakan_details[{{ $bIdx }}][penyebab]" class="form-control form-control-sm" placeholder="Keterangan..." value="{{ $bItem['penyebab'] ?? '' }}" {{ $isAdmin ? 'readonly' : '' }}>
                                    </td>
                                    <td class="text-center text-muted">
                                        <i class="bi bi-dash"></i>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- CHECKLIST KONTROL BARANG CETAKAN (YA / TIDAK) -->
                    <div class="section-card">
                        <div class="section-card-header">
                            <div>
                                <i class="bi bi-shield-check me-1"></i>PENGUJIAN KONTROL FISIK BARANG CETAKAN (CHECKLIST MURNI YA / TIDAK)
                            </div>
                            <span class="small text-muted fw-normal">Pengujian prosedur penyimpanan, register, warkat rusak, dan nomor seri.</span>
                        </div>
                        <div class="p-3">
                            <div class="table-responsive">
                                <table class="table table-bordered table-excel mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 4%; text-align: center;">No</th>
                                            <th style="width: 60%;">Butir Uji Pengendalian Internal Barang Cetakan</th>
                                            <th style="width: 16%; text-align: center;">Status (Ya / Tidak)</th>
                                            <th style="width: 20%;">Catatan Observasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($barangCetakanControls as $bcIdx => $bcItem)
                                        <tr>
                                            <td class="text-center fw-bold">{{ $bcItem['no'] }}</td>
                                            <td>
                                                <strong>{{ $bcItem['butir'] }}</strong>
                                                <input type="hidden" name="barang_cetakan_controls[{{ $bcIdx }}][butir]" value="{{ $bcItem['butir'] }}">
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-3">
                                                    <div class="form-check form-check-inline m-0">
                                                        <input class="form-check-input bct-ctrl-radio" type="radio" name="barang_cetakan_controls[{{ $bcIdx }}][jawaban]" id="bctCtrlYa_{{ $bcIdx }}" value="Ya" {{ ($bcItem['jawaban'] ?? 'Ya') === 'Ya' ? 'checked' : '' }} {{ $isAdmin ? 'disabled' : '' }}>
                                                        <label class="form-check-label small fw-bold text-success" for="bctCtrlYa_{{ $bcIdx }}">Ya</label>
                                                    </div>
                                                    <div class="form-check form-check-inline m-0">
                                                        <input class="form-check-input bct-ctrl-radio" type="radio" name="barang_cetakan_controls[{{ $bcIdx }}][jawaban]" id="bctCtrlTidak_{{ $bcIdx }}" value="Tidak" {{ ($bcItem['jawaban'] ?? '') === 'Tidak' ? 'checked' : '' }} {{ $isAdmin ? 'disabled' : '' }}>
                                                        <label class="form-check-label small fw-bold text-danger" for="bctCtrlTidak_{{ $bcIdx }}">Tidak</label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="barang_cetakan_controls[{{ $bcIdx }}][catatan]" class="form-control form-control-sm" placeholder="Catatan kontrol..." value="{{ $bcItem['catatan'] ?? '' }}" {{ $isAdmin ? 'readonly' : '' }}>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Tombol Aksi Simpan / Submit -->
        <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i> Form Tahap 2 otomatis tersimpan ke nomor arsip aktif dan dapat langsung dicetak (PDF) oleh RA maupun Auditor.
                </small>
            </div>
            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="btnSubmitFinal" style="background-color: var(--primary-blue); border-color: var(--primary-blue); border-radius: 8px; font-size: 0.88rem; padding: 0.55rem 1.4rem;">
                <i class="bi bi-save me-1"></i> {{ $isAdmin ? 'Simpan Review Admin' : 'Simpan KKA Fisik & Masuk Arsip Cetak' }}
            </button>
        </div>
    </form>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT REAL-TIME CALCULATION LOGIC                                    -->
<!-- ========================================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isAdmin = {{ $isAdmin ? 'true' : 'false' }};

    function formatRp(num) {
        return 'Rp ' + (Math.round(num) || 0).toLocaleString('id-ID');
    }

    // =========================================================================
    // 1. LOGIKA KALKULASI TAB 1: KAS KHASANAH (3 KELOMPOK ULE, LOGAM, UTLE)
    // =========================================================================
    function calcKhasanah() {
        let subtotalUle = 0;
        let subtotalLogam = 0;
        let subtotalUtle = 0;

        document.querySelectorAll('.cash-calc-row').forEach(row => {
            const kelompok = row.dataset.kelompok;
            const nilai = parseFloat(row.dataset.nilai) || 0;
            const lembar = parseInt(row.querySelector('.cash-lembar')?.value) || 0;
            const nominal = lembar * nilai;

            const disp = row.querySelector('.cash-nominal-display');
            const raw = row.querySelector('.cash-nominal-raw');
            if (disp) disp.value = formatRp(nominal);
            if (raw) raw.value = nominal;

            if (kelompok === 'ULE') subtotalUle += nominal;
            else if (kelompok === 'Logam') subtotalLogam += nominal;
            else if (kelompok === 'UTLE') subtotalUtle += nominal;
        });

        const elSubUle = document.getElementById('subtotalUleDisplay');
        const elSubLogam = document.getElementById('subtotalLogamDisplay');
        const elSubUtle = document.getElementById('subtotalUtleDisplay');
        const elTotalFisik = document.getElementById('totalFisikKasDisplay');

        if (elSubUle) elSubUle.innerText = formatRp(subtotalUle);
        if (elSubLogam) elSubLogam.innerText = formatRp(subtotalLogam);
        if (elSubUtle) elSubUtle.innerText = formatRp(subtotalUtle);

        const totalFisik = subtotalUle + subtotalLogam + subtotalUtle;
        if (elTotalFisik) elTotalFisik.value = formatRp(totalFisik);

        // Rekonsiliasi vs Saldo Olibs
        const inputOlibs = document.getElementById('inputSaldoOlibs');
        const saldoOlibs = parseFloat(inputOlibs?.value) || 0;
        const selisih = totalFisik - saldoOlibs;

        const badgeSelisih = document.getElementById('badgeSelisihKas');
        const labelWajib = document.getElementById('labelPenyebabWajib');
        const penjelasanInput = document.getElementById('penjelasanSelisihKas');

        if (badgeSelisih) {
            badgeSelisih.classList.remove('badge-soft-success', 'badge-soft-danger', 'badge-soft-warning');
            if (Math.abs(selisih) < 0.01) {
                badgeSelisih.classList.add('badge-soft-success');
                badgeSelisih.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Nihil / Sesuai (Rp 0)';
                if (labelWajib) labelWajib.style.display = 'none';
                if (penjelasanInput) penjelasanInput.required = false;
            } else if (selisih > 0) {
                badgeSelisih.classList.add('badge-soft-warning');
                badgeSelisih.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Ada Selisih: Lebih (+ ' + formatRp(selisih) + ')';
                if (labelWajib) labelWajib.style.display = 'inline';
                if (penjelasanInput && !isAdmin) penjelasanInput.required = true;
            } else {
                badgeSelisih.classList.add('badge-soft-danger');
                badgeSelisih.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Ada Selisih: Kurang (- ' + formatRp(Math.abs(selisih)) + ')';
                if (labelWajib) labelWajib.style.display = 'inline';
                if (penjelasanInput && !isAdmin) penjelasanInput.required = true;
            }
        }
    }

    document.querySelectorAll('.cash-lembar').forEach(inp => {
        inp.addEventListener('input', calcKhasanah);
    });
    const inputOlibs = document.getElementById('inputSaldoOlibs');
    if (inputOlibs) inputOlibs.addEventListener('input', calcKhasanah);

    calcKhasanah();

    // =========================================================================
    // RE-INDEXING HELPERS (Prevent index collision and data overwrite on submit)
    // =========================================================================
    function reindexKaset(card) {
        const mIdx = card.dataset.atmIndex;
        const tbody = card.querySelector('.tbody-kaset');
        if (!tbody) return;
        tbody.querySelectorAll('.kaset-row').forEach((row, cIdx) => {
            row.querySelectorAll('input, select, textarea').forEach(inp => {
                if (inp.name) {
                    inp.name = inp.name.replace(/atm_machines\[\d+\]\[cassettes\]\[\d+\]/, `atm_machines[${mIdx}][cassettes][${cIdx}]`);
                }
            });
        });
    }

    function reindexAtmCards() {
        const container = document.getElementById('atmCardsContainer');
        if (!container) return;
        container.querySelectorAll('.atm-card-item').forEach((card, mIdx) => {
            card.dataset.atmIndex = mIdx;
            card.querySelectorAll('input, select, textarea').forEach(inp => {
                if (inp.name && inp.name.startsWith('atm_machines[')) {
                    inp.name = inp.name.replace(/^atm_machines\[\d+\]/, `atm_machines[${mIdx}]`);
                }
            });
            reindexKaset(card);
        });
    }

    function reindexAtmStock() {
        const tbody = document.getElementById('tbodyAtmSectionA');
        if (!tbody) return;
        tbody.querySelectorAll('.atm-stock-calc-row').forEach((row, idx) => {
            const noCol = row.querySelector('td:first-child');
            if (noCol) noCol.innerText = idx + 1;
            row.querySelectorAll('input, select, textarea').forEach(inp => {
                if (inp.name && inp.name.startsWith('kartu_atm_stock[')) {
                    inp.name = inp.name.replace(/^kartu_atm_stock\[\d+\]/, `kartu_atm_stock[${idx}]`);
                }
            });
        });
    }

    function reindexBctRows() {
        const tbody = document.getElementById('tbodyBarangCetakan');
        if (!tbody) return;
        tbody.querySelectorAll('.bct-calc-row').forEach((row, idx) => {
            const noCol = row.querySelector('td:first-child');
            if (noCol) noCol.innerText = idx + 1;
            row.querySelectorAll('input, select, textarea').forEach(inp => {
                if (inp.name && inp.name.startsWith('barang_cetakan_details[')) {
                    inp.name = inp.name.replace(/^barang_cetakan_details\[\d+\]/, `barang_cetakan_details[${idx}]`);
                }
            });
        });
    }

    // =========================================================================
    // 2. LOGIKA KALKULASI TAB 2: CASH OPNAME MESIN ATM & KASET (CARD MODEL)
    // =========================================================================
    function calcAtmCard(card) {
        let totalFisik = 0;
        let hasCassettes = false;

        card.querySelectorAll('.kaset-row').forEach(row => {
            const lembarInp = row.querySelector('.kaset-lembar');
            const denomInp = row.querySelector('.kaset-denom');
            const totalInp = row.querySelector('.kaset-total-nominal');

            const lembar = parseInt(lembarInp?.value) || 0;
            const denom = parseFloat(denomInp?.value) || 0;
            
            let total = parseFloat(totalInp?.value);
            if (isNaN(total) || (lembar > 0 && total === 0)) {
                total = lembar * denom;
                if (totalInp) totalInp.value = total;
            }
            if (total > 0 || lembar > 0) hasCassettes = true;
            totalFisik += (total || 0);
        });

        const totalDisp = card.querySelector('.atm-total-fisik-display');
        if (totalDisp) totalDisp.innerText = formatRp(totalFisik);

        const saldoCbsInp = card.querySelector('.atm-saldo-cbs');
        const saldoCbs = parseFloat(saldoCbsInp?.value) || 0;
        const selisih = saldoCbs - totalFisik;

        const selisihBadge = card.querySelector('.atm-selisih-badge');
        const statusBadge = card.querySelector('.atm-status-badge');
        const ketInput = card.querySelector('.atm-keterangan-selisih');
        const ketWajib = card.querySelector('.atm-ket-wajib');

        if (statusBadge) {
            if (hasCassettes) {
                statusBadge.className = 'badge-soft badge-soft-success atm-status-badge';
                statusBadge.innerHTML = '<i class="bi bi-check2-circle"></i> Selesai Diuji';
            } else {
                statusBadge.className = 'badge-soft badge-soft-warning atm-status-badge';
                statusBadge.innerHTML = '<i class="bi bi-clock"></i> Belum Diuji';
            }
        }

        if (selisihBadge) {
            selisihBadge.classList.remove('badge-soft-success', 'badge-soft-danger', 'badge-soft-warning');
            if (Math.abs(selisih) < 0.01) {
                selisihBadge.classList.add('badge-soft-success');
                selisihBadge.innerHTML = 'Nihil / Sesuai (Rp 0)';
                if (ketWajib) ketWajib.style.display = 'none';
                if (ketInput) ketInput.required = false;
            } else if (totalFisik > saldoCbs) {
                selisihBadge.classList.add('badge-soft-warning');
                selisihBadge.innerHTML = 'Ada Selisih: Lebih (+ ' + formatRp(Math.abs(totalFisik - saldoCbs)) + ')';
                if (ketWajib) ketWajib.style.display = 'inline';
                if (ketInput && !isAdmin) ketInput.required = true;
            } else {
                selisihBadge.classList.add('badge-soft-danger');
                selisihBadge.innerHTML = 'Ada Selisih: Kurang (- ' + formatRp(Math.abs(saldoCbs - totalFisik)) + ')';
                if (ketWajib) ketWajib.style.display = 'inline';
                if (ketInput && !isAdmin) ketInput.required = true;
            }
        }
    }

    function initAtmCardListeners(card) {
        card.querySelectorAll('.kaset-lembar, .kaset-denom').forEach(inp => {
            inp.addEventListener('input', function() {
                const row = this.closest('.kaset-row');
                const lembar = parseInt(row.querySelector('.kaset-lembar')?.value) || 0;
                const denom = parseFloat(row.querySelector('.kaset-denom')?.value) || 0;
                const totalInp = row.querySelector('.kaset-total-nominal');
                if (totalInp) totalInp.value = lembar * denom;
                calcAtmCard(card);
            });
        });

        card.querySelectorAll('.kaset-total-nominal, .atm-saldo-cbs').forEach(inp => {
            inp.addEventListener('input', () => calcAtmCard(card));
        });

        const denomSel = card.querySelector('.atm-denom-select');
        if (denomSel) {
            denomSel.addEventListener('change', function() {
                const chosen = this.value;
                card.querySelectorAll('.kaset-denom').forEach(dInp => {
                    if (dInp.value === '100000' || dInp.value === '50000') {
                        dInp.value = chosen;
                    }
                });
                calcAtmCard(card);
            });
        }

        const btnAddKaset = card.querySelector('.btn-add-kaset');
        if (btnAddKaset) {
            btnAddKaset.addEventListener('click', function() {
                const tbody = card.querySelector('.tbody-kaset');
                const mIdx = card.dataset.atmIndex;
                const cIdx = tbody.querySelectorAll('.kaset-row').length;
                const defDenom = card.querySelector('.atm-denom-select')?.value || 100000;

                const tr = document.createElement('tr');
                tr.className = 'kaset-row';
                tr.innerHTML = `
                    <td>
                        <select name="atm_machines[${mIdx}][cassettes][${cIdx}][nama_kaset]" class="form-select form-select-sm">
                            <option value="Cassette 1">Cassette 1</option>
                            <option value="Cassette 2">Cassette 2</option>
                            <option value="Cassette 3">Cassette 3</option>
                            <option value="Cassette 4">Cassette 4</option>
                            <option value="Kaset Cadangan" selected>Kaset Cadangan</option>
                            <option value="Kaset Reject">Kaset Reject (Cassette Reject Bin)</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="atm_machines[${mIdx}][cassettes][${cIdx}][denominasi]" class="form-control form-control-sm text-end kaset-denom" value="${defDenom}">
                    </td>
                    <td>
                        <input type="number" min="0" step="1" name="atm_machines[${mIdx}][cassettes][${cIdx}][jumlah_lembar]" class="form-control form-control-sm text-end kaset-lembar" placeholder="0" value="0">
                    </td>
                    <td>
                        <input type="number" step="any" name="atm_machines[${mIdx}][cassettes][${cIdx}][total_nominal]" class="form-control form-control-sm text-end fw-bold kaset-total-nominal text-primary" placeholder="0" value="0" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-kaset"><i class="bi bi-x-circle-fill"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
                reindexKaset(card);

                tr.querySelectorAll('.kaset-lembar, .kaset-denom').forEach(inp => {
                    inp.addEventListener('input', function() {
                        const lembar = parseInt(tr.querySelector('.kaset-lembar')?.value) || 0;
                        const denom = parseFloat(tr.querySelector('.kaset-denom')?.value) || 0;
                        tr.querySelector('.kaset-total-nominal').value = lembar * denom;
                        calcAtmCard(card);
                    });
                });
                tr.querySelector('.kaset-total-nominal').addEventListener('input', () => calcAtmCard(card));
                tr.querySelector('.btn-remove-kaset').addEventListener('click', function() {
                    tr.remove();
                    reindexKaset(card);
                    calcAtmCard(card);
                });

                calcAtmCard(card);
            });
        }

        card.querySelectorAll('.btn-remove-kaset').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.kaset-row').remove();
                reindexKaset(card);
                calcAtmCard(card);
            });
        });

        const btnRemoveAtm = card.querySelector('.btn-remove-atm');
        if (btnRemoveAtm) {
            btnRemoveAtm.addEventListener('click', function() {
                if (confirm('Hapus Card Mesin ATM ini?')) {
                    card.remove();
                    reindexAtmCards();
                }
            });
        }

        calcAtmCard(card);
    }

    document.querySelectorAll('.atm-card-item').forEach(card => initAtmCardListeners(card));

    // + Tambah Mesin ATM Button
    const btnTambahAtmCard = document.getElementById('btnTambahAtmCard');
    if (btnTambahAtmCard) {
        btnTambahAtmCard.addEventListener('click', function() {
            const container = document.getElementById('atmCardsContainer');
            const newMIdx = container.querySelectorAll('.atm-card-item').length;
            const newTerminalId = '00' + (120 + newMIdx);

            const div = document.createElement('div');
            div.className = 'atm-card-item';
            div.dataset.atmIndex = newMIdx;
            div.innerHTML = `
                <div class="atm-card-topbar">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-5 text-primary">≡ƒÅº</span>
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <span class="input-group-text fw-bold bg-white text-muted">ID ATM:</span>
                            <input type="text" name="atm_machines[${newMIdx}][terminal_id]" class="form-control font-monospace fw-bold fs-6 atm-terminal-id text-primary" placeholder="00128" value="${newTerminalId}" required>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-soft badge-soft-warning atm-status-badge">
                            <i class="bi bi-clock"></i> Belum Diuji
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-atm" title="Hapus Mesin ATM">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>

                <div class="atm-ident-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted mb-0">Lokasi ATM:</label>
                            <input type="text" name="atm_machines[${newMIdx}][lokasi]" class="form-control form-control-sm" placeholder="Contoh: ATM Museum / Galeri KC" value="ATM Baru Cabang" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted mb-0">Merk Mesin ATM:</label>
                            <input type="text" name="atm_machines[${newMIdx}][merk]" class="form-control form-control-sm" placeholder="DBL / WCR / CRM" value="DBL">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted mb-0">Denominasi Mesin:</label>
                            <select name="atm_machines[${newMIdx}][denominasi]" class="form-select form-select-sm atm-denom-select">
                                <option value="100000" selected>Rp 100.000</option>
                                <option value="50000">Rp 50.000</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted mb-0">Tgl Pengisian Terakhir:</label>
                            <input type="date" name="atm_machines[${newMIdx}][tanggal_pengisian_terakhir]" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-primary mb-0">Total Saldo Sistem (CBS):</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" step="any" name="atm_machines[${newMIdx}][saldo_cbs]" class="form-control form-control-sm text-end fw-bold atm-saldo-cbs" placeholder="0" value="0" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="atm-cassette-box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-dark"><i class="bi bi-inbox me-1 text-primary"></i>Rincian Fisik Uang Per Kaset ATM</span>
                        <button type="button" class="btn btn-xs btn-outline-primary btn-add-kaset fw-bold" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                            <i class="bi bi-plus-lg"></i> + Tambah Kaset
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-excel mb-0 table-kaset">
                            <thead>
                                <tr>
                                    <th style="width: 35%;">Nama / Jenis Kaset</th>
                                    <th style="width: 20%;">Denominasi (Rp)</th>
                                    <th style="width: 15%;">Jumlah Lembar</th>
                                    <th style="width: 25%;">Total Nominal Fisik Kaset (Rp)</th>
                                    <th style="width: 5%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="tbody-kaset">
                                <tr class="kaset-row">
                                    <td>
                                        <select name="atm_machines[${newMIdx}][cassettes][0][nama_kaset]" class="form-select form-select-sm">
                                            <option value="Cassette 1" selected>Cassette 1</option>
                                            <option value="Cassette 2">Cassette 2</option>
                                            <option value="Cassette 3">Cassette 3</option>
                                            <option value="Cassette 4">Cassette 4</option>
                                            <option value="Kaset Cadangan">Kaset Cadangan</option>
                                            <option value="Kaset Reject">Kaset Reject (Cassette Reject Bin)</option>
                                        </select>
                                    </td>
                                    <td><input type="number" name="atm_machines[${newMIdx}][cassettes][0][denominasi]" class="form-control form-control-sm text-end kaset-denom" value="100000"></td>
                                    <td><input type="number" min="0" step="1" name="atm_machines[${newMIdx}][cassettes][0][jumlah_lembar]" class="form-control form-control-sm text-end kaset-lembar" placeholder="0" value="0"></td>
                                    <td><input type="number" step="any" name="atm_machines[${newMIdx}][cassettes][0][total_nominal]" class="form-control form-control-sm text-end fw-bold kaset-total-nominal text-primary" placeholder="0" value="0" required></td>
                                    <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-kaset"><i class="bi bi-x-circle-fill"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="atm-calc-footer">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <span class="small fw-bold text-muted">Total Fisik Seluruh Kaset:</span>
                            <div class="fs-6 fw-bold text-primary atm-total-fisik-display font-monospace">Rp 0</div>
                        </div>
                        <div class="col-md-5">
                            <span class="small fw-bold text-muted">Status &amp; Selisih Kas ATM:</span>
                            <div>
                                <span class="badge-soft badge-soft-success atm-selisih-badge py-2 px-3 fs-6 w-100 text-center">
                                    Nihil / Sesuai (Rp 0)
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100 fw-bold" disabled>
                                <i class="bi bi-printer me-1"></i> Cetak BA ATM
                            </button>
                        </div>
                    </div>

                    <div class="mt-2">
                        <label class="form-label small fw-bold text-muted mb-0">
                            Keterangan Penyebab Selisih Kas ATM:
                            <span class="text-danger small fw-normal atm-ket-wajib" style="display:none;">*(Wajib diisi jika terdapat selisih!)</span>
                        </label>
                        <input type="text" name="atm_machines[${newMIdx}][keterangan_selisih]" class="form-control form-control-sm atm-keterangan-selisih" placeholder="Ketik penyebab selisih kas mesin ATM...">
                    </div>

                    <div class="row g-2 mt-2 pt-2 border-top">
                        <div class="col-md-3"><input type="text" name="atm_machines[${newMIdx}][pelaksana_nama]" class="form-control form-control-sm bg-light" value="{{ auth()->user()->name ?? 'Resident Auditor' }}" readonly></div>
                        <div class="col-md-3"><input type="text" name="atm_machines[${newMIdx}][pelaksana_jabatan]" class="form-control form-control-sm" value="RA Pelaksana SKAI"></div>
                        <div class="col-md-3"><input type="text" name="atm_machines[${newMIdx}][saksi_nama]" class="form-control form-control-sm" value="Supervisor ATM"></div>
                        <div class="col-md-3"><input type="text" name="atm_machines[${newMIdx}][saksi_jabatan]" class="form-control form-control-sm" value="Pemegang Kunci ATM Dual Control"></div>
                    </div>
                </div>
            `;
            container.appendChild(div);
            reindexAtmCards();
            initAtmCardListeners(div);
        });
    }

    // =========================================================================
    // 3. LOGIKA KALKULASI TAB 5: KARTU ATM (SECTION A & SECTION B)
    // =========================================================================
    function calcAtmSectionA() {
        let totKosong = 0;
        let totRusak = 0;
        let totFisik = 0;
        let totNomFisik = 0;
        let totBukuQty = 0;
        let totBukuNom = 0;
        let totPin = 0;
        let hasSelisih = false;

        document.querySelectorAll('.atm-stock-calc-row').forEach(row => {
            const kosong = parseInt(row.querySelector('.atm-qty-kosong')?.value) || 0;
            const rusak = parseInt(row.querySelector('.atm-qty-rusak')?.value) || 0;
            const totalFisik = kosong + rusak;

            const modal = parseFloat(row.querySelector('.atm-harga-modal')?.value) || 0;
            const nomFisik = modal * kosong;
            const bukuQty = parseInt(row.querySelector('.atm-buku-qty')?.value) || 0;
            let bukuNom = parseFloat(row.querySelector('.atm-buku-nom')?.value);
            if (isNaN(bukuNom) || (bukuQty > 0 && bukuNom === 0)) {
                bukuNom = bukuQty * modal;
                const bNomInp = row.querySelector('.atm-buku-nom');
                if (bNomInp) bNomInp.value = bukuNom;
            }

            const selisihPersediaan = nomFisik - (bukuNom || 0);
            const pinQty = parseInt(row.querySelector('.atm-pin-qty')?.value) || 0;
            const selisihPin = kosong - pinQty;

            const totFisikDisp = row.querySelector('.atm-total-fisik-display');
            const nomFisikDisp = row.querySelector('.atm-nominal-fisik-display');
            const selisihStokDisp = row.querySelector('.atm-selisih-persediaan-display');
            const kartuCountDisp = row.querySelector('.atm-kartu-count-display');
            const selisihPinDisp = row.querySelector('.atm-selisih-pin-display');

            if (totFisikDisp) totFisikDisp.value = totalFisik;
            if (nomFisikDisp) nomFisikDisp.value = formatRp(nomFisik);
            if (kartuCountDisp) kartuCountDisp.value = kosong;

            if (selisihStokDisp) {
                selisihStokDisp.value = Math.abs(selisihPersediaan) < 0.01 ? '0' : formatRp(selisihPersediaan);
                selisihStokDisp.className = 'form-control form-control-sm text-end fw-bold atm-selisih-persediaan-display border-0 bg-transparent ' + (Math.abs(selisihPersediaan) >= 0.01 ? 'text-danger' : 'text-success');
            }

            if (selisihPinDisp) {
                selisihPinDisp.value = selisihPin;
                selisihPinDisp.className = 'form-control form-control-sm text-end fw-bold atm-selisih-pin-display border-0 bg-transparent ' + (selisihPin !== 0 ? 'text-danger' : 'text-success');
            }

            totKosong += kosong;
            totRusak += rusak;
            totFisik += totalFisik;
            totNomFisik += nomFisik;
            totBukuQty += bukuQty;
            totBukuNom += (bukuNom || 0);
            totPin += pinQty;

            if (Math.abs(selisihPersediaan) >= 0.01 || selisihPin !== 0) {
                hasSelisih = true;
            }
        });

        // Update footer totals
        const elTotKosong = document.getElementById('totAtmKosong');
        const elTotRusak = document.getElementById('totAtmRusak');
        const elTotFisik = document.getElementById('totAtmFisik');
        const elTotNomFisik = document.getElementById('totAtmNominalFisik');
        const elTotBukuQty = document.getElementById('totAtmBukuQty');
        const elTotBukuNom = document.getElementById('totAtmBukuNom');
        const elTotSelisihStok = document.getElementById('totAtmSelisihStok');
        const elTotFisikKartu = document.getElementById('totAtmFisikKartu');
        const elTotPinMailer = document.getElementById('totAtmPinMailer');
        const elTotSelisihPin = document.getElementById('totAtmSelisihPin');

        if (elTotKosong) elTotKosong.innerText = totKosong;
        if (elTotRusak) elTotRusak.innerText = totRusak;
        if (elTotFisik) elTotFisik.innerText = totFisik;
        if (elTotNomFisik) elTotNomFisik.innerText = formatRp(totNomFisik);
        if (elTotBukuQty) elTotBukuQty.innerText = totBukuQty;
        if (elTotBukuNom) elTotBukuNom.innerText = formatRp(totBukuNom);
        if (elTotSelisihStok) elTotSelisihStok.innerText = formatRp(totNomFisik - totBukuNom);
        if (elTotFisikKartu) elTotFisikKartu.innerText = totKosong;
        if (elTotPinMailer) elTotPinMailer.innerText = totPin;
        if (elTotSelisihPin) elTotSelisihPin.innerText = totKosong - totPin;

        // Check if any Section B control is "Tidak"
        let hasControlTidak = false;
        document.querySelectorAll('.atm-ctrl-radio:checked').forEach(r => {
            if (r.value === 'Tidak') hasControlTidak = true;
        });

        const badgeStatus = document.getElementById('badgeAtmStatusSelisih');
        const findingsInput = document.getElementById('inputAtmFindings');
        const findingsWajib = document.getElementById('labelAtmFindingsWajib');

        if (badgeStatus) {
            badgeStatus.classList.remove('badge-soft-success', 'badge-soft-danger');
            if (!hasSelisih && !hasControlTidak) {
                badgeStatus.classList.add('badge-soft-success');
                badgeStatus.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Tidak Terdapat Selisih';
                if (findingsWajib) findingsWajib.style.display = 'none';
                if (findingsInput && (!findingsInput.value || findingsInput.value.includes('Terdapat selisih'))) {
                    findingsInput.value = 'Tidak terdapat selisih antara fisik kartu ATM, saldo persediaan, dan PIN Mailer';
                }
            } else {
                badgeStatus.classList.add('badge-soft-danger');
                badgeStatus.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Terdapat Selisih';
                if (findingsWajib) findingsWajib.style.display = 'inline';
                if (findingsInput && findingsInput.value === 'Tidak terdapat selisih antara fisik kartu ATM, saldo persediaan, dan PIN Mailer') {
                    findingsInput.value = '';
                }
            }
        }
    }

    function initAtmStockListeners(row) {
        row.querySelectorAll('input').forEach(inp => {
            inp.addEventListener('input', calcAtmSectionA);
        });
        const btnRemove = row.querySelector('.btn-remove-atm-stock');
        if (btnRemove) {
            btnRemove.addEventListener('click', function() {
                row.remove();
                reindexAtmStock();
                calcAtmSectionA();
            });
        }
    }

    document.querySelectorAll('.atm-stock-calc-row').forEach(r => initAtmStockListeners(r));
    document.querySelectorAll('.atm-ctrl-radio').forEach(r => r.addEventListener('change', calcAtmSectionA));

    // + Tambah Jenis Kartu
    const btnAddAtmStockRow = document.getElementById('btnAddAtmStockRow');
    if (btnAddAtmStockRow) {
        btnAddAtmStockRow.addEventListener('click', function() {
            const tbody = document.getElementById('tbodyAtmSectionA');
            const newIdx = tbody.querySelectorAll('tr').length;
            const tr = document.createElement('tr');
            tr.className = 'atm-stock-calc-row';
            tr.innerHTML = `
                <td class="text-center fw-bold">${newIdx + 1}</td>
                <td>
                    <input type="text" name="kartu_atm_stock[${newIdx}][jenis_kartu]" class="form-control form-control-sm fw-bold" placeholder="Nama Jenis Kartu" value="Kartu Tambahan ${newIdx + 1}" required>
                    <input type="hidden" name="kartu_atm_stock[${newIdx}][keterangan_lokasi]" value="Brankas Khasanah">
                </td>
                <td><input type="number" min="0" step="1" name="kartu_atm_stock[${newIdx}][qty_fisik_kosong]" class="form-control form-control-sm text-end atm-qty-kosong" placeholder="0" value="0" required></td>
                <td><input type="number" min="0" step="1" name="kartu_atm_stock[${newIdx}][qty_fisik_rusak]" class="form-control form-control-sm text-end atm-qty-rusak" placeholder="0" value="0"></td>
                <td><input type="text" class="form-control form-control-sm text-end fw-bold atm-total-fisik-display border-0 bg-transparent" readonly value="0"></td>
                <td><input type="number" step="any" name="kartu_atm_stock[${newIdx}][harga_modal]" class="form-control form-control-sm text-end atm-harga-modal" value="50000" required></td>
                <td><input type="text" class="form-control form-control-sm text-end fw-bold atm-nominal-fisik-display border-0 bg-transparent text-primary" readonly value="Rp 0"></td>
                <td><input type="number" min="0" step="1" name="kartu_atm_stock[${newIdx}][saldo_buku_qty]" class="form-control form-control-sm text-end atm-buku-qty" placeholder="0" value="0" required></td>
                <td><input type="number" step="any" name="kartu_atm_stock[${newIdx}][saldo_buku_nominal]" class="form-control form-control-sm text-end atm-buku-nom" placeholder="0" value="0" required></td>
                <td><input type="text" class="form-control form-control-sm text-end fw-bold atm-selisih-persediaan-display border-0 bg-transparent" readonly value="0"></td>
                <td><input type="text" class="form-control form-control-sm text-end atm-kartu-count-display border-0 bg-transparent" readonly value="0"></td>
                <td><input type="number" min="0" step="1" name="kartu_atm_stock[${newIdx}][pin_mailer_qty]" class="form-control form-control-sm text-end atm-pin-qty" placeholder="0" value="0" required></td>
                <td><input type="text" class="form-control form-control-sm text-end fw-bold atm-selisih-pin-display border-0 bg-transparent" readonly value="0"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-atm-stock"><i class="bi bi-x-circle-fill"></i></button></td>
            `;
            tbody.appendChild(tr);
            reindexAtmStock();
            initAtmStockListeners(tr);
            calcAtmSectionA();
        });
    }

    calcAtmSectionA();

    // =========================================================================
    // 4. LOGIKA KALKULASI TAB 6: BARANG CETAKAN (MERGED KRITERIA & 4 KONTROL)
    // =========================================================================
    function calcBctRow(row) {
        const stokInp = row.querySelector('.bct-stok-buku');
        const fisikInp = row.querySelector('.bct-hasil-fisik');
        const selisihDisp = row.querySelector('.bct-selisih-display');
        const badge = row.querySelector('.bct-status-badge');

        const stok = parseFloat(stokInp?.value) || 0;
        const fisikVal = fisikInp?.value;
        const fisik = parseFloat(fisikVal);

        if (isNaN(fisik) || fisikVal === '') {
            if (selisihDisp) selisihDisp.value = '0';
            if (badge) {
                badge.className = 'badge-soft badge-soft-secondary bct-status-badge';
                badge.innerText = 'Belum Diuji';
            }
            return;
        }

        const selisih = fisik - stok;
        if (selisihDisp) selisihDisp.value = selisih;

        if (badge) {
            if (Math.abs(selisih) >= 0.01) {
                badge.className = 'badge-soft badge-soft-danger bct-status-badge';
                badge.innerText = 'EXCEPTION';
            } else {
                badge.className = 'badge-soft badge-soft-success bct-status-badge';
                badge.innerText = 'Sesuai';
            }
        }
    }

    function initBctRowListeners(row) {
        row.querySelectorAll('.bct-stok-buku, .bct-hasil-fisik').forEach(inp => {
            inp.addEventListener('input', () => calcBctRow(row));
        });
        calcBctRow(row);
    }

    document.querySelectorAll('.bct-calc-row').forEach(r => initBctRowListeners(r));

    // + Tambah Item Barang Cetakan
    const btnAddBctItem = document.getElementById('btnAddBctItem');
    if (btnAddBctItem) {
        btnAddBctItem.addEventListener('click', function() {
            const tbody = document.getElementById('tbodyBarangCetakan');
            const newIdx = tbody.querySelectorAll('tr').length;
            const tr = document.createElement('tr');
            tr.className = 'bct-calc-row';
            tr.innerHTML = `
                <td class="text-center fw-bold">${newIdx + 1}</td>
                <td>
                    <select name="barang_cetakan_details[${newIdx}][kategori]" class="form-select form-select-sm">
                        <option value="Formulir">Formulir</option>
                        <option value="Buku Tabungan">Buku Tabungan</option>
                        <option value="Bilyet Deposito">Bilyet Deposito</option>
                        <option value="Cek">Cek</option>
                        <option value="Bilyet Giro">Bilyet Giro</option>
                        <option value="Kertas">Kertas</option>
                        <option value="Amplop">Amplop</option>
                        <option value="Perlengkapan">Perlengkapan</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="barang_cetakan_details[${newIdx}][nama_barang]" class="form-control form-control-sm" placeholder="Nama Barang Cetakan" required>
                </td>
                <td>
                    <input type="text" name="barang_cetakan_details[${newIdx}][kriteria]" class="form-control form-control-sm bct-kriteria-input" placeholder="Nama Barang ΓÇö Kosong / Belum Digunakan" required>
                </td>
                <td>
                    <select name="barang_cetakan_details[${newIdx}][satuan]" class="form-select form-select-sm">
                        <option value="Bh">Bh</option>
                        <option value="Rim">Rim</option>
                        <option value="Pack">Pack</option>
                        <option value="Dos">Dos</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="any" name="barang_cetakan_details[${newIdx}][stok_sistem]" class="form-control form-control-sm text-end bct-stok-buku" placeholder="0" value="0" required>
                </td>
                <td>
                    <input type="number" step="any" name="barang_cetakan_details[${newIdx}][hasil_fisik]" class="form-control form-control-sm text-end bct-hasil-fisik" placeholder="0" value="0" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm text-end fw-bold bct-selisih-display border-0 bg-transparent" readonly value="0">
                </td>
                <td class="text-center">
                    <span class="badge-soft badge-soft-success bct-status-badge">Sesuai</span>
                </td>
                <td>
                    <input type="text" name="barang_cetakan_details[${newIdx}][penyebab]" class="form-control form-control-sm" placeholder="Keterangan...">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-bct" title="Hapus Baris"><i class="bi bi-x-circle-fill"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
            reindexBctRows();
            tr.querySelector('.btn-remove-bct').addEventListener('click', function() {
                tr.remove();
                reindexBctRows();
            });
            initBctRowListeners(tr);
        });
    }

    // =========================================================================
    // 5. LOGIKA OBSERVASI LINGKUNGAN (DYNAMIC EXCEPTION & SEVERITY CALCULATION)
    // =========================================================================
    function calcSeverity(impact, likelihood, isCritical) {
        const score = impact * likelihood;
        let level = 'Rendah';
        if (isCritical || score >= 15) {
            level = 'Kritis';
        } else if (score >= 10) {
            level = 'Tinggi';
        } else if (score >= 5) {
            level = 'Sedang';
        }
        return { score, level };
    }

    function initObsRowListeners(row) {
        const selectHasil = row.querySelector('.hasil-obs');
        const excFields = row.querySelectorAll('.exc-field');
        const impactSel = row.querySelector('.impact-sel');
        const likelihoodSel = row.querySelector('.likelihood-sel');
        const criticalSel = row.querySelector('.critical-sel');
        const scoreInp = row.querySelector('.risk-score');
        const levelInp = row.querySelector('.risk-level');

        function updateObsRow() {
            const isException = (selectHasil && selectHasil.value === 'Tidak Sesuai');
            excFields.forEach(f => {
                f.disabled = !isException;
            });

            if (isException) {
                const impact = parseInt(impactSel?.value) || 1;
                const likelihood = parseInt(likelihoodSel?.value) || 1;
                const isCrit = (criticalSel?.value === 'Ya');
                const res = calcSeverity(impact, likelihood, isCrit);
                if (scoreInp) scoreInp.value = res.score;
                if (levelInp) levelInp.value = res.level;
            } else {
                if (scoreInp) scoreInp.value = 0;
                if (levelInp) levelInp.value = 'Rendah';
            }
        }

        if (selectHasil) selectHasil.addEventListener('change', updateObsRow);
        if (impactSel) impactSel.addEventListener('change', updateObsRow);
        if (likelihoodSel) likelihoodSel.addEventListener('change', updateObsRow);
        if (criticalSel) criticalSel.addEventListener('change', updateObsRow);
    }

    document.querySelectorAll('tr[data-type="obs"]').forEach(r => initObsRowListeners(r));

});
</script>

@endsection
