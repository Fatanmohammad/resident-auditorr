@extends('layouts.app')
@section('title', 'Register Klarifikasi')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div class="page-header-title">
        <h4 class="fw-bold mb-1" style="color: var(--primary-blue, #1e3a8a);">Register Klarifikasi (Follow-up Exception)</h4>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">Tindak lanjut atas Exception / Selisih yang terdeteksi secara otomatis dari Daily Register KKA Fisik.</p>
    </div>
    
    <div class="d-flex align-items-center flex-wrap gap-2">
        @if($isAdmin)
            {{-- Filter Cabang Khusus untuk Akun Admin / Reviewer --}}
            <form method="GET" action="{{ route('daily-register.klarifikasi.index') }}" class="d-flex align-items-center gap-2 m-0">
                <div class="d-flex align-items-center gap-2 bg-white px-3 py-1 rounded shadow-sm border" style="border-color: #cbd5e1 !important;">
                    <label for="filter_kode_unit" class="form-label mb-0 fw-bold text-muted" style="font-size: 0.78rem; white-space: nowrap;">
                        <i class="bi bi-buildings text-primary me-1"></i> Cabang:
                    </label>
                    <select id="filter_kode_unit" name="kode_unit" class="form-select form-select-sm border-0 fw-bold" style="font-size: 0.82rem; min-width: 220px; background: transparent; cursor: pointer; color: #1e3a8a;" onchange="this.form.submit()">
                        <option value="">-- Semua Unit Kantor Cabang --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->unit_code }}" {{ request('kode_unit') == $u->unit_code ? 'selected' : '' }}>
                                {{ $u->unit_code }} - {{ $u->unit_name }}
                            </option>
                        @endforeach
                    </select>
                    @if(request('kode_unit'))
                        <a href="{{ route('daily-register.klarifikasi.index') }}" class="text-danger ms-1" title="Reset filter" style="font-size: 0.85rem;">
                            <i class="bi bi-x-circle-fill"></i>
                        </a>
                    @endif
                </div>
            </form>
        @else
            {{-- Informasi Cabang & Filter Sub-Unit untuk Akun RA --}}
            <div class="d-flex align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 bg-white px-3 py-1 rounded shadow-sm border" style="border-color: #cbd5e1 !important; font-size: 0.82rem; height: 34px;">
                    <i class="bi bi-geo-alt-fill text-primary"></i>
                    <span class="text-muted fw-bold">Cabang Penugasan:</span>
                    <strong class="text-primary">{{ $userCabangName ?? (auth()->user()->cabang->nama_cabang ?? 'Cabang Penugasan') }}</strong>
                </div>

                @if(isset($units) && count($units) > 1)
                <form method="GET" action="{{ route('daily-register.klarifikasi.index') }}" class="d-flex align-items-center gap-2 m-0">
                    <div class="d-flex align-items-center gap-2 bg-white px-3 py-1 rounded shadow-sm border" style="border-color: #cbd5e1 !important;">
                        <label for="filter_kode_unit_ra" class="form-label mb-0 fw-bold text-muted" style="font-size: 0.78rem; white-space: nowrap;">
                            <i class="bi bi-building text-primary me-1"></i> Unit:
                        </label>
                        <select id="filter_kode_unit_ra" name="kode_unit" class="form-select form-select-sm border-0 fw-bold" style="font-size: 0.82rem; min-width: 180px; background: transparent; cursor: pointer; color: #1e3a8a;" onchange="this.form.submit()">
                            <option value="">-- Semua Unit di Cabang Ini --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->unit_code }}" {{ request('kode_unit') == $u->unit_code ? 'selected' : '' }}>
                                    {{ $u->unit_code }} - {{ $u->unit_name }}
                                </option>
                            @endforeach
                        </select>
                        @if(request('kode_unit'))
                            <a href="{{ route('daily-register.klarifikasi.index') }}" class="text-danger ms-1" title="Reset filter" style="font-size: 0.85rem;">
                                <i class="bi bi-x-circle-fill"></i>
                            </a>
                        @endif
                    </div>
                </form>
                @endif
            </div>
        @endif

        <div class="badge badge-purple px-3 py-2" style="font-size: 0.85rem; background: #6366f1; color: #fff; border-radius: 8px;">
            <i class="bi bi-clock-history me-1"></i> Menunggu: {{ $klarifikasis->where('status', 'Menunggu Klarifikasi')->count() }} Item
        </div>
    </div>
