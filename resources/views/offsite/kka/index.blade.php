@extends('layouts.app')

@section('content')

<style>
    /* Styling Dashboard & Cards */
    :root {
        --bg-body: #f8fafc; --text-dark: #1e293b; --text-muted: #64748b;
        --border-color: #e2e8f0; --primary-blue: #1e3a8a;
    }
    
    .dashboard-header h4 { font-weight: 700; color: var(--text-dark); margin-bottom: 0.3rem; }
    .dashboard-header p { color: var(--text-muted); font-size: 0.9rem; }

    .summary-cards-wrapper { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .stat-card {
        background: #fff; border: 1px solid var(--border-color); border-radius: 12px;
        padding: 1.25rem; display: flex; align-items: flex-start; gap: 1rem; position: relative;
    }
    .stat-icon {
        width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;
    }
    .stat-icon.bg-light-gray { background: #f1f5f9; color: #475569; }
    .stat-icon.bg-light-blue { background: #eff6ff; color: #3b82f6; }
    .stat-icon.bg-light-red { background: #fef2f2; color: #ef4444; }
    .stat-icon.bg-light-green { background: #f0fdf4; color: #16a34a; }
    
    .stat-details h6 { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin: 0 0 0.2rem 0; text-transform: uppercase; }
    .stat-details h3 { font-size: 1.3rem; font-weight: 700; color: var(--text-dark); margin: 0 0 0.2rem 0; }
    .stat-details p { font-size: 0.75rem; color: var(--text-muted); margin: 0; }

    /* Styling Tabs */
    .kka-tabs { 
        display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 0.5rem; 
        margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); 
        flex-wrap: nowrap; 
    }
    .kka-tab-item {
        background: transparent; padding: 0.6rem 1rem; font-size: 0.85rem; font-weight: 600; 
        color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; 
        cursor: pointer; transition: all 0.2s; border-bottom: 3px solid transparent;
        white-space: nowrap; flex-shrink: 0; 
    }
    .kka-tab-item.active { color: var(--primary-blue); border-bottom: 3px solid var(--primary-blue); }
    .kka-tab-item .badge-count { background: #f1f5f9; color: #475569; padding: 0.1rem 0.5rem; border-radius: 10px; font-size: 0.7rem; }
    .kka-tab-item.active .badge-count { background: #dbeafe; color: var(--primary-blue); }

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

    /* Fix Tabel */
    .table-wrapper { overflow-x: auto; width: 100%; }
    .data-table { width: 100%; min-width: 1000px; border-collapse: collapse; }
    .data-table th { padding: 1rem; background: #f8fafc; font-size: 0.75rem; color: var(--text-muted); border-bottom: 1px solid var(--border-color); text-transform: uppercase; }
    .data-table td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid var(--border-color); font-size: 0.85rem; }

    .btn-review {
        background-color: var(--primary-blue); color: #fff; font-size: 0.8rem; font-weight: 600; padding: 0.4rem 1rem; border-radius: 6px; border: none; display: inline-flex; align-items: center; gap: 0.3rem; cursor: pointer;
    }
    .btn-review:hover { background-color: #152c6b; color: #fff; }

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
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div class="dashboard-header">
            <h4>Kertas Kerja Audit (KKA) Offsite</h4>
            <p>Review, verifikasi, dan tindak lanjuti indikasi temuan exception transaksi.</p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.5rem; background: #fff; padding: 0.35rem 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <label for="filterPeriodeGlobal" style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); margin: 0; white-space: nowrap;">
                    <i class="bi bi-calendar3 me-1 text-primary"></i> Periode:
                </label>
                <input type="month" id="filterPeriodeGlobal" class="form-control form-control-sm" style="font-size: 0.82rem; border-color: var(--border-color); width: 140px; height: 32px;" onchange="onPeriodeChange()" value="{{ request('periode') }}">
                <button type="button" class="btn btn-sm btn-light text-muted p-0 px-1" style="height: 28px; line-height: 26px;" onclick="resetPeriodeFilter()" title="Tampilkan Semua Periode"><i class="bi bi-x-circle"></i></button>
            </div>
            <button type="button" id="btnToggleRiwayat" onclick="toggleRiwayatMode()" class="btn-filter" style="background: #f1f5f9; color: #475569; border: 1px solid var(--border-color);">
                <i class="bi bi-clock-history"></i> <span id="btnRiwayatText">Lihat Arsip Riwayat Selesai</span>
            </button>
        </div>
    </div>

    <!-- 4 SUMMARY CARDS -->
    <div class="summary-cards-wrapper">
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

    <!-- FOLDER TABS -->
    <div class="kka-tabs" id="kkaTabContainer">
        <div class="kka-tab-item active" onclick="switchTab(this, 'KKA_Teller_Kas', 'KKA Teller & Kas')">
            <i class="bi bi-folder2-open"></i> KKA Teller & Kas <span class="badge-count tab-teller">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA_Kredit', 'KKA Kredit')">
            <i class="bi bi-folder2"></i> KKA Kredit <span class="badge-count tab-kredit">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA_Biaya_Beban', 'KKA Biaya & Beban')">
            <i class="bi bi-folder2"></i> KKA Biaya & Beban <span class="badge-count tab-biaya">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA_Biaya_Internal', 'KKA Biaya Internal')">
            <i class="bi bi-folder2"></i> KKA Biaya Internal <span class="badge-count tab-internal">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA_Pengaduan', 'KKA Pengaduan')">
            <i class="bi bi-folder2"></i> KKA Pengaduan <span class="badge-count tab-pengaduan">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA_Transaksi_Umum', 'KKA Transaksi Umum')">
            <i class="bi bi-folder2"></i> KKA Transaksi Umum <span class="badge-count tab-umum">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA_Transfer_KU', 'KKA Transfer & Pasiva')">
            <i class="bi bi-arrow-left-right"></i> KKA Transfer / KU <span class="badge-count tab-transfer">0</span>
        </div>
    </div>

    <!-- TABEL UTAMA -->
    <div class="card" style="border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; background:#fff;">
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
            <div style="font-weight: 700; color: var(--primary-blue);" id="tableTitle">KKA Teller & Kas</div>
            <div class="badge" style="background:#e0e7ff; color:var(--primary-blue); font-size:0.75rem; padding: 0.4rem 0.8rem; border-radius: 12px;">
                <span id="totalDataCount">0</span> Data
            </div>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 5%;">NO</th>
                        <th style="text-align: left;">TANGGAL DATA</th>
                        <th style="text-align: left;">KODE UNIT</th>
                        <th style="text-align: left;">NO REFERENSI</th>
                        <th style="text-align: left;">USER / MAKER</th>
                        <th style="text-align: left;">KODE TRX</th>
                        <th style="text-align: right;">NOMINAL</th>
                        <th style="text-align: left;">DESKRIPSI / NARASI</th>
                        <th style="text-align: center;">RISK LEVEL</th>
                        <th style="text-align: right;">AKSI</th>
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
                        <label class="custom-form-label" id="label_file_bukti">Upload Bukti Transaksi (PDF)</label>
                        
                        {{-- Tampilan untuk Admin / Reviewer: Melihat & memeriksa PDF yang diunggah RA --}}
                        <div id="view_bukti_admin" style="display:none;"></div>

                        {{-- Tampilan untuk RA: Form Upload File --}}
                        <div id="upload_bukti_ra">
                            <input type="file" class="custom-form-control field-ra" id="file_bukti" accept=".pdf">
                            <div id="file_bukti_info" style="font-size:0.72rem; color:var(--text-muted); margin-top:0.2rem;"></div>
                        </div>
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

                <!-- FLAGGING INDIKASI FRAUD (Poin 8 SOP 09) -->
                <div id="fraud_flagging_banner" style="display:none; background:#fef2f2; border:1px solid #f87171; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1rem; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                    <div style="display:flex; align-items:center; gap:0.5rem; color:#991b1b; font-size:0.85rem; font-weight:600;">
                        <i class="bi bi-flag-fill text-danger fs-5"></i>
                        <div>
                            <div>FLAGGING INDIKASI FRAUD TERDETEKSI (STATUS CRITICAL)</div>
                            <div style="font-weight:400; font-size:0.75rem; color:#b91c1c;">Temuan KKA Offsite ini berisiko Critical dan terindikasi fraud. Admin/Auditor dapat meneruskannya ke Modul Indikasi Fraud (SOP 09).</div>
                        </div>
                    </div>
                    <a href="#" id="btn_eskalasi_fraud_offsite" target="_blank" class="btn btn-sm btn-danger fw-bold shadow-sm" style="white-space:nowrap;">
                        <i class="bi bi-shield-exclamation me-1"></i> Teruskan ke Fraud SOP 09 &rarr;
                    </a>
                </div>

                @if(in_array(strtolower(auth()->user()->role), ['ra', 'admin']))
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
                        <label class="custom-form-label">Keputusan Eskalasi / Fraud Flagging</label>
                        <select class="custom-form-control field-admin" id="keputusan_eskalasi" onchange="onOffsiteAdminEskalasiChange()" @if(strtolower(auth()->user()->role) === 'ra') disabled @endif>
                            <option value="Tidak">Tidak Dieskalasi</option>
                            <option value="Eskalasi Kadiv">Eskalasi ke Kadiv SKAI</option>
                            <option value="Eskalasi Direksi">Eskalasi ke Direksi</option>
                            <option value="Terindikasi Fraud (SOP 09)">Terindikasi Fraud (Teruskan ke SOP 09)</option>
                        </select>
                    </div>
                </div>

                <!-- Box Disposisi Fraud Khusus Admin saat Approved/Terindikasi Fraud (Poin 8) -->
                <div id="adm_offsite_fraud_action_box" style="display:none; margin-bottom:1.5rem; background:#fff1f2; border:1px solid #fecdd3; padding:0.75rem 1rem; border-radius:8px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
                        <div>
                            <div style="font-weight:700; color:#dc2626; font-size:0.85rem;"><i class="bi bi-shield-exclamation me-1"></i> Disposisi Eskalasi Fraud:</div>
                            <div style="font-size:0.75rem; color:#4b5563;">Admin mengonfirmasi temuan Offsite ini sebagai indikasi fraud untuk ditindaklanjuti pada SOP 09.</div>
                        </div>
                        <a href="#" id="adm_btn_direct_fraud_offsite" target="_blank" class="btn btn-sm btn-danger fw-bold shadow-sm" style="font-size:0.78rem;">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Form SOP 09 &rarr;
                        </a>
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
    const baseUrl = '{{ url("/offsite/kka") }}'; 
    const userRole = '{{ strtolower(trim(auth()->user()->role)) }}'; 
    const csrfToken = '{{ csrf_token() }}';
    
    let globalFindings = [];
    let currentActiveSheetCode = 'KKA_Teller_Kas';
    let currentActiveSheet = 'KKA Teller & Kas';
    let isShowingRiwayat = false;

    document.addEventListener("DOMContentLoaded", function() {
        loadData();
    });

    // Pindah Tab Data
    function switchTab(clickedElement, sheetCode, tabName) {
        const allTabs = document.querySelectorAll('.kka-tab-item');
        allTabs.forEach(tab => tab.classList.remove('active'));

        clickedElement.classList.add('active');
        currentActiveSheetCode = sheetCode;
        currentActiveSheet = tabName;
        
        if (isShowingRiwayat) {
            document.getElementById('tableTitle').innerText = `${tabName} (Arsip Riwayat Selesai)`;
        } else {
            document.getElementById('tableTitle').innerText = tabName;
        }
        
        loadData();
    }

    // Filter Periode
    function onPeriodeChange() {
        loadData(1);
    }

    function resetPeriodeFilter() {
        const input = document.getElementById('filterPeriodeGlobal');
        if (input) {
            input.value = '';
        }
        loadData(1);
    }

    // Ambil Data via API
    function loadData(page = 1) {
        const tbody = document.getElementById('kkaTableBody');
        tbody.innerHTML = `<tr><td colspan="10"><div class="empty-state" style="text-align: center; padding: 3rem 0; color: var(--text-muted);"><i class="bi bi-hourglass-split" style="font-size: 2rem; display: block; margin-bottom: 1rem;"></i><p>Memuat data...</p></div></td></tr>`;

        let url = `${baseUrl}/data?page=${page}&source_sheet=${currentActiveSheetCode}`;
        @if(request()->has('kode_unit'))
        url += `&kode_unit={{ request('kode_unit') }}`;
        @endif
        @if(request()->has('cabang'))
        url += `&cabang_id={{ request('cabang') }}`;
        @endif

        const periodeVal = document.getElementById('filterPeriodeGlobal')?.value;
        if (periodeVal) {
            url += `&periode=${encodeURIComponent(periodeVal)}`;
        }

        axios.get(url)
            .then(response => {
                globalFindings = response.data.data.data;
                const summary = response.data.summary || {};
                const totalCount = response.data.data.total !== undefined ? response.data.data.total : globalFindings.length;
                
                document.getElementById('totalDataCount').innerText = totalCount;
                document.getElementById('statTotalException').innerText = summary.total_exception !== undefined ? summary.total_exception : totalCount;
                
                let totalNominal = summary.total_nominal !== undefined ? summary.total_nominal : globalFindings.reduce((acc, curr) => acc + (parseFloat(curr.nominal_terkait) || 0), 0);
                document.getElementById('statTotalNominal').innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(totalNominal);
                
                let highRiskCount = summary.high_risk_count !== undefined ? summary.high_risk_count : globalFindings.filter(x => (x.risk_awal || '').toLowerCase() === 'high').length;
                document.getElementById('statHighRisk').innerText = highRiskCount;
                
                let selesaiCount = summary.selesai_review_count !== undefined ? summary.selesai_review_count : globalFindings.filter(x => x.status_review === 'Approved').length;
                document.getElementById('statSelesaiReview').innerText = selesaiCount;
                const tabCounts = summary.tab_counts || {};
                
                // Update Badge di masing-masing Tab
                const tabMapping = {
                    'KKA_Teller_Kas': '.tab-teller',
                    'KKA_Kredit': '.tab-kredit',
                    'KKA_Biaya_Beban': '.tab-biaya',
                    'KKA_Biaya_Internal': '.tab-internal',
                    'KKA_Pengaduan': '.tab-pengaduan',
                    'KKA_Transaksi_Umum': '.tab-transaksi'
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
            let riskBadge = `<span class="badge ${riskClass}" style="border-radius:12px; padding:0.4rem 0.8rem;">${item.risk_awal || 'Moderate'}</span>`;
            let nominalStr = item.nominal_terkait > 0 ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(item.nominal_terkait) : '-';
            
            // Pengecekan agar user_maker yang kosong atau strip (-) otomatis tertulis "Sistem / Tidak Tercatat"
            let userMakerDisplay = (!item.user_maker || item.user_maker === '-') ? 'Sistem / Tidak Tercatat' : item.user_maker;

            const tr = `<tr>
                <td style="text-align:center; color:var(--text-muted);">${index + 1}</td>
                <td style="white-space:nowrap;">${item.tanggal_data || '-'}</td>
                <td><span class="badge bg-light text-dark border" style="border-radius:20px; padding: 0.3rem 0.6rem;">${item.kode_unit || '-'}</span></td>
                <td>${item.no_referensi || item.offsite_id || '-'}</td>
                <td>${userMakerDisplay}</td>
                <td>${item.kode_trx || '-'}</td>
                <td style="font-weight:700; text-align:right;">${nominalStr}</td>
                <td style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${item.deskripsi || item.catatan_rule || item.rincian || item.jenis_exception_awal || ''}">
                    ${item.deskripsi || item.catatan_rule || item.rincian || item.jenis_exception_awal || '-'}
                </td>
                <td style="text-align:center;">${riskBadge}</td>
                <td style="text-align:right; white-space:nowrap;">
                    ${item.file_bukti ? `<a href="${baseUrl}/${item.id}/view-bukti" target="_blank" class="btn btn-sm btn-outline-danger me-1" style="font-size:0.75rem; padding:0.3rem 0.6rem; border-radius:6px; text-decoration:none;" title="Lihat Dokumen Bukti PDF"><i class="bi bi-file-earmark-pdf-fill"></i> Bukti</a>` : ''}
                    <button class="btn-review" onclick="openDetailView(${item.id})">
                        <i class="bi bi-arrow-right"></i> Review
                    </button>
                </td>
            </tr>`;
            tbody.insertAdjacentHTML('beforeend', tr);
        });
    }

    let currentItem = null;
    let currentChecklists = [];
    let currentRecurringCount = 1;

    // FUNGSI TOGGLE KE FULL PAGE DETAIL VIEW (MENGAMBIL MASTER CHECKLIST & RECURRING COUNT DARI SERVER)
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
                    currentChecklists = [];
                    currentRecurringCount = 1;
                    populateDetailForm(item);
                }
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
        const displayDeskripsi = item.deskripsi || item.rincian || item.catatan_rule || item.jenis_exception_awal || '-';
        document.getElementById('sum_deskripsi').innerText = displayDeskripsi;

        // Populate Section 1 (System)
        document.getElementById('sys_offsite_id').innerText = item.offsite_id || item.staging_id || `KKA-${item.id}`;
        document.getElementById('sys_tanggal_data').innerText = item.tanggal_data || '-';
        document.getElementById('sys_unit_kerja').innerText = item.kode_unit || '-';
        document.getElementById('sys_nominal_terkait').innerText = document.getElementById('sum_nominal').innerText;
        document.getElementById('sys_source_sheet').innerText = item.source_sheet || currentActiveSheet;
        document.getElementById('sys_risk_awal').innerHTML = document.getElementById('sum_risk_badge').innerHTML;
        document.getElementById('sys_exception_awal').innerText = item.jenis_exception_awal || item.deskripsi || '-';
        
        document.getElementById('sys_catatan_rule').innerText = displayDeskripsi;
        document.getElementById('sys_prosedur_uji').innerText = item.prosedur_uji || 'Uji Ketaatan & Keabsahan Dokumen Transaksi Offsite';

        // Populate Section 2 (RA)
        document.getElementById('ra_kka_id').value = item.id;
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

        // Reset File Input
        const fileInputEl = document.getElementById('file_bukti');
        if (fileInputEl) fileInputEl.value = '';

        // Tampilan Dokumen Bukti berdasarkan Role (Admin vs RA)
        const isAdminRole = (userRole !== 'ra');
        const viewAdminEl = document.getElementById('view_bukti_admin');
        const uploadRaEl = document.getElementById('upload_bukti_ra');
        const labelBuktiEl = document.getElementById('label_file_bukti');

        if (isAdminRole) {
            if (labelBuktiEl) labelBuktiEl.innerText = 'Dokumen Bukti Transaksi (PDF)';
            if (uploadRaEl) uploadRaEl.style.display = 'none';
            if (viewAdminEl) {
                viewAdminEl.style.display = 'block';
                if (item.file_bukti) {
                    viewAdminEl.innerHTML = `
                        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:0.6rem 0.85rem; display:flex; align-items:center; justify-content:space-between; gap:0.75rem;">
                            <div style="display:flex; align-items:center; gap:0.6rem; overflow:hidden;">
                                <i class="bi bi-file-earmark-pdf-fill" style="color:#ef4444; font-size:1.6rem; flex-shrink:0;"></i>
                                <div style="line-height:1.2; overflow:hidden;">
                                    <span style="font-size:0.82rem; font-weight:700; color:#1e293b; display:block;">Dokumen Bukti RA Tersedia</span>
                                    <span style="font-size:0.72rem; color:#64748b;">Diunggah oleh Resident Auditor</span>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:0.4rem;">
                                <a href="${baseUrl}/${item.id}/view-bukti" target="_blank" class="btn btn-sm" style="background:#2563eb; color:#fff; font-size:0.78rem; font-weight:600; padding:0.4rem 0.85rem; border-radius:6px; text-decoration:none; display:inline-flex; align-items:center; gap:0.35rem; box-shadow:0 1px 2px rgba(0,0,0,0.08);">
                                    <i class="bi bi-eye-fill"></i> Buka / Lihat PDF
                                </a>
                                <button type="button" onclick="toggleUploadRaForAdmin()" class="btn btn-sm btn-light border text-muted" title="Ganti / Upload Ulang Bukti" style="font-size:0.75rem; padding:0.4rem 0.55rem; border-radius:6px;">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </div>
                    `;
                } else {
                    viewAdminEl.innerHTML = `
                        <div style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px; padding:0.65rem 0.85rem; font-size:0.8rem; color:#64748b; display:flex; align-items:center; justify-content:space-between; gap:0.5rem;">
                            <div style="display:flex; align-items:center; gap:0.45rem;">
                                <i class="bi bi-exclamation-circle text-secondary" style="font-size:1.1rem;"></i>
                                <span>Belum ada file bukti PDF diunggah oleh RA.</span>
                            </div>
                            <button type="button" onclick="toggleUploadRaForAdmin()" class="btn btn-sm btn-outline-secondary" style="font-size:0.72rem; padding:0.25rem 0.55rem;">
                                + Upload Manual
                            </button>
                        </div>
                    `;
                }
            }
        } else {
            // Mode RA
            if (labelBuktiEl) labelBuktiEl.innerText = 'Upload Bukti Transaksi (PDF)';
            if (viewAdminEl) viewAdminEl.style.display = 'none';
            if (uploadRaEl) {
                uploadRaEl.style.display = 'block';
                const fileInfoEl = document.getElementById('file_bukti_info');
                if (fileInfoEl) {
                    if (item.file_bukti) {
                        fileInfoEl.innerHTML = `
                            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:0.4rem 0.65rem; display:flex; align-items:center; justify-content:space-between; margin-top:0.35rem;">
                                <span style="font-size:0.75rem; color:#166534;"><i class="bi bi-check-circle-fill text-success me-1"></i> File tersimpan di sistem</span>
                                <a href="${baseUrl}/${item.id}/view-bukti" target="_blank" style="font-size:0.75rem; color:#2563eb; font-weight:600; text-decoration:none;">
                                    <i class="bi bi-file-earmark-pdf"></i> Lihat File
                                </a>
                            </div>
                        `;
                    } else {
                        fileInfoEl.innerHTML = `<span style="color:#64748b; font-size:0.72rem;">Belum ada file bukti PDF diunggah. Format .pdf (Maks 5MB)</span>`;
                    }
                }
            }
        }

        // Render Accordion Checklist KKA
        renderChecklistAccordion(currentChecklists, item.checklist_answers || []);

        // Populate Section 3 (Admin)
        document.getElementById('admin_kka_id').value = item.id;
        document.getElementById('status_klarifikasi').value = item.status_klarifikasi || '';
        document.getElementById('perluasan_sampel').value = item.perluasan_sampel ? 1 : 0;
        document.getElementById('keputusan_onsite').value = item.keputusan_onsite || '';
        document.getElementById('status_review').value = item.status_review || '';
        document.getElementById('keputusan_eskalasi').value = item.keputusan_eskalasi || 'Tidak';
        document.getElementById('catatan_reviewer').value = item.catatan_reviewer || '';

        // Hitung otomasi skor risiko dan kendalikan visibilitas field exception
        onChecklistChange();

        // Lock Logic based on Role
        const isRa = (userRole === 'ra' || userRole === 'admin');
        document.querySelectorAll('.field-ra').forEach(el => el.disabled = !isRa);
        document.querySelectorAll('.field-admin').forEach(el => el.disabled = (userRole === 'ra'));
        
        document.getElementById('badge_ra_lock').innerText = isRa ? "Mode Edit" : "Read Only";
        document.getElementById('badge_admin_lock').innerText = (userRole === 'ra') ? "Read Only" : "Mode Edit";

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

    // Toggle Form Upload Manual Bukti untuk Admin jika diperlukan
    function toggleUploadRaForAdmin() {
        const uploadRaEl = document.getElementById('upload_bukti_ra');
        if (!uploadRaEl) return;
        uploadRaEl.style.display = (uploadRaEl.style.display === 'none') ? 'block' : 'none';
    }

    // HELPER STYLING SELECT CEK KKA KREDIT (✓, ✗, N/A)
    function getKreditSelectStyle(val) {
        if (val === '✓' || val === 'Ya') {
            return 'background-color:#dcfce7; color:#15803d; border-color:#86efac;';
        } else if (val === '✗' || val === 'Tidak') {
            return 'background-color:#fee2e2; color:#b91c1c; border-color:#fca5a5;';
        } else if (val === 'N/A') {
            return 'background-color:#f1f5f9; color:#475569; border-color:#cbd5e1;';
        }
        return 'background-color:#fef9c3; color:#854d0e; border-color:#fde047;';
    }

    function setKreditAllSesuai() {
        document.querySelectorAll('.chk-kredit-select').forEach(sel => {
            sel.value = '✓';
        });
        onChecklistChange();
    }

    function setKreditAllNA() {
        document.querySelectorAll('.chk-kredit-select').forEach(sel => {
            sel.value = 'N/A';
        });
        onChecklistChange();
    }

    function setSubAreaKreditVal(code, val, event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        document.querySelectorAll(`.chk-kredit-select[data-subarea="${code}"]`).forEach(sel => {
            sel.value = val;
        });
        onChecklistChange();
    }

    function toggleAllSubAreas(expand) {
        document.querySelectorAll('#accordionChecklistKka .accordion-collapse').forEach(el => {
            el.style.display = expand ? 'block' : 'none';
            const collapseId = el.id;
            const icon = document.querySelector(`.toggle-icon-${collapseId}`);
            if (icon) {
                icon.style.transform = expand ? 'rotate(0deg)' : 'rotate(-90deg)';
            }
        });
    }

    // RENDER ACCORDION CHECKLIST KKA DENGAN KELOMPOK PER SUB-AREA
    function renderChecklistAccordion(checklists, existingAnswers) {
        const container = document.getElementById('checklistAccordionContainer');
        if (!container) return;

        if (!checklists || checklists.length === 0) {
            container.innerHTML = `<div class="p-3 text-muted text-center" style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px;">Tidak ada master checklist untuk tab KKA ini.</div>`;
            return;
        }

        const isKredit = (currentActiveSheetCode === 'KKA_Kredit');

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

        const isRa = (userRole === 'ra' || userRole === 'admin');

        let html = '';

        // TAMPILKAN HEADER & PETUNJUK PENGISIAN & REKAP KHUSUS KKA KREDIT (134 ITEM)
        if (isKredit) {
            html += `
                <div class="card mb-3 border-0 shadow-sm" style="border-radius:10px; border:1px solid #cbd5e1; overflow:hidden;">
                    <div style="background:#1e3a8a; color:#fff; padding:10px 16px; font-weight:700; font-size:0.88rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
                        <div>
                            <i class="bi bi-journal-bookmark-fill me-1.5"></i> KKA KREDIT — PETUNJUK PENGISIAN &amp; REKAP
                        </div>
                        <div class="small opacity-75">BPP Resident Auditor Bank Sulteng — SOP-RA-03, 04, 05, 10</div>
                    </div>
                    <div class="card-body p-3 bg-white">
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered align-middle mb-0" style="font-size:0.79rem;">
                                <tbody>
                                    <tr>
                                        <th style="width:160px; background:#f8fafc; color:#334155; font-weight:600;">Dasar</th>
                                        <td style="color:#1e293b; font-weight:500;">BPP RA Bank Sulteng, SK Direksi No. 78/SK-DIR/BPD-ST/2026 (SOP-RA-03, 04, 05, 10)</td>
                                    </tr>
                                    <tr>
                                        <th style="background:#f8fafc; color:#334155; font-weight:600;">Kolom Cek</th>
                                        <td>Pilih dari dropdown: <strong>✓ (sesuai)</strong>, <strong>✗ (tidak sesuai)</strong>, atau <strong>N/A</strong></td>
                                    </tr>
                                    <tr>
                                        <th style="background:#f8fafc; color:#334155; font-weight:600;">Kolom Sumber</th>
                                        <td><span class="badge bg-primary-subtle text-primary border me-1">BPP</span> = bersumber dari dokumen BPP; <span class="badge bg-warning-subtle text-warning-emphasis border me-1">Saran RA</span> = usulan, perlu dikonfirmasi dengan Bank</td>
                                    </tr>
                                    <tr>
                                        <th style="background:#f8fafc; color:#334155; font-weight:600;">Sel kuning</th>
                                        <td><span class="px-2 py-0.5 rounded border" style="background:#fef9c3; border-color:#fde047; font-weight:600; color:#854d0e;">Sel yang harus diisi oleh auditor</span> (Kolom Cek &amp; Keterangan/Catatan)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px;">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <div class="fw-bold text-primary" style="font-size:0.83rem;">
                                    <i class="bi bi-bar-chart-fill me-1"></i> REKAP (otomatis dari sheet KKA Kredit)
                                </div>
                                ${isRa ? `
                                <div class="d-flex gap-1.5 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-success fw-bold py-1 px-2.5" style="font-size:0.75rem;" onclick="setKreditAllSesuai()">
                                        <i class="bi bi-check2-all me-1"></i> Set Semua [✓] Sesuai
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size:0.75rem;" onclick="setKreditAllNA()">
                                        Set Semua N/A
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size:0.75rem;" onclick="toggleAllSubAreas(true)">
                                        <i class="bi bi-arrows-expand me-1"></i> Buka Semua
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size:0.75rem;" onclick="toggleAllSubAreas(false)">
                                        <i class="bi bi-arrows-collapse me-1"></i> Tutup Semua
                                    </button>
                                </div>
                                ` : ''}
                            </div>
                            <div class="d-flex flex-wrap gap-2.5 align-items-center" style="font-size:0.8rem;">
                                <div class="d-flex align-items-center gap-1.5 bg-white px-2.5 py-1 rounded border">
                                    <span class="text-muted">Total item checklist:</span>
                                    <span class="badge bg-secondary fw-bold" id="kredit_rekap_total" style="font-size:0.82rem;">${checklists.length}</span>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 bg-white px-2.5 py-1 rounded border">
                                    <span class="text-muted">Item sudah dicek (Cek terisi):</span>
                                    <span class="badge bg-primary fw-bold" id="kredit_rekap_dicek" style="font-size:0.82rem;">0</span>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 bg-white px-2.5 py-1 rounded border">
                                    <span class="text-muted">Item sesuai (✓):</span>
                                    <span class="badge bg-success fw-bold" id="kredit_rekap_sesuai" style="font-size:0.82rem;">0</span>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 bg-white px-2.5 py-1 rounded border">
                                    <span class="text-muted">Item tidak sesuai (✗):</span>
                                    <span class="badge bg-danger fw-bold" id="kredit_rekap_tidak_sesuai" style="font-size:0.82rem;">0</span>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 bg-white px-2.5 py-1 rounded border">
                                    <span class="text-muted">Item N/A:</span>
                                    <span class="badge bg-warning text-dark fw-bold" id="kredit_rekap_na" style="font-size:0.82rem;">0</span>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 bg-white px-2.5 py-1 rounded border border-primary">
                                    <span class="text-muted fw-bold">Persentase item sudah dicek:</span>
                                    <span class="badge bg-info text-dark fw-bold" id="kredit_rekap_persen" style="font-size:0.85rem;">0.0%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        html += `
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded" style="background:#f1f5f9; border:1px solid var(--border-color); flex-wrap:wrap; gap:0.5rem;">
                <div style="font-size:0.83rem; font-weight:700; color:var(--primary-blue, #1e3a8a); display:flex; align-items:center; gap:0.5rem;">
                    <i class="bi bi-ui-checks me-1"></i> ${isKredit ? 'KKA KREDIT (termasuk Flagging Kredit)' : 'CHECKLIST PENGUJIAN AUDIT'} (${checklists.length} ITEM)
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <span class="badge bg-success" id="badgeTotalYa" style="font-size:0.75rem;">0 ${isKredit ? 'Sesuai (✓)' : 'Ya'}</span>
                    <span class="badge bg-danger" id="badgeTotalTidak" style="font-size:0.75rem;">0 ${isKredit ? 'Tidak Sesuai (✗)' : 'Tidak'}</span>
                    ${!isKredit && isRa ? `
                    <button type="button" class="btn btn-sm btn-success fw-bold px-3 py-1" style="font-size:0.75rem; border-radius:6px; box-shadow:0 1px 2px rgba(0,0,0,0.08); display:inline-flex; align-items:center; gap:0.35rem;" onclick="setGlobalAllYa(event)">
                        <i class="bi bi-check2-all"></i> Set Semua Ya (Seluruh Item)
                    </button>
                    ` : ''}
                </div>
            </div>
            <div class="accordion" id="accordionChecklistKka">
        `;

        for (const code in groups) {
            const grp = groups[code];
            const collapseId = `collapseSubArea_${code}`;

            html += `
            <div class="accordion-item" style="border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 0.65rem; overflow:hidden;">
                <h2 class="accordion-header" id="heading_${code}" style="background:#f8fafc;">
                    <div class="d-flex justify-content-between align-items-center w-100 p-2 px-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap" style="cursor:pointer; flex:1;" onclick="toggleAccordionSubArea('${collapseId}')">
                            <i class="bi bi-chevron-down toggle-icon-${collapseId}" style="font-size:0.8rem; transition: transform 0.2s;"></i>
                            <span class="fw-bold" style="font-size:0.82rem; color:var(--text-dark);">${grp.name}</span>
                            <span class="badge bg-light text-secondary border" style="font-size:0.7rem;">${grp.items.length} Item</span>
                            <span class="badge bg-danger text-white subarea-tidak-badge" id="subTidakBadge_${code}" style="display:none; font-size:0.7rem;">0 Temuan Tidak</span>
                        </div>
                        ${isRa ? `
                        ${isKredit ? `
                        <div class="d-flex gap-1" onclick="event.stopPropagation();">
                            <button type="button" class="btn btn-sm btn-outline-success py-0 px-1.5" style="font-size:0.68rem;" onclick="setSubAreaKreditVal('${code}', '✓', event)" title="Set sub-area ini ke [✓] Sesuai">[✓] Sesuai</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1.5" style="font-size:0.68rem;" onclick="setSubAreaKreditVal('${code}', 'N/A', event)" title="Set sub-area ini ke N/A">N/A</button>
                        </div>
                        ` : `
                        <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 fw-semibold" style="font-size:0.72rem; border-radius:4px; z-index:5;" onclick="setSubAreaAllYa('${code}', event)">
                            <i class="bi bi-check-all"></i> Set Semua Ya
                        </button>
                        `}
                        ` : ''}
                    </div>
                </h2>
                <div id="${collapseId}" class="accordion-collapse" style="display: block;">
                    <div class="accordion-body p-0" id="subAreaBody_${code}">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size:0.78rem;">
                                <thead class="table-light" style="font-size:0.72rem; text-transform:uppercase;">
                                    <tr>
                                        <th style="width: 5%; text-align:center;">No</th>
                                        <th style="width: ${isKredit ? '20%' : '25%'};">Sub-Area / Objek</th>
                                        <th style="width: ${isKredit ? '34%' : '40%'};">Item Checklist</th>
                                        ${isKredit ? '<th style="width: 13%; text-align:center;">Sumber</th>' : ''}
                                        <th style="width: 14%; text-align:center; ${isKredit ? 'background:#fef9c3; color:#854d0e; border-bottom:2px solid #fde047;' : ''}">${isKredit ? 'Cek *' : 'Jawaban'}</th>
                                        <th style="width: ${isKredit ? '14%' : '16%'}; ${isKredit ? 'background:#fef9c3; color:#854d0e; border-bottom:2px solid #fde047;' : ''}">Keterangan / Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;

            grp.items.forEach(chk => {
                const saved = answerMap[chk.id];
                const rawJawaban = saved ? saved.jawaban : (isKredit ? '' : 'Ya');
                const ket = saved ? saved.keterangan : '';

                let jawaban = rawJawaban;
                if (isKredit) {
                    if (rawJawaban === 'Ya') jawaban = '✓';
                    if (rawJawaban === 'Tidak') jawaban = '✗';
                }
                const isTidak = (jawaban === 'Tidak' || jawaban === '✗');
                const isYa = (jawaban === 'Ya' || jawaban === '✓');

                html += `
                    <tr id="row_chk_${chk.id}" class="${isTidak ? 'table-danger' : ''}">
                        <td style="text-align:center; color:var(--text-muted); font-weight:600;">${chk.item_no}</td>
                        <td style="color:var(--text-dark); font-weight:500;">${chk.sub_area_name}</td>
                        <td style="color:var(--text-dark);">${chk.item_checklist}</td>
                        ${isKredit ? `
                        <td style="text-align:center;">
                            <span class="badge ${chk.sumber && chk.sumber.includes('Saran') ? 'bg-warning-subtle text-warning-emphasis border' : 'bg-primary-subtle text-primary border'}" style="font-size:0.71rem;">${chk.sumber || 'BPP'}</span>
                        </td>
                        ` : ''}
                        <td style="text-align:center; ${isKredit ? 'background:#fffbeb;' : ''}">
                            ${isKredit ? `
                            <select class="form-select form-select-sm chk-answer chk-kredit-select" 
                                    name="chk_answer_${chk.id}" 
                                    id="chk_answer_${chk.id}" 
                                    data-subarea="${code}" 
                                    data-chk-id="${chk.id}"
                                    onchange="onChecklistChange()" 
                                    ${!isRa ? 'disabled' : ''} 
                                    style="font-size:0.75rem; font-weight:600; min-width:115px; ${getKreditSelectStyle(jawaban)}">
                                <option value="" ${jawaban === '' ? 'selected' : ''}>-- Pilih Cek --</option>
                                <option value="✓" ${jawaban === '✓' ? 'selected' : ''} style="color:#15803d; font-weight:600;">✓ (Sesuai)</option>
                                <option value="✗" ${jawaban === '✗' ? 'selected' : ''} style="color:#b91c1c; font-weight:600;">✗ (Tidak Sesuai)</option>
                                <option value="N/A" ${jawaban === 'N/A' ? 'selected' : ''} style="color:#475569; font-weight:600;">N/A</option>
                            </select>
                            ` : `
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check chk-answer" name="chk_answer_${chk.id}" id="chk_ya_${chk.id}" value="Ya" data-subarea="${code}" autocomplete="off" onchange="onChecklistChange()" ${isYa ? 'checked' : ''} ${!isRa ? 'disabled' : ''}>
                                <label class="btn btn-outline-success py-0 px-2" for="chk_ya_${chk.id}" style="font-size:0.72rem;">Ya</label>

                                <input type="radio" class="btn-check chk-answer" name="chk_answer_${chk.id}" id="chk_tidak_${chk.id}" value="Tidak" data-subarea="${code}" autocomplete="off" onchange="onChecklistChange()" ${!isYa ? 'checked' : ''} ${!isRa ? 'disabled' : ''}>
                                <label class="btn btn-outline-danger py-0 px-2" for="chk_tidak_${chk.id}" style="font-size:0.72rem;">Tidak</label>
                            </div>
                            `}
                        </td>
                        <td style="${isKredit ? 'background:#fffbeb;' : ''}">
                            <input type="text" class="form-control form-control-sm chk-keterangan" id="chk_ket_${chk.id}" data-checklist-id="${chk.id}" placeholder="Catatan opsional..." value="${ket.replace(/"/g, '&quot;')}" ${!isRa ? 'disabled' : ''} style="font-size:0.75rem; ${isKredit ? 'background-color:#fefce8; border-color:#fef08a;' : ''}">
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

    function toggleAccordionSubArea(collapseId) {
        const el = document.getElementById(collapseId);
        if (!el) return;
        const isVisible = (el.style.display !== 'none');
        el.style.display = isVisible ? 'none' : 'block';
        const icon = document.querySelector(`.toggle-icon-${collapseId}`);
        if (icon) {
            icon.style.transform = isVisible ? 'rotate(-90deg)' : 'rotate(0deg)';
        }
    }

    // Set seluruh checklist dari SEMUA sub-area menjadi Ya sekaligus
    function setGlobalAllYa(event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        const container = document.getElementById('checklistAccordionContainer');
        if (!container) return;
        const yaRadios = container.querySelectorAll('.btn-check[value="Ya"]');
        yaRadios.forEach(r => {
            r.checked = true;
            r.dispatchEvent(new Event('change', { bubbles: true }));
        });
        onChecklistChange();
    }

    // Set seluruh checklist pada sub-area tertentu menjadi Ya
    function setSubAreaAllYa(code, event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        const body = document.getElementById(`subAreaBody_${code}`);
        if (!body) return;
        const yaRadios = body.querySelectorAll('.btn-check[value="Ya"]');
        yaRadios.forEach(r => {
            r.checked = true;
            r.dispatchEvent(new Event('change', { bubbles: true }));
        });
        onChecklistChange();
    }

    // PENANGANAN REAKTIF CHECKLIST & OTOMASI SKOR RISIKO
    function onChecklistChange() {
        const isKredit = (currentActiveSheetCode === 'KKA_Kredit');
        let yaCount = 0;
        let tidakCount = 0;
        let naCount = 0;
        let dicekCount = 0;
        const subAreaTidakMap = {};

        if (isKredit) {
            document.querySelectorAll('.chk-kredit-select').forEach(sel => {
                const sub = sel.getAttribute('data-subarea');
                const val = sel.value;
                const chkId = sel.getAttribute('data-chk-id');
                const row = document.getElementById(`row_chk_${chkId}`);

                sel.style.cssText = 'font-size:0.75rem; font-weight:600; min-width:115px; ' + getKreditSelectStyle(val);

                if (val === '✓' || val === 'Ya') {
                    yaCount++;
                    dicekCount++;
                    if (row) row.classList.remove('table-danger', 'table-danger-subtle');
                } else if (val === '✗' || val === 'Tidak') {
                    tidakCount++;
                    dicekCount++;
                    subAreaTidakMap[sub] = (subAreaTidakMap[sub] || 0) + 1;
                    if (row) row.classList.add('table-danger');
                } else if (val === 'N/A') {
                    naCount++;
                    dicekCount++;
                    if (row) row.classList.remove('table-danger', 'table-danger-subtle');
                } else {
                    if (row) row.classList.remove('table-danger', 'table-danger-subtle');
                }
            });

            // Update Rekap Card KKA Kredit (Otomatis dari sheet KKA Kredit)
            const rekapTotalEl = document.getElementById('kredit_rekap_total');
            const rekapDicekEl = document.getElementById('kredit_rekap_dicek');
            const rekapSesuaiEl = document.getElementById('kredit_rekap_sesuai');
            const rekapTidakEl = document.getElementById('kredit_rekap_tidak_sesuai');
            const rekapNaEl = document.getElementById('kredit_rekap_na');
            const rekapPersenEl = document.getElementById('kredit_rekap_persen');

            const totalMaster = currentChecklists ? currentChecklists.length : 134;
            if (rekapTotalEl) rekapTotalEl.innerText = totalMaster;
            if (rekapDicekEl) rekapDicekEl.innerText = dicekCount;
            if (rekapSesuaiEl) rekapSesuaiEl.innerText = yaCount;
            if (rekapTidakEl) rekapTidakEl.innerText = tidakCount;
            if (rekapNaEl) rekapNaEl.innerText = naCount;
            if (rekapPersenEl) {
                const pct = totalMaster > 0 ? ((dicekCount / totalMaster) * 100).toFixed(1) : '0.0';
                rekapPersenEl.innerText = `${pct}%`;
            }

            const badgeYa = document.getElementById('badgeTotalYa');
            const badgeTidak = document.getElementById('badgeTotalTidak');
            if (badgeYa) badgeYa.innerText = `${yaCount} Sesuai (✓)`;
            if (badgeTidak) badgeTidak.innerText = `${tidakCount} Tidak Sesuai (✗)`;
        } else {
            document.querySelectorAll('.chk-answer:checked').forEach(r => {
                const sub = r.getAttribute('data-subarea');
                const chkId = r.name.replace('chk_answer_', '');
                const row = document.getElementById(`row_chk_${chkId}`);

                if (r.value === 'Tidak' || r.value === '✗') {
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
        }

        // Update SubArea Badges
        document.querySelectorAll('.subarea-tidak-badge').forEach(b => {
            const code = b.id.replace('subTidakBadge_', '');
            const cnt = subAreaTidakMap[code] || 0;
            if (cnt > 0) {
                b.innerText = `${cnt} Temuan Tidak`;
                b.style.display = 'inline-block';
            } else {
                b.style.display = 'none';
            }
        });

        // 2. Kemunculan Kondisional (Jenis Exception, Hasil Pengujian, Klarifikasi Unit)
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

        // 3. Otomasi Skor Risiko
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

        // Flagging Fraud SOP 09 saat Risk Final Critical (Poin 8)
        const fraudBanner = document.getElementById('fraud_flagging_banner');
        if (fraudBanner) {
            if (riskFinal === 'Critical') {
                fraudBanner.style.display = 'flex';
                const exc = document.getElementById('jenis_exception_ra') ? document.getElementById('jenis_exception_ra').value : '';
                const hasil = document.getElementById('hasil_uji') ? document.getElementById('hasil_uji').value : '';
                const fCode = currentKkaItem ? (currentKkaItem.unit_code || '') : '';
                const fName = currentKkaItem ? (currentKkaItem.unit_name || '') : '';
                const url = `{{ route('pemeriksaan_awal.pelaporan_awal') }}?source=OFFSITE_CRITICAL&dugaan=${encodeURIComponent(exc)}&fakta=${encodeURIComponent(hasil)}&kode_unit=${encodeURIComponent(fCode)}&nama_unit=${encodeURIComponent(fName)}&area=Offsite%20Audit`;
                const btnEsk = document.getElementById('btn_eskalasi_fraud_offsite');
                if (btnEsk) btnEsk.href = url;
            } else {
                fraudBanner.style.display = 'none';
            }
        }
    }

    function onOffsiteAdminEskalasiChange() {
        const val = document.getElementById('keputusan_eskalasi').value;
        const box = document.getElementById('adm_offsite_fraud_action_box');
        if (box) {
            if (val === 'Terindikasi Fraud (SOP 09)') {
                box.style.display = 'block';
                const exc = document.getElementById('jenis_exception_ra') ? document.getElementById('jenis_exception_ra').value : '';
                const hasil = document.getElementById('hasil_uji') ? document.getElementById('hasil_uji').value : '';
                const fCode = currentKkaItem ? (currentKkaItem.unit_code || '') : '';
                const fName = currentKkaItem ? (currentKkaItem.unit_name || '') : '';
                const url = `{{ route('pemeriksaan_awal.pelaporan_awal') }}?source=APPROVAL_ADMIN_OFFSITE_FRAUD&dugaan=${encodeURIComponent(exc)}&fakta=${encodeURIComponent(hasil)}&kode_unit=${encodeURIComponent(fCode)}&nama_unit=${encodeURIComponent(fName)}&area=Offsite%20Audit`;
                const btnDirect = document.getElementById('adm_btn_direct_fraud_offsite');
                if (btnDirect) btnDirect.href = url;
            } else {
                box.style.display = 'none';
            }
        }
    }

    function submitRaForm() {
        const id = document.getElementById('ra_kka_id').value;
        
        let tidakCount = 0;
        let unansweredCount = 0;
        const isKredit = (currentActiveSheetCode === 'KKA_Kredit');
        const checklistAnswers = [];
        currentChecklists.forEach(chk => {
            const selectEl = document.getElementById(`chk_answer_${chk.id}`);
            const tidakRadio = document.getElementById(`chk_tidak_${chk.id}`);
            const ketInput = document.getElementById(`chk_ket_${chk.id}`);

            let jawaban = '';
            if (selectEl) {
                jawaban = selectEl.value;
            } else {
                jawaban = (tidakRadio && tidakRadio.checked) ? 'Tidak' : 'Ya';
            }

            if (isKredit && !jawaban) {
                unansweredCount++;
            }

            if (jawaban === 'Tidak' || jawaban === '✗') tidakCount++;
            const keterangan = ketInput ? ketInput.value.trim() : '';
            checklistAnswers.push({
                checklist_id: chk.id,
                jawaban: jawaban || '✓',
                keterangan: keterangan
            });
        });

        if (isKredit && unansweredCount > 0) {
            if (!confirm(`Terdapat ${unansweredCount} item checklist yang belum dicek ([✓] Sesuai, [✗] Tidak Sesuai, atau N/A). Apakah Anda yakin ingin melanjutkan penyimpanan?`)) {
                return;
            }
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
        formData.append('bukti_referensi', document.getElementById('bukti_referensi').value);
        formData.append('critical_trigger', document.getElementById('critical_trigger').value);
        formData.append('jenis_exception_ra', tidakCount >= 1 ? jenisExc : '');
        formData.append('hasil_uji', tidakCount >= 1 ? hasilUji : '');
        formData.append('klarifikasi_unit', tidakCount >= 1 ? klarifikasi : '');
        formData.append('perlu_onsite', document.getElementById('perlu_onsite').value);
        formData.append('tanggal_ditemukan', document.getElementById('tanggal_ditemukan').value);
        formData.append('checklist_answers', JSON.stringify(checklistAnswers));

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
</script>
@endsection