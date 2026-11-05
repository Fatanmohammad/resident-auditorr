@extends('layouts.app')

@section('title', 'Status Penanganan & Disposisi Memo Eskalasi (SOP 09)')

@section('content')
<div class="container-fluid px-4 py-3" style="max-width: 1440px; margin: 0 auto;">
    <!-- Header Page: Elegan, Bersih & Nyaman di Mata -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge px-3 py-2 fs-7 fw-bold" style="background-color: #e0e7ff; color: #3730a3; border-radius: 6px;">
                    <i class="bi bi-shield-check me-1"></i> STANDAR SOP 09
                </span>
                <h3 class="mb-0 fw-bold" style="color: #0f2e5a; letter-spacing: -0.3px;">Status Penanganan &amp; Disposisi Memo</h3>
            </div>
            <p class="text-secondary mb-0" style="font-size: 0.95rem;">
                Monitoring tindak lanjut investigasi, penerbitan memo resmi eskalasi SKAI kepada Direksi, dan penetapan status akhir kasus fraud.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pemeriksaan_awal.pelaporan_awal') }}" class="btn btn-primary rounded-pill px-3 shadow-sm fw-bold" style="background: #0f2e5a; border-color: #0f2e5a; font-size: 0.92rem;">
                <i class="bi bi-plus-circle me-1"></i> Pelaporan Baru
            </a>
            <a href="{{ route('pemeriksaan_awal.daftar_laporan') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm fw-semibold" style="font-size: 0.92rem;">
                <i class="bi bi-journal-text me-1"></i> Daftar Laporan
            </a>
        </div>
    </div>

    @include('pemeriksaan_awal.partials.nav_tabs')

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4 p-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
            <div>
                <h6 class="mb-0 fw-bold text-success">Berhasil</h6>
                <div style="font-size: 0.92rem;">{{ session('success') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4 p-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
            <div>
                <h6 class="mb-0 fw-bold text-danger">Perhatian</h6>
                <div style="font-size: 0.92rem;">{{ session('error') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Cards Summary Pipeline Penanganan (Warna Lembut & Nyaman) -->
    <div class="row g-3 mb-4">
        <!-- Open -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary fw-bold text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">MENUNGGU DISPOSISI (OPEN)</div>
                        <h3 class="fw-bold mb-0 mt-1" style="color: #1d4ed8;">{{ $stats['open'] }}</h3>
                        <div class="text-muted small mt-1">Dalam pengkajian awal</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 48px; height: 48px; background: #eff6ff; color: #1d4ed8; font-size: 1.4rem;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Investigasi -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary fw-bold text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">INVESTIGASI KHUSUS</div>
                        <h3 class="fw-bold mb-0 mt-1" style="color: #be123c;">{{ $stats['investigasi'] }}</h3>
                        <div class="text-muted small mt-1">Tim Investigasi SKAI aktif</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 48px; height: 48px; background: #fff1f2; color: #be123c; font-size: 1.4rem;">
                        <i class="bi bi-search"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pembinaan -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary fw-bold text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">PEMBINAAN CABANG</div>
                        <h3 class="fw-bold mb-0 mt-1" style="color: #b45309;">{{ $stats['pembinaan'] }}</h3>
                        <div class="text-muted small mt-1">Perbaikan internal cabang</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 48px; height: 48px; background: #fffbeb; color: #d97706; font-size: 1.4rem;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Closed -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary fw-bold text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">KASUS SELESAI (CLOSED)</div>
                        <h3 class="fw-bold mb-0 mt-1 text-success">{{ $stats['closed'] }}</h3>
                        <div class="text-muted small mt-1">Selesai ditindaklanjuti</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 48px; height: 48px; background: #f0fdf4; color: #15803d; font-size: 1.4rem;">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="card shadow-sm border-0 rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-3">
            <form action="{{ route('pemeriksaan_awal.status_penanganan') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-secondary mb-1">Cari Kasus:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="Cari ID Kasus, nomor memo, cabang..." 
                               value="{{ request('q') }}" style="font-size: 0.92rem; height: 40px;">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-secondary mb-1">Status Penanganan:</label>
                    <select name="status" class="form-select" style="font-size: 0.92rem; height: 40px;">
                        <option value="">Semua Status Kasus</option>
                        <option value="OPEN" {{ request('status') == 'OPEN' ? 'selected' : '' }}>OPEN (Menunggu Disposisi)</option>
                        <option value="INVESTIGASI" {{ request('status') == 'INVESTIGASI' ? 'selected' : '' }}>INVESTIGASI (Dalam Investigasi)</option>
                        <option value="PEMBINAAN" {{ request('status') == 'PEMBINAAN' ? 'selected' : '' }}>PEMBINAAN (Pembinaan Cabang)</option>
                        <option value="CLOSED" {{ request('status') == 'CLOSED' ? 'selected' : '' }}>CLOSED (Selesai/Arsip)</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end" style="height: 65px;">
                    <div class="d-flex gap-1 w-100">
                        <button type="submit" class="btn btn-primary w-100 fw-bold" style="background: #0f2e5a; border-color: #0f2e5a; height: 40px;">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        @if(request()->hasAny(['q', 'status']))
                            <a href="{{ route('pemeriksaan_awal.status_penanganan') }}" class="btn btn-outline-secondary" style="height: 40px;" title="Reset Filter">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Kartu Penanganan Kasus Fraud: Nyaman Dibaca & Spacing Lega -->
    <div class="row g-4">
        @forelse($kasusList as $kasus)
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 h-100 bg-white" style="border: 1px solid #cbd5e1 !important;">
                <!-- Header Kartu -->
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                    <div>
                        <span class="badge px-3 py-2 fw-bold" style="background-color: #f1f5f9; color: #0f2e5a; border: 1px solid #cbd5e1; font-size: 0.92rem;">
                            {{ $kasus->indikasi_id }}
                        </span>
                        <span class="text-secondary ms-2 fw-semibold" style="font-size: 0.92rem;">
                            <i class="bi bi-building me-1 text-primary"></i>{{ $kasus->nama_unit ?: $kasus->kode_unit }}
                        </span>
                    </div>
                    <div>
                        @php
                            $st = strtoupper($kasus->status_kasus ?? 'OPEN');
                        @endphp
                        @if($st === 'INVESTIGASI')
                            <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: #fee2e2; color: #991b1b; font-size: 0.85rem;">
                                <i class="bi bi-search me-1"></i>INVESTIGASI
                            </span>
                        @elseif($st === 'OPEN')
                            <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: #eff6ff; color: #1e40af; font-size: 0.85rem;">
                                <i class="bi bi-hourglass-split me-1"></i>OPEN
                            </span>
                        @elseif($st === 'PEMBINAAN')
                            <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: #fef3c7; color: #92400e; font-size: 0.85rem;">
                                <i class="bi bi-shield-check me-1"></i>PEMBINAAN
                            </span>
                        @elseif($st === 'CLOSED')
                            <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: #f0fdf4; color: #166534; font-size: 0.85rem;">
                                <i class="bi bi-check2-circle me-1"></i>CLOSED
                            </span>
                        @endif
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Dugaan & Fakta Ringkas -->
                    <div class="mb-3">
                        <label class="text-secondary fw-bold small text-uppercase" style="letter-spacing: 0.5px;">DUGAAN AWAL (MODUS):</label>
                        <div class="fw-bold text-dark mt-1" style="font-size: 0.95rem; line-height: 1.5;">
                            {{ $kasus->dugaan_awal ?: ($kasus->dugaan_belum_terverifikasi ?: 'Dugaan belum dirumuskan secara rinci.') }}
                        </div>
                    </div>

                    <!-- Meta Informasi Box -->
                    <div class="row g-2 mb-3 p-3 rounded-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.9rem;">
                        <div class="col-6">
                            <span class="text-muted">Tipe Kasus:</span>
                            <div class="fw-bold text-dark">{{ $kasus->tipe_kasus ?: 'INSIDENTIL' }}</div>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Pihak Terlibat:</span>
                            <div class="fw-bold text-dark">{{ $kasus->pihak_terlibat ?: 'INTERNAL' }}</div>
                        </div>
                        <div class="col-6 mt-2">
                            <span class="text-muted">Potensi Kerugian:</span>
                            <div class="fw-bold text-danger">Rp {{ number_format($kasus->nominal_awal, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-6 mt-2">
                            <span class="text-muted">Skor Triase:</span>
                            <div class="fw-bold text-dark">Skor {{ $kasus->skor_checklist ?: 0 }} ({{ $kasus->saran_keputusan_triase ?: 'PEMBINAAN' }})</div>
                        </div>
                    </div>

                    <!-- Informasi Memo Eskalasi SKAI -->
                    <div class="p-3 rounded-3 mb-2" style="background-color: #fcfcfd; border: 1px solid #cbd5e1;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark" style="font-size: 0.9rem;">
                                <i class="bi bi-file-earmark-text-fill text-primary me-1"></i> Memo Eskalasi SKAI:
                            </span>
                            @if($kasus->nomor_memo_eskalasi)
                                <span class="badge px-3 py-1 rounded-pill" style="background-color: #f0fdf4; color: #166534; font-size: 0.8rem;">
                                    <i class="bi bi-check-circle me-1"></i>Sudah Diterbitkan
                                </span>
                            @else
                                <span class="badge px-3 py-1 rounded-pill" style="background-color: #fffbeb; color: #b45309; font-size: 0.8rem;">
                                    Draft / Belum Diterbitkan
                                </span>
                            @endif
                        </div>

                        @if($kasus->nomor_memo_eskalasi)
                            <div style="font-size: 0.88rem; line-height: 1.5;">
                                <div class="text-muted">Nomor Memo: <strong class="text-dark">{{ $kasus->nomor_memo_eskalasi }}</strong></div>
                                <div class="text-muted">Tanggal: <span class="text-dark">{{ $kasus->tanggal_memo ? date('d F Y', strtotime($kasus->tanggal_memo)) : '-' }}</span></div>
                                <div class="text-muted text-truncate">Perihal: <span class="text-dark">{{ $kasus->perihal_memo ?: '-' }}</span></div>
                            </div>
                        @else
                            <div class="text-muted fst-italic" style="font-size: 0.85rem;">
                                Memo resmi belum diterbitkan. Klik tombol <strong>"Buat / Edit Memo"</strong> di bawah untuk menyusun disposisi eskalasi.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-footer bg-white py-3 px-4 border-top d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <div class="d-flex gap-2">
                        <!-- Tombol Buka Modal Memo Eskalasi -->
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold btn-memo shadow-sm"
                            style="background: #0f2e5a; border-color: #0f2e5a; font-size: 0.88rem;"
                            data-id="{{ $kasus->id }}"
                            data-kasus-id="{{ $kasus->indikasi_id }}"
                            data-unit="{{ $kasus->nama_unit ?: $kasus->kode_unit }}"
                            data-nomor="{{ $kasus->nomor_memo_eskalasi ?: ('MEMO/SKAI-FRD/' . date('Y') . '/' . strtoupper(date('M')) . '/' . str_pad($kasus->id, 3, '0', STR_PAD_LEFT)) }}"
                            data-tanggal="{{ $kasus->tanggal_memo ?: date('Y-m-d') }}"
                            data-perihal="{{ $kasus->perihal_memo ?: ('Laporan Indikasi Fraud & Usulan Tindak Lanjut pada ' . ($kasus->nama_unit ?: 'Kantor Cabang')) }}"
                            data-isi="{{ $kasus->isi_memo ?: ($kasus->dugaan_awal . "\nFakta: " . $kasus->fakta_temuan . "\nKronologi: " . $kasus->kronologi_awal) }}"
                            data-arahan="{{ $kasus->arahan_kadiv ?: ($kasus->saran_keputusan_triase ?: 'Segera laksanakan investigasi khusus dan amankan seluruh barang bukti.') }}"
                            data-status="{{ $kasus->status_kasus ?: 'OPEN' }}">
                            <i class="bi bi-pencil-square me-1"></i> Buat / Edit Memo
                        </button>

                        <!-- Poin 19: Tombol Print Memo Resmi SKAI -->
                        <a href="{{ route('pemeriksaan_awal.print_memo', $kasus->id) }}" target="_blank" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-semibold" style="font-size: 0.88rem;">
                            <i class="bi bi-printer-fill me-1"></i> Print Memo
                        </a>
                    </div>

                    <!-- Direct Update Status Form -->
                    <form action="{{ route('pemeriksaan_awal.status_penanganan.update_status', $kasus->id) }}" method="POST" class="d-inline-flex align-items-center gap-2">
                        @csrf
                        <label class="small text-muted fw-bold mb-0">Status:</label>
                        <select name="status_kasus" class="form-select form-select-sm rounded-pill" style="width: 140px; font-size: 0.85rem; font-weight: 600;" onchange="this.form.submit()">
                            <option value="OPEN" {{ $kasus->status_kasus == 'OPEN' ? 'selected' : '' }}>OPEN</option>
                            <option value="INVESTIGASI" {{ $kasus->status_kasus == 'INVESTIGASI' ? 'selected' : '' }}>INVESTIGASI</option>
                            <option value="PEMBINAAN" {{ $kasus->status_kasus == 'PEMBINAAN' ? 'selected' : '' }}>PEMBINAAN</option>
                            <option value="CLOSED" {{ $kasus->status_kasus == 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5 text-muted">
            <div class="py-4">
                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                <h6 class="fw-bold text-secondary">Tidak ada kasus yang sesuai filter pencarian.</h6>
            </div>
        </div>
        @endforelse
    </div>

    @if($kasusList->hasPages())
    <div class="mt-4">
        {{ $kasusList->links() }}
    </div>
    @endif
</div>

<!-- Modal Form Penyusunan Memo Eskalasi SKAI: Bersih, Lega & Font Jelas -->
<div class="modal fade" id="modalMemoEskalasi" tabindex="-1" aria-labelledby="modalMemoEskalasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" id="formMemoModal">
            @csrf
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header py-3 px-4" style="background: #0f2e5a; color: #ffffff; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalMemoEskalasiLabel">
                            <i class="bi bi-file-earmark-text-fill text-warning me-2"></i> Memo Resmi Disposisi &amp; Eskalasi SKAI
                        </h5>
                        <div class="small text-white-50 mt-1" id="mUnitKasus"></div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="font-size: 0.95rem;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">Nomor Memo Resmi <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_memo" id="memo_nomor" class="form-control" style="height: 44px; font-size: 0.95rem;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">Tanggal Memo <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_memo" id="memo_tanggal" class="form-control" style="height: 44px; font-size: 0.95rem;" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark mb-1">Perihal Memo <span class="text-danger">*</span></label>
                            <input type="text" name="perihal_memo" id="memo_perihal" class="form-control" style="height: 44px; font-size: 0.95rem;" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark mb-1">Ringkasan Kronologi &amp; Fakta Temuan <span class="text-danger">*</span></label>
                            <textarea name="isi_memo" id="memo_isi" class="form-control" rows="4" style="font-size: 0.95rem; line-height: 1.6;" required></textarea>
                            <div class="text-muted mt-1" style="font-size: 0.82rem;">Uraikan secara singkat dugaan modus operandi, fakta temuan, dan potensi dampak kerugian bagi bank.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark mb-1">Arahan / Rekomendasi Tindak Lanjut Kadiv SKAI</label>
                            <textarea name="arahan_kadiv" id="memo_arahan" class="form-control" rows="2" style="font-size: 0.95rem; line-height: 1.6;" placeholder="Instruksi khusus Kadiv SKAI kepada Tim Investigasi / Resident Auditor..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">Keputusan Status Penanganan <span class="text-danger">*</span></label>
                            <select name="keputusan_status" id="memo_status" class="form-select" style="height: 44px; font-size: 0.95rem;" required>
                                <option value="INVESTIGASI">INVESTIGASI (Eskalasi ke Investigasi Khusus SKAI)</option>
                                <option value="PEMBINAAN">PEMBINAAN (Pembinaan &amp; Perbaikan Cabang)</option>
                                <option value="CLOSED">CLOSED (Kasus Selesai / Ditutup)</option>
                                <option value="OPEN">OPEN (Dalam Pengkajian / Draft)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3 px-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" style="background: #0f2e5a; border-color: #0f2e5a;">
                        <i class="bi bi-save me-1"></i> Simpan &amp; Terbitkan Memo
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let memoButtons = document.querySelectorAll('.btn-memo');
    let modalEl = document.getElementById('modalMemoEskalasi');
    let modal = new bootstrap.Modal(modalEl);
    let form = document.getElementById('formMemoModal');

    memoButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            let id = this.getAttribute('data-id');
            let kasusId = this.getAttribute('data-kasus-id');
            let unit = this.getAttribute('data-unit');

            form.action = "{{ url('/pemeriksaan-awal/status-penanganan') }}/" + id + "/memo";
            document.getElementById('mUnitKasus').innerText = "Kasus: " + kasusId + " | Unit: " + unit;
            document.getElementById('memo_nomor').value = this.getAttribute('data-nomor');
            document.getElementById('memo_tanggal').value = this.getAttribute('data-tanggal');
            document.getElementById('memo_perihal').value = this.getAttribute('data-perihal');
            document.getElementById('memo_isi').value = this.getAttribute('data-isi');
            document.getElementById('memo_arahan').value = this.getAttribute('data-arahan');
            document.getElementById('memo_status').value = this.getAttribute('data-status');

            modal.show();
        });
    });
});
</script>
@endsection
