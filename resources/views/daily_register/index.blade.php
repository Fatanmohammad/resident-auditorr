@extends('layouts.app')

@section('content')

<style>
    /* Styling Dashboard & Cards */
    :root {
        --bg-body: #f8fafc; --text-dark: #1e293b; --text-muted: #64748b;
        --border-color: #e2e8f0; --primary-blue: #1e3a8a;
    }
    
    .dashboard-header h4 { font-weight: 700; color: var(--text-dark); margin-bottom: 0.3rem; }
    .dashboard-header p { color: var(--text-muted); font-size: 0.88rem; margin-bottom: 0; }

    .summary-cards-wrapper { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 0.85rem; margin-bottom: 1.25rem; }
    @media (max-width: 1200px) {
        .summary-cards-wrapper { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 768px) {
        .summary-cards-wrapper { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    .stat-card {
        background: #fff; border: 1px solid var(--border-color); border-radius: 12px;
        padding: 1.1rem; display: flex; align-items: flex-start; gap: 0.85rem; position: relative;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .stat-icon {
        width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0;
    }
    .stat-icon.bg-light-gray { background: #f1f5f9; color: #475569; }
    .stat-icon.bg-light-blue { background: #eff6ff; color: #2563eb; }
    .stat-icon.bg-light-red { background: #fef2f2; color: #ef4444; }
    .stat-icon.bg-light-green { background: #f0fdf4; color: #16a34a; }
    
    .stat-details h6 { font-size: 0.72rem; font-weight: 700; color: var(--text-muted); margin: 0 0 0.2rem 0; text-transform: uppercase; letter-spacing: 0.4px; }
    .stat-details h3 { font-size: 1.25rem; font-weight: 700; color: var(--text-dark); margin: 0 0 0.15rem 0; }
    .stat-details p { font-size: 0.72rem; color: var(--text-muted); margin: 0; }

    /* Modern Harmonized Sub Menu Tabs (No Horizontal Scrollbar, Wraps Cleanly) */
    .kka-tabs { 
        display: flex; 
        align-items: center; 
        flex-wrap: wrap; 
        gap: 0.45rem; 
        padding: 0.45rem 0.6rem; 
        margin-bottom: 1.25rem; 
        background: #ffffff; 
        border: 1px solid var(--border-color); 
        border-radius: 12px; 
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .kka-tab-item {
        background: #f8fafc; 
        border: 1px solid #e2e8f0; 
        border-radius: 8px; 
        padding: 0.45rem 0.8rem; 
        font-size: 0.8rem; 
        font-weight: 600; 
        color: #475569; 
        display: inline-flex; 
        align-items: center; 
        gap: 0.45rem; 
        cursor: pointer; 
        transition: all 0.2s ease; 
        white-space: nowrap; 
        user-select: none;
    }
    .kka-tab-item i { font-size: 0.92rem; color: #64748b; transition: color 0.2s; }
    .kka-tab-item:hover { 
        background: #eff6ff; 
        border-color: #bfdbfe; 
        color: var(--primary-blue); 
    }
    .kka-tab-item:hover i { color: var(--primary-blue); }
    .kka-tab-item.active { 
        background: #1e3a8a; 
        border-color: #1e3a8a; 
        color: #ffffff; 
        box-shadow: 0 2px 6px rgba(30, 58, 138, 0.2); 
    }
    .kka-tab-item.active i { color: #ffffff !important; }
    .kka-tab-item .badge-count { 
        background: #e2e8f0; 
        color: #475569; 
        padding: 0.12rem 0.5rem; 
        border-radius: 12px; 
        font-size: 0.7rem; 
        font-weight: 700; 
        transition: all 0.2s; 
    }
    .kka-tab-item.active .badge-count { 
        background: rgba(255, 255, 255, 0.22); 
        color: #ffffff; 
    }
    .kka-tab-item.tab-control-integrasi {
        margin-left: auto;
        background: #eff6ff;
        border: 1.5px solid #bfdbfe;
        color: #1e3a8a;
        font-weight: 700;
    }
    .kka-tab-item.tab-control-integrasi i { color: #2563eb; }
    .kka-tab-item.tab-control-integrasi:hover {
        background: #dbeafe;
        border-color: #93c5fd;
    }
    .kka-tab-item.tab-control-integrasi.active {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
        border-color: #0f172a;
        color: #ffffff;
        box-shadow: 0 3px 8px rgba(15, 23, 42, 0.3);
    }
    .kka-tab-item.tab-control-integrasi.active i { color: #fbbf24 !important; }
    .kka-tab-item.tab-control-integrasi .badge-count {
        background: #1e3a8a;
        color: #ffffff;
    }
    .kka-tab-item.tab-control-integrasi.active .badge-count {
        background: #f59e0b;
        color: #0f172a;
    }
    @media (max-width: 1200px) {
        .kka-tab-item.tab-control-integrasi { margin-left: 0; }
    }

    /* Filter Box Style */
    .filter-card {
        background: #fff; border: 1px solid var(--border-color); border-radius: 12px;
        padding: 1.25rem; margin-bottom: 1.5rem;
    }
    .filter-grid {
        display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)) auto auto; gap: 1rem; align-items: flex-end;
    }
    .filter-label { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.3rem; text-transform: uppercase; display: block; }
    .filter-control { width: 100%; border: 1px solid var(--border-color); padding: 0.5rem 0.75rem; font-size: 0.85rem; border-radius: 8px; background: #fff; }
    
    .btn-filter { background: var(--primary-blue); color: #fff; border: none; padding: 0.5rem 1.2rem; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; }
    .btn-reset { background: #f1f5f9; color: var(--text-dark); border: 1px solid var(--border-color); padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; }

    /* Styling KKA Control & Integrasi (SOP 04.2) */
    .ci-summary-cards { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem; }
    @media (max-width: 1200px) {
        .ci-summary-cards { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 768px) {
        .ci-summary-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    .ci-stat-card {
        background: #fff; border: 1px solid var(--border-color); border-radius: 12px;
        padding: 1rem; display: flex; align-items: flex-start; gap: 0.75rem; position: relative;
        transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .ci-stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    
    /* Table Control Integrasi (100% Fit-to-Screen, Zero Horizontal Scroll) */
    .table-control-integrasi { 
        width: 100%; 
        table-layout: fixed; 
        border-collapse: collapse; 
        font-size: 0.76rem; 
    }
    .table-control-integrasi th {
        background: #f8fafc; color: #475569; font-weight: 700; font-size: 0.68rem;
        text-transform: uppercase; letter-spacing: 0.3px; padding: 0.65rem 0.5rem;
        border-bottom: 2px solid var(--border-color); border-right: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .table-control-integrasi td {
        padding: 0.6rem 0.5rem; border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9;
        vertical-align: middle; font-size: 0.76rem; word-break: break-word;
    }
    .table-control-integrasi tbody tr:hover { background-color: #f8fafc; }
    
    .badge-status-selesai { background: #dcfce7; color: #166534; border: 1px solid #86efac; border-radius: 12px; padding: 0.25rem 0.55rem; font-weight: 700; font-size: 0.68rem; display: inline-block; }
    .badge-status-pengujian { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; border-radius: 12px; padding: 0.25rem 0.55rem; font-weight: 700; font-size: 0.68rem; display: inline-block; }
    .badge-status-belum { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 12px; padding: 0.25rem 0.55rem; font-weight: 700; font-size: 0.68rem; display: inline-block; }
    .badge-action-lanjut { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; border-radius: 12px; padding: 0.25rem 0.55rem; font-weight: 700; font-size: 0.68rem; display: inline-block; }
    .badge-action-review { background: #fff7ed; color: #9a3412; border: 1px solid #fed7aa; border-radius: 12px; padding: 0.25rem 0.55rem; font-weight: 700; font-size: 0.68rem; display: inline-block; }

    /* Fix Tabel Utama (Fit Screen, Fixed Layout) */
    .table-wrapper { width: 100%; overflow-x: hidden; }
    .data-table { width: 100%; table-layout: fixed; border-collapse: collapse; }
    .data-table th { 
        padding: 0.75rem 0.6rem; background: #f8fafc; font-size: 0.72rem; font-weight: 700; 
        color: #475569; border-bottom: 2px solid var(--border-color); text-transform: uppercase; 
        letter-spacing: 0.3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; 
    }
    .data-table td { 
        padding: 0.7rem 0.6rem; vertical-align: middle; border-bottom: 1px solid var(--border-color); 
        font-size: 0.8rem; overflow: hidden; text-overflow: ellipsis; 
    }
    .data-table tbody tr:hover { background-color: #f8fafc; }

    .btn-review {
        background-color: var(--primary-blue); color: #fff; font-size: 0.76rem; font-weight: 600; 
        padding: 0.35rem 0.7rem; border-radius: 6px; border: none; display: inline-flex; 
        align-items: center; gap: 0.25rem; cursor: pointer; transition: all 0.2s; white-space: nowrap;
    }
    .btn-review:hover { background-color: #152c6b; color: #fff; transform: translateY(-1px); }

    /* Quick Filter Chips */
    .quick-chip {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 0.28rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #475569;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
    }
    .quick-chip:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #1e293b;
    }
    .quick-chip.active {
        background: #1e3a8a;
        border-color: #1e3a8a;
        color: #ffffff !important;
        box-shadow: 0 1px 4px rgba(30, 58, 138, 0.25);
    }
    .quick-chip.chip-revisi.active {
        background: #e11d48;
        border-color: #e11d48;
        color: #ffffff !important;
    }
    .quick-chip.chip-belum.active {
        background: #475569;
        border-color: #475569;
        color: #ffffff !important;
    }
    .quick-chip.chip-menunggu.active {
        background: #d97706;
        border-color: #d97706;
        color: #ffffff !important;
    }
    .quick-chip.chip-selesai.active {
        background: #059669;
        border-color: #059669;
        color: #ffffff !important;
    }
    .chip-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
    }
    .chip-dot.dot-red { background-color: #ef4444; }
    .chip-dot.dot-gray { background-color: #94a3b8; }
    .chip-dot.dot-amber { background-color: #f59e0b; }
    .chip-dot.dot-green { background-color: #10b981; }

    /* Tab Indicator Dots */
    .tab-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 2px;
        flex-shrink: 0;
    }
    .tab-dot.dot-red {
        background-color: #ef4444;
        box-shadow: 0 0 0 2px #fff, 0 0 6px rgba(239, 68, 68, 0.8);
        animation: pulse-dot 1.8s infinite;
    }
    .tab-dot.dot-amber {
        background-color: #f59e0b;
        box-shadow: 0 0 0 2px #fff, 0 0 6px rgba(245, 158, 11, 0.8);
        animation: pulse-dot 1.8s infinite;
    }
    @keyframes pulse-dot {
        0% { transform: scale(0.9); opacity: 0.8; }
        50% { transform: scale(1.2); opacity: 1; }
        100% { transform: scale(0.9); opacity: 0.8; }
    }
    .kka-tab-item .badge-count.tab-completed {
        background: #059669 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
    }

    /* Row Subtle Styling */
    .row-perlu-revisi {
        border-left: 4px solid #f43f5e !important;
        background-color: rgba(255, 241, 242, 0.45) !important;
    }
    .row-selesai {
        background-color: rgba(248, 250, 252, 0.75) !important;
        color: #64748b;
    }

    /* STYLING FULL PAGE DETAIL VIEW */
    .btn-back { background: #fff; border: 1px solid var(--border-color); padding: 0.4rem 1rem; border-radius: 6px; font-weight: 600; font-size: 0.85rem; color: var(--text-dark); cursor: pointer; display: flex; align-items: center; gap: 0.5rem; }
    
    .summary-box { background: #fff; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; }
    
    .custom-grid-5 { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1.5rem; margin-bottom: 1rem; }
    .custom-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 1.5rem; }
    .custom-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 1.5rem; }
    .custom-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
    
    .sum-label { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-bottom: 0.3rem; }
    .sum-val { font-weight: 700; font-size: 0.95rem; color: var(--text-dark); }
    .sum-val.red { color: #dc2626; font-size: 1.05rem; }

    .section-card { background: #fff; border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 1.5rem; overflow: hidden; }
    .section-card-header { padding: 1rem 1.5rem; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; background: #f8fafc; border-bottom: 1px solid var(--border-color); }
    .section-card-body { padding: 1.5rem; }
    
    .section-card.ra { border: 1px solid #fde68a; }
    .section-card.ra .section-card-header { background: #fffbeb; border-bottom: 1px solid #fde68a; color: #b45309; }
    
    .section-card.admin { border: 1px solid #a7f3d0; }
    .section-card.admin .section-card-header { background: #ecfdf5; border-bottom: 1px solid #a7f3d0; color: #047857; }

    .custom-form-label { font-size: 0.8rem; font-weight: 600; color: var(--text-dark); margin-bottom: 0.4rem; display: block; }
    .custom-form-control { width: 100%; border: 1px solid var(--border-color); padding: 0.6rem 0.8rem; font-size: 0.85rem; border-radius: 6px; box-sizing: border-box; background-color: #fff; }
    .custom-form-control:focus { outline: none; border-color: var(--primary-blue); }
    .custom-form-control:disabled { background-color: #f8fafc; color: var(--text-muted); cursor: not-allowed; }
</style>

<!-- ========================================== -->
<!-- 1. VIEW DASHBOARD & TABEL -->
<!-- ========================================== -->
<div id="dashboardView">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
        <div class="dashboard-header">
            @if($isSubmenu)
                <h4>{{ $defaultTabName }} (Daily Register)</h4>
            @else
                <h4>KKA Daily Register (6 KKA Harian)</h4>
            @endif
            <p>Review, verifikasi, dan tindak lanjuti indikasi temuan exception transaksi dan review rutin.</p>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            @if(strtolower(auth()->user()->role ?? '') !== 'ra' && isset($kcUnits))
            <div class="d-flex align-items-center gap-2 bg-white px-3 py-1 rounded shadow-sm border" style="border-color: var(--border-color) !important;">
                <label class="form-label mb-0 fw-bold text-muted" style="font-size: 0.78rem; white-space: nowrap;">
                    <i class="bi bi-buildings text-primary me-1"></i> KC:
                </label>
                <select id="selectAdminCabang" class="form-select form-select-sm border-0 fw-bold" style="font-size: 0.82rem; min-width: 220px; background: transparent; cursor: pointer; color: var(--primary-blue);" onchange="changeAdminCabang(this.value)">
                    @foreach($kcUnits as $kc)
                        <option value="{{ $kc->unit_code }}" data-cabang="{{ $kc->cabang_id }}" {{ ($selectedKodeUnit ?? '') === $kc->unit_code ? 'selected' : '' }}>
                            {{ $kc->unit_code }} - {{ $kc->unit_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <a href="{{ route('daily-register.physical-kka.index') }}" class="btn-filter" style="background: #1e3a8a; color: #fff; text-decoration: none;">
                <i class="bi bi-clipboard2-check"></i> Form 5 KKA Fisik
            </a>
            <button type="button" id="btnToggleRiwayat" onclick="toggleRiwayatMode()" class="btn-filter" style="background: #f1f5f9; color: #475569; border: 1px solid var(--border-color);">
                <i class="bi bi-clock-history"></i> <span id="btnRiwayatText">Lihat Arsip Riwayat Selesai</span>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- PANEL KONTROL HARIAN (KAPASITAS AUDITOR) & KONTROL MINGGUAN (JADWAL 5 KKA) -->
    <!-- HANYA TAMPIL UNTUK ROLE ADMIN / REVIEWER (TIDAK TAMPIL UNTUK ROLE RA)      -->
    <!-- ========================================================================= -->
    @if(strtolower(auth()->user()->role ?? '') !== 'ra')
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: #fff; border: 1px solid var(--border-color) !important; overflow: hidden;">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-bottom: 1px solid var(--border-color);">
            <div class="d-flex align-items-center gap-3">
                <span style="width: 40px; height: 40px; border-radius: 10px; background: #eff6ff; color: #1e3a8a; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                    <i class="bi bi-speedometer2"></i>
                </span>
                <div>
                    <h6 class="mb-0 fw-bold" style="color: var(--text-dark); font-size: 0.95rem;">
                        Kapasitas Harian Auditor (Kontrol_Harian) &amp; Penjadwalan Mingguan (Kontrol_Mingguan)
                    </h6>
                    <small class="text-muted" style="font-size: 0.75rem;">
                        Standar Alokasi: 420 Menit/Hari/Auditor (SOP 04.2 &amp; SOP 02). Pemeriksaan KKA Fisik memotong kuota menit harian.
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span id="badgeUnitInfo" class="badge bg-primary-subtle text-primary border px-3 py-2" style="font-size: 0.75rem; font-weight: 600;">
                    <i class="bi bi-building me-1"></i> KC: <span id="unitInfoText">{{ $selectedKodeUnit ?? '003' }}</span>
                </span>
                <span id="badgeMingguInfo" class="badge bg-light text-dark border px-3 py-2" style="font-size: 0.75rem;">
                    <i class="bi bi-calendar3 me-1 text-primary"></i> Minggu: <span id="mingguDatesText">Memuat...</span>
                </span>
                <span id="badgeKapasitasStatus" class="badge bg-success px-3 py-2" style="font-size: 0.78rem; font-weight: 600;">
                    <i class="bi bi-check-circle me-1"></i> DALAM KAPASITAS
                </span>
                <button type="button" class="btn btn-sm btn-outline-warning shadow-sm" onclick="triggerRerunSampling()" id="btnRerunSampling" style="font-size: 0.78rem; font-weight: 600; border-radius: 8px;" title="Jalankan ulang mesin sampling SOP 02 (Kapasitas KKA max 500 sampel per area)">
                    <i class="bi bi-arrow-repeat me-1"></i> Re-Run Sampling SOP 02
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openJadwalModal()" style="font-size: 0.78rem; font-weight: 600; border-radius: 8px;">
                    <i class="bi bi-calendar-range me-1"></i> Atur Jadwal Mingguan
                </button>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                <!-- SISI KIRI: KALKULATOR KAPASITAS MENIT HARIAN -->
                <div class="col-lg-6">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-bold" style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px;">
                            <i class="bi bi-clock-history me-1 text-primary"></i> Perhitungan Kapasitas Menit Harian
                        </div>
                        <span class="badge bg-light text-primary border" style="font-size: 0.72rem;">
                            Tanggal: <span id="todayReviewDateText">{{ date('d M Y') }}</span>
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin-bottom: 1rem;">
                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 0.75rem 1rem;">
                            <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Menit Harian</div>
                            <div style="font-size: 1.25rem; font-weight: 700; color: var(--text-dark);" id="calcTotalMenit">420 <small style="font-size: 0.75rem; font-weight: normal; color: var(--text-muted);">Menit</small></div>
                            <small class="text-muted" style="font-size: 0.7rem;">420 Menit × <span id="calcJmlAuditor">1</span> Auditor</small>
                        </div>
                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 0.75rem 1rem;">
                            <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Potongan KKA Fisik Hari Ini</div>
                            <div style="font-size: 1.25rem; font-weight: 700; color: #d97706;" id="calcPotonganMingguan">0 <small style="font-size: 0.75rem; font-weight: normal; color: var(--text-muted);">Menit</small></div>
                            <small class="text-muted" style="font-size: 0.7rem; text-overflow:ellipsis; overflow:hidden; white-space:nowrap; display:block;" id="calcNamaFisikHariIni">Tidak ada jadwal KKA fisik hari ini</small>
                        </div>
                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 0.75rem 1rem;">
                            <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Menit Tersedia Uji Sampel</div>
                            <div style="font-size: 1.25rem; font-weight: 700; color: #1e3a8a;" id="calcTersediaSampling">420 <small style="font-size: 0.75rem; font-weight: normal; color: var(--text-muted);">Menit</small></div>
                            <small class="text-muted" style="font-size: 0.7rem;">Max(0, Total - Potongan)</small>
                        </div>
                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 0.75rem 1rem;">
                            <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Beban Menit Seluruh Sampel</div>
                            <div style="font-size: 1.25rem; font-weight: 700; color: #4338ca;" id="calcBebanSampel">0 <small style="font-size: 0.75rem; font-weight: normal; color: var(--text-muted);">Menit</small></div>
                            <small class="text-muted" style="font-size: 0.7rem;"><span id="calcTotalSampelCount">0</span> Sampel 6 KKA Harian</small>
                        </div>
                    </div>

                    <!-- PROGRESS & SISA KAPASITAS -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.75rem;">
                            <span class="text-muted">Beban Kerja Terpakai:</span>
                            <span class="fw-bold" id="capPercentText">0% (0 / 420 Menit)</span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px; background: #e2e8f0;">
                            <div id="capProgressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%;"></div>
                        </div>
                    </div>

                    <div id="bannerSisaKapasitas" class="p-2 px-3 rounded d-flex justify-content-between align-items-center" style="background:#f0fdf4; border:1px solid #bbf7d0; font-size:0.85rem;">
                        <div>
                            <span class="text-muted">Sisa / (Lebih) Kapasitas:</span>
                            <strong id="calcSisaKapasitas" style="color: #16a34a; font-size: 1.05rem; margin-left: 0.4rem;">+420 Menit</strong>
                        </div>
                        <span id="badgeOverCapacityWarning" style="display:none; font-size:0.75rem; color:#dc2626; font-weight:700;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Perlu Eskalasi / Tambah Hari
                        </span>
                    </div>
                </div>

                <!-- SISI KANAN: JADWAL 5 KKA FISIK MINGGUAN (KONTROL_MINGGUAN) -->
                <div class="col-lg-6">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-bold" style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px;">
                            <i class="bi bi-calendar-week me-1 text-primary"></i> Penjadwalan 5 KKA Fisik Minggu Ini
                        </div>
                        <a href="{{ route('daily-register.physical-kka.index') }}" class="btn btn-sm btn-link text-decoration-none p-0" style="font-size: 0.8rem; font-weight: 600;">
                            Buka Form 5 KKA Fisik <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                    <div class="table-responsive" style="border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden;">
                        <table class="table table-sm table-hover mb-0 align-middle" style="font-size: 0.78rem; table-layout: fixed; width: 100%;">
                            <thead class="table-light" style="font-size: 0.68rem; text-transform: uppercase;">
                                <tr>
                                    <th style="width: 34%;">KKA Fisik</th>
                                    <th class="text-center" style="width: 18%;">Jadwal</th>
                                    <th class="text-center" style="width: 18%;">Tgl Efektif</th>
                                    <th class="text-center" style="width: 12%;">Beban</th>
                                    <th class="text-center" style="width: 18%;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="kontrolMingguanTableBody">
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">Memuat jadwal mingguan...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">
                        <i class="bi bi-info-circle me-1"></i> Pemeriksaan fisik lapangan minimal 1x per minggu. Data yang telah diinput otomatis bertahan (carry-forward) dalam minggu yang sama.
                    </small>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 5 SUMMARY CARDS -->
    <div class="summary-cards-wrapper" id="summaryCardsSampling">
        <div class="stat-card">
            <div class="stat-icon bg-light-gray"><i class="bi bi-file-earmark-text"></i></div>
            <div class="stat-details">
                <h6>TOTAL EXCEPTION</h6>
                <h3 id="statTotalException">0</h3>
                <p>Sampel Transaksi</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff7ed; color:#ea580c;"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-details">
                <h6>BELUM DIKERJAKAN</h6>
                <h3 id="statBelumDikerjakan" style="color: #ea580c;">0</h3>
                <p>Perlu Diuji / Backlog</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-light-blue"><i style="font-style:normal; font-weight:bold;">$</i></div>
            <div class="stat-details">
                <h6>TOTAL NOMINAL</h6>
                <h3 id="statTotalNominal">Rp 0</h3>
                <p>Nilai Transaksi Ditemukan</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-light-red"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-details">
                <h6>HIGH RISK</h6>
                <h3 id="statHighRisk" style="color: #dc2626;">0</h3>
                <p>Perlu Atensi Khusus</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-light-green"><i class="bi bi-check2-circle"></i></div>
            <div class="stat-details">
                <h6>SELESAI REVIEW</h6>
                <h3 id="statSelesaiReview">0</h3>
                <p>Sudah Ditindaklanjuti</p>
            </div>
        </div>
    </div>

    <!-- ALERT BACKLOG KEHADIRAN RA (Transaksi Tertunda Wajib Diperiksa) -->
    <div id="backlogAlertBanner" class="alert alert-warning mb-3 shadow-sm border" style="display: none; border-left: 5px solid #f59e0b; border-radius: 8px; background: #fffbeb;">
        <div class="d-flex align-items-center gap-3">
            <span style="font-size: 1.5rem; color: #d97706;"><i class="bi bi-exclamation-triangle-fill"></i></span>
            <div>
                <strong style="color: #92400e;">Pemberitahuan Backlog Kehadiran RA:</strong>
                <span id="backlogAlertText" style="color: #78350f;">Terdapat transaksi backlog dari tanggal sebelumnya yang wajib diperiksa. Data tidak terhapus otomatis.</span>
            </div>
        </div>
    </div>

    <!-- FILTER KHUSUS RIWAYAT (Hanya tampil saat mode riwayat aktif) -->
    <div id="riwayatFilterCard" class="filter-card" style="display: none;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div style="font-weight: 700; color: var(--primary-blue); font-size: 0.95rem;">
                <i class="bi bi-archive-fill me-1"></i> Arsip Riwayat Selesai — Matriks KKA Harian
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="#" id="btnPrintArsipMatriks" target="_blank" class="btn btn-sm btn-primary px-3 fw-semibold shadow-sm" style="border-radius: 6px; font-size: 0.82rem;">
                    <i class="bi bi-printer me-1"></i> Cetak Arsip Matriks (Print / PDF)
                </a>
                <a href="#" id="btnExportExcelArsipMatriks" class="btn btn-sm btn-success px-3 fw-semibold shadow-sm" style="border-radius: 6px; font-size: 0.82rem;">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel Matriks
                </a>
            </div>
        </div>
        <div class="filter-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
            <div>
                <label class="filter-label">Cari Kata Kunci / No Referensi / No. Arsip</label>
                <input type="text" id="filterKeyword" class="filter-control" placeholder="Ketik kata kunci...">
            </div>
            <div>
                <label class="filter-label">Grup Sub-Area Checklist</label>
                <select id="filterRiwayatSubArea" class="filter-control" onchange="applyCustomFilter()">
                    <option value="">-- Semua Sub-Area --</option>
                </select>
            </div>
            <div>
                <label class="filter-label">Status Klarifikasi</label>
                <select id="filterKlarifikasi" class="filter-control">
                    <option value="">-- Semua Status --</option>
                    <option value="Sesuai / Selesai">Sesuai / Selesai</option>
                    <option value="Belum Sesuai">Belum Sesuai</option>
                    <option value="Tidak Ada Klarifikasi">Tidak Ada Klarifikasi</option>
                </select>
            </div>
            <div class="d-flex align-items-end gap-2">
                <button type="button" class="btn-filter" onclick="applyCustomFilter()"><i class="bi bi-search"></i> Filter</button>
                <button type="button" class="btn-reset" onclick="resetCustomFilter()">Reset</button>
            </div>
        </div>
    </div>

    <div class="kka-tabs" id="kkaTabContainer">
        @if(!$isSubmenu)
        <div class="kka-tab-item active" onclick="switchTab(this, 'KKA_Teller_Kas', 'KKA Teller &amp; Kas')">
            <span class="tab-dot dot-red" id="dot_revisi_KKA_Teller_Kas" style="display:none;" title="Ada KKA Perlu Revisi"></span>
            <span class="tab-dot dot-amber" id="dot_menunggu_KKA_Teller_Kas" style="display:none;" title="Ada KKA Menunggu Konfirmasi"></span>
            <i class="bi bi-cash-stack"></i> KKA Teller &amp; Kas <span class="badge-count tab-teller" id="tab_badge_KKA_Teller_Kas">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA_DPK_Layanan', 'KKA DPK &amp; Layanan Nasabah')">
            <span class="tab-dot dot-red" id="dot_revisi_KKA_DPK_Layanan" style="display:none;" title="Ada KKA Perlu Revisi"></span>
            <span class="tab-dot dot-amber" id="dot_menunggu_KKA_DPK_Layanan" style="display:none;" title="Ada KKA Menunggu Konfirmasi"></span>
            <i class="bi bi-wallet2"></i> KKA DPK &amp; Layanan <span class="badge-count tab-dpk" id="tab_badge_KKA_DPK_Layanan">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA_Kredit', 'KKA Kredit')">
            <span class="tab-dot dot-red" id="dot_revisi_KKA_Kredit" style="display:none;" title="Ada KKA Perlu Revisi"></span>
            <span class="tab-dot dot-amber" id="dot_menunggu_KKA_Kredit" style="display:none;" title="Ada KKA Menunggu Konfirmasi"></span>
            <i class="bi bi-credit-card-2-front"></i> KKA Kredit <span class="badge-count tab-kredit" id="tab_badge_KKA_Kredit">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA_Biaya_Beban', 'KKA Biaya &amp; Beban')">
            <span class="tab-dot dot-red" id="dot_revisi_KKA_Biaya_Beban" style="display:none;" title="Ada KKA Perlu Revisi"></span>
            <span class="tab-dot dot-amber" id="dot_menunggu_KKA_Biaya_Beban" style="display:none;" title="Ada KKA Menunggu Konfirmasi"></span>
            <i class="bi bi-receipt"></i> KKA Biaya &amp; Beban <span class="badge-count tab-biaya" id="tab_badge_KKA_Biaya_Beban">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA_Kartu_ATM', 'KKA Kartu ATM')">
            <span class="tab-dot dot-red" id="dot_revisi_KKA_Kartu_ATM" style="display:none;" title="Ada KKA Perlu Revisi"></span>
            <span class="tab-dot dot-amber" id="dot_menunggu_KKA_Kartu_ATM" style="display:none;" title="Ada KKA Menunggu Konfirmasi"></span>
            <i class="bi bi-credit-card"></i> KKA Kartu ATM <span class="badge-count tab-atm" id="tab_badge_KKA_Kartu_ATM">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA_Pengaduan', 'KKA Pengaduan')">
            <span class="tab-dot dot-red" id="dot_revisi_KKA_Pengaduan" style="display:none;" title="Ada KKA Perlu Revisi"></span>
            <span class="tab-dot dot-amber" id="dot_menunggu_KKA_Pengaduan" style="display:none;" title="Ada KKA Menunggu Konfirmasi"></span>
            <i class="bi bi-chat-square-dots"></i> KKA Pengaduan <span class="badge-count tab-pengaduan" id="tab_badge_KKA_Pengaduan">0</span>
        </div>
        <div class="kka-tab-item tab-control-integrasi" id="tabControlIntegrasi" onclick="switchTabControlIntegrasi(this)">
            <i class="bi bi-diagram-3-fill"></i> 
            <span>KKA Control &amp; Integrasi</span> 
            <span class="badge-count" id="badgeTabControlStatus">11 Area</span>
        </div>
        @else
        <div class="kka-tab-item active">
            <i class="bi bi-folder-check"></i> {{ $defaultTabName }} <span class="badge-count">0</span>
        </div>
        @endif
    </div>

    <!-- WRAPPER 1: TABEL UTAMA SAMPLING KKA -->
    <div id="wrapperSamplingKka">
        <div class="card" style="border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; background:#fff;">
            <div style="padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem;">
                <div class="d-flex align-items-center gap-2">
                    <div style="font-weight: 700; color: var(--primary-blue); font-size: 0.95rem;" id="tableTitle">KKA Teller & Kas</div>
                    <div class="badge" style="background:#e0e7ff; color:var(--primary-blue); font-size:0.75rem; padding: 0.35rem 0.75rem; border-radius: 12px; font-weight:700;">
                        <span id="totalDataCount">0</span> Data
                    </div>
                    <button type="button" class="btn btn-sm btn-primary fw-semibold shadow-sm" id="btnTambahMandiriKas" style="border-radius:6px; font-size:0.78rem; padding: 0.28rem 0.75rem; {{ in_array($defaultTab, ['KKA_Teller_Kas', 'KKA_Teller']) ? '' : 'display:none;' }}" onclick="openModalTambahMandiri()">
                        <i class="bi bi-plus-circle me-1"></i> + Tambah Pemeriksaan Kas / Kas Kecil
                    </button>
                </div>

                <!-- QUICK STATUS FILTER CHIPS (Filter 1-Klik dengan Hitungan Angka Dinamis) -->
                <div class="d-flex align-items-center gap-1.5 flex-wrap" id="quickFilterChips">
                    <button type="button" class="quick-chip active" id="chipSemua" onclick="setQuickStatusFilter('ALL')">
                        Semua (<span id="chipCountSemua">0</span>)
                    </button>
                    <button type="button" class="quick-chip chip-revisi" id="chipRevisi" onclick="setQuickStatusFilter('PERLU_REVISI')">
                        <span class="chip-dot dot-red"></span> Perlu Revisi (<span id="chipCountRevisi">0</span>)
                    </button>
                    <button type="button" class="quick-chip chip-belum" id="chipBelum" onclick="setQuickStatusFilter('BELUM_DIUJI')">
                        <span class="chip-dot dot-gray"></span> Belum Diuji (<span id="chipCountBelum">0</span>)
                    </button>
                    <button type="button" class="quick-chip chip-menunggu" id="chipMenunggu" onclick="setQuickStatusFilter('MENUNGGU_KONFIRMASI')">
                        <span class="chip-dot dot-amber"></span> Menunggu Konfirmasi (<span id="chipCountMenunggu">0</span>)
                    </button>
                    <button type="button" class="quick-chip chip-selesai" id="chipSelesai" onclick="setQuickStatusFilter('SELESAI')">
                        <span class="chip-dot dot-green"></span> Selesai (<span id="chipCountSelesai">0</span>)
                    </button>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 4%;">NO</th>
                            <th style="text-align: left; width: 8%;">TANGGAL</th>
                            <th style="text-align: center; width: 5%;">UNIT</th>
                            <th style="text-align: left; width: 11%;">NO REFERENSI</th>
                            <th style="text-align: left; width: 10%;">USER / MAKER</th>
                            <th style="text-align: center; width: 6%;">KODE TRX</th>
                            <th style="text-align: right; width: 11%;">NOMINAL</th>
                            <th style="text-align: left; width: 18%;">DESKRIPSI / NARASI</th>
                            <th style="text-align: center; width: 8%;">RISK</th>
                            <th style="text-align: center; width: 10%;">STATUS</th>
                            <th style="text-align: center; width: 9%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="kkaTableBody">
                        <tr>
                            <td colspan="11">
                                <div class="empty-state" style="text-align: center; padding: 3rem 0; color: var(--text-muted);">
                                    <i class="bi bi-hourglass-split" style="font-size: 2rem; display: block; margin-bottom: 1rem;"></i>
                                    <p>Memuat data KKA...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- WRAPPER 2: PANEL KKA CONTROL & INTEGRASI (11 AREA REVIEW & KESIAPAN TAHAP 5 - SOP 04.2) -->
    <div id="wrapperControlIntegrasi" style="display: none;">
        <!-- 1. HEADER SUMMARY CARDS TAHAP 4 & KESIAPAN TAHAP 5 -->
        <div class="ci-summary-cards">
            <div class="ci-stat-card">
                <div class="stat-icon" style="background:#eff6ff; color:#1e40af;">
                    <i class="bi bi-collection"></i>
                </div>
                <div class="stat-details">
                    <h6>KKA DALAM LINGKUP</h6>
                    <h3 id="ciDalamLingkup">11</h3>
                    <p>Standar SOP (6 Sampling + 5 Fisik)</p>
                </div>
            </div>
            <div class="ci-stat-card">
                <div class="stat-icon" style="background:#f8fafc; color:#475569;">
                    <i class="bi bi-folder-check"></i>
                </div>
                <div class="stat-details">
                    <h6>KKA TERSEDIA</h6>
                    <h3 id="ciTersedia">0</h3>
                    <p>Terbentuk di Sistem</p>
                </div>
            </div>
            <div class="ci-stat-card">
                <div class="stat-icon" style="background:#f0fdf4; color:#16a34a;">
                    <i class="bi bi-check2-all"></i>
                </div>
                <div class="stat-details">
                    <h6>KKA SELESAI</h6>
                    <h3 id="ciSelesai">0</h3>
                    <p>Pengujian Valid 100%</p>
                </div>
            </div>
            <div class="ci-stat-card">
                <div class="stat-icon" style="background:#fefce8; color:#ca8a04;">
                    <i class="bi bi-speedometer2"></i>
                </div>
                <div class="stat-details">
                    <h6>STATUS TAHAP 4 (KKA_CONTROL)</h6>
                    <div class="mt-1">
                        <span id="ciStatusTahap4Badge" class="badge bg-warning text-dark px-2 py-1" style="font-size:0.75rem;">
                            KKA BELUM SELESAI
                        </span>
                    </div>
                    <p class="mt-1">Penyelesaian 11 Area</p>
                </div>
            </div>
            <div class="ci-stat-card">
                <div class="stat-icon" style="background:#fef2f2; color:#ef4444;">
                    <i class="bi bi-arrow-right-circle"></i>
                </div>
                <div class="stat-details">
                    <h6>KESIAPAN TAHAP 5 (KKA_INTEGRASI)</h6>
                    <div class="mt-1">
                        <span id="ciKesiapanTahap5Badge" class="badge bg-danger text-white px-2 py-1" style="font-size:0.75rem;">
                            BELUM - SELESAIKAN KKA
                        </span>
                    </div>
                    <p class="mt-1">Menuju Register Tahap 5</p>
                </div>
            </div>
        </div>

        <!-- 2. TABEL INDEKS 11 AREA KKA (KKA_CONTROL & KKA_INTEGRASI) -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color) !important; background: #fff;">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-bottom: 1px solid var(--border-color);">
                <div class="d-flex align-items-center gap-2">
                    <span style="width: 38px; height: 38px; border-radius: 8px; background: #eff6ff; color: #1e3a8a; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="bi bi-table"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-bold" style="color: var(--primary-blue); font-size: 0.95rem;">
                            Indeks Rekonsiliasi 11 Area KKA (KKA_Control &amp; KKA_Integrasi)
                        </h6>
                        <small class="text-muted" style="font-size: 0.73rem;">
                            Konsolidasi 6 KKA Sampling Harian &amp; 5 KKA Pemeriksaan Langsung Mingguan sesuai SOP 04.2 (100% Otomatis / Read-Only).
                        </small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border px-3 py-2" style="font-size: 0.75rem;">
                        <i class="bi bi-geo-alt me-1 text-primary"></i> Unit: <strong id="ciUnitText">-</strong>
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadControlIntegrasi()" style="border-radius: 8px; font-size: 0.75rem;">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
                    </button>
                </div>
            </div>

            <div class="table-wrapper" style="overflow-x: hidden; width: 100%;">
                <table class="table-control-integrasi">
                    <thead>
                        <tr style="border-bottom: 1px solid #cbd5e1;">
                            <th colspan="6" style="background: #eff6ff; color: #1e3a8a; text-align: left; padding: 0.55rem 0.75rem; font-size: 0.72rem; font-weight: 700; border-right: 2px solid #cbd5e1;">
                                <i class="bi bi-card-checklist me-1"></i> I. INDEKS AREA REVIEW &amp; STATUS PENGUJIAN 11 AREA (SOP 04.2)
                            </th>
                            <th colspan="1" style="background: #f8fafc; color: #334155; text-align: center; padding: 0.55rem 0.5rem; font-size: 0.72rem; font-weight: 700; border-right: 2px solid #cbd5e1;">
                                <i class="bi bi-bar-chart me-1"></i> II. SAMPLING (SOP 02)
                            </th>
                            <th colspan="1" style="background: #eff6ff; color: #1e3a8a; text-align: center; padding: 0.55rem 0.5rem; font-size: 0.72rem; font-weight: 700;">
                                <i class="bi bi-arrow-right-circle me-1"></i> III. TAHAP 5
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 3.5%; text-align: center;">NO</th>
                            <th style="width: 22%; text-align: left;">AREA REVIEW &amp; SHEET</th>
                            <th style="width: 12%; text-align: center;">STATUS KKA</th>
                            <th style="width: 11%; text-align: center;">SAMPEL &amp; UJI</th>
                            <th style="width: 13%; text-align: center;">EXCEPTION &amp; RISIKO</th>
                            <th style="width: 18%; text-align: left; border-right: 2px solid #cbd5e1;">KONTROL KHAS AREA</th>
                            <th style="width: 11%; text-align: center; border-right: 2px solid #cbd5e1;">PARAMETER SAMPLING</th>
                            <th style="width: 9.5%; text-align: center;">TINDAK LANJUT</th>
                        </tr>
                    </thead>
                    <tbody id="ciTableBody">
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                Memuat rekapitulasi 11 Area KKA...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light py-2 px-3 d-flex justify-content-between align-items-center flex-wrap" style="border-top: 1px solid var(--border-color); font-size: 0.72rem; color: var(--text-muted);">
                <span><i class="bi bi-info-circle me-1"></i> Baris 1–6 adalah KKA Sampling Harian. Baris 7–11 adalah Pemeriksaan Langsung Mingguan (kolom 15–20 ditandai strip '-' karena bukan area sampling).</span>
                <span>Standar: <strong>WP_SOP_04_2_DAILY_REVIEW_v1.xlsx</strong></span>
            </div>
        </div>

        <!-- 3. PANEL BAWAH: INTEGRASI TAHAP 5 (REGISTER KLARIFIKASI & TEMUAN) -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color) !important; background: #fff;">
            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: #fff;">
                <div class="d-flex align-items-center gap-3">
                    <span style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.15); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                        <i class="bi bi-diagram-3"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-bold" style="font-size: 0.95rem; color: #fff;">
                            Tahap 5 – Integrasi Register Klarifikasi &amp; Register Temuan
                        </h6>
                        <small style="opacity: 0.85; font-size: 0.73rem;">
                            KKA_Integrasi Baris 24–29: Exception KKA dikelola secara terpusat; hanya temuan final dan valid yang diteruskan ke laporan SKAI.
                        </small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="ciStatusTahap5Badge" class="badge bg-warning text-dark px-3 py-2" style="font-size: 0.78rem; font-weight: 700;">
                        SELESAIKAN KLARIFIKASI / TEMUAN
                    </span>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 10px; padding: 1rem; border-left: 4px solid #ef4444 !important;">
                            <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Perlu Klarifikasi</div>
                            <div style="font-size: 1.4rem; font-weight: 700; color: #dc2626;" id="ciPerluKlarifikasi">0</div>
                            <small class="text-muted" style="font-size: 0.7rem;">Wajib diklarifikasi ke cabang</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 10px; padding: 1rem; border-left: 4px solid #10b981 !important;">
                            <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Klarifikasi Selesai</div>
                            <div style="font-size: 1.4rem; font-weight: 700; color: #16a34a;" id="ciKlarifikasiSelesai">0</div>
                            <small class="text-muted" style="font-size: 0.7rem;">Jawaban dinilai memadai</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 10px; padding: 1rem; border-left: 4px solid #f59e0b !important;">
                            <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Eligible Temuan</div>
                            <div style="font-size: 1.4rem; font-weight: 700; color: #d97706;" id="ciEligibleTemuan">0</div>
                            <small class="text-muted" style="font-size: 0.7rem;">Diputuskan menjadi temuan</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 10px; padding: 1rem; border-left: 4px solid #6366f1 !important;">
                            <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Temuan Valid</div>
                            <div style="font-size: 1.4rem; font-weight: 700; color: #4f46e5;" id="ciTemuanValid">0</div>
                            <small class="text-muted" style="font-size: 0.7rem;">Disetujui Reviewer / Final</small>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-3" style="border-top: 1px solid var(--border-color);">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">
                            Status Tahap 5 otomatis berubah menjadi <strong>SIAP LAPORAN</strong> apabila seluruh 11 KKA selesai dan tidak ada klarifikasi atau temuan yang pending.
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('daily-register.klarifikasi.index') }}" class="btn btn-sm btn-primary px-3 py-2" style="border-radius: 8px; font-weight: 600; font-size: 0.8rem;">
                            <i class="bi bi-chat-left-text me-1"></i> Buka Register Klarifikasi
                        </a>
                        <a href="{{ route('daily-register.physical-kka.index') }}" class="btn btn-sm btn-outline-primary px-3 py-2" style="border-radius: 8px; font-weight: 600; font-size: 0.8rem;">
                            <i class="bi bi-clipboard2-check me-1"></i> Form 5 KKA Fisik
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ============================================== -->
<!-- 2. FULL PAGE DETAIL VIEW -->
<!-- ============================================== -->
<div id="detailView" style="display: none;">
    
    <!-- HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
        <div style="display: flex; gap: 1.5rem;">
            <button class="btn-back" onclick="closeDetailView()">
                <i class="bi bi-arrow-left"></i> Kembali ke Sheet KKA
            </button>
            <div>
                <h4 style="margin:0; font-weight:700; color:var(--primary-blue); font-size:1.3rem;">Review KKA — <span id="header_nama_sheet">KKA Transaksi Umum</span></h4>
                <div style="font-size:0.85rem; color:var(--text-muted); margin-top:0.3rem;">
                    Object ID: <span id="head_staging_id">-</span> <span style="margin:0 0.5rem;">|</span> Case ID: -
                </div>
            </div>
        </div>
        <div style="font-size:0.85rem; font-weight:600; color:var(--text-muted);" id="badge_status_atas">Belum Di-review</div>
    </div>

    <!-- SUMMARY BOX -->
    <div class="summary-box">
        <div class="custom-grid-5">
            <div>
                <div class="sum-label">TANGGAL DATA</div>
                <div class="sum-val" id="sum_tanggal">-</div>
            </div>
            <div>
                <div class="sum-label">KODE UNIT</div>
                <div class="sum-val" id="sum_unit">-</div>
            </div>
            <div>
                <div class="sum-label">USER / MAKER</div>
                <div class="sum-val" id="sum_user">-</div>
            </div>
            <div>
                <div class="sum-label">NOMINAL</div>
                <div class="sum-val red" id="sum_nominal">-</div>
            </div>
            <div>
                <div class="sum-label">RISK LEVEL AWAL</div>
                <div id="sum_risk_badge">-</div>
            </div>
        </div>
        <div style="border-top: 1px solid var(--border-color); padding-top: 0.8rem; font-size:0.9rem;">
            <strong style="color:var(--text-dark);">Deskripsi:</strong> <span id="sum_deskripsi">-</span>
        </div>
    </div>

    <!-- SECTION 1: SYSTEM GENERATED -->
    <div class="section-card">
        <div class="section-card-header">
            <i class="bi bi-cpu" style="color:#64748b;"></i> 1. Informasi Temuan &amp; Prosedur Uji <span style="font-weight:normal; font-size:0.8rem; color:var(--text-muted); margin-left:0.5rem;">(System Generated)</span>
        </div>
        <div class="section-card-body">
            <div class="custom-grid-4">
                <div>
                    <div class="sum-label">STAGING ID</div>
                    <div class="sum-val" id="sys_offsite_id">-</div>
                </div>
                <div>
                    <div class="sum-label">TANGGAL DATA</div>
                    <div class="sum-val" id="sys_tanggal_data">-</div>
                </div>
                <div>
                    <div class="sum-label">UNIT KERJA</div>
                    <div class="sum-val" id="sys_unit_kerja">-</div>
                </div>
                <div>
                    <div class="sum-label">NOMINAL TERKAIT</div>
                    <div class="sum-val red" id="sys_nominal_terkait">-</div>
                </div>
            </div>
            
            <div class="custom-grid-3">
                <div>
                    <div class="sum-label">SUMBER SHEET</div>
                    <div><span style="background:#eff6ff; color:#3b82f6; border:1px solid #bfdbfe; font-size:0.8rem; font-weight:600; padding:0.3rem 0.6rem; border-radius:12px;" id="sys_source_sheet">-</span></div>
                </div>
                <div>
                    <div class="sum-label">RISK AWAL ENGINE</div>
                    <div id="sys_risk_awal">-</div>
                </div>
                <div>
                    <div class="sum-label">JENIS EXCEPTION AWAL</div>
                    <div class="sum-val" id="sys_exception_awal">-</div>
                </div>
            </div>

            <hr style="border-color:var(--border-color); margin-bottom: 1.5rem;">

            <div class="custom-grid-2" style="margin-bottom:0;">
                <div>
                    <div class="sum-label">DESKRIPSI</div>
                    <div style="font-size:0.9rem; color:var(--text-dark);" id="sys_catatan_rule">-</div>
                </div>
                <div>
                    <div class="sum-label">PROSEDUR UJI AUDIT</div>
                    <div style="font-size:0.9rem; color:var(--text-dark);" id="sys_prosedur_uji">-</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ALERT BOX REVISI (Tampil Otomatis saat Status = PERLU_REVISI) -->
    <div id="alertBoxRevisi" class="mb-4 p-4 rounded shadow-sm" style="display: none; background: #fff1f2; border-left: 5px solid #f43f5e; border-top: 1px solid #ffe4e6; border-right: 1px solid #ffe4e6; border-bottom: 1px solid #ffe4e6;">
        <div class="d-flex align-items-start gap-3">
            <span style="width: 44px; height: 44px; border-radius: 10px; background: #fee2e2; color: #e11d48; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                <i class="bi bi-exclamation-octagon-fill"></i>
            </span>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                    <h6 class="mb-0 fw-bold" style="color: #9f1239; font-size: 0.95rem;">
                        <i class="bi bi-arrow-repeat me-1"></i> Catatan Revisi dari Reviewer (Wajib Diperbaiki)
                    </h6>
                    <div class="d-flex align-items-center gap-2" style="font-size: 0.78rem; color: #be123c;">
                        <span><i class="bi bi-person-badge me-1"></i> Reviewer: <strong id="alert_reviewer_name">-</strong></span>
                        <span>•</span>
                        <span><i class="bi bi-clock-history me-1"></i> <span id="alert_reviewed_at">-</span></span>
                    </div>
                </div>
                <div class="p-3 rounded mt-2" style="background: #ffffff; border: 1px solid #fecdd3; font-size: 0.88rem; color: #881337; line-height: 1.5;" id="alert_reviewer_note">
                    -
                </div>
                <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                    <i class="bi bi-info-circle me-1"></i> Silakan sesuaikan isian checklist atau rincian klarifikasi di bawah ini, lalu klik tombol <strong>"Simpan Perbaikan"</strong> untuk mengajukan kembali ke Reviewer.
                </div>
            </div>
        </div>
    </div>

    <!-- INFO BOX RIWAYAT REVISI (Tampil saat status MENUNGGU_KONFIRMASI atau SELESAI jika ada riwayat revisi) -->
    <div id="infoBoxRiwayatRevisi" class="mb-4 p-3 rounded shadow-sm" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #64748b;">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="fw-bold" style="font-size: 0.8rem; color: #334155;">
                <i class="bi bi-clock-history me-1 text-secondary"></i> Riwayat Catatan Revisi Sebelumnya
            </span>
            <span style="font-size: 0.72rem; color: #64748b;">
                Oleh: <strong id="hist_reviewer_name">-</strong> (<span id="hist_reviewed_at">-</span>)
            </span>
        </div>
        <div style="font-size: 0.82rem; color: #475569; background: #fff; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #e2e8f0;" id="hist_reviewer_note">
            -
        </div>
    </div>

    <!-- SECTION 2: INPUTAN RESIDENT AUDITOR (RA) -->
    <div class="section-card ra">
        <div class="section-card-header" style="justify-content: space-between;">
            <div><i class="bi bi-pencil-square"></i> 2. Hasil Pengujian &amp; Klarifikasi <span style="font-weight:normal; font-size:0.8rem; margin-left:0.3rem;">(Porsi Resident Auditor)</span></div>
            <span id="badge_ra_lock" style="font-size:0.75rem; background:#fff; padding:0.2rem 0.6rem; border-radius:12px; border:1px solid #fde68a; color:var(--text-muted);">Mode Edit</span>
        </div>
        <div class="section-card-body">
            <form id="formKkaRa" enctype="multipart/form-data">
                <input type="hidden" id="ra_kka_id">

                <!-- NO. ARSIP WAJIB (BERLAKU UNTUK SEMUA AREA KKA) & PARAMETER PENGADUAN -->
                <div class="row g-3 mb-3 p-3 rounded" style="background:#f8fafc; border:1px solid #e2e8f0;">
                    <div class="col-md-6">
                        <label class="custom-form-label fw-bold text-primary">
                            <i class="bi bi-archive-fill me-1"></i> No. Arsip KKA <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="custom-form-control field-ra" id="no_arsip" placeholder="Misal: ARSIP/2026/001 atau No. Box/Map Fisik" required>
                        <small class="text-muted" style="font-size:0.72rem;">Nomor arsip wajib diisi sebagai identifikasi fisik berkas/folder pada laporan Arsip Matriks.</small>
                    </div>
                    <div class="col-md-6" id="containerPengaduanFields" style="display:none;">
                        <div class="d-flex align-items-center mb-1 text-primary fw-bold" style="font-size:0.8rem;">
                            <i class="bi bi-chat-square-text me-1"></i> Parameter Keluhan Nasabah (E.4.5)
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="custom-form-label" style="font-size:0.72rem;">Tgl Keluhan</label>
                                <input type="date" class="custom-form-control field-ra" id="tgl_keluhan" onchange="calculatePengaduanHariKerja()">
                            </div>
                            <div class="col-md-4">
                                <label class="custom-form-label" style="font-size:0.72rem;">Tgl Buku</label>
                                <input type="date" class="custom-form-control field-ra" id="tgl_buku" onchange="calculatePengaduanHariKerja()">
                            </div>
                            <div class="col-md-4">
                                <label class="custom-form-label" style="font-size:0.72rem;">Status Keluhan</label>
                                <select class="custom-form-control field-ra" id="status_keluhan" onchange="calculatePengaduanHariKerja()">
                                    <option value="Proses">Proses</option>
                                    <option value="Selesai">Selesai</option>
                                    <option value="REJECT">REJECT</option>
                                </select>
                            </div>
                            <div class="col-12 mt-1">
                                <div class="p-2 rounded d-flex justify-content-between align-items-center" style="background:#eff6ff; border:1px solid #bfdbfe; font-size:0.78rem;">
                                    <span>Lama Hari Kerja (NETWORKDAYS):</span>
                                    <strong id="display_lama_hari_kerja" class="text-primary">-</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 1. TABEL / ACCORDION CHECKLIST KKA (YA / TIDAK) -->
                <div id="checklistAccordionContainer" style="margin-bottom: 1.5rem;">
                    <div class="text-center py-3 text-muted" style="font-size:0.85rem;">
                        <i class="bi bi-hourglass-split me-1"></i> Memuat daftar checklist pengujian...
                    </div>
                </div>

                <!-- 2. KEMUNCULAN KONDISIONAL (HANYA MUNCUL JIKA MINIMAL 1 CHECKLIST TIDAK) -->
                <div id="containerExceptionFields" class="p-3 mb-3" style="display:none; background:#fff1f2; border:1px solid #fecdd3; border-radius:8px;">
                    <div class="d-flex align-items-center mb-2" style="color:#be123c; font-weight:700; font-size:0.85rem;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Temuan Pengujian Lapangan Terdeteksi (Checklist Bernilai "Tidak")
                    </div>
                    <div class="custom-grid-2 mb-2">
                        <div>
                            <label class="custom-form-label">Jenis Exception (Versi RA) <span class="text-danger">*</span></label>
                            <input type="text" class="custom-form-control field-ra" id="jenis_exception_ra" placeholder="Klasifikasi penyimpangan hasil pengujian...">
                        </div>
                        <div>
                            <label class="custom-form-label">Klarifikasi Unit Kerja / Cabang <span class="text-danger">*</span></label>
                            <textarea class="custom-form-control field-ra" id="klarifikasi_unit" rows="2" placeholder="Tanggapan dari pihak cabang..."></textarea>
                        </div>
                    </div>
                    <div>
                        <label class="custom-form-label">Hasil Pengujian Lapangan <span class="text-danger">*</span></label>
                        <textarea class="custom-form-control field-ra" id="hasil_uji" rows="3" placeholder="Rincian fakta temuan hasil konfirmasi &amp; pengujian lapangan..."></textarea>
                    </div>
                </div>

                <!-- 3. FIELD BUKTI, CRITICAL TRIGGER & TANGGAL TERKUNCI -->
                <div class="custom-grid-3 mb-3">
                    <div>
                        <label class="custom-form-label">Bukti / Referensi Dokumen</label>
                        <input type="text" class="custom-form-control field-ra" id="bukti_referensi" placeholder="Misal: Surat No. 123/LHA/2026">
                    </div>
                    <div>
                        <label class="custom-form-label">Critical Trigger</label>
                        <select class="custom-form-control field-ra" id="critical_trigger" onchange="onChecklistChange()">
                            <option value="Tidak">Tidak</option>
                            <option value="Ya">Ya (Otomatis Critical/High)</option>
                        </select>
                    </div>
                    <div>
                        <label class="custom-form-label">Upload Bukti Transaksi (PDF)</label>
                        <input type="file" class="custom-form-control field-ra" id="file_bukti" accept=".pdf">
                        <div id="file_bukti_info" style="font-size:0.72rem; color:var(--text-muted); margin-top:0.2rem;"></div>
                    </div>
                </div>

                <div class="custom-grid-2 mb-3">
                    <div>
                        <label class="custom-form-label">Tanggal Ditemukan / Diuji <i class="bi bi-lock-fill text-muted" title="Terkunci otomatis pada tanggal RA membuka KKA"></i></label>
                        <input type="date" class="custom-form-control" id="tanggal_ditemukan" readonly style="background:#f1f5f9; cursor:not-allowed;">
                    </div>
                    <div>
                        <label class="custom-form-label">Perlu Onsite Inspection? <i class="bi bi-lock-fill text-muted" title="Otomatis terhitung dari skor risiko"></i></label>
                        <input type="hidden" id="perlu_onsite" value="Tidak Perlu">
                        <div class="custom-form-control" style="background:#f8fafc; font-weight:600; color:var(--text-dark); height: calc(1.5em + 1.2rem + 2px); display:flex; align-items:center;" id="display_perlu_onsite">Tidak Perlu</div>
                    </div>
                </div>

                <!-- 4. OTOMASI PENUH & PENGUNCIAN (READ-ONLY) SKOR RISIKO -->
                <div class="custom-grid-3 mb-3">
                    <div>
                        <label class="custom-form-label">Skor Dampak (1–5) <i class="bi bi-lock-fill text-muted" title="Terkunci otomatis matriks 5x5"></i></label>
                        <input type="hidden" id="skor_dampak" value="1">
                        <div class="custom-form-control" style="background:#f8fafc; font-weight:600; color:var(--text-dark);" id="display_skor_dampak">1 — Sangat Rendah</div>
                    </div>
                    <div>
                        <label class="custom-form-label">Skor Kemungkinan (1–5) <i class="bi bi-lock-fill text-muted" title="Terkunci otomatis berbasis kejadian berulang"></i></label>
                        <input type="hidden" id="skor_kemungkinan" value="1">
                        <div class="custom-form-control" style="background:#f8fafc; font-weight:600; color:var(--text-dark);" id="display_skor_kemungkinan">1 — Sangat Jarang</div>
                    </div>
                    <div>
                        <label class="custom-form-label">Skor &amp; Risk Final <i class="bi bi-lock-fill text-muted" title="Terkunci otomatis (Dampak x Kemungkinan)"></i></label>
                        <input type="hidden" id="skor_final" value="1">
                        <input type="hidden" id="risk_final" value="Low">
                        <div class="custom-form-control" style="background:#f8fafc; display:flex; align-items:center; gap:0.5rem; height: calc(1.5em + 1.2rem + 2px);">
                            <span style="font-weight:700; font-size:1.1rem;" id="text_skor_final">1</span> 
                            <span style="color:var(--text-muted);">—</span> 
                            <span id="badge_risk_final" style="padding:0.3rem 0.6rem; border-radius:6px; font-weight:600; font-size:0.8rem; background:#dcfce7; color:#16a34a; border:1px solid #bbf7d0;">Low</span>
                        </div>
                    </div>
                </div>

                @if(strtolower(auth()->user()->role) === 'ra' || strtolower(auth()->user()->role) === 'admin')
                <div id="raActionContainer" style="display:flex; justify-content:flex-end; align-items:center; gap:0.8rem; border-top:1px solid var(--border-color); padding-top:1rem;">
                    <span id="badge_ra_status_info"></span>
                    <button type="button" class="btn-back" onclick="closeDetailView()">Batal</button>
                    <button type="button" id="btnSubmitRa" style="background:#1e3a8a; color:#fff; border:none; padding:0.45rem 1.6rem; border-radius:6px; font-weight:600; cursor:pointer;" onclick="submitRaForm()">
                        <i class="bi bi-send-fill" id="btnSubmitRaIcon" style="margin-right:0.3rem;"></i> <span id="btnSubmitRaText">Simpan &amp; Selesaikan KKA (Masuk Arsip)</span>
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>

    <!-- SECTION 3: INPUTAN ADMIN / REVIEWER (KEPUTUSAN VERIFIKASI WORKFLOW) -->
    <div class="section-card admin">
        <div class="section-card-header" style="justify-content: space-between;">
            <div><i class="bi bi-clipboard-check"></i> 3. Review &amp; Keputusan Verifikasi Pengawasan <span style="font-weight:normal; font-size:0.8rem; margin-left:0.3rem;">(Porsi Admin / Reviewer)</span></div>
            <span id="badge_admin_lock" style="font-size:0.75rem; background:#fff; padding:0.2rem 0.6rem; border-radius:12px; border:1px solid #a7f3d0; color:var(--text-muted);">Read Only</span>
        </div>
        <div class="section-card-body">
            <form id="formKkaAdmin">
                <input type="hidden" id="admin_kka_id">

                <div class="custom-grid-3">
                    <div>
                        <label class="custom-form-label">Status Klarifikasi Unit</label>
                        <select class="custom-form-control field-admin" id="status_klarifikasi" @if(strtolower(auth()->user()->role) === 'ra') disabled @endif>
                            <option value="">-- Pilih --</option>
                            <option value="Sesuai / Selesai">Sesuai / Selesai</option>
                            <option value="Belum Sesuai">Belum Sesuai</option>
                            <option value="Tidak Ada Klarifikasi">Tidak Ada Klarifikasi</option>
                        </select>
                    </div>
                    <div>
                        <label class="custom-form-label">Perluasan Sampel?</label>
                        <select class="custom-form-control field-admin" id="perluasan_sampel" @if(strtolower(auth()->user()->role) === 'ra') disabled @endif>
                            <option value="0">Tidak Perlu</option>
                            <option value="1">Ya, Perluasan Sampel</option>
                        </select>
                    </div>
                    <div>
                        <label class="custom-form-label">Keputusan Onsite</label>
                        <select class="custom-form-control field-admin" id="keputusan_onsite" @if(strtolower(auth()->user()->role) === 'ra') disabled @endif>
                            <option value="">-- Pilih --</option>
                            <option value="Disetujui Onsite">Disetujui Onsite</option>
                            <option value="Cukup Offsite">Cukup Offsite</option>
                        </select>
                    </div>
                </div>

                <div class="custom-grid-2">
                    <div>
                        <label class="custom-form-label">Keputusan Eskalasi</label>
                        <select class="custom-form-control field-admin" id="keputusan_eskalasi" @if(strtolower(auth()->user()->role) === 'ra') disabled @endif>
                            <option value="Tidak">Tidak Dieskalasi</option>
                            <option value="Eskalasi Kadiv">Eskalasi ke Kadiv SKAI</option>
                            <option value="Eskalasi Direksi">Eskalasi ke Direksi</option>
                        </select>
                    </div>
                    <div>
                        <label class="custom-form-label">Status Workflow KKA Saat Ini <i class="bi bi-lock-fill text-muted" title="Dikelola 100% otomatis oleh sistem backend"></i></label>
                        <div id="display_workflow_status_admin" class="custom-form-control" style="background:#f8fafc; font-weight:700; height: calc(1.5em + 1.2rem + 2px); display:flex; align-items:center;">
                            -
                        </div>
                    </div>
                </div>

                <!-- PANEL STATUS KETERANGAN VERIFIKASI (JIKA BUKAN MENUNGGU KONFIRMASI) -->
                <div id="panelStatusAdminNonWaiting" class="p-3 rounded mb-3" style="display:none; background:#f8fafc; border:1px solid #e2e8f0;">
                    <div id="statusAdminNonWaitingText" style="font-size:0.83rem; font-weight:600;"></div>
                </div>

                <!-- PANEL VERIFIKASI ADMIN (Tampil Bersih di Bagian Bawah Form Khusus Status MENUNGGU_KONFIRMASI atau Mode Admin) -->
                <div id="panelVerifikasiAdmin" class="p-3 rounded mb-3" style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px;">
                    <div class="d-flex align-items-center gap-2 mb-2" style="color:var(--primary-blue); font-weight:700; font-size:0.88rem;">
                        <i class="bi bi-shield-check fs-5"></i> Panel Aksi Verifikasi Reviewer
                    </div>
                    <div class="mb-3">
                        <label class="custom-form-label">
                            Catatan Reviewer / Pimsie <span id="label_catatan_req" style="color:#dc2626; font-size:0.75rem;">(Wajib diisi jika Minta Revisi / Reject, Opsional jika Approve)</span>
                        </label>
                        <textarea class="custom-form-control field-admin" id="catatan_reviewer" rows="3" placeholder="Tuliskan alasan revisi spesifik untuk RA, atau catatan konfirmasi KKA..." @if(strtolower(auth()->user()->role) === 'ra') disabled @endif></textarea>
                    </div>

                    @if(in_array(strtolower(auth()->user()->role), ['admin', 'kabag_ra', 'kadiv_skai', 'pimsie', 'korwas']))
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2" style="border-top:1px solid #bfdbfe;">
                        <button type="button" class="btn-back" onclick="closeDetailView()">
                            <i class="bi bi-x"></i> Tutup
                        </button>
                        <div class="d-flex gap-2">
                            <!-- TOMBOL MERAH / ORANYE: MINTA REVISI / REJECT -->
                            <button type="button" id="btnAdminReject" class="btn btn-sm text-white px-3 py-2 fw-semibold shadow-sm" style="background:#e11d48; border-color:#be123c; border-radius:6px; font-size:0.82rem;" onclick="submitAdminDecision('REVISI')">
                                <i class="bi bi-arrow-repeat me-1"></i> Minta Revisi / Reject
                            </button>
                            <!-- TOMBOL HIJAU: KONFIRMASI & SELESAIKAN (APPROVE) -->
                            <button type="button" id="btnAdminApprove" class="btn btn-sm text-white px-3 py-2 fw-semibold shadow-sm" style="background:#059669; border-color:#047857; border-radius:6px; font-size:0.82rem;" onclick="submitAdminDecision('APPROVE')">
                                <i class="bi bi-check-circle-fill me-1"></i> Konfirmasi &amp; Selesaikan (Approve)
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

</div>

<!-- SCRIPT JAVASCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    const baseUrl = '{{ url("/daily-register") }}'; 
    const userRole = '{{ strtolower(trim(auth()->user()->role)) }}'; 
    const csrfToken = '{{ csrf_token() }}';
    
    let globalFindings = [];
    let currentActiveSheetCode = '{{ $defaultTab }}';
    let currentActiveSheet = '{{ $defaultTabName }}';
    let isShowingRiwayat = false;

    let currentActiveUnit = '{{ $selectedKodeUnit ?? auth()->user()->unit_code ?? "001" }}';
    let currentActiveDate = '{{ request("tanggal") ?? date("Y-m-d") }}';

    let currentItem = null;
    let currentChecklists = [];
    let currentRecurringCount = 1;
    const allMasterChecklists = @json($allChecklists ?? []);

    function changeAdminCabang(unitCode) {
        currentActiveUnit = unitCode;
        const selectElem = document.getElementById('selectAdminCabang');
        const selectedOption = selectElem ? selectElem.options[selectElem.selectedIndex] : null;
        const cabangId = selectedOption ? selectedOption.getAttribute('data-cabang') : '';

        const url = new URL(window.location.href);
        url.searchParams.set('kode_unit', unitCode);
        if (cabangId) {
            url.searchParams.set('cabang', cabangId);
        } else {
            url.searchParams.delete('cabang');
        }
        window.location.href = url.toString();
    }

    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'control_integrasi') {
            const ctrlTab = document.getElementById('tabControlIntegrasi');
            if (ctrlTab) {
                switchTabControlIntegrasi(ctrlTab);
            } else {
                loadData();
            }
        } else {
            loadData();
        }
        if (userRole !== 'ra') {
            loadKapasitasHarian();
        }
    });

    // Pindah Tab Data Sampling KKA
    function switchTab(clickedElement, sheetCode, tabName) {
        const allTabs = document.querySelectorAll('.kka-tab-item');
        allTabs.forEach(tab => tab.classList.remove('active'));

        clickedElement.classList.add('active');
        currentActiveSheetCode = sheetCode;
        currentActiveSheet = tabName;
        
        // Tampilkan section sampling, sembunyikan control integrasi
        const wrapSampling = document.getElementById('wrapperSamplingKka');
        if (wrapSampling) wrapSampling.style.display = 'block';
        const sumCards = document.getElementById('summaryCardsSampling');
        if (sumCards) sumCards.style.display = 'grid';
        const wrapCI = document.getElementById('wrapperControlIntegrasi');
        if (wrapCI) wrapCI.style.display = 'none';

        if (isShowingRiwayat) {
            document.getElementById('tableTitle').innerText = `${tabName} (Arsip Riwayat Selesai)`;
            updateRiwayatSubAreaDropdown();
        } else {
            document.getElementById('tableTitle').innerText = tabName;
        }

        const isTeller = (sheetCode === 'KKA_Teller_Kas' || sheetCode === 'KKA_Teller');
        const btnTambahMandiri = document.getElementById('btnTambahMandiriKas');
        if (btnTambahMandiri) {
            btnTambahMandiri.style.display = isTeller ? 'inline-flex' : 'none';
        }
        
        loadData();
    }

    // Pindah ke Tab KKA Control & Integrasi (SOP 04.2)
    function switchTabControlIntegrasi(clickedElement) {
        const allTabs = document.querySelectorAll('.kka-tab-item');
        allTabs.forEach(tab => tab.classList.remove('active'));

        if (clickedElement) clickedElement.classList.add('active');

        const btnTambahMandiri = document.getElementById('btnTambahMandiriKas');
        if (btnTambahMandiri) btnTambahMandiri.style.display = 'none';

        // Sembunyikan section sampling, summary cards sampling, dan riwayat filter
        const wrapSampling = document.getElementById('wrapperSamplingKka');
        if (wrapSampling) wrapSampling.style.display = 'none';
        const sumCards = document.getElementById('summaryCardsSampling');
        if (sumCards) sumCards.style.display = 'none';
        const filterCard = document.getElementById('riwayatFilterCard');
        if (filterCard) filterCard.style.display = 'none';

        // Tampilkan section control integrasi
        const wrapCI = document.getElementById('wrapperControlIntegrasi');
        if (wrapCI) wrapCI.style.display = 'block';

        loadControlIntegrasi();
    }

    let currentStatusFilter = 'ALL';

    function setQuickStatusFilter(status) {
        currentStatusFilter = status;
        document.querySelectorAll('#quickFilterChips .quick-chip').forEach(ch => ch.classList.remove('active'));
        if (status === 'ALL') {
            document.getElementById('chipSemua')?.classList.add('active');
        } else if (status === 'PERLU_REVISI') {
            document.getElementById('chipRevisi')?.classList.add('active');
        } else if (status === 'BELUM_DIUJI') {
            document.getElementById('chipBelum')?.classList.add('active');
        } else if (status === 'MENUNGGU_KONFIRMASI') {
            document.getElementById('chipMenunggu')?.classList.add('active');
        } else if (status === 'SELESAI') {
            document.getElementById('chipSelesai')?.classList.add('active');
        }
        processDataDisplay();
    }

    function getItemWorkflowStatus(item) {
        if (item.review_status) {
            return item.review_status;
        }
        if ((item.status_review || '') === 'Approved') {
            return 'SELESAI';
        }
        if ((item.status_review || '') === 'Revisi') {
            return 'PERLU_REVISI';
        }
        if (item.submitted_at) {
            return 'MENUNGGU_KONFIRMASI';
        }
        return 'BELUM_DIUJI';
    }

    // Ambil Data via API
    function loadData(page = 1) {
        const tbody = document.getElementById('kkaTableBody');
        tbody.innerHTML = `<tr><td colspan="11"><div class="empty-state" style="text-align: center; padding: 3rem 0; color: var(--text-muted);"><i class="bi bi-hourglass-split" style="font-size: 2rem; display: block; margin-bottom: 1rem;"></i><p>Memuat data...</p></div></td></tr>`;

        let url = `${baseUrl}/data?page=${page}&source_sheet=${currentActiveSheetCode}`;
        @if(request()->has('kode_unit'))
        url += `&kode_unit={{ request('kode_unit') }}`;
        @elseif(isset($selectedKodeUnit))
        url += `&kode_unit={{ $selectedKodeUnit }}`;
        @endif
        @if(request()->has('cabang'))
        url += `&cabang_id={{ request('cabang') }}`;
        @elseif(isset($selectedCabangId))
        url += `&cabang_id={{ $selectedCabangId }}`;
        @endif

        axios.get(url)
            .then(response => {
                globalFindings = response.data.data.data;
                const summary = response.data.summary || {};
                const tabCounts = summary.tab_counts || {};
                const tabStatuses = summary.tab_statuses || {};
                const currentSheetCounts = summary.current_sheet_counts || {};

                document.getElementById('totalDataCount').innerText = globalFindings.length;
                document.getElementById('statTotalException').innerText = summary.total_exception ?? globalFindings.length;
                const totalBelumAll = summary.total_belum_dikerjakan ?? Math.max(0, (summary.total_exception ?? globalFindings.length) - (summary.selesai_review ?? 0));
                if (document.getElementById('statBelumDikerjakan')) {
                    document.getElementById('statBelumDikerjakan').innerText = totalBelumAll;
                }
                document.getElementById('statTotalNominal').innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(summary.total_nominal || 0);
                document.getElementById('statHighRisk').innerText = summary.high_risk ?? 0;
                document.getElementById('statSelesaiReview').innerText = summary.selesai_review ?? 0;

                // Update Dynamic Counts on Quick Filter Chips
                const activeShStatus = tabStatuses[currentActiveSheetCode] || currentSheetCounts || {};
                const totalSh = activeShStatus.total ?? globalFindings.length;
                const perluSh = activeShStatus.perlu_revisi ?? globalFindings.filter(x => (getItemWorkflowStatus(x) === 'PERLU_REVISI')).length;
                const menungguSh = activeShStatus.menunggu_konfirmasi ?? globalFindings.filter(x => (getItemWorkflowStatus(x) === 'MENUNGGU_KONFIRMASI')).length;
                const selesaiSh = activeShStatus.selesai ?? globalFindings.filter(x => (getItemWorkflowStatus(x) === 'SELESAI')).length;
                const belumSh = activeShStatus.belum_diuji ?? Math.max(0, totalSh - perluSh - menungguSh - selesaiSh);

                if (document.getElementById('chipCountSemua')) document.getElementById('chipCountSemua').innerText = totalSh;
                if (document.getElementById('chipCountRevisi')) document.getElementById('chipCountRevisi').innerText = perluSh;
                if (document.getElementById('chipCountBelum')) document.getElementById('chipCountBelum').innerText = belumSh;
                if (document.getElementById('chipCountMenunggu')) document.getElementById('chipCountMenunggu').innerText = menungguSh;
                if (document.getElementById('chipCountSelesai')) document.getElementById('chipCountSelesai').innerText = selesaiSh;

                // Update 6 Tab Navigation Indicators & Completion Badges
                const sheetKeys = [
                    'KKA_Teller_Kas',
                    'KKA_DPK_Layanan',
                    'KKA_Kredit',
                    'KKA_Biaya_Beban',
                    'KKA_Kartu_ATM',
                    'KKA_Pengaduan'
                ];
                sheetKeys.forEach(sh => {
                    const st = tabStatuses[sh] || {};
                    const total = st.total ?? (tabCounts[sh] || 0);
                    const perluRev = st.perlu_revisi || 0;
                    const menKonf = st.menunggu_konfirmasi || 0;
                    const isAllSelesai = st.is_all_selesai || (total > 0 && st.selesai === total);

                    // Indikator Titik Merah (Red Dot) untuk Role RA
                    const dotRed = document.getElementById(`dot_revisi_${sh}`);
                    if (dotRed) {
                        dotRed.style.display = (userRole === 'ra' && perluRev > 0) ? 'inline-block' : 'none';
                    }

                    // Indikator Titik Kuning/Amber (Amber Dot) untuk Role Admin
                    const dotAmber = document.getElementById(`dot_menunggu_${sh}`);
                    if (dotAmber) {
                        dotAmber.style.display = (userRole !== 'ra' && menKonf > 0) ? 'inline-block' : 'none';
                    }

                    // Penanda Tab Tuntas 100% (✓ Hijau)
                    const badgeElem = document.getElementById(`tab_badge_${sh}`);
                    if (badgeElem) {
                        if (isAllSelesai) {
                            badgeElem.classList.add('tab-completed');
                            badgeElem.innerHTML = `<i class="bi bi-check-lg me-0.5"></i> ${total}`;
                        } else {
                            badgeElem.classList.remove('tab-completed');
                            badgeElem.innerText = total;
                        }
                    }
                });

                // Backlog Alert Banner (Pemberitahuan Transaksi Tertunda Cuti/Absen RA)
                const backlogBanner = document.getElementById('backlogAlertBanner');
                const backlogText = document.getElementById('backlogAlertText');
                if (summary.backlog_count && summary.backlog_count > 0) {
                    if (backlogBanner) backlogBanner.style.display = 'block';
                    if (backlogText) backlogText.innerHTML = `Terdapat <strong>${summary.backlog_count} transaksi backlog</strong> dari tanggal sebelumnya (cuti/absen RA) yang wajib diselesaikan. Data tidak terhapus otomatis.`;
                } else {
                    if (backlogBanner) backlogBanner.style.display = 'none';
                }

                updateRiwayatSubAreaDropdown();
                processDataDisplay();
            })
            .catch(error => {
                tbody.innerHTML = `<tr><td colspan="11"><div class="empty-state" style="text-align: center; padding: 3rem 0; color: #dc2626;"><i class="bi bi-exclamation-triangle" style="font-size: 2rem; display: block; margin-bottom: 1rem;"></i><p>Gagal memuat data.</p></div></td></tr>`;
            });
    }

    function updateRiwayatSubAreaDropdown() {
        const select = document.getElementById('filterRiwayatSubArea');
        if (!select) return;

        const currentVal = select.value;
        select.innerHTML = '<option value="">-- Semua Sub-Area --</option>';

        let sheetKey = currentActiveSheetCode;
        if (sheetKey === 'KKA_Teller' || sheetKey === 'KKA_Teller_Kas') {
            sheetKey = 'KKA_Teller_Kas';
        }

        const items = (allMasterChecklists && (allMasterChecklists[sheetKey] || allMasterChecklists['KKA_Teller'])) || [];
        const seen = new Set();
        items.forEach(chk => {
            if (chk.sub_area_code && !seen.has(chk.sub_area_code)) {
                seen.add(chk.sub_area_code);
                const opt = document.createElement('option');
                opt.value = chk.sub_area_code;
                opt.textContent = `${chk.sub_area_code} - ${chk.sub_area_name}`;
                if (chk.sub_area_code === currentVal) {
                    opt.selected = true;
                }
                select.appendChild(opt);
            }
        });
    }

    // Toggle Mode Riwayat Selesai
    function toggleRiwayatMode() {
        isShowingRiwayat = !isShowingRiwayat;
        const btn = document.getElementById('btnToggleRiwayat');
        const filterCard = document.getElementById('riwayatFilterCard');
        const btnText = document.getElementById('btnRiwayatText');
        
        if (isShowingRiwayat) {
            btn.style.background = 'var(--primary-blue)';
            btn.style.color = '#fff';
            btn.style.borderColor = 'var(--primary-blue)';
            btnText.innerText = `Kembali ke Data Utama`;
            filterCard.style.display = 'block';
            updateRiwayatSubAreaDropdown();
            
            document.getElementById('tableTitle').innerText = `${currentActiveSheet} (Arsip Riwayat Selesai)`;
        } else {
            btn.style.background = '#f1f5f9';
            btn.style.color = '#475569';
            btn.style.borderColor = 'var(--border-color)';
            btnText.innerText = `Lihat Arsip Riwayat Selesai`;
            filterCard.style.display = 'none';
            
            document.getElementById('tableTitle').innerText = currentActiveSheet;
        }
        processDataDisplay();
    }

    // Proses Data untuk Ditampilkan (Filter Quick Chips, Filter Arsip & Smart Sorting)
    function processDataDisplay() {
        let dataToRender = [...globalFindings];

        if (isShowingRiwayat) {
            // Hanya ambil yang status review-nya Approved / SELESAI
            dataToRender = dataToRender.filter(x => getItemWorkflowStatus(x) === 'SELESAI');

            // Ambil parameter filter tambahan
            const keyword = (document.getElementById('filterKeyword')?.value || '').toLowerCase();
            const subArea = document.getElementById('filterRiwayatSubArea')?.value || '';
            const klarifikasi = document.getElementById('filterKlarifikasi')?.value || '';

            if (subArea) {
                dataToRender = dataToRender.filter(x => {
                    let subs = x.selected_sub_areas;
                    if (typeof subs === 'string') {
                        try { subs = JSON.parse(subs); } catch (e) { subs = []; }
                    }
                    if (Array.isArray(subs) && subs.length > 0) {
                        return subs.includes(subArea);
                    }
                    // Fallback jika selected_sub_areas belum tersimpan: periksa jawaban checklist
                    if (Array.isArray(x.checklist_answers) && x.checklist_answers.length > 0) {
                        const answeredIds = x.checklist_answers.map(a => Number(a.checklist_id));
                        const sheetKey = (currentActiveSheetCode === 'KKA_Teller' || currentActiveSheetCode === 'KKA_Teller_Kas') ? 'KKA_Teller_Kas' : currentActiveSheetCode;
                        const matched = (allMasterChecklists[sheetKey] || []).filter(c => answeredIds.includes(Number(c.id)));
                        if (matched.some(c => c.sub_area_code === subArea)) return true;
                    }
                    // Fallback keyword deskripsi Teller
                    const desc = ((x.kategori || '') + ' ' + (x.rincian || '') + ' ' + (x.deskripsi || '') + ' ' + (x.jenis_exception_awal || '')).toLowerCase();
                    if (subArea === 'A2') return desc.includes('kas kecil');
                    if (subArea === 'A3') return desc.includes('khasanah') || desc.includes('brankas');
                    if (subArea === 'A1') return !desc.includes('kas kecil') && !desc.includes('khasanah') && !desc.includes('brankas');
                    return false;
                });
            }

            if (keyword) {
                dataToRender = dataToRender.filter(x => 
                    (x.no_arsip || '').toLowerCase().includes(keyword) ||
                    (x.no_referensi || '').toLowerCase().includes(keyword) ||
                    (x.offsite_id || '').toLowerCase().includes(keyword) ||
                    (x.user_maker || '').toLowerCase().includes(keyword) ||
                    (x.deskripsi || '').toLowerCase().includes(keyword)
                );
            }

            if (klarifikasi) {
                dataToRender = dataToRender.filter(x => (x.status_klarifikasi || '') === klarifikasi);
            }

            // Update Link Print & Export Excel Arsip Matriks
            const activeUnit = '{{ $selectedKodeUnit ?? "003" }}';
            const printBtn = document.getElementById('btnPrintArsipMatriks');
            const exportBtn = document.getElementById('btnExportExcelArsipMatriks');
            const currentSubArea = document.getElementById('filterRiwayatSubArea')?.value || '';
            const subAreaQuery = currentSubArea ? `&sub_area=${encodeURIComponent(currentSubArea)}` : '';
            if (printBtn) {
                printBtn.href = `/daily-register/arsip-matriks/print?source_sheet=${encodeURIComponent(currentActiveSheetCode)}&kode_unit=${encodeURIComponent(activeUnit)}${subAreaQuery}`;
            }
            if (exportBtn) {
                exportBtn.href = `/daily-register/arsip-matriks/export-excel?source_sheet=${encodeURIComponent(currentActiveSheetCode)}&kode_unit=${encodeURIComponent(activeUnit)}${subAreaQuery}`;
            }
        } else {
            // Filter Kapsul Cepat (Quick Status Filter Chips)
            if (currentStatusFilter && currentStatusFilter !== 'ALL') {
                dataToRender = dataToRender.filter(x => getItemWorkflowStatus(x) === currentStatusFilter);
            }
        }

        // Terapkan Smart Sorting Default:
        // Untuk RA: PERLU_REVISI ➔ BELUM_DIUJI ➔ MENUNGGU_KONFIRMASI ➔ SELESAI
        // Untuk Admin: MENUNGGU_KONFIRMASI ➔ PERLU_REVISI ➔ BELUM_DIUJI ➔ SELESAI
        const isRa = (userRole === 'ra');
        const raOrderWeight = {
            'PERLU_REVISI': 1,
            'BELUM_DIUJI': 2,
            'MENUNGGU_KONFIRMASI': 3,
            'SELESAI': 4
        };
        const adminOrderWeight = {
            'MENUNGGU_KONFIRMASI': 1,
            'PERLU_REVISI': 2,
            'BELUM_DIUJI': 3,
            'SELESAI': 4
        };
        const weightMap = isRa ? raOrderWeight : adminOrderWeight;

        dataToRender.sort((a, b) => {
            const stA = getItemWorkflowStatus(a);
            const stB = getItemWorkflowStatus(b);
            const wA = weightMap[stA] || 5;
            const wB = weightMap[stB] || 5;
            if (wA !== wB) return wA - wB;
            return (b.id || 0) - (a.id || 0);
        });

        renderTable(dataToRender);
    }

    function applyCustomFilter() {
        processDataDisplay();
    }

    function resetCustomFilter() {
        if (document.getElementById('filterKeyword')) document.getElementById('filterKeyword').value = '';
        if (document.getElementById('filterRiwayatSubArea')) document.getElementById('filterRiwayatSubArea').value = '';
        if (document.getElementById('filterKlarifikasi')) document.getElementById('filterKlarifikasi').value = '';
        processDataDisplay();
    }

    // Render Tabel Utama dengan Kolom STATUS & Tombol AKSI Kontekstual
    function renderTable(data) {
        const tbody = document.getElementById('kkaTableBody');
        tbody.innerHTML = '';

        if(!data || data.length === 0) {
            const isTellerKas = (currentActiveSheetCode === 'KKA_Teller_Kas' || currentActiveSheetCode === 'KKA_Teller');
            if (isTellerKas) {
                tbody.innerHTML = `<tr><td colspan="11">
                    <div class="empty-state" style="text-align: center; padding: 2.8rem 1rem; color: var(--text-muted);">
                        <i class="bi bi-inbox" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 0.6rem;"></i>
                        <h6 class="fw-bold text-dark mb-1">Nihil Sampel Transaksi Mutasi Teller CBS Hari Ini</h6>
                        <p class="small text-muted mb-3" style="max-width: 540px; margin: 0 auto 1rem auto;">Tidak ada sampel transaksi mutasi teller yang terjaring dari DUMP CBS pada tanggal ini. RA dapat menambahkan baris temuan mandiri untuk <strong>Kas Kecil (A2)</strong> atau <strong>Kas / Khasanah (A3)</strong> agar pengujian, arsip, dan print matriks KKA harian tetap dapat diselesaikan.</p>
                        <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm fw-semibold" onclick="openModalTambahMandiri()">
                            <i class="bi bi-plus-circle me-1"></i> + Tambah Pemeriksaan Kas / Kas Kecil
                        </button>
                    </div>
                </td></tr>`;
            } else {
                tbody.innerHTML = `<tr><td colspan="11"><div class="empty-state" style="text-align: center; padding: 3rem 0; color: var(--text-muted);"><i class="bi bi-inbox" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 1rem;"></i><p>Tidak ada data transaksi ditemukan.</p></div></td></tr>`;
            }
            return;
        }

        const isRa = (userRole === 'ra');

        data.forEach((item, index) => {
            const status = getItemWorkflowStatus(item);

            // 1. Kolom STATUS: Pill badge berikon elegan
            let statusBadge = '';
            let rawNote = item.reviewer_note || item.catatan_reviewer || 'Perlu perbaikan dari RA';
            let reviewerNoteSafe = rawNote.replace(/"/g, '&quot;');

            if (status === 'PERLU_REVISI') {
                statusBadge = `<span class="badge border" style="background:#fff1f2; color:#be123c; border-color:#fecdd3; border-radius:12px; padding:0.25rem 0.55rem; font-size:0.72rem; cursor:pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="Alasan: ${reviewerNoteSafe}"><i class="bi bi-exclamation-triangle-fill me-1"></i> Perlu Revisi</span>`;
            } else if (status === 'MENUNGGU_KONFIRMASI') {
                statusBadge = `<span class="badge border" style="background:#fffbeb; color:#b45309; border-color:#fde68a; border-radius:12px; padding:0.25rem 0.55rem; font-size:0.72rem;"><i class="bi bi-hourglass-split me-1"></i> Menunggu Konfirmasi</span>`;
            } else if (status === 'SELESAI') {
                statusBadge = `<span class="badge border" style="background:#ecfdf5; color:#047857; border-color:#a7f3d0; border-radius:12px; padding:0.25rem 0.55rem; font-size:0.72rem;"><i class="bi bi-check-circle-fill me-1"></i> Selesai</span>`;
            } else {
                statusBadge = `<span class="badge border" style="background:#f1f5f9; color:#475569; border-color:#e2e8f0; border-radius:12px; padding:0.25rem 0.55rem; font-size:0.72rem;"><i class="bi bi-circle me-1" style="font-size:0.6rem;"></i> Belum Diuji</span>`;
            }

            // 2. Row Highlighting Halus
            let rowClass = '';
            if (status === 'PERLU_REVISI') {
                rowClass = 'row-perlu-revisi';
            } else if (status === 'SELESAI') {
                rowClass = 'row-selesai';
            }

            // 3. Tombol AKSI Kontekstual Berdasarkan Role (RA vs Admin)
            let actionBtn = '';
            if (isRa) {
                if (status === 'PERLU_REVISI') {
                    actionBtn = `<button class="btn btn-sm text-white" style="background:#e11d48; border-color:#be123c; font-size:0.74rem; font-weight:600; padding:0.32rem 0.75rem; border-radius:6px; white-space:nowrap;" onclick="openDetailView(${item.id})">Perbaiki <i class="bi bi-chevron-right ms-0.5"></i></button>`;
                } else if (status === 'MENUNGGU_KONFIRMASI') {
                    actionBtn = `<button class="btn btn-sm" style="background:#ffffff; border:1px solid #cbd5e1; color:#334155; font-size:0.74rem; font-weight:600; padding:0.32rem 0.75rem; border-radius:6px; white-space:nowrap;" onclick="openDetailView(${item.id})"><i class="bi bi-eye me-1"></i> Lihat Isian</button>`;
                } else if (status === 'SELESAI') {
                    actionBtn = `<button class="btn btn-sm" style="background:#ecfdf5; border:1px solid #a7f3d0; color:#047857; font-size:0.74rem; font-weight:600; padding:0.32rem 0.75rem; border-radius:6px; white-space:nowrap;" onclick="openDetailView(${item.id})"><i class="bi bi-check2 me-1"></i> Selesai ✓</button>`;
                } else {
                    actionBtn = `<button class="btn btn-sm text-white" style="background:#1e3a8a; border-color:#1e3a8a; font-size:0.74rem; font-weight:600; padding:0.32rem 0.75rem; border-radius:6px; white-space:nowrap;" onclick="openDetailView(${item.id})">Review <i class="bi bi-chevron-right ms-0.5"></i></button>`;
                }
            } else {
                // Admin / Reviewer
                if (status === 'MENUNGGU_KONFIRMASI') {
                    actionBtn = `<button class="btn btn-sm text-white" style="background:#1e3a8a; border-color:#1e3a8a; font-size:0.74rem; font-weight:600; padding:0.32rem 0.75rem; border-radius:6px; white-space:nowrap;" onclick="openDetailView(${item.id})">Verifikasi <i class="bi bi-chevron-right ms-0.5"></i></button>`;
                } else if (status === 'PERLU_REVISI') {
                    actionBtn = `<button class="btn btn-sm" style="background:#fff1f2; border:1px solid #fecdd3; color:#be123c; font-size:0.74rem; font-weight:600; padding:0.32rem 0.75rem; border-radius:6px; white-space:nowrap;" onclick="openDetailView(${item.id})">Menunggu RA</button>`;
                } else if (status === 'SELESAI') {
                    actionBtn = `<button class="btn btn-sm" style="background:#ecfdf5; border:1px solid #a7f3d0; color:#047857; font-size:0.74rem; font-weight:600; padding:0.32rem 0.75rem; border-radius:6px; white-space:nowrap;" onclick="openDetailView(${item.id})">Terkonfirmasi ✓</button>`;
                } else {
                    actionBtn = `<button class="btn btn-sm" style="background:#ffffff; border:1px solid #e2e8f0; color:#64748b; font-size:0.74rem; font-weight:600; padding:0.32rem 0.75rem; border-radius:6px; white-space:nowrap;" onclick="openDetailView(${item.id})">Detail</button>`;
                }
            }

            let riskVal = item.risk_final || item.risk_awal || 'Moderate';
            let riskLower = riskVal.toLowerCase();
            let riskBadgeStyle = 'background-color:#fffbeb; color:#b45309; border:1px solid #fde68a;';
            if (riskLower === 'critical') {
                riskBadgeStyle = 'background-color:#7f1d1d; color:#ffffff; border:1px solid #991b1b;';
            } else if (riskLower === 'high') {
                riskBadgeStyle = 'background-color:#fee2e2; color:#dc2626; border:1px solid #fca5a5;';
            } else if (riskLower === 'moderate to high') {
                riskBadgeStyle = 'background-color:#ffedd5; color:#c2410c; border:1px solid #fed7aa;';
            } else if (riskLower === 'moderate') {
                riskBadgeStyle = 'background-color:#fffbeb; color:#b45309; border:1px solid #fde68a;';
            } else if (riskLower === 'low') {
                riskBadgeStyle = 'background-color:#dcfce7; color:#16a34a; border:1px solid #bbf7d0;';
            }
            let riskBadge = `<span class="badge" style="border-radius:12px; padding:0.25rem 0.55rem; font-size:0.72rem; ${riskBadgeStyle}">${riskVal}</span>`;
            let nominalStr = item.nominal_terkait > 0 ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(item.nominal_terkait) : '-';
            let userMakerDisplay = (!item.user_maker || item.user_maker === '-') ? 'Sistem / Tidak Tercatat' : item.user_maker;
            let deskripsiDisplay = (item.deskripsi && item.deskripsi !== '-') 
                ? item.deskripsi 
                : ((item.ket_tx && item.ket_tx !== '-') 
                    ? item.ket_tx 
                    : ((item.rincian && item.rincian !== '-') 
                        ? item.rincian 
                        : (item.catatan_rule || item.jenis_exception_awal || '-')));
            let deskripsiClean = deskripsiDisplay.replace(/"/g, '&quot;');

            const tr = `<tr class="${rowClass}">
                <td style="text-align:center; color:var(--text-muted); font-size:0.75rem;">${index + 1}</td>
                <td style="white-space:nowrap; font-size:0.78rem;">${item.tanggal_data || '-'}</td>
                <td style="text-align:center;"><span class="badge bg-light text-dark border" style="border-radius:20px; padding: 0.2rem 0.45rem; font-size:0.7rem;">${item.kode_unit || '-'}</span></td>
                <td style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:0.75rem;" title="${item.no_referensi || item.offsite_id || '-'}">
                    <span class="font-monospace text-primary">${item.no_referensi || item.offsite_id || '-'}</span>
                    ${item.no_arsip ? `<div style="font-size:0.7rem; color:#047857; font-weight:600;"><i class="bi bi-archive me-0.5"></i> ${item.no_arsip}</div>` : ''}
                </td>
                <td style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:0.76rem;" title="${userMakerDisplay}">
                    ${userMakerDisplay}
                </td>
                <td style="text-align:center; font-family:monospace; font-size:0.74rem;">${item.kode_trx || '-'}</td>
                <td style="font-weight:700; text-align:right; font-size:0.78rem; white-space:nowrap; color:#1e293b;">${nominalStr}</td>
                <td style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:0.76rem;" title="${deskripsiClean}">
                    ${deskripsiDisplay}
                </td>
                <td style="text-align:center;">${riskBadge}</td>
                <td style="text-align:center;">${statusBadge}</td>
                <td style="text-align:center;">${actionBtn}</td>
            </tr>`;
            tbody.insertAdjacentHTML('beforeend', tr);
        });

        // Initialize tooltips
        try {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        } catch(e) {}
    }

    // FUNGSI TOGGLE KE FULL PAGE DETAIL VIEW (MENGAMBIL DATA REVIEW LENGKAP & CHECKLIST DARI SERVER)
    function openDetailView(id) {
        axios.get(`${baseUrl}/${id}/review-data`)
            .then(response => {
                currentItem = response.data.finding;
                currentChecklists = response.data.checklists || [];
                currentRecurringCount = response.data.recurring_count || 1;
                populateDetailForm(currentItem);
            })
            .catch(() => {
                let item = globalFindings.find(x => x.id == id);
                if (item) {
                    currentItem = item;
                    currentChecklists = allMasterChecklists[item.source_sheet] || [];
                    currentRecurringCount = 1;
                    populateDetailForm(item);
                }
            });
    }

    function populateDetailForm(item) {
        const itemStatus = getItemWorkflowStatus(item);

        // Populate Header & Summary
        document.getElementById('header_nama_sheet').innerText = currentActiveSheet;
        document.getElementById('head_staging_id').innerText = item.offsite_id || item.staging_id || `KKA-${item.id}`;
        
        // Status Badge Atas
        const statusBadgeMap = {
            'PERLU_REVISI': '<span class="badge border" style="background:#fff1f2; color:#be123c; border-color:#fecdd3; padding:0.35rem 0.75rem; border-radius:12px; font-size:0.78rem;"><i class="bi bi-exclamation-triangle-fill me-1"></i> Perlu Revisi</span>',
            'MENUNGGU_KONFIRMASI': '<span class="badge border" style="background:#fffbeb; color:#b45309; border-color:#fde68a; padding:0.35rem 0.75rem; border-radius:12px; font-size:0.78rem;"><i class="bi bi-hourglass-split me-1"></i> Menunggu Konfirmasi</span>',
            'SELESAI': '<span class="badge border" style="background:#ecfdf5; color:#047857; border-color:#a7f3d0; padding:0.35rem 0.75rem; border-radius:12px; font-size:0.78rem;"><i class="bi bi-check-circle-fill me-1"></i> Selesai</span>',
            'BELUM_DIUJI': '<span class="badge border" style="background:#f1f5f9; color:#475569; border-color:#e2e8f0; padding:0.35rem 0.75rem; border-radius:12px; font-size:0.78rem;"><i class="bi bi-circle me-1"></i> Belum Diuji</span>'
        };
        document.getElementById('badge_status_atas').innerHTML = statusBadgeMap[itemStatus] || itemStatus;
        
        document.getElementById('sum_tanggal').innerText = item.tanggal_data || '-';
        document.getElementById('sum_unit').innerText = item.kode_unit || '-';
        document.getElementById('sum_user').innerText = (!item.user_maker || item.user_maker === '-') ? 'Sistem / Tidak Tercatat' : item.user_maker;
        document.getElementById('sum_nominal').innerText = item.nominal_terkait > 0 ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(item.nominal_terkait) : 'Rp 0';
        
        // Custom Badge Generator
        let riskValue = (item.risk_final || item.risk_awal || 'Moderate').toLowerCase();
        let badgeBgColor = '#fffbeb';
        let badgeBorderColor = '#fde68a';
        let badgeTextColor = '#b45309';

        if (riskValue === 'critical') {
            badgeBgColor = '#7f1d1d'; badgeBorderColor = '#991b1b'; badgeTextColor = '#ffffff';
        } else if (riskValue === 'high') {
            badgeBgColor = '#fee2e2'; badgeBorderColor = '#fca5a5'; badgeTextColor = '#dc2626';
        } else if (riskValue === 'moderate to high') {
            badgeBgColor = '#ffedd5'; badgeBorderColor = '#fed7aa'; badgeTextColor = '#c2410c';
        } else if (riskValue === 'moderate') {
            badgeBgColor = '#fffbeb'; badgeBorderColor = '#fde68a'; badgeTextColor = '#b45309';
        } else if (riskValue === 'low') {
            badgeBgColor = '#dcfce7'; badgeBorderColor = '#bbf7d0'; badgeTextColor = '#16a34a';
        }

        document.getElementById('sum_risk_badge').innerHTML = `<span style="background-color:${badgeBgColor}; color:${badgeTextColor}; border:1px solid ${badgeBorderColor}; border-radius:12px; padding:0.3rem 0.6rem; font-size:0.8rem; font-weight:600;">${item.risk_final || item.risk_awal || 'Moderate'}</span>`;
        let detailDeskripsi = (item.deskripsi && item.deskripsi !== '-') 
            ? item.deskripsi 
            : ((item.ket_tx && item.ket_tx !== '-') 
                ? item.ket_tx 
                : ((item.rincian && item.rincian !== '-') 
                    ? item.rincian 
                    : (item.catatan_rule || item.jenis_exception_awal || '-')));
        document.getElementById('sum_deskripsi').innerText = detailDeskripsi;

        // 1. Alert Box Revisi & Riwayat Box (Requirement 5)
        const alertBox = document.getElementById('alertBoxRevisi');
        const histBox = document.getElementById('infoBoxRiwayatRevisi');
        const revNote = item.reviewer_note || item.catatan_reviewer || '';

        if (itemStatus === 'PERLU_REVISI') {
            if (alertBox) {
                alertBox.style.display = 'block';
                document.getElementById('alert_reviewer_name').innerText = item.reviewed_by || 'Reviewer / Admin';
                document.getElementById('alert_reviewed_at').innerText = item.reviewed_at ? new Date(item.reviewed_at).toLocaleString('id-ID') : 'Baru Saja';
                document.getElementById('alert_reviewer_note').innerText = revNote || 'Mohon tinjau dan sesuaikan kembali hasil pengujian KKA ini.';
            }
            if (histBox) histBox.style.display = 'none';
        } else {
            if (alertBox) alertBox.style.display = 'none';
            if ((itemStatus === 'MENUNGGU_KONFIRMASI' || itemStatus === 'SELESAI') && revNote) {
                if (histBox) {
                    histBox.style.display = 'block';
                    document.getElementById('hist_reviewer_name').innerText = item.reviewed_by || 'Reviewer / Admin';
                    document.getElementById('hist_reviewed_at').innerText = item.reviewed_at ? new Date(item.reviewed_at).toLocaleString('id-ID') : '-';
                    document.getElementById('hist_reviewer_note').innerText = revNote;
                }
            } else {
                if (histBox) histBox.style.display = 'none';
            }
        }

        // Populate Section 1 (System)
        document.getElementById('sys_offsite_id').innerText = item.offsite_id || item.staging_id || `KKA-${item.id}`;
        document.getElementById('sys_tanggal_data').innerText = item.tanggal_data || '-';
        document.getElementById('sys_unit_kerja').innerText = item.kode_unit || '-';
        document.getElementById('sys_nominal_terkait').innerText = document.getElementById('sum_nominal').innerText;
        document.getElementById('sys_source_sheet').innerText = item.source_sheet || currentActiveSheet;
        document.getElementById('sys_risk_awal').innerHTML = document.getElementById('sum_risk_badge').innerHTML;
        document.getElementById('sys_exception_awal').innerText = item.jenis_exception_awal || item.deskripsi || '-';
        
        document.getElementById('sys_catatan_rule').innerText = detailDeskripsi;
        document.getElementById('sys_prosedur_uji').innerText = item.prosedur_uji || 'Uji Ketaatan & Keabsahan Dokumen Transaksi Offsite';

        // Populate Section 2 (RA)
        document.getElementById('ra_kka_id').value = item.id;
        document.getElementById('no_arsip').value = item.no_arsip || '';
        document.getElementById('bukti_referensi').value = item.bukti_referensi || '';
        document.getElementById('critical_trigger').value = item.critical_trigger || 'Tidak';
        document.getElementById('jenis_exception_ra').value = item.jenis_exception_ra || '';
        document.getElementById('hasil_uji').value = item.hasil_uji || '';
        document.getElementById('klarifikasi_unit').value = item.klarifikasi_unit || '';

        // Kunci Tanggal Ditemukan / Diuji: Otomatis terkunci saat RA membuka KKA
        const d = new Date();
        const localTodayStr = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        const tanggalVal = item.tanggal_ditemukan ? item.tanggal_ditemukan.split('T')[0].split(' ')[0] : localTodayStr;
        const tglInput = document.getElementById('tanggal_ditemukan');
        tglInput.value = tanggalVal;
        tglInput.readOnly = true;

        // Parameter Pengaduan (Keluhan Nasabah E.4.5)
        const containerPengaduan = document.getElementById('containerPengaduanFields');
        const activeSh = item.source_sheet || currentActiveSheetCode;
        if (activeSh === 'KKA_Pengaduan') {
            if (containerPengaduan) containerPengaduan.style.display = 'block';
            document.getElementById('tgl_keluhan').value = item.tgl_keluhan ? item.tgl_keluhan.split('T')[0] : (item.tanggal_data || '');
            document.getElementById('tgl_buku').value = item.tgl_buku ? item.tgl_buku.split('T')[0] : localTodayStr;
            document.getElementById('status_keluhan').value = item.status_keluhan || 'Proses';
            calculatePengaduanHariKerja();
        } else {
            if (containerPengaduan) containerPengaduan.style.display = 'none';
        }

        // Info File Bukti
        const fileInfoEl = document.getElementById('file_bukti_info');
        if (fileInfoEl) {
            if (item.file_bukti) {
                fileInfoEl.innerHTML = `<a href="/storage/${item.file_bukti}" target="_blank" class="text-primary text-decoration-none"><i class="bi bi-file-earmark-pdf"></i> Lihat File Bukti Terunggah</a>`;
            } else {
                fileInfoEl.innerHTML = `Belum ada file bukti PDF diunggah.`;
            }
        }

        // Render Accordion Checklist KKA
        renderChecklistAccordion(currentChecklists, item.checklist_answers || [], item);

        // Populate Section 3 (Admin)
        document.getElementById('admin_kka_id').value = item.id;
        document.getElementById('status_klarifikasi').value = item.status_klarifikasi || '';
        document.getElementById('perluasan_sampel').value = item.perluasan_sampel ? 1 : 0;
        document.getElementById('keputusan_onsite').value = item.keputusan_onsite || '';
        document.getElementById('keputusan_eskalasi').value = item.keputusan_eskalasi || 'Tidak';
        document.getElementById('catatan_reviewer').value = revNote;
        document.getElementById('display_workflow_status_admin').innerHTML = statusBadgeMap[itemStatus] || itemStatus;

        // Panel Admin State Display & Verification Panel
        const panelNonWaiting = document.getElementById('panelStatusAdminNonWaiting');
        const textNonWaiting = document.getElementById('statusAdminNonWaitingText');
        const panelVerif = document.getElementById('panelVerifikasiAdmin');

        if (itemStatus === 'MENUNGGU_KONFIRMASI') {
            if (panelNonWaiting) panelNonWaiting.style.display = 'none';
            if (panelVerif) panelVerif.style.display = 'block';
        } else {
            if (panelNonWaiting) {
                panelNonWaiting.style.display = 'block';
                if (itemStatus === 'SELESAI') {
                    textNonWaiting.innerHTML = `<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> KKA telah <strong>SELESAI &amp; TERKONFIRMASI</strong> oleh <strong>${item.reviewed_by || 'Admin'}</strong> pada ${item.reviewed_at ? new Date(item.reviewed_at).toLocaleString('id-ID') : '-'}. Form terkunci otomatis (Read-Only).</span>`;
                } else if (itemStatus === 'PERLU_REVISI') {
                    textNonWaiting.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> KKA berstatus <strong>PERLU REVISI</strong>. Menunggu Resident Auditor cabang memperbaiki dan mengajukan ulang.</span>`;
                } else {
                    textNonWaiting.innerHTML = `<span class="text-secondary"><i class="bi bi-circle me-1"></i> KKA <strong>BELUM DIUJI</strong> oleh Resident Auditor.</span>`;
                }
            }
            if (panelVerif) {
                panelVerif.style.display = 'block';
            }
        }

        // Hitung otomasi skor risiko dan kendalikan visibilitas field exception
        onChecklistChange();

        // Lock Logic based on Role & Status
        const isRa = (userRole === 'ra');
        const isSelesai = (itemStatus === 'SELESAI');

        if (isSelesai) {
            // SELESAI: Kunci form menjadi Read-Only bagi RA
            document.querySelectorAll('.field-ra').forEach(el => el.disabled = true);
            document.querySelectorAll('.subarea-checkbox').forEach(el => el.disabled = true);
            document.querySelectorAll('.chk-answer').forEach(el => el.disabled = true);
            document.querySelectorAll('.chk-keterangan').forEach(el => el.disabled = true);

            document.getElementById('badge_ra_lock').innerText = "Read Only (Selesai)";
            const btnSubmit = document.getElementById('btnSubmitRa');
            if (btnSubmit) btnSubmit.style.display = isRa ? 'none' : 'inline-block';
            const statusInfo = document.getElementById('badge_ra_status_info');
            if (statusInfo) {
                statusInfo.innerHTML = '<span class="badge border" style="background:#ecfdf5; color:#047857; border-color:#a7f3d0;"><i class="bi bi-check-circle-fill me-1"></i> Selesai &amp; Terkunci</span>';
            }
        } else {
            const canEditRa = (isRa || userRole === 'admin');
            document.querySelectorAll('.field-ra').forEach(el => el.disabled = !canEditRa);
            document.querySelectorAll('.subarea-checkbox').forEach(el => el.disabled = !canEditRa);
            document.querySelectorAll('.chk-answer').forEach(el => el.disabled = !canEditRa);
            document.querySelectorAll('.chk-keterangan').forEach(el => el.disabled = !canEditRa);

            const btnSubmit = document.getElementById('btnSubmitRa');
            const submitIcon = document.getElementById('btnSubmitRaIcon');
            const submitText = document.getElementById('btnSubmitRaText');
            const statusInfo = document.getElementById('badge_ra_status_info');

            if (btnSubmit) {
                btnSubmit.style.display = 'inline-block';
                if (itemStatus === 'PERLU_REVISI') {
                    btnSubmit.style.background = '#e11d48';
                    if (submitIcon) submitIcon.className = 'bi bi-send-check-fill';
                    if (submitText) submitText.innerText = 'Simpan Perbaikan';
                    document.getElementById('badge_ra_lock').innerText = "Mode Perbaikan";
                } else {
                    btnSubmit.style.background = '#1e3a8a';
                    if (submitIcon) submitIcon.className = 'bi bi-send-fill';
                    if (submitText) submitText.innerText = 'Simpan / Submit Review';
                    document.getElementById('badge_ra_lock').innerText = "Mode Edit";
                }
            }
            if (statusInfo) statusInfo.innerHTML = '';
        }

        document.querySelectorAll('.field-admin').forEach(el => el.disabled = isRa);
        document.getElementById('badge_admin_lock').innerText = isRa ? "Read Only" : "Mode Verifikasi";

        // LAKUKAN TOGGLE TAMPILAN
        document.getElementById('dashboardView').style.display = 'none';
        document.getElementById('detailView').style.display = 'block';
        window.scrollTo(0, 0);
    }

    // KEMBALI KE DASHBOARD
    function closeDetailView() {
        document.getElementById('detailView').style.display = 'none';
        document.getElementById('dashboardView').style.display = 'block';
    }

    // RENDER ACCORDION CHECKLIST KKA DENGAN KELOMPOK PER SUB-AREA
    function renderChecklistAccordion(checklists, existingAnswers, item) {
        const container = document.getElementById('checklistAccordionContainer');
        if (!container) return;

        if (!checklists || checklists.length === 0) {
            container.innerHTML = `<div class="p-3 text-muted text-center" style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px;">Tidak ada master checklist untuk tab KKA ini.</div>`;
            return;
        }

        const answerMap = {};
        if (Array.isArray(existingAnswers)) {
            existingAnswers.forEach(ans => {
                answerMap[ans.checklist_id] = {
                    jawaban: ans.jawaban,
                    keterangan: ans.keterangan || ''
                };
            });
        }

        const groups = {};
        checklists.forEach(chk => {
            const code = chk.sub_area_code;
            if (!groups[code]) {
                groups[code] = {
                    code: code,
                    name: chk.sub_area_name,
                    items: []
                };
            }
            groups[code].items.push(chk);
        });

        // Tentukan grup sub-area mana yang tercentang default / tersimpan
        let savedSubAreas = [];
        if (item && item.selected_sub_areas) {
            let s = item.selected_sub_areas;
            if (typeof s === 'string') {
                try { s = JSON.parse(s); } catch (e) { s = []; }
            }
            if (Array.isArray(s)) savedSubAreas = s;
        }

        // Jika belum ada tersimpan tapi sudah ada jawaban checklist:
        if (savedSubAreas.length === 0 && Array.isArray(existingAnswers) && existingAnswers.length > 0) {
            const answeredChkIds = existingAnswers.map(a => Number(a.checklist_id));
            checklists.forEach(chk => {
                if (answeredChkIds.includes(Number(chk.id)) && !savedSubAreas.includes(chk.sub_area_code)) {
                    savedSubAreas.push(chk.sub_area_code);
                }
            });
        }

        // Jika belum ada tersimpan sama sekali (pemeriksaan baru):
        // "untuk centang dropdown checklist sesuaikan saja dengan yg ada di KKA teller dan kas sekarang"
        if (savedSubAreas.length === 0 && item) {
            const activeSh = item.source_sheet || currentActiveSheetCode;
            const desc = ((item.kategori || '') + ' ' + (item.rincian || '') + ' ' + (item.deskripsi || '') + ' ' + (item.jenis_exception_awal || '')).toLowerCase();
            if (activeSh === 'KKA_Teller_Kas' || activeSh === 'KKA_Teller') {
                if (desc.includes('kas kecil')) {
                    savedSubAreas = ['A2'];
                } else if (desc.includes('khasanah') || desc.includes('brankas') || desc.includes('kas besar')) {
                    savedSubAreas = ['A3'];
                } else {
                    savedSubAreas = ['A1'];
                }
            } else if (activeSh === 'KKA_DPK_Layanan') {
                if (desc.includes('badan usaha') || desc.includes('pt') || desc.includes('cv') || desc.includes('perusahaan')) {
                    savedSubAreas = ['D2'];
                } else if (desc.includes('dormant') || desc.includes('blokir')) {
                    savedSubAreas = ['D4'];
                } else if (desc.includes('cs') || desc.includes('layanan') || desc.includes('antrean')) {
                    savedSubAreas = ['D3'];
                } else {
                    savedSubAreas = ['D1'];
                }
            } else if (activeSh === 'KKA_Biaya_Beban') {
                if (desc.includes('pengadaan') || desc.includes('vendor') || desc.includes('jasa')) {
                    savedSubAreas = ['B2'];
                } else if (desc.includes('perjalanan') || desc.includes('dinas') || desc.includes('sppd') || desc.includes('representasi')) {
                    savedSubAreas = ['B3'];
                } else if (desc.includes('pajak') || desc.includes('retribusi') || desc.includes('ppn') || desc.includes('pph')) {
                    savedSubAreas = ['B4'];
                } else {
                    savedSubAreas = ['B1'];
                }
            } else if (activeSh === 'KKA_Kredit') {
                if (desc.includes('flpp') || desc.includes('agunan') || desc.includes('jaminan') || desc.includes('kpr')) {
                    savedSubAreas = ['C2'];
                } else if (desc.includes('konstruksi') || desc.includes('monitoring') || desc.includes('restruktur')) {
                    savedSubAreas = ['C3'];
                } else {
                    savedSubAreas = ['C1'];
                }
            } else if (activeSh === 'KKA_Kartu_ATM') {
                if (desc.includes('retur') || desc.includes('tertelan') || desc.includes('rusak')) {
                    savedSubAreas = ['E2'];
                } else if (desc.includes('mesin') || desc.includes('fisik') || desc.includes('cctv') || desc.includes('kebersihan')) {
                    savedSubAreas = ['E3'];
                } else {
                    savedSubAreas = ['E1'];
                }
            } else {
                // Untuk sheet lain, default ke grup sub-area pertama
                const firstCode = Object.keys(groups)[0];
                if (firstCode) savedSubAreas = [firstCode];
            }
        }

        const isRa = (userRole === 'ra');
        const itemStatus = getItemWorkflowStatus(item);
        const isSelesai = (itemStatus === 'SELESAI');
        const canEditChecklist = (userRole === 'ra' || userRole === 'admin') && !isSelesai;

        let html = `
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded" style="background:#f1f5f9; border:1px solid var(--border-color);">
                <div style="font-size:0.83rem; font-weight:700; color:var(--primary-blue, #1e3a8a);">
                    <i class="bi bi-ui-checks me-1"></i> CHECKLIST PENGUJIAN AUDIT (<span id="checklistTotalItemsCount">0</span> ITEM)
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-success" id="badgeTotalYa" style="font-size:0.75rem;">0 Ya</span>
                    <span class="badge bg-danger" id="badgeTotalTidak" style="font-size:0.75rem;">0 Tidak</span>
                </div>
            </div>
            <div class="accordion" id="accordionChecklistKka">
        `;

        for (const code in groups) {
            const grp = groups[code];
            const collapseId = `collapseSubArea_${code}`;
            const isChecked = savedSubAreas.includes(code);

            html += `
            <div class="accordion-item" style="border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 0.65rem; overflow:hidden;">
                <h2 class="accordion-header" id="heading_${code}" style="background:#f8fafc; margin: 0;">
                    <div class="d-flex justify-content-between align-items-center w-100 p-2 px-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <input type="checkbox" class="form-check-input subarea-checkbox" id="chk_subarea_${code}" value="${code}" 
                                style="width: 1.15rem; height: 1.15rem; cursor: pointer; margin: 0;"
                                onchange="onSubAreaToggle('${code}', this.checked)"
                                onclick="event.stopPropagation();"
                                ${isChecked ? 'checked' : ''} ${!canEditChecklist ? 'disabled' : ''}
                                title="Centang untuk mengaktifkan pengujian sub-area ini">
                            
                            <div class="d-flex align-items-center gap-2" style="cursor:pointer; user-select:none;" onclick="toggleAccordionSubArea('${collapseId}')">
                                <i class="bi ${isChecked ? 'bi-chevron-down' : 'bi-chevron-right'} toggle-icon-${collapseId}" style="font-size:0.8rem; transition: transform 0.2s;"></i>
                                <span class="fw-bold" style="font-size:0.83rem; color:var(--text-dark);">${grp.name}</span>
                                <span class="badge bg-light text-secondary border" style="font-size:0.7rem;">${grp.items.length} Item</span>
                                <span class="badge bg-danger text-white subarea-tidak-badge" id="subTidakBadge_${code}" style="display:none; font-size:0.7rem;">0 Temuan Tidak</span>
                            </div>
                        </div>
                        <div>
                            <span class="badge ${isChecked ? 'bg-primary-subtle text-primary border' : 'bg-light text-muted border'} subarea-status-badge" id="subStatusBadge_${code}" style="font-size: 0.72rem;">${isChecked ? 'Aktif Digunakan' : 'Nonaktif / Diabaikan'}</span>
                        </div>
                    </div>
                </h2>
                <div id="${collapseId}" class="accordion-collapse" style="display: ${isChecked ? 'block' : 'none'};">
                    <div class="accordion-body p-0" id="subAreaBody_${code}">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size:0.78rem;">
                                <thead class="table-light" style="font-size:0.72rem; text-transform:uppercase;">
                                    <tr>
                                        <th style="width: 5%; text-align:center;">No</th>
                                        <th style="width: 25%;">Sub-Area / Objek</th>
                                        <th style="width: 40%;">Item Checklist</th>
                                        <th style="width: 14%; text-align:center;">Jawaban</th>
                                        <th style="width: 16%;">Keterangan / Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;

            grp.items.forEach(chk => {
                const saved = answerMap[chk.id];
                const jawaban = saved ? saved.jawaban : '';
                const ket = saved ? saved.keterangan : '';
                const isYa = (jawaban === 'Ya');
                const isTidak = (jawaban === 'Tidak');

                html += `
                    <tr id="row_chk_${chk.id}" class="${isTidak ? 'table-danger' : ''}">
                        <td style="text-align:center; color:var(--text-muted);">${chk.item_no}</td>
                        <td style="color:var(--text-dark); font-weight:500;">${chk.sub_area_name}</td>
                        <td style="color:var(--text-dark);">${chk.item_checklist}</td>
                        <td style="text-align:center;">
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check chk-answer" name="chk_answer_${chk.id}" id="chk_ya_${chk.id}" value="Ya" data-subarea="${code}" autocomplete="off" onchange="onChecklistChange()" ${isYa ? 'checked' : ''} ${!canEditChecklist ? 'disabled' : ''}>
                                <label class="btn btn-outline-success py-0 px-2" for="chk_ya_${chk.id}" style="font-size:0.72rem;">[✓] Ya</label>

                                <input type="radio" class="btn-check chk-answer" name="chk_answer_${chk.id}" id="chk_tidak_${chk.id}" value="Tidak" data-subarea="${code}" autocomplete="off" onchange="onChecklistChange()" ${isTidak ? 'checked' : ''} ${!canEditChecklist ? 'disabled' : ''}>
                                <label class="btn btn-outline-danger py-0 px-2" for="chk_tidak_${chk.id}" style="font-size:0.72rem;">[✗] Tidak</label>
                            </div>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm chk-keterangan" id="chk_ket_${chk.id}" data-checklist-id="${chk.id}" data-subarea="${code}" placeholder="Catatan opsional..." value="${ket.replace(/"/g, '&quot;')}" ${!canEditChecklist ? 'disabled' : ''} style="font-size:0.75rem;">
                        </td>
                    </tr>
                `;
            });

            html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            `;
        }

        html += `</div>`;
        container.innerHTML = html;
    }

    function onSubAreaToggle(code, isChecked) {
        const collapseId = `collapseSubArea_${code}`;
        const el = document.getElementById(collapseId);
        const icon = document.querySelector(`.toggle-icon-${collapseId}`);
        const statusBadge = document.getElementById(`subStatusBadge_${code}`);

        if (statusBadge) {
            statusBadge.innerText = isChecked ? 'Aktif Digunakan' : 'Nonaktif / Diabaikan';
            statusBadge.className = isChecked ? 'badge bg-primary-subtle text-primary border subarea-status-badge' : 'badge bg-light text-muted border subarea-status-badge';
        }

        if (isChecked) {
            // Otomatis expand terbuka
            if (el) el.style.display = 'block';
            if (icon) {
                icon.classList.remove('bi-chevron-right');
                icon.classList.add('bi-chevron-down');
            }
        } else {
            // Otomatis collapse tertutup & reset isian di dalam grup tersebut
            if (el) el.style.display = 'none';
            if (icon) {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-right');
            }
            document.querySelectorAll(`.chk-answer[data-subarea="${code}"]`).forEach(r => {
                r.checked = false;
                const chkId = r.name.replace('chk_answer_', '');
                const row = document.getElementById(`row_chk_${chkId}`);
                if (row) row.classList.remove('table-danger', 'table-danger-subtle');
            });
            document.querySelectorAll(`.chk-keterangan[data-subarea="${code}"]`).forEach(inp => {
                inp.value = '';
            });
        }

        onChecklistChange();
    }

    function toggleAccordionSubArea(collapseId) {
        const el = document.getElementById(collapseId);
        if (!el) return;
        const isVisible = (el.style.display !== 'none');
        el.style.display = isVisible ? 'none' : 'block';
        const icon = document.querySelector(`.toggle-icon-${collapseId}`);
        if (icon) {
            if (isVisible) {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-right');
            } else {
                icon.classList.remove('bi-chevron-right');
                icon.classList.add('bi-chevron-down');
            }
        }
    }

    // Kalkulasi Lama Hari Kerja Keluhan Nasabah (NETWORKDAYS)
    function calculatePengaduanHariKerja() {
        const status = (document.getElementById('status_keluhan') ? document.getElementById('status_keluhan').value : '').trim().toUpperCase();
        const display = document.getElementById('display_lama_hari_kerja');
        if (!display) return;

        if (status === 'REJECT' || status === 'DITOLAK') {
            display.innerText = 'DITOLAK';
            display.className = 'badge bg-danger text-white px-2 py-1';
            return;
        }

        const tgl1Val = document.getElementById('tgl_keluhan')?.value;
        const tgl2Val = document.getElementById('tgl_buku')?.value;
        if (!tgl1Val || !tgl2Val) {
            display.innerText = '-';
            display.className = 'text-muted';
            return;
        }

        const d1 = new Date(tgl1Val);
        const d2 = new Date(tgl2Val);
        if (isNaN(d1) || isNaN(d2)) {
            display.innerText = '-';
            return;
        }

        let start = d1 < d2 ? d1 : d2;
        let end = d1 < d2 ? d2 : d1;
        let workDays = 0;
        let cur = new Date(start);
        while (cur <= end) {
            const dayOfWeek = cur.getDay();
            if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Bukan Sabtu (6) / Minggu (0)
                workDays++;
            }
            cur.setDate(cur.getDate() + 1);
        }

        display.innerText = `${workDays} Hari Kerja`;
        display.className = 'badge bg-primary text-white px-2 py-1';
    }

    // PENANGANAN REAKTIF CHECKLIST & OTOMASI SKOR RISIKO
    function onChecklistChange() {
        const checkedBoxes = Array.from(document.querySelectorAll('.subarea-checkbox:checked'));
        const checkedSubAreas = checkedBoxes.map(cb => cb.value);

        // 1. Total item di header HANYA menghitung item dari grup dropdown yang dicentang
        let totalActiveItems = 0;
        if (currentChecklists && Array.isArray(currentChecklists)) {
            totalActiveItems = currentChecklists.filter(chk => checkedSubAreas.includes(chk.sub_area_code)).length;
        }
        const totalItemsHeaderEl = document.getElementById('checklistTotalItemsCount');
        if (totalItemsHeaderEl) {
            totalItemsHeaderEl.innerText = totalActiveItems;
        }

        // 2. Penghitung Ya dan Tidak HANYA menghitung item dari grup dropdown yang dicentang
        let yaCount = 0;
        let tidakCount = 0;
        const subAreaTidakMap = {};

        document.querySelectorAll('.chk-answer:checked').forEach(r => {
            const sub = r.getAttribute('data-subarea');
            if (!checkedSubAreas.includes(sub)) {
                return; // Diabaikan jika grup sub-area tidak dicentang!
            }
            const chkId = r.name.replace('chk_answer_', '');
            const row = document.getElementById(`row_chk_${chkId}`);

            if (r.value === 'Tidak') {
                tidakCount++;
                subAreaTidakMap[sub] = (subAreaTidakMap[sub] || 0) + 1;
                if (row) {
                    row.classList.add('table-danger');
                }
            } else {
                yaCount++;
                if (row) {
                    row.classList.remove('table-danger', 'table-danger-subtle');
                }
            }
        });

        // Update Badges
        const badgeYa = document.getElementById('badgeTotalYa');
        const badgeTidak = document.getElementById('badgeTotalTidak');
        if (badgeYa) badgeYa.innerText = `${yaCount} Ya`;
        if (badgeTidak) badgeTidak.innerText = `${tidakCount} Tidak`;

        // Update SubArea Badges (hanya untuk grup yang dicentang)
        document.querySelectorAll('.subarea-tidak-badge').forEach(b => {
            const code = b.id.replace('subTidakBadge_', '');
            const cnt = checkedSubAreas.includes(code) ? (subAreaTidakMap[code] || 0) : 0;
            if (cnt > 0) {
                b.innerText = `${cnt} Temuan Tidak`;
                b.style.display = 'inline-block';
            } else {
                b.style.display = 'none';
            }
        });

        // 3. Kemunculan Kondisional (Jenis Exception, Hasil Pengujian, Klarifikasi Unit)
        const containerExc = document.getElementById('containerExceptionFields');
        const inputJenisExc = document.getElementById('jenis_exception_ra');
        const inputHasilUji = document.getElementById('hasil_uji');
        const inputKlarifikasi = document.getElementById('klarifikasi_unit');

        if (tidakCount >= 1) {
            if (containerExc) containerExc.style.display = 'block';
            if (inputJenisExc) inputJenisExc.required = true;
            if (inputHasilUji) inputHasilUji.required = true;
            if (inputKlarifikasi) inputKlarifikasi.required = true;
        } else {
            if (containerExc) containerExc.style.display = 'none';
            if (inputJenisExc) { inputJenisExc.required = false; inputJenisExc.value = ''; }
            if (inputHasilUji) { inputHasilUji.required = false; inputHasilUji.value = ''; }
            if (inputKlarifikasi) { inputKlarifikasi.required = false; inputKlarifikasi.value = ''; }
        }

        // 4. Otomasi Skor Risiko
        hitungOtomatisSkorRisiko(tidakCount);
    }

    function hitungOtomatisSkorRisiko(tidakCount) {
        const nominal = currentItem ? (parseFloat(currentItem.nominal_terkait) || 0) : 0;
        const criticalTrigger = document.getElementById('critical_trigger').value;
        const recurringCount = currentRecurringCount || 1;

        let skorDampak = 1;
        let labelDampak = '1 — Sangat Rendah';
        let skorKemungkinan = 1;
        let labelKemungkinan = '1 — Sangat Jarang';
        let skorFinal = 1;
        let riskFinal = 'Low';
        let perluOnsite = 'Tidak Perlu';

        if (tidakCount === 0 && criticalTrigger === 'Tidak') {
            skorDampak = 1;
            labelDampak = '1 — Sangat Rendah';
            skorKemungkinan = 1;
            labelKemungkinan = '1 — Sangat Jarang';
            skorFinal = 1;
            riskFinal = 'Low';
            perluOnsite = 'Tidak Perlu';
        } else {
            // 1. Skor Dampak (1-5) Otomatis
            if (criticalTrigger === 'Ya' || nominal >= 500000000) {
                skorDampak = 5;
                labelDampak = '5 — Sangat Tinggi';
            } else {
                const hasNominal = currentItem && currentItem.nominal_terkait !== null && nominal > 0;
                let chkScore = 1;
                if (tidakCount >= 5) chkScore = 4;
                else if (tidakCount >= 3) chkScore = 3;
                else if (tidakCount >= 2) chkScore = 2;
                else if (tidakCount === 1) chkScore = hasNominal ? 1 : 2;

                let nomScore = 0;
                if (hasNominal) {
                    if (nominal >= 100000000) nomScore = 4;
                    else if (nominal >= 50000000) nomScore = 3;
                    else if (nominal >= 10000000) nomScore = 2;
                    else nomScore = 1;
                }

                skorDampak = Math.max(chkScore, nomScore);
                if (!hasNominal && tidakCount >= 1 && skorDampak < 2) {
                    skorDampak = 2;
                }
                skorDampak = Math.min(Math.max(skorDampak, 1), 5);

                const dampakLabels = {
                    1: '1 — Sangat Rendah',
                    2: '2 — Rendah',
                    3: '3 — Sedang',
                    4: '4 — Tinggi',
                    5: '5 — Sangat Tinggi'
                };
                labelDampak = dampakLabels[skorDampak] || `${skorDampak}`;
            }

            // 2. Skor Kemungkinan (1-5) Otomatis Berdasarkan Kejadian Berulang di Database
            const k = recurringCount;
            if (k >= 7) {
                skorKemungkinan = 5;
                labelKemungkinan = '5 — Sangat Sering';
            } else if (k >= 5) {
                skorKemungkinan = 4;
                labelKemungkinan = '4 — Sering';
            } else if (k >= 3) {
                skorKemungkinan = 3;
                labelKemungkinan = '3 — Cukup Sering';
            } else if (k === 2) {
                skorKemungkinan = 2;
                labelKemungkinan = '2 — Jarang';
            } else {
                skorKemungkinan = (tidakCount >= 2) ? 2 : 1;
                labelKemungkinan = (skorKemungkinan === 2) ? '2 — Jarang (Banyak Gagal)' : '1 — Sangat Jarang';
            }

            // 3. Skor & Risk Final Otomatis (Dampak x Kemungkinan)
            skorFinal = skorDampak * skorKemungkinan;

            if (criticalTrigger === 'Ya') {
                riskFinal = 'Critical';
                if (skorFinal < 16) skorFinal = 16;
            } else if (skorFinal >= 16) {
                riskFinal = 'Critical';
            } else if (skorFinal >= 10) {
                riskFinal = 'High';
            } else if (skorFinal >= 8) {
                riskFinal = 'Moderate to High';
            } else if (skorFinal >= 5) {
                riskFinal = 'Moderate';
            } else {
                riskFinal = 'Low';
            }

            // 4. Perlu Onsite Inspection?
            if (['Moderate to High', 'High', 'Critical'].includes(riskFinal) || skorFinal >= 8 || criticalTrigger === 'Ya') {
                perluOnsite = 'Perlu';
            } else {
                perluOnsite = 'Tidak Perlu';
            }
        }

        // Update Hidden Inputs & Display Elements
        document.getElementById('skor_dampak').value = skorDampak;
        document.getElementById('display_skor_dampak').innerText = labelDampak;

        document.getElementById('skor_kemungkinan').value = skorKemungkinan;
        document.getElementById('display_skor_kemungkinan').innerHTML = `${labelKemungkinan} <span class="badge bg-light text-secondary border ms-1" style="font-size:0.7rem;">Kejadian ke-${recurringCount}</span>`;

        document.getElementById('skor_final').value = skorFinal;
        document.getElementById('risk_final').value = riskFinal;
        document.getElementById('text_skor_final').innerText = skorFinal;

        const badgeEl = document.getElementById('badge_risk_final');
        badgeEl.innerText = riskFinal;

        let badgeBg = '#dcfce7';
        let badgeBorder = '#bbf7d0';
        let badgeText = '#16a34a';

        if (riskFinal === 'Critical') {
            badgeBg = '#7f1d1d'; badgeBorder = '#991b1b'; badgeText = '#ffffff';
        } else if (riskFinal === 'High') {
            badgeBg = '#fee2e2'; badgeBorder = '#fca5a5'; badgeText = '#dc2626';
        } else if (riskFinal === 'Moderate to High') {
            badgeBg = '#ffedd5'; badgeBorder = '#fed7aa'; badgeText = '#c2410c';
        } else if (riskFinal === 'Moderate') {
            badgeBg = '#fffbeb'; badgeBorder = '#fde68a'; badgeText = '#b45309';
        }

        badgeEl.style.backgroundColor = badgeBg;
        badgeEl.style.borderColor = badgeBorder;
        badgeEl.style.color = badgeText;

        document.getElementById('perlu_onsite').value = perluOnsite;
        const onsiteEl = document.getElementById('display_perlu_onsite');
        onsiteEl.innerText = (perluOnsite === 'Perlu') ? 'Perlu Pemeriksaan Onsite' : 'Tidak Perlu';
        if (perluOnsite === 'Perlu') {
            onsiteEl.style.color = '#dc2626';
            onsiteEl.style.fontWeight = '700';
        } else {
            onsiteEl.style.color = 'var(--text-dark)';
            onsiteEl.style.fontWeight = '600';
        }
    }

    function submitRaForm() {
        const id = document.getElementById('ra_kka_id').value;

        const noArsip = (document.getElementById('no_arsip')?.value || '').trim();
        if (!noArsip) {
            alert('Kolom No. Arsip wajib diisi sebelum menyimpan atau menyelesaikan KKA!');
            document.getElementById('no_arsip')?.focus();
            return;
        }
        
        const checkedBoxes = Array.from(document.querySelectorAll('.subarea-checkbox:checked'));
        const checkedSubAreas = checkedBoxes.map(cb => cb.value);

        if (checkedSubAreas.length === 0) {
            alert('Harap centang minimal 1 grup dropdown Sub-Area Checklist [✓] sebelum menyimpan hasil pemeriksaan!');
            return;
        }

        let unansweredCount = 0;
        let tidakCount = 0;
        const checklistAnswers = [];
        currentChecklists.forEach(chk => {
            if (!checkedSubAreas.includes(chk.sub_area_code)) {
                return; // Diabaikan jika grup sub-area tidak dicentang!
            }

            const yaRadio = document.getElementById(`chk_ya_${chk.id}`);
            const tidakRadio = document.getElementById(`chk_tidak_${chk.id}`);
            const ketInput = document.getElementById(`chk_ket_${chk.id}`);

            const isYa = yaRadio && yaRadio.checked;
            const isTidak = tidakRadio && tidakRadio.checked;

            if (!isYa && !isTidak) {
                unansweredCount++;
            }

            const jawaban = isTidak ? 'Tidak' : (isYa ? 'Ya' : 'Ya');
            if (jawaban === 'Tidak') tidakCount++;
            const keterangan = ketInput ? ketInput.value.trim() : '';
            checklistAnswers.push({
                checklist_id: chk.id,
                jawaban: jawaban,
                keterangan: keterangan
            });
        });

        if (unansweredCount > 0) {
            alert(`Terdapat ${unansweredCount} item checklist pada grup terpilih yang belum dicentang. Sesuai instruksi, RA wajib mencentang seluruh kotak checklist pada grup yang dipilih ([✓] Ya atau [✗] Tidak).`);
            return;
        }

        const jenisExc = document.getElementById('jenis_exception_ra').value.trim();
        const hasilUji = document.getElementById('hasil_uji').value.trim();
        const klarifikasi = document.getElementById('klarifikasi_unit').value.trim();

        if (tidakCount >= 1) {
            if (!jenisExc) {
                alert('Jenis Exception (Versi RA) wajib diisi karena terdapat item checklist bernilai Tidak!');
                document.getElementById('jenis_exception_ra').focus();
                return;
            }
            if (!hasilUji) {
                alert('Hasil Pengujian Lapangan wajib diisi karena terdapat item checklist bernilai Tidak!');
                document.getElementById('hasil_uji').focus();
                return;
            }
            if (!klarifikasi) {
                alert('Klarifikasi Unit Kerja / Cabang wajib diisi karena terdapat item checklist bernilai Tidak!');
                document.getElementById('klarifikasi_unit').focus();
                return;
            }
        }

        let formData = new FormData();
        formData.append('no_arsip', noArsip);
        formData.append('bukti_referensi', document.getElementById('bukti_referensi').value);
        formData.append('critical_trigger', document.getElementById('critical_trigger').value);
        formData.append('jenis_exception_ra', tidakCount >= 1 ? jenisExc : '');
        formData.append('hasil_uji', tidakCount >= 1 ? hasilUji : '');
        formData.append('klarifikasi_unit', tidakCount >= 1 ? klarifikasi : '');
        formData.append('perlu_onsite', document.getElementById('perlu_onsite').value);
        formData.append('tanggal_ditemukan', document.getElementById('tanggal_ditemukan').value);
        formData.append('selected_sub_areas', JSON.stringify(checkedSubAreas));
        formData.append('checklist_answers', JSON.stringify(checklistAnswers));

        const activeSh = currentItem?.source_sheet || currentActiveSheetCode;
        if (activeSh === 'KKA_Pengaduan') {
            formData.append('tgl_keluhan', document.getElementById('tgl_keluhan')?.value || '');
            formData.append('tgl_buku', document.getElementById('tgl_buku')?.value || '');
            formData.append('status_keluhan', document.getElementById('status_keluhan')?.value || '');
        }

        const fileInput = document.getElementById('file_bukti');
        if (fileInput.files.length > 0) {
            formData.append('file_bukti', fileInput.files[0]);
        }

        axios.post(`${baseUrl}/${id}/ra`, formData, { 
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'multipart/form-data' } 
        }).then(res => {
            alert(res.data.message || 'Pemeriksaan KKA berhasil disimpan langsung ke Arsip Riwayat Selesai!');
            closeDetailView();
            // Otomatis arahkan RA langsung ke mode Arsip Riwayat Selesai beserta tombol cetak arsip matriks
            if (!isShowingRiwayat) {
                isShowingRiwayat = true;
                const btn = document.getElementById('btnToggleRiwayat');
                const filterCard = document.getElementById('riwayatFilterCard');
                const btnText = document.getElementById('btnRiwayatText');
                if (btn) {
                    btn.style.background = 'var(--primary-blue)';
                    btn.style.color = '#fff';
                    btn.style.borderColor = 'var(--primary-blue)';
                }
                if (btnText) btnText.innerText = 'Kembali ke Data Utama';
                if (filterCard) filterCard.style.display = 'block';
                if (document.getElementById('tableTitle')) {
                    document.getElementById('tableTitle').innerText = `${currentActiveSheet} (Arsip Riwayat Selesai)`;
                }
            }
            loadData();
        }).catch(err => {
            if (err.response) {
                if (err.response.status === 422) {
                    let errors = err.response.data.errors;
                    let msg = "Validasi Gagal:\n";
                    for (let key in errors) {
                        msg += `- ${errors[key][0]}\n`;
                    }
                    alert(msg);
                } else {
                    alert("Error Server (" + err.response.status + "): " + (err.response.data.message || 'Terjadi kesalahan sistem'));
                }
            } else {
                alert('Gagal terhubung ke server!');
            }
        });
    }

    function submitAdminDecision(decision) {
        const id = document.getElementById('admin_kka_id').value;
        const catatan = (document.getElementById('catatan_reviewer')?.value || '').trim();

        if (decision === 'REVISI' && !catatan) {
            alert('Catatan Alasan Revisi wajib diisi ketika meminta perbaikan KKA dari Resident Auditor!');
            document.getElementById('catatan_reviewer')?.focus();
            return;
        }

        const confirmMsg = decision === 'REVISI' 
            ? 'Apakah Anda yakin ingin meminta revisi/perbaikan KKA ini kepada Resident Auditor?' 
            : 'Apakah Anda yakin ingin mengonfirmasi dan menyelesaikan (Approve) KKA ini? Form akan terkunci secara otomatis.';

        if (!confirm(confirmMsg)) {
            return;
        }

        const payload = {
            decision: decision,
            catatan_reviewer: catatan,
            status_klarifikasi: document.getElementById('status_klarifikasi')?.value || '',
            perluasan_sampel: document.getElementById('perluasan_sampel')?.value || 0,
            keputusan_onsite: document.getElementById('keputusan_onsite')?.value || '',
            keputusan_eskalasi: document.getElementById('keputusan_eskalasi')?.value || 'Tidak'
        };

        axios.post(`${baseUrl}/${id}/review-decision`, payload, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
        }).then(res => {
            alert(res.data.message || 'Keputusan verifikasi berhasil disimpan!');
            closeDetailView();
            loadData();
        }).catch(err => {
            if (err.response) {
                if (err.response.status === 422) {
                    let errors = err.response.data.errors;
                    let msg = "Validasi Gagal:\n";
                    if (errors) {
                        for (let key in errors) {
                            msg += `- ${errors[key][0]}\n`;
                        }
                    } else {
                        msg += `- ${err.response.data.message || 'Input tidak valid'}\n`;
                    }
                    alert(msg);
                } else {
                    alert("Error Server (" + err.response.status + "): " + (err.response.data.message || 'Terjadi kesalahan sistem'));
                }
            } else {
                alert('Gagal terhubung ke server!');
            }
        });
    }

    function submitAdminForm() {
        const id = document.getElementById('admin_kka_id').value;
        const payload = {
            status_klarifikasi: document.getElementById('status_klarifikasi')?.value || '',
            perluasan_sampel: document.getElementById('perluasan_sampel')?.value || 0,
            keputusan_onsite: document.getElementById('keputusan_onsite')?.value || '',
            status_review: document.getElementById('status_review')?.value || 'Approved',
            keputusan_eskalasi: document.getElementById('keputusan_eskalasi')?.value || 'Tidak',
            catatan_reviewer: document.getElementById('catatan_reviewer')?.value || '',
            _method: 'PUT'
        };

        axios.post(`${baseUrl}/${id}/admin`, payload, { headers: { 'X-CSRF-TOKEN': csrfToken } })
            .then(res => {
                alert(res.data.message || 'Review Admin berhasil disubmit!');
                closeDetailView();
                loadData();
            })
            .catch(err => {
                if (err.response) {
                    if (err.response.status === 422) {
                        let errors = err.response.data.errors;
                        let msg = "Validasi Gagal:\n";
                        for (let key in errors) {
                            msg += `- ${errors[key][0]}\n`;
                        }
                        alert(msg);
                    } else {
                        alert("Error Server (" + err.response.status + "): " + (err.response.data.message || 'Terjadi kesalahan sistem'));
                    }
                } else {
                    alert('Gagal terhubung ke server!');
                }
            });
    }

    // =========================================================================
    // FITUR KONTROL HARIAN & KONTROL MINGGUAN (SOP 04.2)
    // =========================================================================
    let currentKapasitasData = null;

    function loadKapasitasHarian() {
        if (!document.getElementById('calcTotalMenit')) return;

        let params = new URLSearchParams();
        @if(request()->has('kode_unit'))
        params.append('kode_unit', '{{ request('kode_unit') }}');
        @elseif(isset($selectedKodeUnit))
        params.append('kode_unit', '{{ $selectedKodeUnit }}');
        @endif
        @if(request()->has('cabang'))
        params.append('cabang_id', '{{ request('cabang') }}');
        @elseif(request()->has('cabang_id'))
        params.append('cabang_id', '{{ request('cabang_id') }}');
        @elseif(isset($selectedCabangId))
        params.append('cabang_id', '{{ $selectedCabangId }}');
        @endif
        @if(request()->has('tanggal'))
        params.append('tanggal', '{{ request('tanggal') }}');
        @endif

        let qs = params.toString();
        let url = `${baseUrl}/kapasitas` + (qs ? `?${qs}` : '');

        axios.get(url)
            .then(response => {
                if (response.data.status === 'success') {
                    currentKapasitasData = response.data.data;
                    renderKapasitasUI(currentKapasitasData);
                }
            })
            .catch(err => {
                console.error('Gagal memuat kapasitas harian auditor:', err);
            });
    }

    function renderKapasitasUI(data) {
        if (!data || !document.getElementById('calcTotalMenit')) return;

        // Info Unit / Kantor Cabang
        if (data.kode_unit) {
            const unitInfoElem = document.getElementById('unitInfoText');
            if (unitInfoElem) {
                unitInfoElem.innerText = `${data.kode_unit} - ${data.nama_cabang || ''}`;
            }
        }

        // Info Tanggal & Minggu
        if (data.start_date && data.end_date) {
            document.getElementById('mingguDatesText').innerText = `${formatDateID(data.start_date)} s.d. ${formatDateID(data.end_date)}`;
        }
        if (data.tanggal_review) {
            document.getElementById('todayReviewDateText').innerText = formatDateID(data.tanggal_review);
        }

        // Kalkulasi Kapasitas Harian (Baris 7–10 Kontrol_Harian)
        document.getElementById('calcTotalMenit').innerHTML = `${data.total_menit_harian} <small style="font-size: 0.75rem; font-weight: normal; color: var(--text-muted);">Menit</small>`;
        document.getElementById('calcJmlAuditor').innerText = data.jumlah_auditor;
        const inputJml = document.getElementById('inputJumlahAuditor');
        if (inputJml) inputJml.value = data.jumlah_auditor;

        // Potongan KKA Fisik Hari Ini
        document.getElementById('calcPotonganMingguan').innerHTML = `${data.menit_mingguan_hari_ini} <small style="font-size: 0.75rem; font-weight: normal; color: var(--text-muted);">Menit</small>`;
        const namaFisikElem = document.getElementById('calcNamaFisikHariIni');
        if (data.area_mingguan_hari_ini && data.area_mingguan_hari_ini.length > 0) {
            namaFisikElem.innerText = data.area_mingguan_hari_ini.join(', ');
            namaFisikElem.title = data.area_mingguan_hari_ini.join(', ');
        } else {
            namaFisikElem.innerText = 'Tidak ada jadwal KKA fisik hari ini';
        }

        // Menit Tersedia untuk Sampling Harian
        document.getElementById('calcTersediaSampling').innerHTML = `${data.menit_tersedia_sampling} <small style="font-size: 0.75rem; font-weight: normal; color: var(--text-muted);">Menit</small>`;
        
        // Beban Menit Seluruh Sampel
        document.getElementById('calcBebanSampel').innerHTML = `${data.estimasi_menit_sampel} <small style="font-size: 0.75rem; font-weight: normal; color: var(--text-muted);">Menit</small>`;
        document.getElementById('calcTotalSampelCount').innerText = data.total_jumlah_sampel;

        // Sisa / (Lebih) Kapasitas
        const sisa = data.sisa_kapasitas;
        const sisaElem = document.getElementById('calcSisaKapasitas');
        const banner = document.getElementById('bannerSisaKapasitas');
        const badgeStatus = document.getElementById('badgeKapasitasStatus');
        const warning = document.getElementById('badgeOverCapacityWarning');

        if (sisa < 0) {
            sisaElem.innerText = `${sisa} Menit`;
            sisaElem.style.color = '#dc2626';
            banner.style.background = '#fef2f2';
            banner.style.borderColor = '#fca5a5';
            warning.style.display = 'inline-block';
            badgeStatus.className = 'badge bg-danger px-3 py-2';
            badgeStatus.innerHTML = '<i class="bi bi-exclamation-octagon me-1"></i> OVER CAPACITY';
        } else {
            sisaElem.innerText = `+${sisa} Menit`;
            sisaElem.style.color = '#16a34a';
            banner.style.background = '#f0fdf4';
            banner.style.borderColor = '#bbf7d0';
            warning.style.display = 'none';
            badgeStatus.className = 'badge bg-success px-3 py-2';
            badgeStatus.innerHTML = '<i class="bi bi-check-circle me-1"></i> DALAM KAPASITAS';
        }

        // Progress bar beban kerja
        let usedMinutes = (data.menit_mingguan_hari_ini || 0) + (data.estimasi_menit_sampel || 0);
        let totalMinutes = data.total_menit_harian || 420;
        let pct = Math.min(100, Math.round((usedMinutes / totalMinutes) * 100));
        const pbar = document.getElementById('capProgressBar');
        pbar.style.width = `${pct}%`;
        document.getElementById('capPercentText').innerText = `${pct}% (${usedMinutes} / ${totalMinutes} Menit)`;
        if (usedMinutes > totalMinutes) {
            pbar.className = 'progress-bar bg-danger';
        } else if (pct > 80) {
            pbar.className = 'progress-bar bg-warning';
        } else {
            pbar.className = 'progress-bar bg-success';
        }

        // Render tabel Kontrol Mingguan
        renderJadwalMingguanTable(data.schedules);
    }

    function renderJadwalMingguanTable(schedules) {
        const tbody = document.getElementById('kontrolMingguanTableBody');
        const modalTbody = document.getElementById('modalJadwalTableBody');
        if (!tbody || !schedules) return;

        const hariMap = {1: 'Senin', 2: 'Selasa', 3: 'Rabu', 4: 'Kamis', 5: 'Jumat'};

        let html = '';
        let modalHtml = '';

        schedules.forEach(item => {
            let tglEfektif = item.override_tanggal || item.tanggal_rencana_efektif;
            let formattedDate = tglEfektif ? formatDateID(tglEfektif) : '-';
            let isOverride = !!item.override_tanggal;

            html += `
                <tr>
                    <td>
                        <span class="fw-bold" style="color:var(--text-dark);">${item.nama_area}</span>
                        <div style="font-size:0.7rem; color:#64748b;"><code>${item.sheet_kka}</code></div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border">Hari ${item.hari_rencana} (${hariMap[item.hari_rencana] || '-'})</span>
                    </td>
                    <td class="text-center">
                        <span style="font-weight:600;">${formattedDate}</span>
                        ${isOverride ? '<br><span class="badge bg-warning text-dark" style="font-size:0.65rem;">Override</span>' : ''}
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary">${item.menit_rencana}m</span>
                    </td>
                    <td class="text-center">
                        <span class="badge" style="background-color: ${item.badge_color || '#3b82f6'}; color:#fff; font-size:0.72rem;">
                            ${item.calculated_status || item.status_minggu_ini || 'TERJADWAL'}
                        </span>
                    </td>
                </tr>
            `;

            modalHtml += `
                <tr>
                    <td>
                        <strong>${item.nama_area}</strong>
                        <div style="font-size:0.72rem; color:#64748b;"><code>${item.sheet_kka}</code></div>
                    </td>
                    <td>
                        <select id="sel_hari_${item.id}" class="form-select form-select-sm" ${userRole === 'ra' || userRole === 'admin' ? '' : 'disabled'}>
                            <option value="1" ${item.hari_rencana == 1 ? 'selected' : ''}>Hari 1 (Senin)</option>
                            <option value="2" ${item.hari_rencana == 2 ? 'selected' : ''}>Hari 2 (Selasa)</option>
                            <option value="3" ${item.hari_rencana == 3 ? 'selected' : ''}>Hari 3 (Rabu)</option>
                            <option value="4" ${item.hari_rencana == 4 ? 'selected' : ''}>Hari 4 (Kamis)</option>
                            <option value="5" ${item.hari_rencana == 5 ? 'selected' : ''}>Hari 5 (Jumat)</option>
                        </select>
                    </td>
                    <td>
                        <input type="date" id="input_override_${item.id}" class="form-control form-control-sm" 
                            value="${item.override_tanggal ? item.override_tanggal.substring(0,10) : ''}" 
                            min="${item.start_date ? item.start_date.substring(0,10) : ''}" 
                            max="${item.end_date ? item.end_date.substring(0,10) : ''}" 
                            ${userRole === 'ra' || userRole === 'admin' ? '' : 'disabled'}>
                        <small class="text-muted" style="font-size:0.68rem;">Kosongkan jika sesuai hari bawaan</small>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary">${item.menit_rencana} Menit</span>
                    </td>
                    <td class="text-center">
                        ${userRole === 'ra' || userRole === 'admin' ? `
                        <button type="button" class="btn btn-sm btn-primary" onclick="submitScheduleUpdate(${item.id})">
                            <i class="bi bi-save me-1"></i> Simpan
                        </button>
                        ` : '<span class="text-muted fs-7">Hanya Baca</span>'}
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        if (modalTbody) modalTbody.innerHTML = modalHtml;
    }

    function openJadwalModal() {
        const myModal = new bootstrap.Modal(document.getElementById('modalJadwalMingguan'));
        myModal.show();
    }

    function submitScheduleUpdate(id) {
        const hari = document.getElementById(`sel_hari_${id}`).value;
        const override = document.getElementById(`input_override_${id}`).value;
        const jmlAuditor = document.getElementById('inputJumlahAuditor').value;

        axios.post(`${baseUrl}/kontrol-mingguan/update`, {
            id: id,
            hari_rencana: hari,
            override_tanggal: override,
            jumlah_auditor: jmlAuditor,
            _token: csrfToken
        })
        .then(response => {
            if (response.data.status === 'success') {
                alert(response.data.message);
                currentKapasitasData = response.data.data;
                renderKapasitasUI(currentKapasitasData);
            }
        })
        .catch(err => {
            const msg = err.response && err.response.data && err.response.data.message 
                ? err.response.data.message 
                : 'Gagal memperbarui jadwal.';
            alert('Perhatian: ' + msg);
        });
    }

    function formatDateID(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    // =========================================================================
    // FITUR KKA CONTROL & INTEGRASI (11 AREA REVIEW & KESIAPAN TAHAP 5 - SOP 04.2)
    // =========================================================================
    let currentControlIntegrasiData = null;

    function loadControlIntegrasi() {
        let params = new URLSearchParams();
        @if(request()->has('kode_unit'))
        params.append('kode_unit', '{{ request('kode_unit') }}');
        @elseif(isset($selectedKodeUnit))
        params.append('kode_unit', '{{ $selectedKodeUnit }}');
        @endif
        @if(request()->has('cabang'))
        params.append('cabang_id', '{{ request('cabang') }}');
        @elseif(request()->has('cabang_id'))
        params.append('cabang_id', '{{ request('cabang_id') }}');
        @elseif(isset($selectedCabangId))
        params.append('cabang_id', '{{ $selectedCabangId }}');
        @endif
        @if(request()->has('tanggal'))
        params.append('tanggal', '{{ request('tanggal') }}');
        @endif

        let qs = params.toString();
        let url = `${baseUrl}/control-integrasi` + (qs ? `?${qs}` : '');

        const tbody = document.getElementById('ciTableBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat rekapitulasi 11 Area KKA...</td></tr>`;
        }

        axios.get(url)
            .then(response => {
                if (response.data.status === 'success') {
                    currentControlIntegrasiData = response.data.data;
                    renderControlIntegrasiUI(currentControlIntegrasiData);
                }
            })
            .catch(err => {
                console.error('Gagal memuat KKA Control & Integrasi:', err);
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Gagal memuat data KKA Control & Integrasi.</td></tr>`;
                }
            });
    }

    function renderControlIntegrasiUI(data) {
        if (!data) return;

        // Info Unit
        const unitElem = document.getElementById('ciUnitText');
        if (unitElem) unitElem.innerText = `${data.kode_unit} - ${data.nama_cabang}`;

        // 1. Header Summary Cards
        const hs = data.header_summary;
        if (hs) {
            document.getElementById('ciDalamLingkup').innerText = hs.kka_dalam_lingkup;
            document.getElementById('ciTersedia').innerText = `${hs.kka_tersedia} / ${hs.kka_dalam_lingkup}`;
            document.getElementById('ciSelesai').innerText = `${hs.kka_selesai} / ${hs.kka_dalam_lingkup}`;
            
            // Status Tahap 4 Badge
            const t4Badge = document.getElementById('ciStatusTahap4Badge');
            if (t4Badge) {
                t4Badge.innerText = hs.status_tahap_4;
                if (hs.status_tahap_4 === 'SIAP TAHAP 5') {
                    t4Badge.className = 'badge bg-success text-white px-2 py-1';
                } else {
                    t4Badge.className = 'badge bg-warning text-dark px-2 py-1';
                }
            }

            // Kesiapan Tahap 5 Badge
            const t5Badge = document.getElementById('ciKesiapanTahap5Badge');
            if (t5Badge) {
                t5Badge.innerText = hs.kesiapan_tahap_5;
                if (hs.kesiapan_tahap_5 === 'SIAP TAHAP 5') {
                    t5Badge.className = 'badge bg-success text-white px-2 py-1';
                } else if (hs.kesiapan_tahap_5.includes('TIDAK LENGKAP')) {
                    t5Badge.className = 'badge bg-danger text-white px-2 py-1';
                } else {
                    t5Badge.className = 'badge bg-warning text-dark px-2 py-1';
                }
            }

            // Badge Tab
            const badgeTab = document.getElementById('badgeTabControlStatus');
            if (badgeTab) {
                badgeTab.innerText = `${hs.kka_selesai}/${hs.kka_dalam_lingkup} Selesai`;
            }
        }

        // 2. Render Tabel 11 Baris (8 Kolom Terkonsolidasi - Fit to Screen)
        const tbody = document.getElementById('ciTableBody');
        if (tbody) {
            tbody.innerHTML = '';

            (data.rows || []).forEach(row => {
                let statusBadgeClass = 'badge-status-belum';
                if (row.status_kka === 'SELESAI') statusBadgeClass = 'badge-status-selesai';
                else if (row.status_kka === 'DALAM PENGUJIAN') statusBadgeClass = 'badge-status-pengujian';

                let actionBadgeClass = (row.tindak_lanjut === 'LANJUT TAHAP 5') ? 'badge-action-lanjut' : 'badge-action-review';

                let statusBebanHtml = '<span class="text-muted" style="font-size:0.68rem;">—</span>';
                if (row.status_beban !== '-') {
                    if (row.status_beban === 'DALAM KAPASITAS') {
                        statusBebanHtml = '<span class="badge" style="background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; font-size:0.65rem;">DALAM KAPASITAS</span>';
                    } else {
                        statusBebanHtml = '<span class="badge" style="background:#fef2f2; color:#dc2626; border:1px solid #fecaca; font-size:0.65rem;">LEBIH BEBAN</span>';
                    }
                }

                let samplingBlock = '';
                if (row.is_sampling) {
                    samplingBlock = `
                        <div style="font-size:0.68rem; line-height:1.25;">
                            <div class="text-muted">Pop: <strong>${row.populasi_eligible}</strong> | Kdd: <strong>${row.kandidat_risiko}</strong></div>
                            <div class="text-muted">Visit: <strong>${row.kapasitas_visit_area}</strong> | KKA: <strong>${row.masuk_kka}</strong></div>
                            <div class="mt-1">${statusBebanHtml}</div>
                        </div>
                    `;
                } else {
                    samplingBlock = `
                        <div class="text-center">
                            <span class="badge bg-light text-muted border" style="font-size:0.65rem;">KKA Fisik</span>
                            <div class="text-muted" style="font-size:0.65rem; margin-top:2px;">Langsung 1x/mgg</div>
                        </div>
                    `;
                }

                const pctValid = (row.sampel_terpilih > 0) ? Math.round((row.sampel_diuji / row.sampel_terpilih) * 100) : 0;

                const tr = `<tr>
                    <td class="text-center font-monospace" style="color:var(--text-muted); font-size:0.75rem;">${row.no}</td>
                    <td>
                        <div class="fw-bold text-dark" style="font-size:0.8rem; line-height:1.2;">${row.area_review}</div>
                        <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                            ${row.is_sampling ? '<span class="badge" style="background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; font-size:0.62rem;">Sampling</span>' : '<span class="badge" style="background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; font-size:0.62rem;">Fisik Mingguan</span>'}
                            <code style="font-size:0.68rem; color:#475569; background:#f1f5f9; padding:1px 4px; border-radius:3px;">${row.nama_sheet}</code>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="${statusBadgeClass}">${row.status_kka}</span>
                        <div class="text-muted" style="font-size:0.68rem; margin-top:2px;">Tahap ${row.tahap_build}</div>
                    </td>
                    <td class="text-center">
                        <div class="fw-bold" style="font-size:0.8rem; color:var(--text-dark);">${row.sampel_diuji} <span class="text-muted fw-normal">/ ${row.sampel_terpilih}</span></div>
                        <div class="progress my-1" style="height: 4px; border-radius: 2px; background: #e2e8f0;">
                            <div class="progress-bar ${pctValid === 100 ? 'bg-success' : 'bg-primary'}" role="progressbar" style="width: ${pctValid}%;"></div>
                        </div>
                        <small class="text-muted" style="font-size:0.67rem;">Valid: ${row.komponen_valid}</small>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <span class="badge ${row.exception > 0 ? 'bg-danger text-white' : 'bg-light text-muted border'}" style="font-size:0.68rem;" title="Total Exception">Ex: ${row.exception}</span>
                            <span class="badge ${row.tinggi_kritis > 0 ? 'bg-danger text-white' : 'bg-light text-muted border'}" style="font-size:0.68rem;" title="Tinggi / Kritis">Kritis: ${row.tinggi_kritis}</span>
                        </div>
                        ${row.klarifikasi_pending > 0 ? `<div class="text-warning fw-semibold mt-1" style="font-size:0.68rem;"><i class="bi bi-clock me-1"></i>${row.klarifikasi_pending} Pending</div>` : '<div class="text-muted" style="font-size:0.65rem; margin-top:2px;">0 Pending</div>'}
                    </td>
                    <td style="border-right: 2px solid #cbd5e1;">
                        <div style="font-size:0.72rem; line-height:1.3; color:#334155; word-break:break-word;">${row.kontrol_khas}</div>
                    </td>
                    <td style="border-right: 2px solid #cbd5e1;">
                        ${samplingBlock}
                    </td>
                    <td class="text-center">
                        <span class="${actionBadgeClass}">${row.tindak_lanjut}</span>
                    </td>
                </tr>`;
                tbody.innerHTML += tr;
            });
        }

        // 3. Panel Bawah: Integrasi Tahap 5
        const int5 = data.integrasi_tahap_5;
        if (int5) {
            document.getElementById('ciPerluKlarifikasi').innerText = int5.perlu_klarifikasi;
            document.getElementById('ciKlarifikasiSelesai').innerText = int5.klarifikasi_selesai;
            document.getElementById('ciEligibleTemuan').innerText = int5.eligible_temuan;
            document.getElementById('ciTemuanValid').innerText = int5.temuan_valid;

            const badgeT5 = document.getElementById('ciStatusTahap5Badge');
            if (badgeT5) {
                badgeT5.innerText = int5.status_tahap_5;
                if (int5.status_tahap_5 === 'SIAP LAPORAN') {
                    badgeT5.className = 'badge bg-success text-white px-3 py-2';
                } else {
                    badgeT5.className = 'badge bg-warning text-dark px-3 py-2';
                }
            }
        }
    }

    // FUNGSI JALANKAN ULANG SAMPLING SOP 02
    function triggerRerunSampling() {
        const unitTarget = currentKodeUnit || '{{ $kodeUnitAktif ?? "" }}';
        
        Swal.fire({
            title: 'Jalankan Ulang Sampling SOP 02?',
            html: `<div class="text-start" style="font-size: 0.85rem; color: #475569;">
                    <p class="mb-2">Sistem akan menyaring ulang transaksi Moderate &amp; High pada unit <strong>${unitTarget}</strong> mengacu pada formula <strong>SOP 02 (Parameter_Sampling_SOP02)</strong>:</p>
                    <ul class="ps-3 mb-0">
                        <li>Batas kapasitas KKA: <strong>Maksimum 500 sampel / area</strong></li>
                        <li>High Risk diprioritaskan berdasarkan nominal tertinggi</li>
                        <li>Certainty &amp; Stratified/Random sampling untuk Moderate</li>
                        <li><strong>Data yang sudah diuji RA aman</strong> (tidak akan terhapus)</li>
                        <li>Data non-sampel berlebih akan dibersihkan dari KKA</li>
                    </ul>
                   </div>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1e3a8a',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-play-fill me-1"></i> Ya, Jalankan Sampling',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Sampling SOP 02...',
                    html: '<div class="py-2"><div class="spinner-border text-primary mb-2"></div><div style="font-size: 0.85rem; color: #64748b;">Menyaring populasi, mengamankan temuan valid, dan menyeimbangkan kapasitas KKA...</div></div>',
                    showConfirmButton: false,
                    allowOutsideClick: false
                });

                axios.post('{{ route("daily-register.rerun-sampling") }}', {
                    kode_unit: unitTarget,
                    _token: '{{ csrf_token() }}'
                }).then(res => {
                    if (res.data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sampling Berhasil!',
                            html: `<div style="font-size: 0.88rem; color: #334155;">
                                    ${res.data.message}<br><br>
                                    <span class="badge bg-primary px-3 py-2 fs-6">${res.data.total_sampel} Total Sampel KKA Aktif</span>
                                   </div>`,
                            confirmButtonColor: '#1e3a8a',
                            confirmButtonText: 'Segarkan Halaman'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'Informasi',
                            text: res.data.message || 'Tidak ada data temuan yang disampling.'
                        });
                    }
                }).catch(err => {
                    console.error('Error rerun sampling:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menjalankan Sampling',
                        text: err.response?.data?.message || err.message || 'Terjadi kesalahan sistem saat memproses sampling.'
                    });
                });
            }
        });
    }

    // =========================================================================
    // MODAL TAMBAH MANDIRI PEMERIKSAAN KAS / KAS KECIL
    // =========================================================================
    function openModalTambahMandiri() {
        const modalEl = document.getElementById('modalTambahMandiriKas');
        if (!modalEl) return;

        const dateInp = document.getElementById('mandiriTanggal');
        if (dateInp) {
            dateInp.value = currentActiveDate || '{{ request("tanggal") ?? date("Y-m-d") }}';
        }
        const unitInp = document.getElementById('mandiriKodeUnit');
        if (unitInp) {
            const selAdmin = document.getElementById('selectAdminCabang');
            const activeUnit = (selAdmin && selAdmin.value) ? selAdmin.value : (currentActiveUnit || '{{ $selectedKodeUnit ?? auth()->user()->unit_code ?? "001" }}');
            unitInp.value = activeUnit;
        }

        handleMandiriSubAreaChange();

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        } else if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
            $(modalEl).modal('show');
        }
    }

    function handleMandiriSubAreaChange() {
        const sel = document.getElementById('mandiriSubArea')?.value;
        const descInp = document.getElementById('mandiriDeskripsi');
        if (!descInp) return;
        if (sel === 'A2') {
            descInp.value = 'Pemeriksaan Fisik Mandiri Kas Kecil (Sub-Area A2)';
        } else if (sel === 'A3') {
            descInp.value = 'Pemeriksaan Fisik Mandiri Kas / Khasanah (Sub-Area A3)';
        } else {
            descInp.value = 'Pemeriksaan Fisik Mandiri Kas Kecil dan Kas / Khasanah (A2 & A3)';
        }
    }

    function submitTambahMandiri(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitMandiri');
        if (btn) btn.disabled = true;

        const payload = {
            kode_unit: document.getElementById('mandiriKodeUnit')?.value,
            tanggal_data: document.getElementById('mandiriTanggal')?.value,
            sub_area: document.getElementById('mandiriSubArea')?.value,
            nominal_terkait: document.getElementById('mandiriNominal')?.value || 0,
            no_arsip: document.getElementById('mandiriNoArsip')?.value || '',
            deskripsi: document.getElementById('mandiriDeskripsi')?.value || '',
            _token: '{{ csrf_token() }}'
        };

        axios.post(`${baseUrl}/store-mandiri`, payload)
            .then(res => {
                const modalEl = document.getElementById('modalTambahMandiriKas');
                if (modalEl) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    } else if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
                        $(modalEl).modal('hide');
                    }
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.data.message || 'Pemeriksaan mandiri berhasil ditambahkan.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    alert(res.data.message || 'Pemeriksaan mandiri berhasil ditambahkan.');
                }

                // Segarkan data dan buka review jika berhasil
                loadData();
                if (res.data.finding && res.data.finding.id) {
                    setTimeout(() => {
                        openDetailView(res.data.finding.id);
                    }, 600);
                }
            })
            .catch(err => {
                const msg = err.response?.data?.message || 'Gagal menambahkan pemeriksaan mandiri.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                } else {
                    alert(msg);
                }
            })
            .finally(() => {
                if (btn) btn.disabled = false;
            });
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(strtolower(auth()->user()->role ?? '') !== 'ra')
<!-- MODAL EDIT JADWAL KONTROL MINGGUAN -->
<div class="modal fade" id="modalJadwalMingguan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
            <div class="modal-header py-3 px-4" style="background: #f8fafc; border-bottom: 1px solid var(--border-color);">
                <h6 class="modal-title fw-bold" style="color: var(--primary-blue);">
                    <i class="bi bi-calendar-range me-2"></i> Pengaturan Jadwal 5 KKA Fisik (Kontrol_Mingguan)
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info py-2 px-3 mb-3" style="font-size: 0.82rem;">
                    <i class="bi bi-info-circle me-1"></i> Sesuai SOP 04.2, 5 KKA Fisik dilakukan minimal 1x seminggu. RA dapat menyesuaikan Hari Rencana (1–5) atau menetapkan Override Tanggal (dalam rentang hari kerja Senin s.d. Jumat pada minggu berjalan).
                </div>
                <div class="mb-3 d-flex align-items-center gap-3">
                    <label class="fw-bold mb-0" style="font-size: 0.85rem;">Jumlah Auditor Penugasan:</label>
                    <input type="number" id="inputJumlahAuditor" class="form-control form-control-sm" style="width: 80px;" min="1" value="1" {{ in_array(strtolower(auth()->user()->role), ['ra', 'admin']) ? '' : 'disabled' }}>
                    <small class="text-muted">Kapasitas harian: 420 menit × jumlah auditor.</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light text-center" style="font-size: 0.78rem;">
                            <tr>
                                <th>KKA Fisik</th>
                                <th style="width: 150px;">Hari Rencana</th>
                                <th style="width: 170px;">Override Tanggal</th>
                                <th style="width: 100px;">Beban Waktu</th>
                                <th style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="modalJadwalTableBody">
                            <!-- Populated dynamically by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2 px-4" style="background: #f8fafc; border-top: 1px solid var(--border-color);">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- MODAL TAMBAH MANDIRI PEMERIKSAAN KAS / KAS KECIL -->
<div class="modal fade" id="modalTambahMandiriKas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header py-3 px-4" style="background: #f8fafc; border-bottom: 1px solid var(--border-color);">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-5 text-primary"><i class="bi bi-cash-coin"></i></span>
                    <h6 class="modal-title fw-bold" style="color: var(--primary-blue); margin-bottom: 0;">
                        Tambah Pemeriksaan Kas / Kas Kecil Mandiri
                    </h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahMandiriKas" onsubmit="submitTambahMandiri(event)">
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 mb-3" style="font-size: 0.8rem; border-left: 4px solid #0284c7; background: #f0f9ff;">
                        <i class="bi bi-info-circle-fill me-1 text-info"></i> Gunakan form ini untuk mencatat sampel pemeriksaan fisik Kas Kecil (A2) atau Kas/Khasanah (A3) pada tanggal aktif ketika tidak ada mutasi teller terjaring dari DUMP CBS.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted mb-1">Sub-Area Pemeriksaan <span class="text-danger">*</span></label>
                        <select id="mandiriSubArea" class="form-select form-select-sm" required onchange="handleMandiriSubAreaChange()">
                            <option value="A2" selected>Kas Kecil (Sub-Area A2 — Register, Bukti, & Fisik Kas Kecil)</option>
                            <option value="A3">Kas / Khasanah (Sub-Area A3 — BA Opname & Dual Control Khasanah)</option>
                            <option value="BOTH">Kedua Sub-Area (Kas Kecil & Kas Khasanah — A2 & A3)</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted mb-1">Tanggal Pemeriksaan <span class="text-danger">*</span></label>
                            <input type="date" id="mandiriTanggal" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted mb-1">Kode Unit / Cabang <span class="text-danger">*</span></label>
                            <input type="text" id="mandiriKodeUnit" class="form-control form-control-sm bg-light" readonly required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted mb-1">Nominal Terkait / Plafon Kas (Rp)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light fw-bold">Rp</span>
                            <input type="number" id="mandiriNominal" class="form-control form-control-sm text-end" min="0" step="any" placeholder="0" value="0">
                        </div>
                        <small class="text-muted" style="font-size: 0.72rem;">Isi dengan saldo/plafon kas kecil atau saldo kas besar yang diuji.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted mb-1">Nomor Berita Acara / Arsip <small class="text-muted fw-normal">(Opsional)</small></label>
                        <input type="text" id="mandiriNoArsip" class="form-control form-control-sm" placeholder="Contoh: BA-KK-20261105-001">
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold small text-muted mb-1">Uraian / Deskripsi Pemeriksaan</label>
                        <textarea id="mandiriDeskripsi" class="form-control form-control-sm" rows="2" placeholder="Uraian pemeriksaan mandiri..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2 px-4" style="background: #f8fafc; border-top: 1px solid var(--border-color);">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold" id="btnSubmitMandiri">
                        <i class="bi bi-save me-1"></i> Simpan Pemeriksaan Mandiri
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection