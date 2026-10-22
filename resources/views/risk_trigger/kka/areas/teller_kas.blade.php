{{-- Area: KKA_Teller_Kas (24 Kolom: A s.d. X) --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-cash-stack text-success me-2"></i>KKA Teller Kas (24 Kolom Excel: A s.d. X)
            </h6>
            <small class="text-muted">
                Pengujian transaksi kas, selisih voucher/bukti kas, reversal, dan kepatuhan limit teller.
            </small>
        </div>
        @if($canModify && !$isFinal)
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddTellerKas">
            <i class="bi bi-plus-circle me-1"></i> + Tambah Sampel Teller Kas
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
                    <th>No Referensi (E)</th>
                    <th>Tanggal (F)</th>
                    <th>Redaksi (G)</th>
                    <th>D/K (H)</th>
                    <th>Nominal (I)</th>
                    <th>User ID (J)</th>
                    <th>Jenis Transaksi (K)</th>
                    <th>Nama Transaksi (L)</th>
                    <th>Nominal Pasangan (M)</th>
                    <th>Bukti Kas/Voucher (N)</th>
                    <th class="table-primary text-primary fw-bold">Selisih (O = N - M)</th>
                    <th>Reversal Match (P)</th>
                    <th>Otorisasi Sesuai (Q)</th>
                    <th>Analisis Pola (R)</th>
                    <th>Sebab (S)</th>
                    <th>Dampak (T)</th>
                    <th>Ref Bukti (U)</th>
                    <th>Simpulan (V)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tellerKasRows as $row)
                <tr>
                    <td class="text-center text-nowrap">
                        @if($canModify && !$isFinal)
                        <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditTellerKas{{ $row->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('risk-trigger.kka.area-row.destroy', ['area' => 'KKA_Teller_Kas', 'rowId' => $row->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus baris sampel ini?')">
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
                    <td class="font-monospace fw-semibold">{{ $row->no_referensi }}</td>
                    <td class="text-nowrap">{{ $row->tanggal_transaksi ? \Carbon\Carbon::parse($row->tanggal_transaksi)->format('d/m/Y') : '-' }}</td>
                    <td class="text-wrap" style="max-width: 180px;">{{ $row->redaksi }}</td>
                    <td class="text-center"><span class="badge bg-light text-dark border">{{ $row->d_k }}</span></td>
                    <td class="font-monospace text-end">Rp {{ number_format($row->nominal ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $row->user_id_transaksi }}</td>
                    <td>{{ $row->jenis_transaksi }}</td>
                    <td>{{ $row->nama_transaksi }}</td>
                    <td class="font-monospace text-end">Rp {{ number_format($row->nominal_pasangan ?? 0, 0, ',', '.') }}</td>
                    <td class="font-monospace text-end">Rp {{ number_format($row->bukti_kas_voucher ?? 0, 0, ',', '.') }}</td>
                    <td class="font-monospace text-end fw-bold {{ abs($row->selisih ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($row->selisih ?? 0, 0, ',', '.') }}
                    </td>
                    <td><span class="badge {{ $row->reversal_match == 'Ya' ? 'bg-success' : 'bg-danger' }}">{{ $row->reversal_match }}</span></td>
                    <td><span class="badge {{ $row->otorisasi_sesuai == 'Ya' ? 'bg-success' : 'bg-danger' }}">{{ $row->otorisasi_sesuai }}</span></td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->analisis_pola }}</td>
                    <td class="text-wrap" style="max-width: 160px;">{{ $row->sebab }}</td>
                    <td class="text-wrap" style="max-width: 160px;">{{ $row->dampak }}</td>
                    <td>{{ $row->referensi_bukti }}</td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->simpulan }}</td>
                </tr>

                {{-- Modal Edit Teller Kas --}}
                @if($canModify && !$isFinal)
                <div class="modal fade" id="modalEditTellerKas{{ $row->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <form action="{{ route('risk-trigger.kka.area-row.update', ['area' => 'KKA_Teller_Kas', 'rowId' => $row->id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold">Edit Baris Pengujian Teller Kas #{{ $row->id }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">No Referensi / Arsip (E)</label>
                                        <input type="text" name="no_referensi" class="form-control form-control-sm" value="{{ $row->no_referensi }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Tanggal Transaksi (F)</label>
                                        <input type="date" name="tanggal_transaksi" class="form-control form-control-sm" value="{{ $row->tanggal_transaksi ? \Carbon\Carbon::parse($row->tanggal_transaksi)->format('Y-m-d') : '' }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Redaksi Transaksi (G)</label>
                                        <input type="text" name="redaksi" class="form-control form-control-sm" value="{{ $row->redaksi }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">D/K (H)</label>
                                        <select name="d_k" class="form-select form-select-sm">
                                            <option value="D" {{ $row->d_k == 'D' ? 'selected' : '' }}>D (Debet)</option>
                                            <option value="K" {{ $row->d_k == 'K' ? 'selected' : '' }}>K (Kredit)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Nominal (I)</label>
                                        <input type="number" step="0.01" name="nominal" class="form-control form-control-sm" value="{{ $row->nominal }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">User ID Teller (J)</label>
                                        <input type="text" name="user_id_transaksi" class="form-control form-control-sm" value="{{ $row->user_id_transaksi }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Jenis Transaksi (K)</label>
                                        <input type="text" name="jenis_transaksi" class="form-control form-control-sm" value="{{ $row->jenis_transaksi }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nama Transaksi (L)</label>
                                        <input type="text" name="nama_transaksi" class="form-control form-control-sm" value="{{ $row->nama_transaksi }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nominal Pasangan (M)</label>
                                        <input type="number" step="0.01" name="nominal_pasangan" class="form-control form-control-sm" value="{{ $row->nominal_pasangan }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Bukti Kas / Voucher (N)</label>
                                        <input type="number" step="0.01" name="bukti_kas_voucher" class="form-control form-control-sm" value="{{ $row->bukti_kas_voucher }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Reversal Match? (P)</label>
                                        <select name="reversal_match" class="form-select form-select-sm">
                                            <option value="Ya" {{ $row->reversal_match == 'Ya' ? 'selected' : '' }}>Ya</option>
                                            <option value="Tidak" {{ $row->reversal_match == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Otorisasi Sesuai? (Q)</label>
                                        <select name="otorisasi_sesuai" class="form-select form-select-sm">
                                            <option value="Ya" {{ $row->otorisasi_sesuai == 'Ya' ? 'selected' : '' }}>Ya</option>
                                            <option value="Tidak" {{ $row->otorisasi_sesuai == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Analisis Pola / Perhitungan (R)</label>
                                        <textarea name="analisis_pola" class="form-control form-control-sm" rows="2" required>{{ $row->analisis_pola }}</textarea>
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
                        Belum ada sampel transaksi pada KKA Teller Kas untuk Visit ID ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Add Teller Kas --}}
@if($canModify && !$isFinal)
<div class="modal fade" id="modalAddTellerKas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('risk-trigger.kka.area-row.store', $kka->id) }}" method="POST">
                @csrf
                <input type="hidden" name="kka_area" value="KKA_Teller_Kas">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Tambah Sampel Pengujian Teller Kas (KKA_Teller_Kas)</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">No Referensi / Arsip (E) <span class="text-danger">*</span></label>
                        <input type="text" name="no_referensi" class="form-control form-control-sm" placeholder="Contoh: REF-202610-001" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Tanggal Transaksi (F) <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_transaksi" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Redaksi Transaksi (G) <span class="text-danger">*</span></label>
                        <input type="text" name="redaksi" class="form-control form-control-sm" placeholder="Keterangan transaksi slip/teller" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">D/K (H)</label>
                        <select name="d_k" class="form-select form-select-sm">
                            <option value="D">D (Debet)</option>
                            <option value="K">K (Kredit)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Nominal (I) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="nominal" class="form-control form-control-sm" placeholder="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">User ID Teller (J)</label>
                        <input type="text" name="user_id_transaksi" class="form-control form-control-sm" placeholder="ID user/teller">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Jenis Transaksi (K)</label>
                        <input type="text" name="jenis_transaksi" class="form-control form-control-sm" placeholder="Setoran/Tarikan/Pindah Buku">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nama Transaksi (L)</label>
                        <input type="text" name="nama_transaksi" class="form-control form-control-sm" placeholder="Nama transaksi">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nominal Pasangan (M)</label>
                        <input type="number" step="0.01" name="nominal_pasangan" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Bukti Kas / Voucher (N)</label>
                        <input type="number" step="0.01" name="bukti_kas_voucher" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Reversal Match? (P)</label>
                        <select name="reversal_match" class="form-select form-select-sm">
                            <option value="Ya" selected>Ya</option>
                            <option value="Tidak">Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Otorisasi Sesuai? (Q)</label>
                        <select name="otorisasi_sesuai" class="form-select form-select-sm">
                            <option value="Ya" selected>Ya</option>
                            <option value="Tidak">Tidak</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Analisis Pola / Perhitungan (R) <span class="text-danger">*</span></label>
                        <textarea name="analisis_pola" class="form-control form-control-sm" rows="2" placeholder="Uraian hasil analisis dan pengujian..." required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Sebab (S)</label>
                        <textarea name="sebab" class="form-control form-control-sm" rows="2" placeholder="Akar penyebab jika ada selisih..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Dampak (T)</label>
                        <textarea name="dampak" class="form-control form-control-sm" rows="2" placeholder="Dampak finansial / operasional..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Referensi Bukti (U) <span class="text-danger">*</span></label>
                        <input type="text" name="referensi_bukti" class="form-control form-control-sm" placeholder="Lampiran voucher / printout" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Simpulan (V) <span class="text-danger">*</span></label>
                        <input type="text" name="simpulan" class="form-control form-control-sm" placeholder="Sesuai ketentuan / Terdapat selisih" required>
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
