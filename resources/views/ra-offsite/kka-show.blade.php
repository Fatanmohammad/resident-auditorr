@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('ra-offsite.kka.index', ['sheet' => str_replace('_', '-', $area)]) }}" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; text-decoration: none; color: #334155; display: inline-flex; align-items: center; gap: 0.5rem;">
            <i class="bi bi-arrow-left"></i> Kembali ke Sheet KKA
        </a>
    </div>

    <div class="page-header" style="margin-bottom: 1.5rem;">
        <div class="page-header-title">
            <h1 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--bs-blue-dark, #1e3a8a);">Review KKA RA — {{ $areaLabel }}</h1>
            <p style="color: var(--text-muted, #64748b); font-size: 0.85rem; margin-top: 0.25rem;">
                Object ID: {{ $kka->object_id ?? $kka->getKey() }} | Case ID / No Ref: {{ $kka->case_id ?? $kka->no_referensi ?? '-' }}
            </p>
        </div>
    </div>

    {{-- Alert Sukses --}}
    @if(session('updated_success'))
        <div id="success-alert" style="background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <div><i class="bi bi-check-circle-fill" style="margin-right: 0.5rem;"></i> {{ session('updated_success') }}</div>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #0f5132;">&times;</button>
        </div>
    @endif

    {{-- Alert Gagal / Error Database --}}
    @if(session('error') || $errors->any())
        <div style="background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
            <div style="font-weight: 600; margin-bottom: 0.25rem;"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terjadi Kesalahan:</div>
            <p style="margin: 0; font-size: 0.85rem;">{{ session('error') }}</p>
            @if($errors->any())
                <ul style="margin: 0.5rem 0 0 1rem; padding: 0; font-size: 0.85rem;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; align-items: start;">
        
        {{-- KOLOM KIRI: Konteks Transaksi (Read-Only) --}}
        <div class="card" style="border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div class="card-header" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.8rem 1.25rem;">
                <div class="card-title" style="font-size: 0.9rem; font-weight: 600; color: #1e293b; margin: 0;">
                    <i class="bi bi-file-earmark-text me-1"></i> Konteks Transaksi
                </div>
            </div>
            <div class="card-body" style="padding: 1.25rem;">
                <table style="width: 100%; font-size: 0.85rem; border-collapse: collapse; margin-bottom: 1rem;">
                    <tr>
                        <th style="padding: 0.35rem 0; text-align: left; color: #64748b; width: 42%;">Tanggal Data</th>
                        <td style="padding: 0.35rem 0; font-weight: 600; color: #0f172a;">
                            : {{ isset($kka->tanggal_data) ? \Carbon\Carbon::parse($kka->tanggal_data)->format('d/m/Y') : ($kka->created_at ? $kka->created_at->format('d/m/Y') : '-') }}
                        </td>
                    </tr>
                    <tr>
                        <th style="padding: 0.35rem 0; text-align: left; color: #64748b;">Kode Unit</th>
                        <td style="padding: 0.35rem 0; color: #0f172a;">: {{ $kka->kode_unit ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th style="padding: 0.35rem 0; text-align: left; color: #64748b;">User / Maker</th>
                        <td style="padding: 0.35rem 0; color: #0f172a;">: {{ $kka->user_maker ?? $kka->user_id ?? $kka->teller_id ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th style="padding: 0.35rem 0; text-align: left; color: #64748b;">Nominal</th>
                        <td style="padding: 0.35rem 0; font-weight: bold; color: #1e3a8a;">
                            : Rp {{ number_format($kka->nominal ?? $kka->amount ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <th style="padding: 0.35rem 0; text-align: left; color: #64748b;">Risk Level Awal</th>
                        <td style="padding: 0.35rem 0;">: 
                            <span style="font-weight: 700; text-transform: uppercase; font-size: 0.75rem;">{{ $kka->risk_awal ?? 'N/A' }}</span>
                        </td>
                    </tr>
                </table>

                <div style="border-top: 1px solid #e2e8f0; margin: 1rem 0;"></div>

                <div style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 0.5rem;">Deskripsi Transaksi</div>
                <div style="background-color: #f8fafc; padding: 0.75rem; border-radius: 6px; font-size: 0.85rem; margin-bottom: 1rem; border: 1px solid #e2e8f0; color: #334155; max-height: 150px; overflow-y: auto;">
                    {{ $kka->deskripsi_narasi ?? $kka->deskripsi ?? $kka->uraian ?? 'Tidak ada uraian.' }}
                </div>

                {{-- Catatan Reviewer dari Admin (Read-Only) --}}
                <div style="background-color: #eff6ff; padding: 0.75rem; border-radius: 6px; border: 1px solid #bfdbfe;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #1d4ed8; margin-bottom: 0.25rem;">
                        <i class="bi bi-chat-left-text me-1"></i> Catatan Reviewer (Admin)
                    </div>
                    <p style="font-size: 0.85rem; color: #1e293b; margin: 0;">{{ $kka->catatan_reviewer ?? 'Belum ada catatan dari Admin.' }}</p>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN --}}
        <div>
            @if(auth()->user()->role === 'ra')
                {{-- TAMPILAN RESIK UNTUK RA (Hanya Menampilkan Hasil Jika Admin Sudah Mengisi) --}}
                @php
                    $hasContent = !empty($kka->hasil_uji) || !empty($kka->simpulan_ra) || !empty($kka->bukti_referensi);
                @endphp

                @if($hasContent)
                    <div class="card" style="border: 1px solid #10b981; border-radius: 8px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="card-header" style="background-color: #ecfdf5; border-bottom: 1px solid #a7f3d0; padding: 0.8rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                            <div class="card-title" style="font-size: 0.9rem; font-weight: 600; color: #047857; margin: 0;">
                                <i class="bi bi-check-circle-fill me-1"></i> Hasil Review & Pengujian Auditor
                            </div>
                            <span style="font-size: 0.75rem; font-weight: 700; background: #d1fae5; color: #065f46; padding: 0.2rem 0.6rem; border-radius: 20px;">
                                {{ $kka->status_review ?? 'Selesai' }}
                            </span>
                        </div>
                        <div class="card-body" style="padding: 1.25rem;">
                            
                            @if($kka->hasil_uji)
                                <div style="margin-bottom: 1rem;">
                                    <div style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 0.35rem;">Hasil Analysis / Pengujian</div>
                                    <div style="background-color: #f8fafc; padding: 0.85rem; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.85rem; color: #1e293b; line-height: 1.5;">
                                        {!! nl2br(e($kka->hasil_uji)) !!}
                                    </div>
                                </div>
                            @endif

                            @if($kka->simpulan_ra)
                                <div style="margin-bottom: 1rem;">
                                    <div style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 0.35rem;">Simpulan Akhir</div>
                                    <div style="background-color: #f0fdf4; padding: 0.85rem; border-radius: 6px; border: 1px solid #bbf7d0; font-size: 0.85rem; font-weight: 600; color: #166534;">
                                        {!! nl2br(e($kka->simpulan_ra)) !!}
                                    </div>
                                </div>
                            @endif

                            @if($kka->bukti_referensi || $kka->dampak || $kka->kemungkinan)
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; background: #f8fafc; padding: 0.75rem; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.8rem;">
                                    @if($kka->bukti_referensi)
                                        <div>
                                            <span style="color: #64748b; display: block; font-size: 0.7rem;">BUKTI/REFERENSI</span>
                                            <strong style="color: #0f172a;">{{ $kka->bukti_referensi }}</strong>
                                        </div>
                                    @endif
                                    @if($kka->dampak)
                                        <div>
                                            <span style="color: #64748b; display: block; font-size: 0.7rem;">DAMPAK</span>
                                            <strong style="color: #0f172a;">{{ $kka->dampak }}</strong>
                                        </div>
                                    @endif
                                    @if($kka->kemungkinan)
                                        <div>
                                            <span style="color: #64748b; display: block; font-size: 0.7rem;">KEMUNGKINAN</span>
                                            <strong style="color: #0f172a;">{{ $kka->kemungkinan }}</strong>
                                        </div>
                                    @endif
                                </div>
                            @endif

                        </div>
                    </div>
                @else
                    {{-- State Kosong untuk RA: Jika Admin Belum Review --}}
                    <div class="card" style="border: 1px dashed #cbd5e1; border-radius: 8px; background: #f8fafc; text-align: center; padding: 2.5rem 1.5rem;">
                        <i class="bi bi-clock-history" style="font-size: 2.5rem; color: #94a3b8; display: block; margin-bottom: 0.5rem;"></i>
                        <h6 style="font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Menunggu Review Admin</h6>
                        <p style="font-size: 0.8rem; color: #64748b; margin: 0;">Pengujian dan simpulan untuk KKA ini belum diinput oleh Admin / Auditor.</p>
                    </div>
                @endif

            @else

                {{-- FORM EDIT UNTUK ADMIN --}}
                <form action="{{ route('ra-offsite.kka.update', ['area' => $area, 'kkaId' => $kka->getKey()]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card" style="border: 1px solid #f59e0b; border-radius: 8px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="card-header" style="background-color: #fef3c7; border-bottom: 1px solid #fcd34d; padding: 0.8rem 1.25rem;">
                            <div class="card-title" style="font-size: 0.9rem; font-weight: 600; color: #b45309; margin: 0;">
                                <i class="bi bi-pencil-square me-1"></i> Input Pengujian Risk Analyst (Admin)
                            </div>
                        </div>
                        <div class="card-body" style="padding: 1.25rem;">
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 0.35rem; color: #334155;">Status Review</label>
                                    <select name="status_review" class="form-select" style="width: 100%; font-size: 0.85rem; padding: 0.45rem 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                                        <option value="Belum Review" {{ old('status_review', $kka->status_review) == 'Belum Review' ? 'selected' : '' }}>Belum Review</option>
                                        <option value="Sudah Review" {{ old('status_review', $kka->status_review) == 'Sudah Review' ? 'selected' : '' }}>Sudah Review</option>
                                        <option value="Perlu Clarification" {{ old('status_review', $kka->status_review) == 'Perlu Clarification' ? 'selected' : '' }}>Perlu Clarification</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 0.35rem; color: #334155;">Bukti / Referensi</label>
                                    <input type="text" name="bukti_referensi" class="form-control" style="width: 100%; font-size: 0.85rem; padding: 0.45rem 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;" value="{{ old('bukti_referensi', $kka->bukti_referensi) }}" placeholder="Contoh: No. Memo / Lampiran Dokumen">
                                </div>
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 0.35rem; color: #334155;">Hasil Uji</label>
                                <textarea name="hasil_uji" class="form-control" style="width: 100%; font-size: 0.85rem; padding: 0.45rem 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1; resize: vertical; min-height: 90px;" placeholder="Tuliskan temuan atau hasil analisis pengujian di sini...">{{ old('hasil_uji', $kka->hasil_uji) }}</textarea>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 0.35rem; color: #334155;">Dampak (Impact)</label>
                                    <input type="text" name="dampak" class="form-control" style="width: 100%; font-size: 0.85rem; padding: 0.45rem 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;" value="{{ old('dampak', $kka->dampak) }}" placeholder="Contoh: High / Moderate / Skala 1-5">
                                </div>
                                <div>
                                    <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 0.35rem; color: #334155;">Kemungkinan (Likelihood)</label>
                                    <input type="text" name="kemungkinan" class="form-control" style="width: 100%; font-size: 0.85rem; padding: 0.45rem 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;" value="{{ old('kemungkinan', $kka->kemungkinan) }}" placeholder="Contoh: High / Low / Skala 1-5">
                                </div>
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 0.35rem; color: #334155;">Simpulan Pengujian</label>
                                <textarea name="simpulan_ra" class="form-control" style="width: 100%; font-size: 0.85rem; font-weight: 600; padding: 0.45rem 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1; resize: vertical; min-height: 70px;" placeholder="Kesimpulan akhir pengujian...">{{ old('simpulan_ra', $kka->simpulan_ra) }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-warning" style="width: 100%; font-weight: 600; padding: 0.6rem; color: #fff; background-color: #f59e0b; border: none; border-radius: 6px; cursor: pointer;">
                                <i class="bi bi-save me-1"></i> Simpan Hasil Pengujian
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>

    </div>
</div>
@endsection