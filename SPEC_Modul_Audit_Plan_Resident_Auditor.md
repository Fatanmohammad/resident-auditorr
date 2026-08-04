# SPESIFIKASI SISTEM — MODUL AUDIT PLAN (APLIKASI RESIDENT AUDITOR)

> Dokumen ini adalah rekonstruksi lengkap dari workbook Excel `WP_SOP_01_RA_Audit_Plan.xlsx` (SOP 01 — 17 sheet), diubah menjadi spesifikasi fungsional siap pakai untuk membangun Modul Audit Plan pada Aplikasi Resident Auditor. Tujuannya: tidak ada logika, formula, atau aturan bisnis dari file sumber yang terlewat.
>
> Gunakan dokumen ini sebagai prompt/brief ke tim developer atau ke AI coding assistant (misal Claude Code) untuk membangun modul ini dari nol.

---

## 1. RINGKASAN SISTEM

### 1.1 Tujuan Modul
Modul Audit Plan menghasilkan **rencana audit tahunan** untuk seluruh unit kerja bank (Kantor Cabang, KCP, KCPLK, Payment Point), mencakup:
1. Penilaian risiko tiap unit berbasis data kuantitatif
2. Penentuan Resident Auditor (RA) yang bertanggung jawab per unit
3. Penentuan cakupan pemantauan harian (offsite) per unit
4. Penentuan frekuensi dan jadwal kunjungan fisik (onsite) per unit
5. Pengecekan beban kerja RA supaya tidak overload
6. Output akhir: Final Audit Plan — dasar bagi SOP 02 (Daily Offsite), SOP 04 (Onsite), dan SOP 05 (Risk Trigger)

### 1.2 Prinsip Desain Kunci
- **Satu unit = satu entitas utama.** Semua data lain menempel ke unit lewat kode unit.
- **Input manual minimal, kalkulasi otomatis maksimal.** Hanya beberapa titik input manusia; sisanya adalah hasil turunan (computed/derived).
- **Override manusia selalu menang di atas kalkulasi otomatis**, tapi harus tercatat alasannya (audit trail).
- **RA "menetap" (resident) di satu Kantor Cabang** dan mengawasi cabang itu plus unit-unit kecil (KCP/KCPLK/Payment Point) yang berada di bawah kantor induk yang sama — bukan RA per-bidang.
- **Ada dua jenis pemantauan berbeda:** *offsite* (review harian dari sistem, H+1) dan *onsite* (kunjungan fisik, terjadwal berdasarkan risiko).

### 1.3 Alur Data End-to-End

```
[1] AUDIT UNIVERSE (data master unit)
        │
        ▼
[2] RAW METRICS (input angka kejadian mentah, 6 bidang, 26 indikator)
        │  bobot indikator-dalam-bidang
        ▼
[3] SKOR KOMPONEN (6 skor bidang, 0-100, per unit)
        │  bobot bidang-ke-skor-akhir (beda per jenis unit)
        ▼
[4] SKOR RISIKO FINAL + KATEGORI (Low s.d. High)
        │  dicek terhadap
        ▼
[5] CRITICAL OVERRIDE (jika ada trigger darurat aktif → paksa High)
        │
        ▼
[6] ASSIGNMENT RA (berbasis lokasi geografis, BUKAN skor risiko)
        │
        ├──▶ [7] OFFSITE COVERAGE (per 8 area fungsi → skor kelengkapan)
        │           └──▶ [7B] COVERAGE DETAIL (per 19 data code spesifik)
        │
        └──▶ [8] ONSITE FREQUENCY (kategori risiko + jenis unit → frekuensi/tahun)
                    └──▶ [9] ONSITE CALENDAR (frekuensi → tanggal riil kunjungan)
                                └──▶ [10] RA CAPACITY (cek beban kerja RA per bulan)
                                            └──▶ [11] FINAL AUDIT PLAN (output akhir)
```

Paralel dengan alur ini: **[12] MASTER SETUP** adalah sumber semua parameter (bobot, ambang batas, matriks frekuensi, mapping RA, parameter kalender) yang dipakai di hampir semua langkah di atas. **[13] CHANGE LOG** mencatat semua perubahan manual/override untuk audit trail.

---

## 2. PERAN PENGGUNA (ROLE)

| Role | Tanggung jawab dalam sistem ini |
|---|---|
| **Resident Auditor (RA)** | Melakukan review offsite harian & onsite periodik di unit yang jadi tanggung jawabnya; sumber temuan yang direkap ke Raw Metrics |
| **Bagian RA (Kantor Pusat)** | Mengoordinasi semua RA; input/verifikasi Raw Metrics; mengelola Critical Override; approve perubahan parameter di Master Setup; menyetujui Final Audit Plan |
| **PUK (Pejabat berwenang, semacam approver)** | Menyetujui override manual jadwal onsite; menyetujui perubahan mapping RA |
| **Admin Sistem** | Mengelola Master Setup (bobot, threshold, matriks frekuensi, daftar RA), mengelola Audit Universe (daftar unit) |

*Catatan: file sumber tidak mendefinisikan role secara eksplisit di satu tempat — pembagian ini disimpulkan dari pola desain workbook (siapa yang logis mengisi kolom mana), bukan dari dokumen resmi. Sesuaikan dengan struktur organisasi riil.*

---

## 3. MODEL DATA (ENTITAS & SKEMA TABEL)

Berikut skema database yang disarankan, dipetakan dari 17 sheet Excel jadi entitas ternormalisasi (bukan 1 sheet = 1 tabel, karena banyak sheet Excel sebenarnya adalah *view* hasil kalkulasi, bukan tabel independen).

### 3.1 `units` (dari sheet 01_Audit_Universe)
Entitas utama sistem. Field:

| Field | Tipe | Sumber | Wajib diisi manual? |
|---|---|---|---|
| `unit_code` | string, unique, PK | Kolom B | Ya |
| `unit_name` | string | Kolom C | Ya |
| `unit_type` | enum(KC, KCP, KCPLK, Payment Point) | Kolom D | Ya |
| `parent_office` | string | Kolom E (Kantor Induk) | Ya |
| `region` | string | Kolom F (Wilayah) | Ya |
| `is_active` | boolean (Aktif/Nonaktif) | Kolom G | Ya |
| `base_ra_unit` | string, FK → nama cabang di master RA | Kolom H | Ya — ini yang menentukan RA-nya siapa |
| `distance_from_parent_km` | number | Kolom I | Ya |
| `transaction_volume_category` | enum(Tinggi, Sedang, Rendah) | **computed** | Tidak — auto dari skor risiko unit (≥70 Tinggi, ≥40 Sedang, else Rendah) |
| `auto_description` | text | **computed** | Tidak — auto: jika KC → teks tetap "KC induk tempat RA berkedudukan..."; jika bukan → gabungan frekuensi onsite dari modul frekuensi |

**Aturan validasi:**
- `unit_code` harus unik.
- `base_ra_unit` harus match dengan salah satu nama cabang yang ada mapping RA-nya di Master Setup — jika tidak ketemu, seluruh downstream (assignment RA) harus menampilkan status error "Perlu Mapping RA", bukan gagal diam-diam.

### 3.2 `raw_metrics` (dari sheet 02A_Raw_Metrics)
Satu baris per unit per periode (disarankan: per tahun atau per periode penilaian, bukan snapshot tunggal — beri kolom `period` supaya historis tersimpan, tidak overwrite). 26 indikator mentah, dikelompokkan 6 bidang:

**Bidang A — Riwayat Pemeriksaan Resident Auditor** *(sumber: rekap laporan RA sebelumnya, bukan sistem eksternal)*
| Field | Label |
|---|---|
| `prior_onsite_findings` | Temuan Onsite Tahun Lalu |
| `significant_findings` | Temuan Signifikan |
| `repeat_findings` | Temuan Berulang |
| `offsite_deviation` | Penyimpangan Pada Offsite |
| `offsite_deviation_significant` | Penyimpangan Offsite Signifikan |
| `offsite_deviation_repeat` | Penyimpangan Offsite Berulang |
| `months_since_last_onsite` | Lama Sejak Onsite (Bulan) |

**Bidang B — Kas/Teller & Operasional Harian** *(sumber: CBS teller/kas, GL kas)*
| Field | Label |
|---|---|
| `reversal_correction_txn` | Transaksi Reversal/Koreksi |
| `cash_discrepancy` | Selisih Kas |
| `unusual_cost_journal` | Biaya/Jurnal Tidak Lazim |
| `large_risky_cash_txn` | Transaksi Tunai Besar Berisiko |

**Bidang C — CS/DPK/APU-PPT dan Layanan** *(sumber: CBS/CIF/CS register, FDS/APU-PPT)*
| Field | Label |
|---|---|
| `dpk_anomaly` | Anomali Pengelolaan DPK |
| `overdue_complaints` | Pengaduan Nasabah Overdue |
| `incomplete_cdd_edd` | Pengkinian Data/CDD-EDD Belum Selesai |

**Bidang D — Kredit** *(sumber: core kredit/laporan kredit)*
| Field | Label |
|---|---|
| `debtors_col_3_5` | Jumlah Debitur Kol 3-5 |
| `npl_ratio` | Rasio NPL (desimal, contoh 0.01 = 1%) |
| `credit_deviation` | Penyimpangan/Deviasi Kredit |

