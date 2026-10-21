@extends('layouts.app')

@section('title', 'KKA Lingkungan dan CCTV - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="text-decoration-none text-muted mb-1 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kontrol Pelaksanaan
            </a>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-camera-video text-primary me-2"></i> WP-RA-05-J | KKA LINGKUNGAN DAN CCTV
            </h3>
            <small class="text-muted">Menilai kelayakan, kebersihan, keamanan area, serta fungsi dan cakupan CCTV.</small>
        </div>
        <div>
            <span class="badge bg-dark fs-6 px-3 py-2 rounded-pill shadow-sm">
                {{ $assignment->visit_id }} | {{ $assignment->nama_unit }}
            </span>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-3" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Form Matriks Data -->
    <form action="{{ route('surprise_audit.lingkungan.store', $assignment->id) }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-table me-2 text-warning"></i> Matriks Pengujian Lingkungan & CCTV (KKA_Lingkungan)</span>
                <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">
                    <i class="bi bi-save me-1"></i> Simpan KKA Lingkungan
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 fs-7">
                        <thead class="table-dark text-uppercase small text-center align-middle">
                            <tr>
                                <th style="width: 100px;">Kategori</th>
                                <th style="width: 150px;">Area / Perangkat</th>
                                <th style="width: 220px;">Kriteria Unik</th>
                                <th style="min-width: 180px;">Hasil Observasi RA</th>
                                <th style="width: 140px;">Hasil Uji / Playback</th>
                                <th style="width: 120px;">Kesimpulan Uji</th>
                                <th style="min-width: 140px;">Sebab</th>
                                <th style="min-width: 140px;">Dampak</th>
                                <th style="width: 120px;">Ref. Bukti</th>
                                <th style="width: 120px;">Validasi</th>
                                <th style="width: 90px;">Exception</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lingkunganData as $item)
                            <tr>
                                <td class="fw-bold text-center bg-light">
                                    <span class="badge {{ $item->kategori == 'Kelayakan' ? 'bg-primary' : ($item->kategori == 'Kebersihan' ? 'bg-info text-dark' : 'bg-dark') }}">
                                        {{ $item->kategori }}
                                    </span>
                                </td>
                                <td class="fw-bold text-dark">{{ $item->area_perangkat }}</td>
                                <td class="small text-muted">{{ $item->kriteria_unik }}</td>
                                <td>
                                    <textarea name="items[{{ $item->id }}][hasil_observasi_ra]" class="form-control form-control-sm" rows="2" placeholder="Catatan hasil observasi fisik RA...">{{ $item->hasil_observasi_ra }}</textarea>
                                </td>
                                <td>
                                    @if($item->kategori == 'CCTV')
                                        <input type="text" name="items[{{ $item->id }}][hasil_uji_playback]" class="form-control form-control-sm" value="{{ $item->hasil_uji_playback }}">
                                    @else
                                        <input type="text" name="items[{{ $item->id }}][hasil_uji_playback]" class="form-control form-control-sm text-center bg-light" value="N.A." readonly>
                                    @endif
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][kesimpulan_uji]" class="form-select form-select-sm fw-bold text-center {{ $item->kesimpulan_uji == 'Tidak Sesuai' ? 'text-danger' : 'text-success' }}">
                                        <option value="Sesuai" {{ $item->kesimpulan_uji == 'Sesuai' ? 'selected' : '' }}>Sesuai</option>
                                        <option value="Tidak Sesuai" {{ $item->kesimpulan_uji == 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][sebab]" class="form-control form-control-sm" value="{{ $item->sebab }}" placeholder="Jika Tidak Sesuai">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][dampak]" class="form-control form-control-sm" value="{{ $item->dampak }}" placeholder="Jika Tidak Sesuai">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][referensi_bukti]" class="form-control form-control-sm" value="{{ $item->referensi_bukti }}">
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge {{ $item->validasi == 'VALID' ? 'bg-success' : ($item->validasi == 'ANALISIS BELUM LENGKAP' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                        {{ $item->validasi }}
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge {{ $item->exception_aktif == 'Ya' ? 'bg-danger' : 'bg-secondary' }}">
                                        {{ $item->exception_aktif }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light p-3 text-end">
                <button type="submit" class="btn btn-warning fw-bold px-4">
                    <i class="bi bi-save me-1"></i> Simpan KKA Lingkungan dan CCTV
                </button>
            </div>
        </div>
    </form>
</div>
@endsection