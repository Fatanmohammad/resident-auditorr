<!-- Navigasi Submenu Indikasi Fraud (POJK 12/2024 & SOP 09 - Desain Eksekutif Senior) -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-2">
        <div class="row g-2">
            <!-- Submenu 1: Fraud Triage Checklist RA -->
            <div class="col-lg-auto col-md-6 flex-fill">
                <a href="{{ route('pemeriksaan_awal.triage_checklist') }}" 
                   class="d-flex align-items-center justify-content-between p-2.5 rounded-3 text-decoration-none transition-all {{ request()->routeIs('pemeriksaan_awal.triage_checklist*') ? 'bg-primary text-white shadow' : 'bg-light text-dark hover-shadow' }}"
                   style="border: 1px solid {{ request()->routeIs('pemeriksaan_awal.triage_checklist*') ? '#1e3a8a' : '#e2e8f0' }}; min-height: 64px;">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle d-flex align-items-center justify-content-center {{ request()->routeIs('pemeriksaan_awal.triage_checklist*') ? 'bg-white text-primary' : 'bg-white text-primary shadow-sm' }}" 
                             style="width: 38px; height: 38px; min-width: 38px; font-size: 1.15rem;">
                            <i class="bi bi-ui-checks-grid"></i>
                        </div>
                        <div>
                            <div class="fw-bold {{ request()->routeIs('pemeriksaan_awal.triage_checklist*') ? 'text-white' : 'text-dark' }}" style="font-size: 0.88rem;">1. Fraud Triage Checklist</div>
                            <div class="small {{ request()->routeIs('pemeriksaan_awal.triage_checklist*') ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.75rem;">Instrumen POJK 12/2024 (28 Item)</div>
                        </div>
                    </div>
                    @if(request()->routeIs('pemeriksaan_awal.triage_checklist*'))
                        <i class="bi bi-check-circle-fill text-white fs-6 ms-2"></i>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-2" style="font-size: 0.7rem;">⚡ 3 Kritis</span>
                    @endif
                </a>
            </div>

            <!-- Submenu 2: Indikasi Mencurigakan Cabang (18 Indikator) -->
            <div class="col-lg-auto col-md-6 flex-fill">
                <a href="{{ route('pemeriksaan_awal.indikasi_mencurigakan') }}" 
                   class="d-flex align-items-center justify-content-between p-2.5 rounded-3 text-decoration-none transition-all {{ request()->routeIs('pemeriksaan_awal.indikasi_mencurigakan*') ? 'bg-primary text-white shadow' : 'bg-light text-dark hover-shadow' }}"
                   style="border: 1px solid {{ request()->routeIs('pemeriksaan_awal.indikasi_mencurigakan*') ? '#1e3a8a' : '#e2e8f0' }}; min-height: 64px;">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle d-flex align-items-center justify-content-center {{ request()->routeIs('pemeriksaan_awal.indikasi_mencurigakan*') ? 'bg-white text-primary' : 'bg-white text-primary shadow-sm' }}" 
                             style="width: 38px; height: 38px; min-width: 38px; font-size: 1.15rem;">
                            <i class="bi bi-table"></i>
                        </div>
                        <div>
                            <div class="fw-bold {{ request()->routeIs('pemeriksaan_awal.indikasi_mencurigakan*') ? 'text-white' : 'text-dark' }}" style="font-size: 0.88rem;">2. Indikasi Mencurigakan</div>
                            <div class="small {{ request()->routeIs('pemeriksaan_awal.indikasi_mencurigakan*') ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.75rem;">Matriks 18 Indikator Cabang</div>
                        </div>
                    </div>
                    @if(request()->routeIs('pemeriksaan_awal.indikasi_mencurigakan*'))
                        <i class="bi bi-check-circle-fill text-white fs-6 ms-2"></i>
                    @else
                        <span class="badge bg-secondary text-white ms-2" style="font-size: 0.7rem;">{{ \App\Models\Sop09TemuanIndikasiCabang::count() }} Cabang</span>
                    @endif
                </a>
            </div>

            <!-- Submenu 3: Pelaporan Awal -->
            <div class="col-lg-auto col-md-4 flex-fill">
                <a href="{{ route('pemeriksaan_awal.pelaporan_awal') }}" 
                   class="d-flex align-items-center justify-content-between p-2.5 rounded-3 text-decoration-none transition-all {{ request()->routeIs('pemeriksaan_awal.pelaporan_awal') ? 'bg-primary text-white shadow' : 'bg-light text-dark hover-shadow' }}"
                   style="border: 1px solid {{ request()->routeIs('pemeriksaan_awal.pelaporan_awal') ? '#1e3a8a' : '#e2e8f0' }}; min-height: 64px;">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle d-flex align-items-center justify-content-center {{ request()->routeIs('pemeriksaan_awal.pelaporan_awal') ? 'bg-white text-primary' : 'bg-white text-primary shadow-sm' }}" 
                             style="width: 38px; height: 38px; min-width: 38px; font-size: 1.15rem;">
                            <i class="bi bi-file-earmark-plus-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold {{ request()->routeIs('pemeriksaan_awal.pelaporan_awal') ? 'text-white' : 'text-dark' }}" style="font-size: 0.88rem;">3. Pelaporan Awal</div>
                            <div class="small {{ request()->routeIs('pemeriksaan_awal.pelaporan_awal') ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.75rem;">Kronologi & Berkas Fakta</div>
                        </div>
                    </div>
                    @if(request()->routeIs('pemeriksaan_awal.pelaporan_awal'))
                        <i class="bi bi-check-circle-fill text-white fs-6 ms-2"></i>
                    @endif
                </a>
            </div>

            <!-- Submenu 4: Daftar Laporan -->
            <div class="col-lg-auto col-md-4 flex-fill">
                <a href="{{ route('pemeriksaan_awal.daftar_laporan') }}" 
                   class="d-flex align-items-center justify-content-between p-2.5 rounded-3 text-decoration-none transition-all {{ (request()->routeIs('pemeriksaan_awal.daftar_laporan') || request()->routeIs('pemeriksaan_awal.index')) ? 'bg-primary text-white shadow' : 'bg-light text-dark hover-shadow' }}"
                   style="border: 1px solid {{ (request()->routeIs('pemeriksaan_awal.daftar_laporan') || request()->routeIs('pemeriksaan_awal.index')) ? '#1e3a8a' : '#e2e8f0' }}; min-height: 64px;">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle d-flex align-items-center justify-content-center {{ (request()->routeIs('pemeriksaan_awal.daftar_laporan') || request()->routeIs('pemeriksaan_awal.index')) ? 'bg-white text-primary' : 'bg-white text-primary shadow-sm' }}" 
                             style="width: 38px; height: 38px; min-width: 38px; font-size: 1.15rem;">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div>
                            <div class="fw-bold {{ (request()->routeIs('pemeriksaan_awal.daftar_laporan') || request()->routeIs('pemeriksaan_awal.index')) ? 'text-white' : 'text-dark' }}" style="font-size: 0.88rem;">4. Daftar Laporan</div>
                            <div class="small {{ (request()->routeIs('pemeriksaan_awal.daftar_laporan') || request()->routeIs('pemeriksaan_awal.index')) ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.75rem;">Rekapitulasi Bukti Custody</div>
                        </div>
                    </div>
                    <span class="badge {{ (request()->routeIs('pemeriksaan_awal.daftar_laporan') || request()->routeIs('pemeriksaan_awal.index')) ? 'bg-white text-primary fw-bold' : 'bg-secondary text-white' }} px-2 py-1 rounded-pill ms-2" style="font-size: 0.7rem;">
                        {{ \App\Models\Sop09RegisterIndikasi::count() }} Kasus
                    </span>
                </a>
            </div>

            <!-- Submenu 5: Status Penanganan & Memo -->
            <div class="col-lg-auto col-md-4 flex-fill">
                <a href="{{ route('pemeriksaan_awal.status_penanganan') }}" 
                   class="d-flex align-items-center justify-content-between p-2.5 rounded-3 text-decoration-none transition-all {{ request()->routeIs('pemeriksaan_awal.status_penanganan') ? 'bg-primary text-white shadow' : 'bg-light text-dark hover-shadow' }}"
                   style="border: 1px solid {{ request()->routeIs('pemeriksaan_awal.status_penanganan') ? '#1e3a8a' : '#e2e8f0' }}; min-height: 64px;">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle d-flex align-items-center justify-content-center {{ request()->routeIs('pemeriksaan_awal.status_penanganan') ? 'bg-white text-primary' : 'bg-white text-primary shadow-sm' }}" 
                             style="width: 38px; height: 38px; min-width: 38px; font-size: 1.15rem;">
                            <i class="bi bi-diagram-3-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold {{ request()->routeIs('pemeriksaan_awal.status_penanganan') ? 'text-white' : 'text-dark' }}" style="font-size: 0.88rem;">5. Status Penanganan</div>
                            <div class="small {{ request()->routeIs('pemeriksaan_awal.status_penanganan') ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.75rem;">Eskalasi Memo Kadiv SKAI</div>
                        </div>
                    </div>
                    @if(request()->routeIs('pemeriksaan_awal.status_penanganan'))
                        <i class="bi bi-check-circle-fill text-white fs-6 ms-2"></i>
                    @endif
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow:hover {
    background-color: #f1f5f9 !important;
    transform: translateY(-1px);
    transition: all 0.2s ease-in-out;
}
.transition-all {
    transition: all 0.2s ease-in-out;
}
</style>
