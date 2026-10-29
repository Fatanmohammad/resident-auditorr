@extends('layouts.app')
@section('title', 'Evaluasi Review Laporan Bulanan')

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
    .kpi-stat-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.9rem 1rem;
        text-align: center;
    }
    .kpi-stat-title {
        font-size: 0.72rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .kpi-stat-value {
        font-size: 1.4rem;
        font-weight: 800;
        margin-top: 0.25rem;
    }
    .aspect-card {
        background: #fdfdfd;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem 1.15rem;
        transition: all 0.2s ease;
    }
    .aspect-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
    }
    .form-label {
        font-weight: 600;
        color: #334155;
        font-size: 0.85rem;
    }
    .preview-calc-card {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        color: #ffffff;
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: 0 10px 15px -3px rgba(30, 58, 138, 0.2);
    }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">Pelaporan Bulanan</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.review-laporan', ['periode' => \Carbon\Carbon::parse($kalender->periode)->format('Y-m')]) }}" class="text-decoration-none">Review Laporan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Evaluasi KKA</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0" style="color: #1e3a8a;">
                <i class="bi bi-pencil-square me-2"></i>Form Evaluasi KKA Review Laporan Bulanan
            </h4>
            <p class="text-muted small mb-0">
                Lakukan pengujian 6 aspek substansi laporan untuk menetapkan skor review dan rekomendasi tindak lanjut review.
            </p>
        </div>
        <div>
            <a href="{{ route('pelaporan-bulanan.review-laporan', ['periode' => \Carbon\Carbon::parse($kalender->periode)->format('Y-m')]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Review
            </a>
        </div>
    </div>

    {{-- Error / Validation Alerts --}}
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

    <form action="{{ route('pelaporan-bulanan.review-laporan.update', ['reportId' => $kalender->report_id]) }}" method="POST" id="formReview">
        @csrf

        <div class="row g-3">
            {{-- Kolom Kiri: Metadata & 6 Aspek Substansi --}}
            <div class="col-lg-8">
                {{-- Info Metadata Laporan --}}
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
                                Batas Review: <strong>{{ $kalender->target_review ? \Carbon\Carbon::parse($kalender->target_review)->format('d/m/Y') : '-' }}</strong>
                            </span>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-0">Unit Kerja Cabang</label>
                            <div class="fw-bold text-dark">[{{ $kalender->kode_unit }}] {{ $kalender->nama_unit }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-0">Resident Auditor (RA)</label>
                            <div class="fw-bold text-dark">{{ $kalender->resident_auditor }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-0">Tanggal Submit RA</label>
                            <div class="fw-bold text-dark">
                                {{ $kalender->tanggal_submit ? \Carbon\Carbon::parse($kalender->tanggal_submit)->format('d/m/Y') : 'Belum Submit' }}
                            </div>
                        </div>
                    </div>

                    {{-- 5 Angka Pokok Laporan --}}
                    <label class="form-label fw-bold text-uppercase small text-secondary mb-2">
                        <i class="bi bi-calculator me-1"></i> 5 Angka Pokok Laporan
                    </label>
                    <div class="row g-2">
                        <div class="col">
                            <div class="kpi-stat-box">
                                <div class="kpi-stat-title">Jumlah Kegiatan</div>
                                <div class="kpi-stat-value text-primary">{{ $review->jumlah_kegiatan }}</div>
                                <small class="text-muted" style="font-size:0.7rem;">COUNTIF Kegiatan</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="kpi-stat-box">
                                <div class="kpi-stat-title">Jumlah Isu</div>
                                <div class="kpi-stat-value text-dark">{{ $review->jumlah_isu }}</div>
                                <small class="text-muted" style="font-size:0.7rem;">COUNTIF Isu</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="kpi-stat-box">
                                <div class="kpi-stat-title">High Risk</div>
                                <div class="kpi-stat-value {{ $review->high_risk > 0 ? 'text-danger' : 'text-success' }}">{{ $review->high_risk }}</div>
                                <small class="text-muted" style="font-size:0.7rem;">Risiko Tinggi</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="kpi-stat-box">
                                <div class="kpi-stat-title">TL Overdue</div>
                                <div class="kpi-stat-value {{ $review->tl_overdue > 0 ? 'text-warning' : 'text-success' }}">{{ $review->tl_overdue }}</div>
                                <small class="text-muted" style="font-size:0.7rem;">Lewat Jatuh Tempo</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="kpi-stat-box">
                                <div class="kpi-stat-title">% Kelengkapan</div>
                                <div class="kpi-stat-value {{ $review->persen_kelengkapan >= 100 ? 'text-success' : 'text-warning' }}">
                                    {{ number_format($review->persen_kelengkapan, 0) }}%
                                </div>
                                <small class="text-muted" style="font-size:0.7rem;">Checklist Lampiran</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Reviewer & Tanggal Review --}}
                <div class="glass-card mb-3">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-person-badge me-2 text-primary"></i>Informasi Petugas Reviewer
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="reviewer" class="form-label">Nama Reviewer <span class="text-danger">*</span></label>
                            <input type="text" name="reviewer" id="reviewer" class="form-control"
                                   value="{{ old('reviewer', $review->reviewer ?: (auth()->user()->name ?? 'Reviewer SKAI')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="tanggal_review" class="form-label">Tanggal Review <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_review" id="tanggal_review" class="form-control"
                                   value="{{ old('tanggal_review', $review->tanggal_review ? \Carbon\Carbon::parse($review->tanggal_review)->format('Y-m-d') : date('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>

                {{-- Penilaian Substansi oleh Reviewer --}}
                <div class="glass-card mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-check2-square me-2 text-primary"></i>Penilaian 6 Aspek Substansi
                        </h6>
                        <span class="text-muted small">Dropdown Standar: MEMADAI, PERLU PERBAIKAN, TIDAK MEMADAI</span>
                    </div>

                    <div class="row g-3">
                        {{-- Akurasi Data --}}
                        <div class="col-md-6">
                            <div class="aspect-card">
                                <label for="akurasi_data" class="form-label d-flex justify-content-between align-items-center">
                                    <span><strong>1. Akurasi Data</strong></span>
                                    <span class="badge bg-light text-secondary border">Bobot 15% (Quality)</span>
                                </label>
                                <p class="text-muted small mb-2" style="font-size:0.75rem;">
                                    Kesesuaian data angka pada narasi dengan bukti transaksi dan KKA pengawasan.
                                </p>
                                <select name="akurasi_data" id="akurasi_data" class="form-select aspect-select" required onchange="recalcPreview()">
                                    @foreach($opsiPenilaian as $opt)
                                        <option value="{{ $opt }}" {{ old('akurasi_data', $review->akurasi_data) == $opt ? 'selected' : '' }}>
                                             {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Konsistensi --}}
                        <div class="col-md-6">
                            <div class="aspect-card">
                                <label for="konsistensi" class="form-label d-flex justify-content-between align-items-center">
                                    <span><strong>2. Konsistensi</strong></span>
                                    <span class="badge bg-light text-secondary border">Substansi Review</span>
                                </label>
                                <p class="text-muted small mb-2" style="font-size:0.75rem;">
                                    Konsistensi temuan antar sheet, perbandingan saldo/transaksi antar waktu.
                                </p>
                                <select name="konsistensi" id="konsistensi" class="form-select aspect-select" required onchange="recalcPreview()">
                                    @foreach($opsiPenilaian as $opt)
                                        <option value="{{ $opt }}" {{ old('konsistensi', $review->konsistensi) == $opt ? 'selected' : '' }}>
                                             {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Kualitas Analisis --}}
                        <div class="col-md-6">
                            <div class="aspect-card">
                                <label for="kualitas_analisis" class="form-label d-flex justify-content-between align-items-center">
                                    <span><strong>3. Kualitas Analisis</strong></span>
                                    <span class="badge bg-light text-secondary border">Bobot 15% (Quality)</span>
                                </label>
                                <p class="text-muted small mb-2" style="font-size:0.75rem;">
                                    Ketajaman analisis root cause (5-Why), analisis risiko inheren dan tren risiko.
                                </p>
                                <select name="kualitas_analisis" id="kualitas_analisis" class="form-select aspect-select" required onchange="recalcPreview()">
                                    @foreach($opsiPenilaian as $opt)
                                        <option value="{{ $opt }}" {{ old('kualitas_analisis', $review->kualitas_analisis) == $opt ? 'selected' : '' }}>
                                             {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Bukti Pendukung --}}
                        <div class="col-md-6">
                            <div class="aspect-card">
                                <label for="bukti_pendukung" class="form-label d-flex justify-content-between align-items-center">
                                    <span><strong>4. Bukti Pendukung</strong></span>
                                    <span class="badge bg-light text-secondary border">Bobot 10% (Quality)</span>
                                </label>
                                <p class="text-muted small mb-2" style="font-size:0.75rem;">
                                    Kelengkapan dan validitas bukti audit (KKA fisik, screenshot CBS, dokumen konfirmasi).
                                </p>
                                <select name="bukti_pendukung" id="bukti_pendukung" class="form-select aspect-select" required onchange="recalcPreview()">
                                    @foreach($opsiPenilaian as $opt)
                                        <option value="{{ $opt }}" {{ old('bukti_pendukung', $review->bukti_pendukung) == $opt ? 'selected' : '' }}>
                                             {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Tindak Lanjut --}}
                        <div class="col-md-6">
                            <div class="aspect-card">
                                <label for="tindak_lanjut" class="form-label d-flex justify-content-between align-items-center">
                                    <span><strong>5. Tindak Lanjut</strong></span>
                                    <span class="badge bg-light text-secondary border">Bobot 10% (Quality)</span>
                                </label>
                                <p class="text-muted small mb-2" style="font-size:0.75rem;">
                                    Kejelasan rekomendasi, kesepakatan target komitmen PIC, dan status tindak lanjut temuan.
                                </p>
                                <select name="tindak_lanjut" id="tindak_lanjut" class="form-select aspect-select" required onchange="recalcPreview()">
                                    @foreach($opsiPenilaian as $opt)
                                        <option value="{{ $opt }}" {{ old('tindak_lanjut', $review->tindak_lanjut) == $opt ? 'selected' : '' }}>
                                             {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Eskalasi --}}
                        <div class="col-md-6">
                            <div class="aspect-card">
                                <label for="eskalasi" class="form-label d-flex justify-content-between align-items-center">
                                    <span><strong>6. Eskalasi</strong></span>
                                    <span class="badge bg-light text-secondary border">Bobot 10% (Quality)</span>
                                </label>
                                <p class="text-muted small mb-2" style="font-size:0.75rem;">
                                    Keberanian dan ketepatan eskalasi bila ada temuan High Risk / Fraud / Overdue berulang.
                                </p>
                                <select name="eskalasi" id="eskalasi" class="form-select aspect-select" required onchange="recalcPreview()">
                                    @foreach($opsiPenilaian as $opt)
                                        <option value="{{ $opt }}" {{ old('eskalasi', $review->eskalasi) == $opt ? 'selected' : '' }}>
                                             {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Live Calculation & Override --}}
            <div class="col-lg-4">
                {{-- Live Calculation Card --}}
                <div class="preview-calc-card mb-3">
                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="bi bi-cpu me-2 fs-5"></i>Hasil & Skor Evaluasi Review
                    </h6>

                    <div class="mb-3 pb-3 border-bottom border-light border-opacity-25">
                        <div class="small text-uppercase opacity-75">Skor Review</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="display-5 fw-bold" id="previewSkorReview">{{ $review->skor_review }}</span>
                            <span class="fs-5 opacity-75">/ 100</span>
                        </div>
                        <div class="small opacity-75 mt-1" style="font-size: 0.72rem;">
                            Rumus: (Kelengkapan &times; 40) + ((Memadai / 6) &times; 60)
                        </div>
                    </div>

                    <div class="mb-3 pb-3 border-bottom border-light border-opacity-25">
                        <div class="small text-uppercase opacity-75">Hasil Otomatis</div>
                        <div class="fs-5 fw-bold mt-1" id="previewHasilOtomatis">
                            {{ $review->hasil_otomatis }}
                        </div>
                        <div class="small opacity-75 mt-1" style="font-size: 0.72rem;" id="previewHasilReason">
                            Evaluasi logika berjenjang
                        </div>
                    </div>

                    <div>
                        <div class="small text-uppercase opacity-75">Status Review Final</div>
                        <div class="fs-4 fw-bold mt-1 text-warning" id="previewStatusFinal">
                            {{ $review->status_review_final }}
                        </div>
                        <div class="small opacity-75 mt-1" style="font-size: 0.72rem;">
                            = IF(Override &lt;&gt; "", Override, Hasil Otomatis)
                        </div>
                    </div>
                </div>

                {{-- Override Section --}}
                <div class="glass-card mb-3">
                    <h6 class="fw-bold text-dark mb-2">
                        <i class="bi bi-shield-exclamation me-2 text-warning"></i>Override Reviewer
                    </h6>
                    <p class="text-muted small mb-3" style="font-size: 0.78rem;">
                        Gunakan kolom ini hanya jika terdapat pertimbangan profesional reviewer di luar hasil rumus otomatis.
                    </p>

                    <div class="mb-3">
                        <label for="override" class="form-label">Keputusan Override (Opsional)</label>
                        <select name="override" id="override" class="form-select" onchange="toggleOverrideAlasan()">
                            <option value="">-- Tanpa Override (Gunakan Hasil Otomatis) --</option>
                            @foreach($opsiOverride as $opt)
                                <option value="{{ $opt }}" {{ old('override', $review->override) == $opt ? 'selected' : '' }}>
                                    {{ $opt }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2" id="overrideReasonGroup">
                        <label for="alasan_override" class="form-label">
                            Alasan Override <span class="text-danger" id="reqStar" style="display:none;">*</span>
                        </label>
                        <textarea name="alasan_override" id="alasan_override" rows="3" class="form-control"
                                  placeholder="Wajib diisi jika memilih nilai Override...">{{ old('alasan_override', $review->alasan_override) }}</textarea>
                        <small class="text-muted" style="font-size: 0.72rem;">
                            Kontrol transparansi: wajib mencatat argumentasi dan bukti pendukung override.
                        </small>
                    </div>
                </div>

                {{-- Action Card --}}
                <div class="glass-card">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mb-2 shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Evaluasi Review
                    </button>
                    <a href="{{ route('pelaporan-bulanan.review-laporan', ['periode' => \Carbon\Carbon::parse($kalender->periode)->format('Y-m')]) }}"
                       class="btn btn-outline-secondary w-100 py-2">
                        Batal
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const persenKelengkapan = {{ (float)$review->persen_kelengkapan / 100.0 }};
    const highRiskCount = {{ (int)$review->high_risk }};

    function recalcPreview() {
        const aspects = [
            document.getElementById('akurasi_data').value,
            document.getElementById('konsistensi').value,
            document.getElementById('kualitas_analisis').value,
            document.getElementById('bukti_pendukung').value,
            document.getElementById('tindak_lanjut').value,
            document.getElementById('eskalasi').value
        ];

        let countMemadai = 0;
        let countPerluPerbaikan = 0;
        let countTidakMemadai = 0;

        aspects.forEach(val => {
            if (val === 'MEMADAI') countMemadai++;
            else if (val === 'PERLU PERBAIKAN') countPerluPerbaikan++;
            else if (val === 'TIDAK MEMADAI') countTidakMemadai++;
        });

        // 1. Rumus Skor Review: ROUND((H6 * 40) + ((COUNTIF(I6:N6, "MEMADAI") / 6) * 60))
        const skor = Math.round((persenKelengkapan * 40) + ((countMemadai / 6) * 60));
        document.getElementById('previewSkorReview').textContent = skor;

        // 2. Rumus Hasil Otomatis:
        // IF(H6<1, "KOREKSI SUBSTANSI",
        // IF(COUNTIF(I6:N6,"TIDAK MEMADAI")>0, "KOREKSI SUBSTANSI",
        // IF(AND(F6>0, N6<>"MEMADAI"), "PERLU ESKALASI",
        // IF(COUNTIF(I6:N6,"PERLU PERBAIKAN")>0, "KOREKSI MINOR", "MEMADAI"))))
        const eskalasiVal = document.getElementById('eskalasi').value;
        let hasilOtomatis = 'MEMADAI';
        let reason = 'Semua aspek memadai dan kelengkapan 100%.';

        if (persenKelengkapan < 1) {
            hasilOtomatis = 'KOREKSI SUBSTANSI';
            reason = 'Kelengkapan lampiran belum 100%.';
        } else if (countTidakMemadai > 0) {
            hasilOtomatis = 'KOREKSI SUBSTANSI';
            reason = 'Terdapat ' + countTidakMemadai + ' aspek bernilai TIDAK MEMADAI.';
        } else if (highRiskCount > 0 && eskalasiVal !== 'MEMADAI') {
            hasilOtomatis = 'PERLU ESKALASI';
            reason = 'Terdapat isu HIGH RISK dan aspek Eskalasi belum MEMADAI.';
        } else if (countPerluPerbaikan > 0) {
            hasilOtomatis = 'KOREKSI MINOR';
            reason = 'Terdapat ' + countPerluPerbaikan + ' aspek PERLU PERBAIKAN.';
        }

        document.getElementById('previewHasilOtomatis').textContent = hasilOtomatis;
        document.getElementById('previewHasilReason').textContent = reason;

        // 3. Status Review Final: IF(Q6<>"", Q6, P6)
        const overrideVal = document.getElementById('override').value;
        const finalStatus = overrideVal ? overrideVal : hasilOtomatis;
        document.getElementById('previewStatusFinal').textContent = finalStatus;
    }

    function toggleOverrideAlasan() {
        const overrideVal = document.getElementById('override').value;
        const alasanEl = document.getElementById('alasan_override');
        const reqStar = document.getElementById('reqStar');

        if (overrideVal) {
            alasanEl.required = true;
            reqStar.style.display = 'inline';
        } else {
            alasanEl.required = false;
            reqStar.style.display = 'none';
        }

        recalcPreview();
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleOverrideAlasan();
        recalcPreview();
    });
</script>
@endsection
