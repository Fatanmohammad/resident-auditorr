@extends('layouts.app')

@section('content')

<div style="margin-bottom: 1.5rem;">
    <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 0.3rem;">Upload Data DUMP Daily Register</h4>
    <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Unggah file CSV Core Banking System untuk dianalisis oleh mesin Daily Register Audit.</p>
</div>

<!-- Tampilkan Pesan Sukses atau Error -->
@if(session('success'))
    <div class="alert alert-success" style="max-width: 600px; display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem;">
        <i class="bi bi-check-circle-fill"></i>
        <div><strong>Berhasil!</strong> {{ session('success') }}</div>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger" style="max-width: 600px; display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem;">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div><strong>Error!</strong> {{ session('error') }}</div>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger" style="max-width: 600px; font-size: 0.9rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.3rem;">
            <i class="bi bi-exclamation-triangle-fill"></i> <strong>Terdapat Kesalahan:</strong>
        </div>
        <ul style="margin: 0; padding-left: 1.5rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    <!-- Form Upload Kolom Kiri -->
    <div class="col-lg-7">
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

    <!-- Kolom Kanan: Download Template Siap Pakai untuk Testing -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success-subtle text-success p-2 rounded-circle">
                        <i class="bi bi-file-earmark-excel fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">File Template Testing per KKA</h6>
                        <small class="text-muted">Format CSV/Excel Siap Upload &amp; Uji</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="alert alert-light border p-2.5 mb-3 small" style="font-size: 0.8rem; background-color: #f8fafc;">
                    <i class="bi bi-info-circle text-primary me-1"></i>
                    Unduh file contoh di bawah untuk langsung mencoba upload. File sudah dilengkapi data anomali (High, Moderate, Low Risk) yang otomatis terdeteksi mesin sampling ke KKA Daily Register terkait:
                </div>

                <div class="list-group list-group-flush border rounded-2" style="font-size: 0.82rem;">
                    <!-- DUMP 01 -->
                    <div class="list-group-item p-2.5 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary-subtle text-primary fw-bold me-1">DUMP 01</span>
                            <strong>KKA Teller &amp; Kas</strong>
                            <div class="text-muted" style="font-size: 0.74rem;">Reversal, Setoran Besar, TPP Pegawai</div>
                        </div>
                        <a href="{{ asset('templates/sample_testing/DUMP_01_CBS_TELLER_KAS.csv') }}" download class="btn btn-xs btn-outline-success py-1 px-2.5 rounded-pill fw-semibold" style="font-size: 0.75rem;">
                            <i class="bi bi-download me-1"></i> Unduh
                        </a>
                    </div>

                    <!-- DUMP 02 -->
                    <div class="list-group-item p-2.5 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-info-subtle text-info fw-bold me-1">DUMP 02</span>
                            <strong>KKA DPK &amp; Layanan</strong>
                            <div class="text-muted" style="font-size: 0.74rem;">Dormant Aktif, Rekening Baru Jumbo</div>
                        </div>
                        <a href="{{ asset('templates/sample_testing/DUMP_02_DPK_CS_SPU.csv') }}" download class="btn btn-xs btn-outline-success py-1 px-2.5 rounded-pill fw-semibold" style="font-size: 0.75rem;">
                            <i class="bi bi-download me-1"></i> Unduh
                        </a>
                    </div>

                    <!-- DUMP 03 -->
                    <div class="list-group-item p-2.5 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-warning-subtle text-dark fw-bold me-1">DUMP 03</span>
                            <strong>KKA Kredit</strong>
                            <div class="text-muted" style="font-size: 0.74rem;">Kol 2-5 (NPL/Macet), Plafon Jumbo</div>
                        </div>
                        <a href="{{ asset('templates/sample_testing/DUMP_03_KREDIT.csv') }}" download class="btn btn-xs btn-outline-success py-1 px-2.5 rounded-pill fw-semibold" style="font-size: 0.75rem;">
                            <i class="bi bi-download me-1"></i> Unduh
                        </a>
                    </div>

                    <!-- DUMP 04 -->
                    <div class="list-group-item p-2.5 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-danger-subtle text-danger fw-bold me-1">DUMP 04</span>
                            <strong>KKA Biaya &amp; Beban</strong>
                            <div class="text-muted" style="font-size: 0.74rem;">Biaya Entertain, Promosi, Sewa Gedung</div>
                        </div>
                        <a href="{{ asset('templates/sample_testing/DUMP_04_BEBAN_BIAYA.csv') }}" download class="btn btn-xs btn-outline-success py-1 px-2.5 rounded-pill fw-semibold" style="font-size: 0.75rem;">
                            <i class="bi bi-download me-1"></i> Unduh
                        </a>
                    </div>

                    <!-- DUMP 05 -->
                    <div class="list-group-item p-2.5 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-secondary-subtle text-dark fw-bold me-1">DUMP 05</span>
                            <strong>KKA Pengaduan</strong>
                            <div class="text-muted" style="font-size: 0.74rem;">SLA Penyelesaian Melebihi Batas</div>
                        </div>
                        <a href="{{ asset('templates/sample_testing/DUMP_05_PENGADUAN_NASABAH.csv') }}" download class="btn btn-xs btn-outline-success py-1 px-2.5 rounded-pill fw-semibold" style="font-size: 0.75rem;">
                            <i class="bi bi-download me-1"></i> Unduh
                        </a>
                    </div>

                    <!-- DUMP 06 -->
                    <div class="list-group-item p-2.5 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary-subtle text-primary fw-bold me-1">DUMP 06</span>
                            <strong>KKA Kartu ATM</strong>
                            <div class="text-muted" style="font-size: 0.74rem;">No Arsip Ganda, Kartu Rusak/Retur</div>
                        </div>
                        <a href="{{ asset('templates/sample_testing/DUMP_06_MUTASI_KARTU_ATM.csv') }}" download class="btn btn-xs btn-outline-success py-1 px-2.5 rounded-pill fw-semibold" style="font-size: 0.75rem;">
                            <i class="bi bi-download me-1"></i> Unduh
                        </a>
                    </div>
                </div>

                <div class="mt-3 p-2 bg-light rounded text-muted" style="font-size: 0.74rem;">
                    <i class="bi bi-lightbulb-fill text-warning me-1"></i>
                    <strong>Tips Excel:</strong> Anda dapat membuka file <code>.csv</code> ini langsung dengan Microsoft Excel, mengedit angkanya, lalu menyimpannya kembali sebagai format <strong>CSV (Comma delimited) (*.csv)</strong>.
                </div>
            </div>
        </div>
    </div>
</div>

@endsection