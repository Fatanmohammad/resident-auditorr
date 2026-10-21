@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-primary">WP-RA-07-H | MATRIKS KPI MONITORING TINDAK LANJUT RA</h4>
            <small class="text-muted">Menilai proses dalam kendali RA; kegagalan auditee tidak otomatis menurunkan KPI bila monitoring dan eskalasi terbukti[cite: 6]</small>
        </div>
    </div>

    @include('monitoring.partials.nav_tabs')

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-uppercase mb-1" style="font-size: 11px;">Skor Proses Monitoring RA</h6>
                    <h3 class="fw-bold mb-0">100.0% <span class="badge bg-success fs-6">BAIK</span></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 12px;">
                    <thead class="bg-dark text-white text-center">
                        <tr>
                            <th>Indikator</th>
                            <th>Numerator</th>
                            <th>Denominator</th>
                            <th>Realisasi</th>
                            <th>Target</th>
                            <th>Bobot</th>
                            <th>Nilai Tertimbang</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold">Kelengkapan Register</td>
                            <td class="text-center">1</td>
                            <td class="text-center">1</td>
                            <td class="text-center fw-bold">100.0%</td>
                            <td class="text-center">98.0%</td>
                            <td class="text-center">20.0%</td>
                            <td class="text-center fw-bold">20.0%</td>
                            <td class="text-center"><span class="badge bg-success">MEMENUHI</span></td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Ketepatan Reminder</td>
                            <td class="text-center">0</td>
                            <td class="text-center">0</td>
                            <td class="text-center">-</td>
                            <td class="text-center">95.0%</td>
                            <td class="text-center">20.0%</td>
                            <td class="text-center">0.0%</td>
                            <td class="text-center"><span class="badge bg-secondary">BELUM ADA DATA</span></td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Verifikasi Bukti Tepat Waktu</td>
                            <td class="text-center">0</td>
                            <td class="text-center">0</td>
                            <td class="text-center">-</td>
                            <td class="text-center">95.0%</td>
                            <td class="text-center">20.0%</td>
                            <td class="text-center">0.0%</td>
                            <td class="text-center"><span class="badge bg-secondary">BELUM ADA DATA</span></td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Eskalasi Overdue</td>
                            <td class="text-center">0</td>
                            <td class="text-center">0</td>
                            <td class="text-center">-</td>
                            <td class="text-center">95.0%</td>
                            <td class="text-center">15.0%</td>
                            <td class="text-center">0.0%</td>
                            <td class="text-center"><span class="badge bg-secondary">BELUM ADA DATA</span></td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Akurasi Closure</td>
                            <td class="text-center">0</td>
                            <td class="text-center">0</td>
                            <td class="text-center">-</td>
                            <td class="text-center">100.0%</td>
                            <td class="text-center">15.0%</td>
                            <td class="text-center">0.0%</td>
                            <td class="text-center"><span class="badge bg-secondary">BELUM ADA DATA</span></td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Kelengkapan Dokumentasi</td>
                            <td class="text-center">0</td>
                            <td class="text-center">0</td>
                            <td class="text-center">-</td>
                            <td class="text-center">95.0%</td>
                            <td class="text-center">10.0%</td>
                            <td class="text-center">0.0%</td>
                            <td class="text-center"><span class="badge bg-secondary">BELUM ADA DATA</span></td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection