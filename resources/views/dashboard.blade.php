@extends('layouts.app')
@section('title', 'Dashboard Utama - Sistem Resident Auditor')

@section('content')
<div class="container-fluid px-2 py-1">
    <!-- Header Selamat Datang -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold text-dark mb-1" style="font-size: 1.45rem;">
                <i class="bi bi-speedometer2 text-primary me-2"></i> Dashboard Utama Pengawasan Resident Auditor
            </h2>
            <p class="text-muted mb-0 small">
                Selamat datang kembali, <strong>{{ auth()->user()->name }}</strong> &mdash; {{ now()->isoFormat('dddd, D MMMM Y') }} | PT Bank Sulteng
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('onsite.create') }}" class="btn btn-sm btn-outline-primary fw-bold shadow-sm">
                <i class="bi bi-geo-alt-fill me-1"></i> + Kunjungan Onsite
            </a>
            <a href="{{ route('surprise_audit.create') }}" class="btn btn-sm btn-outline-warning text-dark fw-bold shadow-sm">
                <i class="bi bi-lightning-charge-fill text-warning me-1"></i> + Surprise Audit
            </a>
            <a href="{{ route('monitoring.terpadu') }}" class="btn btn-sm btn-primary fw-bold shadow-sm">
                <i class="bi bi-layers-fill me-1"></i> Monitoring Terpadu
            </a>
        </div>
    </div>

{{-- ALERT BANNER PERINGATAN MENCOLOK RISK TRIGGER (> 10 TEMUAN) --}}
@php
    $uninformedAlarms = collect($riskTriggerAlarms ?? [])->where('is_followed_up', false)->values()->all();
