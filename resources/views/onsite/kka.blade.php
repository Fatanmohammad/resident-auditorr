@extends('layouts.app')

@section('content')

@php $userRole = strtolower(auth()->user()->role); @endphp

<style>
    :root { --primary: #1e3a8a; --border: #e2e8f0; --muted: #64748b; --dark: #1e293b; }
    .stat-card { background:#fff; border:1px solid var(--border); border-radius:12px; padding:1.25rem; display:flex; align-items:flex-start; gap:1rem; }
    .stat-icon { width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; flex-shrink:0; }
    .kka-tabs { display:flex; gap:0.5rem; overflow-x:auto; padding-bottom:0.5rem; margin-bottom:1rem; border-bottom:1px solid var(--border); flex-wrap:nowrap; }
    .kka-tab-item { background:transparent; padding:0.6rem 1rem; font-size:0.85rem; font-weight:600; color:var(--muted); display:flex; align-items:center; gap:0.5rem; cursor:pointer; border-bottom:3px solid transparent; white-space:nowrap; flex-shrink:0; border-top:none; border-left:none; border-right:none; }
    .kka-tab-item.active { color:var(--primary); border-bottom-color:var(--primary); }
    .kka-tab-item .badge-count { background:#f1f5f9; color:#475569; padding:0.1rem 0.5rem; border-radius:10px; font-size:0.7rem; }
    .kka-tab-item.active .badge-count { background:#dbeafe; color:var(--primary); }
    .data-table { width:100%; border-collapse:collapse; min-width:900px; }
    .data-table th { padding:0.875rem 1rem; background:#f8fafc; font-size:0.75rem; color:var(--muted); border-bottom:1px solid var(--border); text-transform:uppercase; font-weight:600; }
    .data-table td { padding:0.875rem 1rem; vertical-align:middle; border-bottom:1px solid var(--border); font-size:0.84rem; }
    .data-table tbody tr:hover { background:#f8fafc; cursor:pointer; }
    .btn-review { background:var(--primary); color:#fff; font-size:0.8rem; font-weight:600; padding:0.35rem 0.9rem; border-radius:6px; border:none; cursor:pointer; }
    .btn-back { background:#fff; border:1px solid var(--border); padding:0.4rem 1rem; border-radius:6px; font-weight:600; font-size:0.85rem; color:var(--dark); cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem; }
    .sum-label { font-size:0.7rem; color:var(--muted); text-transform:uppercase; font-weight:600; margin-bottom:0.3rem; }
    .sum-val { font-weight:700; font-size:0.95rem; color:var(--dark); }
    .section-card { background:#fff; border:1px solid var(--border); border-radius:8px; margin-bottom:1.5rem; overflow:hidden; }
    .section-card-header { padding:1rem 1.5rem; font-weight:700; font-size:0.9rem; display:flex; align-items:center; gap:0.5rem; background:#f8fafc; border-bottom:1px solid var(--border); justify-content:space-between; }
    .section-card-body { padding:1.5rem; }
    .section-card.ra { border-color:#fde68a; }
    .section-card.ra .section-card-header { background:#fffbeb; border-bottom-color:#fde68a; color:#b45309; }
    .section-card.adm { border-color:#a7f3d0; }
    .section-card.adm .section-card-header { background:#ecfdf5; border-bottom-color:#a7f3d0; color:#047857; }
    .f-label { font-size:0.8rem; font-weight:600; color:var(--dark); margin-bottom:0.4rem; display:block; }
    .f-control { width:100%; border:1px solid var(--border); padding:0.55rem 0.75rem; font-size:0.85rem; border-radius:6px; box-sizing:border-box; background:#fff; }
    .f-control:focus { outline:none; border-color:var(--primary); }
    .f-control:disabled { background:#f8fafc; color:var(--muted); cursor:not-allowed; }
    .btn-submit-ra  { background:#f59e0b; color:#fff; border:none; padding:0.45rem 1.5rem; border-radius:6px; font-weight:600; cursor:pointer; }
    .btn-submit-adm { background:#10b981; color:#fff; border:none; padding:0.45rem 1.5rem; border-radius:6px; font-weight:600; cursor:pointer; }
</style>

{{-- ============================= DASHBOARD VIEW ============================= --}}
<div id="dashboardView">

    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; flex-wrap:wrap; gap:0.75rem;">
        <div>
            <a href="{{ route('onsite.index') }}" style="font-size:0.85rem; color:var(--muted); text-decoration:none;">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kunjungan
            </a>
            <h4 style="font-weight:700; color:var(--dark); margin:0.4rem 0 0.2rem;">KKA Onsite — {{ $visit->kode_unit }}</h4>
            <p style="color:var(--muted); font-size:0.88rem; margin:0;">
                {{ $visit->nama_unit }} &nbsp;|&nbsp;
                {{ \Carbon\Carbon::parse($visit->tanggal_mulai)->format('d M Y') }} s/d
                {{ \Carbon\Carbon::parse($visit->tanggal_selesai)->format('d M Y') }}
            </p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem;">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f1f5f9; color:#475569;"><i class="bi bi-file-earmark-text"></i></div>
            <div>
                <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Total Sampel</div>
                <div style="font-size:1.4rem; font-weight:700; color:var(--dark);" id="statTotal">0</div>
                <div style="font-size:0.75rem; color:var(--muted);">Transaksi CBS</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef2f2; color:#dc2626;"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">High Risk</div>
                <div style="font-size:1.4rem; font-weight:700; color:#dc2626;" id="statHigh">0</div>
                <div style="font-size:0.75rem; color:var(--muted);">Perlu Atensi</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fffbeb; color:#ca8a04;"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Belum Direview</div>
                <div style="font-size:1.4rem; font-weight:700; color:#ca8a04;" id="statBelum">0</div>
                <div style="font-size:0.75rem; color:var(--muted);">Menunggu Review</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4; color:#16a34a;"><i class="bi bi-check2-circle"></i></div>
            <div>
                <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Approved</div>
                <div style="font-size:1.4rem; font-weight:700; color:#16a34a;" id="statApproved">0</div>
                <div style="font-size:0.75rem; color:var(--muted);">Selesai Direview</div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="kka-tabs">
        <button class="kka-tab-item active" onclick="switchTab(this,'sampling')">
            <i class="bi bi-file-earmark-bar-graph"></i> KKA Sampling CBS <span class="badge-count" id="tabCountSampling">0</span>
        </button>
        <button class="kka-tab-item" onclick="switchTab(this,'observasi')">
            <i class="bi bi-building-check"></i> Observasi Lingkungan
        </button>
        <button class="kka-tab-item" onclick="switchTab(this,'cash_opname')">
            <i class="bi bi-cash-stack"></i> Cash Opname
        </button>
        <button class="kka-tab-item" onclick="switchTab(this,'rekonsiliasi')">
            <i class="bi bi-arrow-left-right"></i> Rekonsiliasi
        </button>
    </div>

    {{-- Tab Content --}}
    <div id="tabSampling">
        {{-- Badge jenis sampling --}}
        <div style="display:flex; gap:0.75rem; margin-bottom:1.25rem; flex-wrap:wrap;">
            <div style="background:#fef2f2; border:1px solid #dc262640; border-radius:8px; padding:0.5rem 1rem; min-width:110px;">
                <div style="font-size:0.72rem; color:#dc2626; font-weight:600;">Mandatory</div>
                <div style="font-size:1.3rem; font-weight:700; color:#dc2626;" id="cntMandatory">0</div>
            </div>
            <div style="background:#fff7ed; border:1px solid #ea580c40; border-radius:8px; padding:0.5rem 1rem; min-width:110px;">
                <div style="font-size:0.72rem; color:#ea580c; font-weight:600;">Certainty</div>
                <div style="font-size:1.3rem; font-weight:700; color:#ea580c;" id="cntCertainty">0</div>
            </div>
            <div style="background:#fefce8; border:1px solid #ca8a0440; border-radius:8px; padding:0.5rem 1rem; min-width:110px;">
                <div style="font-size:0.72rem; color:#ca8a04; font-weight:600;">Targeted</div>
                <div style="font-size:1.3rem; font-weight:700; color:#ca8a04;" id="cntTargeted">0</div>
            </div>
            <div style="background:#f0fdf4; border:1px solid #16a34a40; border-radius:8px; padding:0.5rem 1rem; min-width:110px;">
                <div style="font-size:0.72rem; color:#16a34a; font-weight:600;">Initial</div>
                <div style="font-size:1.3rem; font-weight:700; color:#16a34a;" id="cntInitial">0</div>
            </div>
        </div>

        <div class="card" style="border-radius:10px; border:1px solid var(--border); overflow:hidden;">
            <div style="padding:0.875rem 1.25rem; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; background:#fff;">
                <div style="font-weight:700; color:var(--primary); font-size:0.9rem;">Daftar Sampel CBS</div>
                <div style="font-size:0.78rem; color:var(--muted);"><span id="totalCount">0</span> sampel</div>
            </div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; width:5%;">No</th>
                            <th>User / Score</th>
                            <th>Keterangan Transaksi</th>
                            <th style="text-align:center;">Jenis</th>
                            <th style="text-align:right;">Nominal</th>
                            <th style="text-align:center;">Risk</th>
                            <th style="text-align:center;">Status Review</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="kkaTableBody">
                        <tr><td colspan="8" style="text-align:center; padding:3rem; color:var(--muted);">
                            <i class="bi bi-hourglass-split" style="font-size:2rem; display:block; margin-bottom:0.75rem;"></i>
                            Memuat data...
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="tabObservasi" style="display:none;">
        @php $observasi = \App\Models\Onsite\OnsiteObservasi::where('onsite_visit_id', $visit->id)->first(); @endphp
        @if($observasi)
            <div style="text-align:center; padding:2rem;">
                <a href="{{ route('onsite.observasi.show', [$visit, $observasi]) }}" class="btn btn-sm btn-primary" style="background:var(--primary); border-color:var(--primary);">
                    <i class="bi bi-building-check me-1"></i> Buka Checklist Observasi ({{ $observasi->items()->count() }} item)
                </a>
            </div>
        @else
            <div style="text-align:center; padding:3rem 1rem;">
                <i class="bi bi-building" style="font-size:2.5rem; color:#cbd5e1; display:block; margin-bottom:1rem;"></i>
                <p style="color:var(--muted); margin-bottom:1.25rem;">Checklist observasi belum dibuat.</p>
                <a href="{{ route('onsite.observasi.create', $visit) }}" class="btn btn-sm btn-primary" style="background:var(--primary); border-color:var(--primary);">
                    <i class="bi bi-plus-circle me-1"></i> Buat Checklist Observasi
                </a>
            </div>
        @endif
    </div>

    <div id="tabCashOpname" style="display:none; text-align:center; padding:3rem 1rem;">
        <i class="bi bi-tools" style="font-size:2.5rem; color:#cbd5e1; display:block; margin-bottom:1rem;"></i>
        <p style="color:var(--muted); font-weight:600;">Modul Cash Opname sedang dalam pengembangan.</p>
    </div>

    <div id="tabRekonsiliasi" style="display:none; text-align:center; padding:3rem 1rem;">
        <i class="bi bi-tools" style="font-size:2.5rem; color:#cbd5e1; display:block; margin-bottom:1rem;"></i>
        <p style="color:var(--muted); font-weight:600;">Modul Rekonsiliasi sedang dalam pengembangan.</p>
    </div>

</div>{{-- end dashboardView --}}

{{-- ============================= DETAIL VIEW ============================= --}}
<div id="detailView" style="display:none;">

    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; flex-wrap:wrap; gap:0.75rem;">
        <div style="display:flex; gap:1rem; align-items:center;">
            <button class="btn-back" onclick="closeDetail()">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Sampel
            </button>
            <div>
                <h5 style="margin:0; font-weight:700; color:var(--primary);">Review KKA — <span id="det_jenis"></span></h5>
                <div style="font-size:0.82rem; color:var(--muted); margin-top:0.2rem;">ID: <span id="det_id"></span></div>
            </div>
        </div>
        <div id="det_status_badge" style="font-size:0.85rem; font-weight:600; color:var(--muted);"></div>
    </div>

    {{-- Summary Box --}}
    <div style="background:#fff; border:1px solid var(--border); border-radius:10px; padding:1.25rem; margin-bottom:1.5rem;">
        <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:1.25rem; margin-bottom:1rem;">
            <div>
                <div class="sum-label">User / Teller</div>
                <div class="sum-val" id="det_kd_user">—</div>
            </div>
            <div>
                <div class="sum-label">Tanggal Transaksi</div>
                <div class="sum-val" id="det_tgl_tx">—</div>
            </div>
            <div>
                <div class="sum-label">No Arsip</div>
                <div class="sum-val" id="det_no_arsip">—</div>
            </div>
            <div>
                <div class="sum-label">Nominal</div>
                <div class="sum-val" style="color:#dc2626; font-size:1.05rem;" id="det_nominal">—</div>
            </div>
            <div>
                <div class="sum-label">Jenis Sampling</div>
                <div id="det_jenis_badge">—</div>
            </div>
        </div>
        <div style="border-top:1px solid var(--border); padding-top:0.75rem; font-size:0.88rem;">
            <strong>Keterangan:</strong> <span id="det_ket_tx" style="color:var(--muted);">—</span>
            &nbsp;|&nbsp; <strong>Alasan Sampling:</strong> <span id="det_alasan" style="color:var(--muted);">—</span>
        </div>
    </div>

    {{-- Section RA --}}
    <div class="section-card ra">
        <div class="section-card-header">
            <div><i class="bi bi-pencil-square"></i> Hasil Pengujian <span style="font-weight:normal; font-size:0.8rem;">(Porsi Resident Auditor)</span></div>
            <span id="badge_ra_lock" style="font-size:0.75rem; background:#fff; padding:0.2rem 0.6rem; border-radius:12px; border:1px solid #fde68a; color:var(--muted);">Mode Edit</span>
        </div>
        <div class="section-card-body">
            <input type="hidden" id="ra_finding_id">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label class="f-label">Hasil Uji</label>
                    <textarea id="ra_hasil_uji" rows="3" class="f-control" placeholder="Rincian fakta temuan..."></textarea>
                </div>
                <div>
                    <label class="f-label">Bukti Referensi</label>
                    <textarea id="ra_bukti_referensi" rows="3" class="f-control" placeholder="Nomor dokumen, foto, dll..."></textarea>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1rem;">
                <div>
                    <label class="f-label">Skor Dampak (1–5)</label>
                    <select id="ra_skor_dampak" class="f-control" onchange="hitungRisk()">
                        <option value="1">1 — Sangat Rendah</option>
                        <option value="2">2 — Rendah</option>
                        <option value="3">3 — Sedang</option>
                        <option value="4">4 — Tinggi</option>
                        <option value="5">5 — Sangat Tinggi</option>
                    </select>
                </div>
                <div>
                    <label class="f-label">Skor Kemungkinan (1–5)</label>
                    <select id="ra_skor_kemungkinan" class="f-control" onchange="hitungRisk()">
                        <option value="1">1 — Sangat Jarang</option>
                        <option value="2">2 — Jarang</option>
                        <option value="3">3 — Kadang-Kadang</option>
                        <option value="4">4 — Sering</option>
                        <option value="5">5 — Sangat Sering</option>
                    </select>
                </div>
                <div>
                    <label class="f-label">Risk Level (otomatis)</label>
                    <div class="f-control" style="background:#f8fafc; display:flex; align-items:center; gap:0.5rem;">
                        <span id="ra_risk_score" style="font-weight:700;">1</span> —
                        <span id="ra_risk_badge" style="padding:0.15rem 0.5rem; border-radius:6px; font-size:0.75rem; font-weight:600; background:#dcfce7; color:#16a34a;">Low</span>
                    </div>
                </div>
                <div>
                    <label class="f-label">Tanggal Ditemukan</label>
                    <input type="date" id="ra_tanggal_ditemukan" class="f-control">
                </div>
            </div>

            <div style="margin-bottom:1rem;">
                <label class="f-label">Simpulan RA</label>
                <textarea id="ra_simpulan_ra" rows="2" class="f-control" placeholder="Simpulan akhir pemeriksaan..."></textarea>
            </div>

            <div id="ra_actions" style="display:flex; justify-content:flex-end; gap:0.75rem; border-top:1px solid var(--border); padding-top:1rem;">
                <button class="btn-back" onclick="closeDetail()">Batal</button>
                <button class="btn-submit-ra" onclick="submitRa()">
                    <i class="bi bi-send-fill me-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>

    {{-- Section Admin --}}
    @if(in_array($userRole, ['admin','kabag_ra','kadiv_skai']))
    <div class="section-card adm">
        <div class="section-card-header">
            <div><i class="bi bi-shield-check"></i> Review &amp; Keputusan Pengawasan <span style="font-weight:normal; font-size:0.8rem;">(Porsi Admin / Pimsie)</span></div>
            <span style="font-size:0.75rem; background:#fff; padding:0.2rem 0.6rem; border-radius:12px; border:1px solid #a7f3d0; color:var(--muted);">Mode Edit</span>
        </div>
        <div class="section-card-body">
            <input type="hidden" id="adm_finding_id">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label class="f-label">Status Review <span style="color:#dc2626;">*</span></label>
                    <select id="adm_status_review" class="f-control">
                        <option value="Belum Direview">Belum Direview</option>
                        <option value="Revisi">Revisi</option>
                        <option value="Approved">Approved</option>
                    </select>
                </div>
                <div>
                    <label class="f-label">Catatan Reviewer</label>
                    <textarea id="adm_catatan_reviewer" rows="2" class="f-control" placeholder="Instruksi revisi atau catatan persetujuan..."></textarea>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:0.75rem; border-top:1px solid var(--border); padding-top:1rem;">
                <button class="btn-back" onclick="closeDetail()">Batal</button>
                <button class="btn-submit-adm" onclick="submitAdm()">
                    <i class="bi bi-check-circle-fill me-1"></i> Simpan Review
                </button>
            </div>
        </div>
    </div>
    @endif

</div>{{-- end detailView --}}

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
const userRole  = '{{ $userRole }}';
const visitId   = {{ $visit->id }};
const dataUrl   = '{{ route("onsite.kka.data", $visit) }}';
const kkaUrl    = '{{ url("/onsite/kka") }}';

const badgeMap = {
    'Mandatory': { bg: '#fef2f2', color: '#dc2626' },
    'Certainty': { bg: '#fff7ed', color: '#ea580c' },
    'Targeted':  { bg: '#fefce8', color: '#ca8a04' },
    'Initial':   { bg: '#f0fdf4', color: '#16a34a' },
};

let globalFindings = [];
let currentTab = 'sampling';

document.addEventListener('DOMContentLoaded', () => loadData());

function loadData() {
    const tbody = document.getElementById('kkaTableBody');
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:3rem; color:#94a3b8;">
        <i class="bi bi-hourglass-split" style="font-size:2rem; display:block; margin-bottom:0.75rem;"></i>Memuat data...</td></tr>`;

    axios.get(dataUrl)
        .then(res => {
            globalFindings = res.data.data;
            updateStats();
            renderTable(globalFindings);
        })
        .catch(() => {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:3rem; color:#dc2626;">
                <i class="bi bi-exclamation-triangle" style="font-size:2rem; display:block; margin-bottom:0.75rem;"></i>Gagal memuat data.</td></tr>`;
        });
}

function updateStats() {
    const total    = globalFindings.length;
    const high     = globalFindings.filter(x => x.risk_level === 'High').length;
    const belum    = globalFindings.filter(x => x.status_review === 'Belum Direview').length;
    const approved = globalFindings.filter(x => x.status_review === 'Approved').length;

    document.getElementById('statTotal').innerText    = total;
    document.getElementById('statHigh').innerText     = high;
    document.getElementById('statBelum').innerText    = belum;
    document.getElementById('statApproved').innerText = approved;
    document.getElementById('totalCount').innerText   = total;
    document.getElementById('tabCountSampling').innerText = total;

    document.getElementById('cntMandatory').innerText = globalFindings.filter(x => x.jenis_sampling === 'Mandatory').length;
    document.getElementById('cntCertainty').innerText = globalFindings.filter(x => x.jenis_sampling === 'Certainty').length;
    document.getElementById('cntTargeted').innerText  = globalFindings.filter(x => x.jenis_sampling === 'Targeted').length;
    document.getElementById('cntInitial').innerText   = globalFindings.filter(x => x.jenis_sampling === 'Initial').length;
}

function renderTable(data) {
    const tbody = document.getElementById('kkaTableBody');
    tbody.innerHTML = '';

    if (!data || data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:3rem; color:#94a3b8;">
            <i class="bi bi-inbox" style="font-size:2.5rem; display:block; margin-bottom:0.75rem;"></i>Belum ada sampel.</td></tr>`;
        return;
    }

    data.forEach((f, i) => {
        const bm = badgeMap[f.jenis_sampling] || { bg: '#f8fafc', color: '#64748b' };
        const riskColor = { High: '#dc2626', Moderate: '#ea580c', Low: '#16a34a' }[f.risk_level] || '#94a3b8';
        const reviewColor = { Approved: '#16a34a', Revisi: '#ea580c' }[f.status_review] || '#94a3b8';
        const nominal = 'Rp ' + Number(f.jumlah_tx).toLocaleString('id-ID');

        tbody.insertAdjacentHTML('beforeend', `<tr onclick="openDetail(${f.id})" style="cursor:pointer;">
            <td style="text-align:center; color:var(--muted);">${i + 1}</td>
            <td>
                <div style="font-weight:600; color:var(--dark);">${f.kd_user || '—'}</div>
                <div style="font-size:0.75rem; color:#94a3b8;">Score: ${f.stable_score}</div>
            </td>
            <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${f.ket_tx || ''}">
                <div>${f.ket_tx || '—'}</div>
                <div style="font-size:0.75rem; color:#94a3b8;">${f.tgl_tx || ''}</div>
            </td>
            <td style="text-align:center;">
                <span style="background:${bm.bg}; color:${bm.color}; padding:0.2rem 0.6rem; border-radius:20px; font-size:0.72rem; font-weight:600;">${f.jenis_sampling}</span>
            </td>
            <td style="text-align:right; font-weight:600;">${nominal}</td>
            <td style="text-align:center; font-weight:700; font-size:0.8rem; color:${riskColor};">${f.risk_level || '—'}</td>
            <td style="text-align:center; font-size:0.78rem; font-weight:600; color:${reviewColor};">${f.status_review}</td>
            <td style="text-align:right;">
                <button class="btn-review" onclick="event.stopPropagation(); openDetail(${f.id})">
                    <i class="bi bi-arrow-right"></i> Review
                </button>
            </td>
        </tr>`);
    });
}

function switchTab(el, tab) {
    document.querySelectorAll('.kka-tab-item').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    currentTab = tab;

    document.getElementById('tabSampling').style.display    = tab === 'sampling'    ? 'block' : 'none';
    document.getElementById('tabObservasi').style.display   = tab === 'observasi'   ? 'block' : 'none';
    document.getElementById('tabCashOpname').style.display  = tab === 'cash_opname' ? 'block' : 'none';
    document.getElementById('tabRekonsiliasi').style.display= tab === 'rekonsiliasi'? 'block' : 'none';
}

function openDetail(id) {
    // Ambil data fresh dari server
    axios.get(dataUrl).then(res => {
        globalFindings = res.data.data;
        updateStats();
        const f = globalFindings.find(x => x.id == id);
        if (!f) return;
        populateDetail(f);
    }).catch(() => {
        const f = globalFindings.find(x => x.id == id);
        if (f) populateDetail(f);
    });
}

function populateDetail(f) {
    const bm = badgeMap[f.jenis_sampling] || { bg: '#f8fafc', color: '#64748b' };

    document.getElementById('det_id').innerText       = f.id;
    document.getElementById('det_jenis').innerText    = f.jenis_sampling;
    document.getElementById('det_kd_user').innerText  = f.kd_user || '—';
    document.getElementById('det_tgl_tx').innerText   = f.tgl_tx || '—';
    document.getElementById('det_no_arsip').innerText = f.no_arsip || '—';
    document.getElementById('det_nominal').innerText  = 'Rp ' + Number(f.jumlah_tx).toLocaleString('id-ID');
    document.getElementById('det_ket_tx').innerText   = f.ket_tx || '—';
    document.getElementById('det_alasan').innerText   = f.alasan_sampling || '—';
    document.getElementById('det_status_badge').innerText = f.status_review;
    document.getElementById('det_jenis_badge').innerHTML =
        `<span style="background:${bm.bg}; color:${bm.color}; padding:0.2rem 0.6rem; border-radius:20px; font-size:0.78rem; font-weight:600;">${f.jenis_sampling}</span>`;

    // Form RA
    document.getElementById('ra_finding_id').value        = f.id;
    document.getElementById('ra_hasil_uji').value         = f.hasil_uji || '';
    document.getElementById('ra_bukti_referensi').value   = f.bukti_referensi || '';
    document.getElementById('ra_skor_dampak').value       = f.skor_dampak || 1;
    document.getElementById('ra_skor_kemungkinan').value  = f.skor_kemungkinan || 1;
    document.getElementById('ra_tanggal_ditemukan').value = f.tanggal_ditemukan || '';
    document.getElementById('ra_simpulan_ra').value       = f.simpulan_ra || '';
    hitungRisk();

    // Lock jika Approved
    const isApproved = f.status_review === 'Approved';
    ['ra_hasil_uji','ra_bukti_referensi','ra_skor_dampak','ra_skor_kemungkinan','ra_tanggal_ditemukan','ra_simpulan_ra']
        .forEach(id => { document.getElementById(id).disabled = isApproved; });
    document.getElementById('ra_actions').style.display = isApproved ? 'none' : 'flex';
    document.getElementById('badge_ra_lock').innerText   = isApproved ? '🔒 Terkunci' : 'Mode Edit';

    // Form Admin
    const admId = document.getElementById('adm_finding_id');
    if (admId) {
        admId.value = f.id;
        document.getElementById('adm_status_review').value    = f.status_review || 'Belum Direview';
        document.getElementById('adm_catatan_reviewer').value = f.catatan_reviewer || '';
    }

    document.getElementById('dashboardView').style.display = 'none';
    document.getElementById('detailView').style.display    = 'block';
    window.scrollTo(0, 0);
}

function closeDetail() {
    document.getElementById('detailView').style.display    = 'none';
    document.getElementById('dashboardView').style.display = 'block';
    renderTable(globalFindings);
}

function hitungRisk() {
    const d = parseInt(document.getElementById('ra_skor_dampak').value) || 1;
    const k = parseInt(document.getElementById('ra_skor_kemungkinan').value) || 1;
    const s = d * k;
    let level = 'Low', bg = '#dcfce7', color = '#16a34a';
    if (s >= 20)      { level = 'Critical'; bg = '#ede9fe'; color = '#7c3aed'; }
    else if (s >= 12) { level = 'High';     bg = '#fef2f2'; color = '#dc2626'; }
    else if (s >= 6)  { level = 'Moderate'; bg = '#fffbeb'; color = '#ca8a04'; }

    document.getElementById('ra_risk_score').innerText = s;
    const badge = document.getElementById('ra_risk_badge');
    badge.innerText = level;
    badge.style.background = bg;
    badge.style.color = color;
}

function submitRa() {
    const id = document.getElementById('ra_finding_id').value;
    const payload = new URLSearchParams({
        _token:            csrfToken,
        _method:           'PATCH',
        hasil_uji:         document.getElementById('ra_hasil_uji').value,
        bukti_referensi:   document.getElementById('ra_bukti_referensi').value,
        skor_dampak:       document.getElementById('ra_skor_dampak').value,
        skor_kemungkinan:  document.getElementById('ra_skor_kemungkinan').value,
        tanggal_ditemukan: document.getElementById('ra_tanggal_ditemukan').value,
        simpulan_ra:       document.getElementById('ra_simpulan_ra').value,
    });

    axios.post(`${kkaUrl}/${id}`, payload, { headers: { 'X-CSRF-TOKEN': csrfToken } })
        .then(() => { closeDetail(); loadData(); })
        .catch(err => {
            const msg = err.response?.data?.message || 'Gagal menyimpan.';
            alert(msg);
        });
}

function submitAdm() {
    const id = document.getElementById('adm_finding_id').value;
    const payload = new URLSearchParams({
        _token:           csrfToken,
        _method:          'PATCH',
        status_review:    document.getElementById('adm_status_review').value,
        catatan_reviewer: document.getElementById('adm_catatan_reviewer').value,
    });

    axios.post(`${kkaUrl}/${id}/review`, payload, { headers: { 'X-CSRF-TOKEN': csrfToken } })
        .then(() => { closeDetail(); loadData(); })
        .catch(err => {
            const msg = err.response?.data?.message || 'Gagal menyimpan review.';
            alert(msg);
        });
}
</script>
@endpush

@endsection
