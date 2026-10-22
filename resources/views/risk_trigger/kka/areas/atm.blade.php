{{-- Area: KKA_ATM (Untuk Area Kartu ATM & Rekon Kartu ATM - 23 Kolom: A s.d. W) --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-credit-card-2-front text-primary me-2"></i>KKA Kartu ATM & Rekon ATM (23 Kolom Excel: A s.d. W)
            </h6>
            <small class="text-muted">
                Pengujian stok kartu ATM instan/reguler, rekonsiliasi fisik vs buku/sistem, kesesuaian PIN Mailer, dual custody, dan akses log kartu.
            </small>
        </div>
        @if($canModify && !$isFinal)
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddAtm">
            <i class="bi bi-plus-circle me-1"></i> + Tambah Sampel Kartu ATM
        </button>
        @endif
    </div>

    <div class="table-responsive" style="max-height: 520px;">
        <table class="table table-hover align-middle mb-0 data-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 70px;">Aksi</th>
                    <th>Status Uji (V)</th>
                    <th>Exception (W)</th>
                    <th>Objek / Jenis Kartu (E)</th>
                    <th>No Referensi (F)</th>
                    <th>Qty Buku (G)</th>
                    <th>Qty Fisik (H)</th>
                    <th>Qty PIN Mailer (I)</th>
                    <th>Harga Satuan (J)</th>
                    <th>Nilai Buku (K)</th>
                    <th>Nilai Fisik (L)</th>
                    <th class="table-primary text-primary fw-bold">Selisih Qty (M = H-G)</th>
                    <th class="table-primary text-primary fw-bold">Selisih Nilai (N = L-K)</th>
                    <th>Custody Sesuai (O)</th>
                    <th>Akses/Log Sesuai (P)</th>
                    <th>Analisis Rekon (Q)</th>
                    <th>Sebab (R)</th>
                    <th>Dampak (S)</th>
                    <th>Ref Bukti (T)</th>
                    <th>Simpulan (U)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($atmRows as $row)
                <tr>
                    <td class="text-center text-nowrap">
                        @if($canModify && !$isFinal)
                        <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditAtm{{ $row->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('risk-trigger.kka.area-row.destroy', ['area' => 'KKA_ATM', 'rowId' => $row->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus baris sampel ini?')">
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
                    <td class="fw-semibold">{{ $row->objek_jenis_kartu_atm }}</td>
                    <td class="font-monospace">{{ $row->no_referensi }}</td>
                    <td class="text-center font-monospace">{{ number_format($row->qty_buku ?? 0, 0) }}</td>
                    <td class="text-center font-monospace">{{ number_format($row->qty_fisik ?? 0, 0) }}</td>
                    <td class="text-center font-monospace">{{ number_format($row->qty_pin_mailer ?? 0, 0) }}</td>
                    <td class="font-monospace text-end">Rp {{ number_format($row->harga_satuan ?? 0, 0, ',', '.') }}</td>
                    <td class="font-monospace text-end">Rp {{ number_format($row->nilai_buku ?? 0, 0, ',', '.') }}</td>
                    <td class="font-monospace text-end">Rp {{ number_format($row->nilai_fisik ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center font-monospace fw-bold {{ abs($row->selisih_qty ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($row->selisih_qty ?? 0, 0) }}
                    </td>
                    <td class="font-monospace text-end fw-bold {{ abs($row->selisih_nilai ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($row->selisih_nilai ?? 0, 0, ',', '.') }}
                    </td>
                    <td><span class="badge {{ $row->custody_sesuai == 'Ya' ? 'bg-success' : 'bg-danger' }}">{{ $row->custody_sesuai }}</span></td>
                    <td><span class="badge {{ $row->akses_log_sesuai == 'Ya' ? 'bg-success' : 'bg-danger' }}">{{ $row->akses_log_sesuai }}</span></td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->analisis_rekonsiliasi }}</td>
                    <td class="text-wrap" style="max-width: 160px;">{{ $row->sebab }}</td>
                    <td class="text-wrap" style="max-width: 160px;">{{ $row->dampak }}</td>
                    <td>{{ $row->referensi_bukti }}</td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->simpulan }}</td>
                </tr>

                {{-- Modal Edit ATM --}}
                @if($canModify && !$isFinal)
                <div class="modal fade" id="modalEditAtm{{ $row->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <form action="{{ route('risk-trigger.kka.area-row.update', ['area' => 'KKA_ATM', 'rowId' => $row->id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold">Edit Baris Pengujian ATM / Kartu #{{ $row->id }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Objek / Jenis Kartu / ATM (E)</label>
                                        <input type="text" name="objek_jenis_kartu_atm" class="form-control form-control-sm" value="{{ $row->objek_jenis_kartu_atm }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">No Referensi / Batch (F)</label>
                                        <input type="text" name="no_referensi" class="form-control form-control-sm" value="{{ $row->no_referensi }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Qty Buku (G)</label>
                                        <input type="number" name="qty_buku" class="form-control form-control-sm" value="{{ $row->qty_buku }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Qty Fisik (H)</label>
                                        <input type="number" name="qty_fisik" class="form-control form-control-sm" value="{{ $row->qty_fisik }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Qty PIN Mailer (I)</label>
                                        <input type="number" name="qty_pin_mailer" class="form-control form-control-sm" value="{{ $row->qty_pin_mailer }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Harga Satuan (J)</label>
                                        <input type="number" step="0.01" name="harga_satuan" class="form-control form-control-sm" value="{{ $row->harga_satuan }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Dual Custody Sesuai? (O)</label>
                                        <select name="custody_sesuai" class="form-select form-select-sm">
                                            <option value="Ya" {{ $row->custody_sesuai == 'Ya' ? 'selected' : '' }}>Ya</option>
                                            <option value="Tidak" {{ $row->custody_sesuai == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Akses / Log Sesuai? (P)</label>
                                        <select name="akses_log_sesuai" class="form-select form-select-sm">
                                            <option value="Ya" {{ $row->akses_log_sesuai == 'Ya' ? 'selected' : '' }}>Ya</option>
                                            <option value="Tidak" {{ $row->akses_log_sesuai == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Analisis Rekonsiliasi (Q)</label>
                                        <textarea name="analisis_rekonsiliasi" class="form-control form-control-sm" rows="2" required>{{ $row->analisis_rekonsiliasi }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Sebab (R)</label>
                                        <textarea name="sebab" class="form-control form-control-sm" rows="2">{{ $row->sebab }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Dampak (S)</label>
                                        <textarea name="dampak" class="form-control form-control-sm" rows="2">{{ $row->dampak }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Referensi Bukti (T)</label>
                                        <input type="text" name="referensi_bukti" class="form-control form-control-sm" value="{{ $row->referensi_bukti }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Simpulan (U)</label>
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
                    <td colspan="20" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-1 opacity-50"></i>
                        Belum ada sampel kartu ATM / rekon ATM pada KKA ATM untuk Visit ID ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Add ATM --}}
@if($canModify && !$isFinal)
<div class="modal fade" id="modalAddAtm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('risk-trigger.kka.area-row.store', $kka->id) }}" method="POST">
                @csrf
                <input type="hidden" name="kka_area" value="KKA_ATM">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Tambah Sampel Kartu ATM / Rekon ATM (KKA_ATM)</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Objek / Jenis Kartu / ATM (E) <span class="text-danger">*</span></label>
                        <input type="text" name="objek_jenis_kartu_atm" class="form-control form-control-sm" placeholder="Contoh: Kartu ATM Instan GPN" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">No Referensi / Batch (F)</label>
                        <input type="text" name="no_referensi" class="form-control form-control-sm" placeholder="No batch pengiriman/buku register">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Qty Buku (G) <span class="text-danger">*</span></label>
                        <input type="number" name="qty_buku" class="form-control form-control-sm" placeholder="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Qty Fisik (H) <span class="text-danger">*</span></label>
                        <input type="number" name="qty_fisik" class="form-control form-control-sm" placeholder="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Qty PIN Mailer (I)</label>
                        <input type="number" name="qty_pin_mailer" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Harga Satuan (J)</label>
                        <input type="number" step="0.01" name="harga_satuan" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Dual Custody Sesuai? (O)</label>
                        <select name="custody_sesuai" class="form-select form-select-sm">
                            <option value="Ya" selected>Ya</option>
                            <option value="Tidak">Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Akses / Log Sesuai? (P)</label>
                        <select name="akses_log_sesuai" class="form-select form-select-sm">
                            <option value="Ya" selected>Ya</option>
                            <option value="Tidak">Tidak</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Analisis Rekonsiliasi (Q) <span class="text-danger">*</span></label>
                        <textarea name="analisis_rekonsiliasi" class="form-control form-control-sm" rows="2" placeholder="Uraian perbandingan fisik vs buku dan audit fisik brankas..." required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Sebab (R)</label>
                        <textarea name="sebab" class="form-control form-control-sm" rows="2" placeholder="Penyebab bila ada selisih..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Dampak (S)</label>
                        <textarea name="dampak" class="form-control form-control-sm" rows="2" placeholder="Potensi penyalahgunaan fisik kartu..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Referensi Bukti (T) <span class="text-danger">*</span></label>
                        <input type="text" name="referensi_bukti" class="form-control form-control-sm" placeholder="Berita Acara Opname Fisik & Register" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Simpulan (U) <span class="text-danger">*</span></label>
                        <input type="text" name="simpulan" class="form-control form-control-sm" placeholder="Fisik cocok / Terdapat selisih" required>
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
