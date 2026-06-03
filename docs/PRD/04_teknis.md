# PRD — 04: Spesifikasi Teknis

**← Kembali ke:** `03_spesifikasi_fitur.md`  
**Selanjutnya:** `05_ux_nfr_roadmap.md`

---

## 7.1 Stack

| Layer | Teknologi | Versi |
|---|---|---|
| Backend | Laravel | 11 |
| Runtime | PHP | 8.3+ |
| Database | MySQL | 8.0+ |
| Frontend | Vue 3 + Vite | Vue 3.x / Vite 5.x |
| PWA | vite-plugin-pwa | — |
| Mobile distribution | PWABuilder (wrap ke APK) | — |
| Payment | Midtrans Snap API | — |
| Email | Laravel Mail (SMTP) | — |
| Queue | Laravel Queue, driver: `database` | — |
| Storage | Laravel Storage (local disk) | — |
| Wilayah | didiwijaya/wilindo | v2.0.0 |
| Auth | Laravel Sanctum (SPA cookie-based) | — |
| PDF Sertifikat | barryvdh/laravel-dompdf | — |
| Activity Log | spatie/laravel-activitylog | — |

---

## 7.2 Struktur URL

| Segmen | Keterangan |
|---|---|
| `asperda.id` | Website utama WordPress (existing, tidak dimodifikasi) |
| `daftar.asperda.id` | Subdomain sistem registrasi ini |
| `daftar.asperda.id/api/v1/*` | REST API endpoint Laravel |
| `daftar.asperda.id/*` | Vue SPA (semua route non-API dilayani `index.html`) |

Subdomain `daftar.asperda.id` mengarah ke direktori project Laravel di shared hosting. Konfigurasi `public/` sebagai document root.

---

## 7.3 Database

11 tabel utama ASPERDA + 4 tabel wilindo (read-only, prefix `wilindo_`) + tabel `app_settings` + tabel sistem Laravel (`jobs`, `failed_jobs`, `personal_access_tokens`, `activity_log`, dll).

Detail lengkap kolom, tipe data, constraint, dan relasi ada di dokumen `asperda_erd.md`.

**Penting:** Relasi ke tabel wilindo menggunakan `code` varchar (bukan FK integer). Custom relationship di model Laravel diperlukan — tidak bisa menggunakan convention `belongsTo` standar.

---

## 7.4 Penyesuaian Shared Hosting

Shared hosting tidak mendukung persistent process. Dua komponen Laravel yang terpengaruh adalah Queue Worker dan Scheduler, keduanya perlu dikonfigurasi berbeda dari setup VPS standar.

### Queue Worker

Shared hosting tidak mengizinkan `php artisan queue:work` berjalan terus-menerus. Solusi yang digunakan:

Tambahkan cron job di cPanel dengan interval **setiap menit**:

```
* * * * * cd /home/username/public_html/daftar && php artisan queue:work --stop-when-empty --tries=3 --timeout=60 >> /dev/null 2>&1
```

Flag `--stop-when-empty` membuat worker berhenti sendiri setelah antrian kosong, sehingga tidak ada proses yang menggantung. Setiap menit cron memanggil kembali worker jika ada job baru.

**Konsekuensi:** Ada delay maksimal 1 menit antara job masuk antrian dan mulai dieksekusi. Untuk volume ASPERDA ini dapat diterima.

### Laravel Scheduler

Tambahkan satu cron job di cPanel:

```
* * * * * cd /home/username/public_html/daftar && php artisan schedule:run >> /dev/null 2>&1
```

Laravel Scheduler mengelola semua jadwal internal (`CheckSurveyDeadlineJob` setiap jam, `SendPaymentReminderJob` setiap pukul 08:00) dari satu cron entry ini. Tidak perlu cron terpisah per job.

### Verifikasi Shared Hosting

Sebelum deploy, konfirmasi hal berikut ke provider hosting:

| Requirement | Keterangan |
|---|---|
| Cron job minimal 1x/menit | Beberapa paket murah membatasi minimum 1x/hari |
| PHP 8.3+ | Pastikan versi PHP dapat dipilih di cPanel |
| MySQL 8.0+ | Atau MariaDB 10.6+ sebagai alternatif |
| `proc_open()` tidak diblokir | Dibutuhkan oleh beberapa package Composer |
| Ukuran file upload | Untuk storage PDF sertifikat |
| Akses SSH | Sangat disarankan untuk menjalankan `artisan migrate` dan `db:seed` |

### Storage Sertifikat PDF

Gunakan `storage/app/certificates/` (Laravel local disk). File diakses melalui endpoint API yang terautentikasi, bukan diekspos langsung ke URL publik.

Pastikan direktori `storage/` dan `bootstrap/cache/` dapat ditulis oleh web server (`chmod 775` atau sesuai konfigurasi provider).

---

## 7.5 Queue Jobs

| Job | Trigger | Keterangan |
|---|---|---|
| `SendEmailVerificationJob` | Registrasi user | Kirim signed URL verifikasi email |
| `SendSurveyNotificationJob` | Assignment baru dibuat | Email notifikasi ke petugas yang ditugaskan |
| `CheckSurveyDeadlineJob` | Scheduled, setiap jam | Cek assignment yang deadline-nya terlewati tanpa `accepted_at` |
| `EscalateSurveyJob` | Dipanggil oleh `CheckSurveyDeadlineJob` | Buat assignment baru + sanctions + notifikasi level atas |
| `GenerateCertificateJob` | Payment paid + survey approved | Generate PDF sertifikat via dompdf |
| `SendPaymentReminderJob` | Scheduled, setiap pukul 08:00 | Email pengingat ke member yang payment-nya masih `pending` |

Semua job menggunakan queue driver `database`. Tabel `jobs` dan `failed_jobs` di-generate via `php artisan queue:table`.

**Failed job handling:** Jika job gagal setelah semua retry, record masuk ke `failed_jobs`. Super Admin mendapat notifikasi email. Untuk `GenerateCertificateJob`, Super Admin dapat memicu ulang generate dari halaman detail member.

---

## 7.6 Midtrans Integration

**Library:** `midtrans/midtrans-php`

**Flow:**

```
Frontend klik "Bayar"
    │
    ▼
POST /api/v1/payment/create
    │ (Laravel membuat transaksi Snap, return snap_token)
    ▼
Frontend load Midtrans Snap popup dengan snap_token
    │
    ▼
User selesaikan payment di popup Midtrans
    │
    ▼
Midtrans kirim webhook POST /api/v1/payment/webhook
    │ (Laravel verifikasi signature, update status)
    ▼
Frontend polling GET /api/v1/payment/{id}/status
    │ (setiap 5 detik selama di halaman payment)
    ▼
Status berubah → redirect ke halaman sukses/gagal
```

**Konfigurasi `.env`:**

```
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

**Webhook security:** Verifikasi signature dengan `SHA512(order_id + status_code + gross_amount + server_key)`. Tolak request yang signature-nya tidak cocok dengan HTTP 403.

**Order ID format:** `ASPERDA-{member_id}-{timestamp_unix}` — memastikan keunikan antar transaksi.

**Idempotency:** Sebelum memproses webhook, cek apakah `midtrans_transaction_id` sudah ada di database. Jika ada, return 200 tanpa memproses ulang.

---

## 7.7 PDF Sertifikat (dompdf)

**Library:** `barryvdh/laravel-dompdf`

Dipilih karena kompatibel dengan shared hosting (murni PHP, tidak butuh binary eksternal seperti Chrome/Puppeteer yang dibutuhkan `spatie/browsershot`).

**Template:** Blade view di `resources/views/certificates/template.blade.php`. Gunakan CSS inline atau `<style>` tag — dompdf tidak mendukung external stylesheet via HTTP.

**Keterbatasan dompdf yang perlu diperhatikan:**

| Keterbatasan | Cara kerja |
|---|---|
| CSS modern terbatas | Gunakan CSS 2.1, hindari flexbox/grid |
| Font custom | Embed font via `@font-face` dengan path absolut ke file font |
| Gambar | Gunakan path absolut atau base64 encode di template |
| Tabel | Didukung dengan baik, gunakan tabel untuk layout kompleks |

**QR Code:** Generate menggunakan `simplesoftwareio/simple-qrcode` (wrapper SVG/PNG, murni PHP). QR code berisi URL `https://daftar.asperda.id/verify/{cert_number}`.

**Rekomendasi template:** Layout tabel 2 kolom, logo ASPERDA di header (base64), QR code di pojok kanan bawah, tanda tangan digital (gambar) di atas nama petugas.

---

## 7.8 PWA

**Plugin:** `vite-plugin-pwa`

**`manifest.json` minimal:**

```json
{
  "name": "ASPERDA — Registrasi Keanggotaan",
  "short_name": "ASPERDA",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#your-brand-color",
  "icons": [
    { "src": "/icons/icon-192.png", "sizes": "192x192", "type": "image/png" },
    { "src": "/icons/icon-512.png", "sizes": "512x512", "type": "image/png" }
  ]
}
```

**Service Worker strategy:** Cache-first untuk assets statis (JS, CSS, gambar). Network-first untuk API calls. Halaman login dan registrasi di-cache untuk akses offline (menampilkan form, submit tetap butuh koneksi).

**Play Store distribution:** Setelah PWA live dan dapat diinstall, gunakan [PWABuilder](https://www.pwabuilder.com/) untuk generate APK wrapper (Trusted Web Activity). Tidak memerlukan Capacitor atau React Native.

**Syarat installable di Android Chrome:**
- HTTPS wajib (shared hosting umumnya sudah support Let's Encrypt)
- `manifest.json` valid dengan `start_url` dan ikon
- Service worker terdaftar
- Skor Lighthouse PWA ≥ 80 (rekomendasi sebelum publish ke Play Store)
