@extends('layouts.app')
@section('title', 'Detail Unit')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>{{ $unit->unit_name }}</h1>
        <p>{{ $unit->unit_code }} — {{ $unit->unit_type }}</p>
    </div>
    <div style="display:flex;gap:0.5rem;">
        @if(in_array(auth()->user()->role, ['kabag_ra','kadiv_skai']))
        <a href="{{ route('raw-metrics.create', ['unit' => $unit, 'period' => $period]) }}" class="btn btn-primary btn-sm"><i class="bi bi-input-cursor-text"></i> Input Raw Metrics</a>
        <a href="{{ route('coverage.show', $unit) }}" class="btn btn-outline btn-sm"><i class="bi bi-grid-3x3-gap"></i> Coverage Setup</a>
        @endif
        <a href="{{ route('units.index') }}" class="btn btn-outline btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif

<div class="grid grid-cols-2" style="margin-bottom:1.25rem;">
    <div class="card">
        <div class="card-header"><div class="card-title">Informasi Unit</div></div>
        <div class="card-body">
            <table style="width:100%;font-size:0.875rem;border-collapse:collapse;">
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);width:45%;">Kode Unit</td><td>{{ $unit->unit_code }}</td></tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Tipe</td><td><span class="badge badge-info">{{ $unit->unit_type }}</span></td></tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Kantor Induk</td><td>{{ $unit->parent_office ?? '-' }}</td></tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Wilayah</td><td>{{ $unit->region ?? '-' }}</td></tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Base RA Unit</td><td>{{ $unit->base_ra_unit ?? '-' }}</td></tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Jarak (km)</td><td>{{ $unit->distance_from_parent_km ?? '-' }}</td></tr>
                <tr><td style="padding:0.4rem 0;color:var(--text-muted);">Status</td>
                    <td><span class="badge {{ $unit->is_active ? 'badge-success' : 'badge-gray' }}">{{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Skor Risiko — Periode {{ $period }}</div>
            <form method="GET" style="display:flex;gap:0.5rem;">
                <select name="period" class="form-select" style="width:auto;padding:0.3rem 0.6rem;font-size:0.8rem;" onchange="this.form.submit()">
                    @for($y = date('Y')+1; $y >= 2025; $y--)
                    <option value="{{ $y }}" {{ $y == $period ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
        <div class="card-body">
            @if($scoring)
            @php $riskCls = match($scoring->final_category) { 'High'=>'badge-danger','Moderate to High'=>'badge-warning','Moderate'=>'badge-info','Low to Moderate'=>'badge-purple',default=>'badge-gray' }; @endphp
            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;padding:0.75rem;background:#f0f4f8;border-radius:var(--radius-md);">
                <div>
                    <div style="font-size:0.72rem;color:var(--text-muted);">Skor Final</div>
                    <div style="font-size:2rem;font-weight:700;color:var(--bs-blue-dark);">{{ number_format($scoring->weighted_score, 1) }}</div>
                </div>
                <div>
                    <span class="badge {{ $riskCls }}" style="font-size:0.82rem;padding:0.3rem 0.75rem;">{{ $scoring->final_category }}</span>
                    @if($scoring->has_active_override)
                    <div style="margin-top:0.3rem;"><span class="badge badge-danger" style="font-size:0.7rem;">Override Aktif</span></div>
                    @endif
                </div>
            </div>
            @if($cs)
            @foreach(['Riwayat RA'=>$cs->skor_riwayat_ra,'Kas/Teller'=>$cs->skor_kas_teller,'CS/DPK'=>$cs->skor_cs_dpk,'Kredit'=>$cs->skor_kredit,'TI/ATM'=>$cs->skor_ti_atm,'Monitoring TL'=>$cs->skor_monitoring_tl] as $label => $skor)
            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.4rem;font-size:0.8rem;">
                <span style="width:100px;color:var(--text-muted);">{{ $label }}</span>
                <div style="flex:1;background:#e5e7eb;border-radius:9999px;height:6px;">
                    <div style="width:{{ min(100,$skor) }}%;background:var(--bs-blue);height:6px;border-radius:9999px;"></div>
                </div>
                <span style="width:35px;text-align:right;font-weight:600;">{{ number_format($skor,1) }}</span>
            </div>
            @endforeach
            @endif
            @else
            <div class="empty-state" style="padding:1.5rem;"><i class="bi bi-bar-chart"></i><p>Belum ada data scoring untuk periode ini.</p></div>
            @endif
        </div>
    </div>
</div>

{{-- Critical Overrides --}}
@if(in_array(auth()->user()->role, ['kabag_ra','kadiv_skai']))
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header">
        <div class="card-title">Critical Override</div>
        <button class="btn btn-outline btn-sm" onclick="document.getElementById('modalOverride').classList.add('open')">
            <i class="bi bi-plus-lg"></i> Tambah Override
        </button>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Tanggal</th><th>Tipe Trigger</th><th>Deskripsi</th><th>Status</th><th>Disetujui</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($overrides as $ov)
                <tr>
                    <td style="font-size:0.8rem;">{{ $ov->trigger_date->format('d M Y') }}</td>
                    <td><span class="badge badge-warning" style="font-size:0.7rem;">{{ $ov->trigger_type }}</span></td>
                    <td style="font-size:0.8rem;">{{ $ov->trigger_description ?? '-' }}</td>
                    <td>
                        @php $stCls = match($ov->status) { 'Aktif'=>'badge-danger','Selesai'=>'badge-success',default=>'badge-gray' }; @endphp
                        <span class="badge {{ $stCls }}">{{ $ov->status }}</span>
                    </td>
                    <td style="font-size:0.8rem;">{{ $ov->approved_by ?? '-' }}</td>
                    <td>
                        <form action="{{ route('critical-override.status', $ov) }}" method="POST" style="display:inline;">
                            @csrf @method('PATCH')
                            <select name="status" class="form-select" style="padding:0.2rem 0.5rem;font-size:0.75rem;width:auto;" onchange="this.form.submit()">
                                @foreach(['Aktif','Tidak Aktif','Selesai'] as $s)
                                <option value="{{ $s }}" {{ $ov->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty-state" style="padding:1.5rem;"><i class="bi bi-shield-check"></i><p>Tidak ada critical override aktif.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Override --}}
<div class="modal-overlay" id="modalOverride">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Tambah Critical Override</div>
            <button class="modal-close" onclick="document.getElementById('modalOverride').classList.remove('open')">&times;</button>
        </div>
        <form action="{{ route('critical-override.store', $unit) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Tanggal Trigger</label>
                    <input type="date" name="trigger_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tipe Trigger</label>
                    <select name="trigger_type" class="form-select" required>
                        @foreach(['Fraud Indicator','Selisih Kas Material','Dokumen/Agunan Hilang','User Sistem Tidak Sah','Transaksi Tanpa Otorisasi','TL High/Critical Overdue','Penolakan Data RA','Repeat Finding Critical'] as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="trigger_description" class="form-textarea" placeholder="Jelaskan kondisi yang memicu override..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Disetujui Oleh</label>
                    <input type="text" name="approved_by" class="form-input" value="{{ auth()->user()->name }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalOverride').classList.remove('open')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Override</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
