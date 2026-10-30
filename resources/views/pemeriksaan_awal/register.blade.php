@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary"><i class="bi bi-journal-plus me-2"></i> REGISTER INDIKASI FRAUD (WP-RA-09-A)</h4>
            <p class="text-muted small mb-0">Pencatatan awal, penerimaan informasi, verifikasi fakta awal, dan red flag utama indikasi fraud</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddRegister">
                <i class="bi bi-plus-lg me-1"></i> Tambah Register Indikasi
            </button>
        </div>
    </div>

    @include('pemeriksaan_awal.partials.nav_tabs')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Kartu Statistik Ringkasan -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-primary rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Total Kasus Masuk</span>
                    <h3 class="fw-bold text-primary mb-0">{{ count($indikasis ?? []) }}</h3>
                    <small class="text-muted">Register Indikasi Kasus</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-warning rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Kasus Status OPEN</span>
                    <h3 class="fw-bold text-dark mb-0">{{ isset($indikasis) ? $indikasis->where('status_kasus', 'OPEN')->count() : 0 }}</h3>
                    <small class="text-muted">Dalam Proses Fact-Finding</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-success rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Sensitivitas Tinggi</span>
                    <h3 class="fw-bold text-success mb-0">{{ isset($indikasis) ? $indikasis->where('sensitivitas_reputasi', 'TINGGI')->count() : 0 }}</h3>
                    <small class="text-muted">Risiko Reputasi Bank</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Data Register Indikasi -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-table me-2"></i> Daftar Register Indikasi Masuk (31 Kolom Standard)</h6>
            <span class="badge bg-primary rounded-pill px-3">{{ count($indikasis ?? []) }} Data</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ID Indikasi</th>
                            <th>Tanggal & Waktu</th>
                            <th>Unit / Cabang</th>
                            <th>Sumber</th>
                            <th>Red Flag Utama</th>
                            <th>Estimasi Nominal</th>
                            <th>Sensitivitas</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($indikasis ?? [] as $item)
                            <tr>
                                <td class="ps-3 fw-bold text-primary">{{ $item->indikasi_id }}</td>
                                <td>
                                    <span class="fw-semibold d-block text-dark">{{ $item->tanggal_diterima }}</span>
                                    <small class="text-muted">{{ $item->jam ?? '00:00' }} WIB</small>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $item->nama_unit ?? $item->kode_unit }}</span>
                                    <small class="d-block text-muted">{{ $item->area }}</small>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $item->sumber_indikasi }}</span></td>
                                <td class="text-truncate" style="max-width: 180px;" title="{{ $item->red_flag_utama }}">
                                    {{ $item->red_flag_utama }}
                                </td>
                                <td class="fw-bold text-danger">Rp {{ number_format($item->nominal_awal) }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->sensitivitas_reputasi == 'TINGGI' ? 'danger' : ($item->sensitivitas_reputasi == 'SEDANG' ? 'warning text-dark' : 'success') }}">
                                        {{ $item->sensitivitas_reputasi }}
                                    </span>
                                </td>
                                <td><span class="badge bg-primary bg-opacity-15 text-primary border border-primary">{{ $item->status_kasus }}</span></td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('pemeriksaan_awal.triase') }}" class="btn btn-sm btn-outline-primary" title="Lanjut ke Triase"><i class="bi bi-arrow-right-circle"></i> Triase</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i> Belum ada data register indikasi fraud yang tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Modal Form Add Register Indikasi diletakkan di section scripts/footer agar terlepas dari hirarki layout utama -->
<div class="modal fade" id="modalAddRegister" tabindex="-1" aria-labelledby="modalAddRegisterLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('pemeriksaan_awal.register.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold" id="modalAddRegisterLabel"><i class="bi bi-journal-plus me-2"></i> Form Register Indikasi Awal Fraud (WP-RA-09-A)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3 p-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Tanggal Diterima</label>
                        <input type="date" name="tanggal_diterima" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Sumber Indikasi</label>
                        <select name="sumber_indikasi" class="form-select form-select-sm" required>
                            <option value="WHISTLEBLOWING">Whistleblowing System (WBS)</option>
                            <option value="LAPORAN_CABANG">Laporan Pimpinan Cabang / Unit</option>
                            <option value="TEMUAN_OFFSITE">Analisis Data Offsite (SOP 02)</option>
                            <option value="AUDIT_ONSITE">Temuan Onsite Audit (SOP 04)</option>
                            <option value="PENGADUAN_NASABAH">Pengaduan Nasabah Langsung</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Pilih Unit / Cabang Bank Sulteng</label>
                        <select id="select_unit_register" class="form-select form-select-sm" onchange="var opt = this.options[this.selectedIndex]; if(this.value){ document.getElementById('reg_kode_unit').value = this.value; document.getElementById('reg_nama_unit').value = opt.getAttribute('data-nama'); }">
                            <option value="">-- Pilih dari Daftar Unit --</option>
                            @foreach($units ?? [] as $u)
                                <option value="{{ $u->kode_unit }}" data-nama="{{ $u->nama_unit }}">
                                    [{{ $u->kode_unit }}] {{ $u->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Kode Unit Kerja <span class="text-danger">*</span></label>
                        <input type="text" name="kode_unit" id="reg_kode_unit" class="form-control form-control-sm" placeholder="Misal: 501" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Nama Unit / Cabang <span class="text-danger">*</span></label>
                        <input type="text" name="nama_unit" id="reg_nama_unit" class="form-control form-control-sm" placeholder="Misal: KCP Bahomotefe" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Area / Bagian Terindikasi</label>
                        <input type="text" name="area" class="form-control form-control-sm" placeholder="Misal: Teller / Kredit / Operasional">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Sensitivitas Reputasi Bank</label>
                        <select name="sensitivitas_reputasi" class="form-select form-select-sm">
                            <option value="RENDAH">RENDAH</option>
                            <option value="SEDANG" selected>SEDANG</option>
                            <option value="TINGGI">TINGGI (Potensi Eskalasi Media/Regulator)</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold small">Red Flag Utama (Indikator Kecurangan)</label>
                        <input type="text" name="red_flag_utama" class="form-control form-control-sm" placeholder="Misal: Selisih kas harian tidak wajar / Transaksi fiktif rekening nasabah" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Fakta Awal Terverifikasi</label>
                        <textarea name="fakta_awal_terverifikasi" class="form-control form-control-sm" rows="3" placeholder="Uraikan fakta awal yang sudah divalidasi..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Dugaan Awal (Belum Terverifikasi)</label>
                        <textarea name="dugaan_belum_terverifikasi" class="form-control form-control-sm" rows="3" placeholder="Uraikan dugaan modus operandi awal..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Estimasi Nominal Paparan Awal (Rp)</label>
                        <input type="number" name="nominal_awal" class="form-control form-control-sm" placeholder="0" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Simpan Register Indikasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Memindahkan elemen modal ke body utama secara otomatis begitu dokumen siap
    document.addEventListener("DOMContentLoaded", function() {
        var modalEl = document.getElementById('modalAddRegister');
        if (modalEl) {
            document.body.appendChild(modalEl);
        }
    });
</script>
@endpush