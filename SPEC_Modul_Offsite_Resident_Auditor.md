# SPESIFIKASI SISTEM — MODUL OFFSITE REVIEW (APLIKASI RESIDENT AUDITOR)

> Dokumen ini adalah rekonstruksi lengkap dari workbook Excel `WP_SOP_02_OFFSITE_V2_Stage4.xlsx` (SOP 02 — 18 sheet), diubah menjadi spesifikasi fungsional siap pakai untuk membangun Modul Offsite Review pada Aplikasi Resident Auditor. Dokumen ini melengkapi (bukan menggantikan) `SPEC_Modul_Audit_Plan_Resident_Auditor.md` — kedua modul terhubung (lihat §8).
>
> Gunakan dokumen ini sebagai prompt/brief ke tim developer atau ke AI coding assistant untuk membangun modul ini dari nol.

---

## 1. RINGKASAN SISTEM

### 1.1 Tujuan Modul
Modul Offsite Review adalah **mesin deteksi anomali transaksi otomatis** yang berjalan **harian**. RA meng-upload data transaksi mentah dari core banking system (CBS), sistem otomatis membaca narasi/deskripsi tiap transaksi, mendeteksi pola risiko lewat rule berbasis kata kunci, mengklasifikasikan level risiko, dan menyaring transaksi mana yang perlu direview manual oleh RA (dituangkan dalam Kertas Kerja Audit / KKA).

Ini adalah implementasi dari **SOP 02 — Daily Offsite Review** yang sebelumnya disebut sebagai referensi (tapi belum ada filenya) di dalam SOP 01 (Audit Plan) — sekarang closure-nya lengkap.

### 1.2 Prinsip Desain Kunci
- **Deteksi berbasis teks, bukan kategori kaku.** Sistem membaca narasi/deskripsi transaksi (bukan cuma kode jenis transaksi) untuk mendeteksi pola mencurigakan lewat pencocokan kata kunci (`SEARCH()`).
- **3 jenis rule terpisah**: Risk Trigger (memicu kecurigaan), Classification (kategorisasi rutin), Whitelist (pengecualian transaksi rutin yang sah, untuk mengurangi false positive).
- **Tidak semua transaksi direview manual.** High/Moderate risk → semua direview. Low risk → hanya **sampel bertingkat** (stratified sampling) yang direview, sisanya tidak masuk KKA.
- **Siklus kerja harian, periode laporan bulanan.** RA upload & review tiap hari (pola H+1: transaksi hari H direview di hari H+1), tapi 1 Kertas Kerja Audit (WP) mencakup 1 bulan penuh.
- **1 WP = 1 unit kerja (KCP/KCPLK) = 1 periode.** Sistem didesain per unit per bulan, bukan multi-unit sekaligus dalam 1 WP.

### 1.3 Alur Data End-to-End

```
[1] UPLOAD CSV (5 jenis data mentah, harian)
        │
        ▼
[2] MESIN DETEKSI (per baris: baca narasi → cocokkan rule → flag risiko)
        │
        ▼
[3] STAGING (gabung 5 sumber, validasi unit & periode, seragamkan kolom)
        │
        ├──────────────────────┬─────────────────────
        ▼                      ▼
[4] REGISTER HARIAN        [5] KKA PER AREA (7 sheet)
    (checklist harian,         (RA kerja detail: Dampak,
     radar/triase)              Kemungkinan, Simpulan)
        │                      │
        └──────────┬───────────┘
                    ▼
        [6] DASHBOARD OFFSITE (ringkasan real-time)
                    │
                    ▼
        [7] STATUS WP: Draft → Aktif → Final
                    │
                    ▼
        [8] FEED BACK ke raw_metrics Modul Audit Plan (periode berikutnya)
```

Paralel dengan alur ini: **Master_Parameter** adalah sumber semua rule deteksi, threshold, parameter sampling, dan identitas WP yang sedang aktif — dipakai di hampir semua sheet.

### 1.4 Role & Hak Akses (WAJIB dibaca sebelum §10)

Modul ini punya **2 role**: **RA** (pelaksana lapangan) dan **Admin/Reviewer** (pusat/atasan). Pembagian ini bukan "RA upload, Admin lihat" — keduanya **sama-sama aktif kerja**, cuma beda jenis pekerjaannya. RA melakukan pekerjaan audit (upload + investigasi + isi kesimpulan), Admin/Reviewer melakukan pengawasan + approval + pengaturan sistem.

**Definisi peran:**
- **RA** — 1 akun RA terhubung ke 1 (atau beberapa) unit basis, sesuai mapping `base_ra_unit` di Modul Audit Plan (§3.7-3.9 dokumen SPEC Audit Plan). RA HANYA bisa akses data unit yang jadi tanggung jawabnya.
- **Admin/Reviewer** — 1 role gabungan: meninjau & approve pekerjaan RA (fungsi "Reviewer Bagian RA") SEKALIGUS mengelola konfigurasi sistem (fungsi "Admin"). Bisa akses SEMUA unit, lintas RA.

**Matriks akses per menu:**

| Menu | RA | Admin/Reviewer |
|---|---|---|
| Upload Data | ✅ Full — upload CSV untuk unit tanggung jawabnya | ❌ Tidak ada akses (bukan tugasnya) |
| Register Harian | ✅ Full — lihat & edit (Status Review, Tanggal Aktual Review, Catatan RA) untuk unit sendiri | 👁️ Lihat semua unit/RA (read-only) — dashboard monitoring progress, TIDAK edit field RA |
| KKA per Area (7 sub) | ✅ Full — isi semua field kuning (Bukti, Hasil Uji, Dampak, Kemungkinan, Critical Trigger, Klarifikasi, Keputusan, Simpulan) untuk unit sendiri | 👁️ Lihat semua unit | ✏️ Edit HANYA field `Catatan Reviewer` — tidak boleh ubah field milik RA |
| Dashboard Offsite | 👁️ Lihat progress unit sendiri saja | 👁️ Lihat semua unit, semua RA, agregat lintas cabang |
| Status WP: Draft → Aktif | ✅ RA yang memulai (submit WP jadi Aktif setelah mulai kerja) | — |
| Status WP: Aktif → Final | ❌ Tidak bisa (butuh approval) | ✅ Hanya Admin/Reviewer yang bisa naikkan ke Final, setelah cek semua KKA rapi |
| Pengaturan Modul (Rule Engine, Threshold, Sampling, Template Prosedur Uji) | ❌ Tidak ada akses | ✅ Full — ini murni fungsi Admin sistem |

**Aturan scope data (penting untuk query/API):**
```
RA: WHERE kode_unit IN (unit-unit yang jadi tanggung jawab RA ini, dari mapping base_ra_unit)
Admin/Reviewer: TIDAK ada filter unit — akses semua data
```

