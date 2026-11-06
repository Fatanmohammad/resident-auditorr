@extends('layouts.app')
@section('title', 'Input Raw Metrics')

@section('content')
@php
    $backUrl = route('units.index');
    $autoCalculated = $autoCalculated ?? [];
    $sourceLabels = $sourceLabels ?? [];
    $v = fn($key) => old($key, $existing?->$key ?? ($autoCalculated[$key] ?? 0));

    // Bidang yang relevan per jenis unit:
    // - Payment Point : hanya Riwayat RA, Teller, Monitoring TL → sembunyikan CS/DPK, Kredit, & TI/ATM
    // - KCPLK         : semua kecuali Kredit → sembunyikan Kredit
    // - lainnya       : semua bidang
    $hideCsDpk = in_array($unit->unit_type, ['Payment Point']);
    $hideKredit = in_array($unit->unit_type, ['Payment Point', 'KCPLK']);
    $hideTi = in_array($unit->unit_type, ['Payment Point']);
@endphp

<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;">
    <div class="page-header-title">
        <div style="display:flex;align-items:center;gap:0.75rem;">
            <h1>Input Raw Metrics</h1>
            <span class="badge badge-primary" style="font-size:0.85rem;padding:0.35rem 0.65rem;">Periode {{ $period }}</span>
        </div>
        <p style="margin-top:0.25rem;"><strong>{{ $unit->unit_name }}</strong> ({{ $unit->unit_code }}) &bull; Tipe: <span class="badge badge-info">{{ $unit->unit_type }}</span></p>
    </div>
    <div style="display:flex;gap:0.5rem;align-items:center;">
        <form action="{{ route('raw-metrics.sync-unit', $unit) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyinkronkan seluruh nilai indikator secara otomatis dari data transaksi & KKA sistem?');">
            @csrf
            <input type="hidden" name="period" value="{{ $period }}">
            <button type="submit" class="btn btn-warning" title="Sinkronkan langsung dan simpan ke database">
                <i class="bi bi-lightning-charge-fill"></i> Sinkron Otomatis & Simpan
            </button>
        </form>
        <button type="button" class="btn btn-outline" id="btn-reload-auto" title="Terapkan angka rekomendasi sistem ke seluruh form di bawah">
            <i class="bi bi-arrow-repeat"></i> Muat Ulang Nilai Otomatis
        </button>
        <a href="{{ $backUrl }}" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1.25rem;">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-error" style="margin-bottom:1.25rem;">
    <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="alert alert-error" style="margin-bottom:1.25rem;">
    <i class="bi bi-exclamation-circle-fill"></i> <strong>Terjadi kesalahan input:</strong>
    <ul style="margin:0.5rem 0 0 1.25rem;">
        @foreach($errors->all() as $err)
        <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Banner Otomatisasi Sistem --}}
<div class="card" style="margin-bottom:1.25rem;border-left:4px solid #0284c7;background:linear-gradient(135deg, rgba(240,249,255,0.9), rgba(255,255,255,0.9));">
    <div class="card-body" style="padding:1rem 1.25rem;">
        <div style="display:flex;align-items:flex-start;gap:0.85rem;">
            <div style="font-size:1.5rem;color:#0284c7;line-height:1;"><i class="bi bi-cpu-fill"></i></div>
            <div style="flex:1;">
                <div style="font-weight:600;color:#0369a1;margin-bottom:0.25rem;">Otomatisasi Pengambilan Data Terintegrasi (27 Indikator)</div>
                <div style="font-size:0.84rem;color:#334155;line-height:1.45;">
                    Nilai setiap indikator di bawah telah <strong>dihitung secara otomatis</strong> oleh sistem dari data operasional terpusat:
                    <span class="badge" style="background:#e0f2fe;color:#0369a1;">KKA Onsite (SOP 04)</span>
                    <span class="badge" style="background:#e0f2fe;color:#0369a1;">KKA Offsite (SOP 02)</span>
                    <span class="badge" style="background:#e0f2fe;color:#0369a1;">Register Temuan Harian</span>
                    <span class="badge" style="background:#e0f2fe;color:#0369a1;">SOP 07 Register Tindak Lanjut</span>
                    <span class="badge" style="background:#e0f2fe;color:#0369a1;">SOP 09 Fraud Triage & Indikasi</span>
                    <br>
                    Anda dapat langsung menyimpan atau melakukan penyesuaian (override) nilai secara manual jika terdapat data audit tambahan.
                </div>
            </div>
        </div>
    </div>
