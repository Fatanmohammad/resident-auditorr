# TODO — Perbaikan Modul Audit Plan

## Fase A — Perbaiki bobot field_weights ✅
- [x] A1. Perbaiki bobot TI/ATM (critical_ti_incident 0.30, unusual_user_reset 0.20)
- [x] A2. Perbaiki bobot Monitoring TL (ra_onsite 0.15, ra_offsite 0.10, skai 0.20, regulator 0.25)
- [x] A3. Hapus entri tl_response_quality (tidak ada bobot di SPEC)
- [x] A4. Tambah cleanup idempoten di seeder + jalankan ulang seeder

## Fase B — Buat view yang hilang ✅
- [x] B1. Buat assignment-ra/index.blade.php (§3B.3)
- [x] B2. Buat risk-scoring/index.blade.php (§3B.2.b)

## Fase C — Perluas sidebar & dashboard ✅
- [x] C1. Perluas sidebar.blade.php (submenu §3B + role-based)
- [x] C2. Tambah agregat §5 di DashboardController
- [x] C3. Tampilkan agregat §5 di dashboard.blade.php
- [x] C4. Tambah CSS nav-group (collapsible sidebar)

## Fase D — Bersihkan & sinkronisasi ✅
- [x] D1. Hapus inspect_weights.php & file sementara
- [x] D2. Jalankan ulang seeder MasterSetup
- [x] D3. Verifikasi bobot & halaman tidak error (view compile OK, syntax OK, route OK)