**Field-level detail untuk KKA (siapa isi apa):**

| Kelompok Field | Siapa isi | Kapan |
|---|---|---|
| Bukti/Referensi, Hasil Uji, Dampak, Kemungkinan, Critical Trigger | RA | Saat proses investigasi |
| Klarifikasi Awal, Status Klarifikasi, Klarifikasi Unit | RA | Setelah RA hubungi unit terkait |
| Perluasan Sampel, Perlu Onsite, Keputusan Onsite | RA | Berdasarkan hasil investigasi |
| Simpulan RA, Tanggal Ditemukan | RA | Menutup 1 kasus |
| Status Review (di level KKA) | RA | Update terakhir saat kasus selesai diproses RA |
| **Catatan Reviewer** | **Admin/Reviewer** | Saat proses approval/peninjauan, terpisah dari kolom RA |
| Skor Risiko, Kategori Risiko Final, Eskalasi Awal, Rekomendasi Eskalasi | *(otomatis, tidak diisi siapapun — computed dari Dampak × Kemungkinan)* | — |

**Kalau RA bertugas di lebih dari 1 unit** (ingat dari Modul Audit Plan: 1 RA bisa pegang beberapa unit anak cabang) — dropdown pemilihan unit di halaman Upload Data dan filter di Register Harian harus otomatis dibatasi hanya ke unit-unit yang jadi tanggung jawab RA yang sedang login, BUKAN daftar seluruh unit di bank.



### 2.1 `units_reference` (referensi unit, dari Master_Parameter kolom A-D)
Daftar semua unit kerja (KC/KCP/KCPLK) — dipakai untuk dropdown pemilihan unit saat setup WP baru. **Entitas ini kemungkinan sama/terhubung dengan `units` di Modul Audit Plan** — pertimbangkan pakai tabel yang sama, jangan duplikasi.

### 2.2 `wp_offsite` (identitas WP aktif — 1 unit, 1 periode)
Representasi 1 "sesi kerja" Offsite Review. Field kunci (semua ini di Excel ada di Master_Parameter kolom E-F, jadi 1 workbook = 1 WP aktif; di aplikasi ini harus jadi tabel dengan banyak baris, 1 baris = 1 WP):

| Field | Sumber Excel | Keterangan |
|---|---|---|
| `kode_wp` | `Kode WP` (F14) | Format: `SOP02-{kode_unit}-{YYYYMM}`, contoh `SOP02-106-202606` |
| `kode_unit`, `nama_unit`, `jenis_unit`, `kantor_induk` | F3-F6 | Lookup dari `units_reference` |
| `periode_mulai`, `periode_selesai` | F7-F8 | Awal & akhir bulan periode WP |
| `ra_pelaksana` | F9 | RA yang mengerjakan |
| `reviewer_bagian_ra` | F10 | Reviewer/atasan yang approve |
| `status_wp` | F11 | Enum: **Draft, Aktif, Final** |
| `scope_wp` | F12 | Selalu "1 UNIT / 1 PERIODE" — desain tetap, tidak bervariasi |
| `validasi_unit` | F13 | Computed: "VALID" jika kode_unit & periode sudah lengkap diisi |

**Aturan bisnis penting**: SEMUA sheet lain (DUMP, STAGING, Register, KKA, Dashboard) mengacu ke 1 WP aktif ini. Di aplikasi multi-user, ini artinya **konteks kerja (unit + periode yang sedang dikerjakan) harus eksplisit di URL/session**, bukan variabel global seperti di Excel (yang cuma bisa 1 WP aktif per waktu).

### 2.3 `rule_engine` (referensi rule deteksi, dari Master_Parameter kolom G-I dst)
Tabel rule yang bisa diedit Admin tanpa perlu ubah kode program. 3 tipe:

| Rule Type | Fungsi | Contoh |
|---|---|---|
| **Risk Trigger** | Memicu flag kecurigaan | `RISK_REV_01`: keyword `"REV-"` |
| **Classification** | Kategorisasi transaksi rutin (bukan pemicu risiko) | `CLS_TLR_01`: keyword `"PENARIKAN TUNAI"` |
| **Whitelist** | Pengecualian — transaksi yang match pattern ini TIDAK dianggap exception meski match Risk Trigger lain | `WL_001`: keyword `"PB GAJI"` |

Field: `rule_id`, `rule_type` (enum 3 nilai di atas), `keyword_pattern` (teks yang dicari via `SEARCH()`, case-insensitive), `area_terkait` (opsional), `aktif` (boolean).

**Daftar rule lengkap ada di Lampiran A.1.**

### 2.4 `rule_threshold` (parameter numerik — batas nominal, dll)
Contoh: batas nominal "Tunai Besar" per jenis unit — disimpan sebagai tabel referensi terpisah (`Master_Parameter` kolom H19 dst), bukan hardcode di rule keyword.

### 2.5 `sampling_strata` (parameter sampling untuk Low Risk)
Field: `domain` (CBS/DPK/Kredit/Biaya/Pengaduan), `strata_name` (misal "Rekening Baru", "Perubahan Data"), `target_case` (jumlah sampel yang harus diambil per strata per periode, contoh: 5).

**Aturan bisnis**: transaksi Low Risk **TIDAK semua** masuk KKA. Sistem mengambil sampel sejumlah `target_case` per strata (stratified sampling), sisanya tetap tersimpan tapi tidak masuk antrian review manual. Lihat §4.6 untuk logika penuh.

### 2.6 `dump_transaksi_cbs`, `dump_dpk_apuppt`, `dump_kredit`, `dump_biaya_beban`, `dump_pengaduan` (5 tabel — data mentah hasil upload CSV)
Ini tabel tempat CSV RA di-upload masuk. Field detail (kolom mana yang dari CSV vs auto) ada di §5 (spesifikasi mapping CSV) dan §7 (aturan deteksi).

**Field umum yang ada di SEMUA 5 tabel** (selain field spesifik per domain):
- `kode_unit`, `tanggal_data` — konteks dasar
- `deskripsi_narasi` — **field paling penting**, sumber untuk mesin deteksi teks
- Field-field hasil deteksi otomatis (lihat §7): `flag_*` (boolean per pola), `risk_level` (High/Moderate/Low), `area_review`, `kka_sheet_tujuan`, `case_id`, `status_data_quality` (VALID / Salah Unit / Luar Periode)

### 2.7 `staging_offsite` (tabel gabungan — SATU tabel, bukan 2 seperti di Excel)
Lihat penjelasan detail di §6. Field kunci:

