@extends('layouts.app')

@section('content')

<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('onsite.index') }}" style="font-size: 0.85rem; color: #64748b; text-decoration: none;">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kunjungan
    </a>
    <h4 style="font-weight: 700; color: #1e293b; margin: 0.5rem 0 0.3rem;">Surat Permintaan Data</h4>
    <p style="color: #64748b; font-size: 0.9rem; margin: 0;">
        {{ $visit->kode_unit }} — {{ $visit->nama_unit }} &nbsp;|&nbsp;
        {{ \Carbon\Carbon::parse($visit->tanggal_mulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($visit->tanggal_selesai)->format('d M Y') }}
    </p>
</div>

@if($errors->any())
    <div class="alert alert-danger" style="max-width: 700px; font-size: 0.9rem;">
        <ul style="margin: 0; padding-left: 1.5rem;">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<form action="{{ route('onsite.permintaan.store', $visit->id) }}" method="POST">
@csrf

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; max-width: 900px;">

    {{-- Kolom kiri: Info surat --}}
    <div class="card" style="border-radius: 10px; border: 1px solid #e2e8f0;">
        <div class="card-body" style="padding: 1.5rem;">
            <div style="font-weight: 700; color: #1e293b; margin-bottom: 1rem; font-size: 0.95rem;">
                <i class="bi bi-file-earmark-text me-2 text-primary"></i>Informasi Surat
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="font-weight: 600; font-size: 0.85rem; color: #334155; display: block; margin-bottom: 0.4rem;">
                    Kepada (Nama Pejabat) <span style="color: #dc2626;">*</span>
                </label>
                <input type="text" name="kepada" class="form-control form-control-sm"
                    value="{{ old('kepada', $existing?->kepada) }}"
                    placeholder="Contoh: Bapak Ahmad Fauzi" required>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="font-weight: 600; font-size: 0.85rem; color: #334155; display: block; margin-bottom: 0.4rem;">
                    Jabatan
                </label>
                <input type="text" name="jabatan_kepada" class="form-control form-control-sm"
                    value="{{ old('jabatan_kepada', $existing?->jabatan_kepada) }}"
                    placeholder="Contoh: Pimpinan KCP Toili">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="font-weight: 600; font-size: 0.85rem; color: #334155; display: block; margin-bottom: 0.4rem;">
                    Tanggal Surat <span style="color: #dc2626;">*</span>
                </label>
                <input type="date" name="tanggal_surat" class="form-control form-control-sm"
                    value="{{ old('tanggal_surat', $existing?->tanggal_surat?->format('Y-m-d') ?? date('Y-m-d')) }}" required>
            </div>

            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #334155; display: block; margin-bottom: 0.4rem;">
                    Catatan Tambahan
                </label>
                <textarea name="catatan" rows="3" class="form-control form-control-sm"
                    placeholder="Catatan khusus jika ada...">{{ old('catatan', $existing?->catatan) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Kolom kanan: Daftar dokumen --}}
    <div class="card" style="border-radius: 10px; border: 1px solid #e2e8f0;">
        <div class="card-body" style="padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">
                    <i class="bi bi-list-check me-2 text-primary"></i>Daftar Dokumen Diminta
                </div>
                <button type="button" id="btn-tambah" class="btn btn-sm"
                    style="background: #1e3a8a; color: white; font-size: 0.78rem; padding: 0.25rem 0.7rem; border-radius: 5px;">
                    <i class="bi bi-plus"></i> Tambah
                </button>
            </div>

            <div id="dokumen-list">
                @if($existing && $existing->items->count())
                    @foreach($existing->items as $i => $item)
                    <div class="dokumen-row" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;">
                        <span style="font-size: 0.8rem; color: #94a3b8; min-width: 18px;">{{ $i+1 }}.</span>
                        <input type="text" name="dokumen[]" class="form-control form-control-sm"
                            value="{{ $item->jenis_dokumen }}" placeholder="Nama dokumen" required>
                        <input type="text" name="periode_data[]" class="form-control form-control-sm"
                            style="max-width: 110px;" value="{{ $item->periode_data }}" placeholder="Periode">
                        <button type="button" class="btn-hapus" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0 0.3rem;">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                    @endforeach
                @else
                    @foreach($dokumenDefault as $i => $dok)
                    <div class="dokumen-row" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;">
                        <span style="font-size: 0.8rem; color: #94a3b8; min-width: 18px;">{{ $i+1 }}.</span>
                        <input type="text" name="dokumen[]" class="form-control form-control-sm"
                            value="{{ $dok }}" placeholder="Nama dokumen" required>
                        <input type="text" name="periode_data[]" class="form-control form-control-sm"
                            style="max-width: 110px;" placeholder="Periode">
                        <button type="button" class="btn-hapus" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0 0.3rem;">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                    @endforeach
                @endif
            </div>

            <p style="font-size: 0.78rem; color: #94a3b8; margin-top: 0.5rem; margin-bottom: 0;">
                Isi kolom periode jika perlu (contoh: September 2026)
            </p>
        </div>
    </div>

</div>

<div style="max-width: 900px; margin-top: 1.5rem; display: flex; gap: 1rem;">
    <button type="submit" class="btn btn-primary" style="font-weight: 600; padding: 0.6rem 2rem; background-color: #1e3a8a; border-color: #1e3a8a;">
        <i class="bi bi-printer me-1"></i> Simpan & Cetak Surat
    </button>
    <a href="{{ route('onsite.index') }}" class="btn btn-outline-secondary" style="padding: 0.6rem 1.5rem;">
        Batal
    </a>
</div>

</form>

@endsection

@push('scripts')
<script>
    // Tambah baris dokumen baru
    document.getElementById('btn-tambah').addEventListener('click', function () {
        const list = document.getElementById('dokumen-list');
        const count = list.querySelectorAll('.dokumen-row').length + 1;
        const row = document.createElement('div');
        row.className = 'dokumen-row';
        row.style.cssText = 'display:flex; gap:0.5rem; margin-bottom:0.5rem; align-items:center;';
        row.innerHTML = `
            <span style="font-size:0.8rem; color:#94a3b8; min-width:18px;">${count}.</span>
            <input type="text" name="dokumen[]" class="form-control form-control-sm" placeholder="Nama dokumen" required>
            <input type="text" name="periode_data[]" class="form-control form-control-sm" style="max-width:110px;" placeholder="Periode">
            <button type="button" class="btn-hapus" style="background:none; border:none; color:#dc2626; cursor:pointer; padding:0 0.3rem;">
                <i class="bi bi-x-circle"></i>
            </button>`;
        list.appendChild(row);
        row.querySelector('input').focus();
    });

    // Hapus baris
    document.getElementById('dokumen-list').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-hapus');
        if (btn) btn.closest('.dokumen-row').remove();
    });
</script>
@endpush
