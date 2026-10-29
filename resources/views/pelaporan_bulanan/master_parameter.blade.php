@extends('layouts.app')
@section('title', 'Master Parameter & Hari Libur')

@section('content')
<style>
    .param-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .param-header {
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
                <i class="bi bi-sliders me-2"></i>Konfigurasi Master Parameter & Hari Libur
            </h4>
            <p class="text-muted small mb-0">
                Pengaturan Standar Acuan Sistem, Bobot Quality Score, Ambang Kinerja RA, dan Kalender Libur Nasional (WORKDAY)
            </p>
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

    <div class="row g-3">
        {{-- Kolom Kiri: Form Parameter Sistem & Bobot --}}
        <div class="col-lg-7">
            <form method="POST" action="{{ route('pelaporan-bulanan.master-parameter.update') }}">
                @csrf
                <div class="param-card">
                    <div class="param-header d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark small"><i class="bi bi-gear-fill me-1 text-primary"></i> 1. Parameter Utama</span>
                    </div>
                    <div class="p-3 row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tanggal Cut-off Perhitungan</label>
                            <input type="date" name="tanggal_cutoff" class="form-control form-control-sm" value="{{ \Carbon\Carbon::parse($params->tanggal_cutoff)->format('Y-m-d') }}" required>
                            <div class="form-text small">Kunci tanggal tetap penutupan perhitungan periode agar aging & keterlambatan tidak berubah.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Periode Dashboard Default (B9)</label>
                            <input type="date" name="periode_dashboard" class="form-control form-control-sm" value="{{ \Carbon\Carbon::parse($params->periode_dashboard)->format('Y-m-d') }}" required>
                            <div class="form-text small">Tanggal pertama bulan yang dianalisis (YYYY-MM-01).</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Hari Kerja Jatuh Tempo</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="hari_kerja_jatuh_tempo" class="form-control" value="{{ $params->hari_kerja_jatuh_tempo }}" required>
                                <span class="input-group-text">Hari</span>
                            </div>
                            <div class="form-text small">Default: Hari kerja ke-5 bulan berikutnya.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Reviewer Default</label>
                            <input type="text" name="reviewer_default" class="form-control form-control-sm" value="{{ $params->reviewer_default }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Unit RA Default</label>
                            <input type="text" name="unit_ra_default" class="form-control form-control-sm" value="{{ $params->unit_ra_default }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Target Quality Score</label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.1" name="target_quality_score" class="form-control" value="{{ $params->target_quality_score }}">
                                <span class="input-group-text">Skala 0-100</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Ambang High Attention</label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.1" name="ambang_high_attention" class="form-control" value="{{ $params->ambang_high_attention }}">
                                <span class="input-group-text">Skor</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bobot Quality Score --}}
                <div class="param-card">
                    <div class="param-header d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark small"><i class="bi bi-award-fill me-1 text-primary"></i> 2. Bobot Quality Score (Total 100)</span>
                    </div>
                    <div class="p-3 row g-2">
                        <div class="col-4 col-md-3">
                            <label class="form-label small fw-semibold">Tepat Waktu (20)</label>
                            <input type="number" name="bobot_tepat_waktu" class="form-control form-control-sm" value="{{ $params->bobot_tepat_waktu }}">
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label small fw-semibold">Kelengkapan (20)</label>
                            <input type="number" name="bobot_kelengkapan" class="form-control form-control-sm" value="{{ $params->bobot_kelengkapan }}">
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label small fw-semibold">Akurasi (15)</label>
                            <input type="number" name="bobot_akurasi" class="form-control form-control-sm" value="{{ $params->bobot_akurasi }}">
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label small fw-semibold">Analisis (15)</label>
                            <input type="number" name="bobot_analisis" class="form-control form-control-sm" value="{{ $params->bobot_analisis }}">
                        </div>
                        <div class="col-4 col-md-4">
                            <label class="form-label small fw-semibold">Bukti (10)</label>
                            <input type="number" name="bobot_bukti" class="form-control form-control-sm" value="{{ $params->bobot_bukti }}">
                        </div>
                        <div class="col-4 col-md-4">
                            <label class="form-label small fw-semibold">Tindak Lanjut (10)</label>
                            <input type="number" name="bobot_tindak_lanjut" class="form-control form-control-sm" value="{{ $params->bobot_tindak_lanjut }}">
                        </div>
                        <div class="col-4 col-md-4">
                            <label class="form-label small fw-semibold">Eskalasi (10)</label>
                            <input type="number" name="bobot_eskalasi" class="form-control form-control-sm" value="{{ $params->bobot_eskalasi }}">
                        </div>
                    </div>
                </div>

                {{-- Parameter Kinerja RA --}}
                <div class="param-card">
                    <div class="param-header d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark small"><i class="bi bi-person-badge-fill me-1 text-primary"></i> 3. Parameter Kinerja RA</span>
                    </div>
                    <div class="p-3 row g-2">
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Konsistensi (40%)</label>
                            <input type="number" name="bobot_konsistensi" class="form-control form-control-sm" value="{{ $params->bobot_konsistensi }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Penyelesaian (30%)</label>
                            <input type="number" name="bobot_penyelesaian" class="form-control form-control-sm" value="{{ $params->bobot_penyelesaian }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Kualitas (30%)</label>
                            <input type="number" name="bobot_kualitas" class="form-control form-control-sm" value="{{ $params->bobot_kualitas }}">
                        </div>
                        <div class="col-4 mt-2">
                            <label class="form-label small fw-semibold">Ambang Baik (90)</label>
                            <input type="number" step="0.1" name="ambang_baik_konsisten" class="form-control form-control-sm" value="{{ $params->ambang_baik_konsisten }}">
                        </div>
                        <div class="col-4 mt-2">
                            <label class="form-label small fw-semibold">Ambang Memadai (80)</label>
                            <input type="number" step="0.1" name="ambang_memadai" class="form-control form-control-sm" value="{{ $params->ambang_memadai }}">
                        </div>
                        <div class="col-4 mt-2">
                            <label class="form-label small fw-semibold">Perlu Peningkatan (60)</label>
                            <input type="number" step="0.1" name="ambang_perlu_peningkatan" class="form-control form-control-sm" value="{{ $params->ambang_perlu_peningkatan }}">
                        </div>
                    </div>
                    <div class="card-footer bg-light p-3 text-end border-top">
                        <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                            <i class="bi bi-save me-1"></i> Simpan Parameter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Kolom Kanan: Daftar Hari Libur Nasional & Master Unit --}}
        <div class="col-lg-5">
            {{-- Daftar Hari Libur --}}
            <div class="param-card">
                <div class="param-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark small"><i class="bi bi-calendar-event-fill me-1 text-danger"></i> Daftar Hari Libur Nasional (WORKDAY)</span>
                    <button class="btn btn-outline-danger btn-sm p-1 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTambahLibur" aria-expanded="false">
                        <i class="bi bi-plus-circle me-1"></i> + Libur
                    </button>
                </div>
                {{-- Form Tambah Hari Libur Inline (Tanpa Modal) --}}
                <div class="collapse p-3 bg-light border-bottom" id="collapseTambahLibur">
                    <form method="POST" action="{{ route('pelaporan-bulanan.master-parameter.store-libur') }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-sm-5">
                            <label class="form-label small fw-bold mb-1">Tanggal Libur</label>
                            <input type="date" name="tanggal" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-sm-5">
                            <label class="form-label small fw-bold mb-1">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Contoh: Idul Fitri" required>
                        </div>
                        <div class="col-sm-2 d-grid">
                            <button type="submit" class="btn btn-danger btn-sm">Simpan</button>
                        </div>
                    </form>
                </div>
                <div class="p-2 table-responsive" style="max-height: 280px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead>
                            <tr class="table-light">
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th class="text-center" style="width: 40px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $h)
                            <tr>
                                <td class="font-monospace fw-bold text-nowrap">{{ \Carbon\Carbon::parse($h->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ $h->keterangan }}</td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('pelaporan-bulanan.master-parameter.destroy-libur', $h->id) }}" onsubmit="return confirm('Hapus hari libur ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0" title="Hapus"><i class="bi bi-x-circle"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-2">Belum ada hari libur terdaftar.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Master Unit Bank Sulteng (40 Unit Kerja) --}}
            <div class="param-card">
                <div class="param-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark small"><i class="bi bi-building me-1 text-primary"></i> 40 Unit Kerja Bank Sulteng</span>
                    <span class="badge bg-success">{{ count($masterUnits) }} Unit Aktif</span>
                </div>
                <div class="p-2 table-responsive" style="max-height: 380px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead>
                            <tr class="table-light">
                                <th>Kode</th>
                                <th>Nama Unit</th>
                                <th>Tipe</th>
                                <th>Assigned RA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($masterUnits as $mu)
                            <tr>
                                <td class="font-monospace fw-bold">{{ $mu->kode_unit }}</td>
                                <td class="fw-semibold">{{ $mu->nama_unit }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $mu->jenis_unit }}</span></td>
                                <td class="small text-muted">{{ $mu->assigned_ra }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