</div>

<form id="raw-metrics-form" action="{{ route('raw-metrics.store', $unit) }}" method="POST" novalidate>
@csrf
<input type="hidden" name="period" value="{{ $period }}">

<div class="grid grid-cols-2" style="gap:1.25rem;">

    {{-- Bidang A --}}
    <div class="card">
        <div class="card-header" style="border-bottom:1px solid #e2e8f0;padding:0.75rem 1.25rem;">
            <div class="card-title" style="font-size:0.95rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="bi bi-clock-history" style="color:var(--bs-blue);"></i> Bidang A — Riwayat Pemeriksaan RA
            </div>
        </div>
        <div class="card-body">
            @foreach([
                ['prior_onsite_findings','Temuan Onsite Tahun Lalu','integer'],
                ['significant_findings','Temuan Signifikan','integer'],
                ['repeat_findings','Temuan Berulang','integer'],
                ['offsite_deviation','Penyimpangan Pada Offsite','integer'],
                ['offsite_deviation_significant','Penyimpangan Offsite Signifikan','integer'],
                ['offsite_deviation_repeat','Penyimpangan Offsite Berulang','integer'],
                ['months_since_last_onsite','Lama Sejak Onsite (Bulan)','integer'],
            ] as [$name,$label,$type])
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-weight:600;font-size:0.85rem;margin-bottom:0.25rem;">{{ $label }}</label>
                <input type="number" name="{{ $name }}" id="input-{{ $name }}" class="form-input" value="{{ $v($name) }}" min="0" step="{{ $type==='decimal'?'0.0001':'1' }}" required>
                <div style="margin-top:0.35rem;display:flex;align-items:center;justify-content:space-between;font-size:0.73rem;color:var(--text-muted);">
                    <span><i class="bi bi-magic" style="color:#0284c7;"></i> Sistem: <strong class="auto-ref" data-key="{{ $name }}">{{ $autoCalculated[$name] ?? 0 }}</strong></span>
                    <span class="badge" style="background:rgba(2,132,199,0.08);color:#0369a1;border:1px solid rgba(2,132,199,0.2);font-size:0.68rem;font-weight:normal;">{{ $sourceLabels[$name] ?? 'Sistem RA' }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Bidang B --}}
    <div class="card">
        <div class="card-header" style="border-bottom:1px solid #e2e8f0;padding:0.75rem 1.25rem;">
            <div class="card-title" style="font-size:0.95rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="bi bi-cash-coin" style="color:var(--bs-blue);"></i> Bidang B — Kas/Teller & Operasional
            </div>
        </div>
        <div class="card-body">
            @foreach([
                ['reversal_correction_txn','Transaksi Reversal/Koreksi'],
                ['cash_discrepancy','Selisih Kas'],
                ['unusual_cost_journal','Biaya/Jurnal Tidak Lazim'],
                ['large_risky_cash_txn','Transaksi Tunai Besar Berisiko'],
            ] as [$name,$label])
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-weight:600;font-size:0.85rem;margin-bottom:0.25rem;">{{ $label }}</label>
                <input type="number" name="{{ $name }}" id="input-{{ $name }}" class="form-input" value="{{ $v($name) }}" min="0" required>
                <div style="margin-top:0.35rem;display:flex;align-items:center;justify-content:space-between;font-size:0.73rem;color:var(--text-muted);">
                    <span><i class="bi bi-magic" style="color:#0284c7;"></i> Sistem: <strong class="auto-ref" data-key="{{ $name }}">{{ $autoCalculated[$name] ?? 0 }}</strong></span>
                    <span class="badge" style="background:rgba(2,132,199,0.08);color:#0369a1;border:1px solid rgba(2,132,199,0.2);font-size:0.68rem;font-weight:normal;">{{ $sourceLabels[$name] ?? 'Sistem RA' }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @if(!$hideCsDpk)
    {{-- Bidang C --}}
    <div class="card">
        <div class="card-header" style="border-bottom:1px solid #e2e8f0;padding:0.75rem 1.25rem;">
            <div class="card-title" style="font-size:0.95rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="bi bi-people" style="color:var(--bs-blue);"></i> Bidang C — CS/DPK/APU-PPT
            </div>
        </div>
        <div class="card-body">
            @foreach([
                ['dpk_anomaly','Anomali Pengelolaan DPK'],
                ['overdue_complaints','Pengaduan Nasabah Overdue'],
                ['incomplete_cdd_edd','Pengkinian Data/CDD-EDD Belum Selesai'],
            ] as [$name,$label])
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-weight:600;font-size:0.85rem;margin-bottom:0.25rem;">{{ $label }}</label>
                <input type="number" name="{{ $name }}" id="input-{{ $name }}" class="form-input" value="{{ $v($name) }}" min="0" required>
                <div style="margin-top:0.35rem;display:flex;align-items:center;justify-content:space-between;font-size:0.73rem;color:var(--text-muted);">
                    <span><i class="bi bi-magic" style="color:#0284c7;"></i> Sistem: <strong class="auto-ref" data-key="{{ $name }}">{{ $autoCalculated[$name] ?? 0 }}</strong></span>
                    <span class="badge" style="background:rgba(2,132,199,0.08);color:#0369a1;border:1px solid rgba(2,132,199,0.2);font-size:0.68rem;font-weight:normal;">{{ $sourceLabels[$name] ?? 'Sistem RA' }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if(!$hideKredit)
    {{-- Bidang D --}}
    <div class="card">
        <div class="card-header" style="border-bottom:1px solid #e2e8f0;padding:0.75rem 1.25rem;">
            <div class="card-title" style="font-size:0.95rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="bi bi-bank" style="color:var(--bs-blue);"></i> Bidang D — Kredit
            </div>
        </div>
        <div class="card-body">
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-weight:600;font-size:0.85rem;margin-bottom:0.25rem;">Jumlah Debitur Kol 3-5</label>
                <input type="number" name="debtors_col_3_5" id="input-debtors_col_3_5" class="form-input" value="{{ $v('debtors_col_3_5') }}" min="0" required>
                <div style="margin-top:0.35rem;display:flex;align-items:center;justify-content:space-between;font-size:0.73rem;color:var(--text-muted);">
                    <span><i class="bi bi-magic" style="color:#0284c7;"></i> Sistem: <strong class="auto-ref" data-key="debtors_col_3_5">{{ $autoCalculated['debtors_col_3_5'] ?? 0 }}</strong></span>
                    <span class="badge" style="background:rgba(2,132,199,0.08);color:#0369a1;border:1px solid rgba(2,132,199,0.2);font-size:0.68rem;font-weight:normal;">{{ $sourceLabels['debtors_col_3_5'] ?? 'KKA Kredit' }}</span>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-weight:600;font-size:0.85rem;margin-bottom:0.25rem;">Rasio NPL <span style="font-size:0.72rem;color:var(--text-muted);font-weight:normal;">(0.00 - 1.00, contoh: 0.05 = 5%)</span></label>
                <input type="number" name="npl_ratio" id="input-npl_ratio" class="form-input" value="{{ $v('npl_ratio') }}" min="0" max="1" step="0.0001" required>
                <div style="margin-top:0.35rem;display:flex;align-items:center;justify-content:space-between;font-size:0.73rem;color:var(--text-muted);">
                    <span><i class="bi bi-magic" style="color:#0284c7;"></i> Sistem: <strong class="auto-ref" data-key="npl_ratio">{{ $autoCalculated['npl_ratio'] ?? 0 }}</strong></span>
                    <span class="badge" style="background:rgba(2,132,199,0.08);color:#0369a1;border:1px solid rgba(2,132,199,0.2);font-size:0.68rem;font-weight:normal;">{{ $sourceLabels['npl_ratio'] ?? 'KKA Kredit' }}</span>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-weight:600;font-size:0.85rem;margin-bottom:0.25rem;">Penyimpangan/Deviasi Kredit</label>
                <input type="number" name="credit_deviation" id="input-credit_deviation" class="form-input" value="{{ $v('credit_deviation') }}" min="0" required>
                <div style="margin-top:0.35rem;display:flex;align-items:center;justify-content:space-between;font-size:0.73rem;color:var(--text-muted);">
                    <span><i class="bi bi-magic" style="color:#0284c7;"></i> Sistem: <strong class="auto-ref" data-key="credit_deviation">{{ $autoCalculated['credit_deviation'] ?? 0 }}</strong></span>
                    <span class="badge" style="background:rgba(2,132,199,0.08);color:#0369a1;border:1px solid rgba(2,132,199,0.2);font-size:0.68rem;font-weight:normal;">{{ $sourceLabels['credit_deviation'] ?? 'KKA Kredit' }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(!$hideTi)
    {{-- Bidang E --}}
    <div class="card">
        <div class="card-header" style="border-bottom:1px solid #e2e8f0;padding:0.75rem 1.25rem;">
            <div class="card-title" style="font-size:0.95rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="bi bi-cpu" style="color:var(--bs-blue);"></i> Bidang E — TI/ATM
            </div>
        </div>
        <div class="card-body">
            @foreach([
                ['atm_dispute','Selisih/Dispute ATM','integer'],
                ['atm_downtime_hours','Downtime ATM (Total Jam)','decimal'],
                ['critical_ti_incident','Insiden TI Kritikal','integer'],
                ['unusual_user_reset','Reset/Buka Blokir User Tidak Lazim','integer'],
            ] as [$name,$label,$type])
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-weight:600;font-size:0.85rem;margin-bottom:0.25rem;">{{ $label }}</label>
                <input type="number" name="{{ $name }}" id="input-{{ $name }}" class="form-input" value="{{ $v($name) }}" min="0" step="{{ $type==='decimal'?'0.01':'1' }}" required>
                <div style="margin-top:0.35rem;display:flex;align-items:center;justify-content:space-between;font-size:0.73rem;color:var(--text-muted);">
                    <span><i class="bi bi-magic" style="color:#0284c7;"></i> Sistem: <strong class="auto-ref" data-key="{{ $name }}">{{ $autoCalculated[$name] ?? 0 }}</strong></span>
                    <span class="badge" style="background:rgba(2,132,199,0.08);color:#0369a1;border:1px solid rgba(2,132,199,0.2);font-size:0.68rem;font-weight:normal;">{{ $sourceLabels[$name] ?? 'Sistem RA' }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Bidang F --}}
    <div class="card">
        <div class="card-header" style="border-bottom:1px solid #e2e8f0;padding:0.75rem 1.25rem;">
            <div class="card-title" style="font-size:0.95rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="bi bi-list-check" style="color:var(--bs-blue);"></i> Bidang F — Monitoring Tindak Lanjut (SOP 07)
            </div>
        </div>
        <div class="card-body">
            @foreach([
                ['ra_onsite_tl_overdue','Temuan RA Onsite Overdue'],
                ['ra_offsite_tl_overdue','Temuan RA Offsite Overdue'],
                ['skai_tl_overdue','Temuan SKAI Overdue'],
                ['regulator_tl_overdue','Temuan Regulator Overdue'],
                ['kap_tl_overdue','Temuan KAP Overdue'],
            ] as [$name,$label])
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-weight:600;font-size:0.85rem;margin-bottom:0.25rem;">{{ $label }}</label>
                <input type="number" name="{{ $name }}" id="input-{{ $name }}" class="form-input" value="{{ $v($name) }}" min="0" required>
                <div style="margin-top:0.35rem;display:flex;align-items:center;justify-content:space-between;font-size:0.73rem;color:var(--text-muted);">
                    <span><i class="bi bi-magic" style="color:#0284c7;"></i> Sistem: <strong class="auto-ref" data-key="{{ $name }}">{{ $autoCalculated[$name] ?? 0 }}</strong></span>
                    <span class="badge" style="background:rgba(2,132,199,0.08);color:#0369a1;border:1px solid rgba(2,132,199,0.2);font-size:0.68rem;font-weight:normal;">{{ $sourceLabels[$name] ?? 'SOP 07 Register TL' }}</span>
                </div>
            </div>
            @endforeach
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-weight:600;font-size:0.85rem;margin-bottom:0.25rem;">Rata-Rata Hari Respons TL</label>
                <input type="number" name="avg_response_days" id="input-avg_response_days" class="form-input" value="{{ $v('avg_response_days') }}" min="0" step="0.01" required>
                <div style="margin-top:0.35rem;display:flex;align-items:center;justify-content:space-between;font-size:0.73rem;color:var(--text-muted);">
                    <span><i class="bi bi-magic" style="color:#0284c7;"></i> Sistem: <strong class="auto-ref" data-key="avg_response_days">{{ $autoCalculated['avg_response_days'] ?? 0 }}</strong></span>
                    <span class="badge" style="background:rgba(2,132,199,0.08);color:#0369a1;border:1px solid rgba(2,132,199,0.2);font-size:0.68rem;font-weight:normal;">{{ $sourceLabels['avg_response_days'] ?? 'SOP 07 Register TL' }}</span>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-weight:600;font-size:0.85rem;margin-bottom:0.25rem;">Kualitas Respons TL <span style="font-size:0.72rem;color:var(--text-muted);font-weight:normal;">(0-4 poin checklist)</span></label>
                <input type="number" name="tl_response_quality" id="input-tl_response_quality" class="form-input" value="{{ $v('tl_response_quality') }}" min="0" max="4" required>
                <div style="margin-top:0.35rem;display:flex;align-items:center;justify-content:space-between;font-size:0.73rem;color:var(--text-muted);">
                    <span><i class="bi bi-magic" style="color:#0284c7;"></i> Sistem: <strong class="auto-ref" data-key="tl_response_quality">{{ $autoCalculated['tl_response_quality'] ?? 0 }}</strong></span>
                    <span class="badge" style="background:rgba(2,132,199,0.08);color:#0369a1;border:1px solid rgba(2,132,199,0.2);font-size:0.68rem;font-weight:normal;">{{ $sourceLabels['tl_response_quality'] ?? 'SOP 07 Register TL' }}</span>
                </div>
            </div>
        </div>
    </div>

</div>

<div style="margin-top:1.5rem;display:flex;gap:0.75rem;align-items:center;">
    <button type="submit" class="btn btn-primary" style="padding:0.6rem 1.5rem;font-weight:600;">
        <i class="bi bi-check-lg"></i> Simpan & Hitung Skor
    </button>
    <a href="{{ $backUrl }}" class="btn btn-outline">Batal</a>
</div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const autoValues = @json($autoCalculated);
    const reloadBtn = document.getElementById('btn-reload-auto');

    if (reloadBtn) {
        reloadBtn.addEventListener('click', function () {
            if (confirm('Apakah Anda ingin memuat ulang seluruh input form sesuai dengan nilai otomatis yang dihitung sistem saat ini?')) {
                Object.keys(autoValues).forEach(function (key) {
                    const input = document.getElementById('input-' + key);
                    if (input) {
                        input.value = autoValues[key];
                        input.style.backgroundColor = '#ecfdf5';
                        setTimeout(function () {
                            input.style.backgroundColor = '';
                        }, 1200);
                    }
                });
            }
        });
    }
});
</script>
@endsection
