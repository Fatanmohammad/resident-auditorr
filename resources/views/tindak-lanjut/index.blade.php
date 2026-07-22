@extends('layouts.app')
@section('title', 'Monitoring Tindak Lanjut')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Monitoring Tindak Lanjut</h1>
        <p>Pantau status penyelesaian tindak lanjut temuan audit</p>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cabang</th>
                    <th>Temuan</th>
                    <th>Kategori</th>
                    <th>Auditee</th>
                    <th>Respon</th>
                    <th>Status TL</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tindakLanjuts as $tl)
                <tr>
                    <td><strong>{{ $tl->temuan?->kka?->auditPlan?->cabang?->nama_cabang ?? '-' }}</strong></td>
                    <td>{{ $tl->temuan?->judul_temuan ?? '-' }}</td>
                    <td>
                        @php $kat = $tl->temuan?->kategori; $cls = match($kat) { 'signifikan'=>'badge-danger','berulang'=>'badge-warning',default=>'badge-info' }; @endphp
                        <span class="badge {{ $cls }}">{{ ucfirst($kat ?? '-') }}</span>
                    </td>
                    <td>{{ $tl->auditeeUser?->name ?? '-' }}</td>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $tl->respon_auditee ?? '-' }}</td>
                    <td>
                        @php $cls = match($tl->status_tl) { 'selesai'=>'badge-success','terlambat'=>'badge-danger','proses_tl'=>'badge-warning',default=>'badge-gray' }; @endphp
                        <span class="badge {{ $cls }}">{{ strtoupper(str_replace('_',' ',$tl->status_tl)) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('temuan.show', $tl->temuan_id) }}" class="btn btn-outline btn-sm"><i class="bi bi-eye"></i></a>
                        @if(auth()->user()->role === 'ra' && $tl->status_tl === 'proses_tl')
                        <button class="btn btn-yellow btn-sm" onclick="openVerif({{ $tl->id }})"><i class="bi bi-check-lg"></i></button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada data tindak lanjut.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(auth()->user()->role === 'ra')
<div class="modal-overlay" id="modalVerif">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Verifikasi Tindak Lanjut</div>
            <button class="modal-close" onclick="document.getElementById('modalVerif').classList.remove('open')">&times;</button>
        </div>
        <form id="formVerif" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Status Verifikasi</label>
                    <select name="status_tl" class="form-select" required>
                        <option value="selesai">Selesai</option>
                        <option value="terlambat">Terlambat</option>
                        <option value="proses_tl">Masih Proses</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Catatan Verifikasi</label>
                    <textarea name="catatan_verifikasi_ra" class="form-textarea" placeholder="Catatan hasil verifikasi..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalVerif').classList.remove('open')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Verifikasi</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function openVerif(id) {
    document.getElementById('formVerif').action = '/tindak-lanjut/' + id + '/verifikasi';
    document.getElementById('modalVerif').classList.add('open');
}
</script>
@endpush
