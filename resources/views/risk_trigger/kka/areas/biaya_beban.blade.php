{{-- Area: KKA_Biaya_Beban (24 Kolom: A s.d. X) --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-receipt-cutoff text-danger me-2"></i>KKA Biaya Beban (24 Kolom Excel: A s.d. X)
            </h6>
            <small class="text-muted">
                Pengujian kewajaran pengeluaran beban/biaya, selisih invoice vs buku, ketersediaan anggaran, bukti penerimaan barang/jasa, dan pemotongan pajak.
            </small>
        </div>
        @if($canModify && !$isFinal)
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddBiayaBeban">
            <i class="bi bi-plus-circle me-1"></i> + Tambah Sampel Biaya Beban
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
                    <th>GL / Rekening (E)</th>
                    <th>No Voucher (F)</th>
                    <th>Tanggal (G)</th>
                    <th>Redaksi (H)</th>
                    <th>Nominal Buku (I)</th>
                    <th>Nominal Invoice (J)</th>
                    <th>Nominal Disetujui (K)</th>
                    <th class="table-danger text-danger fw-bold">Selisih (L = I - K)</th>
                    <th>Anggaran Ada (M)</th>
                    <th>Otorisasi Sesuai (N)</th>
                    <th>Pajak Sesuai (O)</th>
                    <th>Barang Diterima (P)</th>
                    <th>Transaksi Berulang (Q)</th>
                    <th>Analisis Kewajaran (R)</th>
                    <th>Sebab (S)</th>
                    <th>Dampak (T)</th>
                    <th>Ref Bukti (U)</th>
                    <th>Simpulan (V)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($biayaBebanRows as $row)
                <tr>
                    <td class="text-center text-nowrap">
                        @if($canModify && !$isFinal)
                        <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditBiayaBeban{{ $row->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('risk-trigger.kka.area-row.destroy', ['area' => 'KKA_Biaya_Beban', 'rowId' => $row->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus baris sampel ini?')">
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
                    <td class="font-monospace fw-semibold">{{ $row->gl_rekening }}</td>
                    <td class="font-monospace">{{ $row->no_voucher }}</td>
                    <td class="text-nowrap">{{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '-' }}</td>
                    <td class="text-wrap" style="max-width: 180px;">{{ $row->redaksi }}</td>
                    <td class="font-monospace text-end">Rp {{ number_format($row->nominal_buku ?? 0, 0, ',', '.') }}</td>
                    <td class="font-monospace text-end">Rp {{ number_format($row->nominal_invoice ?? 0, 0, ',', '.') }}</td>
                    <td class="font-monospace text-end">Rp {{ number_format($row->nominal_disetujui ?? 0, 0, ',', '.') }}</td>
                    <td class="font-monospace text-end fw-bold {{ abs($row->selisih ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($row->selisih ?? 0, 0, ',', '.') }}
                    </td>
                    <td><span class="badge {{ $row->anggaran_tersedia == 'Ya' ? 'bg-success' : 'bg-danger' }}">{{ $row->anggaran_tersedia }}</span></td>
                    <td><span class="badge {{ $row->otorisasi_sesuai == 'Ya' ? 'bg-success' : 'bg-danger' }}">{{ $row->otorisasi_sesuai }}</span></td>
                    <td><span class="badge {{ $row->pajak_sesuai == 'Ya' ? 'bg-success' : 'bg-danger' }}">{{ $row->pajak_sesuai }}</span></td>
                    <td><span class="badge {{ $row->barang_jasa_diterima == 'Ya' ? 'bg-success' : 'bg-danger' }}">{{ $row->barang_jasa_diterima }}</span></td>
                    <td><span class="badge {{ $row->transaksi_berulang == 'Ya' ? 'bg-danger' : 'bg-light text-dark border' }}">{{ $row->transaksi_berulang }}</span></td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->analisis_kewajaran }}</td>
                    <td class="text-wrap" style="max-width: 160px;">{{ $row->sebab }}</td>
                    <td class="text-wrap" style="max-width: 160px;">{{ $row->dampak }}</td>
                    <td>{{ $row->referensi_bukti }}</td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->simpulan }}</td>
                </tr>

                {{-- Modal Edit Biaya Beban --}}
                @if($canModify && !$isFinal)
                <div class="modal fade" id="modalEditBiayaBeban{{ $row->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <form action="{{ route('risk-trigger.kka.area-row.update', ['area' => 'KKA_Biaya_Beban', 'rowId' => $row->id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold">Edit Baris Pengujian Biaya Beban #{{ $row->id }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">GL / Rekening Beban (E)</label>
                                        <input type="text" name="gl_rekening" class="form-control form-control-sm" value="{{ $row->gl_rekening }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">No Voucher (F)</label>
                                        <input type="text" name="no_voucher" class="form-control form-control-sm" value="{{ $row->no_voucher }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Tanggal (G)</label>
                                        <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d') : '' }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Redaksi Beban (H)</label>
                                        <input type="text" name="redaksi" class="form-control form-control-sm" value="{{ $row->redaksi }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Nominal Buku (I)</label>
                                        <input type="number" step="0.01" name="nominal_buku" class="form-control form-control-sm" value="{{ $row->nominal_buku }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Nominal Invoice (J)</label>
                                        <input type="number" step="0.01" name="nominal_invoice" class="form-control form-control-sm" value="{{ $row->nominal_invoice }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Nominal Disetujui (K)</label>
                                        <input type="number" step="0.01" name="nominal_disetujui" class="form-control form-control-sm" value="{{ $row->nominal_disetujui }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Anggaran Tersedia? (M)</label>
                                        <select name="anggaran_tersedia" class="form-select form-select-sm">
                                            <option value="Ya" {{ $row->anggaran_tersedia == 'Ya' ? 'selected' : '' }}>Ya</option>
                                            <option value="Tidak" {{ $row->anggaran_tersedia == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Otorisasi Sesuai? (N)</label>
                                        <select name="otorisasi_sesuai" class="form-select form-select-sm">
                                            <option value="Ya" {{ $row->otorisasi_sesuai == 'Ya' ? 'selected' : '' }}>Ya</option>
                                            <option value="Tidak" {{ $row->otorisasi_sesuai == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Pajak Sesuai? (O)</label>
                                        <select name="pajak_sesuai" class="form-select form-select-sm">
                                            <option value="Ya" {{ $row->pajak_sesuai == 'Ya' ? 'selected' : '' }}>Ya</option>
                                            <option value="Tidak" {{ $row->pajak_sesuai == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Barang / Jasa Diterima? (P)</label>
                                        <select name="barang_jasa_diterima" class="form-select form-select-sm">
                                            <option value="Ya" {{ $row->barang_jasa_diterima == 'Ya' ? 'selected' : '' }}>Ya</option>
                                            <option value="Tidak" {{ $row->barang_jasa_diterima == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Transaksi Berulang Tanpa Kontrak? (Q)</label>
                                        <select name="transaksi_berulang" class="form-select form-select-sm">
                                            <option value="Tidak" {{ $row->transaksi_berulang == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                            <option value="Ya" {{ $row->transaksi_berulang == 'Ya' ? 'selected' : '' }}>Ya</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Analisis Kewajaran & Pola (R)</label>
                                        <textarea name="analisis_kewajaran" class="form-control form-control-sm" rows="2" required>{{ $row->analisis_kewajaran }}</textarea>
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
                        Belum ada sampel beban/biaya pada KKA Biaya Beban untuk Visit ID ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Add Biaya Beban --}}
@if($canModify && !$isFinal)
<div class="modal fade" id="modalAddBiayaBeban" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('risk-trigger.kka.area-row.store', $kka->id) }}" method="POST">
                @csrf
                <input type="hidden" name="kka_area" value="KKA_Biaya_Beban">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Tambah Sampel Pengujian Biaya Beban (KKA_Biaya_Beban)</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">GL / Rekening Beban (E) <span class="text-danger">*</span></label>
                        <input type="text" name="gl_rekening" class="form-control form-control-sm" placeholder="Contoh: 512.01.002" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">No Voucher (F) <span class="text-danger">*</span></label>
                        <input type="text" name="no_voucher" class="form-control form-control-sm" placeholder="Contoh: VCR-202610-098" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Tanggal (G) <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Redaksi Beban (H)</label>
                        <input type="text" name="redaksi" class="form-control form-control-sm" placeholder="Uraian pembayaran/pengeluaran">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Nominal Buku (I) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="nominal_buku" class="form-control form-control-sm" placeholder="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Nominal Invoice (J)</label>
                        <input type="number" step="0.01" name="nominal_invoice" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Nominal Disetujui (K)</label>
                        <input type="number" step="0.01" name="nominal_disetujui" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Anggaran Tersedia? (M)</label>
                        <select name="anggaran_tersedia" class="form-select form-select-sm">
                            <option value="Ya" selected>Ya</option>
                            <option value="Tidak">Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Otorisasi Sesuai? (N)</label>
                        <select name="otorisasi_sesuai" class="form-select form-select-sm">
                            <option value="Ya" selected>Ya</option>
                            <option value="Tidak">Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Pajak Sesuai? (O)</label>
                        <select name="pajak_sesuai" class="form-select form-select-sm">
                            <option value="Ya" selected>Ya</option>
                            <option value="Tidak">Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Barang / Jasa Diterima? (P)</label>
                        <select name="barang_jasa_diterima" class="form-select form-select-sm">
                            <option value="Ya" selected>Ya</option>
                            <option value="Tidak">Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Transaksi Berulang Tanpa Kontrak? (Q)</label>
                        <select name="transaksi_berulang" class="form-select form-select-sm">
                            <option value="Tidak" selected>Tidak</option>
                            <option value="Ya">Ya</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Analisis Kewajaran & Pola (R) <span class="text-danger">*</span></label>
                        <textarea name="analisis_kewajaran" class="form-control form-control-sm" rows="2" placeholder="Uraian analisis kewajaran harga, kebutuhan, dan kelengkapan bukti..." required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Sebab (S)</label>
                        <textarea name="sebab" class="form-control form-control-sm" rows="2" placeholder="Penyebab jika ada selisih / melebihi anggaran..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Dampak (T)</label>
                        <textarea name="dampak" class="form-control form-control-sm" rows="2" placeholder="Dampak pemborosan / temuan kepatuhan..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Referensi Bukti (U) <span class="text-danger">*</span></label>
                        <input type="text" name="referensi_bukti" class="form-control form-control-sm" placeholder="Invoice, kuitansi, faktur pajak" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Simpulan (V) <span class="text-danger">*</span></label>
                        <input type="text" name="simpulan" class="form-control form-control-sm" placeholder="Beban wajar & sah / Terdapat selisih" required>
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
