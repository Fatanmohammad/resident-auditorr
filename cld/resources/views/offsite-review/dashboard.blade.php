@extends('layouts.app')
@section('title', 'Dashboard Offsite Review — ' . $wp->kode_wp)

@section('content')

{{-- ================================================================
     BLOK 1 — HEADER METADATA WP + 6 KARTU STAT
     ================================================================ --}}

<div class="page-header">
    <div class="page-header-title">
        <h1><i class="bi bi-clipboard2-data" style="color:var(--bs-blue);"></i> Dashboard Offsite Review</h1>
        <p>{{ $wp->kode_wp }} &mdash; {{ $wp->periode_data }}</p>
    </div>
    <div style="display:flex;gap:0.5rem;align-items:center;">
        <form method="POST" action="{{ route('offsite-review.status', $wp) }}">
            @csrf @method('PATCH')
            <select name="status_wp" class="form-select" style="width:auto;display:inline-block;"
                onchange="this.form.submit()">
                @foreach(['Draft','In Review','Final','Approved'] as $s)
                    <option value="{{ $s }}" {{ $wp->status_wp === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('offsite-review.index') }}" class="btn btn-outline btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

{{-- Metadata WP --}}
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header">
        <div class="card-title"><i class="bi bi-info-circle"></i> Metadata Kertas Kerja</div>
        <span class="badge {{ match($wp->status_wp) {
            'Approved'  => 'badge-success',
            'Final'     => 'badge-info',
            'In Review' => 'badge-warning',
            default     => 'badge-gray'
        } }}">{{ $wp->status_wp }}</span>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-3" style="gap:0.75rem 1.5rem;">
            @php
                $meta = [
                    ['label'=>'Kode WP',        'value'=> $wp->kode_wp],
                    ['label'=>'Periode Data',    'value'=> $wp->periode_data],
                    ['label'=>'Kantor Induk',    'value'=> $wp->unit->parent_office ?? '-'],
                    ['label'=>'Unit',            'value'=> $wp->unit->unit_name ?? '-'],
                    ['label'=>'RA Pelaksana',    'value'=> $wp->ra->ra_name ?? '-'],
                    ['label'=>'Jenis Unit',      'value'=> $wp->unit->unit_type ?? '-'],
                    ['label'=>'Status WP',       'value'=> $wp->status_wp],
                    ['label'=>'Reviewer',        'value'=> $wp->reviewer ?? '-'],
                    ['label'=>'Validasi Unit',   'value'=> $wp->validasi_unit ? '✅ Tervalidasi' : '⚠️ Belum Divalidasi'],
                ];
            @endphp
            @foreach($meta as $m)
            <div style="display:flex;flex-direction:column;gap:0.15rem;">
                <span style="font-size:0.7rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.04em;">
                    {{ $m['label'] }}
                </span>
                <span style="font-size:0.875rem;font-weight:500;color:var(--text-main);">{{ $m['value'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- 6 Kartu Stat --}}
@php
    $cards = [
        ['icon'=>'bi-people',          'label'=>'Populasi Eligible',  'value'=> $stats['eligible'],    'sub'=>'record dari staging',       'color'=>'blue'],
        ['icon'=>'bi-file-earmark-check','label'=>'KKA Final',        'value'=> $stats['kkaFinal'],    'sub'=>'item sudah final',          'color'=>'green'],
        ['icon'=>'bi-exclamation-triangle','label'=>'Exception',      'value'=> $stats['exception'],   'sub'=>'item perlu tindak lanjut',  'color'=>'red'],
        ['icon'=>'bi-chat-dots',        'label'=>'Klarifikasi',        'value'=> $stats['klarifikasi'], 'sub'=>'menunggu konfirmasi unit',  'color'=>'yellow'],
        ['icon'=>'bi-arrow-up-circle',  'label'=>'Eskalasi',           'value'=> $stats['eskalasi'],    'sub'=>'risiko naik dari awal',     'color'=>'purple'],
        ['icon'=>'bi-speedometer2',     'label'=>'Progress Review',    'value'=> $stats['progress'].'%','sub'=>'KKA Final / Eligible',     'color'=>'blue'],
    ];
@endphp
<div class="grid grid-cols-3" style="margin-bottom:1.25rem;gap:1rem;">
    @foreach($cards as $c)
    <div class="stat-card">
        <div class="stat-icon {{ $c['color'] }}"><i class="bi {{ $c['icon'] }}"></i></div>
        <div class="stat-info">
            <div class="stat-label">{{ $c['label'] }}</div>
            <div class="stat-value">{{ $c['value'] }}</div>
            <div class="stat-sub">{{ $c['sub'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Progress bar --}}
<div style="margin-bottom:1.5rem;">
    <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:var(--text-muted);margin-bottom:0.35rem;">
        <span>Progress Review Keseluruhan</span>
        <span style="font-weight:600;color:var(--bs-blue-dark);">{{ $stats['progress'] }}%</span>
    </div>
    <div style="background:#e5e7eb;border-radius:9999px;height:10px;">
        <div style="width:{{ $stats['progress'] }}%;background:var(--bs-blue);height:10px;border-radius:9999px;transition:width 0.4s ease;"></div>
    </div>
</div>

{{-- ================================================================
     BLOK 2 — RINGKASAN ELIGIBLE PER AREA REVIEW
     ================================================================ --}}

<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header">
        <div class="card-title"><i class="bi bi-table"></i> Ringkasan Eligible Per Area Review</div>
        <span style="font-size:0.75rem;color:var(--text-muted);">
            Kolom <em>Low to Moderate</em> &amp; <em>Moderate to High</em> = eskalasi rule engine
        </span>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Area Review</th>
                    <th style="text-align:center;">Eligible</th>
                    <th style="text-align:center;color:#dc2626;">High</th>
                    <th style="text-align:center;color:#d97706;">Moderate</th>
                    <th style="text-align:center;color:#16a34a;">Low</th>
                    <th style="text-align:center;color:#7c3aed;">Low→Moderate</th>
                    <th style="text-align:center;color:#b45309;">Moderate→High</th>
                    <th style="text-align:center;color:var(--bs-blue);">KKA Final</th>
                    <th style="text-align:center;">Exception</th>
                    <th style="text-align:center;">Klarifikasi</th>
                </tr>
            </thead>
            <tbody>
                @php $totals = array_fill_keys(['eligible','high','moderate','low','low_to_moderate','moderate_to_high','kka_final','exception','klarifikasi'], 0); @endphp
                @forelse($areaRows as $row)
                @php
                    foreach(array_keys($totals) as $k) $totals[$k] += $row[$k];
                    $pct = $row['eligible'] > 0 ? round($row['kka_final'] / $row['eligible'] * 100) : 0;
                @endphp
                <tr>
                    <td>
                        <span style="font-weight:600;">{{ $row['area'] }}</span>
                        <div style="margin-top:0.25rem;background:#e5e7eb;border-radius:9999px;height:4px;width:80px;">
                            <div style="width:{{ $pct }}%;background:var(--bs-blue);height:4px;border-radius:9999px;"></div>
                        </div>
                    </td>
                    <td style="text-align:center;font-weight:600;">{{ $row['eligible'] }}</td>
                    <td style="text-align:center;">
                        @if($row['high'] > 0)
                            <span class="badge badge-danger">{{ $row['high'] }}</span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @if($row['moderate'] > 0)
                            <span class="badge badge-warning">{{ $row['moderate'] }}</span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @if($row['low'] > 0)
                            <span class="badge badge-success">{{ $row['low'] }}</span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @if($row['low_to_moderate'] > 0)
                            <span class="badge badge-purple">{{ $row['low_to_moderate'] }}</span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @if($row['moderate_to_high'] > 0)
                            <span class="badge" style="background:#fef3c7;color:#b45309;">{{ $row['moderate_to_high'] }}</span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td style="text-align:center;font-weight:600;color:var(--bs-blue);">{{ $row['kka_final'] }}</td>
                    <td style="text-align:center;">
                        @if($row['exception'] > 0)
                            <span class="badge badge-danger">{{ $row['exception'] }}</span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @if($row['klarifikasi'] > 0)
                            <span class="badge badge-info">{{ $row['klarifikasi'] }}</span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada data staging.</p></div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($areaRows->isNotEmpty())
            <tfoot>
                <tr style="background:#f8fafc;font-weight:700;border-top:2px solid var(--border-color);">
                    <td>TOTAL</td>
                    <td style="text-align:center;">{{ $totals['eligible'] }}</td>
                    <td style="text-align:center;"><span class="badge badge-danger">{{ $totals['high'] }}</span></td>
                    <td style="text-align:center;"><span class="badge badge-warning">{{ $totals['moderate'] }}</span></td>
                    <td style="text-align:center;"><span class="badge badge-success">{{ $totals['low'] }}</span></td>
                    <td style="text-align:center;"><span class="badge badge-purple">{{ $totals['low_to_moderate'] }}</span></td>
                    <td style="text-align:center;"><span class="badge" style="background:#fef3c7;color:#b45309;">{{ $totals['moderate_to_high'] }}</span></td>
                    <td style="text-align:center;color:var(--bs-blue);">{{ $totals['kka_final'] }}</td>
                    <td style="text-align:center;"><span class="badge badge-danger">{{ $totals['exception'] }}</span></td>
                    <td style="text-align:center;"><span class="badge badge-info">{{ $totals['klarifikasi'] }}</span></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- ================================================================
     BLOK 3 — KUALITAS & REKONSILIASI SUMBER DATA
     ================================================================ --}}

