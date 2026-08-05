@extends('layouts.app')
@section('title', 'Master Unit')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Master Unit</h1>
        <p>Daftar seluruh unit kerja bank (Audit Universe)</p>
    </div>
@if(in_array(auth()->user()->role, ['kabag_ra','kadiv_skai','admin']))
    <a href="{{ route('units.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Unit</a>
    @endif
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Kode</th><th>Nama Unit</th><th>Tipe</th><th>Kantor Induk</th><th>Base RA Unit</th><th>Vol. Transaksi</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($units as $unit)
                @php
                    $scoring = $unit->riskScorings->first();
                    $riskCls = match($scoring?->final_category) {
                        'High'=>'badge-danger','Moderate to High'=>'badge-warning',
                        'Moderate'=>'badge-info','Low to Moderate'=>'badge-purple',default=>'badge-gray'
                    };
                @endphp
                <tr>
                    <td style="font-size:0.78rem;color:var(--text-muted);">{{ $unit->unit_code }}</td>
                    <td><strong>{{ $unit->unit_name }}</strong></td>
                    <td><span class="badge badge-info">{{ $unit->unit_type }}</span></td>
                    <td style="font-size:0.82rem;">{{ $unit->parent_office ?? '-' }}</td>
                    <td style="font-size:0.82rem;">{{ $unit->base_ra_unit ?? '<span style="color:#dc2626;">Belum diset</span>' }}</td>
                    <td>
                        @if($scoring)
                            <span class="badge {{ $riskCls }}">{{ $scoring->final_category }}</span>
                        @else
                            <span class="badge badge-gray">Belum dinilai</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $unit->is_active ? 'badge-success' : 'badge-gray' }}">
                            {{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
<td style="display:flex;gap:0.4rem;">
                        <a href="{{ route('units.show', $unit) }}" class="btn btn-outline btn-sm" title="Detail"><i class="bi bi-eye"></i></a>
@if(in_array(auth()->user()->role, ['kabag_ra','kadiv_skai','admin']))
                        <a href="{{ route('units.edit', $unit) }}" class="btn btn-outline btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                        <a href="{{ route('raw-metrics.create', $unit) }}" class="btn btn-outline btn-sm" title="Input Data Mentah"><i class="bi bi-input-cursor-text"></i></a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8"><div class="empty-state"><i class="bi bi-building-x"></i><p>Belum ada data unit. Tambah unit atau jalankan seeder.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
