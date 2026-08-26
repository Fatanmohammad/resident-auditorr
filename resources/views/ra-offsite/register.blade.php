@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-journal-check text-primary me-2"></i>Register Harian & Antrian Review RA</h4>
            <p class="text-muted small mb-0">Daftar transaksi hasil scan indikator anomali yang perlu ditindaklanjuti</p>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('ra-offsite.register.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Cabang / Unit Kerja</label>
                    <select name="cabang_id" class="form-select" onchange="this.form.submit()">
                        @foreach($cabangs as $c)
                            <option value="{{ $c->id }}" {{ $cabangId == $c->id ? 'selected' : '' }}>
                                {{ $c->kode_cabang ?? $c->id }} - {{ $c->nama_cabang }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Domain / Jenis Laporan</label>
                    <select name="domain_type" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Domain --</option>
                        <option value="cbs" {{ $domainType == 'cbs' ? 'selected' : '' }}>CBS / Teller</option>
                        <option value="biaya" {{ $domainType == 'biaya' ? 'selected' : '' }}>Jurnal Biaya</option>
                        <option value="kredit" {{ $domainType == 'kredit' ? 'selected' : '' }}>Nominatif Kredit</option>
                        <option value="dpk" {{ $domainType == 'dpk' ? 'selected' : '' }}>Nominatif DPK</option>
                        <option value="pengaduan" {{ $domainType == 'pengaduan' ? 'selected' : '' }}>Pengaduan Nasabah</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('ra-offsite.register.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Register Staging --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>Domain</th>
                            <th>Detail Transaksi (Raw Data)</th>
                            <th>Status Review</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stagings as $item)
                            <tr>
                                <td>{{ $loop->iteration + $stagings->firstItem() - 1 }}</td>
                                <td>{{ $item->tgl_transaksi ? \Carbon\Carbon::parse($item->tgl_transaksi)->format('d/m/Y') : '-' }}</td>
                                <td><span class="badge bg-info text-dark">{{ strtoupper($item->domain_type) }}</span></td>
                                <td>
                                    <small class="text-monospace">
                                        {{ Str::limit(is_array($item->raw_data) ? implode(' | ', $item->raw_data) : $item->raw_data, 90) }}
                                    </small>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($item->status_review ?? 'Pending') {
                                            'Verified' => 'bg-success',
                                            'Escalated' => 'bg-warning text-dark',
                                            'Rejected' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $item->status_review ?? 'Pending' }}</span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $item->id }}">
                                        <i class="bi bi-pencil-square"></i> Review
                                    </button>
                                </td>
                            </tr>

                            {{-- Modal Review --}}
                            <div class="modal fade" id="reviewModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('ra-offsite.register.update', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Review Data Staging #{{ $item->id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Status Review</label>
                                                    <select name="status_review" class="form-select" required>
                                                        <option value="Pending" {{ ($item->status_review ?? '') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                        <option value="Verified" {{ ($item->status_review ?? '') == 'Verified' ? 'selected' : '' }}>Verified (Sesuai/Valid)</option>
                                                        <option value="Escalated" {{ ($item->status_review ?? '') == 'Escalated' ? 'selected' : '' }}>Escalated (Tindak Lanjut Onsite)</option>
                                                        <option value="Rejected" {{ ($item->status_review ?? '') == 'Rejected' ? 'selected' : '' }}>Rejected (Bukan Temuan)</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Catatan Analisis RA</label>
                                                    <textarea name="catatan_ra" class="form-control" rows="3" placeholder="Tambahkan catatan verifikasi...">{{ $item->catatan_ra }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Review</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada data register untuk kriteria yang dipilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $stagings->links() }}
            </div>
        </div>
    </div>
</div>
@endsection