@extends('layouts.app')

@section('title', 'KKA Inventarisasi Aset - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3" style="background-color: #f8fafc; min-height: 100vh;">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="text-decoration-none text-muted mb-2 d-inline-block fw-semibold" style="font-size: 14px;">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kontrol Pelaksanaan
            </a>
            <h3 class="fw-bold mb-1" style="color: #0f2e5a; letter-spacing: -0.3px;">
                <i class="bi bi-box-seam text-primary me-2"></i> WP-RA-05-I | KKA Inventarisasi Aset
            </h3>
            <p class="text-muted mb-0" style="font-size: 14px;">
                Membandingkan register aset dengan keberadaan, lokasi, kuantitas, kondisi, dan label fisik.
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

    <!-- Summary Banner (Soft Executive Metric Cards) -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid #3b82f6 !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Qty Register Total</small>
                <div class="fw-bold mt-1" style="font-size: 1.35rem; color: #1e293b;">
                    {{ number_format($qtyRegisterTotal) }} <span style="font-size: 0.9rem; font-weight: 500;" class="text-muted">Unit</span>
                </div>
                <div class="small text-muted mt-1" style="font-size: 12px;">Pencatatan buku aset kantor</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid #0284c7 !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Qty Fisik Total</small>
                <div class="fw-bold mt-1" style="font-size: 1.35rem; color: #0f2e5a;">
                    {{ number_format($qtyFisikTotal) }} <span style="font-size: 0.9rem; font-weight: 500;" class="text-muted">Unit</span>
                </div>
                <div class="small text-muted mt-1" style="font-size: 12px;">Akumulasi hitung fisik RA</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid {{ $selisihTotal != 0 ? '#ef4444' : '#10b981' }} !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Selisih Total</small>
                <div class="fw-bold mt-1 {{ $selisihTotal != 0 ? 'text-danger' : 'text-success' }}" style="font-size: 1.35rem;">
                    {{ number_format($selisihTotal) }} <span style="font-size: 0.9rem; font-weight: 500;" class="text-muted">Unit</span>
                </div>
                <div class="small mt-1">
                    @if($selisihTotal == 0)
                        <span class="badge rounded-pill px-2 py-1" style="background-color: #f0fdf4; color: #166534; font-size: 11px; font-weight: 600;">NIHIL / COCOK</span>
                    @else
                        <span class="badge rounded-pill px-2 py-1" style="background-color: #fee2e2; color: #991b1b; font-size: 11px; font-weight: 600;">TERDAPAT SELISIH</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid {{ $exceptionAktifCount > 0 ? '#ef4444' : '#10b981' }} !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Exception Aktif</small>
                <div class="mt-2">
                    <span class="badge rounded-pill px-3 py-2" style="{{ $exceptionAktifCount > 0 ? 'background-color: #fee2e2; color: #991b1b;' : 'background-color: #f0fdf4; color: #166534;' }} font-size: 12.5px; font-weight: 600;">
                        {{ $exceptionAktifCount }} Temuan
                    </span>
                </div>
                <div class="small text-muted mt-2" style="font-size: 12px;">Status hasil verifikasi</div>
            </div>
        </div>
    </div>

    <!-- Form Tambah Sampel Aset -->
    <div class="card border-0 shadow-sm rounded-3 mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; overflow: hidden;">
        <div class="card-header py-3 px-4" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); color: #fff;">
            <h6 class="fw-bold mb-0" style="font-size: 14px;">
                <i class="bi bi-plus-circle me-1 text-info"></i> Tambah Sampel Inventarisasi Aset
            </h6>
        </div>
        <div class="card-body p-3" style="background-color: #f8fafc;">
            <form action="{{ route('surprise_audit.aset.store', $assignment->id) }}" method="POST" class="row g-2 align-items-center">
                @csrf
                <input type="hidden" name="add_item" value="1">
                <div class="col-md-2">
                    <input type="text" name="id_tag_aset" class="form-control" style="font-size: 13px; height: 38px; border-color: #cbd5e1;" placeholder="ID / Tag Label Aset" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="jenis_nama_aset" class="form-control" style="font-size: 13px; height: 38px; border-color: #cbd5e1;" placeholder="Nama / Jenis Barang Aset" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="lokasi_register" class="form-control" style="font-size: 13px; height: 38px; border-color: #cbd5e1;" value="Ruang Server / Teller" placeholder="Lokasi Register">
                </div>
                <div class="col-md-2">
                    <input type="text" name="lokasi_fisik" class="form-control" style="font-size: 13px; height: 38px; border-color: #cbd5e1;" value="Ruang Server / Teller" placeholder="Lokasi Fisik">
                </div>
                <div class="col-md-1">
                    <select name="kondisi_fisik" class="form-select" style="font-size: 13px; height: 38px; border-color: #cbd5e1;">
                        <option value="Baik">Baik</option>
                        <option value="Rusak Ringan">Rusak Ringan</option>
                        <option value="Rusak Berat">Rusak Berat</option>
                    </select>
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
    <form action="{{ route('surprise_audit.aset.store', $assignment->id) }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-3 mb-5" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; overflow: hidden;">
            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); color: #fff;">
                <span class="fw-bold" style="font-size: 14.5px;">
                    <i class="bi bi-table me-2 text-info"></i> Matriks Inventarisasi Aset (KKA_Aset)
                </span>
                <button type="submit" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold shadow-sm text-white" style="background-color: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); font-size: 13px;">
                    <i class="bi bi-save me-1"></i> Simpan KKA Aset
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 text-center" style="font-size: 13px;">
                        <thead style="background-color: #f1f5f9; color: #1e293b; font-weight: 700; font-size: 12px;" class="text-uppercase">
                            <tr>
                                <th style="min-width: 130px;" class="py-3 text-start ps-3">ID/Tag Aset</th>
                                <th style="min-width: 160px;" class="py-3">Jenis / Nama Aset</th>
                                <th style="min-width: 130px;" class="py-3">Lokasi Register</th>
                                <th style="min-width: 130px;" class="py-3">Lokasi Fisik</th>
                                <th style="min-width: 90px;" class="py-3">Qty Reg</th>
                                <th style="min-width: 90px;" class="py-3">Qty Fisik</th>
                                <th style="min-width: 100px; background-color: #f8fafc;" class="py-3">Selisih Qty</th>
                                <th style="min-width: 120px;" class="py-3">Kondisi Fisik</th>
                                <th style="min-width: 110px;" class="py-3">Label Inventaris</th>
                                <th style="min-width: 100px;" class="py-3">Status Uji</th>
                                <th style="min-width: 130px;" class="py-3">Sebab</th>
                                <th style="min-width: 130px;" class="py-3">Dampak</th>
                                <th style="min-width: 110px;" class="py-3">Ref. Bukti</th>
                                <th style="min-width: 110px;" class="py-3">Validasi</th>
                                <th style="min-width: 90px;" class="py-3 pe-3">Exception</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asetData as $item)
                            <tr>
                                <td class="text-start ps-3">
                                    <input type="text" name="items[{{ $item->id }}][id_tag_aset]" class="form-control form-control-sm fw-semibold" style="font-size: 13px; height: 38px; border-color: #cbd5e1; color: #0f2e5a;" value="{{ $item->id_tag_aset }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][jenis_nama_aset]" class="form-control form-control-sm" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->jenis_nama_aset }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][lokasi_register]" class="form-control form-control-sm" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->lokasi_register }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][lokasi_fisik]" class="form-control form-control-sm" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->lokasi_fisik }}">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][qty_register]" class="form-control form-control-sm text-center" style="font-size: 13px; height: 38px; border-color: #cbd5e1;" value="{{ $item->qty_register }}">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][qty_fisik]" class="form-control form-control-sm text-center fw-bold" style="font-size: 13px; height: 38px; border-color: #cbd5e1; color: #0f2e5a;" value="{{ $item->qty_fisik }}">
                                </td>
                                <td class="fw-bold {{ $item->selisih_qty != 0 ? 'text-danger' : 'text-success' }}" style="background-color: #f8fafc; font-size: 13px;">
                                    {{ $item->selisih_qty }}
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][kondisi_fisik]" class="form-select form-select-sm text-center fw-semibold" style="font-size: 12px; height: 38px; border-color: #cbd5e1; color: {{ $item->kondisi_fisik != 'Baik' ? '#dc2626' : '#16a34a' }};">
                                        <option value="Baik" {{ $item->kondisi_fisik == 'Baik' ? 'selected' : '' }}>Baik</option>
                                        <option value="Rusak Ringan" {{ $item->kondisi_fisik == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                        <option value="Rusak Berat" {{ $item->kondisi_fisik == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][label_inventaris]" class="form-select form-select-sm text-center" style="font-size: 12px; height: 38px; border-color: #cbd5e1;">
                                        <option value="Ya" {{ $item->label_inventaris == 'Ya' ? 'selected' : '' }}>Ya</option>
                                        <option value="Tidak" {{ $item->label_inventaris == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                    </select>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold" style="{{ $item->status_uji == 'SESUAI' ? 'background-color: #f0fdf4; color: #166534;' : 'background-color: #fee2e2; color: #991b1b;' }} font-size: 11px;">
                                        {{ $item->status_uji }}
                                    </span>
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
                                <td class="small text-muted align-middle" style="font-size: 11.5px;">
                                    {{ $item->validasi }}
                                </td>
                                <td class="pe-3">
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold" style="{{ $item->exception_aktif == 'Ya' ? 'background-color: #fee2e2; color: #991b1b;' : 'background-color: #f1f5f9; color: #475569;' }} font-size: 11px;">
                                        {{ $item->exception_aktif }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="15" class="text-muted py-5" style="font-size: 13.5px;">
                                    <i class="bi bi-box fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Belum ada sampel barang aset yang diuji. Silakan tambahkan pada form di atas.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer p-3 text-end" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                <button type="submit" class="btn rounded-pill px-4 py-2 fw-semibold shadow-sm text-white" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); font-size: 14px;">
                    <i class="bi bi-save me-1"></i> Simpan KKA Inventarisasi Aset
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
