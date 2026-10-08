@extends('layouts.app')

@section('content')

<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 0.3rem;">Kunjungan Onsite</h4>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Manajemen kunjungan audit onsite ke unit KCP / KCPLK.</p>
    </div>
    <a href="{{ route('onsite.create') }}" class="btn btn-primary" style="font-size: 0.9rem; font-weight: 600; background-color: #1e3a8a; border-color: #1e3a8a;">
        <i class="bi bi-plus-circle me-1"></i> Buat Kunjungan Baru
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success" style="font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
@endif

<div class="card" style="border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.04); border: 1px solid #e2e8f0;">
    <div class="card-body" style="padding: 0;">
        <table class="table table-hover" style="margin: 0; font-size: 0.88rem;">
            <thead style="background-color: #f8fafc;">
                <tr>
                    <th style="padding: 0.9rem 1rem; color: #64748b; font-weight: 600;">Unit</th>
                    <th style="padding: 0.9rem 1rem; color: #64748b; font-weight: 600;">Periode</th>
                    <th style="padding: 0.9rem 1rem; color: #64748b; font-weight: 600;">Tanggal Kunjungan</th>
                    <th style="padding: 0.9rem 1rem; color: #64748b; font-weight: 600; text-align: center;">Populasi</th>
                    <th style="padding: 0.9rem 1rem; color: #64748b; font-weight: 600; text-align: center;">Sampel</th>
                    <th style="padding: 0.9rem 1rem; color: #64748b; font-weight: 600; text-align: center;">Status</th>
                    <th style="padding: 0.9rem 1rem; color: #64748b; font-weight: 600;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($visits as $visit)
                <tr>
                    <td style="padding: 0.8rem 1rem; vertical-align: middle;">
                        <div style="font-weight: 600; color: #1e293b;">{{ $visit->kode_unit }}</div>
                        <div style="font-size: 0.8rem; color: #64748b;">{{ $visit->nama_unit }}</div>
                    </td>
                    <td style="padding: 0.8rem 1rem; vertical-align: middle; color: #475569;">{{ $visit->periode }}</td>
                    <td style="padding: 0.8rem 1rem; vertical-align: middle; color: #475569;">
                        {{ \Carbon\Carbon::parse($visit->tanggal_mulai)->format('d M Y') }}
                        — {{ \Carbon\Carbon::parse($visit->tanggal_selesai)->format('d M Y') }}
                    </td>
                    <td style="padding: 0.8rem 1rem; vertical-align: middle; text-align: center; color: #475569;">
                        {{ number_format($visit->total_populasi) }}
                    </td>
                    <td style="padding: 0.8rem 1rem; vertical-align: middle; text-align: center;">
                        <span style="font-weight: 700; color: #1e3a8a;">{{ number_format($visit->total_sampel) }}</span>
                    </td>
                    <td style="padding: 0.8rem 1rem; vertical-align: middle; text-align: center;">
                        @php
                            $badgeColor = match($visit->status) {
                                'Persiapan'   => '#f59e0b',
                                'Berlangsung' => '#3b82f6',
                                'Selesai'     => '#10b981',
                                default       => '#94a3b8',
                            };
                        @endphp
                        <span style="background-color: {{ $badgeColor }}20; color: {{ $badgeColor }}; padding: 0.2rem 0.7rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                            {{ $visit->status }}
                        </span>
                    </td>
                    <td style="padding: 0.8rem 1rem; vertical-align: middle; text-align: right;">
                        @if($visit->status === 'Persiapan')
                            <a href="{{ route('onsite.permintaan.create', $visit->id) }}" class="btn btn-sm btn-outline-secondary" style="font-size: 0.8rem; margin-right: 0.3rem;">
                                <i class="bi bi-printer me-1"></i> Surat
                            </a>
                            <a href="{{ route('onsite.upload', $visit->id) }}" class="btn btn-sm" style="background-color: #1e3a8a; color: white; font-size: 0.8rem;">
                                <i class="bi bi-cloud-upload me-1"></i> Upload CBS
                            </a>
                        @else
                            <a href="{{ route('onsite.kka', $visit->id) }}" class="btn btn-sm btn-outline-primary" style="font-size: 0.8rem; margin-right: 0.3rem;">
                                <i class="bi bi-file-earmark-check me-1"></i> KKA Sampling
                            </a>
                            <a href="{{ route('onsite.observasi.create', $visit->id) }}" class="btn btn-sm btn-outline-secondary" style="font-size: 0.8rem;">
                                <i class="bi bi-building-check me-1"></i> Observasi
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: #94a3b8;">
                        Belum ada kunjungan onsite. <a href="{{ route('onsite.create') }}">Buat kunjungan baru</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($visits->hasPages())
    <div style="margin-top: 1rem;">{{ $visits->links() }}</div>
@endif

@endsection
