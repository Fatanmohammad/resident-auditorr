@extends('layouts.app')
@section('title', 'Register Harian (Low Risk)')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Register Harian (Low Risk)</h1>
        <p>Pemantauan harian transaksi kategori Low Risk dari 6 KKA Sampling Harian.</p>
    </div>
    <a href="{{ route('offsite.history.index') }}" class="btn btn-outline btn-sm">
        <i class="bi bi-clock-history me-1"></i> Riwayat Upload
    </a>
</div>

{{-- Alert Informasi Penyimpanan Sementara (Temporary Storage) --}}
<div class="alert alert-warning d-flex align-items-center mb-3 shadow-sm" role="alert" style="border-left: 4px solid #f59e0b; background-color: #fffbeb; color: #92400e; border-radius: 8px; padding: 0.9rem 1.25rem;">
    <i class="bi bi-clock-history me-3 fs-4 text-warning"></i>
    <div>
        <div style="font-weight: 600; margin-bottom: 2px;">Informasi Penyimpanan Sementara (Temporary Storage)</div>
        <div style="font-size: 0.86rem; line-height: 1.45;">
            Data Register Harian (Low Risk) disimpan pada penyimpanan sementara (Temporary) hingga pukul 23:59 dan akan terhapus otomatis secara permanen saat pergantian hari.
        </div>
    </div>
</div>

