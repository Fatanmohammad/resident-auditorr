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
                    <i class="bi bi-printer"></i> Cetak Berita Acara
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.83rem;">
                    <li><h6 class="dropdown-header text-uppercase" style="font-size: 0.7rem;">Cetak Per Dokumen</h6></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.print-ba-kas', $id) }}" target="_blank"><i class="bi bi-cash-stack me-2 text-primary"></i>BA Kas Khasanah (Olibs)</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.print-ba-atm', $id) }}" target="_blank"><i class="bi bi-wallet2 me-2 text-info"></i>BA Cash Opname ATM</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.print-kka-kartu', $id) }}" target="_blank"><i class="bi bi-credit-card-2-front me-2 text-warning"></i>KKA Kartu ATM (E.4.3)</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.print-kka-stok', $id) }}" target="_blank"><i class="bi bi-journal-check me-2 text-primary"></i>KKA Stok Cek/BG</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.print-kka-cetakan', $id) }}" target="_blank"><i class="bi bi-printer me-2 text-success"></i>KKA Barang Cetakan</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.print-kka-observasi', $id) }}" target="_blank"><i class="bi bi-eye me-2 text-secondary"></i>KKA Observasi Lingkungan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item fw-bold text-primary" href="{{ route('daily-register.physical-kka.print-all', $id) }}" target="_blank"><i class="bi bi-collection me-2"></i>Cetak Berkas Lengkap (Dossier Tahap 2)</a></li>
                </ul>
            </div>

            <!-- Dropdown Download PDF Resmi -->
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-danger dropdown-toggle fw-bold shadow-sm d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 8px; font-size: 0.82rem; padding: 0.45rem 0.85rem;">
                    <i class="bi bi-file-earmark-pdf"></i> Download PDF
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.83rem;">
                    <li><h6 class="dropdown-header text-uppercase" style="font-size: 0.7rem;">Download PDF Per Dokumen</h6></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.download-pdf-kas', $id) }}"><i class="bi bi-cash-stack me-2 text-primary"></i>PDF Kas Khasanah (Olibs)</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.download-pdf-atm', $id) }}"><i class="bi bi-wallet2 me-2 text-info"></i>PDF Cash Opname ATM</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.download-pdf-kartu', $id) }}"><i class="bi bi-credit-card-2-front me-2 text-warning"></i>PDF KKA Kartu ATM (E.4.3)</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.download-pdf-stok', $id) }}"><i class="bi bi-journal-check me-2 text-primary"></i>PDF KKA Stok Cek/BG</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.download-pdf-cetakan', $id) }}"><i class="bi bi-printer me-2 text-success"></i>PDF KKA Barang Cetakan</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.download-pdf-observasi', $id) }}"><i class="bi bi-eye me-2 text-secondary"></i>PDF KKA Observasi Lingkungan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item fw-bold text-danger" href="{{ route('daily-register.physical-kka.download-pdf-all', $id) }}"><i class="bi bi-collection me-2"></i>Download PDF Berkas Lengkap (Rekap)</a></li>
                </ul>
            </div>

            <!-- Dropdown Download Excel Resmi -->
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-success dropdown-toggle fw-bold shadow-sm d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 8px; font-size: 0.82rem; padding: 0.45rem 0.85rem;">
                    <i class="bi bi-file-earmark-excel"></i> Download Excel
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.83rem;">
                    <li><h6 class="dropdown-header text-uppercase" style="font-size: 0.7rem;">Download Excel Per Dokumen</h6></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.export-excel-kas', $id) }}"><i class="bi bi-cash-stack me-2 text-primary"></i>Excel Kas Khasanah (Olibs)</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.export-excel-atm', $id) }}"><i class="bi bi-wallet2 me-2 text-info"></i>Excel Cash Opname ATM</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.export-excel-kartu', $id) }}"><i class="bi bi-credit-card-2-front me-2 text-warning"></i>Excel KKA Kartu ATM (E.4.3)</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.export-excel-stok', $id) }}"><i class="bi bi-journal-check me-2 text-primary"></i>Excel KKA Stok Cek/BG</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.export-excel-cetakan', $id) }}"><i class="bi bi-printer me-2 text-success"></i>Excel KKA Barang Cetakan</a></li>
                    <li><a class="dropdown-item" href="{{ route('daily-register.physical-kka.export-excel-observasi', $id) }}"><i class="bi bi-eye me-2 text-secondary"></i>Excel KKA Observasi Lingkungan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item fw-bold text-success" href="{{ route('daily-register.physical-kka.export-excel-all', $id) }}"><i class="bi bi-collection me-2"></i>Download Excel Berkas Lengkap (Rekap)</a></li>
                </ul>
            </div>
            @endif

            <span class="badge-soft {{ $isAdmin ? 'badge-soft-warning' : 'badge-soft-primary' }} px-3 py-2" style="font-size: 0.8rem;">
                <i class="bi {{ $isAdmin ? 'bi-shield-check' : 'bi-person-badge' }} me-1"></i>
                {{ $isAdmin ? 'Mode: Reviewer / Admin' : 'Mode: Input Lapangan (Resident Auditor)' }}
            </span>
        </div>
    </div>

    <!-- Global Header Inputs: No. Arsip & Posisi Tanggal Rekonsiliasi -->
    <div class="card mb-3 border-0 shadow-sm" style="border-radius: 10px; background: #ffffff;">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <label class="form-label small fw-bold mb-1 text-muted"><i class="bi bi-bookmark-check text-primary me-1"></i>Nomor Arsip Dokumen KKA Fisik</label>
                    <input type="text" id="globalNoArsip" class="form-control form-control-sm font-monospace fw-bold" value="{{ $noArsip }}" placeholder="ARSIP-KKA-..." {{ $isAdmin ? 'readonly' : '' }}>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold mb-1 text-muted"><i class="bi bi-calendar-event text-primary me-1"></i>Posisi Tanggal Rekonsiliasi Fisik</label>
                    <input type="date" id="globalPosisiTanggalRekon" class="form-control form-control-sm" value="{{ $posisiTanggalRekon }}" {{ $isAdmin ? 'readonly' : '' }}>
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
                    <form method="POST" action="{{ $isAdmin ? route('daily-register.physical-kka.review') : route('daily-register.physical-kka.submit') }}" novalidate>
                        @csrf
                        @if(isset($id))
                            <input type="hidden" name="physical_kka_id" value="{{ $id }}">
                        @endif
                        <input type="hidden" name="sub_tab" value="kas">
                        <input type="hidden" name="no_arsip" class="sync-no-arsip" value="{{ $noArsip }}">
                        <input type="hidden" name="posisi_tanggal_rekon" class="sync-posisi-tanggal" value="{{ $posisiTanggalRekon }}">

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
                                <span class="fw-bold text-primary small"><i class="bi bi-cash me-1"></i>KELOMPOK 1 — UANG KERTAS (ULE / LAYAK EDAR)</span>
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
                                                    <input type="number" min="0" step="1" name="cash_details[{{ $idx }}][jumlah_lembar]" class="form-control text-end cash-lembar" placeholder="0" value="{{ $item['jumlah_lembar'] ?? 0 }}" {{ $isAdmin ? 'readonly' : '' }}>
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
                                                <input type="number" min="0" step="1" name="cash_details[{{ $rowIdx }}][jumlah_lembar]" class="form-control text-end cash-lembar" placeholder="0" value="{{ $item['jumlah_lembar'] ?? 0 }}" {{ $isAdmin ? 'readonly' : '' }}>
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
                                                <input type="number" min="0" step="1" name="cash_details[{{ $rowIdx }}][jumlah_lembar]" class="form-control text-end cash-lembar" placeholder="0" value="{{ $item['jumlah_lembar'] ?? 0 }}" {{ $isAdmin ? 'readonly' : '' }}>
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
                                        <input type="number" step="any" name="saldo_kas_olibs" id="inputSaldoOlibs" class="form-control fw-bold text-end" value="{{ $saldoKasOlibs ?? 0 }}" placeholder="0" {{ $isAdmin ? 'readonly' : '' }}>
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

                    <!-- CARD EVALUASI & PENILAIAN RISIKO TUNGGAL (KAS KHASANAH) -->
                    <div class="card border-primary-subtle shadow-sm my-3 eval-card" id="evalCardKas" style="border-radius: 10px; overflow: hidden;">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3" style="background-color: #1e3a8a !important;">
                            <span class="fw-bold"><i class="bi bi-shield-check me-2"></i>Ringkasan Evaluasi &amp; Penilaian Risiko Halaman</span>
                            <span class="badge bg-light text-primary fw-semibold">Evaluasi Keseluruhan Cash Opname Khasanah</span>
                        </div>
                        <div class="card-body p-3 bg-light-subtle">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-secondary">
                                        <i class="bi bi-calendar-event me-1"></i>Tanggal Uji (Tgl Uji) <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tgl_uji" class="form-control form-control-sm eval-tgl-uji" value="{{ $evalKas['tgl_uji'] ?? date('Y-m-d') }}" {{ $isAdmin ? 'readonly' : '' }}>
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label fw-bold small text-secondary">
                                        <i class="bi bi-card-text me-1"></i>Penyebab &amp; Keterangan <span class="text-danger eval-req-indicator" style="display:none;">* (Wajib diisi jika terdapat selisih fisik vs saldo Olibs)</span>
                                    </label>
                                    <textarea name="penyebab_keterangan" rows="2" class="form-control form-control-sm eval-penyebab @error('penyebab_keterangan') is-invalid @enderror" placeholder="Catat uraian penyebab selisih fisik kas / keterangan keseluruhan pemeriksaan kas khasanah..." {{ $isAdmin ? 'readonly' : '' }}>{{ old('penyebab_keterangan', $evalKas['penyebab_keterangan'] ?? '') }}</textarea>
                                    <div class="invalid-feedback">{{ $errors->first('penyebab_keterangan') ?: 'Penyebab & keterangan wajib diisi karena terdapat selisih kas fisik vs saldo Olibs.' }}</div>
                                </div>

                                <div class="col-12">
                                    <div class="p-2 border rounded bg-white shadow-xs">
                                        <div class="row g-2 align-items-center text-center">
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Impact (1-5)</label>
                                                <input type="hidden" name="impact" class="eval-impact" value="{{ $evalKas['impact'] ?? 1 }}">
                                                <span class="badge bg-light text-dark border px-3 py-2 fs-6 eval-impact-badge">
                                                    {{ $evalKas['impact'] ?? 1 }}
                                                </span>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Likelihood (1-5)</label>
                                                <input type="hidden" name="likelihood" class="eval-likelihood" value="{{ $evalKas['likelihood'] ?? 1 }}">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <span class="badge bg-light text-dark border px-2 py-1 eval-likelihood-badge">
                                                            {{ $evalKas['likelihood'] ?? 1 }}
                                                        </span>
                                                        <span class="badge bg-warning-subtle text-dark border eval-recurrence-badge" style="font-size: 0.72rem;">
                                                            {{ ($evalKas['likelihood'] ?? 1) > 1 ? 'Temuan Berulang' : 'Temuan Baru' }}
                                                        </span>
                                                    </div>
                                                    <span class="small text-muted mt-1 eval-likelihood-note" style="font-size: 0.7rem;">
                                                        {{ ($evalKas['likelihood'] ?? 1) > 1 ? 'Terdeteksi riwayat temuan di cabang' : 'Temuan pertama kali' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Critical</label>
                                                <input type="hidden" name="is_critical" class="eval-critical" value="{{ !empty($evalKas['is_critical']) ? 1 : 0 }}">
                                                <span class="badge {{ !empty($evalKas['is_critical']) ? 'bg-danger text-white' : 'bg-success text-white' }} px-3 py-2 fs-6 eval-critical-badge">
                                                    {{ !empty($evalKas['is_critical']) ? 'Ya' : 'Tidak' }}
                                                </span>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Risk Score</label>
                                                <input type="text" name="risk_score" class="form-control form-control-sm text-center fw-bold eval-risk-score bg-light border-0" value="{{ $evalKas['risk_score'] ?? 1 }}" readonly>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Risk Level</label>
                                                <div class="d-flex align-items-center justify-content-center gap-2">
                                                    <input type="hidden" name="risk_level" class="eval-risk-level-input" value="{{ $evalKas['risk_level'] ?? 'Low' }}">
                                                    @php
                                                        $lvlBadgeKas = match($evalKas['risk_level'] ?? 'Low') {
                                                            'High' => 'bg-danger text-white',
                                                            'Moderate to High' => 'bg-danger-subtle text-danger-emphasis border border-danger',
                                                            'Moderate' => 'bg-warning text-dark',
                                                            'Low to Moderate' => 'bg-info text-dark',
                                                            default => 'bg-success text-white',
                                                        };
                                                    @endphp
                                                    <span class="badge px-3 py-2 fs-6 eval-risk-level-badge {{ $lvlBadgeKas }}">
                                                        {{ $evalKas['risk_level'] ?? 'Low' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Bar Tab 1: Simpan & Download -->
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if(isset($id))
                            <a href="{{ route('daily-register.physical-kka.download-pdf-kas', $id) }}" class="btn btn-sm btn-outline-danger fw-bold">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                            </a>
                            <a href="{{ route('daily-register.physical-kka.export-excel-kas', $id) }}" class="btn btn-sm btn-outline-success fw-bold">
                                <i class="bi bi-file-earmark-excel me-1"></i> Download Excel
                            </a>
                            <a href="{{ route('daily-register.physical-kka.print-ba-kas', $id) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold">
                                <i class="bi bi-printer me-1"></i> Cetak BA Kas
                            </a>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" style="background-color: var(--primary-blue); border-color: var(--primary-blue); border-radius: 8px;">
                            <i class="bi bi-save me-1"></i> {{ $isAdmin ? 'Simpan Review Admin' : 'Simpan Cash Opname Khasanah' }}
                        </button>
                    </div>
                </form>
            </div>

                <!-- ========================================================================= -->
                <!-- TAB 2: CASH OPNAME MESIN ATM & KASET (MODEL CARD PER MESIN ATM)          -->
                <!-- ========================================================================= -->
                <div class="tab-pane fade p-3" id="cash-atm" role="tabpanel">
                    <form method="POST" action="{{ $isAdmin ? route('daily-register.physical-kka.review') : route('daily-register.physical-kka.submit') }}" novalidate>
                        @csrf
                        @if(isset($id))
                            <input type="hidden" name="physical_kka_id" value="{{ $id }}">
                        @endif
                        <input type="hidden" name="sub_tab" value="atm">
                        <input type="hidden" name="no_arsip" class="sync-no-arsip" value="{{ $noArsip }}">
                        <input type="hidden" name="posisi_tanggal_rekon" class="sync-posisi-tanggal" value="{{ $posisiTanggalRekon }}">

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
                                            <span class="fs-5 text-primary">🏛️</span>
                                            <div class="input-group input-group-sm" style="width: 250px;">
                                                <span class="input-group-text fw-bold bg-white text-muted">ID ATM:</span>
                                                <input type="text" name="atm_machines[{{ $mIdx }}][terminal_id]" class="form-control font-monospace fw-bold fs-6 atm-terminal-id text-primary" placeholder="00128" value="{{ $mach->terminal_id }}" {{ $isAdmin ? 'readonly' : '' }}>
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
                                            <input type="text" name="atm_machines[{{ $mIdx }}][lokasi]" class="form-control form-control-sm" placeholder="Contoh: ATM Museum / Galeri KC" value="{{ $mach->lokasi }}" {{ $isAdmin ? 'readonly' : '' }}>
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
                                                <input type="number" step="any" name="atm_machines[{{ $mIdx }}][saldo_cbs]" class="form-control form-control-sm text-end fw-bold atm-saldo-cbs" placeholder="0" value="{{ $mach->saldo_cbs ?? 0 }}" {{ $isAdmin ? 'readonly' : '' }}>
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
                                                        <input type="number" step="any" name="atm_machines[{{ $mIdx }}][cassettes][{{ $cIdx }}][total_nominal]" class="form-control form-control-sm text-end fw-bold kaset-total-nominal text-primary" placeholder="0" value="{{ $cas->total_nominal ?? 0 }}" {{ $isAdmin ? 'readonly' : '' }}>
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
                                                        <input type="number" step="any" name="atm_machines[{{ $mIdx }}][cassettes][{{ $dIdx }}][total_nominal]" class="form-control form-control-sm text-end fw-bold kaset-total-nominal text-primary" placeholder="0" value="0" {{ $isAdmin ? 'readonly' : '' }}>
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
                                        <span class="fs-5 text-primary">🏦</span>
                                        <div class="input-group input-group-sm" style="width: 250px;">
                                            <span class="input-group-text fw-bold bg-white text-muted">ID ATM:</span>
                                            <input type="text" name="atm_machines[0][terminal_id]" class="form-control font-monospace fw-bold fs-6 atm-terminal-id text-primary" placeholder="00128" value="00128" {{ $isAdmin ? 'readonly' : '' }}>
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
                                            <input type="text" name="atm_machines[0][lokasi]" class="form-control form-control-sm" placeholder="Contoh: ATM Museum / Galeri KC" value="Galeri ATM KC" {{ $isAdmin ? 'readonly' : '' }}>
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
                                                <input type="number" step="any" name="atm_machines[0][saldo_cbs]" class="form-control form-control-sm text-end fw-bold atm-saldo-cbs" placeholder="0" value="0" {{ $isAdmin ? 'readonly' : '' }}>
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
                                                        <input type="number" step="any" name="atm_machines[0][cassettes][{{ $dIdx }}][total_nominal]" class="form-control form-control-sm text-end fw-bold kaset-total-nominal text-primary" placeholder="0" value="0" {{ $isAdmin ? 'readonly' : '' }}>
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

                    <!-- CARD EVALUASI & PENILAIAN RISIKO TUNGGAL (MESIN ATM) -->
                    <div class="card border-primary-subtle shadow-sm my-3 eval-card" id="evalCardAtm" style="border-radius: 10px; overflow: hidden;">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3" style="background-color: #1e3a8a !important;">
                            <span class="fw-bold"><i class="bi bi-shield-check me-2"></i>Ringkasan Evaluasi &amp; Penilaian Risiko Halaman</span>
                            <span class="badge bg-light text-primary fw-semibold">Evaluasi Keseluruhan Cash Opname Mesin ATM</span>
                        </div>
                        <div class="card-body p-3 bg-light-subtle">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-secondary">
                                        <i class="bi bi-calendar-event me-1"></i>Tanggal Uji (Tgl Uji) <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tgl_uji" class="form-control form-control-sm eval-tgl-uji" value="{{ $evalAtm['tgl_uji'] ?? date('Y-m-d') }}" {{ $isAdmin ? 'readonly' : '' }}>
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label fw-bold small text-secondary">
                                        <i class="bi bi-card-text me-1"></i>Penyebab &amp; Keterangan <span class="text-danger eval-req-indicator" style="display:none;">* (Wajib diisi jika terdapat selisih fisik vs saldo CBS)</span>
                                    </label>
                                    <textarea name="penyebab_keterangan" rows="2" class="form-control form-control-sm eval-penyebab @error('penyebab_keterangan') is-invalid @enderror" placeholder="Catat uraian penyebab selisih kas mesin ATM / keterangan keseluruhan pemeriksaan ATM..." {{ $isAdmin ? 'readonly' : '' }}>{{ old('penyebab_keterangan', $evalAtm['penyebab_keterangan'] ?? '') }}</textarea>
                                    <div class="invalid-feedback">{{ $errors->first('penyebab_keterangan') ?: 'Penyebab & keterangan wajib diisi karena terdapat selisih fisik kas kaset vs CBS.' }}</div>
                                </div>

                                <div class="col-12">
                                    <div class="p-2 border rounded bg-white shadow-xs">
                                        <div class="row g-2 align-items-center text-center">
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Impact (1-5)</label>
                                                <input type="hidden" name="impact" class="eval-impact" value="{{ $evalAtm['impact'] ?? 1 }}">
                                                <span class="badge bg-light text-dark border px-3 py-2 fs-6 eval-impact-badge">
                                                    {{ $evalAtm['impact'] ?? 1 }}
                                                </span>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Likelihood (1-5)</label>
                                                <input type="hidden" name="likelihood" class="eval-likelihood" value="{{ $evalAtm['likelihood'] ?? 1 }}">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <span class="badge bg-light text-dark border px-2 py-1 eval-likelihood-badge">
                                                            {{ $evalAtm['likelihood'] ?? 1 }}
                                                        </span>
                                                        <span class="badge bg-warning-subtle text-dark border eval-recurrence-badge" style="font-size: 0.72rem;">
                                                            {{ ($evalAtm['likelihood'] ?? 1) > 1 ? 'Temuan Berulang' : 'Temuan Baru' }}
                                                        </span>
                                                    </div>
                                                    <span class="small text-muted mt-1 eval-likelihood-note" style="font-size: 0.7rem;">
                                                        {{ ($evalAtm['likelihood'] ?? 1) > 1 ? 'Terdeteksi riwayat temuan di cabang' : 'Temuan pertama kali' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Critical</label>
                                                <input type="hidden" name="is_critical" class="eval-critical" value="{{ !empty($evalAtm['is_critical']) ? 1 : 0 }}">
                                                <span class="badge {{ !empty($evalAtm['is_critical']) ? 'bg-danger text-white' : 'bg-success text-white' }} px-3 py-2 fs-6 eval-critical-badge">
                                                    {{ !empty($evalAtm['is_critical']) ? 'Ya' : 'Tidak' }}
                                                </span>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Risk Score</label>
                                                <input type="text" name="risk_score" class="form-control form-control-sm text-center fw-bold eval-risk-score bg-light border-0" value="{{ $evalAtm['risk_score'] ?? 1 }}" readonly>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Risk Level</label>
                                                <div class="d-flex align-items-center justify-content-center gap-2">
                                                    <input type="hidden" name="risk_level" class="eval-risk-level-input" value="{{ $evalAtm['risk_level'] ?? 'Low' }}">
                                                    @php
                                                        $lvlBadgeAtm = match($evalAtm['risk_level'] ?? 'Low') {
                                                            'High' => 'bg-danger text-white',
                                                            'Moderate to High' => 'bg-danger-subtle text-danger-emphasis border border-danger',
                                                            'Moderate' => 'bg-warning text-dark',
                                                            'Low to Moderate' => 'bg-info text-dark',
                                                            default => 'bg-success text-white',
                                                        };
                                                    @endphp
                                                    <span class="badge px-3 py-2 fs-6 eval-risk-level-badge {{ $lvlBadgeAtm }}">
                                                        {{ $evalAtm['risk_level'] ?? 'Low' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Bar Tab 2: Simpan & Download -->
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if(isset($id))
                            <a href="{{ route('daily-register.physical-kka.download-pdf-atm', $id) }}" class="btn btn-sm btn-outline-danger fw-bold">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF ATM
                            </a>
                            <a href="{{ route('daily-register.physical-kka.export-excel-atm', $id) }}" class="btn btn-sm btn-outline-success fw-bold">
                                <i class="bi bi-file-earmark-excel me-1"></i> Download Excel ATM
                            </a>
                            <a href="{{ route('daily-register.physical-kka.print-ba-atm', $id) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold">
                                <i class="bi bi-printer me-1"></i> Cetak BA ATM
                            </a>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" style="background-color: var(--primary-blue); border-color: var(--primary-blue); border-radius: 8px;">
                            <i class="bi bi-save me-1"></i> {{ $isAdmin ? 'Simpan Review Admin' : 'Simpan Cash Opname Mesin ATM' }}
                        </button>
                    </div>
                </form>
            </div>

                <!-- ========================================================================= -->
                <!-- TAB 3: OBSERVASI LINGKUNGAN                                              -->
                <!-- ========================================================================= -->
                <div class="tab-pane fade p-3" id="obs" role="tabpanel">
                    <form method="POST" action="{{ $isAdmin ? route('daily-register.physical-kka.review') : route('daily-register.physical-kka.submit') }}" novalidate>
                        @csrf
                        @if(isset($id))
                            <input type="hidden" name="physical_kka_id" value="{{ $id }}">
                        @endif
                        <input type="hidden" name="sub_tab" value="observasi">
                        <input type="hidden" name="no_arsip" class="sync-no-arsip" value="{{ $noArsip }}">
                        <input type="hidden" name="posisi_tanggal_rekon" class="sync-posisi-tanggal" value="{{ $posisiTanggalRekon }}">

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
                                    <th style="width:30px;">No</th>
                                    <th style="min-width:180px;">Area Observasi</th>
                                    <th style="min-width:220px;">Kriteria yang Diharapkan</th>
                                    <th style="min-width:140px;" class="text-center">Backup Rekaman (E.1.5)</th>
                                    <th style="min-width:140px;">Hasil Uji</th>
                                    <th style="min-width:180px;">Kondisi Aktual</th>
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
                                        $groupName = '🚨 1. SPESIFIK 4 TITIK ALARM (PANIC BUTTON)';
                                    } elseif (str_contains($area, 'Area Terbatas')) {
                                        $groupName = '🔒 2. SPESIFIK 5 AREA TERBATAS (RESTRICTED ACCESS)';
                                    } elseif ($isCctv) {
                                        $groupName = '📹 3. MATRIKS 9 TITIK CCTV (ACUAN E.1.5 — WAJIB BACKUP MINIMAL 90 HARI)';
                                    } elseif (str_contains($area, 'Security') || str_contains($area, 'Kepegawaian')) {
                                        $groupName = '👮 4. MUTASI & BUKU MUTASI SECURITY SERTA ABSENSI KARYAWAN';
                                    } elseif (str_contains($area, 'Branding Interior')) {
                                        $groupName = '🏢 5. BRANDING INTERIOR KANTOR (STANDAR CORPORATE BANK SULTENG)';
                                    } elseif (str_contains($area, 'Fasilitas ATM')) {
                                        $groupName = '🏧 6. SECTION KHUSUS ATM (ACUAN STANDAR AUDIT E.4.4)';
                                    } elseif (str_contains($area, 'Keselamatan') || str_contains($area, 'Kelistrikan')) {
                                        $groupName = '🧯 7. KESELAMATAN KERJA & FASILITAS LISTRIK CADANGAN';
                                    }
                                @endphp

                                @if($groupName !== $lastGroup)
                                <tr class="table-light">
                                    <td colspan="{{ $isAdmin ? 9 : 6 }}" class="fw-bold text-primary py-2 px-3" style="background:#f1f5f9; border-top: 2px solid #cbd5e1; font-size: 0.82rem;">
                                        {{ $groupName }}
                                    </td>
                                </tr>
                                @php $lastGroup = $groupName; @endphp
                                @endif

                                <tr class="data-row" data-type="obs">
                                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
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
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <select name="obs_details[{{ $index }}][hasil_observasi]" class="form-select hasil-obs" {{ $isAdmin ? 'disabled' : '' }}>
                                            <option value="">Pilih Hasil</option>
                                            <option value="Sesuai" {{ ($item['hasil_observasi'] ?? '') === 'Sesuai' ? 'selected' : '' }}>Sesuai</option>
                                            <option value="Tidak Sesuai" {{ ($item['hasil_observasi'] ?? '') === 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                            <option value="Tidak Berlaku" {{ ($item['hasil_observasi'] ?? '') === 'Tidak Berlaku' ? 'selected' : '' }}>Tidak Berlaku</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="obs_details[{{ $index }}][kondisi_aktual]" class="form-control" placeholder="Observasi lapangan..." value="{{ $item['kondisi_aktual'] ?? '' }}" {{ $isAdmin ? 'readonly' : '' }}></td>
                                    
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

                    <!-- CARD EVALUASI & PENILAIAN RISIKO TUNGGAL (OBSERVASI LINGKUNGAN) -->
                    <div class="card border-primary-subtle shadow-sm my-3 eval-card" id="evalCardObs" style="border-radius: 10px; overflow: hidden;">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3" style="background-color: #1e3a8a !important;">
                            <span class="fw-bold"><i class="bi bi-shield-check me-2"></i>Ringkasan Evaluasi &amp; Penilaian Risiko Halaman</span>
                            <span class="badge bg-light text-primary fw-semibold">Evaluasi Keseluruhan Observasi Lingkungan</span>
                        </div>
                        <div class="card-body p-3 bg-light-subtle">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-secondary">
                                        <i class="bi bi-calendar-event me-1"></i>Tanggal Uji (Tgl Uji) <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tgl_uji" class="form-control form-control-sm eval-tgl-uji" value="{{ $evalObs['tgl_uji'] ?? date('Y-m-d') }}" {{ $isAdmin ? 'readonly' : '' }}>
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label fw-bold small text-secondary">
                                        <i class="bi bi-card-text me-1"></i>Penyebab &amp; Keterangan <span class="text-danger eval-req-indicator" style="display:none;">* (Wajib diisi jika terdapat temuan Tidak Sesuai)</span>
                                    </label>
                                    <textarea name="penyebab_keterangan" rows="2" class="form-control form-control-sm eval-penyebab @error('penyebab_keterangan') is-invalid @enderror" placeholder="Catat penjelasan penyebab / rincian temuan fisik / keterangan keseluruhan pemeriksaan observasi lingkungan..." {{ $isAdmin ? 'readonly' : '' }}>{{ old('penyebab_keterangan', $evalObs['penyebab_keterangan'] ?? '') }}</textarea>
                                    <div class="invalid-feedback">{{ $errors->first('penyebab_keterangan') ?: 'Penyebab & keterangan wajib diisi karena terdapat item yang dinilai Tidak Sesuai.' }}</div>
                                </div>

                                <div class="col-12">
                                    <div class="p-2 border rounded bg-white shadow-xs">
                                        <div class="row g-2 align-items-center text-center">
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Impact (1-5)</label>
                                                <input type="hidden" name="impact" class="eval-impact" value="{{ $evalObs['impact'] ?? 1 }}">
                                                <span class="badge bg-light text-dark border px-3 py-2 fs-6 eval-impact-badge">
                                                    {{ $evalObs['impact'] ?? 1 }}
                                                </span>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Likelihood (1-5)</label>
                                                <input type="hidden" name="likelihood" class="eval-likelihood" value="{{ $evalObs['likelihood'] ?? 1 }}">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <span class="badge bg-light text-dark border px-2 py-1 eval-likelihood-badge">
                                                            {{ $evalObs['likelihood'] ?? 1 }}
                                                        </span>
                                                        <span class="badge bg-warning-subtle text-dark border eval-recurrence-badge" style="font-size: 0.72rem;">
                                                            {{ ($evalObs['likelihood'] ?? 1) > 1 ? 'Temuan Berulang' : 'Temuan Baru' }}
                                                        </span>
                                                    </div>
                                                    <span class="small text-muted mt-1 eval-likelihood-note" style="font-size: 0.7rem;">
                                                        {{ ($evalObs['likelihood'] ?? 1) > 1 ? 'Terdeteksi riwayat temuan di cabang' : 'Temuan pertama kali' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Critical</label>
                                                <input type="hidden" name="is_critical" class="eval-critical" value="{{ !empty($evalObs['is_critical']) ? 1 : 0 }}">
                                                <span class="badge {{ !empty($evalObs['is_critical']) ? 'bg-danger text-white' : 'bg-success text-white' }} px-3 py-2 fs-6 eval-critical-badge">
                                                    {{ !empty($evalObs['is_critical']) ? 'Ya' : 'Tidak' }}
                                                </span>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Risk Score</label>
                                                <input type="text" name="risk_score" class="form-control form-control-sm text-center fw-bold eval-risk-score bg-light border-0" value="{{ $evalObs['risk_score'] ?? 1 }}" readonly>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Risk Level</label>
                                                <div class="d-flex align-items-center justify-content-center gap-2">
                                                    <input type="hidden" name="risk_level" class="eval-risk-level-input" value="{{ $evalObs['risk_level'] ?? 'Low' }}">
                                                    @php
                                                        $lvlBadgeObs = match($evalObs['risk_level'] ?? 'Low') {
                                                            'High' => 'bg-danger text-white',
                                                            'Moderate to High' => 'bg-danger-subtle text-danger-emphasis border border-danger',
                                                            'Moderate' => 'bg-warning text-dark',
                                                            'Low to Moderate' => 'bg-info text-dark',
                                                            default => 'bg-success text-white',
                                                        };
                                                    @endphp
                                                    <span class="badge px-3 py-2 fs-6 eval-risk-level-badge {{ $lvlBadgeObs }}">
                                                        {{ $evalObs['risk_level'] ?? 'Low' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light border-top d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 pt-3">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            @if(isset($id))
                            <a href="{{ route('daily-register.physical-kka.download-pdf-observasi', $id) }}" class="btn btn-sm btn-outline-danger fw-bold">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF Observasi
                            </a>
                            <a href="{{ route('daily-register.physical-kka.export-excel-observasi', $id) }}" class="btn btn-sm btn-outline-success fw-bold">
                                <i class="bi bi-file-earmark-excel me-1"></i> Download Excel Observasi
                            </a>
                            <a href="{{ route('daily-register.physical-kka.print-kka-observasi', $id) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold">
                                <i class="bi bi-printer me-1"></i> Cetak KKA Observasi
                            </a>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" style="background-color: var(--primary-blue); border-color: var(--primary-blue); border-radius: 8px;">
                            <i class="bi bi-save me-1"></i> {{ $isAdmin ? 'Simpan Review Admin' : 'Simpan Observasi Lingkungan' }}
                        </button>
                    </div>
                </form>
            </div>

                <!-- ========================================================================= -->
                <!-- TAB 4: REKON STOK CEK / BILYET GIRO                                      -->
                <!-- ========================================================================= -->
                <div class="tab-pane fade p-3" id="stok" role="tabpanel">
                    <form method="POST" action="{{ $isAdmin ? route('daily-register.physical-kka.review') : route('daily-register.physical-kka.submit') }}" novalidate>
                        @csrf
                        @if(isset($id))
                            <input type="hidden" name="physical_kka_id" value="{{ $id }}">
                        @endif
                        <input type="hidden" name="sub_tab" value="stok">
                        <input type="hidden" name="no_arsip" class="sync-no-arsip" value="{{ $noArsip }}">
                        <input type="hidden" name="posisi_tanggal_rekon" class="sync-posisi-tanggal" value="{{ $posisiTanggalRekon }}">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-1"><i class="bi bi-journal-check me-2 text-primary"></i>KKA_Rekon_Stok_Cek_BG (Pemeriksaan Warkat &amp; Dokumen Berharga)</h6>
                                <small class="text-muted">Pemeriksaan fisik stok buku cek, bilyet giro, dan sertifikat bilyet deposito dalam brankas.</small>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-excel align-middle">
                                <thead>
                                    <tr>
                                        <th style="width:35px;" class="text-center">No</th>
                                        <th style="min-width:110px;">Sub Area</th>
                                        <th style="min-width:220px;">Kriteria / Jenis Warkat</th>
                                        <th style="min-width:180px;">Keterangan / Kondisi</th>
                                        <th style="min-width:110px;" class="text-end">Saldo Buku</th>
                                        <th style="min-width:120px;" class="text-end">Jumlah Fisik</th>
                                        <th style="min-width:110px;" class="text-end">Selisih</th>
                                        <th style="min-width:120px;" class="text-center">Status Cek</th>
                                        @if($isAdmin)
                                        <th class="bg-warning text-dark">Reviewer</th>
                                        <th class="bg-warning text-dark" style="min-width:150px;">Status Review</th>
                                        <th class="bg-warning text-dark" style="min-width:200px;">Catatan Admin</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stokItems as $index => $item)
                                    @php
                                        $isCtrl = ($item['sub_area'] === 'Kontrol');
                                        $saldoSys = (float)($item['saldo_sistem'] ?? 100);
                                        $hasilFis = $item['hasil_fisik'] ?? '';
                                        $selisihVal = ($hasilFis !== '' && !$isCtrl) ? ($saldoSys - (float)$hasilFis) : 0;
                                    @endphp
                                    <tr class="data-row stok-calc-row" data-type="stok" data-is-ctrl="{{ $isCtrl ? '1' : '0' }}">
                                        <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                        <td>
                                            <span class="badge {{ $isCtrl ? 'bg-primary' : 'bg-secondary' }}">{{ $item['sub_area'] }}</span>
                                            <input type="hidden" name="stok_details[{{ $index }}][sub_area]" value="{{ $item['sub_area'] }}">
                                        </td>
                                        <td>
                                            <strong>{{ $item['objek'] }}</strong>
                                            <input type="hidden" name="stok_details[{{ $index }}][kondisi_objek]" value="{{ $item['objek'] }} | {{ $item['pecahan'] }}">
                                        </td>
                                        <td><input type="text" name="stok_details[{{ $index }}][kondisi_aktual]" class="form-control form-control-sm" value="{{ $item['pecahan'] ?? '' }}" {{ $isAdmin ? 'readonly' : '' }}></td>
                                        <td>
                                            @if($isCtrl)
                                                <input type="hidden" name="stok_details[{{ $index }}][saldo_sistem]" value="0">
                                                <div class="text-center text-muted small">—</div>
                                            @else
                                                <input type="number" name="stok_details[{{ $index }}][saldo_sistem]" class="form-control form-control-sm saldo-sistem border-0 bg-transparent text-end" value="{{ $item['saldo_sistem'] ?? 100 }}" readonly>
                                            @endif
                                        </td>
                                        <td>
                                            @if($isCtrl)
                                                <div class="d-flex justify-content-center gap-2">
                                                    <div class="form-check form-check-inline m-0">
                                                        <input class="form-check-input stok-ctrl-radio" type="radio" name="stok_details[{{ $index }}][status_uji]" id="stokCtrlYa_{{ $index }}" value="Ya" {{ ($item['status_uji'] ?? 'Ya') === 'Ya' ? 'checked' : '' }} {{ $isAdmin ? 'disabled' : '' }}>
                                                        <label class="form-check-label small fw-bold text-success" for="stokCtrlYa_{{ $index }}">Ya</label>
                                                    </div>
                                                    <div class="form-check form-check-inline m-0">
                                                        <input class="form-check-input stok-ctrl-radio" type="radio" name="stok_details[{{ $index }}][status_uji]" id="stokCtrlTidak_{{ $index }}" value="Tidak" {{ ($item['status_uji'] ?? '') === 'Tidak' ? 'checked' : '' }} {{ $isAdmin ? 'disabled' : '' }}>
                                                        <label class="form-check-label small fw-bold text-danger" for="stokCtrlTidak_{{ $index }}">Tidak</label>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="stok_details[{{ $index }}][hasil_fisik]" value="0">
                                            @else
                                                <input type="number" name="stok_details[{{ $index }}][hasil_fisik]" class="form-control form-control-sm hasil-fisik text-end stok-fisik-input" placeholder="0" value="{{ $item['hasil_fisik'] ?? '' }}" {{ $isAdmin ? 'readonly' : '' }}>
                                            @endif
                                        </td>
                                        <td>
                                            @if($isCtrl)
                                                <div class="text-center text-muted small">—</div>
                                            @else
                                                <input type="text" class="form-control form-control-sm selisih-display border-0 bg-transparent text-end fw-bold stok-selisih-display {{ abs($selisihVal) >= 0.01 ? 'text-danger' : 'text-success' }}" readonly value="{{ $hasilFis !== '' ? $selisihVal : '' }}">
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($isCtrl)
                                                <span class="badge-soft stok-status-badge {{ ($item['status_uji'] ?? '') === 'Tidak' ? 'badge-soft-danger' : 'badge-soft-success' }}">
                                                    {{ ($item['status_uji'] ?? 'Ya') === 'Tidak' ? 'Tidak' : 'Ya' }}
                                                </span>
                                            @else
                                                <input type="hidden" name="stok_details[{{ $index }}][status_uji]" class="stok-status-input" value="{{ $item['status_uji'] ?? 'Sesuai' }}">
                                                <span class="badge-soft stok-status-badge {{ ($item['status_uji'] ?? '') === 'Exception' || abs($selisihVal) >= 0.01 ? 'badge-soft-danger' : 'badge-soft-success' }}">
                                                    {{ abs($selisihVal) >= 0.01 ? 'Selisih' : ($item['status_uji'] ?? 'Sesuai') }}
                                                </span>
                                            @endif
                                        </td>
                                        
                                        @if($isAdmin)
                                        @php $fakeId = $index + 1; @endphp
                                        <td><input type="text" class="form-control form-control-sm bg-light" value="{{ auth()->user()->name ?? 'Admin' }}" readonly></td>
                                        <td>
                                            <select name="stok_review[{{ $fakeId }}][status_review]" class="form-select form-select-sm border-warning">
                                                <option value="Pending" {{ ($item['status_review'] ?? '') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="Approved" {{ ($item['status_review'] ?? '') === 'Approved' ? 'selected' : '' }}>Approved</option>
                                                <option value="Approved - Fraud" {{ ($item['status_review'] ?? '') === 'Approved - Fraud' ? 'selected' : '' }}>Approved - Terindikasi Fraud (SOP 09)</option>
                                                <option value="Revisi" {{ ($item['status_review'] ?? '') === 'Revisi' ? 'selected' : '' }}>Revisi</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="stok_review[{{ $fakeId }}][catatan_reviewer]" class="form-control form-control-sm border-warning" placeholder="Ketik catatan review..." value="{{ $item['catatan_reviewer'] ?? '' }}"></td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- CARD EVALUASI & PENILAIAN RISIKO TUNGGAL (REKON STOK CEK / BG) -->
                        <div class="card border-primary-subtle shadow-sm my-3 eval-card" id="evalCardStok" style="border-radius: 10px; overflow: hidden;">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3" style="background-color: #1e3a8a !important;">
                                <span class="fw-bold"><i class="bi bi-shield-check me-2"></i>Ringkasan Evaluasi &amp; Penilaian Risiko Halaman</span>
                                <span class="badge bg-light text-primary fw-semibold">Evaluasi Keseluruhan Stok Cek &amp; Bilyet Giro</span>
                            </div>
                            <div class="card-body p-3 bg-light-subtle">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-secondary">
                                            <i class="bi bi-calendar-event me-1"></i>Tanggal Uji (Tgl Uji) <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="tgl_uji" class="form-control form-control-sm eval-tgl-uji" value="{{ $evalStok['tgl_uji'] ?? date('Y-m-d') }}" {{ $isAdmin ? 'readonly' : '' }}>
                                    </div>
                                    <div class="col-md-9">
                                        <label class="form-label fw-bold small text-secondary">
                                            <i class="bi bi-card-text me-1"></i>Penyebab &amp; Keterangan <span class="text-danger eval-req-indicator" style="display:none;">* (Wajib diisi jika terdapat selisih atau kontrol negatif)</span>
                                        </label>
                                        <textarea name="penyebab_keterangan" rows="2" class="form-control form-control-sm eval-penyebab @error('penyebab_keterangan') is-invalid @enderror" placeholder="Catat penjelasan penyebab selisih / rincian temuan fisik / keterangan keseluruhan pemeriksaan warkat cek &amp; bilyet giro..." {{ $isAdmin ? 'readonly' : '' }}>{{ old('penyebab_keterangan', $evalStok['penyebab_keterangan'] ?? '') }}</textarea>
                                        <div class="invalid-feedback">{{ $errors->first('penyebab_keterangan') ?: 'Penyebab & keterangan wajib diisi karena terdapat selisih atau kontrol yang dijawab Tidak.' }}</div>
                                    </div>

                                    <div class="col-12">
                                        <div class="p-2 border rounded bg-white shadow-xs">
                                            <div class="row g-2 align-items-center text-center">
                                                <div class="col-6 col-md-2">
                                                    <label class="form-label small fw-bold text-muted mb-1 d-block">Impact (1-5)</label>
                                                    <input type="hidden" name="impact" class="eval-impact" value="{{ $evalStok['impact'] ?? 1 }}">
                                                    <span class="badge bg-light text-dark border px-3 py-2 fs-6 eval-impact-badge">
                                                        {{ $evalStok['impact'] ?? 1 }}
                                                    </span>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label class="form-label small fw-bold text-muted mb-1 d-block">Likelihood (1-5)</label>
                                                    <input type="hidden" name="likelihood" class="eval-likelihood" value="{{ $evalStok['likelihood'] ?? 1 }}">
                                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                                        <div class="d-flex align-items-center gap-1">
                                                            <span class="badge bg-light text-dark border px-2 py-1 eval-likelihood-badge">
                                                                {{ $evalStok['likelihood'] ?? 1 }}
                                                            </span>
                                                            <span class="badge bg-warning-subtle text-dark border eval-recurrence-badge" style="font-size: 0.72rem;">
                                                                {{ ($evalStok['likelihood'] ?? 1) > 1 ? 'Temuan Berulang' : 'Temuan Baru' }}
                                                            </span>
                                                        </div>
                                                        <span class="small text-muted mt-1 eval-likelihood-note" style="font-size: 0.7rem;">
                                                            {{ ($evalStok['likelihood'] ?? 1) > 1 ? 'Terdeteksi riwayat temuan di cabang' : 'Temuan pertama kali' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-2">
                                                    <label class="form-label small fw-bold text-muted mb-1 d-block">Critical</label>
                                                    <input type="hidden" name="is_critical" class="eval-critical" value="{{ !empty($evalStok['is_critical']) ? 1 : 0 }}">
                                                    <span class="badge {{ !empty($evalStok['is_critical']) ? 'bg-danger text-white' : 'bg-success text-white' }} px-3 py-2 fs-6 eval-critical-badge">
                                                        {{ !empty($evalStok['is_critical']) ? 'Ya' : 'Tidak' }}
                                                    </span>
                                                </div>
                                                <div class="col-6 col-md-2">
                                                    <label class="form-label small fw-bold text-muted mb-1 d-block">Risk Score</label>
                                                    <input type="text" name="risk_score" class="form-control form-control-sm text-center fw-bold eval-risk-score bg-light border-0" value="{{ $evalStok['risk_score'] ?? 1 }}" readonly>
                                                </div>
                                                <div class="col-12 col-md-3">
                                                    <label class="form-label small fw-bold text-muted mb-1 d-block">Risk Level</label>
                                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                                        <input type="hidden" name="risk_level" class="eval-risk-level-input" value="{{ $evalStok['risk_level'] ?? 'Low' }}">
                                                        @php
                                                            $lvlBadgeStok = match($evalStok['risk_level'] ?? 'Low') {
                                                                'High' => 'bg-danger text-white',
                                                                'Moderate to High' => 'bg-danger-subtle text-danger-emphasis border border-danger',
                                                                'Moderate' => 'bg-warning text-dark',
                                                                'Low to Moderate' => 'bg-info text-dark',
                                                                default => 'bg-success text-white',
                                                            };
                                                        @endphp
                                                        <span class="badge px-3 py-2 fs-6 eval-risk-level-badge {{ $lvlBadgeStok }}">
                                                            {{ $evalStok['risk_level'] ?? 'Low' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light border-top d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 pt-3">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                @if(isset($id))
                                <a href="{{ route('daily-register.physical-kka.download-pdf-stok', $id) }}" class="btn btn-sm btn-outline-danger fw-bold">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF Stok Cek/BG
                                </a>
                                <a href="{{ route('daily-register.physical-kka.export-excel-stok', $id) }}" class="btn btn-sm btn-outline-success fw-bold">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Download Excel Stok Cek/BG
                                </a>
                                <a href="{{ route('daily-register.physical-kka.print-kka-stok', $id) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold">
                                    <i class="bi bi-printer me-1"></i> Cetak KKA Stok Cek/BG
                                </a>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" style="background-color: var(--primary-blue); border-color: var(--primary-blue); border-radius: 8px;">
                                <i class="bi bi-save me-1"></i> {{ $isAdmin ? 'Simpan Review Admin' : 'Simpan Rekon Stok Cek/BG' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ========================================================================= -->
                <!-- TAB 5: REKONSILIASI KARTU ATM (FORMAT TEGAS SECTION A & SECTION B ΓÇö E.4.3)-->
                <!-- ========================================================================= -->
                <div class="tab-pane fade p-3" id="atm" role="tabpanel">
                    <form method="POST" action="{{ $isAdmin ? route('daily-register.physical-kka.review') : route('daily-register.physical-kka.submit') }}" novalidate>
                        @csrf
                        @if(isset($id))
                            <input type="hidden" name="physical_kka_id" value="{{ $id }}">
                        @endif
                        <input type="hidden" name="sub_tab" value="kartu">
                        <input type="hidden" name="no_arsip" class="sync-no-arsip" value="{{ $noArsip }}">
                        <input type="hidden" name="posisi_tanggal_rekon" class="sync-posisi-tanggal" value="{{ $posisiTanggalRekon }}">

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
                                                <input type="text" name="kartu_atm_stock[{{ $sIdx }}][jenis_kartu]" class="form-control form-control-sm fw-bold atm-jenis-name" value="{{ $row['jenis_kartu'] }}" {{ $isAdmin ? 'readonly' : '' }}>
                                                <input type="hidden" name="kartu_atm_stock[{{ $sIdx }}][keterangan_lokasi]" value="{{ $row['keterangan_lokasi'] ?? 'Brankas Khasanah' }}">
                                            </td>
                                            <td>
                                                <input type="number" min="0" step="1" name="kartu_atm_stock[{{ $sIdx }}][qty_fisik_kosong]" class="form-control form-control-sm text-end atm-qty-kosong" placeholder="0" value="{{ $row['qty_fisik_kosong'] ?? 0 }}" {{ $isAdmin ? 'readonly' : '' }}>
                                            </td>
                                            <td>
                                                <input type="number" min="0" step="1" name="kartu_atm_stock[{{ $sIdx }}][qty_fisik_rusak]" class="form-control form-control-sm text-end atm-qty-rusak" placeholder="0" value="{{ $row['qty_fisik_rusak'] ?? 0 }}" {{ $isAdmin ? 'readonly' : '' }}>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-end fw-bold atm-total-fisik-display border-0 bg-transparent" readonly value="{{ (int)($row['qty_fisik_kosong'] ?? 0) + (int)($row['qty_fisik_rusak'] ?? 0) }}">
                                            </td>
                                            <td>
                                                <input type="number" step="any" name="kartu_atm_stock[{{ $sIdx }}][harga_modal]" class="form-control form-control-sm text-end atm-harga-modal" value="{{ $row['harga_modal'] ?? ($row['jenis_kartu'] === 'Kartu Silver' ? 42180 : 68820) }}" {{ $isAdmin ? 'readonly' : '' }}>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-end fw-bold atm-nominal-fisik-display border-0 bg-transparent text-primary" readonly value="Rp {{ number_format($row['nominal_fisik'] ?? 0, 0, ',', '.') }}">
                                            </td>
                                            <td>
                                                <input type="number" min="0" step="1" name="kartu_atm_stock[{{ $sIdx }}][saldo_buku_qty]" class="form-control form-control-sm text-end atm-buku-qty" placeholder="0" value="{{ $row['saldo_buku_qty'] ?? 0 }}" {{ $isAdmin ? 'readonly' : '' }}>
                                            </td>
                                            <td>
                                                <input type="number" step="any" name="kartu_atm_stock[{{ $sIdx }}][saldo_buku_nominal]" class="form-control form-control-sm text-end atm-buku-nom" placeholder="0" value="{{ $row['saldo_buku_nominal'] ?? 0 }}" {{ $isAdmin ? 'readonly' : '' }}>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-end fw-bold atm-selisih-persediaan-display border-0 bg-transparent" readonly value="0">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-end atm-kartu-count-display border-0 bg-transparent" readonly value="{{ $row['qty_fisik_kosong'] ?? 0 }}">
                                            </td>
                                            <td>
                                                <input type="number" min="0" step="1" name="kartu_atm_stock[{{ $sIdx }}][pin_mailer_qty]" class="form-control form-control-sm text-end atm-pin-qty" placeholder="0" value="{{ $row['pin_mailer_qty'] ?? ($row['qty_fisik_kosong'] ?? 0) }}" {{ $isAdmin ? 'readonly' : '' }}>
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
                                    <input type="text" name="rekon_kartu_penyebab_selisih" id="inputAtmFindings" class="form-control form-control-sm" value="{{ $rekonKartuPenyebabSelisih }}" placeholder="Tidak terdapat selisih..." {{ $isAdmin ? 'readonly' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION B: KONTROL PENGELOLAAN KARTU ATM (CHECKLIST YA / TIDAK) -->
                    <div class="section-card">
                        <div class="section-card-header">
                            <div>
                                <i class="bi bi-shield-check me-1"></i>SECTION B — KONTROL PENGELOLAAN KARTU ATM (CHECKLIST MURNI YA / TIDAK)
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

                    <!-- CARD EVALUASI & PENILAIAN RISIKO TUNGGAL (REKON KARTU ATM) -->
                    <div class="card border-primary-subtle shadow-sm my-3 eval-card" id="evalCardKartu" style="border-radius: 10px; overflow: hidden;">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3" style="background-color: #1e3a8a !important;">
                            <span class="fw-bold"><i class="bi bi-shield-check me-2"></i>Ringkasan Evaluasi &amp; Penilaian Risiko Halaman</span>
                            <span class="badge bg-light text-primary fw-semibold">Evaluasi Keseluruhan Rekonsiliasi Kartu ATM</span>
                        </div>
                        <div class="card-body p-3 bg-light-subtle">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-secondary">
                                        <i class="bi bi-calendar-event me-1"></i>Tanggal Uji (Tgl Uji) <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tgl_uji" class="form-control form-control-sm eval-tgl-uji" value="{{ $evalKartu['tgl_uji'] ?? date('Y-m-d') }}" {{ $isAdmin ? 'readonly' : '' }}>
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label fw-bold small text-secondary">
                                        <i class="bi bi-card-text me-1"></i>Penyebab &amp; Keterangan <span class="text-danger eval-req-indicator" style="display:none;">* (Wajib diisi jika terdapat selisih atau kontrol bernilai Tidak)</span>
                                    </label>
                                    <textarea name="penyebab_keterangan" rows="2" class="form-control form-control-sm eval-penyebab @error('penyebab_keterangan') is-invalid @enderror" placeholder="Catat uraian penyebab selisih fisik / saldo buku persediaan kartu ATM / keterangan pengujian..." {{ $isAdmin ? 'readonly' : '' }}>{{ old('penyebab_keterangan', $evalKartu['penyebab_keterangan'] ?? '') }}</textarea>
                                    <div class="invalid-feedback">{{ $errors->first('penyebab_keterangan') ?: 'Penyebab & keterangan wajib diisi karena terdapat selisih persediaan atau butir kontrol dijawab Tidak.' }}</div>
                                </div>

                                <div class="col-12">
                                    <div class="p-2 border rounded bg-white shadow-xs">
                                        <div class="row g-2 align-items-center text-center">
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Impact (1-5)</label>
                                                <input type="hidden" name="impact" class="eval-impact" value="{{ $evalKartu['impact'] ?? 1 }}">
                                                <span class="badge bg-light text-dark border px-3 py-2 fs-6 eval-impact-badge">
                                                    {{ $evalKartu['impact'] ?? 1 }}
                                                </span>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Likelihood (1-5)</label>
                                                <input type="hidden" name="likelihood" class="eval-likelihood" value="{{ $evalKartu['likelihood'] ?? 1 }}">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <span class="badge bg-light text-dark border px-2 py-1 eval-likelihood-badge">
                                                            {{ $evalKartu['likelihood'] ?? 1 }}
                                                        </span>
                                                        <span class="badge bg-warning-subtle text-dark border eval-recurrence-badge" style="font-size: 0.72rem;">
                                                            {{ ($evalKartu['likelihood'] ?? 1) > 1 ? 'Temuan Berulang' : 'Temuan Baru' }}
                                                        </span>
                                                    </div>
                                                    <span class="small text-muted mt-1 eval-likelihood-note" style="font-size: 0.7rem;">
                                                        {{ ($evalKartu['likelihood'] ?? 1) > 1 ? 'Terdeteksi riwayat temuan di cabang' : 'Temuan pertama kali' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Critical</label>
                                                <input type="hidden" name="is_critical" class="eval-critical" value="{{ !empty($evalKartu['is_critical']) ? 1 : 0 }}">
                                                <span class="badge {{ !empty($evalKartu['is_critical']) ? 'bg-danger text-white' : 'bg-success text-white' }} px-3 py-2 fs-6 eval-critical-badge">
                                                    {{ !empty($evalKartu['is_critical']) ? 'Ya' : 'Tidak' }}
                                                </span>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Risk Score</label>
                                                <input type="text" name="risk_score" class="form-control form-control-sm text-center fw-bold eval-risk-score bg-light border-0" value="{{ $evalKartu['risk_score'] ?? 1 }}" readonly>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Risk Level</label>
                                                <div class="d-flex align-items-center justify-content-center gap-2">
                                                    <input type="hidden" name="risk_level" class="eval-risk-level-input" value="{{ $evalKartu['risk_level'] ?? 'Low' }}">
                                                    @php
                                                        $lvlBadgeKartu = match($evalKartu['risk_level'] ?? 'Low') {
                                                            'High' => 'bg-danger text-white',
                                                            'Moderate to High' => 'bg-danger-subtle text-danger-emphasis border border-danger',
                                                            'Moderate' => 'bg-warning text-dark',
                                                            'Low to Moderate' => 'bg-info text-dark',
                                                            default => 'bg-success text-white',
                                                        };
                                                    @endphp
                                                    <span class="badge px-3 py-2 fs-6 eval-risk-level-badge {{ $lvlBadgeKartu }}">
                                                        {{ $evalKartu['risk_level'] ?? 'Low' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light border-top d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 pt-3">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            @if(isset($id))
                            <a href="{{ route('daily-register.physical-kka.download-pdf', ['id' => $id, 'type' => 'kartu']) }}" class="btn btn-sm btn-outline-danger fw-bold">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF Kartu
                            </a>
                            <a href="{{ route('daily-register.physical-kka.export-excel', ['id' => $id, 'type' => 'kartu']) }}" class="btn btn-sm btn-outline-success fw-bold">
                                <i class="bi bi-file-earmark-excel me-1"></i> Download Excel Kartu
                            </a>
                            <a href="{{ route('daily-register.physical-kka.print-kka-kartu', $id) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold">
                                <i class="bi bi-printer me-1"></i> Cetak KKA Kartu ATM
                            </a>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" style="background-color: var(--primary-blue); border-color: var(--primary-blue); border-radius: 8px;">
                            <i class="bi bi-save me-1"></i> {{ $isAdmin ? 'Simpan Review Admin' : 'Simpan Rekon Kartu ATM' }}
                        </button>
                    </div>
                </form>
            </div>

                <!-- ========================================================================= -->
                <!-- TAB 6: REKONSILIASI BARANG CETAKAN (MERGED KRITERIA & CHECKLIST KONTROL) -->
                <!-- ========================================================================= -->
                <div class="tab-pane fade p-3" id="cetakan" role="tabpanel">
                    <form method="POST" action="{{ $isAdmin ? route('daily-register.physical-kka.review') : route('daily-register.physical-kka.submit') }}" novalidate>
                        @csrf
                        @if(isset($id))
                            <input type="hidden" name="physical_kka_id" value="{{ $id }}">
                        @endif
                        <input type="hidden" name="sub_tab" value="cetakan">
                        <input type="hidden" name="no_arsip" class="sync-no-arsip" value="{{ $noArsip }}">
                        <input type="hidden" name="posisi_tanggal_rekon" class="sync-posisi-tanggal" value="{{ $posisiTanggalRekon }}">

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
                                    <th style="width: 14%;">Kategori</th>
                                    <th style="width: 22%;">Objek / Nama Barang</th>
                                    <th style="width: 26%;">Kriteria Pengujian (Objek &amp; Kondisi)</th>
                                    <th style="width: 6%;">Satuan</th>
                                    <th style="width: 10%;">Saldo Buku</th>
                                    <th style="width: 10%;">Jumlah Fisik</th>
                                    <th style="width: 9%;">Selisih</th>
                                    <th style="width: 6%;">Status</th>
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
                                        <input type="text" name="barang_cetakan_details[{{ $bIdx }}][kriteria]" class="form-control form-control-sm bct-kriteria-input" value="{{ $bItem['kriteria'] }}" {{ $isAdmin ? 'readonly' : '' }}>
                                    </td>
                                    <td>
                                        <input type="text" name="barang_cetakan_details[{{ $bIdx }}][satuan]" class="form-control form-control-sm text-center bg-light" value="{{ $bItem['satuan'] }}" readonly>
                                    </td>
                                    <td>
                                        <input type="number" step="any" name="barang_cetakan_details[{{ $bIdx }}][stok_sistem]" class="form-control form-control-sm text-end bct-stok-buku" placeholder="0" value="{{ $bItem['stok_sistem'] ?? 0 }}" {{ $isAdmin ? 'readonly' : '' }}>
                                    </td>
                                    <td>
                                        <input type="number" step="any" name="barang_cetakan_details[{{ $bIdx }}][hasil_fisik]" class="form-control form-control-sm text-end bct-hasil-fisik" placeholder="0" value="{{ $bItem['hasil_fisik'] ?? '' }}" {{ $isAdmin ? 'readonly' : '' }}>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm text-end fw-bold bct-selisih-display border-0 bg-transparent" readonly value="{{ $bItem['selisih'] ?? 0 }}">
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-soft bct-status-badge {{ ($bItem['status_uji'] ?? '') === 'EXCEPTION' ? 'badge-soft-danger' : 'badge-soft-success' }}">
                                            {{ $bItem['status_uji'] ?? 'Sesuai' }}
                                        </span>
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

                    <!-- CARD EVALUASI & PENILAIAN RISIKO TUNGGAL (BARANG CETAKAN) -->
                    <div class="card border-primary-subtle shadow-sm my-3 eval-card" id="evalCardCetakan" style="border-radius: 10px; overflow: hidden;">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3" style="background-color: #1e3a8a !important;">
                            <span class="fw-bold"><i class="bi bi-shield-check me-2"></i>Ringkasan Evaluasi &amp; Penilaian Risiko Halaman</span>
                            <span class="badge bg-light text-primary fw-semibold">Evaluasi Keseluruhan Barang Cetakan Berharga</span>
                        </div>
                        <div class="card-body p-3 bg-light-subtle">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-secondary">
                                        <i class="bi bi-calendar-event me-1"></i>Tanggal Uji (Tgl Uji) <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tgl_uji" class="form-control form-control-sm eval-tgl-uji" value="{{ $evalCetakan['tgl_uji'] ?? date('Y-m-d') }}" {{ $isAdmin ? 'readonly' : '' }}>
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label fw-bold small text-secondary">
                                        <i class="bi bi-card-text me-1"></i>Penyebab &amp; Keterangan <span class="text-danger eval-req-indicator" style="display:none;">* (Wajib diisi jika terdapat selisih atau kontrol negatif)</span>
                                    </label>
                                    <textarea name="penyebab_keterangan" rows="2" class="form-control form-control-sm eval-penyebab @error('penyebab_keterangan') is-invalid @enderror" placeholder="Catat penjelasan penyebab selisih / rincian temuan fisik / keterangan keseluruhan pemeriksaan barang cetakan..." {{ $isAdmin ? 'readonly' : '' }}>{{ old('penyebab_keterangan', $evalCetakan['penyebab_keterangan'] ?? '') }}</textarea>
                                    <div class="invalid-feedback">{{ $errors->first('penyebab_keterangan') ?: 'Penyebab & keterangan wajib diisi karena terdapat selisih fisik atau butir kontrol bernilai Tidak.' }}</div>
                                </div>

                                <div class="col-12">
                                    <div class="p-2 border rounded bg-white shadow-xs">
                                        <div class="row g-2 align-items-center text-center">
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Impact (1-5)</label>
                                                <input type="hidden" name="impact" class="eval-impact" value="{{ $evalCetakan['impact'] ?? 1 }}">
                                                <span class="badge bg-light text-dark border px-3 py-2 fs-6 eval-impact-badge">
                                                    {{ $evalCetakan['impact'] ?? 1 }}
                                                </span>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Likelihood (1-5)</label>
                                                <input type="hidden" name="likelihood" class="eval-likelihood" value="{{ $evalCetakan['likelihood'] ?? 1 }}">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <span class="badge bg-light text-dark border px-2 py-1 eval-likelihood-badge">
                                                            {{ $evalCetakan['likelihood'] ?? 1 }}
                                                        </span>
                                                        <span class="badge bg-warning-subtle text-dark border eval-recurrence-badge" style="font-size: 0.72rem;">
                                                            {{ ($evalCetakan['likelihood'] ?? 1) > 1 ? 'Temuan Berulang' : 'Temuan Baru' }}
                                                        </span>
                                                    </div>
                                                    <span class="small text-muted mt-1 eval-likelihood-note" style="font-size: 0.7rem;">
                                                        {{ ($evalCetakan['likelihood'] ?? 1) > 1 ? 'Terdeteksi riwayat temuan di cabang' : 'Temuan pertama kali' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Critical</label>
                                                <input type="hidden" name="is_critical" class="eval-critical" value="{{ !empty($evalCetakan['is_critical']) ? 1 : 0 }}">
                                                <span class="badge {{ !empty($evalCetakan['is_critical']) ? 'bg-danger text-white' : 'bg-success text-white' }} px-3 py-2 fs-6 eval-critical-badge">
                                                    {{ !empty($evalCetakan['is_critical']) ? 'Ya' : 'Tidak' }}
                                                </span>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Risk Score</label>
                                                <input type="text" name="risk_score" class="form-control form-control-sm text-center fw-bold eval-risk-score bg-light border-0" value="{{ $evalCetakan['risk_score'] ?? 1 }}" readonly>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label small fw-bold text-muted mb-1 d-block">Risk Level</label>
                                                <div class="d-flex align-items-center justify-content-center gap-2">
                                                    <input type="hidden" name="risk_level" class="eval-risk-level-input" value="{{ $evalCetakan['risk_level'] ?? 'Low' }}">
                                                    @php
                                                        $lvlBadgeCetakan = match($evalCetakan['risk_level'] ?? 'Low') {
                                                            'High' => 'bg-danger text-white',
                                                            'Moderate to High' => 'bg-danger-subtle text-danger-emphasis border border-danger',
                                                            'Moderate' => 'bg-warning text-dark',
                                                            'Low to Moderate' => 'bg-info text-dark',
                                                            default => 'bg-success text-white',
                                                        };
                                                    @endphp
                                                    <span class="badge px-3 py-2 fs-6 eval-risk-level-badge {{ $lvlBadgeCetakan }}">
                                                        {{ $evalCetakan['risk_level'] ?? 'Low' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light border-top d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 pt-3">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            @if(isset($id))
                            <a href="{{ route('daily-register.physical-kka.download-pdf-cetakan', $id) }}" class="btn btn-sm btn-outline-danger fw-bold">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF Cetakan
                            </a>
                            <a href="{{ route('daily-register.physical-kka.export-excel-cetakan', $id) }}" class="btn btn-sm btn-outline-success fw-bold">
                                <i class="bi bi-file-earmark-excel me-1"></i> Download Excel Cetakan
                            </a>
                            <a href="{{ route('daily-register.physical-kka.print-kka-cetakan', $id) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold">
                                <i class="bi bi-printer me-1"></i> Cetak KKA Cetakan
                            </a>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" style="background-color: var(--primary-blue); border-color: var(--primary-blue); border-radius: 8px;">
                            <i class="bi bi-save me-1"></i> {{ $isAdmin ? 'Simpan Review Admin' : 'Simpan Rekon Barang Cetakan' }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
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
                if (penjelasanInput) penjelasanInput.required = false;
            } else {
                badgeSelisih.classList.add('badge-soft-danger');
                badgeSelisih.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Ada Selisih: Kurang (- ' + formatRp(Math.abs(selisih)) + ')';
                if (labelWajib) labelWajib.style.display = 'inline';
                if (penjelasanInput) penjelasanInput.required = false;
            }
        }
        if (window.evalKasHandler) window.evalKasHandler.update();
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
                if (ketInput) ketInput.required = false;
            } else {
                selisihBadge.classList.add('badge-soft-danger');
                selisihBadge.innerHTML = 'Ada Selisih: Kurang (- ' + formatRp(Math.abs(saldoCbs - totalFisik)) + ')';
                if (ketWajib) ketWajib.style.display = 'inline';
                if (ketInput) ketInput.required = false;
            }
        }
        if (window.evalAtmHandler) window.evalAtmHandler.update();
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
                        <input type="number" step="any" name="atm_machines[${mIdx}][cassettes][${cIdx}][total_nominal]" class="form-control form-control-sm text-end fw-bold kaset-total-nominal text-primary" placeholder="0" value="0">
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
                        <span class="fs-5 text-primary">🏢</span>
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <span class="input-group-text fw-bold bg-white text-muted">ID ATM:</span>
                            <input type="text" name="atm_machines[${newMIdx}][terminal_id]" class="form-control font-monospace fw-bold fs-6 atm-terminal-id text-primary" placeholder="00128" value="${newTerminalId}">
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
                            <input type="text" name="atm_machines[${newMIdx}][lokasi]" class="form-control form-control-sm" placeholder="Contoh: ATM Museum / Galeri KC" value="ATM Baru Cabang">
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
                                <input type="number" step="any" name="atm_machines[${newMIdx}][saldo_cbs]" class="form-control form-control-sm text-end fw-bold atm-saldo-cbs" placeholder="0" value="0">
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
                                    <td><input type="number" step="any" name="atm_machines[${newMIdx}][cassettes][0][total_nominal]" class="form-control form-control-sm text-end fw-bold kaset-total-nominal text-primary" placeholder="0" value="0"></td>
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
        if (window.evalKartuHandler) window.evalKartuHandler.update();
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
                    <input type="text" name="kartu_atm_stock[${newIdx}][jenis_kartu]" class="form-control form-control-sm fw-bold" placeholder="Nama Jenis Kartu" value="Kartu Tambahan ${newIdx + 1}">
                    <input type="hidden" name="kartu_atm_stock[${newIdx}][keterangan_lokasi]" value="Brankas Khasanah">
                </td>
                <td><input type="number" min="0" step="1" name="kartu_atm_stock[${newIdx}][qty_fisik_kosong]" class="form-control form-control-sm text-end atm-qty-kosong" placeholder="0" value="0"></td>
                <td><input type="number" min="0" step="1" name="kartu_atm_stock[${newIdx}][qty_fisik_rusak]" class="form-control form-control-sm text-end atm-qty-rusak" placeholder="0" value="0"></td>
                <td><input type="text" class="form-control form-control-sm text-end fw-bold atm-total-fisik-display border-0 bg-transparent" readonly value="0"></td>
                <td><input type="number" step="any" name="kartu_atm_stock[${newIdx}][harga_modal]" class="form-control form-control-sm text-end atm-harga-modal" value="50000"></td>
                <td><input type="text" class="form-control form-control-sm text-end fw-bold atm-nominal-fisik-display border-0 bg-transparent text-primary" readonly value="Rp 0"></td>
                <td><input type="number" min="0" step="1" name="kartu_atm_stock[${newIdx}][saldo_buku_qty]" class="form-control form-control-sm text-end atm-buku-qty" placeholder="0" value="0"></td>
                <td><input type="number" step="any" name="kartu_atm_stock[${newIdx}][saldo_buku_nominal]" class="form-control form-control-sm text-end atm-buku-nom" placeholder="0" value="0"></td>
                <td><input type="text" class="form-control form-control-sm text-end fw-bold atm-selisih-persediaan-display border-0 bg-transparent" readonly value="0"></td>
                <td><input type="text" class="form-control form-control-sm text-end atm-kartu-count-display border-0 bg-transparent" readonly value="0"></td>
                <td><input type="number" min="0" step="1" name="kartu_atm_stock[${newIdx}][pin_mailer_qty]" class="form-control form-control-sm text-end atm-pin-qty" placeholder="0" value="0"></td>
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
            inp.addEventListener('input', () => {
                calcBctRow(row);
                if (window.evalCetakanHandler) window.evalCetakanHandler.update();
            });
            inp.addEventListener('change', () => {
                calcBctRow(row);
                if (window.evalCetakanHandler) window.evalCetakanHandler.update();
            });
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
                    <input type="text" name="barang_cetakan_details[${newIdx}][nama_barang]" class="form-control form-control-sm" placeholder="Nama Barang Cetakan">
                </td>
                <td>
                    <input type="text" name="barang_cetakan_details[${newIdx}][kriteria]" class="form-control form-control-sm bct-kriteria-input" placeholder="Nama Barang — Kosong / Belum Digunakan">
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
                    <input type="number" step="any" name="barang_cetakan_details[${newIdx}][stok_sistem]" class="form-control form-control-sm text-end bct-stok-buku" placeholder="0" value="0">
                </td>
                <td>
                    <input type="number" step="any" name="barang_cetakan_details[${newIdx}][hasil_fisik]" class="form-control form-control-sm text-end bct-hasil-fisik" placeholder="0" value="0">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm text-end fw-bold bct-selisih-display border-0 bg-transparent" readonly value="0">
                </td>
                <td class="text-center">
                    <span class="badge-soft badge-soft-success bct-status-badge">Sesuai</span>
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
                if (evalCetakanHandler) evalCetakanHandler.update();
            });
            initBctRowListeners(tr);
            if (evalCetakanHandler) evalCetakanHandler.update();
        });
    }

    // =========================================================================
    // 5. FRAMEWORK UNIFIED EVALUATION & PENILAIAN RISIKO (TAB 1 S.D. TAB 6)
    // =========================================================================
    function setupUnifiedEvaluationCard(config) {
        const card = document.getElementById(config.cardId);
        if (!card) return null;

        const impactInp = card.querySelector('.eval-impact');
        const impactBadge = card.querySelector('.eval-impact-badge');
        const likelihoodInp = card.querySelector('.eval-likelihood');
        const likelihoodBadge = card.querySelector('.eval-likelihood-badge');
        const recurrenceBadge = card.querySelector('.eval-recurrence-badge');
        const likelihoodNote = card.querySelector('.eval-likelihood-note');
        const criticalInp = card.querySelector('.eval-critical');
        const criticalBadge = card.querySelector('.eval-critical-badge');
        const scoreInp = card.querySelector('.eval-risk-score');
        const levelInp = card.querySelector('.eval-risk-level-input');
        const levelBadge = card.querySelector('.eval-risk-level-badge');
        const penyebabInp = card.querySelector('.eval-penyebab');
        const reqIndicator = card.querySelector('.eval-req-indicator');
        const form = card.closest('form');

        let previewTimer = null;
        function fetchPreview() {
            if (!config.tab || !form) return;
            clearTimeout(previewTimer);
            previewTimer = setTimeout(function() {
                try {
                    const formData = new FormData(form);
                    formData.append('tab', config.tab);
                    formData.append('context', 'physical');
                    formData.append('kode_unit', '{{ $kodeUnit }}');
                    @if(isset($id))
                    formData.append('kka_id', '{{ $id }}');
                    @endif
                    fetch('{{ route("daily-register.auto-risk.preview") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res && res.success && res.data) {
                            const d = res.data;
                            if (likelihoodInp) likelihoodInp.value = d.likelihood;
                            if (likelihoodBadge) likelihoodBadge.innerText = d.likelihood;
                            if (recurrenceBadge && d.likelihood_badge) {
                                recurrenceBadge.innerText = d.likelihood_badge;
                                recurrenceBadge.className = 'badge border eval-recurrence-badge ' + (d.likelihood > 1 ? 'bg-warning-subtle text-dark' : 'bg-info-subtle text-dark');
                            }
                            if (likelihoodNote && d.likelihood_note) likelihoodNote.innerText = d.likelihood_note;
                            if (impactInp) impactInp.value = d.impact;
                            if (impactBadge) impactBadge.innerText = d.impact;
                            if (criticalInp) criticalInp.value = d.is_critical ? '1' : '0';
                            if (criticalBadge) {
                                criticalBadge.innerText = d.is_critical ? 'Ya' : 'Tidak';
                                criticalBadge.className = 'badge px-3 py-2 fs-6 eval-critical-badge ' + (d.is_critical ? 'bg-danger text-white' : 'bg-success text-white');
                            }
                            if (scoreInp) scoreInp.value = d.risk_score;
                            if (levelInp) levelInp.value = d.risk_level;
                            if (levelBadge) {
                                levelBadge.innerText = d.risk_level;
                                levelBadge.className = 'badge px-3 py-2 fs-6 eval-risk-level-badge ';
                                if (d.risk_level === 'High') {
                                    levelBadge.classList.add('bg-danger', 'text-white');
                                } else if (d.risk_level === 'Moderate to High') {
                                    levelBadge.classList.add('bg-danger-subtle', 'text-danger-emphasis', 'border', 'border-danger');
                                } else if (d.risk_level === 'Moderate') {
                                    levelBadge.classList.add('bg-warning', 'text-dark');
                                } else if (d.risk_level === 'Low to Moderate') {
                                    levelBadge.classList.add('bg-info', 'text-dark');
                                } else {
                                    levelBadge.classList.add('bg-success', 'text-white');
                                }
                            }
                        }
                    })
                    .catch(() => {});
                } catch(e) {}
            }, 300);
        }

        function update() {
            const hasException = config.checkException();
            const metrics = config.calculateMetrics ? config.calculateMetrics() : {
                nominal: 0,
                failedCount: hasException ? 1 : 0,
                hasCriticalSecurity: false,
                hasWarkatCardDiff: false,
                hasCashAtmDiff: false,
                isCritical: false
            };

            let impact = 1;
            let likelihood = parseInt(likelihoodInp?.value) || 1;
            let isCrit = false;

            if (!hasException) {
                impact = 1;
                likelihood = 1;
                isCrit = false;
            } else {
                const nom = Math.abs(metrics.nominal || 0);
                if (metrics.hasCriticalSecurity || nom >= 100000000 || (metrics.hasCashAtmDiff && nom >= 25000000)) {
                    impact = 5;
                } else if (nom >= 25000000 || metrics.hasWarkatCardDiff || (metrics.failedCount > 3)) {
                    impact = 4;
                } else if (nom >= 5000000 || (metrics.failedCount >= 2)) {
                    impact = 3;
                } else {
                    impact = 2;
                }

                if (metrics.isCritical || metrics.hasCashAtmDiff || metrics.hasCriticalSecurity || metrics.hasWarkatCardDiff || impact === 5) {
                    isCrit = true;
                }
            }

            impact = Math.max(1, Math.min(5, impact));
            likelihood = Math.max(1, Math.min(5, likelihood));

            let score = impact * likelihood;
            let level = 'Low';

            if (isCrit) {
                if (score < 16) score = 16;
                level = 'High';
            } else if (!hasException) {
                level = 'Low';
            } else {
                if (score >= 16) {
                    level = 'High';
                } else if (score >= 12) {
                    level = 'Moderate to High';
                } else if (score >= 8) {
                    level = 'Moderate';
                } else if (score >= 4) {
                    level = 'Low to Moderate';
                } else {
                    level = 'Low';
                }
            }

            if (impactInp) impactInp.value = impact;
            if (impactBadge) impactBadge.innerText = impact;

            if (likelihoodInp) likelihoodInp.value = likelihood;
            if (likelihoodBadge) likelihoodBadge.innerText = likelihood;
            if (recurrenceBadge) {
                if (!hasException) {
                    recurrenceBadge.innerText = 'Nihil Temuan';
                    recurrenceBadge.className = 'badge bg-light text-muted border eval-recurrence-badge';
                } else if (likelihood > 1) {
                    recurrenceBadge.innerText = 'Temuan Berulang';
                    recurrenceBadge.className = 'badge bg-warning-subtle text-dark border eval-recurrence-badge';
                } else {
                    recurrenceBadge.innerText = 'Temuan Baru';
                    recurrenceBadge.className = 'badge bg-info-subtle text-dark border eval-recurrence-badge';
                }
            }
            if (likelihoodNote) {
                if (!hasException) {
                    likelihoodNote.innerText = 'Nihil Temuan';
                } else if (likelihood > 1) {
                    likelihoodNote.innerText = 'Terdeteksi riwayat temuan di cabang';
                } else {
                    likelihoodNote.innerText = 'Temuan pertama kali';
                }
            }

            if (criticalInp) criticalInp.value = isCrit ? '1' : '0';
            if (criticalBadge) {
                criticalBadge.innerText = isCrit ? 'Ya' : 'Tidak';
                criticalBadge.className = 'badge px-3 py-2 fs-6 eval-critical-badge ' + (isCrit ? 'bg-danger text-white' : 'bg-success text-white');
            }

            if (scoreInp) scoreInp.value = score;
            if (levelInp) levelInp.value = level;

            if (levelBadge) {
                levelBadge.innerText = level;
                levelBadge.className = 'badge px-3 py-2 fs-6 eval-risk-level-badge ';
                if (level === 'High') {
                    levelBadge.classList.add('bg-danger', 'text-white');
                } else if (level === 'Moderate to High') {
                    levelBadge.classList.add('bg-danger-subtle', 'text-danger-emphasis', 'border', 'border-danger');
                } else if (level === 'Moderate') {
                    levelBadge.classList.add('bg-warning', 'text-dark');
                } else if (level === 'Low to Moderate') {
                    levelBadge.classList.add('bg-info', 'text-dark');
                } else {
                    levelBadge.classList.add('bg-success', 'text-white');
                }
            }

            if (penyebabInp) {
                if (hasException) {
                    if (reqIndicator) reqIndicator.style.display = 'inline';
                    penyebabInp.required = true;
                } else {
                    if (reqIndicator) reqIndicator.style.display = 'none';
                    penyebabInp.required = false;
                    penyebabInp.classList.remove('is-invalid');
                }
            }

            fetchPreview();
        }

        if (form) {
            form.addEventListener('submit', function(e) {
                const hasException = config.checkException();
                if (hasException && penyebabInp && !penyebabInp.value.trim()) {
                    e.preventDefault();
                    penyebabInp.classList.add('is-invalid');
                    penyebabInp.focus();
                    alert('Penyebab & Keterangan wajib diisi pada form ini karena terdeteksi selisih / hasil pemeriksaan tidak sesuai!');
                    return false;
                } else if (penyebabInp) {
                    penyebabInp.classList.remove('is-invalid');
                }
            });
        }

        update();
        return { update };
    }

    // A. Setup Evaluation untuk Tab 1 (Kas Khasanah)
    const evalKasHandler = setupUnifiedEvaluationCard({
        cardId: 'evalCardKas',
        tab: 'kas',
        checkException: function() {
            const inputOlibs = document.getElementById('inputSaldoOlibs');
            const saldoOlibs = parseFloat(inputOlibs?.value) || 0;
            let totalFisik = 0;
            document.querySelectorAll('#cash .cash-lembar').forEach(inp => {
                const tr = inp.closest('tr');
                const nom = parseFloat(tr?.querySelector('.subtotal-display')?.value) || 0;
                totalFisik += nom;
            });
            return Math.abs(totalFisik - saldoOlibs) >= 0.01;
        },
        calculateMetrics: function() {
            const inputOlibs = document.getElementById('inputSaldoOlibs');
            const saldoOlibs = parseFloat(inputOlibs?.value) || 0;
            let totalFisik = 0;
            document.querySelectorAll('#cash .cash-lembar').forEach(inp => {
                const tr = inp.closest('tr');
                const nom = parseFloat(tr?.querySelector('.subtotal-display')?.value) || 0;
                totalFisik += nom;
            });
            const sel = Math.abs(totalFisik - saldoOlibs);
            return {
                nominal: sel,
                failedCount: sel >= 0.01 ? 1 : 0,
                hasCashAtmDiff: sel >= 0.01,
                isCritical: sel >= 0.01
            };
        }
    });
    window.evalKasHandler = evalKasHandler;

    // B. Setup Evaluation untuk Tab 2 (Mesin ATM)
    const evalAtmHandler = setupUnifiedEvaluationCard({
        cardId: 'evalCardAtm',
        tab: 'atm',
        checkException: function() {
            let hasEx = false;
            document.querySelectorAll('#cash-atm .atm-card').forEach(card => {
                const saldoCbs = parseFloat(card.querySelector('.atm-saldo-cbs')?.value) || 0;
                let totFisik = 0;
                card.querySelectorAll('.kaset-total-nominal').forEach(c => {
                    totFisik += (parseFloat(c.value) || 0);
                });
                if (Math.abs(totFisik - saldoCbs) >= 0.01) hasEx = true;
            });
            return hasEx;
        },
        calculateMetrics: function() {
            let totalSel = 0;
            let failed = 0;
            document.querySelectorAll('#cash-atm .atm-card').forEach(card => {
                const saldoCbs = parseFloat(card.querySelector('.atm-saldo-cbs')?.value) || 0;
                let totFisik = 0;
                card.querySelectorAll('.kaset-total-nominal').forEach(c => {
                    totFisik += (parseFloat(c.value) || 0);
                });
                const diff = Math.abs(totFisik - saldoCbs);
                if (diff >= 0.01) {
                    totalSel += diff;
                    failed++;
                }
            });
            return {
                nominal: totalSel,
                failedCount: failed,
                hasCashAtmDiff: totalSel >= 0.01,
                isCritical: totalSel >= 0.01
            };
        }
    });
    window.evalAtmHandler = evalAtmHandler;

    // C. Setup Evaluation untuk Tab 3 (Observasi)
    const evalObsHandler = setupUnifiedEvaluationCard({
        cardId: 'evalCardObs',
        tab: 'observasi',
        checkException: function() {
            let hasEx = false;
            document.querySelectorAll('#obs select.hasil-obs').forEach(sel => {
                if (sel.value === 'Tidak Sesuai' || sel.value === 'Tidak' || sel.value === 'Kurang Baik') hasEx = true;
            });
            return hasEx;
        },
        calculateMetrics: function() {
            let failed = 0;
            let hasCritSec = false;
            document.querySelectorAll('#obs select.hasil-obs').forEach(sel => {
                if (sel.value === 'Tidak Sesuai' || sel.value === 'Tidak' || sel.value === 'Kurang Baik') {
                    failed++;
                    const rowText = sel.closest('tr')?.innerText || '';
                    if (/dual control|khasanah|brankas|kunci|cctv|alarm/i.test(rowText)) {
                        hasCritSec = true;
                    }
                }
            });
            return {
                nominal: 0,
                failedCount: failed,
                hasCriticalSecurity: hasCritSec,
                isCritical: hasCritSec
            };
        }
    });
    window.evalObsHandler = evalObsHandler;

    document.querySelectorAll('#obs select.hasil-obs').forEach(sel => {
        sel.addEventListener('change', function() {
            if (evalObsHandler) evalObsHandler.update();
        });
    });

    // D. Setup Evaluation & Kalkulasi Baris untuk Tab 4 (Stok Cek/BG)
    function calcStokRow(row) {
        const isCtrl = row.dataset.isCtrl === '1';
        if (isCtrl) return;

        const sysInp = row.querySelector('.saldo-sistem');
        const fisInp = row.querySelector('.stok-fisik-input');
        const selDisp = row.querySelector('.stok-selisih-display');
        const statBadge = row.querySelector('.stok-status-badge');
        const statInp = row.querySelector('.stok-status-input');

        const sys = parseFloat(sysInp?.value) || 0;
        const fisVal = fisInp?.value;
        if (fisVal === '' || fisVal === null || isNaN(parseFloat(fisVal))) {
            if (selDisp) {
                selDisp.value = '';
                selDisp.className = 'form-control form-control-sm selisih-display border-0 bg-transparent text-end fw-bold stok-selisih-display';
            }
            if (statBadge) {
                statBadge.className = 'badge-soft badge-soft-secondary stok-status-badge';
                statBadge.innerText = 'Belum Diuji';
            }
            if (statInp) statInp.value = 'BELUM DIUJI';
            return;
        }

        const fis = parseFloat(fisVal) || 0;
        const sel = sys - fis;
        if (selDisp) {
            selDisp.value = sel;
            selDisp.className = 'form-control form-control-sm selisih-display border-0 bg-transparent text-end fw-bold stok-selisih-display ' + (Math.abs(sel) >= 0.01 ? 'text-danger' : 'text-success');
        }

        if (Math.abs(sel) >= 0.01) {
            if (statBadge) {
                statBadge.className = 'badge-soft badge-soft-danger stok-status-badge';
                statBadge.innerText = 'Selisih';
            }
            if (statInp) statInp.value = 'Exception';
        } else {
            if (statBadge) {
                statBadge.className = 'badge-soft badge-soft-success stok-status-badge';
                statBadge.innerText = 'Sesuai';
            }
            if (statInp) statInp.value = 'Sesuai';
        }
    }

    const evalStokHandler = setupUnifiedEvaluationCard({
        cardId: 'evalCardStok',
        tab: 'stok',
        checkException: function() {
            let hasEx = false;
            document.querySelectorAll('#stok .stok-calc-row').forEach(row => {
                const isCtrl = row.dataset.isCtrl === '1';
                if (isCtrl) {
                    const checkedRadio = row.querySelector('.stok-ctrl-radio:checked');
                    if (checkedRadio && checkedRadio.value === 'Tidak') hasEx = true;
                } else {
                    const sysInp = row.querySelector('.saldo-sistem');
                    const fisInp = row.querySelector('.stok-fisik-input');
                    if (fisInp && fisInp.value !== '') {
                        const sys = parseFloat(sysInp?.value) || 0;
                        const fis = parseFloat(fisInp.value) || 0;
                        if (Math.abs(sys - fis) >= 0.01) hasEx = true;
                    }
                }
            });
            return hasEx;
        },
        calculateMetrics: function() {
            let failed = 0;
            let hasWarkat = false;
            document.querySelectorAll('#stok .stok-calc-row').forEach(row => {
                const isCtrl = row.dataset.isCtrl === '1';
                if (isCtrl) {
                    const checkedRadio = row.querySelector('.stok-ctrl-radio:checked');
                    if (checkedRadio && checkedRadio.value === 'Tidak') failed++;
                } else {
                    const sysInp = row.querySelector('.saldo-sistem');
                    const fisInp = row.querySelector('.stok-fisik-input');
                    if (fisInp && fisInp.value !== '') {
                        const sys = parseFloat(sysInp?.value) || 0;
                        const fis = parseFloat(fisInp.value) || 0;
                        if (Math.abs(sys - fis) >= 0.01) {
                            failed++;
                            hasWarkat = true;
                        }
                    }
                }
            });
            return {
                nominal: 0,
                failedCount: failed,
                hasWarkatCardDiff: hasWarkat,
                isCritical: hasWarkat
            };
        }
    });

    document.querySelectorAll('#stok .stok-calc-row').forEach(row => {
        const fisInp = row.querySelector('.stok-fisik-input');
        if (fisInp) {
            fisInp.addEventListener('input', function() {
                calcStokRow(row);
                if (evalStokHandler) evalStokHandler.update();
            });
            fisInp.addEventListener('change', function() {
                calcStokRow(row);
                if (evalStokHandler) evalStokHandler.update();
            });
            calcStokRow(row);
        }
        row.querySelectorAll('.stok-ctrl-radio').forEach(rad => {
            rad.addEventListener('change', function() {
                const badge = row.querySelector('.stok-status-badge');
                if (badge) {
                    badge.className = 'badge-soft stok-status-badge ' + (this.value === 'Tidak' ? 'badge-soft-danger' : 'badge-soft-success');
                    badge.innerText = this.value;
                }
                if (evalStokHandler) evalStokHandler.update();
            });
        });
    });

    // E. Setup Evaluation untuk Tab 5 (Kartu ATM)
    const evalKartuHandler = setupUnifiedEvaluationCard({
        cardId: 'evalCardKartu',
        tab: 'kartu',
        checkException: function() {
            let hasEx = false;
            document.querySelectorAll('#atm .atm-stock-row').forEach(row => {
                const hargaModal = parseFloat(row.querySelector('.atm-harga-modal')?.value) || 0;
                const kosong = parseInt(row.querySelector('.atm-kosong')?.value) || 0;
                const nomFisik = hargaModal * kosong;
                const bukuNom = parseFloat(row.querySelector('.atm-buku-nom')?.value) || 0;
                const selPers = nomFisik - bukuNom;
                const pinQty = parseInt(row.querySelector('.atm-pin-mailer')?.value) || 0;
                const selPin = kosong - pinQty;
                if (Math.abs(selPers) >= 0.01 || selPin !== 0) hasEx = true;
            });
            document.querySelectorAll('#atm .atm-ctrl-radio:checked').forEach(r => {
                if (r.value === 'Tidak') hasEx = true;
            });
            return hasEx;
        },
        calculateMetrics: function() {
            let totalNom = 0;
            let failed = 0;
            let hasDual = false;
            document.querySelectorAll('#atm .atm-stock-row').forEach(row => {
                const hargaModal = parseFloat(row.querySelector('.atm-harga-modal')?.value) || 0;
                const kosong = parseInt(row.querySelector('.atm-kosong')?.value) || 0;
                const nomFisik = hargaModal * kosong;
                const bukuNom = parseFloat(row.querySelector('.atm-buku-nom')?.value) || 0;
                const selPers = Math.abs(nomFisik - bukuNom);
                const pinQty = parseInt(row.querySelector('.atm-pin-mailer')?.value) || 0;
                const selPin = Math.abs(kosong - pinQty);
                if (selPers >= 0.01 || selPin !== 0) {
                    totalNom += selPers;
                    failed++;
                }
            });
            document.querySelectorAll('#atm .atm-ctrl-radio:checked').forEach(r => {
                if (r.value === 'Tidak') {
                    failed++;
                    const text = r.closest('tr')?.innerText || '';
                    if (/dual control|kunci/i.test(text)) hasDual = true;
                }
            });
            return {
                nominal: totalNom,
                failedCount: failed,
                hasWarkatCardDiff: totalNom >= 0.01 || failed > 0,
                hasCriticalSecurity: hasDual,
                isCritical: totalNom >= 0.01 || hasDual
            };
        }
    });
    window.evalKartuHandler = evalKartuHandler;

    // F. Setup Evaluation untuk Tab 6 (Barang Cetakan)
    const evalCetakanHandler = setupUnifiedEvaluationCard({
        cardId: 'evalCardCetakan',
        tab: 'cetakan',
        checkException: function() {
            let hasEx = false;
            document.querySelectorAll('#tbodyBarangCetakan .bct-calc-row').forEach(row => {
                const stokInp = row.querySelector('.bct-stok-buku');
                const fisikInp = row.querySelector('.bct-hasil-fisik');
                if (fisikInp && fisikInp.value !== '') {
                    const stok = parseFloat(stokInp?.value) || 0;
                    const fisik = parseFloat(fisikInp.value) || 0;
                    if (Math.abs(fisik - stok) >= 0.01) hasEx = true;
                }
            });
            document.querySelectorAll('#cetakan .bct-ctrl-radio:checked').forEach(r => {
                if (r.value === 'Tidak') hasEx = true;
            });
            return hasEx;
        },
        calculateMetrics: function() {
            let failed = 0;
            let hasWarkat = false;
            document.querySelectorAll('#tbodyBarangCetakan .bct-calc-row').forEach(row => {
                const stokInp = row.querySelector('.bct-stok-buku');
                const fisikInp = row.querySelector('.bct-hasil-fisik');
                if (fisikInp && fisikInp.value !== '') {
                    const stok = parseFloat(stokInp?.value) || 0;
                    const fisik = parseFloat(fisikInp.value) || 0;
                    if (Math.abs(fisik - stok) >= 0.01) {
                        failed++;
                        const name = row.querySelector('.bct-kriteria-input')?.value || '';
                        if (/cek|bilyet|giro|warkat/i.test(name)) hasWarkat = true;
                    }
                }
            });
            document.querySelectorAll('#cetakan .bct-ctrl-radio:checked').forEach(r => {
                if (r.value === 'Tidak') failed++;
            });
            return {
                nominal: 0,
                failedCount: failed,
                hasWarkatCardDiff: hasWarkat,
                isCritical: hasWarkat
            };
        }
    });
    window.evalCetakanHandler = evalCetakanHandler;

    document.querySelectorAll('#cetakan .bct-ctrl-radio').forEach(r => {
        r.addEventListener('change', function() {
            if (evalCetakanHandler) evalCetakanHandler.update();
        });
    });

    // =========================================================================
    // 6. SINKRONISASI NO ARSIP & POSISI TANGGAL GLOBAL KE SELURUH FORM KKA FISIK
    // =========================================================================
    const globalNoArsip = document.getElementById('globalNoArsip');
    if (globalNoArsip) {
        globalNoArsip.addEventListener('input', function() {
            document.querySelectorAll('.sync-no-arsip').forEach(el => el.value = this.value);
        });
        globalNoArsip.addEventListener('change', function() {
            document.querySelectorAll('.sync-no-arsip').forEach(el => el.value = this.value);
        });
    }

    const globalPosisiTanggalRekon = document.getElementById('globalPosisiTanggalRekon');
    if (globalPosisiTanggalRekon) {
        globalPosisiTanggalRekon.addEventListener('change', function() {
            document.querySelectorAll('.sync-posisi-tanggal').forEach(el => el.value = this.value);
        });
        globalPosisiTanggalRekon.addEventListener('input', function() {
            document.querySelectorAll('.sync-posisi-tanggal').forEach(el => el.value = this.value);
        });
    }

    // Pastikan saat form disubmit, nilai terbaru langsung disinkronkan ke input tersembunyi
    document.querySelectorAll('#physicalKkaTabContent form').forEach(frm => {
        frm.addEventListener('submit', function() {
            if (globalNoArsip) {
                this.querySelectorAll('.sync-no-arsip').forEach(el => el.value = globalNoArsip.value);
            }
            if (globalPosisiTanggalRekon) {
                this.querySelectorAll('.sync-posisi-tanggal').forEach(el => el.value = globalPosisiTanggalRekon.value);
            }
        });
    });

    // =========================================================================
    // 7. AUTO-AKTIVASI TAB KKA FISIK BERDASARKAN QUERY PARAMETER URL (?tab=...) ATAU SESSION old('sub_tab')
    // =========================================================================
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || '{{ old("sub_tab", "") }}';
    if (activeTab) {
        const tabMap = {
            'kas': '#cash-tab',
            'cash': '#cash-tab',
            'atm': '#cash-atm-tab',
            'cash-atm': '#cash-atm-tab',
            'observasi': '#obs-tab',
            'obs': '#obs-tab',
            'stok': '#stok-tab',
            'kartu': '#atm-tab',
            'cetakan': '#cetakan-tab'
        };
        const targetTabSelector = tabMap[activeTab];
        if (targetTabSelector) {
            const tabBtn = document.querySelector(targetTabSelector);
            if (tabBtn) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                    const bsTab = new bootstrap.Tab(tabBtn);
                    bsTab.show();
                } else {
                    tabBtn.click();
                }
            }
        }
    }
});
</script>

@endsection
