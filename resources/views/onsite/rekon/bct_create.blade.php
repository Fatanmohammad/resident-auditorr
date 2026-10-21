@extends('layouts.app')
@section('content')
@php $userRole = strtolower(auth()->user()->role); @endphp

<div style="max-width:600px; margin:0 auto;">
    <a href="{{ route('onsite.kka', $visit) }}" style="font-size:0.85rem; color:#64748b; text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke KKA Onsite
    </a>
    <h4 style="font-weight:700; margin:0.75rem 0 0.25rem;">Rekon Barang Cetakan</h4>
    <p style="color:#64748b; font-size:0.88rem; margin:0 0 1.5rem;">{{ $visit->kode_unit }} — {{ $visit->nama_unit }}</p>

    @if($rekon)
    <div class="alert alert-warning" style="font-size:0.88rem;">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        Checklist sudah ada ({{ $rekon->items()->count() }} item, dibuat {{ $rekon->tanggal_opname }}).
        Submit ulang akan <strong>menghapus dan membuat ulang</strong> seluruh checklist.
    </div>
    @endif

    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:1.5rem;">
        <form method="POST" action="{{ route('onsite.rekon.bct.store', $visit) }}">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:0.4rem;">Nama RA Pelaksana</label>
                <input type="text" name="ra_pelaksana" class="form-control form-control-sm"
                    value="{{ old('ra_pelaksana', auth()->user()->name) }}" required>
            </div>
            <div style="margin-bottom:1.5rem;">
                <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:0.4rem;">Tanggal Stock Opname</label>
                <input type="date" name="tanggal_opname" class="form-control form-control-sm"
                    value="{{ old('tanggal_opname', date('Y-m-d')) }}" required>
            </div>
            <div style="background:#f8fafc; border-radius:8px; padding:1rem; margin-bottom:1.5rem; font-size:0.85rem; color:#475569;">
                <i class="bi bi-info-circle me-1"></i>
                Sistem akan generate <strong>27 item</strong> checklist barang cetakan secara otomatis.
            </div>
            <button type="submit" class="btn btn-primary btn-sm w-100" style="background:#1e3a8a; border-color:#1e3a8a;">
                <i class="bi bi-play-circle me-1"></i> Generate Checklist
            </button>
        </form>
    </div>
</div>
@endsection
