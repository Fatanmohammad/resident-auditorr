@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 position-relative" style="z-index: 10;">
    <div class="d-flex justify-content-between align-items-center mb-3" style="position: relative; z-index: 1050;">
        <div>
            <h4 class="fw-bold mb-0 text-primary">WP SOP 07 | RENCANA MONITORING, REMINDER DAN PRIORITAS</h4>
            <small class="text-muted">Dibentuk otomatis dari Master Register TL; RA tidak perlu menyalin identitas atau menghitung jadwal</small>
        </div>
        <div>
            <a href="{{ route('monitoring.export_excel', 'rencana_reminder') }}" class="btn btn-sm btn-outline-success shadow-sm fw-semibold" style="position: relative; z-index: 1060;">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Navigasi Tab Sheet -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('monitoring.index') ? 'active fw-bold' : 'text-secondary' }}" href="{{ route('monitoring.index') }}">Ringkasan & Control</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('monitoring.register_tl') ? 'active fw-bold' : 'text-secondary' }}" href="{{ route('monitoring.register_tl') }}">Register TL</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('monitoring.rencana_reminder') ? 'active fw-bold' : 'text-secondary' }}" href="{{ route('monitoring.rencana_reminder') }}">Rencana & Reminder</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('monitoring.log_bukti') ? 'active fw-bold' : 'text-secondary' }}" href="{{ route('monitoring.log_bukti') }}">Log Bukti & Komunikasi</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('monitoring.kka_verifikasi') ? 'active fw-bold' : 'text-secondary' }}" href="{{ route('monitoring.kka_verifikasi') }}">KKA Verifikasi</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('monitoring.closure_review') ? 'active fw-bold' : 'text-secondary' }}" href="{{ route('monitoring.closure_review') }}">Closure Review</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('monitoring.eskalasi') ? 'active fw-bold' : 'text-secondary' }}" href="{{ route('monitoring.eskalasi') }}">Perpanjangan & Eskalasi</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('monitoring.status_aging') ? 'active fw-bold' : 'text-secondary' }}" href="{{ route('monitoring.status_aging') }}">Status & Aging</a></li>
    </ul>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 650px; overflow-y: auto;">
                <table class="table table-bordered table-hover align-middle mb-0 text-nowrap" style="font-size: 11px;">
                    <thead class="bg-dark text-white text-center sticky-top" style="z-index: 5;">
                        <tr>
                            <th>TL ID</th>
                            <th>Unit/Kantor</th>
                            <th>Sumber TL</th>
                            <th>Risk/Prioritas</th>
                            <th>Target Berlaku</th>
                            <th>Status Final</th>
                            <th>Aging Bucket</th>
                            <th>Frekuensi</th>
                            <th>Jadwal Reminder</th>
                            <th>Reminder Terakhir</th>
                            <th>Status Reminder</th>
                            <th>Tindakan Berikut</th>
                            <th style="min-width: 200px;">Bukti Diharapkan</th>
                            <th>Closure Authority</th>
                            <th>RA Pemantau</th>
                            <th>Reviewer</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plans ?? [] as $item)
                        @php /** @var object $item */ @endphp
                        <tr>
                            <td><span class="badge bg-primary">{{ $item->tl_id }}</span></td>
                            <td>{{ $item->unit_kantor }}</td>
                            <td>{{ $item->sumber_tl }}</td>
                            <td class="text-center"><span class="badge bg-danger">{{ $item->risk_prioritas }}</span></td>
                            <td class="text-center">{{ $item->target_berlaku }}</td>
                            <td class="text-center"><span class="badge bg-warning text-dark">{{ $item->status_final }}</span></td>
                            <td class="text-center">{{ $item->aging_bucket }}</td>
                            <td class="text-center">{{ $item->frekuensi }}</td>
                            <td class="text-center">{{ $item->jadwal_reminder }}</td>
                            <td class="text-center">{{ $item->reminder_terakhir ?? '-' }}</td>
                            <td class="text-center"><span class="badge bg-secondary">{{ $item->status_reminder }}</span></td>
                            <td class="text-center fw-bold text-primary">{{ $item->tindakan_berikut }}</td>
                            <td>{{ $item->bukti_diharapkan ?? '-' }}</td>
                            <td>{{ $item->closure_authority ?? '-' }}</td>
                            <td>{{ $item->ra_pemantau ?? '-' }}</td>
                            <td>{{ $item->reviewer ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="16" class="text-center text-muted py-5 fw-semibold">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                Belum ada data Rencana & Reminder.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection