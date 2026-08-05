@extends('layouts.app')
@section('title', 'Hasil Skor & Kategori')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Penilaian Risiko</h1>
        <p>Hasil skor komponen & kategori risiko per unit — Periode {{ $period }}</p>
    </div>
    <form method="GET">
        <select name="period" class="form-select" style="width:auto;padding:0.35rem 0.75rem;font-size:0.82rem;" onchange="this.form.submit()">
            @for($y = date('Y')+1; $y >= 2025; $y--)
            <option value="{{ $y }}" {{ $y == $period ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </form>
</div>

{{-- Ringkasan --}}
@php
    $scored = $units->filter(fn($u) => $u->riskScorings->isNotEmpty());
    $counts = $scored->groupBy(fn($u) => $u->riskScorings->first()->final_category)->map->count();
@endphp
<div class="grid grid-cols-5" style="margin-bottom:1.25rem;">
    @foreach(['High','Moderate to High','Moderate','Low to Moderate','Low'] as $cat)
    @php
        $cls = match($cat) { 'High'=>'badge-danger','Moderate to High'=>'badge-warning','Moderate'=>'badge-info','Low to Moderate'=>'badge-purple',default=>'badge-gray' };
    @endphp
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label"><span class="badge {{ $cls }}">{{ $cat }}</span></div>
            <div class="stat-value">{{ $counts[$cat] ?? 0 }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Skor Komponen & Kategori Risiko</div>
        <div style="display:flex;gap:0.5rem;">
            <form method="GET" style="display:flex;gap:0.5rem;">
                <input type="hidden" name="period" value="{{ $period }}">
                <select name="cat" class="form-select" style="width:auto;padding:0.35rem 0.75rem;font-size:0.82rem;" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach(['High','Moderate to High','Moderate','Low to Moderate','Low'] as $c)
                    <option value="{{ $c }}" {{ request('cat') == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Unit</th>
                    <th>Riwayat RA</th><th>Kas/Teller</th><th>CS/DPK</th>
                    <th>Kredit</th><th>TI/ATM</th><th>Monitor TL</th>
                    <th>Skor Final</th><th>Kategori Awal</th><th>Override</th>
                    <th>Kategori Final</th><th>Prioritas</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $filterCat = request('cat');
                    $rows = $units->when($filterCat, fn($c) => $c->filter(fn($u) => $u->riskScorings->first()?->final_category === $filterCat));
                @endphp
                @forelse($rows as $unit)
@php
                    $scor = $unit->riskScorings->first();
                    $csRow = $unit->hasMany(\App\Models\RiskComponentScore::class)->where('period', $period)->first();
                    // Bidang yang tidak relevan per jenis unit → tampilkan "-"
                    $irr = [];
                    if ($unit->unit_type === 'Payment Point') $irr = ['cs_dpk', 'kredit', 'ti_atm'];
                    elseif ($unit->unit_type === 'KCPLK') $irr = ['kredit'];
                    $riskCls = match($scor?->final_category) {
                        'High'=>'badge-danger','Moderate to High'=>'badge-warning',
                        'Moderate'=>'badge-info','Low to Moderate'=>'badge-purple',default=>'badge-gray'
                    };
                    $initCls = match($scor?->initial_category) {
                        'High'=>'badge-danger','Moderate to High'=>'badge-warning',
                        'Moderate'=>'badge-info','Low to Moderate'=>'badge-purple',default=>'badge-gray'
                    };
                    $fmt = fn($bidangKey, $nilai) => in_array($bidangKey, $irr) ? '<span style="color:var(--text-muted);">-</span>' : number_format($nilai, 1);
                @endphp
                <tr>
                    <td>
                        <strong>{{ $unit->unit_name }}</strong>
                        <div style="font-size:0.72rem;color:var(--text-muted);">{{ $unit->unit_code }} · {{ $unit->unit_type }}</div>
                    </td>
<td style="text-align:center;">{!! $fmt('riwayat_ra', $csRow?->skor_riwayat_ra ?? 0) !!}</td>
                    <td style="text-align:center;">{!! $fmt('kas_teller', $csRow?->skor_kas_teller ?? 0) !!}</td>
                    <td style="text-align:center;">{!! $fmt('cs_dpk', $csRow?->skor_cs_dpk ?? 0) !!}</td>
                    <td style="text-align:center;">{!! $fmt('kredit', $csRow?->skor_kredit ?? 0) !!}</td>
                    <td style="text-align:center;">{!! $fmt('ti_atm', $csRow?->skor_ti_atm ?? 0) !!}</td>
                    <td style="text-align:center;">{!! $fmt('monitoring_tl', $csRow?->skor_monitoring_tl ?? 0) !!}</td>
                    <td style="text-align:center;font-weight:700;color:var(--bs-blue-dark);">{{ number_format($scor?->weighted_score ?? 0, 1) }}</td>
                    <td>
                        @if($scor)
                            <span class="badge {{ $initCls }}">{{ $scor->initial_category }}</span>
                        @else
                            <span class="badge badge-gray">-</span>
                        @endif
                    </td>
                    <td>
                        @if($scor?->has_active_override)
                            <span class="badge badge-danger" style="font-size:0.7rem;">Aktif</span>
                        @else
                            <span class="badge badge-gray">-</span>
                        @endif
                    </td>
                    <td>
                        @if($scor)
                            <span class="badge {{ $riskCls }}">{{ $scor->final_category }}</span>
                        @else
                            <span class="badge badge-gray">Belum dinilai</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @if($scor?->priority_rank)
                            <span class="badge badge-info">{{ $scor->priority_rank }}</span>
                        @else
                            -
                        @endif
                    </td>
<td>
                        <a href="{{ route('units.show', ['unit' => $unit, 'period' => $period, 'from' => 'risk-scoring']) }}" class="btn btn-outline btn-sm" title="Drill-down"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="13"><div class="empty-state"><i class="bi bi-bar-chart"></i><p>Belum ada data scoring. Input Raw Metrics terlebih dahulu.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
