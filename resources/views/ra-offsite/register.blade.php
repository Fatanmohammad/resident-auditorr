@extends('layouts.app')

@section('content')
<div>
    <div class="page-header" style="margin-bottom: 1.5rem;">
        <div class="page-header-title" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #1e3a8a;">
                    <i class="bi bi-journal-check" style="margin-right: 0.5rem; color: #3b82f6;"></i>Register Harian Data Staging (Monitoring Log)
                </h1>
                <p style="color: var(--text-muted, #64748b); font-size: 0.85rem; margin-top: 0.25rem;">
                    Daftar seluruh transaksi mentah hasil upload CSV dan status pemrosesan deteksi engine
                </p>
            </div>
            <div>
                <a href="{{ route('ra-offsite.kka.index') }}" style="display: inline-block; text-decoration: none; padding: 0.5rem 1rem; border-radius: 4px; color: #fff; background-color: #1e3a8a; font-size: 0.85rem; font-weight: 600;">
                    <i class="bi bi-file-earmark-spreadsheet" style="margin-right: 0.3rem;"></i> Buka Sheet KKA
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div id="success-alert" style="background-color: #d1e7dd; color: #0f5132; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; transition: opacity 0.5s ease;">
            <div><i class="bi bi-check-circle-fill" style="margin-right: 0.5rem;"></i> {{ session('success') }}</div>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #0f5132;">&times;</button>
        </div>
        <script>
            setTimeout(function() {
                var alertBox = document.getElementById('success-alert');
                if (alertBox) {
                    alertBox.style.opacity = '0';
                    setTimeout(function() { alertBox.style.display = 'none'; }, 500);
                }
            }, 5000);
        </script>
    @endif

    {{-- Filter Form --}}
    <div class="card" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #fff; margin-bottom: 1.5rem;">
        <div class="card-body" style="padding: 1.25rem;">
            <form method="GET" action="{{ route('ra-offsite.register.index') }}" style="display: grid; grid-template-columns: 2fr 2fr 1fr 1fr 1fr; gap: 1rem; align-items: end;">
                <div>
                    <label style="font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 0.4rem;">Cabang / Unit Kerja</label>
                    <select name="cabang_id" class="form-select" style="width: 100%; font-size: 0.9rem; padding: 0.5rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; background-color: #fff;">
                        @foreach($cabangs as $c)
                            <option value="{{ $c->id }}" {{ $cabangId == $c->id ? 'selected' : '' }}>
                                {{ $c->kode_cabang ?? $c->id }} - {{ $c->nama_cabang }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 0.4rem;">Domain / Jenis Laporan</label>
                    <select name="domain_type" class="form-select" style="width: 100%; font-size: 0.9rem; padding: 0.5rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; background-color: #fff;">
                        <option value="">-- Semua Domain --</option>
                        <option value="cbs" {{ request('domain_type') == 'cbs' || request('domain_type') == 'teller_kas' ? 'selected' : '' }}>Teller & Kas (CBS)</option>
                        <option value="biaya" {{ request('domain_type') == 'biaya' || request('domain_type') == 'biaya_beban' ? 'selected' : '' }}>Jurnal Biaya & Beban</option>
                        <option value="kredit" {{ request('domain_type') == 'kredit' ? 'selected' : '' }}>Nominatif Kredit</option>
                        <option value="dpk" {{ request('domain_type') == 'dpk' ? 'selected' : '' }}>Nominatif DPK</option>
                        <option value="pengaduan" {{ request('domain_type') == 'pengaduan' ? 'selected' : '' }}>Pengaduan Nasabah</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 0.4rem;">Status Deteksi</label>
                    <select name="status_flag" class="form-select" style="width: 100%; font-size: 0.9rem; padding: 0.5rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; background-color: #fff;">
                        <option value="">-- Semua Status --</option>
                        <option value="flagged" {{ request('status_flag') == 'flagged' ? 'selected' : '' }}>Flagged (Masuk KKA)</option>
                        <option value="cleared" {{ request('status_flag') == 'cleared' ? 'selected' : '' }}>Normal / Cleared</option>
                    </select>
                </div>
                <div>
                    <button type="submit" style="width: 100%; padding: 0.5rem 1rem; border: none; border-radius: 4px; color: #fff; background-color: #1e3a8a; font-weight: 600; font-size: 0.85rem; cursor: pointer;">
                        <i class="bi bi-funnel" style="margin-right: 0.3rem;"></i> Filter
                    </button>
                </div>
                <div>
                    <a href="{{ route('ra-offsite.register.index') }}" style="display: block; text-align: center; text-decoration: none; padding: 0.5rem 1rem; border: 1px solid #cbd5e1; border-radius: 4px; color: #475569; font-weight: 500; background-color: #f8fafc;">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Register Staging --}}
    <div class="card" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #fff;">
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left;">
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 0.75rem 1rem; color: #475569; font-weight: 600;">#</th>
                        <th style="padding: 0.75rem 1rem; color: #475569; font-weight: 600;">Tanggal</th>
                        <th style="padding: 0.75rem 1rem; color: #475569; font-weight: 600;">Domain</th>
                        <th style="padding: 0.75rem 1rem; color: #475569; font-weight: 600;">Detail Transaksi</th>
                        <th style="padding: 0.75rem 1rem; color: #475569; font-weight: 600;">Risk Level</th>
                        <th style="padding: 0.75rem 1rem; color: #475569; font-weight: 600;">Status Deteksi</th>
                        <th style="padding: 0.75rem 1rem; color: #475569; font-weight: 600; text-align: center;">Aksi / Navigasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stagings as $item)
                        <tr style="border-bottom: 1px solid #e2e8f0; transition: background-color 0.2s;">
                            <td style="padding: 0.75rem 1rem; color: #64748b;">{{ $loop->iteration + $stagings->firstItem() - 1 }}</td>
                            <td style="padding: 0.75rem 1rem; font-weight: 500;">{{ $item->tgl_transaksi ? \Carbon\Carbon::parse($item->tgl_transaksi)->format('d/m/Y') : '-' }}</td>
                            <td style="padding: 0.75rem 1rem;">
                                <span style="display: inline-block; padding: 0.2rem 0.5rem; font-size: 0.75rem; font-weight: 600; border-radius: 4px; background-color: #e0f2fe; color: #0369a1;">{{ strtoupper($item->domain_type) }}</span>
                            </td>
                            
                            {{-- KOLOM DETAIL TRANSAKSI YANG SUDAH DISARING CLEAN --}}
                            <td style="padding: 0.75rem 1rem; max-width: 320px;">
                                @php
                                    $data = is_string($item->raw_data) ? json_decode($item->raw_data, true) : $item->raw_data;
                                    $uraian = $data['URAIAN'] ?? $data['uraian'] ?? $data['DESKRIPSI'] ?? $data['deskripsi'] ?? null;
                                    $noRek  = $data['NO_REK'] ?? $data['no_rek'] ?? $data['NO_REKENING'] ?? $data['no_rekening'] ?? null;
                                @endphp

                                @if($uraian || $noRek)
                                    <div style="font-weight: 600; color: #1e293b; line-height: 1.2;">
                                        {{ $uraian ?? '-' }}
                                    </div>
                                    @if($noRek)
                                        <div style="font-size: 0.75rem; color: #64748b; font-family: monospace; margin-top: 0.15rem;">
                                            No. Rek: {{ $noRek }}
                                        </div>
                                    @endif
                                @else
                                    <span style="color: #94a3b8; font-size: 0.8rem;">-</span>
                                @endif
                            </td>

                            <td style="padding: 0.75rem 1rem;">
                                @if($item->risk_level == 'High')
                                    <span style="display: inline-block; padding: 0.2rem 0.5rem; font-size: 0.75rem; font-weight: 600; border-radius: 4px; background-color: #fee2e2; color: #991b1b;">High Risk</span>
                                @elseif($item->risk_level == 'Moderate')
                                    <span style="display: inline-block; padding: 0.2rem 0.5rem; font-size: 0.75rem; font-weight: 600; border-radius: 4px; background-color: #fef3c7; color: #92400e;">Moderate</span>
                                @else
                                    <span style="display: inline-block; padding: 0.2rem 0.5rem; font-size: 0.75rem; font-weight: 600; border-radius: 4px; background-color: #f1f5f9; color: #475569;">Normal</span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem 1rem;">
                                @if($item->perlu_kka)
                                    <span style="display: inline-block; padding: 0.2rem 0.55rem; font-size: 0.75rem; font-weight: 600; border-radius: 4px; background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">
                                        <i class="bi bi-exclamation-triangle-fill" style="margin-right: 0.3rem;"></i>Flagged &rarr; KKA
                                    </span>
                                @else
                                    <span style="display: inline-block; padding: 0.2rem 0.55rem; font-size: 0.75rem; font-weight: 600; border-radius: 4px; background-color: #dcfce7; color: #166534; border: 1px solid #86efac;">
                                        <i class="bi bi-check-circle-fill" style="margin-right: 0.3rem;"></i>Normal / Cleared
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: center;">
                                @if($item->perlu_kka)
                                    <a href="{{ route('ra-offsite.kka.index', ['sheet' => strtolower($item->kka_sheet_tujuan ?? $item->domain_type)]) }}" 
                                       style="display: inline-block; text-decoration: none; padding: 0.3rem 0.75rem; border: 1px solid #ef4444; color: #dc2626; border-radius: 4px; font-size: 0.8rem; font-weight: 600; background-color: #fff; transition: all 0.2s;">
                                        Buka KKA <i class="bi bi-arrow-right" style="margin-left: 0.2rem;"></i>
                                    </a>
                                @else
                                    <span style="color: #94a3b8; font-size: 0.8rem;">No Action</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 2rem; text-align: center; color: #64748b; font-size: 0.9rem;">Belum ada data register untuk kriteria yang dipilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($stagings->hasPages())
        <div class="pagination-container">
            {{ $stagings->links('pagination::bootstrap-4') }}
        </div>
        @endif
    </div>
