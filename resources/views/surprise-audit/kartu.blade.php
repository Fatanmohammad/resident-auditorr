@extends('layouts.app')

@section('title', 'KKA Kartu ATM & PIN Mailer - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3" style="background-color: #f8fafc; min-height: 100vh;">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="text-decoration-none text-muted mb-2 d-inline-block fw-semibold" style="font-size: 14px;">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kontrol Pelaksanaan
            </a>
            <h3 class="fw-bold mb-1" style="color: #0f2e5a; letter-spacing: -0.3px;">
                <i class="bi bi-credit-card me-2 text-primary"></i> WP-RA-05-E | Rekonsiliasi Kartu ATM & PIN Mailer
            </h3>
            <p class="text-muted mb-0" style="font-size: 14px;">
                Standar Bank Sulteng: Rekonsiliasi Fisik vs Saldo Persediaan GL vs Sistem Cardman & Komparasi PIN Mailer
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('surprise_audit.kartu.berita_acara', $assignment->visit_id) }}" class="btn rounded-pill px-3 py-2 shadow-sm fw-semibold" style="background-color: #0f2e5a; color: #fff; font-size: 13.5px;">
                <i class="bi bi-printer-fill me-1"></i> Cetak Rekonsiliasi Resmi
            </a>
            <span class="badge px-3 py-2 rounded-pill shadow-sm" style="background-color: #1e3a8a; color: #fff; font-size: 13px; font-weight: 600;">
                <i class="bi bi-building me-1"></i> {{ $assignment->visit_id }} | {{ $assignment->nama_unit }}
            </span>
        </div>
    </div>

    <!-- Alert Sukses & Error -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4 border-0" role="alert" style="background-color: #f0fdf4; color: #166534; border-left: 4px solid #22c55e !important;">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i> <span style="font-size: 14px; font-weight: 600;">{{ session('success') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Banner Informasi Rekonsiliasi Header (Soft Executive Metric Cards) -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid #0284c7 !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Total Fisik Kartu</small>
                <div class="fw-bold mt-1" style="font-size: 1.35rem; color: #0f2e5a;">
                    <span id="banner_total_qty">{{ number_format($totalFisikQty, 0, ',', '.') }}</span> <span style="font-size: 0.9rem; font-weight: 500;" class="text-muted">Pcs</span>
                </div>
                <div class="small fw-semibold mt-1" style="color: #64748b;" id="banner_total_nom">
                    Rp {{ number_format($totalNominalFisik, 0, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid #3b82f6 !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Saldo Persediaan (GL)</small>
                <div class="fw-bold mt-1" style="font-size: 1.35rem; color: #1e293b;">
                    Rp {{ number_format($saldoPersediaanGl, 0, ',', '.') }}
                </div>
                <div class="small fw-semibold mt-1" style="color: #64748b;">
                    Cardman: {{ number_format($cardmanQty, 0, ',', '.') }} Pcs
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid {{ $selisihPersediaanGl != 0 ? '#ef4444' : '#10b981' }} !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Selisih Fisik vs GL</small>
                <div class="fw-bold mt-1 {{ $selisihPersediaanGl != 0 ? 'text-danger' : 'text-success' }}" style="font-size: 1.35rem;" id="banner_selisih_gl">
                    Rp {{ number_format($selisihPersediaanGl, 0, ',', '.') }}
                </div>
                <div class="small mt-1">
                    @if($selisihPersediaanGl == 0)
                        <span class="badge rounded-pill px-2 py-1" style="background-color: #f0fdf4; color: #166534; font-size: 11px; font-weight: 600;">SESUAI / MATCH</span>
                    @else
                        <span class="badge rounded-pill px-2 py-1" style="background-color: #fee2e2; color: #991b1b; font-size: 11px; font-weight: 600;">TERDAPAT SELISIH</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-top: 3px solid {{ $selisihTotalKartuPin != 0 ? '#f59e0b' : '#10b981' }} !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">PIN Mailer vs Kartu</small>
                <div class="fw-bold mt-1 {{ $selisihTotalKartuPin != 0 ? 'text-warning' : 'text-success' }}" style="font-size: 1.35rem;" id="banner_selisih_pin">
                    {{ $selisihTotalKartuPin }} <span style="font-size: 0.9rem; font-weight: 500;" class="text-muted">Pcs</span>
                </div>
                <div class="small fw-semibold mt-1" style="color: #64748b;">
                    Fisik PIN: {{ number_format($totalPinMailer, 0, ',', '.') }} Lembar
                </div>
            </div>
        </div>
    </div>

    <!-- Info Detail Assignment -->
    <div class="card border-0 shadow-sm rounded-3 p-3 mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important;">
        <div class="row align-items-center text-muted" style="font-size: 13.5px;">
            <div class="col-md-3"><strong>Unit / Cabang:</strong> <span class="text-dark fw-semibold">{{ $assignment->nama_unit }}</span></div>
            <div class="col-md-3"><strong>Pemeriksa (RA):</strong> <span class="text-dark fw-semibold">{{ $assignment->pemeriksa_1 ?? $assignment->resident_auditor }}</span></div>
            <div class="col-md-3"><strong>Reviewer SKAI:</strong> <span class="text-dark fw-semibold">{{ $assignment->reviewer }}</span></div>
            <div class="col-md-3">
                <strong>Status KKA:</strong> 
                <span class="badge rounded-pill px-3 py-1 ms-1" style="{{ $exceptionAktifCount > 0 ? 'background-color: #fee2e2; color: #991b1b;' : 'background-color: #f0fdf4; color: #166534;' }} font-size: 12px; font-weight: 600;">
                    {{ $exceptionAktifCount > 0 ? 'MEMERLUKAN TINDAKAN (' . $exceptionAktifCount . ')' : 'SESUAI' }}
                </span>
            </div>
        </div>
    </div>

    <!-- FORM MATRIKS KARTU ATM & PIN MAILER -->
    <form action="{{ route('surprise_audit.kartu.store', $assignment->id) }}" method="POST" enctype="multipart/form-data" id="formKartuAudit">
        @csrf

        <!-- Parameter Header Audit -->
        <div class="card border-0 shadow-sm rounded-3 p-3 mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important;">
            <h6 class="fw-bold mb-3 pb-2 border-bottom" style="color: #0f2e5a; font-size: 14.5px;">
                <i class="bi bi-sliders text-primary me-2"></i> Parameter Rekonsiliasi Bank Sulteng
            </h6>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted" style="font-size: 12.5px;">Periode Audit</label>
                    <input type="text" name="periode_audit" class="form-control" style="font-size: 13.5px; height: 40px; border-color: #cbd5e1;" value="{{ $kartuData->first()->periode_audit ?? ('SEMESTER 2 ' . date('Y')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted" style="font-size: 12.5px;">Periode Data</label>
                    <input type="text" name="periode_data" class="form-control" style="font-size: 13.5px; height: 40px; border-color: #cbd5e1;" value="{{ $kartuData->first()->periode_data ?? ('SEMESTER 1 ' . date('Y')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted" style="font-size: 12.5px;">Posisi Rekonsiliasi (Tanggal)</label>
                    <input type="date" name="posisi_rekonsiliasi" class="form-control" style="font-size: 13.5px; height: 40px; border-color: #cbd5e1;" value="{{ $kartuData->first()->posisi_rekonsiliasi ?? date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted" style="font-size: 12.5px;">Program Audit</label>
                    <input type="text" class="form-control bg-light text-muted fw-semibold" style="font-size: 13.5px; height: 40px; border-color: #cbd5e1;" readonly value="KARTU ATM & PIN MAILER">
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TABEL 1: REKONSILIASI FISIK VS SALDO PERSEDIAAN GL VS CARDMAN -->
        <!-- ============================================================== -->
        <div class="card border-0 shadow-sm rounded-3 mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; overflow: hidden;">
            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); color: #fff;">
                <span class="fw-bold" style="font-size: 14.5px;">
                    <i class="bi bi-1-circle me-2 text-info"></i> TABEL 1: Rekonsiliasi Fisik vs Saldo Persediaan (Buku Besar/GL)
                </span>
                <span class="badge rounded-pill px-3 py-1" style="background-color: rgba(255, 255, 255, 0.2); color: #fff; font-size: 12px; font-weight: 600;">WP-RA-05-E-1</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 text-center" style="font-size: 13.5px;">
                        <thead style="background-color: #f1f5f9; color: #1e293b; font-weight: 700; font-size: 13px;" class="text-uppercase">
                            <tr>
                                <th style="width: 170px;" class="text-start ps-3 py-3">JENIS KARTU</th>
                                <th style="width: 150px;" class="py-3">HARGA MODAL (RP)</th>
                                <th style="width: 140px;" class="py-3">QTY FISIK</th>
                                <th style="width: 180px; background-color: #f8fafc;" class="py-3">NOMINAL FISIK (RP)</th>
                                <th style="width: 200px;" class="py-3">SALDO AKHIR PERSEDIAAN (GL)</th>
                                <th style="width: 170px; background-color: #f8fafc;" class="py-3">SELISIH</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kartuData as $item)
                            <tr>
                                <td class="text-start ps-3 fw-bold" style="color: #0f2e5a;">
                                    <span class="badge me-1 rounded-pill px-2 py-1" style="background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 11px;">
                                        {{ strtoupper($item->jenis_kartu) }}
                                    </span>
                                    KARTU {{ strtoupper($item->jenis_kartu) }}
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][harga_per_kartu]" id="modal_{{ $item->jenis_kartu }}" class="form-control form-control-sm text-end" style="font-size: 13.5px; height: 38px; border-color: #cbd5e1;" value="{{ (int)$item->harga_per_kartu }}" oninput="hitungKartu()">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][jumlah_fisik_kartu]" id="qty_{{ $item->jenis_kartu }}" class="form-control form-control-sm text-center fw-bold" style="font-size: 14px; height: 38px; border-color: #cbd5e1; color: #0f2e5a;" value="{{ $item->jumlah_fisik_kartu }}" oninput="hitungKartu()">
                                </td>
                                <td class="text-end pe-3 fw-bold" style="background-color: #f8fafc; color: #0f2e5a; font-size: 14px;" id="nom_{{ $item->jenis_kartu }}">
                                    Rp {{ number_format($item->nilai_fisik_kartu, 0, ',', '.') }}
                                </td>
                                @if($loop->first)
                                <td rowspan="{{ $kartuData->count() }}" class="align-middle p-3">
                                    <div class="input-group">
                                        <span class="input-group-text fw-bold" style="background-color: #f1f5f9; color: #475569; font-size: 13.5px;">Rp</span>
                                        <input type="number" name="saldo_persediaan_gl" id="inp_saldo_gl" class="form-control text-end fw-bold" style="font-size: 14px; height: 42px; border-color: #cbd5e1;" value="{{ (int)$saldoPersediaanGl }}" oninput="hitungKartu()">
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 12px;">Rekening Persediaan Cabang</small>
                                </td>
                                <td rowspan="{{ $kartuData->count() }}" class="align-middle p-3" style="background-color: #f8fafc;">
                                    <h6 class="fw-bold mb-1 {{ $selisihPersediaanGl != 0 ? 'text-danger' : 'text-success' }}" style="font-size: 1.15rem;" id="preview_selisih_gl">
                                        Rp {{ number_format($selisihPersediaanGl, 0, ',', '.') }}
                                    </h6>
                                    <span class="badge rounded-pill px-2 py-1" id="preview_status_gl" style="{{ $selisihPersediaanGl == 0 ? 'background-color: #f0fdf4; color: #166534;' : 'background-color: #fee2e2; color: #991b1b;' }} font-size: 11px; font-weight: 600;">
                                        {{ $selisihPersediaanGl == 0 ? 'Cocok (Match)' : 'Beda Fisik vs GL' }}
                                    </span>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                            <!-- Baris Total -->
                            <tr class="fw-bold" style="background-color: #f1f5f9; font-size: 13.5px; border-top: 2px solid #cbd5e1;">
                                <td colspan="2" class="text-center text-uppercase py-3" style="color: #334155;">TOTAL FISIK KARTU</td>
                                <td class="py-3 text-center" style="color: #0f2e5a; font-size: 14px;" id="total_qty_fisik">{{ number_format($totalFisikQty, 0, ',', '.') }}</td>
                                <td class="text-end pe-3 py-3" style="color: #0f2e5a; font-size: 14px;" id="total_nom_fisik">Rp {{ number_format($totalNominalFisik, 0, ',', '.') }}</td>
                                <td class="text-end pe-3 py-3" style="color: #1e293b; font-size: 14px;">Rp {{ number_format($saldoPersediaanGl, 0, ',', '.') }}</td>
                                <td class="text-end pe-3 py-3 {{ $selisihPersediaanGl != 0 ? 'text-danger' : 'text-success' }}" style="font-size: 14px;">
                                    Rp {{ number_format($selisihPersediaanGl, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Input Cardman & Findings Tabel 1 -->
                <div class="p-3 border-top" style="background-color: #f8fafc;">
                    <div class="row g-3 mb-3 align-items-center">
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark" style="font-size: 12.5px;">Data Sistem Cardman (Qty):</label>
                            <input type="number" name="cardman_qty" id="cardman_qty" class="form-control" style="font-size: 13.5px; height: 40px; border-color: #cbd5e1;" value="{{ $cardmanQty }}" oninput="updateFindingsTabel1()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark" style="font-size: 12.5px;">Data Sistem Cardman (Nominal Rp):</label>
                            <input type="number" name="cardman_nominal" id="cardman_nominal" class="form-control" style="font-size: 13.5px; height: 40px; border-color: #cbd5e1;" value="{{ (int)$cardmanNominal }}" oninput="updateFindingsTabel1()">
                        </div>
                        <div class="col-md-6">
                            <div class="p-2 rounded-2" style="background-color: #eff6ff; border: 1px solid #bfdbfe; font-size: 12.5px; color: #1e40af;">
                                <i class="bi bi-info-circle me-1"></i> Komparasi 3 Posisi: <strong>Fisik Cabang</strong> vs <strong>Buku Persediaan (GL)</strong> vs <strong>Sistem Cardman</strong>.
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="form-label fw-bold text-danger" style="font-size: 12.5px;">Findings / Temuan Otomatis (Tabel 1):</label>
                        <textarea name="findings_tabel1" id="findings_tabel1" class="form-control" rows="2" style="font-size: 13.5px; border-color: #cbd5e1;">{{ $findingsTabel1 }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TABEL 2: KOMPARASI FISIK KARTU ATM VS JUMLAH PIN MAILER -->
        <!-- ============================================================== -->
        <div class="card border-0 shadow-sm rounded-3 mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; overflow: hidden;">
            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); color: #fff;">
                <span class="fw-bold" style="font-size: 14.5px;">
                    <i class="bi bi-2-circle me-2 text-info"></i> TABEL 2: Komparasi Fisik Kartu ATM vs Jumlah PIN Mailer
                </span>
                <span class="badge rounded-pill px-3 py-1" style="background-color: rgba(255, 255, 255, 0.2); color: #fff; font-size: 12px; font-weight: 600;">WP-RA-05-E-2</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 text-center" style="font-size: 13.5px;">
                        <thead style="background-color: #f1f5f9; color: #1e293b; font-weight: 700; font-size: 13px;" class="text-uppercase">
                            <tr>
                                <th style="width: 180px;" class="text-start ps-3 py-3">JENIS KARTU</th>
                                <th style="width: 160px;" class="py-3">JUMLAH KARTU (FISIK)</th>
                                <th style="width: 160px;" class="py-3">PIN MAILER (FISIK)</th>
                                <th style="width: 130px; background-color: #f8fafc;" class="py-3">SELISIH</th>
                                <th style="width: 220px;" class="py-3">STATUS & KOMITMEN</th>
                                <th style="width: 170px;" class="py-3">BUKTI / AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kartuData as $item)
                            @php
                                $selisihPin = (int)$item->jumlah_fisik_kartu - (int)$item->jumlah_pin_mailer;
                            @endphp
                            <tr>
                                <td class="text-start ps-3 fw-bold" style="color: #0f2e5a;">
                                    KARTU {{ strtoupper($item->jenis_kartu) }}
                                </td>
                                <td class="fw-bold" style="color: #0f2e5a; font-size: 14px;" id="t2_kartu_{{ $item->jenis_kartu }}">
                                    {{ $item->jumlah_fisik_kartu }}
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $item->id }}][jumlah_pin_mailer]" id="pin_{{ $item->jenis_kartu }}" class="form-control form-control-sm text-center fw-bold" style="font-size: 14px; height: 38px; border-color: #cbd5e1;" value="{{ $item->jumlah_pin_mailer }}" oninput="hitungKartu()">
                                </td>
                                <td class="fw-bold {{ $selisihPin != 0 ? 'text-danger' : 'text-success' }}" style="background-color: #f8fafc; font-size: 14px;" id="t2_selisih_{{ $item->jenis_kartu }}">
                                    {{ $selisihPin }}
                                </td>
                                <td class="p-2 text-start">
                                    <select name="items[{{ $item->id }}][status_tindak_lanjut]" class="form-select form-select-sm mb-1 fw-semibold" style="font-size: 12.5px; border-color: #cbd5e1;">
                                        <option value="SUDAH SELESAI" {{ $item->status_tindak_lanjut === 'SUDAH SELESAI' ? 'selected' : '' }}>Sudah Selesai</option>
                                        <option value="DALAM PROSES" {{ $item->status_tindak_lanjut === 'DALAM PROSES' ? 'selected' : '' }}>Dalam Proses</option>
                                        <option value="TIDAK BISA" {{ $item->status_tindak_lanjut === 'TIDAK BISA' ? 'selected' : '' }}>Tidak Bisa</option>
                                    </select>
                                    <input type="date" name="items[{{ $item->id }}][komitmen_tanggal]" class="form-control form-control-sm" value="{{ $item->komitmen_tanggal }}" placeholder="Komitmen Tanggal" style="font-size: 12px; border-color: #cbd5e1;" title="Komitmen tanggal penyelesaian cabang">
                                </td>
                                <td class="p-2">
                                    @if($item->file_bukti)
                                        <a href="{{ route('surprise_audit.view_bukti', ['type' => 'kartu', 'id' => $item->id]) }}" target="_blank" class="btn btn-sm rounded-pill mb-1 d-block shadow-sm" style="background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 12px; font-weight: 600;">
                                            <i class="bi bi-eye me-1"></i> Bukti PDF
                                        </a>
                                    @else
                                        <input type="file" name="items[{{ $item->id }}][file_bukti]" class="form-control form-control-sm mb-1" style="font-size: 11.5px; border-color: #cbd5e1;">
                                    @endif

                                    <button type="submit" form="form-minta-bukti-{{ $item->id }}" class="btn btn-sm rounded-pill w-100" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 11.5px; font-weight: 600;" title="Minta Bukti ke Cabang" {{ $item->minta_bukti_cabang ? 'disabled' : '' }}>
                                        <i class="bi bi-send me-1"></i> {{ $item->minta_bukti_cabang ? 'Sudah Diminta' : 'Minta Bukti' }}
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            <!-- Baris Total Tabel 2 -->
                            <tr class="fw-bold" style="background-color: #f1f5f9; font-size: 13.5px; border-top: 2px solid #cbd5e1;">
                                <td class="text-start ps-3 text-uppercase py-3" style="color: #334155;">TOTAL KARTU VS PIN</td>
                                <td class="py-3" style="color: #0f2e5a; font-size: 14px;" id="t2_total_kartu">{{ $totalFisikQty }}</td>
                                <td class="py-3" style="font-size: 14px;" id="t2_total_pin">{{ $totalPinMailer }}</td>
                                <td class="py-3 {{ $selisihTotalKartuPin != 0 ? 'text-danger' : 'text-success' }}" style="font-size: 14px;" id="t2_total_selisih">
                                    {{ $selisihTotalKartuPin }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Findings Tabel 2 -->
                <div class="p-3 border-top" style="background-color: #f8fafc;">
                    <label class="form-label fw-bold text-danger" style="font-size: 12.5px;">Findings / Temuan PIN Mailer (Tabel 2):</label>
                    <textarea name="findings_tabel2" id="findings_tabel2" class="form-control" rows="2" style="font-size: 13.5px; border-color: #cbd5e1;">{{ $findingsTabel2 }}</textarea>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="btn rounded-pill px-4 py-2 fw-semibold" style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 14px;">
                Kembali
            </a>
            <button type="submit" class="btn rounded-pill px-4 py-2 fw-semibold shadow-sm text-white" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); font-size: 14px;">
                <i class="bi bi-save me-1"></i> Simpan Matriks Rekonsiliasi Kartu ATM
            </button>
        </div>
    </form>

    <!-- Separate standalone forms for Minta Bukti to avoid nested form tags -->
    @foreach($kartuData as $item)
    <form id="form-minta-bukti-{{ $item->id }}" action="{{ route('surprise_audit.minta_bukti', $assignment->id) }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="detail_type" value="kartu">
        <input type="hidden" name="detail_id" value="{{ $item->id }}">
    </form>
    @endforeach
