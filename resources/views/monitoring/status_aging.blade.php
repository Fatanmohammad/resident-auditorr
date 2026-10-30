@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-4 position-relative" style="z-index: 10;">
    <div class="d-flex justify-content-between align-items-center mb-4" style="position: relative; z-index: 1050;">
        <div>
            <h4 class="fw-bold mb-1 text-primary"><i class="bi bi-clock-history me-2"></i> WP-RA-07-F | STATUS, AGING DAN OVERDUE</h4>
            <p class="text-muted mb-0" style="font-size: 13px;">Daftar kerja otomatis untuk TL mendekati jatuh tempo, overdue, inadequate, escalated, proposed closed, dan reopened</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('monitoring.export_excel', 'status_aging') }}" class="btn btn-sm btn-outline-success shadow-sm fw-semibold" style="position: relative; z-index: 1060;">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel Status Aging
            </a>
        </div>
    </div>

    @include('monitoring.partials.nav_tabs')

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 680px; overflow-y: auto;">
                <table class="table table-bordered table-hover align-middle mb-0 text-nowrap" style="font-size: 11.5px;">
                    <thead class="table-dark text-center sticky-top" style="z-index: 5;">
                        <tr>
                            <th class="py-3">1. TL ID</th>
                            <th class="py-3">2. Unit / Kantor</th>
                            <th class="py-3">3. Sumber TL</th>
                            <th class="py-3">4. Risk</th>
                            <th class="py-3">5. Target Berlaku</th>
                            <th class="py-3">6. Status Final</th>
                            <th class="py-3">7. Hari ke Target</th>
                            <th class="py-3">8. Aging Overdue</th>
                            <th class="py-3">9. Bucket</th>
                            <th class="py-3">10. Reminder Status</th>
                            <th class="py-3">11. Workflow</th>
                            <th class="py-3">12. RA Pemantau</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($agings) && count($agings) > 0)
                            @foreach($agings as $item)
                            <tr>
                                <td><span class="badge bg-primary px-2 py-1">{{ $item->tl_id }}</span></td>
                                <td class="fw-semibold">{{ $item->nama_unit_sumber ?? '-' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $item->sumber_tl ?? '-' }}</span></td>
                                <td class="text-center"><span class="badge bg-danger px-2 py-1">{{ $item->prioritas ?? '-' }}</span></td>
                                <td class="text-center">{{ $item->target_berlaku ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-secondary px-2 py-1">{{ $item->status_final ?? '-' }}</span></td>
                                <td class="text-end fw-bold">{{ $item->hari_ke_target ?? '-' }}</td>
                                <td class="text-end text-danger fw-bold">{{ $item->aging_overdue ?? '-' }}</td>
                                <td class="text-center">{{ $item->aging_bucket ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-info text-dark px-2 py-1">{{ $item->status_reminder ?? '-' }}</span></td>
                                <td class="text-center"><span class="badge bg-dark px-2 py-1">{{ $item->status_workflow ?? '-' }}</span></td>
                                <td>{{ $item->ra_pemantau ?? '-' }}</td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="12" class="text-center text-muted py-5 fw-semibold">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                    Belum ada data status, aging & overdue.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection