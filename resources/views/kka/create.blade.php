@extends('layouts.app')
@section('title', 'Buat Kartu Kerja Audit')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Buat Kartu Kerja Audit (KKA)</h1>
        <p>Input data pelaksanaan audit — menjadi bukti kehadiran RA</p>
    </div>
    <a href="{{ route('kka.index') }}" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="card" style="max-width: 640px;">
    <div class="card-body">
        <form action="{{ route('kka.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Audit Plan</label>
                <select name="audit_plan_id" class="form-select" required>
                    <option value="">-- Pilih Audit Plan --</option>
                    @foreach($auditPlans as $plan)
                    <option value="{{ $plan->id }}" {{ old('audit_plan_id') == $plan->id ? 'selected' : '' }}>
                        {{ $plan->cabang?->nama_cabang }} — {{ $plan->tahun_periode }}
                    </option>
                    @endforeach
                </select>
                @if($auditPlans->isEmpty())
                <p class="form-error">Tidak ada Audit Plan yang sudah approved. Minta PIMSIE untuk approve terlebih dahulu.</p>
                @endif
            </div>
            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label">Bidang Audit</label>
                    <select name="bidang_audit" class="form-select" required>
                        <option value="">-- Pilih Bidang --</option>
                        <option value="Teller">Teller</option>
                        <option value="Kredit">Kredit</option>
                        <option value="APU">APU (Anti Pencucian Uang)</option>
                        <option value="Operasional">Operasional</option>
                        <option value="Kepatuhan">Kepatuhan</option>
                        <option value="IT">Teknologi Informasi</option>
                        <option value="SDM">SDM</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sub Bidang</label>
                    <input type="text" name="sub_bidang" class="form-input" value="{{ old('sub_bidang') }}" placeholder="Opsional">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Pemeriksaan</label>
                <input type="date" name="tanggal_pemeriksaan" class="form-input" value="{{ old('tanggal_pemeriksaan') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Sample Pemeriksaan</label>
                <textarea name="sample_pemeriksaan" class="form-textarea" placeholder="Deskripsi sample yang diperiksa...">{{ old('sample_pemeriksaan') }}</textarea>
            </div>
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <a href="{{ route('kka.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan KKA</button>
            </div>
        </form>
    </div>
</div>
@endsection
