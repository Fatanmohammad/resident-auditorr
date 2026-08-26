@extends('layouts.app')

@section('content')
<div>
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('admin-offsite.kka-index', [$wp->id, $area]) }}" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; background: #fff;">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar KKA
        </a>
    </div>

    <div class="page-header">
        <div class="page-header-title">
            <h1 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--bs-blue-dark);">Detail KKA — {{ $areaLabel }}</h1>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">Kode WP: {{ $wp->kode_wp }} | Unit: {{ $wp->nama_unit ?? $wp->unit->nama_unit }}</p>
        </div>
    </div>

    @if(session('success'))
        <div style="background-color: #d1e7dd; color: #0f5132; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <div><i class="bi bi-check-circle-fill" style="margin-right: 0.5rem;"></i> {{ session('success') }}</div>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #0f5132;">&times;</button>
        </div>
    @endif

    <div class="grid grid-cols-3">
        {{-- KOLOM KIRI: Konteks Transaksi --}}
        <div class="card">
            <div class="card-header" style="background-color: #f8fafc;">
                <div class="card-title" style="font-size: 0.9rem;"><i class="bi bi-file-earmark-text"></i> Konteks Transaksi</div>
            </div>
            <div class="card-body">
                <table style="width: 100%; font-size: 0.85rem; border-collapse: collapse; margin-bottom: 1rem;">
                    <tr><th style="padding: 0.25rem 0; text-align: left; color: var(--text-muted); width: 40%;">Tanggal</th><td style="padding: 0.25rem 0; font-weight: 600;">: {{ \Carbon\Carbon::parse($kka->tanggal_data ?? $kka->created_at)->format('d/m/Y') }}</td></tr>
                    <tr><th style="padding: 0.25rem 0; text-align: left; color: var(--text-muted);">Object ID</th><td style="padding: 0.25rem 0;">: {{ $kka->object_id ?? '-' }}</td></tr>
                    <tr><th style="padding: 0.25rem 0; text-align: left; color: var(--text-muted);">Case ID</th><td style="padding: 0.25rem 0;">: {{ $kka->case_id ?? '-' }}</td></tr>
                    <tr><th style="padding: 0.25rem 0; text-align: left; color: var(--text-muted);">Data Code</th><td style="padding: 0.25rem 0;">: {{ $kka->data_code ?? '-' }}</td></tr>
                    <tr><th style="padding: 0.25rem 0; text-align: left; color: var(--text-muted);">User / Maker</th><td style="padding: 0.25rem 0;">: {{ $kka->user_maker ?? $kka->user_id ?? '-' }}</td></tr>
                    <tr><th style="padding: 0.25rem 0; text-align: left; color: var(--text-muted);">Nominal</th><td style="padding: 0.25rem 0; font-weight: bold; color: var(--bs-blue-dark);">: Rp {{ number_format($kka->nominal ?? 0, 0, ',', '.') }}</td></tr>
                    <tr><th style="padding: 0.25rem 0; text-align: left; color: var(--text-muted);">Risk Awal</th><td style="padding: 0.25rem 0;">: <span class="badge badge-gray">{{ $kka->risk_awal ?? $kka->risk_level ?? 'Low' }}</span></td></tr>
                    <tr><th style="padding: 0.25rem 0; text-align: left; color: var(--text-muted);">Exception Awal</th><td style="padding: 0.25rem 0;">: {{ $kka->exception_awal ? 'Ya' : 'Tidak' }}</td></tr>
                    <tr><th style="padding: 0.25rem 0; text-align: left; color: var(--text-muted);">Jenis Exception</th><td style="padding: 0.25rem 0;">: {{ $kka->jenis_exception_awal ?? '-' }}</td></tr>
                    <tr><th style="padding: 0.25rem 0; text-align: left; color: var(--text-muted);">Sampel Low</th><td style="padding: 0.25rem 0;">: {{ $kka->sampel_low ? 'Ya' : 'Tidak' }}</td></tr>
                </table>

                <div style="border-top: 1px solid var(--border-color); margin: 1rem 0;"></div>

                <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Deskripsi / Narasi</div>
                <div style="background-color: #f8fafc; padding: 0.75rem; border-radius: 6px; font-size: 0.85rem; color: var(--text-main); margin-bottom: 1rem;">
                    {{ $kka->deskripsi ?? $kka->uraian ?? '-' }}
                </div>

                <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Prosedur Uji Panduan Standar</div>
                <div style="background-color: #f8fafc; padding: 0.75rem; border-radius: 6px; font-size: 0.85rem; color: var(--text-main);">
                    <div style="margin-bottom: 0.25rem;"><strong>Tujuan Uji:</strong> {{ $kka->tujuan_uji ?? 'Memastikan keabsahan & otorisasi transaksi.' }}</div>
                    <div style="margin-bottom: 0.25rem;"><strong>Kriteria:</strong> {{ $kka->kriteria ?? 'Sesuai SOP Operasional yang berlaku.' }}</div>
                    <div><strong>Prosedur:</strong> {{ $kka->prosedur_uji ?? 'Telusuri bukti; cocokkan tanggal, nominal, user, dan otorisasi.' }}</div>
                </div>
            </div>
        </div>

        {{-- KOLOM TENGAH: Hasil Kerja RA --}}
        <div class="card" style="border: 1px solid #f59e0b;">
            <div class="card-header" style="background-color: #fef3c7; border-bottom: 1px solid #f59e0b;">
                <div class="card-title" style="font-size: 0.9rem; color: #b45309;"><i class="bi bi-person-workspace"></i> Hasil Kerja RA (Read-Only)</div>
            </div>
            <div class="card-body">
                <div style="margin-bottom: 0.75rem;">
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Bukti / Referensi</label>
                    <input type="text" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem; background-color: #f8fafc;" value="{{ $kka->bukti_referensi ?? '-' }}" readonly>
                </div>

                <div style="margin-bottom: 0.75rem;">
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Hasil Uji</label>
                    <textarea class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem; background-color: #f8fafc; resize: vertical; min-height: 80px;" readonly>{{ $kka->hasil_uji ?? '-' }}</textarea>
                </div>

                <div style="margin-bottom: 0.75rem;">
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Jenis Exception (RA)</label>
                    <input type="text" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem; background-color: #f8fafc;" value="{{ $kka->jenis_exception_ra ?? '-' }}" readonly>
                </div>

                <div style="display: flex; gap: 0.75rem; margin-bottom: 0.75rem;">
                    <div style="flex: 1;">
                        <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Dampak</label>
                        <input type="text" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem; background-color: #f8fafc;" value="{{ $kka->dampak ?? '-' }}" readonly>
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Kemungkinan</label>
                        <input type="text" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem; background-color: #f8fafc;" value="{{ $kka->kemungkinan ?? '-' }}" readonly>
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem; margin-bottom: 0.75rem;">
                    <div style="flex: 1;">
                        <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Skor Risiko</label>
                        <input type="text" class="form-input" style="width: 100%; font-size: 0.85rem; font-weight: bold; padding: 0.4rem 0.75rem; background-color: #f8fafc;" value="{{ $kka->skor_risiko ?? '-' }}" readonly>
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Kategori Final</label>
                        <input type="text" class="form-input" style="width: 100%; font-size: 0.85rem; font-weight: bold; padding: 0.4rem 0.75rem; background-color: #f8fafc;" value="{{ $kka->kategori_risiko_final ?? '-' }}" readonly>
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Critical Trigger</label>
                    <input type="text" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem; background-color: #f8fafc;" value="{{ $kka->critical_trigger ?? '-' }}" readonly>
                </div>

                <div style="border-top: 1px solid var(--border-color); margin: 1rem 0;"></div>

                <div style="margin-bottom: 0.75rem;">
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Klarifikasi Awal / Unit</label>
                    <textarea class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem; background-color: #f8fafc; resize: vertical; min-height: 60px;" readonly>{{ $kka->klarifikasi_unit ?? $kka->klarifikasi_awal ?? '-' }}</textarea>
                </div>

                <div style="display: flex; gap: 0.75rem; margin-bottom: 0.75rem;">
                    <div style="flex: 1;">
                        <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Status Klarifikasi</label>
                        <input type="text" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem; background-color: #f8fafc;" value="{{ $kka->status_klarifikasi ?? '-' }}" readonly>
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Perlu Onsite</label>
                        <input type="text" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem; background-color: #f8fafc;" value="{{ $kka->perlu_onsite ? 'Ya' : 'Tidak' }}" readonly>
                    </div>
                </div>

                <div style="margin-bottom: 0.5rem;">
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Simpulan RA</label>
                    <textarea class="form-input" style="width: 100%; font-size: 0.85rem; font-weight: bold; padding: 0.4rem 0.75rem; background-color: #f8fafc; resize: vertical; min-height: 60px;" readonly>{{ $kka->simpulan_ra ?? '-' }}</textarea>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: Form Reviewer --}}
        <div>
            <form action="{{ route('admin-offsite.kka-update', ['wp' => $wp->id, 'area' => $area, 'kkaId' => $kka->kka_id ?? $kka->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card" style="border: 1px solid #3b82f6;">
                    <div class="card-header" style="background-color: #eff6ff; border-bottom: 1px solid #3b82f6;">
                        <div class="card-title" style="font-size: 0.9rem; color: #1d4ed8;"><i class="bi bi-pencil-square"></i> Catatan Reviewer (Admin)</div>
                    </div>
                    <div class="card-body">
                        <div style="margin-bottom: 1rem;">
                            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-main); display: block; margin-bottom: 0.25rem;">Status Review</label>
                            <select name="status_review" class="form-select" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 2rem 0.4rem 0.75rem;">
                                <option value="Belum Review" {{ old('status_review', $kka->status_review) == 'Belum Review' ? 'selected' : '' }}>Belum Review</option>
                                <option value="Dalam Proses" {{ old('status_review', $kka->status_review) == 'Dalam Proses' ? 'selected' : '' }}>Dalam Proses</option>
                                <option value="Selesai" {{ old('status_review', $kka->status_review) == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="Perlu Perbaikan" {{ old('status_review', $kka->status_review) == 'Perlu Perbaikan' ? 'selected' : '' }}>Perlu Perbaikan</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 1rem;">
                            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-main); display: block; margin-bottom: 0.25rem;">Catatan Reviewer</label>
                            <textarea name="catatan_reviewer" class="form-input" style="width: 100%; font-size: 0.85rem; padding: 0.4rem 0.75rem; resize: vertical; min-height: 150px;" placeholder="Masukkan komentar/catatan tinjauan Anda (misal: 'Perlu dilengkapi bukti tambahan' atau 'Sudah sesuai, disetujui')...">{{ old('catatan_reviewer', $kka->catatan_reviewer) }}</textarea>
                        </div>

                        @if($kka->updated_at)
                            <div style="color: var(--text-muted); font-size: 0.75rem; margin-bottom: 1rem;">
                                <i class="bi bi-clock-history"></i> Terakhir diupdate: {{ \Carbon\Carbon::parse($kka->updated_at)->format('d/m/Y H:i') }}
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 0.5rem; padding: 0.6rem;">
                            <i class="bi bi-save"></i> Simpan Catatan Reviewer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection