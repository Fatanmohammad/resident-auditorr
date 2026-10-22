{{-- Area: KKA_Observasi (18 Kolom: A s.d. R) --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-camera-video text-secondary me-2"></i>KKA Observasi Lingkungan & Keamanan (18 Kolom Excel: A s.d. R)
            </h6>
            <small class="text-muted">
                Pengujian titik kontrol fisik, CCTV & playback, APAR, ruang arsip/server, dual custody brankas, dan standar 5R keamanan lingkungan.
            </small>
        </div>
        @if($canModify && !$isFinal)
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddObservasi">
            <i class="bi bi-plus-circle me-1"></i> + Tambah Sampel Observasi
        </button>
        @endif
    </div>

    <div class="table-responsive" style="max-height: 520px;">
        <table class="table table-hover align-middle mb-0 data-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 70px;">Aksi</th>
                    <th>Status Uji (Q)</th>
                    <th>Exception (R)</th>
                    <th>Kategori (E)</th>
                    <th>Titik Kontrol / Lokasi (F)</th>
                    <th>Kriteria / Standar (G)</th>
                    <th>Kondisi Hasil Observasi (H)</th>
                    <th>Uji Fungsi / Playback (I)</th>
                    <th>Akses / Bersih / Aman (J)</th>
                    <th>Rating 1-5 (K)</th>
                    <th>Analisis Gap (L)</th>
                    <th>Sebab (M)</th>
                    <th>Dampak (N)</th>
                    <th>Ref Bukti / Foto (O)</th>
                    <th>Simpulan (P)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($observasiRows as $row)
                <tr>
                    <td class="text-center text-nowrap">
                        @if($canModify && !$isFinal)
                        <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditObservasi{{ $row->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('risk-trigger.kka.area-row.destroy', ['area' => 'KKA_Observasi', 'rowId' => $row->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus baris sampel ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        @else
                        <span class="text-muted"><i class="bi bi-lock-fill"></i></span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $row->status_uji == 'SESUAI' ? 'bg-success' : ($row->status_uji == 'EXCEPTION' ? 'bg-danger' : 'bg-warning text-dark') }}">
                            {{ $row->status_uji }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $row->exception == 'Ya' ? 'bg-danger' : 'bg-success' }}">
                            {{ $row->exception }}
                        </span>
                    </td>
                    <td class="fw-semibold">{{ $row->kategori }}</td>
                    <td>{{ $row->titik_kontrol_lokasi }}</td>
                    <td class="text-wrap" style="max-width: 180px;">{{ $row->kriteria_standar }}</td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->kondisi_hasil_observasi }}</td>
                    <td>
                        <span class="badge {{ $row->uji_fungsi_playback == 'Berfungsi' ? 'bg-success' : ($row->uji_fungsi_playback == 'Tidak Berfungsi' ? 'bg-danger' : 'bg-secondary') }}">
                            {{ $row->uji_fungsi_playback }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $row->akses_kebersihan_aman == 'Sesuai' ? 'bg-success' : 'bg-danger' }}">
                            {{ $row->akses_kebersihan_aman }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ ($row->rating_1_5 ?? 3) <= 2 ? 'bg-danger' : 'bg-primary' }}">
                            {{ $row->rating_1_5 }}/5
                        </span>
                    </td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->analisis_gap }}</td>
                    <td class="text-wrap" style="max-width: 160px;">{{ $row->sebab }}</td>
                    <td class="text-wrap" style="max-width: 160px;">{{ $row->dampak }}</td>
                    <td>{{ $row->referensi_bukti_foto }}</td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->simpulan }}</td>
                </tr>

                {{-- Modal Edit Observasi --}}
                @if($canModify && !$isFinal)
                <div class="modal fade" id="modalEditObservasi{{ $row->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <form action="{{ route('risk-trigger.kka.area-row.update', ['area' => 'KKA_Observasi', 'rowId' => $row->id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold">Edit Baris Observasi #{{ $row->id }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Kategori (E)</label>
                                        <input type="text" name="kategori" class="form-control form-control-sm" value="{{ $row->kategori }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Titik Kontrol / Lokasi (F)</label>
                                        <input type="text" name="titik_kontrol_lokasi" class="form-control form-control-sm" value="{{ $row->titik_kontrol_lokasi }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Kriteria / Standar (G)</label>
                                        <textarea name="kriteria_standar" class="form-control form-control-sm" rows="2" required>{{ $row->kriteria_standar }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Kondisi Hasil Observasi (H)</label>
                                        <textarea name="kondisi_hasil_observasi" class="form-control form-control-sm" rows="2" required>{{ $row->kondisi_hasil_observasi }}</textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Uji Fungsi / Playback (I)</label>
                                        <select name="uji_fungsi_playback" class="form-select form-select-sm">
                                            <option value="Berfungsi" {{ $row->uji_fungsi_playback == 'Berfungsi' ? 'selected' : '' }}>Berfungsi</option>
                                            <option value="Tidak Berfungsi" {{ $row->uji_fungsi_playback == 'Tidak Berfungsi' ? 'selected' : '' }}>Tidak Berfungsi</option>
                                            <option value="N/A" {{ $row->uji_fungsi_playback == 'N/A' ? 'selected' : '' }}>N/A</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Akses / Kebersihan / Aman (J)</label>
                                        <select name="akses_kebersihan_aman" class="form-select form-select-sm">
                                            <option value="Sesuai" {{ $row->akses_kebersihan_aman == 'Sesuai' ? 'selected' : '' }}>Sesuai</option>
                                            <option value="Tidak Sesuai" {{ $row->akses_kebersihan_aman == 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Rating Kontrol (1 s.d. 5) (K)</label>
                                        <select name="rating_1_5" class="form-select form-select-sm">
                                            @for($i=1; $i<=5; $i++)
                                                <option value="{{ $i }}" {{ $row->rating_1_5 == $i ? 'selected' : '' }}>{{ $i }} ({{ $i <= 2 ? 'Buruk' : ($i == 3 ? 'Cukup' : 'Baik') }})</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Analisis Gap dan Efektivitas (L)</label>
                                        <textarea name="analisis_gap" class="form-control form-control-sm" rows="2" required>{{ $row->analisis_gap }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Sebab (M)</label>
                                        <textarea name="sebab" class="form-control form-control-sm" rows="2">{{ $row->sebab }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Dampak (N)</label>
                                        <textarea name="dampak" class="form-control form-control-sm" rows="2">{{ $row->dampak }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Referensi Bukti / Foto (O)</label>
                                        <input type="text" name="referensi_bukti_foto" class="form-control form-control-sm" value="{{ $row->referensi_bukti_foto }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Simpulan (P)</label>
                                        <input type="text" name="simpulan" class="form-control form-control-sm" value="{{ $row->simpulan }}" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                @empty
                <tr>
                    <td colspan="15" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-1 opacity-50"></i>
                        Belum ada sampel observasi fisik/lingkungan pada KKA Observasi untuk Visit ID ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Add Observasi --}}
@if($canModify && !$isFinal)
<div class="modal fade" id="modalAddObservasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('risk-trigger.kka.area-row.store', $kka->id) }}" method="POST">
                @csrf
                <input type="hidden" name="kka_area" value="KKA_Observasi">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Tambah Sampel Pengujian Observasi (KKA_Observasi)</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Kategori (E) <span class="text-danger">*</span></label>
                        <input type="text" name="kategori" class="form-control form-control-sm" placeholder="CCTV / Brankas / APAR / Server / Kebersihan" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Titik Kontrol / Lokasi (F) <span class="text-danger">*</span></label>
                        <input type="text" name="titik_kontrol_lokasi" class="form-control form-control-sm" placeholder="Area Teller / Khazanah / Ruang Server" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Kriteria / Standar (G) <span class="text-danger">*</span></label>
                        <textarea name="kriteria_standar" class="form-control form-control-sm" rows="2" placeholder="Standar operasional & parameter keamanan fisik..." required></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Kondisi Hasil Observasi (H) <span class="text-danger">*</span></label>
                        <textarea name="kondisi_hasil_observasi" class="form-control form-control-sm" rows="2" placeholder="Fakta fisik yang diamati di lapangan..." required></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Uji Fungsi / Playback (I)</label>
                        <select name="uji_fungsi_playback" class="form-select form-select-sm">
                            <option value="Berfungsi" selected>Berfungsi</option>
                            <option value="Tidak Berfungsi">Tidak Berfungsi</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Akses / Kebersihan / Aman (J)</label>
                        <select name="akses_kebersihan_aman" class="form-select form-select-sm">
                            <option value="Sesuai" selected>Sesuai</option>
                            <option value="Tidak Sesuai">Tidak Sesuai</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Rating Kontrol (1 s.d. 5) (K)</label>
                        <select name="rating_1_5" class="form-select form-select-sm">
                            <option value="5">5 - Sangat Baik</option>
                            <option value="4" selected>4 - Baik</option>
                            <option value="3">3 - Cukup</option>
                            <option value="2">2 - Kurang (Exception)</option>
                            <option value="1">1 - Sangat Buruk (Exception)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Analisis Gap dan Efektivitas (L) <span class="text-danger">*</span></label>
                        <textarea name="analisis_gap" class="form-control form-control-sm" rows="2" placeholder="Evaluasi efektivitas pengendalian fisik..." required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Sebab (M)</label>
                        <textarea name="sebab" class="form-control form-control-sm" rows="2" placeholder="Penyebab jika kontrol tidak berfungsi..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Dampak (N)</label>
                        <textarea name="dampak" class="form-control form-control-sm" rows="2" placeholder="Dampak kerentanan keamanan cabang..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Referensi Bukti / Foto (O) <span class="text-danger">*</span></label>
                        <input type="text" name="referensi_bukti_foto" class="form-control form-control-sm" placeholder="Lampiran foto titik fisik & logbook" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Simpulan (P) <span class="text-danger">*</span></label>
                        <input type="text" name="simpulan" class="form-control form-control-sm" placeholder="Kontrol berfungsi baik / Perlu perbaikan segera" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">+ Simpan Baris Sampel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
