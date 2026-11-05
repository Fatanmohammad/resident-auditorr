@extends('layouts.app')

@section('content')

@php $userRole = strtolower(auth()->user()->role); @endphp

<style>
    :root { --primary: #1e3a8a; --border: #e2e8f0; --muted: #64748b; --dark: #1e293b; }
    .stat-card { background:#fff; border:1px solid var(--border); border-radius:12px; padding:1.25rem; display:flex; align-items:flex-start; gap:1rem; }
    .stat-icon { width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; flex-shrink:0; }
    .kka-tabs { display:flex; gap:0.5rem; overflow-x:auto; padding-bottom:0.5rem; margin-bottom:1rem; border-bottom:1px solid var(--border); flex-wrap:nowrap; }
    .kka-tab-item { background:transparent; padding:0.6rem 1rem; font-size:0.85rem; font-weight:600; color:var(--muted); display:flex; align-items:center; gap:0.5rem; cursor:pointer; border-bottom:3px solid transparent; white-space:nowrap; flex-shrink:0; border-top:none; border-left:none; border-right:none; text-decoration:none; }
    .kka-tab-item.active { color:var(--primary); border-bottom-color:var(--primary); }
    .kka-tab-item .badge-count { background:#f1f5f9; color:#475569; padding:0.1rem 0.5rem; border-radius:10px; font-size:0.7rem; }
    .kka-tab-item.active .badge-count { background:#dbeafe; color:var(--primary); }
    .data-table { width:100%; border-collapse:collapse; min-width:900px; }
    .data-table th { padding:0.875rem 1rem; background:#f8fafc; font-size:0.75rem; color:var(--muted); border-bottom:1px solid var(--border); text-transform:uppercase; font-weight:600; }
    .data-table td { padding:0.875rem 1rem; vertical-align:middle; border-bottom:1px solid var(--border); font-size:0.84rem; }
    .data-table tbody tr:hover { background:#f8fafc; cursor:pointer; }
    .btn-review { background:var(--primary); color:#fff; font-size:0.8rem; font-weight:600; padding:0.35rem 0.9rem; border-radius:6px; border:none; cursor:pointer; }
    .btn-back { background:#fff; border:1px solid var(--border); padding:0.4rem 1rem; border-radius:6px; font-weight:600; font-size:0.85rem; color:var(--dark); cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem; }
    .sum-label { font-size:0.7rem; color:var(--muted); text-transform:uppercase; font-weight:600; margin-bottom:0.3rem; }
    .sum-val { font-weight:700; font-size:0.95rem; color:var(--dark); }
    .section-card { background:#fff; border:1px solid var(--border); border-radius:8px; margin-bottom:1.5rem; overflow:hidden; }
    .section-card-header { padding:1rem 1.5rem; font-weight:700; font-size:0.9rem; display:flex; align-items:center; gap:0.5rem; background:#f8fafc; border-bottom:1px solid var(--border); justify-content:space-between; }
    .section-card-body { padding:1.5rem; }
    .section-card.ra { border-color:#fde68a; }
    .section-card.ra .section-card-header { background:#fffbeb; border-bottom-color:#fde68a; color:#b45309; }
    .section-card.adm { border-color:#a7f3d0; }
    .section-card.adm .section-card-header { background:#ecfdf5; border-bottom-color:#a7f3d0; color:#047857; }
    .f-label { font-size:0.8rem; font-weight:600; color:var(--dark); margin-bottom:0.4rem; display:block; }
    .f-control { width:100%; border:1px solid var(--border); padding:0.55rem 0.75rem; font-size:0.85rem; border-radius:6px; box-sizing:border-box; background:#fff; }
    .f-control:focus { outline:none; border-color:var(--primary); }
    .f-control:disabled { background:#f8fafc; color:var(--muted); cursor:not-allowed; }
    .btn-submit-ra  { background:#f59e0b; color:#fff; border:none; padding:0.45rem 1.5rem; border-radius:6px; font-weight:600; cursor:pointer; }
    .btn-submit-adm { background:#10b981; color:#fff; border:none; padding:0.45rem 1.5rem; border-radius:6px; font-weight:600; cursor:pointer; }
</style>

{{-- ============================= DASHBOARD VIEW ============================= --}}
<div id="dashboardView">

    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; flex-wrap:wrap; gap:0.75rem;">
        <div>
            <a href="{{ route('onsite.index') }}" style="font-size:0.85rem; color:var(--muted); text-decoration:none;">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kunjungan
            </a>
            <h4 style="font-weight:700; color:var(--dark); margin:0.4rem 0 0.2rem;">KKA Onsite — {{ $visit->kode_unit }}</h4>
            <p style="color:var(--muted); font-size:0.88rem; margin:0;">
                {{ $visit->nama_unit }} &nbsp;|&nbsp;
                {{ \Carbon\Carbon::parse($visit->tanggal_mulai)->format('d M Y') }} s/d
                {{ \Carbon\Carbon::parse($visit->tanggal_selesai)->format('d M Y') }}
            </p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem;">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f1f5f9; color:#475569;"><i class="bi bi-file-earmark-text"></i></div>
            <div>
                <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Total Sampel</div>
                <div style="font-size:1.4rem; font-weight:700; color:var(--dark);" id="statTotal">0</div>
                <div style="font-size:0.75rem; color:var(--muted);">Transaksi CBS</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef2f2; color:#dc2626;"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">High Risk</div>
                <div style="font-size:1.4rem; font-weight:700; color:#dc2626;" id="statHigh">0</div>
                <div style="font-size:0.75rem; color:var(--muted);">Perlu Atensi</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fffbeb; color:#ca8a04;"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Belum Direview</div>
                <div style="font-size:1.4rem; font-weight:700; color:#ca8a04;" id="statBelum">0</div>
                <div style="font-size:0.75rem; color:var(--muted);">Menunggu Review</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4; color:#16a34a;"><i class="bi bi-check2-circle"></i></div>
            <div>
                <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Approved</div>
                <div style="font-size:1.4rem; font-weight:700; color:#16a34a;" id="statApproved">0</div>
                <div style="font-size:0.75rem; color:var(--muted);">Selesai Direview</div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigasi Lengkap (Termasuk Kontrol & Integrasi KKA serta 6 Area CBS & Pendukung) --}}
    <div class="kka-tabs">
        <button class="kka-tab-item active" onclick="switchTab(this,'sampling')">
            <i class="bi bi-file-earmark-bar-graph"></i> Ringkasan CBS <span class="badge-count" id="tabCountSampling">0</span>
        </button>
        <a href="{{ route('onsite.kka.integration.index', $visit->id) }}" class="kka-tab-item {{ request()->routeIs('onsite.kka.integration.*') ? 'active' : '' }}">
            <i class="bi bi-shield-check"></i> Kontrol & Integrasi KKA
        </a>
        <a href="{{ route('onsite.kka.teller_kas.index', $visit->id) }}" class="kka-tab-item">
            <i class="bi bi-wallet2"></i> 1. Teller & Kas
        </a>
        <a href="{{ route('onsite.kka.dpk.index', $visit->id) }}" class="kka-tab-item">
            <i class="bi bi-piggy-bank"></i> 2. DPK & Layanan
        </a>
        <a href="{{ route('onsite.kka.kredit.index', $visit->id) }}" class="kka-tab-item">
            <i class="bi bi-cash-coin"></i> 3. Kredit
        </a>
        <a href="{{ route('onsite.kka.biaya.index', $visit->id) }}" class="kka-tab-item">
            <i class="bi bi-receipt"></i> 4. Biaya & Beban
        </a>
        <a href="{{ route('onsite.kka.atm.index', $visit->id) }}" class="kka-tab-item">
            <i class="bi bi-credit-card"></i> 5. Kartu ATM
        </a>
        <a href="{{ route('onsite.kka.pengaduan.index', $visit->id) }}" class="kka-tab-item">
            <i class="bi bi-headset"></i> 6. Pengaduan
        </a>
        <button class="kka-tab-item" onclick="switchTab(this,'observasi')">
            <i class="bi bi-building-check"></i> Observasi Lingkungan
        </button>
        <button class="kka-tab-item" onclick="switchTab(this,'cash_opname')">
            <i class="bi bi-cash-stack"></i> Cash Opname
        </button>
        <button class="kka-tab-item" onclick="switchTab(this,'bct')">
            <i class="bi bi-journals"></i> Rekon Barang Cetakan
        </button>
        <button class="kka-tab-item" onclick="switchTab(this,'cekbg')">
            <i class="bi bi-file-earmark-ruled"></i> Rekon Cek &amp; BG
        </button>
        <button class="kka-tab-item" onclick="switchTab(this,'rekonsiliasi')">
            <i class="bi bi-arrow-left-right"></i> Rekon Kartu ATM
        </button>
    </div>

    {{-- Tab Content --}}
    <div id="tabSampling">
        <div style="display:flex; gap:0.75rem; margin-bottom:1.25rem; flex-wrap:wrap;">
            <div style="background:#fef2f2; border:1px solid #dc262640; border-radius:8px; padding:0.5rem 1rem; min-width:110px;">
                <div style="font-size:0.72rem; color:#dc2626; font-weight:600;">Mandatory</div>
                <div style="font-size:1.3rem; font-weight:700; color:#dc2626;" id="cntMandatory">0</div>
            </div>
            <div style="background:#fff7ed; border:1px solid #ea580c40; border-radius:8px; padding:0.5rem 1rem; min-width:110px;">
                <div style="font-size:0.72rem; color:#ea580c; font-weight:600;">Certainty</div>
                <div style="font-size:1.3rem; font-weight:700; color:#ea580c;" id="cntCertainty">0</div>
            </div>
            <div style="background:#fefce8; border:1px solid #ca8a0440; border-radius:8px; padding:0.5rem 1rem; min-width:110px;">
                <div style="font-size:0.72rem; color:#ca8a04; font-weight:600;">Targeted</div>
                <div style="font-size:1.3rem; font-weight:700; color:#ca8a04;" id="cntTargeted">0</div>
            </div>
            <div style="background:#f0fdf4; border:1px solid #16a34a40; border-radius:8px; padding:0.5rem 1rem; min-width:110px;">
                <div style="font-size:0.72rem; color:#16a34a; font-weight:600;">Initial</div>
                <div style="font-size:1.3rem; font-weight:700; color:#16a34a;" id="cntInitial">0</div>
            </div>
        </div>

        <div class="card" style="border-radius:10px; border:1px solid var(--border); overflow:hidden;">
            <div style="padding:0.875rem 1.25rem; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; background:#fff;">
                <div style="font-weight:700; color:var(--primary); font-size:0.9rem;">Daftar Sampel CBS</div>
                <div style="font-size:0.78rem; color:var(--muted);"><span id="totalCount">0</span> sampel</div>
            </div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; width:5%;">No</th>
                            <th>User / Score</th>
                            <th>Keterangan Transaksi</th>
                            <th style="text-align:center;">Jenis</th>
                            <th style="text-align:right;">Nominal</th>
                            <th style="text-align:center;">Risk</th>
                            <th style="text-align:center;">Status Review</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="kkaTableBody">
                        <tr><td colspan="8" style="text-align:center; padding:3rem; color:var(--muted);">
                            <i class="bi bi-hourglass-split" style="font-size:2rem; display:block; margin-bottom:0.75rem;"></i>
                            Memuat data...
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="tabObservasi" style="display:none;">
        @php $observasi = \App\Models\Onsite\OnsiteObservasi::where('onsite_visit_id',$visit->id)->first(); @endphp
        @if($observasi)
            <div style="text-align:center; padding:2rem;">
                <a href="{{ route('onsite.observasi.show', [$visit,$observasi]) }}" class="btn btn-sm btn-primary" style="background:var(--primary); border-color:var(--primary);">
                    <i class="bi bi-building-check me-1"></i> Buka Checklist Observasi ({{ $observasi->items()->count() }} item)
                </a>
            </div>
        @else
            <div style="text-align:center; padding:3rem 1rem;">
                <i class="bi bi-building" style="font-size:2.5rem; color:#cbd5e1; display:block; margin-bottom:1rem;"></i>
                <p style="color:var(--muted); margin-bottom:1.25rem;">Checklist observasi belum dibuat.</p>
                <a href="{{ route('onsite.observasi.create', $visit) }}" class="btn btn-sm btn-primary" style="background:var(--primary); border-color:var(--primary);">
                    <i class="bi bi-plus-circle me-1"></i> Buat Checklist Observasi
                </a>
            </div>
        @endif
    </div>

    <div id="tabCashOpname" style="display:none;">
        @php 
            $cashOpname = null;
            $hasRouteCreate = \Illuminate\Support\Facades\Route::has('onsite.cash_opname.create');
            $hasRouteShow = \Illuminate\Support\Facades\Route::has('onsite.cash_opname.show');
            try {
                if (class_exists(\App\Models\Onsite\OnsiteCashOpname::class)) {
                    $cashOpname = \App\Models\Onsite\OnsiteCashOpname::where('onsite_visit_id',$visit->id)->first();
                }
            } catch (\Throwable $e) {$cashOpname = null;
            }
        @endphp

        @if($cashOpname && $hasRouteShow)
            <div style="text-align:center; padding:2rem;">
                <a href="{{ route('onsite.cash_opname.show', [$visit,$cashOpname]) }}" class="btn btn-sm btn-primary" style="background:var(--primary); border-color:var(--primary);">
                    <i class="bi bi-cash-stack me-1"></i> Buka Berita Acara Cash Opname ({{ method_exists($cashOpname, 'items') ?$cashOpname->items()->count() : 0 }} item)
                </a>
            </div>
        @elseif($hasRouteCreate)
            <div style="text-align:center; padding:3rem 1rem;">
                <i class="bi bi-cash-stack" style="font-size:2.5rem; color:#cbd5e1; display:block; margin-bottom:1rem;"></i>
                <p style="color:var(--muted); margin-bottom:1.25rem;">Berita Acara Cash Opname belum dibuat.</p>
                <a href="{{ route('onsite.cash_opname.create', $visit) }}" class="btn btn-sm btn-primary" style="background:var(--primary); border-color:var(--primary);">
                    <i class="bi bi-plus-circle me-1"></i> Buat Berita Acara Cash Opname
                </a>
            </div>
        @else
            <div style="text-align:center; padding:3rem 1rem;">
                <i class="bi bi-tools" style="font-size:2.5rem; color:#cbd5e1; display:block; margin-bottom:1rem;"></i>
                <p style="color:var(--muted); font-weight:600;">Modul Cash Opname sedang dalam konfigurasi rute.</p>
            </div>
        @endif
    </div>

    @php
        $rekonBct   = \App\Models\Onsite\OnsiteRekonBarangCetakan::where('onsite_visit_id',$visit->id)->first();
        $rekonCekBg = \App\Models\Onsite\OnsiteRekonCekBg::where('onsite_visit_id',$visit->id)->first();
        $rekonAtm   = \App\Models\Onsite\OnsiteRekonKartuAtm::where('onsite_visit_id',$visit->id)->first();
    @endphp

    <div id="tabBct" style="display:none;">
        @if($rekonBct)
            <div style="text-align:center; padding:2rem;">
                <a href="{{ route('onsite.rekon.bct.show', [$visit,$rekonBct]) }}" class="btn btn-sm btn-primary" style="background:var(--primary); border-color:var(--primary);">
                    <i class="bi bi-journals me-1"></i> Buka Checklist Barang Cetakan ({{ $rekonBct->items()->count() }} item)
                </a>
            </div>
        @else
            <div style="text-align:center; padding:3rem 1rem;">
                <i class="bi bi-journals" style="font-size:2.5rem; color:#cbd5e1; display:block; margin-bottom:1rem;"></i>
                <p style="color:var(--muted); margin-bottom:1.25rem;">Checklist Rekon Barang Cetakan belum dibuat.</p>
                <a href="{{ route('onsite.rekon.bct.create', $visit) }}" class="btn btn-sm btn-primary" style="background:var(--primary); border-color:var(--primary);">
                    <i class="bi bi-plus-circle me-1"></i> Buat Checklist
                </a>
            </div>
        @endif
    </div>

    <div id="tabCekBg" style="display:none;">
        @if($rekonCekBg)
            <div style="text-align:center; padding:2rem;">
                <a href="{{ route('onsite.rekon.cekbg.show', [$visit,$rekonCekBg]) }}" class="btn btn-sm btn-primary" style="background:var(--primary); border-color:var(--primary);">
                    <i class="bi bi-file-earmark-ruled me-1"></i> Buka Checklist Cek &amp; BG ({{ $rekonCekBg->items()->count() }} item)
                </a>
            </div>
        @else
            <div style="text-align:center; padding:3rem 1rem;">
                <i class="bi bi-file-earmark-ruled" style="font-size:2.5rem; color:#cbd5e1; display:block; margin-bottom:1rem;"></i>
                <p style="color:var(--muted); margin-bottom:1.25rem;">Checklist Rekon Cek &amp; BG belum dibuat.</p>
                <a href="{{ route('onsite.rekon.cekbg.create', $visit) }}" class="btn btn-sm btn-primary" style="background:var(--primary); border-color:var(--primary);">
                    <i class="bi bi-plus-circle me-1"></i> Buat Checklist
                </a>
            </div>
        @endif
    </div>

    <div id="tabRekonsiliasi" style="display:none;">
        @if($rekonAtm)
            <div style="text-align:center; padding:2rem;">
                <a href="{{ route('onsite.rekon.katm.show', [$visit,$rekonAtm]) }}" class="btn btn-sm btn-primary" style="background:var(--primary); border-color:var(--primary);">
                    <i class="bi bi-credit-card-2-front me-1"></i> Buka Berita Acara Rekon Kartu ATM ({{ $rekonAtm->items()->count() }} item)
                </a>
            </div>
        @else
            <div style="text-align:center; padding:3rem 1rem;">
                <i class="bi bi-credit-card-2-front" style="font-size:2.5rem; color:#cbd5e1; display:block; margin-bottom:1rem;"></i>
                <p style="color:var(--muted); margin-bottom:1.25rem;">Berita Acara Rekonsiliasi Kartu ATM belum dibuat.</p>
                <a href="{{ route('onsite.rekon.katm.create', $visit) }}" class="btn btn-sm btn-primary" style="background:var(--primary); border-color:var(--primary);">
                    <i class="bi bi-plus-circle me-1"></i> Buat Berita Acara Rekon Kartu ATM
                </a>
            </div>
        @endif
    </div>

</div>{{-- end dashboardView --}}

{{-- ============================= DETAIL VIEW ============================= --}}
<div id="detailView" style="display:none;">

    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; flex-wrap:wrap; gap:0.75rem;">
        <div style="display:flex; gap:1rem; align-items:center;">
            <button class="btn-back" onclick="closeDetail()">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Sampel
            </button>
            <div>
                <h5 style="margin:0; font-weight:700; color:var(--primary);">Review KKA — <span id="det_jenis"></span></h5>
                <div style="font-size:0.82rem; color:var(--muted); margin-top:0.2rem;">ID: <span id="det_id"></span></div>
            </div>
        </div>
        <div id="det_status_badge" style="font-size:0.85rem; font-weight:600; color:var(--muted);"></div>
    </div>

    {{-- Summary Box --}}
    <div style="background:#fff; border:1px solid var(--border); border-radius:10px; padding:1.25rem; margin-bottom:1.5rem;">
        <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:1.25rem; margin-bottom:1rem;">
            <div>
                <div class="sum-label">User / Teller</div>
                <div class="sum-val" id="det_kd_user">—</div>
            </div>
            <div>
                <div class="sum-label">Tanggal Transaksi</div>
                <div class="sum-val" id="det_tgl_tx">—</div>
            </div>
            <div>
                <div class="sum-label">No Arsip</div>
                <div class="sum-val" id="det_no_arsip">—</div>
            </div>
            <div>
                <div class="sum-label">Nominal</div>
                <div class="sum-val" style="color:#dc2626; font-size:1.05rem;" id="det_nominal">—</div>
            </div>
            <div>
                <div class="sum-label">Jenis Sampling</div>
                <div id="det_jenis_badge">—</div>
            </div>
        </div>
        <div style="border-top:1px solid var(--border); padding-top:0.75rem; font-size:0.88rem;">
            <strong>Keterangan:</strong> <span id="det_ket_tx" style="color:var(--muted);">—</span>
            &nbsp;|&nbsp; <strong>Alasan Sampling:</strong> <span id="det_alasan" style="color:var(--muted);">—</span>
        </div>
    </div>

    {{-- Section RA --}}
    <div class="section-card ra">
        <div class="section-card-header">
            <div><i class="bi bi-pencil-square"></i> Hasil Pengujian &amp; Klarifikasi <span style="font-weight:normal; font-size:0.8rem;">(Porsi Resident Auditor)</span></div>
            <span id="badge_ra_lock" style="font-size:0.75rem; background:#fff; padding:0.2rem 0.6rem; border-radius:12px; border:1px solid #fde68a; color:var(--muted);">Mode Edit</span>
        </div>
        <div class="section-card-body">
            <form id="formKkaRa" enctype="multipart/form-data">
                <input type="hidden" id="ra_finding_id">

                <!-- 1. TABEL / ACCORDION CHECKLIST KKA (YA / TIDAK) -->
                <div id="checklistAccordionContainer" style="margin-bottom: 1.5rem;">
                    <div class="text-center py-3 text-muted" style="font-size:0.85rem;">
                        <i class="bi bi-hourglass-split me-1"></i> Memuat daftar checklist pengujian...
                    </div>
                </div>

                <!-- 2. KEMUNCULAN KONDISIONAL (HANYA MUNCUL JIKA MINIMAL 1 CHECKLIST TIDAK) -->
                <div id="containerExceptionFields" class="p-3 mb-3" style="display:none; background:#fff1f2; border:1px solid #fecdd3; border-radius:8px;">
                    <div class="d-flex align-items-center mb-2" style="color:#be123c; font-weight:700; font-size:0.85rem;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Temuan Pengujian Terdeteksi (Checklist Bernilai "Tidak")
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:0.75rem;">
                        <div>
                            <label class="f-label">Jenis Exception (Versi RA) <span style="color:#dc2626;">*</span></label>
                            <input type="text" class="f-control field-ra" id="ra_jenis_exception_ra" placeholder="Klasifikasi penyimpangan...">
                        </div>
                        <div>
                            <label class="f-label">Klarifikasi Unit Kerja / Cabang <span style="color:#dc2626;">*</span></label>
                            <textarea class="f-control field-ra" id="ra_klarifikasi_unit" rows="2" placeholder="Tanggapan pihak cabang..."></textarea>
                        </div>
                    </div>
                    <div>
                        <label class="f-label">Hasil Pengujian Lapangan <span style="color:#dc2626;">*</span></label>
                        <textarea class="f-control field-ra" id="ra_hasil_uji" rows="3" placeholder="Rincian fakta temuan hasil konfirmasi &amp; pengujian..."></textarea>
                    </div>
                </div>

                <!-- 3. FIELD BUKTI, CRITICAL TRIGGER & TANGGAL TERKUNCI -->
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1rem; margin-bottom:1rem;">
                    <div>
                        <label class="f-label">Bukti / Referensi Dokumen</label>
                        <input type="text" class="f-control field-ra" id="ra_bukti_referensi" placeholder="Misal: Surat No. 123/LHA/2026">
                    </div>
                    <div>
                        <label class="f-label">Critical Trigger</label>
                        <select class="f-control field-ra" id="ra_critical_trigger" onchange="onChecklistChange()">
                            <option value="Tidak">Tidak</option>
                            <option value="Ya">Ya (Otomatis Critical/High)</option>
                        </select>
                    </div>
                    <div>
                        <label class="f-label">Upload Bukti Transaksi (PDF)</label>
                        <input type="file" class="f-control field-ra" id="ra_file_bukti" accept=".pdf">
                        <div id="file_bukti_info" style="font-size:0.72rem; color:var(--muted); margin-top:0.2rem;"></div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                    <div>
                        <label class="f-label">Tanggal Ditemukan / Diuji <i class="bi bi-lock-fill text-muted" title="Terkunci otomatis pada tanggal RA membuka KKA"></i></label>
                        <input type="date" class="f-control" id="ra_tanggal_ditemukan" readonly style="background:#f1f5f9; cursor:not-allowed;">
                    </div>
                    <div>
                        <label class="f-label">Tingkat Tindak Lanjut <i class="bi bi-lock-fill text-muted" title="Otomatis terhitung dari skor risiko"></i></label>
                        <input type="hidden" id="ra_perlu_tindak_lanjut" value="Standar">
                        <div class="f-control" style="background:#f8fafc; font-weight:600; color:var(--dark); height: calc(1.5em + 1.1rem + 2px); display:flex; align-items:center;" id="display_tindak_lanjut">Pemeriksaan Standar</div>
                    </div>
                </div>

                <!-- 4. OTOMASI PENUH & PENGUNCIAN SKOR RISIKO 5x5 -->
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1rem; margin-bottom:1rem;">
                    <div>
                        <label class="f-label">Skor Dampak (1–5) <i class="bi bi-lock-fill text-muted" title="Terkunci otomatis matriks 5x5"></i></label>
                        <input type="hidden" id="ra_skor_dampak" value="1">
                        <div class="f-control" style="background:#f8fafc; font-weight:600; color:var(--dark);" id="display_skor_dampak">1 — Sangat Rendah</div>
                    </div>
                    <div>
                        <label class="f-label">Skor Kemungkinan (1–5) <i class="bi bi-lock-fill text-muted" title="Terkunci otomatis berbasis kejadian berulang"></i></label>
                        <input type="hidden" id="ra_skor_kemungkinan" value="1">
                        <div class="f-control" style="background:#f8fafc; font-weight:600; color:var(--dark);" id="display_skor_kemungkinan">1 — Sangat Jarang</div>
                    </div>
                    <div>
                        <label class="f-label">Skor &amp; Risk Final <i class="bi bi-lock-fill text-muted" title="Terkunci otomatis (Dampak x Kemungkinan)"></i></label>
                        <input type="hidden" id="ra_skor_final" value="1">
                        <input type="hidden" id="ra_risk_final" value="Low">
                        <div class="f-control" style="background:#f8fafc; display:flex; align-items:center; gap:0.5rem; height: calc(1.5em + 1.1rem + 2px);">
                            <span style="font-weight:700; font-size:1.1rem;" id="text_skor_final">1</span> 
                            <span style="color:var(--muted);">—</span> 
                            <span id="badge_risk_final" style="padding:0.2rem 0.5rem; border-radius:6px; font-weight:600; font-size:0.8rem; background:#dcfce7; color:#16a34a; border:1px solid #bbf7d0;">Low</span>
                        </div>
                    </div>
                </div>

                <!-- FLAGGING INDIKASI FRAUD (Poin 8 SOP 09) -->
                <div id="fraud_flagging_banner" style="display:none; background:#fef2f2; border:1px solid #f87171; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1rem; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                    <div style="display:flex; align-items:center; gap:0.5rem; color:#991b1b; font-size:0.85rem; font-weight:600;">
                        <i class="bi bi-flag-fill text-danger fs-5"></i>
                        <div>
                            <div>FLAGGING INDIKASI FRAUD TERDETEKSI (STATUS CRITICAL)</div>
                            <div style="font-weight:400; font-size:0.75rem; color:#b91c1c;">Temuan KKA ini berisiko Critical dan terindikasi fraud. Admin/Auditor dapat meneruskannya ke Modul Indikasi Fraud (SOP 09).</div>
                        </div>
                    </div>
                    <a href="#" id="btn_eskalasi_fraud_onsite" target="_blank" class="btn btn-sm btn-danger fw-bold shadow-sm" style="white-space:nowrap;">
                        <i class="bi bi-shield-exclamation me-1"></i> Teruskan ke Fraud SOP 09 &rarr;
                    </a>
                </div>

                <div id="ra_actions" style="display:flex; justify-content:flex-end; gap:0.75rem; border-top:1px solid var(--border); padding-top:1rem;">
                    <button type="button" class="btn-back" onclick="closeDetail()">Batal</button>
                    <button type="button" class="btn-submit-ra" onclick="submitRa()">
                        <i class="bi bi-send-fill me-1"></i> Simpan Hasil Pengujian
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Section Admin --}}
    @if(in_array($userRole, ['admin','kabag_ra','kadiv_skai']))
    <div class="section-card adm">
        <div class="section-card-header">
            <div><i class="bi bi-shield-check"></i> Review &amp; Keputusan Pengawasan <span style="font-weight:normal; font-size:0.8rem;">(Porsi Admin / Pimsie)</span></div>
            <span style="font-size:0.75rem; background:#fff; padding:0.2rem 0.6rem; border-radius:12px; border:1px solid #a7f3d0; color:var(--muted);">Mode Edit</span>
        </div>
        <div class="section-card-body">
            <input type="hidden" id="adm_finding_id">
            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1rem; margin-bottom:1rem;">
                <div>
                    <label class="f-label">Status Klarifikasi Unit</label>
                    <select id="adm_status_klarifikasi" class="f-control">
                        <option value="">-- Pilih --</option>
                        <option value="Sesuai / Selesai">Sesuai / Selesai</option>
                        <option value="Belum Sesuai">Belum Sesuai</option>
                        <option value="Tidak Ada Klarifikasi">Tidak Ada Klarifikasi</option>
                    </select>
                </div>
                <div>
                    <label class="f-label">Status Review <span style="color:#dc2626;">*</span></label>
                    <select id="adm_status_review" class="f-control">
                        <option value="Belum Direview">Belum Direview</option>
                        <option value="Revisi">Revisi (Minta RA Perbaiki)</option>
                        <option value="Approved">Approved (Selesai)</option>
                    </select>
                </div>
                <div>
                    <label class="f-label">Keputusan Eskalasi / Fraud Flagging</label>
                    <select id="adm_keputusan_eskalasi" class="f-control" onchange="onAdminEskalasiChange()">
                        <option value="Tidak">Tidak Dieskalasi</option>
                        <option value="Eskalasi Kadiv">Eskalasi ke Kadiv SKAI</option>
                        <option value="Eskalasi Direksi">Eskalasi ke Direksi</option>
                        <option value="Terindikasi Fraud (SOP 09)">Terindikasi Fraud (Teruskan ke SOP 09)</option>
                    </select>
                </div>
            </div>

            <!-- Box Disposisi Fraud Khusus Admin saat Approved/Terindikasi Fraud (Poin 8) -->
            <div id="adm_fraud_action_box" style="display:none; margin-bottom:1rem; background:#fff1f2; border:1px solid #fecdd3; padding:0.75rem 1rem; border-radius:8px;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <div class="fw-bold text-danger small"><i class="bi bi-shield-exclamation me-1"></i> Disposisi Eskalasi Fraud:</div>
                        <div class="small text-muted">Admin mengonfirmasi temuan ini sebagai indikasi fraud untuk ditindaklanjuti pada SOP 09.</div>
                    </div>
                    <a href="#" id="adm_btn_direct_fraud" target="_blank" class="btn btn-sm btn-danger fw-bold shadow-sm">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka Form SOP 09 dengan Data Terisi &rarr;
                    </a>
                </div>
            </div>
            <div style="margin-bottom:1rem;">
                <label class="f-label">Catatan Reviewer <span style="color:#dc2626;">*</span></label>
                <textarea id="adm_catatan_reviewer" rows="2" class="f-control" placeholder="Instruksi revisi atau catatan persetujuan..."></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:0.75rem; border-top:1px solid var(--border); padding-top:1rem;">
                <button type="button" class="btn-back" onclick="closeDetail()">Batal</button>
                <button type="button" class="btn-submit-adm" onclick="submitAdm()">
                    <i class="bi bi-check-circle-fill me-1"></i> Simpan Review
                </button>
            </div>
        </div>
    </div>
    @endif

</div>{{-- end detailView --}}

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
const userRole  = '{{ $userRole }}';
const visitId   = {{ $visit->id }};
const dataUrl   = '{{ route("onsite.kka.data", $visit) }}';
const kkaUrl    = '{{ url("/onsite/kka") }}';

const badgeMap = {
    'Mandatory': { bg: '#fef2f2', color: '#dc2626' },
    'Certainty': { bg: '#fff7ed', color: '#ea580c' },
    'Targeted':  { bg: '#fefce8', color: '#ca8a04' },
    'Initial':   { bg: '#f0fdf4', color: '#16a34a' },
};

let globalFindings = [];
let currentTab = 'sampling';

document.addEventListener('DOMContentLoaded', () => loadData());

function loadData() {
    const tbody = document.getElementById('kkaTableBody');
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:3rem; color:#94a3b8;">
        <i class="bi bi-hourglass-split" style="font-size:2rem; display:block; margin-bottom:0.75rem;"></i>Memuat data...</td></tr>`;

    axios.get(dataUrl)
        .then(res => {
            globalFindings = res.data.data;
            updateStats();
            renderTable(globalFindings);
        })
        .catch(() => {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:3rem; color:#dc2626;">
                <i class="bi bi-exclamation-triangle" style="font-size:2rem; display:block; margin-bottom:0.75rem;"></i>Gagal memuat data.</td></tr>`;
        });
}

function updateStats() {
    const total    = globalFindings.length;
    const high     = globalFindings.filter(x => x.risk_level === 'High').length;
    const belum    = globalFindings.filter(x => x.status_review === 'Belum Direview').length;
    const approved = globalFindings.filter(x => x.status_review === 'Approved').length;

    document.getElementById('statTotal').innerText    = total;
    document.getElementById('statHigh').innerText     = high;
    document.getElementById('statBelum').innerText    = belum;
    document.getElementById('statApproved').innerText = approved;
    document.getElementById('totalCount').innerText   = total;
    document.getElementById('tabCountSampling').innerText = total;

    document.getElementById('cntMandatory').innerText = globalFindings.filter(x => x.jenis_sampling === 'Mandatory').length;
    document.getElementById('cntCertainty').innerText = globalFindings.filter(x => x.jenis_sampling === 'Certainty').length;
    document.getElementById('cntTargeted').innerText  = globalFindings.filter(x => x.jenis_sampling === 'Targeted').length;
    document.getElementById('cntInitial').innerText   = globalFindings.filter(x => x.jenis_sampling === 'Initial').length;
}

function renderTable(data) {
    const tbody = document.getElementById('kkaTableBody');
    tbody.innerHTML = '';

    if (!data || data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:3rem; color:#94a3b8;">
            <i class="bi bi-inbox" style="font-size:2.5rem; display:block; margin-bottom:0.75rem;"></i>Belum ada sampel.</td></tr>`;
        return;
    }

    data.forEach((f, i) => {
        const bm = badgeMap[f.jenis_sampling] || { bg: '#f8fafc', color: '#64748b' };
        const riskColor = { High: '#dc2626', Moderate: '#ea580c', Low: '#16a34a' }[f.risk_level] || '#94a3b8';
        const reviewColor = { Approved: '#16a34a', Revisi: '#ea580c' }[f.status_review] || '#94a3b8';
        const nominal = 'Rp ' + Number(f.jumlah_tx).toLocaleString('id-ID');

        tbody.insertAdjacentHTML('beforeend', `<tr onclick="openDetail(${f.id})" style="cursor:pointer;">
            <td style="text-align:center; color:var(--muted);">${i + 1}</td>
            <td>
                <div style="font-weight:600; color:var(--dark);">${f.kd_user || '—'}</div>
                <div style="font-size:0.75rem; color:#94a3b8;">Score: ${f.stable_score}</div>
            </td>
            <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${f.ket_tx || ''}">
                <div>${f.ket_tx || '—'}</div>
                <div style="font-size:0.75rem; color:#94a3b8;">${f.tgl_tx || ''}</div>
            </td>
            <td style="text-align:center;">
                <span style="background:${bm.bg}; color:${bm.color}; padding:0.2rem 0.6rem; border-radius:20px; font-size:0.72rem; font-weight:600;">${f.jenis_sampling || '—'}</span>
            </td>
            <td style="text-align:right; font-weight:600;">${nominal}</td>
            <td style="text-align:center;">
                <span style="color:${riskColor}; font-weight:700;">${f.risk_level || 'Low'}</span>
            </td>
            <td style="text-align:center;">
                <span style="color:${reviewColor}; font-weight:600;">${f.status_review || 'Belum Direview'}</span>
            </td>
            <td style="text-align:center;">
                <button class="btn-review" onclick="event.stopPropagation(); openDetail(${f.id})">Review</button>
            </td>
        </tr>`);
    });
}

function switchTab(btn, tab) {
    document.querySelectorAll('.kka-tab-item').forEach(e => e.classList.remove('active'));
    btn.classList.add('active');

    document.getElementById('tabSampling').style.display     = tab === 'sampling' ? 'block' : 'none';
    document.getElementById('tabObservasi').style.display    = tab === 'observasi' ? 'block' : 'none';
    document.getElementById('tabCashOpname').style.display   = tab === 'cash_opname' ? 'block' : 'none';
    document.getElementById('tabBct').style.display          = tab === 'bct' ? 'block' : 'none';
    document.getElementById('tabCekBg').style.display        = tab === 'cekbg' ? 'block' : 'none';
    document.getElementById('tabRekonsiliasi').style.display = tab === 'rekonsiliasi' ? 'block' : 'none';
    currentTab = tab;
}

let currentItem = null;
let currentChecklists = [];
let currentRecurringCount = 1;

function openDetail(id) {
    axios.get(`${kkaUrl}/${id}/review-data`)
        .then(res => {
            currentItem = res.data.finding;
            currentChecklists = res.data.checklists || [];
            currentRecurringCount = res.data.recurring_count || 1;
            populateDetail(currentItem);
        })
        .catch(() => {
            const f = globalFindings.find(x => x.id === id);
            if (f) {
                currentItem = f;
                currentChecklists = [];
                currentRecurringCount = 1;
                populateDetail(f);
            }
        });
}

function populateDetail(f) {
    document.getElementById('det_id').innerText = f.id;
    document.getElementById('det_jenis').innerText = f.jenis_sampling || '—';
    document.getElementById('det_kd_user').innerText = f.kd_user || '—';
    document.getElementById('det_tgl_tx').innerText = f.tgl_tx || '—';
    document.getElementById('det_no_arsip').innerText = f.no_arsip || '—';
    document.getElementById('det_nominal').innerText = 'Rp ' + Number(f.jumlah_tx).toLocaleString('id-ID');
    document.getElementById('det_ket_tx').innerText = f.ket_tx || '—';
    document.getElementById('det_alasan').innerText = f.alasan_sampling || '—';

    const bm = badgeMap[f.jenis_sampling] || { bg: '#f8fafc', color: '#64748b' };
    document.getElementById('det_jenis_badge').innerHTML = `<span style="background:${bm.bg}; color:${bm.color}; padding:0.2rem 0.6rem; border-radius:20px; font-size:0.75rem; font-weight:600;">${f.jenis_sampling}</span>`;

    // Populate Section 2 (RA)
    document.getElementById('ra_finding_id').value = f.id;
    document.getElementById('ra_bukti_referensi').value = f.bukti_referensi || '';
    document.getElementById('ra_critical_trigger').value = f.critical_trigger || 'Tidak';
    document.getElementById('ra_jenis_exception_ra').value = f.jenis_exception_ra || '';
    document.getElementById('ra_hasil_uji').value = f.hasil_uji || '';
    document.getElementById('ra_klarifikasi_unit').value = f.klarifikasi_unit || '';

    // Tanggal Ditemukan
    const d = new Date();
    const localTodayStr = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    const tanggalVal = f.tanggal_ditemukan ? f.tanggal_ditemukan.split('T')[0].split(' ')[0] : localTodayStr;
    const tglInput = document.getElementById('ra_tanggal_ditemukan');
    tglInput.value = tanggalVal;
    tglInput.readOnly = true;

    // Info File Bukti
    const fileInfoEl = document.getElementById('file_bukti_info');
    if (fileInfoEl) {
        if (f.file_bukti) {
            fileInfoEl.innerHTML = `<a href="/storage/${f.file_bukti}" target="_blank" class="text-primary text-decoration-none"><i class="bi bi-file-earmark-pdf"></i> Lihat File Bukti Terunggah</a>`;
        } else {
            fileInfoEl.innerHTML = `Belum ada file bukti PDF diunggah.`;
        }
    }

    // Render Checklist Accordion
    renderChecklistAccordion(currentChecklists, f.checklist_answers || []);

    // Populate Section 3 (Admin)
    if (document.getElementById('adm_finding_id')) {
        document.getElementById('adm_finding_id').value = f.id;
        document.getElementById('adm_status_klarifikasi').value = f.status_klarifikasi || '';
        document.getElementById('adm_status_review').value = f.status_review || 'Belum Direview';
        document.getElementById('adm_keputusan_eskalasi').value = f.keputusan_eskalasi || 'Tidak';
        document.getElementById('adm_catatan_reviewer').value = f.catatan_reviewer || '';
    }

    onChecklistChange();

    // Lock Logic based on Role
    const isRa = (userRole === 'ra' || userRole === 'admin');
    document.querySelectorAll('.field-ra').forEach(el => el.disabled = !isRa);
    const admBtn = document.querySelector('.btn-submit-adm');
    if (admBtn && userRole === 'ra') admBtn.disabled = true;

    document.getElementById('dashboardView').style.display = 'none';
    document.getElementById('detailView').style.display = 'block';
    window.scrollTo(0, 0);
}

function closeDetail() {
    document.getElementById('detailView').style.display = 'none';
    document.getElementById('dashboardView').style.display = 'block';
}

// RENDER ACCORDION CHECKLIST KKA DENGAN KELOMPOK PER SUB-AREA
function renderChecklistAccordion(checklists, existingAnswers) {
    const container = document.getElementById('checklistAccordionContainer');
    if (!container) return;

    if (!checklists || checklists.length === 0) {
        container.innerHTML = `<div class="p-3 text-muted text-center" style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px;">Tidak ada master checklist untuk kategori ini.</div>`;
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

    const isRa = (userRole === 'ra' || userRole === 'admin');

    let html = `
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded" style="background:#f1f5f9; border:1px solid var(--border);">
            <div style="font-size:0.83rem; font-weight:700; color:var(--primary);">
                <i class="bi bi-ui-checks me-1"></i> CHECKLIST PENGUJIAN AUDIT (${checklists.length} ITEM)
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

        html += `
        <div class="accordion-item" style="border: 1px solid var(--border); border-radius: 8px; margin-bottom: 0.65rem; overflow:hidden;">
            <h2 class="accordion-header" id="heading_${code}" style="background:#f8fafc;">
                <div class="d-flex justify-content-between align-items-center w-100 p-2 px-3" style="cursor:pointer;" onclick="toggleAccordionSubArea('${collapseId}')">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <i class="bi bi-chevron-down toggle-icon-${collapseId}" style="font-size:0.8rem; transition: transform 0.2s;"></i>
                        <span class="fw-bold" style="font-size:0.82rem; color:var(--dark);">${grp.name}</span>
                        <span class="badge bg-light text-secondary border" style="font-size:0.7rem;">${grp.items.length} Item</span>
                        <span class="badge bg-danger text-white subarea-tidak-badge" id="subTidakBadge_${code}" style="display:none; font-size:0.7rem;">0 Temuan Tidak</span>
                    </div>
                    ${isRa ? `
                    <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 fw-semibold" style="font-size:0.72rem; border-radius:4px;" onclick="setSubAreaAllYa('${code}', event)">
                        <i class="bi bi-check-all"></i> Set Semua Ya
                    </button>
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
            const jawaban = saved ? saved.jawaban : 'Ya';
            const ket = saved ? saved.keterangan : '';
            const isYa = (jawaban === 'Ya');

            html += `
                <tr id="row_chk_${chk.id}" class="${!isYa ? 'table-danger' : ''}">
                    <td style="text-align:center; color:var(--muted);">${chk.item_no}</td>
                    <td style="color:var(--dark); font-weight:500;">${chk.sub_area_name}</td>
                    <td style="color:var(--dark);">${chk.item_checklist}</td>
                    <td style="text-align:center;">
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check chk-answer" name="chk_answer_${chk.id}" id="chk_ya_${chk.id}" value="Ya" data-subarea="${code}" autocomplete="off" onchange="onChecklistChange()" ${isYa ? 'checked' : ''} ${!isRa ? 'disabled' : ''}>
                            <label class="btn btn-outline-success py-0 px-2" for="chk_ya_${chk.id}" style="font-size:0.72rem;">Ya</label>

                            <input type="radio" class="btn-check chk-answer" name="chk_answer_${chk.id}" id="chk_tidak_${chk.id}" value="Tidak" data-subarea="${code}" autocomplete="off" onchange="onChecklistChange()" ${!isYa ? 'checked' : ''} ${!isRa ? 'disabled' : ''}>
                            <label class="btn btn-outline-danger py-0 px-2" for="chk_tidak_${chk.id}" style="font-size:0.72rem;">Tidak</label>
                        </div>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm chk-keterangan" id="chk_ket_${chk.id}" data-checklist-id="${chk.id}" placeholder="Catatan opsional..." value="${ket.replace(/"/g, '&quot;')}" ${!isRa ? 'disabled' : ''} style="font-size:0.75rem;">
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
    });
    onChecklistChange();
}

function onChecklistChange() {
    let yaCount = 0;
    let tidakCount = 0;
    const subAreaTidakMap = {};

    document.querySelectorAll('.chk-answer:checked').forEach(r => {
        const sub = r.getAttribute('data-subarea');
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

    // Kemunculan Kondisional
    const containerExc = document.getElementById('containerExceptionFields');
    const inputJenisExc = document.getElementById('ra_jenis_exception_ra');
    const inputHasilUji = document.getElementById('ra_hasil_uji');
    const inputKlarifikasi = document.getElementById('ra_klarifikasi_unit');

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

    hitungOtomatisSkorRisiko(tidakCount);
}

function hitungOtomatisSkorRisiko(tidakCount) {
    const nominal = currentItem ? (parseFloat(currentItem.jumlah_tx) || 0) : 0;
    const criticalTrigger = document.getElementById('ra_critical_trigger') ? document.getElementById('ra_critical_trigger').value : 'Tidak';
    const recurringCount = currentRecurringCount || 1;

    let skorDampak = 1;
    let labelDampak = '1 — Sangat Rendah';
    let skorKemungkinan = 1;
    let labelKemungkinan = '1 — Sangat Jarang';
    let skorFinal = 1;
    let riskFinal = 'Low';
    let tindakLanjut = 'Standar';

    if (tidakCount === 0 && criticalTrigger === 'Tidak') {
        skorDampak = 1;
        labelDampak = '1 — Sangat Rendah';
        skorKemungkinan = 1;
        labelKemungkinan = '1 — Sangat Jarang';
        skorFinal = 1;
        riskFinal = 'Low';
        tindakLanjut = 'Standar';
    } else {
        if (criticalTrigger === 'Ya' || nominal >= 500000000) {
            skorDampak = 5;
            labelDampak = '5 — Sangat Tinggi';
        } else {
            const hasNominal = currentItem && currentItem.jumlah_tx !== null && nominal > 0;
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

        if (['Moderate to High', 'High', 'Critical'].includes(riskFinal) || skorFinal >= 8 || criticalTrigger === 'Ya') {
            tindakLanjut = 'Atensi Khusus & Eskalasi';
        } else {
            tindakLanjut = 'Pemeriksaan Standar';
        }
    }

    document.getElementById('ra_skor_dampak').value = skorDampak;
    document.getElementById('display_skor_dampak').innerText = labelDampak;

    document.getElementById('ra_skor_kemungkinan').value = skorKemungkinan;
    document.getElementById('display_skor_kemungkinan').innerHTML = `${labelKemungkinan} <span class="badge bg-light text-secondary border ms-1" style="font-size:0.7rem;">Kejadian ke-${recurringCount}</span>`;

    document.getElementById('ra_skor_final').value = skorFinal;
    document.getElementById('ra_risk_final').value = riskFinal;
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

    document.getElementById('ra_perlu_tindak_lanjut').value = tindakLanjut;
    const tlEl = document.getElementById('display_tindak_lanjut');
    tlEl.innerText = tindakLanjut;
    if (tindakLanjut.includes('Khusus')) {
        tlEl.style.color = '#dc2626';
        tlEl.style.fontWeight = '700';
    } else {
        tlEl.style.color = 'var(--dark)';
        tlEl.style.fontWeight = '600';
    }

    // Flagging Fraud SOP 09 saat Risk Final Critical (Poin 8)
    const fraudBanner = document.getElementById('fraud_flagging_banner');
    if (fraudBanner) {
        if (riskFinal === 'Critical') {
            fraudBanner.style.display = 'flex';
            const exc = document.getElementById('ra_jenis_exception_ra') ? document.getElementById('ra_jenis_exception_ra').value : '';
            const hasil = document.getElementById('ra_hasil_uji') ? document.getElementById('ra_hasil_uji').value : '';
            const fCode = currentFinding ? (currentFinding.unit_code || '') : '';
            const fName = currentFinding ? (currentFinding.unit_name || '') : '';
            const url = `{{ route('pemeriksaan_awal.pelaporan_awal') }}?source=ONSITE_CRITICAL&dugaan=${encodeURIComponent(exc)}&fakta=${encodeURIComponent(hasil)}&kode_unit=${encodeURIComponent(fCode)}&nama_unit=${encodeURIComponent(fName)}&area=Onsite%20Audit`;
            const btnEsk = document.getElementById('btn_eskalasi_fraud_onsite');
            if (btnEsk) btnEsk.href = url;
        } else {
            fraudBanner.style.display = 'none';
        }
    }
}

function onAdminEskalasiChange() {
    const val = document.getElementById('adm_keputusan_eskalasi').value;
    const box = document.getElementById('adm_fraud_action_box');
    if (box) {
        if (val === 'Terindikasi Fraud (SOP 09)') {
            box.style.display = 'block';
            const exc = document.getElementById('ra_jenis_exception_ra') ? document.getElementById('ra_jenis_exception_ra').value : '';
            const hasil = document.getElementById('ra_hasil_uji') ? document.getElementById('ra_hasil_uji').value : '';
            const fCode = currentFinding ? (currentFinding.unit_code || '') : '';
            const fName = currentFinding ? (currentFinding.unit_name || '') : '';
            const url = `{{ route('pemeriksaan_awal.pelaporan_awal') }}?source=APPROVAL_ADMIN_FRAUD&dugaan=${encodeURIComponent(exc)}&fakta=${encodeURIComponent(hasil)}&kode_unit=${encodeURIComponent(fCode)}&nama_unit=${encodeURIComponent(fName)}&area=Onsite%20Audit`;
            const btnDirect = document.getElementById('adm_btn_direct_fraud');
            if (btnDirect) btnDirect.href = url;
        } else {
            box.style.display = 'none';
        }
    }
}

function submitRa() {
    const id = document.getElementById('ra_finding_id').value;
    let tidakCount = 0;
    const checklistAnswers = [];
    currentChecklists.forEach(chk => {
        const tidakRadio = document.getElementById(`chk_tidak_${chk.id}`);
        const ketInput = document.getElementById(`chk_ket_${chk.id}`);
        const jawaban = (tidakRadio && tidakRadio.checked) ? 'Tidak' : 'Ya';
        if (jawaban === 'Tidak') tidakCount++;
        const keterangan = ketInput ? ketInput.value.trim() : '';
        checklistAnswers.push({
            checklist_id: chk.id,
            jawaban: jawaban,
            keterangan: keterangan
        });
    });

    const jenisExc = document.getElementById('ra_jenis_exception_ra').value.trim();
    const hasilUji = document.getElementById('ra_hasil_uji').value.trim();
    const klarifikasi = document.getElementById('ra_klarifikasi_unit').value.trim();

    if (tidakCount >= 1) {
        if (!jenisExc) {
            alert('Jenis Exception (Versi RA) wajib diisi karena terdapat item checklist bernilai Tidak!');
            document.getElementById('ra_jenis_exception_ra').focus();
            return;
        }
        if (!hasilUji) {
            alert('Hasil Pengujian Lapangan wajib diisi karena terdapat item checklist bernilai Tidak!');
            document.getElementById('ra_hasil_uji').focus();
            return;
        }
        if (!klarifikasi) {
            alert('Klarifikasi Unit Kerja / Cabang wajib diisi karena terdapat item checklist bernilai Tidak!');
            document.getElementById('ra_klarifikasi_unit').focus();
            return;
        }
    }

    let formData = new FormData();
    formData.append('bukti_referensi', document.getElementById('ra_bukti_referensi').value);
    formData.append('critical_trigger', document.getElementById('ra_critical_trigger').value);
    formData.append('jenis_exception_ra', tidakCount >= 1 ? jenisExc : '');
    formData.append('hasil_uji', tidakCount >= 1 ? hasilUji : '');
    formData.append('klarifikasi_unit', tidakCount >= 1 ? klarifikasi : '');
    formData.append('tanggal_ditemukan', document.getElementById('ra_tanggal_ditemukan').value);
    formData.append('checklist_answers', JSON.stringify(checklistAnswers));

    const fileInput = document.getElementById('ra_file_bukti');
    if (fileInput && fileInput.files.length > 0) {
        formData.append('file_bukti', fileInput.files[0]);
    }

    axios.post(`${kkaUrl}/${id}`, formData, { 
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'multipart/form-data' } 
    }).then(res => {
        alert(res.data.message || 'Hasil pengujian RA berhasil disimpan.');
        closeDetail();
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

function submitAdm() {
    const id = document.getElementById('adm_finding_id').value;
    const payload = {
        status_review: document.getElementById('adm_status_review').value,
        status_klarifikasi: document.getElementById('adm_status_klarifikasi').value,
        keputusan_eskalasi: document.getElementById('adm_keputusan_eskalasi').value,
        catatan_reviewer: document.getElementById('adm_catatan_reviewer').value,
        _token: csrfToken
    };

    axios.post(`${kkaUrl}/${id}/review`, payload, {
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(res => {
        alert(res.data.message || 'Status review berhasil disimpan.');
        closeDetail();
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
            alert('Gagal menyimpan review.');
        }
    });
}
</script>
@endpush