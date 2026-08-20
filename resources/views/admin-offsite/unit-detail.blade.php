@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-3">
        <a href="{{ route('admin-offsite.cabang-detail', $unit->cabang_id) }}?tahun={{ $tahun }}&bulan={{ $bulan }}" 
           class="btn btn-sm btn-secondary">← Kembali</a>
    </div>
    <h2 class="mb-2">{{ $unit->unit_code }} — {{ $unit->unit_name }}</h2>
    <p class="text-muted">{{ $unit->unit_type }} | Periode: {{ \Carbon\Carbon::create($tahun, $bulan)->isoFormat('MMMM Y') }}</p>

    @if (!$wp)
        <div class="alert alert-info">
            Belum ada data Offsite Review untuk unit ini pada periode ini.
        </div>
    @else
        {{-- Header identitas WP --}}
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Identitas WP</h5></div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3"><strong>Kode WP:</strong> {{ $wp->kode_wp }}</div>
                    <div class="col-md-3"><strong>Jenis Unit:</strong> {{ $wp->jenis_unit }}</div>
                    <div class="col-md-6"><strong>Kantor Induk:</strong> {{ $wp->kantor_induk }}</div>
                    <div class="col-md-4"><strong>Periode:</strong> {{ $wp->periode_mulai->format('d/m/Y') }} s.d. {{ $wp->periode_selesai->format('d/m/Y') }}</div>
                    {{-- Baris di bawah ini tampilkan RA Pelaksana di header —
                         hapus baris ini kalau mentor mau nama RA benar-benar
                         tidak muncul di manapun, bukan cuma di tabel daftar unit --}}
                    <div class="col-md-4"><strong>Reviewer:</strong> {{ $wp->reviewerBagianRa->name ?? '-' }}</div>
                    <div class="col-md-4">
                        <strong>Status WP:</strong>
                        <span class="badge bg-{{ $wp->status_wp === 'Final' ? 'success' : ($wp->status_wp === 'Aktif' ? 'warning' : 'secondary') }}">
                            {{ $wp->status_wp }}
                        </span>
                    </div>
                    <div class="col-md-4"><strong>Validasi Unit:</strong> {{ $wp->validasi_unit }}</div>
                </div>
            </div>
        </div>

        {{-- 6 kotak ringkasan --}}
        <div class="row text-center mb-4">
            <div class="col">
                <div class="card p-2"><div class="text-muted small">Populasi</div><div class="h4 mb-0">{{ $ringkasan['populasi'] }}</div></div>
            </div>
            <div class="col">
                <div class="card p-2"><div class="text-muted small">Sampel Low</div><div class="h4 mb-0">{{ $ringkasan['sampel_low'] }}</div></div>
            </div>
            <div class="col">
                <div class="card p-2"><div class="text-muted small">KKA Final</div><div class="h4 mb-0">{{ $ringkasan['kka_final'] }}</div></div>
            </div>
            <div class="col">
                <div class="card p-2"><div class="text-muted small">Exception</div><div class="h4 mb-0 text-warning">{{ $ringkasan['exception'] }}</div></div>
            </div>
            <div class="col">
                <div class="card p-2"><div class="text-muted small">Klarifikasi</div><div class="h4 mb-0">{{ $ringkasan['klarifikasi'] }}</div></div>
            </div>
            <div class="col">
                <div class="card p-2"><div class="text-muted small">Eskalasi</div><div class="h4 mb-0 text-danger">{{ $ringkasan['eskalasi'] }}</div></div>
            </div>
        </div>

        {{-- Tabel harian, dikelompokkan per tanggal (accordion) --}}
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Register Harian</h5></div>
            <div class="accordion accordion-flush" id="registerAccordion">
                @forelse($rows as $tanggal => $areaRows)
                    @php $adaException = $areaRows->sum('exception') > 0; @endphp
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapse-{{ $tanggal }}">
                                {{ \Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM Y') }}
                                @if($adaException)
                                    <span class="badge bg-warning ms-2">Ada Exception</span>
                                @endif
                            </button>
                        </h2>
                        <div id="collapse-{{ $tanggal }}" class="accordion-collapse collapse"
                             data-bs-parent="#registerAccordion">
                            <div class="accordion-body p-0">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Area</th>
                                            <th>Populasi</th>
                                            <th>Sampel Low</th>
                                            <th>KKA Final</th>
                                            <th>Exception</th>
                                            <th>Risiko Tertinggi</th>
                                            <th>Hasil Awal</th>
                                            <th>Status Review</th>
                                            <th>Catatan RA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($areaRows as $row)
                                        <tr>
                                            <td>{{ $row->area_review }}</td>
                                            <td>{{ $row->populasi_eligible }}</td>
                                            <td>{{ $row->sampel_low }}</td>
                                            <td>{{ $row->kka_final }}</td>
                                            <td>{{ $row->exception }}</td>
                                            <td>{{ $row->risiko_tertinggi ?? '-' }}</td>
                                            <td>{{ $row->hasil_awal ?? '-' }}</td>
                                            <td>{{ $row->status_review }}</td>
                                            <td>{{ $row->catatan_ra ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-3 text-muted">Belum ada baris register untuk periode ini.</div>
                @endforelse
            </div>
        </div>
    @endif
</div>
@endsection