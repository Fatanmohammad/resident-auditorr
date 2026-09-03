@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="m-0 font-weight-bold text-primary">Kertas Kerja Audit (KKA) - Offsite</h5>
                <small class="text-muted">Login sebagai: <strong class="badge bg-secondary">{{ strtoupper(auth()->user()->role) }}</strong></small>
            </div>
        </div>
        <div class="card-body">
            
            <!-- Tabel Utama KKA -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Data</th>
                            <th>Kode Unit</th>
                            <th>Sumber KKA</th>
                            <th>Nominal Terkait</th>
                            <th>Risk Awal</th>
                            <th>Exception Awal</th>
                            <th>Status Review Admin</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="kkaTableBody">
                        <tr><td colspan="9" class="text-center">Memuat data KKA...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Area Pagination -->
            <div id="paginationArea" class="mt-3 d-flex justify-content-end"></div>
        </div>
    </div>
</div>

<!-- ================================================================= -->
<!-- MODAL UTAMA KKA: KOLABORASI RA & ADMIN/PIMSIE                      -->
<!-- ================================================================= -->
<div class="modal fade" id="modalKkaDetail" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalKkaTitle">Detail Kertas Kerja Audit (KKA)</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body bg-light">
        
        <!-- SECTION 1: SYSTEM GENERATED DATA (KOLOM A - Y) -->
        <div class="card mb-3 border-secondary shadow-sm">
            <div class="card-header bg-secondary text-white font-weight-bold py-2">
                1. Informasi Temuan & Prosedur Uji (System Generated)
            </div>
            <div class="card-body bg-white">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label text-muted small mb-0">Offsite / Staging ID</label>
                        <p class="fw-bold mb-0" id="sys_offsite_id">-</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small mb-0">Tanggal Data</label>
                        <p class="fw-bold mb-0" id="sys_tanggal_data">-</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small mb-0">Unit Kerja (Cabang)</label>
                        <p class="fw-bold mb-0" id="sys_unit">-</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small mb-0">Nominal Terkait</label>
                        <p class="fw-bold text-danger mb-0" id="sys_nominal">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-0">Sumber Sheet (Modul KKA)</label>
                        <p class="mb-0"><span class="badge bg-info text-dark" id="sys_source_sheet">-</span></p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-0">Risk Awal Engine</label>
                        <p class="mb-0" id="sys_risk_awal">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-0">Jenis Exception Awal</label>
                        <p class="fw-bold mb-0" id="sys_exception_awal">-</p>
                    </div>
                    <div class="col-md-12"><hr class="my-1"></div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-0">Catatan Rule / Indikator Risiko</label>
                        <p class="mb-0 small text-dark" id="sys_catatan_rule">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-0">Prosedur Uji Audit</label>
                        <p class="mb-0 small text-dark" id="sys_prosedur_uji">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: INPUTAN RESIDENT AUDITOR / RA (SEL KUNING EXCEL) -->
        <div class="card mb-3 border-warning shadow-sm">
            <div class="card-header bg-warning text-dark font-weight-bold py-2 d-flex justify-content-between align-items-center">
                <span>2. Hasil Pengujian & Klarifikasi (Porsi Resident Auditor)</span>
                <span id="badge_ra_lock" class="badge bg-dark"></span>
            </div>
            <div class="card-body bg-white">
                <form id="formKkaRa">
                    <input type="hidden" id="ra_kka_id">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Bukti / Referensi Dokumen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control field-ra" id="bukti_referensi" placeholder="Misal: Surat No. 123/LHA/2026 atau No. Bilyet" @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Jenis Exception (Versi RA) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control field-ra" id="jenis_exception_ra" placeholder="Klasifikasi penyimpangan hasil pengujian" @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Hasil Pengujian Lapangan <span class="text-danger">*</span></label>
                            <textarea class="form-control field-ra" id="hasil_uji" rows="3" placeholder="Rincian fakta temuan hasil konfirmasi/uji ketaatan..." @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Klarifikasi Unit Kerja / Cabang</label>
                            <textarea class="form-control field-ra" id="klarifikasi_unit" rows="3" placeholder="Tanggapan / jawaban dari pihak cabang terkait temuan..." @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif></textarea>
                        </div>
                    </div>

                    <!-- MATRIX SKOR RISIKO FINAL -->
                    <div class="row g-3 mb-3 p-2 bg-light rounded border">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Skor Dampak (1–5)</label>
                            <select class="form-select field-ra" id="skor_dampak" onchange="hitungSkorRisiko()" @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif>
                                <option value="1">1 - Sangat Rendah</option>
                                <option value="2">2 - Rendah</option>
                                <option value="3">3 - Sedang</option>
                                <option value="4">4 - Tinggi</option>
                                <option value="5">5 - Sangat Tinggi</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Skor Kemungkinan (1–5)</label>
                            <select class="form-select field-ra" id="skor_kemungkinan" onchange="hitungSkorRisiko()" @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif>
                                <option value="1">1 - Sangat Jarang</option>
                                <option value="2">2 - Jarang</option>
                                <option value="3">3 - Kadang-Kadang</option>
                                <option value="4">4 - Sering</option>
                                <option value="5">5 - Sangat Sering</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Critical Trigger</label>
                            <select class="form-select field-ra" id="critical_trigger" onchange="hitungSkorRisiko()" @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif>
                                <option value="Tidak">Tidak</option>
                                <option value="Ya">Ya (Otomatis High)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Skor & Risk Final</label>
                            <div class="p-2 border rounded text-center bg-white" id="box_skor_final">
                                <span class="fw-bold" id="text_skor_final">0</span> - <span class="badge bg-secondary" id="badge_risk_final">Moderate</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Perlu Onsite Inspection?</label>
                            <select class="form-select field-ra" id="perlu_onsite" @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif>
                                <option value="0">Tidak Perlu</option>
                                <option value="1">Perlu Pemeriksaan Onsite</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tanggal Ditemukan / Diuji</label>
                            <input type="date" class="form-control field-ra" id="tanggal_ditemukan" @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Simpulan RA</label>
                            <textarea class="form-control field-ra" id="simpulan_ra" rows="2" placeholder="Simpulan akhir pemeriksaan offsite oleh RA..." @if(strtolower(auth()->user()->role) !== 'ra') disabled @endif></textarea>
                        </div>
                    </div>

                    @if(strtolower(auth()->user()->role) === 'ra')
                    <div class="text-end">
                        <button type="button" class="btn btn-primary" id="btnSaveRa" onclick="submitRaForm()">
                            Simpan Tindak Lanjut (RA)
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- SECTION 3: INPUTAN ADMIN / PIMSIE (REVIEWER) -->
        <div class="card mb-3 border-success shadow-sm">
            <div class="card-header bg-success text-white font-weight-bold py-2 d-flex justify-content-between align-items-center">
                <span>3. Review & Keputusan Pengawasan (Porsi Admin / Pimsie)</span>
                <span id="badge_admin_lock" class="badge bg-dark"></span>
            </div>
            <div class="card-body bg-white">
                <form id="formKkaAdmin">
                    <input type="hidden" id="admin_kka_id">

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Status Klarifikasi Unit</label>
                            <select class="form-select field-admin" id="status_klarifikasi" @if(strtolower(auth()->user()->role) === 'ra') disabled @endif>
                                <option value="">-- Pilih --</option>
                                <option value="Sesuai / Selesai">Sesuai / Selesai</option>
                                <option value="Belum Sesuai">Belum Sesuai</option>
                                <option value="Tidak Ada Klarifikasi">Tidak Ada Klarifikasi</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Perluasan Sampel?</label>
                            <select class="form-select field-admin" id="perluasan_sampel" @if(strtolower(auth()->user()->role) === 'ra') disabled @endif>
                                <option value="0">Tidak Perlu</option>
                                <option value="1">Ya, Perluasan Sampel</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Keputusan Onsite Inspection</label>
                            <select class="form-select field-admin" id="keputusan_onsite" @if(strtolower(auth()->user()->role) === 'ra') disabled @endif>
                                <option value="">-- Pilih Keputusan --</option>
                                <option value="Disetujui Onsite">Disetujui Onsite</option>
                                <option value="Cukup Offsite">Cukup Offsite</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status Review KKA <span class="text-danger">*</span></label>
                            <select class="form-select field-admin" id="status_review" @if(strtolower(auth()->user()->role) === 'ra') disabled @endif>
                                <option value="">-- Pilih Status Review --</option>
                                <option value="Approved">Approved (Selesai)</option>
                                <option value="Need Revision">Need Revision (Minta RA Perbaiki)</option>
                                <option value="Rejected">Rejected (Ditolak)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Keputusan Eskalasi</label>
                            <select class="form-select field-admin" id="keputusan_eskalasi" @if(strtolower(auth()->user()->role) === 'ra') disabled @endif>
                                <option value="Tidak">Tidak Dieskalasi</option>
                                <option value="Eskalasi Kadiv">Eskalasi ke Kadiv SKAI</option>
                                <option value="Eskalasi Direksi">Eskalasi ke Direksi</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Catatan Reviewer / Pimsie <span class="text-danger">*</span></label>
                        <textarea class="form-control field-admin" id="catatan_reviewer" rows="3" placeholder="Instruksi revisi, arahan tambahan, atau catatan persetujuan KKA..." @if(strtolower(auth()->user()->role) === 'ra') disabled @endif></textarea>
                    </div>

                    @if(in_array(strtolower(auth()->user()->role), ['admin', 'korwas', 'pimsie']))
                    <div class="text-end">
                        <button type="button" class="btn btn-success" id="btnSaveAdmin" onclick="submitAdminForm()">
                            Simpan Review (Admin/Pimsie)
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>

      </div>

      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>

    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    const baseUrl = '{{ url("/offsite/kka") }}'; 
    const userRole = '{{ strtolower(trim(auth()->user()->role)) }}'; // Dinormalisasi ke lowercase & tanpa spasi
    const csrfToken = '{{ csrf_token() }}';
    
    let globalFindings = [];

    document.addEventListener("DOMContentLoaded", function() {
        loadData();
    });

    // 1. CARI & TAMPILKAN DATA TABEL
    function loadData(page = 1) {
        axios.get(`${baseUrl}/data?page=${page}`)
            .then(response => {
                globalFindings = response.data.data.data;
                renderTable(globalFindings);
            })
            .catch(error => {
                console.error("Gagal load data KKA:", error);
                document.getElementById('kkaTableBody').innerHTML = '<tr><td colspan="9" class="text-center text-danger">Gagal memuat data KKA</td></tr>';
            });
    }

    function renderTable(data) {
        const tbody = document.getElementById('kkaTableBody');
        tbody.innerHTML = '';

        if(!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center">Tidak ada temuan KKA aktif.</td></tr>';
            return;
        }

        data.forEach((item, index) => {
            let riskBadge = (item.risk_awal || '').toLowerCase() === 'high' 
                ? `<span class="badge bg-danger">High</span>` 
                : `<span class="badge bg-warning text-dark">Moderate</span>`;

            let reviewStatusBadge = item.status_review === 'Approved'
                ? `<span class="badge bg-success">Approved</span>`
                : (item.status_review === 'Need Revision' 
                    ? `<span class="badge bg-warning text-dark">Need Revision</span>` 
                    : `<span class="badge bg-secondary">${item.status_review || 'Belum Di-review'}</span>`);

            let nominalStr = item.nominal_terkait > 0 
                ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(item.nominal_terkait)
                : '-';

            const tr = `<tr>
                <td>${index + 1}</td>
                <td>${item.tanggal_data}</td>
                <td><strong>${item.kode_unit}</strong></td>
                <td><span class="badge bg-info text-dark">${item.source_sheet}</span></td>
                <td>${nominalStr}</td>
                <td>${riskBadge}</td>
                <td class="small">${item.jenis_exception_awal || '-'}</td>
                <td>${reviewStatusBadge}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="openKkaDetailModal(${item.id})">
                        <i class="bi bi-eye"></i> Buka KKA
                    </button>
                </td>
            </tr>`;
            
            tbody.insertAdjacentHTML('beforeend', tr);
        });
    }

    // 2. LOGIKA BUKA MODAL & PERMAINAN READ-ONLY SILANG
    function openKkaDetailModal(id) {
        const item = globalFindings.find(x => x.id === id);
        if(!item) return;

        // Populate SECTION 1: System Generated
        document.getElementById('sys_offsite_id').innerText = item.offsite_id || item.staging_id || `KKA-${item.id}`;
        document.getElementById('sys_tanggal_data').innerText = item.tanggal_data || '-';
        document.getElementById('sys_unit').innerText = `${item.kode_unit} - ${item.nama_unit || ''}`;
        document.getElementById('sys_nominal').innerText = item.nominal_terkait > 0 
            ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(item.nominal_terkait) 
            : 'Rp 0';
        document.getElementById('sys_source_sheet').innerText = item.source_sheet || '-';
        document.getElementById('sys_risk_awal').innerHTML = (item.risk_awal || '').toLowerCase() === 'high' 
            ? `<span class="badge bg-danger">High Risk</span>` 
            : `<span class="badge bg-warning text-dark">Moderate Risk</span>`;
        document.getElementById('sys_exception_awal').innerText = item.jenis_exception_awal || '-';
        document.getElementById('sys_catatan_rule').innerText = item.catatan_rule || item.rincian || '-';
        document.getElementById('sys_prosedur_uji').innerText = item.prosedur_uji || 'Uji Ketaatan & Keabsahan Dokumen Transaksi Offsite';

        // Populate SECTION 2: Input RA
        document.getElementById('ra_kka_id').value = item.id;
        document.getElementById('bukti_referensi').value = item.bukti_referensi || '';
        document.getElementById('jenis_exception_ra').value = item.jenis_exception_ra || '';
        document.getElementById('hasil_uji').value = item.hasil_uji || '';
        document.getElementById('klarifikasi_unit').value = item.klarifikasi_unit || '';
        document.getElementById('skor_dampak').value = item.skor_dampak || 1;
        document.getElementById('skor_kemungkinan').value = item.skor_kemungkinan || 1;
        document.getElementById('critical_trigger').value = item.critical_trigger || 'Tidak';
        document.getElementById('perlu_onsite').value = item.perlu_onsite ? 1 : 0;
        document.getElementById('tanggal_ditemukan').value = item.tanggal_ditemukan || '';
        document.getElementById('simpulan_ra').value = item.simpulan_ra || '';

        // Populate SECTION 3: Input Admin
        document.getElementById('admin_kka_id').value = item.id;
        document.getElementById('status_klarifikasi').value = item.status_klarifikasi || '';
        document.getElementById('perluasan_sampel').value = item.perluasan_sampel ? 1 : 0;
        document.getElementById('keputusan_onsite').value = item.keputusan_onsite || '';
        document.getElementById('status_review').value = item.status_review || '';
        document.getElementById('keputusan_eskalasi').value = item.keputusan_eskalasi || 'Tidak';
        document.getElementById('catatan_reviewer').value = item.catatan_reviewer || '';

        hitungSkorRisiko();

        // LOGIKA KUNCI READ-ONLY SILANG VIA DOM JAVASCRIPT
        const raFields = document.querySelectorAll('.field-ra');
        const adminFields = document.querySelectorAll('.field-admin');
        const isRa = userRole === 'ra';

        if (isRa) {
            // RA BISA EDIT ISI RA, TAPI ADMIN LOCKED (READONLY)
            raFields.forEach(el => el.disabled = false);
            adminFields.forEach(el => el.disabled = true);
            
            document.getElementById('badge_ra_lock').innerText = "Mode Edit (RA)";
            document.getElementById('badge_admin_lock').innerText = "Read Only (Bukan Porsi RA)";
        } else {
            // ADMIN BISA EDIT ISI ADMIN, TAPI RA LOCKED (READONLY)
            raFields.forEach(el => el.disabled = true);
            adminFields.forEach(el => el.disabled = false);

            document.getElementById('badge_ra_lock').innerText = "Read Only (Bukan Porsi Admin)";
            document.getElementById('badge_admin_lock').innerText = "Mode Edit (Admin/Pimsie)";
        }

        new bootstrap.Modal(document.getElementById('modalKkaDetail')).show();
    }

    // 3. KALKULASI SKOR RISIKO FINAL
    function hitungSkorRisiko() {
        const dampak = parseInt(document.getElementById('skor_dampak').value) || 1;
        const kemungkinan = parseInt(document.getElementById('skor_kemungkinan').value) || 1;
        const critical = document.getElementById('critical_trigger').value;

        const skor = dampak * kemungkinan;
        let riskCategory = 'Low';
        let badgeClass = 'bg-success';

        if (critical === 'Ya' || skor >= 15) {
            riskCategory = 'High';
            badgeClass = 'bg-danger';
        } else if (skor >= 6) {
            riskCategory = 'Moderate';
            badgeClass = 'bg-warning text-dark';
        }

        document.getElementById('text_skor_final').innerText = skor;
        const badgeEl = document.getElementById('badge_risk_final');
        badgeEl.innerText = riskCategory;
        badgeEl.className = `badge ${badgeClass}`;
    }

    // 4. SUBMIT FORM RA
    function submitRaForm() {
        const id = document.getElementById('ra_kka_id').value;
        const payload = {
            bukti_referensi: document.getElementById('bukti_referensi').value,
            jenis_exception_ra: document.getElementById('jenis_exception_ra').value,
            hasil_uji: document.getElementById('hasil_uji').value,
            klarifikasi_unit: document.getElementById('klarifikasi_unit').value,
            skor_dampak: document.getElementById('skor_dampak').value,
            skor_kemungkinan: document.getElementById('skor_kemungkinan').value,
            critical_trigger: document.getElementById('critical_trigger').value,
            perlu_onsite: document.getElementById('perlu_onsite').value,
            tanggal_ditemukan: document.getElementById('tanggal_ditemukan').value,
            simpulan_ra: document.getElementById('simpulan_ra').value,
            _method: 'PUT'
        };

        axios.post(`${baseUrl}/${id}/ra`, payload, { headers: { 'X-CSRF-TOKEN': csrfToken } })
            .then(res => {
                alert(res.data.message || 'Tindak lanjut RA berhasil disimpan!');
                bootstrap.Modal.getInstance(document.getElementById('modalKkaDetail')).hide();
                loadData();
            })
            .catch(err => alert('Gagal menyimpan tindak lanjut RA!'));
    }

    // 5. SUBMIT FORM ADMIN
    function submitAdminForm() {
        const id = document.getElementById('admin_kka_id').value;
        const payload = {
            status_klarifikasi: document.getElementById('status_klarifikasi').value,
            perluasan_sampel: document.getElementById('perluasan_sampel').value,
            keputusan_onsite: document.getElementById('keputusan_onsite').value,
            status_review: document.getElementById('status_review').value,
            keputusan_eskalasi: document.getElementById('keputusan_eskalasi').value,
            catatan_reviewer: document.getElementById('catatan_reviewer').value,
            _method: 'PUT'
        };

        axios.post(`${baseUrl}/${id}/admin`, payload, { headers: { 'X-CSRF-TOKEN': csrfToken } })
            .then(res => {
                alert(res.data.message || 'Review Admin berhasil disimpan!');
                bootstrap.Modal.getInstance(document.getElementById('modalKkaDetail')).hide();
                loadData();
            })
            .catch(err => alert('Gagal menyimpan review Admin!'));
    }
</script>
@endsection