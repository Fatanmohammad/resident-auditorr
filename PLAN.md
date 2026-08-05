# RENCANA PERBAIKAN MODUL AUDIT PLAN (SOP 01)

## INFORMASI YANG DIKUMPULKAN

Proyek Laravel 13 ini sudah **sangat lengkap**. Semua layer inti sudah terpasang & berfungsi:
- **Services:** `RiskScoringService`, `CoverageService`, `SchedulingService`, `FinalAuditPlanService`
- **Controllers:** `UnitController`, `RawMetricController`, `CriticalOverrideController`, `CoverageController`, `SchedulingController`, `FinalAuditPlanController`, `DashboardController`
- **Models & Migrations:** lengkap (units, ras, ra_assignments, coverage, onsite_frequency, scheduled_visits, ra_capacity, final_audit_plan, change_log, dll)
- **Seeder:** MasterSetupSeeder, UnitSeeder, RaSeeder, CoverageSeeder, DatabaseSeeder

**Skema skoring SUDAH benar** (6 bidang, bukan 9-komponen fosil) sesuai Lampiran A.4.

## MASALAH / KESENJANGAN TERHADAP SPEC

### 1. Bobot `field_weights` di MasterSetupSeeder tidak cocok Lampiran A.1
- **TI/ATM:** seeder memiliki `critical_ti_incident=0.15`, `unusual_user_reset=0.10` (total 0.75)
  → SPEC A.1: `critical_ti_incident=0.30`, `unusual_user_reset=0.20` (total 1.00)
- **Monitoring TL:** seeder memiliki `ra_onsite=0.20, ra_offsite=0.25, skai=0.10, regulator=0.10, kap=0.10, avg=0.10, + tl_response_quality=0.15`
  → SPEC A.1: `ra_onsite=0.15, ra_offsite=0.10, skai=0.20, regulator=0.25, kap=0.10, avg=0.10` (TANPA `tl_response_quality` — checklist kualitatif)

### 2. View hilang (akan error saat dibuka)
- `resources/views/assignment-ra/index.blade.php` (rute `assignment-ra.index`)
- `resources/views/risk-scoring/index.blade.php` (rute `risk-scoring.index`)

### 3. Sidebar minimal
- Hanya Dashboard + Audit Plan. Belum struktur submenu §3B lengkap.

### 4. Dashboard belum agregat §5
- Belum ada distribusi kategori risiko, frekuensi, jenis unit.

### 5. File sementara `inspect_weights.php` masih ada di root.

## RENCANA EDIT

### Fase A — Perbaiki bobot field_weights (MasterSetupSeeder.php)
Koreksi array `$fieldWeights`:
- Ganti `critical_ti_incident` 0.15 → **0.30**, `unusual_user_reset` 0.10 → **0.20**
- Ganti: `ra_onsite_tl_overdue` 0.20 → **0.15**, `ra_offsite_tl_overdue` 0.25 → **0.10**, `skai_tl_overdue` 0.10 → **0.20**, `regulator_tl_overdue` 0.10 → **0.25**
- `kap_tl_overdue` tetap 0.10, `avg_response_days` tetap 0.10
- **Hapus** entri `tl_response_quality` yang tidak ada bobotnya di SPEC (checklist kualitatif)

### Fase B — Buat view yang hilang
- **`assignment-ra/index.blade.php`** (§3B.3): tabel unit + RA; filter per RA; badge warning "Backup RA kosong"; badge "Perlu Mapping RA"; kategori risiko (konteks).
- **`risk-scoring/index.blade.php`** (§3B.2.b): tabel 6 skor bidang + weighted score + kategori awal/final + override + prioritas; sort/filter; drill-down ke detail unit.

### Fase C — Perluas sidebar & dashboard
- **`sidebar.blade.php`**: struktur submenu §3B lengkap dengan visibilitas berbasis role.
- **`DashboardController`**: tambah agregat §5 (distribusi kategori risiko, frekuensi, jenis unit).
- **`dashboard.blade.php`**: tampilkan agregat §5.

### Fase D — Hapus file sementara & sinkronisasi
- Hapus `inspect_weights.php`.
- Jalankan ulang seeder (php artisan db:seed --class=MasterSetupSeeder) untuk menerapkan bobot baru.

## FILE TERKAIT YANG DIEDIT
- `database/seeders/MasterSetupSeeder.php`
- `resources/views/assignment-ra/index.blade.php` (baru)
- `resources/views/risk-scoring/index.blade.php` (baru)
- `resources/views/layouts/sidebar.blade.php`
- `app/Http/Controllers/DashboardController.php`
- `resources/views/dashboard.blade.php`

## LANGKAH TINDAK LANJUT
1. Jalankan ulang seeder MasterSetup.
2. Verifikasi bobot baru via query (php artisan tinker / script).
3. Buka halaman assignment-ra & risk-scoring untuk memastikan tidak error.
4. Uji dashboard & sidebar.

## STATUS: EKSEKUSI ✅

Semua fase di atas sudah dieksekusi. Rincian lengkap ada di `TODO.md`.

### Fase E — Pengaturan Modul (Admin only) [baru]
- **`MasterSetupController`** (baru): `index`, `storeFieldWeights`, `storeBidangWeights` — pengelolaan bobot skoring via UI, dengan pencatatan otomatis ke Change Log.
- **`resources/views/master-setup/index.blade.php`** (baru): form bobot indikator (Lampiran A.1) & bobot bidang (Lampiran A.4).
- **Route `master-setup.*`** (baru, middleware `role:admin`).
- **Menu "Pengaturan Modul"** di sidebar (hanya admin).
- **Role `admin`** ditambahkan ke enum `users` (migration baru `2026_08_04_000001_add_admin_role_to_users_table.php`) + user admin di `DatabaseSeeder`.
- **Audit trail**: `ChangeLog::record()` ditambahkan ke `CriticalOverrideController`, `SchedulingController`, `UnitController`, `CoverageController`, `MasterSetupController` (memenuhi spec §7.1).

### Fase F — Role admin = akses kabag_ra (kecuali Pengaturan Modul) ✅
- Admin dimasukkan ke middleware semua route operational (units, raw-metrics, critical-override, coverage, scheduling, final-audit-plan, risk-scoring, assignment-ra, audit-plan workflow).
- `$canInput` di sidebar termasuk admin (input raw metrics & override).
- Menu "Pengaturan Modul" tetap **khusus admin**.
- File verifikasi sementara (verify_admin.php, verify_mastersetup.php) dihapus.

### Verifikasi
- `php -l` seluruh file yang diubah → tidak ada syntax error.
- `php artisan view:cache` → semua blade (termasuk baru) terkompilasi tanpa error.
- `php artisan route:list` → 46+ route terdaftar termasuk master-setup.
- Bobot diverifikasi: TI/ATM = 1.00, Monitoring TL = 0.90 (sesuai Lampiran A.1, tanpa bobot `tl_response_quality`).
- User admin `admin@banksulteng.co.id` / `password123` berhasil dibuat.
- Admin & kabag_ra punya akses yang sama ke seluruh modul; hanya admin yang lihat menu "Pengaturan Modul".
