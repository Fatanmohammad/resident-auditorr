@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary"><i class="bi bi-arrow-up-right-circle me-2"></i> KEPUTUSAN ESKALASI KASUS (WP-RA-09-G)</h4>
            <p class="text-muted small mb-0">Penetapan simpulan akhir fact-finding awal dan penyerahan kasus ke Tim Investigasi SKAI</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddEskalasi">
                <i class="bi bi-send-check me-1"></i> Buat Keputusan Eskalasi
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

    <!-- Cards Ringkasan Stat -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-danger rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Dieskalasi ke Investigasi</span>
                    <h3 class="fw-bold text-danger mb-0">{{ isset($eskalasis) ? $eskalasis->where('keputusan_final', 'ESKALASI_INVESTIGASI')->count() : 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-info rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Pembinaan Internal</span>
                    <h3 class="fw-bold text-info mb-0">{{ isset($eskalasis) ? $eskalasis->where('keputusan_final', 'PEMBINAAN_INTERNAL')->count() : 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-secondary rounded-3">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Kasus Ditutup / Arsip</span>
                    <h3 class="fw-bold text-secondary mb-0">{{ isset($eskalasis) ? $eskalasis->where('keputusan_final', 'TUTUP_KASUS')->count() : 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Lembar Keputusan -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-file-earmark-lock me-2"></i> Lembar Keputusan Final Fact-Finding</h6>
            <span class="badge bg-primary rounded-pill px-3">{{ count($eskalasis ?? []) }} Data</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ID Indikasi</th>
                            <th>Tanggal Keputusan</th>
                            <th>Ringkasan Fakta Fact-Finding</th>
                            <th>Estimasi Kerugian</th>
                            <th>Rekomendasi Tim</th>
                            <th>Keputusan Final SKAI</th>
                            <th>Status Penyerahan</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($eskalasis ?? [] as $item)
                            <tr>
                                <td class="ps-3 fw-bold text-primary">{{ $item->indikasi_id }}</td>
                                <td>{{ $item->tanggal_keputusan }}</td>
                                <td class="fw-semibold">{{ Str::limit($item->fakta_utama ?? '-', 40) }}</td>
                                <td class="fw-bold text-danger">Rp {{ number_format($item->estimasi_kerugian ?? 0) }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $item->rekomendasi_tim ?? '-' }}</span></td>
                                <td>
                                    <span class="badge bg-{{ $item->keputusan_final == 'ESKALASI_INVESTIGASI' ? 'danger' : 'success' }}">
                                        {{ $item->keputusan_final }}
                                    </span>
                                </td>
                                <td><span class="badge bg-outline-info text-dark">{{ $item->status_penyerahan ?? 'SUBMITTED' }}</span></td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-printer me-1"></i> Cetak ND</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-box-seam fs-2 d-block mb-2 text-secondary"></i> Belum ada keputusan eskalasi kasus terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL FORM ESKALASI KASUS (Diletakkan di luar kontainer utama) -->
<div class="modal fade" id="modalAddEskalasi" tabindex="-1" aria-labelledby="modalAddEskalasiLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('pemeriksaan_awal.eskalasi.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold" id="modalAddEskalasiLabel"><i class="bi bi-send-check me-2"></i> Form Penetapan Keputusan & Eskalasi Kasus (WP-RA-09-G)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3 p-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">ID Indikasi Fraud <span class="text-danger">*</span></label>
                        <select name="indikasi_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih ID Indikasi --</option>
                            @foreach($indikasis ?? [] as $ind)
                                <option value="{{ $ind->indikasi_id }}">{{ $ind->indikasi_id }} - {{ $ind->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Tanggal Penetapan Keputusan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_keputusan" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold small">Ringkasan Fakta Fact-Finding Awal <span class="text-danger">*</span></label>
                        <textarea name="fakta_utama" class="form-control form-control-sm" rows="3" placeholder="Uraikan temuan utama hasil klarifikasi awal..." required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Estimasi Total Paparan Kerugian Bank (Rp)</label>
                        <input type="number" name="estimasi_kerugian" class="form-control form-control-sm" placeholder="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Rekomendasi Tim Fact-Finder <span class="text-danger">*</span></label>
                        <select name="rekomendasi_tim" class="form-select form-select-sm" required>
                            <option value="INVESTIGASI_LANJUT">REKOMENDASI AUDIT INVESTIGASI (SOP 10)</option>
                            <option value="PEMBINAAN_INTERNAL">REKOMENDASI PERBAIKAN SISTEM & PEMBINAAN</option>
                            <option value="TUTUP_TIDAK_CUKUP_BUKTI">TUTUP / TIDAK CUKUP BUKTI FRAUD</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Keputusan Final Kadiv SKAI <span class="text-danger">*</span></label>
                        <select name="keputusan_final" class="form-select form-select-sm" required>
                            <option value="ESKALASI_INVESTIGASI">ESKALASI KE TIM INVESTIGASI</option>
                            <option value="PEMBINAAN_INTERNAL">PEMBINAAN MANAJEMEN CABANG</option>
                            <option value="TUTUP_KASUS">ARSIP / CASING CLOSED</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Nomor Nota Dinas / Surat Eskalasi</label>
                        <input type="text" name="nomor_nota_dinas" class="form-control form-control-sm" placeholder="Misal: ND/09/SKAI/X/2026">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Simpan Keputusan Final</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var modalEl = document.getElementById('modalAddEskalasi');
        if (modalEl) {
            document.body.appendChild(modalEl);
        }
    });
</script>
@endpush