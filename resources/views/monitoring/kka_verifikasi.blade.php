@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <!-- Header Tools -->
    <div class="d-flex justify-content-between align-items-center mb-4 action-header-tools">
        <div>
            <h4 class="fw-bold mb-1 text-primary"><i class="bi bi-journal-check me-2"></i> WP-RA-07-E | KERTAS KERJA AUDIT VERIFIKASI BUKTI</h4>
            <p class="text-muted mb-0" style="font-size: 13px;">Pengujian 5 kriteria verifikasi bukti (Relevan, Lengkap, Valid, Korektif, Testable) & Rekomendasi Status</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('monitoring.export_excel', 'kka_verifikasi') }}" class="btn btn-sm btn-outline-success shadow-sm fw-semibold">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
            <button type="button" class="btn btn-sm btn-primary shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahKka">
                <i class="bi bi-plus-circle me-1"></i> Buat KKA Verifikasi
            </button>
        </div>
    </div>

    @include('monitoring.partials.nav_tabs')

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 680px; overflow-y: auto;">
                <table class="table table-bordered table-hover align-middle mb-0 text-nowrap" style="font-size: 11px;">
                    <thead class="table-dark text-center sticky-top" style="z-index: 5;">
                        <tr>
                            <th class="py-3">No KKA</th>
                            <th class="py-3">2. TL ID</th>
                            <th class="py-3">3. Unit / Kantor</th>
                            <th class="py-3">4. Bukti Diharapkan</th>
                            <th class="py-3">5. Closure Authority</th>
                            <th class="py-3">6. Relevan</th>
                            <th class="py-3">7. Lengkap</th>
                            <th class="py-3">8. Valid</th>
                            <th class="py-3">9. Korektif</th>
                            <th class="py-3">10. Dapat Diuji</th>
                            <th class="py-3">11. Populasi Terdampak</th>
                            <th class="py-3">12. Objek Diuji</th>
                            <th class="py-3">13. Coverage</th>
                            <th class="py-3">14. Efektif Setelah Koreksi</th>
                            <th class="py-3">15. Exception</th>
                            <th class="py-3" style="min-width: 250px;">16. Analisis RA</th>
                            <th class="py-3">17. Hasil Verifikasi</th>
                            <th class="py-3">18. Bukti Tambahan Diperlukan</th>
                            <th class="py-3">19. Verifikasi Lapangan</th>
                            <th class="py-3">20. Usulan Status</th>
                            <th class="py-3">21. RA Pemantau</th>
                            <th class="py-3">22. Reviewer</th>
                            <th class="py-3">23. Status Review</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($kkas) && count($kkas) > 0)
                            @foreach($kkas as $item)
                            <tr>
                                <td><span class="badge bg-primary px-2 py-1">{{ $item->no_kka ?? '-' }}</span></td>
                                <td>{{ $item->tl_id ?? '-' }}</td>
                                <td class="fw-semibold">{{ $item->unit_kantor ?? '-' }}</td>
                                <td class="text-wrap" style="max-width: 200px;">{{ $item->bukti_diharapkan ?? '-' }}</td>
                                <td>{{ $item->closure_authority ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-success px-2 py-1">{{ $item->relevan ?? 'YA' }}</span></td>
                                <td class="text-center"><span class="badge bg-success px-2 py-1">{{ $item->lengkap ?? 'YA' }}</span></td>
                                <td class="text-center"><span class="badge bg-success px-2 py-1">{{ $item->valid ?? 'YA' }}</span></td>
                                <td class="text-center"><span class="badge bg-success px-2 py-1">{{ $item->korektif ?? 'YA' }}</span></td>
                                <td class="text-center"><span class="badge bg-success px-2 py-1">{{ $item->dapat_diuji ?? 'YA' }}</span></td>
                                <td class="text-end">{{ $item->populasi_terdampak ?? 0 }}</td>
                                <td class="text-end">{{ $item->objek_diuji ?? 0 }}</td>
                                <td class="text-end fw-bold">{{ $item->coverage ?? '100.0%' }}</td>
                                <td class="text-center"><span class="badge bg-success px-2 py-1">{{ $item->efektif ?? 'EFEKTIF' }}</span></td>
                                <td class="text-wrap" style="max-width: 200px;">{{ $item->exception ?? '-' }}</td>
                                <td class="text-wrap" style="max-width: 250px;">{{ $item->analisis_ra ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-warning text-dark px-2 py-1">{{ $item->hasil_verifikasi ?? 'MEMADAI' }}</span></td>
                                <td class="text-wrap" style="max-width: 200px;">{{ $item->bukti_tambahan ?? '-' }}</td>
                                <td class="text-center">{{ $item->perlu_lapangan ?? 'TIDAK' }}</td>
                                <td class="text-center"><span class="badge bg-info text-dark px-2 py-1">{{ $item->usulan_status ?? 'PROPOSED CLOSED' }}</span></td>
                                <td>{{ $item->ra_pemantau ?? '-' }}</td>
                                <td>{{ $item->reviewer ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-secondary px-2 py-1">{{ $item->status_review ?? 'REVIEWED' }}</span></td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="23" class="text-center text-muted py-5 fw-semibold">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                    Belum ada KKA Verifikasi yang dibuat.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah KKA Verifikasi -->
<div class="modal fade" id="modalTambahKka" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-journal-check me-2"></i> Form Buat KKA Verifikasi Bukti</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('monitoring.kka_verifikasi.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">No KKA</label>
                            <input type="text" name="no_kka" class="form-control" required placeholder="Contoh: KKA-07-001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">TL ID</label>
                            <input type="text" name="tl_id" class="form-control" required placeholder="TL-20261001-0001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Unit / Kantor</label>
                            <input type="text" name="unit_kantor" class="form-control" placeholder="Nama Unit / Kantor" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Closure Authority</label>
                            <input type="text" name="closure_authority" class="form-control" placeholder="Contoh: Kadiv SKAI" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">Bukti Diharapkan</label>
                            <textarea name="bukti_diharapkan" class="form-control" rows="2" placeholder="Rincian bukti yang diharapkan..." required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Populasi Terdampak</label>
                            <input type="number" name="populasi_terdampak" class="form-control" value="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Objek Diuji</label>
                            <input type="number" name="objek_diuji" class="form-control" value="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Hasil Verifikasi</label>
                            <select name="hasil_verifikasi" class="form-select" required>
                                <option value="MEMADAI">MEMADAI</option>
                                <option value="KURANG MEMADAI">KURANG MEMADAI</option>
                                <option value="TIDAK MEMADAI">TIDAK MEMADAI</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Usulan Status</label>
                            <select name="usulan_status" class="form-select" required>
                                <option value="PROPOSED CLOSED">PROPOSED CLOSED</option>
                                <option value="OPEN">OPEN</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">RA Pemantau</label>
                            <input type="text" name="ra_pemantau" class="form-control" placeholder="Nama RA Pemantau" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">Analisis RA</label>
                            <textarea name="analisis_ra" class="form-control" rows="3" placeholder="Hasil analisis Resident Auditor..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Simpan KKA Verifikasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Skrip otomatis menarik modal keluar ke body dan CSS z-index tertinggi -->
<style>
    .modal {
        z-index: 999999 !important;
    }
    .modal-backdrop {
        z-index: 999990 !important;
        background-color: rgba(0, 0, 0, 0.5) !important;
    }
</style>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modalEl = document.getElementById('modalTambahKka');
        if (modalEl) {
            document.body.appendChild(modalEl);
        }
    });
</script>
@endsection