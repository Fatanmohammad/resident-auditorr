@extends('layouts.app')

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

        {{-- 7 kartu KKA per area --}}
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Kertas Kerja Audit (KKA)</h5></div>
    <div class="card-body">
        <div class="row g-2">
            @php
                $kkaAreas = [
                    'teller-kas' => ['Teller/Kas', $wp->kkaTellerKas()->count()],
                    'kredit' => ['Kredit', $wp->kkaKredit()->count()],
                    'biaya-beban' => ['Biaya/Beban', $wp->kkaBiayaBeban()->count()],
                    'biaya-internal' => ['Biaya/Internal', $wp->kkaBiayaInternal()->count()],
                    'pengaduan' => ['Pengaduan', $wp->kkaPengaduan()->count()],
                    'transaksi-umum' => ['Transaksi Umum', $wp->kkaTransaksiUmum()->count()],
                    'transfer-ku' => ['Transfer/KU', $wp->kkaTransferKu()->count()],
                ];
            @endphp
            @foreach($kkaAreas as $slug => [$label, $count])
                <div class="col-md-3">
                    <a href="{{ route('admin-offsite.kka-index', [$wp->id, $slug]) }}" 
                       class="card text-decoration-none {{ $count > 0 ? 'border-warning' : '' }}">
                        <div class="card-body py-2 text-center">
                            <div class="small text-muted">{{ $label }}</div>
                            <div class="h5 mb-0 {{ $count > 0 ? 'text-warning' : 'text-muted' }}">{{ $count }}</div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Register Harian Review Offsite</div>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="accordion accordion-flush" id="registerAccordion">
                    @forelse($rows as $tanggal => $areaRows)
                        @php $adaException = $areaRows->sum('exception') > 0; @endphp
                        <div class="accordion-item" style="border-bottom: 1px solid var(--border-color);">
                            <h2 class="accordion-header" style="margin: 0;">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $tanggal }}" style="padding: 1rem 1.25rem; font-weight: 600; color: var(--bs-blue-dark); background-color: #f8fafc; border: none; width: 100%; text-align: left; cursor: pointer;">
                                    <i class="bi bi-calendar-event me-2 text-muted"></i>
                                    {{ \Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM Y') }}
                                    @if($adaException)
                                        <span class="badge badge-warning ms-3">Ada Exception ({{ $areaRows->sum('exception') }})</span>
                                    @endif
                                </button>
                            </h2>
                            <div id="collapse-{{ $tanggal }}" class="accordion-collapse collapse" data-bs-parent="#registerAccordion">
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
                                                    <th>HASIL AWAL</th>
                                                    <th style="text-align: center;">STATUS REVIEW</th>
                                                    <th>CATATAN RA</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($areaRows as $row)
                                                <tr>
                                                    <td style="font-weight: 600;">{{ $row->area_review }}</td>
                                                    <td style="text-align: right; font-family: monospace; font-size: 0.9rem;">{{ number_format($row->populasi_eligible) }}</td>
                                                    <td style="text-align: right; font-family: monospace; font-size: 0.9rem;">{{ number_format($row->sampel_low) }}</td>
                                                    <td style="text-align: right; font-family: monospace; font-size: 0.9rem;">{{ number_format($row->kka_final) }}</td>
                                                    <td style="text-align: right; font-family: monospace; font-size: 0.9rem; {{ $row->exception > 0 ? 'color: #dc2626; font-weight: bold;' : '' }}">
                                                        {{ number_format($row->exception) }}
                                                    </td>
                                                    <td style="text-align: center;">
                                                        @if($row->risiko_tertinggi === 'High')
                                                            <span class="badge badge-danger">High</span>
                                                        @elseif(in_array($row->risiko_tertinggi, ['Moderate', 'Moderate to High']))
                                                            <span class="badge badge-warning">{{ $row->risiko_tertinggi }}</span>
                                                        @elseif(in_array($row->risiko_tertinggi, ['Low', 'Low to Moderate']))
                                                            <span class="badge badge-success">{{ $row->risiko_tertinggi }}</span>
                                                        @else
                                                            <span class="text-muted" style="font-size: 0.75rem;">{{ $row->risiko_tertinggi ?? '-' }}</span>
                                                        @endif
                                                    </td>
                                                    <td style="color: var(--text-muted); font-size: 0.8rem;">{{ $row->hasil_awal ?? '-' }}</td>
                                                    <td style="text-align: center;">
                                                        <span class="badge badge-gray">{{ $row->status_review }}</span>
                                                    </td>
                                                    <td style="color: var(--text-muted); font-size: 0.8rem;">{{ $row->catatan_ra ?? '-' }}</td>
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
                            <p>Belum ada baris register harian untuk periode ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
@endsection