</div>

<script>
function hitungKartu() {
    const modalSilver = parseFloat(document.getElementById('modal_Silver')?.value) || 42180;
    const modalGold = parseFloat(document.getElementById('modal_Gold')?.value) || 68820;
    const modalPlat = parseFloat(document.getElementById('modal_Platinum')?.value) || 68820;

    const qtySilver = parseInt(document.getElementById('qty_Silver')?.value) || 0;
    const qtyGold = parseInt(document.getElementById('qty_Gold')?.value) || 0;
    const qtyPlat = parseInt(document.getElementById('qty_Platinum')?.value) || 0;

    const pinSilver = parseInt(document.getElementById('pin_Silver')?.value) || 0;
    const pinGold = parseInt(document.getElementById('pin_Gold')?.value) || 0;
    const pinPlat = parseInt(document.getElementById('pin_Platinum')?.value) || 0;

    const nomSilver = qtySilver * modalSilver;
    const nomGold = qtyGold * modalGold;
    const nomPlat = qtyPlat * modalPlat;

    const totalQty = qtySilver + qtyGold + qtyPlat;
    const totalNom = nomSilver + nomGold + nomPlat;
    const totalPin = pinSilver + pinGold + pinPlat;
    const totalSelisihPin = totalQty - totalPin;

    const saldoGl = parseFloat(document.getElementById('inp_saldo_gl')?.value) || 0;
    const selisihGl = totalNom - saldoGl;

    const fmt = new Intl.NumberFormat('id-ID');

    // Update nominal per baris
    if (document.getElementById('nom_Silver')) document.getElementById('nom_Silver').innerText = 'Rp ' + fmt.format(nomSilver);
    if (document.getElementById('nom_Gold')) document.getElementById('nom_Gold').innerText = 'Rp ' + fmt.format(nomGold);
    if (document.getElementById('nom_Platinum')) document.getElementById('nom_Platinum').innerText = 'Rp ' + fmt.format(nomPlat);

    // Update total baris 1
    if (document.getElementById('total_qty_fisik')) document.getElementById('total_qty_fisik').innerText = fmt.format(totalQty);
    if (document.getElementById('total_nom_fisik')) document.getElementById('total_nom_fisik').innerText = 'Rp ' + fmt.format(totalNom);
    
    // Update banner metrics
    if (document.getElementById('banner_total_qty')) document.getElementById('banner_total_qty').innerText = fmt.format(totalQty);
    if (document.getElementById('banner_total_nom')) document.getElementById('banner_total_nom').innerText = 'Rp ' + fmt.format(totalNom);

    const prevSelGl = document.getElementById('preview_selisih_gl');
    if (prevSelGl) {
        prevSelGl.innerText = 'Rp ' + fmt.format(selisihGl);
        prevSelGl.className = 'fw-bold mb-1 ' + (selisihGl !== 0 ? 'text-danger' : 'text-success');
    }
    const prevStGl = document.getElementById('preview_status_gl');
    if (prevStGl) {
        prevStGl.innerText = (selisihGl === 0 ? 'Cocok (Match)' : 'Beda Fisik vs GL');
        prevStGl.style = (selisihGl === 0 ? 'background-color: #f0fdf4; color: #166534;' : 'background-color: #fee2e2; color: #991b1b;') + ' font-size: 11px; font-weight: 600;';
    }

    const bannerSelGl = document.getElementById('banner_selisih_gl');
    if (bannerSelGl) {
        bannerSelGl.innerText = 'Rp ' + fmt.format(selisihGl);
        bannerSelGl.className = 'fw-bold mt-1 ' + (selisihGl !== 0 ? 'text-danger' : 'text-success');
    }

    const bannerSelPin = document.getElementById('banner_selisih_pin');
    if (bannerSelPin) {
        bannerSelPin.innerText = totalSelisihPin + ' Pcs';
        bannerSelPin.className = 'fw-bold mt-1 ' + (totalSelisihPin !== 0 ? 'text-warning' : 'text-success');
    }

    // Update Tabel 2
    if (document.getElementById('t2_kartu_Silver')) document.getElementById('t2_kartu_Silver').innerText = qtySilver;
    if (document.getElementById('t2_kartu_Gold')) document.getElementById('t2_kartu_Gold').innerText = qtyGold;
    if (document.getElementById('t2_kartu_Platinum')) document.getElementById('t2_kartu_Platinum').innerText = qtyPlat;

    const selPinSilver = qtySilver - pinSilver;
    const selPinGold = qtyGold - pinGold;
    const selPinPlat = qtyPlat - pinPlat;

    if (document.getElementById('t2_selisih_Silver')) {
        document.getElementById('t2_selisih_Silver').innerText = selPinSilver;
        document.getElementById('t2_selisih_Silver').className = 'fw-bold ' + (selPinSilver !== 0 ? 'text-danger' : 'text-success');
    }
    if (document.getElementById('t2_selisih_Gold')) {
        document.getElementById('t2_selisih_Gold').innerText = selPinGold;
        document.getElementById('t2_selisih_Gold').className = 'fw-bold ' + (selPinGold !== 0 ? 'text-danger' : 'text-success');
    }
    if (document.getElementById('t2_selisih_Platinum')) {
        document.getElementById('t2_selisih_Platinum').innerText = selPinPlat;
        document.getElementById('t2_selisih_Platinum').className = 'fw-bold ' + (selPinPlat !== 0 ? 'text-danger' : 'text-success');
    }

    if (document.getElementById('t2_total_kartu')) document.getElementById('t2_total_kartu').innerText = totalQty;
    if (document.getElementById('t2_total_pin')) document.getElementById('t2_total_pin').innerText = totalPin;
    if (document.getElementById('t2_total_selisih')) {
        document.getElementById('t2_total_selisih').innerText = totalSelisihPin;
        document.getElementById('t2_total_selisih').className = 'py-3 ' + (totalSelisihPin !== 0 ? 'text-danger' : 'text-success');
    }
}

function updateFindingsTabel1() {
    const totalQty = parseInt(document.getElementById('total_qty_fisik')?.innerText.replace(/\./g, '')) || 0;
    const totalNom = document.getElementById('total_nom_fisik')?.innerText.replace('Rp ', '') || '0';
    const saldoGl = new Intl.NumberFormat('id-ID').format(parseFloat(document.getElementById('inp_saldo_gl')?.value) || 0);
    const cardmanQty = document.getElementById('cardman_qty')?.value || '0';
    const cardmanNom = new Intl.NumberFormat('id-ID').format(parseFloat(document.getElementById('cardman_nominal')?.value) || 0);

    const text = `Ditemukan bahwa posisi antara fisik, saldo persediaan dan sistem cardman berbeda; dimana fisik mencatat sebanyak ${totalQty} pcs atau Rp ${totalNom}; rekening persediaan Rp ${saldoGl} dan sistem mencatat ${cardmanQty} pcs atau Rp ${cardmanNom}.`;
    const fArea = document.getElementById('findings_tabel1');
    if (fArea && (!fArea.value || fArea.value.includes('Ditemukan bahwa posisi antara fisik'))) {
        fArea.value = text;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    hitungKartu();
});
</script>
@endsection
