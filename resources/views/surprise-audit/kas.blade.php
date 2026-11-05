@extends('layouts.app')

@section('title', 'KKA Kas - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3" style="max-width: 1440px; margin: 0 auto;">
    <!-- Header Navigasi -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="text-decoration-none text-secondary fw-semibold small mb-2 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kontrol Pelaksanaan
            </a>
            <div class="d-flex align-items-center gap-2">
                <span class="badge px-3 py-2 fs-7 fw-bold" style="background-color: #e0e7ff; color: #3730a3; border-radius: 6px;">
                    <i class="bi bi-cash-stack me-1"></i> WP-RA-05-C
                </span>
                <h3 class="mb-0 fw-bold" style="color: #0f2e5a; letter-spacing: -0.3px;">
                    KKA Kas Khasanah &amp; Teller
                </h3>
            </div>
            <p class="text-secondary mb-0 mt-1" style="font-size: 0.95rem;">
                {{ $assignment->nama_unit }} &bull; Pemeriksaan keadaan kas fisik (Uang Kertas, Logam, Lusuh) dan Berita Acara Pemeriksaan Kas Bank Sulteng.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
            <a href="{{ route('surprise_audit.kas.berita_acara', $assignment->visit_id) }}" target="_blank" 
               class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" style="background: #0f2e5a; border-color: #0f2e5a; font-size: 0.92rem;">
                <i class="bi bi-printer-fill me-1"></i> Cetak Berita Acara Kas
            </a>
            <span class="badge px-3 py-2 rounded-pill fw-bold text-dark" style="background-color: #f1f5f9; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                {{ $assignment->visit_id }}
            </span>
        </div>
    </div>

    <!-- Alert Sukses & Error -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4 p-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
            <div>
                <h6 class="mb-0 fw-bold text-success">Berhasil</h6>
                <div style="font-size: 0.92rem;">{{ session('success') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4 p-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
            <div>
                <h6 class="mb-0 fw-bold text-danger">Perhatian</h6>
                <div style="font-size: 0.92rem;">{{ session('error') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Header Ringkasan Rekonsiliasi Kas: Warna Lembut & Ramah di Mata -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-3">
            <div class="row text-center align-items-center g-2">
                <div class="col-md-3 border-end-md">
                    <small class="text-secondary d-block fw-bold text-uppercase" style="font-size: 0.78rem;">Saldo Sistem (Olibs CBS)</small>
                    <span class="fw-bold fs-5 text-dark">Rp {{ number_format($totalSistem ?? 0, 2, ',', '.') }}</span>
                </div>
                <div class="col-md-3 border-end-md">
                    <small class="text-secondary d-block fw-bold text-uppercase" style="font-size: 0.78rem;">Total Hasil Fisik Uang</small>
                    <span class="fw-bold fs-5" style="color: #0f2e5a;">Rp {{ number_format($totalFisik ?? 0, 2, ',', '.') }}</span>
                </div>
                <div class="col-md-3 border-end-md">
                    <small class="text-secondary d-block fw-bold text-uppercase" style="font-size: 0.78rem;">Pencocokan Saldo (Selisih)</small>
                    <span class="fw-bold fs-5 {{ ($totalSelisih ?? 0) != 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format(abs($totalSelisih ?? 0), 2, ',', '.') }}
                        <small class="fw-normal" style="font-size: 0.8rem;">({{ ($totalSelisih ?? 0) == 0 ? 'Nihil' : (($totalSelisih ?? 0) > 0 ? 'Lebih' : 'Kurang') }})</small>
                    </span>
                </div>
                <div class="col-md-3">
                    <small class="text-secondary d-block fw-bold text-uppercase" style="font-size: 0.78rem;">Status Audit &amp; Temuan</small>
                    <span class="badge px-3 py-1 rounded-pill mt-1 fw-bold fs-7" style="{{ ($exceptionAktif ?? 0) > 0 ? 'background-color: #fee2e2; color: #991b1b;' : 'background-color: #f0fdf4; color: #15803d;' }}">
                        {{ ($exceptionAktif ?? 0) > 0 ? ($exceptionAktif . ' Temuan Selisih') : 'Cocok / Normal' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- FORM INPUT OPNAME FISIK & BERITA ACARA -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-4 bg-white" style="border: 1px solid #cbd5e1 !important;">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background: #0f2e5a; color: #ffffff; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square text-warning fs-5"></i>
                        <h5 class="mb-0 fw-bold text-white" style="font-size: 1.05rem;">Form Opname &amp; Berita Acara</h5>
                    </div>
                    <span class="badge bg-white text-dark fw-bold px-3 py-1 rounded-pill" style="font-size: 0.8rem;">Bank Sulteng</span>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('surprise_audit.kas.store', $assignment->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Pos Kas & Saldo CBS -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.92rem;">Pos Kas / Objek <span class="text-danger">*</span></label>
                                <select name="objek_pemeriksaan" class="form-select" style="font-size: 0.92rem; height: 44px;" required>
                                    <option value="Kas Khasanah (Brankas Utama)">Kas Khasanah (Brankas Utama)</option>
                                    <option value="Kas Teller 01">Kas Teller 01</option>
                                    <option value="Kas Teller 02">Kas Teller 02</option>
                                    <option value="Kas Teller 03">Kas Teller 03</option>
                                    <option value="Kas Sub Vault">Kas Sub Vault</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.92rem;">Saldo Kas Olibs (CBS) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold">Rp</span>
                                    <input type="number" step="0.01" name="nilai_sistem" id="inp_nilai_sistem" class="form-control fw-bold" 
                                           style="font-size: 0.95rem; height: 44px; color: #0f2e5a;" placeholder="1410691300" required 
                                           value="{{ old('nilai_sistem', '1410691300') }}" oninput="hitungTotalKas()">
                                </div>
                            </div>
                        </div>

                        <!-- Accordion Input Pecahan: Uang Kertas, Logam, Lusuh -->
                        <div class="accordion mb-3" id="accordionPecahan">
                            <!-- 1. UANG KERTAS -->
                            <div class="accordion-item border rounded-3 mb-2" style="overflow: hidden;">
                                <h2 class="accordion-header" id="headingKertas">
                                    <button class="accordion-button fw-bold text-dark bg-light py-3 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKertas" aria-expanded="true" style="font-size: 0.95rem;">
                                        <i class="bi bi-cash me-2 text-success fs-5"></i> 1. Uang Kertas Layak Edar (Lembar)
                                        <span class="badge px-3 py-1 rounded-pill ms-auto me-2 fw-bold" id="badge_subtotal_kertas" style="background-color: #dcfce7; color: #15803d;">Rp 0</span>
                                    </button>
                                </h2>
                                <div id="collapseKertas" class="accordion-collapse collapse show" data-bs-parent="#accordionPecahan">
                                    <div class="accordion-body p-3 bg-white" style="font-size: 0.9rem;">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="text-secondary fw-semibold small">Rp 100.000</label>
                                                <input type="number" name="ule_100k" id="kertas_100k" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="6943" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-6">
                                                <label class="text-secondary fw-semibold small">Rp 75.000 (Khusus)</label>
                                                <input type="number" name="ule_75k" id="kertas_75k" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="0" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-6">
                                                <label class="text-secondary fw-semibold small">Rp 50.000</label>
                                                <input type="number" name="ule_50k" id="kertas_50k" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="10458" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-6">
                                                <label class="text-secondary fw-semibold small">Rp 20.000</label>
                                                <input type="number" name="ule_20k" id="kertas_20k" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="5986" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-6">
                                                <label class="text-secondary fw-semibold small">Rp 10.000</label>
                                                <input type="number" name="ule_10k" id="kertas_10k" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="2323" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-6">
                                                <label class="text-secondary fw-semibold small">Rp 5.000</label>
                                                <input type="number" name="ule_5k" id="kertas_5k" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="1403" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-6">
                                                <label class="text-secondary fw-semibold small">Rp 2.000</label>
                                                <input type="number" name="ule_2k" id="kertas_2k" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="1119" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-6">
                                                <label class="text-secondary fw-semibold small">Rp 1.000</label>
                                                <input type="number" name="ule_1k" id="kertas_1k" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="1009" oninput="hitungTotalKas()">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. UANG LOGAM -->
                            <div class="accordion-item border rounded-3 mb-2" style="overflow: hidden;">
                                <h2 class="accordion-header" id="headingLogam">
                                    <button class="accordion-button collapsed fw-bold text-dark bg-light py-3 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLogam" style="font-size: 0.95rem;">
                                        <i class="bi bi-coin me-2 text-warning fs-5"></i> 2. Uang Logam (Keping)
                                        <span class="badge px-3 py-1 rounded-pill ms-auto me-2 fw-bold" id="badge_subtotal_logam" style="background-color: #fef3c7; color: #b45309;">Rp 0</span>
                                    </button>
                                </h2>
                                <div id="collapseLogam" class="accordion-collapse collapse" data-bs-parent="#accordionPecahan">
                                    <div class="accordion-body p-3 bg-white" style="font-size: 0.9rem;">
                                        <div class="row g-2">
                                            <div class="col-4">
                                                <label class="text-secondary fw-semibold small">Rp 1.000 (Kpg)</label>
                                                <input type="number" name="logam_1000" id="logam_1000" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="8" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-4">
                                                <label class="text-secondary fw-semibold small">Rp 500 (Kpg)</label>
                                                <input type="number" name="logam_500" id="logam_500" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="4" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-4">
                                                <label class="text-secondary fw-semibold small">Rp 200 (Kpg)</label>
                                                <input type="number" name="logam_200" id="logam_200" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="4" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-4">
                                                <label class="text-secondary fw-semibold small">Rp 100 (Kpg)</label>
                                                <input type="number" name="logam_100" id="logam_100" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="5" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-4">
                                                <label class="text-secondary fw-semibold small">Rp 50 (Kpg)</label>
                                                <input type="number" name="logam_50" id="logam_50" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="0" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-4">
                                                <label class="text-secondary fw-semibold small">Rp 25 (Kpg)</label>
                                                <input type="number" name="logam_25" id="logam_25" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="0" oninput="hitungTotalKas()">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. UANG LUSUH (UTLE) -->
                            <div class="accordion-item border rounded-3 mb-2" style="overflow: hidden;">
                                <h2 class="accordion-header" id="headingLusuh">
                                    <button class="accordion-button collapsed fw-bold text-dark bg-light py-3 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLusuh" style="font-size: 0.95rem;">
                                        <i class="bi bi-recycle me-2 text-danger fs-5"></i> 3. Uang Lusuh / Tidak Layak Edar (Lembar)
                                        <span class="badge px-3 py-1 rounded-pill ms-auto me-2 fw-bold" id="badge_subtotal_lusuh" style="background-color: #fee2e2; color: #991b1b;">Rp 0</span>
                                    </button>
                                </h2>
                                <div id="collapseLusuh" class="accordion-collapse collapse" data-bs-parent="#accordionPecahan">
                                    <div class="accordion-body p-3 bg-white" style="font-size: 0.9rem;">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="text-secondary fw-semibold small">Rp 100.000 Lusuh</label>
                                                <input type="number" name="utle_100k" id="lusuh_100k" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="170" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-6">
                                                <label class="text-secondary fw-semibold small">Rp 75.000 Lusuh</label>
                                                <input type="number" name="utle_75k" id="lusuh_75k" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="0" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-6">
                                                <label class="text-secondary fw-semibold small">Rp 50.000 Lusuh</label>
                                                <input type="number" name="utle_50k" id="lusuh_50k" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="286" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-6">
                                                <label class="text-secondary fw-semibold small">Rp 20.000 Lusuh</label>
                                                <input type="number" name="utle_20k" id="lusuh_20k" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="187" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-6">
                                                <label class="text-secondary fw-semibold small">Rp 10.000 Lusuh</label>
                                                <input type="number" name="utle_10k" id="lusuh_10k" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="304" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-6">
                                                <label class="text-secondary fw-semibold small">Rp 5.000 Lusuh</label>
                                                <input type="number" name="utle_5k" id="lusuh_5k" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="266" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-6">
                                                <label class="text-secondary fw-semibold small">Rp 2.000 Lusuh</label>
                                                <input type="number" name="utle_2k" id="lusuh_2k" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="380" oninput="hitungTotalKas()">
                                            </div>
                                            <div class="col-6">
                                                <label class="text-secondary fw-semibold small">Rp 1.000 Lusuh</label>
                                                <input type="number" name="utle_1k" id="lusuh_1k" class="form-control hitung-kas text-end" style="height: 40px;" placeholder="0" value="120" oninput="hitungTotalKas()">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Live Hitungan Preview -->
                        <div class="p-3 rounded-3 border mb-3" style="background-color: #f8fafc;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-secondary fw-semibold" style="font-size: 0.9rem;">Total Fisik Dihitung:</span>
                                <span class="fw-bold fs-5" id="preview_total_fisik" style="color: #0f2e5a;">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary fw-semibold" style="font-size: 0.9rem;">Pencocokan Saldo:</span>
                                <span class="fw-bold fs-6" id="preview_selisih">Rp 0</span>
                            </div>
                        </div>

                        <!-- Parameter Berita Acara (Waktu & Saksi) -->
                        <div class="card border rounded-3 p-3 mb-3 bg-white">
                            <small class="fw-bold text-dark text-uppercase d-block mb-2" style="font-size: 0.82rem; letter-spacing: 0.5px;">
                                <i class="bi bi-clock-history text-primary me-1"></i> Waktu &amp; Pejabat Berita Acara
                            </small>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="text-secondary small">Jam Mulai</label>
                                    <input type="text" name="jam_mulai_opname" class="form-control" style="height: 38px; font-size: 0.9rem;" value="{{ $assignment->jam_mulai_opname ?? '16.30 WITA' }}">
                                </div>
                                <div class="col-6">
                                    <label class="text-secondary small">Jam Berakhir</label>
                                    <input type="text" name="jam_selesai_opname" class="form-control" style="height: 38px; font-size: 0.9rem;" value="{{ $assignment->jam_selesai_opname ?? '18.45 WITA' }}">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="text-secondary small">Kondisi Pemeriksaan</label>
                                <input type="text" name="kondisi_opname" class="form-control" style="height: 38px; font-size: 0.9rem;" value="{{ $assignment->kondisi_opname ?? 'sebelum Kas Harian dilimpahkan Kepada Teller' }}">
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="text-secondary small">Pemeriksa 1 (RA)</label>
                                    <input type="text" name="pemeriksa_1" class="form-control" style="height: 38px; font-size: 0.9rem;" value="{{ $assignment->pemeriksa_1 ?? $assignment->resident_auditor }}">
                                </div>
                                <div class="col-6">
                                    <label class="text-secondary small">Pemeriksa 2 (RA)</label>
                                    <input type="text" name="pemeriksa_2" class="form-control" style="height: 38px; font-size: 0.9rem;" value="{{ $assignment->pemeriksa_2 ?? 'Rostika Warda' }}">
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="text-secondary small">Saksi 1 (Pimsek Pelayanan)</label>
                                    <input type="text" name="saksi_1" class="form-control" style="height: 38px; font-size: 0.9rem;" value="{{ $assignment->saksi_1 ?? 'Hajara Harun' }}">
                                </div>
                                <div class="col-6">
                                    <label class="text-secondary small">Saksi 2 (Teller)</label>
                                    <input type="text" name="saksi_2" class="form-control" style="height: 38px; font-size: 0.9rem;" value="{{ $assignment->saksi_2 ?? 'Jasmawati T. Tolabo' }}">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="text-secondary small">Pemimpin Cabang / Pembantu (Mengetahui)</label>
                                <input type="text" name="pejabat_mengetahui" class="form-control" style="height: 38px; font-size: 0.9rem;" value="{{ $assignment->pejabat_mengetahui ?? 'ADE IRMA S. MAILILI' }}">
                            </div>
                            <div>
                                <label class="text-secondary small">Penyebab Selisih Kas (jika ada)</label>
                                <input type="text" name="keterangan_selisih_kas" class="form-control" style="height: 38px; font-size: 0.9rem;" placeholder="Contoh: kesalahan perhitungan uang teller" value="{{ $assignment->keterangan_selisih_kas ?? '' }}">
                            </div>
                        </div>

                        <!-- Tindak Lanjut & Klarifikasi -->
                        <div class="p-3 rounded-3 border mb-3" style="background-color: #f8fafc;">
                            <small class="fw-bold text-dark text-uppercase d-block mb-2" style="font-size: 0.82rem; letter-spacing: 0.5px;">
                                <i class="bi bi-shield-check text-primary me-1"></i> Tindak Lanjut &amp; Klarifikasi Kas
                            </small>
                            <div class="mb-2">
                                <label class="form-label small fw-bold text-dark">Status Tindak Lanjut:</label>
                                <select name="status_tindak_lanjut" id="sel_status_tl" class="form-select" style="font-size: 0.9rem; height: 40px;" onchange="toggleStatusTl(this.value)">
                                    <option value="SUDAH SELESAI" selected>Sudah Selesai (Wajib Upload Bukti)</option>
                                    <option value="DALAM PROSES">Dalam Proses Penyelesaian (Wajib Komitmen Tanggal)</option>
                                    <option value="TIDAK BISA">Tidak Bisa Diselesaikan (Wajib Bukti Alasan/Surat)</option>
                                </select>
                            </div>

                            <div class="mb-2" id="box_komitmen_tanggal" style="display: none;">
                                <label class="form-label small fw-bold text-primary">Komitmen Tanggal Penyelesaian Cabang <span class="text-danger">*</span></label>
                                <input type="date" name="komitmen_tanggal" class="form-control" style="height: 40px; font-size: 0.9rem;">
                                <div class="text-muted mt-1 small" style="font-size: 0.8rem;">Tanggal cabang berjanji menyelesaikan selisih.</div>
                            </div>

                            <div class="mb-2" id="box_upload_bukti">
                                <label class="form-label small fw-bold text-dark">Upload Dokumen Bukti / Slip Setoran</label>
                                <input type="file" name="file_bukti" class="form-control" style="font-size: 0.9rem; height: 40px;" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="text-muted mt-1 small" style="font-size: 0.8rem;">Unggah berkas bukti penyelesaian kas.</div>
                            </div>

                            <div class="mb-1">
                                <label class="form-label small fw-bold text-dark">Catatan / Klarifikasi RA:</label>
                                <textarea name="klarifikasi" class="form-control" rows="2" style="font-size: 0.9rem;" placeholder="Tuliskan klarifikasi atau keterangan fisik kas..."></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm rounded-pill py-3" style="background: #0f2e5a; border-color: #0f2e5a; font-size: 0.98rem;">
                            <i class="bi bi-save me-2"></i> Simpan Data Opname &amp; Berita Acara
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- TABEL REKAPITULASI RINCIAN KAS (WP-RA-05-C) -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-4 bg-white" style="border: 1px solid #e2e8f0 !important; overflow: hidden;">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background: #0f2e5a; color: #ffffff; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-table text-warning fs-5"></i>
                        <h5 class="mb-0 fw-bold text-white" style="font-size: 1.05rem;">Rekapitulasi Opname Kas Cabang</h5>
                    </div>
                    <span class="badge bg-white text-dark fw-bold px-3 py-1 rounded-pill" style="font-size: 0.82rem;">WP-RA-05-C</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.92rem;">
                            <thead style="background-color: #f8fafc; color: #334155;">
                                <tr>
                                    <th class="py-3 ps-3 text-start" style="font-weight: 700;">Pos Kas</th>
                                    <th class="py-3 text-end" style="font-weight: 700;">CBS (Rp)</th>
                                    <th class="py-3 text-end" style="font-weight: 700;">Fisik (Rp)</th>
                                    <th class="py-3 text-end" style="font-weight: 700;">Selisih</th>
                                    <th class="py-3" style="font-weight: 700;">Status TL</th>
                                    <th class="py-3 pe-3" style="font-weight: 700;">Bukti</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kasData as $row)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td class="text-start fw-bold ps-3 py-3">
                                        <div class="text-dark" style="font-size: 0.95rem;">{{ $row->objek_pemeriksaan }}</div>
                                        <div class="text-muted fw-normal mt-1" style="font-size: 0.8rem;">
                                            Kertas: Rp {{ number_format($row->total_ule, 0, ',', '.') }} | 
                                            Logam: Rp {{ number_format($row->total_logam, 0, ',', '.') }} | 
                                            Lusuh: Rp {{ number_format($row->total_utle, 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="text-end py-3">Rp {{ number_format($row->nilai_sistem, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold py-3" style="color: #0f2e5a;">Rp {{ number_format($row->nilai_fisik, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold py-3 {{ $row->selisih != 0 ? 'text-danger' : 'text-success' }}">
                                        Rp {{ number_format($row->selisih, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3">
                                        @php
                                            $stTl = $row->status_tindak_lanjut ?? 'SUDAH SELESAI';
                                            $tlBadge = match($stTl) {
                                                'SUDAH SELESAI' => 'background-color: #f0fdf4; color: #166534;',
                                                'DALAM PROSES' => 'background-color: #fffbeb; color: #b45309;',
                                                default => 'background-color: #fee2e2; color: #991b1b;',
                                            };
                                        @endphp
                                        <span class="badge px-3 py-1 rounded-pill fw-semibold" style="{{ $tlBadge }}; font-size: 0.8rem;">
                                            {{ $stTl }}
                                        </span>
                                        @if($row->komitmen_tanggal)
                                            <div class="text-secondary mt-1 small" style="font-size: 0.78rem;">
                                                Target: {{ date('d/m/Y', strtotime($row->komitmen_tanggal)) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 pe-3">
                                        @if($row->file_bukti)
                                            <a href="{{ route('surprise_audit.view_bukti', ['type' => 'kas', 'id' => $row->id]) }}" target="_blank" 
                                               class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-semibold" 
                                               style="background: #0f2e5a; border-color: #0f2e5a; font-size: 0.82rem;" title="Lihat Bukti PDF">
                                                <i class="bi bi-eye me-1"></i> Bukti
                                            </a>
                                        @else
                                            <form action="{{ route('surprise_audit.minta_bukti', $assignment->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="detail_type" value="kas">
                                                <input type="hidden" name="detail_id" value="{{ $row->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size: 0.82rem;" title="Minta Bukti ke Cabang" {{ $row->minta_bukti_cabang ? 'disabled' : '' }}>
                                                    <i class="bi bi-send me-1"></i> {{ $row->minta_bukti_cabang ? 'Diminta' : 'Minta Bukti' }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <div class="py-4">
                                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                            <h6 class="fw-bold text-secondary mb-1">Belum ada data opname kas yang dicatat.</h6>
                                            <p class="small text-muted mb-0">Gunakan formulir di sebelah kiri untuk menginput rincian lembar kas fisik.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function hitungTotalKas() {
    // 1. Kertas
    const k100 = (parseFloat(document.getElementById('kertas_100k')?.value) || 0) * 100000;
    const k75  = (parseFloat(document.getElementById('kertas_75k')?.value) || 0) * 75000;
    const k50  = (parseFloat(document.getElementById('kertas_50k')?.value) || 0) * 50000;
    const k20  = (parseFloat(document.getElementById('kertas_20k')?.value) || 0) * 20000;
    const k10  = (parseFloat(document.getElementById('kertas_10k')?.value) || 0) * 10000;
    const k5   = (parseFloat(document.getElementById('kertas_5k')?.value) || 0) * 5000;
    const k2   = (parseFloat(document.getElementById('kertas_2k')?.value) || 0) * 2000;
    const k1   = (parseFloat(document.getElementById('kertas_1k')?.value) || 0) * 1000;
    const subtotalKertas = k100 + k75 + k50 + k20 + k10 + k5 + k2 + k1;

    // 2. Logam
    const l1000 = (parseFloat(document.getElementById('logam_1000')?.value) || 0) * 1000;
    const l500  = (parseFloat(document.getElementById('logam_500')?.value) || 0) * 500;
    const l200  = (parseFloat(document.getElementById('logam_200')?.value) || 0) * 200;
    const l100  = (parseFloat(document.getElementById('logam_100')?.value) || 0) * 100;
    const l50   = (parseFloat(document.getElementById('logam_50')?.value) || 0) * 50;
    const l25   = (parseFloat(document.getElementById('logam_25')?.value) || 0) * 25;
    const subtotalLogam = l1000 + l500 + l200 + l100 + l50 + l25;

    // 3. Lusuh
    const u100 = (parseFloat(document.getElementById('lusuh_100k')?.value) || 0) * 100000;
    const u75  = (parseFloat(document.getElementById('lusuh_75k')?.value) || 0) * 75000;
    const u50  = (parseFloat(document.getElementById('lusuh_50k')?.value) || 0) * 50000;
    const u20  = (parseFloat(document.getElementById('lusuh_20k')?.value) || 0) * 20000;
    const u10  = (parseFloat(document.getElementById('lusuh_10k')?.value) || 0) * 10000;
    const u5   = (parseFloat(document.getElementById('lusuh_5k')?.value) || 0) * 5000;
    const u2   = (parseFloat(document.getElementById('lusuh_2k')?.value) || 0) * 2000;
    const u1   = (parseFloat(document.getElementById('lusuh_1k')?.value) || 0) * 1000;
    const subtotalLusuh = u100 + u75 + u50 + u20 + u10 + u5 + u2 + u1;

    // Update Subtotal Badges
    const badgeKertas = document.getElementById('badge_subtotal_kertas');
    if (badgeKertas) badgeKertas.innerText = 'Rp ' + subtotalKertas.toLocaleString('id-ID');
    const badgeLogam = document.getElementById('badge_subtotal_logam');
    if (badgeLogam) badgeLogam.innerText = 'Rp ' + subtotalLogam.toLocaleString('id-ID');
    const badgeLusuh = document.getElementById('badge_subtotal_lusuh');
    if (badgeLusuh) badgeLusuh.innerText = 'Rp ' + subtotalLusuh.toLocaleString('id-ID');

    // Total Fisik & Selisih
    const totalFisik = subtotalKertas + subtotalLogam + subtotalLusuh;
    const cbs = parseFloat(document.getElementById('inp_nilai_sistem')?.value) || 0;
    const selisih = cbs - totalFisik;

    const prevFisik = document.getElementById('preview_total_fisik');
    if (prevFisik) prevFisik.innerText = 'Rp ' + totalFisik.toLocaleString('id-ID');

    const prevSelisih = document.getElementById('preview_selisih');
    if (prevSelisih) {
        if (selisih === 0) {
            prevSelisih.innerText = 'Rp 0 (Cocok / Nihil)';
            prevSelisih.className = 'fw-bold fs-6 text-success';
        } else if (selisih > 0) {
            prevSelisih.innerText = 'Rp ' + Math.abs(selisih).toLocaleString('id-ID') + ' (Kurang Kas Fisik)';
            prevSelisih.className = 'fw-bold fs-6 text-danger';
        } else {
            prevSelisih.innerText = 'Rp ' + Math.abs(selisih).toLocaleString('id-ID') + ' (Lebih Kas Fisik)';
            prevSelisih.className = 'fw-bold fs-6 text-warning';
        }
    }
}

function toggleStatusTl(status) {
    const boxKomitmen = document.getElementById('box_komitmen_tanggal');
    if (boxKomitmen) {
        boxKomitmen.style.display = (status === 'DALAM PROSES') ? 'block' : 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    hitungTotalKas();
});
</script>
@endsection
