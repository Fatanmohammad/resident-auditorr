@extends('layouts.app')
@section('title', 'Keputusan Eskalasi Isu Signifikan')

@section('content')
<style>
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        margin-bottom: 1.5rem;
    }
    .form-label {
        font-weight: 600;
        color: #334155;
        font-size: 0.85rem;
    }
    .issue-info-banner {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-left: 4px solid #ef4444;
        border-radius: 10px;
        padding: 1.25rem;
    }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">Pelaporan Bulanan</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.isu-signifikan', ['reportId' => $isu->report_id, 'periode' => \Carbon\Carbon::parse($isu->periode)->format('Y-m')]) }}" class="text-decoration-none">Isu Signifikan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $isRa ? 'Input Bukti' : 'Penetapan Eskalasi' }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0" style="color: #1e3a8a;">
                <i class="bi bi-shield-slash-fill me-2 text-danger"></i>{{ $isRa ? 'Input Bukti Referensi Isu Signifikan' : 'Penetapan Keputusan & Eskalasi Isu Signifikan' }}
            </h4>
            <p class="text-muted small mb-0">
                Matriks Isu Signifikan & Rekomendasi Eskalasi untuk Issue ID: <strong class="text-danger">{{ $isu->issue_id }}</strong>
            </p>
        </div>
        <div>
            <a href="{{ route('pelaporan-bulanan.isu-signifikan', ['reportId' => $isu->report_id, 'periode' => \Carbon\Carbon::parse($isu->periode)->format('Y-m')]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Matriks
            </a>
        </div>
    </div>

    {{-- Error Alerts --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
            <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Terjadi Kesalahan:</h6>
            <ul class="mb-0 small ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('pelaporan-bulanan.isu-signifikan.update', ['id' => $isu->id]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            {{-- Kolom Kiri: Detail Isu & Form Keputusan --}}
            <div class="col-lg-8">
                {{-- Banner Detail Isu dari Rekap Isu --}}
                <div class="issue-info-banner mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-danger px-3 py-1 font-monospace fs-6">
                            {{ $isu->issue_id }}
                        </span>
                        <div class="d-flex gap-1">
                            <span class="badge bg-danger text-white">Risk: {{ $isu->risk_level }}</span>
                            <span class="badge bg-dark text-white">Jalur: {{ $isu->jalur_sistem }}</span>
                        </div>
                    </div>
                    <h6 class="fw-bold text-dark mt-2 mb-1">
                        {{ $isu->area }} &mdash; {{ $isu->jenis_isu }}
                    </h6>
                    <p class="text-secondary mb-3 small" style="white-space: pre-line;">
                        {{ $isu->uraian_isu }}
                    </p>
                    <div class="row g-2 small text-muted border-top pt-2">
                        <div class="col-6 col-md-3">
                            Status TL: <strong class="text-dark">{{ $isu->status_tl }}</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            Aging Overdue: <strong class="text-dark">{{ $isu->aging ? $isu->aging . ' Hari' : '-' }}</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            Berulang: <strong class="text-dark">{{ $isu->berulang }}</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            Unit: <strong class="text-dark">{{ $isu->nama_unit }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Form Keputusan Eskalasi --}}
                <div class="glass-card">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-sliders me-2 text-primary"></i>Keputusan Eskalasi
                    </h6>

                    @if(!$isRa)
                        {{-- Admin / Reviewer Form --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="keputusan_reviewer" class="form-label">Keputusan Reviewer</label>
                                <select name="keputusan_reviewer" id="keputusan_reviewer" class="form-select">
                                    <option value="">-- Pilih Keputusan --</option>
                                    @foreach($opsiKeputusan as $opt)
                                        <option value="{{ $opt }}" {{ old('keputusan_reviewer', $isu->keputusan_reviewer) == $opt ? 'selected' : '' }}>
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="tujuan_eskalasi" class="form-label">Tujuan Eskalasi</label>
                                <select name="tujuan_eskalasi" id="tujuan_eskalasi" class="form-select">
                                    <option value="">-- Pilih Tujuan Eskalasi --</option>
                                    @foreach($opsiTujuan as $opt)
                                        <option value="{{ $opt }}" {{ old('tujuan_eskalasi', $isu->tujuan_eskalasi) == $opt ? 'selected' : '' }}>
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_eskalasi" class="form-label">Tanggal Eskalasi</label>
                            <input type="date" name="tanggal_eskalasi" id="tanggal_eskalasi" class="form-control"
                                   value="{{ old('tanggal_eskalasi', $isu->tanggal_eskalasi ? \Carbon\Carbon::parse($isu->tanggal_eskalasi)->format('Y-m-d') : '') }}">
                        </div>

                        <div class="mb-3">
                            <label for="referensi_bukti" class="form-label">Referensi Bukti / Lampiran</label>
                            <textarea name="referensi_bukti" id="referensi_bukti" rows="3" class="form-control"
                                      placeholder="Contoh: Lampiran KKA 05.D No. 12, Rekon Kas ATM Tgl 15/10/2026...">{{ old('referensi_bukti', $isu->referensi_bukti) }}</textarea>
                            <small class="text-muted" style="font-size:0.75rem;">Dapat diisi oleh Reviewer maupun Resident Auditor.</small>
                        </div>

                        <div class="mb-4">
                            <label for="arahan_tindak_lanjut" class="form-label">Arahan / Tindak Lanjut Komite</label>
                            <textarea name="arahan_tindak_lanjut" id="arahan_tindak_lanjut" rows="4" class="form-control"
                                      placeholder="Instruksi tindak lanjut khusus, arahan koordinasi atau audit investigasi...">{{ old('arahan_tindak_lanjut', $isu->arahan_tindak_lanjut) }}</textarea>
                        </div>
                    @else
                        {{-- Resident Auditor Mode --}}
                        <div class="alert alert-warning border-0 small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Role Resident Auditor: Anda hanya berwenang melengkapi <strong>Referensi Bukti</strong> untuk verifikasi oleh Reviewer SKAI. Keputusan eskalasi ditetapkan oleh SKAI.
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Keputusan Reviewer</label>
                                <div class="p-2 bg-light border rounded fw-bold text-dark">
                                    {{ $isu->keputusan_reviewer ?: 'Belum Ditetapkan SKAI' }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Tujuan Eskalasi</label>
                                <div class="p-2 bg-light border rounded fw-bold text-dark">
                                    {{ $isu->tujuan_eskalasi ?: 'Belum Ditetapkan SKAI' }}
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="referensi_bukti" class="form-label">
                                Referensi Bukti / Lampiran <span class="text-danger">*</span>
                            </label>
                            <textarea name="referensi_bukti" id="referensi_bukti" rows="4" class="form-control"
                                      placeholder="Tuliskan nomor referensi bukti audit, lampiran KKA, nomor memo atau screenshot transaksi..." required>{{ old('referensi_bukti', $isu->referensi_bukti) }}</textarea>
                            <small class="text-muted" style="font-size:0.75rem;">Cantumkan nama dokumen dan lokasi arsip bukti pendukung.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Arahan / Tindak Lanjut</label>
                            <div class="p-2 bg-light border rounded text-secondary small" style="min-height: 50px;">
                                {{ $isu->arahan_tindak_lanjut ?: 'Belum ada arahan tindak lanjut dari SKAI.' }}
                            </div>
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger px-4 py-2 fw-bold shadow-sm">
                            <i class="bi bi-check-circle-fill me-1"></i> Simpan Data Isu Signifikan
                        </button>
                        <a href="{{ route('pelaporan-bulanan.isu-signifikan', ['reportId' => $isu->report_id, 'periode' => \Carbon\Carbon::parse($isu->periode)->format('Y-m')]) }}" class="btn btn-outline-secondary px-3 py-2">
                            Batal
                        </a>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Panduan Regulasi Eskalasi --}}
            <div class="col-lg-4">
                <div class="glass-card mb-3">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-journal-bookmark me-2 text-danger"></i>Kriteria Jalur Sistem
                    </h6>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item px-0 py-2">
                            <strong class="text-dark">Pemeriksaan Fraud / Investigasi:</strong>
                            <p class="text-muted mb-0 small">Isu bertipe Indikasi Fraud atau berlevel High Risk dengan nilai kerugian finansial material.</p>
                        </li>
                        <li class="list-group-item px-0 py-2">
                            <strong class="text-danger">Pemeriksaan Khusus:</strong>
                            <p class="text-muted mb-0 small">Temuan berulang (YA), status overdue berkepanjangan, atau kendala integritas unit.</p>
                        </li>
                        <li class="list-group-item px-0 py-2">
                            <strong class="text-warning">Monitoring Rutin / Ketat:</strong>
                            <p class="text-muted mb-0 small">Finding operasional dengan status Overdue atau risiko medium yang belum terselesaikan.</p>
                        </li>
                        <li class="list-group-item px-0 py-2">
                            <strong class="text-info">MONITOR BULANAN:</strong>
                            <p class="text-muted mb-0 small">Exception administratif dalam proses penyelesaian normal oleh manajemen cabang.</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
