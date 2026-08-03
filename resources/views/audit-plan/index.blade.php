@extends('layouts.app')
@section('title', 'Audit Plan — SOP 01')

@section('content')

<div class="page-header">
    <div class="page-header-title">
        <h1>Audit Plan</h1>
        <p>SOP 01 — Perencanaan Audit Resident Auditor {{ now()->year }}</p>
    </div>
</div>

{{-- ============================================================ --}}
{{-- STEPPER SOP 01 --}}
{{-- ============================================================ --}}
<div style="margin-bottom: 2rem;">
    <div style="display:flex; align-items:flex-start; gap:0; position:relative;">

        @php
            $steps = [
                [
                    'num'   => 1,
                    'label' => 'Master Unit',
                    'desc'  => 'Data universe unit pengawasan',
                    'icon'  => 'bi-diagram-3',
                    'done'  => $unitCount > 0,
                    'roles' => ['kadiv_skai','kabag_ra','pimsie'],
                    'links' => [
                        ['label'=>'Lihat Master Unit','url'=>route('units.index'),'icon'=>'bi-eye'],
                        ['label'=>'Tambah Unit','url'=>route('units.create'),'icon'=>'bi-plus-lg','roles'=>['kadiv_skai','kabag_ra']],
                    ],
                ],
                [
                    'num'   => 2,
                    'label' => 'Input Risiko',
                    'desc'  => 'Raw metrics & risk scoring per unit',
                    'icon'  => 'bi-graph-up-arrow',
                    'done'  => $rawMetricCount > 0,
                    'roles' => ['kadiv_skai','kabag_ra','ra'],
                    'links' => [
                        ['label'=>'Input Raw Metrics','url'=>route('units.index').'?action=raw-metrics','icon'=>'bi-pencil-square','roles'=>['kabag_ra','ra']],
                    ],
                ],
                [
                    'num'   => 3,
                    'label' => 'Penugasan RA',
                    'desc'  => 'Coverage & assignment RA ke unit',
                    'icon'  => 'bi-person-check',
                    'done'  => $coverageCount > 0,
                    'roles' => ['kadiv_skai','kabag_ra'],
                    'links' => [
                        ['label'=>'Kelola Coverage','url'=>route('units.index').'?action=coverage','icon'=>'bi-people','roles'=>['kadiv_skai','kabag_ra']],
                    ],
                ],
                [
                    'num'   => 4,
                    'label' => 'Jadwal Kunjungan',
                    'desc'  => 'Frekuensi onsite & kapasitas RA',
                    'icon'  => 'bi-calendar-range',
                    'done'  => $scheduleCount > 0,
                    'roles' => ['kadiv_skai','kabag_ra','pimsie'],
                    'links' => [
                        ['label'=>'Lihat Jadwal','url'=>route('scheduling.index'),'icon'=>'bi-calendar3'],
                        ['label'=>'Kapasitas RA','url'=>route('scheduling.capacity'),'icon'=>'bi-person-check','roles'=>['kadiv_skai','kabag_ra']],
                        ['label'=>'Generate Semua','url'=>'#','icon'=>'bi-lightning-charge','roles'=>['kadiv_skai','kabag_ra'],'post'=>route('scheduling.generate-all')],
                    ],
                ],
                [
                    'num'   => 5,
                    'label' => 'Final Audit Plan',
                    'desc'  => 'Output akhir & change log',
                    'icon'  => 'bi-clipboard2-check',
                    'done'  => $finalPlanCount > 0,
                    'roles' => ['kadiv_skai','kabag_ra','pimsie','ra'],
                    'links' => [
                        ['label'=>'Lihat Final Plan','url'=>route('final-audit-plan.index'),'icon'=>'bi-eye'],
                        ['label'=>'Change Log','url'=>route('final-audit-plan.change-log'),'icon'=>'bi-clock-history'],
                        ['label'=>'Generate Final Plan','url'=>'#','icon'=>'bi-lightning-charge','roles'=>['kadiv_skai','kabag_ra'],'post'=>route('final-audit-plan.generate-all')],
                    ],
                ],
            ];
            $role = auth()->user()->role;
        @endphp

        @foreach($steps as $i => $step)
        @if(in_array($role, $step['roles']))
        <div style="flex:1; position:relative; {{ $i < count($steps)-1 ? '' : '' }}">
            {{-- Connector line --}}
            @if($i < count($steps)-1)
            <div style="position:absolute; top:20px; left:calc(50% + 20px); right:calc(-50% + 20px); height:2px; background:{{ $step['done'] ? 'var(--bs-blue)' : 'var(--border-color)' }}; z-index:0;"></div>
            @endif

            <div style="display:flex; flex-direction:column; align-items:center; position:relative; z-index:1;">
                {{-- Circle --}}
                <div style="width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.85rem; flex-shrink:0;
                    background:{{ $step['done'] ? 'var(--bs-blue)' : 'var(--bg-main)' }};
                    color:{{ $step['done'] ? '#fff' : 'var(--text-muted)' }};
                    border:2px solid {{ $step['done'] ? 'var(--bs-blue)' : 'var(--border-color)' }};">
                    @if($step['done'])
                    <i class="bi bi-check-lg"></i>
                    @else
                    {{ $step['num'] }}
                    @endif
                </div>
                {{-- Label --}}
                <div style="margin-top:0.5rem; text-align:center;">
                    <div style="font-size:0.78rem; font-weight:600; color:{{ $step['done'] ? 'var(--bs-blue-dark)' : 'var(--text-muted)' }};">{{ $step['label'] }}</div>
                    <div style="font-size:0.68rem; color:var(--text-muted); margin-top:0.1rem;">{{ $step['desc'] }}</div>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>

{{-- ============================================================ --}}
{{-- CARDS PER STEP --}}
{{-- ============================================================ --}}
<div style="display:flex; flex-direction:column; gap:1rem;">
    @foreach($steps as $step)
    @if(in_array($role, $step['roles']))
    <div class="card" style="border-left:3px solid {{ $step['done'] ? 'var(--bs-blue)' : 'var(--border-color)' }};">
        <div class="card-header" style="cursor:pointer;" onclick="toggleStep('step{{ $step['num'] }}')">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <div style="width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center;
                    background:{{ $step['done'] ? 'var(--bs-blue)' : 'var(--bg-main)' }};
                    border:2px solid {{ $step['done'] ? 'var(--bs-blue)' : 'var(--border-color)' }};">
                    <i class="bi {{ $step['done'] ? 'bi-check-lg' : $step['icon'] }}" style="font-size:0.85rem; color:{{ $step['done'] ? '#fff' : 'var(--text-muted)' }};"></i>
                </div>
                <div>
                    <div style="font-size:0.9rem; font-weight:600; color:var(--bs-blue-dark);">
                        Step {{ $step['num'] }} — {{ $step['label'] }}
                    </div>
                    <div style="font-size:0.75rem; color:var(--text-muted);">{{ $step['desc'] }}</div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <span class="badge {{ $step['done'] ? 'badge-success' : 'badge-gray' }}">
                    {{ $step['done'] ? 'Selesai' : 'Belum' }}
                </span>
                <i class="bi bi-chevron-down" id="chevron-step{{ $step['num'] }}" style="color:var(--text-muted); font-size:0.75rem; transition:transform 0.2s;"></i>
            </div>
        </div>
        <div id="step{{ $step['num'] }}" style="display:none; padding:0 1.25rem 1.25rem;">
            <div style="display:flex; flex-wrap:wrap; gap:0.5rem; padding-top:0.75rem; border-top:1px solid var(--border-color);">
                @foreach($step['links'] as $link)
                @if(!isset($link['roles']) || in_array($role, $link['roles']))
                    @if(isset($link['post']))
                    <form action="{{ $link['post'] }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm">
                            <i class="bi {{ $link['icon'] }}"></i> {{ $link['label'] }}
                        </button>
                    </form>
                    @else
                    <a href="{{ $link['url'] }}" class="btn btn-outline btn-sm">
                        <i class="bi {{ $link['icon'] }}"></i> {{ $link['label'] }}
                    </a>
                    @endif
                @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif
    @endforeach
</div>

{{-- ============================================================ --}}
{{-- APPROVAL WORKFLOW (bawah) --}}
{{-- ============================================================ --}}
<div style="margin-top:2rem;">
    <div style="font-size:0.72rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.75rem;">
        Approval Audit Plan
    </div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">Daftar Audit Plan</div>
            @if($role === 'pimsie')
            <a href="{{ route('audit-plan.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Buat Audit Plan
            </a>
            @endif
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>RA</th>
                        <th>Periode</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditPlans as $plan)
                    <tr>
                        <td><strong>{{ $plan->raUser?->name ?? '-' }}</strong></td>
                        <td>{{ $plan->tahun_periode }}</td>
                        <td style="font-size:0.78rem;">
                            {{ \Carbon\Carbon::parse($plan->jadwal_mulai)->format('d M Y') }}<br>
                            <span style="color:var(--text-muted);">s/d {{ \Carbon\Carbon::parse($plan->jadwal_selesai)->format('d M Y') }}</span>
                        </td>
                        <td>
                            @php
                                $cls = match($plan->status_approval) {
                                    'approved'               => 'badge-success',
                                    'rejected'               => 'badge-danger',
                                    'waiting_kabag_approval' => 'badge-warning',
                                    'waiting_kadiv_approval' => 'badge-purple',
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
                        <td>
                            <a href="{{ route('audit-plan.show', $plan->id) }}" class="btn btn-outline btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5"><div class="empty-state"><i class="bi bi-calendar-x"></i><p>Belum ada Audit Plan.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleStep(id) {
    const el = document.getElementById(id);
    const chevron = document.getElementById('chevron-' + id);
    const isOpen = el.style.display !== 'none';
    el.style.display = isOpen ? 'none' : 'block';
    chevron.style.transform = isOpen ? '' : 'rotate(180deg)';
}
</script>
@endpush
@endsection
