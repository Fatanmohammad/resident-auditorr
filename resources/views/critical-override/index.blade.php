@extends('layouts.app')
@section('title', 'Trigger Darurat')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Trigger Darurat</h1>
        <p>Kelola Critical Override — kejadian darurat yang memaksa kategori risiko unit menjadi High</p>
    </div>
    <form method="GET">
        <select name="status" class="form-select" style="width:auto;padding:0.35rem 0.75rem;font-size:0.82rem;" onchange="this.form.submit()">
            <option value="Aktif" {{ $status == 'Aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="Tidak Aktif" {{ $status == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
            <option value="Selesai" {{ $status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
            <option value="" {{ $status == '' ? 'selected' : '' }}>Semua</option>
        </select>
    </form>
</div>

{{-- Form Tambah Trigger --}}
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header"><div class="card-title"><i class="bi bi-bell-fill" style="color:var(--bs-blue);"></i> Tambah Trigger Darurat</div></div>
    <div class="card-body">
        <form action="{{ route('critical-override.store', 0) }}" method="POST" id="override-form">
        @csrf
        <div class="grid grid-cols-2" style="gap:1rem;">
            <div class="form-group">
                <label class="form-label">Unit <span style="color:#dc2626;">*</span></label>
                <select name="unit_id" class="form-input" required>
                    <option value="">— Pilih Unit —</option>
                    @foreach($units as $u)
                    <option value="{{ $u->id }}">{{ $u->unit_code }} — {{ $u->unit_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Trigger <span style="color:#dc2626;">*</span></label>
                <input type="date" name="trigger_date" class="form-input" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Jenis Trigger <span style="color:#dc2626;">*</span></label>
                <select name="trigger_type" class="form-input" required>
                    <option value="">— Pilih Jenis —</option>
                    @foreach(['Fraud Indicator','Selisih Kas Material','Dokumen/Agunan Hilang','User Sistem Tidak Sah','Transaksi Tanpa Otorisasi','TL High/Critical Overdue','Penolakan Data RA','Repeat Finding Critical'] as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Disetujui Oleh</label>
                <input type="text" name="approved_by" class="form-input" placeholder="Nama approver">
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="trigger_description" class="form-input" rows="2" placeholder="Deskripsi kejadian darurat"></textarea>
            </div>
<div class="form-group">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-input" rows="2" placeholder="Catatan tambahan"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Alasan Persetujuan</label>
                <textarea name="reason" class="form-input" rows="2" placeholder="Alasan persetujuan trigger ini"></textarea>
            </div>
        </div>
        <div style="margin-top:1rem;">
            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Trigger</button>
        </div>
        </form>
    </div>
</div>

{{-- Daftar Trigger --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">Daftar Trigger Darurat — Status: {{ $status ?: 'Semua' }}</div>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tanggal</th><th>Unit</th><th>Jenis Trigger</th><th>Deskripsi</th>
                    <th>Status</th><th>Disetujui</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($overrides as $ov)
                <tr>
                    <td style="font-size:0.8rem;">{{ $ov->trigger_date->format('d-m-Y') }}</td>
                    <td>
                        <strong>{{ $ov->unit?->unit_name }}</strong>
                        <div style="font-size:0.72rem;color:var(--text-muted);">{{ $ov->unit?->unit_code }}</div>
                    </td>
                    <td style="font-size:0.82rem;">{{ $ov->trigger_type }}</td>
                    <td style="font-size:0.82rem;max-width:220px;">{{ $ov->trigger_description ?? '-' }}</td>
                    <td>
                        @php
                            $stCls = match($ov->status) {
                                'Aktif' => 'badge-danger',
                                'Selesai' => 'badge-success',
                                default => 'badge-gray',
                            };
                        @endphp
                        <span class="badge {{ $stCls }}">{{ $ov->status }}</span>
                    </td>
                    <td style="font-size:0.82rem;">{{ $ov->approved_by ?? '-' }}</td>
                    <td>
                        @if($ov->status !== 'Selesai')
                        <div style="display:flex;gap:0.3rem;">
                            <form action="{{ route('critical-override.status', $ov) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $ov->status === 'Aktif' ? 'Tidak Aktif' : 'Aktif' }}">
                                <button type="submit" class="btn btn-outline btn-sm" title="{{ $ov->status === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i class="bi {{ $ov->status === 'Aktif' ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('critical-override.status', $ov) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Selesai">
                                <button type="submit" class="btn btn-outline btn-sm" title="Tandai Selesai" onclick="return confirm('Tandai trigger ini selesai?')">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                            </form>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-bell-slash"></i><p>Tidak ada trigger darurat dengan status ini.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    // Unit dropdown pilih unit, kirim ke route store dengan unit_id
    document.getElementById('override-form').addEventListener('submit', function(e) {
        e.preventDefault();
        var unitId = this.querySelector('[name=unit_id]').value;
        if (!unitId) { alert('Pilih unit terlebih dahulu.'); return; }
        // Update action route ke unit yang dipilih
        var action = "{{ route('critical-override.store', 0) }}";
        this.action = action.replace('/0', '/' + unitId);
        this.submit();
    });
</script>
@endpush
@endsection
