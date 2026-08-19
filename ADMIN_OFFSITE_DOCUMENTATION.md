# Dokumentasi Modul Admin Offsite

## Daftar Isi
1. [Deskripsi Modul](#deskripsi-modul)
2. [Akses & Role](#akses--role)
3. [Struktur Fitur](#struktur-fitur)
4. [Cara Penggunaan](#cara-penggunaan)
5. [Format CSV Register Offsite](#format-csv-register-offsite)
6. [Database Schema](#database-schema)

---

## Deskripsi Modul

Modul **Admin Offsite** adalah dashboard administratif khusus untuk role **admin** yang memungkinkan:
- Melihat daftar semua cabang dengan statistik unit
- Memantau status review offsite per unit
- Upload file CSV Register Offsite Harian dari RA
- Mengubah status review unit secara manual

Modul ini diintegrasikan dengan data **Audit Plan** yang sudah ada (Unit, RA, Cabang).

---

## Akses & Role

- **Role Admin Only**: Hanya role `admin` yang dapat mengakses modul ini
- **Menu**: "Admin Offsite" tersedia di sidebar untuk admin
- **Route Prefix**: `/admin/offsite`

---

## Struktur Fitur

### 1. Index (Daftar Cabang)
- **Route**: `/admin/offsite`
- **View**: `admin-offsite.index`
- **Fitur**:
  - Filter berdasarkan Tahun & Bulan
  - Card per cabang menampilkan:
    - Total Unit aktif
    - Jumlah unit yang perlu review
    - Jumlah unit yang sudah selesai review
    - Progress bar persentase selesai
  - Link ke detail cabang

### 2. Cabang Detail (Daftar Unit per Cabang)
- **Route**: `/admin/offsite/cabang/{cabang}`
- **View**: `admin-offsite.cabang-detail`
- **Fitur**:
  - Tabel daftar unit dengan kolom:
    - Kode & Nama Unit
    - Jenis Unit (KC, KCU, KCP, dst)
    - RA Pelaksana
    - Jumlah Area Berisiko
    - Risiko Tertinggi (High, Moderate, Low)
    - Status Review
    - Terakhir Upload
  - Filter status review (Perlu Review, Tidak Perlu, Selesai)
  - Link ke detail unit

### 3. Unit Detail (Management Unit)
- **Route**: `/admin/offsite/unit/{unit}`
- **View**: `admin-offsite.unit-detail`
- **Fitur**:
  - **Summary Card** menampilkan:
    - Total area eligible
    - Area berisiko
    - Klarifikasi & Eskalasi
    - Risiko tertinggi
    - Status review
  - **Update Status Manual**:
    - Dropdown status review (Tidak Perlu, Perlu, Dalam, Selesai)
    - Field catatan
    - Tombol simpan
  - **Upload Register CSV**:
    - Pilih Tahun & Bulan
    - Upload file CSV
    - Proses otomatis parsing & hitung summary
  - **Riwayat Upload**:
    - Tabel upload history dengan status

---

## Cara Penggunaan

### Step 1: Akses Admin Offsite
1. Login sebagai admin
2. Klik menu "Admin Offsite" di sidebar
3. Halaman index menampilkan daftar cabang

### Step 2: Lihat Unit per Cabang
1. Klik tombol "Lihat Detail Unit" pada card cabang
2. Opsional: Filter berdasarkan status review
3. Lihat daftar unit dengan status review mereka

### Step 3: Upload Register Offsite Harian
1. Klik tombol "Detail" pada unit yang ingin diupload
2. Scroll ke bagian "Upload Register Offsite Harian"
3. Pilih Tahun & Bulan periode data
4. Pilih file CSV register (dari RA)
5. Klik "Upload & Proses"
6. Sistem akan:
   - Menyimpan file
   - Parse CSV
   - Hitung agregat per unit
   - Update status review otomatis

### Step 4: Update Status Manual
1. Jika perlu mengubah status secara manual:
   - Di halaman unit detail
   - Pilih status review baru
   - Tambah catatan jika perlu
   - Klik "Simpan Perubahan"

---

## Format CSV Register Offsite

File CSV harus memiliki kolom-kolom berikut (dengan header di baris pertama):

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| No | Integer | Nomor urut |
| Tanggal Data | Date | Tanggal data (YYYY-MM-DD) |
| Target Review H+1 | Date | Target review H+1 |
| Tanggal Aktual Review | Date | Tanggal review aktual (opsional) |
| Nama RA | String | Nama RA Pelaksana |
| **Kode Unit** | String | Kode unit (WAJIB) - akan dicocokkan dengan master unit |
| **Area Review** | String | Nama area (Teller/Kas, Biaya/Internal, Kredit, dst) |
| Populasi Eligible | Integer | Jumlah populasi eligible |
| Sampel Low | Integer | Jumlah sampel low risk |
| KKA Final | Integer | Jumlah KKA final |
| Exception | Integer | Jumlah exception |
| **Risiko Tertinggi** | String | High, Moderate, Low, atau "Tidak Ada Data" |
| **Perlu Klarifikasi** | Integer | Jumlah item yang perlu klarifikasi |
| **Perlu Eskalasi** | Integer | Jumlah item yang perlu eskalasi |
| KKA Sheet | String | Nama KKA sheet |
| Hasil Awal | String | Hasil awal review |
| **Status Review** | String | Belum Review, Dalam Review, atau Selesai Review |
| Offsite ID | String | ID offsite |
| Catatan RA | String | Catatan dari RA |
| Catatan Reviewer | String | Catatan dari reviewer |

**Kolom yang ditandai WAJIB/penting untuk parsing.**

### Contoh CSV (Tab-delimited atau Comma-delimited):
```csv
No,Tanggal Data,Target Review H+1,Tanggal Aktual Review,Nama RA,Kode Unit,Area Review,Populasi Eligible,Sampel Low,KKA Final,Exception,Risiko Tertinggi,Perlu Klarifikasi,Perlu Eskalasi,KKA Sheet,Hasil Awal,Status Review,Offsite ID,Catatan RA,Catatan Reviewer
1,2026-06-01,2026-06-02,,SHANTY,106,Teller/Kas,0,0,0,0,Tidak Ada Data,0,0,Register,Tidak Ada Data,Belum Review,OFF-106-20260602-TK,,
2,2026-06-01,2026-06-02,,SHANTY,106,Biaya/Internal,1,0,1,1,Moderate,1,0,KKA_Biaya_Internal,Perlu Klarifikasi,Belum Review,OFF-106-20260602-BI,,
```

---

## Database Schema

### Tabel: `offsite_unit_summary`
Menyimpan agregat status review per unit per bulan.

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint | Primary Key |
| unit_id | bigint | FK ke units |
| ra_id | bigint | FK ke ras (nullable) |
| tahun | integer | Tahun periode |
| bulan | integer | Bulan periode (1-12) |
| periode_label | string | Label periode (mis: "Juni 2026") |
| status_review | enum | Status: Tidak Perlu Review / Perlu Review / Dalam Review / Selesai Review |
| total_area_eligible | integer | Total area yang eligible |
| total_area_risiko | integer | Total area dengan risiko |
| total_klarifikasi | integer | Total item yang perlu klarifikasi |
| total_eskalasi | integer | Total item yang perlu eskalasi |
| risiko_tertinggi | string | Risiko tertinggi (High/Moderate/Low/Tidak Ada Data) |
| terakhir_upload | timestamp | Waktu upload terakhir |
| catatan | string | Catatan manual |
| created_at | timestamp | Waktu created |
| updated_at | timestamp | Waktu updated |

**Unique Constraint**: `(unit_id, tahun, bulan)`

### Tabel: `offsite_register_uploads`
Menyimpan tracking setiap upload file CSV.

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint | Primary Key |
| unit_id | bigint | FK ke units |
| tahun | integer | Tahun periode |
| bulan | integer | Bulan periode |
| file_name | string | Nama file yang disimpan |
| file_path | string | Path relatif di storage |
| total_records | integer | Total record dalam CSV |
| total_areas | integer | Total area dalam CSV |
| status | enum | Pending / Processing / Processed / Failed |
| error_message | text | Pesan error jika gagal |
| uploaded_by | string | Nama user yang upload |
| uploaded_at | timestamp | Waktu upload |
| created_at | timestamp | Waktu created |
| updated_at | timestamp | Waktu updated |

---

## Logic Penentuan Status Review

Sistem otomatis menentukan status review berdasarkan:

```php
// Jika sudah Dalam Review atau Selesai → pertahankan status
if ($statusReview === 'Dalam Review' || $statusReview === 'Selesai Review') {
    return $statusReview;
}

// Jika ada klarifikasi, eskalasi, atau exception → Perlu Review
if ($klarifikasi > 0 || $eskalasi > 0 || $exception > 0) {
    return 'Perlu Review';
}

// Jika risiko tertinggi High atau Moderate to High → Perlu Review
if (in_array($risikoTertinggi, ['High', 'Moderate to High'])) {
    return 'Perlu Review';
}

// Selainnya → Tidak Perlu Review
return 'Tidak Perlu Review';
```

---

## File Paths

- **Controller**: `app/Http/Controllers/AdminOffsiteController.php`
- **Service**: `app/Services/OffsiteAdminService.php`
- **Models**: 
  - `app/Models/OffsiteUnitSummary.php`
  - `app/Models/OffsiteRegisterUpload.php`
- **Views**:
  - `resources/views/admin-offsite/index.blade.php` (Daftar cabang)
  - `resources/views/admin-offsite/cabang-detail.blade.php` (Daftar unit)
  - `resources/views/admin-offsite/unit-detail.blade.php` (Detail & upload)
- **Routes**: `routes/web.php` (prefix: `/admin/offsite`, name: `admin-offsite.*`)

---

## Testing

1. **Login sebagai admin**
2. **Akses `/admin/offsite`** → lihat daftar cabang
3. **Klik detail cabang** → lihat daftar unit
4. **Klik detail unit** → lihat upload form
5. **Upload CSV sample** → sistem akan parsing & update status

Semua data akan tersimpan di tabel `offsite_unit_summary` dan `offsite_register_uploads`.

---

## Notes

- File CSV disimpan di `storage/app/offsite-registers/`
- Parsing dilakukan per baris, aggregated per unit
- Status review auto-calculated tapi bisa di-override manual
- RA name di CSV akan dicocokkan dengan master RA untuk link ra_id
- Kode Unit di CSV akan dicocokkan dengan master Unit untuk link unit_id

