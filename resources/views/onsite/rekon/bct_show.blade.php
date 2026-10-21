@extends('layouts.app')
@section('content')
@php
    $userRole  = strtolower(auth()->user()->role);
    $isAdmin   = in_array($userRole, ['admin','kabag_ra','kadiv_skai']);
    $isApproved= $rekon->status === 'Approved';
    $total     = $items->count();
    $sesuai    = $items->where('status_review','Sesuai')->count();
    $tidakSesuai = $items->where('status_review','Tidak Sesuai')->count();
    $belum     = $items->where('status_review','Belum Diuji')->count();
@endphp

<style>
    :root { --primary:#1e3a8a; --border:#e2e8f0; --muted:#64748b; --dark:#1e293b; }
    .stat-box { background:#fff; border:1px solid var(--border); border-radius:10px; padding:1rem 1.25rem; }
    .tbl { width:100%; border-collapse:collapse; font-size:0.83rem; }
    .tbl th { padding:0.75rem 0.875rem; background:#f8fafc; color:var(--muted); font-weight:600; font-size:0.72rem; text-transform:uppercase; border-bottom:1px solid var(--border); }
    .tbl td { padding:0.75rem 0.875rem; border-bottom:1px solid var(--border); vertical-align:middle; }
    .tbl tbody tr:hover { background:#f8fafc; cursor:pointer; }
    .badge-status { padding:0.2rem 0.6rem; border-radius:20px; font-size:0.72rem; font-weight:600; }
    .f-label { font-size:0.8rem; font-weight:600; color:var(--dark); margin-bottom:0.35rem; display:block; }
    .f-ctrl { width:100%; border:1px solid var(--border); padding:0.5rem 0.7rem; font-size:0.84rem; border-radius:6px; box-sizing:border-box; }
    .f-ctrl:disabled { background:#f8fafc; color:var(--muted); }
    .btn-primary-sm { background:var(--primary); color:#fff; border:none; padding:0.4rem 1.2rem; border-radius:6px; font-weight:600; font-size:0.84rem; cursor:pointer; }
    .btn-success-sm { background:#10b981; color:#fff; border:none; padding:0.4rem 1.2rem; border-radius:6px; font-weight:600; font-size:0.84rem; cursor:pointer; }
    .section-ra  { border:1px solid #fde68a; border-radius:8px; overflow:hidden; margin-bottom:1rem; }
    .section-ra .hdr  { background:#fffbeb; padding:0.75rem 1rem; font-weight:700; font-size:0.85rem; color:#b45309; border-bottom:1px solid #fde68a; }
    .section-adm { border:1px solid #a7f3d0; border-radius:8px; overflow:hidden; }
    .section-adm .hdr { background:#ecfdf5; padding:0.75rem 1rem; font-weight:700; font-size:0.85rem; color:#047857; border-bottom:1px solid #a7f3d0; }
</style>

{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.25rem; flex-wrap:wrap; gap:0.75rem;">
    <div>
        <a href="{{ route('onsite.kka', $visit) }}" style="font-size:0.85rem; color:var(--muted); text-decoration:none;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke KKA Onsite
        </a>
        <h4 style="font-weight:700; color:var(--dark); margin:0.4rem 0 0.2rem;">Rekon Barang Cetakan</h4>
        <p style="color:var(--muted); font-size:0.88rem; margin:0;">
            {{ $visit->kode_unit }} — {{ $visit->nama_unit }} &nbsp;|&nbsp;
            Tanggal Opname: {{ \Carbon\Carbon::parse($rekon->tanggal_opname)->format('d M Y') }} &nbsp;|&nbsp;
            RA: {{ $rekon->ra_pelaksana }}
        </p>
    </div>
    @if(!$isApproved)
    <a href="{{ route('onsite.rekon.bct.create', $visit) }}" class="btn btn-sm btn-outline-secondary" style="font-size:0.82rem;">
        <i class="bi bi-arrow-clockwise me-1"></i> Buat Ulang
    </a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success" style="font-size:0.88rem;">{{ session('success') }}</div>
@endif

{{-- Summary Cards --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem;">
    <div class="stat-box">
        <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Total Item</div>
        <div style="font-size:1.5rem; font-weight:700; color:var(--dark);">{{ $total }}</div>
    </div>
    <div class="stat-box">
        <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Sesuai</div>
        <div style="font-size:1.5rem; font-weight:700; color:#16a34a;">{{ $sesuai }}</div>
    </div>
    <div class="stat-box">
        <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Tidak Sesuai</div>
        <div style="font-size:1.5rem; font-weight:700; color:#dc2626;">{{ $tidakSesuai }}</div>
    </div>
    <div class="stat-box">
        <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Belum Diuji</div>
        <div style="font-size:1.5rem; font-weight:700; color:#ca8a04;">{{ $belum }}</div>
    </div>
</div>

{{-- Tabel --}}
<div style="background:#fff; border:1px solid var(--border); border-radius:10px; overflow:hidden;">
    <div style="padding:0.875rem 1.25rem; border-bottom:1px solid var(--border); font-weight:700; color:var(--primary); font-size:0.9rem;">
        Checklist Barang Cetakan
    </div>
    <div style="overflow-x:auto;">
        <table class="tbl">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center;">No</th>
                    <th>Kategori</th>
                    <th>Nama Barang</th>
                    <th style="text-align:center;">Satuan</th>
                    <th style="text-align:center;">Stok Laporan</th>
                    <th style="text-align:center;">Fisik</th>
                    <th style="text-align:center;">Selisih</th>
                    <th style="text-align:center;">Kondisi</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Risk</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $item)
                @php
                    $selisih = ($item->hasil_fisik ?? 0) - ($item->stok_laporan ?? 0);
                    $statusColor = match($item->status_review) {
                        'Sesuai'       => ['bg'=>'#f0fdf4','color'=>'#16a34a'],
                        'Tidak Sesuai' => ['bg'=>'#fef2f2','color'=>'#dc2626'],
                        'Tidak Berlaku'=> ['bg'=>'#f1f5f9','color'=>'#64748b'],
                        default        => ['bg'=>'#fffbeb','color'=>'#ca8a04'],
                    };
                    $riskColor = match($item->risk_level) {
                        'Critical' => '#7c3aed', 'High' => '#dc2626',
                        'Moderate' => '#ea580c', 'Low'  => '#16a34a',
                        default    => '#94a3b8',
                    };
                @endphp
                <tr onclick="openModal({{ $item->id }})" style="cursor:pointer;">
                    <td style="text-align:center; color:var(--muted);">{{ $i+1 }}</td>
                    <td><span style="font-size:0.75rem; background:#f1f5f9; padding:0.15rem 0.5rem; border-radius:10px;">{{ $item->kategori }}</span></td>
                    <td style="font-weight:500;">{{ $item->nama_barang }}</td>
                    <td style="text-align:center; color:var(--muted);">{{ $item->satuan }}</td>
                    <td style="text-align:center;">{{ $item->stok_laporan ?? '—' }}</td>
                    <td style="text-align:center;">{{ $item->hasil_fisik ?? '—' }}</td>
                    <td style="text-align:center; font-weight:700; color:{{ $selisih != 0 ? '#dc2626' : '#16a34a' }};">
                        {{ $item->stok_laporan !== null && $item->hasil_fisik !== null ? ($selisih > 0 ? '+' : '') . $selisih : '—' }}
                    </td>
                    <td style="text-align:center; font-size:0.78rem;">{{ $item->kondisi ?? '—' }}</td>
                    <td style="text-align:center;">
                        <span class="badge-status" style="background:{{ $statusColor['bg'] }}; color:{{ $statusColor['color'] }};">
                            {{ $item->status_review }}
                        </span>
                    </td>
                    <td style="text-align:center; font-weight:700; font-size:0.78rem; color:{{ $riskColor }};">
                        {{ $item->risk_level ?? '—' }}
                    </td>
                    <td style="text-align:right;">
                        <button type="button" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;"
                            onclick="event.stopPropagation(); openModal({{ $item->id }})">
                            {{ $isApproved ? 'Lihat' : 'Input' }}
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL --}}
<div id="itemModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; overflow-y:auto; padding:2rem 1rem;">
    <div style="background:#fff; border-radius:12px; max-width:700px; margin:0 auto; overflow:hidden;">
        <div style="padding:1rem 1.5rem; background:#f8fafc; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-weight:700; color:var(--primary);" id="modal_nama_barang">—</div>
                <div style="font-size:0.78rem; color:var(--muted);" id="modal_procedure_id">—</div>
            </div>
            <button onclick="closeModal()" style="background:none; border:none; font-size:1.3rem; cursor:pointer; color:var(--muted);">&times;</button>
        </div>

        <div style="padding:1.5rem;">
            {{-- Section RA --}}
            <div class="section-ra">
                <div class="hdr"><i class="bi bi-pencil-square me-1"></i> Input RA</div>
                <div style="padding:1rem;">
                    <input type="hidden" id="modal_item_id">
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                        <div>
                            <label class="f-label">Stok Laporan</label>
                            <input type="number" id="m_stok_laporan" class="f-ctrl" placeholder="0" {{ $isApproved ? 'disabled' : '' }}>
                        </div>
                        <div>
                            <label class="f-label">Hasil Fisik</label>
                            <input type="number" id="m_hasil_fisik" class="f-ctrl" placeholder="0" {{ $isApproved ? 'disabled' : '' }}>
                        </div>
                        <div>
                            <label class="f-label">Kondisi</label>
                            <select id="m_kondisi" class="f-ctrl" {{ $isApproved ? 'disabled' : '' }}>
                                <option value="">— Pilih —</option>
                                <option value="Baik">Baik</option>
                                <option value="Rusak">Rusak</option>
                                <option value="Outdated">Outdated</option>
                                <option value="N/A">N/A</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom:0.75rem;">
                        <label class="f-label">Status Uji</label>
                        <select id="m_status_review" class="f-ctrl" onchange="toggleException()" {{ $isApproved ? 'disabled' : '' }}>
                            <option value="Belum Diuji">Belum Diuji</option>
                            <option value="Sesuai">Sesuai</option>
                            <option value="Tidak Sesuai">Tidak Sesuai</option>
                            <option value="Tidak Berlaku">Tidak Berlaku</option>
                        </select>
                    </div>
                    <div id="exceptionFields" style="display:none;">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                            <div>
                                <label class="f-label">Kriteria Tidak Dipenuhi</label>
                                <textarea id="m_kriteria" class="f-ctrl" rows="2" {{ $isApproved ? 'disabled' : '' }}></textarea>
                            </div>
                            <div>
                                <label class="f-label">Penyebab</label>
                                <textarea id="m_penyebab" class="f-ctrl" rows="2" {{ $isApproved ? 'disabled' : '' }}></textarea>
                            </div>
                        </div>
                        <div style="margin-bottom:0.75rem;">
                            <label class="f-label">Dampak</label>
                            <textarea id="m_dampak" class="f-ctrl" rows="2" {{ $isApproved ? 'disabled' : '' }}></textarea>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                            <div>
                                <label class="f-label">Impact (1-5)</label>
                                <select id="m_impact" class="f-ctrl" onchange="hitungRisk()" {{ $isApproved ? 'disabled' : '' }}>
                                    @foreach(range(1,5) as $n)<option value="{{ $n }}">{{ $n }}</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label class="f-label">Likelihood (1-5)</label>
                                <select id="m_likelihood" class="f-ctrl" onchange="hitungRisk()" {{ $isApproved ? 'disabled' : '' }}>
                                    @foreach(range(1,5) as $n)<option value="{{ $n }}">{{ $n }}</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label class="f-label">Risk Level</label>
                                <div class="f-ctrl" style="background:#f8fafc; display:flex; align-items:center; gap:0.5rem;">
                                    <span id="m_risk_score" style="font-weight:700;">1</span> —
                                    <span id="m_risk_badge" style="padding:0.15rem 0.5rem; border-radius:6px; font-size:0.75rem; font-weight:600; background:#dcfce7; color:#16a34a;">Low</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                        <div>
                            <label class="f-label">Referensi Bukti</label>
                            <input type="text" id="m_referensi_bukti" class="f-ctrl" {{ $isApproved ? 'disabled' : '' }}>
                        </div>
                        <div>
                            <label class="f-label">Tanggal Uji</label>
                            <input type="date" id="m_tanggal_uji" class="f-ctrl" {{ $isApproved ? 'disabled' : '' }}>
                        </div>
                    </div>
                    <div style="margin-bottom:0.75rem;">
                        <label class="f-label">Simpulan RA</label>
                        <textarea id="m_simpulan_ra" class="f-ctrl" rows="2" {{ $isApproved ? 'disabled' : '' }}></textarea>
                    </div>
                    @if(!$isApproved)
                    <div style="display:flex; justify-content:flex-end;">
                        <button class="btn-primary-sm" onclick="submitItem()">
                            <i class="bi bi-send-fill me-1"></i> Simpan
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Section Admin --}}
            @if($isAdmin)
            <div class="section-adm">
                <div class="hdr"><i class="bi bi-shield-check me-1"></i> Review Admin</div>
                <div style="padding:1rem;">
                    <input type="hidden" id="modal_item_id_adm">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                        <div>
                            <label class="f-label">Status Review</label>
                            <select id="m_adm_status" class="f-ctrl">
                                <option value="Belum Diuji">Belum Diuji</option>
                                <option value="Sesuai">Sesuai</option>
                                <option value="Tidak Sesuai">Tidak Sesuai</option>
                                <option value="Tidak Berlaku">Tidak Berlaku</option>
                            </select>
                        </div>
                        <div>
                            <label class="f-label">Catatan Reviewer</label>
                            <textarea id="m_adm_catatan" class="f-ctrl" rows="2"></textarea>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:flex-end;">
                        <button class="btn-success-sm" onclick="submitReview()">
                            <i class="bi bi-check-circle-fill me-1"></i> Simpan Review
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
const baseUrl   = '{{ url("/onsite/rekon/bct/item") }}';
const items     = @json($items);

function openModal(id) {
    const item = items.find(x => x.id == id);
    if (!item) return;

    document.getElementById('modal_item_id').value     = item.id;
    document.getElementById('modal_item_id_adm') && (document.getElementById('modal_item_id_adm').value = item.id);
    document.getElementById('modal_nama_barang').innerText  = item.nama_barang;
    document.getElementById('modal_procedure_id').innerText = item.procedure_id || '';

    document.getElementById('m_stok_laporan').value    = item.stok_laporan ?? '';
    document.getElementById('m_hasil_fisik').value     = item.hasil_fisik ?? '';
    document.getElementById('m_kondisi').value         = item.kondisi || '';
    document.getElementById('m_status_review').value   = item.status_review || 'Belum Diuji';
    document.getElementById('m_kriteria').value        = item.kriteria_tidak_dipenuhi || '';
    document.getElementById('m_penyebab').value        = item.penyebab || '';
    document.getElementById('m_dampak').value          = item.dampak || '';
    document.getElementById('m_impact').value          = item.impact || 1;
    document.getElementById('m_likelihood').value      = item.likelihood || 1;
    document.getElementById('m_referensi_bukti').value = item.referensi_bukti || '';
    document.getElementById('m_tanggal_uji').value     = item.tanggal_uji || '';
    document.getElementById('m_simpulan_ra').value     = item.simpulan_ra || '';

    const admStatus = document.getElementById('m_adm_status');
    if (admStatus) admStatus.value = item.status_review || 'Belum Diuji';
    const admCatatan = document.getElementById('m_adm_catatan');
    if (admCatatan) admCatatan.value = item.catatan_reviewer || '';

    toggleException();
    hitungRisk();
    document.getElementById('itemModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('itemModal').style.display = 'none';
}

function toggleException() {
    const status = document.getElementById('m_status_review').value;
    document.getElementById('exceptionFields').style.display = status === 'Tidak Sesuai' ? 'block' : 'none';
}

function hitungRisk() {
    const d = parseInt(document.getElementById('m_impact').value) || 1;
    const k = parseInt(document.getElementById('m_likelihood').value) || 1;
    const s = d * k;
    let level = 'Low', bg = '#dcfce7', color = '#16a34a';
    if (s >= 20)      { level = 'Critical'; bg = '#ede9fe'; color = '#7c3aed'; }
    else if (s >= 12) { level = 'High';     bg = '#fef2f2'; color = '#dc2626'; }
    else if (s >= 6)  { level = 'Moderate'; bg = '#fffbeb'; color = '#ca8a04'; }
    document.getElementById('m_risk_score').innerText = s;
    const badge = document.getElementById('m_risk_badge');
    badge.innerText = level; badge.style.background = bg; badge.style.color = color;
}

function submitItem() {
    const id = document.getElementById('modal_item_id').value;
    const payload = new URLSearchParams({
        _token: csrfToken, _method: 'PATCH',
        stok_laporan:          document.getElementById('m_stok_laporan').value,
        hasil_fisik:           document.getElementById('m_hasil_fisik').value,
        kondisi:               document.getElementById('m_kondisi').value,
        status_review:         document.getElementById('m_status_review').value,
        kriteria_tidak_dipenuhi: document.getElementById('m_kriteria').value,
        penyebab:              document.getElementById('m_penyebab').value,
        dampak:                document.getElementById('m_dampak').value,
        impact:                document.getElementById('m_impact').value,
        likelihood:            document.getElementById('m_likelihood').value,
        referensi_bukti:       document.getElementById('m_referensi_bukti').value,
        tanggal_uji:           document.getElementById('m_tanggal_uji').value,
        simpulan_ra:           document.getElementById('m_simpulan_ra').value,
    });
    axios.post(`${baseUrl}/${id}`, payload, { headers: { 'X-CSRF-TOKEN': csrfToken } })
        .then(() => location.reload())
        .catch(err => alert(err.response?.data?.message || 'Gagal menyimpan.'));
}

function submitReview() {
    const id = document.getElementById('modal_item_id_adm').value;
    const payload = new URLSearchParams({
        _token: csrfToken, _method: 'PATCH',
        status_review:    document.getElementById('m_adm_status').value,
        catatan_reviewer: document.getElementById('m_adm_catatan').value,
    });
    axios.post(`${baseUrl}/${id}/review`, payload, { headers: { 'X-CSRF-TOKEN': csrfToken } })
        .then(() => location.reload())
        .catch(err => alert(err.response?.data?.message || 'Gagal menyimpan review.'));
}

document.getElementById('itemModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endpush
@endsection