**Bidang E — TI/ATM dan User Access** *(sumber: ATM Center/Switch, IAM/AD)*
| Field | Label |
|---|---|
| `atm_dispute` | Selisih/Dispute ATM |
| `atm_downtime_hours` | Downtime ATM (total jam) |
| `critical_ti_incident` | Insiden TI Kritikal |
| `unusual_user_reset` | Reset/Buka Blokir User Tidak Lazim |

**Bidang F — Monitoring Tindak Lanjut** *(sumber: register TL dari RA, SKAI, Regulator, KAP — bukan hanya RA)*
| Field | Label |
|---|---|
| `ra_onsite_tl_overdue` | Temuan RA Onsite Overdue |
| `ra_offsite_tl_overdue` | Temuan RA Offsite Overdue |
| `skai_tl_overdue` | Temuan SKAI Overdue |
| `regulator_tl_overdue` | Temuan Regulator Overdue |
| `kap_tl_overdue` | Temuan KAP Overdue |
| `avg_response_days` | Rata-Rata Hari Respons TL |
| `tl_response_quality` | Kualitas Respons dan Bukti TL (checklist 4 poin: tepat waktu, menjawab pokok masalah, bukti memadai, tindakan perbaikan jelas) |

**Validasi penting:** semua field ini adalah **angka kejadian mentah**, bukan skor. Tidak boleh ada logika subjektif di titik input ini (sesuai catatan asli file: *"Tidak ada skor subjektif di sheet ini"*).

### 3.3 `field_weights` (bagian dari Master Setup — bobot indikator dalam bidang)
Tabel referensi statis, 26 baris (satu per indikator raw metric), field: `metric_key`, `bidang`, `weight` (desimal 0-1). Ini yang dipakai untuk menghitung skor bidang (lihat §4.2). **Total bobot per bidang tidak harus persis 1.0** — desainnya membiarkan skor per-bidang bisa melebihi rentang tertentu sebelum dipotong ke maksimum 100.

*(Nilai lengkap ada di Lampiran A.1)*

### 3.4 `risk_component_scores` (dari sheet 02_Risk_Input — hasil computed, JANGAN diinput manual)
Satu baris per unit per periode. Field: 6 skor bidang (0-100), semua **computed**, lihat formula di §4.2.

### 3.5 `risk_scoring` (dari sheet 03_Risk_Scoring — hasil computed)
| Field | Tipe | Computed dari |
|---|---|---|
| `weighted_score` | number 0-100 | SUM(skor bidang × bobot bidang, bobot beda per jenis unit) |
| `initial_category` | enum(Low, Low to Moderate, Moderate, Moderate to High, High) | Pemetaan `weighted_score` ke rentang |
| `has_active_override` | boolean | Ada tidaknya baris aktif di `critical_overrides` untuk unit ini |
| `final_category` | enum | Jika override aktif → "High" paksa; jika tidak → sama dengan `initial_category` |
| `override_reason` | text, nullable | Diambil dari `critical_overrides` jika ada |
| `priority_rank` | integer 1-5 | High=1 (tertinggi) ... Low=5 |

### 3.6 `critical_overrides` (dari sheet 04_Critical_Override — input manual)
| Field | Tipe |
|---|---|
| `unit_code` | FK → units |
| `trigger_date` | date |
| `trigger_type` | **enum resmi (dropdown asli file), 8 pilihan tetap**: Fraud Indicator, Selisih Kas Material, Dokumen/Agunan Hilang, User Sistem Tidak Sah, Transaksi Tanpa Otorisasi, TL High/Critical Overdue, Penolakan Data RA, Repeat Finding Critical |
| `trigger_description` | text bebas |
| `status` | **enum resmi, 3 nilai**: Aktif, Tidak Aktif, Selesai *(bukan 2 nilai — hanya baris berstatus "Aktif" yang memicu override kategori High)* |
| `approved_by` | text |
| `notes` | text |

Sebuah unit yang punya **minimal satu baris dengan status "Aktif"** akan otomatis dipaksa kategori risiko "High" di `risk_scoring.final_category`, apapun skornya.

### 3.7 `ras` (Resident Auditor — daftar auditor, dari Master Setup kolom A-E)
| Field | Tipe |
|---|---|
| `ra_id` | string, unique, PK (contoh: "AMP-1", "LWK-1", "LWK-2") |
| `ra_name` | string |
| `base_branch` | string (kantor cabang tempat RA menetap) |
| `status` | enum(Aktif, Non-aktif) |
| `monthly_capacity_days` | number (default 20 hari kerja) |

### 3.8 `branch_ra_mapping` (dari Master Setup kolom M-Q — mapping cabang ke RA)
| Field | Tipe |
|---|---|
| `branch_name` | string, unique |
| `primary_ra_id` | FK → ras |
| `backup_ra_id` | FK → ras, **nullable** — banyak cabang TIDAK punya backup (lihat §6.2 catatan data quality) |

### 3.9 `ra_assignments` (dari sheet 05_Assignment_RA — hasil computed dari lookup berantai)
| Field | Computed dari |
|---|---|
| `unit_code` | — |
| `risk_category` | lookup ke `risk_scoring` (hanya untuk ditampilkan, TIDAK memengaruhi assignment) |
| `primary_ra_id` | `units.base_ra_unit` → lookup `branch_ra_mapping.primary_ra_id` |
| `backup_ra_id` | sama, `.backup_ra_id` |
| `resident_base` | = `units.base_ra_unit` |
| `assignment_status` | selalu "Aktif" |
| `valid_from`, `valid_to` | periode assignment (tahunan, harus di-refresh tiap tahun ganti) |
| `notes` | jika `base_ra_unit` tidak ketemu mapping-nya → "Perlu Mapping RA — Lengkapi Master Setup" |

**Aturan bisnis kunci:** assignment RA murni berbasis **kedekatan geografis/administratif** (unit menempel ke cabang mana), **BUKAN berbasis skor risiko atau beban kerja**. Ini desain asli — kalau ingin lebih pintar, ini titik yang layak ditingkatkan di aplikasi baru (lihat §8).

### 3.10 `coverage_setup` (dari sheet 06A_Coverage_Setup — input manual per area fungsi)
8 area per unit, tiap area bernilai **Ya / Tidak / Event**:
`teller_kas`, `cs_dpk`, `kredit`, `atm`, `biaya_jurnal`, `apu_fds`, `ti_event`, `pengaduan_aset`

Beberapa area punya **default otomatis** (boleh dioverride manual):
```
kredit  = "Ya" jika unit_type ∈ {KC, KCP}, selain itu "Event"
atm     = "Ya" jika unit_type = KC, selain itu "Event"
```

### 3.11 `coverage_summary` (dari sheet 06_Offsite_Coverage — computed)
| Field | Computed dari |
|---|---|
| `area_status[8]` | Terjemahan flag `coverage_setup`: Ya→"H+1", Event→"Event-based", Tidak→"Tidak" |
| `active_area_count` | COUNT area yang statusnya H+1 atau Event-based |
| `coverage_score` | `active_area_count / 8` |
| `coverage_status` | Score=1 → "Lengkap"; ≥0.75 → "Cukup"; else → "Perlu Lengkapi Setup". **Field ini punya dropdown resmi 4 nilai** (Lengkap, Cukup, Perlu Lengkapi Setup, **Nonaktif**) — nilai "Nonaktif" tidak dihasilkan formula manapun, kemungkinan disediakan untuk override manual jika unit dinonaktifkan dari monitoring; sediakan sebagai opsi override manual di aplikasi |

### 3.12 `data_codes` (referensi statis, dari Master Setup kolom G-K — 19 data code)
Daftar lengkap ada di Lampiran A.2. Field: `data_code`, `area`, `daily_offsite_capable` (Ya/Tidak/H+1 jika ada), `default_frequency` (H+1/Event-based/Onsite), `description`.

**Penting:** data code dengan `daily_offsite_capable = "Tidak"` (contoh: `ASET_FISIK`, `DOKUMEN_AGUNAN`, `CCTV_ALARM`) **otomatis** dipaksa jadi mode "Onsite/Periodik" — tidak peduli setting `coverage_setup` unit tersebut, karena secara fisik memang tidak bisa dicek dari layar.

### 3.13 `coverage_detail` (dari sheet 06B_Coverage_Detail — computed, 1 baris per unit × per data_code)
Relasi many-to-many antara `units` dan `data_codes`, dengan field tambahan `final_coverage_mode` (H+1 / Event-based / Onsite-Periodik / Tidak), `enters_sop02` (boolean — masuk kerja Daily Offsite), `enters_sop04` (boolean — masuk kerja Onsite Terjadwal).

### 3.14 `frequency_matrix` (referensi statis, Master Setup kolom J-P — matriks 5×4)
Baris = 5 kategori risiko, kolom = 4 jenis unit → hasil label frekuensi + angka kali/tahun. Nilai lengkap di Lampiran A.3.

**Aturan bisnis kritis:** untuk `unit_type = KC`, label frekuensi SELALU "Resident Daily Review" (+ "Trigger" jika kategori High) — dan nilai numerik kali/tahun-nya **0**, bukan angka besar. Ini BUKAN berarti KC tidak dipantau — sebaliknya, KC dipantau **setiap hari** karena RA menetap fisik di situ, sehingga konsep "kunjungan terjadwal diskrit" tidak relevan untuk KC. Field terpisah (`resident_daily_review = true` jika unit_type=KC) harus dibuat eksplisit supaya tidak disalahartikan sebagai "0 = tidak dipantau".

### 3.15 `onsite_frequency` (dari sheet 07_Onsite_Frequency — computed + override manual)
| Field | Tipe |
|---|---|
| `unit_code` | FK |
| `auto_frequency_label` | lookup `frequency_matrix` berdasarkan `risk_category` × `unit_type` |
| `auto_visits_per_year` | konversi label ke angka |
| `manual_override_frequency` | nullable, input manual. **Dropdown resmi HANYA 5 pilihan**: Bulanan, Triwulanan, Semesteran, Tahunan, Tidak Terjadwal — TIDAK bisa dioverride jadi "Resident Daily Review" atau label gabungan seperti "Tahunan/Semesteran" (label gabungan itu HANYA muncul dari hasil otomatis, tidak tersedia sebagai pilihan override manual) |
| `final_frequency_label` | override jika ada, else auto |
| `final_visits_per_year` | angka final |
| `basis_note` | "Otomatis dari kategori risiko dan jenis unit" / "Override manual" |
| `cumulative_visits_running_total` | helper: total kumulatif kunjungan semua unit sampai baris ini (untuk penjadwalan) |
| `visit_sequence_start` | helper: nomor urut kunjungan global dari mana unit ini mulai |

### 3.16 `scheduled_visits` (dari sheet 08_Onsite_Calendar — computed, 1 baris per kunjungan bukan per unit)
Tabel ini adalah **unrolled** dari `onsite_frequency`: kalau sebuah unit punya target 2 kunjungan/tahun, muncul 2 baris di sini.

| Field | Tipe | Computed dari |
|---|---|---|
| `unit_code` | FK | — |
| `visit_number` | integer (ke-1, ke-2, dst) | — |
| `recommended_month` | 1-12 | Bulanan→visit_number; Triwulanan→visit_number×3; Semesteran→visit_number×6; Tahunan→6 (tetap) |
| `default_duration_days` | integer | lookup parameter kalender: Bulanan=2, Triwulanan=5, Semesteran=7, Tahunan=12 |
| `auto_start_date` | date | `DATE(tahun_audit_plan, recommended_month, hari_mulai)` — hari mulai disebar (bukan selalu tgl 1) supaya tidak numpuk |
| `auto_end_date` | date | `auto_start_date + default_duration_days - 1` |
| `manual_override_start`, `manual_override_end` | date, nullable | input manual PUK/Bagian RA |
| `final_start_date`, `final_end_date` | date | override jika ada, else auto |
| `final_duration_days` | integer | `final_end_date - final_start_date + 1` |
| `status` | **enum resmi dari dropdown asli file (Inggris), 5 nilai**: Planned, In Progress, Completed, Postponed, Cancelled | Formula asli SELALU set "Planned" secara hardcode dan tidak pernah otomatis pindah ke status lain — 4 nilai lainnya cuma tersedia sebagai pilihan manual yang faktanya tidak pernah dipakai formula. **Di aplikasi ini WAJIB jadi field yang bisa diupdate riil oleh RA/PUK** |
| `basis_note`, `manual_notes` | text | — |

### 3.17 `ra_capacity` (dari sheet 09_RA_Capacity — computed, 1 baris per RA per bulan)
| Field | Computed dari |
|---|---|
| `ra_id` | — |
| `month` | 1 baris per RA per bulan (12 baris/RA/tahun) |
| `effective_working_days` | parameter default (20 hari/bulan) |
| `daily_offsite_unit_count` | COUNT unit yang: (a) primary RA = RA ini, (b) status Daily Offsite di `coverage_summary` = "Aktif" |
| `estimated_offsite_days` | `daily_offsite_unit_count × effort_per_unit_per_month` (default 1 hari/unit/bulan) |
| `scheduled_visit_count` | COUNT baris `scheduled_visits` bulan ini milik RA ini, status ≠ Cancelled |
| `scheduled_visit_days` | SUM `final_duration_days` untuk kunjungan bulan ini |
| `total_workload_days` | `estimated_offsite_days + scheduled_visit_days` |
| `utilization` | `total_workload_days / effective_working_days` |
| `capacity_status` | >100% → "Over Capacity"; >85% (parameter) → "Warning"; else → "OK" |
| `recommendation_note` | teks saran otomatis sesuai status |

### 3.18 `final_audit_plan` (dari sheet 10_Final_Audit_Plan — output akhir, computed, view gabungan)
Satu baris per unit, gabungan dari semua modul:
`unit_code`, `unit_name`, `unit_type`, `risk_category`, `primary_ra`, `backup_ra`, `daily_offsite_active` (Ya/Tidak — dari `coverage_summary`), `onsite_frequency_label`, `visits_per_year`, `is_resident_daily_review` (Ya jika KC), `risk_trigger_visit_required` (Ya jika kategori High, "Jika Trigger" jika tidak), `plan_status` (Approved / "Draft - Lengkapi Mapping RA" jika RA belum termapping), `notes`.

### 3.19 `change_log` (dari sheet 11_Change_Log — input manual, murni tabel)
`log_id`, `date`, `sheet_area`, `unit_code`, `change_description`, `reason`, `approved_by`, `status` (**enum resmi dari dropdown asli file, 4 nilai**: Draft, Approved, Rejected, Implemented). Tidak ada logika otomatis — ini murni audit trail manual untuk setiap perubahan parameter/mapping/override.

### 3.20 `master_setup` (parameter global, dari sheet 12_Master_Setup — bukan tabel data, tapi konfigurasi sistem)
Lihat Lampiran A untuk nilai lengkap. Kelompok parameter:
- Bobot 6 komponen risiko ke skor akhir (per jenis unit)
- Ambang batas kategori risiko (5 rentang skor)
- Matriks frekuensi onsite (5 kategori × 4 jenis unit)
- Tabel konversi label frekuensi → kali/tahun
- Daftar RA + mapping ke cabang
- Daftar 19 data code + area + mode default
- Parameter kalender & kapasitas (tahun audit plan, durasi kunjungan per tipe, effort daily offsite per unit, ambang warning utilisasi, hari kerja efektif default)

---

## 3B. STRUKTUR NAVIGASI & DETAIL TIAP MENU (PRESENTATION LAYER)

> Bagian ini melengkapi §3 (Model Data) dengan lapisan UI: menu apa saja yang ada di modul Audit Plan, apa isi tiap halaman, field mana read-only vs editable, dan menu itu memetakan ke entitas mana. Dashboard diletakkan di **level aplikasi** (di luar modul Audit Plan), karena dia menampilkan agregat lintas-modul, bukan cuma dari Audit Plan.

### Struktur menu final

```
📁 AUDIT PLAN
│
├── 🏢 Data Unit
├── ⚠️ Penilaian Risiko
│     ├── Input Data Mentah
│     ├── Hasil Skor & Kategori
│     └── Trigger Darurat
├── 👤 Assignment RA
├── 🔍 Coverage Offsite
│     ├── Setup Fungsi Unit
│     └── Detail per Data Code
├── 📅 Jadwal Onsite
│     ├── Frekuensi per Unit
│     └── Kalender Kunjungan
├── 📈 Kapasitas RA
├── ✅ Final Audit Plan
├── 📜 Change Log
└── ⚙️ Pengaturan Modul
```

---

### 3B.1 Data Unit
**Entitas**: `units` (§3.1)
**Tujuan**: kelola daftar master unit kerja.

**Tampilan utama**: tabel semua unit.
| Kolom | Read-only / Editable |
|---|---|
| Kode Unit | Editable saat create, read-only setelahnya |
| Nama Unit | Editable |
| Jenis Unit (KC/KCP/KCPLK/Payment Point) | Editable (dropdown) |
| Kantor Induk | Editable |
| Wilayah | Editable |
| Status Aktif/Nonaktif | Editable (toggle) |
| Base RA Unit | Editable (dropdown dari daftar cabang di mapping RA) |
| Jarak dari Kantor Induk (km) | Editable |
| Kategori Volume Transaksi | **Read-only** — computed dari skor risiko |
| Keterangan | **Read-only** — computed |

**Fitur**: tambah/edit/nonaktifkan unit; filter per jenis unit/wilayah/status aktif; **badge warning** kalau `base_ra_unit` tidak ketemu di mapping RA (link langsung ke Assignment RA untuk lihat detail).

**Aksi**: Create, Edit, Nonaktifkan (soft-delete, bukan hapus permanen — supaya histori tetap ada).

---

### 3B.2 Penilaian Risiko

#### 3B.2.a Input Data Mentah
**Entitas**: `raw_metrics` (§3.2)
**Tujuan**: tempat RA/Bagian RA input angka kejadian mentah per unit per periode.

**Tampilan utama**: form per unit (pilih unit dulu dari dropdown/search), dibagi 6 tab/section sesuai bidang:
- Tab Riwayat Pemeriksaan RA (7 field)
- Tab Kas/Teller (4 field)
- Tab CS/DPK/APU-PPT (3 field)
- Tab Kredit (3 field)
- Tab TI/ATM (4 field)
- Tab Monitoring TL (7 field, termasuk 1 field checklist kualitatif)

Semua field **editable**, semua angka **wajib non-negatif** (validasi), field rasio (NPL) wajib 0-1.

**Fitur penting yang TIDAK ada di Excel tapi WAJIB ada di aplikasi**: pilihan **periode** (misal "Tahun 2027" / "Triwulan 3 2027") supaya input baru tidak menimpa data lama — simpan sebagai histori, bukan overwrite (lihat §7 poin 3).

**Aksi**: Simpan draft, Submit (kunci setelah submit, perubahan berikutnya butuh approval — opsional tergantung kebijakan organisasi kamu), Lihat histori input periode sebelumnya.

#### 3B.2.b Hasil Skor & Kategori
**Entitas**: `risk_component_scores` + `risk_scoring` (§3.4, §3.5) — **digabung satu halaman**
**Tujuan**: menampilkan hasil kalkulasi otomatis, murni read-only.

**Dari mana asal angkanya**: 6 kolom skor bidang dihitung dari `raw_metrics` (angka yang diinput di menu "Input Data Mentah") dikalikan bobot indikator per bidang — rumus lengkapnya ada di **§4.2** dan daftar bobotnya di **Lampiran A.1**. Skor Weighted Final adalah gabungan 6 skor bidang itu dikalikan bobot bidang-ke-final yang berbeda per jenis unit — rumusnya di **§4.3** dan tabelnya di **Lampiran A.4**. Kategori Awal adalah hasil pemetaan Skor Weighted ke salah satu dari 5 rentang (§4.4, Lampiran A.5). Kategori Final memperhitungkan override darurat kalau ada (§4.5).

**Tampilan utama**: tabel semua unit dengan kolom:
| Kolom | Isi |
|---|---|
| Kode/Nama Unit | — |
| 6 kolom skor bidang | Riwayat RA, Kas/Teller, CS/DPK, Kredit, TI/ATM, Monitoring TL — semua 0-100, dari `raw_metrics` × bobot §4.2 |
| Skor Weighted Final | Gabungan 6 skor bidang × bobot per jenis unit, §4.3 |
| Kategori Awal | Pemetaan skor ke rentang, §4.4 |
| Override Aktif? | Ya/Tidak, dicek ke `critical_overrides`, dengan link ke Trigger Darurat kalau Ya |
| Kategori Final | Override menang jika aktif, §4.5 — badge warna (merah=High, kuning=Moderate, hijau=Low) |
| Prioritas | Rank 1-5, hasil pemetaan Kategori Final |

Semua kolom **read-only** — tidak ada tombol edit di sini, karena ini murni tampilan hasil. Kalau user mau ubah angka, mereka diarahkan ke "Input Data Mentah".

**Fitur**: sort berdasarkan Skor Weighted (lihat unit paling berisiko duluan), filter per kategori, **klik nama unit → drill-down** menampilkan rincian perhitungan per unit — daftar 6 bidang beserta angka mentah, bobot, dan kontribusi masing-masing ke skor final (persis pola perhitungan yang dijelaskan di §4.2-§4.3), supaya user bisa lihat persis kenapa angka akhirnya keluar segitu, bukan cuma percaya hasil jadi.

#### 3B.2.c Trigger Darurat
**Entitas**: `critical_overrides` (§3.6)
**Tujuan**: catat kejadian darurat yang memaksa kategori jadi High.

**Tampilan utama**: tabel daftar trigger + form tambah baru.
| Field | Editable? |
|---|---|
| Unit | Editable (dropdown, wajib) |
| Tanggal Trigger | Editable |
| Jenis Trigger | Editable — **dropdown 8 pilihan tetap**: Fraud Indicator, Selisih Kas Material, Dokumen/Agunan Hilang, User Sistem Tidak Sah, Transaksi Tanpa Otorisasi, TL High/Critical Overdue, Penolakan Data RA, Repeat Finding Critical |
| Deskripsi | Editable, text bebas |
| Status | Editable — **dropdown 3 pilihan**: Aktif, Tidak Aktif, Selesai |
| Disetujui Oleh | Editable |
| Catatan | Editable |

**Fitur**: filter status "Aktif" saja (default view); setiap perubahan status otomatis tercatat ke Change Log.

---

### 3B.3 Assignment RA
**Entitas**: `ra_assignments` (§3.9), dibentuk dari `units` + `branch_ra_mapping` (§3.1, §3.8)
**Tujuan**: menampilkan siapa RA (Primary & Backup) yang bertanggung jawab atas tiap unit.

**Dari mana asal datanya (rantai sumbernya, bukan diketik langsung di menu ini)**:
1. Setiap unit di `units` punya field `base_ra_unit` — nama kantor cabang tempat unit itu "menempel" secara administratif (diisi manual di menu **Data Unit**, bukan di sini).
2. Nama cabang itu dicocokkan ke tabel `branch_ra_mapping` (dikelola di **Pengaturan Modul**) untuk mendapat `primary_ra_id` dan `backup_ra_id`.
3. Hasil pencocokan 2 langkah itulah yang muncul di halaman ini — jadi field Primary RA/Backup RA di menu ini **read-only**, bukan diketik manual di sini. Kalau mau ganti RA suatu cabang secara permanen, perubahan dilakukan di **Pengaturan Modul → Daftar RA & Mapping Cabang**, bukan diklik-edit satu-satu di halaman ini.
4. **Kategori Risiko** yang ikut ditampilkan di tabel ini datanya dari `risk_scoring` (§3.5) — murni untuk konteks visual, TIDAK ikut menentukan hasil assignment (assignment murni berbasis lokasi geografis, bukan skor risiko — lihat aturan bisnis §4.6 dan §6.7).

**Tampilan utama**: tabel semua unit.
| Kolom | Read-only / Editable | Sumber |
|---|---|---|
| Kode/Nama Unit | Read-only | `units` |
| Kategori Risiko | Read-only, hanya konteks | `risk_scoring` |
| Resident Base | Read-only | `units.base_ra_unit` |
| Primary RA | Read-only (hasil lookup otomatis) | `branch_ra_mapping.primary_ra_id` |
| Backup RA | Read-only, **bisa kosong** | `branch_ra_mapping.backup_ra_id` |
| Status Assignment | Read-only, selalu "Aktif" | computed |
| Berlaku Mulai — Sampai | Editable oleh Admin (periode tahunan) | `ra_assignments.valid_from/valid_to` |
| Catatan | Read-only, muncul otomatis | computed: "Perlu Mapping RA" jika `base_ra_unit` tidak ketemu di `branch_ra_mapping` |

**Fitur khusus di halaman ini**:
- **Badge warning** pada baris yang Backup RA-nya kosong (ingat temuan §6.2 — mayoritas cabang di data sumber memang tidak punya backup).
- **Badge warning kedua** pada baris berstatus "Perlu Mapping RA" — unit ini butuh perhatian karena base RA unit-nya belum terdaftar di mapping, sehingga tidak ada Primary RA yang bisa ditentukan otomatis.
- **Filter per RA** — pilih 1 RA, tampilkan semua unit yang jadi tanggung jawabnya (berguna buat RA itu sendiri cek portofolio unitnya, atau Bagian RA cek beban kerja kasar sebelum buka menu Kapasitas RA).
- **Form override manual** (khusus kasus khusus, misal RA cuti mendadak dan unitnya perlu dialihkan sementara) — mengisi field override di `ra_assignments` yang menimpa hasil lookup otomatis untuk unit spesifik itu saja, tanpa mengubah mapping cabang secara permanen. Setiap override ini **wajib otomatis tercatat** ke Change Log (§3B.8).

**Aksi**: Lihat detail (klik baris → tampilkan histori assignment unit ini kalau ada), Set Override Manual, Filter per RA/status.

---

### 3B.4 Coverage Offsite

#### 3B.4.a Setup Fungsi Unit
**Entitas**: `coverage_setup` (§3.10)
**Tujuan**: tentukan fungsi apa saja yang aktif di tiap unit.

**Tampilan utama**: tabel unit × 8 area, tiap sel **dropdown editable** (Ya/Tidak/Event):
Teller/Kas, CS/DPK, Kredit, ATM, Biaya/Jurnal, APU/FDS, TI Event, Pengaduan/Aset.

**Fitur**: 2 kolom (Kredit, ATM) punya **nilai default otomatis** saat unit baru dibuat (berdasarkan jenis unit — lihat §4.7), tapi tetap bisa dioverride manual di sini kapan saja.

#### 3B.4.b Detail per Data Code
**Entitas**: `coverage_summary` + `coverage_detail` (§3.11, §3.13) — **digabung, drill-down**
**Tujuan**: lihat skor kelengkapan coverage per unit, lalu drill-down ke 19 data code.

**Tampilan utama (level 1 — daftar unit)**:
| Kolom | Isi |
|---|---|
| Unit | — |
| Jumlah Area Aktif | X dari 8 |
| Coverage Score | persentase |
| Status Coverage | badge: Lengkap / Cukup / Perlu Lengkapi Setup / Nonaktif |

Semua **read-only** (computed dari Setup Fungsi Unit).

**Klik unit → level 2 (detail 19 data code)**: tabel data code, area, mode coverage final (H+1/Event-based/Onsite-Periodik/Tidak), dan flag masuk SOP02/SOP04 — semua **read-only**.

---

### 3B.5 Jadwal Onsite

#### 3B.5.a Frekuensi per Unit
**Entitas**: `onsite_frequency` (§3.15)
**Tujuan**: lihat & override frekuensi kunjungan onsite.

**Tampilan utama**: tabel unit dengan kolom:
| Kolom | Editable? |
|---|---|
| Kategori Risiko | Read-only |
| Frekuensi Otomatis | Read-only |
| Override Manual | **Editable** — dropdown **hanya 5 pilihan**: Bulanan, Triwulanan, Semesteran, Tahunan, Tidak Terjadwal |
| Frekuensi Final | Read-only (override jika ada, else otomatis) |
| Dasar Penetapan | Read-only, teks otomatis |

**Catatan UI penting**: untuk unit `unit_type = KC`, tampilkan badge khusus **"Resident Daily Review"** alih-alih angka "0x/tahun" polos — supaya user tidak salah kira KC "tidak diawasi" (lihat §6.6).

#### 3B.5.b Kalender Kunjungan
**Entitas**: `scheduled_visits` (§3.16)
**Tujuan**: lihat & kelola jadwal kunjungan onsite riil.

**Tampilan utama**: **tampilan kalender visual** (bulan/minggu), bukan tabel mentah seperti Excel — tiap kunjungan muncul sebagai event dengan nama unit, RA, durasi.

| Field per kunjungan | Editable? |
|---|---|
| Unit, RA, Kunjungan ke- | Read-only |
| Tanggal Mulai/Selesai Otomatis | Read-only |
| Override Tanggal Manual | Editable (date picker) |
| Tanggal Final | Read-only (override jika ada) |
| **Status** | **Editable — dropdown 5 pilihan**: Planned, In Progress, Completed, Postponed, Cancelled |
| Catatan | Editable |

**Fitur**: filter per RA/bulan/status; drag-and-drop reschedule (opsional, langsung update override tanggal); klik event → detail unit lengkap.

---

### 3B.6 Kapasitas RA
**Entitas**: `ra_capacity` (§3.17)
**Tujuan**: pantau beban kerja tiap RA per bulan.

**Tampilan utama**: tabel/chart per RA × 12 bulan.
| Kolom | Isi |
|---|---|
| RA | — |
| Bulan | — |
| Jumlah Unit Daily Offsite | — |
| Estimasi Hari Offsite | — |
| Jumlah & Total Hari Kunjungan Terjadwal | — |
| Total Beban Hari | — |
| Utilisasi (%) | — dengan progress bar visual |
| Status Kapasitas | badge: OK (hijau) / Warning (kuning) / Over Capacity (merah) |

Semua **read-only** (full computed). **Fitur**: notifikasi otomatis ke Bagian RA kalau ada RA berstatus "Over Capacity"; klik RA → lihat breakdown unit mana saja yang berkontribusi ke beban.

---

### 3B.7 Final Audit Plan
**Entitas**: `final_audit_plan` (§3.18)
**Tujuan**: output resmi rencana audit tahunan, siap di-approve.

**Tampilan utama**: tabel semua unit, gabungan info dari semua modul sebelumnya (RA, kategori risiko, coverage, frekuensi, status).

**Fitur**: tombol **"Approve"** per unit atau bulk-approve; unit dengan `plan_status = "Draft - Lengkapi Mapping RA"` tidak bisa di-approve sampai Assignment RA-nya dibereskan (validasi blocking); **export ke PDF/Excel** untuk dibagikan ke manajemen.

---

### 3B.8 Change Log
**Entitas**: `change_log` (§3.19)
**Tujuan**: audit trail seluruh perubahan manual di modul ini.

**Tampilan utama**: tabel read-only, terisi **otomatis** setiap ada perubahan di Trigger Darurat, override manual (frekuensi/kalender/assignment), atau perubahan di Pengaturan Modul — user tidak mengetik langsung ke sini kecuali menambahkan alasan tambahan.

| Kolom | Isi |
|---|---|
| Tanggal, Area/Sheet, Unit | — |
| Deskripsi Perubahan | — |
| Alasan | Editable saat pertama kali dicatat (form kecil muncul otomatis begitu user melakukan override di modul lain) |
| Disetujui Oleh | — |
| **Status** | **Dropdown 4 pilihan**: Draft, Approved, Rejected, Implemented |

**Fitur**: filter per unit/tanggal/area; ini menu yang paling penting untuk kepatuhan/audit eksternal, jadi pastikan **tidak ada cara menghapus entri** (append-only).

---

### 3B.9 Pengaturan Modul
**Entitas**: `master_setup` + `field_weights` + `frequency_matrix` + `data_codes` + `ras` + `branch_ra_mapping` (§3.3, §3.7, §3.8, §3.12, §3.14)
**Akses**: **Admin only** — submenu ini disembunyikan sepenuhnya dari role selain Admin (bukan cuma di-disable).

**Tampilan utama**: beberapa tab konfigurasi:
- Tab **Bobot Skoring**: 26 bobot indikator (Lampiran A.1) + 6×5 tabel bobot bidang-ke-skor-final per jenis unit (Lampiran A.4) — semua editable, **tapi ubah ini WAJIB otomatis masuk Change Log** karena dampaknya besar ke semua unit sekaligus
- Tab **Ambang Kategori Risiko**: 5 rentang skor (Lampiran A.5) — editable
- Tab **Matriks Frekuensi**: 5×4 tabel (Lampiran A.3) — editable
- Tab **Daftar RA & Mapping Cabang**: CRUD daftar RA + mapping ke cabang (Lampiran A.7) — editable
- Tab **Data Code**: CRUD 19 data code + area + mode default (Lampiran A.2) — editable
- Tab **Parameter Kalender & Kapasitas**: tahun audit plan, durasi kunjungan, hari kerja efektif, ambang warning (Lampiran A.6) — editable

**Peringatan khusus untuk tab Bobot Skoring**: berdasarkan temuan §6.4 dan §A.4, di file Excel sumber ada 2 tabel bobot "fosil" (9-komponen dan Faktor Skor poin-absolut) yang tidak pernah dipakai. **Jangan tampilkan kedua tabel fosil ini di UI Pengaturan** kecuali sudah dikonfirmasi ke pemilik SOP mau dipakai — kalau ditampilkan berdampingan dengan tabel yang aktif, ini bisa membingungkan Admin baru yang tidak tahu mana yang benar-benar berpengaruh ke kalkulasi.

---

### 4.1 Kategori Volume Transaksi (di `units`, computed)
```
IF risk_component_scores.total >= 70 THEN "Tinggi"
ELSE IF >= 40 THEN "Sedang"
ELSE "Rendah"
```

### 4.2 Skor Komponen per Bidang (6 skor, 0-100)
Untuk setiap bidang X, dengan indikator-indikator raw metric di bidang itu (`m1, m2, ...`) dan bobotnya (`w1, w2, ...` dari `field_weights`):

```
skor_bidang_X = MIN(100, Σ(m_i × w_i))
```

Jika `unit_type` tidak relevan untuk bidang tersebut (contoh: Payment Point untuk bidang Kredit), maka:
```
skor_bidang_X = 0
```

**Contoh nyata (bidang Kredit, unit KC):**
```
skor_kredit = MIN(100, (jumlah_debitur_kol_3_5 × 0.35) + (rasio_npl × 0.40) + (deviasi_kredit × 0.25))
```

### 4.3 Skor Weighted Final (gabung 6 bidang jadi 1 angka)
Bobot bidang berbeda per `unit_type` (lihat Lampiran A.4 untuk semua kombinasi):
```
skor_final = Σ(skor_bidang_i × bobot_bidang_i_untuk_jenis_unit_ini)
```
Bobot untuk KC: Riwayat RA 20%, Kas/Teller 15%, CS/DPK 15%, Kredit 25%, TI/ATM 10%, Monitoring TL 15%. **(Bobot untuk jenis unit lain berbeda — lihat Lampiran A.4, JANGAN diasumsikan sama.)**

### 4.4 Kategori Risiko Awal
```
skor_final ≤ 20        → "Low"
20 < skor_final ≤ 40    → "Low to Moderate"
40 < skor_final ≤ 60    → "Moderate"
60 < skor_final ≤ 80    → "Moderate to High"
skor_final > 80         → "High"
```

### 4.5 Override & Kategori Final
```
has_active_override = EXISTS critical_overrides WHERE unit_code = X AND status = "Aktif"
kategori_final = IF has_active_override THEN "High" ELSE kategori_awal
priority_rank = MAP(kategori_final): High=1, Moderate to High=2, Moderate=3, Low to Moderate=4, Low=5
```

### 4.6 Assignment RA (rantai 2 lookup, TANPA logika risiko)
```
base_branch = units.base_ra_unit  (WHERE unit_code = X)
primary_ra  = branch_ra_mapping.primary_ra_id WHERE branch_name = base_branch
backup_ra   = branch_ra_mapping.backup_ra_id WHERE branch_name = base_branch  (bisa NULL)
```

### 4.7 Coverage Setup Default (boleh dioverride manual)
```
kredit_flag = "Ya" jika unit_type IN (KC, KCP) ELSE "Event"
atm_flag    = "Ya" jika unit_type = KC ELSE "Event"
(6 area lain tidak punya default otomatis — full manual)
```

### 4.8 Coverage Summary & Score
```
area_status = MAP(flag): Ya→"H+1", Event→"Event-based", Tidak→"Tidak"
active_area_count = COUNT(area_status IN ["H+1","Event-based"])
coverage_score = active_area_count / 8
coverage_status = IF score=1 THEN "Lengkap" ELSE IF score≥0.75 THEN "Cukup" ELSE "Perlu Lengkapi Setup"
```

### 4.9 Coverage Detail per Data Code
```
FOR EACH unit × EACH of 19 data_codes:
    IF data_codes.daily_offsite_capable = "Tidak":
        final_mode = "Onsite/Periodik"
    ELSE:
        final_mode = coverage_summary.area_status[data_codes.area]  (H+1 / Event-based / Tidak)
    enters_sop02 = final_mode IN ["H+1", "Event-based"]
    enters_sop04 = final_mode = "Onsite/Periodik"
```

### 4.10 Frekuensi Onsite Otomatis
```
label = frequency_matrix[kategori_risiko_final][unit_type]
kali_per_tahun = frequency_label_to_number(label)
```
**ATURAN KHUSUS:** jika `unit_type = KC`, `kali_per_tahun` SELALU 0 dan label SELALU mengandung "Resident Daily Review" — tandai unit ini dengan flag terpisah `is_resident_daily_review = true`, JANGAN diperlakukan sebagai "0 kunjungan = tidak diawasi".

### 4.11 Override Frekuensi & Helper Penjadwalan
```
frekuensi_final = IF manual_override IS NOT NULL THEN manual_override ELSE auto_frequency
kali_tahun_final = frequency_label_to_number(frekuensi_final)

# Helper untuk penjadwalan (running total lintas SEMUA unit, urutan sesuai urutan di Audit Universe):
cumulative_visits[i] = SUM(kali_tahun_final[1..i])
visit_sequence_start[i] = cumulative_visits[i] - kali_tahun_final[i] + 1
```

### 4.12 Generate Jadwal Kunjungan (unroll per kunjungan)
```
FOR EACH unit WHERE kali_tahun_final > 0:
    FOR visit_number = 1 TO kali_tahun_final:
        recommended_month = MAP(frekuensi_final, visit_number):
            "Bulanan"     → visit_number
            "Triwulanan"  → visit_number × 3
            "Semesteran"  → visit_number × 6
            "Tahunan"     → 6  (tetap, pertengahan tahun)
        duration_days = LOOKUP durasi default per tipe frekuensi (parameter kalender)
        start_day_of_month = MIN(24, 3 + (global_visit_index MOD 5) × 4)   # sebar: 3,7,11,15,19,23,ulang
        auto_start = DATE(tahun_audit_plan, recommended_month, start_day_of_month)
        auto_end   = auto_start + duration_days - 1
        final_start = manual_override_start ?? auto_start
        final_end   = manual_override_end ?? auto_end
        status = "Planned"  (default; harus bisa diupdate manual di aplikasi riil)
```

### 4.13 Kapasitas RA per Bulan
```
FOR EACH ra × EACH month (1-12):
    daily_offsite_unit_count = COUNT units WHERE primary_ra = ra 
                                 AND EXISTS coverage_detail WHERE final_mode IN ["H+1","Event-based"]
    estimated_offsite_days = daily_offsite_unit_count × effort_per_unit_per_month  (parameter, default 1)
    scheduled_visit_days = SUM(scheduled_visits.final_duration_days) 
                             WHERE primary_ra = ra AND month(final_start_date) = month AND status ≠ "Cancelled"
    total_workload_days = estimated_offsite_days + scheduled_visit_days
    utilization = total_workload_days / effective_working_days  (parameter, default 20)
    capacity_status = IF utilization > 1.0 THEN "Over Capacity"
                       ELSE IF utilization > warning_threshold (default 0.85) THEN "Warning"
                       ELSE "OK"
```

### 4.14 Final Audit Plan (gabungan output)
```
daily_offsite_active = "Ya" jika ADA minimal 1 area coverage_summary berstatus "Aktif" untuk unit ini, else "Tidak"
is_resident_daily_review = "Ya" jika unit_type = KC else "Tidak"
risk_trigger_visit_required = "Ya" jika kategori_final = "High" else "Jika Trigger"
plan_status = IF primary_ra IS NULL OR notes = "Perlu Mapping RA" 
                THEN "Draft - Lengkapi Mapping RA" 
                ELSE "Approved"
notes = IF frekuensi_final = "Tidak Terjadwal" 
          THEN "Offsite H+1 tetap wajib; onsite hanya trigger" 
          ELSE "Plan otomatis dari SOP 01"
```

---

## 5. DASHBOARD / RINGKASAN

Modul dashboard menampilkan agregat real-time:
- Total unit, jumlah per jenis unit (KC/KCP/KCPLK/Payment Point)
- Distribusi kategori risiko (COUNT per kategori: High, Moderate to High, Moderate, Low to Moderate, Low)
- Distribusi frekuensi onsite (COUNT per label: Bulanan, Triwulanan, dst)
- Status plan (COUNT Approved vs Draft/Perlu Mapping)

Semua ini adalah **agregasi (COUNT/GROUP BY)** dari entitas di atas — tidak ada logika baru, murni query ringkasan.

---

## 6. ATURAN BISNIS KHUSUS & CATATAN PENTING (JANGAN SAMPAI TERLEWAT)

### 6.1 Perbedaan konsep "temuan riwayat" vs "kejadian berjalan" vs "tindak lanjut"
- **Bidang Riwayat Pemeriksaan RA** = temuan resmi (sudah lewat proses audit, sudah closed) dari periode SEBELUMNYA — sumber: RA saja.
- **5 bidang operasional lain** = kejadian/anomali periode BERJALAN yang sedang dinilai — sebagian mentah, sebagian sudah terkonfirmasi.
- **Bidang Monitoring Tindak Lanjut** = bukan soal jumlah temuan, tapi soal KEPATUHAN menindaklanjuti temuan (dari 4 sumber: RA, SKAI, Regulator, KAP) — mengukur kecepatan & kualitas respons, bukan keparahan masalah.

### 6.2 Backup RA banyak yang kosong (data quality issue di file asli)
Hanya cabang **Luwuk** dan **Kantor Pusat** yang punya Backup RA terisi di data sumber. Semua cabang lain (Sigi, Buol, Salakan, Banggai Laut, Parigi, Palu Barat, Poso, Taweli, Cabang Utama, Tolitoli, Jakarta, Kolonodale, Bungku, Ampana) **tidak punya backup RA**. Aplikasi harus punya validasi/warning eksplisit untuk kondisi ini, bukan menampilkan "0" begitu saja.

### 6.3 Inkonsistensi tahun di file sumber
Sheet Assignment RA hardcode periode berlaku 2026, tapi parameter "Tahun Audit Plan" di Master Setup (dipakai kalender & kapasitas) = 2027. Saat membangun aplikasi, pastikan **satu sumber kebenaran** untuk tahun berjalan (satu field konfigurasi, dipakai konsisten di semua modul), tidak hardcode terpisah-pisah seperti file asli.

### 6.4 Data/tabel yang ternyata TIDAK dipakai formula manapun di file sumber (dead reference)
- Baris 94-97 di Raw Metrics (label "bobot per jenis unit") — tidak direferensikan formula manapun.
- Tabel "Faktor Skor" di Master Setup kolom R-T (daftar 15 raw metric dengan poin per kejadian, misal "NPL 5% = 30 poin") — juga tidak direferensikan formula manapun.

Kedua tabel ini kemungkinan sisa dari desain versi sebelumnya, atau dimaksudkan untuk metodologi skoring alternatif yang belum/tidak jadi dipakai. **Jangan diimplementasikan sebagai logika aktif** kecuali dikonfirmasi ke pemilik SOP bahwa memang harus dipakai — kalau iya, ini bisa jadi metodologi skoring ALTERNATIF (skor absolut per kejadian, bukan proporsional) yang perlu diklarifikasi dulu cara pakainya sebelum dikodekan.

### 6.5 Dua sheet yang disebut di dokumentasi tapi tidak ada di file
`02B_Scoring_Guide` dan `02C_Rule_Parameter` disebut di catatan/instruksi (00_Petunjuk dan beberapa formula lain) sebagai referensi definisi indikator & rule deteksi anomali, tapi **tidak ada** di 17 sheet yang tersedia. Perlu klarifikasi ke sumber SOP asli apakah sheet ini pernah ada dan hilang, atau memang belum dibuat — karena definisi rule deteksi anomali (misal kapan sebuah biaya dianggap "tidak lazim") kemungkinan ada di sana dan belum tertangkap di rekonstruksi ini.

### 6.6 KC tidak sama dengan "tidak terjadwal"
Sudah dibahas di §4.10 — **jangan sampai** logic aplikasi salah menafsirkan `kali_per_tahun = 0` untuk KC sebagai "unit tidak diawasi". KC justru unit dengan pengawasan PALING intensif (harian, resident) — hanya saja tidak dihitung sebagai "kunjungan terjadwal diskrit" dalam skema kali/tahun.

### 6.7 Assignment RA murni geografis
Ditegaskan lagi karena penting: assignment RA di desain asli **tidak mempertimbangkan** skor risiko unit atau beban kerja RA. Kalau di aplikasi baru ingin membuat logic assignment yang mempertimbangkan `ra_capacity` (hindari assign unit baru ke RA yang sudah "Over Capacity"), ini sah-sah saja sebagai **peningkatan** dari desain asli — tapi harus didokumentasikan sebagai deviasi yang disengaja, bukan bug.

---

## 7. NON-FUNCTIONAL REQUIREMENTS

1. **Audit trail wajib**: setiap perubahan pada Master Setup, Critical Override, dan override manual (frekuensi/tanggal onsite) harus otomatis tercatat di `change_log` — bukan opsional.
2. **Recompute otomatis**: begitu `raw_metrics` sebuah unit diubah, seluruh chain turunan (skor komponen → skor final → kategori → frekuensi → kalender → kapasitas → final plan) untuk unit itu harus ter-recompute — idealnya reaktif (event-driven), bukan manual refresh.
3. **Histori/periode**: workbook Excel sumber cuma menyimpan 1 snapshot per unit. Aplikasi seharusnya menyimpan histori per periode (tahun/triwulan) supaya bisa melihat tren risiko unit dari waktu ke waktu — ini peningkatan penting dibanding versi Excel.
4. **Validasi input**: raw metrics harus non-negatif; rasio (NPL) harus 0-1; tanggal override tidak boleh di masa lalu untuk status "Planned".
5. **Role-based access**: input Raw Metrics & Critical Override (Bagian RA/RA), approval override jadwal (PUK), edit Master Setup (Admin) — pisahkan permission per role.
6. **Notifikasi**: unit dengan `capacity_status = "Over Capacity"` atau `plan_status = "Draft - Lengkapi Mapping RA"` sebaiknya memicu notifikasi ke Bagian RA.

---

## 8. REKOMENDASI PENINGKATAN DIBANDING VERSI EXCEL (opsional, didiskusikan dulu)

Ini BUKAN bagian dari spesifikasi wajib — hanya usulan yang sah didiskusikan sebelum diimplementasikan, karena mengubah perilaku dari desain asli:

1. Assignment RA yang mempertimbangkan `ra_capacity` (hindari over-assign ke RA yang sudah penuh).
2. Satu sumber kebenaran untuk "tahun berjalan" (hindari inkonsistensi seperti §6.3).
3. Penyimpanan historis Raw Metrics per periode, bukan overwrite.
4. Status kunjungan (`scheduled_visits.status`) yang benar-benar bisa diupdate (Planned → Ongoing → Selesai/Cancelled), bukan hardcode.
5. Validasi eksplisit untuk unit tanpa Backup RA (§6.2), ditampilkan sebagai warning di dashboard.
6. Klarifikasi & keputusan resmi soal tabel "Faktor Skor" dan bobot baris 94-97 yang tidak terpakai (§6.4) — pakai atau hapus, jangan biarkan ambigu.

---

## LAMPIRAN A — NILAI PARAMETER LENGKAP (dari 12_Master_Setup)

### A.1 Bobot Indikator dalam Bidang (26 indikator, dari 02A_Raw_Metrics baris 93)

| Bidang | Indikator | Bobot |
|---|---|---|
| Riwayat RA | Temuan Onsite Tahun Lalu | 0.15 |
| Riwayat RA | Temuan Signifikan | 0.20 |
| Riwayat RA | Temuan Berulang | 0.15 |
| Riwayat RA | Penyimpangan Pada Offsite | 0.10 |
| Riwayat RA | Penyimpangan Offsite Signifikan | 0.10 |
| Riwayat RA | Penyimpangan Offsite Berulang | 0.05 |
| Riwayat RA | Lama Sejak Onsite (Bulan) | 0.25 |
| Kas/Teller | Transaksi Reversal/Koreksi | 0.25 |
| Kas/Teller | Selisih Kas | 0.30 |
| Kas/Teller | Biaya/Jurnal Tidak Lazim | 0.25 |
| Kas/Teller | Transaksi Tunai Besar Berisiko | 0.20 |
| CS/DPK | Anomali Pengelolaan DPK | 0.35 |
| CS/DPK | Pengaduan Nasabah Overdue | 0.25 |
| CS/DPK | Pengkinian Data/CDD-EDD Belum Selesai | 0.40 |
| Kredit | Jumlah Debitur Kol 3-5 | 0.35 |
| Kredit | Rasio NPL | 0.40 |
| Kredit | Penyimpangan/Deviasi Kredit | 0.25 |
| TI/ATM | Selisih/Dispute ATM | 0.30 |
| TI/ATM | Downtime ATM (Jam) | 0.20 |
| TI/ATM | Insiden TI Kritikal | 0.30 |
| TI/ATM | Reset/Buka Blokir User Tidak Lazim | 0.20 |
| Monitoring TL | Temuan RA Onsite Overdue | 0.15 |
| Monitoring TL | Temuan RA Offsite Overdue | 0.10 |
| Monitoring TL | Temuan SKAI Overdue | 0.20 |
| Monitoring TL | Temuan Regulator Overdue | 0.25 |
| Monitoring TL | Temuan KAP Overdue | 0.10 |
| Monitoring TL | Rata-Rata Hari Respons TL | 0.10 |

*(Catatan: kolom "Kualitas Respons dan Bukti TL" adalah checklist, tidak punya bobot numerik di baris 93 — perlakukan sebagai indikator kualitatif terpisah, klarifikasi cara skoringnya ke pemilik SOP.)*

### A.2 Daftar 19 Data Code (dari Master Setup kolom G-K)

| Data Code | Area | Daily Offsite? | Mode Default |
|---|---|---|---|
| KAS_POSISI | Teller/Kas | Ya | H+1 |
| REVERSAL | Teller/Kas | Ya | H+1 |
| OVERRIDE | Teller/Kas | Ya | H+1 |
| SELISIH_KAS | Teller/Kas | Ya | H+1 |
| BIAYA_HARIAN | Biaya/Jurnal | Ya | H+1 |
| JURNAL_MANUAL | Biaya/Jurnal | Ya | H+1 |
| PENCAIRAN_KREDIT | Kredit | Ya | H+1 |
| EXCEPTION_KREDIT | Kredit | Ya (jika ada) | H+1 jika ada |
| PERUBAHAN_DATA | CS/DPK | Ya | H+1 |
| DORMANT_AKTIF | CS/DPK | Ya | H+1 |
| KARTU_ATM_GANTI | CS/DPK | Ya | H+1 |
| ATM_SELISIH_DISPUTE | ATM | Ya | H+1 |
| APU_FDS_ALERT | APU-PPT/FDS | Ya | H+1 |
| TUNAI_BESAR_BERISIKO | APU-PPT/FDS | Ya | H+1 |
| TI_EVENT | TI Event | Ya | Event-based |
| PENGADUAN_BARU | Pengaduan | Ya | H+1 |
| ASET_FISIK | Aset | **Tidak** | **Onsite** |
| DOKUMEN_AGUNAN | Dokumen/Agunan | **Tidak** | **Onsite** |
| CCTV_ALARM | TI Fisik | **Tidak** | **Onsite** |

### A.3 Matriks Frekuensi Onsite (5 Kategori Risiko × 4 Jenis Unit)

| Kategori Risiko | KC | KCP | KCPLK | Payment Point |
|---|---|---|---|---|
| High | Resident Daily Review + Trigger | Bulanan (12x) | Bulanan (12x) | Bulanan (12x) |
| Moderate to High | Resident Daily Review | Triwulanan (4x) | Triwulanan (4x) | Triwulanan (4x) |
| Moderate | Resident Daily Review | Semesteran (2x) | Semesteran (2x) | Semesteran (2x) |
| Low to Moderate | Resident Daily Review | Tahunan/Semesteran (1x) | Tahunan/Tidak Terjadwal (0x) | Tahunan/Tidak Terjadwal (0x) |
| Low | Resident Daily Review | Tidak Terjadwal (0x) | Tidak Terjadwal (0x) | Tidak Terjadwal (0x) |

Konversi label → hari durasi kunjungan: Bulanan=2 hari, Triwulanan=5 hari, Semesteran=7 hari, Tahunan=12 hari.

### A.4 Bobot 6 Bidang ke Skor Final (per jenis unit) — INI YANG DIPAKAI KALKULASI

**Sumber kebenaran yang benar-benar hidup**: nilai ini diambil langsung dari formula `SWITCH()` di kolom D-I sheet `03_Risk_Scoring` (bobotnya di-hardcode literal di dalam formula, bukan hasil VLOOKUP ke tabel manapun). Dicek juga cocok 100% dengan salinan dokumentasinya di baris 94-97 sheet `02A_Raw_Metrics`.

| Bidang | KC | KCU | KCP | KCPLK | Payment Point |
|---|---|---|---|---|---|
| Riwayat Pemeriksaan RA | 20% | 20% | 20% | 25% | 20% |
| Kas/Teller | 15% | 15% | 20% | 30% | 50% |
| CS/DPK/APU-PPT | 15% | 15% | 15% | 20% | 0% |
| Kredit | 25% | 25% | 25% | 0% | 0% |
| TI/ATM | 10% | 10% | 5% | 5% | 5% |
| Monitoring TL | 15% | 15% | 15% | 20% | 25% |
| **Total** | 100% | 100% | 100% | 100% | 100% |

*(Formula juga menyediakan cabang untuk kode jenis unit "KCU" dan "PP" sebagai **alias**: `KCU` selalu mendapat bobot identik persis dengan `KC` di keenam bidang — bukan kategori terpisah. `PP` selalu mendapat bobot identik persis dengan `Payment Point` — juga alias, bukan kategori terpisah.

`KP` **BUKAN alias dari Payment Point** meski singkatannya mirip — ini kemungkinan besar singkatan **"Kantor Pusat"**, ditulis sebagai cabang `SWITCH` yang eksplisit dan terpisah, dengan bobot **0% di semua 6 bidang tanpa kecuali**. Ini disengaja (bukan sekadar jatuh ke default karena kode tidak dikenali) — artinya unit berjenis "KP" akan SELALU mendapat skor risiko weighted = 0, apapun data mentahnya. Kemungkinan Kantor Pusat memang sengaja dikecualikan dari skema skoring risiko unit cabang ini. Kode jenis unit lain yang benar-benar tidak dikenali (di luar 7 cabang eksplisit ini) jatuh ke default 0% lewat jalur berbeda — secara hasil angka sama-sama 0%, tapi secara desain artinya beda: "sengaja dikecualikan" vs "tidak dikenali sistem".)*

