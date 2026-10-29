@extends('layouts.app')
@section('title', 'Tambah Isu & Tindak Lanjut (WP-RA-08-E) - SOP 08')

@section('content')
<div class="container-fluid px-3 py-3">
    {{-- Header Modul --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.rekap-isu-tl', ['reportId' => $reportId]) }}" class="text-decoration-none">Rekap Isu & TL</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Isu Baru</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1" style="color: #1e3a8a;">
                <i class="bi bi-plus-circle me-2"></i>Tambah Baris Isu & Tindak Lanjut (WP-RA-08-E)
            </h4>
            <p class="text-muted small mb-0">
                Pencatatan Temuan, Penentuan Flag Signifikan Otomatis, dan Penentuan Jalur Tindak Lanjut (SOP 07, 09, 10)
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="{{ route('pelaporan-bulanan.rekap-isu-tl', ['reportId' => $reportId]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Rekap Isu & TL
            </a>
        </div>
    </div>

    {{-- Tabs Navigasi Modul --}}
    @include('pelaporan_bulanan.tabs')

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            {{-- Info Laporan Target --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <span class="badge bg-primary font-monospace fs-6 px-3 py-1">{{ $kalender->report_id }}</span>
                            <span class="ms-2 fw-bold text-dark">{{ $kalender->nama_unit }} ({{ $kalender->kode_unit }})</span>
                            <span class="text-muted ms-2 small">Periode: {{ \Carbon\Carbon::parse($kalender->periode)->translatedFormat('F Y') }}</span>
                        </div>
                        <div class="small text-muted">
                            <i class="bi bi-person-fill me-1"></i>RA: <strong>{{ $kalender->resident_auditor }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Tambah Isu --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-3 px-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3 text-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-shield-exclamation fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">Formulir Isu & Tindak Lanjut</h5>
                            <small class="text-white-50">Sistem otomatis menghitung Flag Signifikan & Jalur Eskalasi sesuai SOP 08</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pelaporan-bulanan.rekap-isu-tl.store', $reportId) }}">
                        @csrf

                        <div class="alert alert-info border-0 rounded-3 d-flex align-items-start mb-4">
                            <i class="bi bi-info-circle-fill fs-5 text-primary me-3 mt-1"></i>
                            <div class="small">
                                <strong>Formula Otomatis SOP-RA-08 Sheet Rekap_Isu_TL:</strong>
                                <ul class="mb-0 ps-3 mt-1">
                                    <li><strong>Flag Signifikan (YA):</strong> Otomatis aktif jika Jenis Isu = <em>INDIKASI FRAUD</em>, Risk Level = <em>HIGH</em>, Nilai Dampak &ge; Rp 100.000.000, atau Overdue &ge; 30 hari.</li>
                                    <li><strong>Jalur Tindak Lanjut:</strong> Dialirkan ke <em>SOP-RA-09/10</em> untuk Fraud, <em>SOP-RA-10</em> untuk Eskalasi Komite Audit, atau <em>SOP-RA-07</em> untuk Pemantauan Rutin.</li>
                                </ul>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3 pb-2 border-bottom">
                            <i class="bi bi-1-circle-fill me-1"></i> Sumber & Karakteristik Temuan
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Sumber SOP <span class="text-danger">*</span></label>
                                <select name="sumber_sop" class="form-select" required>
                                    <option value="SOP 03">SOP 03 (Daily Register)</option>
                                    <option value="SOP 04">SOP 04 (Offsite Review)</option>
                                    <option value="SOP 05">SOP 05 (Onsite Audit)</option>
                                    <option value="SOP 06">SOP 06 (Risk Trigger Visit)</option>
                                    <option value="SOP 07">SOP 07 (Monitoring TL)</option>
                                    <option value="SOP 08" selected>SOP 08 (Pelaporan Bulanan)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Referensi Sumber KKA/Memo</label>
                                <input type="text" name="referensi_sumber" class="form-control" placeholder="Contoh: KKA-2026-001, MEMO-05">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Area Risiko <span class="text-danger">*</span></label>
                                <select name="area" class="form-select" required>
                                    @foreach($areaList as $ar)
                                        <option value="{{ $ar }}">{{ $ar }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Jenis Isu <span class="text-danger">*</span></label>
                                <select name="jenis_isu" class="form-select" required>
                                    <option value="EXCEPTION" selected>EXCEPTION (Deviasi SOP)</option>
                                    <option value="FINDING">FINDING (Temuan Berulang/Sistemik)</option>
                                    <option value="INDIKASI FRAUD">INDIKASI FRAUD (Potensi Fraud)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Risk Level <span class="text-danger">*</span></label>
                                <select name="risk_level" class="form-select" required>
                                    <option value="HIGH">HIGH (Tinggi)</option>
                                    <option value="MEDIUM" selected>MEDIUM (Sedang)</option>
                                    <option value="LOW">LOW (Rendah)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Temuan Berulang? <span class="text-danger">*</span></label>
                                <select name="berulang" class="form-select" required>
                                    <option value="TIDAK" selected>TIDAK (Pertama Kali)</option>
                                    <option value="YA">YA (Pernah Ditemukan Sebelumnya)</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3 pb-2 border-bottom">
                            <i class="bi bi-2-circle-fill me-1"></i> Uraian Temuan, Sebab, Risiko & Rekomendasi
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">Uraian Isu / Masalah <span class="text-danger">*</span></label>
                                <textarea name="uraian_isu" rows="3" class="form-control" placeholder="Deskripsikan kondisi aktual atau deviasi yang ditemukan..." required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Sebab Awal (Root Cause)</label>
                                <textarea name="sebab_awal" rows="2" class="form-control" placeholder="Penyebab mendasar kelemahan sistem / kelalaian..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Dampak / Risiko Finansial & Operasional</label>
                                <textarea name="dampak_risiko" rows="2" class="form-control" placeholder="Potensi kerugian atau eksposur risiko bagi bank..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">Rekomendasi Tindak Lanjut</label>
                                <textarea name="rekomendasi" rows="2" class="form-control" placeholder="Langkah korektif dan preventif yang disarankan bagi manajemen..."></textarea>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3 pb-2 border-bottom">
                            <i class="bi bi-3-circle-fill me-1"></i> Penanggung Jawab, Target & Nilai Finansial
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">PIC Penanggung Jawab</label>
                                <input type="text" name="pic" class="form-control" placeholder="Contoh: PBO, Pemimpin Cabang">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">Target Penyelesaian TL</label>
                                <input type="date" name="target_tl" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">Status Tindak Lanjut</label>
                                <select name="status_tl" class="form-select">
                                    <option value="OPEN" selected>OPEN (Belum Ditindaklanjuti)</option>
                                    <option value="IN PROGRESS">IN PROGRESS (Sedang Berjalan)</option>
                                    <option value="OVERDUE">OVERDUE (Lewat Target)</option>
                                    <option value="CLOSED">CLOSED (Tuntas)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">Nilai Dampak Finansial (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" step="0.01" name="nilai_dampak" class="form-control" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('pelaporan-bulanan.rekap-isu-tl', ['reportId' => $reportId]) }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                                <i class="bi bi-shield-check me-1"></i> Simpan Isu & Hitung Formula
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
