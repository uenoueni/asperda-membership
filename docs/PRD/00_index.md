# PRD — Sistem Registrasi Keanggotaan ASPERDA

**Versi:** 1.0  
**Tanggal:** 2025  
**Author:** Internal  
**Status:** Draft — Siap Review

---

## Daftar Isi

| File | Isi |
|---|---|
| `00_index.md` | Dokumen ini — daftar isi dan ringkasan |
| `01_overview.md` | Latar belakang, tujuan, ruang lingkup, pengguna & peran |
| `02_alur_sistem.md` | Alur utama: registrasi, verifikasi berjenjang, refund, perpanjangan |
| `03_spesifikasi_fitur.md` | Spesifikasi fitur lengkap per modul (F-01 s/d F-62) |
| `04_teknis.md` | Stack, environment shared hosting, queue, Midtrans, PWA, PDF |
| `05_ux_nfr_roadmap.md` | UX requirements, non-functional requirements, batasan, roadmap, urutan pengembangan |

---

## Ringkasan Sistem

Sistem registrasi keanggotaan digital untuk ASPERDA (Asosiasi Pengusaha Rental Kendaraan Indonesia), menggantikan proses manual dengan platform terpusat yang dapat diakses via web dan mobile (PWA).

**Stack:** Laravel 11 REST API + Vue 3 PWA  
**Hosting:** Shared hosting (cPanel) dengan penyesuaian queue dan scheduler  
**PDF Sertifikat:** `barryvdh/laravel-dompdf`  
**Payment:** Midtrans Snap  
**Wilayah:** `didiwijaya/wilindo` v2.0.0

---

## Modul Utama (v1.0)

| No | Modul | Kode Fitur |
|---|---|---|
| 1 | Auth & Email Verification | F-01 – F-07 |
| 2 | Registrasi & Profil Member | F-08 – F-13 |
| 3 | Payment (Midtrans) | F-14 – F-21 |
| 4 | Survey & Eskalasi | F-22 – F-31 |
| 5 | Sertifikat Keanggotaan | F-32 – F-38 |
| 6 | Refund Management | F-39 – F-46 |
| 7 | Starterkit & Inventori | F-47 – F-54 |
| 8 | Admin Dashboard & RBAC | F-55 – F-62 |

---

## Urutan Pengembangan

```
1. Setup project
2. Auth & Email Verification
3. Registrasi & Profil
4. Payment (Midtrans)
5. Survey & Eskalasi
6. Sertifikat
7. Refund
8. Starterkit
9. Admin Dashboard & RBAC
10. PWA
```

Jangan mulai modul berikutnya sebelum modul sebelumnya selesai dan bisa diuji end-to-end. Detail ada di `05_ux_nfr_roadmap.md`.

---

## Dokumen Terkait

- `asperda_erd.md` — Entity Relationship Diagram lengkap (semua tabel, kolom, relasi)
