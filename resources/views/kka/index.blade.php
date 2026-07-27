@extends('layouts.app')
@section('title', 'Kartu Kerja Audit (KKA)')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Kartu Kerja Audit (KKA)</h1>
        <p>Bukti pelaksanaan audit sekaligus absensi kehadiran RA</p>
    </div>
    @if(auth()->user()->role === 'ra')
    <a href="{{ route('kka.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Buat KKA</a>
    @endif
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cabang</th>
                    <th>RA</th>
                    <th>Bidang Audit</th>
                    <th>Sub Bidang</th>
                    <th>Tgl Pemeriksaan</th>
                    <th>Temuan</th>
                    <th>Status KKA</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kkas as $kka)
                <tr>
                    <td><strong>{{ $kka->auditPlan?->cabang?->nama_cabang ?? '-' }}</strong></td>
                    <td>{{ $kka->auditPlan?->raUser?->name ?? '-' }}</td>
                    <td>{{ $kka->bidang_audit }}</td>
                    <td>{{ $kka->sub_bidang ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($kka->tanggal_pemeriksaan)->format('d M Y') }}</td>
                    <td>
                        @if($kka->temuanAudits->count() > 0)
                        <span class="badge badge-warning">{{ $kka->temuanAudits->count() }}</span>
                        @else
                        <span class="badge badge-gray">0</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $cls = match($kka->status_kka) {
                                'approved_kadiv' => 'badge-success',
                                'reviewed_kabag' => 'badge-info',
                                'revisi'         => 'badge-danger',
                                default          => 'badge-gray',
                            };
                        @endphp
                        <span class="badge {{ $cls }}">{{ strtoupper(str_replace('_',' ',$kka->status_kka)) }}</span>
                    </td>
                    <td style="display: flex; gap: 0.4rem;">
                        <a href="{{ route('kka.show', $kka->id) }}" class="btn btn-outline btn-sm"><i class="bi bi-eye"></i></a>
                        @if(auth()->user()->role === 'kabag_ra' && $kka->status_kka === 'draft')
                        <button class="btn btn-yellow btn-sm" onclick="openReview({{ $kka->id }})"><i class="bi bi-check-lg"></i> Review</button>
                        @elseif(auth()->user()->role === 'kadiv_skai' && $kka->status_kka === 'reviewed_kabag')
                        <button class="btn btn-yellow btn-sm" onclick="openReview({{ $kka->id }})"><i class="bi bi-check-lg"></i> Approve</button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8"><div class="empty-state"><i class="bi bi-journal-x"></i><p>Belum ada KKA.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

{{-- Modal Review KKA --}}
@if(in_array(auth()->user()->role, ['kabag_ra','kadiv_skai']))
<div class="modal-overlay" id="modalReview">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Review KKA</div>
            <button class="modal-close" onclick="document.getElementById('modalReview').classList.remove('open')">&times;</button>
        </div>
        <form id="formReview" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Status Review</label>
                    <select name="status_kka" class="form-select" required>
                        @if(auth()->user()->role === 'kabag_ra')
                        <option value="reviewed_kabag">Reviewed Kabag</option>
                        @elseif(auth()->user()->role === 'kadiv_skai')
                        <option value="approved_kadiv">Approved Kadiv</option>
                        @endif
                        <option value="revisi">Perlu Revisi</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan_kabag" class="form-textarea" placeholder="Catatan review..."></textarea>
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalReview').classList.remove('open')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Review</button>
            </div>
        </form>
    </div>
@endif
@endsection

@push('scripts')
<script>
function openReview(id) {
    document.getElementById('formReview').action = '/kka/' + id + '/review';
    document.getElementById('modalReview').classList.add('open');
}
</script>
@endpush</｜｜DSML｜｜parameter>
</invoke>
</｜｜DSML｜｜tool_calls>
