{{-- Area: KKA_Kredit (24 Kolom: A s.d. X) --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-bank2 text-warning me-2"></i>KKA Kredit (24 Kolom Excel: A s.d. X)
            </h6>
            <small class="text-muted">
                Pengujian kelayakan kredit, coverage agunan & asuransi terhadap baki debet (outstanding), persetujuan wewenang, dan covenant deviasi.
            </small>
        </div>
        @if($canModify && !$isFinal)
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddKredit">
            <i class="bi bi-plus-circle me-1"></i> + Tambah Sampel Kredit
        </button>
        @endif
    </div>

    <div class="table-responsive" style="max-height: 520px;">
        <table class="table table-hover align-middle mb-0 data-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 70px;">Aksi</th>
                    <th>Status Uji (W)</th>
                    <th>Exception (X)</th>
                    <th>No Kredit / Debitur (E)</th>
                    <th>Nama Debitur (F)</th>
                    <th>Produk (G)</th>
                    <th>Plafond (H)</th>
                    <th>Outstanding (I)</th>
                    <th>Nilai Agunan (J)</th>
                    <th class="table-warning text-dark fw-bold">Coverage Agunan (K = J/I)</th>
                    <th>Nilai Asuransi (L)</th>
                    <th class="table-warning text-dark fw-bold">Coverage Asuransi (M = L/I)</th>
                    <th>Persetujuan Sesuai (N)</th>
                    <th>Pencairan Sesuai (O)</th>
                    <th>Dokumen Lengkap (P)</th>
                    <th>Covenant / Deviasi (Q)</th>
                    <th>Analisis Kredit (R)</th>
                    <th>Sebab (S)</th>
                    <th>Dampak (T)</th>
                    <th>Ref Bukti (U)</th>
                    <th>Simpulan (V)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kreditRows as $row)
                <tr>
                    <td class="text-center text-nowrap">
                        @if($canModify && !$isFinal)
                        <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditKredit{{ $row->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('risk-trigger.kka.area-row.destroy', ['area' => 'KKA_Kredit', 'rowId' => $row->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus baris sampel ini?')">
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
                    <td class="font-monospace fw-semibold">{{ $row->no_kredit_debitur }}</td>
                    <td class="fw-semibold">{{ $row->nama_debitur }}</td>
                    <td>{{ $row->produk }}</td>
                    <td class="font-monospace text-end">Rp {{ number_format($row->plafond ?? 0, 0, ',', '.') }}</td>
                    <td class="font-monospace text-end fw-bold">Rp {{ number_format($row->outstanding ?? 0, 0, ',', '.') }}</td>
                    <td class="font-monospace text-end">Rp {{ number_format($row->nilai_agunan ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center font-monospace fw-bold {{ ($row->coverage_agunan ?? 0) < 1.0 ? 'text-danger' : 'text-success' }}">
                        {{ $row->coverage_agunan !== null ? number_format($row->coverage_agunan * 100, 1) . '%' : '-' }}
                    </td>
                    <td class="font-monospace text-end">Rp {{ number_format($row->nilai_asuransi ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center font-monospace fw-bold {{ ($row->coverage_asuransi ?? 0) < 1.0 ? 'text-danger' : 'text-success' }}">
                        {{ $row->coverage_asuransi !== null ? number_format($row->coverage_asuransi * 100, 1) . '%' : '-' }}
                    </td>
                    <td><span class="badge {{ $row->persetujuan_sesuai == 'Ya' ? 'bg-success' : 'bg-danger' }}">{{ $row->persetujuan_sesuai }}</span></td>
                    <td><span class="badge {{ $row->pencairan_sesuai == 'Ya' ? 'bg-success' : 'bg-danger' }}">{{ $row->pencairan_sesuai }}</span></td>
                    <td><span class="badge {{ $row->dokumen_lengkap == 'Ya' ? 'bg-success' : 'bg-danger' }}">{{ $row->dokumen_lengkap }}</span></td>
                    <td class="text-wrap" style="max-width: 150px;">{{ $row->covenant_deviasi ?: '-' }}</td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->analisis_kredit }}</td>
                    <td class="text-wrap" style="max-width: 160px;">{{ $row->sebab }}</td>
                    <td class="text-wrap" style="max-width: 160px;">{{ $row->dampak }}</td>
                    <td>{{ $row->referensi_bukti }}</td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->simpulan }}</td>
                </tr>

                {{-- Modal Edit Kredit --}}
                @if($canModify && !$isFinal)
                <div class="modal fade" id="modalEditKredit{{ $row->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <form action="{{ route('risk-trigger.kka.area-row.update', ['area' => 'KKA_Kredit', 'rowId' => $row->id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold">Edit Baris Pengujian Kredit #{{ $row->id }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">No Kredit / Debitur (E)</label>
                                        <input type="text" name="no_kredit_debitur" class="form-control form-control-sm" value="{{ $row->no_kredit_debitur }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nama Debitur (F)</label>
                                        <input type="text" name="nama_debitur" class="form-control form-control-sm" value="{{ $row->nama_debitur }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Produk Kredit (G)</label>
                                        <input type="text" name="produk" class="form-control form-control-sm" value="{{ $row->produk }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Plafond (H)</label>
                                        <input type="number" step="0.01" name="plafond" class="form-control form-control-sm" value="{{ $row->plafond }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Outstanding (I)</label>
                                        <input type="number" step="0.01" name="outstanding" class="form-control form-control-sm" value="{{ $row->outstanding }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nilai Agunan (J)</label>
                                        <input type="number" step="0.01" name="nilai_agunan" class="form-control form-control-sm" value="{{ $row->nilai_agunan }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nilai Asuransi (L)</label>
                                        <input type="number" step="0.01" name="nilai_asuransi" class="form-control form-control-sm" value="{{ $row->nilai_asuransi }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Persetujuan Sesuai? (N)</label>
                                        <select name="persetujuan_sesuai" class="form-select form-select-sm">
                                            <option value="Ya" {{ $row->persetujuan_sesuai == 'Ya' ? 'selected' : '' }}>Ya</option>
                                            <option value="Tidak" {{ $row->persetujuan_sesuai == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Pencairan Sesuai? (O)</label>
                                        <select name="pencairan_sesuai" class="form-select form-select-sm">
                                            <option value="Ya" {{ $row->pencairan_sesuai == 'Ya' ? 'selected' : '' }}>Ya</option>
                                            <option value="Tidak" {{ $row->pencairan_sesuai == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Dokumen Lengkap? (P)</label>
                                        <select name="dokumen_lengkap" class="form-select form-select-sm">
                                            <option value="Ya" {{ $row->dokumen_lengkap == 'Ya' ? 'selected' : '' }}>Ya</option>
                                            <option value="Tidak" {{ $row->dokumen_lengkap == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Covenant / Deviasi (Q)</label>
                                        <input type="text" name="covenant_deviasi" class="form-control form-control-sm" value="{{ $row->covenant_deviasi }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Analisis Kredit & Perhitungan (R)</label>
                                        <textarea name="analisis_kredit" class="form-control form-control-sm" rows="2" required>{{ $row->analisis_kredit }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Sebab (S)</label>
                                        <textarea name="sebab" class="form-control form-control-sm" rows="2">{{ $row->sebab }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Dampak (T)</label>
                                        <textarea name="dampak" class="form-control form-control-sm" rows="2">{{ $row->dampak }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Referensi Bukti (U)</label>
                                        <input type="text" name="referensi_bukti" class="form-control form-control-sm" value="{{ $row->referensi_bukti }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Simpulan (V)</label>
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
                    <td colspan="21" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-1 opacity-50"></i>
                        Belum ada sampel debitur/kredit pada KKA Kredit untuk Visit ID ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Add Kredit --}}
@if($canModify && !$isFinal)
<div class="modal fade" id="modalAddKredit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('risk-trigger.kka.area-row.store', $kka->id) }}" method="POST">
                @csrf
                <input type="hidden" name="kka_area" value="KKA_Kredit">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Tambah Sampel Pengujian Kredit (KKA_Kredit)</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">No Kredit / Debitur (E) <span class="text-danger">*</span></label>
                        <input type="text" name="no_kredit_debitur" class="form-control form-control-sm" placeholder="Contoh: KRD-003-99120" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nama Debitur (F)</label>
                        <input type="text" name="nama_debitur" class="form-control form-control-sm" placeholder="Nama debitur">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Produk Kredit (G)</label>
                        <input type="text" name="produk" class="form-control form-control-sm" placeholder="Kredit Modal Kerja/Konsumtif">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Plafond (H) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="plafond" class="form-control form-control-sm" placeholder="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Outstanding (I) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="outstanding" class="form-control form-control-sm" placeholder="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nilai Agunan (J)</label>
                        <input type="number" step="0.01" name="nilai_agunan" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nilai Asuransi (L)</label>
                        <input type="number" step="0.01" name="nilai_asuransi" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Persetujuan Sesuai? (N)</label>
                        <select name="persetujuan_sesuai" class="form-select form-select-sm">
                            <option value="Ya" selected>Ya</option>
                            <option value="Tidak">Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Pencairan Sesuai? (O)</label>
                        <select name="pencairan_sesuai" class="form-select form-select-sm">
                            <option value="Ya" selected>Ya</option>
                            <option value="Tidak">Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Dokumen Lengkap? (P)</label>
                        <select name="dokumen_lengkap" class="form-select form-select-sm">
                            <option value="Ya" selected>Ya</option>
                            <option value="Tidak">Tidak</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Covenant / Deviasi (Q)</label>
                        <input type="text" name="covenant_deviasi" class="form-control form-control-sm" placeholder="Catatan deviasi bila ada">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Analisis Kredit & Perhitungan (R) <span class="text-danger">*</span></label>
                        <textarea name="analisis_kredit" class="form-control form-control-sm" rows="2" placeholder="Uraian analisis kelayakan dan coverage agunan/asuransi..." required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Sebab (S)</label>
                        <textarea name="sebab" class="form-control form-control-sm" rows="2" placeholder="Penyebab jika under-collateral / dokumen kurang..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Dampak (T)</label>
                        <textarea name="dampak" class="form-control form-control-sm" rows="2" placeholder="Potensi risiko gagal bayar / kerugian..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Referensi Bukti (U) <span class="text-danger">*</span></label>
                        <input type="text" name="referensi_bukti" class="form-control form-control-sm" placeholder="Perjanjian kredit & laporan appraisal" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Simpulan (V) <span class="text-danger">*</span></label>
                        <input type="text" name="simpulan" class="form-control form-control-sm" placeholder="Agunan mencukupi / Deviasi tidak diotorisasi" required>
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
