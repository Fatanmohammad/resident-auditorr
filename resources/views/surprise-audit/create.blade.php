@extends('layouts.app')

@section('title', 'Buat Penugasan Surprise Audit - WP-RA-05-A')

@section('content')
<div class="container-fluid px-4 py-3" style="background-color: #f8fafc; min-height: 100vh;">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <a href="{{ route('surprise_audit.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block fw-semibold" style="font-size: 14px;">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Register Penugasan
            </a>
            <h3 class="fw-bold mb-1" style="color: #0f2e5a; letter-spacing: -0.3px;">
                <i class="bi bi-file-earmark-plus text-primary me-2"></i> WP-RA-05-A | Form Penugasan Surprise Audit
            </h3>
            <p class="text-muted mb-0" style="font-size: 14px;">
                Isi data penugasan awal, pilih unit target & auditor pelaksana, serta tentukan ruang lingkup pemeriksaan fisik.
            </p>
        </div>
        <div>
            <span class="badge px-3 py-2 rounded-pill shadow-sm" style="background-color: #0f2e5a; color: #fff; font-size: 13px; font-weight: 600;">
                <i class="bi bi-person-badge me-1"></i> Pembuat: {{ auth()->user()->name }} ({{ strtoupper(auth()->user()->role) }})
            </span>
        </div>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4 border-0" role="alert" style="background-color: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444 !important;">
            <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i> Mohon lengkapi formulir:</h6>
            <ul class="mb-0 ps-3" style="font-size: 13.5px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; overflow: hidden;">
        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); color: #fff;">
            <span class="fw-bold" style="font-size: 14.5px;">
                <i class="bi bi-clipboard2-check me-2 text-info"></i> Lembar Penugasan Master (WP-RA-05-A)
            </span>
            <span class="badge rounded-pill px-3 py-1" style="background-color: rgba(255, 255, 255, 0.2); color: #fff; font-size: 12px; font-weight: 600;">
                Kerahasiaan: Rahasia - Need to Know
            </span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('surprise_audit.assignment.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Pemilihan Unit Kerja Target -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Pilih Unit Kerja Target (Bank Sulteng) <span class="text-danger">*</span></label>
                        <select id="select_unit" class="form-select select2" style="font-size: 13.5px; height: 42px; border-color: #cbd5e1;" onchange="syncUnitData(this)" required>
                            <option value="">-- Pilih Unit Kerja dari Master --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->unit_code }}" data-name="{{ $u->unit_name }}">
                                    [{{ $u->unit_code }}] {{ $u->unit_name }} ({{ $u->unit_type }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted" style="font-size: 12px;">Memilih dari master otomatis mengisi kode & nama unit di samping.</small>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Kode Unit <span class="text-danger">*</span></label>
                        <input type="text" id="kode_unit" name="kode_unit" class="form-control" style="font-size: 13.5px; height: 42px; border-color: #cbd5e1;" placeholder="Contoh: 407" required value="{{ old('kode_unit') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Nama Unit / Kantor <span class="text-danger">*</span></label>
                        <input type="text" id="nama_unit" name="nama_unit" class="form-control" style="font-size: 13.5px; height: 42px; border-color: #cbd5e1;" placeholder="Contoh: Kantor Cabang Pembantu Bunta" required value="{{ old('nama_unit') }}">
                    </div>
                </div>

                <!-- Tanggal Rencana (+ Nama Hari) & Nomor Surat Tugas -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Tanggal Rencana Tiba <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_rencana" id="tanggal_rencana" class="form-control" style="font-size: 13.5px; height: 42px; border-color: #cbd5e1;" required value="{{ old('tanggal_rencana', date('Y-m-d')) }}" onchange="updateNamaHari(this)">
                        <small class="text-muted" style="font-size: 12px;">Tanggal fisik kedatangan (jam riil diisi di Berita Acara).</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Hari</label>
                        <input type="text" name="tanggal_hari" id="tanggal_hari" class="form-control bg-light fw-bold" style="font-size: 13.5px; height: 42px; border-color: #cbd5e1; color: #0f2e5a;" readonly value="{{ old('tanggal_hari', 'Hari') }}">
                        <small class="text-muted" style="font-size: 12px;">Terdeteksi otomatis dari tanggal.</small>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                            Nomor Surat Tugas <span class="badge rounded-pill px-2 py-1 ms-1" style="background-color: #f1f5f9; color: #64748b; font-weight: normal; font-size: 11px;">Diisi saat Review</span>
                        </label>
                        <input type="text" name="nomor_assignment" class="form-control" style="font-size: 13.5px; height: 42px; border-color: #cbd5e1;" placeholder="Opsional (contoh: ST-RA/SA/{{ date('Ym') }}/001) - dapat diisi saat review" value="{{ old('nomor_assignment') }}">
                        <small class="text-muted" style="font-size: 12px;">Dapat dikosongkan terlebih dahulu, nanti diisi saat penugasan direview/approved.</small>
                    </div>
                </div>

                <!-- KONEKSI USER: Resident Auditor (RA) & Reviewer (Otomatis Terisi) -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                            <label class="form-label fw-bold" style="color: #0f2e5a; font-size: 13.5px;">
                                <i class="bi bi-person-fill-check me-1 text-primary"></i> Akun Resident Auditor (RA Pelaksana) 
                                <span class="badge rounded-pill px-2 py-1 ms-1" style="background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 11px;">Otomatis Terisi</span>
                            </label>
                            @php
                                $currentRaId = (auth()->user()->role === 'ra') ? auth()->id() : ($raUsers->first()->id ?? null);
                            @endphp
                            <select name="ra_user_id" id="ra_user_id" class="form-select mb-2" style="font-size: 13.5px; height: 42px; border-color: #cbd5e1;" onchange="syncRaName(this)">
                                @foreach($raUsers as $ra)
                                    <option value="{{ $ra->id }}" data-name="{{ $ra->name }}" {{ (old('ra_user_id', $currentRaId) == $ra->id) ? 'selected' : '' }}>
                                        {{ $ra->name }} ({{ $ra->email }})
                                    </option>
                                @endforeach
                            </select>
                            <label class="form-label small text-muted mb-1" style="font-size: 12px;">Nama Petugas pada Lembar KKA:</label>
                            <input type="text" id="resident_auditor" name="resident_auditor" class="form-control" style="font-size: 13.5px; height: 38px; border-color: #cbd5e1;" placeholder="Nama RA" required value="{{ old('resident_auditor', (auth()->user()->role === 'ra' ? auth()->user()->name : ($raUsers->first()->name ?? 'Resident Auditor'))) }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                            <label class="form-label fw-bold" style="color: #0f2e5a; font-size: 13.5px;">
                                <i class="bi bi-shield-check me-1 text-primary"></i> Reviewer / Supervisor (SKAI) 
                                <span class="badge rounded-pill px-2 py-1 ms-1" style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 11px;">Otomatis Terisi</span>
                            </label>
                            <select name="reviewer_user_id" id="reviewer_user_id" class="form-select mb-2" style="font-size: 13.5px; height: 42px; border-color: #cbd5e1;" onchange="syncReviewerName(this)">
                                @foreach($reviewers as $rev)
                                    <option value="{{ $rev->id }}" data-name="{{ $rev->name }}" {{ ($loop->first || $rev->role === 'kabag_ra') ? 'selected' : '' }}>
                                        {{ $rev->name }} ({{ strtoupper($rev->role) }})
                                    </option>
                                @endforeach
                            </select>
                            <label class="form-label small text-muted mb-1" style="font-size: 12px;">Nama Reviewer pada Lembar KKA:</label>
                            <input type="text" id="reviewer" name="reviewer" class="form-control" style="font-size: 13.5px; height: 38px; border-color: #cbd5e1;" placeholder="Nama Reviewer" required value="{{ old('reviewer', $reviewers->first()->name ?? 'Supervisor SKAI') }}">
                        </div>
                    </div>
                </div>

                <!-- Upload Sample Data Tambahan (Excel) -->
                <div class="mb-4 p-3 rounded-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                    <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                        <i class="bi bi-file-earmark-spreadsheet text-success me-1"></i> Upload Sample Data Tambahan (Excel) 
                        <span class="badge rounded-pill px-2 py-1 ms-1" style="background-color: #f1f5f9; color: #64748b; font-weight: normal; font-size: 11px;">Opsional</span>
                    </label>
                    <input type="file" name="sample_data_file" class="form-control" style="font-size: 13px; border-color: #cbd5e1;" accept=".xlsx,.xls">
                    <small class="text-muted" style="font-size: 12px;">Unggah data pendukung atau populasi sample audit dalam format Excel (.xlsx, .xls).</small>
                </div>

                <!-- Ruang Lingkup Pemeriksaan -->
                <div class="mb-4 p-4 rounded-3" style="background-color: #ffffff; border: 1px solid #e2e8f0;">
                    <label class="form-label fw-bold mb-3 d-block" style="color: #0f2e5a; font-size: 14px;">
                        <i class="bi bi-check2-square text-primary me-2"></i> Ruang Lingkup Pemeriksaan Fisik (Centang area yang diuji):
                    </label>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 h-100" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="ruang_lingkup[]" value="kas" id="rk_kas" checked style="cursor: pointer;">
                                    <label class="form-check-label fw-semibold" for="rk_kas" style="font-size: 13.5px; cursor: pointer; color: #1e293b;">
                                        <i class="bi bi-cash-stack text-success me-1"></i> WP-RA-05-C: Kas Teller & Khasanah
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 h-100" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="ruang_lingkup[]" value="kas_atm" id="rk_atm" checked style="cursor: pointer;">
                                    <label class="form-check-label fw-semibold" for="rk_atm" style="font-size: 13.5px; cursor: pointer; color: #1e293b;">
                                        <i class="bi bi-credit-card-2-front text-primary me-1"></i> WP-RA-05-D: Kas Mesin ATM
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 h-100" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="ruang_lingkup[]" value="atm_pin" id="rk_kartu" checked style="cursor: pointer;">
                                    <label class="form-check-label fw-semibold" for="rk_kartu" style="font-size: 13.5px; cursor: pointer; color: #1e293b;">
                                        <i class="bi bi-credit-card text-info me-1"></i> WP-RA-05-E: Kartu ATM & PIN
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 h-100" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="ruang_lingkup[]" value="pencetakan" id="rk_cetak" checked style="cursor: pointer;">
                                    <label class="form-check-label fw-semibold" for="rk_cetak" style="font-size: 13.5px; cursor: pointer; color: #1e293b;">
                                        <i class="bi bi-journal-text text-secondary me-1"></i> WP-RA-05-F: Barang Cetakan
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 h-100" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="ruang_lingkup[]" value="dok_berharga" id="rk_dok" style="cursor: pointer;">
                                    <label class="form-check-label fw-semibold" for="rk_dok" style="font-size: 13.5px; cursor: pointer; color: #1e293b;">
                                        <i class="bi bi-file-earmark-check text-warning me-1"></i> WP-RA-05-G: Dokumen Berharga
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 h-100" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="ruang_lingkup[]" value="agunan" id="rk_agunan" style="cursor: pointer;">
                                    <label class="form-check-label fw-semibold" for="rk_agunan" style="font-size: 13.5px; cursor: pointer; color: #1e293b;">
                                        <i class="bi bi-safe text-danger me-1"></i> WP-RA-05-H: Dokumen Agunan
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 h-100" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="ruang_lingkup[]" value="aset" id="rk_aset" checked style="cursor: pointer;">
                                    <label class="form-check-label fw-semibold" for="rk_aset" style="font-size: 13.5px; cursor: pointer; color: #1e293b;">
                                        <i class="bi bi-laptop text-dark me-1"></i> WP-RA-05-I: Inventaris & Aset
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 h-100" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="ruang_lingkup[]" value="lingkungan" id="rk_cctv" checked style="cursor: pointer;">
                                    <label class="form-check-label fw-semibold" for="rk_cctv" style="font-size: 13.5px; cursor: pointer; color: #1e293b;">
                                        <i class="bi bi-camera-video text-danger me-1"></i> WP-RA-05-J: Lingkungan & CCTV
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('surprise_audit.index') }}" class="btn rounded-pill px-4 py-2 fw-semibold" style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 14px;">
                        Batal
                    </a>
                    <button type="submit" class="btn rounded-pill px-4 py-2 fw-semibold shadow-sm text-white" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); font-size: 14px;">
                        <i class="bi bi-save me-1"></i> Simpan Penugasan & Aktifkan KKA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function syncUnitData(select) {
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption && selectedOption.value) {
        document.getElementById('kode_unit').value = selectedOption.value;
        document.getElementById('nama_unit').value = selectedOption.getAttribute('data-name') || '';
    }
}

function syncRaName(select) {
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption && selectedOption.value) {
        document.getElementById('resident_auditor').value = selectedOption.getAttribute('data-name') || '';
    }
}

function syncReviewerName(select) {
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption && selectedOption.value) {
        document.getElementById('reviewer').value = selectedOption.getAttribute('data-name') || '';
    }
}

function updateNamaHari(input) {
    if (!input.value) return;
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const dateObj = new Date(input.value + 'T00:00:00');
    const dayName = days[dateObj.getDay()];
    document.getElementById('tanggal_hari').value = dayName || '';
}

document.addEventListener('DOMContentLoaded', function() {
    const tglInput = document.getElementById('tanggal_rencana');
    if (tglInput && tglInput.value) {
        updateNamaHari(tglInput);
    }
    const raSelect = document.getElementById('ra_user_id');
    if (raSelect && raSelect.value && !document.getElementById('resident_auditor').value) {
        syncRaName(raSelect);
    }
    const revSelect = document.getElementById('reviewer_user_id');
    if (revSelect && revSelect.value && !document.getElementById('reviewer').value) {
        syncReviewerName(revSelect);
    }
});
</script>
@endsection
