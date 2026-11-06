@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 0.3rem;">Upload Data CBS Onsite</h4>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">
            Kunjungan: <strong>{{ $visit->kode_unit }} — {{ $visit->nama_unit }}</strong> &nbsp;|&nbsp;
            {{ \Carbon\Carbon::parse($visit->tanggal_mulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($visit->tanggal_selesai)->format('d M Y') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('onsite.kka', $visit->id) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-file-earmark-check me-1"></i> KKA Onsite
        </a>
        <a href="{{ route('onsite.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">
        <!-- Tampilkan Pesan Sukses atau Error -->
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 mb-3" style="font-size: 0.9rem;">
                <i class="bi bi-check-circle-fill"></i>
                <div><strong>Berhasil!</strong> {{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" style="font-size: 0.9rem;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><strong>Error!</strong> {{ session('error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mb-3" style="font-size: 0.9rem;">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-exclamation-triangle-fill"></i> <strong>Terdapat Kesalahan:</strong>
                </div>
                <ul class="mb-0 ps-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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

                    <!-- PERIODE & FILTER TANGGAL TRANSAKSI (TIME_STAMP) -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label for="periode" class="form-label fw-semibold text-dark small mb-1">
                                Periode Bulan
                            </label>
                            <input type="month" name="periode" id="periode" class="form-control form-control-sm"
                                   value="{{ old('periode', $visit->periode ?? date('Y-m')) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="tanggal_mulai" class="form-label fw-semibold text-dark small mb-1">
                                Tanggal Mulai
                            </label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control form-control-sm"
                                   value="{{ old('tanggal_mulai', $visit->tanggal_mulai ? date('Y-m-d', strtotime($visit->tanggal_mulai)) : '') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="tanggal_selesai" class="form-label fw-semibold text-dark small mb-1">
                                Tanggal Selesai
                            </label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control form-control-sm"
                                   value="{{ old('tanggal_selesai', $visit->tanggal_selesai ? date('Y-m-d', strtotime($visit->tanggal_selesai)) : '') }}">
                        </div>
                        <div class="col-12">
                            <small class="text-muted" style="font-size: 0.75rem;">
                                <i class="bi bi-info-circle me-1"></i>Filter Tanggal Mulai &amp; Selesai memfilter baris CSV berdasarkan kolom <code>TIME_STAMP</code> secara otomatis.
                            </small>
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
</div>

@endsection
