@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="mt-4 mb-4">
        <h1 class="h3 mb-0 text-gray-800">WP-RA-05-A | Form Penugasan Surprise Audit</h1>
        <p class="text-muted">Isi data penugasan awal dan tentukan ruang lingkup pemeriksaan</p>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white fw-bold">
            Form Lembar Penugasan
        </div>
        <div class="card-body">
            <form action="{{ route('surprise_audit.assignment.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Kode Unit Target</label>
                        <input type="text" name="kode_unit" class="form-control" placeholder="Contoh: 407" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Nama Unit / Kantor</label>
                        <input type="text" name="nama_unit" class="form-control" placeholder="Contoh: Kantor Cabang Pembantu Bunta" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nomor Surat Tugas</label>
                        <input type="text" name="nomor_assignment" class="form-control" placeholder="Contoh: ST-RA/SA/1014/2026" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tanggal Rencana</label>
                        <input type="datetime-local" name="tanggal_rencana" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Resident Auditor (RA)</label>
                        <input type="text" name="resident_auditor" class="form-control" placeholder="Nama Petugas Audit" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Reviewer</label>
                        <input type="text" name="reviewer" class="form-control" placeholder="Nama Supervisor / Kabag" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Ruang Lingkup Pemeriksaan (Centang area yang diperiksa):</label>
                    <div class="row">
                        <div class="col-md-4 mb-2"><input type="checkbox" name="ruang_lingkup[]" value="kas" checked> Kas Teller & Khasanah</div>
                        <div class="col-md-4 mb-2"><input type="checkbox" name="ruang_lingkup[]" value="kas_atm" checked> Kas ATM</div>
                        <div class="col-md-4 mb-2"><input type="checkbox" name="ruang_lingkup[]" value="atm_pin" checked> Kartu ATM & PIN Mailer</div>
                        <div class="col-md-4 mb-2"><input type="checkbox" name="ruang_lingkup[]" value="dok_berharga"> Dokumen Berharga (Cek/BG)</div>
                        <div class="col-md-4 mb-2"><input type="checkbox" name="ruang_lingkup[]" value="agunan"> Dokumen Agunan</div>
                        <div class="col-md-4 mb-2"><input type="checkbox" name="ruang_lingkup[]" value="aset" checked> Inventaris & Aset Kantor</div>
                        <div class="col-md-4 mb-2"><input type="checkbox" name="ruang_lingkup[]" value="lingkungan" checked> Lingkungan & CCTV</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> Simpan Assignment & Lanjutkan KKA
                </button>
            </form>
        </div>
    </div>
</div>
@endsection