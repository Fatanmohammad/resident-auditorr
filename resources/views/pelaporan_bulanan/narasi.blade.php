@extends('layouts.app')
@section('title', 'Editor WYSIWYG & Catatan Eksekutif (SOP 08)')

@section('content')
<!-- Quill CSS -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

<style>
    .selector-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.15rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .wysiwyg-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .wysiwyg-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.85rem 1.25rem;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
    .ql-editor {
        min-height: 220px;
        font-family: inherit;
        font-size: 0.92rem;
        line-height: 1.6;
    }
    .ql-toolbar.ql-snow {
        border-top-left-radius: 0;
        border-top-right-radius: 0;
        border-color: #e2e8f0;
        background: #f8fafc;
    }
    .ql-container.ql-snow {
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        border-color: #e2e8f0;
    }
    .preview-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1.25rem;
        min-height: 120px;
        line-height: 1.6;
        font-size: 0.92rem;
    }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Header Modul --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a8a;">
                <i class="bi bi-pencil-square me-2"></i>Editor WYSIWYG &amp; Catatan Eksekutif Laporan Bulanan
            </h4>
            <p class="text-muted small mb-0">
                Penyusunan Catatan Eksekutif Bebas &amp; Rencana Kerja Konsolidasi Menggunakan Rich Text Editor (Bebas Narasi Kaku)
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            @if($kalender)
            <a href="{{ route('pelaporan-bulanan.laporan-bulanan', $kalender->report_id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-eye me-1"></i> Preview Laporan Bulanan
            </a>
            @endif
        </div>
    </div>

    {{-- Tabs Navigasi Modul --}}
    @include('pelaporan_bulanan.tabs')

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Mode Preview Notice for RA --}}
    @if($isRa)
        <div class="alert alert-info border-0 shadow-sm rounded-3 mb-3 d-flex align-items-center">
            <i class="bi bi-info-circle-fill fs-4 me-3 text-primary"></i>
            <div>
                <h6 class="fw-bold mb-0">Mode Preview (Read-Only) Resident Auditor</h6>
                <small class="text-muted">
                    Sesuai SOP 08 revisi, Resident Auditor tidak lagi dibebani pengisian 6 narasi kaku. Catatan dan analisis eksekutif dikelola langsung oleh Admin/Reviewer SKAI melalui Editor WYSIWYG.
                </small>
            </div>
        </div>
    @endif

    {{-- Selector Report ID --}}
    <div class="selector-card">
        <form method="GET" action="{{ route('pelaporan-bulanan.narasi') }}" class="row g-2 align-items-center">
            <div class="col-md-7">
                <label class="form-label small fw-bold text-muted mb-1">Pilih Laporan Bulanan (Report ID)</label>
                <select name="reportId" class="form-select form-select-sm fw-bold" onchange="if(this.value) { window.location.href = '{{ url('/pelaporan-bulanan/narasi') }}/' + this.value; } else { this.form.submit(); }">
                    @forelse($availableReports as $rep)
                        <option value="{{ $rep->report_id }}" {{ ($reportId == $rep->report_id) ? 'selected' : '' }}>
                            {{ $rep->report_id }} - [{{ $rep->kode_unit }}] {{ $rep->nama_unit }} ({{ \Carbon\Carbon::parse($rep->periode)->translatedFormat('F Y') }})
                        </option>
                    @empty
                        <option value="">-- Belum ada laporan terinisiasi --</option>
                    @endforelse
                </select>
            </div>
            <div class="col-md-5 text-md-end mt-2 mt-md-0">
                @if($kalender)
                    <span class="badge bg-light text-dark border px-2 py-1">Cabang: {{ $kalender->nama_unit }} ({{ $kalender->kode_unit }})</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 ms-1">RA: {{ $kalender->resident_auditor }}</span>
                @endif
            </div>
        </form>
    </div>

    @if(!$kalender || !$narasi)
        <div class="alert alert-warning text-center py-4">
            <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
            Silakan inisiasi kalender laporan terlebih dahulu pada menu <a href="{{ route('pelaporan-bulanan.kalender') }}" class="fw-bold">Kalender Laporan</a>.
        </div>
    @else
        @if(!$isRa)
            {{-- ADMIN WYSIWYG FORM --}}
            <form id="wysiwygForm" method="POST" action="{{ route('pelaporan-bulanan.wysiwyg.update', $kalender->report_id) }}">
                @csrf

                {{-- Hidden Inputs for Editor Content --}}
                <input type="hidden" name="catatan_eksekutif" id="catatanEksekutifInput">
                <input type="hidden" name="rencana_bulan_depan" id="rencanaBulanDepanInput">

                <div class="row g-3">
                    {{-- 1. Catatan / Analisis Eksekutif Bebas Admin (WYSIWYG) --}}
                    <div class="col-12">
                        <div class="wysiwyg-card">
                            <div class="wysiwyg-header d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-dark"><i class="bi bi-file-earmark-richtext me-1 text-primary"></i> 1. Catatan &amp; Analisis Eksekutif Bebas (Admin WYSIWYG)</span>
                                    <div class="small text-muted" style="font-size: 0.75rem;">
                                        Bebas memformat heading, bold/italic, bullet/numbered list, kutipan, dan tabel ringkas.
                                    </div>
                                </div>
                                <span class="badge bg-primary">Rich HTML Editor</span>
                            </div>
                            <div class="p-3">
                                <div id="editorCatatan" contenteditable="true" class="form-control border-1 p-3" style="min-height: 220px; overflow-y: auto;">
                                    {!! $narasi->catatan_eksekutif ?: $narasi->ringkasan_eksekutif ?: '' !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Rencana Bulan Berikutnya (WYSIWYG Admin) --}}
                    <div class="col-12">
                        <div class="wysiwyg-card">
                            <div class="wysiwyg-header d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-dark"><i class="bi bi-calendar-check me-1 text-primary"></i> 2. Rencana Kerja Pengawasan Bulan Berikutnya (Admin WYSIWYG)</span>
                                    <div class="small text-muted" style="font-size: 0.75rem;">
                                        Fokus area pemeriksaan, tindak lanjut temuan kritis, dan target pengawasan konsolidasi.
                                    </div>
                                </div>
                                <span class="badge bg-secondary">Rich HTML Editor</span>
                            </div>
                            <div class="p-3">
                                <div id="editorRencana" contenteditable="true" class="form-control border-1 p-3" style="min-height: 220px; overflow-y: auto;">
                                    {!! $narasi->rencana_bulan_depan ?: $narasi->rencana_bulan_berikut ?: '' !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
                    <span class="small text-muted">
                        Report ID: <strong>{{ $kalender->report_id }}</strong> | Terakhir disimpan: {{ $narasi->updated_at ? $narasi->updated_at->diffForHumans() : '-' }}
                    </span>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan Catatan Eksekutif (WYSIWYG)
                    </button>
                </div>
            </form>
        @else
            {{-- RESIDENT AUDITOR READ-ONLY PREVIEW --}}
            <div class="row g-3">
                <div class="col-12">
                    <div class="wysiwyg-card">
                        <div class="wysiwyg-header">
                            <span class="fw-bold text-dark"><i class="bi bi-file-earmark-richtext me-1 text-primary"></i> 1. Catatan &amp; Analisis Eksekutif Laporan Bulanan</span>
                        </div>
                        <div class="p-3">
                            <div class="preview-box">
                                @if(!empty($narasi->catatan_eksekutif))
                                    {!! $narasi->catatan_eksekutif !!}
                                @elseif(!empty($narasi->ringkasan_eksekutif))
                                    {!! nl2br(e($narasi->ringkasan_eksekutif)) !!}
                                @else
                                    <span class="text-muted fst-italic">Belum ada catatan eksekutif yang ditambahkan oleh Admin/SKAI.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="wysiwyg-card">
                        <div class="wysiwyg-header">
                            <span class="fw-bold text-dark"><i class="bi bi-calendar-check me-1 text-primary"></i> 2. Rencana Kerja Pengawasan Bulan Berikutnya</span>
                        </div>
                        <div class="p-3">
                            <div class="preview-box">
                                @if(!empty($narasi->rencana_bulan_depan))
                                    {!! $narasi->rencana_bulan_depan !!}
                                @elseif(!empty($narasi->rencana_bulan_berikut))
                                    {!! nl2br(e($narasi->rencana_bulan_berikut)) !!}
                                @else
                                    <span class="text-muted fst-italic">Melanjutkan pengawasan rutin harian, offsite review, dan monitoring tindak lanjut SKAI.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

@if(!$isRa)
<!-- Quill JS and Initialization -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toolbarOptions = [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'color': [] }, { 'background': [] }],
            ['blockquote', 'code-block'],
            ['clean']
        ];

        let quillCatatan = null;
        let quillRencana = null;

        if (typeof Quill !== 'undefined') {
            quillCatatan = new Quill('#editorCatatan', {
                theme: 'snow',
                modules: { toolbar: toolbarOptions }
            });

            quillRencana = new Quill('#editorRencana', {
                theme: 'snow',
                modules: { toolbar: toolbarOptions }
            });
        }

        const form = document.getElementById('wysiwygForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                const inputCatatan = document.getElementById('catatanEksekutifInput');
                const inputRencana = document.getElementById('rencanaBulanDepanInput');

                if (quillCatatan) {
                    inputCatatan.value = quillCatatan.root.innerHTML;
                } else {
                    const el = document.getElementById('editorCatatan');
                    inputCatatan.value = el ? el.innerHTML : '';
                }

                if (quillRencana) {
                    inputRencana.value = quillRencana.root.innerHTML;
                } else {
                    const el = document.getElementById('editorRencana');
                    inputRencana.value = el ? el.innerHTML : '';
                }
            });
        }
    });
</script>
@endif
@endsection
