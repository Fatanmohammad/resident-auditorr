@extends('layouts.app')

@section('content')

<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('onsite.index') }}" style="font-size: 0.85rem; color: #64748b; text-decoration: none;">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kunjungan
    </a>
    <h4 style="font-weight: 700; color: #1e293b; margin: 0.5rem 0 0.3rem;">Buat Kunjungan Onsite Baru</h4>
    <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Isi detail kunjungan. Setelah disimpan, Anda akan diarahkan untuk upload data CBS populasi.</p>
</div>

@if($errors->any())
    <div class="alert alert-danger" style="max-width: 560px; font-size: 0.9rem;">
        <ul style="margin: 0; padding-left: 1.5rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card" style="max-width: 560px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.04); border: 1px solid #e2e8f0;">
    <div class="card-body" style="padding: 2rem;">
        <form action="{{ route('onsite.store') }}" method="POST">
            @csrf

            <div style="margin-bottom: 1.5rem;">
                <label style="font-weight: 600; color: #334155; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                    Unit Tujuan Kunjungan <span style="color: #dc2626;">*</span>
                </label>
                <select name="kode_unit" required class="form-select" style="font-size: 0.9rem; border-radius: 6px;">
                    <option value="">-- Pilih Unit (KCP / KCPLK) --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->unit_code }}" {{ old('kode_unit') == $unit->unit_code ? 'selected' : '' }}>
                            {{ $unit->unit_code }} - {{ $unit->unit_name }} ({{ $unit->unit_type }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="font-weight: 600; color: #334155; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                        Tanggal Mulai <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="date" name="tanggal_mulai" required class="form-control" style="font-size: 0.9rem; border-radius: 6px;"
                        value="{{ old('tanggal_mulai') }}">
                </div>
                <div>
                    <label style="font-weight: 600; color: #334155; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                        Tanggal Selesai <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="date" name="tanggal_selesai" required class="form-control" style="font-size: 0.9rem; border-radius: 6px;"
                        value="{{ old('tanggal_selesai') }}">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; font-weight: 600; padding: 0.6rem; border-radius: 6px; background-color: #1e3a8a; border-color: #1e3a8a;">
                Simpan & Lanjut Upload CBS
            </button>
        </form>
    </div>
</div>

@endsection
