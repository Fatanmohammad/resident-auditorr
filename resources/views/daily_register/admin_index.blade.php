@extends('layouts.app')
@section('title', 'Rekapitulasi Cabang - Daily Register')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div class="page-header-title">
        <h4 class="fw-bold mb-1" style="color: var(--primary-blue);">Dashboard Rekapitulasi Daily Register &amp; Kontrol Kapasitas</h4>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">Pantau agregasi risiko harian, kapasitas kerja auditor (420 menit), dan kepatuhan 5 KKA fisik mingguan di setiap KC.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-light text-dark border px-3 py-2" style="font-size: 0.8rem;">
            <i class="bi bi-clock-history me-1 text-primary"></i> Minggu: {{ \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY)->format('d M') }} s.d. {{ \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(4)->format('d M Y') }}
        </span>
        <div class="badge bg-purple px-3 py-2" style="font-size: 0.85rem; background-color: #6366f1; color: #fff;">
            <i class="bi bi-shield-lock-fill me-1"></i> AKSES: {{ strtoupper(auth()->user()->role) }}
        </div>
    </div>
</div>

<!-- 3 RINGKASAN METRIK KONTROL ADMIN -->
@php
    $totalCabang = count($rekapCabang);
    $totalOverCapacity = collect($rekapCabang)->filter(fn($c) => ($c['kapasitas']['sisa_kapasitas'] ?? 0) < 0)->count();
    $totalCompletedKka = collect($rekapCabang)->sum(function($c) {
        return collect($c['kapasitas']['schedules'] ?? [])->where('status_minggu_ini', 'SELESAI MINGGU INI')->count();
    });
    $totalPhysicalTarget = $totalCabang * 5;
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3" style="border-radius: 10px; background: #fff; border-left: 4px solid #3b82f6 !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 600;">KC Penempatan RA Terpantau</div>
                    <h3 class="fw-bold mb-0 mt-1" style="color: #1e3a8a;">{{ $totalCabang }} <small class="fs-6 fw-normal text-muted">Kantor Cabang</small></h3>
                </div>
                <span style="width: 44px; height: 44px; border-radius: 10px; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                    <i class="bi bi-buildings"></i>
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3" style="border-radius: 10px; background: #fff; border-left: 4px solid {{ $totalOverCapacity > 0 ? '#ef4444' : '#10b981' }} !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 600;">Status Kapasitas Harian Auditor</div>
                    <h3 class="fw-bold mb-0 mt-1" style="color: {{ $totalOverCapacity > 0 ? '#dc2626' : '#16a34a' }};">
                        @if($totalOverCapacity > 0)
                            {{ $totalOverCapacity }} KC Over Capacity
                        @else
                            Seluruh KC Aman
                        @endif
                    </h3>
                </div>
                <span style="width: 44px; height: 44px; border-radius: 10px; background: {{ $totalOverCapacity > 0 ? '#fef2f2' : '#f0fdf4' }}; color: {{ $totalOverCapacity > 0 ? '#ef4444' : '#16a34a' }}; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                    <i class="bi bi-speedometer2"></i>
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3" style="border-radius: 10px; background: #fff; border-left: 4px solid #8b5cf6 !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 600;">Progres 5 KKA Fisik Minggu Ini</div>
                    <h3 class="fw-bold mb-0 mt-1" style="color: #6d28d9;">{{ $totalCompletedKka }} / {{ $totalPhysicalTarget }} <small class="fs-6 fw-normal text-muted">KKA Selesai</small></h3>
                </div>
                <span style="width: 44px; height: 44px; border-radius: 10px; background: #f5f3ff; color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                    <i class="bi bi-clipboard2-check"></i>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color) !important;">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--border-color);">
        <h6 class="mb-0 fw-bold" style="color: var(--primary-blue);">
            <i class="bi bi-table me-2"></i> Rekapitulasi Cabang, Kapasitas Waktu Kerja &amp; Kepatuhan KKA Fisik
        </h6>
        <span class="badge bg-light text-dark border">Total: {{ count($rekapCabang) }} Cabang</span>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 6%;">Kode</th>
                    <th style="width: 22%;">Kantor Cabang / Wilayah</th>
                    <th class="text-center" style="width: 18%;">Kapasitas Harian (Menit)</th>
                    <th class="text-center" style="width: 14%;">Jadwal KKA Fisik</th>
                    <th class="text-center" style="width: 12%;">Temuan Harian</th>
                    <th class="text-center" style="width: 18%;">Tindakan Reviewer</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekapCabang as $cabang)
                @php
                    $cap = $cabang['kapasitas'] ?? [];
                    $sisaCap = $cap['sisa_kapasitas'] ?? 420;
                    $schedList = $cap['schedules'] ?? [];
                    $completedCount = collect($schedList)->where('status_minggu_ini', 'SELESAI MINGGU INI')->count();
                @endphp
                <tr>
                    <td style="font-size:0.78rem; color:var(--text-muted); font-weight:600;">
                        {{ $cabang['kode_unit'] }}
                    </td>
                    <td>
                        <strong>{{ $cabang['nama_cabang'] }}</strong>
                    </td>
                    
                    <!-- KAPASITAS HARIAN -->
                    <td class="text-center">
                        @if($sisaCap < 0)
                            <span class="badge bg-danger shadow-sm mb-1" style="font-size: 0.72rem;">
                                <i class="bi bi-exclamation-octagon me-1"></i> OVER CAPACITY
                            </span>
                            <div style="font-size: 0.7rem; color: #dc2626; font-weight: 600;">
                                Lebih Beban: {{ abs($sisaCap) }} Menit
                            </div>
                        @else
                            <span class="badge bg-success shadow-sm mb-1" style="font-size: 0.72rem;">
                                <i class="bi bi-check-circle me-1"></i> DALAM KAPASITAS
                            </span>
                            <div style="font-size: 0.7rem; color: #16a34a; font-weight: 600;">
                                Sisa Waktu: +{{ $sisaCap }} Menit
                            </div>
                        @endif
                        <small class="text-muted d-block" style="font-size: 0.68rem;">
                            Tersedia: {{ $cap['menit_tersedia_sampling'] ?? 420 }}m | Beban: {{ $cap['estimasi_menit_sampel'] ?? 0 }}m
                        </small>
                    </td>

                    <!-- JADWAL KKA FISIK MINGGUAN -->
                    <td class="text-center">
                        <span class="badge {{ $completedCount == 5 ? 'bg-success' : ($completedCount > 0 ? 'bg-primary' : 'bg-secondary') }} shadow-sm">
                            <i class="bi bi-calendar-check me-1"></i> {{ $completedCount }} / 5 Selesai
                        </span>
                        <div style="font-size: 0.68rem; color: var(--text-muted); margin-top: 2px;">
                            Pemeriksaan Mingguan
                        </div>
                    </td>

                    <!-- TEMUAN HARIAN -->
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            @if($cabang['total_moderate_all'] > 0)
                                <span class="badge badge-warning shadow-sm" title="Moderate Risk">
                                    M: {{ $cabang['total_moderate_all'] }}
                                </span>
                            @endif
                            @if($cabang['total_high_all'] > 0)
                                <span class="badge badge-danger shadow-sm" title="High Risk">
                                    H: {{ $cabang['total_high_all'] }}
                                </span>
                            @endif
                            @if($cabang['total_moderate_all'] == 0 && $cabang['total_high_all'] == 0)
                                <span class="text-muted" style="font-size: 0.8rem;">Nihil Temuan</span>
                            @endif
                        </div>
                        <small class="text-muted d-block" style="font-size: 0.68rem; margin-top: 2px;">
                            Total: {{ $cabang['total_risiko_all'] }} Temuan
                        </small>
                    </td>
                    
                    <!-- TINDAKAN -->
                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('daily-register.kka.index', ['cabang' => $cabang['id'], 'kode_unit' => $cabang['kode_unit']]) }}" class="btn btn-outline-primary btn-sm shadow-sm" title="Buka KKA Transaksi">
                                <i class="bi bi-eye me-1"></i> KKA Transaksi
                            </a>
                            <a href="{{ route('daily-register.physical-kka.index', ['id' => $cabang['physical_kka_id']]) }}" class="btn btn-outline-success btn-sm shadow-sm" title="Buka KKA Fisik">
                                <i class="bi bi-clipboard2-check me-1"></i> Fisik
                            </a>
                            <button type="button" class="btn btn-outline-info btn-sm shadow-sm" onclick="openAdminCiModal('{{ $cabang['id'] }}', '{{ $cabang['kode_unit'] }}', '{{ addslashes($cabang['nama_cabang']) }}')" title="Pratinjau KKA Control & Integrasi 11 Area">
                                <i class="bi bi-diagram-3 me-1"></i> Control &amp; Integrasi
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state text-center py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-3 text-muted"></i>
                            <h6 class="fw-bold">Belum Ada Data Wilayah</h6>
                            <p class="mb-0 fs-7 text-muted">Sistem belum merekam data cabang untuk ditampilkan.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL PRATINJAU KKA CONTROL & INTEGRASI (ADMIN) -->
<div class="modal fade" id="modalAdminCi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); color: #fff;">
                <div>
                    <h6 class="modal-title fw-bold mb-0 text-white" id="modalAdminCiTitle">
                        <i class="bi bi-diagram-3 me-2"></i> KKA Control &amp; Integrasi 11 Area (SOP 04.2)
                    </h6>
                    <small style="opacity: 0.85; font-size: 0.75rem;" id="modalAdminCiSubtitle">
                        Kantor Cabang: Memuat...
                    </small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background: #f8fafc;">
                <!-- 5 METRIC CARDS HEADER -->
                <div class="row g-2 mb-3">
                    <div class="col">
                        <div class="card p-2 border-0 shadow-sm text-center" style="border-radius: 8px;">
                            <small class="text-muted text-uppercase" style="font-size: 0.68rem; font-weight: 600;">Lingkup</small>
                            <h5 class="fw-bold mb-0 text-primary" id="mCiLingkup">11</h5>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card p-2 border-0 shadow-sm text-center" style="border-radius: 8px;">
                            <small class="text-muted text-uppercase" style="font-size: 0.68rem; font-weight: 600;">Tersedia</small>
                            <h5 class="fw-bold mb-0 text-dark" id="mCiTersedia">0</h5>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card p-2 border-0 shadow-sm text-center" style="border-radius: 8px;">
                            <small class="text-muted text-uppercase" style="font-size: 0.68rem; font-weight: 600;">Selesai</small>
                            <h5 class="fw-bold mb-0 text-success" id="mCiSelesai">0</h5>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card p-2 border-0 shadow-sm text-center" style="border-radius: 8px;">
                            <small class="text-muted text-uppercase" style="font-size: 0.68rem; font-weight: 600;">Status Tahap 4</small>
                            <div class="mt-1"><span id="mCiTahap4Badge" class="badge bg-warning text-dark px-2 py-1" style="font-size: 0.7rem;">KKA BELUM SELESAI</span></div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card p-2 border-0 shadow-sm text-center" style="border-radius: 8px;">
                            <small class="text-muted text-uppercase" style="font-size: 0.68rem; font-weight: 600;">Kesiapan Tahap 5</small>
                            <div class="mt-1"><span id="mCiTahap5Badge" class="badge bg-danger text-white px-2 py-1" style="font-size: 0.7rem;">BELUM SELESAI</span></div>
                        </div>
                    </div>
                </div>

                <!-- TABEL 11 BARIS -->
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px; overflow: hidden;">
                    <div class="table-responsive" style="max-height: 380px; overflow-x: hidden;">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.76rem; table-layout: fixed; width: 100%;">
                            <thead class="table-light text-center" style="font-size: 0.68rem; text-transform: uppercase;">
                                <tr>
                                    <th style="width: 4%;">No</th>
                                    <th style="width: 19%; text-align: left;">Area Review</th>
                                    <th style="width: 13%; text-align: left;">Sheet KKA</th>
                                    <th style="width: 10%;">Status KKA</th>
                                    <th style="width: 6%;">Sampel</th>
                                    <th style="width: 6%;">Diuji</th>
                                    <th style="width: 6%;">Exception</th>
                                    <th style="width: 7%;">Tinggi</th>
                                    <th style="width: 6%;">Pending</th>
                                    <th style="width: 13%; text-align: left;">Kontrol Khas</th>
                                    <th style="width: 10%;">Tindak Lanjut</th>
                                </tr>
                            </thead>
                            <tbody id="mCiTableBody">
                                <tr>
                                    <td colspan="11" class="text-center py-4 text-muted">
                                        <div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data 11 Area KKA...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAHAP 5 PANEL -->
                <div class="card p-3 border-0 shadow-sm" style="border-radius: 10px; background: #fff;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <span class="fw-bold" style="font-size: 0.8rem; color: #1e3a8a;">
                            <i class="bi bi-link-45deg me-1"></i> Integrasi Tahap 5: Register Klarifikasi &amp; Temuan
                        </span>
                        <span id="mCiStatusTahap5FinalBadge" class="badge bg-warning text-dark px-2 py-1" style="font-size: 0.72rem;">
                            SELESAIKAN KLARIFIKASI / TEMUAN
                        </span>
                    </div>
                    <div class="row g-2 text-center" style="font-size: 0.75rem;">
                        <div class="col-3">
                            <div class="p-2 border rounded bg-light">
                                <div class="text-muted">Perlu Klarifikasi</div>
                                <strong class="text-danger fs-6" id="mCiPerluKlarifikasi">0</strong>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 border rounded bg-light">
                                <div class="text-muted">Klarifikasi Selesai</div>
                                <strong class="text-success fs-6" id="mCiKlarifikasiSelesai">0</strong>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 border rounded bg-light">
                                <div class="text-muted">Eligible Temuan</div>
                                <strong class="text-warning fs-6" id="mCiEligibleTemuan">0</strong>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 border rounded bg-light">
                                <div class="text-muted">Temuan Valid</div>
                                <strong class="text-primary fs-6" id="mCiTemuanValid">0</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 px-4 bg-white d-flex justify-content-between align-items-center">
                <small class="text-muted" style="font-size: 0.72rem;">
                    Standar Formulir Rekonsiliasi: SOP 04.2 (WP_SOP_04_2_DAILY_REVIEW_v1.xlsx)
                </small>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-warning btn-sm" id="btnAdminRerunSampling" onclick="adminTriggerRerunSampling()" title="Jalankan ulang sampling SOP 02 untuk cabang ini">
                        <i class="bi bi-arrow-repeat me-1"></i> Re-Run Sampling SOP 02
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <a id="btnAdminCiFullPage" href="#" class="btn btn-primary btn-sm">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka Halaman KKA Lengkap
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let currentAdminKodeUnit = null;
    let currentAdminCabangId = null;
    let currentAdminNamaCabang = null;

    function openAdminCiModal(cabangId, kodeUnit, namaCabang) {
        currentAdminKodeUnit = kodeUnit;
        currentAdminCabangId = cabangId;
        currentAdminNamaCabang = namaCabang;

        document.getElementById('modalAdminCiSubtitle').innerText = `Kantor Cabang: ${kodeUnit} - ${namaCabang}`;
        document.getElementById('btnAdminCiFullPage').href = `{{ url('/daily-register/kka') }}?cabang_id=${cabangId}&kode_unit=${kodeUnit}&tab=control_integrasi`;

        const modalElem = document.getElementById('modalAdminCi');
        const modal = new bootstrap.Modal(modalElem);
        modal.show();

        loadAdminCiData(cabangId, kodeUnit);
    }

    function loadAdminCiData(cabangId, kodeUnit) {
        const tbody = document.getElementById('mCiTableBody');
        tbody.innerHTML = `<tr><td colspan="11" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data 11 Area KKA...</td></tr>`;

        axios.get(`{{ url('/daily-register/control-integrasi') }}?cabang_id=${cabangId}&kode_unit=${kodeUnit}`)
            .then(res => {
                if (res.data.status === 'success') {
                    const data = res.data.data;
                    const hs = data.header_summary;

                    document.getElementById('mCiLingkup').innerText = hs.kka_dalam_lingkup;
                    document.getElementById('mCiTersedia').innerText = `${hs.kka_tersedia} / 11`;
                    document.getElementById('mCiSelesai').innerText = `${hs.kka_selesai} / 11`;

                    const t4 = document.getElementById('mCiTahap4Badge');
                    t4.innerText = hs.status_tahap_4;
                    t4.className = hs.status_tahap_4 === 'SIAP TAHAP 5' ? 'badge bg-success text-white px-2 py-1' : 'badge bg-warning text-dark px-2 py-1';

                    const t5 = document.getElementById('mCiTahap5Badge');
                    t5.innerText = hs.kesiapan_tahap_5;
                    t5.className = hs.kesiapan_tahap_5 === 'SIAP TAHAP 5' ? 'badge bg-success text-white px-2 py-1' : (hs.kesiapan_tahap_5.includes('TIDAK LENGKAP') ? 'badge bg-danger text-white px-2 py-1' : 'badge bg-warning text-dark px-2 py-1');

                    // Tabel
                    tbody.innerHTML = '';
                    (data.rows || []).forEach(row => {
                        let badgeClass = 'badge bg-secondary';
                        if (row.status_kka === 'SELESAI') badgeClass = 'badge bg-success';
                        else if (row.status_kka === 'DALAM PENGUJIAN') badgeClass = 'badge bg-warning text-dark';

                        let actionBadge = row.tindak_lanjut === 'LANJUT TAHAP 5' ? 'badge bg-primary' : 'badge bg-light text-dark border';

                        const tr = `<tr>
                            <td class="text-center text-muted">${row.no}</td>
                            <td class="fw-semibold text-start text-truncate" style="font-size:0.76rem;" title="${row.area_review}">${row.area_review}</td>
                            <td class="text-start text-truncate" title="${row.nama_sheet}"><code style="font-size:0.7rem;">${row.nama_sheet}</code></td>
                            <td class="text-center"><span class="${badgeClass}" style="font-size:0.68rem;">${row.status_kka}</span></td>
                            <td class="text-end fw-semibold" style="font-size:0.76rem;">${row.sampel_terpilih}</td>
                            <td class="text-end" style="font-size:0.76rem;">${row.sampel_diuji}</td>
                            <td class="text-end fw-bold ${row.exception > 0 ? 'text-danger' : 'text-muted'}" style="font-size:0.76rem;">${row.exception}</td>
                            <td class="text-end fw-bold ${row.tinggi_kritis > 0 ? 'text-danger' : 'text-muted'}" style="font-size:0.76rem;">${row.tinggi_kritis}</td>
                            <td class="text-end ${row.klarifikasi_pending > 0 ? 'text-warning fw-bold' : 'text-muted'}" style="font-size:0.76rem;">${row.klarifikasi_pending}</td>
                            <td class="text-start text-truncate" style="font-size:0.7rem;" title="${(row.kontrol_khas || '').replace(/"/g, '&quot;')}">${row.kontrol_khas}</td>
                            <td class="text-center"><span class="${actionBadge}" style="font-size:0.68rem;">${row.tindak_lanjut}</span></td>
                        </tr>`;
                        tbody.innerHTML += tr;
                    });

                    // Tahap 5
                    const int5 = data.integrasi_tahap_5;
                    document.getElementById('mCiPerluKlarifikasi').innerText = int5.perlu_klarifikasi;
                    document.getElementById('mCiKlarifikasiSelesai').innerText = int5.klarifikasi_selesai;
                    document.getElementById('mCiEligibleTemuan').innerText = int5.eligible_temuan;
                    document.getElementById('mCiTemuanValid').innerText = int5.temuan_valid;

                    const bT5 = document.getElementById('mCiStatusTahap5FinalBadge');
                    bT5.innerText = int5.status_tahap_5;
                    bT5.className = int5.status_tahap_5 === 'SIAP LAPORAN' ? 'badge bg-success text-white px-2 py-1' : 'badge bg-warning text-dark px-2 py-1';
                }
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="11" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Gagal memuat data.</td></tr>`;
            });
    }

    function adminTriggerRerunSampling() {
        if (!currentAdminKodeUnit) return;

        Swal.fire({
            title: `Jalankan Ulang Sampling?`,
            html: `<div class="text-start" style="font-size: 0.85rem; color: #475569;">
                    Jalankan ulang sampling SOP 02 untuk Cabang <strong>${currentAdminNamaCabang} (${currentAdminKodeUnit})</strong>?<br><br>
                    <ul class="ps-3 mb-0">
                        <li>Kapasitas KKA dibatasi maksimal <strong>500 sampel per area</strong></li>
                        <li>High Risk diprioritaskan nominal terbesar</li>
                        <li>Data yang sudah diuji RA aman (tidak terhapus)</li>
                        <li>Data non-sampel berlebih dibersihkan</li>
                    </ul>
                   </div>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1e3a8a',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Jalankan Sampling',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((res) => {
            if (res.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Sampling...',
                    html: '<div class="spinner-border text-primary my-2"></div><div class="text-muted" style="font-size:0.85rem;">Menyeimbangkan sampel sesuai SOP 02...</div>',
                    showConfirmButton: false,
                    allowOutsideClick: false
                });

                axios.post('{{ route("daily-register.rerun-sampling") }}', {
                    kode_unit: currentAdminKodeUnit,
                    _token: '{{ csrf_token() }}'
                }).then(resp => {
                    if (resp.data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sampling Selesai!',
                            text: resp.data.message,
                            confirmButtonColor: '#1e3a8a'
                        }).then(() => {
                            loadAdminCiData(currentAdminCabangId, currentAdminKodeUnit);
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'Informasi',
                            text: resp.data.message
                        });
                    }
                }).catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: err.response?.data?.message || err.message || 'Terjadi kesalahan sistem.'
                    });
                });
            }
        });
    }
</script>
@endsection