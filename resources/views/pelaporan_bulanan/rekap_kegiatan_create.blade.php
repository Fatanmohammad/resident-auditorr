@extends('layouts.app')
@section('title', 'Tambah Kegiatan Pengawasan (WP-RA-08-D) - SOP 08')

@section('content')
<div class="container-fluid px-3 py-3">
    {{-- Header Modul --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.rekap-kegiatan', ['reportId' => $reportId]) }}" class="text-decoration-none">Rekap Kegiatan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Kegiatan Baru</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1" style="color: #1e3a8a;">
                <i class="bi bi-plus-circle me-2"></i>Tambah Baris Kegiatan Pengawasan (WP-RA-08-D)
            </h4>
            <p class="text-muted small mb-0">
                Pencatatan Rencana, Realisasi, Populasi, Sampel, dan Temuan Audit Lintas Modul
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="{{ route('pelaporan-bulanan.rekap-kegiatan', ['reportId' => $reportId]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Rekap Kegiatan
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

            {{-- Form Tambah Kegiatan --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-3 px-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3 text-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-journal-plus fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">Formulir Kegiatan Pengawasan</h5>
                            <small class="text-white-50">Pengisian rincian audit bulanan secara manual</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pelaporan-bulanan.rekap-kegiatan.store', $reportId) }}">
                        @csrf

                        <h6 class="fw-bold text-primary mb-3 pb-2 border-bottom">
                            <i class="bi bi-1-circle-fill me-1"></i> Klasifikasi Kegiatan & Area Risiko
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
                                <label class="form-label small fw-bold text-dark">Jenis Kegiatan <span class="text-danger">*</span></label>
                                <select name="jenis_kegiatan" class="form-select" required>
                                    @foreach($jenisKegiatanList as $jk)
                                        <option value="{{ $jk }}">{{ $jk }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Area Risiko <span class="text-danger">*</span></label>
                                <select name="area" class="form-select" required>
                                    @foreach($areaList as $ar)
                                        <option value="{{ $ar }}">{{ $ar }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3 pb-2 border-bottom">
                            <i class="bi bi-2-circle-fill me-1"></i> Rencana, Realisasi & Pengujian Sampel
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6 col-md-4">
                                <label class="form-label small fw-bold text-dark">Rencana Frekuensi/Objek</label>
                                <input type="number" name="rencana" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <label class="form-label small fw-bold text-dark">Realisasi Pengawasan</label>
                                <input type="number" name="realisasi" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <label class="form-label small fw-bold text-dark">Populasi Data</label>
                                <input type="number" name="populasi" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <label class="form-label small fw-bold text-dark">Sampel Terpilih</label>
                                <input type="number" name="sampel_terpilih" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <label class="form-label small fw-bold text-dark">Sampel Sudah Diuji</label>
                                <input type="number" name="sudah_diuji" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <label class="form-label small fw-bold text-dark">Status Kegiatan</label>
                                <select name="status_kegiatan" class="form-select">
                                    <option value="SELESAI" selected>SELESAI</option>
                                    <option value="DALAM PROSES">DALAM PROSES</option>
                                    <option value="TERTUNDA">TERTUNDA</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3 pb-2 border-bottom">
                            <i class="bi bi-3-circle-fill me-1"></i> Hasil Pengujian & Temuan
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-4">
                                <label class="form-label small fw-bold text-warning">Exception (Deviasi Minor)</label>
                                <input type="number" name="exception" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label small fw-bold text-danger">Finding (Temuan Signifikan)</label>
                                <input type="number" name="finding" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label small fw-bold text-danger">High Risk (Risiko Tinggi)</label>
                                <input type="number" name="high_risk" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Referensi Dokumen WP / KKA</label>
                                <input type="text" name="referensi_wp_kka" class="form-control" placeholder="Contoh: KKA-2026-001, WP-RA-05-A">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Catatan / Keterangan Tambahan</label>
                                <input type="text" name="catatan" class="form-control" placeholder="Keterangan singkat objek pemeriksaan...">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('pelaporan-bulanan.rekap-kegiatan', ['reportId' => $reportId]) }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Simpan Kegiatan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
