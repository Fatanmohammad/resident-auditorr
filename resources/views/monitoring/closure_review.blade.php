@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-primary"><i class="bi bi-check2-circle me-2"></i> WP-RA-07-F | CLOSURE REVIEW DAN KEPUTUSAN FINAL</h4>
            <p class="text-muted mb-0" style="font-size: 13px;">RA mengusulkan; closure authority menetapkan status closed final sesuai wewenang</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('monitoring.export_excel', 'closure_review') }}" class="btn btn-sm btn-outline-success shadow-sm fw-semibold">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
            <button type="button" class="btn btn-sm btn-primary shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalAjukanClosure">
                <i class="bi bi-plus-circle me-1"></i> Ajukan Closure Review
            </button>
        </div>
    </div>

    @include('monitoring.partials.nav_tabs')

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 680px; overflow-y: auto;">
                <table class="table table-bordered table-hover align-middle mb-0 text-nowrap" style="font-size: 11.5px;">
                    <thead class="table-dark text-center sticky-top" style="z-index: 10;">
                        <tr>
                            <th class="py-3">1. Closure Review ID</th>
                            <th class="py-3">2. TL ID</th>
                            <th class="py-3">3. Unit / Kantor</th>
                            <th class="py-3">4. Sumber TL</th>
                            <th class="py-3">5. Closure Authority</th>
                            <th class="py-3">6. Verifikasi Terakhir</th>
                            <th class="py-3">7. Usulan Status</th>
                            <th class="py-3">8. Tgl Pengajuan</th>
                            <th class="py-3">9. Keputusan Authority</th>
                            <th class="py-3">10. Tgl Keputusan</th>
                            <th class="py-3">11. Pejabat Authority</th>
                            <th class="py-3">12. Status Efektif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($closures) && count($closures) > 0)
                            @foreach($closures as $item)
                            <tr>
                                <td><span class="badge bg-primary px-2 py-1">{{ $item->closure_review_id ?? '-' }}</span></td>
                                <td>{{ $item->tl_id ?? '-' }}</td>
                                <td class="fw-semibold">{{ $item->unit_kantor ?? '-' }}</td>
                                <td>{{ $item->sumber_tl ?? '-' }}</td>
                                <td>{{ $item->closure_authority ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-info text-dark px-2 py-1">{{ $item->hasil_verifikasi_terakhir ?? '-' }}</span></td>
                                <td class="text-center"><span class="badge bg-dark px-2 py-1">{{ $item->usulan_status ?? '-' }}</span></td>
                                <td class="text-center">{{ $item->tanggal_pengajuan ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-success px-2 py-1">{{ $item->keputusan_authority ?? '-' }}</span></td>
                                <td class="text-center">{{ $item->tanggal_keputusan ?? '-' }}</td>
                                <td>{{ $item->pejabat_authority ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-success px-2 py-1">{{ $item->status_efektif ?? '-' }}</span></td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="12" class="text-center text-muted py-5 fw-semibold">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                    Belum ada pengajuan closure review.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAjukanClosure" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-check2-circle me-2"></i> Form Pengajuan Closure Review</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('monitoring.closure_review.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">TL ID</label>
                            <input type="text" name="tl_id" class="form-control" required placeholder="TL-20261001-0001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Usulan Status</label>
                            <select name="usulan_status" class="form-select" required>
                                <option value="CLOSED">CLOSED</option>
                                <option value="PROPOSED CLOSED">PROPOSED CLOSED</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tanggal Pengajuan</label>
                            <input type="date" name="tanggal_pengajuan" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Closure Authority Target</label>
                            <input type="text" name="closure_authority" class="form-control" placeholder="Contoh: Kadiv SKAI / Komite Audit" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">Referensi Paket Bukti</label>
                            <input type="text" name="referensi_paket_bukti" class="form-control" placeholder="No. KKA & Dokumen Bukti Pendukung" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection