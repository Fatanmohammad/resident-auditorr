@extends('layouts.app')
@section('title', 'Input Trigger Manual - Risk Trigger Visit (SOP 06)')

@section('content')
<style>
    .form-section-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .form-section-header {
        background: #f8fafc;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .form-section-title {
        font-size: 0.96rem;
        font-weight: 700;
        color: var(--bs-blue-dark);
        margin: 0;
    }
    .form-section-body {
        padding: 1.5rem;
    }
    .form-label {
        font-size: 0.82rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 0.4rem;
        display: block;
    }
    .form-label .required {
        color: #dc2626;
    }
    .form-control, .form-select {
        font-size: 0.88rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0.55rem 0.85rem;
        color: #1e293b;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--bs-blue);
        box-shadow: 0 0 0 3px rgba(30,75,133,0.12);
    }
    .field-hint {
        font-size: 0.78rem;
        color: #64748b;
        margin-top: 0.3rem;
    }
</style>

<!-- Header Harmonized SOP 06 -->
<div class="rtv-header">
    <div>
        <div class="rtv-breadcrumb">
            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a> &rsaquo;
            <a href="{{ route('risk-trigger.register') }}">Register Trigger</a> &rsaquo;
            <span class="text-dark fw-semibold">Input Trigger Manual</span>
        </div>
        <h4 class="rtv-title">
            <i class="bi bi-plus-circle text-primary"></i> Input Trigger Manual
            <span class="badge-wp">FORM ENTRY</span>
        </h4>
        <p class="rtv-subtitle">
            Pencatatan temuan/anomali baru dari sumber manual/eksternal. Parameter risiko, SLA, dan rekomendasi visit akan dikalkulasi otomatis.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('risk-trigger.register') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Register
        </a>
    </div>
</div>

