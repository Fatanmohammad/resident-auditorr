{{-- Area: KKA_DPK_Layanan (22 Kolom: A s.d. V) --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-person-check text-info me-2"></i>KKA DPK Layanan (22 Kolom Excel: A s.d. V)
            </h6>
            <small class="text-muted">
                Pengujian kelengkapan dokumen CIF/rekening, profil risiko KYC/CDD, PEP/High Risk, dan otorisasi pembukaan/perubahan data.
            </small>
        </div>
        @if($canModify && !$isFinal)
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddDpkLayanan">
            <i class="bi bi-plus-circle me-1"></i> + Tambah Sampel DPK Layanan
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
                    <th>No CIF/Rekening (E)</th>
                    <th>Produk / Layanan (F)</th>
                    <th>Nama Nasabah (G)</th>
                    <th>Risk Nasabah (H)</th>
                    <th>PEP / High Risk (I)</th>
                    <th>Dokumen Wajib (J)</th>
                    <th>Dokumen Tersedia (K)</th>
                    <th class="table-info text-info fw-bold">Kelengkapan % (L = K/J)</th>
                    <th>KYC Terakhir (M)</th>
                    <th>Otorisasi Sesuai (N)</th>
                    <th>Profil/Transaksi Sesuai (O)</th>
                    <th>Analisis KYC & Risiko (P)</th>
                    <th>Sebab (Q)</th>
                    <th>Dampak (R)</th>
                    <th>Ref Bukti (S)</th>
                    <th>Simpulan (T)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dpkLayananRows as $row)
                <tr>
                    <td class="text-center text-nowrap">
                        @if($canModify && !$isFinal)
                        <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditDpkLayanan{{ $row->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('risk-trigger.kka.area-row.destroy', ['area' => 'KKA_DPK_Layanan', 'rowId' => $row->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus baris sampel ini?')">
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
                    <td class="font-monospace fw-semibold">{{ $row->no_cif_rekening }}</td>
                    <td>{{ $row->produk_layanan }}</td>
                    <td class="fw-semibold">{{ $row->nama_nasabah }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $row->risk_nasabah }}</span></td>
                    <td><span class="badge {{ $row->pep_high_risk == 'Ya' ? 'bg-danger' : 'bg-light text-dark border' }}">{{ $row->pep_high_risk }}</span></td>
                    <td class="text-center">{{ $row->dokumen_wajib }}</td>
                    <td class="text-center">{{ $row->dokumen_tersedia }}</td>
                    <td class="text-center font-monospace fw-bold {{ ($row->kelengkapan_persen ?? 0) < 100 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($row->kelengkapan_persen ?? 0, 1) }}%
                    </td>
                    <td>{{ $row->kyc_terakhir }}</td>
                    <td><span class="badge {{ $row->otorisasi_sesuai == 'Ya' ? 'bg-success' : 'bg-danger' }}">{{ $row->otorisasi_sesuai }}</span></td>
                    <td><span class="badge {{ $row->profil_transaksi_sesuai == 'Ya' ? 'bg-success' : 'bg-danger' }}">{{ $row->profil_transaksi_sesuai }}</span></td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->analisis_kyc_risiko }}</td>
                    <td class="text-wrap" style="max-width: 160px;">{{ $row->sebab }}</td>
                    <td class="text-wrap" style="max-width: 160px;">{{ $row->dampak }}</td>
                    <td>{{ $row->referensi_bukti }}</td>
                    <td class="text-wrap" style="max-width: 200px;">{{ $row->simpulan }}</td>
                </tr>

                {{-- Modal Edit DPK Layanan --}}
                @if($canModify && !$isFinal)
                <div class="modal fade" id="modalEditDpkLayanan{{ $row->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <form action="{{ route('risk-trigger.kka.area-row.update', ['area' => 'KKA_DPK_Layanan', 'rowId' => $row->id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold">Edit Baris Pengujian DPK Layanan #{{ $row->id }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">No CIF / Rekening (E)</label>
                                        <input type="text" name="no_cif_rekening" class="form-control form-control-sm" value="{{ $row->no_cif_rekening }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Produk / Layanan (F)</label>
                                        <input type="text" name="produk_layanan" class="form-control form-control-sm" value="{{ $row->produk_layanan }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nama Nasabah (G)</label>
                                        <input type="text" name="nama_nasabah" class="form-control form-control-sm" value="{{ $row->nama_nasabah }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Risk Nasabah (H)</label>
                                        <select name="risk_nasabah" class="form-select form-select-sm">
                                            <option value="Low" {{ $row->risk_nasabah == 'Low' ? 'selected' : '' }}>Low</option>
                                            <option value="Medium" {{ $row->risk_nasabah == 'Medium' ? 'selected' : '' }}>Medium</option>
                                            <option value="High" {{ $row->risk_nasabah == 'High' ? 'selected' : '' }}>High</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">PEP / High Risk? (I)</label>
                                        <select name="pep_high_risk" class="form-select form-select-sm">
                                            <option value="Tidak" {{ $row->pep_high_risk == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                            <option value="Ya" {{ $row->pep_high_risk == 'Ya' ? 'selected' : '' }}>Ya</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Dokumen Wajib (J)</label>
                                        <input type="number" name="dokumen_wajib" class="form-control form-control-sm" value="{{ $row->dokumen_wajib }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Dokumen Tersedia (K)</label>
                                        <input type="number" name="dokumen_tersedia" class="form-control form-control-sm" value="{{ $row->dokumen_tersedia }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">KYC Terakhir (M)</label>
                                        <input type="text" name="kyc_terakhir" class="form-control form-control-sm" value="{{ $row->kyc_terakhir }}" placeholder="Bulan/Tahun">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Otorisasi Sesuai? (N)</label>
                                        <select name="otorisasi_sesuai" class="form-select form-select-sm">
                                            <option value="Ya" {{ $row->otorisasi_sesuai == 'Ya' ? 'selected' : '' }}>Ya</option>
                                            <option value="Tidak" {{ $row->otorisasi_sesuai == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Profil/Transaksi Sesuai? (O)</label>
                                        <select name="profil_transaksi_sesuai" class="form-select form-select-sm">
                                            <option value="Ya" {{ $row->profil_transaksi_sesuai == 'Ya' ? 'selected' : '' }}>Ya</option>
                                            <option value="Tidak" {{ $row->profil_transaksi_sesuai == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Analisis KYC & Risiko (P)</label>
                                        <textarea name="analisis_kyc_risiko" class="form-control form-control-sm" rows="2" required>{{ $row->analisis_kyc_risiko }}</textarea>
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
                        Belum ada sampel pengujian CIF/rekening pada KKA DPK Layanan untuk Visit ID ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Add DPK Layanan --}}
@if($canModify && !$isFinal)
<div class="modal fade" id="modalAddDpkLayanan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('risk-trigger.kka.area-row.store', $kka->id) }}" method="POST">
                @csrf
                <input type="hidden" name="kka_area" value="KKA_DPK_Layanan">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Tambah Sampel Pengujian DPK Layanan (KKA_DPK_Layanan)</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">No CIF / Rekening (E) <span class="text-danger">*</span></label>
                        <input type="text" name="no_cif_rekening" class="form-control form-control-sm" placeholder="Contoh: CIF-003-88291" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Produk / Layanan (F) <span class="text-danger">*</span></label>
                        <input type="text" name="produk_layanan" class="form-control form-control-sm" placeholder="Tabungan / Giro / Deposito" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nama Nasabah (G)</label>
                        <input type="text" name="nama_nasabah" class="form-control form-control-sm" placeholder="Nama lengkap nasabah">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Risk Nasabah (H)</label>
                        <select name="risk_nasabah" class="form-select form-select-sm">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">PEP / High Risk? (I)</label>
                        <select name="pep_high_risk" class="form-select form-select-sm">
                            <option value="Tidak" selected>Tidak</option>
                            <option value="Ya">Ya</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Dokumen Wajib (J) <span class="text-danger">*</span></label>
                        <input type="number" name="dokumen_wajib" class="form-control form-control-sm" value="5" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Dokumen Tersedia (K) <span class="text-danger">*</span></label>
                        <input type="number" name="dokumen_tersedia" class="form-control form-control-sm" value="5" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">KYC Terakhir (M)</label>
                        <input type="text" name="kyc_terakhir" class="form-control form-control-sm" placeholder="Bln/Thn pengkinian">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Otorisasi Sesuai? (N)</label>
                        <select name="otorisasi_sesuai" class="form-select form-select-sm">
                            <option value="Ya" selected>Ya</option>
                            <option value="Tidak">Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Profil/Transaksi Sesuai? (O)</label>
                        <select name="profil_transaksi_sesuai" class="form-select form-select-sm">
                            <option value="Ya" selected>Ya</option>
                            <option value="Tidak">Tidak</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Analisis KYC & Risiko (P) <span class="text-danger">*</span></label>
                        <textarea name="analisis_kyc_risiko" class="form-control form-control-sm" rows="2" placeholder="Uraian verifikasi identitas dan keabsahan dokumen..." required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Sebab (Q)</label>
                        <textarea name="sebab" class="form-control form-control-sm" rows="2" placeholder="Akar penyebab kekurangan dokumen..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Dampak (R)</label>
                        <textarea name="dampak" class="form-control form-control-sm" rows="2" placeholder="Potensi risiko kepatuhan/APU-PPT..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Referensi Bukti (S) <span class="text-danger">*</span></label>
                        <input type="text" name="referensi_bukti" class="form-control form-control-sm" placeholder="Berkas formulir CIF & identitas" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Simpulan (T) <span class="text-danger">*</span></label>
                        <input type="text" name="simpulan" class="form-control form-control-sm" placeholder="Dokumen lengkap / Belum lengkap" required>
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
