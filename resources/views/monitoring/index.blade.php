@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-primary"><i class="bi bi-speedometer2 me-2"></i> WP-RA-07-A | RINGKASAN & CONTROL MONITORING TINDAK LANJUT</h4>
            <p class="text-muted mb-0" style="font-size: 13px;">Dashboard kendali eksekutif, rekapitulasi volume temuan, serta distribusi workflow pemantauan Resident Auditor</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('monitoring.export_excel', 'ringkasan') }}" class="btn btn-sm btn-outline-success shadow-sm fw-semibold">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Ringkasan Excel
            </a>
        </div>
    </div>

    @include('monitoring.partials.nav_tabs')

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-primary text-white p-3 border-start border-4 border-dark">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase small fw-bold mb-1 text-white-50">Total Tindak Lanjut</h6>
                        <h3 class="fw-bold mb-0">{{ $totalTl ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-folder2-open fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-success text-white p-3 border-start border-4 border-dark">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase small fw-bold mb-1 text-white-50">Register Valid</h6>
                        <h3 class="fw-bold mb-0">{{ $registerValid ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-danger text-white p-3 border-start border-4 border-dark">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase small fw-bold mb-1 text-white-50">High Risk Belum Closed</h6>
                        <h3 class="fw-bold mb-0">{{ $highRiskUnclosed ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-dark text-white p-3 border-start border-4 border-secondary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase small fw-bold mb-1 text-white-50">Overdue / Jatuh Tempo</h6>
                        <h3 class="fw-bold mb-0">{{ $overdue ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-clock-history fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-dark text-white fw-bold py-3">
                    <i class="bi bi-diagram-3 me-2"></i> Rincian Status Workflow Pemantauan
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover align-middle mb-0" style="font-size: 12px;">
                        <thead class="table-light">
                            <tr>
                                <th class="py-2.5 px-3">Status Workflow</th>
                                <th class="py-2.5 px-3 text-end">Jumlah</th>
                                <th class="py-2.5 px-3 text-center">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workflowCounts ?? [] as $key => $val)
                            <tr>
                                <td class="px-3 fw-semibold">{{ ucwords(strtolower(str_replace('_', ' ', $key))) }}</td>
                                <td class="px-3 text-end fw-bold">{{ $val }}</td>
                                <td class="px-3 text-center"><span class="badge bg-secondary">{{ $totalTl > 0 ? round(($val / $totalTl) * 100, 1) : 0 }}%</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-dark text-white fw-bold py-3">
                    <i class="bi bi-pie-chart me-2"></i> Klasifikasi Sumber Temuan Tindak Lanjut
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover align-middle mb-0" style="font-size: 12px;">
                        <thead class="table-light">
                            <tr>
                                <th class="py-2.5 px-3">Sumber Temuan</th>
                                <th class="py-2.5 px-3 text-end">Volume</th>
                                <th class="py-2.5 px-3 text-center">Proporsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sumberCounts ?? [] as $key => $val)
                            <tr>
                                <td class="px-3 fw-semibold">{{ $key }}</td>
                                <td class="px-3 text-end fw-bold">{{ $val }}</td>
                                <td class="px-3 text-center"><span class="badge bg-info text-dark">{{ $totalTl > 0 ? round(($val / $totalTl) * 100, 1) : 0 }}%</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection