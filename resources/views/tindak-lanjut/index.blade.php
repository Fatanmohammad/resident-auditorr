@extends('layouts.app')
@section('title', 'Monitoring Tindak Lanjut')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Monitoring Tindak Lanjut</h1>
        <p>Pantau status penyelesaian tindak lanjut temuan audit</p>
    </div>

{{-- Untuk Auditee: Tampilkan daftar temuan yang perlu ditindaklanjuti --}}
@if(auth()->user()->role === 'auditee' && $temuans->count() > 0)
<div class="card" style="margin-bottom: 1.25rem; border-left: 4px solid var(--bs-yellow);">
    <div class="card-header">
        <div class="card-title" style="color: #92400e;">⚠️ Temuan yang Perlu Ditindaklanjuti</div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cabang</th>
                    <th>Temuan</th>
                    <th>Kategori</th>
                    <th>Target Selesai</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($temuans as $temuan)
                <tr>
                    <td><strong>{{ $temuan->kka?->auditPlan?->cabang?->nama_cabang ?? '-' }}</strong></td>
                    <td>{{ $temuan->judul_temuan }}</td>
                    <td>
                        @php $cls = match($temuan->kategori) { 'signifikan'=>'badge-danger','berulang'=>'badge-warning',default=>'badge-info' }; @endphp
                        <span class="badge {{ $cls }}">{{ ucfirst($temuan->kategori) }}</span>
                    </td>
                    <td>{{ $temuan->target_selesai_tl ? \Carbon\Carbon::parse($temuan->target_selesai_tl)->format('d M Y') : '-' }}</td>
                    <td>
                        <a href="{{ route('temuan.show', $temuan->id) }}" class="btn btn-primary btn-sm"><i class="bi bi-arrow-right"></i> Upload TL</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

{{-- Jika Auditee tidak ada temuan yang perlu ditindaklanjuti --}}
@if(auth()->user()->role === 'auditee' && $temuans->count() === 0 && $tindakLanjuts->count() === 0)
<div class="card">
    <div class="empty-state" style="padding: 3rem;">
        <i class="bi bi-check-circle" style="font-size: 3rem; color: #16a34a;"></i>
        <p style="font-size: 1rem; font-weight: 600; color: #065f46; margin-top: 0.75rem;">Belum ada temuan yang perlu ditindaklanjuti</p>
        <p style="font-size: 0.85rem; color: var(--text-muted);">Jika ada temuan baru, akan muncul di sini.</p>
    </div>
@endif

{{-- Tabel Riwayat Tindak Lanjut --}}
@if($tindakLanjuts->count() > 0)
<div class="card">
    <div class="card-header">
        <div class="card-title">Riwayat Tindak Lanjut</div>
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
                @foreach($tindakLanjuts as $tl)
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
                @endforeach
            </tbody>
        </table>
    </div>
@endif

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
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalVerif').classList.remove('open')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Verifikasi</button>
            </div>
        </form>
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
