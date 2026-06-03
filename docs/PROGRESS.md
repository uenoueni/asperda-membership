# Progress Pengerjaan — Sistem Registrasi Keanggotaan ASPERDA

**Terakhir diperbarui:** 2026-06-02 (Tahap 1 selesai)

---

## Legenda Status

| Simbol | Arti |
|--------|------|
| ✅ | Selesai & teruji end-to-end |
| 🔄 | Sedang dikerjakan |
| ⏳ | Belum dimulai |
| ❌ | Blocked / ada masalah |

---

## Tahap 1 — Fondasi

**Status Tahap:** ✅ Selesai

| # | Task | Status | Catatan |
|---|------|--------|---------|
| 1.1 | Setup project Laravel 11 | ✅ | Sudah ada + semua package terinstall |
| 1.2 | Setup project Vue 3 + Vite | ✅ | `frontend/` dengan PWA plugin |
| 1.3 | Install & konfigurasi `didiwijaya/wilindo` v2.0.0 | ✅ | Migrations wilindo sudah berjalan |
| 1.4 | Buat semua migration (11 tabel ASPERDA + app_settings) | ✅ | 12 migration, sudah dijalankan; `app_settings` di-seed default |
| 1.5 | Setup `app/Enums/` dan `src/constants/enums.js` | ✅ | 9 PHP enum + frontend enums.js |
| 1.6 | Setup `BaseController` + response envelope | ✅ | + Exception handler di bootstrap/app.php |
| 1.7 | Setup `useApi()` composable | ✅ | + usePagination.js + useWilayah.js |
| 1.8 | Auth login / logout (Sanctum) | ✅ | AuthController: register, login, logout, me + WilayahController |
| 1.9 | Email verification (signed URL, 24 jam, `SendEmailVerificationJob`) | ✅ | Job + Mailable + Blade template email |
| 1.10 | Set password setelah verifikasi email | ✅ | setPassword endpoint + SetPasswordView.vue |

---

## Tahap 2 — Registrasi & Payment

**Status Tahap:** ⏳ Belum dimulai  
*Prasyarat: Tahap 1 selesai end-to-end*

| # | Task | Status | Catatan |
|---|------|--------|---------|
| 2.1 | Form registrasi data dasar + rekening bank | ⏳ | — |
| 2.2 | Cascade wilayah: provinsi → kota → kecamatan (lazy-load via `useWilayah`) | ⏳ | — |
| 2.3 | Form data keanggotaan + upload dokumen | ⏳ | — |
| 2.4 | Halaman review sebelum bayar | ⏳ | — |
| 2.5 | Integrasi Midtrans Snap — inisiasi pembayaran | ⏳ | — |
| 2.6 | Midtrans webhook handler | ⏳ | — |
| 2.7 | Update status member setelah payment valid | ⏳ | — |
| 2.8 | `SendPaymentReminderJob` (scheduled tiap 08:00) | ⏳ | — |

---

## Tahap 3 — Survey & Keluaran

**Status Tahap:** ⏳ Belum dimulai  
*Prasyarat: Tahap 2 selesai end-to-end*

| # | Task | Status | Catatan |
|---|------|--------|---------|
| 3.1 | Buat `survey_assignments` record saat member masuk waiting list | ⏳ | — |
| 3.2 | Dashboard verifikasi DPC | ⏳ | — |
| 3.3 | Dashboard verifikasi DPD | ⏳ | — |
| 3.4 | Dashboard verifikasi DPP | ⏳ | — |
| 3.5 | `SendSurveyNotificationJob` — notif email ke petugas | ⏳ | — |
| 3.6 | `CheckSurveyDeadlineJob` (scheduled tiap jam) | ⏳ | — |
| 3.7 | `EscalateSurveyJob` — eskalasi + buat sanksi otomatis | ⏳ | — |
| 3.8 | Generate sertifikat PDF via dompdf (`GenerateCertificateJob`) | ⏳ | — |
| 3.9 | Halaman verifikasi sertifikat publik | ⏳ | — |
| 3.10 | Alur refund — create record + snapshot rekening | ⏳ | — |
| 3.11 | Dashboard refund untuk admin | ⏳ | — |

---

## Tahap 4 — Distribusi & Admin

**Status Tahap:** ⏳ Belum dimulai  
*Prasyarat: Tahap 3 selesai end-to-end*

| # | Task | Status | Catatan |
|---|------|--------|---------|
| 4.1 | CRUD starterkit items (stok per periode) | ⏳ | — |
| 4.2 | Distribusi starterkit per member (`lockForUpdate`) | ⏳ | — |
| 4.3 | Admin dashboard — statistik keanggotaan | ⏳ | — |
| 4.4 | RBAC — Policy scope wilayah (DPC/DPD/DPP) | ⏳ | — |
| 4.5 | `organizational_units` management | ⏳ | — |
| 4.6 | `app_settings` — konfigurasi deadline, biaya, dll | ⏳ | — |
| 4.7 | Manajemen sanksi petugas | ⏳ | — |

---

## Tahap 5 — Polish & Deploy

**Status Tahap:** ⏳ Belum dimulai  
*Prasyarat: Tahap 4 selesai end-to-end*

| # | Task | Status | Catatan |
|---|------|--------|---------|
| 5.1 | PWA manifest + service worker | ⏳ | — |
| 5.2 | Lighthouse audit (target: PWA score ≥ 90) | ⏳ | — |
| 5.3 | PWABuilder — wrap ke APK Play Store | ⏳ | — |
| 5.4 | Setup queue worker cron (`--stop-when-empty`) di cPanel | ⏳ | — |
| 5.5 | Setup Laravel Scheduler cron (1 menit) di cPanel | ⏳ | — |
| 5.6 | Deploy ke shared hosting (cPanel) | ⏳ | — |
| 5.7 | Smoke test end-to-end di production | ⏳ | — |

---

## Ringkasan Progres

| Tahap | Total Task | Selesai | % |
|-------|-----------|---------|---|
| Tahap 1 — Fondasi | 10 | 10 | 100% |
| Tahap 2 — Registrasi & Payment | 8 | 0 | 0% |
| Tahap 3 — Survey & Keluaran | 11 | 0 | 0% |
| Tahap 4 — Distribusi & Admin | 7 | 0 | 0% |
| Tahap 5 — Polish & Deploy | 7 | 0 | 0% |
| **Total** | **43** | **10** | **23%** |

---

## Catatan & Keputusan Aktif

> Gunakan bagian ini untuk mencatat blockers, keputusan desain baru, atau perubahan scope yang muncul selama pengerjaan.

- **2026-06-02 — Tahap 1 selesai.** Semua 12 migration dijalankan ke DB. Auth flow: register → signed URL email → verify → set-password → login (Sanctum cookie-based). Password nullable saat pending email verification. Token set-password reuse tabel `password_reset_tokens` bawaan Laravel, berlaku 24 jam.
- **ERD tidak berubah** — semua skema sesuai. Satu poin catatan: `password` di tabel `users` dibuat nullable via migration terpisah (bukan ubah migration awal) agar aman di shared hosting.
- **Frontend auth views** ditambahkan: `LoginView.vue`, `VerifyEmailView.vue`, `SetPasswordView.vue`. Router diupdate dengan navigation guard berbasis role.
- **WilayahController** + 3 route wilayah (provinsi/kota/kecamatan) sudah aktif — siap dipakai di Tahap 2 cascade dropdown form registrasi.
- **app_settings** di-seed dengan 7 nilai default (biaya, durasi keanggotaan, deadline survey, prefix sertifikat).

---

## Roadmap Post v1.0

| Fase | Fitur | Status |
|------|-------|--------|
| v1.1 | Multi-cabang UI (DB sudah siap dari v1.0) | ⏳ |
| v1.2 | PWA Web Push Notification | ⏳ |
| v1.3 | Export laporan PDF/Excel | ⏳ |
| v1.4 | Dashboard statistik per wilayah | ⏳ |
| v2.0 | Marketplace merchandise ASPERDA | ⏳ |
