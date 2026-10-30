@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-4 position-relative" style="z-index: 10;">
    <div class="d-flex justify-content-between align-items-center mb-4" style="position: relative; z-index: 1050;">
        <div>
            <h4 class="fw-bold mb-1 text-primary"><i class="bi bi-arrow-up-right-circle me-2"></i> WP-RA-07-G | REGISTER PERPANJANGAN, ESKALASI DAN REOPENING</h4>
            <p class="text-muted mb-0" style="font-size: 13px;">Riwayat overdue tidak dihapus oleh perpanjangan; keputusan dan bukti persetujuan wajib dapat ditelusuri</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('monitoring.export_excel', 'eskalasi') }}" class="btn btn-sm btn-outline-success shadow-sm fw-semibold" style="position: relative; z-index: 1060;">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
            <button type="button" class="btn btn-sm btn-primary shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalEskalasi" style="position: relative; z-index: 1060;">
                <i class="bi bi-plus-circle me-1"></i> Tambah Aksi Eskalasi
            </button>
        </div>
    </div>

    @include('monitoring.partials.nav_tabs')

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 680px; overflow-y: auto;">
                <table class="table table-bordered table-hover align-middle mb-0 text-nowrap" style="font-size: 11.5px;">
                    <thead class="table-dark text-center sticky-top" style="z-index: 5;">
                        <tr>
                            <th class="py-3">1. Action ID</th>
                            <th class="py-3">2. TL ID</th>
                            <th class="py-3">3. Unit / Kantor</th>
                            <th class="py-3">4. Risk Level</th>
                            <th class="py-3">5. Jenis Aksi</th>
                            <th class="py-3">6. Tgl Permintaan</th>
                            <th class="py-3" style="min-width: 200px;">7. Alasan</th>
                            <th class="py-3">8. Target Baru Diminta</th>
                            <th class="py-3">9. Level Persetujuan</th>
                            <th class="py-3">10. Keputusan</th>
                            <th class="py-3">11. Target Disetujui</th>
                            <th class="py-3">12. Status Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($eskalasis) && count($eskalasis) > 0)
                            @foreach($eskalasis as $item)
                            <tr>
                                <td><span class="badge bg-primary px-2 py-1">{{ $item->action_id ?? '-' }}</span></td>
                                <td>{{ $item->tl_id ?? '-' }}</td>
                                <td class="fw-semibold">{{ $item->unit_kantor ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-danger px-2 py-1">{{ $item->risk_level ?? '-' }}</span></td>
                                <td class="text-center"><span class="badge bg-info text-dark px-2 py-1">{{ $item->jenis_aksi ?? '-' }}</span></td>
                                <td class="text-center">{{ $item->tanggal_aksi_permintaan ?? '-' }}</td>
                                <td class="text-wrap" style="max-width: 250px;">{{ $item->alasan ?? '-' }}</td>
                                <td class="text-center">{{ $item->target_baru_diminta ?? '-' }}</td>
                                <td>{{ $item->level_persetujuan ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-success px-2 py-1">{{ $item->keputusan ?? '-' }}</span></td>
                                <td class="text-center">{{ $item->target_baru_disetujui ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-secondary px-2 py-1">{{ $item->status_aksi ?? '-' }}</span></td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="12" class="text-center text-muted py-5 fw-semibold">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                    Belum ada riwayat perpanjangan atau eskalasi.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEskalasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-up-right-circle me-2"></i> Form Aksi Perpanjangan / Eskalasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('monitoring.eskalasi.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">TL ID</label>
                            <input type="text" name="tl_id" class="form-control" required placeholder="TL-20261001-0001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Jenis Aksi</label>
                            <select name="jenis_aksi" class="form-select" required>
                                <option value="PERPANJANGAN TARGET">PERPANJANGAN TARGET</option>
                                <option value="ESKALASI DIREKSI">ESKALASI DIREKSI</option>
                                <option value="REOPENING">REOPENING</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tanggal Permintaan</label>
                            <input type="date" name="tanggal_aksi_permintaan" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Target Baru Diminta</label>
                            <input type="date" name="target_baru_diminta" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">Alasan Permintaan / Eskalasi</label>
                            <textarea name="alasan" class="form-control" rows="3" required placeholder="Uraikan kendala atau dasar eskalasi..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Proses Aksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .modal { z-index: 999999 !important; }
    .modal-backdrop { z-index: 999990 !important; background-color: rgba(0,0,0,0.5) !important; }
</style>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modalEl = document.getElementById('modalEskalasi');
        if (modalEl) { document.body.appendChild(modalEl); }
    });
</script>
@endsection