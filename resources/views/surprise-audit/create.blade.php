@extends('layouts.app')

@section('title', 'Buat Penugasan Surprise Audit - WP-RA-05-A')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('surprise_audit.index') }}" class="text-decoration-none text-muted mb-1 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Register Penugasan
            </a>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-file-earmark-plus text-primary me-2"></i> WP-RA-05-A | Form Penugasan Surprise Audit
            </h2>
            <p class="text-muted mb-0">Isi data penugasan awal, pilih unit target & auditor pelaksana, serta tentukan ruang lingkup fisik.</p>
        </div>
        <div>
            <span class="badge bg-dark fs-6 px-3 py-2 rounded-pill shadow-sm">
                <i class="bi bi-person-badge me-1"></i> Pembuat: {{ auth()->user()->name }} ({{ strtoupper(auth()->user()->role) }})
            </span>
        </div>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> Mohon lengkapi formulir:</h6>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-clipboard2-check me-2 text-warning"></i> Lembar Penugasan Master (WP-RA-05-A)</span>
            <span class="badge bg-warning text-dark">Kerahasiaan: Rahasia - Need to Know</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('surprise_audit.assignment.store') }}" method="POST">
                @csrf

                <!-- Pemilihan Unit Kerja Target -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Pilih Unit Kerja Target (Bank Sulteng)</label>
                        <select id="select_unit" class="form-select select2" onchange="syncUnitData(this)">
                            <option value="">-- Pilih Unit Kerja dari Master --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->unit_code }}" data-name="{{ $u->unit_name }}">
                                    [{{ $u->unit_code }}] {{ $u->unit_name }} ({{ $u->unit_type }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Memilih dari master otomatis mengisi kode & nama unit di samping.</small>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">Kode Unit <span class="text-danger">*</span></label>
                        <input type="text" id="kode_unit" name="kode_unit" class="form-control" placeholder="Contoh: 407" required value="{{ old('kode_unit') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nama Unit / Kantor <span class="text-danger">*</span></label>
                        <input type="text" id="nama_unit" name="nama_unit" class="form-control" placeholder="Contoh: Kantor Cabang Pembantu Bunta" required value="{{ old('nama_unit') }}">
                    </div>
                </div>

                <!-- Nomor Surat Tugas & Tanggal -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nomor Surat Tugas <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_assignment" class="form-control" placeholder="Contoh: ST-RA/SA/{{ date('Ymd') }}/407" required value="{{ old('nomor_assignment', 'ST-RA/SA/' . date('Ym') . '/001') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tanggal & Jam Rencana Tiba <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="tanggal_rencana" class="form-control" required value="{{ old('tanggal_rencana', date('Y-m-d\TH:i')) }}">
                        <small class="text-muted">Waktu kedatangan fisik mendadak di unit kerja (Cut-off Kas).</small>
                    </div>
                </div>

                <!-- KONEKSI USER: Resident Auditor (RA) & Reviewer -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <label class="form-label fw-bold text-primary">
                                <i class="bi bi-person-fill-check me-1"></i> Akun Resident Auditor (RA Pelaksana) <span class="text-danger">*</span>
                            </label>
                            <select name="ra_user_id" id="ra_user_id" class="form-select mb-2" onchange="syncRaName(this)">
                                <option value="">-- Pilih Akun RA Terdaftar --</option>
                                @foreach($raUsers as $ra)
                                    <option value="{{ $ra->id }}" data-name="{{ $ra->name }}" {{ (auth()->user()->role === 'ra' && auth()->id() == $ra->id) ? 'selected' : '' }}>
                                        {{ $ra->name }} ({{ $ra->email }})
                                    </option>
                                @endforeach
                            </select>
                            <label class="form-label small text-muted mb-1">Nama Petugas pada Lembar KKA:</label>
                            <input type="text" id="resident_auditor" name="resident_auditor" class="form-control form-control-sm" placeholder="Nama RA" required value="{{ old('resident_auditor', (auth()->user()->role === 'ra' ? auth()->user()->name : '')) }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <label class="form-label fw-bold text-dark">
                                <i class="bi bi-shield-check me-1"></i> Reviewer / Pengawas (SKAI) <span class="text-danger">*</span>
                            </label>
                            <select name="reviewer_user_id" id="reviewer_user_id" class="form-select mb-2" onchange="syncReviewerName(this)">
                                <option value="">-- Pilih Supervisor / Reviewer --</option>
                                @foreach($reviewers as $rev)
                                    <option value="{{ $rev->id }}" data-name="{{ $rev->name }}" {{ ($rev->role === 'kabag_ra' || $loop->first) ? 'selected' : '' }}>
                                        {{ $rev->name }} ({{ strtoupper($rev->role) }})
                                    </option>
                                @endforeach
                            </select>
                            <label class="form-label small text-muted mb-1">Nama Reviewer pada Lembar KKA:</label>
                            <input type="text" id="reviewer" name="reviewer" class="form-control form-control-sm" placeholder="Nama Reviewer" required value="{{ old('reviewer', 'Siti (Kabag RA Korwas)') }}">
                        </div>
                    </div>
                </div>

                <!-- Ruang Lingkup Pemeriksaan -->
                <div class="mb-4 p-3 border rounded-3 bg-white">
                    <label class="form-label fw-bold mb-3 d-block text-dark">
                        <i class="bi bi-check2-square text-warning me-1"></i> Ruang Lingkup Pemeriksaan Fisik (Centang area yang diuji):
                    </label>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="form-check p-2 border rounded bg-light">
                                <input class="form-check-input ms-1" type="checkbox" name="ruang_lingkup[]" value="kas" id="rk_kas" checked>
                                <label class="form-check-label ms-2 fw-semibold" for="rk_kas">
                                    <i class="bi bi-cash-stack text-success me-1"></i> WP-RA-05-C: Kas Teller & Khasanah
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check p-2 border rounded bg-light">
                                <input class="form-check-input ms-1" type="checkbox" name="ruang_lingkup[]" value="kas_atm" id="rk_atm" checked>
                                <label class="form-check-label ms-2 fw-semibold" for="rk_atm">
                                    <i class="bi bi-credit-card-2-front text-primary me-1"></i> WP-RA-05-D: Kas Mesin ATM
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check p-2 border rounded bg-light">
                                <input class="form-check-input ms-1" type="checkbox" name="ruang_lingkup[]" value="atm_pin" id="rk_kartu" checked>
                                <label class="form-check-label ms-2 fw-semibold" for="rk_kartu">
                                    <i class="bi bi-credit-card text-info me-1"></i> WP-RA-05-E: Kartu ATM & PIN Mailer
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check p-2 border rounded bg-light">
                                <input class="form-check-input ms-1" type="checkbox" name="ruang_lingkup[]" value="pencetakan" id="rk_cetak" checked>
                                <label class="form-check-label ms-2 fw-semibold" for="rk_cetak">
                                    <i class="bi bi-journal-text text-secondary me-1"></i> WP-RA-05-F: Barang Cetakan
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check p-2 border rounded bg-light">
                                <input class="form-check-input ms-1" type="checkbox" name="ruang_lingkup[]" value="dok_berharga" id="rk_dok">
                                <label class="form-check-label ms-2 fw-semibold" for="rk_dok">
                                    <i class="bi bi-file-earmark-check text-warning me-1"></i> WP-RA-05-G: Dokumen Berharga (Cek/BG)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check p-2 border rounded bg-light">
                                <input class="form-check-input ms-1" type="checkbox" name="ruang_lingkup[]" value="agunan" id="rk_agunan">
                                <label class="form-check-label ms-2 fw-semibold" for="rk_agunan">
                                    <i class="bi bi-safe text-danger me-1"></i> WP-RA-05-H: Dokumen Agunan Kredit
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check p-2 border rounded bg-light">
                                <input class="form-check-input ms-1" type="checkbox" name="ruang_lingkup[]" value="aset" id="rk_aset" checked>
                                <label class="form-check-label ms-2 fw-semibold" for="rk_aset">
                                    <i class="bi bi-laptop text-dark me-1"></i> WP-RA-05-I: Inventaris & Aset Kantor
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check p-2 border rounded bg-light">
                                <input class="form-check-input ms-1" type="checkbox" name="ruang_lingkup[]" value="lingkungan" id="rk_cctv" checked>
                                <label class="form-check-label ms-2 fw-semibold" for="rk_cctv">
                                    <i class="bi bi-camera-video text-danger me-1"></i> WP-RA-05-J: Lingkungan & CCTV
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('surprise_audit.index') }}" class="btn btn-secondary px-4 rounded-pill">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm fw-bold">
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

document.addEventListener('DOMContentLoaded', function() {
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
