{{-- 
    MODAL PENGUJIAN CHECKLIST KKA STANDAR (SOP 04.2 & DAILY REGISTER SPEC)
    Dipakai seragam di seluruh 6 Area KKA Onsite (Teller, DPK, Kredit, Biaya, ATM, Pengaduan)
--}}

@php
    $userRole = strtolower(auth()->user()->role ?? '');
    $isReviewer = in_array($userRole, ['admin', 'kabag_ra', 'kadiv_skai']);
    $isKredit = (isset($sheetTitle) && ($sheetTitle === 'Kredit' || $sheetTitle === 'KKA_Kredit'));
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
                                <span class="badge bg-light text-muted border small">{{ $isKredit ? 'BPP RA: SOP-RA-03, 04, 05, 10' : 'SOP 04.2' }}</span>
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

    const isKreditSheet = {{ $isKredit ? 'true' : 'false' }};

    function getKreditSelectStyle(val) {
        if (val === '✓' || val === 'Ya') {
            return 'background-color:#dcfce7; color:#15803d; border-color:#86efac;';
        } else if (val === '✗' || val === 'Tidak') {
            return 'background-color:#fee2e2; color:#b91c1c; border-color:#fca5a5;';
        } else if (val === 'N/A') {
            return 'background-color:#f1f5f9; color:#475569; border-color:#cbd5e1;';
        }
        return 'background-color:#fef9c3; color:#854d0e; border-color:#fde047;';
    }

    function setKreditAllSesuai() {
        const checkedBoxes = Array.from(document.querySelectorAll('.subarea-checkbox:checked'));
        const checkedSubAreas = checkedBoxes.map(cb => cb.value);
        document.querySelectorAll('.chk-kredit-select').forEach(sel => {
            const sub = sel.getAttribute('data-subarea');
            if (checkedSubAreas.length === 0 || checkedSubAreas.includes(sub)) {
                sel.value = '✓';
            }
        });
        onChecklistChange();
    }

    function setKreditAllNA() {
        const checkedBoxes = Array.from(document.querySelectorAll('.subarea-checkbox:checked'));
        const checkedSubAreas = checkedBoxes.map(cb => cb.value);
        document.querySelectorAll('.chk-kredit-select').forEach(sel => {
            const sub = sel.getAttribute('data-subarea');
            if (checkedSubAreas.length === 0 || checkedSubAreas.includes(sub)) {
                sel.value = 'N/A';
            }
        });
        onChecklistChange();
    }

    function setSubAreaKreditVal(code, val, event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        document.querySelectorAll(`.chk-kredit-select[data-subarea="${code}"]`).forEach(sel => {
            sel.value = val;
        });
        onChecklistChange();
    }

    function toggleAllSubAreas(expand) {
        document.querySelectorAll('#accordionChecklistKka .accordion-collapse').forEach(el => {
            el.style.display = expand ? 'block' : 'none';
            const collapseId = el.id;
            const icon = document.querySelector(`.toggle-icon-${collapseId}`);
            if (icon) {
                if (expand) {
                    icon.classList.remove('bi-chevron-right');
                    icon.classList.add('bi-chevron-down');
                } else {
                    icon.classList.remove('bi-chevron-down');
                    icon.classList.add('bi-chevron-right');
                }
            }
        });
    }

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
            if (isKreditSheet) {
                // Standar 134 item KKA Kredit: aktifkan seluruh 13 sub-area by default
                savedSubAreas = Object.keys(groups);
            } else {
                const firstCode = Object.keys(groups)[0];
                if (firstCode) savedSubAreas = [firstCode];
            }
        }

        let html = '';

        // TAMPILKAN HEADER & PETUNJUK PENGISIAN & REKAP KHUSUS KKA KREDIT (134 ITEM)
        if (isKreditSheet) {
            html += `
                <div class="card mb-3 border-0 shadow-sm" style="border-radius:10px; border:1px solid #cbd5e1; overflow:hidden;">
                    <div style="background:#1e3a8a; color:#fff; padding:10px 16px; font-weight:700; font-size:0.88rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
                        <div>
                            <i class="bi bi-journal-bookmark-fill me-1.5"></i> KKA KREDIT — PETUNJUK PENGISIAN &amp; REKAP
                        </div>
                        <div class="small opacity-75">BPP Resident Auditor Bank Sulteng — SOP-RA-03, 04, 05, 10</div>
                    </div>
                    <div class="card-body p-3 bg-white">
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered align-middle mb-0" style="font-size:0.79rem;">
                                <tbody>
                                    <tr>
                                        <th style="width:160px; background:#f8fafc; color:#334155; font-weight:600;">Dasar</th>
                                        <td style="color:#1e293b; font-weight:500;">BPP RA Bank Sulteng, SK Direksi No. 78/SK-DIR/BPD-ST/2026 (SOP-RA-03, 04, 05, 10)</td>
                                    </tr>
                                    <tr>
                                        <th style="background:#f8fafc; color:#334155; font-weight:600;">Kolom Cek</th>
                                        <td>Pilih dari dropdown: <strong>✓ (sesuai)</strong>, <strong>✗ (tidak sesuai)</strong>, atau <strong>N/A</strong></td>
                                    </tr>
                                    <tr>
                                        <th style="background:#f8fafc; color:#334155; font-weight:600;">Kolom Sumber</th>
                                        <td><span class="badge bg-primary-subtle text-primary border me-1">BPP</span> = bersumber dari dokumen BPP; <span class="badge bg-warning-subtle text-warning-emphasis border me-1">Saran RA</span> = usulan, perlu dikonfirmasi dengan Bank</td>
                                    </tr>
                                    <tr>
                                        <th style="background:#f8fafc; color:#334155; font-weight:600;">Sel kuning</th>
                                        <td><span class="px-2 py-0.5 rounded border" style="background:#fef9c3; border-color:#fde047; font-weight:600; color:#854d0e;">Sel yang harus diisi oleh auditor</span> (Kolom Cek &amp; Keterangan/Catatan)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px;">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <div class="fw-bold text-primary" style="font-size:0.83rem;">
                                    <i class="bi bi-bar-chart-fill me-1"></i> REKAP (otomatis dari sheet KKA Kredit)
                                </div>
                                <div class="d-flex gap-1.5 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-success fw-bold py-1 px-2.5" style="font-size:0.75rem;" onclick="setKreditAllSesuai()">
                                        <i class="bi bi-check2-all me-1"></i> Set Semua [✓] Sesuai
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size:0.75rem;" onclick="setKreditAllNA()">
                                        Set Semua N/A
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size:0.75rem;" onclick="toggleAllSubAreas(true)">
                                        <i class="bi bi-arrows-expand me-1"></i> Buka Semua
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size:0.75rem;" onclick="toggleAllSubAreas(false)">
                                        <i class="bi bi-arrows-collapse me-1"></i> Tutup Semua
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2.5 align-items-center" style="font-size:0.8rem;">
                                <div class="d-flex align-items-center gap-1.5 bg-white px-2.5 py-1 rounded border">
                                    <span class="text-muted">Total item checklist:</span>
                                    <span class="badge bg-secondary fw-bold" id="kredit_rekap_total" style="font-size:0.82rem;">${checklists.length}</span>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 bg-white px-2.5 py-1 rounded border">
                                    <span class="text-muted">Item sudah dicek (Cek terisi):</span>
                                    <span class="badge bg-primary fw-bold" id="kredit_rekap_dicek" style="font-size:0.82rem;">0</span>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 bg-white px-2.5 py-1 rounded border">
                                    <span class="text-muted">Item sesuai (✓):</span>
                                    <span class="badge bg-success fw-bold" id="kredit_rekap_sesuai" style="font-size:0.82rem;">0</span>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 bg-white px-2.5 py-1 rounded border">
                                    <span class="text-muted">Item tidak sesuai (✗):</span>
                                    <span class="badge bg-danger fw-bold" id="kredit_rekap_tidak_sesuai" style="font-size:0.82rem;">0</span>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 bg-white px-2.5 py-1 rounded border">
                                    <span class="text-muted">Item N/A:</span>
                                    <span class="badge bg-warning text-dark fw-bold" id="kredit_rekap_na" style="font-size:0.82rem;">0</span>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 bg-white px-2.5 py-1 rounded border border-primary">
                                    <span class="text-muted fw-bold">Persentase item sudah dicek:</span>
                                    <span class="badge bg-info text-dark fw-bold" id="kredit_rekap_persen" style="font-size:0.85rem;">0.0%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        html += `<div class="accordion" id="accordionChecklistKka">`;

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
                        <div class="d-flex align-items-center gap-2">
                            ${isKreditSheet ? `
                            <div class="d-flex gap-1" onclick="event.stopPropagation();">
                                <button type="button" class="btn btn-sm btn-outline-success py-0 px-1.5" style="font-size:0.68rem;" onclick="setSubAreaKreditVal('${code}', '✓', event)" title="Set sub-area ini ke [✓] Sesuai">[✓] Sesuai</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1.5" style="font-size:0.68rem;" onclick="setSubAreaKreditVal('${code}', 'N/A', event)" title="Set sub-area ini ke N/A">N/A</button>
                            </div>
                            ` : ''}
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
                                        <th style="width: ${isKreditSheet ? '20%' : '25%'};">Sub-Area / Objek</th>
                                        <th style="width: ${isKreditSheet ? '34%' : '40%'};">Item Checklist</th>
                                        ${isKreditSheet ? '<th style="width: 13%; text-align:center;">Sumber</th>' : ''}
                                        <th style="width: 14%; text-align:center; ${isKreditSheet ? 'background:#fef9c3; color:#854d0e; border-bottom:2px solid #fde047;' : ''}">${isKreditSheet ? 'Cek *' : 'Jawaban'}</th>
                                        <th style="width: ${isKreditSheet ? '14%' : '16%'}; ${isKreditSheet ? 'background:#fef9c3; color:#854d0e; border-bottom:2px solid #fde047;' : ''}">Keterangan / Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;

            grp.items.forEach(chk => {
                const saved = answerMap[chk.id];
                const rawJawaban = saved ? saved.jawaban : '';
                const ket = saved ? saved.keterangan : '';

                let jawaban = rawJawaban;
                if (isKreditSheet) {
                    if (rawJawaban === 'Ya') jawaban = '✓';
                    if (rawJawaban === 'Tidak') jawaban = '✗';
                }
                const isTidak = (jawaban === 'Tidak' || jawaban === '✗');
                const isYa = (jawaban === 'Ya' || jawaban === '✓');

                html += `
                    <tr id="row_chk_${chk.id}" class="${isTidak ? 'table-danger' : ''}">
                        <td style="text-align:center; color:#64748b; font-weight:600;">${chk.item_no}</td>
                        <td style="color:#1e293b; font-weight:500;">${chk.sub_area_name}</td>
                        <td style="color:#1e293b;">${chk.item_checklist}</td>
                        ${isKreditSheet ? `
                        <td style="text-align:center;">
                            <span class="badge ${chk.sumber && chk.sumber.includes('Saran') ? 'bg-warning-subtle text-warning-emphasis border' : 'bg-primary-subtle text-primary border'}" style="font-size:0.71rem;">${chk.sumber || 'BPP'}</span>
                        </td>
                        ` : ''}
                        <td style="text-align:center; ${isKreditSheet ? 'background:#fffbeb;' : ''}">
                            ${isKreditSheet ? `
                            <select class="form-select form-select-sm chk-answer chk-kredit-select" 
                                    name="chk_answer_${chk.id}" 
                                    id="chk_answer_${chk.id}" 
                                    data-subarea="${code}" 
                                    data-chk-id="${chk.id}"
                                    onchange="onChecklistChange()" 
                                    style="font-size:0.75rem; font-weight:600; min-width:115px; ${getKreditSelectStyle(jawaban)}">
                                <option value="" ${jawaban === '' ? 'selected' : ''}>-- Pilih Cek --</option>
                                <option value="✓" ${jawaban === '✓' ? 'selected' : ''} style="color:#15803d; font-weight:600;">✓ (Sesuai)</option>
                                <option value="✗" ${jawaban === '✗' ? 'selected' : ''} style="color:#b91c1c; font-weight:600;">✗ (Tidak Sesuai)</option>
                                <option value="N/A" ${jawaban === 'N/A' ? 'selected' : ''} style="color:#475569; font-weight:600;">N/A</option>
                            </select>
                            ` : `
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check chk-answer" name="chk_answer_${chk.id}" id="chk_ya_${chk.id}" value="Ya" data-subarea="${code}" autocomplete="off" onchange="onChecklistChange()" ${isYa ? 'checked' : ''}>
                                <label class="btn btn-outline-success py-0 px-2" for="chk_ya_${chk.id}" style="font-size:0.72rem;">[✓] Ya</label>

                                <input type="radio" class="btn-check chk-answer" name="chk_answer_${chk.id}" id="chk_tidak_${chk.id}" value="Tidak" data-subarea="${code}" autocomplete="off" onchange="onChecklistChange()" ${isTidak ? 'checked' : ''}>
                                <label class="btn btn-outline-danger py-0 px-2" for="chk_tidak_${chk.id}" style="font-size:0.72rem;">[✗] Tidak</label>
                            </div>
                            `}
                        </td>
                        <td style="${isKreditSheet ? 'background:#fffbeb;' : ''}">
                            <input type="text" class="form-control form-control-sm chk-keterangan" id="chk_ket_${chk.id}" data-checklist-id="${chk.id}" data-subarea="${code}" placeholder="Catatan opsional..." value="${ket.replace(/"/g, '&quot;')}" style="font-size:0.75rem; ${isKreditSheet ? 'background-color:#fefce8; border-color:#fef08a;' : ''}">
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

        // 2. Hitung jumlah jawaban checklist
        let yaCount = 0;
        let tidakCount = 0;
        let naCount = 0;
        let dicekCount = 0;
        const subAreaTidakMap = {};

        if (isKreditSheet) {
            document.querySelectorAll('.chk-kredit-select').forEach(sel => {
                const sub = sel.getAttribute('data-subarea');
                if (!checkedSubAreas.includes(sub)) return;

                const val = sel.value;
                const chkId = sel.getAttribute('data-chk-id');
                const row = document.getElementById(`row_chk_${chkId}`);

                sel.style.cssText = 'font-size:0.75rem; font-weight:600; min-width:115px; ' + getKreditSelectStyle(val);

                if (val === '✓' || val === 'Ya') {
                    yaCount++;
                    dicekCount++;
                    if (row) row.classList.remove('table-danger', 'table-danger-subtle');
                } else if (val === '✗' || val === 'Tidak') {
                    tidakCount++;
                    dicekCount++;
                    subAreaTidakMap[sub] = (subAreaTidakMap[sub] || 0) + 1;
                    if (row) row.classList.add('table-danger');
                } else if (val === 'N/A') {
                    naCount++;
                    dicekCount++;
                    if (row) row.classList.remove('table-danger', 'table-danger-subtle');
                } else {
                    if (row) row.classList.remove('table-danger', 'table-danger-subtle');
                }
            });

            // Update Rekap Card KKA Kredit (Otomatis dari sheet KKA Kredit)
            const rekapTotalEl = document.getElementById('kredit_rekap_total');
            const rekapDicekEl = document.getElementById('kredit_rekap_dicek');
            const rekapSesuaiEl = document.getElementById('kredit_rekap_sesuai');
            const rekapTidakEl = document.getElementById('kredit_rekap_tidak_sesuai');
            const rekapNaEl = document.getElementById('kredit_rekap_na');
            const rekapPersenEl = document.getElementById('kredit_rekap_persen');

            if (rekapTotalEl) rekapTotalEl.innerText = totalActiveItems;
            if (rekapDicekEl) rekapDicekEl.innerText = dicekCount;
            if (rekapSesuaiEl) rekapSesuaiEl.innerText = yaCount;
            if (rekapTidakEl) rekapTidakEl.innerText = tidakCount;
            if (rekapNaEl) rekapNaEl.innerText = naCount;
            if (rekapPersenEl) {
                const pct = totalActiveItems > 0 ? ((dicekCount / totalActiveItems) * 100).toFixed(1) : '0.0';
                rekapPersenEl.innerText = `${pct}%`;
            }

            const badgeYa = document.getElementById('badgeTotalYa');
            const badgeTidak = document.getElementById('badgeTotalTidak');
            if (badgeYa) badgeYa.innerText = `${yaCount} Sesuai (✓)`;
            if (badgeTidak) badgeTidak.innerText = `${tidakCount} Tidak Sesuai (✗)`;
        } else {
            document.querySelectorAll('.chk-answer:checked').forEach(r => {
                const sub = r.getAttribute('data-subarea');
                if (!checkedSubAreas.includes(sub)) return;

                const chkId = r.name.replace('chk_answer_', '');
                const row = document.getElementById(`row_chk_${chkId}`);

                if (r.value === 'Tidak' || r.value === '✗') {
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
        }

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

                const selectEl = document.getElementById(`chk_answer_${chk.id}`);
                const yaRadio = document.getElementById(`chk_ya_${chk.id}`);
                const tidakRadio = document.getElementById(`chk_tidak_${chk.id}`);
                const ketInput = document.getElementById(`chk_ket_${chk.id}`);

                let ansVal = null;
                if (selectEl) {
                    ansVal = selectEl.value || null;
                } else if (yaRadio && yaRadio.checked) {
                    ansVal = 'Ya';
                } else if (tidakRadio && tidakRadio.checked) {
                    ansVal = 'Tidak';
                }

                if (!ansVal) {
                    unansweredCount++;
                }

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
            if (!confirm(`Terdapat ${unansweredCount} item checklist pada grup terpilih yang belum dicek ([✓] Sesuai, [✗] Tidak Sesuai, atau N/A). Apakah Anda yakin ingin melanjutkan penyimpanan?`)) {
                return;
            }
        }

        const tidakCount = checklistAnswers.filter(a => a.jawaban === 'Tidak' || a.jawaban === '✗').length;
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
