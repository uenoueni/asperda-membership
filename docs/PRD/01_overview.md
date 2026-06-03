# PRD — 01: Overview

**← Kembali ke:** `00_index.md`  
**Selanjutnya:** `02_alur_sistem.md`

---

## 1. Latar Belakang

ASPERDA (Asosiasi Pengusaha Rental Kendaraan Indonesia) saat ini mengelola keanggotaan secara manual. Proses pendaftaran, verifikasi, pembayaran, dan penerbitan sertifikat dilakukan tanpa sistem terpusat, mengakibatkan data tidak terstruktur, proses verifikasi tidak transparan, dan tidak ada mekanisme eskalasi formal jika petugas tidak menjalankan tugasnya.

Sistem ini dibangun untuk mendigitalisasi seluruh siklus keanggotaan — dari registrasi calon anggota, verifikasi berjenjang oleh petugas organisasi, pengelolaan pembayaran, hingga penerbitan sertifikat dan distribusi starterkit — dalam satu platform terpusat yang dapat diakses via web dan mobile.

---

## 2. Tujuan Produk

- Memberikan jalur pendaftaran keanggotaan yang terstruktur dan mandiri bagi calon anggota
- Memastikan setiap pengajuan melalui proses verifikasi berjenjang (DPC → DPD → DPP) dengan akuntabilitas yang bisa di-audit
- Mengotomasi pembayaran via Midtrans dan penerbitan sertifikat digital
- Memberi visibilitas penuh kepada administrator atas status setiap anggota, petugas, dan distribusi starterkit
- Menyediakan rekam jejak sanksi atas kelalaian petugas sebagai alat governance internal organisasi

---

## 3. Ruang Lingkup

### Dalam Scope (v1.0)

- Registrasi calon anggota dengan verifikasi email dan pengisian data bertahap
- Pembayaran biaya pendaftaran via Midtrans (registrasi & perpanjangan)
- Alur verifikasi berjenjang DPC → DPD → DPP dengan eskalasi otomatis berbasis deadline
- Pengelolaan sanksi atas kelalaian petugas
- Refund manual untuk pendaftar yang ditolak
- Penerbitan sertifikat keanggotaan digital (PDF auto-generate)
- Manajemen starterkit/seragam per periode: stok, distribusi, konfirmasi penerimaan
- Dashboard admin per level (DPC, DPD, DPP, Super Admin) dengan scope wilayah
- PWA installable di Android

### Di Luar Scope (roadmap berikutnya)

- Marketplace khusus merchandise ASPERDA
- Multi-cabang per member (struktur DB sudah mendukung, UI belum diekspos)
- Notifikasi push native (PWA pakai web push, bukan native)
- Integrasi SSO dengan website WordPress ASPERDA

---

## 4. Pengguna dan Peran

### 4.1 Calon Anggota (Publik)

Pengusaha rental kendaraan yang belum terdaftar. Mengakses sistem melalui link dari website ASPERDA. Tidak memiliki akun sebelum mendaftar.

**Akses:** Halaman registrasi, verifikasi email, set password, form data keanggotaan, halaman pembayaran.

### 4.2 Anggota Aktif (Member)

Calon anggota yang telah lulus verifikasi dan membayar. Dapat mengakses dashboard pribadi.

**Akses:** Status keanggotaan, unduh sertifikat, informasi starterkit, konfirmasi penerimaan starterkit, perpanjangan keanggotaan, edit profil, status refund (jika pernah ditolak).

### 4.3 Petugas DPC (Dewan Pimpinan Cabang)

Petugas tingkat kota. Menerima antrian verifikasi anggota baru dari kota yang menjadi wilayah tugasnya.

**Akses:** Antrian survey (hanya kota sendiri), detail calon anggota, tombol "Terima Tugas", form approve/tolak, riwayat keputusan.  
**Batasan:** Tidak bisa melihat data di luar kota wilayahnya.

### 4.4 Petugas DPD (Dewan Pimpinan Daerah)

Petugas tingkat provinsi. Menerima eskalasi dari DPC yang melewati deadline.

**Akses:** Antrian survey eskalasi (hanya provinsi sendiri), detail calon anggota, riwayat kelalaian DPC di provinsinya, form approve/tolak, form pemberian flag/sanksi ke DPC.  
**Batasan:** Tidak bisa melihat data di luar provinsi wilayahnya.

### 4.5 Petugas DPP (Dewan Pimpinan Pusat)

Petugas tingkat nasional. Level tertinggi dalam rantai eskalasi.

**Akses:** Antrian survey eskalasi dari DPD (nasional), semua riwayat kelalaian lintas wilayah, form approve/tolak, form pemberian sanksi ke DPD dan DPC, laporan governance.

### 4.6 Super Admin

Akses penuh ke seluruh sistem tanpa batasan wilayah.

**Akses:** Semua data lintas wilayah, manajemen akun petugas dan unit organisasi, konfigurasi global (`app_settings`), monitoring queue dan job failures, semua modul.
