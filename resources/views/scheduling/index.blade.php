@extends('layouts.app')
@section('title', 'Jadwal Kunjungan Onsite')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Jadwal Kunjungan Onsite</h1>
        <p>Frekuensi dan jadwal kunjungan fisik per unit — Periode {{ $period }}</p>
    </div>
    @if(in_array(auth()->user()->role, ['kabag_ra','kadiv_skai']))
    <form action="{{ route('scheduling.generate-all') }}" method="POST" style="display:inline;">
        @csrf
        <input type="hidden" name="period" value="{{ $period }}">
        <button type="submit" class="btn btn-primary" onclick="return confirm('Generate semua frekuensi & jadwal untuk periode {{ $period }}?')">
            <i class="bi bi-lightning-charge"></i> Generate Semua
        </button>
    </form>
@endif
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Daftar Unit & Frekuensi</div>
        <form method="GET" style="display:flex;gap:0.5rem;">
            <select name="period" class="form-select" style="width:auto;padding:0.35rem 0.75rem;font-size:0.82rem;" onchange="this.form.submit()">
                @for($y = date('Y')+1; $y >= 2025; $y--)
                <option value="{{ $y }}" {{ $y == $period ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Unit</th><th>Tipe</th><th>Risiko</th><th>Primary RA</th><th>Frekuensi</th><th>Kunjungan/Tahun</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($units as $unit)
                @php
                    $scoring   = $unit->riskScorings->first();
                    $freqLabel = $frequencies[$unit->id] ?? '-';
                    $riskCls   = match($scoring?->final_category) {
                        'High'=>'badge-danger','Moderate to High'=>'badge-warning',
                        'Moderate'=>'badge-info','Low to Moderate'=>'badge-purple',default=>'badge-gray'
                    };
                @endphp
                <tr>
                    <td>
                        <strong>{{ $unit->unit_name }}</strong>
                        <div style="font-size:0.72rem;color:var(--text-muted);">{{ $unit->unit_code }}</div>
                    </td>
                    <td><span class="badge badge-info">{{ $unit->unit_type }}</span></td>
                    <td><span class="badge {{ $riskCls }}">{{ $scoring?->final_category ?? '-' }}</span></td>
                    <td style="font-size:0.82rem;">{{ $unit->raAssignment?->primaryRa?->ra_name ?? '-' }}</td>
                    <td style="font-size:0.82rem;">{{ $freqLabel }}</td>
                    <td style="text-align:center;">
                        @php $visits = \App\Models\OnsiteFrequency::where('unit_id',$unit->id)->where('period',$period)->first(); @endphp
                        @if($visits?->is_resident_daily_review)
                            <span style="font-size:0.78rem;color:var(--text-muted);">Harian</span>
                        @else
                            {{ $visits?->final_visits_per_year ?? '-' }}
                        @endif
                    </td>
                    <td><a href="{{ route('scheduling.unit', ['unit'=>$unit,'period'=>$period]) }}" class="btn btn-outline btn-sm"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-calendar-x"></i><p>Belum ada data. Klik "Generate Semua" untuk memproses.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
