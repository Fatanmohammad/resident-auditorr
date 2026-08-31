@extends('layouts.app')

@section('content')
<div>
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('admin-offsite.kka-index', [$wp->id, $area]) }}" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; background: #fff;">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar KKA
        </a>
    </div>

    <div class="page-header" style="margin-bottom: 1.5rem;">
        <div class="page-header-title">
            <h1 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--bs-blue-dark);">Detail KKA — {{ $areaLabel }}</h1>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">Kode WP: {{ $wp->kode_wp }} | Unit: {{ $wp->nama_unit ?? $wp->unit->nama_unit ?? '-' }}</p>
        </div>
    </div>

    @if(session('updated_success'))
        <div id="success-alert" style="background-color: #d1e7dd; color: #0f5132; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; transition: opacity 0.5s ease;">
            <div><i class="bi bi-check-circle-fill" style="margin-right: 0.5rem;"></i> {{ session('updated_success') }}</div>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #0f5132;">&times;</button>
        </div>

        <script>
            setTimeout(function() {
                var alertBox = document.getElementById('success-alert');
                if (alertBox) {
                    alertBox.style.opacity = '0';
                    setTimeout(function() { alertBox.style.display = 'none'; }, 500);
                }
            }, 10000);
        </script>
    @endif

    {{-- WRAP SATU FORM AGAR ADMIN BISA PROSES SEMUA FIELD SEKALIGUS --}}
    <form action="{{ route('admin-offsite.kka-update', ['wp' => $wp->id, 'area' => $area, 'kkaId' => $kka->staging_id ?? $kka->kka_id ?? $kka->id]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
            
            {{-- KOLOM KIRI: Konteks Transaksi --}}
            <div class="card">
                <div class="card-header" style="background-color: #f8fafc; border-bottom: 1px solid var(--border-color, #e2e8f0);">
                    <div class="card-title" style="font-size: 0.9rem; font-weight: 600;"><i class="bi bi-file-earmark-text"></i> Konteks Transaksi</div>
                </div>
                <div class="card-body">
                    <table style="width: 100%; font-size: 0.85rem; border-collapse: collapse; margin-bottom: 1rem;">
                        <tr>
                            <th style="padding: 0.35rem 0; text-align: left; color: var(--text-muted); width: 42%;">Tanggal Data</th>
                            <td style="padding: 0.35rem 0; font-weight: 600;">: {{ \Carbon\Carbon::parse($kka->tanggal_data ?? $kka->created_at)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th style="padding: 0.35rem 0; text-align: left; color: var(--text-muted);">Object ID</th>
                            <td style="padding: 0.35rem 0;">: {{ $kka->object_id ?? $kka->id ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th style="padding: 0.35rem 0; text-align: left; color: var(--text-muted);">Case ID</th>
                            <td style="padding: 0.35rem 0;">: {{ $kka->case_id ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th style="padding: 0.35rem 0; text-align: left; color: var(--text-muted);">Data Code</th>
                            <td style="padding: 0.35rem 0;">: {{ $kka->data_code ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th style="padding: 0.35rem 0; text-align: left; color: var(--text-muted);">User / Maker</th>
                            <td style="padding: 0.35rem 0;">: {{ $kka->user_maker ?? $kka->user_id ?? $kka->teller_id ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th style="padding: 0.35rem 0; text-align: left; color: var(--text-muted);">Nominal</th>
                            <td style="padding: 0.35rem 0; font-weight: bold; color: #1e3a8a;">: Rp {{ number_format($kka->nominal ?? $kka->amount ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th style="padding: 0.35rem 0; text-align: left; color: var(--text-muted);">Risk Awal</th>
                            <td style="padding: 0.35rem 0;">: 
                                @php $riskAwal = strtolower($kka->risk_awal ?? $kka->risk_level ?? 'low'); @endphp
                                <span class="badge" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; border-radius: 4px; font-weight: 600;
                                    {{ $riskAwal == 'high' ? 'background:#fee2e2; color:#991b1b;' : ($riskAwal == 'medium' ? 'background:#fef3c7; color:#92400e;' : 'background:#f1f5f9; color:#475569;') }}">
                                    {{ ucfirst($riskAwal) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th style="padding: 0.35rem 0; text-align: left; color: var(--text-muted);">Exception Awal</th>
                            <td style="padding: 0.35rem 0;">: {{ ($kka->exception_awal ?? $kka->is_exception) ? 'Ya' : 'Tidak' }}</td>
                        </tr>
                        <tr>
                            <th style="padding: 0.35rem 0; text-align: left; color: var(--text-muted);">Jenis Exception</th>
                            <td style="padding: 0.35rem 0;">: {{ $kka->jenis_exception_awal ?? $kka->jenis_exception ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th style="padding: 0.35rem 0; text-align: left; color: var(--text-muted);">Sampel Low</th>
                            <td style="padding: 0.35rem 0;">: {{ ($kka->sampel_low ?? false) ? 'Ya' : 'Tidak' }}</td>
                        </tr>
                        @if(isset($kka->no_rekening) || isset($kka->nama_nasabah))
                        <tr>
                            <th style="padding: 0.35rem 0; text-align: left; color: var(--text-muted);">No. Rekening</th>
                            <td style="padding: 0.35rem 0;">: {{ $kka->no_rekening ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th style="padding: 0.35rem 0; text-align: left; color: var(--text-muted);">Nasabah</th>
                            <td style="padding: 0.35rem 0;">: {{ $kka->nama_nasabah ?? '-' }}</td>
                        </tr>
                        @endif
                    </table>

                    <div style="border-top: 1px solid var(--border-color, #e2e8f0); margin: 1rem 0;"></div>

                    <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Deskripsi / Narasi Transaksi</div>
                    <div style="background-color: #f8fafc; padding: 0.75rem; border-radius: 6px; font-size: 0.85rem; color: var(--text-main); margin-bottom: 1rem; border: 1px solid #e2e8f0;">
                        {{ $kka->deskripsi_narasi ?? $kka->deskripsi ?? $kka->uraian ?? $kka->keterangan ?? 'Tidak ada uraian deskripsi.' }}
                    </div>

                    <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Prosedur Uji Panduan Standar</div>
                    <div style="background-color: #f8fafc; padding: 0.75rem; border-radius: 6px; font-size: 0.85rem; color: var(--text-main); border: 1px solid #e2e8f0;">
                        <div style="margin-bottom: 0.35rem;"><strong>Tujuan Uji:</strong> {{ $kka->tujuan_uji ?? 'Memastikan keabsahan, kewajaran, & otorisasi transaksi.' }}</div>
                        <div style="margin-bottom: 0.35rem;"><strong>Kriteria:</strong> {{ $kka->kriteria ?? 'Sesuai Ketentuan Operasional & Juklak yang berlaku.' }}</div>
                        <div><strong>Prosedur:</strong> {{ $kka->prosedur_uji ?? 'Telusuri dokumen pendukung; pastikan kesesuaian nominal, user, dan otorisasi.' }}</div>
                    </div>
                </div>
            </div>

            {{-- KOLOM TENGAH: Hasil Kerja RA (Dapat Di-edit Admin) --}}
            <div class="card" style="border: 1px solid #f59e0b;">
                <div class="card-header" style="background-color: #fef3c7; border-bottom: 1px solid #f59e0b;">
                    <div class="card-title" style="font-size: 0.9rem; font-weight: 600; color: #b45309;"><i class="bi bi-person-workspace"></i> Hasil Kerja RA (Admin Edit Mode)</div>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 0.75rem;">
                        <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Bukti / Referensi</label>
                        <input type="text" name="bukti_referensi" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem;" value="{{ old('bukti_referensi', $kka->bukti_referensi ?? $kka->referensi ?? '') }}">
                    </div>

                    <div style="margin-bottom: 0.75rem;">
                        <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Hasil Uji RA</label>
                        <textarea name="hasil_uji_ra" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem; resize: vertical; min-height: 80px;">{{ old('hasil_uji_ra', $kka->hasil_uji_ra ?? $kka->hasil_uji ?? $kka->catatan_ra ?? '') }}</textarea>
                    </div>

                    <div style="margin-bottom: 0.75rem;">
                        <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Jenis Exception (Hasil Uji RA)</label>
                        <input type="text" name="jenis_exception_ra" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem;" value="{{ old('jenis_exception_ra', $kka->jenis_exception_ra ?? $kka->jenis_exception ?? '') }}">
                    </div>

                    {{-- Matriks Risiko --}}
                    <div style="display: flex; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div style="flex: 1;">
                            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Dampak (Impact)</label>
                            <select name="dampak" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem;">
                                <option value="Low" {{ old('dampak', $kka->dampak ?? '') == 'Low' ? 'selected' : '' }}>Low</option>
                                <option value="Medium" {{ old('dampak', $kka->dampak ?? '') == 'Medium' ? 'selected' : '' }}>Medium</option>
                                <option value="High" {{ old('dampak', $kka->dampak ?? '') == 'High' ? 'selected' : '' }}>High</option>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Kemungkinan (Likelihood)</label>
                            <select name="kemungkinan" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem;">
                                <option value="Low" {{ old('kemungkinan', $kka->kemungkinan ?? '') == 'Low' ? 'selected' : '' }}>Low</option>
                                <option value="Medium" {{ old('kemungkinan', $kka->kemungkinan ?? '') == 'Medium' ? 'selected' : '' }}>Medium</option>
                                <option value="High" {{ old('kemungkinan', $kka->kemungkinan ?? '') == 'High' ? 'selected' : '' }}>High</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div style="flex: 1;">
                            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Skor Risiko</label>
                            <input type="text" name="skor_risiko" class="form-input" style="width: 100%; font-size: 0.85rem; font-weight: bold; padding: 0.4rem 0.75rem;" value="{{ old('skor_risiko', $kka->skor_risiko ?? '') }}">
                        </div>
                        <div style="flex: 1;">
                            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Kategori Final</label>
                            <select name="kategori_risiko_final" class="form-input" style="width: 100%; font-size: 0.85rem; font-weight: bold; padding: 0.4rem 0.75rem;">
                                <option value="Low" {{ old('kategori_risiko_final', $kka->kategori_risiko_final ?? $kka->kategori_final ?? '') == 'Low' ? 'selected' : '' }}>Low</option>
                                <option value="Medium" {{ old('kategori_risiko_final', $kka->kategori_risiko_final ?? $kka->kategori_final ?? '') == 'Medium' ? 'selected' : '' }}>Medium</option>
                                <option value="High" {{ old('kategori_risiko_final', $kka->kategori_risiko_final ?? $kka->kategori_final ?? '') == 'High' ? 'selected' : '' }}>High</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Critical Trigger / Catatan Khusus</label>
                        <input type="text" name="critical_trigger" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem;" value="{{ old('critical_trigger', $kka->critical_trigger ?? '') }}">
                    </div>

                    <div style="border-top: 1px solid var(--border-color, #e2e8f0); margin: 1rem 0;"></div>

                    <div style="margin-bottom: 0.75rem;">
                        <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Klarifikasi Unit / Cabang</label>
                        <textarea name="klarifikasi_unit" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem; resize: vertical; min-height: 60px;">{{ old('klarifikasi_unit', $kka->klarifikasi_unit ?? $kka->klarifikasi_awal ?? '') }}</textarea>
                    </div>

                    <div style="display: flex; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div style="flex: 1;">
                            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Status Klarifikasi</label>
                            <input type="text" name="status_klarifikasi" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem;" value="{{ old('status_klarifikasi', $kka->status_klarifikasi ?? '') }}">
                        </div>
                        <div style="flex: 1;">
                            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Perlu Onsite</label>
                            <select name="perlu_onsite" class="form-input" style="width: 100%; font-size: 0.85rem; font-weight: bold; padding: 0.4rem 0.75rem;">
                                <option value="0" {{ !($kka->perlu_onsite ?? false) ? 'selected' : '' }}>Tidak</option>
                                <option value="1" {{ ($kka->perlu_onsite ?? false) ? 'selected' : '' }}>Ya</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 0.5rem;">
                        <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Simpulan RA</label>
                        <textarea name="simpulan_ra" class="form-input" style="width: 100%; font-size: 0.85rem; font-weight: bold; padding: 0.4rem 0.75rem; resize: vertical; min-height: 60px;">{{ old('simpulan_ra', $kka->simpulan_ra ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: Form Reviewer & Akses Status --}}
            <div>
                <div class="card" style="border: 1px solid #3b82f6;">
                    <div class="card-header" style="background-color: #eff6ff; border-bottom: 1px solid #3b82f6;">
                        <div class="card-title" style="font-size: 0.9rem; font-weight: 600; color: #1d4ed8;"><i class="bi bi-pencil-square"></i> Catatan & Decision Reviewer (Admin)</div>
                    </div>
                    <div class="card-body">
                        {{-- STATUS REVIEW: DAPAT DIUBAH ADMIN --}}
                        <div style="margin-bottom: 1rem;">
                            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-main); display: block; margin-bottom: 0.25rem;">Status Review</label>
                            <select name="status_review" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem; font-weight: 600; color: #334155;">
                                <option value="Belum Review" {{ old('status_review', $kka->status_review ?? '') == 'Belum Review' ? 'selected' : '' }}>Belum Review</option>
                                <option value="Dalam Review" {{ old('status_review', $kka->status_review ?? '') == 'Dalam Review' ? 'selected' : '' }}>Dalam Review</option>
                                <option value="Perlu Klarifikasi" {{ old('status_review', $kka->status_review ?? '') == 'Perlu Klarifikasi' ? 'selected' : '' }}>Perlu Klarifikasi</option>
                                <option value="Selesai Review" {{ old('status_review', $kka->status_review ?? '') == 'Selesai Review' ? 'selected' : '' }}>Selesai Review</option>
                            </select>
                        </div>

                        {{-- CATATAN REVIEWER --}}
                        <div style="margin-bottom: 1rem;">
                            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-main); display: block; margin-bottom: 0.25rem;">Catatan Reviewer</label>
                            <textarea name="catatan_reviewer" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem; resize: vertical; min-height: 150px;" placeholder="Masukkan komentar/catatan tinjauan Anda (misal: 'Perlu dilengkapi bukti tambahan' atau 'Sudah sesuai')...">{{ old('catatan_reviewer', $kka->catatan_reviewer ?? '') }}</textarea>
                            @error('catatan_reviewer')
                                <span style="font-size: 0.75rem; color: #dc2626; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        @if(isset($kka->updated_at) && $kka->updated_at)
                            <div style="color: var(--text-muted); font-size: 0.75rem; margin-bottom: 1rem;">
                                <i class="bi bi-clock-history"></i> Terakhir diupdate: {{ \Carbon\Carbon::parse($kka->updated_at)->timezone('Asia/Makassar')->format('d/m/Y H:i') }}
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 0.5rem; padding: 0.6rem;">
                            <i class="bi bi-save"></i> Simpan & Perbarui KKA
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection