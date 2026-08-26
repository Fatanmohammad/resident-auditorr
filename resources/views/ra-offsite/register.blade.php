@extends('layouts.app')

@section('content')
<div>
    <div class="page-header" style="margin-bottom: 1.5rem;">
        <div class="page-header-title">
            <h1 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #1e3a8a;"><i class="bi bi-journal-check" style="margin-right: 0.5rem; color: #3b82f6;"></i>Register Harian & Antrian Review RA</h1>
            <p style="color: var(--text-muted, #64748b); font-size: 0.85rem; margin-top: 0.25rem;">Daftar transaksi hasil scan indikator anomali yang perlu ditindaklanjuti</p>
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
            <form method="GET" action="{{ route('ra-offsite.register.index') }}" style="display: grid; grid-template-columns: 2fr 2fr 1fr; gap: 1rem; align-items: end;">
                <div>
                    <label style="font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 0.4rem;">Cabang / Unit Kerja</label>
                    <select name="cabang_id" class="form-select" onchange="this.form.submit()" style="width: 100%; font-size: 0.9rem; padding: 0.5rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; background-color: #fff;">
                        @foreach($cabangs as $c)
                            <option value="{{ $c->id }}" {{ $cabangId == $c->id ? 'selected' : '' }}>
                                {{ $c->kode_cabang ?? $c->id }} - {{ $c->nama_cabang }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 0.4rem;">Domain / Jenis Laporan</label>
                    <select name="domain_type" class="form-select" onchange="this.form.submit()" style="width: 100%; font-size: 0.9rem; padding: 0.5rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; background-color: #fff;">
                        <option value="">-- Semua Domain --</option>
                        <option value="cbs" {{ $domainType == 'cbs' ? 'selected' : '' }}>CBS / Teller</option>
                        <option value="biaya" {{ $domainType == 'biaya' ? 'selected' : '' }}>Jurnal Biaya</option>
                        <option value="kredit" {{ $domainType == 'kredit' ? 'selected' : '' }}>Nominatif Kredit</option>
                        <option value="dpk" {{ $domainType == 'dpk' ? 'selected' : '' }}>Nominatif DPK</option>
                        <option value="pengaduan" {{ $domainType == 'pengaduan' ? 'selected' : '' }}>Pengaduan Nasabah</option>
                    </select>
                </div>
                <div>
                    <a href="{{ route('ra-offsite.register.index') }}" style="display: block; text-align: center; text-decoration: none; padding: 0.5rem 1rem; border: 1px solid #cbd5e1; border-radius: 4px; color: #475569; font-weight: 500; background-color: #f8fafc; transition: all 0.2s;">Reset Filter</a>
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
                        <th style="padding: 0.75rem 1rem; color: #475569; font-weight: 600;">Detail Transaksi (Raw Data)</th>
                        <th style="padding: 0.75rem 1rem; color: #475569; font-weight: 600;">Status Review</th>
                        <th style="padding: 0.75rem 1rem; color: #475569; font-weight: 600; text-align: center;">Aksi</th>
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
                            <td style="padding: 0.75rem 1rem; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-family: monospace; color: #334155;">
                                {{ Str::limit(is_array($item->raw_data) ? implode(' | ', $item->raw_data) : $item->raw_data, 90) }}
                            </td>
                            <td style="padding: 0.75rem 1rem;">
                                @php
                                    $status = $item->status_review ?? 'Pending';
                                    $bgColor = '#f1f5f9'; $color = '#475569';
                                    if ($status == 'Verified') { $bgColor = '#dcfce7'; $color = '#166534'; }
                                    elseif ($status == 'Escalated') { $bgColor = '#fef3c7'; $color = '#92400e'; }
                                    elseif ($status == 'Rejected') { $bgColor = '#fee2e2'; $color = '#991b1b'; }
                                @endphp
                                <span style="display: inline-block; padding: 0.2rem 0.5rem; font-size: 0.75rem; font-weight: 600; border-radius: 4px; background-color: {{ $bgColor }}; color: {{ $color }};">{{ $status }}</span>
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: center;">
                                <button type="button" onclick="document.getElementById('reviewModal{{ $item->id }}').classList.add('open')" style="background: none; border: 1px solid #3b82f6; color: #3b82f6; padding: 0.3rem 0.75rem; border-radius: 4px; font-size: 0.8rem; font-weight: 500; cursor: pointer; transition: all 0.2s;"><i class="bi bi-pencil-square" style="margin-right: 0.3rem;"></i>Review</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 2rem; text-align: center; color: #64748b; font-size: 0.9rem;">Belum ada data register untuk kriteria yang dipilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Modals Review (menggunakan custom CSS modal-overlay) --}}
        @foreach($stagings as $item)
            <div class="modal-overlay" id="reviewModal{{ $item->id }}">
                <div class="modal">
                    <form action="{{ route('ra-offsite.register.update', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Review Data Staging #{{ $item->id }}</h5>
                            <button type="button" class="modal-close" onclick="document.getElementById('reviewModal{{ $item->id }}').classList.remove('open')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div style="margin-bottom: 1rem;">
                                <label style="font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 0.4rem;">Status Review</label>
                                <select name="status_review" class="form-select" required>
                                    <option value="Pending" {{ ($item->status_review ?? '') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Verified" {{ ($item->status_review ?? '') == 'Verified' ? 'selected' : '' }}>Verified (Sesuai/Valid)</option>
                                    <option value="Escalated" {{ ($item->status_review ?? '') == 'Escalated' ? 'selected' : '' }}>Escalated (Tindak Lanjut Onsite)</option>
                                    <option value="Rejected" {{ ($item->status_review ?? '') == 'Rejected' ? 'selected' : '' }}>Rejected (Bukan Temuan)</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 0.4rem;">Catatan Analisis RA</label>
                                <textarea name="catatan_ra" class="form-textarea" placeholder="Tambahkan catatan verifikasi...">{{ $item->catatan_ra }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" onclick="document.getElementById('reviewModal{{ $item->id }}').classList.remove('open')" class="btn btn-outline">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Review</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
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