{{-- Filter Card --}}
<div class="card mb-3" style="box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border-color);">
    <div class="card-body" style="padding: 1rem 1.25rem;">
        <div style="display:flex; gap:0.75rem; align-items:flex-end; flex-wrap:wrap;">
            <div style="min-width: 180px;">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Unit / Cabang</label>
                <select id="filterUnit" class="form-select form-select-sm" style="font-size: 0.85rem;">
                    <option value="">-- Semua Unit --</option>
                    @if(isset($units))
                        @foreach($units as $u)
                            <option value="{{ $u->unit_code }}">{{ $u->unit_code }} - {{ $u->unit_name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div style="min-width: 180px;">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Sumber Sheet</label>
                <select id="filterSheet" class="form-select form-select-sm" style="font-size: 0.85rem;">
                    <option value="">-- Semua Sheet --</option>
                    <option value="DUMP_01_CBS">DUMP 01 - CBS / Teller & Kas</option>
                    <option value="DUMP_02_DPK">DUMP 02 - DPK</option>
                    <option value="DUMP_03_KREDIT">DUMP 03 - Kredit</option>
                    <option value="DUMP_04_BIAYA">DUMP 04 - Biaya & Beban</option>
                    <option value="DUMP_05_PENGADUAN">DUMP 05 - Pengaduan Nasabah</option>
                    <option value="DUMP_06_KARTU_ATM">DUMP 06 - Kartu ATM</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Pencarian (Rincian/Keterangan)</label>
                <input type="text" id="filterSearch" class="form-input form-input-sm" placeholder="Ketik kata kunci pencarian..." style="font-size: 0.85rem; padding: 0.45rem 0.75rem;">
            </div>
            <div>
                <button type="button" onclick="applyFilter()" class="btn btn-primary btn-sm" style="padding: 0.45rem 0.9rem;">
                    <i class="bi bi-filter"></i> Filter
                </button>
                <button type="button" onclick="resetFilter()" class="btn btn-outline btn-sm" style="padding: 0.45rem 0.9rem;">
                    Reset
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Tabel Data Register Harian --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title">
            <i class="bi bi-journal-text me-2 text-muted"></i>Data Register Harian Hari Ini ({{ \Carbon\Carbon::today()->translatedFormat('d F Y') }})
        </div>
        <span id="dataBadge" class="badge badge-info" style="font-weight: 500;">Memuat...</span>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center;">NO</th>
                    <th>TANGGAL DATA</th>
                    <th>KODE UNIT</th>
                    <th>SUMBER SHEET</th>
                    <th style="text-align:right;">NOMINAL</th>
                    <th style="text-align:center;">TINGKAT RISIKO</th>
                    <th>RINCIAN / INDIKATOR</th>
                </tr>
            </thead>
            <tbody id="registerTableBody">
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="bi bi-hourglass-split"></i>
                            <p>Memuat data register harian...</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div id="paginationArea" style="padding:0.8rem 1.25rem; display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); background:#f8fafc;">
        <div id="paginationInfo" style="font-size:0.82rem; color:var(--text-muted);">-</div>
        <div id="paginationButtons" style="display:flex; gap:0.25rem;"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    const baseUrl = '{{ url("/daily-register/register") }}';
    let currentPage = 1;

    document.addEventListener("DOMContentLoaded", function() {
        loadData(1);

        // Enter key di filter search
        document.getElementById('filterSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilter();
            }
        });
    });

    function applyFilter() {
        loadData(1);
    }

    function resetFilter() {
        document.getElementById('filterUnit').value = '';
        document.getElementById('filterSheet').value = '';
        document.getElementById('filterSearch').value = '';
        loadData(1);
    }

    function loadData(page = 1) {
        currentPage = page;
        const kodeUnit = document.getElementById('filterUnit').value;
        const sourceSheet = document.getElementById('filterSheet').value;
        const search = document.getElementById('filterSearch').value;

        const params = new URLSearchParams();
        params.append('page', page);
        if (kodeUnit) params.append('kode_unit', kodeUnit);
        if (sourceSheet) params.append('source_sheet', sourceSheet);
        if (search) params.append('search', search);

        document.getElementById('registerTableBody').innerHTML =
            '<tr><td colspan="7"><div class="empty-state"><i class="bi bi-hourglass-split"></i><p>Memuat data...</p></div></td></tr>';

        axios.get(`${baseUrl}/data?${params.toString()}`)
            .then(response => {
                const paginator = response.data.data;
                const arrayData = paginator.data;
                const total = paginator.total || 0;
                const from = paginator.from || 0;
                const to = paginator.to || 0;

                document.getElementById('dataBadge').innerText = `${total} Data Aktif`;
                renderTable(arrayData, from);
                renderPagination(paginator);
            })
            .catch(error => {
                console.error("Penyebab error:", error);
                document.getElementById('dataBadge').innerText = '0 Data';
                document.getElementById('registerTableBody').innerHTML =
                    '<tr><td colspan="7"><div class="empty-state"><i class="bi bi-exclamation-triangle"></i><p>Gagal memuat data register harian.</p></div></td></tr>';
                document.getElementById('paginationInfo').innerText = '-';
                document.getElementById('paginationButtons').innerHTML = '';
            });
    }

    function renderTable(data, startIndex = 1) {
        const tbody = document.getElementById('registerTableBody');
        
        if (!data || !Array.isArray(data) || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i><p>Tidak ada catatan register harian (Low Risk) untuk filter ini.</p></div></td></tr>';
            return;
        }
        
        tbody.innerHTML = data.map((item, index) => {
            const rowNumber = (startIndex || 1) + index;
            const nominal = item.nominal_terkait > 0
                ? 'Rp ' + new Intl.NumberFormat('id-ID').format(item.nominal_terkait)
                : '-';
            return `<tr>
                <td style="text-align:center; color:var(--text-muted); font-size:0.8rem;">${rowNumber}</td>
                <td style="font-size:0.82rem; white-space:nowrap;">${item.tanggal_data || '-'}</td>
                <td><span class="badge badge-gray" style="font-family:monospace;">${item.kode_unit || '-'}</span></td>
                <td><span class="badge badge-info">${item.source_sheet || '-'}</span></td>
                <td style="text-align:right; font-family:monospace; font-size:0.85rem; font-weight:600;">${nominal}</td>
                <td style="text-align:center;"><span class="badge badge-success">Low Risk</span></td>
                <td style="font-size:0.82rem; color:var(--text-muted);">${item.rincian || item.kategori || item.jenis_exception_awal || '-'}</td>
            </tr>`;
        }).join('');
    }

    function renderPagination(paginator) {
        const info = document.getElementById('paginationInfo');
        const buttons = document.getElementById('paginationButtons');

        if (!paginator || paginator.total === 0) {
            info.innerText = 'Menampilkan 0 data';
            buttons.innerHTML = '';
            return;
        }

        info.innerText = `Menampilkan ${paginator.from || 0} - ${paginator.to || 0} dari ${paginator.total} data`;

        let html = '';
        if (paginator.current_page > 1) {
            html += `<button type="button" class="btn btn-outline btn-sm" onclick="loadData(${paginator.current_page - 1})" style="padding:0.25rem 0.5rem; font-size:0.8rem;">&laquo; Prev</button>`;
        }

        const maxButtons = 5;
        let startPage = Math.max(1, paginator.current_page - 2);
        let endPage = Math.min(paginator.last_page, startPage + maxButtons - 1);
        if (endPage - startPage < maxButtons - 1) {
            startPage = Math.max(1, endPage - maxButtons + 1);
        }

        for (let p = startPage; p <= endPage; p++) {
            const activeClass = (p === paginator.current_page) ? 'btn-primary' : 'btn-outline';
            html += `<button type="button" class="btn ${activeClass} btn-sm" onclick="loadData(${p})" style="padding:0.25rem 0.6rem; font-size:0.8rem;">${p}</button>`;
        }

        if (paginator.current_page < paginator.last_page) {
            html += `<button type="button" class="btn btn-outline btn-sm" onclick="loadData(${paginator.current_page + 1})" style="padding:0.25rem 0.5rem; font-size:0.8rem;">Next &raquo;</button>`;
        }

        buttons.innerHTML = html;
    }
</script>
@endsection