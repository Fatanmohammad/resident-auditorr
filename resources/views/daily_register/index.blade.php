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

    .summary-cards-wrapper { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.85rem; margin-bottom: 1.25rem; }
    @media (max-width: 992px) {
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

    <!-- 4 SUMMARY CARDS -->
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

    <!-- FILTER KHUSUS RIWAYAT (Hanya tampil saat mode riwayat aktif) -->
    <div id="riwayatFilterCard" class="filter-card" style="display: none;">
        <div style="font-weight: 700; color: var(--primary-blue); margin-bottom: 1rem; font-size: 0.9rem;">
            <i class="bi bi-funnel"></i> Filter Pencarian Arsip Riwayat Selesai
        </div>
        <div class="filter-grid">
            <div>
                <label class="filter-label">Cari Kata Kunci / No Referensi / User</label>
                <input type="text" id="filterKeyword" class="filter-control" placeholder="Ketik kata kunci...">
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
            <div>
                <button type="button" class="btn-filter" onclick="applyCustomFilter()"><i class="bi bi-search"></i> Filter</button>
            </div>
            <div>
                <button type="button" class="btn-reset" onclick="resetCustomFilter()">Reset</button>
            </div>
        </div>
    </div>

    <div class="kka-tabs" id="kkaTabContainer">
        @if(!$isSubmenu)
        <div class="kka-tab-item active" onclick="switchTab(this, 'KKA_Teller_Kas', 'KKA Teller & Kas')">
            <i class="bi bi-cash-stack"></i> KKA Teller & Kas <span class="badge-count tab-teller">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA_DPK_Layanan', 'KKA DPK & Layanan Nasabah')">
            <i class="bi bi-wallet2"></i> KKA DPK & Layanan <span class="badge-count tab-dpk">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA_Kredit', 'KKA Kredit')">
            <i class="bi bi-credit-card-2-front"></i> KKA Kredit <span class="badge-count tab-kredit">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA_Biaya_Beban', 'KKA Biaya & Beban')">
            <i class="bi bi-receipt"></i> KKA Biaya & Beban <span class="badge-count tab-biaya">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA_Kartu_ATM', 'KKA Kartu ATM')">
            <i class="bi bi-credit-card"></i> KKA Kartu ATM <span class="badge-count tab-atm">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA_Pengaduan', 'KKA Pengaduan')">
            <i class="bi bi-chat-square-dots"></i> KKA Pengaduan <span class="badge-count tab-pengaduan">0</span>
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
            <div style="padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
                <div style="font-weight: 700; color: var(--primary-blue); font-size: 0.92rem;" id="tableTitle">KKA Teller & Kas</div>
                <div class="badge" style="background:#e0e7ff; color:var(--primary-blue); font-size:0.75rem; padding: 0.35rem 0.75rem; border-radius: 12px;">
                    <span id="totalDataCount">0</span> Data
                </div>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 4%;">NO</th>
                            <th style="text-align: left; width: 9%;">TANGGAL</th>
                            <th style="text-align: center; width: 6%;">UNIT</th>
                            <th style="text-align: left; width: 13%;">NO REFERENSI</th>
                            <th style="text-align: left; width: 12%;">USER / MAKER</th>
                            <th style="text-align: center; width: 7%;">KODE TRX</th>
                            <th style="text-align: right; width: 13%;">NOMINAL</th>
                            <th style="text-align: left; width: 22%;">DESKRIPSI / NARASI</th>
                            <th style="text-align: center; width: 7%;">RISK</th>
                            <th style="text-align: center; width: 7%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="kkaTableBody">
                        <tr>
                            <td colspan="10">
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

    <!-- SECTION 2: INPUTAN RESIDENT AUDITOR (RA) -->
    <div class="section-card ra">
        <div class="section-card-header" style="justify-content: space-between;">
            <div><i class="bi bi-pencil-square"></i> 2. Hasil Pengujian &amp; Klarifikasi <span style="font-weight:normal; font-size:0.8rem; margin-left:0.3rem;">(Porsi Resident Auditor)</span></div>
            <span id="badge_ra_lock" style="font-size:0.75rem; background:#fff; padding:0.2rem 0.6rem; border-radius:12px; border:1px solid #fde68a; color:var(--text-muted);">Mode Edit</span>
        </div>
        <div class="section-card-body">
            <form id="formKkaRa" enctype="multipart/form-data">
                <input type="hidden" id="ra_kka_id">

                <div class="custom-grid-2">
                    <div>
                        <label class="custom-form-label">Bukti / Referensi Dokumen</label>
                        <input type="text" class="custom-form-control field-ra" id="bukti_referensi" placeholder="Misal: Surat No. 123/LHA/2026" @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif>
                    </div>
                    <div>
                        <label class="custom-form-label">Jenis Exception (Versi RA)</label>
                        <input type="text" class="custom-form-control field-ra" id="jenis_exception_ra" placeholder="Klasifikasi penyimpangan hasil pengujian" @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif>
                    </div>
                </div>

                <div class="custom-grid-2">
                    <div>
                        <label class="custom-form-label">Hasil Pengujian Lapangan</label>
                        <textarea class="custom-form-control field-ra" id="hasil_uji" rows="3" placeholder="Rincian fakta temuan hasil konfirmasi..." @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif></textarea>
                    </div>
                    <div>
                        <label class="custom-form-label">Klarifikasi Unit Kerja / Cabang</label>
                        <textarea class="custom-form-control field-ra" id="klarifikasi_unit" rows="3" placeholder="Tanggapan dari pihak cabang..." @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif></textarea>
                    </div>
                </div>

                <div class="custom-grid-4">
                    <div>
                        <label class="custom-form-label">Skor Dampak (1–5)</label>
                        <select class="custom-form-control field-ra" id="skor_dampak" onchange="hitungSkorRisiko()" @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif>
                            <option value="1">1 - Sangat Rendah</option>
                            <option value="2">2 - Rendah</option>
                            <option value="3">3 - Sedang</option>
                            <option value="4">4 - Tinggi</option>
                            <option value="5">5 - Sangat Tinggi</option>
                        </select>
                    </div>
                    <div>
                        <label class="custom-form-label">Skor Kemungkinan (1–5)</label>
                        <select class="custom-form-control field-ra" id="skor_kemungkinan" onchange="hitungSkorRisiko()" @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif>
                            <option value="1">1 - Sangat Jarang</option>
                            <option value="2">2 - Jarang</option>
                            <option value="3">3 - Kadang-Kadang</option>
                            <option value="4">4 - Sering</option>
                            <option value="5">5 - Sangat Sering</option>
                        </select>
                    </div>
                    <div>
                        <label class="custom-form-label">Critical Trigger</label>
                        <select class="custom-form-control field-ra" id="critical_trigger" onchange="hitungSkorRisiko()" @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif>
                            <option value="Tidak">Tidak</option>
                            <option value="Ya">Ya (Otomatis High)</option>
                        </select>
                    </div>
                    <div>
                        <label class="custom-form-label">Skor &amp; Risk Final</label>
                        <div class="custom-form-control" style="background:#f8fafc; display:flex; align-items:center; gap:0.5rem; height: calc(1.5em + 1.2rem + 2px);">
                            <span style="font-weight:700; font-size:1.1rem;" id="text_skor_final">0</span> 
                            <span style="color:var(--text-muted);">—</span> 
                            <span id="badge_risk_final" style="padding:0.3rem 0.6rem; border-radius:6px; font-weight:600; font-size:0.8rem; background:#dcfce7; color:#16a34a; border:1px solid #bbf7d0;">Low</span>
                        </div>
                    </div>
                </div>

                <div class="custom-grid-3">
                    <div>
                        <label class="custom-form-label">Perlu Onsite Inspection?</label>
                        <select class="custom-form-control field-ra" id="perlu_onsite" @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif>
                            <option value="0">Tidak Perlu</option>
                            <option value="1">Perlu Pemeriksaan Onsite</option>
                        </select>
                    </div>
                    <div>
                        <label class="custom-form-label">Upload Bukti Transaksi (PDF)</label>
                        <input type="file" class="custom-form-control field-ra" id="file_bukti" accept=".pdf" @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif>
                    </div>
                    <div>
                        <label class="custom-form-label">Tanggal Ditemukan / Diuji</label>
                        <input type="date" class="custom-form-control field-ra" id="tanggal_ditemukan" @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label class="custom-form-label">Simpulan RA</label>
                    <textarea class="custom-form-control field-ra" id="simpulan_ra" rows="2" placeholder="Simpulan akhir pemeriksaan offsite..." @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif></textarea>
                </div>

                @if(strtolower(auth()->user()->role) === 'ra')
                <div style="display:flex; justify-content:flex-end; gap:0.8rem; border-top:1px solid var(--border-color); padding-top:1rem;">
                    <button type="button" class="btn-back" onclick="closeDetailView()">Batal</button>
                    <button type="button" style="background:#f59e0b; color:#fff; border:none; padding:0.4rem 1.5rem; border-radius:6px; font-weight:600; cursor:pointer;" onclick="submitRaForm()">
                        <i class="bi bi-send-fill" style="margin-right:0.3rem;"></i> Submit Review
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>

    <!-- SECTION 3: INPUTAN ADMIN / PIMSIE (REVIEWER) -->
    <div class="section-card admin">
        <div class="section-card-header" style="justify-content: space-between;">
            <div><i class="bi bi-clipboard-check"></i> 3. Review &amp; Keputusan Pengawasan <span style="font-weight:normal; font-size:0.8rem; margin-left:0.3rem;">(Porsi Admin / Pimsie)</span></div>
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
                        <label class="custom-form-label">Status Review KKA <span style="color:#dc2626;">*</span></label>
                        <select class="custom-form-control field-admin" id="status_review" @if(strtolower(auth()->user()->role) === 'ra') disabled @endif>
                            <option value="">-- Pilih Status Review --</option>
                            <option value="Approved">Approved (Selesai)</option>
                            <option value="Need Revision">Need Revision (Minta RA Perbaiki)</option>
                            <option value="Rejected">Rejected (Ditolak)</option>
                        </select>
                    </div>
                    <div>
                        <label class="custom-form-label">Keputusan Eskalasi</label>
                        <select class="custom-form-control field-admin" id="keputusan_eskalasi" @if(strtolower(auth()->user()->role) === 'ra') disabled @endif>
                            <option value="Tidak">Tidak Dieskalasi</option>
                            <option value="Eskalasi Kadiv">Eskalasi ke Kadiv SKAI</option>
                            <option value="Eskalasi Direksi">Eskalasi ke Direksi</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label class="custom-form-label">Catatan Reviewer / Pimsie <span style="color:#dc2626;">*</span></label>
                    <textarea class="custom-form-control field-admin" id="catatan_reviewer" rows="2" placeholder="Instruksi revisi, arahan tambahan, atau catatan persetujuan KKA..." @if(strtolower(auth()->user()->role) === 'ra') disabled @endif></textarea>
                </div>

                @if(in_array(strtolower(auth()->user()->role), ['admin', 'korwas', 'pimsie']))
                <div style="display:flex; justify-content:flex-end; gap:0.8rem; border-top:1px solid var(--border-color); padding-top:1rem;">
                    <button type="button" class="btn-back" onclick="closeDetailView()">Batal</button>
                    <button type="button" style="background:#10b981; color:#fff; border:none; padding:0.4rem 1.5rem; border-radius:6px; font-weight:600; cursor:pointer;" onclick="submitAdminForm()">
                        <i class="bi bi-check-circle-fill" style="margin-right:0.3rem;"></i> Submit Keputusan
                    </button>
                </div>
                @endif
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

    function changeAdminCabang(unitCode) {
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
        } else {
            document.getElementById('tableTitle').innerText = tabName;
        }
        
        loadData();
    }

    // Pindah ke Tab KKA Control & Integrasi (SOP 04.2)
    function switchTabControlIntegrasi(clickedElement) {
        const allTabs = document.querySelectorAll('.kka-tab-item');
        allTabs.forEach(tab => tab.classList.remove('active'));

        if (clickedElement) clickedElement.classList.add('active');

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

    // Ambil Data via API
    function loadData(page = 1) {
        const tbody = document.getElementById('kkaTableBody');
        tbody.innerHTML = `<tr><td colspan="10"><div class="empty-state" style="text-align: center; padding: 3rem 0; color: var(--text-muted);"><i class="bi bi-hourglass-split" style="font-size: 2rem; display: block; margin-bottom: 1rem;"></i><p>Memuat data...</p></div></td></tr>`;

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
                
                document.getElementById('totalDataCount').innerText = globalFindings.length;
                document.getElementById('statTotalException').innerText = globalFindings.length;
                
                let totalNominal = globalFindings.reduce((acc, curr) => acc + (parseFloat(curr.nominal_terkait) || 0), 0);
                document.getElementById('statTotalNominal').innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(totalNominal);
                
                let highRiskCount = globalFindings.filter(x => (x.risk_awal || '').toLowerCase() === 'high').length;
                document.getElementById('statHighRisk').innerText = highRiskCount;
                
                let selesaiCount = globalFindings.filter(x => x.status_review === 'Approved').length;
                document.getElementById('statSelesaiReview').innerText = selesaiCount;

                const summary = response.data.summary || {};
                const tabCounts = summary.tab_counts || {};
                
                // Update Badge di masing-masing Tab
                const tabMapping = {
                    'KKA_Teller_Kas': '.tab-teller',
                    'KKA_DPK_Layanan': '.tab-dpk',
                    'KKA_Kredit': '.tab-kredit',
                    'KKA_Biaya_Beban': '.tab-biaya',
                    'KKA_Kartu_ATM': '.tab-atm',
                    'KKA_Pengaduan': '.tab-pengaduan'
                };
                
                for (const [sheet, selector] of Object.entries(tabMapping)) {
                    const badge = document.querySelector(selector);
                    if (badge) {
                        badge.innerText = tabCounts[sheet] || 0;
                    }
                }

                processDataDisplay();
            })
            .catch(error => {
                tbody.innerHTML = `<tr><td colspan="10"><div class="empty-state" style="text-align: center; padding: 3rem 0; color: #dc2626;"><i class="bi bi-exclamation-triangle" style="font-size: 2rem; display: block; margin-bottom: 1rem;"></i><p>Gagal memuat data.</p></div></td></tr>`;
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

    // Proses Data untuk Ditampilkan (Filter Arsip & Pencarian)
    function processDataDisplay() {
        let dataToRender = globalFindings;

        if (isShowingRiwayat) {
            // Hanya ambil yang status review-nya Approved
            dataToRender = dataToRender.filter(x => (x.status_review || '').trim() === 'Approved');

            // Ambil parameter filter tambahan
            const keyword = document.getElementById('filterKeyword').value.toLowerCase();
            const klarifikasi = document.getElementById('filterKlarifikasi').value;

            if (keyword) {
                dataToRender = dataToRender.filter(x => 
                    (x.no_referensi || '').toLowerCase().includes(keyword) ||
                    (x.offsite_id || '').toLowerCase().includes(keyword) ||
                    (x.user_maker || '').toLowerCase().includes(keyword) ||
                    (x.deskripsi || '').toLowerCase().includes(keyword)
                );
            }

            if (klarifikasi) {
                dataToRender = dataToRender.filter(x => (x.status_klarifikasi || '') === klarifikasi);
            }
        }

        renderTable(dataToRender);
    }

    function applyCustomFilter() {
        processDataDisplay();
    }

    function resetCustomFilter() {
        document.getElementById('filterKeyword').value = '';
        document.getElementById('filterKlarifikasi').value = '';
        processDataDisplay();
    }

    // Render Tabel Utama
    function renderTable(data) {
        const tbody = document.getElementById('kkaTableBody');
        tbody.innerHTML = '';

        if(!data || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10"><div class="empty-state" style="text-align: center; padding: 3rem 0; color: var(--text-muted);"><i class="bi bi-inbox" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 1rem;"></i><p>Tidak ada data ditemukan.</p></div></td></tr>`;
            return;
        }

        data.forEach((item, index) => {
            let riskClass = (item.risk_awal || '').toLowerCase() === 'high' ? 'bg-danger text-white' : 'bg-warning text-dark';
            let riskBadge = `<span class="badge ${riskClass}" style="border-radius:12px; padding:0.25rem 0.55rem; font-size:0.72rem;">${item.risk_awal || 'Moderate'}</span>`;
            let nominalStr = item.nominal_terkait > 0 ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(item.nominal_terkait) : '-';
            
            // Pengecekan agar user_maker yang kosong atau strip (-) otomatis tertulis "Sistem / Tidak Tercatat"
            let userMakerDisplay = (!item.user_maker || item.user_maker === '-') ? 'Sistem / Tidak Tercatat' : item.user_maker;
            let deskripsiDisplay = item.deskripsi || item.catatan_rule || item.rincian || '-';
            let deskripsiClean = deskripsiDisplay.replace(/"/g, '&quot;');

            const tr = `<tr>
                <td style="text-align:center; color:var(--text-muted); font-size:0.75rem;">${index + 1}</td>
                <td style="white-space:nowrap; font-size:0.78rem;">${item.tanggal_data || '-'}</td>
                <td style="text-align:center;"><span class="badge bg-light text-dark border" style="border-radius:20px; padding: 0.2rem 0.45rem; font-size:0.7rem;">${item.kode_unit || '-'}</span></td>
                <td style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:0.75rem;" title="${item.no_referensi || item.offsite_id || '-'}">
                    <span class="font-monospace text-primary">${item.no_referensi || item.offsite_id || '-'}</span>
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
                <td style="text-align:center;">
                    <button class="btn-review" onclick="openDetailView(${item.id})">
                        Review <i class="bi bi-chevron-right" style="font-size:0.7rem;"></i>
                    </button>
                </td>
            </tr>`;
            tbody.insertAdjacentHTML('beforeend', tr);
        });
    }

    // FUNGSI TOGGLE KE FULL PAGE DETAIL VIEW (DIPERBARUI AGAR SELALU AMBIL DATA FRESH)
    function openDetailView(id) {
        let item = globalFindings.find(x => x.id == id);
        if(!item) return;

        // Ambil data terbaru langsung dari server berdasarkan ID untuk memastikan data Admin ikut termuat
        axios.get(`${baseUrl}/data?source_sheet=${currentActiveSheetCode}`)
            .then(response => {
                const freshData = response.data.data.data;
                const freshItem = freshData.find(x => x.id == id);
                if(freshItem) {
                    item = freshItem;
                }
                populateDetailForm(item);
            })
            .catch(() => {
                populateDetailForm(item);
            });
    }

    function populateDetailForm(item) {
        // Populate Header & Summary
        document.getElementById('header_nama_sheet').innerText = currentActiveSheet;
        document.getElementById('head_staging_id').innerText = item.offsite_id || item.staging_id || `KKA-${item.id}`;
        document.getElementById('badge_status_atas').innerText = item.status_review || 'Belum Di-review';
        
        document.getElementById('sum_tanggal').innerText = item.tanggal_data || '-';
        document.getElementById('sum_unit').innerText = item.kode_unit || '-';
        document.getElementById('sum_user').innerText = (!item.user_maker || item.user_maker === '-') ? 'Sistem / Tidak Tercatat' : item.user_maker;
        document.getElementById('sum_nominal').innerText = item.nominal_terkait > 0 ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(item.nominal_terkait) : 'Rp 0';
        
        // Custom Badge Generator
        let riskValue = (item.risk_awal || 'Moderate').toLowerCase();
        let badgeBgColor = riskValue === 'high' ? '#fef2f2' : '#fffbeb';
        let badgeBorderColor = riskValue === 'high' ? '#fecaca' : '#fde68a';
        let badgeTextColor = riskValue === 'high' ? '#dc2626' : '#b45309';

        document.getElementById('sum_risk_badge').innerHTML = `<span style="background-color:${badgeBgColor}; color:${badgeTextColor}; border:1px solid ${badgeBorderColor}; border-radius:12px; padding:0.3rem 0.6rem; font-size:0.8rem; font-weight:600;">${item.risk_awal || 'Moderate Risk'}</span>`;
        document.getElementById('sum_deskripsi').innerText = item.deskripsi || item.rincian || '-';

        // Populate Section 1 (System)
        document.getElementById('sys_offsite_id').innerText = item.offsite_id || item.staging_id || `KKA-${item.id}`;
        document.getElementById('sys_tanggal_data').innerText = item.tanggal_data || '-';
        document.getElementById('sys_unit_kerja').innerText = item.kode_unit || '-';
        document.getElementById('sys_nominal_terkait').innerText = document.getElementById('sum_nominal').innerText;
        document.getElementById('sys_source_sheet').innerText = item.source_sheet || currentActiveSheet;
        document.getElementById('sys_risk_awal').innerHTML = document.getElementById('sum_risk_badge').innerHTML;
        document.getElementById('sys_exception_awal').innerText = item.jenis_exception_awal || item.deskripsi || '-';
        
        document.getElementById('sys_catatan_rule').innerText = item.deskripsi || item.catatan_rule || '-';
        document.getElementById('sys_prosedur_uji').innerText = item.prosedur_uji || 'Uji Ketaatan & Keabsahan Dokumen Transaksi Offsite';

        // Populate Section 2 (RA)
        document.getElementById('ra_kka_id').value = item.id;
        document.getElementById('bukti_referensi').value = item.bukti_referensi || '';
        document.getElementById('jenis_exception_ra').value = item.jenis_exception_ra || '';
        document.getElementById('hasil_uji').value = item.hasil_uji || '';
        document.getElementById('klarifikasi_unit').value = item.klarifikasi_unit || '';
        document.getElementById('skor_dampak').value = item.skor_dampak || 1;
        document.getElementById('skor_kemungkinan').value = item.skor_kemungkinan || 1;
        document.getElementById('critical_trigger').value = item.critical_trigger || 'Tidak';
        document.getElementById('perlu_onsite').value = item.perlu_onsite ? 1 : 0;
        document.getElementById('tanggal_ditemukan').value = item.tanggal_ditemukan || '';
        document.getElementById('simpulan_ra').value = item.simpulan_ra || '';

        // Populate Section 3 (Admin)
        document.getElementById('admin_kka_id').value = item.id;
        document.getElementById('status_klarifikasi').value = item.status_klarifikasi || '';
        document.getElementById('perluasan_sampel').value = item.perluasan_sampel ? 1 : 0;
        document.getElementById('keputusan_onsite').value = item.keputusan_onsite || '';
        document.getElementById('status_review').value = item.status_review || '';
        document.getElementById('keputusan_eskalasi').value = item.keputusan_eskalasi || 'Tidak';
        document.getElementById('catatan_reviewer').value = item.catatan_reviewer || '';

        hitungSkorRisiko();

        // Lock Logic based on Role
        const isRa = userRole === 'ra';
        document.querySelectorAll('.field-ra').forEach(el => el.disabled = !isRa);
        document.querySelectorAll('.field-admin').forEach(el => el.disabled = isRa);
        
        document.getElementById('badge_ra_lock').innerText = isRa ? "Mode Edit" : "Read Only";
        document.getElementById('badge_admin_lock').innerText = isRa ? "Read Only" : "Mode Edit";

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

    function hitungSkorRisiko() {
        const dampak = parseInt(document.getElementById('skor_dampak').value) || 1;
        const kemungkinan = parseInt(document.getElementById('skor_kemungkinan').value) || 1;
        const critical = document.getElementById('critical_trigger').value;
        const skor = dampak * kemungkinan;
        let riskCategory = 'Low';
        let badgeBgColor = '#dcfce7';
        let badgeBorderColor = '#bbf7d0';
        let badgeTextColor = '#16a34a';

        if (critical === 'Ya' || skor >= 15) {
            riskCategory = 'High'; 
            badgeBgColor = '#fef2f2'; badgeBorderColor = '#fecaca'; badgeTextColor = '#dc2626';
        } else if (skor >= 6) {
            riskCategory = 'Moderate'; 
            badgeBgColor = '#fffbeb'; badgeBorderColor = '#fde68a'; badgeTextColor = '#b45309';
        }

        document.getElementById('text_skor_final').innerText = skor;
        const badgeEl = document.getElementById('badge_risk_final');
        badgeEl.innerText = riskCategory;
        badgeEl.style.backgroundColor = badgeBgColor;
        badgeEl.style.color = badgeTextColor;
        badgeEl.style.border = `1px solid ${badgeBorderColor}`;
    }

    function submitRaForm() {
        const id = document.getElementById('ra_kka_id').value;
        
        let formData = new FormData();
        formData.append('bukti_referensi', document.getElementById('bukti_referensi').value);
        formData.append('jenis_exception_ra', document.getElementById('jenis_exception_ra').value);
        formData.append('hasil_uji', document.getElementById('hasil_uji').value);
        formData.append('klarifikasi_unit', document.getElementById('klarifikasi_unit').value);
        formData.append('skor_dampak', document.getElementById('skor_dampak').value);
        formData.append('skor_kemungkinan', document.getElementById('skor_kemungkinan').value);
        formData.append('critical_trigger', document.getElementById('critical_trigger').value);
        formData.append('perlu_onsite', document.getElementById('perlu_onsite').value);
        formData.append('tanggal_ditemukan', document.getElementById('tanggal_ditemukan').value);
        formData.append('simpulan_ra', document.getElementById('simpulan_ra').value);

        const fileInput = document.getElementById('file_bukti');
        if (fileInput.files.length > 0) {
            formData.append('file_bukti', fileInput.files[0]);
        }

        axios.post(`${baseUrl}/${id}/ra`, formData, { 
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'multipart/form-data' } 
        }).then(res => {
            alert(res.data.message || 'Review RA berhasil disubmit!');
            closeDetailView();
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

    function submitAdminForm() {
        const id = document.getElementById('admin_kka_id').value;
        const payload = {
            status_klarifikasi: document.getElementById('status_klarifikasi').value,
            perluasan_sampel: document.getElementById('perluasan_sampel').value,
            keputusan_onsite: document.getElementById('keputusan_onsite').value,
            status_review: document.getElementById('status_review').value,
            keputusan_eskalasi: document.getElementById('keputusan_eskalasi').value,
            catatan_reviewer: document.getElementById('catatan_reviewer').value,
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
@endsection