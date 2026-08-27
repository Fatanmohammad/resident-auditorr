@extends('layouts.app')
@section('title', 'Buat Audit Plan')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Buat Audit Plan</h1>
        <p>Penyusunan jadwal audit RA oleh PIMSIE</p>
    </div>
    <a href="{{ route('audit-plan.index') }}" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="card" style="max-width: 640px;">
    <div class="card-body">
        <form action="{{ route('audit-plan.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Cabang Target Audit</label>
                <select name="cabang_id" class="form-select" required>
                    <option value="">-- Pilih Cabang --</option>
                    @foreach($cabangs as $cabang)
                    <option value="{{ $cabang->id }}" {{ old('cabang_id') == $cabang->id ? 'selected' : '' }}>
                        {{ $cabang->nama_cabang }} ({{ strtoupper(str_replace('_',' ',$cabang->tipe)) }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Resident Auditor (RA)</label>
                <select name="ra_user_id" class="form-select" required>
                    <option value="">-- Pilih RA --</option>
                    @foreach($raUsers as $ra)
                    <option value="{{ $ra->id }}" {{ old('ra_user_id') == $ra->id ? 'selected' : '' }}>
                        {{ $ra->name }} — {{ $ra->cabang?->nama_cabang ?? 'Tanpa Cabang' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tahun Periode</label>
                <input type="number" name="tahun_periode" class="form-input" value="{{ old('tahun_periode', date('Y')) }}" min="2020" max="2099" required>
            </div>
            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label">Jadwal Mulai</label>
                    <input type="date" name="jadwal_mulai" class="form-input" value="{{ old('jadwal_mulai') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Jadwal Selesai</label>
                    <input type="date" name="jadwal_selesai" class="form-input" value="{{ old('jadwal_selesai') }}" required>
                </div>
            </div>
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 0.5rem;">
                <a href="{{ route('audit-plan.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan Audit Plan</button>
            </div>
        </form>
    </div>
</div>
@endsection
