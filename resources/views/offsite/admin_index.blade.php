@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="m-0 font-weight-bold text-primary">Rekapitulasi Pengawasan Offsite Per Cabang</h5>
                <small class="text-muted">Pilih Kantor Cabang di bawah untuk melihat rincian unit dan temuan risiko.</small>
            </div>
            <span class="badge bg-success">Role: {{ strtoupper(auth()->user()->role) }}</span>
        </div>
        <div class="card-body">
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode Cabang</th>
                            <th>Nama Kantor Cabang / Wilayah</th>
                            <th class="text-center">Register Harian (Low)</th>
                            <th class="text-center">Temuan KKA (Moderate)</th>
                            <th class="text-center">Temuan KKA (High)</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapCabang as $index => $cabang)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $cabang['kode_cabang'] }}</strong></td>
                            <td>
                                <a href="#" class="text-decoration-none fw-bold text-primary">
                                    {{ $cabang['nama_cabang'] }}
                                </a>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $cabang['total_low'] }} Data</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark">{{ $cabang['total_moderate'] }} Temuan</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">{{ $cabang['total_high'] }} Temuan</span>
                            </td>
                            <td class="text-center">
                                <!-- Tombol untuk melihat detail cabang tersebut -->
                                <a href="{{ url('/offsite/kka?cabang=' . $cabang['kode_cabang']) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-search">}</i> Lihat KKA Cabang
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada data cabang yang tersedia.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection