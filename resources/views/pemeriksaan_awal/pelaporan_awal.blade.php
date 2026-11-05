@extends('layouts.app')

@section('title', 'Pelaporan Awal Indikasi Fraud (SOP 09)')

@section('content')
<div class="container-fluid px-4 py-3" style="max-width: 1440px; margin: 0 auto;">
    <!-- Header Page: Desain Elegan & Bersih -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge px-3 py-2 fs-7 fw-bold" style="background-color: #e0e7ff; color: #3730a3; border-radius: 6px;">
                    <i class="bi bi-shield-check me-1"></i> STANDAR SOP 09
                </span>
                <h3 class="mb-0 fw-bold" style="color: #0f2e5a; letter-spacing: -0.3px;">Pelaporan Awal Indikasi Fraud</h3>
            </div>
            <p class="text-secondary mb-0" style="font-size: 0.95rem;">
                Formulir resmi pencatatan dugaan awal, verifikasi fakta lapangan, kronologi rinci, dan penetapan triase kasus.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pemeriksaan_awal.daftar_laporan') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm fw-semibold" style="font-size: 0.9rem;">
                <i class="bi bi-journal-text me-1"></i> Daftar Laporan ({{ \App\Models\Sop09RegisterIndikasi::count() }})
            </a>
            <a href="{{ route('pemeriksaan_awal.status_penanganan') }}" class="btn btn-outline-primary rounded-pill px-3 shadow-sm fw-semibold" style="font-size: 0.9rem;">
                <i class="bi bi-diagram-3-fill me-1"></i> Status Penanganan
            </a>
        </div>
    </div>

    @include('pemeriksaan_awal.partials.nav_tabs')

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4 p-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
            <div>
                <h6 class="mb-0 fw-bold text-success">Pemberitahuan Sukses</h6>
                <div style="font-size: 0.92rem;">{{ session('success') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(isset($errors) && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4 p-3" role="alert">
        <div class="d-flex align-items-start">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger mt-1"></i>
            <div>
                <h6 class="mb-1 fw-bold text-danger">Mohon Lengkapi Data Berikut:</h6>
                <ul class="mb-0 ps-3" style="font-size: 0.92rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(!empty($prefill['source']))
    <div class="card border-0 shadow-sm rounded-3 mb-4" style="background-color: #fffbeb; border-left: 5px solid #f59e0b !important;">
        <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; min-width: 44px; background: #fef3c7; color: #b45309; font-size: 1.3rem;">
                <i class="bi bi-flag-fill"></i>
            </div>
            <div>
                <h6 class="mb-1 fw-bold" style="color: #92400e;">Data Temuan Kritis Diambil Otomatis (Sumber: {{ $prefill['source'] }})</h6>
                <div style="font-size: 0.9rem; color: #78350f;">
                    Informasi cabang dan temuan telah diisi otomatis dari temuan KKA yang berstatus <strong>CRITICAL</strong>. Silakan periksa dan lengkapi bagian kronologi serta checklist triase.
                </div>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('pemeriksaan_awal.pelaporan_awal.store') }}" method="POST" enctype="multipart/form-data" id="formPelaporanFraud">
        @csrf
        <input type="hidden" name="flagged_from" value="{{ $prefill['source'] ?? 'MANDIRI_RA' }}">

        <div class="row g-4">
            <!-- Kolom Kiri: Formulir Kronologi & Fakta Temuan -->
            <div class="col-lg-8">
                <!-- Card 1: Identitas & Klasifikasi Kasus -->
                <div class="card shadow-sm border-0 rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.85rem;">1</span>
                            <h5 class="mb-0 fw-bold" style="color: #0f2e5a;">Identitas &amp; Klasifikasi Kasus</h5>
                        </div>
                        <span class="badge bg-light text-secondary border px-3 py-1">Registrasi Awal</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- ID Indikasi Kasus (Otomatis) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.92rem;">
                                    ID Indikasi Kasus
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-hash"></i></span>
                                    <input type="text" name="indikasi_id" class="form-control fw-bold bg-light border-start-0" 
                                           style="color: #be123c; font-size: 0.95rem; height: 44px;" value="{{ $autoId }}" readonly>
                                </div>
                                <div class="text-muted mt-1" style="font-size: 0.82rem;">ID unik dibuat otomatis oleh sistem.</div>
                            </div>

                            <!-- Tanggal Pelaporan (Jam dihapus sesuai Poin 12) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.92rem;">
                                    Tanggal Pelaporan / Diterima <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar3 text-primary"></i></span>
                                    <input type="date" name="tanggal_diterima" class="form-control border-start-0" 
                                           style="font-size: 0.95rem; height: 44px;" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>

                            <!-- Kantor Cabang / Unit Kerja (Dropdown Otomatis sesuai Poin 9) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.92rem;">
                                    Kantor Cabang / Unit Kerja <span class="text-danger">*</span>
                                </label>
                                <select name="unit_selection" class="form-select" style="font-size: 0.95rem; height: 44px;" required>
                                    <option value="">-- Pilih Kantor Cabang / Unit --</option>
                                    @foreach($units as $unit)
                                        @php
                                            $val = $unit->unit_code . '|' . $unit->unit_name;
                                            $selected = (!empty($prefill['kode_unit']) && $prefill['kode_unit'] == $unit->unit_code) ? 'selected' : '';
                                        @endphp
                                        <option value="{{ $val }}" {{ $selected }}>
                                            [{{ $unit->unit_code }}] {{ $unit->unit_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Area Kejadian / Area Uji Awal (Poin 16) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.92rem;">
                                    Area Kejadian / Objek Audit <span class="text-danger">*</span>
                                </label>
                                <select name="area_kejadian" class="form-select" style="font-size: 0.95rem; height: 44px;" required>
                                    <option value="Operasional Kas & Teller" {{ (!empty($prefill['area']) && str_contains(strtolower($prefill['area']), 'kas')) ? 'selected' : '' }}>Operasional Kas &amp; Teller (Brankas / Cash Opname)</option>
                                    <option value="Kartu ATM & Electronic Banking" {{ (!empty($prefill['area']) && str_contains(strtolower($prefill['area']), 'atm')) ? 'selected' : '' }}>Kartu ATM &amp; Electronic Banking (PIN Mailer / Mesin ATM)</option>
                                    <option value="Customer Service & Giro/Tabungan" {{ (!empty($prefill['area']) && str_contains(strtolower($prefill['area']), 'cs')) ? 'selected' : '' }}>Customer Service &amp; Pembukaan Rekening Giro/Tabungan</option>
                                    <option value="Kredit & Pembiayaan" {{ (!empty($prefill['area']) && str_contains(strtolower($prefill['area']), 'kredit')) ? 'selected' : '' }}>Kredit &amp; Pembiayaan (Analisis / Agunan / Pencairan)</option>
                                    <option value="Akuntansi & General Ledger">Akuntansi &amp; Pembukuan General Ledger (GL)</option>
                                    <option value="Teknologi Informasi & Akses Sistem">Teknologi Informasi &amp; Akses Core Banking</option>
                                    <option value="Pengadaan & Administrasi Umum">Pengadaan Logistik &amp; Administrasi Umum</option>
                                </select>
                            </div>

                            <!-- Tipe Kasus: Insidentil vs Non-Insidentil (Poin 10) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.92rem;">
                                    Tipe Kasus <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex gap-2 mt-1">
                                    <div class="form-check p-2 px-3 border rounded-3 bg-light flex-fill">
                                        <input class="form-check-input" type="radio" name="tipe_kasus" id="tipe_insidentil" value="INSIDENTIL" checked style="width: 1.15rem; height: 1.15rem;">
                                        <label class="form-check-label fw-bold text-dark ms-2" for="tipe_insidentil" style="font-size: 0.9rem;">
                                            Insidentil
                                            <span class="d-block fw-normal text-muted" style="font-size: 0.78rem;">Kejadian mendadak / sewaktu-waktu</span>
                                        </label>
                                    </div>
                                    <div class="form-check p-2 px-3 border rounded-3 bg-light flex-fill">
                                        <input class="form-check-input" type="radio" name="tipe_kasus" id="tipe_non_insidentil" value="NON_INSIDENTIL" style="width: 1.15rem; height: 1.15rem;">
                                        <label class="form-check-label fw-bold text-dark ms-2" for="tipe_non_insidentil" style="font-size: 0.9rem;">
                                            Non-Insidentil
                                            <span class="d-block fw-normal text-muted" style="font-size: 0.78rem;">Penyimpangan berulang / sistematis</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Pihak Terlibat (Poin 1) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.92rem;">
                                    Pihak yang Terindikasi Terlibat <span class="text-danger">*</span>
                                </label>
                                <select name="pihak_terlibat" class="form-select" style="font-size: 0.95rem; height: 44px;" required>
                                    <option value="INTERNAL">Internal (Pegawai / Pimpinan / Pejabat Bank)</option>
                                    <option value="EKSTERNAL">Eksternal (Nasabah / Rekanan / Pihak Ketiga)</option>
                                    <option value="INDIKASI_INTERNAL_EKSTERNAL">Indikasi Kolusi Internal &amp; Eksternal</option>
                                </select>
                            </div>

                            <!-- Estimasi Nilai Nominal / Potensi Kerugian -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.92rem;">
                                    Estimasi Nilai Nominal / Potensi Kerugian
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold text-dark border-end-0">Rp</span>
                                    <input type="text" name="nominal_awal" id="nominal_awal" class="form-control text-end fw-bold border-start-0" 
                                           style="font-size: 1rem; color: #0f2e5a; height: 44px;" placeholder="0" value="0">
                                </div>
                                <div class="text-muted mt-1" style="font-size: 0.82rem;">Estimasi saldo/dana bank atau nasabah yang terdampak.</div>
                            </div>

                            <!-- Sumber Informasi / Deteksi Awal -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.92rem;">
                                    Sumber Informasi / Deteksi Awal
                                </label>
                                <select name="sumber_indikasi" class="form-select" style="font-size: 0.95rem; height: 44px;">
                                    <option value="Audit Rutin / Uji Petik RA">Audit Rutin / Uji Petik Resident Auditor</option>
                                    <option value="Temuan Surprise Audit Kas/ATM">Temuan Sidak / Surprise Audit (SOP 05)</option>
                                    <option value="Laporan Whistleblowing (WBS)">Laporan Whistleblowing System (WBS)</option>
                                    <option value="Pengaduan Nasabah / Pihak Ketiga">Pengaduan Nasabah / Pihak Ketiga</option>
                                    <option value="Anomali Sistem / Laporan IT">Anomali Core Banking / Pengecekan Sistem IT</option>
                                    <option value="Disposisi Kadiv SKAI / Manajemen">Disposisi Kadiv SKAI / Manajemen</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Uraian Dugaan Awal, Fakta & Kronologi (Poin 2 & Poin 16) -->
                <div class="card shadow-sm border-0 rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.85rem;">2</span>
                            <h5 class="mb-0 fw-bold" style="color: #0f2e5a;">Uraian Kasus: Dugaan, Fakta &amp; Kronologi</h5>
                        </div>
                        <span class="badge bg-light text-dark border px-3 py-1">Poin Utama Pemeriksaan</span>
                    </div>
                    <div class="card-body p-4">
                        <!-- Poin 2: Dugaan Awal diubah urutannya di atas Fakta! -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.95rem;">
                                A. Dugaan Awal (Modus Operandi / Indikasi Awal) <span class="text-danger">*</span>
                            </label>
                            <textarea name="dugaan_awal" class="form-control" rows="3" 
                                      style="font-size: 0.95rem; line-height: 1.6; border-radius: 8px;" required
                                      placeholder="Uraikan dugaan awal bagaimana penyimpangan/kecurangan dilakukan (misal: penarikan kas tanpa slip, pembukuan fiktif, selisih fisik kas kaset ATM, dll)...">{{ old('dugaan_awal', $prefill['dugaan_awal'] ?? '') }}</textarea>
                            <div class="text-muted mt-1" style="font-size: 0.82rem;">Dugaan modus operandi awal sebelum dilakukan verifikasi menyeluruh.</div>
                        </div>

                        <!-- Fakta Temuan Lapangan -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.95rem;">
                                B. Fakta Temuan Lapangan yang Terverifikasi <span class="text-danger">*</span>
                            </label>
                            <textarea name="fakta_temuan" class="form-control" rows="3" 
                                      style="font-size: 0.95rem; line-height: 1.6; border-radius: 8px;" required
                                      placeholder="Tuliskan fakta objektif yang telah diverifikasi di lapangan (misal: selisih kas fisik Rp 15.000.000,- saat cash opname, mutasi penarikan tanpa tanda tangan nasabah, dsb)...">{{ old('fakta_temuan', $prefill['fakta_temuan'] ?? '') }}</textarea>
                            <div class="text-muted mt-1" style="font-size: 0.82rem;">Fakta objektif yang didukung data transaksi atau pemeriksaan fisik.</div>
                        </div>

                        <!-- Poin 16: Kronologi Awal Kasus -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.95rem;">
                                C. Kronologi Awal Kejadian Secara Rinci <span class="text-danger">*</span>
                            </label>
                            <textarea name="kronologi_awal" class="form-control" rows="4" 
                                      style="font-size: 0.95rem; line-height: 1.6; border-radius: 8px;" required
                                      placeholder="Ceritakan secara naratif alur waktu (tanggal, jam perkiraan, pihak yang terlibat, transaksi yang terjadi, dan bagaimana temuan ini pertama kali terdeteksi)...">{{ old('kronologi_awal') }}</textarea>
                            <div class="text-muted mt-1" style="font-size: 0.82rem;">Alur kronologis bagaimana penyimpangan terjadi hingga terdeteksi.</div>
                        </div>

                        <!-- Poin 3: Catatan Awal RA -->
                        <div class="mb-1">
                            <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.95rem;">
                                D. Catatan Awal Resident Auditor (RA)
                            </label>
                            <textarea name="catatan_awal_ra" class="form-control" rows="2" 
                                      style="font-size: 0.95rem; line-height: 1.6; border-radius: 8px;"
                                      placeholder="Catatan evaluasi risiko tambahan dari auditor atau saran pencegahan cepat di cabang...">{{ old('catatan_awal_ra') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Upload Berkas Dokumen / Foto Bukti (Poin 5 & 6) -->
                <div class="card shadow-sm border-0 rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.85rem;">3</span>
                            <h5 class="mb-0 fw-bold" style="color: #0f2e5a;">Berkas Bukti Pendukung (Dokumen / Foto)</h5>
                        </div>
                        <span class="badge bg-light text-secondary border px-3 py-1">PDF / Foto Fisik</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-7">
                                <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.92rem;">
                                    Pilih File Bukti (Dokumen / Foto)
                                </label>
                                <input type="file" name="file_bukti" class="form-control" style="font-size: 0.92rem; height: 44px;" 
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                <div class="text-muted mt-1" style="font-size: 0.82rem;">Format: PDF, Word, Excel, atau Foto fisik (JPG, PNG). Maksimal 20 MB.</div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.92rem;">
                                    Keterangan / Nama Bukti
                                </label>
                                <input type="text" name="nama_bukti" class="form-control" style="font-size: 0.92rem; height: 44px;" 
                                       placeholder="Contoh: Slip Penarikan Rekening / Foto Fisik Kas">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Parameter Triase & Keputusan Otomatis -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-4 mb-4 sticky-top bg-white" style="top: 20px; z-index: 10; border: 1px solid #cbd5e1 !important;">
                    <!-- Header Card Triase: Biru Korporat Elegan -->
                    <div class="card-header py-3 px-4 d-flex align-items-center justify-content-between" style="background: #0f2e5a; color: #ffffff; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-ui-checks text-warning fs-5"></i>
                            <h5 class="mb-0 fw-bold text-white" style="font-size: 1.05rem;">Penetapan Triase Kasus</h5>
                        </div>
                        <span class="badge bg-white text-dark fw-bold px-3 py-1 rounded-pill" id="badgeTotalSkor" style="font-size: 0.85rem;">
                            Skor: 0
                        </span>
                    </div>

                    <div class="card-body p-4">
                        <div class="p-3 rounded-3 mb-3" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-info-circle-fill text-success fs-5 mt-0"></i>
                                <div style="font-size: 0.86rem; color: #166534; line-height: 1.45;">
                                    Centang kriteria yang terpenuhi. <strong>Skor dan rekomendasi keputusan akan dihitung secara otomatis</strong> oleh sistem.
                                </div>
                            </div>
                        </div>

                        <!-- Checklist Kriteria Triase (Mudah Diklik, Font Jelas & Nyaman) -->
                        <div class="checklist-container mb-3 border rounded-3 p-3 bg-light">
                            <!-- Kriteria 1 -->
                            <div class="form-check d-flex align-items-start gap-2 mb-3 pb-3 border-bottom">
                                <input class="form-check-input kriteria-check flex-shrink-0" type="checkbox" name="checklist[]" value="3" id="kriteria_1" onchange="calculateScore()" 
                                       style="width: 1.35rem; height: 1.35rem; cursor: pointer; margin-top: 0.15rem;">
                                <label class="form-check-label text-dark flex-grow-1 cursor-pointer" for="kriteria_1" style="font-size: 0.92rem; line-height: 1.4; cursor: pointer;">
                                    <span class="fw-semibold">Indikasi kerugian finansial bank atau nasabah bernilai signifikan</span>
                                    <span class="badge bg-danger-subtle text-danger ms-1 px-2 py-0" style="font-size: 0.75rem;">+3 Poin</span>
                                </label>
                            </div>

                            <!-- Kriteria 2 -->
                            <div class="form-check d-flex align-items-start gap-2 mb-3 pb-3 border-bottom">
                                <input class="form-check-input kriteria-check flex-shrink-0" type="checkbox" name="checklist[]" value="2" id="kriteria_2" onchange="calculateScore()" 
                                       style="width: 1.35rem; height: 1.35rem; cursor: pointer; margin-top: 0.15rem;">
                                <label class="form-check-label text-dark flex-grow-1 cursor-pointer" for="kriteria_2" style="font-size: 0.92rem; line-height: 1.4; cursor: pointer;">
                                    <span class="fw-semibold">Melibatkan pihak internal (pegawai / pimpinan / pejabat bank)</span>
                                    <span class="badge bg-primary-subtle text-primary ms-1 px-2 py-0" style="font-size: 0.75rem;">+2 Poin</span>
                                </label>
                            </div>

                            <!-- Kriteria 3 -->
                            <div class="form-check d-flex align-items-start gap-2 mb-3 pb-3 border-bottom">
                                <input class="form-check-input kriteria-check flex-shrink-0" type="checkbox" name="checklist[]" value="3" id="kriteria_3" onchange="calculateScore()" 
                                       style="width: 1.35rem; height: 1.35rem; cursor: pointer; margin-top: 0.15rem;">
                                <label class="form-check-label text-dark flex-grow-1 cursor-pointer" for="kriteria_3" style="font-size: 0.92rem; line-height: 1.4; cursor: pointer;">
                                    <span class="fw-semibold">Terdapat risiko tinggi barang bukti dihilangkan atau dimanipulasi</span>
                                    <span class="badge bg-danger-subtle text-danger ms-1 px-2 py-0" style="font-size: 0.75rem;">+3 Poin</span>
                                </label>
                            </div>

                            <!-- Kriteria 4 -->
                            <div class="form-check d-flex align-items-start gap-2 mb-3 pb-3 border-bottom">
                                <input class="form-check-input kriteria-check flex-shrink-0" type="checkbox" name="checklist[]" value="2" id="kriteria_4" onchange="calculateScore()" 
                                       style="width: 1.35rem; height: 1.35rem; cursor: pointer; margin-top: 0.15rem;">
                                <label class="form-check-label text-dark flex-grow-1 cursor-pointer" for="kriteria_4" style="font-size: 0.92rem; line-height: 1.4; cursor: pointer;">
                                    <span class="fw-semibold">Pola transaksi berulang / indikasi penyimpangan terstruktur</span>
                                    <span class="badge bg-warning-subtle text-dark ms-1 px-2 py-0" style="font-size: 0.75rem;">+2 Poin</span>
                                </label>
                            </div>

                            <!-- Kriteria 5 -->
                            <div class="form-check d-flex align-items-start gap-2 mb-3 pb-3 border-bottom">
                                <input class="form-check-input kriteria-check flex-shrink-0" type="checkbox" name="checklist[]" value="2" id="kriteria_5" onchange="calculateScore()" 
                                       style="width: 1.35rem; height: 1.35rem; cursor: pointer; margin-top: 0.15rem;">
                                <label class="form-check-label text-dark flex-grow-1 cursor-pointer" for="kriteria_5" style="font-size: 0.92rem; line-height: 1.4; cursor: pointer;">
                                    <span class="fw-semibold">Berdampak langsung kepada rekening nasabah / reputasi bank</span>
                                    <span class="badge bg-info-subtle text-dark ms-1 px-2 py-0" style="font-size: 0.75rem;">+2 Poin</span>
                                </label>
                            </div>

                            <!-- Kriteria 6 -->
                            <div class="form-check d-flex align-items-start gap-2 mb-0">
                                <input class="form-check-input kriteria-check flex-shrink-0" type="checkbox" name="checklist[]" value="2" id="kriteria_6" onchange="calculateScore()" 
                                       style="width: 1.35rem; height: 1.35rem; cursor: pointer; margin-top: 0.15rem;">
                                <label class="form-check-label text-dark flex-grow-1 cursor-pointer" for="kriteria_6" style="font-size: 0.92rem; line-height: 1.4; cursor: pointer;">
                                    <span class="fw-semibold">Keterlibatan pihak luar / terindikasi sindikat eksternal</span>
                                    <span class="badge bg-secondary-subtle text-dark ms-1 px-2 py-0" style="font-size: 0.75rem;">+2 Poin</span>
                                </label>
                            </div>
                        </div>

                        <!-- Tampilan Akumulasi Skor (Poin 13: Jelas, Nyaman & Mudah Dibaca) -->
                        <div class="p-3 rounded-3 mb-3 text-center border" style="background-color: #f8fafc;">
                            <div class="text-secondary fw-bold" style="font-size: 0.8rem; letter-spacing: 0.5px;">TOTAL SKOR TRIASE</div>
                            <div class="display-5 fw-bold my-1" id="totalSkorDisplay" style="color: #0f2e5a;">0</div>
                            <div class="fw-semibold text-muted" id="skorKeterangan" style="font-size: 0.85rem;">Belum ada kriteria yang dipilih</div>
                        </div>

                        <!-- Rekomendasi Disposisi Otomatis (Poin 14) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.88rem;">Rekomendasi Keputusan Disposisi:</label>
                            <div class="p-3 rounded-3 border text-center transition-all" id="boxRekomendasi" style="background-color: #f1f5f9; border-color: #cbd5e1 !important;">
                                <div class="fw-bold fs-6" id="textRekomendasi" style="color: #334155;">
                                    PEMBINAAN CABANG / MONITORING BERKALA
                                </div>
                                <div class="mt-1" id="subtextRekomendasi" style="font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                    Skor &lt; 4: Potensi penyimpangan berisiko rendah atau administratif.
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Aksi Simpan -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm rounded-pill py-3" style="background: #0f2e5a; border-color: #0f2e5a; font-size: 1rem;">
                                <i class="bi bi-save me-2"></i> Simpan Laporan Indikasi
                            </button>
                            <a href="{{ route('pemeriksaan_awal.daftar_laporan') }}" class="btn btn-outline-secondary rounded-pill py-2" style="font-size: 0.9rem;">
                                Batal &amp; Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript Triase & Format Rupiah -->
<script>
function calculateScore() {
    let checkboxes = document.querySelectorAll('.kriteria-check');
    let totalScore = 0;
    checkboxes.forEach(function(cb) {
        if (cb.checked) {
            totalScore += parseInt(cb.value);
        }
    });

    // Update Display Skor
    document.getElementById('totalSkorDisplay').innerText = totalScore;
    document.getElementById('badgeTotalSkor').innerText = 'Skor: ' + totalScore;

    // Evaluasi Keputusan Triase
    let textRekomendasi = document.getElementById('textRekomendasi');
    let subtextRekomendasi = document.getElementById('subtextRekomendasi');
    let boxRekomendasi = document.getElementById('boxRekomendasi');
    let skorKeterangan = document.getElementById('skorKeterangan');

    if (totalScore >= 7) {
        textRekomendasi.innerText = 'INVESTIGASI SEGERA (PRIORITAS TINGGI)';
        textRekomendasi.className = 'fw-bold fs-6 text-danger';
        subtextRekomendasi.innerText = 'Skor >= 7: Memenuhi kriteria fraud berat. Perlu diterbitkan Surat Penugasan Investigasi Khusus.';
        boxRekomendasi.style.backgroundColor = '#fff1f2';
        boxRekomendasi.style.borderColor = '#fecdd3';
        skorKeterangan.innerText = 'Tingkat Urgensi: SANGAT TINGGI (CRITICAL)';
        skorKeterangan.className = 'fw-bold text-danger';
    } else if (totalScore >= 4) {
        textRekomendasi.innerText = 'AUDIT KHUSUS / FACT-FINDING MENDALAM';
        textRekomendasi.className = 'fw-bold fs-6 text-warning-emphasis';
        subtextRekomendasi.innerText = 'Skor 4 - 6: Memerlukan pendalaman bukti transaksi dan klarifikasi kepada pihak terkait.';
        boxRekomendasi.style.backgroundColor = '#fffbeb';
        boxRekomendasi.style.borderColor = '#fde68a';
        skorKeterangan.innerText = 'Tingkat Urgensi: SEDANG (PENDALAMAN)';
        skorKeterangan.className = 'fw-bold text-warning-emphasis';
    } else {
        textRekomendasi.innerText = 'PEMBINAAN CABANG / MONITORING BERKALA';
        textRekomendasi.className = 'fw-bold fs-6 text-success';
        subtextRekomendasi.innerText = 'Skor < 4: Potensi penyimpangan berisiko rendah atau perbaikan administratif intern cabang.';
        boxRekomendasi.style.backgroundColor = '#f0fdf4';
        boxRekomendasi.style.borderColor = '#bbf7d0';
        skorKeterangan.innerText = 'Tingkat Urgensi: RENDAH (PEMBINAAN)';
        skorKeterangan.className = 'fw-bold text-success';
    }
}

// Auto format nominal rupiah
document.addEventListener('DOMContentLoaded', function() {
    let nominalInput = document.getElementById('nominal_awal');
    if (nominalInput) {
        nominalInput.addEventListener('keyup', function(e) {
            let value = this.value.replace(/[^,\d]/g, '').toString();
            let split = value.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            this.value = rupiah;
        });
    }
});
</script>
@endsection
