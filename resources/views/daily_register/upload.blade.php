@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 0.3rem;">Upload Data DUMP Daily Register</h4>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Unggah file CSV Core Banking System untuk dianalisis oleh mesin Daily Register Audit.</p>
    </div>
    <a href="{{ route('daily-register.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
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
                <form action="{{ route('daily-register.upload.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- 1. INFORMASI TUJUAN PENGIRIMAN -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">
                            Tujuan Pengiriman Data
                        </label>
                        <div class="p-2.5 rounded bg-light border text-primary fw-bold small d-flex align-items-center">
                            <i class="bi bi-send-check-fill me-2 fs-6"></i> Tim SKAI / Admin Pusat (KCU Palu)
                        </div>
                    </div>

                    <!-- 2. PEMILIK DATA CSV -->
                    <div class="mb-3">
                        <label for="kode_unit" class="form-label fw-semibold text-dark small mb-1">
                            Data CSV ini milik Unit/Cabang mana? <span class="text-danger">*</span>
                        </label>
                        <select name="kode_unit" id="kode_unit" required class="form-select form-select-sm">
                            <option value="">-- Pilih Unit/Cabang Anda --</option>
                            @foreach($cabangs as $cabang)
                                <option value="{{ $cabang->unit_code }}">{{ $cabang->unit_code }} - {{ $cabang->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. JENIS FILE DUMP -->
                    <div class="mb-3">
                        <label for="jenis_file" class="form-label fw-semibold text-dark small mb-1">
                            Pilih Jenis File DUMP <span class="text-danger">*</span>
                        </label>
                        <select name="jenis_file" id="jenis_file" required class="form-select form-select-sm">
                            <option value="">-- Pilih Jenis File --</option>
                            <option value="DUMP_01">DUMP 01 - Transaksi Teller / CBS (KKA Teller &amp; Kas)</option>
                            <option value="DUMP_02">DUMP 02 - DPK / CS SPU (KKA DPK &amp; Layanan)</option>
                            <option value="DUMP_03">DUMP 03 - Kredit (KKA Kredit)</option>
                            <option value="DUMP_04">DUMP 04 - Beban Biaya (KKA Biaya &amp; Beban)</option>
                            <option value="DUMP_05">DUMP 05 - Pengaduan Nasabah (KKA Pengaduan)</option>
                            <option value="DUMP_06">DUMP 06 - Mutasi Kartu ATM (KKA Kartu ATM)</option>
                        </select>
                    </div>

                    <!-- 4. FILE CSV -->
                    <div class="mb-4">
                        <label for="file_csv" class="form-label fw-semibold text-dark small mb-1">
                            File CSV DUMP <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="file_csv" id="file_csv" accept=".csv" required class="form-control form-control-sm">
                        <div class="form-text small text-muted">
                            Pastikan format file adalah <code>.csv</code> (dapat dibuat/disimpan dari Microsoft Excel) maksimal 50MB.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm" style="background-color: #0f2e5a; border-color: #0f2e5a;">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Proses dan Analisis DUMP Harian
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