| Field | Keterangan |
|---|---|
| `staging_id` | PK |
| `tanggal_data` | Tanggal transaksi asli |
| `kode_unit`, `nama_unit`, `jenis_unit` | Konteks unit |
| `nama_ra` | RA yang menangani |
| `source_sheet` / `source_table` | DUMP mana asalnya (CBS/DPK/Kredit/Biaya/Pengaduan) |
| `object_id` | ID transaksi asli (No Rekening/No Referensi) |
| `case_id` | Untuk pairing transaksi berpasangan (lihat §7.6) |
| `data_code` | Kode transaksi asli |
| `area_review` | Teller/Kas, Kredit, Biaya/Internal, Transaksi Umum, Transfer/KU, Pengaduan |
| `deskripsi_narasi`, `nominal`, `user_maker` | Detail transaksi |
| `risk_level` | High/Moderate/Low (hasil deteksi) |
| `exception_awal`, `jenis_exception_awal` | Flag & alasan awal dari mesin |
| `sampel_low` | Boolean — apakah baris ini kepilih sebagai sampel (khusus Low risk) |
| `kka_sheet_tujuan` | KKA area mana yang jadi tujuan |
| `masuk_kka_final` | Boolean — apakah baris ini benar-benar tampil di KKA (High/Moderate = selalu Ya; Low = Ya hanya jika `sampel_low` = Ya) |
| `status_data_quality` | VALID / Salah Unit / Luar Periode |

**Field ini menggantikan 2 sheet Excel (STAGING_Raw_Normalized + STAGING_Offsite_Normalized) — di aplikasi cukup 1 tabel ini** (lihat §6 untuk alasan lengkap).

### 2.8 `register_harian` (checklist kerja harian RA)
1 baris = 1 tanggal × 1 area review. Di-generate otomatis untuk **setiap hari dalam periode WP × 6 area** (bukan cuma untuk hari yang ada datanya — hari kosong tetap muncul barisnya dengan status "Tidak Ada Data").

| Field | Read-only/Editable | Sumber |
|---|---|---|
| `tanggal_data`, `target_review_h1` (tanggal+1) | Read-only | Computed dari periode WP |
| `nama_ra`, `kode_unit`, `area_review` | Read-only | Dari WP aktif + siklus 6 area |
| `populasi_eligible`, `sampel_low`, `kka_final`, `exception`, `perlu_klarifikasi`, `perlu_eskalasi` | Read-only | `COUNT` dari `staging_offsite` di-filter tanggal+area+unit |
| `risiko_tertinggi` | Read-only | Level risiko tertinggi yang ditemukan hari itu |
| `hasil_awal` | Read-only | Label prioritas otomatis (lihat §4.7) |
| `kka_sheet_tujuan` | Read-only | Link ke KKA terkait |
| **`tanggal_aktual_review`** | **Editable** | RA isi kapan beneran direview |
| **`status_review`** | **Editable, dropdown 4 pilihan**: Belum Review, Dalam Review, Selesai, Ditunda | |
| **`catatan_ra`** | **Editable**, teks bebas | |
| `offsite_id` | Read-only | ID unik: `OFF-{kode_unit}-{YYYYMMDD}-{kode_area}` |

### 2.9 `kka_teller_kas`, `kka_kredit`, `kka_biaya_beban`, `kka_biaya_internal`, `kka_pengaduan`, `kka_transaksi_umum`, `kka_transfer_ku` (7 tabel — kertas kerja audit per area)
Sumbernya: baris `staging_offsite` WHERE `area_review` = area ini AND `masuk_kka_final` = true AND `status_data_quality` = 'VALID' AND unit+periode cocok WP aktif.

**Field read-only (dari Staging, konteks transaksi):** No, Test ID, Sample ID, Offsite ID, Staging ID, Target Review H+1, Tanggal Data, Nama RA, Kode Unit, Nama Unit, Source Sheet, Object ID, Case ID, Data Code, Deskripsi/Narasi, Nominal, User/Maker/PIC, Risk Awal, Exception Awal, Jenis Exception Awal, Sampel Low, Catatan Rule, Tujuan Uji, Kriteria, Prosedur Uji *(3 field terakhir ini auto-generate teks prosedur audit standar per area, lihat §7.7)*.

**Field editable (kuning, diisi RA/Reviewer):**

| Field | Tipe |
|---|---|
| `bukti_referensi` | Text |
| `hasil_uji` | Dropdown: **Belum Diuji, Sesuai, Tidak Sesuai, Tidak Dapat Disimpulkan** |
| `jenis_exception_ra` | Dropdown (khusus beberapa KKA, contoh KKA_Biaya): Tidak Ada, Dokumen Tidak Memadai, Otorisasi Tidak Sesuai, Salah Klasifikasi/Akun, Salah Periode, Duplikasi/Pembayaran Tidak Wajar, Pajak Tidak Sesuai, dst |
| `dampak` (1-5), `kemungkinan` (1-5) | Integer, dropdown 1-5 |
| `critical_trigger` | Dropdown: **Tidak, Ya** |
| `klarifikasi_awal`, `klarifikasi_unit` | Text |
| `status_klarifikasi` | Dropdown: **Belum Diminta, Diminta, Diterima, Selesai, Eskalasi, Tidak Relevan** *(catatan: beberapa KKA area punya varian pilihan sedikit beda, lihat Lampiran A.2)* |
| `perluasan_sampel` | Dropdown: Tidak, Ya |
| `perlu_onsite` | Dropdown: Tidak, Ya |
| `keputusan_onsite` | Dropdown: **Tidak Perlu, Dijadwalkan, Dilaksanakan, Ditutup Offsite** |
| `keputusan_eskalasi` | Dropdown: **Belum Diputuskan, Tidak, Ya** |
| `simpulan_ra` | Text panjang |
| `tanggal_ditemukan` | Date |
| `status_review` | Dropdown: **Belum Review, Dalam Proses, Selesai** *(atau varian "Revisi" di KKA_Teller_Kas — lihat Lampiran A.2)* |
| `catatan_reviewer` | Text |

**Field computed (formula, bukan dari Staging langsung):**
```
skor_risiko = dampak × kemungkinan
kategori_risiko_final = IF(critical_trigger = "Ya", "High", 
                          IF(skor_risiko >= ambang_high, "High",
                          IF(skor_risiko >= ambang_moderate, "Moderate", "Low")))
eskalasi_awal = IF(kategori_risiko_final = "High", "Ya", "Tidak")
rekomendasi_eskalasi = IF(OR(perlu_onsite="Ya", critical_trigger="Ya", kategori_risiko_final="High"), "Ya", "Tidak")
```
Ambang skor High/Moderate ada di parameter tersendiri (Master_Parameter, sekitar baris 213 di Excel — nilai persisnya harus dikonfirmasi/diambil ulang saat implementasi, karena rentang baris bisa berubah antar versi file).

### 2.10 `dashboard_offsite` (view agregat, bukan tabel fisik)
Query gabungan menampilkan:
- Identitas WP aktif (kode WP, unit, periode, RA, reviewer, status)
- Ringkasan: Populasi Eligible, KKA Final, Exception, Klarifikasi, Eskalasi
- Breakdown per Area Review: Eligible, High/Moderate/Low, KKA Final, Exception, Klarifikasi
- **Rekonsiliasi kualitas sumber data** per DUMP: Normalized (total baris masuk), Eligible, Salah Unit, Luar Periode, KKA Final, Exception — ini fitur validasi penting, tampilkan sebagai warning kalau ada baris "Salah Unit"/"Luar Periode" dalam jumlah besar (indikasi RA salah upload file/salah pilih unit-periode)
- Distribusi KKA Final per sheet KKA (Final/High/Moderate/Low per area)
- Kontrol kesiapan: Validasi Unit (VALID/tidak), Rekonsiliasi Staging (SEIMBANG/tidak), Populasi vs Register (SEIMBANG/tidak)

---

## 3. LOGIKA KALKULASI — MESIN DETEKSI (INTI SISTEM)

Ini bagian **paling penting** dan paling kompleks dari modul ini. Berikut alur deteksi per baris data yang di-upload, memakai contoh nyata dari DUMP_01 (Transaksi CBS) — pola yang sama berlaku (dengan keyword berbeda) di 4 DUMP lain.

### 3.1 Langkah 1 — Gabungkan jadi Teks Deteksi
```
teks_deteksi = UPPER(TRIM(GABUNG(kode_transaksi, nama_transaksi, no_referensi, 
                                   user_maker, nominal, D/K, user_input, deskripsi_narasi)))
```
Semua kolom relevan digabung jadi 1 string besar, huruf besar semua, spasi dirapikan — supaya pencarian kata kunci konsisten tidak peduli kapitalisasi asli data.

### 3.2 Langkah 2 — Cek tiap Flag lewat pencarian kata kunci (`SEARCH`, case-insensitive substring match)

Contoh nyata (bisa dikonfigurasi lewat `rule_engine`, tapi ini pola default dari Excel sumber):

```
flag_reversal = ADA salah satu dari ["REV-", "REVERSAL", "PEMBATALAN", " VOID "] di deskripsi_narasi
flag_koreksi_override = ADA salah satu dari ["REVISI BS", "KOREKSI", "OVERRIDE"]
flag_selisih_kas = ADA salah satu dari ["SELISIH KAS", "PEMBULATAN KAS"]
flag_tunai_besar = (D/K = "D") 
                    DAN BUKAN transaksi kas internal (KAS DARI, KAS KPD, dst — whitelist internal)
                    DAN BUKAN terkait selisih kas
                    DAN (ADA "PENARIKAN TUNAI" ATAU "SETORAN TUNAI")
                    DAN nominal >= threshold_tunai_besar (dari rule_threshold, beda per jenis unit)
flag_biaya_jurnal = BUKAN transaksi transfer/KU DAN ADA salah satu dari 
                     ["BIAYA","JURNAL","NOTA DB","NOTA KR","GL","SUSPENSE","TITIPAN"]
flag_internal_account = ADA salah satu dari ["SUSPENSE","TITIPAN","INTERNAL ACCOUNT"]
flag_pencairan_kredit = ADA salah satu dari ["PENCAIRAN KREDIT","PROVISI KREDIT",
                         "BIAYA ASURANSI DGN NO REK","BIAYA ADMINISTRASI DGN NO REK"]
flag_rutin_whitelist = ADA salah satu dari ["BIAYA GAJI","HONORARIUM","GAJI DAN TUNJ",
                        "PENGHASILAN TETAP","TUNJ KADES","FEE BASE TRX SUKSES",
                        "MPNG3_","BIA TRF","SETOR TRF"]
```

**Aturan penting**: rule ini **harus bisa diedit tanpa ubah kode** — simpan sebagai data di `rule_engine`, bukan hardcode di logika aplikasi. Ini konsisten dengan desain asli (00_Petunjuk poin 3: *"Rule dipisah: Risk Trigger, Classification, dan Whitelist untuk mengurangi false positive"*).

### 3.3 Langkah 3 — Hitung Jumlah Flag Risiko
```
jumlah_flag_risiko = COUNT(flag_reversal, flag_koreksi_override, flag_tunai_besar, flag_selisih_kas = "Ya")
                    + (1 jika flag_biaya_jurnal="Ya" DAN BUKAN whitelist DAN BUKAN kredit)
                    + (1 jika flag_internal_account="Ya")
```

### 3.4 Langkah 4 — Tentukan Area Review (routing)
```
IF flag_pencairan_kredit = "Ya" → Area = "Kredit"
ELSE IF (flag_reversal ATAU flag_koreksi ATAU flag_tunai_besar ATAU flag_selisih_kas ATAU 
          ADA "PENARIKAN TUNAI"/"SETORAN TUNAI"/"KAS DARI"/"KAS KPD") → Area = "Teller/Kas"
ELSE IF (ADA "KU-" ATAU " KLR " ATAU "KELUAR_" ATAU "SETOR TRF" ATAU "BIA TRF") → Area = "Transfer/KU"
ELSE IF (flag_biaya_jurnal ATAU flag_internal_account ATAU ADA "NOTA DB"/"NOTA KR") → Area = "Biaya/Internal"
ELSE → Area = "Transaksi Umum" (default/fallback)
```

### 3.5 Langkah 5 — Tentukan Risk Level
```
IF flag_selisih_kas = "Ya" 
   ATAU (flag_tunai_besar = "Ya" DAN (flag_reversal="Ya" ATAU flag_koreksi="Ya"))
   ATAU jumlah_flag_risiko >= 3
   → Risk Level = "High"
ELSE IF jumlah_flag_risiko > 0 → Risk Level = "Moderate"
ELSE → Risk Level = "Low"
```

### 3.6 Langkah 6 — Case Pairing (deteksi transaksi berpasangan)
Banyak transaksi CBS itu berpasangan (debit di 1 baris, kredit di baris lain, harus balance jadi 0). Sistem deteksi ini via:
```
case_id = tanggal_data (format YYYYMMDD) + "|" + no_referensi
jumlah_baris_case = COUNT baris dengan case_id yang sama
net_nominal_case = SUM nominal semua baris dengan case_id yang sama
status_pairing = IF jumlah_baris_case < 2 → "Single Row"
                  ELSE IF ABS(net_nominal_case) <= 1 → "Balance"
                  ELSE → "Tidak Balance"   ← INI YANG PERLU DIPERHATIKAN RA
risk_tertinggi_case = level risiko tertinggi di antara semua baris dengan case_id sama
```
**Transaksi dengan status "Tidak Balance" adalah red flag** — indikasi transaksi tidak simetris/ada kejanggalan pembukuan.

