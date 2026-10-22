@extends('layouts.app')
@section('content')
<div style="max-width:600px; margin:0 auto; padding-top:1rem;">
    <a href="{{ route('onsite.kka', $visit) }}" style="font-size:0.85rem; color:#64748b; text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke KKA Onsite
    </a>
    <h4 style="font-weight:700; margin:0.75rem 0 0.25rem; color:#1e293b;">Inisialisasi Berita Acara Cash Opname</h4>
    <p style="color:#64748b; font-size:0.88rem; margin:0 0 1.5rem;">{{ $visit->kode_unit }} — {{ $visit->nama_unit }}</p>

    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:1.5rem;">
        <form method="POST" action="{{ route('onsite.cash_opname.store', $visit) }}">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:0.4rem;">Nama RA Pelaksana</label>
                <input type="text" name="ra_pelaksana" class="form-control form-control-sm"
                    value="{{ old('ra_pelaksana', $opname?->ra_pelaksana ?? auth()->user()->name) }}" required>
            </div>
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:0.4rem;">Nama Pemegang Kas / Teller</label>
                <input type="text" name="pemegang_kas" class="form-control form-control-sm"
                    value="{{ old('pemegang_kas', $opname?->pemegang_kas ?? '') }}" placeholder="Masukkan nama Teller / Pemegang Kas" required>
            </div>
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:0.4rem;">Tanggal Opname Physical</label>
                <input type="date" name="tanggal_opname" class="form-control form-control-sm"
                    value="{{ old('tanggal_opname', $opname?->tanggal_opname ?? date('Y-m-d')) }}" required>
            </div>
            <div style="margin-bottom:1.5rem;">
                <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:0.4rem;">Saldo Buku / GL System (Rp)</label>
                <input type="number" name="saldo_gl" class="form-control form-control-sm"
                    value="{{ old('saldo_gl', $opname?->saldo_gl ?? 0) }}" required>
            </div>
            <button type="submit" class="btn btn-primary btn-sm w-100" style="background:#1e3a8a; border-color:#1e3a8a; font-weight:600; padding:0.5rem;">
                <i class="bi bi-play-circle me-1"></i> Buat KKA Cash Opname & ATM
            </button>
        </form>
    </div>
</div>
@endsection