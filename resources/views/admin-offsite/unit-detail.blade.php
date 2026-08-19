@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="mb-3">
                <a href="{{ route('admin-offsite.cabang-detail', $unit->cabang_id) }}?tahun={{ $tahun }}&bulan={{ $bulan }}" class="btn btn-sm btn-secondary">← Kembali</a>
            </div>
            <h2 class="mb-2">{{ $unit->unit_code }} — {{ $unit->unit_name }}</h2>
            <p class="text-muted">{{ $unit->unit_type }} | Periode: {{ \Carbon\Carbon::create($tahun, $bulan)->isoFormat('MMMM Y') }}</p>
        </div>
    </div>

    @if ($summary)
        <!-- Summary Card -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Ringkasan Status Review</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 text-center">
                                <div class="text-muted small">Total Area Eligible</div>
                                <div class="h4 mb-0">{{ $summary->total_area_eligible }}</div>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="text-muted small">Area Berisiko</div>
                                <div class="h4 mb-0 text-warning">{{ $summary->total_area_risiko }}</div>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="text-muted small">Perlu Klarifikasi</div>
                                <div class="h4 mb-0">{{ $summary->total_klarifikasi }}</div>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="text-muted small">Perlu Eskalasi</div>
                                <div class="h4 mb-0">{{ $summary->total_eskalasi }}</div>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="text-muted small">Risiko Tertinggi</div>
                                <div class="h5">
                                    @if($summary->risiko_tertinggi === 'High')
                                        <span class="badge bg-danger">{{ $summary->risiko_tertinggi }}</span>
                                    @elseif(in_array($summary->risiko_tertinggi, ['Moderate', 'Moderate to High']))
                                        <span class="badge bg-warning">{{ $summary->risiko_tertinggi }}</span>
                                    @else
                                        <span class="badge bg-success">{{ $summary->risiko_tertinggi ?? 'Tidak Ada' }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="text-muted small">Status Review</div>
                                <div class="h5">
                                    @if($summary->status_review === 'Perlu Review')
                                        <span class="badge bg-warning text-dark">Perlu Review</span>
                                    @elseif($summary->status_review === 'Dalam Review')
                                        <span class="badge bg-info">Dalam Review</span>
                                    @elseif($summary->status_review === 'Selesai Review')
                                        <span class="badge bg-success">Selesai Review</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Perlu</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Status Manual -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Update Status Review Manual</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin-offsite.update-status', $unit) }}" method="POST" class="row g-3">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="tahun" value="{{ $tahun }}">
                            <input type="hidden" name="bulan" value="{{ $bulan }}">
                            
                            <div class="col-md-6">
                                <label class="form-label">Status Review</label>
                                <select name="status_review" class="form-select" required>
                                    <option value="Tidak Perlu Review" @selected($summary->status_review === 'Tidak Perlu Review')>Tidak Perlu Review</option>
                                    <option value="Perlu Review" @selected($summary->status_review === 'Perlu Review')>Perlu Review</option>
                                    <option value="Dalam Review" @selected($summary->status_review === 'Dalam Review')>Dalam Review</option>
                                    <option value="Selesai Review" @selected($summary->status_review === 'Selesai Review')>Selesai Review</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Catatan</label>
                                <input type="text" name="catatan" class="form-control" value="{{ $summary->catatan }}">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            Belum ada data untuk unit ini pada periode ini. Upload file register terlebih dahulu.
        </div>
    @endif

    <!-- Upload Register Harian -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Upload Register Offsite Harian</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin-offsite.upload-register', $unit) }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label">Tahun</label>
                            <input type="number" name="tahun" class="form-control" value="{{ $tahun }}" min="2020" max="2099" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bulan</label>
                            <select name="bulan" class="form-select" required>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" @selected($i == $bulan)>
                                        {{ \Carbon\Carbon::create(2024, $i)->isoFormat('MMMM') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">File Register (CSV)</label>
                            <input type="file" name="register_file" class="form-control" accept=".csv,.txt" required>
                            <small class="text-muted">Format: CSV | Max: 10 MB</small>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">Upload & Proses</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload History -->
    @if($uploads->count() > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Riwayat Upload</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal Upload</th>
                                    <th>File</th>
                                    <th>Total Records</th>
                                    <th>Total Areas</th>
                                    <th>Status</th>
                                    <th>Upload Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($uploads as $upload)
                                    <tr>
                                        <td>{{ $upload->uploaded_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td>{{ $upload->file_name ?? '-' }}</td>
                                        <td>{{ $upload->total_records }}</td>
                                        <td>{{ $upload->total_areas }}</td>
                                        <td>
                                            @if($upload->status === 'Processed')
                                                <span class="badge bg-success">Berhasil Diproses</span>
                                            @elseif($upload->status === 'Processing')
                                                <span class="badge bg-info">Sedang Diproses</span>
                                            @elseif($upload->status === 'Failed')
                                                <span class="badge bg-danger">Gagal</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $upload->status }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $upload->uploaded_by ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
