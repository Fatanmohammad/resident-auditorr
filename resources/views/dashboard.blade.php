@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

<div style="margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.3rem; font-weight: 700; color: var(--bs-blue-dark);">
        Selamat datang, {{ explode(' ', auth()->user()->name)[0] }}
    </h1>
    <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 0.2rem;">
        {{ now()->isoFormat('dddd, D MMMM Y') }} &mdash; Sistem Resident Auditor PT Bank Sulteng
    </p>
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

{{-- Stat Cards --}}
<div class="grid grid-cols-4" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-diagram-3"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Unit Aktif</div>
            <div class="stat-value">{{ $totalUnit }}</div>
            <div class="stat-sub">unit pengawasan</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-people"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total RA</div>
            <div class="stat-value">{{ $totalRa }}</div>
            <div class="stat-sub">resident auditor</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-calendar3"></i></div>
        <div class="stat-info">
            <div class="stat-label">Audit Plan</div>
            <div class="stat-value">{{ $totalJadwal }}</div>
            <div class="stat-sub">{{ $jadwalSelesai }} approved</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-clipboard2-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">Final Audit Plan</div>
            <div class="stat-value">{{ $totalFinalPlan }}</div>
            <div class="stat-sub">SOP 01 output</div>
        </div>
    </div>
</div>

{{-- Distrribusi Risiko & Frekuensi (§5) --}}
<div class="grid grid-cols-2" style="margin-bottom:1.5rem;">
    <div class="card">
        <div class="card-header"><div class="card-title">Distribusi Kategori Risiko</div></div>
        <div class="card-body">
            @foreach(['High','Moderate to High','Moderate','Low to Moderate','Low','Belum Dinilai'] as $cat)
            @php
                $count = $riskDist[$cat] ?? 0;
                $pct   = $totalUnit > 0 ? round($count / $totalUnit * 100) : 0;
                $cls   = match($cat) { 'High'=>'badge-danger','Moderate to High'=>'badge-warning','Moderate'=>'badge-info','Low to Moderate'=>'badge-purple','Belum Dinilai'=>'badge-gray',default=>'badge-gray' };
            @endphp
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.6rem;">
                <span class="badge {{ $cls }}" style="width:130px;justify-content:center;">{{ $cat }}</span>
                <div style="flex:1;background:#f3f4f6;border-radius:9999px;height:8px;">
                    <div style="width:{{ $pct }}%;background:var(--bs-blue);height:8px;border-radius:9999px;"></div>
                </div>
                <span style="font-size:0.8rem;font-weight:600;color:var(--text-main);width:30px;text-align:right;">{{ $count }}</span>
            </div>
            @endforeach
        </div>
    </div>
    <div>
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header"><div class="card-title">Distribusi Jenis Unit</div></div>
            <div class="card-body">
                @foreach($typeDist as $type => $count)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.4rem 0;border-bottom:1px solid var(--border-color);font-size:0.82rem;">
                    <span>{{ $type }}</span>
                    <span class="badge badge-info">{{ $count }} unit</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="card">
            <div class="card-header"><div class="card-title">Distribusi Frekuensi Onsite</div></div>
            <div class="card-body">
                @foreach($freqDist as $freq => $count)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.4rem 0;border-bottom:1px solid var(--border-color);font-size:0.82rem;">
                    <span>{{ $freq }}</span>
                    <span class="badge badge-info">{{ $count }} unit</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Audit Plan terkini + shortcut --}}
<div class="grid grid-cols-2">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Audit Plan Terkini</div>
            <a href="{{ route('audit-plan.index') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>RA</th><th>Periode</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($jadwalAktif as $plan)
                    <tr>
                        <td><strong>{{ $plan->raUser?->name ?? '-' }}</strong></td>
                        <td>{{ $plan->tahun_periode }}</td>
                        <td>
                            @php
                                $cls = match($plan->status_approval) {
                                    'approved'               => 'badge-success',
                                    'rejected'               => 'badge-danger',
                                    'waiting_kabag_approval',
                                    'waiting_kadiv_approval' => 'badge-warning',
                                    default                  => 'badge-gray',
                                };
                                $lbl = match($plan->status_approval) {
                                    'approved'               => 'Approved',
                                    'rejected'               => 'Ditolak',
                                    'waiting_kabag_approval' => 'Menunggu Kabag',
                                    'waiting_kadiv_approval' => 'Menunggu Kadiv',
                                    default                  => $plan->status_approval,
                                };
                            @endphp
                            <span class="badge {{ $cls }}">{{ $lbl }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3"><div class="empty-state"><i class="bi bi-calendar-x"></i><p>Belum ada data.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">Alur SOP 01</div></div>
        <div class="card-body">
            @php
                $steps = [
                    ['num'=>1,'label'=>'Master Unit','desc'=>'Data universe unit pengawasan','route'=>'units.index'],
                    ['num'=>2,'label'=>'Input Risiko','desc'=>'Raw metrics & risk scoring','route'=>'units.index'],
                    ['num'=>3,'label'=>'Penugasan RA','desc'=>'Coverage & assignment RA','route'=>'units.index'],
                    ['num'=>4,'label'=>'Jadwal Kunjungan','desc'=>'Frekuensi onsite & kapasitas','route'=>'scheduling.index'],
                    ['num'=>5,'label'=>'Final Audit Plan','desc'=>'Output akhir SOP 01','route'=>'final-audit-plan.index'],
                ];
            @endphp
            @foreach($steps as $i => $step)
            <a href="{{ route($step['route']) }}" style="display:flex; align-items:center; gap:1rem; padding:0.65rem 0; text-decoration:none; {{ $i < count($steps)-1 ? 'border-bottom:1px solid var(--border-color);' : '' }}">
                <div style="width:28px; height:28px; border-radius:50%; background:var(--bs-blue); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; flex-shrink:0;">{{ $step['num'] }}</div>
                <div style="flex:1;">
                    <div style="font-size:0.83rem; font-weight:600; color:var(--bs-blue-dark);">{{ $step['label'] }}</div>
                    <div style="font-size:0.72rem; color:var(--text-muted);">{{ $step['desc'] }}</div>
                </div>
                <i class="bi bi-chevron-right" style="color:var(--text-muted); font-size:0.72rem;"></i>
            </a>
            @endforeach
        </div>
    </div>
</div>

@endsection
