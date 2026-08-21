@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <a href="{{ route('admin-offsite.kka-index', [$wp->id, $area]) }}" class="btn btn-sm btn-secondary mb-3">← Kembali</a>

    <h2 class="mb-1">Detail KKA {{ $areaLabel }}</h2>
    <p class="text-muted">{{ $wp->kode_wp }} — {{ $wp->nama_unit }}</p>

    <div class="row">
        {{-- Kolom kiri: konteks transaksi (read-only, dari Staging) --}}
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header"><strong>Konteks Transaksi</strong></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted" width="45%">Tanggal Data</td><td>{{ $kka->tanggal_data->format('d/m/Y') }}</td></tr>
                        <tr><td class="text-muted">Object ID</td><td>{{ $kka->object_id ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Case ID</td><td>{{ $kka->case_id ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Data Code</td><td>{{ $kka->data_code ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Deskripsi/Narasi</td><td>{{ $kka->deskripsi_narasi }}</td></tr>
                        <tr><td class="text-muted">Nominal</td><td>Rp {{ number_format($kka->nominal, 0, ',', '.') }}</td></tr>
                        <tr><td class="text-muted">User/Maker</td><td>{{ $kka->user_maker ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Risk Awal</td><td>{{ $kka->risk_awal }}</td></tr>
                        <tr><td class="text-muted">Exception Awal</td><td>{{ $kka->exception_awal }}</td></tr>
                        <tr><td class="text-muted">Jenis Exception Awal</td><td>{{ $kka->jenis_exception_awal ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Sampel Low</td><td>{{ $kka->sampel_low ? 'Ya' : 'Tidak' }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><strong>Prosedur Uji</strong></div>
                <div class="card-body">
                    <p><strong>Tujuan Uji:</strong> {{ $kka->tujuan_uji ?? '-' }}</p>
                    <p><strong>Kriteria:</strong> {{ $kka->kriteria ?? '-' }}</p>
                    <p class="mb-0"><strong>Prosedur Uji:</strong> {{ $kka->prosedur_uji ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Kolom kanan: hasil kerja RA (read-only untuk Admin) + form Catatan Reviewer --}}
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header"><strong>Hasil Kerja RA</strong> <span class="badge bg-secondary float-end">Read-only</span></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted" width="45%">Bukti/Referensi</td><td>{{ $kka->bukti_referensi ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Hasil Uji</td><td>{{ $kka->hasil_uji ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Jenis Exception (RA)</td><td>{{ $kka->jenis_exception_ra ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Dampak</td><td>{{ $kka->dampak ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Kemungkinan</td><td>{{ $kka->kemungkinan ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Critical Trigger</td><td>{{ $kka->critical_trigger ? 'Ya' : 'Tidak' }}</td></tr>
                        <tr><td class="text-muted">Skor Risiko</td><td><strong>{{ $kka->skor_risiko ?? '-' }}</strong></td></tr>
                        <tr><td class="text-muted">Kategori Risiko Final</td>
                            <td><span class="badge bg-{{ $kka->kategori_risiko_final === 'High' ? 'danger' : ($kka->kategori_risiko_final === 'Moderate' ? 'warning' : 'success') }}">{{ $kka->kategori_risiko_final ?? '-' }}</span></td>
                        </tr>
                        <tr><td class="text-muted">Klarifikasi Awal</td><td>{{ $kka->klarifikasi_awal ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Klarifikasi Unit</td><td>{{ $kka->klarifikasi_unit ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Status Klarifikasi</td><td>{{ $kka->status_klarifikasi ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Perlu Onsite</td><td>{{ $kka->perlu_onsite ? 'Ya' : 'Tidak' }}</td></tr>
                        <tr><td class="text-muted">Keputusan Onsite</td><td>{{ $kka->keputusan_onsite ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Keputusan Eskalasi</td><td>{{ $kka->keputusan_eskalasi ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Simpulan RA</td><td>{{ $kka->simpulan_ra ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Status Review</td><td>{{ $kka->status_review ?? 'Belum Review' }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- HANYA form ini yang editable untuk Admin/Reviewer --}}
            <div class="card border-primary">
                <div class="card-header bg-primary text-white"><strong>Catatan Reviewer</strong></div>
                <div class="card-body">
                    <form action="{{ route('admin-offsite.kka-reviewer-note', [$wp->id, $area, $kka->kka_id]) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <textarea name="catatan_reviewer" class="form-control mb-2" rows="4">{{ $kka->catatan_reviewer }}</textarea>
                        <button type="submit" class="btn btn-primary btn-sm">Simpan Catatan</button>
                    </form>
                    @if($kka->reviewer_id)
                        <small class="text-muted d-block mt-2">Terakhir diisi oleh reviewer, {{ $kka->updated_at->format('d/m/Y H:i') }}</small>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection