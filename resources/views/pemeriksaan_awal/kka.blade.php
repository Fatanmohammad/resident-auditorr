@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary"><i class="bi bi-journal-check me-2"></i> KKA PEMERIKSAAN AWAL / FACT-FINDING (WP-RA-09-E)</h4>
            <p class="text-muted small mb-0">Kertas kerja pengujian anomali, kalkulasi rasio populasi, dan perhitungan selisih nominal otomatis</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddKka">
                <i class="bi bi-plus-lg me-1"></i> Tambah Uji KKA Baru
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

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-calculator me-2"></i> Kertas Kerja Pengujian Fact-Finding (24 Kolom Standard)</h6>
            <a href="{{ route('pemeriksaan_awal.export_excel', 'kka') }}" class="btn btn-outline-success btn-sm fw-semibold">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Test ID</th>
                            <th>Indikasi ID</th>
                            <th>Area Uji Awal</th>
                            <th>Populasi vs Anomali</th>
                            <th>Rasio Anomali</th>
                            <th>Nominal Sistem vs Bukti</th>
                            <th>Selisih (Variance)</th>
                            <th>Simpulan Uji</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kkas as $kka)
                            <tr>
                                <td class="ps-3 fw-bold text-primary">{{ $kka->test_id }}</td>
                                <td class="font-monospace text-dark">{{ $kka->indikasi_id }}</td>
                                <td class="fw-semibold">{{ $kka->area_uji_awal }}</td>
                                <td>
                                    <span class="text-muted">{{ number_format($kka->populasi_universe) }}</span> / 
                                    <span class="fw-bold text-danger">{{ number_format($kka->jumlah_anomali) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $kka->rasio_anomali > 20 ? 'danger' : 'warning text-dark' }} fs-6">
                                        {{ $kka->rasio_anomali }}%
                                    </span>
                                </td>
                                <td>
                                    <small class="d-block text-muted">Sistem: Rp {{ number_format($kka->nominal_sistem) }}</small>
                                    <small class="d-block text-muted">Bukti: Rp {{ number_format($kka->nominal_dokumen_bukti) }}</small>
                                </td>
                                <td class="fw-bold text-{{ $kka->selisih != 0 ? 'danger' : 'success' }}">
                                    Rp {{ number_format($kka->selisih) }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $kka->simpulan_uji }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-2 d-block mb-2"></i> Belum ada pengujian KKA Pemeriksaan Awal yang disimpan.
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
<!-- Modal Form Add KKA di luar kontainer utama -->
<div class="modal fade" id="modalAddKka" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('pemeriksaan_awal.kka.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold"><i class="bi bi-journal-plus me-2"></i> Tambah Kertas Kerja Fact-Finding (KKA SOP 09)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3 p-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">ID Indikasi Kasus</label>
                        <input type="text" name="indikasi_id" class="form-control form-control-sm" placeholder="Misal: IND-202610-0001" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Area Uji Awal</label>
                        <input type="text" name="area_uji_awal" class="form-control form-control-sm" placeholder="Misal: Transaksi Kas Teller / Kredit" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold small">Tujuan Pengujian Awal</label>
                        <textarea name="tujuan_uji" class="form-control form-control-sm" rows="2" placeholder="Tujuan pengujian ketaatan & pengujian substantif awal..."></textarea>
                    </div>
                    <hr class="my-2">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-calculator me-1"></i> Pengujian Populasi & Ratio Anomali</h6>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Jumlah Populasi (Universe)</label>
                        <input type="number" name="populasi_universe" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Jumlah Sampel Diuji</label>
                        <input type="number" name="jumlah_diuji" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Jumlah Temuan Anomali</label>
                        <input type="number" name="jumlah_anomali" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <hr class="my-2">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-cash-stack me-1"></i> Pengujian Nominal (Sistem vs Bukti Fisik)</h6>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nominal Tercatat Sistem (Core Banking) Rp</label>
                        <input type="number" name="nominal_sistem" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nominal Fisik Dokumen Bukti Rp</label>
                        <input type="number" name="nominal_dokumen_bukti" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold small">Simpulan Uji Fact-Finding</label>
                        <select name="simpulan_uji" class="form-select form-select-sm">
                            <option value="TERINDIKASI_ANOMALI">TERINDIKASI ANOMALI / POTENSI FRAUD</option>
                            <option value="WAJAR_DENGAN_CATATAN">WAJAR DENGAN CATATAN KELALAIAN ADMIN</option>
                            <option value="SESUAI_SOP">SESUAI / TIDAK ADA ANOMALI</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Simpan KKA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var modalEl = document.getElementById('modalAddKka');
        if (modalEl) { document.body.appendChild(modalEl); }
    });
</script>
@endpush