</div>

<style>
    .data-table tbody tr:hover {
        background-color: #f8fafc;
    }
    
    /* Pagination Styles */
    .pagination-container {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        background-color: #fff;
        display: flex;
        justify-content: center;
    }
    .pagination-container nav {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .pagination {
        display: flex;
        padding-left: 0;
        list-style: none;
        margin: 0;
        border-radius: 0.25rem;
    }
    .page-item.active .page-link {
        z-index: 3;
        color: #fff;
        background-color: #1e3a8a;
        border-color: #1e3a8a;
    }
    .page-item.disabled .page-link {
        color: #94a3b8;
        pointer-events: none;
        background-color: #f8fafc;
        border-color: #e2e8f0;
    }
    .page-link {
        position: relative;
        display: block;
        padding: 0.4rem 0.75rem;
        margin-left: -1px;
        line-height: 1.25;
        color: #1e3a8a;
        background-color: #fff;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    .page-link:hover {
        z-index: 2;
        color: #1e40af;
        text-decoration: none;
        background-color: #f1f5f9;
        border-color: #cbd5e1;
    }
    .page-item:first-child .page-link {
        border-top-left-radius: 0.375rem;
        border-bottom-left-radius: 0.375rem;
    }
    .page-item:last-child .page-link {
        border-top-right-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
    }
    .pagination-container p {
        margin: 0;
        font-size: 0.85rem;
        color: #64748b;
    }
</style>
@endsection