@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-primary">WP-RA-07-I | RINGKASAN TL BULANAN DAN KONSOLIDASI</h4>
            <small class="text-muted">Bahan otomatis untuk Pelaporan Bulanan</small>
        </div>
        <div>
            <span class="badge bg-dark p-2 fs-6">Periode Laporan: October 2026</span>
        </div>
    </div>

    @include('monitoring.partials.nav_tabs')

    <div class="row g-3">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold">Status TL</div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" style="font-size: 12px;">
                        <tr><td>OPEN</td><td class="text-end fw-bold">1</td></tr>
                        <tr><td>UNDER REVIEW</td><td class="text-end">0</td></tr>
                        <tr><td>INADEQUATE</td><td class="text-end">0</td></tr>
                        <tr><td>OVERDUE</td><td class="text-end">0</td></tr>
                        <tr><td>PROPOSED CLOSED</td><td class="text-end">0</td></tr>
                        <tr><td>CLOSED</td><td class="text-end">0</td></tr>
                        <tr><td>REOPENED</td><td class="text-end">0</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold">Aging / Prioritas</div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" style="font-size: 12px;">
                        <tr><td>HIGH</td><td class="text-end fw-bold">1</td></tr>
                        <tr><td>H-14</td><td class="text-end">0</td></tr>
                        <tr><td>H-7</td><td class="text-end">0</td></tr>
                        <tr><td>H-3</td><td class="text-end">0</td></tr>
                        <tr><td>H-1 / HARI H</td><td class="text-end">0</td></tr>
                        <tr><td>OVERDUE 1-7</td><td class="text-end">0</td></tr>
                        <tr><td>OVERDUE >30</td><td class="text-end">0</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold">Sumber TL</div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" style="font-size: 12px;">
                        <tr><td>Regulator</td><td class="text-end">0</td></tr>
                        <tr><td>SKAI</td><td class="text-end">0</td></tr>
                        <tr><td>Auditor Eksternal</td><td class="text-end">0</td></tr>
                        <tr><td>Resident Auditor</td><td class="text-end fw-bold">1</td></tr>
                        <tr><td>Manajemen/Komite</td><td class="text-end">0</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold">Isu Signifikan & Tindak Lanjut</div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" style="font-size: 11px;">
                        <tr><td>High Risk Belum Closed (1)</td><td>Prioritaskan monitoring & eskalasi</td></tr>
                        <tr><td>Overdue >30 Hari (0)</td><td>Eskalasi formal sesuai authority</td></tr>
                        <tr><td>Bukti Belum Memadai (0)</td><td>Minta bukti tambahan/verifikasi</td></tr>
                        <tr><td>Perlu Eskalasi (0)</td><td>Lengkapi bukti eskalasi & arahan</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection