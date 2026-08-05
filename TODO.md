# TODO — Hak Akses RA: HANYA Input Raw Metrics

## Tujuan
Role RA **hanya** dapat menginput raw metrics untuk unit di wilayahnya (cabang sendiri + anak cabangnya). RA TIDAK boleh melihat skor risiko, penilaian risiko, assignment RA, atau menu lainnya.

## Langkah
- [x] Tambah route `raw-metrics.index` (daftar unit untuk RA input)
- [x] Tambah method `RawMetricController::index()` — list unit sesuai wilayah RA
- [x] Buat view `raw-metrics/index.blade.php` (daftar unit + tombol input, tanpa skor risiko)
- [x] Update `routes/web.php` — hapus `ra` dari `units.*`, `risk-scoring.index`, `assignment-ra.index`
- [x] Update `sidebar.blade.php` — untuk RA hanya tampilkan "Input Raw Metrics"
- [x] Perbaiki link kembali di `raw-metrics/form.blade.php` untuk RA (ganti `units.show` → `raw-metrics.index`)
- [x] Redirect login RA → `raw-metrics.index` (AuthController) — RA tidak mendarat di dashboard
- [x] Redirect dashboard RA → `raw-metrics.index` (DashboardController) — statistik risiko tidak tampil
- [x] Verifikasi (view:cache, route, uji akses)
- [x] Bersihkan file script sementara
