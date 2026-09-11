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
    <div class="dashboard-header mb-4">
        <h4>Kertas Kerja Audit (KKA) Offsite</h4>
        <p>Review, verifikasi, dan tindak lanjuti indikasi temuan exception transaksi.</p>
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
                <p>Sudah Ditindaklanjuti <a href="#" style="font-weight: 600; color: #6366f1; text-decoration: none; margin-left: 5px;">Riwayat</a></p>
            </div>
        </div>
    </div>

    <!-- FOLDER TABS -->
    <div class="kka-tabs" id="kkaTabContainer">
        <div class="kka-tab-item active" onclick="switchTab(this, 'KKA Teller & Kas')">
            <i class="bi bi-folder2-open"></i> KKA Teller & Kas <span class="badge-count tab-teller">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA Kredit')">
            <i class="bi bi-folder2"></i> KKA Kredit <span class="badge-count tab-kredit">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA Biaya & Beban')">
            <i class="bi bi-folder2"></i> KKA Biaya & Beban <span class="badge-count tab-biaya">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA Biaya Internal')">
            <i class="bi bi-folder2"></i> KKA Biaya Internal <span class="badge-count tab-internal">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA Pengaduan')">
            <i class="bi bi-folder2"></i> KKA Pengaduan <span class="badge-count tab-pengaduan">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA Transaksi Umum')">
            <i class="bi bi-folder2"></i> KKA Transaksi Umum <span class="badge-count tab-umum">0</span>
        </div>
        <div class="kka-tab-item" onclick="switchTab(this, 'KKA Transfer & Pasiva')">
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
    const baseUrl = '{{ url("/offsite/kka") }}'; 
    const userRole = '{{ strtolower(trim(auth()->user()->role)) }}'; 
    const csrfToken = '{{ csrf_token() }}';
    
    let globalFindings = [];
    let currentActiveSheet = 'KKA Teller & Kas';

    document.addEventListener("DOMContentLoaded", function() {
        loadData();
    });

    // Pindah Tab Data
    function switchTab(clickedElement, tabName) {
        const allTabs = document.querySelectorAll('.kka-tab-item');
        allTabs.forEach(tab => tab.classList.remove('active'));

        clickedElement.classList.add('active');
        document.getElementById('tableTitle').innerText = tabName;
        currentActiveSheet = tabName;
        
        loadData();
    }

    // Ambil Data via API
    function loadData(page = 1) {
        const tbody = document.getElementById('kkaTableBody');
        tbody.innerHTML = `<tr><td colspan="10"><div class="empty-state" style="text-align: center; padding: 3rem 0; color: var(--text-muted);"><i class="bi bi-hourglass-split" style="font-size: 2rem; display: block; margin-bottom: 1rem;"></i><p>Memuat data...</p></div></td></tr>`;

        axios.get(`${baseUrl}/data?page=${page}&source_sheet=${currentActiveSheet}`)
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

                const activeTabBadge = document.querySelector('.kka-tab-item.active .badge-count');
                if(activeTabBadge) activeTabBadge.innerText = globalFindings.length;

                renderTable(globalFindings);
            })
            .catch(error => {
                tbody.innerHTML = `<tr><td colspan="10"><div class="empty-state" style="text-align: center; padding: 3rem 0; color: #dc2626;"><i class="bi bi-exclamation-triangle" style="font-size: 2rem; display: block; margin-bottom: 1rem;"></i><p>Gagal memuat data.</p></div></td></tr>`;
            });
    }

    // Render Tabel Utama
    function renderTable(data) {
        const tbody = document.getElementById('kkaTableBody');
        tbody.innerHTML = '';

        if(!data || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10"><div class="empty-state" style="text-align: center; padding: 3rem 0; color: var(--text-muted);"><i class="bi bi-inbox" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 1rem;"></i><p>Tidak ada temuan pada ${currentActiveSheet}.</p></div></td></tr>`;
            return;
        }

        data.forEach((item, index) => {
            let riskClass = (item.risk_awal || '').toLowerCase() === 'high' ? 'bg-danger text-white' : 'bg-warning text-dark';
            let riskBadge = `<span class="badge ${riskClass}" style="border-radius:12px; padding:0.4rem 0.8rem;">${item.risk_awal || 'Moderate'}</span>`;
            let nominalStr = item.nominal_terkait > 0 ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(item.nominal_terkait) : '-';

            const tr = `<tr>
                <td style="text-align:center; color:var(--text-muted);">${index + 1}</td>
                <td style="white-space:nowrap;">${item.tanggal_data || '-'}</td>
                <td><span class="badge bg-light text-dark border" style="border-radius:20px; padding: 0.3rem 0.6rem;">${item.kode_unit || '-'}</span></td>
                <td>${item.no_referensi || item.offsite_id || '-'}</td>
                <td>${item.user_maker || '-'}</td>
                <td>${item.kode_trx || '-'}</td>
                <td style="font-weight:700; text-align:right;">${nominalStr}</td>
                <td style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    ${item.deskripsi || item.catatan_rule || item.rincian || '-'}
                </td>
                <td style="text-align:center;">${riskBadge}</td>
                <td style="text-align:right;">
                    <button class="btn-review" onclick="openDetailView(${item.id})">
                        <i class="bi bi-arrow-right"></i> Review
                    </button>
                </td>
            </tr>`;
            tbody.insertAdjacentHTML('beforeend', tr);
        });
    }

    // FUNGSI TOGGLE KE FULL PAGE DETAIL VIEW
    function openDetailView(id) {
        const item = globalFindings.find(x => x.id == id);
        if(!item) return;

        // Populate Header & Summary
        document.getElementById('header_nama_sheet').innerText = currentActiveSheet;
        document.getElementById('head_staging_id').innerText = item.offsite_id || item.staging_id || `KKA-${item.id}`;
        document.getElementById('badge_status_atas').innerText = item.status_review || 'Belum Di-review';
        
        document.getElementById('sum_tanggal').innerText = item.tanggal_data || '-';
        document.getElementById('sum_unit').innerText = item.kode_unit || '-';
        document.getElementById('sum_user').innerText = item.user_maker || '-';
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
        
        // Mengisi otomatis teks deskripsi dari hasil parsing CSV
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
        formData.append('_method', 'PUT');

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
        }).catch(err => alert('Gagal mensubmit review RA!'));
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