@endphp
@if(strtolower(auth()->user()->role ?? '') === 'admin' && !empty($uninformedAlarms))
<div class="alert alert-danger border-danger-subtle p-3.5 mb-4 shadow-sm" style="border-radius: 12px; background: #fff5f5; border: 2px solid #ef4444;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger text-white px-2.5 py-1.5" style="font-size: 0.8rem; font-weight: 700; letter-spacing: 0.03em;">
                <i class="bi bi-bell-fill me-1"></i> ALARM RISK TRIGGER (SOP 06)
            </span>
            <span class="fw-bold text-danger" style="font-size: 0.96rem;">
                Terdeteksi {{ count($uninformedAlarms) }} Cabang Melampaui Ambang Batas Akumulasi (&gt; 10 Temuan)
            </span>
        </div>
        <a href="{{ route('risk-trigger.index') }}" class="btn btn-sm btn-danger px-3 py-1 rounded-pill fw-bold" style="font-size: 0.8rem;">
            Buka SOP 06 Alarm Center &rarr;
        </a>
    </div>
    <div style="font-size: 0.82rem; color: #7f1d1d; margin-bottom: 0.75rem;">
        Alarm sistem menyala otomatis karena akumulasi temuan per cabang di bawah telah menembus ambang batas (&gt; 10 temuan). Admin dapat segera menginformasikan ke pimpinan untuk pendalaman lebih lanjut:
    </div>
    <div class="row g-2">
        @foreach($uninformedAlarms as $alarm)
        @php
            $alarmModalId = 'modalDashAlarm_' . (\Illuminate\Support\Str::slug($alarm['kode_unit']) ?: md5($alarm['kode_unit']));
        @endphp
        <div class="col-md-4">
            <div class="p-3 bg-white rounded border border-danger-subtle h-100 d-flex flex-column justify-content-between shadow-2xs">
                <div>
                    <div class="d-flex justify-content-between align-items-start gap-1">
                        <div class="fw-bold text-danger" style="font-size: 0.88rem;">{{ $alarm['nama_unit'] }} ({{ $alarm['kode_unit'] }})</div>
                        <span class="badge bg-danger text-white">{{ $alarm['count'] }} Temuan</span>
                    </div>
                    <div class="text-dark small fw-semibold mt-1">Area Terbanyak: <span class="text-primary">{{ $alarm['area'] }}</span></div>
                    <div class="text-muted mt-1" style="font-size: 0.76rem;">
                        Sebaran: <strong>{{ $alarm['sebaran_area'] ?? $alarm['area'] }}</strong>
                    </div>
                    @if(!empty($alarm['is_followed_up']) && !empty($alarm['catatan_tindak_lanjut']))
                        <div class="mt-1.5 p-1.5 bg-success-subtle rounded border border-success-subtle text-success small" style="font-size: 0.74rem;">
                            <i class="bi bi-info-circle me-1"></i>{{ Str::limit($alarm['catatan_tindak_lanjut'], 70) }}
                        </div>
                    @endif
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2.5 pt-2 border-top">
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold" style="font-size: 0.72rem;">
                        <i class="bi bi-clock-history me-0.5"></i> Perlu Diinfokan
                    </span>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-xs btn-outline-danger py-0.5 px-2 rounded-pill" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#{{ $alarmModalId }}">
                            <i class="bi bi-send-check me-0.5"></i> Tindak Lanjut
                        </button>
                        <a href="{{ route('risk-trigger.index') }}" class="btn btn-xs btn-outline-secondary py-0.5 px-2 rounded-pill" style="font-size: 0.75rem;">
                            Detail &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL TINDAK LANJUT ALARM --}}
        <div class="modal fade" id="{{ $alarmModalId }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('risk-trigger.alarm.follow-up') }}" method="POST" class="modal-content text-start">
                    @csrf
                    <input type="hidden" name="kode_unit" value="{{ $alarm['kode_unit'] }}">
                    <div class="modal-header bg-danger text-white py-2.5 px-3">
                        <h6 class="modal-title fs-6 fw-bold">
                            <i class="bi bi-bell-fill me-1"></i> Tindak Lanjut Alarm Cabang: {{ $alarm['nama_unit'] }}
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3">
                        <div class="alert alert-light border p-2.5 mb-3 small">
                            <div><strong>Kantor Cabang:</strong> {{ $alarm['nama_unit'] }} ({{ $alarm['kode_unit'] }})</div>
                            <div><strong>Akumulasi Temuan:</strong> <span class="badge bg-danger text-white">{{ $alarm['count'] }} Temuan (&gt; 10)</span></div>
                            <div class="text-muted mt-1">Sebaran Area: {{ $alarm['sebaran_area'] ?? $alarm['area'] }}</div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-dark mb-1">
                                Catatan / Konfirmasi Penginformasian <span class="text-danger">*</span>
                            </label>
                            <textarea name="catatan" class="form-control" rows="3" required placeholder="Tuliskan catatan bahwa alarm telah diinformasikan ke manajemen/Pak Adam...">{{ old('catatan', $alarm['catatan_tindak_lanjut'] ?? 'Diteruskan ke Pemimpin Cabang & Tim SKAI untuk pendalaman') }}</textarea>
                            <div class="form-text small text-muted">
                                Status alarm akan ditandai Sudah Diinfokan tanpa perlu form KKA/review panjang.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2 px-3">
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold">
                            <i class="bi bi-check2-circle me-1"></i> Tandai Sudah Diinfokan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

    <!-- ALERT BANNER INDIKASI FRAUD (SOP 09) - HANYA MUNCUL JIKA ADA KASUS AKTIF -->
    @if($activeFraudCases > 0)
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4 border-0 d-flex justify-content-between align-items-center p-3" role="alert">
        <div class="d-flex align-items-center">
            <div class="bg-white text-danger p-2 rounded-circle me-3">
                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-danger">PERINGATAN KHUSUS: Terdapat {{ $activeFraudCases }} Kasus Indikasi Fraud Aktif (SOP 09)</h6>
                <small class="text-dark">Pemeriksaan awal indikasi kecurangan/penyimpangan operasional sedang ditangani SKAI.</small>
            </div>
        </div>
        <a href="{{ route('pemeriksaan_awal.index') }}" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold shadow-sm">
            Lihat Kasus Fraud <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    @endif

    <!-- 5 KARTU STATISTIK EKSEKUTIF LINTAS MODUL (KLIK UNTUK MENUJU MODUL TERKAIT) -->
    <div class="row g-3 mb-4">
        <!-- 1. Perencanaan & Unit (SOP 01) -->
        <div class="col-md">
            <a href="{{ route('units.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white border-start border-4 border-primary hover-shadow transition">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">SOP 01: Unit Kerja</small>
                            <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalUnit }}</h3>
                            <small class="text-secondary" style="font-size: 0.75rem;">{{ $totalRa }} Resident Auditor</small>
                        </div>
                        <div class="bg-primary-subtle text-primary p-2 rounded-circle">
                            <i class="bi bi-building fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- 2. Kunjungan Onsite (SOP 04) -->
        <div class="col-md">
            <a href="{{ route('onsite.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white border-start border-4 border-info hover-shadow transition">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">SOP 04: Onsite Cabang</small>
                            <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalOnsiteVisits }} Visit</h3>
                            <small class="text-info fw-bold" style="font-size: 0.75rem;">{{ $onsiteBerlangsung }} Berlangsung</small>
                        </div>
                        <div class="bg-info-subtle text-info p-2 rounded-circle">
                            <i class="bi bi-geo-alt fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- 3. Surprise Audit Fisik (SOP 05) -->
        <div class="col-md">
            <a href="{{ route('surprise_audit.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white border-start border-4 border-warning hover-shadow transition">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">SOP 05: Surprise Audit</small>
                            <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalSurpriseAudit }} Sidak</h3>
                            <div class="mt-1">
                                <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">{{ $saDalamProses }} Proses</span>
                                @if($saPerluPerbaikan > 0)
                                    <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">{{ $saPerluPerbaikan }} Revisi</span>
                                @else
                                    <span class="badge bg-success-subtle text-success ms-1" style="font-size: 0.65rem;">{{ $saSelesai }} Selesai</span>
                                @endif
                            </div>
                        </div>
                        <div class="bg-warning-subtle text-warning p-2 rounded-circle">
                            <i class="bi bi-lightning-charge fs-5 text-warning"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- 4. Monitoring Temuan Internal (SOP 07 Sub 1) -->
        <div class="col-md">
            <a href="{{ route('monitoring.terpadu', ['tab' => 'internal', 'status' => 'belum_tl']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white border-start border-4 border-danger hover-shadow transition">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">SOP 07: Temuan Internal</small>
                            <h3 class="fw-bold text-danger mb-0 mt-1">{{ $temuanInternalBelumTl }} Belum TL</h3>
                            <small class="text-success fw-semibold" style="font-size: 0.75rem;"><i class="bi bi-check-circle me-1"></i>{{ $temuanInternalSudahTl }} Sudah TL</small>
                        </div>
                        <div class="bg-danger-subtle text-danger p-2 rounded-circle">
                            <i class="bi bi-exclamation-octagon fs-5 text-danger"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- 5. Monitoring Temuan Regulator (SOP 07 Sub 2) -->
        <div class="col-md">
            <a href="{{ route('monitoring.terpadu', ['tab' => 'regulator']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white border-start border-4 border-dark hover-shadow transition">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">SOP 07: Regulator (OJK)</small>
                            <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalTemuanRegulator }} Temuan</h3>
                            <div class="mt-1">
                                <span class="badge bg-danger" style="font-size: 0.65rem;">{{ $temuanRegulatorBelumTl }} Belum</span>
                                <span class="badge bg-success ms-1" style="font-size: 0.65rem;">{{ $temuanRegulatorSudahTl }} Sudah</span>
                            </div>
                        </div>
                        <div class="bg-secondary-subtle text-dark p-2 rounded-circle">
                            <i class="bi bi-bank2 fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN UTAMA: ANTREAN TUGAS BELUM SELESAI (ACTIONABLE TO-DO LIST RA) -->
    <!-- Ketika diklik, langsung pindah ke halaman pekerjaan yang belum selesai -->
    <!-- ========================================================================= -->
    <div class="row g-4 mb-4">
        <!-- 1. KUNJUNGAN ONSITE & SURPRISE AUDIT YANG SEDANG BERJALAN -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-briefcase text-primary me-2"></i> Audit Lapangan Sedang Berjalan (SOP 04 & 05)
                        </h6>
                        <small class="text-muted">Klik penugasan untuk langsung membuka KKA fisik yang perlu dikerjakan</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Nav Tabs Internal -->
                    <ul class="nav nav-tabs nav-fill border-bottom bg-light px-2 pt-2" id="taskTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active fw-bold small py-2" id="sa-tab" data-bs-toggle="tab" data-bs-target="#tab-sa" type="button">
                                <i class="bi bi-lightning-charge text-warning me-1"></i> Surprise Audit ({{ $pendingSurpriseAudits->count() }})
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold small py-2" id="onsite-tab" data-bs-toggle="tab" data-bs-target="#tab-onsite" type="button">
                                <i class="bi bi-geo-alt text-info me-1"></i> Onsite Cabang ({{ $pendingOnsiteVisits->count() }})
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="taskTabContent">
                        <!-- Tab Surprise Audit -->
                        <div class="tab-pane fade show active" id="tab-sa" role="tabpanel">
                            <div class="list-group list-group-flush">
                                @forelse($pendingSurpriseAudits as $sa)
                                @php
                                    $isRejected = ($sa->status_review_admin === 'PERLU PERBAIKAN');
                                    $badgeClass = $isRejected ? 'bg-danger text-white' : ($sa->status_assignment === 'DALAM PROSES' ? 'bg-warning text-dark' : 'bg-info text-dark');
                                    $statusLabel = $isRejected ? 'REVISI DITOLAK REVIEWER' : $sa->status_assignment;
                                @endphp
                                <div class="list-group-item p-3 d-flex justify-content-between align-items-center hover-bg-light transition">
                                    <div class="me-3" style="max-width: 70%;">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="fw-bold text-primary">{{ $sa->visit_id }}</span>
                                            <span class="badge {{ $badgeClass }}" style="font-size: 0.68rem;">{{ $statusLabel }}</span>
                                        </div>
                                        <h6 class="mb-1 fw-bold text-dark" style="font-size: 0.9rem;">
                                            <i class="bi bi-building text-secondary me-1"></i>{{ $sa->nama_unit }}
                                        </h6>
                                        <small class="text-muted d-block" style="font-size: 0.76rem;">
                                            <i class="bi bi-calendar-event me-1"></i>{{ $sa->tanggal_hari ? $sa->tanggal_hari . ', ' : '' }}{{ $sa->tanggal_rencana ? date('d/m/Y', strtotime($sa->tanggal_rencana)) : '-' }} &bull; RA: {{ $sa->resident_auditor ?? 'RA' }}
                                        </small>
                                        @if($isRejected && $sa->catatan_review_admin)
                                        <div class="alert alert-danger py-1 px-2 mt-1 mb-0 small" style="font-size: 0.75rem;">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Koreksi:</strong> {{ $sa->catatan_review_admin }}
                                        </div>
                                        @endif
                                    </div>
                                    <div>
                                        <!-- Tombol Pindah Langsung ke Halaman Surprise Audit -->
                                        <a href="{{ route('surprise_audit.show', $sa->id) }}" class="btn btn-sm {{ $isRejected ? 'btn-danger' : 'btn-primary' }} rounded-pill px-3 shadow-sm text-nowrap fw-bold" style="font-size: 0.78rem;">
                                            {{ $isRejected ? 'Perbaiki KKA' : 'Lanjutkan' }} <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-check-circle text-success fs-3 d-block mb-1"></i>
                                    <span class="fw-bold small">Semua penugasan Surprise Audit telah selesai.</span>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Tab Onsite Visit -->
                        <div class="tab-pane fade" id="tab-onsite" role="tabpanel">
                            <div class="list-group list-group-flush">
                                @forelse($pendingOnsiteVisits as $ov)
                                <div class="list-group-item p-3 d-flex justify-content-between align-items-center hover-bg-light transition">
                                    <div class="me-3" style="max-width: 70%;">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 0.68rem;">SOP 04 ONSITE</span>
                                            <span class="badge bg-warning text-dark" style="font-size: 0.68rem;">{{ $ov->status ?? 'Berlangsung' }}</span>
                                        </div>
                                        <h6 class="mb-1 fw-bold text-dark" style="font-size: 0.9rem;">
                                            <i class="bi bi-geo-alt text-danger me-1"></i>{{ $ov->nama_unit }}
                                        </h6>
                                        <small class="text-muted d-block" style="font-size: 0.76rem;">
                                            <i class="bi bi-calendar-range me-1"></i>Mulai: {{ $ov->tanggal_mulai ? $ov->tanggal_mulai->format('d/m/Y') : '-' }} s/d {{ $ov->tanggal_selesai ? $ov->tanggal_selesai->format('d/m/Y') : '-' }}
                                        </small>
                                    </div>
                                    <div>
                                        <!-- Tombol Pindah Langsung ke KKA Onsite -->
                                        <a href="{{ route('onsite.kka', $ov->id) }}" class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-sm text-nowrap fw-bold" style="font-size: 0.78rem;">
                                            Buka KKA <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-check-circle text-success fs-3 d-block mb-1"></i>
                                    <span class="fw-bold small">Tidak ada jadwal visit Onsite yang tertunda.</span>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. TEMUAN BELUM SELESAI (SOP 07) & STATUS PELAPORAN BULANAN (SOP 08) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-hourglass-split text-danger me-2"></i> Pengawasan Temuan & Pelaporan (SOP 07 & 08)
                        </h6>
                        <small class="text-muted">Klik untuk langsung membuka dan menyelesaikan tindak lanjut</small>
                    </div>
                    <a href="{{ route('monitoring.terpadu', ['status' => 'belum_tl']) }}" class="btn btn-sm btn-outline-danger py-1 px-2" style="font-size: 0.75rem;">
                        Buka Monitoring
                    </a>
                </div>
                <div class="card-body p-0">
                    <!-- Widget Pengingat Laporan Bulanan (SOP 08) -->
                    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.7rem;">SOP 08: Laporan Bulanan Periode Ini ({{ now()->isoFormat('MMMM Y') }})</small>
                            <span class="badge {{ $laporanBulanIniStatus === 'Approved' ? 'bg-success' : ($laporanBulanIniStatus === 'Belum Diajukan' ? 'bg-warning text-dark' : 'bg-primary') }} mt-1">
                                Status: {{ $laporanBulanIniStatus }}
                            </span>
                        </div>
                        <a href="{{ route('pelaporan-bulanan.kalender') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold" style="font-size: 0.75rem;">
                            Kalender Laporan <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <!-- Daftar Temuan Belum TL -->
                    <div class="list-group list-group-flush">
                        @forelse($pendingFindings as $f)
                        @php
                            $isOverdue = $f->target_penyelesaian && strtotime($f->target_penyelesaian) < strtotime(date('Y-m-d'));
                            $isRegulator = in_array($f->sumber_tl, ['Regulator', 'OJK', 'Bank Indonesia', 'BI']);
                            $tabTarget = $isRegulator ? 'regulator' : 'internal';
                        @endphp
                        <div class="list-group-item p-3 d-flex justify-content-between align-items-center hover-bg-light transition">
                            <div class="me-3" style="max-width: 72%;">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-light text-dark border">{{ $f->tl_id }}</span>
                                    <span class="badge {{ $isRegulator ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' }}" style="font-size: 0.68rem;">
                                        {{ $f->sumber_tl }}
                                    </span>
                                    @if($isOverdue)
                                        <span class="badge bg-danger animate__animated animate__pulse animate__infinite" style="font-size: 0.65rem;">
                                            <i class="bi bi-clock-history me-1"></i>JATUH TEMPO
                                        </span>
                                    @endif
                                </div>
                                <h6 class="mb-1 fw-bold text-dark text-truncate" style="font-size: 0.88rem;" title="{{ $f->rekomendasi_temuan }}">
                                    {{ $f->rekomendasi_temuan }}
                                </h6>
                                <small class="text-muted d-block" style="font-size: 0.76rem;">
                                    <i class="bi bi-building me-1"></i>{{ $f->nama_unit ?? 'Bank Sulteng' }} &bull; Target: 
                                    <span class="{{ $isOverdue ? 'text-danger fw-bold' : 'text-dark' }}">
                                        {{ $f->target_penyelesaian ? date('d/m/Y', strtotime($f->target_penyelesaian)) : '-' }}
                                    </span>
                                </small>
                            </div>
                            <div>
                                <!-- Tombol Pindah Langsung ke Monitoring Terpadu -->
                                <a href="{{ route('monitoring.terpadu', ['tab' => $tabTarget, 'status' => 'belum_tl', 'search' => $f->tl_id]) }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm text-nowrap fw-bold" style="font-size: 0.78rem;">
                                    Tindak Lanjut <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-shield-check text-success fs-3 d-block mb-1"></i>
                            <span class="fw-bold small">Seluruh temuan telah selesai ditindaklanjuti.</span>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN BAWAH: DISTRIBUSI RISIKO & AUDIT PLAN (SOP 01) -->
    <!-- ========================================================================= -->
    <div class="row g-4">
        <!-- Distribusi Kategori Risiko Unit (SOP 01) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-pie-chart text-primary me-2"></i> Distribusi Kategori Risiko Unit (SOP 01)
                    </h6>
                    <a href="{{ route('units.index') }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;">
                        Kelola Unit
                    </a>
                </div>
                <div class="card-body p-0">
                    @foreach(['High','Moderate to High','Moderate','Low to Moderate','Low','Belum Dinilai'] as $cat)
                    @php
                        $count = $riskDist[$cat] ?? 0;
                        $pct   = $totalUnit > 0 ? round($count / $totalUnit * 100) : 0;
                        $badgeColor = match($cat) { 
                            'High'=>'bg-danger',
                            'Moderate to High'=>'bg-warning text-dark',
                            'Moderate'=>'bg-info text-dark',
                            'Low to Moderate'=>'bg-primary',
                            'Low'=>'bg-success',
                            default=>'bg-secondary' 
                        };
                    @endphp
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge {{ $badgeColor }}" style="width: 130px;">{{ $cat }}</span>
                        <div class="progress flex-grow-1" style="height: 8px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="small fw-bold text-dark text-end" style="width: 45px;">{{ $count }} Unit</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Audit Plan Terkini (SOP 01) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-calendar-check text-primary me-2"></i> Jadwal Audit Plan Terkini (SOP 01)
                    </h6>
                    <a href="{{ route('audit-plan.index') }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;">
                        Lihat Semua
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.8rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Auditor (RA)</th>
                                <th>Periode</th>
                                <th class="text-center">Status Approval</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jadwalAktif as $plan)
                            @php
                                $cls = match($plan->status_approval) {
                                    'approved'               => 'bg-success',
                                    'rejected'               => 'bg-danger',
                                    'waiting_kabag_approval',
                                    'waiting_kadiv_approval' => 'bg-warning text-dark',
                                    default                  => 'bg-secondary',
                                };
                                $lbl = match($plan->status_approval) {
                                    'approved'               => 'Approved',
                                    'rejected'               => 'Ditolak',
                                    'waiting_kabag_approval' => 'Menunggu Kabag',
                                    'waiting_kadiv_approval' => 'Menunggu Kadiv',
                                    default                  => $plan->status_approval,
                                };
                            @endphp
                            <tr>
                                <td class="fw-bold">{{ $plan->raUser?->name ?? 'RA' }}</td>
                                <td>{{ $plan->tahun_periode }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $cls }}">{{ $lbl }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('audit-plan.index') }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.72rem;">
                                        Buka <i class="bi bi-chevron-right"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">Belum ada audit plan aktif.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
}
.hover-bg-light:hover {
    background-color: #f8fafc;
}
.transition {
    transition: all 0.2s ease-in-out;
}
</style>
@endsection
