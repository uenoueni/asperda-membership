# PRD — 05: UX, Non-Functional Requirements, Roadmap

**← Kembali ke:** `04_teknis.md`  
**Kembali ke index:** `00_index.md`

---

## 8. User Experience Requirements

**UX-01** Flow registrasi multi-step — user tidak melihat semua field sekaligus. Tepat 3 langkah sebelum pembayaran: (1) data dasar + rekening, (2) data keanggotaan + wilayah, (3) halaman review.

**UX-02** Cascading dropdown wilayah (provinsi → kota → kecamatan) memanggil API endpoint yang mengembalikan data dari wilindo. Bukan di-hardcode di frontend. Kecamatan opsional — field tidak wajib diisi.

**UX-03** Status payment diperbarui secara otomatis menggunakan polling ke endpoint status (setiap 5 detik). User tidak perlu refresh halaman manual untuk mengetahui apakah payment berhasil.

**UX-04** Dashboard member menampilkan status keanggotaan dengan jelas dalam satu area yang prominent:
- **Aktif:** badge hijau + "Berlaku hingga [tanggal] (XX hari lagi)"
- **Akan expired (< 30 hari):** badge kuning + tombol perpanjang
- **Expired:** badge merah + tombol perpanjang
- **Menunggu survey:** badge biru + estimasi waktu verifikasi
- **Ditolak:** badge merah + alasan + status refund

**UX-05** Sertifikat dapat diunduh dari dashboard dalam satu klik. Tidak perlu navigasi tambahan. Jika sertifikat sedang di-generate (job belum selesai), tampilkan spinner dengan teks "Sertifikat sedang disiapkan..."

**UX-06** Semua halaman responsif dan usable di layar mobile 375px ke atas. Prioritaskan mobile-first karena mayoritas anggota kemungkinan mengakses via smartphone.

**UX-07** Formulir yang gagal validasi menampilkan pesan error spesifik di bawah setiap field yang bermasalah — bukan toast atau alert di atas form yang hilang setelah beberapa detik. Error tetap terlihat sampai user memperbaiki input.

**UX-08** Setiap aksi destruktif memerlukan konfirmasi eksplisit via modal dialog sebelum dieksekusi:
- Petugas menolak calon anggota
- Admin menerbitkan sanksi
- Admin membatalkan refund
- Super Admin menonaktifkan akun petugas

---

## 9. Non-Functional Requirements

**NFR-01 Keamanan**
Semua endpoint API memerlukan autentikasi Sanctum kecuali: registrasi, verifikasi email, login, reset password, dan halaman verifikasi sertifikat publik. Webhook Midtrans diverifikasi dengan SHA512 signature sebelum diproses. Signed URL untuk verifikasi email menggunakan HMAC-SHA256. Semua input divalidasi di backend (tidak mengandalkan validasi frontend saja).

**NFR-02 RBAC & Scope Wilayah**
Validasi scope wilayah dilakukan di backend pada level Laravel Policy, bukan hanya di frontend. Setiap query yang mengambil data member, survey, atau distribusi starterkit selalu include filter wilayah berdasarkan `organizational_units` user yang sedang login. Pelanggaran scope mengembalikan HTTP 403, bukan data kosong.

**NFR-03 Data Integrity**
Update `starterkit_items.stock_distributed` selalu dalam `DB::transaction()` dengan `lockForUpdate()`. Pembuatan refund record dan update status survey/member terjadi dalam satu transaction yang sama. Webhook Midtrans bersifat idempoten — diproses sekali meski diterima berkali-kali.

**NFR-04 Auditability**
Seluruh chain eskalasi survei dapat ditelusuri via `survey_assignments.escalated_from`. Raw response Midtrans tersimpan permanen. Setiap sanctions record menyimpan `issued_by` dan `issued_to`. Aksi penting di-log via `spatie/laravel-activitylog` dengan timestamp dan user yang melakukan.

**NFR-05 Maintainability**
Sistem dikelola satu orang. Pilih package yang stabil dan memiliki komunitas aktif. Hindari custom implementation untuk hal-hal yang sudah ada package-nya (auth, PDF, activity log). Dokumentasikan semua keputusan arsitektur non-obvious di komentar kode atau `README.md`.

**NFR-06 Queue Reliability**
Gunakan tabel `failed_jobs` (tersedia built-in di Laravel). Job yang gagal setelah semua retry masuk ke `failed_jobs` dan memicu notifikasi email ke Super Admin. `GenerateCertificateJob` dapat di-retry manual oleh Super Admin dari halaman detail member tanpa harus masuk ke Artisan.

**NFR-07 Kompatibilitas Shared Hosting**
Tidak ada persistent process. Queue worker dan Scheduler dijalankan via cron `--stop-when-empty`. Tidak ada dependency binary eksternal (tidak ada Chrome, ImageMagick wajib, atau ffmpeg). PDF sertifikat murni PHP via dompdf.

---

## 10. Batasan dan Asumsi

- **Satu user = satu entitas rental.** Multi-cabang di UI belum diekspos meski struktur DB sudah siap. Upgrade ke multi-cabang membutuhkan refactor UI dan beberapa API endpoint, tapi tidak membutuhkan perubahan skema database.

- **Refund manual via transfer bank.** Tidak ada auto-refund via Midtrans API. Admin harus memproses secara manual dan mengupdate status di sistem. Data rekening tujuan refund adalah snapshot dari saat registrasi — immutable.

- **Notifikasi hanya via email** untuk v1.0. Push notification (PWA Web Push) dan SMS bukan bagian scope ini.

- **Tidak ada integrasi langsung dengan WordPress.** Sistem ASPERDA WordPress hanya menempatkan link yang mengarah ke `daftar.asperda.id`. Tidak ada SSO, shared session, atau database yang sama.

- **Cron job diasumsikan tersedia** di shared hosting. Jika provider membatasi interval cron minimum lebih dari 1 menit, `CheckSurveyDeadlineJob` masih akan berjalan tapi dengan presisi yang lebih rendah. Verifikasi ke provider sebelum deploy.

- **PDF sertifikat menggunakan dompdf.** Template harus dirancang dengan keterbatasan CSS 2.1. Desain sertifikat yang terlalu kompleks perlu disederhanakan agar render dengan benar.

- **Wilindo data seeding memakan waktu.** Seeder mengambil data dari API SPLP Kemendagri secara real-time untuk 83.000+ desa di seluruh Indonesia. Jalankan seeder pada saat server tidak sibuk dan pastikan koneksi internet stabil. Proses bisa memakan waktu 30–60 menit.

- **Biaya pendaftaran dan perpanjangan belum ditentukan** di dokumen ini — dikonfigurasi via `app_settings` setelah sistem live.

---

## 11. Roadmap Post v1.0

| Fase | Fitur | Dependency | Estimasi Effort |
|---|---|---|---|
| v1.1 | Multi-cabang UI untuk member | Struktur DB sudah siap, butuh refactor form registrasi dan beberapa endpoint | Medium |
| v1.2 | PWA Web Push Notification | Service worker sudah ada dari v1.0 | Low |
| v1.3 | Export laporan (PDF/Excel) per periode | — | Low–Medium |
| v1.4 | Halaman statistik dashboard (jumlah anggota per wilayah, per periode) | — | Medium |
| v2.0 | Marketplace merchandise ASPERDA | Modul payment v1.0 sebagai fondasi, butuh modul baru: produk, keranjang, order | High |

---

## 12. Urutan Pengembangan yang Disarankan

Urutan ini mempertimbangkan dependency ketat antar modul. Modul berikutnya tidak boleh dimulai sebelum modul sebelumnya selesai **dan dapat diuji end-to-end**, bukan hanya "sudah ada kode-nya".

### Tahap 1 — Fondasi

**1. Setup Project**
- Inisialisasi Laravel 11 + Vue 3 + Vite
- Konfigurasi Laravel Sanctum untuk SPA auth
- Install dan publish `didiwijaya/wilindo`, jalankan migration + seeder wilindo
- Jalankan semua migration tabel ASPERDA
- Setup queue driver `database`, buat tabel `jobs` dan `failed_jobs`
- Konfigurasi cron di cPanel (development: simulasikan dengan menjalankan manual)
- Buat struktur folder API: `app/Http/Controllers/Api/V1/`

**2. Auth & Email Verification**
- Endpoint registrasi (simpan user dengan password null)
- Kirim email verifikasi via `SendEmailVerificationJob`
- Endpoint verifikasi signed URL → set `email_verified_at`
- Endpoint set password setelah verifikasi
- Endpoint login (return Sanctum cookie)
- Endpoint logout
- Endpoint lupa password + reset password
- Vue pages: register, verify-email, set-password, login, forgot-password

**Checkpoint:** Siklus registrasi → verifikasi email → set password → login → logout berjalan penuh.

---

### Tahap 2 — Registrasi & Payment

**3. Registrasi & Profil**
- Endpoint cascading wilayah: `GET /api/v1/wilayah/provinsi`, `GET /api/v1/wilayah/kota/{province_code}`, `GET /api/v1/wilayah/kecamatan/{city_code}`
- Endpoint simpan data keanggotaan (buat `members` + `branches`)
- Endpoint GET profil member
- Endpoint update profil
- Vue pages: form multi-step, review, dashboard member (skeleton)

**4. Payment**
- Endpoint `POST /api/v1/payment/create` → return `snap_token`
- Endpoint webhook `POST /api/v1/payment/webhook` → verifikasi signature → update status
- Endpoint `GET /api/v1/payment/{id}/status` → untuk polling
- Vue: integrasi Midtrans Snap JS, halaman status payment, polling logic

**Checkpoint:** Registrasi penuh dari isi form → bayar via Midtrans sandbox → webhook update status → member masuk waiting_survey.

---

### Tahap 3 — Survey & Keluaran

**5. Survey & Eskalasi**
- Endpoint untuk petugas: list antrian, detail assignment, accept, approve, reject
- `CheckSurveyDeadlineJob` + `EscalateSurveyJob`
- `SendSurveyNotificationJob`
- Endpoint manajemen sanksi (create, list)
- Vue pages: halaman antrian survey per level, detail calon anggota, form keputusan, riwayat sanksi

**Checkpoint:** Alur penuh DPC accept → approve berjalan. Alur timeout → eskalasi DPD berjalan via job manual (artisan dispatch).

**6. Sertifikat**
- `GenerateCertificateJob` + template Blade dompdf
- Generate `membership_no` saat member disetujui
- Endpoint `GET /api/v1/certificate/{id}/download`
- Endpoint verifikasi publik `GET /verify/{cert_number}`
- Vue: tombol download di dashboard, halaman verifikasi publik

**7. Refund**
- Auto-create refund saat survey ditolak
- Endpoint daftar refund (admin) dengan filter
- Endpoint update status refund
- Endpoint export CSV
- Vue: halaman refund admin, status refund di dashboard member

---

### Tahap 4 — Distribusi & Admin

**8. Starterkit**
- CRUD `starterkit_items`
- Auto-create distributions saat member diterima
- Endpoint update status distribusi (distributed, confirmed)
- Vue: halaman manajemen item, daftar distribusi dengan filter, konfirmasi di dashboard member

**9. Admin Dashboard & RBAC**
- Implementasi Laravel Policy untuk scope wilayah semua endpoint yang ada
- Manajemen akun petugas + organizational units (Super Admin)
- Halaman daftar member dengan filter lengkap
- Halaman detail member (agregasi semua data)
- Halaman konfigurasi `app_settings`
- Setup `spatie/laravel-activitylog`

**Checkpoint:** DPC tidak bisa melihat data kota lain. DPD tidak bisa melihat data provinsi lain.

---

### Tahap 5 — Polish & Deploy

**10. PWA**
- Konfigurasi `vite-plugin-pwa`: manifest, ikon, service worker
- Test install di Android Chrome
- Lighthouse audit (target PWA score ≥ 80)
- PWABuilder untuk generate APK wrapper

**Deploy ke Shared Hosting**
- Upload files via FTP atau SSH
- Set `APP_ENV=production`, `APP_DEBUG=false`
- Jalankan `php artisan migrate --force`
- Jalankan wilindo seeder
- Set cron di cPanel (queue worker + scheduler)
- Verifikasi HTTPS dan konfigurasi subdomain
- Test webhook Midtrans dengan production endpoint

---

*Dokumen ini adalah referensi utama pengembangan. Setiap perubahan scope harus direflesikan di sini sebelum implementasi.*
