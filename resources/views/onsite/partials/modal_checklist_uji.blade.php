{{-- 
    MODAL PENGUJIAN CHECKLIST KKA STANDAR (SOP 04.2 & DAILY REGISTER SPEC)
    Dipakai seragam di seluruh 6 Area KKA Onsite (Teller, DPK, Kredit, Biaya, ATM, Pengaduan)
--}}

@php
    $userRole = strtolower(auth()->user()->role ?? '');
    $isReviewer = in_array($userRole, ['admin', 'kabag_ra', 'kadiv_skai']);
@endphp

<div class="modal fade" id="modalUjiAtribut" tabindex="-1" aria-labelledby="modalUjiLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            
            {{-- Modal Header --}}
            <div class="modal-header text-white" style="background: var(--primary, #1e3a8a);">
                <div>
                    <h5 class="modal-title fw-bold" id="modalUjiLabel">
                        <i class="bi bi-clipboard2-check me-2"></i>Lembar Pengujian Checklist KKA — {{ $sheetTitle ?? 'Onsite' }}
                    </h5>
                    <div class="small opacity-75 mt-0.5" id="modalSampleSubtitle">
                        Sample ID: <span id="lblSampleId" class="font-monospace fw-bold"></span> &bull; 
                        User/Objek: <span id="lblUser"></span> &bull; 
                        Nominal: <span id="lblNominal" class="fw-bold"></span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-4 bg-light">
                <form id="formUjiAtribut" onsubmit="simpanPengujian(event)">
                    <input type="hidden" id="f_item_id" name="item_id">

                    {{-- Transaction Detail Card --}}
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                        <div class="card-body p-3">
                            <div class="row g-2 text-sm" style="font-size:0.83rem;">
                                <div class="col-md-3">
                                    <span class="text-muted d-block small">Tanggal:</span>
                                    <strong id="detTglTx" class="text-dark">-</strong>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted d-block small">No. Referensi / Arsip:</span>
                                    <code id="detNoArsip" class="text-primary">-</code>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted d-block small">Jenis Sampling:</span>
                                    <span id="detJenisSampling" class="badge bg-secondary">-</span>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted d-block small">Alasan Sampling:</span>
                                    <span id="detAlasan" class="text-dark fw-medium">-</span>
                                </div>
                                <div class="col-12 mt-2 pt-2 border-top">
                                    <span class="text-muted small">Keterangan / Deskripsi Transaksi: </span>
                                    <span id="detKetTx" class="fw-bold text-dark">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bagian A: Accordion Checklist Pengujian Audit (SOP 04.2) --}}
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                        <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="fw-bold text-primary" style="font-size:0.88rem;">
                                    <i class="bi bi-ui-checks me-1"></i> A. Checklist Pengujian Audit (<span id="checklistTotalItemsCount">0</span> Item)
                                </div>
                                <span class="badge bg-success" id="badgeTotalYa" style="font-size:0.75rem;">0 Ya</span>
                                <span class="badge bg-danger" id="badgeTotalTidak" style="font-size:0.75rem;">0 Tidak</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-success fw-bold py-1 px-2.5" onclick="setSemuaYa()" style="font-size:0.75rem;">
                                    <i class="bi bi-check-all me-1"></i> Set Semua [✓] Ya
                                </button>
                                <span class="badge bg-light text-muted border small">SOP 04.2</span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div id="checklistAccordionContainer">
                                <div class="text-center py-3 text-muted" style="font-size:0.85rem;">
                                    <i class="bi bi-hourglass-split me-1"></i> Memuat daftar checklist pengujian...
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bagian B: Kemunculan Kondisional (Hanya Muncul Jika Minimal 1 Checklist "Tidak") --}}
                    <div id="containerExceptionFields" class="card border-0 shadow-sm mb-4" style="display:none; border-radius: 10px; background:#fff1f2; border:1px solid #fecdd3 !important;">
                        <div class="card-header py-2.5 px-3 border-bottom d-flex justify-content-between align-items-center" style="background:#ffe4e6; border-color:#fecdd3 !important;">
                            <div class="fw-bold text-danger" style="font-size:0.88rem;">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> B. Temuan Pengujian Lapangan Terdeteksi (Checklist Bernilai "Tidak")
                            </div>
                            <span class="badge bg-danger text-white small">Wajib Diisi Lengkap</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-danger">Jenis Exception (Versi RA) <span class="text-danger">*</span></label>
                                    <input type="text" id="f_jenis_exception" name="jenis_exception" class="form-control form-control-sm border-danger" placeholder="Misal: Dokumen Tidak Lengkap / Pelampauan Limit">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-danger">Klarifikasi Unit Kerja / Cabang <span class="text-danger">*</span></label>
                                    <textarea id="f_klarifikasi_unit" name="klarifikasi_unit" rows="2" class="form-control form-control-sm border-danger" placeholder="Tanggapan / penjelasan dari pihak cabang terkait exception..."></textarea>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Hasil Pengujian Lapangan <span class="text-danger">*</span></label>
                                <textarea id="f_hasil_uji" name="hasil_uji" rows="3" class="form-control form-control-sm" placeholder="Uraikan fakta temuan hasil konfirmasi & pengujian dokumen/fisik..."></textarea>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Kondisi (Fakta Lapangan)</label>
                                    <textarea id="f_kondisi" name="kondisi" rows="2" class="form-control form-control-sm" placeholder="Fakta kondisi di unit kerja..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Kriteria (SOP / Ketentuan)</label>
                                    <textarea id="f_kriteria" name="kriteria" rows="2" class="form-control form-control-sm" placeholder="Ketentuan / SOP yang dilanggar..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Sebab (Akar Masalah)</label>
                                    <textarea id="f_sebab" name="sebab" rows="2" class="form-control form-control-sm" placeholder="Penyebab terjadinya temuan..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Dampak (Potensi Kerugian / Risiko)</label>
                                    <textarea id="f_dampak" name="dampak" rows="2" class="form-control form-control-sm" placeholder="Potensi kerugian operasional / reputasi / finansial..."></textarea>
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Upload Bukti PDF (Opsional)</label>
                                    <input type="file" id="f_file_bukti" name="file_bukti" class="form-control form-control-sm" accept=".pdf">
                                    <div id="fileBuktiLink" class="small mt-1 text-muted"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Status Klarifikasi</label>
                                    <select id="f_status_klarifikasi" name="status_klarifikasi" class="form-select form-select-sm">
                                        <option value="Draft">Draft</option>
                                        <option value="Menunggu Tanggapan">Menunggu Tanggapan Cabang</option>
                                        <option value="Sudah Ditanggapi">Sudah Ditanggapi</option>
                                        <option value="Selesai">Selesai</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bagian C: Status Uji & Penilaian Matriks Risiko 5x5 --}}
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                        <div class="card-header bg-white py-2.5 px-3 border-bottom">
                            <div class="fw-bold text-dark" style="font-size:0.88rem;">
                                <i class="bi bi-shield-shaded me-1 text-warning"></i> C. Penilaian Risiko &amp; Status Uji (Matriks 5x5)
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Status Uji</label>
                                    <select id="f_status_uji" name="status_uji" class="form-select form-select-sm" readonly style="background:#f8fafc; pointer-events:none;">
                                        <option value="Sesuai">Sesuai (Tanpa Exception)</option>
                                        <option value="Ada Exception">Ada Exception (Ada Temuan)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Skor Dampak (1 - 5)</label>
                                    <select id="f_skor_dampak" name="skor_dampak" class="form-select form-select-sm" onchange="hitungRiskLevel()">
                                        <option value="1">1 — Sangat Rendah</option>
                                        <option value="2">2 — Rendah</option>
                                        <option value="3">3 — Sedang</option>
                                        <option value="4">4 — Tinggi</option>
                                        <option value="5">5 — Sangat Tinggi</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Skor Kemungkinan (1 - 5)</label>
                                    <select id="f_skor_kemungkinan" name="skor_kemungkinan" class="form-select form-select-sm" onchange="hitungRiskLevel()">
                                        <option value="1">1 — Sangat Jarang</option>
                                        <option value="2">2 — Jarang</option>
                                        <option value="3">3 — Kadang-kadang</option>
                                        <option value="4">4 — Sering</option>
                                        <option value="5">5 — Sangat Sering</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark">Critical Trigger</label>
                                    <select id="f_critical_trigger" name="critical_trigger" class="form-select form-select-sm" onchange="hitungRiskLevel()">
                                        <option value="Tidak">Tidak</option>
                                        <option value="Ya">Ya (Otomatis Kritis)</option>
                                    </select>
                                </div>
                                
                                <div class="col-12 mt-2">
                                    <div class="p-2.5 rounded d-flex justify-content-between align-items-center" style="background:#f8fafc; border:1px solid #e2e8f0;">
                                        <div>
                                            <span class="small text-muted me-2">Skor Risiko (D x K):</span>
                                            <strong id="displaySkorRisiko" class="fs-6 text-dark">1</strong>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="small text-muted">Tingkat Risiko:</span>
                                            <span id="badgeRiskLevel" class="badge" style="background:#dcfce7; color:#16a34a; border:1px solid #bbf7d0; font-size:0.82rem;">Low</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Flagging Indikasi Fraud (SOP 09) --}}
                                <div class="col-12" id="boxFraudFlagging" style="display:none;">
                                    <div class="alert alert-danger d-flex justify-content-between align-items-center p-2.5 mb-0" style="font-size:0.83rem;">
                                        <div>
                                            <i class="bi bi-flag-fill me-1"></i>
                                            <strong>Flagging Indikasi Fraud Terdeteksi (Tingkat Kritis):</strong>
                                            Temuan ini berisiko tinggi dan memenuhi kriteria eskalasi ke SOP 09.
                                        </div>
                                        <a href="{{ route('pemeriksaan_awal.index') }}" target="_blank" class="btn btn-sm btn-danger fw-bold py-1 px-2.5" style="white-space:nowrap; font-size:0.75rem;">
                                            <i class="bi bi-shield-exclamation me-1"></i> Buka SOP 09 &rarr;
                                        </a>
                                    </div>
                                </div>

                                <div class="col-12 mt-2">
                                    <label class="form-label small fw-bold text-dark">Simpulan Hasil Pengujian RA</label>
                                    <textarea id="f_simpulan_ra" name="simpulan_ra" rows="2" class="form-control form-control-sm" placeholder="Ringkasan akhir hasil audit terhadap transaksi ini..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bagian D: Review Atasan (Admin / Reviewer) --}}
                    @if($isReviewer)
                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px; border-left: 4px solid #10b981 !important;">
                        <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex justify-content-between align-items-center">
                            <div class="fw-bold text-success" style="font-size:0.88rem;">
                                <i class="bi bi-person-check me-1"></i> D. Porsi Review Atasan (Admin / Reviewer)
                            </div>
                            <span class="badge bg-success text-white small">Mode Review</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-dark">Status Review <span class="text-danger">*</span></label>
                                    <select id="f_status_review" name="status_review" class="form-select form-select-sm">
                                        <option value="Belum Direview">Belum Direview</option>
                                        <option value="Revisi">Revisi (Kembalikan ke RA)</option>
                                        <option value="Approved">Approved (Selesai)</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold text-dark">Catatan / Arahan Reviewer</label>
                                    <input type="text" id="f_catatan_reviewer" name="catatan_reviewer" class="form-control form-control-sm" placeholder="Catatan perbaikan atau arahan untuk RA...">
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <input type="hidden" id="f_status_review" name="status_review" value="Belum Direview">
                    <input type="hidden" id="f_catatan_reviewer" name="catatan_reviewer" value="">
                    @endif

                    <div class="modal-footer px-0 pb-0 pt-3 border-top mt-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold" id="btnSimpanUji">
                            <i class="bi bi-save me-1"></i> Simpan Hasil Pengujian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Master data checklists
    window.masterChecklists = @json($checklists ?? []);
    window.currentModalItem = null;
    let modalInstance = null;

    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('modalUjiAtribut');
        if (modalEl) {
            modalInstance = new bootstrap.Modal(modalEl);
        }
    });

    /**
     * Membuka modal pengujian dan render checklist
     */
    function openModalUji(item) {
        if (!item) return;
        window.currentModalItem = item;

        // Set ID
        document.getElementById('f_item_id').value = item.id;

        // Header info
        document.getElementById('lblSampleId').innerText = item.sample_id || '-';
        document.getElementById('lblUser').innerText = item.kd_user || item.nama_nasabah || item.nama_debitur || item.no_rekening || '-';
        
        let nominalVal = item.jumlah_tx || item.nominal || item.plafon || item.nominal_sengketa || 0;
        document.getElementById('lblNominal').innerText = 'Rp ' + Number(nominalVal || 0).toLocaleString('id-ID');

        // Detail box
        document.getElementById('detTglTx').innerText = item.tgl_tx || item.tgl_akad || item.tgl_transaksi || item.tgl_pengaduan || '-';
        document.getElementById('detNoArsip').innerText = item.no_arsip || item.no_rekening || item.no_voucher || item.no_tiket || item.nomor_kartu || '-';
        document.getElementById('detKetTx').innerText = item.ket_tx || item.keterangan_biaya || item.kategori_pengaduan || item.alasan_sampling || '-';
        document.getElementById('detAlasan').innerText = item.alasan_sampling || 'Sampling SOP 02';

        const jenisBadge = document.getElementById('detJenisSampling');
        jenisBadge.innerText = item.jenis_sampling || 'Targeted';
        jenisBadge.className = 'badge bg-' + ((item.jenis_sampling === 'Mandatory') ? 'danger' : (item.jenis_sampling === 'Certainty' ? 'warning text-dark' : 'primary'));

        // Render Accordion Checklist
        let existingAnswers = item.checklist_answers || [];
        if (typeof existingAnswers === 'string') {
            try { existingAnswers = JSON.parse(existingAnswers); } catch(e) { existingAnswers = []; }
        }
        renderChecklistAccordion(window.masterChecklists, existingAnswers, item);

        // Fields Temuan
        document.getElementById('f_jenis_exception').value = item.jenis_exception || '';
        document.getElementById('f_klarifikasi_unit').value = item.klarifikasi_unit || '';
        document.getElementById('f_hasil_uji').value = item.hasil_uji || item.simpulan_ra || '';
        document.getElementById('f_kondisi').value = item.kondisi || '';
        document.getElementById('f_kriteria').value = item.kriteria || '';
        document.getElementById('f_sebab').value = item.sebab || '';
        document.getElementById('f_dampak').value = item.dampak || '';
        document.getElementById('f_status_klarifikasi').value = item.status_klarifikasi || 'Draft';
        document.getElementById('f_simpulan_ra').value = item.simpulan_ra || '';

        // File bukti info
        const linkBukti = document.getElementById('fileBuktiLink');
        if (linkBukti) {
            if (item.file_bukti) {
                linkBukti.innerHTML = `<a href="/storage/${item.file_bukti}" target="_blank" class="text-primary text-decoration-none"><i class="bi bi-file-earmark-pdf me-1"></i>Lihat File Bukti PDF Terunggah</a>`;
            } else {
                linkBukti.innerHTML = `Belum ada file bukti PDF diunggah.`;
            }
        }

        // Skor Dampak & Kemungkinan
        document.getElementById('f_skor_dampak').value = item.skor_dampak || 1;
        document.getElementById('f_skor_kemungkinan').value = item.skor_kemungkinan || 1;
        document.getElementById('f_critical_trigger').value = item.critical_trigger || 'Tidak';

        // Supervisor review
        const revEl = document.getElementById('f_status_review');
        if (revEl) {
            revEl.value = item.status_review || 'Belum Direview';
            const catEl = document.getElementById('f_catatan_reviewer');
            if (catEl) catEl.value = item.catatan_reviewer || '';
        }

        // Jalankan kalkulasi & evaluasi reaktif
        onChecklistChange();

        if (modalInstance) {
            modalInstance.show();
        }
    }

    // Aliases for backwards compatibility across different KKA views
    window.openModalDpk = openModalUji;
    window.openModalKredit = openModalUji;
    window.openModalBiaya = openModalUji;
    window.openModalAtm = openModalUji;
    window.openModalPengaduan = openModalUji;

    /**
     * Render Accordion Checklist persis seperti di Daily Register
     */
    function renderChecklistAccordion(checklists, existingAnswers, item) {
        const container = document.getElementById('checklistAccordionContainer');
        if (!container) return;

        if (!checklists || checklists.length === 0) {
            container.innerHTML = `<div class="p-3 text-muted text-center" style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px;">Tidak ada master checklist untuk kategori ini.</div>`;
            return;
        }

        const answerMap = {};
        if (Array.isArray(existingAnswers)) {
            existingAnswers.forEach(ans => {
                answerMap[ans.checklist_id] = {
                    jawaban: ans.jawaban,
                    keterangan: ans.keterangan || ''
                };
            });
        }

        // Kelompokkan checklist berdasarkan sub_area_code
        const groups = {};
        checklists.forEach(chk => {
            const code = chk.sub_area_code;
            if (!groups[code]) {
                groups[code] = {
                    code: code,
                    name: chk.sub_area_name,
                    items: []
                };
            }
            groups[code].items.push(chk);
        });

        // Tentukan sub-area yang aktif
        let savedSubAreas = [];
        if (item && item.selected_sub_areas) {
            let s = item.selected_sub_areas;
            if (typeof s === 'string') {
                try { s = JSON.parse(s); } catch (e) { s = []; }
            }
            if (Array.isArray(s)) savedSubAreas = s;
        }

        // Fallback jika belum tersimpan: centang sub-area yang ada jawabannya, atau sub-area pertama
        if (savedSubAreas.length === 0 && existingAnswers && existingAnswers.length > 0) {
            const answeredIds = existingAnswers.map(a => Number(a.checklist_id));
            checklists.forEach(chk => {
                if (answeredIds.includes(Number(chk.id)) && !savedSubAreas.includes(chk.sub_area_code)) {
                    savedSubAreas.push(chk.sub_area_code);
                }
            });
        }
        if (savedSubAreas.length === 0) {
            const firstCode = Object.keys(groups)[0];
            if (firstCode) savedSubAreas = [firstCode];
        }

        let html = `<div class="accordion" id="accordionChecklistKka">`;

        for (const code in groups) {
            const grp = groups[code];
            const collapseId = `collapseSubArea_${code}`;
            const isChecked = savedSubAreas.includes(code);

            html += `
            <div class="accordion-item" style="border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 0.65rem; overflow:hidden;">
                <h2 class="accordion-header" id="heading_${code}" style="background:#f8fafc; margin: 0;">
                    <div class="d-flex justify-content-between align-items-center w-100 p-2 px-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <input type="checkbox" class="form-check-input subarea-checkbox" id="chk_subarea_${code}" value="${code}" 
                                style="width: 1.15rem; height: 1.15rem; cursor: pointer; margin: 0;"
                                onchange="onSubAreaToggle('${code}', this.checked)"
                                onclick="event.stopPropagation();"
                                ${isChecked ? 'checked' : ''}
                                title="Centang untuk mengaktifkan pengujian sub-area ini">
                            
                            <div class="d-flex align-items-center gap-2" style="cursor:pointer; user-select:none;" onclick="toggleAccordionSubArea('${collapseId}')">
                                <i class="bi ${isChecked ? 'bi-chevron-down' : 'bi-chevron-right'} toggle-icon-${collapseId}" style="font-size:0.8rem; transition: transform 0.2s;"></i>
                                <span class="fw-bold" style="font-size:0.83rem; color:#1e293b;">${grp.name}</span>
                                <span class="badge bg-light text-secondary border" style="font-size:0.7rem;">${grp.items.length} Item</span>
                                <span class="badge bg-danger text-white subarea-tidak-badge" id="subTidakBadge_${code}" style="display:none; font-size:0.7rem;">0 Temuan Tidak</span>
                            </div>
                        </div>
                        <div>
                            <span class="badge ${isChecked ? 'bg-primary-subtle text-primary border' : 'bg-light text-muted border'} subarea-status-badge" id="subStatusBadge_${code}" style="font-size: 0.72rem;">${isChecked ? 'Aktif Digunakan' : 'Nonaktif / Diabaikan'}</span>
                        </div>
                    </div>
                </h2>
                <div id="${collapseId}" class="accordion-collapse" style="display: ${isChecked ? 'block' : 'none'};">
                    <div class="accordion-body p-0" id="subAreaBody_${code}">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size:0.78rem;">
                                <thead class="table-light" style="font-size:0.72rem; text-transform:uppercase;">
                                    <tr>
                                        <th style="width: 5%; text-align:center;">No</th>
                                        <th style="width: 25%;">Sub-Area / Objek</th>
                                        <th style="width: 40%;">Item Checklist</th>
                                        <th style="width: 14%; text-align:center;">Jawaban</th>
                                        <th style="width: 16%;">Keterangan / Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;

            grp.items.forEach(chk => {
                const saved = answerMap[chk.id];
                const jawaban = saved ? saved.jawaban : '';
                const ket = saved ? saved.keterangan : '';
                const isYa = (jawaban === 'Ya');
                const isTidak = (jawaban === 'Tidak');

                html += `
                    <tr id="row_chk_${chk.id}" class="${isTidak ? 'table-danger' : ''}">
                        <td style="text-align:center; color:#64748b;">${chk.item_no}</td>
                        <td style="color:#1e293b; font-weight:500;">${chk.sub_area_name}</td>
                        <td style="color:#1e293b;">${chk.item_checklist}</td>
                        <td style="text-align:center;">
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check chk-answer" name="chk_answer_${chk.id}" id="chk_ya_${chk.id}" value="Ya" data-subarea="${code}" autocomplete="off" onchange="onChecklistChange()" ${isYa ? 'checked' : ''}>
                                <label class="btn btn-outline-success py-0 px-2" for="chk_ya_${chk.id}" style="font-size:0.72rem;">[✓] Ya</label>

                                <input type="radio" class="btn-check chk-answer" name="chk_answer_${chk.id}" id="chk_tidak_${chk.id}" value="Tidak" data-subarea="${code}" autocomplete="off" onchange="onChecklistChange()" ${isTidak ? 'checked' : ''}>
                                <label class="btn btn-outline-danger py-0 px-2" for="chk_tidak_${chk.id}" style="font-size:0.72rem;">[✗] Tidak</label>
                            </div>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm chk-keterangan" id="chk_ket_${chk.id}" data-checklist-id="${chk.id}" data-subarea="${code}" placeholder="Catatan opsional..." value="${ket.replace(/"/g, '&quot;')}" style="font-size:0.75rem;">
                        </td>
                    </tr>
                `;
            });

            html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            `;
        }

        html += `</div>`;
        container.innerHTML = html;
    }

    /**
     * Handler centang checkbox Sub-Area
     */
    function onSubAreaToggle(code, isChecked) {
        const collapseId = `collapseSubArea_${code}`;
        const el = document.getElementById(collapseId);
        const icon = document.querySelector(`.toggle-icon-${collapseId}`);
        const statusBadge = document.getElementById(`subStatusBadge_${code}`);

        if (statusBadge) {
            statusBadge.innerText = isChecked ? 'Aktif Digunakan' : 'Nonaktif / Diabaikan';
            statusBadge.className = isChecked ? 'badge bg-primary-subtle text-primary border subarea-status-badge' : 'badge bg-light text-muted border subarea-status-badge';
        }

        if (isChecked) {
            if (el) el.style.display = 'block';
            if (icon) {
                icon.classList.remove('bi-chevron-right');
                icon.classList.add('bi-chevron-down');
            }
        } else {
            if (el) el.style.display = 'none';
            if (icon) {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-right');
            }
            document.querySelectorAll(`.chk-answer[data-subarea="${code}"]`).forEach(r => {
                r.checked = false;
                const chkId = r.name.replace('chk_answer_', '');
                const row = document.getElementById(`row_chk_${chkId}`);
                if (row) row.classList.remove('table-danger', 'table-danger-subtle');
            });
            document.querySelectorAll(`.chk-keterangan[data-subarea="${code}"]`).forEach(inp => {
                inp.value = '';
            });
        }

        onChecklistChange();
    }

    /**
     * Toggle buka/tutup accordion
     */
    function toggleAccordionSubArea(collapseId) {
        const el = document.getElementById(collapseId);
        if (!el) return;
        const isVisible = (el.style.display !== 'none');
        el.style.display = isVisible ? 'none' : 'block';
        const icon = document.querySelector(`.toggle-icon-${collapseId}`);
        if (icon) {
            if (isVisible) {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-right');
            } else {
                icon.classList.remove('bi-chevron-right');
                icon.classList.add('bi-chevron-down');
            }
        }
    }

    /**
     * Set semua item dari sub-area aktif menjadi [✓] Ya
     */
    function setSemuaYa() {
        const checkedBoxes = Array.from(document.querySelectorAll('.subarea-checkbox:checked'));
        const checkedSubAreas = checkedBoxes.map(cb => cb.value);

        if (checkedSubAreas.length === 0) {
            alert('Silakan aktifkan minimal satu grup Sub-Area Checklist terlebih dahulu.');
            return;
        }

        document.querySelectorAll('.chk-answer[value="Ya"]').forEach(r => {
            const sub = r.getAttribute('data-subarea');
            if (checkedSubAreas.includes(sub)) {
                r.checked = true;
            }
        });

        onChecklistChange();
    }

    /**
     * Evaluasi Reaktif: Hitung Ya/Tidak, kendalikan form temuan, & hitung matriks risiko
     */
    function onChecklistChange() {
        const checkedBoxes = Array.from(document.querySelectorAll('.subarea-checkbox:checked'));
        const checkedSubAreas = checkedBoxes.map(cb => cb.value);

        // 1. Hitung total item aktif
        let totalActiveItems = 0;
        if (window.masterChecklists && Array.isArray(window.masterChecklists)) {
            totalActiveItems = window.masterChecklists.filter(chk => checkedSubAreas.includes(chk.sub_area_code)).length;
        }
        const totalItemsHeaderEl = document.getElementById('checklistTotalItemsCount');
        if (totalItemsHeaderEl) {
            totalItemsHeaderEl.innerText = totalActiveItems;
        }

        // 2. Hitung jumlah Ya dan Tidak
        let yaCount = 0;
        let tidakCount = 0;
        const subAreaTidakMap = {};

        document.querySelectorAll('.chk-answer:checked').forEach(r => {
            const sub = r.getAttribute('data-subarea');
            if (!checkedSubAreas.includes(sub)) return;

            const chkId = r.name.replace('chk_answer_', '');
            const row = document.getElementById(`row_chk_${chkId}`);

            if (r.value === 'Tidak') {
                tidakCount++;
                subAreaTidakMap[sub] = (subAreaTidakMap[sub] || 0) + 1;
                if (row) row.classList.add('table-danger');
            } else {
                yaCount++;
                if (row) row.classList.remove('table-danger', 'table-danger-subtle');
            }
        });

        const badgeYa = document.getElementById('badgeTotalYa');
        const badgeTidak = document.getElementById('badgeTotalTidak');
        if (badgeYa) badgeYa.innerText = `${yaCount} Ya`;
        if (badgeTidak) badgeTidak.innerText = `${tidakCount} Tidak`;

        // SubArea badges
        document.querySelectorAll('.subarea-tidak-badge').forEach(b => {
            const code = b.id.replace('subTidakBadge_', '');
            const cnt = checkedSubAreas.includes(code) ? (subAreaTidakMap[code] || 0) : 0;
            if (cnt > 0) {
                b.innerText = `${cnt} Temuan Tidak`;
                b.style.display = 'inline-block';
            } else {
                b.style.display = 'none';
            }
        });

        // 3. Kemunculan Kondisional Form Temuan
        const containerExc = document.getElementById('containerExceptionFields');
        const statusUjiEl = document.getElementById('f_status_uji');

        if (tidakCount >= 1) {
            if (containerExc) containerExc.style.display = 'block';
            if (statusUjiEl) statusUjiEl.value = 'Ada Exception';
        } else {
            if (containerExc) containerExc.style.display = 'none';
            if (statusUjiEl) statusUjiEl.value = 'Sesuai';
        }

        // 4. Hitung Matriks Risiko
        hitungRiskLevel(tidakCount);
    }

    /**
     * Hitung Matriks Risiko 5x5 dan Level Risiko
     */
    function hitungRiskLevel(tidakCount = 0) {
        const d = parseInt(document.getElementById('f_skor_dampak').value) || 1;
        const k = parseInt(document.getElementById('f_skor_kemungkinan').value) || 1;
        const skor = d * k;
        const crit = document.getElementById('f_critical_trigger').value;

        const dispSkor = document.getElementById('displaySkorRisiko');
        if (dispSkor) dispSkor.innerText = skor;

        const badge = document.getElementById('badgeRiskLevel');
        const fraudBox = document.getElementById('boxFraudFlagging');
        let level = 'Low';
        let bg = '#dcfce7';
        let col = '#16a34a';
        let border = '#bbf7d0';

        if (crit === 'Ya' || skor >= 20) {
            level = 'Kritis';
            bg = '#fee2e2';
            col = '#dc2626';
            border = '#fca5a5';
        } else if (skor >= 12) {
            level = 'High';
            bg = '#ffedd5';
            col = '#ea580c';
            border = '#fed7aa';
        } else if (skor >= 6) {
            level = 'Moderate';
            bg = '#fef9c3';
            col = '#ca8a04';
            border = '#fef08a';
        }

        if (badge) {
            badge.innerText = level;
            badge.style.background = bg;
            badge.style.color = col;
            badge.style.borderColor = border;
        }

        if (fraudBox) {
            fraudBox.style.display = (level === 'Kritis') ? 'block' : 'none';
        }
    }

    /**
     * Simpan hasil pengujian KKA melalui AJAX
     */
    function simpanPengujian(e) {
        e.preventDefault();
        const itemId = document.getElementById('f_item_id').value;
        if (!itemId) return;

        const checkedBoxes = Array.from(document.querySelectorAll('.subarea-checkbox:checked'));
        const checkedSubAreas = checkedBoxes.map(cb => cb.value);

        if (checkedSubAreas.length === 0) {
            alert('Harap centang minimal 1 grup Sub-Area Checklist sebelum menyimpan hasil pemeriksaan!');
            return;
        }

        // Kumpulkan jawaban checklist
        const checklistAnswers = [];
        let unansweredCount = 0;

        if (window.masterChecklists && Array.isArray(window.masterChecklists)) {
            window.masterChecklists.forEach(chk => {
                if (!checkedSubAreas.includes(chk.sub_area_code)) return;

                const yaRadio = document.getElementById(`chk_ya_${chk.id}`);
                const tidakRadio = document.getElementById(`chk_tidak_${chk.id}`);
                const ketInput = document.getElementById(`chk_ket_${chk.id}`);

                let ansVal = null;
                if (yaRadio && yaRadio.checked) ansVal = 'Ya';
                else if (tidakRadio && tidakRadio.checked) ansVal = 'Tidak';
                else unansweredCount++;

                if (ansVal) {
                    checklistAnswers.push({
                        checklist_id: chk.id,
                        jawaban: ansVal,
                        keterangan: ketInput ? ketInput.value : ''
                    });
                }
            });
        }

        if (unansweredCount > 0) {
            if (!confirm(`Terdapat ${unansweredCount} item checklist pada grup terpilih yang belum dijawab. Apakah Anda yakin ingin melanjutkan penyimpanan?`)) {
                return;
            }
        }

        const tidakCount = checklistAnswers.filter(a => a.jawaban === 'Tidak').length;
        if (tidakCount > 0) {
            const exc = document.getElementById('f_jenis_exception').value.trim();
            const hasilUji = document.getElementById('f_hasil_uji').value.trim();
            const klarif = document.getElementById('f_klarifikasi_unit').value.trim();

            if (!exc) {
                alert('Jenis Exception wajib diisi karena terdapat checklist bernilai [✗] Tidak!');
                document.getElementById('f_jenis_exception').focus();
                return;
            }
            if (!hasilUji) {
                alert('Hasil Pengujian Lapangan wajib diisi karena terdapat checklist bernilai [✗] Tidak!');
                document.getElementById('f_hasil_uji').focus();
                return;
            }
            if (!klarif) {
                alert('Klarifikasi Unit Kerja wajib diisi karena terdapat checklist bernilai [✗] Tidak!');
                document.getElementById('f_klarifikasi_unit').focus();
                return;
            }
        }

        const btn = document.getElementById('btnSimpanUji');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyimpan...`;

        const formData = new FormData();
        formData.append('checklist_answers', JSON.stringify(checklistAnswers));
        formData.append('selected_sub_areas', JSON.stringify(checkedSubAreas));
        formData.append('status_uji', document.getElementById('f_status_uji').value);
        formData.append('jenis_exception', document.getElementById('f_jenis_exception').value);
        formData.append('klarifikasi_unit', document.getElementById('f_klarifikasi_unit').value);
        formData.append('hasil_uji', document.getElementById('f_hasil_uji').value);
        formData.append('kondisi', document.getElementById('f_kondisi').value);
        formData.append('kriteria', document.getElementById('f_kriteria').value);
        formData.append('sebab', document.getElementById('f_sebab').value);
        formData.append('dampak', document.getElementById('f_dampak').value);
        formData.append('simpulan_ra', document.getElementById('f_simpulan_ra').value);
        formData.append('status_klarifikasi', document.getElementById('f_status_klarifikasi').value);
        formData.append('skor_dampak', document.getElementById('f_skor_dampak').value);
        formData.append('skor_kemungkinan', document.getElementById('f_skor_kemungkinan').value);
        formData.append('critical_trigger', document.getElementById('f_critical_trigger').value);

        const revEl = document.getElementById('f_status_review');
        if (revEl) {
            formData.append('status_review', revEl.value);
            formData.append('catatan_reviewer', document.getElementById('f_catatan_reviewer')?.value || '');
        }

        const fileInput = document.getElementById('f_file_bukti');
        if (fileInput && fileInput.files.length > 0) {
            formData.append('file_bukti', fileInput.files[0]);
        }

        const updateUrl = `{{ $updateUrlPrefix }}/${itemId}`;

        fetch(updateUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (modalInstance) modalInstance.hide();
                window.location.reload();
            } else {
                throw new Error(data.message || 'Gagal menyimpan pengujian.');
            }
        })
        .catch(err => {
            alert('Error: ' + err.message);
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }
</script>
@endpush
