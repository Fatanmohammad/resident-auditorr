@extends('layouts.app')

@section('content')

@php
    $hasilColor = fn($h) => match($h) {
        'Sesuai'       => ['bg' => '#f0fdf4', 'color' => '#16a34a'],
        'Tidak Sesuai' => ['bg' => '#fef2f2', 'color' => '#dc2626'],
        'N/A'          => ['bg' => '#f8fafc', 'color' => '#94a3b8'],
        default        => ['bg' => '#fffbeb', 'color' => '#ca8a04'],
    };
    $riskColor = fn($r) => match($r) {
        'Critical' => '#7c3aed',
        'High'     => '#dc2626',
        'Moderate' => '#ea580c',
        'Low'      => '#16a34a',
        default    => '#94a3b8',
    };
    $totalItem    = $itemsGedung->count() + $itemsAtm->flatten()->count();
    $totalSesuai  = $itemsGedung->where('hasil_observasi','Sesuai')->count()  + $itemsAtm->flatten()->where('hasil_observasi','Sesuai')->count();
    $totalTidak   = $itemsGedung->where('hasil_observasi','Tidak Sesuai')->count() + $itemsAtm->flatten()->where('hasil_observasi','Tidak Sesuai')->count();
    $totalBelum   = $totalItem - $totalSesuai - $totalTidak - $itemsGedung->where('hasil_observasi','N/A')->count() - $itemsAtm->flatten()->where('hasil_observasi','N/A')->count();
@endphp

{{-- Header --}}
<div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <a href="{{ route('onsite.observasi.create', $visit) }}" style="font-size: 0.85rem; color: #64748b; text-decoration: none;">
            <i class="bi bi-arrow-left me-1"></i> Setup Observasi
        </a>
        <h4 style="font-weight: 700; color: #1e293b; margin: 0.5rem 0 0.2rem;">
            KKA Observasi Lingkungan — {{ $visit->kode_unit }}
        </h4>
        <p style="color: #64748b; font-size: 0.85rem; margin: 0;">
            {{ $visit->nama_unit }} &nbsp;|&nbsp;
            {{ \Carbon\Carbon::parse($observasi->tanggal_observasi)->format('d M Y') }} &nbsp;|&nbsp;
            RA: {{ $observasi->ra_pelaksana }}
        </p>
    </div>
    {{-- Ringkasan --}}
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        @foreach([['label'=>'Total','val'=>$totalItem,'bg'=>'#f8fafc','c'=>'#475569'],['label'=>'Sesuai','val'=>$totalSesuai,'bg'=>'#f0fdf4','c'=>'#16a34a'],['label'=>'Tidak Sesuai','val'=>$totalTidak,'bg'=>'#fef2f2','c'=>'#dc2626'],['label'=>'Belum Diisi','val'=>$totalBelum,'bg'=>'#fffbeb','c'=>'#ca8a04']] as $s)
        <div style="background:{{ $s['bg'] }};border:1px solid {{ $s['c'] }}30;border-radius:8px;padding:0.4rem 0.8rem;text-align:center;min-width:70px;">
            <div style="font-size:0.7rem;color:{{ $s['c'] }};font-weight:600;">{{ $s['label'] }}</div>
            <div style="font-size:1.2rem;font-weight:700;color:{{ $s['c'] }};">{{ $s['val'] }}</div>
        </div>
        @endforeach
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="font-size: 0.88rem;"><i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}</div>
@endif

{{-- ===== BAGIAN GEDUNG ===== --}}
<div style="font-weight: 700; color: #1e3a8a; font-size: 0.9rem; margin-bottom: 0.75rem; padding: 0.5rem 0.75rem; background: #eff6ff; border-radius: 6px; border-left: 3px solid #1e3a8a;">
    <i class="bi bi-building me-2"></i> Observasi Gedung & Lingkungan ({{ $itemsGedung->count() }} item)
</div>

<div class="card" style="border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 0;">
        <table class="table table-hover" style="margin: 0; font-size: 0.82rem;">
            <thead style="background: #f8fafc;">
                <tr>
                    <th style="padding: 0.7rem 1rem; color: #64748b; font-weight: 600; width: 40px;">No</th>
                    <th style="padding: 0.7rem 1rem; color: #64748b; font-weight: 600; width: 110px;">Area</th>
                    <th style="padding: 0.7rem 1rem; color: #64748b; font-weight: 600;">Objek / Kriteria</th>
                    <th style="padding: 0.7rem 1rem; color: #64748b; font-weight: 600; text-align: center; width: 110px;">Hasil</th>
                    <th style="padding: 0.7rem 1rem; color: #64748b; font-weight: 600; text-align: center; width: 80px;">Risk</th>
                    <th style="padding: 0.7rem 1rem; width: 50px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($itemsGedung as $i => $item)
                @php $hc = $hasilColor($item->hasil_observasi); @endphp
                <tr>
                    <td style="padding: 0.6rem 1rem; color: #94a3b8; vertical-align: middle;">{{ $i + 1 }}</td>
                    <td style="padding: 0.6rem 1rem; vertical-align: middle;">
                        <span style="font-size: 0.75rem; background: #f1f5f9; color: #475569; padding: 0.15rem 0.5rem; border-radius: 4px;">{{ $item->area }}</span>
                    </td>
                    <td style="padding: 0.6rem 1rem; vertical-align: middle;">
                        <div style="font-weight: 500; color: #1e293b;">{{ $item->objek }}</div>
                        <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.15rem;">{{ Str::limit($item->kriteria, 80) }}</div>
                        @if($item->kondisi_aktual)
                            <div style="font-size: 0.75rem; color: #475569; margin-top: 0.2rem; font-style: italic;">
                                "{{ Str::limit($item->kondisi_aktual, 60) }}"
                            </div>
                        @endif
                    </td>
                    <td style="padding: 0.6rem 1rem; vertical-align: middle; text-align: center;">
                        @if($item->hasil_observasi)
                            <span style="background: {{ $hc['bg'] }}; color: {{ $hc['color'] }}; padding: 0.2rem 0.5rem; border-radius: 20px; font-size: 0.72rem; font-weight: 600; white-space: nowrap;">
                                {{ $item->hasil_observasi }}
                            </span>
                        @else
                            <span style="color: #cbd5e1; font-size: 0.78rem;">Belum diisi</span>
                        @endif
                    </td>
                    <td style="padding: 0.6rem 1rem; vertical-align: middle; text-align: center;">
                        @if($item->risk_level)
                            <span style="color: {{ $riskColor($item->risk_level) }}; font-weight: 700; font-size: 0.78rem;">{{ $item->risk_level }}</span>
                        @else
                            <span style="color: #e2e8f0;">—</span>
                        @endif
                    </td>
                    <td style="padding: 0.6rem 1rem; vertical-align: middle; text-align: right;">
                        @if($item->status_review === 'Approved')
                            <button class="btn btn-sm btn-outline-secondary" style="font-size: 0.72rem; opacity: 0.4;" disabled title="Sudah Approved">
                                <i class="bi bi-lock"></i>
                            </button>
                        @else
                            <button type="button" class="btn btn-sm btn-outline-secondary" style="font-size: 0.72rem;"
                                data-bs-toggle="modal" data-bs-target="#modal-item-{{ $item->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                        @endif
                        @if(in_array(auth()->user()->role, ['admin','kabag_ra','kadiv_skai']))
                            <button type="button" class="btn btn-sm btn-outline-primary" style="font-size: 0.72rem; margin-left: 0.2rem;"
                                data-bs-toggle="modal" data-bs-target="#modal-review-{{ $item->id }}">
                                <i class="bi bi-shield-check"></i>
                            </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ===== BAGIAN ATM ===== --}}
