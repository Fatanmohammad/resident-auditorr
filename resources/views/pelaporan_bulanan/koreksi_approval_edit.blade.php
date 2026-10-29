@extends('layouts.app')
@section('title', 'Tinjau & Approval Laporan')

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
    .preview-calc-card {
        background: linear-gradient(135deg, #065f46 0%, #047857 100%);
        color: #ffffff;
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: 0 10px 15px -3px rgba(6, 95, 70, 0.2);
    }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">Pelaporan Bulanan</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.koreksi-approval', ['periode' => \Carbon\Carbon::parse($kalender->periode)->format('Y-m')]) }}" class="text-decoration-none">Koreksi & Approval</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tinjau & Approval</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0" style="color: #1e3a8a;">
                <i class="bi bi-patch-check-fill me-2"></i>Tinjau, Permintaan Revisi & Approval Laporan
            </h4>
            <p class="text-muted small mb-0">
                Form penetapan catatan revisi kepada RA, batas koreksi kerja (WORKDAY), verifikasi perbaikan resubmit, dan approval final.
            </p>
        </div>
        <div>
            <a href="{{ route('pelaporan-bulanan.koreksi-approval', ['periode' => \Carbon\Carbon::parse($kalender->periode)->format('Y-m')]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Register
            </a>
        </div>
    </div>

    {{-- Error / Validation Alerts --}}
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

    <form action="{{ route('pelaporan-bulanan.koreksi-approval.update', ['reportId' => $kalender->report_id]) }}" method="POST">
        @csrf

        <div class="row g-3">
            {{-- Kolom Kiri: Metadata & Form Tindakan --}}
            <div class="col-lg-8">
                {{-- Metadata Ringkas --}}
                <div class="glass-card mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <span class="badge bg-primary px-3 py-2 rounded-pill font-monospace fs-6">
                            <i class="bi bi-file-earmark-check me-1"></i> {{ $kalender->report_id }}
                        </span>
                        <div class="text-end">
                            <span class="badge bg-light text-dark border">
                                Periode: <strong>{{ \Carbon\Carbon::parse($kalender->periode)->translatedFormat('F Y') }}</strong>
                            </span>
                            <span class="badge bg-light text-dark border ms-1">
                                Hasil Review: <strong>{{ $koreksi->hasil_review }}</strong>
                            </span>
                            <span class="badge bg-primary-subtle text-primary border ms-1">
                                Jenis Koreksi: <strong>{{ $koreksi->jenis_koreksi }}</strong>
                            </span>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-0">Unit Kerja Cabang</label>
                            <div class="fw-bold text-dark">[{{ $kalender->kode_unit }}] {{ $kalender->nama_unit }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-0">Resident Auditor (RA)</label>
                            <div class="fw-bold text-dark">{{ $kalender->resident_auditor }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-0">Status Review Final</label>
                            <div class="fw-bold text-primary">{{ $review ? $review->status_review_final : $koreksi->hasil_review }}</div>
                        </div>
                    </div>
                </div>

                {{-- Bagian 1: Permintaan Revisi / Catatan Reviewer --}}
                <div class="glass-card mb-3">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-chat-left-dots me-2 text-warning"></i>Bagian 1: Permintaan Koreksi / Revisi Kepada RA
                    </h6>

                    <div class="mb-3">
                        <label for="catatan_reviewer" class="form-label">Catatan Reviewer / Instruksi Koreksi</label>
                        <textarea name="catatan_reviewer" id="catatan_reviewer" rows="3" class="form-control"
                                  placeholder="Tuliskan catatan perbaikan substantif atau administratif yang perlu diperbaiki RA...">{{ old('catatan_reviewer', $koreksi->catatan_reviewer) }}</textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="tanggal_permintaan" class="form-label">Tanggal Permintaan Revisi</label>
                            <input type="date" name="tanggal_permintaan" id="tanggal_permintaan" class="form-control"
                                   value="{{ old('tanggal_permintaan', $koreksi->tanggal_permintaan ? \Carbon\Carbon::parse($koreksi->tanggal_permintaan)->format('Y-m-d') : '') }}">
                            <small class="text-muted" style="font-size:0.75rem;">
                                Mengacu tanggal saat review dikembalikan ke RA.
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Batas Koreksi Otomatis WORKDAY</label>
                            <div class="p-2 bg-light border rounded text-primary fw-bold font-monospace">
                                {{ $koreksi->batas_koreksi ? \Carbon\Carbon::parse($koreksi->batas_koreksi)->format('d F Y') : 'Otomatis dihitung sistem' }}
                            </div>
                            <small class="text-muted" style="font-size:0.75rem;">
                                Rumus WORKDAY: Minor = +1 Hari Kerja | Substansi/Eskalasi = +2 Hari Kerja (melewati libur).
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Bagian 2: Riwayat Resubmit Perbaikan oleh RA --}}
                <div class="glass-card mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-reply-all me-2 text-info"></i>Bagian 2: Hasil Resubmit & Tanggapan RA
                        </h6>
                        <span class="badge bg-light text-muted border">Input oleh RA</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-1">Tanggal Resubmit RA</label>
                            <div class="p-2 bg-light border rounded font-monospace text-dark fw-bold">
                                {{ $koreksi->tanggal_resubmit ? \Carbon\Carbon::parse($koreksi->tanggal_resubmit)->format('d F Y') : 'Belum Resubmit' }}
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-muted small mb-1">Uraian Perbaikan RA</label>
                            <div class="p-2 bg-light border rounded text-secondary small" style="min-height: 42px;">
                                {{ $koreksi->perbaikan_ra ?: 'Belum ada uraian perbaikan yang dikirimkan.' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bagian 3: Keputusan Approval Final & Review Ulang --}}
                <div class="glass-card mb-3">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-award me-2 text-success"></i>Bagian 3: Keputusan Approval & Versi Final
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="tanggal_review_ulang" class="form-label">Tanggal Review Ulang</label>
                            <input type="date" name="tanggal_review_ulang" id="tanggal_review_ulang" class="form-control"
                                   value="{{ old('tanggal_review_ulang', $koreksi->tanggal_review_ulang ? \Carbon\Carbon::parse($koreksi->tanggal_review_ulang)->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="keputusan_approval" class="form-label">Keputusan Approval <span class="text-danger">*</span></label>
                            <select name="keputusan_approval" id="keputusan_approval" class="form-select fw-bold" onchange="updateLiveStatus()">
                                <option value="">-- Pilih Keputusan Approval --</option>
                                @foreach($opsiKeputusan as $opt)
                                    <option value="{{ $opt }}" {{ old('keputusan_approval', $koreksi->keputusan_approval) == $opt ? 'selected' : '' }}>
                                        {{ $opt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="approved_by" class="form-label">Approved By</label>
                            <input type="text" name="approved_by" id="approved_by" class="form-control"
                                   value="{{ old('approved_by', $koreksi->approved_by ?: (auth()->user()->name ?? 'Approver SKAI')) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="tanggal_approval" class="form-label">Tanggal Approval</label>
                            <input type="date" name="tanggal_approval" id="tanggal_approval" class="form-control"
                                   value="{{ old('tanggal_approval', $koreksi->tanggal_approval ? \Carbon\Carbon::parse($koreksi->tanggal_approval)->format('Y-m-d') : date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="versi_final" class="form-label">Versi Final</label>
                            <input type="text" name="versi_final" id="versi_final" class="form-control font-monospace"
                                   value="{{ old('versi_final', $koreksi->versi_final ?: 'v1.0 Final') }}" placeholder="Contoh: v1.0 Final">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Preview Status Final & Tindakan --}}
            <div class="col-lg-4">
                {{-- Live Status Card --}}
                <div class="preview-calc-card mb-3">
                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="bi bi-shield-check me-2 fs-5"></i>Status Final & Keterlambatan
                    </h6>

                    <div class="mb-3 pb-3 border-bottom border-light border-opacity-25">
                        <div class="small text-uppercase opacity-75">Status Final Laporan</div>
                        <div class="fs-4 fw-bold mt-1 text-warning" id="liveStatusFinal">
                            {{ $koreksi->status_final }}
                        </div>
                        <div class="small opacity-75 mt-1" style="font-size: 0.72rem;">
                            Rumus evaluasi bersyarat sistem
                        </div>
                    </div>

                    <div>
                        <div class="small text-uppercase opacity-75">Hari Terlambat Koreksi</div>
                        <div class="d-flex align-items-baseline gap-2 mt-1">
                            <span class="fs-3 fw-bold">{{ $koreksi->hari_terlambat_koreksi }}</span>
                            <span class="fs-6 opacity-75">Hari Kerja Lewat</span>
                        </div>
                        <div class="small opacity-75 mt-1" style="font-size: 0.72rem;">
                            = MAX(Tanggal Resubmit - Batas Koreksi, 0)
                        </div>
                    </div>
                </div>

                {{-- Action Card --}}
                <div class="glass-card">
                    <div class="alert alert-info border-0 rounded-3 small p-2 mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Jika keputusan diubah ke <strong>APPROVED/FINAL</strong>, laporan otomatis masuk konsolidasi (bila lengkap 100%) dan pengeditan oleh RA akan terkunci!
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold mb-2 shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Penetapan Approval
                    </button>
                    <a href="{{ route('pelaporan-bulanan.koreksi-approval', ['periode' => \Carbon\Carbon::parse($kalender->periode)->format('Y-m')]) }}"
                       class="btn btn-outline-secondary w-100 py-2">
                        Batal
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const hasResubmit = {{ $koreksi->tanggal_resubmit ? 'true' : 'false' }};
    const hasilReview = "{{ $koreksi->hasil_review }}";

    function updateLiveStatus() {
        const keputusan = document.getElementById('keputusan_approval').value;
        const liveEl = document.getElementById('liveStatusFinal');

        // IF(J6="APPROVED/FINAL", "APPROVED/FINAL",
        // IF(J6="REJECTED FOR RESUBMISSION", "REJECTED FOR RESUBMISSION",
        // IF(AND(G6<>"", B6<>"MEMADAI"), "REVISED",
        // IF(B6="MEMADAI", "UNDER REVIEW", "REVISION REQUIRED"))))
        if (keputusan === 'APPROVED/FINAL') {
            liveEl.textContent = 'APPROVED/FINAL';
        } else if (keputusan === 'REJECTED FOR RESUBMISSION') {
            liveEl.textContent = 'REJECTED FOR RESUBMISSION';
        } else if (hasResubmit && hasilReview !== 'MEMADAI') {
            liveEl.textContent = 'REVISED';
        } else if (hasilReview === 'MEMADAI') {
            liveEl.textContent = 'UNDER REVIEW';
        } else {
            liveEl.textContent = 'REVISION REQUIRED';
        }
    }

    document.addEventListener('DOMContentLoaded', updateLiveStatus);
</script>
@endsection
