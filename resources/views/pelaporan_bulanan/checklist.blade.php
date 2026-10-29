@extends('layouts.app')
@section('title', 'Checklist Kelengkapan Laporan (WP-RA-08-B) - SOP 08')

@section('content')
<style>
    .selector-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.15rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .status-banner {
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
        color: #fff;
    }
    .status-banner.siap {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    }
    .status-banner.belum {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
    }
    .checklist-row {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        margin-bottom: 0.65rem;
        transition: all 0.2s;
    }
    .checklist-row:hover {
        border-color: #cbd5e1;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Header Modul --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a8a;">
                <i class="bi bi-ui-checks me-2"></i>Checklist Kelengkapan Dokumen (WP-RA-08-B)
            </h4>
            <p class="text-muted small mb-0">
                Verifikasi 12 Komponen Kelengkapan Laporan Bulanan Sebelum Disubmit untuk Review Unit RA / Korwas
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="{{ route('pelaporan-bulanan.kalender') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kalender
            </a>
            @if($kalender)
            <a href="{{ route('pelaporan-bulanan.laporan-bulanan', $kalender->report_id) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-printer me-1"></i> Cetak Laporan (WP-RA-08-C)
            </a>
            @endif
        </div>
    </div>

    {{-- Tabs Navigasi Modul --}}
    @include('pelaporan_bulanan.tabs')

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Selector Report ID --}}
    <div class="selector-card">
        <form method="GET" action="{{ route('pelaporan-bulanan.checklist') }}" class="row g-2 align-items-center">
            <div class="col-md-7">
                <label class="form-label small fw-bold text-muted mb-1">Pilih Laporan Bulanan (Report ID)</label>
                <select name="reportId" class="form-select form-select-sm fw-bold" onchange="this.form.submit()">
                    @forelse($availableReports as $rep)
                        <option value="{{ $rep->report_id }}" {{ ($reportId == $rep->report_id) ? 'selected' : '' }}>
                            {{ $rep->report_id }} - [{{ $rep->kode_unit }}] {{ $rep->nama_unit }} (Periode: {{ \Carbon\Carbon::parse($rep->periode)->translatedFormat('F Y') }})
                        </option>
                    @empty
                        <option value="">-- Belum ada laporan terinisiasi --</option>
                    @endforelse
                </select>
            </div>
            <div class="col-md-5 d-flex justify-content-md-end gap-2 align-items-end mt-3 mt-md-0">
                @if($kalender)
                    <div class="text-end">
                        <small class="text-muted d-block">Status Kunci Dokumen:</small>
                        <span class="badge {{ $kalender->status_final === 'APPROVED/FINAL' ? 'bg-success' : 'bg-secondary' }}">
                            {{ $kalender->status_final }}
                        </span>
                    </div>
                @endif
            </div>
        </form>
    </div>

    @if(!$kalender || !$checklist)
        <div class="alert alert-warning text-center py-4">
            <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
            Silakan inisiasi kalender laporan terlebih dahulu pada menu <a href="{{ route('pelaporan-bulanan.kalender') }}" class="fw-bold">Kalender Laporan (WP-RA-08-A)</a>.
        </div>
    @else
        @php
            $isLocked = ($kalender->status_final === 'APPROVED/FINAL') && !in_array(auth()->user()->role ?? '', ['admin', 'kabag_ra', 'kadiv_skai', 'reviewer']);
        @endphp

        @if($isLocked)
            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-3 d-flex align-items-center">
                <i class="bi bi-lock-fill fs-4 me-3 text-success"></i>
                <div>
                    <h6 class="fw-bold mb-0">Laporan Telah Berstatus APPROVED/FINAL</h6>
                    <small>Dokumen laporan telah disetujui final oleh Pengawas/Reviewer SKAI. Pengeditan form telah dikunci untuk menjaga integritas versi final.</small>
                </div>
            </div>
        @endif

        {{-- Banner Status Kesiapan (Kolom N, O, P) --}}
        <div class="status-banner {{ $checklist->status_kesiapan === 'SIAP REVIEW' ? 'siap' : 'belum' }} shadow-sm">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi {{ $checklist->status_kesiapan === 'SIAP REVIEW' ? 'bi-patch-check-fill' : 'bi-hourglass-split' }} fs-1"></i>
                        <div>
                            <div class="text-uppercase small fw-bold text-white-50">Status Kesiapan (Kolom P)</div>
                            <h3 class="fw-bold mb-0">{{ $checklist->status_kesiapan }}</h3>
                            <div class="small mt-1 text-white-50">
                                @if($checklist->status_kesiapan === 'SIAP REVIEW')
                                    Seluruh 12 komponen kelengkapan terpenuhi (0 Gap). Dokumen siap disubmit untuk direview oleh Unit RA / Korwas.
                                @else
                                    Terdapat {{ $checklist->jumlah_gap }} komponen belum lengkap atau masih kosong. Lengkapi checklist sebelum men-submit.
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 mt-3 mt-md-0">
                    <div class="row g-2 text-center text-md-end">
                        <div class="col-6">
                            <div class="bg-white bg-opacity-25 rounded-3 p-2">
                                <div class="small text-uppercase text-white-50 fw-bold">% Lengkap (N)</div>
                                <div class="fs-4 fw-bold">{{ number_format($checklist->persen_lengkap, 0) }}%</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-25 rounded-3 p-2">
                                <div class="small text-uppercase text-white-50 fw-bold">Jumlah Gap (O)</div>
                                <div class="fs-4 fw-bold">{{ $checklist->jumlah_gap }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Progress Bar --}}
            <div class="progress mt-3" style="height: 8px; background: rgba(255,255,255,0.3);">
                <div class="progress-bar bg-white" role="progressbar" style="width: {{ $checklist->persen_lengkap }}%;"></div>
            </div>
        </div>

        {{-- Form Checklist 12 Komponen (Kolom B s.d. M) --}}
        <form method="POST" action="{{ route('pelaporan-bulanan.checklist.update', $checklist->report_id) }}">
            @csrf
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-card-checklist me-2 text-primary"></i>Evaluasi 12 Komponen Kelengkapan (Kolom B s.d. M)
                    </h6>
                    <small class="text-muted">Pilih status kelengkapan untuk masing-masing item</small>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        @php
                            $hurufKolom = ['B','C','D','E','F','G','H','I','J','K','L','M'];
                            $i = 0;
                        @endphp
                        @foreach($komponenList as $key => $label)
                        @php
                            $val = $checklist->$key;
                            $kolom = $hurufKolom[$i++];
                        @endphp
                        <div class="col-md-6">
                            <div class="checklist-row d-flex align-items-center justify-content-between">
                                <div class="me-2">
                                    <span class="badge bg-light text-dark border me-1">{{ $kolom }}</span>
                                    <span class="fw-bold text-dark small">{{ $label }}</span>
                                </div>
                                <div style="min-width: 170px;">
                                    <select name="{{ $key }}" class="form-select form-select-sm fw-semibold {{ $val === 'LENGKAP' ? 'text-success border-success' : ($val === 'TIDAK RELEVAN' ? 'text-muted' : 'text-danger border-danger') }}" {{ $isLocked ? 'disabled' : '' }}>
                                        <option value="LENGKAP" {{ $val === 'LENGKAP' ? 'selected' : '' }}>✅ LENGKAP</option>
                                        <option value="BELUM LENGKAP" {{ $val === 'BELUM LENGKAP' ? 'selected' : '' }}>❌ BELUM LENGKAP</option>
                                        <option value="TIDAK RELEVAN" {{ $val === 'TIDAK RELEVAN' ? 'selected' : '' }}>⚪ TIDAK RELEVAN</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Kolom Q: Catatan Checklist --}}
                    <div class="mt-3 pt-3 border-top">
                        <label class="form-label small fw-bold text-muted">
                            Catatan Checklist (Kolom Q) <span class="text-danger">* Wajib diisi jika terdapat komponen yang berstatus "TIDAK RELEVAN"</span>
                        </label>
                        <textarea name="catatan" rows="3" class="form-control form-control-sm" placeholder="Berikan keterangan atau justifikasi kelengkapan / komponen tidak relevan..." {{ $isLocked ? 'disabled' : '' }}>{{ $checklist->catatan }}</textarea>
                    </div>
                </div>
                <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
                    <span class="small text-muted">
                        Terakhir diperbarui: {{ $checklist->updated_at ? $checklist->updated_at->diffForHumans() : '-' }}
                    </span>
                    <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm" {{ $isLocked ? 'disabled' : '' }}>
                        <i class="bi bi-save me-1"></i> Simpan & Hitung Ulang Kesiapan
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection
