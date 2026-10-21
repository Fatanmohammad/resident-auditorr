@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 action-header-tools">
        <div>
            <h4 class="fw-bold mb-1 text-primary"><i class="bi bi-shield-exclamation me-2"></i> DASHBOARD PEMERIKSAAN AWAL INDIKASI FRAUD (SOP 09)</h4>
            <p class="text-muted mb-0" style="font-size: 13px;">Overview eksekutif fact-finding, triase, chain of custody, dan eskalasi kasus</p>
        </div>
    </div>

    @include('pemeriksaan_awal.partials.nav_tabs')

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white rounded-3">
                <div class="card-body">
                    <h6 class="text-white-50 small text-uppercase fw-bold">Total Indikasi Terdaftar</h6>
                    <h2 class="fw-bold mb-0">{{ $totalIndikasi ?? 0 }}</h2>
                    <small>Kasus Masuk (Register A)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white rounded-3">
                <div class="card-body">
                    <h6 class="text-white-50 small text-uppercase fw-bold">Kasus Urgent / Segera</h6>
                    <h2 class="fw-bold mb-0">{{ $urgentCount ?? 0 }}</h2>
                    <small>Berdasarkan Skor Triase</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white rounded-3">
                <div class="card-body">
                    <h6 class="text-white-50 small text-uppercase fw-bold">Bukti Diamankan (CoC)</h6>
                    <h2 class="fw-bold mb-0">{{ $buktiCount ?? 0 }}</h2>
                    <small>Chain of Custody Aktif</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark rounded-3">
                <div class="card-body">
                    <h6 class="text-dark-50 small text-uppercase fw-bold">Eskalasi Investigasi</h6>
                    <h2 class="fw-bold mb-0">{{ $eskalasiCount ?? 0 }}</h2>
                    <small>Dilanjut ke Tim Investigasi SKAI</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-4 text-center py-5">
            <i class="bi bi-shield-check text-primary fs-1 mb-3 d-block"></i>
            <h5 class="fw-bold text-dark">Modul Pemeriksaan Awal Indikasi Fraud Siap Digunakan</h5>
            <p class="text-muted mb-3">Gunakan menu navigasi di atas untuk mengakses lembar kerja Register Indikasi, Triase, Chain of Custody, KKA, hingga Keputusan Eskalasi.</p>
            <a href="{{ route('pemeriksaan_awal.register') }}" class="btn btn-primary btn-sm px-4 fw-semibold">Buka Register Indikasi <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
</div>
@endsection