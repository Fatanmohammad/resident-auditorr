@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-1">Detail KKA — {{ $areaLabel }}</h5>
            <small class="text-muted">Kode WP: {{ $wp->kode_wp }} | Unit: {{ $wp->nama_unit ?? $wp->unit->nama_unit }}</small>
        </div>
        <a href="{{ route('admin-offsite.kka-index', [$wp->id, $area]) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar KKA
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show btn-sm mb-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- KOLOM KIRI: Konteks Transaksi (Putih - Read Only dari Staging) --}}
        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-file-earmark-text"></i> Konteks Transaksi
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless text-sm mb-0">
                        <tr>
                            <th width="45%">Tanggal</th>
                            <td>: {{ \Carbon\Carbon::parse($kka->tanggal_data ?? $kka->created_at)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Object ID</th>
                            <td>: {{ $kka->object_id ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Case ID</th>
                            <td>: {{ $kka->case_id ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Data Code</th>
                            <td>: {{ $kka->data_code ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>User / Maker</th>
                            <td>: {{ $kka->user_maker ?? $kka->user_id ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Nominal</th>
                            <td class="fw-bold">: Rp {{ number_format($kka->nominal ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Risk Awal</th>
                            <td>: <span class="badge bg-secondary">{{ $kka->risk_awal ?? $kka->risk_level ?? 'Low' }}</span></td>
                        </tr>
                        <tr>
                            <th>Exception Awal</th>
                            <td>: {{ $kka->exception_awal ? 'Ya' : 'Tidak' }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Exception</th>
                            <td>: {{ $kka->jenis_exception_awal ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Sampel Low</th>
                            <td>: {{ $kka->sampel_low ? 'Ya' : 'Tidak' }}</td>
                        </tr>
                    </table>

                    <hr>

                    <h6 class="fw-bold text-secondary mb-2"><small>Deskripsi / Narasi</small></h6>
                    <p class="text-muted bg-light p-2 rounded small mb-3">{{ $kka->deskripsi ?? $kka->uraian ?? '-' }}</p>

                    <h6 class="fw-bold text-secondary mb-2"><small>Prosedur Uji Panduan Standar</small></h6>
                    <div class="bg-light p-2 rounded small text-muted">
                        <strong>Tujuan Uji:</strong> {{ $kka->tujuan_uji ?? 'Memastikan keabsahan & otorisasi transaksi.' }}<br>
                        <strong>Kriteria:</strong> {{ $kka->kriteria ?? 'Sesuai SOP Operasional yang berlaku.' }}<br>
                        <strong>Prosedur:</strong> {{ $kka->prosedur_uji ?? 'Telusuri bukti; cocokkan tanggal, nominal, user, dan otorisasi.' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM TENGAH: Hasil Kerja RA (Kuning - Read Only untuk Admin) --}}
        <div class="col-md-4">
            <div class="card shadow-sm mb-3 border-warning">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="bi bi-person-workspace"></i> Hasil Kerja RA (Read-Only)
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label text-muted mb-0"><small>Bukti / Referensi</small></label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $kka->bukti_referensi ?? '-' }}" readonly>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-muted mb-0"><small>Hasil Uji</small></label>
                        <textarea class="form-control form-control-sm bg-light" rows="3" readonly>{{ $kka->hasil_uji ?? '-' }}</textarea>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-muted mb-0"><small>Jenis Exception (RA)</small></label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $kka->jenis_exception_ra ?? '-' }}" readonly>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label text-muted mb-0"><small>Dampak</small></label>
                            <input type="text" class="form-control form-control-sm bg-light" value="{{ $kka->dampak ?? '-' }}" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted mb-0"><small>Kemungkinan</small></label>
                            <input type="text" class="form-control form-control-sm bg-light" value="{{ $kka->kemungkinan ?? '-' }}" readonly>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label text-muted mb-0"><small>Skor Risiko</small></label>
                            <input type="text" class="form-control form-control-sm bg-light fw-bold" value="{{ $kka->skor_risiko ?? '-' }}" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted mb-0"><small>Kategori Final</small></label>
                            <input type="text" class="form-control form-control-sm bg-light fw-bold" value="{{ $kka->kategori_risiko_final ?? '-' }}" readonly>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-muted mb-0"><small>Critical Trigger</small></label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $kka->critical_trigger ?? '-' }}" readonly>
                    </div>

                    <hr class="my-2">

                    <div class="mb-2">
                        <label class="form-label text-muted mb-0"><small>Klarifikasi Awal / Unit</small></label>
                        <textarea class="form-control form-control-sm bg-light" rows="2" readonly>{{ $kka->klarifikasi_unit ?? $kka->klarifikasi_awal ?? '-' }}</textarea>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label text-muted mb-0"><small>Status Klarifikasi</small></label>
                            <input type="text" class="form-control form-control-sm bg-light" value="{{ $kka->status_klarifikasi ?? '-' }}" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted mb-0"><small>Perlu Onsite</small></label>
                            <input type="text" class="form-control form-control-sm bg-light" value="{{ $kka->perlu_onsite ? 'Ya' : 'Tidak' }}" readonly>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-muted mb-0"><small>Simpulan RA</small></label>
                        <textarea class="form-control form-control-sm bg-light fw-bold" rows="2" readonly>{{ $kka->simpulan_ra ?? '-' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: Form Reviewer (Editable untuk Admin) --}}
        <div class="col-md-4">
            <form action="{{ route('admin-offsite.kka-update', ['wp' => $wp->id, 'area' => $area, 'kkaId' => $kka->kka_id ?? $kka->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card shadow-sm border-primary">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-pencil-square"></i> Catatan Reviewer (Admin)
                    </div>
                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label fw-bold"><small>Status Review</small></label>
                            <select name="status_review" class="form-select form-select-sm">
                                <option value="Belum Review" {{ old('status_review', $kka->status_review) == 'Belum Review' ? 'selected' : '' }}>Belum Review</option>
                                <option value="Dalam Proses" {{ old('status_review', $kka->status_review) == 'Dalam Proses' ? 'selected' : '' }}>Dalam Proses</option>
                                <option value="Selesai" {{ old('status_review', $kka->status_review) == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="Perlu Perbaikan" {{ old('status_review', $kka->status_review) == 'Perlu Perbaikan' ? 'selected' : '' }}>Perlu Perbaikan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold"><small>Catatan Reviewer</small></label>
                            <textarea name="catatan_reviewer" class="form-control form-control-sm" rows="6" placeholder="Masukkan komentar/catatan tinjauan Anda (misal: 'Perlu dilengkapi bukti tambahan' atau 'Sudah sesuai, disetujui')...">{{ old('catatan_reviewer', $kka->catatan_reviewer) }}</textarea>
                        </div>

                        @if($kka->updated_at)
                            <div class="text-muted mb-3" style="font-size: 0.75rem;">
                                <i class="bi bi-clock-history"></i> Terakhir diupdate: {{ \Carbon\Carbon::parse($kka->updated_at)->format('d/m/Y H:i') }}
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-save"></i> Simpan Catatan Reviewer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection