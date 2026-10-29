@extends('layouts.app')
@section('title', 'Penerbitan Penugasan Risk Trigger Visit - SOP 06')

@section('content')
<style>
    /* Styling Harmonized with Register Trigger & Daily Register */
    :root {
        --primary-blue: #1e3a8a;
    }

    .form-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .form-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 1.1rem 1.5rem;
    }
    .form-body {
        padding: 1.5rem;
    }
    .form-label {
        font-size: 0.84rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 0.4rem;
        display: block;
    }
    .form-control, .form-select {
        font-size: 0.9rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0.55rem 0.85rem;
        color: #1e293b;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--bs-blue);
        box-shadow: 0 0 0 3px rgba(30,75,133,0.12);
    }
</style>

<!-- Header Harmonized SOP 06 -->
<div class="rtv-header">
    <div>
        <div class="rtv-breadcrumb">
            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a> &rsaquo;
            <a href="{{ route('risk-trigger.assignment') }}">Assignment &amp; Scope</a> &rsaquo;
            <span class="text-dark fw-semibold">Penerbitan Penugasan</span>
        </div>
        <h4 class="rtv-title">
            <i class="bi bi-send-plus-fill text-primary"></i> Penerbitan Penugasan Risk Trigger Visit
            <span class="badge-wp">FORM ASSIGNMENT</span>
            <span class="badge-role-scope"><i class="bi bi-shield-check text-warning me-1"></i> Khusus Reviewer / Admin</span>
        </h4>
        <p class="rtv-subtitle">
            Menerbitkan Assignment &amp; Scope dan menghasilkan Program Kerja Terarah secara otomatis dari Register Trigger terpilih.
        </p>
    </div>

    <div>
        <a href="{{ route('risk-trigger.assignment') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="bi bi-arrow-left me-1"></i>
            <span>Kembali ke Daftar Assignment</span>
        </a>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3 border-0 shadow-sm" style="border-radius: 10px;" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('risk-trigger.assignment.store') }}" method="POST">
    @csrf

    <div class="row g-3">
        {{-- SISI KIRI: PEMILIHAN TRIGGER & PARAMETER OTORISASI --}}
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 mb-3" style="background: #ffffff; border-radius: 12px; border: 1px solid var(--border-color);">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-bullseye text-primary me-2"></i>1. Pilih Cabang &amp; Trigger Sumber (Status: RISK TRIGGER VISIT)
                    </h6>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.76rem;">
                        <i class="bi bi-funnel-fill me-1"></i>Filter Bertingkat (Cabang)
                    </span>
                </div>
                <div class="card-body p-4">
                    {{-- LANGKAH 1A: PILIH KANTOR CABANG / UNIT TERLEBIH DAHULU --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 0.85rem;">
                            <i class="bi bi-buildings-fill text-primary me-1"></i> Kantor Cabang / Unit Target Visit <span class="text-danger">*</span>
                        </label>
                        <select id="selectBranch" class="form-select @error('filter_unit') is-invalid @enderror" onchange="onBranchChanged(this.value)">
                            <option value="">-- Pilih Kantor Cabang / Unit Terlebih Dahulu --</option>
                            @foreach($units as $u)
                                @php
                                    $cnt = $triggerCountByUnit[$u->unit_code] ?? 0;
                                    $isSelectedUnit = old('filter_unit', $selectedKodeUnit) == $u->unit_code;
                                @endphp
                                <option value="{{ $u->unit_code }}" 
                                        data-name="{{ $u->unit_name }}" 
                                        data-count="{{ $cnt }}"
                                        {{ $isSelectedUnit ? 'selected' : '' }}>
                                    {{ $u->unit_code }} - {{ $u->unit_name }} ({{ $cnt }} Trigger Siap Visit)
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted" style="font-size: 0.78rem;">
                            Pilih kantor cabang terlebih dahulu agar daftar Trigger ID hanya memuat trigger dari cabang tersebut.
                        </small>
                    </div>

                    {{-- LANGKAH 1B: DROPDOWN TRIGGER ID TERFILTER KHUSUS CABANG TERPILIH --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 0.85rem;">
                            <i class="bi bi-bullseye text-primary me-1"></i> Trigger ID Cabang Terpilih <span class="text-danger">*</span>
                        </label>
                        <select name="trigger_id" id="selectTrigger" class="form-select @error('trigger_id') is-invalid @enderror" required onchange="onTriggerSelected(this.value)" disabled>
                            <option value="">-- Pilih Kantor Cabang terlebih dahulu --</option>
                        </select>
                        @error('trigger_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        {{-- Alert Jika Cabang Belum Memiliki Trigger Siap Visit --}}
                        <div id="noTriggerNotice" class="alert alert-warning border-0 p-3 mt-2" style="display: none; border-radius: 8px; font-size: 0.84rem; background: #fffbeb; border-left: 4px solid #f59e0b !important;">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-info-circle-fill fs-5 text-warning flex-shrink-0"></i>
                                <div>
                                    <strong class="text-dark">Tidak Ditemukan Trigger Siap Visit:</strong>
                                    <p class="mb-2 mt-1 text-muted" style="font-size: 0.82rem;">
                                        Cabang <span id="noticeBranchName" class="fw-bold text-dark"></span> saat ini belum memiliki data trigger berstatus <strong>RISK TRIGGER VISIT</strong> yang menunggu penugasan.
                                    </p>
                                    <a href="{{ route('risk-trigger.create') }}" id="btnInputTriggerManual" class="btn btn-warning btn-sm fw-bold px-3 shadow-sm" style="font-size: 0.8rem; border-radius: 6px;">
                                        <i class="bi bi-plus-lg me-1"></i> Input Trigger Manual untuk Cabang Ini
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Informasi Rincian Trigger Terpilih --}}
                    <div id="triggerDetailBox" class="p-3 bg-light rounded-3 border mb-3" style="font-size: 0.85rem; display: {{ $selectedTrigger ? 'block' : 'none' }};">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-muted fw-bold text-uppercase" style="font-size: 0.74rem;">Rincian Data Pemicu:</span>
                            <span id="badgeRiskLevel" class="badge bg-danger">{{ $selectedTrigger?->risk_level_reassessment ?: 'TINGGI' }}</span>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <span class="text-muted d-block" style="font-size: 0.76rem;">Unit / Cabang:</span>
                                <span id="dispUnit" class="fw-bold text-dark">{{ $selectedTrigger?->kode_unit }} — {{ $selectedTrigger?->nama_unit }}</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block" style="font-size: 0.76rem;">Area Risiko:</span>
                                <span id="dispArea" class="fw-bold text-primary">{{ $selectedTrigger?->area }}</span>
                            </div>
                            <div class="col-12 mt-2">
                                <span class="text-muted d-block" style="font-size: 0.76rem;">Uraian Pemicu:</span>
                                <span id="dispUraian" class="text-dark">{{ $selectedTrigger?->uraian_trigger }}</span>
                            </div>
                            <div class="col-12 mt-2">
                                <span class="text-muted d-block" style="font-size: 0.76rem;">Periode &amp; Objek:</span>
                                <span id="dispPeriode" class="text-muted font-monospace" style="font-size: 0.8rem;">
                                    {{ $selectedTrigger?->periode_mulai?->format('d/m/Y') }} s.d. {{ $selectedTrigger?->periode_selesai?->format('d/m/Y') }} | {{ $selectedTrigger?->objek_key }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark" style="font-size: 0.85rem;">
                                Tanggal Rencana Visit (Kolom K) <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_visit" class="form-control @error('tanggal_visit') is-invalid @enderror" value="{{ old('tanggal_visit', date('Y-m-d')) }}" required>
                            @error('tanggal_visit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted" style="font-size: 0.76rem;">Target tanggal auditor mulai melaksanakan visit.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark" style="font-size: 0.85rem;">
                                Referensi Otorisasi (Kolom Q) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="referensi_otorisasi" class="form-control @error('referensi_otorisasi') is-invalid @enderror" placeholder="Contoh: ST/042/SKAI/X/2026 atau Memo Kadiv" value="{{ old('referensi_otorisasi') }}" required>
                            @error('referensi_otorisasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted" style="font-size: 0.76rem;">Nomor surat tugas / memo penetapan visit.</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PELAKSANA & REVIEWER --}}
            <div class="card shadow-sm border-0 mb-3" style="background: #ffffff; border-radius: 12px; border: 1px solid var(--border-color);">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-people-fill text-primary me-2"></i>2. Pelaksana &amp; Reviewer
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark" style="font-size: 0.85rem;">
                                RA Pelaksana Visit (Kolom O) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="ra_pelaksana" id="inputRaPelaksana" list="listRaUsers" class="form-control @error('ra_pelaksana') is-invalid @enderror" placeholder="Nama Resident Auditor" value="{{ old('ra_pelaksana', $selectedTrigger?->assigned_ra) }}" required>
                            <datalist id="listRaUsers">
                                @foreach($raUsers as $ra)
                                    <option value="{{ $ra->name }}">{{ $ra->name }} (Cabang: {{ $ra->cabang?->nama_cabang }})</option>
                                @endforeach
                            </datalist>
                            @error('ra_pelaksana')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted" style="font-size: 0.76rem;">Default otomatis dari penempatan cabang, dapat disesuaikan.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark" style="font-size: 0.85rem;">
                                Reviewer / Pemutus (Kolom P) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="reviewer" class="form-control @error('reviewer') is-invalid @enderror" value="{{ old('reviewer', auth()->user()->name) }}" required>
                            @error('reviewer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted" style="font-size: 0.76rem;">Nama Admin / Kabag RA / Reviewer SKAI penugas.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-dark" style="font-size: 0.85rem;">
                                Klasifikasi Visit (Kolom R) <span class="text-danger">*</span>
                            </label>
                            <select name="klasifikasi" class="form-select @error('klasifikasi') is-invalid @enderror" required>
                                <option value="Risk Trigger Visit Terarah" {{ old('klasifikasi') == 'Risk Trigger Visit Terarah' ? 'selected' : '' }}>Risk Trigger Visit Terarah (Standar SOP 06)</option>
                                <option value="Surprise Audit Lanjutan" {{ old('klasifikasi') == 'Surprise Audit Lanjutan' ? 'selected' : '' }}>Surprise Audit Lanjutan</option>
                                <option value="Pendalaman Fraud Khusus" {{ old('klasifikasi') == 'Pendalaman Fraud Khusus' ? 'selected' : '' }}>Pendalaman Fraud Khusus</option>
                                <option value="Monitoring Tindak Lanjut" {{ old('klasifikasi') == 'Monitoring Tindak Lanjut' ? 'selected' : '' }}>Monitoring Tindak Lanjut</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SISI KANAN: BATAS SCOPE & KRITERIA SELESAI --}}
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 mb-3" style="background: #ffffff; border-radius: 12px; border: 1px solid var(--border-color);">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-shield-lock-fill text-primary me-2"></i>3. Batas Scope &amp; Kriteria Selesai
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 0.85rem;">
                            Batas Scope (Kolom T)
                        </label>
                        <textarea name="batas_scope" id="inputBatasScope" rows="4" class="form-control" style="font-size: 0.85rem;">{{ old('batas_scope', 'Terbatas pada area terkait | Periode terkait | Tidak diperluas tanpa persetujuan reviewer') }}</textarea>
                        <small class="text-muted" style="font-size: 0.76rem;">Menjaga agar visit tetap terarah pada pemicu dan tidak melebar tanpa persetujuan.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 0.85rem;">
                            Kriteria Selesai (Kolom U)
                        </label>
                        <textarea name="kriteria_selesai" rows="4" class="form-control" style="font-size: 0.85rem;">{{ old('kriteria_selesai', 'Trigger dinilai terbukti/tidak terbukti; exception diklarifikasi; bukti memadai; keputusan TL, perluasan, atau eskalasi ditetapkan') }}</textarea>
                        <small class="text-muted" style="font-size: 0.76rem;">Kriteria penutupan pengujian di lapangan sesuai SOP 06.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 0.85rem;">
                            Langkah Tambahan RA (Opsional)
                        </label>
                        <textarea name="langkah_tambahan_ra" rows="3" class="form-control" placeholder="Instruksi khusus atau prosedur uji tambahan yang diperintahkan Reviewer..." style="font-size: 0.85rem;">{{ old('langkah_tambahan_ra') }}</textarea>
                    </div>

                    <div class="p-3 bg-primary-subtle text-primary-emphasis rounded-3 border border-primary-subtle" style="font-size: 0.82rem;">
                        <i class="bi bi-magic me-1"></i> <strong>Otomasi Fase 2:</strong> Saat assignment disimpan, sistem otomatis menghasilkan <strong>Visit ID unik</strong>, mengunci status workflow trigger menjadi <strong>VISIT DALAM PROSES</strong>, dan menerbitkan 1 baris <strong>Program Kerja Terarah (PKA)</strong> dengan formula ukuran sampel pemicu dan pembanding.
                    </div>
                </div>
                <div class="card-footer bg-light p-3 text-end border-top">
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" style="border-radius: 8px;">
                        <i class="bi bi-send-check me-1"></i> Terbitkan Penugasan Visit
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Data master seluruh trigger eligible yang belum diterbitkan assignment
const allEligibleTriggers = [
    @foreach($eligibleTriggers as $t)
    {
        id: {{ $t->id }},
        trigger_id: @json($t->trigger_id),
        kode_unit: @json($t->kode_unit),
        nama_unit: @json($t->nama_unit),
        area: @json($t->area),
        risk_level: @json($t->risk_level_reassessment),
        uraian: @json($t->uraian_trigger),
        bukti: @json($t->bukti_awal),
        periode_mulai: @json($t->periode_mulai ? $t->periode_mulai->format('d/m/Y') : '-'),
        periode_selesai: @json($t->periode_selesai ? $t->periode_selesai->format('d/m/Y') : '-'),
        objek: @json($t->objek_key ?: '-'),
        assigned_ra: @json($t->assigned_ra),
        kka_tujuan: @json($t->kka_tujuan),
        jumlah_kejadian: {{ (int)$t->jumlah_kejadian }},
    },
    @endforeach
];

// Data pemetaan RA pelaksana per cabang
const branchRaMap = @json($branchRaMap);

function normalizeUnitCode(code) {
    if (!code) return '';
    let str = String(code).trim().replace('BS-', '');
    return str.replace(/^0+/, '') || '0';
}

function onBranchChanged(selectedKodeUnit, preselectedTriggerId = null) {
    const selTrigger = document.getElementById('selectTrigger');
    const emptyNotice = document.getElementById('noTriggerNotice');
    const detailBox = document.getElementById('triggerDetailBox');
    const btnManual = document.getElementById('btnInputTriggerManual');

    // Reset pilihan trigger dan rincian
    selTrigger.innerHTML = '';
    detailBox.style.display = 'none';

    if (!selectedKodeUnit) {
        selTrigger.disabled = true;
        selTrigger.innerHTML = '<option value="">-- Pilih Kantor Cabang terlebih dahulu --</option>';
        if (emptyNotice) emptyNotice.style.display = 'none';
        return;
    }

    const normSelected = normalizeUnitCode(selectedKodeUnit);
    const branchSel = document.getElementById('selectBranch');
    const branchName = branchSel.options[branchSel.selectedIndex]?.getAttribute('data-name') || selectedKodeUnit;

    // Filter trigger HANYA yang cocok dengan cabang terpilih
    const filtered = allEligibleTriggers.filter(t => {
        return normalizeUnitCode(t.kode_unit) === normSelected;
    });

    if (filtered.length === 0) {
        selTrigger.disabled = true;
        selTrigger.innerHTML = '<option value="">-- Tidak ada trigger siap visit untuk cabang ini --</option>';
        if (emptyNotice) {
            emptyNotice.style.display = 'block';
            document.getElementById('noticeBranchName').textContent = selectedKodeUnit + ' - ' + branchName;
        }
        if (btnManual) {
            btnManual.href = "{{ route('risk-trigger.create') }}?kode_unit=" + encodeURIComponent(selectedKodeUnit);
        }
    } else {
        selTrigger.disabled = false;
        if (emptyNotice) emptyNotice.style.display = 'none';

        let html = `<option value="">-- Pilih Trigger ID (${filtered.length} pemicu tersedia) --</option>`;
        filtered.forEach(t => {
            const isSel = (preselectedTriggerId && (t.id == preselectedTriggerId || t.trigger_id == preselectedTriggerId));
            html += `<option value="${t.id}" data-id="${t.id}" ${isSel ? 'selected' : ''}>`
                 + `${t.trigger_id} — ${t.area} [${t.risk_level}] : ${t.uraian.substring(0, 75)}...`
                 + `</option>`;
        });
        selTrigger.innerHTML = html;

        // Jika ada preselectedTriggerId
        if (preselectedTriggerId) {
            selTrigger.value = preselectedTriggerId;
            onTriggerSelected(preselectedTriggerId);
        }
    }

    // Otomatis isi RA Pelaksana dari mapping penempatan cabang jika field belum diisi
    if (branchRaMap[selectedKodeUnit] || branchRaMap[normSelected]) {
        const defaultRa = branchRaMap[selectedKodeUnit] || branchRaMap[normSelected];
        const raInput = document.getElementById('inputRaPelaksana');
        if (raInput && !raInput.value) {
            raInput.value = defaultRa;
        }
    }
}

function onTriggerSelected(triggerId) {
    const box = document.getElementById('triggerDetailBox');
    if (!triggerId) {
        box.style.display = 'none';
        return;
    }

    const trigger = allEligibleTriggers.find(t => t.id == triggerId || t.trigger_id == triggerId);
    if (!trigger) {
        box.style.display = 'none';
        return;
    }

    document.getElementById('dispUnit').textContent = trigger.kode_unit + ' — ' + trigger.nama_unit;
    document.getElementById('dispArea').textContent = trigger.area;
    document.getElementById('dispUraian').textContent = trigger.uraian;
    document.getElementById('dispPeriode').textContent = trigger.periode_mulai + ' s.d. ' + trigger.periode_selesai + ' | ' + trigger.objek;
    document.getElementById('badgeRiskLevel').textContent = trigger.risk_level;

    // Update Batas Scope otomatis
    const scopeTpl = `Terbatas pada ${trigger.area} | ${trigger.periode_mulai} s.d. ${trigger.periode_selesai} | ${trigger.objek} | Tidak diperluas tanpa persetujuan reviewer`;
    document.getElementById('inputBatasScope').value = scopeTpl;

    // Default RA Pelaksana dari assigned_ra jika ada
    if (trigger.assigned_ra) {
        document.getElementById('inputRaPelaksana').value = trigger.assigned_ra;
    }

    box.style.display = 'block';
}

// Inisialisasi saat dokumen dimuat
document.addEventListener('DOMContentLoaded', function() {
    const branchSel = document.getElementById('selectBranch');
    const preselectedTrigger = @json(old('trigger_id', $selectedTrigger?->id));
    const preselectedUnit = @json(old('filter_unit', $selectedKodeUnit));

    if (preselectedUnit) {
        branchSel.value = preselectedUnit;
        onBranchChanged(preselectedUnit, preselectedTrigger);
    } else if (branchSel.value) {
        onBranchChanged(branchSel.value, preselectedTrigger);
    }
});
</script>
@endsection
