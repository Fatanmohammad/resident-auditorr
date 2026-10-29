@extends('layouts.app')
@section('title', 'Klarifikasi & Simpulan ' . $simpulan->visit_id . ' - SOP 06')

@section('content')
<style>
    .section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: -0.01em;
        margin-bottom: 0.75rem;
    }
    .info-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.85rem 1rem;
    }
    .info-label {
        font-size: 0.72rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.25rem;
    }
    .info-value {
        font-size: 0.88rem;
        color: #1e293b;
        font-weight: 600;
    }

    .badge-status {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }
    .badge-closed { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-open { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-tl { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
    .badge-klar { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .badge-draft { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .badge-revisi { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
    .badge-final { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .badge-gray { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

    .data-table th {
        font-size: 0.76rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        background: #f8fafc;
        padding: 0.75rem 0.85rem;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    .data-table td {
        font-size: 0.82rem;
        padding: 0.75rem 0.85rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
    }
</style>

<!-- Header Harmonized SOP 06 -->
<div class="rtv-header">
    <div>
        <div class="rtv-breadcrumb">
            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a> &rsaquo;
            <a href="{{ route('risk-trigger.simpulan-tl.index') }}">Klarifikasi &amp; Simpulan TL</a> &rsaquo;
            <span class="text-dark fw-semibold">{{ $simpulan->visit_id }}</span>
        </div>
        <h4 class="rtv-title">
            <i class="bi bi-check2-circle text-primary"></i> Lembar Kerja Simpulan &amp; Tindak Lanjut
            <span class="badge-wp">{{ $simpulan->visit_id }}</span>
            <span class="badge-status {{ $simpulan->status_review === 'FINAL' ? 'badge-status-success' : ($simpulan->status_review === 'REVISI' ? 'badge-status-warning' : 'badge-status-neutral') }}">
                STATUS: {{ $simpulan->status_review }}
            </span>
            <span class="badge-status {{ $simpulan->status_closure === 'CLOSED/DITERUSKAN' ? 'badge-status-success' : 'badge-status-warning' }}">
                CLOSURE: {{ $simpulan->status_closure }}
            </span>
        </h4>
        <p class="rtv-subtitle">
            Pencatatan klarifikasi bukti, simpulan pengujian, rencana tindak lanjut, dan approval review.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('risk-trigger.simpulan-tl.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Simpulan
        </a>
    </div>
</div>

    {{-- Alert Notice jika Simpulan sudah FINAL & CLOSED untuk RA --}}
    @if($isLockedForRa)
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-3" style="border-radius: 12px; background: #ecfdf5; border-left: 5px solid #10b981 !important;">
        <i class="bi bi-shield-lock-fill fs-2 text-success"></i>
        <div>
            <div class="fw-bold text-success" style="font-size: 0.92rem;">DOKUMEN TELAH BERSTATUS FINAL & CLOSED (TERKUNCI)</div>
            <div class="text-dark small">
                Hasil simpulan, tindak lanjut, dan seluruh klarifikasi bukti telah disetujui (FINAL) oleh Reviewer dan berstatus CLOSED/DITERUSKAN. Formulir dikunci (Read-Only) untuk akun Resident Auditor.
            </div>
        </div>
    </div>
    @endif

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" style="border-radius: 10px;" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" style="border-radius: 10px;" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Master Assignment & Trigger Info Card (Harmonized dengan KKA Edit) --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3 p-md-4">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <div class="info-label">Visit ID (A)</div>
                        <div class="info-value text-primary font-monospace">{{ $simpulan->visit_id }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <div class="info-label">Trigger ID (B)</div>
                        <div class="info-value text-secondary font-monospace">{{ $simpulan->trigger_id ?: '-' }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <div class="info-label">Unit / Kantor Cabang (C)</div>
                        <div class="info-value text-dark">{{ $simpulan->unit_kantor }} <span class="badge bg-light text-secondary border">({{ $simpulan->kode_unit }})</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <div class="info-label">Area & Risk Level (D & E)</div>
                        <div class="info-value d-flex align-items-center gap-2">
                            <span>{{ $simpulan->area }}</span>
                            <span class="badge {{ $simpulan->risk_level_awal == 'TINGGI' || $simpulan->risk_level_awal == 'HIGH' || $simpulan->risk_level_awal == 'KRITIS' ? 'bg-danger' : 'bg-warning text-dark' }}" style="font-size: 0.72rem;">
                                {{ $simpulan->risk_level_awal }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <div class="info-label">Status Klarifikasi (H)</div>
                        <div class="info-value">
                            @if($simpulan->status_klarifikasi === 'SELESAI')
                                <span class="badge-status badge-closed"><i class="bi bi-check-circle-fill"></i> SELESAI</span>
                            @elseif($simpulan->status_klarifikasi === 'MASIH OPEN')
                                <span class="badge-status badge-klar"><i class="bi bi-exclamation-circle-fill"></i> MASIH OPEN</span>
                            @else
                                <span class="badge-status badge-gray"><i class="bi bi-dash-circle"></i> TIDAK ADA</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <div class="info-label">Total Exception KKA (G)</div>
                        <div class="info-value">
                            @if(($simpulan->jumlah_exception ?? 0) > 0)
                                <span class="badge bg-danger px-2.5 py-1">
                                    <i class="bi bi-exclamation-octagon-fill me-1"></i> {{ $simpulan->jumlah_exception }} Temuan (8 Area)
                                </span>
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i> 0 Temuan (Sesuai)
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <div class="info-label">RA Pelaksana (T)</div>
                        <div class="info-value text-dark">{{ $simpulan->ra_pelaksana ?: '-' }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <div class="info-label">Reviewer (U)</div>
                        <div class="info-value text-dark">{{ $simpulan->reviewer ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BAGIAN 1: REGISTER KLARIFIKASI & BUKTI (16 Kolom Excel: A s.d. P - Multi-Row per Visit ID) --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2 border-bottom">
            <div>
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-chat-left-dots text-primary me-2"></i>
                    Bagian 1: Register Klarifikasi & Bukti (Klarifikasi_Bukti - 16 Kolom Excel)
                </h6>
                <div class="small text-muted mt-0.5" style="font-size: 0.78rem;">
                    Multi-Row per Visit ID ({{ $klarifikasiList->count() }} Baris Terdaftar) &bull; Seluruh baris harus berstatus Closed agar klarifikasi tuntas.
                </div>
            </div>
            @if(!$isLockedForRa)
            <div class="d-flex align-items-center gap-2">
                <form action="{{ route('risk-trigger.simpulan-tl.pull-exceptions', $simpulan->id) }}" method="POST" class="m-0" onsubmit="return confirm('Tarik seluruh baris berstatus EXCEPTION dari 8 KKA Area ke Register Klarifikasi?');">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm fw-semibold text-dark">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Tarik Cepat Exception KKA
                    </button>
                </form>

                <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahKlarifikasi">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Klarifikasi
                </button>
            </div>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover data-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 40px;">No</th>
                        <th style="min-width: 180px;">Isu / Exception (D)</th>
                        <th>Pihak Diklarifikasi (E)</th>
                        <th>Tgl Minta (F)</th>
                        <th style="min-width: 180px;">Pertanyaan Klarifikasi (G)</th>
                        <th style="min-width: 180px;">Jawaban Auditee (H)</th>
                        <th>Bukti Diminta (I)</th>
                        <th>Bukti Diterima & File (J)</th>
                        <th>Tgl Diterima (K)</th>
                        <th>Kecukupan Bukti (L)</th>
                        <th style="min-width: 150px;">Analisis RA (M)</th>
                        <th>Status Klarifikasi (N)</th>
                        <th>Reviewer (O)</th>
                        <th style="min-width: 150px;">Catatan Review Admin (P)</th>
                        <th class="text-center" style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($klarifikasiList as $idx => $klar)
                    <tr>
                        <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                        
                        {{-- Kolom D: Isu/Exception --}}
                        <td>
                            <div class="fw-semibold text-dark" style="font-size: 0.82rem;">{{ $klar->isu_exception }}</div>
                        </td>

                        {{-- Kolom E: Pihak Diklarifikasi --}}
                        <td class="small">{{ $klar->pihak_diklarifikasi ?: '-' }}</td>

                        {{-- Kolom F: Tanggal Permintaan --}}
                        <td class="small font-monospace">{{ $klar->tanggal_permintaan ? $klar->tanggal_permintaan->format('d/m/Y') : '-' }}</td>

                        {{-- Kolom G: Pertanyaan Klarifikasi --}}
                        <td class="small text-muted" style="font-size: 0.78rem;">
                            {{ $klar->pertanyaan_klarifikasi ?: '-' }}
                        </td>

                        {{-- Kolom H: Jawaban Auditee --}}
                        <td class="small" style="font-size: 0.78rem;">
                            {{ $klar->jawaban_auditee ?: '-' }}
                        </td>

                        {{-- Kolom I: Bukti Diminta --}}
                        <td class="small">{{ $klar->bukti_diminta ?: '-' }}</td>

                        {{-- Kolom J: Bukti Diterima & File --}}
                        <td>
                            <div class="small">{{ $klar->bukti_diterima ?: '-' }}</div>
                            @if($klar->lampiran_file)
                            <div class="mt-1">
                                <a href="{{ asset('storage/' . $klar->lampiran_file) }}" target="_blank" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 0.72rem; border-radius: 4px;">
                                    <i class="bi bi-paperclip me-1"></i> Unduh Bukti
                                </a>
                            </div>
                            @endif
                        </td>

                        {{-- Kolom K: Tanggal Diterima --}}
                        <td class="small font-monospace">{{ $klar->tanggal_diterima ? $klar->tanggal_diterima->format('d/m/Y') : '-' }}</td>

                        {{-- Kolom L: Kecukupan Bukti --}}
                        <td>
                            @if($klar->kecukupan_bukti === 'Memadai')
                                <span class="badge bg-success text-white">Memadai</span>
                            @elseif($klar->kecukupan_bukti === 'Kurang Memadai')
                                <span class="badge bg-warning text-dark">Kurang Memadai</span>
                            @else
                                <span class="badge bg-danger text-white">Tidak Ada Bukti</span>
                            @endif
                        </td>

                        {{-- Kolom M: Analisis RA --}}
                        <td class="small text-muted" style="font-size: 0.78rem;">
                            {{ $klar->analisis_ra ?: '-' }}
                        </td>

                        {{-- Kolom N: Status Klarifikasi --}}
                        <td>
                            @if($klar->status_klarifikasi === 'Closed')
                                <span class="badge-status badge-closed"><i class="bi bi-check-circle-fill"></i> Closed</span>
                            @elseif($klar->status_klarifikasi === 'Dalam Proses')
                                <span class="badge-status badge-klar"><i class="bi bi-hourglass-split"></i> Dalam Proses</span>
                            @else
                                <span class="badge-status badge-open"><i class="bi bi-exclamation-circle-fill"></i> Open</span>
                            @endif
                        </td>

                        {{-- Kolom O: Reviewer --}}
                        <td class="small">{{ $klar->reviewer ?: '-' }}</td>

                        {{-- Kolom P: Catatan Review Admin --}}
                        <td class="small">
                            @if($klar->catatan_review)
                                <div class="text-primary fw-semibold" style="font-size: 0.78rem;">
                                    <i class="bi bi-chat-square-quote me-1"></i> {{ $klar->catatan_review }}
                                </div>
                            @else
                                <span class="text-muted small">- Belum Ada -</span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="text-center">
                            @if(!$isLockedForRa)
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <button type="button" class="btn btn-outline-primary btn-sm px-2 py-1" style="border-radius: 6px; font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#modalEditKlarifikasi{{ $klar->id }}" title="Edit Klarifikasi">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('risk-trigger.simpulan-tl.klarifikasi.destroy', $klar->id) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus baris klarifikasi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm px-2 py-1" style="border-radius: 6px; font-size: 0.75rem;" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                            @else
                            <span class="badge bg-light text-muted border"><i class="bi bi-lock-fill"></i> Kunci</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Modal Edit Klarifikasi --}}
                    <div class="modal fade" id="modalEditKlarifikasi{{ $klar->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                                <form action="{{ route('risk-trigger.simpulan-tl.klarifikasi.update', $klar->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header bg-white border-bottom">
                                        <h6 class="modal-title fw-bold text-dark mb-0">
                                            <i class="bi bi-pencil-square text-primary me-2"></i> Edit Klarifikasi & Bukti #{{ $idx + 1 }}
                                        </h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Isu / Exception (Kolom D) *</label>
                                                <textarea name="isu_exception" class="form-control" rows="2" required {{ $isLockedForRa ? 'readonly' : '' }}>{{ $klar->isu_exception }}</textarea>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Pihak Diklarifikasi (Kolom E)</label>
                                                <input type="text" name="pihak_diklarifikasi" class="form-control" value="{{ $klar->pihak_diklarifikasi }}" placeholder="Nama / Jabatan Petugas Unit" {{ $isLockedForRa ? 'readonly' : '' }}>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Tanggal Permintaan (Kolom F)</label>
                                                <input type="date" name="tanggal_permintaan" class="form-control" value="{{ $klar->tanggal_permintaan ? $klar->tanggal_permintaan->format('Y-m-d') : '' }}" {{ $isLockedForRa ? 'readonly' : '' }}>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Pertanyaan / Klarifikasi yang Diajukan (Kolom G)</label>
                                                <textarea name="pertanyaan_klarifikasi" class="form-control" rows="2" placeholder="Uraian pertanyaan klarifikasi audit..." {{ $isLockedForRa ? 'readonly' : '' }}>{{ $klar->pertanyaan_klarifikasi }}</textarea>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Jawaban / Tanggapan Auditee (Kolom H)</label>
                                                <textarea name="jawaban_auditee" class="form-control" rows="2" placeholder="Penjelasan / tanggapan resmi dari auditee..." {{ $isLockedForRa ? 'readonly' : '' }}>{{ $klar->jawaban_auditee }}</textarea>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Bukti Diminta (Kolom I)</label>
                                                <input type="text" name="bukti_diminta" class="form-control" value="{{ $klar->bukti_diminta }}" placeholder="Dokumen yang diminta..." {{ $isLockedForRa ? 'readonly' : '' }}>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Bukti Diterima (Kolom J)</label>
                                                <input type="text" name="bukti_diterima" class="form-control" value="{{ $klar->bukti_diterima }}" placeholder="Dokumen yang diserahkan..." {{ $isLockedForRa ? 'readonly' : '' }}>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Tanggal Bukti Diterima (Kolom K)</label>
                                                <input type="date" name="tanggal_diterima" class="form-control" value="{{ $klar->tanggal_diterima ? $klar->tanggal_diterima->format('Y-m-d') : '' }}" {{ $isLockedForRa ? 'readonly' : '' }}>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Kecukupan Bukti (Kolom L)</label>
                                                <select name="kecukupan_bukti" class="form-select" {{ $isLockedForRa ? 'disabled' : '' }}>
                                                    <option value="Memadai" {{ $klar->kecukupan_bukti == 'Memadai' ? 'selected' : '' }}>Memadai</option>
                                                    <option value="Kurang Memadai" {{ $klar->kecukupan_bukti == 'Kurang Memadai' ? 'selected' : '' }}>Kurang Memadai</option>
                                                    <option value="Tidak Ada Bukti" {{ $klar->kecukupan_bukti == 'Tidak Ada Bukti' ? 'selected' : '' }}>Tidak Ada Bukti</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Status Klarifikasi (Kolom N) *</label>
                                                <select name="status_klarifikasi" class="form-select" required {{ $isLockedForRa ? 'disabled' : '' }}>
                                                    <option value="Open" {{ $klar->status_klarifikasi == 'Open' ? 'selected' : '' }}>Open</option>
                                                    <option value="Dalam Proses" {{ $klar->status_klarifikasi == 'Dalam Proses' ? 'selected' : '' }}>Dalam Proses</option>
                                                    <option value="Closed" {{ $klar->status_klarifikasi == 'Closed' ? 'selected' : '' }}>Closed</option>
                                                </select>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Lampiran Dokumen Bukti (Upload File)</label>
                                                <input type="file" name="lampiran_file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip" {{ $isLockedForRa ? 'disabled' : '' }}>
                                                @if($klar->lampiran_file)
                                                <div class="mt-1 small">
                                                    <span class="text-muted">File saat ini:</span>
                                                    <a href="{{ asset('storage/' . $klar->lampiran_file) }}" target="_blank" class="text-primary fw-semibold">
                                                        <i class="bi bi-paperclip me-1"></i> Lihat Dokumen Terlampir
                                                    </a>
                                                </div>
                                                @endif
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Analisis Profesional RA (Kolom M)</label>
                                                <textarea name="analisis_ra" class="form-control" rows="2" placeholder="Analisis auditor atas tanggapan dan bukti..." {{ $isLockedForRa ? 'readonly' : '' }}>{{ $klar->analisis_ra }}</textarea>
                                            </div>

                                            {{-- Kolom Reviewer & Catatan Review (Admin Only) --}}
                                            <div class="col-12 p-3 bg-light rounded border border-primary border-opacity-25">
                                                <label class="form-label small fw-bold text-primary text-uppercase mb-1">
                                                    <i class="bi bi-shield-check me-1"></i> Catatan Review Khusus Admin / Reviewer (Kolom P)
                                                </label>
                                                @if(!$isRa)
                                                <textarea name="catatan_review" class="form-control" rows="2" placeholder="Catatan reviu khusus dari Admin / Reviewer...">{{ $klar->catatan_review }}</textarea>
                                                <div class="small text-muted mt-1" style="font-size: 0.72rem;">Wewenang eksklusif Admin / Reviewer untuk memvalidasi klarifikasi.</div>
                                                @else
                                                <div class="p-2 bg-white rounded border small text-muted">
                                                    {{ $klar->catatan_review ?: 'Belum ada catatan review dari Admin.' }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-top">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                                        @if(!$isLockedForRa)
                                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                                            <i class="bi bi-save me-1"></i> Simpan Perubahan Klarifikasi
                                        </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="15" class="text-center py-5 text-muted">
                            <i class="bi bi-chat-square-text fs-2 d-block mb-2 text-secondary"></i>
                            <div>Belum ada baris klarifikasi bukti untuk Visit ID ini.</div>
                            <div class="small text-muted mt-1">Gunakan tombol <strong>"Tarik Cepat Exception KKA"</strong> atau <strong>"+ Tambah Klarifikasi"</strong> pada header tabel di atas.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Klarifikasi Manual --}}
    @if(!$isLockedForRa)
    <div class="modal fade" id="modalTambahKlarifikasi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <form action="{{ route('risk-trigger.simpulan-tl.klarifikasi.store', $simpulan->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-white border-bottom">
                        <h6 class="modal-title fw-bold text-dark mb-0">
                            <i class="bi bi-plus-circle text-primary me-2"></i> Tambah Baris Klarifikasi & Bukti Baru
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Isu / Exception (Kolom D) *</label>
                                <textarea name="isu_exception" class="form-control" rows="2" placeholder="Uraian isu / exception yang diklarifikasi kepada auditee..." required></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Pihak Diklarifikasi (Kolom E)</label>
                                <input type="text" name="pihak_diklarifikasi" class="form-control" placeholder="Nama / Jabatan Petugas Unit">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Tanggal Permintaan (Kolom F)</label>
                                <input type="date" name="tanggal_permintaan" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Pertanyaan / Klarifikasi yang Diajukan (Kolom G)</label>
                                <textarea name="pertanyaan_klarifikasi" class="form-control" rows="2" placeholder="Pertanyaan audit yang diajukan..."></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Jawaban / Tanggapan Auditee (Kolom H)</label>
                                <textarea name="jawaban_auditee" class="form-control" rows="2" placeholder="Tanggapan / penjelasan dari pihak auditee..."></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Bukti Diminta (Kolom I)</label>
                                <input type="text" name="bukti_diminta" class="form-control" placeholder="Dokumen / bukti pendukung yang diminta...">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Bukti Diterima (Kolom J)</label>
                                <input type="text" name="bukti_diterima" class="form-control" placeholder="Dokumen / bukti yang diserahkan...">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Tanggal Diterima (Kolom K)</label>
                                <input type="date" name="tanggal_diterima" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Kecukupan Bukti (Kolom L)</label>
                                <select name="kecukupan_bukti" class="form-select">
                                    <option value="Kurang Memadai" selected>Kurang Memadai</option>
                                    <option value="Memadai">Memadai</option>
                                    <option value="Tidak Ada Bukti">Tidak Ada Bukti</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Status Klarifikasi (Kolom N) *</label>
                                <select name="status_klarifikasi" class="form-select" required>
                                    <option value="Open" selected>Open</option>
                                    <option value="Dalam Proses">Dalam Proses</option>
                                    <option value="Closed">Closed</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Lampiran Dokumen Bukti (Upload File)</label>
                                <input type="file" name="lampiran_file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark text-uppercase mb-1">Analisis Profesional RA (Kolom M)</label>
                                <textarea name="analisis_ra" class="form-control" rows="2" placeholder="Analisis profesional auditor atas jawaban dan bukti auditee..."></textarea>
                            </div>

                            @if(!$isRa)
                            <div class="col-12 p-3 bg-light rounded border border-primary border-opacity-25">
                                <label class="form-label small fw-bold text-primary text-uppercase mb-1">
                                    <i class="bi bi-shield-check me-1"></i> Catatan Review Khusus Admin / Reviewer (Kolom P)
                                </label>
                                <textarea name="catatan_review" class="form-control" rows="2" placeholder="Catatan reviu khusus dari Admin / Reviewer..."></textarea>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Klarifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- BAGIAN 2: FORM SIMPULAN, TINDAK LANJUT, ESKALASI & REVIEW FINAL (Simpulan_TL) --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2 border-bottom">
            <div>
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-card-checklist text-primary me-2"></i>
                    Bagian 2: Simpulan Akhir, Tindak Lanjut & Validasi Closure (Simpulan_TL - 23 Kolom Excel)
                </h6>
                <div class="small text-muted mt-0.5" style="font-size: 0.78rem;">
                    Formulir penyelesaian hasil audit lapangan SOP 06 mengacu pada baris 6 sheet Simpulan_TL.
                </div>
            </div>
        </div>

        <div class="card-body p-3 p-md-4">
            <form action="{{ route('risk-trigger.simpulan-tl.update', $simpulan->id) }}" method="POST">
                @csrf
                <div class="row g-3">
                    {{-- Simpulan Akhir (Kolom I) --}}
                    <div class="col-md-8">
                        <label class="form-label small fw-bold text-dark text-uppercase mb-1">
                            Simpulan Akhir Audit (Kolom I) <span class="text-danger">*</span>
                            @if($kkaInduk && $kkaInduk->simpulan_ra)
                            <span class="badge bg-light text-primary border ms-1" style="font-size: 0.7rem; font-weight: normal;">
                                <i class="bi bi-link-45deg"></i> Terhubung KKA Induk Kolom Q
                            </span>
                            @endif
                        </label>
                        <textarea name="simpulan_akhir" class="form-control" rows="3" placeholder="Uraikan simpulan profesional RA atas pengujian dan klarifikasi..." required {{ $isLockedForRa ? 'readonly' : '' }}>{{ $simpulan->simpulan_akhir }}</textarea>
                    </div>

                    {{-- Klasifikasi Hasil (Kolom J - Dropdown Wajib) --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-dark text-uppercase mb-1">
                            Klasifikasi Hasil (Kolom J) <span class="text-danger">*</span>
                        </label>
                        <select name="klasifikasi_hasil" class="form-select fw-semibold" required {{ $isLockedForRa ? 'disabled' : '' }}>
                            <option value="">-- Pilih Klasifikasi Hasil --</option>
                            <option value="Temuan" {{ $simpulan->klasifikasi_hasil == 'Temuan' ? 'selected' : '' }}>Temuan</option>
                            <option value="Perlu Perluasan" {{ $simpulan->klasifikasi_hasil == 'Perlu Perluasan' ? 'selected' : '' }}>Perlu Perluasan</option>
                            <option value="Eskalasi" {{ $simpulan->klasifikasi_hasil == 'Eskalasi' ? 'selected' : '' }}>Eskalasi</option>
                            <option value="Tidak Terbukti / Selesai" {{ $simpulan->klasifikasi_hasil == 'Tidak Terbukti / Selesai' ? 'selected' : '' }}>Tidak Terbukti / Selesai</option>
                        </select>
                        <div class="small text-muted mt-1" style="font-size: 0.74rem;">
                            * Jika "Temuan", "Perlu Perluasan", atau "Eskalasi", maka Tindak Lanjut, PIC, dan Due Date wajib diisi.
                        </div>
                    </div>

                    {{-- Tindak Lanjut Awal (Kolom M) --}}
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-dark text-uppercase mb-1">
                            Tindak Lanjut Awal / Rencana Perbaikan (Kolom M)
                        </label>
                        <textarea name="tindak_lanjut_awal" class="form-control" rows="2" placeholder="Rencana / komitmen perbaikan unit yang disepakati..." {{ $isLockedForRa ? 'readonly' : '' }}>{{ $simpulan->tindak_lanjut_awal }}</textarea>
                    </div>

                    {{-- PIC Unit (Kolom N) & Due Date (Kolom O) --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark text-uppercase mb-1">
                            PIC Unit yang Bertanggung Jawab (Kolom N)
                        </label>
                        <input type="text" name="pic_unit" class="form-control" value="{{ $simpulan->pic_unit }}" placeholder="Contoh: Pemimpin Cabang / Penyelia Operasional" {{ $isLockedForRa ? 'readonly' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark text-uppercase mb-1">
                            Due Date / Batas Waktu Penyelesaian (Kolom O)
                        </label>
                        <input type="date" name="due_date" class="form-control" value="{{ $simpulan->due_date ? $simpulan->due_date->format('Y-m-d') : '' }}" {{ $isLockedForRa ? 'readonly' : '' }}>
                    </div>

                    {{-- Eskalasi & Pelaporan (Kolom P, Q, R) --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-dark text-uppercase mb-1">Level Eskalasi (Kolom P)</label>
                        <select name="level_eskalasi" class="form-select" {{ $isLockedForRa ? 'disabled' : '' }}>
                            <option value="Tidak Perlu" {{ $simpulan->level_eskalasi == 'Tidak Perlu' ? 'selected' : '' }}>Tidak Perlu</option>
                            <option value="Pemimpin Cabang" {{ $simpulan->level_eskalasi == 'Pemimpin Cabang' ? 'selected' : '' }}>Pemimpin Cabang</option>
                            <option value="Kadiv SKAI" {{ $simpulan->level_eskalasi == 'Kadiv SKAI' ? 'selected' : '' }}>Kadiv SKAI</option>
                            <option value="Direksi / SOP 09" {{ $simpulan->level_eskalasi == 'Direksi / SOP 09' ? 'selected' : '' }}>Direksi / SOP 09</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-dark text-uppercase mb-1">Tujuan Eskalasi (Kolom Q)</label>
                        <input type="text" name="tujuan_eskalasi" class="form-control" value="{{ $simpulan->tujuan_eskalasi }}" placeholder="Pihak / divisi tujuan eskalasi" {{ $isLockedForRa ? 'readonly' : '' }}>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-dark text-uppercase mb-1">No / Referensi Laporan (Kolom R)</label>
                        <input type="text" name="laporan" class="form-control" value="{{ $simpulan->laporan }}" placeholder="Nomor laporan hasil audit RTV" {{ $isLockedForRa ? 'readonly' : '' }}>
                    </div>

                    {{-- Review Final & Formula Status Closure Box --}}
                    <div class="col-12 mt-2">
                        <div class="p-3 rounded border" style="background: #f8fafc;">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-primary text-uppercase mb-1">
                                        <i class="bi bi-shield-lock-fill me-1"></i> Status Review Final (Kolom V - Wewenang Admin / Reviewer)
                                    </label>
                                    @if(!$isRa)
                                    <select name="status_review" class="form-select fw-bold border-primary">
                                        <option value="DRAFT" {{ $simpulan->status_review == 'DRAFT' ? 'selected' : '' }}>DRAFT (Belum Direview)</option>
                                        <option value="REVISI" {{ $simpulan->status_review == 'REVISI' ? 'selected' : '' }}>REVISI (Perlu Perbaikan RA)</option>
                                        <option value="FINAL" {{ $simpulan->status_review == 'FINAL' ? 'selected' : '' }}>FINAL (Telah Disetujui / Selesai)</option>
                                    </select>
                                    <div class="small text-muted mt-1" style="font-size: 0.74rem;">
                                        Mengubah status menjadi <strong>FINAL</strong> akan menutup penugasan (CLOSED) bila TL dan klarifikasi lengkap.
                                    </div>
                                    @else
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        @if($simpulan->status_review === 'FINAL')
                                            <span class="badge-status badge-final"><i class="bi bi-lock-fill"></i> FINAL</span>
                                        @elseif($simpulan->status_review === 'REVISI')
                                            <span class="badge-status badge-revisi"><i class="bi bi-arrow-repeat"></i> REVISI</span>
                                        @else
                                            <span class="badge-status badge-draft"><i class="bi bi-pencil"></i> DRAFT</span>
                                        @endif
                                        <span class="small text-muted ms-2">(Wewenang Reviewer / Admin)</span>
                                    </div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                        Status Closure Otomatis (Kolom W - Formula Excel Sel W6)
                                    </label>
                                    <div>
                                        @if($simpulan->status_closure === 'CLOSED/DITERUSKAN')
                                            <span class="badge-status badge-closed fs-6"><i class="bi bi-check-circle-fill"></i> CLOSED / DITERUSKAN</span>
                                        @elseif($simpulan->status_closure === 'TL BELUM LENGKAP')
                                            <span class="badge-status badge-tl fs-6"><i class="bi bi-exclamation-triangle-fill"></i> TL BELUM LENGKAP</span>
                                        @elseif($simpulan->status_closure === 'KLARIFIKASI BELUM SELESAI')
                                            <span class="badge-status badge-klar fs-6"><i class="bi bi-hourglass-split"></i> KLARIFIKASI BELUM SELESAI</span>
                                        @else
                                            <span class="badge-status badge-open fs-6"><i class="bi bi-x-circle-fill"></i> BELUM DITUTUP</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted mt-1" style="font-size: 0.74rem;">
                                        Klarifikasi: <strong>{{ $simpulan->status_klarifikasi }}</strong> &bull; TL Lengkap: <strong>{{ (!empty($simpulan->tindak_lanjut_awal) && !empty($simpulan->pic_unit) && !empty($simpulan->due_date)) ? 'Ya' : 'Belum' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Actions (Hanya Tombol Simpan Tunggal, Tanpa Duplikat) --}}
                    @if(!$isLockedForRa)
                    <div class="col-12 d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary px-4 py-2">
                            <i class="bi bi-save me-1"></i> Simpan Simpulan & Tindak Lanjut
                        </button>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection
