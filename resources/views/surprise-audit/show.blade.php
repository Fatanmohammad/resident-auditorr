@extends('layouts.app')

@section('title', 'Detail Penugasan - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('surprise_audit.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Daftar Penugasan
            </a>
            <h2 class="fw-bold text-dark mb-1">
                Detail Penugasan Surprise Audit
            </h2>
            <p class="text-muted mb-0">Kelola dan pantau pelaksanaan audit mendadak berdasarkan master template WP SOP 05</p>
        </div>
        <div>
            <div class="d-flex align-items-center gap-2">
                @php
                    $curStatus = strtoupper(trim($assignment->status_assignment ?? $assignment->status ?? 'SIAP DILAKSANAKAN'));
                    $badgeBg = match($curStatus) {
                        'SELESAI', 'SELESAI LENGKAP' => 'bg-success text-white',
                        'DALAM PROSES', 'SEDANG BERJALAN' => 'bg-warning text-dark',
                        'SIAP DILAKSANAKAN' => 'bg-primary text-white',
                        default => 'bg-secondary text-white',
                    };
                    $curIcon = match($curStatus) {
                        'SELESAI', 'SELESAI LENGKAP' => 'bi-check-circle-fill',
                        'DALAM PROSES', 'SEDANG BERJALAN' => 'bi-hourglass-split',
                        'SIAP DILAKSANAKAN' => 'bi-clock-history',
                        default => 'bi-tag-fill',
                    };
                @endphp
                <span class="badge {{ $badgeBg }} fs-6 px-3 py-2 rounded-pill shadow-sm">
                    <i class="bi {{ $curIcon }} me-1"></i> {{ $assignment->status_assignment ?? 'SIAP DILAKSANAKAN' }}
                </span>
                
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-pill px-3 shadow-sm bg-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-pencil-square me-1"></i> Ubah Status
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li>
                            <form action="{{ route('surprise_audit.status.update', $assignment->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status_assignment" value="SIAP DILAKSANAKAN">
                                <button type="submit" class="dropdown-item py-2"><span class="badge bg-primary me-2">&bull;</span> SIAP DILAKSANAKAN</button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('surprise_audit.status.update', $assignment->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status_assignment" value="DALAM PROSES">
                                <button type="submit" class="dropdown-item py-2"><span class="badge bg-warning text-dark me-2">&bull;</span> DALAM PROSES</button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('surprise_audit.status.update', $assignment->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status_assignment" value="SELESAI">
                                <button type="submit" class="dropdown-item py-2"><span class="badge bg-success me-2">&bull;</span> SELESAI</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Sukses -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- WP-RA-05-A | ASSIGNMENT DAN RENCANA SURPRISE AUDIT -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-primary text-white fw-bold py-3 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-file-earmark-text me-2"></i> WP-RA-05-A | ASSIGNMENT DAN RENCANA SURPRISE AUDIT</span>
            <span class="badge bg-light text-dark">MASTER ASSIGNMENT</span>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="fw-bold text-muted w-40">Visit ID:</td>
                            <td class="fw-bold text-dark">{{ $assignment->visit_id }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Tanggal/Jam Rencana:</td>
                            <td>{{ $assignment->tanggal_rencana ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Reviewer:</td>
                            <td>{{ $assignment->reviewer ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Nomor Assignment:</td>
                            <td>{{ $assignment->nomor_assignment ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Klasifikasi Kerahasiaan:</td>
                            <td><span class="text-danger fw-bold">{{ $assignment->klasifikasi_kerahasiaan ?? 'Rahasia - Need to Know' }}</span></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="fw-bold text-muted w-40">Kode Unit / Nama Unit:</td>
                            <td class="fw-bold text-dark">{{ $assignment->kode_unit ?? '-' }} — {{ $assignment->nama_unit ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Resident Auditor:</td>
                            <td>{{ $assignment->resident_auditor ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Dasar Penugasan:</td>
                            <td>{{ $assignment->dasar_penugasan ?? 'Informasi Khusus' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Pemberi Tugas:</td>
                            <td>{{ $assignment->pemberi_tugas ?? 'Pimpinan SKAI' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Tujuan Audit:</td>
                            <td>{{ $assignment->tujuan_audit ?? 'Memastikan kesesuaian saldo buku dengan kas fisik khasanah dan kas ATM secara mendadak.' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- KONTROL PELAKSANAAN PER AREA -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-dark text-white fw-bold py-3">
            <i class="bi bi-shield-check me-2 text-warning"></i> KONTROL PELAKSANAAN PER AREA
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 text-start ps-4">Area</th>
                            <th class="py-3">Dalam Lingkup</th>
                            <th class="py-3">Status KKA</th>
                            <th class="py-3">Objek/Baris Diuji</th>
                            <th class="py-3">Exception</th>
                            <th class="py-3">Klasifikasi Hasil</th>
                            <th class="py-3 pe-4">Status Area</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalObjek = 0;
                            $totalException = 0;
                            $areaDalamLingkupCount = 0;
                            $kkaSelesaiCount = 0;
                            $adaBelumLengkap = false;

                            // Default matriks 8 area sesuai SOP 05 Master Excel
                            $controlData = $controlData ?? [
                                ['area' => 'Kas Khasanah', 'dalam_lingkup' => 'Ya', 'status_kka' => 'BELUM LENGKAP', 'objek_diuji' => 0, 'exception' => 0, 'klasifikasi_hasil' => 'Normal', 'status_area' => 'KKA BELUM SELESAI'],
                                ['area' => 'Kas ATM', 'dalam_lingkup' => 'Ya', 'status_kka' => 'BELUM LENGKAP', 'objek_diuji' => 0, 'exception' => 0, 'klasifikasi_hasil' => 'Normal', 'status_area' => 'KKA BELUM SELESAI'],
                                ['area' => 'Kartu ATM/PIN', 'dalam_lingkup' => 'Ya', 'status_kka' => 'BELUM LENGKAP', 'objek_diuji' => 0, 'exception' => 0, 'klasifikasi_hasil' => 'Normal', 'status_area' => 'KKA BELUM SELESAI'],
                                ['area' => 'Persediaan Barang Cetakan', 'dalam_lingkup' => 'Tidak', 'status_kka' => '-', 'objek_diuji' => 0, 'exception' => 0, 'klasifikasi_hasil' => 'Normal', 'status_area' => 'TIDAK DALAM LINGKUP'],
                                ['area' => 'Dokumen Agunan', 'dalam_lingkup' => 'Tidak', 'status_kka' => '-', 'objek_diuji' => 0, 'exception' => 0, 'klasifikasi_hasil' => 'Normal', 'status_area' => 'TIDAK DALAM LINGKUP'],
                                ['area' => 'Aset', 'dalam_lingkup' => 'Ya', 'status_kka' => 'BELUM LENGKAP', 'objek_diuji' => 0, 'exception' => 0, 'klasifikasi_hasil' => 'Normal', 'status_area' => 'KKA BELUM SELESAI'],
                                ['area' => 'Lingkungan', 'dalam_lingkup' => 'Ya', 'status_kka' => 'BELUM LENGKAP', 'objek_diuji' => 0, 'exception' => 0, 'klasifikasi_hasil' => 'Normal', 'status_area' => 'KKA BELUM SELESAI'],
                                ['area' => 'Dokumen Berharga', 'dalam_lingkup' => 'Tidak', 'status_kka' => '-', 'objek_diuji' => 0, 'exception' => 0, 'klasifikasi_hasil' => 'Normal', 'status_area' => 'TIDAK DALAM LINGKUP'],
                            ];
                        @endphp

                        @foreach($controlData as $row)
                            @php
                                $totalObjek += $row['objek_diuji'];
                                $totalException += $row['exception'];
                                if($row['dalam_lingkup'] == 'Ya') $areaDalamLingkupCount++;
                                if(in_array($row['status_area'], ['SIAP DIREVIU', 'ADA EXCEPTION - SIAP DIREVIU'])) $kkaSelesaiCount++;
                                if(str_contains($row['status_area'], 'BELUM') || $row['status_area'] == 'KKA BELUM SELESAI') $adaBelumLengkap = true;
                            @endphp
                            <tr>
                                <td class="text-start fw-bold ps-4">
                                    {{-- Link menuju KKA Kas Khasanah --}}
                                    @if($row['area'] == 'Kas Khasanah')
                                        <a href="{{ route('surprise_audit.kas', $assignment->visit_id ?? $assignment->id) }}" class="text-decoration-none text-primary">
                                            <i class="bi bi-box-arrow-up-right me-1 small"></i> {{ $row['area'] }}
                                        </a>
                                    {{-- Link menuju KKA Kas ATM --}}
                                    @elseif($row['area'] == 'Kas ATM')
                                        <a href="{{ route('surprise_audit.atm', $assignment->visit_id ?? $assignment->id) }}" class="text-decoration-none text-primary">
                                            <i class="bi bi-box-arrow-up-right me-1 small"></i> {{ $row['area'] }}
                                        </a>
                                    {{-- Link menuju KKA Kartu ATM & PIN Mailer --}}
                                    @elseif($row['area'] == 'Kartu ATM/PIN')
                                        <a href="{{ route('surprise_audit.kartu', $assignment->visit_id ?? $assignment->id) }}" class="text-decoration-none text-primary">
                                            <i class="bi bi-box-arrow-up-right me-1 small"></i> {{ $row['area'] }}
                                        </a>
                                    {{-- Link menuju KKA Persediaan Barang Cetakan --}}
                                    @elseif($row['area'] == 'Persediaan Barang Cetakan')
                                        <a href="{{ route('surprise_audit.barang_cetakan', $assignment->visit_id ?? $assignment->id) }}" class="text-decoration-none text-primary">
                                            <i class="bi bi-box-arrow-up-right me-1 small"></i> {{ $row['area'] }}
                                        </a>
                                    {{-- Link menuju KKA Dokumen Agunan --}}
                                    @elseif($row['area'] == 'Dokumen Agunan')
                                        <a href="{{ route('surprise_audit.agunan', $assignment->visit_id ?? $assignment->id) }}" class="text-decoration-none text-primary">
                                            <i class="bi bi-box-arrow-up-right me-1 small"></i> {{ $row['area'] }}
                                        </a>
                                    {{-- Link menuju KKA Aset --}}
                                    @elseif($row['area'] == 'Aset')
                                        <a href="{{ route('surprise_audit.aset', $assignment->visit_id ?? $assignment->id) }}" class="text-decoration-none text-primary">
                                            <i class="bi bi-box-arrow-up-right me-1 small"></i> {{ $row['area'] }}
                                        </a>
                                    {{-- Link menuju KKA Lingkungan --}}
                                    @elseif($row['area'] == 'Lingkungan')
                                        <a href="{{ route('surprise_audit.lingkungan', $assignment->visit_id ?? $assignment->id) }}" class="text-decoration-none text-primary">
                                            <i class="bi bi-box-arrow-up-right me-1 small"></i> {{ $row['area'] }}
                                        </a>
                                    {{-- Link menuju KKA Dokumen Berharga --}}
                                    @elseif($row['area'] == 'Dokumen Berharga')
                                        <a href="{{ route('surprise_audit.dokumen_berharga', $assignment->visit_id ?? $assignment->id) }}" class="text-decoration-none text-primary">
                                            <i class="bi bi-box-arrow-up-right me-1 small"></i> {{ $row['area'] }}
                                        </a>
                                    @else
                                        {{ $row['area'] }}
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $row['dalam_lingkup'] == 'Ya' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $row['dalam_lingkup'] }}
                                    </span>
                                </td>
                                <td class="small">{{ $row['status_kka'] }}</td>
                                <td class="fw-bold">{{ $row['objek_diuji'] }}</td>
                                <td class="fw-bold {{ $row['exception'] > 0 ? 'text-danger' : 'text-success' }}">{{ $row['exception'] }}</td>
                                <td>{{ $row['klasifikasi_hasil'] }}</td>
                                <td class="pe-4">
                                    <span class="badge {{ $row['status_area'] == 'TIDAK DALAM LINGKUP' ? 'bg-light text-dark border' : ($row['status_area'] == 'KKA BELUM SELESAI' ? 'bg-warning text-dark' : 'bg-info') }}">
                                        {{ $row['status_area'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- RINGKASAN KESELURUHAN (SUMMARY) -->
    <div class="card bg-light border-0 shadow-sm rounded-3 p-3">
        <div class="row text-center align-items-center">
            <div class="col">
                <small class="text-muted d-block fw-bold text-uppercase">Area Dalam Lingkup</small>
                <span class="fw-bold fs-5 text-dark">{{ $areaDalamLingkupCount }}</span>
            </div>
            <div class="col border-start">
                <small class="text-muted d-block fw-bold text-uppercase">KKA Selesai</small>
                <span class="fw-bold fs-5 text-success">{{ $kkaSelesaiCount }}</span>
            </div>
            <div class="col border-start">
                <small class="text-muted d-block fw-bold text-uppercase">Total Objek Diuji</small>
                <span class="fw-bold fs-5 text-primary">{{ $totalObjek }}</span>
            </div>
            <div class="col border-start">
                <small class="text-muted d-block fw-bold text-uppercase">Total Exception</small>
                <span class="fw-bold fs-5 {{ $totalException > 0 ? 'text-danger' : 'text-success' }}">{{ $totalException }}</span>
            </div>
            <div class="col border-start">
                <small class="text-muted d-block fw-bold text-uppercase">Status Paket Keseluruhan</small>
                <span class="badge {{ $adaBelumLengkap ? 'bg-danger' : 'bg-success' }} fs-6 px-3 py-1 mt-1">
                    {{ $adaBelumLengkap ? 'BELUM LENGKAP' : 'SELESAI LENGKAP' }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection