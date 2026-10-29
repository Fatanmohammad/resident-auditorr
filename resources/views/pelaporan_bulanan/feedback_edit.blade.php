@extends('layouts.app')
@section('title', 'Tindak Lanjut & Verifikasi Feedback - SOP 08')

@section('content')
<div class="container-fluid px-3 py-3">
    {{-- Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">SOP 08</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.feedback') }}" class="text-decoration-none">Feedback (WP-RA-08-M)</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $feedback->feedback_id }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-chat-square-text-fill text-primary"></i> 
                {{ $isRa ? 'Tindak Lanjut Perbaikan Feedback' : 'Evaluasi & Verifikasi Reviewer' }}
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 fw-semibold">{{ $feedback->feedback_id }}</span>
            </h4>
            <p class="text-muted small mb-0 mt-1">
                {{ $isRa ? 'Lengkapi uraian tindakan perbaikan dan bukti penyelesaian untuk diverifikasi oleh Reviewer/Bagian RA.' : 'Verifikasi pemenuhan perbaikan yang dilakukan oleh RA dan tentukan status validasi akhir.' }}
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
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-dark text-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-sliders2 me-2"></i> Register Feedback: {{ $feedback->feedback_id }}
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark font-monospace">{{ $feedback->report_id }}</span>
                        @if($feedback->status === 'CLOSED')
                            <span class="badge bg-success">CLOSED</span>
                        @elseif($feedback->status === 'MENUNGGU VERIFIKASI')
                            <span class="badge bg-info text-dark">MENUNGGU VERIFIKASI</span>
                        @elseif($feedback->status === 'IN PROGRESS')
                            <span class="badge bg-warning text-dark">IN PROGRESS</span>
                        @else
                            <span class="badge bg-danger">OPEN</span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pelaporan-bulanan.feedback.update', $feedback->id) }}">
                        @csrf
                        @method('PUT')

                        {{-- Section 1: Detail Pemberian Feedback (Read-Only untuk RA) --}}
                        <div class="bg-light p-3 rounded-3 mb-4 border">
                            <h6 class="fw-bold text-primary mb-3 d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-chat-left-dots me-1"></i> 1. Catatan Feedback dari Reviewer (Kolom A s.d. F, H, I)</span>
                                <span class="badge bg-secondary-subtle text-secondary border">WP-RA-08-M</span>
                            </h6>

                            @if($isRa)
                            {{-- RA View: Read Only Display --}}
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="small text-muted fw-bold d-block">Report ID</label>
                                    <span class="fw-bold text-primary">{{ $feedback->report_id }}</span>
                                </div>
                                <div class="col-md-3">
                                    <label class="small text-muted fw-bold d-block">Tanggal Feedback</label>
                                    <span>{{ \Carbon\Carbon::parse($feedback->tanggal)->format('d F Y') }}</span>
                                </div>
                                <div class="col-md-3">
                                    <label class="small text-muted fw-bold d-block">Jenis Feedback</label>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $feedback->jenis_feedback }}</span>
                                </div>
                                <div class="col-md-3">
                                    <label class="small text-muted fw-bold d-block">Target Penyelesaian</label>
                                    <span class="text-danger fw-bold">{{ $feedback->target ? \Carbon\Carbon::parse($feedback->target)->format('d F Y') : '-' }}</span>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted fw-bold d-block">Diberikan Kepada</label>
                                    <span>{{ $feedback->diberikan_kepada }}</span>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted fw-bold d-block">PIC Tindak Lanjut</label>
                                    <span>{{ $feedback->pic ?: '-' }}</span>
                                </div>
                                <div class="col-md-12">
                                    <label class="small text-muted fw-bold d-block">Uraian Feedback & Catatan Evaluasi</label>
                                    <div class="p-3 bg-white rounded border text-dark">
                                        {{ $feedback->uraian_feedback }}
                                    </div>
                                </div>
                            </div>
                            @else
                            {{-- Admin View: Editable Fields --}}
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Tanggal Feedback (Kolom C)</label>
                                    <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', $feedback->tanggal) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Jenis Feedback (Kolom E)</label>
                                    <select name="jenis_feedback" class="form-select">
                                        @foreach($jenisList as $j)
                                            <option value="{{ $j }}" {{ old('jenis_feedback', $feedback->jenis_feedback) == $j ? 'selected' : '' }}>{{ $j }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Target Penyelesaian (Kolom I)</label>
                                    <input type="date" name="target" class="form-control" value="{{ old('target', $feedback->target) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">PIC Tindak Lanjut (Kolom H)</label>
                                    <input type="text" name="pic" class="form-control" value="{{ old('pic', $feedback->pic) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Diberikan Kepada (Kolom D)</label>
                                    <input type="text" class="form-control bg-light" value="{{ $feedback->diberikan_kepada }}" readonly>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-muted">Uraian Feedback (Kolom F)</label>
                                    <textarea name="uraian_feedback" rows="3" class="form-control">{{ old('uraian_feedback', $feedback->uraian_feedback) }}</textarea>
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Section 2: Tindak Lanjut Perbaikan oleh RA (Kolom G, K, L, J) --}}
                        <div class="bg-light p-3 rounded-3 mb-4 border">
                            <h6 class="fw-bold text-success mb-3 d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-tools me-1"></i> 2. Tindak Lanjut Perbaikan oleh Resident Auditor (Kolom G, J, K, L)</span>
                                @if($isRa)
                                    <span class="badge bg-success">Input Area Anda</span>
                                @endif
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-dark">
                                        Tindakan Perbaikan (Kolom G) <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="tindakan_perbaikan" rows="4" class="form-control @error('tindakan_perbaikan') is-invalid @enderror" placeholder="Jelaskan langkah-langkah atau tindak lanjut perbaikan yang telah/akan dilakukan..." required>{{ old('tindakan_perbaikan', $feedback->tindakan_perbaikan) }}</textarea>
                                    @error('tindakan_perbaikan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Bukti Perbaikan / Lampiran Dokumen (Kolom K)</label>
                                    <input type="text" name="bukti" class="form-control" value="{{ old('bukti', $feedback->bukti) }}" placeholder="Contoh: Lampiran KKA Teller Hal 4, Memo No. 12/RA/2026...">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Tanggal Selesai (Kolom L)</label>
                                    <input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai', $feedback->tanggal_selesai ?: now()->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Status Tindak Lanjut (Kolom J)</label>
                                    <select name="status" id="status_select" class="form-select fw-bold">
                                        @if($isRa)
                                            <option value="IN PROGRESS" {{ old('status', $feedback->status) == 'IN PROGRESS' ? 'selected' : '' }}>IN PROGRESS</option>
                                            <option value="MENUNGGU VERIFIKASI" {{ old('status', $feedback->status) == 'MENUNGGU VERIFIKASI' ? 'selected' : '' }}>MENUNGGU VERIFIKASI</option>
                                            @if($feedback->status === 'CLOSED')
                                                <option value="CLOSED" selected>CLOSED</option>
                                            @endif
                                        @else
                                            @foreach($statusList as $st)
                                                <option value="{{ $st }}" {{ old('status', $feedback->status) == $st ? 'selected' : '' }}>{{ $st }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Verifikasi Akhir oleh Reviewer (Kolom M & N) --}}
                        <div class="bg-light p-3 rounded-3 mb-4 border">
                            <h6 class="fw-bold text-info mb-3 d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-shield-check me-1"></i> 3. Validasi & Verifikasi Akhir Reviewer (Kolom M s.d. N)</span>
                                @if(!$isRa)
                                    <span class="badge bg-primary">Khusus Reviewer</span>
                                @endif
                            </h6>

                            @if($isRa)
                            {{-- RA: Read-Only for Reviewer Section --}}
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="small text-muted fw-bold d-block">Hasil Verifikasi Reviewer</label>
                                    @if($feedback->verifikasi_reviewer === 'MEMADAI / CLOSED')
                                        <span class="badge bg-success fs-6"><i class="bi bi-check-all me-1"></i>MEMADAI / CLOSED</span>
                                    @elseif($feedback->verifikasi_reviewer === 'PERLU PERBAIKAN ULANG')
                                        <span class="badge bg-danger fs-6"><i class="bi bi-exclamation-triangle me-1"></i>PERLU PERBAIKAN ULANG</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border fs-6">BELUM DIVERIFIKASI</span>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted fw-bold d-block">Catatan Reviewer</label>
                                    <span class="text-dark">{{ $feedback->catatan ?: '-' }}</span>
                                </div>
                            </div>
                            @else
                            {{-- Admin / Reviewer: Full Interactive Verification --}}
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Hasil Verifikasi Reviewer (Kolom M) <span class="text-danger">*</span></label>
                                    <select name="verifikasi_reviewer" id="verifikasi_select" class="form-select fw-bold" onchange="handleVerifikasiChange()">
                                        @foreach($verifikasiList as $vf)
                                            <option value="{{ $vf }}" {{ old('verifikasi_reviewer', $feedback->verifikasi_reviewer) == $vf ? 'selected' : '' }}>
                                                {{ $vf }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text small">Jika dipilih <strong>MEMADAI / CLOSED</strong>, status feedback otomatis dikunci menjadi <strong>CLOSED</strong>.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Catatan Tambahan Reviewer (Kolom N)</label>
                                    <textarea name="catatan" rows="2" class="form-control" placeholder="Tuliskan evaluasi pemenuhan komitmen...">{{ old('catatan', $feedback->catatan) }}</textarea>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <a href="{{ route('pelaporan-bulanan.feedback') }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                                <i class="bi bi-check-lg me-1"></i> 
                                {{ $isRa ? 'Kirimkan Tindak Lanjut ke Reviewer' : 'Simpan Evaluasi & Verifikasi' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$isRa)
<script>
function handleVerifikasiChange() {
    const vf = document.getElementById('verifikasi_select').value;
    const st = document.getElementById('status_select');
    if (vf === 'MEMADAI / CLOSED') {
        st.value = 'CLOSED';
    } else if (vf === 'PERLU PERBAIKAN ULANG') {
        st.value = 'IN PROGRESS';
    }
}
</script>
@endif
@endsection
