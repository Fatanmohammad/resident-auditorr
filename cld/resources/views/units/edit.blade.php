@extends('layouts.app')
@section('title', isset($unit) ? 'Edit Unit' : 'Tambah Unit')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>{{ isset($unit) ? 'Edit Unit' : 'Tambah Unit' }}</h1>
    </div>
    <a href="{{ route('units.index') }}" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-body">
        <form action="{{ isset($unit) ? route('units.update', $unit) : route('units.store') }}" method="POST">
            @csrf
            @if(isset($unit)) @method('PUT') @endif

            <div class="form-group">
                <label class="form-label">Kode Unit <span style="color:#dc2626;">*</span></label>
                <input type="text" name="unit_code" class="form-input" value="{{ old('unit_code', $unit->unit_code ?? '') }}" {{ isset($unit) ? 'readonly' : '' }} required>
                @error('unit_code')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Nama Unit <span style="color:#dc2626;">*</span></label>
                <input type="text" name="unit_name" class="form-input" value="{{ old('unit_name', $unit->unit_name ?? '') }}" required>
                @error('unit_name')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Tipe Unit <span style="color:#dc2626;">*</span></label>
                <select name="unit_type" class="form-select" required>
                    @foreach(['KC','KCU','KCP','KCPLK','Payment Point'] as $type)
                    <option value="{{ $type }}" {{ old('unit_type', $unit->unit_type ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Kantor Induk</label>
                <input type="text" name="parent_office" class="form-input" value="{{ old('parent_office', $unit->parent_office ?? '') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Wilayah</label>
                <input type="text" name="region" class="form-input" value="{{ old('region', $unit->region ?? '') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Base RA Unit <span style="color:#dc2626;">*</span></label>
                <select name="base_ra_unit" class="form-select">
                    <option value="">-- Pilih Cabang --</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch }}" {{ old('base_ra_unit', $unit->base_ra_unit ?? '') === $branch ? 'selected' : '' }}>{{ $branch }}</option>
                    @endforeach
                </select>
                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.25rem;">Cabang tempat RA yang bertanggung jawab atas unit ini berkedudukan.</div>
            </div>

            <div class="form-group">
                <label class="form-label">Jarak dari Kantor Induk (km)</label>
                <input type="number" name="distance_from_parent_km" class="form-input" step="0.01" min="0" value="{{ old('distance_from_parent_km', $unit->distance_from_parent_km ?? '') }}">
            </div>

            <div class="form-group">
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $unit->is_active ?? true) ? 'checked' : '' }}>
                    <span class="form-label" style="margin:0;">Unit Aktif</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                <i class="bi bi-save"></i> {{ isset($unit) ? 'Simpan Perubahan' : 'Tambah Unit' }}
            </button>
        </form>
    </div>
</div>
@endsection
