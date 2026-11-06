@extends('layouts.app')

@section('title', 'Fraud Triage Checklist RA (POJK No. 12/2024) — Bank Sulteng')

@section('content')
<div class="container-fluid px-4 py-3" style="max-width: 1560px; margin: 0 auto;">

    <!-- Header Page: Desain Eksekutif Korporat -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge px-3 py-2 fs-7 fw-bold" style="background-color: #fee2e2; color: #991b1b; border-radius: 6px;">
                    <i class="bi bi-shield-shaded me-1"></i> POJK NO. 12 TAHUN 2024 &bull; STRATEGI ANTI FRAUD
                </span>
                <h3 class="mb-0 fw-bold" style="color: #0f2e5a; letter-spacing: -0.3px;">
                    FRAUD TRIAGE CHECKLIST – RESIDENT AUDITOR
                </h3>
            </div>
            <p class="text-secondary mb-0" style="font-size: 0.92rem;">
                Instrumen Pemeriksaan Awal Fraud &bull; Kantor Cabang Bank Umum &bull; Berdasarkan POJK No. 12 Tahun 2024 &amp; ACFE Fraud Examiners Manual (FEM)
            </p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            @if(isset($checklist) && $checklist->id)
                <a href="{{ route('pemeriksaan_awal.triage_checklist.print', $checklist->id) }}" target="_blank" class="btn btn-outline-dark rounded-pill px-3 shadow-sm fw-semibold" style="font-size: 0.88rem;">
                    <i class="bi bi-printer me-1"></i> Cetak Kertas Kerja
                </a>
            @endif
            <a href="{{ route('pemeriksaan_awal.indikasi_mencurigakan') }}" class="btn btn-outline-primary rounded-pill px-3 shadow-sm fw-semibold" style="font-size: 0.88rem;">
                <i class="bi bi-table me-1"></i> 18 Indikator Cabang
            </a>
            <a href="{{ route('pemeriksaan_awal.pelaporan_awal') }}" class="btn btn-primary rounded-pill px-3 shadow-sm fw-bold" style="font-size: 0.88rem; background: #1e3a8a; border-color: #1e3a8a;">
                <i class="bi bi-file-earmark-plus me-1"></i> Pelaporan Awal
            </a>
        </div>
    </div>

    @include('pemeriksaan_awal.partials.nav_tabs')

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4 p-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
            <div>
                <h6 class="mb-0 fw-bold text-success">Pemberitahuan Sukses</h6>
                <div style="font-size: 0.92rem;">{{ session('success') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Alert Banner Perhatian Resmi OJK -->
    <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 p-3 d-flex align-items-center gap-3" style="background:#fffbeb; border-left: 5px solid #f59e0b !important;">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: #fef3c7; color: #b45309; font-size: 1.4rem;">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div>
            <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                ⚠ PERHATIAN RESIDEN AUDITOR:
            </div>
            <div class="text-secondary" style="font-size: 0.88rem;">
                Checklist ini adalah <strong>instrumen triage awal (initial screening)</strong>, bukan instrumen audit investigasi mendalam. Jawaban <strong>'Ya'</strong> menunjukkan indikasi/anomali yang perlu ditelusuri faktanya, bukan kesimpulan bahwa telah terjadi tindak pidana fraud.
            </div>
        </div>
    </div>

    <!-- Banner Eskalasi Segera Trigger (Item 3, 15, 17) -->
    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 p-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background:#fef2f2; border: 1px solid #fecdd3 !important;">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: #fee2e2; color: #dc2626; font-size: 1.3rem;">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
            <div>
                <div class="fw-bold text-danger" style="font-size: 0.95rem;">
                    🚨 ESKALASI SEGERA (Tanpa Menunggu Checklist Selesai):
                </div>
                <div class="text-danger-emphasis" style="font-size: 0.85rem;">
                    Perhatikan khusus <strong>Item No. 3 (Reversal Berulang)</strong>, <strong>No. 15 (Selisih Kas Fisik)</strong>, dan <strong>No. 17 (Keluhan Transaksi Tidak Diperintahkan)</strong>. Apabila ditemukan indikasi bernilai <strong>'Ya'</strong>, sistem otomatis memicu status <strong>ESKALASI SEGERA</strong> ke Atasan Langsung &amp; Unit Anti Fraud.
                </div>
            </div>
        </div>
        <div id="badgeStatusCriticalTrigger" class="badge bg-secondary px-3 py-2 rounded-pill fs-7">
            <i class="bi bi-shield-check me-1"></i> Trigger ⚡: Belum Aktif
        </div>
    </div>

    <form action="{{ route('pemeriksaan_awal.triage_checklist.save') }}" method="POST" id="formFraudTriage">
        @csrf
        <input type="hidden" name="triage_id" value="{{ $checklist->id ?? '' }}">

        <!-- Card Identitas Pemeriksaan & History Selector -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.85rem;">
                        <i class="bi bi-building"></i>
                    </span>
                    <h5 class="mb-0 fw-bold" style="color: #0f2e5a; font-size: 1.05rem;">Data Identitas Pemeriksaan Awal</h5>
                </div>
                
                @if(isset($historyList) && $historyList->count() > 0)
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small fw-semibold">Pilih Riwayat Kertas Kerja:</span>
                    <select class="form-select form-select-sm" style="width: auto; font-size: 0.85rem;" onchange="if(this.value) window.location.href='{{ route('pemeriksaan_awal.triage_checklist') }}?id='+this.value">
                        <option value="">-- Kertas Kerja Terkini --</option>
                        @foreach($historyList as $hist)
                            <option value="{{ $hist->id }}" {{ (isset($checklist) && $checklist->id == $hist->id) ? 'selected' : '' }}>
                                {{ $hist->nomor_triage }} - {{ $hist->nama_cabang }} ({{ date('d/m/Y', strtotime($hist->tanggal_pemeriksaan)) }}) - [{{ $hist->keputusan_triage }}]
                            </option>
                        @endforeach
                    </select>
                    <a href="{{ route('pemeriksaan_awal.triage_checklist') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-plus-lg"></i> Baru
                    </a>
                </div>
                @endif
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-dark">Cabang / Unit Diperiksa <span class="text-danger">*</span></label>
                        <select name="nama_cabang" id="f_nama_cabang" class="form-select" required>
                            <option value="">-- Pilih Cabang --</option>
                            @php
                                $selectedCabang = $checklist->nama_cabang ?? 'Kantor Cabang Utama Palu (contoh)';
                            @endphp
                            <option value="Kantor Cabang Utama Palu (contoh)" {{ $selectedCabang == 'Kantor Cabang Utama Palu (contoh)' ? 'selected' : '' }}>Kantor Cabang Utama Palu (contoh)</option>
                            <option value="Kantor Cabang Parigi" {{ $selectedCabang == 'Kantor Cabang Parigi' ? 'selected' : '' }}>Kantor Cabang Parigi</option>
                            <option value="Kantor Cabang Tolitoli" {{ $selectedCabang == 'Kantor Cabang Tolitoli' ? 'selected' : '' }}>Kantor Cabang Tolitoli</option>
                            <option value="Kantor Cabang Luwuk" {{ $selectedCabang == 'Kantor Cabang Luwuk' ? 'selected' : '' }}>Kantor Cabang Luwuk</option>
                            <option value="Kantor Cabang Poso" {{ $selectedCabang == 'Kantor Cabang Poso' ? 'selected' : '' }}>Kantor Cabang Poso</option>
                            @foreach($units as $u)
                                @if(!in_array($u->unit_name, ['Kantor Cabang Utama Palu (contoh)', 'Kantor Cabang Parigi', 'Kantor Cabang Tolitoli', 'Kantor Cabang Luwuk', 'Kantor Cabang Poso']))
                                    <option value="{{ $u->unit_name }}" {{ $selectedCabang == $u->unit_name ? 'selected' : '' }}>{{ $u->unit_name }} ({{ $u->unit_code }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-dark">Tanggal Pemeriksaan Triage <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_pemeriksaan" class="form-control" value="{{ isset($checklist->tanggal_pemeriksaan) ? date('Y-m-d', strtotime($checklist->tanggal_pemeriksaan)) : date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-dark">Nama Resident Auditor (Pemeriksa) <span class="text-danger">*</span></label>
                        <input type="text" name="nama_ra" class="form-control" value="{{ $checklist->nama_ra ?? (auth()->user()->name ?? 'Resident Auditor') }}" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 Panel Indikator Counter Live & Status Mekanisme Triage -->
        <div class="row g-3 mb-4">
            <!-- Counter 1: Total Item -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-primary">
                    <span class="text-muted small fw-bold text-uppercase">Total Item Checklist</span>
                    <div class="d-flex align-items-center justify-content-between mt-1">
                        <h2 class="fw-bold mb-0 text-dark">28</h2>
                        <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill small">POJK 12/2024</span>
                    </div>
                    <small class="text-muted mt-1">7 Kategori Risiko Triage</small>
                </div>
            </div>

            <!-- Counter 2: Dijawab Ya -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-danger">
                    <span class="text-muted small fw-bold text-uppercase">Dijawab "YA" (Indikasi)</span>
                    <div class="d-flex align-items-center justify-content-between mt-1">
                        <h2 class="fw-bold mb-0 text-danger" id="countDijawabYa">0</h2>
                        <span class="badge bg-danger-subtle text-danger px-2.5 py-1 rounded-pill small" id="badgeCriticalCount">0 Kritis ⚡</span>
                    </div>
                    <small class="text-danger fw-medium mt-1">Memerlukan penelusuran fakta</small>
                </div>
            </div>

            <!-- Counter 3: Dijawab Tidak -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-success">
                    <span class="text-muted small fw-bold text-uppercase">Dijawab "TIDAK" (Wajar)</span>
                    <div class="d-flex align-items-center justify-content-between mt-1">
                        <h2 class="fw-bold mb-0 text-success" id="countDijawabTidak">0</h2>
                        <span class="badge bg-success-subtle text-success px-2.5 py-1 rounded-pill small">Kepatuhan Terpenuhi</span>
                    </div>
                    <small class="text-success mt-1">Tidak ditemukan red flag</small>
                </div>
            </div>

            <!-- Counter 4: Belum Dijawab -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-warning">
                    <span class="text-muted small fw-bold text-uppercase">Belum Dijawab</span>
                    <div class="d-flex align-items-center justify-content-between mt-1">
                        <h2 class="fw-bold mb-0 text-warning-emphasis" id="countBelumDijawab">28</h2>
                        <span class="badge bg-warning-subtle text-dark px-2.5 py-1 rounded-pill small">Pending</span>
                    </div>
                    <small class="text-muted mt-1">Wajib diselesaikan seluruhnya</small>
                </div>
            </div>
        </div>

        <!-- Mekanisme Hasil Triage Banner (Dinamis Sesuai Rules Excel OJK) -->
        <div class="card border-0 shadow-sm rounded-4 mb-4" id="cardMekanismeHasilTriage" style="border-left: 6px solid #e2e8f0 !important; background: #ffffff;">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge px-3 py-1.5 rounded-pill fw-bold fs-7" id="badgeKeputusanTriage">
                                KLARIFIKASI TAMBAHAN
                            </span>
                            <span class="text-muted small fw-semibold" id="labelEvaluasiOtomatis">Status Keputusan Otomatis Sesuai POJK 12/2024</span>
                        </div>
                        <h4 class="fw-bold mb-2 text-dark" id="titleKeputusanTriage">
                            🟡 KLARIFIKASI TAMBAHAN
                        </h4>
                        <div class="text-secondary mb-2" id="descKondisiKriteria" style="font-size: 0.92rem;">
                            Terdapat jawaban 'Ya' namun informasi yang tersedia belum memadai untuk menentukan apakah indikasi tersebut signifikan, atau terdapat item yang belum dapat dijawab.
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-success btn-sm fw-bold px-3 py-2" onclick="setSemuaTriage('Tidak')">
                                <i class="bi bi-check-all me-1"></i> Set Semua [✗] Tidak (Wajar)
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-3 py-2" onclick="resetSemuaTriage()">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Checklist
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Box Tindak Lanjut RA yang Wajib Dilakukan -->
                <div class="p-3 rounded-3 mt-3" id="boxTindakLanjutRa" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                    <div class="fw-bold small text-dark mb-1">
                        <i class="bi bi-arrow-right-circle-fill me-1 text-primary"></i> Tindak Lanjut Resident Auditor:
                    </div>
                    <ul class="mb-0 ps-3 small text-secondary" id="listTindakLanjutRa" style="line-height: 1.6;">
                        <li>Lakukan pengumpulan informasi tambahan.</li>
                        <li>Lakukan wawancara atau penelaahan dokumen lanjutan.</li>
                        <li>Tentukan batas waktu klarifikasi.</li>
                        <li>Setelah klarifikasi: putuskan Lanjut atau Tidak Dilanjutkan.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Tabel 28 Item Checklist Pertanyaan Berdasarkan POJK 12/2024 -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0 fw-bold" style="color: #0f2e5a;">
                        <i class="bi bi-list-check me-2 text-primary"></i>Daftar 28 Pertanyaan Triage Awal (Section A s/d G)
                    </h5>
                    <div class="text-muted small">Pilih Ya / Tidak pada masing-masing item, cantumkan catatan observasi lapangan dan referensi dokumen.</div>
                </div>
                <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill small">
                    Instrumen Resmi SAF
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tableChecklistTriage">
                        <thead class="table-light" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <tr>
                                <th style="width: 5%; text-align: center;">No</th>
                                <th style="width: 44%;">Deskripsi Pertanyaan &amp; Indikator</th>
                                <th style="width: 14%; text-align: center;">Checklist (Ya / Tidak)</th>
                                <th style="width: 20%;">Catatan / Observasi RA</th>
                                <th style="width: 17%;">Dasar Regulasi</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.88rem;">
                            @php
                                $savedMap = [];
                                if (isset($checklist->checklist_answers) && is_array($checklist->checklist_answers)) {
                                    foreach ($checklist->checklist_answers as $ans) {
                                        $savedMap[$ans['no']] = $ans;
                                    }
                                }
                                $currentSection = '';
                            @endphp

                            @foreach($masterQuestions as $q)
                                @if($currentSection !== $q['section'])
                                    @php $currentSection = $q['section']; @endphp
                                    <tr style="background: #f1f5f9; border-top: 2px solid #cbd5e1;">
                                        <td colspan="5" class="py-2.5 px-3 fw-bold text-primary" style="font-size: 0.88rem;">
                                            <i class="bi bi-folder2-open me-2"></i>{{ $currentSection }}
                                        </td>
                                    </tr>
                                @endif

                                @php
                                    $saved = $savedMap[$q['no']] ?? null;
                                    $val = $saved['jawaban'] ?? '';
                                    $cat = $saved['catatan'] ?? '';
                                    $isCritical = !empty($q['is_critical']);
                                @endphp

                                <tr id="row_q_{{ $q['no'] }}" class="{{ $val === 'Ya' ? ($isCritical ? 'table-danger' : 'table-warning') : '' }}">
                                    <td class="text-center fw-bold text-muted" style="font-size: 0.85rem;">
                                        {{ $q['no'] }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-start gap-2">
                                            @if($isCritical)
                                                <span class="badge bg-danger text-white flex-shrink-0 mt-0.5" title="Item Kritis Eskalasi Segera">
                                                    <i class="bi bi-lightning-charge-fill"></i> KRITIS ⚡
                                                </span>
                                            @endif
                                            <div>
                                                <span class="fw-semibold text-dark {{ $isCritical ? 'text-danger' : '' }}">{!! $q['question'] !!}</span>
                                                @if($isCritical)
                                                    <div class="small text-danger fw-bold mt-1">
                                                        <i class="bi bi-exclamation-circle me-1"></i>Pemicu Eskalasi Segera (Item No. {{ $q['no'] }})
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <input type="radio" class="btn-check triage-radio" name="chk_answer_{{ $q['no'] }}" id="triage_ya_{{ $q['no'] }}" value="Ya" data-no="{{ $q['no'] }}" data-critical="{{ $isCritical ? '1' : '0' }}" onchange="evaluateTriageDecision()" {{ $val === 'Ya' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-danger py-1 px-2.5 fw-bold" for="triage_ya_{{ $q['no'] }}" style="font-size: 0.78rem;">
                                                [✓] Ya
                                            </label>

                                            <input type="radio" class="btn-check triage-radio" name="chk_answer_{{ $q['no'] }}" id="triage_tidak_{{ $q['no'] }}" value="Tidak" data-no="{{ $q['no'] }}" data-critical="{{ $isCritical ? '1' : '0' }}" onchange="evaluateTriageDecision()" {{ $val === 'Tidak' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-success py-1 px-2.5 fw-bold" for="triage_tidak_{{ $q['no'] }}" style="font-size: 0.78rem;">
                                                [✗] Tidak
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="chk_catatan_{{ $q['no'] }}" class="form-control form-control-sm" placeholder="Catatan/observasi temuan..." value="{{ $cat }}" style="font-size: 0.8rem;">
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border fw-normal text-wrap text-start d-block p-1.5" style="font-size: 0.74rem; line-height: 1.35;">
                                            {{ $q['regulasi'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Card Catatan Kesimpulan & Tindak Lanjut RA -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.85rem;">
                        <i class="bi bi-file-earmark-text"></i>
                    </span>
                    <h5 class="mb-0 fw-bold" style="color: #0f2e5a;">Catatan &amp; Rekomendasi Kesimpulan RA</h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Keputusan Triage:</span>
                    <input type="hidden" name="keputusan_triage_override" id="f_keputusan_triage_override" value="{{ $checklist->keputusan_triage ?? 'KLARIFIKASI TAMBAHAN' }}">
                    <span class="badge bg-primary px-3 py-1.5 fs-7" id="badgeFormFinalKeputusan">
                        {{ $checklist->keputusan_triage ?? 'KLARIFIKASI TAMBAHAN' }}
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Uraian Ringkasan Pertimbangan RA <span class="text-danger">*</span></label>
                    <textarea name="catatan_kesimpulan" id="f_catatan_kesimpulan" rows="3" class="form-control" placeholder="Jelaskan dasar pertimbangan hasil pengujian triage awal, kondisi faktual, dan langkah tindak lanjut yang direkomendasikan..." required>{{ $checklist->catatan_kesimpulan ?? '' }}</textarea>
                </div>

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top">
                    <div class="text-muted small">
                        <i class="bi bi-info-circle me-1 text-primary"></i> Data triage dapat langsung disimpan sebagai kertas kerja arsip atau diteruskan ke Pelaporan Awal Indikasi Fraud.
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="action" value="save_only" class="btn btn-outline-primary px-4 py-2 rounded-pill fw-bold">
                            <i class="bi bi-save me-1"></i> Simpan Kertas Kerja Triage
                        </button>
                        <button type="submit" name="action" value="eskalasi_pelaporan" class="btn btn-danger px-4 py-2 rounded-pill fw-bold shadow-sm" style="background:#dc2626; border-color:#dc2626;">
                            <i class="bi bi-lightning-charge-fill me-1"></i> Eskalasikan ke Pelaporan Awal Indikasi Fraud
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </form>

    <!-- Tabel Rujukan Sumber Regulasi dan Referensi POJK 12/2024 -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold" style="color: #0f2e5a;">
                <i class="bi bi-book-half me-2 text-primary"></i>Penjelasan Sumber Regulasi dan Referensi
            </h5>
            <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill small">Dasar Hukum &amp; Standar Profesi</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light text-uppercase" style="font-size: 0.78rem;">
                        <tr>
                            <th style="width: 5%; text-align: center;">No</th>
                            <th style="width: 25%;">Nama Sumber</th>
                            <th style="width: 15%;">Jenis Referensi</th>
                            <th style="width: 40%;">Keterangan Penggunaan</th>
                            <th style="width: 15%;">Berlaku Untuk</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center fw-bold">1</td>
                            <td class="fw-bold text-dark">POJK No. 12 Tahun 2024 tentang Penerapan Strategi Anti Fraud bagi Lembaga Jasa Keuangan</td>
                            <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle">Regulasi Mengikat</span></td>
                            <td class="text-secondary">Regulasi utama yang mewajibkan LJK termasuk Bank Umum untuk menerapkan strategi anti fraud mencakup empat pilar: Pencegahan, Deteksi, Investigasi, serta Monitoring dan Tindak Lanjut. Menjadi dasar hukum kewajiban mekanisme triage atas indikasi fraud.</td>
                            <td class="fw-semibold text-dark">Bank Umum, BPR, LJK</td>
                        </tr>
                        <tr>
                            <td class="text-center fw-bold">2</td>
                            <td class="fw-bold text-dark">Panduan Strategi Anti Fraud bagi BPR (OJK, 2022)</td>
                            <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">Referensi Praktik</span></td>
                            <td class="text-secondary">Digunakan sebagai referensi praktik untuk indikator dan red flags fraud di kantor cabang. Substansi indikatornya digunakan sebagai panduan praktis dalam menyusun instrumen screening lapangan.</td>
                            <td class="fw-semibold text-dark">BPR (panduan); Bank Umum (referensi praktik)</td>
                        </tr>
                        <tr>
                            <td class="text-center fw-bold">3</td>
                            <td class="fw-bold text-dark">ACFE Fraud Examiners Manual – Bagian Initial Response &amp; Initial Assessment</td>
                            <td><span class="badge bg-warning-subtle text-dark border border-warning-subtle">Referensi Metodologi</span></td>
                            <td class="text-secondary">Digunakan sebagai referensi metodologi profesional untuk prinsip pemeriksaan awal atas dugaan fraud, termasuk konsep triage, identifikasi red flags perilaku (Fraud Triangle: Pressure, Opportunity, Rationalization), dan tahapan initial assessment.</td>
                            <td class="fw-semibold text-dark">Referensi Internasional ACFE</td>
                        </tr>
                        <tr>
                            <td class="text-center fw-bold">4</td>
                            <td class="fw-bold text-dark">Prinsip Kehati-hatian Perbankan &amp; Kebijakan Internal Bank</td>
                            <td><span class="badge bg-light text-dark border">Prinsip &amp; SOP Internal</span></td>
                            <td class="text-secondary">Untuk item terkait kredit, penyalahgunaan kewenangan, dan pengendalian internal, checklist mengacu pada prinsip kehati-hatian (prudential banking) yang berlaku umum dan Buku Pedoman Perusahaan (BPP).</td>
                            <td class="fw-semibold text-dark">Bank Umum (PT Bank Sulteng)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light p-3 small text-muted">
            <i class="bi bi-info-circle me-1"></i> Catatan: Seluruh nomor regulasi dapat diverifikasi melalui situs resmi OJK (ojk.go.id) dan JDIH OJK. Ketentuan internal bank yang dirujuk disesuaikan dengan dokumen resmi yang berlaku di Bank Sulteng.
        </div>
    </div>

</div>

@push('scripts')
<script>
    /**
     * Script Evaluasi Triage Otomatis Berdasarkan POJK No. 12 Tahun 2024
     */
    function evaluateTriageDecision() {
        let yaCount = 0;
        let tidakCount = 0;
        let belumCount = 0;
        let hasCriticalTrigger = false;

        for (let i = 1; i <= 28; i++) {
            const yaRadio = document.getElementById('triage_ya_' + i);
            const tidakRadio = document.getElementById('triage_tidak_' + i);
            const row = document.getElementById('row_q_' + i);
            const isCritical = (i === 3 || i === 15 || i === 17);

            if (yaRadio && yaRadio.checked) {
                yaCount++;
                if (isCritical) {
                    hasCriticalTrigger = true;
                }
                if (row) {
                    row.className = isCritical ? 'table-danger' : 'table-warning';
                }
            } else if (tidakRadio && tidakRadio.checked) {
                tidakCount++;
                if (row) {
                    row.className = '';
                }
            } else {
                belumCount++;
                if (row) {
                    row.className = '';
                }
            }
        }

        // Update live counters
        document.getElementById('countDijawabYa').innerText = yaCount;
        document.getElementById('countDijawabTidak').innerText = tidakCount;
        document.getElementById('countBelumDijawab').innerText = belumCount;

        // Critical trigger badge
        const badgeCrit = document.getElementById('badgeCriticalCount');
        const badgeCritTrigger = document.getElementById('badgeStatusCriticalTrigger');
        if (hasCriticalTrigger) {
            badgeCrit.className = 'badge bg-danger text-white px-2.5 py-1 rounded-pill small animate__animated animate__pulse animate__infinite';
            badgeCrit.innerText = '⚡ KRITIS AKTIF!';
            badgeCritTrigger.className = 'badge bg-danger text-white px-3 py-2 rounded-pill fs-7';
            badgeCritTrigger.innerHTML = '<i class="bi bi-lightning-charge-fill me-1"></i> Trigger ⚡: AKTIF (Eskalasi Segera)';
        } else {
            badgeCrit.className = 'badge bg-danger-subtle text-danger px-2.5 py-1 rounded-pill small';
            badgeCrit.innerText = '0 Kritis ⚡';
            badgeCritTrigger.className = 'badge bg-secondary text-white px-3 py-2 rounded-pill fs-7';
            badgeCritTrigger.innerHTML = '<i class="bi bi-shield-check me-1"></i> Trigger ⚡: Belum Aktif';
        }

        // Rules Evaluasi Triage POJK 12/2024:
        let keputusan = 'KLARIFIKASI TAMBAHAN';
        let badgeClass = 'bg-warning-subtle text-dark border border-warning';
        let title = '🟡 KLARIFIKASI TAMBAHAN';
        let desc = 'Terdapat jawaban "Ya" namun informasi yang tersedia belum memadai untuk menentukan apakah indikasi tersebut signifikan, atau terdapat item yang belum dapat dijawab.';
        let borderLeft = '#f59e0b';
        let tindakLanjut = [
            'Lakukan pengumpulan informasi tambahan.',
            'Lakukan wawancara atau penelaahan dokumen lanjutan.',
            'Tentukan batas waktu klarifikasi.',
            'Setelah klarifikasi: putuskan Lanjut Penyelidikan atau Tidak Dilanjutkan.'
        ];

        if (hasCriticalTrigger) {
            keputusan = 'ESKALASI SEGERA';
            badgeClass = 'bg-danger text-white';
            title = '🔴 ESKALASI SEGERA (Tanpa Menunggu Checklist Selesai)';
            desc = 'Ditemukan jawaban "Ya" pada salah satu atau lebih item yang ditandai ⚡ (No. 3, 15, atau 17), menunjukkan potensi kerugian yang masih berlangsung atau risiko hilangnya bukti.';
            borderLeft = '#dc2626';
            tindakLanjut = [
                'Laporkan segera kepada atasan langsung dan/atau unit Anti Fraud SKAI.',
                'Amankan bukti fisik atau log digital yang tersedia (Chain of Custody).',
                'Lanjutkan checklist paralel dengan proses eskalasi.',
                'Jangan ubah atau pindahkan dokumen/bukti tanpa berita acara serah terima.'
            ];
        } else if (belumCount === 0 && yaCount > 0) {
            keputusan = 'LANJUT KE PENYELIDIKAN';
            badgeClass = 'bg-danger-subtle text-danger border border-danger';
            title = '🔴 LANJUT KE PENYELIDIKAN';
            desc = 'Terdapat satu atau lebih jawaban "Ya" yang didukung fakta atau bukti awal yang dapat diverifikasi, mengindikasikan penyimpangan yang perlu ditelusuri secara formal.';
            borderLeft = '#b91c1c';
            tindakLanjut = [
                'Eskalasikan ke unit Audit Internal atau Anti Fraud.',
                'Dokumentasikan fakta dan bukti awal dalam register.',
                'Sertakan kertas kerja checklist triage ini sebagai lampiran.',
                'Penentuan fraud dilakukan oleh pejabat berwenang melalui proses penyelidikan formal SKAI.'
            ];
        } else if (belumCount === 0 && yaCount === 0) {
            keputusan = 'TIDAK DILANJUTKAN';
            badgeClass = 'bg-success text-white';
            title = '🟢 TIDAK DILANJUTKAN (Kepatuhan Normal)';
            desc = 'Seluruh 28 item dijawab "Tidak" berdasarkan pengecekan yang memadai, atau seluruh jawaban awal telah memperoleh penjelasan wajar yang dapat diverifikasi.';
            borderLeft = '#16a34a';
            tindakLanjut = [
                'Dokumentasikan alasan penutupan secara tertulis.',
                'Arsipkan kertas kerja checklist ini dalam file pemeriksaan cabang.',
                'Pastikan dokumentasi dapat ditelusuri kembali (audit trail) jika diperlukan.',
                'Resident Auditor menandatangani kertas kerja penutupan.'
            ];
        }

        // Render hasil evaluasi ke tampilan UI
        const cardMekanisme = document.getElementById('cardMekanismeHasilTriage');
        if (cardMekanisme) {
            cardMekanisme.style.borderLeft = '6px solid ' + borderLeft + ' !important';
        }
        document.getElementById('badgeKeputusanTriage').className = 'badge px-3 py-1.5 rounded-pill fw-bold fs-7 ' + badgeClass;
        document.getElementById('badgeKeputusanTriage').innerText = keputusan;
        document.getElementById('titleKeputusanTriage').innerText = title;
        document.getElementById('descKondisiKriteria').innerText = desc;

        document.getElementById('f_keputusan_triage_override').value = keputusan;
        const badgeFormFinal = document.getElementById('badgeFormFinalKeputusan');
        if (badgeFormFinal) {
            badgeFormFinal.innerText = keputusan;
            badgeFormFinal.className = 'badge px-3 py-1.5 fs-7 ' + (keputusan === 'ESKALASI SEGERA' ? 'bg-danger' : (keputusan === 'LANJUT KE PENYELIDIKAN' ? 'bg-danger-subtle text-danger' : (keputusan === 'TIDAK DILANJUTKAN' ? 'bg-success' : 'bg-warning-subtle text-dark')));
        }

        const listTl = document.getElementById('listTindakLanjutRa');
        if (listTl) {
            listTl.innerHTML = tindakLanjut.map(t => `<li>${t}</li>`).join('');
        }
    }

    function setSemuaTriage(val) {
        for (let i = 1; i <= 28; i++) {
            const rad = document.getElementById(val === 'Ya' ? 'triage_ya_' + i : 'triage_tidak_' + i);
            if (rad) rad.checked = true;
        }
        evaluateTriageDecision();
    }

    function resetSemuaTriage() {
        for (let i = 1; i <= 28; i++) {
            const ya = document.getElementById('triage_ya_' + i);
            const tidak = document.getElementById('triage_tidak_' + i);
            if (ya) ya.checked = false;
            if (tidak) tidak.checked = false;
        }
        evaluateTriageDecision();
    }

    document.addEventListener('DOMContentLoaded', function () {
        evaluateTriageDecision();
    });
</script>
@endpush

@endsection
