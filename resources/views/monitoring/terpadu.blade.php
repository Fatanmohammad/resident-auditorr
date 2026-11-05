@extends('layouts.app')

@section('title', 'Monitoring Tindak Lanjut Terpadu')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-clock-history text-primary me-2"></i> Monitoring Tindak Lanjut Terpadu
            </h2>
            <p class="text-muted mb-0">Halaman Tunggal Pengawasan Tindak Lanjut: Temuan Audit Internal (Offsite, Onsite, Surprise Audit) & Temuan Regulator (OJK / BI / BPK)</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Tombol Download Template Excel -->
            <a href="{{ route('monitoring.template_excel', $currentTab) }}" class="btn btn-outline-success rounded-pill px-3 shadow-sm">
                <i class="bi bi-download me-1"></i> Unduh Template Excel ({{ ucfirst($currentTab) }})
            </a>
            <!-- Tombol Modal Upload Excel -->
            <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalUploadExcel">
                <i class="bi bi-file-earmark-arrow-up me-1"></i> Upload Excel {{ ucfirst($currentTab) }}
            </button>
        </div>
    </div>

    <!-- Alert Sukses & Error -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
        <i class="bi bi-exclamation-octagon-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- TAB PILIHAN SUB: INTERNAL VS REGULATOR (Poin 10 & 11) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-2">
            <ul class="nav nav-pills nav-fill gap-2" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link py-3 rounded-3 text-start {{ $currentTab === 'internal' ? 'active bg-primary fw-bold text-white shadow-sm' : 'bg-light text-dark' }}" 
                       href="{{ route('monitoring.terpadu', array_merge(request()->query(), ['tab' => 'internal'])) }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-building-check fs-5 me-2"></i>
                                <span class="fs-6">SUB 1: TEMUAN INTERNAL (SKAI & SURPRISE AUDIT)</span>
                                <div class="small fw-normal mt-1 opacity-75">Hasil Temuan KKA Surprise Audit, Offsite CBS, dan Audit Onsite</div>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $currentTab === 'internal' ? 'bg-light text-dark' : 'bg-secondary' }} px-3 py-2 fs-7">
                                    {{ $totalInternal }} Temuan
                                </span>
                                <div class="small mt-1">
                                    <span class="badge bg-danger">Belum TL: {{ $belumTlInternal }}</span>
                                    <span class="badge bg-success">Sudah TL: {{ $sudahTlInternal }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 rounded-3 text-start {{ $currentTab === 'regulator' ? 'active bg-danger fw-bold text-white shadow-sm' : 'bg-light text-dark' }}" 
                       href="{{ route('monitoring.terpadu', array_merge(request()->query(), ['tab' => 'regulator'])) }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-bank2 fs-5 me-2"></i>
                                <span class="fs-6">SUB 2: TEMUAN REGULATOR (OJK / BI / BPK / KAP)</span>
                                <div class="small fw-normal mt-1 opacity-75">Pemeriksaan Eksternal dan Pengawasan Otoritas Jasa Keuangan</div>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $currentTab === 'regulator' ? 'bg-light text-dark' : 'bg-secondary' }} px-3 py-2 fs-7">
                                    {{ $totalRegulator }} Temuan
                                </span>
                                <div class="small mt-1">
                                    <span class="badge bg-danger">Belum TL: {{ $belumTlRegulator }}</span>
                                    <span class="badge bg-success">Sudah TL: {{ $sudahTlRegulator }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- FILTER TOOLBAR SATU-SATUNYA (Poin 11) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white p-3">
        <form method="GET" action="{{ route('monitoring.terpadu') }}">
            <input type="hidden" name="tab" value="{{ $currentTab }}">
            <div class="row g-2 align-items-center">
                <!-- Filter Status Tindak Lanjut -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Status Tindak Lanjut:</label>
                    <div class="btn-group w-100" role="group">
                        <a href="{{ route('monitoring.terpadu', array_merge(request()->query(), ['status' => 'all'])) }}" 
                           class="btn btn-sm {{ $statusFilter === 'all' ? 'btn-dark fw-bold' : 'btn-outline-secondary' }}">
                            Semua
                        </a>
                        <a href="{{ route('monitoring.terpadu', array_merge(request()->query(), ['status' => 'belum_tl'])) }}" 
                           class="btn btn-sm {{ $statusFilter === 'belum_tl' ? 'btn-danger fw-bold' : 'btn-outline-danger' }}">
                            <i class="bi bi-hourglass-split"></i> Belum TL
                        </a>
                        <a href="{{ route('monitoring.terpadu', array_merge(request()->query(), ['status' => 'sudah_tl'])) }}" 
                           class="btn btn-sm {{ $statusFilter === 'sudah_tl' ? 'btn-success fw-bold' : 'btn-outline-success' }}">
                            <i class="bi bi-check2-circle"></i> Sudah TL
                        </a>
                    </div>
                </div>

                <!-- Filter Unit / Cabang -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Pilih Unit Kerja / Cabang:</label>
                    <select name="unit" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Seluruh Cabang --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->unit_name }}" {{ $unitFilter === $u->unit_name ? 'selected' : '' }}>
                                {{ $u->unit_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Tahun -->
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Tahun Laporan:</label>
                    <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Tahun --</option>
                        @for($y = date('Y'); $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ $tahunFilter == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Pencarian Cepat -->
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted mb-1">Cari Temuan / No Laporan:</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Ketik kata kunci..." value="{{ $search }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                        @if($search || $unitFilter || $tahunFilter || $statusFilter !== 'all')
                            <a href="{{ route('monitoring.terpadu', ['tab' => $currentTab]) }}" class="btn btn-outline-secondary" title="Reset Filter">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- TABEL DATA TEMUAN MONITORING -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-table me-2 text-warning"></i> 
                Daftar Temuan {{ $currentTab === 'regulator' ? 'Regulator' : 'Internal' }}
                @if($statusFilter === 'belum_tl') <span class="badge bg-danger ms-2">Filter: Belum Tindak Lanjut</span>
                @elseif($statusFilter === 'sudah_tl') <span class="badge bg-success ms-2">Filter: Sudah Tindak Lanjut</span>
                @endif
            </span>
            <span class="badge bg-light text-dark">Total: {{ $items->total() }} Data</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="table-light text-uppercase text-secondary text-center">
                        <tr>
                            <th style="width: 140px;" class="ps-3 text-start">TL ID / Sumber</th>
                            <th style="width: 170px;" class="text-start">Cabang / Unit</th>
                            <th style="width: 170px;" class="text-start">No. Laporan & Tanggal</th>
                            <th class="text-start">Uraian Temuan & Rekomendasi</th>
                            <th class="text-start" style="width: 210px;">Komitmen & Target Auditee</th>
                            <th style="width: 140px;">Status Tindak Lanjut (Poin 7)</th>
                            <th style="width: 120px;" class="pe-3">Aksi & Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $row)
                        @php
                            $isClosed = in_array(strtoupper(trim($row->status_final ?? '')), ['CLOSED', 'PROPOSED CLOSED']);
                            $badgeStatus = $isClosed ? 'bg-success text-white' : 'bg-danger text-white';
                            $statusTl = $row->status_tindak_lanjut ?: ($isClosed ? 'SUDAH SELESAI' : 'DALAM PROSES');
                        @endphp
                        <tr>
                            <td class="ps-3 fw-bold text-start">
                                <span class="text-primary">{{ $row->tl_id }}</span>
                                <div class="small text-muted fw-normal">
                                    <span class="badge bg-light text-dark border">{{ $row->sumber_tl }}</span>
                                </div>
                            </td>
                            <td class="text-start fw-semibold">
                                {{ $row->nama_unit ?? 'Bank Sulteng' }}
                            </td>
                            <td class="text-start small">
                                <div class="fw-bold">{{ $row->nomor_laporan ?? '-' }}</div>
                                <div class="text-muted">{{ $row->tanggal_laporan ? date('d/m/Y', strtotime($row->tanggal_laporan)) : '-' }}</div>
                            </td>
                            <td class="text-start text-justify">
                                <div class="text-dark">{{ $row->rekomendasi_temuan ?? '-' }}</div>
                                @if($row->klarifikasi)
                                    <small class="text-secondary d-block mt-1 fst-italic">
                                        <i class="bi bi-chat-left-text me-1"></i> Klarifikasi: {{ Str::limit($row->klarifikasi, 90) }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-start small">
                                <div class="text-dark mb-1">{{ $row->komitmen_auditee ?? '-' }}</div>
                                @if($row->target_penyelesaian)
                                    <span class="badge bg-light text-primary border">
                                        <i class="bi bi-calendar-event me-1"></i> Target: {{ date('d/m/Y', strtotime($row->target_penyelesaian)) }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $badgeStatus }} px-3 py-1 rounded-pill mb-1 d-inline-block">
                                    <i class="bi {{ $isClosed ? 'bi-check-circle-fill' : 'bi-hourglass-split' }} me-1"></i>
                                    {{ $isClosed ? 'SUDAH TL' : 'BELUM TL' }}
                                </span>
                                <div>
                                    @if($statusTl === 'SUDAH SELESAI')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">SELESAI</span>
                                    @elseif($statusTl === 'TIDAK BISA')
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">TIDAK BISA</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle text-dark">DALAM PROSES</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center pe-3">
                                <div class="d-flex flex-column gap-1 align-items-center">
                                    @if($row->bukti_terakhir)
                                        <a href="{{ route('monitoring.view_bukti', $row->id) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 w-100" title="Lihat Berkas Bukti">
                                            <i class="bi bi-eye me-1"></i> Bukti
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 w-100" data-bs-toggle="modal" data-bs-target="#modalUpdateTl-{{ $row->id }}" title="Perbarui Status Tindak Lanjut">
                                        <i class="bi bi-pencil-square me-1"></i> Update
                                    </button>
                                </div>

                                <!-- MODAL UPDATE STATUS TINDAK LANJUT (Poin 7) -->
                                <div class="modal fade text-start" id="modalUpdateTl-{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-dark text-white">
                                                <h6 class="modal-title fw-bold">
                                                    <i class="bi bi-shield-check text-warning me-1"></i> Update Tindak Lanjut: {{ $row->tl_id }}
                                                </h6>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('monitoring.update_tl', $row->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-body p-4">
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Temuan:</small>
                                                        <div class="fw-bold small">{{ Str::limit($row->rekomendasi_temuan, 120) }}</div>
                                                    </div>
                                                    <hr class="my-2">

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Status Tindak Lanjut (Poin 7)</label>
                                                        <select name="status_tindak_lanjut" class="form-select form-select-sm fw-bold" id="selStatus-{{ $row->id }}" onchange="toggleModalFields('{{ $row->id }}')">
                                                            <option value="SUDAH SELESAI" {{ $statusTl === 'SUDAH SELESAI' ? 'selected' : '' }}>SUDAH SELESAI (Lampirkan Bukti)</option>
                                                            <option value="DALAM PROSES" {{ $statusTl === 'DALAM PROSES' ? 'selected' : '' }}>DALAM PROSES PENYELESAIAN (Input Komitmen Tanggal)</option>
                                                            <option value="TIDAK BISA" {{ $statusTl === 'TIDAK BISA' ? 'selected' : '' }}>TIDAK BISA DISELESAIKAN (Wajib Alasan / Surat)</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3" id="groupTgl-{{ $row->id }}" style="{{ $statusTl === 'DALAM PROSES' ? '' : 'display:none;' }}">
                                                        <label class="form-label small fw-bold text-danger">Komitmen Tanggal Penyelesaian Cabang *</label>
                                                        <input type="date" name="target_penyelesaian" class="form-control form-control-sm" value="{{ $row->target_penyelesaian }}">
                                                    </div>

                                                    <div class="mb-3" id="groupBukti-{{ $row->id }}" style="{{ $statusTl === 'DALAM PROSES' ? 'display:none;' : '' }}">
                                                        <label class="form-label small fw-bold text-secondary">Upload Berkas Bukti (PDF / Gambar)</label>
                                                        <input type="file" name="file_bukti" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                                                        <small class="text-muted">Maksimal 10MB</small>
                                                    </div>

                                                    <div class="mb-2">
                                                        <label class="form-label small fw-bold text-secondary">Klarifikasi Cabang / Catatan Pemantau</label>
                                                        <textarea name="klarifikasi" class="form-control form-control-sm" rows="3" placeholder="Masukkan kronologis penyelesaian atau alasan jika tidak dapat diselesaikan...">{{ $row->klarifikasi ?: $row->komitmen_auditee }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light py-2">
                                                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                Tidak ada data temuan tindak lanjut untuk filter yang dipilih.
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalUploadExcel">
                                        <i class="bi bi-upload me-1"></i> Upload Data Excel Sekarang
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($items->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted">Menampilkan {{ $items->firstItem() }} - {{ $items->lastItem() }} dari {{ $items->total() }} data</small>
                <div>{{ $items->links('pagination::bootstrap-5') }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL UPLOAD EXCEL (INTERNAL & REGULATOR) (Poin 10 & 11) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalUploadExcel" tabindex="-1" aria-labelledby="modalUploadExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalUploadExcelLabel">
                    <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i> 
                    Upload File Excel Temuan {{ ucfirst($currentTab) }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ $currentTab === 'regulator' ? route('monitoring.upload_regulator') : route('monitoring.upload_internal') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> 
                        Pastikan kolom Excel Anda telah sesuai dengan template standar Bank Sulteng. Kolom yang dibaca: 
                        <strong>Nomor Temuan (TL ID), Sumber, Nama Cabang, No. Laporan, Tanggal, Uraian Temuan, Komitmen, Target, Prioritas, Status</strong>.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih File Excel (.xlsx, .xls, .csv) <span class="text-danger">*</span></label>
                        <input type="file" name="file_excel" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <small class="text-muted">Ukuran file maksimal: 10 MB</small>
                        <a href="{{ route('monitoring.template_excel', $currentTab) }}" class="small text-decoration-none">
                            <i class="bi bi-download me-1"></i> Unduh Format Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Mulai Impor Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleModalFields(id) {
    const val = document.getElementById('selStatus-' + id).value;
    const grpTgl = document.getElementById('groupTgl-' + id);
    const grpBukti = document.getElementById('groupBukti-' + id);

    if (val === 'DALAM PROSES') {
        grpTgl.style.display = 'block';
        grpBukti.style.display = 'none';
    } else {
        grpTgl.style.display = 'none';
        grpBukti.style.display = 'block';
    }
}
</script>
@endsection
