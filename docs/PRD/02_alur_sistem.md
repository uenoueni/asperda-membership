# PRD — 02: Alur Utama Sistem

**← Kembali ke:** `01_overview.md`  
**Selanjutnya:** `03_spesifikasi_fitur.md`

---

## 5.1 Registrasi dan Onboarding Anggota

```
[Calon Anggota]
     │
     ▼
Klik link dari website ASPERDA (asperda.id → daftar.asperda.id)
     │
     ▼
LANGKAH 1 — Isi data dasar:
  - Nama lengkap
  - Nama usaha rental
  - Email
  - Nomor telepon
  - Nomor rekening bank (nama bank, no. rekening, nama pemilik)
     │
     ▼
Sistem kirim email verifikasi (signed URL, berlaku 24 jam)
     │
     ▼
User klik link di email → redirect ke halaman set password
     │
     ▼
LANGKAH 2 — Isi data keanggotaan lengkap:
  - Provinsi operasional (dropdown dari wilindo)
  - Kota/kabupaten operasional (cascading dari provinsi)
  - Kecamatan (opsional, cascading dari kota)
  - Alamat lengkap
  - Jumlah unit armada
  - Nama cabang
     │
     ▼
LANGKAH 3 — Halaman review:
  Tampilkan ringkasan semua data yang diisi
  User konfirmasi sebelum lanjut ke pembayaran
     │
     ▼
Bayar via Midtrans Snap (VA, GoPay, QRIS, dll)
     │
     ├── [Payment GAGAL / EXPIRED]
     │         │
     │    Member dapat memulai payment baru
     │    tanpa mendaftar ulang dari awal
     │
     └── [Payment PAID — via Midtrans webhook]
               │
               ▼
         members.status: pending_verification → waiting_survey
         Sistem assign ke DPC wilayah kota operasional member
         Notifikasi email ke petugas DPC yang ditugaskan
```

---

## 5.2 Alur Verifikasi Berjenjang

```
[ANTRIAN SURVEY — DPC menerima notifikasi]
     │
     ├── [DPC ACCEPT dalam deadline]
     │         │
     │    DPC klik "Terima Tugas"
     │    survey_assignments.accepted_at = now()
     │         │
     │    DPC lakukan verifikasi lapangan
     │         │
     │    ┌────┴────────────────┐
     │    │                     │
     │  SETUJUI               TOLAK
     │    │                     │ (alasan wajib diisi)
     │    ▼                     ▼
     │  members.status:      members.status: rejected
     │    active             refund record dibuat otomatis
     │  sertifikat           (lihat 5.3)
     │    di-generate
     │  starterkit entry
     │    dibuat
     │
     └── [DPC TIDAK ACCEPT sebelum deadline]
               │
         CheckSurveyDeadlineJob mendeteksi timeout
               │
         Sistem otomatis:
         ① survey_assignments.status = escalated  (DPC)
         ② Buat sanctions record
            - issued_to: petugas DPC
            - issued_by: system
            - severity: warning
         ③ Buat survey_assignment baru
            - level: dpd
            - escalated_from: ID assignment DPC
         ④ Notifikasi email ke DPD:
            "Ada assignment terbengkalai dari DPC [nama unit]"
               │
               ▼
         [ANTRIAN SURVEY — DPD menerima]
               │
               ├── [DPD ACCEPT dalam deadline]
               │         │
               │    DPD klik "Terima Tugas"
               │    DPD dapat melihat riwayat kelalaian DPC
               │    DPD dapat memberi flag/sanksi manual ke DPC
               │         │
               │    DPD putuskan: SETUJUI atau TOLAK
               │    (sama seperti alur DPC di atas)
               │
               └── [DPD TIDAK ACCEPT sebelum deadline]
                         │
                   Sistem otomatis:
                   ① survey_assignments.status = escalated (DPD)
                   ② Buat sanctions record untuk DPD
                   ③ Buat survey_assignment baru
                      - level: dpp
                      - escalated_from: ID assignment DPD
                   ④ Notifikasi email ke DPP
                         │
                         ▼
                   [ANTRIAN SURVEY — DPP menerima]
                   DPP wajib accept + putuskan
                   Tidak ada eskalasi lebih lanjut
                   DPP dapat menerbitkan sanksi ke DPD
```

**Catatan chain eskalasi:** Setiap eskalasi menghasilkan record `survey_assignment` baru yang menunjuk ke record sebelumnya melalui kolom `escalated_from`. Chain ini tidak pernah dihapus dan dapat ditelusuri penuh di halaman detail member.

---

## 5.3 Alur Refund

```
Survey → DITOLAK oleh petugas (alasan wajib diisi)
     │
     ▼
Sistem otomatis buat refund record:
  - payment_id        → linked ke payment registrasi
  - member_id         → member yang ditolak
  - survey_assignment_id → assignment yang memutuskan penolakan
  - rejection_reason  → copy dari survey_assignment.rejection_reason
  - rejected_by       → copy dari survey_assignment.assigned_to
  - original_amount   → copy dari payments.amount
  - refund_amount     → default sama dengan original_amount
  - bank_name         → SNAPSHOT dari users.bank_name saat ini
  - bank_account_no   → SNAPSHOT dari users.bank_account_no
  - bank_account_name → SNAPSHOT dari users.bank_account_name
  - status            → queued
     │
     ▼
Admin melihat daftar refund di halaman Refund Management
     │
     ▼
Admin isi planned_refund_date → ubah status: processing
     │
     ▼
Admin lakukan transfer manual ke rekening member
     │
     ▼
Admin isi actual_refund_date + notes → ubah status: completed
     │
     ▼
Member melihat status refund di dashboard (read-only)

CATATAN: Data rekening di refund record tidak berubah meskipun
member memperbarui rekening di profil setelah refund dibuat.
```

---

## 5.4 Perpanjangan Keanggotaan

```
Member mendekati atau melewati expires_at
     │
     ▼
Dashboard member menampilkan notifikasi perpanjangan
(ditampilkan mulai H-30 sebelum kadaluarsa)
     │
     ▼
Member klik "Perpanjang Keanggotaan"
     │
     ▼
Sistem buat payment record baru:
  - type: renewal
  - period_year: tahun baru
  - amount: sesuai app_settings.renewal_fee
     │
     ▼
Member bayar via Midtrans
     │
     ▼ [Payment PAID]
Sistem update:
  ① members.expires_at + durasi (sesuai app_settings.membership_duration_months)
  ② members.period_year = tahun baru
  ③ GenerateCertificateJob dipicu → sertifikat periode baru di-generate
  ④ starterkit_distributions baru dibuat untuk periode baru
     (tidak menimpa atau menghapus distribusi periode lama)

TIDAK ADA survey ulang untuk perpanjangan.
Keanggotaan langsung aktif setelah payment valid.
```
