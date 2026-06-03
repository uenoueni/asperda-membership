# PRD — 03: Spesifikasi Fitur

**← Kembali ke:** `02_alur_sistem.md`  
**Selanjutnya:** `04_teknis.md`

---

## Modul 1: Auth & Email Verification

**F-01** Registrasi akun baru hanya dengan email yang belum pernah digunakan di sistem.

**F-02** Sistem mengirim email verifikasi berisi signed URL yang berlaku 24 jam. URL menggunakan HMAC-SHA256 via Laravel `URL::signedRoute()`.

**F-03** User klik link verifikasi → `email_verified_at` terisi → diarahkan ke halaman set password. Password disimpan di `users.password` (nullable sebelum langkah ini).

**F-04** Setelah set password, user diarahkan ke form kelengkapan data keanggotaan (Langkah 2 registrasi).

**F-05** Login dengan email + password. Session menggunakan Laravel Sanctum cookie-based untuk SPA.

**F-06** Lupa password: user memasukkan email, sistem kirim link reset (berlaku 60 menit), user set password baru.

**F-07** Signed URL yang sudah digunakan atau expired menampilkan pesan yang jelas disertai tombol "Kirim ulang email verifikasi". Kirim ulang dibatasi rate limit (maksimal 3x/jam per email).

---

## Modul 2: Registrasi & Profil Member

**F-08** Form data dasar (Langkah 1): nama lengkap, nama usaha rental, email, nomor telepon, nama bank, nomor rekening, nama pemilik rekening.

**F-09** Form data keanggotaan (Langkah 2): cascading dropdown provinsi → kota → kecamatan (data dari wilindo via API endpoint terpisah), alamat lengkap, jumlah unit armada, nama cabang. Cabang pertama otomatis dijadikan `is_primary = true`.

**F-10** Validasi wajib: email unik, nomor telepon format Indonesia (dimulai `08` atau `+62`), semua field wajib terisi kecuali kecamatan. Validasi dilakukan di backend (Laravel Form Request), error dikembalikan per-field.

**F-11** Langkah 3 (sebelum payment): halaman review menampilkan ringkasan semua data dari Langkah 1 dan 2 dalam format read-only. User harus mengklik "Konfirmasi & Lanjut ke Pembayaran" secara eksplisit.

**F-12** Member aktif dapat mengedit data profil: nama, telepon, alamat, jumlah unit, data rekening. Perubahan email memerlukan verifikasi ulang ke email baru.

**F-13** Perubahan data rekening bank di profil tidak mempengaruhi data di tabel `refunds` yang sudah ada sebelumnya (data rekening di refund adalah snapshot immutable).

---

## Modul 3: Payment (Midtrans)

**F-14** Integrasi Midtrans Snap. Saat user klik bayar, sistem membuat transaksi Snap dan mengembalikan `snap_token` ke frontend. Frontend membuka Midtrans Snap popup menggunakan token tersebut.

**F-15** Channel yang didukung: Virtual Account (semua bank besar), GoPay, QRIS, dan channel lain yang diaktifkan di akun Midtrans.

**F-16** Webhook endpoint `POST /api/v1/payment/webhook` menerima notifikasi status transaksi dari Midtrans. Endpoint memverifikasi signature (`SHA512` dari `order_id + status_code + gross_amount + server_key`) sebelum memproses.

**F-17** Idempotency: sebelum mengupdate status payment, sistem mengecek apakah `midtrans_transaction_id` sudah pernah diproses. Webhook yang sama dapat diterima lebih dari sekali tanpa menghasilkan efek ganda.

**F-18** Jika payment expired atau gagal, member dapat memulai payment baru dari dashboard tanpa harus mengisi ulang data dari awal. Sistem membuat `payments` record baru untuk order yang sama.

**F-19** Status payment di frontend diperbarui secara otomatis. Karena shared hosting tidak mendukung WebSocket, implementasi menggunakan polling (`setInterval` setiap 5 detik) ke endpoint `GET /api/v1/payment/{id}/status` selama berada di halaman pembayaran.

**F-20** Raw response JSON dari Midtrans disimpan di `payments.midtrans_raw_response` untuk keperluan audit, dispute, dan debugging.

**F-21** Biaya pendaftaran (`registration_fee`) dan biaya perpanjangan (`renewal_fee`) dapat diubah oleh Super Admin via halaman konfigurasi — tidak di-hardcode.

---

## Modul 4: Survey & Eskalasi

