@extends('layouts.app')

@section('title', $title ?? 'Halaman')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">{{ $title ?? 'Halaman' }}</h2>
    </div>
    <div style="padding: 2rem 0; text-align: center; color: var(--text-muted);">
        <i class="bi bi-tools" style="font-size: 3rem; color: var(--bs-blue-light); margin-bottom: 1rem; display: block;"></i>
        <h3 style="color: var(--bs-blue-dark); margin-bottom: 0.5rem;">Sedang Dalam Pengembangan</h3>
        <p>Halaman ini merupakan bagian dari alur aplikasi dan akan segera diimplementasikan.</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary" style="margin-top: 1.5rem; display: inline-flex; width: auto;">
            <i class="bi bi-arrow-left" style="margin-right: 0.5rem;"></i> Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection
