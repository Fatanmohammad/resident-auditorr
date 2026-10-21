@extends('layouts.app')

@section('title', 'KKA Kartu ATM & PIN Mailer - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="text-decoration-none text-muted mb-1 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kontrol Pelaksanaan
            </a>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-credit-card text-primary me-2"></i> WP-RA-05-E | KKA REKONSILIASI KARTU ATM DAN PIN MAILER
            </h3>
            <small class="text-muted">Tiga baris tetap: Platinum, Gold, dan Silver. Kuantitas buku = saldo persediaan / harga per kartu.</small>
        </div>
        <div>
            <span class="badge bg-dark fs-6 px-3 py-2 rounded-pill shadow-sm">
                {{ $assignment->visit_id }} | {{ $assignment->nama_unit }}
            </span>
        </div>
    </div>

    <!-- Alert Sukses -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-3" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Banner Informasi Header KKA -->
    <div class="card border-0 bg-light shadow-sm rounded-3 p-3 mb-4">
        <div class="row text-center border-bottom pb-2 mb-2">
            <div class="col-md-3 border-end">
                <small class="text-muted d-block text-uppercase fw-bold">Saldo Buku Total</small>
                <span class="fw-bold fs-5 text-dark">Rp {{ number_format($saldoBukuTotal, 0, ',', '.') }}</span>
            </div>
            <div class="col-md-3 border-end">
                <small class="text-muted d-block text-uppercase fw-bold">Nilai Fisik Total</small>
                <span class="fw-bold fs-5 text-primary">Rp {{ number_format($nilaiFisikTotal, 0, ',', '.') }}</span>
            </div>
            <div class="col-md-3 border-end">
                <small class="text-muted d-block text-uppercase fw-bold">Selisih Nilai Total</small>
                <span class="fw-bold fs-5 {{ $selisihNilaiTotal != 0 ? 'text-danger' : 'text-success' }}">
                    Rp {{ number_format($selisihNilaiTotal, 0, ',', '.') }}
                </span>
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
            <div class="col-md-4"><strong>Status KKA:</strong> {{ $exceptionAktifCount > 0 ? 'MEMERLUKAN TINDAKAN' : 'SESUAI' }}</div>
        </div>
    </div>

    <!-- Form & Tabel Matrix 15 Kolom -->
    <form action="{{ route('surprise_audit.kartu.store', $assignment->id) }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-table me-2 text-warning"></i> Matriks Rekonsiliasi Kartu & PIN Mailer</span>
                <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">
                    <i class="bi bi-save me-1"></i> Simpan KKA
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 text-center fs-7">
                        <thead class="table-dark text-uppercase small align-middle">
                            <tr>
                                <th style="min-width: 140px;">Lokasi Penyimpanan</th>
                                <th style="min-width: 100px;">Jenis Kartu</th>
                                <th style="min-width: 130px;">Saldo Persediaan Buku (Rp)</th>
                                <th style="min-width: 110px;">Harga per Kartu (Rp)</th>
                                <th style="min-width: 90px;" class="bg-secondary text-white">Qty Buku</th>
                                <th style="min-width: 100px;">Fisik Kartu (Pcs)</th>
                                <th style="min-width: 100px;">PIN Mailer (Pcs)</th>
                                <th style="min-width: 120px;" class="bg-secondary text-white">Nilai Fisik Kartu (Rp)</th>
                                <th style="min-width: 100px;" class="bg-secondary text-white">Selisih Qty Buku-Fisik</th>
                                <th style="min-width: 100px;" class="bg-secondary text-white">Selisih PIN-Fisik</th>
                                <th style="min-width: 110px;" class="bg-secondary text-white">Selisih Nilai (Rp)</th>
                                <th style="min-width: 110px;">Status Rekonsiliasi</th>
                                <th style="min-width: 150px;">Sebab</th>
                                <th style="min-width: 150px;">Dampak</th>
                                <th style="min-width: 120px;">Ref. Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kartuData as $item)
                            <tr>
                                <!-- 1. Lokasi -->
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][lokasi_penyimpanan]" class="form-control form-control-sm text-center" value="{{ $item->lokasi_penyimpanan }}">
                                </td>
                                <!-- 2. Jenis Kartu (Fixed) -->
                                <td class="fw-bold bg-light">{{ $item->jenis_kartu }}</td>
                                <!-- 3. Saldo Buku -->
                                <td>
                                    <input type="number" step="0.01" name="items[{{ $item->id }}][saldo_persediaan_buku]" class="form-control form-control-sm text-end fw-bold" value="{{ $item->saldo_persediaan_buku }}">
                                </td>
                                <!-- 4. Harga / Kartu -->
                                <td>
                                    <input type="number" step="0.01" name="items[{{ $item->id }}][harga_per_kartu]" class="form-control form-control-sm text-end" value="{{ $item->harga_per_kartu }}">
                                </td>
                                <!-- 5. Qty Buku (Calculated) -->
                                <td class="bg-light fw-bold">{{ number_format($item->qty_menurut_buku) }}</td>
                                <!-- 6. Fisik Kartu -->
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][jumlah_fisik_kartu]" class="form-control form-control-sm text-center fw-bold text-primary" value="{{ $item->jumlah_fisik_kartu }}">
                                </td>
                                <!-- 7. PIN Mailer -->
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][jumlah_pin_mailer]" class="form-control form-control-sm text-center fw-bold text-info" value="{{ $item->jumlah_pin_mailer }}">
                                </td>
                                <!-- 8. Nilai Fisik (Calculated) -->
                                <td class="bg-light fw-bold text-primary">Rp {{ number_format($item->nilai_fisik_kartu, 0, ',', '.') }}</td>
                                <!-- 9. Selisih Qty Buku-Fisik (Calculated) -->
                                <td class="bg-light fw-bold {{ $item->selisih_qty_buku_fisik != 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $item->selisih_qty_buku_fisik }}
                                </td>
                                <!-- 10. Selisih PIN-Fisik (Calculated) -->
                                <td class="bg-light fw-bold {{ $item->selisih_pin_fisik != 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $item->selisih_pin_fisik }}
                                </td>
                                <!-- 11. Selisih Nilai (Calculated) -->
                                <td class="bg-light fw-bold {{ $item->selisih_nilai != 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($item->selisih_nilai, 0, ',', '.') }}
                                </td>
                                <!-- 12. Status Rekonsiliasi -->
                                <td>
                                    <span class="badge {{ $item->status_rekonsiliasi == 'SESUAI' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $item->status_rekonsiliasi }}
                                    </span>
                                </td>
                                <!-- 13. Sebab -->
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][sebab]" class="form-control form-control-sm" value="{{ $item->sebab }}" placeholder="Sebab jika ada selisih">
                                </td>
                                <!-- 14. Dampak -->
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][dampak]" class="form-control form-control-sm" value="{{ $item->dampak }}" placeholder="Dampak risiko">
                                </td>
                                <!-- 15. Ref Bukti -->
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][referensi_bukti]" class="form-control form-control-sm" value="{{ $item->referensi_bukti }}" placeholder="BA-01">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light p-3 text-end">
                <button type="submit" class="btn btn-warning fw-bold px-4">
                    <i class="bi bi-save me-1"></i> Simpan Seluruh KKA Kartu & PIN Mailer
                </button>
            </div>
        </div>
    </form>
</div>
@endsection