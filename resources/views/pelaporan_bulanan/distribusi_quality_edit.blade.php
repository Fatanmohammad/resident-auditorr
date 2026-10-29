@extends('layouts.app')
@section('title', 'Edit Distribusi, Arsip & Penyesuaian Quality Score')

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
    .score-breakdown-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        text-align: center;
    }
    .score-breakdown-val {
        font-size: 1.3rem;
        font-weight: 800;
        margin-top: 0.2rem;
    }
    .preview-calc-card {
        background: linear-gradient(135deg, #78350f 0%, #b45309 100%);
        color: #ffffff;
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: 0 10px 15px -3px rgba(180, 83, 9, 0.2);
    }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">Pelaporan Bulanan</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.distribusi-quality', ['periode' => \Carbon\Carbon::parse($kalender->periode)->format('Y-m')]) }}" class="text-decoration-none">Distribusi & Quality</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Data</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0" style="color: #1e3a8a;">
                <i class="bi bi-award-fill me-2 text-warning"></i>Distribusi, Pengarsipan & Penyesuaian Quality Score
            </h4>
            <p class="text-muted small mb-0">
                Pencatatan distribusi laporan ke stakeholder, lokasi media arsip, dan evaluasi penyesuaian skor mutu reviewer.
            </p>
        </div>
        <div>
            <a href="{{ route('pelaporan-bulanan.distribusi-quality', ['periode' => \Carbon\Carbon::parse($kalender->periode)->format('Y-m')]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    {{-- Error Alerts --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
            <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Terjadi Kesalahan Pengisian:</h6>
            <ul class="mb-0 small ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('pelaporan-bulanan.distribusi-quality.update', ['reportId' => $kalender->report_id]) }}" method="POST">
        @csrf

        <div class="row g-3">
            {{-- Kolom Kiri: Metadata, 7 Komponen Nilai & Form Distribusi --}}
            <div class="col-lg-8">
                {{-- Metadata Info --}}
                <div class="glass-card mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <span class="badge bg-primary px-3 py-2 rounded-pill font-monospace fs-6">
                            <i class="bi bi-file-earmark-text me-1"></i> {{ $kalender->report_id }}
                        </span>
                        <div class="text-end">
                            <span class="badge bg-light text-dark border">
                                Periode: <strong>{{ \Carbon\Carbon::parse($kalender->periode)->translatedFormat('F Y') }}</strong>
                            </span>
                            <span class="badge bg-light text-dark border ms-1">
                                Ketepatan Submit: <strong>{{ $kalender->ketepatan ?: 'BELUM SUBMIT' }}</strong>
                            </span>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-0">Unit Kerja Cabang</label>
                            <div class="fw-bold text-dark">[{{ $kalender->kode_unit }}] {{ $kalender->nama_unit }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-0">Resident Auditor</label>
                            <div class="fw-bold text-dark">{{ $kalender->resident_auditor }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-0">Status Final Laporan</label>
                            <div class="fw-bold text-success">{{ $kalender->status_final ?: 'UNDER REVIEW' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Visual Breakdown 7 Komponen Skor Dasar --}}
                <div class="glass-card mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-pie-chart-fill me-2 text-primary"></i>Breakdown 7 Komponen Skor Dasar
                        </h6>
                        <span class="badge bg-primary-subtle text-primary border">
                            Skor Dasar: <strong>{{ (float)$dist->skor_dasar }}</strong> / 100
                        </span>
                    </div>

                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <div class="score-breakdown-card">
                                <div class="text-muted small fw-bold" style="font-size:0.7rem;">1. Tepat Waktu</div>
                                <div class="score-breakdown-val {{ $dist->skor_tepat_waktu == 20 ? 'text-success' : 'text-danger' }}">
                                    {{ (float)$dist->skor_tepat_waktu }} <span class="fs-6 text-muted">/20</span>
                                </div>
                                <small class="text-muted" style="font-size:0.65rem;">Kalender Laporan</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="score-breakdown-card">
                                <div class="text-muted small fw-bold" style="font-size:0.7rem;">2. Kelengkapan</div>
                                <div class="score-breakdown-val {{ $dist->skor_kelengkapan == 20 ? 'text-success' : 'text-warning' }}">
                                    {{ (float)$dist->skor_kelengkapan }} <span class="fs-6 text-muted">/20</span>
                                </div>
                                <small class="text-muted" style="font-size:0.65rem;">Checklist Laporan</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="score-breakdown-card">
                                <div class="text-muted small fw-bold" style="font-size:0.7rem;">3. Akurasi Data</div>
                                <div class="score-breakdown-val text-primary">
                                    {{ (float)$dist->skor_akurasi }} <span class="fs-6 text-muted">/15</span>
                                </div>
                                <small class="text-muted" style="font-size:0.65rem;">Review Aspek 1</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="score-breakdown-card">
                                <div class="text-muted small fw-bold" style="font-size:0.7rem;">4. Analisis</div>
                                <div class="score-breakdown-val text-primary">
                                    {{ (float)$dist->skor_analisis }} <span class="fs-6 text-muted">/15</span>
                                </div>
                                <small class="text-muted" style="font-size:0.65rem;">Review Aspek 3</small>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="score-breakdown-card">
                                <div class="text-muted small fw-bold" style="font-size:0.7rem;">5. Bukti</div>
                                <div class="score-breakdown-val text-dark">
                                    {{ (float)$dist->skor_bukti }} <span class="fs-6 text-muted">/10</span>
                                </div>
                                <small class="text-muted" style="font-size:0.65rem;">Review Aspek 4</small>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="score-breakdown-card">
                                <div class="text-muted small fw-bold" style="font-size:0.7rem;">6. Tindak Lanjut</div>
                                <div class="score-breakdown-val text-dark">
                                    {{ (float)$dist->skor_tindak_lanjut }} <span class="fs-6 text-muted">/10</span>
                                </div>
                                <small class="text-muted" style="font-size:0.65rem;">Review Aspek 5</small>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="score-breakdown-card">
                                <div class="text-muted small fw-bold" style="font-size:0.7rem;">7. Eskalasi</div>
                                <div class="score-breakdown-val text-dark">
                                    {{ (float)$dist->skor_eskalasi }} <span class="fs-6 text-muted">/10</span>
                                </div>
                                <small class="text-muted" style="font-size:0.65rem;">Review Aspek 6</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Metadata Distribusi & Pengarsipan --}}
                <div class="glass-card mb-3">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-box-seam me-2 text-primary"></i>Metadata Distribusi & Lokasi Arsip
                    </h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="tanggal_distribusi" class="form-label">Tanggal Distribusi Laporan</label>
                            <input type="date" name="tanggal_distribusi" id="tanggal_distribusi" class="form-control"
                                   value="{{ old('tanggal_distribusi', $dist->tanggal_distribusi ? \Carbon\Carbon::parse($dist->tanggal_distribusi)->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="versi" class="form-label">Versi Laporan</label>
                            <input type="text" name="versi" id="versi" class="form-control font-monospace"
                                   value="{{ old('versi', $dist->versi ?: 'v1.0 Final') }}" placeholder="Contoh: v1.0 Final">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="penerima" class="form-label">Daftar Penerima / Stakeholder</label>
                            <input type="text" name="penerima" id="penerima" class="form-control"
                                   value="{{ old('penerima', $dist->penerima ?: 'Pemimpin Cabang, Kadiv SKAI, Direksi Kepatuhan') }}"
                                   placeholder="Contoh: Pemimpin Cabang, Kadiv SKAI, Direksi Kepatuhan">
                        </div>
                        <div class="col-md-6">
                            <label for="media_arsip" class="form-label">Media / Lokasi Arsip Laporan</label>
                            <input type="text" name="media_arsip" id="media_arsip" class="form-control"
                                   value="{{ old('media_arsip', $dist->media_arsip ?: 'Shared Drive SKAI / E-Doc Bank Sulselbar') }}"
                                   placeholder="Contoh: Shared Drive SKAI / E-Doc Bank Sulselbar">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="status_arsip" class="form-label">Status Arsip Laporan <span class="text-danger">*</span></label>
                        <select name="status_arsip" id="status_arsip" class="form-select fw-bold">
                            @foreach($opsiStatusArsip as $opt)
                                <option value="{{ $opt }}" {{ old('status_arsip', $dist->status_arsip) == $opt ? 'selected' : '' }}>
                                    {{ $opt }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Penyesuaian Reviewer & Live Quality Score --}}
            <div class="col-lg-4">
                {{-- Live Quality Score Preview Card --}}
                <div class="preview-calc-card mb-3">
                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="bi bi-speedometer2 me-2 fs-5"></i>Quality Score Final & Kategori Mutu
                    </h6>

                    <div class="mb-3 pb-3 border-bottom border-light border-opacity-25">
                        <div class="small text-uppercase opacity-75">Quality Score Final</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="display-4 fw-bold" id="previewQualityScore">{{ (float)$dist->quality_score }}</span>
                            <span class="fs-5 opacity-75">/ 100</span>
                        </div>
                        <div class="small opacity-75 mt-1" style="font-size: 0.72rem;">
                            = MAX(0, MIN(100, Skor Dasar + Penyesuaian Reviewer))
                        </div>
                    </div>

                    <div>
                        <div class="small text-uppercase opacity-75">Kategori Mutu</div>
                        <div class="fs-4 fw-bold mt-1 text-warning" id="previewKategori">
                            {{ $dist->kategori }}
                        </div>
                        <div class="small opacity-75 mt-1" style="font-size: 0.72rem;">
                            &ge;90 Sangat Baik | &ge;80 Baik | &ge;60 Perlu Perbaikan | &lt;60 Tidak Memadai
                        </div>
                    </div>
                </div>

                {{-- Penyesuaian Reviewer & Catatan KPI --}}
                <div class="glass-card mb-3">
                    <h6 class="fw-bold text-dark mb-2">
                        <i class="bi bi-sliders2 me-2 text-warning"></i>Penyesuaian Reviewer
                    </h6>
                    <p class="text-muted small mb-3" style="font-size:0.75rem;">
                        Penyesuaian nilai positif (+)/negatif (-) atas dinamika kualitas yang tidak terakomodasi dalam rumus dasar.
                    </p>

                    <div class="mb-3">
                        <label for="penyesuaian_reviewer" class="form-label">Nilai Penyesuaian Reviewer</label>
                        <input type="number" step="0.5" name="penyesuaian_reviewer" id="penyesuaian_reviewer" class="form-control"
                               value="{{ old('penyesuaian_reviewer', (float)$dist->penyesuaian_reviewer) }}" oninput="recalcQuality()">
                        <small class="text-muted" style="font-size:0.72rem;">Contoh: -5.0 atau +2.5. Default: 0</small>
                    </div>

                    <div class="mb-2">
                        <label for="catatan_kpi" class="form-label">
                            Catatan KPI Transparansi <span class="text-danger" id="kpiReqStar" style="display:none;">*</span>
                        </label>
                        <textarea name="catatan_kpi" id="catatan_kpi" rows="4" class="form-control"
                                  placeholder="Wajib memberikan penjelasan jika nilai penyesuaian reviewer diisi selain 0...">{{ old('catatan_kpi', $dist->catatan_kpi) }}</textarea>
                        <small class="text-muted" style="font-size:0.72rem;">
                            Transparansi audit atas setiap modifikasi skor mutu.
                        </small>
                    </div>
                </div>

                {{-- Action Card --}}
                <div class="glass-card">
                    <button type="submit" class="btn btn-warning w-100 py-2 fw-bold mb-2 shadow-sm text-dark">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Distribusi & Quality Score
                    </button>
                    <a href="{{ route('pelaporan-bulanan.distribusi-quality', ['periode' => \Carbon\Carbon::parse($kalender->periode)->format('Y-m')]) }}"
                       class="btn btn-outline-secondary w-100 py-2">
                        Batal
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const skorDasar = {{ (float)$dist->skor_dasar }};

    function recalcQuality() {
        const adjInput = document.getElementById('penyesuaian_reviewer');
        const adj = parseFloat(adjInput.value) || 0;
        const finalScore = Math.max(0, Math.min(100, Math.round((skorDasar + adj) * 10) / 10));

        document.getElementById('previewQualityScore').textContent = finalScore;

        let kategori = 'TIDAK MEMADAI';
        if (finalScore >= 90) {
            kategori = 'SANGAT BAIK';
        } else if (finalScore >= 80) {
            kategori = 'BAIK';
        } else if (finalScore >= 60) {
            kategori = 'PERLU PERBAIKAN';
        }

        document.getElementById('previewKategori').textContent = kategori;

        // Toggle required on catatan_kpi
        const catatanEl = document.getElementById('catatan_kpi');
        const reqStar = document.getElementById('kpiReqStar');
        if (adj !== 0) {
            catatanEl.required = true;
            reqStar.style.display = 'inline';
        } else {
            catatanEl.required = false;
            reqStar.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', recalcQuality);
</script>
@endsection
