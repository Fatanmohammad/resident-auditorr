@extends('layouts.app')
@section('title', 'Parameter RKAT RA')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Parameter RKAT RA</h1>
        <p>Kelola parameter penilaian dan bobot scoring audit</p>
    </div>
    @if(in_array(auth()->user()->role, ['kadiv_skai','kabag_ra']))
    <button class="btn btn-primary" onclick="document.getElementById('modalTambah').classList.add('open')">
        <i class="bi bi-plus-lg"></i> Tambah Parameter
    </button>
    @endif
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Parameter</th>
                    <th>Bobot (%)</th>
                    <th>Deskripsi</th>
                    @if(in_array(auth()->user()->role, ['kadiv_skai','kabag_ra']))
                    <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($parameters as $i => $param)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $param->nama_parameter }}</strong></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="flex: 1; height: 6px; background: var(--border-color); border-radius: 3px; max-width: 80px;">
                                <div style="width: {{ $param->bobot }}%; height: 100%; background: var(--bs-blue); border-radius: 3px;"></div>
                            </div>
                            <span style="font-weight: 600;">{{ $param->bobot }}%</span>
                        </div>
                    </td>
                    <td style="color: var(--text-muted);">{{ $param->deskripsi ?? '-' }}</td>
                    @if(in_array(auth()->user()->role, ['kadiv_skai','kabag_ra']))
                    <td>
                        <button class="btn btn-outline btn-sm" onclick="openEdit({{ $param->id }}, '{{ addslashes($param->nama_parameter) }}', {{ $param->bobot }}, '{{ addslashes($param->deskripsi ?? '') }}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('parameter.destroy', $param->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus parameter ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="5"><div class="empty-state"><i class="bi bi-sliders"></i><p>Belum ada parameter. Tambahkan parameter RKAT.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(in_array(auth()->user()->role, ['kadiv_skai','kabag_ra']))
{{-- Modal Tambah --}}
<div class="modal-overlay" id="modalTambah">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Tambah Parameter</div>
            <button class="modal-close" onclick="document.getElementById('modalTambah').classList.remove('open')">&times;</button>
        </div>
        <form action="{{ route('parameter.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Parameter</label>
                    <input type="text" name="nama_parameter" class="form-input" required placeholder="cth: Profil Risiko Kepatuhan">
                </div>
                <div class="form-group">
                    <label class="form-label">Bobot (%)</label>
                    <input type="number" name="bobot" class="form-input" min="0" max="100" step="0.01" required placeholder="0 - 100">
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-textarea" placeholder="Deskripsi parameter..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalTambah').classList.remove('open')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal-overlay" id="modalEdit">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Edit Parameter</div>
            <button class="modal-close" onclick="document.getElementById('modalEdit').classList.remove('open')">&times;</button>
        </div>
        <form id="formEdit" method="POST">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Parameter</label>
                    <input type="text" name="nama_parameter" id="editNama" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Bobot (%)</label>
                    <input type="number" name="bobot" id="editBobot" class="form-input" min="0" max="100" step="0.01" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" id="editDeskripsi" class="form-textarea"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalEdit').classList.remove('open')">Batal</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function openEdit(id, nama, bobot, deskripsi) {
    document.getElementById('formEdit').action = '/parameters/' + id;
    document.getElementById('editNama').value = nama;
    document.getElementById('editBobot').value = bobot;
    document.getElementById('editDeskripsi').value = deskripsi;
    document.getElementById('modalEdit').classList.add('open');
}
</script>
@endpush
