@extends('layouts.app')

@section('content')

<div style="margin-bottom: 1.5rem;">
    <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 0.3rem;">Upload Data CBS Onsite</h4>
    <p style="color: #64748b; font-size: 0.9rem; margin: 0;">
        Kunjungan: <strong>{{ $visit->kode_unit }} — {{ $visit->nama_unit }}</strong> &nbsp;|&nbsp;
        {{ \Carbon\Carbon::parse($visit->tanggal_mulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($visit->tanggal_selesai)->format('d M Y') }}
    </p>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Form Upload -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="{{ route('onsite.upload.process', $visit->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">
                            Tujuan Pengiriman Data
                        </label>
                        <div class="p-2.5 rounded bg-light border text-primary fw-bold small d-flex align-items-center">
                            <i class="bi bi-person-badge-fill me-2 fs-6"></i> Tim SKAI / Admin Pusat
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">
                            Periode Data <span class="text-danger">*</span>
                        </label>
                        <input type="month" name="periode" required class="form-control form-control-sm"
                               value="{{ old('periode', $visit->periode ?? date('Y-m')) }}">
                        <div class="form-text small text-muted">
                            Tentukan bulan dan tahun periode data transaksi CBS yang diuji selama onsite visit.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark small mb-1">
                            File CBS Populasi Teller <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="file_cbs" accept=".csv,.txt" required
                            class="form-control form-control-sm">
                        <div class="form-text small text-muted">
                            Format CSV / TXT, header pertama wajib <code>KD_TX</code> (11 kolom standar Core Banking). Maksimal 20MB.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm" style="background-color: #0f2e5a; border-color: #0f2e5a;">
                        <i class="bi bi-cloud-upload me-1"></i> Proses Populasi &amp; Jalankan Sampling Onsite
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Template Testing & Penjelasan KKA Onsite -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success-subtle text-success p-2 rounded-circle">
                        <i class="bi bi-file-earmark-excel fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">File Template Testing Onsite</h6>
                        <small class="text-muted">Populasi CBS &amp; Alur KKA Onsite</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary text-white fw-bold">POPULASI CBS ONSITE</span>
                        <a href="{{ asset('templates/sample_testing/ONSITE_POPULASI_CBS.csv') }}" download class="btn btn-xs btn-success rounded-pill px-3 fw-bold" style="font-size: 0.78rem;">
                            <i class="bi bi-download me-1"></i> Unduh Template CSV
                        </a>
                    </div>
                    <div class="text-dark small" style="font-size: 0.8rem;">
                        File ini berisi 11 kolom resmi CBS: <code>KD_TX, KD_CAB, NO_REK, NO_ARSIP, KET_TX, DB_KR, TXTYPE, KD_USER, TGL_TX, TIME_STAMP, JUMLAH_TX</code>.
                    </div>
                </div>

                <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-diagram-3 me-1 text-primary"></i> Otomatis Mengisi 6 KKA Onsite:</h6>
                <div class="list-group list-group-flush border rounded-2 mb-3" style="font-size: 0.8rem;">
                    <div class="list-group-item py-1.5 px-2.5">
                        <span class="badge bg-danger-subtle text-danger fw-bold me-1">Mandatory</span>
                        Reversal, Tunai Besar, Akun Intern &rarr; Masuk <strong>KKA Teller &amp; Kas</strong>
                    </div>
                    <div class="list-group-item py-1.5 px-2.5">
                        <span class="badge bg-warning-subtle text-dark fw-bold me-1">Certainty</span>
                        Nominal &ge; Rp 100 Juta &rarr; Masuk Sampel Pengujian Utama
                    </div>
                    <div class="list-group-item py-1.5 px-2.5">
                        <span class="badge bg-info-subtle text-info fw-bold me-1">Targeted</span>
                        Transaksi di luar jam kerja (overtime)
                    </div>
                    <div class="list-group-item py-1.5 px-2.5">
                        <span class="badge bg-secondary-subtle text-dark fw-bold me-1">Initial</span>
                        Sampling proporsional transaksi harian
                    </div>
                </div>

                <div class="alert alert-info border-info-subtle py-2 px-2.5 mb-0" style="font-size: 0.78rem;">
                    <i class="bi bi-check-circle-fill me-1"></i><strong>KKA Fisik Onsite (Tahap 2):</strong><br>
                    Cash Opname, Rekon Barang Cetakan (BCT), Rekon Cek/BG, Rekon Kartu ATM, dan Observasi Lingkungan <strong>tidak memerlukan file Excel/upload</strong>, melainkan diisi langsung oleh RA melalui form pemeriksaan fisik di halaman KKA Onsite.
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
