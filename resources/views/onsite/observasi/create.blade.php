@extends('layouts.app')

@section('content')

<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('onsite.index') }}" style="font-size: 0.85rem; color: #64748b; text-decoration: none;">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kunjungan
    </a>
    <h4 style="font-weight: 700; color: #1e293b; margin: 0.5rem 0 0.3rem;">
        KKA Observasi Lingkungan — {{ $visit->kode_unit }}
    </h4>
    <p style="color: #64748b; font-size: 0.9rem; margin: 0;">{{ $visit->nama_unit }}</p>
</div>

@if(session('error'))
    <div class="alert alert-danger" style="font-size: 0.9rem;">
        <i class="bi bi-exclamation-circle-fill me-1"></i> {{ session('error') }}
    </div>
@endif

<div class="card" style="max-width: 520px; border-radius: 10px; border: 1px solid #e2e8f0;">
    <div class="card-body" style="padding: 1.5rem;">

        @if($observasi)
        <div class="alert alert-warning" style="font-size: 0.85rem; margin-bottom: 1.25rem;">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            Checklist sudah ada ({{ $observasi->items()->count() }} item). Membuat ulang akan <strong>menghapus semua data yang sudah diisi</strong>.
        </div>
        @endif

        <form action="{{ route('onsite.observasi.store', $visit) }}" method="POST">
            @csrf

            <div style="margin-bottom: 1rem;">
                <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">
                    Tanggal Observasi <span style="color: #dc2626;">*</span>
                </label>
                <input type="date" name="tanggal_observasi" class="form-control form-control-sm"
                    value="{{ $observasi?->tanggal_observasi ?? $visit->tanggal_mulai }}" required>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">
                    RA Pelaksana <span style="color: #dc2626;">*</span>
                </label>
                <input type="text" name="ra_pelaksana" class="form-control form-control-sm"
                    value="{{ $observasi?->ra_pelaksana ?? auth()->user()->name }}"
                    placeholder="Nama RA yang melakukan observasi" required>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">
                    Jumlah ATM di Unit Ini <span style="color: #dc2626;">*</span>
                </label>
                <select name="jumlah_atm" class="form-select form-select-sm" required>
                    @for($i = 0; $i <= 10; $i++)
                        <option value="{{ $i }}" {{ ($observasi?->jumlah_atm ?? 0) == $i ? 'selected' : '' }}>
                            {{ $i == 0 ? 'Tidak ada ATM' : $i . ' ATM' }}
                        </option>
                    @endfor
                </select>
                <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 0.3rem;">
                    Sistem akan generate 20 item gedung + 3 item per ATM secara otomatis.
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-sm w-100"
                style="background: #1e3a8a; border-color: #1e3a8a;">
                <i class="bi bi-list-check me-1"></i>
                {{ $observasi ? 'Buat Ulang Checklist' : 'Buat Checklist Observasi' }}
            </button>
        </form>
    </div>
</div>

@if($observasi)
<div style="margin-top: 1rem;">
    <a href="{{ route('onsite.observasi.show', [$visit, $observasi]) }}"
        class="btn btn-sm"
        style="background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; font-size: 0.85rem;">
        <i class="bi bi-arrow-right me-1"></i> Lanjut Isi Checklist ({{ $observasi->items()->count() }} item)
    </a>
</div>
@endif

@endsection