### 3.7 Langkah 7 — Tentukan tujuan KKA & apakah masuk review
```
IF risk_level ≠ "High"/"Moderate" (artinya "Low") DAN BUKAN whitelist:
     kka_sheet_tujuan = "Register"   (tidak otomatis masuk KKA — kandidat sampling)
ELSE:
     kka_sheet_tujuan = KKA_{Area Review}   (contoh: KKA_Teller_Kas, KKA_Kredit, dst)

perlu_kka_detail = "Ya" JIKA (jumlah_flag_risiko > 0 ATAU risk_level = "High")
perlu_klarifikasi = "Ya" JIKA whitelist_flag = "Ya"   (whitelist tetap dicatat, tapi ditandai beda)
perlu_eskalasi = "Ya" JIKA risk_level = "High"
```

### 3.8 Langkah 8 — Sampling untuk Low Risk (lihat detail di §4.6 bawah)

---

## 4. LOGIKA KALKULASI LAINNYA

### 4.1 Validasi Data Quality (per baris upload)
```
status_data_quality = IF kode_unit_baris ≠ kode_unit WP aktif → "Salah Unit"
                       ELSE IF tanggal_data DI LUAR (periode_mulai, periode_selesai) WP aktif → "Luar Periode"
                       ELSE → "VALID"
```
**Hanya baris berstatus VALID yang masuk ke `staging_offsite`** dan dihitung di Register/KKA/Dashboard. Baris tidak-valid tetap disimpan di tabel DUMP asal (untuk keperluan rekonsiliasi/audit trail — lihat §9 soal retensi data), tapi dikeluarkan dari proses aktif.

### 4.2 Penentuan Masuk KKA Final
```
IF risk_level IN ("High", "Moderate") → masuk_kka_final = TRUE (selalu)
IF risk_level = "Low" → masuk_kka_final = TRUE HANYA JIKA baris ini terpilih sebagai sampel 
                          (lihat §4.6 logika sampling)
```

### 4.3 Skor Risiko & Kategori Final (di level KKA, diisi manual RA)
```
skor_risiko = dampak (1-5) × kemungkinan (1-5)     → rentang 1-25
kategori_risiko_final = IF critical_trigger = "Ya" → "High" (paksa, mengabaikan skor)
                          ELSE IF skor_risiko >= ambang_high → "High"
                          ELSE IF skor_risiko >= ambang_moderate → "Moderate"
                          ELSE → "Low"
```
Ini pola **identik** dengan skema override di Modul Audit Plan (Critical Override memaksa kategori "High") — konsistensi desain antar-modul.

### 4.4 Eskalasi & Rekomendasi
```
eskalasi_awal = IF kategori_risiko_final = "High" → "Ya"
rekomendasi_eskalasi = IF (keputusan_onsite = "Ya" ATAU critical_trigger = "Ya" 
                         ATAU kategori_risiko_final = "High") → "Ya"
```

### 4.5 Status WP (siklus hidup dokumen)
```
Draft → Aktif → Final
```
Naik status manual oleh Reviewer/Bagian RA, biasanya dengan syarat: semua baris Register/KKA sudah `status_review = "Selesai"`.

### 4.6 Logika Sampling untuk Low Risk (stratified sampling)
```
FOR EACH strata (kombinasi domain × jenis kejadian, misal "CBS - Rekening Baru"):
    target = sampling_strata.target_case   (contoh: 5)
    kandidat = SEMUA baris Low Risk dalam strata ini, periode WP aktif
    IF COUNT(kandidat) <= target:
        semua kandidat → sampel_low = TRUE
    ELSE:
        ambil `target` baris secara acak/terstruktur dari kandidat → sampel_low = TRUE
        sisanya → sampel_low = FALSE (tidak masuk KKA)
```
**Catatan implementasi**: file Excel sumber tidak secara eksplisit menunjukkan ALGORITMA pemilihan sampel (acak murni? sistematis/interval? berdasarkan urutan tanggal?) — ini **perlu diklarifikasi ke pemilik SOP** sebelum diimplementasikan, karena metode sampling memengaruhi validitas statistik hasil audit.

### 4.7 Hasil Awal (Register) — Label Prioritas
```
IF populasi_eligible = 0 → "Tidak Ada Data"
ELSE IF perlu_eskalasi > 0 → "Perlu Eskalasi"
ELSE IF perlu_klarifikasi > 0 → "Perlu Klarifikasi"
ELSE IF exception > 0 → "Ada Exception"
ELSE → (kemungkinan "Aman"/"Tidak Ada Temuan" — label tepatnya cek ulang ke source jika beda)
```
Ini cascade prioritas — makin ke bawah kondisinya makin ringan.

---

## 5. SPESIFIKASI MAPPING CSV → DUMP (HASIL VERIFIKASI DENGAN FILE CONTOH ASLI)

Ini bagian **kritis** — hasil analisis terhadap contoh CSV riil yang diberikan mentor, sudah diverifikasi kolom per kolom.

### 5.1 Pemetaan file ke tabel tujuan

| Kode Report (nama file asli) | Tabel tujuan | Status kesiapan |
|---|---|---|
| `RPDT006` (Aktifitas Teller) | `dump_transaksi_cbs` | ✅ Siap — 8/8 field kebutuhan ada |
| `RPDT017` (Transaksi Jurnal) | `dump_biaya_beban` | ✅ Siap — hampir semua field ada, termasuk flag Auto/System yang cocok persis ke kolom `ISAUTOTX` |
| `RPDC001` (Nomi Kredit) | `dump_kredit` | ⚠️ Field lengkap TAPI **tidak ada kolom tanggal** — lihat §5.3 |
| `RPMS001-NOMI_DEPO` | `dump_dpk_apuppt` | ⚠️ Field lengkap TAPI **tidak ada kolom tanggal** |
| `RPMS001-NOMI_TAB` | `dump_dpk_apuppt` (digabung dengan Depo) | ⚠️ **Tidak ada kolom tanggal DAN tidak ada kolom Kode Unit** |
| — (belum ada contoh) | `dump_pengaduan` | ❌ Belum ada file contoh — perlu diminta ke mentor, biasanya dari sistem tiket/CRM pengaduan, bukan core banking |

### 5.2 Mapping kolom detail — DUMP_01 (Transaksi CBS) dari `RPDT006`

