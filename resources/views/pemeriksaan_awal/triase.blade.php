@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary"><i class="bi bi-diagram-3 me-2"></i> TRIASE AWAL INDIKASI FRAUD (WP-RA-09-B)</h4>
            <p class="text-muted small mb-0">Penilaian urgensi, kelayakan data dasar, dan pembentukan skor prioritas fact-finding awal</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddTriase">
                <i class="bi bi-plus-lg me-1"></i> Tambah Triase Awal
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

    <!-- Card Stats Ringkasan -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-danger rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Kasus Urgent / Segera</span>
                    <h3 class="fw-bold text-danger mb-0">{{ $triases->where('urgent', 'YA')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-warning rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Rata-rata Skor Prioritas</span>
                    <h3 class="fw-bold text-dark mb-0">{{ number_format($triases->avg('skor_prioritas') ?? 0, 1) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-success rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Lanjut Fact-Finding</span>
                    <h3 class="fw-bold text-success mb-0">{{ $triases->where('keputusan_triase', 'LANJUT_FACT_FINDING')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-secondary rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Arsip / Ditutup Awal</span>
                    <h3 class="fw-bold text-secondary mb-0">{{ $triases->where('keputusan_triase', 'ARSIP')->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Data Triase -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ID Indikasi</th>
                            <th>Tanggal & Unit</th>
                            <th>Skor Prioritas</th>
                            <th>Status Urgensi</th>
                            <th>Risiko Hilang Bukti</th>
                            <th>Saran Sistem</th>
                            <th>Keputusan Triase</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($triases as $item)
                            <tr>
                                <td class="ps-3 fw-bold text-primary">{{ $item->indikasi_id }}</td>
                                <td>
                                    <span class="fw-semibold d-block text-dark">{{ $item->unit }}</span>
                                    <span class="text-muted small">{{ $item->tanggal }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary rounded-pill fs-6">{{ $item->skor_prioritas }}</span>
                                </td>
                                <td>
                                    @if($item->urgent == 'YA')
                                        <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> URGENT</span>
                                    @else
                                        <span class="badge bg-info text-dark">NORMAL</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $item->risiko_bukti_hilang == 'TINGGI' ? 'danger' : ($item->risiko_bukti_hilang == 'SEDANG' ? 'warning' : 'success') }}">
                                        {{ $item->risiko_bukti_hilang }}
                                    </span>
                                </td>
                                <td class="fst-italic text-muted">{{ $item->saran_sistem }}</td>
                                <td>
                                    <span class="badge bg-outline-primary border border-primary text-primary">{{ $item->keputusan_triase }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> Detail</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i> Belum ada data triase awal terdaftar.
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
<!-- Modal Form Add Triase di luar kontainer utama -->
<div class="modal fade" id="modalAddTriase" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('pemeriksaan_awal.triase.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i> Form Triase Awal Indikasi Fraud</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3 p-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Pilih Register Indikasi ID <span class="text-danger">*</span></label>
                        <select name="indikasi_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Indikasi --</option>
                            @foreach($indikasis as $ind)
                                <option value="{{ $ind->indikasi_id }}">{{ $ind->indikasi_id }} - {{ $ind->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Tanggal Triase</label>
                        <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Nama Unit / Cabang</label>
                        <input type="text" name="unit" class="form-control form-control-sm" placeholder="Misal: KC Palu" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Sumber Indikasi</label>
                        <input type="text" name="sumber" class="form-control form-control-sm" placeholder="Laporan Cabang / Whistleblowing">
                    </div>
                    <hr class="my-3">
                    <h6 class="fw-bold text-primary mb-2"><i class="bi bi-sliders me-1"></i> Parameter Pembentukan Skor Prioritas</h6>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Risiko Hilang Bukti / Alterasi</label>
                        <select name="risiko_bukti_hilang" class="form-select form-select-sm">
                            <option value="RENDAH">RENDAH (+0)</option>
                            <option value="SEDANG">SEDANG (+1)</option>
                            <option value="TINGGI">TINGGI (+3) - Auto Urgent</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Pola Berulang (Repeat Anomaly)</label>
                        <select name="pola_berulang" class="form-select form-select-sm">
                            <option value="TIDAK">TIDAK (+0)</option>
                            <option value="YA">YA (+2)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Melibatkan Nasabah Langsung</label>
                        <select name="nasabah" class="form-select form-select-sm">
                            <option value="TIDAK">TIDAK (+0)</option>
                            <option value="YA">YA (+2)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Melibatkan Pimpinan / Pihak Kunci</label>
                        <select name="pihak_kunci_pimpinan" class="form-select form-select-sm">
                            <option value="TIDAK">TIDAK (+0)</option>
                            <option value="YA">YA (+3)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nominal Estimasi Paparan Risiko (Rp)</label>
                        <input type="number" name="nominal_awal" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Keputusan Triase</label>
                        <select name="keputusan_triase" class="form-select form-select-sm">
                            <option value="LANJUT_FACT_FINDING">LANJUT FACT-FINDING</option>
                            <option value="MONITORING_OFFSITE">DIALIHKAN KE MONITORING OFFSITE</option>
                            <option value="ARSIP">ARSIP / TIDAK CUKUP BUKTI</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Simpan Triase Awal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var modalEl = document.getElementById('modalAddTriase');
        if (modalEl) { document.body.appendChild(modalEl); }
    });
</script>
@endpush