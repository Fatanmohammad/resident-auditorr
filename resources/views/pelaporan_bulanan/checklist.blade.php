@extends('layouts.app')
@section('title', 'Status Kepatuhan KKA & Checklist Laporan')

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
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
        color: #ffffff;
        position: relative;
        overflow: hidden;
    }
    .status-banner.siap {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    }
    .status-banner.belum {
        background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    }
    .checklist-row {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        transition: all 0.2s ease;
    }
    .checklist-row:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    .badge-hijau {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
        font-weight: 700;
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
    }
    .badge-merah {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
        font-weight: 700;
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
    }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Header Modul --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a8a;">
                <i class="bi bi-ui-checks me-2"></i>Status Kepatuhan KKA &amp; Checklist Kelengkapan Laporan
            </h4>
            <p class="text-muted small mb-0">
                Perekaman Otomatis Kepatuhan KKA Seluruh Area (Termasuk Data Nihil) &amp; Mode Preview Resident Auditor
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

    {{-- Mode Preview Notice for RA --}}
    @if($isRa)
        <div class="alert alert-info border-0 shadow-sm rounded-3 mb-3 d-flex align-items-center">
            <i class="bi bi-info-circle-fill fs-4 me-3 text-primary"></i>
            <div>
                <h6 class="fw-bold mb-0">Mode Preview (Read-Only) Resident Auditor</h6>
                <small class="text-muted">
                    Sesuai SOP 08 revisi, Resident Auditor tidak lagi mencentang checklist manual. Seluruh kelengkapan ditarik 100% otomatis dari database pengawasan cabang Anda.
                </small>
            </div>
        </div>
    @endif

    {{-- Selector Report ID --}}
    <div class="selector-card">
        <form method="GET" action="{{ route('pelaporan-bulanan.checklist') }}" class="row g-2 align-items-center">
            <div class="col-md-7">
                <label class="form-label small fw-bold text-muted mb-1">Pilih Laporan Bulanan (Report ID)</label>
                <select name="reportId" class="form-select form-select-sm fw-bold" onchange="if(this.value) { window.location.href = '{{ url('/pelaporan-bulanan/checklist') }}/' + this.value; } else { this.form.submit(); }">
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
            Silakan inisiasi kalender laporan terlebih dahulu pada menu <a href="{{ route('pelaporan-bulanan.kalender') }}" class="fw-bold">Kalender Laporan</a>.
        </div>
    @else
        {{-- KKA COMPLIANCE AUTOMATIC INDICATOR CARD (HIJAU VS MERAH) --}}
        @if($kkaCompliance)
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-shield-check me-2 text-primary"></i>Status Kepatuhan KKA Cabang Otomatis (Database Pull)
                    </h6>
                    <small class="text-muted">Aturan Indikator: 🟢 Hijau (Tuntas/Patuh atau Nihil Terkonfirmasi) vs 🔴 Merah (Ada Tertunggak/Belum Konfirmasi)</small>
                </div>
                <div>
                    @if($kkaCompliance['status'] === 'HIJAU')
                        <span class="badge-hijau fs-6">
                            <i class="bi bi-check-circle-fill me-1"></i> 🟢 HIJAU (Tuntas / Patuh)
                        </span>
                    @else
                        <span class="badge-merah fs-6">
                            <i class="bi bi-exclamation-circle-fill me-1"></i> 🔴 MERAH (Belum Selesai / Tertunggak)
                        </span>
                    @endif
                </div>
            </div>
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">Area Pemeriksaan KKA</th>
                                <th class="text-center" style="width: 12%;">Populasi Data</th>
                                <th class="text-center" style="width: 12%;">Item Selesai</th>
                                <th style="width: 18%;">Kondisi Data</th>
                                <th class="text-center" style="width: 15%;">Indikator Kepatuhan</th>
                                <th class="text-center" style="width: 18%;">Aksi Konfirmasi Nihil</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kkaCompliance['details'] as $area => $stat)
                            <tr>
                                <td><strong>{{ $area }}</strong></td>
                                <td class="text-center font-monospace">{{ $stat['populasi'] }}</td>
                                <td class="text-center font-monospace fw-bold text-primary">{{ $stat['item_selesai'] }}</td>
                                <td>
                                    @if($stat['is_nihil'])
                                        <span class="badge bg-secondary-subtle text-secondary border">0 / Nihil Transaksi</span>
                                        @if($stat['is_confirmed_nihil'])
                                            <span class="badge bg-success-subtle text-success ms-1">Terkonfirmasi</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning ms-1">Belum Dikonfirmasi</span>
                                        @endif
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border">Ada Transaksi ({{ $stat['populasi'] }})</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($stat['status_kepatuhan'] === 'HIJAU')
                                        <span class="badge-hijau"><i class="bi bi-check-circle-fill me-1"></i> 🟢 Tuntas</span>
                                    @else
                                        <span class="badge-merah"><i class="bi bi-x-circle-fill me-1"></i> 🔴 Belum Selesai</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($stat['is_nihil'] && !$stat['is_confirmed_nihil'])
                                        <form method="POST" action="{{ route('pelaporan-bulanan.kka.confirm-nihil') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="kode_unit" value="{{ $kalender->kode_unit }}">
                                            <input type="hidden" name="periode" value="{{ $kalender->periode }}">
                                            <input type="hidden" name="area" value="{{ $area }}">
                                            <input type="hidden" name="catatan" value="Konfirmasi nihil transaksi otomatis oleh Resident Auditor">
                                            <button type="submit" class="btn btn-xs btn-outline-success py-1 px-2" style="font-size: 0.75rem;">
                                                <i class="bi bi-check-lg me-1"></i> Konfirmasi Nihil
                                            </button>
                                        </form>
                                    @elseif($stat['is_nihil'] && $stat['is_confirmed_nihil'])
                                        <span class="text-success small"><i class="bi bi-check2-all me-1"></i> Terkonfirmasi Nihil</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- Checklist Summary Banner --}}
        <div class="status-banner {{ $checklist->status_kesiapan === 'SIAP REVIEW' ? 'siap' : 'belum' }} shadow-sm">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi {{ $checklist->status_kesiapan === 'SIAP REVIEW' ? 'bi-patch-check-fill' : 'bi-hourglass-split' }} fs-1"></i>
                        <div>
                            <div class="text-uppercase small fw-bold text-white-50">Status Kesiapan Laporan</div>
                            <h3 class="fw-bold mb-0">{{ $checklist->status_kesiapan }}</h3>
                            <div class="small mt-1 text-white-50">
                                @if($checklist->status_kesiapan === 'SIAP REVIEW')
                                    Seluruh 12 komponen kelengkapan terpenuhi (0 Gap). Dokumen siap disubmit untuk direview oleh Unit RA / Korwas.
                                @else
                                    Terdapat {{ $checklist->jumlah_gap }} komponen yang belum lengkap. Lengkapi seluruh item agar laporan dapat dilanjutkan.
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="bg-white bg-opacity-25 rounded-3 p-2">
                                <div class="small text-uppercase text-white-50 fw-bold">% Kelengkapan</div>
                                <div class="fs-4 fw-bold">{{ number_format($checklist->persen_lengkap, 0) }}%</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-25 rounded-3 p-2">
                                <div class="small text-uppercase text-white-50 fw-bold">Jumlah Gap</div>
                                <div class="fs-4 fw-bold">{{ $checklist->jumlah_gap }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="progress mt-3" style="height: 8px; background: rgba(255,255,255,0.3);">
                <div class="progress-bar bg-white" role="progressbar" style="width: {{ $checklist->persen_lengkap }}%;"></div>
            </div>
        </div>

        {{-- Evaluasi 12 Komponen (Admin / Read-Only RA) --}}
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-card-checklist me-2 text-primary"></i>Evaluasi 12 Komponen Kelengkapan Dokumen
                </h6>
                <small class="text-muted">{{ $isRa ? 'Tampilan Preview Read-Only' : 'Pengaturan Kelengkapan oleh Admin' }}</small>
            </div>
            <div class="card-body p-3">
                <form method="POST" action="{{ route('pelaporan-bulanan.checklist.update', $checklist->report_id) }}">
                    @csrf
                    <div class="row g-2">
                        @php $counter = 1; @endphp
                        @foreach($komponenList as $key => $label)
                        @php $val = $checklist->$key; @endphp
                        <div class="col-md-6">
                            <div class="checklist-row d-flex align-items-center justify-content-between">
                                <div class="me-2">
                                    <span class="badge bg-light text-dark border me-1">{{ $counter++ }}</span>
                                    <span class="fw-bold text-dark small">{{ $label }}</span>
                                </div>
                                <div style="min-width: 170px;">
                                    @if($isRa)
                                        <span class="badge {{ $val === 'LENGKAP' ? 'badge-hijau' : ($val === 'TIDAK RELEVAN' ? 'bg-light text-muted border' : 'badge-merah') }} px-2 py-1">
                                            {{ $val }}
                                        </span>
                                    @else
                                        <select name="{{ $key }}" class="form-select form-select-sm fw-semibold {{ $val === 'LENGKAP' ? 'text-success border-success' : ($val === 'TIDAK RELEVAN' ? 'text-muted' : 'text-danger border-danger') }}">
                                            <option value="LENGKAP" {{ $val === 'LENGKAP' ? 'selected' : '' }}>LENGKAP</option>
                                            <option value="BELUM LENGKAP" {{ $val === 'BELUM LENGKAP' ? 'selected' : '' }}>BELUM LENGKAP</option>
                                            <option value="TIDAK RELEVAN" {{ $val === 'TIDAK RELEVAN' ? 'selected' : '' }}>TIDAK RELEVAN</option>
                                        </select>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @if(!$isRa)
                    <div class="mt-3 pt-3 border-top">
                        <label class="form-label small fw-bold text-muted">
                            Catatan Checklist <span class="text-danger">* Wajib diisi jika terdapat komponen yang berstatus "TIDAK RELEVAN"</span>
                        </label>
                        <textarea name="catatan" rows="3" class="form-control form-control-sm" placeholder="Berikan keterangan atau justifikasi kelengkapan / komponen tidak relevan...">{{ $checklist->catatan }}</textarea>
                    </div>

                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                            <i class="bi bi-save me-1"></i> Simpan Hasil Evaluasi Checklist
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
