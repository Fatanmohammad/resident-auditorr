@extends('layouts.app')

@section('title', 'KKA Barang Cetakan - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="text-decoration-none text-muted mb-1 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kontrol Pelaksanaan
            </a>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-journal-check text-primary me-2"></i> WP-RA-05-F | KKA PERSEDIAAN BARANG CETAKAN
            </h3>
            <small class="text-muted">Membandingkan persediaan menurut catatan petugas dengan hasil hitung fisik RA (Sheet Excel: KKA_Kartu_Dok).</small>
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
                <small class="text-muted d-block text-uppercase fw-bold">Catatan Petugas Total</small>
                <span class="fw-bold fs-5 text-dark">{{ number_format($catatanTotal) }} Pcs</span>
            </div>
            <div class="col-md-3 border-end">
                <small class="text-muted d-block text-uppercase fw-bold">Hasil Fisik Total</small>
                <span class="fw-bold fs-5 text-primary">{{ number_format($fisikTotal) }} Pcs</span>
            </div>
            <div class="col-md-3 border-end">
                <small class="text-muted d-block text-uppercase fw-bold">Selisih Total</small>
                <span class="fw-bold fs-5 {{ $selisihTotal != 0 ? 'text-danger' : 'text-success' }}">{{ number_format($selisihTotal) }} Pcs</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block text-uppercase fw-bold">Exception Aktif</small>
                <span class="badge {{ $exceptionAktifCount > 0 ? 'bg-danger' : 'bg-success' }} fs-6 px-3 py-1 mt-1">
                    {{ $exceptionAktifCount }} Temuan
                </span>
            </div>
        </div>
    </div>

    <!-- Form Matriks -->
    <form action="{{ route('surprise_audit.barang_cetakan.store', $assignment->id) }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-table me-2 text-warning"></i> Matriks Opname Barang Cetakan (KKA_Kartu_Dok)</span>
                <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">
                    <i class="bi bi-save me-1"></i> Simpan KKA
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 text-center fs-7">
                        <thead class="table-dark text-uppercase small align-middle">
                            <tr>
                                <th style="min-width: 180px;">Jenis Barang Cetakan / Satuan</th>
                                <th style="min-width: 120px;">Catatan Petugas</th>
                                <th style="min-width: 120px;">Hasil Fisik</th>
                                <th style="min-width: 110px;" class="bg-secondary text-white">Selisih</th>
                                <th style="min-width: 140px;">Kondisi Penyimpanan</th>
                                <th style="min-width: 110px;">Status Uji</th>
                                <th style="min-width: 140px;">Sebab</th>
                                <th style="min-width: 140px;">Dampak</th>
                                <th style="min-width: 110px;">Ref. Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cetakanData as $item)
                            <tr>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][jenis_barang_cetakan]" class="form-control form-control-sm fw-bold" value="{{ $item->jenis_barang_cetakan }}">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][catatan_petugas]" class="form-control form-control-sm text-center" value="{{ $item->catatan_petugas }}">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][hasil_fisik]" class="form-control form-control-sm text-center fw-bold text-primary" value="{{ $item->hasil_fisik }}">
                                </td>
                                <td class="bg-light fw-bold {{ $item->selisih != 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $item->selisih }}
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][kondisi_penyimpanan]" class="form-select form-select-sm text-center">
                                        <option value="Aman/Terkunci" {{ $item->kondisi_penyimpanan == 'Aman/Terkunci' ? 'selected' : '' }}>Aman/Terkunci</option>
                                        <option value="Tidak Sesuai" {{ $item->kondisi_penyimpanan == 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
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
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light p-3 text-end">
                <button type="submit" class="btn btn-warning fw-bold px-4">
                    <i class="bi bi-save me-1"></i> Simpan KKA Barang Cetakan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection