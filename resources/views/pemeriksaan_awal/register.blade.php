@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Modul -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-journal-plus me-2"></i> REGISTER INDIKASI FRAUD (WP-RA-09-A)
            </h4>
            <p class="text-muted small mb-0">
                Pencatatan kasus masuk, verifikasi fakta awal, dan identifikasi red flag indikasi fraud Bank Sulteng
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pemeriksaan_awal.triase') }}" class="btn btn-outline-primary btn-sm px-3 fw-semibold">
                <i class="bi bi-diagram-3 me-1"></i> Triase Awal
            </a>
            <a href="{{ route('pemeriksaan_awal.bukti_custody') }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
                <i class="bi bi-shield-check me-1"></i> Chain of Custody
            </a>
            <button type="button" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddRegister">
                <i class="bi bi-plus-lg me-1"></i> Tambah Register Kasus
            </button>
        </div>
    </div>

    <!-- Tab Navigasi SOP 09 -->
    @include('pemeriksaan_awal.partials.nav_tabs')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pipeline Guidance: Alur Kerja Penanganan Kasus SOP 09 -->
    <div class="card border-0 bg-light shadow-sm rounded-3 mb-4 p-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center">
                <span class="badge bg-primary rounded-pill px-3 py-2 me-2 fw-semibold">
                    <i class="bi bi-1-circle-fill me-1"></i> Register Indikasi
                </span>
                <i class="bi bi-chevron-right text-muted me-2"></i>
                <a href="{{ route('pemeriksaan_awal.triase') }}" class="text-decoration-none text-muted small fw-semibold me-2">
                    <i class="bi bi-2-circle me-1"></i> Triase &amp; Skoring
                </a>
                <i class="bi bi-chevron-right text-muted me-2"></i>
                <a href="{{ route('pemeriksaan_awal.bukti_custody') }}" class="text-decoration-none text-muted small fw-semibold me-2">
                    <i class="bi bi-3-circle me-1"></i> Chain of Custody
                </a>
                <i class="bi bi-chevron-right text-muted me-2"></i>
                <a href="{{ route('pemeriksaan_awal.kka') }}" class="text-decoration-none text-muted small fw-semibold me-2">
                    <i class="bi bi-4-circle me-1"></i> KKA Fact-Finding
                </a>
                <i class="bi bi-chevron-right text-muted me-2"></i>
                <a href="{{ route('pemeriksaan_awal.eskalasi') }}" class="text-decoration-none text-muted small fw-semibold">
                    <i class="bi bi-5-circle me-1"></i> Keputusan &amp; Eskalasi
                </a>
            </div>
            <div class="small text-muted">
                <i class="bi bi-info-circle me-1 text-primary"></i> Standar Formulir Kerja: <strong>WP-RA-09-A</strong>
            </div>
        </div>
    </div>

    <!-- 4 Kartu Statistik Ringkasan -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-primary rounded-3 h-100">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Total Kasus Masuk</span>
                    <h3 class="fw-bold text-primary mb-1">{{ $summary['total_kasus'] ?? count($indikasis ?? []) }}</h3>
                    <small class="text-muted">Register Kasus Terdata</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-warning rounded-3 h-100">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Kasus Status OPEN</span>
                    <h3 class="fw-bold text-dark mb-1">{{ $summary['open_kasus'] ?? 0 }}</h3>
                    <small class="text-muted">Dalam Proses Fact-Finding</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-danger rounded-3 h-100">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Sensitivitas Tinggi</span>
                    <h3 class="fw-bold text-danger mb-1">{{ $summary['high_sensitivitas'] ?? 0 }}</h3>
                    <small class="text-muted">Risiko Reputasi &amp; Regulator</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-success rounded-3 h-100">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Total Paparan Finansial</span>
                    <h4 class="fw-bold text-success mb-1">Rp {{ number_format($summary['total_nominal'] ?? 0) }}</h4>
                    <small class="text-muted">Estimasi Nominal Terindikasi</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <form action="{{ route('pemeriksaan_awal.register') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari ID indikasi, nama cabang, kode unit, red flag...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="sumber" class="form-select form-select-sm">
                        <option value="">-- Semua Sumber --</option>
                        <option value="WHISTLEBLOWING" {{ request('sumber') == 'WHISTLEBLOWING' ? 'selected' : '' }}>Whistleblowing (WBS)</option>
                        <option value="LAPORAN_CABANG" {{ request('sumber') == 'LAPORAN_CABANG' ? 'selected' : '' }}>Laporan Cabang</option>
                        <option value="AUDIT_ONSITE" {{ request('sumber') == 'AUDIT_ONSITE' ? 'selected' : '' }}>Audit Onsite</option>
                        <option value="TEMUAN_OFFSITE" {{ request('sumber') == 'TEMUAN_OFFSITE' ? 'selected' : '' }}>Temuan Offsite</option>
                        <option value="PENGADUAN_NASABAH" {{ request('sumber') == 'PENGADUAN_NASABAH' ? 'selected' : '' }}>Pengaduan Nasabah</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sensitivitas" class="form-select form-select-sm">
                        <option value="">-- Sensitivitas --</option>
                        <option value="TINGGI" {{ request('sensitivitas') == 'TINGGI' ? 'selected' : '' }}>Tinggi</option>
                        <option value="SEDANG" {{ request('sensitivitas') == 'SEDANG' ? 'selected' : '' }}>Sedang</option>
                        <option value="RENDAH" {{ request('sensitivitas') == 'RENDAH' ? 'selected' : '' }}>Rendah</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- Status Kasus --</option>
                        <option value="OPEN" {{ request('status') == 'OPEN' ? 'selected' : '' }}>Status OPEN</option>
                        <option value="INVESTIGASI" {{ request('status') == 'INVESTIGASI' ? 'selected' : '' }}>Status INVESTIGASI</option>
                        <option value="CLOSED" {{ request('status') == 'CLOSED' ? 'selected' : '' }}>Status CLOSED</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['q', 'sumber', 'sensitivitas', 'status']))
                        <a href="{{ route('pemeriksaan_awal.register') }}" class="btn btn-light btn-sm border" title="Reset filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data Register Indikasi -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-table me-2 text-primary"></i> Daftar Register Kasus Indikasi Fraud (WP-RA-09-A)
                </h6>
                <small class="text-muted">Format standar pencatatan 31 kolom pengawasan SKAI Bank Sulteng</small>
            </div>
            <span class="badge bg-primary rounded-pill px-3 py-2">{{ count($indikasis ?? []) }} Kasus Terdaftar</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 140px;">ID Indikasi</th>
                            <th>Tanggal &amp; Waktu</th>
                            <th>Unit Kerja &amp; Area</th>
                            <th>Sumber Informasi</th>
                            <th>Red Flag Utama (Indikator)</th>
                            <th>Estimasi Nominal</th>
                            <th>Sensitivitas</th>
                            <th>Status Kasus</th>
                            <th class="text-end pe-3" style="width: 170px;">Aksi Terintegrasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($indikasis ?? [] as $item)
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-light text-primary border border-primary font-monospace fw-bold px-2 py-1">
                                        {{ $item->indikasi_id }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark d-block">
                                        <i class="bi bi-calendar-event me-1 text-muted"></i>{{ $item->tanggal_diterima }}
                                    </span>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>{{ $item->jam ?? '08:30' }} WITA
                                    </small>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark d-block">
                                        {{ $item->nama_unit ?? ('Unit ' . $item->kode_unit) }}
                                    </span>
                                    <div class="d-flex align-items-center gap-1 mt-1">
                                        <span class="badge bg-light text-secondary border font-monospace">[{{ $item->kode_unit }}]</span>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ $item->area ?? 'Operasional' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $src = $item->sumber_indikasi;
                                        $srcClass = match($src) {
                                            'WHISTLEBLOWING' => 'bg-danger bg-opacity-10 text-danger border-danger',
                                            'LAPORAN_CABANG' => 'bg-primary bg-opacity-10 text-primary border-primary',
                                            'TEMUAN_OFFSITE' => 'bg-info bg-opacity-10 text-info border-info',
                                            'AUDIT_ONSITE' => 'bg-warning bg-opacity-10 text-dark border-warning',
                                            default => 'bg-secondary bg-opacity-10 text-secondary border-secondary',
                                        };
                                        $srcIcon = match($src) {
                                            'WHISTLEBLOWING' => 'bi-megaphone-fill',
                                            'LAPORAN_CABANG' => 'bi-building-exclamation',
                                            'TEMUAN_OFFSITE' => 'bi-laptop',
                                            'AUDIT_ONSITE' => 'bi-briefcase-fill',
                                            default => 'bi-person-lines-fill',
                                        };
                                    @endphp
                                    <span class="badge {{ $srcClass }} border">
                                        <i class="bi {{ $srcIcon }} me-1"></i>{{ str_replace('_', ' ', $src) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark" style="max-width: 250px;">
                                        {{ $item->red_flag_utama }}
                                    </div>
                                    @if(!empty($item->fakta_awal_terverifikasi))
                                        <small class="text-muted d-block mt-1">
                                            <i class="bi bi-check2-circle text-success me-1"></i>Fakta Awal Terverifikasi
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-danger d-block">
                                        Rp {{ number_format($item->nominal_awal) }}
                                    </span>
                                    @if(($item->nasabah_terdampak ?? 'TIDAK') == 'YA')
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger small">
                                            <i class="bi bi-person-exclamation me-1"></i>Nasabah
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $sensClass = match($item->sensitivitas_reputasi) {
                                            'TINGGI' => 'bg-danger text-white',
                                            'SEDANG' => 'bg-warning text-dark',
                                            default => 'bg-success text-white',
                                        };
                                    @endphp
                                    <span class="badge {{ $sensClass }}">
                                        {{ $item->sensitivitas_reputasi }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1">
                                        <i class="bi bi-folder2-open me-1"></i>{{ $item->status_kasus }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" 
                                                class="btn btn-outline-secondary py-0 px-2 btn-detail-register"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDetailRegister"
                                                data-id="{{ $item->indikasi_id }}"
                                                data-tgl="{{ $item->tanggal_diterima }}"
                                                data-jam="{{ $item->jam ?? '08:30' }}"
                                                data-unit="[{{ $item->kode_unit }}] {{ $item->nama_unit }}"
                                                data-area="{{ $item->area ?? '-' }}"
                                                data-sumber="{{ $item->sumber_indikasi }}"
                                                data-redflag="{{ $item->red_flag_utama }}"
                                                data-nominal="Rp {{ number_format($item->nominal_awal) }}"
                                                data-sensitivitas="{{ $item->sensitivitas_reputasi }}"
                                                data-nasabah="{{ $item->nasabah_terdampak ?? 'TIDAK' }}"
                                                data-pimpinan="{{ $item->pihak_kunci_pimpinan ?? 'TIDAK' }}"
                                                data-fakta="{{ $item->fakta_awal_terverifikasi ?? 'Belum ada catatan fakta terverifikasi' }}"
                                                data-dugaan="{{ $item->dugaan_belum_terverifikasi ?? 'Belum ada catatan dugaan' }}"
                                                data-status="{{ $item->status_kasus }}"
                                                title="Lihat Detail Kasus">
                                            <i class="bi bi-eye"></i> Detail
                                        </button>
                                        <a href="{{ route('pemeriksaan_awal.triase') }}" class="btn btn-outline-primary py-0 px-2" title="Lanjut Triase">
                                            <i class="bi bi-diagram-3"></i>
                                        </a>
                                        <a href="{{ route('pemeriksaan_awal.bukti_custody', ['indikasi_id' => $item->indikasi_id]) }}" class="btn btn-outline-success py-0 px-2" title="Amankan Bukti (Chain of Custody)">
                                            <i class="bi bi-shield-check"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary"></i>
                                    <strong>Belum ada register indikasi fraud yang sesuai kriteria pencarian.</strong>
                                    <p class="small text-muted mb-3">Klik tombol di bawah untuk menambahkan pencatatan kasus baru.</p>
                                    <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalAddRegister">
                                        <i class="bi bi-plus-lg me-1"></i> Tambah Register Kasus
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Tambah Register Indikasi (Form Terbimbing 3 Tahap) -->
<div class="modal fade" id="modalAddRegister" tabindex="-1" aria-labelledby="modalAddRegisterLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('pemeriksaan_awal.register.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold" id="modalAddRegisterLabel">
                        <i class="bi bi-journal-plus me-2"></i> Form Registrasi Kasus Indikasi Fraud (WP-RA-09-A)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- SECTION 1: IDENTITAS & ASAL KASUS -->
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary rounded-circle p-2 me-2">1</span>
                        <h6 class="fw-bold text-primary mb-0">Identitas Penerimaan &amp; Unit Terindikasi</h6>
                    </div>
                    <div class="row g-3 mb-4 bg-light p-3 rounded-3 border">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Tanggal Diterima <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_diterima" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Jam Penerimaan</label>
                            <input type="time" name="jam" value="{{ date('H:i') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Sumber Informasi Indikasi <span class="text-danger">*</span></label>
                            <select name="sumber_indikasi" class="form-select form-select-sm" required>
                                <option value="WHISTLEBLOWING">Whistleblowing System (WBS)</option>
                                <option value="LAPORAN_CABANG">Laporan Pimpinan Cabang / Unit</option>
                                <option value="AUDIT_ONSITE">Temuan Onsite Audit (SOP 04)</option>
                                <option value="TEMUAN_OFFSITE">Analisis Data EWS Offsite (SOP 02)</option>
                                <option value="PENGADUAN_NASABAH">Pengaduan Nasabah Langsung</option>
                            </select>
                        </div>

                        <!-- Dropdown Unit Kerja Bank Sulteng -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Pilih Unit / Cabang Bank Sulteng <span class="text-danger">*</span></label>
                            <select id="select_unit_register" class="form-select form-select-sm" required onchange="
                                var opt = this.options[this.selectedIndex];
                                if(this.value){
                                    document.getElementById('reg_kode_unit').value = this.value;
                                    document.getElementById('reg_nama_unit').value = opt.getAttribute('data-nama');
                                }
                            ">
                                <option value="">-- Pilih Kantor Cabang / KCP / Kas --</option>
                                @foreach($units ?? [] as $u)
                                    <option value="{{ $u->unit_code ?? $u->kode_unit }}" data-nama="{{ $u->unit_name ?? $u->nama_unit }}">
                                        [{{ $u->unit_code ?? $u->kode_unit }}] {{ $u->unit_name ?? $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small text-muted">Memilih unit akan mengisi otomatis kolom Kode dan Nama Cabang di samping.</div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">Kode Unit <span class="text-danger">*</span></label>
                            <input type="text" name="kode_unit" id="reg_kode_unit" class="form-control form-control-sm bg-white" placeholder="Misal: 501" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Nama Unit / Cabang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_unit" id="reg_nama_unit" class="form-control form-control-sm bg-white" placeholder="Misal: KCP Bahomotefe" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Area / Bidang Operasional Terindikasi</label>
                            <input type="text" name="area" class="form-control form-control-sm" placeholder="Misal: Operasional Kas &amp; Teller / Kredit / DPK / ATM" list="listArea">
                            <datalist id="listArea">
                                <option value="Operasional Kas & Teller">
                                <option value="Kredit & Pembiayaan">
                                <option value="Dana Pihak Ketiga (DPK)">
                                <option value="Operasional Mesin ATM & Kartu">
                                <option value="Biaya & Pengeluaran Operasional">
                                <option value="Pelayanan & Customer Service">
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Klasifikasi Kerahasiaan Kasus</label>
                            <select name="klasifikasi_kerahasiaan" class="form-select form-select-sm">
                                <option value="RAHASIA" selected>RAHASIA (Terbatas Tim Fact-Finder)</option>
                                <option value="SANGAT_RAHASIA">SANGAT RAHASIA (Strict Privilege Kadiv SKAI)</option>
                                <option value="BIASA">INTERNAL SKAI</option>
                            </select>
                        </div>
                    </div>

                    <!-- SECTION 2: INDIKATOR KECURANGAN & PAPARAN FINANSIAL -->
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary rounded-circle p-2 me-2">2</span>
                        <h6 class="fw-bold text-primary mb-0">Indikator Kecurangan (Red Flag) &amp; Paparan Risiko</h6>
                    </div>
                    <div class="row g-3 mb-4 bg-light p-3 rounded-3 border">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Red Flag Utama (Indikator Kecurangan) <span class="text-danger">*</span></label>
                            <textarea name="red_flag_utama" class="form-control form-control-sm" rows="2" placeholder="Uraikan anomali atau gejala fraud utama, misal: Selisih kas fisik berulang, penarikan dormant mencurigakan..." required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Estimasi Paparan Kerugian Awal (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="nominal_awal" id="reg_nominal_awal" class="form-control form-control-sm" placeholder="0" required oninput="
                                var val = Number(this.value);
                                document.getElementById('preview_nominal').textContent = 'Terbaca: Rp ' + val.toLocaleString('id-ID');
                            ">
                            <div class="form-text small fw-semibold text-primary" id="preview_nominal">Terbaca: Rp 0</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Sensitivitas Reputasi Bank</label>
                            <select name="sensitivitas_reputasi" class="form-select form-select-sm">
                                <option value="RENDAH">RENDAH (Dampak lokal cabang)</option>
                                <option value="SEDANG" selected>SEDANG (Perlu koordinasi korwas)</option>
                                <option value="TINGGI">TINGGI (Potensi sorotan regulator OJK/BI/media)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Apakah Nasabah Terdampak Langsung?</label>
                            <select name="nasabah_terdampak" class="form-select form-select-sm">
                                <option value="TIDAK" selected>TIDAK (Kerugian internal bank)</option>
                                <option value="YA">YA (Melibatkan rekening/dana nasabah)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Melibatkan Pihak Kunci / Pimpinan?</label>
                            <select name="pihak_kunci_pimpinan" class="form-select form-select-sm">
                                <option value="TIDAK" selected>TIDAK (Level staf pelaksana)</option>
                                <option value="YA">YA (Pejabat pemutus / Pimpinan Unit)</option>
                            </select>
                        </div>
                    </div>

                    <!-- SECTION 3: ANALISIS AWAL FAKTA VS DUGAAN -->
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary rounded-circle p-2 me-2">3</span>
                        <h6 class="fw-bold text-primary mb-0">Verifikasi Fakta Awal vs Dugaan Modus</h6>
                    </div>
                    <div class="row g-3 bg-light p-3 rounded-3 border">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Fakta Awal Terverifikasi</label>
                            <textarea name="fakta_awal_terverifikasi" class="form-control form-control-sm" rows="3" placeholder="Fakta fisik yang sudah pasti ada (misal: ada dokumen slip palsu fisik, ada selisih kas fisik)..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Dugaan Awal (Belum Terverifikasi)</label>
                            <textarea name="dugaan_belum_terverifikasi" class="form-control form-control-sm" rows="3" placeholder="Dugaan skema/modus operandi yang masih perlu pendalaman bukti lebih lanjut..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Risiko Bukti Hilang / Dirampas / Dirusak</label>
                            <select name="risiko_bukti_hilang" class="form-select form-select-sm">
                                <option value="TINGGI">TINGGI (Segera lakukan pengamanan Chain of Custody)</option>
                                <option value="SEDANG" selected>SEDANG (Dokumen tersimpan di arsip cabang)</option>
                                <option value="RENDAH">RENDAH (Tersimpan aman di sistem CBS terpusat)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Catatan Awal Petugas / Auditor Penerima</label>
                            <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Catatan khusus arahan penanganan awal...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">
                        <i class="bi bi-save me-1"></i> Simpan Register Kasus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Register Indikasi -->
<div class="modal fade" id="modalDetailRegister" tabindex="-1" aria-labelledby="modalDetailRegisterLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold" id="modalDetailRegisterLabel">
                    <i class="bi bi-file-earmark-text me-2"></i> Lembar Kerja Detail Indikasi Fraud
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded border">
                    <div>
                        <span class="badge bg-primary fs-6 font-monospace px-3 py-2" id="dtl_id">-</span>
                        <span class="badge bg-secondary ms-2" id="dtl_status">-</span>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Tanggal Diterima</small>
                        <span class="fw-bold text-dark" id="dtl_waktu">-</span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Unit Kerja Terindikasi</label>
                        <div class="fw-bold text-dark" id="dtl_unit">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Area Operasional</label>
                        <div class="fw-semibold text-dark" id="dtl_area">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Sumber Informasi</label>
                        <div><span class="badge bg-info text-dark" id="dtl_sumber">-</span></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Estimasi Paparan Kerugian</label>
                        <div class="fw-bold text-danger fs-5" id="dtl_nominal">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Sensitivitas Reputasi</label>
                        <div><span class="badge bg-warning text-dark" id="dtl_sensitivitas">-</span></div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Nasabah Terdampak</label>
                        <div class="fw-semibold" id="dtl_nasabah">-</div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Keterlibatan Pimpinan</label>
                        <div class="fw-semibold" id="dtl_pimpinan">-</div>
                    </div>

                    <div class="col-md-12">
                        <hr class="my-2">
                        <label class="text-muted small fw-bold text-uppercase">Red Flag Utama (Indikator Kecurangan)</label>
                        <div class="p-2 bg-light rounded border text-dark fw-semibold" id="dtl_redflag">-</div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase">Fakta Awal Terverifikasi</label>
                        <div class="p-3 bg-light rounded border small text-dark" style="min-height: 80px;" id="dtl_fakta">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase">Dugaan Modus Belum Terverifikasi</label>
                        <div class="p-3 bg-light rounded border small text-dark" style="min-height: 80px;" id="dtl_dugaan">-</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <div>
                    <a href="{{ route('pemeriksaan_awal.triase') }}" class="btn btn-outline-primary btn-sm fw-semibold">
                        <i class="bi bi-diagram-3 me-1"></i> Buka Lembar Triase
                    </a>
                </div>
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Pindahkan modal ke luar layout utama agar tidak terganggu backdrop overlay
        var modalAdd = document.getElementById('modalAddRegister');
        if (modalAdd && modalAdd.parentElement !== document.body) {
            document.body.appendChild(modalAdd);
        }

        var modalDetail = document.getElementById('modalDetailRegister');
        if (modalDetail && modalDetail.parentElement !== document.body) {
            document.body.appendChild(modalDetail);
        }

        // Handler untuk tombol detail register
        document.querySelectorAll('.btn-detail-register').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('dtl_id').textContent = this.getAttribute('data-id');
                document.getElementById('dtl_status').textContent = this.getAttribute('data-status');
                document.getElementById('dtl_waktu').textContent = this.getAttribute('data-tgl') + ' (' + this.getAttribute('data-jam') + ' WITA)';
                document.getElementById('dtl_unit').textContent = this.getAttribute('data-unit');
                document.getElementById('dtl_area').textContent = this.getAttribute('data-area');
                document.getElementById('dtl_sumber').textContent = this.getAttribute('data-sumber');
                document.getElementById('dtl_nominal').textContent = this.getAttribute('data-nominal');
                document.getElementById('dtl_sensitivitas').textContent = this.getAttribute('data-sensitivitas');
                document.getElementById('dtl_nasabah').textContent = this.getAttribute('data-nasabah');
                document.getElementById('dtl_pimpinan').textContent = this.getAttribute('data-pimpinan');
                document.getElementById('dtl_redflag').textContent = this.getAttribute('data-redflag');
                document.getElementById('dtl_fakta').textContent = this.getAttribute('data-fakta');
                document.getElementById('dtl_dugaan').textContent = this.getAttribute('data-dugaan');
            });
        });
    });
</script>
@endpush
