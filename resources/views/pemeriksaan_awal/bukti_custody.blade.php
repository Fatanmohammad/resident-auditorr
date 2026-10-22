@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary"><i class="bi bi-shield-check me-2"></i> CHAIN OF CUSTODY & LOG BUKTI (WP-RA-09-C)</h4>
            <p class="text-muted small mb-0">Inventarisasi, pencatatan integritas, dan riwayat penyerahan bukti fisik/elektronik</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddCustody">
                <i class="bi bi-plus-lg me-1"></i> Catat Bukti Baru
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

    <div class="card border-0 bg-primary bg-opacity-10 shadow-sm d-flex align-items-center mb-4 rounded-3 p-3">
        <div class="d-flex align-items-center text-primary">
            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
            <div class="small text-dark">
                <strong>Prinsip Chain of Custody (SOP 09):</strong> Seluruh bukti fisik (dokumen asli, slip, warkat) dan bukti elektronik (file dump, log CCTV) wajib dicatat tanggal pengamanan, lokasi penyimpanan brankas, dan riwayat perpindahan penguasaan bukti.
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-primary rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Total Barang Bukti</span>
                    <h3 class="fw-bold text-primary mb-0">{{ count($buktis ?? []) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-success rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Kondisi Utuh / Tersegel</span>
                    <h3 class="fw-bold text-success mb-0">{{ isset($buktis) ? $buktis->where('kondisi_bukti', 'UTUH')->count() : 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-dark rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Penyimpanan Brankas SKAI</span>
                    <h3 class="fw-bold text-dark mb-0">{{ isset($buktis) ? $buktis->where('status_penguasaan', 'BRANKAS_SKAI')->count() : 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Register Chain of Custody -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-journal-text me-2"></i> Register Barang Bukti Terdaftar (SOP 09)</h6>
            <span class="badge bg-primary rounded-pill px-3">{{ count($buktis ?? []) }} Bukti Terdata</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Kode Bukti</th>
                            <th>ID Indikasi</th>
                            <th>Deskripsi Bukti</th>
                            <th>Kategori Bukti</th>
                            <th>Tgl Diamankan</th>
                            <th>Kondisi Integritas</th>
                            <th>Lokasi Simpan</th>
                            <th>Status Penguasaan</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($buktis ?? [] as $bukti)
                            <tr>
                                <td class="ps-3 fw-bold text-dark">{{ $bukti->kode_bukti }}</td>
                                <td class="text-primary font-monospace">{{ $bukti->indikasi_id }}</td>
                                <td class="fw-semibold">{{ $bukti->deskripsi_bukti }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $bukti->kategori_bukti }}</span></td>
                                <td>{{ $bukti->tanggal_diperoleh }}</td>
                                <td>
                                    <span class="badge bg-{{ $bukti->kondisi_bukti == 'UTUH' ? 'success' : 'danger' }}">
                                        {{ $bukti->kondisi_bukti }}
                                    </span>
                                </td>
                                <td><i class="bi bi-geo-alt me-1 text-primary"></i>{{ $bukti->lokasi_penyimpanan }}</td>
                                <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary">{{ $bukti->status_penguasaan }}</span></td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-left-right me-1"></i> Transfer</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-shield-slash fs-2 d-block mb-2 text-secondary"></i> Belum ada bukti fisik/elektronik yang dicatatkan dalam Chain of Custody.
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
                <h5 class="modal-title fw-bold" id="modalAddCustodyLabel"><i class="bi bi-shield-plus me-2"></i> Form Input Chain of Custody &amp; Log Bukti (WP-RA-09-C)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3 p-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold small">ID Indikasi Fraud <span class="text-danger">*</span></label>
                    <select name="indikasi_id" class="form-select form-select-sm" required>
                        <option value="">-- Pilih ID Indikasi --</option>
                        @foreach($indikasis ?? [] as $ind)
                            <option value="{{ $ind->indikasi_id }}">
                                {{ $ind->indikasi_id }} - {{ $ind->nama_unit ?? 'Unit' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Kategori Bukti <span class="text-danger">*</span></label>
                    <select name="kategori_bukti" class="form-select form-select-sm" required>
                        <option value="DOKUMEN_FISIK">Dokumen Fisik (Slip / Warkat / SPK / Akad)</option>
                        <option value="FILE_DIGITAL">File Digital / Database Dump / Log System</option>
                        <option value="REKAMAN_CCTV">Rekaman Video CCTV / Audio Record</option>
                        <option value="BARANG_FISIK">Barang Fisik / Cash Tape / Stempel</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold small">Deskripsi Spesifik Barang Bukti <span class="text-danger">*</span></label>
                    <textarea name="deskripsi_bukti" class="form-control form-control-sm" rows="2" placeholder="Uraikan detail fisik, nomor dokumen, seri, atau nama file..." required></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Tanggal Pengamanan / Diperoleh <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_diperoleh" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Sumber / Diserahkan Oleh <span class="text-danger">*</span></label>
                    <input type="text" name="sumber_bukti" class="form-control form-control-sm" placeholder="Misal: Pimpinan Cabang / Teller / Pemilik Rekening" required>
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
                    <label class="form-label fw-bold small">Lokasi Penyimpanan <span class="text-danger">*</span></label>
                    <input type="text" name="lokasi_penyimpanan" class="form-control form-control-sm" placeholder="Misal: Brankas Utama SKAI Lt. 3 / Server Safe" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Status Penguasaan Saat Ini</label>
                    <select name="status_penguasaan" class="form-select form-select-sm">
                        <option value="BRANKAS_SKAI">TERSIMPAN DI BRANKAS SKAI</option>
                        <option value="DIPINJAM_INVESTIGATOR">DIPINJAM TIM FACT-FINDING</option>
                        <option value="DISERAHKAN_KEPOLISIAN">DISERAHKAN KE APARAT HUKUM (APH)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Petugas Penerima / Custodian <span class="text-danger">*</span></label>
                    <input type="text" name="petugas_custodian" value="{{ auth()->user()->name ?? 'Auditor SKAI' }}" class="form-control form-control-sm" required>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Simpan Log Bukti</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var modalEl = document.getElementById('modalAddCustody');
        if (modalEl) {
            document.body.appendChild(modalEl);
        }
    });
</script>
@endpush