| Kolom CSV | Field tujuan |
|---|---|
| `TGL_TX` | `tanggal_data` |
| `KD_CAB` | `kode_unit` |
| `NO_ARSIP` | `no_referensi` |
| `KD_USER` | `user_maker` |
| `NAMA_USER` | `nama_user` (bonus field, tidak wajib tapi berguna) |
| `KD_TX` | `kode_transaksi` |
| `JUMLAH_TX` | `nominal` |
| `KET_TX` | `deskripsi_narasi` |
| — | `data_source` = hardcode "CBS" |

### 5.3 Mapping kolom detail — DUMP_04 (Biaya/Beban) dari `RPDT017`

| Kolom CSV | Field tujuan |
|---|---|
| `TGL_TX` | `tanggal_data` |
| `KD_CAB` | `kode_unit` |
| `NO_REK` | `no_rekening` |
| `NO_ARSIP` | `no_arsip` |
| `KD_TX` | `kode_transaksi` |
| `KET_TX` | `keterangan_transaksi` (= deskripsi_narasi) |
| `DB_KR` | `d_k` |
| `JUMLAH_TX` | `nominal` |
| `KD_USER` | `user_input` |
| `TIME_STAMP` | `time_stamp` |
| `ISAUTOTX` | `auto_system_flag` |

### 5.4 Mapping kolom detail — DUMP_03 (Kredit) dari `RPDC001`

| Kolom CSV | Field tujuan |
|---|---|
| `KD_CAB` | `kode_unit` |
| `NO_REK` | `no_rekening_kredit` |
| `NO_NASABAH` | `cif_nasabah` |
| `NO_AKD` | `no_akad` |
| `NAMA_SINGKAT` | `nama_debitur` |
| `PRD_NAME`, `KD_PRD` | `produk_kredit` |
| `JENIS_KREDIT` | `jenis_kredit` |
| `TGLMULAI` | `tanggal_realisasi` |
| `TGL_JT` | `tanggal_jatuh_tempo` |
| `JNK_WKT_BL` | `jangka_waktu_bulan` |
| `PLAFOND` | `plafon` |
| `SALDO_AKHIR` | `baki_debet` |
| `KOLEKTIBILITY` | `kolektibilitas` |
| `TUNGG_POKOK`, `TUNGG_BUNGA` | `tunggakan_pokok`, `tunggakan_bunga` |
| `NAMA_AO`, `KODE_AO` | `ao_pengelola` |
| `TOTAGUNAN` | `total_agunan` |
| — | `tanggal_data` = **TIDAK ADA di CSV, lihat §5.6** |

### 5.5 Mapping kolom detail — DUMP_02 (DPK/APU-PPT) dari `RPMS001` (2 file digabung)

**Dari RPMS001-NOMI_DEPO:**
| Kolom CSV | Field tujuan |
|---|---|
| `KD_CAB` | `kode_unit` |
| `KD_PRODUK` | `produk` |
| `NO_NSB` | `cif_nasabah` |
| `NO_REK` | `no_rekening` |
| `NAMA_SINGKAT` | `nama_nasabah` |
| `GOL_PEMILIK` | `gol_pemilik` |
| `TGL_BUKA_REK` | `tanggal_buka` |
| `TGL_JT` | `jatuh_tempo` |
| `SALDO_AKHIR` | `saldo_akhir` |
| `KD_STATUS` | `status_rekening` |
| `NO_BILYET` | `no_bilyet` |
| `NO_REK_BUNGA` | `no_rek_penerima_bunga` |
| `PRS_BUNGA` | `persen_bunga` |
| `KD_PAJAK`, `FLG_BNG` | `kode_pajak_flag_bunga` |

**Dari RPMS001-NOMI_TAB:** kolom serupa (`NO_REK`, `NO_NSB`, `NAMA_SINGKAT`, `TGL_BUKA_REK`, `TGL_TX_AKHIR`, `GOL_PEMILIK`, `SALDO_AKHIR`, `PRODNM`/`KD_PRODUK`, `ACCSTS`/`STSDESC` → `status_rekening`) — **TAPI TIDAK ADA `KD_CAB`**, lihat §5.6.

### 5.6 ⚠️ Masalah berulang yang WAJIB ditangani di aplikasi

**Masalah A — Laporan jenis "Nominatif/Posisi" tidak punya kolom tanggal per baris**
Berlaku untuk: `RPDC001` (Kredit), `RPMS001` Depo, `RPMS001` Tab.

**Penyebab**: laporan ini adalah **snapshot** (posisi/saldo pada 1 titik waktu), bukan log kejadian harian — jadi wajar tidak ada kolom tanggal per baris, karena SELURUH FILE mewakili 1 tanggal (tanggal laporan digenerate).

**Solusi WAJIB**: form upload untuk jenis laporan Nominatif harus punya **input tanggal manual** (1 nilai, berlaku untuk SEMUA baris dalam file itu) yang diisi/dikonfirmasi RA saat upload — BUKAN diharapkan ada di kolom CSV.

**Masalah B — `RPMS001-NOMI_TAB` tidak punya kolom Kode Unit sama sekali**
**Solusi WAJIB**: sama seperti tanggal — form upload harus punya **input Kode Unit manual** untuk file jenis ini (RA pilih dari dropdown unit sebelum upload), karena laporan ini kemungkinan didesain "1 file = 1 cabang" dari sisi core banking system.

**Implementasi teknis yang disarankan**: bedakan 2 mode upload di aplikasi:
```
Mode "Log Transaksi" (RPDT-series)   → tanggal & unit WAJIB ada di tiap baris CSV
Mode "Snapshot/Nominatif" (RPDC/RPMS-series) → tanggal & unit (jika tidak ada di CSV) 
                                                  diinput 1x manual di form upload,
                                                  diterapkan ke SEMUA baris saat proses
```

### 5.7 Field CSV yang TIDAK dipetakan (boleh diabaikan)
Kolom seperti `KD_APL`, `TXTYPE`, `KODE_INSTANSI`, `NILAI_WAJAR_KREDIT`, dst yang muncul di CSV tapi tidak ada padanan field target — boleh diabaikan saat import, kecuali nanti ada kebutuhan spesifik baru yang memerlukannya.

---

## 6. KEPUTUSAN ARSITEKTUR PENTING: STAGING CUKUP 1 TABEL

Excel sumber punya **2 sheet STAGING** (`STAGING_Raw_Normalized` dan `STAGING_Offsite_Normalized`) — ini **murni keterbatasan teknis Excel**, BUKAN kebutuhan bisnis yang harus direplikasi di aplikasi.

**Alasan Excel butuh 2 tahap**: `STAGING_Raw_Normalized` menggabungkan 5 DUMP tapi hasilnya "berlubang" (baris kosong menyisip di posisi baris yang gagal validasi, karena formula Excel mengikuti posisi baris asal). `STAGING_Offsite_Normalized` memakai `FILTER()` untuk membuang baris kosong itu.

