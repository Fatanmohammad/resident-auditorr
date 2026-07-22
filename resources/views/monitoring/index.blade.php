@extends('layouts.app')
@section('title', 'Monitoring Temuan')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Monitoring Temuan</h1>
        <p>Monitoring realisasi audit dan kinerja RA terhadap Audit Plan</p>
    </div>
</div>

<div class="card" style="margin-bottom: 1.25rem;">
    <div class="card-header">
        <div class="card-title">Sync Monitoring per Audit Plan</div>
    </div>
    <div class="card-body">
        <form action="{{ route('monitoring.sync', ':id') }}" method="POST" id="formSync">
            @csrf
            <div style="display: flex; gap: 0.75rem; align-items: flex-end; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                    <label class="form-label">Pilih Audit Plan</label>
                    <select name="audit_plan_id" id="selectPlan" class="form-select" required>
                        <option value="">-- Pilih Audit Plan --</option>
                        @foreach($auditPlans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->cabang?->nama_cabang }} — {{ $plan->tahun_periode }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="flex: 2; min-width: 200px; margin-bottom: 0;">
                    <label class="form-label">Catatan Monitoring</label>
                    <input type="text" name="catatan_monitoring" class="form-input" placeholder="Opsional...">
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Sync Otomatis</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><div class="card-title">Hasil Monitoring</div></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cabang</th>
                    <th>Periode</th>
                    <th>Jenis Monitoring</th>
                    <th>Total Temuan</th>
                    <th>TL Selesai</th>
                    <th>TL Pending</th>
                    <th>Progress</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monitorings as $mon)
                <tr>
                    <td><strong>{{ $mon->auditPlan?->cabang?->nama_cabang ?? '-' }}</strong></td>
                    <td>{{ $mon->auditPlan?->tahun_periode ?? '-' }}</td>
                    <td><span class="badge badge-info">{{ strtoupper(str_replace('_',' ',$mon->jenis_monitoring)) }}</span></td>
                    <td>{{ $mon->total_temuan }}</td>
                    <td><span class="badge badge-success">{{ $mon->total_tl_selesai }}</span></td>
                    <td><span class="badge badge-warning">{{ $mon->total_tl_pending }}</span></td>
                    <td>
                        @php $pct = $mon->total_temuan > 0 ? round(($mon->total_tl_selesai / $mon->total_temuan) * 100) : 0; @endphp
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="flex: 1; height: 6px; background: var(--border-color); border-radius: 3px; min-width: 60px;">
                                <div style="width: {{ $pct }}%; height: 100%; background: var(--bs-blue); border-radius: 3px;"></div>
                            </div>
                            <span style="font-size: 0.75rem; font-weight: 600;">{{ $pct }}%</span>
                        </div>
                    </td>
                    <td style="font-size: 0.78rem; color: var(--text-muted);">{{ $mon->catatan_monitoring ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="8"><div class="empty-state"><i class="bi bi-graph-up"></i><p>Belum ada data monitoring. Gunakan tombol Sync di atas.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('formSync').addEventListener('submit', function(e) {
    const planId = document.getElementById('selectPlan').value;
    if (!planId) { e.preventDefault(); return; }
    this.action = this.action.replace(':id', planId);
});
</script>
@endpush