@if($itemsAtm->count())
    @foreach($itemsAtm as $slot => $atmItems)
    <div style="font-weight: 700; color: #7c3aed; font-size: 0.9rem; margin-bottom: 0.75rem; padding: 0.5rem 0.75rem; background: #faf5ff; border-radius: 6px; border-left: 3px solid #7c3aed;">
        <i class="bi bi-credit-card-2-front me-2"></i> {{ $slot }} ({{ $atmItems->count() }} item)
    </div>
    <div class="card" style="border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 1.25rem;">
        <div class="card-body" style="padding: 0;">
            <table class="table table-hover" style="margin: 0; font-size: 0.82rem;">
                <tbody>
                    @foreach($atmItems as $item)
                    @php $hc = $hasilColor($item->hasil_observasi); @endphp
                    <tr>
                        <td style="padding: 0.6rem 1rem; vertical-align: middle; width: 200px;">
                            <div style="font-weight: 500; color: #1e293b;">{{ Str::after($item->objek, '| ') }}</div>
                            @if($item->kondisi_aktual)
                                <div style="font-size: 0.75rem; color: #475569; font-style: italic;">"{{ Str::limit($item->kondisi_aktual, 50) }}"</div>
                            @endif
                        </td>
                        <td style="padding: 0.6rem 1rem; vertical-align: middle; font-size: 0.75rem; color: #94a3b8;">
                            {{ Str::limit($item->kriteria, 80) }}
                        </td>
                        <td style="padding: 0.6rem 1rem; vertical-align: middle; text-align: center; width: 110px;">
                            @if($item->hasil_observasi)
                                <span style="background: {{ $hc['bg'] }}; color: {{ $hc['color'] }}; padding: 0.2rem 0.5rem; border-radius: 20px; font-size: 0.72rem; font-weight: 600;">{{ $item->hasil_observasi }}</span>
                            @else
                                <span style="color: #cbd5e1; font-size: 0.78rem;">Belum diisi</span>
                            @endif
                        </td>
                        <td style="padding: 0.6rem 1rem; vertical-align: middle; text-align: center; width: 80px;">
                            @if($item->risk_level)
                                <span style="color: {{ $riskColor($item->risk_level) }}; font-weight: 700; font-size: 0.78rem;">{{ $item->risk_level }}</span>
                            @endif
                        </td>
                        <td style="padding: 0.6rem 1rem; vertical-align: middle; text-align: right; width: 80px;">
                            @if($item->status_review === 'Approved')
                                <button class="btn btn-sm btn-outline-secondary" style="font-size: 0.72rem; opacity: 0.4;" disabled><i class="bi bi-lock"></i></button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary" style="font-size: 0.72rem;"
                                    data-bs-toggle="modal" data-bs-target="#modal-item-{{ $item->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            @endif
                            @if(in_array(auth()->user()->role, ['admin','kabag_ra','kadiv_skai']))
                                <button type="button" class="btn btn-sm btn-outline-primary" style="font-size: 0.72rem; margin-left: 0.2rem;"
                                    data-bs-toggle="modal" data-bs-target="#modal-review-{{ $item->id }}">
                                    <i class="bi bi-shield-check"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
@endif

{{-- ===== MODALS ===== --}}
@foreach($itemsGedung->merge($itemsAtm->flatten()) as $item)

