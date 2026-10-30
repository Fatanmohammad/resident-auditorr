@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Modul -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-shield-check me-2"></i> CHAIN OF CUSTODY &amp; LOG BUKTI (WP-RA-09-C)
            </h4>
            <p class="text-muted small mb-0">
                Inventarisasi bukti fisik &amp; digital, penjagaan integritas, dan riwayat penguasaan bukti penanganan fraud
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pemeriksaan_awal.register') }}" class="btn btn-outline-primary btn-sm px-3 fw-semibold">
                <i class="bi bi-journal-plus me-1"></i> Register Indikasi
            </a>
            <button type="button" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddCustody">
                <i class="bi bi-plus-lg me-1"></i> Catat Bukti Baru
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

    <!-- Guidance Banner -->
    <div class="card border-0 bg-primary bg-opacity-10 shadow-sm d-flex align-items-center mb-4 rounded-3 p-3">
        <div class="d-flex align-items-center text-primary w-100">
            <i class="bi bi-info-circle-fill fs-4 me-3 flex-shrink-0"></i>
            <div class="small text-dark">
                <strong>Prinsip Ketat Chain of Custody (SOP 09):</strong> Seluruh bukti fisik (warkat, slip, agunan, buku tabungan) dan bukti elektronik (file dump CBS, rekaman CCTV) wajib terikat pada <strong>ID Indikasi Kasus</strong> resmi, dicatat tanggal pengamanan, lokasi penyimpanan brankas SKAI, serta dipelihara integritas fisiknya.
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-primary rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Total Barang Bukti</span>
                    <h3 class="fw-bold text-primary mb-0">{{ count($buktis ?? []) }}</h3>
                    <small class="text-muted">Item Bukti Terdaftar</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-success rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Kondisi Utuh / Tersegel</span>
                    <h3 class="fw-bold text-success mb-0">
                        {{ isset($buktis) ? $buktis->where('kondisi_bukti', 'UTUH')->count() : 0 }}
                    </h3>
                    <small class="text-muted">Integritas 100% Terjaga</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-dark rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Penyimpanan Brankas SKAI</span>
                    <h3 class="fw-bold text-dark mb-0">
                        {{ isset($buktis) ? $buktis->where('status_penguasaan', 'BRANKAS_SKAI')->count() : 0 }}
                    </h3>
                    <small class="text-muted">Tersimpan di Brankas Pusat</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-warning rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Dipinjam / Ditransfer</span>
                    <h3 class="fw-bold text-warning mb-0">
                        {{ isset($buktis) ? $buktis->where('status_penguasaan', '!=', 'BRANKAS_SKAI')->count() : 0 }}
                    </h3>
                    <small class="text-muted">Tim Investigasi / APH</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <form action="{{ route('pemeriksaan_awal.bukti_custody') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari kode bukti, uraian, unit, sumber...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="indikasi_id" class="form-select form-select-sm">
                        <option value="">-- Semua Indikasi Kasus --</option>
                        @foreach($indikasis ?? [] as $ind)
                            <option value="{{ $ind->indikasi_id }}" {{ request('indikasi_id') == $ind->indikasi_id ? 'selected' : '' }}>
                                {{ $ind->indikasi_id }} — {{ $ind->nama_unit ?? $ind->kode_unit }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="kategori_bukti" class="form-select form-select-sm">
                        <option value="">-- Semua Kategori Bukti --</option>
                        <option value="DOKUMEN_FISIK" {{ request('kategori_bukti') == 'DOKUMEN_FISIK' ? 'selected' : '' }}>Dokumen Fisik</option>
                        <option value="FILE_DIGITAL" {{ request('kategori_bukti') == 'FILE_DIGITAL' ? 'selected' : '' }}>File Digital / Data Dump</option>
                        <option value="REKAMAN_CCTV" {{ request('kategori_bukti') == 'REKAMAN_CCTV' ? 'selected' : '' }}>Rekaman CCTV / Video</option>
                        <option value="BARANG_FISIK" {{ request('kategori_bukti') == 'BARANG_FISIK' ? 'selected' : '' }}>Barang Fisik / Kartu / Alat</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['q', 'indikasi_id', 'kategori_bukti']))
                        <a href="{{ route('pemeriksaan_awal.bukti_custody') }}" class="btn btn-light btn-sm border" title="Reset filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Register Chain of Custody -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-journal-text me-2"></i> Register Barang Bukti Terdaftar
            </h6>
            <span class="badge bg-primary rounded-pill px-3">{{ count($buktis ?? []) }} Bukti Terdata</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Kode Bukti</th>
                            <th>ID Indikasi Kasus</th>
                            <th>Deskripsi Barang Bukti</th>
                            <th>Kategori</th>
                            <th>Tgl Diamankan</th>
                            <th>Kondisi Integritas</th>
                            <th>Lokasi Penyimpanan</th>
                            <th>Status Penguasaan</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($buktis ?? [] as $bukti)
                            <tr>
                                <td class="ps-3 fw-bold text-dark font-monospace">
                                    <span class="badge bg-light text-dark border">{{ $bukti->kode_bukti }}</span>
                                </td>
                                <td>
                                    <span class="text-primary font-monospace fw-bold d-block">{{ $bukti->indikasi_id }}</span>
                                    <small class="text-muted">{{ $bukti->unit ?? ($bukti->registerIndikasi->nama_unit ?? 'Unit Bank') }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $bukti->deskripsi_bukti }}</div>
                                    <small class="text-muted"><i class="bi bi-person-check me-1"></i>Dari: {{ $bukti->sumber_bukti }}</small>
                                </td>
                                <td>
                                    @php
                                        $kat = $bukti->kategori_bukti;
                                        $badgeClass = match($kat) {
                                            'DOKUMEN_FISIK' => 'bg-info bg-opacity-10 text-info border border-info',
                                            'FILE_DIGITAL' => 'bg-primary bg-opacity-10 text-primary border border-primary',
                                            'REKAMAN_CCTV' => 'bg-danger bg-opacity-10 text-danger border border-danger',
                                            default => 'bg-secondary bg-opacity-10 text-secondary border border-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ str_replace('_', ' ', $kat) }}</span>
                                </td>
                                <td>
                                    <span class="d-block text-dark">{{ $bukti->tanggal_diperoleh }}</span>
                                    <small class="text-muted">{{ $bukti->petugas_custodian }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $bukti->kondisi_bukti == 'UTUH' ? 'success' : 'danger' }}">
                                        <i class="bi bi-{{ $bukti->kondisi_bukti == 'UTUH' ? 'check-circle' : 'exclamation-circle' }} me-1"></i>
                                        {{ $bukti->kondisi_bukti }}
                                    </span>
                                </td>
                                <td>
                                    <small class="d-block text-dark fw-semibold">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $bukti->lokasi_penyimpanan }}
                                    </small>
                                </td>
                                <td>
                                    @if($bukti->status_penguasaan == 'BRANKAS_SKAI')
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">
                                            <i class="bi bi-safe2 me-1"></i> BRANKAS SKAI
                                        </span>
                                    @elseif($bukti->status_penguasaan == 'DIPINJAM_INVESTIGATOR')
                                        <span class="badge bg-warning bg-opacity-10 text-dark border border-warning">
                                            <i class="bi bi-person-badge me-1"></i> FACT-FINDING
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                            <i class="bi bi-building me-1"></i> DISERAHKAN APH
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary py-0 px-2 btn-detail-bukti"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalDetailBukti"
                                            data-kode="{{ $bukti->kode_bukti }}"
                                            data-indikasi="{{ $bukti->indikasi_id }}"
                                            data-unit="{{ $bukti->unit ?? ($bukti->registerIndikasi->nama_unit ?? 'Unit') }}"
                                            data-deskripsi="{{ $bukti->deskripsi_bukti }}"
                                            data-kategori="{{ $bukti->kategori_bukti }}"
                                            data-tanggal="{{ $bukti->tanggal_diperoleh }}"
                                            data-sumber="{{ $bukti->sumber_bukti }}"
                                            data-kondisi="{{ $bukti->kondisi_bukti }}"
                                            data-lokasi="{{ $bukti->lokasi_penyimpanan }}"
                                            data-penguasaan="{{ $bukti->status_penguasaan }}"
                                            data-custodian="{{ $bukti->petugas_custodian }}"
                                            data-catatan="{{ $bukti->catatan ?? '-' }}">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-shield-slash fs-2 d-block mb-2 text-secondary"></i> 
                                    Belum ada bukti fisik/elektronik yang dicatatkan dalam Chain of Custody.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Chain of Custody -->
<div class="modal fade" id="modalAddCustody" tabindex="-1" aria-labelledby="modalAddCustodyLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="{{ route('pemeriksaan_awal.bukti_custody.store') }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="modalAddCustodyLabel">
                    <i class="bi bi-shield-plus me-2"></i> Form Input Chain of Custody &amp; Log Bukti (WP-RA-09-C)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3 p-4">
                <!-- Dropdown ID Indikasi Fraud -->
                <div class="col-md-12">
                    <label class="form-label fw-bold small">
                        Pilih ID Indikasi Fraud <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <select name="indikasi_id" id="select_indikasi_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih ID Indikasi Kasus Fraud --</option>
                            @foreach($indikasis ?? [] as $ind)
                                <option value="{{ $ind->indikasi_id }}"
                                        data-unit="{{ $ind->nama_unit ?? $ind->kode_unit }}"
                                        data-kode-unit="{{ $ind->kode_unit ?? '-' }}"
                                        data-sumber="{{ $ind->sumber_indikasi }}"
                                        data-redflag="{{ $ind->red_flag_utama }}"
                                        data-nominal="{{ $ind->nominal_awal }}">
                                    {{ $ind->indikasi_id }} &bull; [{{ $ind->kode_unit ?? '-' }}] {{ $ind->nama_unit }} &bull; {{ Str::limit($ind->red_flag_utama ?? $ind->area, 45) }} (Rp {{ number_format($ind->nominal_awal ?? 0) }})
                                </option>
                            @endforeach
                        </select>
                        <a href="{{ route('pemeriksaan_awal.register') }}" target="_blank" class="btn btn-outline-secondary btn-sm" title="Buka Register Indikasi untuk menambah kasus baru">
                            <i class="bi bi-plus-circle me-1"></i> Register Baru
                        </a>
                    </div>
                    <div class="form-text small text-muted">
                        Pilihan ID diambil dari data Register Indikasi (WP-RA-09-A).
                    </div>
                </div>

                <!-- Info Box Kasus Terpilih -->
                <div class="col-md-12" id="infoBoxIndikasi" style="display: none;">
                    <div class="alert alert-light border border-primary border-opacity-25 rounded-3 py-2 px-3 mb-0 small text-dark bg-white">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle-fill text-primary me-2 fs-5 mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-primary mb-1" id="infoJudulKasus">Detail Indikasi Kasus</div>
                                <div class="text-muted" id="infoUnitKasus"></div>
                                <div class="fw-semibold text-danger mt-1" id="infoRedFlagKasus"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small">Kategori Bukti <span class="text-danger">*</span></label>
                    <select name="kategori_bukti" id="input_kategori_bukti" class="form-select form-select-sm" required>
                        <option value="DOKUMEN_FISIK">Dokumen Fisik (Slip / Warkat / SPK / Akad / Berkas Kredit)</option>
                        <option value="FILE_DIGITAL">File Digital / Database Dump CBS / Audit Trail Log</option>
                        <option value="REKAMAN_CCTV">Rekaman Video CCTV / Audio Recording</option>
                        <option value="BARANG_FISIK">Barang Fisik / Kartu ATM / Cash Tape / Cap Stempel</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small">Tanggal Pengamanan / Diperoleh <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_diperoleh" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold small">Deskripsi Spesifik Barang Bukti <span class="text-danger">*</span></label>
                    <textarea name="deskripsi_bukti" class="form-control form-control-sm" rows="2" placeholder="Uraikan detail fisik, nomor seri dokumen, tanggal transaksi, nama file, atau cap tanda tangan..." required></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small">Sumber / Diserahkan Oleh <span class="text-danger">*</span></label>
                    <input type="text" name="sumber_bukti" id="input_sumber_bukti" class="form-control form-control-sm" placeholder="Misal: Pimpinan Cabang / Teller / Pemilik Rekening / Security CCTV" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small">Integritas &amp; Kondisi Bukti</label>
                    <select name="kondisi_bukti" class="form-select form-select-sm">
                        <option value="UTUH">UTUH / TERSEGEL DENGAN BAIK</option>
                        <option value="RUSAK_SEBAGIAN">RUSAK SEBAGIAN / CACAT DOKUMEN</option>
                        <option value="FOTOKOPI_LEGALISIR">FOTOKOPI LEPAS / LEGALISIR</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small">Lokasi Penyimpanan Aman <span class="text-danger">*</span></label>
                    <input type="text" name="lokasi_penyimpanan" class="form-control form-control-sm" value="Brankas SKAI Kantor Pusat Lt. 3" list="listLokasi" required>
                    <datalist id="listLokasi">
                        <option value="Brankas SKAI Kantor Pusat Lt. 3">
                        <option value="Safe Box Khusus Bukti RA Cabang">
                        <option value="Server Storage Safe Terenkripsi SKAI">
                        <option value="Lemari Bersegel Arsip Investigasi">
                    </datalist>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small">Status Penguasaan Bukti Saat Ini</label>
                    <select name="status_penguasaan" class="form-select form-select-sm">
                        <option value="BRANKAS_SKAI">TERSIMPAN DI BRANKAS SKAI</option>
                        <option value="DIPINJAM_INVESTIGATOR">DIPINJAM TIM FACT-FINDING / AUDITOR</option>
                        <option value="DISERAHKAN_KEPOLISIAN">DISERAHKAN KE APARAT PENEGAK HUKUM (APH)</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small">Petugas Penerima / Custodian <span class="text-danger">*</span></label>
                    <input type="text" name="petugas_custodian" value="{{ auth()->user()->name ?? 'Andi (Resident Auditor)' }}" class="form-control form-control-sm" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small">Bentuk Bukti</label>
                    <select name="asli_salinan" class="form-select form-select-sm">
                        <option value="ASLI">DOKUMEN / BARANG ASLI</option>
                        <option value="SALINAN_LEGALISIR">SALINAN / COPY LEGALISIR</option>
                        <option value="SALINAN_DIGITAL">SALINAN ELEKTRONIK / EXPORT SYSTEM</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold small">Catatan Tambahan &amp; Nomor Segel Pengaman</label>
                    <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Misal: Dimasukkan amplop anti-tamper No. Segel: SKAI-0941">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">
                    <i class="bi bi-save me-1"></i> Simpan Log Bukti
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail Bukti -->
<div class="modal fade" id="modalDetailBukti" tabindex="-1" aria-labelledby="modalDetailBuktiLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h6 class="modal-title fw-bold" id="modalDetailBuktiLabel">
                    <i class="bi bi-file-earmark-text me-2"></i> Detail Bukti Chain of Custody
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-primary fs-6 px-3 py-2 font-monospace" id="detKodeBukti">-</span>
                    <span class="badge bg-secondary" id="detKategori">-</span>
                </div>
                <table class="table table-sm table-borderless small mb-0">
                    <tr>
                        <td class="text-muted" style="width: 38%;">ID Indikasi Kasus</td>
                        <td class="fw-bold text-primary font-monospace" id="detIndikasi">-</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Unit Kerja Terkait</td>
                        <td class="fw-semibold" id="detUnit">-</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Deskripsi Barang Bukti</td>
                        <td class="fw-semibold text-dark" id="detDeskripsi">-</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal Diamankan</td>
                        <td id="detTanggal">-</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Sumber / Diserahkan</td>
                        <td id="detSumber">-</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kondisi Integritas</td>
                        <td><span class="badge bg-success" id="detKondisi">UTUH</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lokasi Penyimpanan</td>
                        <td class="fw-semibold text-danger" id="detLokasi">-</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Penguasaan</td>
                        <td><span class="badge bg-primary" id="detPenguasaan">-</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Petugas Custodian</td>
                        <td id="detCustodian">-</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Catatan Integritas</td>
                        <td class="text-muted" id="detCatatan">-</td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Pindahkan modal ke luar kontainer untuk mencegah masalah overlay backdrop
        var modalAdd = document.getElementById('modalAddCustody');
        if (modalAdd && modalAdd.parentElement !== document.body) {
            document.body.appendChild(modalAdd);
        }

        var modalDetail = document.getElementById('modalDetailBukti');
        if (modalDetail && modalDetail.parentElement !== document.body) {
            document.body.appendChild(modalDetail);
        }

        // Listener saat memilih ID Indikasi Fraud
        var selectInd = document.getElementById('select_indikasi_id');
        var infoBox = document.getElementById('infoBoxIndikasi');
        var infoJudul = document.getElementById('infoJudulKasus');
        var infoUnit = document.getElementById('infoUnitKasus');
        var infoRedFlag = document.getElementById('infoRedFlagKasus');
        var inputSumber = document.getElementById('input_sumber_bukti');

        if (selectInd) {
            selectInd.addEventListener('change', function() {
                var selectedOption = this.options[this.selectedIndex];
                if (this.value) {
                    var unit = selectedOption.getAttribute('data-unit') || 'Unit Cabang';
                    var redflag = selectedOption.getAttribute('data-redflag') || '-';
                    var nominal = selectedOption.getAttribute('data-nominal') || '0';
                    var sumber = selectedOption.getAttribute('data-sumber') || '';

                    infoJudul.textContent = 'Indikasi: ' + this.value;
                    infoUnit.textContent = 'Unit Kerja: ' + unit + ' | Estimasi Paparan: Rp ' + Number(nominal).toLocaleString('id-ID');
                    infoRedFlag.textContent = 'Red Flag: ' + redflag;
                    infoBox.style.display = 'block';

                    if (inputSumber && !inputSumber.value) {
                        inputSumber.placeholder = 'Pimpinan / PIC ' + unit;
                    }
                } else {
                    infoBox.style.display = 'none';
                }
            });
        }

        // Handler untuk tombol detail bukti
        document.querySelectorAll('.btn-detail-bukti').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('detKodeBukti').textContent = this.getAttribute('data-kode');
                document.getElementById('detKategori').textContent = this.getAttribute('data-kategori');
                document.getElementById('detIndikasi').textContent = this.getAttribute('data-indikasi');
                document.getElementById('detUnit').textContent = this.getAttribute('data-unit');
                document.getElementById('detDeskripsi').textContent = this.getAttribute('data-deskripsi');
                document.getElementById('detTanggal').textContent = this.getAttribute('data-tanggal');
                document.getElementById('detSumber').textContent = this.getAttribute('data-sumber');
                document.getElementById('detKondisi').textContent = this.getAttribute('data-kondisi');
                document.getElementById('detLokasi').textContent = this.getAttribute('data-lokasi');
                document.getElementById('detPenguasaan').textContent = this.getAttribute('data-penguasaan');
                document.getElementById('detCustodian').textContent = this.getAttribute('data-custodian');
                document.getElementById('detCatatan').textContent = this.getAttribute('data-catatan');
            });
        });
    });
</script>
@endpush
