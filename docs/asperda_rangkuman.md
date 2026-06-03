# Rangkuman Sesi Perencanaan — Sistem Registrasi Keanggotaan ASPERDA

**Tanggal:** 2025  
**Topik:** Desain arsitektur, ERD, PRD, dan coding conventions untuk sistem keanggotaan ASPERDA

---

## Keputusan Utama yang Diambil

### Stack Teknologi

| Layer | Pilihan | Alasan |
|---|---|---|
| Backend | Laravel 11 REST API | Sudah familiar, ekosistem matang |
| Frontend | Vue 3 + Vite | SPA, kompatibel PWA |
| Mobile | PWA (bukan Capacitor) | Capacitor dinilai overkill untuk use case yang user-nya buka 2-3x/tahun |
| PDF Sertifikat | `barryvdh/laravel-dompdf` | Satu-satunya opsi yang kompatibel shared hosting (browsershot butuh Chrome) |
| Payment | Midtrans Snap | — |
| Wilayah | `didiwijaya/wilindo` v2.0.0 | Data SPLP Kemendagri, 4 level: provinsi → kota → kecamatan → desa |
| Hosting | Shared hosting (cPanel) | Queue via `--stop-when-empty`, scheduler via cron 1 menit |

**Mengapa bukan Capacitor:** Persistent process tidak didukung shared hosting. Queue worker dan Scheduler dijalankan via cron `--stop-when-empty`. PWA Builder digunakan untuk wrap ke APK Play Store.

---

## Arsitektur Sistem

### Aktor dan Peran

| Role | Scope | Kewenangan |
|---|---|---|
| Calon Anggota | Publik | Registrasi, bayar, pantau status |
| Anggota Aktif (Member) | Akun sendiri | Dashboard, sertifikat, starterkit, perpanjangan |
| DPC | Kota | Verifikasi anggota baru di kotanya |
| DPD | Provinsi | Eskalasi dari DPC, sanksi DPC |
| DPP | Nasional | Eskalasi dari DPD, sanksi DPD, override semua |
| Super Admin | Nasional | Akses penuh, konfigurasi sistem |

### Alur Registrasi (ringkas)

```
Landing page ASPERDA
  → Isi data dasar + rekening bank
  → Verifikasi email (signed URL, 24 jam)
  → Set password
  → Isi data keanggotaan (wilayah cascading dari wilindo)
  → Review & bayar (Midtrans Snap)
  → Masuk waiting list survey
  → Verifikasi berjenjang DPC → DPD → DPP
  → Diterima: sertifikat + starterkit
  → Ditolak: refund manual ke rekening yang diinput saat registrasi
```

### Eskalasi Survey

Setiap level punya deadline (configurable via `app_settings`). Jika petugas tidak `accept` sebelum deadline:
- Assignment lama → status `escalated`
- Sanksi `warning` otomatis dibuat untuk petugas yang lalai
- Assignment baru dibuat ke level berikutnya dengan `escalated_from` menunjuk ke yang lama
- Chain history utuh dan auditable

DPP adalah level tertinggi — tidak ada eskalasi lebih lanjut.

### Perpanjangan

Bayar saja, tidak perlu survey ulang. Sertifikat dan starterkit baru dibuat otomatis setelah payment valid.

---

## Keputusan Desain Database

### Pemisahan Member dan Branch

`MEMBERS` dan `BRANCHES` dipisah sejak awal meski saat ini UI hanya ekspos 1 branch per member. Alasan: mengubah struktur flat ke multi-cabang setelah sistem berjalan membutuhkan refactor besar (tabel, migrasi data, API). Dengan `is_primary = true` di tabel `BRANCHES`, upgrade ke multi-cabang nanti hanya butuh perubahan UI dan beberapa endpoint — skema DB tidak berubah.

### Wilindo Integration

Relasi ke wilindo menggunakan `code` varchar (bukan FK integer biasa):
- `province_code` varchar(2): contoh `32` = Jawa Barat
- `city_code` varchar(4): contoh `3273` = Kota Bandung
- `district_code` varchar(7)

Tidak bisa pakai `belongsTo` Laravel standar. Custom relationship diperlukan di model.

Wilayah di-cache 1 jam di controller untuk mengurangi query ke tabel wilindo yang besar.

### Snapshot Data Rekening di Refund

`bank_name`, `bank_account_no`, `bank_account_name` disimpan di dua tempat:
- `users` — diisi saat registrasi, bisa diupdate user
- `refunds` — di-copy saat record refund dibuat, **immutable**

Ini mencegah data refund berubah kalau user memperbarui rekening setelah pengajuan ditolak.

### Scope Wilayah di RBAC

Tabel `organizational_units` memetakan petugas ke wilayah:
- DPC: `province_code` + `city_code` wajib
- DPD: `province_code` wajib, `city_code` null
- DPP: keduanya null (nasional)

Validasi scope dilakukan di **Laravel Policy**, bukan di controller atau frontend. Query selalu include filter wilayah berdasarkan `organizational_units` user yang login.

---

## Dokumen yang Dihasilkan