<form action="{{ route('risk-trigger.store-manual') }}" method="POST">
    @csrf

    <!-- SECTION 1: Identitas & Sumber Data -->
    <div class="form-section-card">
        <div class="form-section-header">
            <i class="bi bi-info-circle text-primary fs-5"></i>
            <h5 class="form-section-title">1. Identitas &amp; Sumber Data</h5>
        </div>
        <div class="form-section-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Sumber Trigger <span class="required">*</span></label>
                    <select name="sumber" class="form-select" required>
                        <option value="Surprise" {{ old('sumber') == 'Surprise' ? 'selected' : '' }}>Surprise</option>
                        <option value="Monitoring TL" {{ old('sumber') == 'Monitoring TL' ? 'selected' : '' }}>Monitoring TL</option>
                        <option value="Pengaduan" {{ old('sumber') == 'Pengaduan' ? 'selected' : '' }}>Pengaduan</option>
                        <option value="Informasi Manajemen" {{ old('sumber') == 'Informasi Manajemen' ? 'selected' : '' }}>Informasi Manajemen</option>
                        <option value="Daily Review" {{ old('sumber') == 'Daily Review' ? 'selected' : '' }}>Daily Review</option>
                        <option value="Offsite" {{ old('sumber') == 'Offsite' ? 'selected' : '' }}>Offsite</option>
                        <option value="Onsite" {{ old('sumber') == 'Onsite' ? 'selected' : '' }}>Onsite</option>
                    </select>
                    <div class="field-hint">Kategori asal pemicu audit lapangan</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tanggal Trigger <span class="required">*</span></label>
                    <input type="date" name="tanggal_trigger" class="form-control" value="{{ old('tanggal_trigger', date('Y-m-d')) }}" required>
                    <div class="field-hint">Tanggal kejadian atau laporan diterima</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Source WP ID</label>
                    <input type="text" name="source_wp_id" class="form-control" placeholder="Contoh: SR-202610-001" value="{{ old('source_wp_id') }}">
                    <div class="field-hint">Kode kertas kerja atau nomor registrasi asal (opsional)</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Kantor Cabang / Unit <span class="required">*</span></label>
                    <select name="kode_unit" class="form-select" required>
                        <option value="">-- Pilih Kantor Unit --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->unit_code }}" {{ old('kode_unit') == $u->unit_code ? 'selected' : '' }}>
                                {{ $u->unit_code }} - {{ $u->unit_name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="field-hint">Unit kerja yang menjadi subjek pemeriksaan</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">13 Area Risiko Standar SOP 06 <span class="required">*</span></label>
                    <select name="area" class="form-select" required>
                        @foreach($standarAreas as $area)
                            <option value="{{ $area }}" {{ old('area') == $area ? 'selected' : '' }}>{{ $area }}</option>
                        @endforeach
                    </select>
                    <div class="field-hint">Klasifikasi area risiko sesuai pedoman WP_SOP_06</div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: Objek & Uraian Pengujian -->
    <div class="form-section-card">
        <div class="form-section-header">
            <i class="bi bi-box-seam text-primary fs-5"></i>
            <h5 class="form-section-title">2. Objek &amp; Uraian Pengujian</h5>
        </div>
        <div class="form-section-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Objek / Key Pengujian <span class="required">*</span></label>
                    <input type="text" name="objek_key" class="form-control" placeholder="Contoh: Brankas Kas Utama / No Arsip / Rekening" value="{{ old('objek_key') }}" required>
                    <div class="field-hint">Objek utama, nama akun, nomor rekening, atau identitas fisik yang diuji</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Periode Mulai</label>
                    <input type="date" name="periode_mulai" class="form-control" value="{{ old('periode_mulai', date('Y-m-d')) }}">
                    <div class="field-hint">Awal rentang waktu pengujian</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Periode Selesai</label>
                    <input type="date" name="periode_selesai" class="form-control" value="{{ old('periode_selesai', date('Y-m-d')) }}">
                    <div class="field-hint">Akhir rentang waktu pengujian</div>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Uraian Temuan / Pemicu Risiko <span class="required">*</span></label>
                    <textarea name="uraian_trigger" class="form-control" rows="3" placeholder="Uraikan peristiwa, temuan, atau anomali yang terjadi secara rinci dan objektif..." required>{{ old('uraian_trigger') }}</textarea>
                    <div class="field-hint">Penjelasan naratif mengenai kondisi yang ditemukan</div>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Bukti Awal Minimum <span class="required">*</span></label>
                    <textarea name="bukti_awal" class="form-control" rows="2" placeholder="Sebutkan bukti pendukung awal (dokumen, nomor transaksi, bukti slip, foto fisik, dll)..." required>{{ old('bukti_awal') }}</textarea>
                    <div class="field-hint">Dokumentasi pendukung awal yang mendasari pembukaan trigger</div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 3: Parameter Penilaian Risiko & Indikasi Khusus -->
    <div class="form-section-card">
        <div class="form-section-header">
            <i class="bi bi-shield-check text-primary fs-5"></i>
            <h5 class="form-section-title">3. Parameter Penilaian Risiko &amp; Indikasi Khusus</h5>
        </div>
        <div class="form-section-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Risk Score Sumber (1 - 25) <span class="required">*</span></label>
                    <input type="number" name="risk_score_sumber" class="form-control" step="0.5" min="1" max="25" value="{{ old('risk_score_sumber', 10) }}" required>
                    <div class="field-hint">Skor risiko awal dari modul sumber (&ge;16 Kritis, 10-15 Tinggi, 5-9 Sedang, &lt;5 Rendah)</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Jumlah Kejadian <span class="required">*</span></label>
                    <input type="number" name="jumlah_kejadian" class="form-control" min="1" value="{{ old('jumlah_kejadian', 1) }}" required>
                    <div class="field-hint">Frekuensi kemunculan peristiwa atau transaksi terkait</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Eksposur Nominal (Rp)</label>
                    <input type="number" name="eksposur_nominal" class="form-control" min="0" step="1000" value="{{ old('eksposur_nominal', 0) }}">
                    <div class="field-hint">Nilai kerugian finansial atau potensi eksposur moneter</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Perlu Verifikasi Lapangan?</label>
                    <select name="perlu_verifikasi_lapangan" class="form-select">
                        <option value="Ya" {{ old('perlu_verifikasi_lapangan') == 'Ya' ? 'selected' : '' }}>Ya</option>
                        <option value="Tidak" {{ old('perlu_verifikasi_lapangan') == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                    </select>
                    <div class="field-hint">Apakah temuan ini memerlukan uji keabsahan langsung di unit</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Indikasi Fraud?</label>
                    <select name="indikasi_fraud" class="form-select">
                        <option value="Tidak" {{ old('indikasi_fraud') == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                        <option value="Ya" {{ old('indikasi_fraud') == 'Ya' ? 'selected' : '' }}>Ya (Pemeriksaan Awal Fraud)</option>
                    </select>
                    <div class="field-hint">Jika "Ya", sistem otomatis mengunci keputusan ke Pemeriksaan Awal Fraud</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Objek Surprise / Fisik?</label>
                    <select name="objek_surprise_fisik" class="form-select">
                        <option value="Tidak" {{ old('objek_surprise_fisik') == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                        <option value="Ya" {{ old('objek_surprise_fisik') == 'Ya' ? 'selected' : '' }}>Ya (Surprise Audit Fisik)</option>
                    </select>
                    <div class="field-hint">Pemeriksaan fisik mendadak (kas/brankas/agunan/stok)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- FORM ACTION BUTTONS -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-5">
        <a href="{{ route('risk-trigger.register') }}" class="btn btn-outline" style="font-size: 0.9rem; padding: 0.65rem 1.5rem; border-radius: 8px;">
            <i class="bi bi-x-circle me-1"></i> Batal
        </a>
        <button type="submit" class="btn btn-primary" style="font-size: 0.95rem; font-weight: 700; padding: 0.65rem 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(30,75,133,0.25);">
            <i class="bi bi-save me-1"></i> Simpan &amp; Kalkulasi Trigger
        </button>
    </div>
</form>
@endsection
