@extends('layouts.app')

@section('title', 'Detail Penugasan - ' . $assignment->visit_id)

@section('content')
<style>
    .active-view-btn {
        background: #0f2e5a !important;
        color: #ffffff !important;
    }
    .kka-card-hover {
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .kka-card-hover:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(15, 46, 90, 0.08) !important;
        border-color: #93c5fd !important;
    }
</style>

<div class="container-fluid px-4 py-3" style="max-width: 1440px; margin: 0 auto; background-color: #f8fafc; min-height: 100vh;">
    <!-- Header Navigasi -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <a href="{{ route('surprise_audit.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block fw-semibold" style="font-size: 14px;">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Penugasan
            </a>
            <div class="d-flex align-items-center gap-2">
                <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: #0f2e5a; color: #ffffff; font-size: 13px;">
                    <i class="bi bi-shield-lock-fill me-1"></i> STANDAR SOP 05
                </span>
                <h3 class="mb-0 fw-bold" style="color: #0f2e5a; letter-spacing: -0.3px;">
                    Detail Penugasan Surprise Audit: {{ $assignment->visit_id }}
                </h3>
            </div>
            <p class="text-muted mb-0 mt-1" style="font-size: 14px;">
                {{ $assignment->nama_unit }} [{{ $assignment->kode_unit }}] &bull; Pemantauan KKA fisik, verifikasi bukti pendukung, dan kontrol pelaksanaan audit mendadak.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
            @php
                $curStatus = strtoupper(trim($assignment->status_assignment ?? $assignment->status ?? 'SIAP DILAKSANAKAN'));
                $badgeStyle = match($curStatus) {
                    'SELESAI', 'SELESAI LENGKAP' => 'background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;',
                    'DALAM PROSES', 'SEDANG BERJALAN' => 'background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a;',
                    'SIAP DILAKSANAKAN' => 'background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe;',
                    default => 'background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;',
                };
                $curIcon = match($curStatus) {
                    'SELESAI', 'SELESAI LENGKAP' => 'bi-check-circle-fill',
                    'DALAM PROSES', 'SEDANG BERJALAN' => 'bi-hourglass-split',
                    'SIAP DILAKSANAKAN' => 'bi-clock-history',
                    default => 'bi-tag-fill',
                };
            @endphp
            <span class="badge px-3 py-2 rounded-pill shadow-sm fw-bold fs-6" style="{{ $badgeStyle }}">
                <i class="bi {{ $curIcon }} me-1"></i> {{ $assignment->status_assignment ?? 'SIAP DILAKSANAKAN' }}
            </span>

            @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'kabag_ra', 'kadiv_skai']))
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle rounded-pill px-3 shadow-sm bg-white fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 13.5px;">
                    <i class="bi bi-pencil-square me-1"></i> Ubah Status
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-2" style="font-size: 13.5px;">
                    <li>
                        <form action="{{ route('surprise_audit.status.update', $assignment->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status_assignment" value="SIAP DILAKSANAKAN">
                            <button type="submit" class="dropdown-item py-2 rounded-2"><span class="badge bg-primary me-2">&bull;</span> SIAP DILAKSANAKAN</button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('surprise_audit.status.update', $assignment->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status_assignment" value="DALAM PROSES">
                            <button type="submit" class="dropdown-item py-2 rounded-2"><span class="badge bg-warning text-dark me-2">&bull;</span> DALAM PROSES</button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('surprise_audit.status.update', $assignment->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status_assignment" value="SELESAI">
                            <button type="submit" class="dropdown-item py-2 rounded-2"><span class="badge bg-success me-2">&bull;</span> SELESAI</button>
                        </form>
                    </li>
                </ul>
            </div>
            @endif
        </div>
    </div>

    <!-- Alert Sukses / Error -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4 border-0 p-3" role="alert" style="background-color: #f0fdf4; color: #166534; border-left: 4px solid #22c55e !important;">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
            <div>
                <h6 class="mb-0 fw-bold text-success">Berhasil</h6>
                <div style="font-size: 13.5px;">{{ session('success') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4 border-0 p-3" role="alert" style="background-color: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444 !important;">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
            <div>
                <h6 class="mb-0 fw-bold text-danger">Perhatian</h6>
                <div style="font-size: 13.5px;">{{ session('error') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Banner Catatan Review Admin / Reviewer SKAI -->
    @if($assignment->status_review_admin === 'PERLU PERBAIKAN')
    <div class="card border-0 shadow-sm rounded-3 mb-4" style="background-color: #fffbeb; border-left: 5px solid #f59e0b !important;">
        <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; min-width: 44px; background: #fef3c7; color: #b45309; font-size: 1.3rem;">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div>
                <h6 class="mb-1 fw-bold" style="color: #92400e;">KKA Fisik Memerlukan Perbaikan (Catatan Reject Reviewer SKAI):</h6>
                <div style="font-size: 13.5px; color: #78350f;">{{ $assignment->catatan_review_admin ?? 'Harap lengkapi dan sesuaikan kembali lembar KKA yang dikerjakan.' }}</div>
            </div>
        </div>
    </div>
    @elseif($assignment->status_review_admin === 'DISETUJUI')
    <div class="card border-0 shadow-sm rounded-3 mb-4" style="background-color: #f0fdf4; border-left: 5px solid #10b981 !important;">
        <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; min-width: 44px; background: #dcfce7; color: #15803d; font-size: 1.3rem;">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
                <div>
                    <h6 class="mb-1 fw-bold text-success">KKA Fisik Telah Disetujui (Approved) oleh Reviewer / Admin SKAI</h6>
                    <div class="small text-muted">Seluruh KKA fisik dinyatakan valid dan selesai diuji.</div>
                </div>
            </div>
            @if($assignment->nomor_surat_tugas)
                <span class="badge px-3 py-2 rounded-pill fw-bold text-white fs-7" style="background-color: #0f2e5a;">
                    No. ST: {{ $assignment->nomor_surat_tugas }}
                </span>
            @endif
        </div>
    </div>
    @endif

    <!-- CARD 1: MASTER ASSIGNMENT & RENCANA SURPRISE AUDIT (WP-RA-05-A) -->
    <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important; overflow: hidden;">
        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); color: #ffffff;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-text text-info fs-5"></i>
                <h5 class="mb-0 fw-bold text-white" style="font-size: 15px;">WP-RA-05-A | Informasi Penugasan Surprise Audit</h5>
            </div>
            <span class="badge rounded-pill px-3 py-1" style="background-color: rgba(255, 255, 255, 0.2); color: #fff; font-size: 12px; font-weight: 600;">Master Assignment</span>
        </div>
        <div class="card-body p-4" style="font-size: 14px;">
            <div class="row g-4">
                <div class="col-md-6 border-end-md">
                    <table class="table table-borderless table-sm mb-0">
                        <tr class="border-bottom">
                            <td class="text-muted fw-semibold py-2" style="width: 40%;">Visit ID / Kode Penugasan</td>
                            <td class="fw-bold py-2" style="color: #0f2e5a;">{{ $assignment->visit_id }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted fw-semibold py-2">Nomor Assignment</td>
                            <td class="fw-semibold text-dark py-2">{{ $assignment->nomor_assignment ?? '-' }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted fw-semibold py-2">Tanggal / Jam Pelaksanaan</td>
                            <td class="fw-semibold text-dark py-2">
                                {{ $assignment->tanggal_rencana ? date('d F Y', strtotime($assignment->tanggal_rencana)) : '-' }}
                                @if($assignment->tanggal_hari)
                                    <span class="badge bg-light text-muted border ms-1">{{ $assignment->tanggal_hari }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted fw-semibold py-2">Reviewer SKAI</td>
                            <td class="fw-semibold text-dark py-2">{{ $assignment->reviewer ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold py-2">Klasifikasi Kerahasiaan</td>
                            <td class="py-2"><span class="badge px-2 py-1 rounded-pill" style="background-color: #fee2e2; color: #991b1b; font-size: 11.5px; font-weight: 600;">{{ $assignment->klasifikasi_kerahasiaan ?? 'Rahasia - Need to Know' }}</span></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless table-sm mb-0">
                        <tr class="border-bottom">
                            <td class="text-muted fw-semibold py-2" style="width: 40%;">Kantor Cabang / Unit</td>
                            <td class="fw-bold text-dark py-2">[{{ $assignment->kode_unit ?? '-' }}] {{ $assignment->nama_unit ?? '-' }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted fw-semibold py-2">Resident Auditor (RA)</td>
                            <td class="fw-semibold text-dark py-2">{{ $assignment->resident_auditor ?? '-' }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted fw-semibold py-2">Dasar Penugasan</td>
                            <td class="fw-semibold text-dark py-2">{{ $assignment->dasar_penugasan ?? 'Audit Fisik Mendadak Rutin' }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted fw-semibold py-2">Pemberi Tugas</td>
                            <td class="fw-semibold text-dark py-2">{{ $assignment->pemberi_tugas ?? 'Kepala Divisi SKAI' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold py-2">Tujuan Audit</td>
                            <td class="text-muted py-2" style="font-size: 13.5px; line-height: 1.4;">{{ $assignment->tujuan_audit ?? 'Memastikan kesesuaian fisik kas khasanah, kas ATM, kartu ATM, dan barang berharga secara mendadak terhadap saldo pembukuan.' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Quick Action Links & Berkas Sample Data Excel -->
            <div class="mt-4 pt-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    @if($assignment->sample_data_file)
                        <a href="{{ asset('storage/' . $assignment->sample_data_file) }}" target="_blank" 
                           class="btn btn-outline-success rounded-pill px-3 shadow-sm fw-semibold" style="font-size: 13px;">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Unduh Berkas Sample Awal (Excel)
                        </a>
                    @else
                        <span class="text-muted small" style="font-size: 12.5px;"><i class="bi bi-info-circle me-1"></i> Tidak ada file lampiran sample Excel pada penugasan ini.</span>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('surprise_audit.kas.berita_acara', $assignment->visit_id) }}" target="_blank" 
                       class="btn rounded-pill px-3 py-1 shadow-sm fw-semibold" style="background-color: #f1f5f9; color: #0f2e5a; border: 1px solid #cbd5e1; font-size: 13px;">
                        <i class="bi bi-printer-fill me-1 text-primary"></i> Cetak Berita Acara Kas
                    </a>
                    <a href="{{ route('surprise_audit.kartu.berita_acara', $assignment->visit_id) }}" target="_blank" 
                       class="btn rounded-pill px-3 py-1 shadow-sm fw-semibold" style="background-color: #f1f5f9; color: #0f2e5a; border: 1px solid #cbd5e1; font-size: 13px;">
                        <i class="bi bi-printer-fill me-1 text-primary"></i> Cetak Berita Acara Kartu ATM
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- CARD 2: KONTROL PELAKSANAAN PER AREA PEMERIKSAAN FISIK (8 KKA MASTER SOP 05) -->
    <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important; overflow: hidden;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px; background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); color: #ffffff; font-size: 1.25rem;">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold" style="color: #0f2e5a; font-size: 16px; letter-spacing: -0.2px;">
                        Area Pemeriksaan Fisik & Lembar KKA (WP-RA-05-B)
                    </h5>
                    <small class="text-muted" style="font-size: 13px;">
                        8 Objek Pengujian Fisik Mendadak &bull; Pilih area untuk mengisi atau meninjau lembar kerja audit (KKA)
                    </small>
                </div>
            </div>

            <!-- View Switcher Pills (Grid Kartu vs Tabel Matriks) -->
            <div class="btn-group p-1 rounded-pill" style="background-color: #f1f5f9; border: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold active-view-btn shadow-sm" id="btnToggleGrid" onclick="switchKkaView('grid')" style="font-size: 12.5px;">
                    <i class="bi bi-grid-fill me-1"></i> Kartu KKA (8 Area)
                </button>
                <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold text-muted" id="btnToggleTable" onclick="switchKkaView('table')" style="font-size: 12.5px;">
                    <i class="bi bi-table me-1"></i> Tabel Rekapitulasi
                </button>
            </div>
        </div>

        @php
            $totalObjek = 0;
            $totalException = 0;
            $areaDalamLingkupCount = 0;
            $kkaSelesaiCount = 0;
            $adaBelumLengkap = false;

            $controlData = $controlData ?? [];

            // Metadata visual untuk 8 Area KKA Fisik (Warna Eksekutif Lembut & Representatif)
            $areaMeta = [
                'Kas Khasanah' => [
                    'code' => 'WP-RA-05-C',
                    'icon' => 'bi-cash-stack',
                    'color' => '#059669',
                    'bg' => '#ecfdf5',
                    'border' => '#a7f3d0',
                    'desc' => 'Uji fisik uang layak edar & kas teller vs saldo CBS Olibs',
                    'route' => route('surprise_audit.kas', $assignment->visit_id ?? $assignment->id),
                ],
                'Kas ATM' => [
                    'code' => 'WP-RA-05-D',
                    'icon' => 'bi-cpu-fill',
                    'color' => '#0891b2',
                    'bg' => '#ecfeff',
                    'border' => '#a5f3fc',
                    'desc' => 'Hitung fisik 4 kaset ATM & pencocokan saldo real-time',
                    'route' => route('surprise_audit.atm', $assignment->visit_id ?? $assignment->id),
                ],
                'Kartu ATM/PIN' => [
                    'code' => 'WP-RA-05-E',
                    'icon' => 'bi-credit-card-2-front-fill',
                    'color' => '#4f46e5',
                    'bg' => '#eef2ff',
                    'border' => '#c7d2fe',
                    'desc' => 'Rekonsiliasi kartu ATM & PIN Mailer vs saldo persediaan GL',
                    'route' => route('surprise_audit.kartu', $assignment->visit_id ?? $assignment->id),
                ],
                'Persediaan Barang Cetakan' => [
                    'code' => 'WP-RA-05-F',
                    'icon' => 'bi-journal-bookmark-fill',
                    'color' => '#d97706',
                    'bg' => '#fffbeb',
                    'border' => '#fde68a',
                    'desc' => 'Opname fisik slip, bilyet & buku tabungan di khasanah',
                    'route' => route('surprise_audit.barang_cetakan', $assignment->visit_id ?? $assignment->id),
                ],
                'Dokumen Berharga' => [
                    'code' => 'WP-RA-05-G',
                    'icon' => 'bi-file-earmark-check-fill',
                    'color' => '#0d9488',
                    'bg' => '#f0fdfa',
                    'border' => '#99f6e4',
                    'desc' => 'Verifikasi nomor seri fisik Cek, BG, dan Bilyet Deposito',
                    'route' => route('surprise_audit.dokumen_berharga', $assignment->visit_id ?? $assignment->id),
                ],
                'Dokumen Agunan' => [
                    'code' => 'WP-RA-05-H',
                    'icon' => 'bi-safe2-fill',
                    'color' => '#9333ea',
                    'bg' => '#faf5ff',
                    'border' => '#e9d5ff',
                    'desc' => 'Custody, keaslian sertifikat (SHM/BPKB) & masa berlaku',
                    'route' => route('surprise_audit.agunan', $assignment->visit_id ?? $assignment->id),
                ],
                'Aset' => [
                    'code' => 'WP-RA-05-I',
                    'icon' => 'bi-laptop-fill',
                    'color' => '#334155',
                    'bg' => '#f1f5f9',
                    'border' => '#cbd5e1',
                    'desc' => 'Inventarisasi fisik peralatan kantor, kondisi & stiker barcode',
                    'route' => route('surprise_audit.aset', $assignment->visit_id ?? $assignment->id),
                ],
                'Lingkungan' => [
                    'code' => 'WP-RA-05-J',
                    'icon' => 'bi-camera-video-fill',
                    'color' => '#e11d48',
                    'bg' => '#fff1f2',
                    'border' => '#fecdd3',
                    'desc' => 'Pemeriksaan kebersihan, keamanan & playback rekaman CCTV',
                    'route' => route('surprise_audit.lingkungan', $assignment->visit_id ?? $assignment->id),
                ],
            ];

            foreach($controlData as $row) {
                $totalObjek += $row['objek_diuji'];
                $totalException += $row['exception'];
                if($row['dalam_lingkup'] == 'Ya') $areaDalamLingkupCount++;
                if(in_array($row['status_area'], ['SIAP DIREVIU', 'ADA EXCEPTION - SIAP DIREVIU', 'KKA SELESAI'])) $kkaSelesaiCount++;
                if(str_contains($row['status_area'], 'BELUM') || $row['status_area'] == 'KKA BELUM SELESAI') $adaBelumLengkap = true;
            }
        @endphp

        <!-- TAMPILAN 1: GRID KARTU VISUAL KKA (DEFAULT - ELEGAN, NYAMAN DI MATA) -->
        <div id="kkaGridView" class="p-4" style="background-color: #f8fafc;">
            <div class="row g-3">
                @foreach($controlData as $row)
                    @php
                        $meta = $areaMeta[$row['area']] ?? [
                            'code' => 'WP-RA-05',
                            'icon' => 'bi-folder2-open',
                            'color' => '#0f2e5a',
                            'bg' => '#eff6ff',
                            'border' => '#bfdbfe',
                            'desc' => 'Pemeriksaan fisik sesuai standar Bank Sulteng',
                            'route' => '#',
                        ];
                        $isInScope = ($row['dalam_lingkup'] === 'Ya');
                        $isCompleted = in_array($row['status_area'], ['KKA SELESAI', 'SIAP DIREVIU', 'ADA EXCEPTION - SIAP DIREVIU']);
                    @endphp
                    <div class="col-md-6 col-xl-3">
                        <div class="card h-100 border-0 shadow-sm rounded-4 kka-card-hover" style="background: #ffffff; border: 1px solid #e2e8f0 !important; overflow: hidden; display: flex; flex-direction: column;">
                            <!-- Header Card: Tag KKA & Status Lingkup -->
                            <div class="p-3 pb-2 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #f1f5f9;">
                                <span class="badge px-2 py-1 rounded-pill fw-bold" style="background-color: #f1f5f9; color: #334155; font-size: 11px; letter-spacing: 0.3px;">
                                    {{ $meta['code'] }}
                                </span>
                                @if($isInScope)
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold" style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 11px;">
                                        <i class="bi bi-check-circle-fill me-1"></i>Dalam Lingkup
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-2 py-1 fw-normal" style="background-color: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; font-size: 11px;">
                                        Di Luar Lingkup
                                    </span>
                                @endif
                            </div>

                            <!-- Body Card: Icon, Judul, Deskripsi & Metrik -->
                            <div class="p-3 flex-grow-1 d-flex flex-direction-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                                             style="width: 44px; height: 44px; background-color: {{ $meta['bg'] }}; color: {{ $meta['color'] }}; border: 1px solid {{ $meta['border'] }}; font-size: 1.35rem;">
                                            <i class="bi {{ $meta['icon'] }}"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0" style="color: #0f2e5a; font-size: 14.5px; line-height: 1.25;">
                                                {{ $row['area'] }}
                                            </h6>
                                            <small class="text-muted d-block" style="font-size: 11.5px; margin-top: 2px;">
                                                {{ $row['status_kka'] }}
                                            </small>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45; min-height: 35px;">
                                        {{ $meta['desc'] }}
                                    </p>
                                </div>

                                <!-- Box Metrik Status Objek & Temuan -->
                                <div class="rounded-3 p-2 mb-3" style="background-color: #f8fafc; border: 1px solid #f1f5f9;">
                                    <div class="row g-1 text-center align-items-center">
                                        <div class="col-6 border-end" style="border-color: #e2e8f0 !important;">
                                            <small class="text-muted d-block" style="font-size: 10.5px; text-transform: uppercase; font-weight: 600;">Objek Diuji</small>
                                            <span class="fw-bold" style="color: #0f2e5a; font-size: 13.5px;">{{ $row['objek_diuji'] }} Item</span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block" style="font-size: 10.5px; text-transform: uppercase; font-weight: 600;">Exception</small>
                                            @if($row['exception'] > 0)
                                                <span class="badge rounded-pill px-2 py-0 fw-bold" style="background-color: #fee2e2; color: #991b1b; font-size: 11px;">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $row['exception'] }} Selisih
                                                </span>
                                            @else
                                                <span class="badge rounded-pill px-2 py-0 fw-semibold" style="background-color: #f0fdf4; color: #166534; font-size: 11px;">
                                                    <i class="bi bi-check-circle me-1"></i>Nihil (0)
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Card: Tombol Akses KKA -->
                            <div class="p-3 pt-0">
                                @if($isInScope)
                                    <a href="{{ $meta['route'] }}" class="btn w-100 rounded-pill py-2 fw-semibold text-white shadow-sm d-flex align-items-center justify-content-center gap-2" 
                                       style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); font-size: 13px;">
                                        <i class="bi bi-pencil-square"></i>
                                        <span>Buka &amp; Isi KKA Fisik</span>
                                    </a>
                                @else
                                    <button class="btn w-100 rounded-pill py-2 fw-semibold text-muted d-flex align-items-center justify-content-center gap-1" 
                                            style="background-color: #f1f5f9; border: 1px dashed #cbd5e1; font-size: 12.5px;" disabled>
                                        <i class="bi bi-dash-circle"></i> Tidak Diuji
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- TAMPILAN 2: TABEL REKAPITULASI MATRIKS (BISA DITAMPILKAN VIA SWITCHER) -->
        <div id="kkaTableView" style="display: none;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center" style="font-size: 13.5px;">
                    <thead style="background-color: #f1f5f9; color: #1e293b; font-weight: 700; font-size: 12.5px;" class="text-uppercase">
                        <tr>
                            <th class="py-3 text-start ps-4" style="width: 250px;">Area Pemeriksaan Fisik</th>
                            <th class="py-3" style="width: 120px;">Kode KKA</th>
                            <th class="py-3" style="width: 130px;">Dalam Lingkup?</th>
                            <th class="py-3" style="width: 130px;">Status KKA</th>
                            <th class="py-3" style="width: 110px;">Objek Diuji</th>
                            <th class="py-3" style="width: 130px;">Exception (Selisih)</th>
                            <th class="py-3" style="width: 130px;">Klasifikasi</th>
                            <th class="py-3 pe-4 text-center" style="width: 160px;">Aksi Lembar KKA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($controlData as $row)
                            @php
                                $meta = $areaMeta[$row['area']] ?? [
                                    'code' => 'WP-RA-05',
                                    'icon' => 'bi-folder2-open',
                                    'color' => '#0f2e5a',
                                    'bg' => '#eff6ff',
                                    'border' => '#bfdbfe',
                                    'route' => '#',
                                ];
                                $isInScope = ($row['dalam_lingkup'] === 'Ya');
                            @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td class="text-start ps-4 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                                             style="width: 32px; height: 32px; background-color: {{ $meta['bg'] }}; color: {{ $meta['color'] }}; font-size: 1.05rem;">
                                            <i class="bi {{ $meta['icon'] }}"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold" style="color: #0f2e5a; font-size: 13.5px;">{{ $row['area'] }}</div>
                                            <small class="text-muted" style="font-size: 11.5px;">{{ $meta['code'] }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="badge px-2 py-1 rounded-pill" style="background-color: #f1f5f9; color: #334155; font-size: 11.5px; font-weight: 600;">
                                        {{ $meta['code'] }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span class="badge px-3 py-1 rounded-pill fw-semibold" style="{{ $isInScope ? 'background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;' : 'background-color: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;' }} font-size: 11.5px;">
                                        {{ $row['dalam_lingkup'] }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span class="badge px-2 py-1 rounded-pill fw-semibold" style="{{ $row['status_kka'] === 'LENGKAP' ? 'background-color: #f0fdf4; color: #166534;' : ($row['status_kka'] === 'TIDAK DALAM LINGKUP' ? 'background-color: #f1f5f9; color: #64748b;' : 'background-color: #fffbeb; color: #b45309;') }} font-size: 11.5px;">
                                        {{ $row['status_kka'] }}
                                    </span>
                                </td>
                                <td class="py-3 fw-bold" style="color: #0f2e5a; font-size: 14px;">
                                    {{ $row['objek_diuji'] }}
                                </td>
                                <td class="py-3">
                                    @if($row['exception'] > 0)
                                        <span class="badge rounded-pill px-2 py-1 fw-bold" style="background-color: #fee2e2; color: #991b1b; font-size: 11.5px;">
                                            {{ $row['exception'] }} Selisih
                                        </span>
                                    @else
                                        <span class="badge rounded-pill px-2 py-1 fw-semibold" style="background-color: #f0fdf4; color: #166534; font-size: 11.5px;">
                                            Nihil
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <span class="badge px-2 py-1 rounded-pill fw-semibold" style="{{ $row['klasifikasi_hasil'] === 'Temuan' ? 'background-color: #fee2e2; color: #991b1b;' : 'background-color: #f0fdf4; color: #166534;' }} font-size: 11px;">
                                        {{ $row['klasifikasi_hasil'] }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center py-3">
                                    @if($isInScope)
                                        <a href="{{ $meta['route'] }}" class="btn btn-sm rounded-pill px-3 py-1 text-white shadow-sm fw-semibold" 
                                           style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); font-size: 12.5px;">
                                            <i class="bi bi-pencil-square me-1"></i> Buka KKA
                                        </a>
                                    @else
                                        <span class="badge rounded-pill px-2 py-1" style="background-color: #f1f5f9; color: #94a3b8; font-size: 11px;">
                                            Di Luar Lingkup
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SUMMARY BAR: Ringkasan Metrik Eksekutif (Soft Executive Cards) -->
    <div class="row g-3 mb-4">
        <div class="col-md">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white" style="border: 1px solid #e2e8f0 !important; border-top: 3px solid #3b82f6 !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Area Dalam Lingkup</small>
                <div class="fw-bold mt-1" style="font-size: 1.4rem; color: #1e293b;">
                    {{ $areaDalamLingkupCount }} <span class="text-muted" style="font-size: 0.85rem; font-weight: normal;">dari 8 Area</span>
                </div>
                <div class="small text-muted mt-1" style="font-size: 12px;">Objek yang diuji fisik</div>
            </div>
        </div>
        <div class="col-md">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white" style="border: 1px solid #e2e8f0 !important; border-top: 3px solid #10b981 !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">KKA Selesai Diisi</small>
                <div class="fw-bold mt-1 text-success" style="font-size: 1.4rem;">
                    {{ $kkaSelesaiCount }} <span class="text-muted" style="font-size: 0.85rem; font-weight: normal;">KKA</span>
                </div>
                <div class="small text-muted mt-1" style="font-size: 12px;">Lembar kerja terverifikasi</div>
            </div>
        </div>
        <div class="col-md">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white" style="border: 1px solid #e2e8f0 !important; border-top: 3px solid #0284c7 !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Total Objek Diuji</small>
                <div class="fw-bold mt-1" style="font-size: 1.4rem; color: #0f2e5a;">
                    {{ $totalObjek }} <span class="text-muted" style="font-size: 0.85rem; font-weight: normal;">Sampel/Item</span>
                </div>
                <div class="small text-muted mt-1" style="font-size: 12px;">Akumulasi populasi fisik</div>
            </div>
        </div>
        <div class="col-md">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white" style="border: 1px solid #e2e8f0 !important; border-top: 3px solid {{ $totalException > 0 ? '#ef4444' : '#10b981' }} !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Total Exception (Selisih)</small>
                <div class="fw-bold mt-1 {{ $totalException > 0 ? 'text-danger' : 'text-success' }}" style="font-size: 1.4rem;">
                    {{ $totalException }} <span class="text-muted" style="font-size: 0.85rem; font-weight: normal;">Temuan</span>
                </div>
                <div class="small mt-1">
                    @if($totalException == 0)
                        <span class="badge rounded-pill px-2 py-0" style="background-color: #f0fdf4; color: #166534; font-size: 11px;">NIHIL / COCOK</span>
                    @else
                        <span class="badge rounded-pill px-2 py-0" style="background-color: #fee2e2; color: #991b1b; font-size: 11px;">TERDAPAT SELISIH</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white" style="border: 1px solid #e2e8f0 !important; border-top: 3px solid {{ $adaBelumLengkap ? '#f59e0b' : '#10b981' }} !important;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Status Paket Penugasan</small>
                <div class="mt-2">
                    <span class="badge px-3 py-2 rounded-pill fw-bold" style="{{ $adaBelumLengkap ? 'background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a;' : 'background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;' }} font-size: 12.5px;">
                        {{ $adaBelumLengkap ? 'BELUM LENGKAP' : 'SELESAI LENGKAP' }}
                    </span>
                </div>
                <div class="small text-muted mt-2" style="font-size: 12px;">Keseluruhan 8 KKA fisik</div>
            </div>
        </div>
    </div>

    <!-- CARD 3: PANEL VERIFIKASI & APPROVAL ADMIN SKAI (Khusus Admin / Pimsie / Reviewer) -->
    @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'kabag_ra', 'kadiv_skai']))
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white" style="border: 1px solid #fde68a !important; border-left: 5px solid #f59e0b !important; overflow: hidden;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-warning fs-5"></i>
                <h5 class="mb-0 fw-bold" style="color: #0f2e5a; font-size: 15px;">Panel Approval &amp; Verifikasi KKA Fisik (Khusus Admin / Reviewer)</h5>
            </div>
            <span class="badge px-3 py-1 rounded-pill" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 12px;">
                Mode Otorisasi
            </span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('surprise_audit.admin_review', $assignment->id) }}" method="POST">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-dark mb-1" style="font-size: 13px;">
                            Nomor Surat Tugas Resmi <span class="badge rounded-pill px-2 py-0 ms-1" style="background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 11px;">Wajib Diisi</span>
                        </label>
                        <input type="text" name="nomor_surat_tugas" class="form-control" style="height: 42px; font-size: 13.5px; border-color: #cbd5e1;" 
                               placeholder="Contoh: ST-RA/SA/{{ date('Ym') }}/001" 
                               value="{{ old('nomor_surat_tugas', $assignment->nomor_surat_tugas ?: $assignment->nomor_assignment) }}">
                        <div class="text-muted mt-1" style="font-size: 12px;">Nomor surat tugas resmi dari Divisi SKAI.</div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold text-dark mb-1" style="font-size: 13px;">Catatan Review / Arahan Perbaikan:</label>
                        <input type="text" name="catatan_review_admin" class="form-control" style="height: 42px; font-size: 13.5px; border-color: #cbd5e1;" 
                               placeholder="Tuliskan catatan persetujuan atau poin revisi..." 
                               value="{{ old('catatan_review_admin', $assignment->catatan_review_admin) }}">
                        <div class="text-muted mt-1" style="font-size: 12px;">Wajib dicantumkan apabila status ditolak (Reject).</div>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" name="action" value="approve" class="btn btn-success fw-semibold flex-fill rounded-pill shadow-sm" style="height: 42px; font-size: 13.5px;">
                            <i class="bi bi-check-lg me-1"></i> Approve
                        </button>
                        <button type="submit" name="action" value="reject" class="btn btn-outline-danger fw-semibold flex-fill rounded-pill" style="height: 42px; font-size: 13.5px;">
                            <i class="bi bi-x-lg me-1"></i> Reject
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- CARD 4: REKAPITULASI BERKAS BUKTI & SAMPLE DATA YANG DIUNGGAH RA (PUSAT PENGECEKAN ADMIN - HANYA SATU BLOK TUNGGAL) -->
    <div class="card shadow-sm border-0 rounded-3 mb-5 bg-white" style="border: 1px solid #e2e8f0 !important; overflow: hidden;">
        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); color: #ffffff;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-folder-check text-info fs-5"></i>
                <h5 class="mb-0 fw-bold text-white" style="font-size: 15px;">Berkas Bukti Fisik &amp; Sample Data yang Diunggah RA</h5>
            </div>
            <span class="badge rounded-pill px-3 py-1" style="background-color: rgba(255, 255, 255, 0.2); color: #fff; font-size: 12px; font-weight: 600;">
                {{ count($uploadedBukti ?? []) }} Dokumen Terunggah
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center" style="font-size: 13px;">
                    <thead style="background-color: #f1f5f9; color: #1e293b; font-weight: 700; font-size: 12px;" class="text-uppercase">
                        <tr>
                            <th class="py-3 ps-4 text-start" style="width: 170px;">Area / Modul</th>
                            <th class="py-3 text-start" style="width: 180px;">Objek Pemeriksaan</th>
                            <th class="py-3 text-start">Nama Dokumen / Berkas Bukti</th>
                            <th class="py-3" style="width: 160px;">Status Tindak Lanjut</th>
                            <th class="py-3 text-start">Klarifikasi / Catatan RA</th>
                            <th class="py-3 pe-4 text-center" style="width: 170px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($uploadedBukti ?? [] as $bukti)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td class="ps-4 text-start py-3">
                                <span class="badge px-3 py-1 rounded-pill" style="background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-size: 11.5px; font-weight: 600;">
                                    {{ $bukti['area'] }}
                                </span>
                            </td>
                            <td class="text-start fw-bold py-3" style="color: #0f2e5a; font-size: 13.5px;">
                                {{ $bukti['objek'] }}
                            </td>
                            <td class="text-start py-3">
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $fName = $bukti['file_name'] ?? '';
                                        $iconClass = str_contains($fName, '.pdf') ? 'text-danger bi-file-earmark-pdf-fill' : (str_contains($fName, '.xls') ? 'text-success bi-file-earmark-spreadsheet-fill' : 'text-primary bi-file-earmark-image-fill');
                                    @endphp
                                    <i class="bi {{ $iconClass }} fs-5"></i>
                                    <span class="fw-semibold text-dark">{{ $fName }}</span>
                                </div>
                            </td>
                            <td class="py-3">
                                @php
                                    $stTl = $bukti['status_tl'] ?? 'SELESAI';
                                    $tlStyle = match($stTl) {
                                        'SUDAH SELESAI' => 'background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;',
                                        'DALAM PROSES' => 'background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a;',
                                        default => 'background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;',
                                    };
                                @endphp
                                <span class="badge px-3 py-1 rounded-pill fw-semibold" style="{{ $tlStyle }} font-size: 11.5px;">
                                    {{ $stTl }}
                                </span>
                                @if(!empty($bukti['komitmen_tanggal']))
                                    <div class="text-muted mt-1 small" style="font-size: 11px;">
                                        Target: {{ date('d/m/Y', strtotime($bukti['komitmen_tanggal'])) }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-start text-muted py-3 text-truncate" style="max-width: 280px; font-size: 12.5px;" title="{{ $bukti['klarifikasi'] }}">
                                {{ $bukti['klarifikasi'] ?? '-' }}
                            </td>
                            <td class="pe-4 text-center py-3">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('surprise_audit.view_bukti', ['type' => $bukti['type'], 'id' => $bukti['id']]) }}" target="_blank" 
                                       class="btn btn-sm rounded-pill px-3 py-1 shadow-sm fw-semibold text-white" 
                                       style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); font-size: 12px;" title="Lihat Dokumen">
                                        <i class="bi bi-eye me-1"></i> Lihat Bukti
                                    </a>
                                    <a href="{{ route('surprise_audit.view_bukti', ['type' => $bukti['type'], 'id' => $bukti['id'], 'download' => 1]) }}" 
                                       class="btn btn-sm rounded-pill px-2 py-1 shadow-sm" style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 12px;" title="Unduh Berkas Asli">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <h6 class="fw-bold text-secondary mb-1">Belum ada berkas bukti fisik atau sample yang diunggah oleh Resident Auditor.</h6>
                                <p class="small text-muted mb-0">Berkas bukti yang diunggah pada saat KKA fisik akan secara otomatis terkumpul pada tabel ini.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function switchKkaView(mode) {
    const gridEl = document.getElementById('kkaGridView');
    const tableEl = document.getElementById('kkaTableView');
    const btnGrid = document.getElementById('btnToggleGrid');
    const btnTable = document.getElementById('btnToggleTable');
    
    if (mode === 'grid') {
        gridEl.style.display = 'block';
        tableEl.style.display = 'none';
        btnGrid.classList.add('active-view-btn', 'shadow-sm');
        btnGrid.classList.remove('text-muted');
        btnTable.classList.remove('active-view-btn', 'shadow-sm');
        btnTable.classList.add('text-muted');
    } else {
        gridEl.style.display = 'none';
        tableEl.style.display = 'block';
        btnTable.classList.add('active-view-btn', 'shadow-sm');
        btnTable.classList.remove('text-muted');
        btnGrid.classList.remove('active-view-btn', 'shadow-sm');
        btnGrid.classList.add('text-muted');
    }
}
</script>
@endsection
