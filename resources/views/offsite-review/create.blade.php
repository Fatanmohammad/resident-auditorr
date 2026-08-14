@extends('layouts.app')
@section('title', 'Buat WP Offsite Baru')

@section('content')

<div class="page-header">
    <div class="page-header-title">
        <h1>Buat WP Offsite Baru</h1>
        <p>Kertas Kerja Pemantauan Harian (SOP 02)</p>
    </div>
    <a href="{{ route('offsite-review.index') }}" class="btn btn-outline btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card" style="max-width:600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('offsite-review.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Unit <span style="color:#dc2626;">*</span></label>
                <select name="unit_id" class="form-select" required>
                    <option value="">— Pilih Unit —</option>
                    @foreach($units as $u)
                    <option value="{{ $u->id }}" {{ old('unit_id') == $u->id ? 'selected' : '' }}>
                        {{ $u->unit_name }} ({{ $u->unit_type }})
                    </option>
                    @endforeach
                </select>
                @error('unit_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">RA Pelaksana</label>
                <select name="ra_id" class="form-select">
                    <option value="">— Pilih RA —</option>
                    @foreach($ras as $ra)
                    <option value="{{ $ra->id }}" {{ old('ra_id') == $ra->id ? 'selected' : '' }}>
                        {{ $ra->ra_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2" style="gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Tahun <span style="color:#dc2626;">*</span></label>
                    <input type="number" name="tahun" class="form-input"
                        value="{{ old('tahun', date('Y')) }}" min="2020" max="2099" required>
                    @error('tahun')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Bulan <span style="color:#dc2626;">*</span></label>
                    <select name="bulan" class="form-select" required>
                        @foreach(range(1,12) as $b)
                        <option value="{{ $b }}" {{ old('bulan', date('n')) == $b ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $b)->isoFormat('MMMM') }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Reviewer</label>
                <input type="text" name="reviewer" class="form-input"
                    value="{{ old('reviewer') }}" placeholder="Nama reviewer">
            </div>

            <div class="form-group" style="display:flex;align-items:center;gap:0.75rem;">
                <input type="checkbox" name="validasi_unit" id="validasi_unit" value="1"
                    {{ old('validasi_unit') ? 'checked' : '' }}
                    style="width:16px;height:16px;cursor:pointer;">
                <label for="validasi_unit" class="form-label" style="margin:0;cursor:pointer;">
                    Unit sudah tervalidasi
                </label>
            </div>

            <div style="display:flex;gap:0.75rem;margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Buat WP
                </button>
                <a href="{{ route('offsite-review.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

@endsection
