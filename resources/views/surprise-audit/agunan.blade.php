@extends('layouts.app')

@section('title', 'KKA Dokumen Agunan - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3" style="background-color: #f8fafc; min-height: 100vh;">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="text-decoration-none text-muted mb-2 d-inline-block fw-semibold" style="font-size: 14px;">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kontrol Pelaksanaan
            </a>
            <h3 class="fw-bold mb-1" style="color: #0f2e5a; letter-spacing: -0.3px;">
                <i class="bi bi-folder-check text-primary me-2"></i> WP-RA-05-H | KKA Dokumen Agunan
            </h3>
            <p class="text-muted mb-0" style="font-size: 14px;">
                Pemeriksaan keberadaan, bentuk, identitas, masa berlaku, custody, dan kemutakhiran register dokumen agunan.
            </p>
        </div>
        <div>
            <span class="badge px-3 py-2 rounded-pill shadow-sm" style="background-color: #0f2e5a; color: #fff; font-size: 13px; font-weight: 600;">
                <i class="bi bi-building me-1"></i> {{ $assignment->visit_id }} | {{ $assignment->nama_unit }}
            </span>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4 border-0" role="alert" style="background-color: #f0fdf4; color: #166534; border-left: 4px solid #22c55e !important;">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i> <span style="font-size: 14px; font-weight: 600;">{{ session('success') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Form Tambah Sampel Agunan -->
    <div class="card border-0 shadow-sm rounded-3 mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; overflow: hidden;">
        <div class="card-header py-3 px-4" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); color: #fff;">
            <h6 class="fw-bold mb-0" style="font-size: 14px;">
                <i class="bi bi-plus-circle me-1 text-info"></i> Tambah Sampel Dokumen Agunan
            </h6>
        </div>
        <div class="card-body p-3" style="background-color: #f8fafc;">
            <form action="{{ route('surprise_audit.agunan.store', $assignment->id) }}" method="POST" class="row g-2 align-items-center">
                @csrf
                <input type="hidden" name="add_item" value="1">
                <div class="col-md-2">
                    <input type="text" name="cif_debitur" class="form-control" style="font-size: 13px; height: 38px; border-color: #cbd5e1;" placeholder="CIF / Nama Debitur" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="no_kredit" class="form-control" style="font-size: 13px; height: 38px; border-color: #cbd5e1;" placeholder="No. Rekening Kredit" required>
                </div>
                <div class="col-md-2">
                    <select name="jenis_agunan" class="form-select" style="font-size: 13px; height: 38px; border-color: #cbd5e1;" required>
                        <option value="Sertifikat (SHM/SHGB)">Sertifikat (SHM/SHGB)</option>
                        <option value="BPKB Kendaraan">BPKB Kendaraan</option>
                        <option value="Bilyet Deposito">Bilyet Deposito</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="nomor_dokumen" class="form-control" style="font-size: 13px; height: 38px; border-color: #cbd5e1;" placeholder="No. Sertifikat / BPKB" required>
                </div>
                <div class="col-md-2">
                    <input type="date" name="tanggal_kedaluwarsa" class="form-control" style="font-size: 13px; height: 38px; border-color: #cbd5e1;" title="Tanggal Kedaluwarsa">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn rounded-pill w-100 fw-semibold text-white shadow-sm" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); font-size: 13px; height: 38px;">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Sampel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Form Matriks Data -->
    <form action="{{ route('surprise_audit.agunan.store', $assignment->id) }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-3 mb-5" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; overflow: hidden;">
            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); color: #fff;">
                <span class="fw-bold" style="font-size: 14.5px;">
                    <i class="bi bi-table me-2 text-info"></i> Matriks Pengujian Fisik Dokumen Agunan (KKA_Agunan)
                </span>
                <button type="submit" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold shadow-sm text-white" style="background-color: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); font-size: 13px;">
                    <i class="bi bi-save me-1"></i> Simpan KKA Agunan
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 text-center" style="font-size: 13px;">
                        <thead style="background-color: #f1f5f9; color: #1e293b; font-weight: 700; font-size: 12px;" class="text-uppercase">
                            <tr>
                                <th style="min-width: 140px;" class="py-3 text-start ps-3">CIF / Debitur</th>
                                <th style="min-width: 110px;" class="py-3">No. Kredit</th>
                                <th style="min-width: 120px;" class="py-3">Jenis Agunan</th>
                                <th style="min-width: 130px;" class="py-3">Dokumen Register</th>
                                <th style="min-width: 100px;" class="py-3">Disyaratkan</th>
                                <th style="min-width: 100px;" class="py-3">Ditemukan?</th>
                                <th style="min-width: 110px;" class="py-3">Bentuk Ditemukan</th>
                                <th style="min-width: 110px;" class="py-3">Identitas Sesuai?</th>
                                <th style="min-width: 120px;" class="py-3">No. Dokumen</th>
                                <th style="min-width: 120px;" class="py-3">Tgl Kedaluwarsa</th>
                                <th style="min-width: 100px; background-color: #f8fafc;" class="py-3">Hari Expired</th>
                                <th style="min-width: 110px;" class="py-3">Akses/Custody</th>
                                <th style="min-width: 110px;" class="py-3">Reg. Mutakhir</th>
                                <th style="min-width: 120px;" class="py-3">Metode Sampel</th>
                                <th style="min-width: 120px;" class="py-3">Sebab</th>
                                <th style="min-width: 120px;" class="py-3">Dampak</th>
                                <th style="min-width: 100px;" class="py-3">Ref. Bukti</th>
                                <th style="min-width: 100px;" class="py-3">Status Uji</th>
                                <th style="min-width: 90px;" class="py-3 pe-3">Exception</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($agunanData as $item)
                            <tr>
                                <td class="text-start ps-3">
                                    <input type="text" name="items[{{ $item->id }}][cif_debitur]" class="form-control form-control-sm fw-semibold" style="font-size: 13px; height: 38px; border-color: #cbd5e1; color: #0f2e5a;" value="{{ $item->cif_debitur }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][no_kredit]" class="form-control form-control-sm" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->no_kredit }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][jenis_agunan]" class="form-control form-control-sm" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->jenis_agunan }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][dokumen_menurut_register]" class="form-control form-control-sm" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->dokumen_menurut_register }}">
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][bentuk_disyaratkan]" class="form-select form-select-sm" style="font-size: 12px; height: 38px; border-color: #cbd5e1;">
                                        <option value="Asli" {{ $item->bentuk_disyaratkan == 'Asli' ? 'selected' : '' }}>Asli</option>
                                        <option value="Salinan" {{ $item->bentuk_disyaratkan == 'Salinan' ? 'selected' : '' }}>Salinan</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][dokumen_ditemukan]" class="form-select form-select-sm fw-semibold" style="font-size: 12px; height: 38px; border-color: #cbd5e1; color: {{ $item->dokumen_ditemukan == 'Tidak' ? '#dc2626' : '#16a34a' }};">
                                        <option value="Ya" {{ $item->dokumen_ditemukan == 'Ya' ? 'selected' : '' }}>Ya</option>
                                        <option value="Tidak" {{ $item->dokumen_ditemukan == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][bentuk_ditemukan]" class="form-select form-select-sm" style="font-size: 12px; height: 38px; border-color: #cbd5e1;">
                                        <option value="Asli" {{ $item->bentuk_ditemukan == 'Asli' ? 'selected' : '' }}>Asli</option>
                                        <option value="Salinan" {{ $item->bentuk_ditemukan == 'Salinan' ? 'selected' : '' }}>Salinan</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][identitas_sesuai]" class="form-select form-select-sm fw-semibold" style="font-size: 12px; height: 38px; border-color: #cbd5e1; color: {{ $item->identitas_sesuai == 'Tidak' ? '#dc2626' : '#16a34a' }};">
                                        <option value="Ya" {{ $item->identitas_sesuai == 'Ya' ? 'selected' : '' }}>Ya</option>
                                        <option value="Tidak" {{ $item->identitas_sesuai == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][nomor_dokumen]" class="form-control form-control-sm" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->nomor_dokumen }}">
                                </td>
                                <td>
                                    <input type="date" name="items[{{ $item->id }}][tanggal_kedaluwarsa]" class="form-control form-control-sm" style="font-size: 12px; height: 38px; border-color: #cbd5e1;" value="{{ $item->tanggal_kedaluwarsa }}">
                                </td>
                                <td class="fw-bold" style="background-color: #f8fafc; font-size: 12.5px; color: {{ $item->hari_ke_kedaluwarsa !== null && $item->hari_ke_kedaluwarsa < 0 ? '#dc2626' : '#1e293b' }};">
                                    {{ $item->hari_ke_kedaluwarsa ?? '-' }}
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][akses_custodian]" class="form-select form-select-sm" style="font-size: 12px; height: 38px; border-color: #cbd5e1;">
                                        <option value="Sesuai" {{ $item->akses_custodian == 'Sesuai' ? 'selected' : '' }}>Sesuai</option>
                                        <option value="Tidak Sesuai" {{ $item->akses_custodian == 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][register_mutakhir]" class="form-select form-select-sm" style="font-size: 12px; height: 38px; border-color: #cbd5e1;">
                                        <option value="Ya" {{ $item->register_mutakhir == 'Ya' ? 'selected' : '' }}>Ya</option>
                                        <option value="Tidak" {{ $item->register_mutakhir == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                    </select>
                                </td>
                                <td class="small text-muted align-middle" style="font-size: 11.5px;">
                                    {{ $item->metode_pemilihan }}
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][sebab]" class="form-control form-control-sm" style="font-size: 12px; height: 38px; border-color: #cbd5e1;" value="{{ $item->sebab }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][dampak]" class="form-control form-control-sm" style="font-size: 12px; height: 38px; border-color: #cbd5e1;" value="{{ $item->dampak }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][referensi_bukti]" class="form-control form-control-sm" style="font-size: 12px; height: 38px; border-color: #cbd5e1;" value="{{ $item->referensi_bukti }}">
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold" style="{{ $item->status_uji == 'SESUAI' ? 'background-color: #f0fdf4; color: #166534;' : 'background-color: #fee2e2; color: #991b1b;' }} font-size: 11px;">
                                        {{ $item->status_uji }}
                                    </span>
                                </td>
                                <td class="pe-3">
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold" style="{{ $item->exception_aktif == 'Ya' ? 'background-color: #fee2e2; color: #991b1b;' : 'background-color: #f1f5f9; color: #475569;' }} font-size: 11px;">
                                        {{ $item->exception_aktif }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="19" class="text-muted py-5" style="font-size: 13.5px;">
                                    <i class="bi bi-folder-x fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Belum ada sampel dokumen agunan yang diuji. Silakan tambahkan sampel pada form di atas.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer p-3 text-end" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                <button type="submit" class="btn rounded-pill px-4 py-2 fw-semibold shadow-sm text-white" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); font-size: 14px;">
                    <i class="bi bi-save me-1"></i> Simpan KKA Dokumen Agunan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
