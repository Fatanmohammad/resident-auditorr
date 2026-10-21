@extends('layouts.app')

@section('title', 'KKA Kas ATM - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="text-decoration-none text-muted mb-2 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kontrol Pelaksanaan
            </a>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-cpu text-warning me-2"></i> KKA Surprise Kas ATM (WP-RA-05-D)
            </h2>
            <p class="text-muted mb-0">Satu baris untuk satu ATM. Empat kaset disajikan sebagai kolom; hasil fisik merupakan jumlah nilai seluruh kaset.</p>
        </div>
        <div>
            <span class="badge bg-dark fs-6 px-3 py-2 rounded-pill shadow-sm">
                {{ $assignment->visit_id }} | {{ $assignment->nama_unit }}
            </span>
        </div>
    </div>

    <!-- Alert Sukses -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- HEADER RINGKASAN REKONSILIASI (WP-RA-05-D) -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-light border-0 shadow-sm p-3">
                <div class="row text-center">
                    <div class="col-md-3 border-end">
                        <small class="text-muted d-block fw-bold text-uppercase">Saldo Buku / Expected</small>
                        <span class="fw-bold fs-5 text-dark">Rp {{ number_format($saldoBukuTotal ?? 0, 2, ',', '.') }}</span>
                    </div>
                    <div class="col-md-3 border-end">
                        <small class="text-muted d-block fw-bold text-uppercase">Hasil Fisik Total</small>
                        <span class="fw-bold fs-5 text-primary">Rp {{ number_format($hasilFisikTotal ?? 0, 2, ',', '.') }}</span>
                    </div>
                    <div class="col-md-3 border-end">
                        <small class="text-muted d-block fw-bold text-uppercase">Selisih Total</small>
                        <span class="fw-bold fs-5 {{ ($selisihTotal ?? 0) != 0 ? 'text-danger' : 'text-success' }}">
                            Rp {{ number_format($selisihTotal ?? 0, 2, ',', '.') }}
                        </span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block fw-bold text-uppercase">Exception Aktif</small>
                        <span class="badge {{ ($exceptionAktifCount ?? 0) > 0 ? 'bg-danger' : 'bg-success' }} fs-6 px-3 py-1 mt-1">
                            {{ $exceptionAktifCount ?? 0 }} Temuan
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form Input Opname Kas ATM -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-4">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                    <i class="bi bi-plus-circle me-1 text-warning"></i> Input Opname ATM
                </h5>
                <form action="{{ route('surprise_audit.atm.store', $assignment->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Objek / Lokasi ATM</label>
                        <input type="text" name="objek_lokasi_atm" class="form-control" placeholder="Contoh: DBL LWK / ATM KCP Bunta" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Saldo Buku / Expected (CBS)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">Rp</span>
                            <input type="number" step="0.01" name="saldo_buku" class="form-control" placeholder="0" required>
                        </div>
                    </div>

                    <hr class="text-muted my-3">
                    <h6 class="fw-bold text-primary mb-2"><i class="bi bi-stack me-1"></i> Rincian Kaset ATM</h6>

                    <!-- Kaset 1 -->
                    <div class="p-3 bg-light rounded-3 mb-2">
                        <label class="form-label fw-bold small text-secondary mb-1">Kaset 1 (Pecahan 100k)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="kaset1_pecahan" class="form-control form-control-sm" value="100000" readonly>
                            </div>
                            <div class="col-6">
                                <input type="number" name="kaset1_lembar" class="form-control form-control-sm" placeholder="Jml Lembar">
                            </div>
                        </div>
                    </div>

                    <!-- Kaset 2 -->
                    <div class="p-3 bg-light rounded-3 mb-2">
                        <label class="form-label fw-bold small text-secondary mb-1">Kaset 2 (Pecahan 50k)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="kaset2_pecahan" class="form-control form-control-sm" value="50000" readonly>
                            </div>
                            <div class="col-6">
                                <input type="number" name="kaset2_lembar" class="form-control form-control-sm" placeholder="Jml Lembar">
                            </div>
                        </div>
                    </div>

                    <!-- Kaset 3 -->
                    <div class="p-3 bg-light rounded-3 mb-2">
                        <label class="form-label fw-bold small text-secondary mb-1">Kaset 3 (Pecahan 100k)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="kaset3_pecahan" class="form-control form-control-sm" value="100000" readonly>
                            </div>
                            <div class="col-6">
                                <input type="number" name="kaset3_lembar" class="form-control form-control-sm" placeholder="Jml Lembar">
                            </div>
                        </div>
                    </div>

                    <!-- Kaset 4 -->
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <label class="form-label fw-bold small text-secondary mb-1">Kaset 4 (Pecahan 50k)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="kaset4_pecahan" class="form-control form-control-sm" value="50000" readonly>
                            </div>
                            <div class="col-6">
                                <input type="number" name="kaset4_lembar" class="form-control form-control-sm" placeholder="Jml Lembar">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Sebab (Jika Ada Selisih)</label>
                        <textarea name="sebab" class="form-control" rows="2" placeholder="Analisis penyebab..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Dampak</label>
                        <textarea name="dampak" class="form-control" rows="2" placeholder="Dampak temuan..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 fw-bold text-dark shadow-sm py-2">
                        <i class="bi bi-save me-1"></i> Simpan Data Opname ATM
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabel Rekapitulasi Kas ATM -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-table me-2 text-warning"></i> Rekapitulasi Perhitungan KKA Kas ATM</span>
                    <span class="badge bg-light text-dark">WP-RA-05-D</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0 text-center fs-7">
                            <thead class="table-light text-uppercase text-secondary">
                                <tr>
                                    <th rowspan="2" class="align-middle ps-3">Objek/Lokasi</th>
                                    <th rowspan="2" class="align-middle">Saldo Buku</th>
                                    <th colspan="3" class="bg-light">Kaset 1</th>
                                    <th colspan="3" class="bg-light">Kaset 2</th>
                                    <th colspan="3" class="bg-light">Kaset 3</th>
                                    <th colspan="3" class="bg-light">Kaset 4</th>
                                    <th rowspan="2" class="align-middle">Total Fisik</th>
                                    <th rowspan="2" class="align-middle">Selisih</th>
                                    <th rowspan="2" class="align-middle">Status Uji</th>
                                    <th rowspan="2" class="align-middle pe-3">Exception</th>
                                </tr>
                                <tr>
                                    <th>Pec</th>
                                    <th>Lbr</th>
                                    <th>Nilai</th>
                                    <th>Pec</th>
                                    <th>Lbr</th>
                                    <th>Nilai</th>
                                    <th>Pec</th>
                                    <th>Lbr</th>
                                    <th>Nilai</th>
                                    <th>Pec</th>
                                    <th>Lbr</th>
                                    <th>Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($atmData ?? [] as $row)
                                <tr>
                                    <td class="text-start fw-bold ps-3">{{ $row->objek_lokasi_atm }}</td>
                                    <td class="text-end">Rp {{ number_format($row->saldo_buku, 0, ',', '.') }}</td>
                                    <td>{{ number_format($row->kaset1_pecahan, 0) }}</td>
                                    <td>{{ $row->kaset1_lembar }}</td>
                                    <td class="text-end">Rp {{ number_format($row->kaset1_nilai, 0, ',', '.') }}</td>
                                    <td>{{ number_format($row->kaset2_pecahan, 0) }}</td>
                                    <td>{{ $row->kaset2_lembar }}</td>
                                    <td class="text-end">Rp {{ number_format($row->kaset2_nilai, 0, ',', '.') }}</td>
                                    <td>{{ number_format($row->kaset3_pecahan, 0) }}</td>
                                    <td>{{ $row->kaset3_lembar }}</td>
                                    <td class="text-end">Rp {{ number_format($row->kaset3_nilai, 0, ',', '.') }}</td>
                                    <td>{{ number_format($row->kaset4_pecahan, 0) }}</td>
                                    <td>{{ $row->kaset4_lembar }}</td>
                                    <td class="text-end">Rp {{ number_format($row->kaset4_nilai, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold text-primary">Rp {{ number_format($row->hasil_fisik_total, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold {{ $row->selisih != 0 ? 'text-danger' : 'text-success' }}">
                                        Rp {{ number_format($row->selisih, 0, ',', '.') }}
                                    </td>
                                    <td><span class="badge bg-info">{{ $row->status_uji }}</span></td>
                                    <td>
                                        <span class="badge {{ $row->exception_aktif == 'Ya' ? 'bg-danger' : 'bg-success' }}">
                                            {{ $row->exception_aktif }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="18" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        Belum ada data opname kas ATM yang diinput.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection