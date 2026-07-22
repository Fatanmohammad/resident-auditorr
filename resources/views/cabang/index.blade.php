@extends('layouts.app')
@section('title', 'Master Cabang')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Master Cabang</h1>
        <p>Struktur cabang dan anak cabang PT Bank Sulteng</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('modalTambah').classList.add('open')">
        <i class="bi bi-plus-lg"></i> Tambah Cabang
    </button>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Cabang</th>
                    <th>Tipe</th>
                    <th>Induk Cabang</th>
                    <th>Jumlah RA</th>
                    <th>Anak Cabang</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cabangs as $cabang)
                <tr>
                    <td><code style="background: var(--bg-main); padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.78rem;">{{ $cabang->kode_cabang }}</code></td>
                    <td>
                        @if($cabang->tipe === 'anak_cabang')
                        <span style="color: var(--text-muted); margin-right: 0.4rem;">↳</span>
                        @endif
                        <strong>{{ $cabang->nama_cabang }}</strong>
                    </td>
                    <td>
                        @php
                            $cls = match($cabang->tipe) {
                                'pusat'  => 'badge-danger',
                                'kcu'    => 'badge-info',
                                'anak_cabang' => 'badge-gray',
                                default  => 'badge-warning',
                            };
                        @endphp
                        <span class="badge {{ $cls }}">{{ strtoupper(str_replace('_',' ',$cabang->tipe)) }}</span>
                    </td>
                    <td>{{ $cabang->parentCabang?->nama_cabang ?? '-' }}</td>
                    <td>
                        @php $raCount = $cabang->users->where('role','ra')->count(); @endphp
                        @if($raCount > 0)
                        <span class="badge badge-success">{{ $raCount }} RA</span>
                        @else
                        <span style="color: var(--text-muted);">-</span>
                        @endif
                    </td>
                    <td>
                        @if($cabang->anakCabang->count() > 0)
                        <span class="badge badge-info">{{ $cabang->anakCabang->count() }} anak</span>
                        @else
                        <span style="color: var(--text-muted);">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-building"></i><p>Belum ada data cabang.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Tambah Cabang --}}
<div class="modal-overlay" id="modalTambah">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Tambah Cabang</div>
            <button class="modal-close" onclick="document.getElementById('modalTambah').classList.remove('open')">&times;</button>
        </div>
        <form action="{{ route('cabang.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Cabang</label>
                    <input type="text" name="nama_cabang" class="form-input" required placeholder="cth: Cabang Palu KCU">
                </div>
                <div class="form-group">
                    <label class="form-label">Kode Cabang</label>
                    <input type="text" name="kode_cabang" class="form-input" required placeholder="cth: BS-001">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipe Cabang</label>
                    <select name="tipe" class="form-select" required>
                        <option value="">-- Pilih Tipe --</option>
                        <option value="pusat">Pusat (Head Office)</option>
                        <option value="kcu">KCU (Kantor Cabang Utama)</option>
                        <option value="cabang_a">Cabang A</option>
                        <option value="cabang_b">Cabang B</option>
                        <option value="cabang_pembantu">Cabang Pembantu</option>
                        <option value="anak_cabang">Anak Cabang</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Induk Cabang (opsional)</label>
                    <select name="parent_id" class="form-select">
                        <option value="">-- Tidak Ada (Cabang Utama) --</option>
                        @foreach($cabangs as $c)
                        <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalTambah').classList.remove('open')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
