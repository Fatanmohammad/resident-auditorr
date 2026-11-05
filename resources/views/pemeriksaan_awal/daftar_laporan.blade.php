@extends('layouts.app')

@section('title', 'Daftar Laporan Indikasi Fraud (SOP 09)')

@section('content')
<div class="container-fluid px-4 py-3" style="max-width: 1440px; margin: 0 auto;">
    <!-- Header Page: Elegan, Tenang & Mudah Dibaca -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge px-3 py-2 fs-7 fw-bold" style="background-color: #e0e7ff; color: #3730a3; border-radius: 6px;">
                    <i class="bi bi-shield-check me-1"></i> STANDAR SOP 09
                </span>
                <h3 class="mb-0 fw-bold" style="color: #0f2e5a; letter-spacing: -0.3px;">Daftar Laporan Indikasi Fraud</h3>
            </div>
            <p class="text-secondary mb-0" style="font-size: 0.95rem;">
                Rekapitulasi seluruh laporan kasus fraud, status triase, unduh lembar register resmi, dan akses dokumen/foto barang bukti.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pemeriksaan_awal.pelaporan_awal') }}" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" style="background: #0f2e5a; border-color: #0f2e5a; font-size: 0.92rem;">
                <i class="bi bi-plus-circle me-1"></i> Buat Pelaporan Baru
            </a>
            <a href="{{ route('pemeriksaan_awal.status_penanganan') }}" class="btn btn-outline-primary rounded-pill px-3 shadow-sm fw-semibold" style="font-size: 0.92rem;">
                <i class="bi bi-diagram-3-fill me-1"></i> Status Penanganan &amp; Memo
            </a>
        </div>
    </div>

    @include('pemeriksaan_awal.partials.nav_tabs')

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4 p-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
            <div>
                <h6 class="mb-0 fw-bold text-success">Berhasil</h6>
                <div style="font-size: 0.92rem;">{{ session('success') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4 p-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
            <div>
                <h6 class="mb-0 fw-bold text-danger">Perhatian</h6>
                <div style="font-size: 0.92rem;">{{ session('error') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Cards Summary Metrik: Warna Lembut, Nyaman di Mata -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total Register -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary fw-bold text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">TOTAL REGISTER</div>
                        <h3 class="fw-bold mb-0 mt-1" style="color: #0f2e5a;">{{ $stats['total'] }}</h3>
                        <div class="text-muted small mt-1">Seluruh kasus tercatat</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 48px; height: 48px; background: #eff6ff; color: #1d4ed8; font-size: 1.4rem;">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Insidentil vs Non-Insidentil -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary fw-bold text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">TIPE INSIDENTIL</div>
                        <h3 class="fw-bold mb-0 mt-1" style="color: #b45309;">{{ $stats['insidentil'] }}</h3>
                        <div class="text-muted small mt-1">{{ $stats['non_insidentil'] }} Non-Insidentil</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 48px; height: 48px; background: #fffbeb; color: #d97706; font-size: 1.4rem;">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Dalam Investigasi -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary fw-bold text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">DALAM INVESTIGASI</div>
                        <h3 class="fw-bold mb-0 mt-1" style="color: #be123c;">{{ $stats['investigasi'] }}</h3>
                        <div class="text-muted small mt-1">Prioritas khusus SKAI</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 48px; height: 48px; background: #fff1f2; color: #be123c; font-size: 1.4rem;">
                        <i class="bi bi-search"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Total Potensi Fraud -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary fw-bold text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">POTENSI KERUGIAN</div>
                        <h4 class="fw-bold mb-0 mt-1 text-success" style="font-size: 1.15rem;">Rp {{ number_format($stats['total_nominal'], 0, ',', '.') }}</h4>
                        <div class="text-muted small mt-1">Total nilai terindikasi</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 48px; height: 48px; background: #f0fdf4; color: #15803d; font-size: 1.4rem;">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian: Nyaman, Font Besar & Jelas -->
    <div class="card shadow-sm border-0 rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-3">
            <form action="{{ route('pemeriksaan_awal.daftar_laporan') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1">Cari Kata Kunci:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="Cari ID, dugaan, fakta, cabang..." 
                               value="{{ request('q') }}" style="font-size: 0.92rem; height: 40px;">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1">Kantor Cabang:</label>
                    <select name="unit" class="form-select" style="font-size: 0.92rem; height: 40px;">
                        <option value="">Semua Cabang</option>
                        @foreach($units as $u)
                            <option value="{{ $u->unit_code }}" {{ request('unit') == $u->unit_code ? 'selected' : '' }}>
                                {{ $u->unit_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1">Tipe Kasus:</label>
                    <select name="tipe_kasus" class="form-select" style="font-size: 0.92rem; height: 40px;">
                        <option value="">Semua Tipe</option>
                        <option value="INSIDENTIL" {{ request('tipe_kasus') == 'INSIDENTIL' ? 'selected' : '' }}>Insidentil</option>
                        <option value="NON_INSIDENTIL" {{ request('tipe_kasus') == 'NON_INSIDENTIL' ? 'selected' : '' }}>Non-Insidentil</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1">Pihak Terlibat:</label>
                    <select name="pihak_terlibat" class="form-select" style="font-size: 0.92rem; height: 40px;">
                        <option value="">Semua Pihak</option>
                        <option value="INTERNAL" {{ request('pihak_terlibat') == 'INTERNAL' ? 'selected' : '' }}>Internal</option>
                        <option value="EKSTERNAL" {{ request('pihak_terlibat') == 'EKSTERNAL' ? 'selected' : '' }}>Eksternal</option>
                        <option value="INDIKASI_INTERNAL_EKSTERNAL" {{ request('pihak_terlibat') == 'INDIKASI_INTERNAL_EKSTERNAL' ? 'selected' : '' }}>Internal & Eksternal</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1">Status Kasus:</label>
                    <select name="status" class="form-select" style="font-size: 0.92rem; height: 40px;">
                        <option value="">Semua Status</option>
                        <option value="OPEN" {{ request('status') == 'OPEN' ? 'selected' : '' }}>OPEN</option>
                        <option value="INVESTIGASI" {{ request('status') == 'INVESTIGASI' ? 'selected' : '' }}>INVESTIGASI</option>
                        <option value="PEMBINAAN" {{ request('status') == 'PEMBINAAN' ? 'selected' : '' }}>PEMBINAAN</option>
                        <option value="CLOSED" {{ request('status') == 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end" style="height: 65px;">
                    <div class="d-flex gap-1 w-100">
                        <button type="submit" class="btn btn-primary w-100 fw-bold" style="background: #0f2e5a; border-color: #0f2e5a; height: 40px;" title="Terapkan Filter">
                            <i class="bi bi-funnel"></i>
                        </button>
                        @if(request()->hasAny(['q', 'unit', 'tipe_kasus', 'pihak_terlibat', 'status']))
                            <a href="{{ route('pemeriksaan_awal.daftar_laporan') }}" class="btn btn-outline-secondary" style="height: 40px;" title="Reset Filter">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Laporan: Font Jelas, Spacing Nyaman & Ramah untuk Penglihatan Senior -->
    <div class="card shadow-sm border-0 rounded-4 bg-white" style="border: 1px solid #e2e8f0 !important; overflow: hidden;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-table text-primary fs-5"></i>
                <h5 class="mb-0 fw-bold" style="color: #0f2e5a;">Data Register Kasus Fraud</h5>
            </div>
            <span class="text-secondary fw-medium" style="font-size: 0.9rem;">
                Menampilkan <strong>{{ $laporans->count() }}</strong> dari <strong>{{ $laporans->total() }}</strong> kasus
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                <thead style="background-color: #f8fafc; color: #334155;">
                    <tr>
                        <th class="py-3 px-3" style="width: 140px; font-weight: 700;">ID Kasus</th>
                        <th class="py-3" style="width: 150px; font-weight: 700;">Tanggal &amp; Unit</th>
                        <th class="py-3" style="width: 150px; font-weight: 700;">Klasifikasi</th>
                        <th class="py-3" style="font-weight: 700;">Dugaan Awal &amp; Kronologi Singkat</th>
                        <th class="py-3" style="width: 150px; font-weight: 700;">Potensi Kerugian</th>
                        <th class="py-3 text-center" style="width: 130px; font-weight: 700;">Triase &amp; Skor</th>
                        <th class="py-3 text-center" style="width: 110px; font-weight: 700;">Bukti</th>
                        <th class="py-3 text-center" style="width: 120px; font-weight: 700;">Status</th>
                        <th class="py-3 text-center" style="width: 120px; font-weight: 700;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporans as $item)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <!-- ID Kasus -->
                        <td class="py-3 px-3">
                            <span class="badge px-3 py-2 fw-bold text-start" style="background-color: #f1f5f9; color: #0f2e5a; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                                {{ $item->indikasi_id }}
                            </span>
                            @if($item->flagged_from && $item->flagged_from !== 'MANDIRI_RA')
                                <div class="mt-1">
                                    <span class="badge px-2 py-1" style="background-color: #fef3c7; color: #92400e; font-size: 0.75rem;">
                                        Flag: {{ $item->flagged_from }}
                                    </span>
                                </div>
                            @endif
                        </td>

                        <!-- Tanggal & Unit -->
                        <td class="py-3">
                            <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                {{ $item->tanggal_diterima ? date('d/m/Y', strtotime($item->tanggal_diterima)) : '-' }}
                            </div>
                            <div class="text-secondary mt-1" style="font-size: 0.85rem;">
                                <i class="bi bi-geo-alt-fill text-primary me-1"></i>{{ $item->nama_unit ?: ($item->kode_unit ?: 'Cabang') }}
                            </div>
                        </td>

                        <!-- Klasifikasi (Tipe Kasus & Pihak Terlibat) -->
                        <td class="py-3">
                            @if(($item->tipe_kasus ?: 'INSIDENTIL') === 'INSIDENTIL')
                                <span class="badge px-3 py-1 rounded-pill" style="background-color: #fef3c7; color: #92400e; font-weight: 600; font-size: 0.82rem;">
                                    Insidentil
                                </span>
                            @else
                                <span class="badge px-3 py-1 rounded-pill" style="background-color: #f1f5f9; color: #475569; font-weight: 600; font-size: 0.82rem;">
                                    Non-Insidentil
                                </span>
                            @endif

                            <div class="mt-1">
                                @if(($item->pihak_terlibat ?: 'INTERNAL') === 'INTERNAL')
                                    <span class="badge px-2 py-1" style="background-color: #fee2e2; color: #991b1b; font-size: 0.78rem;">Internal</span>
                                @elseif($item->pihak_terlibat === 'EKSTERNAL')
                                    <span class="badge px-2 py-1" style="background-color: #eff6ff; color: #1e40af; font-size: 0.78rem;">Eksternal</span>
                                @else
                                    <span class="badge px-2 py-1" style="background-color: #f3e8ff; color: #6b21a8; font-size: 0.78rem;">Kolusi Intern-Ekstern</span>
                                @endif
                            </div>
                        </td>

                        <!-- Dugaan Awal & Kronologi -->
                        <td class="py-3">
                            <div class="fw-bold text-dark text-truncate" style="max-width: 320px; font-size: 0.95rem;" title="{{ $item->dugaan_awal ?: $item->dugaan_belum_terverifikasi }}">
                                {{ $item->dugaan_awal ?: ($item->dugaan_belum_terverifikasi ?: 'Dugaan belum dirumuskan') }}
                            </div>
                            <div class="text-secondary text-truncate mt-1" style="max-width: 320px; font-size: 0.85rem;" title="{{ $item->kronologi_awal }}">
                                <i class="bi bi-clock-history me-1 text-primary"></i>{{ $item->kronologi_awal ?: ($item->fakta_temuan ?: '-') }}
                            </div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">
                                <i class="bi bi-tag-fill me-1 text-secondary opacity-50"></i>Area: {{ $item->area_kejadian ?: ($item->area ?: 'Operasional') }}
                            </div>
                        </td>

                        <!-- Potensi Kerugian -->
                        <td class="py-3">
                            <div class="fw-bold text-dark" style="font-size: 1rem;">
                                Rp {{ number_format($item->nominal_awal, 0, ',', '.') }}
                            </div>
                            <div class="text-muted small">Estimasi saldo terdampak</div>
                        </td>

                        <!-- Triase & Skor -->
                        <td class="py-3 text-center">
                            <span class="badge px-3 py-1 rounded-pill fw-bold" style="background-color: #0f2e5a; color: #ffffff; font-size: 0.85rem;">
                                Skor: {{ $item->skor_checklist ?: 0 }}
                            </span>
                            <div class="mt-1">
                                @php
                                    $saran = $item->saran_keputusan_triase ?: $item->keputusan_final;
                                @endphp
                                @if(str_contains(strtoupper($saran), 'INVESTIGASI'))
                                    <span class="badge px-2 py-1" style="background-color: #fee2e2; color: #991b1b; font-size: 0.78rem;">Investigasi</span>
                                @elseif(str_contains(strtoupper($saran), 'AUDIT') || str_contains(strtoupper($saran), 'FACT'))
                                    <span class="badge px-2 py-1" style="background-color: #fef3c7; color: #92400e; font-size: 0.78rem;">Audit Khusus</span>
                                @else
                                    <span class="badge px-2 py-1" style="background-color: #f0fdf4; color: #166534; font-size: 0.78rem;">Pembinaan</span>
                                @endif
                            </div>
                        </td>

                        <!-- Bukti (Poin 5 & 6) -->
                        <td class="py-3 text-center">
                            @if($item->file_bukti)
                                <div class="btn-group shadow-sm">
                                    <a href="{{ route('pemeriksaan_awal.download_bukti', ['id' => $item->id, 'inline' => 1]) }}" target="_blank" 
                                       class="btn btn-sm btn-light border px-2 py-1" title="Lihat Berkas ({{ $item->tipe_bukti }})">
                                        @if($item->tipe_bukti === 'FOTO')
                                            <i class="bi bi-image text-primary"></i>
                                        @else
                                            <i class="bi bi-file-earmark-pdf text-danger"></i>
                                        @endif
                                    </a>
                                    <a href="{{ route('pemeriksaan_awal.download_bukti', $item->id) }}" 
                                       class="btn btn-sm btn-light border px-2 py-1" title="Unduh Berkas Bukti">
                                        <i class="bi bi-download text-secondary"></i>
                                    </a>
                                </div>
                                <div class="text-muted mt-1" style="font-size: 0.75rem;">{{ $item->tipe_bukti }}</div>
                            @else
                                <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.78rem;">- Belum Ada -</span>
                            @endif
                        </td>

                        <!-- Status Kasus -->
                        <td class="py-3 text-center">
                            @php
                                $status = strtoupper($item->status_kasus ?? 'OPEN');
                            @endphp
                            @if($status === 'INVESTIGASI')
                                <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: #fee2e2; color: #991b1b; font-size: 0.82rem;">
                                    <i class="bi bi-search me-1"></i>INVESTIGASI
                                </span>
                            @elseif($status === 'OPEN')
                                <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: #eff6ff; color: #1e40af; font-size: 0.82rem;">
                                    <i class="bi bi-hourglass-split me-1"></i>OPEN
                                </span>
                            @elseif($status === 'PEMBINAAN')
                                <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: #fef3c7; color: #92400e; font-size: 0.82rem;">
                                    <i class="bi bi-shield-check me-1"></i>PEMBINAAN
                                </span>
                            @elseif($status === 'CLOSED')
                                <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: #f0fdf4; color: #166534; font-size: 0.82rem;">
                                    <i class="bi bi-check2-circle me-1"></i>CLOSED
                                </span>
                            @else
                                <span class="badge px-3 py-2 rounded-pill bg-secondary text-white" style="font-size: 0.82rem;">{{ $status }}</span>
                            @endif
                        </td>

                        <!-- Tombol Aksi: Dropdown Ramah Pengguna -->
                        <td class="py-3 text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-pill px-3 fw-bold" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.85rem;">
                                    Menu Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border rounded-3 p-2" style="font-size: 0.9rem; min-width: 200px;">
                                    <!-- Detail Kronologi Modal Popup -->
                                    <li>
                                        <button class="dropdown-item py-2 rounded-2 btn-detail-kronologi" type="button" 
                                            data-id="{{ $item->indikasi_id }}"
                                            data-unit="{{ $item->nama_unit ?: $item->kode_unit }}"
                                            data-tanggal="{{ $item->tanggal_diterima ? date('d/m/Y', strtotime($item->tanggal_diterima)) : '-' }}"
                                            data-tipe="{{ $item->tipe_kasus }}"
                                            data-pihak="{{ $item->pihak_terlibat }}"
                                            data-area="{{ $item->area_kejadian ?: $item->area }}"
                                            data-dugaan="{{ $item->dugaan_awal ?: $item->dugaan_belum_terverifikasi }}"
                                            data-fakta="{{ $item->fakta_temuan ?: $item->fakta_awal_terverifikasi }}"
                                            data-kronologi="{{ $item->kronologi_awal }}"
                                            data-catatan="{{ $item->catatan_awal_ra }}"
                                            data-nominal="Rp {{ number_format($item->nominal_awal, 0, ',', '.') }}"
                                            data-skor="{{ $item->skor_checklist }}"
                                            data-saran="{{ $item->saran_keputusan_triase ?: $item->keputusan_final }}"
                                            data-status="{{ $item->status_kasus }}">
                                            <i class="bi bi-eye-fill text-primary me-2"></i> Detail Kronologi
                                        </button>
                                    </li>

                                    <!-- Poin 4: Tombol Download/Cetak Lembar Register -->
                                    <li>
                                        <a class="dropdown-item py-2 rounded-2" href="{{ route('pemeriksaan_awal.download_register', $item->id) }}" target="_blank">
                                            <i class="bi bi-file-earmark-text-fill text-success me-2"></i> Cetak Register (PDF)
                                        </a>
                                    </li>

                                    <!-- Poin 19: Tombol Print Memo Resmi SKAI -->
                                    <li>
                                        <a class="dropdown-item py-2 rounded-2" href="{{ route('pemeriksaan_awal.print_memo', $item->id) }}" target="_blank">
                                            <i class="bi bi-printer-fill text-dark me-2"></i> Cetak Memo SKAI
                                        </a>
                                    </li>

                                    <li><hr class="dropdown-divider my-1"></li>

                                    <!-- Link Status Penanganan & Memo -->
                                    <li>
                                        <a class="dropdown-item py-2 rounded-2 fw-bold text-danger" href="{{ route('pemeriksaan_awal.status_penanganan', ['q' => $item->indikasi_id]) }}">
                                            <i class="bi bi-diagram-3-fill me-2"></i> Disposisi Penanganan
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <div class="py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <h6 class="fw-bold text-secondary">Belum ada laporan indikasi fraud yang sesuai filter.</h6>
                                <p class="small text-muted mb-3">Gunakan menu Pelaporan Awal untuk mencatat kronologi kasus baru.</p>
                                <a href="{{ route('pemeriksaan_awal.pelaporan_awal') }}" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background: #0f2e5a; border-color: #0f2e5a;">
                                    <i class="bi bi-plus-circle me-1"></i> Buat Pelaporan Awal
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($laporans->hasPages())
        <div class="card-footer bg-white py-3 px-4 border-top">
            {{ $laporans->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Popup Detail Kronologi Kasus (Desain Bersih & Teks Mudah Dibaca) -->
<div class="modal fade" id="modalDetailKronologi" tabindex="-1" aria-labelledby="modalDetailKronologiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header py-3 px-4" style="background: #0f2e5a; color: #ffffff; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-0" id="modalDetailKronologiLabel">
                        <i class="bi bi-journal-text text-warning me-2"></i> Detail Kasus: <span id="mIdKasus"></span>
                    </h5>
                    <div class="small text-white-50 mt-1" id="mUnitTanggal"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="font-size: 0.95rem;">
                <!-- Ringkasan Cepat -->
                <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                    <div class="col-md-3">
                        <span class="text-secondary small fw-semibold">Tipe Kasus:</span>
                        <div class="fw-bold text-dark mt-1" id="mTipe"></div>
                    </div>
                    <div class="col-md-3">
                        <span class="text-secondary small fw-semibold">Pihak Terlibat:</span>
                        <div class="fw-bold text-dark mt-1" id="mPihak"></div>
                    </div>
                    <div class="col-md-3">
                        <span class="text-secondary small fw-semibold">Area Kejadian:</span>
                        <div class="fw-bold text-dark mt-1" id="mArea"></div>
                    </div>
                    <div class="col-md-3">
                        <span class="text-secondary small fw-semibold">Estimasi Kerugian:</span>
                        <div class="fw-bold text-danger mt-1 fs-6" id="mNominal"></div>
                    </div>
                </div>

                <!-- 1. Dugaan Awal (Modus) -->
                <div class="mb-4">
                    <label class="fw-bold text-dark mb-1" style="color: #0f2e5a !important;">
                        <i class="bi bi-question-circle-fill text-danger me-1"></i> A. Dugaan Awal (Modus Operandi):
                    </label>
                    <div class="p-3 bg-white border rounded-3 text-dark" id="mDugaan" style="white-space: pre-wrap; line-height: 1.6; background-color: #fff9f9 !important; border-color: #fecdd3 !important;"></div>
                </div>

                <!-- 2. Fakta Temuan Lapangan -->
                <div class="mb-4">
                    <label class="fw-bold text-dark mb-1" style="color: #0f2e5a !important;">
                        <i class="bi bi-check-circle-fill text-success me-1"></i> B. Fakta Temuan Lapangan:
                    </label>
                    <div class="p-3 bg-white border rounded-3 text-dark" id="mFakta" style="white-space: pre-wrap; line-height: 1.6; background-color: #f9fbf9 !important; border-color: #bbf7d0 !important;"></div>
                </div>

                <!-- 3. Kronologi Kejadian Rinci -->
                <div class="mb-4">
                    <label class="fw-bold text-dark mb-1" style="color: #0f2e5a !important;">
                        <i class="bi bi-clock-history text-primary me-1"></i> C. Kronologi Kejadian:
                    </label>
                    <div class="p-3 bg-white border rounded-3 text-dark" id="mKronologi" style="white-space: pre-wrap; line-height: 1.6; background-color: #f8fafc !important; border-color: #cbd5e1 !important;"></div>
                </div>

                <!-- 4. Catatan Awal RA -->
                <div class="mb-4">
                    <label class="fw-bold text-dark mb-1" style="color: #0f2e5a !important;">
                        <i class="bi bi-pencil-square text-secondary me-1"></i> D. Catatan Awal Resident Auditor:
                    </label>
                    <div class="p-3 bg-white border rounded-3 text-dark" id="mCatatan" style="white-space: pre-wrap; line-height: 1.6;"></div>
                </div>

                <!-- 5. Triase & Rekomendasi Disposisi -->
                <div class="p-3 rounded-3" style="background-color: #f1f5f9; border: 1px solid #cbd5e1;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-secondary" style="font-size: 0.85rem;">HASIL TRIASE AWAL SISTEM:</span>
                        <span class="badge px-3 py-1 rounded-pill fw-bold text-white" id="mSkor" style="background: #0f2e5a;"></span>
                    </div>
                    <div class="fw-bold mt-2 fs-6 text-dark" id="mSaran"></div>
                </div>
            </div>
            <div class="modal-footer bg-light py-3 px-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let detailButtons = document.querySelectorAll('.btn-detail-kronologi');
    let modalEl = document.getElementById('modalDetailKronologi');
    let modal = new bootstrap.Modal(modalEl);

    detailButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('mIdKasus').innerText = this.getAttribute('data-id');
            document.getElementById('mUnitTanggal').innerText = this.getAttribute('data-unit') + ' | Tgl Pelaporan: ' + this.getAttribute('data-tanggal');
            document.getElementById('mTipe').innerText = this.getAttribute('data-tipe');
            document.getElementById('mPihak').innerText = this.getAttribute('data-pihak');
            document.getElementById('mArea').innerText = this.getAttribute('data-area');
            document.getElementById('mNominal').innerText = this.getAttribute('data-nominal');
            document.getElementById('mDugaan').innerText = this.getAttribute('data-dugaan') || '-';
            document.getElementById('mFakta').innerText = this.getAttribute('data-fakta') || '-';
            document.getElementById('mKronologi').innerText = this.getAttribute('data-kronologi') || '-';
            document.getElementById('mCatatan').innerText = this.getAttribute('data-catatan') || 'Tidak ada catatan awal tambahan.';
            document.getElementById('mSkor').innerText = 'Skor: ' + (this.getAttribute('data-skor') || 0);
            document.getElementById('mSaran').innerText = this.getAttribute('data-saran') || 'PEMBINAAN CABANG';

            modal.show();
        });
    });
});
</script>
@endsection
