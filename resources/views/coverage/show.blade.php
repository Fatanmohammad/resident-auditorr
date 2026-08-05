 @extends('layouts.app')
@section('title', 'Coverage Offsite — ' . $unit->unit_name)

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Coverage Offsite</h1>
        <p>{{ $unit->unit_name }} ({{ $unit->unit_code }}) — Periode {{ $period }}</p>
    </div>
<a href="{{ route('coverage.index') }}" class="btn btn-outline btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="grid grid-cols-2" style="margin-bottom:1.25rem;">
    <div class="card">
        <div class="card-header"><div class="card-title">Setup Fungsi Unit (8 Area)</div></div>
        <form action="{{ route('coverage.store', $unit) }}" method="POST">
            @csrf
            <input type="hidden" name="period" value="{{ $period }}">
<div class="card-body">
                @php
                    $allAreas = [
                        'teller_kas'     => 'Teller/Kas',
                        'cs_dpk'         => 'CS/DPK',
                        'kredit'         => 'Kredit',
                        'atm'            => 'ATM',
                        'biaya_jurnal'   => 'Biaya/Jurnal',
                        'apu_fds'        => 'APU/FDS',
                        'ti_event'       => 'TI Event',
                        'pengaduan_aset' => 'Pengaduan/Aset',
                    ];
                    $relevant = \App\Models\CoverageSetup::relevantAreas($unit->unit_type);
                    $areas = array_intersect_key($allAreas, array_flip($relevant));
                @endphp
                @foreach($areas as $field => $label)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.4rem 0;border-bottom:1px solid var(--border-color);">
                    <label style="font-size:0.85rem;font-weight:500;">{{ $label }}</label>
                    <select name="{{ $field }}" class="form-select" style="width:130px;padding:0.3rem 0.6rem;font-size:0.82rem;">
                        @foreach(['Ya','Tidak','Event'] as $opt)
                        <option value="{{ $opt }}" {{ ($setup?->$field ?? $defaults[$field] ?? 'Tidak') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                @endforeach
            </div>
<div style="padding:1rem 1.25rem;">
                <div class="form-group" style="margin-bottom:0.75rem;">
                    <label class="form-label">Alasan Perubahan (untuk audit trail)</label>
                    <textarea name="reason" class="form-input" rows="2" placeholder="Alasan perubahan setup coverage..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;"><i class="bi bi-save"></i> Simpan Coverage Setup</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">Coverage Summary</div></div>
        <div class="card-body">
            @if($summary)
            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;padding:0.75rem;background:#f0f4f8;border-radius:var(--radius-md);">
                <div>
                    <div style="font-size:0.72rem;color:var(--text-muted);">Coverage Score</div>
                    <div style="font-size:2rem;font-weight:700;color:var(--bs-blue-dark);">{{ round($summary->coverage_score * 100) }}%</div>
                </div>
                <div>
                    @php $stCls = match($summary->coverage_status) { 'Lengkap'=>'badge-success','Cukup'=>'badge-warning',default=>'badge-danger' }; @endphp
<span class="badge {{ $stCls }}">{{ $summary->coverage_status }}</span>
                    <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.3rem;">{{ $summary->active_area_count }} dari {{ count($relevant) }} area relevan aktif</div>
                </div>
            </div>
            @php
                $areaLabels = [
                    'status_teller_kas'=>'Teller/Kas','status_cs_dpk'=>'CS/DPK',
                    'status_kredit'=>'Kredit','status_atm'=>'ATM',
                    'status_biaya_jurnal'=>'Biaya/Jurnal','status_apu_fds'=>'APU/FDS',
                    'status_ti_event'=>'TI Event','status_pengaduan_aset'=>'Pengaduan/Aset',
                ];
                $areaLabels = array_intersect_key($areaLabels, array_flip(array_map(fn($a)=>'status_'.$a, $relevant)));
            @endphp
            @foreach($areaLabels as $field => $label)
            @php $val = $summary->$field ?? 'Tidak'; $cls = match($val) { 'H+1'=>'badge-success','Event-based'=>'badge-warning',default=>'badge-gray' }; @endphp
            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.35rem 0;border-bottom:1px solid var(--border-color);font-size:0.82rem;">
                <span>{{ $label }}</span><span class="badge {{ $cls }}">{{ $val }}</span>
            </div>
            @endforeach
            @else
            <div class="empty-state" style="padding:1.5rem;"><i class="bi bi-grid-3x3-gap"></i><p>Simpan setup untuk melihat summary.</p></div>
            @endif
        </div>
    </div>
</div>

@if($details->count())
<div class="card">
    <div class="card-header"><div class="card-title">Detail per Data Code</div></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Data Code</th><th>Area</th><th>Mode Coverage</th><th>Masuk SOP 02</th><th>Masuk SOP 04</th></tr></thead>
            <tbody>
                @foreach($details as $d)
                @php $modeCls = match($d->final_coverage_mode) { 'H+1'=>'badge-success','Event-based'=>'badge-warning','Onsite-Periodik'=>'badge-info',default=>'badge-gray' }; @endphp
                <tr>
                    <td style="font-size:0.8rem;font-weight:600;">{{ $d->dataCode?->data_code }}</td>
                    <td style="font-size:0.8rem;">{{ $d->dataCode?->area }}</td>
                    <td><span class="badge {{ $modeCls }}">{{ $d->final_coverage_mode }}</span></td>
                    <td><span class="badge {{ $d->enters_sop02 ? 'badge-success' : 'badge-gray' }}">{{ $d->enters_sop02 ? 'Ya' : 'Tidak' }}</span></td>
                    <td><span class="badge {{ $d->enters_sop04 ? 'badge-info' : 'badge-gray' }}">{{ $d->enters_sop04 ? 'Ya' : 'Tidak' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
