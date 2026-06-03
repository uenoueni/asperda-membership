# ASPERDA — Entity Relationship Diagram

**Stack:** Laravel 11 REST API + Vue JS PWA  
**Wilayah:** menggunakan package [didiwijaya/wilindo](https://github.com/didiwijaya/wilindo) (Provinsi, Kab/Kota, Kecamatan, Desa — data SPLP Kemendagri)

---

## Ringkasan Relasi Antar Domain

```
USERS ──< MEMBERS ──< BRANCHES          (1 user = 1 member, 1 member bisa punya banyak cabang)
MEMBERS ──< SURVEY_ASSIGNMENTS           (setiap proses survey adalah record baru, termasuk eskalasi)
SURVEY_ASSIGNMENTS ──< SANCTIONS         (pelanggaran tenggat dicatat di tabel sanctions)
MEMBERS ──< PAYMENTS                     (registrasi & perpanjangan)
PAYMENTS ──< REFUNDS                     (jika member ditolak)
PAYMENTS ──< CERTIFICATES                (jika member diterima)
MEMBERS ──< STARTERKIT_DISTRIBUTIONS     (per periode pendaftaran)
STARTERKIT_ITEMS ──< STARTERKIT_DISTRIBUTIONS
ORGANIZATIONAL_UNITS >── USERS           (scope wilayah petugas DPC/DPD/DPP)
ORGANIZATIONAL_UNITS >── wilindo_cities  (DPC terikat ke kota)
ORGANIZATIONAL_UNITS >── wilindo_provinces (DPD terikat ke provinsi)
```

---

## Wilindo Tables (read-only, dari package)

Tabel-tabel ini di-seed oleh `WilindoSeeder` dan tidak boleh dimodifikasi secara manual.  
Prefix default: `wilindo_`

### wilindo_provinces
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `code` | varchar(2) | Kode provinsi 2 digit (contoh: `32` = Jawa Barat) |
| `name` | varchar | Nama provinsi |
| `created_at`, `updated_at` | timestamps | |

### wilindo_cities
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `code` | varchar(4) | Kode kab/kota 4 digit (contoh: `3273` = Kota Bandung) |
| `province_code` | varchar(2) FK → wilindo_provinces.code | |
| `name` | varchar | Nama kabupaten/kota |
| `created_at`, `updated_at` | timestamps | |

### wilindo_districts
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `code` | varchar(7) | Kode kecamatan 7 digit |
| `city_code` | varchar(4) FK → wilindo_cities.code | |
| `name` | varchar | Nama kecamatan |
| `created_at`, `updated_at` | timestamps | |

### wilindo_villages
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `code` | varchar(10) | Kode desa/kelurahan 10 digit |
| `district_code` | varchar(7) FK → wilindo_districts.code | |
| `name` | varchar | Nama desa/kelurahan |
| `created_at`, `updated_at` | timestamps | |

---

## Domain ASPERDA

### 1. users
Tabel autentikasi sekaligus identitas semua actor: calon anggota, anggota aktif, dan petugas organisasi (DPC/DPD/DPP/Super Admin).

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `name` | varchar(255) | Nama lengkap |
| `email` | varchar(255) UNIQUE | |
| `email_verified_at` | datetime nullable | null = belum verifikasi |
| `password` | varchar(255) nullable | null saat pending konfirmasi email |
| `remember_token` | varchar(100) nullable | |
| `role` | enum | `super_admin`, `dpp`, `dpd`, `dpc`, `member` |
| `phone` | varchar(20) nullable | |
| `bank_name` | varchar(100) nullable | Diisi saat registrasi, untuk keperluan refund |
| `bank_account_no` | varchar(50) nullable | |
| `bank_account_name` | varchar(255) nullable | Nama pemilik rekening |
| `created_at`, `updated_at` | timestamps | |

> **Catatan:** `bank_*` disimpan di `users` saat registrasi, lalu di-copy ke tabel `refunds` saat refund dibuat. Data refund tidak akan berubah meski user memperbarui rekening.

---

### 2. organizational_units
Memetakan petugas ke scope wilayah mereka. DPC terikat ke kota, DPD ke provinsi, DPP tidak terikat wilayah (scope nasional).

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | bigint FK → users.id | Petugas yang ditetapkan |
| `unit_type` | enum | `dpc`, `dpd`, `dpp` |
| `province_code` | varchar(2) nullable FK → wilindo_provinces.code | Diisi untuk DPD dan DPC |
| `city_code` | varchar(4) nullable FK → wilindo_cities.code | Diisi untuk DPC saja |
| `name` | varchar(255) | Nama unit, contoh: `DPC Kota Bandung` |
| `is_active` | boolean default true | |
| `created_at`, `updated_at` | timestamps | |

> **Rule wilayah:**  
> - DPC: `province_code` + `city_code` wajib diisi  
> - DPD: `province_code` wajib, `city_code` null  
> - DPP: keduanya null (scope nasional)  
> Enforce di level aplikasi (Form Request) dan/atau DB constraint.

---

### 3. members
Data keanggotaan, terpisah dari `users` agar satu user hanya punya satu record member yang bisa memiliki history perpanjangan melalui `payments`.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | bigint FK → users.id UNIQUE | |
| `membership_no` | varchar(50) UNIQUE nullable | Generate saat status menjadi `active` |
| `rental_name` | varchar(255) | Nama usaha rental |
| `status` | enum | `pending_verification` → `waiting_survey` → `active` / `rejected` / `expired` |
| `registered_at` | date nullable | Tanggal payment pertama lunas |
| `expires_at` | date nullable | Tanggal kadaluarsa keanggotaan |
| `period_year` | varchar(4) | Tahun periode, contoh: `2025` |
| `created_at`, `updated_at` | timestamps | |

---

### 4. branches
Cabang operasional member. Saat ini 1 member = 1 primary branch (diset dari UI), tapi struktur sudah mendukung multi-cabang untuk ekspansi berikutnya.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `member_id` | bigint FK → members.id | |
| `branch_name` | varchar(255) | Nama cabang, contoh: `Kantor Pusat Bandung` |
| `province_code` | varchar(2) FK → wilindo_provinces.code | |
| `city_code` | varchar(4) FK → wilindo_cities.code | Kota operasional utama |
| `district_code` | varchar(7) nullable FK → wilindo_districts.code | |
| `address` | text | Alamat lengkap |
| `unit_count` | int default 0 | Jumlah armada unit kendaraan |
| `is_primary` | boolean default false | Hanya satu branch per member yang true |
| `created_at`, `updated_at` | timestamps | |

---

### 5. survey_assignments
Setiap level verifikasi (DPC/DPD/DPP) adalah record terpisah. Eskalasi menghasilkan record baru dengan `escalated_from` menunjuk ke assignment sebelumnya, sehingga chain history utuh dan bisa di-audit.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `member_id` | bigint FK → members.id | |
| `assigned_to` | bigint FK → users.id | Petugas yang diberi tugas |
| `organizational_unit_id` | bigint FK → organizational_units.id | Unit yang bertanggung jawab |
| `level` | enum | `dpc`, `dpd`, `dpp` |
| `status` | enum | `pending` → `accepted` → `approved` / `rejected` / `escalated` |
| `rejection_reason` | text nullable | Wajib diisi jika status `rejected` |
| `deadline` | datetime | Tenggat waktu verifikasi (configurable per setting) |
| `accepted_at` | datetime nullable | Waktu petugas klik "terima tugas" |
| `decided_at` | datetime nullable | Waktu keputusan final (approve/reject) |
| `escalated_from` | bigint nullable FK → survey_assignments.id | Self-referencing, null jika assignment pertama |
| `created_at`, `updated_at` | timestamps | |

> **Alur eskalasi:**  
> Assignment DPC timeout tanpa `accepted_at` → sistem buat record baru level `dpd` dengan `escalated_from` = ID assignment DPC.  
> DPD harus eksplisit `accept` sebelum bisa memutuskan. Jika DPD juga timeout → buat record baru level `dpp` dengan cara yang sama.

---

### 6. sanctions
Rekam jejak kelalaian petugas. Dibuat saat eskalasi terjadi karena deadline terlampaui, atau saat DPP/DPD secara manual memberikan teguran formal.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `survey_assignment_id` | bigint FK → survey_assignments.id | Assignment yang memicu sanksi |
| `issued_to` | bigint FK → users.id | Petugas yang dikenai sanksi |
| `issued_by` | bigint FK → users.id | Petugas yang menerbitkan sanksi |
| `reason` | text | Alasan sanksi |
| `severity` | enum | `warning`, `suspension`, `termination` |
| `notes` | text nullable | Catatan tambahan |
| `created_at`, `updated_at` | timestamps | |

---

### 7. payments
Satu tabel untuk semua transaksi: registrasi awal dan perpanjangan tahunan. Type dan `period_year` membedakannya.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `member_id` | bigint FK → members.id | |
| `midtrans_order_id` | varchar(100) UNIQUE | Format: `ASPERDA-{member_id}-{timestamp}` |
| `midtrans_transaction_id` | varchar(100) nullable | Diisi setelah notifikasi Midtrans |
| `type` | enum | `registration`, `renewal` |
| `period_year` | varchar(4) | Tahun periode |
| `amount` | decimal(15,2) | |
| `status` | enum | `pending`, `paid`, `failed`, `expired`, `refunded` |
| `paid_at` | datetime nullable | |
| `payment_channel` | varchar(50) nullable | Contoh: `bca_va`, `gopay`, `qris` |
| `midtrans_raw_response` | json nullable | Simpan raw callback untuk audit |
| `created_at`, `updated_at` | timestamps | |

---

### 8. refunds
Dibuat saat member ditolak pada tahap survey. Data rekening di-copy dari `users` saat record ini dibuat.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `payment_id` | bigint FK → payments.id | Payment yang akan di-refund |
| `member_id` | bigint FK → members.id | |
| `survey_assignment_id` | bigint FK → survey_assignments.id | Assignment yang memutuskan penolakan |
| `rejection_reason` | text | Salinan alasan penolakan dari survey |
| `rejected_by` | bigint FK → users.id | Petugas yang menolak |
| `original_amount` | decimal(15,2) | Nominal payment awal |
| `refund_amount` | decimal(15,2) | Bisa berbeda jika ada potongan admin |
| `bank_name` | varchar(100) | Copy dari users.bank_name saat record dibuat |
| `bank_account_no` | varchar(50) | Copy dari users.bank_account_no |
| `bank_account_name` | varchar(255) | Copy dari users.bank_account_name |
| `planned_refund_date` | date nullable | Rencana tanggal transfer |
| `actual_refund_date` | date nullable | Realisasi tanggal transfer |
| `status` | enum | `queued`, `processing`, `completed`, `cancelled` |
| `processed_by` | bigint nullable FK → users.id | Admin yang memproses refund |
| `notes` | text nullable | Catatan proses refund |
| `created_at`, `updated_at` | timestamps | |

---

### 9. certificates
Generate otomatis saat member diterima. Satu record per periode per member.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `member_id` | bigint FK → members.id | |
| `payment_id` | bigint FK → payments.id | Linked ke payment yang trigger keaktifan |
| `cert_number` | varchar(100) UNIQUE | Format: `ASPERDA/{period_year}/{member_no}/{seq}` |
| `period_year` | varchar(4) | |
| `valid_from` | date | |
| `valid_until` | date | |
| `file_path` | varchar(500) nullable | Path PDF di storage |
| `generated_at` | datetime nullable | Null jika belum di-generate |
| `created_at`, `updated_at` | timestamps | |

---

### 10. starterkit_items
Daftar item seragam/starterkit per periode. Stok dikelola di sini.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `name` | varchar(255) | Nama item, contoh: `Kaos Polo ASPERDA 2025` |
| `description` | text nullable | |
| `period_year` | varchar(4) | |
| `stock_total` | int default 0 | Total stok yang disiapkan |
| `stock_distributed` | int default 0 | Sudah didistribusikan (update via transaction + lockForUpdate) |
| `is_active` | boolean default true | |
| `created_at`, `updated_at` | timestamps | |

> **Penting:** Update `stock_distributed` harus dalam `DB::transaction()` dengan `lockForUpdate()` untuk mencegah race condition saat distribusi serentak.

---

### 11. starterkit_distributions
Record distribusi per member per periode. Satu entry per item per member.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `member_id` | bigint FK → members.id | |
| `payment_id` | bigint FK → payments.id | Linked ke payment periode terkait |
| `item_id` | bigint FK → starterkit_items.id | |
| `period_year` | varchar(4) | |
| `status` | enum | `pending`, `distributed`, `confirmed` |
| `distributed_at` | datetime nullable | Waktu distribusi oleh admin |
| `confirmed_at` | datetime nullable | Waktu konfirmasi penerimaan oleh member |
| `distributed_by` | bigint nullable FK → users.id | Admin yang mendistribusikan |
| `notes` | text nullable | |
| `created_at`, `updated_at` | timestamps | |

---

## Diagram Relasi Ringkas

```
wilindo_provinces ──< wilindo_cities ──< wilindo_districts ──< wilindo_villages
        │                    │
        │                    └──< organizational_units >──── users
        └──────────────────────< organizational_units       (petugas DPC/DPD/DPP)
                                                               │
                                             users ───────────┘
                                               │
                                           members ──────────────────┐
                                               │                      │
                                           branches                   │
                                     (wilayah via wilindo)            │
                                               │                      │
                                    survey_assignments ──< sanctions  │
                                    (self-ref: escalated_from)        │
                                               │                      │
                                           payments ──────────────────┘
                                               │
                              ┌────────────────┼────────────────┐
                           refunds       certificates    starterkit_distributions
                                                               │
                                                       starterkit_items
```

---

## Settings (Global Config)

Tidak dimasukkan sebagai tabel relasional, cukup sebagai key-value store.

### app_settings
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `key` | varchar(100) UNIQUE | Contoh: `survey_deadline_dpc_days` |
| `value` | text | |
| `description` | varchar(255) nullable | |
| `updated_by` | bigint FK → users.id | |
| `updated_at` | timestamp | |

**Key yang direkomendasikan:**

| Key | Default | Keterangan |
|---|---|---|
| `survey_deadline_dpc_days` | `3` | Tenggat DPC dalam hari |
| `survey_deadline_dpd_days` | `3` | Tenggat DPD setelah eskalasi |
| `survey_deadline_dpp_days` | `5` | Tenggat DPP setelah eskalasi |
| `membership_duration_months` | `12` | Durasi keanggotaan aktif |
| `registration_fee` | `500000` | Biaya pendaftaran (rupiah) |
| `renewal_fee` | `300000` | Biaya perpanjangan (rupiah) |
| `cert_number_prefix` | `ASPERDA` | Prefix nomor sertifikat |

---

## Catatan Implementasi Laravel

### Model & Relationship
```php
// Member.php
public function user(): BelongsTo
public function branches(): HasMany
public function primaryBranch(): HasOne  // where is_primary = true
public function surveyAssignments(): HasMany
public function payments(): HasMany
public function certificates(): HasMany
public function starterkitDistributions(): HasMany
public function latestActiveSurvey(): HasOne  // latest waiting_survey assignment

// SurveyAssignment.php
public function escalatedFrom(): BelongsTo  // self-referencing
public function escalations(): HasMany       // self-referencing inverse
public function sanctions(): HasMany

// OrganizationalUnit.php
public function user(): BelongsTo
public function province()  // via wilindo Province model
public function city()      // via wilindo City model
```

### Wilindo Integration
```php
use DidiWijaya\WilIndo\Models\Province;
use DidiWijaya\WilIndo\Models\City;

// Di Branch model
public function province()
{
    return Province::where('code', $this->province_code)->first();
}

public function city()
{
    return City::where('code', $this->city_code)->first();
}
```

### Queue Jobs yang Diperlukan
| Job | Trigger | Keterangan |
|---|---|---|
| `SendEmailVerificationJob` | Registrasi user | Kirim link verifikasi |
| `CheckSurveyDeadlineJob` | Scheduled (setiap jam) | Cek assignment yang mendekati/melewati deadline |
| `EscalateSurveyJob` | Dipanggil oleh CheckSurveyDeadlineJob | Buat assignment baru + sanksi |
| `GenerateCertificateJob` | Payment status → `paid` + survey `approved` | Generate PDF sertifikat |
| `SendSurveyNotificationJob` | Assignment baru dibuat | Notif petugas via email |

---

*Dokumen ini adalah living document — perbarui setiap kali ada perubahan skema sebelum mulai coding migration.*
