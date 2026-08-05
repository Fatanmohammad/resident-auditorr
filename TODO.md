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

## Fase E — Menu Pengaturan Modul (Admin only) ✅
- [x] E1. Buat MasterSetupController (index, storeFieldWeights, storeBidangWeights)
- [x] E2. Buat view master-setup/index.blade.php (form bobot sederhana)
- [x] E3. Tambah route master-setup (admin only) di routes/web.php
- [x] E4. Tambah menu "Pengaturan Modul" di sidebar (hanya admin)
- [x] E5. Tambah role 'admin' ke enum users + user admin di DatabaseSeeder
- [x] E6. Pencatatan otomatis ke Change Log saat bobot diubah
- [x] E7. Jalankan migration & seeder, verifikasi route & user admin

## Fase F — Role admin = akses kabag_ra (kecuali Pengaturan Modul) ✅
- [x] F1. Tambah 'admin' ke middleware semua route operational (units, raw-metrics, critical-override, coverage, scheduling, final-audit-plan, risk-scoring, assignment-ra)
- [x] F2. Update $canInput di sidebar agar admin termasuk (input raw metrics & override)
- [x] F3. Menu "Pengaturan Modul" tetap hanya admin
- [x] F4. Bersihkan file verifikasi sementara (verify_admin.php, verify_mastersetup.php)
- [x] F5. Verifikasi route list OK
- [x] F6. Tambah 'admin' ke role check di view (audit-plan/index, final-audit-plan/index, scheduling/index & unit, coverage/index, assignment-ra/index, units/index, units/show)

## Fase G — Coverage score berbasis bidang relevan per jenis unit ✅
- [x] G1. Tambah CoverageSetup::relevantAreas() (Payment Point=gayung/Teller saja; KCPLK=semua kecuali Kredit)
- [x] G2. CoverageService::computeCoverageSummary() hitung score hanya dari area relevan (persentase dari jumlah relevan, bukan /8)
- [x] G3. CoverageController::store() validasi hanya area relevan + set area tidak relevan ke 'Tidak'
- [x] G4. View coverage/show.blade.php hanya tampilkan area relevan + "X dari Y area relevan"
- [x] G5. Form raw-metrics/form.blade.php sembunyikan Bidang C (untuk Payment Point) & Bidang D (untuk Payment Point & KCPLK)
- [x] G6. RawMetricController::store() validasi hanya bidang relevan + isi 0 untuk bidang tidak relevan

## Fase H — Payment Point hanya Riwayat RA, Teller, Monitoring TL (hilangkan TI/ATM) ✅
- [x] H1. Form raw-metrics/form.blade.php sembunyikan Bidang E (TI/ATM) untuk Payment Point
- [x] H2. RawMetricController::store() TI/ATM tidak required untuk Payment Point + isi 0
- [x] H3. RawMetric::hitungSemuaSkor() ti_atm = 0 untuk Payment Point
- [x] H4. MasterSetupSeeder bobot Payment Point dinormalkan 100% (Riwayat 21.05%, Teller 52.63%, Monitoring 26.32%)
- [x] H5. View units/show, risk-scoring/index, final-audit-plan/show tampilkan "-" untuk bidang tidak relevan Payment Point
- [x] H6. Verifikasi: php -l lolos, view:cache sukses, db:seed MasterSetup sukses
