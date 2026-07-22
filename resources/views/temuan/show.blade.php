@extends('layouts.app')
@section('title', 'Detail Temuan')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>{{ $temuan->judul_temuan }}</h1>
        <p>{{ $temuan->kka?->auditPlan?->cabang?->nama_cabang }} — {{ $temuan->kka?->bidang_audit }}</p>
    </div>
    <a href="{{ route('temuan.index') }}" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="grid grid-cols-2" style="margin-bottom: 1.25rem;">
    <div class="card">
        <div class="card-header"><div class="card-title">Detail Temuan</div></div>
        <div class="card-body" style="font-size: 0.875rem;">
            <div style="margin-bottom: 0.75rem;">
                @php $cls = match($temuan->kategori) { 'signifikan'=>'badge-danger','berulang'=>'badge-warning','operasional'=>'badge-info','kepatuhan'=>'badge-purple',default=>'badge-gray' }; @endphp
                <span class="badge {{ $cls }}">{{ ucfirst($temuan->kategori) }}</span>
            </div>
            <div style="display: grid; gap: 0.75rem;">
                <div><div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 0.2rem;">Kondisi</div><p>{{ $temuan->kondisi }}</p></div>
                <div><div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 0.2rem;">Kriteria</div><p>{{ $temuan->kriteria }}</p></div>
                <div><div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 0.2rem;">Sebab</div><p>{{ $temuan->sebab }}</p></div>
                <div><div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 0.2rem;">Akibat</div><p>{{ $temuan->akibat }}</p></div>
                <div><div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 0.2rem;">Rekomendasi RA</div><p>{{ $temuan->rekomendasi_ra }}</p></div>
                @if($temuan->target_selesai_tl)
                <div><div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 0.2rem;">Target Selesai TL</div><p>{{ \Carbon\Carbon::parse($temuan->target_selesai_tl)->format('d M Y') }}</p></div>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Tindak Lanjut</div>
            @if(in_array(auth()->user()->role, ['auditee','ra']))
            <button class="btn btn-primary btn-sm" onclick="document.getElementById('modalTL').classList.add('open')">
                <i class="bi bi-plus-lg"></i> Upload TL
            </button>
            @endif
        </div>
        <div class="card-body">
            @forelse($temuan->tindakLanjuts as $tl)
            <div style="padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); margin-bottom: 0.75rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $tl->auditeeUser?->name ?? 'Auditee' }} — {{ $tl->created_at->format('d M Y') }}</span>
                    @php $cls = match($tl->status_tl) { 'selesai'=>'badge-success','terlambat'=>'badge-danger','proses_tl'=>'badge-warning',default=>'badge-gray' }; @endphp
                    <span class="badge {{ $cls }}">{{ strtoupper(str_replace('_',' ',$tl->status_tl)) }}</span>
                </div>
                <p style="font-size: 0.85rem;">{{ $tl->respon_auditee }}</p>
                @if($tl->bukti_lampiran_path)
                <a href="{{ asset('storage/'.$tl->bukti_lampiran_path) }}" target="_blank" class="btn btn-outline btn-sm" style="margin-top: 0.5rem;">
                    <i class="bi bi-paperclip"></i> Lihat Bukti
                </a>
                @endif
                @if($tl->catatan_verifikasi_ra)
                <div style="margin-top: 0.5rem; padding: 0.5rem; background: #f0fdf4; border-radius: var(--radius-sm); font-size: 0.78rem; color: #065f46;">
                    <strong>Verifikasi RA:</strong> {{ $tl->catatan_verifikasi_ra }}
                </div>
                @endif
                @if(auth()->user()->role === 'ra' && $tl->status_tl === 'proses_tl')
                <form action="{{ route('tindak-lanjut.verifikasi', $tl->id) }}" method="POST" style="margin-top: 0.5rem; display: flex; gap: 0.5rem; align-items: center;">
                    @csrf
                    <select name="status_tl" class="form-select" style="flex: 1;">
                        <option value="selesai">Selesai</option>
                        <option value="terlambat">Terlambat</option>
                        <option value="proses_tl">Masih Proses</option>
                    </select>
                    <input type="text" name="catatan_verifikasi_ra" class="form-input" placeholder="Catatan..." style="flex: 2;">
                    <button type="submit" class="btn btn-success btn-sm">Verifikasi</button>
                </form>
                @endif
            </div>
            @empty
            <div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada tindak lanjut.</p></div>
            @endforelse
        </div>
    </div>
</div>

{{-- Modal Upload TL --}}
@if(in_array(auth()->user()->role, ['auditee','ra']))
<div class="modal-overlay" id="modalTL">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Upload Tindak Lanjut</div>
            <button class="modal-close" onclick="document.getElementById('modalTL').classList.remove('open')">&times;</button>
        </div>
        <form action="{{ route('tindak-lanjut.respon') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="temuan_id" value="{{ $temuan->id }}">
            <input type="hidden" name="auditee_user_id" value="{{ auth()->id() }}">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Respon / Keterangan Tindak Lanjut</label>
                    <textarea name="respon_auditee" class="form-textarea" required placeholder="Jelaskan tindak lanjut yang telah dilakukan..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Bukti Lampiran (PDF/JPG/PNG/ZIP, maks 5MB)</label>
                    <input type="file" name="bukti_lampiran" class="form-input" accept=".pdf,.jpg,.png,.zip">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalTL').classList.remove('open')">Batal</button>
                <button type="submit" class="btn btn-primary">Upload TL</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