**✅ KLARIFIKASI FINAL soal ambiguitas "6 bidang vs 9 komponen" (sudah diverifikasi tuntas ke level formula, bukan dugaan lagi):**

Di Master Setup kolom A5:C13 ada TABEL LAIN berisi 9 baris komponen (Hasil Onsite Tahun Sebelumnya 20%, Hasil Offsite Tahun Sebelumnya 20%, Operasional Transaksi 15%, Kredit 15%, DPK/Layanan 10%, ATM 5%, APU-PPT/Fraud 5%, TI Event 5%, Tindak Lanjut dan Respons Unit 5% — total pas 100%). Setelah ditelusuri **setiap formula di seluruh 17 sheet** untuk mencari referensi ke sel `$A$`, `$B$`, atau `$C$` di sheet Master Setup — **hasilnya NOL, tidak ada satupun formula yang memakainya.**

Kesimpulan: tabel 9-komponen ini adalah **sisa rancangan versi sebelumnya (orphaned/fosil)**, bukan metodologi alternatif yang sedang aktif dipakai berdampingan. Skema yang **benar-benar berjalan** di seluruh sistem adalah skema **6 bidang** di atas. Kalau ada instruksi/ekspektasi yang mengacu ke 9 komponen ini (misalnya dari mentor atau dokumen SOP versi lain), itu kemungkinan besar sumber dari "ketidakkonsistenan" yang disebutkan — dan itu PR untuk diklarifikasi ke pemilik SOP, bukan sesuatu yang bisa ditebak dari file Excel ini sendiri.

### A.5 Ambang Batas Kategori Risiko

| Kategori | Skor Min | Skor Max |
|---|---|---|
| Low | 0 | 20 |
| Low to Moderate | 20.01 | 40 |
| Moderate | 40.01 | 60 |
| Moderate to High | 60.01 | 80 |
| High | 80.01 | 100 |

### A.6 Parameter Kalender & Kapasitas

| Parameter | Nilai |
|---|---|
| Tahun Audit Plan | 2027 |
| Durasi Onsite Bulanan | 2 hari |
| Durasi Onsite Triwulanan | 5 hari |
| Durasi Onsite Semesteran | 7 hari |
| Durasi Onsite Tahunan | 12 hari |
| Effort Daily Offsite per Unit per Bulan | 1 hari |
| Ambang Warning Utilisasi | 85% |
| Hari Kerja Efektif Default per Bulan | 20 hari |

### A.7 Daftar RA & Mapping Cabang (contoh dari data sumber — sesuaikan dengan data riil organisasi)

| RA ID | Nama | Cabang Basis | Backup RA |
|---|---|---|---|
| LWK-1 | Jilly Keshia Lambeto | Cabang Luwuk | LWK-2 |
| LWK-2 | Selvi R. Madina | Cabang Sigi | — (tidak konsisten di data sumber, cek ulang) |
| SGI-1 | Yuyun | Cabang Buol | — |
| BUOL-1 | Andika | Cabang Salakan | — |
| SLKN-1 | Lucky Haryanto L | Cabang Banggai Laut | — |
| BLT-1 | Moh. Rizal Abbas | Cabang Parigi | — |
| PRG-1 | Nur Santi Armatia | Cabang Palu Barat | — |
| PLB-1 | Mardudin | Kantor Pusat | KP-2 |
| KP-1 | Evawani A. Thayeb | Cabang Poso | — |
| PSO-1 | Yan Hamsah | Cabang Pembantu Taweli | — |
| TWL-1 | Risnandar Thayeb | Cabang Utama | KCU-2 |
| KCU-1 | Januar | Cabang Tolitoli | — |
| TLS-1 | Suparman | Cabang Jakarta | — |
| JKT-1 | Sri Fika Reski | Cabang Kolonodale | — |
| KDL-1 | Dedi Paris Djafar | Cabang Bungku | — |
| BGK-1 | Mastini | Cabang Ampana | — |
| AMP-1 | Treesya | (kosong di data sumber, unit terakhir) | — |

*(⚠️ Perhatikan pola di tabel sumber: kolom "Base Unit" RA [C] tampak tidak selalu match dengan kolom mapping "Base RA Unit → Primary RA" [M→N] pada baris yang sama — ini kemungkinan besar karena kedua tabel disusun berdampingan tapi TIDAK saling merujuk baris-ke-baris (masing-masing independen). WAJIB divalidasi ulang ke data RA riil organisasi sebelum dipakai produksi — jangan copy-paste tabel di atas mentah-mentah.)*

---

## 8B. METODOLOGI VERIFIKASI & BATASAN CAKUPAN DOKUMEN INI

Supaya transparan, berikut yang SUDAH dan BELUM diverifikasi saat menyusun dokumen ini:

**Sudah diverifikasi (dicek langsung ke file XML/data, bukan cuma dibaca sekilas):**
- Semua formula kunci di 17 sheet (dibaca sebagai teks formula DAN dihitung ulang manual mencocokkan angka hasil di file)
- Named ranges/defined names → kosong, tidak ada
- VBA/macro → tidak ada
- Cell comments → tidak ada di sheet manapun
- **Data validation (dropdown list) di semua sheet** → ditemukan di 8 sheet (01, 02_Risk_Input, 04, 06, 06A, 07, 08, 11) dan sudah dimasukkan ke §3 di atas
- Struktur tabel Excel (Table Objects) yang dipakai formula

**Belum/tidak diverifikasi baris-per-baris (disampel, bukan exhaustive):**
- Tidak semua 80 baris data unit di tiap sheet dicek satu-satu — verifikasi dilakukan dengan mengambil 2-3 unit sampel (Ampana/301, KCP Paleleh/211, Kantor Cabang Bungku) dan mencocokkan hasil formula ke angka aslinya. Kemungkinan ada baris data lain dengan kasus tepi (edge case) yang belum ketemu.
- Conditional formatting (pewarnaan otomatis sel) tidak ditelusuri — kalau ada aturan visual yang membawa makna bisnis tersembunyi (misal "merah = butuh perhatian"), itu belum tertangkap.
- Print settings, page setup, header/footer dokumen → tidak relevan untuk aplikasi, sengaja tidak dicek.
- Isi lengkap kata-per-kata sheet **00_Petunjuk** (28 baris instruksi) sudah dibaca sekali di awal percakapan, tapi tidak di-cross-check ulang detail terhadap setiap aturan di dokumen ini — ada kemungkinan kecil ada 1-2 catatan instruksi yang terlewat.
- Isi lengkap 91 baris data mentah di 02A_Raw_Metrics (angka riil semua 80 unit) tidak dianalisis satu-satu — dokumen ini fokus ke STRUKTUR dan LOGIKA, bukan mem-verifikasi kewajaran tiap angka data.

**Kesimpulan jujur:** dokumen ini mencakup **seluruh logika, struktur, formula, dan aturan bisnis** yang ada di file — itu yang paling penting untuk membangun aplikasi. Yang TIDAK tercakup penuh adalah verifikasi ke tingkat "satu per satu dari 80×26 sel data mentah", karena itu levelnya data quality checking, bukan spesifikasi sistem. Kalau kamu mau, aku bisa jalankan pengecekan tambahan (misal: cari baris yang datanya kosong/aneh, cek konsistensi semua 80 unit terhadap pola yang sudah ditemukan) sebagai langkah terpisah.

---

## 9. RINGKASAN PRIORITAS IMPLEMENTASI (jika dibangun bertahap)

1. **Fase 1 — Master data**: `units`, `ras`, `branch_ra_mapping`, `master_setup` (semua parameter Lampiran A)
2. **Fase 2 — Input & Scoring**: `raw_metrics` → `risk_component_scores` → `risk_scoring` → `critical_overrides`
3. **Fase 3 — Assignment & Coverage**: `ra_assignments`, `coverage_setup` → `coverage_summary` → `coverage_detail`
4. **Fase 4 — Penjadwalan**: `onsite_frequency` → `scheduled_visits` → `ra_capacity`
5. **Fase 5 — Output & Dashboard**: `final_audit_plan`, dashboard agregat, `change_log`

Setiap fase sebaiknya diverifikasi dengan cara membandingkan hasil kalkulasi aplikasi terhadap angka yang ada di file Excel sumber (gunakan unit "301 - Kantor Cabang Ampana" dan "211 - KCP Paleleh" sebagai kasus uji, karena sudah diverifikasi manual dalam proses penyusunan dokumen ini).

---

*Dokumen ini disusun berdasarkan analisis menyeluruh terhadap file `WP_SOP_01_RA_Audit_Plan.xlsx` (17 sheet, termasuk formula tersembunyi dan tabel referensi). Beberapa poin memerlukan konfirmasi ke pemilik SOP asli sebelum diimplementasikan sebagai logika final — semua ditandai eksplisit di §6 dan Lampiran A.4/A.7.*
