@extends('layouts.app')
@section('title', 'Scoring RA')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Scoring RA</h1>
        <p>Sistem menghitung skor RA berdasarkan parameter dan bobot yang ditetapkan</p>
    </div>
</div>

@if(in_array(auth()->user()->role, ['kadiv_skai','kabag_ra']))
<div class="card" style="margin-bottom: 1.25rem; max-width: 640px;">
    <div class="card-header"><div class="card-title">Input Skor Parameter</div></div>
    <div class="card-body">
        <form action="{{ route('scoring.kalkulasi') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Audit Plan</label>
                <select name="audit_plan_id" class="form-select" required>
                    <option value="">-- Pilih Audit Plan --</option>
                    @foreach($auditPlans as $plan)
                    <option value="{{ $plan->id }}">{{ $plan->cabang?->nama_cabang }} — {{ $plan->tahun_periode }}</option>
                    @endforeach
                </select>
                @if($auditPlans->isEmpty())
                <p class="form-error">Semua Audit Plan sudah memiliki scoring.</p>
                @endif
            </div>
            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label">Skor Parameter KAT (0-100)</label>
                    <input type="number" name="skor_parameter_kat" class="form-input" min="0" max="100" step="0.01" required placeholder="Bobot 40%">
                    <p style="font-size: 0.72rem; color: var(--text-muted); margin-top: 0.25rem;">Berdasarkan checklist KKA per bidang</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Skor Tindak Lanjut (0-100)</label>
                    <input type="number" name="skor_tindak_lanjut" class="form-input" min="0" max="100" step="0.01" required placeholder="Bobot 60%">
                    <p style="font-size: 0.72rem; color: var(--text-muted); margin-top: 0.25rem;">Persentase penyelesaian TL oleh Auditee</p>
                </div>
            </div>
            <div style="padding: 0.75rem; background: #f0f4f8; border-radius: var(--radius-md); font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1rem;">
                <strong>Formula:</strong> Skor Akhir = (Skor KAT × 40%) + (Skor TL × 60%)
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-calculator"></i> Hitung Skor</button>
        </form>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header"><div class="card-title">Hasil Scoring RA</div></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cabang</th>
                    <th>Periode</th>
                    <th>Skor KAT</th>
                    <th>Skor TL</th>
                    <th>Skor Akhir</th>
                    <th>Peringkat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($scorings as $scoring)
                <tr>
                    <td><strong>{{ $scoring->auditPlan?->cabang?->nama_cabang ?? '-' }}</strong></td>
                    <td>{{ $scoring->auditPlan?->tahun_periode ?? '-' }}</td>
                    <td>{{ $scoring->skor_parameter_kat }}</td>
                    <td>{{ $scoring->skor_tindak_lanjut }}</td>
                    <td>
                        <span style="font-size: 1.1rem; font-weight: 700; color: var(--bs-blue-dark);">{{ $scoring->skor_akhir }}</span>
                    </td>
                    <td>
                        @php
                            $cls = $scoring->skor_akhir >= 85 ? 'badge-success' : ($scoring->skor_akhir >= 70 ? 'badge-warning' : 'badge-danger');
                        @endphp
                        <span class="badge {{ $cls }}">{{ $scoring->peringkat_ra }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-bar-chart"></i><p>Belum ada data scoring.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
