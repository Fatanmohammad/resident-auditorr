@extends('layouts.app')

@section('title', 'KKA Dokumen Agunan - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="text-decoration-none text-muted mb-1 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kontrol Pelaksanaan
            </a>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-folder-check text-primary me-2"></i> WP-RA-05-H | KKA DOKUMEN AGUNAN
            </h3>
            <small class="text-muted">Pemeriksaan keberadaan, bentuk, identitas, masa berlaku, custody, dan kemutakhiran register dokumen agunan.</small>
        </div>
        <div>
            <span class="badge bg-dark fs-6 px-3 py-2 rounded-pill shadow-sm">
                {{ $assignment->visit_id }} | {{ $assignment->nama_unit }}
            </span>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-3" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Form Tambah Sampel Agunan -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-primary text-white fw-bold py-2">
            <i class="bi bi-plus-circle me-1"></i> Tambah Sampel Dokumen Agunan
        </div>
        <div class="card-body p-3">
            <form action="{{ route('surprise_audit.agunan.store', $assignment->id) }}" method="POST" class="row g-2 align-items-center">
                @csrf
                <input type="hidden" name="add_item" value="1">
                <div class="col-md-2">
                    <input type="text" name="cif_debitur" class="form-control form-control-sm" placeholder="CIF / Nama Debitur" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="no_kredit" class="form-control form-control-sm" placeholder="No. Rekening Kredit" required>
                </div>
                <div class="col-md-2">
                    <select name="jenis_agunan" class="form-select form-select-sm" required>
                        <option value="Sertifikat (SHM/SHGB)">Sertifikat (SHM/SHGB)</option>
                        <option value="BPKB Kendaraan">BPKB Kendaraan</option>
                        <option value="Bilyet Deposito">Bilyet Deposito</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="nomor_dokumen" class="form-control form-control-sm" placeholder="No. Sertifikat / No. BPKB" required>
                </div>
                <div class="col-md-2">
                    <input type="date" name="tanggal_kedaluwarsa" class="form-control form-control-sm" title="Tanggal Kedaluwarsa">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Sampel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Form Matriks Data -->
    <form action="{{ route('surprise_audit.agunan.store', $assignment->id) }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-table me-2 text-warning"></i> Matriks Pengujian Fisik Dokumen Agunan (KKA_Agunan)</span>
                <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">
                    <i class="bi bi-save me-1"></i> Simpan KKA Agunan
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 text-center fs-7">
                        <thead class="table-dark text-uppercase small align-middle">
                            <tr>
                                <th style="min-width: 130px;">CIF / Debitur</th>
                                <th style="min-width: 110px;">No. Kredit</th>
                                <th style="min-width: 120px;">Jenis Agunan</th>
                                <th style="min-width: 140px;">Dokumen Register</th>
                                <th style="min-width: 110px;">Disyaratkan</th>
                                <th style="min-width: 100px;">Ditemukan?</th>
                                <th style="min-width: 110px;">Bentuk Ditemukan</th>
                                <th style="min-width: 110px;">Identitas Sesuai?</th>
                                <th style="min-width: 120px;">No. Dokumen</th>
                                <th style="min-width: 120px;">Tgl Kedaluwarsa</th>
                                <th style="min-width: 110px;" class="bg-secondary text-white">Hari Kedaluwarsa</th>
                                <th style="min-width: 110px;">Akses/Custodian</th>
                                <th style="min-width: 110px;">Reg. Mutakhir</th>
                                <th style="min-width: 130px;">Metode Pemilihan</th>
                                <th style="min-width: 120px;">Sebab</th>
                                <th style="min-width: 120px;">Dampak</th>
                                <th style="min-width: 100px;">Ref. Bukti</th>
                                <th style="min-width: 110px;">Status Uji</th>
                                <th style="min-width: 100px;">Exception</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($agunanData as $item)
                            <tr>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][cif_debitur]" class="form-control form-control-sm fw-bold" value="{{ $item->cif_debitur }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][no_kredit]" class="form-control form-control-sm" value="{{ $item->no_kredit }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][jenis_agunan]" class="form-control form-control-sm" value="{{ $item->jenis_agunan }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][dokumen_menurut_register]" class="form-control form-control-sm" value="{{ $item->dokumen_menurut_register }}">
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][bentuk_disyaratkan]" class="form-select form-select-sm">
                                        <option value="Asli" {{ $item->bentuk_disyaratkan == 'Asli' ? 'selected' : '' }}>Asli</option>
                                        <option value="Salinan" {{ $item->bentuk_disyaratkan == 'Salinan' ? 'selected' : '' }}>Salinan</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][dokumen_ditemukan]" class="form-select form-select-sm fw-bold {{ $item->dokumen_ditemukan == 'Tidak' ? 'text-danger' : 'text-success' }}">
                                        <option value="Ya" {{ $item->dokumen_ditemukan == 'Ya' ? 'selected' : '' }}>Ya</option>
                                        <option value="Tidak" {{ $item->dokumen_ditemukan == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][bentuk_ditemukan]" class="form-select form-select-sm">
                                        <option value="Asli" {{ $item->bentuk_ditemukan == 'Asli' ? 'selected' : '' }}>Asli</option>
                                        <option value="Salinan" {{ $item->bentuk_ditemukan == 'Salinan' ? 'selected' : '' }}>Salinan</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][identitas_sesuai]" class="form-select form-select-sm fw-bold {{ $item->identitas_sesuai == 'Tidak' ? 'text-danger' : 'text-success' }}">
                                        <option value="Ya" {{ $item->identitas_sesuai == 'Ya' ? 'selected' : '' }}>Ya</option>
                                        <option value="Tidak" {{ $item->identitas_sesuai == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][nomor_dokumen]" class="form-control form-control-sm" value="{{ $item->nomor_dokumen }}">
                                </td>
                                <td>
                                    <input type="date" name="items[{{ $item->id }}][tanggal_kedaluwarsa]" class="form-control form-control-sm" value="{{ $item->tanggal_kedaluwarsa }}">
                                </td>
                                <td class="bg-light fw-bold {{ $item->hari_ke_kedaluwarsa !== null && $item->hari_ke_kedaluwarsa < 0 ? 'text-danger' : 'text-dark' }}">
                                    {{ $item->hari_ke_kedaluwarsa ?? '-' }}
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][akses_custodian]" class="form-select form-select-sm">
                                        <option value="Sesuai" {{ $item->akses_custodian == 'Sesuai' ? 'selected' : '' }}>Sesuai</option>
                                        <option value="Tidak Sesuai" {{ $item->akses_custodian == 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][register_mutakhir]" class="form-select form-select-sm">
                                        <option value="Ya" {{ $item->register_mutakhir == 'Ya' ? 'selected' : '' }}>Ya</option>
                                        <option value="Tidak" {{ $item->register_mutakhir == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                    </select>
                                </td>
                                <td class="small text-muted align-middle">
                                    {{ $item->metode_pemilihan }}
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][sebab]" class="form-control form-control-sm" value="{{ $item->sebab }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][dampak]" class="form-control form-control-sm" value="{{ $item->dampak }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][referensi_bukti]" class="form-control form-control-sm" value="{{ $item->referensi_bukti }}">
                                </td>
                                <td>
                                    <span class="badge {{ $item->status_uji == 'SESUAI' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $item->status_uji }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $item->exception_aktif == 'Ya' ? 'bg-danger' : 'bg-secondary' }}">
                                        {{ $item->exception_aktif }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="19" class="text-muted py-4">Belum ada sampel dokumen agunan yang diuji. Silakan tambahkan sampel pada form di atas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light p-3 text-end">
                <button type="submit" class="btn btn-warning fw-bold px-4">
                    <i class="bi bi-save me-1"></i> Simpan KKA Dokumen Agunan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection