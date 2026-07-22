@extends('layouts.app')

@section('title', 'Dashboard Resident Auditor')

@section('content')
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h2 class="card-title">Ringkasan Sistem Resident Auditor (RA)</h2>
    </div>
    
    <div class="grid grid-cols-4">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-journal-text"></i></div>
            <div class="stat-info">
                <h3>Rencana Audit</h3>
                <p>12</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-clipboard-check"></i></div>
            <div class="stat-info">
                <h3>Pelaksanaan</h3>
                <p>8</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-search"></i></div>
            <div class="stat-info">
                <h3>Temuan & Tindak Lanjut</h3>
                <p>24</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-file-earmark-bar-graph"></i></div>
            <div class="stat-info">
                <h3>Laporan</h3>
                <p>5</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-2">
    <!-- Alur Kerja -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Siklus Berkelanjutan (RA)</h2>
        </div>
        <div style="padding-top: 1rem;">
            <ul style="list-style: none; padding-left: 0; position: relative;">
                <li style="margin-bottom: 1.5rem; display: flex; align-items: center;">
                    <span style="background: var(--bs-blue); color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 1rem; font-size: 0.8rem;">1</span>
                    <div>
                        <strong style="color: var(--bs-blue-dark); font-size: 0.95rem;">Input Rencana Audit (RM/K/A/N)</strong>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Menginput kegiatan sesuai parameter, Scoring Parameter, Approval RA.</p>
                    </div>
                </li>
                <li style="margin-bottom: 1.5rem; display: flex; align-items: center;">
                    <span style="background: var(--bs-blue); color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 1rem; font-size: 0.8rem;">2</span>
                    <div>
                        <strong style="color: var(--bs-blue-dark); font-size: 0.95rem;">Pelaksanaan Audit (RA)</strong>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Penugasan Audit P/M, Pelaksanaan Audit di cabang, Approval Hasil.</p>
                    </div>
                </li>
                <li style="margin-bottom: 1.5rem; display: flex; align-items: center;">
                    <span style="background: var(--bs-yellow); color: var(--bs-blue-dark); border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 1rem; font-size: 0.8rem;">3</span>
                    <div>
                        <strong style="color: var(--bs-blue-dark); font-size: 0.95rem;">Monitoring & Tindak Lanjut</strong>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Pemantauan program kerja, Penyelesaian Tindak Lanjut.</p>
                    </div>
                </li>
                <li style="display: flex; align-items: center;">
                    <span style="background: var(--bs-blue); color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 1rem; font-size: 0.8rem;">4</span>
                    <div>
                        <strong style="color: var(--bs-blue-dark); font-size: 0.95rem;">Reporting & Laporan</strong>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Sistem menghitung skor, Laporan bulanan/triwulanan.</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <!-- Status Audit Terkini -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Status Pelaksanaan Terkini</h2>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cabang</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Cabang KCU</td>
                        <td>Operasional</td>
                        <td><span class="badge badge-success">Selesai</span></td>
                        <td><a href="#" style="color: var(--bs-blue); font-size: 0.8rem; text-decoration: none; font-weight: 500;">Lihat Detail</a></td>
                    </tr>
                    <tr>
                        <td>Cabang Palu</td>
                        <td>Kredit</td>
                        <td><span class="badge badge-warning">Monitoring</span></td>
                        <td><a href="#" style="color: var(--bs-blue); font-size: 0.8rem; text-decoration: none; font-weight: 500;">Tindak Lanjut</a></td>
                    </tr>
                    <tr>
                        <td>Cabang Parigi</td>
                        <td>Kepatuhan</td>
                        <td><span class="badge badge-info">Approval P/M</span></td>
                        <td><a href="#" style="color: var(--bs-blue); font-size: 0.8rem; text-decoration: none; font-weight: 500;">Evaluasi</a></td>
                    </tr>
                    <tr>
                        <td>Cabang Poso</td>
                        <td>Operasional</td>
                        <td><span class="badge badge-warning">Pelaksanaan</span></td>
                        <td><a href="#" style="color: var(--bs-blue); font-size: 0.8rem; text-decoration: none; font-weight: 500;">Update</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