| File | Isi |
|---|---|
| `asperda_erd.md` | ERD lengkap: 11 tabel ASPERDA + 4 tabel wilindo + app_settings, beserta catatan implementasi Laravel |
| `asperda_prd/00_index.md` | Daftar isi PRD, ringkasan modul, urutan pengembangan |
| `asperda_prd/01_overview.md` | Latar belakang, tujuan, scope v1.0, deskripsi 6 peran |
| `asperda_prd/02_alur_sistem.md` | 4 alur utama dalam diagram ASCII: registrasi, verifikasi, refund, perpanjangan |
| `asperda_prd/03_spesifikasi_fitur.md` | 62 feature spec (F-01–F-62) per modul |
| `asperda_prd/04_teknis.md` | Stack, penyesuaian shared hosting, Midtrans, dompdf, PWA |
| `asperda_prd/05_ux_nfr_roadmap.md` | UX requirements, NFR, batasan, roadmap, urutan pengembangan 5 tahap |
| `asperda-stack.skill` | Skill untuk Claude Code — konvensi teknis seragam |

---

## Skill `asperda-stack`

Skill untuk Claude Code yang memastikan semua coding di project ASPERDA konsisten. Berisi 4 reference file:

| File | Isi |
|---|---|
| `references/laravel.md` | Folder structure, routing, `BaseController`, response envelope, Form Request, Model, Enum, Policy, Job, Migration, cron shared hosting |
| `references/vue.md` | `useApi` composable (wajib untuk semua HTTP call), `useWilayah` cascade lazy-load, `WilayahSelect` component, enums JS, Vue Router + guard, Pinia, pagination, PWA config |
| `references/api-contract.md` | Shape request/response semua endpoint, format error, pagination |
| `references/patterns.md` | Midtrans flow lengkap, survey escalation chain, wilindo integration + caching, dompdf template, AppSetting helper, starterkit lockForUpdate |

**Cara install di project:**

```bash
# Di root folder project ASPERDA
mkdir -p .claude/skills/asperda-stack
unzip asperda-stack.skill -d .claude/skills/asperda-stack/
git add .claude/skills/
git commit -m "chore: tambah skill asperda-stack"
```

Skill otomatis aktif saat Claude mendeteksi task coding ASPERDA. Bisa juga dipanggil manual dengan `/asperda-stack`.

---

## Aturan Konsistensi Teknis (Quick Rules)

1. **API calls selalu via `useApi()` composable** — tidak ada `fetch` atau `axios` langsung di komponen
2. **Dropdown dengan data eksternal selalu lazy-load** — tidak preload saat page mount
3. **Semua enum di satu tempat** — `app/Enums/` (PHP) dan `src/constants/enums.js` (Vue), tidak hardcode string inline
4. **Response API selalu pakai envelope standar** — `{ success, data, message, errors?, meta? }`
5. **RBAC scope di Policy, bukan controller** — controller hanya memanggil `$this->authorize()`
6. **Queue job untuk semua proses async** — email, PDF, eskalasi; tidak dilakukan synchronous di controller
7. **Dropdown wilayah selalu call API** — tidak import data wilindo langsung ke Vue

---

## Tabel Tabel Database (ringkas)

| Tabel | Fungsi |
|---|---|
| `users` | Auth + identitas semua actor |
| `organizational_units` | Memetakan petugas ke scope wilayah |
| `members` | Data keanggotaan (terpisah dari users) |
| `branches` | Cabang operasional member (wilayah via wilindo) |
| `survey_assignments` | Setiap level verifikasi = 1 record, self-ref `escalated_from` |
| `sanctions` | Rekam jejak kelalaian petugas |
| `payments` | Registrasi & perpanjangan (type enum) |
| `refunds` | Antrian refund manual + snapshot rekening |
| `certificates` | Sertifikat PDF per periode |
| `starterkit_items` | Stok seragam/starterkit per periode |
| `starterkit_distributions` | Distribusi per member per periode |
| `app_settings` | Konfigurasi global (deadline, biaya, dll) |
| `wilindo_*` | 4 tabel wilayah Indonesia (read-only, dari package) |

---

## Queue Jobs

| Job | Trigger | Keterangan |
|---|---|---|
| `SendEmailVerificationJob` | Registrasi | Signed URL verifikasi |
| `SendSurveyNotificationJob` | Assignment dibuat | Email ke petugas |
| `CheckSurveyDeadlineJob` | Scheduled tiap jam | Cek assignment timeout |
| `EscalateSurveyJob` | Dipanggil CheckSurveyDeadlineJob | Buat assignment baru + sanksi |
| `GenerateCertificateJob` | Payment paid + survey approved | Generate PDF via dompdf |
| `SendPaymentReminderJob` | Scheduled tiap pukul 08:00 | Reminder payment pending |

---

## Urutan Pengembangan

```
Tahap 1 — Fondasi
  1. Setup project (Laravel + Vue + wilindo + semua migration)
  2. Auth & Email Verification

Tahap 2 — Registrasi & Payment
  3. Registrasi & Profil (cascade wilayah)
  4. Payment (Midtrans Snap + webhook)

Tahap 3 — Survey & Keluaran
  5. Survey & Eskalasi
  6. Sertifikat (dompdf + halaman verifikasi publik)
  7. Refund Management

Tahap 4 — Distribusi & Admin
  8. Starterkit & Inventori
  9. Admin Dashboard & RBAC (scope wilayah)

Tahap 5 — Polish & Deploy
  10. PWA (manifest, service worker, Lighthouse audit, PWABuilder)
```

Setiap tahap harus selesai dan bisa diuji **end-to-end** sebelum lanjut ke tahap berikutnya.

---

## Roadmap Post v1.0

| Fase | Fitur |
|---|---|
| v1.1 | Multi-cabang UI (DB sudah siap) |
| v1.2 | PWA Web Push Notification |
| v1.3 | Export laporan PDF/Excel |
| v1.4 | Dashboard statistik per wilayah |
| v2.0 | Marketplace merchandise ASPERDA |
