@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-cloud-upload text-primary me-2"></i>Upload Data Transaksi Offsite</h4>
            <p class="text-muted small mb-0">Modul Upload Harian Data CBS, DPK, Kredit, Biaya/Beban & Pengaduan</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="card-title mb-0 fw-bold">Form Input & Upload File CSV</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('ra-offsite.upload.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    {{-- 1. Pilihan Unit Naungan RA --}}
                    <div class="col-md-6">
                        <label for="cabang_id" class="form-label fw-semibold">Pilih Cabang / Unit Kerja <span class="text-danger">*</span></label>
                        <select name="cabang_id" id="cabang_id" class="form-select @error('cabang_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Unit Responsibility --</option>
                            @foreach($cabangs as $cabang)
                                <option value="{{ $cabang->id }}">{{ $cabang->kode_cabang ?? $cabang->id }} - {{ $cabang->nama_cabang }}</option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted">Daftar unit otomatis dibatasi sesuai hak akses wilayah audit Anda.</div>
                        @error('cabang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- 2. Jenis Domain Report --}}
                    <div class="col-md-6">
                        <label for="domain_type" class="form-label fw-semibold">Jenis Laporan / Domain <span class="text-danger">*</span></label>
                        <select name="domain_type" id="domain_type" class="form-select @error('domain_type') is-invalid @enderror" onchange="toggleManualInputs()" required>
                            <option value="">-- Pilih Jenis Report --</option>
                            <option value="cbs">RPDT006 - Aktifitas Teller / CBS (Log Transaksi)</option>
                            <option value="biaya">RPDT017 - Transaksi Jurnal / Biaya (Log Transaksi)</option>
                            <option value="kredit">RPDC001 - Nominatif Kredit (Snapshot/Posisi)</option>
                            <option value="dpk">RPMS001 - Nominatif DPK / APU-PPT (Snapshot/Posisi)</option>
                            <option value="pengaduan">Pengaduan Nasabah (Tiket/CRM)</option>
                        </select>
                        @error('domain_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- 3. Input Tanggal Manual (Hanya untuk Laporan Snapshot/Nominatif §5.6) --}}
                    <div class="col-md-6 d-none" id="manual_date_group">
                        <label for="tanggal_data_manual" class="form-label fw-semibold">Tanggal Data (Snapshot Date) <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_data_manual" id="tanggal_data_manual" class="form-control @error('tanggal_data_manual') is-invalid @enderror">
                        <div class="form-text text-warning"><i class="bi bi-info-circle me-1"></i>Diperlukan karena jenis laporan Nominatif tidak memiliki kolom tanggal harian di CSV.</div>
                        @error('tanggal_data_manual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- 4. File Upload CSV --}}
                    <div class="col-md-12">
                        <label for="file_csv" class="form-label fw-semibold">Pilih File CSV <span class="text-danger">*</span></label>
                        <input type="file" name="file_csv" id="file_csv" class="form-control @error('file_csv') is-invalid @enderror" accept=".csv,.txt" required>
                        @error('file_csv') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-upload me-1"></i> Mulai Process & Scan Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleManualInputs() {
        const domain = document.getElementById('domain_type').value;
        const dateGroup = document.getElementById('manual_date_group');
        
        // Jenis laporan snapshot/nominatif butuh input tanggal manual (§5.6)
        if (domain === 'kredit' || domain === 'dpk') {
            dateGroup.classList.remove('d-none');
        } else {
            dateGroup.classList.add('d-none');
        }
    }
</script>
@endsection