**F-22** Saat `payments.status` berubah menjadi `paid` (dipicu webhook), sistem otomatis membuat `survey_assignment` baru dengan: `level = dpc`, `status = pending`, `deadline = now() + survey_deadline_dpc_days`, dan `assigned_to` = petugas DPC yang unit organisasinya mencakup `city_code` dari branch utama member.

**F-23** Notifikasi email dikirim ke petugas DPC via `SendSurveyNotificationJob` (antri di queue) segera setelah assignment dibuat. Email berisi nama calon anggota, nama rental, kota, dan link ke halaman assignment.

**F-24** Petugas DPC harus klik "Terima Tugas" (`accepted_at` terisi) sebelum tombol approve/reject muncul. Ini mencegah petugas langsung memutuskan tanpa acknowledgment.

**F-25** Form penolakan memiliki field `rejection_reason` yang wajib diisi minimal 20 karakter. Tombol "Tolak" tidak bisa diklik jika field kosong.

**F-26** `CheckSurveyDeadlineJob` berjalan setiap jam via Laravel Scheduler (dipanggil dari cron `php artisan schedule:run`). Job ini mencari semua `survey_assignment` dengan `status = pending` dan `deadline < now()` dan `accepted_at IS NULL`.

**F-27** Untuk setiap assignment yang timeout: sistem mengubah `status` menjadi `escalated`, membuat `sanctions` record (`severity = warning`), membuat assignment baru ke level berikutnya (DPD atau DPP), dan mengirim notifikasi email ke level berikutnya.

**F-28** DPD dapat melihat riwayat semua `sanctions` yang diterima petugas DPC di provinsinya. DPD dapat menambahkan sanksi manual dengan severity `warning`, `suspension`, atau `termination` disertai catatan alasan.

**F-29** DPP dapat melihat riwayat sanctions lintas wilayah. DPP dapat menerbitkan sanksi manual ke DPD dan DPC manapun.

**F-30** Halaman detail member di admin dashboard menampilkan seluruh chain eskalasi secara visual — dari assignment DPC awal, eskalasi ke DPD, hingga DPP — dengan timestamp masing-masing tahapan.

**F-31** Nilai deadline per level dapat diubah di `app_settings`: `survey_deadline_dpc_days`, `survey_deadline_dpd_days`, `survey_deadline_dpp_days`. Perubahan berlaku untuk assignment yang dibuat setelah perubahan disimpan, tidak retroaktif.

---

## Modul 5: Sertifikat Keanggotaan

**F-32** Saat survey disetujui, `GenerateCertificateJob` dimasukkan ke queue. Job ini menggunakan `barryvdh/laravel-dompdf` untuk generate PDF dari template Blade.

**F-33** Isi sertifikat: nama lengkap member, nama usaha rental, nomor anggota (`membership_no`), kota operasional, periode berlaku (`valid_from` – `valid_until`), nama dan jabatan petugas yang menyetujui, nomor sertifikat, dan QR code yang berisi URL verifikasi publik.

**F-34** Nomor sertifikat mengikuti format: `ASPERDA/{period_year}/{membership_no}/{seq_3digit}`. Contoh: `ASPERDA/2025/0001/001`.

**F-35** Member dapat mengunduh sertifikat dari halaman dashboard kapan saja selama akun aktif. Endpoint `GET /api/v1/certificate/{id}/download` mengembalikan file PDF dengan header `Content-Disposition: attachment`.

**F-36** Sertifikat dari periode sebelumnya tetap dapat diakses dan diunduh setelah member melakukan perpanjangan (tidak dihapus atau di-overwrite).

**F-37** Halaman verifikasi publik `GET /verify/{cert_number}` dapat diakses tanpa login. Halaman menampilkan status validitas sertifikat (valid/expired/tidak ditemukan) beserta nama member dan periode berlaku. QR code di sertifikat mengarah ke URL ini.

**F-38** Jika `GenerateCertificateJob` gagal, job di-retry otomatis sebanyak 3 kali dengan interval backoff (1 menit, 5 menit, 15 menit). Jika semua retry habis, job masuk ke `failed_jobs` dan Super Admin mendapat notifikasi email.

---

## Modul 6: Refund Management

**F-39** Saat petugas menyimpan keputusan tolak, sistem otomatis membuat `refund` record dengan status `queued`. Proses ini terjadi dalam satu database transaction bersama update status survey dan member.

