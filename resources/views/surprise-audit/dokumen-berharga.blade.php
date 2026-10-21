@extends('layouts.app')

@section('title', 'KKA Dokumen Berharga - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="text-decoration-none text-muted mb-1 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kontrol Pelaksanaan
            </a>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-file-earmark-check text-primary me-2"></i> WP-RA-05-G | KKA DOKUMEN BERHARGA
            </h3>
            <small class="text-muted">Tiga baris tetap: Cek, BG, dan Bilyet Deposito. Harga satuan diterapkan untuk Cek dan BG.</small>
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

    <!-- Banner Ringkasan Header KKA -->
    <div class="card border-0 bg-light shadow-sm rounded-3 p-3 mb-4">
        <div class="row text-center border-bottom pb-2 mb-2">
            <div class="col-md-3 border-end">
                <small class="text-muted d-block text-uppercase fw-bold">Qty Sistem Total</small>
                <span class="fw-bold fs-5 text-dark">{{ number_format($qtySistemTotal) }} Lembar</span>
            </div>
            <div class="col-md-3 border-end">
                <small class="text-muted d-block text-uppercase fw-bold">Qty Catatan Total</small>
                <span class="fw-bold fs-5 text-secondary">{{ number_format($qtyCatatanTotal) }} Lembar</span>
            </div>
            <div class="col-md-3 border-end">
                <small class="text-muted d-block text-uppercase fw-bold">Qty Fisik Total</small>
                <span class="fw-bold fs-5 text-primary">{{ number_format($qtyFisikTotal) }} Lembar</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block text-uppercase fw-bold">Exception Aktif</small>
                <span class="badge {{ $exceptionAktifCount > 0 ? 'bg-danger' : 'bg-success' }} fs-6 px-3 py-1 mt-1">
                    {{ $exceptionAktifCount }} Temuan
                </span>
            </div>
        </div>
        <div class="row text-start small text-muted pt-1">
            <div class="col-md-4"><strong>Pelaksana:</strong> {{ $assignment->resident_auditor }}</div>
            <div class="col-md-4"><strong>Reviewer:</strong> {{ $assignment->reviewer }}</div>
            <div class="col-md-4"><strong>Status KKA:</strong> {{ $exceptionAktifCount > 0 ? 'BELUM LENGKAP' : 'LENGKAP' }}</div>
        </div>
    </div>

    <!-- Form Matriks Dokumen Berharga -->
    <form action="{{ route('surprise_audit.dokumen_berharga.store', $assignment->id) }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-table me-2 text-warning"></i> Matriks Opname & Rekonsiliasi Dokumen Berharga</span>
                <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">
                    <i class="bi bi-save me-1"></i> Simpan KKA
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 text-center fs-7">
                        <thead class="table-dark text-uppercase small align-middle">
                            <tr>
                                <th style="min-width: 120px;">Jenis Dokumen</th>
                                <th style="min-width: 110px;">Persediaan Sistem (Qty)</th>
                                <th style="min-width: 110px;">Catatan Petugas (Qty)</th>
                                <th style="min-width: 110px;">Hasil Fisik (Qty)</th>
                                <th style="min-width: 110px;">Harga per Lembar</th>
                                <th style="min-width: 120px;" class="bg-secondary text-white">Nilai Sistem</th>
                                <th style="min-width: 120px;" class="bg-secondary text-white">Nilai Fisik</th>
                                <th style="min-width: 110px;" class="bg-secondary text-white">Selisih Sistem-Catatan</th>
                                <th style="min-width: 110px;" class="bg-secondary text-white">Selisih Sistem-Fisik</th>
                                <th style="min-width: 110px;" class="bg-secondary text-white">Selisih Catatan-Fisik</th>
                                <th style="min-width: 120px;" class="bg-secondary text-white">Selisih Nilai</th>
                                <th style="min-width: 140px;">Range/Nomor Seri</th>
                                <th style="min-width: 140px;">Nomor Hilang/Lompat</th>
                                <th style="min-width: 110px;">Status Uji</th>
                                <th style="min-width: 140px;">Sebab</th>
                                <th style="min-width: 140px;">Dampak</th>
                                <th style="min-width: 110px;">Ref. Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dokumenData as $item)
                            <tr>
                                <!-- Jenis Dokumen (Fixed) -->
                                <td class="fw-bold bg-light">{{ $item->jenis_dokumen }}</td>
                                
                                <!-- Persediaan Sistem (Qty) -->
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][persediaan_sistem_qty]" class="form-control form-control-sm text-center fw-bold" value="{{ $item->persediaan_sistem_qty }}">
                                </td>
                                
                                <!-- Catatan Petugas (Qty) -->
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][catatan_petugas_qty]" class="form-control form-control-sm text-center" value="{{ $item->catatan_petugas_qty }}">
                                </td>
                                
                                <!-- Hasil Fisik (Qty) -->
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][hasil_fisik_qty]" class="form-control form-control-sm text-center fw-bold text-primary" value="{{ $item->hasil_fisik_qty }}">
                                </td>
                                
                                <!-- Harga per Lembar -->
                                <td>
                                    @if($item->jenis_dokumen == 'Bilyet Deposito')
                                        <span class="text-muted small">Tidak diterapkan</span>
                                        <input type="hidden" name="items[{{ $item->id }}][harga_per_lembar]" value="0">
                                    @else
                                        <input type="number" step="0.01" name="items[{{ $item->id }}][harga_per_lembar]" class="form-control form-control-sm text-end" value="{{ $item->harga_per_lembar }}">
                                    @endif
                                </td>

                                <!-- Nilai Sistem (Calculated) -->
                                <td class="bg-light text-end fw-bold">Rp {{ number_format($item->nilai_sistem, 0, ',', '.') }}</td>
                                
                                <!-- Nilai Fisik (Calculated) -->
                                <td class="bg-light text-end fw-bold text-primary">Rp {{ number_format($item->nilai_fisik, 0, ',', '.') }}</td>
                                
                                <!-- Selisih Sistem-Catatan -->
                                <td class="bg-light fw-bold {{ $item->selisih_sistem_catatan != 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $item->selisih_sistem_catatan }}
                                </td>
                                
                                <!-- Selisih Sistem-Fisik -->
                                <td class="bg-light fw-bold {{ $item->selisih_sistem_fisik != 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $item->selisih_sistem_fisik }}
                                </td>

                                <!-- Selisih Catatan-Fisik -->
                                <td class="bg-light fw-bold {{ $item->selisih_catatan_fisik != 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $item->selisih_catatan_fisik }}
                                </td>

                                <!-- Selisih Nilai -->
                                <td class="bg-light text-end fw-bold {{ $item->selisih_nilai != 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($item->selisih_nilai, 0, ',', '.') }}
                                </td>

                                <!-- Range Nomor Seri -->
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][range_nomor_seri]" class="form-control form-control-sm" value="{{ $item->range_nomor_seri }}" placeholder="CK-001 s.d CK-100">
                                </td>

                                <!-- Nomor Hilang/Lompat -->
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][nomor_hilang_lompat]" class="form-control form-control-sm" value="{{ $item->nomor_hilang_lompat }}" placeholder="CK-045 hilang">
                                </td>

                                <!-- Status Uji -->
                                <td>
                                    <span class="badge {{ $item->status_uji == 'LENGKAP' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ $item->status_uji }}
                                    </span>
                                </td>

                                <!-- Sebab -->
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][sebab]" class="form-control form-control-sm" value="{{ $item->sebab }}">
                                </td>

                                <!-- Dampak -->
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][dampak]" class="form-control form-control-sm" value="{{ $item->dampak }}">
                                </td>

                                <!-- Ref Bukti -->
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][referensi_bukti]" class="form-control form-control-sm" value="{{ $item->referensi_bukti }}" placeholder="BA-02">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light p-3 text-end">
                <button type="submit" class="btn btn-warning fw-bold px-4">
                    <i class="bi bi-save me-1"></i> Simpan Seluruh KKA Dokumen Berharga
                </button>
            </div>
        </div>
    </form>
</div>
@endsection