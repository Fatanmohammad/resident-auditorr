@extends('layouts.app')
@section('title', 'Catat Temuan Audit')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Catat Temuan Audit</h1>
        <p>Input temuan hasil pemeriksaan KKA</p>
    </div>
    <a href="{{ route('temuan.index') }}" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="card" style="max-width: 720px;">
    <div class="card-body">
        <form action="{{ route('temuan.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Kartu Kerja Audit (KKA)</label>
                <select name="kka_id" class="form-select" required>
                    <option value="">-- Pilih KKA --</option>
                    @foreach($kkas as $kka)
                    <option value="{{ $kka->id }}" {{ (old('kka_id') ?? request('kka_id')) == $kka->id ? 'selected' : '' }}>
                        {{ $kka->auditPlan?->cabang?->nama_cabang }} — {{ $kka->bidang_audit }} ({{ \Carbon\Carbon::parse($kka->tanggal_pemeriksaan)->format('d M Y') }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label">Judul Temuan</label>
                    <input type="text" name="judul_temuan" class="form-input" value="{{ old('judul_temuan') }}" required placeholder="Judul singkat temuan">
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="signifikan" {{ old('kategori') === 'signifikan' ? 'selected' : '' }}>Signifikan</option>
                        <option value="berulang" {{ old('kategori') === 'berulang' ? 'selected' : '' }}>Berulang</option>
                        <option value="operasional" {{ old('kategori') === 'operasional' ? 'selected' : '' }}>Operasional</option>
                        <option value="kepatuhan" {{ old('kategori') === 'kepatuhan' ? 'selected' : '' }}>Kepatuhan</option>
                        <option value="lainnya" {{ old('kategori') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Kondisi (Fakta yang ditemukan)</label>
                <textarea name="kondisi" class="form-textarea" required placeholder="Deskripsikan kondisi yang ditemukan...">{{ old('kondisi') }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Kriteria (Standar yang seharusnya)</label>
                <textarea name="kriteria" class="form-textarea" required placeholder="Standar/aturan yang berlaku...">{{ old('kriteria') }}</textarea>
            </div>
            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label">Sebab</label>
                    <textarea name="sebab" class="form-textarea" required placeholder="Penyebab terjadinya temuan...">{{ old('sebab') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Akibat</label>
                    <textarea name="akibat" class="form-textarea" required placeholder="Dampak dari temuan...">{{ old('akibat') }}</textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Rekomendasi RA</label>
                <textarea name="rekomendasi_ra" class="form-textarea" required placeholder="Rekomendasi tindak lanjut...">{{ old('rekomendasi_ra') }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Target Selesai Tindak Lanjut</label>
                <input type="date" name="target_selesai_tl" class="form-input" value="{{ old('target_selesai_tl') }}">
            </div>
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <a href="{{ route('temuan.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan Temuan</button>
            </div>
        </form>
    </div>
</div>
@endsection
