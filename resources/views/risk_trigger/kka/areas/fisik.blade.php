{{-- Area: KKA_Fisik (Untuk Cash Opname, Rekon Cek BG, Rekon Barang Cetakan, & Aset - 22 Kolom: A s.d. V) --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-box-seam text-success me-2"></i>KKA Pemeriksaan Fisik & Aset (22 Kolom Excel: A s.d. V)
            </h6>
            <small class="text-muted">
                Pemeriksaan fisik brankas (Cash Opname), rekonsiliasi Cek/Bilyet Giro, barang cetakan bernilai, dan fisik aset inventaris kantor.
            </small>
        </div>
        @if($canModify && !$isFinal)
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddFisik">
            <i class="bi bi-plus-circle me-1"></i> + Tambah Sampel Fisik
        </button>
        @endif
    </div>

    <div class="table-responsive" style="max-height: 520px;">
        <table class="table table-hover align-middle mb-0 data-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 70px;">Aksi</th>
                    <th>Status Uji (U)</th>
                    <th>Exception (V)</th>
                    <th>Jenis Objek (E)</th>
                    <th>Identitas / Lokasi / Seri (F)</th>
                    <th>Qty Sistem (G)</th>
                    <th>Qty Catatan (H)</th>
                    <th>Qty Fisik (I)</th>
                    <th>Harga Satuan (J)</th>
                    <th>Nilai Sistem (K)</th>
                    <th>Nilai Fisik (L)</th>
                    <th class="table-success text-success fw-bold">Selisih Qty (M = I-G)</th>
                    <th class="table-success text-success fw-bold">Selisih Nilai (N = L-K)</th>
                    <th>Kondisi / Custody (O)</th>
                    <th>Analisis Rekonsiliasi (P)</th>
                    <th>Sebab (Q)</th>
                    <th>Dampak (R)</th>
                    <th>Ref Bukti (S)</th>
                    <th>Simpulan (T)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fisikRows as $row)
                <tr>
                    <td class="text-center text-nowrap">
                        @if($canModify && !$isFinal)
                        <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditFisik{{ $row->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('risk-trigger.kka.area-row.destroy', ['area' => 'KKA_Fisik', 'rowId' => $row->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus baris sampel ini?')">
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
                    <td class="fw-semibold">{{ $row->jenis_objek }}</td>
                    <td class="font-monospace">{{ $row->identitas_lokasi_seri }}</td>
                    <td class="text-center font-monospace">{{ number_format($row->qty_sistem ?? 0, 0) }}</td>
                    <td class="text-center font-monospace">{{ number_format($row->qty_catatan ?? 0, 0) }}</td>
                    <td class="text-center font-monospace">{{ number_format($row->qty_fisik ?? 0, 0) }}</td>
                    <td class="font-monospace text-end">Rp {{ number_format($row->harga_satuan ?? 0, 0, ',', '.') }}</td>
                    <td class="font-monospace text-end">Rp {{ number_format($row->nilai_sistem ?? 0, 0, ',', '.') }}</td>
                    <td class="font-monospace text-end">Rp {{ number_format($row->nilai_fisik ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center font-monospace fw-bold {{ abs($row->selisih_qty ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($row->selisih_qty ?? 0, 0) }}
                    </td>
                    <td class="font-monospace text-end fw-bold {{ abs($row->selisih_nilai ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($row->selisih_nilai ?? 0, 0, ',', '.') }}
                    </td>
                    <td>
                        <span class="badge {{ $row->kondisi_custody == 'Sesuai' ? 'bg-success' : 'bg-danger' }}">
                            {{ $row->kondisi_custody }}
                        </span>
                    </td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->analisis_rekonsiliasi }}</td>
                    <td class="text-wrap" style="max-width: 160px;">{{ $row->sebab }}</td>
                    <td class="text-wrap" style="max-width: 160px;">{{ $row->dampak }}</td>
                    <td>{{ $row->referensi_bukti }}</td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->simpulan }}</td>
                </tr>

                {{-- Modal Edit Fisik --}}
                @if($canModify && !$isFinal)
                <div class="modal fade" id="modalEditFisik{{ $row->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <form action="{{ route('risk-trigger.kka.area-row.update', ['area' => 'KKA_Fisik', 'rowId' => $row->id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold">Edit Baris Pemeriksaan Fisik #{{ $row->id }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Jenis Objek (E)</label>
                                        <input type="text" name="jenis_objek" class="form-control form-control-sm" value="{{ $row->jenis_objek }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Identitas / Seri / Lokasi (F)</label>
                                        <input type="text" name="identitas_lokasi_seri" class="form-control form-control-sm" value="{{ $row->identitas_lokasi_seri }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Qty Sistem (G)</label>
                                        <input type="number" name="qty_sistem" class="form-control form-control-sm" value="{{ $row->qty_sistem }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Qty Catatan Register (H)</label>
                                        <input type="number" name="qty_catatan" class="form-control form-control-sm" value="{{ $row->qty_catatan }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Qty Fisik Hasil Opname (I)</label>
                                        <input type="number" name="qty_fisik" class="form-control form-control-sm" value="{{ $row->qty_fisik }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Harga Satuan (J)</label>
                                        <input type="number" step="0.01" name="harga_satuan" class="form-control form-control-sm" value="{{ $row->harga_satuan }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Kondisi / Custody (O)</label>
                                        <select name="kondisi_custody" class="form-select form-select-sm">
                                            <option value="Sesuai" {{ $row->kondisi_custody == 'Sesuai' ? 'selected' : '' }}>Sesuai</option>
                                            <option value="Tidak Sesuai" {{ $row->kondisi_custody == 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Analisis Rekonsiliasi (P)</label>
                                        <textarea name="analisis_rekonsiliasi" class="form-control form-control-sm" rows="2" required>{{ $row->analisis_rekonsiliasi }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Sebab (Q)</label>
                                        <textarea name="sebab" class="form-control form-control-sm" rows="2">{{ $row->sebab }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Dampak (R)</label>
                                        <textarea name="dampak" class="form-control form-control-sm" rows="2">{{ $row->dampak }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Referensi Bukti (S)</label>
                                        <input type="text" name="referensi_bukti" class="form-control form-control-sm" value="{{ $row->referensi_bukti }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Simpulan (T)</label>
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
                    <td colspan="19" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-1 opacity-50"></i>
                        Belum ada sampel pemeriksaan fisik/aset pada KKA Fisik untuk Visit ID ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Add Fisik --}}
@if($canModify && !$isFinal)
<div class="modal fade" id="modalAddFisik" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('risk-trigger.kka.area-row.store', $kka->id) }}" method="POST">
                @csrf
                <input type="hidden" name="kka_area" value="KKA_Fisik">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Tambah Sampel Pemeriksaan Fisik & Aset (KKA_Fisik)</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Jenis Objek (E) <span class="text-danger">*</span></label>
                        <input type="text" name="jenis_objek" class="form-control form-control-sm" placeholder="Uang Kas / Cek BG / Formulir Cetakan / Aset" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Identitas / Seri / Lokasi (F) <span class="text-danger">*</span></label>
                        <input type="text" name="identitas_lokasi_seri" class="form-control form-control-sm" placeholder="No seri / brankas / lokasi ruang" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Qty Sistem (G) <span class="text-danger">*</span></label>
                        <input type="number" name="qty_sistem" class="form-control form-control-sm" placeholder="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Qty Catatan Register (H)</label>
                        <input type="number" name="qty_catatan" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Qty Fisik Opname (I) <span class="text-danger">*</span></label>
                        <input type="number" name="qty_fisik" class="form-control form-control-sm" placeholder="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Harga Satuan (J)</label>
                        <input type="number" step="0.01" name="harga_satuan" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Kondisi / Custody (O)</label>
                        <select name="kondisi_custody" class="form-select form-select-sm">
                            <option value="Sesuai" selected>Sesuai</option>
                            <option value="Tidak Sesuai">Tidak Sesuai</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Analisis Rekonsiliasi (P) <span class="text-danger">*</span></label>
                        <textarea name="analisis_rekonsiliasi" class="form-control form-control-sm" rows="2" placeholder="Uraian pencocokan antara fisik dan pembukuan/sistem..." required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Sebab (Q)</label>
                        <textarea name="sebab" class="form-control form-control-sm" rows="2" placeholder="Penyebab jika timbul selisih..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Dampak (R)</label>
                        <textarea name="dampak" class="form-control form-control-sm" rows="2" placeholder="Potensi kerugian fisik/aset..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Referensi Bukti (S) <span class="text-danger">*</span></label>
                        <input type="text" name="referensi_bukti" class="form-control form-control-sm" placeholder="Berita Acara Opname Fisik & foto bukti" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Simpulan (T) <span class="text-danger">*</span></label>
                        <input type="text" name="simpulan" class="form-control form-control-sm" placeholder="Fisik sesuai / Terdapat selisih" required>
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
