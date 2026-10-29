@extends('layouts.app')
@section('title', 'Edit Kegiatan ' . $kegiatan->source_export_id . ' - SOP 08')

@section('content')
<div class="container-fluid px-3 py-3">
    {{-- Header Modul --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.rekap-kegiatan', ['reportId' => $kegiatan->report_id]) }}" class="text-decoration-none">Rekap Kegiatan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit {{ $kegiatan->source_export_id }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1" style="color: #1e3a8a;">
                <i class="bi bi-pencil-square me-2"></i>Edit Kegiatan: {{ $kegiatan->source_export_id }}
            </h4>
            <p class="text-muted small mb-0">
                Pembaruan data rencana, realisasi, sampel, temuan audit, atau referensi kerja.
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="{{ route('pelaporan-bulanan.rekap-kegiatan', ['reportId' => $kegiatan->report_id]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Rekap Kegiatan
            </a>
        </div>
    </div>

    {{-- Tabs Navigasi Modul --}}
    @include('pelaporan_bulanan.tabs')

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            {{-- Info Kegiatan Target --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <span class="badge bg-primary font-monospace fs-6 px-3 py-1">{{ $kegiatan->source_export_id }}</span>
                            <span class="badge bg-secondary ms-1">{{ $kegiatan->sumber_sop }}</span>
                            <span class="ms-2 fw-bold text-dark">{{ $kalender->nama_unit }} ({{ $kalender->kode_unit }})</span>
                            <span class="text-muted ms-2 small">Periode: {{ \Carbon\Carbon::parse($kalender->periode)->translatedFormat('F Y') }}</span>
                        </div>
                        <div class="small text-muted">
                            <i class="bi bi-file-earmark-text me-1"></i>Report ID: <strong>{{ $kegiatan->report_id }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Edit Kegiatan --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-sliders me-2 text-primary"></i>Pembaruan Data Kegiatan Pengawasan
                    </h5>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pelaporan-bulanan.rekap-kegiatan.update', $kegiatan->id) }}">
                        @csrf
                        @method('PUT')

                        <h6 class="fw-bold text-primary mb-3 pb-2 border-bottom">
                            <i class="bi bi-1-circle-fill me-1"></i> Klasifikasi Kegiatan & Area Risiko
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Sumber SOP <span class="text-danger">*</span></label>
                                <input type="text" name="sumber_sop" class="form-control" value="{{ $kegiatan->sumber_sop }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Jenis Kegiatan <span class="text-danger">*</span></label>
                                <select name="jenis_kegiatan" class="form-select" required>
                                    @foreach($jenisKegiatanList as $jk)
                                        <option value="{{ $jk }}" {{ $kegiatan->jenis_kegiatan == $jk ? 'selected' : '' }}>{{ $jk }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Area Risiko <span class="text-danger">*</span></label>
                                <select name="area" class="form-select" required>
                                    @foreach($areaList as $ar)
                                        <option value="{{ $ar }}" {{ $kegiatan->area == $ar ? 'selected' : '' }}>{{ $ar }}</option>
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
                                <input type="number" name="rencana" class="form-control" value="{{ $kegiatan->rencana }}" min="0">
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <label class="form-label small fw-bold text-dark">Realisasi Pengawasan</label>
                                <input type="number" name="realisasi" class="form-control" value="{{ $kegiatan->realisasi }}" min="0">
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <label class="form-label small fw-bold text-dark">Populasi Data</label>
                                <input type="number" name="populasi" class="form-control" value="{{ $kegiatan->populasi }}" min="0">
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <label class="form-label small fw-bold text-dark">Sampel Terpilih</label>
                                <input type="number" name="sampel_terpilih" class="form-control" value="{{ $kegiatan->sampel_terpilih }}" min="0">
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <label class="form-label small fw-bold text-dark">Sampel Sudah Diuji</label>
                                <input type="number" name="sudah_diuji" class="form-control" value="{{ $kegiatan->sudah_diuji }}" min="0">
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <label class="form-label small fw-bold text-dark">Status Kegiatan</label>
                                <select name="status_kegiatan" class="form-select">
                                    <option value="SELESAI" {{ $kegiatan->status_kegiatan == 'SELESAI' ? 'selected' : '' }}>SELESAI</option>
                                    <option value="DALAM PROSES" {{ $kegiatan->status_kegiatan == 'DALAM PROSES' ? 'selected' : '' }}>DALAM PROSES</option>
                                    <option value="TERTUNDA" {{ $kegiatan->status_kegiatan == 'TERTUNDA' ? 'selected' : '' }}>TERTUNDA</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3 pb-2 border-bottom">
                            <i class="bi bi-3-circle-fill me-1"></i> Hasil Pengujian & Temuan
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-4">
                                <label class="form-label small fw-bold text-warning">Exception (Deviasi Minor)</label>
                                <input type="number" name="exception" class="form-control" value="{{ $kegiatan->exception }}" min="0">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label small fw-bold text-danger">Finding (Temuan Signifikan)</label>
                                <input type="number" name="finding" class="form-control" value="{{ $kegiatan->finding }}" min="0">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label small fw-bold text-danger">High Risk (Risiko Tinggi)</label>
                                <input type="number" name="high_risk" class="form-control" value="{{ $kegiatan->high_risk }}" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Referensi Dokumen WP / KKA</label>
                                <input type="text" name="referensi_wp_kka" class="form-control" value="{{ $kegiatan->referensi_wp_kka }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Catatan / Keterangan Tambahan</label>
                                <input type="text" name="catatan" class="form-control" value="{{ $kegiatan->catatan }}">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('pelaporan-bulanan.rekap-kegiatan', ['reportId' => $kegiatan->report_id]) }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
