@extends('layouts.app')
@section('title', 'Edit Isu ' . $isu->issue_id)

@section('content')
<div class="container-fluid px-3 py-3">
    {{-- Header Modul --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.rekap-isu-tl', ['reportId' => $isu->report_id]) }}" class="text-decoration-none">Rekap Isu & TL</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit {{ $isu->issue_id }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1" style="color: #1e3a8a;">
                <i class="bi bi-pencil-square me-2"></i>Edit Isu & Tindak Lanjut: {{ $isu->issue_id }}
            </h4>
            <p class="text-muted small mb-0">
                Pembaruan status tindak lanjut, perubahan rekomendasi, risiko, atau eskalasi komite audit.
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="{{ route('pelaporan-bulanan.rekap-isu-tl', ['reportId' => $isu->report_id]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Rekap Isu & TL
            </a>
        </div>
    </div>

    {{-- Tabs Navigasi Modul --}}
    @include('pelaporan_bulanan.tabs')

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            {{-- Status Saat Ini Card --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2 pb-2 border-bottom">
                        <div>
                            <span class="badge bg-primary font-monospace fs-6 px-3 py-1">{{ $isu->issue_id }}</span>
                            <span class="ms-2 fw-bold text-dark">{{ $kalender->nama_unit }} ({{ $kalender->kode_unit }})</span>
                            <span class="text-muted ms-2 small">Report: {{ $isu->report_id }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $isu->flag_signifikan === 'YA' ? 'bg-danger' : 'bg-secondary' }} px-3 py-1">
                                Flag Signifikan: {{ $isu->flag_signifikan }}
                            </span>
                            <span class="badge bg-dark px-3 py-1 font-monospace">
                                Jalur: {{ $isu->jalur_tindak_lanjut }}
                            </span>
                        </div>
                    </div>
                    <div class="row g-2 text-muted small">
                        <div class="col-sm-3"><strong>Sumber:</strong> {{ $isu->sumber_sop }} ({{ $isu->referensi_sumber ?: '-' }})</div>
                        <div class="col-sm-3"><strong>Area:</strong> {{ $isu->area }}</div>
                        <div class="col-sm-3"><strong>Target TL:</strong> {{ $isu->target_tl ? \Carbon\Carbon::parse($isu->target_tl)->format('d/m/Y') : '-' }}</div>
                        <div class="col-sm-3"><strong>Aging Overdue:</strong> {{ $isu->aging_overdue }} Hari</div>
                    </div>
                </div>
            </div>

            {{-- Form Edit Isu --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-sliders me-2 text-primary"></i>Pembaruan Data Isu & Tindak Lanjut
                    </h5>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pelaporan-bulanan.rekap-isu-tl.update', $isu->id) }}">
                        @csrf
                        @method('PUT')

                        <h6 class="fw-bold text-primary mb-3 pb-2 border-bottom">
                            <i class="bi bi-1-circle-fill me-1"></i> Sumber & Karakteristik Temuan
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Sumber SOP <span class="text-danger">*</span></label>
                                <input type="text" name="sumber_sop" class="form-control" value="{{ $isu->sumber_sop }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Referensi Sumber KKA/Memo</label>
                                <input type="text" name="referensi_sumber" class="form-control" value="{{ $isu->referensi_sumber }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Area Risiko <span class="text-danger">*</span></label>
                                <select name="area" class="form-select" required>
                                    @foreach($areaList as $ar)
                                        <option value="{{ $ar }}" {{ $isu->area == $ar ? 'selected' : '' }}>{{ $ar }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Jenis Isu <span class="text-danger">*</span></label>
                                <select name="jenis_isu" class="form-select" required>
                                    <option value="EXCEPTION" {{ $isu->jenis_isu == 'EXCEPTION' ? 'selected' : '' }}>EXCEPTION (Deviasi SOP)</option>
                                    <option value="FINDING" {{ $isu->jenis_isu == 'FINDING' ? 'selected' : '' }}>FINDING (Temuan Berulang/Sistemik)</option>
                                    <option value="INDIKASI FRAUD" {{ $isu->jenis_isu == 'INDIKASI FRAUD' ? 'selected' : '' }}>INDIKASI FRAUD (Potensi Fraud)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Risk Level <span class="text-danger">*</span></label>
                                <select name="risk_level" class="form-select" required>
                                    <option value="HIGH" {{ $isu->risk_level == 'HIGH' ? 'selected' : '' }}>HIGH (Tinggi)</option>
                                    <option value="MEDIUM" {{ $isu->risk_level == 'MEDIUM' ? 'selected' : '' }}>MEDIUM (Sedang)</option>
                                    <option value="LOW" {{ $isu->risk_level == 'LOW' ? 'selected' : '' }}>LOW (Rendah)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Temuan Berulang? <span class="text-danger">*</span></label>
                                <select name="berulang" class="form-select" required>
                                    <option value="TIDAK" {{ $isu->berulang == 'TIDAK' ? 'selected' : '' }}>TIDAK (Pertama Kali)</option>
                                    <option value="YA" {{ $isu->berulang == 'YA' ? 'selected' : '' }}>YA (Pernah Ditemukan Sebelumnya)</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3 pb-2 border-bottom">
                            <i class="bi bi-2-circle-fill me-1"></i> Uraian Temuan, Sebab, Risiko & Rekomendasi
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">Uraian Isu / Masalah <span class="text-danger">*</span></label>
                                <textarea name="uraian_isu" rows="3" class="form-control" required>{{ $isu->uraian_isu }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Sebab Awal (Root Cause)</label>
                                <textarea name="sebab_awal" rows="2" class="form-control">{{ $isu->sebab_awal }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Dampak / Risiko Finansial & Operasional</label>
                                <textarea name="dampak_risiko" rows="2" class="form-control">{{ $isu->dampak_risiko }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">Rekomendasi Tindak Lanjut</label>
                                <textarea name="rekomendasi" rows="2" class="form-control">{{ $isu->rekomendasi }}</textarea>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3 pb-2 border-bottom">
                            <i class="bi bi-3-circle-fill me-1"></i> Penanggung Jawab, Target & Nilai Finansial
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">PIC Penanggung Jawab</label>
                                <input type="text" name="pic" class="form-control" value="{{ $isu->pic }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">Target Penyelesaian TL</label>
                                <input type="date" name="target_tl" class="form-control" value="{{ $isu->target_tl ? \Carbon\Carbon::parse($isu->target_tl)->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">Status Tindak Lanjut</label>
                                <select name="status_tl" class="form-select">
                                    <option value="OPEN" {{ $isu->status_tl == 'OPEN' ? 'selected' : '' }}>OPEN (Belum Ditindaklanjuti)</option>
                                    <option value="IN PROGRESS" {{ $isu->status_tl == 'IN PROGRESS' ? 'selected' : '' }}>IN PROGRESS (Sedang Berjalan)</option>
                                    <option value="OVERDUE" {{ $isu->status_tl == 'OVERDUE' ? 'selected' : '' }}>OVERDUE (Lewat Target)</option>
                                    <option value="CLOSED" {{ $isu->status_tl == 'CLOSED' ? 'selected' : '' }}>CLOSED (Tuntas)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">Nilai Dampak Finansial (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" step="0.01" name="nilai_dampak" class="form-control" value="{{ $isu->nilai_dampak }}">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('pelaporan-bulanan.rekap-isu-tl', ['reportId' => $isu->report_id]) }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan & Hitung Formula
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
