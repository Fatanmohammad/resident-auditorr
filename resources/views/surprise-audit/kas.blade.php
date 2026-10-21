@extends('layouts.app')

@section('title', 'KKA Kas - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="text-decoration-none text-muted mb-2 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kontrol Pelaksanaan
            </a>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-cash-stack text-warning me-2"></i> KKA Kas Khasanah & Teller (WP-RA-05-C)
            </h2>
            <p class="text-muted mb-0">Pemeriksaan Rincian Pecahan Uang (ULE, UTLE, Logam) vs Saldo Sistem</p>
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

    <!-- Header Ringkasan Rekonsiliasi -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-light border-0 shadow-sm p-3">
                <div class="row text-center">
                    <div class="col-md-3 border-end">
                        <small class="text-muted d-block fw-bold text-uppercase">Total Saldo Sistem (CBS)</small>
                        <span class="fw-bold fs-5 text-dark">Rp {{ number_format($totalSistem ?? 0, 2, ',', '.') }}</span>
                    </div>
                    <div class="col-md-3 border-end">
                        <small class="text-muted d-block fw-bold text-uppercase">Total Hasil Fisik</small>
                        <span class="fw-bold fs-5 text-primary">Rp {{ number_format($totalFisik ?? 0, 2, ',', '.') }}</span>
                    </div>
                    <div class="col-md-3 border-end">
                        <small class="text-muted d-block fw-bold text-uppercase">Selisih Total</small>
                        <span class="fw-bold fs-5 {{ ($totalSelisih ?? 0) != 0 ? 'text-danger' : 'text-success' }}">
                            Rp {{ number_format($totalSelisih ?? 0, 2, ',', '.') }}
                        </span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block fw-bold text-uppercase">Exception Aktif</small>
                        <span class="badge {{ ($exceptionAktif ?? 0) > 0 ? 'bg-danger' : 'bg-success' }} fs-6 px-3 py-1 mt-1">
                            {{ $exceptionAktif ?? 0 }} Temuan
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form Input Rincian Pecahan Uang -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-3 p-4">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                    <i class="bi bi-plus-circle me-1 text-warning"></i> Input Opname Rincian Pecahan
                </h5>
                <form action="{{ route('surprise_audit.kas.store', $assignment->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pos Kas / Objek Pemeriksaan</label>
                        <select name="objek_pemeriksaan" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Pos Kas --</option>
                            <option value="Kas Khasanah (Brankas Utama)">Kas Khasanah (Brankas Utama)</option>
                            <option value="Kas Teller 01">Kas Teller 01</option>
                            <option value="Kas Teller 02">Kas Teller 02</option>
                            <option value="Kas Teller 03">Kas Teller 03</option>
                            <option value="Kas Sub Vault">Kas Sub Vault</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Saldo Sistem (CBS)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">Rp</span>
                            <input type="number" step="0.01" name="nilai_sistem" class="form-control" placeholder="0" required>
                        </div>
                    </div>

                    <!-- Accordion Input Pecahan ULE, UTLE, Logam -->
                    <div class="accordion mb-3" id="accordionPecahan">
                        <!-- ULE (Layak Edar) -->
                        <div class="accordion-item border-0 bg-light rounded-3 mb-2">
                            <h2 class="accordion-header" id="headingUle">
                                <button class="accordion-button fw-bold text-success bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUle" aria-expanded="true">
                                    <i class="bi bi-cash me-2"></i> Uang Layak Edar (ULE) - Kertas
                                </button>
                            </h2>
                            <div id="collapseUle" class="accordion-collapse collapse show" data-bs-parent="#accordionPecahan">
                                <div class="accordion-body small">
                                    <div class="row g-2">
                                        <div class="col-6"><label>Rp 100.000 (Lbr)</label><input type="number" name="ule_100k" class="form-control form-control-sm" placeholder="0"></div>
                                        <div class="col-6"><label>Rp 50.000 (Lbr)</label><input type="number" name="ule_50k" class="form-control form-control-sm" placeholder="0"></div>
                                        <div class="col-6"><label>Rp 20.000 (Lbr)</label><input type="number" name="ule_20k" class="form-control form-control-sm" placeholder="0"></div>
                                        <div class="col-6"><label>Rp 10.000 (Lbr)</label><input type="number" name="ule_10k" class="form-control form-control-sm" placeholder="0"></div>
                                        <div class="col-6"><label>Rp 5.000 (Lbr)</label><input type="number" name="ule_5k" class="form-control form-control-sm" placeholder="0"></div>
                                        <div class="col-6"><label>Rp 2.000 (Lbr)</label><input type="number" name="ule_2k" class="form-control form-control-sm" placeholder="0"></div>
                                        <div class="col-6"><label>Rp 1.000 (Lbr)</label><input type="number" name="ule_1k" class="form-control form-control-sm" placeholder="0"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- UTLE (Tidak Layak Edar) -->
                        <div class="accordion-item border-0 bg-light rounded-3 mb-2">
                            <h2 class="accordion-header" id="headingUtle">
                                <button class="accordion-button collapsed fw-bold text-danger bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUtle">
                                    <i class="bi bi-journal-x me-2"></i> Uang Tidak Layak Edar (UTLE)
                                </button>
                            </h2>
                            <div id="collapseUtle" class="accordion-collapse collapse" data-bs-parent="#accordionPecahan">
                                <div class="accordion-body small">
                                    <div class="row g-2">
                                        <div class="col-6"><label>Rp 100.000 (Lbr)</label><input type="number" name="utle_100k" class="form-control form-control-sm" placeholder="0"></div>
                                        <div class="col-6"><label>Rp 50.000 (Lbr)</label><input type="number" name="utle_50k" class="form-control form-control-sm" placeholder="0"></div>
                                        <div class="col-6"><label>Rp 20.000 (Lbr)</label><input type="number" name="utle_20k" class="form-control form-control-sm" placeholder="0"></div>
                                        <div class="col-6"><label>Rp 10.000 (Lbr)</label><input type="number" name="utle_10k" class="form-control form-control-sm" placeholder="0"></div>
                                        <div class="col-6"><label>Rp 5.000 (Lbr)</label><input type="number" name="utle_5k" class="form-control form-control-sm" placeholder="0"></div>
                                        <div class="col-6"><label>Rp 2.000 (Lbr)</label><input type="number" name="utle_2k" class="form-control form-control-sm" placeholder="0"></div>
                                        <div class="col-6"><label>Rp 1.000 (Lbr)</label><input type="number" name="utle_1k" class="form-control form-control-sm" placeholder="0"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Logam -->
                        <div class="accordion-item border-0 bg-light rounded-3 mb-2">
                            <h2 class="accordion-header" id="headingLogam">
                                <button class="accordion-button collapsed fw-bold text-secondary bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLogam">
                                    <i class="bi bi-coin me-2"></i> Uang Logam (Keping)
                                </button>
                            </h2>
                            <div id="collapseLogam" class="accordion-collapse collapse" data-bs-parent="#accordionPecahan">
                                <div class="accordion-body small">
                                    <div class="row g-2">
                                        <div class="col-6"><label>Rp 1.000 (Keping)</label><input type="number" name="logam_1000" class="form-control form-control-sm" placeholder="0"></div>
                                        <div class="col-6"><label>Rp 500 (Keping)</label><input type="number" name="logam_500" class="form-control form-control-sm" placeholder="0"></div>
                                        <div class="col-6"><label>Rp 200 (Keping)</label><input type="number" name="logam_200" class="form-control form-control-sm" placeholder="0"></div>
                                        <div class="col-6"><label>Rp 100 (Keping)</label><input type="number" name="logam_100" class="form-control form-control-sm" placeholder="0"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Catatan / Keterangan Pemeriksa</label>
                        <textarea name="catatan_pemeriksa" class="form-control" rows="2" placeholder="Catatan selisih, kondisi segel brankas, dll."></textarea>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 fw-bold text-dark shadow-sm py-2">
                        <i class="bi bi-save me-1"></i> Simpan Data Rincian Pecahan
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabel Rekapitulasi Rincian Kas -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-table me-2 text-warning"></i> Rekapitulasi Opname Kas</span>
                    <span class="badge bg-light text-dark">WP-RA-05-C</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center fs-7">
                            <thead class="table-light text-uppercase text-secondary">
                                <tr>
                                    <th class="py-3 ps-3 text-start">Pos Kas</th>
                                    <th class="py-3 text-end">CBS</th>
                                    <th class="py-3 text-end">ULE</th>
                                    <th class="py-3 text-end">UTLE</th>
                                    <th class="py-3 text-end">Logam</th>
                                    <th class="py-3 text-end">Total Fisik</th>
                                    <th class="py-3 text-end">Selisih</th>
                                    <th class="py-3 pe-3">Exception</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kasData as $row)
                                <tr>
                                    <td class="text-start fw-bold ps-3">{{ $row->objek_pemeriksaan }}</td>
                                    <td class="text-end">Rp {{ number_format($row->nilai_sistem, 0, ',', '.') }}</td>
                                    <td class="text-end text-success">Rp {{ number_format($row->total_ule, 0, ',', '.') }}</td>
                                    <td class="text-end text-danger">Rp {{ number_format($row->total_utle, 0, ',', '.') }}</td>
                                    <td class="text-end text-secondary">Rp {{ number_format($row->total_logam, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold text-primary">Rp {{ number_format($row->nilai_fisik, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold {{ $row->selisih != 0 ? 'text-danger' : 'text-success' }}">
                                        Rp {{ number_format($row->selisih, 0, ',', '.') }}
                                    </td>
                                    <td class="pe-3">
                                        <span class="badge {{ $row->exception_aktif == 'Ya' ? 'bg-danger' : 'bg-success' }}">
                                            {{ $row->exception_aktif }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        Belum ada data opname kas yang diinput.
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