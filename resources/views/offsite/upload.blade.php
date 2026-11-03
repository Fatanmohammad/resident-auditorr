@extends('layouts.app')

@section('content')

<div style="margin-bottom: 1.5rem;">
    <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 0.3rem;">Upload Data DUMP Offsite</h4>
    <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Unggah file CSV Core Banking System untuk dianalisis oleh mesin Offsite Audit sesuai periode data.</p>
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

@if(isset($errors) && $errors->any())
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

<!-- Form Upload -->
<div class="card" style="max-width: 600px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.04); border: 1px solid #e2e8f0;">
    <div class="card-body" style="padding: 2rem;">
        
        <form action="{{ route('offsite.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- 1. INFORMASI TUJUAN PENGIRIMAN (Visual Saja) -->
            <div style="margin-bottom: 1.5rem;">
                <label style="font-weight: 600; color: #334155; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                    Tujuan Pengiriman Data
                </label>
                <div style="padding: 0.6rem 0.8rem; background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; color: #1e3a8a; font-weight:600;">
                    <i class="bi bi-send-check-fill me-2"></i> Admin Pusat (KCU Palu)
                </div>
            </div>

            <!-- 2. PEMILIK DATA CSV (Fungsional - Wajib diisi) -->
            <div style="margin-bottom: 1.5rem;">
                <label for="kode_unit" style="font-weight: 600; color: #334155; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                    Data CSV ini milik Unit/Cabang mana? <span style="color: #dc2626;">*</span>
                </label>
                <select name="kode_unit" id="kode_unit" required class="form-select" style="font-size: 0.9rem; border-radius: 6px;">
                    <option value="">-- Pilih Unit/Cabang Anda --</option>
                    @foreach($cabangs as $cabang)
                        <option value="{{ $cabang->unit_code }}" {{ old('kode_unit') == $cabang->unit_code ? 'selected' : '' }}>
                            {{ $cabang->unit_code }} - {{ $cabang->unit_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 3. PERIODE DATA AUDIT / TRANSAKSI -->
            <div style="margin-bottom: 1.5rem;">
                <label for="periode" style="font-weight: 600; color: #334155; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                    Periode Data Transaksi (Bulan &amp; Tahun) <span style="color: #dc2626;">*</span>
                </label>
                <input type="month" name="periode" id="periode" required value="{{ old('periode', date('Y-m')) }}" class="form-control" style="font-size: 0.9rem; border-radius: 6px;">
                <p style="font-size: 0.78rem; color: #64748b; margin-top: 0.35rem; margin-bottom: 0;">
                    Tentukan bulan data transaksi yang diunggah agar isolasi data KKA dan mesin sampling tercatat akurat per periode.
                </p>
            </div>

            <!-- 4. JENIS FILE DUMP -->
            <div style="margin-bottom: 1.5rem;">
                <label for="jenis_file" style="font-weight: 600; color: #334155; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                    Pilih Jenis File DUMP <span style="color: #dc2626;">*</span>
                </label>
                <select name="jenis_file" id="jenis_file" required class="form-select" style="font-size: 0.9rem; border-radius: 6px;">
                    <option value="">-- Pilih Jenis File --</option>
                    <option value="DUMP_01" {{ old('jenis_file') == 'DUMP_01' ? 'selected' : '' }}>DUMP 01 - Transaksi Teller / CBS</option>
                    <option value="DUMP_02" {{ old('jenis_file') == 'DUMP_02' ? 'selected' : '' }}>DUMP 02 - DPK / CS SPU</option>
                    <option value="DUMP_03" {{ old('jenis_file') == 'DUMP_03' ? 'selected' : '' }}>DUMP 03 - Kredit</option>
                    <option value="DUMP_04" {{ old('jenis_file') == 'DUMP_04' ? 'selected' : '' }}>DUMP 04 - Beban Biaya</option>
                    <option value="DUMP_05" {{ old('jenis_file') == 'DUMP_05' ? 'selected' : '' }}>DUMP 05 - Pengaduan</option>
                    <option value="DUMP_06" {{ old('jenis_file') == 'DUMP_06' ? 'selected' : '' }}>DUMP 06 - Mutasi Kartu ATM</option>
                </select>
            </div>

            <!-- 5. FILE CSV -->
            <div style="margin-bottom: 2rem;">
                <label for="file_csv" style="font-weight: 600; color: #334155; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                    File CSV DUMP <span style="color: #dc2626;">*</span>
                </label>
                <input type="file" name="file_csv" id="file_csv" accept=".csv" required class="form-control" style="font-size: 0.9rem; border-radius: 6px;">
                <p style="font-size: 0.8rem; color: #64748b; margin-top: 0.5rem; margin-bottom: 0;">
                    Pastikan format file adalah .csv dengan ukuran maksimal 50MB.
                </p>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; font-weight: 600; padding: 0.6rem; border-radius: 6px; background-color: #1e3a8a; border-color: #1e3a8a;">
                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Proses dan Kirim Data ke Pusat
            </button>
        </form>
        
    </div>
</div>

@endsection
