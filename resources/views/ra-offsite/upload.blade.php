@extends('layouts.app')

@section('content')
<div>
    <div class="page-header" style="margin-bottom: 1.5rem;">
        <div class="page-header-title">
            <h1 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #1e3a8a;"><i class="bi bi-cloud-upload" style="margin-right: 0.5rem; color: #3b82f6;"></i>Upload Data Transaksi Offsite</h1>
            <p style="color: var(--text-muted, #64748b); font-size: 0.85rem; margin-top: 0.25rem;">Modul Upload Harian Data CBS, DPK, Kredit, Biaya/Beban & Pengaduan</p>
        </div>
    </div>

    @if(session('success'))
        <div id="success-alert" style="background-color: #d1e7dd; color: #0f5132; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; transition: opacity 0.5s ease;">
            <div><i class="bi bi-check-circle-fill" style="margin-right: 0.5rem;"></i> {{ session('success') }}</div>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #0f5132;">&times;</button>
        </div>
        <script>
            setTimeout(function() {
                var alertBox = document.getElementById('success-alert');
                if (alertBox) {
                    alertBox.style.opacity = '0';
                    setTimeout(function() { alertBox.style.display = 'none'; }, 500);
                }
            }, 5000);
        </script>
    @endif

    <div class="card" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #fff;">
        <div class="card-header" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.5rem;">
            <div class="card-title" style="font-size: 0.95rem; font-weight: 600; color: #334155;"><i class="bi bi-file-earmark-spreadsheet" style="margin-right: 0.4rem;"></i>Form Input & Upload File CSV</div>
        </div>
        <div class="card-body" style="padding: 1.5rem;">
            <form action="{{ route('ra-offsite.upload.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    {{-- 1. Pilihan Unit Naungan RA --}}
                    <div>
                        <label for="cabang_id" style="font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 0.4rem;">Pilih Cabang / Unit Kerja <span style="color: #ef4444;">*</span></label>
                        <select name="cabang_id" id="cabang_id" class="form-select @error('cabang_id') is-invalid @enderror" style="width: 100%; font-size: 0.9rem; padding: 0.5rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; background-color: #fff;" required>
                            <option value="">-- Pilih Unit Responsibility --</option>
                            @foreach($cabangs as $cabang)
                                <option value="{{ $cabang->id }}">{{ $cabang->kode_cabang ?? $cabang->id }} - {{ $cabang->nama_cabang }}</option>
                            @endforeach
                        </select>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">Daftar unit otomatis dibatasi sesuai hak akses wilayah audit Anda.</div>
                        @error('cabang_id') <div style="font-size: 0.8rem; color: #ef4444; margin-top: 0.25rem;">{{ $message }}</div> @enderror
                    </div>

                    {{-- 2. Jenis Domain Report --}}
                    <div>
                        <label for="domain_type" style="font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 0.4rem;">Jenis Laporan / Domain <span style="color: #ef4444;">*</span></label>
                        <select name="domain_type" id="domain_type" class="form-select @error('domain_type') is-invalid @enderror" onchange="toggleManualInputs()" style="width: 100%; font-size: 0.9rem; padding: 0.5rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; background-color: #fff;" required>
                            <option value="">-- Pilih Jenis Report --</option>
                            <option value="cbs">RPDT006 - Aktifitas Teller / CBS (Log Transaksi)</option>
                            <option value="biaya">RPDT017 - Transaksi Jurnal / Biaya (Log Transaksi)</option>
                            <option value="kredit">RPDC001 - Nominatif Kredit (Snapshot/Posisi)</option>
                            <option value="dpk">RPMS001 - Nominatif DPK / APU-PPT (Snapshot/Posisi)</option>
                            <option value="pengaduan">Pengaduan Nasabah (Tiket/CRM)</option>
                        </select>
                        @error('domain_type') <div style="font-size: 0.8rem; color: #ef4444; margin-top: 0.25rem;">{{ $message }}</div> @enderror
                    </div>

                    {{-- 3. Input Tanggal Manual (Hanya untuk Laporan Snapshot/Nominatif §5.6) --}}
                    <div id="manual_date_group" style="display: none; grid-column: span 2;">
                        <label for="tanggal_data_manual" style="font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 0.4rem;">Tanggal Data (Snapshot Date) <span style="color: #ef4444;">*</span></label>
                        <input type="date" name="tanggal_data_manual" id="tanggal_data_manual" class="form-control @error('tanggal_data_manual') is-invalid @enderror" style="width: 100%; font-size: 0.9rem; padding: 0.5rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; background-color: #fff;">
                        <div style="font-size: 0.75rem; color: #b45309; margin-top: 0.4rem; display: flex; align-items: center;"><i class="bi bi-info-circle" style="margin-right: 0.3rem;"></i> Diperlukan karena jenis laporan Nominatif tidak memiliki kolom tanggal harian di CSV.</div>
                        @error('tanggal_data_manual') <div style="font-size: 0.8rem; color: #ef4444; margin-top: 0.25rem;">{{ $message }}</div> @enderror
                    </div>

                    {{-- 4. File Upload CSV --}}
                    <div style="grid-column: span 2;">
                        <label for="file_csv" style="font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 0.4rem;">Pilih File CSV <span style="color: #ef4444;">*</span></label>
                        <input type="file" name="file_csv" id="file_csv" class="form-control @error('file_csv') is-invalid @enderror" accept=".csv,.txt" style="width: 100%; font-size: 0.9rem; padding: 0.4rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; background-color: #fff;" required>
                        @error('file_csv') <div style="font-size: 0.8rem; color: #ef4444; margin-top: 0.25rem;">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div style="text-align: right; margin-top: 2rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
                    <button type="submit" style="background-color: #2563eb; color: #fff; border: none; padding: 0.6rem 1.5rem; font-size: 0.9rem; font-weight: 600; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: background-color 0.2s;"><i class="bi bi-upload" style="margin-right: 0.5rem;"></i> Mulai Process & Scan Rule</button>
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
            dateGroup.style.display = 'block';
        } else {
            dateGroup.style.display = 'none';
        }
    }
</script>
@endsection