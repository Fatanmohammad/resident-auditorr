@extends('layouts.app')
@section('title', 'Register Harian Offsite')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Register Offsite Harian</h1>
        <p>Pemantauan harian transitori — data ter-refresh otomatis setiap upload CSV baru.</p>
    </div>
    <a href="{{ route('offsite.history.index') }}" class="btn btn-outline btn-sm">
        <i class="bi bi-clock-history me-1"></i> Riwayat
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="bi bi-journal-text me-2 text-muted"></i>Data Register Harian</div>
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
    <div id="paginationArea" style="padding:0.8rem 1.25rem; display:flex; justify-content:flex-end; border-top:1px solid var(--border-color); background:#f8fafc;"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    const baseUrl = '{{ url("/offsite/register") }}';

    document.addEventListener("DOMContentLoaded", function() {
        loadData();
    });

    function loadData() {
        axios.get(`${baseUrl}/data`)
            .then(response => {
                // PERBAIKAN DI SINI: Harus pakai .data.data.data karena pakai paginate(15)
                const arrayData = response.data.data.data;
                renderTable(arrayData);
            })
            .catch(error => {
                console.error("Penyebab error:", error); // Supaya error-nya terlihat di inspect element
                document.getElementById('registerTableBody').innerHTML =
                    '<tr><td colspan="7"><div class="empty-state"><i class="bi bi-exclamation-triangle"></i><p>Gagal memuat data register harian.</p></div></td></tr>';
            });
    }

    function renderTable(data) {
        const tbody = document.getElementById('registerTableBody');
        
        // Pastikan variabel 'data' adalah array
        if (!data || !Array.isArray(data) || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i><p>Tidak ada catatan register harian untuk saat ini.</p></div></td></tr>';
            return;
        }
        
        tbody.innerHTML = data.map((item, index) => {
            const nominal = item.nominal_terkait > 0
                ? 'Rp ' + new Intl.NumberFormat('id-ID').format(item.nominal_terkait)
                : '-';
            return `<tr>
                <td style="text-align:center; color:var(--text-muted); font-size:0.8rem;">${index + 1}</td>
                <td style="font-size:0.82rem; white-space:nowrap;">${item.tanggal_data || '-'}</td>
                <td><span class="badge badge-gray" style="font-family:monospace;">${item.kode_unit || '-'}</span></td>
                <td><span class="badge badge-info">${item.source_sheet || '-'}</span></td>
                <td style="text-align:right; font-family:monospace; font-size:0.85rem; font-weight:600;">${nominal}</td>
                <td style="text-align:center;"><span class="badge badge-success">Low Risk</span></td>
                <td style="font-size:0.82rem; color:var(--text-muted);">${item.rincian || item.jenis_exception_awal || '-'}</td>
            </tr>`;
        }).join('');
    }
</script>
@endsection