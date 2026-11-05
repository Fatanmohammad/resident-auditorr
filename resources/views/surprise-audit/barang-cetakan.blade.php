@extends('layouts.app')

@section('title', 'KKA Barang Cetakan - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3" style="background-color: #f8fafc; min-height: 100vh;">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="text-decoration-none text-muted mb-2 d-inline-block fw-semibold" style="font-size: 14px;">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kontrol Pelaksanaan
            </a>
            <h3 class="fw-bold mb-1" style="color: #0f2e5a; letter-spacing: -0.3px;">
                <i class="bi bi-journal-check text-primary me-2"></i> WP-RA-05-F | KKA Persediaan Barang Cetakan
            </h3>
            <p class="text-muted mb-0" style="font-size: 14px;">
                Membandingkan persediaan menurut catatan petugas dengan hasil hitung fisik RA (KKA_Kartu_Dok).
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
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Catatan Petugas Total</small>
                <div class="fw-bold mt-1" style="font-size: 1.35rem; color: #1e293b;">
                    {{ number_format($catatanTotal) }} <span style="font-size: 0.9rem; font-weight: 500;" class="text-muted">Pcs</span>
                </div>
                <div class="small text-muted mt-1" style="font-size: 12px;">Saldo buku catatan cabang</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid #0284c7 !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Hasil Fisik Total</small>
                <div class="fw-bold mt-1" style="font-size: 1.35rem; color: #0f2e5a;">
                    {{ number_format($fisikTotal) }} <span style="font-size: 0.9rem; font-weight: 500;" class="text-muted">Pcs</span>
                </div>
                <div class="small text-muted mt-1" style="font-size: 12px;">Akumulasi hitung fisik RA</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid {{ $selisihTotal != 0 ? '#ef4444' : '#10b981' }} !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Selisih Total</small>
                <div class="fw-bold mt-1 {{ $selisihTotal != 0 ? 'text-danger' : 'text-success' }}" style="font-size: 1.35rem;">
                    {{ number_format($selisihTotal) }} <span style="font-size: 0.9rem; font-weight: 500;" class="text-muted">Pcs</span>
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

    <!-- Form Matriks -->
    <form action="{{ route('surprise_audit.barang_cetakan.store', $assignment->id) }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-3 mb-5" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; overflow: hidden;">
            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); color: #fff;">
                <span class="fw-bold" style="font-size: 14.5px;">
                    <i class="bi bi-table me-2 text-info"></i> Matriks Opname Barang Cetakan (KKA_Kartu_Dok)
                </span>
                <button type="submit" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold shadow-sm text-white" style="background-color: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); font-size: 13px;">
                    <i class="bi bi-save me-1"></i> Simpan KKA
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 text-center" style="font-size: 13.5px;">
                        <thead style="background-color: #f1f5f9; color: #1e293b; font-weight: 700; font-size: 12.5px;" class="text-uppercase">
                            <tr>
                                <th style="min-width: 180px;" class="py-3 text-start ps-3">Jenis Barang Cetakan</th>
                                <th style="min-width: 120px;" class="py-3">Catatan Petugas</th>
                                <th style="min-width: 120px;" class="py-3">Hasil Fisik</th>
                                <th style="min-width: 110px; background-color: #f8fafc;" class="py-3">Selisih</th>
                                <th style="min-width: 150px;" class="py-3">Kondisi Penyimpanan</th>
                                <th style="min-width: 110px;" class="py-3">Status Uji</th>
                                <th style="min-width: 140px;" class="py-3">Sebab</th>
                                <th style="min-width: 140px;" class="py-3">Dampak</th>
                                <th style="min-width: 120px;" class="py-3 pe-3">Ref. Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cetakanData as $item)
                            <tr>
                                <td class="text-start ps-3">
                                    <input type="text" name="items[{{ $item->id }}][jenis_barang_cetakan]" class="form-control form-control-sm fw-semibold" style="font-size: 13px; height: 38px; border-color: #cbd5e1; color: #0f2e5a;" value="{{ $item->jenis_barang_cetakan }}">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][catatan_petugas]" class="form-control form-control-sm text-center" style="font-size: 13.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->catatan_petugas }}">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][hasil_fisik]" class="form-control form-control-sm text-center fw-bold" style="font-size: 13.5px; height: 38px; border-color: #cbd5e1; color: #0f2e5a;" value="{{ $item->hasil_fisik }}">
                                </td>
                                <td class="fw-bold {{ $item->selisih != 0 ? 'text-danger' : 'text-success' }}" style="background-color: #f8fafc; font-size: 14px;">
                                    {{ $item->selisih }}
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][kondisi_penyimpanan]" class="form-select form-select-sm text-center fw-semibold" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;">
                                        <option value="Aman/Terkunci" {{ $item->kondisi_penyimpanan == 'Aman/Terkunci' ? 'selected' : '' }}>Aman/Terkunci</option>
                                        <option value="Tidak Sesuai" {{ $item->kondisi_penyimpanan == 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                    </select>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="{{ $item->status_uji == 'SESUAI' ? 'background-color: #f0fdf4; color: #166534;' : 'background-color: #fee2e2; color: #991b1b;' }} font-size: 11.5px;">
                                        {{ $item->status_uji }}
                                    </span>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][sebab]" class="form-control form-control-sm" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->sebab }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][dampak]" class="form-control form-control-sm" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->dampak }}">
                                </td>
                                <td class="pe-3">
                                    <input type="text" name="items[{{ $item->id }}][referensi_bukti]" class="form-control form-control-sm" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->referensi_bukti }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer p-3 text-end" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                <button type="submit" class="btn rounded-pill px-4 py-2 fw-semibold shadow-sm text-white" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); font-size: 14px;">
                    <i class="bi bi-save me-1"></i> Simpan KKA Barang Cetakan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
