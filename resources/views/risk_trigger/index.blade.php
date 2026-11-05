@extends('layouts.app')
@section('title', 'Risk Trigger (SOP 06) - Alarm Center')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="fs-4 fw-bold text-dark mb-1">
            <i class="bi bi-shield-exclamation text-danger me-2"></i>Alarm Risk Trigger Cabang (&gt; 10 Temuan)
        </h1>
        <p class="text-muted small mb-0">
            Sistem Pemantauan Otomatis Akumulasi Temuan Pemeriksaan Cabang (SOP 06) &bull; Khusus Notifikasi Admin
        </p>
    </div>
    <form action="{{ route('risk-trigger.sync') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
            <i class="bi bi-arrow-repeat me-1"></i> Sinkronkan Data Temuan
        </button>
    </form>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show small py-2 px-3 mb-3 border-success-subtle shadow-sm" role="alert">
    <i class="bi bi-check-circle-fill me-1 text-success"></i> {{ session('success') }}
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- KPI RINGKAS DI ATAS --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-danger border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Cabang Terkena Trigger (&gt; 10)</div>
                    <div class="fs-2 fw-bold text-danger mt-1">{{ $kpi['total_cabang'] ?? count($alarms) }}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Akumulasi temuan menembus batas batas &gt; 10</div>
                </div>
                <div class="p-3 bg-danger-subtle text-danger rounded-circle">
                    <i class="bi bi-exclamation-octagon-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-warning border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Belum Diinfokan / Baru</div>
                    <div class="fs-2 fw-bold text-warning-emphasis mt-1">{{ $kpi['belum_diinfokan'] ?? 0 }}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Perlu segera diinformasikan ke manajemen / Pak Adam</div>
                </div>
                <div class="p-3 bg-warning-subtle text-warning-emphasis rounded-circle">
                    <i class="bi bi-bell-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-success border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Sudah Diinfokan</div>
                    <div class="fs-2 fw-bold text-success mt-1">{{ $kpi['sudah_diinfokan'] ?? 0 }}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Telah diteruskan untuk tindak lanjut pendalaman</div>
                </div>
                <div class="p-3 bg-success-subtle text-success rounded-circle">
                    <i class="bi bi-check2-circle fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TABEL DAFTAR ALARM CABANG (> 10 TEMUAN) --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="card-header bg-white py-3 px-3.5 d-flex justify-content-between align-items-center border-bottom">
        <div class="fw-bold text-dark">
            <i class="bi bi-table me-1.5 text-primary"></i> Daftar Alarm Cabang (&gt; 10 Temuan)
        </div>
        <span class="badge bg-danger rounded-pill px-2.5 py-1">
            {{ count($alarms) }} Cabang Terdeteksi
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
            <thead class="table-light text-muted fw-semibold">
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="min-width: 220px;">Nama &amp; Kode Cabang</th>
                    <th class="text-center" style="min-width: 160px;">Total Akumulasi Temuan</th>
                    <th style="min-width: 260px;">Sebaran Area Temuan Terbanyak</th>
                    <th class="text-center" style="min-width: 140px;">Waktu Terdeteksi</th>
                    <th class="text-center" style="min-width: 160px;">Status Notifikasi</th>
                    <th class="text-center" style="min-width: 220px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alarms as $index => $alarm)
                @php
                    $modalId = 'modalDetail_' . (\Illuminate\Support\Str::slug($alarm['kode_unit']) ?: md5($alarm['kode_unit']));
                    $isFollowedUp = !empty($alarm['is_followed_up']);
                @endphp
                <tr>
                    <td class="text-center fw-semibold text-muted">{{ $loop->iteration }}</td>
                    <td>
                        <div class="fw-bold text-dark">{{ $alarm['nama_unit'] }}</div>
                        <div class="text-muted small" style="font-size: 0.78rem;">Kode Unit: <span class="badge bg-light text-dark border">{{ $alarm['kode_unit'] }}</span></div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-danger fs-6 px-3 py-1.5 shadow-2xs fw-bold">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $alarm['count'] }} Temuan
                        </span>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $alarm['sebaran_area'] ?? $alarm['area'] }}</div>
                        <div class="text-muted small mt-0.5" style="font-size: 0.75rem;">
                            Daily Review: <strong>{{ $alarm['dr_count'] }}</strong> &bull; Offsite: <strong>{{ $alarm['off_count'] }}</strong> &bull; Onsite: <strong>{{ $alarm['on_count'] }}</strong>
                        </div>
                    </td>
                    <td class="text-center text-muted small">
                        <i class="bi bi-clock me-1"></i>{{ $alarm['waktu_terdeteksi'] }}
                    </td>
                    <td class="text-center">
                        @if(!$isFollowedUp)
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 fw-bold rounded-pill">
                                <i class="bi bi-circle-fill text-danger me-1"></i> Perlu Diinfokan
                            </span>
                        @else
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 fw-bold rounded-pill">
                                <i class="bi bi-check-circle-fill text-success me-1"></i> Sudah Diinfokan
                            </span>
                            @if(!empty($alarm['waktu_tindak_lanjut']))
                                <div class="text-muted mt-1" style="font-size: 0.72rem;">{{ $alarm['waktu_tindak_lanjut'] }}</div>
                            @endif
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-1.5 align-items-center">
                            {{-- Tombol Lihat Rincian Temuan --}}
                            <button type="button" class="btn btn-sm btn-outline-primary px-2.5 py-1 rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                                <i class="bi bi-eye me-1"></i> Lihat Rincian
                            </button>

                            {{-- Tombol Tandai Sudah Diinfokan --}}
                            @if(!$isFollowedUp)
                                <form action="{{ route('risk-trigger.inform', $alarm['kode_unit']) }}" method="POST" class="d-inline" onsubmit="return confirm('Tandai alarm cabang {{ $alarm['nama_unit'] }} sebagai Sudah Diinfokan ke manajemen/Pak Adam?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success px-2.5 py-1 rounded-pill fw-bold">
                                        <i class="bi bi-check2 me-1"></i> Tandai Diinfokan
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary px-2.5 py-1 rounded-pill" disabled title="Telah diinformasikan oleh {{ $alarm['ditindaklanjuti_oleh'] ?? 'Admin' }}">
                                    <i class="bi bi-check2-all me-1 text-success"></i> Terinfokan
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>

                {{-- MODAL LIHAT RINCIAN TEMUAN (READ-ONLY) --}}
                <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-dark text-white py-2.5 px-3">
                                <h6 class="modal-title fs-6 fw-bold" id="{{ $modalId }}Label">
                                    <i class="bi bi-journal-text me-1.5 text-danger"></i> Rincian Temuan Cabang: {{ $alarm['nama_unit'] }} ({{ $alarm['kode_unit'] }})
                                </h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-3">
                                <div class="p-3 bg-light rounded-3 border mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <span class="badge bg-danger fs-6 me-2">{{ $alarm['count'] }} Total Temuan</span>
                                        <span class="text-muted small">Ambang batas (&gt; 10 temuan) terlampaui. Data ini siap dilaporkan ke Pak Adam untuk pendalaman audit lebih lanjut.</span>
                                    </div>
                                    <div class="text-end small">
                                        <div><strong>Sebaran:</strong> {{ $alarm['sebaran_area'] ?? $alarm['area'] }}</div>
                                        <div class="text-muted">Terdeteksi: {{ $alarm['waktu_terdeteksi'] }}</div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped align-middle" style="font-size: 0.82rem;">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th class="text-center" style="width: 40px;">No</th>
                                                <th style="width: 100px;">Tanggal</th>
                                                <th style="width: 120px;">Sumber Modul</th>
                                                <th style="width: 140px;">Area Risiko</th>
                                                <th>Uraian Temuan / Bukti Awal</th>
                                                <th class="text-end" style="width: 130px;">Eksposur Nominal</th>
                                                <th class="text-center" style="width: 100px;">Level Risiko</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($alarm['findings'] as $fIdx => $finding)
                                            <tr>
                                                <td class="text-center text-muted">{{ $fIdx + 1 }}</td>
                                                <td>{{ $finding->tanggal_trigger ? \Carbon\Carbon::parse($finding->tanggal_trigger)->format('d/m/Y') : '-' }}</td>
                                                <td>
                                                    <span class="badge bg-secondary-subtle text-secondary border">{{ $finding->sumber ?: 'Pemeriksaan' }}</span>
                                                </td>
                                                <td class="fw-semibold text-primary">{{ $finding->area }}</td>
                                                <td>
                                                    <div class="fw-semibold text-dark">{{ $finding->uraian_trigger ?: $finding->objek_key }}</div>
                                                    @if($finding->bukti_awal)
                                                        <div class="text-muted small mt-0.5"><i class="bi bi-paperclip me-0.5"></i>{{ $finding->bukti_awal }}</div>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if($finding->eksposur_nominal > 0)
                                                        <span class="fw-bold">Rp {{ number_format($finding->eksposur_nominal, 0, ',', '.') }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $rLvl = strtoupper($finding->risk_level_reassessment ?: $finding->risk_level_sumber ?: 'SEDANG');
                                                        $badgeColor = match(true) {
                                                            str_contains($rLvl, 'TINGGI') || str_contains($rLvl, 'HIGH') || str_contains($rLvl, 'KRITIS') => 'bg-danger text-white',
                                                            str_contains($rLvl, 'SEDANG') || str_contains($rLvl, 'MODERATE') => 'bg-warning text-dark',
                                                            default => 'bg-info text-dark',
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $badgeColor }}">{{ $rLvl }}</span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-3">Tidak ada data temuan rinci.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer py-2 px-3 bg-light">
                                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                                @if(!$isFollowedUp)
                                    <form action="{{ route('risk-trigger.inform', $alarm['kode_unit']) }}" method="POST" class="d-inline" onsubmit="return confirm('Tandai alarm cabang {{ $alarm['nama_unit'] }} sebagai Sudah Diinfokan?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                                            <i class="bi bi-check2 me-1"></i> Tandai Sudah Diinfokan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-shield-check text-success fs-1 d-block mb-2"></i>
                        <span class="fw-bold fs-6">Tidak Ada Cabang Terkena Trigger (&gt; 10 Temuan)</span>
                        <div class="small mt-1">Seluruh cabang berada dalam batas toleransi aman. Alarm akan menyala otomatis jika temuan cabang melebihi 10 temuan.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
