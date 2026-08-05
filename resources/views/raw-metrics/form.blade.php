@extends('layouts.app')
@section('title', 'Input Raw Metrics')

@section('content')
@php
    $backUrl = (auth()->user()->role === 'ra')
        ? route('raw-metrics.index')
        : route('units.show', $unit);
@endphp
<div class="page-header">
    <div class="page-header-title">
        <h1>Input Raw Metrics</h1>
        <p>{{ $unit->unit_name }} — {{ $unit->unit_code }}</p>
    </div>
    <a href="{{ $backUrl }}" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<form action="{{ route('raw-metrics.store', $unit) }}" method="POST">
@csrf
<input type="hidden" name="period" value="{{ $period }}">

@php
$v = fn($key) => old($key, $existing?->$key ?? 0);

// Bidang yang relevan per jenis unit:
// - Payment Point : hanya Riwayat RA, Teller, Monitoring TL → sembunyikan CS/DPK, Kredit, & TI/ATM
// - KCPLK         : semua kecuali Kredit → sembunyikan Kredit
// - lainnya       : semua bidang
$hideCsDpk = in_array($unit->unit_type, ['Payment Point']);
$hideKredit = in_array($unit->unit_type, ['Payment Point', 'KCPLK']);
$hideTi = in_array($unit->unit_type, ['Payment Point']);
@endphp

<div class="grid grid-cols-2" style="gap:1.25rem;">

    {{-- Bidang A --}}
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="bi bi-clock-history" style="color:var(--bs-blue);"></i> Bidang A — Riwayat Pemeriksaan RA</div></div>
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
            <div class="form-group">
                <label class="form-label">{{ $label }}</label>
                <input type="number" name="{{ $name }}" class="form-input" value="{{ $v($name) }}" min="0" step="{{ $type==='decimal'?'0.0001':'1' }}" required>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Bidang B --}}
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="bi bi-cash-coin" style="color:var(--bs-blue);"></i> Bidang B — Kas/Teller & Operasional</div></div>
        <div class="card-body">
            @foreach([
                ['reversal_correction_txn','Transaksi Reversal/Koreksi'],
                ['cash_discrepancy','Selisih Kas'],
                ['unusual_cost_journal','Biaya/Jurnal Tidak Lazim'],
                ['large_risky_cash_txn','Transaksi Tunai Besar Berisiko'],
            ] as [$name,$label])
            <div class="form-group">
                <label class="form-label">{{ $label }}</label>
                <input type="number" name="{{ $name }}" class="form-input" value="{{ $v($name) }}" min="0" required>
            </div>
            @endforeach
        </div>
    </div>

@if(!$hideCsDpk)
    {{-- Bidang C --}}
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="bi bi-people" style="color:var(--bs-blue);"></i> Bidang C — CS/DPK/APU-PPT</div></div>
        <div class="card-body">
            @foreach([
                ['dpk_anomaly','Anomali Pengelolaan DPK'],
                ['overdue_complaints','Pengaduan Nasabah Overdue'],
                ['incomplete_cdd_edd','Pengkinian Data/CDD-EDD Belum Selesai'],
            ] as [$name,$label])
            <div class="form-group">
                <label class="form-label">{{ $label }}</label>
                <input type="number" name="{{ $name }}" class="form-input" value="{{ $v($name) }}" min="0" required>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if(!$hideKredit)
    {{-- Bidang D --}}
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="bi bi-bank" style="color:var(--bs-blue);"></i> Bidang D — Kredit</div></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Jumlah Debitur Kol 3-5</label>
                <input type="number" name="debtors_col_3_5" class="form-input" value="{{ $v('debtors_col_3_5') }}" min="0" required>
            </div>
            <div class="form-group">
                <label class="form-label">Rasio NPL <span style="font-size:0.72rem;color:var(--text-muted);">(0.00 - 1.00, contoh: 0.05 = 5%)</span></label>
                <input type="number" name="npl_ratio" class="form-input" value="{{ $v('npl_ratio') }}" min="0" max="1" step="0.0001" required>
            </div>
            <div class="form-group">
                <label class="form-label">Penyimpangan/Deviasi Kredit</label>
                <input type="number" name="credit_deviation" class="form-input" value="{{ $v('credit_deviation') }}" min="0" required>
            </div>
        </div>
    </div>
    @endif

@if(!$hideTi)
    {{-- Bidang E --}}
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="bi bi-cpu" style="color:var(--bs-blue);"></i> Bidang E — TI/ATM</div></div>
        <div class="card-body">
            @foreach([
                ['atm_dispute','Selisih/Dispute ATM','integer'],
                ['atm_downtime_hours','Downtime ATM (Total Jam)','decimal'],
                ['critical_ti_incident','Insiden TI Kritikal','integer'],
                ['unusual_user_reset','Reset/Buka Blokir User Tidak Lazim','integer'],
            ] as [$name,$label,$type])
            <div class="form-group">
                <label class="form-label">{{ $label }}</label>
                <input type="number" name="{{ $name }}" class="form-input" value="{{ $v($name) }}" min="0" step="{{ $type==='decimal'?'0.01':'1' }}" required>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Bidang F --}}
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="bi bi-list-check" style="color:var(--bs-blue);"></i> Bidang F — Monitoring Tindak Lanjut</div></div>
        <div class="card-body">
            @foreach([
                ['ra_onsite_tl_overdue','Temuan RA Onsite Overdue'],
                ['ra_offsite_tl_overdue','Temuan RA Offsite Overdue'],
                ['skai_tl_overdue','Temuan SKAI Overdue'],
                ['regulator_tl_overdue','Temuan Regulator Overdue'],
                ['kap_tl_overdue','Temuan KAP Overdue'],
            ] as [$name,$label])
            <div class="form-group">
                <label class="form-label">{{ $label }}</label>
                <input type="number" name="{{ $name }}" class="form-input" value="{{ $v($name) }}" min="0" required>
            </div>
            @endforeach
            <div class="form-group">
                <label class="form-label">Rata-Rata Hari Respons TL</label>
                <input type="number" name="avg_response_days" class="form-input" value="{{ $v('avg_response_days') }}" min="0" step="0.01" required>
            </div>
            <div class="form-group">
                <label class="form-label">Kualitas Respons TL <span style="font-size:0.72rem;color:var(--text-muted);">(0-4 poin checklist)</span></label>
                <input type="number" name="tl_response_quality" class="form-input" value="{{ $v('tl_response_quality') }}" min="0" max="4" required>
            </div>
        </div>
    </div>

</div>

<div style="margin-top:1.25rem;display:flex;gap:0.75rem;">
<button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan & Hitung Skor</button>
    <a href="{{ $backUrl }}" class="btn btn-outline">Batal</a>
</div>
</form>
@endsection
