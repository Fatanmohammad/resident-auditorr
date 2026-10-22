@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-primary"><i class="bi bi-journal-text me-2"></i> WP-RA-07-D | LOG BUKTI & KOMUNIKASI</h4>
            <p class="text-muted mb-0" style="font-size: 13px;">Pencatatan riwayat pengiriman reminder dan penerimaan dokumen bukti tindak lanjut</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('monitoring.export_excel', 'log_bukti') }}" class="btn btn-sm btn-outline-success shadow-sm fw-semibold">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
            <button type="button" class="btn btn-sm btn-primary shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahLog">
                <i class="bi bi-plus-circle me-1"></i> Tambah Log Bukti
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
                            <th class="py-3">1. Log ID</th>
                            <th class="py-3">2. TL ID</th>
                            <th class="py-3">3. Unit / Kantor</th>
                            <th class="py-3">4. Tanggal Komunikasi</th>
                            <th class="py-3">5. Jenis Komunikasi</th>
                            <th class="py-3">6. Saluran</th>
                            <th class="py-3" style="min-width: 250px;">7. Ringkasan Komunikasi</th>
                            <th class="py-3">8. Bukti Diterima</th>
                            <th class="py-3">9. Referensi Bukti</th>
                            <th class="py-3">10. Status Log</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($logs) && count($logs) > 0)
                            @foreach($logs as $item)
                            <tr>
                                <td><span class="badge bg-primary px-2 py-1">{{ $item->log_id ?? '-' }}</span></td>
                                <td>{{ $item->tl_id ?? '-' }}</td>
                                <td class="fw-semibold">{{ $item->unit_kantor ?? '-' }}</td>
                                <td class="text-center">{{ $item->tanggal_komunikasi ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-info text-dark px-2 py-1">{{ $item->jenis_komunikasi ?? '-' }}</span></td>
                                <td>{{ $item->saluran ?? '-' }}</td>
                                <td class="text-wrap" style="max-width: 300px;">{{ $item->ringkasan_komunikasi ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-{{ ($item->bukti_diterima ?? '') == 'YA' ? 'success' : 'secondary' }} px-2 py-1">{{ $item->bukti_diterima ?? '-' }}</span></td>
                                <td>{{ $item->referensi_bukti ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-secondary px-2 py-1">{{ $item->status_log ?? '-' }}</span></td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5 fw-semibold">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                    Belum ada riwayat log bukti & komunikasi.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahLog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i> Tambah Log Bukti & Komunikasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('monitoring.log_bukti.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">TL ID</label>
                            <input type="text" name="tl_id" class="form-control" placeholder="Contoh: TL-20261001-0001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tanggal Komunikasi</label>
                            <input type="date" name="tanggal_komunikasi" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Jenis Komunikasi</label>
                            <select name="jenis_komunikasi" class="form-select" required>
                                <option value="PENGIRIMAN REMINDER">PENGIRIMAN REMINDER</option>
                                <option value="PENERIMAAN BUKTI">PENERIMAAN BUKTI</option>
                                <option value="DISCUSSI / KLARIFIKASI">DISCUSSI / KLARIFIKASI</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Saluran</label>
                            <input type="text" name="saluran" class="form-control" placeholder="Contoh: Email / Nota Dinas / Whatsapp" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">Ringkasan Komunikasi</label>
                            <textarea name="ringkasan_komunikasi" class="form-control" rows="3" placeholder="Tulis ringkasan hasil komunikasi..." required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Bukti Diterima?</label>
                            <select name="bukti_diterima" class="form-select">
                                <option value="YA">YA</option>
                                <option value="TIDAK">TIDAK</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Referensi Bukti</label>
                            <input type="text" name="referensi_bukti" class="form-control" placeholder="No. Dokumen / Memo Bukti">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Simpan Log</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection