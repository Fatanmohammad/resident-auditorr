@extends('layouts.app')

@section('title', 'Temuan Indikasi Mencurigakan Cabang (18 Indikator) — Bank Sulteng')

@section('content')
<div class="container-fluid px-4 py-3" style="max-width: 1720px; margin: 0 auto;">

    <!-- Header Page -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge px-3 py-2 fs-7 fw-bold" style="background-color: #e0e7ff; color: #3730a3; border-radius: 6px;">
                    <i class="bi bi-shield-exclamation me-1"></i> MONITORING RED FLAGS CABANG
                </span>
                <h3 class="mb-0 fw-bold" style="color: #0f2e5a; letter-spacing: -0.3px;">
                    TEMUAN INDIKASI MENCURIGAKAN – RESIDENT AUDITOR
                </h3>
            </div>
            <p class="text-secondary mb-0" style="font-size: 0.92rem;">
                Matriks Pengawasan 18 Indikator Potensi Fraud per Kantor Cabang / KCP / KCPLK Berdasarkan SOP Anti Fraud &amp; BPP SK 60/2022
            </p>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <form action="{{ route('pemeriksaan_awal.indikasi_mencurigakan.sync') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary rounded-pill px-3 shadow-sm fw-semibold" style="font-size: 0.88rem;" title="Kalkulasi otomatis dari data transaksi CBS, Daily Register & Temuan KKA">
                    <i class="bi bi-arrow-repeat me-1"></i> ⚡ Tarik Otomatis dari CBS &amp; KKA
                </button>
            </form>
            <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm fw-bold" style="font-size: 0.88rem; background: #1e3a8a; border-color: #1e3a8a;" onclick="openModalTambah()">
                <i class="bi bi-plus-circle me-1"></i> Input Data Cabang Baru
            </button>
            <a href="{{ route('pemeriksaan_awal.triage_checklist') }}" class="btn btn-outline-danger rounded-pill px-3 shadow-sm fw-semibold" style="font-size: 0.88rem;">
                <i class="bi bi-ui-checks-grid me-1"></i> Fraud Triage Checklist
            </a>
            <button type="button" class="btn btn-outline-dark rounded-pill px-3 shadow-sm fw-semibold" style="font-size: 0.88rem;" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Cetak / PDF
            </button>
        </div>
    </div>

    @include('pemeriksaan_awal.partials.nav_tabs')

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4 p-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
            <div>
                <h6 class="mb-0 fw-bold text-success">Pemberitahuan Sukses</h6>
                <div style="font-size: 0.92rem;">{{ session('success') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- 4 Kartu Ringkasan Indikator Mencurigakan -->
    @php
        $totalCabang = $indikasis->count();
        $totalAnomali = 0;
        $cabangBermasalah = 0;
        foreach($indikasis as $ind) {
            $sum = $ind->total_temuan;
            $totalAnomali += $sum;
            if ($sum > 0) $cabangBermasalah++;
        }
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-primary">
                <span class="text-muted small fw-bold text-uppercase">Total Cabang Dimonitor</span>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <h2 class="fw-bold mb-0 text-dark">{{ $totalCabang }}</h2>
                    <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill small">Unit Kerja</span>
                </div>
                <small class="text-muted mt-1">KCU, KC, KCP, KCPLK Bank Sulteng</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-danger">
                <span class="text-muted small fw-bold text-uppercase">Total Kejadian Anomali Red Flag</span>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <h2 class="fw-bold mb-0 text-danger">{{ $totalAnomali }}</h2>
                    <span class="badge bg-danger-subtle text-danger px-2.5 py-1 rounded-pill small">Jumlah / Bulan</span>
                </div>
                <small class="text-danger fw-medium mt-1">Tersebar di 18 Parameter Pengujian</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-warning">
                <span class="text-muted small fw-bold text-uppercase">Cabang Terdapat Indikasi</span>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <h2 class="fw-bold mb-0 text-warning-emphasis">{{ $cabangBermasalah }}</h2>
                    <span class="badge bg-warning-subtle text-dark px-2.5 py-1 rounded-pill small">{{ $totalCabang > 0 ? round(($cabangBermasalah/$totalCabang)*100) : 0 }}% Terindikasi</span>
                </div>
                <small class="text-muted mt-1">Memerlukan penelusuran Fact-Finding RA</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-success">
                <span class="text-muted small fw-bold text-uppercase">Cabang Tanpa Anomali (Nol Kasus)</span>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <h2 class="fw-bold mb-0 text-success">{{ $totalCabang - $cabangBermasalah }}</h2>
                    <span class="badge bg-success-subtle text-success px-2.5 py-1 rounded-pill small">Tertib SOP</span>
                </div>
                <small class="text-success mt-1">Seluruh indikator bernilai 0</small>
            </div>
        </div>
    </div>

    <!-- TABEL 1: MATRIKS 18 INDIKATOR TEMUAN MENCURIGAKAN PER CABANG -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold" style="color: #0f2e5a;">
                    <i class="bi bi-grid-3x3-gap-fill me-2 text-primary"></i>Matriks Temuan Indikasi Mencurigakan (18 Indikator)
                </h5>
                <div class="text-muted small">Rekapitulasi frekuensi kejadian per bulan berdasarkan pengujian log OLIBS, bloter teller, dan berita acara kas.</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill small">
                    Periode: <strong>{{ date('F Y') }}</strong>
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                <table class="table table-bordered table-hover align-middle mb-0 text-center" style="font-size: 0.76rem; border-color: #cbd5e1;">
                    <thead style="background: #1e3a8a; color: #ffffff; position: sticky; top: 0; z-index: 10;">
                        <!-- Baris 1 Grup Header -->
                        <tr>
                            <th rowspan="2" style="width: 35px; vertical-align: middle; background: #0f2e5a;">No</th>
                            <th rowspan="2" style="min-width: 170px; text-align: left; vertical-align: middle; background: #0f2e5a;">Nama Cabang / KCP / KCPLK</th>
                            <th colspan="6" style="background: #1e40af; border-left: 2px solid #ffffff;">KAS &amp; TELLER</th>
                            <th colspan="6" style="background: #0369a1; border-left: 2px solid #ffffff;">REKENING &amp; SISTEM</th>
                            <th colspan="2" style="background: #0284c7; border-left: 2px solid #ffffff;">ATM &amp; APMK</th>
                            <th colspan="2" style="background: #0d9488; border-left: 2px solid #ffffff;">KREDIT</th>
                            <th style="background: #b45309; border-left: 2px solid #ffffff;">SDM</th>
                            <th style="background: #be123c; border-left: 2px solid #ffffff;">PENGADUAN</th>
                            <th rowspan="2" style="min-width: 60px; vertical-align: middle; background: #334155;">Total</th>
                            <th rowspan="2" style="min-width: 180px; text-align: left; vertical-align: middle; background: #0f2e5a;">Catatan Umum / Temuan Lain</th>
                            <th rowspan="2" style="min-width: 90px; vertical-align: middle; background: #0f2e5a;">Aksi</th>
                        </tr>
                        <!-- Baris 2 Rincian Sub-Kolom 18 Indikator -->
                        <tr style="font-size: 0.68rem; line-height: 1.25;">
                            <!-- Kas & Teller (6) -->
                            <th style="width: 80px; background: #1e3a8a;">Reversal Teller</th>
                            <th style="width: 80px; background: #1e3a8a;">Selisih Kas Teller</th>
                            <th style="width: 80px; background: #1e3a8a;">Selisih Khasanah</th>
                            <th style="width: 80px; background: #1e3a8a;">Kas Kecil &gt;Limit</th>
                            <th style="width: 80px; background: #1e3a8a;">Kas Kecil &gt;5 Hari</th>
                            <th style="width: 80px; background: #1e3a8a;">Dual Control Kunci</th>

                            <!-- Rekening & Sistem (6) -->
                            <th style="width: 80px; background: #0284c7;">Rekening Pasif</th>
                            <th style="width: 80px; background: #0284c7;">Rekening Titipan</th>
                            <th style="width: 85px; background: #0284c7;">Trx Pegawai Sendiri</th>
                            <th style="width: 80px; background: #0284c7;">Luar Jam Operasional</th>
                            <th style="width: 85px; background: #0284c7;">Setoran Non-Tunai</th>
                            <th style="width: 80px; background: #0284c7;">Sharing User ID</th>

                            <!-- ATM & APMK (2) -->
                            <th style="width: 80px; background: #0284c7;">Skimming ATM</th>
                            <th style="width: 80px; background: #0284c7;">Selisih Opname CRM</th>

                            <!-- Kredit (2) -->
                            <th style="width: 85px; background: #0f766e;">Restrukturisasi Berulang</th>
                            <th style="width: 85px; background: #0f766e;">Pencairan Tanpa Dokumen</th>

                            <!-- SDM (1) -->
                            <th style="width: 85px; background: #b45309;">Cuti Wajib Belum Diambil</th>

                            <!-- Pengaduan (1) -->
                            <th style="width: 85px; background: #be123c;">Lonjakan Lewat SLA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($indikasis as $index => $item)
                            @php
                                $total = $item->total_temuan;
                                $hasAnomaly = ($total > 0);
                            @endphp
                            <tr class="{{ $hasAnomaly ? 'table-light' : '' }}">
                                <td class="fw-bold text-muted">{{ $index + 1 }}</td>
                                <td class="text-start fw-bold text-dark text-nowrap">
                                    {{ $item->nama_cabang }}
                                </td>

                                <!-- Kas & Teller (6) -->
                                <td>{!! $item->reversal_teller > 0 ? '<span class="badge bg-danger rounded-pill px-2 py-1">'.$item->reversal_teller.'</span>' : '<span class="text-muted">0</span>' !!}</td>
                                <td>{!! $item->selisih_kas_teller > 0 ? '<span class="badge bg-danger rounded-pill px-2 py-1">'.$item->selisih_kas_teller.'</span>' : '<span class="text-muted">0</span>' !!}</td>
                                <td>{!! $item->selisih_kas_khasanah > 0 ? '<span class="badge bg-danger rounded-pill px-2 py-1">'.$item->selisih_kas_khasanah.'</span>' : '<span class="text-muted">0</span>' !!}</td>
                                <td>{!! $item->kas_kecil_melebihi_limit > 0 ? '<span class="badge bg-warning text-dark rounded-pill px-2 py-1">'.$item->kas_kecil_melebihi_limit.'</span>' : '<span class="text-muted">0</span>' !!}</td>
                                <td>{!! $item->kas_kecil_belum_selesai > 0 ? '<span class="badge bg-warning text-dark rounded-pill px-2 py-1">'.$item->kas_kecil_belum_selesai.'</span>' : '<span class="text-muted">0</span>' !!}</td>
                                <td>{!! $item->dual_control_kunci_dilanggar > 0 ? '<span class="badge bg-danger rounded-pill px-2 py-1">'.$item->dual_control_kunci_dilanggar.'</span>' : '<span class="text-muted">0</span>' !!}</td>

                                <!-- Rekening & Sistem (6) -->
                                <td>{!! $item->trx_rekening_pasif > 0 ? '<span class="badge bg-danger rounded-pill px-2 py-1">'.$item->trx_rekening_pasif.'</span>' : '<span class="text-muted">0</span>' !!}</td>
                                <td>{!! $item->trx_rekening_titipan > 0 ? '<span class="badge bg-warning text-dark rounded-pill px-2 py-1">'.$item->trx_rekening_titipan.'</span>' : '<span class="text-muted">0</span>' !!}</td>
                                <td>{!! $item->trx_tidak_wajar_pegawai > 0 ? '<span class="badge bg-danger rounded-pill px-2 py-1">'.$item->trx_tidak_wajar_pegawai.'</span>' : '<span class="text-muted">0</span>' !!}</td>
                                <td>{!! $item->trx_luar_jam_operasional > 0 ? '<span class="badge bg-warning text-dark rounded-pill px-2 py-1">'.$item->trx_luar_jam_operasional.'</span>' : '<span class="text-muted">0</span>' !!}</td>
                                <td>{!! $item->setoran_non_tunai_pegawai > 0 ? '<span class="badge bg-danger rounded-pill px-2 py-1">'.$item->setoran_non_tunai_pegawai.'</span>' : '<span class="text-muted">0</span>' !!}</td>
                                <td>{!! $item->sharing_user_id > 0 ? '<span class="badge bg-danger rounded-pill px-2 py-1">'.$item->sharing_user_id.'</span>' : '<span class="text-muted">0</span>' !!}</td>

                                <!-- ATM & APMK (2) -->
                                <td>{!! $item->skimming_atm > 0 ? '<span class="badge bg-danger rounded-pill px-2 py-1">'.$item->skimming_atm.'</span>' : '<span class="text-muted">0</span>' !!}</td>
                                <td>{!! $item->selisih_kas_crm_atm > 0 ? '<span class="badge bg-warning text-dark rounded-pill px-2 py-1">'.$item->selisih_kas_crm_atm.'</span>' : '<span class="text-muted">0</span>' !!}</td>

                                <!-- Kredit (2) -->
                                <td>{!! $item->restrukturisasi_berulang > 0 ? '<span class="badge bg-warning text-dark rounded-pill px-2 py-1">'.$item->restrukturisasi_berulang.'</span>' : '<span class="text-muted">0</span>' !!}</td>
                                <td>{!! $item->pencairan_tanpa_dokumen > 0 ? '<span class="badge bg-danger rounded-pill px-2 py-1">'.$item->pencairan_tanpa_dokumen.'</span>' : '<span class="text-muted">0</span>' !!}</td>

                                <!-- SDM (1) -->
                                <td>{!! $item->cuti_wajib_belum_diambil > 0 ? '<span class="badge bg-warning text-dark rounded-pill px-2 py-1">'.$item->cuti_wajib_belum_diambil.'</span>' : '<span class="text-muted">0</span>' !!}</td>

                                <!-- Pengaduan (1) -->
                                <td>{!! $item->lonjakan_pengaduan_sla > 0 ? '<span class="badge bg-danger rounded-pill px-2 py-1">'.$item->lonjakan_pengaduan_sla.'</span>' : '<span class="text-muted">0</span>' !!}</td>

                                <!-- Total Temuan -->
                                <td class="fw-bold font-monospace {{ $total > 0 ? 'text-danger bg-danger-subtle' : 'text-muted' }}">
                                    {{ $total }}
                                </td>

                                <!-- Catatan Umum -->
                                <td class="text-start small text-secondary" style="max-width: 240px; font-size: 0.72rem; line-height: 1.3;">
                                    {{ $item->catatan_umum ?: '-' }}
                                </td>

                                <!-- Tombol Aksi -->
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary p-1" title="Edit Baris Data" onclick='openModalEdit(@json($item))'>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form action="{{ route('pemeriksaan_awal.indikasi_mencurigakan.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus baris data temuan indikasi cabang {{ $item->nama_cabang }}?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="23" class="py-4 text-muted text-center">
                                    <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                    Belum ada data temuan indikasi mencurigakan per cabang. Silakan klik tombol "Input Data Cabang Baru".
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TABEL 2: DAFTAR RUJUKAN PENJELASAN INDIKATOR, SATUAN HITUNG & SOP/BPP -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold" style="color: #0f2e5a;">
                <i class="bi bi-book-half me-2 text-primary"></i>Panduan Definisi 18 Indikator, Satuan Hitung &amp; Rujukan Regulasi SOP/BPP
            </h5>
            <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill small">Pedoman Resmi SK 60/2022</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.83rem;">
                    <thead class="table-light text-uppercase" style="font-size: 0.76rem; letter-spacing: 0.5px;">
                        <tr>
                            <th style="width: 22%;">Kolom / Indikator</th>
                            <th style="width: 14%;">Grup</th>
                            <th style="width: 28%;">Penjelasan Singkat</th>
                            <th style="width: 22%;">Satuan Hitung &amp; Ambang Batas Wajar</th>
                            <th style="width: 14%;">Rujukan SOP/BPP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($indicatorRefs as $ref)
                            <tr>
                                <td class="fw-bold text-dark">
                                    {{ $ref['kolom'] }}
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        {{ $ref['grup'] }}
                                    </span>
                                </td>
                                <td class="text-secondary">
                                    {{ $ref['penjelasan'] }}
                                </td>
                                <td class="small" style="line-height: 1.35;">
                                    {{ $ref['satuan_hitung'] }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-medium text-wrap text-start">
                                        {{ $ref['rujukan'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal Input / Edit Data Indikasi Cabang (Bootstrap 5) -->
<div class="modal fade" id="modalIndikasiCabang" tabindex="-1" aria-labelledby="modalIndikasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header text-white" style="background: #1e3a8a;">
                <div>
                    <h5 class="modal-title fw-bold" id="modalIndikasiLabel">
                        <i class="bi bi-pencil-square me-2"></i>Input Temuan Indikasi Mencurigakan Cabang
                    </h5>
                    <div class="small opacity-75">Masukkan frekuensi temuan per bulan (isi angka 0 bila tidak ditemukan)</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pemeriksaan_awal.indikasi_mencurigakan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="item_id" id="f_item_id">

                <div class="modal-body p-4 bg-light">
                    <!-- Data Cabang & Periode -->
                    <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white p-3">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label small fw-bold text-dark">Nama Cabang / KCP / KCPLK <span class="text-danger">*</span></label>
                                <input type="text" name="nama_cabang" id="f_nama_cabang" class="form-control" placeholder="Misal: Kantor Cabang Utama Palu" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-dark">Periode Bulan <span class="text-danger">*</span></label>
                                <input type="text" name="periode_bulan" id="f_periode_bulan" class="form-control" value="{{ date('F Y') }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- 1. KAS & TELLER (6) -->
                    <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white">
                        <div class="card-header bg-white py-2 px-3 border-bottom fw-bold text-primary small">
                            <i class="bi bi-wallet2 me-1"></i> 1. KAS &amp; TELLER (Jumlah / Bulan)
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small">Reversal Transaksi Teller:</label>
                                    <input type="number" name="reversal_teller" id="f_reversal_teller" class="form-control form-control-sm" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Selisih Kas Teller Akhir Hari:</label>
                                    <input type="number" name="selisih_kas_teller" id="f_selisih_kas_teller" class="form-control form-control-sm" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Selisih Kas Khasanah vs OLIBS:</label>
                                    <input type="number" name="selisih_kas_khasanah" id="f_selisih_kas_khasanah" class="form-control form-control-sm" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Kas Kecil Melebihi Limit (&gt;10jt/&gt;5jt):</label>
                                    <input type="number" name="kas_kecil_melebihi_limit" id="f_kas_kecil_melebihi_limit" class="form-control form-control-sm" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Kas Kecil Belum Selesai &gt;5 Hari:</label>
                                    <input type="number" name="kas_kecil_belum_selesai" id="f_kas_kecil_belum_selesai" class="form-control form-control-sm" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Dual Control Kunci Khasanah Dilanggar:</label>
                                    <input type="number" name="dual_control_kunci_dilanggar" id="f_dual_control_kunci_dilanggar" class="form-control form-control-sm" value="0" min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. REKENING & SISTEM (6) -->
                    <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white">
                        <div class="card-header bg-white py-2 px-3 border-bottom fw-bold text-primary small">
                            <i class="bi bi-cpu me-1"></i> 2. REKENING &amp; SISTEM (Jumlah / Bulan)
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small">Transaksi Rekening Pasif/Dormant:</label>
                                    <input type="number" name="trx_rekening_pasif" id="f_trx_rekening_pasif" class="form-control form-control-sm" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Rekening Titipan Mengendap Lama:</label>
                                    <input type="number" name="trx_rekening_titipan" id="f_trx_rekening_titipan" class="form-control form-control-sm" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Trx Tidak Wajar Rekening Pegawai:</label>
                                    <input type="number" name="trx_tidak_wajar_pegawai" id="f_trx_tidak_wajar_pegawai" class="form-control form-control-sm" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Trx di Luar Jam Operasional:</label>
                                    <input type="number" name="trx_luar_jam_operasional" id="f_trx_luar_jam_operasional" class="form-control form-control-sm" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Setoran Non-Tunai Rek. Pegawai/Keluarga:</label>
                                    <input type="number" name="setoran_non_tunai_pegawai" id="f_setoran_non_tunai_pegawai" class="form-control form-control-sm" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Sharing User ID/Password:</label>
                                    <input type="number" name="sharing_user_id" id="f_sharing_user_id" class="form-control form-control-sm" value="0" min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. ATM, KREDIT, SDM, PENGADUAN (6) -->
                    <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white">
                        <div class="card-header bg-white py-2 px-3 border-bottom fw-bold text-primary small">
                            <i class="bi bi-layers me-1"></i> 3. ATM, KREDIT, SDM &amp; PENGADUAN NASABAH
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small">Indikasi Skimming/Alat Asing ATM:</label>
                                    <input type="number" name="skimming_atm" id="f_skimming_atm" class="form-control form-control-sm" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Selisih Kas Opname CRM/ATM:</label>
                                    <input type="number" name="selisih_kas_crm_atm" id="f_selisih_kas_crm_atm" class="form-control form-control-sm" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Restrukturisasi Berulang (Debitur Sama):</label>
                                    <input type="number" name="restrukturisasi_berulang" id="f_restrukturisasi_berulang" class="form-control form-control-sm" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Pencairan Kredit Dokumen Tidak Lengkap:</label>
                                    <input type="number" name="pencairan_tanpa_dokumen" id="f_pencairan_tanpa_dokumen" class="form-control form-control-sm" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Teller/Kasir Belum Ambil Cuti Wajib:</label>
                                    <input type="number" name="cuti_wajib_belum_diambil" id="f_cuti_wajib_belum_diambil" class="form-control form-control-sm" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Lonjakan Pengaduan Sejenis/Lewat SLA:</label>
                                    <input type="number" name="lonjakan_pengaduan_sla" id="f_lonjakan_pengaduan_sla" class="form-control form-control-sm" value="0" min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Umum / Temuan Lain -->
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                        <label class="form-label small fw-bold text-dark">Catatan Umum / Temuan Lapangan Lain</label>
                        <textarea name="catatan_umum" id="f_catatan_umum" rows="2" class="form-control" placeholder="Uraian temuan atau penjelasan atas anomali yang terjadi..."></textarea>
                    </div>

                </div>
                <div class="modal-footer bg-white border-top">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold" style="background:#1e3a8a; border-color:#1e3a8a;">
                        <i class="bi bi-save me-1"></i> Simpan Data Cabang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let modalIndikasi = null;

    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('modalIndikasiCabang');
        if (modalEl) {
            modalIndikasi = new bootstrap.Modal(modalEl);
        }
    });

    function openModalTambah() {
        document.getElementById('f_item_id').value = '';
        document.getElementById('f_nama_cabang').value = '';
        document.getElementById('f_catatan_umum').value = '';

        const fields = [
            'reversal_teller', 'selisih_kas_teller', 'selisih_kas_khasanah',
            'kas_kecil_melebihi_limit', 'kas_kecil_belum_selesai', 'dual_control_kunci_dilanggar',
            'trx_rekening_pasif', 'trx_rekening_titipan', 'trx_tidak_wajar_pegawai',
            'trx_luar_jam_operasional', 'setoran_non_tunai_pegawai', 'sharing_user_id',
            'skimming_atm', 'selisih_kas_crm_atm', 'restrukturisasi_berulang',
            'pencairan_tanpa_dokumen', 'cuti_wajib_belum_diambil', 'lonjakan_pengaduan_sla'
        ];

        fields.forEach(f => {
            const el = document.getElementById('f_' + f);
            if (el) el.value = 0;
        });

        document.getElementById('modalIndikasiLabel').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Input Temuan Indikasi Mencurigakan Cabang';
        if (modalIndikasi) modalIndikasi.show();
    }

    function openModalEdit(item) {
        if (!item) return;

        document.getElementById('f_item_id').value = item.id;
        document.getElementById('f_nama_cabang').value = item.nama_cabang || '';
        document.getElementById('f_periode_bulan').value = item.periode_bulan || '{{ date("F Y") }}';
        document.getElementById('f_catatan_umum').value = item.catatan_umum || '';

        const fields = [
            'reversal_teller', 'selisih_kas_teller', 'selisih_kas_khasanah',
            'kas_kecil_melebihi_limit', 'kas_kecil_belum_selesai', 'dual_control_kunci_dilanggar',
            'trx_rekening_pasif', 'trx_rekening_titipan', 'trx_tidak_wajar_pegawai',
            'trx_luar_jam_operasional', 'setoran_non_tunai_pegawai', 'sharing_user_id',
            'skimming_atm', 'selisih_kas_crm_atm', 'restrukturisasi_berulang',
            'pencairan_tanpa_dokumen', 'cuti_wajib_belum_diambil', 'lonjakan_pengaduan_sla'
        ];

        fields.forEach(f => {
            const el = document.getElementById('f_' + f);
            if (el) el.value = item[f] || 0;
        });

        document.getElementById('modalIndikasiLabel').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Temuan Indikasi: ' + item.nama_cabang;
        if (modalIndikasi) modalIndikasi.show();
    }
</script>
@endpush

@endsection