<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header">
        <div class="card-title"><i class="bi bi-database-check"></i> Kualitas &amp; Rekonsiliasi Sumber Data</div>
        <span style="font-size:0.72rem;color:var(--text-muted);">5 DUMP File CBS</span>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>DUMP</th>
                    <th>Sumber</th>
                    <th style="text-align:center;">Total Record</th>
                    <th style="text-align:center;color:#16a34a;">Normalized</th>
                    <th style="text-align:center;color:var(--bs-blue);">Eligible</th>
                    <th style="text-align:center;color:#dc2626;">Salah Unit</th>
                    <th style="text-align:center;color:#d97706;">Luar Periode</th>
                    <th style="text-align:center;color:var(--bs-blue);">KKA Final</th>
                    <th style="text-align:center;color:#dc2626;">Exception</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekonsiliasi as $row)
                @php
                    $pctNorm = $row['total'] > 0 ? round($row['normalized'] / $row['total'] * 100) : 0;
                    $pctElig = $row['total'] > 0 ? round($row['eligible']   / $row['total'] * 100) : 0;
                    $hasIssue = $row['salah_unit'] > 0 || $row['luar_periode'] > 0;
                @endphp
                <tr style="{{ $hasIssue ? 'background:#fffbeb;' : '' }}">
                    <td><span class="badge badge-gray" style="font-family:monospace;">{{ $row['dump'] }}</span></td>
                    <td style="font-weight:500;">{{ $row['label'] }}</td>
                    <td style="text-align:center;">{{ $row['total'] }}</td>
                    <td style="text-align:center;">
                        <div style="display:flex;align-items:center;gap:0.4rem;justify-content:center;">
                            <span style="font-weight:600;color:#16a34a;">{{ $row['normalized'] }}</span>
                            <span style="font-size:0.7rem;color:var(--text-muted);">({{ $pctNorm }}%)</span>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <div style="display:flex;align-items:center;gap:0.4rem;justify-content:center;">
                            <span style="font-weight:600;color:var(--bs-blue);">{{ $row['eligible'] }}</span>
                            <span style="font-size:0.7rem;color:var(--text-muted);">({{ $pctElig }}%)</span>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        @if($row['salah_unit'] > 0)
                            <span class="badge badge-danger">{{ $row['salah_unit'] }}</span>
                        @else
                            <span style="color:#16a34a;font-size:0.85rem;">✓</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @if($row['luar_periode'] > 0)
                            <span class="badge badge-warning">{{ $row['luar_periode'] }}</span>
                        @else
                            <span style="color:#16a34a;font-size:0.85rem;">✓</span>
                        @endif
                    </td>
                    <td style="text-align:center;font-weight:600;color:var(--bs-blue);">{{ $row['kka_final'] }}</td>
                    <td style="text-align:center;">
                        @if($row['exception'] > 0)
                            <span class="badge badge-danger">{{ $row['exception'] }}</span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada data staging.</p></div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ================================================================
     BLOK 4 — DISTRIBUSI KKA FINAL + KONTROL KESIAPAN
     ================================================================ --}}