**Di aplikasi, cukup 1 tabel `staging_offsite`.** Saat backend memproses upload CSV: validasi baris → kalau valid, langsung `INSERT` ke `staging_offsite`; kalau tidak valid, skip (atau log terpisah untuk rekonsiliasi, lihat §9). Tidak perlu tahap "simpan dulu semua termasuk yang kosong, baru filter belakangan" — itu cuma relevan untuk keterbatasan formula spreadsheet.

**STAGING_Source_Map** (sheet ke-18) adalah sheet rekonsiliasi murni untuk memastikan tidak ada data hilang antara Raw dan Compact — di aplikasi, validasi ini otomatis tidak relevan lagi karena hanya ada 1 tabel (tidak ada 2 versi yang perlu dicocokkan).

---

## 7. TEKS PROSEDUR AUDIT OTOMATIS (per Area, ditampilkan di KKA)

Tiap baris KKA otomatis menampilkan 3 kolom bantuan (read-only, generate otomatis berdasarkan `area_review`):

| Kolom | Contoh isi (Area = Teller/Kas) |
|---|---|
| Tujuan Uji | "Memastikan transaksi teller/kas sah, wajar, sesuai kewenangan, dan sesuai ketentuan" |
| Kriteria | "Ketentuan operasional teller/kas, limit kewenangan, maker-checker" |
| Prosedur Uji | "Telusuri bukti; cocokkan tanggal, nominal, user, otorisasi, dan kelengkapan dokumen" |

Tiap area (Kredit, Biaya/Beban, DPK, Pengaduan, dst) punya teks standarnya sendiri. **Ini nilai tambah besar** — RA tidak perlu mengarang prosedur uji dari nol tiap kali, sistem sudah kasih template standar sesuai area, RA tinggal sesuaikan/lengkapi di kolom Bukti/Hasil Uji.

Simpan template teks ini sebagai data referensi (`prosedur_uji_template` per `area_review`), bukan hardcode di UI, supaya bisa diedit Admin kalau standar prosedur audit berubah.

---

## 8. HUBUNGAN DENGAN MODUL AUDIT PLAN (SOP 01) — INTEGRASI YANG WAJIB DIBANGUN

Excel sumber **tidak** punya link otomatis antar-file (2 workbook terpisah, Excel tidak reliable untuk cross-file link). Di aplikasi, ini **wajib diotomatisasi**, karena secara proses bisnis dua modul ini terhubung erat:

```
Modul Offsite (SOP 02) — kerja harian sepanjang bulan
        │
        ▼  begitu Status WP = "Final"
Hitung otomatis: jumlah Exception, Eskalasi, High Risk temuan periode ini
        │
        ▼
Update raw_metrics unit ini di Modul Audit Plan (SOP 01), khususnya:
   - bidang "Riwayat Pemeriksaan RA" → kolom Penyimpangan pada Offsite, 
     Penyimpangan Offsite Signifikan/Berulang
   - bidang operasional terkait (Kas/Teller, Kredit, dst) → sesuai area temuan
        │
        ▼
Skor risiko unit di Audit Plan ter-update untuk PERIODE BERIKUTNYA
   (memengaruhi kategori risiko → memengaruhi frekuensi onsite berikutnya)
```

**Rekomendasi implementasi**: bikin *event/job* terpisah yang trigger begitu `status_wp` di Offsite berubah jadi "Final" — job ini menghitung agregat temuan periode itu dan menulis ke `raw_metrics` (§3.2 dokumen Audit Plan) unit terkait. Ini **bukan** fitur yang ada eksplisit di Excel — ini rekomendasi arsitektur berdasarkan pemahaman alur bisnis kedua SOP, perlu dikonfirmasi detail pemetaan field-nya ke pemilik SOP.

---

## 9. STRATEGI RETENSI & VOLUME DATA

Karena RA upload CSV **harian**, volume data di tabel DUMP/Staging bisa besar dalam jangka panjang. Rekomendasi:

### 9.1 Bukan "hapus vs simpan semua" — tapi tiering
```
Data ter-flag (masuk KKA, High/Moderate/Sampel Low)
    → simpan permanen di tabel aktif (nilai audit tinggi, sering dirujuk ulang)

Data tidak ter-flag (Low risk, tidak kepilih sampel)
    → setelah periode WP selesai & Final, pindahkan ke tabel/partisi arsip
      (BUKAN dihapus langsung — lihat pertimbangan kepatuhan di bawah)

Data arsip yang sudah melewati batas retensi resmi (sesuai kebijakan bank/regulator)
    → baru dihapus permanen, lewat proses terjadwal
```

### 9.2 Pertimbangan kepatuhan (WAJIB dikonfirmasi ke bank, bukan diasumsikan)
- Berapa lama data transaksi wajib disimpan menurut kebijakan retensi resmi bank/OJK?
- Apakah regulator butuh akses ke SELURUH data mentah (termasuk yang tidak ter-flag), atau cukup yang ter-flag saja?
- Jangan implementasikan "hapus otomatis begitu selesai scan" tanpa konfirmasi eksplisit ini — resiko kepatuhan lebih besar daripada resiko biaya storage.

### 9.3 Implementasi teknis yang disarankan
- Partisi tabel DUMP/Staging per bulan (`staging_offsite_202606`, dst) — mempercepat query data terbaru, mempermudah penghapusan massal per periode saat retensi habis.
- Kompresi data arsip (data teks bisa terkompresi signifikan).
- Ini semua **bukan kebutuhan mendesak untuk tahap awal development** (skala data mahasiswa/prototipe kecil) — cukup desain skema yang sudah mendukung partisi/arsip di masa depan (kolom periode yang jelas), implementasi penuh menyusul saat sudah mendekati produksi skala bank sungguhan.

---

## 10. STRUKTUR NAVIGASI & MENU (PRESENTATION LAYER)

Mengikuti prinsip yang sama dengan Modul Audit Plan: **ikuti alur kerja user, bukan struktur sheet Excel 1:1.** Sheet yang murni proses backend (DUMP, STAGING) TIDAK perlu tampil sebagai menu. Setiap menu ditandai role yang bisa akses (lihat detail matriks di §1.4).