{{-- Modal Input RA --}}
<div class="modal fade" id="modal-item-{{ $item->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" style="font-weight: 700; font-size: 0.9rem;">
                    {{ $item->objek }}
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('onsite.observasi.item.update', $item) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-body" style="font-size: 0.88rem;">

                    {{-- Kriteria --}}
                    <div style="background: #f8fafc; border-radius: 6px; padding: 0.75rem; margin-bottom: 1rem; font-size: 0.8rem; color: #475569;">
                        <strong>Kriteria:</strong> {{ $item->kriteria }}
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">
                            Kondisi Aktual / Hasil Pengamatan
                        </label>
                        <textarea name="kondisi_aktual" rows="2" class="form-control form-control-sm"
                            placeholder="Deskripsikan kondisi yang ditemukan...">{{ $item->kondisi_aktual }}</textarea>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">
                            Hasil Observasi <span style="color: #dc2626;">*</span>
                        </label>
                        <div style="display: flex; gap: 0.75rem;">
                            @foreach(['Sesuai', 'Tidak Sesuai', 'N/A'] as $opt)
                            <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; font-size: 0.85rem;"
                                class="hasil-option" data-val="{{ $opt }}">
                                <input type="radio" name="hasil_observasi" value="{{ $opt }}"
                                    {{ $item->hasil_observasi === $opt ? 'checked' : '' }}
                                    style="accent-color: #1e3a8a;">
                                {{ $opt }}
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Kolom kondisional — hanya muncul jika Tidak Sesuai --}}
                    <div id="section-tidak-sesuai-{{ $item->id }}"
                        style="{{ $item->hasil_observasi === 'Tidak Sesuai' ? '' : 'display:none;' }}">

                        <div style="margin-bottom: 1rem;">
                            <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">Uraian Ketidaksesuaian</label>
                            <textarea name="uraian_ketidaksesuaian" rows="2" class="form-control form-control-sm">{{ $item->uraian_ketidaksesuaian }}</textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">Penyebab</label>
                                <textarea name="penyebab" rows="2" class="form-control form-control-sm">{{ $item->penyebab }}</textarea>
                            </div>
                            <div>
                                <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">Dampak</label>
                                <textarea name="dampak" rows="2" class="form-control form-control-sm">{{ $item->dampak }}</textarea>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">
                                    Impact (1-5)
                                    <span style="font-size: 0.72rem; color: #94a3b8; font-weight: 400;">— Risk dihitung otomatis</span>
                                </label>
                                <input type="number" name="impact" min="1" max="5" class="form-control form-control-sm" value="{{ $item->impact }}">
                            </div>
                            <div>
                                <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">Likelihood (1-5)</label>
                                <input type="number" name="likelihood" min="1" max="5" class="form-control form-control-sm" value="{{ $item->likelihood }}">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">Referensi Bukti / Foto</label>
                        <input type="text" name="referensi_bukti" class="form-control form-control-sm"
                            placeholder="Nomor foto, nama file, atau keterangan bukti..."
                            value="{{ $item->referensi_bukti }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary" style="background: #1e3a8a; border-color: #1e3a8a;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Review Admin --}}
@if(in_array(auth()->user()->role, ['admin','kabag_ra','kadiv_skai']))
<div class="modal fade" id="modal-review-{{ $item->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #eff6ff;">
                <h6 class="modal-title" style="font-weight: 700; color: #1e3a8a; font-size: 0.9rem;">
                    <i class="bi bi-shield-check me-2"></i>Review — {{ Str::limit($item->objek, 40) }}
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('onsite.observasi.item.review', $item) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-body" style="font-size: 0.88rem;">
                    <div style="background: #f8fafc; border-radius: 6px; padding: 0.75rem; margin-bottom: 1rem; font-size: 0.82rem; color: #475569;">
                        <div><strong>Hasil RA:</strong> {{ $item->hasil_observasi ?? '—' }}</div>
                        @if($item->risk_level)
                        <div><strong>Risk:</strong> {{ $item->risk_level }} ({{ $item->risk_score }})</div>
                        @endif
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">Status Review</label>
                        <select name="status_review" class="form-select form-select-sm" required>
                            @foreach(['Belum Direview','Revisi','Approved'] as $sr)
                                <option value="{{ $sr }}" {{ $item->status_review === $sr ? 'selected' : '' }}>{{ $sr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">Catatan Reviewer</label>
                        <textarea name="catatan_reviewer" rows="3" class="form-control form-control-sm">{{ $item->catatan_reviewer }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary" style="background: #1e3a8a; border-color: #1e3a8a;">
                        <i class="bi bi-shield-check me-1"></i> Simpan Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endforeach

@push('scripts')
<script>
// Toggle section Tidak Sesuai secara kondisional
document.querySelectorAll('input[name="hasil_observasi"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        var modalId = this.closest('.modal').id;
        var itemId  = modalId.replace('modal-item-', '');
        var section = document.getElementById('section-tidak-sesuai-' + itemId);
        if (section) {
            section.style.display = this.value === 'Tidak Sesuai' ? '' : 'none';
        }
    });
});
</script>
@endpush

@endsection
