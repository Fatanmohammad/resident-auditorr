{{-- Area: KKA_Pengaduan (21 Kolom: A s.d. U) --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-chat-left-dots text-danger me-2"></i>KKA Pengaduan Nasabah (21 Kolom Excel: A s.d. U)
            </h6>
            <small class="text-muted">
                Pengujian kepatuhan SLA penanganan pengaduan nasabah, eskalasi status, kelengkapan bukti penyelesaian, dan recurring issues.
            </small>
        </div>
        @if($canModify && !$isFinal)
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddPengaduan">
            <i class="bi bi-plus-circle me-1"></i> + Tambah Sampel Pengaduan
        </button>
        @endif
    </div>

    <div class="table-responsive" style="max-height: 520px;">
        <table class="table table-hover align-middle mb-0 data-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 70px;">Aksi</th>
                    <th>Status Uji (T)</th>
                    <th>Exception (U)</th>
                    <th>Complaint ID (E)</th>
                    <th>Tanggal Terima (F)</th>
                    <th>Kategori (G)</th>
                    <th>Uraian Pengaduan (H)</th>
                    <th>SLA Hari (I)</th>
                    <th>Tanggal Selesai (J)</th>
                    <th class="table-warning text-dark fw-bold">Aging Hari (K)</th>
                    <th>Status Selesai (L)</th>
                    <th>Bukti Selesai (M)</th>
                    <th>Berulang? (N)</th>
                    <th>Akar Masalah (O)</th>
                    <th>Dampak Nasabah (P)</th>
                    <th>Analisis SLA (Q)</th>
                    <th>Ref Bukti (R)</th>
                    <th>Simpulan (S)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengaduanRows as $row)
                <tr>
                    <td class="text-center text-nowrap">
                        @if($canModify && !$isFinal)
                        <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditPengaduan{{ $row->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('risk-trigger.kka.area-row.destroy', ['area' => 'KKA_Pengaduan', 'rowId' => $row->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus baris sampel ini?')">
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
                    <td class="font-monospace fw-semibold">{{ $row->complaint_id }}</td>
                    <td class="text-nowrap">{{ $row->tanggal_terima ? \Carbon\Carbon::parse($row->tanggal_terima)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $row->kategori }}</td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->uraian_pengaduan }}</td>
                    <td class="text-center font-monospace">{{ $row->sla_hari }} hari</td>
                    <td class="text-nowrap">{{ $row->tanggal_selesai ? \Carbon\Carbon::parse($row->tanggal_selesai)->format('d/m/Y') : '-' }}</td>
                    <td class="text-center font-monospace fw-bold {{ ($row->aging_hari ?? 0) > ($row->sla_hari ?? 0) ? 'text-danger' : 'text-success' }}">
                        {{ $row->aging_hari }} hari
                    </td>
                    <td>
                        <span class="badge {{ $row->status_penyelesaian == 'Selesai' ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $row->status_penyelesaian }}
                        </span>
                    </td>
                    <td><span class="badge {{ $row->bukti_penyelesaian == 'Ya' ? 'bg-success' : 'bg-danger' }}">{{ $row->bukti_penyelesaian }}</span></td>
                    <td><span class="badge {{ $row->berulang == 'Ya' ? 'bg-danger' : 'bg-light text-dark border' }}">{{ $row->berulang }}</span></td>
                    <td class="text-wrap" style="max-width: 160px;">{{ $row->akar_masalah }}</td>
                    <td class="text-wrap" style="max-width: 160px;">{{ $row->dampak_nasabah }}</td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->analisis_sla }}</td>
                    <td>{{ $row->referensi_bukti }}</td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->simpulan }}</td>
                </tr>

                {{-- Modal Edit Pengaduan --}}
                @if($canModify && !$isFinal)
                <div class="modal fade" id="modalEditPengaduan{{ $row->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <form action="{{ route('risk-trigger.kka.area-row.update', ['area' => 'KKA_Pengaduan', 'rowId' => $row->id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold">Edit Baris Pengaduan #{{ $row->id }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Complaint ID (E)</label>
                                        <input type="text" name="complaint_id" class="form-control form-control-sm" value="{{ $row->complaint_id }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Tanggal Terima (F)</label>
                                        <input type="date" name="tanggal_terima" class="form-control form-control-sm" value="{{ $row->tanggal_terima ? \Carbon\Carbon::parse($row->tanggal_terima)->format('Y-m-d') : '' }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Kategori (G)</label>
                                        <input type="text" name="kategori" class="form-control form-control-sm" value="{{ $row->kategori }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">SLA Hari (I)</label>
                                        <input type="number" name="sla_hari" class="form-control form-control-sm" value="{{ $row->sla_hari }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Tanggal Selesai (J)</label>
                                        <input type="date" name="tanggal_selesai" class="form-control form-control-sm" value="{{ $row->tanggal_selesai ? \Carbon\Carbon::parse($row->tanggal_selesai)->format('Y-m-d') : '' }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Uraian Pengaduan (H)</label>
                                        <textarea name="uraian_pengaduan" class="form-control form-control-sm" rows="2" required>{{ $row->uraian_pengaduan }}</textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Status Penyelesaian (L)</label>
                                        <select name="status_penyelesaian" class="form-select form-select-sm">
                                            <option value="Selesai" {{ $row->status_penyelesaian == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                            <option value="Dalam Proses" {{ $row->status_penyelesaian == 'Dalam Proses' ? 'selected' : '' }}>Dalam Proses</option>
                                            <option value="Belum Ditangani" {{ $row->status_penyelesaian == 'Belum Ditangani' ? 'selected' : '' }}>Belum Ditangani</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Bukti Penyelesaian? (M)</label>
                                        <select name="bukti_penyelesaian" class="form-select form-select-sm">
                                            <option value="Ya" {{ $row->bukti_penyelesaian == 'Ya' ? 'selected' : '' }}>Ya</option>
                                            <option value="Tidak" {{ $row->bukti_penyelesaian == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Berulang? (N)</label>
                                        <select name="berulang" class="form-select form-select-sm">
                                            <option value="Tidak" {{ $row->berulang == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                            <option value="Ya" {{ $row->berulang == 'Ya' ? 'selected' : '' }}>Ya</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Akar Masalah (O)</label>
                                        <textarea name="akar_masalah" class="form-control form-control-sm" rows="2">{{ $row->akar_masalah }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Dampak Nasabah (P)</label>
                                        <textarea name="dampak_nasabah" class="form-control form-control-sm" rows="2">{{ $row->dampak_nasabah }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Analisis SLA dan Penanganan (Q)</label>
                                        <textarea name="analisis_sla" class="form-control form-control-sm" rows="2" required>{{ $row->analisis_sla }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Referensi Bukti (R)</label>
                                        <input type="text" name="referensi_bukti" class="form-control form-control-sm" value="{{ $row->referensi_bukti }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Simpulan (S)</label>
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
                    <td colspan="18" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-1 opacity-50"></i>
                        Belum ada sampel pengaduan nasabah pada KKA Pengaduan untuk Visit ID ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Add Pengaduan --}}
@if($canModify && !$isFinal)
<div class="modal fade" id="modalAddPengaduan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('risk-trigger.kka.area-row.store', $kka->id) }}" method="POST">
                @csrf
                <input type="hidden" name="kka_area" value="KKA_Pengaduan">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Tambah Sampel Pengujian Pengaduan (KKA_Pengaduan)</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Complaint ID (E) <span class="text-danger">*</span></label>
                        <input type="text" name="complaint_id" class="form-control form-control-sm" placeholder="Contoh: CMP-202610-0012" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Tanggal Terima (F) <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_terima" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Kategori (G)</label>
                        <input type="text" name="kategori" class="form-control form-control-sm" placeholder="ATM / Layanan Teller / Kredit / Lainnya">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">SLA Hari (I) <span class="text-danger">*</span></label>
                        <input type="number" name="sla_hari" class="form-control form-control-sm" value="5" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Tanggal Selesai (J)</label>
                        <input type="date" name="tanggal_selesai" class="form-control form-control-sm">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Uraian Pengaduan (H) <span class="text-danger">*</span></label>
                        <textarea name="uraian_pengaduan" class="form-control form-control-sm" rows="2" placeholder="Uraian ringkas keluhan nasabah..." required></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Status Penyelesaian (L)</label>
                        <select name="status_penyelesaian" class="form-select form-select-sm">
                            <option value="Selesai" selected>Selesai</option>
                            <option value="Dalam Proses">Dalam Proses</option>
                            <option value="Belum Ditangani">Belum Ditangani</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Bukti Penyelesaian? (M)</label>
                        <select name="bukti_penyelesaian" class="form-select form-select-sm">
                            <option value="Ya" selected>Ya</option>
                            <option value="Tidak">Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Berulang? (N)</label>
                        <select name="berulang" class="form-select form-select-sm">
                            <option value="Tidak" selected>Tidak</option>
                            <option value="Ya">Ya</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Akar Masalah (O)</label>
                        <textarea name="akar_masalah" class="form-control form-control-sm" rows="2" placeholder="Penyebab sistem/human error..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Dampak Nasabah (P)</label>
                        <textarea name="dampak_nasabah" class="form-control form-control-sm" rows="2" placeholder="Kerugian materiil/immateriil nasabah..."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Analisis SLA dan Penanganan (Q) <span class="text-danger">*</span></label>
                        <textarea name="analisis_sla" class="form-control form-control-sm" rows="2" placeholder="Uraian analisis ketepatan waktu respon dan efektivitas resolusi..." required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Referensi Bukti (R) <span class="text-danger">*</span></label>
                        <input type="text" name="referensi_bukti" class="form-control form-control-sm" placeholder="Tiket pengaduan / formulir penyelesaian" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Simpulan (S) <span class="text-danger">*</span></label>
                        <input type="text" name="simpulan" class="form-control form-control-sm" placeholder="Sesuai SLA / Terlambat melebihi SLA" required>
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
