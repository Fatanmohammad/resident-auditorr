@extends('layouts.app')

@section('title', 'KKA Kas ATM (WP-RA-05-D) - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3" style="background-color: #f8fafc; min-height: 100vh;">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="text-decoration-none text-muted mb-2 d-inline-block fw-semibold" style="font-size: 14px;">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kontrol Pelaksanaan
            </a>
            <h3 class="fw-bold mb-1" style="color: #0f2e5a; letter-spacing: -0.3px;">
                <i class="bi bi-cpu text-primary me-2"></i> WP-RA-05-D | KKA Surprise Kas ATM
            </h3>
            <p class="text-muted mb-0" style="font-size: 14px;">
                Pemeriksaan fisik kaset ATM: Auditor <strong>hanya menginput jumlah lembar</strong>. Nilai per kaset, Total Fisik, dan Selisih akan <strong>terhitung otomatis secara real-time</strong>.
            </p>
        </div>
        <div>
            <span class="badge px-3 py-2 rounded-pill shadow-sm" style="background-color: #0f2e5a; color: #fff; font-size: 13px; font-weight: 600;">
                <i class="bi bi-building me-1"></i> {{ $assignment->visit_id }} | {{ $assignment->nama_unit }}
            </span>
        </div>
    </div>

    <!-- Alert Sukses / Gagal -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4 border-0" role="alert" style="background-color: #f0fdf4; color: #166534; border-left: 4px solid #22c55e !important;">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i> <span style="font-size: 14px; font-weight: 600;">{{ session('success') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4 border-0" role="alert" style="background-color: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444 !important;">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i> <strong>Mohon lengkapi formulir dengan benar:</strong>
        <ul class="mb-0 mt-2 ps-3" style="font-size: 13.5px;">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- HEADER RINGKASAN REKONSILIASI KAS ATM (WP-RA-05-D) -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid #3b82f6 !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Saldo Buku CBS (Olibs)</small>
                <div class="fw-bold mt-1" style="font-size: 1.35rem; color: #1e293b;">
                    Rp {{ number_format($saldoBukuTotal ?? 0, 0, ',', '.') }}
                </div>
                <div class="small text-muted mt-1" style="font-size: 12px;">Saldo buku kas seluruh ATM</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid #0284c7 !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Hasil Fisik Seluruh ATM</small>
                <div class="fw-bold mt-1" style="font-size: 1.35rem; color: #0f2e5a;">
                    Rp {{ number_format($hasilFisikTotal ?? 0, 0, ',', '.') }}
                </div>
                <div class="small text-muted mt-1" style="font-size: 12px;">Akumulasi fisik kaset diuji</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid {{ ($selisihTotal ?? 0) != 0 ? '#ef4444' : '#10b981' }} !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Total Selisih Kas ATM</small>
                <div class="fw-bold mt-1 {{ ($selisihTotal ?? 0) != 0 ? 'text-danger' : 'text-success' }}" style="font-size: 1.35rem;">
                    Rp {{ number_format($selisihTotal ?? 0, 0, ',', '.') }}
                </div>
                <div class="small mt-1">
                    @if(($selisihTotal ?? 0) == 0)
                        <span class="badge rounded-pill px-2 py-1" style="background-color: #f0fdf4; color: #166534; font-size: 11px; font-weight: 600;">NIHIL / COCOK</span>
                    @else
                        <span class="badge rounded-pill px-2 py-1" style="background-color: #fee2e2; color: #991b1b; font-size: 11px; font-weight: 600;">TERDAPAT SELISIH</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid {{ ($exceptionAktifCount ?? 0) > 0 ? '#ef4444' : '#10b981' }} !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Status Temuan / Exception</small>
                <div class="mt-2">
                    <span class="badge rounded-pill px-3 py-2" style="{{ ($exceptionAktifCount ?? 0) > 0 ? 'background-color: #fee2e2; color: #991b1b;' : 'background-color: #f0fdf4; color: #166534;' }} font-size: 12.5px; font-weight: 600;">
                        {{ $exceptionAktifCount ?? 0 }} Mesin Selisih
                    </span>
                </div>
                <div class="small text-muted mt-2" style="font-size: 12px;">Hasil verifikasi KKA</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- FORM INPUT OPNAME KAS ATM DENGAN REALTIME CALCULATOR -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; overflow: hidden;">
                <div class="card-header py-3 px-4" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); color: #fff;">
                    <h5 class="card-title fw-bold mb-1" style="font-size: 15px;">
                        <i class="bi bi-calculator me-2"></i> Input Fisik Kas ATM (Real-Time)
                    </h5>
                    <small class="text-white-50" style="font-size: 12px;">Isi lembar saja, nilai rupiah & selisih dihitung otomatis</small>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('surprise_audit.atm.store', $assignment->id) }}" method="POST" enctype="multipart/form-data" id="formAtm">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark" style="font-size: 13px;">Objek / Lokasi Mesin ATM <span class="text-danger">*</span></label>
                            <input type="text" name="objek_lokasi_atm" class="form-control" style="font-size: 13.5px; height: 42px; border-color: #cbd5e1;" placeholder="Contoh: ATM Galeri KCU Palu / ATM KCP Toili" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark" style="font-size: 13px;">Saldo Buku / Expected (CBS Olibs) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold" style="background-color: #f1f5f9; color: #475569; font-size: 13.5px;">Rp</span>
                                <input type="number" step="1" name="saldo_buku" id="saldo_buku" class="form-control fw-bold" style="font-size: 14px; height: 42px; border-color: #cbd5e1;" placeholder="0" required oninput="hitungAtm()">
                            </div>
                            <small class="text-muted" style="font-size: 12px;">Saldo akhir rekening kas ATM menurut sistem CBS</small>
                        </div>

                        <!-- 4 KASET ATM DENGAN INPUT LEMBAR SAJA -->
                        <div class="rounded-3 p-3 mb-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold" style="color: #0f2e5a; font-size: 13.5px;"><i class="bi bi-stack me-1 text-primary"></i> Rincian 4 Kaset ATM</span>
                                <span class="badge rounded-pill px-2 py-1" style="background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 11px;">Rumus: Lembar &times; Pecahan</span>
                            </div>

                            <!-- Kaset 1 -->
                            <div class="card border mb-2 shadow-none" style="background-color: #ffffff; border-color: #e2e8f0 !important;">
                                <div class="card-body p-2">
                                    <div class="row align-items-center g-2">
                                        <div class="col-4">
                                            <label class="small fw-bold text-muted d-block" style="font-size: 11.5px;">Kaset 1</label>
                                            <select name="kaset1_pecahan" id="k1_pecahan" class="form-select form-select-sm fw-bold" style="font-size: 12.5px; border-color: #cbd5e1;" onchange="hitungAtm()">
                                                <option value="100000" selected>Rp 100.000</option>
                                                <option value="50000">Rp 50.000</option>
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <label class="small fw-bold text-muted d-block" style="font-size: 11.5px;">Jumlah Lembar</label>
                                            <input type="number" min="0" name="kaset1_lembar" id="k1_lembar" class="form-control form-control-sm text-end fw-bold" style="font-size: 13.5px; border-color: #cbd5e1; color: #0f2e5a;" placeholder="0" oninput="hitungAtm()">
                                        </div>
                                        <div class="col-4 text-end">
                                            <label class="small text-muted d-block" style="font-size: 11px;">Nilai Kaset 1</label>
                                            <span class="badge px-2 py-1 fw-bold rounded-pill" style="background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 12px;" id="k1_nilai_badge">Rp 0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kaset 2 -->
                            <div class="card border mb-2 shadow-none" style="background-color: #ffffff; border-color: #e2e8f0 !important;">
                                <div class="card-body p-2">
                                    <div class="row align-items-center g-2">
                                        <div class="col-4">
                                            <label class="small fw-bold text-muted d-block" style="font-size: 11.5px;">Kaset 2</label>
                                            <select name="kaset2_pecahan" id="k2_pecahan" class="form-select form-select-sm fw-bold" style="font-size: 12.5px; border-color: #cbd5e1;" onchange="hitungAtm()">
                                                <option value="100000">Rp 100.000</option>
                                                <option value="50000" selected>Rp 50.000</option>
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <label class="small fw-bold text-muted d-block" style="font-size: 11.5px;">Jumlah Lembar</label>
                                            <input type="number" min="0" name="kaset2_lembar" id="k2_lembar" class="form-control form-control-sm text-end fw-bold" style="font-size: 13.5px; border-color: #cbd5e1; color: #0f2e5a;" placeholder="0" oninput="hitungAtm()">
                                        </div>
                                        <div class="col-4 text-end">
                                            <label class="small text-muted d-block" style="font-size: 11px;">Nilai Kaset 2</label>
                                            <span class="badge px-2 py-1 fw-bold rounded-pill" style="background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 12px;" id="k2_nilai_badge">Rp 0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kaset 3 -->
                            <div class="card border mb-2 shadow-none" style="background-color: #ffffff; border-color: #e2e8f0 !important;">
                                <div class="card-body p-2">
                                    <div class="row align-items-center g-2">
                                        <div class="col-4">
                                            <label class="small fw-bold text-muted d-block" style="font-size: 11.5px;">Kaset 3</label>
                                            <select name="kaset3_pecahan" id="k3_pecahan" class="form-select form-select-sm fw-bold" style="font-size: 12.5px; border-color: #cbd5e1;" onchange="hitungAtm()">
                                                <option value="100000" selected>Rp 100.000</option>
                                                <option value="50000">Rp 50.000</option>
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <label class="small fw-bold text-muted d-block" style="font-size: 11.5px;">Jumlah Lembar</label>
                                            <input type="number" min="0" name="kaset3_lembar" id="k3_lembar" class="form-control form-control-sm text-end fw-bold" style="font-size: 13.5px; border-color: #cbd5e1; color: #0f2e5a;" placeholder="0" oninput="hitungAtm()">
                                        </div>
                                        <div class="col-4 text-end">
                                            <label class="small text-muted d-block" style="font-size: 11px;">Nilai Kaset 3</label>
                                            <span class="badge px-2 py-1 fw-bold rounded-pill" style="background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 12px;" id="k3_nilai_badge">Rp 0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kaset 4 -->
                            <div class="card border mb-2 shadow-none" style="background-color: #ffffff; border-color: #e2e8f0 !important;">
                                <div class="card-body p-2">
                                    <div class="row align-items-center g-2">
                                        <div class="col-4">
                                            <label class="small fw-bold text-muted d-block" style="font-size: 11.5px;">Kaset 4</label>
                                            <select name="kaset4_pecahan" id="k4_pecahan" class="form-select form-select-sm fw-bold" style="font-size: 12.5px; border-color: #cbd5e1;" onchange="hitungAtm()">
                                                <option value="100000">Rp 100.000</option>
                                                <option value="50000" selected>Rp 50.000</option>
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <label class="small fw-bold text-muted d-block" style="font-size: 11.5px;">Jumlah Lembar</label>
                                            <input type="number" min="0" name="kaset4_lembar" id="k4_lembar" class="form-control form-control-sm text-end fw-bold" style="font-size: 13.5px; border-color: #cbd5e1; color: #0f2e5a;" placeholder="0" oninput="hitungAtm()">
                                        </div>
                                        <div class="col-4 text-end">
                                            <label class="small text-muted d-block" style="font-size: 11px;">Nilai Kaset 4</label>
                                            <span class="badge px-2 py-1 fw-bold rounded-pill" style="background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 12px;" id="k4_nilai_badge">Rp 0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KARTU PREVIEW OTOMATIS HASIL FISIK & SELISIH (Eye-Calming Executive Style) -->
                        <div class="p-3 mb-3 rounded-3" style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-top: 3px solid #0f2e5a;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-uppercase fw-bold" style="color: #475569; font-size: 11.5px; letter-spacing: 0.5px;">
                                    <i class="bi bi-cpu-fill me-1 text-primary"></i> Live Calculator Hasil Fisik
                                </small>
                                <span class="badge rounded-pill px-2 py-1" style="background-color: #e2e8f0; color: #334155; font-size: 11px;" id="total_lembar_badge">0 Lembar Total</span>
                            </div>
                            <div class="row g-2 text-center py-2">
                                <div class="col-6 border-end" style="border-color: #cbd5e1 !important;">
                                    <small class="text-muted d-block" style="font-size: 11.5px;">Total Fisik Kas ATM</small>
                                    <div class="fw-bold" style="font-size: 1.25rem; color: #0f2e5a;" id="live_total_fisik">Rp 0</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block" style="font-size: 11.5px;">Selisih (Fisik - Saldo CBS)</small>
                                    <div class="fw-bold text-success" style="font-size: 1.25rem;" id="live_selisih">Rp 0</div>
                                </div>
                            </div>
                            <div class="mt-2 text-center" id="selisih_status_alert">
                                <span class="badge rounded-pill w-100 py-2 fw-semibold" style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 12px;">
                                    <i class="bi bi-check-circle me-1"></i> STATUS: NIHIL / SESUAI
                                </span>
                            </div>
                        </div>

                        <!-- TINDAK LANJUT & BUKTI -->
                        <div class="rounded-3 p-3 bg-white mb-3" style="border: 1px solid #e2e8f0;">
                            <div class="fw-bold mb-2" style="color: #0f2e5a; font-size: 13.5px;">
                                <i class="bi bi-shield-check text-success me-1"></i> Status Tindak Lanjut & Bukti
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted" style="font-size: 12.5px;">Status Penyelesaian</label>
                                <select name="status_tindak_lanjut" id="status_tindak_lanjut" class="form-select fw-semibold" style="font-size: 13px; height: 40px; border-color: #cbd5e1;" onchange="toggleBuktiFields()">
                                    <option value="SUDAH SELESAI" selected>SUDAH SELESAI (Lampirkan Bukti)</option>
                                    <option value="DALAM PROSES">DALAM PROSES PENYELESAIAN (Input Komitmen Tanggal)</option>
                                    <option value="TIDAK BISA">TIDAK BISA DISELESAIKAN (Wajib Alasan / Surat Bukti)</option>
                                </select>
                            </div>

                            <div class="mb-3" id="group_komitmen_tanggal" style="display: none;">
                                <label class="form-label small fw-bold text-danger" style="font-size: 12.5px;">Komitmen Tanggal Penyelesaian Cabang *</label>
                                <input type="date" name="komitmen_tanggal" id="komitmen_tanggal" class="form-control" style="font-size: 13px; height: 40px; border-color: #cbd5e1;">
                                <small class="text-muted" style="font-size: 11.5px;">Jika dalam proses, wajib menentukan batas akhir perbaikan cabang.</small>
                            </div>

                            <div class="mb-3" id="group_file_bukti">
                                <label class="form-label small fw-bold text-muted" style="font-size: 12.5px;">Upload Berkas Bukti Opname ATM (Foto / Struk / Counter)</label>
                                <input type="file" name="file_bukti" class="form-control" style="font-size: 12.5px; border-color: #cbd5e1;" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted" style="font-size: 11.5px;">Format didukung: PDF, JPG, PNG (Maks 10MB)</small>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-bold text-muted" style="font-size: 12.5px;">Klarifikasi Cabang / Catatan Pemeriksa</label>
                                <textarea name="klarifikasi" class="form-control" rows="2" style="font-size: 13px; border-color: #cbd5e1;" placeholder="Klarifikasi petugas ATM atau kronologis selisih..."></textarea>
                            </div>
                        </div>

                        <!-- ANALISIS PENYEBAB & DAMPAK (JIKA SELISIH) -->
                        <div class="accordion mb-3" id="accordionSebabDampak">
                            <div class="accordion-item rounded-3" style="border: 1px solid #e2e8f0; overflow: hidden;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-2 text-dark fw-semibold" style="font-size: 13px; background-color: #f8fafc;" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSebab">
                                        <i class="bi bi-journal-text me-2 text-primary"></i> Analisis Tambahan (Sebab & Dampak)
                                    </button>
                                </h2>
                                <div id="collapseSebab" class="accordion-collapse collapse">
                                    <div class="accordion-body p-3" style="background-color: #ffffff;">
                                        <div class="mb-2">
                                            <label class="form-label small fw-bold text-muted" style="font-size: 12px;">Sebab / Penyebab Selisih</label>
                                            <textarea name="sebab" class="form-control" rows="2" style="font-size: 13px; border-color: #cbd5e1;" placeholder="Contoh: Kesalahan pengisian kaset atau lembar tersangkut..."></textarea>
                                        </div>
                                        <div>
                                            <label class="form-label small fw-bold text-muted" style="font-size: 12px;">Dampak Temuan</label>
                                            <textarea name="dampak" class="form-control" rows="2" style="font-size: 13px; border-color: #cbd5e1;" placeholder="Contoh: Potensi komplain nasabah atau keterlambatan rekonsiliasi..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn w-100 fw-semibold py-2 shadow-sm text-white rounded-pill" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); font-size: 14px;">
                            <i class="bi bi-check-circle me-1"></i> Simpan Data Opname Kas ATM
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- TABEL REKAPITULASI HASIL KKA KAS ATM -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; overflow: hidden;">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); color: #fff;">
                    <span class="fw-bold" style="font-size: 14.5px;">
                        <i class="bi bi-table me-2 text-info"></i> Rekapitulasi Fisik KKA Kas ATM
                    </span>
                    <span class="badge rounded-pill px-3 py-1" style="background-color: rgba(255, 255, 255, 0.2); color: #fff; font-size: 12px; font-weight: 600;">WP-RA-05-D</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0 text-center" style="font-size: 13px;">
                            <thead style="background-color: #f1f5f9; color: #1e293b; font-weight: 700; font-size: 12.5px;" class="text-uppercase">
                                <tr>
                                    <th rowspan="2" class="align-middle ps-3 text-start" style="width: 140px;">Mesin ATM</th>
                                    <th rowspan="2" class="align-middle" style="width: 110px;">Saldo CBS</th>
                                    <th colspan="2" style="background-color: #eff6ff; color: #1e40af;">Kaset 1</th>
                                    <th colspan="2" style="background-color: #eff6ff; color: #1e40af;">Kaset 2</th>
                                    <th colspan="2" style="background-color: #eff6ff; color: #1e40af;">Kaset 3</th>
                                    <th colspan="2" style="background-color: #eff6ff; color: #1e40af;">Kaset 4</th>
                                    <th rowspan="2" class="align-middle" style="width: 120px;">Total Fisik</th>
                                    <th rowspan="2" class="align-middle" style="width: 120px;">Selisih</th>
                                    <th rowspan="2" class="align-middle" style="width: 110px;">Status</th>
                                    <th rowspan="2" class="align-middle pe-2" style="width: 60px;">Aksi</th>
                                </tr>
                                <tr>
                                    <th class="py-1" style="font-size: 11px;">Lbr</th>
                                    <th class="py-1" style="font-size: 11px;">Nilai</th>
                                    <th class="py-1" style="font-size: 11px;">Lbr</th>
                                    <th class="py-1" style="font-size: 11px;">Nilai</th>
                                    <th class="py-1" style="font-size: 11px;">Lbr</th>
                                    <th class="py-1" style="font-size: 11px;">Nilai</th>
                                    <th class="py-1" style="font-size: 11px;">Lbr</th>
                                    <th class="py-1" style="font-size: 11px;">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($atmData ?? [] as $row)
                                <tr>
                                    <td class="text-start fw-bold ps-3 py-2">
                                        <div style="color: #0f2e5a;">{{ $row->objek_lokasi_atm }}</div>
                                        <small class="text-muted d-block" style="font-size: 11px;">{{ $row->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td class="text-end fw-semibold py-2">Rp {{ number_format($row->saldo_buku, 0, ',', '.') }}</td>
                                    
                                    <!-- Kaset 1 -->
                                    <td class="py-2">{{ number_format($row->kaset1_lembar, 0) }}</td>
                                    <td class="text-end py-2" style="font-size: 11.5px;">Rp {{ number_format($row->kaset1_nilai, 0, ',', '.') }}</td>

                                    <!-- Kaset 2 -->
                                    <td class="py-2">{{ number_format($row->kaset2_lembar, 0) }}</td>
                                    <td class="text-end py-2" style="font-size: 11.5px;">Rp {{ number_format($row->kaset2_nilai, 0, ',', '.') }}</td>

                                    <!-- Kaset 3 -->
                                    <td class="py-2">{{ number_format($row->kaset3_lembar, 0) }}</td>
                                    <td class="text-end py-2" style="font-size: 11.5px;">Rp {{ number_format($row->kaset3_nilai, 0, ',', '.') }}</td>

                                    <!-- Kaset 4 -->
                                    <td class="py-2">{{ number_format($row->kaset4_lembar, 0) }}</td>
                                    <td class="text-end py-2" style="font-size: 11.5px;">Rp {{ number_format($row->kaset4_nilai, 0, ',', '.') }}</td>

                                    <!-- Total Fisik -->
                                    <td class="text-end fw-bold py-2" style="color: #0f2e5a;">Rp {{ number_format($row->hasil_fisik_total, 0, ',', '.') }}</td>
                                    
                                    <!-- Selisih -->
                                    <td class="text-end fw-bold py-2 {{ $row->selisih != 0 ? 'text-danger' : 'text-success' }}">
                                        Rp {{ number_format($row->selisih, 0, ',', '.') }}
                                        @if($row->selisih == 0)
                                            <span class="badge rounded-pill px-2 py-0 d-block mt-1" style="background-color: #f0fdf4; color: #166534; font-size: 10px;">NIHIL</span>
                                        @else
                                            <span class="badge rounded-pill px-2 py-0 d-block mt-1" style="background-color: #fee2e2; color: #991b1b; font-size: 10px;">{{ $row->selisih > 0 ? '+ LEBIH' : '- KURANG' }}</span>
                                        @endif
                                    </td>

                                    <!-- Status & Bukti -->
                                    <td class="text-center py-2">
                                        <span class="badge rounded-pill px-2 py-1 mb-1 d-inline-block" style="{{ $row->status_tindak_lanjut === 'SUDAH SELESAI' ? 'background-color: #f0fdf4; color: #166534;' : ($row->status_tindak_lanjut === 'DALAM PROSES' ? 'background-color: #fffbeb; color: #b45309;' : 'background-color: #fee2e2; color: #991b1b;') }} font-size: 11px; font-weight: 600;">
                                            {{ $row->status_tindak_lanjut ?? 'SELESAI' }}
                                        </span>
                                        @if($row->file_bukti)
                                            <div>
                                                <a href="{{ route('surprise_audit.view_bukti', ['type' => 'atm', 'id' => $row->id]) }}" target="_blank" class="badge rounded-pill px-2 py-1 text-decoration-none shadow-sm" style="background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 10.5px;">
                                                    <i class="bi bi-eye me-1"></i> Bukti
                                                </a>
                                            </div>
                                        @elseif($row->komitmen_tanggal)
                                            <small class="text-muted d-block" style="font-size: 10.5px;">Tgl: {{ \Carbon\Carbon::parse($row->komitmen_tanggal)->format('d/m/Y') }}</small>
                                        @endif
                                    </td>

                                    <!-- Aksi Hapus -->
                                    <td class="pe-2 text-center py-2">
                                        <form action="{{ route('surprise_audit.atm.destroy', ['id' => $assignment->id, 'detailId' => $row->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data opname ATM ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 rounded-pill" style="font-size: 12px;" title="Hapus Data">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="14" class="text-center py-5 text-muted">
                                        <i class="bi bi-cpu fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        Belum ada data opname kas ATM yang diinput pada penugasan ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Petunjuk SOP Audit Kas ATM -->
            <div class="card border-0 rounded-3 p-3 shadow-sm" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important;">
                <div class="d-flex align-items-start">
                    <i class="bi bi-info-circle-fill text-primary fs-4 me-3 mt-1"></i>
                    <div>
                        <h6 class="fw-bold mb-1" style="color: #0f2e5a; font-size: 14px;">Petunjuk Perhitungan Kas Fisik ATM:</h6>
                        <ul class="mb-0 text-muted ps-3" style="font-size: 13px; line-height: 1.6;">
                            <li>Auditor hanya perlu mengisikan <strong>jumlah lembar</strong> yang terdapat pada masing-masing kaset mesin ATM.</li>
                            <li>Sistem secara otomatis mengalikan <code>Pecahan &times; Lembar = Nilai Rupiah Kaset</code>.</li>
                            <li><strong>Total Fisik</strong> merupakan penjumlahan nilai seluruh kaset (Kaset 1 + 2 + 3 + 4).</li>
                            <li><strong>Selisih</strong> dihitung secara otomatis: <code>Hasil Fisik Total &minus; Saldo Buku CBS (Olibs)</code>.</li>
                            <li>Jika terdapat selisih, auditor wajib meminta klarifikasi petugas dan melampirkan bukti register/counter ATM.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JAVASCRIPT: REAL-TIME ATM CALCULATOR -->
<script>
function hitungAtm() {
    // 1. Ambil Pecahan
    const p1 = parseFloat(document.getElementById('k1_pecahan').value) || 0;
    const p2 = parseFloat(document.getElementById('k2_pecahan').value) || 0;
    const p3 = parseFloat(document.getElementById('k3_pecahan').value) || 0;
    const p4 = parseFloat(document.getElementById('k4_pecahan').value) || 0;

    // 2. Ambil Lembar yang diinput user
    const l1 = parseInt(document.getElementById('k1_lembar').value) || 0;
    const l2 = parseInt(document.getElementById('k2_lembar').value) || 0;
    const l3 = parseInt(document.getElementById('k3_lembar').value) || 0;
    const l4 = parseInt(document.getElementById('k4_lembar').value) || 0;

    // 3. Hitung Nilai Per Kaset
    const n1 = p1 * l1;
    const n2 = p2 * l2;
    const n3 = p3 * l3;
    const n4 = p4 * l4;

    // 4. Update Badge Nilai Per Kaset
    document.getElementById('k1_nilai_badge').innerText = 'Rp ' + n1.toLocaleString('id-ID');
    document.getElementById('k2_nilai_badge').innerText = 'Rp ' + n2.toLocaleString('id-ID');
    document.getElementById('k3_nilai_badge').innerText = 'Rp ' + n3.toLocaleString('id-ID');
    document.getElementById('k4_nilai_badge').innerText = 'Rp ' + n4.toLocaleString('id-ID');

    // 5. Hitung Total Lembar & Total Fisik
    const totalLembar = l1 + l2 + l3 + l4;
    const totalFisik = n1 + n2 + n3 + n4;
    document.getElementById('total_lembar_badge').innerText = totalLembar.toLocaleString('id-ID') + ' Lembar Total';
    document.getElementById('live_total_fisik').innerText = 'Rp ' + totalFisik.toLocaleString('id-ID');

    // 6. Hitung Selisih dengan Saldo Buku CBS
    const saldoBuku = parseFloat(document.getElementById('saldo_buku').value) || 0;
    const selisih = totalFisik - saldoBuku;
    
    const liveSelisihEl = document.getElementById('live_selisih');
    const alertEl = document.getElementById('selisih_status_alert');

    if (selisih === 0) {
        liveSelisihEl.className = 'fw-bold text-success';
        liveSelisihEl.innerText = 'Rp 0 (NIHIL)';
        alertEl.innerHTML = `
            <span class="badge rounded-pill w-100 py-2 fw-semibold" style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 12px;">
                <i class="bi bi-check-circle me-1"></i> STATUS: NIHIL / SESUAI
            </span>
        `;
    } else if (selisih > 0) {
        liveSelisihEl.className = 'fw-bold text-danger';
        liveSelisihEl.innerText = '+ Rp ' + Math.abs(selisih).toLocaleString('id-ID');
        alertEl.innerHTML = `
            <span class="badge rounded-pill w-100 py-2 fw-semibold" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 12px;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> PERINGATAN: LEBIH FISIK (+ Rp ${Math.abs(selisih).toLocaleString('id-ID')})
            </span>
        `;
    } else {
        liveSelisihEl.className = 'fw-bold text-danger';
        liveSelisihEl.innerText = '- Rp ' + Math.abs(selisih).toLocaleString('id-ID');
        alertEl.innerHTML = `
            <span class="badge rounded-pill w-100 py-2 fw-semibold" style="background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca; font-size: 12px;">
                <i class="bi bi-exclamation-circle-fill me-1"></i> PERINGATAN: KURANG FISIK (- Rp ${Math.abs(selisih).toLocaleString('id-ID')})
            </span>
        `;
    }
}

function toggleBuktiFields() {
    const status = document.getElementById('status_tindak_lanjut').value;
    const groupTanggal = document.getElementById('group_komitmen_tanggal');
    const groupBukti = document.getElementById('group_file_bukti');

    if (status === 'DALAM PROSES') {
        groupTanggal.style.display = 'block';
        groupBukti.style.display = 'none';
    } else if (status === 'SUDAH SELESAI') {
        groupTanggal.style.display = 'none';
        groupBukti.style.display = 'block';
    } else { // TIDAK BISA
        groupTanggal.style.display = 'none';
        groupBukti.style.display = 'block';
    }
}

// Inisialisasi saat halaman pertama kali dibuka
document.addEventListener('DOMContentLoaded', function() {
    hitungAtm();
    toggleBuktiFields();
});
</script>
@endsection
