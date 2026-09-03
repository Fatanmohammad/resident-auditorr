@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="m-0 font-weight-bold text-primary">Register Offsite Harian (Low Risk)</h5>
                <small class="text-muted">Pemantauan Harian Transitori — Data ter-refresh otomatis setiap upload CSV baru</small>
            </div>
            <span class="badge bg-info text-dark">Role: {{ strtoupper(auth()->user()->role) }}</span>
        </div>
        <div class="card-body">
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Data</th>
                            <th>Kode Unit</th>
                            <th>Sumber Sheet</th>
                            <th>Nominal Terkait</th>
                            <th>Tingkat Risiko</th>
                            <th>Rincian / Indikator Exception</th>
                        </tr>
                    </thead>
                    <tbody id="registerTableBody">
                        <tr><td colspan="7" class="text-center">Memuat data register harian...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Area Pagination -->
            <div id="paginationArea" class="mt-3 d-flex justify-content-end"></div>
        </div>
    </div>
</div>

<!-- ======================= SCRIPT JS ======================= -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    const baseUrl = '{{ url("/offsite/register") }}';

    document.addEventListener("DOMContentLoaded", function() {
        loadData();
    });

    function loadData(page = 1) {
        axios.get(`${baseUrl}/data?page=${page}`)
            .then(response => {
                renderTable(response.data.data.data);
            })
            .catch(error => {
                console.error("Gagal load data register:", error);
                document.getElementById('registerTableBody').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Gagal memuat data register harian</td></tr>';
            });
    }

    function renderTable(data) {
        const tbody = document.getElementById('registerTableBody');
        tbody.innerHTML = '';

        if(!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">Tidak ada catatan register harian (Low Risk) untuk hari ini.</td></tr>';
            return;
        }

        data.forEach((item, index) => {
            let nominalStr = item.nominal > 0 
                ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(item.nominal)
                : '-';

            const tr = `<tr>
                <td>${index + 1}</td>
                <td>${item.tanggal_data || '-'}</td>
                <td><strong>${item.kode_unit || '-'}</strong></td>
                <td><span class="badge bg-secondary">${item.source_sheet || '-'}</span></td>
                <td>${nominalStr}</td>
                <td><span class="badge bg-success">Low Risk</span></td>
                <td class="small">${item.rincian || item.jenis_exception_awal || '-'}</td>
            </tr>`;
            
            tbody.insertAdjacentHTML('beforeend', tr);
        });
    }
</script>
@endsection