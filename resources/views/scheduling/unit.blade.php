@extends('layouts.app')
@section('title', 'Jadwal Unit')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>{{ $unit->unit_name }}</h1>
        <p>Jadwal Kunjungan Onsite — Periode {{ $period }}</p>
    </div>
    <a href="{{ route('scheduling.index', ['period'=>$period]) }}" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

{{-- Info Frekuensi --}}
@if($freq)
<div class="card" style="margin-bottom:1.25rem;max-width:640px;">
    <div class="card-header">
        <div class="card-title">Frekuensi Onsite</div>
@if(in_array(auth()->user()->role, ['kabag_ra','kadiv_skai','admin']))
        <button class="btn btn-outline btn-sm" onclick="document.getElementById('modalFreq').classList.add('open')"><i class="bi bi-pencil"></i> Override</button>
        @endif
    </div>
    <div class="card-body">
        <table style="width:100%;font-size:0.875rem;border-collapse:collapse;">
            <tr><td style="padding:0.35rem 0;color:var(--text-muted);width:45%;">Frekuensi Otomatis</td><td>{{ $freq->auto_frequency_label }}</td></tr>
            <tr><td style="padding:0.35rem 0;color:var(--text-muted);">Override Manual</td><td>{{ $freq->manual_override_frequency ?? '-' }}</td></tr>
            <tr><td style="padding:0.35rem 0;color:var(--text-muted);">Frekuensi Final</td><td><strong>{{ $freq->final_frequency_label }}</strong></td></tr>
            <tr><td style="padding:0.35rem 0;color:var(--text-muted);">Kunjungan/Tahun</td><td>{{ $freq->is_resident_daily_review ? 'Harian (Resident)' : $freq->final_visits_per_year.'x' }}</td></tr>
            <tr><td style="padding:0.35rem 0;color:var(--text-muted);">Basis</td><td style="font-size:0.8rem;color:var(--text-muted);">{{ $freq->basis_note }}</td></tr>
        </table>
    </div>
</div>
@endif

{{-- Tabel Jadwal --}}
<div class="card">
    <div class="card-header"><div class="card-title">Daftar Kunjungan</div></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Ke-</th><th>Bulan</th><th>Tanggal Mulai</th><th>Tanggal Selesai</th><th>Durasi</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($visits as $v)
                @php $stCls = match($v->status) { 'Completed'=>'badge-success','In Progress'=>'badge-info','Postponed'=>'badge-warning','Cancelled'=>'badge-danger',default=>'badge-gray' }; @endphp
                <tr>
                    <td style="text-align:center;font-weight:600;">{{ $v->visit_number }}</td>
                    <td>{{ \Carbon\Carbon::create(null, $v->recommended_month)->translatedFormat('F') }}</td>
                    <td>
                        {{ $v->final_start_date?->format('d M Y') ?? '-' }}
                        @if($v->manual_override_start) <span style="font-size:0.7rem;color:var(--bs-blue);">⚙</span> @endif
                    </td>
                    <td>{{ $v->final_end_date?->format('d M Y') ?? '-' }}</td>
                    <td>{{ $v->final_duration_days }} hari</td>
                    <td>
@if(in_array(auth()->user()->role, ['kabag_ra','kadiv_skai','ra','admin']))
                        <form action="{{ route('scheduling.visit-status', $v) }}" method="POST">
                            @csrf @method('PATCH')
                            <select name="status" class="form-select" style="padding:0.2rem 0.5rem;font-size:0.75rem;width:auto;" onchange="this.form.submit()">
                                @foreach(['Planned','In Progress','Completed','Postponed','Cancelled'] as $s)
                                <option value="{{ $s }}" {{ $v->status===$s?'selected':'' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </form>
                        @else
                        <span class="badge {{ $stCls }}">{{ $v->status }}</span>
                        @endif
                    </td>
                    <td>
@if(in_array(auth()->user()->role, ['kabag_ra','kadiv_skai','admin']))
                        <button class="btn btn-outline btn-sm" onclick="openOverrideVisit({{ $v->id }}, '{{ $v->final_start_date?->format('Y-m-d') }}', '{{ $v->final_end_date?->format('Y-m-d') }}')">
                            <i class="bi bi-calendar-event"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-calendar-x"></i><p>Belum ada jadwal kunjungan.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Override Frekuensi --}}
@if(in_array(auth()->user()->role, ['kabag_ra','kadiv_skai','admin']))
<div class="modal-overlay" id="modalFreq">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Override Frekuensi</div>
            <button class="modal-close" onclick="document.getElementById('modalFreq').classList.remove('open')">&times;</button>
        </div>
<form action="{{ route('scheduling.override-frequency', $unit) }}" method="POST">
            @csrf
            <input type="hidden" name="period" value="{{ $period }}">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Frekuensi Override</label>
                    <select name="manual_override_frequency" class="form-select" required>
                        @foreach(['Bulanan','Triwulanan','Semesteran','Tahunan','Tidak Terjadwal'] as $f)
                        <option value="{{ $f }}" {{ $freq?->manual_override_frequency===$f?'selected':'' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Alasan Persetujuan</label>
                    <textarea name="reason" class="form-textarea" placeholder="Alasan perubahan frekuensi..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalFreq').classList.remove('open')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Override Visit --}}
<div class="modal-overlay" id="modalVisit">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Override Tanggal Kunjungan</div>
            <button class="modal-close" onclick="document.getElementById('modalVisit').classList.remove('open')">&times;</button>
        </div>
<form id="formVisit" method="POST">
            @csrf @method('PATCH')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="manual_override_start" id="visitStart" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="manual_override_end" id="visitEnd" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Catatan</label>
                    <textarea name="manual_notes" class="form-textarea" placeholder="Alasan perubahan jadwal..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Alasan Persetujuan</label>
                    <textarea name="reason" class="form-textarea" placeholder="Alasan persetujuan perubahan jadwal..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalVisit').classList.remove('open')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openOverrideVisit(id, start, end) {
    document.getElementById('formVisit').action = '/scheduling/visit/' + id + '/override';
    document.getElementById('visitStart').value = start;
    document.getElementById('visitEnd').value = end;
    document.getElementById('modalVisit').classList.add('open');
}
</script>
@endpush
@endif
@endsection
