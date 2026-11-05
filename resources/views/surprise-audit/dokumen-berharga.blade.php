@extends('layouts.app')

@section('title', 'KKA Dokumen Berharga - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3" style="background-color: #f8fafc; min-height: 100vh;">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="text-decoration-none text-muted mb-2 d-inline-block fw-semibold" style="font-size: 14px;">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kontrol Pelaksanaan
            </a>
            <h3 class="fw-bold mb-1" style="color: #0f2e5a; letter-spacing: -0.3px;">
                <i class="bi bi-file-earmark-check text-primary me-2"></i> WP-RA-05-G | KKA Dokumen Berharga
            </h3>
            <p class="text-muted mb-0" style="font-size: 14px;">
                Tiga baris tetap: Cek, BG, dan Bilyet Deposito. Harga satuan diterapkan untuk Cek dan BG.
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

    <!-- Banner Ringkasan Header KKA (Soft Executive Metric Cards) -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid #3b82f6 !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Qty Sistem Total</small>
                <div class="fw-bold mt-1" style="font-size: 1.35rem; color: #1e293b;">
                    {{ number_format($qtySistemTotal) }} <span style="font-size: 0.9rem; font-weight: 500;" class="text-muted">Lembar</span>
                </div>
                <div class="small text-muted mt-1" style="font-size: 12px;">Persediaan sistem core banking</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid #64748b !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Qty Catatan Petugas</small>
                <div class="fw-bold mt-1" style="font-size: 1.35rem; color: #334155;">
                    {{ number_format($qtyCatatanTotal) }} <span style="font-size: 0.9rem; font-weight: 500;" class="text-muted">Lembar</span>
                </div>
                <div class="small text-muted mt-1" style="font-size: 12px;">Catatan register unit cabang</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid #0284c7 !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Qty Fisik Total</small>
                <div class="fw-bold mt-1" style="font-size: 1.35rem; color: #0f2e5a;">
                    {{ number_format($qtyFisikTotal) }} <span style="font-size: 0.9rem; font-weight: 500;" class="text-muted">Lembar</span>
                </div>
                <div class="small text-muted mt-1" style="font-size: 12px;">Hasil opname fisik RA</div>
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

    <!-- Info Pelaksana & Reviewer -->
    <div class="card border-0 shadow-sm rounded-3 p-3 mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important;">
        <div class="row align-items-center text-muted" style="font-size: 13.5px;">
            <div class="col-md-4"><strong>Pelaksana (RA):</strong> <span class="text-dark fw-semibold">{{ $assignment->resident_auditor }}</span></div>
            <div class="col-md-4"><strong>Reviewer SKAI:</strong> <span class="text-dark fw-semibold">{{ $assignment->reviewer }}</span></div>
            <div class="col-md-4">
                <strong>Status KKA:</strong> 
                <span class="badge rounded-pill px-3 py-1 ms-1" style="{{ $exceptionAktifCount > 0 ? 'background-color: #fee2e2; color: #991b1b;' : 'background-color: #f0fdf4; color: #166534;' }} font-size: 12px; font-weight: 600;">
                    {{ $exceptionAktifCount > 0 ? 'BELUM LENGKAP / SELISIH' : 'LENGKAP / SESUAI' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Form Matriks Dokumen Berharga -->
    <form action="{{ route('surprise_audit.dokumen_berharga.store', $assignment->id) }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-3 mb-5" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; overflow: hidden;">
            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); color: #fff;">
                <span class="fw-bold" style="font-size: 14.5px;">
                    <i class="bi bi-table me-2 text-info"></i> Matriks Opname & Rekonsiliasi Dokumen Berharga
                </span>
                <button type="submit" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold shadow-sm text-white" style="background-color: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); font-size: 13px;">
                    <i class="bi bi-save me-1"></i> Simpan KKA
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 text-center" style="font-size: 13px;">
                        <thead style="background-color: #f1f5f9; color: #1e293b; font-weight: 700; font-size: 12px;" class="text-uppercase">
                            <tr>
                                <th style="min-width: 140px;" class="py-3 text-start ps-3">Jenis Dokumen</th>
                                <th style="min-width: 110px;" class="py-3">Sistem (Qty)</th>
                                <th style="min-width: 110px;" class="py-3">Catatan (Qty)</th>
                                <th style="min-width: 110px;" class="py-3">Fisik (Qty)</th>
                                <th style="min-width: 130px;" class="py-3">Harga/Lembar</th>
                                <th style="min-width: 130px; background-color: #eff6ff; color: #1e40af;" class="py-3">Nilai Sistem</th>
                                <th style="min-width: 130px; background-color: #eff6ff; color: #1e40af;" class="py-3">Nilai Fisik</th>
                                <th style="min-width: 100px; background-color: #f8fafc;" class="py-3">Sel. Sist-Cat</th>
                                <th style="min-width: 100px; background-color: #f8fafc;" class="py-3">Sel. Sist-Fis</th>
                                <th style="min-width: 100px; background-color: #f8fafc;" class="py-3">Sel. Cat-Fis</th>
                                <th style="min-width: 130px; background-color: #f8fafc;" class="py-3">Selisih Nilai</th>
                                <th style="min-width: 140px;" class="py-3">Range No. Seri</th>
                                <th style="min-width: 140px;" class="py-3">No. Hilang/Lompat</th>
                                <th style="min-width: 110px;" class="py-3">Status</th>
                                <th style="min-width: 130px;" class="py-3">Sebab</th>
                                <th style="min-width: 130px;" class="py-3">Dampak</th>
                                <th style="min-width: 110px;" class="py-3 pe-3">Ref. Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dokumenData as $item)
                            <tr>
                                <!-- Jenis Dokumen (Fixed) -->
                                <td class="fw-bold text-start ps-3" style="color: #0f2e5a; font-size: 13.5px;">
                                    {{ $item->jenis_dokumen }}
                                </td>
                                
                                <!-- Persediaan Sistem (Qty) -->
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][persediaan_sistem_qty]" class="form-control form-control-sm text-center fw-bold" style="font-size: 13px; height: 38px; border-color: #cbd5e1;" value="{{ $item->persediaan_sistem_qty }}">
                                </td>
                                
                                <!-- Catatan Petugas (Qty) -->
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][catatan_petugas_qty]" class="form-control form-control-sm text-center" style="font-size: 13px; height: 38px; border-color: #cbd5e1;" value="{{ $item->catatan_petugas_qty }}">
                                </td>
                                
                                <!-- Hasil Fisik (Qty) -->
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][hasil_fisik_qty]" class="form-control form-control-sm text-center fw-bold" style="font-size: 13px; height: 38px; border-color: #cbd5e1; color: #0f2e5a;" value="{{ $item->hasil_fisik_qty }}">
                                </td>
                                
                                <!-- Harga per Lembar -->
                                <td>
                                    @if($item->jenis_dokumen == 'Bilyet Deposito')
                                        <span class="text-muted small" style="font-size: 11.5px;">Tidak Diterapkan</span>
                                        <input type="hidden" name="items[{{ $item->id }}][harga_per_lembar]" value="0">
                                    @else
                                        <input type="number" step="0.01" name="items[{{ $item->id }}][harga_per_lembar]" class="form-control form-control-sm text-end" style="font-size: 13px; height: 38px; border-color: #cbd5e1;" value="{{ $item->harga_per_lembar }}">
                                    @endif
                                </td>

                                <!-- Nilai Sistem (Calculated) -->
                                <td class="text-end fw-bold" style="background-color: #eff6ff; color: #1e293b; font-size: 13px;">
                                    Rp {{ number_format($item->nilai_sistem, 0, ',', '.') }}
                                </td>
                                
                                <!-- Nilai Fisik (Calculated) -->
                                <td class="text-end fw-bold" style="background-color: #eff6ff; color: #0f2e5a; font-size: 13px;">
                                    Rp {{ number_format($item->nilai_fisik, 0, ',', '.') }}
                                </td>
                                
                                <!-- Selisih Sistem-Catatan -->
                                <td class="fw-bold {{ $item->selisih_sistem_catatan != 0 ? 'text-danger' : 'text-success' }}" style="background-color: #f8fafc; font-size: 13px;">
                                    {{ $item->selisih_sistem_catatan }}
                                </td>
                                
                                <!-- Selisih Sistem-Fisik -->
                                <td class="fw-bold {{ $item->selisih_sistem_fisik != 0 ? 'text-danger' : 'text-success' }}" style="background-color: #f8fafc; font-size: 13px;">
                                    {{ $item->selisih_sistem_fisik }}
                                </td>

                                <!-- Selisih Catatan-Fisik -->
                                <td class="fw-bold {{ $item->selisih_catatan_fisik != 0 ? 'text-danger' : 'text-success' }}" style="background-color: #f8fafc; font-size: 13px;">
                                    {{ $item->selisih_catatan_fisik }}
                                </td>

                                <!-- Selisih Nilai -->
                                <td class="text-end fw-bold {{ $item->selisih_nilai != 0 ? 'text-danger' : 'text-success' }}" style="background-color: #f8fafc; font-size: 13px;">
                                    Rp {{ number_format($item->selisih_nilai, 0, ',', '.') }}
                                </td>

                                <!-- Range Nomor Seri -->
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][range_nomor_seri]" class="form-control form-control-sm" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->range_nomor_seri }}" placeholder="CK-001 s.d CK-100">
                                </td>

                                <!-- Nomor Hilang/Lompat -->
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][nomor_hilang_lompat]" class="form-control form-control-sm" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->nomor_hilang_lompat }}" placeholder="CK-045 hilang">
                                </td>

                                <!-- Status Uji -->
                                <td>
                                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="{{ $item->status_uji == 'LENGKAP' ? 'background-color: #f0fdf4; color: #166534;' : 'background-color: #fffbeb; color: #b45309;' }} font-size: 11.5px;">
                                        {{ $item->status_uji }}
                                    </span>
                                </td>

                                <!-- Sebab -->
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][sebab]" class="form-control form-control-sm" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->sebab }}">
                                </td>

                                <!-- Dampak -->
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][dampak]" class="form-control form-control-sm" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->dampak }}">
                                </td>

                                <!-- Ref Bukti -->
                                <td class="pe-3">
                                    <input type="text" name="items[{{ $item->id }}][referensi_bukti]" class="form-control form-control-sm" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->referensi_bukti }}" placeholder="BA-02">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer p-3 text-end" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                <button type="submit" class="btn rounded-pill px-4 py-2 fw-semibold shadow-sm text-white" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); font-size: 14px;">
                    <i class="bi bi-save me-1"></i> Simpan Seluruh KKA Dokumen Berharga
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
