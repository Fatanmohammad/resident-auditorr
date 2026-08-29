@extends('layouts.app')

@push('styles')
<style>
    /* Styling Khusus untuk Card Grid KKA */
    .kka-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .kka-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.1rem;
        transition: all 0.25s ease-in-out;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-decoration: none !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .kka-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
        border-color: #cbd5e1;
    }

    .kka-card.has-data {
        border-left: 4px solid #0284c7;
    }

    .kka-card.has-data:hover {
        border-left-color: #0369a1;
    }

    .kka-card.no-data {
        border-left: 4px solid #cbd5e1;
        background-color: #f8fafc;
        opacity: 0.85;
    }

    .kka-icon-wrapper {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 0.75rem;
    }

    .has-data .kka-icon-wrapper {
        background-color: #e0f2fe;
        color: #0284c7;
    }

    .no-data .kka-icon-wrapper {
        background-color: #f1f5f9;
        color: #94a3b8;
    }

    .kka-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--bs-blue-dark, #0f172a);
        margin-bottom: 0.25rem;
    }

    .kka-meta {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px dashed #e2e8f0;
    }

    .kka-count-number {
        font-size: 1.4rem;
        font-weight: 800;
        line-height: 1;
        font-family: monospace;
    }

    .has-data .kka-count-number {
        color: #0284c7;
    }

    .no-data .kka-count-number {
        color: #94a3b8;
    }

    .kka-action-text {
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .has-data .kka-action-text {
        color: #0284c7;
    }

    .no-data .kka-action-text {
        color: #64748b;
    }

    .badge-status-pill {
        font-size: 0.68rem;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .pill-active {
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .pill-empty {
        background-color: #f1f5f9;
        color: #64748b;
    }
</style>
@endpush

@section('content')
<div>
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('admin-offsite.cabang-detail', $unit->cabang_id) }}?tahun={{ $tahun }}&bulan={{ $bulan }}" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; background: #fff;">
            <i class="bi bi-arrow-left"></i> Kembali ke Detail Cabang
        </a>
    </div>

    <div class="page-header">
        <div class="page-header-title">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-gray" style="font-family: monospace;">{{ $unit->unit_code }}</span>
                <span class="badge badge-info">{{ $unit->unit_type }}</span>
                <span class="badge badge-gray">Periode: {{ \Carbon\Carbon::create($tahun, $bulan)->isoFormat('MMMM Y') }}</span>
            </div>
            <h1 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--bs-blue-dark);">{{ $unit->unit_name }}</h1>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">Rincian Lembar Kerja Audit dan Register Harian Offsite Review</p>
        </div>
    </div>

    @if (!$wp)
        <div class="empty-state">
            <i class="bi bi-folder-x" style="font-size: 3rem;"></i>
            <h5 style="margin-top: 1rem; color: var(--bs-blue-dark);">Belum Ada Data Offsite Review</h5>
            <p>Belum ada data Work Paper (WP) Offsite Review untuk unit ini pada periode {{ \Carbon\Carbon::create($tahun, $bulan)->isoFormat('MMMM Y') }}.</p>
        </div>
    @else
        <!-- Identitas WP Card -->
        <div class="widget-card" style="margin-bottom: 1.5rem;">
            <div class="widget-header">
                <div class="widget-title">
                    <i class="bi bi-file-earmark-text"></i> Identitas Work Paper (WP) Audit
                </div>
            </div>
            <div class="widget-body">
                <div class="grid grid-cols-4">
                    <div style="margin-bottom: 1rem;">
                        <div class="stat-label">KODE WP</div>
                        <div style="font-family: monospace; font-weight: 600; font-size: 1.1rem; color: var(--bs-blue-dark);">{{ $wp->kode_wp }}</div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <div class="stat-label">JENIS UNIT</div>
                        <div style="font-weight: 600; font-size: 1rem; color: var(--text-main);">{{ $wp->jenis_unit }}</div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <div class="stat-label">KANTOR INDUK</div>
                        <div style="font-weight: 600; font-size: 1rem; color: var(--text-main);">{{ $wp->kantor_induk }}</div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <div class="stat-label">STATUS WP</div>
                        <div>
                            <span class="badge {{ $wp->status_wp === 'Final' ? 'badge-success' : ($wp->status_wp === 'Aktif' ? 'badge-warning' : 'badge-gray') }}">
                                {{ $wp->status_wp }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="stat-label">PERIODE PELAKSANAAN</div>
                        <div style="font-weight: 600; font-size: 1rem; color: var(--text-main);">
                            {{ $wp->periode_mulai->format('d/m/Y') }} s.d. {{ $wp->periode_selesai->format('d/m/Y') }}
                        </div>
                    </div>
                    <div>
                        <div class="stat-label">REVIEWER (BAGIAN RA)</div>
                        <div style="font-weight: 600; font-size: 1rem; color: var(--text-main);">{{ $wp->reviewerBagianRa->name ?? '-' }}</div>
                    </div>
                    <div style="grid-column: span 2;">
                        <div class="stat-label">VALIDASI UNIT</div>
                        <div style="font-weight: 600; font-size: 1rem; color: var(--text-main);">{{ $wp->validasi_unit ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-3" style="margin-bottom: 1.5rem;">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-people"></i></div>
                <div class="stat-info">
                    <div class="stat-label">POPULASI</div>
                    <div class="stat-value">{{ number_format($ringkasan['populasi']) }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="bi bi-search"></i></div>
                <div class="stat-info">
                    <div class="stat-label">SAMPEL LOW</div>
                    <div class="stat-value">{{ number_format($ringkasan['sampel_low']) }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-file-check"></i></div>
                <div class="stat-info">
                    <div class="stat-label">KKA FINAL</div>
                    <div class="stat-value">{{ number_format($ringkasan['kka_final']) }}</div>
                </div>
            </div>
            <div class="stat-card" style="{{ $ringkasan['exception'] > 0 ? 'border-color: #fca5a5; background-color: #fef2f2;' : '' }}">
                <div class="stat-icon {{ $ringkasan['exception'] > 0 ? 'red' : 'gray' }}"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-info">
                    <div class="stat-label" style="{{ $ringkasan['exception'] > 0 ? 'color: #dc2626;' : '' }}">EXCEPTION</div>
                    <div class="stat-value" style="{{ $ringkasan['exception'] > 0 ? 'color: #b91c1c;' : '' }}">{{ number_format($ringkasan['exception']) }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="bi bi-chat-dots"></i></div>
                <div class="stat-info">
                    <div class="stat-label">KLARIFIKASI</div>
                    <div class="stat-value">{{ number_format($ringkasan['klarifikasi']) }}</div>
                </div>
            </div>
            <div class="stat-card" style="{{ $ringkasan['eskalasi'] > 0 ? 'border-color: #fca5a5; background-color: #fef2f2;' : '' }}">
                <div class="stat-icon {{ $ringkasan['eskalasi'] > 0 ? 'red' : 'gray' }}"><i class="bi bi-arrow-up-right-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-label" style="{{ $ringkasan['eskalasi'] > 0 ? 'color: #dc2626;' : '' }}">ESKALASI</div>
                    <div class="stat-value" style="{{ $ringkasan['eskalasi'] > 0 ? 'color: #b91c1c;' : '' }}">{{ number_format($ringkasan['eskalasi']) }}</div>
                </div>
            </div>
        </div>

        <!-- 7 Kartu KKA Modul -->
        <div class="widget-card" style="margin-bottom: 1.5rem;">
            <div class="widget-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="widget-title">
                    <i class="bi bi-journal-bookmark-fill"></i> Kertas Kerja Audit (KKA) Per Area
                </div>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Pilih area untuk membuka modul KKA</span>
            </div>
            <div class="widget-body">
                @php
                    $stagingData = $wp->stagingOffsite;
                    $kkaAreas = [
                        'teller-kas'     => ['label' => 'Teller / Kas',       'icon' => 'bi-cash-coin',             'area' => 'Teller/Kas'],
                        'kredit'         => ['label' => 'Kredit',             'icon' => 'bi-credit-card-2-front',   'area' => 'Kredit'],
                        'biaya-beban'    => ['label' => 'Biaya / Beban',      'icon' => 'bi-receipt-cutoff',        'area' => 'Biaya/Beban'],
                        'biaya-internal' => ['label' => 'Biaya / Internal',   'icon' => 'bi-building-gear',         'area' => 'Biaya/Internal'],
                        'pengaduan'      => ['label' => 'Pengaduan Customer', 'icon' => 'bi-chat-square-quote',    'area' => 'Pengaduan'],
                        'transaksi-umum' => ['label' => 'Transaksi Umum',    'icon' => 'bi-arrow-left-right',      'area' => 'Transaksi Umum'],
                        'transfer-ku'     => ['label' => 'Transfer / KU',     'icon' => 'bi-bank',                  'area' => 'Transfer/KU'],
                    ];
                @endphp

                <div class="kka-grid">
                    @foreach($kkaAreas as $slug => $config)
                        @php
                            $count = $stagingData->where('area_review', $config['area'])->count();
                            $hasData = $count > 0;
                        @endphp
                        
                        <a href="{{ route('admin-offsite.kka-index', [$wp->id, $slug]) }}" 
                           class="kka-card {{ $hasData ? 'has-data' : 'no-data' }}">
                            
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div class="kka-icon-wrapper">
                                        <i class="bi {{ $config['icon'] }}"></i>
                                    </div>
                                    <span class="badge-status-pill {{ $hasData ? 'pill-active' : 'pill-empty' }}">
                                        {{ $hasData ? 'Ada Data' : 'Kosong' }}
                                    </span>
                                </div>
                                <div class="kka-title">{{ $config['label'] }}</div>
                            </div>

                            <div class="kka-meta">
                                <div>
                                    <div style="font-size: 0.68rem; text-transform: uppercase; font-weight: 700; color: #94a3b8;">Total Rekord</div>
                                    <div class="kka-count-number">{{ number_format($count) }}</div>
                                </div>
                                <div class="kka-action-text">
                                    <span>Buka KKA</span>
                                    <i class="bi bi-chevron-right"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Tabel Register Harian -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Register Harian Review Offsite</div>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="accordion accordion-flush" id="registerAccordion">
                    @forelse($rows as $tanggal => $areaRows)
                        @php $adaException = $areaRows->where('exception_awal', 1)->count() > 0; @endphp
                        <div class="accordion-item" style="border-bottom: 1px solid var(--border-color);">
                            <h2 class="accordion-header" style="margin: 0;">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ \Illuminate\Support\Str::slug($tanggal) }}" style="padding: 1rem 1.25rem; font-weight: 600; color: var(--bs-blue-dark); background-color: #f8fafc; border: none; width: 100%; text-align: left; cursor: pointer;">
                                    <i class="bi bi-calendar-event me-2 text-muted"></i>
                                    {{ \Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM Y') }}
                                    @if($adaException)
                                        <span class="badge badge-warning ms-3">Ada Exception ({{ $areaRows->where('exception_awal', 1)->count() }})</span>
                                    @endif
                                </button>
                            </h2>
                            <div id="collapse-{{ \Illuminate\Support\Str::slug($tanggal) }}" class="accordion-collapse collapse" data-bs-parent="#registerAccordion">
                                <div class="accordion-body" style="padding: 0;">
                                    <div class="table-wrapper">
                                        <table class="data-table">
                                            <thead>
                                                <tr>
                                                    <th>AREA REVIEW</th>
                                                    <th style="text-align: right;">POPULASI</th>
                                                    <th style="text-align: right;">SAMPEL LOW</th>
                                                    <th style="text-align: right;">KKA FINAL</th>
                                                    <th style="text-align: right;">EXCEPTION</th>
                                                    <th style="text-align: center;">RISIKO TERTINGGI</th>
                                                    <th>DETAIL TRANSAKSI (URAIAAN & REK)</th>
                                                    <th style="text-align: center;">STATUS REVIEW</th>
                                                    <th>CATATAN REVIEWER</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($areaRows as $row)
                                                <tr>
                                                    <td style="font-weight: 600;">{{ $row->area_review ?? '-' }}</td>
                                                    <td style="text-align: right; font-family: monospace; font-size: 0.9rem;">1</td>
                                                    <td style="text-align: right; font-family: monospace; font-size: 0.9rem;">{{ $row->sampel_low ? '1' : '0' }}</td>
                                                    <td style="text-align: right; font-family: monospace; font-size: 0.9rem;">{{ $row->masuk_kka_final ? '1' : '0' }}</td>
                                                    <td style="text-align: right; font-family: monospace; font-size: 0.9rem; {{ $row->exception_awal ? 'color: #dc2626; font-weight: bold;' : '' }}">
                                                        {{ $row->exception_awal ? '1' : '0' }}
                                                    </td>
                                                    <td style="text-align: center;">
                                                        @if(($row->risk_level ?? $row->risk_awal) === 'High')
                                                            <span class="badge badge-danger">High</span>
                                                        @elseif(in_array(($row->risk_level ?? $row->risk_awal), ['Moderate', 'Moderate to High']))
                                                            <span class="badge badge-warning">{{ $row->risk_level ?? $row->risk_awal }}</span>
                                                        @elseif(in_array(($row->risk_level ?? $row->risk_awal), ['Low', 'Low to Moderate']))
                                                            <span class="badge badge-success">{{ $row->risk_level ?? $row->risk_awal }}</span>
                                                        @else
                                                            <span class="text-muted" style="font-size: 0.75rem;">{{ $row->risk_level ?? $row->risk_awal ?? '-' }}</span>
                                                        @endif
                                                    </td>

                                                    {{-- PARSING RAW JSON SECARA FLEKSIBEL SESUAI STAGING OFFSITE --}}
                                                    <td>
                                                        @php
                                                            // Ambil isi raw data dari berbagai alternatif nama atribut Staging
                                                            $rawString = $row->detail_transaksi ?? $row->raw_data ?? $row->deskripsi_narasi ?? $row->deskripsi;
                                                            
                                                            $dataArray = is_string($rawString) ? json_decode($rawString, true) : (is_array($rawString) ? $rawString : null);
                                                            
                                                            // Ekstraksi Uraian
                                                            $uraian = $dataArray['URAIAN'] 
                                                                ?? $dataArray['uraian'] 
                                                                ?? $dataArray['NAMA_TRANSAKSI'] 
                                                                ?? $dataArray['nama_transaksi'] 
                                                                ?? $dataArray['DESKRIPSI'] 
                                                                ?? $dataArray['deskripsi'] 
                                                                ?? $row->deskripsi_narasi 
                                                                ?? 'Detail Transaksi';

                                                            // Ekstraksi No Rekening
                                                            $noRek = $dataArray['NO_REK'] 
                                                                ?? $dataArray['no_rek'] 
                                                                ?? $dataArray['NO_REKENING'] 
                                                                ?? $dataArray['no_rekening'] 
                                                                ?? $dataArray['REKENING'] 
                                                                ?? '-';
                                                        @endphp

                                                        @if(!empty($dataArray) && is_array($dataArray))
                                                            <div style="font-weight: 600; color: #0f172a; font-size: 0.85rem;">
                                                                {{ $uraian }}
                                                            </div>
                                                            <div style="font-size: 0.75rem; color: #64748b; font-family: monospace;">
                                                                No. Rek: {{ $noRek }}
                                                            </div>
                                                            
                                                            <!-- Modal Trigger -->
                                                            <a href="#" data-bs-toggle="modal" data-bs-target="#modalRaw-{{ $row->id }}" style="font-size: 0.7rem; color: #0284c7; text-decoration: underline; margin-top: 2px; display: inline-block;">
                                                                <i class="bi bi-code-slash"></i> Lihat Raw Data
                                                            </a>

                                                            <!-- Modal Popup JSON Raw Data -->
                                                            <div class="modal fade" id="modalRaw-{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered">
                                                                    <div class="modal-content" style="text-align: left;">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" style="font-size: 0.9rem; font-weight: 700;">Detail Raw Data CSV Dump</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <pre style="background: #f8fafc; padding: 1rem; border-radius: 8px; font-size: 0.75rem; border: 1px solid #e2e8f0; max-height: 300px; overflow-y: auto; color: #0f172a;">{{ json_encode($dataArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <span style="color: #475569; font-size: 0.85rem;">{{ $rawString ?? '-' }}</span>
                                                        @endif
                                                    </td>

                                                    <td style="text-align: center;">
                                                        <span class="badge badge-gray">{{ $row->status_review ?? $row->status_data_quality ?? 'Draft' }}</span>
                                                    </td>
                                                    <td style="color: var(--text-muted); font-size: 0.8rem;">{{ $row->catatan_reviewer ?? $row->catatan_validasi ?? '-' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>Belum ada data register harian untuk unit dan periode ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
@endsection