<!-- Navigasi Submenu Indikasi Fraud (Desain Elegan, Nyaman di Mata & Ramah untuk Senior/Eksekutif) -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-2">
        <div class="row g-2">
            <!-- Submenu 1: Pelaporan Awal -->
            <div class="col-md-4">
                <a href="{{ route('pemeriksaan_awal.pelaporan_awal') }}" 
                   class="d-flex align-items-center justify-content-between p-3 rounded-3 text-decoration-none transition-all {{ request()->routeIs('pemeriksaan_awal.pelaporan_awal') ? 'bg-primary text-white shadow' : 'bg-light text-dark hover-shadow' }}"
                   style="border: 1px solid {{ request()->routeIs('pemeriksaan_awal.pelaporan_awal') ? '#1e3a8a' : '#e2e8f0' }}; min-height: 72px;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center {{ request()->routeIs('pemeriksaan_awal.pelaporan_awal') ? 'bg-white text-primary' : 'bg-white text-primary shadow-sm' }}" 
                             style="width: 44px; height: 44px; min-width: 44px; font-size: 1.25rem;">
                            <i class="bi bi-file-earmark-plus-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 {{ request()->routeIs('pemeriksaan_awal.pelaporan_awal') ? 'text-white' : 'text-dark' }}">1. Pelaporan Awal</div>
                            <div class="small {{ request()->routeIs('pemeriksaan_awal.pelaporan_awal') ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.82rem;">Form Kronologi & Triase Kasus</div>
                        </div>
                    </div>
                    @if(request()->routeIs('pemeriksaan_awal.pelaporan_awal'))
                        <i class="bi bi-check-circle-fill text-white fs-5"></i>
                    @endif
                </a>
            </div>

            <!-- Submenu 2: Daftar Laporan -->
            <div class="col-md-4">
                <a href="{{ route('pemeriksaan_awal.daftar_laporan') }}" 
                   class="d-flex align-items-center justify-content-between p-3 rounded-3 text-decoration-none transition-all {{ (request()->routeIs('pemeriksaan_awal.daftar_laporan') || request()->routeIs('pemeriksaan_awal.index')) ? 'bg-primary text-white shadow' : 'bg-light text-dark hover-shadow' }}"
                   style="border: 1px solid {{ (request()->routeIs('pemeriksaan_awal.daftar_laporan') || request()->routeIs('pemeriksaan_awal.index')) ? '#1e3a8a' : '#e2e8f0' }}; min-height: 72px;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center {{ (request()->routeIs('pemeriksaan_awal.daftar_laporan') || request()->routeIs('pemeriksaan_awal.index')) ? 'bg-white text-primary' : 'bg-white text-primary shadow-sm' }}" 
                             style="width: 44px; height: 44px; min-width: 44px; font-size: 1.25rem;">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 {{ (request()->routeIs('pemeriksaan_awal.daftar_laporan') || request()->routeIs('pemeriksaan_awal.index')) ? 'text-white' : 'text-dark' }}">2. Daftar Laporan</div>
                            <div class="small {{ (request()->routeIs('pemeriksaan_awal.daftar_laporan') || request()->routeIs('pemeriksaan_awal.index')) ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.82rem;">Rekapitulasi, Berkas & Bukti</div>
                        </div>
                    </div>
                    <span class="badge {{ (request()->routeIs('pemeriksaan_awal.daftar_laporan') || request()->routeIs('pemeriksaan_awal.index')) ? 'bg-white text-primary fw-bold' : 'bg-secondary text-white' }} px-3 py-2 rounded-pill fs-7">
                        {{ \App\Models\Sop09RegisterIndikasi::count() }} Kasus
                    </span>
                </a>
            </div>

            <!-- Submenu 3: Status Penanganan & Memo -->
            <div class="col-md-4">
                <a href="{{ route('pemeriksaan_awal.status_penanganan') }}" 
                   class="d-flex align-items-center justify-content-between p-3 rounded-3 text-decoration-none transition-all {{ request()->routeIs('pemeriksaan_awal.status_penanganan') ? 'bg-primary text-white shadow' : 'bg-light text-dark hover-shadow' }}"
                   style="border: 1px solid {{ request()->routeIs('pemeriksaan_awal.status_penanganan') ? '#1e3a8a' : '#e2e8f0' }}; min-height: 72px;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center {{ request()->routeIs('pemeriksaan_awal.status_penanganan') ? 'bg-white text-primary' : 'bg-white text-primary shadow-sm' }}" 
                             style="width: 44px; height: 44px; min-width: 44px; font-size: 1.25rem;">
                            <i class="bi bi-diagram-3-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 {{ request()->routeIs('pemeriksaan_awal.status_penanganan') ? 'text-white' : 'text-dark' }}">3. Status Penanganan</div>
                            <div class="small {{ request()->routeIs('pemeriksaan_awal.status_penanganan') ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.82rem;">Eskalasi & Memo Kadiv SKAI</div>
                        </div>
                    </div>
                    @if(request()->routeIs('pemeriksaan_awal.status_penanganan'))
                        <i class="bi bi-check-circle-fill text-white fs-5"></i>
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
