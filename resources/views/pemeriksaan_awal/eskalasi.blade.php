@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Modul -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-arrow-up-right-circle me-2"></i> KEPUTUSAN ESKALASI KASUS (WP-RA-09-G)
            </h4>
            <p class="text-muted small mb-0">
                Penetapan simpulan akhir fact-finding awal dan disposisi kasus ke Tim Investigasi SKAI atau Pembinaan
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pemeriksaan_awal.triase') }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
                <i class="bi bi-diagram-3 me-1"></i> Triase Awal
            </a>
            <a href="{{ route('pemeriksaan_awal.kka') }}" class="btn btn-outline-primary btn-sm px-3 fw-semibold">
                <i class="bi bi-journal-check me-1"></i> KKA Fact-Finding
            </a>
            <button type="button" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddEskalasi">
                <i class="bi bi-send-check me-1"></i> Buat Keputusan Eskalasi
            </button>
        </div>
    </div>

    <!-- Tab Navigasi SOP 09 -->
    @include('pemeriksaan_awal.partials.nav_tabs')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pipeline Guidance: Alur Kerja Penanganan Kasus SOP 09 -->
    <div class="card border-0 bg-light shadow-sm rounded-3 mb-4 p-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center">
                <a href="{{ route('pemeriksaan_awal.register') }}" class="text-decoration-none text-muted small fw-semibold me-2">
                    <i class="bi bi-1-circle me-1"></i> Register Indikasi
                </a>
                <i class="bi bi-chevron-right text-muted me-2"></i>
                <a href="{{ route('pemeriksaan_awal.triase') }}" class="text-decoration-none text-muted small fw-semibold me-2">
                    <i class="bi bi-2-circle me-1"></i> Triase &amp; Skoring
                </a>
                <i class="bi bi-chevron-right text-muted me-2"></i>
                <a href="{{ route('pemeriksaan_awal.bukti_custody') }}" class="text-decoration-none text-muted small fw-semibold me-2">
                    <i class="bi bi-3-circle me-1"></i> Chain of Custody
                </a>
                <i class="bi bi-chevron-right text-muted me-2"></i>
                <a href="{{ route('pemeriksaan_awal.kka') }}" class="text-decoration-none text-muted small fw-semibold me-2">
                    <i class="bi bi-4-circle me-1"></i> KKA Fact-Finding
                </a>
                <i class="bi bi-chevron-right text-muted me-2"></i>
                <span class="badge bg-primary rounded-pill px-3 py-2 fw-semibold">
                    <i class="bi bi-5-circle-fill me-1"></i> Keputusan &amp; Eskalasi
                </span>
            </div>
            <div class="small text-muted">
                <i class="bi bi-info-circle me-1 text-primary"></i> Standar Formulir Kerja: <strong>WP-RA-09-G</strong>
            </div>
        </div>
    </div>

    <!-- Cards Ringkasan Stat -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-danger rounded-3 h-100">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Dieskalasi ke Investigasi</span>
                    <h3 class="fw-bold text-danger mb-1">{{ isset($eskalasis) ? $eskalasis->where('keputusan_final', 'ESKALASI_INVESTIGASI')->count() : 0 }}</h3>
                    <small class="text-muted">Diteruskan ke Tim Investigasi SKAI (SOP 10)</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-info rounded-3 h-100">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Pembinaan Internal</span>
                    <h3 class="fw-bold text-info mb-1">{{ isset($eskalasis) ? $eskalasis->where('keputusan_final', 'PEMBINAAN_INTERNAL')->count() : 0 }}</h3>
                    <small class="text-muted">Perbaikan Prosedur &amp; Pembinaan Cabang</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-secondary rounded-3 h-100">
                <div class="card-body py-3">
                    <span class="text-muted small fw-bold text-uppercase">Kasus Ditutup / Arsip</span>
                    <h3 class="fw-bold text-secondary mb-1">{{ isset($eskalasis) ? $eskalasis->where('keputusan_final', 'TUTUP_KASUS')->count() : 0 }}</h3>
                    <small class="text-muted">Tidak Cukup Bukti Fraud</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <form action="{{ route('pemeriksaan_awal.eskalasi') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nomor keputusan, ID kasus, unit kerja, ringkasan fakta...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="keputusan" class="form-select form-select-sm">
                        <option value="">-- Semua Keputusan Final --</option>
                        <option value="ESKALASI_INVESTIGASI" {{ request('keputusan') == 'ESKALASI_INVESTIGASI' ? 'selected' : '' }}>ESKALASI INVESTIGASI (SOP 10)</option>
                        <option value="PEMBINAAN_INTERNAL" {{ request('keputusan') == 'PEMBINAAN_INTERNAL' ? 'selected' : '' }}>PEMBINAAN INTERNAL</option>
                        <option value="TUTUP_KASUS" {{ request('keputusan') == 'TUTUP_KASUS' ? 'selected' : '' }}>TUTUP KASUS / ARSIP</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['q', 'keputusan']))
                        <a href="{{ route('pemeriksaan_awal.eskalasi') }}" class="btn btn-light btn-sm border" title="Reset filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Lembar Keputusan -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-file-earmark-lock me-2 text-primary"></i> Lembar Penetapan Keputusan Final &amp; Disposisi Eskalasi</h6>
                <small class="text-muted">Otorisasi penetapan hasil fact-finding Kepala Divisi SKAI Bank Sulteng</small>
            </div>
            <span class="badge bg-primary rounded-pill px-3 py-2">{{ count($eskalasis ?? []) }} Data Terdaftar</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 140px;">No. Keputusan</th>
                            <th>ID Indikasi &amp; Unit</th>
                            <th>Tanggal Keputusan</th>
                            <th>Ringkasan Fakta Utama</th>
                            <th>Estimasi Kerugian</th>
                            <th>Keputusan Final SKAI</th>
                            <th>No. Nota Dinas</th>
                            <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($eskalasis ?? [] as $item)
                            <tr>
                                <td class="ps-3 fw-bold font-monospace text-primary">
                                    <span class="badge bg-light text-dark border">{{ $item->decision_id ?? ('DEC-' . $item->id) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border border-primary font-monospace fw-bold">
                                        {{ $item->indikasi_id }}
                                    </span>
                                    <span class="fw-semibold text-dark d-block mt-1">
                                        {{ $item->unit ?? ($item->registerIndikasi->nama_unit ?? 'Unit Bank') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-dark d-block">
                                        <i class="bi bi-calendar-event me-1 text-muted"></i>{{ $item->tanggal_keputusan }}
                                    </span>
                                    <small class="text-muted">{{ $item->diputuskan_oleh ?? 'Kadiv SKAI' }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark" style="max-width: 250px;">
                                        {{ Str::limit($item->fakta_utama ?? $item->arahan_tindakan_segera ?? '-', 60) }}
                                    </div>
                                    <small class="text-muted">
                                        Rekomendasi: {{ $item->rekomendasi_tim ?? $item->saran_sistem ?? '-' }}
                                    </small>
                                </td>
                                <td>
                                    <span class="fw-bold text-danger">
                                        Rp {{ number_format($item->estimasi_kerugian ?? ($item->registerIndikasi->nominal_awal ?? 0)) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $kep = $item->keputusan_final;
                                        $kepBadge = match($kep) {
                                            'ESKALASI_INVESTIGASI' => 'bg-danger text-white',
                                            'PEMBINAAN_INTERNAL' => 'bg-info text-dark',
                                            default => 'bg-secondary text-white',
                                        };
                                    @endphp
                                    <span class="badge {{ $kepBadge }} px-2 py-1">
                                        {{ str_replace('_', ' ', $kep) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border font-monospace">
                                        {{ $item->nomor_nota_dinas ?? ($item->laporan_insidentil_id ?? '-') }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary py-0 px-2 btn-detail-eskalasi"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalDetailEskalasi"
                                            data-dec="{{ $item->decision_id ?? ('DEC-' . $item->id) }}"
                                            data-id="{{ $item->indikasi_id }}"
                                            data-unit="{{ $item->unit ?? ($item->registerIndikasi->nama_unit ?? 'Unit') }}"
                                            data-tgl="{{ $item->tanggal_keputusan }}"
                                            data-fakta="{{ $item->fakta_utama ?? $item->arahan_tindakan_segera ?? '-' }}"
                                            data-nominal="Rp {{ number_format($item->estimasi_kerugian ?? ($item->registerIndikasi->nominal_awal ?? 0)) }}"
                                            data-rekomendasi="{{ $item->rekomendasi_tim ?? $item->saran_sistem ?? '-' }}"
                                            data-keputusan="{{ $item->keputusan_final }}"
                                            data-nd="{{ $item->nomor_nota_dinas ?? ($item->laporan_insidentil_id ?? '-') }}"
                                            data-pejabat="{{ $item->diputuskan_oleh ?? 'Kadiv SKAI' }}"
                                            title="Lihat Detail Keputusan">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary"></i>
                                    <strong>Belum ada keputusan eskalasi kasus yang tersimpan.</strong>
                                    <p class="small text-muted mb-3">Gunakan tombol di bawah untuk menetapkan keputusan akhir hasil fact-finding.</p>
                                    <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalAddEskalasi">
                                        <i class="bi bi-send-check me-1"></i> Buat Keputusan Eskalasi
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL FORM ESKALASI KASUS -->
<div class="modal fade" id="modalAddEskalasi" tabindex="-1" aria-labelledby="modalAddEskalasiLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('pemeriksaan_awal.eskalasi.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold" id="modalAddEskalasiLabel">
                        <i class="bi bi-send-check me-2"></i> Form Penetapan Keputusan &amp; Eskalasi Kasus (WP-RA-09-G)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3 p-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold small">Pilih ID Indikasi Fraud <span class="text-danger">*</span></label>
                        <select name="indikasi_id" id="eskalasi_select_indikasi" class="form-select form-select-sm" required onchange="
                            var opt = this.options[this.selectedIndex];
                            if(this.value){
                                var nominal = opt.getAttribute('data-nominal') || '0';
                                document.getElementById('esk_estimasi_kerugian').value = nominal;
                                document.getElementById('esk_preview_nominal').textContent = 'Terbaca: Rp ' + Number(nominal).toLocaleString('id-ID');
                                document.getElementById('esk_fakta_utama').value = opt.getAttribute('data-fakta') || opt.getAttribute('data-redflag') || '';
                            }
                        ">
                            <option value="">-- Pilih ID Indikasi Kasus --</option>
                            @foreach($indikasis ?? [] as $ind)
                                <option value="{{ $ind->indikasi_id }}"
                                        data-unit="[{{ $ind->kode_unit ?? '-' }}] {{ $ind->nama_unit }}"
                                        data-nominal="{{ $ind->nominal_awal }}"
                                        data-redflag="{{ $ind->red_flag_utama }}"
                                        data-fakta="{{ $ind->fakta_awal_terverifikasi }}">
                                    {{ $ind->indikasi_id }} &bull; [{{ $ind->kode_unit ?? '-' }}] {{ $ind->nama_unit }} &bull; {{ Str::limit($ind->red_flag_utama ?? $ind->area, 45) }} (Rp {{ number_format($ind->nominal_awal ?? 0) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Tanggal Penetapan Keputusan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_keputusan" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Nomor Nota Dinas / Surat Eskalasi</label>
                        <input type="text" name="nomor_nota_dinas" value="ND/{{ date('Ymd') }}/SKAI-FRAUD/{{ rand(100, 999) }}" class="form-control form-control-sm" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold small">Ringkasan Fakta Fact-Finding Awal <span class="text-danger">*</span></label>
                        <textarea name="fakta_utama" id="esk_fakta_utama" class="form-control form-control-sm" rows="3" placeholder="Uraikan temuan utama hasil fact-finding, klarifikasi bukti, dan potensi kerugian bank..." required></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Estimasi Total Paparan Kerugian Bank (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="estimasi_kerugian" id="esk_estimasi_kerugian" class="form-control form-control-sm" placeholder="0" required oninput="
                            var val = Number(this.value);
                            document.getElementById('esk_preview_nominal').textContent = 'Terbaca: Rp ' + val.toLocaleString('id-ID');
                        ">
                        <div class="form-text small fw-semibold text-primary" id="esk_preview_nominal">Terbaca: Rp 0</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Rekomendasi Tim Fact-Finder <span class="text-danger">*</span></label>
                        <select name="rekomendasi_tim" class="form-select form-select-sm" required>
                            <option value="INVESTIGASI_LANJUT">REKOMENDASI AUDIT INVESTIGASI (SOP 10)</option>
                            <option value="PEMBINAAN_INTERNAL">REKOMENDASI PERBAIKAN SISTEM &amp; PEMBINAAN</option>
                            <option value="TUTUP_TIDAK_CUKUP_BUKTI">TUTUP / TIDAK CUKUP BUKTI FRAUD</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold small">Keputusan Final Kadiv SKAI <span class="text-danger">*</span></label>
                        <select name="keputusan_final" class="form-select form-select-sm fw-bold border-primary" required>
                            <option value="ESKALASI_INVESTIGASI">ESKALASI KE TIM INVESTIGASI (SOP 10)</option>
                            <option value="PEMBINAAN_INTERNAL">PEMBINAAN MANAJEMEN CABANG / REKOMENDASI KEPATUHAN</option>
                            <option value="TUTUP_KASUS">ARSIP / CASING CLOSED (TIDAK CUKUP BUKTI)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">
                        <i class="bi bi-save me-1"></i> Simpan Keputusan Final
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DETAIL KEPUTUSAN ESKALASI -->
<div class="modal fade" id="modalDetailEskalasi" tabindex="-1" aria-labelledby="modalDetailEskalasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold" id="modalDetailEskalasiLabel">
                    <i class="bi bi-file-earmark-lock me-2"></i> Rincian Disposisi Keputusan Final (WP-RA-09-G)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded border">
                    <div>
                        <span class="badge bg-primary fs-6 font-monospace px-3 py-2" id="det_esk_dec">-</span>
                        <span class="badge bg-secondary ms-2" id="det_esk_id">-</span>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Tanggal Penetapan</small>
                        <span class="fw-bold text-dark" id="det_esk_tgl">-</span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Unit Kerja Terkait</label>
                        <div class="fw-bold text-dark" id="det_esk_unit">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Nomor Nota Dinas SKAI</label>
                        <div class="fw-bold text-primary font-monospace" id="det_esk_nd">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Estimasi Paparan Kerugian</label>
                        <div class="fw-bold text-danger fs-5" id="det_esk_nominal">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Pejabat Pengambil Keputusan</label>
                        <div class="fw-semibold text-dark" id="det_esk_pejabat">-</div>
                    </div>
                    <div class="col-md-12">
                        <label class="text-muted small">Rekomendasi Tim Fact-Finder</label>
                        <div class="fw-semibold text-dark" id="det_esk_rekomendasi">-</div>
                    </div>
                    <div class="col-md-12">
                        <hr class="my-2">
                        <label class="text-muted small fw-bold text-uppercase">Keputusan Final Disposisi</label>
                        <div class="p-3 bg-primary bg-opacity-10 text-primary border border-primary rounded fw-bold fs-6" id="det_esk_keputusan">-</div>
                    </div>
                    <div class="col-md-12">
                        <label class="text-muted small fw-bold text-uppercase">Ringkasan Fakta &amp; Dasar Arahan</label>
                        <div class="p-3 bg-light rounded border small text-dark" id="det_esk_fakta">-</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var modalAdd = document.getElementById('modalAddEskalasi');
        if (modalAdd && modalAdd.parentElement !== document.body) {
            document.body.appendChild(modalAdd);
        }

        var modalDetail = document.getElementById('modalDetailEskalasi');
        if (modalDetail && modalDetail.parentElement !== document.body) {
            document.body.appendChild(modalDetail);
        }

        // Handler untuk tombol detail eskalasi
        document.querySelectorAll('.btn-detail-eskalasi').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('det_esk_dec').textContent = this.getAttribute('data-dec');
                document.getElementById('det_esk_id').textContent = this.getAttribute('data-id');
                document.getElementById('det_esk_tgl').textContent = this.getAttribute('data-tgl');
                document.getElementById('det_esk_unit').textContent = this.getAttribute('data-unit');
                document.getElementById('det_esk_nd').textContent = this.getAttribute('data-nd');
                document.getElementById('det_esk_nominal').textContent = this.getAttribute('data-nominal');
                document.getElementById('det_esk_pejabat').textContent = this.getAttribute('data-pejabat');
                document.getElementById('det_esk_rekomendasi').textContent = this.getAttribute('data-rekomendasi');
                document.getElementById('det_esk_keputusan').textContent = this.getAttribute('data-keputusan');
                document.getElementById('det_esk_fakta').textContent = this.getAttribute('data-fakta');
            });
        });
    });
</script>
@endpush
