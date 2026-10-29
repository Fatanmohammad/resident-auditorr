@extends('layouts.app')
@section('title', 'KKA ' . $kka->visit_id . ' - Kertas Kerja Risk Trigger Visit')

@section('content')
<style>
    .section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: -0.01em;
        margin-bottom: 0.75rem;
    }
    .info-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.85rem 1rem;
    }
    .info-label {
        font-size: 0.72rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.25rem;
    }
    .info-value {
        font-size: 0.88rem;
        color: #1e293b;
        font-weight: 600;
    }

    /* Tabs Area */
    .area-nav-tabs {
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-bottom: 1.25rem;
    }
    .area-nav-tab {
        padding: 0.65rem 1rem;
        font-size: 0.84rem;
        font-weight: 600;
        color: #64748b;
        border-radius: 8px 8px 0 0;
        border: 1px solid transparent;
        border-bottom: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        background: #f8fafc;
        transition: all 0.2s ease;
    }
    .area-nav-tab:hover {
        background: #eff6ff;
        color: var(--bs-blue);
    }
    .area-nav-tab.active {
        background: #ffffff;
        color: var(--bs-blue);
        border-color: #cbd5e1 #cbd5e1 #ffffff #cbd5e1;
        border-top: 3px solid var(--bs-blue);
        font-weight: 700;
    }

    .data-table th {
        font-size: 0.76rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        background: #f8fafc;
        padding: 0.75rem 0.85rem;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    .data-table td {
        font-size: 0.82rem;
        padding: 0.75rem 0.85rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
    }

    .badge-status {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }
    .badge-closed { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-open { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-draft { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .badge-revisi { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
    .badge-final { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
</style>

<!-- Header Harmonized SOP 06 -->
<div class="rtv-header">
    <div>
        <div class="rtv-breadcrumb">
            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a> &rsaquo;
            <a href="{{ route('risk-trigger.kka.index') }}">KKA Risk Trigger</a> &rsaquo;
            <span class="text-dark fw-semibold">{{ $kka->visit_id }}</span>
        </div>
        <h4 class="rtv-title">
            <i class="bi bi-file-earmark-spreadsheet text-primary"></i> Lembar Kerja KKA Induk &amp; 8 Area
            <span class="badge-wp">{{ $kka->visit_id }}</span>
            <span class="badge-status {{ $kka->status_review === 'FINAL' ? 'badge-status-success' : ($kka->status_review === 'REVISI' ? 'badge-status-warning' : 'badge-status-neutral') }}">
                STATUS: {{ $kka->status_review }}
            </span>
        </h4>
        <p class="rtv-subtitle">
            KKA Induk (20 Kolom Excel A s.d. T) dan pengujian spesifik per-area risiko.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('risk-trigger.kka.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar KKA
        </a>
    </div>
</div>

    {{-- Alert Notice jika KKA sudah FINAL --}}
    @if($isFinal)
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-3" style="border-radius: 12px; background: #ecfdf5; border-left: 5px solid #10b981 !important;">
        <i class="bi bi-shield-lock-fill fs-2 text-success"></i>
        <div>
            <div class="fw-bold text-success" style="font-size: 0.92rem;">KKA INDUK TELAH BERSTATUS FINAL & TERKUNCI</div>
            <div class="text-dark small">
                Hasil pengujian pada KKA Induk dan seluruh 8 KKA Area telah disetujui (FINAL) oleh Reviewer. Pengubahan data oleh Resident Auditor (RA) dinonaktifkan.
            </div>
        </div>
    </div>
    @elseif($kka->status_review == 'REVISI')
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-3" style="border-radius: 12px; background: #fffbeb; border-left: 5px solid #f59e0b !important;">
        <i class="bi bi-exclamation-triangle-fill fs-2 text-warning"></i>
        <div>
            <div class="fw-bold text-warning" style="font-size: 0.92rem;">PERLU REVISI OLEH RESIDENT AUDITOR</div>
            <div class="text-dark small">
                Reviewer meminta tindak lanjut / perbaikan atas pengujian lapangan ini. Silakan periksa kembali bukti, analisis, dan baris sampel pada KKA Area terkait.
            </div>
        </div>
    </div>
    @endif

    {{-- Master Assignment & Trigger Info Card --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3 p-md-4">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <div class="info-label">Visit ID (A)</div>
                        <div class="info-value text-primary font-monospace">{{ $kka->visit_id }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <div class="info-label">Trigger ID (B)</div>
                        <div class="info-value text-secondary font-monospace">{{ $kka->trigger_id }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <div class="info-label">Unit / Kantor Cabang (C)</div>
                        <div class="info-value text-dark">{{ $kka->unit_kantor }} <span class="badge bg-light text-secondary border">({{ $kka->kode_unit }})</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <div class="info-label">Area & Risk Level (D & E)</div>
                        <div class="info-value d-flex align-items-center gap-2">
                            <span>{{ $kka->area }}</span>
                            <span class="badge {{ $kka->risk_level == 'TINGGI' || $kka->risk_level == 'HIGH' ? 'bg-danger' : 'bg-warning text-dark' }}" style="font-size: 0.72rem;">
                                {{ $kka->risk_level }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <div class="info-label">KKA Tujuan Area (O)</div>
                        <div class="info-value text-primary font-monospace">
                            <i class="bi bi-box-arrow-in-right me-1"></i>{{ $kka->kka_area }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <div class="info-label">Exception Area (P - Real-Time)</div>
                        <div class="info-value">
                            @if(($kka->exception_area ?? 0) > 0)
                                <span class="badge bg-danger px-2.5 py-1">
                                    <i class="bi bi-exclamation-octagon-fill me-1"></i> {{ $kka->exception_area }} Temuan Exception
                                </span>
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i> 0 Temuan (Sesuai)
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <div class="info-label">RA Pelaksana (R)</div>
                        <div class="info-value text-dark">{{ $kka->ra_pelaksana ?: '-' }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <div class="info-label">Reviewer (S)</div>
                        <div class="info-value text-dark">{{ $kka->reviewer ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BAGIAN 1: FORM INPUT KKA INDUK (KKA_Risk_Trigger - 20 KOLOM EXCEL) --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2 border-bottom">
            <div>
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-card-checklist text-primary me-2"></i>
                    Bagian 1: KKA Induk (KKA_Risk_Trigger - Rumus Otomatis & Pertimbangan Profesional)
                </h6>
                <div class="small text-muted mt-0.5" style="font-size: 0.78rem;">
                    20 Kolom Excel A s.d. T &bull; Pertimbangan profesional auditor atas trigger visit.
                </div>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1" style="font-size: 0.75rem; font-weight: 600;">
                20 Kolom Excel A s.d. T
            </span>
        </div>
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('risk-trigger.kka.update', $kka->id) }}" method="POST">
                @csrf

                {{-- Baris Otomatis F, G, H, I --}}
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">
                            <i class="bi bi-question-circle text-primary me-1"></i>Pertanyaan Audit (Kolom F - Rumus Otomatis)
                        </label>
                        <div class="p-2.5 bg-light rounded border text-dark fw-semibold" style="font-size: 0.9rem; line-height: 1.5;">
                            {{ $kka->pertanyaan_audit }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">
                            Kondisi Awal (Kolom G - dari Register Trigger)
                        </label>
                        <div class="p-2.5 bg-light rounded border text-secondary" style="font-size: 0.85rem; min-height: 60px;">
                            {{ $kka->kondisi_awal ?: '-' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">
                            Kriteria (Kolom H - dari Program Kerja)
                        </label>
                        <div class="p-2.5 bg-light rounded border text-secondary" style="font-size: 0.85rem; min-height: 60px;">
                            {{ $kka->kriteria ?: '-' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">
                            Prosedur Utama (Kolom I - dari Program Kerja)
                        </label>
                        <div class="p-2.5 bg-light rounded border text-secondary" style="font-size: 0.85rem; min-height: 60px;">
                            {{ $kka->prosedur_utama ?: '-' }}
                        </div>
                    </div>
                </div>

                <hr class="text-muted opacity-25 my-3">

                {{-- Baris Input Pengujian Profesional RA (J s.d. N, Q) --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            Ringkasan Bukti (Kolom J - Input Teks RA)
                        </label>
                        <textarea name="ringkasan_bukti" class="form-control" rows="3" 
                                  placeholder="Tuliskan daftar dan ringkasan bukti fisik/dokumen yang diperoleh di lapangan..."
                                  {{ ($isFinal && $isRa) ? 'disabled' : '' }}>{{ old('ringkasan_bukti', $kka->ringkasan_bukti) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            Hasil Analisis (Kolom K - Input Teks RA)
                        </label>
                        <textarea name="hasil_analisis" class="form-control" rows="3" 
                                  placeholder="Uraikan hasil analisis mendalam atas bukti pengujian..."
                                  {{ ($isFinal && $isRa) ? 'disabled' : '' }}>{{ old('hasil_analisis', $kka->hasil_analisis) }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-dark">
                            Trigger Terbukti? (Kolom L) <span class="text-danger">*</span>
                        </label>
                        <select name="trigger_terbukti" class="form-select" {{ ($isFinal && $isRa) ? 'disabled' : '' }}>
                            <option value="">-- Pilih Pembuktian --</option>
                            <option value="Ya" {{ old('trigger_terbukti', $kka->trigger_terbukti) == 'Ya' ? 'selected' : '' }}>Ya (Terbukti Ada Penyimpangan / Risiko)</option>
                            <option value="Sebagian" {{ old('trigger_terbukti', $kka->trigger_terbukti) == 'Sebagian' ? 'selected' : '' }}>Sebagian (Terbukti Sebagian Objek)</option>
                            <option value="Tidak" {{ old('trigger_terbukti', $kka->trigger_terbukti) == 'Tidak' ? 'selected' : '' }}>Tidak (Tidak Terbukti / Sesuai Ketentuan)</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-dark">
                            Perlu Perluasan? (Kolom M) <span class="text-danger">*</span>
                        </label>
                        <select name="perlu_perluasan" id="selectPerluPerluasan" class="form-select" onchange="toggleAlasanPerluasan()" {{ ($isFinal && $isRa) ? 'disabled' : '' }}>
                            <option value="Tidak" {{ old('perlu_perluasan', $kka->perlu_perluasan) == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                            <option value="Ya" {{ old('perlu_perluasan', $kka->perlu_perluasan) == 'Ya' ? 'selected' : '' }}>Ya (Perlu Perluasan Sampel / Audit Reguler)</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-dark">
                            Alasan Perluasan (Kolom N) <span id="labelWajibPerluasan" class="text-danger" style="display: none;">*</span>
                        </label>
                        <textarea name="alasan_perluasan" id="inputAlasanPerluasan" class="form-control" rows="2" 
                                  placeholder="Wajib diisi jika Perlu Perluasan = Ya..."
                                  {{ ($isFinal && $isRa) ? 'disabled' : '' }}>{{ old('alasan_perluasan', $kka->alasan_perluasan) }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-dark">
                            Simpulan Profesional RA (Kolom Q - Simpulan Akhir RA atas Trigger Tersebut)
                        </label>
                        <textarea name="simpulan_ra" class="form-control" rows="3" 
                                  placeholder="Tuliskan simpulan profesional akhir, rekomendasi mitigasi risiko, dan implikasi bagi unit kerja..."
                                  {{ ($isFinal && $isRa) ? 'disabled' : '' }}>{{ old('simpulan_ra', $kka->simpulan_ra) }}</textarea>
                    </div>

                    {{-- Review Section: Khusus Admin/Reviewer --}}
                    @if(!$isRa)
                    <div class="col-md-6 mt-3">
                        <div class="p-3 bg-light rounded border border-primary border-opacity-25">
                            <label class="form-label small fw-bold text-primary text-uppercase">
                                <i class="bi bi-shield-check me-1"></i>Status Review (Kolom T - Wewenang Khusus Admin/Reviewer)
                            </label>
                            <select name="status_review" class="form-select">
                                <option value="DRAFT / BELUM DIREVIEW" {{ old('status_review', $kka->status_review) == 'DRAFT / BELUM DIREVIEW' ? 'selected' : '' }}>DRAFT / BELUM DIREVIEW</option>
                                <option value="REVISI" {{ old('status_review', $kka->status_review) == 'REVISI' ? 'selected' : '' }}>REVISI (Kembalikan ke RA)</option>
                                <option value="FINAL" {{ old('status_review', $kka->status_review) == 'FINAL' ? 'selected' : '' }}>FINAL (Kunci & Selesai)</option>
                            </select>
                            <small class="text-muted d-block mt-1">
                                Jika diubah menjadi <strong>FINAL</strong>, KKA dan seluruh baris sampel area akan otomatis terkunci.
                            </small>
                        </div>
                    </div>
                    @else
                    <div class="col-md-6 mt-3">
                        <div class="p-3 bg-light rounded border">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Status Review (Kolom T - Wewenang Reviewer)
                            </label>
                            <div>
                                @if($kka->status_review === 'FINAL')
                                    <span class="badge-status badge-final"><i class="bi bi-lock-fill"></i> FINAL</span>
                                @elseif($kka->status_review === 'REVISI')
                                    <span class="badge-status badge-revisi"><i class="bi bi-arrow-repeat"></i> REVISI</span>
                                @else
                                    <span class="badge-status badge-draft"><i class="bi bi-pencil"></i> DRAFT</span>
                                @endif
                            </div>
                            <small class="text-muted d-block mt-1">
                                Status review ditetapkan dan dikunci secara eksklusif oleh Reviewer / Admin SKAI.
                            </small>
                        </div>
                    </div>
                    @endif
                </div>

                @if($canModify && !$isFinal || !$isRa)
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-save me-1"></i> Simpan Pertimbangan KKA Induk
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>

    {{-- BAGIAN 2: DELAPAN (8) KKA SPESIFIK AREA (MULTI-ROW PER VISIT ID) --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="section-title mb-0">
            <i class="bi bi-layers-fill text-primary me-2"></i>
            Bagian 2: Delapan (8) KKA Spesifik Area Pengujian (Multi-Row per Visit ID: {{ $kka->visit_id }})
        </h5>
        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1">
            Total Exception Terdeteksi: {{ $kka->exception_area ?? 0 }}
        </span>
    </div>

    {{-- Navigation Tabs Lintas 8 KKA Area --}}
    <div class="area-nav-tabs">
        <a href="?tab=KKA_Teller_Kas" class="area-nav-tab {{ $activeTab == 'KKA_Teller_Kas' ? 'active' : '' }}">
            <i class="bi bi-cash-stack"></i> 1. Teller Kas
            <span class="badge {{ ($areaCounts['KKA_Teller_Kas'] ?? 0) > 0 ? 'bg-primary' : 'bg-light text-secondary border' }} ms-1">
                {{ $areaCounts['KKA_Teller_Kas'] ?? 0 }}
            </span>
        </a>
        <a href="?tab=KKA_DPK_Layanan" class="area-nav-tab {{ $activeTab == 'KKA_DPK_Layanan' ? 'active' : '' }}">
            <i class="bi bi-person-check"></i> 2. DPK Layanan
            <span class="badge {{ ($areaCounts['KKA_DPK_Layanan'] ?? 0) > 0 ? 'bg-primary' : 'bg-light text-secondary border' }} ms-1">
                {{ $areaCounts['KKA_DPK_Layanan'] ?? 0 }}
            </span>
        </a>
        <a href="?tab=KKA_Kredit" class="area-nav-tab {{ $activeTab == 'KKA_Kredit' ? 'active' : '' }}">
            <i class="bi bi-bank2"></i> 3. Kredit
            <span class="badge {{ ($areaCounts['KKA_Kredit'] ?? 0) > 0 ? 'bg-primary' : 'bg-light text-secondary border' }} ms-1">
                {{ $areaCounts['KKA_Kredit'] ?? 0 }}
            </span>
        </a>
        <a href="?tab=KKA_Biaya_Beban" class="area-nav-tab {{ $activeTab == 'KKA_Biaya_Beban' ? 'active' : '' }}">
            <i class="bi bi-receipt-cutoff"></i> 4. Biaya Beban
            <span class="badge {{ ($areaCounts['KKA_Biaya_Beban'] ?? 0) > 0 ? 'bg-primary' : 'bg-light text-secondary border' }} ms-1">
                {{ $areaCounts['KKA_Biaya_Beban'] ?? 0 }}
            </span>
        </a>
        <a href="?tab=KKA_ATM" class="area-nav-tab {{ $activeTab == 'KKA_ATM' ? 'active' : '' }}">
            <i class="bi bi-credit-card-2-front"></i> 5. ATM & Kartu
            <span class="badge {{ ($areaCounts['KKA_ATM'] ?? 0) > 0 ? 'bg-primary' : 'bg-light text-secondary border' }} ms-1">
                {{ $areaCounts['KKA_ATM'] ?? 0 }}
            </span>
        </a>
        <a href="?tab=KKA_Pengaduan" class="area-nav-tab {{ $activeTab == 'KKA_Pengaduan' ? 'active' : '' }}">
            <i class="bi bi-chat-left-dots"></i> 6. Pengaduan
            <span class="badge {{ ($areaCounts['KKA_Pengaduan'] ?? 0) > 0 ? 'bg-primary' : 'bg-light text-secondary border' }} ms-1">
                {{ $areaCounts['KKA_Pengaduan'] ?? 0 }}
            </span>
        </a>
        <a href="?tab=KKA_Fisik" class="area-nav-tab {{ $activeTab == 'KKA_Fisik' ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> 7. Fisik & Aset
            <span class="badge {{ ($areaCounts['KKA_Fisik'] ?? 0) > 0 ? 'bg-primary' : 'bg-light text-secondary border' }} ms-1">
                {{ $areaCounts['KKA_Fisik'] ?? 0 }}
            </span>
        </a>
        <a href="?tab=KKA_Observasi" class="area-nav-tab {{ $activeTab == 'KKA_Observasi' ? 'active' : '' }}">
            <i class="bi bi-camera-video"></i> 8. Observasi
            <span class="badge {{ ($areaCounts['KKA_Observasi'] ?? 0) > 0 ? 'bg-primary' : 'bg-light text-secondary border' }} ms-1">
                {{ $areaCounts['KKA_Observasi'] ?? 0 }}
            </span>
        </a>
    </div>

    {{-- Render Selected KKA Area Partial --}}
    @switch($activeTab)
        @case('KKA_Teller_Kas')
            @include('risk_trigger.kka.areas.teller_kas')
            @break
        @case('KKA_DPK_Layanan')
            @include('risk_trigger.kka.areas.dpk_layanan')
            @break
        @case('KKA_Kredit')
            @include('risk_trigger.kka.areas.kredit')
            @break
        @case('KKA_Biaya_Beban')
            @include('risk_trigger.kka.areas.biaya_beban')
            @break
        @case('KKA_ATM')
            @include('risk_trigger.kka.areas.atm')
            @break
        @case('KKA_Pengaduan')
            @include('risk_trigger.kka.areas.pengaduan')
            @break
        @case('KKA_Fisik')
            @include('risk_trigger.kka.areas.fisik')
            @break
        @case('KKA_Observasi')
            @include('risk_trigger.kka.areas.observasi')
            @break
        @default
            @include('risk_trigger.kka.areas.teller_kas')
    @endswitch

<script>
    function toggleAlasanPerluasan() {
        const sel = document.getElementById('selectPerluPerluasan');
        const txt = document.getElementById('inputAlasanPerluasan');
        const req = document.getElementById('labelWajibPerluasan');
        if (sel && sel.value === 'Ya') {
            if (req) req.style.display = 'inline';
            if (txt) txt.setAttribute('required', 'required');
        } else {
            if (req) req.style.display = 'none';
            if (txt) txt.removeAttribute('required');
        }
    }
    document.addEventListener('DOMContentLoaded', toggleAlasanPerluasan);
</script>
@endsection
