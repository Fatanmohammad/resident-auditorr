@extends('layouts.app')

@section('title', 'KKA Lingkungan dan CCTV - ' . $assignment->visit_id)

@section('content')
<div class="container-fluid px-4 py-3" style="background-color: #f8fafc; min-height: 100vh;">
    <!-- Header Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <a href="{{ route('surprise_audit.show', $assignment->id) }}" class="text-decoration-none text-muted mb-2 d-inline-block fw-semibold" style="font-size: 14px;">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kontrol Pelaksanaan
            </a>
            <h3 class="fw-bold mb-1" style="color: #0f2e5a; letter-spacing: -0.3px;">
                <i class="bi bi-camera-video text-primary me-2"></i> WP-RA-05-J | KKA Lingkungan dan CCTV
            </h3>
            <p class="text-muted mb-0" style="font-size: 14px;">
                Menilai kelayakan, kebersihan, keamanan area kerja, serta fungsi dan cakupan rekaman CCTV.
            </p>
        </div>
        <div>
            <span class="badge px-3 py-2 rounded-pill shadow-sm" style="background-color: #0f2e5a; color: #fff; font-size: 13px; font-weight: 600;">
                <i class="bi bi-building me-1"></i> {{ $assignment->visit_id }} | {{ $assignment->nama_unit }}
            </span>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4 border-0" role="alert" style="background-color: #f0fdf4; color: #166534; border-left: 4px solid #22c55e !important;">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i> <span style="font-size: 14px; font-weight: 600;">{{ session('success') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Form Matriks Data -->
    <form action="{{ route('surprise_audit.lingkungan.store', $assignment->id) }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-3 mb-5" style="background-color: #ffffff; border: 1px solid #e2e8f0 !important; overflow: hidden;">
            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); color: #fff;">
                <span class="fw-bold" style="font-size: 14.5px;">
                    <i class="bi bi-table me-2 text-info"></i> Matriks Pengujian Lingkungan & CCTV (KKA_Lingkungan)
                </span>
                <button type="submit" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold shadow-sm text-white" style="background-color: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); font-size: 13px;">
                    <i class="bi bi-save me-1"></i> Simpan KKA Lingkungan
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" style="font-size: 13px;">
                        <thead style="background-color: #f1f5f9; color: #1e293b; font-weight: 700; font-size: 12px;" class="text-uppercase text-center">
                            <tr>
                                <th style="width: 110px;" class="py-3">Kategori</th>
                                <th style="width: 160px;" class="py-3">Area / Perangkat</th>
                                <th style="width: 220px;" class="py-3">Kriteria Unik</th>
                                <th style="min-width: 180px;" class="py-3">Hasil Observasi RA</th>
                                <th style="width: 140px;" class="py-3">Hasil Uji / Playback</th>
                                <th style="width: 130px;" class="py-3">Kesimpulan Uji</th>
                                <th style="min-width: 140px;" class="py-3">Sebab</th>
                                <th style="min-width: 140px;" class="py-3">Dampak</th>
                                <th style="width: 120px;" class="py-3">Ref. Bukti</th>
                                <th style="width: 120px;" class="py-3">Validasi</th>
                                <th style="width: 90px;" class="py-3 pe-3">Exception</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lingkunganData as $item)
                            <tr>
                                <td class="text-center">
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold" style="background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 11px;">
                                        {{ $item->kategori }}
                                    </span>
                                </td>
                                <td class="fw-bold" style="color: #0f2e5a; font-size: 13px;">
                                    {{ $item->area_perangkat }}
                                </td>
                                <td class="small text-muted" style="font-size: 12px; line-height: 1.4;">
                                    {{ $item->kriteria_unik }}
                                </td>
                                <td>
                                    <textarea name="items[{{ $item->id }}][hasil_observasi_ra]" class="form-control" rows="2" style="font-size: 12.5px; border-color: #cbd5e1;" placeholder="Catatan hasil observasi fisik RA...">{{ $item->hasil_observasi_ra }}</textarea>
                                </td>
                                <td>
                                    @if($item->kategori == 'CCTV')
                                        <input type="text" name="items[{{ $item->id }}][hasil_uji_playback]" class="form-control" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="{{ $item->hasil_uji_playback }}">
                                    @else
                                        <input type="text" name="items[{{ $item->id }}][hasil_uji_playback]" class="form-control text-center bg-light text-muted" style="font-size: 12.5px; height: 38px; border-color: #cbd5e1;" value="N.A." readonly>
                                    @endif
                                </td>
                                <td>
                                    <select name="items[{{ $item->id }}][kesimpulan_uji]" class="form-select text-center fw-semibold" style="font-size: 12px; height: 38px; border-color: #cbd5e1; color: {{ $item->kesimpulan_uji == 'Tidak Sesuai' ? '#dc2626' : '#16a34a' }};">
                                        <option value="Sesuai" {{ $item->kesimpulan_uji == 'Sesuai' ? 'selected' : '' }}>Sesuai</option>
                                        <option value="Tidak Sesuai" {{ $item->kesimpulan_uji == 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][sebab]" class="form-control" style="font-size: 12px; height: 38px; border-color: #cbd5e1;" value="{{ $item->sebab }}" placeholder="Jika Tidak Sesuai">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][dampak]" class="form-control" style="font-size: 12px; height: 38px; border-color: #cbd5e1;" value="{{ $item->dampak }}" placeholder="Jika Tidak Sesuai">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $item->id }}][referensi_bukti]" class="form-control" style="font-size: 12px; height: 38px; border-color: #cbd5e1;" value="{{ $item->referensi_bukti }}">
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold" style="{{ $item->validasi == 'VALID' ? 'background-color: #f0fdf4; color: #166534;' : ($item->validasi == 'ANALISIS BELUM LENGKAP' ? 'background-color: #fffbeb; color: #b45309;' : 'background-color: #f1f5f9; color: #475569;') }} font-size: 11px;">
                                        {{ $item->validasi }}
                                    </span>
                                </td>
                                <td class="text-center align-middle pe-3">
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold" style="{{ $item->exception_aktif == 'Ya' ? 'background-color: #fee2e2; color: #991b1b;' : 'background-color: #f1f5f9; color: #475569;' }} font-size: 11px;">
                                        {{ $item->exception_aktif }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer p-3 text-end" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                <button type="submit" class="btn rounded-pill px-4 py-2 fw-semibold shadow-sm text-white" style="background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 100%); font-size: 14px;">
                    <i class="bi bi-save me-1"></i> Simpan KKA Lingkungan dan CCTV
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
