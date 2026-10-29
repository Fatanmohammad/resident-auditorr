@extends('layouts.app')
@section('title', 'Buat Feedback Konsolidasi')

@section('content')
<div class="container-fluid px-3 py-3">
    {{-- Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">Pelaporan Bulanan</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.feedback') }}" class="text-decoration-none">Feedback Konsolidasi</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Buat Baru</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-chat-square-quote-fill text-primary"></i> Buat Feedback Konsolidasi & Perbaikan
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Formulir umpan balik manajemen/reviewer kepada Resident Auditor untuk penyempurnaan kualitas laporan dan pengawasan.
            </p>
        </div>
        <div>
            <a href="{{ route('pelaporan-bulanan.feedback') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Register Feedback
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger shadow-sm">
            <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terdapat Kesalahan Input:</h6>
            <ul class="mb-0 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-primary text-white py-3 px-4">
                    <h5 class="card-title mb-0 fw-bold d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-pencil-square me-2"></i> Formulir Feedback Konsolidasi Bagian RA</span>
                        <span class="badge bg-white text-primary font-monospace">{{ $autoFeedbackId }}</span>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pelaporan-bulanan.feedback.store') }}" id="formFeedback">
                        @csrf

                        {{-- Section 1: Identitas Laporan --}}
                        <div class="bg-light p-3 rounded-3 mb-3 border">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-file-earmark-text me-1"></i> 1. Rujukan Laporan & Penerima Feedback
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Pilih Report ID <span class="text-danger">*</span></label>
                                    <select name="report_id" id="report_id" class="form-select @error('report_id') is-invalid @enderror" required onchange="handleReportChange()">
                                        <option value="">-- Pilih Laporan Bulanan --</option>
                                        @foreach($availableReports as $rep)
                                            <option value="{{ $rep->report_id }}" 
                                                    data-ra="{{ $rep->resident_auditor }}" 
                                                    data-unit="{{ $rep->nama_unit }}"
                                                    data-kode="{{ $rep->kode_unit }}"
                                                    {{ old('report_id') == $rep->report_id ? 'selected' : '' }}>
                                                {{ $rep->report_id }} - {{ $rep->nama_unit }} ({{ \Carbon\Carbon::parse($rep->periode)->format('M Y') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('report_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Tanggal Feedback <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                                    @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-muted">Diberikan Kepada (Otomatis)</label>
                                    <input type="text" id="diberikan_kepada_display" class="form-control bg-white text-dark fw-bold" readonly placeholder="Otomatis terisi saat Report ID dipilih">
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Substansi Feedback --}}
                        <div class="bg-light p-3 rounded-3 mb-3 border">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-chat-left-dots me-1"></i> 2. Substansi & Arahan Perbaikan
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Jenis Feedback <span class="text-danger">*</span></label>
                                    <select name="jenis_feedback" class="form-select @error('jenis_feedback') is-invalid @enderror" required>
                                        <option value="">-- Pilih Kategori Feedback --</option>
                                        @foreach($jenisList as $j)
                                            <option value="{{ $j }}" {{ old('jenis_feedback') == $j ? 'selected' : '' }}>{{ $j }}</option>
                                        @endforeach
                                    </select>
                                    @error('jenis_feedback') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">PIC Tindak Lanjut</label>
                                    <input type="text" name="pic" id="pic_field" class="form-control @error('pic') is-invalid @enderror" value="{{ old('pic') }}" placeholder="Default: Resident Auditor terkait">
                                    @error('pic') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-muted">Uraian Feedback & Catatan Evaluasi <span class="text-danger">*</span></label>
                                    <textarea name="uraian_feedback" rows="4" class="form-control @error('uraian_feedback') is-invalid @enderror" placeholder="Tuliskan catatan detail review mengenai kualitas laporan, kelengkapan kertas kerja, metodologi sampling, atau monitoring tindak lanjut..." required>{{ old('uraian_feedback') }}</textarea>
                                    @error('uraian_feedback') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Target Penyelesaian Perbaikan</label>
                                    <input type="date" name="target" class="form-control @error('target') is-invalid @enderror" value="{{ old('target', now()->addDays(7)->format('Y-m-d')) }}">
                                    @error('target') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Catatan Tambahan Reviewer</label>
                                    <input type="text" name="catatan" class="form-control" value="{{ old('catatan') }}" placeholder="Catatan internal Bagian RA...">
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Status & Verifikasi Initial --}}
                        <div class="alert alert-info py-2 px-3 small mb-4">
                            <i class="bi bi-info-circle me-1"></i> Feedback baru otomatis berstatus <strong>OPEN</strong> dengan Verifikasi Reviewer <strong>BELUM DIVERIFIKASI</strong>. Setelah disimpan, Resident Auditor penerima akan dapat menginput Tindakan Perbaikan dan Bukti.
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <a href="{{ route('pelaporan-bulanan.feedback') }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                                <i class="bi bi-send-check me-1"></i> Simpan & Kirim Feedback ke RA
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function handleReportChange() {
    const sel = document.getElementById('report_id');
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
        const ra = opt.getAttribute('data-ra') || '';
        const unit = opt.getAttribute('data-unit') || '';
        document.getElementById('diberikan_kepada_display').value = ra + ' (' + unit + ')';
        if (!document.getElementById('pic_field').value) {
            document.getElementById('pic_field').value = ra;
        }
    } else {
        document.getElementById('diberikan_kepada_display').value = '';
    }
}
document.addEventListener('DOMContentLoaded', function() {
    handleReportChange();
});
</script>
@endsection
