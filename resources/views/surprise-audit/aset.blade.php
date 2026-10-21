@extends('layouts.app')

@section('title', 'KKA Inventarisasi Aset - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="text-decoration-none text-muted mb-1 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kontrol Pelaksanaan
            </a>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-box-seam text-primary me-2"></i> WP-RA-05-I | KKA INVENTARISASI ASET
            </h3>
            <small class="text-muted">Membandingkan register aset dengan keberadaan, lokasi, kuantitas, kondisi, dan label fisik.</small>
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

    <!-- Summary Banner -->
    <div class="card border-0 bg-light shadow-sm rounded-3 p-3 mb-4">
        <div class="row text-center">
            <div class="col-md-3 border-end">
                <small class="text-muted d-block text-uppercase fw-bold">Qty Register Total</small>
                <span class="fw-bold fs-5 text-dark">{{ number_format($qtyRegisterTotal) }} Unit</span>
            </div>
            <div class="col-md-3 border-end">
                <small class="text-muted d-block text-uppercase fw-bold">Qty Fisik Total</small>
                <span class="fw-bold fs-5 text-primary">{{ number_format($qtyFisikTotal) }} Unit</span>
            </div>
            <div class="col-md-3 border-end">
                <small class="text-muted d-block text-uppercase fw-bold">Selisih Total</small>
                <span class="fw-bold fs-5 {{ $selisihTotal != 0 ? 'text-danger' : 'text-success' }}">{{ number_format($selisihTotal) }} Unit</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block text-uppercase fw-bold">Exception Aktif</small>
                <span class="badge {{ $exceptionAktifCount > 0 ? 'bg-danger' : 'bg-success' }} fs-6 px-3 py-1 mt-1">
                    {{ $exceptionAktifCount }} Temuan
                </span>
            </div>
        </div>
    </div>

    <!-- Form Tambah Sampel Aset -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-primary text-white fw-bold py-2">
            <i class="bi bi-plus-circle me-1"></i> Tambah Sampel Inventarisasi Aset
        </div>
        <div class="card-body p-3">
            <form action="{{ route('surprise_audit.aset.store', $assignment->id) }}" method="POST" class="row g-2 align-items-center">
                @csrf
                <input type="hidden" name="add_item" value="1">
                <div class="col-md-2">
                    <input type="text" name="id_tag_aset" class="form-control form-control-sm" placeholder="ID / Tag Label Aset" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="jenis_nama_aset" class="form-control form-control-sm" placeholder="Nama / Jenis Barang Aset" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="lokasi_register" class="form-control form-control-sm" value="Ruang Server / Teller" placeholder="Lokasi Register">
                </div>
                <div class="col-md-2">
                    <input type="text" name="lokasi_fisik" class="form-control form-control-sm" value="Ruang Server / Teller" placeholder="Lokasi Fisik">
                </div>
                <div class="col-md-1">
                    <select name="kondisi_fisik" class="form-select form-select-sm">
                        <option value="Baik">Baik</option>
                        <option value="Rusak Ringan">Rusak Ringan</option>
                        <option value="Rusak Berat">Rusak Berat</option>
                    </select>
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
    <form action="{{ route('surprise_audit.aset.store', $assignment->id) }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-table me-2 text-warning"></i> Matriks Inventarisasi Aset (KKA_Aset)</span>
                <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">
                    <i class="bi bi-save me-1"></i> Simpan KKA Aset
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 text-center fs-7">
                        <thead class="table-dark text-uppercase small align-middle">
                            <tr>
                                <th style="min-width: 130px;">ID/Tag Aset</th>
                                <th style="min-width: 160px;">Jenis/Nama Aset</th>
                                <th style="min-width: 130px;">Lokasi Register</th>
                                <th style="min-width: 130px;">Lokasi Fisik</th>
                                <th style="min-width: 100px;">Qty Reg</th>
                                <th style="min-width: 100px;">Qty Fisik</th>
                                <th style="min-width: 100px;" class="bg-secondary text-white">Selisih Qty</th>
                                <th style="min-width: 120px;">Kondisi Fisik</th>
                                <th style="min-width: 110px;">Label Inventaris</th>
                                <th style="min-width: 110px;">Status Uji</th>
                                <th style="min-width: 130px;">Sebab</th>
                                <th style="min-width: 130px;">Dampak</th>
                                <th style="min-width: 110px;">Ref. Bukti</th>
                                <th style="min-width: 110px;">Validasi</th>
                                <th style="min-width: 100px;">Exception</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asetData as $item)
                            <tr>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][id_tag_aset]" class="form-control form-control-sm fw-bold" value="{{ $item->id_tag_aset }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][jenis_nama_aset]" class="form-control form-control-sm" value="{{ $item->jenis_nama_aset }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][lokasi_register]" class="form-control form-control-sm" value="{{ $item->lokasi_register }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][lokasi_fisik]" class="form-control form-control-sm" value="{{ $item->lokasi_fisik }}">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][qty_register]" class="form-control form-control-sm text-center" value="{{ $item->qty_register }}">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][qty_fisik]" class="form-control form-control-sm text-center fw-bold text-primary" value="{{ $item->qty_fisik }}">
                                </td>
                                <td class="bg-light fw-bold {{ $item->selisih_qty != 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $item->selisih_qty }}
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][kondisi_fisik]" class="form-select form-select-sm text-center fw-bold {{ $item->kondisi_fisik != 'Baik' ? 'text-danger' : 'text-success' }}">
                                        <option value="Baik" {{ $item->kondisi_fisik == 'Baik' ? 'selected' : '' }}>Baik</option>
                                        <option value="Rusak Ringan" {{ $item->kondisi_fisik == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                        <option value="Rusak Berat" {{ $item->kondisi_fisik == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][label_inventaris]" class="form-select form-select-sm text-center">
                                        <option value="Ya" {{ $item->label_inventaris == 'Ya' ? 'selected' : '' }}>Ya</option>
                                        <option value="Tidak" {{ $item->label_inventaris == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                    </select>
                                </td>
                                <td>
                                    <span class="badge {{ $item->status_uji == 'SESUAI' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $item->status_uji }}
                                    </span>
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
                                <td class="small text-muted align-middle">
                                    {{ $item->validasi }}
                                </td>
                                <td>
                                    <span class="badge {{ $item->exception_aktif == 'Ya' ? 'bg-danger' : 'bg-secondary' }}">
                                        {{ $item->exception_aktif }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="15" class="text-muted py-4">Belum ada sampel barang aset yang diuji. Silakan tambahkan pada form di atas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light p-3 text-end">
                <button type="submit" class="btn btn-warning fw-bold px-4">
                    <i class="bi bi-save me-1"></i> Simpan KKA Inventarisasi Aset
                </button>
            </div>
        </div>
    </form>
</div>
@endsection