**F-40** Data rekening (bank_name, bank_account_no, bank_account_name) di-copy dari `users` saat record refund dibuat. Field ini tidak dapat diubah setelah record tersimpan.

**F-41** Halaman daftar refund admin menampilkan kolom: nama member, nama rental, alasan penolakan, nama petugas yang menolak, nominal, nama bank, nomor rekening, nama pemilik rekening, `planned_refund_date`, `actual_refund_date`, status, dan tombol aksi.

**F-42** Filter daftar refund: status (queued/processing/completed/cancelled), periode pendaftaran, dan wilayah (DPP/DPD/Super Admin dapat filter lintas wilayah).

**F-43** Admin mengisi `planned_refund_date` dan mengubah status ke `processing` untuk menandai refund sedang dalam proses.

**F-44** Setelah transfer selesai, admin mengisi `actual_refund_date` dan catatan opsional, lalu mengubah status ke `completed`.

**F-45** Member dapat melihat status refund mereka di halaman dashboard (read-only): alasan penolakan, nominal, rencana transfer, dan status terkini.

**F-46** Tombol export daftar refund ke CSV tersedia di halaman admin, dengan filter yang aktif saat itu ikut diterapkan ke data yang diexport.

---

## Modul 7: Starterkit & Inventori

**F-47** Super Admin dapat membuat, mengedit, dan menonaktifkan item starterkit per periode di halaman manajemen starterkit. Setiap item memiliki nama, deskripsi, `period_year`, dan `stock_total`.

**F-48** Saat member diterima (survey approved + payment paid), sistem otomatis membuat `starterkit_distributions` record berstatus `pending` untuk setiap `starterkit_items` yang `is_active = true` pada `period_year` yang sama.

**F-49** Halaman daftar distribusi dapat difilter berdasarkan: status (pending/distributed/confirmed), `period_year`, provinsi, dan kota.

**F-50** Admin menandai item sebagai `distributed` setelah serah terima fisik dengan mengisi `distributed_at`. Satu item per member dapat ditandai secara individual atau batch.

**F-51** Member mengkonfirmasi penerimaan dari dashboard dengan klik "Konfirmasi Penerimaan", mengubah status menjadi `confirmed` dan mengisi `confirmed_at`.

**F-52** Update `starterkit_items.stock_distributed` dilakukan dalam `DB::transaction()` dengan `lockForUpdate()` pada row item yang bersangkutan untuk mencegah race condition saat distribusi serentak ke banyak member.

**F-53** Dashboard inventori menampilkan per item per periode: `stock_total`, `stock_distributed`, stok belum terdistribusi (selisih), dan jumlah yang sudah dikonfirmasi member.

**F-54** Saat member perpanjang, distribusi periode baru dibuat sebagai record baru dengan `period_year` baru. Riwayat distribusi periode lama tidak berubah.

---

## Modul 8: Admin Dashboard & RBAC

**F-55** Scope wilayah divalidasi di backend (Laravel Policy), bukan hanya di frontend. Setiap query data member, branch, dan survey selalu memfilter berdasarkan `city_code` (DPC) atau `province_code` (DPD) dari `organizational_units` user yang sedang login.

**F-56** DPP dan Super Admin tidak memiliki batasan wilayah — query tidak difilter per wilayah.

**F-57** Super Admin dapat membuat akun petugas baru dengan menetapkan role dan unit organisasi (`organizational_units`). Super Admin dapat menonaktifkan akun petugas (`is_active = false`) tanpa menghapus data historisnya.

**F-58** Halaman daftar anggota dengan filter: status member, periode, provinsi, kota, nama member, dan nama rental. Mendukung pagination.

**F-59** Halaman detail member menampilkan dalam satu tampilan: data profil & branch, riwayat payment (semua periode), riwayat survey beserta chain eskalasi, sertifikat (semua periode), status starterkit per periode, dan status refund jika ada.

**F-60** Halaman daftar sanksi dapat difilter per petugas, per periode, dan per severity. DPP melihat semua. DPD hanya melihat sanksi petugas di provinsinya.

**F-61** Halaman konfigurasi sistem (`app_settings`) hanya dapat diakses oleh Super Admin. Menampilkan semua key yang bisa diedit dalam format form dengan deskripsi masing-masing setting.

**F-62** Aksi penting dicatat menggunakan `spatie/laravel-activitylog`: approve/reject survey, buat sanksi, update status refund, perubahan konfigurasi, dan pembuatan/nonaktifan akun petugas.