</div>

@if(request('kode_unit'))
    <div class="alert alert-info py-2 px-3 d-flex align-items-center justify-content-between mb-3 shadow-sm" style="font-size: 0.82rem; border-radius: 8px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af;">
        <div>
            <i class="bi bi-funnel-fill me-1 text-primary"></i> Filter aktif pada kode unit: <strong>{{ request('kode_unit') }}</strong>
        </div>
        <a href="{{ route('daily-register.klarifikasi.index') }}" class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold" style="font-size: 0.75rem; border-radius: 6px;">
            <i class="bi bi-x me-1"></i> Reset Filter
        </a>
    </div>
@endif

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Tanggal / Sumber</th>
                    <th style="width: 15%;">Unit / Cabang</th>
                    <th style="width: 30%;">Detail Temuan (Exception)</th>
                    <th class="text-center" style="width: 15%;">Risk Matriks</th>
                    <th class="text-center" style="width: 15%;">Status</th>
                    <th class="text-center" style="width: 10%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($klarifikasis as $k)
                    @php
                        // Polymorphic mapping
                        $tipeKka = 'Unknown';
                        $objek = '';
                        $selisih = 0;
                        $penyebab = '';
                        $riskScore = 0;
                        $riskLevel = 'Rendah';
                        
                        if ($k->source) {
                            $riskScore = $k->source->risk_score ?? 0;
                            $riskLevel = $k->source->severity_level ?? 'Rendah';
                            $penyebab = $k->source->penyebab ?? 'Tidak ada penyebab dilampirkan.';
                            
                            if (str_contains($k->source_type, 'CashDetail')) {
                                $tipeKka = 'Cash Opname';
                                $objek = $k->source->objek_persediaan . ' (' . $k->source->pecahan . ')';
                                $selisih = 'Selisih Rp ' . number_format($k->source->selisih, 0, ',', '.');
                            } elseif (str_contains($k->source_type, 'ObsDetail')) {
                                $tipeKka = 'Observasi Lingkungan';
                                $objek = $k->source->area_observasi;
                                $selisih = 'Kondisi: ' . $k->source->kondisi_aktual;
                            } elseif (str_contains($k->source_type, 'StokDetail')) {
                                $tipeKka = 'Stok Opname';
                                $objek = $k->source->sub_area . ' - ' . explode('|', $k->source->kondisi_objek)[0];
                                $selisih = 'Selisih ' . $k->source->selisih . ' lembar/buku';
                            } elseif (str_contains($k->source_type, 'KartuAtmDetail')) {
                                $tipeKka = 'Rekon Kartu ATM';
                                $objek = ($k->source->sub_area ?? '') . ' - ' . ($k->source->jenis_kartu ?? '');
                                $selisih = (is_numeric($k->source->selisih ?? null)) ? 'Selisih ' . $k->source->selisih . ' keping' : 'Kondisi: ' . ($k->source->hasil_fisik ?? '-');
                            } elseif (str_contains($k->source_type, 'BarangCetakanDetail')) {
                                $tipeKka = 'Rekon Barang Cetakan';
                                $objek = ($k->source->kategori ?? '') . ' - ' . ($k->source->nama_barang ?? '');
                                $selisih = 'Selisih ' . ($k->source->selisih ?? 0) . ' ' . ($k->source->satuan ?? 'Bh');
                            }
                        }

                        // Badge Color Logic
                        $badgeColor = match($riskLevel) {
                            'Kritis' => 'badge-danger',
                            'Tinggi' => 'badge-warning',
                            'Sedang' => 'badge-info',
                            default => 'badge-gray'
                        };
                        
                        $isMenunggu = $k->status === 'Menunggu Klarifikasi';
                    @endphp
                    <tr>
                        <td style="font-size:0.78rem;">
                            <div style="font-weight:600; color:var(--text-main);">{{ $k->created_at->format('d M Y') }}</div>
                            <div style="color:var(--text-muted); margin-top:2px;">{{ $tipeKka }}</div>
                        </td>
                        <td>
                            <strong>{{ $k->kode_unit }}</strong>
                        </td>
                        <td>
                            <strong style="color:var(--bs-blue-dark);">{{ $objek }}</strong><br>
                            <span style="color:#dc2626; font-weight:600; font-size:0.8rem;">{{ $selisih }}</span><br>
                            <span style="font-size:0.75rem; color:var(--text-muted); display:inline-block; margin-top:4px;">
                                "{{ $penyebab }}"
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $badgeColor }} shadow-sm">
                                {{ $riskLevel }} ({{ $riskScore }})
                            </span>
                        </td>
                        <td class="text-center">
                            @if($isMenunggu)
                                <span class="badge badge-warning shadow-sm">
                                    <i class="bi bi-hourglass-split me-1"></i> Menunggu
                                </span>
                            @else
                                <span class="badge badge-success shadow-sm">
                                    <i class="bi bi-check-circle me-1"></i> Selesai
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($isMenunggu && !$isAdmin)
                                <button class="btn btn-primary btn-sm shadow-sm btn-klarifikasi" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#klarifikasiModal"
                                    data-id="{{ $k->id }}"
                                    data-tipe="{{ $tipeKka }}"
                                    data-objek="{{ $objek }}"
                                    data-selisih="{{ $selisih }}"
                                    data-penyebab="{{ $penyebab }}"
                                    data-unit="{{ $k->kode_unit }}"
                                    data-tanggapan="{{ $k->tanggapan_cabang }}"
                                    data-simpulan="{{ $k->simpulan_ra }}">
                                    Isi Tanggapan
                                </button>
                            @elseif($isMenunggu && $isAdmin)
                                <button class="btn btn-warning btn-sm shadow-sm btn-klarifikasi" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#klarifikasiModal"
                                    data-id="{{ $k->id }}"
                                    data-tipe="{{ $tipeKka }}"
                                    data-objek="{{ $objek }}"
                                    data-selisih="{{ $selisih }}"
                                    data-penyebab="{{ $penyebab }}"
                                    data-unit="{{ $k->kode_unit }}"
                                    data-tanggapan="{{ $k->tanggapan_cabang }}"
                                    data-simpulan="{{ $k->simpulan_ra }}">
                                    Review Temuan
                                </button>
                            @else
                                <button class="btn btn-outline btn-sm shadow-sm disabled">
                                    Selesai
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="bi bi-shield-check fs-1 d-block mb-3" style="color: var(--bs-blue-light);"></i>
                                <h6 class="fw-bold">Tidak Ada Exception</h6>
                                <p class="mb-0 fs-7">Belum ada KKA Fisik yang memicu exception atau memerlukan klarifikasi.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Klarifikasi -->