<div class="grid grid-cols-2" style="gap:1.25rem;">

    {{-- Distribusi KKA Final --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-pie-chart"></i> Distribusi KKA Final</div>
            <span style="font-size:0.72rem;color:var(--text-muted);">Status per KKA Sheet</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>KKA Sheet</th>
                        <th style="text-align:center;">Total</th>
                        <th style="text-align:center;">Draft</th>
                        <th style="text-align:center;color:var(--bs-blue);">Final</th>
                        <th style="text-align:center;color:#16a34a;">Approved</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($distribusiKka as $row)
                    @php
                        $pctFinal = $row['total'] > 0 ? round(($row['final'] + $row['approved']) / $row['total'] * 100) : 0;
                    @endphp
                    <tr>
                        <td>
                            <span style="font-weight:500;">{{ $row['sheet'] ?? '(Belum diset)' }}</span>
                            <div style="margin-top:0.2rem;background:#e5e7eb;border-radius:9999px;height:3px;width:70px;">
                                <div style="width:{{ $pctFinal }}%;background:var(--bs-blue);height:3px;border-radius:9999px;"></div>
                            </div>
                        </td>
                        <td style="text-align:center;font-weight:600;">{{ $row['total'] }}</td>
                        <td style="text-align:center;">
                            @if($row['draft'] > 0)
                                <span class="badge badge-gray">{{ $row['draft'] }}</span>
                            @else <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($row['final'] > 0)
                                <span class="badge badge-info">{{ $row['final'] }}</span>
                            @else <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($row['approved'] > 0)
                                <span class="badge badge-success">{{ $row['approved'] }}</span>
                            @else <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state" style="padding:1.5rem;">
                                <i class="bi bi-file-earmark-x"></i>
                                <p>Belum ada KKA.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Kontrol Kesiapan Dashboard --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-shield-check"></i> Kontrol Kesiapan Dashboard</div>
            @php
                $allOk = collect($kontrol)->every(fn($k) => $k['ok']);
            @endphp
            <span class="badge {{ $allOk ? 'badge-success' : 'badge-warning' }}">
                {{ $allOk ? '✅ Siap' : '⚠️ Belum Siap' }}
            </span>
        </div>
        <div class="card-body" style="padding:0;">
            @foreach($kontrol as $i => $item)
            <div style="display:flex;align-items:center;gap:1rem;padding:0.875rem 1.25rem;
                {{ !$loop->last ? 'border-bottom:1px solid var(--border-color);' : '' }}
                {{ $item['ok'] ? '' : 'background:#fffbeb;' }}">
                {{-- Indikator --}}
                <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;
                    justify-content:center;font-size:1rem;flex-shrink:0;
                    background:{{ $item['ok'] ? '#d1fae5' : '#fee2e2' }};
                    color:{{ $item['ok'] ? '#065f46' : '#dc2626' }};">
                    <i class="bi {{ $item['ok'] ? 'bi-check-lg' : 'bi-x-lg' }}"></i>
                </div>
                {{-- Label + detail --}}
                <div style="flex:1;">
                    <div style="font-size:0.85rem;font-weight:600;color:var(--text-main);">
                        {{ $item['label'] }}
                    </div>
                    <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.1rem;">
                        {{ $item['detail'] }}
                    </div>
                </div>
                {{-- Badge status --}}
                <span class="badge {{ $item['ok'] ? 'badge-success' : 'badge-danger' }}">
                    {{ $item['ok'] ? 'OK' : 'Perlu Aksi' }}
                </span>
            </div>
            @endforeach
        </div>
        {{-- Ringkasan skor kesiapan --}}
        @php $okCount = collect($kontrol)->where('ok', true)->count(); @endphp
        <div style="padding:0.875rem 1.25rem;border-top:1px solid var(--border-color);
            background:#f8fafc;display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:0.8rem;color:var(--text-muted);">
                Indikator terpenuhi
            </span>
            <span style="font-size:0.875rem;font-weight:700;color:{{ $allOk ? '#065f46' : '#d97706' }};">
                {{ $okCount }} / {{ count($kontrol) }}
            </span>
        </div>
    </div>

</div>

@endsection