```
📁 OFFSITE REVIEW
│
├── 📤 Upload Data                          [RA — full akses]
│     5 slot upload (CBS, DPK/APU-PPT, Kredit, Biaya/Beban, Pengaduan)
│     → preview validasi singkat (jumlah baris, unit/periode terdeteksi,
│       input manual tanggal/unit untuk file jenis Nominatif — §5.6)
│     → konfirmasi → diproses di backend (DUMP tidak tampil sebagai tabel mentah)
│     Dropdown unit dibatasi hanya ke unit tanggung jawab RA yang login.
│
├── ✅ Register Harian                       [RA — edit | Admin/Reviewer — lihat semua]
│     Checklist harian per tanggal × area — accordion/tabel per hari,
│     RA update Status Review & Catatan, klik baris → lompat ke KKA terkait.
│     Admin/Reviewer lihat lintas RA/unit untuk pantau progress & keterlambatan.
│
├── 📝 KKA per Area (7 sub-menu)             [RA — isi field kerja | Admin/Reviewer — Catatan Reviewer]
│     Teller/Kas, Kredit, Biaya/Beban, Biaya/Internal, Pengaduan, 
│     Transaksi Umum, Transfer/KU
│     — tempat kerja utama RA: isi Hasil Uji, Dampak, Kemungkinan, 
│       Klarifikasi, Simpulan. Admin/Reviewer isi Catatan Reviewer saja.
│
├── 📈 Dashboard Offsite                      [RA — unit sendiri | Admin/Reviewer — semua unit]
│     Ringkasan + rekonsiliasi kualitas data + kontrol kesiapan
│
└── ⚙️ Pengaturan Modul                        [Admin/Reviewer only]
      Rule Engine (Risk Trigger/Classification/Whitelist), Threshold,
      Parameter Sampling, Template Prosedur Uji per Area
```

**Field yang TIDAK boleh dilupakan di halaman Upload**: preview validasi sebelum data "resmi" diproses — RA harus bisa lihat "file terbaca X baris, unit terdeteksi Y, ada Z baris ditolak" SEBELUM data itu benar-benar dianggap masuk sistem, supaya kesalahan upload (salah file/salah unit) ketahuan sejak awal, bukan setelah proses jalan.

---

## 11. RINGKASAN PRIORITAS IMPLEMENTASI

1. **Fase 1 — Master data & rule**: `units_reference` (bisa reuse dari Audit Plan), `rule_engine`, `rule_threshold`, `sampling_strata`, `wp_offsite`
2. **Fase 2 — Upload & Deteksi**: 5 tabel DUMP + mesin deteksi teks (§3) — ini bagian paling kompleks, sarankan dibangun & diuji satu domain dulu (CBS) sebelum replikasi ke 4 domain lain
3. **Fase 3 — Staging**: 1 tabel `staging_offsite` + proses validasi & normalisasi
4. **Fase 4 — Register & KKA**: checklist harian + 7 halaman kerja detail
5. **Fase 5 — Dashboard & Status WP**: ringkasan, rekonsiliasi, siklus Draft→Aktif→Final
6. **Fase 6 — Integrasi ke Audit Plan**: job otomatis feed hasil ke `raw_metrics` (§8) — lakukan setelah kedua modul solid berjalan sendiri-sendiri

Verifikasi tiap fase dengan membandingkan hasil kalkulasi aplikasi terhadap contoh data di Excel sumber (unit 106 - KCP Tinombo, periode Juni 2026, RA Shanty, sudah ada beberapa angka terverifikasi manual dalam proses penyusunan dokumen ini).

---

## LAMPIRAN A — REFERENSI DETAIL

### A.1 Contoh Rule Engine (dari Master_Parameter, sebagian — lihat file asli untuk daftar lengkap)

| Rule ID | Tipe | Keyword/Pola |
|---|---|---|
| RISK_REV_01 | Risk Trigger | REV- |
| RISK_KOR_01 | Risk Trigger | REVISI BS |
| RISK_SEL_01 | Risk Trigger | SELISIH KAS |
| RISK_SEL_02 | Risk Trigger | PEMBULATAN KAS |
| CLS_TLR_01 | Classification | PENARIKAN TUNAI |
| CLS_TLR_02 | Classification | SETORAN TUNAI |
| CLS_KRD_01 | Classification | PENCAIRAN KREDIT |
| CLS_BIAYA_01 | Classification | BIAYA |
| WL_001 | Whitelist | PB GAJI |
| WL_002 | Whitelist | GAJI DAN TUNJ KADES |
| WL_003 | Whitelist | MPNG3_ |
| WL_004 | Whitelist | BIA TRF |

*(Ini contoh sebagian — Master_Parameter punya rule terpisah untuk tiap domain: CBS, DPK, Kredit, Biaya, Pengaduan, masing-masing dengan set keyword sendiri. Ekstrak lengkap perlu dilakukan langsung dari file saat implementasi, karena jumlahnya banyak dan terus bisa bertambah.)*

### A.2 Variasi Dropdown Status per KKA Area (WAJIB dicek ulang per sheet, tidak seragam persis)

| KKA Area | Status Review | Status Klarifikasi |
|---|---|---|
| Teller/Kas | Belum Review, Dalam Review, **Selesai, Revisi** | Belum Diminta, Diminta, Diterima, Selesai, Eskalasi, Tidak Relevan |
| Kredit, Biaya/Beban, Pengaduan, dll | Belum Review, **Dalam Proses**, Selesai | Belum Diminta, **Menunggu Unit**, Selesai, Tidak Memadai |

**Perhatikan**: KKA_Teller_Kas punya varian pilihan yang sedikit BEDA dari 6 KKA area lainnya (Selesai/Revisi vs Dalam Proses, dan variasi Status Klarifikasi). Jangan asumsikan semua 7 KKA punya dropdown identik — verifikasi tiap sheet kalau butuh presisi penuh.

### A.3 Contoh Data Terverifikasi (untuk testing/validasi hasil development)

```
Kode WP: SOP02-106-202606
Unit: 106 - Kantor Cabang Pembantu Tinombo (KCP)
Kantor Induk: Kantor Cabang Parigi
Periode: 1 Juni 2026 s.d. 30 Juni 2026
RA Pelaksana: Shanty
Reviewer: Rostika Warda
Status WP: Draft

Contoh baris Staging (dari DUMP_01):
  Unit 102 - Kantor Cabang Parigi, RA Shanty, tanggal 24 Juli 2026
  Deskripsi: "KU-214193 KLR KELUAR_102->000"
  → Area Review: Transfer/KU (karena match keyword "KU-" dan "KLR")
  → KKA Sheet tujuan: KKA_Transfer_KU
```

---

*Dokumen ini disusun berdasarkan analisis menyeluruh terhadap file `WP_SOP_02_OFFSITE_V2_Stage4.xlsx` (18 sheet, termasuk formula deteksi tersembunyi dan seluruh dropdown validasi) serta 5 file contoh CSV riil dari mentor. Beberapa poin (algoritma sampling detail, ambang skor risiko persis, daftar rule engine lengkap per domain, field mapping DUMP_05 Pengaduan) memerlukan konfirmasi/ekstraksi lanjutan sebelum diimplementasikan sebagai logika final — semua ditandai eksplisit di bagian terkait.*