<div class="modal fade" id="klarifikasiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background: var(--bg-main); border-bottom: 1px solid var(--border-color);">
        <h5 class="modal-title fw-bold" style="color: var(--bs-blue-dark);">Form Tindak Lanjut Klarifikasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('daily-register.klarifikasi.submit') }}" method="POST">
          @csrf
          <input type="hidden" name="klarifikasi_id" id="modalKlarifikasiId" value="">
          
          <div class="modal-body" style="padding: 1.5rem;">
            
            <!-- Temuan Box -->
            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                <h6 style="color: #dc2626; font-weight: 700; font-size: 0.85rem; margin-bottom: 0.8rem; text-transform: uppercase;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Detail Exception
                </h6>
                <div style="display: grid; grid-template-columns: 120px 1fr; gap: 0.5rem; font-size: 0.85rem;">
                    <div style="color: #7f1d1d; font-weight: 600;">Sumber KKA</div>
                    <div id="modalTipeKka" style="font-weight: 700;"></div>
                    
                    <div style="color: #7f1d1d; font-weight: 600;">Unit / Cabang</div>
                    <div id="modalUnit" style="font-weight: 700;"></div>
                    
                    <div style="color: #7f1d1d; font-weight: 600;">Objek Masalah</div>
                    <div id="modalObjek" style="font-weight: 700;"></div>
                    
                    <div style="color: #7f1d1d; font-weight: 600;">Selisih Fisik</div>
                    <div id="modalSelisih" style="color: #dc2626; font-weight: 700;"></div>
                    
                    <div style="color: #7f1d1d; font-weight: 600;">Penyebab</div>
                    <div id="modalPenyebab" style="font-style: italic;"></div>
                </div>
            </div>

            <!-- Input Fields -->
            <div class="form-group">
                <label class="form-label">Tanggapan Pihak Cabang (Hasil Klarifikasi) <span style="color: #dc2626;">*</span></label>
                <textarea class="form-textarea" name="tanggapan_cabang" id="modalTanggapan" rows="3" required placeholder="Jelaskan kronologi, alasan, atau dokumen pendukung yang diberikan oleh pihak cabang atas selisih ini..." {{ $isAdmin ? 'readonly' : '' }}></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Simpulan Akhir Auditor (RA) <span style="color: #dc2626;">*</span></label>
                <textarea class="form-textarea" name="simpulan_ra" id="modalSimpulan" rows="3" required placeholder="Berikan opini dan kesimpulan akhir Anda setelah mengklarifikasi temuan ini..." {{ $isAdmin ? 'readonly' : '' }}></textarea>
            </div>

          </div>
          
          <div class="modal-footer" style="background: var(--bg-main); border-top: 1px solid var(--border-color); display: flex; justify-content: space-between;">
            @if($isAdmin)
                <div style="font-size: 0.75rem; color: var(--text-muted);">
                    Tentukan status akhir temuan ini
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" name="action" value="clear" class="btn btn-success" style="background: #10b981; border: none; font-size: 0.85rem;">
                        Clear Temuan (Wajar)
                    </button>
                    <button type="submit" name="action" value="escalate" class="btn btn-danger" style="background: #ef4444; border: none; font-size: 0.85rem;">
                        Eskalasi Temuan Resmi
                    </button>
                </div>
            @else
                <div style="font-size: 0.75rem; color: var(--text-muted);">
                    Simpan tanggapan Anda
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" name="action" value="save" class="btn btn-primary" style="font-size: 0.85rem;">
                        <i class="bi bi-save me-1"></i> Simpan Tanggapan
                    </button>
                </div>
            @endif
          </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnKlarifikasis = document.querySelectorAll('.btn-klarifikasi');
    
    btnKlarifikasis.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('modalKlarifikasiId').value = this.getAttribute('data-id');
            document.getElementById('modalTipeKka').textContent = this.getAttribute('data-tipe');
            document.getElementById('modalObjek').textContent = this.getAttribute('data-objek');
            document.getElementById('modalSelisih').textContent = this.getAttribute('data-selisih');
            document.getElementById('modalPenyebab').textContent = `"${this.getAttribute('data-penyebab')}"`;
            document.getElementById('modalUnit').textContent = this.getAttribute('data-unit');
            document.getElementById('modalTanggapan').value = this.getAttribute('data-tanggapan') || '';
            document.getElementById('modalSimpulan').value = this.getAttribute('data-simpulan') || '';
        });
    });
});
</script>
@endsection
