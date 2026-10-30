@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Modul -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-diagram-3 me-2"></i> TRIASE AWAL INDIKASI FRAUD (WP-RA-09-B)
            </h4>
            <p class="text-muted small mb-0">
                Penilaian urgensi, analisis skor prioritas risiko, dan penetapan disposisi tindak lanjut fact-finding SKAI
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pemeriksaan_awal.register') }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
                <i class="bi bi-journal-plus me-1"></i> Register Indikasi
            </a>
            <a href="{{ route('pemeriksaan_awal.bukti_custody') }}" class="btn btn-outline-primary btn-sm px-3 fw-semibold">
                <i class="bi bi-shield-check me-1"></i> Chain of Custody
            </a>
            <button type="button" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddTriase">
                <i class="bi bi-plus-lg me-1"></i> Tambah Triase Awal
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
                <a href="{{ route('pemeriksaan_awal.register') }}" class="text-decoration-none text-muted small fw-semibold me-2">
                    <i class="bi bi-1-circle me-1"></i> Register Indikasi
                </a>
                <i class="bi bi-chevron-right text-muted me-2"></i>
                <span class="badge bg-primary rounded-pill px-3 py-2 me-2 fw-semibold">
                    <i class="bi bi-2-circle-fill me-1"></i> Triase &amp; Skoring
                </span>
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
                <i class="bi bi-info-circle me-1 text-primary"></i> Standar Formulir Kerja: <strong>WP-RA-09-B</strong>
            </div>
        </div>
    </div>

    <!-- Parameter Formula Banner -->
    <div class="card border-0 bg-primary bg-opacity-10 shadow-sm rounded-3 mb-4 p-3">
        <div class="d-flex align-items-start text-primary">
            <i class="bi bi-sliders fs-4 me-3 flex-shrink-0 mt-1"></i>
            <div class="small text-dark">
                <strong>Matriks Skor Prioritas Triase (SOP 09):</strong>
                Skor dihitung otomatis berdasarkan 4 bobot risiko:
                <span class="badge bg-white text-danger border border-danger ms-1">Risiko Hilang Bukti (+3)</span>
                <span class="badge bg-white text-primary border border-primary ms-1">Pola Berulang (+2)</span>
                <span class="badge bg-white text-warning text-dark border border-warning ms-1">Dampak Nasabah (+2)</span>
                <span class="badge bg-white text-dark border ms-1">Pihak Kunci/Pimpinan (+3)</span>.
                Jika <strong>Skor &ge; 5</strong> atau <strong>Risiko Bukti TINGGI</strong>, status otomatis ditetapkan <strong class="text-danger">URGENT</strong> untuk pengamanan segera barang bukti fisik/digital.
            </div>
        </div>
    </div>

    <!-- 4 Kartu Statistik Ringkasan Triase -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-danger rounded-3 h-100">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Kasus Urgent / Segera</span>
                    <h3 class="fw-bold text-danger mb-1">{{ $triases->where('urgent', 'YA')->count() }}</h3>
                    <small class="text-muted">Prioritas Investigasi Segera</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-warning rounded-3 h-100">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Rata-rata Skor Prioritas</span>
                    <h3 class="fw-bold text-dark mb-1">
                        {{ number_format($triases->avg('skor_prioritas') ?? 0, 1) }} <span class="fs-6 text-muted">/ 10</span>
                    </h3>
                    <small class="text-muted">Indeks Tingkat Keparahan</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-success rounded-3 h-100">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Lanjut Fact-Finding</span>
                    <h3 class="fw-bold text-success mb-1">
                        {{ $triases->where('keputusan_triase', 'LANJUT_FACT_FINDING')->count() + $triases->where('keputusan_triase', 'INVESTIGASI_LANJUT')->count() }}
                    </h3>
                    <small class="text-muted">Diteruskan ke Pendalaman Bukti</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-secondary rounded-3 h-100">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Arsip / Monitoring</span>
                    <h3 class="fw-bold text-secondary mb-1">
                        {{ $triases->whereIn('keputusan_triase', ['ARSIP', 'MONITORING_OFFSITE'])->count() }}
                    </h3>
                    <small class="text-muted">Dialihkan atau Ditutup Awal</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <form action="{{ route('pemeriksaan_awal.triase') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari ID indikasi, unit cabang, sumber, saran...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="urgent" class="form-select form-select-sm">
                        <option value="">-- Semua Urgensi --</option>
                        <option value="YA" {{ request('urgent') == 'YA' ? 'selected' : '' }}>URGENT (Skor &ge; 5)</option>
                        <option value="TIDAK" {{ request('urgent') == 'TIDAK' ? 'selected' : '' }}>NORMAL (Skor &lt; 5)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="keputusan" class="form-select form-select-sm">
                        <option value="">-- Keputusan --</option>
                        <option value="LANJUT_FACT_FINDING" {{ request('keputusan') == 'LANJUT_FACT_FINDING' ? 'selected' : '' }}>Lanjut Fact-Finding</option>
                        <option value="INVESTIGASI_LANJUT" {{ request('keputusan') == 'INVESTIGASI_LANJUT' ? 'selected' : '' }}>Investigasi Lanjut</option>
                        <option value="MONITORING_OFFSITE" {{ request('keputusan') == 'MONITORING_OFFSITE' ? 'selected' : '' }}>Monitoring Offsite</option>
                        <option value="ARSIP" {{ request('keputusan') == 'ARSIP' ? 'selected' : '' }}>Arsip</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['q', 'urgent', 'keputusan']))
                        <a href="{{ route('pemeriksaan_awal.triase') }}" class="btn btn-light btn-sm border" title="Reset filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data Triase Awal -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-table me-2 text-primary"></i> Register Lembar Kerja Triase Awal (WP-RA-09-B)
                </h6>
                <small class="text-muted">Hasil evaluasi pembobotan risiko dan disposisi fact-finding</small>
            </div>
            <span class="badge bg-primary rounded-pill px-3 py-2">{{ count($triases ?? []) }} Data Terdata</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 150px;">ID Indikasi Kasus</th>
                            <th>Tanggal &amp; Unit Kerja</th>
                            <th class="text-center" style="width: 100px;">Skor Prioritas</th>
                            <th>Status Urgensi</th>
                            <th>Risiko Hilang Bukti</th>
                            <th>Saran Otomatis Sistem</th>
                            <th>Keputusan Triase</th>
                            <th class="text-end pe-3" style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($triases as $item)
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-light text-primary border border-primary font-monospace fw-bold px-2 py-1">
                                        {{ $item->indikasi_id }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark d-block">{{ $item->unit }}</span>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-check me-1"></i>{{ $item->tanggal }} &bull;
                                        <span class="badge bg-light text-secondary border">{{ $item->sumber ?? 'Audit SKAI' }}</span>
                                    </small>
                                </td>
                                <td class="text-center">
                                    @php
                                        $skor = $item->skor_prioritas ?? 0;
                                        $skorClass = $skor >= 5 ? 'bg-danger text-white' : ($skor >= 3 ? 'bg-warning text-dark' : 'bg-success text-white');
                                    @endphp
                                    <span class="badge {{ $skorClass }} rounded-pill fs-6 px-3 py-1">
                                        {{ $skor }} <small class="fw-normal" style="font-size: 10px;">/10</small>
                                    </span>
                                </td>
                                <td>
                                    @if($item->urgent == 'YA')
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1 fw-bold">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> URGENT
                                        </span>
                                    @else
                                        <span class="badge bg-info bg-opacity-10 text-primary border border-info px-2 py-1">
                                            <i class="bi bi-clock me-1"></i> NORMAL
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $rh = $item->risiko_bukti_hilang;
                                        $rhClass = match($rh) {
                                            'TINGGI' => 'bg-danger',
                                            'SEDANG' => 'bg-warning text-dark',
                                            default => 'bg-success',
                                        };
                                    @endphp
                                    <span class="badge {{ $rhClass }}">
                                        {{ $rh }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark" style="max-width: 240px;">
                                        {{ $item->saran_sistem ?? 'Penelusuran bukti dan klarifikasi auditee' }}
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $kep = $item->keputusan_triase;
                                        $kepClass = match($kep) {
                                            'LANJUT_FACT_FINDING', 'INVESTIGASI_LANJUT' => 'bg-primary bg-opacity-10 text-primary border border-primary',
                                            'MONITORING_OFFSITE' => 'bg-warning bg-opacity-10 text-dark border border-warning',
                                            default => 'bg-secondary bg-opacity-10 text-secondary border border-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $kepClass }} px-2 py-1">
                                        {{ str_replace('_', ' ', $kep) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" 
                                                class="btn btn-outline-secondary py-0 px-2 btn-detail-triase"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDetailTriase"
                                                data-id="{{ $item->indikasi_id }}"
                                                data-tgl="{{ $item->tanggal }}"
                                                data-unit="{{ $item->unit }}"
                                                data-sumber="{{ $item->sumber ?? '-' }}"
                                                data-skor="{{ $item->skor_prioritas }}"
                                                data-urgent="{{ $item->urgent }}"
                                                data-risiko="{{ $item->risiko_bukti_hilang }}"
                                                data-pola="{{ $item->pola_berulang ?? 'TIDAK' }}"
                                                data-nasabah="{{ $item->nasabah ?? 'TIDAK' }}"
                                                data-pimpinan="{{ $item->pihak_kunci_pimpinan ?? 'TIDAK' }}"
                                                data-saran="{{ $item->saran_sistem }}"
                                                data-keputusan="{{ $item->keputusan_triase }}"
                                                data-catatan="{{ $item->dasar_keputusan_catatan ?? '-' }}"
                                                title="Lihat Detail Triase">
                                            <i class="bi bi-eye"></i> Detail
                                        </button>
                                        <a href="{{ route('pemeriksaan_awal.bukti_custody', ['indikasi_id' => $item->indikasi_id]) }}" 
                                           class="btn btn-outline-primary py-0 px-2" 
                                           title="Amankan Bukti ke Chain of Custody">
                                            <i class="bi bi-shield-check"></i>
                                        </a>
                                        <a href="{{ route('pemeriksaan_awal.kka') }}" 
                                           class="btn btn-outline-success py-0 px-2" 
                                           title="Buka KKA Pemeriksaan">
                                            <i class="bi bi-journal-check"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-diagram-3 fs-1 d-block mb-2 text-secondary"></i>
                                    <strong>Belum ada data lembar kerja triase awal yang tersimpan.</strong>
                                    <p class="small text-muted mb-3">Gunakan form di bawah untuk mulai menilai kasus indikasi dari register.</p>
                                    <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalAddTriase">
                                        <i class="bi bi-plus-lg me-1"></i> Tambah Triase Awal
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

<!-- Modal Form Tambah Triase Awal (Interactive Live Calculator) -->
<div class="modal fade" id="modalAddTriase" tabindex="-1" aria-labelledby="modalAddTriaseLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('pemeriksaan_awal.triase.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold" id="modalAddTriaseLabel">
                        <i class="bi bi-diagram-3 me-2"></i> Form Triase Awal &amp; Penilaian Prioritas (WP-RA-09-B)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- SECTION 1: KASUS TERINDIKASI -->
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary rounded-circle p-2 me-2">1</span>
                        <h6 class="fw-bold text-primary mb-0">Identitas Kasus yang Ditriase</h6>
                    </div>
                    <div class="row g-3 mb-4 bg-light p-3 rounded-3 border">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Pilih Kasus Indikasi Fraud <span class="text-danger">*</span></label>
                            <select name="indikasi_id" id="triase_select_indikasi" class="form-select form-select-sm" required onchange="
                                var opt = this.options[this.selectedIndex];
                                if(this.value){
                                    document.getElementById('triase_unit').value = opt.getAttribute('data-unit') || '';
                                    document.getElementById('triase_sumber').value = opt.getAttribute('data-sumber') || '';
                                    document.getElementById('triase_nominal').value = opt.getAttribute('data-nominal') || '0';
                                    
                                    // Set auto default parameter
                                    var nasabahVal = opt.getAttribute('data-nasabah') || 'TIDAK';
                                    document.getElementById('param_nasabah').value = nasabahVal;
                                    var pimpinanVal = opt.getAttribute('data-pimpinan') || 'TIDAK';
                                    document.getElementById('param_pimpinan').value = pimpinanVal;
                                    var risikoVal = opt.getAttribute('data-risiko') || 'SEDANG';
                                    document.getElementById('param_risiko').value = risikoVal;
                                    
                                    calcTriaseScore();
                                }
                            ">
                                <option value="">-- Pilih Kasus Indikasi dari Register --</option>
                                @foreach($indikasis as $ind)
                                    <option value="{{ $ind->indikasi_id }}" 
                                            data-unit="[{{ $ind->kode_unit ?? '-' }}] {{ $ind->nama_unit }}" 
                                            data-sumber="{{ $ind->sumber_indikasi }}"
                                            data-nominal="{{ $ind->nominal_awal }}"
                                            data-nasabah="{{ $ind->nasabah_terdampak ?? 'TIDAK' }}"
                                            data-pimpinan="{{ $ind->pihak_kunci_pimpinan ?? 'TIDAK' }}"
                                            data-risiko="{{ $ind->risiko_bukti_hilang ?? 'SEDANG' }}">
                                        {{ $ind->indikasi_id }} &bull; [{{ $ind->kode_unit ?? '-' }}] {{ $ind->nama_unit }} &bull; {{ Str::limit($ind->red_flag_utama ?? $ind->area, 45) }} (Rp {{ number_format($ind->nominal_awal ?? 0) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Tanggal Triase <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Unit / Cabang Terkait</label>
                            <input type="text" name="unit" id="triase_unit" class="form-control form-control-sm bg-white" placeholder="Otomatis terisi..." required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Sumber Indikasi</label>
                            <input type="text" name="sumber" id="triase_sumber" class="form-control form-control-sm bg-white" placeholder="Otomatis terisi...">
                        </div>
                        <div class="col-md-12">
                            <input type="hidden" name="nominal_awal" id="triase_nominal" value="0">
                        </div>
                    </div>

                    <!-- SECTION 2: PARAMETER SKOR & LIVE CALCULATOR -->
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary rounded-circle p-2 me-2">2</span>
                        <h6 class="fw-bold text-primary mb-0">Parameter Penilaian Prioritas (Live Calculator)</h6>
                    </div>
                    <div class="row g-3 mb-4 bg-light p-3 rounded-3 border">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Risiko Hilang Bukti / Alterasi <span class="text-danger">*</span></label>
                            <select name="risiko_bukti_hilang" id="param_risiko" class="form-select form-select-sm" onchange="calcTriaseScore()">
                                <option value="RENDAH">RENDAH (+0 Poin)</option>
                                <option value="SEDANG" selected>SEDANG (+1 Poin)</option>
                                <option value="TINGGI">TINGGI (+3 Poin) — Auto URGENT</option>
                            </select>
                            <div class="form-text small text-muted">Jika risiko fisik hilang tinggi, sistem otomatis menandai status URGENT.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Pola Berulang (Repeat Anomaly) <span class="text-danger">*</span></label>
                            <select name="pola_berulang" id="param_pola" class="form-select form-select-sm" onchange="calcTriaseScore()">
                                <option value="TIDAK" selected>TIDAK (+0 Poin)</option>
                                <option value="YA">YA (+2 Poin) — Terjadi berulang kali</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Melibatkan Nasabah Langsung <span class="text-danger">*</span></label>
                            <select name="nasabah" id="param_nasabah" class="form-select form-select-sm" onchange="calcTriaseScore()">
                                <option value="TIDAK" selected>TIDAK (+0 Poin)</option>
                                <option value="YA">YA (+2 Poin) — Merugikan dana/rekening nasabah</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Melibatkan Pimpinan / Pihak Kunci <span class="text-danger">*</span></label>
                            <select name="pihak_kunci_pimpinan" id="param_pimpinan" class="form-select form-select-sm" onchange="calcTriaseScore()">
                                <option value="TIDAK" selected>TIDAK (+0 Poin)</option>
                                <option value="YA">YA (+3 Poin) — Melibatkan level pimpinan/otorisator</option>
                            </select>
                        </div>

                        <!-- LIVE SCORE PREVIEW CARD -->
                        <div class="col-md-12 mt-3">
                            <div class="card border border-primary border-opacity-25 rounded-3 bg-white p-3 shadow-sm">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="text-center px-3 py-2 bg-light rounded border">
                                            <span class="text-muted d-block small fw-bold">TOTAL SKOR</span>
                                            <span class="fw-bold fs-4 text-primary" id="calcScoreDisplay">1</span>
                                            <small class="text-muted">/ 10</small>
                                        </div>
                                        <div>
                                            <span class="text-muted small d-block">Status Urgensi Dihitung:</span>
                                            <span class="badge bg-info text-dark fs-6 px-3 py-1" id="calcUrgentBadge">NORMAL</span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-muted small d-block">Rekomendasi Algoritma Sistem:</span>
                                        <strong class="text-dark" id="calcSaranDisplay">MONITORING BERKALA ATAU ARSIP KASUS</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: KEPUTUSAN TRIASE -->
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary rounded-circle p-2 me-2">3</span>
                        <h6 class="fw-bold text-primary mb-0">Penetapan Keputusan Disposisi Triase</h6>
                    </div>
                    <div class="row g-3 bg-light p-3 rounded-3 border">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Keputusan Triase <span class="text-danger">*</span></label>
                            <select name="keputusan_triase" id="triase_keputusan" class="form-select form-select-sm" required>
                                <option value="LANJUT_FACT_FINDING" selected>LANJUT FACT-FINDING (Klarifikasi &amp; Bukti)</option>
                                <option value="INVESTIGASI_LANJUT">INVESTIGASI LANJUT (Eskalasi ke Investigasi SKAI)</option>
                                <option value="MONITORING_OFFSITE">DIALIHKAN KE MONITORING OFFSITE (SOP 02)</option>
                                <option value="ARSIP">ARSIP / KASUS TIDAK CUKUP BUKTI</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Batas Waktu Triase / Target Selesai</label>
                            <input type="date" name="batas_triase" value="{{ date('Y-m-d', strtotime('+3 days')) }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Dasar Pertimbangan Keputusan &amp; Catatan Khusus</label>
                            <textarea name="dasar_keputusan_catatan" class="form-control form-control-sm" rows="2" placeholder="Uraikan dasar penetapan keputusan, rekomendasi langkah awal pengamanan bukti..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">
                        <i class="bi bi-save me-1"></i> Simpan Triase Awal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Triase Awal -->
<div class="modal fade" id="modalDetailTriase" tabindex="-1" aria-labelledby="modalDetailTriaseLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold" id="modalDetailTriaseLabel">
                    <i class="bi bi-file-earmark-check me-2"></i> Rincian Lembar Kerja Triase Awal (WP-RA-09-B)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded border">
                    <div>
                        <span class="badge bg-primary fs-6 font-monospace px-3 py-2" id="dt_tri_id">-</span>
                        <span class="badge bg-danger ms-2 fs-6" id="dt_tri_urgent">-</span>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Tanggal Triase</small>
                        <span class="fw-bold text-dark" id="dt_tri_tgl">-</span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Unit Kerja Terkait</label>
                        <div class="fw-bold text-dark" id="dt_tri_unit">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Sumber Informasi</label>
                        <div class="fw-semibold text-primary" id="dt_tri_sumber">-</div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Skor Prioritas</label>
                        <div><span class="badge bg-danger fs-5 px-3 py-1" id="dt_tri_skor">-</span></div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Risiko Bukti Hilang</label>
                        <div class="fw-semibold" id="dt_tri_risiko">-</div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Pola Berulang</label>
                        <div class="fw-semibold" id="dt_tri_pola">-</div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Dampak Nasabah</label>
                        <div class="fw-semibold" id="dt_tri_nasabah">-</div>
                    </div>

                    <div class="col-md-12">
                        <hr class="my-2">
                        <label class="text-muted small fw-bold text-uppercase">Rekomendasi Algoritma Sistem</label>
                        <div class="p-2 bg-light rounded border text-dark fw-bold" id="dt_tri_saran">-</div>
                    </div>

                    <div class="col-md-12">
                        <label class="text-muted small fw-bold text-uppercase">Keputusan Triase Final</label>
                        <div class="p-2 bg-primary bg-opacity-10 text-primary border border-primary rounded fw-bold" id="dt_tri_keputusan">-</div>
                    </div>

                    <div class="col-md-12">
                        <label class="text-muted small fw-bold text-uppercase">Dasar Pertimbangan Keputusan</label>
                        <div class="p-3 bg-light rounded border small text-dark" id="dt_tri_catatan">-</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <div>
                    <a href="{{ route('pemeriksaan_awal.bukti_custody') }}" class="btn btn-outline-primary btn-sm fw-semibold">
                        <i class="bi bi-shield-check me-1"></i> Buka Chain of Custody
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
    // Fungsi Kalkulasi Live Skor Prioritas Triase
    function calcTriaseScore() {
        var risiko = document.getElementById('param_risiko').value;
        var pola = document.getElementById('param_pola').value;
        var nasabah = document.getElementById('param_nasabah').value;
        var pimpinan = document.getElementById('param_pimpinan').value;

        var skor = 0;
        if (risiko === 'TINGGI') skor += 3;
        else if (risiko === 'SEDANG') skor += 1;

        if (pola === 'YA') skor += 2;
        if (nasabah === 'YA') skor += 2;
        if (pimpinan === 'YA') skor += 3;

        var isUrgent = (skor >= 5 || risiko === 'TINGGI');

        document.getElementById('calcScoreDisplay').textContent = skor;

        var badge = document.getElementById('calcUrgentBadge');
        if (isUrgent) {
            badge.className = 'badge bg-danger text-white fs-6 px-3 py-1';
            badge.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> URGENT';
        } else {
            badge.className = 'badge bg-info text-dark fs-6 px-3 py-1';
            badge.innerHTML = '<i class="bi bi-clock me-1"></i> NORMAL';
        }

        var saran = document.getElementById('calcSaranDisplay');
        var selectKep = document.getElementById('triase_keputusan');
        if (isUrgent) {
            saran.textContent = 'INVESTIGASI SEGERA & AMANKAN BUKTI (PRIORITAS TINGGI)';
            saran.className = 'text-danger fw-bold';
            if (selectKep) selectKep.value = 'INVESTIGASI_LANJUT';
        } else if (skor >= 3) {
            saran.textContent = 'FACT-FINDING MENDALAM DENGAN KLARIFIKASI AUDITEE';
            saran.className = 'text-primary fw-bold';
            if (selectKep) selectKep.value = 'LANJUT_FACT_FINDING';
        } else {
            saran.textContent = 'MONITORING BERKALA ATAU ARSIP KASUS';
            saran.className = 'text-secondary fw-bold';
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Pindahkan modal ke luar layout utama
        var modalAdd = document.getElementById('modalAddTriase');
        if (modalAdd && modalAdd.parentElement !== document.body) {
            document.body.appendChild(modalAdd);
        }

        var modalDetail = document.getElementById('modalDetailTriase');
        if (modalDetail && modalDetail.parentElement !== document.body) {
            document.body.appendChild(modalDetail);
        }

        calcTriaseScore();

        // Handler untuk tombol detail triase
        document.querySelectorAll('.btn-detail-triase').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('dt_tri_id').textContent = this.getAttribute('data-id');
                document.getElementById('dt_tri_tgl').textContent = this.getAttribute('data-tgl');
                document.getElementById('dt_tri_unit').textContent = this.getAttribute('data-unit');
                document.getElementById('dt_tri_sumber').textContent = this.getAttribute('data-sumber');
                document.getElementById('dt_tri_skor').textContent = this.getAttribute('data-skor') + ' / 10';
                
                var urg = this.getAttribute('data-urgent');
                var urgEl = document.getElementById('dt_tri_urgent');
                if (urg === 'YA') {
                    urgEl.className = 'badge bg-danger ms-2 fs-6';
                    urgEl.textContent = 'URGENT';
                } else {
                    urgEl.className = 'badge bg-info text-dark ms-2 fs-6';
                    urgEl.textContent = 'NORMAL';
                }

                document.getElementById('dt_tri_risiko').textContent = this.getAttribute('data-risiko');
                document.getElementById('dt_tri_pola').textContent = this.getAttribute('data-pola');
                document.getElementById('dt_tri_nasabah').textContent = this.getAttribute('data-nasabah');
                document.getElementById('dt_tri_saran').textContent = this.getAttribute('data-saran') || '-';
                document.getElementById('dt_tri_keputusan').textContent = this.getAttribute('data-keputusan');
                document.getElementById('dt_tri_catatan').textContent = this.getAttribute('data-catatan');
            });
        });
    });
</script>
@endpush
