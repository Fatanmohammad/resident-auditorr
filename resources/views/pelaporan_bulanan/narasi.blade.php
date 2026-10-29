@extends('layouts.app')
@section('title', 'Narasi Laporan Bulanan')

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
    .narasi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .narasi-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.85rem 1.25rem;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Header Modul --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a8a;">
                <i class="bi bi-journal-text me-2"></i>Pengisian Narasi Kualitatif Laporan Bulanan
            </h4>
            <p class="text-muted small mb-0">
                Penyusunan 6 Blok Narasi Formal untuk Lembar Laporan Bulanan Eksekutif
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            @if($kalender)
            <a href="{{ route('pelaporan-bulanan.laporan-bulanan', $kalender->report_id) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-printer me-1"></i> Cetak Laporan Bulanan
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
        <form method="GET" action="{{ route('pelaporan-bulanan.narasi') }}" class="row g-2 align-items-center">
            <div class="col-md-7">
                <label class="form-label small fw-bold text-muted mb-1">Pilih Laporan Bulanan (Report ID)</label>
                <select name="reportId" class="form-select form-select-sm fw-bold" onchange="if(this.value) { window.location.href = '{{ url('/pelaporan-bulanan/narasi') }}/' + this.value; } else { this.form.submit(); }">
                    @forelse($availableReports as $rep)
                        <option value="{{ $rep->report_id }}" {{ ($reportId == $rep->report_id) ? 'selected' : '' }}>
                            {{ $rep->report_id }} - [{{ $rep->kode_unit }}] {{ $rep->nama_unit }} ({{ \Carbon\Carbon::parse($rep->periode)->translatedFormat('F Y') }})
                        </option>
                    @empty
                        <option value="">-- Belum ada laporan terinisiasi --</option>
                    @endforelse
                </select>
            </div>
            <div class="col-md-5 text-md-end mt-2 mt-md-0">
                @if($kalender)
                    <span class="badge bg-light text-dark border px-2 py-1">Resident Auditor: {{ $kalender->resident_auditor }}</span>
                @endif
            </div>
        </form>
    </div>

    @if(!$kalender || !$narasi)
        <div class="alert alert-warning text-center py-4">
            <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
            Silakan inisiasi kalender laporan terlebih dahulu pada menu <a href="{{ route('pelaporan-bulanan.kalender') }}" class="fw-bold">Kalender Laporan</a>.
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
                    <small>Dokumen narasi laporan telah disetujui final oleh Pengawas/Reviewer SKAI. Pengeditan narasi telah dikunci.</small>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('pelaporan-bulanan.narasi.update', $kalender->report_id) }}">
            @csrf

            <div class="row g-3">
                {{-- 1. Ringkasan Eksekutif --}}
                <div class="col-md-6">
                    <div class="narasi-card">
                        <div class="narasi-header d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark small"><i class="bi bi-card-text me-1 text-primary"></i> 1. Ringkasan Eksekutif</span>
                        </div>
                        <div class="p-3">
                            <textarea name="ringkasan_eksekutif" rows="4" class="form-control form-control-sm" placeholder="Rangkuman umum pelaksanaan tugas audit dan kondisi pengawasan cabang..." {{ $isLocked ? 'disabled' : '' }}>{{ $narasi->ringkasan_eksekutif }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 2. Hasil Utama --}}
                <div class="col-md-6">
                    <div class="narasi-card">
                        <div class="narasi-header d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark small"><i class="bi bi-trophy me-1 text-primary"></i> 2. Hasil Utama & Temuan Kunci</span>
                        </div>
                        <div class="p-3">
                            <textarea name="hasil_utama" rows="4" class="form-control form-control-sm" placeholder="Temuan paling signifikan, kelemahan kontrol internal, dan hasil pengujian sampel..." {{ $isLocked ? 'disabled' : '' }}>{{ $narasi->hasil_utama }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 3. Analisis Risiko / Tren --}}
                <div class="col-md-6">
                    <div class="narasi-card">
                        <div class="narasi-header d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark small"><i class="bi bi-graph-up-arrow me-1 text-primary"></i> 3. Analisis Risiko & Tren</span>
                        </div>
                        <div class="p-3">
                            <textarea name="analisis_risiko_tren" rows="4" class="form-control form-control-sm" placeholder="Perkembangan tren risiko operasional, kepatuhan, atau penyimpangan berulang..." {{ $isLocked ? 'disabled' : '' }}>{{ $narasi->analisis_risiko_tren }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 4. Kendala Pelaksanaan --}}
                <div class="col-md-6">
                    <div class="narasi-card">
                        <div class="narasi-header d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark small"><i class="bi bi-exclamation-triangle me-1 text-primary"></i> 4. Kendala Pengawasan</span>
                        </div>
                        <div class="p-3">
                            <textarea name="kendala" rows="4" class="form-control form-control-sm" placeholder="Hambatan operasional lapangan, ketersediaan data, atau respon auditee..." {{ $isLocked ? 'disabled' : '' }}>{{ $narasi->kendala }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 5. Rencana Bulan Berikut --}}
                <div class="col-md-6">
                    <div class="narasi-card">
                        <div class="narasi-header d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark small"><i class="bi bi-calendar-check me-1 text-primary"></i> 5. Rencana Bulan Berikut</span>
                        </div>
                        <div class="p-3">
                            <textarea name="rencana_bulan_berikut" rows="4" class="form-control form-control-sm" placeholder="Fokus area pemeriksaan dan target sampling untuk bulan berikutnya..." {{ $isLocked ? 'disabled' : '' }}>{{ $narasi->rencana_bulan_berikut }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 6. Arahan yang Diperlukan --}}
                <div class="col-md-6">
                    <div class="narasi-card">
                        <div class="narasi-header d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark small"><i class="bi bi-megaphone me-1 text-primary"></i> 6. Arahan yang Diperlukan</span>
                        </div>
                        <div class="p-3">
                            <textarea name="arahan_diperlukan" rows="4" class="form-control form-control-sm" placeholder="Dukungan manajemen SKAI / Korwas yang diperlukan terkait eskalasi atau tindak lanjut..." {{ $isLocked ? 'disabled' : '' }}>{{ $narasi->arahan_diperlukan }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="small text-muted">
                    Laporan: <strong>{{ $kalender->report_id }}</strong> | Terakhir disimpan: {{ $narasi->updated_at ? $narasi->updated_at->diffForHumans() : '-' }}
                </span>
                <button type="submit" class="btn btn-primary px-4 shadow-sm" {{ $isLocked ? 'disabled' : '' }}>
                    <i class="bi bi-save me-1"></i> Simpan Seluruh Narasi Laporan
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
